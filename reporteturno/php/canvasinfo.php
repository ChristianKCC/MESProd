<?php 		
include "../../../csql.php";
$query=("SELECT COUNT(*) FROM tblRepmtto WHERE terminado=1 ");
$resultado = sqlsrv_query($conn,$query);
$x=0;
$dir = array();
while($row=sqlsrv_fetch_array($resultado)){
$dir[$x]=array("Cont"=>$row[0],"x"=>"Total Reportes");
$x++;
}
echo json_encode($dir);

?>