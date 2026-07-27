<?php 
if (isset($_GET['dtk'])) {
function DTK(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblDTKs ORDER BY DescripcionDTK ASC";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['IdDTK'].'>'.$row['DescripcionDTK'].'</option>';
  }
  return $listas;
}
echo DTK();
}
else if (isset($_GET['depto'])) {
function Departamento(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblDepartamentos WHERE Filtro=1 ORDER BY NombreDepto ASC";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['NoDepto'].'>'.$row['NombreDepto'].'</option>';
  }
  return $listas;
}
echo Departamento();
}else if (isset($_GET['fuente'])) {
function Fuente(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblFuentes ORDER BY DescripcionFuente ASC";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['IdFuente'].'>'.$row['DescripcionFuente'].'</option>';
  }
  return $listas;
}
echo Fuente();
}else if (isset($_GET['clariesgo'])) {
function ClaseRiesgo(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblClaseRiesgo ORDER BY TipoTiesgo ASC";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['IdClaseRiesgo'].'>'.$row['TipoRiesgocalidad'].'</option>';
  }
  return $listas;
}
echo ClaseRiesgo();
}else if (isset($_GET['tipocauda'])) {
function TiposCausas(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblTiposCausas ORDER BY DescripcionCausa ASC";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['IdTipoCausa'].'>'.$row['DescripcionCausa'].'</option>';
  }
  return $listas;
}
echo TiposCausas();
}else if (isset($_GET['emp'])) {
function Empleados(){ 
 include '../../../csql32.php';
  $query = "SELECT * FROM tblEmpleados ORDER BY Nombre ASC";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['NoEmp'].'>'.$row['Nombre'].'</option>';
  }
  return $listas;
}
echo Empleados();
}else if (isset($_GET['descausa'])) {
function DescripcionCausa(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblDescipcionCausas ORDER BY DescripcionCausa ASC";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['IdDescripcionCausa'].'>'.$row['DescripcionCausa'].'</option>';
  }
  return $listas;
}
echo DescripcionCausa();
}
else if (isset($_GET['mcm'])) {
function MCM(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblMCM ORDER BY MCM ASC";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['IdMCM'].'>'.$row['MCM'].'</option>';
  }
  return $listas;
}
echo MCM();
}

else if (isset($_GET['enviaaemp'])) {
function Empleados(){ 
 include '../../../csql32.php';
  $query = "SELECT NoEmp,Nombre,ApellidoPaterno,ApellidoMaterno,tblEmpleadosnvlautoriza.* FROM tblEmpleados INNER JOIN tblEmpleadosnvlautoriza ON tblEmpleadosnvlautoriza.ibm=tblEmpleados.NoEmp WHERE tblEmpleadosnvlautoriza.nvlautorizacapa>=1 ORDER BY Nombre ASC";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row['NoEmp'].'>'.$row['Nombre'].'</option>';
  }
  return $listas;
}
echo Empleados();
}

