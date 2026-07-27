<?php
require_once "../../../csql6.php";
$id=$_POST['id']; 
$query="SELECT tblEncabezadoCAPAweb.FolioCapa,tblEncabezadoCAPAweb.Fecha,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,TLX009MXDB.dbo.tblSecciones.NombreSeccion,TLX009MXDB.dbo.tblFuentes.DescripcionFuente,TLX009MXDB.dbo.tblTipoFuente.DescripcionTipoFuente,TLX009MXDB.dbo.tblMCM.MCM,tblEncabezadoCAPAweb.DescripcionCAPA,TLX009MXDB.dbo.tblCapaSeveridades.riesgo AS severidad,TLX009MXDB.dbo.tblCapaProbabilidades.riesgo AS probabilidad,TLX009MXDB.dbo.tblCapaDetecciones.riesgo AS deteccion FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEncabezadoCAPAweb.NoDepto INNER JOIN TLX009MXDB.dbo.tblSecciones on TLX009MXDB.dbo.tblSecciones.NoSeccion=tblEncabezadoCapaweb.NoSeccion INNER JOIN TLX009MXDB.dbo.tblMaquinas on TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblEncabezadoCapaweb.NoMaquina INNER JOIN TLX009MXDB.dbo.tblFuentes ON TLX009MXDB.dbo.tblFuentes.IdFuente = tblEncabezadoCapaweb.IdFuente INNER JOIN TLX009MXDB.dbo.tblTipoFuente ON TLX009MXDB.dbo.tblTipoFuente.IdTipoFuente=tblEncabezadoCapaweb.IdTipoFuente  INNER JOIN TLX009MXDB.dbo.tblMCM ON TLX009MXDB.dbo.tblMCM.IdMCM= tblEncabezadoCapaweb.IdMCM INNER JOIN TLX009MXDB.dbo.tblCapaSeveridades ON TLX009MXDB.dbo.tblCapaSeveridades.id=tblEncabezadoCapaweb.Severidad INNER JOIN TLX009MXDB.dbo.tblCapaProbabilidades ON TLX009MXDB.dbo.tblCapaProbabilidades.id=tblEncabezadoCapaweb.Probabilidad INNER JOIN TLX009MXDB.dbo.tblCapaDetecciones ON TLX009MXDB.dbo.tblCapaDetecciones.id=tblEncabezadoCapaweb.Deteccion WHERE tblEncabezadoCAPAweb.FolioCapa = '".$id."' ORDER BY tblEncabezadoCAPAweb.FolioCapa DESC";
		$result=sqlsrv_query($conn,$query);
 ?>
 <div class="row justify-content-end">
 <div class="col-6">
 <h4>Plan de correcciones</h4>
 </div>
 <div class="col-1 ">
 <button type="button" class="btn btn-secondary btn-sm ml-auto" style="float: right; position: fixed;" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Salir</button>
  </div>
 </div>
 <hr>
 <div class="row">
 	<div class="col-6">
            <?php 
            $centinela="";
      while ($fila=sqlsrv_fetch_array($result)) {
          $suma=$fila[9]+$fila[10]+$fila[11];
          echo "<h5 class='card-title fw-bold'>Folio:".$fila[0]."</h5>";
          echo "<span>Departamento: ".$fila[2]."</span><br>";
          echo "<span>Máquina: ".$fila[3]."</span><br>";
          echo "<span>Sección: ".$fila[4]."</span><br>";
          echo "<span>Fuente: ".$fila[5]."</span><br>";
          echo "<span>Tipo de fuente: ".$fila[6]."</span><br>";
          echo "<span>MCM: ".$fila[7]."</span><br>";
          echo "<span>Descripción CAPA: ".$fila[8]."</span><br>";
          echo "<p class='text-warning'>La desviación no representa un riesgo mayor o crítico, por lo que únicamente se deberá ejecutar la etapa de revisión inicial e investigación y la etapa de plan de correciones para eliminar el problema.</p>";
      	}
      	?>
      </div>
 	<div class="col-6">
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
		  $queryaccion="SELECT tblCapaAccionesMenor.id,tblCapaAccionesMenor.idcapa, tblCapaTipoAccionMenor.nombre,tblCapaAccionesMenor.actividad ,tblCapaAccionesMenor.responsable,TLX032MXDB.dbo.tblEmpleados.Nombre, tblCapaAccionesMenor.fechadecompromiso FROM tblCapaAccionesMenor INNER JOIN tblCapaTipoAccionMenor ON tblCapaTipoAccionMenor.id= tblCapaAccionesMenor.tipodeaccion INNER JOIN TLX032MXDB.dbo.tblEmpleados on TLX032MXDB.dbo.tblEmpleados.NoEmp=tblCapaAccionesMenor.responsable WHERE tblCapaAccionesMenor.idcapa=".$id;
          $resultaccion=sqlsrv_query($conn,$queryaccion);
          		echo "<h5>Plan de Correcciones</h5>";
		      while ($filaaccion=sqlsrv_fetch_array($resultaccion)) {
	      		echo "<span>Tipo de acción: ".$filaaccion[2]."</span><br>";
	      		echo "<span>Actividad: ".$filaaccion[3]."</span><br>";
	      		echo "<span>Responsable: ".$filaaccion[4]." - ".$filaaccion[5]."</span><br>";
	      		echo "<span>Fecha compromiso ".$filaaccion[6]->format('Y/m/d')."</span><br>";
	      		echo '<hr>';
		      }
	
       ?>
   </div>

