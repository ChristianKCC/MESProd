<?php 
if(isset($_GET['datosemp'])){
	require_once "../../../csql32.php";
	$noemp=$_POST["noemp"];
	$query="SELECT tblEmpleados.Nombre,tblPuestos.nombre as puestonom FROM tblEmpleados INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblEmpleados.puesto WHERE NoEmp=$noemp";
	$result=sqlsrv_query($conn,$query);
	while($row = sqlsrv_fetch_array($result)){
		$nombre=$row['Nombre'];
		$puesto=$row['puestonom'];
	}
	if(!empty($nombre)){
	?>
	<script type="text/javascript">
		$("#nombre").val('<?php echo "$nombre" ?>');
	</script>
	<?php 
	}else{
	?>
	<script type="text/javascript">
		$("#nombre").val('');
	</script>
	<?php 
	echo "No hay coincidencias";
	}
	sqlsrv_close($conn);
}else if(isset($_GET['datosempcap'])){
	require_once "../../../csql32.php";
	$noemp=$_POST["noemp"];
	$query="SELECT tblEmpleados.Nombre,tblDepartamentos.NombreDepto FROM tblEmpleados INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento WHERE NoEmp=$noemp";
	$result=sqlsrv_query($conn,$query);
	while($row = sqlsrv_fetch_array($result)){
		$nombre=$row['Nombre'];
	}
	if(!empty($nombre)){
	?>
	<script type="text/javascript">
		$("#nombrecap").val('<?php echo "$nombre" ?>');
	</script>
	<?php 
	}else{
	?>
	<script type="text/javascript">
		$("#nombrecap").val('');
	</script>
	<?php 
	echo "No hay coincidencias";
	}
	sqlsrv_close($conn);
}else if(isset($_GET['slctipo'])){
		require_once "../../../csql35.php";
		$query="SELECT * FROM tblTipoPlatica5min";
		$result=sqlsrv_query($conn,$query);
		echo "<option value=''>Elige el tipo</option>";
		while($row = sqlsrv_fetch_array($result)){
		echo "<option value='".$row['id']."'>".$row['nombre']."</option>";
	}
	sqlsrv_close($conn);
}
else if(isset($_GET['POE'])){
		require_once "../../../csql35.php";
		$poeid=$_POST['poeid'];
		// Realiza una consulta a la BD para obtener todos los valores donde haya coincidencia
		$query="SELECT * FROM tblAOPOEinvoperaciones WHERE procedimiento LIKE '%".$poeid."%' ORDER BY tipoactividad ASC";
		$result=sqlsrv_query($conn,$query);
		while($row = sqlsrv_fetch_array($result)){
		echo "<option value='".$row['id']."'>".$row[1]." - ".$row[2]."</option>";
	}
	sqlsrv_close($conn);
}else if(isset($_GET['departamento'])){
		require_once "../../../csql.php";
		// Selecciona todo de la tabla
		// Se modifico aqui para no mostrar el departamento de Administrativo en los departamentos reales
		$query="SELECT * FROM tblDepartamentos WHERE NombreDepto <> 'Administrativo' ORDER BY NombreDepto ASC";
		// almacena como un array dentro de una nueva variable
		$result=sqlsrv_query($conn,$query);
		echo "<option value=''>Elige el Departamento</option>";
		while($row = sqlsrv_fetch_array($result)){
		echo "<option value='".$row[0]."'>".$row[1]."</option>";
	}
	sqlsrv_close($conn);
}else if(isset($_GET['clasificacion'])){
		require_once "../../../csql35.php";
		$id=$_POST["id"];
		// Realiza la consulta para obtener informacion de la tabla mediante una condicion
		$query="SELECT tblAOPOEclasif.nombre FROM tblAOPOEinvoperaciones INNER JOIN tblAOPOEclasif ON tblAOPOEclasif.id=tblAOPOEinvoperaciones.critico WHERE tblAOPOEinvoperaciones.id='".$id."'";
		$result=sqlsrv_query($conn,$query);
		while($row = sqlsrv_fetch_array($result)){
		echo $row[0];
		}
	sqlsrv_close($conn);
}else if(isset($_GET['puesto'])){
		require_once "../../../csql.php";
		$query="SELECT * FROM tblPuestos";
		$result=sqlsrv_query($conn,$query);
		echo "<option value=''>Elige el Puesto</option>";
		while($row = sqlsrv_fetch_array($result)){
		echo "<option value='".$row[0]."'>".$row[1]."</option>";
		}
	sqlsrv_close($conn);
}

?>
