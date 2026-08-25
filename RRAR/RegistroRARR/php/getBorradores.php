<?php
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

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$id = enteroONull($_GET['idBorrador'] ?? null);

if ($id === null) {
    /* Listado */
    $filas = ejecutarQuery(
        $conn,
        "SELECT IdBorrador, ISNULL(IdEquipo,'(sin equipo)') AS IdEquipo,
                ISNULL(Maquina,'-') AS Maquina, ISNULL(Seccion,'-') AS Seccion,
                PasoActual, FechaGuardado
         FROM TLX002MXDB.dbo.Seg_BorradorRARR
         WHERE no_emp = ? AND Activo = 1
         ORDER BY FechaGuardado DESC",
        [$noEmp]
    );
    foreach ($filas as &$f) {
        $f['FechaGuardado'] = !empty($f['FechaGuardado'])
            ? date('d/m/Y H:i', strtotime($f['FechaGuardado'])) : '-';
    }
    unset($f);
    sqlsrv_close($conn);
    responderOK($filas);
}

/* Uno solo, con el índice de sus imágenes */
$b = ejecutarQuery(
    $conn,
    "SELECT TOP 1 IdBorrador, Payload, PasoActual
     FROM TLX002MXDB.dbo.Seg_BorradorRARR
     WHERE IdBorrador = ? AND no_emp = ? AND Activo = 1",
    [$id, $noEmp]
);
if (count($b) === 0) {
    sqlsrv_close($conn);
    responderError("Borrador no encontrado", 404);
}
$imgs = ejecutarQuery(
    $conn,
    "SELECT Indice, Paso FROM TLX002MXDB.dbo.Seg_BorradorImagenRARR WHERE IdBorrador = ?",
    [$id]
);
sqlsrv_close($conn);

responderOK([
    "idBorrador" => (int) $b[0]['IdBorrador'],
    "pasoActual" => (int) $b[0]['PasoActual'],
    "payload" => json_decode($b[0]['Payload'], true),
    "imagenes" => $imgs
]);