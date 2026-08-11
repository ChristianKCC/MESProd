<?php
session_start();
header('Content-Type: application/json');

define('CSV_PATH', __DIR__ . '/./solicitudes_te/solicitudes.csv');
define('CSV_CAMPOS', ['NoEmp','Folio','Fecha','HoraI','HoraF','Motivos','Maquina','Razon','TurnoSeleccionado','HoraFinalSinMargen','HoraFinalConMargen','Estado']);

$data = json_decode(file_get_contents('php://input'), true);
$ibm = trim($data['NoEmp'] ?? '');
$fecha_solicitada = trim($data['Fecha'] ?? '');
$nuevo_estado = trim($data['Estado'] ?? '');

$estados_validos = ['Aprobado', 'Rechazado', 'Pendiente'];

if(!$ibm || !$fecha_solicitada || !in_array($nuevo_estado, $estados_validos)){
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

if (!file_exists(CSV_PATH)){
    echo json_encode(['success' => false, 'message' => 'No hay archivo CSV cargado']);
    exit;
}


// Leer todos los registros
$f = fopen(CSV_PATH, 'r');
fgetcsv($f);
$registros = [];
while ($fila = fgetcsv($f)){
    if (count($fila) === count(CSV_CAMPOS)) {
        $registros[] = array_combine(CSV_CAMPOS, $fila);
    }
}
fclose($f);

// Actualizacion de datos segun el elemento seleccionado que coincida
$actualizado = false;
foreach($registros as &$r) {
    if ($r['ibm'] === $ibm && $r['fecha'] === $fecha_solicitada && $r['Estado'] === 'Pendiente'){
        $r['Estado'] = $nuevo_estado;
        $actualizado = true;
        break;
    }
}

unset($r);

if(!$actualizado){
    echo json_encode(['success' => false, 'message' => 'No se encontro una solicitud pendiente']);
    exit;
}

// Rescribir CVS
$f = fopen(CSV_PATH, 'w');
fputcsv($f, CSV_CAMPOS);
foreach($registros as $r){
    fputcsv($f, array_values($r));
}
fclose($f);

echo json_encode(['success' => true, 'message' => "Solicitud actualizada a: $nuevo_estado"]);
?>