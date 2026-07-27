<?php
require_once "../../../csql6.php";
$id=$_POST['id']; 
$contaccionescomp=0;
$contaccionestotal=0;
$query="SELECT tblEncabezadoCAPAweb.FolioCapa,tblEncabezadoCAPAweb.Fecha,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,TLX009MXDB.dbo.tblSecciones.NombreSeccion,TLX009MXDB.dbo.tblFuentes.DescripcionFuente,TLX009MXDB.dbo.tblTipoFuente.DescripcionTipoFuente,TLX009MXDB.dbo.tblMCM.MCM,tblEncabezadoCAPAweb.DescripcionCAPA,TLX009MXDB.dbo.tblCapaSeveridades.riesgo AS severidad,TLX009MXDB.dbo.tblCapaProbabilidades.riesgo AS probabilidad,TLX009MXDB.dbo.tblCapaDetecciones.riesgo AS deteccion,TLX009MXDB.dbo.tblCapaNoExpuestas.riesgo AS noexpuestas,tblEncabezadoCAPAweb.Noemp AS NoEmp FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEncabezadoCAPAweb.NoDepto INNER JOIN TLX009MXDB.dbo.tblSecciones on TLX009MXDB.dbo.tblSecciones.NoSeccion=tblEncabezadoCapaweb.NoSeccion INNER JOIN TLX009MXDB.dbo.tblMaquinas on TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblEncabezadoCapaweb.NoMaquina INNER JOIN TLX009MXDB.dbo.tblFuentes ON TLX009MXDB.dbo.tblFuentes.IdFuente = tblEncabezadoCapaweb.IdFuente INNER JOIN TLX009MXDB.dbo.tblTipoFuente ON TLX009MXDB.dbo.tblTipoFuente.IdTipoFuente=tblEncabezadoCapaweb.IdTipoFuente  INNER JOIN TLX009MXDB.dbo.tblMCM ON TLX009MXDB.dbo.tblMCM.IdMCM= tblEncabezadoCapaweb.IdMCM INNER JOIN TLX009MXDB.dbo.tblCapaSeveridades ON TLX009MXDB.dbo.tblCapaSeveridades.id=tblEncabezadoCapaweb.Severidad INNER JOIN TLX009MXDB.dbo.tblCapaProbabilidades ON TLX009MXDB.dbo.tblCapaProbabilidades.id=tblEncabezadoCapaweb.Probabilidad INNER JOIN TLX009MXDB.dbo.tblCapaDetecciones ON TLX009MXDB.dbo.tblCapaDetecciones.id=tblEncabezadoCapaweb.Deteccion INNER JOIN TLX009MXDB.dbo.tblCapaNoExpuestas ON TLX009MXDB.dbo.tblCapaNoExpuestas.id=tblEncabezadoCapaweb.Noexpuestas WHERE tblEncabezadoCAPAweb.FolioCapa = '".$id."' ORDER BY tblEncabezadoCAPAweb.FolioCapa DESC";
		$result=sqlsrv_query($conn,$query);
 ?>
  <div class="row justify-content-end">
 <div class="col-6">
 <h4>CAPA</h4>
 </div>
 <div class="col-1 ">
 <button type="button" class="btn btn-secondary btn-sm ml-auto" style="float: right; position: fixed;" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Salir</button>
  </div>
 </div>
 <hr>
 <div class="row">
 	<div class="col-4">
            <?php
      while ($fila=sqlsrv_fetch_array($result)) {
          echo " <h5 >Folio:".$fila[0]."</h5>";
          echo "<span>Cargada por: ".$fila['NoEmp']."</span><br>";
          echo "<span>Departamento: ".$fila[2]."</span><br>";
          echo "<span>Máquina: ".$fila[3]."</span><br>";
          echo "<span>Sección: ".$fila[4]."</span><br>";
          echo "<span>Fuente: ".$fila[5]."</span><br>";
          echo "<span>Tipo de fuente: ".$fila[6]."</span><br>";
          echo "<span>MCM: ".$fila[7]."</span><br>";
          echo "<span >Descipción CAPA: ".$fila[8]."</span><br>";
          if($fila[7]=='Seguridad'){
          	 $suma=$fila[9]*$fila[10]*$fila[11]*$fila[12];
              if($suma<=5){
              echo '<p class="text-success">Riesgo aceptable.</p>';
              }else if($suma<=50){
              echo '<p class="text-warning">Riesgo bajo.</p>';
              }else if($suma<=500){
              echo '<p class="text-danger">Riesgo alto.</p>';
              }else if($suma>500){
              echo '<p class="text-danger">Riesgo inaceptable.</p>';
              }
          }else if($fila[7]=='Calidad'){
          echo "<p class='text-danger'>La desviación representa un riesgo crítico, por lo que es mandatorio ejecutar todas las etapas del proceso CAPA</p>";
        }
      	}
      	?>
      </div>
