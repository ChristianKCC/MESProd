<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
<div class="row">
  <div class="col"><h5 class="fw-bold">Reporte de asistencias por empleado</h5></div>
  <form id="formresert" class="p-2">
 <div class="row"><small>Empleados</small>
  <div class="col-1"><input type="text" class="form-control form-control-sm" id="noemp" placeholder="No. Emp"></div>
  <div class="col-3"><select class="form-control form-control-sm" id="slcemp"></select></div>
  <div class="col-1"><select class="form-control form-control-sm" id="tipo">
          <option value="">Tipo</option>
          <option value="1">Auditable</option>
          <option value="2">Específico</option>
          <option value="3">Inducción</option>
          <option value="4">Salud</option>
          <option value="5">ETQ</option>
          </select>
</div>
  <div class="col-2"><input type="date" class="form-control form-control-sm" id="fechai"><small>Del:</small></div>
  <div class="col-2"><input type="date" class="form-control form-control-sm" id="fechaf"><small>Al:</small></div>
  <div class="col-1"><button class="form-control form-control-sm bg-target btn btn-sm" id="consultarxemp">Consultar</button></div>
  <div class="col-1"><button type="reset" class="form-control form-control-sm btn btn-danger  btn-sm">Limpiar</button></div>
</div>
<div class="row">
  
</div>
<div id="respuesta"></div>
  </form>
</div>
<div id="tblreporte"></div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/reporte.js" type="module"></script>
<script>
  
document.getElementById("consultarxemp").addEventListener("click", (e) => {
  e.preventDefault();
  var data2 = $("#slcemp").val();
  var data5 = $("#tipo").val();
  var data3 = $("#fechai").val();
  var data4 = $("#fechaf").val();
  if (data2 == "") {
    return false;
  } else if (data2 == null) {
    $("#tblreporte").html("No se encontrol al empleado");
    return false;
  }
  $.ajax({
    url: "php/tbl.php?reportexemp",
    type: "POST",
    dataType: "html",
    data: { emp: data2, fechai: data3, fechaf: data4, tipo: data5 },
  })
    .done(function (x) {
      $("#tblreporte").html(x);
    })
    .fail(function () {
      console.log("error");
    });
});
</script>
