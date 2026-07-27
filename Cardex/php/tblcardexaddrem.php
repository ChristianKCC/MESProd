<?php 
require_once "../../../csql35.php";
require_once("../../Session/seguridad.php");
$idcardex=$_POST['idcardex'];
$id=$_POST['id'];
$query="EXEC pa_P009_00203_02_tblCardexCPLogEmp_Del '".$idcardex."','".$id."','".$_SESSION["ibm"]."'";
sqlsrv_query($conn,$query);
 ?>
     