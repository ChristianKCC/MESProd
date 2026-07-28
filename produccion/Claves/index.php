<?php
require_once("../../Session/seguridad.php");
if ($_SESSION["permisoConfClaves"] != 1) {
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<div class="container rounded shadow">
    <h4 class="tittlecont">Configuracion de claves</h4>
    <div class="row mt-2">
        <div class="table-responsive">
            <!-- Controles de paginación y buscador -->
            <div class="d-flex justify-content-between align-items-center mt-3 pagination-controls">
                <div>
                    <label class="mb-0">
                        MOSTRAR:
                        <select id="pageSize" class="form-select form-select-sm d-inline-block" style="width:80px;">
                            <option value="10">10</option>
                            <option value="15" selected>15</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        REGISTROS
                    </label>
                </div>
                <!-- Buscador -->
                <div class="me-2" style="flex:1; max-width:300px;">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="BUSCAR..." />
                </div>
                <div>
                    <button id="nuevaclave" class="btn btn-primary btn-sm">Nueva Clave</button>
                    <!-- <button id="nuevoProducto" class="btn btn-success btn-sm">Nuevo Producto</button>
                    <button id="nuevoTamano" class="btn btn-info btn-sm">Nuevo Tamaño</button> -->
                </div>
            </div>
            <br>
            <table class="table table-bordered table-striped" style="text-align: center;">
                <thead class="table-dark">
                    <th>DEPARTAMENTO</th>
                    <th>MAQUINA</th>
                    <th>CATEGORIA</th>
                    <th>CLAVE</th>
                    <th>DESCRIPCIÓN</th>
                    <th>PRODUCTO</th>
                    <th>TAMAÑO</th>
                    <th>XCAJA</th>
                    <th>FACTOR</th>
                    <th hidden>Clase</th>
                    <th hidden>Tipo</th>
                    <th>EDITAR</th>
                </thead>
                <tbody id="tblclaves"></tbody>
            </table>
            <br>
            <!-- Controles para pasar a la siguiente pagina -->
            <div class="d-flex justify-content-end">
                <button id="prevPage" class="btn btn-dark btn-sm">Anterior</button>
                <span id="pageInfo" class="mx-2 my-auto"></span>
                <button id="nextPage" class="btn btn-dark btn-sm">Siguiente</button>
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
                        <small>Maquina</small>
                        <select class="form-control form-control-sm" id="claveMaquina"></select>
                    </div>
                    <div class="col-12">
                        <small>Categoria</small>
                        <select class="form-control form-control-sm" id="categoriaProducto"></select>
                    </div>
                    <div class="col-12">
                        <small>No. Clave</small>
                        <input type="text" class="form-control form-control-sm" id="noclave" readonly>
                        <input type="text" class="form-control form-control-sm" id="idclave" hidden readonly>
                    </div>
                    <div class="col-12">
                        <small>Descripcion</small>
                        <input type="text" class="form-control form-control-sm" id="descripcionclave">
                    </div>
                    <div class="col-4">
                        <small id="xCajaValue">XCaja</small>
                        <input type="number" class="form-control form-control-sm" id="xcaja">
                    </div>
                    <div class="col-4">
                        <small>Equivalencia USTD</small>
                        <select name="" id="ustd" class="form-control form-control-sm">
                            <option value="" aria-checked="">Selecciona una opcion</option>
                            <option value="1000">1000</option>
                            <option value="480">480</option>
                            <option value="150">150</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <small>Factor</small>
                        <input type="number" class="form-control form-control-sm" id="factor" readonly>
                    </div>
                    <div class="col-4" id="pesoBaseDiv" hidden>
                        <small>Peso Base (GSM)</small>
                        <input type="number" class="form-control form-control-sm" id="pesobase">
                    </div>
                    <div class="col-4" id="anchoDiv" hidden>
                        <small>Ancho (mm)</small>
                        <input type="number" class="form-control form-control-sm" id="ancho">
                    </div>
                    <div class="col-12">
                        <small>Producto</small>
                        <select class="form-control form-control-sm" id="claveproducto"></select>
                    </div>
                    <div class="col-12">
                        <small>Tamaño</small>
                        <select class="form-control form-control-sm" id="clavetamaño"></select>
                    </div>
                    <div class="col-12" id="conteosBajos" hidden>
                        <small>Clave Puente</small>
                        <div class="row">
                            <div class="col-2"><input type="text" class="form-control form-control-sm" id="claveconv" readonly/></div>
                            <div class="col-10"><input type="text" class="form-control form-control-sm" id="clavePuente"></div>
                        </div>                        
                     <div class="col-10"><br/>
                        <div class="autocomplete-container">
                            <div id="autocompleteclaves" class="autocomplete-items"></div>
                        </div>
                    </div>
                    </div>
                    <div class="col-12" hidden>
                        <small>Clase</small>
                        <select class="form-control form-control-sm" id="claveclase"></select>
                    </div>
                    <div class="col-12" hidden>
                        <small>Tipo</small>
                        <select class="form-control form-control-sm" id="clavetipo"></select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><i
                        class="fas fa-window-close"></i> Cancelar</button>
                <button type="button" class="btn btn-sm bg-target" id='savechgclaves'><i class="fas fa-save"></i>
                    Guardar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Producto -->
<div class="modal fade" id="modalProductos" tabindex="-1" aria-labelledby="modalProductoslabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductoslabel">Dar de alta un nuevo producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12">
                    <small>Descripcion</small>
                    <input type="text" class="form-control form-control-sm" id="descripcionProducto">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarProducto">Guardar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tamaño -->
<div class="modal fade" id="modalTamano" tabindex="-1" aria-labelledby="modalTamanolabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTamanolabel">Dar de alta un nuevo tamaño</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12">
                    <small>Descripcion</small>
                    <input type="text" class="form-control form-control-sm" id="descripcionTamano">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarTamano">Guardar</button>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Configuracionclaves.js"></script>