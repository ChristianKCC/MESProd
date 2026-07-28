<?php
/* ============================================================================
   ENDPOINT: Listado de un catálogo configurable (modal Personalizar)
   ?tipo=categorias|consecuencias|mecanismos|fuentes|secciones|maquinas
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';
require_once __DIR__ . '/../../Hooks/helpers.php';
require_once __DIR__ . '/../../Hooks/config.php';

$tipo = limpiar($_GET['tipo'] ?? '');

/* ---- Máquinas: solo lectura, vienen del catálogo compartido ---- */
if ($tipo === 'maquinas') {
    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX009MXDB");

    $filas = ejecutarQuery(
        $conn,
        "SELECT
             m.NoMaquina            AS id,
             RTRIM(m.NombreMaquina) AS Descripcion,
             RTRIM(d.NombreDepto)   AS Departamento
         FROM TLX009MXDB.dbo.tblMaquinas m
         INNER JOIN TLX009MXDB.dbo.tblMaquinasCombo mc ON mc.NoMaquina = m.NoMaquina
         INNER JOIN TLX009MXDB.dbo.tblDepartamentos d  ON d.NoDepto   = mc.NoDepto
         WHERE ISNULL(m.MaquinaObsoleta,0) <> 1
           AND ISNULL(d.Filtro,0) <> 0
           AND ISNULL(d.DepartamentoObsoleto,0) = 0
         ORDER BY RTRIM(m.NombreMaquina)"
    );
    sqlsrv_close($conn);
    responderOK($filas);
}

/* ---- Secciones: tabla propia, con su máquina y su ID generado ---- */
if ($tipo === 'secciones') {
    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX002MXDB");

    $filas = ejecutarQuery(
        $conn,
        "SELECT
             s.IdSeccion            AS id,
             RTRIM(s.NombreSeccion) AS Descripcion,
             s.NoMaquina, RTRIM(s.Maquina) AS Maquina,
             s.NoDepto, RTRIM(s.Departamento) AS Departamento,
             RTRIM(s.Abreviatura)   AS Abreviatura,
             s.IdEquipo,
             (SELECT COUNT(*) FROM TLX002MXDB.dbo.Seg_RARR r
               WHERE r.IdEquipo = s.IdEquipo) AS TieneRARR
         FROM TLX002MXDB.dbo.Seg_SeccionMaquina s
         WHERE s.Activo = 1
         ORDER BY RTRIM(s.Maquina), RTRIM(s.NombreSeccion)"
    );
    sqlsrv_close($conn);
    responderOK($filas);
}

/* ---- Catálogos simples ---- */
$cfg = configOMorir($tipo);

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$filas = ejecutarQuery(
    $conn,
    "SELECT {$cfg['pk']} AS id, Descripcion, Activo
     FROM TLX002MXDB.dbo.{$cfg['tabla']}
     WHERE Activo = 1
     ORDER BY Descripcion"
);
sqlsrv_close($conn);
responderOK($filas);