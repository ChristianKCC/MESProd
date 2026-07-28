<?php
/* ============================================================================
   ENDPOINT: Exportar un RARR a Excel
   GET: ?idEquipo=PAÑAL-MP25-DBA-01
   Un renglón por escenario propio; cada uno con su evaluación, contención,
   control, plan e imágenes. Genéricos y panel se ubican dinámicamente.
   ============================================================================ */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';
require __DIR__ . '/../../../php/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$idEquipo = trim($_GET['idEquipo'] ?? '');
if ($idEquipo === '') {
    if (ob_get_length())
        ob_end_clean();
    header('Content-Type: text/plain; charset=utf-8');
    echo "ID de equipo no válido";
    exit;
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

/* ---------------- Maestro ---------------- */
$sqlR = "SELECT TOP 1 IdRARR, IdEquipo,
            RTRIM(Maquina) AS Maquina, ISNULL(SeccionEquipo,'-') AS SeccionEquipo,
            RTRIM(Departamento) AS Departamento,
            ISNULL(Estatus,'Pendiente') AS Estatus,
            MarcadorPuro, MarcadorGuardas, MarcadorIngenieria,
            FechaCreacion, FechaConclusion, FechaActualizacion
         FROM TLX002MXDB.dbo.Seg_RARR WHERE IdEquipo = ? ORDER BY IdRARR DESC";
$stmt = sqlsrv_query($conn, $sqlR, [$idEquipo]);
$rarr = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
if (!$rarr) {
    if (ob_get_length())
        ob_end_clean();
    header('Content-Type: text/plain; charset=utf-8');
    echo "No hay RARR registrado para $idEquipo";
    exit;
}
$idRARR = (int) $rarr['IdRARR'];

/* ---------------- Escenarios (propios + genéricos) ---------------- */
$sqlE = "SELECT e.IdEscenario, e.EsGenerico,
            ISNULL(cp.Descripcion,'-') AS Categoria, e.EscenarioRiesgo,
            ISNULL(s.Descripcion,'-') AS Severidad, s.Valor AS ValSeveridad,
            ISNULL(p.Descripcion,'-') AS Probabilidad, p.Valor AS ValProbabilidad,
            ISNULL(f.Descripcion,'-') AS Frecuencia, f.Valor AS ValFrecuencia,
            ISNULL(e.PersonalExpuesto,'-') AS Personas,
            per.Valor AS ValPersonas,
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
         ORDER BY e.EsGenerico, e.IdEscenario";
$stmt = sqlsrv_query($conn, $sqlE, [$idRARR]);
$propios = [];
$genericos = [];
while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    if ((int) $f['EsGenerico'] === 1)
        $genericos[] = $f;
    else
        $propios[] = $f;
}

/* ---------------- Evaluación por escenario ---------------- */
$sqlV = "SELECT ev.IdEscenario, ev.DescripcionHallazgo,
            ISNULL(sf.Descripcion, ISNULL(ev.NivelRiesgoActual,'-')) AS SeguridadFuncional,
            ISNULL(ev.AccionesPropuestas,'-') AS Contencion,
            ev.PorcentajeAvance,
            ISNULL(ev.MedidasMitigacion,'-') AS MedidasAdicionales,
            ISNULL(ev.NombreResponsable,'-') AS Responsable, ev.FechaCompromiso
         FROM TLX002MXDB.dbo.Seg_EvaluacionRARR ev
         LEFT JOIN TLX002MXDB.dbo.Seg_CatSeguridadFuncional sf
                ON sf.IdSeguridadFuncional = ev.IdSeguridadFuncional
         WHERE ev.IdRARR = ? AND ev.Activo = 1";
$stmt = sqlsrv_query($conn, $sqlV, [$idRARR]);
$evalPorEsc = [];
while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $evalPorEsc[(int) $f['IdEscenario']] = $f;
}

/* ---------------- Controles de Ingeniería por escenario ---------------- */
$sqlC = "SELECT a.IdEscenario, a.Descripcion, a.FechaImplementacion, a.InversionEstimada,
            ISNULL(es.Descripcion,'Pendiente') AS Estatus
         FROM TLX002MXDB.dbo.Seg_AccionMejora a
         LEFT JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = a.IdEstatus
         WHERE a.IdRARR = ? AND a.Activo = 1";
$stmt = sqlsrv_query($conn, $sqlC, [$idRARR]);
$controlPorEsc = [];
$inversionTotal = 0;
while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $controlPorEsc[(int) $f['IdEscenario']] = $f;
    $inversionTotal += (float) ($f['InversionEstimada'] ?? 0);
}

/* ---------------- Plan de Acción por escenario ---------------- */
$sqlP = "SELECT s.IdEscenario, s.Descripcion, ISNULL(Responsable,'-') AS Responsable, FechaImplementacion,
            ISNULL(es.Descripcion,'Pendiente') AS Estatus
         FROM TLX002MXDB.dbo.Seg_SeguimientoControl s
         LEFT JOIN TLX002MXDB.dbo.Seg_CatEstatus es ON es.IdEstatus = s.IdEstatus
         WHERE s.IdRARR = ? AND s.Activo = 1";
