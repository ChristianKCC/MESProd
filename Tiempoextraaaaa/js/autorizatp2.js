import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();

// ─────────────────────────────────────────────────────────────────────────────
// IDs DE MOTIVOS
// ─────────────────────────────────────────────────────────────────────────────
const MOTIVO_CAMBIO_HORARIO     = 8;
const MOTIVO_HORA_COMIDA        = 9;
const MOTIVO_DESCANSO_TRABAJADO = 10;
const MOTIVO_DIA_FESTIVO        = 12;

const MOTIVOS_AUTO_APTOS     = [MOTIVO_CAMBIO_HORARIO, MOTIVO_HORA_COMIDA];
const MOTIVOS_ESPECIALES     = [MOTIVO_DESCANSO_TRABAJADO, MOTIVO_DIA_FESTIVO];
const MOTIVOS_MIXTO_ESPECIAL = [1, 2, 3, 4, 5, 6, 7, 11];
const TURNOS_MIXTOS          = ["mixto1", "mixto2", "mixto3", "mixto4"];

// ─────────────────────────────────────────────────────────────────────────────
// CATÁLOGO CENTRAL DE TURNOS
// ─────────────────────────────────────────────────────────────────────────────
const CATALOGO_TURNOS = {
    turno1: { nombre: "Turno 1 (07:00 - 15:00)", entrada: { ideal: 7*60, tolerancia: 35 }, salida: { ideal: 15*60, tolerancia: 60 }, cruzaMedia: false, horasReglamentarias: "08:00:00", sabado: null },
    turno2: { nombre: "Turno 2 (15:00 - 22:30)", entrada: { ideal: 15*60, tolerancia: 35 }, salida: { ideal: 22*60+30, tolerancia: 60 }, cruzaMedia: false, horasReglamentarias: "07:30:00", sabado: null },
    turno2_13hrs: { nombre: "Turno 2 13hrs (10:30 - 22:30)", entrada: { ideal: 10*60+30, tolerancia: 20 }, salida: { ideal: 22*60+30, tolerancia: 60 }, cruzaMedia: false, horasReglamentarias: "04:30:00", sabado: null },
    turno3: { nombre: "Turno 3 (22:30 - 07:00)", entrada: { ideal: 22*60+30, tolerancia: 35 }, salida: { ideal: 7*60, tolerancia: 35 }, cruzaMedia: true, horasReglamentarias: "08:30:00", sabado: null },
    turno3_12hrs: { nombre: "Turno 3 12hrs (19:00 - 07:00)", entrada: { ideal: 19*60, tolerancia: 20 }, salida: { ideal: 7*60, tolerancia: 20 }, cruzaMedia: true, horasReglamentarias: "08:30:00", sabado: null },
    mixto1: { nombre: "Mixto 1 (07:30 - 17:00)", entrada: { ideal: 7*60+30, tolerancia: 35 }, salida: { ideal: 17*60, tolerancia: 60 }, cruzaMedia: false, horasReglamentarias: "10:00:00", sabado: { nombre: "Mixto 1 Guardia (Sáb/Dom)", salida: { ideal: 12*60+30, tolerancia: 60 }, horasReglamentarias: "05:00:00" } },
    mixto2: { nombre: "Mixto 2 (08:30 - 18:30)", entrada: { ideal: 8*60+30, tolerancia: 35 }, salida: { ideal: 18*60+30, tolerancia: 60 }, cruzaMedia: false, horasReglamentarias: "10:00:00", sabado: { nombre: "Mixto 2 Guardia (Sáb/Dom)", salida: { ideal: 13*60+30, tolerancia: 60 }, horasReglamentarias: "05:00:00" } },
    mixto3: { nombre: "Mixto 3 (07:00 - 16:30)", entrada: { ideal: 7*60, tolerancia: 35 }, salida: { ideal: 16*60+30, tolerancia: 60 }, cruzaMedia: false, horasReglamentarias: "09:30:00", sabado: { nombre: "Mixto 3 Guardia (Sáb/Dom)", salida: { ideal: 12*60, tolerancia: 60 }, horasReglamentarias: "05:00:00" } },
    mixto4: { nombre: "Mixto 4 (07:00 - 17:00)", entrada: { ideal: 7*60, tolerancia: 15 }, salida: { ideal: 17*60, tolerancia: 60 }, cruzaMedia: false, horasReglamentarias: "10:00:00", sabado: { nombre: "Mixto 4 Guardia (Sáb/Dom)", salida: { ideal: 12*60, tolerancia: 60 }, horasReglamentarias: "05:00:00" } }
};

// ─────────────────────────────────────────────────────────────────────────────
// UTILIDADES
// ─────────────────────────────────────────────────────────────────────────────
function horaAMinutos(hora) { const [h, m] = hora.split(":").map(Number); return h * 60 + m; }
function minutosAHora(m) { const h = Math.floor(Math.abs(m) / 60) % 24, mn = Math.abs(m) % 60; return h.toString().padStart(2,"0") + ":" + mn.toString().padStart(2,"0"); }
function minutosAHoraConSegundos(m) { return minutosAHora(m) + ":00"; }
function limpiarRegistros(registros) {
    const vistos = new Set();
    return registros.filter(r => r.fecha_h).map(r => ({ ...r, hora_limpia: r.fecha_h.substring(0, 8) }))
        .filter(r => { const c = r.hora_limpia.substring(0, 5); if (vistos.has(c)) return false; vistos.add(c); return true; })
        .sort((a, b) => horaAMinutos(a.hora_limpia) - horaAMinutos(b.hora_limpia));
}

// ─────────────────────────────────────────────────────────────────────────────
// resolverLapso — lógica UNIVERSAL por lapso (para TODOS los turnos)
//
// Para turnos que cruzan medianoche (turno3): usar resolverTurnoNocturno.
// Para el resto: tomar primer y último registro del día.
// ─────────────────────────────────────────────────────────────────────────────
function resolverTurnoNocturno(regAnterior, regActual, regSiguiente) {
    const nocturnos = regActual.filter(r => horaAMinutos(r.hora_limpia) >= 18 * 60);
    const diurnos   = regActual.filter(r => horaAMinutos(r.hora_limpia) <  12 * 60);

    console.log(`[Nocturno] Nocturnos(>=18h): ${nocturnos.map(r=>r.hora_limpia).join(", ") || "ninguno"}`);
    console.log(`[Nocturno] Diurnos(<12h): ${diurnos.map(r=>r.hora_limpia).join(", ") || "ninguno"}`);

    // CASO A: hay entrada nocturna hoy → salida en el día siguiente
    if (nocturnos.length > 0) {
        const entrada = nocturnos[nocturnos.length - 1];
        if (regSiguiente.length > 0) {
            const salidaMañana = regSiguiente.find(r => horaAMinutos(r.hora_limpia) < 15 * 60) || regSiguiente[0];
            return { entrada, salida: salidaMañana, escenario: "nocturno_entrada_hoy_salida_mañana" };
        }
        return { entrada, salida: null, escenario: "nocturno_sin_salida" };
    }

    // CASO B: solo diurnos hoy → son la salida, entrada en el día anterior
    if (diurnos.length > 0) {
        const salida = diurnos[diurnos.length - 1];
        const nocturnosAyer = regAnterior.filter(r => horaAMinutos(r.hora_limpia) >= 18 * 60);
        if (nocturnosAyer.length > 0) {
            const entrada = nocturnosAyer[nocturnosAyer.length - 1];
            return { entrada, salida, escenario: "nocturno_entrada_ayer_salida_hoy" };
        }
        return { entrada: null, salida, escenario: "nocturno_sin_entrada" };
    }
    return null;
}

