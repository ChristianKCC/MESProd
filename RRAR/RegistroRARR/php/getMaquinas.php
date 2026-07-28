<?php
/* ============================================================================
   ENDPOINT: Catálogo de máquinas (select de Máquina en Tab 1 y Tab 2)
   ----------------------------------------------------------------------------
   Regla: sólo máquinas que pertenezcan a un departamento con Filtro <> 1
   (relación en tblMaquinasCombo). Se excluyen máquinas y deptos obsoletos.
   Si llega idDepartamento, además se acota a ese depto.
   Devuelve 'id' y 'nombre' para analisis.js
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';

$idDepartamento = enteroONull($_GET['idDepartamento'] ?? null);

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX009MXDB");

$sql = "SELECT
            m.NoMaquina            AS id,
            RTRIM(m.NombreMaquina) AS nombre
        FROM TLX009MXDB.dbo.tblMaquinas m
        WHERE ISNULL(m.MaquinaObsoleta, 0) <> 1
          AND EXISTS (
                SELECT 1
                FROM TLX009MXDB.dbo.tblMaquinasCombo mc
                INNER JOIN TLX009MXDB.dbo.tblDepartamentos d
                        ON d.NoDepto = mc.NoDepto
                WHERE mc.NoMaquina = m.NoMaquina
                  AND ISNULL(d.Filtro, 0) <> 0
                  AND ISNULL(d.DepartamentoObsoleto, 0) <> 1
                  AND (? IS NULL OR mc.NoDepto = ?)
          )
        ORDER BY RTRIM(m.NombreMaquina)";

$filas = ejecutarQuery($conn, $sql, [$idDepartamento, $idDepartamento]);

sqlsrv_close($conn);
responderOK($filas);