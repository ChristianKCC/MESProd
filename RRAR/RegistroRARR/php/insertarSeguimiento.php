<?php
/* ============================================================================
   ENDPOINT: Agregar acción del Plan de Acción (Tab 3, bloque B)
   POST: idMaquina, descripcion (Acción a realizar), responsable,
         fechaImplementacion (Fecha objetivo), idEstatus, idTipoControl?
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idMaquina = enteroONull($_POST['idMaquina'] ?? null);
$descripcion = limpiar($_POST['descripcion'] ?? '');
$responsable = limpiar($_POST['responsable'] ?? '');
$idTipoControl = enteroONull($_POST['idTipoControl'] ?? null);
$fechaImplementacion = limpiar($_POST['fechaImplementacion'] ?? '');
$idEstatus = enteroONull($_POST['idEstatus'] ?? null);

if ($idMaquina === null || $descripcion === '') {
    responderError("La máquina y la acción a realizar son obligatorias");
}
if ($fechaImplementacion !== '' && DateTime::createFromFormat('Y-m-d', $fechaImplementacion) === false) {
    responderError("La fecha objetivo no tiene un formato válido");
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

$idSeguimiento = insertarYObtenerId(
    $conn,
    "INSERT INTO TLX002MXDB.dbo.Seg_SeguimientoControl
        (IdRARR, Descripcion, Responsable, IdTipoControl, FechaImplementacion, IdEstatus, no_emp)
     VALUES (?,?,?,?,?,?,?)",
    [
        $idRARR,
        $descripcion,
        $responsable !== '' ? $responsable : null,
        $idTipoControl,
        $fechaImplementacion !== '' ? $fechaImplementacion : null,
        $idEstatus,
        $noEmp
    ]
);

sqlsrv_close($conn);
responderOK(
    ["idSeguimiento" => $idSeguimiento, "idRARR" => $idRARR],
    "Acción agregada correctamente"
);
