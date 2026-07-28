<?php
/* ============================================================================
   ENDPOINT: Evaluaciones registradas (Tab 2, tabla inferior)
   Columnas del diseño: Descripción de Guarda Actual | Criterio de Guarda
   Actual | Nivel de Riesgo | Responsable | Fecha Implementación | Progreso
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
            ev.IdEvaluacion,
            r.Maquina,
            ISNULL(ev.Componente,'-')          AS Componente,
            ev.FechaSistema,
            ev.DescripcionHallazgo,
            ev.CriterioGuarda,
            ev.NivelRiesgoActual,
            ISNULL(ev.MedidasMitigacion,'-')   AS MedidasMitigacion,
            ISNULL(ev.AccionesPropuestas,'-')  AS AccionesPropuestas,
            ev.PorcentajeAvance,
            ISNULL(ev.NombreResponsable,'-')   AS NombreResponsable,
            ev.FechaCompromiso
        FROM TLX002MXDB.dbo.Seg_EvaluacionRARR ev
        INNER JOIN TLX002MXDB.dbo.Seg_RARR r ON r.IdRARR = ev.IdRARR
        WHERE r.IdMaquina = ? AND ev.Activo = 1
        ORDER BY ev.FechaRegistro DESC";

$filas = ejecutarQuery($conn, $sql, [$idMaquina]);
sqlsrv_close($conn);
responderOK($filas);
