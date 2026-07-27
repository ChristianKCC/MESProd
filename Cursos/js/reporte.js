function slccurso(data1){
	$.ajax({
		url: 'php/slcldesccursofiltro.php',
		type: 'POST',
		dataType: 'html',
		data: {'clasif':data1}
	})
	.done(function(x) {
		$("#cursos").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	
}

$("#clasificacion").change(function(event) {
var data1 = $("#clasificacion").val();
slccurso(data1);
});
function slcdeps(){
	$.ajax({
		url: '../RI/php/slcdepartamento.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#dep").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	
}slcdeps();
function slcdepsreal(){
	$.ajax({
		url: '../RI/php/slcdptoreal.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#depsreal").html(x);
	})
	.fail(function() {
		console.log("error");
	})
	
}slcdepsreal();
function consulta(){
	event.preventDefault();
	var data1=$("#cursos").val();
	var data2=$("#depsreal").val();
	var data3=$("#fecha").val();
	var data4=$("#dep").val();
	var induccion=0;
	var reinduccion=0;
	var filano=0;
	if($("#induccion").prop('checked')){
		induccion=1;
	}
	if($("#reinduccion").prop('checked')){
		reinduccion=1;
	}
	if($("#filano").prop('checked')){
		filano=1;
	}
	if(data1=="" || (data2=="" && data4=="") || data3==""){
		$("#respuesta").html("Por favor selecciona un curso, un departamento y la fecha");
		$("#tblreporte").html("");
	}else{
		$.ajax({
		url: 'php/tblreporte.php',
		type: 'POST',
		dataType: 'html',
		data: {'curso':data1,'depreal':data2,'fecha':data3,'dep':data4,'induccion':induccion,'reinduccion':reinduccion,'filano':filano}
	})
	.done(function(x) {
		$("#tblreporte").html(x);
		$("#respuesta").html("");
	})
	.fail(function() {
		console.log("error");
	})
	}
}