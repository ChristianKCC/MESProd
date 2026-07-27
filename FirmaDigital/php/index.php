<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";

class FirmaDig
{
    function tblenc() {
        $usuarioSesion = $_SESSION['ibm'] ?? null;
        $usuariosPermitidos = ['60040','58998'];

        $permisoEdicion = in_array($usuarioSesion, $usuariosPermitidos);

        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $query = "SELECT 
        FD_id, 
        FD_noemp, 
        FD_nombre,
        FD_departamento,
        FD_puesto,
        FD_imgSign,
        FD_fechaRegistro
        FROM TLX002MXDB.dbo.tblMXPRFirmasDigitales
        ORDER BY tblMXPRFirmasDigitales.FD_id DESC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
    
        while ($row = sqlsrv_fetch_array($result)) {
            array_push(
                $array, [
                    "FD_id" => $row["FD_id"], 
                    "FD_noemp" => $row["FD_noemp"], 
                    "FD_nombre" => $row["FD_nombre"], 
                    "FD_departamento" => $row["FD_departamento"], 
                    "FD_puesto" => $row["FD_puesto"], 
                    "FD_imgSign" => $row["FD_imgSign"], 
                    "FD_fechaRegistro" => $row["FD_fechaRegistro"]->format("Y-m-d"),
                    "FD_permisoEdicion" => $permisoEdicion,
                    "FD_usuario" => $usuarioSesion
                    ]
                );
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }

    function guardarFirma() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || empty($data["imagen_b64"]) || empty($data["usuario_id"])) {
            echo json_encode(["ok" => false, "error" => "Datos incompletos"]);
            return;
        }

        $imagen_b64 = $data["imagen_b64"];
        $usuario_id = intval($data["usuario_id"]);
        $nombre_usuario = $data["nombre_usuario"] ?? null;
        $departamento_usuario = $data["departamento_usuario"] ?? null;
        $puesto_usuario = $data["puesto_usuario"] ?? null;

        // Validación de firma existente para ese IBM ANTES de guardar archivo
        $checkQuery = "SELECT COUNT(*) AS total FROM TLX002MXDB.dbo.tblMXPRFirmasDigitales WHERE FD_noemp = ?";
        $checkParams = [$usuario_id];
        $checkStmt = sqlsrv_query($conn, $checkQuery, $checkParams);

        if ($checkStmt === false) {
            echo json_encode(["ok" => false, "error" => "Error al validar existencia: " . print_r(sqlsrv_errors(), true)]);
            return;
        }

        $checkRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
        if ($checkRow["total"] > 0) {
            echo json_encode(["ok" => false, "error" => "Ya existe una firma registrada para este IBM"]);
            return;
        }

        // Carpeta de firmas
        $folder = __DIR__ . "/../firmas/";
        if (!is_dir($folder)) {
            if (!mkdir($folder, 0777, true)) {
                echo json_encode(["ok" => false, "error" => "No se pudo crear la carpeta de firmas"]);
                return;
            }
        }

        // Nombre de archivo
        $filename = $usuario_id . ".png";
        $filepath = $folder . $filename;

        // Obtencion de la imagen en base64
        $img_data = base64_decode($imagen_b64);
        if ($img_data === false) {
            echo json_encode(["ok" => false, "error" => "Error al decodificar base64"]);
            return;
        }
        file_put_contents($filepath, $img_data);

        // Post-proceso con GD para quitar fondo blanco
        // Carga de imagen
        $image = imagecreatefrompng($filepath);
        if ($image !== false) {
            $width  = imagesx($image);
            $height = imagesy($image);

            // Creacion de nuevo lienzo con canal alfa
            $newImage = imagecreatetruecolor($width, $height);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparent);

            // Iteracion de pixeles sobre el nuevo lienzo alfa            
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $colors = imagecolorsforindex($image, $rgb);

