<?php
require_once("../Session/seguridad.php");

require_once("../index/header.php");
require_once("./config.php");

// Ruta del historial
define('HISTORIAL_FILE', UPLOAD_DIR . "Historial_Solicitudes_Vacaciones.csv");

$solicitudes = [];
if (file_exists(HISTORIAL_FILE)) {
    $handle = fopen(HISTORIAL_FILE, "r");
    $headers = fgetcsv($handle);
    while (($line = fgetcsv($handle)) !== false) {
        $solicitudes[] = $line;
    }
    fclose($handle);
}
$pendientes = 0;
$aprobadas = 0;
$rechazadas = 0;

foreach ($solicitudes as $solicitud) {
    $estatus = trim($solicitud[7]);
    if ($estatus === "Pendiente") $pendientes++;
    elseif ($estatus === "Aprobado") $aprobadas++;
    elseif ($estatus === "Rechazado") $rechazadas++;
}

$ibmFiltro = $_GET['ibm'] ?? '';
$nombreFiltro = $_GET['nombre'] ?? '';
$fechaFiltro = $_GET['fecha'] ?? '';

$solicitudesFiltradas = array_filter($solicitudes, function($s) use ($ibmFiltro, $nombreFiltro, $fechaFiltro) {
    $ok = true;
    if ($ibmFiltro && stripos($s[0], $ibmFiltro) === false) $ok = false;
    if ($nombreFiltro && stripos($s[1], $nombreFiltro) === false) $ok = false;

    if ($fechaFiltro) {
        // Convertir YYYY-MM-DD a m/d/Y
        $fechaFiltroFormateada = date("n/j/Y", strtotime($fechaFiltro));
        if (trim($s[3]) !== $fechaFiltroFormateada) $ok = false;
    }

    return $ok;
});
?>
