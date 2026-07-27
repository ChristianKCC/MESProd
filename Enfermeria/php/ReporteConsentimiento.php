<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");


function nombreMes($numMes){
    $numMes == 1 && $numMes = "Enero";
    $numMes == 2 && $numMes = "Febrero";
    $numMes == 3 && $numMes = "Marzo";
    $numMes == 4 && $numMes = "Abril";
    $numMes == 5 && $numMes = "Mayo";
    $numMes == 6 && $numMes = "Junio";
    $numMes == 7 && $numMes = "Julio";
    $numMes == 8 && $numMes = "Agosto";
    $numMes == 9 && $numMes = "Septiembre";
    $numMes == 10 && $numMes = "Octubre";
    $numMes == 11 && $numMes = "Noviembre";
    $numMes == 12 && $numMes = "Diciembre";

    return $numMes; 
  }

// Recibir datos POST
$noemp = $_POST['noemp'] ?? 'Sin número';
$fechaNac = $_POST['fechaNac'] ?? 'Sin fecha';

$firmaBase64 = $_POST['firma'];
$base64Data = explode(',', $firmaBase64)[1];
// Crear archivo temporal
$imagenPath = tempnam(sys_get_temp_dir(), 'firma_') . '.png';
file_put_contents($imagenPath, base64_decode($base64Data));

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX032MXDB");

$query = "SELECT tbe.Nombre, tbe.Domicilio, getDate() AS fechaActual,
          MONTH(GETDATE()) AS mes,
          DAY(GETDATE()) AS dia,
          YEAR(GETDATE()) AS anio
          FROM tblEmpleados tbe
          WHERE tbe.NoEmp = $noemp";
$result = sqlsrv_query($conn, $query);
$array = array();
while ($row = sqlsrv_fetch_array($result)) {
    array_push($array, [
       "Nombre"=> $row["Nombre"],
       "Domicilio"=> $row["Domicilio"],
       "fechaActual" => $row['fechaActual'] -> format('d-m-Y'),
       "dia" => $row["dia"],
       "mes"=> nombreMes($row["mes"]),
       "anio"=> $row["anio"],
    ]);
}


