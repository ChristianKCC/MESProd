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
    turno1: {
        nombre: "Turno 1 (07:00 - 15:00)",
        entrada: { ideal: 7 * 60, tolerancia: 35 },
        salida:  { ideal: 15 * 60, tolerancia: 60 },
        cruzaMedia: false,
        horasReglamentarias: "08:00:00",
        sabado: null
    },
    turno2: {
        nombre: "Turno 2 (15:00 - 22:30)",
        entrada: { ideal: 15 * 60, tolerancia: 35 },
        salida:  { ideal: 22 * 60 + 30, tolerancia: 60 },
        cruzaMedia: false,
        horasReglamentarias: "07:30:00",
        sabado: null
    },
    turno2_13hrs: {
        nombre: "Turno 2 13hrs (10:30 - 22:30)",
        entrada: { ideal: 10 * 60 + 30, tolerancia: 20 },
        salida:  { ideal: 22 * 60 + 30, tolerancia: 60 },
        cruzaMedia: false,
        horasReglamentarias: "04:30:00",
        sabado: null
    },
    turno3: {
        nombre: "Turno 3 (22:30 - 07:00)",
        entrada: { ideal: 22 * 60 + 30, tolerancia: 35 },
        salida:  { ideal: 7 * 60, tolerancia: 35 },
        cruzaMedia: true,
        horasReglamentarias: "08:30:00",
        sabado: null
    },
    turno3_12hrs: {
        nombre: "Turno 3 12hrs (19:00 - 07:00)",
        entrada: { ideal: 19 * 60, tolerancia: 20 },
        salida:  { ideal: 7 * 60, tolerancia: 20 },
        cruzaMedia: true,
        horasReglamentarias: "08:30:00",
        sabado: null
    },
    mixto1: {
        nombre: "Mixto 1 (07:30 - 17:00)",
        entrada: { ideal: 7 * 60 + 30, tolerancia: 35 },
        salida:  { ideal: 17 * 60, tolerancia: 60 },
        cruzaMedia: false,
        horasReglamentarias: "10:00:00",
        sabado: { nombre: "Mixto 1 Sábado (07:30 - 12:30)", salida: { ideal: 12 * 60 + 30, tolerancia: 60 }, horasReglamentarias: "05:00:00" }
    },
    mixto2: {
        nombre: "Mixto 2 (08:30 - 18:30)",
        entrada: { ideal: 8 * 60 + 30, tolerancia: 35 },
        salida:  { ideal: 18 * 60 + 30, tolerancia: 60 },
        cruzaMedia: false,
        horasReglamentarias: "10:00:00",
        sabado: { nombre: "Mixto 2 Sábado (08:30 - 13:30)", salida: { ideal: 13 * 60 + 30, tolerancia: 60 }, horasReglamentarias: "05:00:00" }
    },
    mixto3: {
        nombre: "Mixto 3 (07:00 - 16:30)",
        entrada: { ideal: 7 * 60, tolerancia: 35 },
        salida:  { ideal: 16 * 60 + 30, tolerancia: 60 },
        cruzaMedia: false,
        horasReglamentarias: "09:30:00",
        sabado: { nombre: "Mixto 3 Sábado (07:00 - 12:00)", salida: { ideal: 12 * 60, tolerancia: 60 }, horasReglamentarias: "05:00:00" }
    },
    mixto4: {
        nombre: "Mixto 4 (07:00 - 17:00)",
        entrada: { ideal: 7 * 60, tolerancia: 15 },
        salida:  { ideal: 17 * 60, tolerancia: 60 },
        cruzaMedia: false,
        horasReglamentarias: "10:00:00",
        sabado: { nombre: "Mixto 4 Sábado (07:00 - 12:00)", salida: { ideal: 12 * 60, tolerancia: 60 }, horasReglamentarias: "05:00:00" }
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
function limpiarRegistros(registros) {
    const vistos = new Set();
    return registros
        .filter(r => r.fecha_h)
        .map(r => ({ ...r, hora_limpia: r.fecha_h.substring(0, 8) }))
        .filter(r => {
            const clave = r.hora_limpia.substring(0, 5);
            if (vistos.has(clave)) return false;
            vistos.add(clave);
            return true;
        })
        .sort((a, b) => horaAMinutos(a.hora_limpia) - horaAMinutos(b.hora_limpia));
}

// ─────────────────────────────────────────────────────────────────────────────
// resolverEntradaSalida — solo para turnos NO nocturnos y flujo normal
// ─────────────────────────────────────────────────────────────────────────────
function resolverEntradaSalida(turnoAsignado, regAnterior, regActual, regSiguiente, esSabado, horaFinRegistro = null) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) return null;

    const salidaDef  = (esSabado && def.sabado) ? def.sabado.salida : def.salida;
    const entradaDef = def.entrada;

    const buscarMasCercano = (registros, ideal, tolerancia) => {
        let mejor = null, menorDiff = Infinity;
        for (const r of registros) {
            const diff = Math.abs(horaAMinutos(r.hora_limpia) - ideal);
            if (diff <= tolerancia && diff < menorDiff) { menorDiff = diff; mejor = r; }
        }
        return mejor;
    };

    // Turno nocturno (cruza medianoche) — ya NO se usa esta rama para turno3
    // Se maneja por resolverTurnoNocturno aparte
    if (def.cruzaMedia) {
        const entradaHoy = buscarMasCercano(regActual, entradaDef.ideal, entradaDef.tolerancia);
        if (entradaHoy) {
            const salidaMañana = buscarMasCercano(regSiguiente, salidaDef.ideal, salidaDef.tolerancia);
            if (salidaMañana) return { entrada: entradaHoy, salida: salidaMañana, escenario: "inicio_turno" };
            return { entrada: entradaHoy, salida: null, escenario: "sin_salida" };
        }
        const salidaHoy = buscarMasCercano(regActual, salidaDef.ideal, salidaDef.tolerancia);
        if (salidaHoy) {
            const entradaAyer = buscarMasCercano(regAnterior, entradaDef.ideal, entradaDef.tolerancia);
            if (entradaAyer) return { entrada: entradaAyer, salida: salidaHoy, escenario: "fin_turno" };
        }
        return null;
    }

    const entrada = buscarMasCercano(regActual, entradaDef.ideal, entradaDef.tolerancia);
    if (!entrada) return null;
    const entradaMin = horaAMinutos(entrada.hora_limpia);

    const salidaV1 = buscarMasCercano(regActual, salidaDef.ideal, salidaDef.tolerancia);
    if (salidaV1 && horaAMinutos(salidaV1.hora_limpia) > entradaMin) {
        return { entrada, salida: salidaV1, escenario: "mismo_dia_normal" };
    }

    if (horaFinRegistro) {
        const salidaV2 = buscarMasCercano(regActual, horaAMinutos(horaFinRegistro), 60);
        if (salidaV2 && horaAMinutos(salidaV2.hora_limpia) > entradaMin) {
            return { entrada, salida: salidaV2, escenario: "mismo_dia_con_extra" };
        }
        const candidatos = regActual.filter(r => horaAMinutos(r.hora_limpia) > entradaMin);
        if (candidatos.length > 0) return { entrada, salida: candidatos[candidatos.length - 1], escenario: "mismo_dia_ultimo_registro" };
    }

    return { entrada, salida: null, escenario: "sin_salida" };
}

