<?php
require_once('../../fpdf/fpdf.php');
require_once('../../conexion.php');

$conection = new ClassConexion();
$conn = $conection->conexion("TLX001MXDB");

$fechai = $_POST['fechai'];
$tipemp = $_POST['tipemp'];
$ctrocstos = $_POST['ctrocstos'];
$departamento = $_POST['departamento'];

$departamento == '' ? $departamento = '' : $departamento = " AND tblEmpleados.NombreDepartamento=" . $_POST["departamento"];
$tipemp === '' ? $tipemp = "" : $tipemp = " AND tblEmpleados.EmpleadoSindicalizado = " . $tipemp;
$ctrocstos == '' ? $ctrocstos = "" : $ctrocstos = " AND tblEmpleados.IdCentroCosto = " . $ctrocstos;

$fechaf = date("Y-m-d", strtotime($fechai . "+ 6 days"));

// 🔹 Arreglo de rangos de nómina
$rangos = [
    ["nomina" => 1, "inicio" => "2025-12-29", "fin" => "2026-01-04"],
    ["nomina" => 2, "inicio" => "2026-01-05", "fin" => "2026-01-11"],
    ["nomina" => 3, "inicio" => "2026-01-12", "fin" => "2026-01-18"],
    ["nomina" => 4, "inicio" => "2026-01-19", "fin" => "2026-01-25"],
    ["nomina" => 5, "inicio" => "2026-01-26", "fin" => "2026-02-01"],
    ["nomina" => 6, "inicio" => "2026-02-02", "fin" => "2026-02-08"],
    ["nomina" => 7, "inicio" => "2026-02-09", "fin" => "2026-02-15"],
    ["nomina" => 8, "inicio" => "2026-02-16", "fin" => "2026-02-22"],
    ["nomina" => 9, "inicio" => "2026-02-23", "fin" => "2026-03-01"],
    ["nomina" => 10, "inicio" => "2026-03-02", "fin" => "2026-03-08"],
    ["nomina" => 11, "inicio" => "2026-03-09", "fin" => "2026-03-15"],
    ["nomina" => 12, "inicio" => "2026-03-16", "fin" => "2026-03-22"],
    ["nomina" => 13, "inicio" => "2026-03-23", "fin" => "2026-03-29"],
    ["nomina" => 14, "inicio" => "2026-03-30", "fin" => "2026-04-05"],
    ["nomina" => 15, "inicio" => "2026-04-06", "fin" => "2026-04-12"],
    ["nomina" => 16, "inicio" => "2026-04-13", "fin" => "2026-04-19"],
    ["nomina" => 17, "inicio" => "2026-04-20", "fin" => "2026-04-26"],
    ["nomina" => 18, "inicio" => "2026-04-27", "fin" => "2026-05-03"],
    ["nomina" => 19, "inicio" => "2026-05-04", "fin" => "2026-05-10"],
    ["nomina" => 20, "inicio" => "2026-05-11", "fin" => "2026-05-17"],
    ["nomina" => 21, "inicio" => "2026-05-18", "fin" => "2026-05-24"],
    ["nomina" => 22, "inicio" => "2026-05-25", "fin" => "2026-05-31"],
    ["nomina" => 23, "inicio" => "2026-06-01", "fin" => "2026-06-07"],
    ["nomina" => 24, "inicio" => "2026-06-08", "fin" => "2026-06-14"],
    ["nomina" => 25, "inicio" => "2026-06-15", "fin" => "2026-06-21"],
    ["nomina" => 26, "inicio" => "2026-06-22", "fin" => "2026-06-28"],
    ["nomina" => 27, "inicio" => "2026-06-29", "fin" => "2026-07-05"],
    ["nomina" => 28, "inicio" => "2026-07-06", "fin" => "2026-07-12"],
    ["nomina" => 29, "inicio" => "2026-07-13", "fin" => "2026-07-19"],
    ["nomina" => 30, "inicio" => "2026-07-20", "fin" => "2026-07-26"],
    ["nomina" => 31, "inicio" => "2026-07-27", "fin" => "2026-08-02"],
    ["nomina" => 32, "inicio" => "2026-08-03", "fin" => "2026-08-09"],
    ["nomina" => 33, "inicio" => "2026-08-10", "fin" => "2026-08-16"],
    ["nomina" => 34, "inicio" => "2026-08-17", "fin" => "2026-08-23"],
    ["nomina" => 35, "inicio" => "2026-08-24", "fin" => "2026-08-30"],
    ["nomina" => 36, "inicio" => "2026-08-31", "fin" => "2026-09-06"],
    ["nomina" => 37, "inicio" => "2026-09-07", "fin" => "2026-09-13"],
    ["nomina" => 38, "inicio" => "2026-09-14", "fin" => "2026-09-20"],
    ["nomina" => 39, "inicio" => "2026-09-21", "fin" => "2026-09-27"],
    ["nomina" => 40, "inicio" => "2026-09-28", "fin" => "2026-10-04"],
    ["nomina" => 41, "inicio" => "2026-10-05", "fin" => "2026-10-11"],
    ["nomina" => 42, "inicio" => "2026-10-12", "fin" => "2026-10-18"],
    ["nomina" => 43, "inicio" => "2026-10-19", "fin" => "2026-10-25"],
    ["nomina" => 44, "inicio" => "2026-10-26", "fin" => "2026-11-01"],
    ["nomina" => 45, "inicio" => "2026-11-02", "fin" => "2026-11-08"],
    ["nomina" => 46, "inicio" => "2026-11-09", "fin" => "2026-11-15"],
    ["nomina" => 47, "inicio" => "2026-11-16", "fin" => "2026-11-22"],
    ["nomina" => 48, "inicio" => "2026-11-23", "fin" => "2026-11-29"],
    ["nomina" => 49, "inicio" => "2026-11-30", "fin" => "2026-12-06"],
    ["nomina" => 50, "inicio" => "2026-12-07", "fin" => "2026-12-13"],
    ["nomina" => 51, "inicio" => "2026-12-14", "fin" => "2026-12-20"],
    ["nomina" => 52, "inicio" => "2026-12-21", "fin" => "2026-12-27"],
];

