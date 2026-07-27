<?php
require_once "../../conexion.php";
require_once "../../Session/seguridad.php";
class Preusos
{
    function reportePreusos()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $departamento = $_POST["departamento"];
        $maquina = $_POST["maquina"];
        $addwhere = '';
        $maquina == '' ? $addwhere =" " : $addwhere = " AND tblMaquinas.NoMaquina = $maquina"; 
        $query = "SELECT dbo.tblInspeccionDesc.descInpeccion, dbo.tblBitInspeccionesChecklist.itemcheck, dbo.tblInspeccionTipo.tipoinspeccion, dbo.tblBitPreusosSecciones.seccionpre, dbo.tblBitInspecciones.comentarios, 
                         dbo.tblBitInspecciones.fechasave, dbo.tblBitInspecciones.noemp, TLX032MXDB.dbo.tblEmpleados.NoEmp AS Expr1, TLX032MXDB.dbo.tblEmpleados.Nombre, tblBitInspecciones.id, 
                         TLX004MXDB.dbo.tblEncabezadoBitacora.Turno, TLX009MXDB.dbo.tblMaquinas.NombreMaquina
                          FROM dbo.tblBitInspecciones INNER JOIN
                         dbo.tblBitPreusosSecciones ON dbo.tblBitInspecciones.seccion = dbo.tblBitPreusosSecciones.id INNER JOIN
                         dbo.tblBitInspeccionesChecklist ON dbo.tblBitInspecciones.id = dbo.tblBitInspeccionesChecklist.idenc INNER JOIN
                         dbo.tblInspeccionDesc ON dbo.tblBitInspeccionesChecklist.idcheck = dbo.tblInspeccionDesc.id INNER JOIN
                         dbo.tblInspeccionTipo ON dbo.tblInspeccionDesc.tipo = dbo.tblInspeccionTipo.id INNER JOIN
                         TLX032MXDB.dbo.tblEmpleados ON dbo.tblBitInspecciones.noemp = TLX032MXDB.dbo.tblEmpleados.NoEmp INNER JOIN
                         TLX004MXDB.dbo.tblEncabezadoBitacora ON dbo.tblBitInspecciones.folio = TLX004MXDB.dbo.tblEncabezadoBitacora.IdEncabezadoBItacora INNER JOIN
                         TLX009MXDB.dbo.tblMaquinas ON TLX004MXDB.dbo.tblEncabezadoBitacora.NoMaquina = TLX009MXDB.dbo.tblMaquinas.NoMaquina
                         WHERE tblBitInspecciones.fechasave>= '$fechai' AND tblBitInspecciones.fechasave<= '$fechaf' $addwhere ORDER BY tblBitInspecciones.id DESC";
                        //   echo $query;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $row["fechasave"] = $row["fechasave"]->format("Y-m-d");
            $array[] = $row;
        }
        echo json_encode($array);
    }
}

if (isset($_GET['reportePreusos'])) {
    $Preusosobj = new Preusos();
    $Preusosobj->reportePreusos();
}
