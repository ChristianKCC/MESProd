<?php
function lstmcm(){ 
 include '../../../csql32.php';
  $query = "SELECT * FROM tblCTAgastosregMonedas;";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo lstmcm();
?>