<?php require_once("../index/header.php"); ?>
<div class="container bg-white rounded shadow">
<div class="row justify-content-between text">
<div class="col">
	<h5 class="fw-bold">Cuenta de gastos</h5>
</div>
<div class="col d-flex justify-content-end">
	<span class="fw-bold ">Fecha: </span><?php echo date('d-m-Y');?>
</div>
</div>
<form id="formcta" class="p-2">
		<div class="row">
				<div class="col-1">
					<small class="fw-bold">Folio:</small>
					<input type="text" id="folio" class="form-control form-control-sm" readonly>
				</div>
				<div class="col-3">
					<small class="fw-bold">Nombre:</small>
					<select id="empleados" class="form-control form-control-sm" readonly>
					</select>
				</div>
				<div class="col-3">
					<small class="fw-bold">Departamento:</small>
					<div id="departamento"></div>
				</div>
				<div class="col">
					<small class="fw-bold">Centro de costos:</small>
					<select id="ctrocostos" class="form-control form-control-sm"></select>
				</div>
			</div>
			<div class="row">
				<div class="col">
				  <div class="form-check form-switch">
				  <input class="form-check-input" type="checkbox" id="extranjero">
				  <label class="extranjero" for="flexSwitchCheckDefault">Viaje a extranjero</label>
				</div>
				<div class="col" id="tipodemoneda">
					<small class="fw-bold">Selecciona el tipo de moneda:</small>
					<select id="moneda" class="form-control form-control-sm"></select>
				</div>
				</div>
			</div>
			<div id="resultadoenc"></div>
  <div class="row my-2 justify-content-between">
  	<div class="col"><button type="button" class="btn bg-target btn-sm" name="" id="guardar" title="Guardar"><i class="fas fa-save"></i> Crear cuenta</button>
  	</div>
    <div class="col"><button class="btn btn-warning btn-sm" name="" id="modificar" title="Editar"><i class="fas fa-edit"></i> Editar información</button>
    </div>
    <div class="col"><button class="btn btn-danger btn-sm" name="" id="eliminar" title="Borrar"><i class="fas fa-trash-alt"></i> Eliminar cuenta</button>
    </div>
    <div class="col"><button type="reset" class="btn btn-secondary btn-sm" name="" id="nueva" title="Limpiar"><i class="fas fa-plus"></i> Limpiar</button>
    </div>
    <div class="col"><button type="reset" class="btn btn-success btn-sm" name="" id="finalizar"><i class="fa-solid fa-arrow-up-right-from-square"></i> Enviar a revisión</button>
  	</div>
    <div class="col"><button class="btn bg-target btn-sm" id="tblstctas"><i class="fa-solid fa-clipboard-list"></i> Cuentas creadas</button>
  	</div>
  </div>
</form>
<!-- Conceptos -->
		<form id="fomrconcepto" class="mb-2">
			<div class="row">
				<div class="col">
			<div class="row">
				<div class="col-6">
				<small class="fw-bold">Selecciona el concepto a llenar</small>
				<select class="form-control form-control-sm" id="conseptos"></select>
				</div>
				<div class="col">
				<small class="fw-bold">Anticipo</small>
				<input type="number" id="cntanticipo" class="form-control form-control-sm">
				</div>
				<div class="col">
				<small class="text-danger">Solo puedes agregar un anticipo por cuenta</small>
				<button class="btn btn-primary form-control btn-sm" id="anticipoadd" >Agregar</button>
				</div>
			</div>
				</div>
			</div>
			<div class="row">
					<div class="col-4" id="target">
						<div class="row mb-2">
							<div class="col">
							<small class="fw-bold">Importe</small>
							<input type="number" class="form-control form-control-sm" id="importe">
							</div>
							<div class="col">
							<small class="fw-bold">IVA</small>
							<input type="number" class="form-control form-control-sm" id="iva">
							</div>
							<div class="col">
							<small class="fw-bold">Folio XML <a href="http://www.mx.kcc.com/rge" class="text-primary" target="_blank"><i class="fa-solid fa-circle-question"></i></a></small>
							<input type="text" class="form-control form-control-sm" id="xml">
							</div>
						</div>
						<div class="row mb-2">
							<div class="col">
							<small class="fw-bold">Fecha factura</small>
							<input type="date" class="form-control form-control-sm" id="fecha">
							</div>
						</div>
						<div class="row mb-2">
							<div class="col">
							<small class="fw-bold">Archivo factura</small>
							<input type="file" class="form-control form-control-sm" id="archivo">
							</div>
						</div>
						<div class="row mb-2">
							<div class="col">
								<textarea placeholder="Observaciones" id="observaciones" class="form-control form-control-sm"></textarea>
							</div>
						</div>
						<div class="row mb-2">
							<div class="col">
							<div id="informacion"></div>
							</div>
						</div>
							<button class="btn bg-target btn-sm" id="guardaconsepto">Agregar concepto</button>
					</div>
					<div class="col">
						<div id="tblconseptos"></div>
					</div>
			</div>
		</form>
	<div id="resultadosub"></div>
<div class="modal fade" id="modalstcta" tabindex="-1" aria-labelledby="modalcta" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalcta">Cuentas de gastos cargadas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="tblstctastbl"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/llnarenccta.js" type="text/javascript"></script>