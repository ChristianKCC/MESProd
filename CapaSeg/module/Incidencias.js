export class Incidencias {
  async saveIncidencia(
    fecha,
    NoDepto,
    NoMaquina,
    version,
    clasificacion,
    incidencias,
    descripcioncapa,
    implicado,
    antiguedadpuesto,
    antiguedadempresa,
    diasincapacidad,
    diastrabajo,
    tipocontacto,
    provocolesion,
    tipolesion,
    parteafectada,
    severidad,
    probabilidad,
    frecuencia,
    noexpuetas,
    etapa1check1,
    etapa1check2,
    file,
    noReporte,
    totalE1,
    folio
  ) {
    const data = new FormData();
    let ruta = "";

    data.append("fecha", fecha);
    data.append("NoDepto", NoDepto);
    data.append("NoMaquina", NoMaquina);
    data.append("version", version);
    data.append("clasificacion", clasificacion);
    data.append("incidencias", incidencias);
    data.append("descripcioncapa", descripcioncapa);
    data.append("implicado", implicado);
    data.append("antiguedadpuesto", antiguedadpuesto);
    data.append("antiguedadempresa", antiguedadempresa);
    data.append("diasincapacidad", diasincapacidad);
    data.append("diastrabajo", diastrabajo);
    data.append("tipocontacto", tipocontacto);
    data.append("provocolesion", provocolesion);
    data.append("parteafectada", parteafectada);
    data.append("tipolesion", tipolesion);
    data.append("severidad", severidad);
    data.append("probabilidad", probabilidad);
    data.append("frecuencia", frecuencia);
    data.append("noexpuetas", noexpuetas);
    data.append("etapa1check1", etapa1check1);
    data.append("etapa1check2", etapa1check2);
    data.append("file", file);
    data.append("noReporte", noReporte);
    data.append("totalE1", totalE1);
    data.append("folio", folio);

    folio == "" ? (ruta = "saveIncidencia") : (ruta = "updateIncidencia");

    const respuesta = await fetch("php/Incidencia.php?" + ruta, {
      method: "POST",
      body: data,
    });

    const resraw = await respuesta.json();
    respuesta.status === 200 && swal.fire("Listo!", resraw, "success");
    respuesta.status === 500 && swal.fire("Error!", resraw, "error");
  }
  async tblEncabezado() {
    const respuesta = await fetch("php/Incidencia.php?tblEncabezado");
    const resraw = await respuesta.json();
    return resraw;
  }

  async llnarslcValor(get, dom, tipo = 0) {
    const respuestaraw = await fetch(
      "../Components/CatalogoSeguridad.php?" + get
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    tipo == 0
      ? (body = `<option value = ''>Seleciona una opción</option>`)
      : (body = "");
    respuesta.forEach((elemento) => {
      body += `<option value = "${elemento.id}" data-value2="${elemento.valor}">  ${elemento.nombre}  </option>`;
    });
    document.getElementById(dom).innerHTML = body;
  }

  async editEtapa1y2(id) {
    const dataPromise = await fetch(
      "php/Incidencia.php?dataForEditEncEtapa&id=" + id
    );

    const dataResp = await dataPromise.json();
    console.log(dataResp);
    dataPromise.status === 403 && swal.fire("Error!", resraw, "error");
    return dataResp;
  }

  // ----- ETAPA 3 -----
  async saveIncidenciaEtapa3(
    id,
    eventosprev,
    eventofalla,
    equipos,
    operacion,
    producto,
    material,
    otro,
    otroexplique,
    descp1,
    responsable1,
    fechaimp1,
    descp2,
    responsable2,
    fechaimp2,
    descp3,
    responsable3,
    fechaimp3,
    folioenc
  ) {
    const data = new FormData();
    let ruta = "";

    data.append("id", id);
    data.append("eventosprev", eventosprev);
    data.append("eventofalla", eventofalla);
    data.append("equipos", equipos);
    data.append("operacion", operacion);
    data.append("producto", producto);
    data.append("material", material);
    data.append("otro", otro);
    data.append("otroexplique", otroexplique);
    data.append("descp1", descp1);
    data.append("responsable1", responsable1);
    data.append("fechaimp1", fechaimp1);
    data.append("descp2", descp2);
    data.append("responsable2", responsable2);
    data.append("fechaimp2", fechaimp2);
    data.append("descp3", descp3);
    data.append("responsable3", responsable3);
    data.append("fechaimp3", fechaimp3);
    data.append("folioenc", folioenc);

    id == ""
      ? (ruta = "saveIncidenciaEtapa3")
      : (ruta = "actualizarIncidenciaEtapa3");

    const respuesta = await fetch("php/Incidencia.php?" + ruta, {
      method: "POST",
      body: data,
    });

    const resraw = await respuesta.json();

    respuesta.status === 200 && swal.fire("Listo!", resraw, "success");
    respuesta.status === 500 && swal.fire("Error!", resraw, "error");
  }

  async tblEtapa3(id) {
    const respuesta = await fetch(
      "php/Incidencia.php?tblEtapa3&folioenc=" + id
    );
    const resraw = await respuesta.json();
    let body = "";
    resraw.forEach((element) => {
      body += `
      <tr>
        <td>${element.NoReporte}</td>
        <td>${element.EventosPrev}</td>
        <td>${element.Eventofalla}</td>
        <td>${element.desc1}</td>
        <td>${element.Nombre1}</td>
        <td>${element.desc2}</td>
        <td>${element.Nombre2}</td>
        <td>${element.desc3}</td>
        <td>${element.Nombre3}</td>
         <td>
          <button onclick="editEtapa3(${element.id})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i>
          </button>
        </td>
      </tr>`;
    });
    document.getElementById("tbletapa3").innerHTML = body;
  }

  async editEtapaTres(id) {
    const datPromise = await fetch(
      "php/Incidencia.php?dataForEditEtapa3&id=" + id
    );
    const dataRaw = await datPromise.json();
    return dataRaw;
  }

  // ----- ETAPA 4 -----
  async saveEtapa4(
    folioetapa4,
    comportamiento,
    causainmediata,
    porquecausa,
    causabasica,
    porque1,
    causaraiz,
    porqueraiz,
    accioncorrectiva,
    responsableetapa4,
    fechaac,
    accioncorrectiva2,
    responsableetapa42,
    fechaac2,
    accioncorrectiva3,
    responsableetapa43,
    fechaac3,
    accioncorrectiva4,
    responsableetapa44,
    fechaac4,
    accioncorrectiva5,
    responsableetapa45,
    fechaac5,
    folioenc
  ) {
    const data = new FormData();
    let ruta = "";
    data.append("folioetapa4", folioetapa4);
    data.append("comportamiento", comportamiento);
    data.append("causainmediata", causainmediata);
    data.append("porquecausa", porquecausa);
    data.append("causabasica", causabasica);
    data.append("porque1", porque1);
    data.append("causaraiz", causaraiz);
    data.append("porqueraiz", porqueraiz);

    data.append("accioncorrectiva", accioncorrectiva);
    data.append("responsableetapa4", responsableetapa4);
    data.append("fechaac", fechaac);

    data.append("accioncorrectiva2", accioncorrectiva2);
    data.append("responsableetapa42", responsableetapa42);
    data.append("fechaac2", fechaac2);

    data.append("accioncorrectiva3", accioncorrectiva3);
    data.append("responsableetapa43", responsableetapa43);
    data.append("fechaac3", fechaac3);

    data.append("accioncorrectiva4", accioncorrectiva4);
    data.append("responsableetapa44", responsableetapa44);
    data.append("fechaac4", fechaac4);

    data.append("accioncorrectiva5", accioncorrectiva5);
    data.append("responsableetapa45", responsableetapa45);
    data.append("fechaac5", fechaac5);

    data.append("folioenc", folioenc);

    folioetapa4 == "" ? (ruta = "saveEtapa4") : (ruta = "actualizarEtapa4");

    const respuesta = await fetch("php/Incidencia.php?" + ruta, {
      method: "POST",
      body: data,
    });
    const resraw = await respuesta.json();
    respuesta.status === 200 && swal.fire("Listo!", resraw, "success");
    respuesta.status === 500 && swal.fire("Error!", resraw, "error");
  }

  async tblEtapa4(id) {
    const respuesta = await fetch(
      "php/Incidencia.php?tblEtapa4&folioenc=" + id
    );
    const resraw = await respuesta.json();
    let body = "";
    resraw.forEach((element) => {
      body += `
      <tr>
        <td>${element.NoReporte !== null ? element.NoReporte : ""}</td>
        <td>${element.NombreComp !== null ? element.NombreComp : ""}</td>
        <td>${
          element.NombreCausaInm !== null ? element.NombreCausaInm : ""
        }</td>
        <td>${element.porquecausa !== null ? element.porquecausa : ""}</td>
        <td>${element.NombreCauB !== null ? element.NombreCauB : ""}</td>
        <td>${element.NombreCauR !== null ? element.NombreCauR : ""}</td>
        <td>${
          element.accioncorrectiva !== null ? element.accioncorrectiva : ""
        }</td>
        <td>${element.NombreResp !== null ? element.NombreResp : ""}</td>
        <td>${element.fechaac !== null ? element.fechaac : ""}</td>
        <td>
          <button onclick="editEtapa4(${
            element.id
          })" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i>
          </button>
        </td>
      </tr>`;
    });
    document.getElementById("tbletapa4").innerHTML = body;
  }

  async editEtapaCuatro(id) {
    const dataPromise = await fetch(
      "php/Incidencia.php?dataForEditEtapa4&id=" + id
    );
    const dataRaw = await dataPromise.json();
    return dataRaw;
  }

  // ----- ETAPA 5 -----
  async saveEtapa5(
    idEtapa5,
    incprisa,
    incojostarea,
    frustracion,
    mente,
    fatiga,
    peligro,
    riesgo,
    equilibrio,
    interaccion1,
    interaccion2,
    interaccion3,
    interaccion4,
    interaccion5,
    interaccion6,
    riesgos1,
    riesgos1porque,
    riesgos2,
    riesgos2porque,
    riesgos3,
    riesgos3porque,
    riesgos4,
    riesgos4porque,
    folioenc
  ) {
    const data = new FormData();
    let ruta = "";

    data.append("idEtapa5", idEtapa5);
    data.append("incprisa", incprisa);
    data.append("incojostarea", incojostarea);
    data.append("frustracion", frustracion);
    data.append("mente", mente);
    data.append("fatiga", fatiga);
    data.append("peligro", peligro);
    data.append("riesgo", riesgo);
    data.append("equilibrio", equilibrio);
    data.append("interaccion1", interaccion1);
    data.append("interaccion2", interaccion2);
    data.append("interaccion3", interaccion3);
    data.append("interaccion4", interaccion4);
    data.append("interaccion5", interaccion5);
    data.append("interaccion6", interaccion6);
    data.append("riesgos1", riesgos1);
    data.append("riesgos2", riesgos2);
    data.append("riesgos3", riesgos3);
    data.append("riesgos4", riesgos4);
    data.append("riesgos1porque", riesgos1porque);
    data.append("riesgos2porque", riesgos2porque);
    data.append("riesgos3porque", riesgos3porque);
    data.append("riesgos4porque", riesgos4porque);
    data.append("folioenc", folioenc);

    idEtapa5 == "" ? (ruta = "saveEtapa5") : (ruta = "actualizarEtapa5");

    const respuesta = await fetch("php/Incidencia.php?" + ruta, {
      method: "POST",
      body: data,
    });
    const resraw = await respuesta.json();
    respuesta.status === 200 && swal.fire("Listo!", resraw, "success");
    respuesta.status === 500 && swal.fire("Error!", resraw, "error");
  }

  async tblEtapa5(id) {
    const respuesta = await fetch(
      "php/Incidencia.php?tbletapa5&folioenc=" + id
    );
    const resraw = await respuesta.json();
    let body = "";
    resraw.forEach((element) => {
      body += `
      <tr>
        <td>${element.id}</td>
        <td>${element.Elemento}</td>
        <td>${element.tipoelemento}</td>
        <td>${element.sistemagestionporque}</td>
        <td>${element.incprisa === 1 ? `Si` : `No`}</td>
        <td>${element.frustracion === 1 ? `Si` : `No`}</td>
        <td>
          <button onclick="editEtapa5(${
            element.id
          })" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i>
          </button>
        </td>
      </tr>`;
    });
    document.getElementById("tbletapa5").innerHTML = body;
  }

  async editEtapaCinco(id) {
    const dataPromise = await fetch(
      "php/Incidencia.php?dataForEditEtapa5&id=" + id
    );
    const dataRaw = await dataPromise.json();
    return dataRaw;
  }

  // ----- ETAPA 6 -----
  async saveEtapa6(
    idEtapa6,
    sistemagestion,
    sistemagestionsub,
    sistemagestionporque,
    folioenc
  ) {
    const data = new FormData();
    let ruta = "";

    data.append("idEtapa6", idEtapa6);
    data.append("sistemagestion", sistemagestion);
    data.append("sistemagestionsub", sistemagestionsub);
    data.append("sistemagestionporque", sistemagestionporque);
    data.append("folio", folioenc);

    idEtapa6 == "" ? (ruta = "saveEtapa6") : (ruta = "actualizarEtapa6");

    const respuesta = await fetch("php/Incidencia.php?" + ruta, {
      method: "POST",
      body: data,
    });
    const resraw = await respuesta.json();
    respuesta.status === 200 && swal.fire("Listo!", resraw, "success");
    respuesta.status === 500 && swal.fire("Error!", resraw, "error");
  }

  async tblEtapa6(id) {
    const respuesta = await fetch(
      "php/Incidencia.php?tbletapa6&folioenc=" + id
    );
    const resraw = await respuesta.json();
    let body = "";
    resraw.forEach((element) => {
      body += `
      <tr>
        <td>${element.NoReporte}</td>
        <td>${element.tipoelemento}</td>
        <td>${element.elemento}</td>
        <td>${element.sistemagestionporque}</td>
        <td>
          <button onclick="editEtapa6(${element.id})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i>
          </button>
        </td>
      </tr>`;
    });
    document.getElementById("tbletapa6").innerHTML = body;
  }

  async editEtapaSeis(id) {
    const dataPromise = await fetch(
      "php/Incidencia.php?dataForEditEtapa6&id=" + id
    );
    const dataRaw = await dataPromise.json();
    return dataRaw;
  }

  // ------------- ETAPA 7 --------------

  async saveEtapa7(idEtapa7, noempEtapa7, folioenc) {
    const data = new FormData();
    let ruta = "";
    data.append("idEtapa7", idEtapa7);
    data.append("noempEtapa7", noempEtapa7);
    data.append("folioenc", folioenc);

    idEtapa7 == "" ? (ruta = "saveEtapa7") : (ruta = "actualizarEtapa7");

    const respuesta = await fetch("php/Incidencia.php?" + ruta, {
      method: "POST",
      body: data,
    });
    const resraw = await respuesta.json();
    respuesta.status === 200 && swal.fire("Listo!", resraw, "success");
    respuesta.status === 500 && swal.fire("Error!", resraw, "error");
  }

  async tblEtapa7(id) {
    const respuesta = await fetch(
      "php/Incidencia.php?tbletapa7&folioenc=" + id
    );

    const resraw = await respuesta.json();
    let body = "";
    resraw.forEach((element) => {
      body += `
      <tr>
        <td>${element.NoReporte}</td>
        <td>${element.noemp}</td>
        <td>${element.Nombre}</td>
        <td>${element.departamento}</td>
        <td>${element.puesto}</td>
        <td>
          <button onclick="editEtapa7(${element.id})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i>
          </button>
        </td>
      </tr>`;
    });
    document.getElementById("tbletapa7").innerHTML = body;
  }

  async editEtapaSiete(id) {
    const dataPromise = await fetch(
      "php/Incidencia.php?dataForEditEtapa7&id=" + id
    );
    const dataRaw = await dataPromise.json();
    return dataRaw;
  }

  // ------------- ETAPA 8 ------------------
  async saveEtapa8(idEtapa8, noEmpEtapa8, tipo, folio) {
    const data = new FormData();
    let ruta = "";
    data.append("idEtapa8", idEtapa8);
    data.append("noEmpEtapa8", noEmpEtapa8);
    data.append("tipo", tipo);
    data.append("folio", folio);

    idEtapa8 == "" ? (ruta = "saveEtapa8") : (ruta = "actualizarEtapa8");

    const respuesta = await fetch("php/Incidencia.php?" + ruta, {
      method: "POST",
      body: data,
    });
    const resraw = await respuesta.json();
    respuesta.status === 200 && swal.fire("Listo!", resraw, "success");
    respuesta.status === 500 && swal.fire("Error!", resraw, "error");
  }

  async tblEtapa8(id) {
    const respuesta = await fetch(
      "php/Incidencia.php?tbletapa8&folioenc=" + id
    );

    const resraw = await respuesta.json();
    let body = "";
    resraw.forEach((element) => {
      body += `
      <tr>
        <td>${element.NoReporte}</td>
        <td>${element.noemp}</td>
        <td>${element.Nombre}</td>
        <td>${element.departamento}</td>
        <td>${element.tipoEvaluador}</td>
        <td>
          <button onclick="editEtapa8(${element.id})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i>
          </button>
        </td>
      </tr>`;
    });
    document.getElementById("tbletapa8").innerHTML = body;
  }

  async editEtapaOcho(id) {
    const dataPromise = await fetch(
      "php/Incidencia.php?dataForEditEtapa8&id=" + id
    );
    const dataRaw = await dataPromise.json();
    return dataRaw;
  }

  async elimarRegistroIncidencia(id) {
    console.log(id);
  }
}

export class ReporteIncidencias {
  async tblReporteIncidencias(fechaInicial, fechaFinal, departamento) {
    const data = new FormData();

    // data.append("etapa", etapa);
    data.append("fechaInicial", fechaInicial);
    data.append("fechaFinal", fechaFinal);
    data.append("departamento", departamento);

    const dataPromise = await fetch(
      "php/Incidencia.php?tblReporteIncidencias",
      {
        method: "POST",
        body: data,
      }
    );

    const respuesta = await dataPromise.json();
    let body = "";

    respuesta.forEach((element) => {
      body += `
        <tr>
          <td>${element.Folio}</td>
          <td>${element.fecha}</td>
          <td>${element.NumeroEmpleado}</td>
          <td>${element.ApellidoPaterno} ${element.ApellidoMaterno} ${element.NombreEmpleado}</td>
          <td>${element.Area}</td>
          <td>${element.Departamento}</td>
          <td>${element.EmpleadoImplicado}</td>
          <td>${element.APaternoImplicado} ${element.AMaternoImplicado} ${element.NombresImplicado}</td>
          <td>${element.AreaImplicado}</td>
          <td>${element.DepartamentoImplicado}</td>
          <td>${element.SubClasificacion}</td>
          <td>${element.Clasificacion}</td>
          <td>
            <center>
              <button onclick="autorizarReporte(${element.Folio})" id="autorizarReporte" class="btn btn-sm btn-primary"><i class="fas fa-check"></i></button>
              <button onclick="generarReporte(${element.Folio})" id="generarReporte" class="btn btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i></button>
            </center>
          </td>
        </tr>
      `;
    });
    document.getElementById("tblIncidencias").innerHTML = body;
  }

  async generarReporte(folio) {
    try {
      const url = `php/generarPDF.php?folio=${encodeURIComponent(folio)}`;
      window.open(url, "_blank"); // Abre el PDF en una nueva pestaña
    } catch (error) {
      console.error("Error:", error);
    }
  }
}

export class AccionesCorrectivas {
  async tblAccionesCorrectivas(fechaInicial, fechaFinal, departamento, dom) {
    const data = new FormData();

    data.append("fechaInicial", fechaInicial);
    data.append("fechaFinal", fechaFinal);
    data.append("departamento", departamento);

    const dataPromise = await fetch(
      "php/Incidencia.php?tblAccionesCorrectivas",
      {
        method: "POST",
        body: data,
      }
    );

    const resp = await dataPromise.json();

    let body = "";

    resp.forEach((element) => {
      body += `
        <tr>
          <td>${element.folioenc}</td>
          <td>${element.responsableetapa4}</td>
          <td>${element.Nombre}</td>
          <td>${element.CauBasica}</td>
          <td>${element.CauInmediata}</td>
          <td>${element.CausaRaiz}</td>
          <td>${element.Comportamiento}</td>
          <td>${element.accioncorrectiva}</td>
          <td>${element.porque1}</td>
          <td>${element.porquecausa}</td>
          <td>${element.porqueraiz}</td>
        </tr>
      `;
    });
    document.getElementById(dom).innerHTML = body;
  }
}
