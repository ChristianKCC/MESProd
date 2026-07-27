<?php
require_once '../../../csql6.php';
require_once("../../Session/seguridad.php");
if(isset($_GET['encabezado'])){
 $fecha=$_POST['fecha'];
 $NoDepto=$_POST['NoDepto'];
 $NoMaquina=$_POST['NoMaquina'];
 $NoSeccion=$_POST['NoSeccion'];
 $IdMCM=$_POST['IdMCM'];
 $IdFuente=$_POST['IdFuente'];
 $IdTipoFuente=$_POST['IdTipoFuente'];
 $severidad=$_POST['severidad'];
 $probabilidad=$_POST['probabilidad'];
 $deteccion=$_POST['deteccion'];
 $descripcioncapa=$_POST['descripcioncapa'];
 $noexpuestas=$_POST['noexpuestas'];
 $asignado=$_POST['asignado'];
 $descripcioncapa=str_replace('"', '', $descripcioncapa);
 $descripcioncapa=str_replace("'", '', $descripcioncapa);
$query="INSERT INTO tblEncabezadoCapaweb (Fecha,NoEmp,NoDepto,NoMaquina,NoSeccion,IdFuente,IdTipoFuente,DescripcionCAPA,IdMCM,Severidad,Probabilidad,Deteccion,IdAutorizacion,Edicionterminada,Noexpuestas,Asignado) VALUES ('".$fecha."','".$_SESSION['ibm']."','".$NoDepto."','".$NoMaquina."','".$NoSeccion."','".$IdFuente."','".$IdTipoFuente."','".$descripcioncapa."','".$IdMCM."','".$severidad."','".$probabilidad."','".$deteccion."','0',0,'".$noexpuestas."','".$asignado."')";
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
$query= sqlsrv_query($conn,"SELECT @@identity AS id");
     if ($row = sqlsrv_fetch_array($query)) 
     {
       $id = trim($row[0]);
     }
require_once '../../../csql6.php';   
$cal="SELECT TLX009MXDB.dbo.tblCapaSeveridades.riesgo AS severidad,TLX009MXDB.dbo.tblCapaProbabilidades.riesgo AS probabilidad,TLX009MXDB.dbo.tblCapaDetecciones.riesgo AS deteccion,TLX009MXDB.dbo.tblCapaNoExpuestas.riesgo AS noexpuestas FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblCapaSeveridades ON TLX009MXDB.dbo.tblCapaSeveridades.id=tblEncabezadoCapaweb.Severidad INNER JOIN TLX009MXDB.dbo.tblCapaProbabilidades ON TLX009MXDB.dbo.tblCapaProbabilidades.id=tblEncabezadoCapaweb.Probabilidad INNER JOIN TLX009MXDB.dbo.tblCapaDetecciones ON TLX009MXDB.dbo.tblCapaDetecciones.id=tblEncabezadoCapaweb.Deteccion INNER JOIN TLX009MXDB.dbo.tblCapaNoExpuestas ON TLX009MXDB.dbo.tblCapaNoExpuestas.id=tblEncabezadoCapaweb.Noexpuestas where tblEncabezadoCAPAweb.FolioCapa=$id";
    $querycal= sqlsrv_query($conn,$cal);
     if( $querycal === false ) {
    if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
}
while($rescal=sqlsrv_fetch_array($querycal)){
if($IdMCM==13){
$suma=$rescal[0]+$rescal[1]+$rescal[2];
if($suma<=10){
 ?>
 <div class='alert alert-success' role='alert'>La desviación no representa un riesgo mayor o crítico, por lo que únicamente se deberá ejecutar la etapa de revisión inicial e investigación y la etapa de plan de correciones para eliminar el problema.</div>
<?php }else if($suma<=20){ ?>
   <div class='alert alert-warning text-dark' role='alert'>La desviación representa un riesgo mayor, por lo que deberá ejecutar todas las etapas del proceso CAPA.</div>
   <?php }else if($suma<=30){ ?> 
    <div class='alert alert-danger' role='alert'>La desviación representa un riesgo crítico, por lo que es mandatorio ejecutar todas las etapas del proceso CAPA.</div>
    <?php } 
    echo "El folio es: ".$id;
}else if($IdMCM==11){
$suma=$rescal[0]*$rescal[1]*$rescal[2]*$rescal[3];
if($suma<=5){
?>
 <div class='alert alert-success' role='alert'>Riesgo aceptable.<a href="capainforme.php" class="text-decoration-underline">Ve a los informes para revisar</a></div>
 <?php 
}
else if($suma<=50){
?>
 <div class='alert alert-warning' role='alert'>Riesgo bajo.<a href="capainforme.php" class="text-decoration-underline">Ve a los informes para revisar</a></div>
 <?php 
}
else if($suma<=500){
?>
 <div class='alert alert-success' role='alert'>Riesgo alto.<a href="capainforme.php" class="text-decoration-underline">Ve a los informes para revisar</a></div>
 <?php 
}else if($suma>500){
?>
 <div class='alert alert-success' role='alert'>Riesgo inaceptable.<a href="capainforme.php" class="text-decoration-underline">Ve a los informes para revisar</a></div>
 <?php 
}
}
}
}else if(isset($_GET['investigacion'])){
require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
 $idcapa=$_POST['idcapa'];
 $quesuc=$_POST['quesuc'];
 $cuandosuc=$_POST['cuandosuc'];
 $comosuc=$_POST['comosuc'];
 $porquesuc=$_POST['porquesuc'];
 $dondesuc=$_POST['dondesuc'];
 $operabaempleados=$_POST['operabaempleados'];
 $cuantasveces=$_POST['cuantasveces'];
 $confirmado=$_POST['confirmado'];
 $descripcion=$_POST['descripcion'];
 $descripcion=str_replace('"', '', $descripcion);
 $descripcion=str_replace("'", '', $descripcion);
  $queryan="SELECT * FROM tblCapaInforme WHERE idcapa=$idcapa";
  $resultan=sqlsrv_query($conn,$queryan);
  $contan=0;
  while($rowinv=sqlsrv_fetch_array($resultan)){
    $contan=1;
  }
 if($contan==1){
 echo "<div class='alert alert-danger my-2' role='alert'>Ya existe una investigación</div>";
 }
 else{ 
 if($confirmado==1){
 if(isset($_FILES['file']['name'])){
 $nombrepdf =  $idcapa."-".$_FILES['file']['name'];
 $archivo=$_FILES['file']['tmp_name'];
 $ruta="../Archivos/";
 $ruta=$ruta."".$nombrepdf;
 move_uploaded_file($archivo, $ruta);
 $query="INSERT INTO tblCapaInforme (idcapa,quesucedio,cuandosucedio,comosucedio,porquesucedio,dondesucedio,quienoperaba,cuantasvecespaso,confirmado,descripcion,archivo) VALUES ('".$idcapa."','".$quesuc."','".$cuandosuc."','".$comosuc."','".$porquesuc."','".$dondesuc."','".$operabaempleados."','".$cuantasveces."','".$confirmado."','".$descripcion."','".$ruta."')";
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
  echo "<div class='alert alert-success my-2' role='alert'>Se agregó correctamente la investigación</div>";
 }else
 echo "<div class='alert alert-danger my-2' role='alert'>Error en el archivo</div>";
}
else{
$query="INSERT INTO tblCapaInforme (idcapa,quesucedio,cuandosucedio,comosucedio,porquesucedio,dondesucedio,quienoperaba,cuantasvecespaso,confirmado,descripcion) VALUES ('".$idcapa."','".$quesuc."','".$cuandosuc."','".$comosuc."','".$porquesuc."','".$dondesuc."','".$operabaempleados."','".$cuantasveces."','".$confirmado."','".$descripcion."')";
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
echo "<div class='alert alert-success my-2' role='alert'>Se agregó correctamente el informe</div>";
}
}
}
else if(isset($_GET['causas'])){
require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
 $idcapa=$_POST['idcapa'];
 $elemento=$_POST['elemento'];
 $porque1=$_POST['1porque'];
 $porque2=$_POST['2porque'];
 $porque3=$_POST['3porque'];
 $porque4=$_POST['4porque'];
 $porque5=$_POST['5porque'];
 $prioridad=$_POST['prioridad'];
 $causaraiz=$_POST['causaraiz'];
 
 $porque1=str_replace('"', '', $porque1);
 $porque1=str_replace("'", '', $porque1);
 $porque2=str_replace('"', '', $porque2);
 $porque2=str_replace("'", '', $porque2);
 $porque3=str_replace('"', '', $porque3);
 $porque3=str_replace("'", '', $porque3);
 $porque4=str_replace('"', '', $porque4);
 $porque4=str_replace("'", '', $porque4);
 $porque5=str_replace('"', '', $porque5);
 $porque5=str_replace("'", '', $porque5);


$query="INSERT INTO tblCapaAnalisis (idcapa,elemento,porque1,porque2,porque3,porque4,porque5,proridad,raiz) VALUES ('".$idcapa."','".$elemento."','".$porque1."','".$porque2."','".$porque3."','".$porque4."','".$porque5."','".$prioridad."','".$causaraiz."')";
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
 <div class='alert alert-success my-2' role='alert'>Se agregó correctamente la causa</div>
<?php 
}


