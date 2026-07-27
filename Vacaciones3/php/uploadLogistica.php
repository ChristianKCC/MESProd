<?php
require_once(__DIR__ . "/../config.php");

// Crear carpeta si no existe
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['archivo'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $tmpPath = $file['tmp_name'];

    $mensaje = '';
    $tipo_msg = '';

    // Determinar destino según formulario
    $destino = (isset($_POST['tipo']) && $_POST['tipo'] === 'sind') ? CSV_FILE_SIND : CSV_FILE;

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
        // Si ya existe un CSV activo se intenta renombrar
        if (file_exists($destino)) {
            $backupName = UPLOAD_DIR . '/backup_' . $file['name'] . date("dmY_His") . '.csv';
            if (!@rename($destino, $backupName)) {
                header("Location: ../Vacaciones/upload.php?fileopen=1&tipo=" . (isset($_POST['tipo']) ? $_POST['tipo'] : 'vac'));
                exit;
            }
        }

        if ($ext === 'csv') {
            if (move_uploaded_file($tmpPath, $destino)) {
                $mensaje = 'Archivo CSV actualizado correctamente.';
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

    header("Location: ../Vacaciones/upload.php?msg=" . urlencode($mensaje) . "&tipo=" . urlencode($tipo_msg));
    exit;
}

// Verificar si ya existe un CSV activo de vacaciones
$cvsExiste = file_exists(CSV_FILE);
$csvFecha = $cvsExiste ? date("d/m/Y H:i:s", filemtime(CSV_FILE)) : null;
$csvLineas = $cvsExiste ? count(file(CSV_FILE)) - 1 : 0;

// Verificar si ya existe el CSV de sindicalizados
$cvsSindExiste = file_exists(CSV_FILE_SIND);
$csvSindFecha = $cvsSindExiste ? date("d/m/Y H:i:s", filemtime(CSV_FILE_SIND)) : null;
$csvSindLineas = $cvsSindExiste ? count(file(CSV_FILE_SIND)) - 1 : 0;

// Mensajes desde GET
$mensaje = $_GET['msg'] ?? '';
$tipo_msg = $_GET['tipo'] ?? '';
?>
