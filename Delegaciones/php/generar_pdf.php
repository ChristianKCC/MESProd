<?php
/**
 * generar_pdf.php
 * Carta "Inter Oficina" de Ausencia de Planta y Delegación de autoridad.
 * Se abre con ?id=IdDelegacion. Mismo patrón que los demás módulos (FPDF + firmas por IBM).
 */
require_once __DIR__ . '/../../fpdf/fpdf.php';
require_once "../../conexion.php";

$PLANTA = 'CUAUTITLÁN';
// Lista "con copia para" (edítala según tu distribución)
$ccp = [
    'Lic. Eduardo Ponce',
    'Ing. Ricardo Alcántara',
    'CP. Carlos Moreno',
    'Ing. Alejandro Ramírez',
    'Ing. Israel García',
    'Ing. Alfredo Flores',
    'Ing. Antonio Rodríguez',
    'Ing. Luis Edgar Chaparro',
    'Sr. José Carlos García',
    'Ing. Omar Fuentes',
    'Sr. Jorge Ramírez Luna',
    'Ing. Armando Martínez',
    'Ing. Ana Karen Martínez',
    'Ing. Juan Manuel Lozano',
];

// --- helper de codificación para FPDF (core = latin1) ---
function u($s)
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string) $s);
}

// --- fecha en texto: "03 de Agosto de 2026" ($conAnio=true) o "03 de Agosto" ---
function fechaTexto($iso, $conAnio = true)
{
    $meses = [
        1 => 'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'
    ];
    $t = strtotime($iso);
    $txt = date('d', $t) . ' de ' . $meses[(int) date('n', $t)];
    return $conAnio ? $txt . ' de ' . date('Y', $t) : $txt;
}

// --- firma por IBM: primero ../firmas, luego ../../FirmaDigital/firmas ---
function buscarFirma($ibm)
{
    $exts = ['png', 'jpg', 'jpeg'];
    foreach ($exts as $e) {
        $r = __DIR__ . "/../firmas/{$ibm}.{$e}";
        if (file_exists($r))
            return ['ruta' => $r, 'w' => 28];
    }
    foreach ($exts as $e) {
        $r = __DIR__ . "/../../FirmaDigital/firmas/{$ibm}.{$e}";
        if (file_exists($r))
            return ['ruta' => $r, 'w' => 38];
    }
    return null;
}

// --- datos de empleado (nombre + puesto) ---
function datosEmpleado($conn, $ibm)
{
    $sql = "SELECT TOP 1
                e.Nombre              AS Nombre,
                ISNULL(p.nombre, '')  AS Puesto
            FROM TLX032MXDB.dbo.tblEmpleados e
            INNER JOIN TLX009MXDB.dbo.tblPuestos p 
                ON p.id = e.Puesto
            WHERE e.NoEmp = ?";
    $st = sqlsrv_query($conn, $sql, [$ibm]);
    if ($st && ($r = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC))) {
        return ['nombre' => trim($r['Nombre'] ?? ''), 'puesto' => trim($r['Puesto'] ?? '')];
    }
    return ['nombre' => 'IBM ' . $ibm, 'puesto' => ''];
}

// ─────────────────────────── DATOS ───────────────────────────
$conn = (new ClassConexion())->conexion("TLX002MXDB");
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Id no válido');
}

$sql = "SELECT IBMDelegante, IBMDelegado,
               CONVERT(varchar(10), FechaInicio,  23) AS FechaInicio,
               CONVERT(varchar(10), FechaFin,     23) AS FechaFin,
               CONVERT(varchar(10), FechaRegistro,23) AS FechaRegistro
        FROM dbo.tblMXPRDelegaciones
        WHERE IdDelegacion = ?";
$st = sqlsrv_query($conn, $sql, [$id]);
$d = $st ? sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC) : null;
if (!$d) {
    die('Delegación no encontrada');
}

$delegante = datosEmpleado($conn, $d['IBMDelegante']);
$delegado = datosEmpleado($conn, $d['IBMDelegado']);
sqlsrv_close($conn);

$nombreDelegante = ucwords(strtolower($delegante['nombre']));
$nombreDelegado = ucwords(strtolower($delegado['nombre']));
$primerNombreDel = explode(' ', trim($nombreDelegado))[0] ?? $nombreDelegado;

$fFecha = fechaTexto($d['FechaRegistro'], true);
$fIni = fechaTexto($d['FechaInicio'], false);
$fFin = fechaTexto($d['FechaFin'], true);

// ─────────────────────────── PDF ───────────────────────────
$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

$L = 25;
$R = 215.9 - 25;
$W = $R - $L;

// INTER OFICINA + regla
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(90, 90, 90);
$pdf->SetXY($L, 18);
$pdf->Cell(0, 5, u('INTER OFICINA'), 0, 1);
$pdf->SetLineWidth(0.4);
$pdf->Line($L, 26, $R, 26);

