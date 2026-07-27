// PASO DE DATOS SEGUN CONSULTA

// function solicitarVacaciones() {
//     const ibm = document.getElementById("ibmActual").value;
//     const nombre = document.getElementById("nombreActual").value;
//     const dias = document.getElementById("diasActual").value;
//     const empleado = document.getElementById("empleadoActual").value;
//     const fingreso = document.getElementById("fingresoActual").value;

//     const form = document.createElement("form");
//     form.method = "POST";
//     form.action = "solicitar.php";

//     const input = document.createElement("input");
//     input.type = "hidden";
//     input.name = "ibm";
//     input.value = ibm;
//     form.appendChild(input);

//     const input1 = document.createElement("input");
//     input1.type = "hidden";
//     input1.name = "nombre";
//     input1.value = nombre;
//     form.appendChild(input1);

//     const input2 = document.createElement("input");
//     input2.type = "hidden";
//     input2.name = "dias";
//     input2.value = dias;
//     form.appendChild(input2);

//     const input3 = document.createElement("input");
//     input3.type = "hidden";
//     input3.name = "empleado";
//     input3.value = empleado;
//     form.appendChild(input3);

//     const input4 = document.createElement("input");
//     input4.type = "hidden";
//     input4.name = "fingreso";
//     input4.value = fingreso;
//     form.appendChild(input4);

//     document.body.appendChild(form);
//     form.submit();
// }

