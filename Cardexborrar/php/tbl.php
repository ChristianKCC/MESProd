<?php
if(isset($_GET['reporte'])){
 include '../../../csql35.php';
  $emp=$_POST['emp'];
  $cardex=$_POST['cardex'];
  $query = "SELECT tblCursos.IdCurso,tblCursos.NombreCurso FROM TLX032MXDB.dbo.tblEmpleados INNER JOIN tblSubEncabCapturaCapacitacion ON tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura INNER JOIN tblCursos ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso WHERE tblEmpleados.NoEmp=$emp  ORDER BY tblEmpleados.NoEmp ASC";
  $result = sqlsrv_query($conn, $query);
  ?>
<div class="row">
  <div class="col">
<h5>Cursos tomados</h5>
<div class="table-responsive" style="height:400px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>No.</th>
      <th>ID curso</th>
      <th>Nombre curso</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  while($row = sqlsrv_fetch_array($result)){
  	$cont++;
    echo "<tr><td>".$cont."</td>";
    echo "<td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td>";
    }
?>
</tbody>
</table>
</div>
</div>
<div class="col">
  <?php 
  $query = "SELECT tblCursos.IdCurso,tblCursos.NombreCurso FROM tblCardexCP INNER JOIN tblCursos ON tblCursos.IdCurso= tblCardexCP.IdCurso WHERE tblCardexCP.idCardex=$cardex";
    $result = sqlsrv_query($conn, $query);
   ?>

<h5>Cursos del cardex</h5>
<div class="table-responsive" style="height:400px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>No.</th>
      <th>ID curso</th>
      <th>Nombre curso</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  while($row = sqlsrv_fetch_array($result)){
    $cont++;
    echo "<tr><td>".$cont."</td>";
    echo "<td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td></tr>";
    }
?>
</tbody>
</table>
</div>
</div>
</div>
<div class="row">
<div class="col">
  <?php 
  $query = "SELECT C2.IdCurso,C2.NombreCurso FROM tblCardexCP INNER JOIN tblCursos  AS C2 ON C2.IdCurso= tblCardexCP.IdCurso WHERE (NOT EXISTS (SELECT tblCursos.IdCurso,tblCursos.NombreCurso FROM TLX032MXDB.dbo.tblEmpleados INNER JOIN tblSubEncabCapturaCapacitacion 
ON tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp INNER JOIN tblEncabezadoCapturaCapacitacion 
ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura INNER JOIN tblCursos 
ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso WHERE (C2.IdCurso=tblCursos.IdCurso AND tblEmpleados.NoEmp=$emp)) AND tblCardexCP.idCardex=$cardex) ";
    $result = sqlsrv_query($conn, $query);
   ?>

<h5>Cursos restantes para completar el cardex</h5>
<div class="table-responsive" style="height:400px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>No.</th>
      <th>ID curso</th>
      <th>Nombre curso</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  while($row = sqlsrv_fetch_array($result)){
    $cont++;
    echo "<tr><td>".$cont."</td>";
    echo "<td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td></tr>";
    }
