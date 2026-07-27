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
if (!$ibmSesion ||(!in_array($ibmSesion, $listaSupervisores) && !in_array($ibmSesion, $ibmPermitidos))) {
    // No está autorizado → redirigir
    header("Location:../index/index.php");
    exit;
}

// Si llega aquí, es supervisor autorizado
require_once(__DIR__ . "/../index/header.php");
?>


<link rel="stylesheet" href="css/estilosModal.css">
<link rel="stylesheet" href="css/estilosNav.css">
<!-- DRIVER JS -->
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">
    <h5 class="tittlecont">Tiempos Extra</h5>
    <div style="float:right" class="p-1 ayudaSupervisor">
        <button id="btnAyuda" class="btn btn-info">
            <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
        </button>
    </div>
    <br>
   
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
                Los tiempos extra se consideran apartir de 55 min. en adelante despues de tus hrs. reglamentarias segun tu turno.
            </small>
        </div>
    </div>
    <br />
   
    <div style="float:right;"><small>Fecha: </small><span id="fechaheader"></span></div><br />
    <div style="float:right;"><small>SUPERVISOR: </small><span> <?php echo $_SESSION["nombre"] ?></span></div><br />
    <span hidden id="clvdepartamento"> <?php echo $_SESSION["clvDepartamento"] ?></span>

    <div class="card card-body">
    <div class="row">
        <div class="col-2"><small>FOLIO</small><input type="text" id="folio" class="form-control form-control-sm" readonly></div>
        <div class="col-2"><small>Fecha</small><input type="date" id="fechaenc" class="form-control form-control-sm"></div>
        <div class="col-2"><small>Departamento</small><select id="departamentoenc" class="form-control form-control-sm"></select></div>
        <div class="col"><br /><button class="btn-success btn btn-sm" id="abrir"><i class="fa-solid fa-plus"></i> Crear folio</button></div>
       
        <div class="col"><br />
            <button class="btn bg-vista btn-sm" id="btnverfolio" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                <i class="fa-solid fa-binoculars"></i> <span id="txtview">Ver Folios</span>
            </button>
        </div>

        <?php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                require_once("php/guard.php");
                $Verificarsesion = new VerificarSesion();
                
                if (!isset($_SESSION['ibm']) || $_SESSION['ibm'] === 58998 || $_SESSION['ibm'] === 51947 || $_SESSION['ibm'] === 55268 || $_SESSION['ibm'] === 53224 || $Verificarsesion->esEnSupervisoresValidar() || obtenerSupervisoresIBM()) {
                ?>
                    <div class="col"><br />
                        <a href="#" id="creapdf" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-file-pdf"></i> Pre. de PDF
                        </a>
                    </div>
                <?php
                }
            ?>        

        <div class="col"><br />
            <button class="btn-danger btn btn-sm botonEmpezarDeNuevo" onClick="window.location.reload()">
                <i class="fa-solid fa-retweet"></i> Empezar de nuevo
            </button>
        </div>
        <input type="hidden" id="semanaFolioHidden">
    </div>
    </div>

    <div class="collapse m-2" id="collapseExample">
        <div class="card card-body">
            <ul class="nav nav-tabs mb-3" id="encTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
                Pendientes <span class="badge bg-warning text-dark" id="countPendientes">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="procesadas-tab" data-bs-toggle="tab" data-bs-target="#procesadas" type="button" role="tab">
                Terminados <span class="badge bg-success" id="countProcesadas">0</span>
                </button>
            </li>
            </ul>

            <div class="tab-content">
                <!-- Pendientes -->
                <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                    <div class="table-responsive" style="max-height: 200px;">
                    <table class="table table-sm">
                        <thead class="table-dark">
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Departamento</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="tblPendientes"></tbody>
                    </table>
                    </div>
                </div>

                <!-- Procesadas -->
                <div class="tab-pane fade" id="procesadas" role="tabpanel">
                    <div class="table-responsive" style="max-height: 200px;">
                    <table class="table table-sm">
                        <thead class="table-dark">
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Departamento</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="tblProcesadas"></tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="formtiempoextra" class="border m-2 p-2 rounded">
        <small>
            COMPLETA LOS CAMPOS CORRESPONDIENTES:
        </small>
        <div class="row">
            <div class="col-1">
                <small>No. Emp</small>
                <input type="number" id="noemp" class="form-control form-control-sm" />
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
                <small>Motivo del tiempo extra</small>
                <select id="motivos" class="form-control form-control-sm"></select>
            </div>

            <div class="col">
                <small>Inicio de T. extra: </small>

                <!-- Motivo 8: Cambio de horario — opciones de horas (3 o 5) -->
                <select id="cambiohrario" class="form-control form-control-sm" hidden>
                    <option value="2.5">2.5 horas</option>
                    <option value="3">3 horas</option>
                    <option value="5">5 horas</option>
                </select>

                <!-- Motivo 5: Hora de comida — opciones de duración -->
                <select id="horacomida" class="form-control form-control-sm" hidden>
                    <option value="00:30">30 minutos</option>
                    <option value="01:00">1 hora</option>
                </select>

                <input type="time" step="1" id="horai" class="form-control form-control-sm" readonly/>
            </div>

            <div class="col">
                <small>Fin de T. extra:</small>
                <input type="time" step="1" id="horaf" class="form-control form-control-sm" readonly/>
            </div>
        </div>

        <div class="row my-2">
            <div class="col">
                <small>Maquina</small>
                <select id="maquinas" class="form-control form-control-sm"></select>
            </div>
            <div class="col">
                <small>Fecha de solicitud de T. extra</small>
                <input type="date" id="fechainput" class="form-control form-control-sm" />
            </div>
            <div class="col">
                <small>Razon del tiempo extra</small>
                <input type="text" id="razon" class="form-control form-control-sm" />
            </div>
            <div class="col">
                <small>Horas solicitadas de T. extra:</small>
                <input type="text" id="duracionTE"
                    placeholder="hh:mm"
                    class="form-control form-control-sm"
                    required
                    pattern="^([01]\d|2[0-3]):([0-5]\d)$"
                    title="Formato válido: hh:mm (00:00 a 23:59)" />
            </div>

            <div class="col">
                <small>Selecciona tu turno:</small>
                <select id="turnosel" name="turnosel" class="form-control form-control-sm" disabled required>
                    <option value="">Selecciona tu turno</option>
                    <option value="turno1">Turno 1</option>
                    <option value="turno2">Turno 2</option>
                    <option value="turno3">Turno 3</option>                    
                    <!-- <option value="turno2_12hrs">Turno 2 — 12 hrs</option>
                    <option value="turno3_12hrs">Turno 3 — 12 hrs</option> -->
                    <option value="mixto1">Mixto 1</option>
                    <option value="mixto2">Mixto 2</option>
                    <option value="mixto3">Mixto 3</option>
                    <option value="mixto4">Mixto 4</option>
                </select>                
            </div>            

            <div id="horariosDeTurno" name="horariosDeTurno" class="col card" style="display:none; margin-top:5px;">
                <small><b>HORARIO SEGÚN TURNO SELECCIONADO:</b></small>
                <br />
                <b><label class="text-primary" id="valorTurnoHora">Info</label></b>
            </div>
            

            <div id="contenedorCheckboxes" class="col card" style="margin-top:5px; padding:8px;">
                <small><strong><code>Selecciona según tu caso:</code></strong></small>

                <!-- Checkbox 12 hrs: solo visible bajo condiciones específicas de turno/horas -->
                <div id="contenedorCheckbox12hrs" style="display:none; margin-bottom:4px;">
                    <input type="checkbox" id="checkboxTurno3">
                    <small> 12 hrs</small>
                </div>

                <!-- Anticipo: siempre visible -->
                <div style="margin-bottom:4px;">
                    <input type="checkbox" id="checkboxAnticipo">
                    <small> Anticipo</small>
                </div>

                <!-- Apoyo: siempre visible -->
                <div>
                    <input type="checkbox" id="checkboxApoyo">
                    <small> Reingreso</small>
                </div>
            </div>

            <!-- Inputs hidden para cálculos internos -->
            <input type="hidden" id="turnoSeleccionadoHidden">
            <input type="hidden" id="horaFinalSinMargenHidden">
            <input type="hidden" id="horaFinalConMargenHidden">
            <input type="hidden" id="estadoHidden" value="null">
            <!-- Valores esperados: "empleado" o "sindicalizado" -->
            <input type="hidden" id="tipoEmpleadoHidden" value="">

            <div class="col"><br />
                <button class="bg-success btn btn-sm text-white" id="guardar">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar y enviar
                </button>
            </div>
           
            <div class="col"><br />
                <button type="reset" class="btn bg-vista btn-sm LimpiarCampos">
                    <i class="fa-solid fa-soap"></i> Limpiar campos
                </button>
            </div>

            <?php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                require_once("php/guard.php");
                $Verificarsesion = new VerificarSesion();

                if (!isset($_SESSION['ibm']) || $_SESSION['ibm'] === 58998 || $_SESSION['ibm'] === 51947 || $_SESSION['ibm'] === 55268 || $_SESSION['ibm'] === 53224 || $Verificarsesion->esEnSupervisoresValidar() || obtenerSupervisoresIBM()) {
                ?>
                    <div class="col">
                        <br />
                        <button class="btn-warning btn btn-sm" id="consultar">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            VALIDAR SOLICITUDES
                        </button>
                    </div>
                <?php
                }
            ?>

        </div>
    </form>

    <div>
        <br>
        <div style="float:left" >
            <div class="col-20">    
                <small class="alert alert-success">
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
                    Consulta en esta tabla el estado de las ultimas solicitudes segun el folio seleccionado (Solo si no han sido aprobadas/rechazadas).
                </small>
            </div>
        </div>
        <br>
        <br>
        <div class="table-responsive" style="max-height: 200px;">
            <table class="table table-sm" width="1000px;">
                <thead class="table-dark">
                    <th>Id</th>
                    <th>NoEmp</th>
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Puesto</th>
                    <th>Fecha</th>
                    <th>De</th>
                    <th>A</th>
                    <th>Maquina</th>
                    <th>Motivo</th>
                    <th>Razon</th>
                    <th>Turno</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </thead>
                <tbody id="tbltiempoextra">
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalOverlay" tabindex="-1" aria-labelledby="modalOverlayLabel" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">                
                <div class="modal-header">
                    <h5 class="tittlecont" id="modalOverlayLabel">Cambio Temporal de Turno</h5>

                    <button id="btnAyudaModal" class="btn btn-info btn-sm ms-2">
                        <i class="fa-solid fa-circle-question"></i> Ver tutorial de registro de formato
                    </button>
                </div>

                <form method="POST" id="formCambio" target='_blank'>
                    <div class="doc-layout">
                        <div class="doc-sidebar">
                            <span> PARA MAYOR INFORMACIÓN FAVOR DE CONTACTAR A SU SUPERVISOR</span>
                        </div>
                       
                        <div class="doc-main">
                            <div class="doc-header">
                                <div class="doc-header-logo">
                                    <div class="logo-row">
                                        <img src="../img/logo.jpg" width="200">
                                    </div>
                                </div>
                           
                                <div class="doc-title-wrap">
                                    <div class="doc-title">
                                        Cambio Temporal de Turno
                                    </div>                                    
                                </div>
                            </div>

                            <input type="hidden" id="folioTiempoExtra" name="folioTiempoExtra">
                           
                            <div class="doc-body">
                                <div class="row">
                                    <div class="campo grow">
                                        <span class="lbl">Fecha:</span>
                                        <input class="inp" type="date" name="fecha_emision" id="fecha_emision">
                                    </div>

                                    <div class="campo grow">
                                        <span class="lbl">Depto:</span>
                                        <input class="inp" type="text" name="Depto_m" id="Depto_m">
                                    </div>
                                </div>
                                <!-- IBM del empleado (se recupera del form de tiempo extra) -->
                                <input type="hidden" name="ibm_empleado" id="ibm_empleado_modal">

                                <!-- IBM del supervisor (de la sesión) -->
                                <input type="hidden" name="ibm_autoriza" id="ibm_autoriza"
                                    value="<?php echo htmlspecialchars($_SESSION['ibm']); ?>">


                                <div class="row">
                                    <div class="campo grow">
                                        <span class="lbl"> A: </span>
                                        <div class="campo grow">
                                            <input class="inp" type="text" name="nombre_receptor" id="nombre_receptor" placeholder="Nombre completo del receptor">
                                        </div>
                                    </div>
                                    <div class="campo grow">
                                        <span class="lbl">De:</span>
                                        <input class="inp" type="text" name="de_area" id="de_area" placeholder="Nombre de supervisor" value="<?php echo htmlspecialchars($_SESSION['nombre']); ?>">
                                    </div>
                                </div>

                                <div class="intro"> <strong> Por medio de la presente se le informa el siguiente cambio de: </strong></div>

                                <div class="row">
                                    <div class="campo grow">
                                        <span class="lbl"> Horario: </span>
                                        <input class="inp" type="text" name="horario_texto" id="horario_texto" placeholder="Primer turno...">
                                    </div>
                                    <div class="campo grow">
                                        <span class="lbl"> Rol: </span>
                                        <input class="inp" type="text" name="rol" id="rol" placeholder="Encargado de ...">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="campo grow">
                                        <span class="lbl"> A partir del día: </span>
                                        <input class="inp" type="date" name="fecha_inicio" id="fecha_inicio" >
                                    </div>
                                    <div class="campo grow">
                                        <span class="lbl"> Hasta: </span>
                                        <input class="inp" type="date" name="hasta_el" id="hasta_el" placeholder="Ej: fecha límite">
                                    </div>
                                </div>

                                <div class="horario-box">
                                    <div class="h-col">
                                        <div class="h-col-title">
                                            Debiendose presentar a:
                                        </div>
                                        <div class="row" style="margin-bottom:0; align-items:flex-end;">
                                            <div class="campo grow">
                                                <span class="lbl">Turno: </span>
                                                <input class="inp" type="text" name="turno_presentacion" id="turno_presentacion" placeholder="Ej: 1er Turno / 2do turno" >
                                            </div>
                                            <span style="font-size:9px; padding-bottom:4px;"> </span>
                                            <div class="campo grow">
                                                <span class="lbl"> Hora </span>
                                                <input class="inp" type="time" name="hora_presentacion" id="hora_presentacion" >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="h-col">
                                        <div class="h-col-title">
                                            Horario:
                                        </div>
                                        <div class="row" style="margin-bottom:0; align-items:flex-end;">
                                            <div class="campo grow">
                                                <span class="lbl"> En el horario</span>
                                                <input class="inp" type="time" name="horario_desde" id="horario_desde" placeholder="Ej: 1" >
                                            </div>
                                            <span style="font-size:9px; padding-bottom:4px;"> </span>
                                            <div class="campo grow">
                                                <span class="lbl"> A este horario</span>
                                                <input class="inp" type="time" name="horario_hasta" id="horario_hasta" >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="h-col">
                                        <div class="h-col-title">Conductor: </div>
                                        <div class="campo grow">
                                            <span class="lbl"> De: </span>
                                            <input class="inp" type="text" name="hasta_tripulacion" id="hasta_tripulacion" placeholder="Ej: Nombre">
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-box">
                                    <div class="row" style="margin-bottom:0; align-items:flex-end;">
                                        <span class="lbl" style="white-space:nowrap;">Sus descansos: </span>
                                        <div class="campo grow">
                                            <input class="inp" type="text" name="descansos" id="descansos" placeholder="Especifica si aplica">
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-box">
                                    <div class="row" style="margin-top:5px;">
                                        <span class="lbl" style="white-space:nowrap; padding-bottom:3px;">Adicional los días: </span>
                                        <div class="campo grow">
                                            <input class="inp" type="text" name="dias_adicionales" id="dias_adicionales" placeholder="Especifica si aplica">
                                        </div>
                                    </div>
                                </div>

                                <div class="sub-box">
                                    <div class="row" style="margin-top:5px;">
                                        <span class="lbl" style="white-space:nowrap; padding-bottom:3px;">En el horario: </span>
                                        <div class="campo grow">
                                            <input class="inp" type="text" name="horario_adicional" id="horario_adicional" placeholder="Especifica si aplica">
                                        </div>
                                    </div>
                                </div>
                            </div>
                           
                            <div class="doc-footer">
                                <span> KCM-173881 </span>
                                <span> Ref-8-702A-18 </span>
                                <span> Rev-01 </span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fa-solid fa-rectangle-xmark"></i>
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-success" id="btnGuardar">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Guardar Cambio Temporal de Turno
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/index.js"></script>