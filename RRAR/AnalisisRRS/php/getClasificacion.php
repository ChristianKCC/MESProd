<?php
/* ============================================================================
   ENDPOINT: Clasificación + indicadores del panel (Reporte)
   Tabla azul e indicadores tomados del PASO 2:
     - tabla-calificación: conteo por NivelRiesgoP2 de los escenarios del depto
     - kpiPorcentaje:      (Aceptable + Bajo) / total escenarios P2 del depto
     - kpiPromedio:        AVG(MarcadorGuardas) de los RARR del depto (Paso 2)
     - kpiRiesgoTotal:     año actual
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idDepartamento = enteroONull($_GET['idDepartamento'] ?? null);
if ($idDepartamento === null) {
    responderError("Departamento no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

/* Conteo por nivel del PASO 2 (NivelRiesgoP2). Incluye genéricos, que viven
   en la misma tabla. Solo cuenta los que ya tienen Paso 2 calculado. */
$sql = "SELECT
            SUM(CASE WHEN e.NivelRiesgoP2 = 'Aceptable'   THEN 1 ELSE 0 END) AS Aceptable,
            SUM(CASE WHEN e.NivelRiesgoP2 = 'Bajo'        THEN 1 ELSE 0 END) AS Bajo,
            SUM(CASE WHEN e.NivelRiesgoP2 = 'Alto'        THEN 1 ELSE 0 END) AS Alto,
            SUM(CASE WHEN e.NivelRiesgoP2 = 'Inaceptable' THEN 1 ELSE 0 END) AS Inaceptable
        FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo e
        INNER JOIN TLX002MXDB.dbo.Seg_RARR r ON r.IdRARR = e.IdRARR
        WHERE r.IdDepartamento = ? AND e.Activo = 1
          AND e.NivelRiesgoP2 IS NOT NULL";
$res = ejecutarQuery($conn, $sql, [$idDepartamento]);

$aceptable = (int) ($res[0]['Aceptable'] ?? 0);
$bajo = (int) ($res[0]['Bajo'] ?? 0);
$alto = (int) ($res[0]['Alto'] ?? 0);
$inaceptable = (int) ($res[0]['Inaceptable'] ?? 0);
$totalEsc = $aceptable + $bajo + $alto + $inaceptable;

/* kpiPorcentaje = (Aceptable + Bajo) / total escenarios P2 */
$pctResidual = $totalEsc > 0 ? round((($aceptable + $bajo) / $totalEsc) * 100, 2) : 0;

/* kpiPromedio = promedio del Marcador del Paso 2 (MarcadorGuardas) por depto */
$sqlProm = "SELECT AVG(CAST(MarcadorGuardas AS FLOAT)) AS PromedioP2
            FROM TLX002MXDB.dbo.Seg_RARR
            WHERE IdDepartamento = ? AND MarcadorGuardas IS NOT NULL";
$prom = ejecutarQuery($conn, $sqlProm, [$idDepartamento]);

sqlsrv_close($conn);

responderOK([
    "aceptable" => $aceptable,
    "bajo" => $bajo,
    "alto" => $alto,
    "inaceptable" => $inaceptable,
    "indicadores" => [
        "pctAreasResidual" => $pctResidual,
        "promedioMarcador" => $prom[0]['PromedioP2'] !== null ? round((float) $prom[0]['PromedioP2'], 2) : null,
        "riesgoTotal" => (int) date('Y')
    ]
]);