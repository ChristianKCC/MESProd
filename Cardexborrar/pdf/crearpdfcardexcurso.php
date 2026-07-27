<?php
require('../../fpdf/fpdf.php');
require_once "../../../csql35.php";
$query=$_GET['query'];
$idcurso=$_GET['idcurso'];
$fechai=$_GET['fechai'];
$fechaf=$_GET['fechaf'];
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png',50,5,115);
$pdf->Ln(25);
$Y = $pdf->GetY();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(10,10,utf8_decode('REPORTE CARDEX DE EMPLEADOS POR CURSO'));
$pdf->Ln(8);
$pdf->SetFont('Arial','',10);
$pdf->Cell(10,10,utf8_decode('De: '.$fechai.' A '.$fechaf));
$pdf->Ln(8);
$Y = $pdf->GetY();
if($idcurso != ''){
$query2="SELECT NombreCurso FROM tblCursos WHERE IdCurso=$idcurso";
$result2=sqlsrv_query($conn,$query2);
$pdf->SetFont('Arial','B',14);
while ($row = sqlsrv_fetch_array($result2))
$pdf->Cell(10,5,utf8_decode($row[0]));
}
$pdf->Ln(10);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(10,5,utf8_decode("Noemp"));
$pdf->Cell(80,5,utf8_decode("Nombre"));
$pdf->Cell(20,5,utf8_decode("Cap"));
$pdf->Cell(20,5,utf8_decode("Calificación"));
$pdf->Cell(20,5,utf8_decode("Duración"));
$pdf->Cell(20,5,utf8_decode("Fecha"));
$pdf->Cell(20,5,utf8_decode("Instructor"));
$pdf->Ln();
$result=sqlsrv_query($conn,$query);
while ($row = sqlsrv_fetch_array($result)) {
$pdf->SetFont('Arial','',8);
$pdf->Cell(10,5,utf8_decode($row[0]));
$pdf->Cell(80,5,utf8_decode($row[1]));
$pdf->Cell(20,5,utf8_decode($row[5]));
$pdf->Cell(20,5,utf8_decode($row[2]));
$pdf->Cell(20,5,utf8_decode($row[3]));
$pdf->Cell(20,5,utf8_decode($row[4]->format('Y-m-d')));
$pdf->Cell(20,5,utf8_decode($row[8]));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 200 , $Y  );
}


$pdf->Output();
?>