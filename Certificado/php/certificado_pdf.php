<?php
// php/certificado_pdf.php — Certificado de Calidad FORM-63297 en FPDF
// Uso:  certificado_pdf.php?id=123        (descarga/inline en el navegador)
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once "../../conexion.php";
require_once "../../fpdf/fpdf.php";      // <-- ajusta a la ruta de FPDF de tu proyecto

// ====== CONFIG ======
$DB_APP = "TLX004MXDB";
$VISTA_CLAVES = "vwMXPRClaveMaquina";
$RUTA_LOGO = __DIR__ . "/../../img/imglogoprosede.png";        // logo del encabezado
$RUTA_FIRMAS = __DIR__ . "/../../FirmaDigital/firmas/";      // {noemp}.png


$ESPECS_DEFAULT = [
    'visMin' => 2,
    'visObj' => 2,
    'visMax' => 2,
    'phMin' => 2,
    'phObj' => 2,
    'phMax' => 2,
    'denMin' => 2,
    'denObj' => 2,
    'denMax' => 2,
    'aspecto' => 'Dato a definir de la base',
    'olor' => 'Dato a definir de la base',
];
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
function fecha($v, $f = 'd/m/Y')
{
    return ($v instanceof DateTime) ? $v->format($f) : ($v ?: '');
}

$c = fx($conn, "SELECT * FROM tblMXPRCertificadoFR WHERE CER_id = ? AND CER_activo = 1", [$id]);
if (!$c)
    die('Certificado no encontrado');
if ($c['CER_estatus'] !== 'APROBADO')
    die('El certificado aún no está aprobado');

$ins = fx($conn, "SELECT * FROM tblMXPRCertificadoInspeccionFR    WHERE INS_idCertificado = ?", [$id]);
$fis = fx($conn, "SELECT * FROM tblMXPRCertificadoFisicoquimicoFR WHERE FIS_idCertificado = ?", [$id]);
$mic = fx($conn, "SELECT * FROM tblMXPRCertificadoMicrobiologiaFR WHERE MIC_idCertificado = ?", [$id]);

// Los 3 datos del catálogo, SOLO para el PDF
$vw = fx($conn, "SELECT TOP 1 Producto, Categoria, Descripcion_Articulo FROM $VISTA_CLAVES WHERE NoClave = ?", [$c['CER_clave']]);
$pdfProducto = $vw['Producto'] ?? $c['CER_producto'];
$pdfCategoria = $vw['Categoria'] ?? '';
// $pdfPresentacion = $vw['Descripcion_Articulo'] ?? '';

$pdfPresentacionRaw = $vw['Descripcion_Articulo'] ?? '';
$pdfPresentacion = ucfirst(strtolower($pdfPresentacionRaw));

// $gen = [
//     ['Categoría del Producto', $pdfCategoria, 'Nombre del Producto', $pdfProducto],
//     ['Presentación', $pdfPresentacion, 'Nombre del Fabricante', $c['CER_fabricante']],
//     ['País de Origen', $c['CER_paisOrigen'], 'Fecha de Fabricación', fecha($ins['INS_fechaFabricacion'] ?? null)],
//     ['Fecha de Caducidad', fecha($ins['INS_fechaCaducidad'] ?? null) ?: 'No aplica', 'Número de Lote', $c['CER_lote']],
//     ['Clave KCM', $c['CER_clave'], 'Folio', $c['CER_folio']],
// ];


// Especificaciones
$e = fx($conn, "SELECT * FROM tblMXPRCertificadoEspecsFR WHERE ESP_clave = ? AND ESP_activo = 1", [$c['CER_clave']]);
$esp = $ESPECS_DEFAULT;
if ($e) {
    foreach (
        [
            'visMin' => 'ESP_visMin',
            'visObj' => 'ESP_visObj',
            'visMax' => 'ESP_visMax',
            'phMin' => 'ESP_phMin',
            'phObj' => 'ESP_phObj',
            'phMax' => 'ESP_phMax',
            'denMin' => 'ESP_denMin',
            'denObj' => 'ESP_denObj',
            'denMax' => 'ESP_denMax',
            'aspecto' => 'ESP_aspecto',
            'olor' => 'ESP_olor'
        ] as $k => $col
    ) {
        if (!empty($e[$col]))
            $esp[$k] = $e[$col];
    }
}

// Firma del gerente
$firma = null;
if ($c['CER_ibmGerente']) {
    $g = fx($conn, "SELECT TOP 1 PER_noemp FROM tblMXPRCertificadoPerfilFR WHERE PER_ibm = ?", [$c['CER_ibmGerente']]);
    if (!empty($g['PER_noemp']) && file_exists($RUTA_FIRMAS . $g['PER_noemp'] . '.png'))
        $firma = $RUTA_FIRMAS . $g['PER_noemp'] . '.png';
}

