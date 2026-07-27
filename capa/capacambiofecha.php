
<?php 
 require_once("../Session/seguridad.php");
 require_once("../index/header.php"); ?>
<!-- Contenido -->
<div class="container rounded shadow">
<h5 class="fw-bold">Tienes estos cambios de fecha pendientes por aprobar</h5>
<div id="tblmisacciones"></div>
</div>
<div id="errores"></div>
<?php require_once("../index/footer.php") ?>
<script src="js/cambiofecha.js" type="text/javascript"></script>
</body>
</html>