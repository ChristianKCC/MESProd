<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX003MXDB");
$folio = base64_decode($_GET["folio"]);

// Detección de duplicados de cobertura en la misma semana
$noSemana = null;
$anioSem = null;
$rWk = sqlsrv_query($conn, "SELECT noSemana, YEAR(fecha) AS anio FROM CambiopuestoEnc WHERE id = ?", [$folio]);
if ($rWk && ($wk = sqlsrv_fetch_array($rWk, SQLSRV_FETCH_ASSOC))) {
    $noSemana = $wk['noSemana'];
    $anioSem = $wk['anio'];
}

$diasDupPorReg = [];   // idReg => [indicesDia duplicados]
$conflictosPorReg = [];   // idReg => [ {folio, sup, dias, folioOrig, supOrig, motivo} ]

if ($noSemana !== null) {
    $qWeek = "SELECT CPS.id, CPS.folio, CPS.noemp, CPS.ibmACubrir, CPS.maquina, CPS.puestotemporal,
                     CPS.porcionTurno, CPS.lunes,CPS.martes,CPS.miercoles,CPS.jueves,CPS.viernes,CPS.sabado,CPS.domingo,
                     CPS.esExcepcion, CPS.motivoExcepcion,
                     CE.supervisor, sup.Nombre AS supNombre, CE.fechacreacion
              FROM TLX003MXDB.dbo.CambiopuestoSubEnc CPS
              INNER JOIN TLX003MXDB.dbo.CambiopuestoEnc CE ON CE.id = CPS.folio
              INNER JOIN TLX032MXDB.dbo.tblEmpleados sup ON sup.NoEmp = CE.supervisor
              WHERE CE.noSemana = ? AND YEAR(CE.fecha) = ?";
    $rW = sqlsrv_query($conn, $qWeek, [$noSemana, $anioSem]);
    $week = [];
    if ($rW !== false)
        while ($w = sqlsrv_fetch_array($rW, SQLSRV_FETCH_ASSOC))
            $week[] = $w;

    $dayCols = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    $nombresDia = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    $solapa = function ($a, $b) {
        return ($a === 'completo' || $b === 'completo') ? true : ($a === $b);
    };
    $slotDe = function ($r) {
        $ibm = trim((string) $r['ibmACubrir']);
        return $ibm === '' ? ('V|' . $r['maquina'] . '|' . $r['puestotemporal']) : ('P|' . $ibm);
    };
    $keyOrden = function ($r) {
        $f = ($r['fechacreacion'] instanceof DateTime) ? $r['fechacreacion']->format('Y-m-d H:i:s') : '9999-99-99 99:99:59';
        return $f . '|' . str_pad((string) $r['folio'], 8, '0', STR_PAD_LEFT);
    };

    foreach ($week as $a) {
        if ((string) $a['folio'] !== (string) $folio)
            continue;   // solo los registros de ESTE folio
        foreach ($week as $b) {
            if ($a['id'] == $b['id'])
                continue;
            if ($slotDe($a) !== $slotDe($b))
                continue;           // mismo cubierto (persona o vacante)

            $comunes = [];
            for ($d = 0; $d < 7; $d++)
                if ($a[$dayCols[$d]] == 1 && $b[$dayCols[$d]] == 1 && $solapa($a['porcionTurno'], $b['porcionTurno']))
                    $comunes[] = $d;
            if (empty($comunes))
                continue;

            foreach ($comunes as $d)
                $diasDupPorReg[$a['id']][$d] = true;

            $orig = ($keyOrden($a) <= $keyOrden($b)) ? $a : $b;
            $motivo = ($a['esExcepcion'] == 1 && trim((string) $a['motivoExcepcion']) !== '') ? $a['motivoExcepcion']
                : (($b['esExcepcion'] == 1) ? $b['motivoExcepcion'] : '');

            $nombresComunes = [];
            foreach ($comunes as $i)
                $nombresComunes[] = $nombresDia[$i];

            $conflictosPorReg[$a['id']][] = [
                'folio' => $b['folio'],
                'sup' => ucwords(strtolower($b['supNombre'])),
                'dias' => implode(', ', $nombresComunes),
                'folioOrig' => $orig['folio'],
                'supOrig' => ucwords(strtolower($orig['supNombre'])),
                'motivo' => trim((string) $motivo)
            ];
        }
    }
    foreach ($diasDupPorReg as $k => $set)
        $diasDupPorReg[$k] = array_keys($set);
}

