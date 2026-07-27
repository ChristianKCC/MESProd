<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-3 border rounded shadow">
  <h4 class="tittlecont">Registro IT/ OPT</h4>
  <form id="formenc">
    <div class="row">
      <div class="col-1">
        <small>No. emp</small>
        <input type="number" id="noemp" min="0" class="form-control form-control-sm">
      </div>
      <div class="col-2">
        <small>Nombre</small>
        <input type="text" id="nombre" class="form-control form-control-sm" readonly>
      </div>
      <div class="col-2">
        <small>Puesto</small>
        <select id="puesto" class="form-control form-control-sm"></select>
      </div>
      <div class="col-2">
        <small>Departamento</small>
        <select id="departamento" class="form-control form-control-sm"></select>
      </div>
      <div class="col-2">
        <small>Máquina</small>
        <select id="maquina" class="form-control form-control-sm">
        </select>
      </div>
      <div class="col-1">
        <small>Tipo</small>
        <select id="tipo" class="form-control form-control-sm">
          <option value="">Selecciona una opción</option>
          <option value="1">IT</option>
          <option value="2">OPT</option>
          <option value="3">Lectura AOPOE</option>
        </select>
      </div>
      <div class="col-2">
        <small>Motivo</small>
        <select id="motivo" class="form-control form-control-sm">
          <option value="">Selecciona una opción</option>
          <option value="1">Nuevo ingreso</option>
          <option value="2">Cambio de puesto</option>
          <option value="3">Revisión</option>
        </select>
        <!-- AGREGAR A INICIO AOPOE Y ACCIONES CORRECTO  -->
      </div>
    </div>
    <div class="row">
      <div class="col-1">
        <small>ID POE</small>
        <input type="text" id="POEID" min="0" class="form-control form-control-sm">
      </div>
      <div class="col-4">
        <small>POE</small>
        <select id="POE" class="form-control form-control-sm">
        </select>
      </div>
      <div class="col-1">
        <small>Clasificación</small>
        <div type="text" id="clasif" class="form-control form-control-sm" readonly></div>
        </select>
      </div>
      <div class="col-1">
        <small>Capacitador</small>
        <input type="number" id="noempcap" min="0" class="form-control form-control-sm">
      </div>
      <div class="col-3">
        <small>Nombre</small>
        <input type="text" id="nombrecap" class="form-control form-control-sm" readonly>
      </div>
      <div class="col-2">
        <small>Fecha</small>
        <input type="date" id="fecha" name="" class="form-control form-control-sm">
      </div>
      <div class="col-11">
        <small>Observación</small>
        <textarea id="observacion" name="" class="form-control form-control-sm"></textarea>
      </div>
      <div class="col-1">
        <small>Duración</small>
        <input type="number" id="minutos" min="0" name="" class="form-control form-control-sm">
      </div>
    </div>

    <div class="row justify-content-center text-center my-2">
      <div class="col-8">
        <input type="text" class="form-control form-control-sm" id="folio" placeholder="Folio seleccionado" readonly>
      </div>
      <div class="col-2">
        <button class="btn btn-sm bg-target" onclick="guardarenc()"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
      </div>
      <div class="col-2">
        <button type="reset" class="btn btn-sm btn-secondary"><i class="fa-solid fa-arrows-rotate"></i> Limpiar</button>
      </div>
    </div>
    <div id="resultado"></div>
  </form>
  <div id="tblenc"></div>
</div>


<?php require_once("../index/footer.php") ?>
<script type="text/javascript" src="js/index.js"></script>