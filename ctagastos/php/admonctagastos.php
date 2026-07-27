<?php
 include '../../../csql32.php';
 $id=$_POST['id'];
 $query="SELECT tblCTAgastosEncabezadocta.*,tblEmpleados.Nombre,tblCTAgastosCentroCostos.nombre as centrocostos,tblCTAgastosregMonedas.nombre, tblCTAgastosEncabezadocta.anticipo as anticipo FROM tblCTAgastosEncabezadocta INNER JOIN tblEmpleados on tblEmpleados.NoEmp=tblCTAgastosEncabezadocta.Noemp INNER JOIN tblCTAgastosCentroCostos ON tblCTAgastosCentroCostos.id=tblCTAgastosEncabezadocta.ctrocostos INNER JOIN tblCTAgastosregMonedas ON tblCTAgastosregMonedas.id=tblCTAgastosEncabezadocta.tipomoneda WHERE tblCTAgastosEncabezadocta.id=$id";
$resultado=sqlsrv_query($conn,$query);
$anticipo=0;
$usuario='';
while ($row = sqlsrv_fetch_array($resultado)) {
 $anticipo=$row['anticipo'];
 $usuario=$row[7];
 ?>
<h4>Folio: <?php echo $id; ?></h4>
<div class="row">
    <div class="col">
      <h6>Empleado: <?php echo $row[1]." - ".$row[7]; ?></h6>
      <h6>Centro de costos: <?php echo $row[2]." - ".$row[8]; ?></h6>
      <h6>Tipo moneda: <?php echo $row[9]; ?></h6>
      <h6>Fecha de carga: <?php echo $row[4]->format("Y/m/d"); ?></h6>
    </div>
</div>
<?php 
}
$query="SELECT tblCTAgastosSubencta.*,tblCTAgastosConceptos.nombre as nombreconcepto,tblCTAgastosConceptos.cta_contable as cta_contable FROM tblCTAgastosSubencta INNER JOIN tblCTAgastosConceptos on tblCTAgastosConceptos.id=tblCTAgastosSubencta.idconsepto WHERE tblCTAgastosSubencta.folio=$id";
$resultado=sqlsrv_query($conn,$query);
$subtotal=0;
$iva=0;
$total=0;
?>
<div class="row">
    <div class="col-12">
<div class="table-responsive"  style="height: 300px; overflow: scroll;">
    <table class="table table-hover" id="tblconsep">
      <thead class="table-dark text-center">
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Concepto</th>
          <th scope="col">Cta C.</th>
          <th scope="col">Importe</th>
          <th scope="col">IVA</th>
          <th scope="col">XML</th>
          <th scope="col">Observaciones</th>
          <th scope="col">Fecha</th>
          <th scope="col">Archivo</th>
        </tr>
      </thead>
      <tbody>
       
<?php 
while ($row = sqlsrv_fetch_array($resultado)) {
  if($row[7] != null){
  $row[7]=$row[7]->format('Y/d/m');
  }
  $subtotal=$subtotal+$row[3];
  $iva=$iva+$row[4];
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[9]."</td>";
    echo "<td>".$row[10]."</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row[4]."</td>";
    echo "<td>".$row[5]."</td>";
    echo "<td>".$row[6]."</td>";
    echo "<td>".$row[7]."</td>";
    echo "<td><a href='Ctagastos/Archivos/$row[8]' class='text-dark fst-italic text-decoration-underline' target='_blank'>".$row[8]."</a></td></tr>";
}
$total=($subtotal+$iva);
$total=$total*-1;
?>
      </tbody>
    </table>
</div>
</div>
</div>
<div class="row">
  <div class="col">
    <p class="h5">Subtotal: $<span class="fw-bold"><?php echo ($subtotal*-1); ?></span></p>
    <p class="h5">Iva: $<span class="fw-bold"><?php echo ($iva*-1); ?></span></p>
    <p class="h5">Total: $<span class="fw-bold"><?php echo ($total); ?></span></p>
    <p class="h5">Anticipo: $<span class="fw-bold"><?php echo $anticipo; ?></span></p>
    <p class="h5">Saldo: $<span class="fw-bold"><?php echo ($total+$anticipo); ?></span></p>
    <p><span><?php if(($total+$anticipo)>=0) echo "Saldo a favor de Kimberly Clark de México"; else echo "Saldo a favor de ".$usuario.""; ?></span></p>
  </div>
  <div class="col">
<div class="table-responsive" >
    <table class="table table-sm " id="tblconsep">
      <thead class="table-dark text-center">
        <tr>
          <th scope="col">KM</th>
          <th scope="col">Gasolina</th>
          <th scope="col">ID concepto</th>
        </tr>
      </thead>
      <tbody>
    <?php 
      $query2="SELECT tblCTAgastoskmgasolina.km,tblCTAgastoskmgasolina.gasolina,tblCTAgastoskmgasolina.idconsepto from tblCTAgastoskmgasolina INNER JOIN tblCTAgastosSubencta ON tblCTAgastosSubencta.id=tblCTAgastoskmgasolina.idconsepto INNER JOIN tblCTAgastosEncabezadocta ON tblCTAgastosEncabezadocta.id=tblCTAgastosSubencta.folio WHERE tblCTAgastosEncabezadocta.id=$id";
    $resultado2=sqlsrv_query($conn,$query2);
    while ($row2 = sqlsrv_fetch_array($resultado2)) {
      echo "<tr><td>".$row2[0]."</td>";
      echo "<td>".$row2[1]."</td>";
      echo "<td>".$row2[2]."</td></tr>";
    }
     ?>
   </tbody>
 </table>
</div>
  </div>
</div>