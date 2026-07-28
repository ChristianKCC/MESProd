<?php
/* ============================================================================
   ENDPOINT: Eliminar TODO el RARR de un IdEquipo (Tab 4)
   Borra en transacción: escenarios, evaluaciones, soluciones, acciones y
   el maestro.
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
    "SELECT IdRARR FROM TLX002MXDB.dbo.Seg_RARR WHERE IdEquipo = ?",
    [$idEquipo]
);
if (count($rarr) === 0) {
    sqlsrv_close($conn);
    responderError("No hay RARR registrado para este equipo", 404);
}

if (sqlsrv_begin_transaction($conn) === false) {
    sqlsrv_close($conn);
    responderError("No se pudo iniciar la transacción", 500, sqlsrv_errors());
}

function borrarTx($conn, $sql, $params)
{
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        sqlsrv_rollback($conn);
        sqlsrv_close($conn);
        responderError("Error al eliminar el RARR", 500, sqlsrv_errors());
    }
    sqlsrv_free_stmt($stmt);
}

foreach ($rarr as $r) {
    $id = (int) $r['IdRARR'];
    /* Hijas primero, maestro al final */
    borrarTx($conn, "DELETE FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo    WHERE IdRARR = ?", [$id]);
    borrarTx($conn, "DELETE FROM TLX002MXDB.dbo.Seg_EvaluacionRARR     WHERE IdRARR = ?", [$id]);
    borrarTx($conn, "DELETE FROM TLX002MXDB.dbo.Seg_AccionMejora       WHERE IdRARR = ?", [$id]);
    borrarTx($conn, "DELETE FROM TLX002MXDB.dbo.Seg_SeguimientoControl WHERE IdRARR = ?", [$id]);
    borrarTx($conn, "DELETE FROM TLX002MXDB.dbo.Seg_RARR               WHERE IdRARR = ?", [$id]);
}

if (sqlsrv_commit($conn) === false) {
    sqlsrv_rollback($conn);
    sqlsrv_close($conn);
    responderError("No se pudo confirmar la eliminación", 500, sqlsrv_errors());
}
sqlsrv_close($conn);

responderOK(["idEquipo" => $idEquipo], "RARR eliminado correctamente");

// registrarLog($conn, 'Eliminacion', ['modulo' => 'RegistroRARR', 'entidad' => 'RARR', 'idEquipo' => $idEquipo]);