<?php
// Generacion de PDF de Tiempos Extra por FILTROS (multi-folio)
// Una hoja por folio, incluyendo solo los empleados que cumplen los filtros.
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX003MXDB");

/* =========================================================
   1. LECTURA Y SANEO DE FILTROS
   ========================================================= */
$fechaInicio  = isset($_GET['fechaInicio'])  ? trim($_GET['fechaInicio'])  : '';
$fechaFin     = isset($_GET['fechaFin'])     ? trim($_GET['fechaFin'])     : '';
$departamento = isset($_GET['departamento']) ? trim($_GET['departamento']) : '';
$tipoEmpleado = isset($_GET['tipoEmpleado']) ? trim($_GET['tipoEmpleado']) : '';
$ibm          = isset($_GET['ibm'])          ? trim($_GET['ibm'])          : '';
$estado       = isset($_GET['tipoAprobado']) ? trim($_GET['tipoAprobado']) : '';
$esPreview    = !isset($_GET["true"]);

// Valida que una fecha venga en formato YYYY-MM-DD; si no, regresa null
function validarFecha($f) {
    if ($f === '') return null;
    $d = DateTime::createFromFormat('Y-m-d', $f);
    return ($d && $d->format('Y-m-d') === $f) ? $f : null;
}
$fechaInicio = validarFecha($fechaInicio);
$fechaFin    = validarFecha($fechaFin);

/* =========================================================
   2. CONSTRUCCION DE CONDICIONES
   ---------------------------------------------------------
   $condEmp   -> filtros a nivel EMPLEADO. Se usan tanto para
                 listar los folios como para listar empleados
                 dentro de cada folio (asi se excluye a los que
                 no aplican).
   $condFecha -> rango de fechas; define que folios entran.   
   ========================================================= */
$condEmp = "";
if ($ibm !== '' && ctype_digit($ibm)) {
    $condEmp .= " AND TiempoextraSubEnc.noemp = " . intval($ibm);
}
// EmpleadoSindicalizado: 0 = Sindicalizado, 1 = Empleado
if ($tipoEmpleado === '0' || $tipoEmpleado === '1') {
    $condEmp .= " AND tblEmpleados.EmpleadoSindicalizado = " . intval($tipoEmpleado);
}
if ($departamento !== '' && ctype_digit($departamento)) {
    $condEmp .= " AND tblEmpleados.NombreDepartamento = " . intval($departamento);
}

$condestado = '';
if ($estado === '0') {
    $condestado .= " AND TiempoextraSubEnc.validado IS NULL";
} elseif ($estado !== '' && ctype_digit($estado)) {
    $condestado .= " AND TiempoextraSubEnc.validado = " . intval($estado);
}


$condFecha = "";
if ($fechaInicio && $fechaFin) {
    $condFecha = " AND TiempoextraSubEnc.fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
} elseif ($fechaInicio) {
    $condFecha = " AND TiempoextraSubEnc.fecha >= '$fechaInicio'";
} elseif ($fechaFin) {
    $condFecha = " AND TiempoextraSubEnc.fecha <= '$fechaFin'";
}

// Guarda: exige al menos un filtro para no traer toda la base
$hayFiltro = ($condFecha !== "" || $condEmp !== "" || $condestado !== "");

/* =========================================================
   3. OBTENER FOLIOS UNICOS QUE CUMPLEN LOS FILTROS
   ========================================================= */
$folios = [];
if ($hayFiltro) {
    $sqlFolios = "SELECT DISTINCT TiempoextraSubEnc.folio
    FROM TiempoextraSubEnc
    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraSubEnc.noemp
    WHERE 1=1 $condFecha $condEmp $condestado
    ORDER BY TiempoextraSubEnc.folio";
    $resFolios = sqlsrv_query($conn, $sqlFolios);
    if ($resFolios) {
        while ($r = sqlsrv_fetch_array($resFolios)) {
            $folios[] = $r['folio'];
        }
    }
}

/* =========================================================
   4. CLASE PDF
   ========================================================= */
