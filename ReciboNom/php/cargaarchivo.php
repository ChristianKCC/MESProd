<?php

if (isset($_FILES['archivo'])) {
    $archivo = $_FILES['archivo'];
    $nombreArchivo = 'archivo.pdf';
    $ubicacionTemporal = $archivo['tmp_name'];
    $ubicacionDestino =  '../' .$nombreArchivo;

    if (move_uploaded_file($ubicacionTemporal, $ubicacionDestino)) {
        echo 'Archivo subido exitosamente';
    } else {
        echo 'Error al subir el archivo';
    }
} else {
    echo 'No se recibió ningún archivo';
}
?>