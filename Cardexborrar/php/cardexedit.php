<?php 
require_once "../../../csql32.php";
$id=$_POST['id'];
$query="SELECT IdCardex, NombreCardex, NoDepto, CardexObsoleto, idPuesto FROM tblCardex WHERE IdCardex=".$id;
$resultado=sqlsrv_query($conn,$query);
while($linea=sqlsrv_fetch_array($resultado)){
$nombre=$linea['NombreCardex'];
$departamento=$linea['NoDepto'];
$puesto=$linea['idPuesto'];
$obsoleto=$linea['CardexObsoleto'];
}
 ?>
<script type="text/javascript">
	$("#resultedit").html("");
	$( document ).ready(function() {
    $('#cardexeditmodaledit').modal('toggle');
	$("#idcardexedit").val("<?php echo $id ?>");
	$("#nomedit").val("<?php echo $nombre ?>");
	$("#depsedit").val("<?php echo $departamento ?>").change();
	$("#puestosedit").val("<?php echo $puesto ?>").change();
	var validacheck= "<?php echo $obsoleto ?>";
	if(validacheck==1)
	$("#obsoletoedit").prop('checked', true);
	else
	$("#obsoletoedit").prop('checked', false);
	});
</script>