// ================= PDF =================
class CertPDF extends FPDF
{
    public $logo, $folio;

    // FPDF core usa cp1252: convertimos desde UTF-8
    function t($s)
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $s);
    }

    // function Header()
    // {
    //     if ($this->logo && file_exists($this->logo))
    //         $this->Image($this->logo, 12, 10, 32);
    //     $this->SetXY(48, 11);
    //     $this->SetFont('Arial', 'B', 12);
    //     $this->SetTextColor(0, 43, 117);
    //     $this->Cell(120, 6, $this->t('CERTIFICADO DE CALIDAD LÍQUIDOS Y FORMULADOS'), 0, 2, 'C');
    //     $this->SetFont('Arial', 'B', 11);
    //     // $this->Cell(120, 6, $this->t('LÍQUIDOS Y FORMULADOS'), 0, 2, 'C');
    //     $this->SetFont('Arial', '', 7);
    //     $this->SetTextColor(110, 110, 110);
    //     $this->Cell(120, 4, $this->t('FORM-63297'), 0, 2, 'C');
    //     $this->SetTextColor(0);
    //     $this->SetDrawColor(0, 43, 117);
    //     $this->SetLineWidth(0.6);
    //     $this->Line(12, 30, 199, 30);
    //     $this->SetLineWidth(0.2);
    //     $this->SetDrawColor(70, 70, 70);
    //     $this->SetY(34);
    // }

    function Header()
    {
        if ($this->logo && file_exists($this->logo))
            $this->Image($this->logo, 12, 10, 32);

        $this->SetXY(48, 11);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 43, 117);
        $this->Cell(120, 6, $this->t('CERTIFICADO DE CALIDAD LÍQUIDOS Y FORMULADOS'), 0, 2, 'C');

        $this->SetFont('Arial', 'B', 11);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(110, 110, 110);
        // $this->Cell(120, 4, $this->t('FORM-63297'), 0, 2, 'C');

        $this->SetTextColor(0);
        $this->SetDrawColor(0, 43, 117);
        $this->SetLineWidth(0.6);
        // Línea más cercana al título
        $this->Line(12, 26, 199, 26);

        $this->SetLineWidth(0.2);
        $this->SetDrawColor(70, 70, 70);
        // Ajusta la posición inicial del contenido
        $this->SetY(28);
    }


    function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(130);
        $this->Cell(0, 5, $this->t('Documento generado electrónicamente para folio ' . $this->folio), 0, 0, 'L');
        $this->Cell(0, 5, $this->t('Página ') . $this->PageNo(), 0, 0, 'R');
        $this->SetTextColor(0);
    }

    // Título de bloque
    function Bloque($txt)
    {
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(0, 43, 117);
        $this->Cell(0, 5, $this->t($txt), 0, 1, 'L');
        $this->SetTextColor(0);
    }

    // Fila de encabezado de tabla
    function Th($cols, $anchos)
    {
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(220, 230, 244);
        foreach ($cols as $i => $x)
            $this->Cell($anchos[$i], 6, $this->t($x), 1, 0, 'C', true);
        $this->Ln();
    }

    // Fila normal
    function Td($cols, $anchos, $aligns = null, $h = 5.5)
    {
        $this->SetFont('Arial', '', 7.5);
        foreach ($cols as $i => $x)
            $this->Cell($anchos[$i], $h, $this->t($x), 1, 0, $aligns[$i] ?? 'C');
        $this->Ln();
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
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

    function FilaGeneral($etq1, $val1, $etq2, $val2, $anchos, $hLinea = 4.5)
    {
        list($L1, $V1, $L2, $V2) = $anchos;

        // Alto necesario = la celda que más líneas ocupe
        $this->SetFont('Arial', '', 7.5);
        $n = max(
            $this->NbLines($V1, $val1),
            $this->NbLines($V2, $val2),
            $this->NbLines($L1, $etq1),
            $this->NbLines($L2, $etq2),
            1
        );
        $h = $n * $hLinea;

        // Salto de página si no cabe
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);

        $x = $this->GetX();
        $y = $this->GetY();

        $celda = function ($w, $txt, $bold) use (&$x, $y, $h, $hLinea) {
            $this->SetXY($x, $y);
            $this->SetFont('Arial', $bold ? 'B' : '', 7.5);
            if ($bold)
                $this->SetFillColor(244, 246, 251);
            $this->Rect($x, $y, $w, $h, $bold ? 'DF' : 'D');
            // Centrado vertical del texto dentro de la celda
            $lineas = $this->NbLines($w, $txt);
            $this->SetXY($x, $y + ($h - $lineas * $hLinea) / 2);
            $this->MultiCell($w, $hLinea, $this->t($txt), 0, 'L');
            $x += $w;
        };

        $celda($L1, $etq1, true);
        $celda($V1, $val1, false);
        $celda($L2, $etq2, true);
        $celda($V2, $val2, false);

        $this->SetXY($this->lMargin, $y + $h);
    }
}

