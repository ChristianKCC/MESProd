<?php
require_once("../Session/seguridad.php");
// if ($_SESSION["permisoConfClaves"] != 1) {
//     header('Location: ../index/index');
// }
require_once("../index/header.php");
?>
<div class="container rounded shadow p-4">
    <h4 class="tittlecont">Configuración de Modulos</h4>
    <div class="row">
        <div class="col">
            <form>
                <div class="row mb-2">
                    <div class="col-8"><small>Buscar Combinación</small><input type="text" class="form-control form-control-sm" id="combinacionBusqueda" /> </div>
                    <div class="col-2"><br />
                        <center>
                            <button class="btn btn-sm bg-target" id="buscarCombinacion"><i class="fas fa-search"></i> Buscar</button>
                        </center>
                    </div>
                    <div class="col-2"><br />
                    <center>
                        <button class="btn btn-sm btn-danger" id="nuevaCombinacion"><i class="fas fa-plus"></i> Nueva Combinación</button>
                    </center>
                    </div>
                </div>
            </form>
            <div class="table-responsive border" style="height: 600px;">
                <table class="table table-sm border" id="tblclavesenc">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>No. Máquina</th>
                        <th>Máquina</th>
                        <th>Sección</th>
                        <th>Nombre Sección</th>
                        <th>Módulo</th>
                        <th>Nombre Módulo</th>
                        <th>Falla</th>
                        <th>Nombre Falla</th>
                        <th></th>
                    </thead>
                    <tbody id="tblCombinaciones">
                    </tbody>  
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Seccion de modales -->

<!-- Modal Combinaciones -->
<div class="modal fade" id="modalCombinaciones" tabindex="-1" aria-labelledby="modalCombinacioneslabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCombinacioneslabel">Información nueva combinación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-2"><small>F. Editado</small><input type="text" class="form-control form-control-sm" id="idconvinacion" readonly></div>
                    <div class="col-10"><small>Maquina</small><select class="form-control form-control-sm" id="maquinaconv"></select></div>
                    
                    <div class="col-2"><small>Sección</small><input type="text" class="form-control form-control-sm" id="seccionConb" readonly /></div>
                    <div class="col-10"><br/>
                        <div class="autocomplete-container">
                            <input class="form-control form-control-sm" id="seccionInput" type="text" name="seccionInput" placeholder="Buscar Sección">
                            <div id="autocompleteSecciones" class="autocomplete-items"></div>
                        </div>
                    </div>
                    <div class="col-2"><small>Modulo</small><input type="text" class="form-control form-control-sm" id="moduloConb" readonly /></div>
                    <div class="col-10"><br/>
                        <div class="autocomplete-container">
                            <input class="form-control form-control-sm" id="moduloInput" type="text" name="moduloInput" placeholder="Buscar Modulo">
                            <div id="autocompleteModulos" class="autocomplete-items"></div>
                        </div>
                    </div>
                    <div class="col-2"><small>Falla</small><input type="text" class="form-control form-control-sm" id="fallaConb" readonly /></div>
                    <div class="col-10"><br/>
                        <div class="autocomplete-container">
                            <input class="form-control form-control-sm" id="fallaInput" type="text" name="fallaInput" placeholder="Buscar Falla">
                            <div id="autocompletefalla" class="autocomplete-items"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="fas fa-window-close"></i> Cancelar</button>
                <button type="button" class="btn btn-sm bg-target" id='savecombinacion'><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Combinaciones.js"></script>