<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); ?>
<!-- Contenido -->
<div class="container p-3">
    <h5 class="tittlecont">Reporte de Incidentes</h5>
    <div class="row mb-2">
        <div class="col">
            <small>Fecha Inicial</small>
            <input type="date" class="form-control form-control-sm" id="fechai">
        </div>
        <div class="col">
            <small>Fecha Final</small>
            <input type="date" class="form-control form-control-sm" id="fechaf">
        </div>
        <div class="col">
            <small>Departamento</small>
            <select class="form-control form-control-sm" id="departamento"></select>
        </div>
        <div class="col">
            <br>
            <button class="btn btn-sm bg-target" id="buscar"><i class="fas fa-search"></i> Buscar</button>
            <button class="btn btn-sm btn-danger" id="limpiar"><i class="fas fa-undo-alt"></i> Limpiar</button>
        </div>
    </div>
    <div class="table-responsive" style="height: 600px;">
        <table class="table text-center">
            <thead class="table-dark">
                <th>Folio</th>
                <th>Fecha</th>
                <th>NumEmp</th>
                <th>Nombre</th>
                <th>Puesto</th>
                <th>Departamento</th>
                <th>NumEmpImplicado</th>
                <th>Nombre</th>
                <th>Puesto</th>
                <th>Departamento</th>
                <th>Incidencia</th>
                <th>Clasificación</th>
                <th>Acciones</th>
            </thead>
            <tbody id="tblIncidencias">
                
            </tbody>
        </table>
    </div>
</div>


<?php require_once("../index/footer.php") ?>
<script src="js/reporteIncidencias.js" type="module"></script>

