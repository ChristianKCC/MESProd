<?php
require_once "../../conexion.php";
require_once "../../Session/seguridad.php";
class Proact
{
    public static function llenarslc($query, $conn)
    {
        $res = sqlsrv_query($conn, $query);
        $datos = [];
        $i = 0;
        while ($row = sqlsrv_fetch_array($res)) {
            $datos[$i] = ["id" => $row[0], "nombre" => $row[1]];
            $i++;
        }
        echo json_encode($datos);
    }
    public static function guardarinteracciones($folio, $array, $tipo)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX003MXDB');
        foreach ($array as $check) {
            $sql = "INSERT INTO tblProactobservacionesadd (idfolio, idobservacion, opcion) VALUES ('" . $folio . "','" . $check . "','" . $tipo . "')";
            $result = sqlsrv_query($conn, $sql);
            if ($result = false) echo json_encode("Errorsql");
        }
        sqlsrv_close($conn);
    }
}
$conexion = new ClassConexion();
$conn = $conexion->conexion('TLX032MXDB');
if (isset($_GET["Usuarios"])) {
    Proact::llenarslc("Select NoEmp,Nombre from TLX032MXDB.dbo.tblEmpleados Order by nombre", $conn);
}
sqlsrv_close($conn);

$conn = $conexion->conexion('TLX009MXDB');
if (isset($_GET["Puestos"])) {
    Proact::llenarslc("Select id,nombre from tblPuestos Order by nombre", $conn);
} else if (isset($_GET["Areas"])) {
    Proact::llenarslc("Select NoDepto,NombreDepto from tblDepartamentos WHERE Filtro=1 Order by NombreDepto", $conn);
}
sqlsrv_close($conn);

$conn = $conexion->conexion('TLX036MXDB');
if (isset($_GET["Turnos"])) {
    Proact::llenarslc("Select id,nombre from Trazabilidadturno", $conn);
}

