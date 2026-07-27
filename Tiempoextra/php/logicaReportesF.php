<?php

require_once(__DIR__ . "/../../Session/seguridad.php");
require_once(__DIR__ . "/../../Vacaciones/php/vacacionesLogistica.php");

// IBM del usuario en sesión
$ibmSesion = $_SESSION["ibm"] ?? null;

// Obtener lista de supervisores
$listaSupervisores = obtenerSupervisoresIBM();
$ibmPermitidos = [60040, 58998, 22622, 51947, 55268, 53224];

// Validar acceso
if (!$ibmSesion ||(!in_array($ibmSesion, $listaSupervisores) && !in_array($ibmSesion, $ibmPermitidos))) {
    // No está autorizado → redirigir
    header("Location:../../index/index.php");
    exit;
}

require_once(__DIR__ . "/../../conexion.php");
$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX009MXDB");

// Cargar departamentos para el combo del filtro
$sqlDep = "SELECT NoDepto,NombreDepto FROM tblDepartamentos WHERE Filtro=1 ORDER BY NombreDepto ASC";
$resDep = sqlsrv_query($conn, $sqlDep);
$departamentos = [];
if ($resDep) {
    while ($d = sqlsrv_fetch_array($resDep)) {
        $departamentos[] = $d;
    }
}

// Si llega aquí, es supervisor autorizado
require_once(__DIR__ . "/../../index/header.php");
?>