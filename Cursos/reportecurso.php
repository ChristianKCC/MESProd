<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
<div class="row">
  <div class="col"><h5 class="tittlecont">Reporte de capacitaciones</h5></div>
  <form id="formresert" class="p-2">
 <div class="row">
  <div class="col-2"><small>Clasificación</small><select class="form-control form-control-sm" id="clasificacion">
     <option value="">Elige una opción</option>
     <option value="1">Auditable</option>
     <option value="2">Específico</option>
     <option value="3">Inducción</option>
     <option value="4">Campaña de salud</option>
     <option value="5">ETQ</option>
  </select></div>
  <div class="col-4"><small>Curso</small><select class="form-control form-control-sm" id="cursos"></select></div>
  <div class="col-3"><small>Departamento</small><select class="form-control form-control-sm" id="dep"></select></div>
  <div class="col-3"><small>Departamento real</small><select class="form-control form-control-sm" id="depsreal"></select></div>
  <div class="col-2"><small>Fecha</small><input type="month" class="form-control form-control-sm" id="fecha" ></div>
  <div class="col-2"><small>Filtrar por</small>
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="filano">
      <label class="form-check-label" for="filano">Fecha por año</label>
    </div>
  </div>
</div>
<div id="respuesta"></div>
<div class="row my-2">
  <div class="col-6"><button class="form-control form-control-sm bg-target btn" id="consultar">Aceptar</button></div>
  <div class="col-6"><button type="reset" class="form-control form-control-sm btn btn-danger">Limpiar</button></div>
</div>
  </form>
</div>
<div id="tblreporte"></div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/reportes.js" type="module"></script>
