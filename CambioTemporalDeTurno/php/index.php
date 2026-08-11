<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";
require_once(__DIR__ . "/../../BDNominas/config.php"); // Carga de datos para datos de GERENTE

class CambioTemporalTurno
{

    // function guardarCambioTurno(){
    //     $ClassConexion = new ClassConexion();
    //     $conn = $ClassConexion->conexion("TLX002MXDB");
    //     $folio = $_GET["folio"];

    //     // Usar datos del folio del registro ddel tiempo extra y usarlo para identificar los nuevos datos
    //     // $folioTE = $_POST['folioCambioTemporalTurno'];
    //     $folioTE = (!empty($_POST['folioCambioTemporalTurno'])) ? $_POST['folioTiempoExtra'] : null;
    //     $fecha = $_POST['fecha_emision'];
    //     $depto = $_POST['Depto_m'];        
    //     $a = $_POST['nombre_receptor'];
    //     $de = $_POST['de_area'];        
    //     //$tripulacion = $_POST['tripulacion'];
    //     $tripulacion = null;
    //     $horario = $_POST['horario_texto'];
    //     $rol = $_POST['rol'];
    //     $aPartirDel = $_POST['fecha_inicio'];
    //     $hastaEl = $_POST['hasta_el'];
    //     $horaPresentacion = $_POST['hora_presentacion'];
    //     $turnoPresentacion = $_POST['turno_presentacion'];
    //     $horarioDe = $_POST['horario_desde'];
    //     $horarioA = $_POST['horario_hasta'];
    //     $hastaTripulacion = $_POST['hasta_tripulacion'];
    //     $descansos = $_POST['descansos'];
    //     $diaAdd = $_POST['dias_adicionales'];
    //     $horarioAdd = $_POST['horario_adicional'];
    //     $pdfDir = "";
    //     $estado = 0;

    //     $sql = "INSERT INTO tblMXPRCambioTurnoTemporal(
    //             Ctt_folio, Ctt_fol_TE, Ctt_fecha, Ctt_depto, Ctt_a, Ctt_de, 
    //             Ctt_tripulacion, Ctt_horario, Ctt_rol,
    //             Ctt_aPartirDel, Ctt_hastaEl, Ctt_horaPresentacion, 
    //             Ctt_turnoPresentacion, Ctt_tripulacionDe, Ctt_horarioDe, 
    //             Ctt_horarioA, Ctt_descansos, Ctt_diaAdd, Ctt_horarioAdd, 
    //             Ctt_PDFDir, Ctt_estado)
    //             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    //     $params = array($folio, $folioTE, $fecha, $depto, $a, $de, $tripulacion, $horario, $rol, $aPartirDel, $hastaEl, $horaPresentacion, $turnoPresentacion, 
    //     $hastaTripulacion, $horarioDe, $horarioA, $descansos, $diaAdd, $horarioAdd, $pdfDir, $estado);

    //     $stmt = sqlsrv_query($conn, $sql, $params);

    //     if($stmt){
    //         echo json_encode(["success"=>true, "message"=>"Guardado en BD"]);
    //     } else {
    //         echo json_encode(["success"=>false, "message"=>sqlsrv_errors()]);
    //     }
    //     exit;
    // }