$pdf = new CertPDF('P', 'mm', 'Letter');
$pdf->logo = $RUTA_LOGO;
$pdf->folio = $c['CER_folio'];
$pdf->SetMargins(12, 10, 12);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// ---- Fecha de emisión ----
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(0, 5, $pdf->t('FECHA DE EMISIÓN: ') . fecha($c['CER_fechaEmision']), 0, 1, 'R');

// ---- Datos generales (2 columnas de etiqueta/valor) ----
$pdf->Bloque('Información del producto');

$anchos = [34, 72, 34, 47];

$L = 38;
$V = 55.5;  // 38+55.5+38+55.5 = 187 (ancho útil)
$pdf->FilaGeneral('Categoría del Producto', $pdfCategoria, 'Nombre del Producto', $pdfProducto, $anchos);
$pdf->FilaGeneral('Presentación', $pdfPresentacion, 'Nombre del Fabricante', $c['CER_fabricante'], $anchos);
$pdf->FilaGeneral('País de Origen', $c['CER_paisOrigen'], 'Fecha de Fabricación', fecha($ins['INS_fechaFabricacion'] ?? null), $anchos);
$pdf->FilaGeneral('Fecha de Caducidad', fecha($ins['INS_fechaCaducidad'] ?? null) ?: 'No aplica', 'Número de Lote', $c['CER_lote'], $anchos);
$pdf->FilaGeneral('Clave KCM', $c['CER_clave'], '', '', $anchos);
// foreach ($gen as $g) {
//     $pdf->SetFont('Arial', 'B', 7.5);
//     $pdf->SetFillColor(244, 246, 251);
//     $pdf->Cell($L, 6, $pdf->t($g[0]), 1, 0, 'L', true);
//     $pdf->SetFont('Arial', '', 7.5);
//     $pdf->Cell($V, 6, $pdf->t($g[1]), 1, 0, 'L');
//     $pdf->SetFont('Arial', 'B', 7.5);
//     $pdf->Cell($L, 6, $pdf->t($g[2]), 1, 0, 'L', true);
//     $pdf->SetFont('Arial', '', 7.5);
//     $pdf->Cell($V, 6, $pdf->t($g[3]), 1, 1, 'L');
// }

// ---- Fisicoquímicas ----
$pdf->Bloque('Variables Fisicoquímicas');
$a = [40, 27, 22, 24, 24, 24, 26];
$pdf->Th(['Variable', 'Método', 'Unidad', 'Mínimo', 'Objetivo', 'Máximo', 'Resultado'], $a);
$pdf->Td(['Viscosidad', 'TTM-00557', 'cps', $esp['visMin'], $esp['visObj'], $esp['visMax'], $fis['FIS_viscosidad'] ?? ''], $a, ['L']);
$pdf->Td(['pH', 'TTM-00558', 'pH', $esp['phMin'], $esp['phObj'], $esp['phMax'], $fis['FIS_ph'] ?? ''], $a, ['L']);
$pdf->Td(['Densidad', 'TTM-00559', 'g / mL', $esp['denMin'], $esp['denObj'], $esp['denMax'], $fis['FIS_densidad'] ?? ''], $a, ['L']);

// ---- Organolépticas ----
$pdf->Bloque('Especificaciones Organolépticas');
$a = [50, 107, 30];
$pdf->Th(['Característica', 'Especificación', 'Resultado'], $a);
$pdf->Td(['Aspecto / Color', $esp['aspecto'], $fis['FIS_aspectoColor'] ?? ''], $a, ['L', 'L', 'C']);
$pdf->Td(['Olor', $esp['olor'], $fis['FIS_olor'] ?? ''], $a, ['L', 'L', 'C']);

