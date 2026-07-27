<?php 
require_once "../../Session/seguridad.php";
if(isset($_GET['editenc'])){
	require_once "../../../csql.php";
	$id=$_POST['id'];
	$query="SELECT * FROM tblRepmtto WHERE id=$id";
	$result=sqlsrv_query($conn,$query);
	while($fila = sqlsrv_fetch_array($result)){
		$id=$fila[0];
		$turno=$fila[3];
		$deps=$fila[5];
	}
	sqlsrv_close($conn);
	?>
	<script type="text/javascript">
		$("#folio").val("<?php echo $id; ?>");
		$("#turnoenc").val("<?php echo $turno; ?>").change();
		$("#deps").val("<?php echo $deps; ?>").change();
	</script>
	<?php 
}
else if(isset($_GET['editencguardar'])){
	require_once "../../../csql.php";
	$id=$_POST['id'];
	$turno=$_POST['turno'];
	$deps=$_POST['deps'];
	$query="UPDATE tblRepmtto SET turno='".$turno."',departamento='".$deps."' WHERE id=$id";
    sqlsrv_query($conn,$query);
	echo "<div class='alert alert-success'>Se actualizo correctamente la infomación</div>";
	sqlsrv_close($conn);
}
else if(isset($_GET['deleteenc'])){
	require_once "../../../csql.php";
	$id=$_POST['id'];
	$query="DELETE FROM tblRepmtto WHERE id=$id";
	sqlsrv_query($conn,$query);
	echo "<div class='alert alert-danger'>Se elimino correctamente el reporte</div>";
	sqlsrv_close($conn);
}

else if(isset($_GET['finalizarrep'])){
	require_once "../../../csql.php";
	$id=$_POST['id'];
	$query="UPDATE tblRepmtto SET terminado=1 WHERE id=$id";
	sqlsrv_query($conn,$query);
	echo "<div class='alert alert-success'>Se envio el reporte</div>";
	sqlsrv_close($conn);
}

else if(isset($_GET['deleteri'])){
	require_once "../../../csql.php";
	$id=$_POST['id'];
	$query="DELETE FROM tblRepmttori WHERE id=$id";
	sqlsrv_query($conn,$query);
	sqlsrv_close($conn);
}

else if(isset($_GET['deletepm'])){
	require_once "../../../csql.php";
	$id=$_POST['id'];
	$query="DELETE FROM tblRepmttopmecanicos WHERE id=$id";
	sqlsrv_query($conn,$query);
	echo "<div class='alert alert-danger'>Se elimino correctamente el registro</div>";
	sqlsrv_close($conn);
}

else if(isset($_GET['deleteco'])){
	require_once "../../../csql.php";
	$id=$_POST['id'];
	$query="DELETE FROM tblRepmttocomentarios WHERE id=$id";
	sqlsrv_query($conn,$query);
	echo "<div class='alert alert-danger'>Se elimino correctamente el registro</div>";
	sqlsrv_close($conn);
}
 ?>
