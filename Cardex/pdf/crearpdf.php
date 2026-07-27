<?php
require('../../fpdf/fpdf.php');
$query=$_GET['query'];
$puesto=$_GET['puesto'];
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png',50,5,120);
$pdf->Ln(25);
$Y = $pdf->GetY();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(10,10,utf8_decode('REPORTE CARDEX POR PUESTO'));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 10 , $Y  );
$pdf->SetFont('Arial','',8);
$pdf->SetFont('Arial','B',12);
$pdf->Ln();
require_once "../../../csql.php";
$query2="SELECT * FROM tblPuestos WHERE id=$puesto";
$result=sqlsrv_query($conn,$query2);
while ($row2 = sqlsrv_fetch_array($result)) {
$pdf->multiCell(200,5,utf8_decode($row2[1]));
}
$pdf->SetFont('Arial','B',8);
$pdf->Cell(10,5,utf8_decode("ID"));
$pdf->Cell(80,5,utf8_decode("Nombre"));
$pdf->Ln();
require_once "../../../csql35.php";
$result=sqlsrv_query($conn,$query);
while ($row2 = sqlsrv_fetch_array($result)) {
$pdf->SetFont('Arial','',8);
$pdf->Cell(10,5,utf8_decode($row2[0]));
$pdf->Cell(80,5,utf8_decode($row2[1]));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 200 , $Y  );
}
$pdf->Ln();
$Y = $pdf->GetY();

$pdf->Output();
?>