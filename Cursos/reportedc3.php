<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
<div class="row">
<div class="col"><h5 class="fw-bold">Reporte Interno DC3</h5></div>
<form id="formrptdc3">
<div class="row">
<div class="col-2"><small>Tipo</small><select class="form-control form-control-sm" id="cursostipo" name="cursostipo"></select></div>
<div class="col-4"><small>Cursos</small><select class="form-control form-control-sm" id="cursos" name="cursos"></select></div>
<div class="col-2"><small>Fecha inicial</small><input type="date" class="form-control form-control-sm" id="fechai" name="fechai"></select></div>
<div class="col-2"><small>Fecha final</small><input type="date" class="form-control form-control-sm" id="fechaf" name="fechaf"></select></div>
<div class="col-1"><br><button class="form-control form-control-sm bg-target btn btn-sm" id="consultar">Aceptar</button></div>
<div class="col-1"><br><button type="reset" class="form-control form-control-sm btn btn-danger btn-sm">Limpiar</button></div>
</div>
</form>
<div id="respuesta"></div>
</div>
<div class="table-responsive">
  <table class="table table-sm">
    <thead>
      <th>ID</th>
      <th>Nombre</th>
      <th>Fecha I</th>
      <th>Fecha F</th>
      <th>DC3</th>
    </thead>
    <tbody id="tblrptdc3"></tbody>
  </table>
</div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="./js/dc3.js" type="module"></script>
