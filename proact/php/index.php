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
}
else if (isset($_GET["Areas"])) {
    Proact::llenarslc("Select 
                            NoDepto,
                            NombreDepto 
                        from tblDepartamentos 
                        WHERE Filtro=1 
                        Order by NombreDepto", $conn);
}
sqlsrv_close($conn);

$conn = $conexion->conexion('TLX036MXDB');
if (isset($_GET["Turnos"])) {
    Proact::llenarslc("Select id,nombre from Trazabilidadturno", $conn);
}

$conn = $conexion->conexion('TLX003MXDB');

if (isset($_GET["getobservacionest1"])) {
    Proact::llenarslc("Select 
                        id,
                        nombre 
                    from tblObservaciones 
                    WHERE tipo=1 
                    ORDER BY nombre", $conn);
}   

else if (isset($_GET["proactComportamientos"])) {
    Proact::llenarslc("SELECT * FROM tblProactComportamiento", $conn);
}

else if (isset($_GET["Maquinas"])) {
    $areas = $_GET["areas"];
    Proact::llenarslc("SELECT tblProactAreas.Id,tblProactAreas.NombreArea from tblProactAreas 
    INNER JOIN tblProactAreasCombo ON tblProactAreasCombo.NoArea = tblProactAreas.Id WHERE tblProactAreas.AreaObsoleta=0 AND tblProactAreasCombo.NoDepto=$areas Order by tblProactAreas.NombreArea", $conn);
}

else if (isset($_GET["getobservacionest2"])) {
    Proact::llenarslc("Select 
                            id,
                            nombre 
                        from tblObservaciones 
                        WHERE tipo=2 
                        ORDER BY nombre", $conn);
}

else if (isset($_GET["guardar"])) {
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
        trim(isset($_POST["comportamiento"])) === '' ||
        trim($_POST["hora"]) === ''
    )
        echo json_encode("Vacios");
    else {
        $res = $_POST["deacuerdo"];
        $cumple = $_POST["cumple"];
        $comportamiento = $_POST["comportamiento"];
        $sql = "INSERT INTO tblProactEnc(observador,observado,departamento,maquina,fechainput,hora,comentarios,otrainteraccion,fecha,escalar,observacion,observacionreal,cumple,comportamiento) VALUES ('" . $_SESSION['ibm'] . "','" . $_POST["nombres"] . "','" . $_POST["areas"] . "',
    '" . $_POST["maquinas"] . "','" . $_POST["fecha"] . "','" . $_POST["hora"] . "','" . $_POST["comentarios"] . "','" . $_POST["otrainteraccion"] . "','" . date('Y/m/d') . "',$res," . $_POST["observacion"] . "
    ," . $_POST["observacionreal"] . "," . $cumple . "," . $comportamiento . "); SELECT @@IDENTITY;";
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
    $sql = "SELECT TOP 10 
    tblProactEnc.id,
    tblProactEnc.observador, 
    tblEmpleados.Nombre, 
    tblDepartamentos.NombreDepto,
    tblProactAreas.NombreArea,
    tblProactEnc.fechainput
        FROM tblProactEnc 
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblProactEnc.observado
        LEFT JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblProactEnc.departamento 
        INNER JOIN TLX003MXDB.dbo.tblProactAreas ON tblProactAreas.Id = tblProactEnc.maquina 
    WHERE tblProactEnc.observador = " . $_SESSION['ibm'] . "
    ORDER BY tblProactEnc.id DESC";
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

// Funcion para obtener colores aleatorios para graficas HX entre un 0 y un 0xFFFFFFF
function color_rand()
{
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

// // Validacion para uso de form
// function validarformreporte()
// {
//     $fechai = $_POST["fechai"];
//     $fechaf = $_POST["fechaf"];
//     $addquery = "WHERE (tblProactEnc.fecha >= '" . $fechai . "' 
//                 AND tblProactEnc.fecha < dateadd(day, 1, '" . $fechaf . "'))";
               
//     // Concatenacion de busqueda por maquina
//     if (isset($_POST["maquinas"])) {
//         $maquinas = $_POST["maquinas"];
//         $addquery .= " AND (";
//         foreach ($maquinas as $elementos)
//             $elementos == "" ? 
//             $addquery .= "" : 
//             $addquery .= "tblProactEnc.maquina = $elementos OR ";
//         $addquery = substr($addquery, 0, -4);
//         $addquery .= ")";
//     }
//     return $addquery;
// }

function validarformreporte() {
    $addquery = " WHERE 1=1 ";
    $params = [];

    if (!empty($_POST['fechai']) && !empty($_POST['fechaf'])) {
        $addquery .= " AND tblProactEnc.fechainput BETWEEN ? AND ? ";
        $params[] = $_POST['fechai'];
        $params[] = $_POST['fechaf'];
    }

    if (!empty($_POST['areas'])) {
        $addquery .= " AND tblProactEnc.departamento = ? ";
        $params[] = $_POST['areas'];
    }

    if (!empty($_POST['maquinas'])) {
        $addquery .= " AND tblProactEnc.maquina IN (" . implode(',', array_fill(0, count($_POST['maquinas']), '?')) . ")";
        foreach ($_POST['maquinas'] as $maq) {
            $params[] = $maq;
        }
    }

    // 🔎 Nuevo filtro por IBM observado
    if (!empty($_POST['observado'])) {
        $addquery .= " AND tblProactEnc.observado = ? ";
        $params[] = $_POST['observado'];
    }

    return [$addquery, $params];
}


// Consulta por fecha de maquina
if (isset($_GET["consultarxfechamaq"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];

    list($addquery, $params) = validarformreporte();

    $query = "Select tblMaquinas.NombreMaquina, COUNT(*) FROM tblProactEnc 
     INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina=tblProactEnc.maquina
     $addquery
     GROUP BY tblMaquinas.NombreMaquina";
    // $result = sqlsrv_query($conn, $query);
    
    $result = sqlsrv_query($conn, $query, $params);


    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
        array_push($datosVentas, $row[1]);
        array_push($colores, color_rand());
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    echo json_encode($respuesta);
}

// Busqueda de datos con las que el trabajador no cumple
if (isset($_GET["consultarxfechaobs"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];

    list($addquery, $params) = validarformreporte();
    
    $query = "Select tblObservaciones.nombre, COUNT(*) FROM tblProactEnc 
     INNER JOIN tblObservaciones on tblProactEnc.observacionreal=tblObservaciones.id
     $addquery AND tblObservaciones.tipo = 1 
     GROUP BY tblObservaciones.nombre";
    // $result = sqlsrv_query($conn, $query);

    $result = sqlsrv_query($conn, $query, $params);

    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
        array_push($datosVentas, $row[1]);
        array_push($colores, color_rand());
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    echo json_encode($respuesta);
}

// Busqueda de datos para total de personas observadas por maquina
if (isset($_GET["consultarxfechaobs2"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];

    list($addquery, $params) = validarformreporte();
    

    $query = "Select tblObservaciones.nombre, COUNT(*) FROM tblProactEnc 
     INNER JOIN tblObservaciones on tblProactEnc.observacionreal=tblObservaciones.id
     $addquery AND tblObservaciones.tipo = 2 
     GROUP BY tblObservaciones.nombre";
    
    $result = sqlsrv_query($conn, $query, $params);
    
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
        array_push($datosVentas, $row[1]);
        array_push($colores, color_rand());
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    echo json_encode($respuesta);
}

// Top 10 personas mas observadas
if (isset($_GET["consultarxfechatop5"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];
    
    // $query = "SELECT TOP 10 tblEmpleados.Nombre,COUNT(*) as contador FROM tblProactEnc 
    //  INNER JOIN TLX032MXDB.dbo.tblEmpleados on tblEmpleados.NoEmp = tblProactEnc.observado 
    //  $addquery GROUP BY tblEmpleados.Nombre ORDER BY contador DESC";
    // $result = sqlsrv_query($conn, $query);
    // while ($row = sqlsrv_fetch_array($result)) {
    //     array_push($etiquetas, $row[0]);
    //     array_push($datosVentas, $row[1]);
    //     array_push($colores, color_rand());
    // }
    // $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    // echo json_encode($respuesta);

    list($addquery, $params) = validarformreporte();
    
    $query = "SELECT TOP 10 tblEmpleados.NoEmp, tblEmpleados.Nombre, COUNT(*) as contador
          FROM tblProactEnc 
          INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblProactEnc.observado 
          $addquery 
          GROUP BY tblEmpleados.NoEmp, tblEmpleados.Nombre 
          ORDER BY contador DESC";

    
    $result = sqlsrv_query($conn, $query, $params);
    $etiquetas = [];
    $ids = [];
    $datosVentas = [];
    $colores = [];

    while ($row = sqlsrv_fetch_array($result)) {
        $ids[] = $row[0];          // NoEmp
        $etiquetas[] = $row[1];    // Nombre
        $datosVentas[] = $row[2];  // contador
        $colores[] = color_rand();
    }

    $respuesta = [
        "ids" => $ids,
        "etiquetas" => $etiquetas,
        "datos" => $datosVentas,
        "colores" => $colores
    ];
    echo json_encode($respuesta);

}

// Top observaciones por maquina
if (isset($_GET["consultarxfechalwsenc"])) {
    $addquery = validarformreporte();
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];

    list($addquery, $params) = validarformreporte();
    
    $query = "Select tblMaquinas.NombreMaquina, COUNT(*) FROM tblProactEnc 
    INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina=tblProactEnc.maquina
    $addquery 
    GROUP BY tblMaquinas.NombreMaquina";

    $result = sqlsrv_query($conn, $query, $params);
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
        array_push($datosVentas, $row[1]);
        array_push($colores, color_rand());
    }
    $respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
    echo json_encode($respuesta);
}

// Consulta data reporte
if (isset($_GET["consultardatosrep"])) {
    $addquery = validarformreporte();

    list($addquery, $params) = validarformreporte();
    
    $sql = "SELECT tblProactEnc.id,tblProactEnc.observador, tblEmpleados.Nombre, tblDepartamentos.NombreDepto,tblProactAreas.NombreArea,tblProactEnc.fechainput,tblProactEnc.hora, 
    tblProactEnc.comentarios, tblProactEnc.escalar,tblProactEnc.otrainteraccion,tblObservaciones.nombre as observacion,tblProactEnc.otrainteraccion,
    tblProactObsTipo.nombre as tipoobs, tblProactEnc.cumple
    FROM tblProactEnc INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp= tblProactEnc.observado
    INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto= tblProactEnc.departamento 
    INNER JOIN TLX003MXDB.dbo.tblProactAreas ON tblProactAreas.Id = tblProactEnc.maquina 
	INNER JOIN tblObservaciones ON tblObservaciones.id= tblProactEnc.observacionreal
	INNER JOIN tblProactObsTipo ON tblProactObsTipo.id= tblProactEnc.observacion
    $addquery";

    
    $stpm = sqlsrv_query($conn, $sql, $params);

    
    $datos = [];
    $i = 0;
    while ($row = sqlsrv_fetch_array($stpm)) {
        $datos[$i] = [
            "id" => $row[0], 
            "Observador" => $row[1], 
            "Observado" => $row[2], 
            "opcion" => $row[13], 
            "Area" => $row[3], 
            "Maquina" => $row[4], 
            "Fecha" => $row[5]->format("Y-m-d"),
            "Hora" => $row[6]->format("H:i:s"), 
            "Comentario" => $row[7], 
            "Deacuerdo" => $row[8], 
            "Otraint" => $row[9], 
            "Obsnombre" => $row[10], 
            "Otra" => $row[11], 
            "Observacion" => $row[12]
        ];
        $i++;
    }
    echo json_encode($datos);
}

//
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

// Busqueda de tipo de observaciones
if (isset($_GET["Observacion"])) {
    Proact::llenarslc("SELECT id,nombre FROM tblProactObsTipo Order by nombre", $conn);
}

// Busqueda de las observaciones segun el tipo en la consulta anterior
if (isset($_GET["Observacionreal"])) {
    $id = $_GET["id"];
    Proact::llenarslc("SELECT 
                            id,
                            nombre 
                        FROM tblObservaciones 
                        WHERE tipo=$id 
                        Order by nombre", 
                        $conn);
}

// Consulta de mis propios proactivos para desplegar
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
            "id" => $row[0], 
            "Observador" => $row[1], 
            "Observado" => $row[2], 
            "opcion" => $row[13], 
            "Area" => $row[3], 
            "Maquina" => $row[4], 
            "Fecha" => $row[5]->format("Y-m-d"),
            "Hora" => $row[6]->format("H:i:s"), 
            "Comentario" => $row[7], 
            "Deacuerdo" => $row[8], 
            "Otraint" => $row[9], 
            "Obsnombre" => $row[10], 
            "Otra" => $row[11], 
            "Observacion" => $row[12]
        ]);
    }
    echo json_encode($datos);
}

if (isset($_GET['detallepersona'])) {
    $persona = $_GET['persona'] ?? null;
    $fechai  = $_GET['fechai'] ?? null;
    $fechaf  = $_GET['fechaf'] ?? null;
    $maquina = $_GET['maquina'] ?? null;

    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX003MXDB");
    if ($conn === false) {
        echo json_encode([]);
        exit;
    }

    $query = "SELECT e.id,
                    e.observador,
                    e.observado,
                    d.NombreDepto AS departamento,
                    m.NombreArea AS maquina,
                    e.fechainput,
                    e.hora,
                    e.comentarios,
                    e.escalar,
                    e.otrainteraccion,
                    o.nombre AS observacion,
                    ot.nombre AS observacionreal,
                    e.cumple,
                    e.comportamiento
            FROM dbo.tblProactEnc e
            LEFT JOIN TLX009MXDB.dbo.tblDepartamentos d ON d.NoDepto = e.departamento
            LEFT JOIN TLX003MXDB.dbo.tblProactAreas m ON m.Id = e.maquina
            LEFT JOIN dbo.tblObservaciones o ON o.id = e.observacion
            LEFT JOIN dbo.tblProactObsTipo ot ON ot.id = e.observacionreal
            WHERE 1=1";

    $params = [];

    if (!empty($persona)) {
        $query .= " AND e.observado = ?";
        $params[] = $persona;
    }

    if (!empty($maquina)) {
        $query .= " AND e.maquina = ?";
        $params[] = $maquina;
    }

    if (!empty($fechai) && !empty($fechaf)) {
        $query .= " AND e.fechainput BETWEEN ? AND ?";
        $params[] = $fechai;
        $params[] = $fechaf;
    }

    $query .= " ORDER BY e.fechainput ASC";

    $result = sqlsrv_query($conn, $query, $params);
    if ($result === false) {
        echo json_encode([]);
        exit;
    }

    $data = [];
    while ($fila = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    foreach ($fila as $key => $value) {
        if ($value === null) {
            $fila[$key] = "Ninguna";
        }
    }
    if ($fila['fechainput'] instanceof DateTime) {
        $fila['fechainput'] = $fila['fechainput']->format("Y-m-d");
    }
    if ($fila['hora'] instanceof DateTime) {
        $fila['hora'] = $fila['hora']->format("H:i:s");
    }
    $data[] = $fila;
}


    echo json_encode($data);
    sqlsrv_close($conn);
    exit;
}

sqlsrv_close($conn);