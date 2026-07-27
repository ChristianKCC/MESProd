import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();

// ─────────────────────────────────────────────────────────────────────────────
// CATÁLOGO CENTRAL DE TURNOS
// Fuente única de verdad. Cada turno define:
//   entrada      → ventana de tolerancia para el chekado de entrada (en minutos desde medianoche)
//   salida       → ventana de tolerancia para el chekado de salida
//   cruzaMedia   → true si la salida es al día siguiente
//   horasReglamentarias → duración contractual del turno
//   esSabado     → override para sábados (mixtos)
// ─────────────────────────────────────────────────────────────────────────────
const CATALOGO_TURNOS = {
    turno1: {
        nombre:              "Turno 1 (07:00 - 15:00)",
        entrada:             { ideal: 7 * 60,       tolerancia: 35 },
        salida:              { ideal: 15 * 60,      tolerancia: 60 },
        cruzaMedia:          false,
        horasReglamentarias: "08:00:00",
        sabado:              null   // no aplica
    },
    turno2: {
        nombre:              "Turno 2 (15:00 - 22:30)",
        entrada:             { ideal: 15 * 60,      tolerancia: 35 },
        salida:              { ideal: 22 * 60 + 30, tolerancia: 60 },
        cruzaMedia:          false,
        horasReglamentarias: "07:30:00",
        sabado:              null
    },
    turno2_13hrs: {
        nombre:              "Turno 2 13hrs (10:30 - 22:30)",
        entrada:             { ideal: 10 * 60 + 30, tolerancia: 20 },
        salida:              { ideal: 22 * 60 + 30, tolerancia: 60 },
        cruzaMedia:          false,
        horasReglamentarias: "04:30:00",  // bloque extra antes del turno 2 normal
        sabado:              null
    },
    turno3: {
        nombre:              "Turno 3 (22:30 - 07:00)",
        entrada:             { ideal: 22 * 60 + 30, tolerancia: 35 },
        salida:              { ideal: 7 * 60,        tolerancia: 35 },
        cruzaMedia:          true,
        horasReglamentarias: "08:30:00",
        sabado:              null
    },
    turno3_12hrs: {
        nombre:              "Turno 3 12hrs (19:00 - 07:00)",
        entrada:             { ideal: 19 * 60,       tolerancia: 20 },
        salida:              { ideal: 7 * 60,         tolerancia: 20 },
        cruzaMedia:          true,
        horasReglamentarias: "08:30:00",
        sabado:              null
    },
    mixto1: {
        nombre:              "Mixto 1 (07:30 - 17:00)",
        entrada:             { ideal: 7 * 60 + 30,  tolerancia: 35 },
        salida:              { ideal: 17 * 60,       tolerancia: 60 },
        cruzaMedia:          false,
        horasReglamentarias: "10:00:00",
        sabado: {
            nombre:              "Mixto 1 Sábado (07:30 - 12:30)",
            salida:              { ideal: 12 * 60 + 30, tolerancia: 60 },
            horasReglamentarias: "05:00:00"
        }
    },
    mixto2: {
        nombre:              "Mixto 2 (08:30 - 18:30)",
        entrada:             { ideal: 8 * 60 + 30,  tolerancia: 35 },
        salida:              { ideal: 18 * 60 + 30, tolerancia: 60 },
        cruzaMedia:          false,
        horasReglamentarias: "10:00:00",
        sabado: {
            nombre:              "Mixto 2 Sábado (08:30 - 13:30)",
            salida:              { ideal: 13 * 60 + 30, tolerancia: 60 },
            horasReglamentarias: "05:00:00"
        }
    },
    mixto3: {
        nombre:              "Mixto 3 (07:00 - 16:30)",
        entrada:             { ideal: 7 * 60,        tolerancia: 35 },
        salida:              { ideal: 16 * 60 + 30,  tolerancia: 60 },
        cruzaMedia:          false,
        horasReglamentarias: "09:30:00",
        sabado: {
            nombre:              "Mixto 3 Sábado (07:00 - 12:00)",
            salida:              { ideal: 12 * 60,       tolerancia: 60 },
            horasReglamentarias: "05:00:00"
        }
    },
    mixto4: {
        nombre:              "Mixto 4 (07:00 - 17:00)",
        entrada:             { ideal: 7 * 60,        tolerancia: 15 },  // estrecho para no solapar mixto3
        salida:              { ideal: 17 * 60,        tolerancia: 60 },
        cruzaMedia:          false,
        horasReglamentarias: "10:00:00",
        sabado: {
            nombre:              "Mixto 4 Sábado (07:00 - 12:00)",
            salida:              { ideal: 12 * 60,       tolerancia: 60 },
            horasReglamentarias: "05:00:00"
        }
    }
};

// ─────────────────────────────────────────────────────────────────────────────
// UTILIDADES
// ─────────────────────────────────────────────────────────────────────────────
function horaAMinutos(hora) {
    const [h, m] = hora.split(":").map(Number);
    return h * 60 + m;
}

function minutosAHora(minutos) {
    const h = Math.floor(Math.abs(minutos) / 60) % 24;
    const m = Math.abs(minutos) % 60;
    return h.toString().padStart(2, "0") + ":" + m.toString().padStart(2, "0");
}

function minutosAHoraConSegundos(minutos) {
    return minutosAHora(minutos) + ":00";
}

function dentroDeVentana(minActual, ideal, tolerancia) {
    return Math.abs(minActual - ideal) <= tolerancia;
}

// Agrupa registros del checador que están a menos de `umbral` minutos entre sí.
// Devuelve el primer registro de cada grupo (la marca más temprana de ese bloque).
function agruparRegistros(registros, umbral = 30) {
    if (!registros.length) return [];
    const sorted = [...registros].sort((a, b) =>
        horaAMinutos(a.hora_limpia) - horaAMinutos(b.hora_limpia)
    );
    const grupos = [[sorted[0]]];
    for (let i = 1; i < sorted.length; i++) {
        const diff = horaAMinutos(sorted[i].hora_limpia) - horaAMinutos(sorted[i - 1].hora_limpia);
        if (diff <= umbral) {
            grupos[grupos.length - 1].push(sorted[i]);
        } else {
            grupos.push([sorted[i]]);
        }
    }
    // De cada grupo tomamos el primero (entrada más temprana del bloque)
    // y el último (salida más tardía del bloque)
    return grupos.map(g => ({
        entrada: g[0],
        salida:  g[g.length - 1]
    }));
}

