<?php
require('../../fpdf/fpdf.php');
require_once "../../conexion.php";
$conection = new ClassConexion();
$conn=$conection->conexion("TLX002MXDB");
$fechai=$_POST["fechai"];
$fechaf=$_POST["fechaf"];
$noemp=$_POST["noemp"];
$departamento=$_POST["departamento"];
$maquinas=$_POST["maquinas"];
empty($noemp) ? $noemp="" : $noemp = "AND tblEmpleados.NoEmp=".$_POST["noemp"];
empty($departamento) ? $departamento = "" : $departamento = "AND tblEmpleados.NombreDepartamento=".$_POST["departamento"];
empty($maquinas) ? $maquinas = "" : $maquinas = "AND tblPlaticas5minAsistencias.idsession=".$_POST["maquinas"];
$query="SELECT tblPlaticas5minAsistencias.id,tblPlaticas5min.fecha,tblPlaticas5min.minutos,tblPlaticas5minAsistencias.noemp,
tblEmpleados.Nombre,tblPlaticas5min.nombreplatica FROM tblPlaticas5min 
INNER JOIN tblPlaticas5minAsistencias ON tblPlaticas5min.id = tblPlaticas5minAsistencias.idplatica
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblPlaticas5minAsistencias.noemp WHERE tblPlaticas5min.fecha BETWEEN '".$fechai."' AND '".$fechaf."' $noemp $departamento $maquinas ORDER BY tblPlaticas5minAsistencias.noemp ASC";
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png',10,5,80);
$pdf->Ln(20);
$Y = $pdf->GetY();
$conteo=0;
$pdf->SetFont('Arial','B',16);
$pdf->Cell(45,10,"");
$pdf->Cell(10,10,utf8_decode('Reporte platicas de 5 minutos asistencias'));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,10,utf8_decode(""));
$pdf->Cell(50,10,utf8_decode("Fecha inicial: ".$fechai));
$pdf->MultiCell(50,10,utf8_decode("Fecha final: ".$fechaf));
$result=sqlsrv_query($conn,$query);
$senddata=$query;
$pdf->SetFont('Arial','B',11);
$pdf->Cell(15,8,utf8_decode("Noemp "));
$pdf->Cell(60,8,utf8_decode("Nombre "));
$pdf->Cell(16,8,utf8_decode("Fecha "));
$pdf->Cell(20,8,utf8_decode("Tiempo "));
$pdf->MultiCell(120,8,utf8_decode("Platica "));
$pdf->SetFont('Arial','',8);
while ($fila = sqlsrv_fetch_array($result)) {
    $pdf->Cell(15,5,utf8_decode($fila[3]));
    $pdf->Cell(60,5,utf8_decode($fila[4]));
    $pdf->Cell(20,5,$fila[1]->format("Y-m-d"));
    $pdf->Cell(15,5,"5 min");
    $pdf->MultiCell(120,5,utf8_decode($fila[5]));
    $conteo=$conteo+5;

}
$pdf->Ln(20);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(80,10,"Tiempo total: " .$conteo ."min");
$conteo = $conteo / 60;
$pdf->Cell(80,10,"Tiempo total: " .number_format($conteo,2) ."hrs");
$pdf->Ln(40);
$pdf->Cell(50,10,"");
$Y = $pdf->GetY();
$pdf->Output();
?>