    function guardarCambioTurno() {
        $ClassConexion = new ClassConexion();
        $conn  = $ClassConexion->conexion("TLX002MXDB");
        $folio = $_GET["folio"] ?? "";

        $folioTE = (!empty($_POST['folioTiempoExtra'])) ? $_POST['folioTiempoExtra'] : null;

        $fecha             = $_POST['fecha_emision'];
        $depto             = $_POST['Depto_m'];
        $a                 = $_POST['nombre_receptor'];
        $de                = $_POST['de_area'];
        $tripulacion       = null;
        $horario           = $_POST['horario_texto'];
        $rol               = $_POST['rol'];
        $aPartirDel        = $_POST['fecha_inicio'];
        $hastaEl           = $_POST['hasta_el'];
        $horaPresentacion  = $_POST['hora_presentacion'];
        $turnoPresentacion = $_POST['turno_presentacion'];
        $horarioDe         = $_POST['horario_desde'];
        $horarioA          = $_POST['horario_hasta'];
        $hastaTripulacion  = $_POST['hasta_tripulacion'];
        $descansos         = $_POST['descansos'];
        $diaAdd            = $_POST['dias_adicionales'];
        $horarioAdd        = $_POST['horario_adicional'];
        $pdfDir            = "";
        $estado            = 0;

        // ── NUEVOS: IBM del empleado e IBM del supervisor en sesión ───────────────
        $ibmEmpleado = !empty($_POST['ibm_empleado']) ? intval($_POST['ibm_empleado']) : null;
        $ibmAutoriza = $_SESSION['ibm'] ?? null;   // siempre del servidor, nunca del cliente

        $sql = "INSERT INTO tblMXPRCambioTurnoTemporal (
                    Ctt_folio, Ctt_fol_TE, Ctt_fecha, Ctt_depto, Ctt_a, Ctt_de,
                    Ctt_tripulacion, Ctt_horario, Ctt_rol,
                    Ctt_aPartirDel, Ctt_hastaEl, Ctt_horaPresentacion,
                    Ctt_turnoPresentacion, Ctt_tripulacionDe, Ctt_horarioDe,
                    Ctt_horarioA, Ctt_descansos, Ctt_diaAdd, Ctt_horarioAdd,
                    Ctt_PDFDir, Ctt_estado,
                    Ctt_ibmEmpleado, Ctt_ibmAutoriza
                ) VALUES (
                    ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
                )";

