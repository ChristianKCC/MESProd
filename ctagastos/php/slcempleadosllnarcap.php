<?php
function lstemp(){ 
 include '../../../csql32.php';
require_once("../../Session/seguridad.php");
  $query = "SELECT * FROM tblEmpleados WHERE Noemp=".$_SESSION['ibm'].";";
  $listas='';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['NoEmp'].'>'.$row['Nombre'].'</option>';
  }
  return $listas;
}
echo lstemp();
?>
<script type="text/javascript">
  $(document).ready(function(){
  var data1=$("#empleados").val();
  $.ajax({
    url: 'php/cstdepartamento.php',
    type: 'POST',
    dataType: 'html',
    data:{'id':data1}
  })
  .done(function(x) {
    $("#departamento").html(x);
  })
  .fail(function() {
    console.log("error");
  })
  .always(function() {
    console.log("complete dep");
  });
})
</script>