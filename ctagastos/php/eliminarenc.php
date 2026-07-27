<?php
 include '../../../csql32.php';
 $id=$_POST['id'];
 $query="DELETE FROM tblCTAgastosEncabezadocta WHERE id=$id";
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
 <div class="alert alert-danger" role="alert">Se eliminó la cuenta</div>
 <script type="text/javascript">
    $("#resultadosub").html('');
 </script>
