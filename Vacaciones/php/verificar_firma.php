<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

header('Content-type: application/json');

// Validaciones para verificar si la persona logueada tiene una firma registrada

// Verificación de que exista una sesión activa
if (!isset($_SESSION['ibm'])) {
    echo json_encode(['success' => false, 'message' => 'Sin sesión activa']);
    exit;
}

// Recuperación de los datos a trabajar con la sesión
$num_empleado = $_SESSION['ibm'];

// Lista de extensiones válidas
$extensiones = ['png', 'jpg', 'jpeg'];
$firma_existente = false;
$ruta_firma = "";

// Buscar primero en ../firmas/
foreach ($extensiones as $ext) {
    $ruta = "../firmas/" . $num_empleado . "." . $ext;
    if (file_exists($ruta)) {
        $firma_existente = true;
        $ruta_firma = $ruta;
        break;
    }
}

// Si no se encontró, buscar en ../../FirmaDigital/firmas/
if (!$firma_existente) {
    foreach ($extensiones as $ext) {
        $ruta = "../../FirmaDigital/firmas/" . $num_empleado . "." . $ext;
        if (file_exists($ruta)) {
            $firma_existente = true;
            $ruta_firma = $ruta;
            break;
        }
    }
}

// Devolución de datos para comprobación de la firma
echo json_encode([
    'success' => $firma_existente,
    'num_empleado' => $num_empleado,
    'ruta_firma' => $ruta_firma
]);