// Limpia registros del checador eliminando duplicados por minuto
function limpiarRegistros(registros) {
    const vistos = new Set();
    return registros
        .filter(r => r.fecha_h)
        .map(r => ({ ...r, hora_limpia: r.fecha_h.substring(0, 8) }))
        .filter(r => {
            const clave = r.hora_limpia.substring(0, 5); // HH:MM
            if (vistos.has(clave)) return false;
            vistos.add(clave);
            return true;
        })
        .sort((a, b) => horaAMinutos(a.hora_limpia) - horaAMinutos(b.hora_limpia));
}

// ─────────────────────────────────────────────────────────────────────────────
// NÚCLEO: resolverEntradaSalida
//
// Dado el turnoAsignado (key del catálogo), los registros del checador de
// ayer/hoy/mañana y si es sábado, devuelve { entrada, salida } o null.
//
// Estrategia:
//  1. Obtiene la definición del turno del catálogo.
//  2. Si es sábado y el turno tiene override de sábado, lo aplica.
//  3. Busca la entrada en los registros del día actual dentro de la ventana.
//  4. Busca la salida:
//     - Si cruzaMedia = false → en los registros del día actual.
//     - Si cruzaMedia = true  → en los registros del día SIGUIENTE.
//  5. Casos especiales de turnos de 12/13 hrs que "entran antes":
//     - turno3_12hrs: la entrada puede estar en el día actual (~19:00) y
//       la salida en el siguiente (~07:00). Si no hay entrada hoy en ventana,
//       revisa si hay salida hoy (~07:00) y entrada ayer (~19:00).
//     - turno2_13hrs: entrada hoy ~10:30, salida hoy ~22:30 (mismo día).
//  6. Si no se resuelve con el turno asignado, devuelve null para que el
//     llamador muestre el mensaje adecuado al usuario.
// ─────────────────────────────────────────────────────────────────────────────
function resolverEntradaSalida(turnoAsignado, regAnterior, regActual, regSiguiente, esSabado) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) return null;

    // Aplicar override de sábado si corresponde
    const salidaDef  = (esSabado && def.sabado) ? def.sabado.salida  : def.salida;
    const entradaDef = def.entrada;

    // ── Helpers locales ───────────────────────────────────────────────────────
    const buscarEnVentana = (registros, ideal, tolerancia) =>
        registros.find(r => dentroDeVentana(horaAMinutos(r.hora_limpia), ideal, tolerancia)) || null;

    const buscarMasCercano = (registros, ideal, tolerancia) => {
        let mejor = null, menorDiff = Infinity;
        for (const r of registros) {
            const diff = Math.abs(horaAMinutos(r.hora_limpia) - ideal);
            if (diff <= tolerancia && diff < menorDiff) {
                menorDiff = diff;
                mejor = r;
            }
        }
        return mejor;
    };

    // ── CASO: turno cruza medianoche (turno3, turno3_12hrs) ───────────────────
    if (def.cruzaMedia) {
        // Escenario A: inicio del turno hoy (entrada hoy, salida mañana)
        const entradaHoy = buscarMasCercano(regActual, entradaDef.ideal, entradaDef.tolerancia);
        if (entradaHoy) {
            const salidaMañana = buscarMasCercano(regSiguiente, salidaDef.ideal, salidaDef.tolerancia);
            if (salidaMañana) {
                return { entrada: entradaHoy, salida: salidaMañana, escenario: "inicio_turno" };
            }
            // Entró pero aún no hay salida (turno en curso o salida no registrada)
            return { entrada: entradaHoy, salida: null, escenario: "sin_salida" };
        }

        // Escenario B: el día actual tiene la SALIDA de un turno iniciado ayer
        // (el empleado ya venía trabajando desde ayer)
        const salidaHoy = buscarMasCercano(regActual, salidaDef.ideal, salidaDef.tolerancia);
        if (salidaHoy) {
            const entradaAyer = buscarMasCercano(regAnterior, entradaDef.ideal, entradaDef.tolerancia);
            if (entradaAyer) {
                return { entrada: entradaAyer, salida: salidaHoy, escenario: "fin_turno" };
            }
        }

        return null; // No se pudo resolver
    }

    // ── CASO: mismo día ───────────────────────────────────────────────────────
    const entrada = buscarMasCercano(regActual, entradaDef.ideal, entradaDef.tolerancia);
    const salida  = buscarMasCercano(regActual, salidaDef.ideal,  salidaDef.tolerancia);

    if (entrada && salida && horaAMinutos(entrada.hora_limpia) < horaAMinutos(salida.hora_limpia)) {
        return { entrada, salida, escenario: "mismo_dia" };
    }

    // Solo entrada (turno aún en curso o salida no registrada)
    if (entrada && !salida) {
        return { entrada, salida: null, escenario: "sin_salida" };
    }

    return null;
}

