<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

function out($a){
    if (ob_get_length() !== false) { $s = ob_get_clean(); }
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode($a);
    exit;
}
register_shutdown_function(function(){
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>false, 'error'=>'Error fatal: '.$e['message']]);
    }
});

// Uso de slug() + $COLS_PRESION
require_once __DIR__ . '/catalogos.php';
require_once __DIR__ . '/../../conexion.php';
$conn = (new ClassConexion())->conexion('TLX004MXDB');
if (!$conn) out(['ok'=>false, 'error'=>'No se pudo conectar a la base de datos.']);

/* ---------- Helpers ---------- */
function q($conn, $sql, $params){
    $st = sqlsrv_query($conn, $sql, $params);
    if ($st === false) throw new Exception(print_r(sqlsrv_errors(), true));
    $rows = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) $rows[] = $row;
    // liberar stmt para proxima ejecucion
    sqlsrv_free_stmt($st);
    return $rows;
}
/* Obtener número sin ceros sobrantes validando posibles datos null*/
function fmt($v){
    if ($v === null) return '';
    if (is_numeric($v)) {
        $s = rtrim(rtrim(number_format((float)$v, 4, '.', ''), '0'), '.');
        return $s === '' ? '0' : $s;
    }
    return (string)$v;
}
/* Obtener texto validando posibles datos null */
function txt($v){ return $v === null ? '' : (string)$v; }

$folio = isset($_REQUEST['folio']) ? (int)$_REQUEST['folio'] : 0;
if ($folio <= 0) out(['ok'=>false, 'error'=>'Folio inválido o ausente.']);

