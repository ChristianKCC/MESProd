<?php
require_once("../Session/seguridad.php");
require_once(__DIR__ . "/../Vacaciones/php/vacacionesLogistica.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ibmSesion = $_SESSION["ibm"] ?? null;
$listaSupervisores = obtenerSupervisoresIBM();
// $ibmPermitidos = [60040, 58998, 22622, 51947, 55268, 53224];
$ibmPermitidos = [60040, 58998, 22622, 51947, 55268, 53224, 55075, 53412, 27825, 30950, 59610, 55075, 28342];

if (!$ibmSesion || (!in_array($ibmSesion, $listaSupervisores) && !in_array($ibmSesion, $ibmPermitidos))) {
    header("Location:../index/index.php");
    exit;
}

require_once(__DIR__ . "/../index/header.php");
?>

<link rel="stylesheet" href="../Tiempoextra/css/estilosModal.css">
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css" />
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">

    <h5 class="tittlecont">Cambio Temporal de Turno</h5>

    <div style="float:right" class="p-1">
        <button id="btnAyuda" class="btn btn-info ">
            <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
        </button>
    </div>
    <br>

    <div style="float:left" class="row mb-2">
        <div class="col-20">
            <small class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                    class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                    aria-label="Info:">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                </svg>
                Desde aquí puedes registrar un Cambio Temporal de Turno sin necesidad de asociarlo a un tiempo extra.
            </small>
        </div>
    </div>
    <br>

    <div style="float:right;">
        <small>SUPERVISOR: </small><span><?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
    </div>
    <br>

    <!-- ── FORMULARIO ───────────────────────────────────────────────────────── -->
    <div class="doc-layout border rounded m-2 p-2">

        <div class="doc-sidebar">
            <span>PARA MAYOR INFORMACIÓN FAVOR DE CONTACTAR A SU SUPERVISOR</span>
        </div>

        <div class="doc-main">

            <div class="doc-header">
                <div class="doc-header-logo">
                    <div class="logo-row">
                        <img src="../img/logo.jpg" width="200">
                    </div>
                </div>
                <div class="doc-title-wrap">
                    <div class="doc-title">Cambio Temporal de Turno</div>
                </div>
            </div>

            <form id="formCambioTurno">

                <!-- IBM oculto del supervisor en sesión — se usará en el PDF para su firma -->
                <input type="hidden" name="ibm_sesion" id="ibm_sesion"
                    value="<?php echo htmlspecialchars($ibmSesion); ?>">

                <div class="doc-body">

                    <!-- ── Fila: IBM empleado + campos autollenados ── -->
                    <div class="row mb-2">
                        <div class="col-2">
                            <small>IBM Empleado</small>
                            <input type="number" id="ibm_empleado" name="ibm_empleado"
                                class="form-control form-control-sm" placeholder="No. Emp">
                        </div>
                        <div class="col">
                            <small>Nombre receptor</small>
                            <!-- Se llena automáticamente al escribir el IBM del empleado -->
                            <input class="form-control form-control-sm" type="text" name="nombre_receptor"
                                id="nombre_receptor" placeholder="Nombre recuperado" readonly>
                        </div>
                        <div class="col">
                            <small>Departamento</small>
                            <!-- Se llena automáticamente al escribir el IBM del empleado -->
                            <input class="form-control form-control-sm" type="text" name="Depto_m" id="Depto_m"
                                placeholder="Departamento recuperado" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="campo grow">
                            <span class="lbl">Fecha:</span>
                            <input class="inp" type="date" name="fecha_emision" id="fecha_emision">
                        </div>
                        <div class="campo grow">
                            <span class="lbl">De (Supervisor):</span>
                            <input class="inp" type="text" name="de_area" id="de_area"
                                value="<?php echo htmlspecialchars($_SESSION['nombreFull']); ?>"
                                placeholder="Nombre de supervisor">
                        </div>
                    </div>

                    <div class="intro">
                        <strong>Por medio de la presente se le informa el siguiente cambio de:</strong>
                    </div>

                    <div class="row">
                        <div class="campo grow">
                            <span class="lbl"> Horario (de acuerdo a rol): </span>
                            <!-- <input class="inp" type="text" name="horario_texto" id="horario_texto"
                                placeholder="Primer turno..."> -->
                            <select name="horario_texto" id="horario_texto" class="inp">
                                <option value="">Selecciona tu turno</option>
                                <option value="turno1">Turno 1</option>
                                <option value="turno2">Turno 2</option>
                                <option value="turno3">Turno 3</option>
                                <option value="turno1_12hrs">Turno 1 (12 hrs)</option>
                                <option value="turno2_12hrs">Turno 2 (12 hrs)</option>
                                <option value="turno3_12hrs">Turno 3 (12 hrs)</option>
                                <option value="mixto1">Mixto 1</option>
                                <option value="mixto2">Mixto 2</option>
                                <option value="mixto3">Mixto 3</option>
                                <option value="mixto4">Mixto 4</option>
                            </select>
                        </div>
                        <div class="campo grow">
                            <span class="lbl">Tripulacion:</span>
                            <input class="inp" type="text" name="rol" id="rol" placeholder="Tripulacion ...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="campo grow">
                            <span class="lbl">A partir del día:</span>
                            <input class="inp" type="date" name="fecha_inicio" id="fecha_inicio">
                        </div>
                        <div class="campo grow">
                            <span class="lbl">Hasta:</span>
                            <input class="inp" type="date" name="hasta_el" id="hasta_el">
                        </div>
                    </div>

                    <div class="horario-box">
                        <div class="h-col">
                            <div class="h-col-title">Debiéndose presentar a:</div>
                            <div class="row" style="margin-bottom:0; align-items:flex-end;">
                                <div class="campo grow">
                                    <span class="lbl">Turno:</span>
                                    <!-- <input class="inp" type="text" name="turno_presentacion"
                                        id="turno_presentacion" placeholder="Ej: 1er Turno / 2do turno"> -->
                                    <select class="inp" name="turno_presentacion" id="turno_presentacion">
                                        <option value="">Selecciona tu turno</option>
                                        <option value="turno1">Turno 1</option>
                                        <option value="turno2">Turno 2</option>
                                        <option value="turno3">Turno 3</option>
                                        <option value="turno1_12hrs">Turno 1 (12 hrs)</option>
                                        <option value="turno2_12hrs">Turno 2 (12 hrs)</option>
                                        <option value="turno3_12hrs">Turno 3 (12 hrs)</option>
                                        <option value="mixto1">Mixto 1</option>
                                        <option value="mixto2">Mixto 2</option>
                                        <option value="mixto3">Mixto 3</option>
                                        <option value="mixto4">Mixto 4</option>
                                    </select>
                                </div>
                                <!-- <div class="campo grow">
                                    <span class="lbl">Hora:</span> -->
                                <input hidden class="inp" type="time" name="hora_presentacion" id="hora_presentacion"
                                    value="00:00:00">
                                <!-- </div> -->
                            </div>
                        </div>

                        <div class="h-col">
                            <div class="h-col-title">Horario:</div>
                            <div class="row" style="margin-bottom:0; align-items:flex-end;">
                                <div class="campo grow">
                                    <span class="lbl">En el horario:</span>
                                    <input class="inp" type="time" name="horario_desde" id="horario_desde">
                                </div>
                                <div class="campo grow">
                                    <span class="lbl">A este horario:</span>
                                    <input class="inp" type="time" name="horario_hasta" id="horario_hasta">
                                </div>
                            </div>
                        </div>

                        <!-- <div class="h-col">
                            <div class="h-col-title">Conductor:</div>
                            <div class="campo grow">
                                <span class="lbl">De:</span> -->
                        <input hidden class="inp" type="text" name="hasta_tripulacion" id="hasta_tripulacion"
                            placeholder="Ej: Nombre" value="-">
                        <!-- </div>
                        </div> -->
                    </div>

                    <div class="sub-box">
                        <div class="row" style="margin-bottom:0; align-items:flex-end;">
                            <span class="lbl" style="white-space:nowrap;">Sus descansos:</span>
                            <div class="campo grow">
                                <input class="inp" type="text" name="descansos" id="descansos"
                                    placeholder="Especifica si aplica">
                            </div>
                        </div>
                    </div>

                    <div class="sub-box">
                        <div class="row" style="margin-top:5px;">
                            <span class="lbl" style="white-space:nowrap; padding-bottom:3px;">Adicional los días:</span>
                            <div class="campo grow">
                                <input class="inp" type="text" name="dias_adicionales" id="dias_adicionales"
                                    placeholder="Especifica si aplica">
                                <input class="inp" type="text" name="horario_adicional" id="horario_adicional"
                                    placeholder="Continua en esta linea">
                            </div>
                        </div>
                    </div>

                    <!-- <div class="sub-box">
                        <div class="row" style="margin-top:5px;">
                            <span class="lbl" style="white-space:nowrap; padding-bottom:3px;">En el horario:</span>
                            <div class="campo grow">
                                
                            </div>
                        </div>
                    </div> -->

                </div><!-- /doc-body -->

                <div class="doc-footer">
                    <span>KCM-173881</span>
                    <span>Ref-8-702A-18</span>
                    <span>Rev-01</span>
                </div>

            </form><!-- /formCambioTurno -->

        </div><!-- /doc-main -->
    </div><!-- /doc-layout -->

    <!-- ── BOTONES DE ACCIÓN ──────────────────────────────────────────────── -->
    <div class="row m-2 mt-3 gap-2">
        <div class="col-auto">
            <button class="btn btn-success" id="btnGuardarCambio">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Cambio Temporal de Turno
            </button>
        </div>
        <div class="col-auto">
            <button class="btn btn-secondary" id="btnLimpiar">
                <i class="fa-solid fa-soap"></i> Limpiar campos
            </button>
        </div>
    </div>

    <!-- ── TABLA DE REGISTROS ─────────────────────────────────────────────── -->
    <div class="m-2 mt-4">
        <small class="alert alert-success d-inline-block mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                aria-label="Info:">
                <path
                    d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
            </svg>
            Registros de Cambios Temporales de Turno creados desde esta vista.
        </small>

        <div class="table-responsive" style="max-height: 300px;">
            <table class="table table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha emisión</th>
                        <th>Receptor</th>
                        <th>Departamento</th>
                        <th>De</th>
                        <th>Horario</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Turno presentación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tblCambiosTurno">
                    <tr>
                        <td colspan="10" class="text-center text-muted">
                            <small>Cargando registros...</small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /container -->

<?php require_once("../index/footer.php"); ?>
<script type="module" src="js/cambioTurno.js"></script>