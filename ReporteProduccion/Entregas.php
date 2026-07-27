<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container">
    <h5 class="tittlecont">Entregas de producción</h5>
    <div class="row">
        <div class="col-1">
            <small>Folio</small>
            <input type="number" class="form-control form-control-sm" id="folio" name="folio" />
        </div>
        <div class="col-2">
            <small>Fecha</small>
            <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" />
        </div>
        <div class="col-2">
            <small>Maquina</small>
            <select class="form-control form-control-sm" id="maquinas" name="maquinas"></select>
        </div>
        <div class="col-3">
            <small>Clave</small>
            <select class="form-control form-control-sm" id="clave" name="clave"></select>
        </div>
        <div class="col-1">
            <small>Entregado</small>
            <input type="number" class="form-control form-control-sm" id="Entregado" name="Entregado" />
        </div>
        <div class="col-1">
            <br />
            <button class="form-control form-control-sm btn btn-sm bg-target" id="btnsave" name="btnsave"><i class="fas fa-save"></i> Guardar</button>
        </div>
        <div class="col-1">
            <br />
            <button class="form-control form-control-sm btn btn-sm btn-danger" id="btnclean" name="btnclean"><i class="fas fa-undo-alt"></i> Limpiar</button>
        </div>
    </div>
    <div class="row mt-2">
        <div class="table-responsive" style="height: 200px;">
            <table class="table table-hover">
                <thead class="table-dark">
                    <th>ID</th>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Maquina</th>
                    <th>Clave</th>
                    <th>Descripción</th>
                    <th>Tipo</th>
                    <th>Clase</th>
                    <th>Entregado</th>
                    <th>Factor</th>
                    <th>STD</th>
                    <th></th>
                </thead>
                <tbody id="tblEntregados"></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/entregados.js" type="module"></script>