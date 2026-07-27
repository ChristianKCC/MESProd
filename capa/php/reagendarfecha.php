<?php $id=$_POST['id'] ?>
<form>
	  <div class="row justify-content-end">
 <div class="col-6">
 <h4>Nueva fecha compromiso</h4>
 </div>
 <div class="col-1 ">
 <button type="button" class="btn btn-secondary btn-sm ml-auto" style="float: right; position: fixed;" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Salir</button>
  </div>
 </div>
	<div class="row">
		<input type="hidden" id="idaccion" value="<?php echo $id; ?>" readonly>
		<div class="col-5">
		<input type="date" id="fecha" class="form-control">
		<small>Selecciona la nueva fecha compromiso</small>
		</div>
		<div class="col-5">
		<select class="form-control" id="slcvalidadores">
		</select>
		<small>¿Quién va revisar la acción?</small>
		</div>
		<div class="col-2">
			<button  class="form-control my-2 btn btn-primary" id="nuevafecha">Enviar solicitud</button>	
		</div>
	</div>
</form>
<div id="errores"></div>
<script type="text/javascript">
	$("#nuevafecha").click(function(x){
	x.preventDefault();
	var formData = new FormData();
	var data1=$("#fecha").val();
	var data2=$("#idaccion").val();
	var data3=$("#slcvalidadores").val();
	var hoy = new Date();
    var dd = hoy.getDate()+1;
    var mm = hoy.getMonth()+1;
    var yyyy = hoy.getFullYear();
    if(dd<10) 
    dd='0'+dd;
    if(mm<10) 
    mm='0'+mm;
    var fecha=yyyy+"-"+mm+"-"+dd;
	if(data1=="" || data2=="" || data3==""){
	      $("#errores").html("<div class='alert alert-danger my-2' role='alert'>No puede haber campos vacíos.</div>");
	}else if(data1<=fecha){
		      $("#errores").html("<div class='alert alert-danger my-2' role='alert'>La fecha compromiso debe ser por lo menos 2 días después a la fecha actual.</div>");
	}
	else{
	// $("#modalcapa").modal("toggle");
	formData.append('fecha',data1);
	formData.append('idaccion',data2);
	formData.append('validador',data3);
	$.ajax({
		url: 'php/nuevafecha.php',
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