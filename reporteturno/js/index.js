$("#guardar").click(function(event) {
	event.preventDefault();
	var folio = $("#folio").val();
	var data1 = $("#turnoenc").val();
	var data2 = $("#deps").val();
	if(data1=="" || data2==""){
		$("#resultadoenc").html("<div class='alert alert-danger'>No puede haber campos vacíos</div>");
	}else{
	if (folio=='') {
	$.ajax({
		url: 'php/guardar.php?enc',
		type: 'POST',
		dataType: 'html',
		data: {'turno': data1,'deps': data2}
	})	
	.done(function(x) {
		$("#resultadoenc").html(x);
		$('form').trigger("reset");
		tblrepenc();
	})
	.fail(function() {
		console.log("error");
	});
	}
	else{
		$.ajax({
		url: 'php/altinfo.php?editencguardar',
		type: 'POST',
		dataType: 'html',
		data: {'id': folio,'turno': data1,'deps': data2}
	}).done(function(x) {
		$("#resultadoenc").html(x);
		$('form').trigger("reset");
		tblrepenc();
	})
	.fail(function() {
		console.log("error");
	});
	}
	}
});

$("#terminar").click(function(event) {
	event.preventDefault();
	var folio = $("#folio").val();
	if(folio==""){
		$("#resultadoenc").html("<div class='alert alert-danger'>Selecciona un folio</div>");
		$("#subenc").html("");
	}else{
	$.ajax({
		url: 'php/altinfo.php?finalizarrep',
		type: 'POST',
		dataType: 'html',
		data: {'id': folio}
	})	
	.done(function(x) {
		$("#resultadoenc").html(x);
		$("#subenc").html("");
		$('form').trigger("reset");
		tblrepenc();
	})
	.fail(function() {
		console.log("error");
	});
	}
});
(async function deps(){
	const respuestaraw = await fetch("../Components/CatalogoPersonal.php?GetSlcDeps");
	const respuesta = await respuestaraw.json();
	let body = "<option value=''>Selecciona un departamento</option>";
	respuesta.forEach(element => {
		body += `<option value="${element.id}">${element.nombre}</option>`;
	}); 
	document.getElementById("deps").innerHTML = body;
})();

