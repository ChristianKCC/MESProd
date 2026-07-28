<?php
require __DIR__ . '/../../php/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

function ci($letra){ return Coordinate::columnIndexFromString($letra); }         // letra -> indice
function cl($idx){ return Coordinate::stringFromColumnIndex($idx); }             // indice -> letra

function limpiar($t){ return trim(preg_replace('/\s+/', ' ', (string)$t)); }

// Expande un rango de columnas: 'S','Y' => ['S','T','U','V','W','X','Y']
function cols_rango($ini, $fin){
    $out = [];
    for($i = ci($ini); $i <= ci($fin); $i++) $out[] = cl($i);
    return $out;
}

// Mapa columna(IO Puesto) -> rango en hoja 'Listas Desplegables'
function construir_dropdowns(){
    $map = [
        'E'=>'B5:B6','F'=>'C5:C17','I'=>'F5:F18','J'=>'G5:G13','K'=>'H5:H11',
        'L'=>'I5:I14','M'=>'J5:J11','N'=>'K5:K12',
        'BL'=>'L5:L11','BN'=>'O5:O8','BP'=>'Q5:Q10','BR'=>'S5:S9','BW'=>'U5:U12','CW'=>'X5:X6',
        // EPP específicos
        'CG'=>'Z5:Z8','CH'=>'AB5:AB7','CI'=>'AA5:AA10','CJ'=>'AC5:AC7','CK'=>'AD5:AD10',
        'CL'=>'AF5:AF6','CM'=>'AE5:AE10','CN'=>'AG5:AG12','CO'=>'AH5:AH10','CP'=>'AI5:AI9',
    ];
    // Grupos por rango de columnas
    foreach(cols_rango('S','Z')  as $c) $map[$c] = 'V5:V6';   // Si / No
    foreach(['AA','AB','AC','AH','AI','AJ','AK','AL','AO'] as $c) $map[$c] = 'W5:W6'; // Requerido / No Req.
    foreach(['AD','AE','AF','AG','AM','AN','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','BI','BJ','BK'] as $c)
        $map[$c] = 'X5:X7'; // Si / No / No Aplica
    foreach(['AZ','BA','BB','BC','BD','BE','BF','BG','BH','BX','BY','BZ','CA','CB','CC','CD','CE','CF'] as $c)
        $map[$c] = 'Y5:Y6'; // X / -
    return $map;
}

// Lee los valores no vacíos de un rango de la hoja de listas
function leer_lista($wsListas, $rango){
    $filas = $wsListas->rangeToArray($rango, null, false, false);
    $vals = [];
    foreach($filas as $f){
        foreach($f as $v){
            $v = limpiar($v);
            if($v !== '') $vals[] = $v;
        }
    }
    return $vals;
}

function cargar_spreadsheet($ruta){
    $reader = IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(false);   // importacion de estilos, fórmulas y validaciones
    return $reader->load($ruta);
}

function ultima_fila_datos($ws, $colNum, $ini){
    $alto = $ws->getHighestDataRow();
    $last = $ini - 1;
    for($r = $ini; $r <= $alto; $r++){
        $v = $ws->getCell($colNum . $r)->getValue();
        if($v !== null && trim((string)$v) !== '') $last = $r;
    }
    return $last;
}

// Ajusta las referencias de fila de una fórmula copiada de la plantilla
function ajustar_formula($f, $filaPlantilla, $filaNueva){
    return preg_replace_callback('/(\$?[A-Z]{1,3}\$?)(\d+)/', function($m) use ($filaPlantilla, $filaNueva){
        return ((int)$m[2] === $filaPlantilla) ? $m[1] . $filaNueva : $m[0];
    }, $f);
}

// ===== Logs =====
function _dir_logs(){ global $CFG; if(!is_dir($CFG['dir_logs'])) @mkdir($CFG['dir_logs'], 0775, true); return $CFG['dir_logs']; }
function ibm_actual(){ return isset($_SESSION['IBM']) && $_SESSION['IBM'] !== '' ? (string)$_SESSION['IBM'] : 'DESCONOCIDO'; }

function log_debug($msg){
    $f = _dir_logs() . '/app_' . date('Y-m-d') . '.log';
    @file_put_contents($f, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}
function log_accion($accion, $detalle){
    $f = _dir_logs() . '/acciones.log';
    $linea = sprintf('[%s] IBM=%s ACCION=%s %s', date('Y-m-d H:i:s'), ibm_actual(), $accion, $detalle);
    @file_put_contents($f, $linea . PHP_EOL, FILE_APPEND);
}

// Escribe una celda respetando tipo (evita inyección de fórmulas en texto)
function escribir_celda($ws, $col, $fila, $val){
    global $CFG;
    $coord = $col . $fila;
    if(in_array($col, $CFG['cols_fecha'], true)){
        if(trim((string)$val) === ''){ $ws->setCellValue($coord, null); return; }
        try {
            $serial = XlsDate::PHPToExcel(new DateTime($val));
            $ws->setCellValue($coord, $serial);
            $ws->getStyle($coord)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        } catch (\Throwable $e){
            $ws->setCellValueExplicit($coord, (string)$val, DataType::TYPE_STRING);
        }
    } elseif(in_array($col, $CFG['cols_numero'], true)){
        $ws->setCellValueExplicit($coord, is_numeric($val) ? $val + 0 : 0, DataType::TYPE_NUMERIC);
    } else {
        $ws->setCellValueExplicit($coord, (string)$val, DataType::TYPE_STRING); // fuerza texto
    }
}