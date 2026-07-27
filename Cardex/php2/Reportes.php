<?php
require_once('../../conexion.php');
require_once('../../Components/tools.php');
require_once('../../Session/seguridad.php');
class CapacitacionesReportes
{
    function getDataReportxCurso()
    {
        $classconexion = new ClassConexion();
        $conn = $classconexion->conexion('TLX035MXDB');
        $array = array();
        $curso=$_POST['curso'];
        $fechai=$_POST['fechai'];
        $fechaf=$_POST['fechaf'];
        empty($curso) ? $addcurso = "" : $addcurso = " AND tblCursos.IdCurso=$curso";
        $cont = 1;
        $query = "SELECT tblSubEncabCapturaCapacitacion.IdSubEncabCaptura,tblEmpleados.NoEmp,tblEmpleados.Nombre,tblSubEncabCapturaCapacitacion.Calificacion,tblEncabezadoCapturaCapacitacion.DuracionReal,
        tblEncabezadoCapturaCapacitacion.FechaInicial,tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura,tblPuestos.nombre as puesto,tblDepartamentos.NombreDepto as depto, 
        tblEncabezadoCapturaCapacitacion.NoEmpInstructor,tblCursos.NombreCurso as nomcurso FROM TLX032MXDB.dbo.tblEmpleados INNER JOIN tblSubEncabCapturaCapacitacion ON 
        tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = 
        tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura INNER JOIN tblCursos ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso 
        INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.puesto INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON 
        tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento 
        WHERE tblEncabezadoCapturaCapacitacion.FechaInicial >= '$fechai' AND tblEncabezadoCapturaCapacitacion.FechaInicial < DATEADD(day,1,'$fechaf') $addcurso ORDER BY tblEmpleados.Nombre ASC";
        $result = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array,['cont'=>$cont++,'noemp'=>$row['NoEmp'],'nombre'=>$row['Nombre'],'calificacion'=>number_format($row['Calificacion'],2),'duracion'=>$row['DuracionReal'],'fecha'=>$row['FechaInicial']->format('Y-m-d')
            ,'puesto'=>$row['puesto'],'depto'=>$row['depto'],'instructor'=>$row['NoEmpInstructor'],'nombrecurso'=>$row['nomcurso'],'id'=>$row['IdSubEncabCaptura']
            ,'idcap'=>$row['IdEncabezadoCaptura']]);
        }
        echo json_encode($array);
    }
    function getDataExament(){
        $classconexion = new ClassConexion();
        $conn = $classconexion->conexion('TLX035MXDB');
        $id = $_POST['id'];
        $array = array();
        $query = "SELECT tblSubEncabCapturaCapacitacion.IdSubEncabCaptura,tblCursosPregunta.pregunta, tblCursosPregunta.r1,tblCursosPregunta.r2,tblCursosPregunta.r3, tblCursosPregunta.correcta, tblSubEncabCapturaCapacitacionExamen.respuesta 
FROM tblSubEncabCapturaCapacitacionExamen
LEFT JOIN tblSubEncabCapturaCapacitacion ON tblSubEncabCapturaCapacitacion.IdSubEncabCaptura = tblSubEncabCapturaCapacitacionExamen.idsubcapacitacion
LEFT JOIN tblCursosPregunta ON tblCursosPregunta.id = tblSubEncabCapturaCapacitacionExamen.pregunta WHERE tblSubEncabCapturaCapacitacion.IdSubEncabCaptura = $id and tblCursosPregunta.obsoleta = 0";
        $result = sqlsrv_query($conn, $query);
            while ($row = sqlsrv_fetch_array($result)) {
                array_push($array,['pregunta'=>$row['pregunta'],'r1'=>$row['r1'],'r2'=>$row['r2'],'r3'=>$row['r3'],
                'correcta'=>$row['correcta'],'respuesta'=>$row['respuesta'],'id'=>$row['IdSubEncabCaptura']]);
            }
            echo json_encode($array);
    }
}

if(isset($_GET['getDataReportxCurso'])){
    $Reportes = new CapacitacionesReportes();
    $Reportes->getDataReportxCurso();
}else if(isset($_GET['getDataExament'])){
    $Reportes = new CapacitacionesReportes();
    $Reportes->getDataExament();
}
?>