$stmt = sqlsrv_query($conn, $sqlP, [$idRARR]);
$planPorEsc = [];
while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $planPorEsc[(int) $f['IdEscenario']] = $f;
}

/* ---------------- Imágenes (BLOB) por escenario ---------------- */
function imagenEsc($conn, $idEscenario, $paso)
{
    $sql = "SELECT TOP 1 Imagen FROM TLX002MXDB.dbo.Seg_ImagenRARR
            WHERE IdEscenario = ? AND Paso = ? AND Activo = 1 ORDER BY IdImagen DESC";
    $stmt = sqlsrv_query($conn, $sql, [$idEscenario, $paso]);
    $row = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
    return $row ? $row['Imagen'] : null;
}

/* Conteo por nivel */
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

/* Avance promedio (panel lateral) */
$avancePanel = 0;
if (count($evalPorEsc) > 0) {
    $avancePanel = round(array_sum(array_map(fn($x) => (int) $x['PorcentajeAvance'], $evalPorEsc)) / count($evalPorEsc));
}

/* ============================================================================
   CONSTRUCCIÓN DEL EXCEL
   ============================================================================ */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('RARR');

/* Paleta */
$AZUL = 'FF002060';
$AMARILLO = 'FFFFC000';
$VERDE = 'FF00B050';
$ROJO = 'FFC00000';
$ROJO_TXT = 'FFFF0000';
$AZUL2 = 'FF0066FF';
$GRIS = 'FFF2F2F2';
$NEGRO = 'FF000000';
$NARANJA = 'FFFF6600';
$MORADO = 'FF800080';
$GRISOSCURO = 'FF474747';

$colorNivel = function ($nivel) {
    switch ($nivel) {
        case 'Aceptable':
            return 'FF0AF244';
        case 'Bajo':
            return 'FF24D450';
        case 'Alto':
            return 'FFF20A0A';
        case 'Inaceptable':
            return 'FF7A0C0C';
        default:
            return 'FFFFFFFF';
    }
};
$textoNivel = function ($nivel) {
    $m = [
        'Aceptable' => 'RIESGO ACEPTABLE',
        'Bajo' => 'RIESGO BAJO',
        'Alto' => 'RIESGO ALTO',
        'Inaceptable' => 'RIESGO INACEPTABLE'
    ];
    return $m[$nivel] ?? '-';
};
$mensajeNivel = function ($nivel) {
    switch ($nivel) {
        case 'Inaceptable':
            return 'Inmediato, el trabajo o tarea debe ser suspendido hasta que el riesgo sea reducido por debajo de este nivel.';
        case 'Alto':
            return 'Riesgos en este nivel deben contar con un plan desarrollado para eliminar, y/o minimizar el riesgo y definir el proceso de reducción del nivel de riesgo en un plazo de 15 días.';
        case 'Bajo':
            return 'Riesgos en este nivel deben contar con planes desarrollados para mitigar y reducir el riesgo en un plazo de 45 días. Implementación de mitigación a exposiciones a riesgo debe estar completada en un plazo de 90 días.';
        case 'Aceptable':
            return 'Este nivel de riesgo puede ser tolerable. Acciones específicas y pasos para reducir el riesgo residual en este nivel quedan a discreción de la planta.';
        default:
            return '';
    }
};

$bordeThin = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]
    ]
];

