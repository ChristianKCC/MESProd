function tblcapa(){
$.ajax({
	url: 'php/tbl.php?accionesmenoruser',
	type: 'POST',
	dataType: 'html'
})
.done(function(x) {
	$("#tblmisacciones").html(x);
})
.fail(function() {
	console.log("error");
})
.always(function() {
	console.log("complete");
});

}tblcapa();
function vercapa2($id){
	$("#modalcapa").modal("toggle");
	$.ajax({
		url: 'php/vercorrecciones.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id},
	})
	.done(function(x) {
		$("#contenidomodal").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}
function finalizaraccion2($id){
	$("#modalcapa").modal("toggle");
	$.ajax({
		url: 'php/finalizarcorreccion.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id},
	})
	.done(function(x) {
		$("#contenidomodal").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}
