<?php
require('../../fpdf/fpdf.php');
require_once('../../conexion.php');
require_once('Consultas.php');


// Declaración de variables
$folio = $_GET['folio'];
$var = new ValeProductosConsultas();
$data = $var->generarValePDF($folio);

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 10, 8, 50);
    }

    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x+$r)*$k, ($hp-$y)*$k));
        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-$y)*$k));
        $this->_Arc($xc+$r*$MyArc, $yc-$r, $xc+$r, $yc-$r*$MyArc, $xc+$r, $yc);
        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k, ($hp-$yc)*$k));
        $this->_Arc($xc+$r, $yc+$r*$MyArc, $xc+$r*$MyArc, $yc+$r, $xc, $yc+$r);
        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k, ($hp-($y+$h))*$k));
        $this->_Arc($xc-$r*$MyArc, $yc+$r, $xc-$r, $yc+$r*$MyArc, $xc-$r, $yc);
        $xc = $x+$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $x*$k, ($hp-$yc)*$k));
        $this->_Arc($xc-$r, $yc-$r*$MyArc, $xc-$r*$MyArc, $yc-$r, $xc, $yc-$r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k,
            $x3*$this->k, ($h-$y3)*$this->k));
    }
}
 
$pdf = new PDF('L', 'mm', array(210, 130));
$pdf->AddPage();
//--------------------------------------------- Inicio Encabezado
$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(74,137,189);
$pdf->SetXY(100,8);
$pdf->Cell(40,5,mb_convert_encoding('VENTAS A EMPLEADOS', 'ISO-8859-1','UTF-8'));
$endY = $pdf->GetY();
$pdf->SetXY(87,y: $endY+6);
$pdf->Cell(40,5,mb_convert_encoding('DE PRODUCTOS DE', 'ISO-8859-1','UTF-8'));
$pdf->SetXY(130,y: $endY+6);
$pdf->SetFont('Arial', 'IB');
$pdf->Cell(40,5,mb_convert_encoding('Kimberly-Clark', 'ISO-8859-1','UTF-8'));
$endY = $pdf->GetY();
//----------------Datos del Solicitante-------------------------
$pdf->SetXY(15,$endY+10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50,5,'FECHA:');
$pdf->SetXY(30,$endY+ 10);
$pdf->SetFont('Arial','',10);
$pdf->Cell(40,5,'____________________________');
$pdf->SetXY(87,$endY+10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60,5,'CORRESPONDIENTE AL MES DE:');
$pdf->SetXY(145,$endY+ 10);
$pdf->SetFont('Arial','',10);
$pdf->Cell(40,5,'_________________________');
$endY = $pdf->GetY();
//--------------------------------------
$pdf->SetXY(33,$endY);
$pdf->MultiCell(50,5, $data[0]['FechaVale'], 0, 'C');
//--------------------------------------
$pdf->SetXY(146,$endY);
$pdf->MultiCell(49,5, $data[0]['Mes'], 0, 'C');
//--------------------------------------
$pdf->SetXY(14,$endY+10);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50,5,'DATOS DEL EMPLEADO');
// $pdf->Rect(15,40,220,15);
$pdf->RoundedRect(15, 40, 181, 16, 3, 'D'); // x, y, ancho, alto, radio, estil
$endY = $pdf->GetY();
// -----------------------------------
$pdf->SetXY(14,$endY+ 9);
$pdf->SetFont('Arial','',10);
$pdf->Cell(40,5,'____________________________________________________________________________________________');
$pdf->Rect(47,$endY+6,38,16);
//------------------
$pdf->SetXY(20,$endY+8);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,5,mb_convert_encoding('N°. DEPTO.', 'ISO-8859-1','UTF-8'));
$pdf->SetXY(51,$endY+8);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,5,mb_convert_encoding('N°. EMPLEADO', 'ISO-8859-1','UTF-8'));
$pdf->SetXY(130,$endY+8);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,5,mb_convert_encoding('NOMBRE', 'ISO-8859-1','UTF-8'));
$endY = $pdf->GetY();
//--------------------------------------
$pdf->SetFont('Arial','',10);
$pdf->SetXY(15,$endY+7);
$pdf->MultiCell(32,5, mb_convert_encoding($data[0]['NoDepto'],'ISO-8859-1','UTF-8'), 0, 'C');
//--------------------------------------
$pdf->SetXY(47,$endY+7);
$pdf->MultiCell(38,5, mb_convert_encoding($data[0]['NoEmp'],'ISO-8859-1','UTF-8'), 0, 'C');
//--------------------------------------
$pdf->SetXY(90,$endY+7);
$pdf->MultiCell(100,5, mb_convert_encoding($data[0]['Nombre'],'ISO-8859-1','UTF-8'), 0, 'C');
//--------------------------------------
$pdf->SetXY(14,$endY+18);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50,5,'PRODUCTO QUE SOLICITA');
// $pdf->Rect(15,40,220,15);
$pdf->RoundedRect(15, $endY+24, 181, 20, 3, 'D'); 
$endY = $pdf->GetY();
// -----------------------------------
$pdf->SetXY(14,$endY+ 9);
$pdf->SetFont('Arial','',10);
$pdf->Cell(40,5,'____________________________________________________________________________________________');
$pdf->Rect(55,$endY+6,100,20);

