class Vacaciones {
    async consulta() {
        const respuestaRaw = await fetch("php/index.php?tblVacacionesEnc");
        const respuesta = await respuestaRaw.json();

        let body = "";
        respuesta.forEach(folio => {
            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "En espera de aprobación/rechazo por gerente";

            let estadoClassF = "badge bg-warning text-dark";
            let estadoTextoF = "En espera de firma por RI";

            let estadoClassN = "badge bg-secondary text-white";
            let estadoTextoN = "En espera de validacion por nominas";

            // Estado de autorizado por gerente
            if (folio.autorizado == 1) {
                estadoClass = "badge bg-success";
                estadoTexto = "Aprobado por gerente";
            } else if (folio.autorizado == 2) {
                estadoClass = "badge bg-danger";
                estadoTexto = "Rechazado por gerente";
            }

            // Estado de validacion para area de nominas
            if (folio.Vc_revisado == 1){
                estadoClassN = "badge bg-light text-dark";
                estadoTextoN = "Validado por nominas";
            } else if (folio.Vc_revisado == 0){
                estadoClassN = "badge bg-secondary text-white";
                estadoTextoN = "Aun no validado por nominas";
            }

            // Estado de firmado por RI
            if (folio.firmaRI == 1) {
                estadoClassF = "badge bg-success";
                estadoTextoF = "Firmado por RI";
            } else if (folio.firmaRI == 0) {
                estadoClassF = "badge bg-warning text-dark";
                estadoTextoF = "En espera de firma por RI";
            }

            let accionesHtml = "";

            // Boton del PDF
            let botonPDF = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Ver PDF
                </button>
            `;

            let botonComprobar = `
                <button class="btn btn-success btn-sm" 
                        onclick="comprobarDias(${folio.id}, '${folio.nombre}', '${folio.departamento}', ${folio.noemp})">
                    <i class="fa-solid fa-calendar-check"></i> Verificar No Asistencia
                </button>
            `;

            // Validaciones dependiendo del estado
            if (folio.autorizado == 1) {
                accionesHtml = `                    
                    <button class="btn btn-primary btn-sm" onclick="verInfo(${folio.id})">
                        <i class="fa-solid fa-arrows-rotate"></i> Comprobar/Corregir información
                    </button>
                    ${botonPDF}
                    ${botonComprobar}
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
                        <span class="${estadoClassN}">${estadoTextoN}</span>
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



window.comprobarDias = async function(folio, nombre, departamento, noemp) {
    try {
        const respDias = await fetch(`php/index.php?diasSolicitados&folio=${folio}`);
        const dias = await respDias.json();

        document.getElementById("infoEmpleado").innerHTML = `
            <p><strong>Empleado:</strong> ${nombre}</p>
            <p><strong>Departamento:</strong> ${departamento}</p>
        `;

         function parseFechaLocal(fechaStr){
            const [anio,mes,dia] = fechaStr.split("-");
            return new Date(parseInt(anio), parseInt(mes) - 1, parseInt(dia));
        }

        let bodyRows = "";
        const now = new Date();
        const hoy = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        

        for (const d of dias) {
            // const fechaSolicitada = new Date(d.fecha);
            const fechaSolicitada = parseFechaLocal(d.fecha);

            // Caso: día aún no llega

            // Contemplar el dia de hoy como no valido aun hasta que finalice
            // if (fechaSolicitada >= hoy) {

            // Ver incluso el dia de hoy con registros
            if (fechaSolicitada > hoy) {
                bodyRows += `
                    <tr class="dia-futuro">
                        <td>${d.fecha}</td>
                        <td>—</td>
                        <td>—</td>
                        <td><i class="fa-solid fa-clock"></i> Día aún no llegado o finalizado, revisar cuando corresponda</td>
                    </tr>
                `;
                continue;
            }

            // Caso: día ya llegó, revisar registros
            const respHoras = await fetch(`php/index.php?datoshoraysalida&noemp=${noemp}&fechabien=${d.fecha}`);
            const horas = await respHoras.json();

            if (horas.length > 0) {
                const hIni = horas[0].fecha_h;
                const hFin = horas[horas.length - 1].fecha_h;
                bodyRows += `
                    <tr class="dia-trabajado">
                        <td>${d.fecha}</td>
                        <td>${hIni}</td>
                        <td>${hFin}</td>
                        <td><i class="fa-solid fa-circle-xmark"></i> Día de vacaciones trabajado</td>
                    </tr>
                `;
            } else {
                bodyRows += `
                    <tr class="dia-vacaciones">
                        <td>${d.fecha}</td>
                        <td>—</td>
                        <td>—</td>
                        <td><i class="fa-solid fa-circle-check"></i> Día de vacaciones tomado</td>
                    </tr>
                `;
            }
        }

        document.getElementById("diasBody").innerHTML = bodyRows;

        const modal = new bootstrap.Modal(document.getElementById("modalDias"));
        modal.show();
    } catch (err) {
        Swal.fire("Error", "No se pudo recuperar los días: " + err.message, "error");
    }
};

window.refrescarTablaVacaciones = function() {    
    window.Vacaciones.consulta();
};
