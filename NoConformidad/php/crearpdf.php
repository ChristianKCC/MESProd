<?php
require('../../fpdf/fpdf.php');
require_once "../../conexion.php";
$conection = new ClassConexion();
$conn=$conection->conexion("TLX002MXDB");
$id=$_GET['id'];
$query="SELECT tblNoConformidad.id,tblDepartamentos.NombreDepto,tblEmpleados.Nombre,emp2.Nombre,tblNoConformidad.fecha,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,tblValeEClaves.Descripcion_Articulo as nomproducto,trazabilidaddefectos.nombre,Trazabilidadturno.nombre,
tblNoConformidad.hora,tblNoConformidad.descripcion,tblNoConformidad.totalprod,tblNoConformidad.prodrecuperado,tblNoConformidad.prodmerma, tblNoConformidad.accionescorrectivas,
tblNoConformidad.producto as claveid, lideres.Nombre as lider,calideses.nombre as calidad,tblNoConformidad.codempdefecto,tblNoConformidad.codterdefecto,tblNoConformidadComponentes.Componente
FROM tblNoConformidad INNER JOIN TLX009MXDB.dbo.tblMaquinas ON TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblNoConformidad.maquina INNER JOIN TLX036MXDB.dbo.trazabilidaddefectos on trazabilidaddefectos.id = tblNoConformidad.defecto 
INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto = tblNoConformidad.departamento
LEFT JOIN TLX032MXDB.dbo.tblEmpleados on TLX032MXDB.dbo.tblEmpleados.NoEmp = tblNoConformidad.sellador 
LEFT JOIN TLX032MXDB.dbo.tblEmpleados AS emp2 on emp2.NoEmp = tblNoConformidad.operador
LEFT JOIN TLX032MXDB.dbo.tblEmpleados AS lideres on lideres.NoEmp = tblNoConformidad.lider
LEFT JOIN TLX032MXDB.dbo.tblEmpleados AS calideses on calideses.NoEmp = tblNoConformidad.calidad
INNER JOIN TLX002MXDB.dbo.tblValeEClaves ON tblValeEClaves.NoClave=tblNoConformidad.producto 
INNER JOIN TLX036MXDB.dbo.Trazabilidadturno on Trazabilidadturno.id = tblNoConformidad.turno
LEFT JOIN tblNoConformidadComponentes ON tblNoConformidadComponentes.id = tblNoConformidad.componente WHERE tblNoConformidad.id=$id";
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png',10,5,80);
$pdf->Ln(20);
$Y = $pdf->GetY();
$pdf->SetFont('Arial','',10);
$pdf->SetY(10);
$pdf->SetX(180);
$pdf->Cell(10,10,utf8_decode('FORM-56511'));
$pdf->SetY(15);
$pdf->SetX(180);
$pdf->Cell(10,10,utf8_decode('PR-28133'));
$pdf->SetXY(180,20);
$pdf->Cell(10,10,utf8_decode('F-' .$id));
$pdf->Ln(20);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(20,10,"");
$pdf->Cell(10,10,utf8_decode('Producto No Conforme Departamento de Producción'));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->SetFont('Arial','B',12);
$result=sqlsrv_query($conn,$query);
$senddata=$query;
$pdf->SetFont('Arial','',8);
while ($fila = sqlsrv_fetch_array($result)) {
$pdf->Cell(20,10,utf8_decode("Departamento: "));
$pdf->Cell(30,10,utf8_decode($fila[1]));
$pdf->Ln();
$pdf->Cell(10,10,utf8_decode("Fecha:"));
$pdf->Cell(20,10,$fila["fecha"]->format("d-m-Y"));
$pdf->Cell(25,10,utf8_decode("Sellador de calidad:"));
$pdf->Cell(70,10,utf8_decode($fila[2]));

$pdf->Cell(14,10,utf8_decode("Operador:"));
$pdf->Cell(70,10,utf8_decode($fila[3]));
$pdf->Ln();
$pdf->Cell(12,10,utf8_decode("Maquina:"));
$pdf->Cell(15,10,utf8_decode($fila["NombreMaquina"]));
$pdf->Cell(10,10,utf8_decode("Turno:"));
$pdf->Cell(15,10,utf8_decode($fila[8]));
$pdf->Cell(22,10,utf8_decode("Producto Clave:"));
$pdf->Cell(80,10,utf8_decode($fila['nomproducto']));
$pdf->Cell(22,10,utf8_decode("Hora de desvio:"));
$pdf->Cell(30,10,utf8_decode($fila["hora"]));
$pdf->Ln();
$pdf->Cell(22,10,utf8_decode("Defecto:"));
$pdf->Cell(30,10,utf8_decode($fila[7]));
$pdf->Ln();
$pdf->Cell(22,10,utf8_decode("Causa del defecto encontrado:"));
$pdf->Ln(5);
$pdf->Cell(30,10,utf8_decode($fila[10]));
$pdf->Ln();
$pdf->Cell(80,10,utf8_decode("Tota de producto retenido:"));
$pdf->Cell(80,10,utf8_decode("Producto recuperado:"));
$pdf->Cell(80,10,utf8_decode("Producto a merma:"));
$pdf->Ln(5);
$pdf->Cell(80,10,utf8_decode($fila["totalprod"]));
$pdf->Cell(80,10,utf8_decode($fila["prodrecuperado"]));
$pdf->Cell(80,10,utf8_decode($fila["prodmerma"]));
$pdf->Ln();
$pdf->Cell(22,10,utf8_decode("Correcciones / Acciones de contención:"));
$pdf->Ln(5);
$pdf->MultiCell(240,10,utf8_decode($fila["accionescorrectivas"]));
$pdf->Cell(60,10,utf8_decode("Codigo de inicio:"));
$pdf->Cell(60,10,utf8_decode("Componente:"));
$pdf->Cell(60,10,utf8_decode("Codigo final:"));
$pdf->Ln(5);
$pdf->Cell(60,10,utf8_decode($fila["codempdefecto"]));
$pdf->Cell(60,10,utf8_decode($fila["Componente"]));
$pdf->Cell(60,10,utf8_decode($fila["codterdefecto"]));
$pdf->Ln(25);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(60,10,utf8_decode("Operador"));
$pdf->Cell(0,10,utf8_decode($fila[3]));
$pdf->Ln();
$pdf->Cell(60,10,utf8_decode("Ing. De Proceso/ Líder"));
$pdf->Cell(0,10,utf8_decode($fila[16]));

$pdf->Ln();
$pdf->Cell(60,10,utf8_decode("Departamento Técnico"));
$pdf->Cell(0,10,utf8_decode($fila[17]));
$pdf->Ln();

}
$pdf->Ln(40);
$pdf->Cell(50,10,"");
$pdf->Cell(80,10,utf8_decode("Mi firma electrónica es y será el equivalente legal de mi firma manuscrita"),0,0,'C');
$Y = $pdf->GetY();
$pdf->Output();
?>