export class Toolsjs {
  async senDatawithdata(ruta, data) {
    const respuestaraw = await fetch(ruta, {
      method: "POST",
      body: data,
    });
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuestaraw.status === 201 &&
      swal.fire("Ups", "El registro ya existe", "warning");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error"
      );
  }
  async llnarslc(catalogo, get, dom, tipo = 0) {
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

async llenarCheckbox(catalogo, get, dom) {
  const respuestaraw = await fetch(`../Components/${catalogo}.php?${get}`);
  const respuesta = await respuestaraw.json();
  let body = '<div class="row">';

  respuesta.forEach((elemento, index) => {
    if (index % 2 === 0 && index !== 0) {
      body += '</div><div class="row">';
    }

    body += `
      <div class="col-6">
        <label><strong>${elemento.nombre}</strong></label><br>
        <div style="display: flex; gap: 10px; align-items: center;">
          <label style="display: flex; align-items: center;">
            <input type="radio" name="opcion_${elemento.id}" value="1"> Sí
          </label>
          <label style="display: flex; align-items: center;">
            <input type="radio" name="opcion_${elemento.id}" value="2"> No
          </label>
          <label style="display: flex; align-items: center;">
            <input type="radio" name="opcion_${elemento.id}" value="0"> N/A
          </label>
        </div>
      </div>
    `;
  });

  body += "</div>"; 
  document.getElementById(dom).innerHTML = body;
}



  seleccionarFila(tablaId, callback, btnreset, tipo = 1) {
    const table = document.getElementById(tablaId);
    let selectedRow = null;
    tipo == 1 ? (tipo = "click") : (tipo = "dblclick");
    table.addEventListener(tipo, (event) => {
      const target = event.target;
      const row = target.closest("tr");

      if (row && row.parentNode === table) {
        if (selectedRow) {
          selectedRow.classList.remove("selected");
        }
        row.classList.add("selected");
        selectedRow = row;
        const rowId = row.id;
        if (callback) {
          callback(rowId);
        }
      }
    });
    document.getElementById(btnreset).addEventListener("click", () => {
      if (selectedRow) {
        selectedRow.classList.remove("selected");
        selectedRow = null;
        console.log("Selección limpiada");
      }
    });
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
  async getDataEmpleado(noemp, domnom, domdep = "", dompue = "") {
    if (noemp === "") {
      domnom != "" && (document.getElementById(domnom).value = "");
      domdep != "" && (document.getElementById(domdep).value = "");
      dompue != "" && (document.getElementById(dompue).value = "");
      return false;
    }
    const respuetaraw = await fetch(
      "../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp
    );
    const respuesta = await respuetaraw.json();
    if (respuesta.length === 0) {
      domnom != "" && (document.getElementById(domnom).value = "");
      domdep != "" && (document.getElementById(domdep).value = "");
      dompue != "" && (document.getElementById(dompue).value = "");
      return false;
    }
    domnom != "" &&
      (document.getElementById(domnom).value = respuesta[0].nombre);
    domdep != "" &&
      (document.getElementById(domdep).value = respuesta[0].departamento);
    dompue != "" &&
      (document.getElementById(dompue).value = respuesta[0].puesto);
  }
  validarCamposPorID(idsCampos) {
    for (let id of idsCampos) {
      const campo = document.getElementById(id);
      if (campo && campo.value.trim() === "") {
         campo.classList.add("campo-obligatorio");
        swal.fire("Ups!!!", `Por favor, completa el campo marcado`, "warning");
        return false;
      }
      else{
         campo.classList.remove("campo-obligatorio");
      }
    }
  }
  limpiarCamposPorID(idsCampos) {
    for (let id of idsCampos) {
      const campo = document.getElementById(id);
      if (campo) {
        campo.value = "";
      }
    }
  }
  addEventButton(namebutton, functionnamex) {
    const btndom = document.querySelectorAll("." + namebutton);
    btndom.forEach((element) => {
      const dataid = element.getAttribute("data-id");
      element.addEventListener("click", (e) => {
        e.preventDefault();
        functionnamex(dataid);
      });
    });
  }

  mostrarHora() {
    let fecha = new Date();
    let horas = fecha.getHours();
    let minutos = fecha.getMinutes();
    let segundos = fecha.getSeconds();
    document.getElementById("fecha").innerHTML =
      horas +
      ":" +
      (minutos < 10 ? "0" : "") +
      minutos +
      ":" +
      (segundos < 10 ? "0" : "") +
      segundos;
  }
  mostrarHoraSimple() {
    let fecha = new Date();
    let horas = fecha.getHours();
    horas = (horas < 10 ? "0" : "") + horas;
    document.getElementById("fechaheader").innerHTML = fecha;
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
