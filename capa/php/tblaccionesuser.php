<?php 
require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
$query="SELECT tblCapaAcciones.id,tblCapaAcciones.idcausas,tblCapaAcciones.tipodeaccion,tblCapaAcciones.actividad,tblCapaAcciones.responsable,tblCapaAcciones.fechadecompromiso,tblCapaAcciones.solucion,tblCapaAcciones.archivo,tblCapaAcciones.accioncompleta,TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa,tblCapaTipoAccion.nombre FROM tblCapaAcciones inner join tblCapaAnalisis on tblCapaAnalisis.id=tblCapaAcciones.idcausas inner join TLX006MXDB.dbo.tblEncabezadoCapaweb on TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa = tblCapaAnalisis.idcapa INNER JOIN tblCapaTipoAccion on tblCapaTipoAccion.id= tblCapaAcciones.tipodeaccion WHERE (tblCapaAcciones.responsable=".$_SESSION['ibm']." AND TLX006MXDB.dbo.tblEncabezadoCapaweb.IdAutorizacion=1 AND tblCapaAcciones.accioncompleta='0')";
$resultado = sqlsrv_query($conn,$query);
$fecha='';
 ?>
 <div class="table-responsive">
 <table class="table table-hover table-striped table-sm">
 	<thead class="table-dark">
 		<th>Folio</th>
              <th>Tipo acción</th>
              <th>Descripción</th>
              <th>Fecha compromiso</th>
              <th>Ver CAPA</th>
              <th>Finalizar</th>
 	</thead>
 	<tbody>
 		<?php 
 		while ($row=sqlsrv_fetch_array($resultado)) {
                     $fecha=$row[5]->format("Y/m/d");
                     echo "<tr><td>".$row[0]."</td>";
 			echo "<td>".$row[10]."</td>";
                     echo "<td>".$row[3]."</td>";
                     echo "<td>".$row[5]->format("Y/m/d")."</td>";
            echo "<td><button class='btn btn-primary btn-sm' onclick='vercapa($row[9])'><i class='fas fa-eye'></i></button> ";
            echo "<button class='btn btn-primary btn-sm' onclick='vercaparesumen($row[9])'><i class='fa-solid fa-chart-line'></i></button></td>";
            if($fecha>=date('Y/m/d'))
            echo "<td><button class='btn btn-success btn-sm' onclick='finalizaraccion($row[0])'><i class='fas fa-check-double'></i></button></td></tr>";
            else
            echo "<td><button class='btn btn-danger btn-sm' onclick='reagendarfecha($row[0])'>Fecha expirada</button></td></tr>";
              }
 		 ?>
 	</tbody>
 </table>
</div>
