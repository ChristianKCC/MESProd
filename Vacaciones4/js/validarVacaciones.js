class Vacaciones {
    async consulta() {
        const respuestaRaw = await fetch("php/index.php?tblVacacionesEnc");
        const respuesta = await respuestaRaw.json();

        let body = "";
        respuesta.forEach(folio => {
            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "En espera de aprobación/rechazo";

            let estadoClassF = "badge bg-warning text-dark";
            let estadoTextoF = "En espera de firma";

            // Estado de autorizado por gerente
            if (folio.autorizado == 1) {
                estadoClass = "badge bg-success";
                estadoTexto = "Aprobado";
            } else if (folio.autorizado == 2) {
                estadoClass = "badge bg-danger";
                estadoTexto = "Rechazado";
            }

            // Estado de firmado por RI
            if (folio.firmaRI == 1) {
                estadoClassF = "badge bg-success";
                estadoTextoF = "Firmado";
            } else if (folio.firmaRI == 0) {
                estadoClassF = "badge bg-warning text-dark";
                estadoTextoF = "En espera de firma";
            }

            let accionesHtml = "";

            // Boton del PDF
            let botonPDF = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Ver PDF
                </button>
            `;

            // Validaciones dependiendo del estado
            if (folio.autorizado == 1) {
                accionesHtml = `                    
                    <button class="btn btn-primary btn-sm" onclick="verInfo(${folio.id})">
                        <i class="fa-solid fa-arrows-rotate"></i> Comprobar/Corregir información
                    </button>
                    ${botonPDF}
                `;
            } else {
                // En cualquier otro estado, solo mostrar PDF
                accionesHtml = `
                    <span class="badge bg-info text-dark">
                        Vacaciones aún no autorizadas por el gerente
                    </span>
                `;

            }

            body += `
                <tr>
                    <td>${folio.id}</td>
                    <td>${folio.noemp}</td>
                    <td>${folio.nombre}</td>
                    <td>${folio.departamento}</td>
                    <td>${folio.fecha}</td>
                    <td>
                        <span class="${estadoClass}">${estadoTexto}</span>
                        <span class="${estadoClassF}">${estadoTextoF}</span>
                    </td>
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
    const respuestaRaw = await fetch(`php/index.php?autorizaVac&id=${id}&autor=${accion}`);
    const respuesta = await respuestaRaw.json();

    if (respuesta === "Listo") {
        Swal.fire("Éxito", `La solicitud con folio ${id} fue procesada.`, "success")
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

window.verInfo = function(id) {
    if (!id) {
        Swal.fire("UPS!!!", "No hay un folio seleccionado", "info");
        return false;
    }
    window.open("../Vacaciones/EditarInfo?folio=" + btoa(id));
}