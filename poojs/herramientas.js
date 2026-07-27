class Herramientas {
  async llnarslc(ruta, dom) {
    const respuestaraw = await fetch("../poophp/calls.php?" + ruta);
    const respuesta = await respuestaraw.json();
    let body = "<option value = ''>Seleciona una opción</option>";
    respuesta.forEach((elemento) => {
      body += `<option value = "${elemento.id}">  ${elemento.nombre}  </option>`;
    });
    document.getElementById(dom).innerHTML = body;
  }
  async llnarslcruta(ruta, dom) {
    const respuestaraw = await fetch(ruta);
    const respuesta = await respuestaraw.json();
    let body = "<option value = ''>Seleciona una opción</option>";
    respuesta.forEach((elemento) => {
      body += `<option value = "${elemento.id}">  ${elemento.nombre}  </option>`;
    });
    document.getElementById(dom).innerHTML = body;
  }
  async llnarslcCatalogo(catalogo, get, dom, tipo = 0) {
    const respuestaraw = await fetch(
      "../Components/" + catalogo + ".php?" + get
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    tipo == 0
      ? (body = `<option value = ''>Seleciona una opción</option>`)
      : (body = "");
    respuesta.forEach((elemento) => {
      body += `<option value = "${elemento.id}">  ${elemento.nombre}  </option>`;
    });
    document.getElementById(dom).innerHTML = body;
  }
  async filldataxadd(ruta, folio, dom) {
    const data = new FormData();
    data.append("folio", folio);
    const respuestaraw = await fetch(ruta, {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((elemento) => {
      body += `<option value = "${elemento.id}">  ${elemento.nombre}  </option>`;
    });
    document.getElementById(dom).innerHTML = body;
  }
  mostrarHora() {
    let fecha = new Date();
    let horas = fecha.getHours();
    horas = (horas < 10 ? "0" : "") + horas;
    document.getElementById("fecha").innerHTML = fecha;
  }
  exportartablaexcel(tableID) {
    var uri = "data:application/vnd.ms-excel;base64,",
      template =
        '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
      base64 = function (s) {
        return window.btoa(unescape(encodeURIComponent(s)));
      },
      format = function (s, c) {
        return s.replace(/{(\w+)}/g, function (m, p) {
          return c[p];
        });
      };
    var table = tableID;
    var name = "nombre_hoja_calculo";
    if (!table.nodeType) table = document.getElementById(table);
    var ctx = { worksheet: name || "Worksheet", table: table.innerHTML };
    window.location.href = uri + base64(format(template, ctx));
  }
}
