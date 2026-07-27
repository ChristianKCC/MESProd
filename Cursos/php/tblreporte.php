<?php
 include '../../../csql35.php';
  $curso=$_POST['curso'];
  $depreal=$_POST['depreal'];
  $dep=$_POST['dep'];
  $fecha=$_POST['fecha'];
  $induccion=$_POST['induccion'];
  $reinduccion=$_POST['reinduccion'];
  $filano=$_POST['filano'];
  $addin='';
  $addrein='';
  $addreano='';
  $tipodep='';
  $tipondep='';
  if(!empty($depreal)){
  $tipodep='NoDeptoReal='.$depreal;
  $tipondep='NoDeptoReal';
  }else{
  $tipodep='NombreDepartamento='.$dep;
  $tipondep='NombreDepartamento';
  }
  if($induccion==1){
  $addin=' ';
  }else{
  $addin=' AND tblEncabezadoCapturaCapacitacion.Induccion=0';
  }
  if($reinduccion==1){
  $addrein=' AND tblEncabezadoCapturaCapacitacion.Reinduccion=1';
  }
  if($filano==1){
  $datetime = new datetime($fecha);
  $addreano=" AND FORMAT(tblEncabezadoCapturaCapacitacion.FechaFinal,'yyyy') = '".$datetime->format('Y')."'";
  }
  else{
  $addreano=" AND FORMAT(tblEncabezadoCapturaCapacitacion.FechaFinal,'yyyy-MM') = '".$fecha."'";
  }
  $puestosadd='';
  $puestosaddgraf='';

  $query="SELECT * FROM 
  (SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre,tblSubEncabCapturaCapacitacion.Calificacion,tblCursos.NombreCurso,tblEncabezadoCapturaCapacitacion.FechaInicial ,
  tblEncabezadoCapturaCapacitacion.FechaFinal,tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura,tblEncabezadoCapturaCapacitacion.DuracionReal,tblEmpleados.Puesto,
  tblEncabezadoCapturaCapacitacion.NoEmpInstructor, ROW_NUMBER() OVER(PARTITION BY TLX032MXDB.dbo.tblEmpleados.NoEmp Order By TLX032MXDB.dbo.tblEmpleados.NoEmp Desc) rn
  FROM TLX032MXDB.dbo.tblEmpleados LEFT JOIN tblSubEncabCapturaCapacitacion ON tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp 
  LEFT JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura 
  LEFT JOIN tblCursos ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso WHERE (EXISTS (SELECT TLX032MXDB.dbo.tblEmpleados.NoEmp FROM tblSubCursosXPuesto 
  WHERE  tblSubCursosXPuesto.IdPuesto=tblEmpleados.Puesto AND tblSubCursosXPuesto.IdCurso=$curso) AND tblEmpleados.".$tipodep." AND tblCursos.IdCurso=$curso AND 
  tblEmpleados.bajas=0 AND tblSubEncabCapturaCapacitacion.Contestado=1 $addreano $addin $addrein $puestosaddgraf ) ) a WHERE rn=1;";
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
      <th>ID inst.</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  $contasis=0;
  $calif=0;
  while($row = sqlsrv_fetch_array($result)){
    $cont++;
    $contasis++;
    echo "<tr><td>".$cont."</td>";
    echo "<td>".$row[6]."</td>";
    echo "<td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td>";
    echo "<td>".$row[2]."</td>";
    echo "<td>".$row[7]." min</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row[4]->format('Y-m-d')."</td>";
    echo "<td>".$row[5]->format('Y-m-d')."</td>";
    echo "<td>".$row[9]."</td></tr>";
    $calif=$calif+$row[2];
    }
?>
</tbody>
</table>
</div>
  <a style="float: right;" href="php/crearexcel.php?opcion=1&query=<?php echo $query ?>">Crear excel</a>
<?php
if($cont!=0)
  echo "Promedio calificación: <span class='fw-bold'>".number_format((($calif/$cont)),2)."</span><br>";
  echo "Total cargados: <span class='fw-bold'>".$cont."</span><br>";
  ?>
  <?php 
  $query = "SELECT COUNT(*) FROM TLX032MXDB.dbo.tblEmpleados WHERE tblEmpleados.".$tipodep."";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
  $numtotal=$row[0];
  }
  $contgraf=$cont;
  
  $query = "SELECT tbl2.NoEmp,tbl2.Nombre,tblPuestos.nombre as puestonom, tblDepartamentos.NombreDepto FROM TLX032MXDB.dbo.tblEmpleados as tbl2 INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tbl2.puesto INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto=tbl2.NoDeptoReal WHERE (NOT EXISTS(SELECT TLX032MXDB.dbo.tblEmpleados.* FROM TLX032MXDB.dbo.tblEmpleados INNER JOIN tblSubEncabCapturaCapacitacion ON tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura INNER JOIN tblCursos ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso WHERE (tbl2.NoEmp = tblEmpleados.NoEmp AND tblEmpleados.bajas=0  AND tblSubEncabCapturaCapacitacion.Contestado=1 AND tblEmpleados.".$tipodep." AND tblCursos.IdCurso=$curso $addreano $addin $addrein)) AND (EXISTS (SELECT tbl2.NoEmp FROM tblSubCursosXPuesto WHERE  tblSubCursosXPuesto.IdPuesto=tbl2.Puesto AND tblSubCursosXPuesto.IdCurso=$curso)) AND tbl2.".$tipodep." AND tbl2.bajas=0 $puestosadd) ORDER BY tbl2.NoEmp ASC";
  $result = sqlsrv_query($conn, $query);
  //echo $query;
  ?>
