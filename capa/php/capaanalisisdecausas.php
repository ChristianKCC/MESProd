<?php $idcapa=$_POST['id'] ?>
<h5>Etapa 4: Análisis de causas</h5>
<hr>
<form  id="formcau">
 <input type="hidden" id="idcapa" class="form-control form-control-sm" readonly value="<?php echo $idcapa; ?>">
  <div class="row">    
    <div class="col-1">
      <small class="fw-bold">Folio análisis</small>
      <input type="text" id="folioanal" class="form-control form-control-sm" readonly>
    </div>
    <div class="col-3">
      <small class="fw-bold">Elemento a analizar</small>
       <select class="form-control form-control-sm" id="elemento">
      </select>
    </div>
    <div class="col-12">
      <small class="fw-bold">1er. ¿Por qué?</small>
      <input type="text" id="1porque" class="form-control form-control-sm">
    </div>

    <div class="col-12">
      <small class="fw-bold">2do. ¿Por qué?</small>
      <input type="text" id="2porque" class="form-control form-control-sm">
    </div>

    <div class="col-12">
      <small class="fw-bold">3er. ¿Por qué?</small>
      <input type="text" id="3porque" class="form-control form-control-sm">
    </div>
    <div class="col-12">
      <small class="fw-bold">4to. ¿Por qué?</small>
      <input type="text" id="4porque" class="form-control form-control-sm">
    </div>
    <div class="col-12">
      <small class="fw-bold">5to. ¿Por qué?</small>
      <input type="text" id="5porque" class="form-control form-control-sm">
    </div>
    <div class="col-3">
      <small class="fw-bold">Causa raíz</small>
      <select class="form-control form-control-sm" id="causaraiz">
      </select>
    </div>
    <div class="col-3">
      <small class="fw-bold">Prioridad de solución</small>
      <!-- Eliminar prioridad de solucion -->
      <select class="form-control form-control-sm" id="prioridad">
      </select>
    </div>
    <div class="col-12" id="descripcionprio"></div>
    
  </div>
  <div id="resultcausas"></div>
    <div class="row justify-content-between text-center my-5">
  <div class="col text-center">
    <button type="button" class="btn bg-target btn-sm" name="" id="guardarcausa" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
  </div>
    <div class="col">
      <button type="reset" class="btn btn-primary btn-sm" name="" id="" onclick="nuevacapa()" title="Limpiar"><i class="fas fa-plus"></i> Nueva causa</button>
  </div>
   <div class="col text-center">
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar</button>
  </div>
  </div>
</form>
<small class="text-danger">Nota: En caso de ver un número en el campo folio, estas editando un registro.</small>
<div id="tblcausas"></div>