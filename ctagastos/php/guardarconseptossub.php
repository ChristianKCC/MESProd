<?php
 include '../../../csql32.php';
 $folio=$_POST['folio'];
 $consepto=$_POST['conseptos'];
 $importe=$_POST['importe'];
 $iva=$_POST['iva'];
 $xml=$_POST['conseptos'];
 $observaciones=$_POST['observaciones'];
 $fecha=$_POST['fecha'];
 $nombreimg =  $folio."-".$_FILES['file']['name'];
 $archivo=$_FILES['file']['tmp_name'];
 $ruta="../Archivos/";
$ruta=$ruta."".$nombreimg;
move_uploaded_file($archivo, $ruta);
$query="INSERT INTO tblCTAgastosSubencta (folio,idconsepto,importe,iva,xml,observaciones,fecha,archivoxml) VALUES (".$folio.",".$consepto.",".$importe.",".$iva.",".$xml.",'".$observaciones."','".$fecha."','".$ruta."')";
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
 if(isset($_POST['km']) AND isset($_POST['gasolina'])){
     $km=$_POST['km'];
     $gasolina=$_POST['gasolina'];
      $query= sqlsrv_query($conn,"SELECT @@identity AS id");
       if ($row = sqlsrv_fetch_array($query)) 
       {
         $id = trim($row[0]);
       }
       $query="INSERT INTO tblCTAgastoskmgasolina (km,gasolina,idconsepto) VALUES ('".$km."','".$gasolina."','".$id."')";
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
    }
 ?>
 <div class='alert alert-success my-2' role='alert'>Se generó correctamente tu concepto.</div>
 <script type="text/javascript">
    $("#resultadoenc").html('');
 </script>
