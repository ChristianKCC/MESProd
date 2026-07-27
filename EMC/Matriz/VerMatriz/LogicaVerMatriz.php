<?php
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');
define('EXCEL_FILE', UPLOAD_DIR . 'datos.xlsx');
define('CSV_FILE', UPLOAD_DIR . 'datos.csv');

$activeFile = null;
$activeExt = null;

if (file_exists(EXCEL_FILE)) { $activeFile = EXCEL_FILE; $activeExt = 'xlsx'; }
elseif (file_exists(CSV_FILE)) { $activeFile = CSV_FILE; $activeExt = 'csv'; }

$fileExists = $activeFile !== null;
$fileName = $fileExists ? basename($activeFile) : null;
$fileUpdated = $fileExists ? date('d/m/Y H:i:s', filemtime($activeFile)) : null;
$fileSize = $fileExists ? round(filesize($activeFile) / 1024, 1) . ' KB' : null;

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
// $fileUrl = $fileExists
//           ? $protocol . '://' . $host . $basePath . '/uploads/' . $fileName
//           : '';
$fileUrl = $fileExists ? $protocol . '://' . $host . '/mes/KCMes/EMC/uploads/' . $fileName : '';


?>