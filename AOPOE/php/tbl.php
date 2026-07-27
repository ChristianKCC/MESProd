<?php 	
if(isset($_GET['tblenc'])){
	require_once "../../../csql35.php";
	$query="SELECT TOP 200 tblAOPOEEncIT.id,tblEmpleados.NoEmp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblMaquinas.NombreMaquina,tblAOPOEinvoperaciones.tipoactividad,tblemp2.NoEmp as cap,tblemp2.Nombre as capnom,fecha,duracion FROM tblAOPOEEncIT 
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblAOPOEEncIT.noemp
INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto=tblAOPOEEncIT.departamento
INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina=tblAOPOEEncIT.maquina
INNER JOIN tblAOPOEinvoperaciones ON tblAOPOEinvoperaciones.id=tblAOPOEEncIT.POE
INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblemp2 ON tblemp2.NoEmp=tblAOPOEEncIT.capacitador ORDER BY tblAOPOEEncIT.id DESC";
	$result=sqlsrv_query($conn,$query);
	?>
	<div class="table-responsive" style="height:500px;">
	<table class="table table-sm table-bordered">	
		<thead class="table-dark">
			<th>ID</th>
			<th>Noemp</th>
			<th>Nombre</th>
			<th>Departamento</th>
			<th>Máquina</th>
			<th>POE</th>
			<th>Cap</th>
			<th>Nombre</th>
			<th>Fecha</th>
			<th>Duración</th>
			<th class="text-center"><i class="fa-solid fa-chevron-down"></i></th>
		</thead>
		<tbody>
	<?php 	
	while($row = sqlsrv_fetch_array($result)){
			echo "<tr><td>".$row['id']."</td>";
			echo "<td>".$row['NoEmp']."</td>";
			echo "<td>".$row['Nombre']."</td>";
			echo "<td>".$row['NombreDepto']."</td>";
			echo "<td>".$row['NombreMaquina']."</td>";
			echo "<td>".$row['tipoactividad']."</td>";
			echo "<td>".$row['cap']."</td>";
			echo "<td>".$row['capnom']."</td>";
			echo "<td>".$row['fecha']->format("Y-m-d")."</td>";
			echo "<td>".$row['duracion']."</td>";
			echo "<td><button onclick='edit(".$row['id'].")' class='btn btn-sm btn-warning'><i class='fa-regular fa-pen-to-square'></i></button>
			<button onclick='deleteenc(".$row['id'].")' class='btn btn-sm btn-danger'><i class='fa-solid fa-delete-left'></i></button></td></tr>";
	}
	?>
		</tbody>
	</table>
</div>
	<?php 	
}
?>