// ─────────────────────────────────────────────────────────────────────────────
// resolverLapsoDiurno — para turno1, turno2, mixtos
// Si hay ≥2 registros hoy: primer y último del día.
// Si hay 1 registro hoy: retorna null para que procesarPorLapso
// pida al supervisor elegir la hora de salida del día siguiente.
// ─────────────────────────────────────────────────────────────────────────────
function resolverLapsoDiurno(regActual) {
    if (regActual.length >= 2) {
        return { entrada: regActual[0], salida: regActual[regActual.length - 1], escenario: "lapso_directo" };
    }
    return null; // 1 registro → se maneja interactivamente en procesarPorLapso
}

// ─────────────────────────────────────────────────────────────────────────────
// fetchChecador — descarga registros de 3 días
// ─────────────────────────────────────────────────────────────────────────────
async function fetchChecador(noemp, date) {
    const fa = new Date(date + "T00:00:00"); fa.setDate(fa.getDate() - 1);
    const fs = new Date(date + "T00:00:00"); fs.setDate(fs.getDate() + 1);
    const fmt = f => `${f.getFullYear()}-${String(f.getMonth()+1).padStart(2,"0")}-${String(f.getDate()).padStart(2,"0")}`;

    const [rawA, rawC, rawS] = await Promise.all([
        fetch(`../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${fmt(fa)}`).then(r => r.json()),
        fetch(`../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${date}`).then(r => r.json()),
        fetch(`../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${fmt(fs)}`).then(r => r.json())
    ]);

    return {
        regAnterior:  limpiarRegistros(rawA),
        regActual:    limpiarRegistros(rawC),
        regSiguiente: limpiarRegistros(rawS),
        rawActual: rawC,
        fechaAnterior: fmt(fa), fechaActual: date, fechaSiguiente: fmt(fs)
    };
}

// ─────────────────────────────────────────────────────────────────────────────
// actualizarValidado
// ─────────────────────────────────────────────────────────────────────────────
async function actualizarValidado(folioRegistro, horaFinNueva) {
    const data = new FormData();
    data.append("folioRegistro", folioRegistro);
    if (horaFinNueva) data.append("nuevaHoraFin", horaFinNueva);
    try {
        const r = await fetch(`php/index.php?${horaFinNueva ? "actualizarHoraFin" : "actualizarEstadoValidado"}`, { method: "POST", body: data });
        const resp = await r.json();
        if (!resp.success) Swal.fire("Error", `No se pudo actualizar: ${resp.error}`, "error");
        return resp.success;
    } catch (err) { console.error("Error actualizarValidado:", err); return false; }
}

