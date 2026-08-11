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
<link rel="stylesheet" href="./css/estilos.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css" />
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-3">
    <div class="cp-wrap">

        <!-- Barra superior -->
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                        class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                        aria-label="Warning:">
                        <path
                            d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                    </svg>
                    Desde esta sección elabora tus solicitudes de cambio de puesto.
                </small>
            </div>
        </div>
        <br />
        <br />

        <div style="float:right;"><small>Fecha: </small><span id="fechaheader"></span></div><br />
        <div style="float:right;"><small>Supervisor: </small><span> <?php echo $_SESSION["nombreFull"] ?></span></div>
        <br />
        <br />
        <span hidden id="clvdepartamento"> <?php echo $_SESSION["clvDepartamento"] ?></span>
        <span hidden id="ibmSup"> <?php echo $_SESSION["ibm"] ?></span>

        <input type="hidden" id="nosemana" name="nosemana" />

        <!-- Paso 1: Folio -->
        <div class="cp-card">
            <div class="cp-section-head">
                <span class="cp-step">1</span>
                <h6>Folio de la semana <small>— crea o selecciona un folio para empezar</small></h6>
            </div>
            <div class="row g-2 align-items-end">
                <div class="col">
                    <small class="form-lbl">Folio</small>
                    <input type="text" id="folio" class="form-control form-control-sm" readonly>
                </div>

                <div class="col">
                    <small class="form-lbl">Inicio de la semana</small>
                    <input type="date" id="fechainput" name="fechainput" class="form-control form-control-sm" />
                </div>

                <div class="col">
                    <small class="form-lbl">Departamento</small>
                    <select id="departamentoenc" class="form-control form-control-sm"></select>
                </div>

                <div class="col-12 col-md">
                    <div class="cp-actions d-flex flex-wrap gap-2">
                        <button class="btn btn-success btn-sm" id="abrir">
                            <i class="fa-solid fa-square-up-right"></i> Crear folio
                        </button>
                        <button class="btn bg-vista btn-sm" id="btnverfolio" data-bs-toggle="collapse"
                            href="#collapseExample" role="button">
                            <i class="fa-solid fa-binoculars"></i> <span id="txtview">Ver folios</span>
                        </button>
                        <button class="btn btn-warning btn-sm empezarDeNuevo" onClick="window.location.reload()">
                            <i class="fa-solid fa-retweet"></i> Empezar de nuevo
                        </button>
                        <a href="#" id="creapdf" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-file-pdf"></i> Pre. de PDF
                        </a>
                        <button class="btn btn-info btn-sm" id="btnVacantes" style="display:none;"
                            data-bs-toggle="modal" data-bs-target="#modalVacantes">
                            <i class="fa-solid fa-table"></i> Ver vacantes libres
                        </button>
                        <button type="button" class="btn btn-dark btn-sm" id="btnReporteCob" data-bs-toggle="modal"
                            data-bs-target="#modalReporteCob">
                            <i class="fa-solid fa-people-arrows"></i> ¿Quién cubre a quién?
                        </button>
                    </div>
                </div>
            </div>

            <div class="collapse mt-2" id="collapseExample">
                <div class="table-responsive" style="max-height:200px;">
                    <table class="table table-sm">
                        <thead class="table-dark">
                            <th>Folio</th>
                            <th>NoEmp Supervisor</th>
                            <th>Nombre Supervisor</th>
                            <th>Inicio de semana</th>
                            <th>Acciones</th>
                        </thead>
                        <tbody id="tblenc"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <form id="formtiempoextra">
            <!-- Paso 2: Empleado -->
            <div class="cp-card">
                <div class="cp-section-head">
                    <span class="cp-step">2</span>
                    <h6>Empleado que hará la cobertura <small>— carga sus datos por número</small></h6>
                </div>
                <div class="row g-2 align-items-end">
                    <!-- No. Emp más pequeño -->
                    <div class="col-6 col-md-1">
                        <small class="form-lbl">No. Emp</small>
                        <input type="number" id="noemp" name="noemp" class="form-control form-control-sm" />
                    </div>

                    <!-- Nombre -->
                    <div class="col-6 col-md-3">
                        <small class="form-lbl">Nombre</small>
                        <input type="text" id="nombre" class="form-control form-control-sm" readonly />
                    </div>

                    <!-- Departamento -->
                    <div class="col-6 col-md-2">
                        <small class="form-lbl">Departamento</small>
                        <input type="text" id="departamento" class="form-control form-control-sm" readonly />
                    </div>

                    <!-- Puesto -->
                    <div class="col-6 col-md-3">
                        <small class="form-lbl">Puesto</small>
                        <input type="text" id="puesto" class="form-control form-control-sm" readonly />
                    </div>

                    <!-- Máquina más compacta -->
                    <div class="col-12 col-md-3">
                        <small class="form-lbl">Máquina</small>
                        <select id="maquinas" name="maquinas" class="form-control form-control-sm"></select>
                    </div>
                </div>


                <div id="contenedorDisponibles" style="display:none;"></div>
            </div>

            <!-- Paso 3: Detalle de cobertura -->
            <div class="cp-card">
                <div class="cp-section-head">
                    <span class="cp-step">3</span>
                    <h6>Detalle de la cobertura <small>— días, puesto a cubrir y a quién</small></h6>
                </div>
                <div class="row g-3">
                    <!-- Días -->
                    <div class="col-12 col-md-3">
                        <div class="cp-dias diasSeleccion p-3 h-100">
                            <p class="fw-bold mb-2"><i class="fa-regular fa-calendar-check"></i> Días a cubrir
                            </p>
                            <?php
                            $dias = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'];
                            foreach ($dias as $id => $lbl): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="<?php echo $id ?>"
                                        name="dias[]">
                                    <label class="form-check-label" for="<?php echo $id ?>"><?php echo $lbl ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Puestos / motivo / porción / IBM -->
                    <div class="col-12 col-md-6">
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="form-lbl">Puesto actual</small>
                                <select id="puestoant" name="puestoant" class="form-control form-control-sm"
                                    disabled></select>
                            </div>
                            <div class="col-6">
                                <small class="form-lbl">Puesto a cubrir</small>
                                <select id="temporal" name="temporal" class="form-control form-control-sm"></select>
                            </div>
                            <div class="col-6">
                                <small class="form-lbl">Motivo</small>
                                <select id="motivos" name="motivos"
                                    class="form-control form-control-sm motivoSeleccion"></select>
                            </div>
                            <div class="col-6">
                                <small class="form-lbl">Porción del turno</small>
                                <select id="porcionTurno" class="form-control form-control-sm">
                                    <option value="completo">Turno completo</option>
                                    <option value="primera_mitad">Primera mitad</option>
                                    <option value="segunda_mitad">Segunda mitad</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <small class="form-lbl">IBM a cubrir</small>
                                <input type="text" id="IBMCubrir" class="form-control form-control-sm" />
                            </div>
                            <div class="col-6">
                                <small class="form-lbl">Puesto del IBM</small>
                                <input type="text" id="puestoCubrir" class="form-control form-control-sm" readonly />
                            </div>
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="fa-solid fa-lightbulb"></i>
                                    Usa <b>primera/segunda mitad</b> para repartir a un mismo cubierto entre dos
                                    personas.
                                    Con motivo <b>Vacante</b> no se pide IBM.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="col-12 col-md-3">
                        <div class="cp-guardar-box">
                            <button class="btn btn-success btn-sm" id="guardar" hidden>
                                <i class="fa-solid fa-floppy-disk"></i> Guardar cobertura
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-sm">
                                <i class="fa-solid fa-eraser"></i> Limpiar campos
                            </button>
                            <small class="text-muted text-center">El botón Guardar aparece al completar los
                                datos
                                requeridos.</small>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Tabla de solicitudes -->
        <div class="alert alert-warning d-flex align-items-center py-2">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <small>Solicitudes creadas y asociadas al folio seleccionado/creado.</small>
        </div>
        <div class="cp-card">
            <div class="table-responsive">
                <table class="table cp-table">
                    <thead class="table-dark">
                        <th>Id</th>
                        <th>Folio</th>
                        <th>NoEmp</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>Máquina</th>
                        <th>Puesto actual</th>
                        <th>Puesto temporal</th>
                        <th>Motivo</th>
                        <th>L</th>
                        <th>M</th>
                        <th>Mi</th>
                        <th>J</th>
                        <th>V</th>
                        <th>S</th>
                        <th>D</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody id="tblCambiopuesto"></tbody>
                </table>
            </div>
        </div>

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
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16"
                                    role="img" aria-label="Warning:">
                                    <path
                                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                                </svg>
                                Consulta desde esta tabla las vacantes disponibles de esta semana, encontraras
                                los dias
                                DISPONIBLES marcados con una '✔', por el contrario los NO DISPONIBLES con '✘'.
                            </small>
                        </div>
                    </div>
                    <br />
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Empleado/Vacante</th>
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

    <!-- Modal de reportes -->
    <div class="modal fade" id="modalReporteCob" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-people-arrows"></i> Coberturas — quién cubre a quién
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-4">
                            <small>Semana (elige cualquier día de esa semana)</small>
                            <input type="date" id="repFecha" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <small>Departamento</small>
                            <select id="repDepto" class="form-control form-control-sm"></select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary btn-sm w-100" id="repAplicar">
                                <i class="fa-solid fa-filter"></i> Aplicar
                            </button>
                        </div>
                        <div class="col-md-2 form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="repSoloRepetidos">
                            <label class="form-check-label" for="repSoloRepetidos"><small>Solo
                                    repetidos</small></label>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height:60vh;">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Semana</th>
                                    <th>Folio</th>
                                    <th>Autor</th>
                                    <th>F. creación</th>
                                    <th>Cubre</th>
                                    <th>Cubierto</th>
                                    <th>Tipo Porcióin</th>
                                    <th>Puesto temp.</th>
                                    <th>Máquina</th>
                                    <th>L</th>
                                    <th>M</th>
                                    <th>Mi</th>
                                    <th>J</th>
                                    <th>V</th>
                                    <th>S</th>
                                    <th>D</th>
                                </tr>
                            </thead>
                            <tbody id="tblCoberturas"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require_once("../index/footer.php") ?>
<script type="module" src="./js/index.js"></script>