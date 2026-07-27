<?php
// Instancia de conexion a la BD
require_once "./CatalogosF.php";

// Obtencion de datos de trazabilidad de turnos
if (isset($_GET["GetSlcTurnos"])) {
    Catalogos::getDataSlcDB("TLX036MXDB", "SELECT * FROM Trazabilidadturno");
} 