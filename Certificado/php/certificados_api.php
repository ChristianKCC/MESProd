<?php
// php/certificados_api.php — Certificados de Calidad Líquidos y Formulados (FORM-63297) — v3
// Flujo: INS ∥ FIS -> (ambas autorizadas) MIC -> LISTO -> ENVIADO_GT -> APROBADO (+firma) / RECHAZADO
// Estatus etapa: 0=captura, 1=enviado, 2=autorizado, 3=rechazado
//
// NOTA v3:
//  - El PRODUCTO de las tablas/listados viene de tblMXPRBajadasFormulados.
//  - Producto y Categoría de vwMXPRClaveMaquina se usan SOLO al armar el PDF.
//  - 'espacio' ya NO oculta certificados cuando el usuario no tiene acciones.
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once "../../conexion.php";

// ====== CONFIG ======
$DB_APP = "TLX004MXDB";
$VISTA_CLAVES = "vwMXPRClaveMaquina";   // columnas: NoClave, Producto, Categoria  (solo para el PDF)

// Especificaciones por defecto (matriz 3x3) cuando la clave no tiene registro propio
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

// ---------- Helpers ----------
function q($conn, $sql, $params = [])
{
    $rs = sqlsrv_query($conn, $sql, $params);
    if ($rs === false) {
        jlog(print_r(sqlsrv_errors(), true));
        salir(['ok' => false, 'error' => 'Error SQL']);
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
// sqlsrv devuelve objetos DateTime: los pasamos a string para el front
function fmtFecha($v, $formato = 'Y-m-d')
{
    if ($v instanceof DateTime)
        return $v->format($formato);
    return $v ? (string) $v : null;
}

// SOLO PARA EL PDF: Producto y Categoría del catálogo de claves
// function datosClavePDF($conn, $vista, $clave)
// {
//     $rs = sqlsrv_query($conn, "SELECT TOP 1 Producto, Categoria FROM $vista WHERE NoClave = ?", [$clave]);
//     if ($rs === false) {
//         jlog('vista claves ' . print_r(sqlsrv_errors(), true));
//         return null;
//     }
//     return sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC) ?: null;
// }

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

// Specs resueltas: las de la tabla ESP o, si no hay, los valores por defecto
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

// Roles del usuario actual (puede tener varios)
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
    return $r ? (int) $r['e'] : -1; // -1 = sin registro
}

// Visibilidad de consulta: dueño de etapa, su supervisor, MIC (ve todo), GERENTE (ve todo)
function puedeVer($roles, $etapa, $ETAPAS)
{
    if (tieneRol($roles, 'GERENTE', 'MICROBIOLOGIA', 'SUP_MICROBIOLOGIA'))
        return true;
    $t = $ETAPAS[$etapa];
    return tieneRol($roles, $t['rolCap'], $t['rolSup']);
}

// ============================================================
// perfil
// ============================================================
if ($accion === 'perfil') {
    salir(['ok' => true, 'ibm' => $ibm, 'nombre' => $nombreUsuario, 'roles' => $roles]);
}

// ============================================================
// folios — bajadas (folio+clave) que aún no tienen certificado
// ============================================================
if ($accion === 'folios') {
    $rs = q(
        $conn,
        "SELECT b.folio, b.claveProducto AS clave, MAX(b.producto) AS producto,
                CONVERT(varchar(10), MAX(b.fecha), 23) AS fecha, MAX(b.maquina) AS maquina
         FROM tblMXPRBajadasFormulados b
         LEFT JOIN tblMXPRCertificadoFR c ON c.CER_folio = b.folio AND c.CER_activo = 1
         WHERE b.activo = 1 AND c.CER_id IS NULL
         GROUP BY b.folio, b.claveProducto
         ORDER BY MAX(b.id) DESC"
    );
    salir(['ok' => true, 'items' => filas($rs)]);
}

// ============================================================
// iniciar — crea el certificado desde un folio de bajada
//           El producto guardado es el de la BAJADA (no el de la vista)
// ============================================================
if ($accion === 'iniciar') {
    $folio = trim($in['folio'] ?? '');
    if ($folio === '')
        salir(['ok' => false, 'error' => 'Folio requerido']);

    $b = fila(q(
        $conn,
        "SELECT TOP 1 folio, claveProducto, producto FROM tblMXPRBajadasFormulados
         WHERE folio = ? AND activo = 1 ORDER BY id DESC",
        [$folio]
    ));
    if (!$b)
        salir(['ok' => false, 'error' => 'Folio no encontrado en bajadas']);

    $esp = especsClave($conn, $b['claveProducto'], $ESPECS_DEFAULT);

    $rs = q(
        $conn,
        "INSERT INTO tblMXPRCertificadoFR (CER_folio, CER_clave, CER_producto, CER_presentacion, CER_lote)
         OUTPUT INSERTED.CER_id
         VALUES (?,?,?,?,?)",
        [$b['folio'], $b['claveProducto'], $b['producto'], $esp['presentacion'], $b['folio']]
    );
    $id = (int) fila($rs)['CER_id'];

    q($conn, "INSERT INTO tblMXPRCertificadoInspeccionFR    (INS_idCertificado) VALUES (?)", [$id]);
    q($conn, "INSERT INTO tblMXPRCertificadoFisicoquimicoFR (FIS_idCertificado) VALUES (?)", [$id]);
    q($conn, "INSERT INTO tblMXPRCertificadoMicrobiologiaFR (MIC_idCertificado) VALUES (?)", [$id]);

    salir(['ok' => true, 'id' => $id]);
}

// ============================================================
// espacio — "Mi espacio": un item por certificado con sus 3 etapas.
//           Devuelve TODOS los certificados en proceso; 'tieneAccion'
//           marca los que este usuario puede trabajar.
// ============================================================
if ($accion === 'espacio') {
    $certs = filas(q(
        $conn,
        "SELECT CER_id, CER_folio, CER_clave, CER_producto, CER_estatus
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
                'autorizo' => $visible ? ($e["{$p}_nombreAutoriza"] ?? null) : null,
                'motivoRechazo' => $visible ? ($e["{$p}_motivoRechazo"] ?? null) : null,
                'fechaCaptura' => $visible ? fmtFecha($e["{$p}_fechaCaptura"] ?? null, 'Y-m-d H:i') : null,
            ];
        }

        $accionCert = null;
        if ($c['CER_estatus'] === 'LISTO') {
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
            'producto' => $c['CER_producto'],   // <- de la bajada
            'estatusCert' => $c['CER_estatus'],
            'accionCert' => $accionCert,
            'tieneAccion' => $tieneAccion,
            'etapas' => $etapas,
        ];
    }

    // Primero lo accionable
    usort($items, fn($a, $b) => ($b['tieneAccion'] <=> $a['tieneAccion']) ?: ($b['id'] <=> $a['id']));

    salir(['ok' => true, 'items' => $items, 'roles' => $roles, 'ibm' => $ibm]);
}