// ─────────────────────────────────────────────────────────────────────────────
// actualizarFilaDOM
// ─────────────────────────────────────────────────────────────────────────────
function actualizarFilaDOM(folioRegistro, registro) {
    const fila = document.querySelector(`#msg-${folioRegistro}`)?.closest("tr");
    if (!fila) return;
    const ec = fila.querySelector("td:nth-child(13)");
    const ac = fila.querySelector("td:nth-child(14)");
    if (ec) ec.innerHTML = registro?.validado == 1 ? `<span class="badge bg-success">Validado</span>` : `<span class="badge bg-warning">No validado aún</span>`;
    if (ac) {
        ac.innerHTML = registro?.validado == 1
            ? `<span class="badge bg-success">Ya validado</span><small id="msg-${folioRegistro}" class="d-block mt-1"></small>`
            : `<button class="btn btn-sm btn-warning" onclick="validarInfo('${registro.NoEmpleadoSol}','${registro.fechaSol}','${registro.folioRegistro}','${registro.HoraInicio}','${registro.turnoAsignado}',${registro.folioRegistro})" id="btnValidar-${registro.folioRegistro}"><i class="fa-solid fa-eye"></i> Validar T. extra</button>
               <button class="btn btn-sm btn-danger" onclick="window.deletesub(${registro.folioRegistro})" id="btnEliminar-${registro.folioRegistro}" hidden><i class="fas fa-times"></i> Eliminar</button>
               <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// ajustarHorasRegistro — actualiza HoraInicio y HoraFin en BD
// ─────────────────────────────────────────────────────────────────────────────
async function ajustarHorasRegistro(folioRegistro, nuevaHoraInicio, nuevaHoraFin) {
    const d = new FormData();
    d.append("folioRegistro", folioRegistro);
    d.append("nuevaHoraFin", nuevaHoraFin);
    d.append("nuevaHoraInicio", nuevaHoraInicio);
    try {
        const r = await fetch("php/index.php?actualizarHorasReales", { method: "POST", body: d });
        const resp = await r.json();
        if (!resp.success) { await actualizarValidado(folioRegistro, nuevaHoraFin); return false; }
        const reg = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
        if (reg) { reg.HoraInicio = nuevaHoraInicio; reg.HoraFin = nuevaHoraFin; reg.validado = 1; }
        actualizarFilaDOM(folioRegistro, reg);
        return true;
    } catch (err) { await actualizarValidado(folioRegistro, nuevaHoraFin); return false; }
}

// ─────────────────────────────────────────────────────────────────────────────
// crearRegistroExcedente — SOLO para descanso trabajado y día festivo
// ─────────────────────────────────────────────────────────────────────────────
async function crearRegistroExcedente(datosRegistro, excedenteMin, motivo) {
    const def = CATALOGO_TURNOS[datosRegistro.turnoAsignado];
    const esSabado = [6, 0].includes(new Date(datosRegistro.fechaSol + "T00:00:00").getDay());
    const salidaDef = (esSabado && def?.sabado) ? def.sabado.salida : def?.salida;
    const finMin = salidaDef ? salidaDef.ideal : 0;

    const data = new FormData();
    data.append("noemp",       datosRegistro.NoEmpleadoSol);
    data.append("fechainput",  datosRegistro.fechaSol);
    data.append("horai",       minutosAHoraConSegundos(finMin));
    data.append("horaf",       minutosAHoraConSegundos((finMin + excedenteMin) % (24 * 60)));
    data.append("maquina",     datosRegistro.maquina || "");
    data.append("motivos",     motivo);
    data.append("razon",       datosRegistro.razon || "");
    data.append("folio",       datosRegistro.id);
    data.append("turnosel",    datosRegistro.turnoAsignado);
    data.append("nombre",      datosRegistro.NombreEmpleadoSol);
    data.append("esExcedente", "1");
    data.append("validado",    "1");

    try {
        const r = await fetch("php/index.php?guardartiempoextra", { method: "POST", body: data });
        const resp = await r.json();
        if (resp === "Listo") {
            await Swal.fire("Excedente registrado", `Se creó un registro adicional de <b>${minutosAHoraConSegundos(excedenteMin)}</b> hrs como tiempo extra.`, "success");
        } else { Swal.fire("Advertencia", `No se pudo crear el registro de excedente: ${JSON.stringify(resp)}`, "warning"); }
    } catch (err) { console.error("Error crearRegistroExcedente:", err); }
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarPorLapso — función genérica para procesar cualquier turno por lapso
//
// Para TODOS los turnos (turno1, turno2, mixtos) en motivos normales.
// Si hay 1 solo registro hoy → muestra los primeros 2 registros del día
// siguiente para que el supervisor elija cuál es la hora de salida real.
// Siempre ajusta HoraInicio y HoraFin en BD con las horas reales.
// ─────────────────────────────────────────────────────────────────────────────
async function procesarPorLapso(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) { mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`); return; }

    const esSabado   = [6, 0].includes(new Date(date + "T00:00:00").getDay());
    const hrsReglDef = (esSabado && def.sabado) ? def.sabado.horasReglamentarias : def.horasReglamentarias;
    const hrsReglMin = horaAMinutos(hrsReglDef);

    console.log(`═══ procesarPorLapso: folio=${folioRegistro} emp=${noemp} fecha=${date} turno=${turnoAsignado} regl=${hrsReglDef}`);

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr    = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regAyerStr   = checador.regAnterior.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regMañanaStr = checador.regSiguiente.map(r => r.hora_limpia).join(", ") || "ninguno";

    console.log(`Ayer(${checador.fechaAnterior}): ${regAyerStr} | Hoy(${checador.fechaActual}): ${regHoyStr} | Mañana(${checador.fechaSiguiente}): ${regMañanaStr}`);

    // Sin registros hoy → no se puede validar
    if (checador.regActual.length === 0) {
        mostrarError(
            `No hay ningún registro en el checador para el día ${date}.`,
            { registrosHoy: "ninguno", registrosAyer: regAyerStr, registrosMañana: regMañanaStr }
        );
        return;
    }

    let primerReg, ultimoReg, diaSalida;

    // ── CASO: 1 solo registro hoy → pedir al supervisor elegir la salida ──────
    if (checador.regActual.length === 1) {
        primerReg = checador.regActual[0];

        if (checador.regSiguiente.length === 0) {
            mostrarError(
                `Solo se encontró 1 registro hoy (<b>${primerReg.hora_limpia}</b>) y no hay registros en el día siguiente (${checador.fechaSiguiente}) para determinar la salida.`,
                { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: "ninguno" }
            );
            return;
        }

        // Mostrar solo los primeros 2 registros del día siguiente como opciones
        const candidatos = checador.regSiguiente.slice(0, 2);
        const opcionesHTML = candidatos.map((r, i) =>
            `<div style="margin:8px 0;">
                <button id="opcion-salida-${i}" class="swal2-confirm swal2-styled" style="background:#4caf50; margin:4px; padding:8px 20px; font-size:14px;">
                    ${r.hora_limpia} <small style="opacity:0.8">(${checador.fechaSiguiente})</small>
                </button>
             </div>`
        ).join("");

        const { value: horaElegida } = await Swal.fire({
            title: `Solo 1 registro hoy — Selecciona la hora de salida`,
            html: `Solo se encontró <b>1 registro</b> en el checador para el día <b>${checador.fechaActual}</b>: <b>${primerReg.hora_limpia}</b><br><br>
                   Se encontraron <b>${checador.regSiguiente.length}</b> registro(s) en el día siguiente (<b>${checador.fechaSiguiente}</b>): <b>${regMañanaStr}</b><br><br>
                   <b>Selecciona cuál es la hora de salida real:</b><br>
                   ${opcionesHTML}`,
            icon: "question",
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: "Cancelar — no validar",
            didOpen: () => {
                candidatos.forEach((r, i) => {
                    document.getElementById(`opcion-salida-${i}`)?.addEventListener("click", () => {
                        Swal.close();
                        // Guardar la elección en el elemento del DOM para recuperarla
                        document.getElementById(`opcion-salida-${i}`).dataset.elegida = r.hora_limpia;
                        window._salidaElegidaLapso = r.hora_limpia;
                    });
                });
            }
        });

        // Si canceló → no validar
        if (!window._salidaElegidaLapso) {
            const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
            if (fid) window.Reportes.evaluarFolio(fid);
            return;
        }

        // Confirmación de la hora elegida
        const salidaElegida = window._salidaElegidaLapso;
        window._salidaElegidaLapso = null; // limpiar

        const confirmacion = await Swal.fire({
            title: "Confirmar hora de salida",
            html: `¿Confirmas que la hora de salida es <b>${salidaElegida}</b> del día <b>${checador.fechaSiguiente}</b>?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, confirmar",
            cancelButtonText: "No, cancelar"
        });

        if (!confirmacion.isConfirmed) {
            const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
            if (fid) window.Reportes.evaluarFolio(fid);
            return;
        }

        ultimoReg  = { hora_limpia: salidaElegida };
        diaSalida  = checador.fechaSiguiente;

    } else {
        // ── CASO NORMAL: ≥2 registros hoy ────────────────────────────────────
        const resolucion = resolverLapsoDiurno(checador.regActual);
        primerReg = resolucion.entrada;
        ultimoReg = resolucion.salida;
        diaSalida = checador.fechaActual;
    }

    // ── Calcular excedente ────────────────────────────────────────────────────
    const entradaMin = horaAMinutos(primerReg.hora_limpia);
    let   salidaMin  = horaAMinutos(ultimoReg.hora_limpia);
    if (salidaMin < entradaMin) salidaMin += 1440;

    const minutosTrabajados = salidaMin - entradaMin;
    const excedente         = Math.max(0, minutosTrabajados - hrsReglMin);
    const horaFinReal       = ultimoReg.hora_limpia;
    const horaInicioExtra   = excedente > 0
        ? minutosAHoraConSegundos(((salidaMin - excedente) + 24 * 60) % (24 * 60))
        : horaFinReal;

    console.log(`Entrada(${checador.fechaActual}): ${primerReg.hora_limpia} | Salida(${diaSalida}): ${ultimoReg.hora_limpia} | Trabajado: ${minutosTrabajados}min | Excedente: ${excedente}min`);

    if (minutosTrabajados < (hrsReglMin - 5)) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: `${turnoAsignado} — No apto`,
            html:  `El empleado trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero el turno requiere <b>${hrsReglDef}</b>.<br><br>
                    Turno: <b>${turnoAsignado}</b><br>
                    Entrada (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                    Salida (${diaSalida}): <b>${ultimoReg.hora_limpia}</b><br>
                    Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>
                    Registros ayer (${checador.fechaAnterior}): <b>${regAyerStr}</b><br>
                    Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
            icon: "warning"
        });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
        if (fid) window.Reportes.evaluarFolio(fid);
        return;
    }

    if (excedente < 55) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: `${turnoAsignado} — No apto (excedente insuficiente)`,
            html:  `El empleado completó las horas reglamentarias pero el excedente (<b>${minutosAHoraConSegundos(excedente)}</b>) no alcanza los 55 minutos mínimos.<br><br>
                    Turno: <b>${turnoAsignado}</b><br>
                    Entrada (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                    Salida (${diaSalida}): <b>${ultimoReg.hora_limpia}</b><br>
                    Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                    Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>
                    Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
            icon: "warning"
        });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
        if (fid) window.Reportes.evaluarFolio(fid);
        return;
    }

    // ── Apto → mostrar resultado y ajustar horas en BD ────────────────────────
    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({
        title: `${turnoAsignado} — Apto`,
        html:  `Turno: <b>${turnoAsignado}</b><br>
                Horas reglamentarias: <b>${hrsReglDef}</b><br>
                Entrada (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                Salida (${diaSalida}): <b>${ultimoReg.hora_limpia}</b><br>
                Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                Excedente (T. extra): <b>${minutosAHoraConSegundos(excedente)}</b><br>
                Tipo: <b>${tipoEmpleado}</b><br><br>
                Horas T. extra ajustadas:<br>
                Inicio: <b>${horaInicioExtra}</b> | Fin: <b>${horaFinReal}</b>`,
        icon: "success"
    });

    // Siempre ajustar horas (incluso si no hubo excedente para dejar las horas reales)
    const ok = await ajustarHorasRegistro(folioRegistro, horaInicioExtra, horaFinReal);
    if (ok) await Swal.fire("Actualización realizada", `Registro validado.<br>Horas ajustadas: <b>${horaInicioExtra}</b> → <b>${horaFinReal}</b>`, "success");

    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarDoblete
//
// Registros con razon = "Doblete":
//   - Verificar hrs reglamentarias del turno asignado.
//   - El excedente es el tiempo extra.
//   - Si también tiene razon = "Anticipo" (no aplica acá, ese caso es "Anticipo"):
//     → se maneja en procesarAnticipo.
//   - Doblete hacia adelante (sin anticipo):
//     HoraInicio = fin_turno | HoraFin = fin_turno + excedente
//   - Doblete hacia atrás (anticipo):
//     HoraInicio = inicio_turno - excedente | HoraFin = inicio_turno
//
// Como la razon ya dice "Doblete" (no "Anticipo"), sabemos que es hacia adelante.
// ─────────────────────────────────────────────────────────────────────────────
async function procesarDoblete(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) { mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`); return; }

    const esSabado   = [6, 0].includes(new Date(date + "T00:00:00").getDay());
    const hrsReglDef = (esSabado && def.sabado) ? def.sabado.horasReglamentarias : def.horasReglamentarias;
    const hrsReglMin = horaAMinutos(hrsReglDef);

    const esTurnoNocturno = ["turno3", "turno3_12hrs"].includes(turnoAsignado);

    console.log(`═══ procesarDoblete: folio=${folioRegistro} turno=${turnoAsignado} regl=${hrsReglDef}`);

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr    = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regAyerStr   = checador.regAnterior.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regMañanaStr = checador.regSiguiente.map(r => r.hora_limpia).join(", ") || "ninguno";

    let resolucion;
    if (esTurnoNocturno) {
        resolucion = resolverTurnoNocturno(checador.regAnterior, checador.regActual, checador.regSiguiente);
        if (!resolucion || !resolucion.entrada || !resolucion.salida) {
            mostrarError(`No se pudo determinar entrada/salida del turno nocturno para el día ${date}.`,
                { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: regMañanaStr });
            return;
        }
    } else {
        resolucion = resolverLapsoDiurno(checador.regActual);
        if (!resolucion) {
            mostrarError(`Se necesitan al menos 2 registros en el checador del día ${date}.`,
                { registrosHoy: regHoyStr, registrosAyer: regAyerStr });
            return;
        }
    }

    const entradaMin = horaAMinutos(resolucion.entrada.hora_limpia);
    let   salidaMin  = horaAMinutos(resolucion.salida.hora_limpia);
    if (salidaMin < entradaMin) salidaMin += 1440;

    const minutosTrabajados = salidaMin - entradaMin;
    const excedente         = Math.max(0, minutosTrabajados - hrsReglMin);

    // Doblete hacia adelante: inicio del extra = fin del turno normal
    const def_fin = esTurnoNocturno
        ? ((def.cruzaMedia ? def.salida.ideal + 1440 : def.salida.ideal))
        : def.salida.ideal;

    const horaInicioExtra = minutosAHoraConSegundos(def.salida.ideal);
    const horaFinExtra    = minutosAHoraConSegundos((def.salida.ideal + excedente) % (24 * 60));

    console.log(`Entrada: ${resolucion.entrada.hora_limpia} | Salida: ${resolucion.salida.hora_limpia} | Trabajado: ${minutosTrabajados}min | Excedente: ${excedente}min`);

    if (minutosTrabajados < (hrsReglMin - 5)) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: "Doblete — No apto",
            html:  `El empleado trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero el turno requiere <b>${hrsReglDef}</b>.<br><br>
                    Turno: <b>${turnoAsignado}</b><br>
                    Entrada (${checador.fechaActual} o ayer): <b>${resolucion.entrada.hora_limpia}</b><br>
                    Salida: <b>${resolucion.salida.hora_limpia}</b><br>
                    Registros hoy: <b>${regHoyStr}</b><br>
                    Registros ayer (${checador.fechaAnterior}): <b>${regAyerStr}</b><br>
                    Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
            icon: "warning"
        });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
        if (fid) window.Reportes.evaluarFolio(fid);
        return;
    }

    if (excedente < 55) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: "Doblete — No apto (excedente insuficiente)",
            html:  `El empleado completó las reglamentarias pero el excedente (<b>${minutosAHoraConSegundos(excedente)}</b>) no alcanza los 55 minutos mínimos.<br><br>
                    Turno: <b>${turnoAsignado}</b><br>
                    Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                    Registros hoy: <b>${regHoyStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
            icon: "warning"
        });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
        if (fid) window.Reportes.evaluarFolio(fid);
        return;
    }

    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({
        title: "Doblete — Apto",
        html:  `Turno: <b>${turnoAsignado}</b><br>
                Horas reglamentarias: <b>${hrsReglDef}</b><br>
                Entrada (${checador.fechaActual} o ayer): <b>${resolucion.entrada.hora_limpia}</b><br>
                Salida: <b>${resolucion.salida.hora_limpia}</b><br>
                Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                Excedente (T. extra doblete): <b>${minutosAHoraConSegundos(excedente)}</b><br>
                Tipo: <b>${tipoEmpleado}</b><br><br>
                Horas T. extra ajustadas (hacia adelante):<br>
                Inicio: <b>${horaInicioExtra}</b> | Fin: <b>${horaFinExtra}</b>`,
        icon: "success"
    });

    const ok = await ajustarHorasRegistro(folioRegistro, horaInicioExtra, horaFinExtra);
    if (ok) await Swal.fire("Actualización realizada", `Registro validado.<br>Horas ajustadas: <b>${horaInicioExtra}</b> → <b>${horaFinExtra}</b>`, "success");

    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarDescansoOFestivo
// ─────────────────────────────────────────────────────────────────────────────
async function procesarDescansoOFestivo(noemp, date, folioRegistro, turnoAsignado, motivo, tipoEmpleado, datosRegistro, mostrarError) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) { mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`); return; }

    const hrsReglMin = horaAMinutos(def.horasReglamentarias);
    const esSabado   = [6, 0].includes(new Date(date + "T00:00:00").getDay());
    const esTurnoNocturno = ["turno3", "turno3_12hrs"].includes(turnoAsignado);
    const esMixtoFestivo  = motivo === MOTIVO_DIA_FESTIVO && TURNOS_MIXTOS.includes(turnoAsignado);

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr    = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regAyerStr   = checador.regAnterior.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regMañanaStr = checador.regSiguiente.map(r => r.hora_limpia).join(", ") || "ninguno";

    if (!checador.rawActual.length) { mostrarError(`No hay registros en el checador para el día ${date}.`, { registrosHoy: "ninguno", registrosAyer: regAyerStr }); return; }

    let resolucion;
    if (esMixtoFestivo) {
        if (checador.regActual.length < 2) { mostrarError(`Turno mixto en día festivo: se necesitan al menos 2 registros del día ${date}.`, { registrosHoy: regHoyStr }); return; }
        resolucion = { entrada: checador.regActual[0], salida: checador.regActual[checador.regActual.length - 1], escenario: "mixto_festivo_directo" };
    } else if (esTurnoNocturno) {
        resolucion = resolverTurnoNocturno(checador.regAnterior, checador.regActual, checador.regSiguiente);
        if (!resolucion || !resolucion.entrada || !resolucion.salida) {
            mostrarError(`No se pudo determinar entrada/salida del turno nocturno para el día ${date}.`, { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: regMañanaStr }); return;
        }
    } else {
        resolucion = resolverLapsoDiurno(checador.regActual, checador.regSiguiente);
        if (!resolucion) {
            mostrarError(
                `No hay suficientes registros en el checador del día ${date} y tampoco en el día siguiente (${checador.fechaSiguiente}) para determinar la salida.`,
                { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: regMañanaStr }
            );
            return;
        }
    }

    const entradaMin = horaAMinutos(resolucion.entrada.hora_limpia);
    let   salidaMin  = horaAMinutos(resolucion.salida.hora_limpia);
    if (salidaMin < entradaMin) salidaMin += 1440;
    const minutosTrabajados = salidaMin - entradaMin;

    if (motivo === MOTIVO_DESCANSO_TRABAJADO) {
        if (minutosTrabajados < (hrsReglMin - 5)) {
            window.Reportes.marcarRegistroComoNoApto(folioRegistro);
            await Swal.fire({ title: "Descanso trabajado — No apto", html: `Trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero requiere <b>${def.horasReglamentarias}</b>.<br><br>Entrada: <b>${resolucion.entrada.hora_limpia}</b> | Salida: <b>${resolucion.salida.hora_limpia}</b><br>Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>Registros ayer (${checador.fechaAnterior}): <b>${regAyerStr}</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "warning" });
            return;
        }
        const excedente = Math.max(0, minutosTrabajados - hrsReglMin);
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        await Swal.fire({ title: "Descanso trabajado — Apto", html: `Turno: <b>${turnoAsignado}</b> | Reglamentarias: <b>${def.horasReglamentarias}</b><br>Entrada: <b>${resolucion.entrada.hora_limpia}</b> | Salida: <b>${resolucion.salida.hora_limpia}</b><br>Trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>Tipo: <b>${tipoEmpleado}</b><br>${excedente >= 55 ? `Excedente: <b>${minutosAHoraConSegundos(excedente)}</b> → se creará registro adicional.` : "Sin excedente significativo."}`, icon: "success" });
        const ok = await actualizarValidado(folioRegistro, null);
        if (ok) { const reg = window.Reportes.data.find(e => e.folioRegistro == folioRegistro); if (reg) { reg.validado = 1; actualizarFilaDOM(folioRegistro, reg); } await Swal.fire("Actualización realizada", "El registro quedó marcado como validado.", "success"); }
        if (excedente >= 55) await crearRegistroExcedente(datosRegistro, excedente, motivo);

    } else if (motivo === MOTIVO_DIA_FESTIVO) {
        const hrsParaFestivo = Math.min(minutosTrabajados, hrsReglMin);
        const excedente = Math.max(0, minutosTrabajados - hrsReglMin);
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        await Swal.fire({ title: "Día festivo — Apto", html: `Turno: <b>${turnoAsignado}</b><br>Entrada: <b>${resolucion.entrada.hora_limpia}</b> | Salida: <b>${resolucion.salida.hora_limpia}</b><br>Trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> | Reconocidas: <b>${minutosAHoraConSegundos(hrsParaFestivo)}</b><br>Tipo: <b>${tipoEmpleado}</b><br>${excedente >= 55 ? `Excedente: <b>${minutosAHoraConSegundos(excedente)}</b> → se creará registro adicional.` : ""}`, icon: "success" });
        const ok = await ajustarHorasRegistro(folioRegistro, resolucion.entrada.hora_limpia, resolucion.salida.hora_limpia);
        if (ok) await Swal.fire("Actualización realizada", `Horas ajustadas: <b>${resolucion.entrada.hora_limpia}</b> → <b>${resolucion.salida.hora_limpia}</b>`, "success");
        if (excedente >= 55) await crearRegistroExcedente(datosRegistro, excedente, motivo);
    }

    const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (folioId) window.Reportes.evaluarFolio(folioId);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarTurnoNocturno — turno3 y turno3_12hrs en flujo normal