// ─────────────────────────────────────────────────────────────────────────────
// resolverTurnoNocturno — lógica por LAPSO para turno3 y turno3_12hrs
//
// No buscamos por ventana de hora ideal. En cambio:
//   1. Tomamos el registro más bajo en horas del día actual como candidato a "salida" (ej. 08:08)
//      y el más alto como "entrada" (ej. 22:29).
//   2. Si hay registros en el día siguiente, el primero es candidato a "salida" del turno.
//   3. Combinamos entrada (del día actual, parte nocturna) + salida (primer registro del día siguiente).
//   4. Si no hay registros del día siguiente, salida = null.
//   5. Si solo hay registros bajos en el día actual (todos < 15:00), asumimos que son la salida
//      y la entrada estuvo en el día anterior.
// ─────────────────────────────────────────────────────────────────────────────
function resolverTurnoNocturno(regAnterior, regActual, regSiguiente) {
    // Separar registros del día actual en "nocturnos" (≥ 18:00) y "diurnos" (< 12:00)
    const nocturnos = regActual.filter(r => horaAMinutos(r.hora_limpia) >= 18 * 60);
    const diurnos   = regActual.filter(r => horaAMinutos(r.hora_limpia) <  12 * 60);

    console.log(`[TurnoNocturno] Nocturnos hoy (>=18h): ${nocturnos.map(r=>r.hora_limpia).join(", ") || "ninguno"}`);
    console.log(`[TurnoNocturno] Diurnos hoy (<12h): ${diurnos.map(r=>r.hora_limpia).join(", ") || "ninguno"}`);
    console.log(`[TurnoNocturno] Registros mañana: ${regSiguiente.map(r=>r.hora_limpia).join(", ") || "ninguno"}`);
    console.log(`[TurnoNocturno] Registros ayer: ${regAnterior.map(r=>r.hora_limpia).join(", ") || "ninguno"}`);

    // CASO A: hay entrada nocturna hoy → salida en el día siguiente
    if (nocturnos.length > 0) {
        const entrada = nocturnos[nocturnos.length - 1]; // última hora nocturna del día = entrada del turno
        if (regSiguiente.length > 0) {
            // Salida = primer registro del día siguiente con hora < 15:00
            const salidaMañana = regSiguiente.find(r => horaAMinutos(r.hora_limpia) < 15 * 60) || regSiguiente[0];
            console.log(`[TurnoNocturno] CASO A: entrada=${entrada.hora_limpia} | salida mañana=${salidaMañana.hora_limpia}`);
            return { entrada, salida: salidaMañana, escenario: "nocturno_entrada_hoy_salida_mañana" };
        }
        console.log(`[TurnoNocturno] CASO A sin salida: entrada=${entrada.hora_limpia} | sin registros mañana`);
        return { entrada, salida: null, escenario: "nocturno_sin_salida" };
    }

    // CASO B: solo hay registros diurnos hoy → son la salida de un turno que inició ayer
    if (diurnos.length > 0) {
        const salida = diurnos[diurnos.length - 1]; // último diurno = salida del turno
        // Buscar entrada en el día anterior (registros >= 18:00 de ayer)
        const nocturnosAyer = regAnterior.filter(r => horaAMinutos(r.hora_limpia) >= 18 * 60);
        if (nocturnosAyer.length > 0) {
            const entrada = nocturnosAyer[nocturnosAyer.length - 1];
            console.log(`[TurnoNocturno] CASO B: entrada ayer=${entrada.hora_limpia} | salida hoy=${salida.hora_limpia}`);
            return { entrada, salida, escenario: "nocturno_entrada_ayer_salida_hoy" };
        }
        // No encontramos entrada ayer pero sí la salida hoy
        console.log(`[TurnoNocturno] CASO B sin entrada: salida=${salida.hora_limpia} | sin nocturno en ayer`);
        return { entrada: null, salida, escenario: "nocturno_sin_entrada" };
    }

    // CASO C: no hay nada claro
    console.log(`[TurnoNocturno] CASO C: sin registros útiles`);
    return null;
}

// ─────────────────────────────────────────────────────────────────────────────
// calcularTurnoYHoras
// ─────────────────────────────────────────────────────────────────────────────
function calcularTurnoYHoras(horaEntrada, horaSalida, esSabado = false, turnoAsignado = null) {
    const entradaMin = horaAMinutos(horaEntrada);
    let salidaMin    = horaAMinutos(horaSalida);
    if (salidaMin < entradaMin) salidaMin += 1440;

    const def = turnoAsignado ? CATALOGO_TURNOS[turnoAsignado] : null;
    let nombreTurno, horaInicioTurno, horaFinTurno, horasReglamentarias, finTurnoMin;

    if (def) {
        const salidaDef = (esSabado && def.sabado) ? def.sabado.salida : def.salida;
        const nomDef    = (esSabado && def.sabado) ? def.sabado.nombre : def.nombre;
        const hrsDef    = (esSabado && def.sabado) ? def.sabado.horasReglamentarias : def.horasReglamentarias;
        nombreTurno     = nomDef;
        horaInicioTurno = minutosAHora(def.entrada.ideal);
        horaFinTurno    = minutosAHora(salidaDef.ideal);
        horasReglamentarias = hrsDef;
        if (turnoAsignado === "turno2_13hrs") { finTurnoMin = 15 * 60; }
        else if (def.cruzaMedia) { finTurnoMin = salidaDef.ideal + 1440; }
        else { finTurnoMin = salidaDef.ideal; }
    } else {
        nombreTurno = "Desconocido";
        horaInicioTurno = minutosAHora(entradaMin);
        horaFinTurno    = minutosAHora(salidaMin);
        horasReglamentarias = "00:00:00";
        finTurnoMin = salidaMin;
    }

    const minutosTrabajados = salidaMin - entradaMin;
    const horasExtrasMin    = Math.max(0, salidaMin - finTurnoMin);
    return {
        turno: nombreTurno, horaInicioTurno, horaFinTurno,
        horasExtras: horasExtrasMin > 0 ? minutosAHoraConSegundos(horasExtrasMin) : "00:00:00",
        totalHoras: minutosAHoraConSegundos(minutosTrabajados),
        horasReglamentarias, salidaMin, entradaMin, finTurnoMin
    };
}

// ─────────────────────────────────────────────────────────────────────────────
// fetchChecador — descarga registros de 3 días
// ─────────────────────────────────────────────────────────────────────────────
async function fetchChecador(noemp, date) {
    const fa = new Date(date + "T00:00:00"); fa.setDate(fa.getDate() - 1);
    const fs = new Date(date + "T00:00:00"); fs.setDate(fs.getDate() + 1);
    const fmt = f => `${f.getFullYear()}-${String(f.getMonth()+1).padStart(2,"0")}-${String(f.getDate()).padStart(2,"0")}`;

    const [rawAnterior, rawActual, rawSiguiente] = await Promise.all([
        fetch(`../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${fmt(fa)}`).then(r => r.json()),
        fetch(`../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${date}`).then(r => r.json()),
        fetch(`../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${fmt(fs)}`).then(r => r.json())
    ]);

    return {
        regAnterior:  limpiarRegistros(rawAnterior),
        regActual:    limpiarRegistros(rawActual),
        regSiguiente: limpiarRegistros(rawSiguiente),
        rawActual,
        fechaAnterior: fmt(fa),
        fechaActual:   date,
        fechaSiguiente: fmt(fs)
    };
}