// ============================================================
// detalle — datos del certificado con reglas de visibilidad
// ============================================================
if ($accion === 'detalle') {
    $id = (int) ($in['id'] ?? 0);
    $c = fila(q($conn, "SELECT * FROM tblMXPRCertificadoFR WHERE CER_id = ? AND CER_activo = 1", [$id]));
    if (!$c)
        salir(['ok' => false, 'error' => 'Certificado no encontrado']);

    $esp = especsClave($conn, $c['CER_clave'], $ESPECS_DEFAULT);

    $out = ['cert' => $c, 'especs' => $esp, 'etapas' => []];
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
    $out['ok'] = true;
    salir($out);
}

// ============================================================
// guardar
// ============================================================
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
    $v = function ($k) use ($d) {                 // valor o NULL
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
    } elseif ($etapa === 'fisicoquimico') {
        q(
            $conn,
            "UPDATE tblMXPRCertificadoFisicoquimicoFR SET
                    FIS_viscosidad=?, FIS_ph=?, FIS_densidad=?, FIS_aspectoColor=?, FIS_olor=?,
                    FIS_observaciones=?, FIS_estatus=?, FIS_ibmCaptura=?, FIS_nombreCaptura=?, FIS_fechaCaptura=GETDATE(),
                    FIS_motivoRechazo=NULL
                  WHERE FIS_idCertificado=?",
            [
                $v('viscosidad'),
                $v('ph'),
                $v('densidad'),
                $v('aspectoColor'),
                $v('olor'),
                $v('observaciones'),
                $nuevoEstatus,
                $ibm,
                $nombreUsuario,
                $id
            ]
        );
    } else { // microbiologia
        $tamc = $v('tamc');
        $tymc = $v('tymc');
        q(
            $conn,
            "UPDATE tblMXPRCertificadoMicrobiologiaFR SET
                    MIC_tamc=?, MIC_tymc=?, MIC_patogenos=?,
                    MIC_observaciones=?, MIC_estatus=?, MIC_ibmCaptura=?, MIC_nombreCaptura=?, MIC_fechaCaptura=GETDATE(),
                    MIC_motivoRechazo=NULL
                  WHERE MIC_idCertificado=?",
            [
                $tamc !== null ? (int) $tamc : null,
                $tymc !== null ? (int) $tymc : null,
                $v('patogenos'),
                $v('observaciones'),
                $nuevoEstatus,
                $ibm,
                $nombreUsuario,
                $id
            ]
        );
    }

    salir(['ok' => true, 'estatus' => $nuevoEstatus]);
}

