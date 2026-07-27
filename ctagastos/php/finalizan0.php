<?php
 include '../../../csql32.php';
 $id=$_POST['id'];
$query="UPDATE tblCTAgastosEncabezadocta SET estado=2 WHERE id=$id;";
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
 <div class='alert alert-success' role='alert'>Enviado correctamente</div>
