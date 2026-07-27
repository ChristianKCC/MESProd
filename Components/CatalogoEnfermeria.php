<?php
// Instancia de conexion a BD
require_once "./CatalogosF.php";

// Obtencion de datos con CatalogosF mandando por parametro datos para info de enfermeria
if (isset($_GET["GetEnfermeriaEquipos"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblEnfermeriaEquipos");
} else if (isset($_GET["GetEnfermeriaEnfermedades"])) {
    $id=$_GET['id'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblEnfermeriaEnfermedades WHERE idequipo=$id");
}  else if (isset($_GET["GetEnfermeriaTipoConsult"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblEnfermeriaTipoConsult");
}  else if (isset($_GET["GetEnfermeriaTipoIncapacidad"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblEnfermeriaTipoIncapacidad");
}  else if (isset($_GET["GetEnfermeriaFrec"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblEnfermeriaFrec");
}  else if (isset($_GET["GetEnfermeriaIMC"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblEnfermeriaTipoIMC");
}  else if (isset($_GET["GetEnfermeriaAudiometria"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblEnfermeriaTipoAudiometria");
}