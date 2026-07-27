<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5>Reporte No Conformidad</h5>
    <form id="reporteLSW">
        <div class="row">
            <div class="col"><small>Fecha Inicial</small><input type="date" class="form-control form-control-sm" name="fechai" id="fechai"></div>
            <div class="col"><small>Fecha Final</small><input type="date" class="form-control form-control-sm" name="fechaf" id="fechaf"></div>
            <div class="col"><small>Departamento</small><select name="departamentos" id="departamentos" class="form-control form-control-sm"></select></div>
            <div class="col"><small>Maquina</small><select name="maquina" id="maquina" class="form-control form-control-sm"></select></div>
            <div class="col"><br /><button class="bg-target btn btn-sm" id="consulta"><i class="fa-solid fa-database"></i> Consultar información</button></div>
        </div>
    </form>
    <div id="resultado"></div>
    <hr>
    <a href="#" id="exportarexcel">exportar a excel</a>
    <div class="table-responsive" style="height:600px">
        <table class="table table-sm" id="tblnoconformidad">
            <thead class="table-dark">
                <th>ID</th>
                <th>Fecha</th>
                <th>Departamento</th>
                <th>Maquina</th>
                <th>Sellador</th>
                <th>Operador</th>
                <th>Turno</th>
                <th>Producto</th>
                <th>Hora</th>
                <th>Defecto</th>
                <th>TotalProd</th>
                <th>ProdRecuperado</th>
                <th>Lider</th>
                <th>Calidad</th>
                <th>CodEmpDefecto</th>
                <th>CodTerDefecto</th>
                <th>Causa</th>
                <th>Correcciones</th>
                <th>Componente</th>
                <th></th>
                <th></th>
            </thead>
            <tbody id="tblReporteIMC">
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="modalrepnocof" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <small>Folio:</small>
                            <input type="text" class="form-control form-control-sm" id="folioNoconf" readonly>
                        </div>
                        <div class="mb-3">
                            <small>Departamento:</small>
                            <select class="form-control form-control-sm" id="departamentomodal"></select>
                        </div>
                        <div class="mb-3">
                            <small>Defecto:</small>
                            <select class="form-control form-control-sm" id="defectomodal"></select>
                        </div>
                        <div class="mb-3">
                            <small>Calidad:</small>
                            <select class="form-control form-control-sm" id="calidadmodal"></select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times-circle"></i> Cerrar</button>
                    <button type="button" class="btn btn-sm btn-primary" id="saveUpdateNoconf"><i class="fas fa-save"></i> Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Reporteimc.js"></script>