// ─────────────────────────────────────────────────────────────────────────────
// actualizarValidado
// ─────────────────────────────────────────────────────────────────────────────
async function actualizarValidado(folioRegistro, horaFinNueva) {
    const data = new FormData();
    data.append("folioRegistro", folioRegistro);
    if (horaFinNueva) data.append("nuevaHoraFin", horaFinNueva);
    const endpoint = horaFinNueva ? "actualizarHoraFin" : "actualizarEstadoValidado";
    try {
        const r    = await fetch(`php/index.php?${endpoint}`, { method: "POST", body: data });
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
    const estatusCell  = fila.querySelector("td:nth-child(13)");
    const accionesCell = fila.querySelector("td:nth-child(14)");
    if (estatusCell) {
        estatusCell.innerHTML = registro?.validado == 1
            ? `<span class="badge bg-success">Validado</span>`
            : `<span class="badge bg-warning">No validado aún</span>`;
    }
    if (accionesCell) {
        accionesCell.innerHTML = registro?.validado == 1
            ? `<span class="badge bg-success">Ya validado</span>
               <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`
            : `<button class="btn btn-sm btn-warning"
                       onclick="validarInfo('${registro.NoEmpleadoSol}','${registro.fechaSol}','${registro.folioRegistro}','${registro.HoraInicio}','${registro.turnoAsignado}',${registro.folioRegistro})"
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

// ─────────────────────────────────────────────────────────────────────────────
// crearRegistroExcedente — SOLO para descanso trabajado y día festivo
// ─────────────────────────────────────────────────────────────────────────────
async function crearRegistroExcedente(datosRegistro, excedenteMin, motivo) {
    const def       = CATALOGO_TURNOS[datosRegistro.turnoAsignado];
    const esSabado  = new Date(datosRegistro.fechaSol + "T00:00:00").getDay() === 6;
    const salidaDef = (esSabado && def?.sabado) ? def.sabado.salida : def?.salida;
    const finMin    = salidaDef ? salidaDef.ideal : 0;

    const data = new FormData();
    data.append("noemp",       datosRegistro.NoEmpleadoSol);
    data.append("fechainput",  datosRegistro.fechaSol);
    data.append("horai",       minutosAHoraConSegundos(finMin));
    data.append("horaf",       minutosAHoraConSegundos((finMin + excedenteMin) % (24 * 60)));
    data.append("maquina",     datosRegistro.maquina || "");
    data.append("motivos",     motivo);
    data.append("razon",       datosRegistro.razon || "");  // conserva la razón original
    data.append("folio",       datosRegistro.id);
    data.append("turnosel",    datosRegistro.turnoAsignado);
    data.append("nombre",      datosRegistro.NombreEmpleadoSol);
    data.append("esExcedente", "1");
    data.append("validado",    "1");  // ya validado de entrada

    try {
        const r    = await fetch("php/index.php?guardartiempoextra", { method: "POST", body: data });
        const resp = await r.json();
        if (resp === "Listo") {
            await Swal.fire("Excedente registrado",
                `Se creó un registro adicional de <b>${minutosAHoraConSegundos(excedenteMin)}</b> hrs como tiempo extra.<br>
                 Motivo: <b>${motivo === MOTIVO_DESCANSO_TRABAJADO ? "Descanso trabajado" : "Día festivo"}</b>`,
                "success");
        } else {
            Swal.fire("Advertencia", `No se pudo crear el registro de excedente: ${JSON.stringify(resp)}`, "warning");
        }
    } catch (err) { console.error("Error crearRegistroExcedente:", err); }
}

// ─────────────────────────────────────────────────────────────────────────────
// ajustarHorasRegistro — actualiza HoraInicio y HoraFin en BD sin crear un nuevo registro
// ─────────────────────────────────────────────────────────────────────────────
async function ajustarHorasRegistro(folioRegistro, nuevaHoraInicio, nuevaHoraFin) {
    const dataAjuste = new FormData();
    dataAjuste.append("folioRegistro",   folioRegistro);
    dataAjuste.append("nuevaHoraFin",    nuevaHoraFin);
    dataAjuste.append("nuevaHoraInicio", nuevaHoraInicio);

    try {
        const rAjuste    = await fetch("php/index.php?actualizarHorasReales", { method: "POST", body: dataAjuste });
        const respAjuste = await rAjuste.json();
        if (!respAjuste.success) {
            console.warn("No se pudieron ajustar las horas:", respAjuste.error);
            // Fallback: al menos marcar como validado
            await actualizarValidado(folioRegistro, nuevaHoraFin);
            return false;
        }
        const reg = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
        if (reg) { reg.HoraInicio = nuevaHoraInicio; reg.HoraFin = nuevaHoraFin; reg.validado = 1; }
        actualizarFilaDOM(folioRegistro, reg);
        return true;
    } catch (err) {
        console.error("Error ajustarHorasRegistro:", err);
        await actualizarValidado(folioRegistro, nuevaHoraFin);
        return false;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarDescansoOFestivo
// ─────────────────────────────────────────────────────────────────────────────
async function procesarDescansoOFestivo(
    noemp, date, folioRegistro, turnoAsignado,
    motivo, tipoEmpleado, datosRegistro, mostrarError
) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) { mostrarError("Turno asignado no reconocido en el catálogo."); return; }

    const hrsReglMin = horaAMinutos(def.horasReglamentarias);
    const esSabado   = new Date(date + "T00:00:00").getDay() === 6;

    console.log("═══════════ DIAGNÓSTICO procesarDescansoOFestivo ═══════════");
    console.log(`Folio: ${folioRegistro} | Empleado: ${noemp} | Fecha: ${date}`);
    console.log(`Turno: ${turnoAsignado} | Hrs regl.: ${def.horasReglamentarias} | Motivo: ${motivo}`);

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr      = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regAyerStr     = checador.regAnterior.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regMañanaStr   = checador.regSiguiente.map(r => r.hora_limpia).join(", ") || "ninguno";

    console.log(`Ayer (${checador.fechaAnterior}): ${regAyerStr}`);
    console.log(`Hoy  (${checador.fechaActual}): ${regHoyStr}`);
    console.log(`Mañana (${checador.fechaSiguiente}): ${regMañanaStr}`);

    if (!checador.rawActual.length) {
        mostrarError(
            `No hay registros en el checador para el día ${date}.`,
            { registrosHoy: "ninguno", registrosAyer: regAyerStr, registrosMañana: regMañanaStr }
        );
        return;
    }

    const horaFinRegistro = datosRegistro?.HoraFin || null;
    const esMixtoFestivo  = motivo === MOTIVO_DIA_FESTIVO && TURNOS_MIXTOS.includes(turnoAsignado);

    let resolucion;

    if (esMixtoFestivo) {
        if (checador.regActual.length < 2) {
            mostrarError(
                `Turno mixto en día festivo: se necesitan al menos 2 registros en el checador del día ${date}.`,
                { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: regMañanaStr }
            );
            return;
        }
        resolucion = {
            entrada:  checador.regActual[0],
            salida:   checador.regActual[checador.regActual.length - 1],
            escenario: "mixto_festivo_directo"
        };
    } else if (def.cruzaMedia) {
        // Turno nocturno — usar resolverTurnoNocturno
        resolucion = resolverTurnoNocturno(checador.regAnterior, checador.regActual, checador.regSiguiente);
        if (!resolucion || !resolucion.entrada || !resolucion.salida) {
            mostrarError(
                `No se pudo determinar la entrada y salida del turno nocturno para el día ${date}.`,
                { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: regMañanaStr,
                  entradaDetectada: resolucion?.entrada?.hora_limpia || "no encontrada",
                  salidaDetectada:  resolucion?.salida?.hora_limpia  || "no encontrada" }
            );
            return;
        }
    } else {
        resolucion = resolverEntradaSalida(
            turnoAsignado, checador.regAnterior, checador.regActual, checador.regSiguiente,
            esSabado, horaFinRegistro
        );
        if (!resolucion || !resolucion.salida) {
            mostrarError(
                `No se encontraron registros de entrada/salida coherentes con el turno ${turnoAsignado} para el día ${date}.`,
                { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: regMañanaStr,
                  entradaDetectada: resolucion?.entrada?.hora_limpia || "no encontrada",
                  salidaDetectada:  "no encontrada" }
            );
            return;
        }
    }

    const entradaMin = horaAMinutos(resolucion.entrada.hora_limpia);
    let   salidaMin  = horaAMinutos(resolucion.salida.hora_limpia);
    if (salidaMin < entradaMin) salidaMin += 1440;
    const minutosTrabajados = salidaMin - entradaMin;

    console.log(`Entrada: ${resolucion.entrada.hora_limpia} | Salida: ${resolucion.salida.hora_limpia}`);
    console.log(`Trabajados: ${minutosTrabajados} min (${minutosAHoraConSegundos(minutosTrabajados)})`);
    console.log("════════════════════════════════════════════════════════════");

    if (motivo === MOTIVO_DESCANSO_TRABAJADO) {
        const margenMin = 5;
        const cumpleHrs = minutosTrabajados >= (hrsReglMin - margenMin);

        if (!cumpleHrs) {
            window.Reportes.marcarRegistroComoNoApto(folioRegistro);
            await Swal.fire({
                title: "Descanso trabajado — No apto",
                html:  `El empleado trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero el turno requiere <b>${def.horasReglamentarias}</b>.<br><br>
                        Turno: <b>${turnoAsignado}</b><br>
                        Entrada detectada (${checador.fechaActual} o anterior): <b>${resolucion.entrada.hora_limpia}</b><br>
                        Salida detectada: <b>${resolucion.salida.hora_limpia}</b><br>
                        Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>
                        Registros ayer (${checador.fechaAnterior}): <b>${regAyerStr}</b><br>
                        Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>
                        Tipo: <b>${tipoEmpleado}</b>`,
                icon: "warning"
            });
            return;
        }

        const excedente = Math.max(0, minutosTrabajados - hrsReglMin);
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        await Swal.fire({
            title: "Descanso trabajado — Apto",
            html:  `Turno: <b>${turnoAsignado}</b><br>
                    Horas reglamentarias: <b>${def.horasReglamentarias}</b><br>
                    Entrada detectada: <b>${resolucion.entrada.hora_limpia}</b><br>
                    Salida detectada: <b>${resolucion.salida.hora_limpia}</b><br>
                    Horas trabajadas: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                    Tipo: <b>${tipoEmpleado}</b><br>
                    ${excedente >= 55 ? `Excedente: <b>${minutosAHoraConSegundos(excedente)}</b> → se creará registro adicional.` : "Sin excedente significativo."}`,
            icon: "success"
        });

        const ok = await actualizarValidado(folioRegistro, null);
        if (ok) {
            const reg = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
            if (reg) { reg.validado = 1; actualizarFilaDOM(folioRegistro, reg); }
            await Swal.fire("Actualización realizada", "El registro quedó marcado como validado.", "success");
        }
        if (excedente >= 55) await crearRegistroExcedente(datosRegistro, excedente, motivo);

    } else if (motivo === MOTIVO_DIA_FESTIVO) {
        const horaInicioReal = resolucion.entrada.hora_limpia;
        const horaFinReal    = resolucion.salida.hora_limpia;
        const hrsParaFestivo = Math.min(minutosTrabajados, hrsReglMin);
        const excedente      = Math.max(0, minutosTrabajados - hrsReglMin);

        window.Reportes.marcarRegistroComoApto(folioRegistro);
        await Swal.fire({
            title: "Día festivo — Apto",
            html:  `Turno: <b>${turnoAsignado}</b>${esMixtoFestivo ? " <small>(turno mixto)</small>" : ""}<br>
                    Horas reglamentarias: <b>${def.horasReglamentarias}</b><br>
                    Entrada detectada (${checador.fechaActual} o anterior): <b>${horaInicioReal}</b><br>
                    Salida detectada: <b>${horaFinReal}</b><br>
                    Horas trabajadas: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                    Horas reconocidas: <b>${minutosAHoraConSegundos(hrsParaFestivo)}</b><br>
                    Tipo: <b>${tipoEmpleado}</b><br>
                    ${excedente >= 55 ? `Excedente: <b>${minutosAHoraConSegundos(excedente)}</b> → se creará registro adicional.` : ""}`,
            icon: "success"
        });

        const ok = await ajustarHorasRegistro(folioRegistro, horaInicioReal, horaFinReal);
        if (ok) {
            await Swal.fire("Actualización realizada",
                `Registro validado.<br>Horas ajustadas: <b>${horaInicioReal}</b> → <b>${horaFinReal}</b>`, "success");
        }
        if (excedente >= 55) await crearRegistroExcedente(datosRegistro, excedente, motivo);
    }

    const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (folioId) window.Reportes.evaluarFolio(folioId);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarMixtoEspecial
//
// Para motivos 1-7, 11 con turno mixto:
//   - Tomar primer y último registro del día (sin validar ventana de turno).
//   - Verificar que minutosTrabajados >= hrsReglamentarias (margen 5 min).
//   - Si cumple → apto. Ajustar horas del registro original:
//       HoraFin   = hora real del último registro
//       HoraInicio = HoraFin - excedente  (tramo del tiempo extra real)
//   - NO se crea ningún registro adicional para mixtos (solo para festivos/descansos).
// ─────────────────────────────────────────────────────────────────────────────
async function procesarMixtoEspecial(
    noemp, date, folioRegistro, turnoAsignado,
    motivo, tipoEmpleado, datosRegistro, mostrarError
) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) { mostrarError("Turno asignado no reconocido en el catálogo."); return; }

    const esSabado        = new Date(date + "T00:00:00").getDay() === 6;
    const hrsReglDef      = (esSabado && def.sabado) ? def.sabado.horasReglamentarias : def.horasReglamentarias;
    const hrsReglMinFinal = horaAMinutos(hrsReglDef);

    console.log("═══════════ DIAGNÓSTICO procesarMixtoEspecial ═══════════");
    console.log(`Folio: ${folioRegistro} | Empleado: ${noemp} | Fecha: ${date}`);
    console.log(`Turno: ${turnoAsignado} | Hrs regl.: ${hrsReglDef} | Motivo: ${motivo}`);

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr    = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regAyerStr   = checador.regAnterior.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regMañanaStr = checador.regSiguiente.map(r => r.hora_limpia).join(", ") || "ninguno";

    console.log(`Registros hoy (${checador.fechaActual}): ${regHoyStr}`);

    if (checador.regActual.length < 2) {
        mostrarError(
            `Se necesitan al menos 2 registros en el checador del día ${date} para validar el turno mixto.`,
            { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: regMañanaStr, tipoEmpleado }
        );
        return;
    }

    const primerReg = checador.regActual[0];
    const ultimoReg = checador.regActual[checador.regActual.length - 1];

    const entradaMin = horaAMinutos(primerReg.hora_limpia);
    let   salidaMin  = horaAMinutos(ultimoReg.hora_limpia);
    if (salidaMin < entradaMin) salidaMin += 1440;

    const minutosTrabajados = salidaMin - entradaMin;
    const margenMin         = 5;

    console.log(`Primer registro hoy: ${primerReg.hora_limpia}`);
    console.log(`Último registro hoy: ${ultimoReg.hora_limpia}`);
    console.log(`Total trabajado: ${minutosTrabajados} min (${minutosAHoraConSegundos(minutosTrabajados)})`);
    console.log(`Hrs reglamentarias: ${hrsReglMinFinal} min | Excedente: ${Math.max(0, minutosTrabajados - hrsReglMinFinal)} min`);
    console.log("════════════════════════════════════════════════════════════");

    if (minutosTrabajados < (hrsReglMinFinal - margenMin)) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: "Turno mixto — No apto",
            html:  `El empleado trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero el turno <b>${turnoAsignado}</b> requiere <b>${hrsReglDef}</b>.<br><br>
                    Primer registro hoy (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                    Último registro hoy: <b>${ultimoReg.hora_limpia}</b><br>
                    Registros hoy: <b>${regHoyStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
            icon: "warning"
        });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
        if (fid) window.Reportes.evaluarFolio(fid);
        return;
    }

    const excedente = Math.max(0, minutosTrabajados - hrsReglMinFinal);

    // HoraFin   = última hora real del checador
    // HoraInicio = HoraFin - excedente  (solo el tramo del tiempo extra)
    const horaFinReal     = ultimoReg.hora_limpia;
    const horaInicioExtra = excedente > 0
        ? minutosAHoraConSegundos(((salidaMin - excedente) + 24 * 60) % (24 * 60))
        : horaFinReal;

    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({
        title: "Turno mixto — Apto",
        html:  `Turno: <b>${turnoAsignado}</b><br>
                Horas reglamentarias: <b>${hrsReglDef}</b><br>
                Primer registro hoy (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                Último registro hoy: <b>${ultimoReg.hora_limpia}</b><br>
                Horas trabajadas: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                Tipo: <b>${tipoEmpleado}</b><br>
                ${excedente >= 55
                    ? `Excedente: <b>${minutosAHoraConSegundos(excedente)}</b><br>
                       Horas T. extra ajustadas:<br>
                       Inicio: <b>${horaInicioExtra}</b> | Fin: <b>${horaFinReal}</b>`
                    : "Sin excedente significativo — se marcará como validado sin ajuste de horas."}`,
        icon: "success"
    });

    // ── Solo ajustar horas del registro original, SIN crear registro nuevo ────
    if (excedente >= 55) {
        const ok = await ajustarHorasRegistro(folioRegistro, horaInicioExtra, horaFinReal);
        if (ok) {
            await Swal.fire("Actualización realizada",
                `Registro validado.<br>Horas ajustadas: <b>${horaInicioExtra}</b> → <b>${horaFinReal}</b>`, "success");
        }
    } else {
        // Sin excedente relevante: solo marcar como validado
        const ok = await actualizarValidado(folioRegistro, null);
        if (ok) {
            const reg = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
            if (reg) { reg.validado = 1; actualizarFilaDOM(folioRegistro, reg); }
            await Swal.fire("Actualización realizada", "El registro quedó marcado como validado.", "success");
        }
    }

    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarTurnoNocturno — flujo normal para turno3 y turno3_12hrs
//
// Usa lógica de LAPSO (como mixtos): toma primer y último registro del
// período nocturno (cruzando medianoche) y calcula el excedente.
// HoraFin   = salida real | HoraInicio = HoraFin - excedente
// ─────────────────────────────────────────────────────────────────────────────
async function procesarTurnoNocturno(
    noemp, date, folioRegistro, turnoAsignado,
    tipoEmpleado, datosRegistro, mostrarError
) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) { mostrarError("Turno asignado no reconocido en el catálogo."); return; }

    const hrsReglMin = horaAMinutos(def.horasReglamentarias);

    console.log("═══════════ DIAGNÓSTICO procesarTurnoNocturno ═══════════");
    console.log(`Folio: ${folioRegistro} | Empleado: ${noemp} | Fecha: ${date}`);
    console.log(`Turno: ${turnoAsignado} | Hrs regl.: ${def.horasReglamentarias}`);

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr    = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regAyerStr   = checador.regAnterior.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regMañanaStr = checador.regSiguiente.map(r => r.hora_limpia).join(", ") || "ninguno";

    console.log(`Ayer (${checador.fechaAnterior}): ${regAyerStr}`);
    console.log(`Hoy  (${checador.fechaActual}): ${regHoyStr}`);
    console.log(`Mañana (${checador.fechaSiguiente}): ${regMañanaStr}`);

    const resolucion = resolverTurnoNocturno(checador.regAnterior, checador.regActual, checador.regSiguiente);

    console.log(`Escenario nocturno: ${resolucion?.escenario ?? "null"}`);
    console.log(`Entrada: ${resolucion?.entrada?.hora_limpia ?? "no encontrada"}`);
    console.log(`Salida : ${resolucion?.salida?.hora_limpia  ?? "no encontrada"}`);

    if (!resolucion || !resolucion.entrada || !resolucion.salida) {
        const detalle = !resolucion
            ? `No se encontraron registros nocturnos (>=18:00) ni diurnos (<12:00) que permitan determinar el turno.`
            : !resolucion.entrada
                ? `Se detectó la salida (<b>${resolucion.salida?.hora_limpia}</b>) pero no hay entrada nocturna en el día ${checador.fechaAnterior}.`
                : `Se detectó la entrada (<b>${resolucion.entrada?.hora_limpia}</b>) pero no hay salida en el día ${checador.fechaSiguiente}.`;

        mostrarError(
            `No se pudo determinar la entrada/salida del turno nocturno para el día ${date}.<br>${detalle}`,
            { registrosHoy: regHoyStr, registrosAyer: regAyerStr, registrosMañana: regMañanaStr,
              entradaDetectada: resolucion?.entrada?.hora_limpia || "no encontrada",
              salidaDetectada:  resolucion?.salida?.hora_limpia  || "no encontrada" }
        );
        return;
    }

    const entradaMin = horaAMinutos(resolucion.entrada.hora_limpia);
    let   salidaMin  = horaAMinutos(resolucion.salida.hora_limpia);
    if (salidaMin < entradaMin) salidaMin += 1440;

    const minutosTrabajados = salidaMin - entradaMin;
    const excedente         = Math.max(0, minutosTrabajados - hrsReglMin);

    const horaFinReal     = resolucion.salida.hora_limpia;
    const horaInicioExtra = excedente > 0
        ? minutosAHoraConSegundos(((salidaMin - excedente) + 24 * 60) % (24 * 60))
        : horaFinReal;

    console.log(`Total trabajado: ${minutosTrabajados} min | Excedente: ${excedente} min`);
    console.log(`HoraInicio T.extra: ${horaInicioExtra} | HoraFin T.extra: ${horaFinReal}`);
    console.log("════════════════════════════════════════════════════════════");

    if (minutosTrabajados < (hrsReglMin - 5)) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: "Turno nocturno — No apto",
            html:  `El empleado trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero el turno requiere <b>${def.horasReglamentarias}</b>.<br><br>
                    Turno: <b>${turnoAsignado}</b><br>
                    Entrada detectada (${resolucion.escenario.includes("ayer") ? checador.fechaAnterior : checador.fechaActual}): <b>${resolucion.entrada.hora_limpia}</b><br>
                    Salida detectada (${resolucion.escenario.includes("mañana") || resolucion.escenario.includes("siguiente") ? checador.fechaSiguiente : checador.fechaActual}): <b>${resolucion.salida.hora_limpia}</b><br>
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
            title: "Turno nocturno — No apto",
            html:  `El empleado completó las horas reglamentarias pero el excedente (<b>${minutosAHoraConSegundos(excedente)}</b>) no alcanza los 55 minutos mínimos para tiempo extra.<br><br>
                    Turno: <b>${turnoAsignado}</b><br>
                    Entrada detectada: <b>${resolucion.entrada.hora_limpia}</b><br>
                    Salida detectada: <b>${resolucion.salida.hora_limpia}</b><br>
                    Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
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

    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({
        title: "Turno nocturno — Apto",
        html:  `Turno: <b>${turnoAsignado}</b><br>
                Horas reglamentarias: <b>${def.horasReglamentarias}</b><br>
                Entrada detectada (${resolucion.escenario.includes("ayer") ? checador.fechaAnterior : checador.fechaActual}): <b>${resolucion.entrada.hora_limpia}</b><br>
                Salida detectada (${resolucion.escenario.includes("mañana") || resolucion.escenario.includes("siguiente") ? checador.fechaSiguiente : checador.fechaActual}): <b>${resolucion.salida.hora_limpia}</b><br>
                Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                Excedente: <b>${minutosAHoraConSegundos(excedente)}</b><br>
                Tipo: <b>${tipoEmpleado}</b><br><br>
                Horas T. extra ajustadas:<br>
                Inicio: <b>${horaInicioExtra}</b> | Fin: <b>${horaFinReal}</b>`,
        icon: "success"
    });

    const ok = await ajustarHorasRegistro(folioRegistro, horaInicioExtra, horaFinReal);
    if (ok) {
        await Swal.fire("Actualización realizada",
            `Registro validado.<br>Horas ajustadas: <b>${horaInicioExtra}</b> → <b>${horaFinReal}</b>`, "success");
    }

    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarAnticipo
// ─────────────────────────────────────────────────────────────────────────────
async function procesarAnticipo(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError) {
    const def = CATALOGO_TURNOS[turnoAsignado];
    if (!def) { mostrarError("Turno asignado no reconocido en el catálogo."); return; }

    const hrsReglMin     = horaAMinutos(def.horasReglamentarias);
    const horaInicioEnBD = datosRegistro?.HoraInicio || null;
    const horaFinEnBD    = datosRegistro?.HoraFin    || null;

    console.log("═══════════ DIAGNÓSTICO procesarAnticipo ═══════════");
    console.log(`Folio: ${folioRegistro} | Empleado: ${noemp} | Fecha: ${date}`);
    console.log(`Turno: ${turnoAsignado} | Hrs regl.: ${def.horasReglamentarias}`);

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";

    if (checador.regActual.length < 1) {
        mostrarError(`No hay registros en el checador para el día ${date}.`, { registrosHoy: regHoyStr });
        return;
    }

    const primerReg = checador.regActual[0];
    const ultimoReg = checador.regActual[checador.regActual.length - 1];

    const entradaReal = horaAMinutos(primerReg.hora_limpia);
    let   salidaReal  = horaAMinutos(ultimoReg.hora_limpia);
    if (salidaReal < entradaReal) salidaReal += 1440;

    const totalTrabajadoMin = salidaReal - entradaReal;
    const hrsExtraRealesMin = Math.max(0, totalTrabajadoMin - hrsReglMin);

    let hrsSolicitadasMin = 0;
    if (horaInicioEnBD && horaFinEnBD) {
        let iniMin = horaAMinutos(horaInicioEnBD);
        let finMin = horaAMinutos(horaFinEnBD);
        if (finMin < iniMin) finMin += 1440;
        hrsSolicitadasMin = finMin - iniMin;
    }

    console.log(`Primer registro: ${primerReg.hora_limpia} | Último: ${ultimoReg.hora_limpia}`);
    console.log(`Total trabajado: ${totalTrabajadoMin} min | Reglamentarias: ${hrsReglMin} min | Extra reales: ${hrsExtraRealesMin} min`);
    console.log("════════════════════════════════════════════════════════════");

    if (totalTrabajadoMin < hrsReglMin) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: "Anticipo — No apto",
            html:  `El empleado no completó las horas reglamentarias del turno.<br><br>
                    Turno: <b>${turnoAsignado}</b> (reglamentarias: <b>${def.horasReglamentarias}</b>)<br>
                    Primer registro hoy (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                    Último registro hoy: <b>${ultimoReg.hora_limpia}</b><br>
                    Total trabajado: <b>${minutosAHoraConSegundos(totalTrabajadoMin)}</b><br>
                    Registros hoy: <b>${regHoyStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
            icon: "warning"
        });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
        if (fid) window.Reportes.evaluarFolio(fid);
        return;
    }

    if (hrsExtraRealesMin < 55) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: "Anticipo — No apto",
            html:  `El empleado completó las reglamentarias pero el excedente (<b>${minutosAHoraConSegundos(hrsExtraRealesMin)}</b>) no alcanza los 55 minutos mínimos.<br><br>
                    Turno: <b>${turnoAsignado}</b> (reglamentarias: <b>${def.horasReglamentarias}</b>)<br>
                    Primer registro hoy (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                    Último registro hoy: <b>${ultimoReg.hora_limpia}</b><br>
                    Total trabajado: <b>${minutosAHoraConSegundos(totalTrabajadoMin)}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
            icon: "warning"
        });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
        if (fid) window.Reportes.evaluarFolio(fid);
        return;
    }

    const finAnticipo    = def.entrada.ideal;
    const inicioAnticipo = ((finAnticipo - hrsExtraRealesMin) + 24 * 60) % (24 * 60);
    const nuevaHoraInicio = minutosAHoraConSegundos(inicioAnticipo);
    const nuevaHoraFin    = minutosAHoraConSegundos(finAnticipo);

    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({
        title: "Anticipo — Apto",
        html:  `Turno: <b>${turnoAsignado}</b><br>
                Horas reglamentarias: <b>${def.horasReglamentarias}</b><br>
                Primer registro hoy (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                Último registro hoy: <b>${ultimoReg.hora_limpia}</b><br>
                Total trabajado: <b>${minutosAHoraConSegundos(totalTrabajadoMin)}</b><br>
                Horas de anticipo reales: <b>${minutosAHoraConSegundos(hrsExtraRealesMin)}</b><br>
                Tipo: <b>${tipoEmpleado}</b><br><br>
                Horas ajustadas:<br>
                Inicio T. extra: <b>${nuevaHoraInicio}</b> | Fin T. extra: <b>${nuevaHoraFin}</b>`,
        icon: "success"
    });

    const ok = await ajustarHorasRegistro(folioRegistro, nuevaHoraInicio, nuevaHoraFin);
    if (ok) {
        await Swal.fire("Actualización realizada",
            `Registro validado.<br>Horas ajustadas: <b>${nuevaHoraInicio}</b> → <b>${nuevaHoraFin}</b>`, "success");
    }

    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarApoyo
