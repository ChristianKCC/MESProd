<?php 
require_once "../../../csql.php";
$folio=$_POST['id'];
$query="SELECT * FROM tblCapaInforme WHERE id =".$folio.";";
$result=sqlsrv_query($conn,$query);
while($fila=sqlsrv_fetch_array($result)){
     $id=$fila['id'];
    $quesucedio=$fila['quesucedio'];
    $cuandosucedio=$fila['cuandosucedio']->format('Y-m-d');
    $comosucedio=$fila['comosucedio'];
    $porquesucedio=$fila['porquesucedio'];
    $dondesucedio=$fila['dondesucedio'];
    $quienoperaba=$fila['quienoperaba'];
    $cuantasvecespaso=$fila['cuantasvecespaso'];
    $confirmado=$fila['confirmado'];
    $descripcion=$fila['descripcion'];
    $archivo=$fila['archivo'];
}
 ?>
 <script type="text/javascript">
    $("#folioinv").val("<?php echo $id; ?>");
    $("#quesuc").val("<?php echo $quesucedio; ?>");
    $("#cuandosuc").val("<?php echo $cuandosucedio ?>");
    $("#comosuc").val("<?php echo $comosucedio; ?>");
    $("#porquesuc").val("<?php echo $porquesucedio; ?>");
    $("#dondesuc").val("<?php echo $dondesucedio; ?>");
    var conf= "<?php echo $confirmado; ?>";
    if(conf==1){
    $("#confirmado").prop('checked',true).change();
     $("#fileconfirmado").html("Ya hay un archivo cargado, para reemplazarlo vuelva a confirmar la investigación.<input type='hidden' id='valarch' value='1'>");
    }
    else
    $("#confirmado").prop('checked',false).change();
    $("#operabaempleados").val("<?php echo $quienoperaba; ?>").change();
    $("#cuantasveces").val("<?php echo $cuantasvecespaso; ?>");
    $("#descripcion").val("<?php echo $descripcion; ?>");

 </script>
