<?php
 include '../../../csql32.php';
 $id=$_POST['id'];
$query="SELECT tblCTAgastosSubencta.*,tblCTAgastosConceptos.nombre as nombreconcepto, tblCTAgastosEncabezadocta.anticipo as anticipo FROM tblCTAgastosSubencta INNER JOIN tblCTAgastosConceptos on tblCTAgastosConceptos.id=tblCTAgastosSubencta.idconsepto LEFT JOIN tblCTAgastosEncabezadocta on tblCTAgastosEncabezadocta.id=tblCTAgastosSubencta.folio WHERE tblCTAgastosSubencta.folio=$id";
$resultado=sqlsrv_query($conn,$query);
$subtotal=0;
$iva=0;
$total=0;
$anticipo=0;
?>
<div class="table-responsive"  style="height: 300px; overflow: scroll;">
    <table class="table table-hover" id="tblconsep">
      <thead class="thead-dark">
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Concepto</th>
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
  $anticipo=$row['anticipo'];
  $subtotal=$subtotal+$row[3];
  $iva=$iva+$row[4];
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[9]."</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row[4]."</td>";
    echo "<td>".$row[5]."</td>";
    echo "<td>".$row[6]."</td>";
    echo "<td>".$row[7]."</td>";
    echo "<td><a href='Ctagastos/Archivos/$row[8]' class='text-dark fst-italic text-decoration-underline' target='_blank'>".$row[8]."</a></td></tr>";
}
$total=($subtotal+$iva);
?>
      </tbody>
    </table>
</div>
<div class="row mt-2">
  <div class="col">
    <h5>Anticipo: $<span class="fw-bold"><?php echo $anticipo; ?></span></h5>
    <h5>Subtotal: $<span class="fw-bold"><?php echo $subtotal; ?></span></h5>
    <h5>IVA: $<span class="fw-bold"><?php echo $iva; ?></span></h5>
    <h5>Total $<span class="fw-bold"><?php echo ($total-$anticipo); ?></span></h5>
  </div>
  <div class="col">
  <div class="alert alert-info">Doble click para eliminar un concepto.</div>
</div>
</div>
<script type="text/javascript">
$('#tblconsep tr').on('dblclick', function(){
    var data1 = $(this).find('td:first').html();
    $.ajax({
      url: 'php/eliminarconcepto.php',
      type: 'POST',
      dataType: 'html',
      data: {'id':data1}
    })
    .done(function(x) {
    tblconseptos();
    })
    .fail(function(){
      console.log("error");
    })
})
</script>