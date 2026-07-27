function tblfechas(){
$.ajax({
	url: 'php/tblfechasretrasadas.php',
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

}tblfechas();
function autoriza($id,$idac){
$.ajax({
	url: 'php/autorizacambiofecha.php',
	type: 'POST',
	dataType: 'html',
	data:{'id':$id,'idac':$idac}
})
.done(function(x) {
	tblfechas();
	$("#errores").html(x);
})
.fail(function() {
	console.log("error");
})
.always(function() {
	console.log("complete");
});

}