// ─────────────────────────────────────────────────────────────────────────────
// calcularTurnoYHoras  (firma pública preservada)
//
// Recibe las horas ya resueltas (entrada, salida como string HH:MM:SS o HH:MM)
// y el turnoAsignado para calcular horas totales, reglamentarias y extras.
// ─────────────────────────────────────────────────────────────────────────────
function calcularTurnoYHoras(horaEntrada, horaSalida, esSabado = false, turnoAsignado = null) {
    const entradaMin = horaAMinutos(horaEntrada);
    let   salidaMin  = horaAMinutos(horaSalida);

    // Ajuste por cruce de medianoche
    if (salidaMin < entradaMin) salidaMin += 1440;

    const def = turnoAsignado ? CATALOGO_TURNOS[turnoAsignado] : null;

    let nombreTurno, horaInicioTurno, horaFinTurno, horasReglamentarias, finTurnoMin;

    if (def) {
        const salidaDef = (esSabado && def.sabado) ? def.sabado.salida  : def.salida;
        const nomDef    = (esSabado && def.sabado) ? def.sabado.nombre  : def.nombre;
        const hrsDef    = (esSabado && def.sabado) ? def.sabado.horasReglamentarias : def.horasReglamentarias;

        nombreTurno         = nomDef;
        horaInicioTurno     = minutosAHora(def.entrada.ideal);
        horaFinTurno        = minutosAHora(salidaDef.ideal);
        horasReglamentarias = hrsDef;

        // finTurnoMin: punto desde donde empiezan las horas extra
        // Para turno2_13hrs el extra arranca desde las 15:00 (fin del bloque extra)
        if (turnoAsignado === "turno2_13hrs") {
            finTurnoMin = 15 * 60;
        } else if (def.cruzaMedia) {
            finTurnoMin = salidaDef.ideal + 1440; // referencia al día siguiente
        } else {
            finTurnoMin = salidaDef.ideal;
        }
    } else {
        // Fallback sin turno conocido (no debería ocurrir con la nueva arquitectura)
        nombreTurno         = "Desconocido";
        horaInicioTurno     = minutosAHora(entradaMin);
        horaFinTurno        = minutosAHora(salidaMin);
        horasReglamentarias = "00:00:00";
        finTurnoMin         = salidaMin;
    }

    const minutosTrabajados = salidaMin - entradaMin;
    const horasExtrasMin    = Math.max(0, salidaMin - finTurnoMin);

    return {
        turno:               nombreTurno,
        horaInicioTurno,
        horaFinTurno,
        horasExtras:         horasExtrasMin > 0 ? minutosAHoraConSegundos(horasExtrasMin) : "00:00:00",
        totalHoras:          minutosAHoraConSegundos(minutosTrabajados),
        horasReglamentarias,
        salidaMin,
        entradaMin,
        finTurnoMin
    };
}

// ─────────────────────────────────────────────────────────────────────────────
// validarTiempoExtra  (firma pública preservada)
// ─────────────────────────────────────────────────────────────────────────────
function validarTiempoExtra(horasExtra, totalHoras, horasReglamentarias, folioRegistro, horasalida, horasExtra2, HoraInicio, turnoDetectado) {
    function sumarHoras(horaInicio, horasExtra) {
        const [h1, m1] = horaInicio.split(":").map(Number);
        const [h2, m2] = horasExtra.split(":").map(Number);
        let totalMin = (h1 * 60 + m1 + h2 * 60 + m2) % (24 * 60);
        return minutosAHoraConSegundos(totalMin);
    }

    const nuevaHoraFin          = sumarHoras(HoraInicio, horasExtra2);
    const minutosExtra          = horaAMinutos(horasExtra);
    const minutosTotal          = horaAMinutos(totalHoras);
    const minutosReglamentarios = horaAMinutos(horasReglamentarias);
    const btnEliminarFila       = document.getElementById(`btnEliminar-${folioRegistro}`);

    // Helper para actualizar DOM tras fetch exitoso
    function actualizarFilaDOM(folioRegistro, registro) {
        const fila = document.querySelector(`#msg-${folioRegistro}`)?.closest("tr");
        if (!fila) return;

        const estatusCell  = fila.querySelector("td:nth-child(13)");
        const accionesCell = fila.querySelector("td:nth-child(14)");

        if (estatusCell) {
            estatusCell.innerHTML = registro?.validado == 1
                ? `<span class="badge bg-success">Validado</span>`
                : `<span class="badge bg-warning">No validado aun</span>`;
        }
        if (accionesCell) {
            accionesCell.innerHTML = registro?.validado == 1
                ? `<span class="badge bg-success">Ya validado</span>
                   <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`
                : `<button class="btn btn-sm btn-warning"
                           onclick="validarInfo('${registro.NoEmpleadoSol}','${registro.fechaSol}','${registro.folioRegistro}','${registro.HoraInicio}')"
                           id="btnValidar-${registro.folioRegistro}">
                       <i class="fa-solid fa-eye"></i> Validar T. extra
                   </button>
                   <button class="btn btn-sm btn-danger"
                           onclick="window.deletesub(${registro.folioRegistro})"
                           id="btnEliminar-${registro.folioRegistro}" hidden>
                       <i class="fas fa-times"></i> Eliminar
                   </button>
                   <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`;
        }
    }

    // Helper para hacer fetch de actualización y manejar respuesta
    async function fetchActualizar(endpoint, extraData, horaFinNueva, folioRegistro) {
        const data = new FormData();
        data.append("folioRegistro", folioRegistro);
        if (horaFinNueva) data.append("nuevaHoraFin", horaFinNueva);
        if (extraData) Object.entries(extraData).forEach(([k, v]) => data.append(k, v));

        try {
            const r        = await fetch(`php/index.php?${endpoint}`, { method: "POST", body: data });
            const respuesta = await r.json();

            if (respuesta.success) {
                const registro = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
                if (registro) {
                    if (horaFinNueva) registro.HoraFin = horaFinNueva;
                    registro.validado = 1;
                }
                actualizarFilaDOM(folioRegistro, registro);

                await Swal.fire(
                    "Actualización realizada",
                    `El registro quedó marcado como validado${horaFinNueva ? `. Hora Fin ajustada a ${horaFinNueva}` : ""}.`,
                    "success"
                );
                const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
                if (folioId) window.Reportes.evaluarFolio(folioId);
            } else {
                Swal.fire("Error en actualización", `Detalle: ${respuesta.error}`, "error");
            }
        } catch (err) {
            console.error("Error al actualizar:", err);
        }
    }

    // ── CASO: turno2_13hrs ────────────────────────────────────────────────────
    if (turnoDetectado === "turno2_13hrs") {
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        const horaFinExtra2_13 = "16:30:00";
        Swal.fire({
            title:  "Resultados de validación:",
            html:   `Se detectó un <b>Turno 2 de 13 hrs</b> válido<br><br>
                     Hora de Inicio de T. extra: <b>${HoraInicio}</b><br>
                     Hora de Entrada al turno: <b>10:30</b><br>
                     Hora de Fin de T. extra ajustada: <b>${horaFinExtra2_13}</b>`,
            icon:   "info"
        }).then(() => fetchActualizar("actualizarHoraFin", null, horaFinExtra2_13, folioRegistro));
        if (btnEliminarFila) btnEliminarFila.hidden = true;

    // ── CASO: turno3_12hrs ────────────────────────────────────────────────────
    } else if (turnoDetectado === "turno3_12hrs") {
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        Swal.fire({
            title:  "Resultados de validación:",
            html:   `Se detectó un <b>Turno 3ro de 12 horas</b> válido<br><br>
                     Hora de Inicio de T. extra: <b>${HoraInicio}</b><br>
                     Hora de Salida del turno: <b>${horasalida}</b>`,
            icon:   "info"
        }).then(() => fetchActualizar("actualizarEstadoValidado", null, null, folioRegistro));
        if (btnEliminarFila) btnEliminarFila.hidden = true;

    // ── CASO: mixto4 ──────────────────────────────────────────────────────────
    } else if (turnoDetectado === "mixto4") {
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        Swal.fire({
            title:  "Resultados de validación:",
            html:   `Se detectó un <b>Mixto 4 (07:00 - 17:00)</b> válido<br><br>
                     Hora de Inicio de T. extra: <b>${HoraInicio}</b><br>
                     Horas Extra trabajadas: <b>${horasExtra2}</b><br>
                     Hora de Fin de T. extra ajustada: <b>${nuevaHoraFin}</b>`,
            icon:   "info"
        }).then(() => fetchActualizar("actualizarHoraFin", null, nuevaHoraFin, folioRegistro));
        if (btnEliminarFila) btnEliminarFila.hidden = true;

    // ── CASO: turno normal con ≥55 min de tiempo extra ────────────────────────
    } else if (minutosTotal >= minutosReglamentarios && minutosExtra >= 55) {
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        Swal.fire({
            title:  "Resultados de validación:",
            html:   `Con base en las horas checadas, esta persona cumple los requisitos para el T. extra solicitado<br><br>
                     Hora de Inicio de T. extra: <b>${HoraInicio}</b><br>
                     Horas Extras trabajadas: <b>${horasExtra2}</b><br>
                     Turno identificado: <b>${turnoDetectado}</b><br>
                     Hora de Fin de T. extra ajustada: <b>${nuevaHoraFin}</b>`,
            icon:   "info"
        }).then(() => {
            if (horasalida !== nuevaHoraFin) {
                fetchActualizar("actualizarHoraFin", null, nuevaHoraFin, folioRegistro);
            }
        });
        if (btnEliminarFila) btnEliminarFila.hidden = true;

    // ── CASO: no cumple requisitos ────────────────────────────────────────────
    } else {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        Swal.fire({
            title:  "Resultados de validación:",
            html:   `No se cumplen los requisitos para solicitar un tiempo extra.<br><br>
            Turno identificado: <b>${turnoDetectado}</b><br/>         
            Tiempo extra trabajado: <b>${horasExtra2}</b>`,
            icon:   "info"
        });
        if (btnEliminarFila) btnEliminarFila.hidden = false;
    }

    const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (folioId) window.Reportes.evaluarFolio(folioId);
}

