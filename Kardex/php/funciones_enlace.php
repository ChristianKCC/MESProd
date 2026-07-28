<?php
/* =====================================================================
   funciones_enlace.php
   Recorte y armado del enlace de Office Online (SharePoint), por TIPO.

   recortarEnlaceEMC()    -> deja el enlace "puro": base + ?sourcedoc={GUID}
   construirEnlaceEmbed() -> al enlace puro le pega los parametros fijos
                             SEGUN el tipo (Excel / PowerPoint / ...).
   obtenerEnlaceActivo()  -> trae de BD el enlace activo de un tipo.
   ===================================================================== */

/* ── Tipos (mismos numeros que en tblMXPRTipoEnlaceEMC) ── */
const EMC_TIPO_EXCEL_KDX =5;   // Kardex
const EMC_TIPO_PPT = 2;
const EMC_TIPO_EXCEL = 1;

/* ── Parametros fijos del visor, por tipo ──
   Si quieres cambiar permisos/aspecto, se cambia aqui en un solo lugar. */
const EMC_EMBED_PARAMS_EXCEL_KDX =
    'action=embedview'
  . '&wdAllowInteractivity=True'
  . '&wdDownloadButton=False'
  . '&wdHideGridlines=True'
  . '&wdHideHeaders=True'
  . '&wdInConfigurator=True'
  . '&wdOpenInExcel=False';

/* wdAr = relacion de aspecto del PPT. 1.7777... = 16:9.
   Si tu presentacion es 4:3 usa 1.3333333333333333 */
const EMC_EMBED_PARAMS_PPT =
    'action=embedview'
  . '&wdAr=1.7777777777777777'
  . '&wdEaaCheck=1';

/**
 * Devuelve los parametros fijos segun el tipo.
 */
function paramsPorTipo($tipo)
{
    switch ((int) $tipo) {
        case EMC_TIPO_PPT:
            return EMC_EMBED_PARAMS_PPT;
        case EMC_TIPO_EXCEL_KDX:
            return EMC_EMBED_PARAMS_EXCEL_KDX;
        default:
            return EMC_EMBED_PARAMS_EXCEL_KDX;
    }
}

/**
 * Recorta el enlace que comparte Office Online y deja solo lo esencial.
 * Acepta el enlace directo o el codigo <iframe ... src="...">.
 *
 * @param  string $raw
 * @return array|null  ['sourcedoc'=>..., 'enlace'=>base.'?sourcedoc='.GUID] | null
 */
function recortarEnlaceEMC($raw)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return null;
    }

    /* 1) Si pegaron el <iframe ... src="...">, extraer el src */
    if (stripos($raw, '<iframe') !== false) {
        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $raw, $m)) {
            $raw = $m[1];
        }
    }

    /* 2) &amp; -> & */
    $raw = html_entity_decode($raw, ENT_QUOTES);

    /* 3) Partir la URL */
    $parts = parse_url($raw);
    if (!$parts || empty($parts['host']) || empty($parts['path'])) {
        return null;
    }

    /* 4) Sacar el sourcedoc */
    $sourcedoc = null;
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $q);
        if (isset($q['sourcedoc']) && $q['sourcedoc'] !== '') {
            $sourcedoc = $q['sourcedoc'];
        }
    }
    if ($sourcedoc === null) {
        return null;
    }

    /* 5) Reconstruir SOLO base + sourcedoc (con sus llaves {} tal cual) */
    $scheme = $parts['scheme'] ?? 'https';
    $base   = $scheme . '://' . $parts['host'] . $parts['path'];

    return [
        'sourcedoc' => $sourcedoc,
        'enlace'    => $base . '?sourcedoc=' . $sourcedoc,
    ];
}

/**
 * Enlace puro + parametros fijos del tipo => src final del iframe.
 *
 * @param  string $enlaceBase
 * @param  int    $tipo
 * @return string|null
 */
function construirEnlaceEmbed($enlaceBase, $tipo = EMC_TIPO_EXCEL_KDX)
{
    $enlaceBase = trim((string) $enlaceBase);
    if ($enlaceBase === '') {
        return null;
    }
    $sep = (strpos($enlaceBase, '?') !== false) ? '&' : '?';
    return $enlaceBase . $sep . paramsPorTipo($tipo);
}

/**
 * Trae de BD el enlace activo de un tipo (o null si no hay).
 *
 * @param  resource $conn  conexion sqlsrv
 * @param  int      $tipo
 * @return array|null
 */
function obtenerEnlaceActivo($conn, $tipo)
{
    if ($conn === false) {
        return null;
    }
    $sql = "SELECT TOP 1 id, tipo, nombre_archivo, enlace, sourcedoc, fecha_registro, ibm_registro
            FROM tblMXPREnlaceEMC
            WHERE activo = 1 AND tipo = ?
            ORDER BY fecha_registro DESC";
    $res = sqlsrv_query($conn, $sql, [(int) $tipo]);
    return $res ? sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC) : null;
}