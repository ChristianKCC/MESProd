<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
<h4>Reportes de turno</h4>
<form>
<div class="row">
	<div class="col-6"><small>fecha inicial</small><input type="date" id="fechai" class="form-control form-control-sm"></div>
	<div class="col-6"><small>fecha final</small><input type="date" id="fechaf" class="form-control form-control-sm"></div>
</div> 
<div class="row my-2">
	<div class="col-6"><button class="form-control form-control-sm bg-target btn" onclick="consulta()">Aceptar</button></div>
	<div class="col-6"><button type="reset" class="form-control form-control-sm btn btn-danger">Limpiar</button></div>
</div>
</form> 
<div id="tabla"></div>
  <div class="col-4">
		<canvas id="bar-chart"></canvas>
	</div>
</div>
<div class="modal fade" id="viewmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content p-2">
      <div id="contmodal"></div>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/index.js" type="text/javascript"></script>