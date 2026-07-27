<?php
require_once("../Session/seguridad.php");
if ($_SESSION["permisoConfClaves"] != 1) {
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<div class="container rounded shadow">
    <h4 class="tittlecont">Configuracion de claves</h4>
    <div class="row">
        <div class="col">
            <form>
                <div class="row mb-2">
                    <div class="col"><small>Buscar Clave</small><input type="text" class="form-control form-control-sm" id="clavebusqueda" /> </div>
                    <div class="col-1"><br /><button class="btn btn-sm bg-target" id="buscarclave"><i class="fas fa-search"></i> Buscar</button></div>
                    <div class="col-1"><br /><button class="btn btn-sm btn-danger" id="nuevaclave"><i class="fas fa-plus"></i> Nueva Clave</button></div>
                </div>
            </form>
            <div class="table-responsive border" style="height: 600px;">
                <table class="table border" id="tblclavesenc">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>Clave</th>
                        <th>Descripcion</th>
                        <th>XCaja</th>
                        <th>Factor</th>
                        <th>Clase</th>
                        <th>Tipo</th>
                        <th></th>
                    </thead>
                    <tbody id="tblclaves">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>





<!-- Seccion de modales -->

<!-- Modal Claves -->
<div class="modal fade" id="modalClaves" tabindex="-1" aria-labelledby="modalClaveslabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalClaveslabel">Actualizar información</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <small>No. Clave</small>
                        <input type="text" class="form-control form-control-sm" id="noclave" readonly>
                        <input type="text" class="form-control form-control-sm" id="idclave" hidden readonly>
                    </div>
                    <div class="col-12">
                        <small>Descripcion</small>
                        <input type="text" class="form-control form-control-sm" id="descripcionclave">
                    </div>
                    <div class="col-6">
                        <small>XCaja</small>
                        <input type="number" class="form-control form-control-sm" id="xcaja">
                    </div>
                    <div class="col-6">
                        <small>Factor</small>
                        <input type="number" class="form-control form-control-sm" id="factor">
                    </div>
                    <div class="col-12">
                        <small>Clase</small>
                        <select class="form-control form-control-sm" id="claveclase"></select>
                    </div>
                    <div class="col-12">
                        <small>Tipo</small>
                        <select class="form-control form-control-sm" id="clavetipo"></select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="fas fa-window-close"></i> Cancelar</button>
                <button type="button" class="btn btn-sm bg-target" id='savechgclaves'><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Configuracionclaves.js"></script>