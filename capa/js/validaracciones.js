function tblcapa(){
$.ajax({
	url: 'php/tblaccionesvalidas.php',
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
function vercapa($id){
	$("#modalcapa").modal("toggle");
	$.ajax({
		url: 'php/vercapa.php',
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
function validaraccion($id){
	$("#modalcapa").modal("toggle");
	$.ajax({
		url: 'php/validaraccion.php',
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

function vercaparesumen($id){
  $("#modalcapa").modal("toggle");
  $.ajax({
    url: 'php/vercaparesumen.php',
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
}