<?php
 include '../../../csql32.php';
 $id=$_POST['id'];
 $query="DELETE FROM tblCTAgastosSubencta WHERE id=$id";
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
 <script type="text/javascript">
    $("#resultadoenc").html('');
    $("#resultadosub").html('');
 </script>