// ============================================================
// autorizar
// ============================================================
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

    // Al autorizar MIC: fecha de emisión automática + certificado LISTO
    if ($ok && $etapa === 'microbiologia') {
        q($conn, "UPDATE tblMXPRCertificadoFR SET CER_fechaEmision = CONVERT(date, GETDATE()), CER_estatus = 'LISTO'
                  WHERE CER_id = ?", [$id]);
    }
    salir(['ok' => true]);
}

// ============================================================
// enviar_gt
// ============================================================
if ($accion === 'enviar_gt') {
    $id = (int) ($in['id'] ?? 0);
    foreach (array_keys($ETAPAS) as $etapa) {
        if (estatusEtapa($conn, $ETAPAS, $etapa, $id) !== 2)
            salir(['ok' => false, 'error' => 'Aún hay etapas sin autorizar']);
    }
    q($conn, "UPDATE tblMXPRCertificadoFR SET CER_estatus = 'ENVIADO_GT' WHERE CER_id = ? AND CER_estatus = 'LISTO'", [$id]);
    salir(['ok' => true]);
}

// ============================================================
// validar_gt
// ============================================================
// if ($accion === 'validar_gt') {
//     $id = (int) ($in['id'] ?? 0);
//     $ok = !empty($in['aprobar']);
//     $motivo = trim($in['motivo'] ?? '');
//     if (!tieneRol($roles, 'GERENTE'))
//         salir(['ok' => false, 'error' => 'Solo Gerencia Técnica puede validar']);

//     $c = fila(q($conn, "SELECT CER_estatus FROM tblMXPRCertificadoFR WHERE CER_id = ?", [$id]));
//     if (!$c || $c['CER_estatus'] !== 'ENVIADO_GT')
//         salir(['ok' => false, 'error' => 'El certificado no está en validación']);
//     if (!$ok && $motivo === '')
//         salir(['ok' => false, 'error' => 'Indica el motivo de rechazo']);

//     $conclusion = $ok
//         ? 'Confirmo a la especificación definida que el producto es aceptable para su distribución a KCM, S.A.B. de C.V.'
//         : 'Confirmo a la especificación definida que el producto no es aceptable para su distribución a KCM, S.A.B. de C.V.';

