<?php
// Creacion de tarjetas para asistencias externas
// Dividir responsabilidades entre clases como en crearTarjeta y TarjetaPDF
require_once('../../fpdf/fpdf.php');
require_once('../../conexion.php');
$conection = new ClassConexion();
$conn = $conection->conexion("TLX002MXDB");
$fechai = $_POST['fechai'];
$tipemp = $_POST['tipemp'];
$ctrocstos = $_POST['ctrocstos'];
$departamento = $_POST['departamento'];
$departamento == '' ? $departamento = '' : $departamento = " AND tblEmpleados.NombreDepartamento=" . $_POST["departamento"];
$tipemp === '' ? $tipemp = "" : $tipemp = " AND tblEmpleados.EmpleadoSindicalizado = " . $tipemp;
$ctrocstos == '' ? $ctrocstos = "" : $ctrocstos = " AND tblEmpleados.IdCentroCosto = " . $ctrocstos;
$fechaf = date("Y-m-d", strtotime($fechai . "+ 6 days"));
$query = "SELECT DISTINCT tblEmpleados.NoEmp,tblEmpleados.Nombre, tblPuestos.nombre as Puesto,tblDepartamentos.clave, DATEPART(wk, '" . $fechai . "') as numsem from TLX032MXDB.dbo.tblEmpleados 
INNER JOIN tblSistenciasbiostar ON tblEmpleados.NoEmp = tblSistenciasbiostar.noemp
INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id= tblEmpleados.Puesto
INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto= tblEmpleados.NombreDepartamento WHERE tblEmpleados.Bajas=0 AND tblEmpleados.NoEmp>100 $tipemp $ctrocstos $departamento ORDER BY tblEmpleados.NoEmp ASC";
$pdf = new FPDF('P');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->Ln(20);
$pdf->SetFont('Arial', '', 8);
$x = 2;
$y = 2;
$cont = 0;
$result = sqlsrv_query($conn, $query);
while ($fila = sqlsrv_fetch_array($result)) {
	$pdf->rect($x, $y, 205, 142);
	$pdf->Image('../../img/logo.jpg', $x + 5, $y + 5, 60);
	$pdf->SetXY($x + 5, $y + 10);
	$pdf->Cell(15, 10, "DEPTO:");
	$pdf->Cell(20, 10, $fila[3]);
	$pdf->Cell(15, 10, "NUM:");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell(110, 10, "0" . $fila[0]);
	$pdf->Cell(20, 10, "NUM:");
	$pdf->Cell(20, 10, $fila[0]);
	$pdf->SetFont('Arial', '', 8);
	$pdf->Ln(5);
	$pdf->SetX($x + 5);
	$pdf->Cell(20, 10, utf8_decode($fila[1]));
	$pdf->Ln(5);
	$pdf->SetXY($x + 180, $y + 5);
	$pdf->Cell(20, 10, "NOMINA NO." . ($fila[4] + 1));
	$pdf->Ln(15);
	$pdf->SetX($x);
	$pdf->Cell(20, 10, "DEL   $fechai   AL   $fechaf");
	$pdf->Ln(5);
	$pdf->SetX($x + 5);
	$pdf->Cell(20, 10, "14");
	$pdf->SetX($x + 30);
	$pdf->Cell(20, 10, "020006");
	$pdf->SetX($x + 60);
	$pdf->Cell(20, 10, "87");
	$pdf->Ln(5);
	$pdf->SetX($x + 5);
	$pdf->Cell(20, 10, utf8_decode($fila[2]));
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

	$pdf->rect($x + 90, $y + 20, 110, 120);
	$pdf->SetXY($x + 90, $y + 20);
	$pdf->Cell(2, 5, "");
	$pdf->Cell(22, 5, "CONCEPTO");
	$pdf->Cell(20, 5, "# DIAS");
	$pdf->Cell(16, 5, "# HRS");
	$pdf->Cell(22, 5, "CAMBIO PROV");
	$pdf->Cell(20, 5, "OBSERVACIONES");
	$pdf->SetXY($x + 152, $y + 25);
	$pdf->Cell(20, 5, "AL PUESTO");
	// vertical
	$pdf->line($x + 110, $y + 20, $x + 110, $y + 140);
	$pdf->line($x + 130, $y + 20, $x + 130, $y + 140);
	$pdf->line($x + 150, $y + 20, $x + 150, $y + 140);
	$pdf->line($x + 170, $y + 20, $x + 170, $y + 140);

	// horizontal
	for ($sub = 30; $sub <= 130; $sub = $sub + 10)
		$pdf->line($x + 90, $y + $sub, $x + 200, $y + $sub);

	$pdf->SetFont('Arial', '', 7);
	$horasx = $x;
	$horasy = $y;
	$query2 = "WITH cte AS (
	SELECT *,
	   ROW_NUMBER() OVER (PARTITION BY noemp, DATEADD(hour, DATEDIFF(hour, 0, fecha), 0) ORDER BY fecha) AS rn
	FROM tblSistenciasbiostar
	WHERE noemp = $fila[0]
 )
 SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre,fecha,DATEPART(WEEKDAY, fecha)
 FROM cte
 INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = cte.noemp
 WHERE rn = 1
 AND NOT EXISTS (
	SELECT *
	FROM cte c2
	WHERE c2.id = cte.id
	AND c2.rn = 1
	AND c2.fecha  <> cte.fecha
	AND DATEDIFF(hour, c2.fecha, cte.fecha) BETWEEN 0 AND 1
 )  AND CONVERT(date,cte.fecha) BETWEEN '$fechai' AND '$fechaf' ORDER BY fecha Asc";
	$result2 = sqlsrv_query($conn, $query2);
	$contavance = 0;
	$horasx = 12;
	$antrow = 0;
	while ($row = sqlsrv_fetch_array($result2)) {
		$hora = $row[2]->format("H:i:s");
		if ($row[3] == $antrow) {
			$contavance++;
		} else {
			$contavance = 0;
			$horasx = 12;
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

	$pdf->SetFont('Arial', 'B', 10);
	$queryadddescansos = "SELECT tblAsistenciasDescansos.*,tblAsistenciaDescansosTipo.nombre as l,mart.Nombre as m
	,mierc.Nombre as mi,juev.Nombre as j,vierne.Nombre as v,sabad.Nombre as s,dom.Nombre as d
	 FROM TLX002MXDB.dbo.tblAsistenciasDescansos
	LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo ON tblAsistenciasDescansos.lunes = tblAsistenciaDescansosTipo.id
	LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo AS mart ON tblAsistenciasDescansos.martes = mart.id
	LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo AS mierc ON tblAsistenciasDescansos.miercoles = mierc.id
	LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo AS juev ON tblAsistenciasDescansos.jueves = juev.id
	LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo AS vierne ON tblAsistenciasDescansos.viernes = vierne.id
	LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo AS sabad ON tblAsistenciasDescansos.sabado = sabad.id
	LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo AS dom ON tblAsistenciasDescansos.domingo = dom.id 
	WHERE tblAsistenciasDescansos.fecha BETWEEN '$fechai' AND '$fechaf' AND tblAsistenciasDescansos.noemp = $fila[0]";
	$resultdesc = sqlsrv_query($conn, $queryadddescansos);
	while ($row = sqlsrv_fetch_array($resultdesc)) {
		if ($row[3] >= 1) {
			$pdf->SetXY(60, $horasy + 70);
			$pdf->Cell(15, 5, $row[10]);
		}
		if ($row[4] >= 1) {
			$pdf->SetXY(60, $horasy + 80);
			$pdf->Cell(15, 5, $row[11]);
		}
		if ($row[5] >= 1) {
			$pdf->SetXY(60, $horasy + 90);
			$pdf->Cell(15, 5, $row[12]);
		}
		if ($row[6] >= 1) {
			$pdf->SetXY(60, $horasy + 100);
			$pdf->Cell(15, 5, $row[13]);
		}
		if ($row[7] >= 1) {
			$pdf->SetXY(60, $horasy + 110);
			$pdf->Cell(15, 5, $row[14]);
		}
		if ($row[8] >= 1) {
			$pdf->SetXY(60, $horasy + 120);
			$pdf->Cell(15, 5, $row[15]);
		}
		if ($row[9] >= 1) {
			$pdf->SetXY(60, $horasy + 130);
			$pdf->Cell(15, 5, $row[16]);
		}
	}
	$y = $y + 150;
	$cont++;
	if ($cont % 2 === 0) {
		$pdf->AddPage();
		$y = 2;
	}
}



$Y = $pdf->GetY();
$pdf->Output();
