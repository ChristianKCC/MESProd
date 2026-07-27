<?php
include '../../../csql32.php';
if (isset($_GET['conseptosindex'])) {
	$addquery='';
	if(isset($_POST['conseptos'])){
		$conseptos=$_POST['conseptos'];
		for($i=0; $i<count($conseptos); $i++){
		if($addquery==''){
		$addquery.=" WHERE (tblCTAgastosSubencta.idconsepto=".$conseptos[$i]." ";
		}else{
		if($i==0)$andor='and (';else$andor='or';
		$addquery.=" $andor tblCTAgastosSubencta.idconsepto=".$conseptos[$i]." ";
		}
	}$addquery.=")";
	}
	if(isset($_POST['ctrocostos'])){
		$ctrocostos=$_POST['ctrocostos'];
		for($i=0; $i<count($ctrocostos); $i++){
		if($addquery==''){
		$addquery.=" WHERE (tblCTAgastosEncabezadocta.ctrocostos=".$ctrocostos[$i]." ";
		}else{
			if($i==0)$andor='and (';else$andor='or';
			$addquery.=" $andor tblCTAgastosEncabezadocta.ctrocostos=".$ctrocostos[$i]." ";
		}
	}$addquery.=")";
	}
	if(isset($_POST['estado'])){
		$estado=$_POST['estado'];
		for($i=0; $i<count($estado); $i++){
		if($addquery==''){
		$addquery.=" WHERE (tblCTAgastosEncabezadocta.estado=".$estado[$i]." ";
		}else{
			if($i==0)$andor='and (';else$andor='or';
			$addquery.=" $andor tblCTAgastosEncabezadocta.estado=".$estado[$i]." ";
		}
	  }$addquery.=")";
	}
	$query="SELECT tblCTAgastosSubencta.*,tblCTAgastosConceptos.nombre as nombreconcepto,tblCTAgastosEncabezadocta.*,tblCTAgastosEstado.nombre as estadonom FROM tblCTAgastosSubencta INNER JOIN tblCTAgastosConceptos on tblCTAgastosConceptos.id=tblCTAgastosSubencta.idconsepto INNER JOIN tblCTAgastosEncabezadocta ON tblCTAgastosEncabezadocta.id=tblCTAgastosSubencta.folio INNER JOIN tblCTAgastosEstado on tblCTAgastosEstado.id= tblCTAgastosEncabezadocta.estado $addquery";
	$result=sqlsrv_query($conn,$query);
	?>

<div class="table-responsive" style="height:500px;">
	<table class="table  table-sm">
		<thead class="table-dark">
			<th>Folio</th>
			<th>Cuenta</th>
			<th>Concepto</th>
			<th>Importe</th>
			<th>IVA</th>
			<th>XML</th>
			<th>Descripción</th>
			<th>Archivo</th>
			<th>Fecha</th>
			<th>IBM</th>
			<th>Cuenta</th>
			<th>Estado</th>
		</thead>
		<tbody>
	<?php 
	while ($row=sqlsrv_fetch_array($result)) {
		echo "<tr>";
		echo "<td>$row[0]</td>";
		echo "<td>$row[1]</td>";
		echo "<td>$row[9]</td>";
		echo "<td>$$row[3]</td>";
		echo "<td>$$row[4]</td>";
		echo "<td>$row[5]</td>";
		echo "<td>$row[6]</td>";
		echo "<td>$row[8]</td>";
		echo "<td>".$row[7]->format('Y/m/d')."</td>";
		echo "<td>$row[11]</td>";
		echo "<td>$row[12]</td>";
		echo "<td>$row[estadonom]</td>";
		echo "</tr>";
	}
?>
		</tbody>
	</table>
</div>
	<?php 
 $query="SELECT DISTINCT tblCTAgastosEncabezadocta.*,tblCTAgastosEstado.nombre, tblEmpleados.Nombre as nombemp FROM tblCTAgastosEncabezadocta INNER JOIN tblCTAgastosEstado on tblCTAgastosEstado.id= tblCTAgastosEncabezadocta.estado  INNER JOIN tblCTAgastosSubencta ON tblCTAgastosSubencta.folio=tblCTAgastosEncabezadocta.id INNER JOIN tblEmpleados on tblEmpleados.NoEmp= tblCTAgastosEncabezadocta.noemp $addquery";
	$result=sqlsrv_query($conn,$query);
	?>
<div class="table-responsive" style="height:250px;">
	<table class="table table-sm">
		<thead class="table-dark">
			<th>Folio</th>
			<th>IBM</th>
			<th>Nombre</th>
			<th>Estado</th>
			<th>Cuenta</th>
			<th>Fecha</th>
			<th>Mostrar</th>
		</thead>
		<tbody>
	<?php 
	while ($row=sqlsrv_fetch_array($result)) {
		echo "<tr>";
		echo "<td>$row[0]</td>";
		echo "<td>$row[1]</td>";
		echo "<td>$row[7]</td>";
		echo "<td>$row[6]</td>";
		echo "<td>$row[2]</td>";
		echo "<td>".$row[4]->format('Y/m/d')."</td>";
		echo "<td><a href='#' onclick='configcta($row[0])' class='text-primary'><i class='fa-solid fa-street-view'></i></a></td>";
		echo "</tr>";
	}
?>
		</tbody>
	</table>
</div>
<?php } ?>
	<div class="modal fade" id="modalviewcta" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Revisa la información</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div id="result"></div>
      </div>
    </div>
  </div>
</div>