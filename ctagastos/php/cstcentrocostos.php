<?php
function lstmcm(){ 
 include '../../../csql32.php';
  $query = "SELECT * FROM tblCTAgastosCentroCostos;";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].' - '.$row[0].'</option>';
  }
  return $listas;
}
echo lstmcm();
?>