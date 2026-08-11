<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

// Guardar resumen de llenado en tablas dentro de un log
$LOG = __DIR__ . '/wr_guardar.log';
function logwr($m){
    global $LOG;
    @file_put_contents($LOG, '['.date('Y-m-d H:i:s').'] '.(is_string($m)?$m:print_r($m,true)).PHP_EOL, FILE_APPEND);
}

function out($a){
    $stray = ob_get_length() !== false ? ob_get_clean() : '';
    if (is_string($stray) && trim($stray) !== '') logwr('Salida inesperada antes del JSON: '.$stray);
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode($a);
    exit;
}

register_shutdown_function(function(){
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        logwr('FATAL: '.$e['message'].' en '.$e['file'].':'.$e['line']);
        while (ob_get_level()) ob_end_clean();
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>false, 'error'=>'Error fatal en el servidor: '.$e['message']]);
    }
});

// Encabezado de log para resumen de operaciones
logwr('--- INICIO guardado ---');

// Invocacion de elementos
require_once __DIR__ . '/catalogos.php';
require_once __DIR__ . '/../../conexion.php';
$conn = (new ClassConexion())->conexion('TLX004MXDB');
if ($conn === false || $conn === null) {
    logwr('Conexión nula/false: '.print_r(function_exists('sqlsrv_errors')?sqlsrv_errors():'sin sqlsrv', true));
    out(['ok'=>false, 'error'=>'No se pudo conectar a la base de datos.']);
}

/* ---------- Helpers ---------- */
function errs(){ return print_r(sqlsrv_errors(), true); }
function nv($v){ if (is_array($v)) return $v; $v = is_string($v) ? trim($v) : $v; return ($v === '' || $v === null) ? null : $v; }
function numv($v){
    $v = nv($v);
    if ($v === null) return null;
    $s = str_replace(',', '.', (string)$v);
    // Si no es un número válido, se descarta (NULL) para no romper columnas DECIMAL.
    return is_numeric($s) ? $s : null;
}
function cellNum($v){
    $v = trim((string)$v); if ($v === '') return null;
    $s = 0; $hay = false;
    foreach (preg_split('/[+\s]+/', $v) as $p) {
        $p = str_replace(',', '.', $p);
        if ($p !== '' && is_numeric($p)) { $s += (float)$p; $hay = true; }
    }
    return $hay ? $s : null;
}

// Relacion rapida para nombre de tablas
$KEYS = [
    'tblMXPRBitDisponibleWR'           => ['Equipo'],
    'tblMXPRBitRecuperadoWR'           => ['Equipo'],
    'tblMXPRBitPacasRecibidasWR'       => ['Planta'],
    'tblMXPRBitPacasAlimentadasWR'     => ['Planta'],
    'tblMXPRBitPresionWR'              => ['Compactador'],
    'tblMXPRBitOrdenLimpiezaWR'        => ['Area'],
    'tblMXPRBitTiemposWR'              => [],
    'tblMXPRBitExportacionWR'          => ['Tipo'],
    'tblMXPRBitSamRecuperadoWR'        => ['NoValor'],
    'tblMXPRBitWRMermaWR'              => ['Tipo'],
    'tblMXPRBitWRSamTolvaWR'           => ['Tolva'],
    'tblMXPRBitWRPacasAlimentadasWR'   => ['Planta'],
    'tblMXPRBitWRMaquinaWR'            => ['Maquina'],
    'tblMXPRBitWRPacasMermaWR'         => ['Concepto'],
    'tblMXPRBitWRNotaWR'               => ['Maquina'],
    'tblMXPRBitPesoBolsaWR'            => ['Tipo','NoFila'],
    'tblMXPRBitReporteWR'              => [],
    'tblMXPRBitResponsablesWR'         => [],
];