$conn = $conexion->conexion('TLX003MXDB');
if (isset($_GET["getobservacionest1"])) {
    Proact::llenarslc("Select id,nombre from tblObservaciones WHERE tipo=1 ORDER BY nombre", $conn);
} else if (isset($_GET["Maquinas"])) {
    $areas = $_GET["areas"];
    Proact::llenarslc("SELECT tblProactAreas.Id,tblProactAreas.NombreArea from tblProactAreas 
    INNER JOIN tblProactAreasCombo ON tblProactAreasCombo.NoArea = tblProactAreas.Id WHERE tblProactAreas.AreaObsoleta=0 AND tblProactAreasCombo.NoDepto=$areas Order by tblProactAreas.NombreArea", $conn);
} else if (isset($_GET["getobservacionest2"])) {
    Proact::llenarslc("Select id,nombre from tblObservaciones WHERE tipo=2 ORDER BY nombre", $conn);
} else if (isset($_GET["guardar"])) {
    if (
        !$_POST ||
        trim($_POST["observado"]) === '' ||
        trim(isset($_POST["nombres"])) === '' ||
        trim($_POST["areas"]) === '' ||
        trim($_POST["maquinas"]) === '' ||
        trim($_POST["fecha"]) === '' ||
        trim($_POST["observacion"]) === '' ||
        trim($_POST["observacionreal"]) === '' ||
        trim(isset($_POST["deacuerdo"])) === '' ||
        trim(isset($_POST["cumple"])) === '' ||
        trim($_POST["hora"]) === ''
    )
        echo json_encode("Vacios");
    else {
        $res = $_POST["deacuerdo"];
        $cumple = $_POST["cumple"];
        $sql = "INSERT INTO tblCalidadProact(observador,observado,departamento,maquina,fechainput,hora,comentarios,otrainteraccion,fecha,escalar,observacion,observacionreal,cumple) VALUES ('" . $_SESSION['ibm'] . "','" . $_POST["nombres"] . "','" . $_POST["areas"] . "',
    '" . $_POST["maquinas"] . "','" . $_POST["fecha"] . "','" . $_POST["hora"] . "','" . $_POST["comentarios"] . "','" . $_POST["otrainteraccion"] . "','" . date('Y/m/d') . "',$res," . $_POST["observacion"] . "
    ," . $_POST["observacionreal"] . "," . $cumple . "); SELECT @@IDENTITY;";
        $stpm = sqlsrv_query($conn, $sql);
        if ($stpm === false)
            echo json_encode("Errorsql");
        else {
            sqlsrv_next_result($stpm);
            sqlsrv_fetch($stpm);
            $folioget = sqlsrv_get_field($stpm, 0);
            echo json_encode($folioget);
        }
    }
} else if (isset($_GET["tablaproact"])) {
    $sql = "SELECT TOP 5 tblCalidadProact.id,tblCalidadProact.observador, tblEmpleados.Nombre, tblDepartamentos.NombreDepto,tblProactAreas.NombreArea,tblCalidadProact.fechainput
    FROM tblCalidadProact LEFT JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp= tblCalidadProact.observado
    LEFT JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto= tblCalidadProact.departamento 
    INNER JOIN TLX003MXDB.dbo.tblProactAreas ON tblProactAreas.Id = tblCalidadProact.maquina WHERE tblCalidadProact.observador=" . $_SESSION['ibm'] . "  ORDER BY tblCalidadProact.id DESC";
    $stpm = sqlsrv_query($conn, $sql);
    $datos = [];
    $i = 0;
    while ($row = sqlsrv_fetch_array($stpm)) {
        $datos[$i] = ["id" => $row[0], "Observador" => $row[1], "Observado" => $row[2], "Area" => $row[3], "Maquina" => $row[4], "Fecha" => $row[5]->format("Y-m-d")];
        $i++;
    }
    echo json_encode($datos);
}
if (isset($_GET["consultardatos"])) {
    $folio = $_GET["folio"];
    $sql = "SELECT * FROM tblProactEnc WHERE id=$folio";
    $stpm = sqlsrv_query($conn, $sql);
    $datos = array();
    while ($row = sqlsrv_fetch_array($stpm)) {
        array_push($datos, [
            "id" => $row[0], "Observador" => $row[1], "Observado" => $row[2], "Area" => $row[3], "Maquina" => $row[4], "Fecha" => $row[5]->format("Y-m-d"),
            "Hora" => $row[6]->format("H:i:s"), "Comentario" => $row[7], "Deacuerdo" => $row[9], "Otrainteraccion" => $row[10]
        ]);
    }
    echo json_encode($datos);
}