/*
QUERY PARA ECABEZADOS DEL PDF
*/
$query = "SELECT 
        CambiopuestoEnc.supervisor,        
        TL2.Nombre AS NombreGerente,        
        tblDepartamentos.NombreDepto,
        CambiopuestoEnc.fecha,
        DATEADD(DAY,6,CambiopuestoEnc.fecha) as fechaf,
        DATEPART(ISO_WEEK, CambiopuestoEnc.fecha) as numsem,
        TL1.Nombre AS NombreSupervisor,
        CambiopuestoEnc.noempautoriza
    FROM CambiopuestoEnc 
    INNER JOIN TLX032MXDB.dbo.tblEmpleados TL1 ON TL1.NoEmp=CambiopuestoEnc.supervisor
    INNER JOIN TLX032MXDB.dbo.tblEmpleados TL2 ON TL2.NoEmp=CambiopuestoEnc.noempautoriza
    INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto= TL1.NombreDepartamento 
    WHERE CambiopuestoEnc.id=$folio";

$result = sqlsrv_query($conn, $query);
$nombreSupervisor = "";
$noempSupervisor = "";

$nombreGerente = "";
$noempGerente = "";

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
        $this->Cell(160, 10, "");
        $this->Cell(40, 5, "Folio:" . base64_decode($folio));
        if (!isset($_GET["true"])) {
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
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(250, 100);
$pdf->MultiCell(50, 5, "GLOSARIO DE MOTIVOS:");
$pdf->SetXY(250, 105);
$pdf->MultiCell(50, 5, "CAMBIO TEMP. DE PUESTO");
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(250, 110);
$pdf->MultiCell(50, 5, "1. VACACIONES");
$pdf->SetXY(250, 115);
$pdf->MultiCell(50, 5, "2. INCAPACIDAD");
$pdf->SetXY(250, 120);
$pdf->MultiCell(50, 5, "3. AUSENTISMO");
$pdf->SetXY(250, 125);
$pdf->MultiCell(50, 5, utf8_decode("4 .VACANTE"));
$pdf->SetXY(250, 130);
$pdf->MultiCell(50, 5, "5. DESCANSO / DIA FESTIVO");
$pdf->SetXY(5, 20);
// -------------------------------

// Inicio de PDF
$pdf->SetFont('Arial', 'B', 7.5);
while ($row = sqlsrv_fetch_array($result)) {
    $nombreSupervisor = $row['NombreSupervisor'];
    $noempSupervisor = $row["supervisor"];
    $nombreGerente = $row["NombreGerente"];
    $pdf->Cell(60, 5, "");
    $pdf->Cell(15, 5, "DEPTO:");
    $pdf->Cell(40, 5, utf8_decode($row['NombreDepto']));
    $pdf->Cell(15, 5, "SEMANA:");
    $pdf->Cell(20, 5, $row['numsem']);
    $pdf->Cell(10, 5, "DEL:");
    $pdf->Cell(30, 5, $row['fecha']->format("Y-m-d"));
    $pdf->Cell(10, 5, "AL:");
    $pdf->Cell(30, 5, $row['fechaf']->format("Y-m-d"));
}
$pdf->Ln(10);

$pdf->Cell(12, 5, utf8_decode("No. Reg."), 1, 0, 'L', 0);
$pdf->Cell(10, 5, utf8_decode("Folio"), 1, 0, 'L', 0);
$pdf->Cell(11, 5, utf8_decode("NoEmp"), 1, 0, 'L', 0);

$pdf->Cell(32, 5, utf8_decode("Nombre del solicitante"), 1, 0, 'L', 0);

$pdf->Cell(5, 5, "");

$pdf->Cell(28, 5, utf8_decode("Puesto general"), 1, 0, 'L', 0);
$pdf->Cell(28, 5, utf8_decode("Puesto temporal"), 1, 0, 'L', 0);

$pdf->Cell(11, 5, utf8_decode("Motivo"), 1, 0, 'L', 0);
$pdf->Cell(16, 5, utf8_decode("Jornada"), 1, 0, 'L', 0);
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

$pdf->Cell(32, 5, utf8_decode("Persona a cubrir"), 1, 0, 'L', 0);
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
            CPS.motivos,
            CPS.porcionTurno,
            CPS.esExcepcion,
            CPS.motivoExcepcion
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
$notasExc = [];

while ($row = sqlsrv_fetch_array($result)) {
    $autorizo = $row['Estatus'];
    $autorizoemp = $row['NoEmp_Autoriza'];
    $pdf->Cell(12, 5, utf8_decode($row[0]), 1, 0, 'L', 0);
    $pdf->Cell(10, 5, utf8_decode($row[1]), 1, 0, 'L', 0);
    $pdf->Cell(11, 5, utf8_decode($row[2]), 1, 0, 'L', 0);

    $pdf->Cell(32, 5, utf8_decode(ucwords(strtolower($row[3]))), 1, 0, 'L', 0);
    $pdf->Cell(5, 5, "");

    $pdf->Cell(28, 5, utf8_decode($row[14]), 1, 0, 'L', 0);
    $pdf->Cell(28, 5, utf8_decode($row[15]), 1);

    $pdf->Cell(11, 5, utf8_decode($row[20]), 1, 0, 'L', 0);
    $labelP = ['completo' => 'Completo', 'primera_mitad' => '1a mitad', 'segunda_mitad' => '2a mitad'][$row['porcionTurno']] ?? 'Completo';
    if ($row['esExcepcion'] == 1) {
        $labelP .= ' *';
        $notasExc[$row[0]] = $row['motivoExcepcion'];
    }
    $pdf->Cell(16, 5, utf8_decode($labelP), 1, 0, 'L', 0);   // Integra el tipo de jornada

    $pdf->Cell(5, 5, "");

    $dupSet = $diasDupPorReg[$row[0]] ?? [];
    $diasVals = [$row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12]];
    for ($d = 0; $d < 7; $d++) {
        if (in_array($d, $dupSet))
            $pdf->SetTextColor(255, 0, 0);
        else
            $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(5, 5, ($diasVals[$d] == 1 ? '1' : '0'), 1, 0, 'L', 0);
    }
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(5, 5, "");

    $pdf->Cell(11, 5, utf8_decode($row[17]), 1, 0, 'L', 0);

    $nombreCubrir = trim((string) $row[18]) === '' ? 'VACANTE' : ucwords(strtolower($row[18]));
    $pdf->Cell(32, 5, utf8_decode($nombreCubrir), 1, 0, 'L', 0);
    $pdf->MultiCell(25, 5, utf8_decode($row[13]), 1);
}

