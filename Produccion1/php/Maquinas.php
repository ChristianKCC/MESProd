<?php

use function PHPSTORM_META\map;

require_once "../../conexion.php";
require_once '../../Session/seguridad.php';


class Maquinas{

    //  INICIO FUNCIONES DE SECCIONES
    function tblDatosSecciones(){
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $busqueda = $_POST['busqueda'];
        empty($busqueda) ? $busqueda = '' : $busqueda = "WHERE idSeccion LIKE '%$busqueda' OR Seccion LIKE '%$busqueda'";
        $query = "SELECT tblps.idSeccion, tblps.Seccion
                  FROM tblProduccionSecciones tblps $busqueda";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while($row = sqlsrv_fetch_array($result)){
            array_push($array, [
                "ID"=> $row["idSeccion"],
                'NombreSeccion'=> $row['Seccion'],
            ]);
        }

        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function editseccionxid(){
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $id = $_GET["id"];

        $query = "SELECT * FROM tblProduccionSecciones WHERE idSeccion=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while($row = sqlsrv_fetch_array($result)){
            array_push($array, [
                'ID'=> $row['idSeccion'],
                'NombreSeccion'=> $row['Seccion'],
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }

    function saveNuevaSeccion(){
        $nombreSeccion = $_POST['nombreSeccion'];

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        
        $array = array(
           $nombreSeccion
        );
        $query = "INSERT INTO tblProduccionSecciones(Seccion) VALUES (?)";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
    }
 
    function updateSecciones() {
        $idSeccion = $_POST['idSeccion'];
        $nombreSeccion = $_POST['nombreSeccion'];

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");

        $array = array(
            $nombreSeccion,
            $idSeccion,
        );
        
        if($idSeccion === '' && $nombreSeccion === '') {
            http_response_code(201);
            die();
        }

        $query = "UPDATE tblProduccionSecciones SET Seccion = ? WHERE idSeccion=$idSeccion";
        $result  = sqlsrv_query($conn, $query, $array);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    //  FIN FUNCIONES DE SECCIONES

    //  INICIO FUNCIONES DE MODULOS

    function tblDatosModulos() {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $busqueda = $_POST['busqueda'];
        empty($busqueda) ? $busqueda = '' : $busqueda = "WHERE idModulos LIKE '%$busqueda' OR Modulos LIKE '%$busqueda'";
        $query = "SELECT tblpm.idModulos, tblpm.Modulos 
                  FROM tblProduccionModulos tblpm $busqueda";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while($row = sqlsrv_fetch_array($result)){
            array_push($array, [
                "ID"=> $row["idModulos"],
                'NombreModulo'=> $row['Modulos'],
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);   
    }

    function editmoduloxid() {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $id = $_GET["id"];

        $query= "SELECT * FROM tblProduccionModulos WHERE idModulos=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while($row = sqlsrv_fetch_array($result)){
            array_push($array, [
                "ID"=> $row["idModulos"],
                "NombreModulo"=> $row["Modulos"],
            ]);
        }

        echo json_encode($array);
        sqlsrv_close($conn);
    }

    function saveNuevoModulo() {
        $nombreModulo = $_POST["nombreModulo"];

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");

        $array = array(
            $nombreModulo,
        );
        $query = "INSERT INTO tblProduccionModulos(Modulos) VALUES (?)";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }

    }

    function updateModulo() {
        $idModulo = $_POST['idModulo'];
        $nombreModulo = $_POST['nombreModulo'];

        if ($idModulo === '' && $nombreModulo === '') {
            http_response_code(201);
            die();
        }

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");

        $query = "UPDATE tblProduccionModulos SET Modulos = ? WHERE idModulos = ?";
        $array = array($nombreModulo, $idModulo);

        $result = sqlsrv_query($conn, $query, $array);

        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true)); 
        } else {
            http_response_code(200);
        }
    }

    //  FIN FUNCIONES DE MODULOS

    //  INICIO DE FUNCIONES DE COMBINACIONES
    
    function tblDatosCombinaciones() {
    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX002MXDB");

    $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';
    $filtro = '';

    if (!empty($busqueda)) {
        $filtro = " WHERE tblMaquinas.NombreMaquina LIKE '%$busqueda%' 
                    OR tblProduccionSecciones.idSeccion LIKE '%$busqueda%' 
                    OR tblProduccionSecciones.Seccion LIKE '%$busqueda%'
                    OR tblProduccionModulos.idModulos LIKE '%$busqueda%'
                    OR tblProduccionModulos.Modulos LIKE '%$busqueda%'";
    }

    $query = "SELECT TOP 300 tblProduccionConSeccModFall.id AS idConb, 
                tblProduccionConSeccModFall.NoMaquina, tblMaquinas.NombreMaquina, 
                tblProduccionConSeccModFall.idSecciones, tblProduccionSecciones.Seccion,
                tblProduccionConSeccModFall.idModulos, tblProduccionModulos.Modulos, 
                tblProduccionConSeccModFall.idFallas, tblProduccionFallas.Fallas
              FROM tblProduccionConSeccModFall
              LEFT JOIN dbo.tblProduccionSecciones ON tblProduccionSecciones.idSeccion = tblProduccionConSeccModFall.idSecciones
              LEFT JOIN dbo.tblProduccionModulos ON tblProduccionModulos.idModulos = tblProduccionConSeccModFall.idModulos
              LEFT JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblProduccionConSeccModFall.NoMaquina
              LEFT JOIN dbo.tblProduccionFallas ON tblProduccionFallas.idFallas = tblProduccionConSeccModFall.idFallas
              $filtro
              ORDER BY tblProduccionConSeccModFall.id DESC";

    $result = sqlsrv_query($conn, $query);

    if ($result === false) {
        http_response_code(500);
        die("Error en la consulta: " . print_r(sqlsrv_errors(), true));
    }

    $array = array();
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($array, [
            'IDComb' => $row['idConb'],
            'NoMaquina' => $row['NoMaquina'],
            'NombMaquina' => $row['NombreMaquina'],
            'IDSecc' => $row['idSecciones'],
            'NombSeccion' => $row['Seccion'],
            'IDModulo' => $row['idModulos'],
            'NombModulo' => $row['Modulos'],
            'IDFallas' => $row['idFallas'],
            'NombFalla' => $row['Fallas'],
        ]);
    }

    echo json_encode($array);
    sqlsrv_close($conn);
    }

    function autoSecciones(){
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $busqueda = $_GET['q'];
        $query = "SELECT idSeccion, Seccion
                  FROM tblProduccionSecciones WHERE Seccion LIKE '%$busqueda%'";
        $result = sqlsrv_query($conn, $query);
        $datos = [];
        while($row = sqlsrv_fetch_array($result)){
            $datos [] = [
                'idVal' => $row['idSeccion'], 
                'Nombre' => $row['Seccion']];
        }

        echo json_encode(value: $datos);
        sqlsrv_close($conn);    
    }

    function autoModulos(){
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $busqueda = $_GET['q'];
        $query = "SELECT idModulos, Modulos
                  FROM tblProduccionModulos WHERE Modulos LIKE '%$busqueda%'";
        $result = sqlsrv_query($conn, $query);
        $datos = [];
        while($row = sqlsrv_fetch_array($result)){
            $datos [] = [
                'idVal' => $row['idModulos'], 
                'Nombre' => $row['Modulos']];
        }

        echo json_encode(value: $datos);
        sqlsrv_close($conn);    
    }

     function autoFallas(){
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $busqueda = $_GET['q'];
        $query = "SELECT idFallas, Fallas
                  FROM tblProduccionFallas WHERE Fallas LIKE '%$busqueda%'";
        $result = sqlsrv_query($conn, $query);
        $datos = [];
        while($row = sqlsrv_fetch_array($result)){
            $datos [] = [
                'idVal' => $row['idFallas'], 
                'Nombre' => $row['Fallas']];
        }

        echo json_encode(value: $datos);
        sqlsrv_close($conn);    
    }

    function saveCombinacion(){
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $idconvinacion = $_POST["idconvinacion"];
        $maquinaconv = $_POST["maquinaconv"];
        $seccionConb = $_POST["seccionConb"];
        $moduloConb = $_POST["moduloConb"];
        $fallaConb = $_POST["fallaConb"];

        if($maquinaconv === '' || $seccionConb === '' || $moduloConb === '' || $fallaConb === ''){
            http_response_code(201);
            die();
        }

        $queryval = "SELECT COUNT(*) FROM tblProduccionConSeccModFall WHERE NoMaquina = $maquinaconv AND idSecciones = '$seccionConb' AND idModulos = '$moduloConb' AND idFallas = '$fallaConb'";
        $stpm = sqlsrv_query($conn, $queryval);
        sqlsrv_fetch($stpm);
        $res2 = sqlsrv_get_field($stpm, 0);

        if ($res2 > 0) {
            http_response_code(202);
            die();
        }
        $idconvinacion == '' ?
            $query = "INSERT INTO tblProduccionConSeccModFall(NoMaquina,idSecciones,idModulos,idFallas) VALUES (?,?,?,?)" :
            $query = "UPDATE tblProduccionConSeccModFall SET NoMaquina = ?, idSecciones = ?, idModulos = ?, idFallas = ? WHERE id = $idconvinacion";
        $datos = array($maquinaconv, $seccionConb, $moduloConb, $fallaConb);
        $result = sqlsrv_query($conn, $query, $datos);
        $result === false ? http_response_code(500) : http_response_code(200);
    }

    function editCombinaciones(){
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $id = $_GET["id"];
        $query = "SELECT tblProduccionConSeccModFall.id AS idConb, 
                tblProduccionConSeccModFall.NoMaquina, tblMaquinas.NombreMaquina, 
                tblProduccionConSeccModFall.idSecciones, tblProduccionSecciones.Seccion,
                tblProduccionConSeccModFall.idModulos, tblProduccionModulos.Modulos, 
                tblProduccionConSeccModFall.idFallas, tblProduccionFallas.Fallas
                FROM tblProduccionConSeccModFall
                INNER JOIN dbo.tblProduccionSecciones ON tblProduccionSecciones.idSeccion = tblProduccionConSeccModFall.idSecciones
                INNER JOIN dbo.tblProduccionModulos ON tblProduccionModulos.idModulos = tblProduccionConSeccModFall.idModulos
                INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblProduccionConSeccModFall.NoMaquina
                INNER JOIN dbo.tblProduccionFallas ON tblProduccionFallas.idFallas = tblProduccionConSeccModFall.idFallas
                WHERE id = $id";

        $result = sqlsrv_query($conn, $query);
        $array = array();
        while($row = sqlsrv_fetch_array($result)){
            array_push($array, [
                'idConb' => $row['idConb'],
                'idMaquina' => $row['NoMaquina'],
                'NombMaquina' => $row['NombreMaquina'],
                'idSecc' => $row['idSecciones'],
                'NombSeccion' => $row['Seccion'],
                'idMod' => $row['idModulos'],
                'NombModulo' => $row['Modulos'],
                'idFalla' => $row['idFallas'],
                'NombFalla' => $row['Fallas'],
            ]);
        }

        echo json_encode(value: $array);
        sqlsrv_close($conn);
    }

    //  FIN FUNCIONES DE COMBINACIONES

    // INICIO FUNCIONES DE FALLAS

    function tblDatosFallas() {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $busqueda = $_POST['busqueda'];
        empty($busqueda) ? $busqueda = '' : $busqueda = "WHERE idFallas LIKE '%$busqueda' OR Fallas LIKE '%$busqueda'";
        $query = "SELECT TOP 200 tblpf.idFallas, tblpf.Fallas 
                  FROM tblProduccionFallas tblpf $busqueda";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while($row = sqlsrv_fetch_array($result)){
            array_push($array, [
                "ID"=> $row["idFallas"],
                'NombreFalla'=> $row['Fallas'],
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);   
    }

    function editFallaxid() {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $id = $_GET["id"];

        $query= "SELECT * FROM tblProduccionFallas WHERE idFallas=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while($row = sqlsrv_fetch_array($result)){
            array_push($array, [
                "ID"=> $row["idFallas"],
                "NombreFalla"=> $row["Fallas"],
            ]);
        }

        echo json_encode($array);
        sqlsrv_close($conn);
    }

    function saveNuevaFalla() {
        $nombreFalla = $_POST["nombreFalla"];

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");

        $array = array(
            $nombreFalla,
        );
        $query = "INSERT INTO tblProduccionFallas(Fallas) VALUES (?)";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }

    }

    function updateFalla() {
        $idFalla = $_POST['idFalla'];
        $nombreFalla = $_POST['nombreFalla'];

        if ($idFalla === '' && $nombreFalla === '') {
            http_response_code(201);
            die();
        }

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");

        $query = "UPDATE tblProduccionFallas SET Fallas = ? WHERE idFallas = ?";
        $array = array($nombreFalla, $idFalla);

        $result = sqlsrv_query($conn, $query, $array);

        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true)); 
        } else {
            http_response_code(200);
        }
    }
    
}

