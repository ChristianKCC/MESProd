<?php
function lst(){ 
 include '../../../csql32.php';
  $query = "SELECT * FROM tblCTAgastosConceptos ORDER BY nombre ASC";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo lst();
?>