function color_rand()
{
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}
function validarformreporte()
{
    $fechai = $_POST["fechai"];
    $fechaf = $_POST["fechaf"];
    $addquery = "WHERE (tblProactEnc.fecha >= '" . $fechai . "' AND tblProactEnc.fecha < dateadd(day, 1, '" . $fechaf . "'))";
    if (isset($_POST["maquinas"])) {
        $maquinas = $_POST["maquinas"];
        $addquery .= " AND (";
        foreach ($maquinas as $elementos)
            $elementos == "" ? $addquery .= "" : $addquery .= "tblProactEnc.maquina = $elementos OR ";
        $addquery = substr($addquery, 0, -4);
        $addquery .= ")";
    }
    return $addquery;
}
if (isset($_GET["consultarxfechamaq"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];
    $query = "Select tblMaquinas.NombreMaquina, COUNT(*) FROM tblProactEnc 
     INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina=tblProactEnc.maquina
     $addquery
     GROUP BY tblMaquinas.NombreMaquina";
    $result = sqlsrv_query($conn, $query);
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
        array_push($datosVentas, $row[1]);
        array_push($colores, color_rand());
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    echo json_encode($respuesta);
}
if (isset($_GET["consultarxfechaobs"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];
    $query = "Select tblObservaciones.nombre, COUNT(*) FROM tblProactEnc 
     INNER JOIN tblObservaciones on tblProactEnc.observacionreal=tblObservaciones.id
     $addquery AND tblObservaciones.tipo = 1 
     GROUP BY tblObservaciones.nombre";
    $result = sqlsrv_query($conn, $query);
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
        array_push($datosVentas, $row[1]);
        array_push($colores, color_rand());
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    echo json_encode($respuesta);
}
if (isset($_GET["consultarxfechaobs2"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];
    $query = "Select tblObservaciones.nombre, COUNT(*) FROM tblProactEnc 
     INNER JOIN tblObservaciones on tblProactEnc.observacionreal=tblObservaciones.id
     $addquery AND tblObservaciones.tipo = 2 
     GROUP BY tblObservaciones.nombre";
    $result = sqlsrv_query($conn, $query);
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
        array_push($datosVentas, $row[1]);
        array_push($colores, color_rand());
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    echo json_encode($respuesta);
}
if (isset($_GET["consultarxfechatop5"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];
    $query = "SELECT TOP 10 tblEmpleados.Nombre,COUNT(*) as contador FROM tblProactEnc 
     INNER JOIN TLX032MXDB.dbo.tblEmpleados on tblEmpleados.NoEmp = tblProactEnc.observado 
     $addquery GROUP BY tblEmpleados.Nombre ORDER BY contador DESC";
    $result = sqlsrv_query($conn, $query);
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
        array_push($datosVentas, $row[1]);
        array_push($colores, color_rand());
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    echo json_encode($respuesta);
}
if (isset($_GET["consultarxfechalwsenc"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];
    $query = "Select tblMaquinas.NombreMaquina, COUNT(*) FROM tblProactEnc 
     INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina=tblProactEnc.maquina
     $addquery 
     GROUP BY tblMaquinas.NombreMaquina";
    $result = sqlsrv_query($conn, $query);
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
        array_push($datosVentas, $row[1]);
        array_push($colores, color_rand());
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    echo json_encode($respuesta);
}
if (isset($_GET["consultardatosrep"])) {
    $addquery = validarformreporte();
    $sql = "SELECT tblProactEnc.id,tblProactEnc.observador, tblEmpleados.Nombre, tblDepartamentos.NombreDepto,tblProactAreas.NombreArea,tblProactEnc.fechainput,tblProactEnc.hora, 
    tblProactEnc.comentarios, tblProactEnc.escalar,tblProactEnc.otrainteraccion,tblObservaciones.nombre as observacion,tblProactEnc.otrainteraccion,
    tblProactObsTipo.nombre as tipoobs, tblProactEnc.cumple
    FROM tblProactEnc INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp= tblProactEnc.observado
    INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto= tblProactEnc.departamento 
    INNER JOIN TLX003MXDB.dbo.tblProactAreas ON tblProactAreas.Id = tblProactEnc.maquina 
	INNER JOIN tblObservaciones ON tblObservaciones.id= tblProactEnc.observacionreal
	INNER JOIN tblProactObsTipo ON tblProactObsTipo.id= tblProactEnc.observacion
    $addquery";
    $stpm = sqlsrv_query($conn, $sql);
    $datos = [];
    $i = 0;
    while ($row = sqlsrv_fetch_array($stpm)) {
        $datos[$i] = [
            "id" => $row[0], "Observador" => $row[1], "Observado" => $row[2], "opcion" => $row[13], "Area" => $row[3], "Maquina" => $row[4], "Fecha" => $row[5]->format("Y-m-d"),
            "Hora" => $row[6]->format("H:i:s"), "Comentario" => $row[7], "Deacuerdo" => $row[8], "Otraint" => $row[9], "Obsnombre" => $row[10], "Otra" => $row[11], "Observacion" => $row[12]
        ];
        $i++;
    }
    echo json_encode($datos);
}
if (isset($_GET["reporteavancemeta"])) {
    $fechai = $_POST["fechai"];
    $fechaf = $_POST["fechaf"];
    $areas = $_POST["areas"];
    $tipo = $_POST["tipo"];
    $avance = $_POST["avance"];
    $itxsem = 0;
    $fechaif = date_create($fechai);
    $fechaff = date_create($fechaf);
    $difernciafechas = date_diff($fechaif, $fechaff)->format("%a");
    $difernciafechas = $difernciafechas + 1;
    $addquery = "";
    $andwhere = "";
    $areas != '' ? $addquery .= " WHERE tblEmpleados.NombreDepartamento='" . $areas . "'" : $addquery = "";
    $areas != '' ? $andwhere = "AND" : $andwhere = "WHERE";
    $tipo != '' ? $addquery .= $andwhere . "  tblLWSTipoempleado.tipo='" . $tipo . "'" : $addquery .= "";
    $etiquetas = [];
    $metas = [];
    $colores = $colores2 = [];
    $datosvalreal = [];
    $pass = 0;
    $query = "Select tblEmpleados.NoEmp,tblEmpleados.Nombre,(SELECT COUNT(*) FROM tblProactEnc WHERE tblProactEnc.observador=tblEmpleados.NoEmp AND 
     (tblProactEnc.fecha >= '" . $fechai . "' AND tblProactEnc.fecha < dateadd(day, 1, '" . $fechaf . "'))),
     tblProactobjetivos.num from tblProactobjetivos  
     LEFT JOIN tblProactEnc ON tblProactEnc.observador =tblProactobjetivos.noemp
     LEFT JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp= tblProactobjetivos.noemp
	 LEFT JOIN tblLWSTipoempleado on tblLWSTipoempleado.ibm= tblProactobjetivos.noemp $addquery
     GROUP BY tblEmpleados.Nombre,tblEmpleados.NoEmp,tblProactobjetivos.num ORDER BY Nombre";
    $result = sqlsrv_query($conn, $query);
    while ($row = sqlsrv_fetch_array($result)) {
        $itxsem = (7 / $row[3]);
        $pass = (($row[2] * 100) / ($difernciafechas / $itxsem));
        if ($pass <= $avance || empty($avance)) {
            $itxsem = ($difernciafechas / $itxsem) - $row[2];
            array_push($etiquetas, $row[1]);
            array_push($metas, $itxsem);
            array_push($datosvalreal, $row[2]);
            array_push($colores, $itxsem < 0 ? "rgba(0, 115, 255, .6)" : "rgba(255, 0, 0, .6)");
            array_push($colores2, "rgba(12, 255, 0, .6)");
        }
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $metas, "numreal" => $datosvalreal, "colores" => $colores, "colores2" => $colores2];
    echo json_encode($respuesta);
}
sqlsrv_close($conn);

$conn = $conexion->conexion('TLX003MXDB');
if (isset($_GET["Observacion"])) {
    Proact::llenarslc("SELECT TOP 1 id,nombre FROM tblProactObsTipo Order by nombre", $conn);
}
if (isset($_GET["Observacionreal"])) {
    Proact::llenarslc("SELECT id ,nombre FROM tblCalidadProactObs", $conn);
}
if (isset($_GET["misproact"])) {
    $sql = "SELECT TOP 50 tblProactEnc.id,tblProactEnc.observador, tblEmpleados.Nombre, tblDepartamentos.NombreDepto,tblProactAreas.NombreArea,tblProactEnc.fechainput,tblProactEnc.hora, 
        tblProactEnc.comentarios, tblProactEnc.escalar,tblProactEnc.otrainteraccion,tblObservaciones.nombre as observacion,tblProactEnc.otrainteraccion,
        tblProactObsTipo.nombre as tipoobs, tblProactEnc.cumple
        FROM tblProactEnc INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp= tblProactEnc.observado
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto= tblProactEnc.departamento 
        INNER JOIN TLX003MXDB.dbo.tblProactAreas ON tblProactAreas.Id = tblProactEnc.maquina 
        INNER JOIN tblObservaciones ON tblObservaciones.id= tblProactEnc.observacionreal
        INNER JOIN tblProactObsTipo ON tblProactObsTipo.id= tblProactEnc.observacion WHERE tblProactEnc.observado = " . $_SESSION['ibm'];
    $stpm = sqlsrv_query($conn, $sql);
    $datos = array();
    while ($row = sqlsrv_fetch_array($stpm)) {
        array_push($datos,[
            "id" => $row[0], "Observador" => $row[1], "Observado" => $row[2], "opcion" => $row[13], "Area" => $row[3], "Maquina" => $row[4], "Fecha" => $row[5]->format("Y-m-d"),
            "Hora" => $row[6]->format("H:i:s"), "Comentario" => $row[7], "Deacuerdo" => $row[8], "Otraint" => $row[9], "Obsnombre" => $row[10], "Otra" => $row[11], "Observacion" => $row[12]
        ]);
    }
    echo json_encode($datos);
}
sqlsrv_close($conn);
