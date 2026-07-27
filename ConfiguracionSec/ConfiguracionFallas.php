<?php
require_once("../Session/seguridad.php");
// if ($_SESSION["permisoConfClaves"] != 1) {
//     header('Location: ../index/index');
// }
require_once("../index/header.php");
?>

<div class="container rounded shadow p-4">
    <h4 class="tittlecont">Configuración de Fallas</h4>
    <div class="row">
        <div class="col">
            <form>
                <div class="row mb-2">
                    <div class="col-8"><small>Buscar Falla</small><input type="text" class="form-control form-control-sm" id="fallaBusqueda" /> </div>
                    <div class="col-2"><br />
                        <center>
                            <button class="btn btn-sm bg-target" id="buscarFalla"><i class="fas fa-search"></i> Buscar</button>
                        </center>
                    </div>
                    <div class="col-2"><br />
                    <center>
                        <button class="btn btn-sm btn-danger" id="nuevaFalla"><i class="fas fa-plus"></i> Nueva falla</button>
                    </center>
                    </div>
                </div>
            </form>
            <div class="table-responsive border" style="height: 600px;">
                <table class="table table-sm border" id="tblclavesenc">
                    <thead class="table-dark">
                        <th>ID fallas</th>
                        <th>Nombre fallas</th>
                        <th></th>
                    </thead>
                    <tbody id="tblFallas">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Seccion de modales -->

<!-- Modal Update Secciones -->
<div class="modal fade" id="modalUpdateFallas" tabindex="-1" aria-labelledby="modalSeccioneslabel1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSeccioneslabel1">Actualizar información</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <small id="idSectxt">No. falla</small>
                        <input type="text" class="form-control form-control-sm" id="noModuloFallas" readonly>
                        <input type="text" class="form-control form-control-sm" id="idModuloFallas" hidden readonly>
                    </div>
                    <div class="col-12">
                        <small>Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="nombreFallaUpdate">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="fas fa-window-close"></i> Cancelar</button>
                <button type="button" class="btn btn-sm bg-target" id='updateFallas'><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Seccion -->
<div class="modal fade" id="modalNuevaFalla" tabindex="-1" aria-labelledby="modalSeccioneslabel1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSeccioneslabel1">Nuevo módulo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <small>Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="idFallaNueva" hidden>
                        <input type="text" class="form-control form-control-sm" id="nombreFallaNueva">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="fas fa-window-close"></i> Cancelar</button>
                <button type="button" class="btn btn-sm bg-target" id='saveNuevaFalla'><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/ConfiguracionFallas.js"></script>