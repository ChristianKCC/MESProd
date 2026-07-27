let cacheDepartamentos = null;

export async function llnarslcDepartamentos(selector, tipo = 0) {
  const data = await getDepartamentos();

  let body =
    tipo === 0 ? `<option value="">SELECCIONA UNA OPCIÓN</option>` : "";

  data.forEach((e) => {
    body += `<option value="${e.NoDepto}">${e.NombreDepto}</option>`;
  });

  document.querySelectorAll(selector).forEach((s) => {
    s.innerHTML = body;
  });
}

async function getDepartamentos() {
  if (cacheDepartamentos) return cacheDepartamentos;

  const res = await fetch(
    "../Reporteproduccion/php/reporteDepartamentos.php?getDataDepartamentos",
  );
  cacheDepartamentos = await res.json();
  return cacheDepartamentos;
}

export class ProduccionEnc {
  constructor(
    id,
    fecha,
    departamento,
    maquina,
    clave,
    noemp,
    turno,
    horastrabajadas,
    cajastotales,
    cajasreales,
  ) {
    this.id = id;
    this.fecha = fecha;
    this.departamento = departamento;
    this.maquina = maquina;
    this.clave = clave;
    this.noemp = noemp;
    this.turno = turno;
    this.horastrabajadas = horastrabajadas;
    this.cajastotales = cajastotales;
    this.cajasreales = cajasreales;
  }
  async saveEncProduccion() {
    const data = new FormData();
    let ruta;
    data.append("id", this.id.value);
    data.append("fecha", this.fecha.value);
    data.append("departamento", this.departamento.value);
    data.append("maquina", this.maquina.value);
    data.append("clave", this.clave.value);
    data.append("noemp", this.noemp.value);
    data.append("turno", this.turno.value);
    data.append("horastrabajadas", this.horastrabajadas.value);
    data.append("cajastotales", this.cajastotales.value);
    data.append("cajasreales", this.cajasreales.value);
    this.id.value == ""
      ? (ruta = "../Reporteproduccion/php/producciones.php?saveProduccionesEnc")
      : (ruta =
          "../Reporteproduccion/php/producciones.php?updateProduccionesEnc");
    const respuestaraw = await fetch(ruta, {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    respuestaraw.status === 200 && swal.fire("Listo!!!", respuesta, "success");
    respuestaraw.status === 500 && swal.fire("ERROR!!!", respuesta, "error");
  }
  async tblProduccionesEnc(fechai, fechaf, maquina, idproduccion) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("maquina", maquina);
    data.append("idproduccion", idproduccion);
    const respuestaraw = await fetch(
      "../Reporteproduccion/php/producciones.php?tblProduccionesEnc",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    let merma = 0;
    respuesta.forEach((element) => {
      body += `<tr id='${element.idProduccion}'><td>${
        element.idProduccion
      }</td><td>${element.fecha}</td><td>${element.departamento}</td>
            <td>${element.maquina}</td><td>${
              element.clave + " - " + element.clavenombre
            }</td><td>${element.clase}</td><td>${element.tipo}</td>
            <td>${element.conductor}</td><td>${element.turno}</td><td>${
              element.hrs
            }</td><td>${element.golpestotales}</td><td>${element.cajasreales}</td>
            <td>${element.std}</td><td>${element.merma}</td>
            <td><button class='btn btn-sm btn-danger' onclick='deleteProduccion(this,${
              element.idProduccion
            })'><i class="fas fa-backspace"></i></button></td></tr>`;
    });
    return body;
  }

  async deleteProduccion(filainput, id) {
    const data = new FormData();
    data.append("id", id);
    const respuestaraw = await fetch(
      "../Reporteproduccion/php/producciones.php?deleteEncProduccion",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    respuestaraw.status === 200 && swal.fire("Listo!!!", respuesta, "success");
    respuestaraw.status === 500 && swal.fire("ERROR!!!", respuesta, "error");
    const fila = filainput.parentNode.parentNode;
    fila.parentNode.removeChild(fila);
  }
}

export class Entregas {
  constructor(folio, fecha, maquinas, clave, Entregado) {
    this.folio = folio;
    this.fecha = fecha;
    this.maquinas = maquinas;
    this.clave = clave;
    this.Entregado = Entregado;
  }
  async saveEntregas() {
    const data = new FormData();
    data.append("folio", this.folio.value);
    data.append("fecha", this.fecha.value);
    data.append("maquinas", this.maquinas.value);
    data.append("clave", this.clave.value);
    data.append("Entregado", this.Entregado.value);
    const respuestaraw = await fetch(
      "../Reporteproduccion/php/producciones.php?saveEntregados",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    respuestaraw.status === 200 && swal.fire("Listo!!!", respuesta, "success");
    respuestaraw.status === 500 && swal.fire("ERROR!!!", respuesta, "error");
  }
  async tblEntregados() {
    const respuestaraw = await fetch(
      "../Reporteproduccion/php/producciones.php?tblEntregados",
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `
      <tr id='${element.id}'>
        <td>${element.id}</td>
        <td>${element.folio}</td>
        <td>${element.fecha}</td>
        <td>${element.maquinanombre}</td>
        <td>${element.clave}</td>
        <td>${element.clavenombre}</td>
        <td>${element.tipo}</td>
        <td>${element.clase}</td>
        <td>${element.entregados}</td>
        <td>${element.factor}</td>
        <td>${element.Entstd}</td>
        <td>
          <button class='btn btn-sm btn-danger' onclick='deleteEntregas(this,${element.id})'>
            <i class="fas fa-backspace"></i>
          </button>
        </td>
      </tr>`;
    });
    return body;
  }
  async deleteEntregas(filainput, id) {
    const data = new FormData();
    data.append("id", id);
    const respuestaraw = await fetch(
      "../Reporteproduccion/php/producciones.php?deleteEntregados",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    respuestaraw.status === 200 && swal.fire("Listo!!!", respuesta, "success");
    respuestaraw.status === 500 && swal.fire("ERROR!!!", respuesta, "error");
    const fila = filainput.parentNode.parentNode;
    fila.parentNode.removeChild(fila);
  }
}
export class ReporteProduccion {
  async getdataReporteProduccion(fechai, fechaf, departamento, maquina) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("departamento", departamento);
    data.append("maquina", maquina);
    const respuestaraw = await fetch(
      "./php/ReporteProduccion.php?dataProduccion",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }

  async getdataReporteProduccionTurnos(fechai, fechaf, departamento, maquina) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("departamento", departamento);
    data.append("maquina", maquina);
    const respuestaraw = await fetch(
      "./php/ReporteProduccion.php?dataProducciontblTurnos",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }

  tbldataReporteTurnos(dataTurnos, dom, turno) {
    const datosFiltrados = dataTurnos.slice(0, turno);
    let body = "";
    datosFiltrados.forEach((element) => {
      body += `
        <tr>
          <td>${element.Turno}</td>
          <td>${element.golpes}</td>
        </tr>
        
        
      `;
    });
    dom.innerHTML = body;
  }

  tbldataReporte(data, dom) {
    let body = "";
    data.forEach((element) => {
      body += ` <tr><td>${element.presentacion}</td><td>${element.Descripcion_Articulo}</td><td>${element.NombreMaquina}</td>
            <td>${element.Fecha.date}</td><td>${element.Turno}</td><td>${element.golpes}</td><td>${element.merma}</td>
            <td>${element.std}</td><td><button onclick ="verdetalles(${element.id})" class="btn btn-sm btn-info">Ver detalles</button></td></tr>`;
    });
    dom.innerHTML = body;
  }
  async getDataParos(fechai, fechaf, departamento, maquina) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("departamento", departamento);
    data.append("maquina", maquina);
    const respuestaraw = await fetch("./php/ReporteProduccion.php?dataParos", {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  tbldataParosM(data, dom, fechai, fechaf) {
    let body = "";
    data.forEach((element, index) => {
      const rowId = `detalle-${index}`;
      body += `
      <tr>
        <td>${element.NombreMaquina}</td>
        <td>${element.Modulos}</td>
        <td>${element.Seccion}</td>
        <td>${element.TotalSuma}</td>
        <td>
          <button onclick="verDetalles('${element.Modulos}','${element.Seccion}','${fechai}','${fechaf}','${rowId}')" class="btn btn-sm btn-info">
            Ver detalles
          </button>
        </td>
      </tr>
      <tr id="${rowId}" style="display:none;">
        <td colspan="5">
          <div class="detalle-container"></div>
        </td>
      </tr>
    `;
    });
    dom.innerHTML = body;
  }

  async verDetalles(modulo, seccion, fechai, fechaf, rowId) {
    const detalleRow = document.getElementById(rowId);
    const container = detalleRow.querySelector(".detalle-container");
    // Toggle: si ya está visible, lo ocultamos
    if (detalleRow.style.display === "table-row") {
      detalleRow.style.display = "none";
      container.innerHTML = "";
      return;
    }
    const data = new FormData();
    data.append("modulo", modulo);
    data.append("seccion", seccion);
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    const result = await fetch("./php/ReporteProduccion.php?DataParosDetails", {
      method: "POST",
      body: data,
    });
    const datadetails = await result.json();
    let detalleHTML = `
    <table class="table table-sm table-bordered">
      <thead>
        <tr>
          <th>Operación</th>
          <th>Eléctrico</th>
          <th>Mecánico</th>
          <th>Materias</th>
          <th>Grado</th>
          <th>Prev</th>
          <th>Servicios</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
  `;
    datadetails.forEach((item) => {
      const total =
        item.operacion +
        item.electrico +
        item.mecanico +
        item.materias +
        item.grado +
        item.prev +
        item.servicios;
      detalleHTML += `
      <tr>
        <td>${item.operacion}</td>
        <td>${item.electrico}</td>
        <td>${item.mecanico}</td>
        <td>${item.materias}</td>
        <td>${item.grado}</td>
        <td>${item.prev}</td>
        <td>${item.servicios}</td>
        <td>${total}</td>
      </tr>
    `;
    });

    detalleHTML += `</tbody></table>`;
    container.innerHTML = detalleHTML;
    detalleRow.style.display = "table-row";
  }
}

function checkInfinity(value) {
  if (value === Infinity || value === -Infinity || Number.isNaN(value)) {
    return 0;
  }
  return Number(value).toFixed(2);
}

export class PlanProduccion {
  async tblPlanProduccion(dom) {
    const dataPromise = await fetch(
      "php/producciones.php?ObtenerdatosPlanProduccion",
    );

    const resp = await dataPromise.json();

    let body = "";
    let sumSTD = 0;
    let sumProducc = 0;
    let sumProdvsReal = 0;
    let porcenTotal = 0;
    let porcentaje = 0;

    resp.forEach((element) => {
      porcentaje = checkInfinity((element.produccion / element.STD) * 100);
      sumSTD += element.STD;
      sumProducc += element.produccion;
      sumProdvsReal += element.produccvsreal;
      porcenTotal = checkInfinity((sumProducc / sumSTD) * 100);

      body += `
      <tr>
          <td>${element.id ?? "Sin Información"}</td>
          <td>${element.clave ?? "Sin Información"}</td>
          <td>${element.descripcion ?? "Sin Información"}</td>
          <td>${element.nombreEtapa ?? "Sin Información"}</td>
          <td>${element.nombreProducto ?? "Sin Información"}</td>
          <td>${element.fecha ?? "Sin Información"}</td>
          <td>${element.NombreMaquina ?? "Ninguna"}</td>
          <td>${element.STD}</td>
          <td>${element.produccion}</td>
          <td>${element.produccvsreal}</td>
          <td>${porcentaje} %</td>
      </tr>`;
    });
    document.getElementById(dom).innerHTML = body;

    document.getElementById("totalSTD").innerHTML = sumSTD;
    document.getElementById("totalProducc").innerHTML = sumProducc;
    document.getElementById("totalProduccvsReal").innerHTML = sumProdvsReal;
    document.getElementById("porcenTotal").innerHTML = porcenTotal + " %";
  }

  async tblPlanProduccionFiltro(idMaquina, clave, dom) {
    const data = new FormData();
    data.append("idMaquina", idMaquina);
    data.append("clave", clave);

    const dataPromise = await fetch("php/producciones.php?tblDatosProduccion", {
      method: "POST",
      body: data,
    });

    const resp = await dataPromise.json();

    let body = "";
    let sumSTD = 0;
    let sumProducc = 0;
    let sumProdvsReal = 0;
    let porcenTotal = 0;
    let porcentaje = 0;

    resp.forEach((element) => {
      porcentaje = checkInfinity((element.produccion / element.STD) * 100);
      sumSTD += element.STD;
      sumProducc += element.produccion;
      sumProdvsReal += element.produccvsreal;
      porcenTotal = checkInfinity((sumProducc / sumSTD) * 100);
      body += `
      <tr>
          <td>${element.id ?? "Sin Información"}</td>
          <td>${element.clave ?? "Sin Información"}</td>
          <td>${element.descripcion ?? "Sin Información"}</td>
          <td>${element.nombreEtapa ?? "Sin Información"}</td>
          <td>${element.nombreProducto ?? "Sin Información"}</td>
          <td>${element.fecha ?? "Sin Información"}</td>
          <td>${element.NombreMaquina ?? "Ninguna"}</td>
          <td>${element.STD ?? "Sin Información"}</td>
          <td>${element.produccion ?? "Sin Información"}</td>
          <td>${element.produccvsreal ?? "Sin Información"}</td>
          <td>${porcentaje} %</td>
      </tr>`;
    });
    document.getElementById(dom).innerHTML = body;
    document.getElementById("totalSTD").innerHTML = sumSTD;
    document.getElementById("totalProducc").innerHTML = sumProducc;
    document.getElementById("totalProduccvsReal").innerHTML = sumProdvsReal;
    document.getElementById("porcenTotal").innerHTML = porcenTotal + " %";
  }

  async visualizarPlanProducc(idMaquina, dom) {
    const data = new FormData();
    data.append("idMaquina", idMaquina);
    const dataPromise = await fetch(
      "php/producciones.php?ObtenerdatosPlanProduccionMod",
      {
        method: "POST",
        body: data,
      },
    );
    const response = await dataPromise.json();

    if (response.length > 0) {
      document.getElementById("ModalPlanProduccionLabel").innerText =
        `Plan de Producción de Máquina ${response[0].NombreMaquina}`;
    } else {
      document.getElementById("ModalPlanProduccionLabel").innerText =
        `Sin Información`;
    }

    let body = '<div class="row">';
    let porcentaje = 0;
    response.forEach((element) => {
      porcentaje = checkInfinity((element.produccion / element.STD) * 100);
      body += `
      <div class="col-md-6 mb-4">
        <div class="card">
          <h5 class="card-header fw-bold">${element.clave} - ${element.descripcion}</h5>
          <div class="card-body">
            <div class="row">
              <div class="col-12">
                <div class="row">
                  <div class="col-4">
                    <span class="fw-bold">Programa Mensual</span>
                    <p class="card-text">${element.STD}</p>
                  </div>
                  <div class="col-4">
                    <span class="fw-bold">STD Acumuladas</span>
                    <p class="card-text">${element.produccion}</p>
                  </div>
                  <div class="col-4">
                    <span class="fw-bold">Programa vs Producción</span>
                    <p class="card-text">${element.produccvsreal}</p>
                  </div>
                </div>
                <br>
                <div class="row">
                  <div class="col-12">
                    <div class="progress" role="progressbar" aria-label="Animated striped example" style="height: 30px; font-size: 1rem;" aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">
                      <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: ${porcentaje}%;    background-color: #00aeffd2;">${porcentaje}%</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
    });
    body += "</div>"; // Cierra el <div class="row">
    document.getElementById(dom).innerHTML = body;
  }
}

export class ReporteProduccionTurnos {
  // Funcion para llenar el select de maquinas dependiendo del usuario y de su departamento
  async llnarslcMaquinas(dom, tipo = 0) {
    const respuestaraw = await fetch(
      "../Reporteproduccion/php/producciones.php?getDataMaquinas",
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    tipo == 0
      ? (body = `<option value = ''>Seleciona una opción</option>`)
      : (body = "");
    respuesta.forEach((elemento) => {
      body += `<option value = "${elemento.NoMaquina}">  ${elemento.NombreMaquina}  </option>`;
    });
    document.getElementById(dom).innerHTML = body;
  }

  // Generar reporte en la tabla de todas las máquinas
  async generarTabla(fecha, maquina, turno) {
    const data = new FormData();
    data.append("fecha", fecha);
    data.append("maquina", maquina);
    data.append("turno", turno);

    const dataPromise = await fetch(
      "../Reporteproduccion/php/producciones.php?infoTurnosAnteriores",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await dataPromise.json();
    let body = "";
    respuesta.forEach((element) => {
      // ----- Esta version es por si se quiere omitir registros con cortes o rechazos en 0 ------
      // Si Cortes o Rechazos es 0, no mostrar el registro
      // const cortes = Number(element.Cortes);
      // const rechazos = Number(element.Rechazos);
      // if (cortes === 0 || rechazos === 0) return;

      // Ajuste: para máquinas 60-64 dividir TiempoAbajo entre 60
      // const tiempoAbajoRaw = [60, 61, 62, 63, 64].includes(
      //   Number(element.Maquina)
      // )
      //   ? Number(element.TiempoAbajo) / 60
      //   : Number(element.TiempoAbajo);

      // Sin decimales: validar Infinity/NaN y redondear al entero más cercano
      // const tiempoAbajo =
      //   tiempoAbajoRaw === Infinity ||
      //   tiempoAbajoRaw === -Infinity ||
      //   Number.isNaN(tiempoAbajoRaw)
      //     ? 0
      //     : Math.round(Number(tiempoAbajoRaw));

      const specialMachines = [60, 61, 62, 63, 64];

      const minutosEnhebrandoRaw = specialMachines.includes(
        Number(element.Maquina),
      )
        ? Number(element.MinutosEnhebrando) / 60
        : Number(element.MinutosEnhebrando);
      const minutosEnhebrando =
        minutosEnhebrandoRaw === Infinity ||
        minutosEnhebrandoRaw === -Infinity ||
        Number.isNaN(minutosEnhebrandoRaw)
          ? 0
          : Math.round(Number(minutosEnhebrandoRaw));

      const tiempoArribaRaw = specialMachines.includes(Number(element.Maquina))
        ? Number(element.TiempoArriba) / 60
        : Number(element.TiempoArriba);
      const tiempoArriba =
        tiempoArribaRaw === Infinity ||
        tiempoArribaRaw === -Infinity ||
        Number.isNaN(tiempoArribaRaw)
          ? 0
          : Math.round(Number(tiempoArribaRaw));

      const tiempoPerdidoRaw = specialMachines.includes(Number(element.Maquina))
        ? Number(element.TiempoPerdido) / 60
        : Number(element.TiempoPerdido);
      const tiempoPerdido =
        tiempoPerdidoRaw === Infinity ||
        tiempoPerdidoRaw === -Infinity ||
        Number.isNaN(tiempoPerdidoRaw)
          ? 0
          : Math.round(Number(tiempoPerdidoRaw));

      const mermaMaquina =
        Number(element.Cortes) !== 0
          ? checkInfinity(
              (Number(element.Rechazos) / Number(element.Cortes)) * 100,
            )
          : "0.00";

      const editableIBMs = [
        58998, 31773, 33802, 31578, 57723, 33279, 57118, 34374,
      ];
      const isLocked = !editableIBMs.includes(Number(element.IBM));
      const editButtonAttrs = isLocked
        ? 'disabled aria-disabled="true" title="Bloqueado"'
        : `data-bs-toggle="modal" data-bs-target="#modadalEditRegistroTurno" data-bs-whatever="${element.id}"`;
      const editBtnClass = isLocked
        ? "btn btn-sm btn-warning disabled"
        : "btn btn-sm btn-warning";

      body += `
      <tr data-id="${element.id}">
        <td>${element.Fecha}</td>
        <td>${element.Turno}</td>
        <td>${element.NombreMaquina}</td>
        <td>${element.Cortes}</td>
        <td>${element.Rechazos}</td>
        <td>${element.TiempoAbajo}</td>
        <td>${minutosEnhebrando}</td>
        <td>${tiempoArriba}</td>
        <td>${mermaMaquina} %</td>
        <td>${tiempoPerdido}</td>
        <td>${element.ParosMaquina}</td>
        <td>
          <center>
            <button class="${editBtnClass}" ${editButtonAttrs}><i class="fas fa-pen"></i> Editar Registro</button>
            <button class="btn btn-sm btn-danger" onclick="reporteMaquinaAnterior(${element.id})"><i class="fas fa-file-pdf"></i> Generar reporte</button>
          </center>
        </td>
      </tr>
      `;
    });
    return body;
  }

  async generarTablaMaquinasSinRed(fecha, maquina, turno) {
    const data = new FormData();
    data.append("fecha", fecha);
    data.append("maquina", maquina);
    data.append("turno", turno);

    const dataPromise = await fetch(
      "../Reporteproduccion/php/producciones.php?infoMaquinasSinRed",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await dataPromise.json();
    let body = "";
    respuesta.forEach((element) => {
      const tiempoPerdidoPorc =
        element.MinutosTurno !== 0
          ? checkInfinity(
              (Number(element.TiempoPerdido) / Number(element.MinutosTurno)) *
                100,
            )
          : "0.00";

      body += `
      <tr>
        <td>${element.Fecha}</td>
        <td>${element.Turno}</td>
        <td>${element.NombreMaquina}</td>
        <td>${element.golpes}</td>
        <td></td>
        <td>${element.TiempoPerdido}</td>
        <td></td>
        <td>${element.TiempoArriba}</td>
        <td></td>
        <td>${tiempoPerdidoPorc} %</td>
        <td>${element.ParosMaquina}</td>
        <td>
          <center>
            <button class="btn btn-sm btn-danger" onclick="reporteMaquinasSinRed('${element.Fecha}', ${element.NoMaquina}, '${element.Turno}')"><i class="fas fa-file-pdf"></i> Generar reporte</button>
          </center>
        </td>
      </tr>
      `;
    });
    return body;
  }
  // Camino para generar el reporte PDF sobe la maquina, fecha y turno seleccionados
  async generarPDF(fecha, maquina, turno) {
    try {
      const params = new URLSearchParams({
        fecha: fecha,
        maquina: maquina,
        turno: turno,
      });
      const url = `php/reporteTurnosPDF.php?${params.toString()}#zoom=150`;
      window.open(url, "_blank");
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async generarPDFFolio(folio) {
    try {
      const params = new URLSearchParams({ folio });
      const url = `php/reporteTurnosID.php?${params.toString()}#zoom=150`;
      window.open(url, "_blank");
    } catch (error) {
      console.error("Error:", error);
    }
  }

  async dataForActualizarRegistro(folio) {
    const dataPromise = await fetch(
      `../Reporteproduccion/php/producciones.php?getDataRegistroTurno&folio=${folio}`,
    );
    const respuesta = await dataPromise.json();
    return respuesta;
  }

  async actualizarRegistroTurnoMaquina(
    folio,
    folioBitacora,
    cortes,
    rechazos,
    tiempoAbajo,
    minutosEnhebrando,
    tiempoArriba,
    tiempoPerdido,
    paros,
    horasTrabajadas,
    motivoCambio,
  ) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("folioBitacora", folioBitacora);
    data.append("cortes", cortes);
    data.append("rechazos", rechazos);
    data.append("tiempoAbajo", tiempoAbajo);
    data.append("minutosEnhebrando", minutosEnhebrando);
    data.append("tiempoArriba", tiempoArriba);
    data.append("tiempoPerdido", tiempoPerdido);
    data.append("paros", paros);
    data.append("horasTrabajadas", horasTrabajadas);
    data.append("motivoCambio", motivoCambio);

    const response = await fetch(
      `../Reporteproduccion/php/producciones.php?actualizarRegistroTurnoMaquina`,
      {
        method: "POST",
        body: data,
      },
    );
    response.status === 200 &&
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    response.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }
}

export class ReporteProduccionDepartamentos {
  async generarPDFReporteDepartamentos(fechai, fechaf, departamento) {
    console.log(fechai, fechaf, departamento);
    let url = "";
    try {
      const params = new URLSearchParams({
        fechai: fechai,
        fechaf: fechaf,
        departamento: departamento,
      });
      if (departamento === "1") {
        url = `php/reportePanal.php?${params.toString()}#zoom=200`;
        window.open(url, "_blank");
      } else if (departamento === "2") {
        url = `php/reporteTNT.php?${params.toString()}#zoom=150`;
        window.open(url, "_blank");
      } else if (departamento === "24") {
        url = `php/reporteProteccionFemenina.php?${params.toString()}#zoom=200`;
        window.open(url, "_blank");
      } else if (departamento === "25") {
        url = `php/reporteIncontinencia.php?${params.toString()}#zoom=200`;
        window.open(url, "_blank");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  }
}

export class ReporteProduccionDireccion {
  async generarPDFDireccion(fecha) {
    let url = "";
    try {
      const params = new URLSearchParams({
        fecha: fecha,
      });
      url = `php/reporteDireccion.php?${params.toString()}#zoom=170`;
      window.open(url, "_blank");
    } catch (error) {
      console.error("Error:", error);
    }
  }
  async generarPDFReporteGerencia(fecha) {
    let url = "";
    try {
      const params = new URLSearchParams({
        fecha: fecha,
      });
      url = `php/reporteGerencia.php?${params.toString()}#zoom=170`;
      window.open(url, "_blank");
    } catch (error) {
      console.error("Error:", error);
    }
  }
}
