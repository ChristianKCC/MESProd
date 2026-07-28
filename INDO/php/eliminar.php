<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/config.php';
require_once __DIR__ . '/../utils/funciones.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$num = isset($_POST['num']) ? (int)$_POST['num'] : 0;
if ($num <= 0) { echo json_encode(['ok'=>false,'msg'=>'# inválido']); exit; }

$lock = fopen($CFG['dir_logs'] . '/.lock', 'c');
if ($lock) flock($lock, LOCK_EX);
$resp = ['ok'=>false,'msg'=>''];

try {
    $ss = cargar_spreadsheet($CFG['ruta_excel']);
    $ws = $ss->getSheetByName($CFG['hoja']);
    $ini    = $CFG['fila_datos_ini'];
    $colNum = $CFG['col_num'];
    $ultima = ultima_fila_datos($ws, $colNum, $ini);

    // Buscar la fila cuyo # == num
    $filaObj = 0;
    for ($r = $ini; $r <= $ultima; $r++) {
        if ((int)$ws->getCell($colNum . $r)->getValue() === $num) { $filaObj = $r; break; }
    }
    if (!$filaObj) throw new Exception("No se encontró el # $num");

    $folio = limpiar($ws->getCell('C' . $filaObj)->getValue());
    $tarea = limpiar($ws->getCell('D' . $filaObj)->getValue());

    // Eliminar fila (PhpSpreadsheet reajusta fórmulas de las filas de abajo)
    $ws->removeRow($filaObj, 1);

    // Renumerar # para que queden consecutivos
    $ultima2 = ultima_fila_datos($ws, $colNum, $ini);
    $n = 1;
    for ($r = $ini; $r <= $ultima2; $r++) {
        $ws->setCellValueExplicit($colNum . $r, $n++, DataType::TYPE_NUMERIC);
    }

    $tmp = $CFG['ruta_excel'] . '.tmp';
    IOFactory::createWriter($ss, 'Xlsx')->save($tmp);
    if (!rename($tmp, $CFG['ruta_excel'])) throw new Exception('No se pudo reemplazar el archivo');

    log_accion('ELIMINAR', "#=$num FOLIO=\"$folio\" TAREA=\"$tarea\" FILA=$filaObj");
    $resp = ['ok'=>true];

} catch (\Throwable $e) {
    log_debug('ERROR eliminar: ' . $e->getMessage());
    $resp = ['ok'=>false,'msg'=>$e->getMessage()];
} finally {
    if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
}
echo json_encode($resp);