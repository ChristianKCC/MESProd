<?php 
class ClassConexion{
function conexion($database){
$serverName="172.26.24.101";
$conexionInfo= array("Database"=>$database,"UID"=>"Pra0mxpublic","PWD"=>"MxPra0202111P73","TrustServerCertificate"=>1,"CharacterSet"=>"UTF-8");
$conexion=sqlsrv_connect($serverName,$conexionInfo);
if($conexion){}
else{
	echo "Error en la conexion";
	die( print_r(sqlsrv_errors(), true));
}
return $conexion;
}
function validaquery($stmt){
	if( $stmt === false ) {
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				echo json_encode("SQLSTATE: ".$error[ 'SQLSTATE']."<br />".
				"code: ".$error[ 'code']."<br />".
				"message: ".$error[ 'message']."<br />");
			}
		}
	}else
	echo json_encode("ok");
}
function conexioniap(){
	$serverName="172.26.24.101";
	$conexionInfo= array("Database"=>"iap0mxdb","UID"=>"iap0mxpublic","PWD"=>"Mxiap202105P31","TrustServerCertificate"=>1,"CharacterSet"=>"UTF-8");
	$conexion=sqlsrv_connect($serverName,$conexionInfo);
	if($conexion){}
	else{
		echo "Error en la conexion";
		die( print_r(sqlsrv_errors(), true));
	}
	return $conexion;
}
}
?>
