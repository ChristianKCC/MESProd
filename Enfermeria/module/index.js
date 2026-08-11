export class Incapacidades {
  async tblIncapacidadessession() {
    const datapromise = await fetch("php/Incapacidades.php?tblIncapacidades");
    const respromise = await datapromise.json();
    return respromise;
  }
  async tblIncapacidad(dom, data, edit) {
    // 1. Ordenar ascendente para calcular bien el acumulado
    data.sort((a, b) => a.id - b.id);

    // 2. Calcular acumulado de días por noemp
    const acum = {};
    data.forEach((element) => {
      acum[element.noemp] = (acum[element.noemp] || 0) + element.dias;
      element.diasAcum = acum[element.noemp];
    });

    // 3. Invertir para mostrar del más reciente al más antiguo
    data.sort((a, b) => b.id - a.id);

    let body = "";
    let val2 = "";
    data.forEach((element) => {
      val2 =
        edit == 1
          ? `<td><button onclick="editIncapacidad(${element.id})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button></td>`
          : "";
      body += `
      <tr>
        <td>${element.noemp}</td>
        <td>${element.Nombre}</td>
        <td>${element.departamento}</td>
        <td>${element.puesto}</td>
        <td>${element.responsable}</td>
        <td>${element.responsablenombre}</td>
        <td>${element.folio}</td>
        <td>${element.tipo}</td>
        <td>${element.frecuencia}</td>
        <td>${element.fecharevision}</td>
        <td>${element.dias}</td>
        <td>${element.diasAcum}</td>
        <td>${element.fechainicio}</td>
        <td>${element.fechafin}</td>
        <td>${element.st1}</td>
        <td>${element.stps}</td>
        <td>${element.fechaentrega}</td>
        <td>${element.dx}</td>
      ${val2}</tr>`;
    });
    document.getElementById(dom).innerHTML = body;
  }
  async ReporteIncapacidad(fechai, fechaf, departamento, maquina, noemp) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("departamento", departamento);
    data.append("maquina", maquina);
    data.append("noemp", noemp);
    const datapromise = await fetch(
      "php/Incapacidades.php?reporteIncapacidadesdata",
      {
        method: "POST",
        body: data,
      },
    );
    const dataresprom = await datapromise.json();
    return dataresprom;
  }
  async saveIncapacidad(
    noemp,
    departamento,
    puesto,
    responsable,
    folio,
    tipo,
    frecuencia,
    fecharevision,
    dias,
    fechainicio,
    st1,
    stps,
    fechaentrega,
    dx,
    id,
    fechatermina,
  ) {
    const data = new FormData();
    let ruta = "";
    data.append("noemp", noemp);
    data.append("departamento", departamento);
    data.append("puesto", puesto);
    data.append("responsable", responsable);
    data.append("folio", folio);
    data.append("tipo", tipo);
    data.append("frecuencia", frecuencia);
    data.append("fecharevision", fecharevision);
    data.append("dias", dias);
    data.append("fechainicio", fechainicio);
    data.append("st1", st1);
    data.append("stps", stps);
    data.append("fechaentrega", fechaentrega);
    data.append("dx", dx);
    data.append("id", id);
    data.append("fechatermina", fechatermina);
    id == "" ? (ruta = "saveIncapacidad") : (ruta = "updateIncapacidad");
    const datapromise = await fetch("php/Incapacidades.php?" + ruta, {
      method: "POST",
      body: data,
    });
    datapromise.status === 200 &&
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    datapromise.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }
  async editarIncapacidad(id) {
    const datapromise = await fetch(
      "php/Incapacidades.php?dataforeditIncapacidad&id=" + id,
    );
    const dataraw = await datapromise.json();
    return dataraw;
  }
}