// ─────────────────────────────────────────────────────────────────────────────
async function procesarTurnoNocturno(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) { mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`); return; }
    const hrsReglMin = horaAMinutos(def.horasReglamentarias);

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr    = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regAyerStr   = checador.regAnterior.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regMañanaStr = checador.regSiguiente.map(r => r.hora_limpia).join(", ") || "ninguno";

    const resolucion = resolverTurnoNocturno(checador.regAnterior, checador.regActual, checador.regSiguiente);

    if (!resolucion || !resolucion.entrada || !resolucion.salida) {
        const detalle = !resolucion ? "Sin registros nocturnos ni diurnos."
            : !resolucion.entrada ? `Salida detectada (<b>${resolucion.salida?.hora_limpia}</b>) pero sin entrada nocturna en ${checador.fechaAnterior}.`
            : `Entrada detectada (<b>${resolucion.entrada?.hora_limpia}</b>) pero sin salida en ${checador.fechaSiguiente}.`;
        mostrarError(`No se pudo determinar entrada/salida del turno nocturno para el día ${date}.<br>${detalle}`,
            { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: regMañanaStr,
              entradaDetectada: resolucion?.entrada?.hora_limpia || "no encontrada",
              salidaDetectada:  resolucion?.salida?.hora_limpia  || "no encontrada" });
        return;
    }

    const entradaMin = horaAMinutos(resolucion.entrada.hora_limpia);
    let   salidaMin  = horaAMinutos(resolucion.salida.hora_limpia);
    if (salidaMin < entradaMin) salidaMin += 1440;
    const minutosTrabajados = salidaMin - entradaMin;
    const excedente = Math.max(0, minutosTrabajados - hrsReglMin);
    const horaFinReal = resolucion.salida.hora_limpia;
    const horaInicioExtra = excedente > 0 ? minutosAHoraConSegundos(((salidaMin - excedente) + 24 * 60) % (24 * 60)) : horaFinReal;

    const diaEntrada = resolucion.escenario.includes("ayer") ? checador.fechaAnterior : checador.fechaActual;
    const diaSalida  = resolucion.escenario.includes("mañana") || resolucion.escenario.includes("siguiente") ? checador.fechaSiguiente : checador.fechaActual;

    if (minutosTrabajados < (hrsReglMin - 5)) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({ title: `${turnoAsignado} — No apto`, html: `Trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero requiere <b>${def.horasReglamentarias}</b>.<br><br>Entrada (${diaEntrada}): <b>${resolucion.entrada.hora_limpia}</b><br>Salida (${diaSalida}): <b>${resolucion.salida.hora_limpia}</b><br>Hoy: <b>${regHoyStr}</b> | Ayer: <b>${regAyerStr}</b> | Mañana: <b>${regMañanaStr}</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "warning" });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) window.Reportes.evaluarFolio(fid); return;
    }
    if (excedente < 55) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({ title: `${turnoAsignado} — No apto (excedente insuficiente)`, html: `Excedente <b>${minutosAHoraConSegundos(excedente)}</b> no alcanza los 55 min mínimos.<br><br>Entrada (${diaEntrada}): <b>${resolucion.entrada.hora_limpia}</b><br>Salida (${diaSalida}): <b>${resolucion.salida.hora_limpia}</b><br>Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>Hoy: <b>${regHoyStr}</b> | Ayer: <b>${regAyerStr}</b> | Mañana: <b>${regMañanaStr}</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "warning" });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) window.Reportes.evaluarFolio(fid); return;
    }

    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({ title: `${turnoAsignado} — Apto`, html: `Reglamentarias: <b>${def.horasReglamentarias}</b><br>Entrada (${diaEntrada}): <b>${resolucion.entrada.hora_limpia}</b><br>Salida (${diaSalida}): <b>${resolucion.salida.hora_limpia}</b><br>Trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> | Excedente: <b>${minutosAHoraConSegundos(excedente)}</b><br>Tipo: <b>${tipoEmpleado}</b><br><br>Inicio T. extra: <b>${horaInicioExtra}</b> | Fin T. extra: <b>${horaFinReal}</b>`, icon: "success" });
    const ok = await ajustarHorasRegistro(folioRegistro, horaInicioExtra, horaFinReal);
    if (ok) await Swal.fire("Actualización realizada", `Horas ajustadas: <b>${horaInicioExtra}</b> → <b>${horaFinReal}</b>`, "success");
    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarAnticipo — razon = "Anticipo"
// ─────────────────────────────────────────────────────────────────────────────
async function procesarAnticipo(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) { mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`); return; }
    const hrsReglMin = horaAMinutos(def.horasReglamentarias);

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    if (checador.regActual.length < 1) { mostrarError(`No hay registros en el checador para el día ${date}.`, { registrosHoy: regHoyStr }); return; }

    const primerReg = checador.regActual[0];
    const ultimoReg = checador.regActual[checador.regActual.length - 1];
    const entradaReal = horaAMinutos(primerReg.hora_limpia);
    let   salidaReal  = horaAMinutos(ultimoReg.hora_limpia);
    if (salidaReal < entradaReal) salidaReal += 1440;

    const totalTrabajadoMin = salidaReal - entradaReal;
    const hrsExtraRealesMin = Math.max(0, totalTrabajadoMin - hrsReglMin);

    console.log(`Anticipo: primer=${primerReg.hora_limpia} último=${ultimoReg.hora_limpia} total=${totalTrabajadoMin}min extraReales=${hrsExtraRealesMin}min`);

    if (totalTrabajadoMin < hrsReglMin) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({ title: "Anticipo — No apto", html: `No completó las reglamentarias.<br><br>Turno: <b>${turnoAsignado}</b> (regl.: <b>${def.horasReglamentarias}</b>)<br>Primer registro (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>Último: <b>${ultimoReg.hora_limpia}</b><br>Total: <b>${minutosAHoraConSegundos(totalTrabajadoMin)}</b><br>Registros hoy: <b>${regHoyStr}</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "warning" });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) window.Reportes.evaluarFolio(fid); return;
    }
    if (hrsExtraRealesMin < 55) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({ title: "Anticipo — No apto (excedente insuficiente)", html: `Excedente real (<b>${minutosAHoraConSegundos(hrsExtraRealesMin)}</b>) no alcanza 55 min mínimos.<br><br>Turno: <b>${turnoAsignado}</b> (regl.: <b>${def.horasReglamentarias}</b>)<br>Primer registro (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>Último: <b>${ultimoReg.hora_limpia}</b><br>Total: <b>${minutosAHoraConSegundos(totalTrabajadoMin)}</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "warning" });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) window.Reportes.evaluarFolio(fid); return;
    }

    // Anticipo: HoraFin = inicio_turno | HoraInicio = inicio_turno - excedente
    const finAnticipo    = def.entrada.ideal;
    const inicioAnticipo = ((finAnticipo - hrsExtraRealesMin) + 24 * 60) % (24 * 60);
    const nuevaHoraInicio = minutosAHoraConSegundos(inicioAnticipo);
    const nuevaHoraFin    = minutosAHoraConSegundos(finAnticipo);

    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({ title: "Anticipo — Apto", html: `Turno: <b>${turnoAsignado}</b> | Regl.: <b>${def.horasReglamentarias}</b><br>Primer registro (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>Último: <b>${ultimoReg.hora_limpia}</b><br>Total: <b>${minutosAHoraConSegundos(totalTrabajadoMin)}</b><br>Hrs anticipo reales: <b>${minutosAHoraConSegundos(hrsExtraRealesMin)}</b><br>Tipo: <b>${tipoEmpleado}</b><br><br>Inicio T. extra: <b>${nuevaHoraInicio}</b> | Fin T. extra: <b>${nuevaHoraFin}</b>`, icon: "success" });
    const ok = await ajustarHorasRegistro(folioRegistro, nuevaHoraInicio, nuevaHoraFin);
    if (ok) await Swal.fire("Actualización realizada", `Horas ajustadas: <b>${nuevaHoraInicio}</b> → <b>${nuevaHoraFin}</b>`, "success");
    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarReingreso — razon = "Reingreso" (antes Apoyo)