// Funcion para actualizar/insertar valores segun su existencia en la BD
function upsertTabla($conn, $tabla, $folio, array $cols, array $keyCols, array $rows){
    // leer existentes del folio: Id + columnas llave
    $selCols = array_merge(['Id'], $keyCols);
    $sel = sqlsrv_query($conn, "SELECT " . implode(',', $selCols) . " FROM dbo.$tabla WHERE IdEncabezadoBitacora = ?", [$folio]);
    if ($sel === false) throw new Exception("SELECT $tabla: " . errs());
    $existentes = [];   // keyStr => Id
    while ($row = sqlsrv_fetch_array($sel, SQLSRV_FETCH_ASSOC)) {
        $kp = [];
        foreach ($keyCols as $kc) $kp[] = (string)$row[$kc];
        $existentes[implode('||', $kp)] = $row['Id'];
    }
    // Liberar stmt para siguuiente transaccion
    sqlsrv_free_stmt($sel);

    // keyStr de lo que sí existe
    $usados = [];  
    $n = 0;
    foreach ($rows as $rrow) {
        $kp = [];
        foreach ($keyCols as $kc) $kp[] = (string)($rrow[$kc] ?? '');
        $kstr = implode('||', $kp);
        $usados[$kstr] = true;

        // UPDATE en caso de existir
        if (array_key_exists($kstr, $existentes)) {            
            $set = implode(',', array_map(fn($c) => "$c = ?", $cols));
            $params = [];
            foreach ($cols as $c) $params[] = $rrow[$c] ?? null;
            $params[] = $existentes[$kstr];
            $st = sqlsrv_query($conn, "UPDATE dbo.$tabla SET $set WHERE Id = ?", $params);
            if ($st === false) throw new Exception("UPDATE $tabla: " . errs());
            // Liberar stmt para siguuiente transaccion
            sqlsrv_free_stmt($st);
        }
       
        // INSERT en caso de ser un folio nuevo
        else {            
            $ph  = implode(',', array_fill(0, count($cols) + 1, '?'));
            $sql = "INSERT INTO dbo.$tabla (IdEncabezadoBitacora," . implode(',', $cols) . ") VALUES ($ph)";
            $params = [$folio];
            foreach ($cols as $c) $params[] = $rrow[$c] ?? null;
            $st = sqlsrv_query($conn, $sql, $params);
            if ($st === false) throw new Exception("INSERT $tabla: " . errs());
            // Liberar stmt para siguuiente transaccion
            sqlsrv_free_stmt($st);
        }
        $n++;
    }

    // Borrar lo que estaba antes y ya no vino en el nuevo registro
    foreach ($existentes as $kstr => $id) {
        if (!isset($usados[$kstr])) {
            $st = sqlsrv_query($conn, "DELETE FROM dbo.$tabla WHERE Id = ?", [$id]);
            if ($st === false) throw new Exception("DELETE $tabla: " . errs());
            // Liberar stmt para siguuiente transaccion
            sqlsrv_free_stmt($st);
        }
    }

    // Resumen final de operacion dentro del log
    logwr("  $tabla -> $n fila(s) (upsert)");
    return $n;
}

/* ---------- Entrada ---------- */
$folio = isset($_POST['folio']) ? (int)$_POST['folio'] : 0;
$turno = isset($_POST['turno']) ? (int)$_POST['turno'] : 0;

// Adjuntar el folio, turno y cuantos campos fueron insertados
logwr("Folio=$folio Turno=$turno  (campos POST: ".count($_POST).")");

if ($folio <= 0)
    out(['ok' => false, 'error' => 'Folio inválido o ausente.']);
if ($turno < 1 || $turno > 3)
    out(['ok' => false, 'error' => 'Turno inválido (1-3).']);

$chk = sqlsrv_query($conn, "SELECT TOP 1 1 AS x FROM dbo.tblEncabezadoBitacora WHERE IdEncabezadoBitacora = ?", [$folio]);
if ($chk === false) { logwr('Error verificando folio: '.errs()); out(['ok'=>false, 'error'=>'No se pudo verificar el folio: '.errs()]); }
$existeFolio = sqlsrv_fetch($chk);
// Liberar stmt para siguuiente transaccion
sqlsrv_free_stmt($chk);

// Validacion en caso de que no exista un folio valido
if (!$existeFolio) {
    logwr("Folio $folio inexistente en tblEncabezadoBitacora");
    out(['ok'=>false, 'error'=>"El folio $folio no existe en tblEncabezadoBitacora. Verifica el encabezado."]);
}

$P      = $_POST;
$tLong  = ['1ero', '2do', '3ero'][$turno - 1];
$tShort = ['t1', 't2', 't3'][$turno - 1];
$tSlug  = slug(['1ERO', '2DO', '3ERO'][$turno - 1]);
$tDigit = (string)$turno;
$tRecib = $turno === 1 ? '1er' : ($turno === 2 ? '2do' : null);

/* ---------- Construcción de filas por tabla ---------- */
$plan = [];

$r = [];
foreach ($EQUIPOS_DISPONIBLE as $eq) {
    if ($eq === '') continue;
    $k = slug($eq);
    $v = numv($P['disponible'][$k][$tLong] ?? null);
    if ($v !== null) $r[] = ['Equipo' => $eq, 'Cantidad' => $v];
}
$plan['tblMXPRBitDisponibleWR'] = ['cols' => ['Equipo','Cantidad'], 'rows' => $r];

