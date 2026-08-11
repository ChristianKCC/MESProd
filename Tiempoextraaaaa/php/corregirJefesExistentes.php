<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Requiere: ClassConexion, las constantes del CSV (CSV_NOMINAS_FILE, CSV_SEPARATOR,
// COL_NUMERO, COL_ID_JEFE, COL_IBM, COL_NOMBRE_DEPTO) y el helper normalizarDepto()
require_once "../../conexion.php";
require_once(__DIR__ . "/../../BDNominas/config.php");
require_once(__DIR__ . "/deptosSupervisor.php");

// ── CONFIG ────────────────────────────────────────────────────────────
$APLICAR        = isset($_GET['aplicar']) && $_GET['aplicar'] == '1'; // ?aplicar=1 ejecuta; sin él solo simula
$SOLO_PENDIENTES = true; // true = solo folios no terminados/autorizados (recomendado)
// ──────────────────────────────────────────────────────────────────────

header('Content-Type: text/plain; charset=utf-8');

// 1) Mapa NoDepto -> NombreDepto normalizado (una sola consulta)
$conn9 = (new ClassConexion())->conexion("TLX009MXDB");
$resD  = sqlsrv_query($conn9, "SELECT NoDepto, NombreDepto FROM TLX009MXDB.dbo.tblDepartamentos");
$mapDeptos = [];
while ($resD && ($r = sqlsrv_fetch_array($resD, SQLSRV_FETCH_ASSOC))) {
    $mapDeptos[trim((string)$r['NoDepto'])] = normalizarDepto($r['NombreDepto'] ?? '');
}

// 2) Mapa del CSV: [ibm][deptoNorm] => jefe/superint  +  fallback por ibm (1er match)
function construirMapaJefes() {
    $mapa = [];       // $mapa[ibm][deptoNorm] = ['jefe'=>, 'superintendente'=>]
    $fallback = [];   // $fallback[ibm] = ['jefe'=>, 'superintendente'=>] (primera fila)

    if (!file_exists(CSV_NOMINAS_FILE)) return [$mapa, $fallback];
    $h = fopen(CSV_NOMINAS_FILE, "r");
    if (!$h) return [$mapa, $fallback];

    $bom = fread($h, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($h);

    $headers = fgetcsv($h, 0, CSV_SEPARATOR);
    if (!$headers) { fclose($h); return [$mapa, $fallback]; }
    $headers = array_map(fn($x) => preg_replace('/^\xEF\xBB\xBF/', '', trim($x)), $headers);

    while (($line = fgetcsv($h, 0, CSV_SEPARATOR)) !== false) {
        if (array_filter($line) === []) continue;
        if (count($line) < count($headers))      $line = array_pad($line, count($headers), '');
        elseif (count($line) > count($headers))  $line = array_slice($line, 0, count($headers));
        $row = @array_combine($headers, $line);
        if (!$row) continue;

        $ibm = trim($row[COL_NUMERO] ?? '');
        if ($ibm === '') continue;

        $fila = [
            "jefe"            => ($v = trim($row[COL_ID_JEFE] ?? '')) !== '' ? $v : null,
            "superintendente" => ($s = trim($row[COL_IBM] ?? ''))     !== '' ? $s : null
        ];
        $deptoNorm = normalizarDepto($row[COL_DEPTO] ?? '');

        if ($deptoNorm !== '') $mapa[$ibm][$deptoNorm] = $fila;
        if (!isset($fallback[$ibm])) $fallback[$ibm] = $fila; // respaldo = 1er match por IBM
    }
    fclose($h);
    return [$mapa, $fallback];
}
[$mapaCsv, $fallbackCsv] = construirMapaJefes();

// Resuelve jefe/superint correctos igual que buscarJefeInmediato()
function resolverJefe($ibm, $deptoNorm, $mapaCsv, $fallbackCsv) {
    $ibm = trim((string)$ibm);
    if ($deptoNorm !== '' && isset($mapaCsv[$ibm][$deptoNorm]))
        return [$mapaCsv[$ibm][$deptoNorm], 'exacto'];
    if (isset($fallbackCsv[$ibm]))
        return [$fallbackCsv[$ibm], 'fallback'];
    return [null, 'sin-resolver'];
}

// 3) Recorrer registros existentes y comparar
$conn3 = (new ClassConexion())->conexion("TLX003MXDB");
$whereEstado = $SOLO_PENDIENTES ? "WHERE terminado IS NULL AND autorizado IS NULL" : "";
$sql = "SELECT id, supervisor, departamento, noempautoriza, noempSupIntendente
        FROM TiempoextraEnc $whereEstado ORDER BY id";
$res = sqlsrv_query($conn3, $sql);

$cambios = []; $iguales = 0; $sinResolver = 0;
while ($res && ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC))) {
    $noDepto   = trim((string)$row['departamento']);
    $deptoNorm = $mapDeptos[$noDepto] ?? '';
    [$correcto, $tipo] = resolverJefe($row['supervisor'], $deptoNorm, $mapaCsv, $fallbackCsv);

    if ($correcto === null) { $sinResolver++; continue; }

    $jefeActual   = trim((string)($row['noempautoriza'] ?? ''));
    $superActual  = trim((string)($row['noempSupIntendente'] ?? ''));
    $jefeNuevo    = $correcto['jefe'];
    $superNuevo   = $correcto['superintendente'];

    $set = []; $params = [];
    if ($jefeNuevo  !== null && (string)$jefeNuevo  !== $jefeActual)  { $set[] = "noempautoriza = ?";      $params[] = $jefeNuevo; }
    if ($superNuevo !== null && (string)$superNuevo !== $superActual) { $set[] = "noempSupIntendente = ?"; $params[] = $superNuevo; }

    if (empty($set)) { $iguales++; continue; }

    $cambios[] = [
        'id' => $row['id'], 'sup' => $row['supervisor'], 'depto' => $noDepto,
        'jefe' => "$jefeActual -> " . ($jefeNuevo ?? '=='),
        'super' => "$superActual -> " . ($superNuevo ?? '=='),
        'tipo' => $tipo, 'set' => $set, 'params' => $params
    ];
}

// 4) Reporte / aplicación
echo ($APLICAR ? "== MODO APLICAR ==\n" : "== MODO SIMULACIÓN (agrega ?aplicar=1 para ejecutar) ==\n");
echo "Registros a cambiar: " . count($cambios) . " | Ya correctos: $iguales | Sin resolver: $sinResolver\n";
echo str_repeat("-", 80) . "\n";

$aplicados = 0;
foreach ($cambios as $c) {
    echo "id={$c['id']} sup={$c['sup']} depto={$c['depto']} [{$c['tipo']}] "
       . "jefe: {$c['jefe']} | super: {$c['super']}\n";

    if ($APLICAR) {
        $upd = "UPDATE TiempoextraEnc SET " . implode(", ", $c['set']) . " WHERE id = ?";
        $p   = array_merge($c['params'], [$c['id']]);
        $r   = sqlsrv_query($conn3, $upd, $p);
        if ($r !== false) $aplicados++;
        else echo "   ERROR al actualizar id={$c['id']}: " . print_r(sqlsrv_errors(), true) . "\n";
    }
}
if ($APLICAR) echo str_repeat("-", 80) . "\nActualizados: $aplicados\n";