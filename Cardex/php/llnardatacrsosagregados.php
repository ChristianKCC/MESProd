<?php 
require_once "../../../csql35.php";
$idcardex=$_POST['idcardex'];
$query="EXEC pa_P009_00204LlenarDGVCursosXCardex '".$idcardex."'";
$result=sqlsrv_query($conn,$query);
while ($row=sqlsrv_fetch_array($result)) {
echo '<option value="'.$row[0].'">'.$row[0]." - ".$row[1].'</option>';
}
 ?>
     