// ─────────────────────────────────────────────────────────────────────────────
// CLASE PRINCIPAL
// ─────────────────────────────────────────────────────────────────────────────
class Reportes {
    constructor() {
        this.data = [];
    }

    // ── getinfohoraentradaysalida (firma pública preservada) ──────────────────
    //
    // NUEVA ARQUITECTURA:
    //  1. Recibe turnoAsignado desde la solicitud (ya conocido, confiable).
    //  2. Descarga registros del checador para ayer/hoy/mañana.
    //  3. Llama a resolverEntradaSalida() que usa el catálogo para encontrar
    //     entrada y salida dentro de las ventanas de tolerancia del turno.
    //  4. Si no resuelve → mensaje al usuario con botón eliminar.
    //  5. Si resuelve → calcula horas y llama a validarTiempoExtra().
    // ─────────────────────────────────────────────────────────────────────────
    async getinfohoraentradaysalida(noemp, date, folioRegistro, HoraInicio, turnoAsignado) {
        // ── Fechas ────────────────────────────────────────────────────────────
        const fechaActual    = new Date(date + "T00:00:00");
        const fechaAnterior  = new Date(fechaActual); fechaAnterior.setDate(fechaAnterior.getDate() - 1);
        const fechaSiguiente = new Date(fechaActual); fechaSiguiente.setDate(fechaSiguiente.getDate() + 1);

        const fmt = f => {
            const y = f.getFullYear();
            const m = (f.getMonth() + 1).toString().padStart(2, "0");
            const d = f.getDate().toString().padStart(2, "0");
            return `${y}-${m}-${d}`;
        };

        const lblMensaje = document.getElementById("lblMensaje");

        const mostrarError = (msg) => {
            lblMensaje.hidden    = false;
            lblMensaje.className = "alert alert-warning mt-2";
            lblMensaje.style.display = "block";
            const btnFila = document.getElementById(`btnEliminar-${folioRegistro}`);
            if (btnFila) btnFila.hidden = false;
            Swal.fire({
                title: "Resultados de validación:",
                html:  `Se recomienda eliminar este registro de empleado<br><br><b>${msg}</b><br><br>
                        Turno solicitado: <b>${turnoAsignado}</b>`,
                icon:  "info"
            });
        };

        // ── Fetch checador ────────────────────────────────────────────────────
        let rawAnterior, rawActual, rawSiguiente;
        try {
            [rawAnterior, rawActual, rawSiguiente] = await Promise.all([
                fetch(`../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${fmt(fechaAnterior)}`).then(r => r.json()),
                fetch(`../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${date}`).then(r => r.json()),
                fetch(`../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${fmt(fechaSiguiente)}`).then(r => r.json())
            ]);
        } catch (e) {
            mostrarError("Error al conectar con el checador. Intenta de nuevo más tarde.");
            return;
        }

        if (!rawActual.length) {
            mostrarError("No hay registros de horarios checados para el empleado en el día especificado. Si crees que es un error comunícate con el departamento de Nóminas.");
            return;
        }

        // ── Limpiar y agrupar ─────────────────────────────────────────────────
        const regAnterior  = limpiarRegistros(rawAnterior);
        const regActual    = limpiarRegistros(rawActual);
        const regSiguiente = limpiarRegistros(rawSiguiente);

        const fechar  = new Date(date + "T00:00:00");
        const esSabado = fechar.getDay() === 6;

        // ── Validar que el turnoAsignado existe en el catálogo ────────────────
        if (!CATALOGO_TURNOS[turnoAsignado]) {
            mostrarError(`El turno asignado "${turnoAsignado}" no está reconocido en el sistema. Comunícate con el departamento de Nóminas.`);
            return;
        }

        // ── Resolver entrada/salida usando el turno como ancla ────────────────
        const resolucion = resolverEntradaSalida(turnoAsignado, regAnterior, regActual, regSiguiente, esSabado);

        if (!resolucion) {
            mostrarError("No se pudieron encontrar registros de entrada/salida coherentes con el turno asignado. Si lo deseas, consulta más tarde o comunícate con Nóminas.");
            return;
        }

        if (!resolucion.salida) {
            // Turno en curso o salida no registrada aún
            mostrarError("Solo se detectó la entrada del turno. Consulta más tarde para verificar si el registro de salida fue realizado. Si crees que es un error comunícate con Nóminas.");
            console.log(turnoAsignado);
            return;
        }

        const horaentrada = resolucion.entrada.hora_limpia;
        const horasalida  = resolucion.salida.hora_limpia;

        // ── Calcular horas ────────────────────────────────────────────────────
        const resultado = calcularTurnoYHoras(horaentrada, horasalida, esSabado, turnoAsignado);

        const comp = "00:05:00";
        if (resultado.totalHoras <= comp) {
            mostrarError("Solo se tiene un registro de entrada/salida para el empleado. Consulta más tarde para verificar si el registro fue completado.");
            return;
        }

        // Verificar que el nombre del turno tenga suficiente info (guard original)
        const turnonom = resultado.turno.split(/[\s,;()\-\.]+/);
        if (turnonom[3] === undefined) {
            mostrarError("No se pudo determinar el turno correctamente. Comunícate con Nóminas.");
            return;
        }

        // ── Calcular horas extra ──────────────────────────────────────────────
        const horaExtrass  = horaAMinutos(resultado.totalHoras);
        const horaReglamen = horaAMinutos(resultado.horasReglamentarias);
        let horaExtra2 = "00:00:00";

        if (horaExtrass >= horaReglamen) {
            horaExtra2 = minutosAHoraConSegundos(horaExtrass - horaReglamen);
        }

        // Mostrar Swal informativo del turno detectado para turnos especiales
        if (turnoAsignado === "turno3_12hrs") {
            await Swal.fire({
                title: "Turno detectado: Tercero 12 hrs",
                html:  `Se identificó turno <b>Tercero 12 hrs</b><br><br>
                        Entrada: <b>${resultado.horaInicioTurno}</b><br>
                        Salida esperada: <b>${resultado.horaFinTurno}</b><br>
                        Total horas trabajadas: <b>${resultado.totalHoras}</b><br>
                        Horas reglamentarias: <b>${resultado.horasReglamentarias}</b>`,
                icon:  "info"
            });
        } else if (turnoAsignado === "turno2_13hrs") {
            await Swal.fire({
                title: "Turno detectado: Segundo 13 hrs",
                html:  `Se identificó turno <b>Segundo 13 hrs</b><br><br>
                        Entrada: <b>${resultado.horaInicioTurno}</b> (10:30)<br>
                        Salida de turno normal: <b>${resultado.horaFinTurno}</b> (~22:30)<br>
                        Horas reglamentarias (bloque extra): <b>${resultado.horasReglamentarias}</b><br>
                        Hora fin T. extra ajustada: <b>16:30</b>`,
                icon:  "info"
            });
        } else if (turnoAsignado === "mixto4") {
            await Swal.fire({
                title: "Turno detectado: Mixto 4",
                html:  `Se identificó turno <b>Mixto 4 (07:00 - 17:00)</b><br><br>
                        Entrada: <b>${resultado.horaInicioTurno}</b><br>
                        Salida esperada: <b>${resultado.horaFinTurno}</b><br>
                        Total horas trabajadas: <b>${resultado.totalHoras}</b><br>
                        Horas reglamentarias: <b>${resultado.horasReglamentarias}</b>`,
                icon:  "info"
            });
        }

        validarTiempoExtra(
            horaExtra2,
            resultado.totalHoras,
            resultado.horasReglamentarias,
            folioRegistro,
            horasalida,
            horaExtra2,
            HoraInicio,
            turnoAsignado
        );

        const registro = this.data.find(e => e.folioRegistro == folioRegistro);
        if (registro) this.evaluarFolio(registro.id);
    }

