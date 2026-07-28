<?php
/* ============================================================================
   ENDPOINT: Registrar el RARR completo (3 pasos en transacción).
   Cada escenario lleva sus propios P2/P3/P3b y su foto.
   Alta: inserta todo. Edición: upsert de escenarios (IdEscenario estable),
   reinserta eval/control/plan/genéricos, y conserva imágenes salvo reemplazo.
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$payload = json_decode($_POST['payload'] ?? '', true);
if (!is_array($payload)) {
    responderError("El payload del RARR no es válido");
}

$p1 = $payload['paso1'] ?? null;
$p2 = $payload['paso2'] ?? null;
$p3 = $payload['paso3'] ?? null;
$idRARREdicion = enteroONull($payload['idRARR'] ?? null);

if (!$p1 || !$p2 || !$p3) {
    responderError("Faltan pasos por completar");
}
if (empty($p1['escenarios'])) {
    responderError("El Paso 1 no tiene escenarios de riesgo");
}

$idMaquina = enteroONull($p1['idMaquina'] ?? null);
$idEquipo = limpiar($p1['idEquipo'] ?? '');
if ($idMaquina === null || $idEquipo === '') {
    responderError("Máquina o ID de equipo no válidos");
}

$noEmp = obtenerNoEmp();

$infoM = obtenerInfoMaquinaDepto($idMaquina);
if (count($infoM) === 0) {
    responderError("La máquina no existe en el catálogo", 404);
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

/* ---------- Utilerías de transacción ---------- */
function abortar($conn, $mensaje, $det = null)
{
    sqlsrv_rollback($conn);
    sqlsrv_close($conn);
    responderError($mensaje, 500, $det);
}

function insertarTx($conn, $sql, $params)
{
    $sql .= "; SELECT SCOPE_IDENTITY() AS Id;";
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        abortar($conn, "Error al guardar el RARR", sqlsrv_errors());
    }
    sqlsrv_next_result($stmt);
    sqlsrv_fetch($stmt);
    $id = sqlsrv_get_field($stmt, 0);
    sqlsrv_free_stmt($stmt);
    return (int) $id;
}

function ejecutarTx($conn, $sql, $params)
{
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        abortar($conn, "Error al guardar el RARR", sqlsrv_errors());
    }
    sqlsrv_free_stmt($stmt);
}

/* ---------- Catálogos en memoria ---------- */
function cargarValores($conn, $tabla, $llave)
{
    $filas = ejecutarQuery($conn, "SELECT $llave AS id, Valor FROM TLX002MXDB.dbo.$tabla WHERE Activo = 1");
    $mapa = [];
    foreach ($filas as $f) {
        $mapa[(int) $f['id']] = (float) $f['Valor'];
    }
    return $mapa;
}

$valSeveridad = cargarValores($conn, 'Seg_CatSeveridad', 'IdSeveridad');
$valProb = cargarValores($conn, 'Seg_CatProbabilidad', 'IdProbabilidad');
$valFrec = cargarValores($conn, 'Seg_CatFrecuencia', 'IdFrecuencia');
$valPersonas = cargarValores($conn, 'Seg_CatPersonasExpuestas', 'IdPersonas');
$valCriterio = cargarValores($conn, 'Seg_CatCriterioGuarda', 'IdCriterio');
$valMedida = cargarValores($conn, 'Seg_CatMedidaMitigacion', 'IdMedida');
$txtMedida = [];
foreach (ejecutarQuery($conn, "SELECT IdMedida AS id, Descripcion FROM TLX002MXDB.dbo.Seg_CatMedidaMitigacion") as $f) {
    $txtMedida[(int) $f['id']] = $f['Descripcion'];
}

$descPersonas = [];
foreach (ejecutarQuery($conn, "SELECT IdPersonas AS id, Descripcion FROM TLX002MXDB.dbo.Seg_CatPersonasExpuestas") as $f) {
    $descPersonas[(int) $f['id']] = $f['Descripcion'];
}