// Imprime las notas de excepción
if (!empty($notasExc)) {
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 6.5);
    $pdf->Cell(0, 4, utf8_decode("* Coberturas por excepción (duplicado autorizado por motivo):"), 0, 1);
    $pdf->SetFont('Arial', '', 6.5);
    foreach ($notasExc as $reg => $mot) {
        $pdf->Cell(0, 4, utf8_decode("   Reg. " . $reg . ": " . $mot), 0, 1);
    }
}

if (!empty($conflictosPorReg)) {
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 6.5);
    $pdf->Cell(0, 4, utf8_decode("COINCIDENCIAS DE COBERTURA EN LA MISMA SEMANA (duplicados):"), 0, 1);
    $pdf->SetFont('Arial', '', 6.5);
    foreach ($conflictosPorReg as $reg => $confs) {
        foreach ($confs as $c) {
            $txt1 = "Reg. $reg - tambien registrado en Folio {$c['folio']} por supervisor {$c['sup']} los dias: {$c['dias']}. ";

            $txtBold = "1er. folio en ser creado";

            $txt2 = ": Folio {$c['folioOrig']} por supervisor {$c['supOrig']}."
                . ($c['motivo'] !== '' ? "  Motivo del duplicado: {$c['motivo']}" : "");

            $pdf->SetFont('Arial', '', 6.5);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Write(4, utf8_decode($txt1));

            $pdf->SetFont('Arial', 'B', 6.5);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Write(4, utf8_decode($txtBold));

            $pdf->SetFont('Arial', '', 6.5);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Write(4, utf8_decode($txt2));

            $pdf->Ln(4);
        }
    }
}

