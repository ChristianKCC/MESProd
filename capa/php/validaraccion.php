<?php $id=$_POST['id'] ?>
<form>
	<div class="row justify-content-end">
	 <div class="col-6">
	 <h4>Carga la información</h4>
	 </div>
	 <div class="col-1 ">
	 <button type="button" class="btn btn-secondary btn-sm ml-auto" style="float: right; position: fixed;" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Salir</button>
	  </div>
	 </div>
	<div class="row">
	<div class="col-12">
	<input type="hidden" id="idaccion" value="<?php echo $id; ?>" readonly>
	<textarea class="form-control" id="solucion" placeholder="Descripción de la validación"></textarea>
	</div>
	<div class="col-6">
	<small>Importa tu archivo de evidencia</small>
	<input type="file" id="archivo" class="form-control" accept="application/pdf">
	</div>
	<div class="col-12">
	<div class="form-check form-switch">
	  <input class="form-check-input" type="checkbox" id="Implementado" value="0">
	  <label class="form-check-label" for="Implementado">¿Implementado?</label>
	</div>
	</div>
	<button  class="form-control my-2 btn btn-primary" id="completaraccion">Completar</button>
	</div>
</form>
<div id="errores"></div>
Agrega nueva eficacia: <button class="btn btn-success" onclick="eficacia1(<?php echo $id; ?> ,1)"><i class="fa-solid fa-check-double"></i></button>
        <button class="btn btn-warning" onclick="eficacia1(<?php echo $id; ?> ,0)"><i class="fa-solid fa-xmark"></i></button>
<div id="tbleficacia"></div>
<script type="text/javascript">
	function eficacia1($id,$eficacia){
		$.ajax({
			url: 'php/altcapa.php?eficacia1',
			type: 'POST',
			dataType: 'html',
			data: {'id': $id,'eficacia':$eficacia},
		})
		.done(function(x) {
			console.log("Eficacia enviada");
			$("#errores").html(x);
			tbleficacia();
		})
		.fail(function() {
			console.log("error");
		});
	}
	function tbleficacia(){
		var $id=$("#idaccion").val();
		$.ajax({
			url: 'php/tbl.php?eficacia',
			type: 'POST',
			dataType: 'html',
			data: {'id': $id},
		})
		.done(function(x) {
			$("#tbleficacia").html(x);
		})
		.fail(function() {
			console.log("error");
		});	
	}tbleficacia();
	$("#completaraccion").click(function(x){
	x.preventDefault();
	var formData = new FormData();
    var files = $('#archivo')[0].files[0];
	var data1=$("#idaccion").val();
	var data2=$("#solucion").val();
	var data3=$("#Implementado").val();
	if($("#Implementado").prop('checked')){
	data3=1;
	}
	if(data1=="" || data2==""){
	      $("#errores").html("<div class='alert alert-danger my-2' role='alert'>Escribe la descripción.</div>");
	}else if(data3==0){
	      $("#errores").html("<div class='alert alert-danger my-2' role='alert'>Es necesario que sea implementada la acción.</div>");	
	}
	else{
	$("#modalcapa").modal("toggle");
    formData.append('file',files);
	formData.append('idaccion',data1);
    formData.append('solucion',data2);
    formData.append('implementado',data3);
	$.ajax({
		url: 'php/validaaccionupdate.php',
		type: 'POST',
        dataType: "html",
        data: formData,
		cache: false,
        contentType: false,
        processData: false
	})
	.done(function(x) {
		$("#errores").html(x);
		tblcapa();
	});
}
})
</script>