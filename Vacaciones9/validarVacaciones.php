<?php
require_once("../Session/seguridad.php");
require_once(__DIR__ . "/../../conexion.php");
require_once("php/guard.php");

$ibmPermitidos = [60040, 58998, 22622, 51947, 55268, 53224];

if (!isset($_SESSION['ibm']) || !in_array($_SESSION['ibm'], $ibmPermitidos)) {
    header("Location:../index/index.php");
    exit;
}

$Verificarsesion = new VerificarSesionVac();
$Verificarsesion->esEnSupervisores();

require_once("../index/header.php");
?>
<style>
    table td:last-child,
    table th:last-child {
    white-space: nowrap;
    width: 1%;
    }

    #tablaDias th {
    background-color: #343a40;
    color: #fff;
    text-align: center;
    }

    #tablaDias td {
    text-align: center;
    vertical-align: middle;
    padding: 6px;
    }

    .dia-vacaciones {
    background-color: #d4edda; /* verde claro */
    color: #155724;
    font-weight: bold;
    }

    .dia-trabajado {
    background-color: #f8d7da; /* rojo claro */
    color: #721c24;
    font-weight: bold;
    }


    .dia-futuro {
    background-color: #fff3cd; /* amarillo claro */
    color: #856404;
    font-weight: bold;
    }


</style>

<div class="container p-4">
    <h5 class="tittlecont">Validación de informacion en solicitudes de vacaciones</h5>

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
                    Desde esta seccion valida la información del empleado para que sea enviado a firma de Relaciones Industriales
            </small>
        </div>
    </div>

    <div class="card card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Folio</th>
                        <th>NoEmp</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>Fecha Solicitud</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tblVacaciones"></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Comprobar Días -->    
    <div class="modal fade" id="modalDias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
            <i class="fa-solid fa-calendar-check"></i> Verificación de NO ASISTENCIA en periodo de vacaciones
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <!-- Info del empleado -->
            <div id="infoEmpleado" class="mb-3"></div>

            <!-- Tabla de días -->
            <div class="table-responsive">
            <table class="table table-bordered text-center" id="tablaDias">
                <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Hora de inicio</th>
                    <th>Hora de fin</th>
                    <th>Nota</th>
                </tr>
                </thead>
                <tbody id="diasBody"></tbody>
            </table>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
        </div>
    </div>
    </div>

</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/validarVacaciones.js"></script>