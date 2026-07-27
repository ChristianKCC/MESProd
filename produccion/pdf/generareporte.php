<?php 
//Llamamos a la librería
include "../../../csql32.php";
include "../../fpdf183/fpdf.php";
$query=$_POST['query'];
class PDF extends FPDF
{
function Header()
{
$titulo=$_POST['titulo'];
$fechaActual = date('d-m-Y'); 
$this->Image('img/kcm.png',10,0,150);
$this->SetTitle('Empleados');
$this->SetTextColor(255,255,255);
$this->SetFont('Arial','',12);
$this->Cell(190,10,$titulo,1,1,'C',1);
$this->SetFont('Arial','',8);
$this->SetTextColor(0,0,0);
$this->Cell(190,5,'Fecha: '.$fechaActual,0,0,'R',0);
$this->Ln();
}
	

// Pie de página
function Footer()
{
    // Posición: a 1,5 cm del final
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Número de página
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->SetFont('Arial','B',10);
			$pdf->Cell(20,5,'Maquina');
			$pdf->Cell(20,5,'Seccion');
			$pdf->Cell(20,5,'Cortes');
			$pdf->Cell(20,5,'Rechazos');
			$pdf->Cell(30,5,'Fecha / Hora');
			$pdf->Cell(20,5,'Falla');
			$pdf->Cell(30,5,'Comentarios');
			$pdf->Ln();
			$pdf->SetFont('Times','',8);
				$consulta2 = $query;
				$result = sqlsrv_query($conn, $consulta2);	
				while ($fila = sqlsrv_fetch_array($result))
				{
				$date = $fila['hora'];
				$pdf->Cell(20,5,$fila['maquina']);
				$pdf->Cell(20,5,$fila['seccion']);
				$pdf->Cell(20,5,$fila['Cortesc']);
				$pdf->Cell(20,5,$fila['Rechazosc']);
				$pdf->Cell(30,5,$date->format("Y-m-d H:i:s"));
				$pdf->Cell(20,5,$fila['falla']);
				$pdf->MultiCell(30,5,$fila['comentario']);
				$y=$pdf->GetY();
				if($y>260)
				$pdf->AddPage();	
				}
$pdf->Output();
?>