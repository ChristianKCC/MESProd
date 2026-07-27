<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
    <h5 class="tittlecont">Reporte Folios Laboratorio</h5>
    <div class="row">
        <div class="col">
            <small>Fecha Inicio</small>
            <input type="date" class="form-control form-control-sm" id="fechainicio" />
        </div>
        <div class="col">
            <small>Fecha Final</small>
            <input type="date" class="form-control form-control-sm" id="fechafinal" />
        </div>
        <div class="col-1">
            <br/>
            <button class="btn btn-sm bg-target" id="buscar"><i class="fas fa-search"></i> Buscar</button>
        </div>
        <div class="col-1">
            <br/>
            <button class="btn btn-sm btn-danger"><i class="fas fa-undo-alt"></i> Limpiar</button>
        </div>
    </div>
    <div class="row m-2">
        <div class="table-responsive" style="height: 200px;">
            <table class="table table-hover">
                <thead class="table-dark">
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th>Monitor</th>
                    <th>S + D</th>
                    <th>QL</th>
                    <th>No. muestras</th>
                    <th>Departamento</th>
                    <th>Maquina</th>
                    <th>Conductor</th>
                    <th>Supervisor</th>
                    <th></th>
                </thead>
                <tbody id="tblReportefolio"></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/Reporte.js" type="module"></script>