        $params = [
            $folio, $folioTE, $fecha, $depto, $a, $de,
            $tripulacion, $horario, $rol,
            $aPartirDel, $hastaEl, $horaPresentacion,
            $turnoPresentacion, $hastaTripulacion, $horarioDe,
            $horarioA, $descansos, $diaAdd, $horarioAdd,
            $pdfDir, $estado,
            $ibmEmpleado, $ibmAutoriza 
        ];

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            echo json_encode(["success" => true, "message" => "Guardado en BD"]);
        } else {
            echo json_encode(["success" => false, "message" => sqlsrv_errors()]);
        }
        exit;
    }

    function guardarCambiosTurnoIndependientes(){
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $ibmSesion = $_SESSION["ibm"] ?? null;

        $sql = "SELECT
                    Ctt_id,
                    CONVERT(varchar, Ctt_fecha, 23)       AS Ctt_fecha,
                    Ctt_a,
                    Ctt_depto,
                    Ctt_de,
                    Ctt_horario,
                    CONVERT(varchar, Ctt_aPartirDel, 23)  AS Ctt_aPartirDel,
                    CONVERT(varchar, Ctt_hastaEl, 23)     AS Ctt_hastaEl,
                    Ctt_turnoPresentacion,
                    Ctt_horarioDe,
                    Ctt_horarioA,
                    Ctt_descansos,
                    Ctt_diaAdd,
                    Ctt_horarioAdd,
                    Ctt_estado
                FROM tblMXPRCambioTurnoTemporal
                WHERE (Ctt_fol_TE IS NULL OR Ctt_fol_TE = '')
                AND Ctt_folio IN (
                    SELECT id FROM tblMXPRCabEncabezado WHERE ibm = ?
                )
                ORDER BY Ctt_id DESC";

        // Filtramos por los folios del supervisor en sesión para no mostrar los de otros
        $params = [$ibmSesion];
        $stmt   = sqlsrv_query($conn, $sql, $params);

        $array = [];
        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $array[] = $row;
            }
        }

        echo json_encode($array);
        exit;
    }

    function eliminarCambioTurno(){
       $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $id   = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(["success" => false, "error" => "ID no proporcionado"]);
            exit;
        }

        $sql    = "DELETE FROM [TLX002MXDB].[dbo].[tblMXPRCambioTurnoTemporal] WHERE Ctt_id = ?";
        $params = [$id];
        $stmt   = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
        }
        exit;
    }

    function tblCambiosTurnoIndependientes(){
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $ibmSesion = $_SESSION['ibm'] ?? null;

        // Lista de IBM con acceso total
        $admins = ['58998','51947','55268','53224','60040'];

        // Si el IBM en sesión es admin, ve todos los registros
        if (in_array($ibmSesion, $admins)) {
            $sql = "SELECT
                        Ctt_id,
                        Ctt_folio,
                        Ctt_fol_TE,
                        CONVERT(varchar, Ctt_fecha, 23)       AS Ctt_fecha,
                        Ctt_depto,
                        Ctt_a,
                        Ctt_de,
                        Ctt_tripulacion,
                        Ctt_horario,
                        Ctt_rol,
                        CONVERT(varchar, Ctt_aPartirDel, 23)  AS Ctt_aPartirDel,
                        CONVERT(varchar, Ctt_hastaEl, 23)     AS Ctt_hastaEl,
                        CONVERT(varchar, Ctt_horaPresentacion, 8) AS Ctt_horaPresentacion,
                        Ctt_turnoPresentacion,
                        Ctt_tripulacionDe,
                        CONVERT(varchar, Ctt_horarioDe, 8)    AS Ctt_horarioDe,
                        CONVERT(varchar, Ctt_horarioA, 8)     AS Ctt_horarioA,
                        Ctt_descansos,
                        Ctt_diaAdd,
                        Ctt_horarioAdd,
                        Ctt_PDFDir,
                        Ctt_estado,
                        Ctt_ibmEmpleado,
                        Ctt_ibmAutoriza
                    FROM [TLX002MXDB].[dbo].[tblMXPRCambioTurnoTemporal]
                    ORDER BY Ctt_fecha DESC";
            $params = [];
        } else {
            // Si no es admin, solo ve los registros que él creó
            $sql = "SELECT
                        Ctt_id,
                        Ctt_folio,
                        Ctt_fol_TE,
                        CONVERT(varchar, Ctt_fecha, 23)       AS Ctt_fecha,
                        Ctt_depto,
                        Ctt_a,
                        Ctt_de,
                        Ctt_tripulacion,
                        Ctt_horario,
                        Ctt_rol,
                        CONVERT(varchar, Ctt_aPartirDel, 23)  AS Ctt_aPartirDel,
                        CONVERT(varchar, Ctt_hastaEl, 23)     AS Ctt_hastaEl,
                        CONVERT(varchar, Ctt_horaPresentacion, 8) AS Ctt_horaPresentacion,
                        Ctt_turnoPresentacion,
                        Ctt_tripulacionDe,
                        CONVERT(varchar, Ctt_horarioDe, 8)    AS Ctt_horarioDe,
                        CONVERT(varchar, Ctt_horarioA, 8)     AS Ctt_horarioA,
                        Ctt_descansos,
                        Ctt_diaAdd,
                        Ctt_horarioAdd,
                        Ctt_PDFDir,
                        Ctt_estado,
                        Ctt_ibmEmpleado,
                        Ctt_ibmAutoriza
                    FROM [TLX002MXDB].[dbo].[tblMXPRCambioTurnoTemporal]
                    WHERE Ctt_ibmAutoriza = ?
                    ORDER BY Ctt_fecha DESC";
            $params = [$ibmSesion];
        }

        $stmt  = sqlsrv_query($conn, $sql, $params);
        $array = [];

        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $array[] = $row;
            }
        }

        echo json_encode($array);
        exit;
    }

}

if(isset($_GET['guardarCambioTurno'])){
    $CambioTemporalTurno = new CambioTemporalTurno();
    $CambioTemporalTurno->guardarCambioTurno();
} else if (isset($_GET['tblCambiosTurnoIndependientes'])) {
    $CambioTemporalTurno = new CambioTemporalTurno();
    $CambioTemporalTurno->tblCambiosTurnoIndependientes();
} else if (isset($_GET['eliminarCambioTurno'])) {
    $CambioTemporalTurno = new CambioTemporalTurno();
    $CambioTemporalTurno->eliminarCambioTurno();
}
?>
