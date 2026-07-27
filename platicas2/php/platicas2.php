<?php
require_once '../../conexion.php';
require_once '../../Session/seguridad.php';
class PlaticasMaquina
{
	function tblPlaticas5min()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$query = "SELECT TOP 200 tblPlaticas5min.id,tblPlaticas5min.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblPlaticas5min.fecha,tblPlaticas5min.tipo,
		tblPlaticas5min.nombreplatica,tblPlaticas5min.minutos,tblPlaticas5min.archivo FROM tblPlaticas5min
		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblPlaticas5min.noemp
		INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento ORDER BY tblPlaticas5min.id DESC";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, $row);
		}
		echo json_encode($array);
		sqlsrv_close($conn);
	}
	function tblsubencabezado()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX035MXDB");
		$folio = $_GET["id"];
		$query = "SELECT tblSubEncabezadoplaticas5min.id,tblSubEncabezadoplaticas5min.noemp, tblEmpleados.Nombre FROM tblSubEncabezadoplaticas5min INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblSubEncabezadoplaticas5min.noemp WHERE tblSubEncabezadoplaticas5min.folio=$folio ORDER BY tblSubEncabezadoplaticas5min.id DESC";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["id" => $row[0], "noemp" => $row[1], "nombre" => $row[2]]);
		}
		echo json_encode($array);
		sqlsrv_close($conn);
	}
	function deletePlatica()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$folio = $_POST["folio"];
		$query = "DELETE FROM tblPlaticas5min WHERE id=$folio";
		$result = sqlsrv_query($conn, $query);
		$result === false ? http_response_code(500) : http_response_code(200);
	}
	function savePlaticas5min()
	{
		$Conexion = new ClassConexion();
		$conn = $Conexion->conexion("TLX002MXDB");
		$noemp = $_POST['noemp'];
		$nombre = $_POST['nombre'];
		$fecha = $_POST['fecha'];
		$tipo = $_POST['tipo'];
		$nombreplatica = $_POST['nombreplatica'];
		$minutos = $_POST['minutos'];
		$file = $_FILES['file'];
		if ($noemp == '' || $nombre == '' || $fecha == '' || $tipo == '' || $nombreplatica == '' || $minutos == '') {
			http_response_code(201);
			die();
		}
		$ruta = "../Files/" . $file['name'];
		move_uploaded_file($file['tmp_name'], $ruta);
		$sql = "INSERT INTO tblPlaticas5min (noemp,fecha,tipo,nombreplatica,minutos,archivo,noempsession) VALUES ( ? , ? ,? ,? ,? ,? ,?)";
		$params = array($noemp, $fecha, $tipo, $nombreplatica, $minutos, $ruta, $_SESSION['ibm']);
		$result = sqlsrv_query($conn, $sql, $params);
		$result === false ? http_response_code(500) : http_response_code(200);
		sqlsrv_close($conn);
	}
	function confirmaAsistencia()
	{
		$Conexion = new ClassConexion();
		$conn = $Conexion->conexion("TLX002MXDB");
		$idplatica = $_GET['idplatica'];
		$params = array($_SESSION['ibm'], $idplatica);
		$validasql = 'SELECT count(*) FROM tblPlaticas5minAsistencias WHERE noemp= ? AND idplatica = ?';
		$result = sqlsrv_query($conn, $validasql, $params);
		sqlsrv_fetch($result);
		$res = sqlsrv_get_field($result, 0);
		if ($res > 0) {
			http_response_code(201);
			die();
		}
		$sql = "INSERT INTO tblPlaticas5minAsistencias (noemp,idplatica) VALUES ( ? , ?)";
		$result = sqlsrv_query($conn, $sql, $params);
		$result === false ? http_response_code(500) : http_response_code(200);
		sqlsrv_close($conn);
	}
	function getPlaticatoday()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$query = "SELECT id,archivo FROM tblPlaticas5min WHERE fecha = convert(date,GETDATE())";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["id" => $row['id'], "ruta" => $row['archivo']]);
		}
		echo json_encode($array);
		sqlsrv_close($conn);
	}
	function regPlaticasub()
	{
		$Conexion = new ClassConexion();
		$conn = $Conexion->conexion("TLX002MXDB");
		$idplatica = $_POST['idplatica'];
		$noemp = $_POST['noemp'];
		$params = array($noemp, $idplatica);
		$validasql = 'SELECT count(*) FROM tblPlaticas5minAsistencias WHERE noemp= ? AND idplatica = ?';
		$result = sqlsrv_query($conn, $validasql, $params);
		sqlsrv_fetch($result);
		$res = sqlsrv_get_field($result, 0);
		if ($res > 0) {
			http_response_code(201);
			die();
		}
		$sql = "INSERT INTO tblPlaticas5minAsistencias (noemp,idplatica,idsession) VALUES ( ? , ?, " . $_SESSION['idmaquina'] . ")";
		$result = sqlsrv_query($conn, $sql, $params);
		$result === false ? http_response_code(500) : http_response_code(200);
		sqlsrv_close($conn);
	}
	function getDataAsistencias()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$query = "SELECT TOP 100 tblPlaticas5minAsistencias.id,tblEmpleados.noemp,tblEmpleados.Nombre,tblPuestos.nombre as puesto FROM tblPlaticas5minAsistencias
		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblPlaticas5minAsistencias.noemp
		INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.Puesto 
		INNER JOIN tblPlaticas5min ON tblPlaticas5min.id = tblPlaticas5minAsistencias.idplatica WHERE tblPlaticas5min.fecha = CONVERT(date,GETDATE()) AND idsession = " . $_SESSION['idmaquina'] . " ORDER BY tblPlaticas5minAsistencias.id";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["id" => $row['id'], "noemp" => $row['noemp'], "Nombre" => $row['Nombre'], "puesto" => $row['puesto']]);
		}
		echo json_encode($array);
		sqlsrv_close($conn);
	}
}

if (isset($_GET["tblPlaticas5min"])) {
	$PlaticasMaquina = new PlaticasMaquina();
	$PlaticasMaquina->tblPlaticas5min();
} else if (isset($_GET["tblsubencabezado"])) {
	$PlaticasMaquina = new PlaticasMaquina();
	$PlaticasMaquina->tblsubencabezado();
} else if (isset($_GET["savePlaticas5min"])) {
	$PlaticasMaquina = new PlaticasMaquina();
	$PlaticasMaquina->savePlaticas5min();
} else if (isset($_GET["confirmaAsistencia"])) {
	$PlaticasMaquina = new PlaticasMaquina();
	$PlaticasMaquina->confirmaAsistencia();
} else if (isset($_GET["getPlaticatoday"])) {
	$PlaticasMaquina = new PlaticasMaquina();
	$PlaticasMaquina->getPlaticatoday();
} else if (isset($_GET["regPlaticasub"])) {
	$PlaticasMaquina = new PlaticasMaquina();
	$PlaticasMaquina->regPlaticasub();
} else if (isset($_GET["getDataAsistencias"])) {
	$PlaticasMaquina = new PlaticasMaquina();
	$PlaticasMaquina->getDataAsistencias();
} else if (isset($_GET["deletePlatica"])) {
	$PlaticasMaquina = new PlaticasMaquina();
	$PlaticasMaquina->deletePlatica();
}
