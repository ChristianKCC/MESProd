<?php
require_once("../Session/seguridad.php");
require_once(__DIR__ . "/../../conexion.php");
require_once("php/guard.php");


$Verificarsesion = new VerificarSesionVac();
$Verificarsesion->esEnSupIntendente();

require_once("../index/header.php");
?>

<div class="container p-4">
    <h5 class="tittlecont">Pre-autorización de Solicitudes de Vacaciones (Super Intendente)</h5>

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
                    Desde esta seccion aprueba o rechaza las solicitudes correspondientes de vacaciones como Super Intendente
            </small>
        </div>
    </div>

    <div class="row mb-3">
    <div class="col">
        <label>Filtra por fecha </label>
        <input type="date" id="filtroFecha" class="form-control" placeholder="Filtrar por fecha">
    </div>

    <div class="col">        
        <label>Filtra por estatus </label>
        <select id="filtroEstatus" class="form-select">
        <option value="">Todos</option>
        <option value="0">En espera</option>
        <option value="1">Aprobado</option>
        <option value="2">Rechazado</option>
        </select>
    </div>
    <div class="col">
        <button class="btn btn-primary" onclick="window.Vacaciones.consulta()">Filtrar</button>
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
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/vacacionesAutSupInt.js"></script>
