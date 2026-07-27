<?php
  require_once "../../../csql.php";
  require_once("../../Session/seguridad.php");
  $query = "SELECT * FROM tblCapaNuevafecha WHERE estado=0 AND valida='".$_SESSION['ibm']."'";
  $result = sqlsrv_query($conn, $query);
?>
<div class="table-responsive">
<table class="table table-sm table-striped table-hover" id="capa">
  <thead class="table-dark">
    <th>ID</th>
    <th>ID acción</th>
    <th>Nueva fecha</th>
    <th>Se solicitó</th>
    <th>Autorizar</th>
  </thead>
<tbody>
<?php
  while($row = sqlsrv_fetch_array($result)){
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td>";
    echo "<td>".$row[2]->format("Y/m/d")."</td>";
    echo "<td>".$row[3]->format("Y/m/d")."</td>";
    echo "<td><button class='btn btn-success btn-sm' onclick='autoriza(".$row[0].",".$row[1].")'><i class='fas fa-check-square'></i></button></td></tr>";
    
  }
?>
</tbody>
</table>
</div>
