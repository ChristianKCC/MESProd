class Folios {
    async consulta() {
        const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
        const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

        const respuestaRaw = await fetch(`php/index.php?tblencfolioSupInt&fecha=${fechaFiltro}&estatus=${estatusFiltro}`);
        const respuesta = await respuestaRaw.json();

        let body = "";
        respuesta.forEach(folio => {
            let estClass = "badge bg-warning text-dark";
            let estTexto = "En espera de pre-aprobación de Super Intendente";

            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "En espera de aprobación de gerente";

            if (folio.estadoTer == 1) {
                estadoClass = "badge bg-success";
                estadoTexto = "Aprobado por gerente";
            } else if (folio.estadoTer == 2) {
                estadoClass = "badge bg-danger";
                estadoTexto = "Rechazado por gerente";
            }

            if (folio.autorizaSupInt == 1) {
                estClass = "badge bg-success";
                estTexto = "Pre-Aprobado por Super Intendente";
            } else if (folio.autorizaSupInt == 2) {
                estClass = "badge bg-danger";
                estTexto = "Rechazado por Super Intendente";
            }            

            let accionesHtml = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Consultar PDF
                </button>
            `;

            if (folio.estadoTer == null || folio.estadoTer === "") {
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
                    <span class="${estadoClass}">${estadoTexto}</span>                    
                    ${accionesHtml}
                `;
            }

            body += `
                <tr>
                    <td>${folio.id}</td>
                    <td>${folio.NoempSupervisor}</td>
                    <td>${folio.NombreSupervisor ?? ""}</td>
                    <td>${folio.fechai}</td>
                    <td>${folio.fechaf}</td>
                    <td><span class="${estadoClass}">${estadoTexto}</span> <span class="${estClass}">${estTexto}</span></td>
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
/*
window.autorizarFolio = async function(id, accion) {
    const respuestaRaw = await fetch(`php/index.php?autorizafol&id=${id}&autor=${accion}`);
    const respuesta = await respuestaRaw.json();

    if (respuesta === "Listo") {
        Swal.fire("Éxito", `La solicitud con el folio ${id} fue procesada correctamente.`, "success")
            .then(() => window.Folios.consulta());
    } else {
        Swal.fire("Error", "No se pudo procesar el folio", "error");
    }
};
*/

// Funcion para autorizar el tiempo extra con parametros como el id y quien lo autoriza
window.autorizarFolio = async function(id, accion){
    // Verificación de firma solo si es autorización
    if (accion === 1) {
        const verificacion = await fetch('../../KCMes/Tiempoextra/php/verificar_firma.php')
            .then(res => res.json())
            .catch(() => null);

        if(!verificacion || !verificacion.success){
            Swal.fire({
                icon: 'warning',
                title: 'Firma no registrada',
                text: 'Debes registrar tu firma primero antes de autorizar el tiempo extra.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#f0ad4e'
            });
            return;
        }
    }

    const respuestaraw = await fetch(`./php/index.php?autorizafolSupInt&id=${id}&autor=${accion}`);
    const respuesta = await respuestaraw.json();

    if (respuesta === "Listo") {
        if (accion === 1) {
            Swal.fire({
                icon: 'success',
                title: 'Autorizado',
                text: 'El registro fue autorizado con éxito',
                timer: 2000,
                showConfirmButton: false
            });            
        } else if (accion === 2) {
            Swal.fire({
                icon: 'info',
                title: 'Rechazado',
                text: 'El registro fue rechazado',
                timer: 2000,
                showConfirmButton: false
            });
        }
        // Refrescar tabla en ambos casos
        window.Folios.consulta();
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
