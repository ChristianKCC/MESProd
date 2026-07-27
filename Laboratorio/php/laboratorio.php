<?php
require_once "../../Session/seguridad.php";
require_once "../../conexion.php";
class Laboratorio
{
    function guardarencabezado()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $fecha = $_POST['fecha'];
        $turno = $_POST['turno'];
        $monitor = $_POST['monitor'];
        $sd = $_POST['sd'];
        $ql = $_POST['ql'];
        $muestras = $_POST['muestras'];
        $departamento = $_POST['departamento'];
        $maquina = $_POST['maquina'];
        $conductor = $_POST['conductor'];
        $supervisor = $_POST['supervisor'];
        $query = 'INSERT INTO tblLaboratorioFolios(fecha,turno,monitor,sd,ql,muestras,departamento,maquina,conductor,supervisor) VALUES (?,?,?,?,?,?,?,?,?,?)';
        $result = sqlsrv_query($conn, $query, array($fecha, $turno, $monitor, $sd, $ql, $muestras, $departamento, $maquina, $conductor, $supervisor));
        if ($result === false) {
            http_response_code(500);
            $errors = sqlsrv_errors();
            foreach ($errors as $error) {
                echo json_encode("message: " . $error['message']);
            }
        } else {
            http_response_code(200);
            echo json_encode('Registro exitoso');
        }
    }
    function tblLaboratorioEnc()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $array = array();
        $query = "SELECT TOP (50) id, fecha,turno,monitor.Nombre as monitor,sd,ql,muestras,Departamentos.NombreDepto,Maquinas.NombreMaquina,
        Maquinas.NoMaquina,conductor.Nombre as conductor,supervisor.Nombre as supervisor,monitor.NoEmp as monitornum, supervisor.NoEmp as supervisornum
        ,conductor.NoEmp as conductornum,Departamentos.NoDepto FROM tblLaboratorioFolios
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as monitor ON monitor.NoEmp = tblLaboratorioFolios.monitor
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as conductor ON conductor.NoEmp = tblLaboratorioFolios.monitor
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as supervisor ON supervisor.NoEmp = tblLaboratorioFolios.monitor
        INNER JOIN TLX009MXDB.dbo.tblMaquinas as Maquinas ON Maquinas.NoMaquina = tblLaboratorioFolios.maquina
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos as Departamentos ON Departamentos.NoDepto = tblLaboratorioFolios.departamento ORDER BY id Desc";
        $result = sqlsrv_query($conn, $query);
        while ($row =  sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'fecha' => $row['fecha']->format('Y-m-d'),
                'turno' => $row['turno'],
                'monitor' => $row['monitor'],
                'monitornum' => $row['monitornum'],
                'sd' => $row['sd'],
                'ql' => $row['ql'],
                'muestras' => $row['muestras'],
                'NoDepto' => $row['NoDepto'],
                'NombreDepto' => $row['NombreDepto'],
                'NoMaquina' => $row['NoMaquina'],
                'NombreMaquina' => $row['NombreMaquina'],
                'conductor' => $row['conductor'],
                'conductornum' => $row['conductornum'],
                'supervisor' => $row['supervisor'],
                'supervisornum' => $row['supervisornum']
            ]);
        }
        echo json_encode($array);
    }
    function tblLaboratorioEncxid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $array = array();
        $id = $_GET['id'];
        $query = "SELECT TOP (50) id, fecha,turno,monitor.Nombre as monitor,sd,ql,muestras,Departamentos.NombreDepto,Maquinas.NombreMaquina,
        Maquinas.NoMaquina,conductor.Nombre as conductor,supervisor.Nombre as supervisor,monitor.NoEmp as monitornum, supervisor.NoEmp as supervisornum
        ,conductor.NoEmp as conductornum,Departamentos.NoDepto FROM tblLaboratorioFolios
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as monitor ON monitor.NoEmp = tblLaboratorioFolios.monitor
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as conductor ON conductor.NoEmp = tblLaboratorioFolios.monitor
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as supervisor ON supervisor.NoEmp = tblLaboratorioFolios.monitor
        INNER JOIN TLX009MXDB.dbo.tblMaquinas as Maquinas ON Maquinas.NoMaquina = tblLaboratorioFolios.maquina
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos as Departamentos ON Departamentos.NoDepto = tblLaboratorioFolios.departamento
        WHERE id = $id";
        $result = sqlsrv_query($conn, $query);
        while ($row =  sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'fecha' => $row['fecha']->format('Y-m-d'),
                'turno' => $row['turno'],
                'monitor' => $row['monitor'],
                'monitornum' => $row['monitornum'],
                'sd' => $row['sd'],
                'ql' => $row['ql'],
                'muestras' => $row['muestras'],
                'NoDepto' => $row['NoDepto'],
                'NombreDepto' => $row['NombreDepto'],
                'NoMaquina' => $row['NoMaquina'],
                'NombreMaquina' => $row['NombreMaquina'],
                'conductor' => $row['conductor'],
                'conductornum' => $row['conductornum'],
                'supervisor' => $row['supervisor'],
                'supervisornum' => $row['supervisornum']
            ]);
        }
        echo json_encode($array);
    }

    function guardarsubencabezado()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['folio'];
        $clave = $_POST['clave'];
        $retenido = $_POST['retenido'];
        $merma = $_POST['merma'];
        $recuperado = $_POST['recuperado'];
        $defecto = $_POST['defecto'];
        $componente = $_POST['componente'];
        $numeroaparto = $_POST['numeroaparto'];
        $numerolibero = $_POST['numerolibero'];
        $hora = $_POST['hora'];
        $numerooperador = $_POST['numerooperador'];
        $estatus = $_POST['estatus'];
        $comentario = $_POST['comentario'];
        $seccion = $_POST['seccion'];
        $idencabezado = $_POST['idencabezado'];
        $query = 'INSERT INTO tblLaboratorioFoliosSubEnc(folio,clave,retenido,merma,recuperado,defecto,componente,numeroaparto,
        numerolibero,hora,numerooperador,estatus,comentario,seccion,idencabezado) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $result = sqlsrv_query($conn, $query, array(
            $folio,
            $clave,
            $retenido,
            $merma,
            $recuperado,
            $defecto,
            $componente,
            $numeroaparto,
            $numerolibero,
            $hora,
            $numerooperador,
            $estatus,
            $comentario,
            $seccion,
            $idencabezado
        ));
        if ($result === false) {
            http_response_code(500);
            $errors = sqlsrv_errors();
            foreach ($errors as $error) {
                echo json_encode("message: " . $error['message']);
            }
        } else {
            http_response_code(200);
            echo json_encode('Registro exitoso');
        }
    }
    function tblLaboratorioSubEnc()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $array = array();
        $id = $_GET['id'];
        $query = "SELECT idsubEncabezado,folio,Trazabilidadclavesdeproducto.nombre as clave,retenido,merma,recuperado,defecto,componente,apartoEmp.Nombre as aparto,
        liberoEmp.Nombre as libero,operadorEmp.Nombre as operador,hora,tblLaboratorioFoliosEstados.nombreestado,comentario,seccion,idencabezado FROM [TLX002MXDB].[dbo].[tblLaboratorioFoliosSubEnc]
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as apartoEmp ON apartoEmp.NoEmp = tblLaboratorioFoliosSubEnc.numeroaparto
        INNER JOIN TLX036MXDB.dbo.Trazabilidadclavesdeproducto ON Trazabilidadclavesdeproducto.id = tblLaboratorioFoliosSubEnc.clave
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as liberoEmp ON liberoEmp.NoEmp = tblLaboratorioFoliosSubEnc.numerolibero
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as operadorEmp ON operadorEmp.NoEmp = tblLaboratorioFoliosSubEnc.numerooperador
        INNER JOIN tblLaboratorioFoliosEstados ON tblLaboratorioFoliosEstados.idestado= tblLaboratorioFoliosSubEnc.estatus
        WHERE idencabezado= $id";
        $result = sqlsrv_query($conn, $query);
        while ($row =  sqlsrv_fetch_array($result)) {
            array_push($array, [
                'idsubEncabezado' => $row['idsubEncabezado'],
                'folio' => $row['folio'],
                'clave' => $row['clave'],
                'retenido' => $row['retenido'],
                'merma' => $row['merma'],
                'recuperado' => $row['recuperado'],
                'defecto' => $row['defecto'],
                'componente' => $row['componente'],
                'aparto' => $row['aparto'],
                'libero' => $row['libero'],
                'operador' => $row['operador'],
                'hora' => $row['hora']->format('H:i'),
                'estado' => $row['nombreestado'],
                'comentario' => $row['comentario'],
                'seccion' => $row['seccion'],
                'idencabezado' => $row['idencabezado'],
            ]);
        }
        echo json_encode($array);
    }
    function tblLaboratorioReporte()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $array = array();
        $fechai = $_POST['fechai'];
        $fechaf = $_POST['fechaf'];
        $query = "SELECT TOP (50) id, fecha,turno,monitor.Nombre as monitor,sd,ql,muestras,Departamentos.NombreDepto,Maquinas.NombreMaquina,
        Maquinas.NoMaquina,conductor.Nombre as conductor,supervisor.Nombre as supervisor,monitor.NoEmp as monitornum, supervisor.NoEmp as supervisornum
        ,conductor.NoEmp as conductornum,Departamentos.NoDepto FROM tblLaboratorioFolios
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as monitor ON monitor.NoEmp = tblLaboratorioFolios.monitor
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as conductor ON conductor.NoEmp = tblLaboratorioFolios.monitor
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as supervisor ON supervisor.NoEmp = tblLaboratorioFolios.monitor
        INNER JOIN TLX009MXDB.dbo.tblMaquinas as Maquinas ON Maquinas.NoMaquina = tblLaboratorioFolios.maquina
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos as Departamentos ON Departamentos.NoDepto = tblLaboratorioFolios.departamento
        WHERE fecha BETWEEN '$fechai' AND '$fechaf'";
        $result = sqlsrv_query($conn, $query);
        while ($row =  sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'fecha' => $row['fecha']->format('Y-m-d'),
                'turno' => $row['turno'],
                'monitor' => $row['monitor'],
                'monitornum' => $row['monitornum'],
                'sd' => $row['sd'],
                'ql' => $row['ql'],
                'muestras' => $row['muestras'],
                'NoDepto' => $row['NoDepto'],
                'NombreDepto' => $row['NombreDepto'],
                'NoMaquina' => $row['NoMaquina'],
                'NombreMaquina' => $row['NombreMaquina'],
                'conductor' => $row['conductor'],
                'conductornum' => $row['conductornum'],
                'supervisor' => $row['supervisor'],
                'supervisornum' => $row['supervisornum']
            ]);
        }
        echo json_encode($array);
    }
}

if (isset($_GET['guardarencabezado'])) {
    $Laboratorio = new Laboratorio();
    $Laboratorio->guardarencabezado();
} else if (isset($_GET['guardarsubencabezado'])) {
    $Laboratorio = new Laboratorio();
    $Laboratorio->guardarsubencabezado();
} else if (isset($_GET['tblLaboratorioEnc'])) {
    $Laboratorio = new Laboratorio();
    $Laboratorio->tblLaboratorioEnc();
} else if (isset($_GET['tblLaboratorioSubEnc'])) {
    $Laboratorio = new Laboratorio();
    $Laboratorio->tblLaboratorioSubEnc();
} else if (isset($_GET['tblLaboratorioEncxid'])) {
    $Laboratorio = new Laboratorio();
    $Laboratorio->tblLaboratorioEncxid();
} else if (isset($_GET['tblLaboratorioReporte'])) {
    $Laboratorio = new Laboratorio();
    $Laboratorio->tblLaboratorioReporte();
}