export class Consultas {
  async saveConsulta(
    noemp,
    nombre,
    departamento,
    puesto,
    maquina,
    edad,
    antiguedad,
    tratamiento,
    observacion,
    tipoaparato,
    tipoenfermedad,
    tipoconsulta,
    sexo,
    rolturno,
    temperatura,
    frecuencia,
    pasistolica,
    padistolica,
    nombreexterno,
    empresaexterna,
    folio,
    fecharevision,
    horaRevision,
    canvas,
  ) {
    const data = new FormData();
    let ruta = "";
    data.append("noemp", noemp);
    data.append("nombre", nombre);
    data.append("departamento", departamento);
    data.append("puesto", puesto);
    data.append("maquina", maquina);
    data.append("edad", edad);
    data.append("antiguedad", antiguedad);
    data.append("tratamiento", tratamiento);
    data.append("observacion", observacion);
    data.append("tipoaparato", tipoaparato);
    data.append("tipoenfermedad", tipoenfermedad);
    data.append("tipoconsulta", tipoconsulta);
    data.append("sexo", sexo);
    data.append("rolturno", rolturno);
    data.append("temperatura", temperatura);
    data.append("frecuencia", frecuencia);
    data.append("pasistolica", pasistolica);
    data.append("padistolica", padistolica);
    data.append("nombreexterno", nombreexterno);
    data.append("empresaexterna", empresaexterna);
    data.append("fecharevision", fecharevision);
    data.append("horaRevision", horaRevision);
    data.append("folio", folio);
    if (canvas) {
      const dataURL = canvas.toDataURL("image/png");
      data.append("firma", dataURL);
    }

    folio == "" ? (ruta = "saveConsulta") : (ruta = "updateConsulta");
    const datapromise = await fetch("php/Consultas.php?" + ruta, {
      method: "POST",
      body: data,
    });
    datapromise.status === 200 &&
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    datapromise.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }
  async datauserEnfermeria(noemp) {
    const data = new FormData();
    data.append("noemp", noemp);
    const datapromise = await fetch("php/Consultas.php?dataUserCompleate", {
      method: "POST",
      body: data,
    });
    const dataraw = await datapromise.json();
    return dataraw;
  }
  async tblConsultaSession(dom) {
    const datapromise = await fetch("php/Consultas.php?tblConsultas");
    const dataraw = await datapromise.json();
    let body = "";
    dataraw.forEach((element) => {
      body += `<tr>
            <td>${element.noemp}</td>
            <td>${element.Nombre}</td>
            <td>${element.departamento}</td>
            <td>${element.puesto}</td>
            <td>${element.maquina}</td>
            <td>${element.edad}</td>
            <td>${element.antiguedad}</td>
            <td>${element.tratamiento}</td>
            <td>${element.observacion}</td>
            <td>${element.equipomedico}</td>
            <td>${element.enfermedad}</td>
            <td>${element.tipoconsulta}</td>
            <td>${element.fecharevision}</td>
            <td>${
              element.horarevision == null
                ? "Sin registro"
                : element.horarevision
            }</td>
            <td><img src="data:image/png;base64,${
              element.firma
            }" style="max-width:80px;" alt="Firma"></td>
            <td><button onclick="editconsulta(${
              element.id
            })" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button></td>
          </tr>`;
    });
    document.getElementById(dom).innerHTML = body;
  }
  async resporteConsultas(
    fechai,
    fechaf,
    aparato,
    enfermedad,
    departamento,
    maquina,
    noemp,
  ) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("aparato", aparato);
    data.append("enfermedad", enfermedad);
    data.append("departamento", departamento);
    data.append("maquina", maquina);
    data.append("noemp", noemp);
    const datapromise = await fetch("php/Consultas.php?reporteConsultasdata", {
      method: "POST",
      body: data,
    });
    const dataresprom = await datapromise.json();
    return dataresprom;
  }
  async tblConsulta(dom, data) {
    let body = "";
    data.forEach((element) => {
      body += `<tr><td>${element.id}</td><td>${element.noemp}</td><td>${element.Nombre}</td><td>${element.departamento}</td><td>${element.puesto}</td><td>${element.maquina}</td>
            <td>${element.edad}</td><td>${element.antiguedad}</td><td>${element.tratamiento}</td><td>${element.observacion}</td>
            <td>${element.equipomedico}</td><td>${element.enfermedad}</td><td>${element.tipoconsulta}</td><td>${element.fecharevision}</td>
            <td><img src="data:image/png;base64,${element.firma}" style="max-width:80px;" alt="Firma"></td></tr>`;
    });
    document.getElementById(dom).innerHTML = body;
  }
  async editconsulta(id) {
    const datapromise = await fetch(
      "php/Consultas.php?dataforeditConsulta&id=" + id,
    );
    const dataraw = await datapromise.json();
    return dataraw;
  }
}

export class ExamenMedico {
  async saveExamen(examenData, folio) {
    const data = new FormData();
    let ruta = "";
    for (const key in examenData) {
      if (examenData.hasOwnProperty(key)) {
        data.append(key, examenData[key]);
      }
    }

    if (canvas) {
      const dataURL = canvas.toDataURL("image/png");
      data.append("firma", dataURL);
    }

    if (examenData.ruta instanceof File) {
      data.append("file", examenData.ruta); // clave debe ser "file"
    }

    const obj = Object.fromEntries(data.entries());
    // Obtener la ruta de la consulta php con respecto al valor almacenado dentro del folio
    folio == "" ? (ruta = "saveExamenMedico") : (ruta = "updateExamenMedico");
    const datapromise = await fetch("php/ExamenM.php?" + ruta, {
      method: "POST",
      body: data,
    });
    datapromise.status === 200 &&
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    datapromise.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }

  // async tblExamenMSession(dom, edit) {
  //   const datapromise = await fetch("php/ExamenM.php?tblExamenMedico");
  //   const respromise = await datapromise.json();
  //   let val2 = "";
  //   let body = "";
  //   respromise.forEach((element) => {
  //     const pdfMap = {
  //       1: "php/ExamenIngreso.php",
  //       2: "php/Exampdf.php",
  //       3: "php/ExamenEgreso.php",
  //     };
  //     const pdfUrl = pdfMap[element.tipoExamen] ?? "php/Exampdf.php";

  //     val2 =
  //       edit == 1
  //         ? `<td>
  //           <button onclick="editExamenM(${element.id})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
  //           <a class="btn btn-sm btn-danger" target="_blank" href="${pdfUrl}?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
  //           <a class="btn btn-sm btn-danger" target="_blank" href="php/Consentimiento.php?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
  //         </td>`
  //         : "";

  //     body += `
  //   <tr>
  //     <td hidden>${element.id}</td>
  //     <td>${element.noemp}</td>
  //     <td>${element.nombre}</td>
  //     <td>${element.departamento}</td>
  //     <td>${element.puesto}</td>
  //     <td>${element.fecha}</td>
  //     <td><center><img src="${element.firma}" style="max-width:100px;" alt="Firma" loading="lazy"/></center></td>
  //         ${val2}
  //   </tr>`;
  //   });
  //   document.getElementById(dom).innerHTML = body;
  // }

  // Datos para editar examen médico

  async tblExamenMSession(dom, edit) {
    const datapromise = await fetch("php/ExamenM.php?tblExamenMedico");
    const respromise = await datapromise.json();
    this.pintarTablaExamenM(dom, respromise, edit);
  }

  // pintarTablaExamenM(dom, data, edit) {
  //   let body = "";
  //   data.forEach((element) => {
  //     const pdfMap = {
  //       1: "php/ExamenIngreso.php",
  //       2: "php/Exampdf.php",
  //       3: "php/ExamenEgreso.php",
  //     };
  //     const pdfUrl = pdfMap[element.tipoExamen] ?? "php/Exampdf.php";
  //     const val2 =
  //       edit == 1
  //         ? `<td>
  //             <button onclick="editExamenM(${element.id})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
  //             <a class="btn btn-sm btn-danger" target="_blank" href="${pdfUrl}?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
  //             <a class="btn btn-sm btn-danger" target="_blank" href="php/Consentimiento.php?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
  //           </td>`
  //         : "";
  //     body += `
  //     <tr>
  //       <td hidden>${element.id}</td>
  //       <td>${element.noemp}</td>
  //       <td>${element.nombre}</td>
  //       <td>${element.departamento}</td>
  //       <td>${element.puesto}</td>
  //       <td>${element.fecha}</td>
  //       <td><center><img src="${element.firma}" style="max-width:100px;" alt="Firma" loading="lazy"/></center></td>
  //       ${val2}
  //     </tr>`;
  //   });
  //   document.getElementById(dom).innerHTML = body;
  // }

  async filtrarExamenM(noemp, departamento, fechai, fechaf) {
    const data = new FormData();
    data.append("noemp", noemp);
    data.append("departamento", departamento);
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    const datapromise = await fetch("php/ExamenM.php?filtrarExamenM", {
      method: "POST",
      body: data,
    });
    return await datapromise.json();
  }

  async editExamenM(id) {
    const dataPromise = await fetch(
      "php/ExamenM.php?dataForEditExamenM&id=" + id,
    );

    const dataraw = await dataPromise.json();
    return dataraw;
  }