// ---- Microbiológicas ----
$pdf->Bloque('Especificaciones Microbiológicas');
$a = [88, 34, 35, 30];
$pdf->Th(['Determinación', 'Especificación', 'Técnica', 'Resultado'], $a);
$pdf->Td(['Recuento total de Mesófilos Aerobios (TAMC)', "\xE2\x89\xA4 100 UFC / g", 'TTM-00554', $mic['MIC_tamc'] ?? ''], $a, ['L', 'C', 'C', 'C']);
$pdf->Td(['Recuento total de Hongos y Levaduras (TYMC)', "\xE2\x89\xA4 10 UFC / g", 'TTM-00556', $mic['MIC_tymc'] ?? ''], $a, ['L', 'C', 'C', 'C']);
// Fila de patógenos: texto largo en MultiCell alineado a mano
$yIni = $pdf->GetY();
$xIni = $pdf->GetX();
$pdf->SetFont('Arial', '', 6);
$patog = 'Pseudomonas aeruginosa, Escherichia coli, Salmonella spp., Coliformes fecales y totales, Burkholderia cepacia, '
    . 'Staphylococcus aureus, Aspergillus brasiliensis, Candida albicans, Pluralibacter gergoviae';
$pdf->MultiCell($a[0], 4, $pdf->t($patog), 1, 'L');
$hFila = $pdf->GetY() - $yIni;
$pdf->SetXY($xIni + $a[0], $yIni);
$pdf->SetFont('Arial', '', 7.5);
$pdf->Cell($a[1], $hFila, $pdf->t('Ausencia'), 1, 0, 'C');
$pdf->Cell($a[2], $hFila, '', 1, 0, 'C');
$pdf->Cell($a[3], $hFila, $pdf->t($mic['MIC_patogenos'] ?? ''), 1, 1, 'C');

// ---- Atributos ----
$pdf->Bloque('Evaluación de atributos');
$a = [88, 69, 30];
$pdf->Th(['Atributo', 'Especificación (AQL)', 'Resultado'], $a);
$pdf->Td(['Seguridad', '< 0.025', $ins['INS_seguridad'] ?? ''], $a, ['L', 'C', 'C']);
$pdf->Td(['Desempeño', '< 2.5', $ins['INS_desempeno'] ?? ''], $a, ['L', 'C', 'C']);
$pdf->Td(['Apariencia', '< 4.0', $ins['INS_apariencia'] ?? ''], $a, ['L', 'C', 'C']);

// ---- Conclusión ----
$pdf->Bloque('Conclusión');
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(0, 4.5, $pdf->t($c['CER_conclusion']), 0, 'J');
if (!empty($c['CER_observacionesGT'])) {
    $pdf->Ln(1);
    $pdf->SetFont('Arial', 'B', 7.5);
    $pdf->Cell(0, 4, $pdf->t('Observaciones de Gerencia Técnica:'), 0, 1);
    $pdf->SetFont('Arial', '', 7.5);
    $pdf->MultiCell(0, 4, $pdf->t($c['CER_observacionesGT']), 0, 'J');
}

// ---- Pie: firma a la izquierda + sello a la derecha ----
$pdf->Ln(4);
$y = $pdf->GetY();
$y += 10; // baja un poco el bloque
$pdf->SetY($y);
$y = $pdf->GetY();

// Firma (izquierda)
$pdf->SetDrawColor(150);

$pdf->SetXY(12, $y);
$pdf->SetFont('Arial', 'B', 7.5);
$pdf->Cell(88, 5, $pdf->t('KIMBERLY CLARK DE MÉXICO S.A.B DE C.V.'), 0, 1, 'C');
if ($firma)
    $pdf->Image($firma, 35, $y + 10, 50); // firma dentro del recuadro
$pdf->Line(15, $y + 28, 95, $y + 28);
$pdf->SetXY(12, $y + 28.5);
$pdf->SetFont('Arial', '', 7.5);
$pdf->Cell(88, 4, $pdf->t($c['CER_nombreGerente']), 0, 2, 'C');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(88, 4, $pdf->t('Gerencia Técnica'), 0, 2, 'C');
$pdf->Cell(88, 4, $pdf->t('Firmado: ' . fecha($c['CER_fechaFirma'], 'd/m/Y H:i')), 0, 2, 'C');

// Sello (derecha)
$pdf->SetDrawColor(150);
$pdf->Rect(105, $y, 94, 38); // recuadro para el sello
$pdf->SetXY(105, $y + 12);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(120);
$pdf->Cell(94, 5, $pdf->t('CONTROL DE CALIDAD'), 0, 2, 'C');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(94, 4, $pdf->t('Colocación de sello'), 0, 2, 'C');
$pdf->Cell(94, 4, $pdf->t('de acuerdo al estatus'), 0, 2, 'C');
$pdf->SetTextColor(0);

$pdf->Output('I', 'Certificado_' . $c['CER_folio'] . '.pdf');