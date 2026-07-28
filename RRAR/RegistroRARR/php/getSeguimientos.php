<?php
/* ============================================================================
   ENDPOINT: Registros del Plan de Acción (Tab 3, bloque B)
   Columnas del diseño: Acción a realizar | Responsable | Fecha objetivo |
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
            s.IdSeguimiento,
            s.Descripcion,
            s.Responsable,
            s.FechaImplementacion,
            ISNULL(es.Descripcion,'Pendiente')  AS Estatus
        FROM TLX002MXDB.dbo.Seg_SeguimientoControl s
        INNER JOIN TLX002MXDB.dbo.Seg_RARR r ON r.IdRARR = s.IdRARR
        LEFT JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = s.IdEstatus
        WHERE r.IdMaquina = ? AND s.Activo = 1
        ORDER BY s.FechaRegistro DESC";

$filas = ejecutarQuery($conn, $sql, [$idMaquina]);
sqlsrv_close($conn);
responderOK($filas);