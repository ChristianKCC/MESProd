<?php
/* ============================================================================
   ENDPOINT: Solo el número de reportes nuevos de un RARR (badge del Tab 4)
   GET: ?idEquipo=
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

/* Nada de caché: este dato cambia a cada rato */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$idEquipo = limpiar($_GET['idEquipo'] ?? '');
if ($idEquipo === '') {
    responderError("ID de equipo no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$res = ejecutarQuery(
    $conn,
    "SELECT
        (SELECT COUNT(*)
           FROM TLX002MXDB.dbo.Seg_FeedbackRARR f
           INNER JOIN TLX002MXDB.dbo.Seg_SeguimientoControl s ON s.IdSeguimiento = f.IdSeguimiento
           INNER JOIN TLX002MXDB.dbo.Seg_RARR r ON r.IdRARR = s.IdRARR
          WHERE r.IdEquipo = ? AND f.Activo = 1 AND s.Activo = 1
            AND f.Tipo = 'reporte' AND f.Leido = 0) AS Nuevos,
        (SELECT COUNT(*)
           FROM TLX002MXDB.dbo.Seg_SeguimientoControl s2
           INNER JOIN TLX002MXDB.dbo.Seg_RARR r2 ON r2.IdRARR = s2.IdRARR
          WHERE r2.IdEquipo = ? AND s2.Activo = 1) AS Asignaciones",
    [$idEquipo, $idEquipo]
);

sqlsrv_close($conn);
responderOK([
    "nuevos" => (int) ($res[0]['Nuevos'] ?? 0),
    "asignaciones" => (int) ($res[0]['Asignaciones'] ?? 0)
]);