<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/config.php';
require_once __DIR__ . '/../utils/funciones.php';

try {
    $ss = cargar_spreadsheet($CFG['ruta_excel']);
    $ws = $ss->getSheetByName($CFG['hoja']);
    $ini    = $CFG['fila_datos_ini'];
    $colNum = $CFG['col_num'];
    $ultima = ultima_fila_datos($ws, $colNum, $ini);

    $out = [];
    for ($r = $ini; $r <= $ultima; $r++) {
        $num = $ws->getCell($colNum . $r)->getValue();
        if ($num === null || trim((string)$num) === '') continue;
        $out[] = [
            'num'   => (int)$num,
            'folio' => limpiar($ws->getCell('C' . $r)->getValue()),
            'tarea' => limpiar($ws->getCell('D' . $r)->getValue()),
            'tipo'  => limpiar($ws->getCell('E' . $r)->getValue()),
        ];
    }
    echo json_encode($out);
} catch (\Throwable $e) {
    log_debug('ERROR buscar: ' . $e->getMessage());
    echo json_encode([]);
}

