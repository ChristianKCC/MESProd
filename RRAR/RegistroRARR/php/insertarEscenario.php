<?php
/* ============================================================================
   ENDPOINT: Insertar escenario de riesgo (Tab 1, punto 9)
   POST:
     idMaquina, seccionEquipo, idCategoriaPeligro, descripcionPeligro,
     escenarioRiesgo, idSeveridad, idProbabilidad, idFrecuencia, personalExpuesto
   Calcula Calificacion = Sev * Prob * Frec y su NivelRiesgo,
   se crea un RARR maestro si no existe y actualiza su nivel global.
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idMaquina = enteroONull($_POST['idMaquina'] ?? null);
$seccionEquipo = limpiar($_POST['seccionEquipo'] ?? '');
$idCategoriaPeligro = enteroONull($_POST['idCategoriaPeligro'] ?? null);
$descripcionPeligro = limpiar($_POST['descripcionPeligro'] ?? '');
$escenarioRiesgo = limpiar($_POST['escenarioRiesgo'] ?? '');
$idSeveridad = enteroONull($_POST['idSeveridad'] ?? null);
$idProbabilidad = enteroONull($_POST['idProbabilidad'] ?? null);
$idFrecuencia = enteroONull($_POST['idFrecuencia'] ?? null);
$personalExpuesto = limpiar($_POST['personalExpuesto'] ?? '');
$idPersonas = enteroONull($_POST['idPersonas'] ?? null);

if (
    $idMaquina === null || $idCategoriaPeligro === null || $descripcionPeligro === ''
    || $idSeveridad === null || $idProbabilidad === null || $idFrecuencia === null || $idPersonas === null

) {
    responderError("Faltan campos obligatorios del escenario de riesgo");
}

$noEmp = obtenerNoEmp();

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

/* Valores de los catálogos para el cálculo */
$vals = ejecutarQuery(
    $conn,
    "SELECT
        (SELECT Valor FROM TLX002MXDB.dbo.Seg_CatSeveridad         WHERE IdSeveridad = ?)     AS Sev,
        (SELECT Valor FROM TLX002MXDB.dbo.Seg_CatProbabilidad      WHERE IdProbabilidad = ?)  AS Prob,
        (SELECT Valor FROM TLX002MXDB.dbo.Seg_CatFrecuencia        WHERE IdFrecuencia = ?)    AS Frec,
        (SELECT Valor       FROM TLX002MXDB.dbo.Seg_CatPersonasExpuestas WHERE IdPersonas = ?) AS Pers,
        (SELECT Descripcion FROM TLX002MXDB.dbo.Seg_CatPersonasExpuestas WHERE IdPersonas = ?) AS PersDesc",
    [$idSeveridad, $idProbabilidad, $idFrecuencia, $idPersonas, $idPersonas]
);

if (
    $vals[0]['Sev'] === null || $vals[0]['Prob'] === null
    || $vals[0]['Frec'] === null || $vals[0]['Pers'] === null
) {
    sqlsrv_close($conn);
    responderError("Alguno de los catálogos seleccionados no existe");
}

$calificacion = (float) $vals[0]['Sev'] * (float) $vals[0]['Prob']
    * (float) $vals[0]['Frec'] * (float) $vals[0]['Pers'];
$nivel = clasificarNivelRiesgo($calificacion);

$personalExpuesto = $vals[0]['PersDesc'];   // se guarda la descripción, ej. "1 a 2 personas"

/* Info de la máquina para el RARR maestro */
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
    $seccionEquipo !== '' ? $seccionEquipo : null,
    $noEmp
);

$idEscenario = insertarYObtenerId(
    $conn,
    "INSERT INTO TLX002MXDB.dbo.Seg_EscenarioRiesgo
        (IdRARR, IdCategoriaPeligro, DescripcionPeligro, EscenarioRiesgo,
         IdSeveridad, IdProbabilidad, IdFrecuencia, PersonalExpuesto,
         Calificacion, NivelRiesgo, no_emp)
     VALUES (?,?,?,?,?,?,?,?,?,?,?)",
    [
        $idRARR,
        $idCategoriaPeligro,
        $descripcionPeligro,
        $escenarioRiesgo !== '' ? $escenarioRiesgo : null,
        $idSeveridad,
        $idProbabilidad,
        $idFrecuencia,
        $personalExpuesto !== '' ? $personalExpuesto : null,
        $calificacion,
        $nivel,
        $noEmp
    ]
);

actualizarNivelRARR($conn, $idRARR);
sqlsrv_close($conn);

responderOK([
    "idEscenario" => $idEscenario,
    "idRARR" => $idRARR,
    "calificacion" => $calificacion,
    "nivelRiesgo" => $nivel
], "Escenario de riesgo registrado correctamente");
