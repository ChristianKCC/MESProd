<?php
require_once '../../../csql.php';
 $id=$_POST['idaccion'];
 $solucion=$_POST['solucion'];
 $validador=$_POST['validador'];
 $fecha=$_POST['fecha'];
 $nombrepdf =  $id."-".$_FILES['file']['name'];
 $archivo=$_FILES['file']['tmp_name'];
 $ruta="../Archivos/";
 $ruta=$ruta."".$nombrepdf;
 $fechavalidacion=date("Y/m/d",strtotime($fecha."+ 1 days"));
move_uploaded_file($archivo, $ruta);
$query="UPDATE tblCapaAcciones SET solucion='".$solucion."',archivo='".$ruta."',accioncompleta=1,usuariovalida='".$validador."',fechacompletada='".$fecha."',fechavalidacion='".$fechavalidacion."' WHERE id=".$id; 
  $stmt = sqlsrv_query($conn,$query);
  if( $stmt === false ) {
    if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
}

 ?>