// 🔹 Función para obtener nómina según fecha
function obtenerNominaPorFecha($fecha, $rangos)
{
    $fecha = date('Y-m-d', strtotime($fecha));
    foreach ($rangos as $rango) {
        if ($fecha >= $rango['inicio'] && $fecha <= $rango['fin']) {
            return $rango['nomina'];
        }
    }
    return 'N/A';
}

$query = "SELECT DISTINCT NoEmp,tblEmpleados.Nombre, tblPuestos.nombre as Puesto,tblDepartamentos.clave, NoDepto 
FROM TLX032MXDB.dbo.tblEmpleados 
INNER JOIN acc_transaction ON tblEmpleados.NoEmp = acc_transaction.pin
INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id= tblEmpleados.Puesto
INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto= tblEmpleados.NombreDepartamento
WHERE tblEmpleados.Bajas=0 $tipemp $ctrocstos $departamento
ORDER BY NoEmp ASC";

$pdf = new FPDF('P');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);

$x = 2;
$y = 2;
$cont = 0;

$result = sqlsrv_query($conn, $query);

while ($fila = sqlsrv_fetch_array($result)) {

    // 📌 Calcula nómina según la fecha de inicio del POST
    $nomina = obtenerNominaPorFecha($fechai, $rangos);

    $pdf->Rect($x, $y, 205, 142);
    $pdf->Image('../../img/logo.jpg', $x + 5, $y + 5, 60);

    $pdf->SetXY($x + 5, $y + 10);
    $pdf->Cell(15, 10, "DEPTO:");
    $pdf->Cell(20, 10, $fila[3]);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(110, 10, "0" . $fila[0]);

    $pdf->SetFont('Arial', '', 8);
    $pdf->Ln(5);
    $pdf->SetX($x + 5);
    $pdf->Cell(20, 10, utf8_decode($fila[1]));

    $pdf->SetXY($x + 180, $y + 5);
    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(20, 10, "NOMINA NO. " . $nomina);

    $pdf->Ln(15);
    $pdf->SetX($x);
    $pdf->Cell(20, 10, "DEL   $fechai   AL   $fechaf");

    // Avance de hojas
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
	   ROW_NUMBER() OVER (PARTITION BY pin, DATEADD(hour, DATEDIFF(hour, 0, event_time), 0) ORDER BY event_time) AS rn
	FROM acc_transaction
	WHERE pin = $fila[0]
 )
 SELECT NoEmp,Nombre,event_time,DATEPART(WEEKDAY, event_time)
 FROM cte
 INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = cte.pin
 WHERE rn = 1
 AND NOT EXISTS (
	SELECT *
	FROM cte c2
	WHERE c2.id = cte.id
	AND c2.rn = 1
	AND c2.event_time  <> cte.event_time
	AND DATEDIFF(hour, c2.event_time, cte.event_time) BETWEEN 0 AND 1
 )  AND CONVERT(date,cte.event_time) BETWEEN '$fechai' AND '$fechaf' ORDER BY event_time Asc";
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

?>