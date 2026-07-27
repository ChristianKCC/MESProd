<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittle">Reporte Aris</h5>
    <div class="row">
        <div class="col-2">
            <small>Fecha Inicio</small>
            <input type="date" id="fechai" class="form-control"/>
        </div>
        <div class="col-2">
            <small>Fecha Final</small>
            <input type="date" id="fechaif" class="form-control"/>
        </div>
        <div class="col-2">
            <small>Maquina</small>
            <select id="maquina" class="form-control">
                <option value="BCM-4">BCM-4</option>
            </select>
        </div>
        <div class="col-1">
            <br>
            <button class="btn btn-primary ">Procesar</button>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <canvas id="graficaMerma" height="100"></canvas>
        </div>
        <div class="col-6">
            <div class="table-responsive" style="height: 400px;">
                <table class="table">
                    <thead>
                    <th>ID</th>
                    <th>CODIGO</th>
                    <th>DESCRIPCION</th>
                    <th>MERMA</th>
                    <th>FECHA</th>
                    <th>TURNO</th>
                    <th>CATEGORIA</th>
                    <thead>
                    <tbody id="tblRechazos">
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<?php require_once("../index/footer.php") ?>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script type="module" src="js/rechazosaris.js"></script>