<?php require_once("../Session/seguridad.php");
if($_SESSION["admincursos"] !=2 ){
      header("Location:../index/index.php");
   }
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container p-2 border shadow rounded">
	<h5>Lista de cardex</h5>
	<div class="row">
		<div class="col"><input type="text" class="form-control" id="buscarcardex" value="" placeholder="Buscar cardex"></div>
		<div class="col"><button class="btn btn-primary" id="cardexnew"><i class="fas fa-plus-circle"></i> Nuevo cardex</button></div>
	</div>
	<div id="tblcardex"></div>
	<div id="resultado"></div>
  <div id="resultelimina"></div>
	 <div class="modal fade" id="cardexmodal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalLabel">Crear cardex</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      	<small class="fw-bold">Nombre</small>
        <input type="text" class="form-control" id="nom" name="">
      	<small class="fw-bold">Departamento</small>
        <select class="form-control" id="deps"></select>
        <small class="fw-bold">Puestos</small>
        <select class="form-control" id="puestos"></select>
       <div class="form-check form-switch"> 
		  <input class="form-check-input" type="checkbox" id="obsoleto" value="0">
		  <label class="form-check-label" for="obsoleto">Obsoleto</label>
		</div>
      </div>
      <div id="resultsave"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="cardexguardar">Guardar cambio</button>
      </div>
    </div>
  </div>
</div>

	 <div class="modal fade" id="cardexeditmodaledit" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalLabel">Editanto Cardex</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      	<small class="fw-bold">Folio</small>
        <input type="text" class="form-control" id="idcardexedit" readonly>
      	<small class="fw-bold">Nombre</small>
        <input type="text" class="form-control" id="nomedit" name="">
      	<small class="fw-bold">Departamento</small>
      	<select class="form-control" id="depsedit"></select>
        <small class="fw-bold">Puestos</small>
        <select class="form-control" id="puestosedit"></select>
        <div class="form-check form-switch">
		  <input class="form-check-input" type="checkbox" id="obsoletoedit" value="0">
		  <label class="form-check-label" for="obsoletoedit">Obsoleto</label>
		</div>
      </div>
      <div id="resultedit"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="cardexmodificar">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/llnrcardex.js" type="text/javascript"></script>
</body>
</html>