// ─────────────────────────────────────────────────────────────────────────────
async function procesarReingreso(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError) {
    const MARGEN = 30;
    const horaInicioRef = datosRegistro?.HoraInicio || null;
    if (!horaInicioRef) { mostrarError("No se encontró la hora de inicio de reingreso en el registro."); return; }

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr    = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regMañanaStr = checador.regSiguiente.map(r => r.hora_limpia).join(", ") || "ninguno";
    const refMin = horaAMinutos(horaInicioRef);

    const registroInicio = checador.regActual.find(r => Math.abs(horaAMinutos(r.hora_limpia) - refMin) <= MARGEN);
    if (!registroInicio) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({ title: "Reingreso — No apto", html: `No se encontró ningún registro dentro de <b>±${MARGEN} minutos</b> de la hora de inicio indicada (<b>${horaInicioRef}</b>).<br><br>Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "warning" });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) window.Reportes.evaluarFolio(fid); return;
    }

    const inicioRealMin = horaAMinutos(registroInicio.hora_limpia);
    const candidatosHoy = checador.regActual.filter(r => horaAMinutos(r.hora_limpia) > inicioRealMin);
    const registroFin   = candidatosHoy.length > 0 ? candidatosHoy[0] : (checador.regSiguiente.length > 0 ? checador.regSiguiente[0] : null);

    if (!registroFin) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({ title: "Reingreso — No apto", html: `Se encontró el inicio (<b>${registroInicio.hora_limpia}</b>) pero no hay ningún registro posterior.<br><br>Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "warning" });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) window.Reportes.evaluarFolio(fid); return;
    }

    const nuevaHoraInicio = registroInicio.hora_limpia;
    const nuevaHoraFin    = registroFin.hora_limpia;
    let finMin = horaAMinutos(nuevaHoraFin);
    if (finMin < inicioRealMin) finMin += 1440;
    const duracion = finMin - inicioRealMin;

    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({ title: "Reingreso — Apto", html: `Inicio encontrado (${checador.fechaActual}): <b>${nuevaHoraInicio}</b><br>Fin encontrado: <b>${nuevaHoraFin}</b><br>Duración: <b>${minutosAHoraConSegundos(duracion)}</b><br>Tipo: <b>${tipoEmpleado}</b><br><br>Las horas se ajustarán con los registros reales.`, icon: "success" });
    const ok = await ajustarHorasRegistro(folioRegistro, nuevaHoraInicio, nuevaHoraFin);
    if (ok) await Swal.fire("Actualización realizada", `Horas ajustadas: <b>${nuevaHoraInicio}</b> → <b>${nuevaHoraFin}</b>`, "success");
    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// CLASE PRINCIPAL
// ─────────────────────────────────────────────────────────────────────────────
class Reportes {
    constructor() { this.data = []; }

    async getinfohoraentradaysalida(noemp, date, folioRegistro, HoraInicio, turnoAsignado, datosRegistro = null) {
        if (!datosRegistro) datosRegistro = this.data.find(e => e.folioRegistro == folioRegistro) || null;

        const motivo  = parseInt(datosRegistro?.motivo ?? 0);
        const rawTipo = datosRegistro?.tipo_empleado;
        let tipoEmpleado;
        if (rawTipo === 1 || rawTipo === "1" || rawTipo === "empleado")            tipoEmpleado = "empleado";
        else if (rawTipo === 0 || rawTipo === "0" || rawTipo === "sindicalizado")  tipoEmpleado = "sindicalizado";
        else tipoEmpleado = "empleado";

        const razonRegistro   = (datosRegistro?.razon || "").toString().trim().toLowerCase();
        const esAnticipo      = razonRegistro === "anticipo";
        const esReingreso     = razonRegistro === "reingreso";
        const esDoblete       = razonRegistro === "doblete";
        const esTurnoNocturno = ["turno3", "turno3_12hrs"].includes(turnoAsignado);
        const esTurnoMixto    = TURNOS_MIXTOS.includes(turnoAsignado);

        console.log(`═══ INICIO validación: folio=${folioRegistro} emp=${noemp} fecha=${date} turno=${turnoAsignado} motivo=${motivo} razon="${razonRegistro}"`);
        console.log(`esAnticipo=${esAnticipo} | esReingreso=${esReingreso} | esDoblete=${esDoblete} | esTurnoNocturno=${esTurnoNocturno}`);

        const lblMensaje = document.getElementById("lblMensaje");
        const mostrarError = (msg, ctx = {}) => {
            if (lblMensaje) { lblMensaje.hidden = false; lblMensaje.className = "alert alert-warning mt-2"; lblMensaje.style.display = "block"; }
            const btn = document.getElementById(`btnEliminar-${folioRegistro}`);
            if (btn) btn.hidden = false;
            const def = CATALOGO_TURNOS[turnoAsignado];
            const lineas = [
                `Turno solicitado: <b>${turnoAsignado}</b>`,
                def ? `Hrs reglamentarias: <b>${def.horasReglamentarias}</b>` : "",
                `Tipo empleado: <b>${tipoEmpleado}</b>`,
                ctx.registrosHoy    ? `Registros hoy: <b>${ctx.registrosHoy}</b>`       : "",
                ctx.registrosAyer   ? `Registros ayer: <b>${ctx.registrosAyer}</b>`     : "",
                ctx.registrosMañana ? `Registros mañana: <b>${ctx.registrosMañana}</b>` : "",
                ctx.entradaDetectada ? `Entrada detectada: <b>${ctx.entradaDetectada}</b>` : "",
                ctx.salidaDetectada  ? `Salida detectada: <b>${ctx.salidaDetectada}</b>`   : "",
            ].filter(Boolean).join("<br>");
            Swal.fire({ title: "Resultados de validación", html: `Se recomienda revisar este registro.<br><br><b>${msg}</b><br><br>── Diagnóstico ──<br>${lineas}`, icon: "info" });
        };

        if (!CATALOGO_TURNOS[turnoAsignado]) { mostrarError(`El turno "<b>${turnoAsignado}</b>" no está reconocido. Comunícate con Nóminas.`); return; }

        // ── Reingreso ─────────────────────────────────────────────────────────
        if (esReingreso) { await procesarReingreso(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError); return; }

        // ── Anticipo ──────────────────────────────────────────────────────────
        if (esAnticipo) { await procesarAnticipo(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError); return; }

        // ── Doblete (hacia adelante) ───────────────────────────────────────────
        if (esDoblete) { await procesarDoblete(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError); return; }

        // ── Auto-aptos ────────────────────────────────────────────────────────
        if (MOTIVOS_AUTO_APTOS.includes(motivo)) {
            window.Reportes.marcarRegistroComoApto(folioRegistro);
            const ok = await actualizarValidado(folioRegistro, null);
            if (ok) { const reg = this.data.find(e => e.folioRegistro == folioRegistro); if (reg) { reg.validado = 1; actualizarFilaDOM(folioRegistro, reg); } await Swal.fire("Validado", `El registro de ${motivo === MOTIVO_HORA_COMIDA ? "hora de comida" : "cambio de horario"} fue marcado como validado.`, "success"); }
            const fid = this.data.find(e => e.folioRegistro == folioRegistro)?.id; if (fid) this.evaluarFolio(fid); return;
        }

        // ── Descanso trabajado / Día festivo ──────────────────────────────────
        if (MOTIVOS_ESPECIALES.includes(motivo)) { await procesarDescansoOFestivo(noemp, date, folioRegistro, turnoAsignado, motivo, tipoEmpleado, datosRegistro, mostrarError); return; }

        // ── Turno nocturno (flujo normal) ─────────────────────────────────────
        if (esTurnoNocturno) { await procesarTurnoNocturno(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError); return; }

        // ── TODOS los demás (turno1, turno2, mixtos) → lógica de lapso ────────
        await procesarPorLapso(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError);
    }

