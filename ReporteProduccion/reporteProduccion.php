<?php
require_once("../Session/seguridad.php");
// if ($_SESSION["permisoConfClaves"] != 1) {
//     header('Location: ../index/index');
// }
require_once("../index/header.php");
?>

<style>
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
</style>

<!-- Contenido -->
<div class="container p-4">
    <h5 class="tittlecont">Reporte de producción de máquinas</h5>
    <!-- Loader oculto por defecto -->
    <div id="loader" class="loader-overlay" style="display:none;">
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Generando reporte...</span>
        </div>
        <p class="text-white mt-2">Generando reporte, espera un momento...</p>
    </div>

    <div class="row">
        <div class="col-1">
            <small>Fecha</small>
            <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" />
        </div>
        <div class="col-2">
            <small>Maquina</small>
            <select class="form-control form-control-sm" id="maquinas" name="maquinas"></select>
        </div>
        <div class="col-2">
            <small>Turno</small>
            <select class="form-control form-control-sm" id="turnos" name="turnos"></select>
        </div>
        <div class="col-auto">
            <br>
            <button class="form-control form-control-sm btn btn-sm btn-primary" id="generarTabla"
                name="btnGenerarTabla"><i class="fa-solid fa-magnifying-glass"></i> REPORTE TURNOS</button>
        </div>
        <div class="col-auto">
            <br>
            <button class="form-control form-control-sm btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#modalReporteDepartamental" data-bs-whatever="7408"><i class="fa-solid fa-building"></i>
                REPORTE DEPARTAMENTAL</button>
        </div>
        <div class="col-auto">
            <br>
            <button class="form-control form-control-sm btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#modalReporteGerencia" data-bs-whatever="7408">
                <i class="fa-solid fa-chart-gantt"></i>
                REPORTE GERENCIA</button>
        </div>
        <div class="col-auto">
            <br>
            <button class="form-control form-control-sm btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#modalReporteDireccion" data-bs-whatever="7408">
                <i class="fa-solid fa-chart-gantt"></i>
                REPORTE DIRECCIÓN</button>
        </div>
        <div class="col-auto">
            <br>
            <button class="form-control form-control-sm btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#modalReporteProduccionContraloria">
                <i class="fa-solid fa-industry"></i>
                REPORTE CONTRALORÍA</button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
        <!-- Controles de paginación y buscador -->
        <div class="d-flex justify-content-between align-items-center mt-3 pagination-controls">
            <div>
                <label class="mb-0">
                    Mostrar:
                    <select id="pageSize" class="form-select form-select-sm d-inline-block" style="width:80px;">
                        <option value="10">10</option>
                        <option value="15" selected>15</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    registros
                </label>
            </div>
            <!-- Buscador -->
            <div class="me-2" style="flex:1; max-width:420px;" hidden>
                <input type="text" id="searchInput" class="form-control form-control-sm"
                    placeholder="Buscar por máquina..." />
            </div>
        </div>
        <br>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th>Máquina</th>
                    <th>Cortes</th>
                    <th>Rechazos</th>
                    <th>Tiempo abajo</th>
                    <!-- <th>Minutos enhebrando</th> -->
                    <th>Tiempo arriba</th>
                    <th>Merma máquina</th>
                    <!-- <th>Tiempo perdido</th> -->
                    <th>Paros máquina</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <br>
        <!-- Controles para pasar a la siguiente pagina -->
        <div class="d-flex justify-content-end">
            <button id="prevPage" class="btn btn-dark btn-sm">Anterior</button>
            <span id="pageInfo" class="mx-2 my-auto"></span>
            <button id="nextPage" class="btn btn-dark btn-sm">Siguiente</button>
        </div>
    </div>

    <!-- Modal editar datos -->
    <div class="modal fade" id="modadalEditRegistroTurno" tabindex="-1"
        aria-labelledby="modadalEditRegistroTurnoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modadalEditRegistroTurnoModalLabel">New message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Solo para efectos de programacion, siempre oculto -->
                        <div class="col-1" hidden>
                            <small>FolioBitacora</small>
                            <input type="number" class="form-control form-control-sm" id="folioBitacora" readonly>
                        </div>
                        <!-- Solo para efectos de programacion, siempre oculto -->
                        <!-- Elementos editables del registro de turno -->
                        <div class="col-2">
                            <small class="fw-bold">Cortes</small>
                            <input type="number" class="form-control form-control-sm" id="cortes" min="0" name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Rechazos</small>
                            <input type="number" class="form-control form-control-sm" id="rechazos" min="0" name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Tiempo abajo</small>
                            <input type="number" class="form-control form-control-sm" id="tiempoabajo" min="0" name="">
                        </div>
                        <div class="col-2" hidden>
                            <small class="fw-bold">Minutos enhebrando</small>
                            <input type="number" class="form-control form-control-sm" id="minutosenhebrando" min="0"
                                name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Tiempo arriba</small>
                            <input type="number" class="form-control form-control-sm" id="tiempoarriba" min="0" name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">No. paros</small>
                            <input type="number" class="form-control form-control-sm" id="paros" min="0" name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Horas trabajadas</small>
                            <input type="number" class="form-control form-control-sm" id="horastrabajadas" min="0"
                                step="0.5" value="0" name="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2" hidden>
                            <small class="fw-bold">Tiempo perdido</small>
                            <input type="number" class="form-control form-control-sm" id="tiempoperdido" min="0"
                                name="">
                        </div>

                        <div class="col-6">
                            <small class="fw-bold">Motivo de cambio</small>
                            <textarea class="form-control form-control-sm" id="motivoCambio" name=""></textarea>
                        </div>
                    </div>
                    <div class="row justify-content-end m-2">
                        <div class="col-2"> <button class="btn btn-sm btn-warning"
                                id="actualizarRegistroTurnoMaquina"><i class="fa-solid fa-floppy-disk"></i>
                                Actualizar</button></div>
                        <div class="col-2"> <button class="btn btn-sm btn-secondary" id="limpiarFormulario"><i
                                    class="fa-solid fa-rotate-left"></i> Limpiar</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal editar datos sin red-->
    <div class="modal fade" id="modalEditRegistroTurnoSinRed" tabindex="-1"
        aria-labelledby="modalEditRegistroTurnoSinRedLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditRegistroTurnoSinRedLabel">New message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Solo para efectos de programacion, siempre oculto -->
                        <div class="col-1" hidden>
                            <small>FolioBitacora</small>
                            <input type="number" class="form-control form-control-sm" id="folioBitacoraSR" readonly>
                        </div>
                        <!-- Solo para efectos de programacion, siempre oculto -->
                        <!-- Elementos editables del registro de turno -->
                        <div class="col-2">
                            <small class="fw-bold">Cortes</small>
                            <input type="number" class="form-control form-control-sm" id="cortesSinRed" min="0" name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Rechazos</small>
                            <input type="number" class="form-control form-control-sm" id="rechazosSinRed" min="0"
                                name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Tiempo abajo</small>
                            <input type="number" class="form-control form-control-sm" id="tiempoabajoSinRed" min="0"
                                name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Tiempo arriba</small>
                            <input type="number" class="form-control form-control-sm" id="tiempoarribaSinRed" min="0"
                                name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">No. paros</small>
                            <input type="number" class="form-control form-control-sm" id="parosSinRed" min="0" name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Horas trabajadas</small>
                            <input type="number" class="form-control form-control-sm" id="horastrabajadasSinRed" min="0"
                                step="0.5" value="0" name="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small class="fw-bold">Motivo de cambio</small>
                            <textarea class="form-control form-control-sm" id="motivoCambioSinRed" name=""></textarea>
                        </div>
                    </div>
                    <div class="row justify-content-end m-2">
                        <div class="col-2"> <button class="btn btn-sm btn-warning"
                                id="actualizarRegistroTurnoMaquinaSinRed"><i class="fa-solid fa-floppy-disk"></i>
                                Actualizar</button></div>
                        <div class="col-2"> <button class="btn btn-sm btn-secondary" id="limpiarFormulario"><i
                                    class="fa-solid fa-rotate-left"></i> Limpiar</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal reporte departamental -->
    <div class="modal fade" id="modalReporteDepartamental" tabindex="-1"
        aria-labelledby="modalReporteDepartamentalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReporteDepartamentalModalLabel">REPORTE POR DEPARTAMENTO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-2">
                            <small>FECHA INICIO</small>
                            <input type="date" class="form-control form-control-sm" id="fechaInicio"
                                name="fechaInicio" />
                        </div>
                        <div class="col-2">
                            <small>FECHA FIN</small>
                            <input type="date" class="form-control form-control-sm" id="fechaFin" name="fechaFin" />
                        </div>
                        <div class="col-3">
                            <small>DEPARTAMENTO</small>
                            <select class="form-control form-control-sm slc-departamentos"
                                name="departamentos"></select>
                        </div>
                        <div class="col-2">
                            <br>
                            <button class="form-control form-control-sm btn btn-sm btn-danger btn-generar"
                                data-reporte="departamentos"><i class="fas fa-file-pdf"></i> GENERAR
                                REPORTE</button>
                        </div>
                    </div>
                    <!-- <div class="row justify-content-end m-2">
                        <div class="col-2"> <button class="btn btn-sm btn-warning"
                                id="actualizarRegistroTurnoMaquina"><i class="fa-solid fa-floppy-disk"></i>
                                Actualizar</button></div>
                        <div class="col-2"> <button class="btn btn-sm btn-secondary" id="limpiarFormulario"><i
                                    class="fa-solid fa-rotate-left"></i> Limpiar</button></div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal reporte gerencia -->
    <div class="modal fade" id="modalReporteGerencia" tabindex="-1" aria-labelledby="modalReporteGerenciaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReporteGerenciaModalLabel">REPORTE GERENCIA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-5">
                            <small>FECHA</small>
                            <input type="date" class="form-control form-control-sm" id="fechaGerencia"
                                name="fechaInicio" />
                        </div>
                        <div class="col-4" hidden>
                            <small>DEPARTAMENTO</small>
                            <select class="form-control form-control-sm slc-departamentos" name="direcciones"></select>
                        </div>
                        <div class="col-5">
                            <br>
                            <button class="form-control form-control-sm btn btn-sm btn-danger btn-generar"
                                data-reporte="gerencia"><i class="fas fa-file-pdf"></i> GENERAR
                                REPORTE</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal reporte direccion -->
    <div class="modal fade" id="modalReporteDireccion" tabindex="-1" aria-labelledby="modalReporteDireccionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReporteDireccionModalLabel">REPORTE DIRECCIÓN</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-5">
                            <small>FECHA</small>
                            <input type="date" class="form-control form-control-sm" id="fechaDireccion"
                                name="fechaInicio" />
                        </div>
                        <div class="col-4" hidden>
                            <small>DEPARTAMENTO</small>
                            <select class="form-control form-control-sm slc-departamentos" name="direcciones"></select>
                        </div>
                        <div class="col-5">
                            <br>
                            <button class="form-control form-control-sm btn btn-sm btn-danger btn-generar"
                                data-reporte="direccion"><i class="fas fa-file-pdf"></i> GENERAR
                                REPORTE</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalReporteProduccionContraloria" tabindex="-1"
        aria-labelledby="modalReporteProduccionContraloriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReporteProduccionContraloriaLabel">REPORTE PRODUCCIÓN MES</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <form id="formReporte" method="POST" target="iframe_a" class="mb-4">
                            <div class="row">
                                <div class="col">
                                    <small>Fecha inicial</small>
                                    <input type="date" class="form-control form-control-sm" name="fechai" id="fechai"
                                        required>
                                </div>
                                <div class="col">
                                    <small>Fecha final</small>
                                    <input type="date" class="form-control form-control-sm" name="fechaf" id="fechaf"
                                        required>
                                </div>
                                <div class="col-4">
                                    <br>
                                    <button id="btnExcel"
                                        class="form-control form-control-sm btn btn-sm btn-success btn-generar"><i
                                            class="fa-solid fa-file-excel"></i> GENERAR REPORTE EXCEL</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--  -->
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/ReporteTurnos.js" type="module"></script>
<script src="js/ReporteDepartamentos.js" type="module"></script>
<script src="js/reporteProduccionContraloria.js" type="module"></script>