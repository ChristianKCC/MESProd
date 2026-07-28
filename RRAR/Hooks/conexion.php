<?php
// Ruta de archivo a la conexion real
require_once __DIR__ . '/../../conexion.php';

/* Base donde viven los catálogos tblDepartamentos / tblMaquinas.
   Se usa con nombre calificado en las queries: [CAT_DB].dbo.tabla */
define('CAT_DB', 'TLX011MXDB');

function obtenerConexion()
{
    global $conn;    // <-- objeto de conexión sqlsrv
    return $conn;
}
