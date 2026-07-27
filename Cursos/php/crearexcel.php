<?php 
header("Content-Type: application/vnd.ms-excel charset=iso-8859-1");
header("Pragma: public");
header("Expires: 0");
$filename = "Asistencias.xls";
header("Content-type: application/x-msdownload");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
 include '../../../csql35.php';
  ?>
 <html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php if($_GET['opcion']==1){ 
$query=$_GET['query'];
$result = sqlsrv_query($conn, $query);
?>
<h5>Asistencias al curso</h5>
<div class="table-responsive" style="height:400px">
<table class="table table-hover table-sm" id="tblseleccion">
  <thead class="table-dark">
      <th>No.</th>
      <th>ID cap</th>
      <th>IBM</th>
      <th>Nombre</th>
      <th>Calificación</th>
      <th>Duración</th>
      <th>Curso</th>
      <th>Fecha inicio</th>
      <th>Fecha final</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  $calif=0;
  while($row = sqlsrv_fetch_array($result)){
  	$cont++;
    echo "<tr><td>".$cont."</td>";
    echo "<td>".$row[6]."</td>";
    echo "<td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td>";
    echo "<td>".$row[2]."</td>";
    echo "<td>".$row[7]." min</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row[4]->format('Y-m-d')."</td>";
    echo "<td>".$row[5]->format('Y-m-d')."</td></tr>";
    $calif=$calif+$row[2];
    }
?>
</tbody>
</table>
</div>
<?php }else if($_GET['opcion']==2){
$query=$_GET['query'];
$result = sqlsrv_query($conn, $query);
	?>
<h5>Restantes por tomar el curso</h5>
<div class="table-responsive" style="height:300px">
<table class="table table-hover table-sm" id="tblseleccion">
  <thead class="table-dark">
      <th>No.</th>
      <th>IBM</th>
      <th>Nombre</th>
      <th>Departamento</th>
      <th>Puesto</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  while($row = sqlsrv_fetch_array($result)){
  	$cont++;
    echo "<tr><td>".$cont."</td>";
    echo "<td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row[2]."</td></tr>";
    }
?>
</tbody>
</table>
</div>
<?php 
	}	
 ?>
</body>
</html>