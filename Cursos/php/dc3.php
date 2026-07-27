<?php
require_once '../../conexion.php';
Class Cursos{
	function tblrptdc3(){
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX035MXDB");
		$cursos=$_POST["cursos"];
		$fechai=$_POST["fechai"];
		$fechaf=$_POST["fechaf"];
		$query = "SELECT tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura,tblCursos.NombreCurso,tblEncabezadoCapturaCapacitacion.FechaInicial,tblEncabezadoCapturaCapacitacion.FechaFinal FROM tblEncabezadoCapturaCapacitacion
		INNER JOIN tblCursos ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso WHERE tblCursos.IdCurso=$cursos AND tblEncabezadoCapturaCapacitacion.FechaInicial BETWEEN '$fechai' AND '$fechaf';";
		$result = sqlsrv_query($conn,$query);
		$array=array();
		while($row= sqlsrv_fetch_array($result)){
			array_push($array,["idcurso"=>$row[0],"nombrecurso"=>$row[1],"fechainicial"=>$row[2]->format('Y-m-d'),"fechafinal"=>$row[3]->format('Y-m-d')]);
		}
		echo json_encode($array);
	}
}

if (isset($_GET["tblrptdc3"])) {
	$cursos=new Cursos();
	$cursos->tblrptdc3();
}
?>