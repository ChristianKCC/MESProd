<?php 
require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
  $query = "SELECT * FROM tblMaquinas WHERE MaquinaObsoleta=0";
  $result = sqlsrv_query($conn,$query);
    echo "<option value=''>Selecciona una opción</option>";
  while($row = sqlsrv_fetch_array($result)){
    echo "<option value='$row[0]'> $row[1]</option>";
}