    async consulta() {
        const respuetaraw = await fetch("php/index.php?tblautorizatp");
        const respuesta   = await respuetaraw.json();
        if (Array.isArray(respuesta)) {
            this.data = respuesta;
            let body = ""; const folios = new Set();
            respuesta.forEach(e => { folios.add(e.id); body += this.renderRow(e); });
            document.getElementById("tblenc").innerHTML = body;
            const ac = document.getElementById("accionesGlobales"); ac.innerHTML = "";
            folios.forEach(fid => { ac.innerHTML += `<div class="mb-3" id="acciones-folio-${fid}"><button class="btn btn-success" id="btnAutorizar-${fid}" hidden>Autorizar TODO el Folio ${fid}</button><button class="btn btn-danger" id="btnRechazar-${fid}" hidden>Rechazar TODO el Folio ${fid}</button></div>`; });
            folios.forEach(fid => { document.getElementById(`btnAutorizar-${fid}`)?.addEventListener("click", () => this.enviar(fid, 1)); document.getElementById(`btnRechazar-${fid}`)?.addEventListener("click", () => this.enviar(fid, 2)); });
            const ahora = new Date(); respuesta.forEach(e => this._aplicarEstadoFecha(e, ahora));
        }
    }

    renderRow(elemento) {
        let accionHtml = "", accionTerminadoHtml = "", badgeValidado = "";
        if (elemento.terminado === null || elemento.terminado === "") {
            if (elemento.validado == null || elemento.validado == 0) {
                accionHtml = `<button class="btn btn-sm btn-warning" onclick="validarInfo('${elemento.NoEmpleadoSol}','${elemento.fechaSol}','${elemento.folioRegistro}','${elemento.HoraInicio}','${elemento.turnoAsignado}',${elemento.folioRegistro})" id="btnValidar-${elemento.folioRegistro}"><i class="fa-solid fa-eye"></i> Validar T. extra</button>
                              <button class="btn btn-sm btn-danger" onclick="window.deletesub(${elemento.folioRegistro})" id="btnEliminar-${elemento.folioRegistro}" hidden><i class="fas fa-times"></i> Eliminar</button>`;
            } else { accionHtml = `<span class="badge bg-success">Solicitud validada</span>`; }
            accionTerminadoHtml = `<span class="badge bg-warning">No procesada (Pendiente)</span>`;
        } else if (elemento.terminado == 1) { accionHtml = `<span class="badge bg-success">Aprobado</span>`; accionTerminadoHtml = `<span class="badge bg-success">Solicitud procesada (Aprobado)</span>`; }
        else if (elemento.terminado == 2) { accionHtml = `<span class="badge bg-danger">Rechazado</span>`; accionTerminadoHtml = `<span class="badge bg-danger">Solicitud procesada (Rechazado)</span>`; }
        badgeValidado = elemento.validado == 1 ? `<span class="badge bg-success">Validado</span>` : `<span class="badge bg-secondary">No validado</span>`;
        return `<tr><td>${elemento.folioRegistro}</td><td>${elemento.id}</td><td>${elemento.creado}</td><td>${elemento.NoEmp}</td><td>${elemento.SupervisorNombre}</td><td>${elemento.NoEmpleadoSol}</td><td>${elemento.fechaSol}</td><td>${elemento.HoraInicio}</td><td>${elemento.HoraFin}</td><td>${elemento.NombreEmpleadoSol}</td><td>${elemento.departamento}</td><td>${accionTerminadoHtml}<br></td><td>${badgeValidado}</td><td>${accionHtml}<small id="msg-${elemento.folioRegistro}" class="d-block mt-1"></small></td></tr>`;
    }

    _aplicarEstadoFecha(elemento, ahora) {
        const fechaSol = new Date(elemento.fechaSol + "T00:00:00");
        const [h, m, s] = elemento.HoraFin.split(":").map(Number);
        const horaFinDate = new Date(fechaSol); horaFinDate.setHours(h, m, s);
        const margen = new Date(horaFinDate.getTime() + 5 * 60000);
        const btnValidar = document.getElementById(`btnValidar-${elemento.folioRegistro}`);
        const msg = document.getElementById(`msg-${elemento.folioRegistro}`);
        if (ahora < fechaSol) {
            if (btnValidar) btnValidar.hidden = true;
            if (msg) { msg.textContent = `El día solicitado (${elemento.fechaSol}) aún no ha llegado.`; msg.className = "alert alert-warning p-1 mt-1"; }
        } else if (ahora.toDateString() === fechaSol.toDateString() && ahora < margen) {
            if (btnValidar) btnValidar.hidden = true;
            if (msg) { msg.textContent = `Aún no es momento. Se habilitará a las ${margen.toLocaleTimeString([], {hour:"2-digit",minute:"2-digit"})} hrs.`; msg.className = "alert alert-warning p-1 mt-1"; }
        } else { if (msg) { msg.textContent = ""; msg.dataset.estado = "pendiente"; } }
    }

    filtrarPorFolio(folioId) {
        const filtrados = this.data.filter(e => e.id == folioId);
        let body = ""; filtrados.forEach(e => body += this.renderRow(e));
        document.getElementById("tblenc").innerHTML = body;
        document.getElementById("accionesGlobales").innerHTML = `<div class="mb-3" id="acciones-folio-${folioId}"><button class="btn btn-success" id="btnAutorizar-${folioId}" hidden>Autorizar TODO el Folio ${folioId}</button><button class="btn btn-danger" id="btnRechazar-${folioId}" hidden>Rechazar TODO el Folio ${folioId}</button></div>`;
        document.getElementById(`btnAutorizar-${folioId}`)?.addEventListener("click", () => this.enviar(folioId, 1));
        document.getElementById(`btnRechazar-${folioId}`)?.addEventListener("click",  () => this.enviar(folioId, 2));
        const ahora = new Date(); filtrados.forEach(e => this._aplicarEstadoFecha(e, ahora));
        this.evaluarFolio(folioId);
    }

