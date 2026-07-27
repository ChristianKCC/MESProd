<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5>Avance observador LSW</h5>
    <form id="reporteLSW">
    <div class="row mb-2">
        <div class="col"><small>Fecha Inicial</small><input type="date" class="form-control form-control-sm" name="fechai" id="fechai"></div>
        <div class="col"><small>Fecha Final</small><input type="date" class="form-control form-control-sm" name="fechaf" id="fechaf"></div>
        <div class="col"><small>Areas</small><select name="areas" id="areas" class="form-control form-control-sm"></select></div>
        <div class="col"><small>Puesto</small><select name="tipo" id="tipo" class="form-control form-control-sm">
        <option value="">Seleccina una opción</option>
        <option value="1">Staff</option>
        <option value="2">Operación</option>
        </select></div>
        <div class="col"><small>% de avance</small><select name="avance" id="avance" class="form-control form-control-sm">
        <option value="">Seleccina una opción</option>
        <option value="25">Menos del 25%</option>
        <option value="50">Menos del 50%</option>
        <option value="75">Menos del 75%</option>
        </select></div>
        <div class="col"><br/><button class="bg-target btn btn-sm" id="consulta"><i class="fa-solid fa-database"></i> Consultar información</button></div>
    </div>
    </form>
    <div id="resultado"></div>
    <hr>
    <!-- <div class="row justify-content-center">
    <div class="col-12"><h5 class="text-center">Avance</h5><canvas id="myChart2"></canvas></div>
    </div> -->
    <canvas id="myChart2"  height="600"></canvas>
</div>
<?php require_once("../index/footer.php") ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.1.0/chartjs-plugin-datalabels.min.js"></script>
<script type="text/javascript" src="js/index.js"></script>
<script type="text/javascript">
    proact= new Proact();
    proact.reportexavance();
</script>
