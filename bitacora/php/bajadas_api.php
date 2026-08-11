<?php
// php/bajadas_api.php — API de Bajadas WR (Líquidos). Acciones: crear | listar | zpl
// Cálculo encadenado en servidor: Piezas = Cajas*panalxcaja; AcumR = acum previo + Piezas; USTD = AcumR*factor
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once "../../conexion.php";

// ====== CONFIG ======
$DB_APP = "TLX004MXDB";   // bajadas + plantillas
$DB_MAQ = "TLX009MXDB";   // tblMaquinas
$DB_CLAVE = "TLX002MXDB";   // tblValeEClaves (panalxcaja, factor)
$PLANTILLA_BAJADA = "Etiqueta Bajada WR";
$DIAS_LIBERACION = 6;
$PLANTA = 'CUAUTITLÁN';
$PREFIJO_LOTE = 'PS';
// ====================

function jlog($m)
{
    file_put_contents(__DIR__ . '/bajadas_debug.log', date('c') . " $m\n", FILE_APPEND);
}
function salir($a)
{
    echo json_encode($a);
    exit;
}
function limpiarZ($s)
{
    return str_replace(['^', '~'], '', (string) $s);
}
function calcularTurno(int $h): int
{
    if ($h >= 6 && $h < 14)
        return 1;
    if ($h >= 14 && $h < 22)
        return 2;
    return 3;
}
function turnoTexto(int $t): string
{
    return [1 => '1er', 2 => '2do', 3 => '3er'][$t] ?? (string) $t;
}

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $in['accion'] ?? '';
$ibm = $_SESSION['ibm'] ?? $_SESSION['IBM'] ?? null;
$idMaquina = $_SESSION['idmaquina'] ?? null;

