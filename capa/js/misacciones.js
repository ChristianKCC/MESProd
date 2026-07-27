function tblcapa(){
$.ajax({
	url: 'php/tblaccionesuser.php',
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
function finalizaraccion($id){
	$("#modalcapa").modal("toggle");
	$.ajax({
		url: 'php/finalizaraccion.php',
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
function reagendarfecha($id){
	$("#modalcapa").modal("toggle");
	$.ajax({
		url: 'php/reagendarfecha.php',
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