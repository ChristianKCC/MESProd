<?php
/* ============================================================================
   ENDPOINT: Insertar acción de mejora (Tab 3 bloque A, punto 2)
   POST: idMaquina, descripcion, fechaImplementacion?, inversionEstimada?, idEstatus?
   (fechaImplementacion / inversión / estatus estan opcionales: "por definir")
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idMaquina = enteroONull($_POST['idMaquina'] ?? null);
$descripcion = limpiar($_POST['descripcion'] ?? '');
$fechaImplementacion = limpiar($_POST['fechaImplementacion'] ?? '');
$inversionEstimada = limpiar($_POST['inversionEstimada'] ?? '');
$idEstatus = enteroONull($_POST['idEstatus'] ?? null);

if ($idMaquina === null || $descripcion === '') {
    responderError("La máquina y la descripción de la acción son obligatorias");
}
if ($fechaImplementacion !== '' && DateTime::createFromFormat('Y-m-d', $fechaImplementacion) === false) {
    responderError("La fecha de implementación no tiene un formato válido");
}
$inversion = null;
if ($inversionEstimada !== '') {
    if (!is_numeric($inversionEstimada)) {
        responderError("La inversión estimada debe ser numérica");
    }
    $inversion = round((float) $inversionEstimada, 2);
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
    null,
    $noEmp
);

$idAccion = insertarYObtenerId(
    $conn,
    "INSERT INTO TLX002MXDB.dbo.Seg_AccionMejora
        (IdRARR, Descripcion, FechaImplementacion, InversionEstimada, IdEstatus, no_emp)
     VALUES (?,?,?,?,?,?)",
    [
        $idRARR,
        $descripcion,
        $fechaImplementacion !== '' ? $fechaImplementacion : null,
        $inversion,
        $idEstatus,
        $noEmp
    ]
);

sqlsrv_close($conn);
responderOK(
    ["idAccion" => $idAccion, "idRARR" => $idRARR],
    "Acción de mejora registrada correctamente"
);
