<?php
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');
define('EXCEL_FILE', UPLOAD_DIR . 'datos.xlsx');
define('CSV_FILE', UPLOAD_DIR . 'datos.csv');
define('MAX_SIZE_MB', 20);

$response = [];
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

/* ── Si es un POST por AJAX, respondemos SIEMPRE en JSON y salimos ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {

    while (ob_get_level()) {
        ob_end_clean();
    }   // descarta cualquier salida previa (header, warnings)
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_FILES['excel_file'])) {
        echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo (excel_file).']);
        exit;
    }

    $file = $_FILES['excel_file'];
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error al recibir el archivo (código ' . $file['error'] . ').';
    }
    $maxBytes = MAX_SIZE_MB * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        $errors[] = 'El archivo supera el tamaño máximo (' . MAX_SIZE_MB . ' MB).';
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
        $errors[] = 'Solo se permiten archivos Excel (.xlsx, .xls) o CSV (.csv).';
    }

    if (empty($errors)) {
        if (!is_dir(UPLOAD_DIR))
            mkdir(UPLOAD_DIR, 0755, true);
        $destFile = ($ext === 'csv') ? CSV_FILE : EXCEL_FILE;
        if ($ext === 'csv' && file_exists(EXCEL_FILE))
            unlink(EXCEL_FILE);
        if ($ext !== 'csv' && file_exists(CSV_FILE))
            unlink(CSV_FILE);

        if (move_uploaded_file($file['tmp_name'], $destFile)) {
            $response = [
                'success' => true,
                'message' => '¡Archivo cargado correctamente!',
                'filename' => basename($file['name']),
                'ext' => $ext,
                'updated' => date('d/m/Y H:i:s'),
                'size' => round($file['size'] / 1024, 1) . ' KB',
            ];
        } else {
            $response = ['success' => false, 'message' => 'No se pudo guardar el archivo. Verifica los permisos del servidor.'];
        }
    } else {
        $response = ['success' => false, 'message' => implode(' ', $errors)];
    }

    echo json_encode($response);
    exit;   // <- corta ANTES de que CargarMatriz.php incluya el header
}

/* ── (De aquí para abajo queda igual: estado actual para la vista normal) ── */
$activeFile = null;
$activeExt = null;
if (file_exists(EXCEL_FILE)) {
    $activeFile = EXCEL_FILE;
    $activeExt = 'xlsx';
} elseif (file_exists(CSV_FILE)) {
    $activeFile = CSV_FILE;
    $activeExt = 'csv';
}
$fileExists = $activeFile !== null;
$fileName = $fileExists ? basename($activeFile) : null;
$fileUpdated = $fileExists ? date('d/m/Y H:i:s', filemtime($activeFile)) : null;
$fileSize = $fileExists ? round(filesize($activeFile) / 1024, 1) . ' KB' : null;
$typeLabel = match ($activeExt) {
    'xlsx' => 'Excel (.xlsx)', 'xls' => 'Excel (.xls)', 'csv' => 'CSV (.csv)', default => '—'
};
?>