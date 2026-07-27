<?php 

if (isset($_GET['eliminacapa'])) {
require_once '../../../csql6.php';
 $id=$_POST['id'];
$query="DELETE FROM tblEncabezadoCapaweb WHERE FolioCapa=".$id; 
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
 <div class='alert alert-danger my-2' role='alert'>Análisis eliminado</div>
 <?php 
}

else if (isset($_GET['autorizacapa'])) {
require_once '../../../csql6.php';
require_once("../../Session/seguridad.php");
 $id=$_POST['id'];
$query="UPDATE tblEncabezadoCapaweb SET IdAutorizacion=1,Edicionterminada=1,AutorizadoPor='".$_SESSION['ibm']."',FechaAutorizacion=GETDATE() WHERE FolioCapa=".$id; 
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
}

else if (isset($_GET['devolvercapa'])) {
require_once '../../../csql6.php';
 $id=$_POST['id'];
$query="UPDATE tblEncabezadoCapaweb SET IdAutorizacion=0,Edicionterminada=0 WHERE FolioCapa=".$id; 
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
}

else if (isset($_GET['terminacapa'])) {
require_once '../../../csql6.php';
 $id=$_POST['id'];
 $ibm=$_POST['enviaaemp'];
 $correo='';
$query="UPDATE tblEncabezadoCapaweb SET Edicionterminada=1,Autorizador=$ibm WHERE FolioCapa=".$id; 
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
if($ibm=='46686')$correo='mauricioisrael.estrada@kcc.com';  
else if($ibm=='22622')$correo='eponce@kcc.com'; 
else if($ibm=='26543')$correo='Gustavo.Canela@kcc.com';
else if($ibm=='28026')$correo='Omar.Fuentes@kcc.com';
else if($ibm=='31578')$correo='Israel.Garcia@kcc.com';
else if($ibm=='33820')$correo='Luis.Liljehult@kcc.com';
else if($ibm=='37866')$correo='Eliher.Ortiz@kcc.com';
else if($ibm=='45045')$correo='RicardoFrancisco.Alcantara@kcc.com';
else if($ibm=='50756')$correo='marthaolivia.ramirez@kcc.com';
require '../mail/PHPMailer.php';
require '../mail/SMTP.php';
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->CharSet = 'UTF-8';
$mail->Host       = 'smtp.hostinger.com';
$mail->SMTPSecure = 'ssl';
$mail->Port       = 465;
$mail->SMTPDebug  = 4;
$mail->SMTPAuth   = true;
$mail->Username   = 'noreply@xenbookli.com';
$mail->Password   = 'Porta021.';
$mail->SetFrom('noreply@xenbookli.com', "Mes Prosede");
$mail->AddReplyTo('noreply@xenbookli.com','no-reply');
$mail->Subject    = 'Capa pendiente por autorizar';
$mail->MsgHTML("Se ha terminado de capturar una nueva capa y es necesario que la autorices por favor ingresa a el <a href='http://mes/dashboard/KCMes/index/index.php'>sistema mes</a> para su revisión");
$mail->AddAddress($correo, 'Destinatario');
$mail->send();
}

else if (isset($_GET['modcapaenc'])) {
require_once '../../../csql6.php';
require_once("../../Session/seguridad.php");
 $folio=$_POST['folio'];
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
$query="UPDATE tblEncabezadoCapaweb SET Fecha='".$fecha."',NoEmp='".$_SESSION['ibm']."',NoDepto='".$NoDepto."',NoMaquina='".$NoMaquina."',NoSeccion='".$NoSeccion."',IdFuente='".$IdFuente."',IdTipoFuente='".$IdTipoFuente."',DescripcionCAPA='".$descripcioncapa."',IdMCM='".$IdMCM."',Severidad='".$severidad."',Probabilidad='".$probabilidad."',Deteccion='".$deteccion."',Noexpuestas='".$noexpuestas."',Asignado='".$asignado."' WHERE FolioCapa='".$folio."'";
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
if($IdMCM==13){
$suma=$severidad+$probabilidad+$deteccion;
if($suma<=10){
 ?>
 <div class='alert alert-success' role='alert'>La desviación no representa un riesgo mayor o crítico, por lo que únicamente se deberá ejecutar la etapa de revisión inicial e investigación y la etapa de plan de correciones para eliminar el problema.</div>
<?php }else if($suma<=20){ ?>
   <div class='alert alert-warning text-dark' role='alert'>La desviación representa un riesgo mayor, por lo que deberá ejecutar todas las etapas del proceso CAPA.</div>
   <?php }else if($suma<=30){ ?> 
    <div class='alert alert-danger' role='alert'>La desviación representa un riesgo crítico, por lo que es mandatorio ejecutar todas las etapas del proceso CAPA.</div>
    <?php 
} 
}
}
else if (isset($_GET['deletecausas'])) {
require_once '../../../csql.php';
 $id=$_POST['id'];
$query="DELETE FROM tblCapaAnalisis WHERE id=".$id; 
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
 <div class='alert alert-danger my-2' role='alert'>Análisis eliminado</div>
<?php 
}

