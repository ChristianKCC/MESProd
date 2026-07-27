<?php 
$id=$_POST['id'];
$contaccionescomp=0;
$contaccionestotal=0;
$fechamasalta='';
require_once "../../../csql.php";
$queryaccion="SELECT MAX(tblCapaAcciones.fechavalidacion) AS maxfecha from tblCapaAcciones inner join tblCapaAnalisis on tblCapaAnalisis.id= tblCapaAcciones.idcausas inner join TLX006MXDB.dbo.tblEncabezadoCapaweb ON TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa= tblCapaAnalisis.idcapa WHERE TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=$id";
          $resultaccion=sqlsrv_query($conn,$queryaccion);
                while ($filaaccion=sqlsrv_fetch_array($resultaccion)) {
                    if($filaaccion['maxfecha']!=''){
                    $fechamasalta=$filaaccion['maxfecha']->format("Y-m-d");
                    }
           }
 ?>
 <div class="row justify-content-end">
      <div class="col-6">
      <h4>Carga la información</h4>
      </div>
      <div class="col-1 ">
      <button type="button" class="btn btn-secondary btn-sm ml-auto" style="float: right; position: fixed;" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Salir</button>
       </div>
      </div>
<div class="row">
     <div class="col-12">
     <div class="card card-body">
     Efetividad de la CAPA 
    <form>
     <div class="row">
     	<div class="col">
          <input type="hidden" id="foliocapa" class="form-control form-control-sm" value="<?php echo $id; ?>" readonly>
     	<small>Eventos antes de acciones</small>
     	<input type="number" id="aac" class="form-control form-control-sm">
     	</div>
     	<div class="col">
     	<small>Última fecha de efectividad</small>
     	<input type="date" id="fecha" class="form-control form-control-sm" value="<?php echo $fechamasalta ?>" readonly>
     	</div>
     	<div class="col">
     	<small>Eventos después de acciones</small>
     	<input type="number" id="dac" class="form-control form-control-sm">
     	</div>
     	<div class="col">
     	<small>Guardar cálculo</small>
     	<button class="btn bg-target form-control form-control-sm btn-sm" id="guardar">Guardar</button>
     	</div>
     </div>
     	<div id="result"></div>
          <div  class="my-2" id="tblefectividadcalculos"></div>
    </form>
     </div>
   </div>
</div>