<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";
class IMC
{
    function getDataCondiciones()
    {
        $conection = new ClassConexion();
        $conn = $conection->conexion('TLX002MXDB');
        $query = 'SELECT * FROM tblIMCConInse';
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ["id" => $row[0], 'nombre' => $row[1]]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function saveIMC()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $fecha = $_POST['fecha'];
        $emisor = $_POST['emisor'];
        $departamento = $_POST['departamento'];
        $area = $_POST['area'];
        $detriesgo = $_POST['detriesgo'];
        $tiporiesgo = $_POST['tiporiesgo'];
        $tipo = $_POST['tipo'];
        $descripcion = $_POST['descripcion'];
        $responsable = $_POST['responsable'];
        $sugerencias = $_POST['sugerencias'];
        $fechacompromiso = $_POST['fechacompromiso'];
        $estado = $_POST['estado'];
        $condicionins = $_POST['condicion'];
        $query = "INSERT INTO tblIMCEnc(fecha,emisor,departamento,area,detriesgo,tiporiesgo,tipo,descripcion,responsable,sugerencias,fechacompromiso,estado,
        condicionins,sessionuser) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $result = sqlsrv_query($conn, $query, (array(
            $fecha, $emisor, $departamento, $area, $detriesgo, $tiporiesgo,
            $tipo, $descripcion, $responsable, $sugerencias, $fechacompromiso, $estado, $condicionins, $_SESSION['ibm']
        )));
        $result === false ? http_response_code(500) : http_response_code(200);

