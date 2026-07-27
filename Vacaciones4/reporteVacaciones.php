<?php
require_once("../Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location:../index/index.php");
}
require_once("../index/header.php");
?>
<style>
    #tblVacaciones th, #tblVacaciones td {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    }

    #tblVacaciones th:nth-child(4), 
    #tblVacaciones th:nth-child(5), 
    #tblVacaciones th:nth-child(8), 
    #tblVacaciones th:nth-child(10) {
        width: 50px;
    }

</style>
<div class="container p-4">
    <h5 class="tittlecont">Consulta de Solicitudes de Vacaciones</h5>

    <div style="float-left" class="row">
        <div class="col-20">
            <small class="alert alert-info" style= "float:left">
                    <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        width="16" 
                        height="16" 
                        fill="currentColor" 
                        class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" 
                        viewBox="0 0 16 16" 
                        role="img" 
                        aria-label="Warning:">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    Desde esta vista consulta el estatus de tus solicitudes para tus vacaciones.
            </small>
        </div>
    </div>

    <form id="formVacRep">
        <div class="row">
            <div class="col">
                <small>Fecha inicio</small>
                <input type="date" id="fechai" class="form-control form-control-sm" />
            </div>
            <div class="col">
                <small>Fecha final</small>
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
                <small>Departamento</small>
                <select id="departamento" class="form-control form-control-sm"></select>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm bg-target" id="consultar">
                    <i class="fa fa-search"></i> Consultar
                </button>
            </div>
            <div class="col-1">
                <br />
                <button id="limpiartodo" class="btn btn-sm btn-danger">
                    <i class="fa fa-eraser"></i> Limpiar
                </button>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm btn-success" id="exportexcel">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
            </div>
        </div>
    </form>

    <br>
    <div style="float-left" class="row">
        <div class="col-20">
            <small class="alert alert-primary d-flex align-items-center" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" 
                    fill="currentColor" class="bi bi-exclamation-triangle-fill me-2" 
                    viewBox="0 0 16 16" role="img" aria-label="Warning:">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                <div>
                    <strong>Glosario de colores en los dias del calendario:</strong><br />
                    <span style="background-color: rgb(198,224,180); padding:4px 8px; color:black; border:1px solid #000;">V = Vacaciones</span>
                    <span style="background-color: rgb(255,255,153); padding:4px 8px; color:black; border:1px solid #000;">D = Descanso</span>
                    <span style="background-color: rgb(255,153,153); padding:4px 8px; color:black; border:1px solid #000;">F = Festivo</span>
                    <span style="background-color: rgb(180,198,231); padding:4px 8px; color:black; border:1px solid #000;">R = Reposición</span>
                </div>
            </small>

        </div>
    </div>

    <div class="table-responsive my-2">
    <table class="table table-sm" id="tblVacaciones">
        <thead class="table-dark">
            <tr>
                <th>Folio</th>
                <th>NoEmp</th>
                <th>Nombre</th>
                <th>Departamento</th>
                <th>Puesto</th>
                <th>Del</th>
                <th>Al</th>
                <th>Días Solicitados</th>
                <th>Tipo de solicitud</th>
                <th>Días por antiguedad</th>
                <th>Estatus</th>
                <th>No. Semana</th>
                <th>L</th>
                <th>M</th>
                <th>Mi</th>
                <th>J</th>
                <th>V</th>
                <th>S</th>
                <th>D</th>
                <th>Vac</th>
                <th>Fes</th>
                <th>Des</th>
                <th>Rep</th>
            </tr>
        </thead>
        <tbody id="tblVacBody"></tbody>
    </table>
</div>

</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/reporteVacaciones.js"></script>
