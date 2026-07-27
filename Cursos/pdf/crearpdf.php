<?php
require('../../fpdf/fpdf.php');
require_once "../../conexion.php";
$Conection = new ClassConexion();
$conn = $Conection->conexion("TLX035MXDB");
$idenc=$_GET["idcap"];
$pdf = new FPDF();
$pdf->SetAutoPageBreak(true);
$query="SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre,tblEmpleados.CURP,tblEmpleados.IdClvOcupaciones,tblPuestos.nombre as puesto,tblCursos.NombreCurso,
tblCursos.Duracion,tblCursos.IdClvAreaTematica,tblEncabezadoCapturaCapacitacion.FechaInicial,tblEncabezadoCapturaCapacitacion.FechaFinal 
FROM TLX032MXDB.dbo.tblEmpleados 
INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id= tblEmpleados.Puesto           
INNER JOIN tblSubEncabCapturaCapacitacion ON tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp 
INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura 
INNER JOIN tblCursos ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso WHERE tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura=$idenc";
$result=sqlsrv_query($conn,$query);
while($row = sqlsrv_fetch_array($result)){
$nombre=$row[1];
$curp=$row[2];
$claveocupacion=$row[3];
$puesto=$row[4];
$nombrecurso=$row[5];
$duracionhrs=$row[6];
$atematica=$row[7];
$fechaini=$row[8]->format("Ymd");
$fechaf=$row[9]->format("Ymd");
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png',20,10,60);
$pdf->SetFont('Arial','B',14);
$pdf->SetX(150);
$pdf->Cell(100,10,utf8_decode("INTERNO"));
$pdf->Ln(10);
$pdf->SetX(90);
$pdf->Cell(10,10,utf8_decode('FORMATO DC-3'));
$pdf->Ln();
$pdf->SetX(25);
$pdf->Cell(10,10,utf8_decode('CONSTANCIA DE COMPETENCIAS O DE HABILIDADES LABORALES'));
$pdf->Ln();
$pdf->SetX(10);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190, 5, utf8_decode('DATOS DEL TRABAJADOR'), 1, 1, 'C',1);
$pdf->Ln();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(0,0,utf8_decode('Nombre (Anotar apellido paterno, apellido materno y nombre (s))'));
$pdf->SetX(10);
// Nombre
$pdf->Cell(0,10,utf8_decode($nombre));
$pdf->Ln(10);
$Y = $pdf->GetY();
$pdf->Line(10, $Y ,200 , $Y  );
$pdf->Cell(0,10,utf8_decode('Clave Única de Registro de Población'));
$pdf->SetX(10);
$x=$pdf->GetX();
$x=$x-5;
// CURP
for ($i = 0; $i < strlen($curp); $i++) {
    $caracter = substr($curp, $i, 1);
    $pdf->Cell($x,25,utf8_decode($caracter));
}

$pdf->Line(15, $Y+10 ,15 , $Y+15 );
$pdf->Line(20, $Y+10 ,20 , $Y+15 );
$pdf->Line(25, $Y+10 ,25 , $Y+15 );
$pdf->Line(30, $Y+10 ,30 , $Y+15 );
$pdf->Line(35, $Y+10 ,35 , $Y+15 );
$pdf->Line(40, $Y+10 ,40 , $Y+15 );
$pdf->Line(45, $Y+10 ,45 , $Y+15 );
$pdf->Line(50, $Y+10 ,50 , $Y+15 );
$pdf->Line(55, $Y+10 ,55 , $Y+15 );
$pdf->Line(60, $Y+10 ,60 , $Y+15 );
$pdf->Line(65, $Y+10 ,65 , $Y+15 );
$pdf->Line(70, $Y+10 ,70 , $Y+15 );
$pdf->Line(75, $Y+10 ,75 , $Y+15 );
$pdf->Line(80, $Y+10 ,80 , $Y+15 );
$pdf->Line(85, $Y+10 ,85 , $Y+15 );
$pdf->Line(90, $Y+10 ,90 , $Y+15 );
$pdf->Line(95, $Y+10 ,95 , $Y+15 );
$pdf->Line(100, $Y+10 ,100 , $Y+15 );


$pdf->Line(100, $Y ,100 , $Y+15 );
$pdf->SetX(100);
$pdf->Cell(10,10,utf8_decode('Ocupación específica (Catálogo Nacional de Ocupaciones) 1/'));
$pdf->SetX(100);
// Clave de ocupacion
$pdf->Cell(0,20,utf8_decode('05 PROCESAMIENTO Y FABRICACION'));
$pdf->Ln(15);
$Y = $pdf->GetY();
$pdf->Line(10, $Y ,200 , $Y  );
$pdf->Cell(0,10,utf8_decode('Puesto*'));
$pdf->SetX(10);
// Puesto
$pdf->Cell(0,20,utf8_decode($puesto));
$pdf->Ln(15);
$pdf->Rect(10, 45, 190, 45);



