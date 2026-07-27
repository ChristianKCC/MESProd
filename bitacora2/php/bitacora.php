<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';
class BitacoraElectronica
{
	function saveTrazabilidad()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$clave = $_POST['clave'];
		$modulo = $_POST['modulo'];
		$material = $_POST['material'];
		$empleado = $_POST['empleado'];
		$lote = $_POST['lote'];
		$folio = $_POST['folio'];
		$hora = $_POST['hora'];
		$idbitacora = $_POST['idbitacora'];
		$query = "INSERT INTO tblTrazabilidadEnc (clave,modulo,material,empleado,numlote,folio,hora,idbitacora) 
        VALUES ('" . $clave . "','" . $modulo . "','" . $material . "','" . $empleado . "','" . $lote . "','" . $folio . "','" . $hora . "','" . $idbitacora . "')";
		$result = sqlsrv_query($conn, $query);
		$result === false ? http_response_code(500) : http_response_code(200);
		sqlsrv_close($conn);
	}
	function tblTrazabilidadEnc()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$idbitacora = $_GET['idbitacora'];
		$query = "SELECT tblTrazabilidadClaves.Nombre,tblTrazabilidadModulos.Nombre,tblTrazabilidadMateriales.Nombre,tblEmpleados.Nombre,
        tblTrazabilidadEnc.numlote,tblTrazabilidadEnc.folio,tblTrazabilidadEnc.hora,tblTrazabilidadEnc.fecha,tblabreturno.turno, 
        tblMaquinas.NombreMaquina FROM tblTrazabilidadEnc
        INNER JOIN tblTrazabilidadClaves on tblTrazabilidadClaves.id= tblTrazabilidadEnc.clave
        INNER JOIN tblTrazabilidadModulos ON tblTrazabilidadModulos.ID= tblTrazabilidadEnc.modulo
        INNER JOIN tblTrazabilidadMateriales ON tblTrazabilidadMateriales.ID= tblTrazabilidadEnc.material
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp= tblTrazabilidadEnc.empleado
        INNER JOIN TLX002MXDB.dbo.tblabreturno ON tblabreturno.id=tblTrazabilidadEnc.idbitacora
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblabreturno.maquina WHERE tblabreturno.id=$idbitacora";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"clave" => $row[0],
				"modulo" => $row[1],
				"material" => $row[2],
				"empleado" => $row[3],
				"lote" => $row[4],
				"folio" => $row[5],
				"hora" => $row[6]->format('H:i:s'),
				"fecha" => $row[7]->format('Y-d-m H:i:s'),
				"turno" => $row[8],
				"maquina" => $row[9]
			]);
		}
		echo json_encode($array);
		sqlsrv_close($conn);
	}
	function saveNoConformidad()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$fecha = $_POST['fechaconf'];
		$deps = $_POST['depsconf'];
		$sellador = $_POST['selladorconf'];
		$operador = $_POST['operadorconf'];
		$maquinas = $_SESSION['idmaquina'];
		$turno = $_POST['turnoconf'];
		$claveprod = $_POST['claveprodconf'];
		$hora = $_POST['horaconf'];
		$defecto = $_POST['defectoconf'];
		$descripcion = $_POST['descripcionconf'];
		$totalprod = $_POST['totalprodconf'];
		$prodrecuperado = $_POST['prodrecuperadoconf'];
		$prodmerma = $_POST['prodmermaconf'];
		$empdefecto = $_POST['empdefectioconf'];
		$terdefecto = $_POST['terdefectoconf'];
		$accionescorrectivas = $_POST['accionescorrectivasconf'];
		$lider = $_POST['liderconf'];
		$tipeatributeconf = $_POST['tipeatributeconf'];
		$componentesconf = $_POST['componentesconf'];
		$query = "INSERT INTO tblNoConformidad(fecha,departamento,sellador,operador,maquina,turno,producto,hora,defecto,descripcion,totalprod,prodrecuperado,
        prodmerma,accionescorrectivas,lider,codempdefecto,codterdefecto,tipoatributo,componente) 
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$result = sqlsrv_query($conn, $query, (array(
			$fecha,
			$deps,
			$sellador,
			$operador,
			$maquinas,
			$turno,
			$claveprod,
			$hora,
			$defecto,
			$descripcion,
			$totalprod,
			$prodrecuperado,
			$prodmerma,
			$accionescorrectivas,
			$lider,
			$empdefecto,
			$terdefecto,
			$tipeatributeconf,
			$componentesconf
		)));
		$result === false ? http_response_code(500) : http_response_code(200);
		sqlsrv_close($conn);
	}
	function updateNoConformidad()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$idconf = $_POST['idconf'];
		$fecha = $_POST['fechaconf'];
		$deps = $_POST['depsconf'];
		$sellador = $_POST['selladorconf'];
		$operador = $_POST['operadorconf'];
		$maquinas = $_POST['maquinasconf'];
		$turno = $_POST['turnoconf'];
		$claveprod = $_POST['claveprodconf'];
		$hora = $_POST['horaconf'];
		$defecto = $_POST['defectoconf'];
		$descripcion = $_POST['descripcionconf'];
		$totalprod = $_POST['totalprodconf'];
		$prodrecuperado = $_POST['prodrecuperadoconf'];
		$prodmerma = $_POST['prodmermaconf'];
		$empdefecto = $_POST['empdefectioconf'];
		$terdefecto = $_POST['terdefectoconf'];
		$accionescorrectivas = $_POST['accionescorrectivasconf'];
		$lider = $_POST['liderconf'];
		$tipeatributeconf = $_POST['tipeatributeconf'];
		$componentesconf = $_POST['componentesconf'];
		$query = "UPDATE tblNoConformidad SET fecha='" . $fecha . "',departamento='" . $deps . "',sellador='" . $sellador . "',operador='" . $operador . "',
        maquina='" . $maquinas . "',turno='" . $turno . "',producto='" . $claveprod . "',hora='" . $hora . "',defecto='" . $defecto . "',descripcion='" . $descripcion . "',
        totalprod='" . $totalprod . "',prodrecuperado='" . $prodrecuperado . "',prodmerma='" . $prodmerma . "',accionescorrectivas='" . $accionescorrectivas . "',
        lider='" . $lider . "',codempdefecto='" . $empdefecto . "',codterdefecto='" . $terdefecto . "',tipoatributo='" . $tipeatributeconf . "',componente ='" . $componentesconf . "' WHERE id=$idconf";
		$result = sqlsrv_query($conn, $query);
		$result === false ? http_response_code(500) : http_response_code(200);
		sqlsrv_close($conn);
	}
	function tblNoConformidad()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$query = "SELECT TOP 20 tblNoConformidad.id,tblNoConformidad.fecha,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,trazabilidaddefectos.nombre 
        FROM tblNoConformidad INNER JOIN TLX009MXDB.dbo.tblMaquinas ON TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblNoConformidad.maquina 
        INNER JOIN TLX036MXDB.dbo.trazabilidaddefectos on trazabilidaddefectos.id = tblNoConformidad.defecto WHERE tblMaquinas.NoMaquina= " . $_SESSION['idmaquina'] . "  ORDER BY id DESC";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"id" => $row[0],
				"fecha" => $row[1]->format('Y-m-d'),
				"maquina" => $row[2],
				"defecto" => $row[3]
			]);
		}
		echo json_encode($array);
		sqlsrv_close($conn);
	}
	function getAllDataConf()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_POST['id'];
		$query = "SELECT * FROM tblNoConformidad WHERE id=$id";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"id" => $row[0],
				"fecha" => $row[1]->format('Y-m-d'),
				"departamento" => $row[2],
				"sellador" => $row[3],
				"operador" => $row[4],
				"maquina" => $row[5],
				"turno" => $row[6],
				"producto" => $row[7],
				"hora" => $row[8],
				"defecto" => $row[9],
				"descripcion" => $row[10],
				"totalprod" => $row[11],
				"prodrecuperado" => $row[12],
				"prodmerma" => $row[13],
				"accionescorrectivas" => $row[14],
				"lider" => $row[15],
				"calidad" => $row[16],
				"codempdefecto" => $row[17],
				"codterdefecto" => $row[18],
				"tipoatributo" => $row[19]
			]);
		}
		echo json_encode($array);
		sqlsrv_close($conn);
	}

	function getDataNow($maquina)
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX004MXDB");
		$query = "SELECT TOP 1 Turno,VelPromMaquina,VelocidadActual,TotalCortes,TotalMerma,TiempoCorrida,TiempoParo,idBitacora,MMC_MachineRun,CortesCorrida,
		RechazosCorrida,TiempoarribaTurno,TiempoabajoTurno,idBitacora FROM tblBitacoraMaquinas WHERE Maquina=$maquina ORDER BY id DESC";
		$result = sqlsrv_query($conn, $query);
		$array =  array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				'turno' => $row['Turno'],
				'velocidadprom' => $row['VelPromMaquina'],
				'velocidadact' => $row['VelocidadActual'],
				'cortes' => $row['TotalCortes'],
				'rechazos' => $row['TotalMerma'],
				'tcorrida' => $row['TiempoCorrida'],
				'tparo' => $row['TiempoParo'],
				'idbitacora' => $row['idBitacora'],
				'estado' => $row['MMC_MachineRun'],
				'CortesCorrida' => $row['CortesCorrida'],
				'RechazosCorrida' => $row['RechazosCorrida'],
				'TiempoarribaTurno' => $row['TiempoarribaTurno'],
				'TiempoabajoTurno' => $row['TiempoabajoTurno'],
				'idBitacora' => $row['idBitacora']
			]);
		}
		echo json_encode($array);
		sqlsrv_close($conn);
	}
	function GetDataMonitor($maquina)
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX004MXDB");
		$hora = $Opera =  $merma = array();
		$numhrs = $_GET["numhrs"] * 180;
		$query = "SELECT * FROM (SELECT TOP ($numhrs) * FROM tblBitacoraMaquinas WHERE Maquina=$maquina ORDER BY id DESC) as T
			ORDER BY id ASC;";
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			$row["TotalMerma"] == 0 || $row["TotalCortes"] == 0 ? $resultado = 0 : $resultado = ($row["TotalMerma"] / $row["TotalCortes"]) * 100;
			array_push($hora, $row["FechaHora"]->format("H:i:s"));
			array_push($Opera, ($row["MMC_MachineRun"] * 10));
			array_push($merma, number_format($resultado, 2));
		}
		$respuesta = ["hora" => $hora, "datos" => $Opera, "merma" => $merma];
		echo json_encode($respuesta);
		sqlsrv_close($conn);
	}
	function hora_actual()
	{
		date_default_timezone_set('America/Mexico_City');
		$hora_actual = date('H:i');
		if ($hora_actual >= '07:00' && $hora_actual < '15:00') {
			$turno = 1;
		} else if ($hora_actual >= '15:00' && $hora_actual < '22:30') {
			$turno = 2;
		} else {
			$turno = 3;
		}
		return $turno;
	}
	function abreturno()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX004MXDB");
		$maquina = $_SESSION['idmaquina'];
		$turno = $this->hora_actual();
		$hora_actual = date('H:i');
		$numerohrs = 24;
		if ($turno == 3 && $hora_actual < '07:00')
			$numerohrs = 35;
		$cont = 0;
		$query = "SELECT TOP 1 * FROM tblEncabezadoBitacora WHERE NoMaquina = $maquina AND turno=$turno  AND Fecha >= DATEADD(hour, -$numerohrs, GETDATE()) ORDER BY IdEncabezadoBItacora desc";
		$res = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($res)) {
			array_push($array, ['id' => $row['IdEncabezadoBItacora'], 'turno' => $row['Turno']]);
			$cont++;
		}
		if ($cont > 0)
			echo json_encode($array);
		else {
			date_default_timezone_set('America/Mexico_City');
			$hora_actual = date('H:i');
			$fecha = 'CONVERT(date, GETDATE())';
			if ($turno == 3 && $hora_actual < '07:00')
				$fecha = 'DATEADD(day, -1, CONVERT(date, GETDATE()))';
			$query = "INSERT INTO tblEncabezadoBitacora(Turno,Fecha,NoMaquina) VALUES ('" . $turno . "',$fecha,'" . $_SESSION['idmaquina'] . "')";
			sqlsrv_query($conn, $query);
			
			$query = sqlsrv_query($conn, "SELECT @@identity AS id");
			if ($row = sqlsrv_fetch_array($query))
				$id = trim($row[0]);
			array_push($array, ['id' => $id, 'turno' => $turno]);
			echo json_encode($array);
		}
	}

