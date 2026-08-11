// class Folios {
//     async consulta() {
//         const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
//         const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

//         const respuestaRaw = await fetch(`php/index.php?tblencfolioSupInt&fecha=${fechaFiltro}&estatus=${estatusFiltro}`);
//         const respuesta = await respuestaRaw.json();

//         let body = "";
//         respuesta.forEach(folio => {
//             let estadoClass = "badge bg-warning text-dark";
//             let estadoTexto = "En espera de pre-aprobación/rechazo por Super Intendente";

//             let estClass = "badge bg-warning text-dark";
//             let estTexto = "En espera de aprobación de gerente";

//             if (folio.autorizaSupInt == 1) {
//                 estadoClass = "badge bg-success";
//                 estadoTexto = "Pre-Aprobado por Super Intendente";
//             } else if (folio.autorizaSupInt == 2) {
//                 estadoClass = "badge bg-danger";
//                 estadoTexto = "Rechazado por Super Intendente";
//             }

//             if (folio.autorizado == 1) {
//                 estClass = "badge bg-success";
//                 estTexto = "Aprobado por gerente";
//             } else if (folio.autorizado == 2) {
//                 estClass = "badge bg-danger";
//                 estTexto = "Rechazado por gerente";
//             }

//             let accionesHtml = `
//                 <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
//                     <i class="fa-solid fa-file-pdf"></i> Pre. PDF
//                 </button>
//             `;

//             if (folio.autorizaSupInt == null || folio.autorizaSupInt === "") {
//                 if (folio.pendientesValidar == 0) {
//                     accionesHtml = `
//                         <button class="btn btn-success btn-sm" onclick="autorizarFolio(${folio.id},1)">
//                             <i class="fa-solid fa-circle-check"></i> Autorizar
//                         </button>
//                         <button class="btn btn-danger btn-sm" onclick="autorizarFolio(${folio.id},2)">
//                             <i class="fa-solid fa-circle-xmark"></i> Rechazar
//                         </button>
//                         ${accionesHtml}
//                     `;
//                 } else {
//                     accionesHtml = `
//                         <span class="badge bg-secondary">Folio aún no validado por el supervisor</span>
//                         ${accionesHtml}
//                     `;
//                 }
//             } else {
//                 accionesHtml = `
//                     <span class="${estadoClass}">${estadoTexto}</span>
//                     ${accionesHtml}
//                 `;
//             }

//             body += `
//                 <tr>
//                     <td>${folio.id}</td>
//                     <td>${folio.supervisor}</td>
//                     <td>${folio.NombreSupervisor ?? ""}</td>
//                     <td>${folio.departamento}</td>
//                     <td>${folio.fecha}</td>
//                     <td><span class="${estadoClass}">${estadoTexto}</span>  <span class="${estClass}">${estTexto}</span></td>
//                     <td>${accionesHtml}</td>
//                 </tr>
//             `;
//         });
//         document.getElementById("tblFolios").innerHTML = body;
//     }
// }

// window.Folios = new Folios();
// window.Folios.consulta();

class Folios {
  async consulta() {
    const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
    const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

    const ibmFiltro = document.getElementById("filtroIbm")?.value || "";
    const respuestaRaw = await fetch(
      `php/index.php?tblencfolioSupInt&fecha=${fechaFiltro}&ibm=${ibmFiltro}`,
    );
    const respuesta = await respuestaRaw.json();

    let pendientes = "";
    let procesadas = "";
    let rechazadas = "";
    let countPendientes = 0;
    let countProcesadas = 0;
    let countRechazadas = 0;

    respuesta.forEach((folio) => {
      // Normalizamos ambos campos
      const supIntStatus =
        folio.autorizaSupInt == null ? 0 : Number(folio.autorizaSupInt);
      const gerenteStatus =
        folio.autorizado == null ? 0 : Number(folio.autorizado);

      let estadoClass = "badge bg-warning text-dark";
      let estadoTexto =
        "En espera de pre-aprobación/rechazo por Super Intendente";

      if (supIntStatus === 1) {
        estadoClass = "badge bg-success";
        estadoTexto = "Pre-Aprobado por Super Intendente";
      } else if (supIntStatus === 2) {
        estadoClass = "badge bg-danger";
        estadoTexto = "Pre-Rechazado por Super Intendente";
      } else if (gerenteStatus === 2) {
        estadoClass = "badge bg-danger";
        estadoTexto = "Rechazado por Gerente";
      }

      let accionesHtml = `
    <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
      <i class="fa-solid fa-file-pdf"></i> Pre. PDF
    </button>
  `;

      if (supIntStatus === 0 && gerenteStatus !== 2) {
        accionesHtml = `
      <button class="btn btn-success btn-sm" onclick="autorizarFolio(${folio.id},1)">
        <i class="fa-solid fa-circle-check"></i> Autorizar
      </button>
      <button class="btn btn-danger btn-sm" onclick="autorizarFolio(${folio.id},2)">
        <i class="fa-solid fa-circle-xmark"></i> Rechazar
      </button>
      ${accionesHtml}
    `;
      }

      let row = `
    <tr>
      <td>${folio.id}</td>
      <td>${folio.supervisor}</td>
      <td>${folio.NombreSupervisor ?? ""}</td>
      <td>${folio.departamento}</td>
      <td>${folio.fecha}</td>
      <td><span class="${estadoClass}">${estadoTexto}</span></td>
      <td>${accionesHtml}</td>
    </tr>
  `;

      // Clasificación final
      if (supIntStatus === 0 && gerenteStatus !== 2) {
        pendientes += row;
        countPendientes++;
      } else if (supIntStatus === 1) {
        procesadas += row;
        countProcesadas++;
      } else if (supIntStatus === 2 || gerenteStatus === 2) {
        rechazadas += row;
        countRechazadas++;
      }
    });

    document.getElementById("tblFoliosPendientes").innerHTML = pendientes;
    document.getElementById("tblFoliosProcesadas").innerHTML = procesadas;
    document.getElementById("tblFoliosRechazadas").innerHTML = rechazadas;
    document.getElementById("countPendientes").innerText = countPendientes;
    document.getElementById("countProcesadas").innerText = countProcesadas;
    document.getElementById("countRechazadas").innerText = countRechazadas;
  }
}

window.Folios = new Folios();
window.Folios.consulta();

// Función para autorizar/rechazar folio
window.autorizarFolio = async function (id, accion) {
  const verificacion = await fetch("./php/verificar_firma.php")
    .then((r) => r.json())
    .catch(() => null);

  if (!verificacion?.success) {
    Swal.fire({
      icon: "warning",
      title: "Firma no registrada",
      text: "Debes registrar tu firma primero antes de autorizar el tiempo extra. Consulta a RI para el registro de tu firma digital.",
      confirmButtonText: "Entendido",
      confirmButtonColor: "#f0ad4e",
    });
    return;
  }

  const respuestaRaw = await fetch(
    `php/index.php?autorizafolSupInt&id=${id}&autor=${accion}`,
  );
  const respuesta = await respuestaRaw.json();

  if (respuesta === "Listo") {
    Swal.fire(
      "Éxito",
      `La solicitud con el folio ${id} fue procesada correctamente.`,
      "success",
    ).then(() => window.Folios.consulta());
  } else {
    Swal.fire("Error", "No se pudo procesar el folio", "error");
  }
};

// Función para abrir el PDF de un folio
window.verPDF = function (id) {
  if (!id) {
    Swal.fire("UPS!!!", "No hay un folio válido", "info");
    return false;
  }
  window.open("./pdf/reporte.php?folio=" + btoa(id));
};
