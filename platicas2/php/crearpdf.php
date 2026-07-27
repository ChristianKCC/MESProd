<?php
require('../../fpdf/fpdf.php');
require_once "../../conexion.php";
$conection = new ClassConexion();
$conn=$conection->conexion("TLX002MXDB");
$fechai=$_POST["fechai"];
$fechaf=$_POST["fechaf"];
$query="SELECT tblDepartamentos.NombreDepto,COUNT(*) FROM tblPlaticas5minAsistencias
INNER JOIN tblPlaticas5min ON tblPlaticas5min.id = tblPlaticas5minAsistencias.idplatica
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp= tblPlaticas5minAsistencias.noemp
INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento
WHERE tblPlaticas5min.fecha BETWEEN '".$fechai."' AND '".$fechaf."' GROUP BY tblDepartamentos.NombreDepto";
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png',10,5,80);
$pdf->Ln(20);
$Y = $pdf->GetY();

$pdf->SetFont('Arial','B',16);
$pdf->Cell(55,10,"");
$pdf->Cell(10,10,utf8_decode('Reporte platicas de 5 Minutos'));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,10,utf8_decode(""));
$pdf->Cell(50,10,utf8_decode("Fecha inicial: ".$fechai));
$pdf->MultiCell(50,10,utf8_decode("Fecha final: ".$fechaf));
$result=sqlsrv_query($conn,$query);
$senddata=$query;
$pdf->SetFont('Arial','B',11);
$pdf->Cell(50,10,utf8_decode("Departamento: "));
$pdf->Cell(50,10,utf8_decode("No. Personas: "));
$pdf->Cell(50,10,utf8_decode("Minutos: "));
$pdf->MultiCell(50,10,utf8_decode("Total hrs: "));
$pdf->SetFont('Arial','',8);
while ($fila = sqlsrv_fetch_array($result)) {
$pdf->Cell(50,10,utf8_decode($fila[0]));
$pdf->Cell(50,10,utf8_decode($fila[1]));
$pdf->Cell(50,10,($fila[1]*5));
$pdf->MultiCell(50,10,number_format(((($fila[1]*5)/60)*$fila[1]),2));

}
$pdf->Ln(40);
$pdf->Cell(50,10,"");
$Y = $pdf->GetY();
$pdf->Output();
?>