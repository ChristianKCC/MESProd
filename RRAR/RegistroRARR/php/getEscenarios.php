<?php
/* ============================================================================
   ENDPOINT: Escenarios de riesgo de una máquina (Tab 1, punto 10)
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
            e.IdEscenario,
            r.Maquina,
            ISNULL(r.SeccionEquipo,'-') AS SeccionEquipo,
            c.Descripcion               AS CategoriaPeligro,
            e.DescripcionPeligro,
            ISNULL(e.EscenarioRiesgo,'-') AS EscenarioRiesgo,
            s.Descripcion               AS Severidad,
            p.Descripcion               AS Probabilidad,
            f.Descripcion               AS Frecuencia,
            ISNULL(e.PersonalExpuesto,'-') AS PersonalExpuesto,
            e.Calificacion,
            e.NivelRiesgo,
            e.FechaRegistro
        FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo e
        INNER JOIN TLX002MXDB.dbo.Seg_RARR r               ON r.IdRARR = e.IdRARR
        INNER JOIN TLX002MXDB.dbo.Seg_CatCategoriaPeligro c ON c.IdCategoria = e.IdCategoriaPeligro
        INNER JOIN TLX002MXDB.dbo.Seg_CatSeveridad s        ON s.IdSeveridad = e.IdSeveridad
        INNER JOIN TLX002MXDB.dbo.Seg_CatProbabilidad p     ON p.IdProbabilidad = e.IdProbabilidad
        INNER JOIN TLX002MXDB.dbo.Seg_CatFrecuencia f       ON f.IdFrecuencia = e.IdFrecuencia
        WHERE r.IdMaquina = ? AND e.Activo = 1
        ORDER BY e.FechaRegistro DESC";

$filas = ejecutarQuery($conn, $sql, [$idMaquina]);
sqlsrv_close($conn);
responderOK($filas);
