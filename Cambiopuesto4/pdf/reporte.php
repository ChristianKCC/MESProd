<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX003MXDB");
$folio = base64_decode($_GET["folio"]);

/*
QUERY PARA ECABEZADOS DEL PDF
*/
// Query para traer registros de
// Supervisor as IBM, Nombre supervisor, NombreDepto, Fecha de creacion, fecha final (Funciona agregando 6 dias extra a la fecha de inicio), numero de semana
// Solo se usa Depto (NombreDepto), Semana(numsem), Del(fecha), AL(fechaf)
$query = "SELECT 
        CambiopuestoEnc.supervisor,
        tblEmpleados.Nombre,
        tblDepartamentos.NombreDepto,
        CambiopuestoEnc.fecha,
        DATEADD(DAY,6,CambiopuestoEnc.fecha) as fechaf,
        DATEPART(ISO_WEEK, CambiopuestoEnc.fecha) as numsem
    FROM CambiopuestoEnc 
    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=CambiopuestoEnc.supervisor
    INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento 
    WHERE CambiopuestoEnc.id=$folio";
$result = sqlsrv_query($conn, $query);

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $folio = $_GET["folio"];
        $this->Image('../../img/imglogoprosede.png', 10, 5, 50);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(80, 10, "");
        $this->Cell(10, 10, utf8_decode('RELACIÓN DE CAMBIO TEMPORAL DE PUESTO'));
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(160, 10,"");
        $this->Cell(40, 5, "Folio:".base64_decode($folio));
        if(!isset($_GET["true"])){
            $this->SetTextColor(255, 0, 0);
            $this->SetFont('Arial', 'B', 16);
            $this->MultiCell(160, 10, "");
            $this->Cell(40, 5, utf8_decode("Previsualización"));
            }
        $this->Ln(10);
    }
}

// -------------------------------
// Glosario de conceptos a usar en los Tiempos Extra
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(250,50);
$pdf->MultiCell(50, 5, "GLOSARIO DE MOTIVOS:");
$pdf->SetXY(250,55);
$pdf->MultiCell(50, 5, "CAMBIO TEMP. DE PUESTO");
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(250,65);
$pdf->MultiCell(50, 5, "1. VACACIONES");
$pdf->SetXY(250,70);
$pdf->MultiCell(50, 5, "2. INCAPACIDAD");
$pdf->SetXY(250,75);
$pdf->MultiCell(50, 5, "3. AUSENTISMO");
$pdf->SetXY(250,80);
$pdf->MultiCell(50, 5,  utf8_decode("4 .VACANTE"));
$pdf->SetXY(250,85);
$pdf->MultiCell(50, 5, "5. DESCANSO / DIA FESTIVO");
$pdf->SetXY(5,20);
// -------------------------------

// Inicio de PDF
$pdf->SetFont('Arial', 'B', 7.5);
while ($row = sqlsrv_fetch_array($result)) {
    $pdf->Cell(60, 5, "");
    $pdf->Cell(15, 5, "DEPTO:");
    $pdf->Cell(40, 5, utf8_decode($row[2]));
    $pdf->Cell(15, 5, "SEMANA:");
    $pdf->Cell(20, 5, $row[5]);
    $pdf->Cell(10, 5, "DEL:");
    $pdf->Cell(30, 5, $row[3]->format("Y-m-d"));
    $pdf->Cell(10, 5, "AL:");
    $pdf->Cell(30, 5, $row[4]->format("Y-m-d"));
}
$pdf->Ln(10);

$pdf->Cell(12, 5, utf8_decode("No. Reg."), 1, 0, 'L', 0);
$pdf->Cell(10, 5, utf8_decode("Folio"), 1, 0, 'L', 0);
$pdf->Cell(11, 5, utf8_decode("NoEmp"), 1, 0, 'L', 0);
$pdf->Cell(40, 5, utf8_decode("Nombre del solicitante"), 1, 0, 'L', 0);

$pdf->Cell(5, 5, "");

