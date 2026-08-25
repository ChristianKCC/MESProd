<?php
// php/certificado_pdf.php — CERTIFICADO DE ANÁLISIS (CoA) con FPDF
// v7.1 · sin sello · tipografía a escala de hoja · nombre = folio + clave + descripción
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once "../../conexion.php";
require_once "../../fpdf/fpdf.php";      // <-- ajusta a tu ruta de FPDF

// ====== CONFIG ======
$DB_APP = "TLX004MXDB";
$VISTA_CLAVES = "vwMXPRClaveMaquina";
$RUTA_LOGO = __DIR__ . "/../../img/imglogoprosede.png";
$RUTA_FIRMAS = __DIR__ . "/../../FirmaDigital/firmas/";
$FABRICANTE = 'KCM Prosede Planta Formulados';
$REFERENCIA = 'Análisis: Especificación interna.';
$PROCEDENCIA = 'México';
// ====================

$id = (int) ($_GET['id'] ?? 0);
$ibm = $_SESSION['ibm'] ?? $_SESSION['IBM'] ?? null;
if (!$id || !$ibm)
    die('Solicitud inválida');

$cx = new ClassConexion();
$conn = $cx->conexion($DB_APP);
if (!$conn)
    die('Sin conexión a BD');

function fx($conn, $sql, $p = [])
{
    $rs = sqlsrv_query($conn, $sql, $p);
    return $rs ? sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC) : null;
}
function fxAll($conn, $sql, $p = [])
{
    $rs = sqlsrv_query($conn, $sql, $p);
    $o = [];
    if ($rs)
        while ($r = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
            $o[] = $r;
    return $o;
}
function fecha($v, $f = 'd/m/Y')
{
    return ($v instanceof DateTime) ? $v->format($f) : ($v ?: '');
}

// Veredicto contra parámetros (misma lógica que el API y el front)
function veredictoParametro($valor, $p)
{
    if ($valor === null || $valor === '' || !$p)
        return '';
    $v = (float) $valor;
    $min = $p['minimo'];
    $obj = $p['objetivo'];
    $max = $p['maximo'];
    if ($min === null && $max === null && $obj === null)
        return '';
    if ($min !== null && $v < $min)
        return 'No cumple';
    if ($max !== null && $v > $max)
        return 'No cumple';
    if ($obj !== null && abs($v - $obj) < 0.00001)
        return 'Cumple';
    if ($obj !== null && $v < $obj)
        return 'Cumple';
    if ($obj !== null && $v > $obj)
        return 'Cumple';
    return 'Cumple';
}

// ---------- Datos ----------
$c = fx($conn, "SELECT * FROM tblMXPRCertificadoFR WHERE CER_id = ? AND CER_activo = 1", [$id]);
if (!$c)
    die('Certificado no encontrado');
// if ($c['CER_estatus'] !== 'APROBADO')
//     die('El certificado aún no está aprobado');
if (!in_array($c['CER_estatus'], ['APROBADO', 'RECHAZADO'], true))
    die('El certificado aún no ha sido validado');

$ins = fx($conn, "SELECT * FROM tblMXPRCertificadoInspeccionFR    WHERE INS_idCertificado = ?", [$id]);
$fis = fx($conn, "SELECT * FROM tblMXPRCertificadoFisicoquimicoFR WHERE FIS_idCertificado = ?", [$id]);

$vw = fx($conn, "SELECT TOP 1 Producto, Categoria, Descripcion_Articulo FROM $VISTA_CLAVES WHERE NoClave = ?", [$c['CER_clave']]);
$descRaw = $vw['Descripcion_Articulo'] ?? ($vw['Producto'] ?? $c['CER_producto']);
$descProducto = ucfirst(mb_strtolower((string) $descRaw, 'UTF-8'));

// Parámetros por clave (unidades + rangos)
$par = [];
foreach (
    fxAll(
        $conn,
        "SELECT PAR_variable, PAR_unidad, PAR_minimo, PAR_objetivo, PAR_maximo
         FROM tblMXPRCertificadoParametroFR
         WHERE LTRIM(RTRIM(PAR_clave)) = LTRIM(RTRIM(?)) AND PAR_activo = 1",
        [$c['CER_clave']]
    ) as $p
) {
    $par[strtoupper(trim($p['PAR_variable']))] = [
        'unidad' => $p['PAR_unidad'],
        'minimo' => $p['PAR_minimo'] !== null ? (float) $p['PAR_minimo'] : null,
        'objetivo' => $p['PAR_objetivo'] !== null ? (float) $p['PAR_objetivo'] : null,
        'maximo' => $p['PAR_maximo'] !== null ? (float) $p['PAR_maximo'] : null,
    ];
}
$uni = fn($v, $fb) => $par[$v]['unidad'] ?? $fb;

$evalFQ = [
    'DENSIDAD' => veredictoParametro($fis['FIS_densidad'] ?? null, $par['DENSIDAD'] ?? null),
    'VISCOSIDAD' => veredictoParametro($fis['FIS_viscosidad'] ?? null, $par['VISCOSIDAD'] ?? null),
    'PH' => veredictoParametro($fis['FIS_ph'] ?? null, $par['PH'] ?? null),
];

$mo = fxAll(
    $conn,
    "SELECT m.MOC_nombre, m.MOC_tipo, m.MOC_unidad, d.MOD_resultado
     FROM tblMXPRCertificadoMODetFR d
     JOIN tblMXPRCertificadoMOFR m ON m.MOC_id = d.MOD_idMO
     WHERE d.MOD_idCertificado = ? ORDER BY m.MOC_orden",
    [$id]
);

$defectos = fxAll(
    $conn,
    "SELECT f.DEF_atributo, f.DEF_nombre
     FROM tblMXPRCertificadoDefectoDetFR d
     JOIN tblMXPRCertificadoDefectoFR f ON f.DEF_id = d.DFD_idDefecto
     WHERE d.DFD_idCertificado = ? ORDER BY f.DEF_atributo, f.DEF_orden",
    [$id]
);

$firma = null;
if ($c['CER_ibmGerente']) {
    $g = fx($conn, "SELECT TOP 1 PER_noemp FROM tblMXPRCertificadoPerfilFR WHERE PER_ibm = ?", [$c['CER_ibmGerente']]);
    if (!empty($g['PER_noemp']) && file_exists($RUTA_FIRMAS . $g['PER_noemp'] . '.png'))
        $firma = $RUTA_FIRMAS . $g['PER_noemp'] . '.png';
}

// ¿Cumple?
$noCumple = false;
foreach (
    [
        $ins['INS_seguridad'] ?? '',
        $ins['INS_desempeno'] ?? '',
        $ins['INS_apariencia'] ?? '',
        $fis['FIS_aspecto'] ?? '',
        $fis['FIS_color'] ?? '',
        $fis['FIS_olor'] ?? '',
        $evalFQ['DENSIDAD'],
        $evalFQ['VISCOSIDAD'],
        $evalFQ['PH']
    ] as $r
) {
    if (stripos((string) $r, 'no') === 0)
        $noCumple = true;
}
foreach ($mo as $m) {
    if (stripos((string) $m['MOD_resultado'], 'presente') !== false)
        $noCumple = true;
}

// ================= PDF =================
class CoaPDF extends FPDF
{
    public $logo, $folio;

    function t($s)
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $s);
    }

    function Header()
    {
        if ($this->logo && file_exists($this->logo))
            $this->Image($this->logo, 16, 11, 48);

        $this->SetY(13);
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(20, 20, 20);
        // $this->Cell(0, 8, $this->t('Kimberly-Clark de México, S.A.B. de C.V.'), 0, 1, 'C');

        $this->Ln(3);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(31, 73, 125);
        $this->Cell(0, 7, $this->t('CERTIFICADO DE ANÁLISIS'), 0, 1, 'C');
        $this->SetTextColor(0);
        $this->Ln(1);
    }

    function Footer()
    {
        $this->SetY(-13);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(130);
        $this->Cell(0, 5, $this->t('Documento generado electrónicamente · ' . $this->folio), 0, 0, 'L');
        $this->Cell(0, 5, $this->t('Página ') . $this->PageNo(), 0, 0, 'R');
        $this->SetTextColor(0);
    }

    function Seccion($txt)
    {
        $this->Ln(2.5);
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(31, 73, 125);
        $this->Cell(0, 6, $this->t($txt), 0, 1, 'C');
        $this->SetTextColor(0);
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string) $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $ch = $s[$i];
            if ($ch == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($ch == ' ')
                $sep = $i;
            $l += $cw[$ch] ?? 0;
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    // Fila etiqueta/valor con alto automático
    function FilaDato($etq, $val, $wEtq, $wVal, $x0, $h = 5.6)
    {
        $this->SetFont('Arial', '', 7.5);
        $n = max($this->NbLines($wVal, $val), 1);
        $alto = $n * $h;
        $y = $this->GetY();

        $this->Rect($x0, $y, $wEtq, $alto);
        $this->SetXY($x0 + 1.5, $y + ($alto - $h) / 2);
        $this->Cell($wEtq - 3, $h, $this->t($etq), 0, 0, 'L');

        $this->Rect($x0 + $wEtq, $y, $wVal, $alto);
        $this->SetXY($x0 + $wEtq + 1.5, $y);
        $this->MultiCell($wVal - 3, $h, $this->t($val), 0, 'C');

        $this->SetXY($x0, $y + $alto);
    }

    function Th($cols, $anchos, $x0, $h = 4.8)
    {
        $this->SetX($x0);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(236, 239, 245);
        foreach ($cols as $i => $c)
            $this->Cell($anchos[$i], $h, $this->t($c), 1, 0, 'C', true);
        $this->Ln();
    }

    function Td($cols, $anchos, $x0, $aligns = [], $h = 4.2, $italic = false)
    {
        $this->SetX($x0);
        $this->SetFont('Arial', $italic ? 'I' : '', 7.5);
        foreach ($cols as $i => $c)
            $this->Cell($anchos[$i], $h, $this->t($c), 1, 0, $aligns[$i] ?? 'C');
        $this->Ln();
    }
}