// segundo cuadro


$pdf->Ln(5);
$pdf->SetX(10);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190, 5, utf8_decode('DATOS DE LA EMPRESA'), 1, 1, 'C',1);
$pdf->Ln();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(0,0,utf8_decode('Nombre o razón social (En caso de persona física, anotar apellido paterno, apellido materno y nombre(s))'));
$pdf->SetX(10);
// Nombre
$pdf->Cell(0,10,utf8_decode('KIMBERLY CLARK DE MEXICO S.A.B DE C.V..'));
$pdf->Ln(10);
$Y = $pdf->GetY();
$pdf->Line(10, $Y ,200 , $Y  );
$pdf->Cell(0,10,utf8_decode('Registro Federal de Contribuyentes con homoclave (SHCP)'));
$pdf->SetX(10);
$x=$pdf->GetX();
$x=$x-5;
// CURP
$pdf->Cell($x,25,utf8_decode('K'));
$pdf->Cell($x,25,utf8_decode('C'));
$pdf->Cell($x,25,utf8_decode('M'));
$pdf->Cell($x,25,utf8_decode('-'));
$pdf->Cell($x,25,utf8_decode('8'));
$pdf->Cell($x,25,utf8_decode('1'));
$pdf->Cell($x,25,utf8_decode('0'));
$pdf->Cell($x,25,utf8_decode('2'));
$pdf->Cell($x,25,utf8_decode('2'));
$pdf->Cell($x,25,utf8_decode('6'));
$pdf->Cell($x,25,utf8_decode('-'));
$pdf->Cell($x,25,utf8_decode('D'));
$pdf->Cell($x,25,utf8_decode('E'));
$pdf->Cell($x,25,utf8_decode('A'));

$pdf->Line(15, $Y+10 ,15 , $Y+15 );
$pdf->Line(20, $Y+10 ,20 , $Y+15 );
$pdf->Line(25, $Y+10 ,25 , $Y+15 );
$pdf->Line(30, $Y+10 ,30 , $Y+15 );
$pdf->Line(35, $Y+10 ,35 , $Y+15 );
$pdf->Line(40, $Y+10 ,40 , $Y+15 );
$pdf->Line(45, $Y+10 ,45 , $Y+15 );
$pdf->Line(50, $Y+10 ,50 , $Y+15 );
$pdf->Line(55, $Y+10 ,55 , $Y+15 );
$pdf->Line(60, $Y+10 ,60 , $Y+15 );
$pdf->Line(65, $Y+10 ,65 , $Y+15 );
$pdf->Line(70, $Y+10 ,70 , $Y+15 );
$pdf->Line(75, $Y+10 ,75 , $Y+15 );
$pdf->Line(80, $Y+10 ,80 , $Y+15 );


$pdf->Ln(15);
$pdf->Rect(10, 100, 190, 30);

// TERCER CUADRO


$pdf->Ln(5);
$pdf->SetX(10);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190, 5, utf8_decode('DATOS DEL PROGRAMA DE CAPACITACIÓN, ADIESTRAMIENTO Y PRODUCTIVIDAD'), 1, 1, 'C',1);
$pdf->Ln();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',8);
$pdf->Cell(0,0,utf8_decode('Nombre del curso'));
$pdf->SetX(10);
// Nombre
$pdf->Cell(0,10,utf8_decode($nombrecurso));
$pdf->Ln(10);
$Y = $pdf->GetY();
$pdf->Line(10, $Y ,200 , $Y  );
$pdf->Cell(0,10,utf8_decode('Duración en horas'));
$pdf->SetX(100);
$pdf->Cell(0,10,utf8_decode('Año'));
$pdf->SetX(120);
$pdf->Cell(0,10,utf8_decode('Mes'));
$pdf->SetX(130);
$pdf->Cell(0,10,utf8_decode('Día'));
$pdf->SetX(155);
$pdf->Cell(0,10,utf8_decode('Año'));
$pdf->SetX(175);
$pdf->Cell(0,10,utf8_decode('Mes'));
$pdf->SetX(185);
$pdf->Cell(0,10,utf8_decode('Día'));
$pdf->Ln();
$pdf->Cell(0,0,utf8_decode('08'));
$pdf->Line(70, $Y ,70 , $Y+15 );
$pdf->Setx(70);
$pdf->Cell(0,-10,utf8_decode('Periodo de ejecución:'));
$pdf->SetX(92);
$pdf->Cell(0,0,utf8_decode('De:'));
$pdf->Line(100, $Y ,100 , $Y+15 );

$pdf->SetX(100);
$x=$pdf->GetX();
$x=$x-95;
// año de inicio y final

for ($i = 0; $i < strlen($fechaini); $i++) {
    $caracter = substr($fechaini, $i, 1); 
    if($caracter != "/")
    $pdf->Cell($x,5,utf8_decode($caracter));
}
$pdf->Cell($x,5,utf8_decode(''));
$pdf->Cell($x+5,5,utf8_decode('A:'));

