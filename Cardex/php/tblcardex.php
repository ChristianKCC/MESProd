<?php 
require_once "../../../csql32.php";
$inputbuscar=$_POST['buscarcardex'];
$query="pa_P009_04300_sLlenardgvNombreCardex_02 '".$inputbuscar."'";
$result=sqlsrv_query($conn,$query);
$obsoleto="";
 ?>
 <div class="table-responsive">
 <table class="table table-hover table-sm table-striped">
 	<thead>
 		<th>Folio</th>
 		<th>Nombre</th>
 		<th>No. depto</th>
 		<th>Obsoleto</th>
 		<th>Editar</th>
 		<th>Borrar</th>
 	</thead>
 	<tbody>
 		<?php 
 		while ($fila=sqlsrv_fetch_array($result)) {
 		if($fila['CardexObsoleto']==1)
 		$obsoleto="Si";
 		else
 		$obsoleto="No";	
 		echo "<tr><td>".$fila[0]."</td>";
 		echo "<td>".$fila[1]."</td>";
 		echo "<td>".$fila[3]."</td>";
 		echo "<td>".$obsoleto."</td>";
 		echo "<td><button class='btn btn-info btn-sm' onclick='cardexedit($fila[0])'><i class='far fa-edit'></i></button></td>";
 		echo "<td><button class='btn btn-danger btn-sm' onclick='cardexdelete($fila[0])'><i class='far fa-trash-alt'></i></button></td></tr>";
		}
 		 ?>
 	</tbody>
 </table>
</div>