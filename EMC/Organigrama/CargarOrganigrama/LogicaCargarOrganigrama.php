<?php

define('UPLOAD_DIR', __DIR__ . '/../../uploads/');
define('PPTX_FILE', UPLOAD_DIR . 'organigrama.pptx');
define('MAX_SIZE_MB', 50);

/* ── Procesar subida ─────────────────────── */
$response = [];
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pptx_file'])) {

    $file = $_FILES['pptx_file'];
    $errors = [];

    /* Validaciones */
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error al recibir el archivo (código ' . $file['error'] . ').';
    }

    $maxBytes = MAX_SIZE_MB * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        $errors[] = 'El archivo supera el tamaño máximo permitido (' . MAX_SIZE_MB . ' MB).';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip', // algunos sistemas reportan pptx como zip
        'application/octet-stream',
    ];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pptx' && $ext !== 'ppt') {
        $errors[] = 'Solo se permiten archivos PowerPoint (.pptx / .ppt).';
    }

    if (empty($errors)) {
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], PPTX_FILE)) {
            $response = [
                'success' => true,
                'message' => '¡Presentación actualizada correctamente!',
                'filename' => basename($file['name']),
                'updated' => date('d/m/Y H:i:s'),
                'size' => round($file['size'] / 1024, 1) . ' KB',
            ];
        } else {
            $response = ['success' => false, 'message' => 'No se pudo guardar el archivo. Verifica los permisos del servidor.'];
        }
    } else {
        $response = ['success' => false, 'message' => implode(' ', $errors)];
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }
}

/* ── Estado actual del archivo ───────────── */
$fileExists = file_exists(PPTX_FILE);
$fileName = $fileExists ? 'organigrama.pptx' : null;
$fileUpdated = $fileExists ? date('d/m/Y H:i:s', filemtime(PPTX_FILE)) : null;
$fileSize = $fileExists ? round(filesize(PPTX_FILE) / 1024, 1) . ' KB' : null;
?>