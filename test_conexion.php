<?php
// ============================================================
//  TEST DE CONEXIÓN - ClassConexion
//  Ejecutar desde CLI: php test_conexion.php
//  O colócalo en tu carpeta htdocs/www y ábrelo en el browser
// ============================================================

// ── Tu clase original ────────────────────────────────────────
class ClassConexion {
    function conexion($database) {
        $serverName  = "172.26.24.101";
        $conexionInfo = [
            "Database"             => $database,
            "UID"                  => "Pra0mxpublic",
            "PWD"                  => "MxPra0202111P73",
            "TrustServerCertificate" => 1,
            "CharacterSet"         => "UTF-8",
        ];
        $conexion = sqlsrv_connect($serverName, $conexionInfo);
        if ($conexion) {
        } else {
            echo "Error en la conexion\n";
            die(print_r(sqlsrv_errors(), true));
        }
        return $conexion;
    }

    function validaquery($stmt) {
        if ($stmt === false) {
            if (($errors = sqlsrv_errors()) != null) {
                foreach ($errors as $error) {
                    echo json_encode(
                        "SQLSTATE: " . $error['SQLSTATE'] . "\n" .
                        "code: "     . $error['code']    . "\n" .
                        "message: "  . $error['message'] . "\n"
                    );
                }
            }
        } else {
            echo json_encode("ok");
        }
    }

    function conexioniap() {
        $serverName  = "172.26.24.101";
        $conexionInfo = [
            "Database"             => "iap0mxdb",
            "UID"                  => "iap0mxpublic",
            "PWD"                  => "Mxiap202105P31",
            "TrustServerCertificate" => 1,
            "CharacterSet"         => "UTF-8",
        ];
        $conexion = sqlsrv_connect($serverName, $conexionInfo);
        if ($conexion) {
        } else {
            echo "Error en la conexion\n";
            die(print_r(sqlsrv_errors(), true));
        }
        return $conexion;
    }
}

// ── Helpers de output (funciona en browser y en CLI) ─────────
$isCli = (php_sapi_name() === 'cli');
$nl     = $isCli ? "\n" : "<br>";
$sep    = $isCli ? str_repeat("-", 50) . "\n" : "<hr>";

if (!$isCli) {
    echo "<pre style='font-family:monospace; background:#1e1e1e; color:#d4d4d4; padding:20px;'>";
}

// ── Verificar extensión sqlsrv ────────────────────────────────
echo "=== VERIFICACIÓN DE ENTORNO ==={$nl}";
if (!extension_loaded('sqlsrv')) {
    echo "❌ La extensión 'sqlsrv' NO está cargada en este PHP.{$nl}";
    echo "   Instálala con: pecl install sqlsrv{$nl}";
    echo "   O descárgala desde: https://github.com/microsoft/msphpsql{$nl}";
    exit(1);
} else {
    echo "✅ Extensión sqlsrv: cargada (v" . phpversion('sqlsrv') . "){$nl}";
}
echo "   PHP version: " . PHP_VERSION . $nl;
echo $sep;

// ── Prueba 1: conexion() con base por defecto ─────────────────
echo "=== TEST 1: conexion('master') ==={$nl}";
// Usamos 'master' como base neutral para verificar credenciales Pra0mxpublic
// Cambia al nombre real de tu BD si lo conoces
$db = new ClassConexion();

try {
    $conn = $db->conexion('master');
    echo "✅ Conexión exitosa con Pra0mxpublic{$nl}";

    // Query mínima de verificación
    $stmt = sqlsrv_query($conn, "SELECT @@VERSION AS version, DB_NAME() AS db_actual");
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Solo la primera línea del @@VERSION para que no sea tan largo
        $ver = explode("\n", $row['version'])[0];
        echo "   Servidor : {$ver}{$nl}";
        echo "   BD actual: {$row['db_actual']}{$nl}";
    }
    sqlsrv_close($conn);
} catch (Throwable $e) {
    echo "❌ Excepción: " . $e->getMessage() . $nl;
}
echo $sep;

// ── Prueba 2: conexioniap() ───────────────────────────────────
echo "=== TEST 2: conexioniap() ==={$nl}";

try {
    $connIap = $db->conexioniap();
    echo "✅ Conexión exitosa con iap0mxpublic → iap0mxdb{$nl}";

    $stmt = sqlsrv_query($connIap, "SELECT @@SERVERNAME AS srv, DB_NAME() AS bd");
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo "   Servidor : {$row['srv']}{$nl}";
        echo "   BD actual: {$row['bd']}{$nl}";
    }
    sqlsrv_close($connIap);
} catch (Throwable $e) {
    echo "❌ Excepción: " . $e->getMessage() . $nl;
}
echo $sep;

// ── validaquery() demo ────────────────────────────────────────
echo "=== TEST 3: validaquery() con query real ==={$nl}";
$conn2 = $db->conexion('master');
$stmt  = sqlsrv_query($conn2, "SELECT GETDATE() AS ahora");
echo "   Resultado validaquery: ";
$db->validaquery($stmt);
echo $nl;

if ($stmt) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    echo "   Fecha/hora del servidor: " . $row['ahora']->format('Y-m-d H:i:s') . $nl;
}
sqlsrv_close($conn2);
echo $sep;

echo "=== FIN DE PRUEBAS ==={$nl}";

if (!$isCli) echo "</pre>";