try {
    /* ---- Turno del folio ---- */
    $enc = q($conn, "SELECT Turno FROM dbo.tblEncabezadoBitacora WHERE IdEncabezadoBitacora=?", [$folio]);
    if (!$enc) out(['ok'=>false, 'error'=>"El folio $folio no existe."]);
    $turno = (int)$enc[0]['Turno'];
    if ($turno < 1 || $turno > 3) out(['ok'=>false, 'error'=>"Turno inválido en el folio $folio."]);

    $tLong  = ['1ero', '2do', '3ero'][$turno - 1];
    $tShort = ['t1', 't2', 't3'][$turno - 1];
    $tSlug  = slug(['1ERO', '2DO', '3ERO'][$turno - 1]);
    $tDigit = (string)$turno;
    $tRecib = $turno === 1 ? '1er' : ($turno === 2 ? '2do' : null);

    // Almacenar el valor de cada input
    $F = [];

    /* ===== TAB 1 ===== */
    foreach (q($conn, "SELECT Equipo,Cantidad FROM dbo.tblMXPRBitDisponibleWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Equipo']);
        $F["disponible[$k][$tLong]"] = fmt($row['Cantidad']);
    }

    foreach (q($conn, "SELECT Equipo,Porcentaje,Kilogramos FROM dbo.tblMXPRBitRecuperadoWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Equipo']);
        $F["recup_pct[$k][$tLong]"] = fmt($row['Porcentaje']);
        $F["recup_kg[$k][$tLong]"]  = fmt($row['Kilogramos']);
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

    // 'DESECHOS' => 'desechos'
    $presRev = array_flip($COLS_PRESION);
    foreach (q($conn, "SELECT Compactador,Valor FROM dbo.tblMXPRBitPresionWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $ck = $presRev[$row['Compactador']] ?? null;
        if ($ck !== null) $F["presion[$tSlug][$ck]"] = txt($row['Valor']);
    }

    foreach (q($conn, "SELECT Area,Estado FROM dbo.tblMXPRBitOrdenLimpiezaWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Area']);
        $F["orden[$k][$tLong]"] = txt($row['Estado']);
    }

    $t = q($conn, "SELECT PacasLocales,PacasRecorte FROM dbo.tblMXPRBitTiemposWR WHERE IdEncabezadoBitacora=?", [$folio]);
    if ($t) {
        $F["pacas_locales[$tLong]"] = fmt($t[0]['PacasLocales']);
        $F["pacas_recorte[$tLong]"] = fmt($t[0]['PacasRecorte']);
    }

    foreach (q($conn, "SELECT Tipo,Cantidad FROM dbo.tblMXPRBitExportacionWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        if ($row['Tipo'] === 'PANAL')  $F["export_panal[$tLong]"]  = fmt($row['Cantidad']);
        if ($row['Tipo'] === 'TOALLA') $F["export_toalla[$tLong]"] = fmt($row['Cantidad']);
    }

    /* SAM del turno */
    $samMap = []; $maxNo = 0;
    foreach (q($conn, "SELECT NoValor,Valor FROM dbo.tblMXPRBitSamRecuperadoWR WHERE IdEncabezadoBitacora=? ORDER BY NoValor", [$folio]) as $row) {
        $no = (int)$row['NoValor'];
        if ($no > 0) { $samMap[$no] = fmt($row['Valor']); if ($no > $maxNo) $maxNo = $no; }
    }
    $sam = [];
    for ($i = 1; $i <= $maxNo; $i++) $sam[] = $samMap[$i] ?? '';

    /* ===== TAB 2 ===== */
    foreach (q($conn, "SELECT Tipo,Kilos FROM dbo.tblMXPRBitWRMermaWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $F["wr_merma[{$row['Tipo']}][$tShort]"] = fmt($row['Kilos']);
    }

    foreach (q($conn, "SELECT Tolva,Kilos FROM dbo.tblMXPRBitWRSamTolvaWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $F["wr_sam[{$row['Tolva']}][$tShort]"] = fmt($row['Kilos']);
    }

    foreach (q($conn, "SELECT Planta,Cantidad FROM dbo.tblMXPRBitWRPacasAlimentadasWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Planta']);
        $F["wr_pacas_alim[$k][$tShort]"] = fmt($row['Cantidad']);
    }

    foreach (q($conn, "SELECT Maquina,Kilos,Porcentaje FROM dbo.tblMXPRBitWRMaquinaWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Maquina']);
        $F["wr_maq[$k][$tShort][kg]"]  = fmt($row['Kilos']);
        $F["wr_maq[$k][$tShort][pct]"] = fmt($row['Porcentaje']);
    }

    foreach (q($conn, "SELECT Concepto,Kilos,Pacas FROM dbo.tblMXPRBitWRPacasMermaWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $c = $row['Concepto'];
        $F["wr_pacasmerma[$c][$tShort][kilos]"] = fmt($row['Kilos']);
        $F["wr_pacasmerma[$c][$tShort][pacas]"] = fmt($row['Pacas']);
    }

    foreach (q($conn, "SELECT Maquina,Nota FROM dbo.tblMXPRBitWRNotaWR WHERE IdEncabezadoBitacora=?", [$folio]) as $row) {
        $k = slug($row['Maquina']);
        $F["wr_nota[$k]"] = txt($row['Nota']);
    }

    /* ===== TAB 3 · Peso de bolsas ===== */
    $peso = ['PANAL' => [], 'TOALLA' => []];
    $tmp  = ['PANAL' => [], 'TOALLA' => []];
    foreach (q($conn, "SELECT Tipo,NoFila,Peso FROM dbo.tblMXPRBitPesoBolsaWR WHERE IdEncabezadoBitacora=? ORDER BY Tipo,NoFila", [$folio]) as $row) {
        $tp = $row['Tipo'];
        if (isset($tmp[$tp])) $tmp[$tp][(int)$row['NoFila']] = fmt($row['Peso']);
    }
    foreach (['PANAL','TOALLA'] as $tp) {
        if (!$tmp[$tp]) continue;
        $max = max(array_keys($tmp[$tp]));
        for ($i = 1; $i <= $max; $i++) $peso[$tp][] = $tmp[$tp][$i] ?? '';
    }

    /* ===== TAB 4 · Reporte final ===== */
    $rep = q($conn, "SELECT Operador,Ayudante,LineaConfort,Supervisor,TrabajosDiversos,Comentarios FROM dbo.tblMXPRBitReporteWR WHERE IdEncabezadoBitacora=?", [$folio]);
    if ($rep) {
        $r = $rep[0];
        $F["reporte[$tShort][operador]"]          = txt($r['Operador']);
        $F["reporte[$tShort][ayudante]"]          = txt($r['Ayudante']);
        $F["reporte[$tShort][linea_confort]"]     = txt($r['LineaConfort']);
        $F["reporte[$tShort][supervisor]"]        = txt($r['Supervisor']);
        $F["reporte[$tShort][trabajos_diversos]"] = txt($r['TrabajosDiversos']);
        $F["reporte[$tShort][comentarios]"]       = txt($r['Comentarios']);
    }

    /* ===== Responsables ===== */
    $resp = q($conn, "SELECT InspeccionoLimpieza,ConductorWR FROM dbo.tblMXPRBitResponsablesWR WHERE IdEncabezadoBitacora=?", [$folio]);
    if ($resp) {
        $r = $resp[0];
        $F["inspecciono[$tSlug]"]    = txt($r['InspeccionoLimpieza']);
        $F["wr_conductor[$tDigit]"]  = txt($r['ConductorWR']);
    }

    out(['ok'=>true, 'folio'=>$folio, 'turno'=>$turno, 'fields'=>$F, 'sam'=>$sam, 'peso'=>$peso]);

} catch (Exception $e) {
    out(['ok'=>false, 'error'=>$e->getMessage()]);
}