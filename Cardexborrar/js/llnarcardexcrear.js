function tblcardex(){
	$.ajax({
		url: 'php/slccardex.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#slccardex").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}tblcardex();
$("#slccardex").on('change',function(){
		llnardatacrsosagregados();
		llnardatacrsos();
})
function llnardatacrsos(){
	var data1 = $("#slccardex").val(); 
	if(data1=="")
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y una tecnología.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/llnardatacrsos.php',
		type: 'POST',
		dataType: 'html',
		data:{'idcardex':data1}
	})
	.done(function(x) {
		$("#tblcursosadd").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}
};
function llnardatacrsosagregados(){
	var data1 = $("#slccardex").val(); 
	if(data1=="")
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y una tecnología.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/llnardatacrsosagregados.php',
		type: 'POST',
		dataType: 'html',
		data:{'idcardex':data1}
	})
	.done(function(x) {
		$("#tblcursos").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}
};
function lstMCM(){
var data1 = $("#folio").val();
	$.ajax({
		url: '../Cursos/php/slclstmcm.php',
		type: 'POST',
		dataType: 'html',
		data: {'folio':data1}
	})
	.done(function(x) {
		$("#MCM").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}lstMCM();
function lstDeps(){
var data1 = $("#folio").val();
	$.ajax({
		url: '../Cursos/php/slclstdesp.php',
		type: 'POST',
		dataType: 'html',
		data: {'folio':data1}
	})
	.done(function(x) {
		$("#NombreDepto").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}lstDeps();
function lstActividad(){
var data1 = $("#folio").val();
	$.ajax({
		url: '../Cursos/php/slclstactividad.php',
		type: 'POST',
		dataType: 'html',
		data: {'folio':data1}
	})
	.done(function(x) {
		$("#DescripcionActividad").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}lstActividad(); 
function lstTecnologia(){
var data1 = $("#folio").val();
	$.ajax({
		url: '../Cursos/php/slclsttecnologia.php',
		type: 'POST',
		dataType: 'html',
		data: {'folio':data1}
	})
	.done(function(x) {
		$("#NombreTecnologia").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
}lstTecnologia();
function Maquina(){
var data1 = $("#folio").val();
var data2 = $("#NombreDepto").val();
$.ajax({
	url: '../Cursos/php/slclstmaquinas.php',
	type: 'POST',
	dataType: 'html',
	data: {'folio': data1, 'iddep':data2},
})
.done(function(x) {
$("#NombreMaquina").html(x);
})
.fail(function() {
	console.log("error");
})
.always(function() {
	console.log("complete");
});
}
function Seccion(){
var data1 = $("#folio").val();
var data2 = $("#NombreMaquina").val();
$.ajax({
	url: '../Cursos/php/slclstsecciones.php',
	type: 'POST',
	dataType: 'html',
	data: {'folio': data1, 'idmaq':data2},
})
.done(function(x) {
$("#NombreSecciones").html(x);
})
.fail(function() {
	console.log("error");
})
.always(function() {
	console.log("complete");
});
}
$("#NombreDepto").click(function(event) {
Maquina();
});
$("#NombreMaquina").click(function(event) {
Seccion();
});

$("#csltmcm").on('click',function(){
	var data1 = $("#slccardex").val(); 
	var data2 = $("#MCM").val();
	if(data1=="" || data2==null)
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y un MCM.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/consultarmcm.php',
		type: 'POST',
		dataType: 'html',
		data: {'idcardex':data1,'idmcm':data2},
	})
	.done(function(x) {
		$("#tblcursosadd").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}
});
$("#csltdep").on('click',function(){
	var data1 = $("#slccardex").val(); 
	var data2 = $("#NombreDepto").val();
	if(data1=="" || data2==null)
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y un departamento.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/consultardeps.php',
		type: 'POST',
		dataType: 'html',
		data: {'idcardex':data1,'id':data2},
	})
	.done(function(x) {
		$("#tblcursosadd").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}
});
$("#csltmaq").on('click',function(){
	var data1 = $("#slccardex").val(); 
	var data2 = $("#NombreMaquina").val();
	if(data1=="" || data2==null)
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y una máquina.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/consultarmaq.php',
		type: 'POST',
		dataType: 'html',
		data: {'idcardex':data1,'id':data2},
	})
	.done(function(x) {
		$("#tblcursosadd").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}
});
$("#csltsec").on('click',function(){
var data1 = $("#slccardex").val(); 
	var data2 = $("#NombreSecciones").val();
	if(data1=="" || data2==null)
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y una sección.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/consultarsec.php',
		type: 'POST',
		dataType: 'html',
		data: {'idcardex':data1,'id':data2},
	})
	.done(function(x) {
		$("#tblcursosadd").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}
});
$("#csltact").on('click',function(){
var data1 = $("#slccardex").val(); 
	var data2 = $("#DescripcionActividad").val();
	if(data1=="" || data2==null)
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y una actividad.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/consultaract.php',
		type: 'POST',
		dataType: 'html',
		data: {'idcardex':data1,'id':data2},
	})
	.done(function(x) {
		$("#tblcursosadd").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}
});
$("#cslttec").on('click',function(){
var data1 = $("#slccardex").val(); 
	var data2 = $("#NombreTecnologia").val();
	if(data1=="" || data2==null)
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y una tecnología.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/consultartec.php',
		type: 'POST',
		dataType: 'html',
		data: {'idcardex':data1,'id':data2},
	})
	.done(function(x) {
		$("#tblcursosadd").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}
});
$("#tblcursosadd").on('dblclick',function(){
	var data1 = $("#slccardex").val(); 
	var data2 = $("#tblcursosadd").val(); 
	if(data1=="")
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y una tecnología.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/tblcardexaddemp.php',
		type: 'POST',
		dataType: 'html',
		data: {'idcardex':data1,'id':data2},
	})
	.done(function() {
		llnardatacrsosagregados();
		llnardatacrsos();
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}
});
// Otros comandos
$("#tblcursos").on('dblclick',function(){
	var data1 = $("#slccardex").val(); 
	var data2 = $("#tblcursos").val(); 
	if(data1=="")
		 $("#resultadoerror").html("<div class='alert alert-danger' role='alert'>Selecciona un cardex y una tecnología.</div>");
		else{
		 $("#resultadoerror").html("");
	$.ajax({
		url: 'php/tblcardexaddrem.php',
		type: 'POST',
		dataType: 'html',
		data: {'idcardex':data1,'id':data2},
	})
	.done(function() {
		llnardatacrsosagregados();
		llnardatacrsos();
	})
	.fail(function() {
		console.log("error");
	})
	.always(function() {
		console.log("complete");
	});
	}
});