<?php 
header("Content-Type: application/vnd.ms-excel charset=iso-8859-1");
header("Pragma: public");
header("Expires: 0");
$filename = "Empxcurso.xls";
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
<?php if($_GET['opcion']=='xcurso'){ 
$query=$_GET['query'];
?>
<h5>Asistencias al curso</h5>
<div class="table-responsive" style="height:600px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>No.</th>
      <th>No.Emp</th>
      <th>Nombre</th>
      <th>Área</th>
      <th>Puesto</th>
      <th>Folio cap.</th>
      <th>Calificación</th>
      <th>Duración</th>
      <th>Fecha</th>
      <th>Nombre curso</th>
      <th>Inst.</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $cont++;
    echo "<tr><td>".$cont." </td>";
    echo "<td>".$row[0]." </td>";
    echo "<td>".$row[1]." </td>";
    echo "<td>".$row[7]." </td>";
    echo "<td>".$row[6]." </td>";
    echo "<td>".$row[5]." </td>";
    echo "<td>".$row[2]." </td>";
    echo "<td>".$row[3]." </td>";
    echo "<td>".$row[4]->format("Y-m-d")."</td>";
    echo "<td>".$row["nomcurso"]."</td>";
    echo "<td>".$row[8]." </td></tr>";
   }
?>
</tbody>
</table>
</div>
<?php 
} ?>
</body>
</html>