    async deletesub(id) {
        const data = new FormData(); data.append("id", id);
        const resp = await fetch("./php/index.php?deletesub", { method: "POST", body: data }).then(r => r.json());
        this.consulta();
        resp === "Listo" ? Swal.fire("Listo!!!", "Registro eliminado", "success") : Swal.fire("ERROR!!!", "Hay un problema al eliminar", "error");
    }

    async enviar(id, autor) {
        const verif = await fetch("./php/verificar_firma.php").then(r => r.json()).catch(() => null);
        if (!verif?.success) { Swal.fire({ icon: "warning", title: "Firma no registrada", text: "Debes registrar tu firma primero.", confirmButtonText: "Entendido", confirmButtonColor: "#f0ad4e" }); return; }
        const resp = await fetch(`./php/index.php?autorizafol&id=${id}&autor=${autor}`).then(r => r.json());
        resp === false ? Swal.fire({ icon: "error", title: "Error", text: resp.message || "Error con la base de datos." }) : Swal.fire({ icon: "success", title: "Autorizado", text: "Autorizado con éxito", timer: 2000, showConfirmButton: false });
        window.open(`./pdf/reporte.php?folio=${btoa(id)}&true=1trlist`);
        window.location.reload();
    }

    pdffin(id) { window.open(`./pdf/reporte.php?folio=${btoa(id)}&true=1trlist`); }

    marcarRegistroComoApto(folioRegistro) {
        const btn = document.getElementById(`btnValidar-${folioRegistro}`), msg = document.getElementById(`msg-${folioRegistro}`);
        if (btn) btn.hidden = true;
        if (msg) { msg.textContent = "Registro apto para tiempo extra."; msg.className = "alert alert-success p-1 mt-1"; msg.dataset.estado = "apto"; }
    }

    marcarRegistroComoNoApto(folioRegistro) {
        const btn = document.getElementById(`btnValidar-${folioRegistro}`), be = document.getElementById(`btnEliminar-${folioRegistro}`), msg = document.getElementById(`msg-${folioRegistro}`);
        if (btn) btn.hidden = true; if (be) be.hidden = false;
        if (msg) { msg.textContent = "Registro no apto, puede eliminarse."; msg.className = "alert alert-warning p-1 mt-1"; msg.dataset.estado = "noapto"; }
    }

    evaluarFolio(folioId) {
        const registros = this.data.filter(e => e.id == folioId);
        let todosAnalizados = true, todosAptos = true;
        registros.forEach(e => {
            const estado = document.getElementById(`msg-${e.folioRegistro}`)?.dataset.estado;
            if (estado !== "apto" && estado !== "noapto") todosAnalizados = false;
            if (estado === "noapto") todosAptos = false;
        });
        const bA = document.getElementById(`btnAutorizar-${folioId}`), bR = document.getElementById(`btnRechazar-${folioId}`);
        if (todosAnalizados && todosAptos) { if (bA) bA.hidden = false; if (bR) bR.hidden = false; }
        else { if (bA) bA.hidden = true; if (bR) bR.hidden = true; }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// INSTANCIA GLOBAL
// ─────────────────────────────────────────────────────────────────────────────
window.Reportes = new Reportes();
window.Reportes.consulta();

window.validarInfo = function(noEmpSol, fechaSol, folioRegistro, HoraInicio, turnoAsignado, folioRegistroId) {
    const datosRegistro = window.Reportes.data.find(e => e.folioRegistro == (folioRegistroId ?? folioRegistro)) || null;
    window.Reportes.getinfohoraentradaysalida(noEmpSol, fechaSol, folioRegistro, HoraInicio, turnoAsignado, datosRegistro);
};
window.deletesub = id => window.Reportes.deletesub(id);
window.Autoriza  = id => window.Reportes.enviar(id, 1);
window.Rechazar  = id => window.Reportes.enviar(id, 2);
window.pdfFin    = id => window.Reportes.pdffin(id);

// ── Buscador ──────────────────────────────────────────────────────────────────
let infoPlanEntregados = [];
document.getElementById("filtroGlobal").addEventListener("keyup", (e) => {
    e.preventDefault();
    clearTimeout(e.target._searchTimer);
    e.target._searchTimer = setTimeout(() => mostrarTabla(), 250);
});

async function obtenerDatosArray() {
    try {
        const axiosResponse = await axios.post("php/index.php?tblautorizatp");
        infoPlanEntregados = axiosResponse.data.map(item => ({ ...item }));
        if (axiosResponse.status === 200) mostrarTabla();
    } catch (error) { Swal.fire("Error", "Hay un problema con la base de datos", "error"); }
}
obtenerDatosArray();

function mostrarTabla(query = document.getElementById("filtroGlobal").value) {
    const tbody = document.getElementById("tblenc");
    tbody.innerHTML = "";
    const q = (query || "").toString().trim().toLowerCase();
    const datosFiltrados = q
        ? infoPlanEntregados.filter(item =>
            [item.folioRegistro, item.id, item.fecha, item.departamento, item.creado, item.terminado, item.autorizado,
             item.NoEmp, item.SupervisorNombre, item.noempautoriza, item.fechaSol, item.NoEmpleadoSol,
             item.HoraInicio, item.HoraFin, item.NombreEmpleadoSol, item.validado, item.motivo, item.turnoAsignado, item.razon]
            .some(v => v && v.toString().toLowerCase().includes(q)))
        : infoPlanEntregados.slice();

    if (!datosFiltrados.length) { tbody.innerHTML = `<tr><td colspan="14" class="text-center">No hay registros que coincidan</td></tr>`; }
    else {
        let body = "";
        datosFiltrados.forEach(e => { body += window.Reportes.renderRow(e); });
        tbody.innerHTML = body;
    }
    const ahora = new Date(); datosFiltrados.forEach(e => window.Reportes._aplicarEstadoFecha(e, ahora));
}

// ── Tutorial ──────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    const driver = window.driver.js.driver;
    const steps = [
        { element: ".tittlecont",                  popover: { title: "Validación de solicitudes", description: "Aquí podrás validar las solicitudes de tiempo extra antes de enviarlas a Gerencia.", side: "bottom" } },
        { element: "#filtroGlobal",                popover: { title: "Filtro global",             description: "Busca solicitudes por cualquier dato.", side: "top", popoverClass: "popover-importante" } },
        { element: "table thead th:nth-child(12)", popover: { title: "Estatus",                   description: "Indica si fue aprobada o rechazada por Gerencia.", side: "top", popoverClass: "popover-importante" } },
        { element: "table thead th:nth-child(13)", popover: { title: "Validado",                  description: "Indica si ya fue validada.", side: "top", popoverClass: "popover-importante" } },
        { element: "table thead th:nth-child(14)", popover: { title: "Acciones",                  description: "Botones para validar individualmente.", side: "top", popoverClass: "popover-importante" } },
        { element: "#btnAyuda",                    popover: { title: "Tutorial",                  description: "Presiona para repetir el tutorial.", side: "bottom" } }
    ];
    const driverObj = driver({ showProgress: true, allowClose: false, disableInteraction: true, progressText: "Paso {{current}} de {{total}}", doneBtnText: "Finalizar", nextBtnText: "Siguiente", prevBtnText: "Atrás", steps });
    const tk = "tutorial_validacionTE";
    if (!localStorage.getItem(tk)) { driverObj.drive(); localStorage.setItem(tk, "true"); }
    document.getElementById("btnAyuda")?.addEventListener("click", () => driverObj.drive());
});