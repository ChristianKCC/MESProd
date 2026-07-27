<?php
$carpeta = __DIR__ . '/solicitudes';
if(!is_dir($carpeta)){
    mkdir($carpeta, 0755, true);
}

require_once("../Session/seguridad.php");
require_once("../index/header.php");
require_once("./php/vacacionesLogistica.php");

// Recuperar datos enviados desde solicitar.php
$nombre = $_POST['nombre'] ?? '';
$ibm = $_POST['ibm'] ?? '';
$limite_dias = $_POST['limite_dias']   ?? '';
$tipo_empleado = $_POST['tipo_empleado'] ?? '';
// $dias = $_POST['dias'] ?? 0;

$dias = isset($_POST['dias']) ? (int)$_POST['dias'] : 0;

$dias_festivos = $_POST['dias_festivos'] ?? '0';
$fecha_ingreso_raw = $_POST['fecha_ingreso'] ?? '';
$fecha_ingreso = '';
$tipo_Solicitud = $_POST['tipo'] ?? '';

// Formateo de fechas
// if (!empty($fecha_ingreso_raw)) {
//     try {
//         $fechaObj = new DateTime($fecha_ingreso_raw);
//         $fecha_ingreso = $fechaObj->format('Y-m-d');
//     } catch (Exception $e) {
//         $fecha_ingreso = '';
//     }
// }

if (!empty($fecha_ingreso_raw)) {
    // Detectar si viene con guiones o diagonales
    $fecha_ingreso_raw = str_replace('-', '/', $fecha_ingreso_raw);

    // Forzar formato esperado (MM/DD/YYYY)
    $fechaObj = DateTime::createFromFormat('m/d/Y', $fecha_ingreso_raw);

    if (!$fechaObj) {
        // Intentar con formato alternativo (DD/MM/YYYY)
        $fechaObj = DateTime::createFromFormat('d/m/Y', $fecha_ingreso_raw);
    }

    $fecha_ingreso = $fechaObj ? $fechaObj->format('Y-m-d') : '';
}


// Recuperacion de fechas
$fecha_a = $_POST['fecha_a'] ?? '';
$diasSeleccionados = isset($_POST['dias_festivos']) ? explode(',', $_POST['dias_festivos']) : [];
$fechaDe = $_POST['fecha_de'] ?? '';

// Formateo de fechas
$fechaInicioMes = new DateTime($fechaDe);
$anio = $fechaInicioMes->format('Y');
$mes = $fechaInicioMes->format('m');
$primerDiaMes = new DateTime("$anio-$mes-01");
$diaSemanaInicio = (int)$primerDiaMes->format('N');

$anioS = (int)$anio + 1;

// Funcion de calcular la antiguedd


// Array de dias
$diasSemana = ['L','M','M','J','V','S','D'];

// Array de festivos
$diasFestivos = [
    "2026-01-01","2026-02-02","2026-03-16",
    "2026-05-01","2026-09-16","2026-11-16",
    "2026-12-25","2026-04-02","2026-04-03",
    "2026-11-02","2026-12-12","2026-12-24",
    "2026-12-31"
];

// Variables iniciales
$diasReposicionFestivo = 0;
$diasDescanso = 0;

$diasReposicionFestivo = (int)$diasReposicionFestivo;
$diasDescanso = (int)$diasDescanso;


// Calcular antes de imprimir los inputs
foreach ($diasSeleccionados as $fechaStr) {
    // Si es festivo
    if (in_array($fechaStr, $diasFestivos)) {
        $diasReposicionFestivo++;
    } else if ($fechaStr === 'R'){
        $diasReposicionFestivo++;
    }
    // Si el usuario marcó descanso en la selección (si lo manejas así)
    if ($fechaStr === 'D') {
        $diasDescanso++;
    }
}

// Suma total de dias
$ResTot = $dias + $diasReposicionFestivo + $diasDescanso;

// Identificar el origen
$origen = $_POST['origen'] ?? 'consulta.php';

