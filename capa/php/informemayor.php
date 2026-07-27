<?php $idcapa=$_POST['id'] ?>
<h5 class="mb-2">Etapa 3: Investigación</h5>
<hr>
<form  id="forminf">
 <input type="hidden" id="idcapa" class="form-control" readonly value="<?php echo $idcapa; ?>">
  <div class="row">
  <div class="col-1">
      <small class="fw-bold">Folio investigación</small>
      <input type="text" id="folioinv" class="form-control form-control-sm" readonly>
    </div>
    <div class="col-11">
      <small class="fw-bold">¿Qué sucedió?</small>
      <textarea id="quesuc" class="form-control form-control-sm"></textarea>
    </div>
    <div class="col-4">
      <small class="fw-bold">¿Cuándo sucedió?</small>
      <input type="date" id="cuandosuc" class="form-control form-control-sm">
    </div>
    <div class="col-4">
      <small class="fw-bold">¿Quién operaba la máquina o era el responsable del área?</small>
      <select class="form-control form-control-sm" id="operabaempleados">
      </select>
    </div>
     <div class="col-3">
      <small class="fw-bold">¿Cuántas veces pasó?</small>
      <input type="number" min="0" id="cuantasveces" class="form-control form-control-sm">
    </div>
    <div class="col-12">
      <small class="fw-bold">¿Cómo sucedió?</small>
      <textarea id="comosuc"  class="form-control form-control-sm"></textarea>
    </div>
    <div class="col-6">
      <small class="fw-bold">¿Por qué sucedió?</small>
      <textarea id="porquesuc" class="form-control form-control-sm"></textarea>
    </div>
    <div class="col-6">
      <small class="fw-bold">¿Dónde sucedió?</small>
      <textarea id="dondesuc" class="form-control form-control-sm"></textarea>
    </div>
    <div class="col-2">
         <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="confirmado" value="0">
            <label class="custom-control-label fw-bold" for="confirmado">¿Confirmado?</label>
          </div>
    </div>
    <div class="col-12">
      <small class="fw-bold" id="tipohipodesc">Hipótesis</small>
      <textarea class="form-control form-control-sm" id="descripcion"></textarea>
    </div>
    <div class="col-12" id="fileconfirmado">
    </div>
  </div>
  <div id="resultinvestigacion"></div>
  <div class="row justify-content-between text-center my-5">
  <div class="col ">
      <button type="button" class="btn bg-target btn-sm" name="" id="guardarinvestigacion" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
  </div>
  <div class="col">
      <button type="reset" class="btn btn-primary btn-sm" name="" id="" onclick="nuevacapa(1)" title="Limpiar"><i class="fas fa-plus"></i> Nueva investigación</button>
  </div>
   <div class="col">
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar</button>
  </div>
  </div>
</form>
<small class="text-danger">Nota: En caso de ver un número en el campo folio, estas editando un registro.</small>
<div id="tblinvestigacion"></div>