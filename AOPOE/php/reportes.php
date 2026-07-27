<?php 
if(isset($_GET['date'])){
 include '../../../csql35.php';
 $fechaswhere='';
 $tipoadd='';
 $departamentoadd='';
 $nomidpoedd='';
 $puestoedd='';
 $whereandor='WHERE';
 if (!empty($_POST['fechai']) && !empty($_POST['fechaf'])) {
 $fechai=$_POST['fechai'];
 $fechaf=$_POST['fechaf'];
 $fechaswhere=" $whereandor tblAOPOEEncIT.fecha >= '$fechai' AND tblAOPOEEncIT.fecha < DATEADD(day,1,'$fechaf')";
 $whereandor="AND";
 }if(!empty($_POST['tipo'])){
 $tipo=$_POST['tipo'];
 $tipoadd="$whereandor tblAOPOEEncIT.tipo=$tipo";
 $whereandor="AND";
 }if(!empty($_POST['departamento'])){
 $departamento=$_POST['departamento'];
 $departamentoadd="$whereandor tblAOPOEEncIT.departamento=$departamento";
 $whereandor="AND";
 }if(!empty($_POST['puesto'])){
 $puesto=$_POST['puesto'];
 $puestoedd="$whereandor tblEmpleados.puesto=$puesto";
 $whereandor="AND";
 }if(!empty($_POST['nomidpoe'])){
 $nomidpoe=$_POST['nomidpoe'];
 $nomidpoedd="$whereandor (tblAOPOEinvoperaciones.procedimiento like '%".$nomidpoe."%' OR tblEmpleados.Nombre like '%".$nomidpoe."%' OR tblEmpleados.NoEmp like '%".$nomidpoe."%')";
 $whereandor="AND";
 }
 ?>
<div class="table-responsive" style="height:600px">
<table class="table table-hover table-sm">
  <thead class="table-dark">
      <th>Folio</th>
      <th>No Emp</th>
      <th>Nombre</th>
      <th>Departamento</th>
      <th>Máquina</th>
      <th>AOPOE</th>
      <th>Procedimiento</th>
      <th>Criticidad</th>
      <th>IBM</th>
      <th>Capacitador</th>
      <th>Fecha</th>
      <th>Duración</th>
      <th>Tipo</th>
      <th>Observaciones</th>
    </thead>
    <tbody>
  <?php 
  $cont=0;
  $query = "SELECT  tblAOPOEEncIT.id,tblAOPOEEncIT.noemp as IBM,TLX032MXDB.dbo.tblEmpleados.Nombre,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,
TLX009MXDB.dbo.tblMaquinas.NombreMaquina,tblAOPOEinvoperaciones.procedimiento,tblAOPOEinvoperaciones.tipoactividad,tblAOPOEclasif.nombre as Criticidad,tblemp2.NoEmp as IBMCap,tblemp2.Nombre as Capacitador,
tblAOPOEEncIT.fecha,tblAOPOEEncIT.duracion as Duración,tblAOPOEtipo.nombre as Tipo,tblAOPOEEncIT.observacion as Observaciones
 FROM tblAOPOEEncIT inner join TLX032MXDB.dbo.tblEmpleados on tblEmpleados.NoEmp=tblAOPOEEncIT.noemp
 inner join TLX009MXDB.dbo.tblDepartamentos on tblDepartamentos.NoDepto=tblAOPOEEncIT.departamento
 inner join TLX009MXDB.dbo.tblMaquinas on tblMaquinas.NoMaquina=tblAOPOEEncIT.maquina
 inner join tblAOPOEinvoperaciones on tblAOPOEinvoperaciones.id=tblAOPOEEncIT.POE
 inner join TLX032MXDB.dbo.tblEmpleados as tblemp2 on tblemp2.NoEmp=tblAOPOEEncIT.capacitador
 inner join tblAOPOEclasif on tblAOPOEclasif.id=tblAOPOEinvoperaciones.critico
 inner join tblAOPOEtipo on tblAOPOEtipo.id=tblAOPOEEncIT.tipo $fechaswhere $tipoadd $departamentoadd $nomidpoedd $puestoedd";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    echo "<tr><td>".$row[0]." </td>";
    echo "<td>".$row[1]." </td>";
    echo "<td>".$row[2]." </td>";
    echo "<td>".$row[3]." </td>";
    echo "<td>".$row[4]." </td>";
    echo "<td>".$row[5]." </td>";
    echo "<td>".$row[6]." </td>";
    echo "<td>".$row[7]." </td>";
    echo "<td>".$row[8]." </td>";
    echo "<td>".$row[9]." </td>";
    echo "<td>".$row[10]->format('Y-m-d')."</td>";
    echo "<td>".$row[11]." </td>";
    echo "<td>".$row[12]." </td>";
    echo "<td>".$row[13]." </td></tr>";
   }
?>
</tbody>
</table>
</div>
 <a href="pdf/crearpdf.php?query=<?php echo base64_encode($query); ?>" target="_blank" class="btn btn-danger mb-2">Generar pdf</a>
<?php 
}