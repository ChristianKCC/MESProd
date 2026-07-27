<?php
require_once("../Session/seguridad.php");
// if ($_SESSION["permisoConfClaves"] != 1) {
//     header('Location: ../index/index');
// }
require_once("../index/header.php");
?>

<style>
    .container{
        max-width: 50%;
    }
</style>

<div class="container rounded shadow p-4">
    <h4 class="tittlecont">Configuración de Secciones</h4>
    <div class="row">
        <div class="col">
            <form>
                <div class="row mb-2">
                    <div class="col-8"><small>Buscar Sección</small><input type="text" class="form-control form-control-sm" id="seccionBusqueda" /> </div>
                    <div class="col-2"><br />
                        <center>
                            <button class="btn btn-sm bg-target" id="buscarSeccion"><i class="fas fa-search"></i> Buscar</button>
                        </center>
                    </div>
                    <div class="col-2"><br />
                    <center>
                        <button class="btn btn-sm btn-danger" id="nuevaSeccion"><i class="fas fa-plus"></i> Nueva Sección</button>
                    </center>
                    </div>
                </div>
            </form>
            <div class="table-responsive border" style="height: 600px;">
                <table class="table table-sm border" id="tblclavesenc">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>Nombre Sección</th>
                        <th></th>
                    </thead>
                    <tbody id="tblSecciones">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Seccion de modales -->

<!-- Modal Update Secciones -->
<div class="modal fade" id="modalUpdateSecciones" tabindex="-1" aria-labelledby="modalSeccioneslabel1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSeccioneslabel1">Actualizar información</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <small id="idSectxt">No. Sección</small>
                        <input type="text" class="form-control form-control-sm" id="noSeccionUpdate" readonly>
                        <input type="text" class="form-control form-control-sm" id="idSeccionUpdate" hidden readonly>
                    </div>
                    <div class="col-12">
                        <small>Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="nombreSeccionUpdate">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="fas fa-window-close"></i> Cancelar</button>
                <button type="button" class="btn btn-sm bg-target" id='updateSecciones'><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Secciones -->
<div class="modal fade" id="modalNuevaSeccion" tabindex="-1" aria-labelledby="modalSeccioneslabel1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSeccioneslabel1">Nueva Sección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                        <!-- <small id="idSectxt">No. Sección</small> -->
                        <!-- <input type="text" class="form-control form-control-sm" id="noSeccion" hidden readonly> -->
                        <!-- <input type="text" class="form-control form-control-sm" id="idSeccionNueva" hidden readonly> -->
                    <!-- </div>  -->
                    <div class="col-12">
                        <small>Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="idSeccionNueva" hidden>
                        <input type="text" class="form-control form-control-sm" id="nombreSeccionNueva">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="fas fa-window-close"></i> Cancelar</button>
                <button type="button" class="btn btn-sm bg-target" id='saveNuevaSeccion'><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/ConfiguracionSecciones.js"></script>