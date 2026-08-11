<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
header('Content-Type: application/json; charset=utf-8');

require_once "../../conexion.php";
require_once(__DIR__ . "/deptosSupervisor.php");

$ibmSesion = trim((string) ($_SESSION['ibm'] ?? ($_SESSION['IBM'] ?? '')));
if ($ibmSesion === '') {
    echo json_encode(['ok' => false, 'error' => 'Sin sesión IBM', 'ids' => []]);
    exit;
}

// ── CONFIG: ruta real del CSV ──
$rutaCsv = __DIR__ . "/../../BDNominas/uploads/AUTORIZACION GERENCIA Y SUPERINTENDENTE.csv";


// Índices de columna (A=0 … H=7)
const COL_IBM = 0; // A - Número IBM
const COL_DEPTO = 5; // F - NOMBRE DE DEPTO

// Normaliza: fuerza UTF-8, minúsculas, quita acentos y colapsa espacios
function normalizar($txt)
{
    $txt = (string) $txt;
    if ($txt !== '' && !mb_check_encoding($txt, 'UTF-8')) {
        $txt = mb_convert_encoding($txt, 'UTF-8', 'Windows-1252');
    }
    $txt = mb_strtolower(trim($txt), 'UTF-8');
    $txt = strtr($txt, [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u',
        'ñ' => 'n',
        'à' => 'a',
        'è' => 'e',
        'ì' => 'i',
        'ò' => 'o',
        'ù' => 'u'
    ]);
    return preg_replace('/\s+/', ' ', $txt);
}

// Deja solo el IBM numérico ("27419.0" o "27419 " -> "27419")
function soloIbm($v)
{
    return preg_replace('/[^\d].*$/', '', trim((string) $v));
}

$ibmBuscado = soloIbm($ibmSesion);

// ── 1) CSV -> nombres de depto (normalizados) del IBM en sesión ──
if (!is_file($rutaCsv)) {
    echo json_encode(['ok' => false, 'error' => 'No se encontró el CSV', 'ruta' => $rutaCsv, 'ids' => []]);
    exit;
}

$fh = fopen($rutaCsv, 'r');
if ($fh === false) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo abrir el CSV', 'ids' => []]);
    exit;
}

// Detectar delimitador con la 1ª línea (; , o tab)
$primera = ltrim((string) fgets($fh), "\xEF\xBB\xBF");
$conteos = [';' => substr_count($primera, ';'), ',' => substr_count($primera, ','), "\t" => substr_count($primera, "\t")];
arsort($conteos);
$delim = array_key_first($conteos) ?: ',';
rewind($fh);

$deptosCsv = []; // set: nombreNormalizado => true
$esEncabezado = true;
while (($fila = fgetcsv($fh, 0, $delim)) !== false) {
    if ($esEncabezado) {
        $esEncabezado = false;
        continue;
    } // saltar fila 1
    if (!isset($fila[COL_IBM]))
        continue;
    if (soloIbm($fila[COL_IBM]) !== $ibmBuscado)
        continue;
    $nom = normalizar($fila[COL_DEPTO] ?? '');
    if ($nom !== '')
        $deptosCsv[$nom] = true;
}
fclose($fh);

// ── 2) Cruzar contra tblDepartamentos para obtener los NoDepto ──
$ids = [];
$nombres = [];
if (!empty($deptosCsv)) {
    $conn = (new ClassConexion())->conexion("TLX009MXDB");
    $res = sqlsrv_query($conn, "SELECT NoDepto, NombreDepto FROM TLX009MXDB.dbo.tblDepartamentos");
    if ($res !== false) {
        while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
            $nomNorm = normalizar($row['NombreDepto'] ?? '');
            if ($nomNorm !== '' && isset($deptosCsv[$nomNorm])) {
                $ids[] = trim((string) $row['NoDepto']);
                $nombres[] = trim((string) $row['NombreDepto']);
            }
        }
    }
}

echo json_encode([
    'ok' => true,
    'ibm' => $ibmBuscado,
    'ids' => array_values(array_unique($ids)),
    'nombres' => $nombres
], JSON_UNESCAPED_UNICODE);