$pdf->Ln(20);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(50, 5, "");
$pdf->Cell(60, 5, "_____________________________________");
$pdf->Cell(60, 5, "");
$x = $pdf->GetX();
$y = $pdf->GetY();

if ($autorizo == 1) {
    $extensiones = ['png', 'jpg', 'jpeg'];
    $ruta_firma = null;

    // Buscar primero en Tiempoextra/firmas/
    foreach ($extensiones as $ext) {
        $ruta = "../../Tiempoextra/firmas/" . $autorizoemp . "." . $ext;
        if (file_exists($ruta)) {
            $ruta_firma = $ruta;
            $size = 15;
            break;
        }
    }

    // Si no se encontró, buscar en FirmaDigital/firmas/
    if (!$ruta_firma) {
        foreach ($extensiones as $ext) {
            $ruta = "../../FirmaDigital/firmas/" . $autorizoemp . "." . $ext;
            if (file_exists($ruta)) {
                $ruta_firma = $ruta;
                $size = 35;
                break;
            }
        }
    }

    // Insertar la firma en el PDF si se encontró
    if ($ruta_firma) {
        $pdf->Image($ruta_firma, $x + 15, $y - 10, $size);
    }
}


if ($noempSupervisor) {
    $extensiones = ['png', 'jpg', 'jpeg'];
    $ruta_firma = null;

    // Buscar primero en Tiempoextra/firmas/
    foreach ($extensiones as $ext) {
        $ruta = "../../Tiempoextra/firmas/" . $noempSupervisor . "." . $ext;
        if (file_exists($ruta)) {
            $ruta_firma = $ruta;
            $size = 15;
            break;
        }
    }

    // Si no se encontró, buscar en FirmaDigital/firmas/
    if (!$ruta_firma) {
        foreach ($extensiones as $ext) {
            $ruta = "../../FirmaDigital/firmas/" . $noempSupervisor . "." . $ext;
            if (file_exists($ruta)) {
                $ruta_firma = $ruta;
                $size = 35;
                break;
            }
        }
    }

    // Insertar la firma en el PDF si se encontró
    if ($ruta_firma) {
        $pdf->Image($ruta_firma, $x - 110, $y - 10, $size);
    }
}

$pdf->Text($x - 108, $y + 3, ucwords(strtolower(utf8_decode($nombreSupervisor))));
$pdf->Text($x + 10, $y + 3, ucwords(strtolower(utf8_decode($nombreGerente))));

$pdf->MultiCell(60, 5, "_____________________________________");
$pdf->Cell(70, 5, "");
$pdf->Cell(40, 5, "Elaboro");
$pdf->Cell(82, 5, "");
$pdf->Cell(40, 5, "Autorizo");
$pdf->SetFont('Arial', 'B', 5);
$pdf->Cell(20, 5, "");
$pdf->Output();