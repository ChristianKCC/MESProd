<?php
require('../../fpdf/fpdf.php');
require_once(__DIR__ . "/../../conexion.php");

if (session_status() === PHP_SESSION_NONE)
    session_start();

/* =========================================================================
 *  Subclase FPDF con soporte de texto rotado (para la franja vertical)
 * ========================================================================= */
class PDF extends FPDF
{
    protected $angle = 0;
    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf(
                'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',
                $c,
                $s,
                -$s,
                $c,
                $cx,
                $cy,
                -$cx,
                -$cy
            ));
        }
    }
    function RotatedText($x, $y, $txt, $angle)
    {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }
    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}

function formatDate($value, $format = "d/m/Y")
{
    if ($value instanceof DateTime)
        return $value->format($format);
    return $value ? $value : "";
}
function formatTime($value, $format = "H:i")
{
    if ($value instanceof DateTime)
        return $value->format($format);
    return $value ? $value : "";
}

$id = isset($_GET['id']) ? base64_decode($_GET['id']) : null;
if (!$id)
    exit;

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");
$query = "SELECT
            ct.Ctt_id,
            ct.Ctt_fecha,
            ct.Ctt_depto,
            ct.Ctt_a,
            ct.Ctt_de,
            ct.Ctt_horario,
            ct.Ctt_rol,
            ct.Ctt_aPartirDel,
            ct.Ctt_hastaEl,
            ct.Ctt_horaPresentacion,
            ct.Ctt_turnoPresentacion,
            ct.Ctt_tripulacionDe,
            ct.Ctt_horarioDe,
            ct.Ctt_horarioA,
            ct.Ctt_descansos,
            ct.Ctt_diaAdd,
            ct.Ctt_horarioAdd,
            ct.Ctt_ibmEmpleado,
            emp.Nombre AS NombreEmpleado,
            ct.Ctt_ibmAutoriza,
            sup.Nombre AS NombreSupervisor,
            dep.NombreDepto
        FROM dbo.tblMXPRCambioTurnoTemporal ct
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados emp ON emp.NoEmp = ct.Ctt_ibmEmpleado
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados sup ON sup.NoEmp = ct.Ctt_ibmAutoriza
        LEFT JOIN TLX009MXDB.dbo.tblDepartamentos dep
       ON dep.NoDepto = TRY_CAST(ct.Ctt_depto AS INT)
        WHERE ct.Ctt_id = ?";

$stmt = sqlsrv_query($conn, $query, [intval($id)]);
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$row)
    exit;

/* =========================================================================
 * MAPA para normalizacion de valores
 * ========================================================================== */
function normalizarTurno($valor)
{
    $mapa = [
        "" => "Selecciona tu turno",
        "turno1" => "Turno 1",
        "turno2" => "Turno 2",
        "turno3" => "Turno 3",
        "turno1_12hrs" => "Turno 1 (12 hrs)",
        "turno2_12hrs" => "Turno 2 (12 hrs)",
        "turno3_12hrs" => "Turno 3 (12 hrs)",
        "mixto1" => "Mixto 1",
        "mixto2" => "Mixto 2",
        "mixto3" => "Mixto 3",
        "mixto4" => "Mixto 4"
    ];

    return $mapa[$valor] ?? $valor;
}


/* =========================================================================
 *  PDF — diseño compacto: ocupa la MITAD SUPERIOR de una hoja Letter vertical
 *  (mismo estilo/formato/tablas que el formato web)
 * ========================================================================= */
$pdf = new PDF('P', 'mm', 'Letter');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

function u($t)
{
    return utf8_decode($t);
}

/* ---- Geometría ---- */
$L = 8;            // borde izquierdo exterior
$R = 208;         // borde derecho exterior
$T = 8;           // borde superior
$strip = 7;            // ancho de la franja vertical izquierda
$cx = $L + $strip; // inicio del contenido
$mid = 112;         // divisor de columnas en campos a 2 columnas

$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.3);

// // Texto principal en azul oscuro
// $pdf->SetTextColor(10, 10, 80);

// Etiquetas en azul más claro (antes gris)
// $pdf->SetTextColor(40, 40, 120);

// // Líneas y bordes en azul
// $pdf->SetDrawColor(10, 10, 80);


/* ---- Helper: campo con etiqueta arriba y valor sobre subrayado (auto-ajuste) ---- */
function campo($pdf, $x, $y, $w, $h, $label, $value)
{
    $pdf->SetTextColor(90, 90, 90);
    // $pdf->SetTextColor(40, 40, 120); // etiquetas en azul claro


    $pdf->SetFont('Arial', '', 6);
    $pdf->Text($x + 1.5, $y + 3, strtoupper($label));
    // Valor: reduce el tamaño de letra si no cabe en el ancho del campo
    $pdf->SetTextColor(0, 0, 0);
    // $pdf->SetTextColor(10, 10, 80); // valores en azul oscuro

    $maxW = $w - 4;
    $fs = 9;
    $pdf->SetFont('Arial', '', $fs);
    while ($fs > 5.5 && $pdf->GetStringWidth($value) > $maxW) {
        $fs -= 0.5;
        $pdf->SetFont('Arial', '', $fs);
    }
    $pdf->Text($x + 2.5, $y + $h - 1.8, $value);
    // Subrayado
    $pdf->SetLineWidth(0.2);
    $pdf->Line($x + 1.5, $y + $h - 1, $x + $w - 1.5, $y + $h - 1);
    $pdf->SetLineWidth(0.3);
}