    // ── consulta ──────────────────────────────────────────────────────────────
    async consulta() {
        const respuetaraw = await fetch("php/index.php?tblautorizatp");
        const respuesta   = await respuetaraw.json();

        if (Array.isArray(respuesta)) {
            this.data = respuesta;
            let body = "";
            const folios = new Set();

            respuesta.forEach(elemento => {
                folios.add(elemento.id);
                body += this.renderRow(elemento);
            });

            document.getElementById("tblenc").innerHTML = body;

            const accionesContainer = document.getElementById("accionesGlobales");
            accionesContainer.innerHTML = "";
            folios.forEach(folioId => {
                accionesContainer.innerHTML += `
                    <div class="mb-3" id="acciones-folio-${folioId}">
                        <button class="btn btn-success" id="btnAutorizar-${folioId}" hidden>
                            Autorizar TODO el Folio ${folioId}
                        </button>
                        <button class="btn btn-danger" id="btnRechazar-${folioId}" hidden>
                            Rechazar TODO el Folio ${folioId}
                        </button>
                    </div>`;
            });

            folios.forEach(folioId => {
                document.getElementById(`btnAutorizar-${folioId}`)
                    ?.addEventListener("click", () => this.enviar(folioId, 1));
                document.getElementById(`btnRechazar-${folioId}`)
                    ?.addEventListener("click", () => this.enviar(folioId, 2));
            });

            const ahora = new Date();
            respuesta.forEach(elemento => this._aplicarEstadoFecha(elemento, ahora));
        }
    }