//------------------
$pdf->SetXY(27,$endY+8);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,5,mb_convert_encoding('CLAVE', 'ISO-8859-1','UTF-8'));
//------------------
$pdf->SetXY(90,$endY+8);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,5,mb_convert_encoding('DESCRIPCIÓN', 'ISO-8859-1','UTF-8'));
//------------------
$pdf->SetXY(167,$endY+8);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,5,mb_convert_encoding('PRECIO', 'ISO-8859-1','UTF-8'));
$endY = $pdf->GetY();
//--------------------------------
$pdf->SetFont('Arial','',10);
$pdf->SetXY(15,$endY+9);
$pdf->MultiCell(40,5, mb_convert_encoding($data[0]['ClaveProducto'],'ISO-8859-1','UTF-8'), 0, 'C');
//--------------------------------------
$descripcion = str_replace(array("\r", "\n"), ' ', $data[0]['Descripcion']);
$pdf->SetXY(57, $endY + 6.5);
$pdf->MultiCell(95, 5, mb_convert_encoding($descripcion, 'ISO-8859-1', 'UTF-8'), 0, 'C');
//--------------------------------------
$pdf->SetXY(155,$endY+9);
$pdf->MultiCell(41,5, mb_convert_encoding($data[0]['Precio'],'ISO-8859-1','UTF-8'), 0, 'C');
//--------------------------------------
$pdf->RoundedRect(15, $endY+20, 71, 16, 3, 'D');
$pdf->SetXY(14,$endY+26);
$pdf->SetFont('Arial','',10);
$pdf->Cell(40,5,'____________________________________');
//--------------------------------
$pdf->SetXY(88,$endY+23);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(35,5,mb_convert_encoding('"Tiempo de retención 1 año"', 'ISO-8859-1','UTF-8'), 0, 'C');
//--------------------------------
$pdf->RoundedRect(125, $endY+20, 71, 16, 3, 'D');
$pdf->SetXY(124,$endY+26);
$pdf->SetFont('Arial','',10);
$pdf->Cell(40,5,'____________________________________');
$endY = $pdf->GetY();
//--------------------
$pdf->SetXY(27,$endY+5);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,5,mb_convert_encoding('FIRMA DEL SOLICITANTE', 'ISO-8859-1','UTF-8'));
//--------------------
$pdf->SetXY(128,$endY+5);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,5,mb_convert_encoding('FIRMA DE RECIBIDO EL PRODUCTO', 'ISO-8859-1','UTF-8'));


$pdf->Output('I', 'ReporteIncidencias.pdf');