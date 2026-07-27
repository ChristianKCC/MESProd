<?php
require_once('../../conexion.php');
require_once('../../Components/tools.php');

// Clase principal de CRUD asistencias 
class Asistencias
{
	// Funcion para rellenado de tabla en Asistencias.php para consulta de asistencias
	// function reporteasistencias()
	// {
	// 	$Conecta = new ClassConexion();
	// 	$conn = $Conecta->conexion("TLX001MXDB");
	// 	$fechai =  $_POST['fechai'];
	// 	$fechaf =  $_POST['fechaf'];
	// 	$empno =  $_POST['empno'];
	// 	$departamento =  $_POST['departamento'];
	// 	$addwhere = "";
	// 	// Adjunta condicionales en caso de que los campos no esten vacios
	// 	empty($departamento) ? $addwhere .= "" : $addwhere .= "AND tblEmpleados.NombreDepartamento=$departamento";
	// 	empty($empno) ? $addwhere .= "" : $addwhere .= "AND tblEmpleados.NoEmp LIKE '%$empno%'";
	// 	// Obtencion de NoEmp, Nombre, fecha, temperatura y nombre de departamento con:
	// 	// TLX001MXDB para datos, uniendo solo la 32 para busqueda de nombre mediante INNER por pin buscando entre rango de fechas
	// 	$query = "SELECT 
	// 				pin,
	// 				tblEmpleados.Nombre,
	// 				event_time,
	// 				temperature,
	// 				dept_name 
	// 			FROM TLX001MXDB.dbo.acc_transaction
	// 		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=pin 
	// 		WHERE CONVERT(date,event_time) 
	// 		BETWEEN '$fechai' AND '$fechaf' 
	// 		$addwhere";
	// 	$result = sqlsrv_query($conn, $query);
	// 	$array = array();
	// 	while ($row = sqlsrv_fetch_array($result)) {
	// 		array_push($array, ["ibm" => $row[0], "nombre" => $row[1], "fecha" => $row[2]->format('Y-m-d H:i:s'), "temperatura" => $row[3], "ubicacion" => $row[4]]);
	// 	}
	// 	sqlsrv_close($conn);
	// 	echo json_encode($array);
	// }


	function reporteasistencias()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX001MXDB");

		$fechai = $_POST['fechai'];
		$fechaf = $_POST['fechaf'];
		$empno = $_POST['empno'];
		$departamento = $_POST['departamento'];

		$params = [$fechai, $fechaf];
		$addwhere = "";

		if (!empty($departamento)) {
			$addwhere .= " AND E.NombreDepartamento = ?";
			$params[] = $departamento;
		}
		if (!empty($empno)) {
			$addwhere .= " AND E.NoEmp LIKE ?";
			$params[] = '%' . $empno . '%';
		}

		$query = "SELECT
                U.pin,
                E.Nombre,
                U.event_time,
                U.temperature,
                U.ubicacion,
                U.origen
            FROM (
                SELECT
                    CAST(pin AS VARCHAR(50))  AS pin,
                    event_time                AS event_time,
                    temperature               AS temperature,
                    dept_name                 AS ubicacion,
                    'ACCESO'                  AS origen
                FROM TLX001MXDB.dbo.acc_transaction

                UNION ALL

                SELECT
                    CAST(pers_person_pin AS VARCHAR(50)),
                    CAST(CAST(att_date AS DATETIME) + CAST(att_time AS DATETIME) AS DATETIME),
                    temperature,
                    auth_area_name,
                    'ASISTENCIA'
                FROM TLX001MXDB.dbo.att_transaction
                WHERE auth_area_no IN (16)
            ) AS U
            INNER JOIN TLX032MXDB.dbo.tblEmpleados AS E
                ON E.NoEmp = U.pin
            WHERE CONVERT(date, U.event_time) BETWEEN ? AND ?
            $addwhere
            ORDER BY U.event_time DESC";

		$result = sqlsrv_query($conn, $query, $params);

		if ($result === false) {
			http_response_code(500);
			echo json_encode(sqlsrv_errors());
			return;
		}

		$array = array();
		while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
			array_push($array, [
				"ibm" => $row['pin'],
				"nombre" => $row['Nombre'],
				"fecha" => $row['event_time']->format('Y-m-d H:i:s'),
				"temperatura" => $row['temperature'],
				"ubicacion" => $row['ubicacion'],
				"origen" => $row['origen']
			]);
		}
		sqlsrv_close($conn);
		echo json_encode($array);
	}







	// Funcion de carga de archivos para descansos de empleados
	function uploadFile()
	{
		$ClassConexion = new ClassConexion();
		$conn = $ClassConexion->conexion("TLX002MXDB");
		$fechadescansos = $_POST['fechadescansos'];
		if ($_FILES['filec']['error'] == UPLOAD_ERR_OK) {
			$csvFile = $_FILES['filec']['tmp_name'];
			$file = fopen($csvFile, 'r');
			while (($line = fgetcsv($file)) !== FALSE) {
				$query = "INSERT INTO tblAsistenciasDescansos (fecha, noemp, lunes, martes, miercoles, jueves, viernes, sabado, domingo) 
				VALUES ('" . $fechadescansos . "','" . $line[0] . "','" . $line[1] . "','" . $line[2] . "','" . $line[3] . "','" . $line[4] . "','" . $line[5] . "'
				,'" . $line[6] . "','" . $line[7] . "')";
				$stmt = sqlsrv_query($conn, $query);
			}
			http_response_code(200);
			fclose($file);
		}
	}

	// Funcion para obtener los descansos de empleados entre determinado rango de fechas
	function getDatatblDescansos()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$fechai = $_POST['fechai'];
		$fechaf = $_POST['fechaf'];
		$noemp = $_POST['noemp'];
		$noemp != '' ? $noemp = "AND tblAsistenciasDescansos.noemp =" . $_POST['noemp'] : $noemp = '';
		$query = "SELECT tblAsistenciasDescansos.*, tblEmpleados.Nombre as nombreemp FROM tblAsistenciasDescansos
		INNER JOIN TLX032MXDB.dbo.tblEmpleados  ON tblEmpleados.NoEmp = tblAsistenciasDescansos.Noemp
		WHERE fecha BETWEEN '$fechai' AND '$fechaf' $noemp";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"id" => $row[0],
				"noemp" => $row[2],
				"nombre" => $row[10],
				"fecha" => $row[1]->format('Y-m-d'),
				"lunes" => $row[3],
				"martes" => $row[4],
				"miercoles" => $row[5],
				"jueves" => $row[6],
				"viernes" => $row[7],
				"sabado" => $row[8],
				"domingo" => $row[9]
			]);
		}
		sqlsrv_close($conn);
		echo json_encode($array);
	}

	// Funcion para eliminar descansos de determinado empleado segun su numero de empleado
	function deleteDescanso()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_POST['id'];
		$query = "DELETE FROM tblAsistenciasDescansos WHERE id=$id";
		$result = sqlsrv_query($conn, $query);
		$result === false ? http_response_code(500) : http_response_code(200);
	}
}

if (isset($_GET["reporteasistencias"])) {
	$asistencia = new Asistencias();
	$asistencia->reporteasistencias();
} else if (isset($_GET["uploadFile"])) {
	$asistencia = new Asistencias();
	$asistencia->uploadFile();
} else if (isset($_GET["getDatatblDescansos"])) {
	$asistencia = new Asistencias();
	$asistencia->getDatatblDescansos();
} else if (isset($_GET["deleteDescanso"])) {
	$asistencia = new Asistencias();
	$asistencia->deleteDescanso();
}
