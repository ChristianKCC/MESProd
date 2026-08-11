<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once __DIR__ . "/../../conexion.php";
header('Content-Type: application/json; charset=utf-8');

$ibm = trim($_GET['ibm'] ?? '');
if ($ibm === '') {
    echo json_encode([]);
    exit;
}

$conn = (new ClassConexion())->conexion("TLX002MXDB");
$q = "SELECT sub.Vcs_de AS fde, sub.Vcs_hasta AS fa,
             enc.Vc_autorizado AS aut, enc.Vc_revisado AS rev, enc.Vc_firmaRI AS ri
      FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
      INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub ON sub.Vcs_vc_id = enc.Vc_id
      WHERE enc.Vc_ibm = ?
        AND enc.Vc_autorizado <> 2";   // excluye rechazados → esos días quedan libres
$r = sqlsrv_query($conn, $q, [$ibm]);

$eventos = [];
if ($r !== false) {
    while ($row = sqlsrv_fetch_array($r, SQLSRV_FETCH_ASSOC)) {
        if (!$row['fde'] || !$row['fa'])
            continue;
        $aExcl = clone $row['fa'];
        $aExcl->modify('+1 day'); // end exclusivo en FullCalendar

        $aprobado = ($row['aut'] == 1 && $row['rev'] == 1 && $row['ri'] == 1);
        $eventos[] = [
            "start" => $row['fde']->format('Y-m-d'),
            "end" => $aExcl->format('Y-m-d'),
            "display" => "background",
            "color" => $aprobado ? "#28a745" : "#0d6efd",   // verde aprobado / azul pendiente
            "title" => $aprobado ? "Aprobado" : "Pendiente"
        ];
    }
}
echo json_encode($eventos);
sqlsrv_close($conn);