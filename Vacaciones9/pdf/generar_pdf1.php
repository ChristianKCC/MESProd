<?php
//Creacion de PDF para Autotizaciones de PDF
require('../../fpdf/fpdf.php');

// SANITIZACION DE DATOS
function getData(string $key, string $default = ''): string {
    return isset($_POST[$key]) ? trim(strip_tags($_POST[$key])) : $default;
}

$nombre = getData('nombre');
$puesto = getData('puesto');
$fecha_ingreso = getData('fecha_ingreso');
$solicitud_por = getData('solicitud_por');
$vacaciones_por = getData('vacaciones_de');
$vacaciones_anio_de = getData('vacaciones_anio_de');
$vacaciones_hasta = getData('vacaciones_hasta');
$dias_antiguedad = getData('dias_antiguedad');
$dias_solicitados = getData('dias_solicitados');
$prima_vacacional = getData('prima_vacacional');
$dias_reposicion = getData('dias_reposicion');
$dias_descanso = getData('dias_descanso');
$total_dias = getData('total_dias');
$tarjeta = getData('tarjeta');
$departamento = getData('departamento');
$antiguedad_de = getData('antiguedad_de');
$dias_habiles_partir = getData('dias_habiles_partir');
$periodo_de = getData('periodo_de');
$periodo_anio = getData('periodo_anio');
$periodo_solicitado = getData('periodo_solicitado');
$tipo_solicitud = getData('tipo_solicitud');
$importes = [
    getData('importe1'),
    getData('importe2'),
    getData('importe3'),
    getData('importe4'),
    getData('importe5') ,
];
$fechaDe = getData('vacaciones_de');
$fechaInicioMes = new DateTime($fechaDe);
$anio = $fechaInicioMes->format('Y');
$mes  = $fechaInicioMes->format('m');

$observaciones = getData('observaciones');
$fechas_reposicion = getData('fechas_reposicion');
$saldo_periodo = getData('saldo_periodo');
$dias_habiles_saldo = getData('dias_habiles_saldo');

// Recuperar días seleccionados
// $diasSeleccionadosRaw = getData('dias_festivos');
// $diasSeleccionados = [];
// if (!empty($diasSeleccionadosRaw)) {
//     $diasSeleccionados = explode(',', $diasSeleccionadosRaw);
// }
// Recuperar días seleccionados desde POST (name="dia_YYYY-MM-DD")
$diasSeleccionados = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'dia_') === 0 && !empty($value)) {
        $fechaStr = substr($key, 4); // quita "dia_"
        $diasSeleccionados[$fechaStr] = $value; // V, F, D, R
    }
}

// Detectar meses involucrados
$fechaInicio = new DateTime($fechaDe);
$fechaFin = new DateTime($vacaciones_hasta);

$mesesInvolucrados = [];
$cursor = clone $fechaInicio;
while ($cursor <= $fechaFin) {
    $mesesInvolucrados[] = [
        'anio' => $cursor->format('Y'),
        'mes' => $cursor->format('m')
    ];
    $cursor->modify('first day of next month');
}

$nombreMeses = [
    '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril',
    '05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto',
    '09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'
];

// // Array de festivos
$diasFestivos = [
    "2026-01-01","2026-02-02","2026-03-16",
    "2026-05-01","2026-09-16","2026-11-16",
    "2026-12-25","2026-04-02","2026-04-03",
    "2026-11-02","2026-12-12","2026-12-24",
    "2026-12-31"
];

// Dias del calendario
$dias_cal = [];
for ($i = 1; $i <= 31; $i++){
    $dias_cal[$i] = getData("dia_$i");
}

// Crear carpeta solicitudes
$carpeta = __DIR__ . '/solicitudes';
if(!is_dir($carpeta)){
    mkdir($carpeta, 0755, true);
}

// Nombre del archivo: IBM_Nombre_Fecha.pdf

$fecha_hoy = date('Y-m-d');
$nombre_limpio = preg_replace('/[^a-zA-Z0-9 _-]/', '', $nombre);
$nombre_limpio = str_replace(' ', '_', trim($nombre_limpio));
$ibm_limpio = preg_replace('/[^a-zA-Z0-9 _-]/', '', $tarjeta);
$archivo_pdf = $carpeta . '/' . ($ibm_limpio ? $ibm_limpio . '_' : '') . $nombre_limpio . '_' . $fecha_hoy . '.pdf';

