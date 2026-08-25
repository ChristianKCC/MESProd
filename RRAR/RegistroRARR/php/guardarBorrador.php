<?php
/* ============================================================================
   ENDPOINT: Guardar/actualizar el borrador del RARR en curso
   POST: idBorrador (opcional), payload (JSON), pasoActual, imgP1_i / imgP3_i
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$noEmp = $_SESSION['ibm'] ?? null;
if (!$noEmp) {
    responderError("Sesión no válida", 401);
}

$idBorrador = enteroONull($_POST['idBorrador'] ?? null);
$payload = $_POST['payload'] ?? '';
$pasoActual = enteroONull($_POST['pasoActual'] ?? 1) ?? 1;

if (trim($payload) === '' || json_decode($payload, true) === null) {
    responderError("El borrador no tiene datos válidos");
}
$p = json_decode($payload, true);
$idEquipo = limpiar($p['paso1']['idEquipo'] ?? '');
$maquina = limpiar($p['paso1']['maquina'] ?? '');
$seccion = limpiar($p['paso1']['seccion'] ?? '');

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

if ($idBorrador === null) {
    $idBorrador = insertarYObtenerId(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_BorradorRARR
            (no_emp, IdEquipo, Maquina, Seccion, PasoActual, Payload)
         VALUES (?,?,?,?,?,?)",
        [$noEmp, $idEquipo, $maquina, $seccion, $pasoActual, $payload]
    );
} else {
    ejecutarAccion(
        $conn,
        "UPDATE TLX002MXDB.dbo.Seg_BorradorRARR
         SET IdEquipo = ?, Maquina = ?, Seccion = ?, PasoActual = ?,
             Payload = ?, FechaGuardado = GETDATE()
         WHERE IdBorrador = ? AND no_emp = ?",
        [$idEquipo, $maquina, $seccion, $pasoActual, $payload, $idBorrador, $noEmp]
    );
}

/* Imágenes nuevas: reemplazan la de ese índice + paso */
foreach ($_FILES as $campo => $f) {
    if ($f['error'] !== UPLOAD_ERR_OK)
        continue;
    if (!preg_match('/^img(P1|P3)_(\d+)$/', $campo, $m))
        continue;
    $paso = $m[1] === 'P1' ? 1 : 3;
    $indice = (int) $m[2];

    $info = @getimagesize($f['tmp_name']);
    if ($info === false || $f['size'] > 5 * 1024 * 1024)
        continue;

    ejecutarAccion(
        $conn,
        "DELETE FROM TLX002MXDB.dbo.Seg_BorradorImagenRARR
         WHERE IdBorrador = ? AND Indice = ? AND Paso = ?",
        [$idBorrador, $indice, $paso]
    );

    $bin = file_get_contents($f['tmp_name']);
    sqlsrv_query(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_BorradorImagenRARR
            (IdBorrador, Indice, Paso, NombreArchivo, TipoMime, Imagen)
         VALUES (?,?,?,?,?,?)",
        [
            $idBorrador,
            $indice,
            $paso,
            basename($f['name']),
            $info['mime'],
            [$bin, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max')]
        ]
    );
}

sqlsrv_close($conn);
responderOK(["idBorrador" => $idBorrador], "Borrador guardado");