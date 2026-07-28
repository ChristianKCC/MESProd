<?php
/* ============================================================================
   ENDPOINT: Alta / edición de un catálogo simple (modal Personalizar)
   POST: tipo, id (vacío = nuevo), descripcion
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';
require_once __DIR__ . '/../../Hooks/config.php';

$tipo = limpiar($_POST['tipo'] ?? '');
$id = enteroONull($_POST['id'] ?? null);
$descripcion = limpiar($_POST['descripcion'] ?? '');

$cfg = configOMorir($tipo);

if ($descripcion === '') {
    responderError("La descripción es obligatoria");
}
if (mb_strlen($descripcion) > 200) {
    responderError("La descripción no debe pasar de 200 caracteres");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

/* No permitir duplicados */
$dup = ejecutarQuery(
    $conn,
    "SELECT COUNT(*) AS N FROM TLX002MXDB.dbo.{$cfg['tabla']}
     WHERE Activo = 1 AND RTRIM(Descripcion) = ? AND {$cfg['pk']} <> ISNULL(?, 0)",
    [$descripcion, $id]
);
if ((int) $dup[0]['N'] > 0) {
    sqlsrv_close($conn);
    responderError("Ya existe un registro con esa descripción");
}

if ($id === null) {
    $nuevo = insertarYObtenerId(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.{$cfg['tabla']} (Descripcion, Activo) VALUES (?, 1)",
        [$descripcion]
    );
    sqlsrv_close($conn);
    responderOK(["id" => $nuevo], "Registro agregado correctamente");
}

$stmt = sqlsrv_query(
    $conn,
    "UPDATE TLX002MXDB.dbo.{$cfg['tabla']} SET Descripcion = ? WHERE {$cfg['pk']} = ?",
    [$descripcion, $id]
);
if ($stmt === false) {
    sqlsrv_close($conn);
    responderError("No se pudo actualizar el registro", 500, sqlsrv_errors());
}
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

responderOK(["id" => $id], "Registro actualizado correctamente");