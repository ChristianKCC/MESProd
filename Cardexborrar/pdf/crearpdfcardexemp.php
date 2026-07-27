<?php
require('../../fpdf/fpdf.php');
require_once "../../../csql35.php";
$query=$_GET['query'];
$emp=$_GET['emp'];
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png',50,5,120);
$pdf->Ln(25);
$Y = $pdf->GetY();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(10,10,utf8_decode('REPORTE CURSOS POR EMPLEADO'));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 10 , $Y  );
$query2="SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre, tblDepartamentos.NombreDepto as NomDep , tbldep2.NombreDepto as NomDepreal, tblPuestos.nombre as NomPuesto FROM TLX032MXDB.dbo.tblEmpleados LEFT join TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento LEFT join TLX009MXDB.dbo.tblDepartamentos AS tbldep2 on tbldep2.NoDepto=tblEmpleados.NoDeptoReal LEFT join TLX009MXDB.dbo.tblPuestos on tblPuestos.id=tblEmpleados.Puesto WHERE tblEmpleados.NoEmp=$emp";
$result2=sqlsrv_query($conn,$query2);
$pdf->SetFont('Arial','',10);
while ($row = sqlsrv_fetch_array($result2)) {
$pdf->Cell(10,5,utf8_decode($row[0]." - ".$row[1]." - ".$row['NomDep']));
$pdf->Ln();
$pdf->Cell(10,5,utf8_decode($row['NomDepreal']." - ".$row['NomPuesto']));
}
$pdf->Ln(10);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(10,5,utf8_decode("ID"));
$pdf->Cell(100,5,utf8_decode("Curso"));
$pdf->Cell(15,5,utf8_decode("Cap"));
$pdf->Cell(15,5,utf8_decode("IBM"));
$pdf->Cell(15,5,utf8_decode("Calif"));
$pdf->Cell(15,5,utf8_decode("Duración"));
$pdf->Cell(20,5,utf8_decode("Fecha"));
$pdf->Ln();
$result=sqlsrv_query($conn,$query);
while ($row = sqlsrv_fetch_array($result)) {
$pdf->SetFont('Arial','',8);
$pdf->Cell(10,5,utf8_decode($row[0]));
$pdf->Cell(100,5,utf8_decode($row[1]));
$pdf->Cell(15,5,utf8_decode($row[5]));
$pdf->Cell(15,5,utf8_decode($row[6]));
$pdf->Cell(15,5,utf8_decode($row[2]));
$pdf->Cell(15,5,utf8_decode($row[3]));
$pdf->Cell(20,5,utf8_decode($row[4]->format('Y-m-d')));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 200 , $Y  );
}


$pdf->Output();
?>