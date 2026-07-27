<?php
include '../../../csql.php';
$query="SELECT tblDepartamentos.NombreDepto as componente,COUNT(*) as contado FROM TLX006MXDB.dbo.tblEncabezadoCapaweb LEFT JOIN tblDepartamentos ON tblDepartamentos.NoDepto= TLX006MXDB.dbo.tblEncabezadoCapaweb.NoDepto GROUP BY tblDepartamentos.NombreDepto";
  $result = sqlsrv_query($conn, $query);
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Departamentos', 'Creadas'],
          <?php 
          while ($row = sqlsrv_fetch_array($result)) {
           echo "['".$row[0]."',".$row[1]."],";
          }
           ?>
        ]);
        var options = {
         legend:'left',
          title: 'Capa cargadas por departamento',
          pieHole: .4,
          theme: 'material',
          height: 400,
          opacity: 1,
        };

        var chart = new google.visualization.PieChart(document.getElementById('grafica3'));
        chart.draw(data, options);
      }
    </script>
<?php
include '../../../csql.php';
$query="SELECT tblCapaTipoAccion.nombre as tipoaccion,COUNT(*) as contador FROM tblCapaAcciones LEFT JOIN tblCapaTipoAccion ON tblCapaTipoAccion.id = tblCapaAcciones.tipodeaccion GROUP BY tblCapaTipoAccion.nombre";
  $result = sqlsrv_query($conn, $query);
?>
     <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = google.visualization.arrayToDataTable([
          ['Accion', 'Numero de veces'],
         <?php 
          while ($row = sqlsrv_fetch_array($result)) {
           echo "['".$row[0]."',".$row[1]."],";
          }
          ?>
        ]);

        var options = {
          title: 'Total Acciones Correctivas Preventivas',
          theme: 'material',
          colors: ['#e0440e', '#e6693e'],
          height: 400,
          opacity: 1,
        };

        var chart = new google.visualization.PieChart(document.getElementById('chart1'));

        chart.draw(data, options);
      }
    </script>

    <?php
include '../../../csql.php';
$query="SELECT COUNT(*) AS Total,(SELECT COUNT(*) FROM tblCapaAcciones WHERE tblCapaAcciones.accioncompleta=1) AS Restantes FROM tblCapaAcciones";
  $result = sqlsrv_query($conn, $query);
?>
     <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = google.visualization.arrayToDataTable([
          ['Accion', 'Numero de veces'],
         <?php 
          while ($row = sqlsrv_fetch_array($result)) {
           echo "['Total',".$row[0]."],";
           echo "['Restantes',".$row[1]."],";
          }
          ?>
        ]);

        var options = {
          title: 'Acciones Correctivas y preventivas pendientes.',
          theme: 'material',
          colors: ['#129490', '#f18701'],
          height: 400,
          opacity: 1,
          is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('chart2'));

        chart.draw(data, options);
      }
    </script>
 
 