<?php
/* ============================================================================
   ENDPOINT: Responder un reporte y/o validar la asignación como completada
   POST: idSeguimiento, comentario (opcional), validar (0|1)
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$ibm = $_SESSION['ibm'] ?? null;
if (!$ibm) {
    responderError("Sesión no válida", 401);
}

$idSeguimiento = enteroONull($_POST['idSeguimiento'] ?? null);
$comentario = limpiar($_POST['comentario'] ?? '');
$validar = (int) ($_POST['validar'] ?? 0);

if ($idSeguimiento === null) {
    responderError("Registro no válido");
}
if ($comentario === '' && $validar !== 1) {
    responderError("Escribe una respuesta o marca la asignación como completada");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$reg = ejecutarQuery(
    $conn,
    "SELECT TOP 1 s.IdSeguimiento, s.IdRARR, s.IdEscenario, r.IdEquipo
     FROM TLX002MXDB.dbo.Seg_SeguimientoControl s
     INNER JOIN TLX002MXDB.dbo.Seg_RARR r ON r.IdRARR = s.IdRARR
     WHERE s.IdSeguimiento = ? AND s.Activo = 1",
    [$idSeguimiento]
);
if (count($reg) === 0) {
    sqlsrv_close($conn);
    responderError("La asignación no existe", 404);
}

/* Marca los reportes como leídos */
ejecutarAccion(
    $conn,
    "UPDATE TLX002MXDB.dbo.Seg_FeedbackRARR SET Leido = 1
     WHERE IdSeguimiento = ? AND Tipo = 'reporte' AND Leido = 0",
    [$idSeguimiento]
);

/* Guarda la respuesta del supervisor */
if ($comentario !== '') {
    // ejecutarAccion(
    //     $conn,
    //     "INSERT INTO TLX002MXDB.dbo.Seg_FeedbackRARR
    //         (IdSeguimiento, IdRARR, IdEscenario, IdEquipo, Ibm, Comentario, Tipo, Leido)
    //      VALUES (?,?,?,?,?,?, 'respuesta', 1)",
    //     [
    //         $idSeguimiento,
    //         $reg[0]['IdRARR'],
    //         $reg[0]['IdEscenario'],
    //         $reg[0]['IdEquipo'],
    //         $ibm,
    //         $comentario
    //     ]
    // );

    ejecutarAccion(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_FeedbackRARR
            (IdSeguimiento, IdRARR, IdEscenario, IdEquipo, Ibm, Comentario, Tipo, Leido)
         VALUES (?,?,?,?,?,?, 'respuesta', 0)",
        [
            $idSeguimiento,
            $reg[0]['IdRARR'],
            $reg[0]['IdEscenario'],
            $reg[0]['IdEquipo'],
            $ibm,
            $comentario
        ]
    );
}

/* Valida: marca la asignación como concluida (estatus 3) */
if ($validar === 1) {
    ejecutarAccion(
        $conn,
        "UPDATE TLX002MXDB.dbo.Seg_SeguimientoControl SET IdEstatus = 3 WHERE IdSeguimiento = ?",
        [$idSeguimiento]
    );
}

if (function_exists('registrarLog')) {
    registrarLog($conn, $validar === 1 ? 'Edicion' : 'Consulta', [
        'modulo' => 'RegistroRARR',
        'entidad' => 'FeedbackRARR',
        'idEquipo' => $reg[0]['IdEquipo'],
        'idRARR' => $reg[0]['IdRARR'],
        'detalle' => ['idSeguimiento' => $idSeguimiento, 'validado' => $validar]
    ]);
}

sqlsrv_close($conn);
responderOK(["idSeguimiento" => $idSeguimiento], $validar === 1
    ? "Asignación marcada como completada"
    : "Respuesta enviada");