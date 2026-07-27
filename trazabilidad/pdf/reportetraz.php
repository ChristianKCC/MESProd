<?php
require('../../fpdf/fpdf.php');
require_once "../../conexion.php";
$conection = new ClassConexion();
$conn = $conection->conexion("TLX002MXDB");
$fechai = $_POST["fechai"];
$turno = $_POST["turno"];
$departamento = $_POST["departamento"];
$maquinas = $_POST["maquinas"];
empty($turno) ? $turno = "" : $turno = " AND turno=" . $turno;
empty($maquinas) ? $maquinas = "" : $maquinas = " AND NoMaquina=" . $_POST["maquinas"];
$query = "SELECT TOP 50 tblRillEnc.*,tblValeEMateriales.NombreMaterial,tblempleados.Nombre as empleadonombre,tblValeEEnc.foliocons as foliocons,
        tblMaquinas.NombreMaquina FROM tblRillEnc
        LEFT JOIN tblValeEEnc ON tblValeEEnc.id = tblRillEnc.foliovalesril
        LEFT JOIN tblValeEMateriales On tblValeEMateriales.NoMaterial = tblRillEnc.material
        INNER JOIN TLX032MXDB.dbo.tblempleados On tblEmpleados.NoEmp = tblRillEnc.noemp 
		INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblRillEnc.sessionmaquina
        WHERE (tblValeEEnc.maquina = $idmaquina OR tblRillEnc.sessionmaquina =  $idmaquina) AND tblRillEnc.fechasave >= '$fechai' AND tblRillEnc.fechasave < DATEADD(day,1,'$fechaf') ORDER BY tblRillEnc.id DESC";
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png', 10, 5, 80);
$pdf->Ln(20);
$Y = $pdf->GetY();
$conteo = 0;
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(125,5);
$pdf->Cell(10, 10, utf8_decode('Reporte de producciones'));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(160,10);
$pdf->MultiCell(50, 10, utf8_decode("Fecha: " . $fechai));
$result = sqlsrv_query($conn, $query);
$senddata = $query;
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 8, utf8_decode("FECHA"));
$pdf->Cell(17, 8, utf8_decode("TURNO "));
$pdf->Cell(28, 8, utf8_decode("MAQUINA "));
$pdf->Cell(20, 8, utf8_decode("CLAVE "));
$pdf->Cell(20, 8, utf8_decode("STD "));
$pdf->Cell(20, 8, utf8_decode("CjsReales "));
$pdf->Cell(20, 8, utf8_decode("GolpesT "));
$pdf->Cell(20, 8, utf8_decode("Merma"));
$pdf->MultiCell(120, 8, utf8_decode("TPT"));
$pdf->SetFont('Arial', '', 7);
while ($fila = sqlsrv_fetch_array($result)) {
    $pdf->Cell(18, 5, utf8_decode($fila['fecha']->format('Y-m-d')));
    $pdf->Cell(13, 5, utf8_decode($fila['turno']));
    $pdf->Cell(30, 5, utf8_decode($fila['maquina']));
    $pdf->Cell(20, 5, utf8_decode($fila['NoClave']));
    $pdf->Cell(20, 5, utf8_decode($fila['std']));
    $pdf->Cell(20, 5, utf8_decode($fila['cajasreales']));
    $pdf->Cell(20, 5, utf8_decode($fila['golpestotales']));
    $pdf->Cell(20, 5, utf8_decode(number_format($fila['merma'])));
    $pdf->MultiCell(120, 5, utf8_decode($fila['tpt']));
}

$pdf->Cell(50, 10, "");
$Y = $pdf->GetY();
$pdf->Output();
