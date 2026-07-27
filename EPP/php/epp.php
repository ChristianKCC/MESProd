<?php
require_once '../../conexion.php';
require_once '../../Session/seguridad.php';
class EPP
{
    function ListEppBasico($tipo)
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $query = 'SELECT * FROM tblEPPListEquipo WHERE tipo=' . $tipo;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ['id' => $row['id'], 'nombre' => $row['nombre'], 'tipo' => $row['tipo']]);
        }
        echo json_encode($array);
    }
    function saveEPP()
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $noemp = $_POST['noemp'];
        $noempres = $_POST['noempres'];
        $noempres == '' ? $_SESSION["ibm"] : $noempres;
        $idsession = '';
        $noempres == '' ? $idsession = $_SESSION["ibm"] : $idsession = $_SESSION["idmaquina"];
        $comentario = $_POST['comentario'];
        $checkbox = json_decode($_POST['checkbox']);
        $query = "INSERT INTO tblEPPEnc(noemp,cargo,comentario,idsession) VALUES (?,?,?,?)";
        $result = sqlsrv_query($conn, $query, array($noemp, $noempres, $comentario, $idsession));
        if ($result) {
            $query_get_id = "SELECT @@IDENTITY AS id";
            $result_get_id = sqlsrv_query($conn, $query_get_id);
            $row = sqlsrv_fetch_array($result_get_id, SQLSRV_FETCH_ASSOC);
            $querycheck = "INSERT INTO tblEPPSubEnc(idEnc,Equipo,valor) VALUES (?,?,?)";
            foreach ($checkbox as $checkrec)
                sqlsrv_query($conn, $querycheck, array($row['id'], $checkrec->nombre, $checkrec->valor));
            http_response_code(200);
        } else {
            http_response_code(500);
        }
    }
    function tblEPPEnc($idSession)
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $query = 'SELECT tblEPPEnc.id,tblEPPEnc.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblEPPEnc.fecha,tblEPPEnc.comentario FROM tblEPPEnc 
		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON  tblEmpleados.NoEmp = tblEPPEnc.noemp
		INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON  tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento WHERE tblEPPEnc.cargo = (?) OR tblEPPEnc.idsession = (?)';
        $result = sqlsrv_query($conn, $query, array($idSession == 1 ? $_SESSION['ibm'] : $_SESSION['idmaquina'], $idSession == 1 ? $_SESSION['ibm'] : $_SESSION['idmaquina']));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'], 'noemp' => $row['noemp'], 'nombre' => $row['Nombre'], 'departamento' => $row['NombreDepto'], 'comentario' => $row['comentario'],
                'fecha' => $row['fecha']->format('Y/m/d H:i:s')
            ]);
        }
        echo json_encode($array);
    }
    function tblEPPSubEnc()
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $folio = $_GET['folio'];
        $query = 'SELECT tblEPPEnc.id,tblEPPEnc.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblEPPEnc.fecha,tblEPPListEquipo.nombre as equipo,tblEPPListValor.nombre as valor FROM tblEPPEnc 
        INNER JOIN tblEPPSubEnc ON tblEPPSubEnc.idEnc = tblEPPEnc.id
        INNER JOIN tblEPPListEquipo ON tblEPPListEquipo.id = tblEPPSubEnc.Equipo
        INNER JOIN tblEPPListValor ON tblEPPListValor.id = tblEPPSubEnc.valor
		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON  tblEmpleados.NoEmp = tblEPPEnc.noemp
		INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON  tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento WHERE tblEPPEnc.id=' . $folio;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'], 'noemp' => $row['noemp'], 'nombre' => $row['Nombre'], 'departamento' => $row['NombreDepto'],
                'fecha' => $row['fecha']->format('Y/m/d H:i:s'), 'equipo' => $row['equipo'], 'valor' => $row['valor']
            ]);
        }
        echo json_encode($array);
    }
    function tblEPPReporte()
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $fechai = $_POST['fechai'];
        $fechaf = $_POST['fechaf'];
        $departamento = $_POST['departamento'];
        $noemp = $_POST['noemp'];
        $observador = $_POST['observador'];
        $departamento != '' &&  $departamento = "AND tblDepartamentos.NoDepto=$departamento";
        $noemp != '' &&  $noemp = "AND tblEmpleados.NoEmp LIKE '%$noemp%'";
        $observador != '' &&  $observador = "AND tblEPPEnc.cargo LIKE '%$observador%'";
        $query = "SELECT tblEPPEnc.id,tblEPPEnc.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblEPPEnc.fecha,tblEPPEnc.comentario FROM tblEPPEnc 
		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON  tblEmpleados.NoEmp = tblEPPEnc.noemp
		INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON  tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento WHERE tblEPPEnc.fecha between '$fechai' AND '$fechaf' 
        $departamento $noemp $observador";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'], 'noemp' => $row['noemp'], 'nombre' => $row['Nombre'], 'departamento' => $row['NombreDepto'], 'comentario' => $row['comentario'],
                'fecha' => $row['fecha']->format('Y/m/d H:i:s')
            ]);
        }
        echo json_encode($array);
    }
}

$EppObj = new EPP();
if (isset($_GET['ListEppBasico'])) {
    $EppObj->ListEppBasico(3);
} else if (isset($_GET['ListEppEspecifico'])) {
    $EppObj->ListEppBasico(1);
} else if (isset($_GET['ListEppBPM'])) {
    $EppObj->ListEppBasico(2);
} else if (isset($_GET['saveEPP'])) {
    $EppObj->saveEPP();
} else if (isset($_GET['tblEPPEnc'])) {
    $session = $_GET['session'];
    $EppObj->tblEPPEnc($session);
} else if (isset($_GET['tblEPPSubEnc'])) {
    $EppObj->tblEPPSubEnc();
} else if (isset($_GET['tblEPPReporte'])) {
    $EppObj->tblEPPReporte();
}
