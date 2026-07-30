<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Reporte Equipo de Protección Personal</h5>
    <div class="row mb-2">
        <div class="col">
            <small>Fecha Inicial</small>
            <input type="date" class="form-control form-control-sm" id="fechai" />
        </div>
        <div class="col">
            <small>Fecha Final</small>
            <input type="date" class="form-control form-control-sm" id="fechaf" />
        </div>
        <div class="col">
            <small>Departamento</small>
            <select class="form-control form-control-sm" id="departamento"></select>
        </div>
        <div class="col">
            <small>Observado</small>
            <input type="text" class="form-control form-control-sm" id="noemp" />
        </div>
        <div class="col">
            <small>Observador</small>
            <input type="text" class="form-control form-control-sm" id="observador" />
        </div>
        <div class="col">
            <br />
            <button class="btn btn-sm bg-target" id="buscar"><i class="fas fa-search"></i> Buscar</button>
            <button class="btn btn-sm btn-danger" id="limpiar"><i class="fas fa-undo-alt"></i> Limpiar</button>
        </div>
    </div>
    <div class="table-responsive" style="height: 600px;">
        <table class="table text-center">
            <thead class="table-dark">
                <th>Folio</th>
                <th>Noemp</th>
                <th>Nombre</th>
                <th>Departamento</th>
                <th>Comentario</th>
                <!-- <th>Fecha</th> -->
                <th></th>
            </thead>
            <tbody id="tbleppenc">

            </tbody>
        </table>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">New message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive" style="height: 500px;">
                        <table class="table">
                            <thead class="table-dark text-center">
                                <th>Noemp</th>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <!-- <th>Fecha</th> -->
                                <th>Equipo</th>
                                <th>Res</th>
                            </thead>
                            <tbody id="tblsubenc">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/reporteepp.js"></script>