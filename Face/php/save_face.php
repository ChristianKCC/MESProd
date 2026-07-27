
<?php
// Ruta del archivo JSON
$file = '../data/faces.json';

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'];
$descriptor = $data['descriptor'];

// Leer archivo existente
$faces = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// Agregar nuevo rostro
$faces[] = ['name' => $name, 'descriptor' => $descriptor];

// Guardar en el archivo
file_put_contents($file, json_encode($faces));

echo "Rostro guardado correctamente.";
?>
