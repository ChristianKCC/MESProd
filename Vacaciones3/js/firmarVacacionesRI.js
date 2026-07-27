class Vacaciones {
    async consulta() {
        const respuestaRaw = await fetch("php/index.php?tblVacacionesRIEnc");
        const respuesta = await respuestaRaw.json();

        let body = "";
        respuesta.forEach(folio => {
            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "En espera de autorización";

            let estadoClassV = "badge bg-warning text-dark";
            let estadoTextoV = "En espera de validación";

            if (folio.autorizado == 1) {
                estadoClass = "badge bg-success";
                estadoTexto = "Autorizado";
            } else if (folio.autorizado == 2) {
                estadoClass = "badge bg-danger";
                estadoTexto = "Rechazado";
            }

            if (folio.revisado == 1) {
                estadoClassV = "badge bg-success";
                estadoTextoV = "Revisado por nominas";
            } else if (folio.revisado == 0) {
                estadoClassV = "badge bg-warning text-dark";
                estadoTextoV = "En espera de revisión";
            }

            let accionesHtml = "";

            // Boton del PDF
            let botonPDF = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Ver PDF
                </button>
            `;

            // Validaciones dependiendo del estado
            if (folio.autorizado == 1 && folio.revisado == 1 && folio.firmaRI != 1) {
                // Autorizado y revisado, pero aún no firmado
                accionesHtml = `                    
                    <button class="btn btn-primary btn-sm" onclick="firmarInfo(${folio.id})">
                        <i class="fa-solid fa-file-signature"></i> Firmar solicitud
                    </button>
                    ${botonPDF}
                `;
            } else if (folio.firmaRI == 1) {
                // Ya firmado por RI
                accionesHtml = `
                    <span class="badge bg-success">
                        Solicitud firmada
                    </span>
                    ${botonPDF}
                `;
            } else {
                // En cualquier otro estado, mostrar aviso
                accionesHtml = `
                    <span class="badge bg-info text-dark">
                        Solicitud aún no lista para firma (falta autorización o revisión)
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
                        <span class="${estadoClassV}">${estadoTextoV}</span>
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

window.firmarInfo = async function(id) {
    if (!id) {
        Swal.fire("UPS!!!", "No hay un folio seleccionado", "info");
        return false;
    }

    try {
        const respuestaRaw = await fetch(`php/index.php?firmarVac&id=${id}`, {
            method: "POST"
        });
        const respuesta = await respuestaRaw.json();

        if (respuesta.success) {
            Swal.fire("Éxito", `Solicitud firmada y aceptada correctamente.`, "success")
                .then(() => window.Vacaciones.consulta());
        } else {
            Swal.fire("Error", "No se pudo firmar la solicitud", "error");
        }
    } catch (err) {
        Swal.fire("Error", "Hubo un problema en la conexión", "error");
    }
};
