<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
<div class="row">
  <div class="col"><h5 class="fw-bold">Reporte de IT/OPT</h5></div>
  <form id="formresert" class="p-2">
 <div class="row">
  <div class="col-2"><input type="date" class="form-control form-control-sm" id="fechai"><small>Del:</small></div>
  <div class="col-2"><input type="date" class="form-control form-control-sm" id="fechaf"><small>Al:</small></div>
  <div class="col-2">
  <select class="form-control form-control-sm" id="tipo">
    <option value="">Selecciona el tipo</option>
    <option value="1">IT</option>
    <option value="2">OPT</option>
    <option value="3">Lectura AOPOE</option>
  </select>
  <small>Tipo</small></div>
  <div class="col-2">
  <select class="form-control form-control-sm" id="departamento">
  </select>
  <small>Departamento</small></div>
  <div class="col-2">
  <select class="form-control form-control-sm" id="puesto">
  </select>
  <small>Puesto</small></div>
  <div class="col-2">
  <input type="text" class="form-control form-control-sm" id="nomidpoe">
  <small>NoEmp, Nombre y POE</small></div>
</div>
<div class="row justify-content-end">
  <div class="col-1"><button class="form-control form-control-sm bg-target btn" onclick="consultaxdate()">Aceptar</button></div>
  <div class="col-1"><button type="reset" class="form-control form-control-sm btn btn-danger">Limpiar</button></div>
</div>
</form>
<div id="respuesta"></div>
  </form>
</div>
<div id="tblreporte"></div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/reporte.js" type="text/javascript"></script>
