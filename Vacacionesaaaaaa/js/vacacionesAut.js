class Vacaciones {
  async consulta() {
    const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
    const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

    const ibmFiltro = document.getElementById("filtroIbm")?.value || "";
    const respuestaRaw = await fetch(
      `php/index.php?tblVacacionesEnc&fecha=${fechaFiltro}&ibm=${ibmFiltro}`,
    );
    // const respuestaRaw = await fetch(
    //   `php/index.php?tblVacacionesEnc&fecha=${fechaFiltro}&estatus=${estatusFiltro}`,
    // );
    const respuesta = await respuestaRaw.json();

    let pendientes = "";
    let procesadas = "";
    let rechazadas = "";
    let countPendientes = 0;
    let countProcesadas = 0;
    let countRechazadas = 0;

    respuesta.forEach((folio) => {
      let estadoClass = "badge bg-warning text-dark";
      let estadoTexto = "En espera de aprobación/rechazo por gerente";

      if (folio.autorizado == 1) {
        estadoClass = "badge bg-success";
        estadoTexto = "Aprobado por gerente";
      } else if (folio.autorizado == 2) {
        estadoClass = "badge bg-danger";
        estadoTexto = "Rechazado por gerente";
      }

      let accionesHtml = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Revisar info. en PDF
                </button>
            `;

      if (!folio.autorizado || folio.autorizado === 0) {
        accionesHtml = `
                    <button class="btn btn-success btn-sm" onclick="autorizarVac(${folio.id},1)">
                        <i class="fa-solid fa-circle-check"></i> Autorizar
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="autorizarVac(${folio.id},2)">
                        <i class="fa-solid fa-circle-xmark"></i> Rechazar
                    </button>
                    ${accionesHtml}
                `;
      }

      let row = `
                <tr>
                    <td>${folio.id}</td>
                    <td>${folio.noemp}</td>
                    <td>${folio.nombre}</td>
                    <td>${folio.departamento}</td>
                    <td>${folio.fecha}</td>
                    <td><span class="${estadoClass}">${estadoTexto}</span></td>
                    <td>${accionesHtml}</td>
                </tr>
            `;

      if (folio.autorizado == 1) {
        procesadas += row;
        countProcesadas++;
      } else if (!folio.autorizado || folio.autorizado === 0) {
        pendientes += row;
        countPendientes++;
      } else if (folio.autorizado == 2) {
        rechazadas += row;
        countRechazadas++;
      }
    });

    document.getElementById("tblPendientes").innerHTML = pendientes;
    document.getElementById("tblProcesadas").innerHTML = procesadas;
    document.getElementById("tblRechazadas").innerHTML = rechazadas;
    document.getElementById("countPendientes").innerText = countPendientes;
    document.getElementById("countProcesadas").innerText = countProcesadas;
    document.getElementById("countRechazadas").innerText = countRechazadas;
  }
}

window.Vacaciones = new Vacaciones();
window.Vacaciones.consulta();

window.autorizarVac = async function (id, accion) {
  // Verificación de firma solo si es autorización
  if (accion === 1) {
    const verificacion = await fetch(
      "../../KCMes/Tiempoextra/php/verificar_firma.php",
    )
      .then((res) => res.json())
      .catch(() => null);

    if (!verificacion || !verificacion.success) {
      Swal.fire({
        icon: "warning",
        title: "Firma no registrada",
        text: "Debes registrar tu firma primero antes de autorizar el tiempo extra. Consulta a RI para el registro de tu firma digital.",
        confirmButtonText: "Entendido",
        confirmButtonColor: "#f0ad4e",
      });
      return;
    }
  }

  const respuestaRaw = await fetch(
    `php/index.php?autorizaVac&id=${id}&autor=${accion}`,
  );
  const respuesta = await respuestaRaw.json();

  if (respuesta === "Listo") {
    Swal.fire(
      "Éxito",
      `La solicitud con folio ${id} fue procesada correctamente.`,
      "success",
    ).then(() => window.Vacaciones.consulta());
  } else {
    Swal.fire("Error", "No se pudo procesar la solicitud", "error");
  }
};

window.verPDF = function (id) {
  if (!id) {
    Swal.fire("UPS!!!", "No hay un folio válido", "info");
    return false;
  }
  window.open("./pdf/GenPDF?folio=" + btoa(id));
};
