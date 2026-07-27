class Vacaciones {
    async consulta() {
        const fechaFiltro = document.getElementById("filtroFecha")?.value || "";
        const estatusFiltro = document.getElementById("filtroEstatus")?.value || "";

        const respuestaRaw = await fetch(`php/index.php?tblVacacionesEncSupInt&fecha=${fechaFiltro}&estatus=${estatusFiltro}`);
        const respuesta = await respuestaRaw.json();

        let pendientes = "";
        let procesadas = "";
        let countPendientes = 0;
        let countProcesadas = 0;

        respuesta.forEach(folio => {
            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "En espera de pre-aprobación/rechazo por Super Intendente";

            if (folio.Vc_autSupIn == 1) {
                estadoClass = "badge bg-success";
                estadoTexto = "Pre-Aprobado por Super Intendente";
            } else if (folio.Vc_autSupIn == 2) {
                estadoClass = "badge bg-danger";
                estadoTexto = "Pre-Rechazado por Super Intendente";
            }

            let accionesHtml = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Revisar info. en PDF
                </button>
            `;

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

            if (!folio.Vc_autSupIn || folio.Vc_autSupIn === 0) {
                pendientes += row;
                countPendientes++;
            } else {
                procesadas += row;
                countProcesadas++;
            }
        });

        document.getElementById("tblPendientes").innerHTML = pendientes;
        document.getElementById("tblProcesadas").innerHTML = procesadas;
        document.getElementById("countPendientes").innerText = countPendientes;
        document.getElementById("countProcesadas").innerText = countProcesadas;
    }
}

window.Vacaciones = new Vacaciones();
window.Vacaciones.consulta();



window.autorizarVac = async function(id, accion) {

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