    // ── renderRow ─────────────────────────────────────────────────────────────
    renderRow(elemento) {
        let accionHtml = "", accionTerminadoHtml = "", badgeValidado = "";

        if (elemento.terminado === null || elemento.terminado === "") {
            if (elemento.validado == null || elemento.validado == 0) {
                // CAMBIO CLAVE: se pasa turnoAsignado como 5to argumento a validarInfo
                accionHtml = `
                    <button class="btn btn-sm btn-warning"
                            onclick="validarInfo('${elemento.NoEmpleadoSol}','${elemento.fechaSol}','${elemento.folioRegistro}','${elemento.HoraInicio}','${elemento.turnoAsignado}')"
                            id="btnValidar-${elemento.folioRegistro}">
                        <i class="fa-solid fa-eye"></i> Validar T. extra
                    </button>
                    <button class="btn btn-sm btn-danger"
                            onclick="window.deletesub(${elemento.folioRegistro})"
                            id="btnEliminar-${elemento.folioRegistro}" hidden>
                        <i class="fas fa-times"></i> Eliminar
                    </button>`;
            } else {
                accionHtml = `<span class="badge bg-success">Solicitud validada</span>`;
            }
            accionTerminadoHtml = `<span class="badge bg-warning">No procesada (Pendiente)</span>`;
        } else if (elemento.terminado == 1) {
            accionHtml          = `<span class="badge bg-success">Aprobado</span>`;
            accionTerminadoHtml = `<span class="badge bg-success">Solicitud procesada (Aprobado)</span>`;
        } else if (elemento.terminado == 2) {
            accionHtml          = `<span class="badge bg-danger">Rechazado</span>`;
            accionTerminadoHtml = `<span class="badge bg-danger">Solicitud procesada (Rechazado)</span>`;
        }

        badgeValidado = (elemento.validado == 1)
            ? `<span class="badge bg-success">Validado</span>`
            : `<span class="badge bg-secondary">No validado</span>`;

        return `<tr>
            <td>${elemento.folioRegistro}</td>
            <td>${elemento.id}</td>
            <td>${elemento.creado}</td>
            <td>${elemento.NoEmp}</td>
            <td>${elemento.SupervisorNombre}</td>
            <td>${elemento.NoEmpleadoSol}</td>
            <td>${elemento.fechaSol}</td>
            <td>${elemento.HoraInicio}</td>
            <td>${elemento.HoraFin}</td>
            <td>${elemento.NombreEmpleadoSol}</td>
            <td>${elemento.departamento}</td>
            <td>${accionTerminadoHtml}<br></td>
            <td>${badgeValidado}</td>
            <td>
                ${accionHtml}
                <small id="msg-${elemento.folioRegistro}" class="d-block mt-1"></small>
            </td>
        </tr>`;
    }

    // ── _aplicarEstadoFecha (helper interno) ──────────────────────────────────
    _aplicarEstadoFecha(elemento, ahora) {
        const fechaSol  = new Date(elemento.fechaSol + "T00:00:00");
        const [h, m, s] = elemento.HoraFin.split(":").map(Number);
        const horaFinDate = new Date(fechaSol);
        horaFinDate.setHours(h, m, s);
        const horaFinMasMargen = new Date(horaFinDate.getTime() + 5 * 60000);

        const btnValidar = document.getElementById(`btnValidar-${elemento.folioRegistro}`);
        const msg        = document.getElementById(`msg-${elemento.folioRegistro}`);

        if (ahora < fechaSol) {
            if (btnValidar) btnValidar.hidden = true;
            if (msg) {
                msg.textContent = `El día solicitado (${elemento.fechaSol}) aún no ha llegado para hacer el cálculo correspondiente.`;
                msg.className   = "alert alert-warning p-1 mt-1";
            }
        } else if (ahora.toDateString() === fechaSol.toDateString() && ahora < horaFinMasMargen) {
            if (btnValidar) btnValidar.hidden = true;
            if (msg) {
                msg.textContent = `Aún no es momento de calcular. El botón se habilitará a las ${horaFinMasMargen.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })} hrs.`;
                msg.className   = "alert alert-warning p-1 mt-1";
            }
        } else {
            if (msg) { msg.textContent = ""; msg.dataset.estado = "pendiente"; }
        }
    }

    // ── filtrarPorFolio ───────────────────────────────────────────────────────
    filtrarPorFolio(folioId) {
        const filtrados = this.data.filter(e => e.id == folioId);
        let body = "";
        filtrados.forEach(e => body += this.renderRow(e));
        document.getElementById("tblenc").innerHTML = body;

        const accionesContainer = document.getElementById("accionesGlobales");
        accionesContainer.innerHTML = `
            <div class="mb-3" id="acciones-folio-${folioId}">
                <button class="btn btn-success" id="btnAutorizar-${folioId}" hidden>
                    Autorizar TODO el Folio ${folioId}
                </button>
                <button class="btn btn-danger" id="btnRechazar-${folioId}" hidden>
                    Rechazar TODO el Folio ${folioId}
                </button>
            </div>`;

        document.getElementById(`btnAutorizar-${folioId}`)
            ?.addEventListener("click", () => this.enviar(folioId, 1));
        document.getElementById(`btnRechazar-${folioId}`)
            ?.addEventListener("click", () => this.enviar(folioId, 2));

        const ahora = new Date();
        filtrados.forEach(e => this._aplicarEstadoFecha(e, ahora));
        this.evaluarFolio(folioId);
    }

    // ── deletesub ─────────────────────────────────────────────────────────────
    async deletesub(id) {
        const data = new FormData();
        data.append("id", id);
        const respuestaraw = await fetch("./php/index.php?deletesub", { method: "POST", body: data });
        const respuesta    = await respuestaraw.json();
        this.consulta();
        respuesta === "Listo"
            ? Swal.fire("Listo!!!", "Registro eliminado", "success")
            : Swal.fire("ERROR!!!", "Hay un problema al eliminar", "error");
    }

    // ── enviar ────────────────────────────────────────────────────────────────
    async enviar(id, autor) {
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

        const respuesta = await fetch(`./php/index.php?autorizafol&id=${id}&autor=${autor}`).then(r => r.json());

        respuesta === false
            ? Swal.fire({ icon: "error",   title: "Error",      text: respuesta.message || "Hay un error con la base de datos." })
            : Swal.fire({ icon: "success", title: "Autorizado", text: "El registro fue autorizado con éxito", timer: 2000, showConfirmButton: false });

        window.open(`./pdf/reporte.php?folio=${btoa(id)}&true=1trlist`);
        window.location.reload();
    }

