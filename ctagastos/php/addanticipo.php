<?php
 include '../../../csql32.php';
 $folio=$_POST['folio'];
 $anticipo=$_POST['cntanticipo'];
$query="UPDATE tblCTAgastosEncabezadocta SET anticipo='".$anticipo."' WHERE id=$folio";
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