$set = function ($coord, $val, $opts = []) use ($sheet, $bordeThin) {
    $sheet->setCellValue($coord, $val);
    $estilo = $bordeThin;
    $estilo['alignment'] = [
        'horizontal' => $opts['h'] ?? Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => $opts['wrap'] ?? true,
    ];
    $estilo['font'] = [
        'bold' => $opts['bold'] ?? false,
        'size' => $opts['sz'] ?? 11,
        'name' => $opts['name'] ?? 'Arial',
        'color' => ['argb' => $opts['color'] ?? 'FF000000'],
    ];
    if (!empty($opts['bg'])) {
        $estilo['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $opts['bg']]];
    }
    $sheet->getStyle($opts['range'] ?? $coord)->applyFromArray($estilo);
};

/* ---------------- Logo ---------------- */
if (file_exists(__DIR__ . '/../../img/imglogoprosede.png')) {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setPath(__DIR__ . '/../../img/imglogoprosede.png');
    $drawing->setHeight(55);
    $drawing->setCoordinates('A2');
    $drawing->setWorksheet($sheet);
}

/* ---------------- Encabezado ---------------- */
$sheet->mergeCells('E2:O3');
$set(
    'E2',
    'Análisis de Riesgos Potenciales generados por la Maquinaria y Equipos',
    ['bold' => true, 'sz' => 15, 'color' => $AZUL, 'h' => Alignment::HORIZONTAL_LEFT, 'range' => 'E2:O3']
);

$sheet->mergeCells('AV2:AX2');
$set('AV2', 'Revisión ' . date('Y'), ['bold' => true, 'sz' => 10, 'range' => 'AV2:AX2']);
$sheet->mergeCells('AV3:AX5');
$set('AV3', "Ref. NOM-004-STPS-2020\nISO 12100", ['sz' => 10, 'range' => 'AV3:AX5']);

$sheet->mergeCells('D4:E4');
$set('D4', 'Máquina / Módulo / Sección', ['bold' => true, 'range' => 'D4:E4', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('H4:N4');
$set('H4', 'ID Equipo', ['bold' => true, 'range' => 'H4:N4', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('R4:T4');
$set('R4', 'Fecha Última Actualización', ['bold' => true, 'range' => 'R4:T4', 'h' => Alignment::HORIZONTAL_LEFT]);

$sheet->mergeCells('D5:F5');
$set('D5', $rarr['Maquina'] . ' - ' . $rarr['SeccionEquipo'], ['range' => 'D5:F5', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('H5:N5');
$set('H5', $rarr['IdEquipo'], ['bold' => true, 'range' => 'H5:N5', 'h' => Alignment::HORIZONTAL_LEFT]);
$fechaAct = $rarr['FechaActualizacion'] instanceof DateTime ? $rarr['FechaActualizacion']->format('d/m/Y') : '-';
$sheet->mergeCells('R5:T5');
$set('R5', $fechaAct, ['range' => 'R5:T5', 'h' => Alignment::HORIZONTAL_LEFT]);

/* ---------------- Franjas de paso ---------------- */
$sheet->mergeCells('A7:P7');
$set('A7', 'Paso 1', ['bold' => true, 'sz' => 20, 'color' => 'FFFFFFFF', 'bg' => $AZUL, 'range' => 'A7:P7', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('R7:AB7');
$set('R7', 'Paso 2', ['bold' => true, 'sz' => 20, 'color' => 'FFFFFFFF', 'bg' => $AZUL, 'range' => 'R7:AB7', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('AD7:AI7');
$set('AD7', 'Contención', ['bold' => true, 'sz' => 20, 'color' => 'FFFFFFFF', 'bg' => $AZUL, 'range' => 'AD7:AI7', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('AK7:AS7');
$set('AK7', 'Paso 3', ['bold' => true, 'sz' => 20, 'color' => 'FFFFFFFF', 'bg' => $AZUL, 'range' => 'AK7:AS7', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('AU7:AX7');
$set('AU7', 'Plan de Acción', ['bold' => true, 'sz' => 16, 'bg' => $AMARILLO, 'range' => 'AU7:AX7', 'h' => Alignment::HORIZONTAL_LEFT]);

$sheet->mergeCells('A8:P8');
$set(
    'A8',
    'Identificación de Peligros sin Protecciones - "Peligro Puro"',
    ['bold' => true, 'bg' => $NEGRO, 'sz' => 11, 'color' => 'FFFFFFFF', 'range' => 'A8:P8', 'h' => Alignment::HORIZONTAL_LEFT]
);

$sheet->mergeCells('A11:P11');
$set('A11', 'Maquinaria en operación', ['bold' => true, 'bg' => 'FF0B5E54', 'sz' => 11, 'color' => 'FFFFFFFF', 'range' => 'A11:P11', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('R11:AB11');
$set('R11', 'Maquinaria en operación', ['bold' => true, 'bg' => 'FF0B5E54', 'sz' => 11, 'color' => 'FFFFFFFF', 'range' => 'R11:AB11', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('AD11:AI11');
$set('AD11', 'Maquinaria en operación', ['bold' => true, 'bg' => 'FF0B5E54', 'sz' => 11, 'color' => 'FFFFFFFF', 'range' => 'AD11:AI11', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('AK11:AS11');
$set('AK11', 'Maquinaria en operación', ['bold' => true, 'bg' => 'FF0B5E54', 'sz' => 11, 'color' => 'FFFFFFFF', 'range' => 'AK11:AS11', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('AU11:AX11');
$set('AU11', 'Maquinaria en operación', ['bold' => true, 'bg' => 'FF0B5E54', 'sz' => 11, 'color' => 'FFFFFFFF', 'range' => 'AU11:AX11', 'h' => Alignment::HORIZONTAL_LEFT]);

$sheet->mergeCells('R8:AB8');
$set('R8', 'Evaluación de la Protección Actual', ['bold' => true, 'range' => 'R8:AB8', 'bg' => $NEGRO, 'sz' => 11, 'color' => 'FFFFFFFF', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('AD8:AI8');
$set('AD8', 'Controles Administrativos + Contención Física Temporal', ['bold' => true, 'sz' => 11, 'bg' => $NEGRO, 'color' => 'FFFFFFFF', 'range' => 'AD8:AI8', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('AK8:AS8');
$set('AK8', 'Reducción de Riesgo por Controles de Ingeniería', ['bold' => true, 'sz' => 10, 'bg' => $NEGRO, 'color' => 'FFFFFFFF', 'range' => 'AK8:AS8', 'h' => Alignment::HORIZONTAL_LEFT]);
$sheet->mergeCells('AU8:AU10');
$set('AU8', 'Medidas de mitigación a implementar', ['bold' => true, 'sz' => 9, 'bg' => $GRISOSCURO, 'color' => 'FFFFFFFF', 'range' => 'AU8:AU10']);
$sheet->mergeCells('AV8:AV10');
$set('AV8', 'Inversión Estimada', ['bold' => true, 'sz' => 9, 'bg' => $GRISOSCURO, 'color' => 'FFFFFFFF', 'range' => 'AV8:AV10']);
$sheet->mergeCells('AW8:AW10');
$set('AW8', 'Fecha de Implementación', ['bold' => true, 'sz' => 9, 'bg' => $GRISOSCURO, 'color' => 'FFFFFFFF', 'range' => 'AW8:AW10']);
$sheet->mergeCells('AX8:AX10');
$set('AX8', 'Estatus', ['bold' => true, 'sz' => 9, 'bg' => $GRISOSCURO, 'color' => 'FFFFFFFF', 'range' => 'AX8:AX10']);

/* ---------------- Cabeceras de columna (fila 9-10) ---------------- */
$hdr = [
    'A9' => 'Peligro #',
    'B9' => 'Categoría de Peligro',
    'D9' => 'Escenario de Riesgo',
    'E9' => 'Severidad',
    'G9' => 'Probabilidad',
    'I9' => 'Frecuencia',
    'K9' => 'Número de Personas',
    'M9' => 'Estimación del Riesgo',
    'N9' => 'Clasificación del Riesgo',
    'O9' => 'Categoría del Sistema de Seguridad Funcional',
    'P9' => 'Imagen'
];
$merge9 = ['A9:A10', 'B9:C10', 'D9:D10', 'E9:F10', 'G9:H10', 'I9:J10', 'K9:L10', 'M9:M10', 'N9:N10', 'O9:O10', 'P9:P10'];
foreach ($merge9 as $m)
    $sheet->mergeCells($m);
foreach ($hdr as $c => $t) {
    $bg = ($c === 'N9') ? $ROJO : (($c === 'P9') ? $AMARILLO : $GRIS);
    $col = ($c === 'N9') ? 'FFFFFFFF' : 'FF000000';
    $set($c, $t, ['bold' => true, 'sz' => 10, 'bg' => $bg, 'color' => $col]);
}
$hdr2 = [
    'R9' => 'Guardas y Dispositivos existentes',
    'T9' => 'Criterio de Guarda Actual',
    'U9' => 'S',
    'V9' => 'P',
    'W9' => 'F',
    'X9' => 'N',
    'Y9' => 'Estimación Actual',
    'Z9' => 'Clasificación',
    'AA9' => 'Respuesta a Acciones',
    'AB9' => 'Nivel de Desempeño (PL)'
];
foreach (['R9:S10', 'T9:T10', 'U9:U10', 'V9:V10', 'W9:W10', 'X9:X10', 'Y9:Y10', 'Z9:Z10', 'AA9:AA10', 'AB9:AB10'] as $m)
    $sheet->mergeCells($m);
foreach ($hdr2 as $c => $t)
    $set($c, $t, ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);

foreach (['AD9:AE10', 'AF9:AF10', 'AG9:AG10', 'AH9:AH10', 'AI9:AI10'] as $m)
    $sheet->mergeCells($m);
$set('AD9', 'Controles Administrativos', ['bold' => false, 'sz' => 9, 'bg' => $GRIS, 'h' => Alignment::HORIZONTAL_LEFT]);
$set('AF9', 'Progreso %', ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);
$set('AG9', 'Medidas adicionales', ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);
$set('AH9', 'Responsable', ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);
$set('AI9', 'Fecha de Implementación', ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);

foreach (['AK9:AL9', 'AM9:AM10', 'AN9:AN10', 'AO9:AO10', 'AP9:AP10', 'AQ9:AQ10', 'AR9:AR10', 'AS9:AS10'] as $m)
    $sheet->mergeCells($m);
$set('AK9', 'Medida de mitigación (reducir a < 5)', ['bold' => true, 'sz' => 9, 'bg' => $GRIS, 'color' => $VERDE]);
$set('AM9', 'S', ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);
$set('AN9', 'P', ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);
$set('AO9', 'F', ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);
$set('AP9', 'N', ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);
$set('AQ9', 'Marcador Residual', ['bold' => true, 'sz' => 9, 'bg' => $VERDE, 'color' => 'FFFFFFFF']);
$set('AR9', 'Clasificación Residual', ['bold' => true, 'sz' => 9, 'bg' => $GRIS]);
$set('AS9', 'Imagen', ['bold' => true, 'sz' => 9, 'bg' => $AMARILLO]);

/* ---------------- Filas de datos: un renglón por escenario propio ---------------- */
$filaIni = 12;
$fila = $filaIni;

$pintarEscenario = function ($r, $e, $num) use ($set, $colorNivel, $textoNivel) {
    $set('A' . $r, $num);
    $set('B' . $r, $e['Categoria'], ['h' => Alignment::HORIZONTAL_LEFT, 'range' => 'B' . $r . ':C' . $r]);
    $set('D' . $r, $e['EscenarioRiesgo'], ['h' => Alignment::HORIZONTAL_LEFT]);
    $set('E' . $r, $e['Severidad'], ['range' => 'E' . $r . ':F' . $r, 'sz' => 9]);
    $set('G' . $r, $e['Probabilidad'], ['range' => 'G' . $r . ':H' . $r, 'sz' => 8]);
    $set('I' . $r, $e['Frecuencia'], ['range' => 'I' . $r . ':J' . $r, 'sz' => 9]);
    $set('K' . $r, $e['Personas'], ['range' => 'K' . $r . ':L' . $r, 'sz' => 9]);
    $set('M' . $r, (float) $e['Calificacion'], ['bold' => true]);
    $set('N' . $r, $textoNivel($e['NivelRiesgo']), ['bold' => true, 'color' => 'FFFFFFFF', 'bg' => $colorNivel($e['NivelRiesgo']), 'sz' => 9]);
};

$insertarImagen = function ($blob, $coord) use ($sheet) {
    if (!$blob)
        return;
    $img = @imagecreatefromstring($blob);
    if ($img === false)
        return;
    $draw = new MemoryDrawing();
    $draw->setImageResource($img);
    $draw->setRenderingFunction(MemoryDrawing::RENDERING_JPEG);
    $draw->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
    $draw->setHeight(140);
    $draw->setCoordinates($coord);
    $draw->setWorksheet($sheet);
};

foreach ($propios as $idx => $e) {
    $r = $fila;
    $idEsc = (int) $e['IdEscenario'];
    $ev = $evalPorEsc[$idEsc] ?? null;
    $ctrl = $controlPorEsc[$idEsc] ?? null;
    $pl = $planPorEsc[$idEsc] ?? null;

    foreach (['A' => 'A', 'B' => 'C', 'E' => 'F', 'G' => 'H', 'I' => 'J', 'K' => 'L'] as $ini => $fin) {
        $sheet->mergeCells($ini . $r . ':' . $fin . $r);
    }
    $pintarEscenario($r, $e, $idx + 1);

    $set('O' . $r, $ev['SeguridadFuncional'] ?? '-', ['sz' => 9]);

    /* Paso 2 */
    $sheet->mergeCells('R' . $r . ':S' . $r);
    if ($ev) {
        $set('R' . $r, $ev['DescripcionHallazgo'] ?? '-', ['h' => Alignment::HORIZONTAL_LEFT, 'sz' => 9, 'range' => 'R' . $r . ':S' . $r]);
        $set('T' . $r, $e['CriterioGuarda'], ['sz' => 8, 'h' => Alignment::HORIZONTAL_LEFT]);
        $set('U' . $r, $e['ValSeveridad'] !== null ? (float) $e['ValSeveridad'] : '-', ['sz' => 8]);
        $set('V' . $r, $e['ValCriterio'] !== null ? (float) $e['ValCriterio'] : '-', ['sz' => 8]);
        $set('W' . $r, $e['ValFrecuencia'] !== null ? (float) $e['ValFrecuencia'] : '-', ['sz' => 8]);
        $set('X' . $r, $e['ValPersonas'] !== null ? (float) $e['ValPersonas'] : '-', ['sz' => 8]);
        $set('Y' . $r, $e['CalificacionP2'] !== null ? (float) $e['CalificacionP2'] : '-', ['bold' => true]);
        $set('Z' . $r, $textoNivel($e['NivelRiesgoP2']), ['bold' => true, 'color' => 'FFFFFFFF', 'bg' => $colorNivel($e['NivelRiesgoP2']), 'sz' => 9]);
        $set('AA' . $r, $mensajeNivel($e['NivelRiesgoP2']), ['sz' => 8, 'h' => Alignment::HORIZONTAL_LEFT]);
        $set('AB' . $r, $ev['SeguridadFuncional'] ?? '-', ['sz' => 8, 'h' => Alignment::HORIZONTAL_LEFT]);
    } else {
        $set('R' . $r, '-', ['range' => 'R' . $r . ':S' . $r]);
        foreach (['T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB'] as $col)
            $set($col . $r, '-', ['sz' => 8]);
    }

    /* Contención */
    $sheet->mergeCells('AD' . $r . ':AE' . $r);
    if ($ev) {
        $set('AD' . $r, $ev['Contencion'], ['h' => Alignment::HORIZONTAL_LEFT, 'sz' => 8, 'range' => 'AD' . $r . ':AE' . $r]);
        $set('AF' . $r, (int) $ev['PorcentajeAvance'] . '%');
        $set('AG' . $r, $ev['MedidasAdicionales'], ['h' => Alignment::HORIZONTAL_LEFT, 'sz' => 8]);
        $set('AH' . $r, $ev['Responsable'], ['sz' => 9]);
        $fImp = $ev['FechaCompromiso'] instanceof DateTime ? $ev['FechaCompromiso']->format('d/m/Y') : '-';
        $set('AI' . $r, $fImp, ['sz' => 9]);
    } else {
        $set('AD' . $r, '-', ['range' => 'AD' . $r . ':AE' . $r]);
        foreach (['AF', 'AG', 'AH', 'AI'] as $col)
            $set($col . $r, '-', ['sz' => 8]);
    }

    /* Paso 3 */
    $sheet->mergeCells('AK' . $r . ':AL' . $r);
    $set('AK' . $r, $e['Medida'], ['h' => Alignment::HORIZONTAL_LEFT, 'sz' => 8, 'range' => 'AK' . $r . ':AL' . $r]);
    $set('AM' . $r, $e['ValSeveridad'] !== null ? (float) $e['ValSeveridad'] : '-', ['sz' => 8]);
    $set('AN' . $r, $e['ValMedida'] !== null ? (float) $e['ValMedida'] : '-', ['sz' => 8]);
    $set('AO' . $r, $e['ValFrecuencia'] !== null ? (float) $e['ValFrecuencia'] : '-', ['sz' => 8]);
    $set('AP' . $r, $e['ValPersonas'] !== null ? (float) $e['ValPersonas'] : '-', ['sz' => 8]);
    $set('AQ' . $r, $e['CalificacionP3'] !== null ? (float) $e['CalificacionP3'] : '-', ['bold' => true]);
    $set('AR' . $r, $textoNivel($e['NivelRiesgoP3']), ['bold' => true, 'color' => 'FFFFFFFF', 'bg' => $colorNivel($e['NivelRiesgoP3']), 'sz' => 9]);

    /* Plan de acción */
    if ($pl) {
        $invEsc = $ctrl && $ctrl['InversionEstimada'] !== null ? (float) $ctrl['InversionEstimada'] : 0;
        $set('AU' . $r, $pl['Descripcion'], ['h' => Alignment::HORIZONTAL_LEFT, 'sz' => 8]);
        $set('AV' . $r, $invEsc > 0 ? '$ ' . number_format($invEsc, 2) : '-', ['sz' => 9]);
        $fPlan = $pl['FechaImplementacion'] instanceof DateTime ? $pl['FechaImplementacion']->format('d/m/Y') : '-';
        $set('AW' . $r, $fPlan, ['sz' => 9]);
        $set('AX' . $r, $pl['Estatus'], ['sz' => 9]);
    } else {
        foreach (['AU', 'AV', 'AW', 'AX'] as $col)
            $set($col . $r, '-', ['sz' => 8]);
    }

    /* Imágenes */
    $insertarImagen(imagenEsc($conn, $idEsc, 1), 'P' . $r);
    $insertarImagen(imagenEsc($conn, $idEsc, 3), 'AS' . $r);

    $sheet->getRowDimension($r)->setRowHeight(150);
    $fila++;
}

/* Genéricos: 1 fila de separación tras el último escenario */
$filaGen = $fila + 1;
$rGen = $filaGen + 1;

/* ---------------- Peligros Genéricos (dinámico) ---------------- */
$sheet->mergeCells('A' . $filaGen . ':N' . $filaGen);
$set('A' . $filaGen, 'Peligros Genéricos', ['bold' => true, 'sz' => 14, 'bg' => $NARANJA, 'color' => 'FFFFFFFF', 'h' => Alignment::HORIZONTAL_LEFT, 'range' => 'A' . $filaGen . ':N' . $filaGen]);
$sheet->mergeCells('R' . $filaGen . ':AA' . $filaGen);
$set('R' . $filaGen, 'Peligros Genéricos', ['bold' => true, 'sz' => 14, 'bg' => $NARANJA, 'color' => 'FFFFFFFF', 'h' => Alignment::HORIZONTAL_LEFT, 'range' => 'R' . $filaGen . ':AA' . $filaGen]);
$sheet->mergeCells('AK' . $filaGen . ':AR' . $filaGen);
$set('AK' . $filaGen, 'Peligros Genéricos', ['bold' => true, 'sz' => 14, 'bg' => $NARANJA, 'color' => 'FFFFFFFF', 'h' => Alignment::HORIZONTAL_LEFT, 'range' => 'AK' . $filaGen . ':AR' . $filaGen]);

$letras = ['a.', 'b.', 'c.', 'd.', 'e.', 'f.', 'g.'];
$r = $rGen;
foreach ($genericos as $i => $g) {
    foreach (['A' => 'A', 'B' => 'C', 'E' => 'F', 'G' => 'H', 'I' => 'J', 'K' => 'L'] as $ini => $fin) {
        $sheet->mergeCells($ini . $r . ':' . $fin . $r);
    }
    $set('A' . $r, $letras[$i] ?? ($i + 1));
    $set('B' . $r, $g['Categoria'], ['h' => Alignment::HORIZONTAL_LEFT, 'range' => 'B' . $r . ':C' . $r, 'sz' => 9]);
    $set('D' . $r, $g['EscenarioRiesgo'], ['h' => Alignment::HORIZONTAL_LEFT, 'sz' => 9]);
    $set('E' . $r, $g['Severidad'], ['range' => 'E' . $r . ':F' . $r, 'sz' => 9]);
    $set('G' . $r, $g['Probabilidad'], ['range' => 'G' . $r . ':H' . $r, 'sz' => 8]);
    $set('I' . $r, $g['Frecuencia'], ['range' => 'I' . $r . ':J' . $r, 'sz' => 9]);
    $set('K' . $r, $g['Personas'], ['range' => 'K' . $r . ':L' . $r, 'sz' => 9]);
    $set('M' . $r, (float) $g['Calificacion'], ['bold' => true]);
    $set('N' . $r, $textoNivel($g['NivelRiesgo']), ['bold' => true, 'color' => 'FFFFFFFF', 'bg' => $colorNivel($g['NivelRiesgo']), 'sz' => 9]);
    $set('O' . $r, '', ['sz' => 9]);

    $sheet->mergeCells('R' . $r . ':S' . $r);
    $set('R' . $r, '', ['range' => 'R' . $r . ':S' . $r]);
    $set('T' . $r, $g['CriterioGuarda'], ['h' => Alignment::HORIZONTAL_LEFT, 'sz' => 8]);
    $set('U' . $r, $g['ValSeveridad'] !== null ? (float) $g['ValSeveridad'] : '-', ['sz' => 8]);
    $set('V' . $r, $g['ValCriterio'] !== null ? (float) $g['ValCriterio'] : '-', ['sz' => 8]);
    $set('W' . $r, $g['ValFrecuencia'] !== null ? (float) $g['ValFrecuencia'] : '-', ['sz' => 8]);
    $set('X' . $r, $g['ValPersonas'] !== null ? (float) $g['ValPersonas'] : '-', ['sz' => 8]);
    $set('Y' . $r, $g['CalificacionP2'] !== null ? (float) $g['CalificacionP2'] : '-', ['bold' => true]);
    $set('Z' . $r, $textoNivel($g['NivelRiesgoP2']), ['bold' => true, 'color' => 'FFFFFFFF', 'bg' => $colorNivel($g['NivelRiesgoP2']), 'sz' => 9]);
    $set('AA' . $r, $mensajeNivel($g['NivelRiesgoP2']), ['sz' => 7, 'h' => Alignment::HORIZONTAL_LEFT]);
    $set('AB' . $r, '', ['sz' => 8]);

    $sheet->mergeCells('AK' . $r . ':AL' . $r);
    $set('AK' . $r, $g['Medida'], ['h' => Alignment::HORIZONTAL_LEFT, 'sz' => 8, 'range' => 'AK' . $r . ':AL' . $r]);
    $set('AM' . $r, $g['ValSeveridad'] !== null ? (float) $g['ValSeveridad'] : '-', ['sz' => 8]);
    $set('AN' . $r, $g['ValMedida'] !== null ? (float) $g['ValMedida'] : '-', ['sz' => 8]);
    $set('AO' . $r, $g['ValFrecuencia'] !== null ? (float) $g['ValFrecuencia'] : '-', ['sz' => 8]);
    $set('AP' . $r, $g['ValPersonas'] !== null ? (float) $g['ValPersonas'] : '-', ['sz' => 8]);
    $set('AQ' . $r, $g['CalificacionP3'] !== null ? (float) $g['CalificacionP3'] : '-', ['bold' => true]);
    $set('AR' . $r, $textoNivel($g['NivelRiesgoP3']), ['bold' => true, 'color' => 'FFFFFFFF', 'bg' => $colorNivel($g['NivelRiesgoP3']), 'sz' => 9]);
    $r++;
}

/* ---------------- Panel lateral: resumen y marcadores ---------------- */
$sheet->mergeCells('BD9:BH9');
$set('BD9', 'Paso 1', ['bold' => true, 'sz' => 14, 'color' => 'FFFFFFFF', 'bg' => $AZUL, 'range' => 'BD9:BH9']);
$sheet->mergeCells('BJ9:BN9');
$set('BJ9', 'Paso 2', ['bold' => true, 'sz' => 14, 'color' => 'FFFFFFFF', 'bg' => $AZUL, 'range' => 'BJ9:BN9']);
$sheet->mergeCells('BP9:BT9');
$set('BP9', 'Paso 3', ['bold' => true, 'sz' => 14, 'color' => 'FFFFFFFF', 'bg' => $AZUL, 'range' => 'BP9:BT9']);

$encRes = ['Riesgo Aceptable', 'Riesgo Bajo', 'Riesgo Alto', 'Riesgo Inaceptable', 'Total'];
$cols1 = ['BD', 'BE', 'BF', 'BG', 'BH'];
$cols2 = ['BJ', 'BK', 'BL', 'BM', 'BN'];
$cols3 = ['BP', 'BQ', 'BR', 'BS', 'BT'];
foreach ([$cols1, $cols2, $cols3] as $cols) {
    foreach ($encRes as $j => $txt) {
        $set($cols[$j] . '11', $txt, ['bold' => true, 'sz' => 8, 'bg' => $GRIS]);
    }
}
$fmtCont = function ($cols, $c) use ($set) {
    $total = $c['Aceptable'] + $c['Bajo'] + $c['Alto'] + $c['Inaceptable'];
    $set($cols[0] . '12', $c['Aceptable']);
    $set($cols[1] . '12', $c['Bajo']);
    $set($cols[2] . '12', $c['Alto']);
    $set($cols[3] . '12', $c['Inaceptable']);
    $set($cols[4] . '12', $total, ['bold' => true]);
};
$fmtCont($cols1, $cont1);
$fmtCont($cols2, $cont2);
$fmtCont($cols3, $cont3);

$sheet->mergeCells('BD13:BH13');
$set('BD13', 'Marcador de Riesgo', ['bold' => false, 'sz' => 10, 'range' => 'BD13:BH13']);
$sheet->mergeCells('BJ13:BN13');
$set('BJ13', 'Marcador de Riesgo', ['bold' => false, 'sz' => 10, 'range' => 'BJ13:BN13']);
$sheet->mergeCells('BP13:BT13');
$set('BP13', 'Marcador de Riesgo', ['bold' => false, 'sz' => 10, 'range' => 'BP13:BT13']);

$sheet->mergeCells('BD14:BH14');
$set('BD14', round((float) $rarr['MarcadorPuro']), ['bold' => true, 'sz' => 20, 'range' => 'BD14:BH14']);
$sheet->mergeCells('BJ14:BN14');
$set('BJ14', round((float) $rarr['MarcadorGuardas']), ['bold' => true, 'sz' => 20, 'range' => 'BJ14:BN14']);
$sheet->mergeCells('BP14:BT14');
$set('BP14', round((float) $rarr['MarcadorIngenieria'], 1), ['bold' => true, 'sz' => 20, 'range' => 'BP14:BT14']);

$sheet->mergeCells('BD15:BH15');
$set('BD15', 'Peligro Puro', ['sz' => 11, 'color' => $ROJO_TXT, 'range' => 'BD15:BH15']);
$sheet->mergeCells('BJ15:BN15');
$set('BJ15', 'Reducción con Guardas Actuales', ['sz' => 11, 'color' => $ROJO_TXT, 'range' => 'BJ15:BN15']);
$sheet->mergeCells('BP15:BT15');
$set('BP15', 'Potencial de Reducción con Ingeniería', ['sz' => 10, 'color' => $VERDE, 'range' => 'BP15:BT15']);

$sheet->mergeCells('BD17:BH17');
$set('BD17', 'Implementación de Controles Administrativos', ['sz' => 12, 'color' => 'FFFFFFFF', 'bg' => $AZUL2, 'range' => 'BD17:BH17']);
$sheet->mergeCells('BQ17:BS17');
$set('BQ17', $avancePanel, ['bold' => true, 'sz' => 16, 'range' => 'BQ17:BS17', 'h' => Alignment::HORIZONTAL_RIGHT]);
$set('BT17', '%', ['bold' => true, 'sz' => 16]);
$sheet->mergeCells('BD18:BH18');
$set('BD18', 'Inversión Estimada', ['bold' => true, 'sz' => 11, 'range' => 'BD18:BH18']);
$sheet->mergeCells('BQ18:BT18');
$set('BQ18', $inversionTotal > 0 ? '$ ' . number_format($inversionTotal, 2) : '$ 0.00', ['bold' => true, 'sz' => 14, 'range' => 'BQ18:BT18']);

sqlsrv_close($conn);

/* ---------------- Anchos de columna ---------------- */
$anchos = [
    'A' => 5,
    'B' => 12,
    'C' => 14,
    'D' => 32,
    'E' => 8,
    'F' => 6,
    'G' => 12,
    'H' => 6,
    'I' => 10,
    'J' => 6,
    'K' => 10,
    'L' => 6,
    'M' => 12,
    'N' => 15,
    'O' => 18,
    'P' => 26,
    'R' => 14,
    'S' => 12,
    'T' => 25,
    'U' => 5,
    'V' => 5,
    'W' => 5,
    'X' => 5,
    'Y' => 12,
    'Z' => 14,
    'AA' => 20,
    'AB' => 24,
    'AD' => 4,
    'AE' => 40,
    'AF' => 10,
    'AG' => 26,
    'AH' => 14,
    'AI' => 14,
    'AK' => 4,
    'AL' => 28,
    'AM' => 5,
    'AN' => 5,
    'AO' => 5,
    'AP' => 5,
    'AQ' => 14,
    'AR' => 18,
    'AS' => 26,
    'AU' => 25,
    'AV' => 16,
    'AW' => 18,
    'AX' => 14
];
foreach ($anchos as $col => $w)
    $sheet->getColumnDimension($col)->setWidth($w);

foreach (['BD', 'BE', 'BF', 'BG', 'BH', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BP', 'BQ', 'BR', 'BS', 'BT'] as $col) {
    $sheet->getColumnDimension($col)->setWidth(9);
}

/* ---------------- Altura de filas ---------------- */
$sheet->getRowDimension(9)->setRowHeight(70);
for ($i = $rGen; $i < $r; $i++) {
    $sheet->getRowDimension($i)->setRowHeight(120);
}

/* ---------------- Descarga ---------------- */
if (ob_get_length())
    ob_end_clean();

$nombre = 'RARR_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $idEquipo) . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombre . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

// registrarLog($conn, 'Exporta', ['modulo' => 'AnalisisRARR', 'entidad' => 'RARR', 'idEquipo' => $idEquipo])