function calcular($s, $cuarto, $f, $n)
{
    if ($s === null || $cuarto === null || $f === null || $n === null) {
        return null;
    }
    return round($s * $cuarto * $f * $n, 2);
}

if (sqlsrv_begin_transaction($conn) === false) {
    sqlsrv_close($conn);
    responderError("No se pudo iniciar la transacción", 500, sqlsrv_errors());
}

/* ---------- 0. Edición: escenarios propios previos + limpiar hijos reinsertables ---------- */
$idsPrevios = [];
if ($idRARREdicion !== null) {
    foreach (ejecutarQuery(
        $conn,
        "SELECT IdEscenario FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo
         WHERE IdRARR = ? AND EsGenerico = 0 AND Activo = 1",
        [$idRARREdicion]
    ) as $r) {
        $idsPrevios[(int) $r['IdEscenario']] = true;
    }
    ejecutarTx($conn, "DELETE FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo WHERE IdRARR = ? AND EsGenerico = 1", [$idRARREdicion]);
    ejecutarTx($conn, "DELETE FROM TLX002MXDB.dbo.Seg_EvaluacionRARR WHERE IdRARR = ?", [$idRARREdicion]);
    ejecutarTx($conn, "DELETE FROM TLX002MXDB.dbo.Seg_AccionMejora WHERE IdRARR = ?", [$idRARREdicion]);
    ejecutarTx($conn, "DELETE FROM TLX002MXDB.dbo.Seg_SeguimientoControl WHERE IdRARR = ?", [$idRARREdicion]);

}


/* ---------- 1. RARR maestro ---------- */
$seccion = limpiar($p1['seccion'] ?? '');
$idRARR = obtenerOCrearRARR(
    $conn,
    (int) $infoM[0]['IdDepartamento'],
    $infoM[0]['Departamento'],
    (int) $infoM[0]['IdMaquina'],
    $infoM[0]['Maquina'],
    $seccion !== '' ? $seccion : null,
    $noEmp
);

ejecutarTx($conn, "UPDATE TLX002MXDB.dbo.Seg_RARR SET IdEquipo = ? WHERE IdRARR = ?", [$idEquipo, $idRARR]);

/* ---------- 2. Escenarios upsert + su evaluación, control y plan ---------- */
$marcadorPuro = 0;
$marcadorGuardas = 0;
$marcadorIngenieria = 0;
$idsEscenario = [];  // índice del payload -> IdEscenario (para las imágenes)
$vistos = [];
$totalEscenarios = 0;
$totalEvaluaciones = 0;
$totalSoluciones = 0;
$totalAcciones = 0;