else if(isset($_GET['acp'])){
require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
 $causaraiz=$_POST['causaraiz'];
 $tipoaccionc=$_POST['tipoaccionc'];
 $responsable=$_POST['responsable'];
 $fechacompromiso=$_POST['fechacompromiso'];
 $actividad=$_POST['actividad'];
 $causaimediata=$_POST['causaimediata'];
 $actividad=str_replace('"', '', $actividad);
 $actividad=str_replace("'", '', $actividad);
$query="INSERT INTO tblCapaAcciones (idcausas,tipodeaccion,actividad,responsable,fechadecompromiso,accioncompleta,causainm) VALUES ('".$causaraiz."','".$tipoaccionc."','".$actividad."','".$responsable."','".$fechacompromiso."','0','".$causaimediata."')";
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
 <div class='alert alert-success my-2' role='alert'>Se agregó correctamente la acción</div>
<?php 
}


else if(isset($_GET['acpmenor'])){
require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
 $causaraiz=$_POST['idcapa'];
 $tipoaccionc=$_POST['tipoaccionc'];
 $responsable=$_POST['responsable'];
 $fechacompromiso=$_POST['fechacompromiso'];
 $actividad=$_POST['actividad'];
$query="INSERT INTO tblCapaAccionesmenor (idcapa,tipodeaccion,actividad,responsable,fechadecompromiso,accioncompleta) VALUES ('".$causaraiz."','".$tipoaccionc."','".$actividad."','".$responsable."','".$fechacompromiso."','0')";
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
 <div class='alert alert-success my-2' role='alert'>Se agregó correctamente la acción</div>
<?php 
}
?>
