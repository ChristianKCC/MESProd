<?php
require('../../fpdf/fpdf.php');
require_once(__DIR__ . "/../../conexion.php");

if (session_status() === PHP_SESSION_NONE) session_start();

function formatDate($value, $format = "d/m/Y") {
    if ($value instanceof DateTime) return $value->format($format);
    return $value ? $value : "";
}
function formatTime($value, $format = "H:i") {
    if ($value instanceof DateTime) return $value->format($format);
    return $value ? $value : "";
}

$id = isset($_GET['id']) ? base64_decode($_GET['id']) : null;
if (!$id) exit;

$ClassConexion = new ClassConexion();
$conn   = $ClassConexion->conexion("TLX002MXDB");
$query  = "SELECT 
            ct.Ctt_id,
            ct.Ctt_fecha,
            ct.Ctt_depto,
            ct.Ctt_a,
            ct.Ctt_de,
            ct.Ctt_horario,
            ct.Ctt_rol,
            ct.Ctt_aPartirDel,
            ct.Ctt_hastaEl,
            ct.Ctt_horaPresentacion,
            ct.Ctt_turnoPresentacion,
            ct.Ctt_tripulacionDe,
            ct.Ctt_horarioDe,
            ct.Ctt_horarioA,
            ct.Ctt_descansos,
            ct.Ctt_diaAdd,
            ct.Ctt_horarioAdd,
            ct.Ctt_ibmEmpleado,
            emp.Nombre AS NombreEmpleado,
            ct.Ctt_ibmAutoriza,
            sup.Nombre AS NombreSupervisor,
            dep.NombreDepto
        FROM dbo.tblMXPRCambioTurnoTemporal ct
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados emp ON emp.NoEmp = ct.Ctt_ibmEmpleado
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados sup ON sup.NoEmp = ct.Ctt_ibmAutoriza
        LEFT JOIN TLX009MXDB.dbo.tblDepartamentos dep 
       ON dep.NoDepto = TRY_CAST(ct.Ctt_depto AS INT)
        WHERE ct.Ctt_id = ?";

$stmt = sqlsrv_query($conn, $query, [intval($id)]);
$row  = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$row) exit;

$ibmSupervisor = $row['Ctt_ibmAutoriza'] ?? null;
$ibmEmpleado   = $row['Ctt_ibmEmpleado'] ?? null;

$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

// Logo y título
$pdf->Ln(10);
$pdf->Image('../../img/logo.jpg', 15, 10, 70);
$pdf->SetFont('Arial','B',16);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,utf8_decode('Relación Cambio Temporal de Turno'),0,1,'C');
$pdf->Ln(10);

// Línea separadora azul
$pdf->SetDrawColor(0,102,204);
$pdf->Line(15, 33, 200, 33);
$pdf->Ln(5);

