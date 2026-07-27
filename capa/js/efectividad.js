function tblefectividad(){
$.ajax({
	url: 'php/tbl.php?efectividad',
	type: 'POST',
	dataType: 'html'
})
.done(function(x) {
	$("#tblefectividad").html(x);
})
.fail(function() {
	console.log("error");
});
}tblefectividad();

function tblefectividadcalculos(){
var data1 = $("#foliocapa").val();
$.ajax({
	url: 'php/tbl.php?tblefectividadcalculos',
	type: 'POST',
	dataType: 'html',
	data: {'foliocapa':data1}
})
.done(function(x) {
	$("#tblefectividadcalculos").html(x);
})
.fail(function() {
	console.log("error");
});
}

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
}

function efectividad($id){
	$("#modalcapa").modal("toggle");
	$.ajax({
		url: 'php/efectividad.php',
		type: 'POST',
		dataType: 'html',
		data: {'id': $id},
	})
	.done(function(x) {
		$("#contenidomodal").html(x);
		caldatos();
		tblefectividadcalculos();
	})
	.fail(function() {
		console.log("error");
	});
}
function calc(){
   	var num1=$("#aac").val();
   	var num2=$("#dac").val();
   		$("#result").html((((num2/num1)-1)*100)*-1+"%");
   		var res=(((num2/num1)-1)*100);
   		res=res*-1;
   		return res;
   	}
   	function caldatos(){
   	$("#dac").keyup(function(){
  	 calc();
   	})
   	$("#aac").keyup(function(){
  	 calc();
   	})
   	$("#guardar").click(function(x){
   		x.preventDefault();
  	 	var res = calc();
  	 	var idcapa = $("#foliocapa").val();
  	 $.ajax({
  	 	url: 'php/guardarefectividad.php',
  	 	type: 'POST',
  	 	dataType: 'html',
  	 	data: {'calc':res,'idcapa':idcapa},
  	 })
  	 .done(function(x) {
  	 	$("#result").html(x);
  	 	tblefectividadcalculos();
  	 })
  	 .fail(function() {
  	 	console.log("error");
  	 });
  	 })
   }
