<?php
require_once("../Session/seguridad.php");
require_once __DIR__ . '/Hooks/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ibmSesion = $_SESSION["ibm"] ?? null;

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");
$query = "SELECT ibm FROM Seg_permisosRARR";
$result = sqlsrv_query($conn, $query);

$ibmPermitidos = [];
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $ibmPermitidos[] = $row["ibm"];
}

// Validar acceso
if (!$ibmSesion || !in_array($ibmSesion, $ibmPermitidos)) {
    header("Location:../index/index.php");
    exit;
}

// Supervisor autorizado
require_once(__DIR__ . "/../index/header.php");
?>

<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
<link href="Assets/CSS/estilos.css" rel="stylesheet">

<div class="container p-4">
    <h5 class="tittlecont">Registro RARR</h5>
    <br>

    <div style="float:left" class="row">
        <div class="col-20">
            <small class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                    class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                    aria-label="Warning:">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                </svg>
                Desde este apartado registra tus RARR pasando por 3 pasos fundamentales.
            </small>
        </div>
    </div>
    <br /><br /><br />

    <div class="card card-body">
        <div id="moduloRegistroRARR">
            <div>
                <!-- ==================== TABS ==================== -->
                <ul class="nav nav-tabs nav-tabs-rarr" id="tabsRARR" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab1" type="button"
                            role="tab">Paso 1 – Análisis de Riesgos Potenciales</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab2" type="button"
                            role="tab">Paso 2 – Análisis de Protección de Maquinaria</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab3" type="button"
                            role="tab">Paso 3 – Controles de Ingeniería</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab4" type="button"
                            role="tab">Análisis de Registros RARR</button>
                    </li>
                </ul>

                <!-- Botón flotante Personalizar -->
                <button type="button" id="btnPersonalizar" class="btn btn-azul" data-bs-toggle="modal"
                    data-bs-target="#modalPersonalizar"
                    style="position:fixed;top:90px;right:24px;z-index:1030;border-radius:24px;box-shadow:0 3px 10px rgba(0,0,0,.2)">
                    <i class="fa-solid fa-sliders me-1"></i>Personalizar
                </button>

                <div class="tab-content">

                    <!-- ####################### TAB 1 ####################### -->
                    <div class="tab-pane fade show active" id="tab1" role="tabpanel">

                        <div class="row g-3 mb-4">
                            <div class="col-lg-4">
                                <label class="form-label">Máquina</label>
                                <select id="t1Maquina" class="form-select slcMaquina">
                                    <option value="">Seleccione una opción</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Sección / Módulo</label>
                                <select id="t1Seccion" class="form-select">
                                    <option value="">Seleccione la máquina primero</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">ID Equipo</label>
                                <input type="text" id="t1IdEquipo" class="form-control input-solo-lectura" readonly>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-9">

                                <!-- Registro de Escenario de Riesgo -->
                                <div class="card-seccion">
                                    <div class="encabezado"><i class="fa-solid fa-user-shield"></i>Registro de
                                        Escenario de Riesgo</div>
                                    <div class="cuerpo">
                                        <div class="row g-3">
                                            <div class="col-lg-3"><label class="form-label">Categoría de Peligro</label>
                                                <select id="t1Categoria" class="form-select form-select-sm">
                                                    <option value="">Seleccione</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3"><label class="form-label">Consecuencia</label>
                                                <select id="t1Consecuencia" class="form-select form-select-sm">
                                                    <option value="">Seleccione</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3"><label class="form-label">Mecanismo</label>
                                                <select id="t1Mecanismo" class="form-select form-select-sm">
                                                    <option value="">Seleccione</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3"><label class="form-label">Fuente</label>
                                                <select id="t1Fuente" class="form-select form-select-sm">
                                                    <option value="">Seleccione</option>
                                                </select>
                                            </div>
                                            <div class="col-12"><label class="form-label">Escenario de Riesgo</label>
                                                <input type="text" id="t1Escenario"
                                                    class="form-control form-control-sm input-solo-lectura" readonly
                                                    placeholder="Se arma al elegir consecuencia, mecanismo y fuente">
                                            </div>
                                        </div>

                                        <div class="row g-3 mt-1">
                                            <div class="col-lg-2 col-md-3"><label class="form-label">Severidad</label>
                                                <select id="t1Severidad" class="form-select form-select-sm">
                                                    <option value="">--</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-3"><label
                                                    class="form-label">Probabilidad</label>
                                                <select id="t1Probabilidad" class="form-select form-select-sm">
                                                    <option value="">--</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-3"><label class="form-label">Frecuencia</label>
                                                <select id="t1Frecuencia" class="form-select form-select-sm">
                                                    <option value="">--</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-3"><label class="form-label">N. de Personas del
                                                    Riesgo</label>
                                                <select id="t1Personas" class="form-select form-select-sm">
                                                    <option value="">--</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-6"><label class="form-label">Puntaje
                                                    RARR</label>
                                                <div class="nivel-riesgo-box nivel-nulo" id="t1Puntaje"><span>—</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-6"><label class="form-label">Nivel de
                                                    Riesgo</label>
                                                <div class="nivel-riesgo-box nivel-nulo" id="t1NivelRiesgo"><i
                                                        class="fa-solid fa-shield-halved"></i><span>—</span></div>
                                            </div>
                                        </div>

                                        <!-- Foto por escenario -->
                                        <div class="row g-3 mt-2">
                                            <div class="col-lg-6"><label class="form-label">Foto del escenario <span
                                                        class="text-danger">*</span></label>
                                                <input type="file" id="t1Imagen" class="form-control" accept="image/*">
                                                <div class="subtexto mt-1">JPG o PNG, máx. 5 MB. Se guarda con este
                                                    escenario.</div>
                                            </div>
                                            <div class="col-lg-6 text-center">
                                                <img id="t1ImagenPreview" src="" alt=""
                                                    style="display:none;max-height:120px;border:1px solid #d9dce1;border-radius:8px">
                                            </div>
                                        </div>

                                        <div class="text-end mt-3">
                                            <button type="button" id="t1BtnCancelarEdicion" class="btn btn-gris me-2"
                                                style="display:none">
                                                <i class="fa-solid fa-xmark me-1"></i>Cancelar edición</button>
                                            <button type="button" id="t1BtnAgregar" class="btn btn-gris">
                                                <i class="fa-solid fa-plus me-1"></i>Agregar a la lista</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Escenarios Registrados -->
                                <div class="card-seccion">
                                    <div class="encabezado"><i class="fa-regular fa-square-check"></i>Escenarios de
                                        Riesgo Registrados</div>
                                    <div class="cuerpo">
                                        <div class="table-responsive">
                                            <table class="table tabla-rarr mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Categoría de Peligro</th>
                                                        <th>Escenario de Riesgo</th>
                                                        <th>Severidad</th>
                                                        <th>Probabilidad</th>
                                                        <th>Frecuencia</th>
                                                        <th>N. de Personas</th>
                                                        <th>Estimación</th>
                                                        <th>Clasificación</th>
                                                        <th class="text-center">Foto</th>
                                                        <th class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="t1Tbody">
                                                    <tr>
                                                        <td colspan="11" class="text-center text-muted py-4">Aún no has
                                                            agregado escenarios</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="pie-tabla" id="t1PieTabla">Mostrando 0 registros</div>
                                    </div>
                                </div>

                                <!-- Peligros Genéricos -->
                                <div class="card-seccion">
                                    <div class="encabezado"><i class="fa-solid fa-triangle-exclamation"></i>Peligros
                                        Genéricos</div>
                                    <div class="cuerpo">
                                        <div class="subtexto">Peligros presentes por defecto en todo equipo. Sus
                                            puntajes se suman al total del RARR.</div>
                                        <div class="table-responsive">
                                            <table class="table tabla-rarr mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width:4%">#</th>
                                                        <th style="width:15%">Categoría de Peligro</th>
                                                        <th style="width:22%">Escenario de Riesgo</th>
                                                        <th style="width:13%">Severidad</th>
                                                        <th style="width:17%">Probabilidad</th>
                                                        <th style="width:10%">Frecuencia</th>
                                                        <th style="width:9%">N. Personas</th>
                                                        <th class="text-center" style="width:5%">Puntaje</th>
                                                        <th class="text-center" style="width:5%">Nivel</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="t1TbodyGen"></tbody>
                                            </table>
                                        </div>
                                        <div class="pie-tabla" id="t1PieGen">Cargando…</div>
                                    </div>
                                </div>

                                <!-- Botones del Paso 1 -->
                                <div>
                                    <div class="cuerpo">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" id="t1BtnLimpiar" class="btn btn-gris"><i
                                                    class="fa-solid fa-rotate-left me-1"></i>Limpiar</button>
                                            <button type="button" id="t1BtnContinuar" class="btn btn-azul">Continuar<i
                                                    class="fa-solid fa-arrow-right ms-1"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-lg-3">
                                <div class="card-resumen">
                                    <div class="titulo">Resumen de Riesgos</div>
                                    <div class="resumen-item">
                                        <div class="icono" style="background:#7c1d1d"><i
                                                class="fa-solid fa-skull-crossbones"></i></div>
                                        <div>
                                            <div class="etiqueta">Inaceptables</div>
                                            <div class="numero" id="resInaceptables">0</div>
                                        </div>
                                    </div>
                                    <div class="resumen-item">
                                        <div class="icono" style="background:#dc3545"><i
                                                class="fa-solid fa-exclamation-triangle"></i></div>
                                        <div>
                                            <div class="etiqueta">Altos</div>
                                            <div class="numero" id="resAltos">0</div>
                                        </div>
                                    </div>
                                    <div class="resumen-item">
                                        <div class="icono" style="background:#f0930d"><i class="fa-solid fa-shield"></i>
                                        </div>
                                        <div>
                                            <div class="etiqueta">Bajos</div>
                                            <div class="numero" id="resBajos">0</div>
                                        </div>
                                    </div>
                                    <div class="resumen-item mb-0">
                                        <div class="icono" style="background:#23923d"><i
                                                class="fa-solid fa-check-circle"></i></div>
                                        <div>
                                            <div class="etiqueta">Aceptables</div>
                                            <div class="numero" id="resAceptables">0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ####################### TAB 2 ####################### -->
                    <div class="tab-pane fade" id="tab2" role="tabpanel">

                        <div class="row g-3 mb-4">
                            <div class="col-lg-3"><label class="form-label">Máquina / Equipo</label>
                                <input type="text" id="t2Maquina" class="form-control input-solo-lectura" readonly>
                            </div>
                            <div class="col-lg-3"><label class="form-label">Sección / Módulo</label>
                                <input type="text" id="t2Componente" class="form-control input-solo-lectura" readonly>
                            </div>
                            <div class="col-lg-3"><label class="form-label">ID Equipo</label>
                                <input type="text" id="t2IdEquipo" class="form-control input-solo-lectura" readonly>
                            </div>
                            <div class="col-lg-3"><label class="form-label">Fecha Última Actualización</label>
                                <div class="input-group">
                                    <span class="input-group-text input-solo-lectura"><i
                                            class="fa-regular fa-calendar"></i></span>
                                    <input type="text" id="t2FechaUltima" class="form-control input-solo-lectura"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Una tarjeta por escenario -->
                        <div id="t2Cards"></div>

                        <!-- Peligros Genéricos con guardas -->
                        <div class="card-seccion">
                            <div class="encabezado"><i class="fa-solid fa-triangle-exclamation"></i>Reducción con
                                Guardas Actuales</div>
                            <div class="cuerpo">
                                <div class="subtexto">Severidad, frecuencia y personas se mantienen del Paso 1. Solo
                                    cambia el criterio de guarda, que recalcula el puntaje de cada peligro.</div>
                                <div class="table-responsive">
                                    <table class="table tabla-rarr mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:4%">#</th>
                                                <th style="width:26%">Escenario de Riesgo</th>
                                                <th style="width:28%">Criterio de Guarda Actual</th>
                                                <th style="width:12%">Severidad</th>
                                                <th style="width:9%">Frecuencia</th>
                                                <th style="width:9%">N. Personas</th>
                                                <th class="text-center" style="width:6%">Puntaje</th>
                                                <th class="text-center" style="width:6%">Nivel</th>
                                            </tr>
                                        </thead>
                                        <tbody id="t2TbodyGen"></tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                                    <div class="pie-tabla" id="t2PieGen">Completa el Paso 1</div>
                                    <div class="d-flex gap-2">
                                        <button type="button" id="t2BtnRegresar" class="btn btn-gris"><i
                                                class="fa-solid fa-arrow-left me-1"></i>Regresar</button>
                                        <button type="button" id="t2BtnContinuar" class="btn btn-azul">Continuar<i
                                                class="fa-solid fa-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ####################### TAB 3 ####################### -->
                    <div class="tab-pane fade" id="tab3" role="tabpanel">


                        <div class="row g-3 mb-4">
                            <div class="col-lg-3">
                                <label class="form-label">Máquina / Equipo</label>
                                <input type="text" id="t3Maquina" class="form-control input-solo-lectura" readonly>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Sección / Módulo</label>
                                <input type="text" id="t3Componente" class="form-control input-solo-lectura" readonly>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">ID Equipo</label>
                                <input type="text" id="t3IdEquipo" class="form-control input-solo-lectura" readonly>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Fecha Última Actualización</label>
                                <div class="input-group">
                                    <span class="input-group-text input-solo-lectura"><i
                                            class="fa-regular fa-calendar"></i></span>
                                    <input type="text" id="t3FechaUltima" class="form-control input-solo-lectura"
                                        readonly>
                                </div>
                            </div>
                            <!-- Aquí lo metemos en una columna y lo alineamos abajo -->
                            <div class="col-lg-1 d-flex align-items-end">
                                <div class="ref-normas">
                                    Revisión 2026<br>Ref. NOM-004-STPS-2020<br>ISO 12100
                                </div>
                            </div>
                        </div>

                        <!-- DIV 3: Controles de Ingeniería – Solución de Diseño Ideal (una tarjeta por escenario) -->
                        <div id="t3Cards"></div>

                        <!-- Peligros Genéricos con ingeniería -->
                        <div class="card-seccion">
                            <div class="encabezado"><i class="fa-solid fa-triangle-exclamation"></i>Potencial de
                                Reducción con Ingeniería</div>
                            <div class="cuerpo">
                                <div class="subtexto">Severidad, frecuencia y personas se mantienen del Paso 1. Solo
                                    cambia la medida de mitigación, que recalcula el puntaje de cada peligro.</div>
                                <div class="table-responsive">
                                    <table class="table tabla-rarr mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:4%">#</th>
                                                <th style="width:26%">Escenario de Riesgo</th>
                                                <th style="width:28%">Medidas de mitigación a implementar</th>
                                                <th style="width:12%">Severidad</th>
                                                <th style="width:9%">Frecuencia</th>
                                                <th style="width:9%">N. Personas</th>
                                                <th class="text-center" style="width:6%">Puntaje</th>
                                                <th class="text-center" style="width:6%">Nivel</th>
                                            </tr>
                                        </thead>
                                        <tbody id="t3TbodyGen"></tbody>
                                    </table>
                                </div>
                                <div class="pie-tabla" id="t3PieGen">Completa el Paso 2</div>
                            </div>
                        </div>

                        <!-- Botones del Paso 3 -->
                        <div>
                            <div class="cuerpo">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" id="t3BtnRegresar" class="btn btn-gris"><i
                                            class="fa-solid fa-arrow-left me-1"></i>Regresar</button>
                                    <button type="button" id="t3BtnRegistrar" class="btn btn-azul"><i
                                            class="fa-solid fa-floppy-disk me-1"></i>Registrar RARR</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ####################### TAB 4 ####################### -->
                    <div class="tab-pane fade" id="tab4" role="tabpanel">

                        <div class="row g-3 mb-4">
                            <div class="col-lg-3"><label class="form-label">Máquina</label>
                                <select id="t4Maquina" class="form-select slcMaquina">
                                    <option value="">Seleccione una opción</option>
                                </select>
                            </div>
                            <div class="col-lg-3"><label class="form-label">Sección / Módulo</label>
                                <select id="t4Seccion" class="form-select">
                                    <option value="">Seleccione la máquina primero</option>
                                </select>
                            </div>
                            <div class="col-lg-3"><label class="form-label">ID Equipo</label>
                                <input type="text" id="t4IdEquipo" class="form-control input-solo-lectura" readonly>
                            </div>
                        </div>

                        <div id="t4SinDatos" class="text-center text-muted py-5">Selecciona una máquina y su sección
                            para ver el RARR registrado</div>

                        <div id="t4Contenido" style="display:none">
                            <div class="row g-4">
                                <div class="col-lg-9">
                                    <div class="row g-3">
                                        <div class="col-lg-4">
                                            <div class="card-seccion h-100 mb-0">
                                                <div class="encabezado justify-content-center text-center"
                                                    style="background:#1f3d7c;color:#fff;border-radius:9px 9px 0 0">
                                                    Paso 1: Identificación de Peligros<br>sin Protecciones - "Peligro
                                                    Puro"</div>
                                                <div class="cuerpo text-center">
                                                    <div style="position:relative;height:160px"><canvas id="t4Gauge1"
                                                            height="150"></canvas></div>
                                                    <div class="fs-3 fw-bold" id="t4Marcador1">—</div>
                                                    <div class="fw-bold" id="t4Etiqueta1" style="color:#dc3545">Peligro
                                                        Puro</div>
                                                    <hr>
                                                    <table class="table tabla-rarr mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Nivel</th>
                                                                <th class="text-center">Escenarios</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="t4TablaNiveles"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="card-seccion h-100 mb-0">
                                                <div class="encabezado justify-content-center text-center"
                                                    style="background:#1f3d7c;color:#fff;border-radius:9px 9px 0 0">
                                                    Paso 2: Evaluación de la<br>Protección Actual</div>
                                                <div class="cuerpo text-center">
                                                    <div style="position:relative;height:160px"><canvas id="t4Gauge2"
                                                            height="150"></canvas></div>
                                                    <div class="fs-3 fw-bold" id="t4Marcador2">—</div>
                                                    <div class="fw-bold" style="color:#f0930d">Reducción con Guardas
                                                        Actuales</div>
                                                    <hr>
                                                    <table class="table tabla-rarr mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Criterio de Guarda</th>
                                                                <th class="text-center">Puntaje</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="t4TablaGuardas"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="card-seccion h-100 mb-0">
                                                <div class="encabezado justify-content-center text-center"
                                                    style="background:#1f3d7c;color:#fff;border-radius:9px 9px 0 0">
                                                    Paso 3: Reducción de Riesgo por<br>Controles de Ingeniería</div>
                                                <div class="cuerpo text-center">
                                                    <div style="position:relative;height:160px"><canvas id="t4Gauge3"
                                                            height="150"></canvas></div>
                                                    <div class="fs-3 fw-bold" id="t4Marcador3">—</div>
                                                    <div class="fw-bold" style="color:#23923d">Potencial de Reducción
                                                        con Ingeniería</div>
                                                    <hr>
                                                    <table class="table tabla-rarr mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Controles de Ingeniería</th>
                                                                <th class="text-center">Estatus</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="t4TablaControles"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="card-resumen mb-3">
                                        <div class="titulo">Resumen de Riesgos</div>
                                        <div class="resumen-item">
                                            <div class="icono" style="background:#7c1d1d"><i
                                                    class="fa-solid fa-skull-crossbones"></i></div>
                                            <div>
                                                <div class="etiqueta">Inaceptables</div>
                                                <div class="numero" id="t4ResInaceptables">0</div>
                                            </div>
                                        </div>
                                        <div class="resumen-item">
                                            <div class="icono" style="background:#dc3545"><i
                                                    class="fa-solid fa-exclamation-triangle"></i></div>
                                            <div>
                                                <div class="etiqueta">Altos</div>
                                                <div class="numero" id="t4ResAltos">0</div>
                                            </div>
                                        </div>
                                        <div class="resumen-item">
                                            <div class="icono" style="background:#f0930d"><i
                                                    class="fa-solid fa-shield"></i></div>
                                            <div>
                                                <div class="etiqueta">Bajos</div>
                                                <div class="numero" id="t4ResBajos">0</div>
                                            </div>
                                        </div>
                                        <div class="resumen-item mb-0">
                                            <div class="icono" style="background:#23923d"><i
                                                    class="fa-solid fa-check-circle"></i></div>
                                            <div>
                                                <div class="etiqueta">Aceptables</div>
                                                <div class="numero" id="t4ResAceptables">0</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-resumen">
                                        <div class="titulo">Resumen de Controles y Costos</div>
                                        <div class="mb-2">
                                            <div class="etiqueta">Avance de implementación</div>
                                            <div class="avance-linea">
                                                <div class="progress progress-rarr flex-grow-1">
                                                    <div class="progress-bar" id="t4BarraAvance" style="width:0%"></div>
                                                </div>
                                                <span class="pct" id="t4PctAvance">0%</span>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="etiqueta">Inversión Estimada</div>
                                            <div class="fs-5 fw-bold" id="t4Inversion">$ 0.00</div>
                                        </div>
                                        <hr>
                                        <div class="d-grid gap-2">
                                            <button type="button" id="t4BtnConcluir" class="btn btn-azul"><i
                                                    class="fa-solid fa-circle-check me-1"></i>Concluir RARR</button>
                                            <button type="button" id="t4BtnEditar" class="btn btn-gris"><i
                                                    class="fa-solid fa-pencil me-1"></i>Editar RARR</button>
                                            <button type="button" id="t4BtnEliminar" class="btn"
                                                style="background:#dc3545;color:#fff;font-weight:600;border-radius:8px">
                                                <i class="fa-regular fa-trash-can me-1"></i>Eliminar RARR</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== MODAL PERSONALIZAR ==================== -->
                <div class="modal fade" id="modalPersonalizar" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content" style="border-radius:12px">
                            <div class="modal-header" style="background:#1f3d7c;color:#fff;border-radius:12px 12px 0 0">
                                <h5 class="modal-title"><i class="fa-solid fa-sliders me-2"></i>Personalizar</h5>
                                <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-4">
                                    <div class="col-lg-8">
                                        <div class="card-seccion h-100 mb-0">
                                            <div class="encabezado"><i class="fa-solid fa-pen-to-square"></i><span
                                                    id="cfgTitulo">Configuraciones</span></div>
                                            <div class="cuerpo">
                                                <div id="cfgVacio" class="text-center text-muted py-5">Elige un ajuste
                                                    de la izquierda para configurarlo</div>
                                                <div id="cfgForm" style="display:none">
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-lg-9"><label class="form-label">Registro</label>
                                                            <select id="cfgRegistro" class="form-select">
                                                                <option value="">Seleccione para editar</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-3 d-flex align-items-end">
                                                            <button type="button" id="cfgBtnNuevo"
                                                                class="btn btn-gris w-100"><i
                                                                    class="fa-solid fa-plus me-1"></i>Agregar
                                                                nuevo</button>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div id="cfgCampos" class="row g-3"></div>
                                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                                        <button type="button" id="cfgBtnCancelar"
                                                            class="btn btn-gris">Cancelar</button>
                                                        <button type="button" id="cfgBtnGuardar" class="btn btn-azul"><i
                                                                class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="card-seccion h-100 mb-0">
                                            <div class="encabezado"><i class="fa-solid fa-list"></i>Ajustes</div>
                                            <div class="cuerpo p-2">
                                                <div class="d-grid gap-2" id="cfgMenu">
                                                    <button type="button" class="btn btn-gris text-start cfg-btn"
                                                        data-tipo="maquinas"><i
                                                            class="fa-solid fa-gear me-2"></i>Máquinas</button>
                                                    <button type="button" class="btn btn-gris text-start cfg-btn"
                                                        data-tipo="secciones"><i
                                                            class="fa-solid fa-diagram-project me-2"></i>Secciones</button>
                                                    <button type="button" class="btn btn-gris text-start cfg-btn"
                                                        data-tipo="categorias"><i
                                                            class="fa-solid fa-triangle-exclamation me-2"></i>Categorías
                                                        de Peligro</button>
                                                    <button type="button" class="btn btn-gris text-start cfg-btn"
                                                        data-tipo="consecuencias"><i
                                                            class="fa-solid fa-heart-crack me-2"></i>Consecuencias</button>
                                                    <button type="button" class="btn btn-gris text-start cfg-btn"
                                                        data-tipo="mecanismos"><i
                                                            class="fa-solid fa-gears me-2"></i>Mecanismos</button>
                                                    <button type="button" class="btn btn-gris text-start cfg-btn"
                                                        data-tipo="fuentes"><i
                                                            class="fa-solid fa-bolt me-2"></i>Fuentes</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script type="module" src="Assets/JS/analisis.js"></script>
    <?php require_once("../index/footer.php") ?>
</div>