    // ── pdffin ────────────────────────────────────────────────────────────────
    pdffin(id) {
        window.open(`./pdf/reporte.php?folio=${btoa(id)}&true=1trlist`);
    }

    // ── marcarRegistroComoApto ────────────────────────────────────────────────
    marcarRegistroComoApto(folioRegistro) {
        const btnValidar = document.getElementById(`btnValidar-${folioRegistro}`);
        const msg        = document.getElementById(`msg-${folioRegistro}`);
        if (btnValidar) btnValidar.hidden = true;
        if (msg) {
            msg.textContent    = "Registro apto para tiempo extra.";
            msg.className      = "alert alert-success p-1 mt-1";
            msg.dataset.estado = "apto";
        }
    }

    // ── marcarRegistroComoNoApto ──────────────────────────────────────────────
    marcarRegistroComoNoApto(folioRegistro) {
        const btnValidar  = document.getElementById(`btnValidar-${folioRegistro}`);
        const btnEliminar = document.getElementById(`btnEliminar-${folioRegistro}`);
        const msg         = document.getElementById(`msg-${folioRegistro}`);
        if (btnValidar)  btnValidar.hidden  = true;
        if (btnEliminar) btnEliminar.hidden = false;
        if (msg) {
            msg.textContent    = "Registro no apto, puede eliminarse.";
            msg.className      = "alert alert-warning p-1 mt-1";
            msg.dataset.estado = "noapto";
        }
    }

