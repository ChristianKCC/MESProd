function llnarformcapa(){
  $.ajax({
    type: 'POST',
    url: 'php/slc.php?dtk'
  })
  .done(function(listas_rep){
    $('#IdDTK').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })

  $.ajax({
    type: 'POST',
    url: 'php/slc.php?depto'
  })
  .done(function(listas_rep){
    $('#NoDepto').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })

  $.ajax({
    type: 'POST',
    url: 'php/slc.php?fuente'
  })
  .done(function(listas_rep){
    $('#IdFuente').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })

  $.ajax({
    type: 'POST',
    url: 'php/slc.php?clariesgo'
  })
  .done(function(listas_rep){
    $('#IdClaseRiesgo').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })

   $.ajax({
    type: 'POST',
    url: 'php/slc.php?tipocauda'
  })
  .done(function(listas_rep){
    $('#IdTipoCausa').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
$.ajax({
    type: 'POST',
    url: 'php/slc.php?emp'
  })
  .done(function(listas_rep){
    $('#NoEmp').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
    $.ajax({
    type: 'POST',
    url: 'php/slc.php?descausa'
  })
  .done(function(listas_rep){
    $('#IdDescripcionCausa').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
  $.ajax({
    type: 'POST',
    url: 'php/slc.php?mcm'
  })
  .done(function(listas_rep){
    $('#IdMCM').html(listas_rep);
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
llnarformcapa();
function slcseveridad($id){
  var data1 = $("#IdMCM").val();
   $.ajax({
    type: 'POST',
    url: 'php/slc.php?severidad',
    data: {'id':data1,'selected':$id}
  })
  .done(function(listas_rep){
    $('#severidad').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function slcprobabilidad($id){
  var data1 = $("#IdMCM").val();
   $.ajax({
    type: 'POST',
    url: 'php/slc.php?probabilidad',
    data: {'id':data1,'selected':$id}
  })
  .done(function(listas_rep){
    $('#probabilidad').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function slcdeteccion($id){
  var data1 = $("#IdMCM").val();
   $.ajax({
    type: 'POST',
    url: 'php/slc.php?deteccion',
    data: {'id':data1,'selected':$id}
  })
  .done(function(listas_rep){
    $('#deteccion').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function slcnumpersonas($id){
   $.ajax({
    type: 'POST',
    url: 'php/slc.php?numpersonas',
    data: {'selected':$id}
  })
  .done(function(listas_rep){
    $('#noexpuetas').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function slcmaquina($id,$id2){
   var data1 = $("#NoDepto").val();
  $.ajax({
    type: 'POST',
    url: 'php/slc.php?maquinas',
    data: {'id':data1,'selected':$id}
  })
  .done(function(listas_rep){
    $('#NoMaquina').html(listas_rep);
    slcseccion($id2);
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function slcseccion($id){
  var data1 = $("#NoMaquina").val();
$.ajax({
    type: 'POST',
    url: 'php/slc.php?secciones',
    data: {'id':data1,'selected':$id}
  })
  .done(function(listas_rep){
    $('#NoSeccion').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function slctipofuente($id){
var data1 = $("#IdFuente").val();
 $.ajax({
    type: 'POST',
    url:  'php/slc.php?tipofuente',
    data: {'id':data1,'selected':$id}
  })
  .done(function(listas_rep){
    $('#IdTipoFuente').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function tblcapa($id){
  $.ajax({
    url: 'php/tbl.php?capas',
    type: 'POST',
    dataType: 'html',
    data:{'key':$id}
  })
  .done(function(x) {
    $("#resulttbl").html(x);
    enviaaemp();
  })
  .fail(function() {
    console.log("error");
  })
}tblcapa("");
function tblcapasm(){
  $.ajax({
    type: 'POST',
    url: 'php/tbl.php?capascst'
  })
  .done(function(listas_rep){
    $('#tblcapas').html(listas_rep);
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
tblcapasm();

function asigusauariocapa(){
  $.ajax({
    type: 'POST',
    url: 'php/slc.php?emp'
  })
  .done(function(listas_rep){
    $('#asigusauariocapa').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}asigusauariocapa();

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
  })
}
function vercaparesumen($id){
  $("#modalcapa").modal("toggle");
  $.ajax({
    url: 'php/vercaparesumen.php',
    type: 'POST',
    dataType: 'html',
    data: {'id': $id},
  })
  .done(function(x) {
    $("#contenidomodal").html(x);
  })
  .fail(function() {
    console.log("error");
  })
}
function eliminarcapa($id){
$.ajax({
    type: 'POST',
    url: 'php/altcapa.php?eliminacapa',
    data: {'id':$id}
  })
  .done(function(listas_rep){
    tblcapa("");
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

function sendcapa($id){
  var data1=$("#enviaaemp").val();
  if(data1==''){
    alert("Por favor selecciona un líder.");
  }else{
$.ajax({
    type: 'POST',
    url: 'php/altcapa.php?terminacapa',
    data: {'id':$id,'enviaaemp':data1}
  })
  .done(function(listas_rep){
    tblcapa("");
  })
  .fail(function(){
    alert('Hubo un error')
  })
  }
}
function enviaaemp(){
$.ajax({
    type: 'POST',
    url: 'php/slc.php?enviaaemp',
  })
  .done(function(x){
   $("#enviaaemp").html(x);
  })
  .fail(function(){
    alert('Hubo un error')
  })
  }

function nuevacapa(val){
    $('form')[0].reset();
    $("#folio").val("");
    $("#resultact").html("");
    $("#resultinvestigacion").html("");
    $("#resultencabezado").html("");
    $("#resultcausas").html("");
    $("#descripcionprio").html("");
    $("#fileconfirmado").html("");
    $("form").find("select").val("").change();
    if(val==1){
    $("#confirmado").prop('checked',false).change();
    }
}

function llnarctacapa(data1){
$.ajax({
  url: 'php/consultacapa.php',
  type: 'POST',
  dataType: 'html',
  data: {'id':data1}
})
.done(function(x) {
$("#resultencabezado").html(x);
})
.fail(function() {
  console.log("error");
})
}

// tipoinforme

function informemayor($id){
  $("#modalcapa").modal("toggle");
  $.ajax({
    url: 'php/informemayor.php',
    type: 'POST',
    dataType: 'html',
    data: {'id': $id}
  })
  .done(function(x) {
    $("#contenidomodal").html(x);
    cargaempleados();
    confinvestigacion();
    tblinvestigacion();
    guardarinv();
  })
  .fail(function() {
    console.log("error");
  })
}

function cargaempleados(){
  $.ajax({
    type: 'POST',
    url: 'php/slc.php?emp'
  })
  .done(function(listas_rep){
    $('#operabaempleados').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}cargaempleados();


function confinvestigacion(){
$("#confirmado").change(function(){
  if($("#confirmado").prop('checked')){
    $("#tipohipodesc").html("Definición del problema ");  
    $("#fileconfirmado").html('<small class="fw-bold">Agrega la evidencia</small><input type="file" id="archivo" class="form-control" accept="application/pdf">');    
  }else{
    $("#tipohipodesc").html("Hipotesis");     
    $("#fileconfirmado").html('');    
  }
})
}

function tblinvestigacion(){
  var data1=$("#idcapa").val();
    $.ajax({
    type: 'POST',
    url: 'php/tbl.php?investigacion',
    data: {'id':data1}
  })
  .done(function(listas_rep){
    $('#tblinvestigacion').html(listas_rep);
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
  
function llnarinv(data1){
$.ajax({
  url: 'php/consultainv.php',
  type: 'POST',
  dataType: 'html',
  data: {'id':data1}
})
.done(function(x) {
$("#resultinvestigacion").html(x);
})
.fail(function() {
  console.log("error");
})
}

$("#buscarinvestigacion").keyup(function(event) {
var data1=$("#buscarinvestigacion").val();
tblcapa(data1);
});
tblcapa("");
$("#IdMCM").change(function(){
slcseveridad();
slcnumpersonas();
slcdeteccion();
slcprobabilidad();
})
$("#NoDepto").on('change',function(){
 slcmaquina();
})
$("#NoMaquina").on('change',function(){
  slcseccion();
})
$("#IdFuente").change(function(){
  slctipofuente();
})

$("#guardar").on('click',function(x){
  x.preventDefault();
  var data1 =$("#folio").val();
  var data2 =$("#fecha").val();
  var data3 =$("#NoDepto").val();
  var data4 =$("#NoMaquina").val();
  var data5 =$("#NoSeccion").val();
  var data6 =$("#IdMCM").val();
  var data7 =$("#IdFuente").val();
  var data8 =$("#IdTipoFuente").val();
  var data9 =$("#severidad").val();
  var data10 =$("#probabilidad").val();
  var data11 =$("#deteccion").val();
  var data12 =$("#descripcioncapa").val();
  var data15 =$("#noexpuetas").val();
  var data16 =$("#asigusauariocapa").val();
  if(data2==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Agrega una fecha</div>");
  }else if(data3==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Selecciona el departamento</div>");
  }else if(data4==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Selecciona una máquina</div>");
  }else if(data5==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Selecciona la sección</div>");
  }else if(data6==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Elige la responsabilidad</div>");
  }else if(data7==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Selecciona una fuente</div>");
  }else if(data8==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Selecciona el tipo de fuente</div>");
  }else if(data9=="" || data10=="" || data11==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Los campos severidad, probabilidad y detección no pueden estar vacíos</div>");
  }else if(data12==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Agrega la descripción de la revisión</div>");
  }else if(data15==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Selecciona el número de personas expuestas</div>");
  }else if(data16==""){
      $("#resultencabezado").html("<div class='alert alert-danger' role='alert'>Selecciona el usuario al que será asignada la investigación.</div>");
  }
  else{
    if(data1==''){
     $.ajax({
    type: 'POST',
    url: 'php/guardar.php?encabezado',
    data: {'fecha':data2,'NoDepto':data3,'NoMaquina':data4,'NoSeccion':data5,'IdMCM':data6,'IdFuente':data7,'IdTipoFuente':data8,
    'severidad':data9,'probabilidad':data10,'deteccion':data11,'descripcioncapa':data12,'noexpuestas':data15,'asignado':data16}
  })
  .done(function(x){
    $('#resultencabezado').html(x);
    $('#formcapa')[0].reset();
    tblcapa();
    tblcapasm();
  })
  .fail(function(){
    alert('Hubo un error')
  })
}else{
   $.ajax({
    type: 'POST',
    url: 'php/altcapa.php?modcapaenc',
    data: {'folio':data1,'fecha':data2,'NoDepto':data3,'NoMaquina':data4,'NoSeccion':data5,'IdMCM':data6,'IdFuente':data7,'IdTipoFuente':data8,
    'severidad':data9,'probabilidad':data10,'deteccion':data11,'descripcioncapa':data12,'noexpuestas':data15,'asignado':data16}
  })
  .done(function(x){
    $('#resultencabezado').html(x);
    $('#formcapa')[0].reset();
    $("#formcapa").find("select").val("").change();
    tblcapa();
    tblcapasm();
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
}
})
function guardarinv(){
$("#guardarinvestigacion").click(function(x){
  x.preventDefault();
  var formData = new FormData();
  var folioinv=$("#folioinv").val();
  var idcapa=$("#idcapa").val();
  var data1=$("#quesuc").val();
  var data2=$("#cuandosuc").val();
  var data3=$("#comosuc").val();
  var data4=$("#porquesuc").val();
  var data5=$("#dondesuc").val();
  var data6=$("#operabaempleados").val();
  var data7=$("#cuantasveces").val();
  var data8=$("#confirmado").val();
  var data9=$("#descripcion").val();
  if(data1=="" || data3=="" || data4=="" || data5=="" || data6=="" || data7=="" || data8=="" || data9=="" || idcapa==""){
      $("#resultinvestigacion").html("<div class='alert alert-danger my-2' role='alert'>No puede haber campos vacios.</div>");
  }else{
    if($("#confirmado").prop('checked')){
    data8=1;
    var valarc=$("#valarch").val();
    if(valarc==1){
    data8=2;
    }
    else{
    if(data8==2){
    }else{
    var files = $('#archivo')[0].files[0];
    formData.append('file',files);
    }
    }
    }
    formData.append('folioinv',folioinv);
    formData.append('idcapa',idcapa);
    formData.append('quesuc',data1);
    formData.append('cuandosuc',data2);
    formData.append('comosuc',data3);
    formData.append('porquesuc',data4);
    formData.append('dondesuc',data5);
    formData.append('operabaempleados',data6);
    formData.append('cuantasveces',data7);
    formData.append('confirmado',data8);
    formData.append('descripcion',data9);
  if(folioinv == ''){
  $.ajax({
    url: 'php/guardar.php?investigacion',
    type: 'POST',
    dataType: 'html',
    data: formData,
    cache: false,
    contentType: false,
    processData: false
  })
  .done(function(x) {
    $("#resultinvestigacion").html(x);
    $('#forminf')[0].reset();
     $("#confirmado").prop('checked',false).change();
    tblinvestigacion();
    tblcapa();
  })
  .fail(function() {
    console.log("error");
  })
  .always(function() {
    console.log("complete");
  });
  }else{
    $.ajax({
    url: 'php/altcapa.php?investigacionedit',
    type: 'POST',
    dataType: 'html',
    data: formData,
    cache: false,
    contentType: false,
    processData: false
  })
  .done(function(x) {
    $("#resultinvestigacion").html(x);
    $('#forminf')[0].reset();
    $("#confirmado").prop('checked',false).change();
    tblinvestigacion();
  })
  .fail(function() {
    console.log("error");
  })
  .always(function() {
    console.log("complete");
  });
  }
  }
})
}
function eliminainvestigacion($id){
  $.ajax({
    type: 'POST',
    url: 'php/altcapa.php?deleteinvestigacion',
    data: {'id':$id}
  })
  .done(function(listas_rep){
    $('#resultinvestigacion').html(listas_rep);
    tblinvestigacion();
    tblcapa();
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

// Analisis de caudad
function analisis($id){
  $("#modalcapa").modal("toggle");
  $.ajax({
    url: 'php/capaanalisisdecausas.php',
    type: 'POST',
    dataType: 'html',
    data: {'id': $id},
  })
  .done(function(x) {
    $("#contenidomodal").html(x);
    elemento();
    prioridad();
    causaraiz();
    prioridadchg();
    guardarcausas();
    tblcausas();
  })
  .fail(function() {
    console.log("error");
  })
}
function elemento(){
    $.ajax({
    type: 'POST',
    url: 'php/slc.php?elemento'
  })
  .done(function(listas_rep){
    $('#elemento').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

function practicas(){
  $.ajax({
    type: 'POST',
    url: 'php/slc.php?practicas'
  })
  .done(function(listas_rep){
    $('#causaimediata').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

function prioridad(){
      $.ajax({
    type: 'POST',
    url: 'php/slc.php?prioridad'
  })
  .done(function(listas_rep){
    $('#prioridad').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

function causaraiz(){
      $.ajax({
    type: 'POST',
    url: 'php/slc.php?causaraiz'
  })
  .done(function(listas_rep){
    $('#causaraiz').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

function prioridadchg(){
  $("#prioridad").change(function(){
  var data1=$("#prioridad").val();
  $.ajax({
    type: 'POST',
    url: 'php/slc.php?descripcionprio',
    data: {"id":data1}
  })
  .done(function(listas_rep){
    $('#descripcionprio').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
})
}

function tblcausas(){
  var data1=$("#idcapa").val();
    $.ajax({
    type: 'POST',
    url: 'php/tbl.php?causas',
    data: {'id':data1}
  })
  .done(function(listas_rep){
    $('#tblcausas').html(listas_rep);
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

function llnaranal(data1){
$.ajax({
  url: 'php/consultaanal.php',
  type: 'POST',
  dataType: 'html',
  data: {'id':data1}
})
.done(function(x) {
$("#resultcausas").html(x);
})
.fail(function() {
  console.log("error");
})
}

function guardarcausas(){
 $("#guardarcausa").click(function(){
  var folioanal=$("#folioanal").val();
  var idcapa=$("#idcapa").val();
  var data1=$("#elemento").val();
  var data2=$("#1porque").val();
  var data3=$("#2porque").val();
  var data4=$("#3porque").val();
  var data5=$("#4porque").val();
  var data6=$("#5porque").val();
  var data7=$("#causaimediata").val();
  var data8=$("#prioridad").val();
  var data10=$("#causaraiz").val();
  var data11=$("#tipocuentaraiz").val();
if(data3=="" || data4=="" || data5=="" || data6=="" || data7=="" || data8=="" || data10=="" || data11=="" || idcapa==""){
      $("#resultcausas").html("<div class='alert alert-danger my-2' role='alert'>No puede haber campos vacios.</div>");
}else{
  if(folioanal==''){
  $.ajax({
    url: 'php/guardar.php?causas',
    type: 'POST',
    dataType: 'html',
    data: {'elemento':data1,'1porque':data2,'2porque':data3,'3porque':data4,'4porque':data5,'5porque':data6,'causaimediata':data7,'prioridad':data8,'causaraiz':data10,'tipocuentaraiz':data11,'idcapa':idcapa},
  })
  .done(function(x) {
    $("#resultcausas").html(x);
    $('#formcau')[0].reset();
    $('#descripcionprio').html("");
    tblcausas();
    tblcapa();
  })
  .fail(function() {
    console.log("error");
  });
  }else{
      $.ajax({
    url: 'php/altcapa.php?causasedit',
    type: 'POST',
    dataType: 'html',
    data: {'folioanal':folioanal,'elemento':data1,'1porque':data2,'2porque':data3,'3porque':data4,'4porque':data5,'5porque':data6,'causaimediata':data7,'prioridad':data8,'causaraiz':data10,'tipocuentaraiz':data11,'idcapa':idcapa},
  })
  .done(function(x) {
    $("#resultcausas").html(x);
    $('#formcau')[0].reset();
    $('#descripcionprio').html("");
    tblcausas();
    tblcapa();
  })
  .fail(function() {
    console.log("error");
  });
  }
  }
}) 
}

function eliminacausas($id){
  $.ajax({
    type: 'POST',
    url: 'php/altcapa.php?deletecausas',
    data: {'id':$id}
  })
  .done(function(listas_rep){
    $('#resultcausas').html(listas_rep);
    tblcausas();
    tblcapa();
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

// acciones correctivas
function acciones($id){
  $("#modalcapa").modal("toggle");
  $.ajax({
    url: 'php/accionescp.php',
    type: 'POST',
    dataType: 'html',
    data: {'id': $id},
  })
  .done(function(x) {
    $("#contenidomodal").html(x);
    reponsable();
    tipoaccion();
    atacarcausa();
    tblacciones();
    guardaracp();
    practicas();
  })
  .fail(function() {
    console.log("error");
  })
}

function reponsable(){
    $.ajax({
    type: 'POST',
    url: 'php/slc.php?emp',
  })
  .done(function(listas_rep){
    $('#responsable').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function tipoaccion(){
    $.ajax({
    type: 'POST',
    url: 'php/slc.php?tipoacp',
  })
  .done(function(listas_rep){
    $('#tipoaccionc').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

function atacarcausa(){
    var data1=$("#idcapa").val();
    $.ajax({
    type: 'POST',
    url: 'php/slc.php?atacarcausa',
    data: {'id':data1}
  })
  .done(function(listas_rep){
    $('#causaraiz').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function tblacciones(){
  var data1=$("#idcapa").val();
    $.ajax({
    type: 'POST',
    url: 'php/tbl.php?acp',
    data:{'id':data1}
  })
  .done(function(listas_rep){
    $('#tblacciones').html(listas_rep);
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function acpllnar(data1){
$.ajax({
  url: 'php/consultaacp.php',
  type: 'POST',
  dataType: 'html',
  data: {'id':data1}
})
.done(function(x) {
$("#resultact").html(x);
})
.fail(function() {
  console.log("error");
})
}

function guardaracp(){
  $("#guardaracciones").click(function(){
  var folioacp=$("#folioacp").val();
  var data1=$("#causaraiz").val();
  var data2=$("#tipoaccionc").val();
  var data3=$("#responsable").val();
  var data4=$("#fechacompromiso").val();
  var data5=$("#actividad").val();
  var data6=$("#causaimediata").val();
    var hoy = new Date();
    var dd = hoy.getDate()+1;
    var mm = hoy.getMonth()+1;
    var yyyy = hoy.getFullYear();
    if(dd<10) 
    dd='0'+dd;
    if(mm<10) 
    mm='0'+mm;
    var fecha=yyyy+"-"+mm+"-"+dd;
if(data1=="" || data2=="" || data3=="" || data4=="" || data5=="" || data6==""){
      $("#resultact").html("<div class='alert alert-danger my-2' role='alert'>No puede haber campos vacíos.</div>");
}
else if(data4<=fecha){
      $("#resultact").html("<div class='alert alert-danger my-2' role='alert'>La fecha compromiso debe ser por lo menos 2 días después a la fecha actual.</div>");
}
else{
  if(folioacp==''){
  $.ajax({
    url: 'php/guardar.php?acp',
    type: 'POST',
    dataType: 'html',
    data: {'causaraiz':data1,'tipoaccionc':data2,'responsable':data3,'fechacompromiso':data4,'actividad':data5,'causaimediata':data6},
  })
  .done(function(x) {
    $("#resultact").html(x);
    tblacciones();
    tblcapa();
  })
  .fail(function() {
    console.log("error");
  });
  }else{
     $.ajax({
    url: 'php/altcapa.php?acpedit',
    type: 'POST',
    dataType: 'html',
    data: {'folioacp':folioacp,'causaraiz':data1,'tipoaccionc':data2,'responsable':data3,'fechacompromiso':data4,'actividad':data5,'causaimediata':data6},
  })
  .done(function(x) {
    $("#resultact").html(x);
    tblacciones();
    tblcapa();
  })
  .fail(function() {
    console.log("error");
  });
  }
}
})
}

function eliminaacciones($id){
  $.ajax({
    type: 'POST',
    url: 'php/altcapa.php?deleteacp',
    data: {'id':$id}
  })
  .done(function(listas_rep){
    $('#resultact').html(listas_rep);
    tblacciones();
    tblcapa();
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

// Informe menor

function informemenor($id){
  $("#modalcapa").modal("toggle");
  $.ajax({
    url: 'php/informemenor.php',
    type: 'POST',
    dataType: 'html',
    data: {'id': $id},
  })
  .done(function(x) {
    $("#contenidomodal").html(x);
    cargaempleados();
    confinvestigacion();
    guardarinv();
    tblinvestigacion();
  })
  .fail(function() {
    console.log("error");
  });
}

function accionesmenor($id){
  $("#modalcapa").modal("toggle");
  $.ajax({
    url: 'php/accionescpmenor.php',
    type: 'POST',
    dataType: 'html',
    data: {'id': $id},
  })
  .done(function(x) {
    $("#contenidomodal").html(x);
    reponsable();
    tipoaccion();
    tblaccionesmenor();
    guardaracpm();
  })
  .fail(function() {
    console.log("error");
  })
}
function tblaccionesmenor(){
  var data1=$("#idcapa").val();
    $.ajax({
    type: 'POST',
    url: 'php/tbl.php?acpmenor',
    data:{'id':data1}
  })
  .done(function(listas_rep){
    $('#tblaccionesmenor').html(listas_rep)
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function eliminaaccionesmenor($id){
  $.ajax({
    type: 'POST',
    url: 'php/altcapa.php?eliminaracpmenor',
    data: {'id':$id}
  })
  .done(function(listas_rep){
    $('#resultact').html(listas_rep);
    tblaccionesmenor();
  })
  .fail(function(){
    alert('Hubo un error')
  })
}

function guardaracpm(){
  $("#guardaraccionesmenor").click(function(){
  var data1=$("#idcapa").val();
  var data2=$("#tipoaccionc").val();
  var data3=$("#responsable").val();
  var data4=$("#fechacompromiso").val();
  var data5=$("#actividad").val();
if(data1=="" || data3=="" || data4=="" || data5==""){
      $("#resultact").html("<div class='alert alert-danger my-2' role='alert'>No puede haber campos vacíos.</div>");
}else{
  $.ajax({
    url: 'php/guardar.php?acpmenor',
    type: 'POST',
    dataType: 'html',
    data: {'idcapa':data1,'tipoaccionc':data2,'responsable':data3,'fechacompromiso':data4,'actividad':data5},
  })
  .done(function(x) {
    $("#resultact").html(x);
    tblaccionesmenor();
  })
  .fail(function() {
    console.log("error");
  })
  .always(function() {
    console.log("complete");
  });
  }
})
}
function vercorrecciones($id){
  $("#modalcapa").modal("toggle");
  $.ajax({
    url: 'php/vercorrecciones.php',
    type: 'POST',
    dataType: 'html',
    data: {'id': $id},
  })
  .done(function(x) {
    $("#contenidomodal").html(x);
  })
  .fail(function() {
    console.log("error");
  })
}