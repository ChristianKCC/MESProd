<?php
require_once('../conexion.php');
require_once('../Components/tools.php');
class Perfil
{
    function informacionempleado($noemp)
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX032MXDB");
        $consulta = "SELECT tblEmpleados.*,tblPuestos.nombre as nompuesto, tblDepartamentos.NombreDepto as nomdepto, tblRINivelMaxEstudios2016.DescNivelEstudios as nivestu 
		FROM tblEmpleados INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.Puesto 
		INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento 
		INNER JOIN TLX009MXDB.dbo.tblRINivelMaxEstudios2016 ON tblRINivelMaxEstudios2016.IdClvNivelEstudios = tblEmpleados.IdClvNivelEstudios WHERE noemp = $noemp";
        $ejecutar = sqlsrv_query($conn, $consulta);
        while ($fila = sqlsrv_fetch_array($ejecutar)) {
            $FechaIngreso = $fila['FechaIngreso'] == NULL ? $FechaIngreso = '' : $FechaIngreso = $fila['FechaIngreso']->format('Y-m-d');
            $FechaAntiguedad = $fila['FechaAntiguedad'] == NULL ? $FechaAntiguedad = '' : $FechaAntiguedad = $fila['FechaAntiguedad']->format('Y-m-d');
            $FechaBaja = $fila['FechaBaja'] == NULL ? $FechaBaja = '' : $FechaBaja = $fila['FechaBaja']->format('Y-m-d');
            $datos = array(
                "NoEmp" => $fila['NoEmp'], "Nombres" => $fila['Nombres'], "ApellidoPaterno" => $fila['ApellidoPaterno'], "ApellidoMaterno" => $fila['ApellidoMaterno'],
                "Nohijos" => $fila['NoHijosDependientes'], "fechaantiguedad" => $FechaAntiguedad, "CURP" => $fila['CURP'], "IMSS" => $fila['IMSS'],
                "NombreDepartamento" => $fila['NombreDepartamento'], "Puesto" => $fila['Puesto'], "RFC" => $fila['RFC'], "CorreoInterno" => $fila['CorreoInterno'],
                "EstadoCivil" => $fila['EstadoCivil'], "Domicilio" => $fila['Domicilio'], "TipoTrabajador" => $fila['TipoTrabajador'],
                "NombreEstudioCarrera" => $fila['NombreEstudioCarrera'], "Telefono" => $fila['Telefono'], "Telefono1" => $fila['Telefono1'],
                "Nvlestudios" => $fila['nivestu'], "NombreDepartamento" => $fila['nomdepto'], "nompuesto" => $fila['nompuesto'],
                "Domicilio" => $fila['Domicilio']
            );
        }
        return $datos;
    }
    function tblcursosempleado($ibm)
	{
		$ClassConexion = new ClassConexion();
		$conn = $ClassConexion->conexion("TLX035MXDB");
		$querycursos = "SELECT tblCursos.NombreCurso,tblSubEncabCapturaCapacitacion.Calificacion, tblSubEncabCapturaCapacitacion.Contestado,tblEncabezadoCapturaCapacitacion.Induccion,
		tblEncabezadoCapturaCapacitacion.DuracionReal,tblEncabezadoCapturaCapacitacion.FechaInicial,tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura 
		FROM tblSubEncabCapturaCapacitacion INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura= tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura 
		INNER JOIN tblCursos on tblCursos.IdCurso=tblEncabezadoCapturaCapacitacion.IdCurso WHERE tblSubEncabCapturaCapacitacion.NoEmp=$ibm";
		$resultcursos = sqlsrv_query($conn, $querycursos);
		$contesto = '';
		while ($filascursos = sqlsrv_fetch_array($resultcursos)) {
			$filascursos[2] == 1 ? $contesto = 'SI' : $contesto = 'NO';
			$filascursos[3] == 1 ? $indu = 'Inducción' : $indu = 'Normal';
			echo "<tr><td>" . $filascursos[0] . "</td>";
			echo "<td>" . $filascursos[1] . "</td>";
			echo "<td>" . $filascursos[4] . "</td>";
			echo "<td>" . $filascursos[5]->format('Y-m-d') . "</td>";
			echo "<td>" . $filascursos[6] . "</td>";
			echo "<td>" . $contesto . "</td>";
			echo "<td>" . $indu . "</td></tr>";
		}
	}
}
?>