?>
</tbody>
</table>
</div>
</div>
</div>
<?php 
}
else if(isset($_GET['reportepuesto'])){
 include '../../../csql35.php';
 $puestos=$_POST['puestos'];
 $addtipo='';
 if(!empty($_POST['tipo'])){
  $tipo=$_POST['tipo'];
  $addtipo=" AND tblCursos.clasificacion=$tipo";
 }
 $query = "SELECT tblCursos.* FROM tblCursos INNER JOIN tblSubCursosXPuesto ON tblSubCursosXPuesto.IdCurso=tblCursos.IdCurso WHERE tblSubCursosXPuesto.IdPuesto=$puestos $addtipo";
 $result = sqlsrv_query($conn, $query);
 ?>
<h5>Cursos del puesto</h5>
<div class="row">
  <div class="col-12">
<div class="table-responsive" style="height:400px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>No.</th>
      <th>Folio</th>
      <th>Nombre curso</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  while($row = sqlsrv_fetch_array($result)){
    $cont++;
    echo "<tr><td>".$cont."</td>";
    echo "<td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td></tr>";
    }
?>
</tbody>
</table>
</div>
</div>
</div>
<a href="pdf/crearpdf.php?query=<?php echo $query; ?>&puesto=<?php echo $puestos ?>" target="_blank" class="btn btn-danger">Generar PDF</a>
<br>
 <div id="chart_wrapper" style="
  overflow-x: scroll;
  overflow-y: none;
  width: 100%;">
  <div id="Puestosbar"></div>
  </div>
  <?php 
  $query = "SELECT tblPuestos.nombre as puestonombre,COUNT(*) as cont FROM TLX009MXDB.dbo.tblPuestos INNER JOIN tblSubCursosXPuesto ON tblPuestos.id=tblSubCursosXPuesto.IdPuesto INNER JOIN tblCursos ON tblCursos.IdCurso= tblSubCursosXPuesto.IdCurso $addtipo GROUP BY tblPuestos.nombre";
  $result = sqlsrv_query($conn, $query);
   ?>
   <script type="text/javascript">
    google.charts.load("current", {packages:["corechart"]});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable([
        ["Puestos", "Contador", { role: "style" } ],
        <?php 
         while($row=sqlsrv_fetch_array($result)){
          // $randomcolor = '#' . dechex(rand(100,10000000));
          echo '["'.$row[0].'",'.$row[1].',"color: #2980b9"],';
         }
        ?>

      ]);
      var view = new google.visualization.DataView(data);
      var options = {
           title: 'Número de cursos por puesto',
           width: 2400,
           height: 600,
           legend: 'none',
           bar: {groupWidth: '95%'},
           vAxis: { gridlines: { count: 4 } },
           hAxis: {
            direction:-1,
            slantedText:true,
            slantedTextAngle:90,
            fontSize:16,
            textStyle : {
            fontSize: 12
            }
            },
            chartArea:{left:60,top:60,width:"100%",height:"80%"}
         };
      var chart = new google.visualization.ColumnChart(document.getElementById("Puestosbar"));
      chart.draw(view, options);
  }
  </script>
<?php 
}else if(isset($_GET['reportecurso'])){
 include '../../../csql35.php';
 $curso=$_POST['curso'];
 $fechai=$_POST['fechai'];
 $fechaf=$_POST['fechaf'];
 $addcurso= '';
 empty($curso) ? $addcurso = "" : $addcurso = " AND tblCursos.IdCurso=$curso";
 ?>
<div class="table-responsive" style="height:600px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>No.</th>
      <th>IBM</th>
      <th>Nombre</th>
      <th>Curso</th>
      <th>Área</th>
      <th>Puesto</th>
      <th>Cap.</th>
      <th>Calificación</th>
      <th>Duración</th>
      <th>Fecha</th>
      <th>Inst.</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  $query = "SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre,tblSubEncabCapturaCapacitacion.Calificacion,tblEncabezadoCapturaCapacitacion.DuracionReal,tblEncabezadoCapturaCapacitacion.FechaInicial,tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura,tblPuestos.nombre,tblDepartamentos.NombreDepto, tblEncabezadoCapturaCapacitacion.NoEmpInstructor,tblCursos.NombreCurso as nomcurso FROM TLX032MXDB.dbo.tblEmpleados INNER JOIN tblSubEncabCapturaCapacitacion ON tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura INNER JOIN tblCursos ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.puesto INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento WHERE tblEncabezadoCapturaCapacitacion.FechaInicial >= '$fechai' AND tblEncabezadoCapturaCapacitacion.FechaInicial < DATEADD(day,1,'$fechaf') $addcurso ORDER BY tblEmpleados.Nombre ASC";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $cont++;
    echo "<tr><td>".$cont." </td>";
    echo "<td>".$row[0]." </td>";
    echo "<td>".$row[1]." </td>";
    echo "<td width='200px'>".$row['nomcurso']." </td>";
    echo "<td>".$row[7]." </td>";
    echo "<td>".$row[6]." </td>";
    echo "<td>".$row[5]." </td>";
    echo "<td>".$row[2]." </td>";
    echo "<td>".$row[3]." </td>";
    echo "<td>".$row[4]->format("Y-m-d")."</td>";
    echo "<td>".$row[8]." </td></tr>";
   }
