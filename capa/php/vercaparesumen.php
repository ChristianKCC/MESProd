<?php
require_once "../../../csql6.php";
$id=$_POST['id']; 
$contaccionescomp=0;
$contaccionestotal=0;
$fechamasalta='';
$query="SELECT tblEncabezadoCAPAweb.FolioCapa,tblEncabezadoCAPAweb.Fecha,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,TLX009MXDB.dbo.tblSecciones.NombreSeccion,TLX009MXDB.dbo.tblFuentes.DescripcionFuente,TLX009MXDB.dbo.tblTipoFuente.DescripcionTipoFuente,TLX009MXDB.dbo.tblMCM.MCM,tblEncabezadoCAPAweb.DescripcionCAPA,TLX009MXDB.dbo.tblCapaSeveridades.riesgo AS severidad,TLX009MXDB.dbo.tblCapaProbabilidades.riesgo AS probabilidad,TLX009MXDB.dbo.tblCapaDetecciones.riesgo AS deteccion,TLX009MXDB.dbo.tblCapaNoExpuestas.riesgo AS noexpuestas,tblEncabezadoCAPAweb.Noemp AS NoEmp FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEncabezadoCAPAweb.NoDepto INNER JOIN TLX009MXDB.dbo.tblSecciones on TLX009MXDB.dbo.tblSecciones.NoSeccion=tblEncabezadoCapaweb.NoSeccion INNER JOIN TLX009MXDB.dbo.tblMaquinas on TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblEncabezadoCapaweb.NoMaquina INNER JOIN TLX009MXDB.dbo.tblFuentes ON TLX009MXDB.dbo.tblFuentes.IdFuente = tblEncabezadoCapaweb.IdFuente INNER JOIN TLX009MXDB.dbo.tblTipoFuente ON TLX009MXDB.dbo.tblTipoFuente.IdTipoFuente=tblEncabezadoCapaweb.IdTipoFuente  INNER JOIN TLX009MXDB.dbo.tblMCM ON TLX009MXDB.dbo.tblMCM.IdMCM= tblEncabezadoCapaweb.IdMCM INNER JOIN TLX009MXDB.dbo.tblCapaSeveridades ON TLX009MXDB.dbo.tblCapaSeveridades.id=tblEncabezadoCapaweb.Severidad INNER JOIN TLX009MXDB.dbo.tblCapaProbabilidades ON TLX009MXDB.dbo.tblCapaProbabilidades.id=tblEncabezadoCapaweb.Probabilidad INNER JOIN TLX009MXDB.dbo.tblCapaDetecciones ON TLX009MXDB.dbo.tblCapaDetecciones.id=tblEncabezadoCapaweb.Deteccion INNER JOIN TLX009MXDB.dbo.tblCapaNoExpuestas ON TLX009MXDB.dbo.tblCapaNoExpuestas.id=tblEncabezadoCapaweb.Noexpuestas WHERE tblEncabezadoCAPAweb.FolioCapa = '".$id."' ORDER BY tblEncabezadoCAPAweb.FolioCapa DESC";
		$result=sqlsrv_query($conn,$query);
 ?>
 <div class="row justify-content-end">
 <div class="col-6">
 <h4>Resumen del CAPA</h4>
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
          echo "<span>Responsabilidad: ".$fila[7]."</span><br>";
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
		      		echo "<span>¿Confirmado? ".$confirmado."</span><br>";
		      		echo "<span>Resumen: ".$filainfo[11]."</span><br>";
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
          $contnumcausas=1;
		      while ($filacausa=sqlsrv_fetch_array($resultcausa)) {
		     echo '<div class="col-4">';
          	echo "<h5>Análisis determinación de causa ".$contnumcausas."</h5>";
          	$contnumcausas++;
		      		echo "<span class='fw-bold'>".$filacausa[2]."</span><br>";
		      		echo "<span>Prioridad:".$filacausa[8]."</span><br>";
		      		echo "<span>Causa raíz: ".$filacausa[9]."</span><br>";
		      		echo '</div>';
		  $queryaccion="SELECT tblCapaAcciones.id,tblCapaAcciones.idcausas, tblCapaTipoAccion.nombre,tblCapaAcciones.actividad ,tblCapaAcciones.responsable,TLX032MXDB.dbo.tblEmpleados.Nombre, tblCapaAcciones.fechadecompromiso,tblCapaAcciones.solucion, tblCapaAcciones.archivo, tblCapaAcciones.accioncompleta, tblCapaAcciones.accionvalidada, tblCapaAcciones.descripcionvalidacion,tblCapaAcciones.archivovalidacion,(SELECT MAX(fechavalidacion) FROM tblCapaAcciones WHERE tblCapaAcciones.idcausas=".$filacausa[0].") as maxfecha FROM tblCapaAcciones INNER JOIN tblCapaTipoAccion ON tblCapaTipoAccion.id= tblCapaAcciones.tipodeaccion INNER JOIN TLX032MXDB.dbo.tblEmpleados on TLX032MXDB.dbo.tblEmpleados.NoEmp=tblCapaAcciones.responsable WHERE tblCapaAcciones.idcausas=".$filacausa[0];
          $resultaccion=sqlsrv_query($conn,$queryaccion);
		     echo '<div class="col-8">';
          		echo "<h5>Acción correctiva o preventiva</h5>";
          		echo "<h6>Actividades:</h6>";
          		$accioncom='';
          		$accionval='';
          		$contact0=1;
		      while ($filaaccion=sqlsrv_fetch_array($resultaccion)) {
		      	if($filaaccion['maxfecha']!=''){
		      	$fechamasalta=$filaaccion['maxfecha']->format("Y-m-d");
		      	}
		      	echo '<div class="row">
                    <div class="col-6">';
		      	$contaccionestotal++;
		      	if($filaaccion["accioncompleta"]==0)$accioncom='<i class="fa-solid fa-xmark"></i>';else $accioncom='<i class="fa-solid fa-check"></i>'; 
		      	if($filaaccion["accionvalidada"]==0)$accionval='<i class="fa-solid fa-xmark"></i>';else{$accionval='<i class="fa-solid fa-check"></i>'; $contaccionescomp++;} 
	      		echo "<span>".$contact0.". ".$filaaccion[3]."</span><br>";
		      	$contact0++;
		      	echo '</div><div class="col-3">';
	      		echo "<span>Fecha compromiso: ".$filaaccion[6]->format('Y/m/d')."</span><br>";
		      	echo '</div><div class="col-3">';
            echo "<span>Completa: ".$accioncom."</span><br>";
            echo "<span>Validada: ".$accionval."</span><br>";
	      		echo '</div></div><hr>';
		      }
		       $queryefe="SELECT TOP 1 * FROM tblCapaefectividad WHERE idcapa=$id ORDER BY id DESC";
						$resultefec=sqlsrv_query($conn,$queryefe);
				   	while ($fila = sqlsrv_fetch_array($resultefec)) {
				   		echo "Efectividad de un ".$fila[2]."% cargado el día ".$fila[3]->format('Y/m/d')."<br>";
				   }
		      echo "</div><hr class='m-2 bg-primary'>";
		      }
		    

       ?> 

  </div>

