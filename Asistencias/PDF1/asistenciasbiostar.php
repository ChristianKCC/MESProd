<?php
require_once('../../fpdf/fpdf.php');
require_once('../../conexion.php');
$conection = new ClassConexion();
$conn = $conection->conexion("TLX002MXDB");
$fechai = $_POST['fechai'];
$fechaf = date("Y-m-d", strtotime($fechai . "+ 6 days"));
$query = "SELECT DISTINCT tblEmpleadosExternos.id,tblEmpleadosExternos.nombres,tblEmpleadosExternos.app,tblEmpleadosExternos.apm,DATEPART(wk, '" . $fechai . "') as numsem from TLX032MXDB.dbo.tblEmpleadosExternos 
INNER JOIN TLX002MXDB.dbo.tblSistenciasbiostar ON tblEmpleadosExternos.id = tblSistenciasbiostar.noemp";
// echo $query;
// $pdf = new FPDF('L'); Horizontal
$pdf = new FPDF('P');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->Ln(20);
$pdf->SetFont('Arial', '', 8);
// Tarjeta
$x = 2;
$y = 2;
$cont = 0;
$result = sqlsrv_query($conn, $query);
while ($fila = sqlsrv_fetch_array($result)) {
	$pdf->rect($x, $y, 90, 142);
	$pdf->SetXY($x, $y + 5);
	$pdf->SetFont('Arial', 'B', 15);
	$pdf->multiCell(80, 5, "Bennetts");
	$pdf->SetXY($x + 5, $y + 10);
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell(15, 10, "NUM:");
	$pdf->Cell(110, 10, "0" . $fila[0]);
	$pdf->Ln(5);
	$pdf->SetX($x + 5);
	$pdf->Cell(20, 10, utf8_decode($fila['nombres'] . ' ' . $fila['app'] . ' ' . $fila['apm']));
	// $pdf->Ln(5);
	// $pdf->SetXY($x+180,$y+5);
	// $pdf->Ln(15);$x
	$pdf->Ln(5);
	$pdf->SetX($x + 5);
	$pdf->SetFont('Arial', '', 8);
	$pdf->Cell($x, 10, "DEL   $fechai   AL   $fechaf");
	$pdf->Ln(5);

	$pdf->SetX($x + 5);
	// $pdf->Cell(20,10,utf8_decode($fila[2]));
	$pdf->rect($x + 5, $y + 50, 80, 90);
	$pdf->SetXY($x + 5, $y + 50);

	$pdf->SetFont('Arial', 'B', 7);
	$pdf->multiCell(2, 5, "DIA L M M J V S D");
	$pdf->SetXY($x + 10, $y + 50);
	$pdf->Cell(15, 5, "I TURNO");
	$pdf->Cell(15, 5, "I TURNO");
	$pdf->Cell(24, 5, "III TURNO");
	$yi = $pdf->GETY();
	$xi = $pdf->GETX();
	$pdf->multiCell(20, 20, "TIEMPOS");
	$pdf->SETY($yi);
	$pdf->SETX($xi + 15);
	$pdf->SetXY($x + 10, $y + 55);
	$pdf->Cell(15, 5, "ENTRADA");
	$pdf->Cell(15, 5, "SALIDA");
	$pdf->Cell(15, 5, "ENTRADA");
	$pdf->SetXY($x + 10, $y + 60);
	$pdf->Cell(15, 5, "III TURNO");
	$pdf->Cell(15, 5, "II TURNO");
	$pdf->Cell(15, 5, "II TURNO");
	$pdf->SetXY($x + 10, $y + 65);
	$pdf->Cell(15, 5, "SALIDA");
	$pdf->Cell(15, 5, "ENTRADA");
	$pdf->Cell(15, 5, "SALIDA");
	$pdf->line($x + 10, $y + 55, $x + 55, $y + 55);
	$pdf->line($x + 10, $y + 60, $x + 55, $y + 60);
	$pdf->line($x + 10, $y + 65, $x + 55, $y + 65);
	$pdf->line($x + 5, $y + 70, $x + 85, $y + 70);
	$pdf->line($x + 5, $y + 80, $x + 85, $y + 80);
	$pdf->line($x + 5, $y + 90, $x + 85, $y + 90);
	$pdf->line($x + 5, $y + 100, $x + 85, $y + 100);
	$pdf->line($x + 5, $y + 110, $x + 85, $y + 110);
	$pdf->line($x + 5, $y + 120, $x + 85, $y + 120);
	$pdf->line($x + 5, $y + 130, $x + 85, $y + 130);
	$pdf->line($x + 10, $y + 50, $x + 10, $y + 140);
	$pdf->line($x + 25, $y + 50, $x + 25, $y + 140);
	$pdf->line($x + 40, $y + 50, $x + 40, $y + 140);
	$pdf->line($x + 55, $y + 50, $x + 55, $y + 140);
	$pdf->SetFont('Arial', '', 7);
	$horasx = $x;
	$horasy = $y;
	$query2 = "WITH cte AS (
	SELECT *,
	   ROW_NUMBER() OVER (PARTITION BY noemp, DATEADD(hour, DATEDIFF(hour, 0, fecha), 0) ORDER BY fecha) AS rn
	FROM tblSistenciasbiostar
	WHERE noemp = $fila[0]
 )
 SELECT tblEmpleadosExternos.id,tblEmpleadosExternos.nombres,cte.fecha,DATEPART(WEEKDAY, fecha)
 FROM cte
 INNER JOIN TLX032MXDB.dbo.tblEmpleadosExternos ON tblEmpleadosExternos.id = cte.noemp
 WHERE rn = 1
 AND NOT EXISTS (
	SELECT *
	FROM cte c2
	WHERE c2.id = cte.id
	AND c2.rn = 1
	AND c2.fecha  <> cte.fecha
	AND DATEDIFF(hour, c2.fecha, cte.fecha) BETWEEN 0 AND 1
 )  AND CONVERT(date,cte.fecha) BETWEEN '$fechai' AND '$fechaf' ORDER BY fecha Asc";
	//  echo $query2;
	$result2 = sqlsrv_query($conn, $query2);
	if ($result2 === false) {
		die(print_r(sqlsrv_errors(), true));
	}
	$contavance = 0;
	$horasx = $x;
	$antrow = 0;
	while ($row = sqlsrv_fetch_array($result2)) {
		$hora = $row[2]->format("H:i:s");
		if ($row[3] == $antrow) {
			$contavance++;
		} else {
			$contavance = 0;
			$horasx = $x + 12;
		}
		$contavance > 0 ? $horasx = $horasx + 15 : NULL;

		if ($row[3] == 2) {
			$pdf->SetXY($horasx, $horasy + 70);
		} else if ($row[3] == 3) {
			$pdf->SetXY($horasx, $horasy + 80);
		} else if ($row[3] == 4) {
			$pdf->SetXY($horasx, $horasy + 90);
		} else if ($row[3] == 5) {
			$pdf->SetXY($horasx, $horasy + 100);
		} else if ($row[3] == 6) {
			$pdf->SetXY($horasx, $horasy + 110);
		} else if ($row[3] == 7) {
			$pdf->SetXY($horasx, $horasy + 120);
		} else if ($row[3] == 1) {
			$pdf->SetXY($horasx, $horasy + 130);
		}
		$pdf->Cell(15, 5, $row[2]->format("H:i:s"));
		$antrow = $row[3];
	}

	$x = $x + 100;
	$cont++;
	if ($cont % 4 == 0) {
		$pdf->AddPage();
		$x = 2;
		$y = 2;
	}
	else if ($cont % 2 === 0) {
		$y = $y + 150;
		$x = 2;
	} 
}



$Y = $pdf->GetY();
$pdf->Output();
