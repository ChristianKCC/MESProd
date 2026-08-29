let infoMaquinas = [];
let infoMaquinasSinRed = [];
let currentPage = 1;
let pageSize = 20;

function checkInfinity(value) {
  if (value === Infinity || value === -Infinity || Number.isNaN(value)) {
    return 0;
  }
  return Number(value).toFixed(2);
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

  // Funcion que genera tabla de turnos anteriores. Aquí se usa ya axios
  async generarTabla(fecha, maquina, turno) {
    try {
      const formData = new FormData();
      formData.append("fecha", fecha);
      formData.append("maquina", String(maquina));
      formData.append("turno", String(turno));

      const endpoint =
        maquina == 67
          ? "../Reporteproduccion/php/producciones.php?infoTurnoHook"
          : "../Reporteproduccion/php/producciones.php?infoTurnosAnteriores";

      const response = await axios.post(endpoint, formData);

      console.log(response);

      if (maquina == 67) {
        mostrarTablaPor(67);
        infoMaquinas = response.data.map((item) => ({
          id: item.id,
          fecha: item.Fecha,
          turno: item.Turno,
          maquina: item.NoMaquina,
          NombreMaquina: item.NombreMaquina,
          metrosLineales: item.Metros,
          metros: item.Metros,
          tiempoAbajo: item.TiempoAbajo,
          tiempoArriba: item.TiempoArriba,
          parosMaquina: item.ParosMaquina,
          IBM: item.IBM,
        }));
        mostrarTablaHook();
      } else {
        mostrarTablaPor(null);
        infoMaquinas = response.data.map((item) => ({
          id: item.id,
          fecha: item.Fecha,
          turno: item.Turno,
          maquina: item.Maquina,
          NombreMaquina: item.NombreMaquina,
          cortes: item.Cortes,
          rechazos: item.Rechazos,
          tiempoAbajo: item.TiempoAbajo,
          minutosEnhebrando: item.MinutosEnhebrando,
          tiempoArriba: item.TiempoArriba,
          mermaMaquina: item.MermaMaquina,
          tiempoPerdido: item.TiempoPerdido,
          parosMaquina: item.ParosMaquina,
          IBM: item.IBM,
        }));
        mostrarTabla();
      }
    } catch (err) {
      console.error("Error en generarTabla:", err?.message || err);
      throw err;
    }
  }

  async generarTablaMaquinasSinRed(fecha, maquina, turno) {
    try {
      const formData = new FormData();
      formData.append("fecha", fecha);
      formData.append("maquina", String(maquina));
      formData.append("turno", String(turno));

      const response = await axios.post(
        "../Reporteproduccion/php/producciones.php?infoMaquinasSinRed",
        formData,
      );
      infoMaquinasSinRed = response.data.map((item) => ({
        id: item.IdEncabezadoBItacora,
        fecha: item.Fecha,
        turno: item.Turno,
        maquina: item.NoMaquina,
        nombreMaquina: item.NombreMaquina,
        cortes: item.golpes,
        rechazos: item.Rechazos,
        tiempoArriba: item.TiempoArriba,
        tiempoPerdido: item.TiempoPerdido,
        parosMaquina: item.ParosMaquina,
        minutosTurno: item.MinutosTurno,
        IBM: item.IBM,
      }));
      mostrarTablaMaquinasSinRed();
    } catch (error) {
      console.error("Error en generarTabla:", err?.message || err);
      throw err;
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

  async generarPDFHook(folio) {
    console.log(folio);
    try{
      const params = new URLSearchParams({ folio });
      const url = `php/reporteTurnosHook.php?${params.toString()}#zoom=150`;
      window.open(url, "_blank");
    }catch(error){
      console.error("Error:", error);
    }
  }

  async dataForActualizarRegistro(folio) {
    try {
      const { data } = await axios.get(
        "../Reporteproduccion/php/producciones.php",
        {
          params: {
            getDataRegistroTurno: "",
            folio: folio,
          },
          timeout: 15000,
        },
      );
      console.log(data);
      return data;
    } catch (error) {
      if (error.response) {
        console.error(
          "Error de servidor:",
          error.response.status,
          error.response.data,
        );
      } else if (error.request) {
        console.error("Sin respuesta del servidor:", error.message);
      } else {
        console.error("Error al configurar la solicitud:", error.message);
      }
      throw error;
    }
  }

  async dataForActualizarRegistroSinRed(folio) {
    try {
      const { data } = await axios.get(
        "../ReporteProduccion/php/producciones.php",
        {
          params: {
            getDataRegistroTurnoSinRed: "",
            folio: folio,
          },
          timeout: 15000,
        },
      );
      return data;
    } catch (error) {
      if (error.response) {
        console.error(
          "Error de servidor:",
          error.response.status,
          error.response.data,
        );
      } else if (error.request) {
        console.error("Sin respuesta del servidor:", error.message);
      } else {
        console.error("Error al configurar la solicitud:", error.message);
      }
      throw error;
    }
  }

  async dataForActualizarRegistroHook(folio){
    console.log(folio);
    try {
      const { data } = await axios.get("../Reporteproduccion/php/producciones.php", {
        params: {
          getDataRegistroTurnoHook: "",
          folio: folio,
        },
        timeout: 15000,
      },
    );
    return data;
    } catch (error) {
      if (error.response) {
        console.error(
          "Error de servidor:",
          error.response.status,
          error.response.data,
        );
      } else if (error.request) {
        console.error("Sin respuesta del servidor:", error.message);
      } else {
        console.error("Error al configurar la solicitud:", error.message);
      }
      throw error;
    }
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

    try {
      const response = await axios.post(
        "../Reporteproduccion/php/producciones.php?actualizarRegistroTurnoMaquina",
        data,
        {
          // Axios detecta FormData y pone el boundary automáticamente,
          // pero incluir el content-type puede ayudar en algunos servidores:
          headers: { "Content-Type": "multipart/form-data" },
          // Si tu backend necesita credenciales (cookies/sesión PHP):
          withCredentials: true,
        },
      );

      // Manejo de respuesta por status
      if (response.status === 200) {
        swal.fire("¡Listo!", "El registro se guardó con éxito", "success");
      } else {
        // Cubre otros 2xx que no sean 200, o casos raros
        swal.fire(
          "Atención",
          `Respuesta inesperada del servidor (status: ${response.status})`,
          "warning",
        );
      }
    } catch (error) {
      // Axios lanza excepción en status >= 400 o problemas de red
      if (error.response) {
        // El servidor respondió con un status fuera de 2xx
        const status = error.response.status;
        if (status === 500) {
          swal.fire("ERROR", "Hay un problema en la base de datos", "error");
        } else {
          // Muestra mensaje del servidor si existe
          const msg =
            error.response.data?.message ||
            `Error del servidor (status: ${status})`;
          swal.fire("ERROR", msg, "error");
        }
      } else if (error.request) {
        // No hubo respuesta (timeout, red, CORS, etc.)
        swal.fire(
          "ERROR",
          "No se recibió respuesta del servidor. Verifica tu conexión.",
          "error",
        );
      } else {
        // Error al configurar la petición
        swal.fire(
          "ERROR",
          `Error al enviar la solicitud: ${error.message}`,
          "error",
        );
      }
    }
  }

  async actualizarRegistroTurnoMaquinaSinRed(
    folio,
    folioBitacora,
    cortes,
    rechazos,
    tiempoAbajo,
    tiempoArriba,
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
    data.append("tiempoArriba", tiempoArriba);
    data.append("paros", paros);
    data.append("horasTrabajadas", horasTrabajadas);
    data.append("motivoCambio", motivoCambio);

    try {
      const response = await axios.post(
        "../Reporteproduccion/php/producciones.php?actualizarRegistroTurnoMaquinaSinRed",
        data,
        {
          // Axios detecta FormData y pone el boundary automáticamente,
          // pero incluir el content-type puede ayudar en algunos servidores:
          headers: { "Content-Type": "multipart/form-data" },
          // Si tu backend necesita credenciales (cookies/sesión PHP):
          withCredentials: true,
        },
      );

      // Manejo de respuesta por status
      if (response.status === 200) {
        swal.fire("¡Listo!", "El registro se guardó con éxito", "success");
      } else {
        // Cubre otros 2xx que no sean 200, o casos raros
        swal.fire(
          "Atención",
          `Respuesta inesperada del servidor (status: ${response.status})`,
          "warning",
        );
      }
    } catch (error) {
      // Axios lanza excepción en status >= 400 o problemas de red
      if (error.response) {
        // El servidor respondió con un status fuera de 2xx
        const status = error.response.status;
        if (status === 500) {
          swal.fire("ERROR", "Hay un problema en la base de datos", "error");
        } else {
          // Muestra mensaje del servidor si existe
          const msg =
            error.response.data?.message ||
            `Error del servidor (status: ${status})`;
          swal.fire("ERROR", msg, "error");
        }
      } else if (error.request) {
        // No hubo respuesta (timeout, red, CORS, etc.)
        swal.fire(
          "ERROR",
          "No se recibió respuesta del servidor. Verifica tu conexión.",
          "error",
        );
      } else {
        // Error al configurar la petición
        swal.fire(
          "ERROR",
          `Error al enviar la solicitud: ${error.message}`,
          "error",
        );
      }
    }
  }
}

function mostrarTablaPor(maquina) {
  const tablaNormal = document.getElementById("tablaNormal");
  const tablaHook = document.getElementById("tablaHook");

  if (maquina == 67) {
    tablaNormal.classList.add("d-none");
    tablaHook.classList.remove("d-none");
  } else {
    tablaHook.classList.add("d-none");
    tablaNormal.classList.remove("d-none");
  }
}

function mostrarTabla() {
  const tbody = document.getElementById("tableBody");
  tbody.innerHTML = "";

  const totalRegistros = infoMaquinas.length;
  const totalPaginas = Math.ceil(totalRegistros / pageSize);

  if (currentPage > totalPaginas) currentPage = totalPaginas || 1;

  const inicio = (currentPage - 1) * pageSize;
  const fin = inicio + pageSize;

  const paginaActualDatos = infoMaquinas.slice(inicio, fin);

  paginaActualDatos.forEach((element) => {
    // ----- Esta version es por si se quiere omitir registros con cortes o rechazos en 0 ------
    // Si Cortes o Rechazos es 0, no mostrar el registro
    // const cortes = Number(element.cortes);
    // const rechazos = Number(element.rechazos);
    // if (cortes === 0 || rechazos === 0) return;

    const specialMachines = [60, 61, 62, 63, 64];

    const tiempoAbajoRaw = specialMachines.includes(Number(element.maquina))
      ? Number(element.tiempoAbajo) / 60
      : Number(element.tiempoAbajo);
    const tiempoAbajo =
      tiempoAbajoRaw === Infinity ||
      tiempoAbajoRaw === -Infinity ||
      Number.isNaN(tiempoAbajoRaw)
        ? 0
        : Math.round(Number(tiempoAbajoRaw));

    const minutosEnhebrandoRaw = specialMachines.includes(
      Number(element.maquina),
    )
      ? Number(element.minutosEnhebrando) / 60
      : Number(element.minutosEnhebrando);
    const minutosEnhebrando =
      minutosEnhebrandoRaw === Infinity ||
      minutosEnhebrandoRaw === -Infinity ||
      Number.isNaN(minutosEnhebrandoRaw)
        ? 0
        : Math.round(Number(minutosEnhebrandoRaw));

    const tiempoArribaRaw = specialMachines.includes(Number(element.maquina))
      ? Number(element.tiempoArriba) / 60
      : Number(element.tiempoArriba);
    const tiempoArriba =
      tiempoArribaRaw === Infinity ||
      tiempoArribaRaw === -Infinity ||
      Number.isNaN(tiempoArribaRaw)
        ? 0
        : Math.round(Number(tiempoArribaRaw));

    const tiempoPerdidoRaw = specialMachines.includes(Number(element.maquina))
      ? Number(element.tiempoPerdido) / 60
      : Number(element.tiempoPerdido);
    const tiempoPerdido =
      tiempoPerdidoRaw === Infinity ||
      tiempoPerdidoRaw === -Infinity ||
      Number.isNaN(tiempoPerdidoRaw)
        ? 0
        : Math.round(Number(tiempoPerdidoRaw));

    const mermaMaquina =
      Number(element.cortes) !== 0
        ? checkInfinity(
            (Number(element.rechazos) / Number(element.cortes)) * 100,
          )
        : "0.00";

    const editableIBMs = [
      58998, 31773, 33802, 31578, 57723, 33279, 57118, 34374, 46473,
    ];
    const isLocked = !editableIBMs.includes(Number(element.IBM));
    const editButtonAttrs = isLocked
      ? 'disabled aria-disabled="true" title="Bloqueado"'
      : `data-bs-toggle="modal" data-bs-target="#modadalEditRegistroTurno" data-bs-whatever="${element.id}"`;
    const editBtnClass = isLocked
      ? "btn btn-sm btn-warning disabled"
      : "btn btn-sm btn-warning";
    const row = `
        <tr>
            <td>${element.fecha}</td>
            <td>${element.turno}</td>
            <td>${element.NombreMaquina}</td>
            <td>${element.cortes}</td>
            <td>${element.rechazos}</td>
            <td>${Number(element.tiempoAbajo).toFixed(2)}</td>
            <td>${Number(element.tiempoArriba).toFixed(2)}</td>
            <td>${mermaMaquina} %</td>
            <td>${element.parosMaquina}</td>
            <td>
                <center>
                    <button class="${editBtnClass}" ${editButtonAttrs}><i class="fas fa-pen"></i> Editar Registro</button>
                    <button class="btn btn-sm btn-danger" onclick="reporteMaquinaAnterior(${element.id})"><i class="fas fa-file-pdf"></i> Generar reporte</button>
                </center>
            </td>
        </tr>
    `;
    tbody.innerHTML += row;
  });
  document.getElementById("pageInfo").innerText =
    `Página ${currentPage} de ${totalPaginas}`;
}

function mostrarTablaMaquinasSinRed() {
  const tbody = document.getElementById("tableBody");
  tbody.innerHTML = "";

  const totalRegistros = infoMaquinasSinRed.length;
  const totalPaginas = Math.ceil(totalRegistros / pageSize);

  if (currentPage > totalPaginas) currentPage = totalPaginas || 1;

  const inicio = (currentPage - 1) * pageSize;
  const fin = inicio + pageSize;

  const paginaActualDatos = infoMaquinasSinRed.slice(inicio, fin);

  paginaActualDatos.forEach((element) => {
    const tiempoPerdidoPorc =
      element.MinutosTurno !== 0
        ? checkInfinity(
            (Number(element.tiempoPerdido) / Number(element.minutosTurno)) *
              100,
          )
        : "0.00";

    const rechazos = element.rechazos == null ? 0 : element.rechazos;
    const cortes = element.cortes == null ? 0 : element.cortes;
    const filaFecha = element.fecha || element.Fecha || "";
    const filaMaquina = element.maquina ?? element.NoMaquina ?? "0";
    const filaTurno = element.turno || element.Turno || "";
    const mermaMaquina =
      Number(cortes) !== 0 && Number(rechazos) !== 0
        ? checkInfinity((Number(rechazos) / Number(cortes)) * 100)
        : "0.00";
    const editableIBMs = [
      58998, 31773, 33802, 31578, 57723, 33279, 57118, 34374,
    ];
    const isLocked = !editableIBMs.includes(Number(element.IBM));
    const editButtonAttrs = isLocked
      ? 'disabled aria-disabled="true" title="Bloqueado"'
      : `data-bs-toggle="modal" data-bs-target="#modalEditRegistroTurnoSinRed" data-bs-whatever="${element.id}"`;
    const editBtnClass = isLocked
      ? "btn btn-sm btn-warning disabled"
      : "btn btn-sm btn-warning";

    const row = `
    <tr>
        <td>${filaFecha}</td>
        <td>${filaTurno}</td>
        <td>${element.nombreMaquina}</td>
        <td>${cortes}</td>
        <td>${rechazos}</td>
        <td>${element.tiempoPerdido}</td>
        <td>${element.tiempoArriba}</td>
        <td>${mermaMaquina} %</td>
        <td>${element.parosMaquina}</td>
        <td>
          <center>
            <button class="${editBtnClass}" ${editButtonAttrs}><i class="fas fa-pen"></i> Editar Registro</button>
            <button class="btn btn-sm btn-danger" onclick="reporteMaquinasSinRed('${element.fecha}', ${element.maquina}, '${element.turno}')"><i class="fas fa-file-pdf"></i> Generar reporte</button>
          </center>
        </td>
      </tr>
    `;
    tbody.innerHTML += row;
  });
  document.getElementById("pageInfo").innerText =
    `Página ${currentPage} de ${totalPaginas}`;
}

function mostrarTablaHook() {
  const tbody = document.getElementById("tableBodyHook");
  tbody.innerHTML = "";
  const editableIBMs = [
      58998, 31773, 33802, 31578, 57723, 33279, 57118, 34374, 46473,
    ];
  infoMaquinas.forEach((item) => {
    console.log(item.IBM);

    const isLocked = !editableIBMs.includes(Number(item.IBM));
    const editButtonAttrs = isLocked
      ? 'disabled aria-disabled="true" title="Bloqueado"'
      : `data-bs-toggle="modal" data-bs-target="#modadalEditRegistroTurnoHook" data-bs-whatever="${item.id}"`;
    const editBtnClass = isLocked
      ? "btn btn-sm btn-warning disabled"
      : "btn btn-sm btn-warning";
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${item.fecha}</td>
      <td>${item.turno}</td>
      <td>${item.NombreMaquina}</td>
      <td>${item.metrosLineales.toFixed(2) ?? "-"}</td>
      <td>${item.tiempoAbajo.toFixed(2) ?? "-"}</td>
      <td>${item.tiempoArriba.toFixed(2) ?? "-"}</td>
      <td>${item.parosMaquina ?? "-"}</td>
      <td>
        <center>
            <button class="${editBtnClass}" ${editButtonAttrs}><i class="fas fa-pen"></i> Editar Registro</button>
            <button class="btn btn-sm btn-danger" onclick="reporteAnteriorHook(${item.id})"><i class="fas fa-file-pdf"></i> Generar reporte</button>
        </center>
      </td>
    `;
    tbody.appendChild(tr);
  });
} 

// === Buscador ===
// document.getElementById("searchInput").addEventListener("input", (e) => {
//   currentPage = 1;
//   mostrarTabla(e.target.value);
// });

// === Cambiar cantidad por página ===
document.getElementById("pageSize").addEventListener("change", (e) => {
  pageSize = parseInt(e.target.value);
  currentPage = 1;
  mostrarTabla(document.getElementById("searchInput").value);
});

// === Paginación ===
document.getElementById("prevPage").addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    mostrarTabla(document.getElementById("searchInput").value);
  }
});

document.getElementById("nextPage").addEventListener("click", () => {
  currentPage++;
  mostrarTabla(document.getElementById("searchInput").value);
});
