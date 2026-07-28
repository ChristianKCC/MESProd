<?php
/* ============================================================================
   ENDPOINT: Concluir el RARR (Tab 4).
   Solo concluye si TODOS los escenarios tienen:
     - Paso 2: PorcentajeAvance = 100
     - Paso 3 (plan de acción, Seg_SeguimientoControl): IdEstatus = 3 (Concluido)
   POST: idEquipo
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idEquipo = limpiar($_POST['idEquipo'] ?? '');
if ($idEquipo === '') {
    responderError("ID de equipo no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$rarr = ejecutarQuery(
    $conn,
    "SELECT IdRARR, ISNULL(Estatus,'Pendiente') AS Estatus
     FROM TLX002MXDB.dbo.Seg_RARR WHERE IdEquipo = ?",
    [$idEquipo]
);
if (count($rarr) === 0) {
    sqlsrv_close($conn);
    responderError("No hay RARR registrado para este equipo", 404);
}
if ($rarr[0]['Estatus'] === 'Concluido') {
    sqlsrv_close($conn);
    responderError("Este RARR ya está concluido");
}
$idRARR = (int) $rarr[0]['IdRARR'];

/* Cuenta pendientes: Paso 2 < 100% y Paso 3 (plan) no concluido */
$pend = ejecutarQuery(
    $conn,
    "SELECT
        (SELECT COUNT(*) FROM TLX002MXDB.dbo.Seg_EvaluacionRARR
          WHERE IdRARR = ? AND Activo = 1 AND ISNULL(PorcentajeAvance,0) < 100) AS Paso2Pend,
        (SELECT COUNT(*) FROM TLX002MXDB.dbo.Seg_SeguimientoControl
          WHERE IdRARR = ? AND Activo = 1 AND ISNULL(IdEstatus,0) <> 3) AS Paso3Pend",
    [$idRARR, $idRARR]
);
$p2 = (int) $pend[0]['Paso2Pend'];
$p3 = (int) $pend[0]['Paso3Pend'];

if ($p2 > 0 || $p3 > 0) {
    sqlsrv_close($conn);
    $msg = "No se puede concluir. Faltan: ";
    $partes = [];
    if ($p2 > 0)
        $partes[] = "$p2 plan(es) de contención (Paso 2) sin llegar al 100%";
    if ($p3 > 0)
        $partes[] = "$p3 plan(es) de acción (Paso 3) sin concluir";
    responderError($msg . implode(" y ", $partes));
}

$stmt = sqlsrv_query(
    $conn,
    "UPDATE TLX002MXDB.dbo.Seg_RARR
     SET Estatus = 'Concluido', FechaConclusion = GETDATE()
     WHERE IdEquipo = ?",
    [$idEquipo]
);
if ($stmt === false) {
    sqlsrv_close($conn);
    responderError("No se pudo concluir el RARR", 500, sqlsrv_errors());
}
sqlsrv_free_stmt($stmt);

if (function_exists('registrarLog')) {
    registrarLog($conn, 'Concluye', [
        'modulo' => 'AnalisisRARR',
        'entidad' => 'RARR',
        'idEquipo' => $idEquipo,
        'idRARR' => $idRARR
    ]);
}

sqlsrv_close($conn);
responderOK(["idEquipo" => $idEquipo], "RARR concluido correctamente");