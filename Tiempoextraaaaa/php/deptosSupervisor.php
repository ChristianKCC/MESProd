<?php

function normalizarDepto($txt) {
    $txt = (string)$txt;
    if ($txt !== '' && !mb_check_encoding($txt, 'UTF-8')) {
        $txt = mb_convert_encoding($txt, 'UTF-8', 'Windows-1252');
    }
    $txt = mb_strtolower(trim($txt), 'UTF-8');
    $txt = strtr($txt, [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n',
        'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u'
    ]);
    return preg_replace('/\s+/', ' ', $txt);
}

function soloIbmDepto($v) { return preg_replace('/[^\d].*$/', '', trim((string)$v)); }

/**
 * Devuelve array de NoDepto (strings) que el supervisor puede ver,
 * cruzando el CSV de autorización contra tblDepartamentos.
 */
function deptosPermitidosSupervisor($ibm) {
    $ibmBuscado = soloIbmDepto($ibm);
    if ($ibmBuscado === '') return [];

    // AJUSTA la ruta a tu estructura real
    $rutaCsv = __DIR__ . "/../../BDNominas/uploads/AUTORIZACION GERENCIA Y SUPERINTENDENTE.csv";
    if (!is_file($rutaCsv)) return [];

    $fh = fopen($rutaCsv, 'r');
    if ($fh === false) return [];

    // Detectar delimitador con la 1ª línea
    $primera = ltrim((string)fgets($fh), "\xEF\xBB\xBF");
    $conteos = [';'=>substr_count($primera,';'), ','=>substr_count($primera,','), "\t"=>substr_count($primera,"\t")];
    arsort($conteos);
    $delim = array_key_first($conteos) ?: ',';
    rewind($fh);

    $deptosCsv = []; // set: nombreNormalizado => true
    $enc = true;
    while (($fila = fgetcsv($fh, 0, $delim)) !== false) {
        if ($enc) { $enc = false; continue; }        // saltar encabezado
        if (!isset($fila[0])) continue;
        if (soloIbmDepto($fila[0]) !== $ibmBuscado) continue;
        $nom = normalizarDepto($fila[5] ?? '');       // F = NOMBRE DE DEPTO
        if ($nom !== '') $deptosCsv[$nom] = true;
    }
    fclose($fh);
    if (empty($deptosCsv)) return [];

    // Cruzar contra tblDepartamentos
    $conn = (new ClassConexion())->conexion("TLX009MXDB");
    $res  = sqlsrv_query($conn, "SELECT NoDepto, NombreDepto FROM TLX009MXDB.dbo.tblDepartamentos");
    $ids = [];
    if ($res !== false) {
        while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
            $nomNorm = normalizarDepto($row['NombreDepto'] ?? '');
            if ($nomNorm !== '' && isset($deptosCsv[$nomNorm])) {
                $ids[] = trim((string)$row['NoDepto']);
            }
        }
    }
    return array_values(array_unique($ids));
}

/**
 * Devuelve el NombreDepto (tal cual en BD) a partir de un NoDepto.
 */
function nombreDeptoPorNo($noDepto) {
    $noDepto = trim((string)$noDepto);
    if ($noDepto === '') return '';
    $conn = (new ClassConexion())->conexion("TLX009MXDB");
    $res  = sqlsrv_query(
        $conn,
        "SELECT NombreDepto FROM TLX009MXDB.dbo.tblDepartamentos WHERE NoDepto = ?",
        [$noDepto]
    );
    if ($res && ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC))) {
        return trim((string)$row['NombreDepto']);
    }
    return '';
}