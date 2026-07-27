<?php
function lstdep(){ 
 include '../../../csql32.php';
 $id=$_POST['id'];
  $query = "SELECT TLX009MXDB.dbo.tblDepartamentos.NombreDepto FROM tblEmpleados INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento WHERE tblEmpleados.NoEmp=$id";
  $listas='';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<p>'.$row[0].'</p>';
  }
  return $listas;
}
echo lstdep();
?>