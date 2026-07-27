import { Toolsjs } from "../../Tools/Tools.js";

const tools = new Toolsjs();
tools.llnarslc('CatalogoPersonal','GetSlcDeps','dep',0);
tools.llnarslc('CatalogoPersonal','GetSlcDepsall','depsreal',0);
async function consultar(){
    // const curso = document.getElementById('cursos').value;
    // const departamento = document.getElementById('dep').value;
    // const departamentoreal = document.getElementById('depsreal').value;
    // const fecha = document.getElementById('fecha').value;
    // const filano = document.getElementById('filano').checked ? 1 : 0;
    // const induccion = document.getElementById('induccion').checked ? 1 : 0;
    // const reinduccion = document.getElementById('reinduccion').checked ? 1 : 0;
    // const respuesta = await fetch('./php/reportes.php',{
    //     method: 'POST',
    //     body: data
    // });
    // const respuestaraw = await respuesta.json();
    // console.log(respuestaraw);
    var data1=$("#cursos").val();
	var data2=$("#depsreal").val();
	var data3=$("#fecha").val();
	var data4=$("#dep").val();
	var induccion=1;
	var reinduccion=0;
	var filano=0;
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
document.getElementById('clasificacion').addEventListener('change',function(){
    const clasificacion = document.getElementById('clasificacion').value;
    tools.llnarslc('CatalogoCursos','GetSlcCursosxClasificacion&clasificacion='+clasificacion,'cursos',0);
})
document.getElementById('consultar').addEventListener('click',function(e){
    e.preventDefault();
    consultar();

})