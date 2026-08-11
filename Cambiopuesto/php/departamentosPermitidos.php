<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once __DIR__ . "/../../conexion.php";

if (!function_exists('cp_normalizar')) {
    function cp_normalizar($txt)
    {
        $txt = (string) $txt;
        if ($txt !== '' && !mb_check_encoding($txt, 'UTF-8'))
            $txt = mb_convert_encoding($txt, 'UTF-8', 'Windows-1252');
        $txt = mb_strtolower(trim($txt), 'UTF-8');
        $txt = strtr($txt, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u']);
        return preg_replace('/\s+/', ' ', $txt);
    }
    function cp_soloIbm($v)
    {
        return preg_replace('/[^\d].*$/', '', trim((string) $v));
    }
}

/** Devuelve ['ids'=>[NoDepto...], 'nombres'=>[...]] de los departamentos del IBM según el CSV de nóminas */
function deptosPermitidosIBM($ibmSesion): array
{
    $COL_IBM = 0;   // A - Número IBM
    $COL_DEPTO = 5; // F - NOMBRE DE DEPTO
    $ibmBuscado = cp_soloIbm($ibmSesion);
    $rutaCsv = __DIR__ . "/../../BDNominas/uploads/AUTORIZACION GERENCIA Y SUPERINTENDENTE.csv";
    if ($ibmBuscado === '' || !is_file($rutaCsv))
        return ['ids' => [], 'nombres' => []];

    $fh = fopen($rutaCsv, 'r');
    if ($fh === false)
        return ['ids' => [], 'nombres' => []];

    $primera = ltrim((string) fgets($fh), "\xEF\xBB\xBF");
    $conteos = [';' => substr_count($primera, ';'), ',' => substr_count($primera, ','), "\t" => substr_count($primera, "\t")];
    arsort($conteos);
    $delim = array_key_first($conteos) ?: ',';
    rewind($fh);

    $deptosCsv = [];
    $esEncabezado = true;
    while (($fila = fgetcsv($fh, 0, $delim)) !== false) {
        if ($esEncabezado) {
            $esEncabezado = false;
            continue;
        }
        if (!isset($fila[$COL_IBM]))
            continue;
        if (cp_soloIbm($fila[$COL_IBM]) !== $ibmBuscado)
            continue;
        $nom = cp_normalizar($fila[$COL_DEPTO] ?? '');
        if ($nom !== '')
            $deptosCsv[$nom] = true;
    }
    fclose($fh);

    $ids = [];
    $nombres = [];
    if (!empty($deptosCsv)) {
        $conn = (new ClassConexion())->conexion("TLX009MXDB");
        $res = sqlsrv_query($conn, "SELECT NoDepto, NombreDepto FROM TLX009MXDB.dbo.tblDepartamentos");
        if ($res !== false) {
            while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
                $nomNorm = cp_normalizar($row['NombreDepto'] ?? '');
                if ($nomNorm !== '' && isset($deptosCsv[$nomNorm])) {
                    $ids[] = trim((string) $row['NoDepto']);
                    $nombres[] = trim((string) $row['NombreDepto']);
                }
            }
        }
    }
    return ['ids' => array_values(array_unique($ids)), 'nombres' => $nombres];
}

// Endpoint AJAX directo (para el select de departamento y el filtro del reporte)
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'departamentosPermitidos.php') {
    header('Content-Type: application/json; charset=utf-8');
    $ibm = trim((string) ($_SESSION['ibm'] ?? ''));
    if ($ibm === '') {
        echo json_encode(['ok' => false, 'error' => 'Sin sesión IBM', 'ids' => []]);
        exit;
    }
    $r = deptosPermitidosIBM($ibm);
    echo json_encode(['ok' => true, 'ibm' => $ibm, 'ids' => $r['ids'], 'nombres' => $r['nombres']], JSON_UNESCAPED_UNICODE);
    exit;
}