<?php
/* =====================================================================
   LogicaVerMatriz.php  (tipo 1 = Excel)
   Toma el enlace ACTIVO de tipo Excel desde tblMXPREnlaceEMC.

   Ubicacion: EMC/Matriz/VerMatriz/LogicaVerMatriz.php
   - conexion.php  -> dirname(__DIR__, 3)  (VerMatriz->Matriz->EMC->KCMes)
   - funciones     -> dirname(__DIR__, 2)."/php/funciones_enlace.php"
   ===================================================================== */

require_once(dirname(__DIR__, 3) . "/conexion.php");
require_once(dirname(__DIR__, 2) . "/php/funciones_enlace.php");

/* (opcional) archivo fisico, solo para mostrar tamaño si lo sigues subiendo */
define('UPLOAD_DIR_EMC_CL', __DIR__ . '/../../uploads/');
define('EXCEL_FILE_EMC_CL', UPLOAD_DIR_EMC_CL . 'datos.xlsx');
define('CSV_FILE_EMC_CL',   UPLOAD_DIR_EMC_CL . 'datos.csv');

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$enlaceRow   = obtenerEnlaceActivo($conn, EMC_TIPO_EXCEL_CL);
$enlaceEmbed = $enlaceRow ? construirEnlaceEmbed($enlaceRow['enlace'], EMC_TIPO_EXCEL_CL) : null;

$fileExists = ($enlaceRow !== null) && !empty($enlaceEmbed);
$fileName   = $fileExists ? $enlaceRow['nombre_archivo'] : null;

$fileUpdated = null;
if ($fileExists) {
    $fileUpdated = ($enlaceRow['fecha_registro'] instanceof DateTime)
                 ? $enlaceRow['fecha_registro']->format('d/m/Y H:i:s')
                 : (string) $enlaceRow['fecha_registro'];
}

$activeFile = file_exists(EXCEL_FILE_EMC_CL) ? EXCEL_FILE_EMC_CL
            : (file_exists(CSV_FILE_EMC_CL) ? CSV_FILE_EMC_CL : null);
$fileSize = $activeFile ? round(filesize($activeFile) / 1024, 1) . ' KB' : '';
?>