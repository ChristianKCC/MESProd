<?php
require_once("../Session/seguridad.php");
$_SESSION["nvlplaticas"] > 0 ? NULL : header("Location:../index/index.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-3 border rounded shadow">
    <h4 class="tittlecont">Platica de 5 minutos</h4>
    <form id="formenc">
        <div class="row">
            <div class="col-1">
                <small>No. emp</small>
                <input type="number" id="noemp" min="0" class="form-control form-control-sm">
            </div>
            <div class="col-3">
                <small>Nombre</small>
                <input type="text" id="nombre" class="form-control form-control-sm" readonly>
            </div>
            <div class="col-3">
                <small>Departamento</small>
                <input type="text" id="departamento" class="form-control form-control-sm" readonly>
            </div>
            <div class="col-2">
                <small>Fecha</small>
                <input type="date" id="fecha" name="" class="form-control form-control-sm">
            </div>
            <div class="col-3">
                <small>Tipo</small>
                <select id="tipo" class="form-control form-control-sm">
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <small>Nombre de la platica</small>
                <input type="text" id="nombreplatica" class="form-control form-control-sm">
            </div>
            <div class="col-2">
                <small>Minutos</small>
                <input type="number" id="minutos" name="" class="form-control form-control-sm">
            </div>
            <div class="col-3">
                <small>Archivo de la platica</small>
                <input type="file" class="form-control form-control-sm" id="archivoplatica" name="archivoplatica">
            </div>
            <div class="col-1">
                <br><button class="btn btn-sm bg-target" id="guardarEnc"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
            <div class="col-1">
                <br><button type="reset" class="btn btn-sm btn-secondary"><i class="fa-solid fa-arrows-rotate"></i> Limpiar</button>
            </div>
        </div>
        <div class="table-responsive mt-4 border" style="height: 600px;">
            <table class="table table-hover text-center">
                <thead class="table-dark">
                    <th>ID</th>
                    <th>No</th>
                    <th>NOMBRE</th>
                    <th>DEPARTAMENTO</th>
                    <th>FECHA</th>
                    <th>TIPO</th>
                    <th>NOMBRE PLATICA</th>
                    <th>MINUTOS</th>
                    <th>ARCHIVO</th>
                    <th></th>
                </thead>
                <tbody id="tblPlaticas"></tbody>
            </table>
        </div>
    </form>

    <!-- Modal actualizar archivo -->
    <div class="modal fade" id="modalUpdateArchivo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Actualizar archivo de plática</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Archivo actual: <a id="archivoActual" href="#" target="_blank"></a></p>
                <div class="mb-3">
                <label for="nuevoArchivo" class="form-label">Nuevo archivo</label>
                <input type="file" class="form-control" id="nuevoArchivo">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnActualizarArchivo">Actualizar</button>
            </div>
            </div>
        </div>
    </div>

</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/platicas.js"></script>