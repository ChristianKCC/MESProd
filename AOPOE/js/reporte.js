function consultaxdate(){
	event.preventDefault();
	var data1=$("#fechai").val();
	var data2=$("#fechaf").val();
	var data3=$("#tipo").val();
	var data4=$("#departamento").val();
	var data5=$("#nomidpoe").val();
	var data6=$("#puesto").val();
$.ajax({
	url: 'php/reportes.php?date',
	type: 'POST',
	data: {'fechai': data1, 'fechaf':data2, 'tipo':data3, 'departamento':data4, 'nomidpoe':data5, 'puesto':data6},
})
.done(function(x) {
	$("#respuesta").html(x)
})
.fail(function() {
	console.log("Error");
});
}
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

function slcpuesto(){
	$.ajax({
		url: 'php/slc.php?puesto',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#puesto").html(x);
	})
	.fail(function() {
		console.log("Error de línea");
	})

}slcpuesto();