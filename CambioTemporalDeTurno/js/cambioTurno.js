import { Toolsjs } from "../../Tools/Tools.js";

class Tiempoextra {
    constructor() {
        this.Tools = new Toolsjs();
    }

    async getinfoemp(noemp) {
        const respuetaraw = await fetch("../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp);
        const respuesta = await respuetaraw.json();
        if (respuesta.length === 0) {
            document.getElementById("nombre_receptor").value = "";
            document.getElementById("Depto_m").value = "";
            // document.getElementById("rol").value = "";
            return;
        }
        document.getElementById("nombre_receptor").value = respuesta[0].nombre;
        document.getElementById("Depto_m").value = respuesta[0].departamento.trim();
        // document.getElementById("rol").value = respuesta[0].puesto;
    }

    establecerFechaHoy() {
        const hoy = new Date();
        const fechaLocal =
            hoy.getFullYear() + "-" +
            String(hoy.getMonth() + 1).padStart(2, "0") + "-" +
            String(hoy.getDate()).padStart(2, "0");
        document.getElementById("fecha_emision").value = fechaLocal;
    }

    limpiarFormulario() {
        document.getElementById("formCambioTurno").reset();
        this.establecerFechaHoy();
    }

    async cargarTabla() {
        const tbody = document.getElementById("tblCambiosTurno");
        tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted"><small>Cargando...</small></td></tr>`;

        try {
            const response  = await fetch("php/index.php?tblCambiosTurnoIndependientes");
            const registros = await response.json();

            if (!Array.isArray(registros) || registros.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted"><small>No hay registros aún.</small></td></tr>`;
                return;
            }

            let body = "";
            registros.forEach(r => {
                body += `<tr>
                    <td>${r.Ctt_id ?? ""}</td>
                    <td>${r.Ctt_fecha ?? ""}</td>
                    <td>${r.Ctt_a ?? ""}</td>
                    <td>${r.Ctt_depto ?? ""}</td>
                    <td>${r.Ctt_de ?? ""}</td>
                    <td>${r.Ctt_horario ?? ""}</td>
                    <td>${r.Ctt_aPartirDel ?? ""}</td>
                    <td>${r.Ctt_hastaEl ?? ""}</td>
                    <td>${r.Ctt_turnoPresentacion ?? ""}</td>
                    <td>
                        <button class="btn btn-sm btn-primary"
                                onclick="tiempoextra.verPDFCambio(${r.Ctt_id})">
                            <i class="fa-solid fa-file-pdf"></i> Ver PDF
                        </button>
                        <button class="btn btn-sm btn-danger"
                                onclick="tiempoextra.eliminarCambio(${r.Ctt_id})">
                            <i class="fa-solid fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>`;
            });

            tbody.innerHTML = body;

        } catch (err) {
            console.error("Error al cargar tabla:", err);
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger"><small>Error al cargar los registros.</small></td></tr>`;
        }
    }

    verPDFCambio(id) {
        window.open(`../Tiempoextra/pdf/cambio_turno.php?id=${btoa(id)}`, "_blank");
    }

    async eliminarCambio(id) {
        const confirmacion = await Swal.fire({
            title: "¿Estás seguro?",
            text:  "Se eliminará este Cambio Temporal de Turno.",
            icon:  "warning",
            showCancelButton:  true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText:  "Cancelar",
            reverseButtons:    true
        });

        if (!confirmacion.isConfirmed) return;

        const data = new FormData();
        data.append("id", id);

        try {
            const response = await fetch("php/index.php?eliminarCambioTurno", { method: "POST", body: data });
            const result   = await response.json();

            if (result.success) {
                Swal.fire("Eliminado", "El registro fue eliminado correctamente.", "success");
                this.cargarTabla();
            } else {
                Swal.fire("Error", "No se pudo eliminar el registro.", "error");
            }
        } catch (err) {
            console.error("Error al eliminar:", err);
            Swal.fire("Error", "Ocurrió un problema al conectar con el servidor.", "error");
        }
    }

    async guardarCambio() {
        const hora_presentacion = document.getElementById("hora_presentacion").value.trim();
        const horario_desde     = document.getElementById("horario_desde").value.trim();
        const horario_hasta     = document.getElementById("horario_hasta").value.trim();
        const hasta_tripulacion = document.getElementById("hasta_tripulacion").value.trim();
        const nombre_receptor   = document.getElementById("nombre_receptor").value.trim();
        const fecha_emision     = document.getElementById("fecha_emision").value.trim();

        if (!fecha_emision || !hora_presentacion || !horario_desde || !horario_hasta ||
            !hasta_tripulacion || !nombre_receptor) {
            Swal.fire("Error", "Debes completar todos los campos obligatorios.", "error");
            return;
        }

        const form = document.getElementById("formCambioTurno");
        const data = new FormData(form);
        data.append("folioTiempoExtra", "");

        try {
            const response = await fetch("php/index.php?guardarCambioTurno&folio=", { method: "POST", body: data });
            const result   = await response.json();

            if (result.success) {
                Swal.fire("Guardado", "El cambio temporal de turno fue registrado correctamente.", "success");
                this.limpiarFormulario();
                this.cargarTabla();
            } else {
                Swal.fire("Error", "No se pudo guardar el registro.", "error");
            }
        } catch (err) {
            console.error("Error al guardar:", err);
            Swal.fire("Error", "Ocurrió un problema al conectar con el servidor.", "error");
        }
    }
}

// ── Instancia global ──────────────────────────────────────────────────────────
const tiempoextra = new Tiempoextra();
window.tiempoextra = tiempoextra;

// ── Eventos ───────────────────────────────────────────────────────────────────

// IBM empleado: solo buscar si tiene al menos 3 dígitos para evitar
// llamadas con valor vacío que rompen CatalogoSeguridad.php
document.getElementById("ibm_empleado").addEventListener("keyup", function () {
    const val = this.value.trim();
    if (!val || val.length < 3) {
        document.getElementById("nombre_receptor").value = "";
        document.getElementById("Depto_m").value = "";
        // document.getElementById("rol").value = "";
        return;
    }
    tiempoextra.getinfoemp(val);
});

document.getElementById("btnGuardarCambio").addEventListener("click", function () {
    tiempoextra.guardarCambio();
});

document.getElementById("btnLimpiar").addEventListener("click", function () {
    tiempoextra.limpiarFormulario();
});

document.addEventListener("DOMContentLoaded", function () {
    tiempoextra.cargarTabla();
    tiempoextra.establecerFechaHoy();
});

// ── Tutorial driver.js ────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
// Turorial de driver js
 const driver = window.driver.js.driver;

