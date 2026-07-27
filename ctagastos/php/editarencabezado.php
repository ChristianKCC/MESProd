<?php
 include '../../../csql32.php';
 $folio=$_POST['folio'];
 $empleado=$_POST['empleado'];
 $ctrocostos=$_POST['ctrocostos'];
 $moneda=$_POST['moneda'];
$query="UPDATE tblCTAgastosEncabezadocta SET noemp=$empleado,ctrocostos=$ctrocostos,tipomoneda=$moneda WHERE id=$folio";
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
 <div class='alert alert-success' role='alert'>Edición exitosa.</div>
 <script type="text/javascript">
  $("#folio").val("<?php echo $id; ?>");
 </script>