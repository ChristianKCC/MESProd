<?php
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$host = trim($data['host'] ?? '');
$puerto = (int) ($data['puerto'] ?? 9100);

if ($host === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Host/IP requerido']);
    exit;
}

$fp = @fsockopen($host, $puerto, $errno, $errstr, 3); // timeout 3s
if (!$fp) {
    echo json_encode(['ok' => false, 'error' => "Sin conexión: $errstr ($errno)"]);
    exit;
}
fclose($fp);
echo json_encode(['ok' => true, 'mensaje' => "Conexión OK a $host:$puerto"]);



/*
PRUEBAS DESDE CONSOLDEV CHROME TOOLS
*/

// fetch("http://localhost/Mes/KCMes/bitacora/php/impresora_test.php", {
//   method: "POST",
//   headers: { "Content-Type": "application/json" },
//   body: JSON.stringify({ host: "172.26.25.171", puerto: 9100 })
// })
// .then(r => r.json())
// .then(console.log);

// Promise {<pending>}
// {ok: false, error: 'Sin conexión: Se produjo un error durante el inten… el host conectado no ha podido responder (10060)'}