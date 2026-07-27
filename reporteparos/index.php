
<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref p-2">
  <h4>Reporte de Paros</h4>
  <form>
  <div class="row">
    <div class="col-6"><small>fecha inicial</small><input type="date" id="fechai" class="form-control form-control-sm"></div>
    <div class="col-6"><small>fecha final</small><input type="date" id="fechaf" class="form-control form-control-sm"></div>
  </div> 
<div class="row my-2">
  <div class="col-6"><button class="form-control form-control-sm bg-target btn" onclick="tblinf()">Aceptar</button></div>
  <div class="col-6"><button type="reset" class="form-control form-control-sm btn btn-danger">Limpiar</button></div>
</div>
</form> 
<div id="tablerptparos"></div>
</div>
<?php require_once("../index/footer.php") ?> 
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> 
<script src="js/index.js" type="text/javascript"></script>