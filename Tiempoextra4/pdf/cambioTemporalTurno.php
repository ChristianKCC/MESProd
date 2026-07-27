<?php
require('../../fpdf/fpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
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
    $pdf->Cell(70,8,utf8_decode($_POST['fecha_emision']),1,0);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(20,8,'Depto:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(60,8,utf8_decode($_POST['Depto_m']),1,1);

    // === FILA 2: A y De ===
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10,8,'A:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(90,8,utf8_decode($_POST['nombre_receptor']),1,0);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(20,8,'De:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(60,8,utf8_decode($_POST['de_area']),1,1);

    $pdf->Ln(5);
    $pdf->SetFont('Arial','I',9);
    $pdf->Cell(0,8,'Por medio de la presente se le informa el siguiente cambio de:',0,1,'C');

    // === FILA 3: Tripulación / Horario / Rol ===
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(30,8,'Conductor:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(35,8,utf8_decode($_POST['tripulacion']),1,0);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(30,8,'Horario:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(35,8,utf8_decode($_POST['horario_texto']),1,0);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(20,8,'Rol:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(30,8,utf8_decode($_POST['rol']),1,1);

    // === FILA 4: A partir del día / Hasta ===
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(40,8,'A partir del dia:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(55,8,utf8_decode($_POST['fecha_inicio']),1,0);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(30,8,'Hasta el dia:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(55,8,utf8_decode($_POST['hasta_el']),1,1);

    $pdf->Ln(5);

    // === BLOQUE: Debiéndose presentar a ===
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(180,8,'Debiendose presentar a:',1,1,'C');
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(40,8,'Turno:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(60,8,utf8_decode($_POST['turno_presentacion']),1,0);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(20,8,'Hora:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(60,8,utf8_decode($_POST['hora_presentacion']),1,1);

    // === BLOQUE: Horario ===
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(40,8,'En el horario:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(60,8,utf8_decode($_POST['horario_desde']),1,0);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(20,8,'a: ',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(60,8,utf8_decode($_POST['horario_hasta']),1,1);

    // === BLOQUE: Tripulación de ===
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(40,8,'Conductor:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(140,8,utf8_decode($_POST['hasta_tripulacion']),1,1);
    $pdf->Ln(5);    

    // === BLOQUE: Descansos ===
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(40,8,'Sus descansos:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(140,8,utf8_decode($_POST['descansos']),1,1);

    // === BLOQUE: Días adicionales ===
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(40,8,'Adicional los dias:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(140,8,utf8_decode($_POST['dias_adicionales']),1,1);

    // === BLOQUE: Horario adicional ===
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(40,8,'En el horario:',1,0);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(140,8,utf8_decode($_POST['horario_adicional']),1,1);

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

    // Mostrar directamente en navegador
    $pdf->Output('I', "cambio_turno.pdf");
}
?>