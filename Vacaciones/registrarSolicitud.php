<?php
require_once("config.php");
require_once("./php/vacacionesLogistica.php");

$ibm = $ibmSession;
$nombre = col($empleado, COL_NOMBRE);
$tipo = col($empleado, COL_TIPO);
$diasSolicitados = (int)($_POST['dias'] ?? 0);
$fechaSolicitud = date("Y-m-d");
$estatus = "Pendiente";
$origen = $_POST['origen'] ?? 'consulta.php';

$diasDisponibles = (int)($empleado[COL_VAC] ?? 0);

// Metodo anterior para resta de dias contra el sistema
// // Restar solicitudes previas
// if (file_exists(HISTORIAL_FILE)) {
//     $handle = fopen(HISTORIAL_FILE, "r");
//     $headers = fgetcsv($handle);
//     while (($line = fgetcsv($handle)) !== false) {
//         if (trim($line[0]) === $ibm && trim($line[5]) !== "Rechazado") {
//             $diasDisponibles -= (int)$line[4];
//         }
//     }
//     fclose($handle);
// }

// Función para imprimir SweetAlert en HTML válido
function mostrarAlerta($icon, $title, $text, $redirect = null) {
    echo "<!DOCTYPE html><html><head>
            <meta charset='UTF-8'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
          </head><body>
          <script>
            Swal.fire({
                icon: '$icon',
                title: '$title',
                text: '$text'
            }).then(() => {";
    if ($redirect) {
        echo "window.location.href = '$redirect';";
    } else {
        echo "window.history.back();";
    }
    echo "});
          </script>
          </body></html>";
    exit;
}

// Validación
if ($diasSolicitados <= 0) {
    mostrarAlerta("error", "Error", "Debes seleccionar al menos 1 día.");
}
if ($diasSolicitados > $diasDisponibles) {
    mostrarAlerta("error", "Error", "No tienes suficientes días disponibles.");
}

// Creacion de excel para el registro de solicitudes de vacaciones
// Crear archivo si no existe
// $fechaDe = $_POST['fecha_de'] ?? '';
// $fechaA = $_POST['fecha_a'] ?? '';

// Crear archivo si no existe
// if (!file_exists(HISTORIAL_FILE)) {
//     $handle = fopen(HISTORIAL_FILE, "w");
//     fputcsv($handle, ["IBM","NOMBRE","TIPO","F. SOLICITUD","DE","A","DIAS","ESTATUS"]);
//     fclose($handle);
// }

// Guardar nueva solicitud
// $handle = fopen(HISTORIAL_FILE, "a");
// fputcsv($handle, [$ibm, $nombre, $tipo, $fechaSolicitud, $fechaDe, $fechaA, $diasSolicitados, $estatus]);
// fclose($handle);

// Mensaje de éxito
mostrarAlerta("success", "Solicitud realizada", "Se solicitaron $diasSolicitados días correctamente.", "Consulta.php");
?>