    const steps = [
        {
            element: ".tittlecont",
            popover: {
                title: "Cambio Temporal de Turno",
                description: "Desde aquí puedes registrar cambios temporales de turno sin necesidad de asociarlos a un tiempo extra.",
                side: "bottom"
            }
        },
        {
            element: "#fecha_emision",
            popover: {
                title: "Fecha de emisión",
                description: "Fecha en que se emite este cambio. Se llena automáticamente con la fecha de hoy.",
                side: "top"
            }
        },
        {
            element: "#Depto_m",
            popover: {
                title: "Departamento",
                description: "Indica el departamento al que pertenece el empleado.",
                side: "top"
            }
        },
        {
            element: "#nombre_receptor",
            popover: {
                title: "Receptor",
                description: "Nombre completo del empleado que recibirá el cambio de turno.",
                side: "top"
            }
        },
        {
            element: "#de_area",
            popover: {
                title: "Supervisor",
                description: "Nombre del supervisor que autoriza el cambio. Se llena automáticamente.",
                side: "top"
            }
        },
        {
            element: "#horario_texto",
            popover: {
                title: "Horario",
                description: "Especifica el nuevo horario asignado (ej: Primer turno).",
                side: "top"
            }
        },
        {
            element: "#rol",
            popover: {
                title: "Rol",
                description: "Indica la tripulacion indicada.",
                side: "top"
            }
        },
        {
            element: "#fecha_inicio",
            popover: {
                title: "Fecha de inicio",
                description: "Día en que comienza el cambio de turno.",
                side: "top"
            }
        },
        {
            element: "#hasta_el",
            popover: {
                title: "Fecha de término",
                description: "Día en que finaliza el cambio de turno.",
                side: "top"
            }
        },
        {
            element: "#turno_presentacion",
            popover: {
                title: "Turno de presentación",
                description: "Indica el turno en el que debe presentarse el empleado.",
                side: "top"
            }
        },
        {
            element: "#hora_presentacion",
            popover: {
                title: "Hora de presentación",
                description: "Hora exacta en que debe presentarse.",
                side: "top"
            }
        },
        {
            element: "#horario_desde",
            popover: {
                title: "Horario desde",
                description: "Hora de inicio del nuevo horario.",
                side: "top"
            }
        },
        {
            element: "#horario_hasta",
            popover: {
                title: "Horario hasta",
                description: "Hora de fin del nuevo horario.",
                side: "top"
            }
        },
        {
            element: "#hasta_tripulacion",
            popover: {
                title: "Conductor",
                description: "Nombre de quien está a cargo de la tripulación.",
                side: "top"
            }
        },
        {
            element: "#descansos",
            popover: {
                title: "Descansos",
                description: "Especifica los descansos aplicables al nuevo turno si corresponde.",
                side: "top"
            }
        },
        {
            element: "#dias_adicionales",
            popover: {
                title: "Días adicionales",
                description: "Indica si hay días adicionales que aplicar.",
                side: "top"
            }
        },
        {
            element: "#horario_adicional",
            popover: {
                title: "Horario adicional",
                description: "Especifica horarios adicionales si aplica.",
                side: "top"
            }
        },
        {
            element: "#btnGuardarCambio",
            popover: {
                title: "Guardar",
                description: "Haz clic aquí para guardar el cambio temporal de turno.",
                side: "top",
                popoverClass: "popover-importante"
            }
        },
        {
            element: "#btnLimpiar",
            popover: {
                title: "Limpiar campos",
                description: "Limpia todos los campos del formulario para comenzar un nuevo registro.",
                side: "top"
            }
        },
        {
            element: "#tblCambiosTurno",
            popover: {
                title: "Registros creados",
                description: "Aquí aparecerán todos los cambios temporales de turno creados desde esta vista.",
                side: "top",
                popoverClass: "popover-importante"
            }
        },
        {
            element: "#btnAyuda",
            popover: {
                title: "Tutorial",
                description: "Presiona este botón cuando quieras repetir el tutorial.",
                side: "bottom"
            }
        }
    ];

