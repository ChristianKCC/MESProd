<?php 
function llnrslccardex(){
require_once "../../../csql35.php";
$query="EXEC pa_P009_00201LlenarCbxIdCardexCP";
$lista='<option value="">Elige un cardex</option>';
$result=sqlsrv_query($conn,$query);
while ($row=sqlsrv_fetch_array($result)) {
$lista.='<option value="'.$row['IdCardex'].'">'.$row['NombreCardex'].'</option>';
}
return $lista;
}
echo llnrslccardex();
 ?>