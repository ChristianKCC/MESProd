<?php 
require_once "../../Session/seguridad.php";
if(isset($_GET['enc'])){
	require_once "../../../csql.php";
	$turno=$_POST['turno'];
	$deps=$_POST['deps'];
	$query="INSERT INTO tblRepmtto(ibm,fecha,turno,terminado,departamento) VALUES ('".$_SESSION['ibm']."',GETDATE(),'".$turno."',0,'".$deps."')";
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
	echo "<div class='alert alert-success'>Reporte creado con exito</div>";
	sqlsrv_close($conn);
}


else if(isset($_GET['ri'])){
	require_once "../../../csql.php";
	$folio=$_POST['folio'];
	$noemp=$_POST['noemp'];
	$query="INSERT INTO tblRepmttori(folioenc,noemp) VALUES ('".$folio."','".$noemp."')";
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
	echo "<div class='alert alert-success'>Resgitro guardado con exito</div>";
	sqlsrv_close($conn);
}


else if(isset($_GET['guardarpm'])){
	require_once "../../../csql.php";
	$folio=$_POST['folio'];
	$maquinas=$_POST['maquinas'];
	$secciones=$_POST['secciones'];
	$descpend=$_POST['descpend'];
	$tipopendiente=$_POST['tipopendiente'];
	$query="INSERT INTO tblRepmttopmecanicos(folioenc,maquina,seccion,comentarios,tipopendiente) VALUES ('".$folio."','".$maquinas."','".$secciones."','".$descpend."','".$tipopendiente."')";
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
	echo "<div class='alert alert-success'>Resgitro guardado con exito</div>";
	sqlsrv_close($conn);
}


else if(isset($_GET['guardarcom'])){
	require_once "../../../csql.php";
	$folio=$_POST['folio'];
	$maquinas=$_POST['maquinas'];
	$descomentarios=$_POST['descomentarios'];
	$query="INSERT INTO tblRepmttocomentarios(folioenc,maquina,comentarios) VALUES ('".$folio."','".$maquinas."','".$descomentarios."')";
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
	echo "<div class='alert alert-success'>Resgitro guardado con exito</div>";
	sqlsrv_close($conn);
}


else if(isset($_GET['guardarparo'])){
	require_once "../../../csql.php";
	$folio=$_POST['folio'];
	$maquinas=$_POST['maquinas'];
	$secciones=$_POST['secciones'];
	$hparo=$_POST['hparo'];
	$tperdido=$_POST['tperdido'];
	$comentarios=$_POST['comentarios'];
	$query="INSERT INTO tblRepmttoparosmaquina(folioenc,maquina,seccion,hparo,tperdido,comentarios) VALUES ('".$folio."','".$maquinas."','".$secciones."','".$hparo."','".$tperdido."','".$comentarios."')";
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
	echo "<div class='alert alert-success'>Resgitro guardado con exito</div>";
	sqlsrv_close($conn);
}


 ?>
