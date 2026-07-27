<?php
// Inclucion de archivos para conexion
require_once "./CatalogosF.php";

// Categoria y datos del producto
if (isset($_GET["GetCategoriaProductos"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM ProductoSec");
}  else if (isset($_GET["GetSubcategoriaProductos"])) {
    $id=$_GET["id"];
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT IdProductSub, DescProductSub + ' ' + PaqueteContenido FROM ProductoSub WHERE IdProductSec = $id");
} 