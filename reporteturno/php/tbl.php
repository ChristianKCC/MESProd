<?php 
require_once "../../Session/seguridad.php";
if(isset($_GET['enc'])){
require_once "../../../csql.php";
$query="SELECT tblRepmtto.*, tblEmpleados.Nombre FROM tblRepmtto INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblRepmtto.ibm WHERE (tblRepmtto.terminado=0 AND tblRepmtto.ibm='".$_SESSION['ibm']."')";
$result=sqlsrv_query($conn,$query);
?>
<div class="table-responsive" style="height:300px;">
<table class="table table-hover table-sm">
	<thead class="table-dark">
		<th>Folio</th>
		<th>Noemp</th>
		<th>Fecha</th>
		<th>Turno</th>
		<th>Edit/borrar/view</th>
	</thead>
	<tbody>

<?php 
while ($fila = sqlsrv_fetch_array($result)) {
	echo "<tr>";
	echo "<td>".$fila[0]."</td>";
	echo "<td>".$fila[1]." - ".$fila[6]."</td>";
	echo "<td>".$fila[2]->format("Y-m-d")."</td>";
	echo "<td>".$fila[3]."</td>";
	echo "<td><button class='btn btn-warning btn-sm' onclick='editenc(".$fila[0].")' title='Editar'><i class='fa-solid fa-pen-to-square'></i></button> <button class='btn btn-danger btn-sm' onclick='eliminarenc(".$fila[0].")' title='Borrar'><i class='fas fa-trash-alt'></i></button> <button class='btn bg-target btn-sm' onclick='view(".$fila[0].")' title='Editar'><i class='fa-solid fa-eye'></i></button></td>";
	echo "</tr>";
}
 ?>
	</tbody>
</table>
</div>
<?php 
sqlsrv_close($conn);
}

else if(isset($_GET['tblri'])){
require_once "../../../csql.php";
$id=$_POST['id'];
$query="SELECT tblRepmttori.*, tblEmpleados.Nombre FROM tblRepmttori INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblRepmttori.noemp WHERE tblRepmttori.folioenc=$id";
$result=sqlsrv_query($conn,$query);
?>
<div class="table-responsive" style="height:300px;">
<table class="table table-hover table-sm">
	<thead class="table-dark">
		<th>Folio</th>
		<th>Noemp</th>
		<th>Borrar</th>
	</thead>
	<tbody>
<?php 
while ($fila = sqlsrv_fetch_array($result)) {
	echo "<tr>";
	echo "<td>".$fila[0]."</td>";
	echo "<td>".$fila[2]." - ".$fila[3]."</td>";
	echo "<td><button class='btn btn-danger btn-sm' onclick='eliminarri(".$fila[0].")' title='Borrar'><i class='fas fa-trash-alt'></i></button>";
	echo "</tr>";
}
 ?>
	</tbody>
</table>
</div>
<?php 
sqlsrv_close($conn);
}

else if(isset($_GET['tblpmecanicos'])){
require_once "../../../csql.php";
$id=$_POST['id'];
$query="SELECT tblRepmttopmecanicos.*,tblMaquinas.NombreMaquina,tblSecciones.NombreSeccion,tblRepmttotipopendiente.nombre FROM tblRepmttopmecanicos INNER JOIN tblMaquinas ON tblMaquinas.NoMaquina= tblRepmttopmecanicos.maquina INNER JOIN tblSecciones ON tblSecciones.NoSeccion=tblRepmttopmecanicos.seccion left JOIN tblRepmttotipopendiente ON tblRepmttotipopendiente.id=tblRepmttopmecanicos.tipopendiente WHERE tblRepmttopmecanicos.folioenc=$id";
$result=sqlsrv_query($conn,$query);
?>
<div class="table-responsive" style="height:300px;">
<table class="table table-hover table-sm">
	<thead class="table-dark">
		<th>Folio</th>
		<th>Maquina</th>
		<th>Sección</th>
		<th>tipo</th>
		<th>Pendiente</th>
		<th>Borrar</th>
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
	echo "<td><button class='btn btn-danger btn-sm' onclick='eliminarpm(".$fila[0].")' title='Borrar'><i class='fas fa-trash-alt'></i></button></td>";
	echo "</tr>";
}
 ?>
	</tbody>
</table>
</div>
<?php 
sqlsrv_close($conn);
}
else if(isset($_GET['tblcomentarios'])){
require_once "../../../csql.php";
$id=$_POST['id'];
$query="SELECT tblRepmttocomentarios.*,tblMaquinas.NombreMaquina FROM tblRepmttocomentarios INNER JOIN tblMaquinas ON tblMaquinas.NoMaquina= tblRepmttocomentarios.maquina WHERE tblRepmttocomentarios.folioenc=$id";
$result=sqlsrv_query($conn,$query);
?>
<div class="table-responsive" style="height:300px;">
<table class="table table-hover table-sm">
	<thead class="table-dark">
		<th>Folio</th>
		<th>Maquina</th>
		<th>Comentarios</th>
		<th>Borrar</th>
	</thead>
	<tbody>

<?php 
while ($fila = sqlsrv_fetch_array($result)) {
	echo "<tr>";
	echo "<td>".$fila[0]."</td>";
	echo "<td>".$fila[4]."</td>";
	echo "<td>".$fila[2]."</td>";
	echo "<td><button class='btn btn-danger btn-sm' onclick='eliminarco(".$fila[0].")' title='Borrar'><i class='fas fa-trash-alt'></i></button></td>";
	echo "</tr>";
}
 ?>
	</tbody>
</table>
</div>
<?php 
sqlsrv_close($conn);
}

else if(isset($_GET['tblparosmaquina'])){
	require_once "../../../csql.php";
	$id=$_POST['id'];
	$query="SELECT tblRepmttoparosmaquina.*,tblMaquinas.NombreMaquina,tblSecciones.NombreSeccion FROM tblRepmttoparosmaquina INNER JOIN tblMaquinas ON tblMaquinas.NoMaquina= tblRepmttoparosmaquina.maquina INNER JOIN tblSecciones ON tblSecciones.NoSeccion=tblRepmttoparosmaquina.seccion WHERE tblRepmttoparosmaquina.folioenc=$id";
	$result=sqlsrv_query($conn,$query);
	?>
	<div class="table-responsive" style="height:300px;">
	<table class="table table-hover table-sm">
		<thead class="table-dark">
			<th>Folio</th>
			<th>Maquina</th>
			<th>Sección</th>
			<th>hora</th>
			<th>tiemp</th>
			<th>Comen..</th>
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
	<?php 
	sqlsrv_close($conn);
	}
 ?>