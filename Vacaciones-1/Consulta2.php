<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
require_once("./php/vacacionesLogistica.php");
?>
<link rel="stylesheet" href="css/estilosConsulta.css">

<!-- DRIVER JS -->
<link rel="stylesheet" href="css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<!-- Identificacion del tipo -->
<body data-tipo="<?= col($empleado, COL_TIPO) ?>" data-rol="<?= $supervisor !== null ? 'SUPERVISOR' : 'NORMAL' ?>">

<!-- Empleado y Supervisor -->
<?php if (col($empleado, COL_TIPO) === 'EMPL' && $supervisor !== null): ?>
    <div style="float:right" class="p-4 ayudaSupervisor">
        <button id="btnAyuda" class="btn btn-info">
            <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
        </button>
    </div>
    <div class="d-flex align-items-center mb-4 gap-3">

        <div class="header-vacaciones">
            <h4 class="mb-0 fw-bold text-white">Mis vacaciones !</h4>
            <p class="bienvenida text-white">
            <br/>
            ¡Bienvenido Supervisor! :
                <?php if ($empleado !== null): ?>
                        <?= col($empleado, COL_NOMBRE) ?>
                <?php else: ?>
                    Ocurrio un error inesperado, intenta mas tarde !
                <?php endif; ?>
            </p>
        </div>

        <div class="header-vacaciones-consulta">
            <div class="row">
                <small>
                    <p><code><b>BUSCA POR LOS SIGUIENTES ELEMENTOS:</b></code></p>
                </small>
                <div class="col-3 busquedaIBM">
                    <small><b><p class="bienvenida">IBM: </p></b></small>
                    <input type="text" id="ibmFiltro" class="form-control form-control-sm" placeholder="Escribe el IBM del empleado" />
                </div>
                <div class="col-3 busquedaNOMBRE">
                    <small><b><p class="bienvenida">NOMBRE: </p></b></small>
                    <input type="text" id="nombreFiltro" class="form-control form-control-sm" placeholder="Escribe el nombre del empleado" />
                </div>
                <div class="col-2 botonBuscarEspecifica">
                    <br />
                    <button class="btn btn-primary text-white" id="consultarEmpleado" name="consultarEmpleado">
                        <i class="fa-brands fa-searchengin"></i> Buscar empleado
                    </button>
                </div>

                <div class="col-2 botonBuscarPropia">
                    <br />
                    <button class="btn btn-success text-white" id="verInformacion">
                        <i class="fa-solid fa-user"></i> Consultar mis datos
                    </button>
                </div>

                <div class="col-2 botonGenerarReporte">
                    <br />
                    <button class="btn btn-warning text-black" id="generarReporte" name="generarReporte">
                        <i class="fa-solid fa-file-pdf"></i> Consultar/generar reportes de vacaciones
                    </button>
                </div>
            </div>
            <input type="hidden" id="ibmActual" name="ibmActual">
            <input type="hidden" id="nombreActual" name="nombreActual">
            <input type="hidden" id="diasActual" name="diasActual">
            <input type="hidden" id="empleadoActual" name="empleadoActual">
            <input type="hidden" id="fingresoActual" name="fingresoActual">
        </div>

    </div>

    <!-- Manejo de falta de csv -->
    <?php if(!$csvExiste): ?>
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <p class="mb-0"> El sistema de vacaciones no está disponible en este momento, intenta mas tarde. </p>
            </div>
        </div>

    <!-- Manejo de que no exista empleado -->
    <?php elseif ($empleado === null): ?>
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <p class="mb-0"><code>No se encontró información sobre tus vacaciones, si crees que es un error notifica al área de Recursos Humanos</code> </p>
            </div>
        </div>

        <?php else: ?>

        <div  style="max-width: 1500px; margin: 20px auto;">
            <div class="row ">
                <div class="col-md-7">
                    <div class="card mb-3 datainfosup">
                        <div class="card-header bg-white fw-semibold py-3 border-bottom">
                            <i class="fa-solid fa-address-card"></i> TUS DATOS:                            
                        </div>
                        <div class="card-body py-4">
                            <div class="row g-4">
                                <div class="col-6">
                                    <div class="dato-label">IBM:</div>
                                    <div class="dato-valor ibm"><code></code></div>
                                </div>
                                <div class="col-6">
                                    <div class="dato-label">Nombre:</div>
                                    <div class="dato-valor nombre"><code></code></div>
                                </div>
                                <div class="col-6">
                                    <div class="dato-label">Fecha de ingreso:</div>
                                    <div class="dato-valor fingreso"><code></code></div>
                                </div>
                                <div class="col-6">
                                    <div class="dato-label">Antigüedad:</div>
                                    <div class="dato-valor antiguedad"><code></code></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card dataVacacionesDiassup">
                        <div class="card-header bg-white fw-semibold py-3 border-bottom">
                            <i class="fa-solid fa-calendar-day"></i> DÍAS DISPONIBLES DE VACACIONES:
                        </div>
                        <div class="card-body text-center py-4">
                            <div class="dias-badge"></div>
                            <div class="dias-label">Días de vacaciones restantes</div>
                        </div>
                    </div>

                </div>

                <div class="col-md-5">
                    <div class="card infoPersonalsup">
                        <div class="card-header bg-white fw-semibold py-3 border-bottom">
                            <i class="fa-solid fa-circle-info"></i> INFORMACIÓN ADICIONAL:
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between">                            
                            <!-- <?php
                                require_once(__DIR__ . "/solicitudesVacaciones.php")
                            ?> -->

                            <!-- Contenedor para la tabla de solicitudes -->
                            <!-- <div id="tablaSolicitudes" style="display:none;"></div> -->

                            <p class="mt-3"><strong>Siguiente aniversario:</strong></p>
                            <p id="proximoAniversario" class="text-muted" style="display:none;">
                                <b><code></code></b>
                            </p>

                            <div class="mt-4">
                                <p class="mt-3"><stong><b>Solicita tus vacaciones: </b></stong></p>
                                <button type="button" id="btnSolicitarVacaciones" class="btn btn-warning" style="display:none;">
                                    <i class="fa-solid fa-calendar-plus"></i> Solicitar Vacaciones
                                </button>
                            </div>

                            <div class="mt-4">
                                <p id="labelP" class="text-muted" style="display:none;"><code><b>No cuentas con días disponibles, pero puedes solicitar un adelanto.</b></code></p>
                                <button type="button" id="btnAdelantarVacaciones" class="btn btn-warning" style="display:none;">
                                    <i class="fa-solid fa-calendar-plus"></i> Solicita un adelanto de tus vacaciones
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de solicitudes de vacaciones -->
        <div class="modal fade" id="modalSolicitudesVacaciones" tabindex="-1" aria-labelledby="modalSolicitudesLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
            
            <div class="modal-header text-primary">
                <h5 class="modal-title" id="modalSolicitudesLabel">
                <i class="fa-solid fa-calendar-check"></i> Solicitudes de Vacaciones Realizadas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body">
                <table class="table table-striped table-bordered" id="tablaSolicitudesVacaciones">
                <thead class="table-dark">
                    <tr>
                    <th>Folio</th>
                    <th>NoEmp</th>
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Fecha solicitud</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
                </table>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
            
            </div>
        </div>
        </div>

    <?php endif; ?>