// Condicionales a usar segun el tipo de usuario
// if ($origen === "solicitar.php" ) {
//     // Referencia a: limite_dias (Cantidad exacta de dias) || dias (dias solicitados de vacaciones, este NO) ||
//     $_SESSION['solicitud'] = [
//         'ibm' => $ibm,
//         'nombre' => $nombre,
//         'limite_dias' => $limite_dias,
//         'tipo_empleado' => $tipo_empleado,
//         'fingreso' => $fecha_ingreso
//     ];
// } 
// else if($origen === "solicitarJE.php"){

// }

if ($origen === "solicitar.php" ) {
    $_SESSION['solicitud'] = [
        'ibm' => $ibm,
        'nombre' => $nombre,
        'limite_dias' => $limite_dias,
        'tipo_empleado' => $tipo_empleado,
        'fingreso' => $fecha_ingreso,
        'tipo' => $tipo_Solicitud
    ];
} 
else if($origen === "solicitarJE.php"){
    $_SESSION['solicitud'] = [ 
        'tipo' => $tipo_Solicitud,
        'limite_dias' => $limite_dias
    ];
}

// Si no hay datos de identificacion la session se conserva sin ingun cambio en su estructura
else {
}

// Identificacion de dias y mes
$fechaInicio = new DateTime($_POST['fecha_de']);
$fechaFin = new DateTime($_POST['fecha_a']);

// Lista de meses involucrados
$mesesInvolucrados = [];
$cursor = clone $fechaInicio;
while ($cursor <= $fechaFin) {
    $mesesInvolucrados[] = [
        'anio' => $cursor->format('Y'),
        'mes' => $cursor->format('m')
    ];
    $cursor->modify('first day of next month');
}

?>