// CLASE FPDF SIN HEADER / FOOTER AUTOMATICO

class PDF extends FPDF {
    function Header() {}
    function Footer() {}
}

// INICIALIZAR PDF
$pdf = new PDF('P', 'mm', 'Letter');
$pdf -> AddPage();
$pdf -> SetMargins(14, 10, 14);
$pdf -> SetAutoPageBreak(false);
$pdf -> SetLineWidth(0.3);

$pw = 182; // ancho util
$lx = 14; // margen izquierdo

// Encabezado
$pdf -> SetFont('Arial', 'B', 13);
$pdf -> SetXY($lx + 16, 10);

$pdf -> Image('../../img/logo.jpg', 60, 7, 100);
$pdf -> Ln(4);

$pdf -> SetFont('Arial', 'B', 7);
$pdf -> SetX($lx + 16);
$pdf -> Cell($pw - 16, 4, 'PLANTA PROSEDE', 0, 1, 'C');

$pdf -> SetFont('Arial', 'B', 11);
$pdf -> SetX($lx);
$pdf -> Cell($pw + 10, 5, 'SOLICITUD DE VACACIONES', 0, 1, 'C');

$pdf -> Ln(5);
$pdf -> SetLineWidth(0.5);

$pdf -> SetLineWidth(0.3);
$pdf -> Ln(10);

// Campos principales
$y0 = $pdf -> GetY();
$c1x = $lx;
$c2x = $lx + 94;
$cw1 = 90;
$cw2 = 88;
$rh = 5;

function fila(FPDF $pdf, $x, $y, $label, $value, $totalW) {
    $pdf -> SetFont('Arial', 'B', 7);
    $pdf -> SetXY($x, $y);
    $lw = $pdf -> GetStringWidth($label) + 1;
    $pdf -> Cell($lw, 4, $label, 0, 0, 'L');
    $pdf -> SetFont('Arial', '', 8);
    $pdf -> SetXY($x + $lw, $y + 0.5);
    $pdf -> Cell($totalW - $lw, 4, $value, 'B', 0, 'L');
}

// Columna izquierda
$yi = $y0;
fila($pdf, $c1x, $yi, 'NOMBRE: ', utf8_decode($nombre), $cw1);
$yi += $rh;
fila($pdf, $c1x, $yi, 'PUESTO: ', utf8_decode($puesto), $cw1);
$yi += $rh;
fila($pdf, $c1x, $yi, 'FECHA DE INGRESO: ', utf8_decode($fecha_ingreso), $cw1);
$yi += $rh;
fila($pdf, $c1x, $yi, 'SOLICITUD DE VACACIONES: ', utf8_decode($solicitud_por), $cw1);
$yi += $rh;

$pdf -> SetFont('Arial', 'B', 7);
$pdf -> SetXY($c1x, $yi);
$pdf -> Cell(6, 4, 'DE: ',    0, 0, 'L');
$pdf -> SetFont('Arial', '', 8);
$pdf -> Cell(28, 4, $vacaciones_por, 'B', 0, 'L');
$pdf -> SetFont('Arial', 'B', 7);
$pdf -> Cell(12, 4, 'DEL 20',    0, 0, 'L');
$pdf -> SetFont('Arial', '', 8);
$pdf -> Cell(10, 4, $vacaciones_anio_de, 'B', 0, 'L');
$pdf -> SetFont('Arial', 'B', 7);
$pdf -> Cell(18, 4, 'HASTA EL DIA', 0, 0, 'L');
$pdf -> SetFont('Arial', '', 8);
$pdf -> Cell($cw1 - 74, 4, $vacaciones_hasta, 'B', 1, 'L');
$yi += $rh;

fila($pdf, $c1x, $yi, 'DIAS CORRESPONDIENTES POR ANTIGUEDAD: ', utf8_decode($dias_antiguedad), $cw1);
$yi += $rh;
fila($pdf, $c1x, $yi, 'DIAS DE VACACIONES SOLICITADOS: ', utf8_decode($dias_solicitados), $cw1);
$yi += $rh;
fila($pdf, $c1x, $yi, 'PRIMA VACACIONAL EQUIVALENTE: ', utf8_decode($prima_vacacional), $cw1);
$yi += $rh;
fila($pdf, $c1x, $yi, 'DIAS DE REPOSICION O FESTIVO: ', utf8_decode($dias_reposicion), $cw1);
$yi += $rh;
fila($pdf, $c1x, $yi, 'DIAS DE DESCANSO: ', utf8_decode($dias_descanso), $cw1);
$yi += $rh;
fila($pdf, $c1x, $yi, 'TOTAL DE DIAS: ', utf8_decode($total_dias), $cw1);
$yi += $rh;

