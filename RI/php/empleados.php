<?php
require_once ('../../conexion.php');
require_once ('../../Components/tools.php');
class Empleados
{
	function consultaempleados()
	{
		$Herramientas = new Herramientas();
		$ClassConexion = new ClassConexion();
		$conn = $ClassConexion->conexion("TLX032MXDB");
		$addquery = "";
		$array = array();
		isset($_POST["libre"]) ? $addquery .= $Herramientas->gettextfiltro("tblEmpleados.Nombre", "tblEmpleados.NoEmp", $_POST['libre']) : NULL;
		isset($_POST["deps"]) ? $addquery .= $Herramientas->getmultislcfiltro("TLX009MXDB.dbo.tblDepartamentos.NoDepto", $_POST["deps"], 1) : NULL;
		isset($_POST["Puestos"]) ? $addquery .= $Herramientas->getmultislcfiltro("TLX009MXDB.dbo.tblPuestos.id", $_POST["Puestos"], 1) : NULL;
		isset($_POST["nivestu"]) ? $addquery .= $Herramientas->getmultislcfiltro("TLX009MXDB.dbo.tblRINivelMaxEstudios2016.IdClvNivelEstudios", $_POST["nivestu"], 1) : NULL;
		$query = "SELECT tblEmpleados.*, tblDepartamentos.NombreDepto as NomDep , tbldep2.NombreDepto as NomDepreal, tblPuestos.nombre as NomPuesto,
		tblRIEntidadFederativa.DescEntidadFederativa as Estado,tblRIDocumentoProbatorio.DescDocProbatorio as docapro FROM tblEmpleados 
		LEFT join TLX009MXDB.dbo.tblDepartamentos on tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento 
		LEFT join TLX009MXDB.dbo.tblDepartamentos AS tbldep2 on tbldep2.NoDepto=tblEmpleados.NoDeptoReal 
		LEFT join TLX009MXDB.dbo.tblPuestos on tblPuestos.id=tblEmpleados.Puesto 
		LEFT join TLX009MXDB.dbo.tblRIEntidadFederativa on tblRIEntidadFederativa.IdClvEntidad=tblEmpleados.IdClvEntidad 
		LEFT join TLX009MXDB.dbo.tblRIDocumentoProbatorio on tblRIDocumentoProbatorio.IdClvDocProbatorio=tblEmpleados.IdClvDocProbatorio 
		LEFT join TLX009MXDB.dbo.tblEstadoCivil on tblEstadoCivil.IdClvEstadoCivil=tblEmpleados.IdClvEstadoCivil 
		LEFT join TLX009MXDB.dbo.tblRINivelMaxEstudios2016 on tblRINivelMaxEstudios2016.IdClvNivelEstudios=tblEmpleados.IdClvNivelEstudios 
		LEFT join TLX009MXDB.dbo.tblRIOcupaciones on tblRIOcupaciones.IdClvOcupaciones=tblEmpleados.IdClvOcupaciones " . $addquery . " ORDER BY tblEmpleados.Noemp ASC";
		//  echo $query;
		$result = sqlsrv_query($conn, $query);
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, [
				"NoEmp" => $row["NoEmp"], "Nombres" => $row["Nombres"], "ApellidoPaterno" => $row["ApellidoPaterno"], "ApellidoMaterno" => $row["ApellidoMaterno"],
				"NomDep" => $row["NomDep"], "NomDepreal" => $row["NomDepreal"], "NomPuesto" => $row["NomPuesto"], "Bajas" => $row["Bajas"]
			]);
		}
		echo json_encode($array);
	}
	
	function grafcursos()
	{
		$Herramientas = new Herramientas();
		$ClassConexion = new ClassConexion();
		$conn = $ClassConexion->conexion("TLX035MXDB");
		$ibm = $_GET['ibm'];
		$querygrf1 = "SELECT COUNT(*),(SELECT COUNT(*) FROM tblSubEncabCapturaCapacitacion WHERE (Contestado=1 AND NoEmp=$ibm)) FROM tblSubEncabCapturaCapacitacion WHERE NoEmp=$ibm";
		$resultgrf = sqlsrv_query($conn, $querygrf1);
		while ($fila = sqlsrv_fetch_array($resultgrf)) {
			$datos = array("asignadas" => $fila[0], "terminadas" => $fila[1]);
			$etiquetas = ["Asignados", "Asistencias"];
			$datosVentas = [$fila[0], $fila[1]];
		}
		$colores = [$Herramientas->color_rand(), $Herramientas->color_rand()];
		$respuesta = ["etiquetas" => $etiquetas, "datos" => $datosVentas, "colores" => $colores];
		echo json_encode($respuesta);
	}
	function consultaasistencias()
	{
		$ClassConexion = new ClassConexion();
		$conn = $ClassConexion->conexion("TLX001MXDB");
		$noemp = $_GET["noemp"];
		$query = "SELECT TOP 10 pin,tblEmpleados.Nombre,event_time,temperature,dept_name FROM acc_transaction
		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=pin WHERE NoEmp=$noemp";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["ibm" => $row[0], "nombre" => $row[1], "fecha" => $row[2]->format('Y-m-d H:i:s')]);
		}
		echo json_encode($array);
	}
	function consultasEnfermeria()
	{
		$ClassConexion = new ClassConexion();
		$conn = $ClassConexion->conexion("TLX002MXDB");
		$noemp = $_GET["noemp"];
		$query = "SELECT tblEnfermeriaConsultas.id,tblEmpleados.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblPuestos.nombre as puesto,edad,antiguedad,
		    tratamiento,observacion,tblEnfermeriaEquipos.equipomedico,tblEnfermeriaEnfermedades.enfermedad,tblEnfermeriaTipoConsult.tipoconsulta,fecharevision
			 FROM tblEnfermeriaConsultas
			  INNER JOIN tblEnfermeriaEquipos ON tblEnfermeriaEquipos.id = tblEnfermeriaConsultas.tipoaparato
			  INNER JOIN tblEnfermeriaEnfermedades ON tblEnfermeriaEnfermedades.id = tblEnfermeriaConsultas.tipoenfermedad
			  INNER JOIN tblEnfermeriaTipoConsult ON tblEnfermeriaTipoConsult.id = tblEnfermeriaConsultas.tipoconsulta
			  INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEnfermeriaConsultas.departamento
			  INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEnfermeriaConsultas.puesto
			  INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblEnfermeriaConsultas.noemp WHERE tblEnfermeriaConsultas.noemp=$noemp";
		$result = sqlsrv_query($conn, $query);
		$array = array();
		while ($row = sqlsrv_fetch_array($result)) {
			array_push($array, ["id" => $row['id'], "noemp" => $row['noemp'], "Nombre" => $row['Nombre'], "NombreDepto" => $row['NombreDepto'],
			 "edad" => $row['edad'], "tratamiento" => $row['tratamiento'], "observacion" => $row['observacion'],
			 "equipomedico" => $row['equipomedico'], "enfermedad" => $row['enfermedad'], "fecha" => $row['fecharevision']->format('Y-m-d H:i:s')]);
		}
		echo json_encode($array);
	}
	
	function consultaallinfempleados()
	{
		$ibm = $_POST['ibm'];
		$ClassConexion = new ClassConexion();
		$conn = $ClassConexion->conexion("TLX032MXDB");
		$query = "SELECT * FROM tblEmpleados WHERE NoEmp = $ibm";
		$resultado = sqlsrv_query($conn, $query);
		$datos = array();
		while ($row = sqlsrv_fetch_array($resultado)) {
			$FechaIngreso = $row['FechaIngreso'] == NULL ? $FechaIngreso = '' : $FechaIngreso = $row['FechaIngreso']->format('Y-m-d');
			$FechaAntiguedad = $row['FechaAntiguedad'] == NULL ? $FechaAntiguedad = '' : $FechaAntiguedad = $row['FechaAntiguedad']->format('Y-m-d');
			$FechaBaja = $row['FechaBaja'] == NULL ? $FechaBaja = '' : $FechaBaja = $row['FechaBaja']->format('Y-m-d');
			$FechaVencimientoContrato = $row['FechaVencimientoContrato'] == NULL ? $FechaVencimientoContrato = '' : $FechaVencimientoContrato = $row['FechaVencimientoContrato']->format('Y-m-d');
			$datos[0] = array(
				"Noemp" => $row['NoEmp'], "Nombres" => $row['Nombres'], "ApellidoPaterno" => $row['ApellidoPaterno'], "ApellidoMaterno" => $row['ApellidoMaterno'],
				"NombreDepartamento" => $row['NombreDepartamento'], "IMSS" => $row['IMSS'], "RFC" => $row['RFC'], "CURP" => $row['CURP'], "IdClvEstadoCivil" => $row['IdClvEstadoCivil'],
				"Puesto" => $row['Puesto'], "FechaIngreso" => $FechaIngreso, "Telefono" => $row['Telefono'], "Telefono1" => $row['Telefono1'], "Telefono2" => $row['Telefono2'],
				"Domicilio" => $row['Domicilio'], "IdClvEntidad" => $row['IdClvEntidad'], "ClvMunicipioYDelegacion" => $row['ClvMunicipioYDelegacion'],
				"IdClvNivelEstudios" => $row['IdClvNivelEstudios'], "IdClvDocProbatorio" => $row['IdClvDocProbatorio'], "FechaVencimientoContrato" => $FechaVencimientoContrato,
				"TipoTrabajador" => $row['TipoTrabajador'], "FechaAntiguedad" => $FechaAntiguedad, "FechaBaja" => $FechaBaja, "IdMotivoBaja" => $row['IdMotivoBaja'],
				"OtroMotivoBaja" => $row['OtroMotivoBaja'], "Bajas" => $row['Bajas'], "EmpleadoSindicalizado" => $row['EmpleadoSindicalizado'],
				"IdClvDiscapacidad" => $row['IdClvDiscapacidad'], "NoHijosDependientes" => $row['NoHijosDependientes'], "IdClvOcupaciones" => $row['IdClvOcupaciones'],
				"AnioEmisionDocto" => $row['AnioEmisionDocto'], "IdClvTipoInstEducativa" => $row['IdClvTipoInstEducativa'], "NombreEstudioCarrera" => $row['NombreEstudioCarrera'],
				"JefeInm" => $row['JefeInm'], "NoDeptoReal" => $row['NoDeptoReal'], "IdCentroCosto" => $row['IdCentroCosto'], "IdCentroCostoReal" => $row['IdCentroCostoReal'],
				"Sexo" => $row['Sexo'], "RecibeOferta" => $row['RecibeOferta'], "IdClvTipoInstEducativa" => $row['IdClvTipoInstEducativa']
			);
		}
		echo json_encode($datos);
	}
	function guardarempleado()
	{
		$ClassConexion = new ClassConexion();
		$NoEmp = $_POST['NoEmp'];
		$Nombres = $_POST['Nombres'];
		$ApellidoPaterno = $_POST['ApellidoPaterno'];
		$ApellidoMaterno = $_POST['ApellidoMaterno'];
		$IdCentroCosto = $_POST['IdCentroCosto'];
		$NombreDepartamento = $_POST['NombreDepartamento'];
		$JefeInm = $_POST['JefeInm'];
		$Puesto = $_POST['Puesto'];
		$TipoTrabajador = $_POST['TipoTrabajador'];
		$FechaIngreso = $_POST['FechaIngreso'];
		$IdClvEstadoCivil = $_POST['IdClvEstadoCivil'];
		$FechaVencimientoContrato = $_POST['FechaVencimientoContrato'];
		$contrasenaOpcional = 'mexico2023';
		$IMSS = $_POST['IMSS'];
		$RFC = $_POST['RFC'];
		$CURP = $_POST['CURP'];
		$Telefono = $_POST['Telefono'];
		$Telefono1 = $_POST['Telefono1'];
		$Telefono2 = $_POST['Telefono2'];
		$IdClvNivelEstudios = $_POST['IdClvNivelEstudios'];
		$IdClvProbatorio = $_POST['IdClvProbatorio'];
		$IdClvEntidad = $_POST['IdClvEntidad'];
		$ClvMunicipioYDelegacion = $_POST['ClvMunicipioYDelegacion'];
		$Domicilio = $_POST['Domicilio'];
		$FechaAntiguedad = $_POST['FechaAntiguedad'];
		$IdClvDiscapacidad = $_POST['IdClvDiscapacidad'];
		$NoHijosDependientes = $_POST['NoHijosDependientes'];
		$IdClvOcupaciones = $_POST['IdClvOcupaciones'];
		$AnioEmisionDocto = $_POST['AnioEmisionDocto'];
		$IdClvInstitucionEducativa = $_POST['IdClvInstitucionEducativa'];
		$NombreEstudioCarrera = $_POST['NombreEstudioCarrera'];
		$FechaBja = $_POST['FechaBaja'];
		$IdMotivoBaja = $_POST['IdMotivoBaja'];
		$OtroMotivoBaja = $_POST['OtroMotivoBaja'];
		$IdCentroCostoReal = $_POST['IdCentroCostoReal'];
		$NoDeptoReal = $_POST['NoDeptoReal'];
		$Sexo = $_POST['Sexo'];
		$RecibeOferta = isset($_POST['RecibeOferta']) ? $RecibeOferta = 1 : $RecibeOferta = 0;
		$Bajas = isset($_POST['Bajas']) ? $Bajas = 1 : $Bajas = 0;
		$EmpleadoSindicalizado = isset($_POST['EmpleadoSindicalizado']) ? $EmpleadoSindicalizado = 1 : $EmpleadoSindicalizado = 0;
		$prsedure = "pa_P009_00101_02_GuardartblEmpleados '" . $NoEmp . "','" . $Nombres . "','" . $ApellidoPaterno . "','" . $ApellidoMaterno . "','" . $IdCentroCosto . "','" . $NombreDepartamento . "','" . $IMSS . "','" . $RFC . "','" . $CURP . "','" . $IdClvEstadoCivil . "','" . $Puesto . "','" . $FechaIngreso . "','" . $Telefono . "','" . $Telefono1 . "','" . $Telefono2 . "','" . $Domicilio . "','" . $IdClvEntidad . "','" . $ClvMunicipioYDelegacion . "','" . $IdClvNivelEstudios . "','" . $FechaVencimientoContrato . "','" . $contrasenaOpcional . "','" . $TipoTrabajador . "','" . $RecibeOferta . "','" . $FechaAntiguedad . "','" . $FechaBja . "','" . $IdMotivoBaja . "','" . $OtroMotivoBaja . "','" . $EmpleadoSindicalizado . "','" . $JefeInm . "','" . $IdClvProbatorio . "','0','" . $IdClvDiscapacidad . "','" . $NoHijosDependientes . "','" . $IdClvOcupaciones . "','" . $AnioEmisionDocto . "','" . $IdClvInstitucionEducativa . "','" . $NombreEstudioCarrera . "','','" . $IdCentroCostoReal . "','" . $NoDeptoReal . "','" . $Sexo . "',46686";
		$conn = $ClassConexion->conexion("TLX032MXDB");
		$result = sqlsrv_query($conn, $prsedure);
		if ($result === false) {
			echo json_encode('error');
		} else {
			sqlsrv_fetch($result);
			$existe = sqlsrv_get_field($result, 0);
			echo json_encode($existe == 2 ? "existe" : 'ok');
		}
		sqlsrv_close($conn);
	}
	function actualizarempleado()
	{
		$ClassConexion = new ClassConexion();
		$NoEmp = $_POST['NoEmp'];
		$Nombres = $_POST['Nombres'];
		$ApellidoPaterno = $_POST['ApellidoPaterno'];
		$ApellidoMaterno = $_POST['ApellidoMaterno'];
		$IdCentroCosto = $_POST['IdCentroCosto'];
		$NombreDepartamento = $_POST['NombreDepartamento'];
		$JefeInm = $_POST['JefeInm'];
		$Puesto = $_POST['Puesto'];
		$TipoTrabajador = $_POST['TipoTrabajador'];
		$FechaIngreso = $_POST['FechaIngreso'];
		$IdClvEstadoCivil = $_POST['IdClvEstadoCivil'];
		$FechaVencimientoContrato = $_POST['FechaVencimientoContrato'];
		$IMSS = $_POST['IMSS'];
		$RFC = $_POST['RFC'];
		$CURP = $_POST['CURP'];
		$Telefono = $_POST['Telefono'];
		$Telefono1 = $_POST['Telefono1'];
		$Telefono2 = $_POST['Telefono2'];
		$IdClvNivelEstudios = $_POST['IdClvNivelEstudios'];
		$IdClvProbatorio = $_POST['IdClvProbatorio'];
		$IdClvEntidad = $_POST['IdClvEntidad'];
		$ClvMunicipioYDelegacion = $_POST['ClvMunicipioYDelegacion'];
		$Domicilio = $_POST['Domicilio'];
		$FechaAntiguedad = $_POST['FechaAntiguedad'];
		$IdClvDiscapacidad = $_POST['IdClvDiscapacidad'];
		$NoHijosDependientes = $_POST['NoHijosDependientes'];
		$IdClvOcupaciones = $_POST['IdClvOcupaciones'];
		$AnioEmisionDocto = $_POST['AnioEmisionDocto'];
		$IdClvInstitucionEducativa = $_POST['IdClvInstitucionEducativa'];
		$NombreEstudioCarrera = $_POST['NombreEstudioCarrera'];
		$FechaBja = $_POST['FechaBaja'];
		$IdMotivoBaja = $_POST['IdMotivoBaja'];
		$OtroMotivoBaja = $_POST['OtroMotivoBaja'];
		$IdCentroCostoReal = $_POST['IdCentroCostoReal'];
		$NoDeptoReal = $_POST['NoDeptoReal'];
		$Sexo = $_POST['Sexo'];
		$RecibeOferta = isset($_POST['RecibeOferta']) ? $RecibeOferta = 1 : $RecibeOferta = 0;
		$Bajas = isset($_POST['Bajas']) ? $Bajas = 1 : $Bajas = 0;
		$EmpleadoSindicalizado = isset($_POST['EmpleadoSindicalizado']) ? $EmpleadoSindicalizado = 1 : $EmpleadoSindicalizado = 0;
		$prsedure = "EXEC pa_P009_00102_ModificartblEmpleados '" . $NoEmp . "','" . $Nombres . "','" . $ApellidoPaterno . "','" . $ApellidoMaterno . "','" . $IdCentroCosto . "','" . $NombreDepartamento . "','" . $IMSS . "','" . $RFC . "','" . $CURP . "','" . $IdClvEstadoCivil . "','" . $Puesto . "','" . $FechaIngreso . "','" . $Telefono . "','" . $Telefono1 . "','" . $Telefono2 . "','" . $Domicilio . "','" . $IdClvEntidad . "','" . $ClvMunicipioYDelegacion . "','" . $IdClvNivelEstudios . "','" . $FechaVencimientoContrato . "','" . $TipoTrabajador . "','" . $RecibeOferta . "','" . $FechaAntiguedad . "','" . $FechaBja . "','" . $IdMotivoBaja . "','" . $OtroMotivoBaja . "','" . $EmpleadoSindicalizado . "','" . $JefeInm . "','" . $IdClvProbatorio . "','" . $IdClvDiscapacidad . "','" . $NoHijosDependientes . "','" . $IdClvOcupaciones . "','" . $AnioEmisionDocto . "','" . $IdClvInstitucionEducativa . "','" . $NombreEstudioCarrera . "','" . $Bajas . "','','" . $IdCentroCostoReal . "','" . $NoDeptoReal . "','" . $Sexo . "',46686";
		$conn = $ClassConexion->conexion("TLX032MXDB");
		$result = sqlsrv_query($conn, $prsedure);
		echo json_encode($result === false ? "error" : "ok");
		sqlsrv_close($conn);
	}
}
if(isset($_GET["consultaempleados"])){
    $Empleado = new Empleados();
    $Empleado->consultaempleados();
}else if(isset($_GET["grafcursos"])){
    $Empleado = new Empleados();
    $Empleado->grafcursos();
}else if(isset($_GET["consultaasistencias"])){
    $Empleado = new Empleados();
    $Empleado->consultaasistencias();
}else if(isset($_GET["consultaallinfempleados"])){
    $Empleado = new Empleados();
    $Empleado->consultaallinfempleados();
}else if(isset($_GET["guardarempleado"])){
    $Empleado = new Empleados();
    $Empleado->guardarempleado();
}else if(isset($_GET["actualizarempleado"])){
    $Empleado = new Empleados();
    $Empleado->actualizarempleado();
}else if(isset($_GET["consultasEnfermeria"])){
    $Empleado = new Empleados();
    $Empleado->consultasEnfermeria();
}