//     q(
//         $conn,
//         "UPDATE tblMXPRCertificadoFR SET CER_estatus=?, CER_conclusion=?, CER_ibmGerente=?, CER_nombreGerente=?,
//               CER_fechaFirma=GETDATE(), CER_motivoRechazoGT=? WHERE CER_id=?",
//         [$ok ? 'APROBADO' : 'RECHAZADO', $conclusion, $ibm, $nombreUsuario, $ok ? null : $motivo, $id]
//     );

//     salir(['ok' => true, 'noempFirma' => $perfil['PER_noemp'] ?? null]);
// }

if ($accion === 'validar_gt') {
    $id = (int) ($in['id'] ?? 0);
    $ok = !empty($in['aprobar']);
    $motivo = trim($in['motivo'] ?? '');
    $observaciones = trim($in['observaciones'] ?? '');   // <-- NUEVO
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
        "UPDATE tblMXPRCertificadoFR SET CER_estatus=?, CER_conclusion=?, 
              CER_ibmGerente=?, CER_nombreGerente=?, CER_fechaFirma=GETDATE(), CER_motivoRechazoGT=?
         WHERE CER_id=?",
        [
            $ok ? 'APROBADO' : 'RECHAZADO',
            $conclusion,

            $ibm,
            $nombreUsuario,
            $ok ? null : $motivo,
            $id
        ]
    );

    salir(['ok' => true, 'noempFirma' => $perfil['PER_noemp'] ?? null]);
}

// ============================================================
// certificados — listado (producto de la BAJADA)
// ============================================================
if ($accion === 'certificados') {
    $rs = q(
        $conn,
        "SELECT CER_id AS id, CER_folio AS folio, CER_clave AS clave, CER_producto AS producto,
                CER_estatus AS estatus, CONVERT(varchar(10), CER_fechaEmision, 23) AS fechaEmision,
                CER_nombreGerente AS gerente, CONVERT(varchar(16), CER_fechaFirma, 120) AS fechaFirma
         FROM tblMXPRCertificadoFR WHERE CER_activo = 1 ORDER BY CER_id DESC"
    );
    salir(['ok' => true, 'items' => filas($rs)]);
}

// ============================================================
// pdf_datos — aquí SÍ se usa vwMXPRClaveMaquina (producto/categoría del PDF)
// ============================================================
if ($accion === 'pdf_datos') {
    $id = (int) ($in['id'] ?? 0);
    $c = fila(q($conn, "SELECT * FROM tblMXPRCertificadoFR WHERE CER_id = ? AND CER_activo = 1", [$id]));
    if (!$c)
        salir(['ok' => false, 'error' => 'Certificado no encontrado']);
    if ($c['CER_estatus'] !== 'APROBADO' && !(tieneRol($roles, 'GERENTE') && $c['CER_estatus'] === 'ENVIADO_GT'))
        salir(['ok' => false, 'error' => 'El certificado aún no está aprobado']);

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

    // Producto y Categoría del catálogo, SOLO para el PDF
    $dc = datosClavePDF($conn, $VISTA_CLAVES, $c['CER_clave']);

    $pdfProducto = $dc['Producto'] ?? $c['CER_producto'];
    $pdfCategoria = $dc['Categoria'] ?? $c['CER_categoria'];
    $pdfPresentacion = $dc['Descripcion_Articulo'] ?? $c['CER_presentacion'];

    // $pdfProducto = $dc['Producto'] ?? $c['CER_producto'];
    // $pdfCategoria = $dc['Categoria'] ?? $c['CER_categoria'];

    $noemp = null;
    if ($c['CER_ibmGerente']) {
        $g = fila(q($conn, "SELECT TOP 1 PER_noemp FROM tblMXPRCertificadoPerfilFR WHERE PER_ibm = ?", [$c['CER_ibmGerente']]));
        $noemp = $g['PER_noemp'] ?? null;
    }

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
        'noempFirma' => $noemp,
    ]);

}

salir(['ok' => false, 'error' => 'Acción no válida']);