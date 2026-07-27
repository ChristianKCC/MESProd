<?php $idcapa=$_POST['id'] ?>
<h5>Etapa 5: Plan de acciones correctivas y preventivas</h5>
<hr>
<form id="formacp">
 <input type="hidden" id="idcapa" class="form-control" readonly value="<?php echo $idcapa; ?>">
  <div class="row">
    <div class="col-1">
      <small class="fw-bold">Folio ACP</small>
      <input type="text" id="folioacp" class="form-control form-control-sm" readonly>
    </div>
    <div class="col-8">
      <small class="fw-bold">Determina la causa raíz a atacar</small>
      <select class="form-control form-control-sm" id="causaraiz">
      </select>
    </div>
      <div class="col-3">
      <small class="fw-bold">Causas inmediatas</small>
      <select id="causaimediata" class="form-control form-control-sm">
      </select>
    </div>
  </div>
  <div class="row">
    <div class="col-3">
      <small class="fw-bold">Tipo de acción</small>
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
<div class="row justify-content-between text-center my-5">
  <div class="col text-center">
    <button type="button" class="btn bg-target btn-sm" name="" id="guardaracciones" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
  </div>
     <div class="col">
      <button type="reset" class="btn btn-primary btn-sm" name="" id="" onclick="nuevacapa()" title="Limpiar"><i class="fas fa-plus"></i> Nueva acción</button>
  </div>
  <div class="col text-center">
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar</button>
  </div>
  </div>
</form>
<small class="text-danger">Nota: En caso de ver un número en el campo folio, estas editando un registro.</small>
<div id="tblacciones"></div>
