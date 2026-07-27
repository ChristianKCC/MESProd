<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<div class="container p-3">
    <h5 class="tittlecont">Reporte vales electrónicos</h5>
    <div class="row">
        <div class="col-2">
            <small>Fecha Inicio</small>
            <input type="date" class="form-control form-control-sm" id="fechai" />
        </div>
        <div class="col-2">
            <small>Fecha Final</small>
            <input type="date" class="form-control form-control-sm" id="fechaf" />
        </div>
        <div class="col-2">
            <small>Maquina</small>
            <select class="form-control form-control-sm" id="maquinas">
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
            <button class="btn btn-sm btn-secondary"><i class="fas fa-undo-alt"></i> Limpiar</button>
        </div>
    </div>
    <div class="row">
        <div class="table-responsive" style="height: 600px;">
            <table class="table">
                <thead>
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
                    <th>RecibidoMP</th>
                    <th></th>
                    <th></th>
                </thead>
                <tbody id="tblValesCreados">
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="modalenv" tabindex="-1" aria-labelledby="modalenvLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalenvLabel">Información <span id="folioencvista"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" class="form-control form-control-sm" id="folio" readonly>
                        <div class="col-1">
                            <small>Clave 1</small>
                            <input type="text" class="form-control form-control-sm" id="clave1" readonly>
                        </div>
                        <div class="col-1">
                            <small>Clave 2</small>
                            <input type="text" class="form-control form-control-sm" id="clave2" readonly>
                        </div>
                        <div class="col-1">
                            <small>Clave 3</small>
                            <input type="text" class="form-control form-control-sm" id="clave3" readonly>
                        </div>
                        <div class="col-1">
                            <small>Clave 4</small>
                            <input type="text" class="form-control form-control-sm" id="clave4" readonly>
                        </div>
                        <div class="col-1">
                            <small>NoEmp</small>
                            <input type="text" class="form-control form-control-sm" id="noemp" readonly>
                        </div>
                        <div class="col-3">
                            <small>Nombre</small>
                            <input type="text" class="form-control form-control-sm" id="nombre" readonly>
                        </div>
                        <div class="col-1">
                            <small>Maquina</small>
                            <input type="text" class="form-control form-control-sm" id="maquina" readonly>
                            <input type="hidden" class="form-control form-control-sm" id="maquinaid" readonly>
                        </div>
                        <div class="col-2">
                            <small>Turno</small>
                            <input type="text" class="form-control form-control-sm" id="turno" readonly>
                        </div>
                        <div class="col-2">
                            <small>Fecha Creado</small>
                            <input type="text" class="form-control form-control-sm" id="fechac" readonly>
                        </div>
                        <div class="col-2">
                            <small>Fecha Enviado</small>
                            <input type="text" class="form-control form-control-sm" id="fechae" readonly>
                        </div>
                        <div class="col-2">
                            <small>Estado</small>
                            <input type="text" class="form-control form-control-sm" id="estado" readonly>
                        </div>

                        <h5 class="fw-bold text-center">Materiales Solicitados</h5>
                        <div class="table-responsive" style="height: 200px;">
                            <table class="table text-center" id="datavale">
                                <thead class="table-dark">
                                    <th>Folio</th>
                                    <th>ID</th>
                                    <th width="400px">MATERIAL</th>
                                    <th>Centro de constos</th>
                                    <th>TIEMPO</th>
                                    <th>TIPO DE MONTA</th>
                                    <th>CANTIDAD</th>
                                    <th>ESTADO</th>
                                    <th>MM2/KG</th>
                                    <th>Envases</th>
                                </thead>
                                <tbody id="tblmaterialesagregados">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <a href="#" class="btn btn-success" id="exportvaleexcel"><i class="fas fa-file-excel"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/reporteVales.js"></script>
</body>

</html>