// Función para enviar solicitud
function enviarSolicitudVacaciones(tipo) {
    const ibm = document.getElementById("ibmActual").value;
    const nombre = document.getElementById("nombreActual").value;
    const dias = document.getElementById("diasActual").value;
    const empleado = document.getElementById("empleadoActual").value;
    const fingreso = document.getElementById("fingresoActual").value;

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "solicitar.php";

    // Campos del form
    const campos = {
        ibm,
        nombre,
        dias,
        empleado,
        fingreso,
        tipo
    };

    // Bifurcacion de datos para agregar al form
    for (const [name, value] of Object.entries(campos)) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

// Funcion que muestra el boton segun la cantidad de dias disponibles
function mostrarBotonSegunDias(diasDisponibles) {
    const btn = document.getElementById("btnSolicitarVacaciones");
    const img = document.getElementById("btnAdelantarVacaciones");
    const labelP = document.getElementById("labelP");

    if (!btn || !img || !labelP  ) {
        console.warn("Elementos no encontrados en el DOM");
        return;
    }

    if (parseInt(diasDisponibles) > 0) {
        btn.style.display = "inline-block";
        img.style.display = "none";
        labelP.style.display = "none"
    } else {
        btn.style.display = "none";
        img.style.display = "inline-block";
        labelP.style.display = "block"
    }
}

function calcularAniversario(fechaIngreso) {
    if (!fechaIngreso) return "";

    // Normalizar separadores
    const partes = fechaIngreso.split(/[-/]/);
    if (partes.length !== 3) return "";

    // Asumimos formato YYYY-MM-DD o similar
    let anio, mes, dia;
    if (fechaIngreso.includes("-")) {
        [anio, mes, dia] = partes;
    } else {
        [mes, dia, anio] = partes;
    }

    const proximo = new Date();
    proximo.setMonth(parseInt(mes) - 1);
    proximo.setDate(parseInt(dia));
    // Año actual
    proximo.setFullYear(new Date().getFullYear());

    return proximo.toLocaleDateString("es-MX");
}

// Funcion de busqueda de carga de solicitudes
function cargarSolicitudes(ibm) {
    fetch(`solicitudesVacaciones.php?ibm=${encodeURIComponent(ibm)}`)
        .then(res => res.text())
        .then(html => {
            const contenedor = document.getElementById("tablaSolicitudes");
            if (html.trim() !== "") {
                contenedor.innerHTML = html;
                contenedor.style.display = "block";
            } else {
                contenedor.innerHTML = "";
                contenedor.style.display = "none";
            }
        });
}

// Busqueda de empleados por el supervisor
const btnConsultarEmpleado = document.getElementById("consultarEmpleado");
if (btnConsultarEmpleado) {
    // BOTON DE SOLICITAR VACACIONES
    // document.getElementById("btnSolicitarVacaciones").addEventListener("click", solicitarVacaciones);
    // document.getElementById("btnAdelantarVacaciones").addEventListener("click", solicitarVacaciones);
    // Asignacion de eventos a los botones para datos en caso de tener o no vacaciones
    const btnSolicitar = document.getElementById("btnSolicitarVacaciones");
    if (btnSolicitar) {
        btnSolicitar.addEventListener("click", function() {
            enviarSolicitudVacaciones("Normal");
        });
    }

    const btnSolicitarSup = document.getElementById("btnAdelantarVacaciones");
    if (btnSolicitarSup) {
        btnSolicitarSup.addEventListener("click", function() {
            enviarSolicitudVacaciones("Adelanto");
        });
    }

    btnConsultarEmpleado.addEventListener("click", function() {
        const ibm = document.getElementById("ibmFiltro").value.trim();
        const nombre = document.getElementById("nombreFiltro").value.trim();

        if (ibm === "" && nombre === "") {
            Swal.fire({
                icon: 'error',
                title: 'Campos vacios',
                text: 'Debes escribir al menos IBM o Nombre para buscar.',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        fetch(`../Vacaciones/php/buscarEmpleado.php?ibm=${encodeURIComponent(ibm)}&nombre=${encodeURIComponent(nombre)}`)
            .then(res => res.json())
            .then(data => {
                if (!data || Object.keys(data).length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso denegado',
                        text: 'No se encontró el empleado o no tienes permiso para verlo.',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                mostrarBotonSegunDias(data["VAC_DISPONIBLES"]);
                const aniversario = calcularAniversario(data["F_INGRESO"]);
                const pAniv = document.getElementById("proximoAniversario");
                if (aniversario && pAniv) {
                    pAniv.style.display = "block";
                    pAniv.querySelector("code").innerText = aniversario;
                }

                document.querySelector(".dato-valor.ibm code").innerText = data["IBM"];
                document.querySelector(".dato-valor.nombre code").innerText = data["NOMBRE"];
                document.querySelector(".dato-valor.fingreso code").innerText = data["F_INGRESO"];
                document.querySelector(".dato-valor.antiguedad code").innerText = data["ANTIGUEDAD"];
                document.querySelector(".dias-badge").innerText = data["VAC_DISPONIBLES"];
                //document.querySelector(".aniversario code").innerText = data["ANIVERSARIO"];     
                document.getElementById("ibmActual").value = data["IBM"];
                document.getElementById("nombreActual").value = data["NOMBRE"];
                document.getElementById("diasActual").value = data["VAC_DISPONIBLES"];
                document.getElementById("empleadoActual").value = data["TIPO"];
                document.getElementById("fingresoActual").value = data["F. INGRESO"];

            });
    });
}

// Busqueda de datos propios del supervisor
const btnVerInformacion = document.getElementById("verInformacion");
if (btnVerInformacion) {
    // BOTON DE SOLICITAR VACACIONES
    // document.getElementById("btnSolicitarVacaciones").addEventListener("click", solicitarVacaciones);
    // document.getElementById("btnAdelantarVacaciones").addEventListener("click", solicitarVacaciones);
    // Asignacion de eventos a los botones para datos en caso de tener o no vacaciones
    const btnSolicitar = document.getElementById("btnSolicitarVacaciones");
    if (btnSolicitar) {
        btnSolicitar.addEventListener("click", function() {
            enviarSolicitudVacaciones("Normal");
        });
    }

    const btnSolicitarSup = document.getElementById("btnAdelantarVacaciones");
    if (btnSolicitarSup) {
        btnSolicitarSup.addEventListener("click", function() {
            enviarSolicitudVacaciones("Adelanto");
        });
    }

    btnVerInformacion.addEventListener("click", function() {
        fetch(`../Vacaciones/php/buscarEmpleado.php?modo=propio`)
            .then(res => res.json())
            .then(data => {
                if (!data || Object.keys(data).length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin datos',
                        text: 'No se encontraron tus datos.',
                        confirmButtonText: 'Ok'
                    });
                    return;
                }
                mostrarBotonSegunDias(data["VAC_DISPONIBLES"]);
                const aniversario = calcularAniversario(data["F_INGRESO"]);
                const pAniv = document.getElementById("proximoAniversario");
                if (aniversario && pAniv) {
                    pAniv.style.display = "block";
                    pAniv.querySelector("code").innerText = aniversario;
                }

                document.querySelector(".dato-valor.ibm code").innerText = data["IBM"];
                document.querySelector(".dato-valor.nombre code").innerText = data["NOMBRE"];
                document.querySelector(".dato-valor.fingreso code").innerText = data["F_INGRESO"];
                document.querySelector(".dato-valor.antiguedad code").innerText = data["ANTIGUEDAD"];
                document.querySelector(".dias-badge").innerText = data["VAC_DISPONIBLES"];
                //document.querySelector(".aniversario code").innerText = data["ANIVERSARIO"];
                document.getElementById("ibmActual").value = data["IBM"];
                document.getElementById("nombreActual").value = data["NOMBRE"];
                document.getElementById("diasActual").value = data["VAC_DISPONIBLES"];
                document.getElementById("empleadoActual").value = data["TIPO"];
                document.getElementById("fingresoActual").value = data["F. INGRESO"];
            });
    });
}

// Eventos de Driver JS
document.addEventListener("DOMContentLoaded", () => {
    const driver = window.driver.js.driver;
    const tipoUsuario = document.body.dataset.tipo;   // 'EMPL' o 'SIND'
    const rolUsuario  = document.body.dataset.rol;    // 'SUPERVISOR' o 'NORMAL'

    let steps = [];

    if (tipoUsuario === "EMPL" && rolUsuario === "SUPERVISOR") {
        // Tour para supervisor
        steps = [
            {
                element: ".header-vacaciones",
                popover: {
                    title: "Bienvenido",
                    description: "Aquí verás tu nombre y mensaje de bienvenida.",
                    side: "bottom"
                }
            },
            {
                element: ".busquedaIBM",
                popover: {
                    title: "Buscar empleados",
                    description: "Usa este campo para buscar a un empleado tipo sindicalizado por su IBM.",
                    side: "bottom"
                }
            },
            {
                element: ".busquedaNOMBRE",
                popover: {
                    title: "Buscar empleados",
                    description: "Usa este campo para buscar a un empleado tipo sindicalizado por su NOMBRE.",
                    side: "bottom"
                }
            },
            {
                element: ".botonBuscarEspecifica",
                popover: {
                    title: "Consulta información",
                    description: "Una vez escribas el nombre o el ibm del empleado presiona este botón para realizar la busqueda, si no esta a tu cargo no tendras permiso para su consulta.",
                    side: "bottom"
                }
            },
            {
                element: ".botonBuscarPropia",
                popover: {
                    title: "Consulta información",
                    description: "Usa este botón para ver tu propia información como supervisor.",
                    side: "bottom"
                }
            }
            ,
            {
                element: ".datainfosup",
                popover: {
                    title: "Tus datos",
                    description: "Una vez que hagas la consulta, observaras en esta ventana un breve resumen sobre tu información como IBM, Nombre, Fecha de ingreso y Antiguedad.",
                    side: "bottom"
                }
            },
            {
                element: ".dataVacacionesDiassup",
                popover: {
                    title: "Tus días disponibles",
                    description: "Una vez que hagas la consulta, aquí se indicara cuántos días de vacaciones te quedan segun tu historial y aniversarios.",
                    side: "left"
                }
            },            
            {
                element: ".infoPersonalsup",
                popover: {
                    title: "Información adicional",
                    description: "Aquí encontrarás información como tu próximo aniversario y un boton para solicitar vacaciones o adelantarlas en caso de que no tengas días disponibles",
                    side: "bottom"
                }
            },            
            {
                element: ".ayudaSupervisor",
                popover: {
                    title: "Volver a ver el tutorial",
                    description: "Si necesitas repasar cómo usar esta pantalla, presiona este botón para repetir el tutorial.",
                    side: "bottom"
                }
            }
        ];
    } else if (tipoUsuario === "EMPL" && rolUsuario === "NORMAL") {
        // Tour para empleado normal
        steps = [
            {
                element: ".header-vacaciones",
                popover: {
                    title: "Bienvenido",
                    description: "Aquí verás tu nombre y mensaje de bienvenida.",
                    side: "right"
                }
            },
            {
                element: ".card-datainfo",
                popover: {
                    title: "Tus datos",
                    description: "En esta ventana observaras un breve resumen sobre tu información como IBM, Nombre, Fecha de ingreso y Antiguedad.",
                    side: "left"
                }
            },
            {
                element: ".card-dataVacacionesDias",
                popover: {
                    title: "Tus días disponibles",
                    description: "Este número indica cuántos días de vacaciones te quedan segun tu historial y aniversarios.",
                    side: "left"
                }
            },            
            {
                element: ".card-infoPersonal",
                popover: {
                    title: "Información adicional",
                    description: "Aquí encontrarás datos como el estado de tus ultimas tres solicitudes y tu próximo aniversario",
                    side: "top"
                }
            },
            {
                element: "form[action='solicitarJE.php'] button",
                popover: {
                    title: "Solicitar vacaciones",
                    description: "Haz clic aquí para iniciar tu solicitud (En el caso de que no tengas días disponibles podras pedir un adelanto de días de vacaciones).",
                    side: "bottom"
                }
            },            
            {
                element: ".ayudaEmpleado",
                popover: {
                    title: "Volver a ver el tutorial",
                    description: "Si necesitas repasar cómo usar esta pantalla, presiona este botón para repetir el tutorial.",
                    side: "bottom"
                }
            }
        ]
    } else if (tipoUsuario === "SIND") {
        // Tour para sindicalizado
        steps = [
            {
                element: ".infoSind",
                popover: {
                    title: "Sindicalizado",
                    description: "Consulta tu informacíon con tu supervisor.",
                    side: "bottom"
                }
            }
        ];
    }

     if (steps.length > 0) {
        const driverObj = driver({
            showProgress: true,
            allowClose: false,
            progressText: "Paso {{current}} de {{total}}",
            doneBtnText: "Finalizar",
            nextBtnText: "Siguiente",
            prevBtnText: "Atrás",
            steps
        });

        // Verificar si ya se mostró el tutorial
        const tutorialKey = `tutorial_${tipoUsuario}_${rolUsuario}`;
        const tutorialYaVisto = localStorage.getItem(tutorialKey);

        if (!tutorialYaVisto) {
            // Primera vez: mostrar tutorial y marcarlo como visto
            driverObj.drive();
            localStorage.setItem(tutorialKey, "true");
        }

        // Botón de ayuda para relanzar el tutorial
        const btnAyuda = document.getElementById("btnAyuda");
        if (btnAyuda) {
            btnAyuda.addEventListener("click", () => {
                driverObj.drive();
            });
        }
    }
});


const btnVerInformacionPDF = document.getElementById("generarReporte");
if (btnVerInformacionPDF) {
    document.getElementById("generarReporte").addEventListener("click", async () => {    
        const modal = new bootstrap.Modal(document.getElementById("modalSolicitudesVacaciones"));
        modal.show();
        const respuestaRaw = await fetch(`php/index.php?tblVacacionesEncSupervisor`);
        const respuesta = await respuestaRaw.json();

        let body = "";
        respuesta.forEach(folio => {
            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "En espera de aprobación/rechazo";

            if (folio.autorizado == 1 && folio.revisado == 1 && folio.firmaRI == 1) {
                estadoClass = "badge bg-success";
                estadoTexto = "Aprobado";
            } else if (folio.autorizado == 2) {
                estadoClass = "badge bg-danger";
                estadoTexto = "Rechazado";
            }

            let accionesHtml = `
                <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.id})">
                    <i class="fa-solid fa-file-pdf"></i> Revisar info. en PDF
                </button>
            `;

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

        document.querySelector("#tablaSolicitudesVacaciones tbody").innerHTML = body;
    });
}

window.verPDF = function(id) {
    if (!id) {
        Swal.fire('UPS!!!', 'No hay un folio válido', 'info');
        return false;
    }
    window.open("./pdf/GenPDF?folio=" + btoa(id));
};