// Encabezado KC
$logo = __DIR__ . '/../../img/logo.jpg';
$yEnc = 30;
if (file_exists($logo)) {
    $pdf->Image($logo, $L, $yEnc, 100);
    $yEnc += 15;
} else {
    $pdf->SetXY($L, $yEnc);
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->SetTextColor(0, 60, 120);
    $pdf->Cell(0, 7, u('Kimberly-Clark de México, S.A.B. de C.V.'), 0, 1);
    $yEnc += 9;
}
$pdf->SetXY($L, $yEnc);
$pdf->SetFont('Arial', '', 13);
$pdf->SetTextColor(0, 0, 0);

// ancho fijo para la etiqueta
$pdf->Cell(30, 7, u('Planta '), 0, 0);

// ancho flexible para el valor
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(100, 7, u($PLANTA), 0, 1);


// Campos: Fecha / Para / De / Asunto
function campo($pdf, $L, $y, $label, $valor, $bold = false)
{
    $pdf->SetXY($L, $y);
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(16, 5, u($label), 0, 0);
    $pdf->SetFont('Arial', $bold ? 'B' : '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 5, u($valor), 0, 1);
}
$y = $yEnc + 13;
campo($pdf, $L, $y, 'Fecha:', $fFecha);
campo($pdf, $L, $y + 9, 'Para:', 'Jefes de Departamento');
campo($pdf, $L, $y + 18, 'De:', $nombreDelegante);
campo($pdf, $L, $y + 27, 'Asunto:', 'Ausencia de Planta y Delegación de autoridad', true);

// Cuerpo
$cuerpo = "Por medio del presente se les informa que a partir del día $fIni y hasta el $fFin "
    . "inclusive, su servidor estará fuera de planta, por lo que quedará a cargo "
    . strtoupper($nombreDelegado) . ", firmando todo lo necesario dentro de los límites "
    . "autorizados para un servidor y conforme a la política de Control Interno vigente.";
$pdf->SetXY($L, $y + 40);
$pdf->SetFont('Arial', '', 10.5);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell($W, 6, u($cuerpo), 0, 'J');
$pdf->Ln(4);
$pdf->SetX($L);
$pdf->MultiCell($W, 6, u("Sin más por el momento y agradeciendo el apoyo que tengan a bien "
    . "brindarle a $nombreDelegado, quedo de ustedes."), 0, 'J');

// ─────────────── Firmas (ATENTAMENTE / FIRMA AUTORIZADA) ───────────────
$ySig = $pdf->GetY() + 10;
if ($ySig > 200)
    $ySig = 200;
$colW = $W / 2;
$lcx = $L + $colW * 0.5;
$rcx = $L + $colW * 1.5;

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($L, $ySig);
$pdf->Cell($colW, 6, u('ATENTAMENTE'), 0, 0, 'C');
$pdf->SetXY($L + $colW, $ySig);
$pdf->Cell($colW, 6, u('FIRMA AUTORIZADA'), 0, 0, 'C');

$lineY = $ySig + 26;
$lx1 = $L + 12;
$lx2 = $L + $colW - 12;
$rx1 = $L + $colW + 12;
$rx2 = $R - 12;

$fSup = buscarFirma($d['IBMDelegante']);
if ($fSup) {
    $w = $fSup['w'];
    $pdf->Image($fSup['ruta'], $lcx - $w / 2, $lineY - 15, $w);
}
$fAut = buscarFirma($d['IBMDelegado']);
if ($fAut) {
    $w = $fAut['w'];
    $pdf->Image($fAut['ruta'], $rcx - $w / 2, $lineY - 15, $w);
}

$pdf->SetLineWidth(0.3);
$pdf->Line($lx1, $lineY, $lx2, $lineY);
$pdf->Line($rx1, $lineY, $rx2, $lineY);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($lx1, $lineY + 1);
$pdf->Cell($lx2 - $lx1, 5, u($nombreDelegante), 0, 0, 'C');
$pdf->SetXY($rx1, $lineY + 1);
$pdf->Cell($rx2 - $rx1, 5, u($nombreDelegado), 0, 0, 'C');

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(90, 90, 90);
$pdf->SetXY($lx1, $lineY + 6);
$pdf->Cell($lx2 - $lx1, 4, u($delegante['puesto']), 0, 0, 'C');
$pdf->SetXY($rx1, $lineY + 6);
$pdf->Cell($rx2 - $rx1, 4, u($delegado['puesto']), 0, 0, 'C');

// ─────────────── ccp (esquina inferior izquierda) ───────────────
$yCcp = $lineY + 20;
$pdf->SetFont('Arial', '', 6);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY($L, $yCcp);
$pdf->Cell(12, 4.5, u('CCP:'), 0, 0);
$xNombres = $L + 12;
foreach ($ccp as $i => $persona) {
    $pdf->SetXY($xNombres, $yCcp + $i * 4.5);
    $pdf->Cell(80, 4.5, u($persona), 0, 0);
}


$pdf->Output('I', 'Delegacion_' . $id . '.pdf');