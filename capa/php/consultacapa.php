<?php 
require_once "../../../csql6.php";
$folio=$_POST['id'];
$query="SELECT * FROM tblEncabezadoCapaweb WHERE FolioCapa =".$folio.";";
$result=sqlsrv_query($conn,$query);
while($fila=sqlsrv_fetch_array($result)){
    $FolioCapa=$fila['FolioCapa'];
    $Fecha=$fila['Fecha'];
    $NoDepto=$fila['NoDepto'];
    $NoMaquina=$fila['NoMaquina'];
    $NoSeccion=$fila['NoSeccion'];
    $IdFuente=$fila['IdFuente'];
    $IdTipoFuente=$fila['IdTipoFuente'];
    $IdMCM=$fila['IdMCM'];
    $DescripcionCAPA=$fila['DescripcionCAPA'];
    $Severidad=$fila['Severidad'];
    $Probabilidad=$fila['Probabilidad'];
    $Deteccion=$fila['Deteccion'];
    $Noexpuestas=$fila['Noexpuestas'];
    $Asignado=$fila['Asignado'];
}
 ?>
 <script type="text/javascript">
    $("#folio").val("<?php echo $FolioCapa; ?>");
    $("#NoDepto").val("<?php echo $NoDepto; ?>");
    $("#IdFuente").val("<?php echo $IdFuente; ?>");
    slctipofuente("<?php echo $IdTipoFuente; ?>");
    slcmaquina("<?php echo $NoMaquina; ?>","<?php echo $NoSeccion; ?>")
    $("#IdMCM").val("<?php echo $IdMCM; ?>");
    slcseveridad("<?php echo $Severidad; ?>");
    slcprobabilidad("<?php echo $Probabilidad; ?>");
    slcdeteccion("<?php echo $Deteccion; ?>");
    slcnumpersonas("<?php echo $Noexpuestas; ?>");
    $("#descripcioncapa").val("<?php echo $DescripcionCAPA; ?>");
    $("#asigusauariocapa").val("<?php echo $Asignado; ?>");
 </script>
