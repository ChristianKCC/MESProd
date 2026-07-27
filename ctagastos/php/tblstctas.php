<?php
 include '../../../csql32.php';
  require_once("../../Session/seguridad.php");
$query="SELECT tblCTAgastosEncabezadocta.*, tblEmpleados.Nombre FROM tblCTAgastosEncabezadocta INNER JOIN tblEmpleados on tblEmpleados.NoEmp= tblCTAgastosEncabezadocta.noemp WHERE tblCTAgastosEncabezadocta.estado=1 AND tblCTAgastosEncabezadocta.noemp='".$_SESSION['ibm']."'";
$resultado=sqlsrv_query($conn,$query);
?>
<div class="table-responsive">
    <table class="table table-hover table-striped table-sm" id="tblctagastoscl">
      <thead class="thead-dark">
        <tr>
          <th scope="col">Folio</th>
          <th scope="col">IBM</th>
          <th scope="col">Nombre</th>
          <th scope="col">Cuenta</th>
          <th scope="col">Fecha</th>
        </tr>
      </thead>
      <tbody>
       
<?php 
while ($row = sqlsrv_fetch_array($resultado)) {
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td>";
    echo "<td>".$row[7]."</td>";
    echo "<td>".$row[2]."</td>";
    echo "<td>".$row[4]->format("Y/m/d")."</td></tr>";
}
?>
      </tbody>
    </table>
</div>
<script type="text/javascript">
$('#tblctagastoscl tr').on('dblclick', function(){
    $('#modalstcta').modal('toggle');
    var data1 = $(this).find('td:first').html();
    $.ajax({
      url: 'php/consultacta.php',
      type: 'POST',
      dataType: 'html',
      data: {'id':data1}
    })
    .done(function(x) {
    $("#resultadoenc").html('');
    $("#resultadosub").html('');
    $("#resultadoenc").html(x);
    $("#fomrconcepto")[0].reset();
    })
    .fail(function(){
      console.log("error");
    })
})
</script>
