function guardarenc(){
	event.preventDefault();
	var folio=$("#folio").val();
	var noemp=$("#noemp").val();
	var puesto=$("#puesto").val();
	var departamento=$("#departamento").val();
	var maquina=$("#maquina").val();
	var poe=$("#POE").val();
	var noempcap=$("#noempcap").val();
	var fecha=$("#fecha").val();
	var minutos=$("#minutos").val();
	var tipo=$("#tipo").val();
	var motivo=$("#motivo").val();
	var observacion=$("#observacion").val();observacion
	if(noemp=='' || departamento==''  || puesto=='' || maquina=='' || tipo==''  || poe=='' || fecha=='' || minutos=='' || motivo==''){
		$("#resultado").html("Hubo un error. No puedes tener campos vacíos");
		return false;
	}
	if(folio==''){
	$.ajax({
		url: 'php/insert.php?enc',
		type: 'POST',
		dataType: 'html',
		data: {'noemp': noemp,
		'departamento': departamento,
		'puesto': puesto,
		'maquina': maquina,
		'poe': poe,
		'noempcap': noempcap,
		'fecha': fecha,
		'minutos': minutos,
		'tipo':tipo,
		'motivo':motivo,
		'observacion':observacion
	    }
	})
	 .done(function(x) {
		$("#resultado").html(x);
		$("#formenc")[0].reset();
		tblenc();
		$("#POE").html("");
		$("#clasif").html("");
	})
	.fail(function() {
		console.log("Error");
	})
	}else{
		$.ajax({
		url: 'php/mod.php?modenc',
		type: 'POST',
		dataType: 'html',
		data: {'folio': folio,
		'noemp': noemp,
		'departamento': departamento,
		'puesto': puesto,
		'maquina': maquina,
		'poe': poe,
		'noempcap': noempcap,
		'fecha': fecha,
		'minutos': minutos,
		'tipo':tipo,
		'motivo':motivo,
		'observacion':observacion
	    }
	})
	.done(function(x) {
		$("#resultado").html(x);
		$("#formenc")[0].reset();
		tblenc();
		$("#POE").html("");
		$("#clasif").html("");

	})
	.fail(function() {
		console.log("Error");
	})
	}
	
}

function llnrdatoemp(){
	event.preventDefault();
	var noemp=$("#noemp").val();
	if(noemp==""){
		$("#resultado").html("El campo no puede estar vacío");
		$("#nombre").val("");
		$("#departamento").val("");
		return false;
	}
	$.ajax({
		url: 'php/slc.php?datosemp',
		type: 'POST',
		dataType: 'html',
		data: {'noemp': noemp},
	})
	.done(function(x) {
		$("#resultado").html(x);
	})
	.fail(function() {
		console.log("Error");
	})
}
function llnrdatoempcap(){
	event.preventDefault();
	var noemp=$("#noempcap").val();
	if(noemp==""){
		$("#resultado").html("El campo no puede estar vacío");
		$("#nombrecap").val("");
		return false;
	}
	$.ajax({
		url: 'php/slc.php?datosempcap',
		type: 'POST',
		dataType: 'html',
		data: {'noemp': noemp},
	})
	.done(function(x) {
		$("#resultado").html(x);
	})
	.fail(function() {
		console.log("Error");
	})
}
function slcmaquinas(){
	$.ajax({
		url: '../trazabilidad/php/slcmaquinasall.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#maquina").html(x);
	})
	.fail(function() {
		console.log("Error");
	})
}slcmaquinas();
function slcpuestos(){
	$.ajax({
		url: 'php/slc.php?puesto',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#puesto").html(x);
	})
	.fail(function() {
		console.log("Error");
	})
}slcpuestos();
function slcdepartamentos(){
	$.ajax({
		url: 'php/slc.php?departamento',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#departamento").html(x);
	})
	.fail(function() {
		console.log("Error");
	})
}slcdepartamentos();
function POE(){
	var poeid=$("#POEID").val();
	$.ajax({
		url: 'php/slc.php?POE',
		type: 'POST',
		dataType: 'html',
		data: {'poeid':poeid}
	})
	.done(function(x) {
		$("#POE").html(x);
		clasificacion();
	})
	.fail(function() {
		console.log("Error");
	})
}
$("#POEID").keyup(function(event) {
POE();
})
$("#POE").change(function(event) {
clasificacion();
})

function clasificacion(){
	var data1= $("#POE").val();
	$.ajax({
		url: 'php/slc.php?clasificacion',
		type: 'POST',
		dataType: 'html',
		data: {'id':data1}
	})
	.done(function(x) {
		$("#clasif").html(x);
	})
	.fail(function() {
		console.log("Error");
	})
}

$("#noemp").on('keyup blur',function(event) {
llnrdatoemp();
});
$("#noempcap").on('keyup blur',function(event) {
llnrdatoempcap();
});

function tblenc(){
	$.ajax({
		url: 'php/tbl.php?tblenc',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#tblenc").html(x);
	})
	.fail(function() {
		console.log("Error");
	})
	
}tblenc();

function edit(id){
	$.ajax({
		url: 'php/mod.php?editenc',
		type: 'POST',
		dataType: 'html',
		data: {'id': id}
	})
	.done(function(x) {
		$("#resultado").html(x);
	})
	.fail(function() {
		console.log("Error");
	})
}
function deleteenc(id){
	$.ajax({
		url: 'php/mod.php?deleteenc',
		type: 'POST',
		dataType: 'html',
		data: {'id': id},
	})
	.done(function(x) {
		$("#resultado").html(x);
		tblenc();
	})
	.fail(function() {
		console.log("Error");
	})
}