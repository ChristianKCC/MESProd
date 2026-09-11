<?php
require_once("../../conexion.php");
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getAll': getAll(); break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
}

// ── GET: todas las secciones ───────────────────────────────────
function getSecciones() {
    global $conn;

    $sql  = "SELECT idSeccion, Seccion FROM tblProduccionSecciones ORDER BY Seccion ASC";
    $stmt = sqlsrv_query($conn, $sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al consultar secciones']);
        return;
    }

    $rows = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = [
            'id'    => $row['idSeccion'],
            'label' => $row['Seccion'],
        ];
    }

    echo json_encode($rows);
}