foreach ($p1['escenarios'] as $i => $e) {
    $idCategoria = enteroONull($e['idCategoria'] ?? null);
    $idSeveridad = enteroONull($e['idSeveridad'] ?? null);
    $idProbabilidad = enteroONull($e['idProbabilidad'] ?? null);
    $idFrecuencia = enteroONull($e['idFrecuencia'] ?? null);
    $idPersonas = enteroONull($e['idPersonas'] ?? null);
    $escenario = limpiar($e['escenario'] ?? '');

    if (
        $idCategoria === null || $idSeveridad === null || $idProbabilidad === null
        || $idFrecuencia === null || $idPersonas === null || $escenario === ''
    ) {
        abortar($conn, "Un escenario de riesgo tiene campos incompletos");
    }

    $sev = $valSeveridad[$idSeveridad] ?? null;
    $frec = $valFrec[$idFrecuencia] ?? null;
    $pers = $valPersonas[$idPersonas] ?? null;

    $c1 = calcular($sev, $valProb[$idProbabilidad] ?? null, $frec, $pers);
    if ($c1 === null) {
        abortar($conn, "Alguno de los catálogos del escenario no existe");
    }

    /* P2 y P3 propios del escenario */
    $ep2 = $e['p2'] ?? [];
    $ep3 = $e['p3'] ?? [];
    $ep3b = $e['p3b'] ?? [];
    $idCriterio = enteroONull($ep2['idCriterioGuarda'] ?? null);
    $idMedida = enteroONull($ep3['idMedida'] ?? null);
    $c2 = calcular($sev, $idCriterio !== null ? ($valCriterio[$idCriterio] ?? null) : null, $frec, $pers);
    $c3 = calcular($sev, $idMedida !== null ? ($valMedida[$idMedida] ?? null) : null, $frec, $pers);

    $paramsEsc = [
        $idCategoria,
        $escenario,
        $escenario,
        enteroONull($e['idFuente'] ?? null),
        enteroONull($e['idMecanismo'] ?? null),
        enteroONull($e['idConsecuencia'] ?? null),
        $idSeveridad,
        $idProbabilidad,
        $idFrecuencia,
        $idPersonas,
        $descPersonas[$idPersonas] ?? null,
        $c1,
        clasificarNivelRiesgo($c1),
        $idCriterio,
        $c2,
        $c2 !== null ? clasificarNivelRiesgo($c2) : null,
        $idMedida,
        $c3,
        $c3 !== null ? clasificarNivelRiesgo($c3) : null,
    ];

    $idEscExistente = enteroONull($e['idEscenario'] ?? null);
    if ($idEscExistente !== null && isset($idsPrevios[$idEscExistente])) {
        ejecutarTx(
            $conn,
            "UPDATE TLX002MXDB.dbo.Seg_EscenarioRiesgo SET
                IdCategoriaPeligro=?, DescripcionPeligro=?, EscenarioRiesgo=?,
                IdFuente=?, IdMecanismo=?, IdConsecuencia=?,
                IdSeveridad=?, IdProbabilidad=?, IdFrecuencia=?, IdPersonas=?, PersonalExpuesto=?,
                Calificacion=?, NivelRiesgo=?,
                IdCriterioGuarda=?, CalificacionP2=?, NivelRiesgoP2=?,
                IdMedidaMitigacion=?, CalificacionP3=?, NivelRiesgoP3=?
             WHERE IdEscenario=?",
            array_merge($paramsEsc, [$idEscExistente])
        );
        $idEsc = $idEscExistente;
    } else {
        $idEsc = insertarTx(
            $conn,
            "INSERT INTO TLX002MXDB.dbo.Seg_EscenarioRiesgo
                (IdRARR, IdCategoriaPeligro, DescripcionPeligro, EscenarioRiesgo,
                 IdFuente, IdMecanismo, IdConsecuencia,
                 IdSeveridad, IdProbabilidad, IdFrecuencia, IdPersonas, PersonalExpuesto,
                 Calificacion, NivelRiesgo,
                 IdCriterioGuarda, CalificacionP2, NivelRiesgoP2,
                 IdMedidaMitigacion, CalificacionP3, NivelRiesgoP3,
                 EsGenerico, no_emp)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,?)",
            array_merge([$idRARR], $paramsEsc, [$noEmp])
        );
    }

    $vistos[$idEsc] = true;
    $idsEscenario[$i] = $idEsc;
    $marcadorPuro += $c1;
    $totalEscenarios++;

    /* Evaluación (Paso 2) — una por escenario */
    if ($c2 === null) {
        abortar($conn, "El Escenario " . ($i + 1) . " no tiene criterio de guarda válido");
    }
    $avance = enteroONull($ep2['avance'] ?? 0) ?? 0;
    if ($avance < 0 || $avance > 100) {
        abortar($conn, "El progreso del Escenario " . ($i + 1) . " debe estar entre 0 y 100");
    }
    $fechaEv = limpiar($ep2['fecha'] ?? '');
    if ($fechaEv !== '' && DateTime::createFromFormat('Y-m-d', $fechaEv) === false) {
        abortar($conn, "Una fecha del Paso 2 no es válida");
    }
    insertarTx(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_EvaluacionRARR
            (IdRARR, IdEscenario, Componente, FechaSistema, DescripcionHallazgo, CriterioGuarda,
             IdCriterioGuarda, Calificacion, NivelRiesgo, IdSeguridadFuncional, NivelRiesgoActual,
             PorcentajeAvance, AccionesPropuestas, MedidasMitigacion,
             IbmResponsable, NombreResponsable, FechaCompromiso, no_emp)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [
            $idRARR,
            $idEsc,
            $seccion !== '' ? $seccion : null,
            fechaSistemaMX(),
            limpiar($ep2['descGuarda'] ?? ''),
            null,
            $idCriterio,
            $c2,
            clasificarNivelRiesgo($c2),
            enteroONull($ep2['idSeguridadFuncional'] ?? null),
            null,
            $avance,
            limpiar($ep2['accionesContencion'] ?? ''),
            limpiar($ep2['mitigacion'] ?? ''),
            limpiar($ep2['ibm'] ?? ''),
            limpiar($ep2['responsable'] ?? ''),
            $fechaEv !== '' ? $fechaEv : null,
            $noEmp
        ]
    );
    $marcadorGuardas += $c2;
    $totalEvaluaciones++;

    /* Control de ingeniería (Paso 3, div 1) — uno por escenario */
    if ($c3 === null) {
        abortar($conn, "El Escenario " . ($i + 1) . " no tiene medida de mitigación válida");
    }
    $fechaSol = limpiar($ep3['fecha'] ?? '');
    if ($fechaSol !== '' && DateTime::createFromFormat('Y-m-d', $fechaSol) === false) {
        abortar($conn, "Una fecha del control (Paso 3) no es válida");
    }
    $inv = limpiar($ep3['inversion'] ?? '');
    if ($inv !== '' && !is_numeric($inv)) {
        abortar($conn, "La inversión estimada debe ser numérica");
    }

    insertarTx(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_AccionMejora
        (IdRARR, IdEscenario, Descripcion, IdMedidaMitigacion, Calificacion, NivelRiesgo,
         FechaImplementacion, InversionEstimada, IdEstatus, no_emp)
     VALUES (?,?,?,?,?,?,?,?,?,?)",
        [
            $idRARR,
            $idEsc,
            $txtMedida[$idMedida] ?? 'Control de ingeniería',
            $idMedida,
            $c3,
            clasificarNivelRiesgo($c3),
            $fechaSol !== '' ? $fechaSol : null,
            $inv !== '' ? round((float) $inv, 2) : null,
            enteroONull($ep3['idEstatus'] ?? null),
            $noEmp
        ]
    );
    $marcadorIngenieria += $c3;
    $totalSoluciones++;

    /* Plan de Acción (Paso 3, div 2) — uno por escenario */
    $descPlan = limpiar($ep3b['descripcion'] ?? '');
    $fechaPlan = limpiar($ep3b['fecha'] ?? '');
    if ($descPlan === '') {
        abortar($conn, "El plan de acción del Escenario " . ($i + 1) . " está incompleto");
    }
    if ($fechaPlan !== '' && DateTime::createFromFormat('Y-m-d', $fechaPlan) === false) {
        abortar($conn, "Una fecha objetivo del Plan de Acción no es válida");
    }
    insertarTx(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_SeguimientoControl
            (IdRARR, IdEscenario, Descripcion, IbmResponsable, Responsable,
             FechaImplementacion, IdEstatus, no_emp)
         VALUES (?,?,?,?,?,?,?,?)",
        [
            $idRARR,
            $idEsc,
            $descPlan,
            limpiar($ep3b['ibm'] ?? ''),
            limpiar($ep3b['responsable'] ?? ''),
            $fechaPlan !== '' ? $fechaPlan : null,
            enteroONull($ep3b['idEstatus'] ?? null),
            $noEmp
        ]
    );
    $totalAcciones++;
}

