<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';
class HomeMes
{
  function getDataNoCursos()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX035MXDB");
        $query = "SELECT COUNT(*) as cursos,
        (SELECT COUNT(*) FROM TLX003MXDB.dbo.tblProactEnc WHERE tblProactEnc.observado=".$_SESSION['ibm']." AND tblProactEnc.cumple=0) as proact,
		    (SELECT COUNT(*) FROM TLX002MXDB.dbo.tblIMCEnc WHERE tblIMCEnc.responsable =".$_SESSION['ibm']." AND tblIMCEnc.estado=1) as IMC
        FROM tblSubEncabCapturaCapacitacion WHERE NoEmp=".$_SESSION['ibm']." AND Contestado=0";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
              "cursos" => $row['cursos'], "proact" => $row['proact'], "IMC" => $row['IMC']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function updatePassword(){
      $Conectar = new ClassConexion();
      $conn = $Conectar->conexion('TLX032MXDB');
      $password = $_POST['password'];
      $query = "UPDATE tblEmpleados SET ContrasenaOpcional = ? , CambioPassword=1 WHERE NoEmp = ?";
      $result = sqlsrv_query($conn,$query,(array($password,$_SESSION['ibm'])));
      $result === false ? http_response_code(500) : http_response_code(200);
    }
    function validaChgPassword(){
      $Conectar = new ClassConexion();
      $conn = $Conectar->conexion('TLX032MXDB');
      $query = "SELECT CambioPassword FROM tblEmpleados WHERE NoEmp=?";
      $result = sqlsrv_query($conn,$query,array($_SESSION['ibm']));
      sqlsrv_fetch($result);
      $valida= sqlsrv_get_field($result,0);
      echo $result === false ? json_encode('errorsql') : ($valida==0 ? json_encode('nocambiado') : json_encode('cambiado'));
    }
}
if(isset($_GET['getDataNoCursos'])){
  $homemes = new HomeMes();
  $homemes->getDataNoCursos();
}else if(isset($_GET['updatePassword'])){
  $homemes = new HomeMes();
  $homemes->updatePassword();
}else if(isset($_GET['validaChgPassword'])){
  $homemes = new HomeMes();
  $homemes->validaChgPassword();
}