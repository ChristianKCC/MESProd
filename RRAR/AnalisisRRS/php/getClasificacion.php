<?php
/* ============================================================================
   ENDPOINT: Clasificación + indicadores del panel (Reporte)
   Cada RARR aporta UN dato: su MarcadorGuardas (marcador final del Paso 2),
   clasificado con los cortes <=5 / <=50 / <=500 / >500.
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

$sql = "SELECT
            SUM(CASE WHEN MarcadorGuardas <= 5                              THEN 1 ELSE 0 END) AS Aceptable,
            SUM(CASE WHEN MarcadorGuardas >  5   AND MarcadorGuardas <= 50  THEN 1 ELSE 0 END) AS Bajo,
            SUM(CASE WHEN MarcadorGuardas >  50  AND MarcadorGuardas <= 500 THEN 1 ELSE 0 END) AS Alto,
            SUM(CASE WHEN MarcadorGuardas >  500                            THEN 1 ELSE 0 END) AS Inaceptable,
            COUNT(*) AS TotalRARR,
            AVG(CAST(MarcadorGuardas AS FLOAT)) AS PromedioP2
        FROM TLX002MXDB.dbo.Seg_RARR
        WHERE IdDepartamento = ? AND MarcadorGuardas IS NOT NULL";
$res = ejecutarQuery($conn, $sql, [$idDepartamento]);
sqlsrv_close($conn);

$aceptable = (int) ($res[0]['Aceptable'] ?? 0);
$bajo = (int) ($res[0]['Bajo'] ?? 0);
$alto = (int) ($res[0]['Alto'] ?? 0);
$inaceptable = (int) ($res[0]['Inaceptable'] ?? 0);
$total = (int) ($res[0]['TotalRARR'] ?? 0);

/* % de áreas con riesgo residual = las que quedaron en Aceptable o Bajo */
$pct = $total > 0 ? round((($aceptable + $bajo) / $total) * 100, 2) : 0;

responderOK([
    "aceptable" => $aceptable,
    "bajo" => $bajo,
    "alto" => $alto,
    "inaceptable" => $inaceptable,
    "indicadores" => [
        "pctAreasResidual" => $pct,
        "promedioMarcador" => $res[0]['PromedioP2'] !== null ? round((float) $res[0]['PromedioP2'], 2) : null,
        "riesgoTotal" => (int) date('Y')
    ]
]);