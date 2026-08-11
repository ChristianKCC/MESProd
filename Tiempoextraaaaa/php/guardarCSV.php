<?php
// Endpoint para guardar datos en CSV
if (isset($_GET['guardarCSV'])) {
    // Carpeta donde se guardarán los CSV
    $dir = __DIR__ . "/../solicitudes_te";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $file = $dir . "/solicitudes.csv";

    // Abrir archivo en modo append
    $fp = fopen($file, "a");

    // Si el archivo está vacío, escribir encabezados
    if (filesize($file) === 0) {
        fputcsv($fp, [
            "NoEmp", "Folio", "Fecha", "HoraI", "HoraF", "Motivos", "Maquina", "Razon",
            "TurnoSeleccionado", "HoraFinalSinMargen", "HoraFinalConMargen", "Estado"
        ]);
    }

    // Escribir fila con los datos recibidos
    fputcsv($fp, [
        $_POST["noemp"] ?? "",
        $_POST["folio"] ?? "",
        $_POST["fechainput"] ?? "",
        $_POST["horai"] ?? "",
        $_POST["horaf"] ?? "",
        $_POST["motivos"] ?? "",
        $_POST["maquina"] ?? "",
        $_POST["razon"] ?? "",
        $_POST["turnoSeleccionado"] ?? "",
        $_POST["horaFinalSinMargen"] ?? "",
        $_POST["horaFinalConMargen"] ?? "",
        $_POST["estado"] ?? ""
    ]);

    fclose($fp);

    echo json_encode("CSV Guardado");
    exit;
}
?>