<h5>Restantes por tomar el curso</h5>
<div class="row">
	<div class="col-4">
		    <div id="Asistencias" style="width: 500px; height: 300px;"></div>
	</div>
	<div class="col">
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
  $cont2=0;
  while($row = sqlsrv_fetch_array($result)){
  	$cont2++;
    echo "<tr><td>".$cont2."</td>";
    echo "<td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row['puestonom']."</td></tr>";
    }
?>
</tbody>
</table>
</div>
Total Restantes: <span class='fw-bold'><?php echo $cont2 ?> </span><br>
<a style="float: right;" href="php/crearexcel.php?opcion=2&query=<?php echo $query ?>">Crear excel</a>
</div>
</div>
<div class="row">
  <div class="col">
    <?php 
   $query = "SELECT tblPuestos.nombre FROM tblSubCursosXPuesto INNER JOIN tblCursos ON tblCursos.IdCurso=tblSubCursosXPuesto.IdCurso INNER JOIN TLX009MXDB.dbo.tblPuestos on tblPuestos.id=tblSubCursosXPuesto.IdPuesto WHERE tblCursos.IdCurso=$curso";
  $result = sqlsrv_query($conn, $query);
   ?>

<h6>Puestos seleccionados para tomar este curso</h6>
 <div class="table-responsive" style="height:300px">
 <table class="table table-hover table-sm" id="tblseleccion">
  <thead class="table-dark">
      <th>No.</th>
      <th>Puesto</th>
    </thead>
    <tbody>
  <?php 
  $contpues=0;
  while($row = sqlsrv_fetch_array($result)){
    $contpues++;
    echo "<tr><td>".$contpues."</td>";
    echo "<td>".$row[0]."</td>";
    }
?>
</tbody>
</table>
</div>
  </div>
  <div class="col-7">
    <div id="contdepart" style="width: 100%; height: 400px;"></div>
  </div>
</div>
<div class="row">
<h5>Total horas por mes</h5>
    <div class="col">
      <?php  
        $query="
   SELECT TLX009MXDB.dbo.tblDepartamentos.NombreDepto,sum(tblEncabezadoCapturaCapacitacion.DuracionReal) FROM TLX009MXDB.dbo.tblDepartamentos
    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.".$tipondep."=tblDepartamentos.NoDepto 
  INNER JOIN tblSubEncabCapturaCapacitacion ON tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp 
  INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura=tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura WHERE FORMAT(tblEncabezadoCapturaCapacitacion.FechaFinal,'yyyy-MM') = '".$fecha."' GROUP BY TLX009MXDB.dbo.tblDepartamentos.NombreDepto";
        $stpm=sqlsrv_query($conn,$query);
       ?>
       <div class="table-responsive" style="height:300px">
        <table class="table table-hover table-sm" id="tblseleccion">
          <thead class="table-dark" >
              <th>Departamento</th>
              <th>Horas</th>
            </thead>
            <tbody>
          <?php 
          $cont=0;
          while($row = sqlsrv_fetch_array($stpm)){
            $horas=($row[1]/60);
            echo "<tr><td>".$row[0]."</td>";
            echo "<td>".number_format($horas,2)." horas</td>";
            }
        ?>
        </tbody>
        </table>
        </div>
    </div>
</div>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Task', 'Hours per Day'],
          ['Asistencias',<?php echo $contasis ?>],
          ['Restantes',<?php echo $cont2 ?>],
        ]);

        var options = {
          title: 'Asistencias al curso',
          legend: { position: "bottom" }
        }
        var chart = new google.visualization.PieChart(document.getElementById('Asistencias'));
        chart.draw(data, options);
      }
    </script>
      <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Empleados', 'Número de registros al curso', { role: 'style' }],
          <?php
           $query = "SELECT tblDepartamentos.NombreDepto,count(distinct tblEmpleados.NoEmp) as logincount FROM TLX009MXDB.dbo.tblDepartamentos INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.".$tipondep." = tblDepartamentos.NoDepto INNER JOIN tblSubEncabCapturaCapacitacion on tblSubEncabCapturaCapacitacion.NoEmp=tblEmpleados.NoEmp INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura=tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura INNER JOIN tblCursos ON tblCursos.IdCurso=tblEncabezadoCapturaCapacitacion.IdCurso WHERE (tblCursos.IdCurso=$curso AND tblEmpleados.Bajas=0  AND tblSubEncabCapturaCapacitacion.Contestado=1 $addin $addreano) AND (EXISTS (SELECT tblEmpleados.NoEmp FROM tblSubCursosXPuesto WHERE  tblSubCursosXPuesto.IdPuesto=tblEmpleados.Puesto AND tblSubCursosXPuesto.IdCurso=$curso)) GROUP BY tblDepartamentos.NombreDepto";
        $result = sqlsrv_query($conn, $query);
            while($row = sqlsrv_fetch_array($result)){
        $randomcolor = '#' . dechex(rand(0,10000000));
        echo "['".$row[0]."',".$row[1].",'". $randomcolor."'],";
        }   
        ?> 
        ]);
        var options = {
          title: 'Asistencias por departamento al curso seleccionado',
          legend: { position: "bottom" },
          pieHole: 0.4,
        }     
        var chart = new google.visualization.ColumnChart(document.getElementById('contdepart'));
        chart.draw(data, options);
      }
    </script>