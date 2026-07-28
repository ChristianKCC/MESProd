<?php
/* ============================================================================
   ENDPOINT: Exportar un RARR completo a PDF (FPDF)
   GET: ?idEquipo=PAÑAL-MP25-DBA-01
   Estructura: portada/resumen · una ficha por escenario · genéricos · cierre
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';
require_once __DIR__ . '/../../../fpdf/fpdf.php';

/* Acentos: FPDF core trabaja en Latin-1 */
function t($s)
{
    return iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $s);
}

/* Color por nivel de riesgo */
function colorNivel($nivel)
{
    switch ($nivel) {
        case 'Aceptable':
            return [35, 146, 61];
        case 'Bajo':
            return [232, 195, 26];
        case 'Alto':
            return [240, 147, 13];
        case 'Inaceptable':
            return [220, 53, 69];
        default:
            return [150, 150, 150];
    }
}
function textoNivel($nivel)
{
    $m = [
        'Aceptable' => 'RIESGO ACEPTABLE',
        'Bajo' => 'RIESGO BAJO',
        'Alto' => 'RIESGO ALTO',
        'Inaceptable' => 'RIESGO INACEPTABLE'
    ];
    return $m[$nivel] ?? '-';
}
function mensajeNivel($nivel)
{
    switch ($nivel) {
        case 'Inaceptable':
            return 'Inmediato: el trabajo o tarea debe ser suspendido hasta que el riesgo sea reducido por debajo de este nivel.';
        case 'Alto':
            return 'Debe contar con un plan desarrollado para eliminar y/o minimizar el riesgo, y definir el proceso de reducción en un plazo de 15 días.';
        case 'Bajo':
            return 'Debe contar con planes para mitigar y reducir el riesgo en un plazo de 45 días. La implementación debe completarse en un plazo de 90 días.';
        case 'Aceptable':
            return 'Este nivel de riesgo puede ser tolerable. Las acciones para reducir el riesgo residual quedan a discreción de la planta.';
        default:
            return '';
    }
}
function fFecha($f)
{
    return $f instanceof DateTime ? $f->format('d/m/Y') : '-';
}
function nn($v, $def = '-')
{
    $v = trim((string) $v);
    return ($v === '' || $v === '-') ? $def : $v;
}

/* ============================================================================
   CLASE PDF
   ============================================================================ */
class PDF_RARR extends FPDF
{
    public $idEquipo = '';
    public $anchoUtil = 192;

    function Header()
    {
        $logo = __DIR__ . '/../../img/imglogoprosede.png';
        if (file_exists($logo)) {
            $this->Image($logo, 12, 8, 26);
        }
        $this->SetXY(42, 9);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(0, 32, 96);
        $this->MultiCell(162, 5, t('Análisis de Riesgos Potenciales generados por la Maquinaria y Equipos'), 0, 'L');
        $this->SetX(42);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(95, 95, 95);
        $this->Cell(0, 4, t('Revisión ' . date('Y') . '   ·   Ref. NOM-004-STPS-2020   ·   ISO 12100'), 0, 1);

        $this->SetDrawColor(0, 32, 96);
        $this->SetLineWidth(0.6);
        $this->Line(12, 26, 204, 26);
        $this->SetLineWidth(0.2);
        $this->SetTextColor(0, 0, 0);
        $this->SetY(31);
    }

