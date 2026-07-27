<?php
require_once("../Session/seguridad.php");
// if ($_SESSION["adminReportesProduccion"] != 1) {
//     header('Location: ../index/index');
// }
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-4">
    <h5 class="tittlecont">Entregas de producción</h5>
    <div class="row">
        <div class="col-2">
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
        <div class="col-1">
            <br>
            <button class="form-control form-control-sm btn btn-sm btn-primary" id="generarTabla"
                name="btnGenerarTabla"><i class="fa-solid fa-magnifying-glass"></i> Generar</button>
        </div>
        <!-- <div class="col-1">
            <br>
            <button class="form-control form-control-sm btn btn-sm btn-danger" id="btnGenerarReportePDF" name="btnGenerarReportePDF"><i class="fas fa-file-pdf"></i> Generar reporte</button>
        </div> -->
    </div>
    <div class="row">
        <!-- Tabla -->
        <div class="my-4 table-responsive table-striped " style="max-height: 650px;">
            <table class="table table-bordered" id="tblValeProductos">
                <thead class="table-dark">
                    <!-- <th>ID</th> -->
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th>Máquina</th>
                    <th>Cortes</th>
                    <th>Rechazos</th>
                    <!-- <th>Pañales empacados</th> -->
                    <th>Tiempo abajo</th>
                    <th>Minutos enhebrando</th>
                    <th>Tiempo arriba</th>
                    <th>Merma máquina</th>
                    <th>Tiempo perdido</th>
                    <th>Paros máquina</th>
                    <!-- <th>Hrs trabajadas</th> -->
                    <th>Acciones</th>
                </thead>
                <tbody id="tblTurnosAnteriores">

                </tbody>
            </table>
        </div>
    </div>

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
                        <div class="col-2">
                            <small class="fw-bold">Minutos enhebrando</small>
                            <input type="number" class="form-control form-control-sm" id="minutosenhebrando" min="0"
                                name="">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Tiempo arriba</small>
                            <input type="number" class="form-control form-control-sm" id="tiempoarriba" min="0" name="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2">
                            <small class="fw-bold">Tiempo perdido</small>
                            <input type="number" class="form-control form-control-sm" id="tiempoperdido" min="0"
                                name="">
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
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/ReporteTurnos.js" type="module"></script>