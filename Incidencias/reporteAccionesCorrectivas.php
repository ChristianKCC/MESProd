<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); ?>
<!-- Contenido -->
<div class="container p-3">
    <h5 class="tittlecont">Reporte de Acciones Correctivas</h5>
    <div class="row mb-2">
        <!-- <div class="col">
            <small>Etapa</small>
            <select class="form-control form-control-sm" id="etapa">
                <option value = ''>Seleciona una opción</option>
                <option value="3">Etapa 3</option>
                <option value="4">Etapa 4</option>
                <option value="5">Etapa 5</option>
                <option value="6">Etapa 6</option>
            </select>
        </div> -->
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
                <th>NoEmp</th>
                <th>Nombre</th>
                <th>Causa Básica</th>
                <th>Causa Inmediata</th>
                <th>Causa Raíz</th>
                <th>Comportamiento</th>
                <th>Acción Correctiva</th>
                <th>Porque</th>
                <th>Porque Causa</th>
                <th>Porque Raíz</th>
            </thead>
            <tbody id="tblAcciones">
                
            </tbody>
        </table>
    </div>
</div>


<?php require_once("../index/footer.php") ?>
<script src="js/reporteAcciones.js" type="module"></script>