<!-- Caso de empleado sin ser supervisor -->
<?php elseif (col($empleado, COL_TIPO) === 'EMPL' && $supervisor == null): ?>
    <div style="float:right" class="p-4">
        <button id="btnAyuda" class="btn btn-info ayudaEmpleado">
            <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
        </button>
    </div>

    <div class="d-flex align-items-center mb-4 gap-3">
        <div class="header-vacaciones">
            <h4 class="mb-0 fw-bold text-white">Mis vacaciones !</h4>
            <p class="bienvenida text-white">
            <br/>
            ¡Bienvenido! :
                <?php if ($empleado !== null): ?>
                        <?= col($empleado, COL_NOMBRE) ?>
                <?php else: ?>
                    Ocurrio un error inesperado, intenta mas tarde !
                <?php endif; ?>
            </p>
        </div>        
    </div>    

    <?php if(!$csvExiste): ?>
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <p class="mb-0"> El sistema de vacaciones no está disponible en este momento, intenta mas tarde. </p>
            </div>
        </div>

    <?php elseif ($empleado === null): ?>
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <p class="mb-0"><code>No se encontró información sobre tus vacaciones, si crees que es un error notifica al área de Recursos Humanos</code> </p>
            </div>
        </div>

    <?php else: ?>
        <div style="max-width: 1500px; margin: 20px auto;">        
            <div class="row ">
                <div class="col-md-7">
                    <div class="card mb-3 card-datainfo">
                        <div class="card-header bg-white fw-semibold py-3 border-bottom">
                            <i class="fa-solid fa-address-card"></i> TUS DATOS:
                        </div>
                        <div class="card-body py-4">
                            <div class="row g-4">
                                <div class="col-6">
                                    <div class="dato-label">IBM:</div>
                                    <div class="dato-valor"><code><?= col($empleado, COL_IBM) ?></code></div>
                                </div>
                                <div class="col-6">
                                    <div class="dato-label">Nombre:</div>
                                    <div class="dato-valor"><code><?= col($empleado, COL_NOMBRE) ?></code></div>
                                </div>
                                <div class="col-6">
                                    <div class="dato-label">Fecha de ingreso:</div>
                                    <div class="dato-valor"><code>
                                        <?php 
                                        $fechaNormalizada = normalizarFechaISO(col($empleado, COL_FINGRESO));
                                        echo $fechaNormalizada;
                                        ?>
                                    </code></div>
                                </div>
                                <div class="col-6">
                                    <div class="dato-label">Antigüedad:</div>
                                    <div class="dato-valor"><code>
                                        <?php
                                        $fechaNormalizada = normalizarFechaISO(col($empleado, COL_FINGRESO));
                                        echo calcularAntiguedad($fechaNormalizada);
                                        ?>
                                    </code></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-dataVacacionesDias">
                        <div class="card-header bg-white fw-semibold py-3 border-bottom">
                            <i class="fa-solid fa-calendar-day"></i> DÍAS DISPONIBLES DE VACACIONES:
                        </div>
                        <div class="card-body text-center py-4">
                            <div class="dias-badge"><?= $diasDisponibles ?></div>
                            <div class="dias-label">Días de vacaciones restantes</div>
                        </div>
                    </div>

                </div>

                <div class="col-md-5">
                    <div class="card card-infoPersonal">
                        <div class="card-header bg-white fw-semibold py-3 border-bottom">
                            <i class="fa-solid fa-circle-info"></i> INFORMACIÓN ADICIONAL:
                        </div>
                        
                        <div class="card-body d-flex flex-column justify-content-between">
                            <?php
                                require_once(__DIR__ . "/solicitudesVacaciones.php")
                            ?>

                            <p class="mt-3"><strong>Siguiente aniversario:</strong></p>
                            <p class="text-muted">
                                <b>
                                    <code>
                                    <?php
                                    $fechaIngreso = col($empleado, COL_FINGRESO);
                                    $partes = explode('/', str_replace('-', '/', $fechaIngreso));
                                    if(count($partes) === 3){
                                        [$mes, $dia, $anio] = $partes;
                                        $proximo = mktime(0,0,0,(int)$mes,(int)$dia,date("Y"));
                                        echo date("m/d/Y", $proximo);
                                    }
                                    ?>
                                    </code>
                                </b>
                            </p>                            

                            <?php if($diasDisponibles > 0): ?>
                                <div class="mt-4">
                                    <p class="mt-3"><strong>Solicita tus vacaciones:</strong></p>
                                    <form method="POST" action="solicitarJE.php">
                                        <input type="hidden" name="tipo" value="Normal">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fa-solid fa-calendar-plus"></i> Solicitar vacaciones
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>                                
                                <div class="mt-4">
                                    <p class="text-muted"><code><b>No cuentas con días disponibles, pero puedes solicitar un adelanto.</b></code></p>
                                    <form method="POST" action="solicitarJE.php">
                                        <input type="hidden" name="tipo" value="Adelanto">
                                        <input type="hidden" name="dias" value="0">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fa-solid fa-calendar-plus"></i> Solicita un adelanto de tus vacaciones
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>

<!-- ************************************************************************************************************************************************************* -->
<!-- SINDICALIZADO -->
<?php elseif (col($empleado, COL_TIPO) === 'SIND'): ?>
    <div style="display:flex; justify-content:center; align-items:center; height:80vh;">
        <div class="card mb-3 infoSind">
            <div class="card-header bg-white fw-semibold py-3 border-bottom">
                <i class="fa-solid fa-address-card"></i> SOBRE TUS VACACIONES:
            </div>
            <div class="card-body py-4">
                <div class="row g-4">
                    <div class="dato-label">
                        <h5>
                            <code>
                                <b>
                                    Consulta tus dias de vacaciones con tu supervisor de área.
                                </b>
                            </code>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- No hay identificacion de empleado -->
<?php elseif ($empleado == null): ?>
    <div style="display:flex; justify-content:center; align-items:center; height:80vh;">
        <div class="card mb-3">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <h5>
                    <code>
                        <b>
                            IBM no reconocido, si crees que es un error consulta a RRHH.
                        </b>
                    </code>
                </h5>
            </div>
        </div>
    </div>
<?php endif; ?>

<script type="module" src="js/consulta.js"></script>
<?php require_once("../index/footer.php") ?>