    function Footer()
    {
        $this->SetY(-14);
        $this->SetDrawColor(190, 190, 190);
        $this->Line(12, $this->GetY(), 204, $this->GetY());
        $this->SetY(-12);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(110, 110, 110);
        $this->Cell(96, 6, t($this->idEquipo), 0, 0, 'L');
        $this->Cell(96, 6, t('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
    }

    /* Banda de sección a todo lo ancho */
    function banda($texto, $rgb = [0, 32, 96], $alto = 7, $tam = 10)
    {
        $this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', $tam);
        $this->Cell($this->anchoUtil, $alto, '  ' . t($texto), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(1.5);
    }

    /* Subtítulo gris dentro de un bloque */
    function subBanda($texto)
    {
        $this->SetFillColor(235, 237, 240);
        $this->SetTextColor(50, 50, 50);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($this->anchoUtil, 5.5, '  ' . t($texto), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(1);
    }

    /* Etiqueta + valor con salto automático */
    function fila($label, $valor, $ancho = null, $anchoLabel = 32)
    {
        $ancho = $ancho ?? $this->anchoUtil;
        $x = $this->GetX();
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(70, 70, 70);
        $this->Cell($anchoLabel, 4.6, t($label), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell($ancho - $anchoLabel, 4.6, t(nn($valor)), 0, 'L');
        $this->SetX($x);
    }

    /* Caja de puntaje + nivel, coloreada */
    function cajaNivel($puntaje, $nivel, $ancho = 92, $formula = '')
    {
        $c = colorNivel($nivel);
        $x = $this->GetX();
        $y = $this->GetY();

        if ($formula !== '') {
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(70, 70, 70);
            $this->Cell(60, 8, t($formula), 0, 0, 'L');
            $this->SetTextColor(0, 0, 0);
        }

        $this->SetFillColor($c[0], $c[1], $c[2]);
        $this->SetTextColor($nivel === 'Bajo' ? 0 : 255, $nivel === 'Bajo' ? 0 : 255, $nivel === 'Bajo' ? 0 : 255);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($ancho, 8, t(($puntaje === null ? '—' : $puntaje) . '   ' . textoNivel($nivel)), 0, 1, 'C', true);
        $this->SetTextColor(0, 0, 0);
        $this->SetX($x);
        $this->Ln(1);
    }

    /* Barra de progreso */
    function barra($pct, $ancho = 60, $alto = 4)
    {
        $pct = max(0, min(100, (int) $pct));
        $x = $this->GetX();
        $y = $this->GetY() + 1;
        $this->SetFillColor(215, 218, 222);
        $this->Rect($x, $y, $ancho, $alto, 'F');
        $this->SetFillColor(37, 99, 235);
        if ($pct > 0) {
            $this->Rect($x, $y, $ancho * $pct / 100, $alto, 'F');
        }
        $this->SetXY($x + $ancho + 3, $y - 1);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(15, 6, $pct . '%', 0, 1, 'L');
    }

    /* Imagen desde BLOB: devuelve la altura usada */
    function imagenBlob($blob, $x, $y, $ancho)
    {
        if (!$blob) {
            $this->SetDrawColor(200, 200, 200);
            $this->SetFillColor(248, 248, 248);
            $this->Rect($x, $y, $ancho, 28, 'DF');
            $this->SetXY($x, $y + 11);
            $this->SetFont('Arial', 'I', 7.5);
            $this->SetTextColor(140, 140, 140);
            $this->Cell($ancho, 5, t('Sin evidencia fotográfica'), 0, 0, 'C');
            $this->SetTextColor(0, 0, 0);
            return 28;
        }
        $info = @getimagesizefromstring($blob);
        if ($info === false) {
            return 0;
        }
        $alto = $ancho * $info[1] / $info[0];
        if ($alto > 55) {
            $alto = 55;
            $ancho = $alto * $info[0] / $info[1];
        }
        $tipo = ($info['mime'] === 'image/png') ? 'PNG' : 'JPG';
        $tmp = tempnam(sys_get_temp_dir(), 'rarr');
        file_put_contents($tmp, $blob);
        $this->Image($tmp, $x, $y, $ancho, $alto, $tipo);
        @unlink($tmp);
        $this->SetDrawColor(190, 190, 190);
        $this->Rect($x, $y, $ancho, $alto);
        return $alto;
    }
}

/* ============================================================================
   DATOS
   ============================================================================ */
$idEquipo = trim($_GET['idEquipo'] ?? '');
if ($idEquipo === '') {
    header('Content-Type: text/plain; charset=utf-8');
    exit('ID de equipo no válido');
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

/* Maestro */
$stmt = sqlsrv_query(
    $conn,
    "SELECT TOP 1 IdRARR, IdEquipo, RTRIM(Maquina) AS Maquina,
            ISNULL(SeccionEquipo,'-') AS SeccionEquipo, RTRIM(Departamento) AS Departamento,
            ISNULL(Estatus,'Pendiente') AS Estatus,
            MarcadorPuro, MarcadorGuardas, MarcadorIngenieria,
            FechaCreacion, FechaActualizacion, FechaConclusion
     FROM TLX002MXDB.dbo.Seg_RARR WHERE IdEquipo = ? ORDER BY IdRARR DESC",
    [$idEquipo]
);
$rarr = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
if (!$rarr) {
    header('Content-Type: text/plain; charset=utf-8');
    exit("No hay RARR registrado para $idEquipo");
}
$idRARR = (int) $rarr['IdRARR'];

/* Escenarios (propios + genéricos) */
$stmt = sqlsrv_query(
    $conn,
    "SELECT e.IdEscenario, e.EsGenerico,
            ISNULL(cp.Descripcion,'-') AS Categoria, e.EscenarioRiesgo,
            ISNULL(s.Descripcion,'-') AS Severidad, s.Valor AS ValSeveridad,
            ISNULL(p.Descripcion,'-') AS Probabilidad, p.Valor AS ValProbabilidad,
            ISNULL(f.Descripcion,'-') AS Frecuencia, f.Valor AS ValFrecuencia,
            ISNULL(e.PersonalExpuesto,'-') AS Personas, per.Valor AS ValPersonas,
            e.Calificacion, e.NivelRiesgo,
            ISNULL(cg.Descripcion,'-') AS CriterioGuarda, cg.Valor AS ValCriterio,
            e.CalificacionP2, e.NivelRiesgoP2,
            ISNULL(mm.Descripcion,'-') AS Medida, mm.Valor AS ValMedida,
            e.CalificacionP3, e.NivelRiesgoP3
     FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo e
     LEFT JOIN TLX002MXDB.dbo.Seg_CatCategoriaPeligro cp ON cp.IdCategoria = e.IdCategoriaPeligro
     LEFT JOIN TLX002MXDB.dbo.Seg_CatSeveridad     s  ON s.IdSeveridad    = e.IdSeveridad
     LEFT JOIN TLX002MXDB.dbo.Seg_CatProbabilidad  p  ON p.IdProbabilidad = e.IdProbabilidad
     LEFT JOIN TLX002MXDB.dbo.Seg_CatFrecuencia    f  ON f.IdFrecuencia   = e.IdFrecuencia
     LEFT JOIN TLX002MXDB.dbo.Seg_CatPersonasExpuestas per ON per.IdPersonas = e.IdPersonas
     LEFT JOIN TLX002MXDB.dbo.Seg_CatCriterioGuarda   cg ON cg.IdCriterio = e.IdCriterioGuarda
     LEFT JOIN TLX002MXDB.dbo.Seg_CatMedidaMitigacion mm ON mm.IdMedida   = e.IdMedidaMitigacion
     WHERE e.IdRARR = ? AND e.Activo = 1
     ORDER BY e.EsGenerico, e.IdEscenario",
    [$idRARR]
);
$propios = [];
$genericos = [];
while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if ((int) $f['EsGenerico'] === 1)
        $genericos[] = $f;
    else
        $propios[] = $f;
}

/* Evaluación por escenario */
$stmt = sqlsrv_query(
    $conn,
    "SELECT ev.IdEscenario, ev.DescripcionHallazgo,
            ISNULL(sf.Descripcion, ISNULL(ev.NivelRiesgoActual,'-')) AS SeguridadFuncional,
            ISNULL(ev.AccionesPropuestas,'-') AS Contencion, ev.PorcentajeAvance,
            ISNULL(ev.MedidasMitigacion,'-') AS MedidasAdicionales,
            ISNULL(ev.NombreResponsable,'-') AS Responsable,
            ISNULL(ev.IbmResponsable,'') AS Ibm, ev.FechaCompromiso
     FROM TLX002MXDB.dbo.Seg_EvaluacionRARR ev
     LEFT JOIN TLX002MXDB.dbo.Seg_CatSeguridadFuncional sf ON sf.IdSeguridadFuncional = ev.IdSeguridadFuncional
     WHERE ev.IdRARR = ? AND ev.Activo = 1",
    [$idRARR]
);
$evalPorEsc = [];
while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
    $evalPorEsc[(int) $f['IdEscenario']] = $f;

/* Controles de ingeniería por escenario */
$stmt = sqlsrv_query(
    $conn,
    "SELECT a.IdEscenario, a.Descripcion, a.FechaImplementacion, a.InversionEstimada,
            ISNULL(es.Descripcion,'Pendiente') AS Estatus
     FROM TLX002MXDB.dbo.Seg_AccionMejora a
     LEFT JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = a.IdEstatus
     WHERE a.IdRARR = ? AND a.Activo = 1",
    [$idRARR]
);
$controlPorEsc = [];
$inversionTotal = 0;
while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $controlPorEsc[(int) $f['IdEscenario']] = $f;
    $inversionTotal += (float) ($f['InversionEstimada'] ?? 0);
}

/* Plan de acción por escenario */
$stmt = sqlsrv_query(
    $conn,
    "SELECT s.IdEscenario, s.Descripcion, ISNULL(s.Responsable,'-') AS Responsable,
            ISNULL(s.IbmResponsable,'') AS Ibm, s.FechaImplementacion,
            ISNULL(es.Descripcion,'Pendiente') AS Estatus
     FROM TLX002MXDB.dbo.Seg_SeguimientoControl s
     LEFT JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = s.IdEstatus
     WHERE s.IdRARR = ? AND s.Activo = 1",
    [$idRARR]
);
$planPorEsc = [];
while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
    $planPorEsc[(int) $f['IdEscenario']] = $f;

/* Imagen por escenario y paso */
function imagenEsc($conn, $idEscenario, $paso)
{
    $stmt = sqlsrv_query(
        $conn,
        "SELECT TOP 1 Imagen FROM TLX002MXDB.dbo.Seg_ImagenRARR
         WHERE IdEscenario = ? AND Paso = ? AND Activo = 1 ORDER BY IdImagen DESC",
        [$idEscenario, $paso]
    );
    $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
    return $row ? $row['Imagen'] : null;
}

/* Conteos */
function contar($lista, $campo)
{
    $c = ['Aceptable' => 0, 'Bajo' => 0, 'Alto' => 0, 'Inaceptable' => 0];
    foreach ($lista as $f) {
        $n = $f[$campo] ?? null;
        if (isset($c[$n]))
            $c[$n]++;
    }
    return $c;
}
$todos = array_merge($propios, $genericos);
$cont1 = contar($todos, 'NivelRiesgo');
$cont2 = contar($todos, 'NivelRiesgoP2');
$cont3 = contar($todos, 'NivelRiesgoP3');

$avancePanel = 0;
if (count($evalPorEsc) > 0) {
    $avancePanel = round(array_sum(array_map(fn($x) => (int) $x['PorcentajeAvance'], $evalPorEsc)) / count($evalPorEsc));
}

/* ============================================================================
   RENDER
   ============================================================================ */
$pdf = new PDF_RARR('P', 'mm', 'Letter');
$pdf->idEquipo = $rarr['IdEquipo'];
$pdf->AliasNbPages();
$pdf->SetMargins(12, 31, 12);
$pdf->SetAutoPageBreak(true, 18);

/* ---------- PÁGINA 1: RESUMEN ---------- */
$pdf->AddPage();
$pdf->banda('DATOS DEL EQUIPO');

$pdf->SetFont('Arial', '', 9);
$y = $pdf->GetY();
$pdf->SetXY(12, $y);
$pdf->fila('ID Equipo', $rarr['IdEquipo'], 92, 26);
$pdf->fila('Máquina', $rarr['Maquina'], 92, 26);
$pdf->fila('Sección', $rarr['SeccionEquipo'], 92, 26);
$yIzq = $pdf->GetY();
$pdf->SetXY(108, $y);
$pdf->fila('Departamento', $rarr['Departamento'], 96, 32);
$pdf->SetX(108);
$pdf->fila('Estatus', $rarr['Estatus'], 96, 32);
$pdf->SetX(108);
$pdf->fila('Actualizado', fFecha($rarr['FechaActualizacion'] ?? $rarr['FechaCreacion']), 96, 32);
$pdf->SetY(max($yIzq, $pdf->GetY()) + 3);

/* Marcadores */
$pdf->banda('MARCADORES DE RIESGO');
$marc = [
    ['Paso 1 · Peligro Puro', (float) $rarr['MarcadorPuro'], clasificarNivelRiesgo((float) $rarr['MarcadorPuro'])],
    ['Paso 2 · Con Guardas Actuales', (float) $rarr['MarcadorGuardas'], clasificarNivelRiesgo((float) $rarr['MarcadorGuardas'])],
    ['Paso 3 · Con Ingeniería', (float) $rarr['MarcadorIngenieria'], clasificarNivelRiesgo((float) $rarr['MarcadorIngenieria'])],
];
$x = 12;
$y = $pdf->GetY();
foreach ($marc as $m) {
    $c = colorNivel($m[2]);
    $pdf->SetFillColor($c[0], $c[1], $c[2]);
    $pdf->Rect($x, $y, 61, 20, 'F');
    $pdf->SetXY($x, $y + 2);
    $pdf->SetTextColor($m[2] === 'Bajo' ? 0 : 255, $m[2] === 'Bajo' ? 0 : 255, $m[2] === 'Bajo' ? 0 : 255);
    $pdf->SetFont('Arial', 'B', 17);
    $pdf->Cell(61, 9, round($m[1], 2), 0, 2, 'C');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(61, 4, t(textoNivel($m[2])), 0, 2, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 7.5);
    $pdf->SetXY($x, $y + 20.5);
    $pdf->Cell(61, 4, t($m[0]), 0, 0, 'C');
    $x += 65.5;
}
$pdf->SetY($y + 27);

/* Conteo por nivel */
$pdf->banda('CONTEO DE ESCENARIOS POR NIVEL');
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(235, 237, 240);
foreach (['', 'Aceptable', 'Bajo', 'Alto', 'Inaceptable', 'Total'] as $i => $h) {
    $pdf->Cell($i === 0 ? 42 : 30, 6, t($h), 1, 0, 'C', true);
}
$pdf->Ln();
$pdf->SetFont('Arial', '', 8);
foreach ([['Paso 1 · Peligro Puro', $cont1], ['Paso 2 · Con Guardas', $cont2], ['Paso 3 · Con Ingeniería', $cont3]] as $fila) {
    $c = $fila[1];
    $tot = $c['Aceptable'] + $c['Bajo'] + $c['Alto'] + $c['Inaceptable'];
    $pdf->Cell(42, 6, t($fila[0]), 1, 0, 'L');
    $pdf->Cell(30, 6, $c['Aceptable'], 1, 0, 'C');
    $pdf->Cell(30, 6, $c['Bajo'], 1, 0, 'C');
    $pdf->Cell(30, 6, $c['Alto'], 1, 0, 'C');
    $pdf->Cell(30, 6, $c['Inaceptable'], 1, 0, 'C');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(30, 6, $tot, 1, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
}
$pdf->Ln(3);

/* Avance e inversión */
$pdf->banda('IMPLEMENTACIÓN Y COSTOS');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(70, 6, t('Avance de controles administrativos'), 0, 0, 'L');
$pdf->barra($avancePanel, 60);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(70, 6, t('Inversión estimada total'), 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 6, t('$ ' . number_format($inversionTotal, 2)), 0, 1, 'L');
$pdf->Ln(3);

/* Índice de escenarios */
$pdf->banda('ÍNDICE DE ESCENARIOS');
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(235, 237, 240);
$pdf->Cell(10, 6, '#', 1, 0, 'C', true);
$pdf->Cell(92, 6, t('Escenario de riesgo'), 1, 0, 'L', true);
$pdf->Cell(25, 6, t('Paso 1'), 1, 0, 'C', true);
$pdf->Cell(25, 6, t('Paso 2'), 1, 0, 'C', true);
$pdf->Cell(25, 6, t('Paso 3'), 1, 0, 'C', true);
$pdf->Cell(15, 6, t('Pág.'), 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 8);
foreach ($propios as $i => $e) {
    $txt = $e['EscenarioRiesgo'];
    if (mb_strlen($txt) > 62)
        $txt = mb_substr($txt, 0, 60) . '…';
    $pdf->Cell(10, 5.5, $i + 1, 1, 0, 'C');
    $pdf->Cell(92, 5.5, t($txt), 1, 0, 'L');
    $pdf->Cell(25, 5.5, $e['Calificacion'], 1, 0, 'C');
    $pdf->Cell(25, 5.5, $e['CalificacionP2'] ?? '-', 1, 0, 'C');
    $pdf->Cell(25, 5.5, $e['CalificacionP3'] ?? '-', 1, 0, 'C');
    $pdf->Cell(15, 5.5, $i + 2, 1, 1, 'C');
}

/* ---------- PÁGINAS 2..N: UNA FICHA POR ESCENARIO ---------- */
$totalEsc = count($propios);
foreach ($propios as $idx => $e) {
    $idEsc = (int) $e['IdEscenario'];
    $ev = $evalPorEsc[$idEsc] ?? null;
    $ct = $controlPorEsc[$idEsc] ?? null;
    $pl = $planPorEsc[$idEsc] ?? null;

    $pdf->AddPage();
    $pdf->SetFillColor(31, 61, 124);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(192, 7, '  ' . t('ESCENARIO ' . ($idx + 1) . ' DE ' . $totalEsc), 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    /* ---- PASO 1 ---- */
    $pdf->banda('PASO 1  ·  IDENTIFICACIÓN DEL PELIGRO ("Peligro Puro")', [0, 0, 0], 6, 9);
    $yIni = $pdf->GetY();
    $pdf->SetXY(12, $yIni);
    $pdf->fila('Categoría', $e['Categoria'], 128);
    $pdf->fila('Escenario', $e['EscenarioRiesgo'], 128);
    $pdf->fila('Severidad', $e['Severidad'] . '  (' . (float) $e['ValSeveridad'] . ')', 128);
    $pdf->fila('Probabilidad', $e['Probabilidad'] . '  (' . (float) $e['ValProbabilidad'] . ')', 128);
    $pdf->fila('Frecuencia', $e['Frecuencia'] . '  (' . (float) $e['ValFrecuencia'] . ')', 128);
    $pdf->fila('N. de Personas', $e['Personas'] . '  (' . (float) $e['ValPersonas'] . ')', 128);
    $yTxt = $pdf->GetY();
    $altoImg = $pdf->imagenBlob(imagenEsc($conn, $idEsc, 1), 146, $yIni, 58);
    $pdf->SetY(max($yTxt, $yIni + $altoImg) + 2);

    $form = (float) $e['ValSeveridad'] . ' × ' . (float) $e['ValProbabilidad'] . ' × '
        . (float) $e['ValFrecuencia'] . ' × ' . (float) $e['ValPersonas'] . '  =';
    $pdf->cajaNivel((float) $e['Calificacion'], $e['NivelRiesgo'], 130, $form);
    $pdf->Ln(1);

    /* ---- PASO 2 ---- */
    $pdf->banda('PASO 2  ·  EVALUACIÓN DE LA PROTECCIÓN ACTUAL', [0, 0, 0], 6, 9);
    $pdf->fila('Guarda actual', $ev['DescripcionHallazgo'] ?? '-');
    $pdf->fila('Criterio', $e['CriterioGuarda'] . '  (' . (float) $e['ValCriterio'] . ')');
    $pdf->fila('Desempeño (PL)', $ev['SeguridadFuncional'] ?? '-');
    $pdf->Ln(0.5);
    $form2 = (float) $e['ValSeveridad'] . ' × ' . (float) $e['ValCriterio'] . ' × '
        . (float) $e['ValFrecuencia'] . ' × ' . (float) $e['ValPersonas'] . '  =';
    $pdf->cajaNivel($e['CalificacionP2'] !== null ? (float) $e['CalificacionP2'] : null, $e['NivelRiesgoP2'], 130, $form2);
    $pdf->SetFont('Arial', 'I', 7.5);
    $pdf->SetTextColor(90, 90, 90);
    $pdf->MultiCell(192, 4, t('Acción requerida: ' . mensajeNivel($e['NivelRiesgoP2'])), 0, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(1.5);

    $pdf->subBanda('CONTENCIÓN  ·  controles administrativos y contención física temporal');
    $pdf->fila('Acciones', $ev['Contencion'] ?? '-');
    $pdf->fila('Medidas adicionales', $ev['MedidasAdicionales'] ?? '-', null, 38);
    $pdf->fila('Responsable', ($ev['Responsable'] ?? '-') . (empty($ev['Ibm']) ? '' : '  (IBM ' . $ev['Ibm'] . ')'));
    $pdf->fila('Fecha implementación', fFecha($ev['FechaCompromiso'] ?? null), null, 38);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(70, 70, 70);
    $pdf->Cell(32, 5, t('Progreso'), 0, 0, 'L');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->barra((int) ($ev['PorcentajeAvance'] ?? 0), 55);
    $pdf->Ln(1.5);

    /* ---- PASO 3 ---- */
    $pdf->banda('PASO 3  ·  REDUCCIÓN DE RIESGO POR CONTROLES DE INGENIERÍA', [0, 0, 0], 6, 9);
    $yIni = $pdf->GetY();
    $pdf->SetXY(12, $yIni);
    $pdf->fila('Medida', $e['Medida'] . '  (' . (float) $e['ValMedida'] . ')', 128);
    $pdf->fila('Fecha implementación', fFecha($ct['FechaImplementacion'] ?? null), 128, 38);
    $pdf->fila('Inversión estimada', $ct && $ct['InversionEstimada'] !== null
        ? '$ ' . number_format((float) $ct['InversionEstimada'], 2) : '-', 128, 38);
    $pdf->fila('Estatus', $ct['Estatus'] ?? '-', 128);
    $yTxt = $pdf->GetY();
    $altoImg = $pdf->imagenBlob(imagenEsc($conn, $idEsc, 3), 146, $yIni, 58);
    $pdf->SetY(max($yTxt, $yIni + $altoImg) + 2);

    $form3 = (float) $e['ValSeveridad'] . ' × ' . (float) $e['ValMedida'] . ' × '
        . (float) $e['ValFrecuencia'] . ' × ' . (float) $e['ValPersonas'] . '  =';
    $pdf->cajaNivel($e['CalificacionP3'] !== null ? (float) $e['CalificacionP3'] : null, $e['NivelRiesgoP3'], 130, $form3);
    $pdf->Ln(1);

    $pdf->subBanda('PLAN DE ACCIÓN  ·  Solución de Diseño Ideal');
    $pdf->fila('Acción a realizar', $pl['Descripcion'] ?? '-', null, 38);
    $pdf->fila('Responsable', ($pl['Responsable'] ?? '-') . (empty($pl['Ibm']) ? '' : '  (IBM ' . $pl['Ibm'] . ')'), null, 38);
    $pdf->fila('Fecha objetivo', fFecha($pl['FechaImplementacion'] ?? null), null, 38);
    $pdf->fila('Estatus', $pl['Estatus'] ?? '-', null, 38);
}

/* ---------- PELIGROS GENÉRICOS ---------- */
$pdf->AddPage();
$pdf->banda('PELIGROS GENÉRICOS', [255, 102, 0]);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(90, 90, 90);
$pdf->MultiCell(192, 4, t('Peligros presentes por defecto en todo equipo. Sus puntajes se suman al total del RARR.'), 0, 'L');
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 7.5);
$pdf->SetFillColor(235, 237, 240);
$pdf->Cell(8, 6, '#', 1, 0, 'C', true);
$pdf->Cell(70, 6, t('Escenario de riesgo'), 1, 0, 'L', true);
$pdf->Cell(20, 6, t('Severidad'), 1, 0, 'C', true);
$pdf->Cell(18, 6, t('Frec.'), 1, 0, 'C', true);
$pdf->Cell(18, 6, t('Personas'), 1, 0, 'C', true);
$pdf->Cell(19, 6, t('Paso 1'), 1, 0, 'C', true);
$pdf->Cell(19, 6, t('Paso 2'), 1, 0, 'C', true);
$pdf->Cell(20, 6, t('Paso 3'), 1, 1, 'C', true);

$letras = ['a.', 'b.', 'c.', 'd.', 'e.', 'f.', 'g.'];
$pdf->SetFont('Arial', '', 7);
$sub1 = $sub2 = $sub3 = 0;
foreach ($genericos as $i => $g) {
    $y = $pdf->GetY();
    $txt = $g['EscenarioRiesgo'];
    $pdf->Cell(8, 12, $letras[$i] ?? ($i + 1), 1, 0, 'C');
    $x = $pdf->GetX();
    $pdf->MultiCell(70, 4, t(mb_strlen($txt) > 150 ? mb_substr($txt, 0, 148) . '…' : $txt), 1, 'L');
    $pdf->SetXY($x + 70, $y);
    $pdf->Cell(20, 12, t(mb_substr($g['Severidad'], 0, 14)), 1, 0, 'C');
    $pdf->Cell(18, 12, t($g['Frecuencia']), 1, 0, 'C');
    $pdf->Cell(18, 12, t($g['Personas']), 1, 0, 'C');
    foreach ([['Calificacion', 'NivelRiesgo'], ['CalificacionP2', 'NivelRiesgoP2'], ['CalificacionP3', 'NivelRiesgoP3']] as $k => $par) {
        $c = colorNivel($g[$par[1]]);
        $pdf->SetFillColor($c[0], $c[1], $c[2]);
        $pdf->SetTextColor($g[$par[1]] === 'Bajo' ? 0 : 255, $g[$par[1]] === 'Bajo' ? 0 : 255, $g[$par[1]] === 'Bajo' ? 0 : 255);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell($k === 2 ? 20 : 19, 12, $g[$par[0]] !== null ? (float) $g[$par[0]] : '-', 1, $k === 2 ? 1 : 0, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 7);
    }
    $sub1 += (float) $g['Calificacion'];
    $sub2 += (float) $g['CalificacionP2'];
    $sub3 += (float) $g['CalificacionP3'];
    $pdf->SetY($y + 12);
}
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(134, 6, t('Subtotal peligros genéricos'), 1, 0, 'R');
$pdf->Cell(19, 6, round($sub1, 2), 1, 0, 'C');
$pdf->Cell(19, 6, round($sub2, 2), 1, 0, 'C');
$pdf->Cell(20, 6, round($sub3, 2), 1, 1, 'C');

/* ---------- CIERRE ---------- */
$pdf->Ln(6);
$pdf->banda('RESUMEN FINAL');
$puro = (float) $rarr['MarcadorPuro'];
$ing = (float) $rarr['MarcadorIngenieria'];
$reduccion = $puro > 0 ? round((1 - $ing / $puro) * 100, 1) : 0;
$pdf->fila('Reducción lograda', round($puro, 2) . '  →  ' . round($ing, 2) . '   (−' . $reduccion . '%)', null, 45);
$pdf->fila('Escenarios evaluados', count($propios) . ' propios  +  ' . count($genericos) . ' genéricos  =  ' . (count($propios) + count($genericos)), null, 45);
$pdf->fila('Inversión comprometida', '$ ' . number_format($inversionTotal, 2), null, 45);
$pdf->Ln(2);

$pdf->subBanda('LEYENDA DE NIVELES');
$x = 12;
$y = $pdf->GetY();
foreach ([['Aceptable', '≤ 5'], ['Bajo', '≤ 50'], ['Alto', '≤ 500'], ['Inaceptable', '> 500']] as $l) {
    $c = colorNivel($l[0]);
    $pdf->SetFillColor($c[0], $c[1], $c[2]);
    $pdf->Rect($x, $y + 1, 5, 4, 'F');
    $pdf->SetXY($x + 6, $y);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(40, 6, t($l[0] . '  ' . $l[1]), 0, 0, 'L');
    $x += 48;
}
$pdf->SetY($y + 10);

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(96, 14, '', 0, 0);
$pdf->Cell(96, 14, '', 0, 1);
$pdf->Cell(96, 5, t('_______________________________'), 0, 0, 'C');
$pdf->Cell(96, 5, t('_______________________________'), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(96, 5, t('Elaboró'), 0, 0, 'C');
$pdf->Cell(96, 5, t('Revisó'), 0, 1, 'C');
$pdf->Ln(6);
$pdf->SetFont('Arial', 'I', 7.5);
$pdf->SetTextColor(110, 110, 110);
$pdf->Cell(192, 5, t('Documento generado el ' . date('d/m/Y H:i') . ' · ' . $rarr['IdEquipo']), 0, 1, 'C');

/* Log de auditoría */
if (function_exists('registrarLog')) {
    registrarLog($conn, 'Exporta', [
        'modulo' => 'AnalisisRARR',
        'entidad' => 'PDF',
        'idEquipo' => $idEquipo,
        'idRARR' => $idRARR
    ]);
}
sqlsrv_close($conn);

/* ---------- DESCARGA ---------- */
if (ob_get_length())
    ob_end_clean();
$nombre = 'RARR_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $idEquipo) . '.pdf';
$pdf->Output('D', $nombre);
exit;