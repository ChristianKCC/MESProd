<?php
require_once(__DIR__ . "/../Session/seguridad.php");
require_once(__DIR__ . "/../index/header.php");

$carpeta = __DIR__ . '/solicitudes';
if(!is_dir($carpeta)){
    mkdir($carpeta, 0755, true);
}

if (!isset($_GET['folio'])) {
    die("Folio no válido");
}
$folio = base64_decode($_GET['folio']);

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$query = "SELECT enc.Vc_id, enc.Vc_tipo, enc.Vc_ibm, sub.Vcs_nombre, sub.Vcs_puesto, sub.Vcs_fingreso,
            sub.Vcs_diasVacSol, sub.Vcs_totalDias, sub.Vcs_de, sub.Vcs_hasta,
            sub.Vcs_depto, sub.Vcs_antiguedad, sub.Vcs_impOne, sub.Vcs_impTwo,
            sub.Vcs_impThree, sub.Vcs_impFour, sub.Vcs_impFive, sub.Vcs_Observacion,
            sub.Vcs_solVacBy, sub.Vcs_diasByAntiguedad, sub.Vcs_noTarjeta,
            sub.Vcs_priVacEq, sub.Vcs_diasRF, sub.Vcs_diasD, sub.Vcs_diasHabAp, sub.Vcs_fechasRF, sub.Vcs_periodo,
            sub.Vcs_saldoPeriodo, sub.Vcs_diasHabiles
        FROM tblMXPRVacacionesEnc enc
        INNER JOIN tblMXPRVacacionesSubEnc sub ON sub.Vcs_vc_id = enc.Vc_id
        WHERE enc.Vc_id = ?";
// $res = sqlsrv_query($conn, $query, [$folio]);
// $row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
$res = sqlsrv_query($conn, $query, [$folio]);
if ($res === false) {
    die(print_r(sqlsrv_errors(), true));
}
$row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);

// Recuperar días seleccionados del calendario
// $queryCal = "SELECT Cav_dia, Cav_tipoDia, Cav_seleccionado 
//              FROM tblMXPRCalendarioVacaciones 
//              WHERE Cav_folio = ?";
// $resCal = sqlsrv_query($conn, $queryCal, [$folio]);

// $diasSeleccionados = [];
// while ($rowCal = sqlsrv_fetch_array($resCal, SQLSRV_FETCH_ASSOC)) {
//     if ($rowCal['Cav_seleccionado'] == 1) {
//         // Guardamos por número de día (1–31)
//         $diaNum = (int)$rowCal['Cav_dia'];
//         $diasSeleccionados[$diaNum] = $rowCal['Cav_tipoDia']; // V, D, F, R
//     }
// }

$queryCal = "SELECT Cav_fecha, Cav_tipoDia, Cav_seleccionado 
             FROM tblMXPRCalendarioVacaciones 
             WHERE Cav_folio = ?";
$resCal = sqlsrv_query($conn, $queryCal, [$folio]);

$diasSeleccionados = [];
while ($rowCal = sqlsrv_fetch_array($resCal, SQLSRV_FETCH_ASSOC)) {
    if ($rowCal['Cav_seleccionado'] == 1) {
        $fechaStr = $rowCal['Cav_fecha']->format("Y-m-d");
        $diasSeleccionados[$fechaStr] = $rowCal['Cav_tipoDia']; // V, D, F, R
    }
}

