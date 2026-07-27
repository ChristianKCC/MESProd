<?php require_once("../index/header.php"); ?>
<div class="container bg-white rounded shadow">
<div class="row justify-content-between text">
<div class="col">
	<h5 class="fw-bold">Reporte cuenta de gastos</h5>
</div>
<div class="col d-flex justify-content-end">
	<span class="fw-bold ">Fecha: </span><?php echo date('d-m-Y');?>
</div>
</div>
<form id="formcta" class="p-2">
	<div class="row">
		<div class="col">
			<small class="fw-bold">Departamento</small>
			<select class="form-control form-control-sm" id="ctrocostos" multiple>
			</select>
		</div>
		<div class="col">
			<small class="fw-bold">Concepto</small>
			<select class="form-control form-control-sm" id="conseptos" multiple>
			</select>
		</div>
		<div class="col">
			<small class="fw-bold">Estado</small>
			<select class="form-control form-control-sm" id="estado" multiple>
			</select>
		</div>
	</div>
	<div class="row justify-content-between text-center mt-2">
		<div class="col"><button class="btn btn-sm bg-target" id="consultarreporte">Consultar información</button></div>
		<div class="col"><button type="reset" class="btn btn-sm btn-danger">Empezar de nuevo</button></div>
	</div>
</form>
<div id="tablectareporte"></div>
<div id="tablectareporteconc"></div>
<?php require_once("../index/footer.php") ?>
<script src="js/llnarenccta.js" type="text/javascript"></script>