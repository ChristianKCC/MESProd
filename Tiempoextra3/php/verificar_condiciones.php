<?php
header('Content-Type: application/json');

$dir_csv = __DIR__ . '/../solicitudes_te';
$ruta_csv = $dir_csv . '/solicitudes.csv';

$cabeceras = ['NoEmp','Folio','Fecha','HoraI','HoraF','Motivos','Maquina','Razon','TurnoSeleccionado','HoraFinalSinMargen','HoraFinalConMargen','Estado'];

$ibm = trim($_GET['ibm'] ?? '');
$fecha = trim($_GET['fecha'] ?? '');

if (!$ibm || !$fecha){
    echo json_encode(['success' => false, 'message' => 'Parametros IBM y fecha son requeridos']);
    exit;
}

if (!file_exists($ruta_csv)){
    echo json_encode(['success' => false, 'message' => 'No hay solicitudes registradas aun']);
    exit;
}

// Leer CSV
$todas = [];
$f = fopen($ruta_csv, 'r');
fgetcsv($f); // Salto de cabecera
while ($fila = fgetcsv($f)){
    if (count($fila) === count($cabeceras)) {
        $todas[] = array_combine($cabeceras, $fila);
    }
}
fclose($f);


// Filtro por IBM + fecha
$solicitudes = array_values(array_filter($todas, function ($r) use ($ibm, $fecha) {
    return $r['NoEmp'] === $ibm && $r['Fecha'] === $fecha;
}));


if (empty($solicitudes)) {
    echo json_encode([
        'success' => false,
        'message' => 'No se encontraron solicitudes para este IBM y fecha',
        'resultados' => []
    ]);
    exit;
}

// Fecha y hora del sistema
$ahora = new DateTime();
$fecha_hoy = $ahora -> format('Y-m-d');
$hora_actual = $ahora -> format('H:i:s');

// Evaluar copndiciones por cada solicitud
$resultados = [];

foreach ($solicitudes as $sol) {
    $fecha_sol = new DateTime($sol["Fecha"]);
    $hora_fin_extra = $sol["HoraFinalConMargen"];
    $estado = $sol["Estado"];

    $item = [
        'solicitud' => $sol,
        'habilitado' => false,
        'razon' => ''
    ];

    // Condición 0: ya procesada
    if (in_array($estado, ['Aprobado','Rechazado'])) {
        $item['razon'] = "Esta solicitud ya fue procesada ($estado).";
        $resultados[] = $item;
        continue;
    }

    // Condición 1: fecha aún no llega
    if ($fecha_sol > $ahora) {
        $item['razon'] = "El día solicitado (".$sol["Fecha"].") aún no ha llegado.";
        $resultados[] = $item;
        continue;
    }

    // Condición 2: es hoy pero la hora aún no llega
    if ($fecha_sol->format('Y-m-d') === $fecha_hoy && $hora_actual < $hora_fin_extra) {
        $item['razon'] = "Aún no es momento de calcular. El botón se habilitará a las $hora_fin_extra hrs.";
        $resultados[] = $item;
        continue;
    }

    // Condiciones cumplidas
    $item['habilitado'] = true;
    $item['razon'] = "Listo para hacer el cálculo.";
    $resultados[] = $item;
}

echo json_encode([
    'success' => true,
    'fecha_hoy' => $fecha_hoy,
    'hora_actual' => $hora_actual,
    'resultados' => $resultados
]);
?>