// 	function turnoanterior(){
//     $Conecta = new ClassConexion();
//     $conn = $Conecta->conexion("TLX004MXDB");

//     $fecha = $_POST['fecha'];
//     $turno = $_POST['turno'];
//     // $usuario = $_POST['usuario'];
//     // $password = $_POST['password'];
//     $idmaquina = $_SESSION['idmaquina'];

// 	// Validar el usuario y la contraseña
//     $sql_validacion = "SELECT ContrasenaOpcional 
//                        FROM TLX032MXDB.dbo.tblEmpleados 
//                        WHERE NoEmp = ? AND ContrasenaOpcional = ?";
//     $params_validacion = [$usuario, $password];
//     $res_validacion = sqlsrv_query($conn, $sql_validacion, $params_validacion);
// 	// var_dump($usuario, $password);

    
// 	if ($res_validacion === false || sqlsrv_fetch_array($res_validacion) === false) {
// 			echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
// 			return;
// 		}


// 	// Realizar la consulta si la validación es exitosa
//     $sql = "SELECT tEB.IdEncabezadoBItacora, tEB.Fecha, tEB.Turno, tSM.NoSupervisor, TE.ContrasenaOpcional 
//             FROM tblEncabezadoBitacora tEB 
//             LEFT JOIN TLX004MXDB.dbo.tblSupervisorMaquina tSM ON tSM.NoMaquina = tEB.NoMaquina
//             LEFT JOIN TLX032MXDB.dbo.tblEmpleados TE ON TE.NoEmp = tSM.NoSupervisor 
//             WHERE Fecha = ? AND Turno = ? AND tEB.NoMaquina = ? AND TSM.NoSupervisor = ? AND ContrasenaOpcional = ?";
//     $params = [$fecha, $turno, $idmaquina, $usuario, $password]; 
//     $res = sqlsrv_query($conn, $sql, $params);