/* Escenarios que ya no vinieron en la edición: se apagan junto con sus imágenes */
foreach (array_keys($idsPrevios) as $idViejo) {
    if (!isset($vistos[$idViejo])) {
        ejecutarTx($conn, "UPDATE TLX002MXDB.dbo.Seg_EscenarioRiesgo SET Activo = 0 WHERE IdEscenario = ?", [$idViejo]);
        ejecutarTx($conn, "UPDATE TLX002MXDB.dbo.Seg_ImagenRARR SET Activo = 0 WHERE IdEscenario = ?", [$idViejo]);
    }
}

/* ---------- 3. Peligros genéricos (una fila con sus 3 pasos) ---------- */
$genP2 = [];
foreach (($p2['genericos'] ?? []) as $g) {
    $genP2[(int) $g['idGenerico']] = enteroONull($g['idCriterioGuarda'] ?? null);
}
$genP3 = [];
foreach (($p3['genericos'] ?? []) as $g) {
    $genP3[(int) $g['idGenerico']] = enteroONull($g['idMedida'] ?? null);
}

$totalGenericos = 0;
foreach (($p1['genericos'] ?? []) as $g) {
    $idGenerico = enteroONull($g['idGenerico'] ?? null);
    $idCategoria = enteroONull($g['idCategoria'] ?? null);
    $idSeveridad = enteroONull($g['idSeveridad'] ?? null);
    $idProbabilidad = enteroONull($g['idProbabilidad'] ?? null);
    $idFrecuencia = enteroONull($g['idFrecuencia'] ?? null);
    $idPersonas = enteroONull($g['idPersonas'] ?? null);
    $escenario = limpiar($g['escenario'] ?? '');

    if (
        $idGenerico === null || $idCategoria === null || $idSeveridad === null
        || $idProbabilidad === null || $idFrecuencia === null || $idPersonas === null
    ) {
        abortar($conn, "Un peligro genérico tiene campos incompletos");
    }

    $sev = $valSeveridad[$idSeveridad] ?? null;
    $frec = $valFrec[$idFrecuencia] ?? null;
    $pers = $valPersonas[$idPersonas] ?? null;
    $idCriterio = $genP2[$idGenerico] ?? null;
    $idMedida = $genP3[$idGenerico] ?? null;

    $c1 = calcular($sev, $valProb[$idProbabilidad] ?? null, $frec, $pers);
    $c2 = calcular($sev, $idCriterio !== null ? ($valCriterio[$idCriterio] ?? null) : null, $frec, $pers);
    $c3 = calcular($sev, $idMedida !== null ? ($valMedida[$idMedida] ?? null) : null, $frec, $pers);

    if ($c1 === null || $c2 === null || $c3 === null) {
        abortar($conn, "Un peligro genérico no tiene completos sus 3 pasos");
    }

    insertarTx(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_EscenarioRiesgo
            (IdRARR, IdCategoriaPeligro, DescripcionPeligro, EscenarioRiesgo,
             IdSeveridad, IdProbabilidad, IdFrecuencia, IdPersonas, PersonalExpuesto,
             Calificacion, NivelRiesgo,
             IdCriterioGuarda, CalificacionP2, NivelRiesgoP2,
             IdMedidaMitigacion, CalificacionP3, NivelRiesgoP3,
             EsGenerico, IdGenerico, no_emp)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)",
        [
            $idRARR,
            $idCategoria,
            $escenario,
            $escenario,
            $idSeveridad,
            $idProbabilidad,
            $idFrecuencia,
            $idPersonas,
            $descPersonas[$idPersonas] ?? null,
            $c1,
            clasificarNivelRiesgo($c1),
            $idCriterio,
            $c2,
            clasificarNivelRiesgo($c2),
            $idMedida,
            $c3,
            clasificarNivelRiesgo($c3),
            $idGenerico,
            $noEmp
        ]
    );

    $marcadorPuro += $c1;
    $marcadorGuardas += $c2;
    $marcadorIngenieria += $c3;
    $totalGenericos++;
}