// ─────────────────────────────────────────────────────────────────────────────
async function procesarApoyo(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError) {
    const MARGEN_APOYO_MIN = 30;
    const horaInicioRef    = datosRegistro?.HoraInicio || null;

    if (!horaInicioRef) { mostrarError("No se encontró la hora de inicio de apoyo en el registro."); return; }

    let checador;
    try { checador = await fetchChecador(noemp, date); }
    catch (e) { mostrarError("Error al conectar con el checador."); return; }

    const regHoyStr    = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";
    const regMañanaStr = checador.regSiguiente.map(r => r.hora_limpia).join(", ") || "ninguno";
    const refMin       = horaAMinutos(horaInicioRef);

    const registroInicio = checador.regActual.find(r => Math.abs(horaAMinutos(r.hora_limpia) - refMin) <= MARGEN_APOYO_MIN);

    if (!registroInicio) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: "Apoyo — No apto",
            html:  `No se encontró ningún registro en el checador dentro de <b>±${MARGEN_APOYO_MIN} minutos</b> de la hora de inicio indicada (<b>${horaInicioRef}</b>).<br><br>
                    Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
            icon: "warning"
        });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
        if (fid) window.Reportes.evaluarFolio(fid);
        return;
    }

    const inicioRealMin  = horaAMinutos(registroInicio.hora_limpia);
    const candidatosHoy  = checador.regActual.filter(r => horaAMinutos(r.hora_limpia) > inicioRealMin);
    const registroFin    = candidatosHoy.length > 0 ? candidatosHoy[0] : (checador.regSiguiente.length > 0 ? checador.regSiguiente[0] : null);

    if (!registroFin) {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        await Swal.fire({
            title: "Apoyo — No apto",
            html:  `Se encontró el registro de inicio (<b>${registroInicio.hora_limpia}</b>) pero no hay ningún registro posterior para determinar la hora de fin.<br><br>
                    Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>
                    Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
            icon: "warning"
        });
        const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
        if (fid) window.Reportes.evaluarFolio(fid);
        return;
    }

    const nuevaHoraInicio = registroInicio.hora_limpia;
    const nuevaHoraFin    = registroFin.hora_limpia;
    let   finMin          = horaAMinutos(nuevaHoraFin);
    if (finMin < inicioRealMin) finMin += 1440;
    const duracionApoyo   = finMin - inicioRealMin;

    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({
        title: "Apoyo — Apto",
        html:  `Registro de inicio encontrado en el checador (${checador.fechaActual}): <b>${nuevaHoraInicio}</b><br>
                Registro de fin encontrado: <b>${nuevaHoraFin}</b><br>
                Duración del apoyo: <b>${minutosAHoraConSegundos(duracionApoyo)}</b><br>
                Tipo: <b>${tipoEmpleado}</b><br><br>
                Las horas de inicio y fin se ajustarán con los registros reales.`,
        icon: "success"
    });

    const ok = await ajustarHorasRegistro(folioRegistro, nuevaHoraInicio, nuevaHoraFin);
    if (ok) {
        await Swal.fire("Actualización realizada",
            `Registro validado.<br>Horas ajustadas: <b>${nuevaHoraInicio}</b> → <b>${nuevaHoraFin}</b>`, "success");
    }

    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// validarTiempoExtra — flujo normal (turno1, turno2, mixtos en motivos normales)
