<?php
require_once("../Session/seguridad.php");
if($_SESSION["admincursos"]!=1){
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container">
    <h3>Agregar capacitaciones</h3>
    <div class="row mb-3">
        <div class="col-1"><small class="fw-bold">Folio:</small>
            <input type="text" id="folio" class="form-control form-control-sm" readonly>
        </div>
        <div class="col-2"><small class="fw-bold">Fecha inicial:</small>
            <input type="date" id="fechainicio" class="form-control form-control-sm">
        </div>
        <div class="col-2"><small class="fw-bold">Fecha final:</small>
            <input type="date" id="fechafinal" class="form-control form-control-sm">
        </div>
        <div class="col-1"><small class="fw-bold">ID curso:</small>
            <input type="text" id="idcurso" class="form-control form-control-sm">
        </div>
        <div class="col-4"><small class="fw-bold">Descripción:</small>
            <select class="form-control form-control-sm" id="desccurso"></select>
        </div>
        <div class="col-2"><small class="fw-bold">Duración:</small>
            <input type="number" id="duracion" class="form-control form-control-sm">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-1">
            <small class="fw-bold">No emp:</small>
            <input type="text" id="noemp" class="form-control form-control-sm">
        </div>
        <div class="col-3">
            <small class="fw-bold">Instructor:</small>
            <select class="form-control form-control-sm" id="instructor">
            </select>
        </div>
        <div class="col-5">
            <small class="fw-bold">Comentarios:</small>
            <input type="text" id="coment" class="form-control form-control-sm">
        </div>
        <div class="col-1">
            <br />
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="induccion">
                <label class="form-check-label" for="induccion">
                    Inducción
                </label>
            </div>
        </div>
        <div class="col-1">
            <br />
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="reinduccion">
                <label class="form-check-label" for="reinduccion">
                    Reinducción
                </label>
            </div>
        </div>
    </div>
    <div id="llenrencabezado"></div>
    <div id="respuesta"></div>
    <div class="row mb-2">
        <div class="col text-center">
            <button type="button" class="btn btn-primary btn-sm" name="" id="guardar" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
            <button class="btn btn-danger btn-sm" name="" id="eliminar" title="Borrar"><i class="fas fa-trash-alt"></i> Borrar</button>
            <button type="button" class="btn btn-secondary btn-sm" id="limpiar" title="Limpiar"><i class="fas fa-plus"></i> Limpiar</button>
        </div>
    </div>
    <div class="row">
        <div class="table-responsive" style="height: 250px;">
            <table class="table">
                <thead class="table-dark">
                    <th>ID</th>
                    <th>Inicio</th>
                    <th>Finalizó</th>
                    <th>ID</th>
                    <th>Curso</th>
                    <th>No emp</th>
                    <th>Instructor</th>
                    <th>Comentarios</th>
                    <th>I</th>
                    <th>R</th>
                    <th></th>
                </thead>
                <tbody id="tblcapacitaciones"></tbody>
            </table>
        </div>
    </div>
    <hr/>
    <form id="formsubencap">
        <div class="row">
            <div class="col-2"><small class="fw-bold">Folio:</small>
                <input type="text" id="idsubencabezado" class="form-control form-control-sm" readonly>
            </div>
            <div class="col-1"><small class="fw-bold">No emp:</small>
                <input type="text" id="noempcap" class="form-control form-control-sm">
            </div>
            <div class="col-4"><small class="fw-bold">Nombre:</small>
                <select id="empleados" class="form-control form-control-sm"></select>
            </div>
            <div class="col-1"><small class="fw-bold">Calificación:</small>
                <input type="number" max="10" min="0" id="calificacion" class="form-control form-control-sm">
            </div>
            <div class="col-4">
                <div class="row">
                    <div class="col"><br /><button class="btn btn-primary btn-sm" id="guardaremp"><i class="fas fa-user-plus"></i></button> </div>
                </div>
            </div>
        </div>
    </form>
    <div class="table-responsive my-1" style="height: 250px;">
        <table class="table table-hover" id="tblempleadocapacitacion">
            <thead class="table-dark">
                <th>No</th>
                <th>Folio</th>
                <th>No emp</th>
                <th>Nombre</th>
                <th>Calificación</th>
                <th>Contestó</th>
                <th></th>
            </thead>
            <tbody id="tblsubcapacitacion">
            </tbody>
        </table>
    </div>
</div>
<?php require_once("../index/footer.php"); ?>
<script type="text/javascript" src="../poojs/herramientas.js"></script>
<script type="text/javascript" src="./js/capasitacion.js"></script>
<script type="text/javascript">
    capacitacion = new Capacitacion();
    capacitacion.start()
</script>