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
move_uploaded_file($archivo, $ruta);
$query="UPDATE tblCapaAccionesMenor SET solucion='".$solucion."',archivo='".$ruta."',accioncompleta=1,usuariovalida='".$validador."',fechacompletada='".$fecha."' WHERE id=".$id; 
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
else{
  echo"Todo bien";
}
?>
