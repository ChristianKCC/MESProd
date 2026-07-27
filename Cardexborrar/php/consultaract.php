<?php 
require_once "../../../csql35.php";
$idcardex=$_POST['idcardex'];
$id=$_POST['id'];
$query="EXEC pa_P009_00217LlenarDGVCursosxAxtividad '".$idcardex."','".$id."'";
$result=sqlsrv_query($conn,$query);
while ($row=sqlsrv_fetch_array($result)) {
echo '<option value="'.$row[0].'">'.$row[1].'</option>';
}
 ?>
     