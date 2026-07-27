$("#consultar").click(function(event) {
	var data1 = $('#departamento').val();
	var data2 = $('#fuente').val();
	var data3 = $('#tipofuente').val();
	var data4 = $('#fechai').val();
	var data5 = $('#fechaf').val();
	var id = $('#buscarinvestigacion').val();
	$.ajax({
		url: 'php/tblcapareporte.php',
		type: 'POST',
		dataType: 'html',
		data:{'key':id,'departamento':data1,'tipofuente':data3,'fechai':data4,'fechaf':data5}
	})
	.done(function(x) {
		$("#resulttbl").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
});
$("#buscarinvestigacion").keyup(function(event) {
var data1=$("#buscarinvestigacion").val();
tblcapa(data1);
});
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
	});
}function vercorrecciones($id){
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

function vercaparesumengraf($id){
  $("#modalcapa").modal("toggle");
  $.ajax({
    url: 'php/vercaparesumengraf.php',
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
$(document).ready(function(){
$.ajax({
	url: 'php/reportegraficas.php',
	type: 'POST',
})
.done(function(data) {
    $("#capareporte").html(data);
	console.log("success");
})
.fail(function() {
	console.log("error");
});
})
function slcdepartamentos(){
  $.ajax({
    url: 'php/slc.php?depto',
    type: 'POST',
    dataType: 'html'
  })
  .done(function(x) {
    $("#departamento").html(x);
  })
  .fail(function() {
    console.log("error");
  })
}slcdepartamentos();
function slcfuente(){
  $.ajax({
    url: 'php/slc.php?fuente',
    type: 'POST',
    dataType: 'html'
  })
  .done(function(x) {
    $("#fuente").html(x);
  })
  .fail(function() {
    console.log("error");
  })
}slcfuente();

function slctipofuente($id){
var data1 = $("#fuente").val();
 $.ajax({
    type: 'POST',
    url:  'php/slc.php?tipofuente',
    data: {'id':data1,'selected':$id}
  })
  .done(function(listas_rep){
    $('#tipofuente').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
$("#fuente").change(function(){
  slctipofuente();
})
