<?php
/* ============================================================================
   ENDPOINT: Secciones / Equipos de una máquina (columna SECCIÓN / EQUIPO)
   ----------------------------------------------------------------------------
   Relación directa máquina <-> sección usando:
     - tblBitCatCombinacionesMaquina (idMaquina, idSeccion, idModulo, idFalla)
     - tblBitCatSecciones (idSeccion, nombreSeccion, obsoleta)
   Se listan las secciones no obsoletas de la máquina seleccionada.
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idMaquina = enteroONull($_GET['idMaquina'] ?? null);
if ($idMaquina === null) {
  responderError("Máquina no válida");
}

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$sql = "SELECT DISTINCT
            s.idSeccion          AS IdSeccion,
            RTRIM(s.nombreSeccion) AS Seccion
        FROM TLX002MXDB.dbo.tblBitCatCombinacionesMaquina c
        INNER JOIN TLX002MXDB.dbo.tblBitCatSecciones s
                ON s.idSeccion = c.idSeccion
        WHERE c.idMaquina = ?
          AND ISNULL(s.obsoleta, 0) = 0
        ORDER BY RTRIM(s.nombreSeccion)";

$filas = ejecutarQuery($conn, $sql, [$idMaquina]);
sqlsrv_close($conn);
responderOK($filas);
