<?php
/* ============================================================================
   ENDPOINT: RARR de un equipo (panel del Reporte)
   Trae los 3 marcadores, avance, inversión y estatus ya calculados.
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

function formatearFechaMostrar($fecha)
{
  if ($fecha instanceof DateTime) {
    return $fecha->format('d/m/Y');
  } elseif (is_string($fecha)) {
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fecha);
    if ($dt !== false) {
      return $dt->format('d/m/Y');
    }
  }
  return '-';
}

$idEquipo = limpiar($_GET['idEquipo'] ?? '');
if ($idEquipo === '') {
  responderError("ID de equipo no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$sql = "SELECT TOP 1
            r.IdRARR,
            RTRIM(r.Maquina)              AS Maquina,
            ISNULL(r.SeccionEquipo,'-')   AS SeccionEquipo,
            r.IdEquipo,
            ISNULL(r.Estatus,'Pendiente') AS EstatusRARR,
            ISNULL(r.NivelRiesgo,'-')     AS NivelRiesgo,
            r.MarcadorPuro, r.MarcadorGuardas, r.MarcadorIngenieria,
            r.FechaCreacion, r.FechaActualizacion, r.FechaConclusion,            
            (SELECT COUNT(*) FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo e
              WHERE e.IdRARR = r.IdRARR AND e.Activo = 1) AS Escenarios,
            (SELECT AVG(CAST(ev.PorcentajeAvance AS FLOAT))
              FROM TLX002MXDB.dbo.Seg_EvaluacionRARR ev
              WHERE ev.IdRARR = r.IdRARR AND ev.Activo = 1) AS Avance,
            (SELECT SUM(ISNULL(a.InversionEstimada,0))
              FROM TLX002MXDB.dbo.Seg_AccionMejora a
              WHERE a.IdRARR = r.IdRARR AND a.Activo = 1) AS Inversion
        FROM TLX002MXDB.dbo.Seg_RARR r
        WHERE r.IdEquipo = ?
        ORDER BY r.IdRARR DESC";

$filas = ejecutarQuery($conn, $sql, [$idEquipo]);
sqlsrv_close($conn);

if (count($filas) === 0) {
  responderOK([]);
}

$r = $filas[0];
responderOK([
  [
    "IdRARR" => $r['IdRARR'],
    "Maquina" => $r['Maquina'],
    "SeccionEquipo" => $r['SeccionEquipo'],
    "IdEquipo" => $r['IdEquipo'],
    "EstatusRARR" => $r['EstatusRARR'],
    "NivelRiesgo" => $r['NivelRiesgo'],
    "Escenarios" => (int) $r['Escenarios'],
    "marcadorPuro" => $r['MarcadorPuro'] !== null ? (float) $r['MarcadorPuro'] : null,
    "marcadorGuardas" => $r['MarcadorGuardas'] !== null ? (float) $r['MarcadorGuardas'] : null,
    "marcadorIngenieria" => $r['MarcadorIngenieria'] !== null ? (float) $r['MarcadorIngenieria'] : null,
    "avance" => $r['Avance'] !== null ? round((float) $r['Avance'], 2) : 0,
    "inversion" => $r['Inversion'] !== null ? (float) $r['Inversion'] : 0,
    "fechaActualizacion" => formatearFechaMostrar($r['FechaActualizacion'] ?? $r['FechaCreacion']),
  ]
]);