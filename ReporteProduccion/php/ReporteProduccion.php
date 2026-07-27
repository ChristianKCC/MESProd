<?php
require_once "../../Session/seguridad.php";
require_once "../../conexion.php";
class ReporteProduccion
{
    function dataProduccion()
    {

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $departamento = $_POST["departamento"];
        $maquina = $_POST["maquina"];
        $addwhere = '';
        $addwhere .= $departamento == '' ? '' : " AND tblMaquinasCombo.NoDepto=$departamento";
        $addwhere .= $maquina == '' ? '' : " AND tblMaquinasCombo.Nomaquina=$maquina";
        $query = "SELECT * FROM dbo.ProduccionesView 
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = ProduccionesView.NoMaquina 
        INNER JOIN TLX009MXDB.dbo.tblMaquinasCombo ON tblMaquinasCombo.NoMaquina=tblMaquinas.NoMaquina
        WHERE fecha between ? AND ? $addwhere  ORDER BY fecha DESC";
        $result = sqlsrv_query($conn, $query, array($fechai, $fechaf));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = $row;
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function dataProducciontblTurnos() {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $departamento = $_POST["departamento"];
        $maquina = $_POST["maquina"];
        $addwhere = '';
        $addwhere .= $departamento == '' ? '' : " AND tblMaquinasCombo.NoDepto=$departamento";
        $addwhere .= $maquina == '' ? '' : " AND tblMaquinasCombo.Nomaquina=$maquina";
        $query = "SELECT * FROM dbo.ProduccionesViewTurnos 
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = ProduccionesView.NoMaquina 
        INNER JOIN TLX009MXDB.dbo.tblMaquinasCombo ON tblMaquinasCombo.NoMaquina=tblMaquinas.NoMaquina
        WHERE fecha between ? AND ? $addwhere  ORDER BY fecha DESC";
        $result = sqlsrv_query($conn, $query, array($fechai, $fechaf));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = $row;
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function dataParos()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $departamento = $_POST["departamento"];
        $maquina = $_POST["maquina"];
        $addwhere = '';
        $addwhere .= $departamento == '' ? '' : "AND tblMaquinasCombo.NoDepto=$departamento";
        $addwhere .= $maquina == '' ? '' : " AND tblMaquinasCombo.Nomaquina=$maquina";
        $query = "SELECT 
        tblMaquinas.NombreMaquina,
        tblProduccionSecciones.Seccion, 
        tblProduccionModulos.Modulos, 
        SUM(tblBitCtrltiempos.operacion + tblBitCtrltiempos.electrico + tblBitCtrltiempos.mecanico + 
        tblBitCtrltiempos.materias + tblBitCtrltiempos.grado + tblBitCtrltiempos.prev + 
        tblBitCtrltiempos.servicios) AS TotalSuma
        FROM tblBitCtrltiempos 
        INNER JOIN tblProduccionSecciones 
            ON tblProduccionSecciones.idSeccion = tblBitCtrltiempos.seccion
        INNER JOIN tblProduccionModulos 
            ON tblProduccionModulos.idModulos = tblBitCtrltiempos.modulo
        INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora 
            ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitCtrltiempos.folio
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblEncabezadoBitacora.NoMaquina 
        INNER JOIN TLX009MXDB.dbo.tblMaquinasCombo ON tblMaquinasCombo.NoMaquina=tblMaquinas.NoMaquina
        WHERE tblEncabezadoBitacora.Fecha BETWEEN ? AND ? $addwhere
        GROUP BY
            tblMaquinas.NombreMaquina,
            tblProduccionSecciones.Seccion,
            tblProduccionModulos.Modulos;
       ";
        $result = sqlsrv_query($conn, $query, array($fechai, $fechaf));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = $row;
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }
    function DataParosDetails()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $seccion = $_POST['seccion'];
        $modulo = $_POST['modulo'];
        $fechai = $_POST['fechai'];
        $fechaf = $_POST['fechaf'];
        $query = "SELECT operacion, electrico, mecanico, materias, grado, prev, servicios 
          FROM tblBitCtrltiempos 
          INNER JOIN tblProduccionSecciones ON tblProduccionSecciones.idSeccion = tblBitCtrltiempos.seccion
          INNER JOIN tblProduccionModulos ON tblProduccionModulos.idModulos = tblBitCtrltiempos.modulo
          INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitCtrltiempos.folio
          WHERE tblEncabezadoBitacora.Fecha BETWEEN ? AND ? AND (tblProduccionSecciones.Seccion = ? AND tblProduccionModulos.Modulos = ?)";
        $result = sqlsrv_query($conn, $query, array($fechai, $fechaf,$seccion, $modulo));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = $row;
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }
}
if (isset($_GET['dataProduccion'])) {
    $ReporteProduccion = new ReporteProduccion();
    $ReporteProduccion->dataProduccion();
} else if (isset($_GET['dataParos'])) {
    $ReporteProduccion = new ReporteProduccion();
    $ReporteProduccion->dataParos();
} else if (isset($_GET['DataParosDetails'])) {
    $ReporteProduccion = new ReporteProduccion();
    $ReporteProduccion->DataParosDetails();
} else if (isset($_GET['dataProducciontblTurnos'])) {
    $ReporteProduccion = new ReporteProduccion();
    $ReporteProduccion->dataProducciontblTurnos();
}



