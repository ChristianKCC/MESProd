<?php 
require_once "../../../csql.php";
$folio=$_POST['id'];
$query="SELECT * FROM tblCapaAcciones WHERE id =".$folio."";
$result=sqlsrv_query($conn,$query);
while($fila=sqlsrv_fetch_array($result)){
    $id=$fila['id'];
    $tipodeaccion=$fila['tipodeaccion'];
    $actividad=$fila['actividad'];
    $responsable=$fila['responsable'];
    $fechadecompromiso=$fila['fechadecompromiso']->format('Y-m-d');
    $idcausas=$fila['idcausas'];
    $causainm=$fila['causainm'];
}
 ?>
 <script type="text/javascript">
    $("#folioacp").val("<?php echo $id; ?>");
    $("#causaraiz").val("<?php echo $idcausas; ?>");
    $("#tipoaccionc").val("<?php echo $tipodeaccion ?>");
    $("#responsable").val("<?php echo $responsable; ?>");
    $("#fechacompromiso").val("<?php echo $fechadecompromiso; ?>");
    $("#actividad").val("<?php echo $actividad; ?>");
    $("#causaimediata").val("<?php echo $causainm; ?>");
 </script>
