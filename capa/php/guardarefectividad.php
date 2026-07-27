<?php
require_once '../../../csql.php';
 $calc=$_POST['calc'];
 $idcapa=$_POST['idcapa'];
 $fecha=date('Y/m/d');
$query="INSERT INTO tblCapaEfectividad (idcapa,efectividad,fecha) VALUES ('".$idcapa."','".$calc."','".$fecha."')";
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

