<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-3 border rounded shadow">
    <h5 class="tittlecont">Reporte de producción</h5>
    <form method="POST" action="php/reporteproduccionpdf.php" target="iframe_a" class="mb-2">
        <div class="row">
            <div class="col">
                <small>Fecha Inicial</small>
                <input type="date" class="form-control form-control-sm" name="fechai" id="fechai" required>
            </div>
            <div class="col">
                <small>Fecha Final</small>
                <input type="date" class="form-control form-control-sm" name="fechaf" id="fechaf" required>
            </div>
            <div class="col-3">
                <small>Departamento</small>
                <select id="departamento" name="departamento" class="form-control form-control-sm"></select>
            </div>
            <div class="col-2">
                <small>Maquinas</small>
                <select id="maquinas" name="maquinas" class="form-control form-control-sm">
                    <option value="">Selecciona una opción</option>
                </select>
            </div>
            <div class="col-2">
                <small>Turno</small>
                <select class="form-control form-control-sm" id="turno" name="turno"></select>
            </div>
            <div class="col">
                <br>
                <button type="submit" id="reporteProduccion" class="btn btn-sm bg-target"><i class="fas fa-file-prescription"></i> Crear Reporte</button>
            </div>
            <div class="col">
                <br>
                <button type="reset" class="btn btn-sm btn-danger"><i class="fas fa-undo-alt"></i> Limpiar</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="table-responsive" style="height: 300px;">
            <table class="table table-bordead">
                <thead class="table-dark">
                    <th>Clave</th>
                    <th>Descripcion</th>
                    <th>Maquina</th>
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th>Cortes</th>
                    <th>Merma</th>
                    <th>STD</th>
                    <th></th>
                </thead>
                <tbody id="tblReporteProdEnc">
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <div class="table-responsive" style="height: 300px;">
                <table class="table table-bordead">
                    <thead class="table-dark">
                        <th>Maquina</th>
                        <th>Seccion</th>
                        <th>Modulo</th>
                        <th>Total</th>
                        <th></th>
                    </thead>
                    <tbody id="tblParosManual">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-8">
            <table class="table table-bordead">
                <thead class="table-dark">
                    <th>Turno</th>
                    <th>Cortes</th>
                    <th>Rechazos</th>
                    <th>No. Paros</th>
                    <th>Tiempo abajo</th>
                    <th>Tiempo arriba</th>
                    <th>Hrs Trabajadas</th>
                    <th>% Merma máquina</th>
                    <th>% Tiempo Perdido</th>
                </thead>
                <tbody id="tablaDetallesProduccion"></tbody>
            </table>
        </div>
    </div>
    <!-- <iframe name="iframe_a" height="670px" width="100%" title="Iframe Example"></iframe> -->
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="./js/reporteproduccion.js"></script>