<?php
require('../../fpdf/fpdf.php');
require_once(__DIR__ . "/../../conexion.php");

// Funciones auxiliares para formatear fechas y horas
function formatDate($value, $format="Y-m-d") {
    if ($value instanceof DateTime) {
        return $value->format($format);
    }
    return $value ? $value : "";
}

function formatTime($value, $format="H:i:s") {
    if ($value instanceof DateTime) {
        return $value->format($format);
    }
    return $value ? $value : "";
}

$id = isset($_GET['id']) ? base64_decode($_GET['id']) : null;

if ($id) {
    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX002MXDB");
    $query = "SELECT * FROM tblMXPRCambioTurnoTemporal WHERE Ctt_id = $id";
    $result = sqlsrv_query($conn, $query);
    $row = sqlsrv_fetch_array($result);

    if ($row) {
        $pdf = new FPDF('P','mm','Letter');
        $pdf->AddPage();
        $pdf->SetMargins(15, 15, 15);

        // Logo y título
        $pdf->Image('../../img/logo.jpg', 10, 10, 40);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'CAMBIO TEMPORAL DE TURNO',0,1,'C');
        $pdf->Ln(5);

        // === FILA 1: Fecha y Depto ===
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30,8,'Fecha:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(70,8,formatDate($row['Ctt_fecha']),1,0);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20,8,'Depto:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(60,8,utf8_decode($row['Ctt_depto']),1,1);

        // === FILA 2: A y De ===
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(10,8,'A:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(90,8,utf8_decode($row['Ctt_a']),1,0);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20,8,'De:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(60,8,utf8_decode($row['Ctt_de']),1,1);

        $pdf->Ln(5);
        $pdf->SetFont('Arial','I',10);
        $pdf->Cell(0,8,'Por medio de la presente se le informa el siguiente cambio de:',0,1,'C');

        // === FILA 3: Tripulación / Horario / Rol ===
        $pdf->SetFont('Arial','B',10);
        // $pdf->Cell(40,8,'Tripulacion:',1,0);
        // $pdf->SetFont('Arial','',10);
        // $pdf->Cell(55,8,utf8_decode($row['Ctt_tripulacion']),1,0);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,8,'Horario:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(55,8,utf8_decode($row['Ctt_horario']),1,0);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(25,8,'Rol:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(60,8,utf8_decode($row['Ctt_rol']),1,1);

        // === FILA 4: A partir del día / Hasta ===
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,8,'A partir del dia:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(55,8,formatDate($row['Ctt_aPartirDel']),1,0);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(25,8,'Hasta el dia:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(60,8,formatDate($row['Ctt_hastaEl']),1,1);

        $pdf->Ln(5);

        // === BLOQUE: Debiéndose presentar a ===
        $pdf->SetFont('Arial','I',10);
        $pdf->Cell(180,8,'Debiendose presentar a:',0,1,'C');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,8,'Turno:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(60,8,utf8_decode($row['Ctt_turnoPresentacion']),1,0);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20,8,'Hora:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(60,8,formatTime($row['Ctt_horaPresentacion']),1,1);

        // === BLOQUE: Horario ===
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,8,'En el horario:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(60,8,formatTime($row['Ctt_horarioDe']),1,0);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20,8,'a: ',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(60,8,formatTime($row['Ctt_horarioA']),1,1);

        // === BLOQUE: Tripulación de ===
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,8,'Conductor:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(140,8,utf8_decode($row['Ctt_tripulacionDe']),1,1);
        $pdf->Ln(10);    

        // === BLOQUE: Descansos ===
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,8,'Sus descansos:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(140,8,$row['Ctt_descansos'],1,1);

        // === BLOQUE: Días adicionales ===
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,8,'Adicional los dias:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(140,8,utf8_decode($row['Ctt_diaAdd']),1,1);

        // === BLOQUE: Horario adicional ===
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,8,'En el horario:',1,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(140,8,formatTime($row['Ctt_horarioAdd']),1,1);

        $pdf->Ln(30);

        // === Firmas ===
        $y = $pdf->GetY();
        $pdf->Line(25, $y, 95, $y);
        $pdf->Line(120, $y, 190, $y);
        $pdf->Ln(5);
        $pdf->Cell(90,8,'Firma del Supervisor',0,0,'C');
        $pdf->Cell(90,8,'Firma del Empleado',0,1,'C');

        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(0,8,'KCM-173881                           Ref-8-702A-18                  Rev-01',0,1,'C');

        // Mostrar directamente en navegador (nueva pestaña)
        $pdf->Output('I', "cambio_turno.pdf");
    }
}
?>