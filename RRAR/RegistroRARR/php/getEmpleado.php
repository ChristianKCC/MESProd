<?php
/* ============================================================================
   ENDPOINT: Nombre del empleado a partir de su IBM (tabs 2 y 3)
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$noEmp = limpiar($_GET['noEmp'] ?? '');
if ($noEmp === '') {
    responderError("IBM no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX032MXDB");

$sql = "SELECT TOP 1 RTRIM(NoEmp) AS NoEmp, RTRIM(Nombre) AS Nombre
        FROM TLX032MXDB.dbo.tblEmpleados
        WHERE RTRIM(NoEmp) = ?";

$filas = ejecutarQuery($conn, $sql, [$noEmp]);
sqlsrv_close($conn);

if (count($filas) === 0) {
    responderError("No se encontró un empleado con ese IBM", 404);
}
responderOK($filas[0]);