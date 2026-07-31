<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';

class OpcMonitor
{
	function getDataNow()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX004MXDB");
		$maquina = $_GET['maquina'];
		$query = "SELECT TOP 1 Turno, MilesMetrosHora, Metros, Rechazos, TiempoParoMin, TiempoEnhebrandoMin,
		TiempoCorriendoMin, MermaMaquina, TiempoPerdido, ParosMaquina, PorcentajeTiempoPerdido, CorriendoParada, VelocidadActual,
		FechaHora
		FROM tblMXPRBitacoraHook WHERE NoMaquina=$maquina ORDER BY Id DESC";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				'turno' => $row['Turno'],
				'velocidad' => round($row['MilesMetrosHora'], 2),
				'metros' => round($row['Metros'], 2),
				'rechazos' => round($row['Rechazos'], 2),
				'tiempoParoMin' => round($row['TiempoParoMin'], 2),
				'tiempoEnhebrandoMin' => round($row['TiempoEnhebrandoMin'], 2),
				'tiempoCorriendoMin' => round($row['TiempoCorriendoMin'], 2),
				'merma' => round($row['MermaMaquina'], 2),
				'tiempoPerdido' => round($row['TiempoPerdido'], 2),
				'paros' => round($row['ParosMaquina'], 2),
				'porcentajeTiempoPerdido' => round($row['PorcentajeTiempoPerdido'], 2),
				'corriendoParada' => $row['CorriendoParada'],
				'velocidadActual' => round($row['VelocidadActual'], 2),
				'fechaHora' => $row['FechaHora']->format('H:i:s')
			]);
		}
		echo json_encode($array);
		sqlsrv_close($conn);
	}

	// $numhrs = cuantas horas hacia atras (se multiplica por 180 porque
	// cada registro es cada ~20s, y 3600s/20s = 180 registros por hora)
	function getDataMonitor()
	{
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX004MXDB");
		$maquina = $_GET['maquina'];
		$numregs = (int) $_GET['numhrs'] * 180;
		$hora = $operacion = $merma = $velocidad = array();
		$query = "SELECT * FROM (SELECT TOP ($numregs) * FROM tblMXPRBitacoraHook WHERE NoMaquina=$maquina ORDER BY Id DESC) as T
			ORDER BY Id ASC;";
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($hora, $row["FechaHora"]->format("H:i:s"));
			array_push($operacion, $row["CorriendoParada"] * 10);
			array_push($merma, number_format($row["MermaMaquina"], 2));
			array_push($velocidad, number_format($row["VelocidadActual"], 2));
		}
		$respuesta = ["hora" => $hora, "operacion" => $operacion, "merma" => $merma, "velocidad" => $velocidad];
		echo json_encode($respuesta);
		sqlsrv_close($conn);
	}
}

if (isset($_GET["getDataNow"])) {
	$OpcMonitor = new OpcMonitor();
	$OpcMonitor->getDataNow();
} else if (isset($_GET["getDataMonitor"])) {
	$OpcMonitor = new OpcMonitor();
	$OpcMonitor->getDataMonitor();
}