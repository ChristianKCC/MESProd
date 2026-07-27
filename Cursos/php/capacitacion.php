<?php
require_once('../../conexion.php');
require_once('../../Components/tools.php');
require_once('../../Session/seguridad.php');
class Capacitaciones extends Herramientas
{
    function getDatatblCapacitaciones()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX035MXDB");
        $query = 'EXEC pa_P009_00512_02_LlenarDGVDetalleCapturaCapacitacion @NoEmp=' . $_SESSION["ibm"];
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'folio' => $row[0], 'inicio' => $row[1]->format("Y-m-d"), 'finalizo' => $row[2]->format("Y-m-d"), 'idcurso' => $row[3], 'curso' => $row[4],
                'noemp' => $row[5], 'instructor' => $row[6], 'comentarios' => $row[7], 'induccion' => $row[8], 'reinduccion' => $row[9]
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function getDatabyfolioCapacitacion()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $query = "EXEC pa_P009_00520_sdgvDetalleCaptura_CellDoubleClick '" . $folio . "' ";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'folio' => $row['IdEncabezadoCaptura'], 'inicio' => $row['FechaInicial']->format('Y-m-d'), 'finalizo' => $row['FechaFinal']->format('Y-m-d'),
                'curso' => $row['IdCurso'], 'instructor' => $row['NoEmpInstructor'], 'comentarios' => $row['Comentarios'], 'duracion' => $row['DuracionReal'],
                'induccion' => $row['Induccion'], 'reinduccion' => $row['Reinduccion']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function saveCapacitacion()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $inicio = $_POST['inicio'];
        $finalizo = $_POST['finalizo'];
        $curso = $_POST['curso'];
        $duracion = $_POST['duracion'];
        $instructor = $_POST['instructor'];
        $comentarios = $_POST['comentarios'];
        $induccion = $_POST['induccion'];
        $reinduccion = $_POST['reinduccion'];
        if ($folio === '') {
            $query = "EXEC pa_P009_00505_02_GuardarEncabezadoCapturaCapacitacion '0','" . $inicio . "','" . $finalizo . "',
        '" . $curso . "','" . $instructor . "','" . $comentarios . "','" . $induccion . "','" . $duracion . "','" . $_SESSION["ibm"] . "','" . $reinduccion . "'";
            sqlsrv_query($conn, $query);
            $query = sqlsrv_query($conn, "SELECT @@identity AS id");
            while ($row = sqlsrv_fetch_array($query))
                $id = trim($row[0]);
            echo json_encode($id);
        } else {
            $query = "EXEC pa_P009_00506_02_ModificarEncabezadoCapturaCapacitacion '" . $folio . "','" . $inicio . "',
            '" . $inicio . "','" . $curso . "','" . $instructor . "','" . $comentarios . "','" . $induccion . "','" . $duracion . "','" . $_SESSION["ibm"] . "','" . $reinduccion . "'";
            sqlsrv_query($conn, $query);
            echo json_encode($folio);
        }
        sqlsrv_close($conn);
    }
    function deleteCapacitacion()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $query = "EXEC pa_P009_00507_EliminarEncabezadoCaptura '" . $folio . "'";
        $result = sqlsrv_query($conn, $query);
        $result === 'false' ? http_response_code(500) :  http_response_code(200);
        sqlsrv_close($conn);
    }
    function getDatatblSubCapacitacion()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $query = "EXEC pa_P009_00509_01_LlenarDGVCapacitacionCapturaCapacitacion '" . $folio . "'";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while (sqlsrv_next_result($result)) {
            while ($row = sqlsrv_fetch_array($result)) {
                array_push($array, ['folio' => $row[0], 'noemp' => $row[1], 'nombre' => $row[2], 'calificacion' => $row[3], 'contesto' => $row[4]]);
            }
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function deleteSubCapacitacion()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $id = $_POST['id'];
        $query = "EXEC pa_P009_00511_EliminarSubEncabCaptura '" . $folio . "','" . $id . "'";
        $result = sqlsrv_query($conn, $query);
        $result === 'false' ? http_response_code(500) :  http_response_code(200);
        sqlsrv_close($conn);
    }
    function saveSubCapacitacion()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $noemp = $_POST['noemp'];
        $calificacion = $_POST['calificacion'];
        $query = "EXEC pa_P009_00508_GuardarSubEncabCapturaCapacitacion '0','" . $folio . "','" . $noemp . "','" . $calificacion . "'";
        $result = sqlsrv_query($conn, $query);
        $result === 'false' ? http_response_code(500) :  http_response_code(200);
        sqlsrv_close($conn);
    }
}
if (isset($_GET["getDatatblCapacitaciones"])) {
    $capacitaciones = new Capacitaciones();
    $capacitaciones->getDatatblCapacitaciones();
}
if (isset($_GET["getDatabyfolioCapacitacion"])) {
    $capacitaciones = new Capacitaciones();
    $capacitaciones->getDatabyfolioCapacitacion();
}
if (isset($_GET["saveCapacitacion"])) {
    $capacitaciones = new Capacitaciones();
    $capacitaciones->saveCapacitacion();
}
if (isset($_GET["deleteCapacitacion"])) {
    $capacitaciones = new Capacitaciones();
    $capacitaciones->deleteCapacitacion();
}
if (isset($_GET["getDatatblSubCapacitacion"])) {
    $capacitaciones = new Capacitaciones();
    $capacitaciones->getDatatblSubCapacitacion();
}
if (isset($_GET["deleteSubCapacitacion"])) {
    $capacitaciones = new Capacitaciones();
    $capacitaciones->deleteSubCapacitacion();
}

if (isset($_GET["saveSubCapacitacion"])) {
    $capacitaciones = new Capacitaciones();
    $capacitaciones->saveSubCapacitacion();
}

