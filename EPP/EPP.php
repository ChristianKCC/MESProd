<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Equipo de protección personal</h5>
    <form id="formepp">
        <div class="row">
            <div class="col-1">
                <small>No. emp</small>
                <input type="number" class="form-control form-control-sm" id="noemp" name="noemp" />
            </div>
            <div class="col-3">
                <small>Nombre</small>
                <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" readonly />
            </div>
            <div class="col-3">
                <small>Departamento</small>
                <input type="text" class="form-control form-control-sm" id="departamento" readonly />
            </div>
            <div class="col-3">
                <small>Puesto</small>
                <input type="text" class="form-control form-control-sm" id="puesto" readonly />
            </div>
            <div class="col-1">
                <br />
                <button class="btn bg-target btn-sm" id="saveEpp"><i class="fas fa-save"></i> Guardar</button>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-secondary btn-sm" id="limpiar"><i class="fas fa-save"></i> Limpiar</button>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <th>Equipo de protección básico</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </thead>
                        <tbody id="listeppbasico">
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="col-4">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <th style="width: 320px;">Equipo de protección específico</th>
                            <th></th>
                            <th></th>
                        </thead>
                        <tbody id="listeppespecifico">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-4">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <th>BPM</th>
                            <th></th>
                            <th></th>
                        </thead>
                        <tbody id="listeppbpm">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <dv class="col-12">
                <small>Comentarios</small>
                <textarea class="form-control form-control-sm" id="comentario"></textarea>
            </dv>
        </div>
        <div class="row mt-4">
            <div class="table-responsive" style="height: 350px;">
                <table class="table text-center">
                    <thead class="table-dark">
                        <th>Folio</th>
                        <th>No. emp</th>
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
        </div>
    </form>
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
                                <th>No. Emp</th>
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
<script type="module" src="js/epp.js"></script>