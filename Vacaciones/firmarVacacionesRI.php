<?php
require_once("../Session/seguridad.php");
require_once(__DIR__ . "/../../conexion.php");
require_once("php/guard.php");

// if (is_null($_SESSION["admincursos"])) {
//     header("Location:../index/index.php");
//     exit;
// }

$Verificarsesion = new VerificarSesionVac();
$Verificarsesion->esEnSupervisores();

require_once("../index/header.php");
?>

<link rel="stylesheet" href="css/estilosNav.css">

<div class="container p-4">
    <h5 class="tittlecont">Firma de Relaciones Inds. en solicitudes de vacaciones</h5>

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
                        Desde esta sección agrega tu firma a las solicitudes de vacaciones ya autorizadas y validadas.
                </small>
            </div>
        </div>

        <div class="card card-body">
            <div class="row mb-3">
                <div class="col">
                    <label>Filtra por fecha </label>
                    <input type="date" id="filtroFecha" class="form-control" placeholder="Filtrar por fecha">
                </div>
                <div class="col">        
                    <label>Filtra por estatus </label>
                    <select id="filtroEstatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="0">En espera de firma</option>
                        <option value="1">Firmados</option>
                        <option value="2">Rechazado</option>
                    </select>
                </div>
                <div class="col">
                    <button class="btn btn-primary" onclick="window.Vacaciones.consulta()"> <i class="fa-solid fa-filter"></i> Filtrar</button>
                </div>
            </div>
            

        <ul class="nav nav-tabs mb-3" id="vacacionesTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
                Pendientes <span class="badge bg-warning text-dark" id="countPendientes">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="procesadas-tab" data-bs-toggle="tab" data-bs-target="#procesadas" type="button" role="tab">
                Firmadas <span class="badge bg-success" id="countProcesadas">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rechazadas-tab" data-bs-toggle="tab" data-bs-target="#rechazadas" type="button" role="tab">
                Rechazadas <span class="badge bg-danger text-white" id="countRechazadas">0</span>
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
                        <th>Folio</th>
                        <th>NoEmp</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>Fecha Solicitud</th>
                        <th>Estatus</th>
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
                        <th>Folio</th>
                        <th>NoEmp</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>Fecha Solicitud</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tblProcesadas"></tbody>
                </table>
                </div>
            </div>

            <!-- Rechazadas -->
            <div class="tab-pane fade" id="rechazadas" role="tabpanel">
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
                    <tbody id="tblRechazadas"></tbody>
                </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/firmarVacacionesRI.js"></script>