/* ---------- ENCABEZADO (logo + título) ---------- */
$pdf->Image('../../img/logo.jpg', $cx + 1, $T + 3, 48);
$pdf->SetTextColor(0, 0, 0);
// // $pdf->SetTextColor(40, 40, 120);
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetXY($cx + 70, $T + 1.5);
$titulo = 'C A M B I O   T E M P O R A L   D E   T U R N O';
$pdf->Cell($R - ($cx + 52) - 2, 6, u($titulo), 0, 0, 'C');

$headBottom = $T + 8;
$pdf->SetLineWidth(0.4);
$pdf->Line($cx, $headBottom, $R, $headBottom);
$pdf->SetLineWidth(0.3);

/* ---------- FILAS DE CAMPOS ---------- */
$y = $headBottom;
$rowH = 8;

// FECHA / DEPTO
campo($pdf, $cx, $y, $mid - $cx, $rowH, 'Fecha:', formatDate($row['Ctt_fecha']));
campo($pdf, $mid, $y, $R - $mid, $rowH, 'Depto:', u($row['Ctt_depto']));
$y += $rowH;

// A / DE  (nombre + IBM en el mismo renglón)
// . '  -  ' . $row['Ctt_ibmEmpleado']
$aVal = u($row['Ctt_a']);
// . '  -  ' . $row['Ctt_ibmAutoriza']
$deVal = u($row['NombreSupervisor']);
campo($pdf, $cx, $y, $mid - $cx, $rowH, 'A:', ucwords(strtolower(u($aVal))));
campo($pdf, $mid, $y, $R - $mid, $rowH, 'De:', ucwords(strtolower(u($deVal))));
$y += $rowH;

// Frase en cursiva
$pdf->SetFont('Arial', 'B', 8.5);
$pdf->SetXY($cx, $y + 1);
$pdf->Cell($R - $cx, 5, u('Por medio de la presente se le informa el siguiente cambio de:'), 0, 0, 'C');
$y += 5;

// HORARIO / TRIPULACION
//   TRIPULACION -> se mantiene la columna Ctt_rol (antes etiquetada "Rol")
campo($pdf, $cx, $y, $mid - $cx, $rowH, 'Horario (de acuerdo a rol): ', u(normalizarTurno($row['Ctt_horario'])));
campo($pdf, $mid, $y, $R - $mid, $rowH, 'Tripulacion:', u($row['Ctt_tripulacionDe']));
$y += $rowH;

// A PARTIR DEL DIA / HASTA
campo($pdf, $cx, $y, $mid - $cx, $rowH, 'A partir del dia:', formatDate($row['Ctt_aPartirDel']));
campo($pdf, $mid, $y, $R - $mid, $rowH, 'Hasta:', formatDate($row['Ctt_hastaEl']));
$y += $rowH + 1;

/* ---------- CAJA DE 2 COLUMNAS ---------- */
$boxTop = $y;
$boxH = 25;
$c1 = $cx;     // Debiendose presentar a
$c2 = 92;      // Horario

// Dibujar caja general
$pdf->Rect($cx, $boxTop, $R - $cx, $boxH);

// Línea divisoria entre columna 1 y 2
$pdf->Line(110, $boxTop, 110, $boxTop + $boxH);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(0, 0, 0);
// $pdf->SetTextColor(40, 40, 120);

// Encabezados
$pdf->Text($c1 + 2, $boxTop + 5, u('Debiendose presentar a:'));
$pdf->Text(110 + 2, $boxTop + 5, u('Horario:'));

// Col 1: TURNO
campo($pdf, $c1, $boxTop + 7, $c2 - $c1, 9, 'Turno:', u(normalizarTurno($row['Ctt_turnoPresentacion'])));

// Col 2: EN EL HORARIO / A ESTE HORARIO
campo($pdf, 110, $boxTop + 7, $R - 110, 9, 'En el horario:', formatTime($row['Ctt_horarioDe']));
campo($pdf, 110, $boxTop + 16, $R - 110, 9, 'A este horario:', formatTime($row['Ctt_horarioA']));

$y = $boxTop + $boxH + 1.5;


/* ---------- SUS DESCANSOS ---------- */
$h1 = 8;
$pdf->Rect($cx, $y, $R - $cx, $h1);
campo($pdf, $cx, $y, $R - $cx, $h1, 'Sus descansos:', u($row['Ctt_descansos']));
$y += $h1 + 1;

/* ---------- ADICIONAL LOS DIAS ---------- */
$h2 = 13;
$pdf->Rect($cx, $y, $R - $cx, $h2);
$pdf->SetTextColor(90, 90, 90);
$pdf->SetFont('Arial', '', 6);
$pdf->Text($cx + 1.5, $y + 3, strtoupper(u('Adicional los dias:')));
$pdf->SetTextColor(0, 0, 0);
// $pdf->SetTextColor(40, 40, 120);