// Fecha y Depto
$pdf->SetFillColor(220,230,241); // azul claro
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(30, 8, 'Fecha:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(70, 8, formatDate($row['Ctt_fecha']), 1, 0);
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(20, 8, 'Depto:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(60, 8, utf8_decode($row['Ctt_depto']), 1, 1);

// A y De
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(10, 8, 'A:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(90, 8, utf8_decode($row['Ctt_a']), 1, 0);
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(20, 8, 'De:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(60, 8, utf8_decode($row['Ctt_de']), 1, 1);

// Nueva fila para mostrar IBM debajo de cada uno
$pdf->SetFont('Arial', 'B', 9); 
$pdf->Cell(10, 8, 'IBM', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 9);  
$pdf->Cell(90, 8, $row['Ctt_ibmEmpleado'], 1, 0);

$pdf->SetFont('Arial', 'B', 9); 
$pdf->Cell(20, 8, 'IBM', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 9);  
$pdf->Cell(60, 8, $row['Ctt_ibmAutoriza'], 1, 1);

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 8, 'Por medio de la presente se le informa el siguiente cambio de:', 0, 1, 'C');

// Horario y Rol
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(40, 8, 'Horario:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(55, 8, utf8_decode($row['Ctt_horario']), 1, 0);
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 8, 'Rol:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(60, 8, utf8_decode($row['Ctt_rol']), 1, 1);

// A partir del día / Hasta
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(40, 8, 'A partir del dia:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(55, 8, formatDate($row['Ctt_aPartirDel']), 1, 0);
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(25, 8, 'Hasta el dia:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(60, 8, formatDate($row['Ctt_hastaEl']), 1, 1);

$pdf->Ln(5);

// Debiéndose presentar a
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(180, 8, utf8_decode('Debiéndose presentar a:'), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(40, 8, 'Turno:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(60, 8, utf8_decode($row['Ctt_turnoPresentacion']), 1, 0);
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(20, 8, 'Hora:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(60, 8, formatTime($row['Ctt_horaPresentacion']), 1, 1);

// Horario
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(40, 8, 'En el horario:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(60, 8, formatTime($row['Ctt_horarioDe']), 1, 0);
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(20, 8, 'a:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(60, 8, formatTime($row['Ctt_horarioA']), 1, 1);

// Conductor
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(40, 8, 'Conductor:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(140, 8, utf8_decode($row['Ctt_tripulacionDe']), 1, 1);
$pdf->Ln(10);

// Descansos
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(40, 8, 'Sus descansos:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(140, 8, $row['Ctt_descansos'], 1, 1);

// Días adicionales
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(40, 8, 'Adicional los dias:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(140, 8, utf8_decode($row['Ctt_diaAdd']), 1, 1);

// Horario adicional
$pdf->SetFont('Arial', 'B', 10); $pdf->Cell(40, 8, 'En el horario:', 1, 0,'L',true);
$pdf->SetFont('Arial', '', 10);  $pdf->Cell(140, 8, formatTime($row['Ctt_horarioAdd']), 1, 1);

$pdf->Ln(30);

// ── FIRMAS ────────────────────────────────────────────────────────────────────
$y = $pdf->GetY();
$pdf->Line(25, $y, 95, $y);
$pdf->Line(120, $y, 190, $y);
$pdf->Ln(5);
$pdf->Cell(90, 6, 'Firma del Supervisor', 0, 0, 'C');
$pdf->Cell(100, 6, 'Firma del Empleado',   0, 1, 'C');

$extensiones = ['png', 'jpg', 'jpeg'];

function buscarFirma($ibm, $extensiones) {
    if (!$ibm) return null;
    foreach ($extensiones as $ext) {
        $ruta = "../firmas/{$ibm}.{$ext}";
        if (file_exists($ruta)) return ['ruta' => $ruta, 'size' => 13];
    }
    foreach ($extensiones as $ext) {
        $ruta = "../../FirmaDigital/firmas/{$ibm}.{$ext}";
        if (file_exists($ruta)) return ['ruta' => $ruta, 'size' => 40];
    }
    return null;
}

// SUPERVISOR / AUTORIZA
$firmaSup = buscarFirma($row['Ctt_ibmAutoriza'], $extensiones);
if ($firmaSup) {
    $pdf->Image($firmaSup['ruta'], 40, $y - 15, $firmaSup['size']);
    $pdf->SetFont('Arial', '', 8);    
}

// EMPLEADO
$firmaEmp = buscarFirma($row['Ctt_ibmEmpleado'], $extensiones);
if ($firmaEmp) {
    $pdf->Image($firmaEmp['ruta'], 135, $y - 15, $firmaEmp['size']);
    $pdf->SetFont('Arial', '', 8);
}

$pdf->Text(40, $y -1, ucwords(strtolower(utf8_decode($row['NombreSupervisor']))));
$pdf->Text(130, $y - 1, ucwords(strtolower(utf8_decode($row['NombreEmpleado']))));

// Footer
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 8, 'KCM-173881                           Ref-8-702A-18                  Rev-01', 0, 1, 'C');

$pdf->Output('I', "cambio_turno.pdf");
?>
