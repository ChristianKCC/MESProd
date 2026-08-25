<?php
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$id = enteroONull($_GET['idBorrador'] ?? null);
$indice = enteroONull($_GET['indice'] ?? null);
$paso = enteroONull($_GET['paso'] ?? null);
if ($id === null || $indice === null || $paso === null) {
    http_response_code(400);
    exit('Parámetros');
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");
$stmt = sqlsrv_query(
    $conn,
    "SELECT TOP 1 Imagen, TipoMime FROM TLX002MXDB.dbo.Seg_BorradorImagenRARR
     WHERE IdBorrador = ? AND Indice = ? AND Paso = ?",
    [$id, $indice, $paso]
);
$row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
sqlsrv_close($conn);
if (!$row) {
    http_response_code(404);
    exit('Sin imagen');
}
header('Content-Type: ' . $row['TipoMime']);
echo $row['Imagen'];