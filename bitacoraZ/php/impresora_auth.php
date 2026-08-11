<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Genera tu hash una vez con: echo password_hash('TuClave', PASSWORD_DEFAULT);
// SistemasFG67
const IMP_HASH = '$2y$10$M7YyX5kFbHFXMPAfRXyaO.cbD4V1HSxsbYqhavelntYoLprMycVKq';
const IMP_MINUTOS = 1;

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$pass = (string) ($in['pass'] ?? '');

// Freno anti fuerza bruta
$_SESSION['imp_try'] = ($_SESSION['imp_try'] ?? 0) + 1;
if ($_SESSION['imp_try'] > 5 && (time() - ($_SESSION['imp_try_ts'] ?? 0)) < 300) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Demasiados intentos. Espera 5 minutos.']);
    exit;
}
$_SESSION['imp_try_ts'] = time();

if (!password_verify($pass, IMP_HASH)) {
    echo json_encode(['ok' => false, 'error' => 'Contraseña incorrecta']);
    exit;
}

$_SESSION['imp_try'] = 0;
$_SESSION['imp_exp'] = time() + (IMP_MINUTOS * 60);
echo json_encode(['ok' => true, 'minutos' => IMP_MINUTOS]);