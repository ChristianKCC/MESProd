export class AccionesCorrectivas {
  async consultaAcciones(dom) {
    const dataPromise = await fetch("php/accCorrectivas.php?consultaAcciones");
    const dataRaw = await dataPromise.json();
    let body = "";

    dataRaw.forEach((element) => {
      body += `
        <tr>
          <td>${element.id}</td>
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
          <td>${element.folioenc}</td>
          <td>
            <button onclick="registrarAccion(${element.id})" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fas fa-edit"></i></button>
          </td>
        </tr>
      `;
    });
    document.getElementById(dom).innerHTML = body;
  }

  async guardarRegistroAcciones(
    folio,
    comentarios,
    fecha,
    checkRegAcc,
    archivo
  ) {
    const file = archivo.files[0];

    const data = new FormData();

    data.append("folio", folio);
    data.append("comentarios", comentarios);
    data.append("fecha", fecha);
    data.append("checkRegAcc", checkRegAcc);
    data.append("file", file);

    const dataPromise = await fetch(
      "php/accCorrectivas.php?saveRegistroAcciones",
      {
        method: "POST",
        body: data,
      }
    );

    if (dataPromise.status === 200) {
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    } else {
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
    }
  }
}

export class Incidencias {
  async consultaIncidencias(dom) {
    const dataPromise = await fetch("php/accCorrectivas.php?datosIncidencias");
    const dataRaw = await dataPromise.json();
    console.log(dataRaw);
    let body = "";

    dataRaw.forEach((element) => {
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
          <td>${element.Version}</td>
          <td>${element.Evento}</td>
          <td>${element.AntEmpresa}</td>
          <td>${element.AntPuesto}</td>
          <td>${element.diasincapacidad}</td>
          <td>${element.diastrabajo}</td>
          <td>${element.TipContacto}</td>
          <td>${element.TipLesion}</td>
          <td>${element.provoco}</td>
          <td>${element.ParteCuerpoAfectada}</td>
          <td>${element.Severidad}</td>
          <td>${element.Probabilidad}</td>
          <td>${element.Frecuencia}</td>
          <td>${element.Personas}</td>
          <td>${element.Evidencia}</td>
          <td>${element.lesion}</td>
          <td>${element.equipos}</td>
          <td>
            <center>
              <button onclick="aceptarIncidencia(${element.Folio})" id="btnAceptar" class="btn btn-sm btn-primary"><i class="fas fa-save"></i></button>
              <button onclick="rechazarIncidencia(${element.Folio})" id="btnRechazar" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </center>
          </td>
        </tr>
      `;
    });
    document.getElementById(dom).innerHTML = body;
  }

  async actualizarEstadoIncidencia(folio, estado) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("estado", estado);

    const dataPromise = await fetch("php/accCorrectivas.php?saveIncidencias", {
      method: "POST",
      body: data,
    });
    console.log(dataPromise);

    dataPromise.status === 200 &&
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    dataPromise.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }
}