//     $array = array();
//     while ($row = sqlsrv_fetch_array($res)) {
//         array_push($array, ['id' => $row['IdEncabezadoBItacora'], 'turno' => $row['Turno']]);
//     }

	
//  if (empty($array)) {
//         echo json_encode(['error' => 'No se encontraron datos para los parámetros proporcionados']);
//         return;
//     }

//     echo json_encode($array);
// }


	function turnoanterior(){
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX004MXDB");
		$fecha = $_POST['fecha'];
		$turno = $_POST['turno'];
		$ult = "SELECT * FROM tblEncabezadoBitacora where Fecha = '$fecha' AND Turno = $turno AND NoMaquina = ".$_SESSION['idmaquina'];
		$res = sqlsrv_query($conn, $ult);
		$array = array();
		while ($row = sqlsrv_fetch_array($res)) {
			array_push($array, ['id' => $row['IdEncabezadoBItacora'], 'turno' => $row['Turno']]);
		}
		echo json_encode($array);
	}



	function datosemp()
	{
		$noemp = $_GET["noemp"];
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX032MXDB");
		$query = "SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto as departamento, tblPuestos.nombre as puesto FROM tblEmpleados
		INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento
		INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblEmpleados.Puesto WHERE NoEmp=$noemp";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["noemp" => $row[0], "nombre" => $row[1], "departamento" => $row[2], "puesto" => $row[3]]);
		}
		sqlsrv_close($conn);
		echo json_encode($array);
	}
	function guardacorrugados()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_POST['id'];
		$crecibidas = $_POST["crecibidas"];
		$crecibidas = $_POST["crecibidas"];
		$calmacen = $_POST["calmacen"];
		$cproducidas = $_POST["cproducidas"];
		$centregadas = $_POST["centregadas"];
		$claveproducto = $_POST["claveproducto"];
		$folio = $_POST["folio"];
		$id == '' ? $query = "INSERT INTO tblBitCorrugados(folio,crecibidas,calmacen,cproducidas,centregadas,claveproducto,fecha) VALUES ('" . $folio . "','" . $crecibidas . "','" . $calmacen . "','" . $cproducidas . "','" . $centregadas . "','" . $claveproducto . "','" . date("Y-m-d H:i:s") . "')" :
			$query = "UPDATE tblBitCorrugados SET crecibidas=$crecibidas,calmacen=$calmacen,cproducidas=$cproducidas,centregadas=$centregadas,claveproducto='$claveproducto' WHERE tblBitCorrugados.id=$id";
		$result = sqlsrv_query($conn, $query);
		echo $result === false ?  json_encode("Hay un problema") :  json_encode("La información se guardo con exito");
	}
	function tblcorrugados()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$folio = $_GET["folio"];
		$query = "SELECT * FROM tblBitCorrugados WHERE folio=" . $folio;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["folio" => $row[1], "crecibidas" => $row[2], "calmacen" => $row[3], "cproducidas" => $row[4], "centregadas" => $row[5], "claveproducto" => $row[6], "id" => $row[0]]);
		}
		echo json_encode($array);
	}
	function consultarcorrugado()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_GET["id"];
		$query = "SELECT * FROM tblBitCorrugados WHERE tblBitCorrugados.id=" . $id;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["folio" => $row[1], "crecibidas" => $row[2], "calmacen" => $row[3], "cproducidas" => $row[4], "centregadas" => $row[5], "claveproducto" => $row[6], "id" => $row[0]]);
		}
		echo json_encode($array);
	}

	function guardarasistencias()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$noemp = $_POST["noemp"];
		$folio = $_POST["folio"];
		$id = $_POST["id"];
		$id == '' ?
			$query = "INSERT INTO tblBitAsistencias(folio,noemp,fecha) VALUES ('" . $folio . "','" . $noemp . "','" . date("Y-m-d H:i:s") . "')" :
			$query = "UPDATE tblBitAsistencias SET noemp=$noemp WHERE id=$id";
		$result = sqlsrv_query($conn, $query);
		echo $result === false ? json_encode($noemp . $folio . $id) : json_encode("Se guardo la información correctamente");
	}
	function tblasistencias()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$folio = $_GET["folio"];
		$query = "SELECT tblBitAsistencias.folio,tblBitAsistencias.noemp,tblempleados.nombre,tblPuestos.nombre,tblBitAsistencias.id FROM tblBitAsistencias 
		INNER JOIN TLX032MXDB.dbo.tblempleados ON tblempleados.noemp=tblBitAsistencias.noemp
		INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblempleados.puesto WHERE folio=" . $folio;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["folio" => $row[0], "noemp" => $row[1], "nombre" => $row[2], "puesto" => $row[3], "id" => $row[4]]);
		}
		echo json_encode($array);
	}
	function consultarasistencia()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_GET["id"];
		$query = "SELECT tblBitAsistencias.folio,tblBitAsistencias.noemp,tblempleados.nombre,tblPuestos.nombre,tblBitAsistencias.id FROM tblBitAsistencias 
		INNER JOIN TLX032MXDB.dbo.tblempleados ON tblempleados.noemp=tblBitAsistencias.noemp
		INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblempleados.puesto WHERE tblBitAsistencias.id=" . $id;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["folio" => $row[0], "noemp" => $row[1], "nombre" => $row[2], "puesto" => $row[3], "id" => $row[4]]);
		}
		echo json_encode($array);
	}

	function guardarpresentacion()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$presentacion = $_POST["presentacion"];
		$hora = $_POST["hora"];
		$real = $_POST["real"];
		$golpes = $_POST["golpes"];
		$folio = $_POST["folio"];
		$query = "INSERT INTO tblBitPresentacion(folio,presentacion,hora,realac,golpes,fecha) VALUES ('" . $folio . "','" . $presentacion . "','" . $hora . "','" . $real . "','" . $golpes . "','" . date("Y-m-d H:i:s") . "')";
		sqlsrv_query($conn, $query);
		echo json_encode("ok");
	}
	function tblpresentaciones()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$folio = $_GET["folio"];
		$query = "SELECT * FROM tblBitPresentacion INNER JOIN tblBitPresentaciones ON tblBitPresentaciones.clave= tblBitPresentacion.presentacion WHERE folio=" . $folio . " ORDER BY tblBitPresentacion.presentacion,tblBitPresentacion.hora";
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["folio" => $row[1], "presentacion" => $row[2], "hora" => $row[3]->format("H:i:s"), "real" => $row[4], "acumulado" => $row[5], "std" => $row[6], "golpes" => $row['golpes'], "factor" => $row['factor'], "id" => $row[0]]);
		}
		echo json_encode($array);
	}
	function consultarpresentacion()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_GET["id"];
		$query = "SELECT * FROM tblBitPresentacion INNER JOIN tblBitPresentaciones ON tblBitPresentaciones.clave= tblBitPresentacion.presentacion WHERE tblBitPresentacion.id=" . $id . "";
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["folio" => $row[1], "presentacion" => $row[2], "hora" => $row[3]->format("H:i:s"), "real" => $row[4], "acumulado" => $row[5], "std" => $row[6], "golpes" => $row['golpes'], "factor" => $row['factor'], "id" => $row[0]]);
		}
		echo json_encode($array);
	}
	function editarpresentacion()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$presentacion = $_POST["presentacion"];
		$hora = $_POST["hora"];
		$real = $_POST["real"];
		$golpes = $_POST["golpes"];
		$id = $_POST["id"];
		$query = "UPDATE tblBitPresentacion SET presentacion=$presentacion,hora='" . $hora . "',realac=$real,golpes= $golpes WHERE tblBitPresentacion.id=$id";
		sqlsrv_query($conn, $query);
		echo json_encode("ok");
	}
	function guardarctrltiempos()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$horainicio = $_POST["horainicio"];
		$horafinal = $_POST["horafinal"];
		$operacion = $_POST["operacion"];
		$electrico = $_POST["electrico"];
		$mecanico = $_POST["mecanico"];
		$materias = $_POST["materias"];
		$grado = $_POST["grado"];
		$prev = $_POST["prev"];
		$servicios = $_POST["servicios"];
		$subtotal = $operacion + $electrico + $mecanico + $materias + $grado + $prev + $servicios;
		$seccion = $_POST["seccion"];
		$modulo = $_POST["modulo"];
		$motivo = $_POST["motivo"];
		$correccion = $_POST["correccion"];
		$folio = $_POST["folio"];
		$query = "INSERT INTO tblBitCtrltiempos(folio,horainicio,horafinal,operacion,electrico,mecanico,materias,grado,prev,servicios,subtotal,seccion,modulo,motivo,correccion,fecha) 
		VALUES ('" . $folio . "','" . $horainicio . "','" . $horafinal . "','" . $operacion . "','" . $electrico . "','" . $mecanico . "','" . $materias . "','" . $grado . "','" . $prev . "'
		,'" . $servicios . "','" . $subtotal . "','" . $seccion . "','" . $modulo . "','" . $motivo . "','" . $correccion . "','" . date("Y-m-d H:i:s") . "')";
		sqlsrv_query($conn, $query);
		echo json_encode("ok");
	}
	function tblctrltiempos()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$folio = $_GET["folio"];
		$query = "SELECT *,tblProduccionSecciones.Seccion,tblProduccionModulos.Modulos,
		(SELECT COUNT(*) FROM tblBitSanitizacionEnc WHERE tblBitSanitizacionEnc.idparo=tblBitCtrltiempos.id) as numsan FROM tblBitCtrltiempos 
		INNER JOIN tblProduccionSecciones on tblProduccionSecciones.idSeccion= tblBitCtrltiempos.seccion
		INNER JOIN tblProduccionModulos ON tblProduccionModulos.idModulos=tblBitCtrltiempos.modulo WHERE folio=" . $folio;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"folio" => $row[1],
				"horainicio" => $row[2]->format("H:i:s"),
				"horafinal" => $row[3]->format("H:i:s"),
				"operacion" => $row[4],
				"electrico" => $row[5],
				"mecanico" => $row[6],
				"materias" => $row[7],
				"grado" => $row[8],
				"prev" => $row[9],
				"servicios" => $row[10],
				"subtotal" => $row[11],
				"seccion" => $row["Seccion"],
				"modulo" => $row["Modulos"],
				"motivo" => $row[14],
				"correccion" => $row[15],
				"numsan" => $row['numsan'],
				"id" => $row[0]
			]);
		}
		echo json_encode($array);
	}
	function consultarctrltiempos()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_GET["id"];
		$query = "SELECT * FROM tblBitCtrltiempos WHERE id=" . $id;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"folio" => $row[1],
				"horainicio" => $row[2]->format("H:i:s"),
				"horafinal" => $row[3]->format("H:i:s"),
				"operacion" => $row[4],
				"electrico" => $row[5],
				"mecanico" => $row[6],
				"materias" => $row[7],
				"grado" => $row[8],
				"prev" => $row[9],
				"servicios" => $row[10],
				"subtotal" => $row[11],
				"seccion" => $row[12],
				"modulo" => $row[13],
				"motivo" => $row[14],
				"correccion" => $row[15],
				"id" => $row[0]
			]);
		}
		echo json_encode($array);
	}
	function editarctrltiempos()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$horainicio = $_POST["horainicio"];
		$horafinal = $_POST["horafinal"];
		$operacion = $_POST["operacion"];
		$electrico = $_POST["electrico"];
		$mecanico = $_POST["mecanico"];
		$materias = $_POST["materias"];
		$grado = $_POST["grado"];
		$prev = $_POST["prev"];
		$servicios = $_POST["servicios"];
		$subtotal = $operacion + $electrico + $mecanico + $materias + $grado + $prev + $servicios;
		$seccion = $_POST["seccion"];
		$modulo = $_POST["modulo"];
		$motivo = $_POST["motivo"];
		$correccion = $_POST["correccion"];
		$id = $_POST["id"];
		$query = "UPDATE tblBitCtrltiempos SET horainicio='" . $horainicio . "',horafinal='" . $horafinal . "',operacion=$operacion,electrico=$electrico,mecanico=$mecanico,materias=$materias,
		grado=$grado,prev=$prev,servicios=$servicios,subtotal=$subtotal,seccion=$seccion,modulo=$modulo,motivo='" . $motivo . "',correccion='" . $correccion . "' WHERE tblBitCtrltiempos.id=$id";
		sqlsrv_query($conn, $query);
		echo json_encode("ok");
	}
	function saveSanitizacion()
	{
		$Conecta = new ClassConexion();
		$folio = $_POST["folio"];
		$motivo = $_POST["motivo"];
		$tiempo = $_POST["tiempo"];
		$usuario = $_POST["usuario"];
		$password = $_POST["password"];
		$conn = $Conecta->conexion("TLX032MXDB");
		$sql = "SELECT NoEmp,Nombre FROM tblEmpleados WHERE NoEmp = ? AND ContrasenaOpcional = ?";
		$params = array($usuario, $password);
		$stmt = sqlsrv_query($conn, $sql, $params);
		if ($stmt === false) {
			http_response_code(500);
			die(print_r(sqlsrv_errors(), true));
		}
		if (sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
		$conn = $Conecta->conexion("TLX002MXDB");
		$empleados = json_decode($_POST['empleados'], true);
		$this->saveEmpSanitizacion($folio, $empleados);
		$query = "INSERT INTO tblBitSanitizacionEnc(idparo,motivo,tiempo,libero) VALUES (?, ?, ?, ?)";
		$result = sqlsrv_query($conn, $query, array($folio, $motivo, $tiempo, $usuario));
		$result === false ? http_response_code(500) : http_response_code(200);
		}else{
			http_response_code(201);
		}
	}
	function saveEmpSanitizacion($folio, $dataemp)
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		foreach ($dataemp as $employee) {
			$emp = $employee['id'];
			$sql = "INSERT INTO tblBitSanitizacionEmp (folio,NoEmp) VALUES (?, ?)";
			$params = array($folio, $emp);
			$stmt = sqlsrv_query($conn, $sql, $params);
			if ($stmt === false) {
				http_response_code(500);
				die(print_r(sqlsrv_errors(), true));
			}
		}
	}

	function guardarcomentarios()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$folio = $_POST["folio"];
		$seguridad = $_POST["seguridad"];
		$calidad = $_POST["calidad"];
		$oyl = $_POST["oyl"];
		$pendientes = $_POST["pendientes"];
		$otros = $_POST["otros"];
		$query = "INSERT INTO tblBitComentarios(folio,seguridad,calidad,oyl,pendientes,otros,fecha) 
		VALUES ('" . $folio . "','" . $seguridad . "','" . $calidad . "','" . $oyl . "','" . $pendientes . "','" . $otros . "','" . date("Y-m-d H:i:s") . "')";
		sqlsrv_query($conn, $query);
		echo json_encode("ok");
	}
	function tblcomentarios()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$folio = $_GET["folio"];
		$query = "SELECT * FROM tblBitComentarios WHERE folio=" . $folio;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"folio" => $row[1],
				"seguridad" => $row[2],
				"calidad" => $row[3],
				"oyl" => $row[4],
				"pendientes" => $row[5],
				"otros" => $row[6],
				"id" => $row[0]
			]);
		}
		echo json_encode($array);
	}
	function consultarcomentarios()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_GET["id"];
		$query = "SELECT * FROM tblBitComentarios WHERE id=" . $id;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"folio" => $row[1],
				"seguridad" => $row[2],
				"calidad" => $row[3],
				"oyl" => $row[4],
				"pendientes" => $row[5],
				"otros" => $row[6],
				"id" => $row[0]
			]);
		}
		echo json_encode($array);
	}
	function editarcomentarios()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_POST["id"];
		$seguridad = $_POST["seguridad"];
		$calidad = $_POST["calidad"];
		$oyl = $_POST["oyl"];
		$pendientes = $_POST["pendientes"];
		$otros = $_POST["otros"];
		$query = "UPDATE tblBitComentarios SET seguridad='" . $seguridad . "',calidad='" . $calidad . "',oyl='" . $oyl . "',pendientes='" . $pendientes . "',otros='" . $otros . "'
		WHERE id=$id";
		sqlsrv_query($conn, $query);
		echo json_encode("ok");
	}
	function saveRill()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$claverill = $_POST["claverill"];
		$claseril = $_POST["claseril"];
		$clmaterialrilaseril = $_POST["clmaterialrilaseril"];
		$noempril = $_POST["noempril"];
		$loteril = $_POST["loteril"];
		$foliovalesril = $_POST["foliovalesril"];
		$horaril = $_POST["horaril"];
		$materialprueba = $_POST["materialprueba"];
		$foliomanual = $_POST["foliovalemanual"];
		$query = "INSERT INTO tblRillEnc (clave,clase,material,noemp,lote,foliovalesril,horaril,materialprueba,foliomanual,sessionmaquina) VALUES (?,?,?,?,?,?,?,?,?,?)";
		$result = sqlsrv_query($conn, $query, array($claverill, $claseril, $clmaterialrilaseril, $noempril, $loteril, $foliovalesril, $horaril, $materialprueba, $foliomanual, $_SESSION['idmaquina']));
		$result == false ? http_response_code(500) : http_response_code(200);
	}
	function tblRillEnc()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$query = "SELECT TOP 50 tblRillEnc.*,tblValeEMateriales.NombreMaterial,tblempleados.Nombre as empleadonombre,tblValeEEnc.foliocons as foliocons FROM tblRillEnc
		LEFT JOIN tblValeEEnc ON tblValeEEnc.id = tblRillEnc.foliovalesril
		LEFT JOIN tblValeEMateriales On tblValeEMateriales.NoMaterial = tblRillEnc.material
		INNER JOIN TLX032MXDB.dbo.tblempleados On tblEmpleados.NoEmp = tblRillEnc.noemp 
		WHERE tblValeEEnc.maquina = " . $_SESSION['idmaquina'] . " OR tblRillEnc.sessionmaquina =  " . $_SESSION['idmaquina'] . " ORDER BY tblRillEnc.id DESC";
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"id" => $row[0],
				"clave" => $row[1],
				"clase" => $row[2],
				"material" => $row[3],
				"noemp" => $row[4],
				"lote" => $row[5],
				"foliovalesril" => $row[6],
				"horaril" => $row[7]->format('H:i:s'),
				"fecha" => $row[8]->format('Y-m-d H:i:s'),
				"materialnombre" => $row['NombreMaterial'],
				"empleadonombre" => $row['empleadonombre'],
				"materialprueba" => $row['materialprueba'],
				"foliovalemanual" => $row['foliomanual'],
				"foliovalecons" => $row['foliocons'],
				"foliovaleconsmaq" => $_SESSION['usuario'] . ' - ' . $row['foliocons']
			]);
		}
		echo json_encode($array);
	}


	function tblcalidad()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$folio = $_GET["folio"];
		$query = "SELECT * FROM tblBitCalidad WHERE folio=" . $folio;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"id" => $row['id'],
				"folio" => $row['folio'],
				"inspeccionados" => $row['inspeccionados'],
				"sd" => $row['sd'],
				"ql" => $row['ql'],
				"observacion" => $row['observacion']
			]);
		}
		echo json_encode($array);
	}
	function savecalidadsd()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_POST["id"];
		$folio = $_POST["folio"];
		$insp = $_POST["insp"];
		$sd = $_POST["sd"];
		$ql = $_POST["ql"];
		$obs = $_POST["obs"];
		$id == '' ? $query = "INSERT INTO tblBitCalidad(folio,inspeccionados,sd,ql,observacion) VALUES (?, ? , ? , ?, ?)" :
			$query = "UPDATE tblBitCalidad SET folio = ?, inspeccionados= ?, sd= ?, ql= ? ,observacion =? WHERE id=$id";
		$result = sqlsrv_query($conn, $query, array($folio, $insp, $sd, $ql, $obs));
		if ($result === false) {
			if (($errors = sqlsrv_errors()) != null) {
				foreach ($errors as $error) {
					echo "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
					echo "code: " . $error['code'] . "<br />";
					echo "message: " . $error['message'] . "<br />";
				}
			}
		}
		echo json_encode("Se guardo la información correctamente");
	}
	function consultarCalidadxID()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$id = $_GET["id"];
		$query = "SELECT * FROM tblBitCalidad WHERE id=" . $id;
		$array = array();
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"id" => $row['id'],
				"folio" => $row['folio'],
				"inspeccionados" => $row['inspeccionados'],
				"sd" => $row['sd'],
				"ql" => $row['ql'],
				"observacion" => $row['observacion']
			]);
		}
		echo json_encode($array);
	}

	function ObtenerdatosPlanProduccion(){
		$idMaquina = $_SESSION['idmaquina'];
		$conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $sql = "SELECT tblProduccionPlan.*,tblcla.Descripcion_Articulo AS descripcion,
                tblcla.Producto, tblppr.Producto AS nombreProducto, tblcla.Etapa, tblpet.Etapa AS nombreEtapa,
                tblm.NombreMaquina 
                FROM tblProduccionPlan
                INNER JOIN TLX009MXDB.dbo.tblMaquinas tblm ON tblm.NoMaquina = tblProduccionPlan.maquina
                LEFT JOIN TLX002MXDB.dbo.tblValeEClaves tblcla ON tblcla.NoClave = tblProduccionPlan.clave
                LEFT JOIN TLX002MXDB.dbo.tblProduccionProductos tblppr ON tblppr.idProducto = tblcla.Producto
                LEFT JOIN TLX002MXDB.dbo.tblProduccionEtapas tblpet ON tblpet.idEtapa = tblcla.Etapa
				WHERE tblProduccionPlan.maquina = $idMaquina 
				AND CONVERT(DATE, tblProduccionPlan.fechasave) = CONVERT(DATE, GETDATE())
                ORDER BY tblProduccionPlan.id DESC";
        $array = array();
        $result = sqlsrv_query($conn, $sql);
        while ($row = sqlsrv_fetch_array($result))
            $array[] = $row;
        if ($result === false) {
            http_response_code(500);
            echo json_encode('error');
        } else {
            http_response_code(200);
            echo json_encode($array);
        }
	}

	function saveCov(){
		$pesos = $_POST["pesos"];
		$cov = $_POST["cov"];
		$promedio = $_POST["promedio"];
		$min = $_POST["min"];
		$max = $_POST["max"];
		$folio = $_POST["folio"];

		$array = array(
			$pesos,
			$cov,
			$promedio,
			$min,
			$max,
			$folio
		);

		$conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');

		$query = "INSERT INTO tblPanalCOV(pesos, cov, promedio, min, max, folio) VALUES(?, ?, ?, ?, ?, ?)";
		$result = sqlsrv_query($conn, $query, $array);
		if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
	}

	function updateCov(){
		$id = $_POST["id"];
		$pesos = $_POST["pesos"];
		$cov = $_POST["cov"];
		$promedio = $_POST["promedio"];
		$min = $_POST["min"];
		$max = $_POST["max"];

		$conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');

		$array = array(
			$pesos,
			$cov,
			$promedio,
			$min,
			$max,
			$id
		);

		$query = "UPDATE tblPanalCOV SET pesos=?, cov=?, promedio=?, min=?, max=? WHERE id=?";
		$result = sqlsrv_query($conn, $query,  $array );
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
	}

	function mostrarCov(){
		$folio = $_GET["folio"];
		$conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
		$query = "SELECT * FROM tblPanalCOV WHERE folio = $folio";
		$result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)){
            $array[] = $row;
        }
        sqlsrv_close($conn);
        echo json_encode($array); 
	}
}
if (isset($_GET["saveTrazabilidad"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->saveTrazabilidad();
} else if (isset($_GET["tblTrazabilidadEnc"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->tblTrazabilidadEnc();
} else if (isset($_GET["saveNoConformidad"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->saveNoConformidad();
} else if (isset($_GET["tblNoConformidad"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->tblNoConformidad();
} else if (isset($_GET["updateNoConformidad"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->updateNoConformidad();
} else if (isset($_GET["getAllDataConf"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->getAllDataConf();
} else if (isset($_GET["getDataNow"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->getDataNow($_SESSION['idmaquina']);
} else if (isset($_GET["getDataNowMult"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->getDataNow($_GET['maquina']);
} else if (isset($_GET["GetDataMonitor"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->GetDataMonitor($_SESSION['idmaquina']);
} else if (isset($_GET["GetDataMonitorMult"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->GetDataMonitor($_GET['maquina']);
} else if (isset($_GET["abreturno"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->abreturno();
} else if (isset($_GET["datosemp"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->datosemp();
} else if (isset($_GET["guardacorrugados"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->guardacorrugados();
} else if (isset($_GET["tblcorrugados"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->tblcorrugados();
} else if (isset($_GET["guardarasistencias"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->guardarasistencias();
} else if (isset($_GET["tblasistencias"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->tblasistencias();
} else if (isset($_GET["guardarpresentacion"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->guardarpresentacion();
} else if (isset($_GET["tblpresentaciones"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->tblpresentaciones();
} else if (isset($_GET["guardarctrltiempos"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->guardarctrltiempos();
} else if (isset($_GET["tblctrltiempos"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->tblctrltiempos();
} else if (isset($_GET["guardarcomentarios"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->guardarcomentarios();
} else if (isset($_GET["tblcomentarios"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->tblcomentarios();
} else if (isset($_GET["consultarasistencia"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->consultarasistencia();
} else if (isset($_GET["consultarcorrugado"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->consultarcorrugado();
} else if (isset($_GET["consultarpresentacion"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->consultarpresentacion();
} else if (isset($_GET["editarpresentacion"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->editarpresentacion();
} else if (isset($_GET["consultarctrltiempos"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->consultarctrltiempos();
} else if (isset($_GET["editarctrltiempos"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->editarctrltiempos();
} else if (isset($_GET["consultarcomentarios"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->consultarcomentarios();
} else if (isset($_GET["editarcomentarios"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->editarcomentarios();
} else if (isset($_GET["saveRill"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->saveRill();
} else if (isset($_GET["tblRillEnc"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->tblRillEnc();
} else if (isset($_GET["tblcalidad"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->tblcalidad();
} else if (isset($_GET["savecalidadsd"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->savecalidadsd();
} else if (isset($_GET["consultarCalidadxID"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->consultarCalidadxID();
} else if (isset($_GET["turnoanterior"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->turnoanterior();
} else if (isset($_GET["saveSanitizacion"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->saveSanitizacion();
} else if (isset($_GET["ObtenerdatosPlanProduccion"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->ObtenerdatosPlanProduccion();
} else if (isset($_GET["saveCov"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->saveCov();
} else if (isset($_GET["updateCov"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->updateCov();
}else if (isset($_GET["mostrarCov"])) {
	$BitacoraElectronica = new BitacoraElectronica();
	$BitacoraElectronica->mostrarCov();
}
