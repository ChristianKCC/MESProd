<?php
 include '../../../csql32.php';
 $empleado=$_POST['empleado'];
 $ctrocostos=$_POST['ctrocostos'];
 $moneda=$_POST['moneda'];
$query="INSERT INTO tblCTAgastosEncabezadocta (noemp,ctrocostos,tipomoneda,fecha,estado) VALUES (".$empleado.",".$ctrocostos.",".$moneda.",GETDATE(),1)";
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
  $query= sqlsrv_query($conn,"SELECT @@identity AS id");
   if ($row = sqlsrv_fetch_array($query)) 
   {
     $id = trim($row[0]);
   }

 ?>
 <div class='alert alert-success' role='alert'>Se generó correctamente tu cuenta.</div>
 <script type="text/javascript">
  $("#folio").val("<?php echo $id; ?>");
 </script>