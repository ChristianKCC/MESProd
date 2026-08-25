<?php
// php/certificados_api.php — Certificados de Calidad Líquidos y Formulados (FORM-63297)
// Flujo: INS ∥ FIS -> (ambas autorizadas) MIC -> LISTO -> ENVIADO_GT -> APROBADO / RECHAZADO
// Estatus etapa: 0=captura, 1=enviado, 2=autorizado, 3=rechazado
//
// Estados del palet — SOLO DOS BANDERAS:
//   PROCESO             -> Cuarentena 1 · Rechazado 0   (creación, inspección, identificación)
//   RECHAZADO_ORIGEN    -> Cuarentena 1 · Rechazado 1   (rechazo sin iniciar certificación)
//   RECHAZADO_GERENCIA  -> Cuarentena 0 · Rechazado 1   (rechazo en la firma final)
//   LIBERADO            -> Cuarentena 0 · Rechazado 0   (aprobado por Gerencia)
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once "../../conexion.php";

// ====== CONFIG ======
$DB_APP = "TLX004MXDB";
$VISTA_CLAVES = "vwMXPRClaveMaquina";   // NoClave, Producto, Categoria, Descripcion_Articulo

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
    'presentacion' => null,
];
// ====================

function salir($a)
{
    echo json_encode($a);
    exit;
}
function jlog($m)
{
    file_put_contents(__DIR__ . '/certificados_debug.log', date('c') . " $m\n", FILE_APPEND);
}

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $in['accion'] ?? '';
$ibm = $_SESSION['ibm'] ?? $_SESSION['IBM'] ?? null;
if (!$ibm)
    salir(['ok' => false, 'error' => 'Sin IBM en sesión']);

$cx = new ClassConexion();
$conn = $cx->conexion($DB_APP);
if (!$conn)
    salir(['ok' => false, 'error' => "Sin conexión a $DB_APP"]);


/* =============================================================
   HELPERS
   ============================================================= */

function q($conn, $sql, $params = [])
{
    $rs = sqlsrv_query($conn, $sql, $params);
    if ($rs === false) {
        $err = sqlsrv_errors();
        jlog("SQL: $sql\nPARAMS: " . print_r($params, true) . "\nERROR: " . print_r($err, true));
        salir([
            'ok' => false,
            'error' => 'Error SQL: ' . ($err[0]['message'] ?? 'desconocido'),
            'sqlstate' => $err[0]['SQLSTATE'] ?? null,
            'code' => $err[0]['code'] ?? null,
        ]);
    }
    return $rs;
}
function fila($rs)
{
    return sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
}
function filas($rs)
{
    $o = [];
    while ($r = fila($rs))
        $o[] = $r;
    return $o;
}
function fmtFecha($v, $formato = 'Y-m-d')
{
    if ($v instanceof DateTime)
        return $v->format($formato);
    return $v ? (string) $v : null;
}

function bitacora($conn, $idCert, $modulo, $accion, $etapa, $detalle, $ibm, $nombre)
{
    sqlsrv_query(
        $conn,
        "INSERT INTO tblMXPRCertificadoBitacoraFR
           (BIT_idCertificado, BIT_modulo, BIT_accion, BIT_etapa, BIT_detalle, BIT_ibm, BIT_nombre)
         VALUES (?,?,?,?,?,?,?)",
        [$idCert, $modulo, $accion, $etapa, $detalle, $ibm, $nombre]
    );
}

/** Cambia el estado de un palet a través del procedimiento único.
 *  Estados: PROCESO | LIBERADO | RECHAZADO_GERENCIA | RECHAZADO_ORIGEN */
function estadoPalet($conn, $idBajada, $estado)
{
    q($conn, "EXEC sp_MXPR_EstadoPalet @idBajada = ?, @estado = ?", [(int) $idBajada, $estado]);
}

/** Palets vigentes del folio+clave que quedan fuera de la selección y aún no están en un certificado. */
function paletsExcluidos($conn, $folio, $clave, $seleccionados)
{
    $sel = array_map('intval', (array) $seleccionados);
    $rows = filas(q(
        $conn,
        "SELECT b.id, b.noPalet, b.cajas
         FROM tblMXPRBajadasFormulados b
         LEFT JOIN tblMXPRCertificadoPaletFR p ON p.PAL_idBajada = b.id
         WHERE b.vigente = 1 AND b.folio = ? AND b.claveProducto = ?
           AND p.PAL_id IS NULL AND b.Rechazado = 0",
        [$folio, $clave]
    ));
    $out = [];
    foreach ($rows as $r) {
        if (!in_array((int) $r['id'], $sel, true))
            $out[] = $r;
    }
    return $out;
}

/** Guarda el motivo de los palets que quedaron fuera del certificado. */
function registrarExclusiones($conn, $idCert, $folio, $clave, $seleccionados, $motivo, $ibm, $nombre)
{
    if (trim((string) $motivo) === '')
        return 0;
    $excluidos = paletsExcluidos($conn, $folio, $clave, $seleccionados);
    foreach ($excluidos as $e) {
        q(
            $conn,
            "INSERT INTO tblMXPRCertificadoExclusionFR
                (EXC_idCertificado, EXC_folio, EXC_clave, EXC_idBajada, EXC_noPalet, EXC_cajas,
                 EXC_motivo, EXC_ibm, EXC_nombre)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [
                $idCert,
                $folio,
                $clave,
                (int) $e['id'],
                (int) $e['noPalet'],
                (int) $e['cajas'],
                $motivo,
                $ibm,
                $nombre
            ]
        );
    }
    return count($excluidos);
}

/** Marca como resueltas las exclusiones de los palets que ya se integraron. */
function resolverExclusiones($conn, $palets)
{
    $ids = array_map('intval', (array) $palets);
    if (!count($ids))
        return;
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    q(
        $conn,
        "UPDATE tblMXPRCertificadoExclusionFR SET EXC_resuelta = 1
         WHERE EXC_resuelta = 0 AND EXC_idBajada IN ($marcas)",
        $ids
    );
}

function parametrosClave($conn, $clave)
{
    $rs = sqlsrv_query(
        $conn,
        "SELECT PAR_variable, PAR_unidad, PAR_minimo, PAR_objetivo, PAR_maximo, PAR_metodo
         FROM tblMXPRCertificadoParametroFR
         WHERE LTRIM(RTRIM(PAR_clave)) = LTRIM(RTRIM(?)) AND PAR_activo = 1",
        [$clave]
    );
    $out = [];
    if ($rs !== false) {
        while ($r = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
            $out[strtoupper(trim($r['PAR_variable']))] = [
                'unidad' => $r['PAR_unidad'],
                'minimo' => $r['PAR_minimo'] !== null ? (float) $r['PAR_minimo'] : null,
                'objetivo' => $r['PAR_objetivo'] !== null ? (float) $r['PAR_objetivo'] : null,
                'maximo' => $r['PAR_maximo'] !== null ? (float) $r['PAR_maximo'] : null,
                'metodo' => $r['PAR_metodo'],
            ];
        }
    }
    return $out;
}

function veredictoParametro($valor, $p)
{
    if ($valor === null || $valor === '' || !$p)
        return '';
    $v = (float) preg_replace('/[<>≤≥\s]/u', '', (string) $valor);
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
        return 'Cumple min';
    if ($obj !== null && $v > $obj)
        return 'Cumple max';
    return 'Cumple';
}

function organolepticasClave($conn, $clave)
{
    $rs = sqlsrv_query(
        $conn,
        "SELECT ORG_id, ORG_tipo, ORG_especificacion FROM tblMXPRCertificadoOrganolepticaFR
         WHERE LTRIM(RTRIM(ORG_clave)) = LTRIM(RTRIM(?)) AND ORG_activo = 1
         ORDER BY ORG_tipo, ORG_orden, ORG_id",
        [$clave]
    );
    $out = ['ASPECTO' => [], 'COLOR' => [], 'OLOR' => []];
    if ($rs !== false) {
        while ($r = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
            $t = strtoupper(trim($r['ORG_tipo']));
            if (!isset($out[$t]))
                $out[$t] = [];
            $out[$t][] = $r['ORG_especificacion'];
        }
    }
    return $out;
}

function opcionesTipo($conn, $tipo)
{
    $rs = sqlsrv_query(
        $conn,
        "SELECT OPC_valor, OPC_esFalla FROM tblMXPRCertificadoOpcionFR
         WHERE OPC_tipo = ? AND OPC_activo = 1 ORDER BY OPC_orden",
        [$tipo]
    );
    $out = [];
    if ($rs !== false) {
        while ($r = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
            $out[] = ['valor' => $r['OPC_valor'], 'esFalla' => (bool) $r['OPC_esFalla']];
    }
    return $out;
}

function datosClavePDF($conn, $vista, $clave)
{
    $rs = sqlsrv_query(
        $conn,
        "SELECT TOP 1 Producto, Categoria, Descripcion_Articulo FROM $vista WHERE NoClave = ?",
        [$clave]
    );
    if ($rs === false) {
        jlog('vista claves ' . print_r(sqlsrv_errors(), true));
        return null;
    }
    return sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC) ?: null;
}

function especsClave($conn, $clave, $default)
{
    $rs = sqlsrv_query(
        $conn,
        "SELECT * FROM tblMXPRCertificadoEspecsFR WHERE ESP_clave = ? AND ESP_activo = 1",
        [$clave]
    );
    $e = ($rs !== false) ? sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC) : null;
    if (!$e)
        return $default;
    return [
        'visMin' => $e['ESP_visMin'] ?? $default['visMin'],
        'visObj' => $e['ESP_visObj'] ?? $default['visObj'],
        'visMax' => $e['ESP_visMax'] ?? $default['visMax'],
        'phMin' => $e['ESP_phMin'] ?? $default['phMin'],
        'phObj' => $e['ESP_phObj'] ?? $default['phObj'],
        'phMax' => $e['ESP_phMax'] ?? $default['phMax'],
        'denMin' => $e['ESP_denMin'] ?? $default['denMin'],
        'denObj' => $e['ESP_denObj'] ?? $default['denObj'],
        'denMax' => $e['ESP_denMax'] ?? $default['denMax'],
        'aspecto' => $e['ESP_aspecto'] ?: $default['aspecto'],
        'olor' => $e['ESP_olor'] ?: $default['olor'],
        'presentacion' => $e['ESP_presentacion'] ?? null,
    ];
}