// COLUMNA DERECHA
$yd = $y0;
fila($pdf, $c2x, $yd, 'TARJETA NO.: ', utf8_decode($tarjeta), $cw2);
$yd += $rh;
fila($pdf, $c2x, $yd, 'DEPARTAMENTO: ', utf8_decode($departamento), $cw2);
$yd += $rh;
fila($pdf, $c2x, $yd, 'ANTIGUEDAD DE: ', utf8_decode($antiguedad_de), $cw2);
$yd += $rh;
fila($pdf, $c2x, $yd, 'DIA(S) HABIL(ES) A PARTIR DEL: ', utf8_decode($dias_habiles_partir), $cw2);
$yd += $rh;

// $pdf -> SetFont('Arial', 'B', 7);
// $pdf -> SetXY($c2x, $yd);
// $pdf -> Cell(6, 4, 'DE: ',   0, 0, 'L');
// $pdf -> SetFont('Arial', '', 8);
// $pdf -> Cell(22, 4, $periodo_de, 'B', 0, 'L');
// $pdf -> SetFont('Arial', 'B', 7);
// $pdf -> Cell(12, 4, 'DEL 20', 0, 0, 'L');
// $pdf -> SetFont('Arial', '', 8);
// $pdf -> Cell($cw2 - 40, 4, $vacaciones_anio_de, 'B', 1, 'L');
// $yd += $rh;

fila($pdf, $c2x, $yd, 'PERIODO SOLICITADO: ', utf8_decode($periodo_solicitado), $cw2);
$yd += $rh;
fila($pdf, $c2x, $yd, 'TIPO DE SOLICITUD: ', utf8_decode($tipo_solicitud), $cw2);
$yd += $rh;

foreach($importes as $imp){
    fila($pdf, $c2x, $yd, 'IMPORTE $', $imp, $cw2);
    $yd += $rh;
}

$pdf -> SetY(max($yi, $yd) + 2);

// LINEA
$pdf -> Ln(10);
$pdf -> SetLineWidth(0.5);

$pdf -> SetLineWidth(0.3);

// INSTRUCCION
$pdf -> SetFont('Arial', 'B', 8);
$pdf -> Cell($pw, 5, 'ANOTAR EN EL RECUADRO SIGUIENTE LO QUE CORRESPONDA SEGUN EL CASO', 0, 1, 'C');

$pdf -> SetFont('Arial', 'B', 8);
$pdf -> SetX($lx);
$pdf -> Cell(46, 4, 'V = VACACIONES', 0, 0, 'L');
$pdf -> Cell(46, 4, 'D = DESCANSO', 0, 0, 'L');
$pdf -> Cell(45, 4, 'F = FESTIVO', 0, 0, 'L');
$pdf -> Cell(45, 4, 'R = REPOSICION', 0, 1, 'L');
$pdf -> Ln(5);

// TABLA DEL CALENDARIO
$cal_x = $lx;
$cal_y = $pdf->GetY();
$cw_cel = $pw / 16;
$ch_hdr = 5;
$ch_val = 7;

$colores = [
    'V' => [198, 224, 180],
    'D' => [255, 255, 153],
    'F' => [255, 153, 153],
    'R' => [180, 198, 231],
];

// Array de iniciales de días de la semana
$diasSemana = ['L','M','M','J','V','S','D'];

$nombreMeses = [
    '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril',
    '05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto',
    '09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'
];

