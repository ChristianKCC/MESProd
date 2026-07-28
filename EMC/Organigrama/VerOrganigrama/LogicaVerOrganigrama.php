<?php
/* =====================================================================
   LogicaVerOrganigrama.php  (tipo 2 = PowerPoint)
   Toma el enlace ACTIVO de tipo PowerPoint desde tblMXPREnlaceEMC.

   Ubicacion: EMC/Organigrama/VerOrganigrama/LogicaVerOrganigrama.php
   - conexion.php  -> dirname(__DIR__, 3)  (VerOrganigrama->Organigrama->EMC->KCMes)
   - funciones     -> dirname(__DIR__, 2)."/php/funciones_enlace.php"
   ===================================================================== */

require_once(dirname(__DIR__, 3) . "/conexion.php");
require_once(dirname(__DIR__, 2) . "/php/funciones_enlace.php");

/* (opcional) archivo fisico, solo para mostrar tamaño si lo sigues subiendo */
define('PPTX_FILE', __DIR__ . '/../../uploads/organigrama.pptx');

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$enlaceRow   = obtenerEnlaceActivo($conn, EMC_TIPO_PPT);
$enlaceEmbed = $enlaceRow ? construirEnlaceEmbed($enlaceRow['enlace'], EMC_TIPO_PPT) : null;

$fileExists = ($enlaceRow !== null) && !empty($enlaceEmbed);
$fileName   = $fileExists ? $enlaceRow['nombre_archivo'] : null;

$fileUpdated = null;
if ($fileExists) {
    $fileUpdated = ($enlaceRow['fecha_registro'] instanceof DateTime)
                 ? $enlaceRow['fecha_registro']->format('d/m/Y H:i:s')
                 : (string) $enlaceRow['fecha_registro'];
}

$fileSize = file_exists(PPTX_FILE) ? round(filesize(PPTX_FILE) / 1024, 1) . ' KB' : '';
?>