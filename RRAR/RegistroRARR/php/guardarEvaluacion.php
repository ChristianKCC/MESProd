<?php
/* ============================================================================
   ENDPOINT: Guardar evaluación (Tab 2 — Análisis de Protección de Maquinaria)
   POST:
     idMaquina, componente, descripcionHallazgo (Descripción de Guarda Actual),
     criterioGuarda, nivelRiesgoActual, accionesPropuestas (Acciones de
     Contención), medidasMitigacion, porcentajeAvance, nombreResponsable,
     fechaCompromiso (Fecha Implementación), idTipoControl?, idPrioridad?,
     areaResponsable?
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idMaquina = enteroONull($_POST['idMaquina'] ?? null);
$componente = limpiar($_POST['componente'] ?? '');
$descripcion = limpiar($_POST['descripcionHallazgo'] ?? '');
$criterioGuarda = limpiar($_POST['criterioGuarda'] ?? '');
$nivelRiesgoActual = limpiar($_POST['nivelRiesgoActual'] ?? '');
$accionesPropuestas = limpiar($_POST['accionesPropuestas'] ?? '');
$medidasMitigacion = limpiar($_POST['medidasMitigacion'] ?? '');
$porcentajeAvance = enteroONull($_POST['porcentajeAvance'] ?? 0) ?? 0;
$nombreResponsable = limpiar($_POST['nombreResponsable'] ?? '');
$fechaCompromiso = limpiar($_POST['fechaCompromiso'] ?? '');
$idTipoControl = enteroONull($_POST['idTipoControl'] ?? null);
$idPrioridad = enteroONull($_POST['idPrioridad'] ?? null);
$areaResponsable = limpiar($_POST['areaResponsable'] ?? '');

if ($idMaquina === null || $descripcion === '') {
    responderError("La máquina y la descripción de guarda son obligatorias");
}
if ($porcentajeAvance < 0 || $porcentajeAvance > 100) {
    responderError("El progreso de implementación debe estar entre 0 y 100");
}
if ($fechaCompromiso !== '' && DateTime::createFromFormat('Y-m-d', $fechaCompromiso) === false) {
    responderError("La fecha de implementación no tiene un formato válido (YYYY-MM-DD)");
}

$noEmp = obtenerNoEmp();

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$infoM = obtenerInfoMaquinaDepto($idMaquina);

if (count($infoM) === 0) {
    sqlsrv_close($conn);
    responderError("La máquina no existe en el catálogo", 404);
}

$idRARR = obtenerOCrearRARR(
    $conn,
    (int) $infoM[0]['IdDepartamento'],
    $infoM[0]['Departamento'],
    (int) $infoM[0]['IdMaquina'],
    $infoM[0]['Maquina'],
    $componente !== '' ? $componente : null,
    $noEmp
);

$idEvaluacion = insertarYObtenerId(
    $conn,
    "INSERT INTO TLX002MXDB.dbo.Seg_EvaluacionRARR
        (IdRARR, Componente, FechaSistema, DescripcionHallazgo, CriterioGuarda,
         NivelRiesgoActual, IdTipoControl, IdPrioridad, AreaResponsable,
         PorcentajeAvance, AccionesPropuestas, MedidasMitigacion,
         NombreResponsable, FechaCompromiso, no_emp)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
    [
        $idRARR,
        $componente !== '' ? $componente : null,
        fechaSistemaMX(),
        $descripcion,
        $criterioGuarda !== '' ? $criterioGuarda : null,
        $nivelRiesgoActual !== '' ? $nivelRiesgoActual : null,
        $idTipoControl,
        $idPrioridad,
        $areaResponsable !== '' ? $areaResponsable : null,
        $porcentajeAvance,
        $accionesPropuestas !== '' ? $accionesPropuestas : null,
        $medidasMitigacion !== '' ? $medidasMitigacion : null,
        $nombreResponsable !== '' ? $nombreResponsable : null,
        $fechaCompromiso !== '' ? $fechaCompromiso : null,
        $noEmp
    ]
);

sqlsrv_close($conn);
responderOK(
    ["idEvaluacion" => $idEvaluacion, "idRARR" => $idRARR],
    "Evaluación guardada correctamente"
);