?>
</tbody>
</table>
</div>
 <a href="pdf/crearpdfcardexcurso.php?query=<?php echo $query; ?>&idcurso=<?php echo $curso; ?>&fechai=<?php echo $fechai; ?>&fechaf=<?php echo $fechaf; ?>" target="_blank" class="btn btn-danger mb-2">Generar PDF</a>
<a style="float: right;" href="pdf/crearexcel.php?opcion=xcurso&query=<?php echo $query ?>">Crear excel</a>
<?php 
}else if(isset($_GET['reportexemp'])){
 include '../../../csql35.php';
 $emp=$_POST['emp'];
 $fechaswhere='';
 $addtipo='';
 $addtipotbl2='';
 if (!empty($_POST['fechai']) && !empty($_POST['fechaf'])) {
 $fechai=$_POST['fechai'];
 $fechaf=$_POST['fechaf'];
 $fechaswhere=" AND tblEncabezadoCapturaCapacitacion.FechaInicial >= '$fechai' AND tblEncabezadoCapturaCapacitacion.FechaInicial < DATEADD(day,1,'$fechaf')";
 }if(!empty($_POST['tipo'])){
  $tipo=$_POST['tipo'];
  $addtipo=" AND tblCursos.clasificacion=$tipo";
  $addtipotbl2=" AND tbl2cursos.clasificacion=$tipo";
  $tipo == 5 ? $fechaswhere = "" : NULL;  
 }

$query2="SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre, tblDepartamentos.NombreDepto as NomDep , tbldep2.NombreDepto as NomDepreal, tblPuestos.nombre as NomPuesto,tblPuestos.id as idpues FROM TLX032MXDB.dbo.tblEmpleados LEFT join TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento LEFT join TLX009MXDB.dbo.tblDepartamentos AS tbldep2 on tbldep2.NoDepto=tblEmpleados.NoDeptoReal LEFT join TLX009MXDB.dbo.tblPuestos on tblPuestos.id=tblEmpleados.Puesto  WHERE NoEmp=$emp";
$result2=sqlsrv_query($conn,$query2);
while ($row = sqlsrv_fetch_array($result2)) {
  $idpuesto=$row['idpues'];
  echo "<div class='row'><small class='fw-bold'>Datos del empleado</small> 
  <div class='col-2'><small class='fw-bold'>NoEmp: </small>$row[0]</div>
  <div class='col-5'><small class='fw-bold'>Nombre: </small>$row[1]</div>
  <div class='col-5'><small class='fw-bold'>Área: </small>".$row['NomDep']."</div></div>
  <div class='row mb-3'>
  <div class='col-7'><small class='fw-bold'>Departamento: </small>".$row['NomDepreal']."</div>
  <div class='col'><small class='fw-bold'>Puesto: </small>".$row['NomPuesto']."</div></div>";
}
 ?>
<div class="table-responsive" style="height:400px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>No</th>
      <th>ID curso</th>
      <th>Tipo</th>
      <th>Nombre</th>
      <th>Folio. cap</th>
      <th>IBM</th>
      <th>Calificación</th>
      <th>Duración</th>
      <th>Fecha</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  $query = "SELECT tblCursos.IdCurso,tblCursos.NombreCurso,tblSubEncabCapturaCapacitacion.Calificacion,tblEncabezadoCapturaCapacitacion.DuracionReal,tblEncabezadoCapturaCapacitacion.FechaInicial,tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura,tblEmpleados.NoEmp,tblcursosclasificacion.nombre as clasificacion FROM TLX032MXDB.dbo.tblEmpleados INNER JOIN tblSubEncabCapturaCapacitacion ON tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura INNER JOIN tblCursos ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso INNER JOIN tblcursosclasificacion ON tblcursosclasificacion.id=tblCursos.clasificacion WHERE (tblEmpleados.NoEmp=$emp $fechaswhere $addtipo) ORDER BY tblEmpleados.Nombre ASC";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $cont++;
    echo "<tr><td>".$cont." </td>";
    echo "<td>".$row[0]." </td>";
    echo "<td>".$row[7]." </td>";
    echo "<td>".$row[1]." </td>";
    echo "<td>".$row[5]." </td>";
    echo "<td>".$row[6]." </td>";
    echo "<td>".$row[2]." </td>";
    echo "<td>".$row[3]." </td>";
    echo "<td>".$row[4]->format("Y-m-d")."</td></tr>";
   }
