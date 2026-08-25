<?php
/* ============================================================================
   ENDPOINT: Todo el RARR de un IdEquipo (Tab 4 — Análisis de Registros)
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
    "SELECT TOP 1
         IdRARR, IdEquipo,
         RTRIM(Maquina)               AS Maquina,
         ISNULL(SeccionEquipo,'-')    AS SeccionEquipo,
         RTRIM(Departamento)          AS Departamento,
         ISNULL(Estatus,'Pendiente')  AS Estatus,
         ISNULL(NivelRiesgo,'-')      AS NivelRiesgo,
         MarcadorPuro, MarcadorGuardas, MarcadorIngenieria,
         FechaCreacion
     FROM TLX002MXDB.dbo.Seg_RARR
     WHERE IdEquipo = ?
     ORDER BY IdRARR DESC",
    [$idEquipo]
);

if (count($rarr) === 0) {
    sqlsrv_close($conn);
    responderOK(null, "Este equipo no tiene RARR registrado");
}

$idRARR = (int) $rarr[0]['IdRARR'];

/* Escenarios: propios y genéricos */
$escenarios = ejecutarQuery(
    $conn,
    "SELECT
         e.IdEscenario, e.EsGenerico, e.IdGenerico,
         e.IdCategoriaPeligro, e.IdSeveridad, e.IdProbabilidad,
         e.IdFrecuencia, e.IdPersonas,
         e.IdFuente, e.IdMecanismo, e.IdConsecuencia,
         e.IdCriterioGuarda, e.IdMedidaMitigacion,
         ISNULL(cp.Descripcion,'-')  AS CategoriaPeligro,
         e.EscenarioRiesgo,
         ISNULL(s.Descripcion,'-')   AS Severidad,
         ISNULL(p.Descripcion,'-')   AS Probabilidad,
         ISNULL(f.Descripcion,'-')   AS Frecuencia,
         ISNULL(e.PersonalExpuesto,'-') AS PersonalExpuesto,
         e.Calificacion, e.NivelRiesgo,
         e.CalificacionP2, e.NivelRiesgoP2, e.CalificacionP3
     FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo e
     LEFT JOIN TLX002MXDB.dbo.Seg_CatCategoriaPeligro cp ON cp.IdCategoria   = e.IdCategoriaPeligro
     LEFT JOIN TLX002MXDB.dbo.Seg_CatSeveridad        s  ON s.IdSeveridad    = e.IdSeveridad
     LEFT JOIN TLX002MXDB.dbo.Seg_CatProbabilidad     p  ON p.IdProbabilidad = e.IdProbabilidad
     LEFT JOIN TLX002MXDB.dbo.Seg_CatFrecuencia       f  ON f.IdFrecuencia   = e.IdFrecuencia
     WHERE e.IdRARR = ? AND e.Activo = 1
     ORDER BY e.EsGenerico, e.IdEscenario",
    [$idRARR]
);

/* Conteo por nivel (escenarios + genéricos) */
// $conteo = ['Aceptable' => 0, 'Bajo' => 0, 'Alto' => 0, 'Inaceptable' => 0];
// foreach ($escenarios as $e) {
//     if (isset($conteo[$e['NivelRiesgo']])) {
//         $conteo[$e['NivelRiesgo']]++;
//     }
// }

$conteo = ['Aceptable' => 0, 'Bajo' => 0, 'Alto' => 0, 'Inaceptable' => 0];
$conteoP2 = ['Aceptable' => 0, 'Bajo' => 0, 'Alto' => 0, 'Inaceptable' => 0];
foreach ($escenarios as $e) {
    if (isset($conteo[$e['NivelRiesgo']])) {
        $conteo[$e['NivelRiesgo']]++;
    }
    if (isset($conteoP2[$e['NivelRiesgoP2']])) {
        $conteoP2[$e['NivelRiesgoP2']]++;
    }
}


