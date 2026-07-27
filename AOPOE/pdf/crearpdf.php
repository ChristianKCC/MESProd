<?php
require('../../fpdf/fpdf.php');
require_once "../../../csql35.php";
$query=$_GET['query'];
$query=base64_decode($query);
$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png',50,5,120);
$pdf->Ln(25);
$Y = $pdf->GetY();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(10,10,utf8_decode('REPORTE IT Y OPT DE EMPLEADOS'));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 10 , $Y  );
$pdf->SetFont('Arial','B',14);
$pdf->Ln(10);
$pdf->SetFont('Arial','B',6);
$pdf->Cell(10,5,utf8_decode("Folio"));
$pdf->Cell(10,5,utf8_decode("No Emp"));
$pdf->Cell(42,5,utf8_decode("Nombre"));
$pdf->Cell(15,5,utf8_decode("Departamento"));
$pdf->Cell(15,5,utf8_decode("Maquina"));
$pdf->Cell(18,5,utf8_decode("Id AOPOE"));
$pdf->Cell(30,5,utf8_decode("Procedimiento"));
$pdf->Cell(20,5,utf8_decode("Criticidad"));
$pdf->Cell(20,5,utf8_decode("Fecha"));
$pdf->Cell(12,5,utf8_decode("Duración"));
$pdf->Cell(15,5,utf8_decode("Tipo"));
$pdf->Cell(10,5,utf8_decode("IBM"));
$pdf->Cell(42,5,utf8_decode("Capacitador"));
$pdf->multiCell(25,5,utf8_decode("Observaciones"));
$pdf->Ln();
$result=sqlsrv_query($conn,$query);
while ($row = sqlsrv_fetch_array($result)) {
$pdf->SetFont('Arial','',6);
$pdf->Cell(10,5,utf8_decode($row[0]));
$pdf->Cell(10,5,utf8_decode($row[1]));
$pdf->Cell(42,5,utf8_decode($row[2]));
$pdf->Cell(15,5,utf8_decode($row[3]));
$pdf->Cell(15,5,utf8_decode($row[4]));
$pdf->Cell(18,5,utf8_decode($row[5]));
$Saltoy = $pdf->GetY();
$Saltox = $pdf->GetX();
$pdf->multiCell(30,5,utf8_decode($row[6]));
$Saltoy1 = $pdf->GetY();
$pdf->SetXY($Saltox+30,$Saltoy);
$pdf->Cell(20,5,utf8_decode($row[7]));
$pdf->Cell(20,5,utf8_decode($row[10]->format('Y-m-d')));
$pdf->Cell(12,5,utf8_decode($row[11]));
$pdf->Cell(15,5,utf8_decode($row[12]));
$pdf->Cell(10,5,utf8_decode($row[8]));
$pdf->Cell(42,5,utf8_decode($row[9]));
$pdf->multiCell(25,5,utf8_decode($row[13]));
$pdf->SetY($Saltoy1);
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 290 , $Y  );
}


$pdf->Output();
?>