    let driverObj = null;

    function launchDriver() {
        if (driverObj) driverObj.destroy();
        driverObj = driver({
            showProgress:      true,
            allowClose:        false,
            disableInteraction: true,
            progressText:      "Paso {{current}} de {{total}}",
            doneBtnText:       "Finalizar",
            nextBtnText:       "Siguiente",
            prevBtnText:       "Atrás",
            steps
        });
        driverObj.drive();
    }

    const tutorialKey = "tutorial_cambioTurnoIndependiente";
    if (!localStorage.getItem(tutorialKey)) {
        launchDriver();
        localStorage.setItem(tutorialKey, "true");
    }

    document.getElementById("btnAyuda")?.addEventListener("click", launchDriver);
});

document.getElementById("turno_presentacion").addEventListener("change", function () {
    const turnoSeleccionado = this.value;
    const horario_desde = document.getElementById("horario_desde");
    const horario_hasta = document.getElementById("horario_hasta");

    switch (turnoSeleccionado) {
        case "turno1":
            horario_desde.value = "07:00:00";
            horario_hasta.value = "15:00:00";
            break;
        case "turno2":
            horario_desde.value = "15:00:00";
            horario_hasta.value = "22:30:00";
            break;
        case "turno3":
            horario_desde.value = "22:30:00";
            horario_hasta.value = "07:00:00";
            break;
        case "mixto1":
            horario_desde.value = "07:00:00";
            horario_hasta.value = "17:00:00";
            break;
        case "mixto2":
            horario_desde.value = "08:30:00";
            horario_hasta.value = "18:30:00";
            break;
        case "mixto3":
            horario_desde.value = "07:00:00";
            horario_hasta.value = "16:30:00";
            break;
        case "mixto4":
            horario_desde.value = "07:00:00";
            horario_hasta.value = "17:00:00";
            break;
        case "turno2_12hrs":
            horario_desde.value = "10:30:00";
            horario_hasta.value = "22:30:00";
            break;
        case "turno3_12hrs":
            horario_desde.value = "19:00:00";
            horario_hasta.value = "07:00:00";
            break;
        default:
            horario_desde.value = "";
            horario_hasta.value = "";
            break;
    }
});
