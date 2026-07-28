import { Toolsjs } from "../../Tools/Tools.js";
export class Trazabilidad {
  constructor(
    clave,
    modulo,
    material,
    empleado,
    lote,
    folio,
    hora,
    idbitacora
  ) {
    this.clave = clave;
    this.modulo = modulo;
    this.material = material;
    this.empleado = empleado;
    this.lote = lote;
    this.folio = folio;
    this.hora = hora;
    this.idbitacora = idbitacora;
  }
  validarcampos() {
    if (
      this.clave == "" ||
      this.modulo == "" ||
      this.material == "" ||
      this.empleado == "" ||
      this.lote == "" ||
      this.folio == "" ||
      this.idbitacora == "" ||
      this.hora == ""
    ) {
      swal.fire("Ups!!!", "Todos los campos son obligatorios", "warning");
      return false;
    }
  }
  async getEspecificacion(clave) {
    const respuestaraw = await fetch(
      "../Components/CatalogosBitacora.php?especificacionTraz&clave=" + clave
    );
    const respuesta = await respuestaraw.json();
    return respuesta[0].nombre;
  }
  async saveTrazabilidad() {
    const data = new FormData();
    data.append("clave", this.clave);
    data.append("modulo", this.modulo);
    data.append("material", this.material);
    data.append("empleado", this.empleado);
    data.append("lote", this.lote);
    data.append("folio", this.folio);
    data.append("hora", this.hora);
    data.append("idbitacora", this.idbitacora);
    const respuestaraw = await fetch(
      "../bitacora/php/bitacora.php?saveTrazabilidad",
      {
        method: "POST",
        body: data,
      }
    );
    respuestaraw.status === 200 &&
      swal.fire("Listo!!!", "Se guardo correctamente el registro", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR!!!",
        "hay problemas para guardar la información",
        "error"
      );
  }
  async tblTrazabilidad() {
    const idbitacora = document.getElementById("folio").value;
    const respuestaraw = await fetch(
      "../bitacora/php/bitacora.php?tblTrazabilidadEnc&idbitacora=" + idbitacora
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr><td>${element.clave}</td><td>${element.modulo}</td><td>${element.material}</td><td>${element.empleado}</td>
            <td>${element.lote}</td><td>${element.folio}</td><td>${element.hora}</td><td>${element.fecha}</td><td>${element.turno}</td>
            <td>${element.maquina}</td></tr>`;
    });
    document.getElementById("tbltrazabilidad").innerHTML = body;
  }
  Reset() {
    document.getElementById("clave").value = 0;
    document.getElementById("modulotraz").innerHTML = "";
    document.getElementById("materialtraz").innerHTML = "";
    document.getElementById("especificacion").value = "";
    document.getElementById("empleadotraz").value = 0;
    document.getElementById("numlotetraz").value = "";
    document.getElementById("foliotraz").value = "";
    document.getElementById("horatraz").value = "";
  }
}
export class NoConformidad {
  constructor(
    fechaconf,
    depsconf,
    selladorconf,
    operadorconf,
    turnoconf,
    claveprodconf,
    horaconf,
    defectoconf,
    descripcionconf,
    totalprodconf,
    prodrecuperadoconf,
    prodmermaconf,
    empdefectioconf,
    terdefectoconf,
    liderconf,
    accionescorrectivasconf,
    tipeatributeconf,
    idconf,
    componentesconf
  ) {
    this.fechaconf = fechaconf;
    this.depsconf = depsconf;
    this.selladorconf = selladorconf;
    this.operadorconf = operadorconf;
    this.turnoconf = turnoconf;
    this.claveprodconf = claveprodconf;
    this.horaconf = horaconf;
    this.defectoconf = defectoconf;
    this.descripcionconf = descripcionconf;
    this.totalprodconf = totalprodconf;
    this.prodrecuperadoconf = prodrecuperadoconf;
    this.prodmermaconf = prodmermaconf;
    this.empdefectioconf = empdefectioconf;
    this.terdefectoconf = terdefectoconf;
    this.liderconf = liderconf;
    this.accionescorrectivasconf = accionescorrectivasconf;
    this.tipeatributeconf = tipeatributeconf;
    this.idconf = idconf;
    this.componentesconf = componentesconf;
  }
  validarcampos() {
    if (
      this.fechaconf == "" ||
      this.depsconf == "" ||
      this.selladorconf == "" ||
      this.operadorconf == "" ||
      this.turnoconf == "" ||
      this.claveprodconf == "" ||
      this.horaconf == "" ||
      this.defectoconf == "" ||
      this.descripcionconf == "" ||
      this.totalprodconf == "" ||
      this.prodrecuperadoconf == "" ||
      // this.prodmermaconf == "" ||
      this.empdefectioconf == "" ||
      this.terdefectoconf == "" ||
      this.liderconf == "" ||
      this.accionescorrectivasconf == "" ||
      this.tipeatributeconf == ""
    ) {
      swal.fire("Ups!!!", "Todos los campos son obligatorios", "warning");
      return false;
    }
  }
  async saveNoConformidad() {
    const data = new FormData();
    data.append("fechaconf", this.fechaconf);
    data.append("depsconf", this.depsconf);
    data.append("selladorconf", this.selladorconf);
    data.append("operadorconf", this.operadorconf);
    data.append("turnoconf", this.turnoconf);
    data.append("claveprodconf", this.claveprodconf);
    data.append("horaconf", this.horaconf);
    data.append("defectoconf", this.defectoconf);
    data.append("descripcionconf", this.descripcionconf);
    data.append("totalprodconf", this.totalprodconf);
    data.append("prodrecuperadoconf", this.prodrecuperadoconf);
    data.append("prodmermaconf", this.prodmermaconf);
    data.append("empdefectioconf", this.empdefectioconf);
    data.append("terdefectoconf", this.terdefectoconf);
    data.append("liderconf", this.liderconf);
    data.append("accionescorrectivasconf", this.accionescorrectivasconf);
    data.append("tipeatributeconf", this.tipeatributeconf);
    data.append("idconf", this.idconf);
    data.append("componentesconf", this.componentesconf);
    let respuestaraw;
    if (this.idconf === "") {
      respuestaraw = await fetch(
        "../bitacora/php/bitacora.php?saveNoConformidad",
        {
          method: "POST",
          body: data,
        }
      );
    } else {
      respuestaraw = await fetch(
        "../bitacora/php/bitacora.php?updateNoConformidad",
        {
          method: "POST",
          body: data,
        }
      );
    }
    respuestaraw.status === 200 &&
      swal.fire(
        "Listo!!!",
        "Se guardo correctamente la infomación, Recuerda: deberás retener todo el producto desde tu último muestreo. Tu firma electrónica será utilizada únicamente para la liberación de este formato",
        "success"
      );
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR!!!",
        "hay problemas para guardar la información",
        "error"
      );
  }
  async EditNoconformidad(id) {
    const data = new FormData();
    data.append("id", id);
    const respuestaraw = await fetch(
      "../bitacora/php/bitacora.php?getAllDataConf",
      {
        method: "POST",
        body: data,
      }
    );
    const respuesta = await respuestaraw.json();
    const tools = new Toolsjs();
    document.getElementById("folioconf").value = respuesta[0].id;
    document.getElementById("fechaconf").value = respuesta[0].fecha;
    document.getElementById("depsconf").value = respuesta[0].departamento;
    document.getElementById("selladorconf").value = respuesta[0].sellador;
    document.getElementById("operadorconf").value = respuesta[0].operador;
    document.getElementById("turnoconf").value = respuesta[0].turno;
    document.getElementById("claveprodconf").value = respuesta[0].producto;
    document.getElementById("horaconf").value = respuesta[0].hora;
    document.getElementById("descripcionconf").value = respuesta[0].descripcion;
    document.getElementById("totalprodconf").value = respuesta[0].totalprod;
    document.getElementById("prodrecuperadoconf").value =
      respuesta[0].prodrecuperado;
    document.getElementById("prodmermaconf").value = respuesta[0].prodmerma;
    document.getElementById("empdefectioconf").value =
      respuesta[0].codempdefecto;
    document.getElementById("terdefectoconf").value =
      respuesta[0].codterdefecto;
    document.getElementById("liderconf").value = respuesta[0].lider;
    document.getElementById("accionescorrectivasconf").value =
      respuesta[0].accionescorrectivas;
    document.querySelector(
      'input[name="tipeatributeconf"][value="' +
        respuesta[0].tipoatributo +
        '"]'
    ).checked = true;
    tools
      .llnarslc(
        "CatalogosBitacora",
        "GetDefectosxdep&deps=" + respuesta[0].departamento,
        "defectoconf",
        0
      )
      .then(
        () =>
          (document.getElementById("defectoconf").value = respuesta[0].defecto)
      );
  }
  async tblNoConformidad() {
    const tools = new Toolsjs();
    const respuestaraw = await fetch(
      "../bitacora/php/bitacora.php?tblNoConformidad"
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr><td>${element.id}</td><td>${element.fecha}</td><td>${element.maquina}</td><td>${element.defecto}</td> </tr>`;
    });
    // <td><button class='btn btn-sm btn-warning btneditconformidad' data-id='${element.id}'><i class="fa-solid fa-pen-to-square"></i></button></td>
    document.getElementById("tblnoconformidad").innerHTML = body;
    tools.addEventButton("btneditconformidad", (e) =>
      this.EditNoconformidad(e)
    );
  }
}
export class Grafica {
  constructor(idCanvas) {
    this.canvas = document.getElementById(idCanvas);
    this.ctx = this.canvas.getContext("2d");
    this.chart = null;
  }
  crearGrafica(tipo, datos, opciones) {
    this.chart = new Chart(this.ctx, {
      type: tipo,
      data: datos,
      options: opciones,
    });
  }
  actualizarGrafica(grafica, dataDb) {
    const chart = Chart.getChart(grafica);
    chart.data.labels = dataDb.hora;
    chart.data.datasets[0].data = dataDb.datos;
    chart.data.datasets[1].data = dataDb.merma;
    chart.update();
  }
  async getDataDBMonitor() {
    let numreg = document.getElementById("numregmonitor").value;
    const respuestaraw = await fetch(
      "../Bitacora/php/bitacora.php?GetDataMonitor&numhrs=" + numreg
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  async getDataDBMonitorMult(maquina) {
    let numreg = document.getElementById("numregmonitor").value;
    const respuestaraw = await fetch(
      "../Bitacora/php/bitacora.php?GetDataMonitorMult&numhrs=" +
        numreg +
        "&maquina=" +
        maquina
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
}
export class Rill {
  ToolsObj = new Toolsjs();
  async saveRill(
    claverill,
    claseril,
    clmaterialrilaseril,
    noempril,
    loteril,
    foliovalesril,
    horaril,
    materialprueba,
    foliovalemanual
  ) {
    const data = new FormData();
    data.append("claverill", claverill);
    data.append("claseril", claseril);
    data.append("clmaterialrilaseril", clmaterialrilaseril);
    data.append("noempril", noempril);
    data.append("loteril", loteril);
    data.append("foliovalesril", foliovalesril);
    data.append("horaril", horaril);
    data.append("materialprueba", materialprueba);
    data.append("foliovalemanual", foliovalemanual);
    const respuesta = await fetch("../bitacora/php/bitacora.php?saveRill", {
      method: "POST",
      body: data,
    });
    respuesta.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuesta.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error"
      );
  }
  async tblRill() {
    const respuestaraw = await fetch("../bitacora/php/bitacora.php?tblRillEnc");
    const respuesta = await respuestaraw.json();
    let body = "";
    let tipomat = "";
    let tipofolio = "";
    respuesta.forEach((res) => {
      res.material == 0
        ? (tipomat =
            res.material + " " + res.materialprueba + "(Material de prueba)")
        : (tipomat = res.material + " " + res.materialnombre);
      res.foliovalesril == 0
        ? (tipofolio = res.foliovalemanual + "(Papel)")
        : (tipofolio = res.foliovaleconsmaq);
      body += `<tr><td>${res.id}</td><td>${res.clave}</td><td>${
        res.clase
      }</td><td>${tipomat}</td><td>${res.noemp + " " + res.empleadonombre}</td>
        <td>${res.lote}</td><td>${tipofolio}</td><td>${res.horaril}</td><td>${
        res.fecha
      }</td></tr>`;
    });
    return body;
  }
}

function checkInfinity(value) {
  if (value === Infinity || value === -Infinity || Number.isNaN(value)) {
    return 0;
  }
  return Number(value).toFixed(2);
}

export class PlanProducc {
  async visualizarPlanProducc(dom) {
    const dataPromise = await fetch(
      "../bitacora/php/bitacora.php?ObtenerdatosPlanProduccion",
    );
    const resp = await dataPromise.json();

    let body = '<div class="row">';
    let porcentaje = 0;
    resp.forEach((element, index) => {
      const STD = Number(element.STD ?? 0);
      const STDAcumulado = Number(element.STDAcumulado ?? 0);
      
      if (STD === 0 && STDAcumulado === 0) {
        return;
      }

      const produccvsreal = STD - STDAcumulado;

      const displayPercent =
        STD === 0
          ? STDAcumulado > 0
            ? STDAcumulado
            : 0
          : (STDAcumulado / STD) * 100;
      const porcentajeSafeDisplay = Math.max(0, checkInfinity(displayPercent));

      body += `
      <div class="col-md-4 mb-4 d-flex">
        <div class="card w-100 h-100">
          <div class="card-header fw-bold">${element.clave} - ${element.descripcion}</div>
          <div class="card-body d-flex flex-column justify-content-between">
        <div>
          <div class="row text-center">
        <div class="col-4">
          <span class="fw-bold d-block">Programa Mensual</span>
          <p class="card-text mb-0">${STD.toFixed(2)}</p>
        </div>
        <div class="col-4">
          <span class="fw-bold d-block">STD Acumuladas</span>
          <p class="card-text mb-0">${STDAcumulado.toFixed(2)}</p>
        </div>
        <div class="col-4">
          <span class="fw-bold d-block">Programa vs Producción</span>
          <p class="card-text mb-0">${produccvsreal < 0 ? `<span style="color:red;font-weight:600">${produccvsreal.toFixed(2)}</span>` : produccvsreal.toFixed(2)}</p>
        </div>
          </div>
        </div>
          <div class="mt-3">
        <div class="progress" role="progressbar" aria-label="Programa vs Producción" style="height: 30px; font-size: 1rem; position: relative; overflow: hidden;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: ${Math.min(porcentajeSafeDisplay, 100).toFixed(2)}%; background-color: #00aeffd2; height: 100%;">
        ${Math.min(porcentajeSafeDisplay, 100).toFixed(2)}%
          </div>
          ${porcentajeSafeDisplay > 100 ? `<div style="position: absolute; right: 0; top: 0; height: 100%; min-width: 50px; padding: 0 6px; background: linear-gradient(90deg,#9b59b6,#8e44ad); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; border-top-left-radius: .25rem; border-bottom-left-radius: .25rem;">+${(porcentajeSafeDisplay - 100).toFixed(2)}%</div>` : ""}
        </div>
          </div>
          </div>
        </div>
      </div>
        `;
    });
    body += "</div>";

    document.getElementById(dom).innerHTML = body;
  }

  async visualizarPlanProduccFecha(dom, fecha) {
    const data = new FormData();
    data.append("fecha", fecha);

    const dataPromise = await fetch(
      "../bitacora/php/bitacora.php?ObtenerdatosPlanProduccionFecha",
      {
        method: "POST",
        body: data,
      },
    );

    let body = '<div class="row">';
    const resp = await dataPromise.json();
    resp.forEach((element) => {
      const STD = Number(element.STD ?? 0);
      const STDAcumulado = Number(element.STDAcumulado ?? 0);
      const produccvsreal = STD === 0 ? 0 : (STDAcumulado / STD) * 100;
      const porcentajeSafe = Math.max(
        0,
        Math.min(checkInfinity(produccvsreal)),
      );

      const displayPercent =
        STD === 0
          ? STDAcumulado > 0
            ? STDAcumulado
            : 0
          : (STDAcumulado / STD) * 100;
      const porcentajeSafeDisplay = Math.max(0, checkInfinity(displayPercent));

      if (STD === 0 && STDAcumulado === 0) {
        return;
      }

      body += `
      <div class="col-md-4 mb-4 d-flex">
        <div class="card w-100 h-100">
          <div class="card-header fw-bold">${element.clave} - ${element.descripcion}</div>
          <div class="card-body d-flex flex-column justify-content-between">
        <div>
          <div class="row text-center">
        <div class="col-4">
          <span class="fw-bold d-block">Programa Mensual</span>
          <p class="card-text mb-0">${STD.toFixed(2)}</p>
        </div>
        <div class="col-4">
          <span class="fw-bold d-block">STD Acumuladas</span>
          <p class="card-text mb-0">${STDAcumulado.toFixed(2)}</p>
        </div>
        <div class="col-4">
          <span class="fw-bold d-block">Programa vs Producción</span>
          <p class="card-text mb-0">${displayPercent.toFixed(2)}</p>
        </div>
          </div>
        </div>
          <div class="mt-3">
        <!-- contenedor que oculta el desbordamiento para mantener todo dentro de la tarjeta -->
        <div class="progress" role="progressbar" aria-label="Programa vs Producción" style="height: 30px; font-size: 1rem; position: relative; overflow: hidden;">
          <!-- barra principal (hasta 100%) -->
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: ${Math.min(porcentajeSafeDisplay, 100).toFixed(2)}%; background-color: #00aeffd2; height: 100%;">
        ${Math.min(porcentajeSafeDisplay, 100).toFixed(2)}%
          </div>
          <!-- indicador de excedente en morado dentro del mismo contenedor, alineado a la derecha -->
          ${porcentajeSafeDisplay > 100 ? `<div style="position: absolute; right: 0; top: 0; height: 100%; min-width: 50px; padding: 0 6px; background: linear-gradient(90deg,#9b59b6,#8e44ad); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; border-top-left-radius: .25rem; border-bottom-left-radius: .25rem;">+${(porcentajeSafeDisplay - 100).toFixed(2)}%</div>` : ""}
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


export class RegistroCOV {
  async enviarPesos(id, pesos, cov, promedio, min, max, folio) {
    const pesosCadena = JSON.stringify(pesos);
    const covArrr = cov.split(" ");
    const data = new FormData();
    let ruta = "";

    data.append("id", id);
    data.append("pesos", pesosCadena);
    data.append("cov", covArrr[0]);
    data.append("promedio", promedio);
    data.append("min", min);
    data.append("max", max);
    data.append("folio", folio);

    id == "" ? (ruta = "saveCov") : (ruta = "updateCov");

    const dataPromise = await fetch("../bitacora/php/bitacora.php?" + ruta, {
      method: "POST",
      body: data,
    });

    dataPromise.status === 200 &&
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    dataPromise.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }

  async mostrarPesos(folio) {
    const dataPromise = await fetch(
      "../bitacora/php/bitacora.php?mostrarCov&folio=" + folio
    );

    const resp = await dataPromise.json();
    return resp;
  }
}