// ---------- Roles ----------
$roles = array_column(filas(q(
    $conn,
    "SELECT PER_rol FROM tblMXPRCertificadoPerfilFR WHERE PER_ibm = ? AND PER_activo = 1",
    [$ibm]
)), 'PER_rol');
$perfil = fila(q(
    $conn,
    "SELECT TOP 1 PER_nombre, PER_noemp FROM tblMXPRCertificadoPerfilFR WHERE PER_ibm = ? AND PER_activo = 1",
    [$ibm]
));
$nombreUsuario = $perfil['PER_nombre'] ?? $ibm;

function tieneRol($roles, ...$r)
{
    return count(array_intersect($roles, $r)) > 0;
}
function exigeConfigurador($roles)
{
    if (!tieneRol($roles, 'CONFIGURADOR'))
        salir(['ok' => false, 'error' => 'No tienes permiso para configurar este módulo']);
}
function exigeConsultaLiberacion($roles)
{
    if (!tieneRol($roles, 'CONSULTA_LIBERACION', 'GERENTE', 'CONFIGURADOR'))
        salir(['ok' => false, 'error' => 'No tienes permiso para consultar la liberación por folio']);
}

$ETAPAS = [
    'inspeccion' => ['tabla' => 'tblMXPRCertificadoInspeccionFR', 'pre' => 'INS', 'rolCap' => 'INSPECCION', 'rolSup' => 'SUP_INSPECCION'],
    'fisicoquimico' => ['tabla' => 'tblMXPRCertificadoFisicoquimicoFR', 'pre' => 'FIS', 'rolCap' => 'FISICOQUIMICO', 'rolSup' => 'SUP_FISICOQUIMICO'],
    'microbiologia' => ['tabla' => 'tblMXPRCertificadoMicrobiologiaFR', 'pre' => 'MIC', 'rolCap' => 'MICROBIOLOGIA', 'rolSup' => 'SUP_MICROBIOLOGIA'],
];

function estatusEtapa($conn, $ETAPAS, $etapa, $idCert)
{
    $t = $ETAPAS[$etapa];
    $p = $t['pre'];
    $r = fila(q($conn, "SELECT {$p}_estatus AS e FROM {$t['tabla']} WHERE {$p}_idCertificado = ?", [$idCert]));
    return $r ? (int) $r['e'] : -1;
}

function puedeVer($roles, $etapa, $ETAPAS)
{
    if (tieneRol($roles, 'GERENTE', 'MICROBIOLOGIA', 'SUP_MICROBIOLOGIA'))
        return true;
    $t = $ETAPAS[$etapa];
    return tieneRol($roles, $t['rolCap'], $t['rolSup']);
}

// Catálogos permitidos en Configuraciones
$CATALOGOS = [
    'parametros' => [
        'tabla' => 'tblMXPRCertificadoParametroFR',
        'pk' => 'PAR_id',
        'pre' => 'PAR',
        'campos' => ['clave', 'variable', 'unidad', 'minimo', 'objetivo', 'maximo', 'metodo'],
        'orden' => 'PAR_clave, PAR_variable',
    ],
    'organolepticas' => [
        'tabla' => 'tblMXPRCertificadoOrganolepticaFR',
        'pk' => 'ORG_id',
        'pre' => 'ORG',
        'campos' => ['clave', 'tipo', 'especificacion', 'orden'],
        'orden' => 'ORG_clave, ORG_tipo, ORG_orden',
    ],
    'defectos' => [
        'tabla' => 'tblMXPRCertificadoDefectoFR',
        'pk' => 'DEF_id',
        'pre' => 'DEF',
        'campos' => ['atributo', 'nombre', 'orden'],
        'orden' => 'DEF_atributo, DEF_orden',
    ],
    'mo' => [
        'tabla' => 'tblMXPRCertificadoMOFR',
        'pk' => 'MOC_id',
        'pre' => 'MOC',
        'campos' => ['nombre', 'tipo', 'especificacion', 'unidad', 'metodo', 'orden'],
        'orden' => 'MOC_orden',
    ],
    'opciones' => [
        'tabla' => 'tblMXPRCertificadoOpcionFR',
        'pk' => 'OPC_id',
        'pre' => 'OPC',
        'campos' => ['tipo', 'valor', 'esFalla', 'orden'],
        'orden' => 'OPC_tipo, OPC_orden',
    ],
    'perfiles' => [
        'tabla' => 'tblMXPRCertificadoPerfilFR',
        'pk' => 'PER_id',
        'pre' => 'PER',
        'campos' => ['ibm', 'nombre', 'noemp', 'rol'],
        'orden' => 'PER_rol, PER_nombre',
    ],
];

/** Siguiente consecutivo del campo 'orden', con el ámbito de cada catálogo. */
function siguienteOrden($conn, $cat, $vals)
{
    switch ($cat) {
        case 'defectos':
            $sql = "SELECT ISNULL(MAX(DEF_orden),0)+1 AS n FROM tblMXPRCertificadoDefectoFR
                    WHERE DEF_atributo = ? AND DEF_activo = 1";
            $p = [$vals['atributo'] ?? ''];
            break;
        case 'opciones':
            $sql = "SELECT ISNULL(MAX(OPC_orden),0)+1 AS n FROM tblMXPRCertificadoOpcionFR
                    WHERE OPC_tipo = ? AND OPC_activo = 1";
            $p = [$vals['tipo'] ?? ''];
            break;
        case 'mo':
            $sql = "SELECT ISNULL(MAX(MOC_orden),0)+1 AS n FROM tblMXPRCertificadoMOFR WHERE MOC_activo = 1";
            $p = [];
            break;
        case 'organolepticas':
            $sql = "SELECT ISNULL(MAX(ORG_orden),0)+1 AS n FROM tblMXPRCertificadoOrganolepticaFR
                    WHERE LTRIM(RTRIM(ORG_clave)) = LTRIM(RTRIM(?)) AND ORG_tipo = ? AND ORG_activo = 1";
            $p = [$vals['clave'] ?? '', $vals['tipo'] ?? ''];
            break;
        default:
            return 1;
    }
    $rs = sqlsrv_query($conn, $sql, $p);
    $r = $rs ? sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC) : null;
    return (int) ($r['n'] ?? 1);
}


/* =============================================================
   ACCIONES
   ============================================================= */

// ------------------------------------------------------------
// perfil
// ------------------------------------------------------------
if ($accion === 'perfil') {
    salir(['ok' => true, 'ibm' => $ibm, 'nombre' => $nombreUsuario, 'roles' => $roles]);
}

// ------------------------------------------------------------
// grupos — folios con palets disponibles
// ------------------------------------------------------------
// if ($accion === 'grupos') {
//     $rs = q(
//         $conn,
//         "SELECT  b.folio                AS folio,
//                  b.claveProducto        AS clave,
//                  MAX(b.producto)        AS producto,
//                  b.maquina              AS maquina,
//                  b.turno                AS turno,
//                  b.IdEncabezadoBit      AS idEncabezado,
//                  CONVERT(varchar(10), MIN(b.fecha), 23) AS fecha,
//                  COUNT(*)               AS totalPalets,
//                  SUM(b.cajas)           AS totalCajas,
//                  SUM(CASE WHEN p.PAL_id IS NULL THEN 0 ELSE 1 END) AS paletsUsados
//          FROM tblMXPRBajadasFormulados b
//          LEFT JOIN tblMXPRCertificadoPaletFR p ON p.PAL_idBajada = b.id
//          WHERE b.vigente = 1
//          GROUP BY b.folio, b.claveProducto, b.maquina, b.turno, b.IdEncabezadoBit
//          HAVING COUNT(*) > SUM(CASE WHEN p.PAL_id IS NULL THEN 0 ELSE 1 END)
//          ORDER BY MIN(b.fecha) DESC, b.folio DESC"
//     );

//     $items = [];
//     foreach (filas($rs) as $r) {
//         $r['totalPalets'] = (int) $r['totalPalets'];
//         $r['paletsUsados'] = (int) $r['paletsUsados'];
//         $r['disponibles'] = $r['totalPalets'] - $r['paletsUsados'];
//         $r['turno'] = (int) $r['turno'];
//         $items[] = $r;
//     }
//     salir(['ok' => true, 'items' => $items]);
// }

if ($accion === 'grupos') {
    $rs = q(
        $conn,
        "SELECT  b.folio                AS folio,
                 b.claveProducto        AS clave,
                 MAX(b.producto)        AS producto,
                 b.maquina              AS maquina,
                 b.turno                AS turno,
                 b.IdEncabezadoBit      AS idEncabezado,
                 CONVERT(varchar(10), MIN(b.fecha), 23) AS fecha,
                 COUNT(*)               AS totalPalets,
                 SUM(b.cajas)           AS totalCajas,
                 SUM(CASE WHEN p.PAL_id IS NULL THEN 0 ELSE 1 END) AS paletsUsados
         FROM tblMXPRBajadasFormulados b
         LEFT JOIN tblMXPRCertificadoPaletFR p ON p.PAL_idBajada = b.id
         WHERE b.vigente = 1
           AND b.Rechazado = 0                    -- <-- fuera los rechazados
         GROUP BY b.folio, b.claveProducto, b.maquina, b.turno, b.IdEncabezadoBit
         HAVING COUNT(*) > SUM(CASE WHEN p.PAL_id IS NULL THEN 0 ELSE 1 END)
         ORDER BY MIN(b.fecha) DESC, b.folio DESC"
    );

    $items = [];
    foreach (filas($rs) as $r) {
        $r['totalPalets'] = (int) $r['totalPalets'];
        $r['paletsUsados'] = (int) $r['paletsUsados'];
        $r['disponibles'] = $r['totalPalets'] - $r['paletsUsados'];
        $r['turno'] = (int) $r['turno'];
        $items[] = $r;
    }
    salir(['ok' => true, 'items' => $items]);
}