foreach ($mesesInvolucrados as $m) {
    $anio = $m['anio'];
    $mes = $m['mes'];

    // Título del mes
    // $pdf->SetFont('Arial','B',10);
    // $pdf->Cell(0,10,"Calendario de {$nombreMeses[$mes]} $anio",0,1,'C');
    $pdf->SetFont('Arial','B',8);
    $pdf->SetXY($cal_x, $cal_y - 4);
    $pdf->Cell(60, 6, "Calendario de {$nombreMeses[$mes]} $anio", 0, 1, 'L');
    $cal_y = $pdf->GetY();

    // NUMEROS DEL 1-16
    $pdf->SetFont('Arial', 'B', 8);
    for($i = 1; $i <= 16; $i++){
        $pdf->SetXY($cal_x + ($i - 1) * $cw_cel, $cal_y);
        $pdf->Cell($cw_cel, $ch_hdr, (string)$i, 1, 0, 'C');
    }
    $cal_y += $ch_hdr;

    // INICIALES DE DÍAS DE LA SEMANA (1–16)
    $pdf->SetFont('Arial', 'I', 7);
    for($i = 1; $i <= 16; $i++){
        $fecha = DateTime::createFromFormat('Y-m-d', "$anio-$mes-".str_pad($i,2,'0',STR_PAD_LEFT));
        $diaInicial = $diasSemana[$fecha->format('N')-1];
        $pdf->SetXY($cal_x + ($i - 1) * $cw_cel, $cal_y);
        $pdf->Cell($cw_cel, $ch_hdr, $diaInicial, 1, 0, 'C');
    }
    $cal_y += $ch_hdr;

    // VALORES 1-16
    for ($i = 1; $i <= 16; $i++) {
        $fechaStr = "$anio-$mes-".str_pad($i,2,'0',STR_PAD_LEFT);
        $val = $diasSeleccionados[$fechaStr] ?? ''; // ahora usamos el array con fechas completas

        $pdf->SetXY($cal_x + ($i - 1) * $cw_cel, $cal_y);
        if (isset($colores[$val])) {
            [$r,$g,$b] = $colores[$val];
            $pdf->SetFillColor($r,$g,$b);
        } else {
            $pdf->SetFillColor(255,255,255);
        }
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($cw_cel, $ch_val, $val, 1, 0, 'C', true);
    }
    $cal_y += $ch_val + 2;

    // NUMEROS DEL 17-31
    $pdf->SetFont('Arial', 'B', 8);
    for($i = 17; $i <= 31; $i++){
        $pdf->SetXY($cal_x + ($i - 17) * $cw_cel, $cal_y);
        $pdf->Cell($cw_cel, $ch_hdr, (string)$i, 1, 0, 'C');
    }
    $cal_y += $ch_hdr;

    // INICIALES DE DÍAS DE LA SEMANA (17–31)
    $pdf->SetFont('Arial', 'I', 7);
    for($i = 17; $i <= 31; $i++){
        $fecha = DateTime::createFromFormat('Y-m-d', "$anio-$mes-".str_pad($i,2,'0',STR_PAD_LEFT));
        $diaInicial = $diasSemana[$fecha->format('N')-1];
        $pdf->SetXY($cal_x + ($i - 17) * $cw_cel, $cal_y);
        $pdf->Cell($cw_cel, $ch_hdr, $diaInicial, 1, 0, 'C');
    }
    $cal_y += $ch_hdr;

    // VALORES 17-31
    for ($i = 17; $i <= 31; $i++) {
        $fechaStr = "$anio-$mes-".str_pad($i,2,'0',STR_PAD_LEFT);
        $val = $diasSeleccionados[$fechaStr] ?? ''; // igual que arriba

        $pdf->SetXY($cal_x + ($i - 17) * $cw_cel, $cal_y);
        if (isset($colores[$val])) {
            [$r,$g,$b] = $colores[$val];
            $pdf->SetFillColor($r,$g,$b);
        } else {
            $pdf->SetFillColor(255,255,255);
        }
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($cw_cel, $ch_val, $val, 1, 0, 'C', true);
    }

    $cal_y += $ch_val + 5;
}

$pdf->SetY($cal_y + 3);

// Si hay más de 2 meses involucrados, saltar a nueva página
if (count($mesesInvolucrados) > 1) {
    $pdf->AddPage();
    $cal_y = $pdf->GetY();
}

// OBSERVACIONES
$pdf -> Ln(10);
$pdf -> SetFont('Arial', 'B', 8);
$pdf -> SetX($lx);
$pdf -> Cell(28, 5, 'OBSERVACIONES',0, 0, 'L');
$pdf -> SetFont('Arial', '', 8);
$pdf -> Cell($pw - 28, 5, $observaciones, 'B', 1, 'L');
$pdf -> SetX($lx);
$pdf -> Ln(2);

