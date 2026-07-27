function tblinf(){
event.preventDefault();
var data1 = $("#fechai").val();
var data2 = $("#fechaf").val();
$.ajax({
	url: 'php/tbl.php?tblenc',
	type: 'POST',
	dataType: 'html',
	data: {"fechai":data1 ,"fechaf":data2}
})
.done(function(x) {
	$("#tablerptparos").html(x);
})
.fail(function() {
	console.log("error");
});
}