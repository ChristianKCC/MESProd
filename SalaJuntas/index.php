<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<div class="container p-5">
    <h5 class="tittlecont">Reservación de Sala de Juntas</h5>
    <!-- Formulario reservar Sala de Juntas -->
    <form id="formSalaJuntas">
        <div class="row text-center">
          <div class="col-2"><small>Sala</small><select name="salaJuntas" id="salaJuntas" class="form-control form-control-sm"></select></div>
          <div class="col-2"><small>No Empleado</small><input type="number" name="noemp" id="noemp" class="form-control form-control-sm"></div>
          <div class="col-3"><small>Nombre</small><input type="text" name="nombre" id="nombre" class="form-control form-control-sm" disabled></div>
          <div class="col-2"><small>Departamento</small><select id="departamento" name="departamento" class="form-control form-control-sm" disabled></select></div>
          <div class="col-3"><small>Puesto</small><select id="puesto" name="puesto" class="form-control form-control-sm" disabled></select></div>
        </div>
        <div class="row text-center">
          <div class="col-2"><small>Seleccionar Fecha</small><input type="date" id="fechaReservacion" name="fechaReservacion" class="form-control form-control-sm"/></div>
          <div class="col-2"><small>Hora de Inicio</small><input type="time" id="horaInicio" name="horaInicio" class="form-control form-control-sm"/></div>
          <div class="col-2"><small>Hora de Finalización</small><input type="time" id="horaFin" name="horaFin" class="form-control form-control-sm"/></div>
        </div>
        <div class="row text-center">
          <div class="col-3"><small>Titulo de Reunión</small><input type="text" name="titulo" id="titulo" class="form-control form-control-sm"></div>
          <div class="col-5"><small>Descripción</small><textarea name="descripcion" id="descripcion" class="form-control form-control-sm"></textarea></div>
          <div class="col-1"><br />
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="capacitacion" name="capacitacion">
              <input type="hidden" name="checkActivoVal" id="checkActivoVal">
              <label class="form-check-label" for="capacitacion">
                Capacitación
              </label>
            </div>
          </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-1"><br><button class="btn btn-sm bg-target" id="reservarSala"><i class="fas fa-save"></i> Reservar</button></div>
            <div class="col-1"><br><button class="btn btn-sm btn-secondary" id="limpiarFormulario"><i class="fas fa-undo-alt"></i> Limpiar</button></div>
        </div>

    </form>
    <div class="my-4 table-responsive" style="height: 450px;">
        <table class="table table-bordered">
            <thead class="table-dark">
                <th>ID</th>
                <th>No Empleado</th>
                <th>Nombre del expositor</th>
                <th>Titulo de la reunión</th>
                <th>Descripcion</th>
                <th>Fecha</th>
                <th>Horario</th>
                <th>Capacitacion</th>
                <th>Estado</th>
            </thead>
            <tbody id="tblReservaciones">
                
            </tbody>
        </table>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/reservarSala.js"></script>
