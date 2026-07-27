
<?php  
require_once("../Session/seguridad.php");
require_once("../index/header.php"); ?>
<!-- Contenido -->
<div class="container rounded shadow">
<h5 class="fw-bold">Validación de la efectividad</h5>
<div id="tblefectividad"></div>
</div>
  <div class="modal fade" id="modalcapa" tabindex="-1" aria-labelledby="capamodal" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-body">
        <div id="contenidomodal"></div>
      </div>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/efectividad.js" type="text/javascript"></script>
</body>
</html>