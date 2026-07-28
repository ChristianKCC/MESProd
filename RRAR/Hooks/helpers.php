<?php
/* ============================================================================
   HOOK: Funciones gancho de negocio
   ============================================================================ */

/* Rangos para clasificar la calificación de riesgo (Sev * Prob * Frec).
   Los umbrales se pueden ajustar segun la matriz origen de riesgos. */
function clasificarNivelRiesgo($calificacion)
{
    if ($calificacion <= 5)
        return 'Aceptable';
    if ($calificacion <= 50)
        return 'Bajo';
    if ($calificacion <= 500)
        return 'Alto';
    return 'Inaceptable';
}

/* Obtencion del IBM (no_emp) de la sesión. */
function obtenerNoEmp()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['ibm']) ? trim($_SESSION['ibm']) : '0';
}

/* Sanitiza un string de entrada. */
function limpiar($valor)
{
    return trim(filter_var($valor ?? '', FILTER_UNSAFE_RAW));
}

/* Valida que un valor sea entero positivo; devuelve int o null. */
function enteroONull($valor)
{
    if ($valor === null || $valor === '')
        return null;
    return filter_var($valor, FILTER_VALIDATE_INT) !== false ? (int) $valor : null;
}

/* Fecha actual de sistema en zona México (Tab 2, punto 3). */
function fechaSistemaMX($formato = 'Y-m-d H:i:s')
{
    $tz = new DateTimeZone('America/Mexico_City');
    $dt = new DateTime('now', $tz);
    return $dt->format($formato);
}

/* Busca o crea el RARR maestro para una máquina + sección.
   $conn debe venir conectado a TLX002MXDB. */
function obtenerOCrearRARR($conn, $idDepartamento, $departamento, $idMaquina, $maquina, $seccionEquipo, $noEmp)
{
    $sql = "SELECT TOP 1 IdRARR FROM TLX002MXDB.dbo.Seg_RARR
            WHERE IdMaquina = ? AND ISNULL(SeccionEquipo,'') = ISNULL(?, '')";
    $stmt = sqlsrv_query($conn, $sql, [$idMaquina, $seccionEquipo]);
    if ($stmt === false) {
        responderError("Error al buscar el RARR", 500, sqlsrv_errors());
    }
    $fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);

    if ($fila) {
        return (int) $fila['IdRARR'];
    }

    return insertarYObtenerId(
        $conn,
        "INSERT INTO TLX002MXDB.dbo.Seg_RARR
            (IdDepartamento, Departamento, IdMaquina, Maquina, SeccionEquipo, Estatus, no_emp)
         VALUES (?,?,?,?,?, 'Pendiente', ?)",
        [$idDepartamento, $departamento, $idMaquina, $maquina, $seccionEquipo, $noEmp]
    );
}

/* Recalcula el peor nivel de riesgo del RARR según los escenarios activos.
   Cortes del método: <=5 Aceptable | <=50 Bajo | <=500 Alto | >500 Inaceptable */
function actualizarNivelRARR($conn, $idRARR)
{
    $sql = "UPDATE r SET r.NivelRiesgo = peor.Nivel
            FROM TLX002MXDB.dbo.Seg_RARR r
            CROSS APPLY (
                SELECT TOP 1
                    CASE
                        WHEN MAX(e.Calificacion) > 500 THEN 'Inaceptable'
                        WHEN MAX(e.Calificacion) > 50  THEN 'Alto'
                        WHEN MAX(e.Calificacion) > 5   THEN 'Bajo'
                        ELSE 'Aceptable'
                    END AS Nivel
                FROM TLX002MXDB.dbo.Seg_EscenarioRiesgo e
                WHERE e.IdRARR = r.IdRARR AND e.Activo = 1
            ) peor
            WHERE r.IdRARR = ?";
    $stmt = sqlsrv_query($conn, $sql, [$idRARR]);
    if ($stmt !== false) {
        sqlsrv_free_stmt($stmt);
    }
}

/* Info de la máquina + su departamento.
   Vive en TLX009MXDB, así que abre su propia conexión (no usa la de TLX002MXDB).
   Si la máquina tiene varios deptos en tblMaquinasCombo, se prefiere el de Filtro = 1. */
function obtenerInfoMaquinaDepto($idMaquina)
{
    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX009MXDB");

    $sql = "SELECT TOP 1
                m.NoMaquina            AS IdMaquina,
                RTRIM(m.NombreMaquina) AS Maquina,
                d.NoDepto              AS IdDepartamento,
                RTRIM(d.NombreDepto)   AS Departamento
            FROM TLX009MXDB.dbo.tblMaquinas m
            LEFT JOIN TLX009MXDB.dbo.tblMaquinasCombo mc
                   ON mc.NoMaquina = m.NoMaquina
            LEFT JOIN TLX009MXDB.dbo.tblDepartamentos d
                   ON d.NoDepto = mc.NoDepto
                  AND ISNULL(d.DepartamentoObsoleto, 0) = 0
            WHERE m.NoMaquina = ?
            ORDER BY CASE WHEN ISNULL(d.Filtro, 0) = 1 THEN 0 ELSE 1 END,
                     d.NoDepto";

    $filas = ejecutarQuery($conn, $sql, [$idMaquina]);
    sqlsrv_close($conn);
    return $filas;
}

function registrarLog($conn, $accion, $opts = [])
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $ibm = $_SESSION['ibm'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    $sql = "INSERT INTO TLX002MXDB.dbo.Seg_LogRARR
                (Ibm, Modulo, Accion, Entidad, IdEquipo, IdRARR, Detalle, Ip)
            VALUES (?,?,?,?,?,?,?,?)";
    $params = [
        $ibm,
        $opts['modulo'] ?? null,
        $accion,
        $opts['entidad'] ?? null,
        $opts['idEquipo'] ?? null,
        $opts['idRARR'] ?? null,
        isset($opts['detalle']) && is_array($opts['detalle'])
        ? json_encode($opts['detalle'], JSON_UNESCAPED_UNICODE)
        : ($opts['detalle'] ?? null),
        $ip,
    ];
    // El log nunca debe tumbar la operación principal: si falla, se ignora.
    @sqlsrv_query($conn, $sql, $params);
}