<?php 
require_once "../../../csql32.php";
$nomedit=$_POST['nomedit'];
$depsedit=$_POST['depsedit'];
$puestos=$_POST['puestos'];
$obsoletoedit=$_POST['obsoletoedit'];
$query="INSERT INTO tblCardex (NombreCardex, NoDepto, CardexObsoleto, idPuesto) Values ('$nomedit','$depsedit','$obsoletoedit','$puestos')";
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
 <div class='alert alert-success' role='alert'>Nuevo cardex agregado con éxito.</div>