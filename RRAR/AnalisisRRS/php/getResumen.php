<?php
/* ============================================================================
   ENDPOINT: Resumen de cards (punto 2 del Reporte RRAR)
   - RARR:     Concluidos | Total | Pendientes (Total - Concluidos)
   - Personal: Capacitados | Total | Pendientes (Total - Capacitados)
   El total personal viene de TLX032MXDB.dbo.tblEmpleados por departamento.
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idDepartamento = enteroONull($_GET['idDepartamento'] ?? null);
if ($idDepartamento === null) {
    responderError("Departamento no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

/* --- RARR --- */
$sqlRARR = "SELECT
                COUNT(*) AS Total,
                SUM(CASE WHEN Estatus = 'Concluido' THEN 1 ELSE 0 END) AS Concluidos
            FROM TLX002MXDB.dbo.Seg_RARR
            WHERE IdDepartamento = ?";
$rarr = ejecutarQuery($conn, $sqlRARR, [$idDepartamento]);
$totalRARR = (int) ($rarr[0]['Total'] ?? 0);
$concluidosRARR = (int) ($rarr[0]['Concluidos'] ?? 0);

/* --- Personal ---
   Total desde el catálogo de empleados por departamento. */
$sqlPersonal = "SELECT COUNT(*) AS TotalPersonal
                FROM TLX032MXDB.dbo.tblEmpleados e
                WHERE e.NombreDepartamento = ?
                AND Bajas <> 1";
$per = ejecutarQuery($conn, $sqlPersonal, [$idDepartamento]);
$totalPersonal = (int) ($per[0]['TotalPersonal'] ?? 0);

$sqlCap = "SELECT COUNT(*) AS Capacitados
           FROM TLX002MXDB.dbo.Seg_PersonalCapacitado
           WHERE IdDepartamento = ? AND Activo = 1";
$cap = ejecutarQuery($conn, $sqlCap, [$idDepartamento]);
$capacitados = (int) ($cap[0]['Capacitados'] ?? 0);

sqlsrv_close($conn);

responderOK([
    "rarr" => [
        "concluidos" => $concluidosRARR,
        "total" => $totalRARR,
        "pendientes" => max(0, $totalRARR - $concluidosRARR)
    ],
    "personal" => [
        "capacitados" => $capacitados,
        "total" => $totalPersonal,
        "pendientes" => max(0, $totalPersonal - $capacitados)
    ]
]);
