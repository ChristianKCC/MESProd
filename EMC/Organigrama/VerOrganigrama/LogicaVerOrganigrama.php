<?php
define('PPTX_FILE',  __DIR__ . '/../../uploads/organigrama.pptx');
define('THUMB_FILE', __DIR__ . '/../../uploads/organigrama_thumb.jpg');

$fileExists  = file_exists(PPTX_FILE);
$fileUpdated = $fileExists ? date('d/m/Y H:i:s', filemtime(PPTX_FILE)) : null;
$fileSize    = $fileExists ? round(filesize(PPTX_FILE) / 1024, 1) . ' KB' : null;
$fileName    = 'organigrama.pptx';

/* ── URL pública del archivo ── */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$fileUrl  = $fileExists ? $protocol . '://' . $host . '/mes/KCMes/EMC/uploads/organigrama.pptx' : '';

/* ── Protocolo ms-powerpoint ── */
$msPptUrl = 'ms-powerpoint:ofv|u|' . $fileUrl;

/* ── Thumbnail con LibreOffice (si disponible) ── */
$thumbExists = file_exists(THUMB_FILE);
$needThumb   = $fileExists && (!$thumbExists ||
               filemtime(PPTX_FILE) > filemtime(THUMB_FILE));

if ($needThumb) {
    $loPaths = [
        'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
        'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        '/usr/bin/libreoffice',
        '/usr/local/bin/libreoffice',
        'soffice',
        'libreoffice',
    ];
    $uploadDir = __DIR__ . '/../../uploads/';
    foreach ($loPaths as $lo) {
        if (!file_exists($lo) && !in_array($lo, ['soffice','libreoffice'])) continue;
        $isWin  = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $null   = $isWin ? '2>nul' : '2>/dev/null';
        $quoted = strpos($lo, ' ') !== false ? '"' . $lo . '"' : $lo;
        $cmd = $quoted . ' --headless --convert-to jpg:impress_jpg_Export'
             . ' --outdir ' . escapeshellarg($uploadDir)
             . ' ' . escapeshellarg(PPTX_FILE) . ' ' . $null;
        @exec($cmd);
        $generated = $uploadDir . 'organigrama.jpg';
        if (file_exists($generated)) {
            rename($generated, THUMB_FILE);
            $thumbExists = true;
            break;
        }
    }
}

?>