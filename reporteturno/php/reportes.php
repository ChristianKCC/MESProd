<?php 
require_once "../../Session/seguridad.php";
if(isset($_GET['view'])){
	require_once "../../../csql.php";
	$id=$_POST['id'];
	?>
	<div class="modal-header my-2">
        <h5 class="text-center modal-title" id="exampleModalToggleLabel">REPORTE DE TURNO MANTENIMIENTO</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="text-center">
	<?php 
	$query="SELECT tblRepmtto.*, tblEmpleados.Nombre FROM tblRepmtto INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblRepmtto.ibm WHERE tblRepmtto.id=$id";
	$result=sqlsrv_query($conn,$query);
	while ($fila = sqlsrv_fetch_array($result)) {
		echo "<span class='h5 fw-bold'>Información del encabezado</span><br>";
		echo "<span class='fw-bold'>Folio turno: $id Turno: $fila[3] Fecha: ".$fila[2]->format("Y-m-d")." Cargado por: $fila[1] - $fila[6]</span>";
	}
	$query="SELECT tblRepmttori.*, tblEmpleados.Nombre FROM tblRepmttori INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblRepmttori.noemp WHERE tblRepmttori.folioenc=$id";
	$result=sqlsrv_query($conn,$query);
	?>
	</div>
	<div class="row">
	<div class="col-6">
	<h6>Relaciones Industriales</h6>
	<div class="table-responsive" style="height:300px;">
	<table class="table table-hover table-sm">
		<thead class="table-dark">
			<th>Folio</th>
			<th>Noemp</th>
		</thead>
		<tbody>
	<?php 
	while ($fila = sqlsrv_fetch_array($result)) {
		echo "<tr>";
		echo "<td>".$fila[0]."</td>";
		echo "<td>".$fila[2]." - ".$fila[3]."</td>";
		echo "</tr>";
	}
	 ?>
		</tbody>
	</table>
	</div>
	</div>
	<div class="col-6">
	<h6>Pendientes Mecanicos</h6>
		<?php 
		$query="SELECT tblRepmttopmecanicos.*,tblMaquinas.NombreMaquina,tblSecciones.NombreSeccion,tblRepmttotipopendiente.nombre FROM tblRepmttopmecanicos INNER JOIN tblMaquinas ON tblMaquinas.NoMaquina= tblRepmttopmecanicos.maquina INNER JOIN tblSecciones ON tblSecciones.NoSeccion=tblRepmttopmecanicos.seccion left JOIN tblRepmttotipopendiente ON tblRepmttotipopendiente.id=tblRepmttopmecanicos.tipopendiente WHERE tblRepmttopmecanicos.folioenc=$id";
		$result=sqlsrv_query($conn,$query);
		?>
		<div class="table-responsive" style="height:300px;">
		<table class="table table-hover table-sm">
			<thead class="table-dark">
				<th>Folio</th>
				<th>Maquina</th>
				<th>Sección</th>
				<th>Tipo</th>
				<th>Pendiente</th>
			</thead>
			<tbody>

		<?php 
		while ($fila = sqlsrv_fetch_array($result)) {
			echo "<tr>";
			echo "<td>".$fila[0]."</td>";
			echo "<td>".$fila[6]."</td>";
			echo "<td>".$fila[7]."</td>";
			echo "<td>".$fila[8]."</td>";
			echo "<td>".$fila[4]."</td>";
			echo "</tr>";
		}
		 ?>
	</tbody>
	</table>
	</div>
	</div>
	<div class="col-6">
	<h6>Comentarios</h6>
	<?php 
	$query="SELECT tblRepmttocomentarios.*,tblMaquinas.NombreMaquina FROM tblRepmttocomentarios INNER JOIN tblMaquinas ON tblMaquinas.NoMaquina= tblRepmttocomentarios.maquina WHERE tblRepmttocomentarios.folioenc=$id";
		$result=sqlsrv_query($conn,$query);
		?>
		<div class="table-responsive" style="height:300px;">
		<table class="table table-hover table-sm">
			<thead class="table-dark">
				<th>Folio</th>
				<th>Maquina</th>
				<th>Comentarios</th>
			</thead>
			<tbody>

		<?php 
		while ($fila = sqlsrv_fetch_array($result)) {
			echo "<tr>";
			echo "<td>".$fila[0]."</td>";
			echo "<td>".$fila[4]."</td>";
			echo "<td>".$fila[2]."</td>";
			echo "</tr>";
		}
		 ?>
			</tbody>
		</table>
		</div>
	</div>
	<div class="col-6">
	<h6>Paros de maquina</h6>
		<?php 
		$query="SELECT tblRepmttoparosmaquina.*,tblMaquinas.NombreMaquina,tblSecciones.NombreSeccion FROM tblRepmttoparosmaquina INNER JOIN tblMaquinas ON tblMaquinas.NoMaquina= tblRepmttoparosmaquina.maquina INNER JOIN tblSecciones ON tblSecciones.NoSeccion=tblRepmttoparosmaquina.seccion WHERE tblRepmttoparosmaquina.folioenc=$id";
		$result=sqlsrv_query($conn,$query);
		?>
		<div class="table-responsive" style="height:300px;">
		<table class="table table-hover table-sm">
			<thead class="table-dark">
				<th>Folio</th>
				<th>Maquina</th>
				<th>Sección</th>
				<th>Hora</th>
				<th>T_Paro</th>
				<th>Comentarios</th>
			</thead>
			<tbody>

		<?php 
		while ($fila = sqlsrv_fetch_array($result)) {
			echo "<tr>";
			echo "<td>".$fila[0]."</td>";
			echo "<td>".$fila[7]."</td>";
			echo "<td>".$fila[8]."</td>";
			echo "<td>".$fila[3]->format("H:i:s")."</td>";
			echo "<td>".$fila[4]."</td>";
			echo "<td>".$fila[5]."</td>";
			echo "</tr>";
		}
		 ?>
	</tbody>
	</table>
	</div>
	</div>
	</div>
		<a href="pdf/crearpdf.php?id=<?php echo $id; ?>" class="btn btn-danger">Generar pdf</a>
	<?php 
	}
 	?>