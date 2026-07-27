class Folios {
    async consulta() {
        const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
        const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

        const respuestaRaw = await fetch(`php/index.php?tblencfolio&fecha=${fechaFiltro}&estatus=${estatusFiltro}`);
        const respuesta = await respuestaRaw.json();

        let body = "";
        respuesta.forEach(folio => {
            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "En espera por gerente";

            if (folio.autorizado == 1) {
                estadoClass = "badge bg-success";
                estadoTexto = "Aprobado por gerente";
            } else if (folio.autorizado == 2) {
                estadoClass = "badge bg-danger";
                estadoTexto = "Rechazado por gerente";
            }

            let accionesHtml = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Pre. PDF
                </button>
            `;
            
            // Acciones para superintendente
            let espera = `
                <span class="badge bg-info text-dark">
                    Solicitud aún no validada por SuperIntendente
                </span>
            `;

            

            if (folio.noempSupIntendente && folio.autorizaSupInt === null) {
                accionesHtml = `
                    ${espera}
                    ${accionesHtml}
                `;   
            } else {
                if (folio.autorizado == null || folio.autorizado === "") {
                    if (folio.pendientesValidar == 0) {
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
                        accionesHtml = `
                            <span class="badge bg-secondary">Folio aún no validado por el supervisor</span>
                            ${accionesHtml}
                        `;
                    }
                } else {
                    accionesHtml = `
                        <span class="${estadoClass}">${estadoTexto}</span>
                        ${accionesHtml}
                    `;
                }
            }

            body += `
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
        });
        document.getElementById("tblFolios").innerHTML = body;
    }
}

window.Folios = new Folios();
window.Folios.consulta();

// Función para autorizar/rechazar folio
window.autorizarFolio = async function(id, accion) {
    const verificacion = await fetch("./php/verificar_firma.php").then(r => r.json()).catch(() => null);

    if (!verificacion?.success) {
        Swal.fire({
            icon:             "warning",
            title:            "Firma no registrada",
            text:             "Debes registrar tu firma primero antes de autorizar el tiempo extra. Consulta a RI para el registro de tu firma digital.",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#f0ad4e"
        });
        return;
    }        

    const respuestaRaw = await fetch(`php/index.php?autorizafol&id=${id}&autor=${accion}`);
    const respuesta = await respuestaRaw.json();

    if (respuesta === "Listo") {
        Swal.fire("Éxito", `La solicitud con el folio ${id} fue procesada correctamente.`, "success")
            .then(() => window.Folios.consulta());
    } else {
        Swal.fire("Error", "No se pudo procesar el folio", "error");
    }
};

// Función para abrir el PDF de un folio
window.verPDF = function(id) {
    if (!id) {
        Swal.fire('UPS!!!', 'No hay un folio válido', 'info');
        return false;
    }
    window.open("./pdf/reporte.php?folio=" + btoa(id));
}
