<?php
/* ============================================================================
   ENDPOINT: Acciones de mejora (Tab 3 bloque A, punto 3 - tabla)
   Cabeceras: Descripción | Fecha implementación | Inversión estimada |
              Estatus | Acciones
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
            a.IdAccion,
            a.Descripcion,
            a.FechaImplementacion,
            a.InversionEstimada,
            ISNULL(es.Descripcion,'Pendiente') AS Estatus
        FROM TLX002MXDB.dbo.Seg_AccionMejora a
        INNER JOIN TLX002MXDB.dbo.Seg_RARR r ON r.IdRARR = a.IdRARR
        LEFT JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = a.IdEstatus
        WHERE r.IdMaquina = ? AND a.Activo = 1
        ORDER BY a.FechaRegistro DESC";

$filas = ejecutarQuery($conn, $sql, [$idMaquina]);
sqlsrv_close($conn);
responderOK($filas);