$pdf = new CoaPDF('P', 'mm', 'Letter');
$pdf->logo = $RUTA_LOGO;
$pdf->folio = $c['CER_folio'];
$pdf->SetMargins(16, 11, 16);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

$X = 22;              // margen interno de los bloques
$ANCHO = 172;         // ancho de las tablas (Carta 216 - 44)

// ---------- Datos generales ----------
$wE = 62;
$wV = $ANCHO - $wE;
$pdf->SetX($X);
$pdf->FilaDato('Descripción de producto', $descProducto, $wE, $wV, $X);
$pdf->FilaDato('Procedencia', $PROCEDENCIA, $wE, $wV, $X);
$pdf->FilaDato('Fecha de Fabricación', fecha($ins['INS_fechaFabricacion'] ?? null), $wE, $wV, $X);
$pdf->FilaDato('Fecha de Emisión del CoA', fecha($c['CER_fechaEmision']), $wE, $wV, $X);
$pdf->FilaDato('Clave', $c['CER_clave'], $wE, $wV, $X);
$pdf->FilaDato('Lote', $c['CER_lote'], $wE, $wV, $X);
$pdf->FilaDato('Referencia', $REFERENCIA, $wE, $wV, $X);
$pdf->FilaDato('Fabricante', $FABRICANTE, $wE, $wV, $X);

