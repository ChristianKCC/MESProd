<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
require_once("consultaTurnosMaquinas.php");

$turnosObj = new Turnos();
$data = $turnosObj->getTurnosMaquinas();
// echo json_encode($data);


class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 5, 5, 60);
    }
}
$pdf = new PDF();
$pdf->AddPage('L');
//------------------------------------------- Coordenadas --------------------------------------------------------
$x = $pdf->GetX();
$y = $pdf->GetY();
//------------------------------------------- Coordenadas --------------------------------------------------------
//-------------------------------------------- Cuerpo del reporte ------------------------------------------------

$pdf->SetTextColor(0, 0, 255);
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetXY(130, $y + 10);
// $pdf->SetXY($x+15,$y+15);
$pdf->Cell(40, 5, 'Datos máquinas turnos anteriores', 0, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetDrawColor(0, 0, 255);
$pdf->Rect(10, $y + 20, 281, 60);
$pdf->SetFont('Arial', 'B', 8);
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY(18, $y + 20);
$pdf->Cell(15, 10, mb_convert_encoding('Máquina', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 26, $y + 20);
$pdf->Cell(30, 10, 'Cortes', 0, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 53, $y + 20);
$pdf->Cell(30, 10, 'Rechazos', 0, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 83, $y + 20);
$pdf->MultiCell(20, 5, 'Tiempo Abajo', 0, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 106, $y + 20);
$pdf->MultiCell(24, 5, 'Minutos Enhebrando', 0, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 132, $y + 20);
$pdf->MultiCell(20, 5, 'Tiempo Arriba', 0, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 158, $y + 20);
$pdf->MultiCell(22, 5, mb_convert_encoding('Merma máquina', 'ISO-8859-1', 'UTF-8'), 0, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 182, $y + 20);
$pdf->MultiCell(22, 5, 'Tiempo Perdido', 0, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 206, $y + 20);
$pdf->MultiCell(20, 5, mb_convert_encoding('Paros máquina', 'ISO-8859-1', 'UTF-8'), 0, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 220, $y + 20);
$pdf->Cell(35, 10, mb_convert_encoding('Turno', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 248, $y + 20);
$pdf->Cell(35, 10, mb_convert_encoding('Fecha', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------

$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Arial', '', 8);
$startY = $y + 30; // posición inicial debajo de los encabezados

foreach ($data as $row) {
    // Salto de página si se pasa del límite
    if ($startY > 175) {
        $pdf->AddPage('L');
        $startY = 20;
    }

    $pdf->SetXY(10, $startY);
    $pdf->Cell(30, 10, $row['NombreMaquina'], 1, 0, 'C');
    $pdf->Cell(26, 10, $row['CortesA'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['RechazosA'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['TiempoAbajoA'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['MinEnhebrandoA'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['TTiempoArribaA'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['MermaMaquinaA'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['TiempoPerdidoA'], 1, 0, 'C');
    $pdf->Cell(20, 10, $row['NoParosMaquinaA'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['Turno'], 1, 0, 'C');
    $pdf->Cell(30, 10, mb_convert_encoding($row['Fecha'], 'ISO-8859-1', 'UTF-8'), 1, 1, 'C'); // '1' en el último parámetro hace salto de línea

    $startY += 10;
}



//-------------------------------------------------------- Separación -------------------------------------------------------------


$pdf->Output();


