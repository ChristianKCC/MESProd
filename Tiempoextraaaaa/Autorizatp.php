<?php
require_once("../Session/seguridad.php");
require_once(__DIR__ . "/../../conexion.php");
require_once(__DIR__ . "/../Vacaciones/php/vacacionesLogistica.php");
require_once("php/guard.php");

// IBM del usuario en sesión
$ibmSesion = $_SESSION["ibm"] ?? null;

// Obtener lista de supervisores
$listaSupervisores = obtenerSupervisoresIBM();
$ibmPermitidos = [60040, 58998, 22622, 51947, 55268, 53224, 55075, 53412, 27825, 30950, 59610, 55075, 28342];

// Validar acceso
if (!$ibmSesion || (!in_array($ibmSesion, $listaSupervisores) && !in_array($ibmSesion, $ibmPermitidos))) {
    // No está autorizado → redirigir
    header("Location:../index/index.php");
    exit;
}

require_once("../index/header.php");
?>

<!-- DRIVER JS -->
<link rel="stylesheet" href="css/estilosNav.css">
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css" />
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">
    <h5 class="tittlecont">Validación de solicitudes de Tiempo Extra</h5>
    <div style="float:right" class="p-1 ayudaSupervisor">
        <button id="btnAyuda" class="btn btn-info">
            <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
        </button>
    </div>

    <div style="float-left" class="row">
        <div class="col-20">
            <small class="alert alert-info" style="float:left">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                    aria-label="Warning:">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                </svg>
                Desde esta sección valida las solicitudes realizadas para ver cuales son aptas a autorización de Tiempo
                Extra antes de ser enviadas a Gerencia.
            </small>
        </div>
    </div>

    <div class="col">
        <div class="row mb-3">
            <!-- Filtro global -->
            <div class="col-4">
                <b>
                    <label for="filtroGlobal">Filtro global:</label>
                </b>
                <input type="text" id="filtroGlobal" class="form-control form-control-sm" placeholder="Buscar...">
            </div>

            <div class="col-auto">
                <b>
                    <label for="filtroDepto">Filtro por departamento:</label>
                </b>
                <select id="filtroDepto" class="form-select form-select-sm d-inline-block" style="width:180px;">
                </select>
            </div>

        </div>
        <div id="accionesGlobales"></div>
    </div>

    <!-- <div class="card card-body">
        <div class="table-responsive ">
            <table class="table table-sm">
                <thead class="table-dark">
                    <th>ID Registro</th>
                    <th>Folio</th>
                    <th>Creación de Folio</th>
                    <th>NoEmp Sup</th>
                    <th>Nombre Supervisor</th>
                    <th>NoEmp Solicitante</th>
                    <th>Fecha Solicitud T. Extra</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                    <th>Nombre Solicitante</th>
                    <th>Departamento</th>            
                    <th>Estatus</th>
                    <th>Validado</th>
                    <th>Acciones</th>
                </thead>
                <tbody id="tblenc">
                </tbody>
            </table>
        </div>
    </div> -->

    <ul class="nav nav-tabs mb-3" id="tiempoExtraTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes"
                type="button" role="tab">
                No validados <span class="badge bg-warning text-dark" id="countPendientes">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="procesadas-tab" data-bs-toggle="tab" data-bs-target="#procesadas" type="button"
                role="tab">
                Validados <span class="badge bg-success" id="countProcesadas">0</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Pendientes -->
        <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Registro</th>
                            <th>Folio</th>
                            <th>Creación de Folio</th>
                            <th>NoEmp Sup</th>
                            <th>Nombre Supervisor</th>
                            <th>NoEmp Solicitante</th>
                            <th>Fecha Solicitud T. Extra</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Nombre Solicitante</th>
                            <th>Departamento</th>
                            <th>Estatus</th>
                            <th>Validado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tblPendientes"></tbody>
                </table>
            </div>
        </div>

        <!-- Procesadas -->
        <div class="tab-pane fade" id="procesadas" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Registro</th>
                            <th>Folio</th>
                            <th>Creación de Folio</th>
                            <th>NoEmp Sup</th>
                            <th>Nombre Supervisor</th>
                            <th>NoEmp Solicitante</th>
                            <th>Fecha Solicitud T. Extra</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Nombre Solicitante</th>
                            <th>Departamento</th>
                            <th>Estatus</th>
                            <th>Validado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tblProcesadas"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/autorizatp.js"></script>