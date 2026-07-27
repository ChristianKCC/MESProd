
<?php 
 require_once("../Session/seguridad.php");
 require_once("../index/header.php"); ?>
<!-- Contenido -->
<div class="container rounded shadow">
	<h3 class="fw-bold">Reporte de incidente</h3>
  <form>
  <div class="row my-1">
  <div class="col-4">
    <small>Departamento</small>
    <select class="form-control form-control-sm" id="departamento" multiple></select>
  </div>
  <div class="col-4">
    <small>Fuente</small>
    <select class="form-control form-control-sm" id="fuente"></select>
  </div>
    <div class="col-4">
    <small>Tipo fuente</small>
    <select class="form-control form-control-sm" id="tipofuente" multiple>
    </select>
  </div>
  <div class="row">
    <div class="col">
    <small>Fecha de inicio</small>
      <input type="date" id="fechai" name="fechai" class="form-control form-control-sm">
  </div>
  <div class="col">
    <small>Fecha final</small>
      <input type="date" id="fechaf" name="fechaf" class="form-control form-control-sm">
  </div>
</div>
  <div class="row">
    <div class="col">
      <button type="button" class="btn btn-sm form-control bg-target" id="consultar">Aceptar</button>
  </div>
  <div class="col">
      <button type="reset" class="btn btn-sm form-control btn-danger" id="reset">Limpiar</button>
  </div>
</div>
  <div class="row my-1">
  <div class="col-12">
    <input type="text" id="buscarinvestigacion" class="form-control form-control-sm" placeholder="Buscar...">
  </div>
</div>
<div id="resulttbl"></div>
  <div class="row my-1">
    <div class="col-4">
    <div id="grafica3"></div>
  </div>
  <div class="col-4">
    <div id="chart1"></div>
  </div>
  <div class="col-4">
    <div id="chart2"></div>
  </div>
</div>
  <div id="capareporte"></div>
    <div class="modal fade" id="modalcapa" tabindex="-1" aria-labelledby="capamodal" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-body">
        <div id="contenidomodal"></div>
      </div>
    </div>
  </div>
</div>
</div>
</form>
<?php require_once("../index/footer.php") ?>
<script src="js/reportecapa.js" type="text/javascript"></script>
</body>
</html>