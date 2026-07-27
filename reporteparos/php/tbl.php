<?php 
if(isset($_GET["tblenc"])){
 ?>
<div class="table-responsive" style="height: 400px;">
<table class="table table-sm table-hover">	
<thead class="table-dark">
	<th>Fecha</th>
	<th>Hora</th>
	<th>No.</th>
	<th>Alarma</th>
	<th>Comentario</th>
	<th>Turno</th>
	<th>Tipo</th>
</thead>
<tbody>
<?php 
include "../../../csql.php";
$fechaswhere='';
$andwhere='WHERE';
if (!empty($_POST['fechai']) || !empty($_POST['fechaf'])) {
 $fechai=$_POST['fechai'];
 $fechaf=$_POST['fechaf'];
 $fechaswhere="WHERE tblmaquinamp15.DateAndTime >= '$fechai' AND tblmaquinamp15.DateAndTime < DATEADD(day,1,'$fechaf')";
} 
if($fechaswhere!=""){
$andwhere='AND';
}
$query="SELECT TOP 200 * FROM tblmaquinamp15 LEFT JOIN tblmaquinamp15alertas on tblmaquinamp15alertas.id= tblmaquinamp15.AlarmaNo $fechaswhere ORDER BY DateAndTime DESC";
$result=sqlsrv_query($conn,$query);
$turno='';
while ($row=sqlsrv_fetch_array($result)) {
	if($row[2]!=55555){
	echo "<tr>";
	echo "<td>".$row["DateAndTime"]->format('Y-m-d')."</td>";
	echo "<td>".$row["DateAndTime"]->format('H:i:s')."</td>";
	echo "<td>".$row["AlarmaNo"]."</td>";
	echo "<td>".$row["nombrealarma"]."</td>";
	echo "<td>".$row["Comentarios"]."</td>";
	if($row["Turno"]==1)$turno='Primero';else if($row["Turno"]==2)$turno='Segundo';else$turno='Tercero';
	echo "<td>".$turno."</td>";
	echo '<td class="text-danger"><i class="fa-solid fa-arrow-trend-down"></i></td>';	
	echo "</tr>";
}
}
?>
</tbody>
</table>
</div>
<div class="row">
	<div class="col">
	<div id="alarmas_bar"></div>
	</div>
</div>
<div class="row">
	<div class="col">
	<div id="alarmas_pastel"></div>
	</div>
</div>
  <script type="text/javascript">
    google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable([
        ["Alarma", "Total", { role: "style" } ],
      	<?php 
		$query="SELECT tblmaquinamp15alertas.nombrealarma, COUNT(*) FROM tblmaquinamp15alertas RIGHT JOIN tblmaquinamp15 ON tblmaquinamp15.AlarmaNo= tblmaquinamp15alertas.id $fechaswhere $andwhere tblmaquinamp15.AlarmaNo <> 55555 GROUP BY tblmaquinamp15alertas.nombrealarma ";
			$result=sqlsrv_query($conn,$query);
			while ($row=sqlsrv_fetch_array($result)) {
			echo "['".$row[0]."',".$row[1].",'#70c1b3'],";
		 	}
		 ?>
      ]);

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        title: "Alarmas de paros de maquina",
        width: '80%',
        height: 500,
        bar: {groupWidth: "50%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("alarmas_bar"));
      chart.draw(view, options);
  }
  </script>


    <script type="text/javascript">
    google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable([
        ["Alarma", "Total"],
      	<?php 
		$query="SELECT tblmaquinamp15alertas.nombrealarma, COUNT(*) FROM tblmaquinamp15alertas RIGHT JOIN tblmaquinamp15 ON tblmaquinamp15.AlarmaNo= tblmaquinamp15alertas.id $fechaswhere $andwhere tblmaquinamp15.AlarmaNo <> 55555 GROUP BY tblmaquinamp15alertas.nombrealarma ";
			$result=sqlsrv_query($conn,$query);
			while ($row=sqlsrv_fetch_array($result)) {
			echo "['".$row[0]."',".$row[1]."],";
		 	}
		 ?>
      ]);

  

      var options = {
        title: "Alarmas de paros de maquina",
        width: '80%',
        height: 500
      };
      var chart = new google.visualization.PieChart(document.getElementById("alarmas_pastel"));
      chart.draw(data, options);
  }
  </script>

<?php 
}
 ?>