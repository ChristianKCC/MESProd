<?php
// Instacia a la BD
require_once "./CatalogosF.php";

// Obtener el nombre y número de la sala
if(isset($_GET["GetNombreSalasJuntas"])){
  Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblSalaJuntas");
}