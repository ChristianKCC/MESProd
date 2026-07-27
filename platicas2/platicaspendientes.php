<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-3 border rounded shadow">
	<form id="formenc">
		<div class="row m-2" id="archivo" style="height: 740px;">
		</div>
		<input type="hidden" id="idplatica">
		<div class="row justify-content-center">
			<div class="col-2"><button class="btn bg-target" id="btnconfirm" style="display: none;">Confirmación de lectura</button></div>
		</div>
	</form>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/regusers.js"></script>