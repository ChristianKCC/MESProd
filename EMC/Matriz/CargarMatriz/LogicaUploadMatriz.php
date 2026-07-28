<?php
define('UPLOAD_DIR_MEMC', __DIR__ . '/../../uploads/');
define('EXCEL_FILE_MEMC', UPLOAD_DIR_MEMC . 'datos.xlsx');
define('CSV_FILE_MEMC', UPLOAD_DIR_MEMC . 'datos.csv');
define('MAX_SIZE_MB', 20);

/* ── Procesar subida ─────────────────────── */
$response = [];
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['EXCEL_FILE_MEMC'])) {

    $file = $_FILES['EXCEL_FILE_MEMC'];
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
        if (!is_dir(UPLOAD_DIR_MEMC)) mkdir(UPLOAD_DIR_MEMC, 0755, true);

        /* Guardar con nombre unificado según tipo */
        $destFile = ($ext === 'csv') ? CSV_FILE_MEMC : EXCEL_FILE_MEMC;

        /* Si existe el otro tipo, eliminarlo para no confundir */
        if ($ext === 'csv' && file_exists(EXCEL_FILE_MEMC)) unlink(EXCEL_FILE_MEMC);
        if ($ext !== 'csv' && file_exists(CSV_FILE_MEMC)) unlink(CSV_FILE_MEMC);

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

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }
}

/* ── Estado actual ───────────────────────── */
$activeFile = null;
$activeExt = null;

if (file_exists(EXCEL_FILE_MEMC)) { $activeFile = EXCEL_FILE_MEMC; $activeExt = 'xlsx'; }
elseif (file_exists(CSV_FILE_MEMC)) { $activeFile = CSV_FILE_MEMC; $activeExt = 'csv'; }

$fileExists = $activeFile !== null;
$fileName = $fileExists ? basename($activeFile) : null;
$fileUpdated = $fileExists ? date('d/m/Y H:i:s', filemtime($activeFile)) : null;
$fileSize = $fileExists ? round(filesize($activeFile) / 1024, 1) . ' KB' : null;

$typeLabel = match($activeExt) {
    'xlsx' => 'Excel (.xlsx)',
    'xls' => 'Excel (.xls)',
    'csv' => 'CSV (.csv)',
    default => '—'
};
?>