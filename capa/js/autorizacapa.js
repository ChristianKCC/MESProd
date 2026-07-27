function capasautoriza(){
  $.ajax({
    type: 'POST',
    url: 'php/tbl.php?capasautoriza'
  })
  .done(function(listas_rep){
    $('#tblcapasautoriza').html(listas_rep);
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
capasautoriza();

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

function autorizacapa($id){
$.ajax({
    type: 'POST',
    url: 'php/altcapa.php?autorizacapa',
    data: {'id':$id}
  })
  .done(function(listas_rep){
    capasautoriza();
  })
  .fail(function(){
    alert('Hubo un error')
  })
}
function devolver($id){
$.ajax({
    type: 'POST',
    url: 'php/altcapa.php?devolvercapa',
    data: {'id':$id}
  })
  .done(function(listas_rep){
    capasautoriza();
  })
  .fail(function(){
    alert('Hubo un error')
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