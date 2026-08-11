// class Folios {
//     async consulta() {
//         const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
//         const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

//         const respuestaRaw = await fetch(`php/index.php?tblencfolio&fecha=${fechaFiltro}&estatus=${estatusFiltro}`);
//         const respuesta = await respuestaRaw.json();

//         let body = "";
//         respuesta.forEach(folio => {
//             let estadoClass = "badge bg-warning text-dark";
//             let estadoTexto = "En espera de aprobación/rechazo por gerente";

//             if (folio.estadoTer == 1) {
//                 estadoClass = "badge bg-success";
//                 estadoTexto = "Aprobado por gerente";
//             } else if (folio.estadoTer == 2) {
//                 estadoClass = "badge bg-danger";
//                 estadoTexto = "Rechazado por gerente";
//             }

//             let accionesHtml = `
//                 <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
//                     <i class="fa-solid fa-file-pdf"></i> Consultar PDF
//                 </button>
//             `;

//             // Acciones para superintendente
//             let espera = `
//                 <span class="badge bg-info text-dark">
//                     Solicitud aún no validada por SuperIntendente
//                 </span>
//             `;

//             if (folio.noempSupIntendente && folio.autorizaSupInt === null) {
//                 accionesHtml = `
//                     ${espera}
//                 `;
//             } else {

//                 if (folio.estadoTer == null || folio.estadoTer === "") {
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
//                         <span class="${estadoClass}">${estadoTexto}</span>
//                         ${accionesHtml}
//                     `;
//                 }
//             }

//             body += `
//                 <tr>
//                     <td>${folio.id}</td>
//                     <td>${folio.NoempSupervisor}</td>
//                     <td>${folio.NombreSupervisor ?? ""}</td>
//                     <td>${folio.fechai}</td>
//                     <td>${folio.fechaf}</td>
//                     <td><span class="${estadoClass}">${estadoTexto}</span></td>
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
      `php/index.php?tblencfolio&fecha=${fechaFiltro}&ibm=${ibmFiltro}`,
    );
    const respuesta = await respuestaRaw.json();

    let pendientes = "";
    let procesadas = "";
    let rechazadas = "";
    let countPendientes = 0;
    let countProcesadas = 0;
    let countRechazadas = 0;

    respuesta.forEach((folio) => {
      const supIntStatus =
        folio.autorizaSupInt == null ? 0 : Number(folio.autorizaSupInt);
      const gerenteStatus =
        folio.estadoTer == null || folio.estadoTer === ""
          ? 0
          : Number(folio.estadoTer);

      let estadoClass = "badge bg-warning text-dark";
      let estadoTexto = "En espera de aprobación/rechazo por gerente";

      if (gerenteStatus === 1) {
        estadoClass = "badge bg-success";
        estadoTexto = "Aprobado por gerente";
      } else if (gerenteStatus === 2) {
        estadoClass = "badge bg-danger";
        estadoTexto = "Rechazado por gerente";
      }

      let accionesHtml = `
        <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
          <i class="fa-solid fa-file-pdf"></i> Consultar PDF
        </button>
      `;

      if (gerenteStatus === 0) {
        accionesHtml = `
          <button class="btn btn-success btn-sm" onclick="autorizarFolio(${folio.id},1)">
            <i class="fa-solid fa-circle-check"></i> Autorizar
          </button>
          <button class="btn btn-danger btn-sm" onclick="autorizarFolio(${folio.id},2)">
            <i class="fa-solid fa-circle-xmark"></i> Rechazar
          </button>
          ${accionesHtml}
        `;
      } else {
        accionesHtml = `<span class="${estadoClass}">${estadoTexto}</span> ${accionesHtml}`;
      }

      let row = `
        <tr>
          <td>${folio.id}</td>
          <td>${folio.NoempSupervisor}</td>
          <td>${folio.NombreSupervisor ?? ""}</td>
          <td>${folio.fechai}</td>
          <td>${folio.fechaf}</td>
          <td><span class="${estadoClass}">${estadoTexto}</span></td>
          <td>${accionesHtml}</td>
        </tr>
      `;

      if (gerenteStatus === 0 && supIntStatus === 0) {
        pendientes += row;
        countPendientes++;
      } else if (gerenteStatus === 1) {
        procesadas += row;
        countProcesadas++;
      } else if (gerenteStatus === 2 || supIntStatus === 2) {
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

window.Folios = new Folios();
window.Folios.consulta();

// Funcion para autorizar el tiempo extra con parametros como el id y quien lo autoriza
window.autorizarFolio = async function (id, accion) {
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

  const respuestaraw = await fetch(
    `./php/index.php?autorizafol&id=${id}&autor=${accion}`,
  );
  const respuesta = await respuestaraw.json();

  if (respuesta === "Listo") {
    if (accion === 1) {
      Swal.fire({
        icon: "success",
        title: "Autorizado",
        text: "El registro fue autorizado con éxito",
        timer: 2000,
        showConfirmButton: false,
      });
      // Solo abrir PDF si fue autorizado
      window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
    } else if (accion === 2) {
      Swal.fire({
        icon: "info",
        title: "Rechazado",
        text: "El registro fue rechazado",
        timer: 2000,
        showConfirmButton: false,
      });
    }
    // Refrescar tabla en ambos casos
    window.Folios.consulta();
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
