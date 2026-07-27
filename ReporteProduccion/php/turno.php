<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
// require_once("../php/anexo.php");
    
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
//------------------------------------------ Encabezado del reporte ----------------------------------------------
$pdf->SetFont('Arial','B',15);
$pdf->SetTextColor(8, 52, 153);
$pdf->SetDrawColor(39, 101, 245);
$texto = mb_convert_encoding(
    "REPORTE DE TURNO",
    'ISO-8859-1',
    'UTF-8'
);
$pdf->SetXY($x+100, $y+5);
$pdf->Write(10, $texto);
$ancho_texto = $pdf->GetStringWidth($texto);
$inicioX = $x+101;
$textoUbicacion = $y+12;
$grosor_linea = 1;
$espaciado_lineas = 0;

$pdf->Line(
    $inicioX,
    $textoUbicacion + $grosor_linea,
    $inicioX + $ancho_texto,
    $textoUbicacion + $grosor_linea
);
$pdf->Line(
    $inicioX,
    $textoUbicacion + $grosor_linea + $espaciado_lineas + $grosor_linea,
    $inicioX + $ancho_texto,
    $textoUbicacion + $grosor_linea + $espaciado_lineas + $grosor_linea
);
//------------------------------------------ Encabezado del reporte ----------------------------------------------

//-------------------------------------------- Cuerpo del reporte ------------------------------------------------
$pdf->SetFont(
    'Arial',
    '',
    12
);
$pdf->SetTextColor(
    0,
    0,
    0
);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);

$pdf->SetXY($x+60,$y+20);
$pdf->SetFont('Arial', 'B');
$pdf->Cell(25,5,'Folio turno: ', 0);
$pdf->SetTextColor(
    133, 130, 130
);
$pdf->SetX($x+90);
$pdf->MultiCell(20,5,'120727',0); //folio del turno
$endY = $pdf->GetY();
$pdf->SetXY($x+85,$endY-5);
$pdf->SetTextColor(
    0,
    0,
    0
);
$pdf->Cell(20,5,'___________');
//---------------------------
$pdf->SetXY($x+115,$y+20);
$pdf->SetFont('Arial', 'B');
$pdf->Cell(25,5,'Turno: ', 0);
$pdf->SetTextColor(
    133, 130, 130
);
$pdf->SetX($x+135);
$pdf->MultiCell(20,5,'3',0); // Turno del reporte
$endY = $pdf->GetY();
$pdf->SetXY($x+130,$endY-5);
$pdf->SetTextColor(
    0,
    0,
    0
);
$pdf->Cell(20,5,'______');
//---------------------------
$pdf->SetXY($x+150,$y+20);
$pdf->SetFont('Arial', 'B');
$pdf->Cell(25,5,'Fecha: ', 0);
$pdf->SetTextColor(
    133, 130, 130
);
$pdf->SetX($x+165);
$pdf->MultiCell(68,5,mb_convert_encoding('Sábado, 16 agosto, 2025','ISO-8859-1','UTF-8'),0,'C'); //fecha
$endY = $pdf->GetY();
$pdf->SetXY($x+165,$endY-5);
$pdf->SetTextColor(
    0,
    0,
    0
);
$pdf->Cell(20,5,'____________________________');
$endY = $pdf->GetY();
//---------------------------
$pdf->SetXY($x+40,$endY+10);
$pdf->SetFont('Arial', 'B');
$pdf->Cell(25,5,mb_convert_encoding('Máquina:','ISO-8859-1','UTF-8'), 0);
$pdf->SetTextColor(
    133, 130, 130
);
$pdf->SetX($x+60);
$pdf->MultiCell(30,5,mb_convert_encoding('PA05','ISO-8859-1','UTF-8'),0,'C'); //fecha
$endY = $pdf->GetY();
$pdf->SetXY($x+60,$endY-5);
$pdf->SetTextColor(
    0,
    0,
    0
);
$pdf->Cell(20,5,'______________');
//---------------------------
$pdf->SetX($x+95);
$pdf->SetFont('Arial', 'B');
$pdf->Cell(25,5,mb_convert_encoding('Tripulación:','ISO-8859-1','UTF-8'), 0);
$pdf->SetTextColor(
    133, 130, 130
);
$pdf->SetX($x+120);
$pdf->MultiCell(20,5,mb_convert_encoding('T','ISO-8859-1','UTF-8'),0,'C'); //fecha
$endY = $pdf->GetY();
$pdf->SetXY($x+120,$endY-5);
$pdf->SetTextColor(
    0,
    0,
    0
);
$pdf->Cell(20,5,'________');
//---------------------------
$pdf->SetX($x+145);
$pdf->SetFont('Arial', 'B');
$pdf->Cell(25,5,mb_convert_encoding('Supervisor:','ISO-8859-1','UTF-8'), 0);
$pdf->SetTextColor(
    133, 130, 130
);
$pdf->SetX($x+170);
$pdf->MultiCell(75,5,mb_convert_encoding('Moreno Flores Juan Carlos','ISO-8859-1','UTF-8'),0,'C');
$endY = $pdf->GetY();
$pdf->SetXY($x+170,$endY-5);
$pdf->SetTextColor(
    0,
    0,
    0
);
$pdf->Cell(20,5,'______________________________');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetFont('Arial','B',15);
$pdf->SetTextColor(255,0,0);
$pdf->SetXY($x+60,$y+45);
$pdf->Cell(170,5,'SEGURIDAD',0,1,'C');

$pdf->SetDrawColor(250,41,0);
// $pdf->Rect($x+10,$y+55,255,10);
$pdf->SetFont('Arial','B',10);
$pdf->SetXY($x,$y+55);
$pdf->Cell(20,5,'Turno',1,1,'C');
$pdf->SetXY($x-2,$y+62);
$pdf->Cell(20,5,'_______________________________________________________________________________________________________________________');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x+25,$y+55);
$pdf->MultiCell(20,5,'EPP #Opp revisado',1,'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x+48,$y+55);
$pdf->MultiCell(20,5,'EPP buen estado',1,'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x+68,$y+55);
$pdf->MultiCell(50,5,'Comentarios',1,'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x+118,$y+55);
$pdf->MultiCell(29,5,'Polipastos #Opp revisados',1,'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x+147,$y+55);
$pdf->MultiCell(23,5,'Polipastos buen estado',1,'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x+170,$y+55);
$pdf->MultiCell(50,5,'Comentarios',1,'C');
//-----------------------------------------------------------------------------------------------------------------
// $pdf->SetXY($x+55.9,$y+55);
// $pdf->MultiCell(26,5,'Pre-uso Polipastos',1,'C');
//-------------------------------------------- Cuerpo del reporte -------------------------------------------------
//------------------------------------------ Fin del reporte ------------------------------------------------------
$pdf->Output();



