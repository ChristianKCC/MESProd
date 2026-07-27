<?php
require_once("../Session/seguridad.php");
if ($_SESSION["permisoConfClaves"] != 1) {
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<div class="container rounded shadow">
    <h4 class="tittlecont">Configuración de materiales</h4>
    <div class="row">
        <div class="col"><small>Buscar material</small><input type="text" class="form-control form-control-sm" id="materialbusqueda" /> </div>
        <div class="col-1"><br /><button class="btn btn-sm bg-target" id="buscarmaterial"><i class="fas fa-search"></i> Buscar</button></div>
        <div class="col-1"><br /><button class="btn btn-sm btn-danger" id="nuevomaterial"><i class="fas fa-plus"></i> Nueva Clave</button></div>
    </div>
    <div class="table-responsive border m-2" style="height: 600px;">
        <table class="table border text-center" id="tblmaterialesenc">
            <thead class="table-dark">
                <th>ID</th>
                <th>No Material</th>
                <th>Descripcion Material</th>
                <th>UMMaterial</th>
                <th>UM</th>
                <th>Montacargas</th>
                <th>Tiempo</th>
                <th></th>
            </thead>
            <tbody id="tblmateriales">
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Seccion de modales -->

<!-- Modal Claves -->
<div class="modal fade" id="modalmaterial" tabindex="-1" aria-labelledby="modalmodalmateriallabel1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalmodalmateriallabel1">Actualizar información</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-12"><small>NoMaterial</small><input type="number" class="form-control form-control-sm" id="nomaterial" />
                        <input type="number" class="form-control form-control-sm" id="idmaterial" readonly hidden />
                    </div>
                    <div class="col-12"><small>Nombre Material</small><input type=" text" class="form-control form-control-sm" id="nombrematerial" />
                    </div>
                    <div class="col-6"><small>UMMaterial</small><select class="form-control form-control-sm" id="ummaterial">
                            <option value=''>Selecciona una opción</option>
                            <option value='KGS'>KGS</option>
                            <option value='PZA'>PZA</option>
                            <option value='MM2'>MM2</option>
                        </select>
                    </div>
                    <div class="col-6"><small>UMMaterial</small><select class="form-control form-control-sm" id="ummat">
                            <option value=''>Selecciona una opción</option>
                            <option value='TAMBO'>TAMBO</option>
                            <option value='PIEZA'>PIEZA</option>
                            <option value='ROLLO'>ROLLO</option>
                            <option value='CAJA'>CAJA</option>
                            <option value='SACO'>SACO</option>
                            <option value='PAQUETE'>PAQUETE</option>
                            <option value='PZA'>PZA</option>
                        </select>
                    </div>
                    <div class="col-6"><small>Montacargas</small><select class="form-control form-control-sm" id="montacargas">
                            <option value=''>Selecciona una opción</option>
                            <option value='CARTON CLAMP'>CARTON CLAMP</option>
                            <option value='ROL CLAMP'>ROL CLAMP</option>
                            <option value='HORQUILLAS'>HORQUILLAS</option>
                        </select>
                    </div>
                    <div class="col-6"><small>Tiempo</small><input type="number" class="form-control form-control-sm" id="tiempo" /> </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i class="fas fa-window-close"></i> Cancelar</button>
                <button type="button" class="btn btn-sm bg-target" id='savechgmateriales'><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Configuracionmateriales.js"></script>