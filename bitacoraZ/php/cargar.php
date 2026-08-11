<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

function out($a)
{
    if (ob_get_length() !== false) {
        $s = ob_get_clean();
    }
    if (!headers_sent())
        header('Content-Type: application/json; charset=utf-8');
    echo json_encode($a);
    exit;
}
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level())
            ob_end_clean();
        if (!headers_sent())
            header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Error fatal: ' . $e['message']]);
    }
});

// Uso de slug() + $COLS_PRESION
require_once __DIR__ . '/catalogos.php';
require_once __DIR__ . '/../../conexion.php';
$conn = (new ClassConexion())->conexion('TLX004MXDB');
if (!$conn)
    out(['ok' => false, 'error' => 'No se pudo conectar a la base de datos.']);

/* ---------- Helpers ---------- */
function q($conn, $sql, $params)
{
    $st = sqlsrv_query($conn, $sql, $params);
    if ($st === false)
        throw new Exception(print_r(sqlsrv_errors(), true));
    $rows = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC))
        $rows[] = $row;
    sqlsrv_free_stmt($st);
    return $rows;
}
/* Número sin ceros sobrantes; null -> '' ; 0 -> '0' */
function fmt($v)
{
    if ($v === null)
        return '';
    if (is_numeric($v)) {
        $s = rtrim(rtrim(number_format((float) $v, 4, '.', ''), '0'), '.');
        return $s === '' ? '0' : $s;
    }
    return (string) $v;
}
/* Texto; null -> '' */
function txt($v)
{
    return $v === null ? '' : (string) $v;
}

/* Lee TODO lo de un folio y lo devuelve con los nombres del turno indicado.
   Devuelve ['fields'=>[...], 'sam'=>[...], 'peso'=>['PANAL'=>[],'TOALLA'=>[]]] */
