<?php
/* ============================================================================
   ENDPOINT: Sirve el binario de una imagen del RARR
   GET: ?idImagen=   (o bien ?idEscenario=&paso=)
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idImagen = enteroONull($_GET['idImagen'] ?? null);
$idEscenario = enteroONull($_GET['idEscenario'] ?? null);
$paso = enteroONull($_GET['paso'] ?? null);

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

if ($idImagen !== null) {
    $sql = "SELECT TOP 1 Imagen, TipoMime FROM TLX002MXDB.dbo.Seg_ImagenRARR
            WHERE IdImagen = ? AND Activo = 1";
    $params = [$idImagen];
} elseif ($idEscenario !== null && $paso !== null) {
    $sql = "SELECT TOP 1 Imagen, TipoMime FROM TLX002MXDB.dbo.Seg_ImagenRARR
            WHERE IdEscenario = ? AND Paso = ? AND Activo = 1 ORDER BY IdImagen DESC";
    $params = [$idEscenario, $paso];
} else {
    http_response_code(400);
    exit('Parámetros insuficientes');
}

$stmt = sqlsrv_query($conn, $sql, $params);
$row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
sqlsrv_close($conn);

if (!$row || $row['Imagen'] === null) {
    http_response_code(404);
    exit('Imagen no encontrada');
}

header('Content-Type: ' . ($row['TipoMime'] ?: 'image/jpeg'));
header('Cache-Control: max-age=3600');
echo $row['Imagen'];
exit;