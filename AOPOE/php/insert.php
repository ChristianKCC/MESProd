<?php 
if(isset($_GET['enc'])){
	require_once "../../../csql35.php";
	$noemp=$_POST['noemp'];
	$departamento=$_POST['departamento'];
	$maquina=$_POST['maquina'];
	$poe=$_POST['poe'];
	$noempcap=$_POST['noempcap'];
	$fecha=$_POST['fecha'];
	$minutos=$_POST['minutos'];
	$tipo=$_POST['tipo'];
	$puesto=$_POST['puesto'];
	$motivo=$_POST['motivo'];
	$observacion=$_POST['observacion'];
	$query="INSERT INTO tblAOPOEEncIT(noemp,departamento,maquina,POE,capacitador,fecha,duracion,tipo,observacion,motivo,puesto) VALUES 
	('".$noemp."','".$departamento."','".$maquina."','".$poe."','".$noempcap."','".$fecha."','".$minutos."','".$tipo."','".$observacion."','".$motivo."','".$puesto."')";
	$result = sqlsrv_query($conn,$query);
	if( $result === false ) {
    if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        		}
    		}
	}
	echo "Se guardó correctamente el registro";
}
 ?>