function leerTurno($conn, $folio, $turno)
{
    global $COLS_PRESION;
    $tLong = ['1ero', '2do', '3ero'][$turno - 1];
    $tShort = ['t1', 't2', 't3'][$turno - 1];
    $tSlug = slug(['1ERO', '2DO', '3ERO'][$turno - 1]);
    $tRecib = $turno === 1 ? '1er' : ($turno === 2 ? '2do' : null);
    $F = [];

    /* ===== Pestaña 1 ===== */
    foreach (q($conn, "SELECT Equipo,Cantidad FROM dbo.tblMXPRBitDisponibleWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Equipo']);
        $F["disponible[$k][$tLong]"] = fmt($row['Cantidad']);
    }
    // Recuperado ahora es captura manual, pero seguimos leyendo lo guardado
    foreach (q($conn, "SELECT Equipo,Porcentaje,Kilogramos FROM dbo.tblMXPRBitRecuperadoWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Equipo']);
        $F["recup_pct[$k][$tLong]"] = fmt($row['Porcentaje']);
        $F["recup_kg[$k][$tLong]"] = fmt($row['Kilogramos']);
    }
    if ($tRecib !== null) {
        foreach (q($conn, "SELECT Planta,Cantidad FROM dbo.tblMXPRBitPacasRecibidasWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
            $k = slug($row['Planta']);
            $F["pacas_recibidas[$k][$tRecib]"] = fmt($row['Cantidad']);
        }
    }
    foreach (q($conn, "SELECT Planta,Cantidad FROM dbo.tblMXPRBitPacasAlimentadasWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Planta']);
        $F["pacas_alimentadas[$k][$tLong]"] = fmt($row['Cantidad']);
    }
    $presRev = array_flip($COLS_PRESION);
    foreach (q($conn, "SELECT Compactador,Valor FROM dbo.tblMXPRBitPresionWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $ck = $presRev[$row['Compactador']] ?? null;
        if ($ck !== null)
            $F["presion[$tSlug][$ck]"] = txt($row['Valor']);
    }
    foreach (q($conn, "SELECT Area,Estado FROM dbo.tblMXPRBitOrdenLimpiezaWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Area']);
        $F["orden[$k][$tLong]"] = txt($row['Estado']);
    }
    // SAM del turno (valores variables)
    $samMap = [];
    $maxNo = 0;
    foreach (q($conn, "SELECT NoValor,Valor FROM dbo.tblMXPRBitSamRecuperadoWR WHERE IdEncabezadoBitacora=? ORDER BY NoValor", [$folio]) as $row) {
        $no = (int) $row['NoValor'];
        if ($no > 0) {
            $samMap[$no] = fmt($row['Valor']);
            if ($no > $maxNo)
                $maxNo = $no;
        }
    }
    $sam = [];
    for ($i = 1; $i <= $maxNo; $i++)
        $sam[] = $samMap[$i] ?? '';

    // Merma máquinas (se alimenta del peso; guardamos su total por turno)
    foreach (q($conn, "SELECT Tipo,Kilos FROM dbo.tblMXPRBitWRMermaWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $F["wr_merma[{$row['Tipo']}][$tShort]"] = fmt($row['Kilos']);
    }

    // Pacas merma (tabla 5, ahora vive en la pestaña 1)
    foreach (q($conn, "SELECT Concepto,Kilos,Pacas FROM dbo.tblMXPRBitWRPacasMermaWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $c = $row['Concepto'];
        $F["wr_pacasmerma[$c][$tShort][kilos]"] = fmt($row['Kilos']);
        $F["wr_pacasmerma[$c][$tShort][pacas]"] = fmt($row['Pacas']);
    }

    /* ===== Peso de bolsas ===== */
    $peso = ['PANAL' => [], 'TOALLA' => []];
    $tmp = ['PANAL' => [], 'TOALLA' => []];
    foreach (q($conn, "SELECT Tipo,NoFila,Peso FROM dbo.tblMXPRBitPesoBolsaWR WHERE IdEncabezadoBitacora=? ORDER BY Tipo,NoFila", [$folio]) as $row) {
        $tp = $row['Tipo'];
        if (isset($tmp[$tp]))
            $tmp[$tp][(int) $row['NoFila']] = fmt($row['Peso']);
    }
    foreach (['PANAL', 'TOALLA'] as $tp) {
        if (!$tmp[$tp])
            continue;
        $max = max(array_keys($tmp[$tp]));
        for ($i = 1; $i <= $max; $i++)
            $peso[$tp][] = $tmp[$tp][$i] ?? '';
    }

    /* ===== Reporte ===== */
    $rep = q($conn, "SELECT Operador,Ayudante,LineaConfort,Supervisor,TrabajosDiversos,Comentarios FROM dbo.tblMXPRBitReporteWR WHERE IdEncabezadoBitacora=?", [$folio]);
    if ($rep) {
        $r = $rep[0];
        $F["reporte[$tShort][operador]"] = txt($r['Operador']);
        $F["reporte[$tShort][ayudante]"] = txt($r['Ayudante']);
        $F["reporte[$tShort][linea_confort]"] = txt($r['LineaConfort']);
        $F["reporte[$tShort][supervisor]"] = txt($r['Supervisor']);
        $F["reporte[$tShort][trabajos_diversos]"] = txt($r['TrabajosDiversos']);
        $F["reporte[$tShort][comentarios]"] = txt($r['Comentarios']);
    }

    /* ===== Responsables (solo inspeccionó; conductor eliminado) ===== */
    $resp = q($conn, "SELECT InspeccionoLimpieza FROM dbo.tblMXPRBitResponsablesWR WHERE IdEncabezadoBitacora=?", [$folio]);
    if ($resp) {
        $F["inspecciono[$tSlug]"] = txt($resp[0]['InspeccionoLimpieza']);
    }

    return ['fields' => $F, 'sam' => $sam, 'peso' => $peso];
}

$folioReq = isset($_REQUEST['folio']) ? (int) $_REQUEST['folio'] : 0;
if ($folioReq <= 0)
    out(['ok' => false, 'error' => 'Folio inválido o ausente.']);

try {
    /* ---- Datos del folio pedido: fecha, turno activo y máquina ---- */
    $enc = q($conn, "SELECT Fecha,Turno,NoMaquina FROM dbo.tblEncabezadoBitacora WHERE IdEncabezadoBitacora=?", [$folioReq]);
    if (!$enc)
        out(['ok' => false, 'error' => "El folio $folioReq no existe."]);
    $turnoActivo = (int) $enc[0]['Turno'];
    if ($turnoActivo < 1 || $turnoActivo > 3)
        out(['ok' => false, 'error' => "Turno inválido en el folio $folioReq."]);

    $fecha = $enc[0]['Fecha'];
    $fechaStr = ($fecha instanceof DateTime) ? $fecha->format('Y-m-d') : substr((string) $fecha, 0, 10);
    $maq = $enc[0]['NoMaquina'];

    /* ---- Todos los folios del mismo día y máquina (un folio por turno) ---- */
    $dia = q($conn, "SELECT IdEncabezadoBitacora, Turno FROM dbo.tblEncabezadoBitacora WHERE CAST(Fecha AS date) = ? AND NoMaquina = ?", [$fechaStr, $maq]);

    $F = [];
    $sam = [];
    $peso = [];
    foreach ($dia as $d) {
        $t = (int) $d['Turno'];
        if ($t < 1 || $t > 3)
            continue;
        $res = leerTurno($conn, $d['IdEncabezadoBitacora'], $t);
        $F = array_merge($F, $res['fields']);
        if (!empty($res['sam']))
            $sam[(string) $t] = $res['sam'];
        $peso[(string) $t] = $res['peso'];
    }

    out([
        'ok' => true,
        'folio' => $folioReq,
        'turno' => $turnoActivo,   // turno editable (los demás son solo lectura)
        'fields' => $F,
        'sam' => $sam,           // { "1":[...], "2":[...], "3":[...] }
        'peso' => $peso,          // { "1":{PANAL,TOALLA}, ... }
    ]);

} catch (Exception $e) {
    out(['ok' => false, 'error' => $e->getMessage()]);
}