                    // Si el pixel esta cercano al blanco (255, 255, 255) se sobreescribe con transparente
                    if ($colors['red'] > 250 && $colors['green'] > 250 && $colors['blue'] > 250) {
                        imagesetpixel($newImage, $x, $y, $transparent);
                    } 
                    // De otro modo si es un colo distinto se conserva el pixel original y se incrusta en el lienzo alfa
                    else {
                        $color = imagecolorallocatealpha(
                            $newImage,
                            $colors['red'],
                            $colors['green'],
                            $colors['blue'],
                            $colors['alpha']
                        );
                        imagesetpixel($newImage, $x, $y, $color);
                    }
                }
            }

            // Almacenamiento de la imagen, actualizar la que existe en carpeta y liberar memoria
            imagepng($newImage, $filepath);
            imagedestroy($newImage);
            imagedestroy($image);
        }

        // Inserción en BD
        $query = "INSERT INTO TLX002MXDB.dbo.tblMXPRFirmasDigitales
                (FD_noemp, FD_nombre, FD_departamento, FD_puesto, FD_imgSign, FD_fechaRegistro) 
                VALUES (?, ?, ?, ?, ?, GETDATE())";

        $params = [$usuario_id, $nombre_usuario, $departamento_usuario, $puesto_usuario, $filename];
        $stmt = sqlsrv_prepare($conn, $query, $params);

        if (!$stmt) {
            echo json_encode(["ok" => false, "error" => "Error en prepare: " . print_r(sqlsrv_errors(), true)]);
            return;
        }

        if (sqlsrv_execute($stmt)) {
            $result = sqlsrv_query($conn, "SELECT SCOPE_IDENTITY() AS id");
            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            echo json_encode(["ok" => true, "firma_id" => $row["id"], "ruta" => $filename]);
        } else {
            echo json_encode(["ok" => false, "error" => "Error en execute: " . print_r(sqlsrv_errors(), true)]);
        }
    }


    function actualizarFirma() {
    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX002MXDB");

    // Recibir datos
    $id_usuario = $_POST["id_usuario"] ?? null;
    $ibm_usuario = $_POST["ibm_usuario"] ?? null;
    $nuevaFirma = $_POST["nuevaFirma"] ?? null;

    if (!$id_usuario || !$ibm_usuario || !$nuevaFirma) {
        echo json_encode(["success" => false, "error" => "Datos incompletos"]);
        return;
    }

    // Carpeta de firmas
    $folder = __DIR__ . "/../firmas/";
    if (!is_dir($folder)) {
        if (!mkdir($folder, 0777, true)) {
            echo json_encode(["success" => false, "error" => "No se pudo crear la carpeta de firmas"]);
            return;
        }
    }

    // Nombre de archivo
    $filename = $ibm_usuario . ".png";
    $filepath = $folder . $filename;

    // Guardar imagen base64
    $img_data = base64_decode($nuevaFirma);
    if ($img_data === false) {
        echo json_encode(["success" => false, "error" => "Error al decodificar base64"]);
        return;
    }
    file_put_contents($filepath, $img_data);

    // Post-proceso con GD para quitar fondo blanco
    // Carga de imagen
    $image = imagecreatefrompng($filepath);
    if ($image !== false) {
        $width  = imagesx($image);
        $height = imagesy($image);

        // Creacion de nuevo lienzo con canal alfa
        $newImage = imagecreatetruecolor($width, $height);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);

        // Iteracion de pixeles sobre el nuevo lienzo alfa            
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $rgb);

                // Si el pixel esta cercano al blanco (255, 255, 255) se sobreescribe con transparente
                if ($colors['red'] > 250 && $colors['green'] > 250 && $colors['blue'] > 250) {
                    imagesetpixel($newImage, $x, $y, $transparent);
                } 
                // De otro modo si es un colo distinto se conserva el pixel original y se incrusta en el lienzo alfa
                else {
                    $color = imagecolorallocatealpha(
                        $newImage,
                        $colors['red'],
                        $colors['green'],
                        $colors['blue'],
                        $colors['alpha']
                    );
                    imagesetpixel($newImage, $x, $y, $color);
                }
            }
        }

        // Almacenamiento de la imagen, actualizar la que existe en carpeta y liberar memoria
        imagepng($newImage, $filepath);
        imagedestroy($newImage);
        imagedestroy($image);
    }


    // Actualizar BD
    $query = "UPDATE TLX002MXDB.dbo.tblMXPRFirmasDigitales
              SET FD_imgSign = ?, FD_fechaRegistro = GETDATE()
              WHERE FD_noemp = ?";
    $params = [$filename, $ibm_usuario];
    $stmt = sqlsrv_prepare($conn, $query, $params);

    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Error en prepare: " . print_r(sqlsrv_errors(), true)]);
        return;
    }

    if (sqlsrv_execute($stmt)) {
        echo json_encode(["success" => true, "ruta" => $filename]);
    } else {
        echo json_encode(["success" => false, "error" => "Error en execute: " . print_r(sqlsrv_errors(), true)]);
    }
}


    function eliminarFirma() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $idFirmaDel = $_POST['id_usuario'];
        $query = "DELETE FROM TLX002MXDB.dbo.tblMXPRFirmasDigitales  WHERE FD_id = ?";
        $params = [$idFirmaDel];
        $result = sqlsrv_query($conn, $query, $params);
        echo json_encode(["success" => $result !== false]);
    }

}

if (isset($_GET["tblenc"])) {
    $FirmaDig = new FirmaDig();
    $FirmaDig->tblenc();
} else if (isset($_GET["guardarFirma"])) {
    $FirmaDig = new FirmaDig();
    $FirmaDig->guardarFirma();
} else if (isset($_GET["actualizarFirma"])) {
    $FirmaDig = new FirmaDig();
    $FirmaDig->actualizarFirma();
} else if (isset($_GET["eliminarFirma"])) {
    $FirmaDig = new FirmaDig();
    $FirmaDig->eliminarFirma();
}