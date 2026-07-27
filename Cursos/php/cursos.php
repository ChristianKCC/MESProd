<?php
require_once('../../conexion.php');
require_once('../../Components/tools.php');
require_once('../../Session/seguridad.php');
class MisCursos
{
    function getDatatblMisCursos()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $query = "SELECT tblCursos.IdCurso,tblCursos.NombreCurso,tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura, tblSubEncabCapturaCapacitacion.IdSubEncabCaptura from tblSubEncabCapturaCapacitacion
         INNER JOIN tblEncabezadoCapturaCapacitacion on tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura INNER JOIN tblCursos on tblCursos.IdCurso=tblEncabezadoCapturaCapacitacion.IdCurso 
         WHERE (tblSubEncabCapturaCapacitacion.NoEmp=" . $_SESSION['ibm'] . " AND tblSubEncabCapturaCapacitacion.Contestado=0)";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "folio" => $row["IdCurso"], "curso" => $row["NombreCurso"], "idenc" => $row["IdEncabezadoCaptura"], "idsubenc" => $row["IdSubEncabCaptura"]
            ]);
        }
        echo $result === false ? json_encode('error') : json_encode($array);
        sqlsrv_close($conn);
    }
    function getFileMisCursos()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $folio = $_POST["folio"];
        $query = "SELECT * FROM tblCursosarchivo WHERE idcap=$folio";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "ruta" => $row[2]
            ]);
        }
        echo $result === false ? json_encode('error') : json_encode($array);
        sqlsrv_close($conn);
    }
    function getQuestions()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $folio = $_POST["folio"];
        $query = 'SELECT * FROM tblCursosPregunta WHERE id_capacitacion=' . $folio . '';
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row['id'], "folio" => $row['id_capacitacion'], "pregunta" => $row['pregunta'], "r1" => $row['r1'], "r2" => $row['r2'], "r3" => $row['r3'], "rc" => $row['correcta']
            ]);
        }
        echo $result === false ? json_encode('error') : json_encode($array);
        sqlsrv_close($conn);
    }
    function getResCorrect()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $folio = $_POST["folio"];
        $query = 'SELECT id,correcta FROM tblCursosPregunta WHERE id_capacitacion=' . $folio . '';
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result))
            array_push($array, [
                "id" => $row['id'], "respuesta" => $row['correcta']
            ]);
        echo $result === false ? json_encode('error') : json_encode($array);
        sqlsrv_close($conn);
    }
    function saveCalificacion()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $idcap = $_POST["idcap"];
        $calificacion = $_POST["calificacion"];
        $query = "UPDATE tblSubEncabCapturaCapacitacion SET Calificacion=" . $calificacion . ",Contestado=1 WHERE IdSubEncabCaptura=$idcap";
        $result = sqlsrv_query($conn, $query);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function SaveExamen(){
        $data = json_decode(file_get_contents('php://input'), true);
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        foreach ($data as $valor) {
            $valor1 = $valor[0];
            $valor2 = $valor[1];
            $valor3 = $valor[2];
            $query = "INSERT INTO tblSubEncabCapturaCapacitacionExamen (idsubcapacitacion,pregunta,respuesta) VALUES (?, ?, ?)";
            $result = sqlsrv_query($conn, $query,array($valor1,$valor2,$valor3));
            $result === false ? http_response_code(500) : http_response_code(200);
        }
        
    }
}
if (isset($_GET['getDatatblMisCursos'])) {
    $miscursos = new MisCursos();
    $miscursos->getDatatblMisCursos();
}
else if (isset($_GET['getFileMisCursos'])) {
    $miscursos = new MisCursos();
    $miscursos->getFileMisCursos();
}
else if (isset($_GET['getQuestions'])) {
    $miscursos = new MisCursos();
    $miscursos->getQuestions();
}
else if (isset($_GET['getResCorrect'])) {
    $miscursos = new MisCursos();
    $miscursos->getResCorrect();
}
else if (isset($_GET['saveCalificacion'])) {
    $miscursos = new MisCursos();
    $miscursos->saveCalificacion();
}
else if (isset($_GET['SaveExamen'])) {
    $miscursos = new MisCursos();
    $miscursos->SaveExamen();
}
