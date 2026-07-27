<?php
require_once("../Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location:../index/index.php");
    exit;
}

require_once("./config.php");
define('HISTORIAL_FILE', UPLOAD_DIR . "Historial_Solicitudes_Vacaciones.csv");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ibm = $_POST["ibm"];
    $fechaDe = $_POST["fecha_de"];
    $fechaA = $_POST["fecha_a"];
    $accion = $_POST["accion"];

    $rows = [];
    if (file_exists(HISTORIAL_FILE)) {
        $handle = fopen(HISTORIAL_FILE, "r");
        $headers = fgetcsv($handle);
        $rows[] = $headers;
        while (($line = fgetcsv($handle)) !== false) {
            if ($line[0] === $ibm && $line[4] === $fechaDe && $line[5] === $fechaA) {
                $line[7] = $accion;
            }
            $rows[] = $line;
        }
        fclose($handle);

        // Abrir en escritura
        $handle = @fopen(HISTORIAL_FILE, "w");
        if ($handle === false) {
            // Mostrar SweetAlert si no se pudo abrir
            echo "<!DOCTYPE html><html><head>
                    <meta charset='UTF-8'>
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                </head><body>";
            echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Archivo en uso',
                    text: 'El archivo de historial está abierto en otro programa. Ciérralo antes de continuar.',
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    window.location.href = 'autorizar.php';
                });
            </script>";
            echo "</body></html>";
            exit;
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}

header("Location: autorizar.php");
exit;