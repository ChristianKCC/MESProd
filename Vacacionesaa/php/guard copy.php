<?php
require_once(__DIR__ . "/../../Session/seguridad.php");
require_once(__DIR__ . "/../../conexion.php");

class VerificarSesionVac {
    // Verifica que exista sesión activa
    function requiere_sesion() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['ibm'])) {
            header("Location: /Mes/KCMes/index/index.php");
            exit;
        }
    }

    // Verifica si el usuario es un supervisor/gerente autorizado para ver solicitudes de vacaciones
    function esEnSupervisores() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $ibm = $_SESSION["ibm"];

        // Casos especiales (superusuarios)
        $ibmPerNom = "51947";
        $ibmPerRoot = "58998";
        $ibmPerRoot2 = "22622";
        $ibmPerRoot3 = "53224";
        $ibmPerRoot4 = "55268";

        if ($ibm == $ibmPerNom || $ibm == $ibmPerRoot || $ibm == $ibmPerRoot2 || $ibm == $ibmPerRoot3 || $ibm == $ibmPerRoot4) {
            return;
        }

        // Buscar si el IBM está registrado como autorizador en las solicitudes de vacaciones
        $stmt = "SELECT Vc_autoriza 
                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc 
                 WHERE Vc_autoriza = ?";
        $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);

        if ($resStmt === false) {
            echo json_encode(["error" => sqlsrv_errors()]);
            exit;
        }

        $rowStmt = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);

        if (!$rowStmt || $rowStmt['Vc_autoriza'] != $ibm) {
            header("Location: /Mes/KCMes/index/index.php");
            exit;
        }
    }

    // Verifica si el IBM es un Super Intendente
    function esEnSupIntendente() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $ibm = $_SESSION["ibm"];

        // Casos especiales (superusuarios)
        $ibmPerNom = "51947";
        $ibmPerRoot = "58998";
        $ibmPerRoot2 = "22622";

        if ($ibm == $ibmPerNom || $ibm == $ibmPerRoot || $ibm == $ibmPerRoot2) {
            return;
        }

        // Buscar si el IBM está registrado como autorizador en las solicitudes de vacaciones
        $stmt = "SELECT Vc_noempSupIntendente 
                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc 
                 WHERE Vc_noempSupIntendente = ?";
        $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);

        if ($resStmt === false) {
            echo json_encode(["error" => sqlsrv_errors()]);
            exit;
        }

        $rowStmt = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);

        if (!$rowStmt || $rowStmt['Vc_noempSupIntendente'] != $ibm) {
            header("Location: /Mes/KCMes/index/index.php");
            exit;
        }
    }

    // Verifica si el usuario puede ver solicitudes de vacaciones pendientes
    function puedeVerVacaciones() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $ibm = $_SESSION["ibm"];

        $ibmMaestros = ['58998', '51947', '22622']; 

        if (in_array($ibm, $ibmMaestros)) {
            return true;
        }

        $stmt = "SELECT COUNT(*) AS total 
                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc 
                 WHERE Vc_autoriza = ?";
        $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);
        if ($resStmt === false) {
            return false;
        }
        $rowStmt = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);

        return ($rowStmt && $rowStmt['total'] > 0);
    }

    // Verifica si el usuario puede ver solicitudes como Super Intendente
    function puedeVerVacacionesSupInt() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $ibm = $_SESSION["ibm"];

        $ibmMaestros = ['58998', '51947', '22622']; 

        if (in_array($ibm, $ibmMaestros)) {
            return true;
        }

        $stmt = "SELECT COUNT(*) AS total 
                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc 
                 WHERE Vc_noempSupIntendente = ?";
        $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);
        if ($resStmt === false) {
            return false;
        }
        $rowStmt = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);

        return ($rowStmt && $rowStmt['total'] > 0);
    }

    // Contar solicitudes de vacaciones pendientes
    // Opcion para gerente
    function contarVacacionesPendientes() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $ibm = $_SESSION["ibm"];

        if ($ibm == '58998' || $ibm == '51947' || '22622') {
            $stmt = "SELECT COUNT(*) AS total FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc WHERE Vc_terminado != 1 OR Vc_terminado IS NULL";
            $resStmt = sqlsrv_query($conn, $stmt);
        } else {
            $stmt = "SELECT COUNT(*) AS total 
                     FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc WHERE Vc_autoriza = ? AND (Vc_terminado != 1 OR Vc_terminado IS NULL)";
            $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);
        }

        if ($resStmt === false) {
            return 0;
        }
        $row = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    }

    // Contador para superintendentes
    function contarVacacionesPendientesSupInt() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $ibm = $_SESSION["ibm"];

        // Superusuarios: ven todos los pendientes de superintendentes
        if ($ibm == '58998' || $ibm == '51947' || $ibm == '22622') {
            $stmt = "SELECT COUNT(*) AS total 
                    FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc 
                    WHERE Vc_noempSupIntendente IS NOT NULL 
                    AND (Vc_autSupIn IS NULL) 
                    AND (Vc_terminado != 1 OR Vc_terminado IS NULL)";
            $resStmt = sqlsrv_query($conn, $stmt);
        } else {
            // Superintendente normal: solo los que le corresponden
            $stmt = "SELECT COUNT(*) AS total 
                    FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc 
                    WHERE Vc_noempSupIntendente = ? 
                    AND (Vc_autSupIn IS NULL) 
                    AND (Vc_autSupIn != 1 OR Vc_autSupIn IS NULL)";
            $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);
        }

        if ($resStmt === false) {
            return 0;
        }
        $row = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);
        return $row ? intval($row['total']) : 0;
    }


    // Contabilizacion para el numero de correcciones
    function contarCorrecciones() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $ibm = $_SESSION["ibm"];
        
        $stmt = "SELECT COUNT(*) AS total 
                    FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc 
                    WHERE Vc_revisado = 0";
        $resStmt = sqlsrv_query($conn, $stmt);        

        if ($resStmt === false) {
            return 0;
        }
        $row = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);
        return $row ? intval($row['total']) : 0;
    }

    function contarPorFirma(){
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $ibm = $_SESSION["ibm"];

        $stmt = "SELECT COUNT(*) AS total FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc WHERE Vc_firmaRI IS NULL";
        $resStmt = sqlsrv_query($conn, $stmt);        

        if ($resStmt === false) {
            return 0;
        }
        $row = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);
        return $row ? intval($row['total']) : 0;

    }

}
?>