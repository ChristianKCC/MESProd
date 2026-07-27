<?php require_once("../index/header.php"); ?>
<div class="container rounded shadow">
	<h4 class="tittlecont">Reporte de trazabilidad</h4>
	<form>
		<div class="row mb-2">
			<div class="col"><small>Maquina</small><select class="form-control form-control-sm" id="maquina"></select></div>
			<div class="col"><small>Fecha Inicio</small><input type="date" id="fechai" class="form-control form-control-sm"></div>
			<div class="col"><small>Fecha Final</small><input type="date" id="fechaf" class="form-control form-control-sm"></div>
			<div class="col-1"><br /><button id="filtro" class="form-control btn btn-sm bg-target"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button></div>
			<div class="col-1"><br /><button type="reset" class="form-control btn btn-sm btn-danger"><i class="fa-solid fa-rotate-left"></i> Limpiar</button></div>
		</div>
		<div class="row justify-content-end">
			<div class="col-1">
				<a href="#" id=exceltbltraz>Exportar Excel</a>
			</div>
		</div>
		<div id="result"></div>
	</form>
	<div class="row">
		<div id="tbltrazabilidadcompleto"></div>
		<div id="graficacanvasjs"></div>
	</div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/trazabilidadreporte.js"></script>