<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

header('Content-type: Aplicattion/json');

// Validaciones para verificar si la persona logueada tiene una firma registrada

// Verificacion  de que exista una sesion activa
if(!isset($_SESSION['ibm'])){
    echo json_encode(['success' => false, 'message' => 'Sin sesion activa']);
    exit;
}

// Recuperacion de los datos a trabajar con la sesion
$num_empleado = $_SESSION['ibm'];

// Ruta de la firma
$ruta_firma = '../firmas/' . $num_empleado. '.JPG';

// Segunda ruta para tomar directo desde las firmas creadas en la firma digital
//$ruta_firma = '../../FirmaDigital/firmas/' . $num_empleado. '.png';

// firma_existente y validaciones de distintos formatos
$extensiones = ['png', 'jpg', 'jpeg'];
$firma_existente = false;

// Verificacion de que la firma exista
foreach ($extensiones as $ext){
    if (file_exists('../firmas/' . $num_empleado. '.' . $ext)){
        // Validar que la firma existe al hacer la busqueda por extension con base en el nombre
        $firma_existente = true;
        break;
    }
}

// Devolucion de datos para comprobacion de la firma
echo json_encode([
    'success' => $firma_existente,
    'num_empleado' => $num_empleado
]);

?>