<?php
require_once("../../Session/seguridad.php");

if($_SESSION["permisoPersonal"]!=1){
  header('Location: ../index/index');
}

// Manejo y respuesta segun la accion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear_pdf') {
        require 'creartarjetasClas.php';
        exit;
    } elseif ($accion === 'crear_excel') {
        // Acceso a php con procesamiento de excel
        require '../Excel/crearExcel.php';
        exit;
    }
}