try {
    $cx = new ClassConexion();
    $connApp = $cx->conexion($DB_APP);
    if (!$connApp)
        salir(['ok' => false, 'error' => "Sin conexión a $DB_APP"]);
    if (!$idMaquina)
        salir(['ok' => false, 'error' => 'Sin máquina en sesión (idmaquina)']);

    // Nombre abreviado de máquina: TAN1-PSD -> TAN1
    $nombreMaquina = '';
    $connMaq = $cx->conexion($DB_MAQ);
    if ($connMaq) {
        $rsM = sqlsrv_query($connMaq, "SELECT NombreMaquina FROM tblMaquinas WHERE NoMaquina = ?", [$idMaquina]);
        if ($rsM && ($m = sqlsrv_fetch_array($rsM, SQLSRV_FETCH_ASSOC))) {
            $nombreMaquina = preg_replace('/-PSD$/i', '', trim($m['NombreMaquina'] ?? ''));
        }
    }

    // ---- Crear bajada (calcula piezas/acumR/ustd en el servidor) ----
    if ($accion === 'crear') {
        $clave = trim($in['clave'] ?? '');
        $producto = trim($in['producto'] ?? '');
        $idEnc = ($in['idEnc'] ?? '') === '' ? null : (int) $in['idEnc'];
        $cajas = ($in['cajas'] ?? '') === '' ? null : (int) $in['cajas'];
        if ($clave === '')
            salir(['ok' => false, 'error' => 'Elige un producto']);
        if ($idEnc === null)
            salir(['ok' => false, 'error' => 'Sin turno abierto (IdEncabezadoBit)']);
        if ($cajas === null || $cajas < 0)
            salir(['ok' => false, 'error' => 'Cajas no válidas']);

        // panalxcaja y factor de la clave
        $connClave = $cx->conexion($DB_CLAVE);
        if (!$connClave)
            salir(['ok' => false, 'error' => "Sin conexión a $DB_CLAVE"]);
        $rsC = sqlsrv_query($connClave, "SELECT panalxcaja, factor FROM tblValeEClaves WHERE NoClave = ?", [$clave]);
        if ($rsC === false) {
            jlog('clave ' . print_r(sqlsrv_errors(), true));
            salir(['ok' => false, 'error' => 'Error al leer clave']);
        }
        $cInfo = sqlsrv_fetch_array($rsC, SQLSRV_FETCH_ASSOC);
        if (!$cInfo)
            salir(['ok' => false, 'error' => 'La clave no existe en tblValeEClaves']);
        $panalxcaja = (float) $cInfo['panalxcaja'];
        $factor = (float) $cInfo['factor'];

        $now = new DateTime('now', new DateTimeZone('America/Mexico_City'));
        $fecha = $now->format('Y-m-d');
        $hora = $now->format('H:i:s');
        $turno = isset($in['turno']) && $in['turno'] !== '' ? (int) $in['turno'] : calcularTurno((int) $now->format('G'));

        // Cálculos encadenados
        $piezas = $cajas * $panalxcaja;   // se guarda para trazabilidad
        // AcumR ahora acumula CAJAS del MISMO producto en el MISMO encabezado
        $rsA = sqlsrv_query(
            $connApp,
            "SELECT TOP 1 acumR FROM tblMXPRBajadasFormulados
             WHERE claveProducto = ? AND IdEncabezadoBit = ? AND activo = 1
             ORDER BY id DESC",
            [$clave, $idEnc]
        );
        $acumPrev = ($rsA && ($rA = sqlsrv_fetch_array($rsA, SQLSRV_FETCH_ASSOC))) ? (float) $rA['acumR'] : 0.0;
        $acumR = $acumPrev + $cajas;      // <-- suma cajas, no piezas
        $ustd = $acumR * $factor;

        // Consecutivo palet por máquina + turno + fecha
        $rsP = sqlsrv_query(
            $connApp,
            "SELECT ISNULL(MAX(noPalet),0)+1 AS n FROM tblMXPRBajadasFormulados
             WHERE id_maquina=? AND turno=? AND fecha=? AND activo=1",
            [$idMaquina, $turno, $fecha]
        );
        if ($rsP === false) {
            jlog('palet ' . print_r(sqlsrv_errors(), true));
            salir(['ok' => false, 'error' => 'Error al calcular palet']);
        }
        $noPalet = ($rP = sqlsrv_fetch_array($rsP, SQLSRV_FETCH_ASSOC)) ? (int) $rP['n'] : 1;
        $paletTxt = str_pad((string) $noPalet, 3, '0', STR_PAD_LEFT);

        // folio = lote + palet
        $anioUlt = substr($now->format('Y'), -1);
        $julP = str_pad((string) ((int) $now->format('z') + 1), 3, '0', STR_PAD_LEFT);
        $folio = $PREFIJO_LOTE . ' ' . $anioUlt . ' ' . $julP . ' ' . $nombreMaquina . ' ' . $turno . ' ' . $paletTxt;

        $sql = "INSERT INTO tblMXPRBajadasFormulados
                  (id_maquina, maquina, noPalet, folio, IdEncabezadoBit, producto, claveProducto,
                   fecha, hora, turno, ibm, cajas, piezas, acumR, ustd, panalxcaja, factor)
                OUTPUT INSERTED.id
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $rs = sqlsrv_query(
            $connApp,
            $sql,
            [
                $idMaquina,
                $nombreMaquina,
                $noPalet,
                $folio,
                $idEnc,
                $producto,
                $clave,
                $fecha,
                $hora,
                $turno,
                $ibm,
                $cajas,
                $piezas,
                $acumR,
                $ustd,
                $panalxcaja,
                $factor
            ]
        );
        if ($rs === false) {
            jlog('crear ' . print_r(sqlsrv_errors(), true));
            salir(['ok' => false, 'error' => 'Error al guardar la bajada']);
        }
        $row = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);

        salir([
            'ok' => true,
            'bajada' => [
                'id' => (int) $row['id'],
                'producto' => $producto,
                'clave' => $clave,
                'palet' => $paletTxt,
                'folio' => $folio,
                'cajas' => $cajas,
                'piezas' => $piezas,
                'acumR' => $acumR,
                'ustd' => $ustd,
                'hora' => substr($hora, 0, 5),
                'turno' => $turno,
                'turnoTxt' => turnoTexto($turno)
            ]
        ]);
    }

    // ---- Listar ----
    if ($accion === 'listar') {
        $idEnc = $in['idEnc'] ?? '';
        $sql = "SELECT TOP 300 id, producto, claveProducto, noPalet, folio,
                       LEFT(CONVERT(varchar(8), hora, 108), 5) AS hora,
                       turno, cajas, piezas, acumR, ustd
                FROM tblMXPRBajadasFormulados WHERE activo = 1 AND id_maquina = ?";
        $params = [$idMaquina];
        if ($idEnc !== '') {
            $sql .= " AND IdEncabezadoBit = ?";
            $params[] = (int) $idEnc;
        }
        $sql .= " ORDER BY id DESC";
        $rs = sqlsrv_query($connApp, $sql, $params);
        if ($rs === false) {
            jlog('listar ' . print_r(sqlsrv_errors(), true));
            salir(['ok' => false, 'error' => 'Error al listar']);
        }
        $items = [];
        while ($r = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
            $r['turno'] = (int) $r['turno'];
            $r['turnoTxt'] = turnoTexto($r['turno']);
            $r['palet'] = str_pad((string) $r['noPalet'], 3, '0', STR_PAD_LEFT);
            $items[] = $r;
        }
        salir(['ok' => true, 'items' => $items]);
    }

    // ---- ZPL ----
    if ($accion === 'zpl') {
        $id = (int) ($in['id'] ?? 0);
        $rs = sqlsrv_query($connApp, "SELECT id, producto, claveProducto, maquina, noPalet, folio,
                    CONVERT(varchar(10), fecha, 23) AS fecha, LEFT(CONVERT(varchar(8), hora, 108), 5) AS hora,
                    turno, cajas FROM tblMXPRBajadasFormulados WHERE id = ? AND id_maquina = ? AND activo = 1", [$id, $idMaquina]);
        if ($rs === false) {
            jlog('zpl ' . print_r(sqlsrv_errors(), true));
            salir(['ok' => false, 'error' => 'Error al leer bajada']);
        }
        $b = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
        if (!$b)
            salir(['ok' => false, 'error' => 'Bajada no encontrada']);

        $rs = sqlsrv_query($connApp, "SELECT datos FROM tblMXPRPlantillaEtiqueta WHERE nombre = ? AND tipo='zpl' AND activo=1", [$PLANTILLA_BAJADA]);
        if ($rs === false) {
            jlog('zpl-tpl ' . print_r(sqlsrv_errors(), true));
            salir(['ok' => false, 'error' => 'Error al leer plantilla']);
        }
        $tpl = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
        if (!$tpl)
            salir(['ok' => false, 'error' => "Falta la plantilla ZPL '$PLANTILLA_BAJADA'"]);

        $prod = new DateTime($b['fecha']);
        $lib = (clone $prod)->modify("+{$DIAS_LIBERACION} days");
        $anio = substr($prod->format('Y'), -1);
        $julP = str_pad((string) ((int) $prod->format('z') + 1), 3, '0', STR_PAD_LEFT);
        $julL = str_pad((string) ((int) $lib->format('z') + 1), 3, '0', STR_PAD_LEFT);
        $palet = str_pad((string) $b['noPalet'], 3, '0', STR_PAD_LEFT);
        $lote = $PREFIJO_LOTE . '  ' . $anio . '  ' . $julP . '  ' . $b['maquina'] . '  ' . $b['turno'];

        // $vals = [
        //     'planta' => $PLANTA,
        //     'lote' => $lote,
        //     'palet' => $palet,
        //     'qr' => (string) $b['id'],
        //     'id' => (string) $b['id'],
        //     'julianProd' => $julP,
        //     'fechaProd' => $prod->format('d/m/y'),
        //     'julianLib' => $julL,
        //     'fechaLib' => $lib->format('d/m/y'),
        //     'hora' => $b['hora'],
        //     'cajas' => $b['cajas']
        // ];
        $vals = [
            'planta' => $PLANTA,
            'lote' => $lote,
            'palet' => $palet,
            'producto' => $b['producto'],       // <-- FALTABA: por esto salía vacío
            'clave' => $b['claveProducto'],  // por si lo quieres usar en la etiqueta
            'qr' => (string) $b['id'],
            'id' => (string) $b['id'],
            'julianProd' => $julP,
            'fechaProd' => $prod->format('d/m/y'),
            'julianLib' => $julL,
            'fechaLib' => $lib->format('d/m/y'),
            'hora' => $b['hora'],
            'cajas' => $b['cajas']
        ];
        $zpl = preg_replace_callback('/\{\{(\w+)\}\}/', fn($m) => isset($vals[$m[1]]) ? limpiarZ($vals[$m[1]]) : '', $tpl['datos']);
        preg_match('/\^PW(\d+)/', $zpl, $mpw);
        preg_match('/\^LL(\d+)/', $zpl, $mll);
        salir(['ok' => true, 'zpl' => $zpl, 'w' => round(($mpw[1] ?? 1218) / 203, 2), 'h' => round(($mll[1] ?? 812) / 203, 2)]);
    }

    // ---- Cancelar producto: borra (lógico) todas sus bajadas del encabezado actual ----
    if ($accion === 'cancelar') {
        $clave = trim($in['clave'] ?? '');
        $idEnc = ($in['idEnc'] ?? '') === '' ? null : (int) $in['idEnc'];
        if ($clave === '' || $idEnc === null)
            salir(['ok' => false, 'error' => 'Datos incompletos']);
        $sql = "UPDATE tblMXPRBajadasFormulados SET activo = 0
                WHERE claveProducto = ? AND IdEncabezadoBit = ? AND id_maquina = ? AND activo = 1";
        $rs = sqlsrv_query($connApp, $sql, [$clave, $idEnc, $idMaquina]);
        if ($rs === false) {
            jlog('cancelar ' . print_r(sqlsrv_errors(), true));
            salir(['ok' => false, 'error' => 'Error al cancelar']);
        }
        salir(['ok' => true, 'eliminados' => sqlsrv_rows_affected($rs)]);
    }

    // ---- Productos con bajadas en el encabezado actual (para rearmar los bloques) ----
    if ($accion === 'productos') {
        $idEnc = ($in['idEnc'] ?? '') === '' ? null : (int) $in['idEnc'];
        if ($idEnc === null)
            salir(['ok' => true, 'items' => []]);
        // orden por primera aparición, para asignarlos a los bloques en orden
        $sql = "SELECT claveProducto, MAX(producto) AS producto, MIN(id) AS primero
                FROM tblMXPRBajadasFormulados
                WHERE activo = 1 AND id_maquina = ? AND IdEncabezadoBit = ?
                GROUP BY claveProducto ORDER BY primero";
        $rs = sqlsrv_query($connApp, $sql, [$idMaquina, $idEnc]);
        if ($rs === false) {
            jlog('productos ' . print_r(sqlsrv_errors(), true));
            salir(['ok' => false, 'error' => 'Error al leer productos']);
        }
        $items = [];
        while ($r = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
            $items[] = ['clave' => $r['claveProducto'], 'producto' => $r['producto']];
        }
        salir(['ok' => true, 'items' => $items]);
    }

    salir(['ok' => false, 'error' => 'Acción no válida']);
} catch (Throwable $e) {
    jlog('excepcion ' . $e->getMessage());
    salir(['ok' => false, 'error' => 'Excepción en servidor']);
}