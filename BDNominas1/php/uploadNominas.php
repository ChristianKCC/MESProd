<?php
require_once(__DIR__ . "/../config.php");

// Crear carpeta si no existe
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['archivoNominas'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $tmpPath = $file['tmp_name'];

    $mensaje = '';
    $tipo_msg = '';

    $destino = CSV_NOMINAS_FILE;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $mensaje = 'Error al subir el archivo.';
        $tipo_msg = 'danger';
    } elseif (!in_array($ext, ALLOWED_EXT)) {
        $mensaje = 'Archivo no permitido. Solo CSV.';
        $tipo_msg = 'warning';
    } elseif ($file['size'] > MAX_FILE_SIZE) {
        $mensaje = 'Archivo demasiado grande. Máx 5MB.';
        $tipo_msg = 'warning';
    } else {
        // Si ya existe un CSV activo se intenta renombrar como respaldo
        if (file_exists($destino)) {
            $backupName = UPLOAD_DIR . '/backup_nominas_' . date("dmY_His") . '.csv';
            if (!@rename($destino, $backupName)) {
                header("Location: ../index.php?fileopen=1");                
                exit;
            }
        }

        if ($ext === 'csv') {
            if (move_uploaded_file($tmpPath, $destino)) {
                $mensaje = 'Archivo CSV de BD Nóminas actualizado correctamente.';
                $tipo_msg = 'success';
            } else {
                $mensaje = 'No se pudo guardar el archivo.';
                $tipo_msg = 'danger';
            }
        } else {
            $destinoExcel = UPLOAD_DIR . '/' . basename($file['name']);
            if (move_uploaded_file($tmpPath, $destinoExcel)) {
                $mensaje = 'Archivo Excel guardado. Convierte a CSV para que el sistema lo lea.';
                $tipo_msg = 'info';
            } else {
                $mensaje = 'No se pudo guardar el archivo Excel.';
                $tipo_msg = 'danger';
            }
        }
    }

    header("Location: ../index.php?msg=" . urlencode($mensaje) . "&tipo=" . urlencode($tipo_msg));
    exit;
}

// Verificar si ya existe un CSV activo de BD Nóminas
$csvNominasExiste = file_exists(CSV_NOMINAS_FILE);
$csvNominasFecha = $csvNominasExiste ? date("d/m/Y H:i:s", filemtime(CSV_NOMINAS_FILE)) : null;
$csvNominasLineas = $csvNominasExiste ? count(file(CSV_NOMINAS_FILE)) - 1 : 0;

// Mensajes desde GET
$mensaje = $_GET['msg'] ?? '';
$tipo_msg = $_GET['tipo'] ?? '';
?>
