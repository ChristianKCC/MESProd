<?php
/* ============================================================================
   ENDPOINT: Secciones del RARR de una máquina (Tab 1)
   Vienen de la tabla propia Seg_SeccionMaquina, con su IdEquipo ya generado.
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

// $sql = "SELECT
//             IdSeccion,
//             RTRIM(NombreSeccion) AS Seccion,
//             IdEquipo,
//             RTRIM(Departamento)  AS Departamento
//         FROM TLX002MXDB.dbo.Seg_SeccionMaquina
//         WHERE NoMaquina = ? AND Activo = 1
//         ORDER BY IdSeccion";

$sql = "SELECT
            s.IdSeccion,
            RTRIM(s.NombreSeccion) AS Seccion,
            s.IdEquipo,
            RTRIM(s.Departamento)  AS Departamento,
            ISNULL(r.Estatus, '') AS EstatusRARR,
            CASE WHEN r.IdRARR IS NULL THEN 0 ELSE 1 END AS TieneRARR,
            ISNULL((SELECT COUNT(*) FROM TLX002MXDB.dbo.Seg_EvaluacionRARR ev
                     WHERE ev.IdRARR = r.IdRARR AND ev.Activo = 1
                       AND ISNULL(ev.PorcentajeAvance,0) < 100), 0)
          + ISNULL((SELECT COUNT(*) FROM TLX002MXDB.dbo.Seg_SeguimientoControl sc
                     WHERE sc.IdRARR = r.IdRARR AND sc.Activo = 1
                       AND ISNULL(sc.IdEstatus,1) <> 3), 0) AS AccionesPendientes
        FROM TLX002MXDB.dbo.Seg_SeccionMaquina s
        OUTER APPLY (SELECT TOP 1 IdRARR, Estatus FROM TLX002MXDB.dbo.Seg_RARR r2
                      WHERE r2.IdEquipo = s.IdEquipo ORDER BY r2.IdRARR DESC) r
        WHERE s.NoMaquina = ? AND s.Activo = 1
        ORDER BY s.IdSeccion";

$filas = ejecutarQuery($conn, $sql, [$idMaquina]);
sqlsrv_close($conn);
responderOK($filas);