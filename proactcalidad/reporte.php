<?php
require_once("../Session/seguridad.php");
if($_SESSION["permisoProact"]!=1){
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5>Observaciones de calidad</h5>
    <form id="reporteLSW">
        <div class="row">
            <div class="col"><small>Fecha Inicial</small><input type="date" class="form-control form-control-sm" name="fechai" id="fechai"></div>
            <div class="col"><small>Fecha Final</small><input type="date" class="form-control form-control-sm" name="fechaf" id="fechaf"></div>
            <div class="col"><small>Area</small><select name="areas" id="areas" class="form-control form-control-sm"></select></div>
            <div class="col"><small>Maquina</small><select name="maquinas[]" id="maquinas" class="form-control form-control-sm" multiple></select></div>
            <div class="col"><br /><button class="bg-target btn btn-sm" id="consulta"><i class="fa-solid fa-database"></i> Consultar información</button></div>
        </div>
    </form>
    <div id="resultado"></div>
    <hr>
    <div class="table-responsive" style="height:400px">
        <table class="table table-sm" id="tblobserb">
            <thead class="table-dark">
                <th>ID</th>
                <th>Observador</th>
                <th>TObservacion</th>
                <th>Observado</th>
                <th>Observacion</th>
                <th>Cumplio</th>
                <th>Area</th>
                <th>Maquina</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Comentarios</th>
                <th>Otra</th>
                <th>Critico</th>
            </thead>
            <tbody id="table">

            </tbody>
        </table>
    </div>
    <a href="#" onclick="proact.exportartablaexcel('tblobserb')">exportar a excel</a>
    <div class="row">
        <div class="col-6 text-center">
            <h5 class="text-center">TOTAL DE OBSERVACIONES POR MAQUINA</h5><canvas id="myChart" height="300"></canvas>
        </div>
        <div class="col-6 text-center">
            <h5 class="text-center">TOTAL DE PERSONAS OBSERVADASPOR MAQUINA</h5><canvas id="myChart3" height="300"></canvas>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-6">
            <h5 class="text-center">Observaciones con las que el trabajados no cumple</h5><canvas id="myChart2" height="200"></canvas>
        </div>
        <div class="col-6">
            <h5 class="text-center">Se está incumpliendo alguna regla critica</h5><canvas id="myChart5" height="200"></canvas>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <h5 class="text-center">Top personas observadas</h5><canvas id="myChart4" height="200"></canvas>
        </div>
    </div>

</div>
<?php require_once("../index/footer.php") ?>
<script type="text/javascript" src="../poojs/herramientas.js"></script>
<script type="text/javascript" src="js/index.js"></script>
<!-- <script type="text/javascript" src="../poojs/index.js"></script> -->
<script type="text/javascript">
    proact = new Proact();
    proact.reporte();

</script>