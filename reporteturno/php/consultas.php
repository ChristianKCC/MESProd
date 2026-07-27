<?php 
require_once "../../Session/seguridad.php";
if (isset($_GET['contulta'])) {
	?>
	<div class="row">
	<div class="col-12">
		<div class="table-responsive" style="height:400px;">
			<table class="table table-sm table-striped">	
				<thead class="table-dark">
					<th>Folio</th>
					<th>Noemp</th>
					<th>Fecha</th>
					<th>Turno</th>
					<th>View</th>
				</thead>
				<tbody>
					<?php 
					require_once "../../../csql.php";
					$fechaswhere='';
					if (!empty($_POST['fechai']) || !empty($_POST['fechaf'])) {
					 $fechai=$_POST['fechai'];
					 $fechaf=$_POST['fechaf'];
					 $fechaswhere="AND tblRepmtto.fecha >= '$fechai' AND tblRepmtto.fecha < DATEADD(day,1,'$fechaf')";
					}
					$query="SELECT tblRepmtto.*, tblEmpleados.Nombre FROM tblRepmtto INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblRepmtto.ibm WHERE tblRepmtto.terminado=1 $fechaswhere ORDER BY tblRepmtto.id DESC";
					$result=sqlsrv_query($conn,$query);
					while ($fila = sqlsrv_fetch_array($result)) {
						echo "<tr>";
						echo "<td>".$fila[0]."</td>";
						echo "<td>".$fila[1]." - ".$fila[6]."</td>";
						echo "<td>".$fila[2]->format("Y-m-d")."</td>";
						echo "<td>".$fila[3]."</td>";
						echo "<td><button class='btn bg-target btn-sm' onclick='view(".$fila[0].")' title='Editar'><i class='fa-solid fa-eye'></i></button></td>";
						echo "</tr>";
					}
					 ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
	<?php 
}
?>