$r = [];
foreach ($EQUIPOS_RECUPERADO as $eq) { $k = slug($eq);
    $pct = numv($P['recup_pct'][$k][$tLong] ?? null);
    $kg  = numv($P['recup_kg'][$k][$tLong] ?? null);
    if ($pct !== null || $kg !== null) $r[] = ['Equipo' => $eq, 'Porcentaje' => $pct, 'Kilogramos' => $kg];
}
$plan['tblMXPRBitRecuperadoWR'] = ['cols' => ['Equipo','Porcentaje','Kilogramos'], 'rows' => $r];

$r = [];
if ($tRecib !== null) foreach ($PLANTAS as $p) { $k = slug($p);
    $v = numv($P['pacas_recibidas'][$k][$tRecib] ?? null);
    if ($v !== null) $r[] = ['Planta' => $p, 'Cantidad' => $v];
}
$plan['tblMXPRBitPacasRecibidasWR'] = ['cols' => ['Planta','Cantidad'], 'rows' => $r];

$r = [];
foreach ($PLANTAS as $p) { $k = slug($p);
    $v = numv($P['pacas_alimentadas'][$k][$tLong] ?? null);
    if ($v !== null) $r[] = ['Planta' => $p, 'Cantidad' => $v];
}
$plan['tblMXPRBitPacasAlimentadasWR'] = ['cols' => ['Planta','Cantidad'], 'rows' => $r];

$r = [];
foreach ($COLS_PRESION as $ck => $cv) {
    $v = nv($P['presion'][$tSlug][$ck] ?? null);
    if ($v !== null) $r[] = ['Compactador' => $cv, 'Valor' => $v];
}
$plan['tblMXPRBitPresionWR'] = ['cols' => ['Compactador','Valor'], 'rows' => $r];

$r = [];
foreach ($AREAS_LIMPIEZA as $a) { $k = slug($a);
    $v = nv($P['orden'][$k][$tLong] ?? null);
    if ($v !== null) $r[] = ['Area' => $a, 'Estado' => $v];
}
$plan['tblMXPRBitOrdenLimpiezaWR'] = ['cols' => ['Area','Estado'], 'rows' => $r];

/* ---- Tiempos: Pacas locales y recorte ---- */
$tiempos = [
    'PacasLocales' => numv($P['pacas_locales'][$tLong] ?? null),
    'PacasRecorte' => numv($P['pacas_recorte'][$tLong] ?? null),
];
$plan['tblMXPRBitTiemposWR'] = ['cols' => array_keys($tiempos), 'rows' => array_filter($tiempos, fn($x) => $x !== null) ? [$tiempos] : []];

/* ---- Exportación (Pañal / Toalla) ---- */
$r = [];
foreach (['PANAL' => 'export_panal', 'TOALLA' => 'export_toalla'] as $tipo => $campo) {
    $v = numv($P[$campo][$tLong] ?? null);
    if ($v !== null) $r[] = ['Tipo' => $tipo, 'Cantidad' => $v];
}
$plan['tblMXPRBitExportacionWR'] = ['cols' => ['Tipo','Cantidad'], 'rows' => $r];

/* ---- SAM recuperado — valores variables c1..n..cN ---- */
$r = [];
$samArr = $P['sam_recuperado'][$tSlug] ?? [];
if (is_array($samArr)) {
    foreach ($samArr as $ckey => $cval) {
        $v = numv($cval);
        if ($v === null) continue;
        // Conversion de datos de c3 -> 3
        $no = (int)preg_replace('/\D/', '', (string)$ckey);
        if ($no <= 0) $no = count($r) + 1;
        $r[] = ['NoValor' => $no, 'Valor' => $v];
    }
}
$plan['tblMXPRBitSamRecuperadoWR'] = ['cols' => ['NoValor','Valor'], 'rows' => $r];

$r = [];
foreach (['PANAL','TOALLA'] as $tipo) {
    $v = numv($P['wr_merma'][$tipo][$tShort] ?? null);
    if ($v !== null) $r[] = ['Tipo' => $tipo, 'Kilos' => $v];
}
$plan['tblMXPRBitWRMermaWR'] = ['cols' => ['Tipo','Kilos'], 'rows' => $r];

$r = [];
foreach ($WR_TOLVAS as $tolva) {
    $v = numv($P['wr_sam'][$tolva][$tShort] ?? null);
    if ($v !== null) $r[] = ['Tolva' => (string)$tolva, 'Kilos' => $v];
}
$plan['tblMXPRBitWRSamTolvaWR'] = ['cols' => ['Tolva','Kilos'], 'rows' => $r];

$r = [];
foreach ($WR_PLANTAS as $p) { $k = slug($p);
    $v = numv($P['wr_pacas_alim'][$k][$tShort] ?? null);
    if ($v !== null) $r[] = ['Planta' => $p, 'Cantidad' => $v];
}
$plan['tblMXPRBitWRPacasAlimentadasWR'] = ['cols' => ['Planta','Cantidad'], 'rows' => $r];

