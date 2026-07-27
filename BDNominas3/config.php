<?php
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Archivo único de BD Nóminas
define('CSV_NOMINAS_FILE', UPLOAD_DIR . 'AUTORIZACION GERENCIA Y SUPERINTENDENTE.csv');
define('ALLOWED_EXT', ['csv']);

// Límite de tamaño de archivo permitido: 5MB
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Ajustes de nombres según los encabezados del archivo BD Nóminas
define('COL_NUMERO', 'NUMERO');
define('COL_NOMBRE', 'NOMBRE');
define('COL_PUESTO', 'NOMBRE DE PUESTO');
define('COL_CVE_DEPT', 'CVE DEPT');
define('COL_DEPTO', 'NOMBRE DE DEPTO');
define('COL_ID_JEFE', 'ID JEFE');
define('COL_NOM_JEFE', 'NOM JEFE');
define('COL_TIPO', 'TIPO');
define('COL_IBM', 'IBM');
define('COL_SUPERINTENTE', 'SUPERINTENTE');

// Separador de CSV
define('CSV_SEPARATOR', ',');
?>
