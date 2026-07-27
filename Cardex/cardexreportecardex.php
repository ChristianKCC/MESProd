<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
<div class="row">
  <div class="col"><h5 class="fw-bold">Reporte de cardex con puesto</h5></div>
  <form id="formresert" class="p-2">
 <div class="row"><small>Puesto</small>
  <div class="col-6"><select class="form-control form-control-sm" id="puestos"></select></div>
  <div class="col-3"><select class="form-control form-control-sm" id="tipo">
          <option value="">Tipo</option>
          <option value="1">Auditable</option>
          <option value="2">Específico</option>
          <option value="3">Inducción</option>
          </select>
</div>
  <div class="col-1"><button class="form-control form-control-sm bg-target btn" id="consultapuesto">Aceptar</button></div>
  <div class="col-1"><button type="reset" class="form-control form-control-sm btn btn-danger">Limpiar</button></div>
</div>
<div id="respuesta"></div>
  </form>
</div>
<div id="tblreporte"></div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/reporte.js" type="module"></script>
<script>
document.getElementById('consultapuesto').addEventListener('click',function(e){
  e.preventDefault();
  var data2 = $("#puestos").val();
  var data5 = $("#tipo").val();
  var data3 = $("#fechai").val();
  var data4 = $("#fechaf").val();
  if (data2 == "") {
    return false;
  }
  $.ajax({
    url: "php/tbl.php?reportepuesto",
    type: "POST",
    dataType: "html",
    data: { puestos: data2, fechai: data3, fechaf: data4, tipo: data5 },
  })
    .done(function (x) {
      $("#tblreporte").html(x);
    })
    .fail(function () {
      console.log("error");
    })
    .always(function () {
      console.log("complete");
    });
})
</script>
