<?php
require_once("../Session/seguridad.php");
if ($_SESSION["admincursos"] != 1) {
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container">
    <h3>Creador de cursos</h3>
    <form id="formcurso">
        <div class="row">
            <div class="col-2">
                <small class="fw-bold">Folio:</small>
                <input type="text" class="form-control form-control-sm form-control form-control-sm-sm" id="folio"
                    name="folio" readonly>
            </div>
            <div class="col-6">
                <small class="fw-bold">Nombre del curso:</small>
                <input type="text" class="form-control form-control-sm form-control form-control-sm-sm" id="nombre"
                    name="nombre">
            </div>
            <div class="col-2">
                <small class="fw-bold">Duración:</small>
                <input type="number" min="0" class="form-control form-control-sm form-control form-control-sm-sm"
                    id="duracion" name="duracion">
            </div>
            <div class="col-2">
                <small class="fw-bold">Sistema:</small>
                <div class="form-group">
                    <input type="radio" class="form-check-input" id="seguridad" name="clasificacionCurso" value="0"
                        checked>
                    <label for="seguridad">Seguridad</label>
                    <input type="radio" class="form-check-input" id="calidad" name="clasificacionCurso" value="1">
                    <label for="calidad">Calidad</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-5">
                <small class="fw-bold">Área tematica</small>
                <select name="tematica" id="DescAreaTematica" name="DescAreaTematica"
                    class="form-control form-control-sm form-control form-control-sm-sm">

                </select>
            </div>
            <div class="col-2">
                <small class="fw-bold">Modalidad capacitación</small>
                <select name="capacitacion" id="ModalidadCapacitacion" name="ModalidadCapacitacion"
                    class="form-control form-control-sm form-control form-control-sm-sm">

                </select>
            </div>
            <div class="col-5">
                <small class="fw-bold">Objetivo capacitación</small>
                <select name="No empleado" id="ObjetivoCapacitacion" name="ObjetivoCapacitacion"
                    class="form-control form-control-sm form-control form-control-sm-sm">
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-7">
                <small class="fw-bold">Ecribe la ruta donde se encuentra el curso:</small>
                <input type="text" id="direccion" name="direccion"
                    class="form-control form-control-sm form-control form-control-sm-sm">
            </div>
            <div class="col-3">
                <small class="fw-bold">Clasificación:</small>
                <select class="form-control form-control-sm" id="clasificacion" name="clasificacion">
                </select>
            </div>
            <div class="col-2">
                <small class="fw-bold">Autoriza:</small>
                <button class="form-control form-control-sm btn-success" id="autoriza"><i class="fas fa-check"></i>
                    Autorizar</button>
            </div>
        </div>
        <div class="row m-1">
            <div class="col-12">
                <div id="resultado"></div>
            </div>
        </div>
        <div class="row">
            <div class="col text-center">
                <button type="button" class="btn btn-sm bg-target" name="" id="guardarcurso" title="Guardar"><i
                        class="fas fa-save"></i> Guardar datos</button>
                <button class="btn btn-sm btn-danger" name="" id="eliminar" title="Borrar"><i
                        class="fas fa-trash-alt"></i> Eliminar</button>
                <button type="reset" class="btn btn-sm btn-secondary" id="limpiar" title="Limpiar"><i
                        class="fas fa-plus"></i> Limpiar</button>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                    data-bs-target="#cursosmodal">
                    <i class="fas fa-clipboard-list fa-1x"></i> Lista de cursos </button>
                <input type="file" name="archivo" id="archivo" required>
                <button type="button" id="uploadcurso" class="btn btn-sm btn-success"><i
                        class="fas fa-clipboard-list fa-1x"></i> Cargar archivo</button>
            </div>
        </div>
    </form>
    <hr />
    <div class="row">
        <div class="col">
            <div class="row mb-1">
                <h5>Instructores</h5>
                <div class="col-12">
                    <small class="fw-bold">Instructores disponibles.</small>
                    <select size="7" class="form-control form-control-sm" id="instructorslc">
                        <option> </option>
                    </select>
                </div>
                <div class="col-12">
                    <small class="fw-bold">Instructores agregados</small>
                    <select size="7" class="form-control form-control-sm" id="instructor">
                        <option> </option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row mb-1">
                <h5>Puestos</h5>
                <div class="col-12">
                    <small class="fw-bold">Puestos disponibles</small>
                    <select size="7" class="form-control form-control-sm" id="puestos">
                        <option> </option>
                    </select>
                </div>
                <div class="col-12">
                    <small class="fw-bold">Puestos agregados</small>
                    <select size="7" class="form-control form-control-sm" id="puestosadd">
                        <option> </option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row mb-1">
                <h5>Preguntas</h5>
                <div class="col-12">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <small class="fw-bold">Escribe la Pregunta tal cual se mostrara en el curso</small>
                                <input type="text" class="form-control form-control-sm" name="pregunta" id="pregunta"
                                    placeholder="Escribe la pregunta">
                            </div>
                            <div class="form-group">
                                <small class="fw-bold">Respuesta 1</small>
                                <input type="text" class="form-control form-control-sm" name="respuesta1"
                                    id="respuesta1">
                            </div>
                            <div class="form-group">
                                <small class="fw-bold">Respuesta 2</small>
                                <input type="text" class="form-control form-control-sm" name="respuesta2"
                                    id="respuesta2">
                            </div>

                            <div class="form-group">
                                <small class="fw-bold">Respuesta 3</small>
                                <input type="text" class="form-control form-control-sm" name="respuesta3"
                                    id="respuesta3">
                            </div>
                            <div class="form-group">
                                <small class="fw-bold">Selecciona la respuesta correcta</small>
                                <select class="form-control form-control-sm mb-1" name="respuestac" id="respuestac">
                                    <option value="">Selecciona una opción</option>
                                    <option value="1">Respuesta 1</option>
                                    <option value="2">Respuesta 2</option>
                                    <option value="3">Respuesta 3</option>
                                </select>
                            </div>
                            <div class=" form-group">
                                <button class="btn btn-sm btn-primary" id="crearpregunta"><i class="fas fa-save"></i>
                                    Guardar pregunta</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="table-responsive" style="height: 200px;">
                    <table class="table ">
                        <thead class="table-dark">
                            <th>ID</th>
                            <th>PREGUNTA</th>
                            <th>RESPUESTA 1</th>
                            <th>RESPUESTA 2</th>
                            <th>RESPUESTA 3</th>
                            <th>RESPUESTA CORRECTA</th>
                            <th></th>
                        </thead>
                        <tbody id="tblpreguntas">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="llenrencabezado"></div>

<!-- Modal cursos -->
<div class="modal fade" id="cursosmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="">Lista de Cursos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-1">
                    <div class="col">
                        <input type="text" id="buscarcurso" class="form-control form-control-sm" />
                    </div>
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="fiteraut">
                            <label class="form-check-label" for="fiteraut">
                                Solo no autorizados
                            </label>
                        </div>
                    </div>
                    <div class="col">
                        <button type="button" id="fitertblcurso" class="btn btn-sm bg-target">Buscar</button>
                    </div>
                </div>
                <div class="table-responsive" style="height: 650px;">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <th>ID</th>
                            <th>NOMBRE</th>
                            <th>DURACION</th>
                            <th>AREA</th>
                            <th>MODALIDAD</th>
                            <th>OBJETIVO</th>
                            <th>UBICACION</th>
                            <th></th>
                        </thead>
                        <tbody id="tablacursosautoriza">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="text/javascript" src="../poojs/herramientas.js"></script>
<script src="js/index.js" type="text/javascript"></script>
<script type="text/javascript">
    cursos = new Cursos();
    cursos.start();
</script>