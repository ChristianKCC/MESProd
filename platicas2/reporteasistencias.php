<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-3 border rounded shadow">
    <h4>Reporte platicas asistencias</h4>
    <form method="POST" action="php/reporteasistenciaspdf.php" target="iframe_a" class="mb-4">
        <div class="row">
            <div class="col">
                <small>Fecha inicial</small>
                <input type="date" class="form-control form-control-sm" name="fechai" id="fechai" required>
            </div>
            <div class="col">
                <small>Fecha final</small>
                <input type="date" class="form-control form-control-sm" name="fechaf" id="fechaf" required>
            </div>
            <div class="col">
                <small>Departamento</small>
                <select id="departamento" name="departamento" class="form-control form-control-sm"></select>
            </div>
            <div class="col">
                <small>Maquinas</small>
                <select id="maquinas" name="maquinas" class="form-control form-control-sm">
                    <option value="">Selecciona una opción</option>
                </select>
            </div>
            <div class="col">
                <small>No.Emp</small>
                <input type="number" class="form-control form-control-sm" name="noemp" id="noemp">
            </div>
            <div class="col">
                <br>
                <input class="form-control form-control-sm btn btn-sm bg-target" value="Consultar" type="submit">
            </div>
            <div class="col">
                <br>
                <input class="form-control form-control-sm btn btn-sm btn-secondary" value="Limpiar" type="reset">
            </div>
        </div>
    </form>
    <iframe name="iframe_a" height="680px" width="100%" title="Iframe Example"></iframe>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="./js/Reporte.js"></script>