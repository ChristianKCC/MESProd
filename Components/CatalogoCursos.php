<?php
// Instancia de conexion a la BD
require_once "./CatalogosF.php";

// Uso de getDataSlcDB para la obtencion e inicializacion de datos en cursos
// Algunos llaman a store procedure / Algunos ejecutan query desde aqui
if (isset($_GET["GetSlcAreatematica"])) {
    Catalogos::getDataSlcDB("TLX035MXDB", "EXEC pa_P009_00102LlenarCbxAreaTematicaCapturaCursos");
} else if (isset($_GET["GetSlcModalidadcap"])) {
    Catalogos::getDataSlcDB("TLX035MXDB", "EXEC pa_P009_00103LlenarCbxModCapacitacionCapturaCursos");
} else if (isset($_GET["GetSlcObjcapasitacion"])) {
    Catalogos::getDataSlcDB("TLX035MXDB", "EXEC pa_P009_00104LlenarCbxObjCapacitacionCapturaCursos");
} else if (isset($_GET["GetSlcClasificacion"])) {
    Catalogos::getDataSlcDB("TLX035MXDB", "SELECT id,nombre FROM tblcursosclasificacion ORDER BY id ASC");
} else if (isset($_GET['GetSlcCursos'])) {
    Catalogos::getDataSlcDB("TLX035MXDB", "pa_P009_00501_01LlenarCbxCursoCApturaCacitacionXCurso");
} else if (isset($_GET['GetSlcInstructorxCurso'])) {
    $folio = $_GET['folio'];
    Catalogos::getDataSlcDB("TLX035MXDB", "EXEC pa_P009_00503LlenarCbxIdInstructorCapturaCacitacionXCurso '" . $folio . "'");
} else if (isset($_GET['GetSlcEmpleados'])) {
    Catalogos::getDataSlcDB("TLX035MXDB", "EXEC pa_P009_00502_01LlenarCbxNoEmpCapturaCacitacionXCurso");
}   else if (isset($_GET['GetSlcCursosxClasificacion'])) {
    $clasificacion=$_GET['clasificacion'];
    Catalogos::getDataSlcDB("TLX035MXDB", "EXEC pa_P009_00501_5LlenarCbxCursoCApturaCacitacionXCursofiltro $clasificacion");
}   else if (isset($_GET['slccursostipo'])) { 
    Catalogos::getDataSlcDB("TLX035MXDB", "SELECT id,nombre FROM tblCursostipo ORDER BY nombre ASC");
}   else if (isset($_GET['slccursosxtipo'])) { 
    $tipo = $_GET['tipo'];
    Catalogos::getDataSlcDB("TLX035MXDB", "SELECT IdCurso,NombreCurso FROM tblCursos WHERE clasificacion=$tipo ORDER BY NombreCurso ASC");
}