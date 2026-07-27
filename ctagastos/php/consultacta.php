<?php 
require_once "../../../csql32.php";
$folio=$_POST['id'];
$query="SELECT * FROM tblCTAgastosEncabezadocta WHERE id =".$folio.";";
$result=sqlsrv_query($conn,$query);
while($fila=sqlsrv_fetch_array($result)){
    $id=$fila['id'];
    $ctrocostos=$fila['ctrocostos'];
    $moneda=$fila['tipomoneda'];
}
 ?>
 <script type="text/javascript">
   var checkmoneda= "<?php echo $moneda; ?>";
    $("#folio").val("<?php echo $id; ?>");
    $("#ctrocostos").val("<?php echo $ctrocostos; ?>").change();
    if(checkmoneda != 1)
     $("#extranjero").prop('checked',true).change();
    else 
      $("#extranjero").prop('checked',false).change();
    $("#moneda").val("<?php echo $moneda; ?>").change();
   tblconseptos();
 </script>
