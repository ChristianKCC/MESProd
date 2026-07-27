<?php 	
if (isset($_GET['editenc'])) {
	require_once "../../../csql35.php";
	$id=$_POST['id'];
	$query="SELECT tblAOPOEEncIT.*,tblAOPOEinvoperaciones.procedimiento FROM tblAOPOEEncIT LEFT JOIN tblAOPOEinvoperaciones ON tblAOPOEEncIT.POE=tblAOPOEinvoperaciones.id WHERE tblAOPOEEncIT.id=$id";
	$result=sqlsrv_query($conn,$query);
	while($row=sqlsrv_fetch_array($result)){
			$id=$row[0];
			$noemp=$row[1];
			$departamento=$row[2];
			$maquina=$row[3];
			$poe=$row[12];
			$noempcap=$row[5];
			$fecha=$row[6]->format('Y-m-d');
			$minutos=$row[7];
			$tipo=$row[8];
			$observacion=$row[9];
			$motivo=$row[10];
			$puesto=$row[11];
	}
	?>
	<script type="text/javascript">
		$("#folio").val('<?php echo $id; ?>');
		$("#noemp").val('<?php echo $noemp; ?>');
		$("#departamento").val('<?php echo $departamento; ?>');
		$("#maquina").val('<?php echo $maquina; ?>');
		$("#POEID").val('<?php echo $poe; ?>');
		$("#noempcap").val('<?php echo $noempcap; ?>');
		$("#fecha").val('<?php echo $fecha; ?>');
		$("#minutos").val('<?php echo $minutos; ?>');
		$("#tipo").val('<?php echo $tipo; ?>').change();
		$("#motivo").val('<?php echo $motivo; ?>').change();
		$("#puesto").val('<?php echo $puesto; ?>').change();
		$("#minutos").val('<?php echo $minutos; ?>');
		$("#observacion").val('<?php echo $observacion; ?>');
		llnrdatoemp();
		llnrdatoempcap();
		POE();
		clasificacion();
	</script>
	<?php 	
}else if (isset($_GET['deleteenc'])) {
	require_once "../../../csql35.php";
	$id=$_POST['id'];
	$query="DELETE FROM tblAOPOEEncIT WHERE id=$id";
	sqlsrv_query($conn,$query);
	}
else if (isset($_GET['modenc'])) {
	require_once "../../../csql35.php";
	$folio=$_POST['folio'];
	$noemp=$_POST['noemp'];
	$departamento=$_POST['departamento'];
	$maquina=$_POST['maquina'];
	$poe=$_POST['poe'];
	$noempcap=$_POST['noempcap'];
	$fecha=$_POST['fecha'];
	$minutos=$_POST['minutos'];
	$tipo=$_POST['tipo'];
	$motivo=$_POST['motivo'];
	$observacion=$_POST['observacion'];
	$puesto=$_POST['puesto'];
	$query="UPDATE tblAOPOEEncIT SET noemp='".$noemp."',departamento='".$departamento."',puesto='".$puesto."',maquina='".$maquina."',POE='".$poe."',capacitador='".$noempcap."',
	fecha='".$fecha."',duracion='".$minutos."',tipo='".$tipo."',observacion='".$observacion."', motivo = '".$motivo."' WHERE id=$folio";
	$result = sqlsrv_query($conn,$query);
	if( $result === false ) {
    if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        		}
    		}
	}
	echo "Se actualizó correctamente el registro";
	}
 ?>