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
	<textarea class="form-control" id="solucion" placeholder="Agrega la descipción de lo que hiciste"></textarea>
		</div>
		<div class="col-4">
	<small>Importa tu archivo de evidencia 2</small>
	<input type="file" id="archivo" class="form-control" accept="application/pdf">
		</div>
		<div class="col-4">
		<small>¿Quién va revisar la acción?</small>
		<select class="form-control" id="slcvalidadores">
		</select>
		</div>
		<div class="col-4">
		<small>Fecha</small>
		<input type="date" id="fecha" class="form-control">
		</div>
	<button  class="form-control my-2 btn btn-primary" id="completarcorrecion">Completar</button>	
</form>
<div id="errores"></div>
<script type="text/javascript">
	$("#completarcorrecion").click(function(x){
	x.preventDefault();
	var formData = new FormData();
    var files = $('#archivo')[0].files[0];
	var data1=$("#idaccion").val();
	var data2=$("#solucion").val();
	var data3=$("#slcvalidadores").val();
	var data4=$("#fecha").val();
	if(data1=="" || data2=="" || data3=="" || data4=="" || files.length==0){
	      $("#errores").html("<div class='alert alert-danger my-2' role='alert'>No puede haber campos vacíos.</div>");
	}
	else{
	$("#modalcapa").modal("toggle");
    formData.append('file',files);
	formData.append('idaccion',data1);
    formData.append('solucion',data2);
    formData.append('validador',data3);
    formData.append('fecha',data4);
	$.ajax({
		url: 'php/completaraccion.php',
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
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}
})
	function slcvalidadores(){
	var idac=$("#idaccion").val();
		$.ajax({
			url: 'php/slc.php?validadores',
			type: 'POST',
			dataType: 'html',
			data: {'idaccion': idac},
		})
		.done(function(x) {
			$("#slcvalidadores").html(x);
		})
		.fail(function() {
			console.log("error");
		})
		.always(function() {
			console.log("complete");
		});
	}slcvalidadores();
</script>