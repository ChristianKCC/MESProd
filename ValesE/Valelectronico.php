<?php
require_once("../Session/seguridad.php");
require_once("../indexmaquina/header.php");
?>
<div class="container p-3">
<h4 class="tittlecont">Vales Electronicos</h4>
        <div class="row">
            <div class="col-1">
                <small>NoEmp / Solicita</small>
                <input type="number" class="form-control form-control-sm" id="noemp">
            </div>
            <div class="col-2">
                <small>Nombre</small>
                <input type="text" class="form-control form-control-sm" id="nombre" readonly>
            </div>
            <div class="col-2">
                <small>Puesto</small>
                <input type="text" class="form-control form-control-sm" id="puesto" readonly>
            </div>
            <div class="col-1">
                <small>Turno</small>
                <input type="number" id="turnoen" class="form-control form-control-sm" readonly>
            </div>
            <div class="col-1">
                <small>Clave 1</small>
                <select class="form-control form-control-sm" id="clave1"></select>
            </div>
            <div class="col-1">
                <small>Clave 2</small>
                <select class="form-control form-control-sm" id="clave2"></select>
            </div>
            <div class="col-1">
                <small>Clave 3</small>
                <select class="form-control form-control-sm" id="clave3"></select>
            </div>
            <div class="col-1">
                <small>Clave 4</small>
                <select class="form-control form-control-sm" id="clave4"></select>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm bg-target" id="crearVale"><i class="fas fa-plus"></i> Crear Vale</button>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm btn-secondary" id="limpiarinicio"><i class="fas fa-undo-alt"></i> Limpiar</button>
            </div>
        </div>
        <div class="row my-2 justify-content-center border">
            <h5 class="fw-bold text-center">Materiales Disponibles</h5>
            <div class="col-3">
                <small>Selecciona la clase</small>
                <select class="form-control form-control-sm" id="clase" size="8">
                </select>
            </div>
            <div class="col-8">
                <div class="table-responsive" style="height: 200px;">
                    <table class="table text-center">
                        <thead class="table-dark">
                            <th>ID</th>
                            <th width="400px">MATERIAL</th>
                            <th>Centro de constos</th>
                            <th>TIEMPO</th>
                            <th>TIPO DE MONTA</th>
                            <th></th>
                        </thead>
                        <tbody id="tblmateriales">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-1 text-end">
                <br />
                <span><?php echo $_SESSION['usuario'] ?> - </span>
            </div>
            <div class="col-1">
                <small>Folio en edición</small>
                <input type="hidden" class="form-control form-control-sm" id="foliovale" readonly>
                <input type="text" class="form-control form-control-sm" id="foliocons" readonly>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm bg-target" id="enviar"><i class="far fa-paper-plane"></i> Enviar</button>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm btn-danger" id="cancelar"><i class="fas fa-ban"></i> Cancelar</button>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ModalLista"><i class="fas fa-list-ul"></i> Lista</button>
            </div>
        </div>
        <div class="row my-2">
            <h5 class="fw-bold text-center">Materiales Agregados</h5>
            <div class="table-responsive" style="height: 200px;">
                <table class="table text-center">
                    <thead class="table-dark">
                        <th>Folio</th>
                        <th>ID</th>
                        <th width="400px">MATERIAL</th>
                        <th>Centro de constos</th>
                        <th>TIEMPO</th>
                        <th>TIPO DE MONTA</th>
                        <th>CANDIDATOS</th>
                        <th></th>
                    </thead>
                    <tbody id="tblmaterialesagregados">
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="ModalLista" tabindex="-1" aria-labelledby="ModalListaLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalListaLabel">Consultar Vales Electronicos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-2">
                                <small>Fecha Inicio</small>
                                <input type="date" class="form-control form-control-sm" id="fechaivales" />
                            </div>
                            <div class="col-2">
                                <small>Fecha Final</small>
                                <input type="date" class="form-control form-control-sm" id="fechafvales" />
                            </div>
                            <div class="col-2">
                                <small>Maquina</small>
                                <select class="form-control form-control-sm" id="maquinasvales">
                                </select>
                            </div>
                            <div class="col-2">
                                <small>Turno</small>
                                <select class="form-control form-control-sm" id="turnoslist">
                                    <option value="">Selecciona el turno</option>
                                </select>
                            </div>
                            <div class="col-2">
                                <small>Estado</small>
                                <select class="form-control form-control-sm" id="estadoslist">
                                    <option value="">Selecciona un estado</option>
                                </select>
                            </div>
                            <div class="col-1">
                                <br />
                                <button class="btn btn-sm bg-target" id="buscar"><i class="fas fa-search"></i> Buscar</button>
                            </div>
                            <div class="col-1">
                                <br />
                                <button class="btn btn-sm btn-secondary" id="limpiarvales"><i class="fas fa-undo-alt"></i></button>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="table-responsive" style="height: 400px;">
                                <table class="table text-center">
                                    <thead class="table-dark">
                                        <th>Folio</th>
                                        <th>Maquina</th>
                                        <th>Noemp</th>
                                        <th>Nombre</th>
                                        <th>Turno</th>
                                        <th>Clave 1</th>
                                        <th>Clave 2</th>
                                        <th>Clave 3</th>
                                        <th>Clave 4</th>
                                        <th>Estado</th>
                                        <th>Creado</th>
                                        <th>Enviado</th>
                                        <th></th>
                                        <th></th>
                                    </thead>
                                    <tbody id="tblValesCreados">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <h5 class="fw-bold text-center">Agregar MM2 / KG</h5>
                            <div class="table-responsive" style="height: 200px;">
                                <table class="table text-center" id="tblvalescambios">
                                    <thead class="table-dark">
                                        <th>Folio</th>
                                        <th>ID</th>
                                        <th width="400px">MATERIAL</th>
                                        <th>Centro de constos</th>
                                        <th>TIEMPO</th>
                                        <th>TIPO DE MONTA</th>
                                        <th>CANDIDATOS</th>
                                        <th>MM2 / KG</th>
                                        <th>Envases</th>
                                    </thead>
                                    <tbody id="tblmaterialemodal">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/vales.js"></script>
</body>

</html>