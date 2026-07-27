<?php
require_once("../Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location:../index/index.php");
}
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Reporte de Cambio de Puesto</h5>
    <br>
    <div style="float:left" class="row">
        <div class="col-20">    
            <small class="alert alert-info">
             <svg 
                xmlns="http://www.w3.org/2000/svg" 
                width="24" 
                height="24" 
                fill="currentColor" 
                class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" 
                viewBox="0 0 16 16" 
                role="img" 
                aria-label="Warning:">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
                Desde esta seccion consulta el estado de las solicitudes de los cambios de puesto
            </small>
        </div>
    </div>
    <br>
    <br>
    <br>
    <form id="formrep">
        <div class="row">
            <div class="col">
                <small>Fecha inicio</small>
                <input type="date" id="fechai" class="form-control form-control-sm" />
            </div>
            <div class="col">
                <small>Fecha Final</small>
                <input type="date" id="fechaf" class="form-control form-control-sm" />
            </div>
            <div class="col">
                <small>Folio</small>
                <input type="number" id="folio" class="form-control form-control-sm" />
            </div>
            <div class="col">
                <small>No. Emp</small>
                <input type="number" id="noemp" class="form-control form-control-sm" />
            </div>
            <div class="col">
                <small>Maquina</small>
                <select id="departamento" class="form-control form-control-sm"></select>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm bg-target" id="consultar"><i class="fa-solid fa-magnifying-glass"></i> Consultar</button>
            </div>
            <div class="col-1">
                <br />
                <button id="limpiartodo" class="btn btn-sm btn-danger"><i class="fa-solid fa-eraser"></i> Limpiar</button>
            </div>
            <div class="col-1">
                <br />
                <button id="exportarexcel" class="btn btn-sm btn-success"><i class="fa-solid fa-file-excel"></i> Excel </button>
            </div>
        </div>
    </form>
    <br>
    <div class="justify-content-center">
        <div class="table-responsive my-2">
            <table class="table table-sm" id="tbltiempoextra">
                <thead class="table-dark">
                    <th>ID</th>
                    <th>FOLIO</th>
                    <th>NOEMP SOLICITANTE</th>
                    <th>NOMBRE SOLICITANTE</th>
                    <th>NOEMP A CUBRIR</th>
                    <th>PERSONA A CUBRIR</th>
                    <th>FECHA</th>
                    <th>MAQUINA</th>
                    <th>L</th>
                    <th>M</th>
                    <th>M</th>
                    <th>J</th>
                    <th>V</th>
                    <th>S</th>
                    <th>D</th>
                    <th>PUESTO REGULAR</th>
                    <th>PUESTO TEMPORAL</th>
                    <th>ESTATUS</th>
                </thead>
                <tbody id="tblrtegenral">
                </tbody>
            </table>    
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/reportegenral.js"></script>