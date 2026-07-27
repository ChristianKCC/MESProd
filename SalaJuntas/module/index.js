export class SalaJuntas {
  // Obtener datos del empleado
  async datauserEmpleado(noemp) {
    const data = new FormData();
    data.append("noemp", noemp);
    const datapromise = await fetch("php/Salas.php?dataUserCompleate", {
      method: "POST",
      body: data,
    });
    const dataraw = await datapromise.json();
    return dataraw;
  }

  // Reservar una reunión en una slaa
  async saveReservacionSala(
    numeroSala,
    noemp,
    fecha,
    horaInicio,
    horaFinal,
    titulo,
    descripcion,
    checkActivo,
    estado
  ) {
    const data = new FormData();
    data.append("numeroSala", numeroSala);
    data.append("noemp", noemp);
    data.append("fecha", fecha);
    data.append("horaInicio", horaInicio);
    data.append("horaFinal", horaFinal);
    data.append("titulo", titulo);
    data.append("descripcion", descripcion);
    data.append("capacitacion", checkActivo);
    data.append("estado", estado);

    const dataPromise = await fetch("php/Salas.php?saveReservacionSala", {
      method: "POST",
      body: data,
    });

    if (dataPromise.status === 200) {
      swal.fire("¡Listo!", "El registro se guardó con éxito", "success");
    } else if (dataPromise.status === 409) {
      const msg = await dataPromise.text();
      swal.fire("¡Conflicto!", msg, "warning");
    } else if (dataPromise.status === 500) {
      swal.fire("¡Error!", "Hubo un problema con la base de datos", "error");
    }
  }

  // Consultar las salas de juntas que se tienen paa poder seleccionar y asignar
  async tblConsultaInfoSala(dom, tipo = 0) {
    const dataPromise = await fetch("php/Salas.php?infoSalas");
    const dataraw = await dataPromise.json();

    let body = "";
    tipo == 0
      ? (body = `<option value = "">Selecciona una opción</option>`)
      : (body = "");
    dataraw.forEach((element) => {
      body += `<option value="${element.id}"> ${element.sala} </option>`;
    });
    document.getElementById(dom).innerHTML = body;
  }

  // Obtener la info de salas reservadas para posteriormente agendar o rechazar
  async tblConsultaInfoSalaReservada(dom) {
    const dataPromise = await fetch("php/Salas.php?salasReservadasPorSesion");
    const dataraw = await dataPromise.json();

    let body = '<div class="row">';
    dataraw.forEach((element, index) => {
      const checked = element.capacitacion === 1 ? "checked" : "";
      if (element.estado === 1) {
        body += `
      <div class="col-md-6 mb-4">
        <div class="card">
          <input type="hidden" name="id_${index}" id="id_${index}" value="${
          element.id
        }">
          <h5 class="card-header" id="numeroSala_${index}">Sala - ${
          element.NombreSala
        }
            ${
              element.pendiente == 1
                ? '<span style="color:red; font-size: 1.2rem;"> - Pendiente de aprobación</span>'
                : ""
            }
          </h5>
          <div class="card-body">
            <div class="row">
              <div class="col-10">
                <div class="row">
                  <div class="col-6">
                    <span class="fw-bold">Nombre del encargado</span>
                    <p class="card-text" id="nombreAgenda_${index}">${
          element.administrador
        }</p>
                  </div>
                  <div class="col-6">
                    <span class="fw-bold">Nombre del solicitante</span>
                    <p class="card-text" id="nombreAgenda_${index}">${
          element.NombreAgenda
        }</p>
                  </div>
                </div>
                <br>
                <div class="row">
                  <div class="col-6">
                    <span class="fw-bold">Titulo de la Reunión</span>
                    <p class="card-text" id="nombreAgenda_${index}">${
          element.titulo
        }</p>
                  </div>
                  <div class="col-6">
                    <span class="fw-bold">Descripción de la reunión</span>
                    <p class="card-text" id="infoSala_${index}">${
          element.descripcion
        }</p>
                  </div>
                </div>
                <br>
                <div class="row">
                  <div class="col-3">
                    <span class="fw-bold">Fecha de la reunión</span>
                    <br>
                    <span id="fecha_${index}">${element.fecha}</span>
                  </div>
                  <div class="col-3">
                    <span class="fw-bold">Hora de Inicio</span>
                    <br>
                    <span name="horaInicio" id="horaInicio_${index}">${
          element.HoraInicio
        }</span>
                  </div>
                  <div class="col-3">
                    <span class="fw-bold">Hora de Finalización</span>
                    <br>
                    <span name="horaFinal" id="horaFinal_${index}">${
          element.HoraFin
        }</span>
                  </div>
                  <div class="col-1">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="capacitacion_${index}" name="capacitacion_${index}" ${checked} disabled>
                      <label class="form-check-label" for="capacitacion_${index}">Capacitación</label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-2">
                <br>
                <center>
                  <button class="btn btn-md bg-target" id="agendarSala_${index}">Agendar</button>
                  <br><br>
                  <button class="btn btn-md btn-danger" id="rechazarSala_${index}">Rechazar</button>
                </center>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
      }
    });
    body += "</div>"; // Cierra el <div class="row">

    document.getElementById(dom).innerHTML = body;

    // Por cada elemento, asignar eventos para actualizar el estado para Agendar o Rechazar
    dataraw.forEach((element, index) => {
      if (element.estado !== 2 || element.estado !== 3) {
        const btnAgendar = document.getElementById(`agendarSala_${index}`);
        const btnRechazar = document.getElementById(`rechazarSala_${index}`);
        const idReunion = element.id;

        if (btnAgendar) {
          btnAgendar.addEventListener("click", async () => {
            await this.actualizarEstadoReunion(idReunion, 2);
            this.tblConsultaInfoSalaReservada(dom);
          });
        }

        if (btnRechazar) {
          btnRechazar.addEventListener("click", async () => {
            await this.actualizarEstadoReunion(idReunion, 3);
            this.tblConsultaInfoSalaReservada(dom);
          });
        }
      }
    });
  }

  // Actualizar el valor Estado debido a un valor dado
  async actualizarEstadoReunion(id, nuevoEstado) {
    console.log(id);
    console.log(nuevoEstado);

    const data = new FormData();
    data.append("id", id);
    data.append("estado", nuevoEstado);

    const dataPromise = await fetch("php/Salas.php?actualizarEstado", {
      method: "POST",
      body: data,
    });

    dataPromise.status === 200 &&
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    dataPromise.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }

  // Obtener datos y rellenar posteriormente los elementos li
  async dataJuntas(idSala) {
    const data = new FormData();
    data.append("idSala", idSala);
    const dataPromise = await fetch("php/Salas.php?dataJuntasAgendadas", {
      method: "POST",
      body: data,
    });
    const dataRaw = await dataPromise.json();
    return dataRaw;
  }

  // Obtener datos y rellenar tabla con la informacion
  async mostrarTabla(dom) {
    const dataPromise = await fetch(
      "php/Salas.php?mostrarTablaSalasReservadas"
    );
    const dataRaw = await dataPromise.json();
    let body = "";
    dataRaw.forEach((element) => {
      body += `
        <tr>
          <td>${element.id}</td>
          <td>${element.noemp}</td>
          <td>${element.NombreAgenda}</td>
          <td>${element.titulo}</td>
          <td>${element.descripcion}</td>
          <td>${element.fecha}</td>
          <td>${element.HoraInicio} - ${element.HoraFin}</td>
          <td>${element.capacitacion == 1 ? "Si" : "No"}</td>
          <td>${
            element.estado === 1
              ? "Pendiente"
              : element.estado === 2
              ? "Autorizada"
              : element.estado === 3
              ? "Rechazada"
              : "Terminada"
          }</td>
        </tr>
      `;
    });
    document.getElementById(dom).innerHTML = body;
  }

  // Guardar registro de reunión mediante reconocimiento facial
  async guardarRegistroSala(idReunion, noEmp) {
    const data = new FormData();
    data.append("idReunion", idReunion);
    data.append("noEmp", noEmp);

    data.forEach((value, key) => {
      console.log(`${key}: ${value}`);
    });

    const dataPromise = await fetch("php/Salas.php?guardarRegistroReunion", {
      method: "POST",
      body: data,
    });

    console.log(dataPromise);
    if (dataPromise.status === 200) {
      swal.fire("¡Listo!", "El registro se guardó con éxito", "success");
    } else if (dataPromise.status === 500) {
      swal.fire("¡Error!", "Hubo un problema con la base de datos", "error");
    }
  }
}

// Actualizar estado global de las reuniones que ya hayan terminado su hora
function actualizarEstadosGlobal() {
  fetch("php/Salas.php?actualizarEstadoReuniones")
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error al actualizar estados:", data.error);
      }
    })
    .catch((error) => console.error("Error de conexión:", error));
}

function actualizarEstadosGlobalCaduc() {
  fetch("php/Salas.php?actualizarEstadoReunionesCaducadas")
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error al actualizar estados:", data.error);
      }
    })
    .catch((error) => console.error("Error de conexión:", error));
}

setInterval(actualizarEstadosGlobal, 15000);
setInterval(actualizarEstadosGlobalCaduc, 15000);
