<?php 
  include '../../../csql32.php';
  require_once("../../Session/seguridad.php");
  $inyquery='';
  if($_SESSION["nvlctagastos"]<2){
    echo "<h4>¡Lo siento! No tienes acceso aquí.</h4>";
  }else{
  if($_SESSION["nvlctagastos"]==2) $inyquery='tblCTAgastosEncabezadocta.estado=2';
  else if($_SESSION["nvlctagastos"]==3) $inyquery='tblCTAgastosEncabezadocta.estado=3';
  else if($_SESSION["nvlctagastos"]==4) $inyquery='tblCTAgastosEncabezadocta.estado=4';
$query="SELECT tblCTAgastosEncabezadocta.*,tblCTAgastosEstado.nombre, tblEmpleados.Nombre as nombemp FROM tblCTAgastosEncabezadocta INNER JOIN tblCTAgastosEstado on tblCTAgastosEstado.id= tblCTAgastosEncabezadocta.estado INNER JOIN tblEmpleados on tblEmpleados.NoEmp= tblCTAgastosEncabezadocta.noemp WHERE $inyquery ORDER BY tblCTAgastosEncabezadocta.id DESC";
$resultado=sqlsrv_query($conn,$query);
?>
<div class="table-responsive">
    <table class="table table-hover table-striped table-sm" id="tblctagastoscl">
      <thead class="table-dark">
        <tr>
          <th scope="col">Folio</th>
          <th scope="col">IBM</th>
          <th scope="col">Nombre</th>
          <th scope="col">Centro</th>
          <th scope="col">Estado</th>
          <th scope="col">Consultar</th>
          <th scope="col">Devolver</th>
          <th scope="col">Acción</th>
        </tr>
      </thead>
      <tbody>
       
<?php 
while ($row = sqlsrv_fetch_array($resultado)) {
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[1]."</td>";
    echo "<td>".$row[8]."</td>";
    echo "<td>".$row[2]."</td>";
    echo "<td><span class='text-primary'>".$row[7]."</span></td>";
    echo "<td><button class='btn bg-target btn-sm' onclick='configcta(".$row[0].")'><i class='fa-solid fa-street-view'></i></td>";
    echo "<td><button class='btn btn-danger btn-sm' onclick='devolver(".$row[0].")'><i class='fas fa-undo-alt'></i></button></td>";
    // Cambia por niveles
    if($_SESSION["nvlctagastos"]==2 AND $row[5]==2)
    echo "<td><button class='btn btn-warning btn-sm text-dark' onclick='finaliza(".$row[0].")'>Autorizar <i class='fa-solid fa-angles-right'></i></button></td></tr>";
    else if($_SESSION["nvlctagastos"]==3 AND $row[5]==3)
    echo "<td><button class='btn btn-warning btn-sm text-dark' onclick='finaliza2(".$row[0].")'>Autorizar <i class='fa-solid fa-angles-right'></i></button></td></tr>";
    else if($_SESSION["nvlctagastos"]==4 AND $row[5]==4)
    echo "<td><button class='btn btn-warning btn-sm text-dark' onclick='finaliza3(".$row[0].")'>Autorizar <i class='fa-solid fa-angles-right'></i></button></td></tr>";
    else{
    echo "<td><button class='btn btn-sm btn-success' onclick=''><i class='fa-solid fa-thumbs-up'></i></button></td></tr>";
    }
}
?>
      </tbody>
    </table>
</div>
<!-- Modal -->
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
<?php } ?>