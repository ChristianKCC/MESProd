<?php
require_once '../../../csql.php';
 $id=$_POST['idaccion'];
 $solucion=$_POST['solucion'];
 $implementado=$_POST['implementado'];
 $ruta='';
 if(!empty($_FILES['file']['tmp_name'])){
 $nombrepdf =  $id."-".$_FILES['file']['name'];
 $archivo=$_FILES['file']['tmp_name'];
 $ruta="../Archivos/";
 $ruta=$ruta."".$nombrepdf;
  move_uploaded_file($archivo, $ruta);
}else
  echo "No hay archivo";
$query="UPDATE tblCapaAcciones SET descripcionvalidacion='".$solucion."',archivovalidacion='".$ruta."',accionvalidada=1,implementado=".$implementado." WHERE id=".$id; 
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
