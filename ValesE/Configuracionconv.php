<?php
require_once("../Session/seguridad.php");
if ($_SESSION["permisoConfClaves"] != 1) {
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<div class="container rounded shadow">
    <h4 class="tittlecont">Configuración Combinaciones</h4>
    <div class="row my-4">
        <div class="col">
            <form>
                <div class="row mb-2">
                    <div class="col"><small>Buscar Combinación</small><input type="text" class="form-control form-control-sm" id="combbusqueda" /> </div>
                    <div class="col-1"><br /><button class="btn btn-sm bg-target" id="buscarcomb"><i class="fas fa-search"></i> Buscar</button></div>
                    <div class="col-2"><br /><button class="btn btn-sm btn-danger" id="nuevacomb"><i class="fas fa-plus"></i> Nueva Combinación</button></div>
                </div>
            </form>
            <div class="table-responsive" style="height: 600px;">
                <table class="table border text-center" id="tblconvinacionesenc">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>IDMaquina</th>
                        <th>Maquina</th>
                        <th>Clave</th>
                        <th>Nombre Clave</th>
                        <th>Clase</th>
                        <th>Nombre Clase</th>
                        <th>SIAM</th>
                        <th>Nombre Material</th>
                        <th></th>
                    </thead>
                    <tbody id="tblconvinaciones">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<!-- Modal Claves -->
<div class="modal fade" id="modalConvinaciones" tabindex="-1" aria-labelledby="modalConvinacioneslabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConvinacioneslabel">Actualizar información</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-2"><small>F. Editado</small><input type="text" class="form-control form-control-sm" id="idconvinacion" readonly></div>
                    <div class="col-10"><small>Maquina</small><select class="form-control form-control-sm" id="maquinaconv"></select></div>
                    
                    <div class="col-2"><small>Clave</small><input type="text" class="form-control form-control-sm" id="claveconv" readonly /></div>
                    <div class="col-10"><br/>
                        <div class="autocomplete-container">
                            <input class="form-control form-control-sm" id="claveinput" type="text" name="claveinput" placeholder="Buscar Clave">
                            <div id="autocompleteclaves" class="autocomplete-items"></div>
                        </div>
                    </div>
                    <div class="col-2"><small>Clase</small><input type="text" class="form-control form-control-sm" id="claseconv" readonly /></div>
                    <div class="col-10"><br/>
                        <div class="autocomplete-container">
                            <input class="form-control form-control-sm" id="claseinput" type="text" name="claseinput" placeholder="Buscar Clase">
                            <div id="autocompleteclases" class="autocomplete-items"></div>
                        </div>
                    </div>
                    <div class="col-2"><small>Material</small><input type="text" class="form-control form-control-sm" id="materialconv" readonly /></div>
                    <div class="col-10"><br/>
                        <div class="autocomplete-container">
                            <input class="form-control form-control-sm" id="materialinput" type="text" name="materialinput" placeholder="Buscar Material">
                            <div id="autocompletematerial" class="autocomplete-items"></div>
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
<script type="module" src="js/Configuracionconvinaciones.js"></script>