<?php $idcapa=$_POST['id'] ?>
<h5>Plan de Correcciones</h5>
<form>
 <input type="hidden" id="idcapa" class="form-control" readonly value="<?php echo $idcapa; ?>">
  <div class="row">
    <div class="col-3">
      <small class="fw-bold">Acción de</small>
       <select class="form-control form-control-sm" id="tipoaccionc">
      </select>
    </div>
    <div class="col-6">
      <small class="fw-bold">Responsable</small>
      <select class="form-control form-control-sm" id="responsable">
      </select>
    </div>
    <div class="col-3">
      <small class="fw-bold">Fecha compromiso</small>
      <input type="date" id="fechacompromiso" class="form-control form-control-sm">
    </div>
    <div class="col-12">
      <small class="fw-bold">Actividad</small>
      <textarea id="actividad" class="form-control form-control-sm"></textarea>
    </div>
  </div>
  <div id="resultact"></div>
    <div class="row my-5">
  <div class="col text-center">
    <button type="button" class="btn btn-primary btn-sm" name="" id="guardaraccionesmenor" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
  </div>
  <div class="col text-center">
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar</button>
  </div>
  </div>
</form>
<div id="tblaccionesmenor"></div>