<?php
require('../../fpdf/fpdf.php');
require_once('../../conexion.php');
require_once('consultasEtapas.php');


// Declaración de variables
$folio = $_GET['folio'];
$var = new Incidencias();
$data = $var->EncabezadoE1($folio);
$datatres = $var->Etapa3($folio);
$datacuatro = $var->Etapa4($folio);
$datacinco = $var->Etapa5($folio);
$dataseis = $var->Etapa6($folio);
$datasiete = $var->Etapa7($folio);
$dataocho = $var->Etapa8($folio);

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 10, 10, 60);
    }
}


$pdf=new PDF();
$pdf->AddPage();
//--------------------------------------------- Inicio Encabezado
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(74,137,189);
$pdf->SetXY(55,25);
$pdf->Cell(40,5,mb_convert_encoding('Etapa 1 - clasificación del evento', 'ISO-8859-1','UTF-8'));
//--------------------------------------------- Fin Encabezado

$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY($x+35, $y-20);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Rect(115,5,40,5,'DF');
$pdf->Cell(40,5,'Fecha');
$pdf->SetXY($x+30, $y-14.5);
$diaE12 = (!empty($data) && isset($data[0]['Dia'])) ? $data[0]['Dia'] : "";
$pdf->Cell(40,5,$diaE12);
$pdf->Rect(115,10,40,5);
$pdf->SetXY(127,10);
//---------------------------------------------
$pdf->SetXY($x+68,$y-20);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Rect(155,5,35,5,'DF');
$pdf->Cell(40,5,'No. Reporte');
$pdf->SetXY($x+63,$y-14.5);
// $serie = (!empty($data) && isset($data[0]['Dia'])) ? $data[0]['Dia'] : "";
$pdf->MultiCell(30,5,$data[0]['NoReporte'], 0, 'C');
$pdf->Rect(155,10,35,5);
//-------------Primera caja del encabezado---------------
$pdf->SetXY(20,35);
$pdf->SetFillColor(201,201,201);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(170,5,'Datos del solicitante',1,1,'C',true);
//----------------Datos del Solicitante-------------------------
$pdf->Rect(20,40,170,125);
$pdf->SetXY($x-72,$y+19);
$pdf->MultiCell(45,5,mb_convert_encoding($data[0]['APP'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x-14,$y+19);
$pdf->MultiCell(45,5,mb_convert_encoding($data[0]['APM'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x+38,$y+19);
$pdf->MultiCell(55,5,mb_convert_encoding($data[0]['NombreE'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY(21,45);
$pdf->SetFont('Arial','',10);
$pdf->Cell(40,5,'_____________________________________________________________________________________');
$pdf->Ln();
$pdf->SetXY(30,50);
$pdf->Cell(40,5,'Apellido paterno');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->SetX($x+20);
$pdf->Cell(40,5,'Apellido materno');
$pdf->SetX($x+80);
$pdf->Cell(40,5,'Nombre(s)');
//----------------Departamento y Puesto--------------------------
$pdf->SetXY($x-42,$y+9);
$pdf->MultiCell(30,5, $data[0]['NoEmp'], 0, 'C');
$pdf->SetXY($x-9,$y+ 9);
$pdf->MultiCell(62,5,mb_convert_encoding($data[0]['Depto'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x+55,$y+9);
$pdf->MultiCell(64,5,mb_convert_encoding($data[0]['NoArea'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x-49,$y+10);
$pdf->Cell(40,5,'_____________________________________________________________________________________');
$pdf->Ln();
$pdf->SetX($x-45); 
$pdf->Cell(40,5,mb_convert_encoding('Número de empleado', 'ISO-8859-1','UTF-8'));
$pdf->SetX($x+10);
$pdf->Cell(40,5,'Departamento');
$pdf->SetX($x+82);
$pdf->Cell(40,5,'Puesto');
//----------------Segunda caja del encabezado--------------------------
$pdf->SetXY(20,75);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,5,'Datos del afectado',1,1,'C', true);
//------------------------------------------
$pdf->SetXY($x-48,$y+ 34);
$pdf->MultiCell(45,5,mb_convert_encoding($data[0]['APPImp'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x+10,$y+ 34);
$pdf->MultiCell(45,5,mb_convert_encoding($data[0]['APMImp'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x+62,$y+ 34);
$pdf->MultiCell(55,5,mb_convert_encoding($data[0]['NombreImp'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x-50,$y+ 35);
$pdf->Cell(40,5,'______________________________________________________________________________________');
$pdf->Ln();
$pdf->SetX($x-40);
$pdf->Cell(40,5,'Apellido paterno');
$pdf->SetX($x+20);
$pdf->Cell(40,5,'Apellido materno');
$pdf->SetX($x+80);
$pdf->Cell(40,5,'Nombre(s)');
//------------------------------------------
$pdf->SetXY($x-42,$y+49);
$pdf->MultiCell(30,5, $data[0]['NoImp'], 0, 'C');
$pdf->SetXY($x-9,$y+49);
$pdf->MultiCell(62,5, mb_convert_encoding($data[0]['DepImp'],'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x+55,$y+49);
$pdf->MultiCell(64,5, mb_convert_encoding($data[0]['AreaImp'],'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x-50,$y+50);
$pdf->Cell(40,5,('______________________________________________________________________________________'));
$pdf->Ln();
$pdf->SetX($x-45);
$pdf->Cell(40,5,mb_convert_encoding('Número de empleado', 'ISO-8859-1','UTF-8'));
$pdf->SetX($x+0);
$pdf->Cell(40,5,'Departamento implicado');
$pdf->SetX($x+73);
$pdf->Cell(40,5,'Puesto implicado');
//------------------------------------------
$pdf->SetXY(20,115);
$pdf->SetFillColor(201,201,201);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(170,5,'Datos del evento',1,1,'C',true);
//------------------------------------------
$pdf->SetXY($x+67,$y+74);
$pdf->MultiCell(40,5,mb_convert_encoding($data[0]['Ver'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x-48,$y+74);
$pdf->MultiCell(45,5,mb_convert_encoding($data[0]['SubInci'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x+10,$y+74);
$pdf->MultiCell(40,5,mb_convert_encoding($data[0]['ClaseInci'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x-50,$y+75);
$pdf->Cell(40,5,('______________________________________________________________________________________'));
$pdf->Ln();
$pdf->SetX($x-45);
$pdf->Cell(40,5,mb_convert_encoding('Clasificación del evento', 'ISO-8859-1','UTF-8'));
$pdf->SetX($x+10);
$pdf->Cell(40,5,mb_convert_encoding('Sub-clasificación del evento', 'ISO-8859-1','UTF-8'));
$pdf->SetX($x+80);
$pdf->Cell(40,5,mb_convert_encoding('Versión', 'ISO-8859-1','UTF-8'));
//------------------------------------------
$pdf->SetXY($x-48,$y+89);
$pdf->MultiCell(166,5,mb_convert_encoding($data[0]['Desc'], 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetXY($x-50,$y+90);
$pdf->Cell(40,5,('______________________________________________________________________________________'));
$pdf->Ln();
$pdf->SetX($x+8);
$pdf->Cell(40,5,mb_convert_encoding('Descripción del evento reportado', 'ISO-8859-1','UTF-8'));
//------------------------------------------
$pdf->SetXY($x-45,$y+105);
$pdf->Cell(40,5,mb_convert_encoding('Lesión Reportable', 'ISO-8859-1','UTF-8'));
$pdf->SetX($x-10);
$pdf->Cell(40,5,mb_convert_encoding('Sí','ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x-5);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+5);
$pdf->Cell(40,5,'No');
$pdf->Ln();
$pdf->SetFont('Arial','',13);
$pdf->SetXY($x+10, $y+105);
$pdf->Cell(40,5,'O');
if($data[0]['CheckLesion'] == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -53.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38 , $pdf->GetY() + 0.1, 3);
}
$pdf->SetX($x+25);
$pdf->SetFont('Arial','',10);
$pdf->Cell(40,5,'Contacto con equipos energizado');
$pdf->SetX($x+85);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+90);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+100);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+105);
$pdf->Cell(40,5,'O');
if($data[0]['CheckEquipos'] == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -53.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38 , $pdf->GetY() + 0.1, 3);
}
//------------------------------------------
$pdf->Rect(20,165,85,45);
$pdf->Rect(105,165,85,45);
$pdf->SetFont('Arial','',10);
$pdf->SetXY($x-49,$y+120);
$pdf->Cell(40,5,mb_convert_encoding('Antigüedad en el puesto: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x-8,$y+120);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['AEmpresa'], 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x-49,$y+130);
$pdf->Cell(40,5,mb_convert_encoding('Antigüedad en la empresa: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x-5,$y+130);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['APuesto'], 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x-49,$y+140);
$pdf->Cell(40,5,mb_convert_encoding('Días de incapacidad: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x-15,$y+140);
$pdf->Cell(40,5, $data[0]['Incapa']);
$pdf->SetXY($x-49,$y+150);
$pdf->Cell(40,5,mb_convert_encoding('Días de trabajo: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x-23,$y+150);
$pdf->Cell(40,5, $data[0]['Trabajo']);
//------------------------------------------
$pdf->SetXY($x+36,$y+ 120);
$pdf->Cell(40,5,'Tipo de contacto:');
$pdf->SetXY($x+65,$y+ 120);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['Contacto'], 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+36,$y+130);
$pdf->Cell(40,5,mb_convert_encoding('¿Qué provocó la lesión?: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+77,$y+130);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['Provocacion'], 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+36,$y+ 140);
$pdf->Cell(40,5,mb_convert_encoding('Tipo de lesión: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+60,$y+ 140);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['Lesion'], 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+36,$y+150);
$pdf->Cell(40,5,'Parte afectada:');
$pdf->SetXY($x+61,$y+150);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['ParteAfectada'], 'ISO-8859-1','UTF-8'));
//--------------Etapa 2: Evaluación encabezado----------------------------
$pdf->SetXY(80,215);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(74,137,189);
$pdf->Cell(40,5,mb_convert_encoding('Etapa 2 - Evaluación', 'ISO-8859-1','UTF-8'));
//------------------------------------------
$pdf->SetXY(20,225);
$pdf->Rect(20,230,170,35);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,5,mb_convert_encoding('Evalua el riesgo del evento reportado para determinar el nivel de atención','ISO-8859-1','UTF-8'),1,1,'C',true);
//------------------------------------------
$pdf->SetXY($x-45,$y+185);
$pdf->Cell(40,5,'Severidad: ');
$pdf->SetXY($x-25,$y+185);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['Sev'],'ISO-8859-1','UTF-8'));
$pdf->SetXY($x-45,$y+195);
$pdf->Cell(40,5,'Probabilidad: ');
$pdf->SetXY($x-22,$y+195);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['Prob'],'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+35,$y+185);
$pdf->Cell(40,5,'Frecuencia: ');
$pdf->SetXY($x+55,$y+185);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['Frec'],'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+34,$y+195);
$pdf->Cell(40,5,mb_convert_encoding('Número de personas expuestas: ','ISO-8859-1','UTF-8'));
$pdf->SetXY($x+86,$y+195);
$pdf->MultiCell(34,5,mb_convert_encoding($data[0]['Afectados'],'ISO-8859-1','UTF-8'));
$pdf->SetXY($x-45,$y+205);
$pdf->Cell(40,5,mb_convert_encoding('Liga del archivo: ','ISO-8859-1','UTF-8'));
$pdf->SetTextColor(0,142,212);
$pdf->SetXY($x-15,$y+ 205);
$pdf->Cell(40,5,mb_convert_encoding($data[0]['Imagen'],'ISO-8859-1','UTF-8'));
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
//------------------------------------------
$pdf->AddPage();
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(74,137,189);
$pdf->SetXY($x+50,$y+15);
$pdf->Cell(40,5,mb_convert_encoding('Etapa 3 - Descripción del evento', 'ISO-8859-1','UTF-8'));
//------------------------------------------
$pdf->SetXY(20,32);
$pdf->Rect(20,35,170,240);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,5,('Eventos previos'),1,1,'C',true);
//------------------------------------------
$pdf->Ln();
$startY = $pdf->GetY(); // Obtener la posicion inicial de Y
$pdf->SetXY($x+13, $y+28);
$pdf->SetFont('Arial', '', 10);
if (isset($datatres[0]['Previos'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datatres[0]['Previos'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}


$endY = $pdf->GetY(); // Obtener la posición final de Y

//------------------------------------------
$pdf->SetXY(20,$endY+2);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,5,mb_convert_encoding('Evento de falla', 'ISO-8859-1','UTF-8'),1,1,'C',true);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY+2);
$pdf->SetFont('Arial', '', 10);
if (isset($datatres[0]['Falla'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datatres[0]['Falla'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}

$endY = $pdf->GetY();
//-----------------------------------------
$pdf->SetXY(20,$endY+2);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,5,mb_convert_encoding('Cuantificación de daños', 'ISO-8859-1','UTF-8'),1,1,'C',true);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY+ 2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Daños a equipo: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+42, $endY+ 4.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datatres[0]['Daños'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datatres[0]['Daños'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}

// //------------------------------------------
$pdf->SetXY($x+53, $endY+2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Suspensión de operaciones: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+103, $endY+4.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datatres[0]['Sus'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datatres[0]['Sus'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}

// //------------------------------------------
$pdf->SetXY($x+118, $endY+2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Perdida de producto: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+156, $endY+4.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datatres[0]['Prod'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datatres[0]['Prod'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}

$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Perdida de material: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+48, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datatres[0]['Mat'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datatres[0]['Mat'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}

//------------------------------------------
$pdf->SetXY($x+58, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Otro: ','ISO-8859-1','UTF-8'));
$pdf->SetXY($x+68, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datatres[0]['Observacion'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datatres[0]['Observacion'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}


//------------------------------------------
$pdf->SetXY($x+13, $endY+8);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Explique: ','ISO-8859-1','UTF-8'));
$pdf->SetXY($x+30, $endY+10.5);
$pdf->SetFont('Arial', '', 10);
$explique = !empty($datatres[0]['Descripcion']) ? $datatres[0]['Descripcion'] : "Sin Información";
$pdf->MultiCell(148, 5, mb_convert_encoding($explique,'ISO-8859-1','UTF-8'), 0, 'J');
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY(20,$endY+2);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,5,mb_convert_encoding('Acciones de contención','ISO-8859-1','UTF-8'),1,1,'C',true);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Descripción: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+36, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
$descripcion = !empty($datatres[0]['Descrip1']) ? $datatres[0]['Descrip1'] : 'Sin descripción';
$pdf->MultiCell(142, 5, mb_convert_encoding($descripcion, 'ISO-8859-1','UTF-8'), 0, 'J');
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Noemp: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+30, $endY);
$pdf->SetFont('Arial', '', 10);

$responsable = isset($datatres[0]['Responsable1']) && $datatres[0]['Responsable1'] != 0
    ? $datatres[0]['Responsable1']
    : '';

if ($responsable !== '') {
    $pdf->Cell(0, 10, mb_convert_encoding($responsable, 'ISO-8859-1', 'UTF-8'), 0, 1);
} else {
    $pdf->Cell(0, 10, '', 0, 1);
}

//------------------------------------------
$pdf->SetXY($x+45, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable 1: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+93, $endY);
$pdf->SetFont('Arial', '', 10);

$nombreResponsable = isset($datatres[0]['NombreEmpleado']) && $datatres[0]['NombreEmpleado'] != 0
    ? $datatres[0]['NombreEmpleado']
    : '';

if ($nombreResponsable !== '') {
    $pdf->Cell(0, 10, mb_convert_encoding($nombreResponsable, 'ISO-8859-1', 'UTF-8'), 0, 1);
} else {
    $pdf->Cell(0, 10, '', 0, 1);
}

$endY = $pdf->GetY();
// //------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Fecha de implementación: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+60, $endY+2.5);
$pdf->SetFont('Arial', '', 10);

if (isset($datatres[0]['Dia1'])) {
    $pdf->MultiCell(100, 5, mb_convert_encoding($datatres[0]['Dia1'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(100, 5, '', 0, 'J');
}

$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY(20,$endY+10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,0,'',1,1,'C',true);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Descripción: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+36, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
$descripcion2 = !empty($datatres[0]['Descrip2']) ? $datatres[0]['Descrip2'] : 'Sin descripción';
$pdf->MultiCell(142, 5, mb_convert_encoding($descripcion2, 'ISO-8859-1','UTF-8'), 0, 'J');
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Noemp: ');
$pdf->SetXY($x+28, $endY);
$pdf->SetFont('Arial', '', 10);

$responsable2 = isset($datatres[0]['Responsable2']) && $datatres[0]['Responsable2'] != 0
    ? $datatres[0]['Responsable2']
    : '';

if ($responsable2 !== '') {
    $pdf->Cell(0, 10, mb_convert_encoding($responsable2, 'ISO-8859-1', 'UTF-8'), 0, 1);
} else {
    $pdf->Cell(0, 10, '', 0, 1);
}

//------------------------------------------
$pdf->SetXY($x+45, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable 2: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+93, $endY);
$pdf->SetFont('Arial', '', 10);
$nombreResponsable2 = !empty($datatres[0]['NombreEmpleado2']) ? $datatres[0]['NombreEmpleado2'] : 'Sin información';
$pdf->Cell(0, 10, mb_convert_encoding($nombreResponsable2, 'ISO-8859-1','UTF-8'), 0, 1);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY-3);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Fecha de implementación: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+60, $endY-1);
$pdf->SetFont('Arial', '', 10); 

if (isset($datatres[0]['Dia2'])) {
    $pdf->MultiCell(100, 5, mb_convert_encoding($datatres[0]['Dia2'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(100, 5, '', 0, 'J');
}


$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY(20,$endY+10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,0,'',1,1,'C',true);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Descripción: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+36, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
$descripcion3 = !empty($datatres[0]['Descrip3']) ? $datatres[0]['Descrip3'] : 'Sin descripción';
$pdf->MultiCell(142, 5, mb_convert_encoding($descripcion3, 'ISO-8859-1','UTF-8'), 0, 'J');
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Noemp: ');
$pdf->SetXY($x+28, $endY);
$pdf->SetFont('Arial', '', 10);

$responsable3 = isset($datatres[0]['Responsable3']) && $datatres[0]['Responsable3'] != 0
    ? $datatres[0]['Responsable3']
    : '';

if ($responsable3 !== '') {
    $pdf->Cell(0, 10, mb_convert_encoding($responsable3, 'ISO-8859-1', 'UTF-8'), 0, 1);
} else {
    $pdf->Cell(0, 10, '', 0, 1);
}


//------------------------------------------
$pdf->SetXY($x+43, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable 3: ','ISO-8859-1','UTF-8'));
$pdf->SetXY($x+91, $endY);
$pdf->SetFont('Arial', '', 10);
$nombreResponsable3 = !empty($datatres[0]['NombreEmpleado3']) ? $datatres[0]['NombreEmpleado3'] : 'Sin información';
$pdf->Cell(0, 10, mb_convert_encoding($nombreResponsable3,'ISO-8859-1','UTF-8'), 0, 1);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Fecha de implementación: ','ISO-8859-1','UTF-8'));
$pdf->SetXY($x+60, $endY+2.5);
$pdf->SetFont('Arial', '', 10);

if (isset($datatres[0]['Dia3'])) {
    $pdf->MultiCell(100, 5, mb_convert_encoding($datatres[0]['Dia3'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(100, 5, '', 0, 'J');
}


//------------------------------------- ETAPA 4 ----------------------------------

$pdf->AddPage();
$pdf->SetXY(45,25);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(74,137,189);
$pdf->Multicell(130,5,mb_convert_encoding('Etapa 4 - Análisis de causas y plan de acciones correctivas', 'ISO-8859-1','UTF-8'), 0, 'C');
//------------------------------------------
$pdf->SetXY(20,40);
$pdf->Rect(20,40,170,220);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Causa inmediata:');
$pdf->SetXY($x+43, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Comp'])) {
    $pdf->MultiCell(145, 5, mb_convert_encoding($datacuatro[0]['Comp'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(145, 5, '', 0, 'J');
}
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY+7);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Subcausa:');
$pdf->SetXY($x+33, $endY+9.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Causa'])) {
    $pdf->MultiCell(145, 5, mb_convert_encoding($datacuatro[0]['Causa'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(145, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Por qué?:','ISO-8859-1','UTF-8'));
$pdf->SetXY($x+33, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Quepaso1'])) {
    $pdf->MultiCell(145, 5, mb_convert_encoding($datacuatro[0]['Quepaso1'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(145, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Causas básicas:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+43, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Basica'])) {
    $pdf->MultiCell(135, 5, mb_convert_encoding($datacuatro[0]['Basica'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(135, 5, '', 0, 'J');
}
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY+7);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Por qué?:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+33, $endY+9.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Quepaso2'])) {
    $pdf->MultiCell(145, 5, mb_convert_encoding($datacuatro[0]['Quepaso2'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(145, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Causas raíz:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+36, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Raiz'])) {
    $pdf->MultiCell(140, 5, mb_convert_encoding($datacuatro[0]['Raiz'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(140, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Por qué?:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+33, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Quepaso3'])) {
    $pdf->MultiCell(145, 5, mb_convert_encoding($datacuatro[0]['Quepaso3'], 'ISO-8859-1', 'UTF-8'), 2, 'J');
} else {
    $pdf->MultiCell(145, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Acción correctiva 1:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+48, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Correcciones'])) {
    $pdf->MultiCell(132, 5, mb_convert_encoding($datacuatro[0]['Correcciones'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(132, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Noemp:');
$pdf->SetXY($x+28, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['NoEmp'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['NoEmp'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
//------------------------------------------
$pdf->SetXY($x+43, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+88, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['NombreResponsable'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['NombreResponsable'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Fecha de implementación:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+58, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['DiaAccidente'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['DiaAccidente'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Acción correctiva 2:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+48, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Correccion2'])) {
    $pdf->MultiCell(132, 5, mb_convert_encoding($datacuatro[0]['Correccion2'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(132, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Noemp:');
$pdf->SetXY($x+28, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['responsableetapa42'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['responsableetapa42'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
//------------------------------------------
$pdf->SetXY($x+43, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+88, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['NombreResponsable2'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['NombreResponsable2'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Fecha de implementación:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+58, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['DiaAccidente2'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['DiaAccidente2'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Acción correctiva 3:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+48, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Correcciones'])) {
    $pdf->MultiCell(132, 5, mb_convert_encoding($datacuatro[0]['Correcciones'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(132, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Noemp:');
$pdf->SetXY($x+28, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['NoEmp'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['NoEmp'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
//------------------------------------------
$pdf->SetXY($x+43, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+88, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['NombreResponsable'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['NombreResponsable'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Acción correctiva 4:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+48, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Correcciones'])) {
    $pdf->MultiCell(132, 5, mb_convert_encoding($datacuatro[0]['Correcciones'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(132, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Noemp:');
$pdf->SetXY($x+28, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['NoEmp'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['NoEmp'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
//------------------------------------------
$pdf->SetXY($x+43, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+88, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['NombreResponsable'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['NombreResponsable'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Fecha de implementación:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+58, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['DiaAccidente'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['DiaAccidente'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}

$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Acción correctiva 5:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+48, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['Correcciones'])) {
    $pdf->MultiCell(132, 5, mb_convert_encoding($datacuatro[0]['Correcciones'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(132, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Noemp:');
$pdf->SetXY($x+28, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['NoEmp'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['NoEmp'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
//------------------------------------------
$pdf->SetXY($x+43, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+88, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datacuatro[0]['NombreResponsable'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datacuatro[0]['NombreResponsable'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------

$pdf->AddPage();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(74,137,189);
$pdf->SetXY($x+25,$y+16);
$pdf->Cell(40,5,mb_convert_encoding('Etapa 5 - Seguridad centrada en las personas', 'ISO-8859-1', 'UTF-8'));
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY(20,$endY+10);
$pdf->Rect(20,$endY+10,170,180);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,5,mb_convert_encoding('¿Qué estados y errores estan asociados con la decisión / comportamiento?', 'ISO-8859-1', 'UTF-8'),1,1,'C',true);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Prisa:', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+55, $endY+2.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+60);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+65);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+70);
$pdf->Cell(40,5,'O');
$prisa = (!empty($datacinco) && isset($datacinco[0]['Prisa'])) ? $datacinco[0]['Prisa'] : "";
if($prisa == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}

//------------------------------------------
$pdf->SetXY($x+80, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Ojos y mente en la tarea:', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+2.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$ojosTarea = (!empty($datacinco) && isset($datacinco[0]['OjosTarea'])) ? $datacinco[0]['OjosTarea'] : "";
if($ojosTarea == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Frustación:', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+55, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+60);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+65);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+ 70);
$pdf->Cell(40,5,'O');
$frustracion = (!empty($datacinco) && isset($datacinco[0]['Frustracion'])) ? $datacinco[0]['Frustracion'] : "";
if($frustracion == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
//------------------------------------------

$pdf->SetXY($x+80, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Mente no en la tarea:', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$mente = (!empty($datacinco) && isset($datacinco[0]['Mente'])) ? $datacinco[0]['Mente'] : "";
if($mente == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Fatiga:', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+55, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+60);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 65);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+70);
$pdf->Cell(40,5,'O');
$fatiga = (!empty($datacinco) && isset($datacinco[0]['Fatiga'])) ? $datacinco[0]['Fatiga'] : "";
if($fatiga == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}

//------------------------------------------
$pdf->SetXY($x+80, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Colocarse en la zona de peligro:', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$peligro = (!empty($datacinco) && isset($datacinco[0]['Peligro'])) ? $datacinco[0]['Peligro'] : "";
if($peligro == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();

//------------------------------------------
$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Tolerancia al riesgo:', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+55, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+60);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 65);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+70);
$pdf->Cell(40,5,'O');
$riesgo = (!empty($datacinco) && isset($datacinco[0]['Riesgo'])) ? $datacinco[0]['Riesgo'] : "";
if($riesgo == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
// //------------------------------------------

$pdf->SetXY($x+80, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Perdida del equilibrio, tracción y agarre:', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$equilibrio = (!empty($datacinco) && isset($datacinco[0]['Equilibrio'])) ? $datacinco[0]['Peligro'] : "";
if($equilibrio == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();

//------------------------------------------
$pdf->SetXY(20,$endY);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,5,mb_convert_encoding('Interacciones 1 a 1', 'ISO-8859-1', 'UTF-8'),1,1,'C',true);
$endY = $pdf->GetY();

//------------------------------------------
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Tuvo una conversación con el(los) colaborador(es) implicado(s)?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+2.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$colaImp = (!empty($datacinco) && isset($datacinco[0]['ColabImp'])) ? $datacinco[0]['ColabImp'] : "";
if($colaImp == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------

$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Reconoció las acciones y decisiones seguras realizadas?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$accionesSeguras = (!empty($datacinco) && isset($datacinco[0]['AccionesSeguras'])) ? $datacinco[0]['AccionesSeguras'] : "";
if($accionesSeguras == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------

$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Generó acuerdos con la(s) persona(s) observada(s)?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$persObservadas = (!empty($datacinco) && isset($datacinco[0]['PerObservada'])) ? $datacinco[0]['PerObservada'] : "";
if($persObservadas == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------

$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Proporcionó una retroalimentación positiva a la(s) persona(s)?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$retroalimentacion = (!empty($datacinco) && isset($datacinco[0]['Retroalimentacion'])) ? $datacinco[0]['Retroalimentacion'] : "";
if($retroalimentacion == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------

$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Se modificó la experiencia de la(s) persona(s) durante la retroalimentación?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$experiencia = (!empty($datacinco) && isset($datacinco[0]['Experiencia'])) ? $datacinco[0]['Experiencia'] : "";
if($experiencia == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------

$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Requiere observación de seguimiento? ¿Cuándo? Inmediato', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+6.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$seguimiento = (!empty($datacinco) && isset($datacinco[0]['Seguimiento'])) ? $datacinco[0]['Seguimiento'] : "";
if($seguimiento == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();

//------------------------------------------
$pdf->SetXY(20,$endY+10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$pdf->Cell(170,0,'',1,1,'C',true);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY(20,$endY+5);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$endY = $pdf->GetY();

//------------------------------------------
$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(130, 5, mb_convert_encoding('Si el incidente ocurrió en un equipo o maquinaria, ¿Este cuenta con una evaluación de riesgos?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+9.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$incOcurrido = (!empty($datacinco) && isset($datacinco[0]['incOcurrido'])) ? $datacinco[0]['incOcurrido'] : "";
if($incOcurrido == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Por qué?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+33, $endY+6.6);
$pdf->SetFont('Arial', '', 10);
$descripcion1 = !empty($datacinco[0]['Descripcion1']) ? $datacinco[0]['Descripcion1'] : 'Sin descripción';
$pdf->MultiCell(140, 5, mb_convert_encoding( $descripcion1, 'ISO-8859-1','UTF-8'), 0, 'J');
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(130, 5, mb_convert_encoding('¿La evaluación de riesgos de la máquina o equipo considera la exposición al peligro y/o escenario de riesgo?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+8.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$riesgoMaquina = (!empty($datacinco) && isset($datacinco[0]['RiesgoMaquina'])) ? $datacinco[0]['RiesgoMaquina'] : "";
if($riesgoMaquina == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+6);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Por qué?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+33, $endY+8.6);
$pdf->SetFont('Arial', '', 10);
$descripcion2 = !empty($datacinco[0]['Descripcion2']) ? $datacinco[0]['Descripcion2'] : 'Sin descripción';
$pdf->MultiCell(140, 5, mb_convert_encoding( $descripcion2, 'ISO-8859-1','UTF-8'), 0, 'J');
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(130, 5, mb_convert_encoding('¿Se cuenta con un análisis de riesgos y procedimiento de operación de la tarea que se estaba ejecutando?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+8.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$analisisRiesgos = (!empty($datacinco) && isset($datacinco[0]['AnalisisRiesgos'])) ? $datacinco[0]['AnalisisRiesgos'] : "";
if($analisisRiesgos == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+6);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Por qué?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+33, $endY+8.6);
$pdf->SetFont('Arial', '', 10);
$descripcion3 = !empty($datacinco[0]['Descripcion3']) ? $datacinco[0]['Descripcion3'] : 'Sin descripción';
$pdf->MultiCell(140, 5, mb_convert_encoding( $descripcion3, 'ISO-8859-1','UTF-8'), 0, 'J');
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(130, 5, mb_convert_encoding('¿El análisis de riesgos y procedimiento de operación estándar consideran el escenario de riesgo relacionado con el inciidente ocurrido?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+150, $endY+8.6);
$pdf->Cell(40,5,mb_convert_encoding('Sí', 'ISO-8859-1','UTF-8'));
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+155);
$pdf->Cell(40,5,'O');
$pdf->SetFont('Arial','',10);
$pdf->SetX($x+ 160);
$pdf->Cell(40,5,'No');
$pdf->SetFont('Arial','',13);
$pdf->SetX($x+165);
$pdf->Cell(40,5,'O');
$escenarioRiesgo = (!empty($datacinco) && isset($datacinco[0]['EscenarioRiesgo'])) ? $datacinco[0]['EscenarioRiesgo'] : "";
if($escenarioRiesgo == 1){
    $pdf->Image("../../img/done.png", $pdf->GetX() -48.2 , $pdf->GetY() + 0.1, 3);
}else{
    $pdf->Image("../../img/no.png", $pdf->GetX() -38.1 , $pdf->GetY() + 0.1, 3);
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+6);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Por qué?', 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY($x+33, $endY+8.6);
$pdf->SetFont('Arial', '', 10);
$SisGestion = !empty($datacinco[0]['Descripcion4']) ? $datacinco[0]['Descripcion4'] : 'Sin descripción';
$pdf->MultiCell(140, 5, mb_convert_encoding( $descripcion3, 'ISO-8859-1','UTF-8'), 0, 'J');
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY(40,$endY+15);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(74,137,189);
$pdf->MultiCell(130,5,mb_convert_encoding('Etapa 6 - Elemento del sistema de gestión EHS que debe ser mejorado', 'ISO-8859-1','UTF-8'), 0, 'C');
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Rect(20,$endY+4,170,40);
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Ln();
$pdf->SetXY($x+13, $endY+4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Elemento del sistema de gestión:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+72, $endY+4);
$pdf->SetFont('Arial', '', 10);
if (!empty($dataseis) && isset($dataseis[0]['tipoelemento'])) {
    $pdf->Cell(0, 10, mb_convert_encoding($dataseis[0]['tipoelemento'], 'ISO-8859-1','UTF-8'));
} else {
    $pdf->Cell(0, 10, 'No disponible');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+6);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Subgestión: ', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+35, $endY+6);
$pdf->SetFont('Arial', '', 10);
if (!empty($dataseis) && isset($dataseis[0]['elemento'])) {
    $pdf->Cell(0, 10, mb_convert_encoding($dataseis[0]['elemento'], 'ISO-8859-1','UTF-8'));
} else {
    $pdf->Cell(0, 10, 'No disponible');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY+6);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('¿Por qué?', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+32, $endY+6);
$pdf->SetFont('Arial', '', 10);
if (!empty($dataseis) && isset($dataseis[0]['sistemagestionporque'])) {
    $pdf->Cell(0, 10, mb_convert_encoding($dataseis[0]['sistemagestionporque'], 'ISO-8859-1','UTF-8'));
} else {
    $pdf->Cell(0, 10, 'No disponible');
}
//------------------------------------------
$pdf->AddPage();
$pdf->SetXY(40,25);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(74,137,189);
$pdf->MultiCell(130,5,mb_convert_encoding('Etapa 7 - Elaboración del reporte de investigación', 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$endY = $pdf->GetY();
//------------------------------------------
$pdf->Rect(20,$endY+5,170,30);
$pdf->Ln();
$pdf->SetXY($x+13, $endY+5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Noemp:');
$pdf->SetXY($x+28, $endY+7.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datasiete[0]['noemp'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datasiete[0]['noemp'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
//------------------------------------------
$pdf->SetXY($x+43, $endY+5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+88, $endY+7.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datasiete[0]['Nombre'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datasiete[0]['Nombre'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//-----------------------------------------
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Area:');
$pdf->SetXY($x+25, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datasiete[0]['NombreDepto'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datasiete[0]['NombreDepto'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Puesto:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+28, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($datasiete[0]['NombrePuesto'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($datasiete[0]['NombrePuesto'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY(40,$endY+10);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(74,137,189);
$pdf->MultiCell(130,5,mb_convert_encoding('Etapa 8 - Revisión del reporte de investigación', 'ISO-8859-1','UTF-8'), 0, 'C');
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(201,201,201);
$pdf->SetDrawColor(0,0,0);
$endY = $pdf->GetY();
//----------------------
$pdf->Rect(20,$endY+5,170,30);
$pdf->Ln();
$pdf->SetXY($x+13, $endY+5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Noemp:');
$pdf->SetXY($x+28, $endY+7.5);
$pdf->SetFont('Arial', '', 10);
if (isset($dataocho[0]['noemp'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($dataocho[0]['noemp'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
//------------------------------------------
$pdf->SetXY($x+43, $endY+5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Nombre del responsable:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+88, $endY+7.5);
$pdf->SetFont('Arial', '', 10);
if (isset($dataocho[0]['Nombre'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($dataocho[0]['Nombre'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//-----------------------------------------
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Area:');
$pdf->SetXY($x+25, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($dataocho[0]['NombreDepto'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($dataocho[0]['NombreDepto'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}
$endY = $pdf->GetY();
//------------------------------------------
$pdf->SetXY($x+13, $endY);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, mb_convert_encoding('Tipo Evaluador:', 'ISO-8859-1','UTF-8'));
$pdf->SetXY($x+43, $endY+2.5);
$pdf->SetFont('Arial', '', 10);
if (isset($dataocho[0]['tipoEvaluador'])) {
    $pdf->MultiCell(165, 5, mb_convert_encoding($dataocho[0]['tipoEvaluador'], 'ISO-8859-1', 'UTF-8'), 0, 'J');
} else {
    $pdf->MultiCell(165, 5, '', 0, 'J');
}

$pdf->Output('I', 'ReporteIncidencias.pdf');