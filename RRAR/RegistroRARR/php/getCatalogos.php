<?php
/* ============================================================================
   ENDPOINT: Catálogos de los selects de los 3 pasos, en una sola llamada.
   Los que traen Valor alimentan el cálculo del puntaje.
   ============================================================================ */
require_once __DIR__ . '/../../Hooks/respuesta.php';
require_once __DIR__ . '/../../Hooks/conexion.php';

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$catalogos = [
    "categoriasPeligro" => ejecutarQuery(
        $conn,
        "SELECT IdCategoria AS id, Descripcion FROM TLX002MXDB.dbo.Seg_CatCategoriaPeligro
         WHERE Activo = 1 ORDER BY Descripcion"
    ),
    "consecuencias" => ejecutarQuery(
        $conn,
        "SELECT IdConsecuencia AS id, Descripcion FROM TLX002MXDB.dbo.Seg_CatConsecuencia
         WHERE Activo = 1 ORDER BY Descripcion"
    ),
    "mecanismos" => ejecutarQuery(
        $conn,
        "SELECT IdMecanismo AS id, Descripcion FROM TLX002MXDB.dbo.Seg_CatMecanismo
         WHERE Activo = 1 ORDER BY Descripcion"
    ),
    "fuentes" => ejecutarQuery(
        $conn,
        "SELECT IdFuente AS id, Descripcion FROM TLX002MXDB.dbo.Seg_CatFuente
         WHERE Activo = 1 ORDER BY Descripcion"
    ),
    "severidades" => ejecutarQuery(
        $conn,
        "SELECT IdSeveridad AS id, Descripcion, Valor FROM TLX002MXDB.dbo.Seg_CatSeveridad
         WHERE Activo = 1 ORDER BY Valor"
    ),
    "probabilidades" => ejecutarQuery(
        $conn,
        "SELECT IdProbabilidad AS id, Descripcion, Valor FROM TLX002MXDB.dbo.Seg_CatProbabilidad
         WHERE Activo = 1 ORDER BY Valor DESC"
    ),
    "frecuencias" => ejecutarQuery(
        $conn,
        "SELECT IdFrecuencia AS id, Descripcion, Valor FROM TLX002MXDB.dbo.Seg_CatFrecuencia
         WHERE Activo = 1 ORDER BY Valor"
    ),
    "personasExpuestas" => ejecutarQuery(
        $conn,
        "SELECT IdPersonas AS id, Descripcion, Valor FROM TLX002MXDB.dbo.Seg_CatPersonasExpuestas
         WHERE Activo = 1 ORDER BY Valor"
    ),
    "criteriosGuarda" => ejecutarQuery(
        $conn,
        "SELECT IdCriterio AS id, Descripcion, Valor FROM TLX002MXDB.dbo.Seg_CatCriterioGuarda
         WHERE Activo = 1 ORDER BY IdCriterio"
    ),
    "medidasMitigacion" => ejecutarQuery(
        $conn,
        "SELECT IdMedida AS id, Descripcion, Valor FROM TLX002MXDB.dbo.Seg_CatMedidaMitigacion
         WHERE Activo = 1 ORDER BY IdMedida"
    ),
    "seguridadFuncional" => ejecutarQuery(
        $conn,
        "SELECT IdSeguridadFuncional AS id, Descripcion
         FROM TLX002MXDB.dbo.Seg_CatSeguridadFuncional
         WHERE Activo = 1 ORDER BY IdSeguridadFuncional"
    ),
    "estatus" => ejecutarQuery(
        $conn,
        "SELECT IdEstatus AS id, Descripcion FROM TLX002MXDB.dbo.Seg_CatEstatus
         WHERE Activo = 1 ORDER BY IdEstatus"
    ),
    "tiposControl" => ejecutarQuery(
        $conn,
        "SELECT IdTipoControl AS id, Descripcion FROM TLX002MXDB.dbo.Seg_CatTipoControl WHERE Activo = 1 ORDER BY IdTipoControl"
    ),
    "prioridades" => ejecutarQuery(
        $conn,
        "SELECT IdPrioridad AS id, Descripcion FROM TLX002MXDB.dbo.Seg_CatPrioridad WHERE Activo = 1 ORDER BY IdPrioridad"
    ),
    "responsables" => ejecutarQuery(
        $conn,
        "SELECT DISTINCT RTRIM(NombreResponsable) AS Nombre
         FROM TLX002MXDB.dbo.Seg_EvaluacionRARR
         WHERE NombreResponsable IS NOT NULL AND LTRIM(RTRIM(NombreResponsable)) <> ''
         ORDER BY Nombre"
    ),
];

sqlsrv_close($conn);
responderOK($catalogos);