        sqlsrv_close($conn);
    }

    function tblIMCEnc()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $query = "SELECT TOP 20 tblIMCEnc.id,tblIMCEnc.fecha,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblProactAreas.NombreArea,
        tblIMCEnc.descripcion,tblIMCEnc.fechacompromiso FROM tblIMCEnc
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIMCEnc.emisor
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblIMCEnc.departamento
        INNER JOIN TLX003MXDB.dbo.tblProactAreas ON tblProactAreas.Id = tblIMCEnc.area
        INNER JOIN tblIMCDetecRisgo ON tblIMCDetecRisgo.id = tblIMCEnc.detriesgo
        INNER JOIN tblIMCTipRisgo ON tblIMCTipRisgo.id = tblIMCEnc.tiporiesgo
        INNER JOIN tblIMCTipo ON tblIMCTipo.id = tblIMCEnc.tipo WHERE tblIMCEnc.sessionuser= (?) ORDER BY id Desc";
        $result = sqlsrv_query($conn, $query, (array($_SESSION['ibm'])));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row["id"], "fecha" => $row["fecha"]->format("Y-m-d"), "Nombre" => $row["Nombre"], "NombreDepto" => $row["NombreDepto"],
                "NombreArea" => $row["NombreArea"], "descripcion" => $row["descripcion"], "fechacompromiso" => $row["fechacompromiso"]->format("Y-m-d")
            ]);
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
        sqlsrv_close($conn);
    }
    function updateEstadoIMC()
    {
        if ($_SESSION["permisoIMC"] != 1) {
            http_response_code(502);
            die();
        }
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $id = $_POST['id'];
        $emisor = $_POST['emisor'];
        $departamento = $_POST['departamento'];
        $area = $_POST['area'];
        $detriesgo = $_POST['detriesgo'];
        $tiporiesgo = $_POST['tiporiesgo'];
        $tipo = $_POST['tipo'];
        $descripcion = $_POST['descripcion'];
        $responsable = $_POST['responsable'];
        $sugerencias = $_POST['sugerencias'];
        $fechacompromiso = $_POST['fechacompromiso'];
        $estado = $_POST['estado'];
        $query = "UPDATE tblIMCEnc SET emisor = ?,departamento = ?,area = ?,detriesgo = ?,tiporiesgo = ?,tipo = ?,descripcion = ?,responsable = ?,
        sugerencias = ?,fechacompromiso = ?,estado = ? WHERE id=?";
        $result = sqlsrv_query($conn, $query, (array(
            $emisor, $departamento, $area, $detriesgo, $tiporiesgo,
            $tipo, $descripcion, $responsable, $sugerencias, $fechacompromiso, $estado, $id
        )));
        $result === false ? http_response_code(500) : http_response_code(200);

        sqlsrv_close($conn);
    }
    function tblReporteIMC()
    {
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $departamento = $_POST["departamento"];
        $area = $_POST["area"];
        $noemp = $_POST["noemp"];
        $estadoimc = $_POST["estadoimc"];
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $addquery = '';
        $departamento != '' && $addquery .= " AND tblIMCEnc.departamento=$departamento";
        $area != '' && $addquery .= " AND tblIMCEnc.area=$area";
        $noemp != '' && $addquery .= " AND (tblIMCEnc.responsable=$noemp OR tblIMCEnc.id=$noemp)";
        $estadoimc != '' && $addquery .= " AND tblIMCEnc.estado =  $estadoimc";
        $query = "SELECT tblIMCEnc.id as imc,tblIMCEnc.fecha as creado,tblEmpleados.Nombre as emisor,tblDepartamentos.NombreDepto as departamento,
		tblProactAreas.NombreArea as area,tblIMCDetecRisgo.detectasteelrisgo as deteccion, tblIMCTipRisgo.tiporiesgo as riesgo,tblIMCTipo.opciones as tipo,
		tblEmpleados2.Nombre as responsable,tblIMCEnc.fechacompromiso,tblIMCEstatus.estatus,tblIMCEnc.descripcion,tblIMCEnc.sugerencias FROM tblIMCEnc
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIMCEnc.emisor
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblIMCEnc.departamento
        INNER JOIN TLX003MXDB.dbo.tblProactAreas ON tblProactAreas.Id = tblIMCEnc.area
        INNER JOIN tblIMCDetecRisgo ON tblIMCDetecRisgo.id = tblIMCEnc.detriesgo
        INNER JOIN tblIMCTipRisgo ON tblIMCTipRisgo.id = tblIMCEnc.tiporiesgo
        INNER JOIN tblIMCTipo ON tblIMCTipo.id = tblIMCEnc.tipo
        INNER JOIN tblIMCEstatus ON tblIMCEstatus.id = tblIMCEnc.estado
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblEmpleados2 ON tblEmpleados2.NoEmp = tblIMCEnc.responsable
        WHERE (tblIMCEnc.fecha >= '" . $fechai . "' AND tblIMCEnc.fecha < dateadd(day, 1, '" . $fechaf . "')) $addquery";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "imc" => $row["imc"], "creado" => $row["creado"]->format("Y-m-d"), "emisor" => $row["emisor"], "departamento" => $row["departamento"],
                "area" => $row["area"], "deteccion" => $row["deteccion"], "riesgo" => $row["riesgo"], "tipo" => $row["tipo"],
                "responsable" => $row["responsable"], "fechacompromiso" => $row["fechacompromiso"]->format("Y-m-d"), "estatus" => $row["estatus"],
                "descripcion" => $row["descripcion"], "sugerencias" => $row["sugerencias"]
            ]);
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
        sqlsrv_close($conn);
    }
    function tblIMCEncxid()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $id = $_GET['id'];
        $query = 'SELECT tblIMCEnc.*,tblEmpleados.Nombre as emisornombre, tblEmpleados2.Nombre as responsablenombre,tblDepartamentos.NombreDepto from tblIMCEnc
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIMCEnc.emisor 
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblEmpleados2 ON tblEmpleados2.NoEmp = tblIMCEnc.responsable
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento WHERE tblIMCEnc.id=' . $id;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'fecha' => $row['fecha'], 'emisor' => $row['emisor'], 'departamento' => $row['departamento'], 'area' => $row['area'], 'detriesgo' => $row['detriesgo'],
                'tiporiesgo' => $row['tiporiesgo'], 'tipo' => $row['tipo'], 'descripcion' => $row['descripcion'], 'responsable' => $row['responsable'], 'sugerencias' => $row['sugerencias'], 'fechacompromiso' => $row['fechacompromiso']->format('Y-m-d'), 'estado' => $row['estado'], 'emisornombre' => $row['emisornombre'], 'responsablenombre' => $row['responsablenombre'], 'NombreDepartamento' => $row['NombreDepto']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function tblMisIMC()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $query = "SELECT TOP 50 tblIMCEnc.id as imc,tblIMCEnc.fecha as creado,tblEmpleados.Nombre as emisor,tblDepartamentos.NombreDepto as departamento,
		tblProactAreas.NombreArea as area,tblIMCDetecRisgo.detectasteelrisgo as deteccion, tblIMCTipRisgo.tiporiesgo as riesgo,tblIMCTipo.opciones as tipo,
		tblEmpleados2.Nombre as responsable,tblIMCEnc.fechacompromiso,tblIMCEstatus.estatus,tblIMCEnc.descripcion,tblIMCEnc.sugerencias FROM tblIMCEnc
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIMCEnc.emisor
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblIMCEnc.departamento
        INNER JOIN TLX003MXDB.dbo.tblProactAreas ON tblProactAreas.Id = tblIMCEnc.area
        INNER JOIN tblIMCDetecRisgo ON tblIMCDetecRisgo.id = tblIMCEnc.detriesgo
        INNER JOIN tblIMCTipRisgo ON tblIMCTipRisgo.id = tblIMCEnc.tiporiesgo
        INNER JOIN tblIMCTipo ON tblIMCTipo.id = tblIMCEnc.tipo
        INNER JOIN tblIMCEstatus ON tblIMCEstatus.id = tblIMCEnc.estado
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblEmpleados2 ON tblEmpleados2.NoEmp = tblIMCEnc.responsable
        WHERE tblIMCEnc.responsable=" . $_SESSION['ibm'];
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "imc" => $row["imc"], "creado" => $row["creado"]->format("Y-m-d"), "emisor" => $row["emisor"], "departamento" => $row["departamento"],
                "area" => $row["area"], "deteccion" => $row["deteccion"], "riesgo" => $row["riesgo"], "tipo" => $row["tipo"],
                "responsable" => $row["responsable"], "fechacompromiso" => $row["fechacompromiso"]->format("Y-m-d"), "estatus" => $row["estatus"],
                "descripcion" => $row["descripcion"], "sugerencias" => $row["sugerencias"]
            ]);
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
        sqlsrv_close($conn);
    }
}
if (isset($_GET['getDataCondiciones'])) {
    $imc = new IMC();
    $imc->getDataCondiciones();
} else if (isset($_GET['saveIMC'])) {
    $imc = new IMC();
    $imc->saveIMC();
} else if (isset($_GET['tblIMCEnc'])) {
    $imc = new IMC();
    $imc->tblIMCEnc();
} else if (isset($_GET['tblReporteIMC'])) {
    $imc = new IMC();
    $imc->tblReporteIMC();
} else if (isset($_GET['updateEstadoIMC'])) {
    $imc = new IMC();
    $imc->updateEstadoIMC();
} else if (isset($_GET['tblIMCEncxid'])) {
    $imc = new IMC();
    $imc->tblIMCEncxid();
} else if (isset($_GET['tblMisIMC'])) {
    $imc = new IMC();
    $imc->tblMisIMC();
}