// ---------- Control físico-químico ----------
$pdf->Seccion('CONTROL FÍSICO-QUÍMICO');
$a = [58, 44, 30, 40];
$pdf->Th(['Característica', 'Resultado', 'Unidades', 'Evaluación'], $a, $X);
$pdf->Td(['Aspecto', $fis['FIS_aspecto'] ?? '', '', ''], $a, $X, ['L']);
$pdf->Td(['Color', $fis['FIS_color'] ?? '', '', ''], $a, $X, ['L']);
$pdf->Td(['Olor', $fis['FIS_olor'] ?? '', '', ''], $a, $X, ['L']);
$pdf->Td(['Densidad', $fis['FIS_densidad'] ?? '', $uni('DENSIDAD', 'g/mL'), $evalFQ['DENSIDAD']], $a, $X, ['L']);
$pdf->Td(['Viscosidad', $fis['FIS_viscosidad'] ?? '', $uni('VISCOSIDAD', 'cps'), $evalFQ['VISCOSIDAD']], $a, $X, ['L']);
$pdf->Td(['pH', $fis['FIS_ph'] ?? '', $uni('PH', ''), $evalFQ['PH']], $a, $X, ['L']);

// ---------- Información de atributos ----------
$pdf->Seccion('INFORMACIÓN DE ATRIBUTOS');
$a = [58, 114];
$pdf->Th(['Atributo', 'Resultado'], $a, $X);
$pdf->Td(['Seguridad', $ins['INS_seguridad'] ?? ''], $a, $X, ['L']);
$pdf->Td(['Desempeño', $ins['INS_desempeno'] ?? ''], $a, $X, ['L']);
$pdf->Td(['Apariencia', $ins['INS_apariencia'] ?? ''], $a, $X, ['L']);