// ─────────────────────────────────────────────────────────────────────────────
function validarTiempoExtra(horasExtra, totalHoras, horasReglamentarias, folioRegistro, horasalida, horaentrada, horasExtra2, HoraInicio, turnoDetectado) {
    function sumarHoras(h1Str, h2Str) {
        const [h1, m1] = h1Str.split(":").map(Number);
        const [h2, m2] = h2Str.split(":").map(Number);
        return minutosAHoraConSegundos(((h1 * 60 + m1 + h2 * 60 + m2)) % (24 * 60));
    }

    const nuevaHoraFin          = sumarHoras(HoraInicio, horasExtra2);
    const minutosExtra          = horaAMinutos(horasExtra);
    const minutosTotal          = horaAMinutos(totalHoras);
    const minutosReglamentarios = horaAMinutos(horasReglamentarias);
    const btnEliminarFila       = document.getElementById(`btnEliminar-${folioRegistro}`);

    function actualizarFilaDOMLocal(folioRegistro, registro) {
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

    async function fetchActualizar(endpoint, horaFinNueva) {
        const data = new FormData();
        data.append("folioRegistro", folioRegistro);
        if (horaFinNueva) data.append("nuevaHoraFin", horaFinNueva);
        try {
            const r    = await fetch(`php/index.php?${endpoint}`, { method: "POST", body: data });
            const resp = await r.json();
            if (resp.success) {
                const reg = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
                if (reg) { if (horaFinNueva) reg.HoraFin = horaFinNueva; reg.validado = 1; }
                actualizarFilaDOMLocal(folioRegistro, reg);
                await Swal.fire("Actualización realizada", `Registro validado${horaFinNueva ? `. Hora Fin ajustada a <b>${horaFinNueva}</b>` : ""}.`, "success");
                const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
                if (fid) window.Reportes.evaluarFolio(fid);
            } else { Swal.fire("Error en actualización", `Detalle: ${resp.error}`, "error"); }
        } catch (err) { console.error("Error fetchActualizar:", err); }
    }

    if (turnoDetectado === "turno2_13hrs") {
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        const hf = "16:30:00";
        Swal.fire({ title: "Turno 2 — 13 hrs — Apto", html: `Inicio T. extra: <b>${HoraInicio}</b><br>Fin ajustado: <b>${hf}</b>`, icon: "info" })
            .then(() => fetchActualizar("actualizarHoraFin", hf));
        if (btnEliminarFila) btnEliminarFila.hidden = true;

    } else if (turnoDetectado === "turno3_12hrs") {
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        Swal.fire({ title: "Turno 3 — 12 hrs — Apto", html: `Inicio T. extra: <b>${HoraInicio}</b><br>Salida: <b>${horasalida}</b>`, icon: "info" })
            .then(() => fetchActualizar("actualizarEstadoValidado", null));
        if (btnEliminarFila) btnEliminarFila.hidden = true;

    } else if (turnoDetectado === "mixto4") {
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        Swal.fire({ title: "Mixto 4 — Apto", html: `Inicio T. extra: <b>${HoraInicio}</b><br>Extra: <b>${horasExtra2}</b><br>Fin ajustado: <b>${nuevaHoraFin}</b>`, icon: "info" })
            .then(() => fetchActualizar("actualizarHoraFin", nuevaHoraFin));
        if (btnEliminarFila) btnEliminarFila.hidden = true;

    } else if (minutosTotal >= minutosReglamentarios && minutosExtra >= 50) {
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        Swal.fire({
            title: "Resultados de validación — Apto",
            html:  `El empleado cumple los requisitos para el T. extra solicitado.<br><br>
                    Inicio T. extra: <b>${HoraInicio}</b><br>
                    Hrs extra trabajadas: <b>${horasExtra2}</b><br>
                    Turno: <b>${turnoDetectado}</b><br>
                    Fin ajustado: <b>${nuevaHoraFin}</b><br>
                    Entrada checador: <b>${horaentrada}</b><br>
                    Salida checador: <b>${horasalida}</b>`,
            icon: "info"
        }).then(() => { if (horasalida !== nuevaHoraFin) fetchActualizar("actualizarHoraFin", nuevaHoraFin); });
        if (btnEliminarFila) btnEliminarFila.hidden = true;

    } else {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        Swal.fire({
            title: "Resultados de validación — No apto",
            html:  `No se cumplen los requisitos para el T. extra solicitado.<br><br>
                    Turno: <b>${turnoDetectado}</b><br>
                    Tiempo extra trabajado: <b>${horasExtra2}</b><br>
                    Total horas trabajadas: <b>${totalHoras}</b><br>
                    Horas reglamentarias: <b>${horasReglamentarias}</b><br>
                    Entrada checador: <b>${horaentrada}</b><br>
                    Salida checador: <b>${horasalida}</b>`,
            icon: "info"
        });
        if (btnEliminarFila) btnEliminarFila.hidden = false;
    }

    const fid = window.Reportes.data.find(e => e.folioRegistro == folioRegistro)?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
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

        const razonRegistro = (datosRegistro?.razon || "").toString().trim().toLowerCase();
        const esAnticipo    = razonRegistro === "anticipo";
        const esApoyo       = razonRegistro === "apoyo";
        const horaFinRegistro = datosRegistro?.HoraFin || null;
        const esTurnoNocturno = ["turno3", "turno3_12hrs"].includes(turnoAsignado);

        console.log("═══════════ INICIO validación ═══════════");
        console.log(`folioRegistro: ${folioRegistro} | noemp: ${noemp} | fecha: ${date}`);
        console.log(`turnoAsignado: ${turnoAsignado} | motivo: ${motivo}`);
        console.log(`tipoEmpleado: ${tipoEmpleado} | razon: "${razonRegistro}"`);
        console.log(`esAnticipo: ${esAnticipo} | esApoyo: ${esApoyo} | esTurnoNocturno: ${esTurnoNocturno}`);

        const lblMensaje = document.getElementById("lblMensaje");

        const mostrarError = (msg, ctx = {}) => {
            if (lblMensaje) {
                lblMensaje.hidden = false;
                lblMensaje.className = "alert alert-warning mt-2";
                lblMensaje.style.display = "block";
            }
            const btn = document.getElementById(`btnEliminar-${folioRegistro}`);
            if (btn) btn.hidden = false;

            const def = CATALOGO_TURNOS[turnoAsignado];
            const lineas = [
                `Turno solicitado: <b>${turnoAsignado}</b>`,
                def ? `Hrs reglamentarias: <b>${def.horasReglamentarias}</b>` : "",
                `Tipo empleado: <b>${tipoEmpleado}</b>`,
                ctx.registrosHoy    ? `Registros checador hoy: <b>${ctx.registrosHoy}</b>`       : "",
                ctx.registrosAyer   ? `Registros checador ayer: <b>${ctx.registrosAyer}</b>`     : "",
                ctx.registrosMañana ? `Registros checador mañana: <b>${ctx.registrosMañana}</b>` : "",
                ctx.entradaDetectada ? `Entrada detectada: <b>${ctx.entradaDetectada}</b>`        : "",
                ctx.salidaDetectada  ? `Salida detectada: <b>${ctx.salidaDetectada}</b>`          : "",
                ctx.horasTrabajadas  ? `Horas trabajadas: <b>${ctx.horasTrabajadas}</b>`          : "",
            ].filter(Boolean).join("<br>");

            Swal.fire({
                title: "Resultados de validación",
                html:  `Se recomienda revisar este registro.<br><br><b>${msg}</b><br><br>── Diagnóstico ──<br>${lineas}`,
                icon:  "info"
            });
        };

        if (!CATALOGO_TURNOS[turnoAsignado]) {
            mostrarError(`El turno "<b>${turnoAsignado}</b>" no está reconocido en el catálogo. Comunícate con Nóminas.`); return;
        }

        // ── APOYO ─────────────────────────────────────────────────────────────
        if (esApoyo) {
            await procesarApoyo(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError);
            return;
        }

        // ── ANTICIPO ──────────────────────────────────────────────────────────
        if (esAnticipo) {
            await procesarAnticipo(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError);
            return;
        }

        // ── AUTO-APTOS (cambio horario / hora comida) ─────────────────────────
        if (MOTIVOS_AUTO_APTOS.includes(motivo)) {
            window.Reportes.marcarRegistroComoApto(folioRegistro);
            const ok = await actualizarValidado(folioRegistro, null);
            if (ok) {
                const reg = this.data.find(e => e.folioRegistro == folioRegistro);
                if (reg) { reg.validado = 1; actualizarFilaDOM(folioRegistro, reg); }
                await Swal.fire("Validado", `El registro de ${motivo === MOTIVO_HORA_COMIDA ? "hora de comida" : "cambio de horario"} fue marcado como validado.`, "success");
            }
            const fid = this.data.find(e => e.folioRegistro == folioRegistro)?.id;
            if (fid) this.evaluarFolio(fid);
            return;
        }

        // ── DESCANSO TRABAJADO / DÍA FESTIVO ─────────────────────────────────
        if (MOTIVOS_ESPECIALES.includes(motivo)) {
            await procesarDescansoOFestivo(noemp, date, folioRegistro, turnoAsignado, motivo, tipoEmpleado, datosRegistro, mostrarError);
            return;
        }

        // ── TURNO NOCTURNO (flujo normal) — motivos que no son especiales ─────
        if (esTurnoNocturno) {
            await procesarTurnoNocturno(noemp, date, folioRegistro, turnoAsignado, tipoEmpleado, datosRegistro, mostrarError);
            return;
        }

        // ── MIXTO ESPECIAL ────────────────────────────────────────────────────
        if (MOTIVOS_MIXTO_ESPECIAL.includes(motivo) && TURNOS_MIXTOS.includes(turnoAsignado)) {
            await procesarMixtoEspecial(noemp, date, folioRegistro, turnoAsignado, motivo, tipoEmpleado, datosRegistro, mostrarError);
            return;
        }

        // ── FLUJO NORMAL (turno1, turno2, mixtos en motivos no especiales) ────
        let checador;
        try { checador = await fetchChecador(noemp, date); }
        catch (e) { mostrarError("Error al conectar con el checador."); return; }

        const regHoyStr = checador.regActual.map(r => r.hora_limpia).join(", ") || "ninguno";

        if (!checador.rawActual.length) {
            mostrarError(`No hay registros en el checador para el día ${date}.`, { registrosHoy: regHoyStr }); return;
        }

        const esSabado   = new Date(date + "T00:00:00").getDay() === 6;
        const resolucion = resolverEntradaSalida(turnoAsignado, checador.regAnterior, checador.regActual, checador.regSiguiente, esSabado, horaFinRegistro);

        if (!resolucion) {
            mostrarError(`No se encontraron registros de entrada/salida coherentes con el turno <b>${turnoAsignado}</b> para el día ${date}.`,
                { registrosHoy: regHoyStr }); return;
        }
        if (!resolucion.salida) {
            mostrarError(`Se detectó la entrada (<b>${resolucion.entrada?.hora_limpia}</b>) del turno <b>${turnoAsignado}</b> pero no hay registro de salida todavía para el día ${date}.`,
                { registrosHoy: regHoyStr, entradaDetectada: resolucion.entrada?.hora_limpia });
            return;
        }

        const horaentrada = resolucion.entrada.hora_limpia;
        const horasalida  = resolucion.salida.hora_limpia;
        const resultado   = calcularTurnoYHoras(horaentrada, horasalida, esSabado, turnoAsignado);

        if (resultado.totalHoras <= "00:05:00") {
            mostrarError(`El lapso entre entrada (<b>${horaentrada}</b>) y salida (<b>${horasalida}</b>) es demasiado corto para el día ${date}. Revisa los registros del checador.`,
                { registrosHoy: regHoyStr, entradaDetectada: horaentrada, salidaDetectada: horasalida });
            return;
        }

        const turnonom = resultado.turno.split(/[\s,;()\-\.]+/);
        if (turnonom[3] === undefined) {
            mostrarError(`No se pudo determinar el turno correctamente. Turno detectado: <b>${resultado.turno}</b>. Comunícate con Nóminas.`,
                { registrosHoy: regHoyStr, entradaDetectada: horaentrada, salidaDetectada: horasalida });
            return;
        }

        const horaExtrass  = horaAMinutos(resultado.totalHoras);
        const horaReglamen = horaAMinutos(resultado.horasReglamentarias);
        let horaExtra2 = "00:00:00";
        if (horaExtrass >= horaReglamen) horaExtra2 = minutosAHoraConSegundos(horaExtrass - horaReglamen);

        if (turnoAsignado === "turno3_12hrs") {
            await Swal.fire({ title: "Turno 3 — 12 hrs", html: `Entrada: <b>${horaentrada}</b><br>Salida: <b>${horasalida}</b><br>Total: <b>${resultado.totalHoras}</b><br>Reglamentarias: <b>${resultado.horasReglamentarias}</b><br>T. extra calculado: <b>${horaExtra2}</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "info" });
        } else if (turnoAsignado === "turno2_13hrs") {
            await Swal.fire({ title: "Turno 2 — 13 hrs", html: `Entrada: <b>${horaentrada}</b><br>Salida: <b>${horasalida}</b><br>Reglamentarias: <b>${resultado.horasReglamentarias}</b><br>T. extra calculado: <b>${horaExtra2}</b><br>Fin ajustado: <b>16:30</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "info" });
        } else if (turnoAsignado === "mixto4") {
            await Swal.fire({ title: "Mixto 4", html: `Entrada: <b>${horaentrada}</b><br>Salida: <b>${horasalida}</b><br>Total: <b>${resultado.totalHoras}</b><br>Reglamentarias: <b>${resultado.horasReglamentarias}</b><br>T. extra calculado: <b>${horaExtra2}</b><br>Tipo: <b>${tipoEmpleado}</b>`, icon: "info" });
        }

        validarTiempoExtra(horaExtra2, resultado.totalHoras, resultado.horasReglamentarias,
            folioRegistro, horasalida, horaentrada, horaExtra2, HoraInicio, turnoAsignado);

        const reg = this.data.find(e => e.folioRegistro == folioRegistro);
        if (reg) this.evaluarFolio(reg.id);
    }

    async consulta() {
        const respuetaraw = await fetch("php/index.php?tblautorizatp");
        const respuesta   = await respuetaraw.json();
        if (Array.isArray(respuesta)) {
            this.data = respuesta;
            let body = ""; const folios = new Set();
            respuesta.forEach(e => { folios.add(e.id); body += this.renderRow(e); });
            document.getElementById("tblenc").innerHTML = body;
            const ac = document.getElementById("accionesGlobales");
            ac.innerHTML = "";
            folios.forEach(fid => {
                ac.innerHTML += `<div class="mb-3" id="acciones-folio-${fid}">
                    <button class="btn btn-success" id="btnAutorizar-${fid}" hidden>Autorizar TODO el Folio ${fid}</button>
                    <button class="btn btn-danger"  id="btnRechazar-${fid}"  hidden>Rechazar TODO el Folio ${fid}</button>
                </div>`;
            });
            folios.forEach(fid => {
                document.getElementById(`btnAutorizar-${fid}`)?.addEventListener("click", () => this.enviar(fid, 1));
                document.getElementById(`btnRechazar-${fid}`)?.addEventListener("click",  () => this.enviar(fid, 2));
            });
            const ahora = new Date();
            respuesta.forEach(e => this._aplicarEstadoFecha(e, ahora));
        }
    }

    renderRow(elemento) {
        let accionHtml = "", accionTerminadoHtml = "", badgeValidado = "";
        if (elemento.terminado === null || elemento.terminado === "") {
            if (elemento.validado == null || elemento.validado == 0) {
                accionHtml = `
                    <button class="btn btn-sm btn-warning"
                            onclick="validarInfo('${elemento.NoEmpleadoSol}','${elemento.fechaSol}','${elemento.folioRegistro}','${elemento.HoraInicio}','${elemento.turnoAsignado}',${elemento.folioRegistro})"
                            id="btnValidar-${elemento.folioRegistro}">
                        <i class="fa-solid fa-eye"></i> Validar T. extra
                    </button>
                    <button class="btn btn-sm btn-danger"
                            onclick="window.deletesub(${elemento.folioRegistro})"
                            id="btnEliminar-${elemento.folioRegistro}" hidden>
                        <i class="fas fa-times"></i> Eliminar
                    </button>`;
            } else { accionHtml = `<span class="badge bg-success">Solicitud validada</span>`; }
            accionTerminadoHtml = `<span class="badge bg-warning">No procesada (Pendiente)</span>`;
        } else if (elemento.terminado == 1) {
            accionHtml = `<span class="badge bg-success">Aprobado</span>`;
            accionTerminadoHtml = `<span class="badge bg-success">Solicitud procesada (Aprobado)</span>`;
        } else if (elemento.terminado == 2) {
            accionHtml = `<span class="badge bg-danger">Rechazado</span>`;
            accionTerminadoHtml = `<span class="badge bg-danger">Solicitud procesada (Rechazado)</span>`;
        }
        badgeValidado = (elemento.validado == 1)
            ? `<span class="badge bg-success">Validado</span>`
            : `<span class="badge bg-secondary">No validado</span>`;
        return `<tr>
            <td>${elemento.folioRegistro}</td><td>${elemento.id}</td><td>${elemento.creado}</td>
            <td>${elemento.NoEmp}</td><td>${elemento.SupervisorNombre}</td><td>${elemento.NoEmpleadoSol}</td>
            <td>${elemento.fechaSol}</td><td>${elemento.HoraInicio}</td><td>${elemento.HoraFin}</td>
            <td>${elemento.NombreEmpleadoSol}</td><td>${elemento.departamento}</td>
            <td>${accionTerminadoHtml}<br></td><td>${badgeValidado}</td>
            <td>${accionHtml}<small id="msg-${elemento.folioRegistro}" class="d-block mt-1"></small></td>
        </tr>`;
    }

    _aplicarEstadoFecha(elemento, ahora) {
        const fechaSol    = new Date(elemento.fechaSol + "T00:00:00");
        const [h, m, s]   = elemento.HoraFin.split(":").map(Number);
        const horaFinDate = new Date(fechaSol); horaFinDate.setHours(h, m, s);
        const margen      = new Date(horaFinDate.getTime() + 5 * 60000);
        const btnValidar  = document.getElementById(`btnValidar-${elemento.folioRegistro}`);
        const msg         = document.getElementById(`msg-${elemento.folioRegistro}`);
        if (ahora < fechaSol) {
            if (btnValidar) btnValidar.hidden = true;
            if (msg) { msg.textContent = `El día solicitado (${elemento.fechaSol}) aún no ha llegado.`; msg.className = "alert alert-warning p-1 mt-1"; }
        } else if (ahora.toDateString() === fechaSol.toDateString() && ahora < margen) {
            if (btnValidar) btnValidar.hidden = true;
            if (msg) { msg.textContent = `Aún no es momento. El botón se habilitará a las ${margen.toLocaleTimeString([], {hour:"2-digit",minute:"2-digit"})} hrs.`; msg.className = "alert alert-warning p-1 mt-1"; }
        } else {
            if (msg) { msg.textContent = ""; msg.dataset.estado = "pendiente"; }
        }
    }

    filtrarPorFolio(folioId) {
        const filtrados = this.data.filter(e => e.id == folioId);
        let body = ""; filtrados.forEach(e => body += this.renderRow(e));
        document.getElementById("tblenc").innerHTML = body;
        document.getElementById("accionesGlobales").innerHTML = `
            <div class="mb-3" id="acciones-folio-${folioId}">
                <button class="btn btn-success" id="btnAutorizar-${folioId}" hidden>Autorizar TODO el Folio ${folioId}</button>
                <button class="btn btn-danger"  id="btnRechazar-${folioId}"  hidden>Rechazar TODO el Folio ${folioId}</button>
            </div>`;
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
        if (!verif?.success) {
            Swal.fire({ icon: "warning", title: "Firma no registrada", text: "Debes registrar tu firma primero.", confirmButtonText: "Entendido", confirmButtonColor: "#f0ad4e" });
            return;
        }
        const resp = await fetch(`./php/index.php?autorizafol&id=${id}&autor=${autor}`).then(r => r.json());
        resp === false
            ? Swal.fire({ icon: "error",   title: "Error", text: resp.message || "Error con la base de datos." })
            : Swal.fire({ icon: "success", title: "Autorizado", text: "Autorizado con éxito", timer: 2000, showConfirmButton: false });
        window.open(`./pdf/reporte.php?folio=${btoa(id)}&true=1trlist`);
        window.location.reload();
    }

    pdffin(id) { window.open(`./pdf/reporte.php?folio=${btoa(id)}&true=1trlist`); }

    marcarRegistroComoApto(folioRegistro) {
        const btn = document.getElementById(`btnValidar-${folioRegistro}`);
        const msg = document.getElementById(`msg-${folioRegistro}`);
        if (btn) btn.hidden = true;
        if (msg) { msg.textContent = "Registro apto para tiempo extra."; msg.className = "alert alert-success p-1 mt-1"; msg.dataset.estado = "apto"; }
    }

    marcarRegistroComoNoApto(folioRegistro) {
        const btn = document.getElementById(`btnValidar-${folioRegistro}`);
        const be  = document.getElementById(`btnEliminar-${folioRegistro}`);
        const msg = document.getElementById(`msg-${folioRegistro}`);
        if (btn) btn.hidden = true;
        if (be) be.hidden = false;
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
        const bA = document.getElementById(`btnAutorizar-${folioId}`);
        const bR = document.getElementById(`btnRechazar-${folioId}`);
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
             item.NombreEmpleadoSol, item.validado, item.motivo, item.turnoAsignado, item.razon]
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
                    accionHtml = `
                        <button class="btn btn-sm btn-warning"
                                onclick="validarInfo('${element.NoEmpleadoSol}','${element.fechaSol}','${element.folioRegistro}','${element.HoraInicio}','${element.turnoAsignado}',${element.folioRegistro})"
                                id="btnValidar-${element.folioRegistro}">
                            <i class="fa-solid fa-eye"></i> Validar T. extra
                        </button>
                        <button class="btn btn-sm btn-danger"
                                onclick="window.deletesub(${element.folioRegistro})"
                                id="btnEliminar-${element.folioRegistro}" hidden>
                            <i class="fas fa-times"></i> Eliminar
                        </button>`;
                } else { accionHtml = `<span class="badge bg-success">Solicitud validada</span>`; }
                accionTerminadoHtml = `<span class="badge bg-warning">No procesada (Pendiente)</span>`;
            } else if (element.terminado == 1) {
                accionHtml = `<span class="badge bg-success">Aprobado</span>`;
                accionTerminadoHtml = `<span class="badge bg-success">Solicitud procesada (Aprobado)</span>`;
            } else if (element.terminado == 2) {
                accionHtml = `<span class="badge bg-danger">Rechazado</span>`;
                accionTerminadoHtml = `<span class="badge bg-danger">Solicitud procesada (Rechazado)</span>`;
            }
            badgeValidado = (element.validado == 1)
                ? `<span class="badge bg-success">Validado</span>`
                : `<span class="badge bg-secondary">No validado</span>`;
            body += `<tr>
                <td>${element.folioRegistro}</td><td>${element.id}</td><td>${element.creado}</td>
                <td>${element.NoEmp}</td><td>${element.SupervisorNombre}</td><td>${element.NoEmpleadoSol}</td>
                <td>${element.fechaSol}</td><td>${element.HoraInicio}</td><td>${element.HoraFin}</td>
                <td>${element.NombreEmpleadoSol}</td><td>${element.departamento}</td>
                <td>${accionTerminadoHtml}<br></td><td>${badgeValidado}</td>
                <td>${accionHtml}<small id="msg-${element.folioRegistro}" class="d-block mt-1"></small></td>
            </tr>`;
        });
    }
    tbody.innerHTML = body;
    const ahora = new Date();
    datosFiltrados.forEach(e => window.Reportes._aplicarEstadoFecha(e, ahora));
}

