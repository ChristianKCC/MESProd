<?php
/* ============================================================================
   ENDPOINT: Eliminar registro (soft delete, Activo = 0)
   POST: tipo (evaluacion | accion | seguimiento | escenario), id
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$tipo = limpiar($_POST['tipo'] ?? '');
$id = enteroONull($_POST['id'] ?? null);

/* Whitelist tabla/llave*/
$mapa = [
    "evaluacion" => ["tabla" => "dbo.Seg_EvaluacionRARR", "llave" => "IdEvaluacion"],
    "accion" => ["tabla" => "dbo.Seg_AccionMejora", "llave" => "IdAccion"],
    "seguimiento" => ["tabla" => "dbo.Seg_SeguimientoControl", "llave" => "IdSeguimiento"],
    "escenario" => ["tabla" => "dbo.Seg_EscenarioRiesgo", "llave" => "IdEscenario"]
];

if (!isset($mapa[$tipo]) || $id === null) {
    responderError("Tipo de registro o identificador no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$sql = "UPDATE " . $mapa[$tipo]['tabla'] . "
        SET Activo = 0
        WHERE " . $mapa[$tipo]['llave'] . " = ?";
$afectadas = ejecutarAccion($conn, $sql, [$id]);

/* Si se eliminó un escenario, recalcular el nivel global del RARR */
if ($tipo === 'escenario') {
    $fila = ejecutarQuery(
        $conn,
        "SELECT IdRARR FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo WHERE IdEscenario = ?",
        [$id]
    );
    if (count($fila) > 0) {
        actualizarNivelRARR($conn, (int) $fila[0]['IdRARR']);
    }
}

sqlsrv_close($conn);

if ($afectadas === 0) {
    responderError("No se encontró el registro a eliminar", 404);
}
responderOK(null, "Registro eliminado correctamente");
