<?php
session_start();
header('Content-Type: application/json');
require_once "../../conexion.php";

$ibmDelegante = trim((string) ($_SESSION['ibm'] ?? $_SESSION['IBM'] ?? ''));
if ($ibmDelegante === '') {
    echo json_encode(["error" => "Sesión no válida"]);
    exit;
}

$conn = (new ClassConexion())->conexion("TLX002MXDB");
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'listar':
        listarDelegaciones($conn, $ibmDelegante);
        break;
    case 'guardar':
        guardarDelegacion($conn, $ibmDelegante);
        break;
    case 'cancelar':
        cancelarDelegacion($conn, $ibmDelegante);
        break;
    case 'listarRecibidas':
        listarRecibidas($conn, $ibmDelegante);
        break;
    case 'listarHistorial':
        listarHistorial($conn, $ibmDelegante);
        break;
    default:
        echo json_encode(["error" => "Acción no válida"]);
}
sqlsrv_close($conn);

function listarDelegaciones($conn, $ibm)
{
    $sql = "SELECT IdDelegacion, IBMDelegado,
                   CONVERT(varchar(10), FechaInicio, 23) AS FechaInicio,
                   CONVERT(varchar(10), FechaFin, 23)    AS FechaFin,
                   Comentario
            FROM dbo.tblMXPRDelegaciones
            WHERE IBMDelegante = ? AND Activo = 1
              AND FechaFin >= CAST(GETDATE() AS DATE)
            ORDER BY FechaInicio";
    $stmt = sqlsrv_query($conn, $sql, [$ibm]);
    $rows = [];
    while ($stmt && ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)))
        $rows[] = $r;
    echo json_encode(["ok" => true, "data" => $rows]);
}

function guardarDelegacion($conn, $ibm)
{
    $delegado = trim((string) ($_POST['ibmDelegado'] ?? ''));
    $fInicio = $_POST['fechaInicio'] ?? '';
    $fFin = $_POST['fechaFin'] ?? '';
    $comentario = trim((string) ($_POST['comentario'] ?? ''));

    if ($delegado === '' || $fInicio === '' || $fFin === '')
        return print (json_encode(["error" => "Faltan datos obligatorios"]));
    if (!preg_match('/^\d{1,20}$/', $delegado))
        return print (json_encode(["error" => "El IBM del delegado debe ser numérico"]));
    if ($delegado === $ibm)
        return print (json_encode(["error" => "No puedes delegarte a ti mismo"]));
    if ($fFin < $fInicio)
        return print (json_encode(["error" => "La fecha fin no puede ser anterior a la de inicio"]));
    if ($fFin < date('Y-m-d'))
        return print (json_encode(["error" => "El periodo ya está vencido"]));

    // Evitar traslapes con delegaciones activas del mismo jefe
    // $sqlDup = "SELECT COUNT(*) AS n FROM dbo.tblMXPRDelegaciones
    //            WHERE IBMDelegante = ? AND Activo = 1 AND FechaInicio <= ? AND FechaFin >= ?";
    // $stmtDup = sqlsrv_query($conn, $sqlDup, [$ibm, $fFin, $fInicio]);
    // $dup = $stmtDup ? sqlsrv_fetch_array($stmtDup, SQLSRV_FETCH_ASSOC) : ['n' => 0];
    // if (($dup['n'] ?? 0) > 0)
    //     return print (json_encode(["error" => "Ya tienes una delegación activa que se traslapa con ese periodo"]));

    // --- CANDADO 1: el delegante solo puede tener UNA delegación vigente a la vez ---
    $sql1 = "SELECT TOP 1 CONVERT(varchar(10), FechaFin, 23) AS FechaFin
             FROM dbo.tblMXPRDelegaciones
             WHERE IBMDelegante = ? AND Activo = 1
               AND FechaFin >= CAST(GETDATE() AS DATE)";
    $st1 = sqlsrv_query($conn, $sql1, [$ibm]);
    if ($st1 && ($r1 = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC))) {
        return print (json_encode([
            "error" =>
                "Ya tienes una delegación vigente hasta el " . fmtFecha($r1['FechaFin']) .
                ". Solo puedes crear otra cuando esa termine o la canceles."
        ]));
    }

    // --- CANDADO 2: la persona a delegar no debe estar ya en otra delegación vigente ---
    // (ni recibiendo otra, ni habiendo delegado lo suyo)
    $sql2 = "SELECT TOP 1 CONVERT(varchar(10), FechaFin, 23) AS FechaFin
             FROM dbo.tblMXPRDelegaciones
             WHERE Activo = 1 AND FechaFin >= CAST(GETDATE() AS DATE)
               AND (IBMDelegado = ? OR IBMDelegante = ?)";
    $st2 = sqlsrv_query($conn, $sql2, [$delegado, $delegado]);
    if ($st2 && ($r2 = sqlsrv_fetch_array($st2, SQLSRV_FETCH_ASSOC))) {
        return print (json_encode([
            "error" =>
                "El IBM " . htmlspecialchars($delegado) . " ya tiene una delegación en curso y no se puede asignar mas por el momento, intenta con otro."
        ]));
    }

    $sql = "INSERT INTO dbo.tblMXPRDelegaciones
                (IBMDelegante, IBMDelegado, FechaInicio, FechaFin, Comentario, IBMRegistro)
            OUTPUT INSERTED.IdDelegacion
            VALUES (?, ?, ?, ?, ?, ?)";
    $params = [$ibm, $delegado, $fInicio, $fFin, ($comentario !== '' ? $comentario : null), $ibm];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false)
        return print (json_encode(["error" => sqlsrv_errors()]));
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    echo json_encode(["ok" => true, "id" => $row['IdDelegacion']]);
}

