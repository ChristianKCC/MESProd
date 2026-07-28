<?php
/* ============================================================================
   ENDPOINT: Alta / edición de una sección (modal Personalizar)
   POST: id (vacío = nueva), idMaquina, nombreSeccion, abreviatura
   El IdEquipo lo genera SQL Server (columna calculada).
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$id = enteroONull($_POST['id'] ?? null);
$idMaquina = enteroONull($_POST['idMaquina'] ?? null);
$nombreSeccion = limpiar($_POST['nombreSeccion'] ?? '');
$abreviatura = strtoupper(limpiar($_POST['abreviatura'] ?? ''));

if ($idMaquina === null || $nombreSeccion === '' || $abreviatura === '') {
    responderError("Máquina, nombre y abreviatura son obligatorios");
}
if (!preg_match('/^[A-Z0-9]{2,5}$/', $abreviatura)) {
    responderError("La abreviatura debe ser de 2 a 5 letras o números, sin espacios");
}

$noEmp = obtenerNoEmp();

/* La máquina y su departamento salen de TLX009MXDB */
$infoM = obtenerInfoMaquinaDepto($idMaquina);
if (count($infoM) === 0) {
    responderError("La máquina no existe en el catálogo", 404);
}
if ($infoM[0]['IdDepartamento'] === null) {
    responderError("Esa máquina no tiene departamento asignado; no se le puede crear una sección");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

/* ---- Edición: si ya tiene RARR, no se puede mover de máquina ni cambiar la abreviatura ---- */
if ($id !== null) {
    $actual = ejecutarQuery(
        $conn,
        "SELECT s.NoMaquina, RTRIM(s.Abreviatura) AS Abreviatura, s.IdEquipo,
                (SELECT COUNT(*) FROM TLX002MXDB.dbo.Seg_RARR r WHERE r.IdEquipo = s.IdEquipo) AS TieneRARR
         FROM TLX002MXDB.dbo.Seg_SeccionMaquina s
         WHERE s.IdSeccion = ?",
        [$id]
    );
    if (count($actual) === 0) {
        sqlsrv_close($conn);
        responderError("La sección no existe", 404);
    }

    $cambioClave = (int) $actual[0]['NoMaquina'] !== $idMaquina
        || $actual[0]['Abreviatura'] !== $abreviatura;

    if ($cambioClave && (int) $actual[0]['TieneRARR'] > 0) {
        sqlsrv_close($conn);
        responderError(
            "Esta sección ya tiene un RARR registrado ({$actual[0]['IdEquipo']}). "
            . "No se puede cambiar su máquina ni su abreviatura porque el ID de equipo "
            . "quedaría desligado del RARR. Solo puedes cambiar el nombre."
        );
    }
}

/* ---- El IdEquipo resultante no se puede repetir ---- */
$idEquipoNuevo = strtoupper(trim($infoM[0]['Departamento'])) . '-'
    . strtoupper(trim($infoM[0]['Maquina'])) . '-' . $abreviatura . '-01';

$dup = ejecutarQuery(
    $conn,
    "SELECT COUNT(*) AS N FROM TLX002MXDB.dbo.Seg_SeccionMaquina
     WHERE Activo = 1 AND IdEquipo = ? AND IdSeccion <> ISNULL(?, 0)",
    [$idEquipoNuevo, $id]
);
if ((int) $dup[0]['N'] > 0) {
    sqlsrv_close($conn);
    responderError("Ya existe una sección con el ID de equipo $idEquipoNuevo");
}

if ($id === null) {
    $nuevo = insertarYObtenerId(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_SeccionMaquina
            (NoDepto, Departamento, NoMaquina, Maquina, NombreSeccion, Abreviatura, no_emp)
         VALUES (?,?,?,?,?,?,?)",
        [
            (int) $infoM[0]['IdDepartamento'],
            $infoM[0]['Departamento'],
            (int) $infoM[0]['IdMaquina'],
            $infoM[0]['Maquina'],
            $nombreSeccion,
            $abreviatura,
            $noEmp
        ]
    );
    sqlsrv_close($conn);
    responderOK(["id" => $nuevo, "idEquipo" => $idEquipoNuevo], "Sección agregada correctamente");
}

$stmt = sqlsrv_query(
    $conn,
    "UPDATE TLX002MXDB.dbo.Seg_SeccionMaquina
     SET NoDepto = ?, Departamento = ?, NoMaquina = ?, Maquina = ?,
         NombreSeccion = ?, Abreviatura = ?
     WHERE IdSeccion = ?",
    [
        (int) $infoM[0]['IdDepartamento'],
        $infoM[0]['Departamento'],
        (int) $infoM[0]['IdMaquina'],
        $infoM[0]['Maquina'],
        $nombreSeccion,
        $abreviatura,
        $id
    ]
);
if ($stmt === false) {
    sqlsrv_close($conn);
    responderError("No se pudo actualizar la sección", 500, sqlsrv_errors());
}
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

responderOK(["id" => $id, "idEquipo" => $idEquipoNuevo], "Sección actualizada correctamente");