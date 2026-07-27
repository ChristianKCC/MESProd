<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<div class="container p-2">
    <h5 class="tittlecont">Lista de Acciones Correctivas</h5>
    <!-- Tabla -->
    <div class="my-2 table-responsive border" style="height: 760px;">
        <table class="table table-bordered">
            <thead class="table-dark">
                <th>ID</th>
                <th>NoEmp</th>
                <th>Nombre</th>
                <th>Causa Básica</th>
                <th>Causa Inmediata</th>
                <th>Causa Raíz</th>
                <th>Comportamiento</th>
                <th>Accion Correctiva</th>
                <th>Porque</th>
                <th>Porque Causa</th>
                <th>Porque Raíz</th>
                <th>Folio</th>
                <th></th>
            </thead>
            <tbody id="tblAcciones">
                
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Registro de Acción Correctiva</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="row">
                <div class="row">
                  <div class="col-1">
                    <small class="fw-bold">ID</small>
                    <input type="text" id="folioetapa4" class="form-control form-control-sm" readonly="">
                  </div>
                  <div class="col-8">
                    <small class="fw-bold">Comentarios</small>
                    <textarea class="form-control" placeholder="Comentarios" id="comentarios" style="height: 50px"></textarea>
                  </div>
                  <div class="col-3">
                    <small class="fw-bold">Fecha</small>
                    <input type="date" id="fechaRevision" name="fechaRevision" class="form-control form-control-sm"/>
                  </div>
                </div>
                <div class="row">
                  <div class="col-4">
                    <small class="fw-bold">Archivo</small>
                    <input type="file" id="archivo">
                  </div>
                  <div class="col-2">
                    <br>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="checkRegistrarAccion">
                      <!-- <input type="hidden" name="checkActivoVal" id="checkActivoVal"> -->
                      <label class="form-check-label" for="checkRegistrarAccion">
                        Finalizado
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btnCancelar" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnRegistrar">Registrar</button>
          </div>
        </div>
      </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/accCorrectivas.js"></script>
