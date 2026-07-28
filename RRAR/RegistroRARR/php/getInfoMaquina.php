<?php
/* ============================================================================
   ENDPOINT: Información de la máquina (Tab 2: llena Sección / Componente)
   ----------------------------------------------------------------------------
   - Máquina + departamento: relación directa usando tblMaquinas + tblMaquinasCombo
   - Secciones / componentes: relación usando tblBitCatCombinacionesMaquina + tblBitCatSecciones
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idMaquina = enteroONull($_GET['idMaquina'] ?? null);
if ($idMaquina === null) {
  responderError("Máquina no válida");
}

/* ---------------------------
   1. Info de la máquina + departamento
   --------------------------- */
$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX009MXDB");

$sqlInfo = "SELECT TOP 1
                m.NoMaquina            AS IdMaquina,
                RTRIM(m.NombreMaquina) AS Maquina,
                d.NoDepto              AS IdDepartamento,
                RTRIM(d.NombreDepto)   AS Departamento
            FROM TLX009MXDB.dbo.tblMaquinas m
            INNER JOIN TLX009MXDB.dbo.tblMaquinasCombo mc
                    ON mc.NoMaquina = m.NoMaquina
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos d
                    ON d.NoDepto = mc.NoDepto
            WHERE m.NoMaquina = ?
              AND m.MaquinaObsoleta = 0";

$info = ejecutarQuery($conn, $sqlInfo, [$idMaquina]);

if (count($info) === 0) {
  sqlsrv_close($conn);
  responderError("La máquina no existe en el catálogo", 404);
}

sqlsrv_close($conn);

/* ---------------------------
   2. Secciones / componentes de la máquina
   --------------------------- */
$conn2 = $ClassConexion->conexion("TLX002MXDB");

$sqlSecciones = "SELECT DISTINCT
                    s.idSeccion          AS IdSeccion,
                    RTRIM(s.nombreSeccion) AS Seccion
                 FROM TLX002MXDB.dbo.tblBitCatCombinacionesMaquina c
                 INNER JOIN TLX002MXDB.dbo.tblBitCatSecciones s
                         ON s.idSeccion = c.idSeccion
                 WHERE c.idMaquina = ?
                   AND ISNULL(s.obsoleta, 0) = 0
                 ORDER BY RTRIM(s.nombreSeccion)";

$secciones = ejecutarQuery($conn2, $sqlSecciones, [$idMaquina]);

sqlsrv_close($conn2);

/* ---------------------------
   3. Respuesta final
   --------------------------- */
responderOK([
  "maquina" => $info[0],
  "secciones" => array_column($secciones, 'Seccion')
]);