class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 10, 10, 60);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(80, 10, "");
        $this->Ln();
        $this->SetLeftMargin(27);
        $this->SetRightMargin(25);
        $this->Cell(100, 20, 'CONSENTIMIENTO INFORMADO PARA LA EVALUACION MEDICA');
        $this->Ln();
    }
}
$array = $array[0];
$pdf = new PDF();
$pdf->AddPage(); 
$pdf->SetFont('Arial', '', 10); //Tamaño y fuente de la letra
$pdf->SetXY(27, 35); //define la pocision de X y Y
$pdf->Cell(40, 5, "Por medio de la presente el que suscribe: ".mb_convert_encoding($array['Nombre'], 'ISO-8859-1','UTF-8')); 
$pdf->Ln();
$pdf->Cell(40, 5, "Fecha de Nacimiento: $fechaNac");
$pdf->Ln();
$pdf->Cell(40, 5, "Con domicilio en: ");
$pdf->SetXY(55,46);
$pdf->MultiCell(130,3,mb_convert_encoding($array['Domicilio'], 'ISO-8859-1','UTF-8'), 0, 'J');
$pdf->Ln();
$endY = $pdf->GetY();
$pdf->SetY($endY-3);
$pdf->Cell(40, 5, "Manifiesto que siendo evaluado y/o bajo reconocimiento medico, para la empresa." );
$pdf->Ln();
$pdf->SetY(57);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 5, "KIMBERLY-CLARK DE MEXICO, S.A.B. DE C.V.: ");
$pdf->Ln();
$pdf->Cell(40, 5, "Autorizo la evaluacion medica que incluya los siguientes procedimientos: ");
$pdf->Ln();
$pdf->SetFont('Arial', '', 10);
$pdf->SetY(70);
$pdf->SetRightMargin(25);
$pdf->MultiCell(w: 160, h: 5, txt: "Llenado y actualizacion cuando sea necesario de historia clinica medica en el cual se que contiene informacion que proporcionare bajo protesta de decir la verdad incluyendo los datos que se me soliciten para el llenado de: ficha deidentidad, antecedentes heredofamiliares, antecedentes personales no patologicos, antecedentes personales patologicos, antecedentes laborales de indole medica y de interes para el proceso de contratacion o procesos posteriores de actualizacion anual, cuando aplique, asi como la valoracion fisica completa y pruebas fisicas que el personal medico juzgue conveniente. Asimismo, los resultados obtenidos seran empleados de forma no discriminatoria ni en perjuicio del candidato o del trabajador. Por tal motivo, se hace de su conocimiento que, como parte del examen medico puede incluirse los siguientes estudios:");
$pdf->Ln();
$pdf->SetY(117);
$pdf->SetLeftMargin(40);
$pdf->SetFont('Arial','B', 10);
$pdf->MultiCell(147, 5, "a)   Examenes sanguineos:");
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(147, 5, 'Para los cuales dare voluntariamente una muestra sanguinea de acuerdo con las condiciones e informacion que se me proporcione previamente, pudiendo ser estos sin llegar a limitarlos pudiendo ser ampliados en cualquier momento: Biometria hematica, quimica sanguinea, pruebas de funcion hepatica, tasa de filtrado glomerular, grupo y Rh.');
$pdf->Ln();
$pdf->SetY(144);
$pdf->SetFont('Arial','B', 10);
$pdf->MultiCell(147, 5, "b)   Audiometria y Espirometria:");
$pdf->SetFont('Arial','', 10);
$pdf->MultiCell(147, 5, "Para los cuales me presentare voluntariamente en el sitio que me indiquen y a la hora indicada de acuerdo con las condiciones e informacion que se me proporcione previamente.");
$pdf->Ln();
$pdf->SetXY(15, 161);
$pdf->SetLeftMargin(27);
$pdf->MultiCell(160, 5, "Las pruebas antes mencionadas se realizan en beneficio del candidato o trabajador y no interfieren en la toma de la decision para la contratacion y/o permanencia en el empleo. La informacion obtenida de los examenes medicos unicamente sera del conocimiento del personal medico y/o enfermeros de la empresa, los cuales cuentan con la competencia tecnica, capacidad y formacion adecuada.");
$pdf->Ln();
$pdf->SetY(187);
$pdf->MultiCell(160, 5, "Por otra parte, se le comunica al candidato(a) a trabajador(a), que es su deber notificar en tiempo y forma, cualquier informacion importante sobre su condicion fisica o estado de salud, asi como colaborar en su obtencion, especialmente cuando sea de interes o afecte la marcha normal de las actividades inherentes a su puesto de trabajo.");
$pdf->Ln();
$pdf->SetY(209);
$pdf->MultiCell(160, 5, "Por lo que, acepto que se me proporciono informacion de la evaluacion medica de admision, de los riesgos y beneficios de la realizacion de los procedimientos medicos y de los estudios clinicos y de gabinete, de los procesos anuales, de los examenes especiales y de las revisiones medicas periodicas, he leido y comprendido y no tengo ninguna duda,firmo voluntariamente y doy mi consentimiento para las evaluaciones y procedimientos medicos aqui mencionados en los eventos que fueran solicitados y durante el tiempo que dure la relacion laboral.");
$pdf->Ln();
$pdf->Cell(40, 5, "_________________________________________");

// Firma
$pdf->Image($imagenPath, 50, 230, 40, 25);
$pdf->SetXY(116,239);
$pdf->MultiCell(75,5, mb_convert_encoding("Cuautitlán Izcalli, Edo. Mex, a ".$array['dia']." de ".$array['mes']." del ".$array['anio'], 'ISO-8859-1','UTF-8'), 0, 'C');
$endY = $pdf->GetY();
$pdf->SetXY(115, $endY-5);
$pdf->Cell(40, 5, "_____________________________________");
$pdf->SetXY(30,245);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 12, "Firmo y autorizo el presente consentimiento");
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(140,245);
$pdf->Cell(40, 12, "Lugar y fecha");
$pdf->SetXY(20, 257);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 5, "FORM-56674");
$pdf->Cell(30, 5, "Revision: 0");
$pdf->MultiCell(65, 5, "CONSENTIMIENTO INFORMADO PARA LA EVALUACION MEDICA");
$pdf->SetXY(160, 257);
$pdf->Cell(30, 5, "Fecha Efectiva:");
$pdf->SetXY(20, 267);
$pdf->Cell(30, 5, "ESTE DOCUMENTO CONTIENE INFORMACION CONFIDENCIAL DE KIMBERLY-CLARK");
$pdf->SetXY(20, 271);
$pdf->Cell(30, 5, "Page 1 of 1");
$pdf->Output();