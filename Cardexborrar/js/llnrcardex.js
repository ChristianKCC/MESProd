function tblcardex(){
	var dato = $("#buscarcardex").val();
	$.ajax({
		url: 'php/tblcardex.php',
		type: 'POST',
		dataType: 'html',
		data: {'buscarcardex': dato }
	})
	.done(function(x) {
		$("#tblcardex").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}
tblcardex();

function cardexedit($id){
	$.ajax({
		url: 'php/cardexedit.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id }
	})
	.done(function(x) {
		$("#resultado").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}
function cardexdelete($id){
	$.ajax({
		url: 'php/cardexeliminar.php',
		type: 'POST',
		dataType: 'html',
		data: {'id':$id}
	})
	.done(function(x) {
		$("#resultelimina").html(x);
		tblcardex();
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}
$("#buscarcardex").on('keyup',function(event) {
tblcardex();
});
$("#cardexnew").on('click', function(event) {
	event.preventDefault();
	$.ajax({
		url: 'php/cardexnew.php',
		type: 'POST',
		dataType: 'html',
	})
	.done(function(x) {
		$("#resultado").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
});

function cargardeps(){
	$.ajax({
		url: '../capa/php/slc.php?depto',
		type: 'POST',
		dataType: 'html',
	})
	.done(function(x) {
		$("#deps").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
		$.ajax({
		url: '../capa/php/slc.php?depto',
		type: 'POST',
		dataType: 'html',
	})
	.done(function(x) {
		$("#depsedit").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}

function cargarpuestos(){
	$.ajax({
		url: '../RI/php/slcpuestos.php',
		type: 'POST',
		dataType: 'html',
	})
	.done(function(x) {
		$("#puestos").html(x);
		$("#puestosedit").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}cargarpuestos();
cargardeps();
$("#cardexmodificar").on('click',function(){
	var data1 = $("#idcardexedit").val();
	var data2 = $("#nomedit").val();
	var data3 = $("#depsedit").val();
	var data5 = $("#puestosedit").val();
	var data4 = $("#obsoletoedit").val();
	if( $('#obsoletoedit').prop('checked') ) {
    var data4=1;
    }
	$.ajax({
		url: 'php/cardexmodificar.php',
		type: 'POST',
		dataType: 'html',
		data: {'idcardexedit':data1,'nomedit':data2,'depsedit':data3,'obsoletoedit':data4,'puestosedit':data5},
	})
	.done(function(x) {
		$("#resultedit").html(x);
		tblcardex();
		$("#resultelimina").html("");
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
})
$("#cardexguardar").on('click',function(){
	var data2 = $("#nom").val();
	var data3 = $("#deps").val();
	var data5 = $("#puestos").val();
	var data4 = $("#obsoleto").val();
	if( $('#obsoleto').prop('checked') ) {
    var data4=1;
    }
	$.ajax({
		url: 'php/cardexagregar.php',
		type: 'POST',
		dataType: 'html',
		data: {'nomedit':data2,'depsedit':data3,'obsoletoedit':data4,'puestos':data5}
	})
	.done(function(x) {
		$("#resultsave").html(x);
		tblcardex();
		$("#resultelimina").html("");
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
})