$nombre = $row['Vcs_nombre'];
$puesto = $row['Vcs_puesto'];
$fecha_ingreso = $row['Vcs_fingreso']->format("Y-m-d");
$solicitud_por = $row['Vcs_solVacBy'] . " días";
$vacaciones_por = $row['Vcs_de']->format("Y-m-d");
$vacaciones_hasta = $row['Vcs_hasta']->format("Y-m-d");
$dias_antiguedad = $row['Vcs_diasByAntiguedad'];
$dias_solicitados = $row['Vcs_diasVacSol'];
$total_dias = $row['Vcs_totalDias'];
$tarjeta = $row['Vcs_noTarjeta'];
$departamento = $row['Vcs_depto'];
$antiguedad_de = $row['Vcs_antiguedad'];
$vacaciones_anio_de = 26;
$prima_vacacional = $row['Vcs_priVacEq'];
$dias_reposicion = $row['Vcs_diasRF'];
$dias_descanso = $row['Vcs_diasD'];
$dias_habiles_partir = $row['Vcs_diasHabAp']->format("Y-m-d");
$periodo_de = $row['Vcs_periodo'];
$vacaciones_anio_de = 26;
$imp1 = $row['Vcs_impOne'] ?? 0;
$imp2 = $row['Vcs_impTwo'] ?? 0;
$imp3 = $row['Vcs_impThree'] ?? 0;
$imp4 = $row['Vcs_impFour'] ?? 0;
$imp5 = $row['Vcs_impFive'] ?? 0;
$observaciones = $row['Vcs_Observacion'];
$fechasRF = $row["Vcs_fechasRF"];
$saldoPeriodo = $row["Vcs_saldoPeriodo"];
$diasHabiles = $row["Vcs_diasHabiles"];
$tipo_solicitud = $row["Vc_tipo"] ?? "No hay tipo registrado";

$fechaInicioMes = new DateTime($vacaciones_por);
$anio = $fechaInicioMes->format('Y');
$mes  = $fechaInicioMes->format('m');

// Detectar meses involucrados según el rango de vacaciones
$fechaInicio = new DateTime($vacaciones_por);
$fechaFin    = new DateTime($vacaciones_hasta);

$mesesInvolucrados = [];
$cursor = clone $fechaInicio;
while ($cursor <= $fechaFin) {
    $mesesInvolucrados[] = [
        'anio' => $cursor->format('Y'),
        'mes'  => $cursor->format('m')
    ];
    $cursor->modify('first day of next month');
}


// Dias para calculo 
$diasReposicionFestivo = 0;
$diasDescanso = 0;

// Formateo de fechas
if (!empty($fecha_ingreso_raw)) {
    try {
        $fechaObj = new DateTime($fecha_ingreso_raw);
        $fecha_ingreso = $fechaObj->format('Y-m-d');
    } catch (Exception $e) {
        $fecha_ingreso = '';
    }
}

// Funcion de calcular la antiguedd
function calcularAntiguedad(string $fechaStr): string {
    $fechaStr = trim($fechaStr);
    if (!$fechaStr || $fechaStr === '-') return '-';

    // Crear objeto DateTime con formato Y-m-d
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fechaStr);
    if (!$fechaObj) return $fechaStr;

    $hoy = new DateTime();
    $diff = $fechaObj->diff($hoy);

    $anios = $diff->y;
    $meses = $diff->m;

    if ($anios === 0) return "$meses mes(es)";
    if ($meses === 0) return "$anios año(s)";
    return "$anios año(s) y $meses mes(es)";
}

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

// Calcular antes de imprimir los inputs
foreach ($diasSeleccionados as $fechaStr) {
    // Si es festivo
    if (isset($diasSeleccionados[$fechaStr])) {
        $valor = $diasSeleccionados[$fechaStr]; // V, D, F, R
    }
     else if ($fechaStr === 'R'){
        $diasReposicionFestivo++;
    }
    // Si el usuario marcó descanso en la selección (si lo manejas así)
    if ($fechaStr === 'D') {
        $diasDescanso++;
    }
}
?>


<link rel="stylesheet" href="css/finalizarSolicitud.css">

