class Vacaciones {
  async consulta() {
    const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
    const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

    const ibmFiltro = document.getElementById("filtroIbm")?.value || "";
    const respuestaRaw = await fetch(
      `php/index.php?tblVacacionesEncSupInt&fecha=${fechaFiltro}&ibm=${ibmFiltro}`,
    );

    // const respuestaRaw = await fetch(
    //   `php/index.php?tblVacacionesEncSupInt&fecha=${fechaFiltro}&estatus=${estatusFiltro}`,
    // );
    const respuesta = await respuestaRaw.json();

    let pendientes = "";
    let procesadas = "";
    let rechazadas = "";
    let countPendientes = 0;
    let countProcesadas = 0;
    let countRechazadas = 0;

    // respuesta.forEach((folio) => {
    //   let estadoClass = "badge bg-warning text-dark";
    //   let estadoTexto =
    //     "En espera de pre-aprobación/rechazo por Super Intendente";

    //   if (folio.Vc_autSupIn == 1) {
    //     estadoClass = "badge bg-success";
    //     estadoTexto = "Pre-Aprobado por Super Intendente";
    //   } else if (folio.Vc_autSupIn == 2) {
    //     estadoClass = "badge bg-danger";
    //     estadoTexto = "Pre-Rechazado por Super Intendente";
    //   }

    //   let accionesHtml = `
    //             <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
    //                 <i class="fa-solid fa-file-pdf"></i> Revisar info. en PDF
    //             </button>
    //         `;

    //   if (!folio.Vc_autSupIn || folio.Vc_autSupIn === 0) {
    //     accionesHtml = `
    //                 <button class="btn btn-success btn-sm" onclick="autorizarVac(${folio.id},1)">
    //                     <i class="fa-solid fa-circle-check"></i> Autorizar
    //                 </button>
    //                 <button class="btn btn-danger btn-sm" onclick="autorizarVac(${folio.id},2)">
    //                     <i class="fa-solid fa-circle-xmark"></i> Rechazar
    //                 </button>
    //                 ${accionesHtml}
    //             `;
    //   }

    //   let row = `
    //             <tr>
    //                 <td>${folio.id}</td>
    //                 <td>${folio.noemp}</td>
    //                 <td>${folio.nombre}</td>
    //                 <td>${folio.departamento}</td>
    //                 <td>${folio.fecha}</td>
    //                 <td><span class="${estadoClass}">${estadoTexto}</span></td>
    //                 <td>${accionesHtml}</td>
    //             </tr>
    //         `;

    //   const supIntStatus = Number(folio.Vc_autSupIn ?? 0);

    //   if (!folio.Vc_autSupIn || folio.Vc_autSupIn === 0) {
    //     pendientes += row;
    //     countPendientes++;
    //   } else if (folio.Vc_autSupIn == 1) {
    //     procesadas += row;
    //     countProcesadas++;
    //   } else if (folio.Vc_autSupIn == 2 || folio.autorizado == 2) {
    //     rechazadas += row;
    //     countRechazadas++;
    //   }
    // });

    respuesta.forEach((folio) => {
      //   const supIntStatus = Number(folio.Vc_autSupIn ?? 0);
      //   const gerenteStatus = Number(folio.autorizado ?? 0);

      const supIntStatus =
        folio.Vc_autSupIn == null ? 0 : Number(folio.Vc_autSupIn);
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
      <i class="fa-solid fa-file-pdf"></i> Revisar info. en PDF
    </button>
  `;

      if (supIntStatus === 0 && gerenteStatus !== 2) {
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
    `php/index.php?autorizaVacSupInt&id=${id}&autor=${accion}`,
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
