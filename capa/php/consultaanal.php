<?php 
require_once "../../../csql.php";
$folio=$_POST['id'];
$query="SELECT * FROM tblCapaAnalisis WHERE id =".$folio.";";
$result=sqlsrv_query($conn,$query);
while($fila=sqlsrv_fetch_array($result)){
     $id=$fila['id'];
    $elemento=$fila['elemento'];
    $porque1=$fila['porque1'];
    $porque2=$fila['porque2'];
    $porque3=$fila['porque3'];
    $porque4=$fila['porque4'];
    $porque5=$fila['porque5'];
    $causainm=$fila['causainm'];
    $proridad=$fila['proridad'];
    $raiz=$fila['raiz'];
}
 ?>
 <script type="text/javascript">
    $("#folioanal").val("<?php echo $id; ?>");
    $("#elemento").val("<?php echo $elemento; ?>").change();
    $("#1porque").val("<?php echo $porque1 ?>");
    $("#2porque").val("<?php echo $porque2; ?>");
    $("#3porque").val("<?php echo $porque3; ?>");
    $("#4porque").val("<?php echo $porque4; ?>");
    $("#5porque").val("<?php echo $porque5; ?>");
    $("#causaimediata").val("<?php echo $causainm; ?>").change();
    $("#prioridad").val("<?php echo $proridad; ?>").change();
    $("#causaraiz").val("<?php echo $raiz; ?>").change();
 </script>
