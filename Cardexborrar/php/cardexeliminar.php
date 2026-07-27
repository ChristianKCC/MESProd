<?php 
require_once "../../../csql32.php";
$id=$_POST['id'];
$query="DELETE FROM tblCardex WHERE IdCardex='".$id."'";
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
 <div class='alert alert-danger' role='alert'>Cardex con folio <?php echo $id; ?>, eliminado.</div>