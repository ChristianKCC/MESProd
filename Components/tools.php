<?php
class Herramientas
{
	// Funcion oara concatenar condicones en busquedas de query siendo adn / or / where
    function getmultislcfiltro($query,$arreglo,$inicia=0)
	{
		$addwhere = '';
		$andor = '';
		$i = 0;
		foreach ($arreglo as $key => $array) {
			if ($i == 0) $andor = 'AND (';
			else $andor = 'OR';
			if ($i > 0 or $inicia != 0)
				$addwhere .= $andor . " " . $query . " = " . $array . " ";
			else
				$addwhere .= "WHERE (" . $query . " = " . $array . " ";
			$i++;
		}
		$addwhere .= ") ";
		return $addwhere;
	}

	// Funcin para crear querys segun filtros de texto con LIKE
    function gettextfiltro($campo1,$campo2,$buscar,$inicia=0)
	{
		$addwhere = "";
		$andor = "AND (";
		$campo2 == "" ? $campo2 = $campo1 : $campo2 = $campo2;
		if ($inicia != 0)
			$addwhere .= $andor . " " . $campo1 . " LIKE '%" . $buscar . "%' OR " . $campo2 . " LIKE '%" . $buscar . "%'";
		else
			$addwhere .= "WHERE (" . $campo1 . " LIKE '%" . $buscar . "%' OR " . $campo2 . " LIKE '%" . $buscar . "%'";
		$addwhere .= ") ";
		return $addwhere;
	}

	// Funcion de retorno de colores de forma aleatoria con mt_rand
	function color_rand()
	{
		return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
	}

	// Funcion para llenado de datos
	public function llnarslc($query, $database)
	{
			$Conecta = new ClassConexion();
			$conn = $Conecta->conexion($database);
			$result = sqlsrv_query($conn, $query);
			$array = array();
			while ($row = sqlsrv_fetch_array($result)) {
				array_push($array, ["id" => $row[0], "nombre" => $row[1]]);
			}
			echo json_encode($array);
			sqlsrv_close($conn);

	}
}
?>