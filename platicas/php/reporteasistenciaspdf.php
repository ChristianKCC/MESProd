<?php
require('../../fpdf/fpdf.php');
require_once "../../conexion.php";

$conection = new ClassConexion();
$conn = $conection->conexion("TLX002MXDB");

// Validar parámetros
$fechai      = $_POST["fechai"]      ?? null;
$fechaf      = $_POST["fechaf"]      ?? null;
$noemp       = $_POST["noemp"]       ?? null;
$departamento= $_POST["departamento"]?? null;
$maquinas    = $_POST["maquinas"]    ?? null;

// Construcción dinámica de filtros
$whereNoemp        = !empty($noemp)        ? "AND tblEmpleados.NoEmp=".$noemp : "";
$whereDepartamento= !empty($departamento) ? "AND tblEmpleados.NombreDepartamento='".$departamento."'" : "";
$whereMaquinas    = !empty($maquinas)     ? "AND tblPlaticas5minAsistencias.idsession=".$maquinas : "";

// Consulta
$query = "SELECT 
    tblPlaticas5minAsistencias.id,
    tblPlaticas5min.fecha,
    tblPlaticas5min.minutos,
    tblPlaticas5minAsistencias.noemp,
    tblEmpleados.Nombre,
    tblPlaticas5min.nombreplatica
FROM tblPlaticas5min 
INNER JOIN tblPlaticas5minAsistencias 
    ON tblPlaticas5min.id = tblPlaticas5minAsistencias.idplatica
INNER JOIN TLX032MXDB.dbo.tblEmpleados 
    ON tblEmpleados.NoEmp = tblPlaticas5minAsistencias.noemp
WHERE tblPlaticas5min.fecha BETWEEN '".$fechai."' AND '".$fechaf."' 
      $whereNoemp $whereDepartamento $whereMaquinas
ORDER BY tblPlaticas5minAsistencias.noemp ASC";

$result = sqlsrv_query($conn, $query);

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png',10,5,80);
$pdf->Ln(20);

$pdf->SetFont('Arial','B',16);
$pdf->Cell(45,10,"");
$pdf->Cell(10,10,utf8_decode('Reporte platicas de 5 minutos asistencias'));
$pdf->Ln();

$pdf->SetFont('Arial','',10);
$pdf->Cell(100,10,"");
$pdf->Cell(50,10,utf8_decode("Fecha inicial: ".$fechai));
$pdf->MultiCell(50,10,utf8_decode("Fecha final: ".$fechaf));

$pdf->SetFont('Arial','B',11);
$pdf->Cell(15,8,"Noemp");
$pdf->Cell(60,8,"Nombre");
$pdf->Cell(20,8,"Fecha");
$pdf->Cell(20,8,"Tiempo");
$pdf->MultiCell(120,8,"Platica");

$pdf->SetFont('Arial','',8);

$conteo = 0;
while ($fila = sqlsrv_fetch_array($result)) {
    $pdf->Cell(15,5,$fila[3]);
    $pdf->Cell(60,5,utf8_decode($fila[4]));
    $pdf->Cell(20,5,$fila[1]->format("Y-m-d"));
    $pdf->Cell(15,5,"5 min");
    $pdf->MultiCell(120,5,utf8_decode($fila[5]));
    $conteo += 5;
}

$pdf->Ln(20);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(80,10,"Tiempo total: ".$conteo." min");
$pdf->Cell(80,10,"Tiempo total: ".number_format($conteo/60,2)." hrs");

// Ruta de guardado
$dirpath = dirname(__DIR__) . "/reportes"; 
// dirname(__DIR__) = carpeta padre de /php, o sea /platicas
if (!is_dir($dirpath)) {
    mkdir($dirpath, 0777, true);
}
$filename = "ReporteAsistencias.pdf";
$filepath = $dirpath . "/" . $filename;

$pdf->Output('F', $filepath);

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=$filename");
readfile($filepath);
exit;

?>
