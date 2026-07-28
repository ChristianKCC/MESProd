<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/config.php';
require_once __DIR__ . '/../utils/funciones.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$resp = ['ok' => false, 'msg' => ''];
$campos = $_POST['campos'] ?? [];

if (!is_array($campos)) { echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit; }
if (trim((string)($campos['D'] ?? '')) === '') { echo json_encode(['ok'=>false,'msg'=>'La tarea (D) es obligatoria']); exit; }

$lock = fopen($CFG['dir_logs'] . '/.lock', 'c');
if ($lock) flock($lock, LOCK_EX);

try {
    $ss = cargar_spreadsheet($CFG['ruta_excel']);
    $ws = $ss->getSheetByName($CFG['hoja']);

    $ini      = $CFG['fila_datos_ini'];
    $plant    = $CFG['fila_plantilla'];
    $ultima   = ultima_fila_datos($ws, $CFG['col_num'], $ini);
    $nuevaFila = $ultima + 1;
    $nuevoNum  = ($ultima - $ini + 1) + 1;   // consecutivo

    // 1) # autoincremental
    $ws->setCellValueExplicit($CFG['col_num'] . $nuevaFila, $nuevoNum, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

    // 2) Recorrer todas las columnas de la plantilla
    $iniIdx = ci($CFG['col_ini']); $finIdx = ci($CFG['col_fin']);
    for ($idx = $iniIdx; $idx <= $finIdx; $idx++) {
        $col = cl($idx);
        if (in_array($col, $CFG['cols_auto'], true)) continue;

        // Copiar estilo desde la plantilla
        try { $ws->duplicateStyle($ws->getStyle($col . $plant), $col . $nuevaFila); } catch (\Throwable $e) {}

        // Intentar heredar validación (dropdown) de la plantilla
        try {
            $dv = $ws->getCell($col . $plant)->getDataValidation();
            if ($dv && $dv->getType() !== '') $ws->getCell($col . $nuevaFila)->setDataValidation(clone $dv);
        } catch (\Throwable $e) {}

        if (in_array($col, $CFG['cols_formula'], true)) {
            $f = $ws->getCell($col . $plant)->getValue();
            if (is_string($f) && strlen($f) && $f[0] === '=')
                $ws->setCellValue($col . $nuevaFila, ajustar_formula($f, $plant, $nuevaFila));
            continue;
        }

        if (array_key_exists($col, $campos)) {
            escribir_celda($ws, $col, $nuevaFila, $campos[$col]);
        }
    }

    // Alto de fila igual a la plantilla
    try { $ws->getRowDimension($nuevaFila)->setRowHeight($ws->getRowDimension($plant)->getRowHeight()); } catch (\Throwable $e) {}

    // 3) Guardar (tmp + rename)
    $tmp = $CFG['ruta_excel'] . '.tmp';
    IOFactory::createWriter($ss, 'Xlsx')->save($tmp);
    if (!rename($tmp, $CFG['ruta_excel'])) throw new Exception('No se pudo reemplazar el archivo');

    $folio = limpiar($campos['C'] ?? ''); $tarea = limpiar($campos['D'] ?? '');
    log_accion('AGREGAR', "#=$nuevoNum FOLIO=\"$folio\" TAREA=\"$tarea\" FILA=$nuevaFila");
    $resp = ['ok' => true, 'num' => $nuevoNum];

} catch (\Throwable $e) {
    log_debug('ERROR agregar: ' . $e->getMessage());
    $resp = ['ok' => false, 'msg' => $e->getMessage()];
} finally {
    if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
}
echo json_encode($resp);