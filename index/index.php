<?php
require_once("../Session/seguridad.php");
require_once("header.php");
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
  <div class="row m-2">
    <div class="col">
      <h4>Hola <?php echo $_SESSION['nombre'] ?>, Bienvenido a <span class="fw-bold">MES</span>!</h4>
    </div>
  </div>
  <div class="row m-2 justify-content-center text-center">
    <div class="col-2">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title" style="color:rgb(0, 109, 160);">Cursos Pendientes</h5>
          <a href="../Cursos/Miscursos.php">
            <h2 class="target-home"><span id="cursos"></span></h2>
          </a>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title" style="color:rgb(0, 109, 160);">Observaciones</h5>
          <a href="../proact/misproact">
            <h2 class="target-home"><span id="proact"></span></h2>
          </a>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title" style="color:rgb(0, 109, 160);">IMC</h5>
          <a href="../IMC/misimc">
            <h2 class="target-home"><span id="IMC"></span></h2>
          </a>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title" style="color:rgb(0, 109, 160);">Platica del día</h5>
          <a href="../platicas/platicaspendientes">
            <h2 class="target-home"><span id="platica">ver</span></h2>
          </a>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title" style="color:rgb(0, 109, 160);">Acciones Correctivas</h5>
          <a href="../Incidencias/accionesCorrectivas">
            <h2 class="target-home"><span id="platica">0</span></h2>
          </a>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title" style="color:rgb(0, 109, 160);">AOPOE</h5>
          <a href="#">
            <h2 class="target-home"><span id="platica">0</span></h2>
          </a>
        </div>
      </div>
    </div>
  </div>
  <div class="row justify-content-center mt-4">
    <div class="col-5">
      <canvas id="canvasgraf"></canvas>
    </div>
  </div>
  <!-- Modal -->
  <div class="modal fade" id="modalpassword" tabindex="-1" aria-labelledby="modalpasswordLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalpasswordLabel">Cambia tu contraseña</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <small>Escribe tu nueva contraseña</small>
          <input type="password" class="form-control form-control-sm" id="contrasena" />
          <small>Confirma tu contraseña</small>
          <input type="password" class="form-control form-control-sm" id="contrasenaconf" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-primary" id="cambiarcontrasena"><i class="fa-solid fa-key"></i> Cambiar contraseña</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once("footer.php") ?>
<script src="js/index.js" type="text/javascript"></script>