// Defectos, solo si los hubo (compactos: un renglón por atributo)
if (count($defectos)) {
    $agr = [];
    foreach ($defectos as $d)
        $agr[$d['DEF_atributo']][] = $d['DEF_nombre'];
    $pdf->Ln(1);
    $pdf->SetX($X);
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->Cell($ANCHO, 5.5, $pdf->t('Defectos detectados'), 1, 1, 'L');
    foreach ($agr as $atr => $lista) {
        $y = $pdf->GetY();
        $pdf->SetXY($X, $y);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(38, 5.5, $pdf->t(ucfirst(mb_strtolower($atr, 'UTF-8'))), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->MultiCell($ANCHO - 38, 5.5, $pdf->t(implode(' · ', $lista)), 0, 'L');
        $alto = $pdf->GetY() - $y;
        $pdf->Rect($X, $y, 38, $alto);
        $pdf->Rect($X + 38, $y, $ANCHO - 38, $alto);
        $pdf->SetXY($X, $y + $alto);
    }
}

// ---------- Recuento microbiológico ----------
$pdf->Seccion('RECUENTO MICROBIOLÓGICO');
$a = [110, 62];
$pdf->Th(['Determinación', 'Resultado'], $a, $X);
if (count($mo)) {
    foreach ($mo as $m) {
        $res = $m['MOD_resultado'];
        if ($m['MOC_tipo'] === 'RECUENTO' && $m['MOC_unidad'])
            $res .= ' ' . $m['MOC_unidad'];
        $pdf->Td([$m['MOC_nombre'], $res], $a, $X, ['L', 'C'], 6.2, $m['MOC_tipo'] === 'AUSENCIA');
    }
} else {
    $pdf->Td(['Sin resultados capturados', ''], $a, $X, ['L', 'C']);
}

// ---------- Conclusión ----------
$pdf->Seccion('CONCLUSIÓN');
$pdf->SetX($X);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(24, 6, $pdf->t('El producto'), 0, 0, 'L');
$pdf->SetFont('Arial', 'BI', 8);
$pdf->SetTextColor(31, 73, 125);
$pdf->Cell(26, 6, $pdf->t($noCumple ? 'No cumple' : 'cumple'), 0, 0, 'L');
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 6, $pdf->t('con la especificación'), 0, 1, 'L');

if (!empty($c['CER_observacionesGT'])) {
    $pdf->Ln(1.5);
    $pdf->SetX($X);
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->Cell(0, 5, $pdf->t('Observaciones de Gerencia Técnica:'), 0, 1, 'L');
    $pdf->SetX($X);
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->MultiCell($ANCHO, 4.6, $pdf->t($c['CER_observacionesGT']), 0, 'J');
}

// ---------- Aprobó + firma (sin sello) ----------
// ---------- Aprobó + firma (compacto) ----------
$pdf->Ln(5);
$y = $pdf->GetY();

// Solo salta de página si de verdad no cabe el bloque completo (32 mm)
if ($y + 32 > ($pdf->GetPageHeight() - 18)) {
    $pdf->AddPage();
    $y = $pdf->GetY();
}

$pdf->SetXY($X, $y);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell($ANCHO, 4.5, $pdf->t('Apróbo'), 0, 1, 'C');

// Firma más chica y pegada a la línea
if ($firma)
    $pdf->Image($firma, $X + ($ANCHO / 2) - 15, $y + 5, 30);

$pdf->Line($X + 50, $y + 19, $X + $ANCHO - 50, $y + 19);
$pdf->SetXY($X, $y + 19.5);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell($ANCHO, 4, $pdf->t($c['CER_nombreGerente']), 0, 2, 'C');
$pdf->SetFont('Arial', '', 8.5);
$pdf->Cell($ANCHO, 4, $pdf->t('Gerente Técnico'), 0, 2, 'C');
$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(130);
$pdf->Cell($ANCHO, 3.5, $pdf->t('Firmado: ' . fecha($c['CER_fechaFirma'], 'd/m/Y H:i')), 0, 2, 'C');
$pdf->SetTextColor(0);

// ---------- Nombre del archivo: folio + clave + descripción ----------
$limpia = function ($s) {
    $s = iconv('UTF-8', 'ASCII//TRANSLIT', (string) $s);
    $s = preg_replace('/[^A-Za-z0-9 \-_]/', '', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
};
$nombre = $limpia($c['CER_folio']) . ' - ' . $limpia($c['CER_clave']) . ' - ' . $limpia($descProducto);
$nombre = substr($nombre, 0, 120) . '.pdf';

$pdf->Output('I', $nombre);