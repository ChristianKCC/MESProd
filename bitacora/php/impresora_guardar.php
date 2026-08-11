<?php
session_start();

if (($_SESSION['imp_exp'] ?? 0) < time()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Sesión de configuración expirada']);
    exit;
}

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

$data = json_decode(file_get_contents('php://input'), true);
$host = trim($data['host'] ?? '');
$puerto = (int) ($data['puerto'] ?? 9100);

if ($host === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'La IP o nombre es obligatorio']);
    exit;
}
if ($puerto < 1 || $puerto > 65535) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Puerto inválido']);
    exit;
}

$sql = "MERGE TLX002MXDB.dbo.tblMXPRImpresoraMaquina AS t
        USING (SELECT ? AS id_maquina, ? AS host, ? AS puerto) AS s
        ON t.id_maquina = s.id_maquina
        WHEN MATCHED THEN
            UPDATE SET t.host = s.host, t.puerto = s.puerto
        WHEN NOT MATCHED THEN
            INSERT (id_maquina, host, puerto) VALUES (s.id_maquina, s.host, s.puerto);";
$stmt = sqlsrv_query($conn, $sql, [$idMaquina, $host, $puerto]);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar']);
    exit;
}

echo json_encode(['ok' => true, 'host' => $host, 'puerto' => $puerto]);