// ── Tutorial driver.js ────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    const driver = window.driver.js.driver;
    const steps = [
        { element: ".tittlecont",                    popover: { title: "Validación de solicitudes",  description: "Aquí podrás validar las solicitudes de tiempo extra antes de enviarlas a Gerencia.", side: "bottom" } },
        { element: ".alert.alert-info",              popover: { title: "Instrucciones",              description: "Desde esta sección revisa qué solicitudes son aptas para autorización.", side: "bottom" } },
        { element: "#filtroGlobal",                  popover: { title: "Filtro global",              description: "Usa este campo para buscar solicitudes.", side: "top", popoverClass: "popover-importante" } },
        { element: "table thead th:nth-child(1)",    popover: { title: "ID Registro",                description: "Identificador único de cada solicitud.", side: "top" } },
        { element: "table thead th:nth-child(2)",    popover: { title: "Folio",                      description: "Número de folio.", side: "top" } },
        { element: "table thead th:nth-child(12)",   popover: { title: "Estatus",                    description: "Aprobada o rechazada por Gerencia.", side: "top", popoverClass: "popover-importante" } },
        { element: "table thead th:nth-child(13)",   popover: { title: "Validado",                   description: "Si ya fue validada con el botón.", side: "top", popoverClass: "popover-importante" } },
        { element: "table thead th:nth-child(14)",   popover: { title: "Acciones",                   description: "Botones para validar individualmente.", side: "top", popoverClass: "popover-importante" } },
        { element: "#btnAyuda",                      popover: { title: "Tutorial",                   description: "Presiona para repetir el tutorial.", side: "bottom" } }
    ];
    document.querySelectorAll('[id^="msg-"]').forEach(msgEl => {
        steps.push({ element: `#${msgEl.id}`, popover: { title: "Mensaje de validación", description: msgEl.textContent || "Estado de la solicitud.", side: "top" } });
    });
    const driverObj = driver({ showProgress: true, allowClose: false, disableInteraction: true, progressText: "Paso {{current}} de {{total}}", doneBtnText: "Finalizar", nextBtnText: "Siguiente", prevBtnText: "Atrás", steps });
    const tk = "tutorial_validacionTE";
    if (!localStorage.getItem(tk)) { driverObj.drive(); localStorage.setItem(tk, "true"); }
    document.getElementById("btnAyuda")?.addEventListener("click", () => driverObj.drive());
});