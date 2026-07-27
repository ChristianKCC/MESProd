<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
<div class="row">
  <div class="col"><h5 class="fw-bold">Reporte de cardex con empleado</h5></div>
  <form id="formresert" class="p-2">
 <div class="row">
  <div class="col-6"><small>Empleado</small><select class="form-control form-control-sm" id="slcemp"></select></div>
  <div class="col-6"><small>Cardex</small><select class="form-control form-control-sm" id="slccardex"></select></div>
</div>
<div id="respuesta"></div>
<div class="row my-2">
  <div class="col-6"><button class="form-control form-control-sm bg-target btn" onclick="consulta()">Aceptar</button></div>
  <div class="col-6"><button type="reset" class="form-control form-control-sm btn btn-danger">Limpiar</button></div>
</div>
  </form>
</div>
<div id="tblreporte"></div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/reporte.js" type="text/javascript"></script>
