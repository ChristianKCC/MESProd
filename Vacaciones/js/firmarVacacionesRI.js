class Vacaciones {
    async consulta() {
        const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
        const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

        const respuestaRaw = await fetch(`php/index.php?tblVacacionesRIEnc&fecha=${fechaFiltro}&estatus=${estatusFiltro}`);
        const respuesta = await respuestaRaw.json();

        let pendientes = "";
        let procesadas = "";
        let rechazadas = "";
        let countPendientes = 0;
        let countProcesadas = 0;
        let countRechazadas = 0;

        respuesta.forEach(folio => {
            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "En espera de gerente";

            let estadoClassV = "badge bg-warning text-dark";
            let estadoTextoV = "En espera de nominas";

            if (folio.autorizado == 1) {
                estadoClass = "badge bg-success";
                estadoTexto = "Autorizado por gerente";
            } else if (folio.autorizado == 2) {
                estadoClass = "badge bg-danger";
                estadoTexto = "Rechazado por gerente";
            }

            if (folio.revisado == 1) {
                estadoClassV = "badge bg-success";
                estadoTextoV = "Revisado por nóminas";
            }

            let accionesHtml = `
                
            `;
            
            let botonPDF = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Ver PDF
                </button>
            `;

            if (folio.autorizado == 1 && folio.revisado == 1 && (folio.firmaRI === null || folio.firmaRI == 0)) {
                accionesHtml = `                    
                    <button class="btn btn-primary btn-sm" onclick="firmarInfo(${folio.id})">
                        <i class="fa-solid fa-file-signature"></i> Firmar solicitud
                    </button>
                    ${botonPDF}
                `;
            } else if (folio.firmaRI == 1) {
                accionesHtml = `<span class="badge bg-success">Solicitud firmada por RI</span> ${botonPDF}`;
            } else if (folio.firmaRI == 2) {
                accionesHtml = `<span class="badge bg-danger">Solicitud rechazada</span> ${botonPDF}`;
            } else if (folio.autorizado == 2){
                accionesHtml = `<span class="badge bg-danger">Solicitud rechazada</span>`;
                estadoTextoV = "";
            }
                else {
                accionesHtml = `<span class="badge bg-info text-dark">Solicitud aún no lista para firma</span>`;
            }

            let row = `
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

            // if (folio.Vc_revisado == 1) {
            //     procesadas += row;
            //     countProcesadas++;
            // } else if (folio.autorizado == 2) {
            //     rechazadas += row;
            //     countRechazadas++;
            // } 
            // // if (folio.autorizado == 1) -> Quitar las que aun no han sido aprobadas por el gerente y mostrar las unicas que ya estan disponibles
            // else {
            //     pendientes += row;
            //     countPendientes++;
            // }


            if (folio.firmaRI == 1) {
                procesadas += row;
                countProcesadas++;
            }                        
            else if (folio.autorizado == 2){
                rechazadas += row;
                countRechazadas++;
            }
            else if (folio.revisado == 1 && (folio.firmaRI === null || folio.firmaRI == 0)) {
                pendientes += row;
                countPendientes++;
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
