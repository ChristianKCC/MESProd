<?php
require_once "../../conexion.php";
require_once "../../Session/seguridad.php";
Class Monitor{
	function datosinicio($query){		
		$conexion = new ClassConexion();
		$conn= $conexion->conexioniap();
			$hora = $Opera =  $merma = array();
			$result=sqlsrv_query($conn,$query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row["Rechazos"]==0 || $row["Cortes"] == 0 ? $resultado=0 : $resultado = ($row["Rechazos"]/$row["Cortes"])*100;
				array_push($hora, $row["t_stamp"]->format("H:i:s"));
				array_push($Opera, ($row["OperMaq"]*10));
				array_push($merma, number_format($resultado,2));
			}
		   $respuesta = [ "hora" => $hora,"datos" => $Opera,"merma" => $merma];
		   echo json_encode($respuesta);
		
	}

    
	function informacionmaquina($query){		
		$conexion = new ClassConexion();
		$conn= $conexion->conexioniap();
		$cortes = $rechazos =  $merma =  $estado =  $velocidad = array();
			$result=sqlsrv_query($conn,$query);
			while ($row = sqlsrv_fetch_array($result)) {
				$row["Rechazos"]==0 || $row["Cortes"] == 0 ? $resultado=0 : $resultado = ($row["Rechazos"]/$row["Cortes"])*100;
				array_push($cortes, $row['Cortes']);
				array_push($rechazos, $row['Rechazos']);
				array_push($estado, ($row["OperMaq"]*10));
				array_push($velocidad, ($row["Velocidad"]));
				array_push($merma, number_format($resultado,2));
			}
		   $respuesta = ["cortes" => $cortes,"rechazos" => $rechazos,"estado" => $estado,"merma" => $merma,"velocidad" => $velocidad];
		   echo json_encode($respuesta);
		
	}



}
?>