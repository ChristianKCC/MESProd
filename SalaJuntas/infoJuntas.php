<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<div class="container p-5">
<div class="card">
  <p class="card-header">Información de Juntas</h5>
  <div class="card-body">
    <!-- Informacion de Juntas en Salas -->
      <div class="row">
        <div class="col-12">
          <h2 class="tittlecont" id="estadoSala">No hay reuniones programadas</h2>
          <div class="card list-group-item-secondary" id="card-juntas">
            <h5 class="card-header" id="tituloJunta">Titulo de Junta</h5>
            <div class="card-body">
              <div class="row">
                <div class="col-5">
                  <input type="hidden" name="id" id="idReunion">
                  <span class="fw-bold">Nombre del encargado</span>
                  <p class="card-text" id="nombre"></p>
                </div>
                <div class="col-3">
                  <span class="fw-bold">Fecha</span>
                  <p class="card-text" id="fecha"></p>
                </div>
                <div class="col-3">
                  <span class="fw-bold">Horario</span>
                  <p class="card-text" id="horario"></p>
                </div>
              </div>
              <div class="row">
                <div class="col-8">
                  <span class="fw-bold">Descripcion</span>
                  <p class="card-text" id="descripcion"></p>
                </div>
                <div class="col-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="capacitacion" name="capacitacion" disabled>
                    <input type="hidden" name="checkActivoVal" id="checkActivoVal">
                      <label class="form-check-label" for="capacitacion">
                        Capacitación
                      </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-3">
          <h4 class="tittlecont">Registrarse</h4>
          <div class="col-12">
            <br>
            <div class="col-12">
              <br>
              <center>
                <button type="button" class="btn btn-lg btn-primary block" data-bs-toggle="modal" data-bs-target="#modalFace" id="recFacial">
                  <i class="fas fa-search"></i> Buscar Empleado
                </button>
              </center>
            </div>
          </div>
        </div>
        <div class="col-9">
          <h4 class="tittlecont">Próximas Reuniones</h4>
          <ul class='list-group' id='listaReunionesAgendadas'></ul>
        </div>
      </div>
      <div class="modal fade" id="modalFace" tabindex="-1" aria-labelledby="modalFaceLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFaceLabel">Reconocimiento Facial</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <video id='video' width='600' height='500' autoplay></video>
                        <canvas id="overlay" width="600" height="500"></canvas>
                        <br>
                        <h3 id="result"></h3>
                    </div>

                </div>
            </div>
        </div>
  </div>
</div>    
</div>

<?php require_once("../index/footer.php") ?>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script type="module" src="js/infoJunTas.js"></script>
              