function tblrepenc(){
		$.ajax({
		url: 'php/tbl.php?enc',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#tblrepturno").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}tblrepenc();
function editenc($id){
		$.ajax({
		url: 'php/altinfo.php?editenc',
		type: 'POST',
		dataType: 'html',
		data: {'id':$id}
	})
	.done(function(x) {
		$("#resultadoenc").html(x);
	})
	.fail(function() {
		console.log("error");
	});
}function eliminarenc($id){
		$.ajax({
		url: 'php/altinfo.php?deleteenc',
		type: 'POST',
		dataType: 'html',
		data: {'id':$id}
	})
	.done(function(x) {
		$("#resultadoenc").html(x);
		tblrepenc();
	})
	.fail(function() {
		console.log("error");
	});
}
// seguridad
	$("#seguridad").click(function(event) {
	event.preventDefault();
	var folioenc=$("#folio").val();
	if(folioenc==""){
		$("#resultadoenc").html("<div class='alert alert-danger'>Edita un folio para agregar la información faltante</div>");
	}else{
	$.ajax({
		url: 'php/form.php?seguridad',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#subenc").html(x);
	})
	.fail(function() {
		console.log("error");
	});
	}
	});
	// Relaciones industriales
	$("#ri").click(function(event) {
	event.preventDefault();
	var folioenc=$("#folio").val();
	if(folioenc==""){
		$("#resultadoenc").html("<div class='alert alert-danger'>Edita un folio para agregar la información restante</div>");
		$("#subenc").html("");
	}else{
	$.ajax({
		url: 'php/form.php?ri',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#subenc").html(x);
		 emp();
		 tblri();
		 guardarri();
	})
	.fail(function() {
		console.log("error");
	});
	}
	});
	function emp(){
	$.ajax({
		url: '../capa/php/slc.php?emp',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#noemp").html(x);
	})
	.fail(function() {
		console.log("error");
	});
	}
	function tblri(){
	var folioenc=$("#folio").val();
	$.ajax({
		url: 'php/tbl.php?tblri',
		type: 'POST',
		dataType: 'html',
		data: {'id':folioenc}
	})
	.done(function(x) {
		$("#tblri").html(x);
	})
	.fail(function() {
		console.log("error");
	});
	}
	function eliminarri($id){
		event.preventDefault()
		$.ajax({
		url: 'php/altinfo.php?deleteri',
		type: 'POST',
		dataType: 'html',
		data: {'id':$id}
	})
	.done(function(x) {
		tblri();
	})
	.fail(function() {
		console.log("error");
	});
	}
	function guardarri(){
	$("#guardarri").click(function(event) {
	event.preventDefault();
	var data2 = $("#noemp").val();
	var folio = $("#folio").val();
	if(folio==""){
		$("#resultadosub").html("<div class='alert alert-danger'>Selecciona un folio</div>");
	}else if(data2==""){
		$("#resultadosub").html("<div class='alert alert-danger'>No puede haber campos vacíos</div>");
	}
	else{
	$.ajax({
		url: 'php/guardar.php?ri',
		type: 'POST',
		dataType: 'html',
		data: {'folio': folio,'noemp': data2}
	})	
	.done(function(x) {
		$("#resultadosub").html(x);
		$('#formri').trigger("reset");
		tblri();
	})
	.fail(function() {
		console.log("error");
	});
	}
	});
	}
	
	// Pendientes mecanicos
	$("#pmecanico").click(function(event) {
	event.preventDefault();
	var folioenc=$("#folio").val();
	if(folioenc==""){
		$("#resultadoenc").html("<div class='alert alert-danger'>Edita un folio para agregar la información restante</div>");
		$("#subenc").html("");
	}else{
	$.ajax({
		url: 'php/form.php?pmecanico',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#subenc").html(x);
		maquinas();
		seccionchg();
		tblpmecanicos();
		guardarpm();
	})
	.fail(function() {
		console.log("error");
	});
	}
	});
	function tblpmecanicos(){
	var folioenc=$("#folio").val();
	$.ajax({
		url: 'php/tbl.php?tblpmecanicos',
		type: 'POST',
		dataType: 'html',
		data: {'id':folioenc}
	})
	.done(function(x) {
		$("#tblpmecanicos").html(x);
	})
	.fail(function() {
		console.log("error");
	});
	}
	function maquinas(){
		var data1=$("#deps").val();
	$.ajax({
		url: '../capa/php/slc.php?maquinas',
		type: 'POST',
		dataType: 'html',
		data:{'id':data1}
	})
	.done(function(x) {
		$("#maquinas").html(x);
	})
	.fail(function() {
		console.log("error");
	});
	}
	function guardarpm(){
	$("#guardarpm").click(function(event) {
	event.preventDefault();
	var folio = $("#folio").val();
	var data2 = $("#maquinas").val();
	var data3 = $("#secciones").val();
	var data4 = $("#descpend").val();
	var data5 = $("#tipopendiente").val();
	if(folio==""){
		$("#resultadosub").html("<div class='alert alert-danger'>Selecciona un folio</div>");
	}else if(data2=="" || data3=="" || data4=="" || data5==""){
		$("#resultadosub").html("<div class='alert alert-danger'>No puede haber campos vacíos</div>");
	}else{
	$.ajax({
		url: 'php/guardar.php?guardarpm',
		type: 'POST',
		dataType: 'html',
		data: {'folio': folio,'maquinas': data2,'secciones': data3,'descpend': data4,'tipopendiente': data5}
	})	
	.done(function(x) {
		$("#resultadosub").html(x);
		$('#formpm').trigger("reset");
		tblri();
		tblpmecanicos();
	})
	.fail(function() {
		console.log("error");
	});
	}
	});
	}
	function eliminarpm($id){
		event.preventDefault()
		$.ajax({
		url: 'php/altinfo.php?deletepm',
		type: 'POST',
		dataType: 'html',
		data: {'id':$id}
	})
	.done(function(x) {
		tblpmecanicos();
	})
	.fail(function() {
		console.log("error");
	});
	}

	function seccion(){
 	var data1 = $("#maquinas").val();
	$.ajax({
		url: '../capa/php/slc.php?secciones',
		type: 'POST',
		dataType: 'html',
    	data: {'id':data1}
	})
	.done(function(x) {
		$("#secciones").html(x);
	})
	.fail(function() {
		console.log("error");
	});
	}
	function seccionchg(){
	$("#maquinas").change(function(event) {
	seccion();
	});
	}

	// Comentarios
	
	$("#comentarios").click(function(event) {
	event.preventDefault();
	var folioenc=$("#folio").val();
	if(folioenc==""){
		$("#resultadoenc").html("<div class='alert alert-danger'>Edita un folio para agregar la información restante</div>");
		$("#subenc").html("");
	}else{
	$.ajax({
		url: 'php/form.php?comentarios',
		type: 'POST',
		dataType: 'html'
	})
	.done(function(x) {
		$("#subenc").html(x);
		 maquinas();
		 tblcomentarios();
		 guardarcom();
	})
	.fail(function() {
		console.log("error");
	});
	}
	});
	function tblcomentarios(){
	var folioenc=$("#folio").val();
	$.ajax({
		url: 'php/tbl.php?tblcomentarios',
		type: 'POST',
		dataType: 'html',
		data: {'id':folioenc}
	})
	.done(function(x) {
		$("#tblcomentarios").html(x);
	})
	.fail(function() {
		console.log("error");
	});
	}
	function guardarcom(){
	$("#guardarcom").click(function(event) {
	event.preventDefault();
	var folio = $("#folio").val();
	var data2 = $("#maquinas").val();
	var data4 = $("#descomentarios").val();
	if(folio==""){
		$("#resultadosub").html("<div class='alert alert-danger'>Selecciona un folio</div>");
	}else if(data2=="" || data4==""){
		$("#resultadosub").html("<div class='alert alert-danger'>No puede haber campos vacíos</div>");
	}else{
	$.ajax({
		url: 'php/guardar.php?guardarcom',
		type: 'POST',
		dataType: 'html',
		data: {'folio': folio,'maquinas': data2,'descomentarios': data4}
	})	
	.done(function(x) {
		$("#resultadosub").html(x);
		$('#formco').trigger("reset");
		 tblcomentarios();
	})
	.fail(function() {
		console.log("error");
	});
	}
	});
	}function eliminarco($id){
	event.preventDefault()
	$.ajax({
	url: 'php/altinfo.php?deleteco',
	type: 'POST',
	dataType: 'html',
	data: {'id':$id}
	})
	.done(function(x) {
		tblcomentarios();
	})
	.fail(function() {
		console.log("error");
	});
	}




	// Poo
	async function tblparosmaquina(){
		let folio = document.getElementById("folio").value;
		const formdata = new FormData();
		formdata.append("id",folio);
		const respuestaraw = await fetch("php/tbl.php?tblparosmaquina",{
			method: "POST",
			body: formdata
		});
		const respuesta = await respuestaraw.text();
		 document.getElementById("tblparosmaquina").innerHTML = respuesta;
	}
	document.getElementById("parosmaquina").addEventListener("click",function(event){
		event.preventDefault();
		let folio = document.getElementById("folio").value;
		if(folio === ''){
			$("#resultadoenc").html("<div class='alert alert-danger'>Edita un folio para agregar la información restante.</div>");
			return false;
		}
		(async ()=>{
			const respuestaraw = await fetch("php/form.php?parosmaquina");
			const respuesta = await respuestaraw.text();
			document.getElementById("subenc").innerHTML = respuesta;
			maquinas();
			seccionchg();
			tblparosmaquina();
		})().then(()=>{
			document.getElementById("guardarparomaquina").addEventListener("click",function(event){
				event.preventDefault();
			    let folio = document.getElementById("folio").value;
			    let maquinas = document.getElementById("maquinas").value;
			    let secciones = document.getElementById("secciones").value;
			    let hparo = document.getElementById("hparo").value;
			    let tperdido = document.getElementById("tperdido").value;
			    let comentarios = document.getElementById("comentariosparo").value;
				const formdata = new FormData();
				formdata.append("folio",folio);
				formdata.append("maquinas",maquinas);
				formdata.append("secciones",secciones);
				formdata.append("hparo",hparo);
				formdata.append("tperdido",tperdido);
				formdata.append("comentarios",comentarios);
				(async ()=>{
					const respuestaraw = await fetch("php/guardar.php?guardarparo",{
						method: "POST",
						body: formdata
					});
					const respuesta = await respuestaraw.text();
					document.getElementById("resultadosub").innerHTML = respuesta;
					tblparosmaquina();
				})();
			});
		});
		
		
	});


// Reporte

	function view(id){
	event.preventDefault();
  	$("#viewmodal").modal("toggle");
	$.ajax({
	url: 'php/reportes.php?view',
	type: 'POST',
	dataType: 'html',
	data: {'id':id}
	})
	.done(function(x) {
		$("#contmodal").html(x);
	})
	.fail(function() {
		console.log("error");
	});
	}

	function consulta(){
	event.preventDefault();
	var data1=$("#fechai").val();
	var data2=$("#fechaf").val();
	$.ajax({
	url: 'php/consultas.php?contulta',
	type: 'POST',
	dataType: 'html',
	data:{'fechai':data1,'fechaf':data2}
	})
	.done(function(x) {
		$("#tabla").html(x);
		chart();
	})
	.fail(function() {
		console.log("error");
	});
	}

	function chart(){
		  var cont = [];
      var x = [];
      var JSON=$.ajax({
              url:"php/canvasinfo.php",
              dataType: 'JSON',
           		async: false}).responseText;
              var Respuesta=jQuery.parseJSON(JSON);
              console.log(Respuesta);
      for (var i in Respuesta) {
          cont.push(Respuesta[i].Cont);
          x.push(Respuesta[i].x);
          }
      var chartdata = {
          labels: x,
          datasets: [
              {
                  label: 'Contador',
                  backgroundColor: '#06908f',
                  lineTension: 0,
                  fill: false,
                  data: cont
              }
          ]
      };
		new Chart(document.getElementById("bar-chart"), {
    type: 'bar',
    data: chartdata,
    options: {
      legend: { display: false },
      title: {
        display: true,
        text: 'Total de reportes cargados'
      }
    }
		});
	}