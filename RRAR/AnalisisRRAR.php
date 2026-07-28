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


<!-- DRIVER JS -->
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">

<link href="Assets/CSS/estilos.css" rel="stylesheet">

<div class="container p-4">
    <h5 class="tittlecont">Análisis RARR</h5>
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
                Desde este apartado consulta el total/avance de los RARR por departamento, maquina y sección.
            </small>
        </div>
    </div>
    <br />
    <br />
    <br />

    <div class="card card-body">
        <div id="moduloAnalisisRRS">
            <div>
                <!-- ==================== DEPARTAMENTO ==================== -->
                <div class="mb-4">
                    <label for="slcDepartamento" class="form-label">Departamento</label>
                    <select id="slcDepartamento" class="select-lista" size="1">
                        <option value="">Selecciona una opción</option>
                    </select>
                </div>

                <div class="row g-4">

                    <!-- ==================== COLUMNA IZQUIERDA ==================== -->
                    <div class="col-lg-4">
                        <div class="row g-3 mb-3">
                            <div class="col-4">
                                <div class="stat-card flex-column flex-xl-row text-center text-xl-start">
                                    <div class="stat-icono icono-azul"><i class="fa-solid fa-clipboard-check"></i></div>
                                    <div class="stat-info">
                                        <span class="stat-label">Concluidos</span>
                                        <span class="stat-num num-azul" id="statConcluidos">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card flex-column flex-xl-row text-center text-xl-start">
                                    <div class="stat-icono icono-naranja"><i class="fa-solid fa-hourglass-half"></i>
                                    </div>
                                    <div class="stat-info">
                                        <span class="stat-label">Pendientes</span>
                                        <span class="stat-num num-naranja" id="statPendientes">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card flex-column flex-xl-row text-center text-xl-start">
                                    <div class="stat-icono icono-teal"><i class="fa-solid fa-chart-pie"></i></div>
                                    <div class="stat-info">
                                        <span class="stat-label">Total</span>
                                        <span class="stat-num num-teal" id="statTotal">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-icono icono-azul"><i class="fa-solid fa-users"></i></div>
                                    <div class="stat-info">
                                        <span class="stat-label">Total de Personal</span>
                                        <span class="stat-num num-azul" id="statPersonal">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-icono icono-teal"><i class="fa-solid fa-graduation-cap"></i></div>
                                    <div class="stat-info">
                                        <span class="stat-label">Capacitados</span>
                                        <span class="stat-num num-teal" id="statCapacitados">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== COLUMNA CENTRO: CALIFICACIÓN + KPI ==================== -->
                    <div class="col-lg-5">
                        <table class="tabla-calificacion">
                            <thead>
                                <tr>
                                    <th>Calificación del Riesgo</th>
                                    <th>Nivel de Riesgo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Aceptable</td>
                                    <td id="calAceptable">0</td>
                                </tr>
                                <tr>
                                    <td>Bajo</td>
                                    <td id="calBajo">0</td>
                                </tr>
                                <tr>
                                    <td>Alto</td>
                                    <td id="calAlto">0</td>
                                </tr>
                                <tr>
                                    <td>Inaceptable</td>
                                    <td id="calInaceptable">0</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Panel KPI -->
                        <table class="kpi-tabla">
                            <tr>
                                <td class="kpi-negro" colspan="3" style="width:75%">% Áreas con<br>Riesgo Residual</td>
                                <td class="kpi-rojo" id="kpiPorcentaje" style="width:25%">0%</td>
                            </tr>
                            <tr>
                                <td class="kpi-negro" style="width:33%">Promedio Marcador</td>
                                <td class="kpi-rojo-grande" rowspan="2" id="kpiPromedio" style="width:30%">-</td>
                                <td class="kpi-verde" rowspan="2" style="width:12%"></td>
                                <!-- Año de creacion -->
                                <td class="kpi-beige" rowspan="2" id="kpiRiesgoTotal" style="width:25%">2026</td>
                            </tr>
                            <tr>
                                <td class="kpi-negro">Riesgo Total Planta</td>
                            </tr>
                        </table>
                    </div>

                    <!-- ==================== COLUMNA DERECHA: AVANCE TOTAL RARR ==================== -->
                    <div class="col-lg-3">
                        <div class="card-avance">
                            <div class="titulo-avance">AVANCE TOTAL RARR</div>
                            <div class="grafica-contenedor">
                                <canvas id="graficaAvance"></canvas>
                                <div class="grafica-centro">
                                    <div class="porcentaje" id="avancePorcentaje">0%</div>
                                    <div class="sub">Avance Total</div>
                                    <div class="detalle" id="avanceDetalle">0 / 0<br>actividades<br>completadas</div>
                                </div>
                            </div>
                            <div class="leyenda-avance">
                                <div class="fila">
                                    <span><span class="cuadro" style="background:#2563eb"></span>Realizados</span>
                                    <span id="leyendaRealizados">0% (0)</span>
                                </div>
                                <div class="fila">
                                    <span><span class="cuadro" style="background:#c9ccd2"></span>Pendientes</span>
                                    <span id="leyendaPendientes">0% (0)</span>
                                </div>
                            </div>
                            <div class="leyenda-total" id="leyendaTotal">Total: 0 actividades</div>
                        </div>
                    </div>
                </div>

                <!-- ==================== LISTAS MAQUINA / SECCIÓN / RARR ==================== -->
                <div class="row g-4 mt-2">
                    <div class="col-lg-4">
                        <div class="titulo-lista">MAQUINA</div>
                        <div class="lista-panel" id="listaMaquinas">
                            <div class="vacio">Selecciona un departamento</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="titulo-lista">SECCIÓN / EQUIPO</div>
                        <div class="lista-panel" id="listaSecciones">
                            <div class="vacio">Selecciona una máquina</div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="titulo-lista">RARR</div>
                        <div class="lista-panel" id="panelRARR">
                            <div class="vacio">Selecciona una sección / equipo</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Importaciones JS -->
<script type="module" src="Assets/JS/reporte.js"></script>
<?php require_once("../index/footer.php") ?>