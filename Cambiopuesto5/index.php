<?php
require_once("../Session/seguridad.php");
require_once(__DIR__ . "/../Vacaciones/php/vacacionesLogistica.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// IBM del usuario en sesión
$ibmSesion = $_SESSION["ibm"] ?? null;

// Obtener lista de supervisores
$listaSupervisores = obtenerSupervisoresIBM();
$ibmPermitidos = [60040, 58998, 22622, 51947, 55268, 53224];

// Validar acceso
if (
    !$ibmSesion ||
    (!in_array($ibmSesion, $listaSupervisores) && !in_array($ibmSesion, $ibmPermitidos))
) {
    // No está autorizado → redirigir
    header("Location:../index/index.php");
    exit;
}

// Si llega aquí, es supervisor autorizado
require_once(__DIR__ . "/../index/header.php");
?>
<!-- DRIVER JS -->
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Cambio de Puesto</h5>
    <div style="float:right" class="p-1 ayudaSupervisor">
        <button id="btnAyuda" class="btn btn-info">
            <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
        </button>
    </div>
    <br />

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
                Desde esta sección elabora tus solicitudes de cambio de puesto.
            </small>
        </div>
    </div>
    <br />
    <br />

    <div style="float:right;"><small>Fecha: </small><span id="fechaheader"></span></div><br />
    <div style="float:right;"><small>Supervisor: </small><span> <?php echo $_SESSION["nombre"] ?></span></div><br />
    <input type="hidden" id="nosemana" name="nosemana" />
    <div class="row">
        <div class="col-2"><small>Folio</small><input type="text" id="folio" class="form-control form-control-sm" readonly></div>
        <div class="col"><small>Inicio de la semana</small><input type="date" id="fechainput" name="fechainput" class="form-control form-control-sm" /></div>
        <div class="col"><br />
            <button class="btn-success btn btn-sm" id="abrir">
                <i class="fa-solid fa-square-up-right"></i> 
                    Crear folio
            </button>
        </div>
        <div class="col"><br />
            <button class="btn bg-vista btn-sm" id="btnverfolio" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                <i class="fa-solid fa-binoculars"></i> 
                    <span id="txtview">Ver folios</span>
                </button>
            </div>        
        <div class="col"><br />
            <button class="btn-warning btn btn-sm empezarDeNuevo" onClick="window.location.reload()">
                <i class="fa-solid fa-retweet"></i> Empezar de nuevo
            </button>
        </div>
        
        <div class="col"><br />
        <button class="btn btn-info btn-sm" id="btnVacantes" style="display:none;" data-bs-toggle="modal" data-bs-target="#modalVacantes">
            <i class="fa-solid fa-table"></i> Ver vacantes libres de la semana
        </button>
        </div>
        
        <div class="col-1 align-self-center"><br />
        <a href="#" id="creapdf" class="btn btn-danger btn-sm">
            <i class="fa-solid fa-file-pdf"></i> Pre. de PDF 
        </a>
    </div>
    </div>
    <div class="collapse m-2" id="collapseExample">
        <div class="card card-body">
            <div class="table-responsive" style="max-height: 200px;">
                <table class="table table-sm">
                    <thead class="table-dark">
                        <th>Folio</th>
                        <th>NoEmp Supervisor</th>
                        <th>Nombre Supervisor</th>
                        <th>Inicio de semana</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody id="tblenc">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <form id="formtiempoextra">
        <div class="row">
            <div class="col-1">
                <small>No. Emp</small>
                <input type="number" id="noemp" name="noemp" class="form-control form-control-sm" />
            </div>
            <div class="col">
                <small>Nombre</small>
                <input type="text" id="nombre" class="form-control form-control-sm" readonly />
            </div>
            <div class="col">
                <small>Departamento</small>
                <input type="text" id="departamento" class="form-control form-control-sm" readonly />
            </div>
            <div class="col">
                <small>Puesto</small>
                <input type="text" id="puesto" class="form-control form-control-sm" readonly />
            </div>
            
            <div class="col">
                <small>Máquina</small>
                <select id="maquinas" name="maquinas" class="form-control form-control-sm"></select>
            </div>
        </div>

        <br />
        <div id="contenedorDisponibles"  style="display:none;">
        </div>

        <div class="row m-2">
            <div class="col-4 border diasSeleccion">
                <p class="fw-bold">Selecciona los días de la semana:</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="lunes" name="dias[]">
                    <label class="form-check-label" for="lunes">
                        Lunes
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="martes" name="dias[]">
                    <label class="form-check-label" for="Martes">
                        Martes
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="miercoles" name="dias[]">
                    <label class="form-check-label" for="Miercoles">
                        Miércoles
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="jueves" name="dias[]">
                    <label class="form-check-label" for="Jueves">
                        Jueves
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="viernes" name="dias[]">
                    <label class="form-check-label" for="Viernes">
                        Viernes
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="sabado" name="dias[]">
                    <label class="form-check-label" for="Sabado">
                        Sábado
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="domingo" name="dias[]">
                    <label class="form-check-label" for="Domingo">
                        Domingo
                    </label>
                </div>
            </div>

            <div class="col-md-4 ">
                <div class="row">
                    <div class="col-6">
                        <small>Puesto actual</small>
                        <select id="puestoant" name="puestoant" class="form-control form-control-sm" disabled></select>
                    </div>
                    <div class="col-6">
                        <small>Puesto a cubrir</small>
                        <select id="temporal" name="temporal" class="form-control form-control-sm"></select>
                    </div>
                </div>
                <br />
                <div class="row">
                    <div class="col-6">                        
                        <small>Motivo</small>
                        <select id="motivos" name="motivos" class="form-control form-control-sm motivoSeleccion"></select>                    
                    </div>
                </div>
                <br />
                <div class="row mt-2">
                    <div class="col-6">
                        <small>IBM de Persona a Cubrir</small>
                        <input type="text" id="IBMCubrir" class="form-control form-control-sm" />
                    </div>
                    <div class="col-6">
                        <small>Puesto del IBM ingresado</small>
                        <input type="text" id="puestoCubrir" class="form-control form-control-sm" readonly />
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="row">
                    <div class="col-6">
                        <br />
                        <div class="col-md-8 text-end">
                            <button class="bg-success btn btn-sm text-white" id="guardar" hidden>
                                <i class="fa-solid fa-floppy-disk"></i> Guardar
                            </button>
                            <br /> <br /> <br /> <br />
                            <button type="reset" class="btn btn-secondary btn btn-sm">
                                <i class="fa-solid fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <br />
    <div class="row">
        <div class="col-20">    
            <small class="alert alert-warning">
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
                En esta tabla encontraras las solicitudes creadas y asociadas al folio seleccionado/creado.
            </small>
        </div>
    </div>
    <br />
    <div class="table-responsive" >
        <table class="table">
            <thead class="table-dark">
                <th>Id</th>
                <th>Folio</th>
                <th>NoEmp</th>
                <th>Nombre</th>
                <th>Departameto</th>
                <th>Máquina</th>
                <th>Puesto actual</th>
                <th>Puesto temporal</th>
                <th>Motivo de cambio</th>
                <th>L</th>
                <th>M</th>
                <th>Mi</th>
                <th>J</th>
                <th>V</th>
                <th>S</th>
                <th>D</th>
                <th>Acciones</th>
            </thead>
            <tbody id="tblCambiopuesto">
            </tbody>
        </table>
    </div>

    <!-- Modal Vacantes -->
    <div class="modal fade" id="modalVacantes" tabindex="-1" aria-labelledby="modalVacantesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalVacantesLabel">Vacantes disponibles de esta semana</h5>
        </div>
        <div class="modal-body">
            <div class="row">
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
                        Consulta desde esta tabla las vacantes disponibles de esta semana, encontraras los dias DISPONIBLES marcados con una '✔', por el contrario los NO DISPONIBLES con '✘'.
                    </small>
                </div>
            </div>
            <br />
            <div class="table-responsive">
            <table class="table">
                <thead class="table-dark text-center">
                <tr>
                    <th>NoEmp</th>
                    <th>Vacante libre</th>
                    <th>Máquina</th>
                    <th>L</th>
                    <th>M</th>
                    <th>Mi</th>
                    <th>J</th>
                    <th>V</th>
                    <th>S</th>
                    <th>D</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody id="tblDisponibles"></tbody>
            </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                <i class="fa-solid fa-xmark"></i>    
                Cerrar
            </button>
        </div>
        </div>
    </div>
    </div>

</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="./js/index.js"></script>