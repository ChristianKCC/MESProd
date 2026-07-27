<?php
require_once(__DIR__ . "/../../Session/seguridad.php");
require_once(__DIR__ . "/../../conexion.php");

class VerificarSesion{
    // Funcion para verificar que los gerentes o personas a autorizar los tiempos extra sean los unicos con permisos a entrar y ver la autorizacion de datos
    // Busqueda de funciones para en caso de que los IBM de la sesion coincidan con los datos
    function requiere_sesion(){
        if (session_status () === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['ibm'])){
            header("Location: /Mes/KCMes/index/index.php");
            exit;
        }
    }

    // Funcion para buscar a los supervisores en la tabla de TiempoextraEnc
    // Busca en el campo de noempautoriza puesto que al abrir una solicitud el supervisor su JefeInm(Gerente) es el que debe de autorizar estos cambios
    function esEnSupervisores()
    {
        // Instancia a la base de datos
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION["ibm"];

        // Manejo de casos especiales para usuarios
        $ibmPerNom = "51947";
        $ibmPerRoot = "60040";
        $ibmPerRoot2 = "53224";
        $ibmPerRoot3 = "55268";

        // Si el ibm que esta inciando sesion es el que se tiene como permitido entonces puede acceder sin problema
        if ($ibm == $ibmPerNom || $ibm == $ibmPerRoot || $ibm == $ibmPerRoot2 || $ibm == $ibmPerRoot3) {
            return;
        }

        // Manejo de casos en los que el ibm sea de un gerente segun condiciones pero si exista en la lista
        // Busqueda del IBM dentro de la tabla, para ello se compara el IBM que esta en la sesion con los que estan registrados
        $stmt = "SELECT noempautoriza 
                FROM TLX003MXDB.dbo.TiempoextraEnc 
                WHERE noempautoriza = ?";
        $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);

        if ($resStmt === false) {
            echo json_encode(["error" => sqlsrv_errors()]);
            exit;
        }
        // Ejecucion de la query para la obtencion de resultados
        $rowStmt = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);

        // Si no hay valores de la consulta o este no coincide se redirige al index sin cerrar la sesion
        if (!$rowStmt || $rowStmt['noempautoriza'] != $ibm) {
            header("Location: /Mes/KCMes/index/index.php");
            exit;
        }
    }

    function esEnSupervisoresValidar(): bool
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION["ibm"];

        // IBM con acceso total
        $ibmPermitidos = ["51947", "60040", "53224", "55268"];
        if (in_array($ibm, $ibmPermitidos)) {
            return true;
        }

        // Buscar si el IBM está registrado como supervisor
        $stmt = "SELECT supervisor 
                FROM TLX003MXDB.dbo.TiempoextraEnc 
                WHERE supervisor = ?";
        $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);

        if ($resStmt === false) {
            error_log("Error SQL: " . print_r(sqlsrv_errors(), true));
            return false;
        }

        $rowStmt = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);

        return ($rowStmt && $rowStmt['supervisor'] == $ibm);
    }

    function esEnSuperIntendentes()
    {
        // Instancia a la base de datos
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION["ibm"];

        // Manejo de casos especiales para usuarios
        $ibmPerNom = "51947";
        $ibmPerRoot = "60040";

        // Si el ibm que esta inciando sesion es el que se tiene como permitido entonces puede acceder sin problema
        if ($ibm == $ibmPerNom || $ibm == $ibmPerRoot) {
            return;
        }

        // Manejo de casos en los que el ibm sea de un gerente segun condiciones pero si exista en la lista
        // Busqueda del IBM dentro de la tabla, para ello se compara el IBM que esta en la sesion con los que estan registrados
        $stmt = "SELECT noempSupIntendente 
                FROM TLX003MXDB.dbo.TiempoextraEnc 
                WHERE noempSupIntendente = ?";
        $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);

        if ($resStmt === false) {
            echo json_encode(["error" => sqlsrv_errors()]);
            exit;
        }
        // Ejecucion de la query para la obtencion de resultados
        $rowStmt = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);

        // Si no hay valores de la consulta o este no coincide se redirige al index sin cerrar la sesion
        if (!$rowStmt || $rowStmt['noempSupIntendente'] != $ibm) {
            header("Location: /Mes/KCMes/index/index.php");
            exit;
        }
    }

    // Verifica si el usuario tiene permisos para ver los tiempos extra pendientes, esto se hace para mostrar el numero de tiempos extra pendientes en el index
    function puedeVerTiemposExtra() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION["ibm"];

        // Lista de IBM autorizados a ver el contenido de gerente
        $ibmMaestros = ['60040', '51947']; 

        // Validar si el ibm de la sesion es un superusuario
        if (in_array($ibm, $ibmMaestros)) {
            return true;
        }

        // Validar si el ibm se la sesion esta en la lista de autorizados a ver contenido de gerente
        $stmt = "SELECT COUNT(*) AS total 
                 FROM TLX003MXDB.dbo.TiempoextraEnc 
                 WHERE noempautoriza = ?";
        $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);
        if ($resStmt === false) {
            return false;
        }
        $rowStmt = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);

        return ($rowStmt && $rowStmt['total'] > 0);
    }

    function puedeVerTiemposExtraSupInt() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION["ibm"];

        // Lista de IBM autorizados a ver el contenido de gerente
        $ibmMaestros = ['60040', '51947']; 

        // Validar si el ibm de la sesion es un superusuario
        if (in_array($ibm, $ibmMaestros)) {
            return true;
        }

        // Validar si el ibm se la sesion esta en la lista de autorizados a ver contenido de gerente
        $stmt = "SELECT COUNT(*) AS total 
                 FROM TLX003MXDB.dbo.TiempoextraEnc 
                 WHERE noempSupIntendente = ?";
        $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);
        if ($resStmt === false) {
            return false;
        }
        $rowStmt = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);

        return ($rowStmt && $rowStmt['total'] > 0);
    }

    // Si el usuario es un superusuario, contar todos los tiempos extra pendientes, 
    // de lo contrario contar solo los tiempos extra pendientes que el usuario tiene que autorizar 
    // Contabilizaciones para gerente
    function contarTiemposExtra() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION["ibm"];

        // Superusuarios (gerentes globales)
        if ($ibm == '60040' || $ibm == '51947') {
            $stmt = "SELECT COUNT(*) AS total FROM TLX003MXDB.dbo.TiempoextraEnc WHERE terminado IS NULL";
            $resStmt = sqlsrv_query($conn, $stmt);
        } else {
            // Gerente normal: solo los que debe autorizar él
            $stmt = "SELECT COUNT(*) AS total FROM TLX003MXDB.dbo.TiempoextraEnc WHERE noempautoriza = ? AND terminado IS NULL";
            $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);
        }

        if ($resStmt === false) return 0;
        $row = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);
        return $row ? intval($row['total']) : 0;
    }

    // Contabilizaciones para superintendente
    function contarTiemposExtraSupInt() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION["ibm"];

        // Superusuarios: ven todos los pendientes de superintendentes
        if ($ibm == '60040' || $ibm == '51947') {
            $stmt = "SELECT COUNT(*) AS total FROM TLX003MXDB.dbo.TiempoextraEnc WHERE noempSupIntendente IS NOT NULL AND autorizaSupInt IS NULL";
            $resStmt = sqlsrv_query($conn, $stmt);
        } else {
            // Superintendente normal: solo los que le corresponden
            $stmt = "SELECT COUNT(*) AS total FROM TLX003MXDB.dbo.TiempoextraEnc WHERE noempSupIntendente = ? AND autorizaSupInt IS NULL";
            $resStmt = sqlsrv_query($conn, $stmt, [$ibm]);
        }

        if ($resStmt === false) return 0;
        $row = sqlsrv_fetch_array($resStmt, SQLSRV_FETCH_ASSOC);
        return $row ? intval($row['total']) : 0;
    }
}
?>