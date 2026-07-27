<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
  <h5 class="tittlecont">Registro de folios Laboratorio</h5>
  <div class="row m-2">
    <div class="col-2">
      <small>Fecha</small>
      <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" />
    </div>
    <div class="col-2">
      <small>Turno</small>
      <select class="form-control form-control-sm" id="turno" name="turno"></select>
    </div>
    <div class="col-1">
      <small>NoEmp</small>
      <input type="number" class="form-control form-control-sm" id="monitor" name="monitor" />
    </div>
    <div class="col-2">
      <small>Monitor</small>
      <input type="text" class="form-control form-control-sm" id="monitornombre" name="monitornombre" readonly />
    </div>
    <div class="col-1">
      <small>S + D</small>
      <input type="number" class="form-control form-control-sm" id="sd" name="sd" />
    </div>
    <div class="col-1">
      <small>QL</small>
      <input type="number" class="form-control form-control-sm" id="ql" name="ql" />
    </div>
    <div class="col-1 ">
      <small>No. muestras</small>
      <input type="number" class="form-control form-control-sm" id="numuestras" name="numuestras" />
    </div>
    <div class="col-2">
      <small>Departamento</small>
      <select class="form-control form-control-sm" id="departamento" name="departamento"></select>
    </div>
    <div class="col-2">
      <small>Maquina</small>
      <select class="form-control form-control-sm" id="maquina" name="maquina"></select>
    </div>
    <div class="col-1">
      <small>No. Conductor</small>
      <input type="number" class="form-control form-control-sm" id="conductor" name="conductor" />
    </div>
    <div class="col-2">
      <small>Conductor</small>
      <input type="text" class="form-control form-control-sm" id="conductornombre" name="conductornombre" readonly />
    </div>
    <div class="col-1">
      <small>No. Supervisor</small>
      <input type="number" class="form-control form-control-sm" id="supervisor" name="supervisor" />
    </div>
    <div class="col-2">
      <small>Supervisor</small>
      <input type="text" class="form-control form-control-sm" id="supervisornombre" name="supervisornombre" readonly />
    </div>
    <div class="col-1">
      <br />
      <button class="btn btn-sm bg-target form-control form-control-sm" id="saveEnclab"><i class="fas fa-save"></i> Guardar</button>
    </div>
    <div class="col-1">
      <br />
      <button class="btn btn-sm btn-secondary form-control form-control-sm" id="resetEnc"><i class="fas fa-undo-alt"></i> Limpiar</button>
    </div>
  </div>
  <div class="row">
    <div class="table-responsive" style="height: 200px;">
      <table class="table table-hover">
        <thead class="table-dark">
          <th>ID</th>
          <th>Fecha</th>
          <th>Turno</th>
          <th>Monitor</th>
          <th>S + D</th>
          <th>QL</th>
          <th>No. muestras</th>
          <th>Departamento</th>
          <th>Maquina</th>
          <th>Conductor</th>
          <th>Supervisor</th>
        </thead>
        <tbody id="tblencfolio"></tbody>
      </table>
    </div>
  </div>

  <hr />
  <div class="row mb-2">
    <div class="col-1">
      <small>No. Folio</small>
      <input type="text" class="form-control form-control-sm" id="noofolio" name="monitornombre" />
    </div>
    <div class="col-3">
      <small>Clave</small>
      <select class="form-control form-control-sm" id="clave" name="clave"></select>
    </div>
    <div class="col-1">
      <small>Retenido</small>
      <input type="numbre" class="form-control form-control-sm" id="retenido" name="retenido" />
    </div>
    <div class="col-1">
      <small>Merma</small>
      <input type="number" class="form-control form-control-sm" id="merma" name="merma" />
    </div>
    <div class="col-1">
      <small>Recuperado</small>
      <input type="number" class="form-control form-control-sm" id="recuperado" name="recuperado" />
    </div>
    <div class="col-3">
      <small>Defecto</small>
      <select class="form-control form-control-sm" id="defecto" name="defecto"></select>
    </div>
    <div class="col-2">
      <small>Componente</small>
      <select class="form-control form-control-sm" id="componente" name="componente"></select>
    </div>
    <div class="col-1">
      <small>No. Apartó</small>
      <input type="text" class="form-control form-control-sm" id="numeroaparto" name="numeroaparto" />
    </div>
    <div class="col-2">
      <small>Apartó</small>
      <input type="text" class="form-control form-control-sm" id="apartonombre" name="apartonombre" readonly />
    </div>
    <div class="col-1">
      <small>No. Liberó</small>
      <input type="text" class="form-control form-control-sm" id="numerolibero" name="numerolibero" />
    </div>
    <div class="col-2">
      <small>Liberó</small>
      <input type="text" class="form-control form-control-sm" id="liberonombre" name="liberonombre" readonly />
    </div>
    <div class="col-1">
      <small>Hora</small>
      <input type="time" class="form-control form-control-sm" id="hora" name="hora" />
    </div>
    <div class="col-1">
      <small>No. Operador</small>
      <input type="text" class="form-control form-control-sm" id="numerooperador" name="numerooperador" />
    </div>
    <div class="col-2">
      <small>Operador</small>
      <input type="text" class="form-control form-control-sm" id="operadornombre" name="operadornombre" readonly />
    </div>
    <div class="col-2">
      <small>Estatus</small>
      <select class="form-control form-control-sm" id="estatus" name="estatus"></select>
    </div>
    <div class="col-6">
      <small>Comentario</small>
      <input type="text" class="form-control form-control-sm" id="comentario" name="comentario" />
    </div>
    <div class="col-2">
      <small>Seccion</small>
      <select class="form-control form-control-sm" id="seccion" name="seccion">
        <option value="">Selecciona una opcion</option>
      </select>
    </div>
    <div class="col-1">
      <small>ID Encabezado</small>
      <input type="text" class="form-control form-control-sm" id="idencabezado" name="idencabezado" readonly />
    </div>
    <div class="col-1">
      <br />
      <button class="btn btn-sm bg-target form-control form-control-sm" id="saveSubEnc"><i class="fas fa-save"></i> Guardar</button>
    </div>
    <div class="col-1">
      <br />
      <button class="btn btn-sm btn-secondary form-control form-control-sm" id="resetsub"><i class="fas fa-undo-alt"></i> Limpiar</button>
    </div>

  </div>
  <div class="row">
    <div class="table-responsive" style="height: 200px;">
      <table class="table ">
        <thead class="table-dark">
          <th>ID</th>
          <th>Folio</th>
          <th>Clave</th>
          <th>Retenido</th>
          <th>Merma</th>
          <th>Recuperado</th>
          <th>Defecto</th>
          <th>Componente</th>
          <th>Aparto</th>
          <th>Libero</th>
          <th>Hora</th>
          <th>Operador</th>
          <th>Estatus</th>
          <th>Comentario</th>
          <th>Seccion</th>
          <th>IDEnc</th>
        </thead>
        <tbody id="tblSubencfolio"></tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/laboratorio.js" type="module"></script>