else if (isset($_GET['severidad'])) {
function severidad(){ 
 include '../../../csql.php';
  $id=$_POST['id'];
   if(isset($_POST['selected']))
  $select=$_POST['selected'];
  else
  $select='';
  $query = "SELECT tblCapaSeveridades.id,tblCapaSeveridades.nombre FROM tblCapaSeveridades INNER JOIN tblCapaSeveridadescombo on tblCapaSeveridadescombo.idseveridad= tblCapaSeveridades.id INNER JOIN tblMCM on tblMCM.IdMCM=tblCapaSeveridadescombo.idresponsabilidad WHERE tblMCM.IdMCM=$id";
    $listas = '<option value="">Selecciona una opción'.$select.'</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
     if($select==$row[0])
    $listas .= '<option value='.$row[0].' selected>'.$row[1].'</option>';
    else
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo severidad();
}
else if (isset($_GET['probabilidad'])) {
function probabilidad(){ 
 include '../../../csql.php';
  $id=$_POST['id'];
  if(isset($_POST['selected']))
  $select=$_POST['selected'];
  else
  $select='';
  $query = "SELECT tblCapaProbabilidades.id,tblCapaProbabilidades.nombre FROM tblCapaProbabilidades INNER JOIN tblCapaProbabilidadescombo on tblCapaProbabilidadescombo.idprobabilidades= tblCapaProbabilidades.id INNER JOIN tblMCM on tblMCM.IdMCM=tblCapaProbabilidadescombo.idresponsabilidad WHERE tblMCM.IdMCM=$id";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
     if($select==$row[0])
    $listas .= '<option value='.$row[0].' selected>'.$row[1].'</option>';
    else
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo probabilidad();
}
else if (isset($_GET['deteccion'])) {
function deteccion(){ 
 include '../../../csql.php';
  $id=$_POST['id'];
   if(isset($_POST['selected']))
  $select=$_POST['selected'];
  else
  $select='';
  $query = "SELECT tblCapaDetecciones.id,tblCapaDetecciones.nombre FROM tblCapaDetecciones INNER JOIN tblCapaDeteccionescombo on tblCapaDeteccionescombo.iddeteccion= tblCapaDetecciones.id INNER JOIN tblMCM on tblMCM.IdMCM=tblCapaDeteccionescombo.idresponsabilidad WHERE tblMCM.IdMCM=$id";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    if($select==$row[0])
    $listas .= '<option value='.$row[0].' selected>'.$row[1].'</option>';
    else
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo deteccion();
}
else if (isset($_GET['numpersonas'])) {
function personasexp(){ 
 include '../../../csql.php';
  if(isset($_POST['selected']))
  $select=$_POST['selected'];
  else
  $select='';
  $query = "SELECT * FROM tblCapaNoExpuestas ORDER BY id ASC";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
   if($select==$row[0])
    $listas .= '<option value='.$row[0].' selected>'.$row[1].'</option>';
    else
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo personasexp();
}

else if (isset($_GET['maquinas'])) {

function Maquinas(){ 
 include '../../../csql.php';
 $id=$_POST['id'];
  if(isset($_POST['selected']))
  $select=$_POST['selected'];
  else
  $select='';
  $query = "SELECT tblMaquinasCombo.NoMaquina, tblMaquinas.NombreMaquina FROM tblMaquinasCombo INNER JOIN tblMaquinas ON tblMaquinasCombo.NoMaquina = tblMaquinas.NoMaquina WHERE(tblMaquinasCombo.NoDepto = $id) ORDER BY tblMaquinas.NombreMaquina";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
  if($select==$row['NoMaquina'])
    $listas .= '<option value='.$row['NoMaquina'].' selected>'.$row['NombreMaquina'].'</option>';
  else
    $listas .= '<option value='.$row['NoMaquina'].'>'.$row['NombreMaquina'].'</option>';
  }
  return $listas;
}
echo Maquinas();
}

else if (isset($_GET['secciones'])) {
function Seccion(){ 
 include '../../../csql.php';
 $id=$_POST['id'];
  if(isset($_POST['selected']))
  $select=$_POST['selected'];
  else
  $select='';
  $query = "SELECT tblSeccionesCombo.NoSeccion, tblSecciones.NombreSeccion FROM tblSeccionesCombo INNER JOIN tblSecciones ON tblSeccionesCombo.NoSeccion = tblSecciones.NoSeccion WHERE(tblSeccionesCombo.NoMaquina = $id) AND (tblSecciones.SeccionDescontinuada = 'False') ORDER BY tblSecciones.NombreSeccion";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
  if($select==$row['NoSeccion'])
    $listas .= '<option value='.$row['NoSeccion'].' selected>'.$row['NombreSeccion'].'</option>';
  else
    $listas .= '<option value='.$row['NoSeccion'].'>'.$row['NombreSeccion'].'</option>';
  }
  return $listas;
}
echo Seccion();
}
else if (isset($_GET['tipofuente'])) {
function TipoFuente(){ 
 include '../../../csql.php';
  $id=$_POST['id'];
  if(isset($_POST['selected']))
  $select=$_POST['selected'];
  else
  $select='';
  $query = "SELECT * FROM tblTipoFuente INNER JOIN tblFuentesCombo on tblFuentesCombo.IdTipoFuente=tblTipoFuente.IdTipoFuente WHERE tblFuentesCombo.IdFuente=$id ORDER BY DescripcionTipoFuente ASC";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    if($select==$row["IdTipoFuente"])
    $listas .= '<option value='.$row['IdTipoFuente'].' selected>'.$row['DescripcionTipoFuente'].'</option>';
    else
    $listas .= '<option value='.$row['IdTipoFuente'].'>'.$row['DescripcionTipoFuente'].'</option>';
  }
  return $listas;
}
echo TipoFuente();
}

// analisis de causa
else if (isset($_GET['elemento'])) {
function Elementos(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblCapaElementos ORDER BY id ASC";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo Elementos();
}

else if (isset($_GET['practicas'])) {
function Practicas(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblCapaPracticasSub ORDER BY id ASC";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo Practicas();
}

else if (isset($_GET['prioridad'])) {
function Prioridad(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblCapaEfectDeseado ORDER BY id ASC";
  $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo Prioridad();
}

else if (isset($_GET['causaraiz'])) {
function Practicas(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblCapaCondicionesSubestandar ORDER BY id ASC";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo Practicas();
}

else if (isset($_GET['descripcionprio'])) {
function Prioridad(){ 
  if($_POST['id']==""){

  }else{
  include '../../../csql.php';
  $id=$_POST['id'];
  $query = "SELECT * FROM tblCapaEfectDeseado WHERE id=$id ORDER BY id ASC";
  $listas="";
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<p>'.$row[2].'</p>';
  }
  return $listas;
  }
}
echo Prioridad();
}

else if (isset($_GET['tipoacp'])) {
function tipoacp(){ 
 include '../../../csql.php';
  $query = "SELECT * FROM tblCapaTipoAccion";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].'</option>';
  }
  return $listas;
}
echo tipoacp();
}


else if (isset($_GET['atacarcausa'])) {
function atacarcausa(){ 
 include '../../../csql.php';
  $id=$_POST['id'];
  $query = "SELECT tblCapaAnalisis.id, tblCapaCondicionesSubestandar.condiciones_subestandar,tblCapaAnalisis.proridad, tblCapaElementos.elementos, tblCapaEfectDeseado.efecto_deseado FROM tblCapaAnalisis INNER JOIN tblCapaCondicionesSubestandar ON tblCapaCondicionesSubestandar.id=tblCapaAnalisis.raiz INNER JOIN  tblCapaElementos ON  tblCapaElementos.id=tblCapaAnalisis.elemento INNER JOIN tblCapaEfectDeseado ON tblCapaEfectDeseado.id=tblCapaAnalisis.proridad WHERE tblCapaAnalisis.idcapa=".$id;
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[3].', Causa raíz: '.$row[1].', Prioridad: '.$row[4].'</option>';
  }
  return $listas;
}
echo atacarcausa();
}


else if (isset($_GET['validadores'])) {
function validadores(){ 
 include '../../../csql.php';
 $idaccion=$_POST['idaccion'];
  $query = "SELECT tblCapaValidadores.ibm,TLX032MXDB.dbo.tblEmpleados.Nombre, tblMCM.MCM FROM tblCapaValidadores inner join TLX032MXDB.dbo.tblEmpleados on TLX032MXDB.dbo.tblEmpleados.NoEmp=tblCapaValidadores.ibm  INNER JOIN tblMCM ON tblMCM.IdMCM=tblCapaValidadores.idresponsabilidad WHERE tblCapaValidadores.idresponsabilidad=(SELECT TLX006MXDB.dbo.tblEncabezadoCapaweb.IdMCM FROM tblCapaAcciones INNER JOIN tblCapaAnalisis on tblCapaAnalisis.id=tblCapaAcciones.idcausas inner join TLX006MXDB.dbo.tblEncabezadoCapaweb on TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=tblCapaAnalisis.idcapa where tblCapaAcciones.id='".$idaccion."')";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].' - '.$row[2].'</option>';
  }
  return $listas;
}
echo validadores();
}

else if (isset($_GET['validadoresmenor'])) {
function validadores(){ 
 include '../../../csql.php';
 $idaccion=$_POST['idaccion'];
  $query = "SELECT tblCapaValidadores.ibm,TLX032MXDB.dbo.tblEmpleados.Nombre, tblMCM.MCM FROM tblCapaValidadores inner join TLX032MXDB.dbo.tblEmpleados on TLX032MXDB.dbo.tblEmpleados.NoEmp=tblCapaValidadores.ibm  INNER JOIN tblMCM ON tblMCM.IdMCM=tblCapaValidadores.idresponsabilidad WHERE tblCapaValidadores.idresponsabilidad=(SELECT TLX006MXDB.dbo.tblEncabezadoCapaweb.IdMCM FROM tblCapaAccionesMenor INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb on TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=tblCapaAccionesMenor.idcapa where tblCapaAccionesMenor.id='".$idaccion."')";
    $listas = '<option value="">Selecciona una opción</option>';
  $result = sqlsrv_query($conn, $query);
  while($row = sqlsrv_fetch_array($result)){
    $listas .= '<option value='.$row[0].'>'.$row[1].' - '.$row[2].'</option>';
  }
  return $listas;
}
echo validadores();
}

 ?>