// ------------------------------------------------------------
// palets — detalle del folio para el modal
// ------------------------------------------------------------
if ($accion === 'palets') {
    $folio = trim($in['folio'] ?? '');
    $clave = trim($in['clave'] ?? '');
    $turno = ($in['turno'] ?? '') === '' ? null : (int) $in['turno'];
    if ($folio === '' || $clave === '')
        salir(['ok' => false, 'error' => 'Folio y clave requeridos']);

    $sql = "SELECT  b.id      AS idBajada, b.noPalet AS noPalet, b.folio AS folio,
                    b.maquina AS maquina,  b.turno   AS turno,   b.cajas AS cajas,
                    b.piezas  AS piezas,
                    b.Cuarentena, b.Rechazado,
                    CONVERT(varchar(10), b.fecha, 23) AS fecha,
                    LEFT(CONVERT(varchar(8), b.hora, 108), 5) AS hora,
                    p.PAL_idCertificado AS idCertUsado,
                    p.PAL_estatus       AS estatusPalet
            FROM tblMXPRBajadasFormulados b
            LEFT JOIN tblMXPRCertificadoPaletFR p ON p.PAL_idBajada = b.id
            WHERE b.vigente = 1 AND b.folio = ? AND b.claveProducto = ?
            AND b.Rechazado = 0";
    $params = [$folio, $clave];
    if ($turno !== null) {
        $sql .= " AND b.turno = ?";
        $params[] = $turno;
    }
    $sql .= " ORDER BY b.noPalet";

    $items = [];
    $totalCajas = 0;
    $disponibles = 0;
    foreach (filas(q($conn, $sql, $params)) as $r) {
        $r['idBajada'] = (int) $r['idBajada'];
        $r['noPalet'] = (int) $r['noPalet'];
        $r['cajas'] = (int) $r['cajas'];
        $r['usado'] = $r['idCertUsado'] !== null;
        $r['estadoBits'] = (int) $r['Rechazado'] === 1
            ? ((int) $r['Cuarentena'] === 1 ? 'RECHAZADO EN ORIGEN' : 'RECHAZADO')
            : ((int) $r['Cuarentena'] === 1 ? 'EN PROCESO' : 'LIBERADO');
        if (!$r['usado']) {
            $disponibles++;
            $totalCajas += $r['cajas'];
        }
        $items[] = $r;
    }

    $p = fila(q(
        $conn,
        "SELECT TOP 1 producto, maquina, turno FROM tblMXPRBajadasFormulados
         WHERE folio = ? AND claveProducto = ? AND vigente = 1 ORDER BY id DESC",
        [$folio, $clave]
    ));

    $certExistente = fila(q(
        $conn,
        "SELECT TOP 1 CER_id, CER_estatus, CER_version, CER_totalPalets
         FROM tblMXPRCertificadoFR
         WHERE CER_folio = ? AND CER_clave = ? AND CER_activo = 1
         ORDER BY CER_id DESC",
        [$folio, $clave]
    ));

    salir([
        'ok' => true,
        'folio' => $folio,
        'clave' => $clave,
        'producto' => $p['producto'] ?? '',
        'maquina' => $p['maquina'] ?? '',
        'turno' => $p['turno'] ?? $turno,
        'items' => $items,
        'disponibles' => $disponibles,
        'cajasDisponibles' => $totalCajas,
        'exclusiones' => filas(q(
            $conn,
            "SELECT EXC_noPalet AS palet, EXC_motivo AS motivo,
                    CONVERT(varchar(16), EXC_fecha, 120) AS fecha, EXC_nombre AS quien
             FROM tblMXPRCertificadoExclusionFR
             WHERE EXC_folio = ? AND EXC_clave = ? AND EXC_resuelta = 0
             ORDER BY EXC_noPalet",
            [$folio, $clave]
        )),
        'certExistente' => $certExistente ? [
            'id' => (int) $certExistente['CER_id'],
            'estatus' => $certExistente['CER_estatus'],
            'version' => (int) $certExistente['CER_version'],
            'palets' => (int) $certExistente['CER_totalPalets'],
        ] : null,
    ]);
}

// ------------------------------------------------------------
// iniciar — crea o INTEGRA al certificado del folio+clave
// ------------------------------------------------------------
if ($accion === 'iniciar') {
    $folio = trim($in['folio'] ?? '');
    $clave = trim($in['clave'] ?? '');
    $palets = $in['palets'] ?? [];
    $motivoExclusion = trim($in['motivoExclusion'] ?? '');

    if ($folio === '' || $clave === '')
        salir(['ok' => false, 'error' => 'Folio y clave requeridos']);
    if (!is_array($palets) || !count($palets))
        salir(['ok' => false, 'error' => 'Selecciona al menos un palet']);

    // Si quedan palets disponibles fuera de la selección, el motivo es obligatorio
    $excluidos = paletsExcluidos($conn, $folio, $clave, $palets);
    if (count($excluidos) && $motivoExclusion === '')
        salir([
            'ok' => false,
            'requiereMotivo' => true,
            'excluidos' => count($excluidos),
            'error' => 'Indica el motivo por el que se excluyen ' . count($excluidos) . ' palet(s)'
        ]);

    $marcas = implode(',', array_fill(0, count($palets), '?'));
    $params = array_merge([$folio, $clave], array_map('intval', $palets));
    $rows = filas(q(
        $conn,
        "SELECT b.id, b.noPalet, b.folio, b.cajas, b.producto, b.maquina, b.turno,
                b.IdEncabezadoBit, CONVERT(varchar(10), b.fecha, 23) AS fechaProd
         FROM tblMXPRBajadasFormulados b
         LEFT JOIN tblMXPRCertificadoPaletFR p ON p.PAL_idBajada = b.id
         WHERE b.vigente = 1 AND b.folio = ? AND b.claveProducto = ?
           AND p.PAL_id IS NULL AND b.id IN ($marcas)",
        $params
    ));
    if (!count($rows))
        salir(['ok' => false, 'error' => 'Los palets seleccionados ya están en un certificado']);

    $primero = $rows[0];
    $nuevosPalets = count($rows);
    $nuevasCajas = array_sum(array_column($rows, 'cajas'));

    $cert = fila(q(
        $conn,
        "SELECT TOP 1 CER_id, CER_estatus, CER_version FROM tblMXPRCertificadoFR
         WHERE CER_folio = ? AND CER_clave = ? AND CER_activo = 1 ORDER BY CER_id DESC",
        [$folio, $clave]
    ));

    if ($cert) {
        // ---------- INTEGRAR AL EXISTENTE ----------
        $id = (int) $cert['CER_id'];
        $version = (int) $cert['CER_version'];
        $reabierto = false;

        if (in_array($cert['CER_estatus'], ['APROBADO', 'RECHAZADO'], true)) {
            $version++;
            $reabierto = true;
            q(
                $conn,
                "UPDATE tblMXPRCertificadoFR
                 SET CER_estatus = 'LISTO', CER_version = ?, CER_fechaReapertura = GETDATE(),
                     CER_ibmGerente = NULL, CER_nombreGerente = NULL, CER_fechaFirma = NULL,
                     CER_conclusion = NULL, CER_observacionesGT = NULL, CER_motivoRechazoGT = NULL
                 WHERE CER_id = ?",
                [$version, $id]
            );
        }

        foreach ($rows as $r) {
            q(
                $conn,
                "INSERT INTO tblMXPRCertificadoPaletFR
                    (PAL_idCertificado, PAL_idBajada, PAL_noPalet, PAL_folioBajada, PAL_cajas,
                     PAL_estatus, PAL_versionCert)
                 VALUES (?,?,?,?,?, 'PENDIENTE', ?)",
                [$id, (int) $r['id'], (int) $r['noPalet'], $r['folio'], (int) $r['cajas'], $version]
            );
            // Con dos banderas el palet SIGUE en Cuarentena 1 / Rechazado 0
            estadoPalet($conn, $r['id'], 'PROCESO');
        }

        q(
            $conn,
            "UPDATE c SET c.CER_totalPalets = t.n, c.CER_totalCajas = t.cajas
             FROM tblMXPRCertificadoFR c
             CROSS APPLY (SELECT COUNT(*) AS n, ISNULL(SUM(PAL_cajas),0) AS cajas
                          FROM tblMXPRCertificadoPaletFR WHERE PAL_idCertificado = c.CER_id) t
             WHERE c.CER_id = ?",
            [$id]
        );

        // Los palets que ahora sí entraron dejan de estar excluidos
        resolverExclusiones($conn, $palets);
        registrarExclusiones($conn, $id, $folio, $clave, $palets, $motivoExclusion, $ibm, $nombreUsuario);

        bitacora(
            $conn,
            $id,
            'CERTIFICADO',
            $reabierto ? 'REABRE' : 'AGREGA',
            null,
            "Se integraron $nuevosPalets palet(s) · $nuevasCajas cajas "
            . ($reabierto ? " · reabierto en versión $version, requiere nueva validación" : ''),
            $ibm,
            $nombreUsuario
        );

        salir([
            'ok' => true,
            'id' => $id,
            'integrado' => true,
            'reabierto' => $reabierto,
            'version' => $version,
            'palets' => $nuevosPalets,
            'cajas' => $nuevasCajas,
        ]);
    }

    // ---------- CERTIFICADO NUEVO ----------
    $esp = especsClave($conn, $clave, $ESPECS_DEFAULT);

    $rs = q(
        $conn,
        "INSERT INTO tblMXPRCertificadoFR
            (CER_folio, CER_clave, CER_producto, CER_presentacion, CER_lote,
             CER_maquina, CER_turno, CER_idEncabezado, CER_totalPalets, CER_totalCajas)
         OUTPUT INSERTED.CER_id VALUES (?,?,?,?,?,?,?,?,?,?)",
        [
            $folio,
            $clave,
            $primero['producto'],
            $esp['presentacion'],
            $primero['folio'],
            $primero['maquina'],
            (int) $primero['turno'],
            $primero['IdEncabezadoBit'],
            $nuevosPalets,
            $nuevasCajas
        ]
    );
    $id = (int) fila($rs)['CER_id'];

    foreach ($rows as $r) {
        q(
            $conn,
            "INSERT INTO tblMXPRCertificadoPaletFR
                (PAL_idCertificado, PAL_idBajada, PAL_noPalet, PAL_folioBajada, PAL_cajas,
                 PAL_estatus, PAL_versionCert)
             VALUES (?,?,?,?,?, 'PENDIENTE', 1)",
            [$id, (int) $r['id'], (int) $r['noPalet'], $r['folio'], (int) $r['cajas']]
        );
        estadoPalet($conn, $r['id'], 'PROCESO');
    }

    q($conn, "INSERT INTO tblMXPRCertificadoInspeccionFR (INS_idCertificado, INS_fechaFabricacion) VALUES (?,?)", [$id, $primero['fechaProd']]);
    q($conn, "INSERT INTO tblMXPRCertificadoFisicoquimicoFR (FIS_idCertificado) VALUES (?)", [$id]);
    q($conn, "INSERT INTO tblMXPRCertificadoMicrobiologiaFR (MIC_idCertificado) VALUES (?)", [$id]);

    registrarExclusiones($conn, $id, $folio, $clave, $palets, $motivoExclusion, $ibm, $nombreUsuario);

    bitacora(
        $conn,
        $id,
        'CERTIFICADO',
        'INICIAR',
        null,
        "Folio $folio · clave $clave · $nuevosPalets palet(s) · $nuevasCajas cajas ",
        $ibm,
        $nombreUsuario
    );

    salir(['ok' => true, 'id' => $id, 'integrado' => false, 'palets' => $nuevosPalets, 'cajas' => $nuevasCajas]);
}

