<?php
/* ============================================================================
   ENDPOINT: Peligros genéricos (valores por defecto de los 3 pasos)
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$sql = "SELECT
            IdGenerico, Orden, EscenarioRiesgo            
        FROM TLX002MXDB.dbo.Seg_CatPeligroGenerico
        WHERE Activo = 1
        ORDER BY Orden";

$filas = ejecutarQuery($conn, $sql);
sqlsrv_close($conn);
responderOK($filas);