else if (isset($_GET['deleteinvestigacion'])) {

require_once '../../../csql.php';
 $id=$_POST['id'];
$query="DELETE FROM tblCapaInforme WHERE id=".$id; 
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
 <div class='alert alert-danger my-2' role='alert'>Investigación eliminada</div>

<?php
}

else if (isset($_GET['deleteacp'])) {
require_once '../../../csql.php';
 $id=$_POST['id'];
$query="DELETE FROM tblCapaAcciones WHERE id=".$id; 
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
 <div class='alert alert-danger my-2' role='alert'>Acción eliminada</div>
 <?php 
}


else if (isset($_GET['eliminaracpmenor'])) {
require_once '../../../csql.php';
 $id=$_POST['id'];
$query="DELETE FROM tblCapaAccionesMenor WHERE id=".$id; 
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
 <div class='alert alert-danger my-2' role='alert'>Acción eliminada</div>
<?php 
}



else if (isset($_GET['eficacia1'])) {
require_once '../../../csql.php';
$id=$_POST['id'];
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
$query="SELECT * FROM tblCapaEficacia WHERE id_actividad=$id";
$resultado = sqlsrv_query($conn,$query, $params, $options );
$row_count = sqlsrv_num_rows( $resultado );
if($row_count==14)
    echo '<div class="text-danger">No puedes calificar mas de 14 dias.</div>';
else{
 $id=$_POST['id'];
 $eficacia=$_POST['eficacia'];
$query="INSERT INTO tblCapaEficacia (id_actividad,eficacia) VALUES ($id,$eficacia)"; 
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
}
 ?>
<?php 
}


else if (isset($_GET['investigacionedit'])) {

require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
$folioinv=$_POST['folioinv'];
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
 
 if($confirmado==1){
 if(isset($_FILES['file']['name'])){
 $nombrepdf =  $idcapa."-".$_FILES['file']['name'];
 $archivo=$_FILES['file']['tmp_name'];
 $ruta="../Archivos/";
 $ruta=$ruta."".$nombrepdf;
 move_uploaded_file($archivo, $ruta);
 $query="UPDATE tblCapaInforme SET quesucedio='".$quesuc."',cuandosucedio='".$cuandosuc."',comosucedio='".$comosuc."',porquesucedio='".$porquesuc."',dondesucedio='".$dondesuc."',quienoperaba='".$operabaempleados."',cuantasvecespaso='".$cuantasveces."',confirmado='".$confirmado."',descripcion='".$descripcion."',archivo='".$ruta."' WHERE id=".$folioinv;
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
  echo "<div class='alert alert-success my-2' role='alert'>Se actualizó correctamente la infomación.</div>";
 }else
 echo "<div class='alert alert-danger my-2' role='alert'>Error en el archivo</div>";
}
else{
    $archivo=",archivo='' ";
    if($confirmado==2){
        $confirmado=1;
        $archivo='';
    }
    $query="UPDATE tblCapaInforme SET quesucedio='".$quesuc."',cuandosucedio='".$cuandosuc."',comosucedio='".$comosuc."',porquesucedio='".$porquesuc."',dondesucedio='".$dondesuc."',quienoperaba='".$operabaempleados."',cuantasvecespaso='".$cuantasveces."',confirmado='".$confirmado."',descripcion='".$descripcion."'".$archivo." WHERE id=".$folioinv;
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
echo "<div class='alert alert-success my-2' role='alert'>Se actualizó correctamente la infomación</div>";
}

}






else if (isset($_GET['causasedit'])) {
require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
 $folioanal=$_POST['folioanal'];
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


$query="UPDATE tblCapaAnalisis SET elemento='".$elemento."',porque1='".$porque1."',porque2='".$porque2."',porque3='".$porque3."',porque4='".$porque4."',porque5='".$porque5."',proridad='".$prioridad."',raiz='".$causaraiz."' WHERE id=$folioanal";
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
echo "<div class='alert alert-success my-2' role='alert'>Se actualizó correctamente la información</div>";
}





else if (isset($_GET['acpedit'])) {
require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
 $folioacp=$_POST['folioacp'];
 $causaraiz=$_POST['causaraiz'];
 $tipoaccionc=$_POST['tipoaccionc'];
 $responsable=$_POST['responsable'];
 $fechacompromiso=$_POST['fechacompromiso'];
 $actividad=$_POST['actividad'];
 $causaimediata=$_POST['causaimediata'];
 $actividad=str_replace('"', '', $actividad);
 $actividad=str_replace("'", '', $actividad);
$query="UPDATE tblCapaAcciones SET idcausas='".$causaraiz."',tipodeaccion='".$tipoaccionc."',actividad='".$actividad."',responsable='".$responsable."',fechadecompromiso='".$fechacompromiso."',causainm='".$causaimediata."' WHERE id=".$folioacp;
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
echo "<div class='alert alert-success my-2' role='alert'>Se actualizó correctamente la información</div>";
}
 ?>