  async resporteExamenM(fechai, fechaf, departamento, noemp) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("departamento", departamento);
    data.append("noemp", noemp);
    const datapromise = await fetch("php/ExamenM.php?reporteExamen", {
      method: "POST",
      body: data,
    });
    const respromise = await datapromise.json();
    let val2 = "";
    let body = "";
    respromise.forEach((element) => {
      body += `<tr>
                <td>${element.id}</td>
                <td>${element.noemp}</td>
                <td>${element.nombre}</td>
                <td>${element.departamento}</td>
                <td>${element.puesto}</td>
                <td>${element.fecha}</td>
                <td><img src="${element.firma}" style="max-width:80px;" alt="Firma"/></td>
                <td>
                  <a class="btn btn-sm btn-danger" target="_blank" href="php/Exampdf.php?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
                  <a class="btn btn-sm btn-danger" target="_blank" href="php/Consentimiento.php?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
                </td>
              </tr>`;
    });
    document.getElementById("tblreporteExamenM").innerHTML = body;
    return respromise;
  }

  async llenarSlcIMC(dom, tipo = 0) {
    const respuestaraw = await fetch("php/ExamenM.php?infoIMC");

    const respuesta = await respuestaraw.json();

    let body = "";
    tipo == 0
      ? (body = `<option value = ''>Seleciona una opción</option>`)
      : (body = "");
    respuesta.forEach((elemento) => {
      body += `<option value="${elemento.idIMC}" data-min="${elemento.Minimo}" data-max="${elemento.Maximo}">  ${elemento.NombreTipo}  </option>`;
    });
    document.getElementById(dom).innerHTML = body;
  }

  async generarReporteConsentimiento(noemp, fechaNac, canvas) {
    const data = new FormData();
    data.append("noemp", noemp);
    data.append("fechaNac", fechaNac);

    if (canvas) {
      const dataURL = canvas.toDataURL("image/png");
      data.append("firma", dataURL);
    }

    try {
      const response = await fetch("php/ReporteConsentimiento.php", {
        method: "POST",
        body: data,
      });

      if (!response.ok) {
        throw new Error("Error al generar el PDF");
      }

      const blob = await response.blob();
      const url = URL.createObjectURL(blob);
      window.open(url, "_blank"); // Abre el PDF en una nueva pestaña
    } catch (error) {
      console.error("Error:", error);
    }
  }

  // pintarTablaExamenM(dom, data, edit) {
  //   const RUTA_FIRMAS = "../../../../Mes/KCMes/FirmaDigital/firmas/";
  //   let body = "";
  //   data.forEach((element) => {
  //     const pdfMap = {
  //       1: "php/ExamenIngreso.php",
  //       2: "php/Exampdf.php",
  //       3: "php/ExamenEgreso.php",
  //     };
  //     const pdfUrl = pdfMap[element.tipoExamen] ?? "php/Exampdf.php";

  //     // Firma de conformidad: PNG por noemp; si no existe, se oculta la imagen
  //     const firmaConf = element.noemp
  //       ? `<img src="${RUTA_FIRMAS}${element.noemp}.png" style="max-width:100px;" alt="Firma conformidad" loading="lazy" onerror="this.style.display='none'"/>`
  //       : "";

  //     const val2 =
  //       edit == 1
  //         ? `<td>
  //             <button onclick="editExamenM(${element.id})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
  //             <a class="btn btn-sm btn-danger" target="_blank" href="${pdfUrl}?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
  //             <a class="btn btn-sm btn-danger" target="_blank" href="php/Consentimiento.php?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
  //             <button onclick="eliminarExamenM(${element.id})" class="btn btn-sm btn-dark"><i class="fas fa-trash"></i></button>
  //           </td>`
  //         : "";

  //     body += `
  //     <tr>
  //       <td hidden>${element.id}</td>
  //       <td>${element.noemp}</td>
  //       <td>${element.nombre}</td>
  //       <td>${element.departamento}</td>
  //       <td>${element.puesto}</td>
  //       <td>${element.fecha}</td>
  //       <td><center>${firmaConf}</center></td>
  //       ${val2}
  //     </tr>`;
  //   });
  //   document.getElementById(dom).innerHTML = body;
  // }

  pintarTablaExamenM(dom, data, edit) {
    const RUTA_FIRMAS = "../../../../Mes/KCMes/FirmaDigital/firmas/";
    let body = "";
    data.forEach((element) => {
      const pdfMap = {
        1: "php/ExamenIngreso.php",
        2: "php/Exampdf.php",
        3: "php/ExamenEgreso.php",
      };
      const pdfUrl = pdfMap[element.tipoExamen] ?? "php/Exampdf.php";

      const firmaConf = element.noemp
        ? `<img src="${RUTA_FIRMAS}${element.noemp}.png" style="max-width:100px;" alt="Firma conformidad" loading="lazy" onerror="this.style.display='none'"/>`
        : "";

      const val2 =
        edit == 1
          ? `<td>
              <button onclick="editExamenM(${element.id})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
              <a class="btn btn-sm btn-danger" target="_blank" href="${pdfUrl}?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
              <a class="btn btn-sm btn-danger" target="_blank" href="php/Consentimiento.php?id=${element.id}"><i class="fa-solid fa-file-pdf"></i></a>
              <button onclick="eliminarExamenM(${element.id})" class="btn btn-sm btn-dark"><i class="fas fa-trash"></i></button>
            </td>`
          : "";

      body += `
      <tr>
        <td hidden>${element.id}</td>
        <td>${element.noemp}</td>
        <td>${element.nombre}</td>
        <td>${element.departamento}</td>
        <td>${element.puesto}</td>
        <td>${element.fecha}</td>        
        <td><center>${firmaConf}</center></td>
        ${val2}
      </tr>`;
    });
    document.getElementById(dom).innerHTML = body;
  }

  async eliminarExamenM(id) {
    const data = new FormData();
    data.append("id", id);
    const datapromise = await fetch("php/ExamenM.php?eliminarExamenM", {
      method: "POST",
      body: data,
    });
    return await datapromise.json();
  }
}
