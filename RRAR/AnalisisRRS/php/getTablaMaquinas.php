<?php
/* ============================================================================
   ENDPOINT: Máquinas de un departamento (columna MAQUINA del Reporte)
   La relación máquina <-> departamento vive en tblMaquinasCombo (TLX009MXDB).
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idDepartamento = enteroONull($_GET['idDepartamento'] ?? null);
if ($idDepartamento === null) {
    responderError("Departamento no válido");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX009MXDB");

$sql = "SELECT DISTINCT
            m.NoMaquina            AS IdMaquina,
            RTRIM(m.NombreMaquina) AS Maquina
        FROM TLX009MXDB.dbo.tblMaquinas m
        INNER JOIN TLX009MXDB.dbo.tblMaquinasCombo mc
                ON mc.NoMaquina = m.NoMaquina
        WHERE mc.NoDepto = ?
          AND ISNULL(m.MaquinaObsoleta, 0) <> 1
        ORDER BY RTRIM(m.NombreMaquina)";

$filas = ejecutarQuery($conn, $sql, [$idDepartamento]);
sqlsrv_close($conn);
responderOK($filas);