for ($i = 0; $i < strlen($fechaf); $i++) {
    $caracter = substr($fechaf, $i, 1); 
    if($caracter != "/")
    $pdf->Cell($x,5,utf8_decode($caracter));
}

$x=100;
for($i=1; $i<=20; $i++){
    if($i==10 || $i==11){

    }else
    $pdf->Line($x, $Y+10 ,$x , $Y+15 );
    $x=$x+5;
}

$pdf->Line(120, $Y ,120 , $Y+15 );
$pdf->Line(130, $Y ,130 , $Y+15 );
$pdf->Line(140, $Y ,140 , $Y+15 );
$pdf->Line(155, $Y ,155 , $Y+15 );
$pdf->Line(175, $Y ,175 , $Y+15 );
$pdf->Line(185, $Y ,185 , $Y+15 );
$pdf->Line(195, $Y ,195 , $Y+15 );

$pdf->Ln(5);
$Y = $pdf->GetY();
$pdf->Line(10, $Y ,200 , $Y  );
$pdf->Cell(0,10,utf8_decode('Área temática del curso 2/'));
$pdf->SetX(10);
// Puesto
$pdf->Cell(0,20,utf8_decode($atematica));
$pdf->Ln(15);
$Y = $pdf->GetY();
$pdf->Line(10, $Y ,200 , $Y  );
$pdf->Cell(0,10,utf8_decode('Nombre del agente capacitador o STPS 3/'));
$pdf->Ln(5);
$pdf->Cell(0,10,utf8_decode('LUBRICANT EXPRESS DE MEXICO S.A DE C.V'));
$pdf->Rect(10, 140, 190, 60);


// firmas
$pdf->Rect(10, 205, 190, 35);
$pdf->Ln(16);
$pdf->SetFont('Arial','B',8);
$pdf->MultiCell(190, 3, utf8_decode('Los datos se asientan en esta constancia bajo protesta de decir verdad, apercibidos de la responsabilidad en que incurre todo aquel que no se conduce con verdad.'), 0, 'C',0);
$pdf->Ln();
$pdf->SetX(32);
$pdf->SetFont('Arial','',8);
$pdf->Cell(0,5,utf8_decode('Instructor o tutor'));
$pdf->SetX(85);
$pdf->Cell(0,5,utf8_decode('Patrón o representante legal 4/'));
$pdf->SetX(142);
$pdf->Cell(0,5,utf8_decode('Representante de los trabajadores 5/'));
$pdf->Ln(6);
$Y=$pdf->GetY();
$Y=$Y+15;
$pdf->Line(20, $Y ,70 , $Y);
$pdf->Line(80, $Y ,130 , $Y);
$pdf->Line(140, $Y ,190 , $Y);

$pdf->SetX(30);
$pdf->Cell(0,10,utf8_decode('Nombre del instructor'));
$pdf->SetX(90);
$pdf->Cell(0,10,utf8_decode('Eduardo Ponce'));
$pdf->SetX(140);
$pdf->Cell(50,10,utf8_decode('Nombre del representante /Quetzalli'));



$pdf->SetX(34);
$pdf->Cell(0,34,utf8_decode('Nombre y Firma'));
$pdf->SetX(95);
$pdf->Cell(0,34,utf8_decode('Nombre y Firma'));
$pdf->SetX(155);
$pdf->Cell(0,34,utf8_decode('Nombre y Firma'));
// instrucciones
$pdf->Ln(20);
$pdf->SetFont('Arial','B',8);
$pdf->MultiCell(0,5,utf8_decode('INSTRUCCIONES'));
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(0,3,utf8_decode('- Llenar a máquina o con letra de molde.'));
$pdf->MultiCell(0,3,utf8_decode('- Deberá entregarse al trabajador dentro de los veinte días hábiles siguientes al término del curso de capacitación aprobado.'));
$pdf->MultiCell(0,3,utf8_decode('1/ Las áreas y subáreas ocupacionales del Catálogo Nacional de Ocupaciones se encuentran disponibles en el reverso de este formato y en la página www.stps.gob.mx'));
$pdf->MultiCell(0,3,utf8_decode('2/ Las áreas temáticas de los cursos se encuentran disponibles en el reverso de este formato y en la página www.stps.gob.mx'));
$pdf->MultiCell(0,3,utf8_decode('3/ Cursos impartidos por el área competente de la Secretaria del Trabajo y Previsión Social.'));
$pdf->MultiCell(0,3,utf8_decode('4/ Para empresas con menos de 51 trabajadores. Para empresas con más de 50 trabajadores firmaría el representante del patrón ante la Comisión mixta de capacitación, adiestramiento y productividad.'));
$pdf->MultiCell(0,3,utf8_decode('5/ Solo para empresas con más de 50 trabajadores.'));
$pdf->MultiCell(0,3,utf8_decode('* Dato no obligatorio.'));

}
$pdf->Output();
?>