$r = [];
foreach ($WR_MAQUINAS as $m) { $k = slug($m);
    $kg  = numv($P['wr_maq'][$k][$tShort]['kg'] ?? null);
    $pct = numv($P['wr_maq'][$k][$tShort]['pct'] ?? null);
    if ($kg !== null || $pct !== null) $r[] = ['Maquina' => $m, 'Kilos' => $kg, 'Porcentaje' => $pct];
}
$plan['tblMXPRBitWRMaquinaWR'] = ['cols' => ['Maquina','Kilos','Porcentaje'], 'rows' => $r];

$r = [];
foreach (['BASURA','RECORTE'] as $cpt) {
    $kilos = numv($P['wr_pacasmerma'][$cpt][$tShort]['kilos'] ?? null);
    $pacas = numv($P['wr_pacasmerma'][$cpt][$tShort]['pacas'] ?? null);
    if ($kilos !== null || $pacas !== null) $r[] = ['Concepto' => $cpt, 'Kilos' => $kilos, 'Pacas' => $pacas];
}
$plan['tblMXPRBitWRPacasMermaWR'] = ['cols' => ['Concepto','Kilos','Pacas'], 'rows' => $r];

$r = [];
foreach (array_merge($WR_MAQUINAS, ['OTROS']) as $m) { $k = slug($m);
    $v = nv($P['wr_nota'][$k] ?? null);
    if ($v !== null) $r[] = ['Maquina' => $m, 'Nota' => $v];
}
$plan['tblMXPRBitWRNotaWR'] = ['cols' => ['Maquina','Nota'], 'rows' => $r];

$r = [];
foreach (array_keys($PESO_TIPOS) as $tipo) {
    $arr = $P['peso'][$tipo][$tShort] ?? [];
    if (!is_array($arr)) continue;
    foreach ($arr as $idx => $celda) {
        $val = cellNum($celda);
        if ($val !== null) $r[] = ['Tipo' => $tipo, 'NoFila' => $idx + 1, 'Peso' => $val];
    }
}
$plan['tblMXPRBitPesoBolsaWR'] = ['cols' => ['Tipo','NoFila','Peso'], 'rows' => $r];

$rep = $P['reporte'][$tShort] ?? [];
$reporte = [
    'Operador' => nv($rep['operador'] ?? null),
    'Ayudante' => nv($rep['ayudante'] ?? null),
    'LineaConfort' => nv($rep['linea_confort'] ?? null),
    'Supervisor' => nv($rep['supervisor'] ?? null),
    'TrabajosDiversos' => nv($rep['trabajos_diversos'] ?? null),
    'Comentarios' => nv($rep['comentarios'] ?? null),
];
$plan['tblMXPRBitReporteWR'] = ['cols' => array_keys($reporte), 'rows' => array_filter($reporte, fn($x) => $x !== null) ? [$reporte] : []];

$resp = [
    'InspeccionoLimpieza' => nv($P['inspecciono'][$tSlug] ?? null),
    'ConductorWR' => nv($P['wr_conductor'][$tDigit] ?? null),
];
$plan['tblMXPRBitResponsablesWR'] = ['cols' => array_keys($resp), 'rows' => array_filter($resp, fn($x) => $x !== null) ? [$resp] : []];

/* ---------- Validación para no guardar un turno vacío ---------- */
$totalFilas = 0;
foreach ($plan as $def) { $totalFilas += count($def['rows']); }
if ($totalFilas === 0) {
    logwr("Sin datos: el turno $turno del folio $folio no trae ninguna fila.");
    out(['ok' => false, 'error' => 'No hay datos para guardar en este turno. Captura al menos un dato.']);
}

/* ---------- Ejecución con transacción ---------- */
if (sqlsrv_begin_transaction($conn) === false) {
    logwr('No se pudo iniciar transacción: '.errs());
    out(['ok' => false, 'error' => 'No se pudo iniciar la transacción: ' . errs()]);
}
try {
    $total = 0;
    foreach ($plan as $tabla => $def) {
        $keyCols = $KEYS[$tabla] ?? [];
        $total += upsertTabla($conn, $tabla, $folio, $def['cols'], $keyCols, $def['rows']);
    }
    sqlsrv_commit($conn);
    // Valores finales asignados para el log final de operaciones
    logwr("OK commit. Total insertados: $total");
    logwr('--- FIN guardado ---');
    out(['ok' => true, 'folio' => $folio, 'turno' => $turno, 'insertados' => $total]);
} catch (Exception $e) {
    sqlsrv_rollback($conn);
    logwr('ROLLBACK: '.$e->getMessage());
    out(['ok' => false, 'error' => $e->getMessage()]);
}