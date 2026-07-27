<?php
require_once '../../../csql.php';
 $fecha=$_POST['fecha'];
 $id=$_POST['idaccion'];
 $validador=$_POST['validador'];
 $fechaactual=date('Y/m/d');
 $valida=0;
 $query="SELECT * FROM tblCapaNuevafecha WHERE estado=0 AND idaccion=".$id; 
  $stmt = sqlsrv_query($conn,$query);
  while ($fila=sqlsrv_fetch_array($stmt)) {
    $valida=1;
    $nueva=$fila[2]->format("Y-m-d");
    $solicito=$fila[3]->format("Y-m-d");
  }
  if($valida==0){
$query="INSERT INTO tblCapaNuevafecha(idaccion,nuevafecha,fechasolicito,estado,valida) VALUES ('".$id."','".$fecha."','".$fechaactual."',0,'".$validador."')"; 
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
 <div class="alert alert-success">Solicitud enviada con éxito</div>
<?php  
}else
echo '<div class="alert alert-danger">Ya hay una solicitud creada para la acción: '.$id.' a la nueva fecha '.$nueva.' y fue solicitada el '.$solicito.' </div>';
?>