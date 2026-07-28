<?php
/* ============================================================================
   ENDPOINT: Departamentos del Reporte
   Solo los que aplican al RARR: Filtro = 1 y no obsoletos (TLX009MXDB).
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX009MXDB");

$sql = "SELECT
            NoDepto              AS id,
            RTRIM(NombreDepto)   AS nombre
        FROM TLX009MXDB.dbo.tblDepartamentos
        WHERE ISNULL(Filtro, 0) <> 0
          AND ISNULL(DepartamentoObsoleto, 0) = 0
        ORDER BY RTRIM(NombreDepto)";

$filas = ejecutarQuery($conn, $sql);
sqlsrv_close($conn);
responderOK($filas);