    // ── evaluarFolio ──────────────────────────────────────────────────────────
    evaluarFolio(folioId) {
        const registros = this.data.filter(e => e.id == folioId);
        let todosAnalizados = true, todosAptos = true;

        registros.forEach(e => {
            const msg    = document.getElementById(`msg-${e.folioRegistro}`);
            const estado = msg?.dataset.estado;
            if (estado !== "apto" && estado !== "noapto") todosAnalizados = false;
            if (estado === "noapto") todosAptos = false;
        });

        const btnAutorizar = document.getElementById(`btnAutorizar-${folioId}`);
        const btnRechazar  = document.getElementById(`btnRechazar-${folioId}`);

        if (todosAnalizados && todosAptos) {
            if (btnAutorizar) btnAutorizar.hidden = false;
            if (btnRechazar)  btnRechazar.hidden  = false;
        } else {
            if (btnAutorizar) btnAutorizar.hidden = true;
            if (btnRechazar)  btnRechazar.hidden  = true;
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Instancia global y eventos
// ─────────────────────────────────────────────────────────────────────────────
window.Reportes = new Reportes();
window.Reportes.consulta();

// CAMBIO: validarInfo recibe turnoAsignado como 5to argumento
window.validarInfo = function(noEmpSol, fechaSol, folioRegistro, HoraInicio, turnoAsignado) {
    window.Reportes.getinfohoraentradaysalida(noEmpSol, fechaSol, folioRegistro, HoraInicio, turnoAsignado);
};
window.deletesub = function(id)  { window.Reportes.deletesub(id); };
window.Autoriza  = function(id)  { window.Reportes.enviar(id, 1); };
window.Rechazar  = function(id)  { window.Reportes.enviar(id, 2); };
window.pdfFin    = function(id)  { window.Reportes.pdffin(id); };

// ── Paginación y buscador ─────────────────────────────────────────────────────
let infoPlanEntregados = [];
let currentPage = 1;
const pageSize  = 15;

document.getElementById("filtroGlobal").addEventListener("keyup", (e) => {
    e.preventDefault();
    clearTimeout(e.target._searchTimer);
    e.target._searchTimer = setTimeout(() => { currentPage = 1; mostrarTabla(); }, 250);
});

async function obtenerDatosArray() {
    try {
        const axiosResponse = await axios.post("php/index.php?tblautorizatp");
        infoPlanEntregados  = axiosResponse.data.map(item => ({ ...item }));
        if (axiosResponse.status === 200) mostrarTabla();
    } catch (error) {
        console.log(error);
        Swal.fire("Error", "Hay un problema con la base de datos", "error");
    }
}
obtenerDatosArray();

function mostrarTabla(query = document.getElementById("filtroGlobal").value) {
    const tbody = document.getElementById("tblenc");
    tbody.innerHTML = "";
    const q = (query || "").toString().trim().toLowerCase();

    const datosFiltrados = q
        ? infoPlanEntregados.filter(item =>
            [item.folioRegistro, item.id, item.fecha, item.departamento,
             item.creado, item.terminado, item.autorizado, item.NoEmp,
             item.SupervisorNombre, item.noempautoriza, item.fechaSol,
             item.NoEmpleadoSol, item.HoraInicio, item.HoraFin,
             item.NombreEmpleadoSol, item.validado, item.turnoAsignado]
            .some(v => v && v.toString().toLowerCase().includes(q)))
        : infoPlanEntregados.slice();

    let body = "";

    if (!datosFiltrados.length) {
        body = `<tr><td colspan="14" class="text-center">No hay registros que coincidan</td></tr>`;
    } else {
        datosFiltrados.forEach(element => {
            let accionHtml = "", accionTerminadoHtml = "", badgeValidado = "";

            if (element.terminado === null || element.terminado === "") {
                if (element.validado == null || element.validado == 0) {
                    // CAMBIO CLAVE: turnoAsignado como 5to argumento
                    accionHtml = `
                        <button class="btn btn-sm btn-warning"
                                onclick="validarInfo('${element.NoEmpleadoSol}','${element.fechaSol}','${element.folioRegistro}','${element.HoraInicio}','${element.turnoAsignado}')"
                                id="btnValidar-${element.folioRegistro}">
                            <i class="fa-solid fa-eye"></i> Validar T. extra
                        </button>
                        <button class="btn btn-sm btn-danger"
                                onclick="window.deletesub(${element.folioRegistro})"
                                id="btnEliminar-${element.folioRegistro}" hidden>
                            <i class="fas fa-times"></i> Eliminar
                        </button>`;
                } else {
                    accionHtml = `<span class="badge bg-success">Solicitud validada</span>`;
                }
                accionTerminadoHtml = `<span class="badge bg-warning">No procesada (Pendiente)</span>`;
            } else if (element.terminado == 1) {
                accionHtml          = `<span class="badge bg-success">Aprobado</span>`;
                accionTerminadoHtml = `<span class="badge bg-success">Solicitud procesada (Aprobado)</span>`;
            } else if (element.terminado == 2) {
                accionHtml          = `<span class="badge bg-danger">Rechazado</span>`;
                accionTerminadoHtml = `<span class="badge bg-danger">Solicitud procesada (Rechazado)</span>`;
            }

            badgeValidado = (element.validado == 1)
                ? `<span class="badge bg-success">Validado</span>`
                : `<span class="badge bg-secondary">No validado</span>`;

            body += `
            <tr>
                <td>${element.folioRegistro}</td>
                <td>${element.id}</td>
                <td>${element.creado}</td>
                <td>${element.NoEmp}</td>
                <td>${element.SupervisorNombre}</td>
                <td>${element.NoEmpleadoSol}</td>
                <td>${element.fechaSol}</td>
                <td>${element.HoraInicio}</td>
                <td>${element.HoraFin}</td>
                <td>${element.NombreEmpleadoSol}</td>
                <td>${element.departamento}</td>
                <td>${accionTerminadoHtml}<br></td>
                <td>${badgeValidado}</td>
                <td>
                    ${accionHtml}
                    <small id="msg-${element.folioRegistro}" class="d-block mt-1"></small>
                </td>
            </tr>`;
        });
    }

    tbody.innerHTML = body;

    const ahora = new Date();
    datosFiltrados.forEach(elemento => window.Reportes._aplicarEstadoFecha(elemento, ahora));
}

// ── Tutorial driver.js ────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    const driver = window.driver.js.driver;

    const steps = [
        { element: ".tittlecont",
            popover: { 
                title: "Validación de solicitudes",  
                description: "Aquí podrás validar las solicitudes de tiempo extra antes de enviarlas a Gerencia.", 
                side: "bottom" 
            } 
        },
        { element: ".alert.alert-info",
            popover: { 
                title: "Instrucciones",
                description: "Desde esta sección revisa qué solicitudes son aptas para autorización.", 
                side: "bottom" 
            } 
        },
        { element: "#filtroGlobal",                  
            popover: { 
                title: "Filtro global",              
                description: "Usa este campo para buscar solicitudes por nombre, folio o cualquier dato de la tabla.", 
                side: "top", 
                popoverClass: "popover-importante" 
            } 
        },
        { element: "table thead th:nth-child(1)",    
            popover: { 
                title: "ID Registro",               
                description: "Identificador único de cada solicitud.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(2)",    
            popover: { 
                title: "Folio",                    
                description: "Número de folio al que pertenece la solicitud.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(3)",    
            popover: { 
                title: "Creación de folio",         
                description: "La fecha en la que se creó el folio inicial.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(4)",    
            popover: { 
                title: "Noemp de supervisor",       
                description: "Número de empleado del supervisor.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(5)",    
            popover: { 
                title: "Nombre de supervisor",      
                description: "Nombre del supervisor que abrió el folio.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(6)",    
            popover: { 
                title: "Noemp solicitante",         
                description: "Número de empleado de la persona que va a realizar el tiempo extra.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(7)",    
            popover: { 
                title: "Fecha Solicitud",           
                description: "Día en que el empleado solicitó el tiempo extra.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(8)",    
            popover: { 
                title: "Hora Inicio",               
                description: "Hora en que comienza el tiempo extra.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(9)",    
            popover: { 
                title: "Hora Fin",                  
                description: "Hora en que termina el tiempo extra.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(10)",   
            popover: { 
                title: "Nombre solicitante",        
                description: "Nombre de la persona que va a realizar el tiempo extra.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(11)",   
            popover: { 
                title: "Departamento",              
                description: "Departamento de la persona que solicita tiempo extra.", 
                side: "top" 
            } 
        },
        { element: "table thead th:nth-child(12)",   
            popover: { 
                title: "Estatus",                   
                description: "Aquí se mostrará si la solicitud fue aprobada o rechazada por Gerencia.", 
                side: "top", 
                popoverClass: "popover-importante" 
            } 
        },
        { element: "table thead th:nth-child(13)",   
            popover: { 
                title: "Validado",                  
                description: "Aquí se mostrará si la solicitud ya fue validada con el botón 'Validar T. extra'.", 
                side: "top", 
                popoverClass: "popover-importante" 
            } 
        },
        { element: "table thead th:nth-child(14)",   
            popover: { 
                title: "Acciones",                  
                description: "Aquí encontrarás botones para validar individualmente cada registro.", 
                side: "top", 
                popoverClass: "popover-importante" 
            } 
        },
        { element: "#tblenc",                        
            popover: { 
                title: "Solicitudes listadas",      
                description: "En esta tabla se mostrarán todas las solicitudes con sus datos y estado actual.", 
                side: "top" 
            } 
        },
        { element: "#btnAyuda",                      
            popover: { 
                title: "Volver a ver el tutorial",  
                description: "Si necesitas repasar cómo usar esta pantalla, presiona este botón.", 
                side: "bottom" 
            } 
        }
    ];

    document.querySelectorAll('[id^="msg-"]').forEach(msgEl => {
        steps.push({
            element: `#${msgEl.id}`,
            popover: { title: "Mensaje de validación", description: msgEl.textContent || "Aquí aparecerán mensajes sobre el estado de la solicitud.", side: "top" }
        });
    });

    const driverObj = driver({
        showProgress:       true,
        allowClose:         false,
        disableInteraction: true,
        progressText:       "Paso {{current}} de {{total}}",
        doneBtnText:        "Finalizar",
        nextBtnText:        "Siguiente",
        prevBtnText:        "Atrás",
        steps
    });

    const tutorialKey      = "tutorial_validacionTE";
    const tutorialYaVisto  = localStorage.getItem(tutorialKey);
    if (!tutorialYaVisto) { driverObj.drive(); localStorage.setItem(tutorialKey, "true"); }

    document.getElementById("btnAyuda")?.addEventListener("click", () => driverObj.drive());
});