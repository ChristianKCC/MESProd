<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
<div class="row">
  <div class="col"><h5 class="fw-bold">Reporte de asistencias por curso</h5></div>
  <form id="formresert" class="p-2">
 <div class="row"><small>Curso</small>
  <div class="col-6"><select class="form-control form-control-sm" id="slccurso"></select></div>
  <div class="col-2"><input type="date" class="form-control form-control-sm" id="fechai"><small>Del:</small></div>
  <div class="col-2"><input type="date" class="form-control form-control-sm" id="fechaf"><small>Al:</small></div>
  <div class="col-1"><button class="form-control form-control-sm bg-target btn" id="consultarxcurso">Aceptar</button></div>
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
document.getElementById('consultarxcurso').addEventListener('click',function(e){
  e.preventDefault();
  var data2 = $("#slccurso").val();
  var data3 = $("#fechai").val();
  var data4 = $("#fechaf").val();
  if (data3 == "" || data4 == "") {
    alert("El rango de fechas es obligatorio");
    return false;
  }
  $.ajax({
    url: "php/tbl.php?reportecurso",
    type: "POST",
    dataType: "html",
    data: { curso: data2, fechai: data3, fechaf: data4 },
  })
    .done(function (x) {
      $("#tblreporte").html(x);
    })
    .fail(function () {
      console.log("error");
    });
})

</script>
