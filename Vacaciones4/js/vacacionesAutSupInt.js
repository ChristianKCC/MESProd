class Vacaciones {
    async consulta() {
        const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
        const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

        const respuestaRaw = await fetch(`php/index.php?tblVacacionesEncSupInt&fecha=${fechaFiltro}&estatus=${estatusFiltro}`);
        const respuesta = await respuestaRaw.json();

        let body = "";
        respuesta.forEach(folio => {
            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "En espera de pre-aprobación/rechazo por Super Intendente";

            let estClass = "badge bg-warning text-dark";
            let estTexto = "En espera de aprobación de gerente";

            if (folio.Vc_autSupIn == 1) {
                estadoClass = "badge bg-success";
                estadoTexto = "Pre-Aprobado por Super Intendente";
            } else if (folio.autorizado == 2) {
                estadoClass = "badge bg-danger";
                estadoTexto = "Rechazado por Super Intendente";
            }            

            if (folio.autorizado == 1) {
                estClass = "badge bg-success";
                estTexto = "Aprobado por gerente";
            } else if (folio.autorizado == 2) {
                estClass = "badge bg-danger";
                estTexto = "Rechazado por gerente";
            }

            let accionesHtml = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Revisar info. en PDF
                </button>
            `;        
            
            // Caso de autorizacion
            if (!folio.Vc_autSupIn || folio.Vc_autSupIn === 0) {
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

            body += `
                <tr>
                    <td>${folio.id}</td>
                    <td>${folio.noemp}</td>
                    <td>${folio.nombre}</td>
                    <td>${folio.departamento}</td>
                    <td>${folio.fecha}</td>
                    <td><span class="${estadoClass}">${estadoTexto}</span> <span class="${estClass}">${estTexto}</span></td>
                    <td>${accionesHtml}</td>
                </tr>
            `;
        });
        document.getElementById("tblVacaciones").innerHTML = body;
    }
}

window.Vacaciones = new Vacaciones();
window.Vacaciones.consulta();

window.autorizarVac = async function(id, accion) {
    const respuestaRaw = await fetch(`php/index.php?autorizaVacSupInt&id=${id}&autor=${accion}`);
    const respuesta = await respuestaRaw.json();

    if (respuesta === "Listo") {
        Swal.fire("Éxito", `La solicitud con folio ${id} fue procesada correctamente.`, "success")
            .then(() => window.Vacaciones.consulta());
    } else {
        Swal.fire("Error", "No se pudo procesar la solicitud", "error");
    }
};

window.verPDF = function(id) {
    if (!id) {
        Swal.fire('UPS!!!', 'No hay un folio válido', 'info');
        return false;
    }
    window.open("./pdf/GenPDF?folio=" + btoa(id));
};

