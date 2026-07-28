<?php
/* ============================================================================
   ENDPOINT: Secciones del RARR de una máquina (Tab 1)
   Vienen de la tabla propia Seg_SeccionMaquina, con su IdEquipo ya generado.
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idMaquina = enteroONull($_GET['idMaquina'] ?? null);
if ($idMaquina === null) {
    responderError("Máquina no válida");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$sql = "SELECT
            IdSeccion,
            RTRIM(NombreSeccion) AS Seccion,
            IdEquipo,
            RTRIM(Departamento)  AS Departamento
        FROM TLX002MXDB.dbo.Seg_SeccionMaquina
        WHERE NoMaquina = ? AND Activo = 1
        ORDER BY IdSeccion";

$filas = ejecutarQuery($conn, $sql, [$idMaquina]);
sqlsrv_close($conn);
responderOK($filas);