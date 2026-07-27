<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
require_once("consultaTurnosMaquinas.php");

$id = $_GET['folio'];


$turnosObj = new Turnos();
$data = $turnosObj->generarReportePorID($id);
$data2 = $turnosObj->clavesProduccion($id);
$dataSecciones = $turnosObj->seccionesPorParo($id);
$dataModulos = $turnosObj->seccionesParosModulos($id);


class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 5, 5, 60);
    }
}
$pdf = new PDF('P', 'mm', array(220, 280));

$pdf->AddPage();
//------------------------------------------- Coordenadas --------------------------------------------------------
$x = $pdf->GetX();
$y = $pdf->GetY();
//------------------------------------------- Coordenadas --------------------------------------------------------
//------------------------------------------ Encabezado del reporte ----------------------------------------------
$pdf->SetFont('Arial', 'B', 15);
$pdf->SetTextColor(65, 147, 199);
$pdf->SetDrawColor(65, 147, 199);
$texto = mb_convert_encoding(
    "Reporte de producción",
    'ISO-8859-1',
    'UTF-8'
);
$pdf->SetXY($x + 73, $y + 10);
$pdf->Write(10, $texto);
$ancho_texto = $pdf->GetStringWidth($texto);
$inicioX = $x + 114;
$textoUbicacion = $y + 12;
$grosor_linea = 1;
$espaciado_lineas = 0;

$pdf->Line(
    $inicioX + 20,
    $textoUbicacion + $grosor_linea + 5,
    $inicioX - $ancho_texto + 15,
    $textoUbicacion + $grosor_linea + 5
);
$pdf->Line(
    $inicioX + 20,
    $textoUbicacion + $grosor_linea + $espaciado_lineas + $grosor_linea + 5,
    $inicioX - $ancho_texto + 15,
    $textoUbicacion + $grosor_linea + $espaciado_lineas + $grosor_linea + 5
);
//------------------------------------------ Encabezado del reporte ----------------------------------------------

