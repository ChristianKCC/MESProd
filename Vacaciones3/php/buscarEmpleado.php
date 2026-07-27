<?php
require_once(__DIR__ . "/./vacacionesLogistica.php");
require_once(__DIR__ . "/../config.php");

$ibm = isset($_GET['ibm']) ? trim($_GET['ibm']) : '';
$nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';
$modo = isset($_GET['modo']) ? trim($_GET['modo']) : '';

if ($modo === 'propio') {
    $empleado = buscarEmpleado($ibmSession);
} else {
    if ($ibm !== '') {
        if (validarSupervisor($ibm, '', $ibmSession)) {
            $empleado = busquedaEmpledoxSupervisor($ibm, '');
        } else {
            $empleado = null;
        }
    } elseif ($nombre !== '') {
        if (validarSupervisor('', $nombre, $ibmSession)) {
            $empleado = busquedaEmpledoxSupervisor('', $nombre);
        } else {
            $empleado = null;
        }
    } else {
        $empleado = null;
    }
}

if ($empleado) {
    $empleado["NOMBRE"] = str_replace(',', ' ', trim($empleado[COL_NOMBRE] ?? ''));
    $empleado["F_INGRESO"] = $empleado[COL_FINGRESO];
    $empleado["ANTIGUEDAD"] = calcularAntiguedad($empleado[COL_FINGRESO]);

    $fechaIngreso = str_replace('-', '/', $empleado[COL_FINGRESO]);
    $partes = explode('/', $fechaIngreso);
    if (count($partes) === 3) {
        [$mes, $dia, $anio] = $partes;
        $proximo = mktime(0,0,0,(int)$mes,(int)$dia,date("Y"));
        $empleado["ANIVERSARIO"] = calcularAniversario($empleado[COL_FINGRESO]);
    }    

    $empleado["VAC_DISPONIBLES"] = (int)($empleado[COL_VAC] ?? 0);

    $historial = [];
    if (file_exists(HISTORIAL_FILE)) {
        $handle = fopen(HISTORIAL_FILE, "r");
        $headers = fgetcsv($handle);
        while (($line = fgetcsv($handle)) !== false) {
            if (trim($line[0]) === (string)$empleado[COL_IBM]) {
                $historial[] = $line;
            }
        }
        fclose($handle);
    }
    $empleado["HISTORIAL"] = $historial;
}

header('Content-Type: application/json');
echo json_encode($empleado ?? []);