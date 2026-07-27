<?php
require_once("../Session/seguridad.php");
require_once(__DIR__ . "/../../conexion.php");
require_once("php/guard.php");

if (is_null($_SESSION["admincursos"])) {
    header("Location:../index/index.php");
    exit;
}

$Verificarsesion = new VerificarSesion();
$Verificarsesion->esEnSupervisores();

require_once("../index/header.php");
?>

<!-- DRIVER JS -->
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">
    <h5 class="tittlecont">Validación de información en solicitudes de tiempo extra</h5>

    <div style="float:right" class="p-1 ayuda">
        <button id="btnAyuda" class="btn btn-info">
            <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
        </button>
    </div>
    <br />

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
                    Desde esta seccion valida la información del empleado para que sea enviado a autorización de gerente
            </small>
        </div>
    </div>

    <div class="row mt-2">
        <div class="table-responsive">
                <!-- Controles de paginación y buscador -->
                <br>
                <div class="card card-body">
                    <div class="row">
                        <div class="col-2">
                            <b>
                                <label> Filtra por cantidad </label>
                            </b>
                            <select id="pageSizeSelect" class="form-select form-select-sm d-inline-block" style="width:80px;">
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>    
                        </div>

                        <div class="col-4">
                            <b>
                                <label for="folioSelect">Filtra por folios:</label>
                            </b>
                            <select id="folioSelect" class="form-select form-select-sm d-inline-block" style="width:180px;">
                            </select>                            
                        </div>

                        <div class="col-2">
                            <button id="allFolios" class="btn btn-sm btn-warning"> 
                                <i class="fa-solid fa-filter"></i>
                                Vizualizar todos 
                            </button>
                        </div>
                    </div>
                <br>                
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Id</th>
                                <th>Folio</th>
                                <th>NoEmp</th>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <th>Puesto</th>
                                <th>De (Hora Inicio)</th>
                                <th>A (Hora Fin)</th>
                                <th>Motivo</th>
                                <th>Razon</th>
                                <th>Turno</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tblTiempoExtra"></tbody>
                    </table>
                </div>
                
                <br>
                <!-- Controles para pasar a la siguiente pagina -->
                <div class="d-flex justify-content-end">
                    <button id="prevPage" class="btn btn-dark btn-sm">Anterior</button>
                    <span id="pageInfo" class="mx-2 my-auto"></span>
                    <button id="nextPage" class="btn btn-dark btn-sm">Siguiente</button>
                </div>            

            </div>
        </div>            
    </div>
    </div>    

    <!-- Modal para editar Tiempo Extra -->
    <div class="modal fade" id="modalEditarTE" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header text-dark">
            <h5 class="modal-title">Editar Solicitud de Tiempo Extra</h5>
            <button id="btnAyudaModal" class="btn btn-info btn-sm ms-2">
                <i class="fa-solid fa-circle-question"></i> Ver tutorial de registro de formato
            </button>
        </div>

        <div style="float-left" class="row">
            <div class="col-20">
                <small class="alert alert-danger" style= "float:left">
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
                        <small>
                        Modifica la información necesaria y presiona “Actualizar y Validar Tiempo Extra” en caso de ser APTO, o “Eliminar registro” en caso de NO SER APTO (según sus horarios de entrada).
                        <br />
                        <p>
                        Una vez que presiones el botón “Actualizar y Validar Tiempo Extra”, el registro se marcará como válido (apto para tiempo extra). Por ello, es importante actualizar y validar únicamente aquellos registros correctos pero con algún error en la detección del turno u otro aspecto.
                        En caso de querer eliminarlo, simplemente presiona el botón “Eliminar registro”.
                        </p>
                        </small>
                </small>
            </div>
        </div>
        
        <div class="modal-body">
            <form id="formEditarTE">
            <input type="hidden" id="editId">

            <div class="row mb-2">
                <div class="col-md-2">
                    <label>Folio</label>
                    <input type="text" class="form-control" id="editFolio" readonly>
                </div>                
                <div class="col-md-3">
                    <label>No. Empleado</label>
                    <input type="text" class="form-control" id="editNoEmp" readonly>
                </div>
                <div class="col-md-7">
                    <label>Nombre</label>
                    <input type="text" class="form-control" id="editNombre" readonly>
                </div>
            </div>

            <div class="row mb-2">
                
                <div class="col-md-6">
                    <label>Departamento</label>
                    <input type="text" class="form-control" id="editDepto" readonly>
                </div>

                <div class="col-md-6">
                    <label>Puesto</label>
                    <input type="text" class="form-control" id="editPuesto" readonly>
                </div>                
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label>Turno</label>
                    <select class="form-select" id="editTurno">
                        <option value="">Seleccione...</option>
                        <option value="turno1">Turno 1</option>
                        <option value="turno2">Turno 2</option>
                        <option value="turno3">Turno 3</option>
                        <option value="mixto1">Mixto 1</option>
                        <option value="mixto2">Mixto 2</option>
                        <option value="mixto3">Mixto 3</option>
                        <option value="mixto4">Mixto 4</option>
                        <option value="turno3_12hrs">Turno 3 - 12 hrs</option>
                        <option value="turno2_12hrs">Turno 2 - 12 hrs</option>                        
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Hora Inicio de turno</label>
                    <input type="time" step=1 class="form-control" id="horaInicioTurno" readonly>
                </div>
                <div class="col-md-4">
                    <label>Hora Fin de turno</label>
                    <input type="time" step=1 class="form-control" id="horaFinTurno" readonly>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <label>Hora Inicio de turno extra</label>
                    <input type="time" step=1 class="form-control" id="editHoraI">
                </div>
                <div class="col-md-4">
                    <label>Hora Fin de turno extra</label>
                    <input type="time" step=1 class="form-control" id="editHoraF">
                </div>
                <div class="col-md-4">
                    <label>Hrs. extras segun lapsos de T. extra</label>                    
                    <input type="text" class="form-control" id="tiempoExtraTmp" readonly>
                </div>
            </div>

            <div class="mb-2">
                <label>Motivo</label>
                <input type="text" class="form-control" id="editMotivo" readonly>
            </div>

            <div class="mb-2">
                <label>Razón</label>
                <textarea class="form-control" id="editRazon"></textarea >
            </div>            

            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                <i class="fa-solid fa-arrow-rotate-left"></i> Regresar
            </button>
            <button type="button" class="btn btn-success" id="btnGuardarYVTE">
                <i class="fa-solid fa-check"></i>
                Actualizar y Validar Tiempo Extra
            </button>
            <button type="button" class="btn btn-danger" id="btnDelete">
                <i class="fa-solid fa-ban"></i>
                Eliminar registro
            </button>
        </div>
        </div>
    </div>
    </div>

</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/validarTiempoExtra.js"></script>