<?php 
require_once "../../../csql32.php";
$idcardexedit=$_POST['idcardexedit'];
$nomedit=$_POST['nomedit'];
$depsedit=$_POST['depsedit'];
$puestosedit=$_POST['puestosedit'];
$obsoletoedit=$_POST['obsoletoedit'];
$query="UPDATE tblCardex SET NombreCardex='".$nomedit."', NoDepto='".$depsedit."', CardexObsoleto='".$obsoletoedit."', idPuesto='".$puestosedit."' WHERE IdCardex='".$idcardexedit."'";
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
 <div class='alert alert-success' role='alert'>Cardex actualizado con éxito.</div>