<?php
require_once('../../conexion.php');
require_once('../../Components/tools.php');
require_once('../../Session/seguridad.php');
class cursos extends Herramientas
{
    function tblcursosall()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX035MXDB");
        $addfilter = '';
        !empty($_POST['libre']) ? $addfilter = "WHERE NombreCurso LIKE '%" . $_POST['libre'] . "%'" : '';
        $_POST['checkautoriza'] == 1 ? ($addfilter == '' ? $addfilter = "WHERE Autorizado=0" : $addfilter .= ' AND Autorizado=0') : '';
        $query = "SELECT tblCursos.IdCurso, tblCursos.NombreCurso, tblCursos.Duracion, [tblRIAreaTematica].DescAreaTematica, 
        [tblRIModalidadCapacitacion].DescModalidadCapacitacion, [tblRIObjetivoCapacitacion].DescObjetivoCapacitacion, tblCursos.DireccionCurso
        FROM tblCursos INNER JOIN
         [TLX009MXDB].[dbo].[tblRIAreaTematica] ON tblCursos.IdClvAreaTematica = [tblRIAreaTematica].IdClvAreaTematica INNER JOIN
         [TLX009MXDB].[dbo].[tblRIModalidadCapacitacion] ON tblCursos.IdClvModCapacitacion = [tblRIModalidadCapacitacion].IdClvModCapacitacion INNER JOIN
         [TLX009MXDB].[dbo].[tblRIObjetivoCapacitacion] ON tblCursos.IdClvObjCapacitacion = [tblRIObjetivoCapacitacion].IdClvObjCapacitacion $addfilter
		ORDER BY IdCurso";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row["IdCurso"],
                "nombre" => $row["NombreCurso"],
                "duracion" => $row["Duracion"],
                "areatem" => $row["DescAreaTematica"],
                "modalidad" => $row["DescModalidadCapacitacion"],
                "objetivo" => $row["DescObjetivoCapacitacion"],
                "direccion" => $row["DireccionCurso"]
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function tblcursosxautorizar()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX035MXDB");
        $query = "EXEC pa_P009_01501LlenarDGVAutorizacionCursos";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row["ID"],
                "nombre" => $row["NOMBRE"],
                "duracion" => $row["DURACION"],
                "areatem" => $row["AREA TEMATICA"],
                "modalidad" => $row["MODALIDAD"],
                "objetivo" => $row["OBJETIVO"],
                "direccion" => $row["DIRECCION"]
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
     function consultarcurso()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $id = $_POST['idcurso'];
        $consulta = "EXEC Select_cursoallpuro $id";
        $ejecutar = sqlsrv_query($conn, $consulta);
        while ($fila = sqlsrv_fetch_array($ejecutar)) {
            $datos[0] = array(
                "idcurso" => $fila['IdCurso'],
                "nombre" => $fila['NombreCurso'],
                "duracion" => $fila['Duracion'],
                "idareatematica" => $fila['IdClvAreaTematica'],
                "idmodcapacitacion" => $fila['IdClvModCapacitacion'],
                "idobjcapacitacion" => $fila['IdClvObjCapacitacion'],
                "direcccioncurso" => $fila['DireccionCurso'],
                "clasificacion" => $fila['clasificacion'],
                "clasificacionCurso" => $fila['clasificacionCurso'],
            );
        }
        echo json_encode($datos);
        sqlsrv_close($conn);
    }
    function getInstructoresxadd()
    {
        $folio = $_POST['folio'];
        cursos::llnarslc("EXEC pa_P009_00402LlenarDGVInstructores '" . $folio . "'", "TLX035MXDB");
    }
    function getInstructorespile()
    {
        $folio = $_POST['folio'];
        cursos::llnarslc("EXEC pa_P009_00404LlenarDGVInstructoresXCurso '" . $folio . "'", "TLX035MXDB");
    }

    function getPuestosxadd()
    {
        $folio = $_POST['folio'];
        cursos::llnarslc("EXEC pa_P009_00112LlenarlstPuestoSeleccionado '" . $folio . "'", "TLX035MXDB");
    }
    function getPuestospile()
    {
        $folio = $_POST['folio'];
        cursos::llnarslc("EXEC pa_P009_00110LlenarlstPuesto '0','" . $folio . "'", "TLX035MXDB");
    }
    function addorremoveregistre($query)
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $result = sqlsrv_query($conn, $query);
        echo $result === false ? json_encode("error") : json_encode("done");
        sqlsrv_close($conn);
    }
    function saveDataCurso()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $nombre = $_POST['nombre'];
        $DescAreaTematica = $_POST['areatem'];
        $ModalidadCapacitacion = $_POST['modalidad'];
        $ObjetivoCapacitacion = $_POST['objetivo'];
        $duracion = $_POST['duracion'];
        $direccion = $_POST['direccion'];
        $clasificacion = $_POST['clasificacion'];
        $clasificacionCurso = $_POST['clasificacionCurso'];
        $id = '';
        if ($folio === '') {
            $query = "EXEC pa_P009_00105_GuardarCursos '0','" . $nombre . "','" . $duracion . "','" . $DescAreaTematica . "','" . $ModalidadCapacitacion . "','" . $ObjetivoCapacitacion . "','" . $direccion . "','" . $clasificacion . "','" . $_SESSION["ibm"] . "','" . $clasificacionCurso . "'";
            sqlsrv_query($conn, $query);
            $query = sqlsrv_query($conn, "SELECT @@identity AS id");
            if ($row = sqlsrv_fetch_array($query)) {
                $id = trim($row[0]);
            }
            echo json_encode($id);
        } else {
            $query = "EXEC pa_P009_00107_ModificarCursos '" . $folio . "','" . $nombre . "','" . $duracion . "','" . $DescAreaTematica . "','" . $ModalidadCapacitacion . "','" . $ObjetivoCapacitacion . "','" . $direccion . "','" . $clasificacion . "','" . $_SESSION["ibm"] . "'";
            sqlsrv_query($conn, $query);
            echo json_encode('done');
        }
        sqlsrv_close($conn);
    }
    function deleteCurso()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $query = "EXEC pa_P009_00108_EliminarCursos '" . $folio . "'";
        $result = sqlsrv_query($conn, $query);
        echo $result === false ? json_encode('error') : json_encode('done');
        sqlsrv_close($conn);
    }
    function autorizacurso()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $query = "EXEC pa_P009_01502_AutorizaCursos '" . $folio . "'," . $_SESSION["ibm"] . "";
        $result = sqlsrv_query($conn, $query);
        echo $result === false ? json_encode('error') : json_encode('done');
        sqlsrv_close($conn);
    }
    function savePregunta()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];  //folio del curso
        $pregunta = $_POST['pregunta'];
        $respuesta1 = $_POST['respuesta1'];
        $respuesta2 = $_POST['respuesta2'];
        $respuesta3 = $_POST['respuesta3'];
        $respuestac = $_POST['respuestac'];
        $pregunta = trim($pregunta);
        $pregunta = htmlspecialchars($pregunta);
        $pregunta = stripslashes($pregunta);
        $query = "INSERT INTO tblCursosPregunta (id_capacitacion,pregunta,r1,r2,r3,correcta,obsoleta,noemp) VALUES ('$folio','$pregunta','$respuesta1','$respuesta2','$respuesta3','$respuestac','0','" . $_SESSION['ibm'] . "')";
        $result = sqlsrv_query($conn, $query);
        echo $result === false ? json_encode('error') : json_encode('done');
        sqlsrv_close($conn);
    }
    function getDataPreguntas()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $query = "SELECT tblCursosPregunta.*,tblCursos.NombreCurso from tblCursosPregunta INNER JOIN tblCursos on tblCursos.IdCurso=tblCursosPregunta.id_capacitacion where id_capacitacion=$folio AND obsoleta=0";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "folio" => $row["id"],
                "pregunta" => $row["pregunta"],
                "respuesta1" => $row["r1"],
                "respuesta2" => $row["r2"],
                "respuesta3" => $row["r3"],
                "respuestac" => $row["correcta"]
            ]);
        }
        echo $result === false ? json_encode('error') : json_encode($array);
        sqlsrv_close($conn);
    }
    function deletePregunta()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $id = $_POST['id'];
        $sql = "UPDATE tblCursosPregunta SET obsoleta = 1 WHERE id = $id";
        $result = sqlsrv_query($conn, $sql);
        $result === false ? http_response_code(500) : http_response_code(200);
        sqlsrv_close($conn);
    }
    function uploadFile()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX035MXDB");
        $folio = $_POST['folio'];
        $file = $_FILES['filec'];
        $ruta = "../Archivos/" . $file['name'];
        move_uploaded_file($file['tmp_name'], $ruta);
        $sql = "INSERT INTO tblCursosarchivo (idcap,Ruta) VALUES ( ? , ? )";
        $params = array($folio, $ruta);
        $result = sqlsrv_query($conn, $sql, $params);
        $result === false ? http_response_code(500) : http_response_code(200);
        sqlsrv_close($conn);
    }
}
if (isset($_GET["tblcursosall"])) {
    $cursos = new Cursos();
    $cursos->tblcursosall();
}
if (isset($_GET["tblcursosxautorizar"])) {
    $cursos = new Cursos();
    $cursos->tblcursosxautorizar();
}
if (isset($_GET["consultarcurso"])) {
    $cursos = new Cursos();
    $cursos->consultarcurso();
}
if (isset($_GET['getInstructoresxadd'])) {
    $cursos = new Cursos();
    $cursos->getInstructoresxadd();
}
if (isset($_GET['getInstructorespile'])) {
    $cursos = new Cursos();
    $cursos->getInstructorespile();
}
if (isset($_GET['getPuestosxadd'])) {
    $cursos = new Cursos();
    $cursos->getPuestosxadd();
}
if (isset($_GET['getPuestospile'])) {
    $cursos = new Cursos();
    $cursos->getPuestospile();
}
if (isset($_GET['addInstructor'])) {
    $cursos = new Cursos();
    $folio = $_POST['folio'];
    $id = $_POST['id'];
    $cursos->addorremoveregistre("pa_P009_00403_GuardarInstructoresXCurso '" . $folio . "','" . $id . "'");
}
if (isset($_GET['removeInstructor'])) {
    $cursos = new Cursos();
    $folio = $_POST['folio'];
    $id = $_POST['id'];
    $cursos->addorremoveregistre("pa_P009_00405_EliminarInstructorXCurso '" . $id . "','" . $folio . "'");
}
if (isset($_GET['addPuestos'])) {
    $cursos = new Cursos();
    $folio = $_POST['folio'];
    $id = $_POST['id'];
    $cursos->addorremoveregistre("EXEC pa_P009_00111_GuardarFiltrosCursoxPuesto '" . $folio . "','" . $id . "'");
}
if (isset($_GET['removePuestos'])) {
    $cursos = new Cursos();
    $folio = $_POST['folio'];
    $id = $_POST['id'];
    $cursos->addorremoveregistre("EXEC pa_P009_00113EliminaCursosXPuesto '" . $folio . "','" . $id . "'");
}
if (isset($_GET['saveDataCurso'])) {
    $cursos = new Cursos();
    $cursos->saveDataCurso();
}
if (isset($_GET['deleteCurso'])) {
    $cursos = new Cursos();
    $cursos->deleteCurso();
}
if (isset($_GET['autorizacurso'])) {
    $cursos = new Cursos();
    $cursos->autorizacurso();
}
if (isset($_GET['savePregunta'])) {
    $cursos = new Cursos();
    $cursos->savePregunta();
}
if (isset($_GET['getDataPreguntas'])) {
    $cursos = new Cursos();
    $cursos->getDataPreguntas();
}
if (isset($_GET['uploadFile'])) {
    $cursos = new Cursos();
    $cursos->uploadFile();
}
if (isset($_GET['deletePregunta'])) {
    $cursos = new Cursos();
    $cursos->deletePregunta();
}