//-------------------------------------------- Cuerpo del reporte ------------------------------------------------
//Folio turno
$pdf->SetTextColor(
    0,
    0,
    0
);
$pdf->SetFillColor(201, 201, 201);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($x + 30, $y + 25);
$pdf->Cell(25, 5, 'Folio turno', 1, 0, 'C', true);
$pdf->SetTextColor(133, 130, 130);
$pdf->SetX($x + 55);
// Validar y obtener el primer IdEncabezadoBitacora no nulo del array
$folioTurno = 'N/A';
if (!empty($data) && is_array($data)) {
    foreach ($data as $row) {
        if (isset($row['IdEncabezadoBitacora']) && $row['IdEncabezadoBitacora'] !== null && trim((string) $row['IdEncabezadoBitacora']) !== '') {
            // Asignar pero no romper; así se quedará con el último elemento válido encontrado
            $folioTurno = $row['IdEncabezadoBitacora'];
        }
    }
}
$pdf->MultiCell(25, 5, $folioTurno, 1, 'C'); //folio del turno
$endY = $pdf->GetY();
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($x + 75, $endY - 5);
//----------------------------------------------------------------------------------------------------------------
//Turno
$pdf->SetFont('Arial', 'B');
$endY = $pdf->GetY();
$pdf->SetXY($x + 130, $endY);
$pdf->SetX($x + 80);
$pdf->Cell(25, 5, 'Turno', 1, 0, 'C', true);
$pdf->SetTextColor(133, 130, 130);
$pdf->SetX($x + 105);
// Validar y obtener el primer valor 'Turno' no nulo del array
$turnoValue = 'N/A';
if (!empty($data) && is_array($data)) {
    foreach ($data as $row) {
        if (isset($row['Turno']) && $row['Turno'] !== null && trim((string) $row['Turno']) !== '') {
            // Asignar pero no romper; así se quedará con el último elemento válido encontrado
            $turnoValue = $row['Turno'];
        }
    }
}
$pdf->MultiCell(10, 5, $turnoValue, 1, 'C');
$pdf->SetTextColor(0, 0, 0);
//----------------------------------------------------------------------------------------------------------------
//Fecha
$pdf->SetFont('Arial', 'B');
$pdf->SetXY($x + 115, $y + 25);
$pdf->Cell(30, 5, 'Fecha', 1, 0, 'C', true);
$pdf->SetTextColor(133, 130, 130);
$pdf->SetX($x + 145);
// Obtener el primer valor 'Fecha' no nulo
$fecha = 'N/A';
if (!empty($data) && is_array($data)) {
    foreach ($data as $row) {
        if (isset($row['Fecha']) && $row['Fecha'] !== null && trim((string) $row['Fecha']) !== '') {
            $fecha = mb_convert_encoding($row['Fecha'], 'ISO-8859-1', 'UTF-8');
            break;
        }
    }
}
$pdf->MultiCell(25, 5, $fecha, 1, 'C'); //fecha
$endY = $pdf->GetY();
$pdf->SetXY($x + 165, $endY - 5);
$pdf->SetTextColor(0, 0, 0);
$endY = $pdf->GetY();
//----------------------------------------------------------------------------------------------------------------
//Máquina
$pdf->SetFont('Arial', 'B');
$pdf->SetXY($x + 30, $endY + 5);
$pdf->Cell(25, 5, mb_convert_encoding('Máquina', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
$pdf->SetTextColor(133, 130, 130);
$pdf->SetX($x + 55);
// Buscar el primer NombreMaquina válido en $data
$nombreMaquina = 'N/A';
if (!empty($data) && is_array($data)) {
    foreach ($data as $row) {
        if (isset($row['NombreMaquina']) && $row['NombreMaquina'] !== null && trim($row['NombreMaquina']) !== '') {
            $nombreMaquina = mb_convert_encoding($row['NombreMaquina'], 'ISO-8859-1', 'UTF-8');
            break;
        }
    }
}
$pdf->SetFont('Arial', 'B', 8);
$pdf->MultiCell(30, 5, $nombreMaquina, 1, 'C');
$endY = $pdf->GetY();
$pdf->SetXY($x + 45, $endY - 5);
$pdf->SetTextColor(0, 0, 0);
//----------------------------------------------------------------------------------------------------------------
//Tripulante
// $pdf->SetFont('Arial', 'B', 8);
// $pdf->SetXY($x + 55, $y + 30);
// $pdf->Cell(25, 5, mb_convert_encoding('Tripulación', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
// $pdf->SetTextColor(133, 130, 130);
// $pdf->SetX($x + 80);
// $pdf->MultiCell(10, 5, mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'), 1, 'C');
// $endY = $pdf->GetY();
// $pdf->SetXY($x + 120, $endY - 5);
// $pdf->SetTextColor(
//     0,
//     0,
//     0
// );
//----------------------------------------------------------------------------------------------------------------
//Conductor
$pdf->SetFont('Arial', 'B');
$pdf->SetXY($x + 85, $endY - 5);
$pdf->Cell(30, 5, 'Conductor', 1, 0, 'C', true);
$pdf->SetTextColor(
    133,
    130,
    130
);
$pdf->SetX($x + 115);
$pdf->MultiCell(55, 5, mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'), 1, 'C');
$endY = $pdf->GetY();
$pdf->SetXY($x + 170, $endY - 5);
$pdf->SetTextColor(
    0,
    0,
    0
);
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetFont('Arial','B',20);
// $pdf->SetTextColor(255,0,0);
// $pdf->SetXY($x+55,$y+45);
// $pdf->Cell(170,5,'Seguridad',0,1,'C');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetTextColor(255,0,0);
// $pdf->SetFont('Arial','B',15);
// $pdf->SetXY($x-4,$y+60);
// $pdf->Cell(20,5,'________________________________________________________________________________________________');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetDrawColor(250,41,0);
// $pdf->SetTextColor(255,0,0);
// $pdf->SetFont('Arial','B',10);
// $pdf->Rect($x-3, $y+55, 283, 108);
// $pdf->SetXY($x-3,$y+57);
// $pdf->Cell(15,5,'Turno',0,1,'C');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetXY($x+12,$y+55);
// $pdf->MultiCell(20,5,'No. opp revisado',0,'C');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetXY($x+32,$y+55);
// $pdf->MultiCell(20,5,'EPP buen estado',0,'C');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetXY($x+52, $y+57);
// $pdf->MultiCell(46,5, "Comentarios", 0,'C');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetXY($x+98, $y+55);
// $pdf->MultiCell(35,5, 'Polipastos no. opp revisados',0,'C');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetXY($x+133, $y+55);
// $pdf->MultiCell(30,5, 'Polipastos buen estado',0,'C');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetXY($x+163, $y+57);
// $pdf->MultiCell(46,5, 'Comentarios',0,'C');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetXY($x+209, $y+55);
// $pdf->MultiCell(36,5, mb_convert_encoding('No. opp pláticas de seguridad', 'ISO-8859-1','UTF-8'),0,'C');
// //-----------------------------------------------------------------------------------------------------------------
// $pdf->SetXY($x+245, $y+55);
// $pdf->MultiCell(33,5, mb_convert_encoding('Título pláticas de seguridad', 'ISO-8859-1','UTF-8'),0,'C');
// //-------------------------------------------------- Llenado de datos ---------------------------------------------------------------

// //-------------------------------------------------- Turno 1 - Turno ----------------------------------------------------------------
// if($turno == 1 ){
// $pdf->SetFont('Arial','',10);
// $pdf->SetTextColor(0,0,0);
// $pdf->SetXY($x-3,$y+75);
// $pdf->MultiCell(15,5,'1',0,'C');
// //Número de Operadores revisados Turno 1
// $pdf->SetXY($x+12,$y+75);
// $pdf->MultiCell(20,5,'7',0,'C');
// //EPP en buen estado Turno1
// $pdf->SetXY($x+32,$y+75);
// $pdf->MultiCell(20,5,'6',0,'C');
// //Comentarios EPP Turno1
// $endY = $pdf->GetY();
// $pdf->SetXY($x+52,$endY-15);
// $pdf->MultiCell(46,5,'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget.qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq',0,'C');
// //Polipastos, número de operadores Turno 1
// $endY = $pdf->GetY();
// $pdf->SetXY($x+98,$endY-15);
// $pdf->MultiCell(35,5,'15',0,'C');
// //Polipastos en buen estado Turno 1
// $endY = $pdf->GetY();
// $pdf->SetXY($x+133,$endY-5);
// $pdf->MultiCell(30,5,'13',0,'C');
// //Comentarios Polipastos Turno 1
// $endY = $pdf->GetY();
// $pdf->SetXY($x+163,$endY-10);
// $pdf->MultiCell(46,5,mb_convert_encoding('Corta cuñetes no funciona (dañado) y no hay patín de formación','ISO-8859-1','UTF-8'),0,'C');
// //Número de operadores que tomaron las pláticas de seguridad Turno 1
// $endY = $pdf->GetY();
// $pdf->SetXY($x+209,$endY-10);
// $pdf->MultiCell(36,5,mb_convert_encoding('7','ISO-8859-1','UTF-8'),0,'C');
// //Título de las pláticas de seguridad Turno 1
// $endY = $pdf->GetY();
// $pdf->SetXY($x+245,$endY-10);
// $pdf->MultiCell(33,5,mb_convert_encoding('Consejos para la salud','ISO-8859-1','UTF-8'),0,'C');
// //------------------------------------------------------ Fin de turno 1 -----------------------------------------------------------

// //-------------------------------------------------------- Separación -------------------------------------------------------------
// $pdf->SetTextColor(255,0,0);
// $pdf->SetXY($x-4,$y+90);
// $pdf->Cell(20,5,'________________________________________________________________________________________________________________________________________________');
// $pdf->SetFont('Arial','',10);

// //--------------------------------------------------------- Separación --------------------------------------------------------------
// }
// if($turno == 2 ){
//     //-------------------------------------------------- Turno 1 - Turno ----------------------------------------------------------------
//     $pdf->SetFont('Arial','',10);
//     $pdf->SetTextColor(0,0,0);
//     $pdf->SetXY($x-3,$y+75);
//     $pdf->MultiCell(15,5,'1',0,'C');
//     //Número de Operadores revisados Turno 1
//     $pdf->SetXY($x+12,$y+75);
//     $pdf->MultiCell(20,5,'7',0,'C');
//     //EPP en buen estado Turno1
//     $pdf->SetXY($x+32,$y+75);
//     $pdf->MultiCell(20,5,'6',0,'C');
//     //Comentarios EPP Turno1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+52,$endY-15);
//     $pdf->MultiCell(46,5,'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget.qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq',0,'C');
//     //Polipastos, número de operadores Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+98,$endY-15);
//     $pdf->MultiCell(35,5,'15',0,'C');
//     //Polipastos en buen estado Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+133,$endY-5);
//     $pdf->MultiCell(30,5,'13',0,'C');
//     //Comentarios Polipastos Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+163,$endY-10);
//     $pdf->MultiCell(46,5,mb_convert_encoding('Corta cuñetes no funciona (dañado) y no hay patín de formación','ISO-8859-1','UTF-8'),0,'C');
//     //Número de operadores que tomaron las pláticas de seguridad Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+209,$endY-10);
//     $pdf->MultiCell(36,5,mb_convert_encoding('7','ISO-8859-1','UTF-8'),0,'C');
//     //Título de las pláticas de seguridad Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+245,$endY-10);
//     $pdf->MultiCell(33,5,mb_convert_encoding('Consejos para la salud','ISO-8859-1','UTF-8'),0,'C');
//     //------------------------------------------------------ Fin de turno 1 -----------------------------------------------------------

//     //-------------------------------------------------------- Separación -------------------------------------------------------------
//     $pdf->SetTextColor(255,0,0);
//     $pdf->SetXY($x-4,$y+90);
//     $pdf->Cell(20,5,'________________________________________________________________________________________________________________________________________________');
//     $pdf->SetFont('Arial','',10);

//     //--------------------------------------------------------- Separación --------------------------------------------------------------

//     //-------------------------------------------------- Turno 2 - Turno ----------------------------------------------------------------

//     $pdf->SetTextColor(0,0,0);
//     //Turno 2
//     $pdf->SetXY($x-3,$y+110);
//     $pdf->MultiCell(15,5,'2',0,'C');
//     //#Opp revisado Turno 2
//     $pdf->SetXY($x+12,$y+110);
//     $pdf->MultiCell(20,5,'7',0,'C');
//     //EPP en buen estado Turno2
//     $pdf->SetXY($x+32,$y+110);
//     $pdf->MultiCell(20,5,'5',0,'C');
//     //Comentarios EPP Turno2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+52,$endY-16);
//     $pdf->MultiCell(46,5,'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget.qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq',0,'C');
//     //Polipastos, número de operadores Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+98,$endY-16);
//     $pdf->MultiCell(35,5,'15',0,'C');
//     //Polipastos en buen estado Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+133,$endY-5);
//     $pdf->MultiCell(30,5,'14',0,'C');
//     //Comentarios Polipastos Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+163,$endY-7);
//     $pdf->MultiCell(46,5,mb_convert_encoding('Polipastos funcionando bien','ISO-8859-1','UTF-8'),0,'C');
//     //Número de operadores que tomaron las pláticas de seguridad Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+209,$endY-8);
//     $pdf->MultiCell(36,5,mb_convert_encoding('6','ISO-8859-1','UTF-8'),0,'C');
//     //Título de las pláticas de seguridad Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+245,$endY-8);
//     $pdf->MultiCell(33,5,mb_convert_encoding('Contaminación térmica','ISO-8859-1','UTF-8'),0,'C');
//     //------------------------------------------------------ Fin de turno 2 -----------------------------------------------------------

//     //-------------------------------------------------------- Separación -------------------------------------------------------------
//     $pdf->SetFont('Arial','B',10);
//     $pdf->SetTextColor(255,0,0);
//     $pdf->SetXY($x-4,$y+124);
//     $pdf->Cell(20,5,'________________________________________________________________________________________________________________________________________________');
//     //-------------------------------------------------------- Separación -------------------------------------------------------------
// }


// if($turno == 3 ){
//     //-------------------------------------------------- Turno 1 - Turno ----------------------------------------------------------------
//     $pdf->SetFont('Arial','',10);
//     $pdf->SetTextColor(0,0,0);
//     $pdf->SetXY($x-3,$y+75);
//     $pdf->MultiCell(15,5,'1',0,'C');
//     //Número de Operadores revisados Turno 1
//     $pdf->SetXY($x+12,$y+75);
//     $pdf->MultiCell(20,5,'7',0,'C');
//     //EPP en buen estado Turno1
//     $pdf->SetXY($x+32,$y+75);
//     $pdf->MultiCell(20,5,'6',0,'C');
//     //Comentarios EPP Turno1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+52,$endY-15);
//     $pdf->MultiCell(46,5,'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget.qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq',0,'C');
//     //Polipastos, número de operadores Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+98,$endY-15);
//     $pdf->MultiCell(35,5,'15',0,'C');
//     //Polipastos en buen estado Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+133,$endY-5);
//     $pdf->MultiCell(30,5,'13',0,'C');
//     //Comentarios Polipastos Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+163,$endY-10);
//     $pdf->MultiCell(46,5,mb_convert_encoding('Corta cuñetes no funciona (dañado) y no hay patín de formación','ISO-8859-1','UTF-8'),0,'C');
//     //Número de operadores que tomaron las pláticas de seguridad Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+209,$endY-10);
//     $pdf->MultiCell(36,5,mb_convert_encoding('7','ISO-8859-1','UTF-8'),0,'C');
//     //Título de las pláticas de seguridad Turno 1
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+245,$endY-10);
//     $pdf->MultiCell(33,5,mb_convert_encoding('Consejos para la salud','ISO-8859-1','UTF-8'),0,'C');
//     //------------------------------------------------------ Fin de turno 1 -----------------------------------------------------------

//     //-------------------------------------------------------- Separación -------------------------------------------------------------
//     $pdf->SetTextColor(255,0,0);
//     $pdf->SetXY($x-4,$y+90);
//     $pdf->Cell(20,5,'________________________________________________________________________________________________________________________________________________');
//     $pdf->SetFont('Arial','',10);

//     //--------------------------------------------------------- Separación --------------------------------------------------------------

//     //-------------------------------------------------- Turno 2 - Turno ----------------------------------------------------------------

//     $pdf->SetTextColor(0,0,0);
//     //Turno 2
//     $pdf->SetXY($x-3,$y+110);
//     $pdf->MultiCell(15,5,'2',0,'C');
//     //#Opp revisado Turno 2
//     $pdf->SetXY($x+12,$y+110);
//     $pdf->MultiCell(20,5,'7',0,'C');
//     //EPP en buen estado Turno2
//     $pdf->SetXY($x+32,$y+110);
//     $pdf->MultiCell(20,5,'5',0,'C');
//     //Comentarios EPP Turno2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+52,$endY-16);
//     $pdf->MultiCell(46,5,'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget.qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq',0,'C');
//     //Polipastos, número de operadores Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+98,$endY-16);
//     $pdf->MultiCell(35,5,'15',0,'C');
//     //Polipastos en buen estado Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+133,$endY-5);
//     $pdf->MultiCell(30,5,'14',0,'C');
//     //Comentarios Polipastos Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+163,$endY-7);
//     $pdf->MultiCell(46,5,mb_convert_encoding('Polipastos funcionando bien','ISO-8859-1','UTF-8'),0,'C');
//     //Número de operadores que tomaron las pláticas de seguridad Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+209,$endY-8);
//     $pdf->MultiCell(36,5,mb_convert_encoding('6','ISO-8859-1','UTF-8'),0,'C');
//     //Título de las pláticas de seguridad Turno 2
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+245,$endY-8);
//     $pdf->MultiCell(33,5,mb_convert_encoding('Contaminación térmica','ISO-8859-1','UTF-8'),0,'C');
//     //------------------------------------------------------ Fin de turno 2 -----------------------------------------------------------

//     //-------------------------------------------------------- Separación -------------------------------------------------------------
//     $pdf->SetFont('Arial','B',10);
//     $pdf->SetTextColor(255,0,0);
//     $pdf->SetXY($x-4,$y+124);
//     $pdf->Cell(20,5,'________________________________________________________________________________________________________________________________________________');
//     //-------------------------------------------------------- Separación -------------------------------------------------------------
//     //-------------------------------------------------- Turno 3 - Turno ----------------------------------------------------------------
//     $pdf->SetFont('Arial','',10);
//     $pdf->SetTextColor(0,0,0);

//     //Turno 3
//     $pdf->SetXY($x-3,$y+143);
//     $pdf->MultiCell(15,5,'3',0,'C');
//     //#Opp revisado Turno 3
//     $pdf->SetXY($x+12,$y+143);
//     $pdf->MultiCell(20,5,'7',0,'C');
//     //EPP en buen estado Turno3
//     $pdf->SetXY($x+32,$y+143);
//     $pdf->MultiCell(20,5,'7',0,'C');
//     //Comentarios EPP Turno3
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+52,$endY-15);
//     $pdf->MultiCell(46,5,'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget.qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq',0,'C');
//     //Polipastos, número de operadores Turno 3
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+98,$endY-15);
//     $pdf->MultiCell(35,5,'14',0,'C');
//     //Polipastos en buen estado Turno 3
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+133,$endY-5);
//     $pdf->MultiCell(30,5,'14',0,'C');
//     //Comentarios Polipastos Turno 3
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+163,$endY-5);
//     $pdf->MultiCell(46,5,mb_convert_encoding('Funcionando correctamente','ISO-8859-1','UTF-8'),0,'C');
//     //Número de operadores que tomaron las pláticas de seguridad Turno 3
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+209,$endY-5);
//     $pdf->MultiCell(36,5,mb_convert_encoding('5','ISO-8859-1','UTF-8'),0,'C');
//     //Título de las pláticas de seguridad Turno 3
//     $endY = $pdf->GetY();
//     $pdf->SetXY($x+245,$endY-7);
//     $pdf->MultiCell(33,5,mb_convert_encoding('Contaminación térmica','ISO-8859-1','UTF-8'),0,'C');
//     //------------------------------------------------------ Fin de turno 3 -----------------------------------------------------------
// }
// //------------------------------------------------------ Segunda página -----------------------------------------------------------
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetXY(90, 51);
$pdf->Cell(40, 5, 'Manejo de activos', 0, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetDrawColor(30, 144, 227);
$pdf->Rect($x + 8, $y + 52, 184.5, 29);
$pdf->SetFont('Arial', 'B', 9);
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 8, $y + 52);
$pdf->Cell(18, 8, 'Turno', 1, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 26, $y + 52);
$pdf->Cell(18, 8, 'Cortes', 1, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 44, $y + 52);
$pdf->MultiCell(20, 4, mb_convert_encoding('Pañales empacados', 'ISO-8859-1', 'UTF-8'), 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 64, $y + 52);
$pdf->Cell(18, 8, mb_convert_encoding('Rechazos', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 82, $y + 52);
$pdf->Cell(18, 8, mb_convert_encoding('# Paros', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 100, $y + 52);
$pdf->MultiCell(18, 4, mb_convert_encoding('Tiempo abajo', 'ISO-8859-1', 'UTF-8'), 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 118, $y + 52);
$pdf->MultiCell(20, 4, mb_convert_encoding('Horas Trabajadas', 'ISO-8859-1', 'UTF-8'), 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 138, $y + 52);
$pdf->MultiCell(18, 4, mb_convert_encoding('% Merma máquina', 'ISO-8859-1', 'UTF-8'), 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 156, $y + 52);
$pdf->MultiCell(18, 4, mb_convert_encoding('% Merma total', 'ISO-8859-1', 'UTF-8'), 1, 'C');
//-----------------------------------------------------------------------------------------------------------------
$pdf->SetXY($x + 174, $y + 52);
$pdf->MultiCell(18.5, 4, mb_convert_encoding('% Tiempo perdido', 'ISO-8859-1', 'UTF-8'), 1, 'C');
//-----------------------------------------------------------------------------------------------------------------

//-------------------------------------------------------- Separación -------------------------------------------------------------
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY($x + 7, $y + 56);
$pdf->Cell(20, 5, '______________________________________________________________________________________________');
//-------------------------------------------------------- Separación -------------------------------------------------------------

if ($data[0]['Turno'] == 1) {
    //-------------------------------------------------- Turno 1 - Turno ----------------------------------------------------------------
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(0, 0, 0);
    //Turno
    $pdf->SetXY($x + 8, $y + 60);
    $pdf->MultiCell(18, 7, '1', 0, 'C');
    //Cortes
    $pdf->SetXY($x + 26, $y + 60);
    $pdf->MultiCell(18, 7, $data[0]['CortesA'], 0, 'C');
    //Cajas reales
    $pdf->SetXY($x + 44, $y + 60);
    $pdf->MultiCell(20, 7, $data[0]['TotalGeneralPañales'], 0, 'C');
    //Rechazos
    $pdf->SetXY($x + 64, $y + 60);
    $pdf->MultiCell(18, 7, $data[0]['RechazosA'], 0, 'C');
    //Número de paros
    $pdf->SetXY($x + 82, $y + 60);
    $pdf->MultiCell(18, 7, $data[0]['NoParosMaquinaA'], 0, 'C');
    //Tiempo abajo en minutos
    $pdf->SetXY($x + 100, $y + 60);
    // Ajustar TiempoAbajoA si la máquina está en el rango especificado
    $tiempoAbajo = $data[0]['TiempoAbajoA'] ?? 0;
    $noMaquina = $data[0]['NoMaquina'] ?? null;

    // Ajustar TiempoAbajoA si la máquina está en el rango especificado y guardar en variable para uso posterior
    if (is_numeric($tiempoAbajo) && in_array((int) $noMaquina, [60, 61, 62, 63, 64], true)) {
        $tiempoAbajoAjustado = round((float) $tiempoAbajo / 60);
    } else {
        $tiempoAbajoAjustado = is_numeric($tiempoAbajo) ? (float) $tiempoAbajo : $tiempoAbajo;
    }
    $valorMostrar = $tiempoAbajoAjustado;

    $pdf->MultiCell(18, 7, $valorMostrar, 0, 'C');
    // // Horas trabajadas
    $pdf->SetXY($x + 118, $y + 60);
    $pdf->MultiCell(20, 7, $data[0]['HorasTrabajadas'], 0, 'C');
    // // Porcentaje de merma en máquina
    $pdf->SetXY($x + 138, $y + 60);
    $pdf->MultiCell(18, 7, number_format($data[0]['CortesA'] != 0 ? ($data[0]['RechazosA'] / $data[0]['CortesA']) * 100 : 0, 2) . '%', 0, 'C');
    // Porcentaje de merma total 
    $pdf->SetXY($x + 156, $y + 60);
    $pdf->MultiCell(18, 7, number_format($data[0]['TotalGeneralPañales'] != 0 ? (1 - ($data[0]['TotalGeneralPañales'] / $data[0]['CortesA'])) * 100 : 0, 2) . '%', 0, 'C');
    // // Porcentaje de tiempo perdido
    $pdf->SetXY($x + 174, $y + 60);
    $pdf->MultiCell(18, 7, number_format($data[0]['TiempoAbajoA'] != 0 ? (($valorMostrar / 480)) * 100 : 0, 2) . '%', 0, 'C');
    //------------------------------------------------------ Fin de turno 1 -----------------------------------------------------------

    //-------------------------------------------------------- Separación -------------------------------------------------------------
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(30, 144, 227);
    $pdf->SetXY($x + 7, $y + 63);
    $pdf->Cell(20, 5, '______________________________________________________________________________________________');
    //-------------------------------------------------------- Separación -------------------------------------------------------------
}
//-------------------------------------------------- Turno 2 - Turno ----------------------------------------------------------------
if (isset($data[1]) && (($data[1]['Turno'] ?? null) == 2)) {
    // Valores seguros para turno 2
    $c1 = $data[1]['CortesA'] ?? 0;
    $r1 = $data[1]['RechazosA'] ?? 0;
    $p1 = $data[1]['NoParosMaquinaA'] ?? 0;
    $t1_raw = $data[1]['TiempoAbajoA'] ?? 0;
    $noMaquina1 = $data[1]['NoMaquina'] ?? null;
    if (is_numeric($t1_raw) && in_array((int) $noMaquina1, [60, 61, 62, 63, 64], true)) {
        // Si NoMaquina está en 60-64, dividir TiempoAbajoA entre 60
        $t1 = round((float) $t1_raw / 60);
    } else {
        // En caso contrario mantener el valor tal cual (convertido a float si es numérico)
        $t1 = is_numeric($t1_raw) ? (float) $t1_raw : $t1_raw;
    }
    $h1 = $data[1]['HorasTrabajadas'] ?? 0;
    $m1 = $data[1]['TotalGeneralPañales'] ?? 0;

    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(0, 0, 0);
    //Turno
    $pdf->SetXY($x + 8, $y + 67);
    $pdf->MultiCell(18, 7, '2', 0, 'C');
    //Cortes
    $pdf->SetXY($x + 26, $y + 67);
    $pdf->MultiCell(18, 7, $c1, 0, 'C');
    //Cajas reales
    $pdf->SetXY($x + 44, $y + 67);
    $pdf->MultiCell(20, 7, $data[1]['TotalGeneralPañales'], 0, 'C');
    //Rechazos
    $pdf->SetXY($x + 64, $y + 67);
    $pdf->MultiCell(18, 7, $r1, 0, 'C');
    //Número de paros
    $pdf->SetXY($x + 82, $y + 67);
    $pdf->MultiCell(18, 7, $p1, 0, 'C');
    //Tiempo abajo en minutos
    $pdf->SetXY($x + 100, $y + 67);
    $pdf->MultiCell(18, 7, $t1, 0, 'C');
    //Horas trabajadas
    $pdf->SetXY($x + 118, $y + 67);
    $pdf->MultiCell(20, 7, $h1, 0, 'C');
    //Porcentaje de merma en máquina
    $merma1 = ($c1 != 0) ? number_format(($r1 / $c1) * 100, 2) . '%' : '0.00%';
    $pdf->SetXY($x + 138, $y + 67);
    $pdf->MultiCell(18, 7, $merma1, 0, 'C');
    //Porcentaje de merma total (fijo)
    $pdf->SetXY($x + 156, $y + 67);
    $pdf->MultiCell(18, 7, number_format($m1 != 0 ? (1 - ($m1 / $c1)) * 100 : 0, 2) . '%', 0, 'C');
    //Porcentaje de tiempo perdido (respecto a 480 min por turno)
    $timePct1 = ($t1 != 0) ? number_format(($t1 / 450) * 100, 2) . '%' : '0.00%';
    $pdf->SetXY($x + 174, $y + 67);
    $pdf->MultiCell(18, 7, $timePct1, 0, 'C');
    //------------------------------------------------------ Fin de turno 2 -----------------------------------------------------------

    //-------------------------------------------------------- Separación -------------------------------------------------------------
    $pdf->SetTextColor(30, 144, 227);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($x + 7, $y + 70);
    $pdf->Cell(20, 5, '______________________________________________________________________________________________');
    //-------------------------------------------------------- Separación -------------------------------------------------------------
}

if (isset($data[2]) && (($data[2]['Turno'] ?? null) == 3)) {
    $t2 = $data[2] ?? [];

    $c2 = (float) ($t2['CortesA'] ?? 0);
    $r2 = (float) ($t2['RechazosA'] ?? 0);
    $p2 = (float) ($t2['NoParosMaquinaA'] ?? 0);
    $ta2_raw = $t2['TiempoAbajoA'] ?? 0;
    $noMaquina2 = $t2['NoMaquina'] ?? null;
    if (is_numeric($ta2_raw) && in_array((int) $noMaquina2, [60, 61, 62, 63, 64], true)) {
        // Dividir entre 60 y redondear a entero (sin decimales)
        $ta2 = (int) round((float) $ta2_raw / 60);
    } else {
        $ta2 = is_numeric($ta2_raw) ? (float) $ta2_raw : $ta2_raw;
    }
    $h2 = (float) ($t2['HorasTrabajadas'] ?? 0);
    $m2 = (float) ($t2['TotalGeneralPañales'] ?? 0);

    // Turno 3
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($x + 8, $y + 74);
    $pdf->MultiCell(18, 7, '3', 0, 'C');
    // Cortes
    $pdf->SetXY($x + 26, $y + 74);
    $pdf->MultiCell(18, 7, $c2, 0, 'C');
    //Cajas reales
    $pdf->SetXY($x + 44, $y + 74);
    $pdf->MultiCell(20, 7, $m2, 0, 'C');
    // Rechazos
    $pdf->SetXY($x + 64, $y + 74);
    $pdf->MultiCell(18, 7, $r2, 0, 'C');
    // Número de paros
    $pdf->SetXY($x + 82, $y + 74);
    $pdf->MultiCell(18, 7, $p2, 0, 'C');
    // Tiempo abajo en minutos
    $pdf->SetXY($x + 100, $y + 74);
    $pdf->MultiCell(18, 7, $ta2, 0, 'C');
    // Horas trabajadas
    $pdf->SetXY($x + 118, $y + 74);
    $pdf->MultiCell(20, 7, $h2, 0, 'C');
    // Porcentaje de merma en máquina
    $pdf->SetXY($x + 138, $y + 74);
    $pdf->MultiCell(18, 7, number_format($c2 != 0 ? ($r2 / $c2) * 100 : 0, 2) . '%', 0, 'C');
    // Porcentaje de merma total (fijo)
    $pdf->SetXY($x + 156, $y + 74);
    $pdf->MultiCell(18, 7, number_format($m2 != 0 ? (1 - ($m2 / $c2)) * 100 : 0, 2) . '%', 0, 'C');
    // Porcentaje de tiempo perdido
    $pdf->SetXY($x + 174, $y + 74);
    $pdf->MultiCell(18, 7, number_format($ta2 != 0 ? ($ta2 / 510) * 100 : 0, 2) . '%', 0, 'C');
}

//------------------------------------------------------- Turno total ---------------------------------------------------------

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(30, 144, 227);
$pdf->Rect($x + 26, $y + 81, 166.5, 7);
$pdf->SetXY($x + 8, $y + 81);
$pdf->Cell(18, 7, 'Total:', 1, 0, 'C');

// Totales según el turno seleccionado
$CortesTotal = 0;
$RechazosTotal = 0;
$ParosTotal = 0;
$TiempoAbajoTotal = 0;
$horasTrabajadasTotal = 0;
$turnosConsiderados = 0;
$mermaMaquinaTotal = 0;
$totalPañales = 0;
// Recorrer todos los turnos disponibles
foreach ($data as $registro) {
    if (isset($registro['Turno']) && $registro['Turno'] !== null) {
        $CortesTotal += (float) $registro['CortesA'];
        $RechazosTotal += (float) $registro['RechazosA'];
        $ParosTotal += (float) $registro['NoParosMaquinaA'];
        $tiempoRaw = $registro['TiempoAbajoA'] ?? 0;
        $noMaquina = $registro['NoMaquina'] ?? null;
        $totalPañales += $registro['TotalGeneralPañales'];
        if (is_numeric($tiempoRaw) && in_array((int) $noMaquina, [60, 61, 62, 63, 64], true)) {
            // Para máquinas 60-64 dividir entre 60 (como se hizo anteriormente)
            $TiempoAbajoTotal += round((float) $tiempoRaw / 60);
        } else {
            // En caso contrario mantener el valor tal cual (o 0 si no es numérico)
            $TiempoAbajoTotal += is_numeric($tiempoRaw) ? (float) $tiempoRaw : 0;
        }
        $horasTrabajadasTotal += (float) $registro['HorasTrabajadas'];
        $mermaMaquinaTotal += (float) $registro['TotalGeneralPañales'];
        $turnosConsiderados++;
    }
}

// Cálculos porcentuales
$MermaMaquinaTotal = $CortesTotal != 0 ? round(($RechazosTotal / $CortesTotal) * 100, 2) : 0;

// Porcentaje de tiempo perdido respecto al total de minutos disponibles (8h = 480 min por turno)
$totalMinutosDisponibles = 0;
foreach ($data as $registro) {
    if (isset($registro['HorasTrabajadas'])) {
        $totalMinutosDisponibles += floatval($registro['HorasTrabajadas']) * 60;
    }
}
$TiempoPerdidoTotal = ($totalMinutosDisponibles > 0) ? round(($TiempoAbajoTotal / $totalMinutosDisponibles) * 100, 2) : 0;

//Merma total
$mermaMaquinaTotal = $CortesTotal != 0 ? round((1 - ($mermaMaquinaTotal / $CortesTotal)) * 100, 2) : 0;

// Mostrar en PDF
$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(18, 7, $CortesTotal, 0, 0, 'C');
$pdf->setXY($x + 44, $y + 81);
$pdf->Cell(20, 7, $totalPañales, 0, 0, 'C');
$pdf->setXY($x + 64, $y + 81);
$pdf->Cell(18, 7, $RechazosTotal, 0, 0, 'C');
$pdf->setXY($x + 82, $y + 81);
$pdf->Cell(18, 7, $ParosTotal, 0, 0, 'C');
$pdf->setXY($x + 100, $y + 81);
$pdf->Cell(18, 7, $TiempoAbajoTotal, 0, 0, 'C');
$pdf->setXY($x + 118, $y + 81);
$pdf->Cell(20, 7, $horasTrabajadasTotal, 0, 0, 'C');
$pdf->setXY($x + 138, $y + 81);
$pdf->Cell(18, 7, $MermaMaquinaTotal . '%', 0, 0, 'C');
$pdf->setXY($x + 156, $y + 81);
$pdf->Cell(18, 7, $mermaMaquinaTotal . '%', 0, 0, 'C');
$pdf->setXY($x + 174, $y + 81);
$pdf->Cell(18, 7, $TiempoPerdidoTotal . '%', 0, 1, 'C');
//------------------------------------------------------- Turno total ---------------------------------------------------
//-------------------------------------- Claves de Produccion ------------------------------------------------
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetXY(45, 105);
$pdf->MultiCell(80, 5, mb_convert_encoding('Claves de producción', 'ISO-8859-1', 'UTF-8'), 0, 'C');
$endY = $pdf->GetY();
//---------------------------------- CONTENEDOR CLAVES DE PRODUCCIÓN ----------------------------------------
$data2 = $turnosObj->clavesProduccion($id);


$startY = $pdf->GetY();
$startX = 4; // posición inicial en X
$cardWidth = 40;
$cardHeight = 60;
$spacing = 6; // espacio entre tarjetas
$clavesDia = [];
$totalDiaReales = 0;
$totalDiaSTD = 0;

$turnos = [1, 2, 3];
foreach ($turnos as $i => $turno) {
    if (isset($data2[$turno]) && $data2[$turno][0]['Turno'] == $turno) {
        $posX = $startX + ($i * ($cardWidth + $spacing));
        generarTarjetaTurno($pdf, $data2[$turno], $turno, $posX, $startY);

        // Acumular datos para el total del día
        foreach ($data2[$turno] as $clave) {
            $claveId = $clave['NoClave'];
            if (!isset($clavesDia[$claveId])) {
                $clavesDia[$claveId] = [
                    'NoClave' => $claveId,
                    'TotalAcumulado' => 0,
                    'TotalSTD' => 0
                ];
            }
            $clavesDia[$claveId]['TotalAcumulado'] += $clave['TotalAcumulado'];
            $clavesDia[$claveId]['TotalSTD'] += $clave['TotalSTD'];
            $totalDiaReales += $clave['TotalAcumulado'];
            $totalDiaSTD += $clave['TotalSTD'];
        }

    }
}



function generarTarjetaTurno($pdf, $datosTurno, $turno, $x, $y)
{
    // Título del turno
    $tituloTurno = '';
    switch ($turno) {
        case 1:
            $tituloTurno = '1er turno';
            break;
        case 2:
            $tituloTurno = '2do turno';
            break;
        case 3:
            $tituloTurno = '3er turno';
            break;
    }

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY($x + 14, $y + 5); // Posición arriba del rectángulo
    $pdf->SetTextColor(30, 144, 227);
    $pdf->MultiCell(45, 5, mb_convert_encoding($tituloTurno, 'ISO-8859-1', 'UTF-8'), 0, 'C');

    // Tarjeta
    // $pdf->Rect($x, $y+10, 90, 60);
    $pdf->SetXY($x + 14, $y + 10);
    $pdf->Cell(15, 5, 'Clave', 0, 0, 'C');
    $endX = $pdf->GetX();
    $pdf->SetXY($endX, $y + 10);
    $pdf->Cell(14, 5, 'Reales', 0, 0, 'C');
    $endX = $pdf->GetX();
    $pdf->SetXY($endX, $y + 10);
    $pdf->Cell(16, 5, mb_convert_encoding('Estándar', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($endX - 28, $y + 11);
    $pdf->Cell(20, 5, '______________________');

    $totalReales = 0;
    $totalSTD = 0;
    $lineY = $y + 15;

    foreach ($datosTurno as $clave) {
        $pdf->SetXY($x + 14, $lineY);
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(15, 5, $clave['NoClave'], 0, 0, 'C');
        $endX = $pdf->GetX();
        $pdf->SetXY($endX, $lineY);
        $pdf->Cell(14, 5, number_format($clave['TotalAcumulado']), 0, 0, 'C');
        $endX = $pdf->GetX();
        $pdf->SetXY($endX, $lineY);
        $pdf->Cell(16, 5, number_format($clave['TotalSTD'], 2), 0, 0, 'C');
        $pdf->SetXY($endX - 27, $lineY + 1);
        $pdf->Cell(20, 5, '______________________________');
        $totalReales += $clave['TotalAcumulado'];
        $totalSTD += $clave['TotalSTD'];
        $lineY += 5;
    }

    // Totales
    $pdf->SetXY($x + 14, $lineY);
    $pdf->SetTextColor(30, 144, 227);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(15, 5, 'Total', 0, 0, 'C');
    $endX = $pdf->GetX();
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($endX, $lineY);
    $pdf->Cell(14, 5, number_format($totalReales), 0, 0, 'C');
    $endX = $pdf->GetX();
    $pdf->SetXY($endX, $lineY);
    $pdf->Cell(16, 5, number_format($totalSTD, 2), 0, 0, 'C');
}
// Nueva tabla: Totales del día
$startY += $cardHeight + 5; // posición debajo de las tarjetas
generarTablaDia($pdf, $clavesDia, $totalDiaReales, $totalDiaSTD, $startX, $startY);
$endY = $pdf->GetY();
function generarTablaDia($pdf, $clavesDia, $totalDiaReales, $totalDiaSTD, $x, $y)
{
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetXY($x + 151, $y - 70);
    $pdf->SetTextColor(30, 144, 227);
    $pdf->MultiCell(50, 5, mb_convert_encoding('Totales del día', 'ISO-8859-1', 'UTF-8'), 0, 'C');

    // Encabezados
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(30, 144, 227);
    $pdf->SetXY($x + 154, $y - 55);
    $pdf->Cell(15, 5, 'Clave', 0, 0, 'C');
    $pdf->Cell(14, 5, 'Reales', 0, 0, 'C');
    $pdf->Cell(16, 5, mb_convert_encoding('Estándar', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($x + 155, $y - 54);
    $pdf->Cell(20, 5, '______________________');

    $lineY = $y + 20;

    // Datos por clave
    foreach ($clavesDia as $clave) {
        $pdf->SetXY($x + 154, $lineY - 70);
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(15, 5, $clave['NoClave'], 0, 0, 'C');
        $pdf->Cell(14, 5, number_format($clave['TotalAcumulado']), 0, 0, 'C');
        $pdf->Cell(16, 5, number_format($clave['TotalSTD'], 2), 0, 1, 'C');
        $pdf->SetXY($x + 156, $lineY - 69);
        $pdf->Cell(20, 5, '______________________________');
        $lineY += 5;
    }

    // Total general
    $pdf->SetXY($x + 154, $lineY - 70);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetTextColor(30, 144, 227);
    $pdf->Cell(15, 5, mb_convert_encoding('Total', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(14, 5, number_format($totalDiaReales), 0, 0, 'C');
    $pdf->Cell(16, 5, number_format($totalDiaSTD, 2), 0, 1, 'C');
}
$endY = $pdf->GetY();
// // //---------------------------------- FIN CONTENEDOR CLAVES DE PRODUCCIÓN ----------------------------------------
// //-------------------------------------------- CONTENEDOR SATO Y PAROS  -------------------------------------------------

$seccionesAgrupadas = [];
$tiempoArribaTotal = 0;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetXY(5, $endY + 10);
$pdf->MultiCell(120, 5, mb_convert_encoding('Sato y paros por sección', 'ISO-8859-1', 'UTF-8'), 0, 'C');
foreach ($dataSecciones as $turno => $registros) {
    foreach ($registros as $r) {
        $seccion = $r['Seccion'] ?? mb_convert_encoding('Sin registro', 'ISO-8859-1', 'UTF-8');
        if (!isset($seccionesAgrupadas[$seccion])) {
            $seccionesAgrupadas[$seccion] = [
                '1' => ['count' => 0, 'sato' => 0],
                '2' => ['count' => 0, 'sato' => 0],
                '3' => ['count' => 0, 'sato' => 0],
            ];
        }
        $seccionesAgrupadas[$seccion][$turno]['count'] += $r['TotalPorSeccion'];
        // Ajustar TiempoArriba si NoMaquina está en 60..64, en caso contrario usar tal cual. Evitar división por cero.
        $tiempoArribaRaw = $r['TiempoArriba'] ?? 0;
        $noMaquina = $r['NoMaquina'] ?? null;
        if (is_numeric($tiempoArribaRaw) && in_array((int) $noMaquina, [60, 61, 62, 63, 64], true)) {
            $tiempoArriba = (float) $tiempoArribaRaw / 60;
        } else {
            $tiempoArriba = is_numeric($tiempoArribaRaw) ? (float) $tiempoArribaRaw : 0;
        }
        if ($tiempoArriba != 0) {
            $seccionesAgrupadas[$seccion][$turno]['sato'] += (($r['TotalPorSeccion'] * ($r['HorasTrabajadas'] * 60)) / $tiempoArriba);
        }
    }
    $tiempoArribaTotal += $tiempoArriba;
}

// Crear encabezado
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(10, $endY + 20);
$pdf->Cell(30, 5, mb_convert_encoding('Seccion', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');

foreach ([1, 2, 3] as $t) {
    $pdf->Cell(8, 5, $t . 'T', 0, 0, 'C');
    $pdf->Cell(10, 5, 'Sato ' . $t . 'T', 0, 0, 'C');
}
$pdf->Cell(10, 5, 'Paros', 0, 0, 'C');
$pdf->Cell(10, 5, 'Sato', 0, 1, 'C');
$pdf->SetXY(9, $endY + 20);
$pdf->Cell(20, 5, '___________________________________________________________________________');
$endY = $pdf->GetY();

// Imprimir datos por sección
$pdf->SetFont('Arial', '', 6);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(10, $endY + 5);
$lineY = 0;
foreach ($seccionesAgrupadas as $seccion => $datos) {
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 6);
    // establecer color de borde gris para esta celda y después restaurar a negro
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Cell(30, 5, mb_convert_encoding($seccion, 'ISO-8859-1', 'UTF-8'), 'B', 0, 'J');
    $totalParos = 0;
    $totalSato = 0;
    foreach ([1, 2, 3] as $t) {
        $paros = $datos[$t]['count'];
        $pdf->SetFont('Arial', '', 6);
        $sato = number_format($datos[$t]['sato'], 2);
        $pdf->Cell(8, 5, $paros > 0 ? $paros : '', 'B', 0, 'C');
        $pdf->Cell(10, 5, $paros > 0 ? $sato : '', 'B', 0, 'C');
        $totalParos += $paros;
        $totalSato = ($totalParos * 1440) / $tiempoArribaTotal;
    }
    $pdf->Cell(10, 5, $totalParos, 'B', 0, 'C');
    $pdf->Cell(10, 5, number_format($totalSato, 2), 'B', 1, 'C');
    $endY = $pdf->GetY();
    $pdf->SetXY(10, $endY);

    $lineY = $endY;
}

// Totales de secciones
$pdf->SetXY(30, $lineY);
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(10, 5, 'Total', 0, 0, 'R');

$totalParosSecciones = 0;
$totalSatoSecciones = 0;
foreach ([1, 2, 3] as $t) {
    $parosTurno = 0;
    $satoTurno = 0;
    foreach ($seccionesAgrupadas as $datos) {
        $parosTurno += $datos[$t]['count'];
        $satoTurno += $datos[$t]['sato'];
    }
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(8, 5, $parosTurno, 0, 0, 'C');
    $pdf->Cell(10, 5, number_format($satoTurno, 2), 0, 0, 'C');
    $totalParosSecciones += $parosTurno;
    $totalSatoSecciones += $satoTurno;
}


// // //-------------------------------------------- FIN CONTENEDOR SATO Y PAROS  -------------------------------------------------
//-------------------------------------------- DETALLE PAROS POR SECCIÓN -------------------------------------------------

// Página 2 (portrait)
$pdf->AddPage('P', array(220, 280));

// (Opcional, recomendado) salto automático con margen inferior
$pdf->SetAutoPageBreak(true, 20);

$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(30, 144, 227);
$pdf->SetXY(20, 20);
$pdf->Cell(190, 10, mb_convert_encoding('Detalle paros por sección', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$endY = $pdf->GetY();

// --- utilidades y constantes ---
$leftMargin = 5;
$bottomMargin = 20;
$lineHeight = 3;

// Anchos consistentes para TODAS las celdas
$wHora = 10;
$wSeccion = 30;
$wModulo = 43; // usa 43 en encabezados y filas
$wTParo = 8;
$wMotivo = 55;
$wCorreccion = 55;

// Función para calcular cuántas líneas ocupa un texto en un ancho específico
function getLinesCount($pdf, $text, $width)
{
    $text = trim((string) $text);
    if ($text === '')
        return 1;
    $words = explode(' ', $text);
    $lines = 1;
    $currentWidth = 0;

    foreach ($words as $word) {
        $wordWidth = $pdf->GetStringWidth($word . ' ');
        if ($currentWidth + $wordWidth > $width) {
            $lines++;
            $currentWidth = $wordWidth;
        } else {
            $currentWidth += $wordWidth;
        }
    }
    return $lines;
}

function printColumnHeaders($pdf, $leftMargin, $wHora, $wSeccion, $wModulo, $wTParo, $wMotivo, $wCorreccion)
{
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetTextColor(30, 144, 227);
    $pdf->setX($leftMargin);
    $pdf->Cell($wHora, 6, 'Hora', 0, 0, 'C');
    $pdf->Cell($wSeccion, 6, 'Seccion', 0, 0, 'C');
    $pdf->Cell($wModulo, 6, 'Modulo', 0, 0, 'C');
    $pdf->Cell($wTParo, 6, 'T.Paro', 0, 0, 'C');
    $pdf->Cell($wMotivo, 6, 'Motivo', 0, 0, 'C');
    $pdf->Cell($wCorreccion, 6, 'Correccion', 0, 1, 'C');
    $pdf->setXY($leftMargin, $pdf->GetY() - 2);
    $pdf->Cell(20, 0, '___________________________________________________________________________________________________________________________________________________________________________');
    $pdf->SetTextColor(0, 0, 0);
}

foreach ($dataModulos as $turno => $eventos) {

    // --- calcular alto requerido para "Turno + Encabezados" ---
    // Ln(5) + fila título (8) + fila encabezados (6) + subrayado (~2)
    $headerBlockHeight = 5 + 8 + 6 + 2;
    if ($pdf->GetY() + $headerBlockHeight > ($pdf->GetPageHeight() - $bottomMargin)) {
        $pdf->AddPage();
    }

    // --- TITULO ---
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetTextColor(30, 144, 227);
    $pdf->Cell(15, 8, 'Turno:', 0, 0, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(15, 8, $turno, 0, 1, 'L');

    // --- ENCABEZADOS ---
    printColumnHeaders($pdf, $leftMargin, $wHora, $wSeccion, $wModulo, $wTParo, $wMotivo, $wCorreccion);

    // --- CONTENIDO ---
    $pdf->SetFont('Arial', '', 5);
    $pdf->SetTextColor(0, 0, 0);

    foreach ($eventos as $evento) {
        // Normaliza textos
        $motivo = mb_convert_encoding($evento['Motivo'] ?? '-', 'UTF-8', mb_detect_encoding($evento['Motivo'] ?? '-', 'UTF-8, ISO-8859-1', true));
        $correccion = mb_convert_encoding($evento['Correccion'] ?? '-', 'UTF-8', mb_detect_encoding($evento['Correccion'] ?? '-', 'UTF-8, ISO-8859-1', true));

        // Calcular líneas para MultiCell
        $motivoLines = getLinesCount($pdf, $motivo, $wMotivo);
        $correccionLines = getLinesCount($pdf, $correccion, $wCorreccion);

        // Altura de la fila y +1 por línea separadora
        $rowHeight = max($lineHeight, $motivoLines * $lineHeight, $correccionLines * $lineHeight);
        $neededForRow = $rowHeight + 1;

        // Verificar espacio suficiente para la fila
        if ($pdf->GetY() + $neededForRow > ($pdf->GetPageHeight() - $bottomMargin)) {
            $pdf->AddPage();
            // Reimprime sólo encabezados (no el "Turno")
            printColumnHeaders($pdf, $leftMargin, $wHora, $wSeccion, $wModulo, $wTParo, $wMotivo, $wCorreccion);
        }

        // Posición inicial de la fila
        $x = $leftMargin;
        $y = $pdf->GetY() + 2;

        // Hora
        $pdf->SetXY($x, $y);
        $pdf->Cell($wHora, $rowHeight, $evento['Hora'], 0, 0, 'C');
        $x += $wHora;

        // Sección
        $pdf->SetXY($x, $y);
        $pdf->Cell($wSeccion, $rowHeight, mb_convert_encoding($evento['Seccion'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'J');
        $x += $wSeccion;

        // Módulo (usa SIEMPRE 43)
        $pdf->SetXY($x, $y);
        $pdf->Cell($wModulo, $rowHeight, mb_convert_encoding($evento['Modulos'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'J');
        $x += $wModulo;

        // Tiempo Paro
        $pdf->SetXY($x, $y);
        $tpRaw = $evento['TiempoParo'] ?? 0;
        if (!is_numeric($tpRaw)) {
            $tpRaw = 0;
        }
        $tpFloat = (float) $tpRaw;
        $sign = $tpFloat < 0 ? -1 : 1;
        $absVal = abs($tpFloat);
        $frac = $absVal - floor($absVal);
        $tpRounded = ($frac > 0.5) ? (int) ceil($absVal) * $sign : (int) floor($absVal) * $sign;
        $pdf->Cell($wTParo, $rowHeight, $tpRounded, 0, 0, 'C');
        $x += $wTParo;

        // Motivo (MultiCell mueve Y internamente)
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($wMotivo, $lineHeight, mb_convert_encoding($motivo, 'ISO-8859-1', 'UTF-8'), 0, 'J');
        $x += $wMotivo;

        // Corrección
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($wCorreccion, $lineHeight, mb_convert_encoding($correccion, 'ISO-8859-1', 'UTF-8'), 0, 'J');

        // Línea separadora de la fila (debajo de la altura máxima calculada)
        $pdf->SetXY($leftMargin, $y + $rowHeight - 1);
        $pdf->Cell(20, 0, '_____________________________________________________________________________________________________________________________________________________________________________________________________________');

        // Avanza Y para la siguiente fila
        $pdf->SetY($y + $rowHeight);
    }

    $pdf->Ln(1);
}

// ------------------------------------------ Fin del reporte ------------------------------------------------------

// // //------------------------------------------ Fin del reporte ------------------------------------------------------
$pdf->Output();


