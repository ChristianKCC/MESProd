<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';
class NoConformidad
{
    function tblReporteNoConformidad()
    {
        $fechai = $_POST['fechai'];
        $fechaf = $_POST['fechaf'];
        $departamento = $_POST['departamento'];
        $maquina = $_POST['maquina'];
        $addquery='';
        $departamento != '' && $addquery .= " AND tblNoConformidad.departamento=$departamento";
        $maquina != '' && $addquery .= " AND tblNoConformidad.maquina=$maquina";
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT  tblNoConformidad.id,tblNoConformidad.fecha,tblDepartamentos.NombreDepto,tblMaquinas.NombreMaquina,tblEmpleados.Nombre as sellador,
        tblemp2.Nombre as operador,Trazabilidadturno.nombre as turno, tblValeEClaves.Descripcion_Articulo as producto,tblNoConformidad.hora,trazabilidaddefectos.nombre as defecto,
        tblNoConformidad.totalprod,tblNoConformidad.prodrecuperado,tblemp3.nombre as lider,tblNoConformidad.codempdefecto,tblNoConformidad.codterdefecto,
        tblNoConformidad.descripcion,tblNoConformidad.accionescorrectivas,tblNoConformidadComponentes.Componente
        FROM tblNoConformidad INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblNoConformidad.departamento 
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblNoConformidad.maquina 
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON TLX032MXDB.dbo.tblEmpleados.NoEmp=tblNoConformidad.sellador 
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblemp2 ON tblemp2.NoEmp=tblNoConformidad.operador 
        INNER JOIN TLX036MXDB.dbo.Trazabilidadturno ON Trazabilidadturno.id=tblNoConformidad.turno 
        INNER JOIN TLX002MXDB.dbo.tblValeEClaves ON tblValeEClaves.NoClave=tblNoConformidad.producto 
        INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblemp3 ON tblemp3.NoEmp=tblNoConformidad.lider 
        INNER JOIN TLX036MXDB.dbo.trazabilidaddefectos on trazabilidaddefectos.id = tblNoConformidad.defecto
        LEFT JOIN TLX002MXDB.dbo.tblNoConformidadComponentes on tblNoConformidadComponentes.id = tblNoConformidad.componente
        WHERE (tblNoConformidad.fecha >= '" . $fechai . "' AND tblNoConformidad.fecha < dateadd(day, 1, '" . $fechaf . "')) $addquery ORDER BY tblNoConformidad.id DESC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row['id'], "fecha" => $row['fecha']->format('Y-m-d'), "departamento" => $row['NombreDepto'], "maquina" => $row['NombreMaquina']
                , "sellador" => $row['sellador'], "operador" => $row['operador'], "turno" => $row['turno'], "producto" => $row['producto']
                , "hora" => $row['hora'], "defecto" => $row['defecto'], "totalprod" => $row['totalprod'], "prodrecuperado" => $row['prodrecuperado']
                , "lider" => $row['lider'], "codempdefecto" => $row['codempdefecto'], "codterdefecto" => $row['codterdefecto'], "descripcion" => $row['descripcion']
                , "accionescorrectivas" => $row['accionescorrectivas'], "Componente" => $row['Componente']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function saveUpdateNoConf(){
        $folio = $_POST['folio'];
        $departamento = $_POST['departamento'];
        $defecto = $_POST['defecto'];
        $calidad = $_POST['calidad'];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "UPDATE tblNoConformidad SET departamento=?, defecto=?,calidad=? WHERE id= ?";
        $result = sqlsrv_query($conn, $query,array($departamento,$defecto,$calidad, $folio));
        $result===false ? http_response_code(500) : http_response_code(200);
    }
}
if (isset($_GET['tblReporteNoConformidad'])) {
    $noconformidad = new NoConformidad();
    $noconformidad->tblReporteNoConformidad();
} else if (isset($_GET['saveUpdateNoConf'])) {
    $noconformidad = new NoConformidad();
    $noconformidad->saveUpdateNoConf();
}