$evaluaciones = ejecutarQuery(
    $conn,
    "SELECT
         ev.IdEvaluacion, ev.IdEscenario, ev.DescripcionHallazgo,
         ev.IdCriterioGuarda, ev.IdSeguridadFuncional,
         ISNULL(cg.Descripcion, ISNULL(ev.CriterioGuarda,'-')) AS CriterioGuarda,
         ISNULL(ev.NivelRiesgoActual,'-')  AS NivelRiesgoActual,
         ev.Calificacion, ev.NivelRiesgo,
         ISNULL(ev.AccionesPropuestas,'-') AS AccionesPropuestas,
         ISNULL(ev.MedidasMitigacion,'-')  AS MedidasMitigacion,
         ev.PorcentajeAvance,
         ISNULL(ev.IbmResponsable,'')      AS IbmResponsable,
         ISNULL(ev.NombreResponsable,'-')  AS NombreResponsable,
         ev.FechaCompromiso
     FROM TLX002MXDB.dbo.Seg_EvaluacionRARR ev
     LEFT JOIN TLX002MXDB.dbo.Seg_CatCriterioGuarda cg ON cg.IdCriterio = ev.IdCriterioGuarda
     WHERE ev.IdRARR = ? AND ev.Activo = 1
     ORDER BY ev.IdEvaluacion",
    [$idRARR]
);

$soluciones = ejecutarQuery(
    $conn,
    "SELECT
         a.IdAccion, a.IdEscenario, a.Descripcion,
         a.IdMedidaMitigacion, a.IdEstatus,
         a.Calificacion, a.NivelRiesgo,
         a.FechaImplementacion, a.InversionEstimada,
         ISNULL(es.Descripcion,'Pendiente') AS Estatus
     FROM TLX002MXDB.dbo.Seg_AccionMejora a
     LEFT JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = a.IdEstatus
     WHERE a.IdRARR = ? AND a.Activo = 1
     ORDER BY a.IdAccion",
    [$idRARR]
);

$acciones = ejecutarQuery(
    $conn,
    "SELECT
         s.IdSeguimiento, s.IdEscenario, s.Descripcion, s.IdEstatus,
         ISNULL(s.IbmResponsable,'') AS IbmResponsable,
         ISNULL(s.Responsable,'-')   AS Responsable,
         s.FechaImplementacion,
         ISNULL(es.Descripcion,'Pendiente') AS Estatus
     FROM TLX002MXDB.dbo.Seg_SeguimientoControl s
     LEFT JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = s.IdEstatus
     WHERE s.IdRARR = ? AND s.Activo = 1
     ORDER BY s.IdSeguimiento",
    [$idRARR]
);

/* Imágenes (solo metadatos; el binario se sirve aparte) */
$imagenes = ejecutarQuery(
    $conn,
    "SELECT IdImagen, Paso, IdEscenario, NombreArchivo, TipoMime
     FROM TLX002MXDB.dbo.Seg_ImagenRARR
     WHERE IdEquipo = ? AND Activo = 1
     ORDER BY Paso",
    [$idEquipo]
);

sqlsrv_close($conn);

$inversionTotal = 0;
foreach ($soluciones as $s) {
    $inversionTotal += (float) ($s['InversionEstimada'] ?? 0);
}
$avance = count($evaluaciones) > 0
    ? round(array_sum(array_column($evaluaciones, 'PorcentajeAvance')) / count($evaluaciones), 2)
    : 0;

responderOK([
    "rarr" => $rarr[0],
    "paso1" => [
        "escenarios" => $escenarios,
        "conteo" => $conteo,
        "marcadorPuro" => $rarr[0]['MarcadorPuro'] !== null ? (float) $rarr[0]['MarcadorPuro'] : null
    ],
    "paso2" => [
        "evaluaciones" => $evaluaciones,
        "avance" => $avance,
        "marcadorGuardas" => $rarr[0]['MarcadorGuardas'] !== null ? (float) $rarr[0]['MarcadorGuardas'] : null,
        "conteo" => $conteoP2,
    ],
    "paso3" => [
        "soluciones" => $soluciones,
        "acciones" => $acciones,
        "inversionTotal" => round($inversionTotal, 2),
        "marcadorIngenieria" => $rarr[0]['MarcadorIngenieria'] !== null ? (float) $rarr[0]['MarcadorIngenieria'] : null
    ],
    "imagenes" => $imagenes
]);

// En GetRARRxEquipo.php, tras leer:
// registrarLog($conn, 'Consulta', ['modulo' => 'AnalisisRARR', 'entidad' => 'RARR', 'idEquipo' => $idEquipo]);