class PDF extends FPDF
{
    public $folioActual = '';
    public $esPreview   = true;

    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 10, 5, 50);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(80, 10, "");
        $this->Cell(10, 10, utf8_decode('RELACION DE TIEMPO EXTRA Y ADICIONAL'));
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(160, 10, "");
        $this->Cell(40, 5, "Folio:" . $this->folioActual);
        if ($this->esPreview) {
            $this->SetTextColor(255, 0, 0);
            $this->SetFont('Arial', 'B', 16);
            $this->MultiCell(160, 10, "");
            $this->Cell(40, 5, utf8_decode("Previsualización"));
            $this->SetTextColor(0, 0, 0);
        }
        $this->Ln(10);
    }
}

/* =========================================================
   5. FUNCION QUE DIBUJA UNA HOJA (UN FOLIO)
   ========================================================= */
function renderFolio($pdf, $conn, $folio, $condEmp)
{
    // ----- Glosario de conceptos -----
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(255,45);  $pdf->MultiCell(50, 5, "CONCEPTOS:");
    $pdf->SetXY(255,50);  $pdf->MultiCell(50, 5, "TIEMPO EXTRA");
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(255,60);  $pdf->MultiCell(50, 5, "1. VACACIONES");
    $pdf->SetXY(255,65);  $pdf->MultiCell(50, 5, "2. INCAPACIDADES");
    $pdf->SetXY(255,70);  $pdf->MultiCell(50, 5, "3. ARRANQUE DE MAQUINA");
    $pdf->SetXY(255,75);  $pdf->MultiCell(50, 5, utf8_decode("4 .CAPACITACIÓN"));
    $pdf->SetXY(255,80);  $pdf->MultiCell(50, 5, "5. PERMISO");
    $pdf->SetXY(255,85);  $pdf->MultiCell(50, 5, "6. JUNTAS");
    $pdf->SetXY(255,90);  $pdf->MultiCell(50, 5, "7. OTRO");
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(255,100); $pdf->MultiCell(50, 5, "CONCEPTOS:");
    $pdf->SetXY(255,105); $pdf->MultiCell(50, 5, "TIEMPO ADICIONAL");
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(255,115); $pdf->MultiCell(50, 5, utf8_decode("8. REDUCCIÓN DE HORARIO"));
    $pdf->SetXY(255,120); $pdf->MultiCell(50, 5, "9. TIEMPO DE COMIDA");
    $pdf->SetXY(255,125); $pdf->MultiCell(50, 5, "10. DESCANSO TRABAJADO");
    $pdf->SetXY(255,130); $pdf->MultiCell(50, 5, "11. EXTEMPORANEO");
    $pdf->SetXY(255,135); $pdf->MultiCell(50, 5, "12. DIA FESTIVO");
    $pdf->SetXY(5,20);

    // ----- Encabezado del folio (depto, semana, fechas, firmas) -----
    // LEFT JOIN en 'aut' para que la hoja se dibuje aunque el folio aun no este autorizado.
    $query = "SELECT
        TiempoextraEnc.supervisor,
        sup.Nombre AS NombreSupervisor,
        dep.NombreDepto,
        TiempoextraEnc.fecha,
        DATEADD(DAY,6,TiempoextraEnc.fecha) AS fechaf,
        DATEPART(wk, TiempoextraEnc.fecha) AS numsem,
        TiempoextraEnc.noempautoriza,
        aut.Nombre AS NombreAutoriza,
        TiempoextraEnc.autorizado
    FROM [TLX003MXDB].[dbo].TiempoextraEnc
    INNER JOIN TLX032MXDB.dbo.tblEmpleados AS sup ON sup.NoEmp = TiempoextraEnc.supervisor
    LEFT  JOIN TLX032MXDB.dbo.tblEmpleados AS aut ON aut.NoEmp = TiempoextraEnc.noempautoriza
    INNER JOIN TLX009MXDB.dbo.tblDepartamentos AS dep ON dep.NoDepto = TiempoextraEnc.departamento
    WHERE TiempoextraEnc.id = $folio";
    $result = sqlsrv_query($conn, $query);

    $autorizo = 0; $autorizoemp = ''; $elaboronoemp = ''; $nombreSup = ''; $nombreAut = '';

    while ($row = sqlsrv_fetch_array($result)) {
        $autorizo     = $row['autorizado'];
        $autorizoemp  = $row['noempautoriza'];
        $elaboronoemp = $row['supervisor'];
        $nombreSup    = $row['NombreSupervisor'];
        $nombreAut    = $row['NombreAutoriza'];
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

    // ----- Cabecera de la tabla -----
    $pdf->Cell(10, 10, utf8_decode("NoEmp"), 1, 0, 'L', 0);
    $pdf->Cell(40, 10, utf8_decode("Nombre"), 1, 0, 'L', 0);
    $pdf->Cell(40, 10, utf8_decode("Puesto"), 1, 0, 'L', 0);
    $pdf->Cell(20, 10, utf8_decode("Maquina"), 1, 0, 'L', 0);
    $pdf->Cell(15, 5, utf8_decode("LUN"), 1, 0, 'L', 0);
    $pdf->Cell(15, 5, utf8_decode("MAR"), 1, 0, 'L', 0);
    $pdf->Cell(15, 5, utf8_decode("MIE"), 1, 0, 'L', 0);
    $pdf->Cell(15, 5, utf8_decode("JUE"), 1, 0, 'L', 0);
    $pdf->Cell(15, 5, utf8_decode("VIE"), 1, 0, 'L', 0);
    $pdf->Cell(15, 5, utf8_decode("SÁB"), 1, 0, 'L', 0);
    $pdf->Cell(15, 5, utf8_decode("DOM"), 1, 0, 'L', 0);
    $pdf->Cell(20, 5, utf8_decode("TOTAL"), 1,0,'L',0);
    $pdf->SetXY(225,35);
    $pdf->Cell(20, 5, utf8_decode("HORAS"), 1,0,'L',0);

    $total2Horas = 0; $total2Minutos = 0;

    $pdf->SetXY(120,35);
    for ($i=0; $i<6; $i++) {
        $pdf->Cell(10, 5, utf8_decode("HRS"), 1, 0, 'L', 0);
        $pdf->Cell(5, 5, utf8_decode("C"), 1, 0, 'L', 0);
    }
    $pdf->Cell(10, 5, utf8_decode("HRS"), 1, 0, 'L', 0);
    $pdf->MultiCell(5, 5, utf8_decode("C"), 1);

    // ----- Empleados del folio (CON FILTROS aplicados via $condEmp) -----
    $queryal = "SELECT
        TiempoextraSubEnc.noemp,
        MAX(tblEmpleados.Nombre) as Nombre,
        MAX(tblDepartamentos.NombreDepto) as depto,
        MAX(tblPuestos.nombre) as puesto,
        MAX(tblMaquinas.NombreMaquina) as NombreMaquina,
        TiempoextraSubEnc.maquina,
        (SELECT TOP 1 t2.turnoAsignado
           FROM TiempoextraSubEnc t2
          WHERE t2.noemp   = TiempoextraSubEnc.noemp
            AND t2.folio   = $folio
            AND t2.maquina = TiempoextraSubEnc.maquina
          GROUP BY t2.turnoAsignado
          ORDER BY COUNT(*) DESC) as turnoAsignado
    FROM TiempoextraSubEnc
    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraSubEnc.noemp
    INNER JOIN TLX009MXDB.dbo.tblPuestos On tblPuestos.id = tblEmpleados.Puesto
    INNER JOIN TLX009MXDB.dbo.tblDepartamentos On tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
    INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = TiempoextraSubEnc.maquina
    WHERE TiempoextraSubEnc.folio = $folio $condEmp
    GROUP BY TiempoextraSubEnc.noemp, TiempoextraSubEnc.maquina";
    $resultal = sqlsrv_query($conn, $queryal);

    $horasx = 120;
    $empleadosProcessados = [];

    while ($rowal = sqlsrv_fetch_array($resultal)) {
        $pdf->SetFont('Arial', '', 6);

        $noemp   = $rowal['noemp'];
        $maquina = $rowal['maquina'];
        $key     = $noemp . '_' . $maquina;

        // Fila principal con datos del empleado
        $pdf->Cell(10, 5, utf8_decode($rowal['noemp']), 1, 0, 'L', 0);
        $pdf->Cell(40, 5, ucwords(strtolower(utf8_decode($rowal['Nombre']))), 1, 0, 'L', 0);
        $pdf->Cell(40, 5, utf8_decode($rowal['puesto']), 1, 0, 'L', 0);
        $pdf->Cell(20, 5, utf8_decode($rowal['NombreMaquina']), 1, 0, 'L', 0);
        for ($i=0; $i<7; $i++) {
            $pdf->Cell(10, 5, " ", 1, 0, 'L', 0);
            $pdf->Cell(5, 5, " ", 1, 0, 'L', 0);
        }
        $horasy = $pdf->GetY();

        // Detalle de horas por dia para este empleado/maquina/folio
        $query = "SELECT DATEPART(WEEKDAY,TiempoextraSubEnc.fecha) as dia,
                         DATEDIFF(MINUTE, TiempoextraSubEnc.horai,TiempoextraSubEnc.horaf) as dif,
                         TiempoextraSubEnc.motivo as motivo
                  FROM TiempoextraSubEnc
                  WHERE TiempoextraSubEnc.noemp={$rowal['noemp']}
                    AND TiempoextraSubEnc.folio=$folio
                    AND TiempoextraSubEnc.maquina={$rowal['maquina']}
                  ORDER BY TiempoextraSubEnc.fecha";
        $resultDet = sqlsrv_query($conn, $query);

        // Horas reglamentarias según turno
        switch($rowal['turnoAsignado']) {
            case 'turno1': $horasReglamentarias = 48; $NombreTurno = "Turno 1"; break;
            case 'turno2': $horasReglamentarias = 45; $NombreTurno = "Turno 2"; break;
            case 'turno3': $horasReglamentarias = 51; $NombreTurno = "Turno 3"; break;
            case 'mixto1': $horasReglamentarias = 48; $NombreTurno = "Mixto 1"; break;
            case 'mixto2': $horasReglamentarias = 48; $NombreTurno = "Mixto 2"; break;
            case 'mixto3': $horasReglamentarias = 48; $NombreTurno = "Mixto 3"; break;
            case 'mixto4': $horasReglamentarias = 48; $NombreTurno = "Mixto 4"; break;
            case 'turno3_12hrs': $horasReglamentarias = 51; $NombreTurno = "Turno 3 (12hrs)"; break;
            case 'turno2_12hrs': $horasReglamentarias = 45; $NombreTurno = "Turno 2 (12hrs)"; break;
            default: $horasReglamentarias = 0; $NombreTurno = "No hay";
        }

        // Recolectar registros agrupados por día
        $detalle = [1=>[],2=>[],3=>[],4=>[],5=>[],6=>[],7=>[]];
        $totalHorasEmpleado   = 0;
        $totalMinutosEmpleado = 0;
        $registroActual       = 0;

        while ($row = sqlsrv_fetch_array($resultDet)) {
            $minutos = $row['dif'];
            if ($minutos < 0) { $minutos += 24 * 60; }

            $horasInt = floor($minutos / 60);
            $minsInt  = $minutos % 60;

            if ($minsInt >= 50) { $horasInt += 1; $minsInt = 0; }
            elseif ($minsInt >= 26) { $minsInt = 30; }
            else { $minsInt = 0; }

            $totalHorasEmpleado   += $horasInt;
            $totalMinutosEmpleado += $minsInt;

            $detalle[$row['dia']][] = [
                'horas'  => sprintf("%02d:%02d", $horasInt, $minsInt),
                'motivo' => $row['motivo']
            ];
            $registroActual++;
        }

        // Número de filas = máximo de registros en un solo día
        $maxFilas = 1;
        foreach ($detalle as $regs) {
            if (count($regs) > $maxFilas) { $maxFilas = count($regs); }
        }

        // Columna X de cada día (DATEPART WEEKDAY: Dom=1, Lun=2, ... Sab=7)
        $colX = [2=>$horasx, 3=>$horasx+15, 4=>$horasx+30, 5=>$horasx+45,
                 6=>$horasx+60, 7=>$horasx+75, 1=>$horasx+90];

        // Filas de continuación (la principal ya está dibujada)
        $filasY = [0 => $horasy];
        for ($f = 1; $f < $maxFilas; $f++) {
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(10, 5, utf8_decode($rowal['noemp']), 1, 0, 'L', 0);                        // NoEmp
            $pdf->Cell(40, 5, ucwords(strtolower(utf8_decode($rowal['Nombre']))), 1, 0, 'L', 0);  // Nombre
            $pdf->Cell(40, 5, "", 1, 0, 'L', 0);  // Puesto vacío
            $pdf->Cell(20, 5, "", 1, 0, 'L', 0);  // Maquina vacío
            for ($i = 0; $i < 7; $i++) {
                $pdf->Cell(10, 5, "", 1, 0, 'L', 0);
                $pdf->Cell(5,  5, "", 1, 0, 'L', 0);
            }
            $filasY[$f] = $pdf->GetY();
        }
        $ultimaFila = $filasY[$maxFilas - 1];

        // Colocar cada registro en su columna (día) y fila (orden de aparición)
        $pdf->SetFont('Arial', '', 7);
        foreach ($detalle as $dia => $regs) {
            foreach ($regs as $idx => $reg) {
                $pdf->SetXY($colX[$dia], $filasY[$idx]);
                $pdf->Cell(10, 5, $reg['horas'],  0, 0, 'C', 0);
                $pdf->Cell(5,  5, $reg['motivo'], 0, 0, 'C', 0);
            }
        }

        // Normalizar y redondear el total del empleado
        $totalHorasEmpleado   += intdiv($totalMinutosEmpleado, 60);
        $totalMinutosEmpleado  = $totalMinutosEmpleado % 60;
        if ($totalMinutosEmpleado >= 50) { $totalHorasEmpleado += 1; $totalMinutosEmpleado = 0; }
        elseif ($totalMinutosEmpleado >= 26) { $totalMinutosEmpleado = 30; }
        else { $totalMinutosEmpleado = 0; }
        $totalFormateado = sprintf("%02d:%02d", $totalHorasEmpleado, $totalMinutosEmpleado);

        // Columna TOTAL: celdas vacías arriba, total en la última fila (todas ancho 20)
        for ($f = 0; $f < $maxFilas; $f++) {
            $pdf->SetXY(225, $filasY[$f]);
            if ($f === $maxFilas - 1) {
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell(20, 5, $totalFormateado, 1, 0, 'C', 0);
            } else {
                $pdf->Cell(20, 5, "", 1, 0, 'C', 0);
            }
        }

        // Validación 60.5 hrs
        $totalFinal = $totalHorasEmpleado + ($totalMinutosEmpleado / 60);
        $horast = $horasReglamentarias + $totalFinal;

        // Acumular total general del folio
        $total2Horas   += $totalHorasEmpleado;
        $total2Minutos += $totalMinutosEmpleado;

        // Guardar resumen
        $empleadosProcessados[$key] = [
            'noemp'             => $noemp,
            'nombre'            => ucwords(strtolower(utf8_decode($rowal['Nombre']))),
            'maquina'           => $rowal['NombreMaquina'],
            'total_horas'       => $totalFormateado,
            'registros'         => $registroActual,
            'turno'             => $rowal["turnoAsignado"],
            'nombreTurno'       => $NombreTurno,
            'hrsReglamentarias' => $horasReglamentarias,
            'HorasPara60'       => $horast
        ];

        $pdf->Ln();
    }

    // ----- Total general del folio -----
    $total2Horas   += intdiv($total2Minutos, 60);
    $total2Minutos  = $total2Minutos % 60;
    if ($total2Minutos >= 55) { $total2Horas += 1; $total2Minutos = 0; }
    elseif ($total2Minutos >= 28) { $total2Minutos = 30; }
    else { $total2Minutos = 0; }
    $totalGeneralFormateado = sprintf("%02d:%02d", $total2Horas, $total2Minutos);

    $oby = $pdf->GetY();
    $pdf->SetXY(225, $oby);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 5, $totalGeneralFormateado, 1, 0, 'C', 0);
    $pdf->Ln(20);

    // ----- Firmas -----
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(50, 5, "");
    $pdf->Cell(60, 5, "_____________________________________");
    $pdf->Cell(60, 5, "");
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    if ($autorizo == 1) {
        $extensiones = ['png', 'jpg', 'jpeg'];
        $ruta_firma = null;
        foreach ($extensiones as $ext) {
            $ruta = "../firmas/" . $autorizoemp . "." . $ext;
            if (file_exists($ruta)) { $ruta_firma = $ruta; $size = 13; break; }
        }
        if (!$ruta_firma) {
            foreach ($extensiones as $ext) {
                $ruta = "../../FirmaDigital/firmas/" . $autorizoemp . "." . $ext;
                if (file_exists($ruta)) { $ruta_firma = $ruta; $size = 40; break; }
            }
        }
        if ($ruta_firma) { $pdf->Image($ruta_firma, $x+12, $y-10, $size); }
    }

    if ($elaboronoemp) {
        $extensiones = ['png', 'jpg', 'jpeg'];
        $ruta_firma_sup = null;
        foreach ($extensiones as $ext) {
            $ruta = "../firmas/" . $elaboronoemp . "." . $ext;
            if (file_exists($ruta)) { $ruta_firma_sup = $ruta; $size = 13; break; }
        }
        if (!$ruta_firma_sup) {
            foreach ($extensiones as $ext) {
                $ruta = "../../FirmaDigital/firmas/" . $elaboronoemp . "." . $ext;
                if (file_exists($ruta)) { $ruta_firma_sup = $ruta; $size = 40; break; }
            }
        }
        if ($ruta_firma_sup) {
            $pdf->Image($ruta_firma_sup, $x-112, $y-12, $size);
            $pdf->SetFont('Arial','',8);
            $pdf->Text($x-112, $y+3, ucwords(strtolower(utf8_decode($nombreSup))));
            $pdf->Text($x+7,   $y+3, ucwords(strtolower(utf8_decode($nombreAut))));
        }
    }

    $pdf->MultiCell(60, 5, "__________________________________");
    $pdf->Cell(70, 5, "");
    $pdf->Cell(40, 5, "Elaboro");
    $pdf->Cell(82, 5, "");
    $pdf->Cell(40, 5, "Autorizo");
    $pdf->SetFont('Arial', 'B', 5);
    $pdf->Cell(20, 5, "");

    // ----- Resumen final del folio -----
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(280, 10, "Resumen final (Folio $folio):", 0, 1);
    $pdf->SetFont('Arial', '', 6);
    foreach ($empleadosProcessados as $hist) {
        $pdf->Cell(240, 4,
            "No. de empleado: {$hist['noemp']}   |  Nombre de empleado: {$hist['nombre']}    |    Maquina: {$hist['maquina']}    |    Cant. de T. extra: {$hist['registros']}   |   Total de horas extra: {$hist['total_horas']}   |   Turno Identificado: {$hist['nombreTurno']}   |   Horas Regl. x turno: {$hist['hrsReglamentarias']}", 1, 1);
    }
}

/* =========================================================
   6. GENERACION DEL PDF (UNA HOJA POR FOLIO)
   ========================================================= */
$pdf = new PDF('L', 'mm', 'A4');
$pdf->esPreview = $esPreview;

if (!$hayFiltro) {
    // Sin filtros: no se ejecuta busqueda para no traer toda la base
    $pdf->folioActual = '';
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(20);
    $pdf->Cell(0, 10, utf8_decode("Debe especificar al menos un filtro (rango de fechas, departamento, tipo de empleado o IBM)."), 0, 1, 'C');
} elseif (count($folios) === 0) {
    // Filtros validos pero sin resultados
    $pdf->folioActual = '';
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(20);
    $pdf->Cell(0, 10, utf8_decode("No se encontraron folios con los filtros seleccionados."), 0, 1, 'C');
} else {
    foreach ($folios as $folio) {
        $pdf->folioActual = $folio;   // se muestra en el encabezado de la hoja
        $pdf->AddPage();
        renderFolio($pdf, $conn, $folio, $condEmp);
    }
}

$pdf->Output();