// ------------------------------------------------------------
// catalogos
// ------------------------------------------------------------
if ($accion === 'catalogos') {
    $clave = trim($in['clave'] ?? '');

    $defectos = ['SEGURIDAD' => [], 'DESEMPENO' => [], 'APARIENCIA' => []];
    foreach (
        filas(q($conn, "SELECT DEF_id, DEF_atributo, DEF_nombre FROM tblMXPRCertificadoDefectoFR
                        WHERE DEF_activo = 1 ORDER BY DEF_atributo, DEF_orden")) as $d
    ) {
        $defectos[$d['DEF_atributo']][] = ['id' => (int) $d['DEF_id'], 'nombre' => $d['DEF_nombre']];
    }

    $mo = [];
    foreach (
        filas(q($conn, "SELECT MOC_id, MOC_nombre, MOC_tipo, MOC_especificacion, MOC_unidad, MOC_metodo
                        FROM tblMXPRCertificadoMOFR WHERE MOC_activo = 1 ORDER BY MOC_orden")) as $m
    ) {
        $mo[] = [
            'id' => (int) $m['MOC_id'],
            'nombre' => $m['MOC_nombre'],
            'tipo' => $m['MOC_tipo'],
            'especificacion' => $m['MOC_especificacion'],
            'unidad' => $m['MOC_unidad'],
            'metodo' => $m['MOC_metodo'],
        ];
    }

    $parametros = $clave !== '' ? parametrosClave($conn, $clave) : [];
    $organolepticas = $clave !== '' ? organolepticasClave($conn, $clave) : [];

    $faltanParametros = [];
    foreach (['DENSIDAD', 'VISCOSIDAD', 'PH'] as $v)
        if (!isset($parametros[$v]))
            $faltanParametros[] = $v;

    $faltanOrganolepticas = [];
    foreach (['ASPECTO', 'COLOR', 'OLOR'] as $v)
        if (empty($organolepticas[$v]))
            $faltanOrganolepticas[] = $v;

    salir([
        'ok' => true,
        'clave' => $clave,
        'defectos' => $defectos,
        'mo' => $mo,
        'parametros' => $parametros,
        'organolepticas' => $organolepticas,
        'faltanParametros' => $faltanParametros,
        'faltanOrganolepticas' => $faltanOrganolepticas,
        'faltantes' => array_merge($faltanParametros, $faltanOrganolepticas),
        'opciones' => [
            'ATRIBUTO' => opcionesTipo($conn, 'ATRIBUTO'),
            'ASPECTO' => opcionesTipo($conn, 'ASPECTO'),
            'COLOR' => opcionesTipo($conn, 'COLOR'),
            'OLOR' => opcionesTipo($conn, 'OLOR'),
            'MO' => opcionesTipo($conn, 'MO'),
        ],
    ]);
}

// ------------------------------------------------------------
// espacio
// ------------------------------------------------------------
if ($accion === 'espacio') {
    $certs = filas(q(
        $conn,
        "SELECT CER_id, CER_folio, CER_clave, CER_producto, CER_estatus,
                CER_maquina, CER_turno, CER_totalPalets, CER_totalCajas, CER_version AS version,
                (SELECT COUNT(*) FROM tblMXPRCertificadoPaletFR
                  WHERE PAL_idCertificado = CER_id AND PAL_estatus = 'PENDIENTE') AS paletsPendientes,
                (SELECT COUNT(*) FROM tblMXPRCertificadoPaletFR
                  WHERE PAL_idCertificado = CER_id AND PAL_estatus = 'LIBERADO')  AS paletsLiberados
         FROM tblMXPRCertificadoFR
         WHERE CER_activo = 1 AND CER_estatus IN ('ABIERTO','LISTO','ENVIADO_GT')
         ORDER BY CER_id DESC"
    ));

    $items = [];
    foreach ($certs as $c) {
        $id = (int) $c['CER_id'];
        $etapas = [];
        $tieneAccion = false;

        foreach ($ETAPAS as $etapa => $t) {
            $p = $t['pre'];
            $e = fila(q($conn, "SELECT * FROM {$t['tabla']} WHERE {$p}_idCertificado = ?", [$id]));
            $st = $e ? (int) $e["{$p}_estatus"] : 0;

            $bloqueado = false;
            if ($etapa === 'microbiologia') {
                $bloqueado = !(estatusEtapa($conn, $ETAPAS, 'inspeccion', $id) === 2
                    && estatusEtapa($conn, $ETAPAS, 'fisicoquimico', $id) === 2);
            }

            $visible = puedeVer($roles, $etapa, $ETAPAS);
            $puedeCapturar = tieneRol($roles, $t['rolCap']) && in_array($st, [0, 3], true) && !$bloqueado;
            $puedeAutorizar = tieneRol($roles, $t['rolSup']) && $st === 1;
            if ($puedeCapturar || $puedeAutorizar)
                $tieneAccion = true;

            $etapas[$etapa] = [
                'estatus' => $st,
                'bloqueado' => $bloqueado,
                'visible' => $visible,
                'puedeCapturar' => $puedeCapturar,
                'puedeAutorizar' => $puedeAutorizar,
                'capturo' => $visible ? ($e["{$p}_nombreCaptura"] ?? null) : null,
                'fechaCaptura' => $visible ? fmtFecha($e["{$p}_fechaCaptura"] ?? null, 'd/m/Y H:i') : null,
                'autorizo' => $visible ? ($e["{$p}_nombreAutoriza"] ?? null) : null,
                'fechaAutoriza' => $visible ? fmtFecha($e["{$p}_fechaAutoriza"] ?? null, 'd/m/Y H:i') : null,
                'motivoRechazo' => $visible ? ($e["{$p}_motivoRechazo"] ?? null) : null,
            ];
        }

        // Solo Micro (supervisor) o Gerencia cierran el flujo
        $accionCert = null;
        if ($c['CER_estatus'] === 'LISTO' && tieneRol($roles, 'SUP_MICROBIOLOGIA', 'GERENTE')) {
            $accionCert = 'enviar_gt';
            $tieneAccion = true;
        }
        if ($c['CER_estatus'] === 'ENVIADO_GT' && tieneRol($roles, 'GERENTE')) {
            $accionCert = 'validacion';
            $tieneAccion = true;
        }

        $items[] = [
            'id' => $id,
            'folio' => $c['CER_folio'],
            'clave' => $c['CER_clave'],
            'producto' => $c['CER_producto'],
            'estatusCert' => $c['CER_estatus'],
            'accionCert' => $accionCert,
            'tieneAccion' => $tieneAccion,
            'etapas' => $etapas,
            'maquina' => $c['CER_maquina'],
            'turno' => $c['CER_turno'],
            'totalPalets' => (int) $c['CER_totalPalets'],
            'totalCajas' => (int) $c['CER_totalCajas'],
            'version' => (int) $c['version'],
            'paletsPendientes' => (int) $c['paletsPendientes'],
            'paletsLiberados' => (int) $c['paletsLiberados'],
        ];
    }

    usort($items, fn($a, $b) => ($b['tieneAccion'] <=> $a['tieneAccion']) ?: ($b['id'] <=> $a['id']));
    salir(['ok' => true, 'items' => $items, 'roles' => $roles, 'ibm' => $ibm]);
}

// ------------------------------------------------------------
// detalle
// ------------------------------------------------------------
if ($accion === 'detalle') {
    $id = (int) ($in['id'] ?? 0);
    $c = fila(q($conn, "SELECT * FROM tblMXPRCertificadoFR WHERE CER_id = ? AND CER_activo = 1", [$id]));
    if (!$c)
        salir(['ok' => false, 'error' => 'Certificado no encontrado']);

    $esp = especsClave($conn, $c['CER_clave'], $ESPECS_DEFAULT);
    $out = ['cert' => $c, 'especs' => $esp, 'etapas' => []];

    $out['defectosSel'] = array_map(
        fn($d) => ['id' => (int) $d['DFD_idDefecto'], 'atributo' => $d['DFD_atributo']],
        filas(q($conn, "SELECT DFD_idDefecto, DFD_atributo FROM tblMXPRCertificadoDefectoDetFR
                        WHERE DFD_idCertificado = ?", [$id]))
    );

    $out['moSel'] = [];
    foreach (
        filas(q($conn, "SELECT MOD_idMO, MOD_resultado FROM tblMXPRCertificadoMODetFR
                        WHERE MOD_idCertificado = ?", [$id])) as $m
    ) {
        $out['moSel'][(int) $m['MOD_idMO']] = $m['MOD_resultado'];
    }

    foreach ($ETAPAS as $etapa => $t) {
        if (!puedeVer($roles, $etapa, $ETAPAS))
            continue;
        $p = $t['pre'];
        $e = fila(q($conn, "SELECT * FROM {$t['tabla']} WHERE {$p}_idCertificado = ?", [$id]));
        if ($e) {
            foreach (["{$p}_fechaFabricacion", "{$p}_fechaCaducidad"] as $campo) {
                if (array_key_exists($campo, $e))
                    $e[$campo] = fmtFecha($e[$campo]);
            }
        }
        $out['etapas'][$etapa] = $e;
    }

    $out['editable'] = [];
    foreach ($ETAPAS as $etapa => $t) {
        $st = estatusEtapa($conn, $ETAPAS, $etapa, $id);
        $ed = tieneRol($roles, $t['rolCap']) && in_array($st, [0, 3], true);
        if ($etapa === 'microbiologia' && $ed) {
            $ed = estatusEtapa($conn, $ETAPAS, 'inspeccion', $id) === 2
                && estatusEtapa($conn, $ETAPAS, 'fisicoquimico', $id) === 2;
        }
        $out['editable'][$etapa] = $ed;
    }

    $bj = fila(q(
        $conn,
        "SELECT TOP 1 CONVERT(varchar(10), fecha, 23) AS fechaProd
         FROM tblMXPRBajadasFormulados
         WHERE folio = ? AND claveProducto = ? AND vigente = 1 ORDER BY id DESC",
        [$c['CER_folio'], $c['CER_clave']]
    ));
    $out['fechaProduccion'] = $bj['fechaProd'] ?? null;

    $out['ok'] = true;
    salir($out);
}

// ------------------------------------------------------------
// guardar
// ------------------------------------------------------------
if ($accion === 'guardar') {
    $id = (int) ($in['id'] ?? 0);
    $etapa = $in['etapa'] ?? '';
    $enviar = !empty($in['enviar']);
    if (!isset($ETAPAS[$etapa]))
        salir(['ok' => false, 'error' => 'Etapa no válida']);
    $t = $ETAPAS[$etapa];
    $p = $t['pre'];

    if (!tieneRol($roles, $t['rolCap']))
        salir(['ok' => false, 'error' => 'Sin permiso para capturar esta etapa']);

    $st = estatusEtapa($conn, $ETAPAS, $etapa, $id);
    if (!in_array($st, [0, 3], true))
        salir(['ok' => false, 'error' => 'La etapa ya no es editable']);

    if ($etapa === 'microbiologia') {
        if (estatusEtapa($conn, $ETAPAS, 'inspeccion', $id) !== 2 || estatusEtapa($conn, $ETAPAS, 'fisicoquimico', $id) !== 2)
            salir(['ok' => false, 'error' => 'Microbiología requiere Inspección y Fisicoquímico autorizados']);
    }

    $d = $in['datos'] ?? [];
    $v = function ($k) use ($d) {
        $x = $d[$k] ?? '';
        return ($x === '' || $x === null) ? null : $x;
    };
    $nuevoEstatus = $enviar ? 1 : 0;

    if ($etapa === 'inspeccion') {
        q(
            $conn,
            "UPDATE tblMXPRCertificadoInspeccionFR SET
                INS_fechaFabricacion=?, INS_fechaCaducidad=?, INS_seguridad=?, INS_desempeno=?, INS_apariencia=?,
                INS_observaciones=?, INS_estatus=?, INS_ibmCaptura=?, INS_nombreCaptura=?, INS_fechaCaptura=GETDATE(),
                INS_motivoRechazo=NULL
             WHERE INS_idCertificado=?",
            [
                $v('fechaFabricacion'),
                $v('fechaCaducidad'),
                $v('seguridad'),
                $v('desempeno'),
                $v('apariencia'),
                $v('observaciones'),
                $nuevoEstatus,
                $ibm,
                $nombreUsuario,
                $id
            ]
        );

        q($conn, "DELETE FROM tblMXPRCertificadoDefectoDetFR WHERE DFD_idCertificado = ?", [$id]);
        foreach (($d['defectos'] ?? []) as $def) {
            $idDef = (int) ($def['id'] ?? 0);
            if (!$idDef)
                continue;
            q(
                $conn,
                "INSERT INTO tblMXPRCertificadoDefectoDetFR
                    (DFD_idCertificado, DFD_idDefecto, DFD_atributo, DFD_comentario, DFD_ibmCaptura)
                 VALUES (?,?,?,?,?)",
                [$id, $idDef, $def['atributo'] ?? '', $def['comentario'] ?? null, $ibm]
            );
        }

    } elseif ($etapa === 'fisicoquimico') {
        q(
            $conn,
            "UPDATE tblMXPRCertificadoFisicoquimicoFR SET
                FIS_viscosidad=?, FIS_ph=?, FIS_densidad=?,
                FIS_aspecto=?, FIS_color=?, FIS_olor=?, FIS_aspectoColor=?,
                FIS_observaciones=?, FIS_estatus=?, FIS_ibmCaptura=?, FIS_nombreCaptura=?, FIS_fechaCaptura=GETDATE(),
                FIS_motivoRechazo=NULL
             WHERE FIS_idCertificado=?",
            [
                $v('viscosidad'),
                $v('ph'),
                $v('densidad'),
                $v('aspecto'),
                $v('color'),
                $v('olor'),
                $v('aspecto'),
                $v('observaciones'),
                $nuevoEstatus,
                $ibm,
                $nombreUsuario,
                $id
            ]
        );

    } else { // microbiologia — TAMC/TYMC son texto: aceptan "<1", ">10"
        q(
            $conn,
            "UPDATE tblMXPRCertificadoMicrobiologiaFR SET
                MIC_tamc=?, MIC_tymc=?, MIC_patogenos=?,
                MIC_observaciones=?, MIC_estatus=?, MIC_ibmCaptura=?, MIC_nombreCaptura=?, MIC_fechaCaptura=GETDATE(),
                MIC_motivoRechazo=NULL
             WHERE MIC_idCertificado=?",
            [
                $v('tamc'),
                $v('tymc'),
                $v('resumenMO'),
                $v('observaciones'),
                $nuevoEstatus,
                $ibm,
                $nombreUsuario,
                $id
            ]
        );

        q($conn, "DELETE FROM tblMXPRCertificadoMODetFR WHERE MOD_idCertificado = ?", [$id]);
        foreach (($d['mo'] ?? []) as $m) {
            $idMO = (int) ($m['id'] ?? 0);
            $res = trim((string) ($m['resultado'] ?? ''));
            if (!$idMO || $res === '')
                continue;
            q(
                $conn,
                "INSERT INTO tblMXPRCertificadoMODetFR
                    (MOD_idCertificado, MOD_idMO, MOD_resultado, MOD_ibmCaptura)
                 VALUES (?,?,?,?)",
                [$id, $idMO, $res, $ibm]
            );
        }
    }

    bitacora(
        $conn,
        $id,
        'CERTIFICADO',
        $enviar ? 'ENVIA' : 'CAPTURA',
        strtoupper($etapa),
        $enviar ? 'Enviado a autorización' : 'Borrador guardado',
        $ibm,
        $nombreUsuario
    );

    salir(['ok' => true, 'estatus' => $nuevoEstatus]);
}

// ------------------------------------------------------------
// autorizar
// ------------------------------------------------------------
if ($accion === 'autorizar') {
    $id = (int) ($in['id'] ?? 0);
    $etapa = $in['etapa'] ?? '';
    $ok = !empty($in['aprobar']);
    $motivo = trim($in['motivo'] ?? '');
    if (!isset($ETAPAS[$etapa]))
        salir(['ok' => false, 'error' => 'Etapa no válida']);
    $t = $ETAPAS[$etapa];
    $p = $t['pre'];

    if (!tieneRol($roles, $t['rolSup']))
        salir(['ok' => false, 'error' => 'Sin permiso para autorizar esta etapa']);
    if (estatusEtapa($conn, $ETAPAS, $etapa, $id) !== 1)
        salir(['ok' => false, 'error' => 'La etapa no está en autorización']);
    if (!$ok && $motivo === '')
        salir(['ok' => false, 'error' => 'Indica el motivo de rechazo']);

    q(
        $conn,
        "UPDATE {$t['tabla']} SET {$p}_estatus=?, {$p}_ibmAutoriza=?, {$p}_nombreAutoriza=?,
              {$p}_fechaAutoriza=GETDATE(), {$p}_motivoRechazo=? WHERE {$p}_idCertificado=?",
        [$ok ? 2 : 3, $ibm, $nombreUsuario, $ok ? null : $motivo, $id]
    );

    if ($ok && $etapa === 'microbiologia') {
        q($conn, "UPDATE tblMXPRCertificadoFR SET CER_fechaEmision = CONVERT(date, GETDATE()), CER_estatus = 'LISTO'
                  WHERE CER_id = ?", [$id]);
    }

    bitacora(
        $conn,
        $id,
        'CERTIFICADO',
        $ok ? 'AUTORIZA' : 'RECHAZA',
        strtoupper($etapa),
        $ok ? null : $motivo,
        $ibm,
        $nombreUsuario
    );

    salir(['ok' => true]);
}

// ------------------------------------------------------------
// enviar_gt — solo Micro (supervisor) o Gerencia
// ------------------------------------------------------------
if ($accion === 'enviar_gt') {
    $id = (int) ($in['id'] ?? 0);

    if (!tieneRol($roles, 'SUP_MICROBIOLOGIA', 'GERENTE'))
        salir(['ok' => false, 'error' => 'Solo Microbiología o Gerencia Técnica pueden enviar el certificado']);

    foreach (array_keys($ETAPAS) as $etapa) {
        if (estatusEtapa($conn, $ETAPAS, $etapa, $id) !== 2)
            salir(['ok' => false, 'error' => 'Aún hay etapas sin autorizar']);
    }
    q($conn, "UPDATE tblMXPRCertificadoFR SET CER_estatus = 'ENVIADO_GT' WHERE CER_id = ? AND CER_estatus = 'LISTO'", [$id]);

    bitacora(
        $conn,
        $id,
        'CERTIFICADO',
        'ENVIA_GT',
        'GERENTE',
        'Certificado enviado a validación final',
        $ibm,
        $nombreUsuario
    );
    salir(['ok' => true]);
}

// ------------------------------------------------------------
// validar_gt — firma final: libera o rechaza los palets pendientes
// ------------------------------------------------------------
if ($accion === 'validar_gt') {
    $id = (int) ($in['id'] ?? 0);
    $ok = !empty($in['aprobar']);
    $motivo = trim($in['motivo'] ?? '');
    $observaciones = trim($in['observaciones'] ?? '');
    if (!tieneRol($roles, 'GERENTE'))
        salir(['ok' => false, 'error' => 'Solo Gerencia Técnica puede validar']);

    $c = fila(q($conn, "SELECT CER_estatus FROM tblMXPRCertificadoFR WHERE CER_id = ?", [$id]));
    if (!$c || $c['CER_estatus'] !== 'ENVIADO_GT')
        salir(['ok' => false, 'error' => 'El certificado no está en validación']);
    if (!$ok && $motivo === '')
        salir(['ok' => false, 'error' => 'Indica el motivo de rechazo']);

    $conclusion = $ok
        ? 'Confirmo a la especificación definida que el producto es aceptable para su distribución a KCM, S.A.B. de C.V.'
        : 'Confirmo a la especificación definida que el producto no es aceptable para su distribución a KCM, S.A.B. de C.V.';

    q(
        $conn,
        "UPDATE tblMXPRCertificadoFR SET CER_estatus=?, CER_conclusion=?, CER_observacionesGT=?,
              CER_ibmGerente=?, CER_nombreGerente=?, CER_fechaFirma=GETDATE(), CER_motivoRechazoGT=?
         WHERE CER_id=?",
        [
            $ok ? 'APROBADO' : 'RECHAZADO',
            $conclusion,
            $observaciones !== '' ? $observaciones : null,
            $ibm,
            $nombreUsuario,
            $ok ? null : $motivo,
            $id
        ]
    );

    // 1) Los palets pendientes se resuelven con el resultado de esta firma
    $nuevoEstatusPalet = $ok ? 'LIBERADO' : 'RECHAZADO';
    q(
        $conn,
        "UPDATE tblMXPRCertificadoPaletFR
         SET PAL_estatus = ?, PAL_fechaResolucion = GETDATE(), PAL_ibmResuelve = ?
         WHERE PAL_idCertificado = ? AND PAL_estatus = 'PENDIENTE'",
        [$nuevoEstatusPalet, $ibm, $id]
    );

    // 2) Las banderas de cada palet resuelto en ESTA firma
    $paletsResueltos = filas(q(
        $conn,
        "SELECT PAL_idBajada FROM tblMXPRCertificadoPaletFR
         WHERE PAL_idCertificado = ? AND PAL_estatus = ?",
        [$id, $nuevoEstatusPalet]
    ));
    foreach ($paletsResueltos as $p) {
        estadoPalet($conn, $p['PAL_idBajada'], $ok ? 'LIBERADO' : 'RECHAZADO_GERENCIA');
    }

    $res = fila(q(
        $conn,
        "SELECT SUM(CASE WHEN PAL_estatus = 'LIBERADO'  THEN 1 ELSE 0 END) AS lib,
                SUM(CASE WHEN PAL_estatus = 'RECHAZADO' THEN 1 ELSE 0 END) AS rec
         FROM tblMXPRCertificadoPaletFR WHERE PAL_idCertificado = ?",
        [$id]
    ));
    bitacora(
        $conn,
        $id,
        'CERTIFICADO',
        $ok ? 'LIBERA' : 'NO_LIBERA',
        'GERENTE',
        'Liberados: ' . ($res['lib'] ?? 0) . ' · Rechazados: ' . ($res['rec'] ?? 0),
        $ibm,
        $nombreUsuario
    );

    bitacora(
        $conn,
        $id,
        'CERTIFICADO',
        $ok ? 'VALIDA' : 'RECHAZA',
        'GERENTE',
        $ok ? $observaciones : $motivo,
        $ibm,
        $nombreUsuario
    );

    salir(['ok' => true, 'noempFirma' => $perfil['PER_noemp'] ?? null]);
}

// ------------------------------------------------------------
// certificados
// ------------------------------------------------------------
if ($accion === 'certificados') {
    $rs = q(
        $conn,
        "SELECT CER_id AS id, CER_folio AS folio, CER_clave AS clave, CER_producto AS producto,
                CER_estatus AS estatus, CONVERT(varchar(10), CER_fechaEmision, 23) AS fechaEmision,
                CER_nombreGerente AS gerente, CONVERT(varchar(16), CER_fechaFirma, 120) AS fechaFirma,
                CER_maquina AS maquina, CER_turno AS turno, CER_version AS version,
                CER_totalPalets AS palets, CER_totalCajas AS cajas,
                (SELECT COUNT(*) FROM tblMXPRCertificadoPaletFR
                  WHERE PAL_idCertificado = CER_id AND PAL_estatus = 'PENDIENTE') AS paletsPendientes,
                (SELECT COUNT(*) FROM tblMXPRCertificadoPaletFR
                  WHERE PAL_idCertificado = CER_id AND PAL_estatus = 'LIBERADO')  AS paletsLiberados
         FROM tblMXPRCertificadoFR WHERE CER_activo = 1 ORDER BY CER_id DESC"
    );
    salir(['ok' => true, 'items' => filas($rs)]);
}

// ------------------------------------------------------------
// pdf_datos — aprobados y rechazados (y preview del Gerente)
// ------------------------------------------------------------
if ($accion === 'pdf_datos') {
    $id = (int) ($in['id'] ?? 0);
    $c = fila(q($conn, "SELECT * FROM tblMXPRCertificadoFR WHERE CER_id = ? AND CER_activo = 1", [$id]));
    if (!$c)
        salir(['ok' => false, 'error' => 'Certificado no encontrado']);

    $firmados = ['APROBADO', 'RECHAZADO'];
    if (
        !in_array($c['CER_estatus'], $firmados, true)
        && !(tieneRol($roles, 'GERENTE') && $c['CER_estatus'] === 'ENVIADO_GT')
    )
        salir(['ok' => false, 'error' => 'El certificado aún no ha sido validado']);

    $c['CER_fechaEmision'] = fmtFecha($c['CER_fechaEmision']);
    $c['CER_fechaFirma'] = fmtFecha($c['CER_fechaFirma'], 'Y-m-d H:i');

    $ins = fila(q($conn, "SELECT * FROM tblMXPRCertificadoInspeccionFR    WHERE INS_idCertificado = ?", [$id]));
    $fis = fila(q($conn, "SELECT * FROM tblMXPRCertificadoFisicoquimicoFR WHERE FIS_idCertificado = ?", [$id]));
    $mic = fila(q($conn, "SELECT * FROM tblMXPRCertificadoMicrobiologiaFR WHERE MIC_idCertificado = ?", [$id]));
    if ($ins) {
        $ins['INS_fechaFabricacion'] = fmtFecha($ins['INS_fechaFabricacion']);
        $ins['INS_fechaCaducidad'] = fmtFecha($ins['INS_fechaCaducidad']);
    }

    $esp = especsClave($conn, $c['CER_clave'], $ESPECS_DEFAULT);
    $dc = datosClavePDF($conn, $VISTA_CLAVES, $c['CER_clave']);
    $pdfProducto = $dc['Producto'] ?? $c['CER_producto'];
    $pdfCategoria = $dc['Categoria'] ?? $c['CER_categoria'];
    $pdfPresentacion = $dc['Descripcion_Articulo'] ?? $c['CER_presentacion'];

    $noemp = null;
    if ($c['CER_ibmGerente']) {
        $g = fila(q($conn, "SELECT TOP 1 PER_noemp FROM tblMXPRCertificadoPerfilFR WHERE PER_ibm = ?", [$c['CER_ibmGerente']]));
        $noemp = $g['PER_noemp'] ?? null;
    }

    $moSel = [];
    foreach (
        filas(q($conn, "SELECT m.MOC_nombre, m.MOC_tipo, m.MOC_unidad, m.MOC_metodo, d.MOD_resultado
                        FROM tblMXPRCertificadoMODetFR d
                        JOIN tblMXPRCertificadoMOFR m ON m.MOC_id = d.MOD_idMO
                        WHERE d.MOD_idCertificado = ? ORDER BY m.MOC_orden", [$id])) as $m
    ) {
        $moSel[] = [
            'nombre' => $m['MOC_nombre'],
            'tipo' => $m['MOC_tipo'],
            'unidad' => $m['MOC_unidad'],
            'metodo' => $m['MOC_metodo'],
            'resultado' => $m['MOD_resultado'],
        ];
    }

    $defSel = array_map(
        fn($d) => ['atributo' => $d['DEF_atributo'], 'nombre' => $d['DEF_nombre']],
        filas(q($conn, "SELECT f.DEF_atributo, f.DEF_nombre
                        FROM tblMXPRCertificadoDefectoDetFR d
                        JOIN tblMXPRCertificadoDefectoFR f ON f.DEF_id = d.DFD_idDefecto
                        WHERE d.DFD_idCertificado = ? ORDER BY f.DEF_atributo, f.DEF_orden", [$id]))
    );

    $parFQ = parametrosClave($conn, $c['CER_clave']);
    $evalFQ = [
        'DENSIDAD' => veredictoParametro($fis['FIS_densidad'] ?? null, $parFQ['DENSIDAD'] ?? null),
        'VISCOSIDAD' => veredictoParametro($fis['FIS_viscosidad'] ?? null, $parFQ['VISCOSIDAD'] ?? null),
        'PH' => veredictoParametro($fis['FIS_ph'] ?? null, $parFQ['PH'] ?? null),
    ];

    // Solo para el preview: el PDF no muestra el estado de los palets
    $paletsCert = filas(q(
        $conn,
        "SELECT PAL_noPalet AS palet, PAL_cajas AS cajas, PAL_estatus AS estatus,
                CONVERT(varchar(16), PAL_fechaResolucion, 120) AS resuelto
         FROM tblMXPRCertificadoPaletFR WHERE PAL_idCertificado = ? ORDER BY PAL_noPalet",
        [$id]
    ));

    salir([
        'ok' => true,
        'cert' => $c,
        'ins' => $ins,
        'fis' => $fis,
        'mic' => $mic,
        'especs' => $esp,
        'pdfProducto' => $pdfProducto,
        'pdfCategoria' => $pdfCategoria,
        'pdfPresentacion' => $pdfPresentacion,
        'pdfDescripcion' => $pdfPresentacion ?: $pdfProducto,
        'noempFirma' => $noemp,
        'mo' => $moSel,
        'defectos' => $defSel,
        'parametros' => $parFQ,
        'evaluacion' => $evalFQ,
        'palets' => $paletsCert,
        'version' => (int) $c['CER_version'],
    ]);
}

// ------------------------------------------------------------
// folios_liberacion — pestaña de liberación por folio
// ------------------------------------------------------------
if ($accion === 'folios_liberacion') {
    exigeConsultaLiberacion($roles);

    $estatus = $in['estatus'] ?? null;
    $desde = $in['desde'] ?? null;
    $texto = trim($in['texto'] ?? '');

    $sql = "SELECT Folio, Clave, Producto, Maquina, Turno,
                   CONVERT(varchar(10), FechaProduccion, 23) AS fechaProduccion,
                   TotalPalets, TotalCajas, CajasLiberadas,
                   PaletsALiberar, PaletsExcluidos,
                   SinCertificar, EnProceso, Liberados, Rechazados,
                   RechazadosGerencia, RechazadosOrigen,
                   RechazoOrigen, RechazoCertificado,
                   MotivoExclusion, MotivosRechazo,
                   IdCertificado,
                   CONVERT(varchar(16), UltimaLiberacion, 120) AS ultimaLiberacion,
                   CONVERT(varchar(16), UltimoRechazo, 120) AS ultimoRechazo,
                   EstatusFolio
            FROM vwMXPRFolioLiberacion WHERE 1 = 1";
    $params = [];

    if ($estatus) {
        $sql .= " AND EstatusFolio = ?";
        $params[] = $estatus;
    }
    if ($desde) {
        $sql .= " AND FechaProduccion >= ?";
        $params[] = $desde;
    }
    if ($texto !== '') {
        $sql .= " AND (Folio LIKE ? OR Clave LIKE ? OR Producto LIKE ?)";
        $like = '%' . $texto . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    $sql .= " ORDER BY FechaProduccion DESC, Folio DESC";

    $items = [];
    foreach (filas(q($conn, $sql, $params)) as $r) {
        foreach ([
            'TotalPalets',
            'TotalCajas',
            'CajasLiberadas',
            'PaletsALiberar',
            'PaletsExcluidos',
            'EnProceso',
            'Liberados',
            'Rechazados',
            'RechazadosGerencia',
            'RechazadosOrigen',
            'Turno',
            'SinCertificar',
            'RechazoOrigen',
            'RechazoCertificado'
        ] as $n)
            $r[$n] = (int) $r[$n];
        // El avance se mide contra los palets que SÍ entraron al certificado
        $base = $r['PaletsALiberar'] > 0 ? $r['PaletsALiberar'] : $r['TotalPalets'];
        $r['avance'] = $base > 0 ? (int) round($r['Liberados'] * 100 / $base) : 0;
        $items[] = $r;
    }

    $resumen = [
        'folios' => count($items),
        'liberados' => 0,
        'parciales' => 0,
        'proceso' => 0,
        'sinCertificar' => 0,
        'rechazados' => 0,
        'cajasLiberadas' => 0,
        'conExcluidos' => 0
    ];
    foreach ($items as $i) {
        $resumen['cajasLiberadas'] += $i['CajasLiberadas'];
        if ($i['PaletsExcluidos'] > 0)
            $resumen['conExcluidos']++;
        switch ($i['EstatusFolio']) {
            case 'LIBERADO':
                $resumen['liberados']++;
                break;
            case 'PARCIAL':
                $resumen['parciales']++;
                break;
            case 'EN PROCESO':
                $resumen['proceso']++;
                break;
            case 'RECHAZADO':
                $resumen['rechazados']++;
                break;
            default:
                $resumen['sinCertificar']++;
        }
    }

    salir(['ok' => true, 'items' => $items, 'resumen' => $resumen]);
}

// ------------------------------------------------------------
// CONFIGURACIONES
// ------------------------------------------------------------
if ($accion === 'config_listar') {
    exigeConfigurador($roles);
    $cat = $in['catalogo'] ?? '';
    if (!isset($CATALOGOS[$cat]))
        salir(['ok' => false, 'error' => 'Catálogo no válido']);

    $c = $CATALOGOS[$cat];
    $p = $c['pre'];
    $cols = [$c['pk'] . ' AS id'];
    foreach ($c['campos'] as $f)
        $cols[] = "{$p}_{$f} AS {$f}";
    if ($cat !== 'perfiles') {
        $cols[] = "{$p}_nombreConfig AS quien";
        $cols[] = "CONVERT(varchar(16), {$p}_fechaConfig, 120) AS cuando";
    }
    $sql = "SELECT " . implode(', ', $cols) . " FROM {$c['tabla']}
            WHERE {$p}_activo = 1 ORDER BY {$c['orden']}";

    salir(['ok' => true, 'catalogo' => $cat, 'items' => filas(q($conn, $sql))]);
}

if ($accion === 'config_guardar') {
    exigeConfigurador($roles);
    $cat = $in['catalogo'] ?? '';
    if (!isset($CATALOGOS[$cat]))
        salir(['ok' => false, 'error' => 'Catálogo no válido']);

    $c = $CATALOGOS[$cat];
    $p = $c['pre'];
    $d = $in['datos'] ?? [];
    $idReg = (int) ($in['id'] ?? 0);

    $vals = [];
    foreach ($c['campos'] as $f) {
        $x = $d[$f] ?? '';
        $vals[$f] = ($x === '' || $x === null) ? null : $x;
    }

    // El consecutivo se calcula solo
    if (in_array('orden', $c['campos'], true) && ($vals['orden'] === null || $vals['orden'] === '')) {
        if ($idReg > 0) {
            $actual = fila(q($conn, "SELECT {$p}_orden AS o FROM {$c['tabla']} WHERE {$c['pk']} = ?", [$idReg]));
            $vals['orden'] = $actual['o'] ?? 1;
        } else {
            $vals['orden'] = siguienteOrden($conn, $cat, $vals);
        }
    }

    if ($cat === 'parametros' && (!$vals['clave'] || !$vals['variable']))
        salir(['ok' => false, 'error' => 'Clave y variable son obligatorias']);
    if ($cat === 'organolepticas' && (!$vals['clave'] || !$vals['tipo'] || !$vals['especificacion']))
        salir(['ok' => false, 'error' => 'Clave, tipo y especificación son obligatorios']);
    if ($cat === 'defectos' && (!$vals['atributo'] || !$vals['nombre']))
        salir(['ok' => false, 'error' => 'Atributo y nombre son obligatorios']);
    if ($cat === 'mo' && !$vals['nombre'])
        salir(['ok' => false, 'error' => 'El nombre del microorganismo es obligatorio']);
    if ($cat === 'opciones' && (!$vals['tipo'] || !$vals['valor']))
        salir(['ok' => false, 'error' => 'Tipo y valor son obligatorios']);
    if ($cat === 'perfiles' && (!$vals['ibm'] || !$vals['rol']))
        salir(['ok' => false, 'error' => 'IBM y rol son obligatorios']);

    $params = array_values($vals);

    if ($idReg > 0) {
        $sets = [];
        foreach ($c['campos'] as $f)
            $sets[] = "{$p}_{$f} = ?";
        if ($cat !== 'perfiles') {
            $sets[] = "{$p}_ibmConfig = ?";
            $sets[] = "{$p}_nombreConfig = ?";
            $sets[] = "{$p}_fechaConfig = GETDATE()";
            $params[] = $ibm;
            $params[] = $nombreUsuario;
        }
        $params[] = $idReg;
        q($conn, "UPDATE {$c['tabla']} SET " . implode(', ', $sets) . " WHERE {$c['pk']} = ?", $params);
        $accionBit = 'CONFIG_EDITA';
    } else {
        $cols = array_map(fn($f) => "{$p}_{$f}", $c['campos']);
        if ($cat !== 'perfiles') {
            $cols[] = "{$p}_ibmConfig";
            $cols[] = "{$p}_nombreConfig";
            $params[] = $ibm;
            $params[] = $nombreUsuario;
        }
        $marcas = implode(',', array_fill(0, count($cols), '?'));
        q($conn, "INSERT INTO {$c['tabla']} (" . implode(', ', $cols) . ") VALUES ($marcas)", $params);
        $accionBit = 'CONFIG_ALTA';
    }

    bitacora(
        $conn,
        null,
        'CONFIG',
        $accionBit,
        strtoupper($cat),
        json_encode($vals, JSON_UNESCAPED_UNICODE),
        $ibm,
        $nombreUsuario
    );

    salir(['ok' => true]);
}

if ($accion === 'config_eliminar') {
    exigeConfigurador($roles);
    $cat = $in['catalogo'] ?? '';
    $idReg = (int) ($in['id'] ?? 0);
    if (!isset($CATALOGOS[$cat]) || !$idReg)
        salir(['ok' => false, 'error' => 'Datos incompletos']);

    $c = $CATALOGOS[$cat];
    $p = $c['pre'];
    q($conn, "UPDATE {$c['tabla']} SET {$p}_activo = 0 WHERE {$c['pk']} = ?", [$idReg]);

    bitacora($conn, null, 'CONFIG', 'CONFIG_BAJA', strtoupper($cat), "id $idReg", $ibm, $nombreUsuario);
    salir(['ok' => true]);
}

if ($accion === 'config_claves') {
    exigeConfigurador($roles);
    $rs = q(
        $conn,
        "SELECT DISTINCT v.NoClave AS clave, v.Producto AS producto, v.Categoria AS categoria,
                (SELECT COUNT(*) FROM tblMXPRCertificadoParametroFR
                  WHERE PAR_clave = v.NoClave AND PAR_activo = 1) AS parametros,
                (SELECT COUNT(*) FROM tblMXPRCertificadoOrganolepticaFR
                  WHERE ORG_clave = v.NoClave AND ORG_activo = 1) AS organolepticas
         FROM $VISTA_CLAVES v
         WHERE v.NoClave IN (SELECT DISTINCT claveProducto FROM tblMXPRBajadasFormulados WHERE vigente = 1)
         ORDER BY v.NoClave"
    );
    salir(['ok' => true, 'items' => filas($rs)]);
}

if ($accion === 'config_agrupado') {
    exigeConfigurador($roles);
    $tipo = $in['tipo'] ?? 'parametros';

    if ($tipo === 'parametros') {
        $filas = filas(q(
            $conn,
            "SELECT PAR_id AS id, LTRIM(RTRIM(PAR_clave)) AS clave, PAR_variable AS variable,
                    PAR_unidad AS unidad, PAR_minimo AS minimo, PAR_objetivo AS objetivo,
                    PAR_maximo AS maximo, PAR_metodo AS metodo,
                    PAR_nombreConfig AS quien, CONVERT(varchar(16), PAR_fechaConfig, 120) AS cuando
             FROM tblMXPRCertificadoParametroFR WHERE PAR_activo = 1
             ORDER BY PAR_clave, PAR_variable"
        ));
        $requeridas = ['DENSIDAD', 'VISCOSIDAD', 'PH'];
    } else {
        $filas = filas(q(
            $conn,
            "SELECT ORG_id AS id, LTRIM(RTRIM(ORG_clave)) AS clave, ORG_tipo AS variable,
                    ORG_especificacion AS especificacion, ORG_orden AS orden,
                    ORG_nombreConfig AS quien, CONVERT(varchar(16), ORG_fechaConfig, 120) AS cuando
             FROM tblMXPRCertificadoOrganolepticaFR WHERE ORG_activo = 1
             ORDER BY ORG_clave, ORG_tipo, ORG_orden"
        ));
        $requeridas = ['ASPECTO', 'COLOR', 'OLOR'];
    }

    $grupos = [];
    foreach ($filas as $f) {
        $k = $f['clave'];
        if (!isset($grupos[$k]))
            $grupos[$k] = ['clave' => $k, 'producto' => null, 'items' => [], 'faltan' => []];
        $grupos[$k]['items'][] = $f;
    }

    foreach ($grupos as $k => &$g) {
        $dc = datosClavePDF($conn, $VISTA_CLAVES, $k);
        $g['producto'] = $dc['Producto'] ?? null;
        $presentes = array_map(fn($i) => strtoupper(trim($i['variable'])), $g['items']);
        $g['faltan'] = array_values(array_diff($requeridas, $presentes));
        $g['completo'] = count($g['faltan']) === 0;
    }
    unset($g);

    $sinConfig = [];
    foreach (
        filas(q($conn, "SELECT DISTINCT claveProducto AS clave FROM tblMXPRBajadasFormulados WHERE vigente = 1")) as $b
    ) {
        if (!isset($grupos[trim($b['clave'])]))
            $sinConfig[] = trim($b['clave']);
    }

    salir([
        'ok' => true,
        'tipo' => $tipo,
        'requeridas' => $requeridas,
        'grupos' => array_values($grupos),
        'sinConfig' => $sinConfig,
    ]);
}

if ($accion === 'bitacora') {
    $idCert = ($in['id'] ?? '') === '' ? null : (int) $in['id'];
    $modulo = $in['modulo'] ?? null;

    $sql = "SELECT TOP 300 BIT_id AS id, BIT_idCertificado AS idCertificado,
                   BIT_modulo AS modulo, BIT_accion AS accion, BIT_etapa AS etapa,
                   BIT_detalle AS detalle, BIT_nombre AS quien, BIT_ibm AS ibm,
                   CONVERT(varchar(16), BIT_fecha, 120) AS cuando
            FROM tblMXPRCertificadoBitacoraFR WHERE 1 = 1";
    $params = [];
    if ($idCert !== null) {
        $sql .= " AND BIT_idCertificado = ?";
        $params[] = $idCert;
    }
    if ($modulo) {
        $sql .= " AND BIT_modulo = ?";
        $params[] = $modulo;
    }
    if ($modulo === 'CONFIG')
        exigeConfigurador($roles);

    $sql .= " ORDER BY BIT_fecha DESC";
    salir(['ok' => true, 'items' => filas(q($conn, $sql, $params))]);
}

// ------------------------------------------------------------
// rechazar_origen — rechaza palets ANTES de iniciar certificación
//   Cuarentena 1 · Rechazado 1
// ------------------------------------------------------------
if ($accion === 'rechazar_origen') {
    if (!tieneRol($roles, 'SUP_INSPECCION', 'SUP_MICROBIOLOGIA', 'SUP_FISICOQUIMICO', 'GERENTE'))
        salir(['ok' => false, 'error' => 'No tienes permiso para rechazar material']);

    $palets = $in['palets'] ?? [];
    $motivo = trim($in['motivo'] ?? '');
    if (!is_array($palets) || !count($palets))
        salir(['ok' => false, 'error' => 'Selecciona al menos un palet']);
    if ($motivo === '')
        salir(['ok' => false, 'error' => 'Indica el motivo del rechazo']);

    // Solo se rechazan en origen los que NO están en un certificado
    $marcas = implode(',', array_fill(0, count($palets), '?'));
    $ids = array_map('intval', $palets);
    $rows = filas(q(
        $conn,
        "SELECT b.id, b.folio, b.claveProducto, b.noPalet, b.cajas
         FROM tblMXPRBajadasFormulados b
         LEFT JOIN tblMXPRCertificadoPaletFR p ON p.PAL_idBajada = b.id
         WHERE b.vigente = 1 AND p.PAL_id IS NULL AND b.id IN ($marcas)",
        $ids
    ));
    if (!count($rows))
        salir(['ok' => false, 'error' => 'Esos palets ya están en un certificado; el rechazo va por Gerencia']);

    foreach ($rows as $r) {
        estadoPalet($conn, $r['id'], 'RECHAZADO_ORIGEN');
        q(
            $conn,
            "INSERT INTO tblMXPRCertificadoExclusionFR
                (EXC_idCertificado, EXC_folio, EXC_clave, EXC_idBajada, EXC_noPalet, EXC_cajas,
                 EXC_motivo, EXC_ibm, EXC_nombre)
             VALUES (NULL,?,?,?,?,?,?,?,?)",
            [
                $r['folio'],
                $r['claveProducto'],
                (int) $r['id'],
                (int) $r['noPalet'],
                (int) $r['cajas'],
                'RECHAZO EN ORIGEN: ' . $motivo,
                $ibm,
                $nombreUsuario
            ]
        );
    }

    bitacora(
        $conn,
        null,
        'CERTIFICADO',
        'RECHAZO_ORIGEN',
        null,
        count($rows) . ' palet(s) rechazados sin certificar · ' . $motivo,
        $ibm,
        $nombreUsuario
    );

    salir(['ok' => true, 'rechazados' => count($rows)]);
}

salir(['ok' => false, 'error' => 'Acción no válida']);