<?php
require_once('../../../conexion.php');
$conection = new ClassConexion();
$conn = $conection->conexion("TLX001MXDB");

$empno = $_POST['empno'] ?? null;
$response = ["success" => false];

if ($empno) {
    $sql = "SELECT IdCentroCosto, EmpleadoSindicalizado, NombreDepartamento
            FROM TLX032MXDB.dbo.tblEmpleados
            WHERE NoEmp = ?";
    $stmt = sqlsrv_query($conn, $sql, [$empno]);
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $response = [
    "success" => true,
    "IdCentroCosto" => $row['IdCentroCosto'] ?? null,
    "EmpleadoSindicalizado" => $row['EmpleadoSindicalizado'] ?? null,
    "NombreDepartamento" => $row['NombreDepartamento'] ?? null
    ];
    } 
}

header('Content-Type: application/json');
echo json_encode($response);