?>
</tbody>
</table>
</div>
<a href="pdf/crearpdfcardexemp.php?query=<?php echo $query; ?>&emp=<?php echo $emp; ?>" target="_blank" class="btn btn-danger mb-2">Generar PDF</a>
<div class="row">
  <div class="col">
    <h6>Cursos que debe tomar acorde al puesto</h6>
    <div class="table-responsive" style="height:400px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>No.</th>
      <th>ID</th>
      <th>Nombre</th>
    </thead>
    <tbody>
  <?php 
  $cont2=0;
  $query = "SELECT tblCursos.IdCurso,tblCursos.NombreCurso FROM tblCursos INNER JOIN tblSubCursosXPuesto ON tblSubCursosXPuesto.IdCurso=tblCursos.IdCurso WHERE tblSubCursosXPuesto.IdPuesto=$idpuesto $addtipo";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $cont2++;
    echo "<tr><td>".$cont2." </td>";
    echo "<td>".$row[0]." </td>";
    echo "<td>".$row[1]." </td></tr>";
   }
?>
</tbody>
</table>
</div>
  </div>
    <div class="col">
    <h6>Cursos restantes por tomar acorde al puesto</h6>
    <div class="table-responsive" style="height:400px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>No.</th>
      <th>ID</th>
      <th>Nombre</th>
    </thead>
    <tbody>
  <?php 
  $cont3=0;
  $query = "SELECT tbl2cursos.IdCurso,tbl2cursos.NombreCurso FROM tblCursos as tbl2cursos INNER JOIN tblSubCursosXPuesto ON tblSubCursosXPuesto.IdCurso=tbl2cursos.IdCurso WHERE tblSubCursosXPuesto.IdPuesto=$idpuesto $addtipotbl2 AND NOT EXISTS (SELECT tblCursos.IdCurso,tblCursos.NombreCurso FROM tblCursos INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdCurso = tblCursos.IdCurso INNER JOIN tblSubEncabCapturaCapacitacion ON tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura=tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblSubEncabCapturaCapacitacion.NoEmp  WHERE (tblEmpleados.NoEmp=$emp AND tbl2cursos.IdCurso=tblCursos.IdCurso $addtipo $fechaswhere))";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $cont3++;
    echo "<tr><td>".$cont3." </td>";
    echo "<td>".$row[0]." </td>";
    echo "<td>".$row[1]." </td></tr>";
   }
$asistencia=$cont2-$cont3;
?>
</tbody>
</table>
</div>
  </div>
</div>
  <div id="piechart_3d" style="width: 900px; height: 500px;"></div>
 <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Cursos', 'Número'],
          ['Cursos tomados',     <?php echo $asistencia ?>],
          ['Cursos restantes',     <?php echo $cont3 ?>],
        ]);

       var options = {
          title: 'Desempeño',
          pieHole: 0.4,
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
      }
    </script>
<?php 
}
 ?>