<div class="container p-4">
    <h5 class="tittlecont">Corrección de datos para solicitud de vacaciones</h5>
    
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
                Corrige la información necesaria, una vez lista, da click en "GUARDAR Y ACTUALIZAR DATOS".
            </small>
        </div>
    </div>
    <br>
    
    <br />
    <br />
    <form id="formEditVac" method="POST" target='_blank'>
        <input type="hidden" name="folio" id="folio" value="<?= htmlspecialchars($folio) ?>">
        <div class="page">
        
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
                    <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
                </div>

                <div class="campo-grupo">
                    <label>PUESTO:</label>
                    <input type="text" name="puesto" value="<?= htmlspecialchars($puesto) ?>">
                </div>

                <div class="campo-grupo">
                    <label>FECHA DE INGRESO:</label>
                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" value="<?= htmlspecialchars($fecha_ingreso) ?>"  >
                </div>

                <div class="campo-grupo">
                    <label>SOLICITUD DE DÍAS DE VACACIONES POR:</label>
                    <input type="text" id="solicitud_por" name="solicitud_por" placeholder="MOTIVO / PERIODO" value="<?= htmlspecialchars($solicitud_por) ?>" >
                </div>
                
                <div class="campo-grupo fila-inline">
                    <label>DE: </label>
                    <input type="date" id="vacaciones_de" name="vacaciones_de" value="<?= htmlspecialchars($vacaciones_por) ?>" style="max-width:80px">
                    <label>DEL 20: </label>
                    <input type="text" id="vacaciones_anio_de" name="vacaciones_anio_de" value="26" style="max-width:50px" placeholder="AA">
                    <label>HASTA EL DIA: </label>
                    <input type="date" id="vacaciones_hasta" name="vacaciones_hasta" value="<?= htmlspecialchars($vacaciones_hasta) ?>" style="max-width:80px">
                </div>

                <div class="campo-grupo">
                    <label>DÍAS CORRESPONDIENTES POR ANTIGUEDAD:</label>
                    <input type="number" id="dias_antiguedad" name="dias_antiguedad" value="<?= htmlspecialchars($dias_antiguedad ?? 0) ?>" min="0"  >   
                </div>

                <div class="campo-grupo">
                    <label>DÍAS DE VACACIONES SOLICITADOS:</label>
                    <input type="number" id="dias_solicitados" name="dias_solicitados" min="0" value="<?= htmlspecialchars($dias_solicitados ?? 0) ?>">
                </div>

                <div class="campo-grupo">
                    <label>PRIMA VACIONAL EQUIVALENTE:</label>
                    <input type="text" id="prima_vacacional" name="prima_vacacional" value="<?= htmlspecialchars($prima_vacacional)?>">
                </div>

                <!-- Campo de reposición/festivo -->
                <div class="campo-grupo">
                    <label>DÍAS DE REPOSICIÓN O FESTIVO:</label>
                    <input type="number" id="dias_reposicion" name="dias_reposicion" min="0" value="<?= htmlspecialchars($dias_reposicion ?? 0) ?>">
                </div>


                <div class="campo-grupo">
                    <label>DÍAS DE DESCANSO:</label>
                    <input type="number" id="dias_descanso" name="dias_descanso" value="<?= htmlspecialchars($dias_descanso ?? 0) ?>">
                </div>

                <div class="campo-grupo">
                    <label>TOTAL DE DÍAS:</label>
                    <input type="number" id="total_dias" name="total_dias" min="0" value="<?= htmlspecialchars($total_dias ?? 0) ?>">
                </div>
            </div>
        
            <!-- RIGHT -->
            <div>
                <div class="campo-grupo">
                    <label>TARJETA NO.:</label>
                    <input type="text" id="tarjeta" name="tarjeta" placeholder="NO. DE TARJETA" value="<?= htmlspecialchars($tarjeta) ?>">
                </div>

                <div class="campo-grupo">
                    <label>DEPARTAMENTO:</label>
                    <input type="text" id="departamento" name="departamento" placeholder="DEPARTAMENTO" value="<?= htmlspecialchars($departamento)?>">
                </div>

                <div class="campo-grupo">
                    <label>ANTIGUEDAD DE:</label>
                    <input type="text" id="antiguedad_de" name="antiguedad_de" placeholder="ANTIGUEDAD DE:" value="<?= htmlspecialchars($antiguedad_de) ?>"  >
                </div>

                <div class="campo-grupo">
                    <label>DÍA(S) HÁBIL(ES) A PARTIR DEL:</label>
                    <input type="text" id="dias_habiles_partir" name="dias_habiles_partir" placeholder="DÍAS HABILES" value="<?= htmlspecialchars($dias_habiles_partir) ?>"  >
                </div>

                <div class="campo-grupo fila-inline">
                    <label>DE: </label>
                    <input type="date" id="periodo_de" name="periodo_de" style="max-width:80px" value="<?= htmlspecialchars($vacaciones_por) ?>">
                    <label>DEL 20: </label>
                    <input type="text" id="periodo_anio_de" name="periodo_anio_de" style="max-width:35px" value="26" placeholder="AA">
                    <label>PERIODO SOLICITADO:</label>
                    <input type="text" id="periodo_solicitado" name="periodo_solicitado" placeholder="PERIODO SOLICITADO" value="<?= htmlspecialchars($periodo_de) ?>"  >
                </div>

                <div class="campo-grupo">
                    <label>TIPO DE SOLICITUD:</label>
                    <input type="text" id="tipo_sol" name="tipo_sol" placeholder="TIPO" value="<?= htmlspecialchars($tipo_solicitud) ?>"  >
                </div>

                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe1" name="importe1" value="<?= htmlspecialchars($imp1) ?>">
                </div>
                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe2" name="importe2" value="<?= htmlspecialchars($imp2) ?>">
                </div>
                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe3" name="importe3" value="<?= htmlspecialchars($imp3) ?>">
                </div>
                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe4" name="importe4" value="<?= htmlspecialchars($imp4) ?>">
                </div>
                <div class="campo-grupo">
                    <label>IMPORTE $: </label>
                    <input type="text" id="importe5" name="importe5" value="<?= htmlspecialchars($imp5) ?>">
                </div>
            </div>
        </div>

        <br />
        <div class="header">            
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
            echo "<tr>";
            for($i=$inicio; $i<=$fin; $i++) {
                $fechaStr = "$anio-$mes-".str_pad($i,2,'0',STR_PAD_LEFT);
                $valor = $diasSeleccionados[$fechaStr] ?? '';

                // Si además es festivo, sobreescribir a F
                if (in_array($fechaStr, $diasFestivos) && $valor == "V") {
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

        $colores = [
            'V' => [198, 224, 180],
            'D' => [255, 255, 153],
            'F' => [255, 153, 153],
            'R' => [180, 198, 231],
        ];        

        $nombreMeses = [
            '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril',
            '05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto',
            '09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'
        ];

        foreach ($mesesInvolucrados as $m) {
            $anio = $m['anio'];
            $mes = $m['mes'];

            echo "<h5>Calendario de {$nombreMeses[$mes]} $anio</h5>";
            pintarFila(1, 16, $anio, $mes, $diasSemana, $diasSeleccionados, $diasFestivos, $colores);
            pintarFila(17, 31, $anio, $mes, $diasSemana, $diasSeleccionados, $diasFestivos, $colores);
        }
        ?>

        <!-- Observaciones -->
        <div class="obs-section">
            <label for="observaciones">OBSERVACIONES:</label>
            <input type="text" 
                id="observaciones" 
                name="observaciones" 
                maxlength="50"
                placeholder="AGREGA TUS OBSERVACIONES AQUI..." 
                value="<?= htmlspecialchars($observaciones) ?>">
            <small class="text-muted">Máximo 50 caracteres.</small>
        </div>

        <!-- REPOSICION/FESTIVO -->
        <div class="obs-section">
            <label>ANOTAR LAS FECHAS DE LOS DIAS POR REPOSICION O FESTIVO:</label>
            <input type="text" 
                id="fechas_reposicion" 
                name="fechas_reposicion" 
                maxlength="50"
                placeholder="AGREGA TUS FECHAS AQUI..." 
                value="<?= htmlspecialchars($fechasRF) ?>">
            <small class="text-muted">Máximo 50 caracteres. <em>Usa formato -> d/m/y </em></small>            
        </div>

        <!-- SALDO -->
        <div class="saldo-row">
            <div class="saldo-item">
                <label>SALDO AL PERIODO:</label>
                <input type="text" id="saldo_periodo" name="saldo_periodo" value="<?= htmlspecialchars($saldoPeriodo ?? 0) ?>">
            </div>
            <div class="saldo-item">
                <label>DÍAS HABILES:</label>
                <input type="text" id="dias_habiles_saldo" name="dias_habiles_saldo" value="<?= htmlspecialchars($diasHabiles ?? 0) ?>">
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
            <button type="button" class="btn btn-primary" onclick="window.close()">
                <i class="fa-solid fa-arrow-rotate-left"></i> REGRESAR
            </button>
            <button type="submit" name="accion" value="actualizar" class="btn btn-warning">
                <i class="fa-solid fa-floppy-disk"></i> GUARDAR Y ACTUALIZAR DATOS
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

<script src="./js/editarInfo.js"></script>
<?php require_once("../index/footer.php") ?>