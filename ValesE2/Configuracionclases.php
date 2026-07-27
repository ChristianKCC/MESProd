<?php
require_once("../Session/seguridad.php");
if ($_SESSION["permisoConfClaves"] != 1) {
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<div class="container rounded shadow">
    <h4 class="tittlecont">Configuración de clases</h4>
    <div class="row">
        <div class="col">
            <form>
                <div class="row mb-2">
                    <div class="col"><small>Buscar Clase</small><input type="text" class="form-control form-control-sm" id="clasebusqueda"/> </div>
                    <div class="col-1"><br /><button class="btn btn-sm bg-target" id="buscarclase"><i class="fas fa-search"></i> Buscar</button></div>
                    <div class="col-1"><br /><button class="btn btn-sm btn-danger" id="nuevaclase"><i class="fas fa-plus"></i> Nueva Clase</button></div>
                </div>
            </form>
            <div class="table-responsive border" style="height: 600px;">
                <table class="table border" id="tblclasesenc">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>NoClase</th>
                        <th>Descripcion Clase</th>
                        <th></th>
                    </thead>
                    <tbody id="tblclases">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Seccion de modales -->

<!-- Modal Claves -->
<div class="modal fade" id="modalClases" tabindex="-1" aria-labelledby="modalClaseslabel1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalClaseslabel1">Actualizar información</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <small>No. Clase</small>
                        <input type="text" class="form-control form-control-sm" id="noclase" readonly>
                        <input type="text" class="form-control form-control-sm" id="idclase" hidden readonly>
                    </div>
                    <div class="col-12">
                        <small>Descripcion</small>
                        <input type="text" class="form-control form-control-sm" id="descripcionclase">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="fas fa-window-close"></i> Cancelar</button>
                <button type="button" class="btn btn-sm bg-target" id='savechgclases'><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Configuracionclases.js"></script>