/* ---------- 4. Imágenes por escenario (Paso 1 y Paso 3) ---------- */
function guardarImagenEsc($conn, $campo, $paso, $idEquipo, $idEscenario, $noEmp)
{
    /* Sin archivo nuevo = conservar la existente */
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $tmp = $_FILES[$campo]['tmp_name'];
    $info = @getimagesize($tmp);
    if ($info === false) {
        abortar($conn, "Una imagen del Paso $paso no es válida");
    }
    if ($_FILES[$campo]['size'] > 5 * 1024 * 1024) {
        abortar($conn, "Una imagen del Paso $paso supera los 5 MB");
    }
    /* Reemplazo: apaga la anterior de ese escenario + paso */
    ejecutarTx(
        $conn,
        "UPDATE TLX002MXDB.dbo.Seg_ImagenRARR SET Activo = 0 WHERE IdEscenario = ? AND Paso = ? AND Activo = 1",
        [$idEscenario, $paso]
    );
    $binario = file_get_contents($tmp);
    $stmt = sqlsrv_query(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_ImagenRARR
            (IdEquipo, Paso, IdEscenario, NombreArchivo, TipoMime, Imagen, no_emp)
         VALUES (?,?,?,?,?,?,?)",
        [
            $idEquipo,
            $paso,
            $idEscenario,
            basename($_FILES[$campo]['name']),
            $info['mime'],
            [$binario, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max')],
            $noEmp
        ]
    );
    if ($stmt === false) {
        abortar($conn, "Error al guardar una imagen del Paso $paso", sqlsrv_errors());
    }
    sqlsrv_free_stmt($stmt);
    return true;
}

foreach ($idsEscenario as $i => $idEsc) {
    guardarImagenEsc($conn, "imgP1_$i", 1, $idEquipo, $idEsc, $noEmp);
    guardarImagenEsc($conn, "imgP3_$i", 3, $idEquipo, $idEsc, $noEmp);
}

/* ---------- 5. Marcadores y nivel global ---------- */
$marcadorPuro = round($marcadorPuro, 2);
$marcadorGuardas = round($marcadorGuardas, 2);
$marcadorIngenieria = round($marcadorIngenieria, 2);

// Actualizar los marcadores y el nivel de riesgo global del RARR
ejecutarTx(
    $conn,
    "UPDATE TLX002MXDB.dbo.Seg_RARR
     SET MarcadorPuro = ?, MarcadorGuardas = ?, MarcadorIngenieria = ?,
         NivelRiesgo = ?, FechaActualizacion = GETDATE()
     WHERE IdRARR = ?",
    [$marcadorPuro, $marcadorGuardas, $marcadorIngenieria, clasificarNivelRiesgo($marcadorPuro), $idRARR]
);

// Actualizar estado en caso de actualizar para regresar a Pendiente y permitir re-evaluación de escenarios
if ($idRARREdicion !== null) {
    ejecutarTx(
        $conn,
        "UPDATE TLX002MXDB.dbo.Seg_RARR
         SET Estatus = 'Pendiente', FechaConclusion = NULL
         WHERE IdRARR = ?",
        [$idRARR]
    );
}

if (sqlsrv_commit($conn) === false) {
    abortar($conn, "No se pudo confirmar el registro", sqlsrv_errors());
}
sqlsrv_close($conn);

registrarLog($conn, $idRARREdicion ? 'Edicion' : 'Alta', [
    'modulo' => 'RegistroRARR',
    'entidad' => 'RARR',
    'idEquipo' => $idEquipo,
    'idRARR' => $idRARR,
    'detalle' => ['escenarios' => $totalEscenarios, 'genericos' => $totalGenericos]
]);

responderOK([
    "idRARR" => $idRARR,
    "idEquipo" => $idEquipo,
    "escenarios" => $totalEscenarios,
    "genericos" => $totalGenericos,
    "evaluaciones" => $totalEvaluaciones,
    "soluciones" => $totalSoluciones,
    "acciones" => $totalAcciones,
    "marcadorPuro" => $marcadorPuro,
    "marcadorGuardas" => $marcadorGuardas,
    "marcadorIngenieria" => $marcadorIngenieria
], "RARR registrado correctamente");