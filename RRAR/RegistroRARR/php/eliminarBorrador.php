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

$id = enteroONull($_POST['idBorrador'] ?? null);
if ($id === null) {
    responderError("Borrador no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");
ejecutarAccion($conn, "DELETE FROM TLX002MXDB.dbo.Seg_BorradorImagenRARR WHERE IdBorrador = ?", [$id]);
ejecutarAccion($conn, "DELETE FROM TLX002MXDB.dbo.Seg_BorradorRARR WHERE IdBorrador = ? AND no_emp = ?", [$id, $noEmp]);
sqlsrv_close($conn);
responderOK(["idBorrador" => $id], "Borrador eliminado");