<link rel="stylesheet" href="css/finalizarSolicitud.css">
<!-- DRIVER JS -->
<link rel="stylesheet" href="css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">
    <h5 class="tittlecont">Revisión de datos para solicitud de vacaciones</h5>

    <div style="float:right" class="p-4">
      <button id="btnAyuda" class="btn btn-info ayudaEmpleado">
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
                Verifica tu información, una vez los datos esten correctos da click en "GUARDAR Y GENERAR PDF" para su posterior envio y autorización.
            </small>
        </div>
    </div>
    <br />
    <br />
    <br />

    
    <form id="formVac" action="./pdf/generar_pdf.php" method="POST" target='_blank'>
        <div class="page">
        
        <input type="hidden" name="origen" value="<?= htmlspecialchars($origen) ?>">
        <input type="hidden" name="tipo" id="tipo" value="<?= htmlspecialchars($tipo_Solicitud) ?>">

        <!-- ENCABEZADOS -->
        <div class="header">
            <div class="logo-row">
                <img src="../img/logo.jpg" width="500">
            </div>
            <div class="planta"> PLANTA PROSEDE</div>
            <div class="titulo"> SOLICITUD DE VACACIONES </div>
        </div>

        <!-- CAMPOS PRINCIPALES -->
        <div class="campos">
            <!-- LEFT -->
            <div>
                <div class="campo-grupo">
                    <label>NOMBRE:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
                </div>

                <div class="campo-grupo">
                    <label>PUESTO:</label>
                    <input type="text" id="puesto" name="puesto" id="puesto" placeholder="PUESTO" required>
                </div>


                <div class="campo-grupo">
                    <label>FECHA DE INGRESO:</label>
                    <input 
                        type="date" 
                        id="fecha_ingreso" 
                        name="fecha_ingreso" 
                        value="<?php 
                            $fechaNormalizada = normalizarFechaISO(col($empleado, COL_FINGRESO));
                            echo htmlspecialchars($fechaNormalizada); 
                        ?>" 
                        required
                    >
                </div>

                <!-- <div class="campo-grupo">
                    <label>FECHA DE INGRESO:</label>
                                                            
                    <input 
                        type="date" 
                        id="fecha_ingreso" 
                        name="fecha_ingreso" 
                        value="<?php 
                        // htmlspecialchars($fecha_ingreso) 
                        $fechaNormalizada = normalizarFechaISO(col($empleado, COL_FINGRESO));
                        // echo $fechaNormalizada;
                        htmlspecialchars($fechaNormalizada) 
                        ?>" 
                        required
                        >
                </div> -->

                <div class="campo-grupo">
                    <label>SOLICITUD DE DÍAS DE VACACIONES POR:</label>
                    <input type="text" id="solicitud_por" name="solicitud_por" placeholder="MOTIVO / PERIODO" value="<?= htmlspecialchars($dias) ?>" required>
                </div>
                
                <div class="campo-grupo fila-inline">
                    <label>DE: </label>
                    <input type="date" id="vacaciones_de" name="vacaciones_de" value="<?= htmlspecialchars($fechaDe) ?>" style="max-width:80px">
                    <label>DEL 20: </label>
                    <input type="text" id="vacaciones_anio_de" name="vacaciones_anio_de" value="26" style="max-width:50px" placeholder="AA">
                    <label>HASTA EL DIA: </label>
                    <input type="date" id="vacaciones_hasta" name="vacaciones_hasta" value="<?= htmlspecialchars($fecha_a) ?>" style="max-width:80px">
                </div>

                <div class="campo-grupo">
                    <label>DÍAS CORRESPONDIENTES POR ANTIGUEDAD:</label>
                    <input type="number" id="dias_antiguedad" name="dias_antiguedad" value="<?= htmlspecialchars($limite_dias) ?>" min="0" required>
                </div>

                <div class="campo-grupo">
                    <label>DÍAS DE VACACIONES SOLICITADOS:</label>
                    <input type="number" id="dias_solicitados" name="dias_solicitados" min="0" value="<?= htmlspecialchars($dias) ?>" required>
                </div>

                <div class="campo-grupo">
                    <label>PRIMA VACIONAL EQUIVALENTE:</label>
                    <input type="text" id="prima_vacacional" name="prima_vacacional">
                </div>

                <!-- Campo de reposición/festivo -->
                <div class="campo-grupo">
                    <label>DÍAS DE REPOSICIÓN O FESTIVO:</label>
                    <input type="number" id="dias_reposicion" name="dias_reposicion" min="0" value="<?= $diasReposicionFestivo ?>" required>
                </div>


                <div class="campo-grupo">
                    <label>DÍAS DE DESCANSO:</label>
                    <input type="number" id="dias_descanso" name="dias_descanso" min="0" required>
                </div>

                <div class="campo-grupo">
                    <label>TOTAL DE DÍAS:</label>
                    <input type="number" id="total_dias" name="total_dias" min="0" value="<?= htmlspecialchars($ResTot) ?>" required>
                </div>
            </div>
        
            <!-- RIGHT -->
            <div>
                <div class="campo-grupo">
                    <label>TARJETA NO.:</label>
                    <input type="text" id="tarjeta" name="tarjeta" placeholder="NO. DE TARJETA" value="<?= htmlspecialchars($ibm) ?>" required>
                </div>

                <div class="campo-grupo">
                    <label>DEPARTAMENTO:</label>
                    <input type="text" id="departamento" name="departamento" placeholder="DEPARTAMENTO" required>
                </div>

                <!-- <div class="campo-grupo">
                    <label>ANTIGUEDAD DE:</label>
                    <input type="text" id="antiguedad_de" name="antiguedad_de" placeholder="ANTIGUEDAD DE:" value="<?= calcularAntiguedad($fecha_ingreso) ?>" required>
                </div> -->

                <div class="campo-grupo">
                    <label>ANTIGUEDAD DE:</label>
                    <input 
                        type="text" 
                        id="antiguedad_de" 
                        name="antiguedad_de" 
                        placeholder="ANTIGUEDAD DE:" 
                        value="<?php 
                            $fechaNormalizada = normalizarFechaISO(col($empleado, COL_FINGRESO));
                            echo calcularAntiguedad($fechaNormalizada); 
                        ?>" 
                        required
                    >
                </div>

                <div class="campo-grupo">
                    <label>DÍA(S) HÁBIL(ES) A PARTIR DEL:</label>
                    <input type="text" id="dias_habiles_partir" name="dias_habiles_partir" placeholder="DÍAS HABILES" value="<?= htmlspecialchars($fechaDe) ?>" required>
                </div>

                <div class="campo-grupo fila-inline">
                    <label>DE: </label>
                    <input type="date" id="periodo_de" name="periodo_de" style="max-width:80px" value="<?= htmlspecialchars($fechaDe) ?>">
                    <label>DEL 20: </label>
                    <input type="text" id="periodo_anio_de" name="periodo_anio_de" style="max-width:35px" value="26" placeholder="AA">                    
                    <label>PERIODO SOLICITADO:</label>
                    <input type="text" id="periodo_solicitado" name="periodo_solicitado" placeholder="PERIODO SOLICITADO" value="<?= htmlspecialchars($anio) ?> - <?= htmlspecialchars($anioS)?>" required>
                </div>                

                <div class="campo-grupo">
                    <label>TIPO DE SOLICITUD:</label>
                    <input type="text" id="tipo_solicitud" name="tipo_solicitud" placeholder="Normal / Adelanto" value="<?= htmlspecialchars($tipo_Solicitud) ?>" required>
                </div>

                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe1" name="importe1" readonly>
                </div>
                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe2" name="importe2" readonly>
                </div>
                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe3" name="importe3" readonly>
                </div>
                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe4" name="importe4" readonly>
                </div>
                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe5" name="importe5" readonly>
                </div>
            </div>
        </div>

        <br />        
        <div class="header glosarioColores">
            <div class="titulo" style="padding:4px 8px; border:1px solid #000;"> SELECCIONA EN LOS SIGUIENTES RECUADROS LO QUE CORRESPONDA SEGUN TU CASO: </div>
            <div class="planta">         
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">                    
                    <span style="background-color: rgb(198,224,180); padding:4px 8px; border:1px solid #000;">V = VACACIONES</span>                    
                    <span style="background-color: rgb(255,255,153); padding:4px 8px; border:1px solid #000;">D = DESCANSO</span>                    
                    <span style="background-color: rgb(255,153,153); padding:4px 8px; border:1px solid #000;">F = FESTIVO</span>                    
                    <span style="background-color: rgb(180,198,231); padding:4px 8px; border:1px solid #000;">R = REPOSICION</span>                    
                </div>
            </div>
        </div>

        <?php
        
        function pintarFila($inicio, $fin, $anio, $mes, $diasSemana, $diasSeleccionados, $diasFestivos, $colores) {
            echo "<table class='calendario' style='margin-top:4px'>";
            echo "<thead><tr>";
            for($i=$inicio; $i<=$fin; $i++) {
                echo "<th>$i</th>";
            }
            echo "</tr></thead>";

            echo "<tbody>";

            // Fila de días de la semana
            echo "<tr>";
            for($i=$inicio; $i<=$fin; $i++) {
                $fecha = new DateTime("$anio-$mes-$i");
                echo "<td>".$diasSemana[$fecha->format('N')-1]."</td>";
            }
            echo "</tr>";

            // Fila editable con select y color de fondo
            echo "<tr class='diaSeleccionable'>";
            for($i=$inicio; $i<=$fin; $i++) {
                $fechaStr = "$anio-$mes-".str_pad($i,2,'0',STR_PAD_LEFT);

                // Por defecto vacío
                $valor = "";

                // Si el día está en vacaciones seleccionadas
                if (in_array($fechaStr, $diasSeleccionados)) {
                    $valor = "V";
                }

                // Si además ese día es festivo, sobreescribir a F
                if (in_array($fechaStr, $diasFestivos) && in_array($fechaStr, $diasSeleccionados)) {
                    $valor = "F";
                }

                // Determinar color de fondo según valor
                $style = "";
                if ($valor !== "" && isset($colores[$valor])) {
                    [$r, $g, $b] = $colores[$valor];
                    $style = "background-color: rgb($r,$g,$b);";
                }

                echo "<td style='$style'>";
                echo "<select name='dia_$fechaStr'>";                
                echo "<option value='' ".($valor==''?'selected':'')."></option>";
                echo "<option value='V' ".($valor=='V'?'selected':'').">V</option>";
                echo "<option value='D' ".($valor=='D'?'selected':'').">D</option>";
                echo "<option value='F' ".($valor=='F'?'selected':'').">F</option>";
                echo "<option value='R' ".($valor=='R'?'selected':'').">R</option>";
                echo "</select>";
                echo "</td>";
            }
            echo "</tr>";

            echo "</tbody></table>";
        }

        // Forma de recuperar todos los dias incluido que estos cubran meses entre pedidos
        $nombreMeses = [
            '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril',
            '05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto',
            '09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'
        ];

        $colores = [
            'V' => [198, 224, 180],
            'D' => [255, 255, 153],
            'F' => [255, 153, 153],
            'R' => [180, 198, 231],
        ];

        // Iteracion de alores recuperados (Cubren los meses necesarios)
        foreach ($mesesInvolucrados as $m) {
            $anio = $m['anio'];
            $mes = $m['mes'];

            echo "<h5>Calendario de {$nombreMeses[$mes]} $anio</h5>";
            pintarFila(1, 16, $anio, $mes, $diasSemana, $diasSeleccionados, $diasFestivos, $colores);
            pintarFila(17, 31, $anio, $mes, $diasSemana, $diasSeleccionados, $diasFestivos, $colores);
        }
        ?>


        <!-- Observaciones -->
        <div class="obs-section observacionesSeccion">
            <label>OBSERVACIONES:</label>
            <input type="text" id="observaciones" maxlength="150" name="observaciones" placeholder="AGREGA TUS OBSERVACIONES AQUI...">
            <small class="text-muted">Máximo 150 caracteres.</small>
        </div>

        <!-- REPOSICION/FESTIVO -->
        <div class="obs-section fechasReposicionfestivo">
            <label>ANOTAR LAS FECHAS DE LOS DIAS POR REPOSICION O FESTIVO:</label>
            <input type="text" id="fechas_reposicion" maxlength="150" name="fechas_reposicion" placeholder="AGREGA TUS FECHAS AQUI...">
            <small class="text-muted">Máximo 150 caracteres. <em>Usa formato -> d/m/y</em></small>
        </div>

        <!-- SALDO -->
        <div class="saldo-row">
            <div class="saldo-item">
                <label>SALDO AL PERIODO:</label>
                <input type="text" id="saldo_periodo" name="saldo_periodo" readonly>
            </div>
            <div class="saldo-item">
                <label>DÍAS HABILES:</label>
                <input type="text" id="dias_habiles_saldo" name="dias_habiles_saldo" readonly>
            </div>
        </div>

        <!-- FIRMAS -->
        <!-- <div class="firmas">
            <div class="firma-item">
                <div style="height:40px"></div>
                <div class="firma-line">FIRMA DEL TRABAJADOR</div>
            </div>

            <div class="firma-item">
                <div style="height:40px"></div>
                <div class="firma-line">AUTORIZACIÓN JEFE ÁREA</div>
            </div>

            <div class="firma-item">
                <div style="height:40px"></div>
                <div class="firma-line">Vo. Bo. RELACIONES INDS.</div>
            </div>
        </div> -->

        <!-- FOOTER -->
        <div class="footer">
            <div class="slogan">¡LOGRAR LA EXCELENCIA A TRAVES DE LA MEJORA CONTINUA!</div>
            <div class="ref">
                Revision: 01
                <br />
                Ref.: 8-702A-07
                <br />
                Formato: KCM-173872
            </div>
        </div>

        <div class="botones">
            <button type="button" class="btn btn-primary botonRegresar" onclick="location.href='<?= htmlspecialchars($origen) ?>'">
                <i class="fa-solid fa-arrow-rotate-left"></i> REGRESAR A LA SOLICITUD
            </button>
            <button type="submit" name="accion" value="pdf" class="btn btn-warning botonGuardar">
                <i class="fa-solid fa-floppy-disk"></i> GUARDAR Y GENERAR PDF
            </button>
        </div>


        <?php if(isset($_GET['ok'])): ?>
            <div class="mensaje ok"> Solicitud guardada correctamente <?= htmlspecialchars($_GET['ok']) ?>
            </div>
        <?php elseif(isset($_GET['err'])): ?>
            <div class="mensaje err"> Error al procesar la solicitud <?= htmlspecialchars($_GET['err']) ?>
            </div>
        <?php endif; ?>
    </form>
    <br />

<script src="./js/finalizarSolicitud.js"></script>

<?php require_once("../index/footer.php") ?>