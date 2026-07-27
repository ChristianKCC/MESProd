<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";
class Aris
{
    function tblRechazos()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT [fecha]
      ,[turno]
      ,[codigo]
      ,[descripcion]
      ,[merma]
      ,[categoria]
      ,[id_inser]
        FROM [TLX003MXDB].[dbo].[merma_records] WHERE merma>10 ORDER BY merma DESC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ["id_inser" => $row['id_inser'], "categoria" => $row['categoria'], "merma" => $row['merma'],
             "descripcion" => $row['descripcion'], "codigo" => $row['codigo'], "turno" => $row['turno'],
              "fecha" => $row['fecha']->format('Y-m-d H:i:s')]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }
}
if (isset($_GET['tblRechazos'])) {
    $Aris = new Aris();
    $Aris->tblRechazos();
}