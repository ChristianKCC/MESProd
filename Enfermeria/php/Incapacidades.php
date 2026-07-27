<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";
class EnfermeriaIncapacidades
{
    function tblIncapacidades()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT tblEnfermeriaIncapacidades.id,tblEnfermeriaIncapacidades.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto as depto,tblPuestos.nombre as puesto,
			tblEmp2.noemp as responsable,tblEmp2.Nombre as responsablenombre,tblEnfermeriaIncapacidades.folio,tblEnfermeriaTipoIncapacidad.tipoincapacidad,tblEnfermeriaIncapacidades.fecharevision,
			tblEnfermeriaFrec.descfrecuencia,tblEnfermeriaIncapacidades.dias,tblEnfermeriaIncapacidades.fechainicio,st1,stps,fechaentrega,dx,firma, tblEnfermeriaIncapacidades.fechafin  
            FROM tblEnfermeriaIncapacidades
			  INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEnfermeriaIncapacidades.departamento
			  INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEnfermeriaIncapacidades.puesto
			  INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblEnfermeriaIncapacidades.noemp
			  INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblEmp2 ON tblEmp2.NoEmp = tblEnfermeriaIncapacidades.responsable
			  INNER JOIN tblEnfermeriaTipoIncapacidad ON tblEnfermeriaTipoIncapacidad.id = tblEnfermeriaIncapacidades.tipo
			  INNER JOIN tblEnfermeriaFrec ON tblEnfermeriaFrec.id = tblEnfermeriaIncapacidades.frecuencia
			  INNER JOIN tblGeneralSiNoNa ON tblGeneralSiNoNa.id = tblEnfermeriaIncapacidades.st1
			  INNER JOIN tblGeneralSiNoNa as sino2 ON sino2.id = tblEnfermeriaIncapacidades.stps ORDER BY tblEnfermeriaIncapacidades.id DESC ";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row['id'],
                "noemp" => $row['noemp'],
                "Nombre" => $row['Nombre'],
                "departamento" => $row['depto'],
                "puesto" => $row['puesto'],
                "responsable" => $row['responsable'],
                "responsablenombre" => $row['responsablenombre'],
                "folio" => $row['folio'],
                "tipo" => $row['tipoincapacidad'],
                "frecuencia" => $row['descfrecuencia'],
                "fecharevision" => $row['fecharevision']->format('Y-m-d'),
                "dias" => $row['dias'],
                "fechainicio" => $row['fechainicio']->format('Y-m-d'),
                "st1" => $row['st1'],
                "stps" => $row['stps'],
                "fechaentrega" => $row['fechaentrega'],
                "dx" => $row['dx'],
                "firma" => $row['firma'],
                "fechafin" => $row['fechafin']->format('Y-m-d')
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }
    function reporteIncapacidadesdata()
    {
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $departamento = $_POST["departamento"];
        $maquina = $_POST["maquina"];
        $noemp = $_POST["noemp"];
        $addwhere = "";
        empty($_POST["noemp"]) ? $addwhere .= "" : $addwhere .= " AND tblEmpleados.noemp = $noemp";
        empty($_POST["departamento"]) ? $addwhere .= "" : $addwhere .= " AND tblDepartamentos.NoDepto = $departamento";
        empty($_POST["maquina"]) ? $addwhere .= "" : $addwhere .= " AND tblMaquinas.NoMaquina = $maquina";
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT tblEnfermeriaIncapacidades.id,tblEnfermeriaIncapacidades.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto as depto,tblPuestos.nombre as puesto,
			tblEmp2.noemp as responsable,tblEmp2.Nombre as responsablenombre,tblEnfermeriaIncapacidades.folio,tblEnfermeriaTipoIncapacidad.tipoincapacidad,tblEnfermeriaIncapacidades.fecharevision,
			tblEnfermeriaFrec.descfrecuencia,tblEnfermeriaIncapacidades.dias,tblEnfermeriaIncapacidades.fechainicio,st1,stps,fechaentrega,dx,firma, fechafin
            FROM tblEnfermeriaIncapacidades
			  INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEnfermeriaIncapacidades.departamento
			  INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEnfermeriaIncapacidades.puesto
			  INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblEnfermeriaIncapacidades.noemp
			  INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblEmp2 ON tblEmp2.NoEmp = tblEnfermeriaIncapacidades.responsable
			  INNER JOIN tblEnfermeriaTipoIncapacidad ON tblEnfermeriaTipoIncapacidad.id = tblEnfermeriaIncapacidades.tipo
			  INNER JOIN tblEnfermeriaFrec ON tblEnfermeriaFrec.id = tblEnfermeriaIncapacidades.frecuencia
			  INNER JOIN tblGeneralSiNoNa ON tblGeneralSiNoNa.id = tblEnfermeriaIncapacidades.st1
			  INNER JOIN tblGeneralSiNoNa as sino2 ON sino2.id = tblEnfermeriaIncapacidades.stps WHERE fecharevision BETWEEN '$fechai' AND DATEADD(DAY,1, '$fechaf') $addwhere";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row['id'],
                "noemp" => $row['noemp'],
                "Nombre" => $row['Nombre'],
                "departamento" => $row['depto'],
                "puesto" => $row['puesto'],
                "responsable" => $row['responsable'],
                "responsablenombre" => $row['responsablenombre'],
                "folio" => $row['folio'],
                "tipo" => $row['tipoincapacidad'],
                "frecuencia" => $row['descfrecuencia'],
                "fecharevision" => $row['fecharevision']->format('Y-m-d'),
                "dias" => $row['dias'],
                "fechainicio" => $row['fechainicio']->format('Y-m-d'),
                "st1" => $row['st1'],
                "stps" => $row['stps'],
                "fechaentrega" => $row['fechaentrega'],
                "dx" => $row['dx'],
                "firma" => $row['firma'],
                "fechafin" => $row['fechafin']->format('Y-m-d')
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }
    function dataforeditIncapacidad()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $id = $_GET['id'];
        $query = "WITH Incapacidades AS (
                    SELECT 
                        i.*,
                        e.Nombre,
                        r.Nombre AS responsablenombre,
                        e.EmpleadoSindicalizado as sindicalizado,
                        SUM(i.dias) OVER (PARTITION BY i.noemp) AS DiasAcumulados
                    FROM tblEnfermeriaIncapacidades AS i
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados AS e ON e.NoEmp = i.noemp
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados AS r ON r.NoEmp = i.responsable
                )
                SELECT * FROM Incapacidades WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $row["fechainicio"] = $row["fechainicio"]->format("Y-m-d");
            $row["fecharevision"] = $row["fecharevision"]->format("Y-m-d");
            $row["DiasAcumulados"] = (int) $row["DiasAcumulados"];
            $array[] = $row;
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }
    function saveIncapacidad()
    {
        $noemp = $_POST["noemp"];
        $departamento = $_POST["departamento"];
        $puesto = $_POST["puesto"];
        $responsable = $_POST["responsable"];
        $folio = $_POST["folio"];
        $tipo = $_POST["tipo"];
        $frecuencia = $_POST["frecuencia"];
        $fecharevision = $_POST["fecharevision"];
        $dias = $_POST["dias"];
        $fechainicio = $_POST["fechainicio"];
        $st1 = $_POST["st1"];
        $stps = $_POST["stps"];
        $fechaentrega = $_POST["fechaentrega"];
        $dx = $_POST["dx"];
        $id = $_POST["id"];
        $fechatermina = $_POST["fechatermina"];
        // $firmaBase64 = $_POST['firma'];
        // $firmaBase64 = str_replace('data:image/png;base64,', '', $firmaBase64);
        // $firmaBase64 = str_replace(' ', '+', $firmaBase64);
        // $firmaBinaria = base64_decode($firmaBase64);

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $array = array(
            $noemp,
            $departamento,
            $puesto,
            $responsable,
            $folio,
            $tipo,
            $frecuencia,
            $dias,
            $fechainicio,
            $st1,
            $stps,
            $fechaentrega,
            $dx,
            $_SESSION['ibm'],
            $fecharevision,             
            $fechatermina
        );
        $query = "INSERT INTO tblEnfermeriaIncapacidades(noemp,departamento,puesto,responsable,folio,tipo,frecuencia,
        dias,fechainicio,st1,stps,fechaentrega,dx,idsession,fecharevision, fechafin) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
    }
    function updateIncapacidad()
    {
        $noemp = $_POST["noemp"];
        $departamento = $_POST["departamento"];
        $puesto = $_POST["puesto"];
        $responsable = $_POST["responsable"];
        $folio = $_POST["folio"];
        $tipo = $_POST["tipo"];
        $frecuencia = $_POST["frecuencia"];
        $fecharevision = $_POST["fecharevision"];
        $dias = $_POST["dias"];
        $fechainicio = $_POST["fechainicio"];
        $st1 = $_POST["st1"];
        $stps = $_POST["stps"];
        $fechaentrega = $_POST["fechaentrega"];
        $dx = $_POST["dx"];
        $id = $_POST["id"];
        $firmaBase64 = $_POST['firma'];
        $firmaBase64 = str_replace('data:image/png;base64,', '', $firmaBase64);
        $firmaBase64 = str_replace(' ', '+', $firmaBase64);
        $firmaBinaria = base64_decode($firmaBase64);
        $fechatermina = $_POST["fechatermina"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $array = array(
            $noemp,
            $departamento,
            $puesto,
            $responsable,
            $folio,
            $tipo,
            $frecuencia,
            $dias,
            $fechainicio,
            $st1,
            $stps,
            $fechaentrega,
            $dx,
            $firmaBase64,
            $_SESSION['ibm'],
            $fecharevision,
            $fechatermina,
            $id,

        );
        $query = "UPDATE tblEnfermeriaIncapacidades SET noemp = ?,departamento = ?,puesto = ?,responsable = ?,folio = ?,tipo = ?,frecuencia = ?,
        dias = ?,fechainicio = ?,st1 = ?,stps = ?,fechaentrega = ?,dx = ?,firma = ?,idsession = ?, fecharevision = ?, fechafin = ? WHERE id = ?";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
    }
}
if (isset($_GET['saveIncapacidad'])) {
    $Consultas = new EnfermeriaIncapacidades();
    $Consultas->saveIncapacidad();
} else if (isset($_GET['tblIncapacidades'])) {
    $Consultas = new EnfermeriaIncapacidades();
    $Consultas->tblIncapacidades();
} else if (isset($_GET['dataforeditIncapacidad'])) {
    $Consultas = new EnfermeriaIncapacidades();
    $Consultas->dataforeditIncapacidad();
} else if (isset($_GET['reporteIncapacidadesdata'])) {
    $Consultas = new EnfermeriaIncapacidades();
    $Consultas->reporteIncapacidadesdata();
} else if (isset($_GET['updateIncapacidad'])) {
    $Consultas = new EnfermeriaIncapacidades();
    $Consultas->updateIncapacidad();
}
