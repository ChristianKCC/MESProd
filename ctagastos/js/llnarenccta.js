$("#anticipoadd").click(function(event) {
	event.preventDefault();
	var data1=$("#folio").val();
	var data2=$("#cntanticipo").val();
	if(data1=='')
      $("#resultadoenc").html("<div class='alert alert-danger' role='alert'>Primero seleccione un folio.</div>");
    else{
   $.ajax({
		url: 'php/addanticipo.php',
		type: 'POST',
		dataType: 'html',
		data: {'folio': data1,'cntanticipo':data2}
	})
	.done(function(x) {
		$("#resultadoenc").html(x);
		tblconseptos();
	})
	.fail(function() {
		console.log("error");
	});
}
});
function llnrslcempleados(){
	$.ajax({
		url: 'php/slcempleadosllnarcap.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#empleados").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}
function llnrslcestados(){
	$.ajax({
		url: 'php/slcestados.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#estado").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}
function llnrcentrocostos(){
	$.ajax({
		url: 'php/cstcentrocostos.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#ctrocostos").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}
function tipodemoneda(){
	$.ajax({
		url: 'php/slctipomoneda.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#moneda").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}llnrslcestados();
tipodemoneda();
llnrslcempleados();
llnrcentrocostos();

function slcconseptos(){
	$.ajax({
		url: 'php/slcconseptos.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#conseptos").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}slcconseptos();


$("#conseptos").on('change',function(){
	var data1 = $("#folio").val();
	var data2 = $("#conseptos").val();
	if(data1==""){
      $("#resultadoenc").html("<div class='alert alert-danger' role='alert'>Primero seleccione un folio.</div>");
	}else{
	tblconseptos();
	}
	if(data2==2)
	$("#informacion").html("<small class='text-info'>Agrega a las personas que te acompañaron a comer.</small>");
	else if(data2==3)
	$("#informacion").html("<small class='text-info'>Se solicitará el km y el precio de la gasolina.</small>");
	else
	$("#informacion").html("");
})

$('#guardar').on('click',function(x){
	x.preventDefault();
	var validar=$("#folio").val();
	var data1=$("#empleados").val();
	var data2=$("#ctrocostos").val();
	var data3=$("#moneda").val();
	if(validar != ""){
      $("#resultadoenc").html("<div class='alert alert-danger' role='alert'>Estas editando un folio, limpia el formulario.</div>");
	}
	else if(data1=="" || data2==""){
      $("#resultadoenc").html("<div class='alert alert-danger' role='alert'>Debe estar seleccionado un usuario y el centro de costos.</div>");
	}
	else{
	$.ajax({
		url: 'php/guardarconsepto.php',
		type: 'POST',
		dataType: 'html',
		data:{'empleado':data1,'ctrocostos':data2,'moneda':data3}
	})
	.done(function(x) {
		$("#resultadoenc").html(x);
      $("#resultadosub").html("");
	})
	.fail(function() {
		console.log("error");
	});
	}
})

$('#modificar').on('click',function(x){
	x.preventDefault();
	var data1=$("#folio").val();
	var data2=$("#empleados").val();
	var data3=$("#ctrocostos").val();
	var data4=$("#moneda").val();
	if(data1 == ""){
      $("#resultadoenc").html("<div class='alert alert-danger' role='alert'>Primero selecciona un folio.</div>");
	}
	else if(data2=="" || data3==""){
      $("#resultadoenc").html("<div class='alert alert-danger' role='alert'>Debe estar seleccionado un usuario y el centro de costos.</div>");
	}else{
	$.ajax({
		url: 'php/editarencabezado.php',
		type: 'POST',
		dataType: 'html',
		data:{'folio':data1,'empleado':data2,'ctrocostos':data3,'moneda':data4}
	})
	.done(function(x) {
		$("#resultadoenc").html(x);
      $("#resultadosub").html("");
	})
	.fail(function() {
		console.log("error");
	});
}
})

$('#finalizar').on('click',function(x){
	x.preventDefault();
	var data1=$("#folio").val();
	if(data1 == "")
      $("#resultadoenc").html("<div class='alert alert-danger' role='alert'>Primero selecciona un folio.</div>");
	else{
	$.ajax({
		url: 'php/finalizan0.php',
		type: 'POST',
		dataType: 'html',
		data:{'id':data1}
	})
	.done(function(x) {
    limpiar();
		$("#resultadoenc").html(x);
    $("#resultadosub").html("");
    tblencabezado();
	})
	.fail(function() {
		console.log("error");
	});
}
})

$('#guardaconsepto').on('click',function(x){
	x.preventDefault();
	var formData = new FormData();
  var files = $('#archivo')[0].files[0];
    formData.append('file',files);
	var data1=$("#folio").val();
	var data2=$("#conseptos").val();
	var data3=$("#importe").val();
	var data4=$("#iva").val();
	var data5=$("#xml").val();
	var data6=$("#observaciones").val();
	var data7=$("#fecha").val();
    formData.append('folio',data1);
    formData.append('conseptos',data2);
    formData.append('importe',data3);
    formData.append('iva',data4);
    formData.append('xml',data5);
    formData.append('observaciones',data6);
    formData.append('fecha',data7);
	if(data1 == ""){
      $("#resultadosub").html("<div class='alert alert-danger' role='alert'>Primero elige un folio.</div>");
	}
	else if(data2==""){
      $("#resultadosub").html("<div class='alert alert-danger' role='alert'>Selecciona un concepto.</div>");
	}
	else if(data3=="" || data4=="" || data5==""){
      $("#resultadosub").html("<div class='alert alert-danger' role='alert'>Los campos importe, iva, folio xml deben estar llenos.</div>");
	}
	else if(data7==""){
      $("#resultadosub").html("<div class='alert alert-danger' role='alert'>Agrega la fecha del concepto</div>");
	}
	else{
	if(data2==3){
		var km = prompt("Escribe tu kilometraje");
		if(km=="" || isNaN(km) || km==false || km==null){
			alert("Lo siento, no ingresaste un numero valido");
			return false;
		}
		var gasolina = prompt("Escribe precio de gasolina");
		if(gasolina=="" || isNaN(gasolina) || gasolina==null){
			alert("Lo siento, no ingresaste un numero valido");
			return false;
		}

    formData.append('km',km);
    formData.append('gasolina',gasolina);
	}
	$.ajax({
		url: 'php/guardarconseptossub.php',
		 type: "post",
        dataType: "html",
        data: formData,
		cache: false,
        contentType: false,
        processData: false
	})
	.done(function(x) {
		$("#resultadosub").html(x);
		$("#fomrconcepto")[0].reset();
		tblconseptos();
	})
	.fail(function() {
		console.log("error");
	});
}
})

function tblconseptos(){
	var data1 = $("#folio").val();
	$.ajax({
		url: 'php/tblconseptos.php',
		type: 'POST',
		dataType: 'html',
		data:{'id':data1}
	})
	.done(function(x) {
		$("#tblconseptos").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}

function tblencabezado(){
	$.ajax({
		url: 'php/tblstctas.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#tblstctastbl").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}

$("#tblstctas").on('click',function(x){
	x.preventDefault();
	$("#modalstcta").modal("show");
	tblencabezado();
});

$('#eliminar').on('click', function(x){
	x.preventDefault();
	var data1 = $("#folio").val();
	if(data1 == ""){
      $("#resultadoenc").html("<div class='alert alert-danger' role='alert'>Primero selecciona un folio.</div>");
	}
	else{
    confirm("Eliminar cuenta");
    $.ajax({
      url: 'php/eliminarenc.php',
      type: 'POST',
      dataType: 'html',
      data: {'id':data1}
    })
    .done(function(x) {
		limpiar();
    $("#resultadoenc").html(x);
    tblencabezado()
    })
    .fail(function(){
      console.log("error");
    })
}
});

function limpiar(){
	$("#formcta")[0].reset();
	$("#fomrconcepto")[0].reset();
	$("#resultadoenc").html("");
	$("#resultadosub").html("");
	$("#tblconseptos").html("");
}
$("#nueva").click(function(event) {
	event.preventDefault();
	limpiar();
});

$(document).ready(function(){
	$("#tipodemoneda").hide();
})
$('#extranjero').on('change', function(x){
	if($('#extranjero').prop('checked')) {
	$("#tipodemoneda").show();
    }else{
	$("#tipodemoneda").hide();
}
})
function tbladmcta(){
	var data1 = $("#folio").val();
	$.ajax({
		url: 'php/tbladmonctas.php',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#tbladmonctas").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}tbladmcta();
function configcta($id){
	$("#modalviewcta").modal('toggle');
	$.ajax({
		url: 'php/admonctagastos.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id},
	})
	.done(function(x) {
		$("#result").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}

function cerrar0($id,$total){
	$.ajax({
		url: 'php/cerrar0.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id,'total': $total},
	})
	.done(function(x) {
		$("#encabezadoresult").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}

function finaliza($id){
	$.ajax({
		url: 'php/finalizan1.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id},
	})
	.done(function(x) {
		tbladmcta();
		$("#encabezadoresult").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}

function finaliza2($id){
	$.ajax({
		url: 'php/finalizan2.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id},
	})
	.done(function(x) {
		tbladmcta();
		$("#encabezadoresult").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}
function finaliza3($id){
	$.ajax({
		url: 'php/finalizan3.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id},
	})
	.done(function(x) {
		tbladmcta();
		$("#encabezadoresult").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}
function devolver($id){
	$.ajax({
		url: 'php/devolver.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id},
	})
	.done(function(x) {
		tbladmcta();
		$("#encabezadoresult").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}

function consultarreporte(){
	var data1=$("#ctrocostos").val();
	var data2=$("#conseptos").val();
	var data3=$("#estado").val();
	$.ajax({
		url: 'php/tblctagastos.php?conseptosindex',
		type: 'POST',
		dataType: 'html',
		data: {'ctrocostos': data1,'conseptos': data2,'estado': data3},
	})
	.done(function(x) {
		$("#tablectareporteconc").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}

$("#consultarreporte").click(function(event) {
	event.preventDefault();
consultarreporte();
ctatablereporte();
});