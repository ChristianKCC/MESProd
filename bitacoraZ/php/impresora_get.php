<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once "../../conexion.php";

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

if (session_status() === PHP_SESSION_NONE)
    session_start();

$idMaquina = $_SESSION['idmaquina'] ?? null;
if (!$idMaquina) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No hay máquina en sesión']);
    exit;
}

// $sql = "SELECT host, puerto FROM TLX002MXDB.dbo.tblMXPRImpresoraMaquina WHERE id_maquina = ?";
$sql = "
SELECT i.host, i.puerto, m.NoMaquina, m.NombreMaquina
FROM TLX002MXDB.dbo.tblMXPRImpresoraMaquina AS i
INNER JOIN TLX009MXDB.dbo.tblMaquinas AS m
    ON i.id_maquina = m.NoMaquina
WHERE i.id_maquina = ?
";
$stmt = sqlsrv_query($conn, $sql, [$idMaquina]);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al consultar']);
    exit;
}
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// echo json_encode([
//     'ok' => true,
//     'id_maquina' => (int) $idMaquina,
//     'existe' => $row ? true : false,
//     'host' => $row['host'] ?? '',
//     'puerto' => $row ? (int) $row['puerto'] : 9100,
// ]);
// $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

echo json_encode([
    'ok' => true,
    'id_maquina' => (int) $idMaquina,
    'nombre_maquina' => $row['NombreMaquina'] ?? '',
    'existe' => $row ? true : false,
    'host' => $row['host'] ?? '',
    'puerto' => $row ? (int) $row['puerto'] : 9100,
]);
