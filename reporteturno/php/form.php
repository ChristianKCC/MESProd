<?php 
require_once "../../Session/seguridad.php";
if(isset($_GET['seguridad'])){
?>
<h5>Seguridad</h5>
<form>
<div class="row">
<div class="col-6">
<div class="col">
	<small>Turno</small>
	 <select class="form-control form-control-sm" id="turno">
        <option value="">Selecciona un turno</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
      </select>
</div>
<div class="col">
	<small>EPP operadores</small>
	<input type="text" class="form-control form-control-sm" id="">
</div>
<div class="col">
	<small>EPP operadores OK</small>
	<input type="text" class="form-control form-control-sm" id="">
</div>
<div class="col">
	<small>Observaciones Operadores</small>
	<input type="text" class="form-control form-control-sm" id="">
</div>
<div class="col">
	<small>Platica seguridad</small>
	<input type="text" class="form-control form-control-sm" id="">
</div>
  <div class="row justify-content-between text-center my-2">
  <div class="col">
    <button type="button" class="btn bg-target btn-sm" name="" id="guardar" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
  </div><div class="col">
      <button type="reset" class="btn btn-primary btn-sm" name="" id="" title="Limpiar"><i class="fas fa-plus"></i> Nuevo Reporte</button>
  </div>
</div>
<div id="resultadosub"></div>
</div>
	<div class="col-6">
		<div id="tblseguridad"></div>
	</div>
</div>
</form>
<?php 
}


else if(isset($_GET['ri'])){
?>
<h5>Recursos Humanos</h5>
<form id="formri">
<div class="row">
<div class="col-6">
<div class="col">
	<small>Ausentismos / Noemp</small>
	<select id="noemp" class="form-control form-control-sm"></select>
</div>
  <div class="row justify-content-between text-center my-2">
  <div class="col">
    <button type="button" class="btn bg-target btn-sm" name="" id="guardarri" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
  </div><div class="col">
      <button type="reset" class="btn btn-primary btn-sm" name="" id="" title="Limpiar"><i class="fas fa-plus"></i> Nuevo Reporte</button>
  </div>
</div>
<div id="resultadosub"></div>
</div>
	<div class="col-6">
		<div id="tblri"></div>
	</div>
</div>
</form>
<?php 
}


else if(isset($_GET['pmecanico'])){
?>
<h5>Pendientes</h5>
<form id="formpm">
<div class="row">
<div class="col-6">
<div class="col">
	<small>Selecciona Maquina</small>
	 <select class="form-control form-control-sm" id="maquinas">
        <option value="">Selecciona una opción</option>
    </select>
</div>
<div class="col">
	<small>Selecciona Seccion</small>
	 <select class="form-control form-control-sm" id="secciones">
        <option value="">Selecciona una opción</option>
    </select>
</div>
<div class="col">
	<small>Tipo de pendiente</small>
	 <select class="form-control form-control-sm" id="tipopendiente">
        <option value="">Selecciona una opción</option>
        <option value="1">Mecanico</option>
        <option value="2">Electrico</option>
    </select>
</div>
<div class="col">
	<small>Pendiente</small>
	<textarea id="descpend" class="form-control form-control-sm"></textarea>
</div>
  <div class="row justify-content-between text-center my-2">
  <div class="col">
    <button type="button" class="btn bg-target btn-sm" name="" id="guardarpm" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
  </div><div class="col">
      <button type="reset" class="btn btn-primary btn-sm" name="" id="" title="Limpiar"><i class="fas fa-plus"></i> Nuevo Reporte</button>
  </div>
</div>
<div id="resultadosub"></div>
</div>
	<div class="col-6">
		<div id="tblpmecanicos"></div>
	</div>
</div>
</form>
<?php 
}

else if(isset($_GET['comentarios'])){
?>
<h5>Comentarios</h5>
<form id="formco">
<div class="row">
<div class="col-6">
<div class="col">
	<small>Selecciona Maquina</small>
	 <select class="form-control form-control-sm" id="maquinas">
        <option value="">Selecciona una opción</option>
    </select>
</div>
<div class="col">
	<small>Comentarios</small>
	<textarea id="descomentarios" class="form-control form-control-sm"></textarea>
</div>
  <div class="row justify-content-between text-center my-2">
  <div class="col">
    <button type="button" class="btn bg-target btn-sm" name="" id="guardarcom" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
  </div><div class="col">
      <button type="reset" class="btn btn-primary btn-sm" name="" id="" title="Limpiar"><i class="fas fa-plus"></i> Nuevo Reporte</button>
  </div>
</div>
<div id="resultadosub"></div>
</div>
	<div class="col-6">
		<div id="tblcomentarios"></div>
	</div>
</div>
</form>
<?php 
}


else if(isset($_GET['parosmaquina'])){
  ?>
  <h5>Paros de maquina</h5>
  <form id="formpm">
  <div class="row">
  <div class="col-6">
  <div class="col">
    <small>Selecciona Maquina</small>
     <select class="form-control form-control-sm" id="maquinas">
          <option value="">Selecciona una opción</option>
      </select>
  </div>
  <div class="col">
    <small>Selecciona Seccion</small>
     <select class="form-control form-control-sm" id="secciones">
          <option value="">Selecciona una opción</option>
      </select>
  </div>
  <div class="col">
    <small>Hora</small>
     <input type="time" class="form-control form-constrol-sm" id="hparo">
  </div>
  <div class="col">
    <small>Tiempo perdido</small>
     <input type="number" class="form-control form-constrol-sm" id="tperdido">
  </div>
  <div class="col">
    <small>Comentarios</small>
    <textarea id="comentariosparo" class="form-control form-control-sm"></textarea>
  </div>
    <div class="row justify-content-between text-center my-2">
    <div class="col">
      <button type="button" class="btn bg-target btn-sm" name="" id="guardarparomaquina" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
    </div><div class="col">
        <button type="reset" class="btn btn-primary btn-sm" name="" id="" title="Limpiar"><i class="fas fa-plus"></i> Nuevo Reporte</button>
    </div>
  </div>
  <div id="resultadosub"></div>
  </div>
    <div class="col-6">
      <div id="tblparosmaquina"></div>
    </div>
  </div>
  </form>
  <?php 
  }

?>