$Consultas = new Maquinas();
if (isset($_GET['tblDatosSecciones'])) {
    $Consultas->tblDatosSecciones();
} else if (isset($_GET['editseccionxid'])) {
    $Consultas->editseccionxid();
} else if(isset($_GET['saveNuevaSeccion'])){
     $Consultas->saveNuevaSeccion();
} else if(isset($_GET['updateSecciones'])){
     $Consultas->updateSecciones();
} else if(isset($_GET['tblDatosModulos'])){
     $Consultas->tblDatosModulos();
} else if (isset($_GET['editmoduloxid'])) {
    $Consultas->editmoduloxid();
} else if (isset($_GET['updateModulo'])) {
    $Consultas->updateModulo();
} else if (isset($_GET['saveNuevoModulo'])) {
    $Consultas->saveNuevoModulo();
} else if (isset($_GET['tblDatosCombinaciones'])) {
    $Consultas->tblDatosCombinaciones();
} else if (isset($_GET['autoSecciones'])) {
    $Consultas->autoSecciones();
} else if (isset($_GET['autoModulos'])) {
    $Consultas->autoModulos();
} else if (isset($_GET['autoFallas'])) {
    $Consultas->autoFallas();
} else if (isset($_GET['editCombinaciones'])) {
    $Consultas->editCombinaciones();
} else if (isset($_GET['saveCombinacion'])) {
    $Consultas->saveCombinacion();
} else if (isset($_GET['tblDatosFallas'])) {
    $Consultas->tblDatosFallas();
} else if (isset($_GET['editFallaxid'])) {
    $Consultas->editFallaxid();
} else if (isset($_GET['saveNuevaFalla'])) {
    $Consultas->saveNuevaFalla();
} else if (isset($_GET['updateFalla'])) {
    $Consultas->updateFalla();
}