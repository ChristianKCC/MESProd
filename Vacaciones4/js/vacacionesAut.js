class Vacaciones {
    async consulta() {
        const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
        const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

        const respuestaRaw = await fetch(`php/index.php?tblVacacionesEnc&fecha=${fechaFiltro}&estatus=${estatusFiltro}`);
        const respuesta = await respuestaRaw.json();

        let body = "";
        respuesta.forEach(folio => {
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

            // Acciones para superintendente
            let espera = `
                <span class="badge bg-info text-dark">
                    Solicitud aún no validada por SuperIntendente
                </span>
            `;

            // Caso de que el superintendente exista y que su autorizacion este en null
            if (folio.Vc_noempSupIntendente && folio.Vc_autSupIn === null) {
                accionesHtml = `
                    ${espera} 
                `;   
            } else {
                // En caso de que el superintendente exista y 
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
            }     

            

            body += `
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
        });
        document.getElementById("tblVacaciones").innerHTML = body;
    }
}

window.Vacaciones = new Vacaciones();
window.Vacaciones.consulta();

window.autorizarVac = async function(id, accion) {
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
    
    const respuestaRaw = await fetch(`php/index.php?autorizaVac&id=${id}&autor=${accion}`);
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

