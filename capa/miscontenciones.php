
<?php  require_once("../Session/seguridad.php");
require_once("../index/header.php"); ?>
<!-- Contenido -->
<div class="container rounded shadow">
<h5 class="fw-bold">Estas son las acciones pendientes</h5>
<div id="tblmisacciones"></div>
  <div class="modal fade" id="modalcapa" tabindex="-1" aria-labelledby="capamodal" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="capamodal">Carga la información</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="contenidomodal"></div>
      </div>
    </div>
  </div>
</div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/miscontenciones.js" type="text/javascript"></script>
</body>
</html>