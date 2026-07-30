<?php
// Funcion auxiliar de autoload para carga de archivos (composer) en excel
spl_autoload_register(function ($class) {
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';
    $base_dir = __DIR__ . '/PhpSpreadsheet/src/PhpSpreadsheet/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Verificar a que url o archivo esta apuntando
    var_dump($class, $file);
    
    if (file_exists($file)) {
        require $file;
    }
});


// Funcion ajustada a cuando se tengan las dependencias descargadas en
/*
Asistencias (Colocar carpeta descargada con librerias)
     Psr
        SimpleCache

spl_autoload_register(function ($class) {
    $prefixes = [
        'PhpOffice\\PhpSpreadsheet\\' => __DIR__ . '/PhpSpreadsheet/src/',
        'Psr\\SimpleCache\\' => __DIR__ . '/Psr/SimpleCache/',
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});
*/