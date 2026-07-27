<?php 
require_once "../../../csql35.php";
$idcardex=$_POST['idcardex'];
$idmcm=$_POST['idmcm'];
$query="EXEC pa_P009_00211LlenarDGVCursosxMCM '".$idcardex."','".$idmcm."'";
$result=sqlsrv_query($conn,$query);
while ($row=sqlsrv_fetch_array($result)) {
echo '<option value="'.$row[0].'">'.$row[1].'</option>';
}
 ?>
     