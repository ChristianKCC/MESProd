<?php
/* ============================================================================
   ENDPOINT: Feedback recibido de un RARR (Tab 4)
   GET: ?idEquipo=
   Devuelve las asignaciones del plan de acción con su hilo de comentarios.
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idEquipo = limpiar($_GET['idEquipo'] ?? '');
if ($idEquipo === '') {
    responderError("ID de equipo no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$rarr = ejecutarQuery(
    $conn,
    "SELECT TOP 1 IdRARR FROM TLX002MXDB.dbo.Seg_RARR WHERE IdEquipo = ? ORDER BY IdRARR DESC",
    [$idEquipo]
);
if (count($rarr) === 0) {
    sqlsrv_close($conn);
    responderOK(["asignaciones" => [], "nuevos" => 0]);
}
$idRARR = (int) $rarr[0]['IdRARR'];

/* Asignaciones del plan de acción con su conteo de reportes */
$asignaciones = ejecutarQuery(
    $conn,
    "SELECT s.IdSeguimiento, s.IdEscenario, e.EscenarioRiesgo,
            s.Descripcion AS Accion,
            ISNULL(s.Responsable,'-') AS Responsable,
            ISNULL(s.IbmResponsable,'') AS Ibm,
            s.FechaImplementacion, ISNULL(s.IdEstatus,1) AS IdEstatus,
            ISNULL(es.Descripcion,'Pendiente') AS Estatus,
            (SELECT COUNT(*) FROM TLX002MXDB.dbo.Seg_FeedbackRARR f
              WHERE f.IdSeguimiento = s.IdSeguimiento AND f.Activo = 1) AS Reportes,
            (SELECT COUNT(*) FROM TLX002MXDB.dbo.Seg_FeedbackRARR f
              WHERE f.IdSeguimiento = s.IdSeguimiento AND f.Activo = 1
                AND f.Tipo = 'reporte' AND f.Leido = 0) AS NoLeidos
     FROM TLX002MXDB.dbo.Seg_SeguimientoControl s
     INNER JOIN TLX002MXDB.dbo.Seg_EscenarioRiesgo e ON e.IdEscenario = s.IdEscenario
     LEFT  JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = s.IdEstatus
     WHERE s.IdRARR = ? AND s.Activo = 1
     ORDER BY s.IdSeguimiento",
    [$idRARR]
);

/* Hilo completo del RARR */
$hilos = ejecutarQuery(
    $conn,
    "SELECT f.IdFeedback, f.IdSeguimiento, f.Comentario, f.Ibm, f.Tipo,
            f.NombreArchivo, f.FechaRegistro
     FROM TLX002MXDB.dbo.Seg_FeedbackRARR f
     WHERE f.IdRARR = ? AND f.Activo = 1
     ORDER BY f.FechaRegistro",
    [$idRARR]
);

sqlsrv_close($conn);

/* Agrupa el hilo por asignación */
/* Formatea fechas del hilo (ejecutarQuery ya las convirtió a string) */
foreach ($hilos as &$h) {
    $h['FechaRegistro'] = !empty($h['FechaRegistro'])
        ? date('d/m/Y H:i', strtotime($h['FechaRegistro'])) : '-';
}
unset($h);

/* Agrupa el hilo por asignación */
$porSeg = [];
foreach ($hilos as $h) {
    $porSeg[(int) $h['IdSeguimiento']][] = $h;
}

$nuevos = 0;
foreach ($asignaciones as &$a) {
    $a['hilo'] = $porSeg[(int) $a['IdSeguimiento']] ?? [];
    $a['FechaImplementacion'] = !empty($a['FechaImplementacion'])
        ? date('d/m/Y', strtotime($a['FechaImplementacion']))
        : '-';
    $nuevos += (int) $a['NoLeidos'];
}
unset($a);

responderOK(["asignaciones" => $asignaciones, "nuevos" => $nuevos]);