<div class="col-2">
	<h6>Efectividad</h6>
<?php 
require_once "../../../csql.php";
$query="SELECT * FROM tblCapaefectividad WHERE idcapa=$id";
$resultado=sqlsrv_query($conn,$query);
$porcentaje=0;
$acumulado=0;
$cont=0;
while ($row=sqlsrv_fetch_array($resultado)) {
   $acumulado=$acumulado+$row[2];
   $cont++;
}
if ($cont>0) {
$porcentaje=$acumulado/$cont;
}
?>
<div class="col-6 p-5 text-center">
    <div class="m-0 row justify-content-center align-items-center">
    <h1><?php echo round($porcentaje,2)."%"; ?></h1>
    </div>
</div>
</div>
<div class="col-2">
	<h6>Eficacia</h6>
<?php 
require_once '../../../csql.php';
$id=$_POST['id'];
$dias=0;
$porcentaje=0;
$query="SELECT tblCapaEficacia.* FROM tblCapaEficacia INNER JOIN tblCapaAcciones ON tblCapaAcciones.id=tblCapaEficacia.id_actividad INNER JOIN tblCapaAnalisis ON tblCapaAnalisis.id=tblCapaAcciones.idcausas INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb ON tblEncabezadoCapaweb.FolioCapa= tblCapaAnalisis.idcapa WHERE tblEncabezadoCapaweb.FolioCapa=$id";
$resultado = sqlsrv_query($conn,$query);
    while ($row=sqlsrv_fetch_array($resultado)) {
      $dias++;
      if($row[2]==1){
       $porcentaje++;
      }
      }
      if($dias>0)
      $porcentaje=($porcentaje/$dias)*100;
       ?>
<div class="col-6 p-5 text-center">
    <div class="m-0 row justify-content-center align-items-center">
    <h1><?php echo round($porcentaje,2)."%"; ?></h1>
    </div>
</div>
</div>
 	<div class="col-4">
 		<?php
 		require_once "../../../csql.php";
          $queryinfo="SELECT tblCapaInforme.id,tblCapaInforme.idcapa,tblCapaInforme.quesucedio,tblCapaInforme.cuandosucedio,tblCapaInforme.comosucedio,tblCapaInforme.porquesucedio,
tblCapaInforme.dondesucedio,tblCapaInforme.quienoperaba ,TLX032MXDB.dbo.tblEmpleados.Nombre,tblCapaInforme.cuantasvecespaso, tblCapaInforme.confirmado,tblCapaInforme.descripcion FROM tblCapaInforme
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON TLX032MXDB.dbo.tblEmpleados.NoEmp=tblCapaInforme.quienoperaba WHERE tblCapaInforme.idcapa=$id";
          $resultinfo=sqlsrv_query($conn,$queryinfo);
          	echo "<h5>Investigación</h5>";
          	$confirmado="";
		      while ($filainfo=sqlsrv_fetch_array($resultinfo)) {
		      		if($filainfo[10]==0)$confirmado="NO"; else $confirmado="SI";
		      		echo "<span>¿Qué sucedió? ".$filainfo[2]."</span><br>";
		      		echo "<span>¿Cuándo sucedió? ".$filainfo[3]->format('Y/m/d')."</span><br>";
		      		echo "<span>¿Cómo sucedió? ".$filainfo[4]."</span><br>";
		      		echo "<span>¿Por qué sucedió? ".$filainfo[5]."</span><br>";
		      		echo "<span>¿Dónde sucedió? ".$filainfo[6]."</span><br>";
		      		echo "<span>¿Quién operaba? ".$filainfo[7]." - ".$filainfo[8]."</span><br>";
		      		echo "<span>¿Cuántas veces pasó? ".$filainfo[9]."</span><br>";
		      		echo "<span>¿Confirmado? ".$confirmado."</span><br>";
		      		echo "<span>Descripción: ".$filainfo[11]."</span><br>";
		      }

       ?>
   </div>
 </div>
 <hr class='m-2 bg-primary'>
    		<div class="row">
 		<?php
 		require_once "../../../csql.php";
          $querycausa="SELECT tblCapaAnalisis.id,tblCapaAnalisis.idcapa,tblCapaElementos.elementos,tblCapaAnalisis.porque1,tblCapaAnalisis.porque2,
