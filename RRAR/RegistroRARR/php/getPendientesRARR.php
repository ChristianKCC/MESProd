<?php
/* ============================================================================
   ENDPOINT: Pendientes de un RARR por escenario (Paso 2 y Paso 3)
   GET: ?idEquipo=
   Devuelve, por escenario: su avance de contención (Paso 2) y el estatus
   de su plan de acción (Paso 3), con banderas de concluido.
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
    responderOK([
        "paso2" => [],
        "paso3" => [],
        "resumen" => [
            "p2Pendientes" => 0,
            "p2Total" => 0,
            "p3Pendientes" => 0,
            "p3Total" => 0
        ]
    ]);
}
$idRARR = (int) $rarr[0]['IdRARR'];

/* Paso 2: contención por escenario */
$paso2 = ejecutarQuery(
    $conn,
    "SELECT ev.IdEscenario, e.EscenarioRiesgo, ev.AccionesPropuestas,
            ISNULL(ev.PorcentajeAvance,0) AS Avance,
            ISNULL(ev.NombreResponsable,'-') AS Responsable,
            CASE WHEN ISNULL(ev.PorcentajeAvance,0) >= 100 THEN 1 ELSE 0 END AS Concluido
     FROM TLX002MXDB.dbo.Seg_EvaluacionRARR ev
     INNER JOIN TLX002MXDB.dbo.Seg_EscenarioRiesgo e ON e.IdEscenario = ev.IdEscenario
     WHERE ev.IdRARR = ? AND ev.Activo = 1
     ORDER BY Concluido, ev.IdEscenario",
    [$idRARR]
);

/* Paso 3: plan de acción por escenario (estatus 3 = Concluido) */
$paso3 = ejecutarQuery(
    $conn,
    "SELECT s.IdEscenario, e.EscenarioRiesgo,
            s.Descripcion AS Accion,
            ISNULL(es.Descripcion,'Pendiente') AS Estatus,
            ISNULL(s.Responsable,'-') AS Responsable,
            CASE WHEN s.IdEstatus = 3 THEN 1 ELSE 0 END AS Concluido
     FROM TLX002MXDB.dbo.Seg_SeguimientoControl s
     INNER JOIN TLX002MXDB.dbo.Seg_EscenarioRiesgo e ON e.IdEscenario = s.IdEscenario
     LEFT  JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = s.IdEstatus
     WHERE s.IdRARR = ? AND s.Activo = 1
     ORDER BY Concluido, s.IdEscenario",
    [$idRARR]
);

sqlsrv_close($conn);

$p2Pend = count(array_filter($paso2, fn($x) => (int) $x['Concluido'] === 0));
$p3Pend = count(array_filter($paso3, fn($x) => (int) $x['Concluido'] === 0));

responderOK([
    "paso2" => $paso2,
    "paso3" => $paso3,
    "resumen" => [
        "p2Pendientes" => $p2Pend,
        "p2Total" => count($paso2),
        "p3Pendientes" => $p3Pend,
        "p3Total" => count($paso3),
        "totalPendientes" => $p2Pend + $p3Pend
    ]
]);