function cancelarDelegacion($conn, $ibm)
{
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0)
        return print (json_encode(["error" => "Id no válido"]));
    $sql = "UPDATE dbo.tblMXPRDelegaciones SET Activo = 0
            WHERE IdDelegacion = ? AND IBMDelegante = ? AND Activo = 1";
    $stmt = sqlsrv_query($conn, $sql, [$id, $ibm]);
    if ($stmt === false)
        return print (json_encode(["error" => sqlsrv_errors()]));
    echo json_encode(["ok" => true, "afectados" => sqlsrv_rows_affected($stmt)]);
}

function listarRecibidas($conn, $ibm)
{
    $sql = "SELECT IdDelegacion, IBMDelegante,
                   CONVERT(varchar(10), FechaInicio, 23) AS FechaInicio,
                   CONVERT(varchar(10), FechaFin, 23)    AS FechaFin,
                   Comentario
            FROM dbo.tblMXPRDelegaciones
            WHERE IBMDelegado = ? AND Activo = 1
              AND FechaFin >= CAST(GETDATE() AS DATE)
            ORDER BY FechaInicio";
    $stmt = sqlsrv_query($conn, $sql, [$ibm]);
    $rows = [];
    while ($stmt && ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)))
        $rows[] = $r;
    echo json_encode(["ok" => true, "data" => $rows]);
}

function fmtFecha($iso)
{
    $p = explode('-', $iso);
    return count($p) === 3 ? "$p[2]/$p[1]/$p[0]" : $iso;
}

// Funcion para listado de historial
function listarHistorial($conn, $ibm)
{
    $sql = "SELECT IdDelegacion,
                   CASE WHEN IBMDelegante = ? THEN 'Hecha' ELSE 'Recibida' END AS Tipo,
                   CASE WHEN IBMDelegante = ? THEN IBMDelegado ELSE IBMDelegante END AS Contraparte,
                   CONVERT(varchar(10), FechaInicio, 23) AS FechaInicio,
                   CONVERT(varchar(10), FechaFin, 23)    AS FechaFin,
                   Comentario,
                   CASE WHEN Activo = 0 THEN 'Cancelada' ELSE 'Vencida' END AS Estado
            FROM dbo.tblMXPRDelegaciones
            WHERE (IBMDelegante = ? OR IBMDelegado = ?)
              AND (Activo = 0 OR FechaFin < CAST(GETDATE() AS DATE))
            ORDER BY FechaFin DESC";
    $stmt = sqlsrv_query($conn, $sql, [$ibm, $ibm, $ibm, $ibm]);
    $rows = [];
    while ($stmt && ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)))
        $rows[] = $r;
    echo json_encode(["ok" => true, "data" => $rows]);
}