$pdf->Cell(28, 5, utf8_decode("Puesto general"), 1, 0, 'L', 0);
$pdf->Cell(28, 5, utf8_decode("Puesto temporal"), 1, 0, 'L', 0);
$pdf->Cell(11, 5, utf8_decode("Motivo"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, "");

$pdf->Cell(5, 5, utf8_decode("L"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("M"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("M"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("J"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("V"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("S"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("D"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, "");

// Generación de tercera tabla
$pdf->Cell(11, 5, utf8_decode("NoEmp"), 1, 0, 'L', 0);
$pdf->Cell(40, 5, utf8_decode("Persona a cubrir"), 1, 0, 'L', 0);
$pdf->multiCell(25, 5, utf8_decode("Maquina"), 1);

$pdf->SetFont('Arial', '', 7);

/*
QUERY PARA OBTENER DATOS DE LLENADO EN LAS TRES TABLAS
*/
// INCLUYE DATOS:
// Primer tabla: No Reg. Folio, Noemp, Nombre
// Segunda tabla: Dias de la semana marcados
// Tercera tabla: Maquina, Puesto, Temporal
$query = "SELECT
            CPS.id, 
            CPS.folio,
            CPS.noemp AS NoEmp_Solicitante,
            E1.Nombre AS Nombre_Solicitante,
            D.NombreDepto AS depto,
            P.nombre AS puesto,
            CPS.lunes,
            CPS.martes,
            CPS.miercoles,
            CPS.jueves,
            CPS.viernes,
            CPS.sabado,
            CPS.domingo,
            M.NombreMaquina,
            L1.nombre AS Puesto_Regular,
            L2.nombre AS Puesto_Temporal,
            CE.noempautoriza AS NoEmp_Autoriza,
            CPS.ibmACubrir AS NoEmp_Cubrir,
            E2.Nombre AS Nombre_PersonaACubrir,
            CE.estadoter AS Estatus,
            CPS.motivos
        FROM TLX003MXDB.dbo.CambiopuestoSubEnc AS CPS
        INNER JOIN TLX032MXDB.dbo.tblEmpleados AS E1 ON E1.NoEmp = CPS.noemp
        INNER JOIN TLX009MXDB.dbo.tblPuestos AS P ON P.id = E1.Puesto
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos AS D ON D.NoDepto = E1.NombreDepartamento
        INNER JOIN TLX003MXDB.dbo.Cambiopuestolistpuestos AS L1 ON L1.id = CPS.puestoregular
        INNER JOIN TLX003MXDB.dbo.Cambiopuestolistpuestos AS L2 ON L2.id = CPS.puestotemporal
        INNER JOIN TLX009MXDB.dbo.tblMaquinas AS M ON M.NoMaquina = CPS.maquina 
        INNER JOIN TLX003MXDB.dbo.CambiopuestoEnc AS CE ON CE.id = CPS.folio
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados AS E2 ON E2.NoEmp = CPS.ibmACubrir
        WHERE CPS.folio = $folio";
$result = sqlsrv_query($conn, $query);
$autorizo = 0;
$autorizoemp = '';

while ($row = sqlsrv_fetch_array($result)) {
    $autorizo = $row['Estatus'];
    $autorizoemp = $row['NoEmp_Autoriza'];
    $pdf->Cell(12, 5, utf8_decode($row[0]), 1, 0, 'L', 0);
    $pdf->Cell(10, 5, utf8_decode($row[1]), 1, 0, 'L', 0);
    $pdf->Cell(11, 5, utf8_decode($row[2]), 1, 0, 'L', 0);
    $pdf->Cell(40, 5, utf8_decode(ucwords(strtolower($row[3]))), 1, 0, 'L', 0);
    $pdf->Cell(5, 5, "");
    
    $pdf->Cell(28, 5, utf8_decode($row[14]), 1, 0, 'L', 0);
    $pdf->Cell(28, 5, utf8_decode($row[15]), 1);
    $pdf->Cell(11, 5, utf8_decode($row[20]), 1, 0, 'L', 0);
    $pdf->Cell(5, 5, "");
    
    $pdf->Cell(5, 5, utf8_decode($row[6] == 1 ? '1' :  '0'), 1, 0, 'L', 0);
    $pdf->Cell(5, 5, utf8_decode($row[7] == 1 ? '1' :  '0'), 1, 0, 'L', 0);
    $pdf->Cell(5, 5, utf8_decode($row[8] == 1 ? '1' :  '0'), 1, 0, 'L', 0);
    $pdf->Cell(5, 5, utf8_decode($row[9] == 1 ? '1' :  '0'), 1, 0, 'L', 0);
    $pdf->Cell(5, 5, utf8_decode($row[10] == 1 ? '1' :  '0'), 1, 0, 'L', 0);
    $pdf->Cell(5, 5, utf8_decode($row[11] == 1 ? '1' :  '0'), 1, 0, 'L', 0);
    $pdf->Cell(5, 5, utf8_decode($row[12] == 1 ? '1' :  '0'), 1, 0, 'L', 0);;
    $pdf->SetFont('Arial', '', 7); 
    $pdf->Cell(5, 5, "");

    $pdf->Cell(11, 5, utf8_decode($row[17]), 1, 0, 'L', 0);
    $pdf->Cell(40, 5, utf8_decode(ucwords(strtolower($row[18]))), 1, 0, 'L', 0);
    $pdf->MultiCell(25, 5, utf8_decode($row[13]), 1);
}

$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(50, 5, "");
$pdf->Cell(60, 5, "_____________________________________");
$pdf->Cell(60, 5, "");
$x = $pdf->GetX();
$y = $pdf->GetY();
$autorizo == 1 && $pdf->Image('../../Tiempoextra/firmas/'.$autorizoemp.'.JPG', $x+20, $y-12, 15);
$pdf->MultiCell(60, 5, "_____________________________________");
$pdf->Cell(70, 5, "");
$pdf->Cell(40, 5, "Elaboro");
$pdf->Cell(82, 5, "");
$pdf->Cell(40, 5, "Autorizo");
$pdf->SetFont('Arial', 'B', 5);
$pdf->Cell(20, 5, "");
$pdf->Output();