$pdf->SetFont('Arial', '', 9);
$pdf->Text($cx + 2, $y + 6.5, u($row['Ctt_diaAdd']));
$pdf->SetLineWidth(0.2);
$pdf->Line($cx + 1.5, $y + 7, $R - 1.5, $y + 7);
$pdf->SetTextColor(90, 90, 90);
$pdf->SetFont('Arial', '', 6);
// $pdf->Text($cx + 1.5, $y + 9.5, strtoupper(u('En el horario:')));
$pdf->SetTextColor(0, 0, 0);
// $pdf->SetTextColor(40, 40, 120);

$pdf->SetFont('Arial', '', 9);
$pdf->Text($cx + 2, $y + 12, u($row['Ctt_horarioAdd']));
$pdf->Line($cx + 1.5, $y + 12.3, $R - 1.5, $y + 12.3);
$pdf->SetLineWidth(0.3);
$y += $h2 + 1.5;

/* ---------- FIRMAS ---------- */
$extensiones = ['png', 'jpg', 'jpeg'];
function buscarFirma($ibm, $extensiones)
{
    if (!$ibm)
        return null;
    foreach ($extensiones as $ext) {
        $ruta = "../firmas/{$ibm}.{$ext}";
        if (file_exists($ruta))
            return ['ruta' => $ruta, 'w' => 13];
    }
    foreach ($extensiones as $ext) {
        $ruta = "../../FirmaDigital/firmas/{$ibm}.{$ext}";
        if (file_exists($ruta))
            return ['ruta' => $ruta, 'w' => 26];
    }
    return null;
}

$sigTop = $y;
$lineY = $sigTop + 8;
$lx1 = $cx + 14;
$lx2 = $mid - 6;   // línea izquierda (Supervisor)
$rx1 = $mid + 6;
$rx2 = $R - 14;    // línea derecha  (Empleado)
$lcen = ($lx1 + $lx2) / 2;
$rcen = ($rx1 + $rx2) / 2;

// Imágenes de firma (si existen)
$firmaSup = buscarFirma($row['Ctt_ibmAutoriza'], $extensiones);
if ($firmaSup) {
    $w = $firmaSup['w'];
    $pdf->Image($firmaSup['ruta'], $lcen - $w / 2, $lineY - 9, $w);
}
$firmaEmp = buscarFirma($row['Ctt_ibmEmpleado'], $extensiones);
if ($firmaEmp) {
    $w = $firmaEmp['w'];
    $pdf->Image($firmaEmp['ruta'], $rcen - $w / 2, $lineY - 9, $w);
}

// Líneas de firma
$pdf->SetLineWidth(0.3);
$pdf->Line($lx1, $lineY, $lx2, $lineY);
$pdf->Line($rx1, $lineY, $rx2, $lineY);

// Nombres
$pdf->SetTextColor(0, 0, 0);
// $pdf->SetTextColor(40, 40, 120);
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY($lx1, $lineY + 0.5);
$pdf->Cell($lx2 - $lx1, 4, ucwords(strtolower(u($row['NombreSupervisor'] ?? ''))), 0, 0, 'C');
$pdf->SetXY($rx1, $lineY + 0.5);
$pdf->Cell($rx2 - $rx1, 4, ucwords(strtolower(u($row['NombreEmpleado'] ?? ''))), 0, 0, 'C');

// Etiquetas
$pdf->SetTextColor(90, 90, 90);
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY($lx1, $lineY + 4.5);
$pdf->Cell($lx2 - $lx1, 4, 'Firma del Supervisor', 0, 0, 'C');
$pdf->SetXY($rx1, $lineY + 4.5);
$pdf->Cell($rx2 - $rx1, 4, 'Firma del Empleado', 0, 0, 'C');
$y = $lineY + 9.5;

/* ---------- PIE ---------- */
$footY = $y;
$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(0, 0, 0);
// $pdf->SetTextColor(40, 40, 120);

$pdf->Text($cx + 1, $footY + 3, 'KCM-173881');
$pdf->SetXY($cx, $footY);
$pdf->Cell($R - $cx, 3, 'Ref-8-702A-18', 0, 0, 'C');
$pdf->Text($R - 14, $footY + 3, 'Rev-01');
$BOT = $footY + 4;

/* ---------- BORDE EXTERIOR + FRANJA VERTICAL ---------- */
$pdf->SetLineWidth(0.4);
$pdf->Rect($L, $T, $R - $L, $BOT - $T);
$pdf->Line($cx, $T, $cx, $BOT);
$pdf->SetLineWidth(0.3);

// Texto vertical de la franja izquierda
$pdf->SetFont('Arial', '', 5.5);
// $pdf->SetTextColor(40, 40, 120);
$midY = ($T + $BOT) / 2;
$pdf->RotatedText(
    $L + 5,
    $midY + 58,
    u('PARA MAYOR INFORMACION FAVOR DE CONTACTAR A SU SUPERVISOR'),
    90
);

$pdf->Output('I', "cambio_turno.pdf");
?>