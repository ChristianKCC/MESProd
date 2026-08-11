<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
require_once("./php/vacacionesLogistica.php");
?>
<link rel="stylesheet" href="css/estilosConsulta.css">

<!-- DRIVER JS -->
<link rel="stylesheet" href="css/estilosDriverJs.css">
<link rel="stylesheet" href="css/estilosNav.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css" />
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
                <h4 class="mb-0 fw-bold text-white"><i class="fa-solid fa-umbrella-beach"></i> Mis vacaciones !</h4>
                <p class="bienvenida text-white">
                    <br />
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
                    <div class="col-2 busquedaIBM">
                        <small><b>
                                <p class="bienvenida">IBM: </p>
                            </b></small>
                        <input type="text" id="ibmFiltro" class="form-control form-control-sm"
                            placeholder="Escribe el IBM del empleado" />
                    </div>
                    <div class="col-2 busquedaNOMBRE">
                        <small><b>
                                <p class="bienvenida">NOMBRE: </p>
                            </b></small>
                        <input type="text" id="nombreFiltro" class="form-control form-control-sm"
                            placeholder="Escribe el nombre del empleado" />
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

                    <div class="col-2 botonGenerarReporte">
                        <br />
                        <button type="button" class="btn btn-dark" id="btnVacacionistas" data-bs-toggle="modal"
                            data-bs-target="#modalVacacionistas">
                            <i class="fa-solid fa-umbrella-beach"></i> ¿Quiénes están de vacaciones?
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
        <?php if (!$csvExiste): ?>
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
                    <p class="mb-0">
                        <code>No se encontró información sobre tus vacaciones, si crees que es un error notifica al área de Recursos Humanos</code>
                    </p>
                </div>
            </div>

        <?php else: ?>

            <div style="max-width: 1500px; margin: 20px auto;">
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
                                    <p class="mt-3">
                                        <stong><b>Solicita tus vacaciones: </b></stong>
                                    </p>
                                    <button type="button" id="btnSolicitarVacaciones" class="btn btn-warning"
                                        style="display:none;">
                                        <i class="fa-solid fa-calendar-plus"></i> Solicitar Vacaciones
                                    </button>
                                </div>

                                <div class="mt-4">
                                    <p id="labelP" class="text-muted" style="display:none;">
                                        <code><b>No cuentas con días disponibles, pero puedes solicitar un adelanto.</b></code>
                                    </p>
                                    <button type="button" id="btnAdelantarVacaciones" class="btn btn-warning"
                                        style="display:none;">
                                        <i class="fa-solid fa-calendar-plus"></i> Solicita un adelanto de tus vacaciones
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de solicitudes de vacaciones -->
            <div class="modal fade" id="modalSolicitudesVacaciones" tabindex="-1" aria-labelledby="modalSolicitudesLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">

                        <div class="modal-header text-dark flex-column align-items-start">
                            <h5 class="modal-title mb-2" id="modalSolicitudesLabel">
                                <i class="fa-solid fa-calendar-check"></i> Solicitudes de Vacaciones Realizadas
                            </h5>

                            <small class="alert alert-warning w-100 mb-0 d-flex align-items-center">
                                <i class="fa-solid fa-circle-info me-2"></i>
                                Nota: Según te desplaces con las barras de navegación, las pestañas de 'Pendientes' y
                                'Procesadas' actualizarán su número en consecuencia.
                            </small>
                        </div>

                        <div class="modal-body">
                            <div class="row mb-2 p-2 rounded align-items-center ">
                                <p class="mb-0">
                                    <code>Puedes filtrar por los siguientes elementos (La información actual se mostrara en bloques de 10).</code>
                                </p>
                                <div class="col-3">
                                    <small><b>IBM:</b></small>
                                    <input type="text" id="filtroIbm" class="form-control form-control-sm" placeholder="NoEmp">
                                </div>
                                <div class="col-3">
                                    <small><b>Fecha:</b></small>
                                    <input type="date" id="filtroFecha" class="form-control form-control-sm">
                                </div>
                                <div class="col-6 text-end">
                                    <button class="btn btn-sm btn-success me-1" id="btnBuscar">
                                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                                    </button>
                                    <button class="btn btn-sm btn-primary" id="btnLimpiar">
                                        <i class="fa-solid fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                            </div>

                            <!-- Tabs -->
                            <ul class="nav nav-tabs mb-3" id="vacacionesTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab"
                                        data-bs-target="#pendientes" type="button" role="tab">
                                        Pendientes <span class="badge bg-warning text-dark" id="countPendientes">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="procesadas-tab" data-bs-toggle="tab"
                                        data-bs-target="#procesadas" type="button" role="tab">
                                        Procesadas <span class="badge bg-success" id="countProcesadas">0</span>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-2">
                                <!-- Pendientes -->
                                <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped table-bordered">
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
                                        <table class="table table-sm table-striped table-bordered">
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
                            </div>

                            <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
                                <button type="button" class="btn btn-outline-info" id="btnPrev">
                                    <i class="fa-solid fa-angles-left"></i>
                                </button>
                                <span id="paginaActual" class="small text-muted">
                                    Página 1 - 0 resultados
                                </span>
                                <button type="button" class="btn btn-outline-info" id="btnNext">
                                    <i class="fa-solid fa-angles-right"></i>
                                </button>
                            </div>
                        </div>


                        <div class="modal-footer">
                            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">
                                <i class="fa-solid fa-rectangle-xmark"></i>
                                Cerrar
                            </button>
                        </div>

                    </div>
                </div>
            </div>


            <div class="modal fade" id="modalVacacionistas" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fa-solid fa-umbrella-beach"></i> Empleados de vacaciones</h5>
                        </div>
                        <div class="modal-body">
                            <div class="row g-2 align-items-end mb-3">
                                <div class="col-md-4">
                                    <small>Inicio de vacaciones</small>
                                    <input type="date" id="vacFecha" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <small>Departamento</small>
                                    <select id="vacDepto" class="form-control form-control-sm"></select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary btn-sm w-100" id="vacAplicar">
                                        <i class="fa-solid fa-filter"></i> Aplicar
                                    </button>
                                </div>
                                <div class="col-md-2 form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="vacSoloCoincidencias">
                                    <label class="form-check-label" for="vacSoloCoincidencias"><small>Solo
                                            coincidencias</small></label>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height:60vh;">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-dark text-center">
                                        <tr>
                                            <th>Empleado</th>
                                            <th>Nombre</th>
                                            <th>Puesto</th>
                                            <th>Departamento</th>
                                            <th>Desde</th>
                                            <th>Hasta</th>
                                            <th>Autoriza</th>
                                            <th>Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tblVacacionistas"></tbody>
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
                <h4 class="mb-0 fw-bold text-white"><i class="fa-solid fa-umbrella-beach"></i> Mis vacaciones !</h4>
                <p class="bienvenida text-white">
                    <br />
                    ¡Bienvenido! :
                    <?php if ($empleado !== null): ?>
                        <?= col($empleado, COL_NOMBRE) ?>
                    <?php else: ?>
                        Ocurrio un error inesperado, intenta mas tarde !
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if (!$csvExiste): ?>
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
                    <p class="mb-0">
                        <code>No se encontró información sobre tus vacaciones, si crees que es un error notifica al área de Recursos Humanos</code>
                    </p>
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
                                                                                                                                                                                            </code>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="dato-label">Antigüedad:</div>
                                        <div class="dato-valor"><code>
                                                                                                                                                                                                <?php
                                                                                                                                                                                                $fechaNormalizada = normalizarFechaISO(col($empleado, COL_FINGRESO));
                                                                                                                                                                                                echo calcularAntiguedad($fechaNormalizada);
                                                                                                                                                                                                ?>
                                                                                                                                                                                            </code>
                                        </div>
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
                                    <b><code>
                                                                                                                                                                                        <?php
                                                                                                                                                                                        $fechaIngresoRaw = col($empleado, COL_FINGRESO);
                                                                                                                                                                                        $fechaISO = normalizarFechaISO($fechaIngresoRaw);

                                                                                                                                                                                        if ($fechaISO) {
                                                                                                                                                                                            $ingreso = DateTime::createFromFormat('Y-m-d', $fechaISO);

                                                                                                                                                                                            // Próximo aniversario en el año actual
                                                                                                                                                                                            $anioActual = (int) date("Y");
                                                                                                                                                                                            $proximo = DateTime::createFromFormat('Y-m-d', sprintf(
                                                                                                                                                                                                "%04d-%02d-%02d",
                                                                                                                                                                                                $anioActual,
                                                                                                                                                                                                $ingreso->format("m"),
                                                                                                                                                                                                $ingreso->format("d")
                                                                                                                                                                                            ));

                                                                                                                                                                                            // Si ya pasó este año, tomar el siguiente
                                                                                                                                                                                            $hoy = new DateTime();
                                                                                                                                                                                            if ($proximo < $hoy) {
                                                                                                                                                                                                $proximo->modify("+1 year");
                                                                                                                                                                                            }

                                                                                                                                                                                            echo $proximo->format("Y-m-d");
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo "-";
                                                                                                                                                                                        }
                                                                                                                                                                                        ?>
                                                                                                                                                                                        </code></b>
                                </p>

                                <?php if ($diasDisponibles > 0): ?>
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
                                        <p class="text-muted">
                                            <code><b>No cuentas con días disponibles, pero puedes solicitar un adelanto.</b></code>
                                        </p>
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