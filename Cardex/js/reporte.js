import { Toolsjs } from "../../Tools/Tools.js";
function slccardex() {
  $.ajax({
    url: "php/slccardex.php",
    type: "POST",
    dataType: "html",
  })
    .done(function (x) {
      $("#slccardex").html(x);
    })
    .fail(function () {
      console.log("error");
    })
    .always(function () {
      console.log("complete");
    });
}
slccardex();
function slcpuestos() {
  $.ajax({
    url: "../AOPOE/php/slc.php?puesto",
    type: "POST",
    dataType: "html",
  })
    .done(function (x) {
      $("#puestos").html(x);
    })
    .fail(function () {
      console.log("error");
    })
    .always(function () {
      console.log("complete");
    });
}
slcpuestos();

async function slccurso() {
  const respuestaraw = await fetch(
    "../Components/CatalogoCursos.php?GetSlcCursos"
  );
  const respuesta = await respuestaraw.json();
  let body = "<option value=''>Selecciona una opción</option>";
  respuesta.forEach((element) => {
    body += `<option value='${element.id}'>${element.nombre}</option>`;
  });
  document.getElementById("slccurso").innerHTML = body;
}
slccurso();

$("#noemp").on("blur", function () {
  $("#slcemp").val($("#noemp").val()).change();
});
$("#slcemp").change(function (event) {
  $("#noemp").val($("#slcemp").val());
});

const tool = new Toolsjs();
tool.llnarslc("CatalogosBitacora", "empleadosallTraz", "slcemp", 0);

function consulta() {
  event.preventDefault();
  var data1 = $("#slcemp").val();
  var data2 = $("#slccardex").val();
  if (data1 == "" || data2 == "") {
    return false;
  }
  $.ajax({
    url: "php/tbl.php?reporte",
    type: "POST",
    dataType: "html",
    data: { emp: data1, cardex: data2 },
  })
    .done(function (x) {
      $("#tblreporte").html(x);
    })
    .fail(function () {
      console.log("error");
    })
    .always(function () {
      console.log("complete");
    });
}