tblCapaAnalisis.porque3,tblCapaAnalisis.porque4,tblCapaAnalisis.porque5, tblCapaEfectDeseado.efecto_deseado,tblCapaCondicionesSubestandar.condiciones_subestandar FROM tblCapaAnalisis
INNER JOIN tblCapaElementos ON tblCapaElementos.id = tblCapaAnalisis.elemento INNER JOIN tblCapaEfectDeseado ON tblCapaEfectDeseado.id= tblCapaAnalisis.proridad INNER JOIN tblCapaCondicionesSubestandar ON tblCapaCondicionesSubestandar.id= tblCapaAnalisis.raiz WHERE tblCapaAnalisis.idcapa=$id";
          $resultcausa=sqlsrv_query($conn,$querycausa);
		      while ($filacausa=sqlsrv_fetch_array($resultcausa)) {
		     echo '<div class="col-6">';
          	echo "<h5>Análisis Causa</h5>";
		      		echo "<span>Elemento: ".$filacausa[2]."</span><br>";
		      		echo "<span>¿Por qué N.1? ".$filacausa[3]."</span><br>";
		      		echo "<span>¿Por qué N.2? ".$filacausa[4]."</span><br>";
		      		echo "<span>¿Por qué N.3? ".$filacausa[5]."</span><br>";
		      		echo "<span>¿Por qué N.4? ".$filacausa[6]."</span><br>";
		      		echo "<span>¿Por qué N.5? ".$filacausa[7]."</span><br>";
		      		echo "<span>Prioridad:".$filacausa[8]."</span><br>";
		      		echo "<span>Raíz: ".$filacausa[9]."</span><br>";
		      		echo '</div>';
		  $queryaccion="SELECT tblCapaAcciones.id,tblCapaAcciones.idcausas, tblCapaTipoAccion.nombre,tblCapaAcciones.actividad ,tblCapaAcciones.responsable,TLX032MXDB.dbo.tblEmpleados.Nombre, tblCapaAcciones.fechadecompromiso,tblCapaAcciones.solucion, tblCapaAcciones.archivo, tblCapaAcciones.accioncompleta, tblCapaAcciones.accionvalidada, tblCapaAcciones.descripcionvalidacion,tblCapaAcciones.archivovalidacion FROM tblCapaAcciones INNER JOIN tblCapaTipoAccion ON tblCapaTipoAccion.id= tblCapaAcciones.tipodeaccion INNER JOIN TLX032MXDB.dbo.tblEmpleados on TLX032MXDB.dbo.tblEmpleados.NoEmp=tblCapaAcciones.responsable WHERE tblCapaAcciones.idcausas=".$filacausa[0];
          $resultaccion=sqlsrv_query($conn,$queryaccion);
		     echo '<div class="col-6">';
          		echo "<h5>Acción correctiva o preventiva</h5>";
          		$accioncom='';
          		$accionval='';
		      while ($filaaccion=sqlsrv_fetch_array($resultaccion)) {
		      	$contaccionestotal++;
		      	if($filaaccion["accioncompleta"]==0)$accioncom='NO';else $accioncom='SI'; 
		      	if($filaaccion["accionvalidada"]==0)$accionval='NO';else{$accionval='SI'; $contaccionescomp++;} 
	      		echo "<span>Tipo de acción: ".$filaaccion[2]."</span><br>";
	      		echo "<span>Actividad: ".$filaaccion[3]."</span><br>";
	      		echo "<span>Responsable: ".$filaaccion[4]." - ".$filaaccion[5]."</span><br>";
	      		echo "<span>Fecha compromiso: ".$filaaccion[6]->format('Y/m/d')."</span><br>";
	      		echo "<span>Acción completa: ".$accioncom."</span><br>";
	      		if($filaaccion["accioncompleta"]){
	      		echo "<span>Solución: ".$filaaccion[7]."</span><br>";
	      		echo "<span>Archivo: <a href='Archivos/".$filaaccion[8]."' target='_blank'>".$filaaccion[8]."</a></span><br>";
	      	}
	      		echo "<span>Acción validada: ".$accionval."</span><br>";
					if($filaaccion["accionvalidada"]){
	      		echo "<span>Descripcion: ".$filaaccion['descripcionvalidacion']."</span><br>";
	      		echo "<span>Archivo: <a href='Archivos/".$filaaccion['archivovalidacion']."' target='_blank'>".$filaaccion['archivovalidacion']."</a></span><br>";
	      	}
	      		echo '<hr>';
		      }
		      echo "</div><hr class='m-2 bg-target'>";
		      }
       ?> 
		 		
           
  </div>
 
