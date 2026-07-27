<?php
require_once('../../conexion.php');
$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");
if ($_FILES['archivo_csv']['error'] == UPLOAD_ERR_OK) {
    $csvFile = $_FILES['archivo_csv']['tmp_name'];
    $file = fopen($csvFile, 'r');
    $tipo = $_POST['tipoempleado'];
    while (($line = fgetcsv($file)) !== FALSE) {
        $fecha_formateada = DateTime::createFromFormat('d/m/Y H:i',$line[0])->format('Y-m-d H:i:s');
        $query = "INSERT INTO tblSistenciasbiostar (fecha, noemp, tipo) VALUES ('" . $fecha_formateada . "','" . $line[1] . "', '".$tipo."')";
        $stmt = sqlsrv_query($conn, $query);
        if ($stmt === false) {
            if (($errors = sqlsrv_errors()) != null) {
                foreach ($errors as $error) {
                    echo "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
                    echo "code: " . $error['code'] . "<br />";
                    echo "message: " . $error['message'] . "<br />";
                }
            }
        }
    }
    fclose($file);
    echo "Archivo CSV cargado correctamente.";
    // header('Location: ../biostart');
} else {
    echo "Error al cargar el archivo CSV.";
}
