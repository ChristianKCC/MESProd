<?php
/* ============================================================================
   HOOK: Respuestas JSON estandarizadas
   Todos los endpoints responden { ok: bool, data|error, msg? }
   No hacer echo de debug: usar logDebug() para no corromper el JSON.
   ============================================================================ */

header('Content-Type: application/json; charset=utf-8');

function responderOK($data = null, $msg = '')
{
    echo json_encode(["ok" => true, "data" => $data, "msg" => $msg]);
    exit;
}

function responderError($error, $codigo = 400, $det = null)
{
    http_response_code($codigo);
    echo json_encode(["ok" => false, "error" => $error, "det" => $det]);
    exit;
}

/* Ejecuta una query con parámetros posicionales, valida errores y
   devuelve todas las filas como arreglo asociativo.
   Libera el statement (sqlsrv_free_stmt) para evitar errores MARS. */
function ejecutarQuery($conn, $sql, $params = [])
{
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        responderError("Error al ejecutar la consulta", 500, sqlsrv_errors());
    }

    $filas = [];
    while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Convertir objetos DateTime a string legible
        foreach ($fila as $k => $v) {
            if ($v instanceof DateTime) {
                $fila[$k] = $v->format('Y-m-d H:i:s');
            }
        }
        $filas[] = $fila;
    }
    sqlsrv_free_stmt($stmt);
    return $filas;
}

/* Ejecuta un INSERT/UPDATE y devuelve filas afectadas. */
function ejecutarAccion($conn, $sql, $params = [])
{
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        responderError("Error al guardar la información", 500, sqlsrv_errors());
    }
    $afectadas = sqlsrv_rows_affected($stmt);
    sqlsrv_free_stmt($stmt);
    return $afectadas;
}

/* Obtiene el último ID insertado (SCOPE_IDENTITY del mismo batch). */
function insertarYObtenerId($conn, $sql, $params = [])
{
    $sql .= "; SELECT SCOPE_IDENTITY() AS Id;";
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        responderError("Error al guardar la información", 500, sqlsrv_errors());
    }
    sqlsrv_next_result($stmt);
    sqlsrv_fetch($stmt);
    $id = sqlsrv_get_field($stmt, 0);
    sqlsrv_free_stmt($stmt);
    return (int)$id;
}

/* Log a archivo (no usar echo para debug: corrompe el JSON). */
function logDebug($msg)
{
    $ruta = __DIR__ . '/debug.log';
    file_put_contents($ruta, date('Y-m-d H:i:s') . " - " . print_r($msg, true) . PHP_EOL, FILE_APPEND);
}
