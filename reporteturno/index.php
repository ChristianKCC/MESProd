
<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
<h5>Creacion de reporte de turno</h5>
<h6>Hola <?php echo $_SESSION['nombre']; ?> el reporte se creará con el número de empleado: <?php echo $_SESSION['ibm']; ?> con la fecha <?php echo date("Y-m-d h:i:s"); ?></h6>
<div class="row">
    <div class="col-6">
<form id="rturno">
  <div class="row my-2">
    <div class="col-2">
      <small>Folio:</small>
      <input type="text" class="form-control form-control-sm" id="folio" readonly>
    </div>
     <div class="col">
      <small>Selecciona el Departamento:</small>
      <select class="form-control form-control-sm" id="deps">
      </select>
    </div>
    <div class="col">
      <small>Selecciona el turno:</small>
      <select class="form-control form-control-sm" id="turnoenc">
        <option value="">Selecciona un turno</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
      </select>
    </div>
  </div>
  <div class="row justify-content-between text-center my-2">
  <div class="col">
    <button type="button" class="btn bg-target btn-sm" name="" id="guardar" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
  </div><div class="col">
    <button type="button" class="btn btn-success btn-sm" name="" id="terminar" title="Finalizar"><i class="fa-solid fa-arrow-up-right-from-square"></i> Terminar</button>
  </div><div class="col">
      <button onclick="location.reload()" class="btn btn-primary btn-sm" name="" id="" title="Limpiar"><i class="fas fa-plus"></i> Nuevo Reporte</button>
  </div>
</div>
</form>
<div id="resultadoenc"></div>
  <div class="row my-2 justify-content-between text-center">
    <div class="col-12 my-2">
      <button class="btn btn-sm form-control" id="ri" style="background:#caf0f8;">Recursos Humanos</button>
    </div>
    <div class="col-12 my-2">
      <button class="btn btn-sm form-control" id="pmecanico" style="background:#caf0f8;">Pendientes</button>
    </div>
    <div class="col-12 my-2">
      <button class="btn btn-sm form-control" id="parosmaquina" style="background:#caf0f8;">Paros de maquina</button>
    </div>
     <div class="col-12 my-2">
      <button class="btn btn-sm form-control" id="comentarios" style="background:#caf0f8;">Comentarios</button>
    </div>
  </div>
</div>
<div class="col-6">
  <div id="tblrepturno">
</div>
</div>
</div>
<div id="subenc"></div>
<div class="modal fade" id="viewmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content p-2">
      <div id="contmodal"></div>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/index.js" type="text/javascript"></script>