// REPOSICION O FESTIVO
$pdf -> SetFont('Arial', 'B', 8);
$pdf -> SetX($lx);
$label_rep = 'ANOTAR LAS FECHAS DE LOS DIAS POR REPOSICION O FESTIVO: ';
$lw_rep = $pdf -> GetStringWidth($label_rep) + 2;
$pdf -> Cell($lw_rep, 5, $label_rep, 0, 0, 'L');
$pdf -> SetFont('Arial', '', 8);
$pdf -> Cell($pw - $lw_rep, 5, $fechas_reposicion, 'B', 1, 'L');
$pdf -> SetX($lx);
$pdf -> Ln(4);

// SALDO
$pdf -> Ln(10);
$pdf -> SetFont('Arial', 'B', 8);
$pdf -> SetX($lx);
$pdf -> Cell(34, 5, 'SALDO AL PERIODO: ', 0, 0, 'L');
$pdf -> SetFont('Arial', '', 8);
$pdf -> Cell(52, 5, $saldo_periodo, 'B', 0, 'L');
$pdf -> SetFont('Arial', 'B', 8);
$pdf -> Cell(30, 5, 'DIAS HABILES: ', 0, 0, 'L');
$pdf -> SetFont('Arial', '', 8);
$pdf -> Cell($pw - 116, 5, $dias_habiles_saldo, 'B', 1, 'L');
$pdf -> Ln(20);

// FIRMAS
$fw = 52;
$gap = 13;
$fx1 = $lx;
$fx2 = $lx + $fw + $gap;
$fx3 = $lx + 2 * ($fw + $gap);
$fy = $pdf -> GetY();

$pdf -> Line($fx1, $fy, $fx1 + $fw, $fy);
$pdf -> Line($fx2, $fy, $fx2 + $fw, $fy);
$pdf -> Line($fx3, $fy, $fx3 + $fw, $fy);

$pdf -> SetFont('Arial', 'B', 7);
$pdf -> SetXY($fx1, $fy + 1);
$pdf -> Cell($fw, 4, 'FIRMA DEL TRABAJADOR', 0, 0, 'C');
$pdf -> SetXY($fx2, $fy + 1);
$pdf -> cell($fw, 4, 'AUTORIZACION JEFE DE AREA', 0, 0, 'C');
$pdf -> SetXY($fx3, $fy + 1);
$pdf -> Cell($fw, 4, 'Vo. Bo. RELACIONES INDS.', 0, 0, 'C');

$pdf -> SetY($fy + 8);

// FOTTER
$pdf -> Ln(8);
$pdf -> SetLineWidth(0.5);

$pdf -> SetlineWidth(0.3);
$pdf -> Ln(5);

$pdf -> SetFont('Arial', 'BI', 9);
$pdf -> SetX($lx);
$pdf -> Cell(140, 5, '!LOGRAR LA EXCELENCIA A TRAVES DE LA MEJORA CONTINUA!', 0, 0, 'L');

$ref_x = $lx + $pw - 38;
$ref_y = $pdf -> GetY() - 1;
$pdf -> SetFont('Arial', '', 7);
$pdf -> SetXY($ref_x, $ref_y);
$pdf -> Cell(38, 3.5, 'Revision: 01', 0, 1, 'R');
$pdf -> SetX($ref_x);
$pdf -> Cell(38, 3.5, 'Ref.: 8-702A-07', 0, 1, 'R');
$pdf -> SetX($ref_x);
$pdf -> Cell(38, 3.5, 'Formato: KCM-173872', 0, 1, 'R');

// GUARDAR EL PDF
// Crear carpeta única para solicitudes
$carpeta = dirname(__DIR__) . '/solicitudes';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0755, true);
}

$archivo_pdf = $carpeta . '/' . ($ibm_limpio ? $ibm_limpio . '_' : '') . $nombre_limpio . '_' . $fecha_hoy . '.pdf';

// Guardar en disco
$pdf->Output('F', $archivo_pdf);

// Mostrar en navegador
header("Content-Type: application/pdf");
readfile($archivo_pdf);
exit;

// BORRAR SESSION DESPUES DE GENERAR EL PDF
unset($_SESSION['solicitud']);

?>