import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();

// ─────────────────────────────────────────────────────────────────────────────
// IDs DE MOTIVOS
// ─────────────────────────────────────────────────────────────────────────────
const MOTIVO_CAMBIO_HORARIO = 8;
const MOTIVO_HORA_COMIDA = 9;
const MOTIVO_DESCANSO_TRABAJADO = 10;
const MOTIVO_DIA_FESTIVO = 12;

const MOTIVOS_AUTO_APTOS = [MOTIVO_CAMBIO_HORARIO, MOTIVO_HORA_COMIDA];
const MOTIVOS_ESPECIALES = [MOTIVO_DESCANSO_TRABAJADO, MOTIVO_DIA_FESTIVO];
const MOTIVOS_MIXTO_ESPECIAL = [1, 2, 3, 4, 5, 6, 7, 11];
const TURNOS_MIXTOS = ["mixto1", "mixto2", "mixto3", "mixto4"];

// ─────────────────────────────────────────────────────────────────────────────
// CATÁLOGO CENTRAL DE TURNOS
// ─────────────────────────────────────────────────────────────────────────────
const CATALOGO_TURNOS = {
  turno1: {
    nombre: "Turno 1 (07:00 - 15:00)",
    entrada: { ideal: 7 * 60, tolerancia: 35 },
    salida: { ideal: 15 * 60, tolerancia: 60 },
    cruzaMedia: false,
    horasReglamentarias: "08:00:00",
    sabado: null,
  },
  turno2: {
    nombre: "Turno 2 (15:00 - 22:30)",
    entrada: { ideal: 15 * 60, tolerancia: 35 },
    salida: { ideal: 22 * 60 + 30, tolerancia: 60 },
    cruzaMedia: false,
    horasReglamentarias: "07:30:00",
    sabado: null,
  },
  turno2_13hrs: {
    nombre: "Turno 2 13hrs (10:30 - 22:30)",
    entrada: { ideal: 10 * 60 + 30, tolerancia: 20 },
    salida: { ideal: 22 * 60 + 30, tolerancia: 60 },
    cruzaMedia: false,
    horasReglamentarias: "04:30:00",
    sabado: null,
  },
  turno3: {
    nombre: "Turno 3 (22:30 - 07:00)",
    entrada: { ideal: 22 * 60 + 30, tolerancia: 35 },
    salida: { ideal: 7 * 60, tolerancia: 35 },
    cruzaMedia: true,
    horasReglamentarias: "08:30:00",
    sabado: null,
  },
  turno3_12hrs: {
    nombre: "Turno 3 12hrs (19:00 - 07:00)",
    entrada: { ideal: 19 * 60, tolerancia: 20 },
    salida: { ideal: 7 * 60, tolerancia: 20 },
    cruzaMedia: true,
    horasReglamentarias: "08:30:00",
    sabado: null,
  },
  mixto1: {
    nombre: "Mixto 1 (07:30 - 17:00)",
    entrada: { ideal: 7 * 60 + 30, tolerancia: 35 },
    salida: { ideal: 17 * 60, tolerancia: 60 },
    cruzaMedia: false,
    horasReglamentarias: "09:30:00",
    sabado: {
      nombre: "Mixto 1 Guardia (Sáb/Dom)",
      salida: { ideal: 12 * 60 + 30, tolerancia: 60 },
      horasReglamentarias: "05:00:00",
    },
  },
  mixto2: {
    nombre: "Mixto 2 (08:30 - 18:30)",
    entrada: { ideal: 8 * 60 + 30, tolerancia: 35 },
    salida: { ideal: 18 * 60 + 30, tolerancia: 60 },
    cruzaMedia: false,
    horasReglamentarias: "10:00:00",
    sabado: {
      nombre: "Mixto 2 Guardia (Sáb/Dom)",
      salida: { ideal: 13 * 60 + 30, tolerancia: 60 },
      horasReglamentarias: "05:00:00",
    },
  },
  mixto3: {
    nombre: "Mixto 3 (07:00 - 16:30)",
    entrada: { ideal: 7 * 60, tolerancia: 35 },
    salida: { ideal: 16 * 60 + 30, tolerancia: 60 },
    cruzaMedia: false,
    horasReglamentarias: "09:30:00",
    sabado: {
      nombre: "Mixto 3 Guardia (Sáb/Dom)",
      salida: { ideal: 12 * 60, tolerancia: 60 },
      horasReglamentarias: "05:00:00",
    },
  },
  mixto4: {
    nombre: "Mixto 4 (07:00 - 17:00)",
    entrada: { ideal: 7 * 60, tolerancia: 15 },
    salida: { ideal: 17 * 60, tolerancia: 60 },
    cruzaMedia: false,
    horasReglamentarias: "10:00:00",
    sabado: {
      nombre: "Mixto 4 Guardia (Sáb/Dom)",
      salida: { ideal: 12 * 60, tolerancia: 60 },
      horasReglamentarias: "05:00:00",
    },
  },
};

// ─────────────────────────────────────────────────────────────────────────────
// UTILIDADES
// ─────────────────────────────────────────────────────────────────────────────
function horaAMinutos(hora) {
  const [h, m] = hora.split(":").map(Number);
  return h * 60 + m;
}
function minutosAHora(m) {
  const h = Math.floor(Math.abs(m) / 60) % 24,
    mn = Math.abs(m) % 60;
  return h.toString().padStart(2, "0") + ":" + mn.toString().padStart(2, "0");
}
function minutosAHoraConSegundos(m) {
  return minutosAHora(m) + ":00";
}
function limpiarRegistros(registros) {
  const vistos = new Set();
  return registros
    .filter((r) => r.fecha_h)
    .map((r) => ({ ...r, hora_limpia: r.fecha_h.substring(0, 8) }))
    .filter((r) => {
      const c = r.hora_limpia.substring(0, 5);
      if (vistos.has(c)) return false;
      vistos.add(c);
      return true;
    })
    .sort((a, b) => horaAMinutos(a.hora_limpia) - horaAMinutos(b.hora_limpia));
}

// ─────────────────────────────────────────────────────────────────────────────
// resolverLapso — lógica UNIVERSAL por lapso (para TODOS los turnos)
//
// Para turnos que cruzan medianoche (turno3): usar resolverTurnoNocturno.
// Para el resto: tomar primer y último registro del día.
// ─────────────────────────────────────────────────────────────────────────────
function resolverTurnoNocturno(regAnterior, regActual, regSiguiente) {
  const nocturnos = regActual.filter(
    (r) => horaAMinutos(r.hora_limpia) >= 18 * 60,
  );
  const diurnos = regActual.filter(
    (r) => horaAMinutos(r.hora_limpia) < 12 * 60,
  );

  console.log(
    `[Nocturno] Nocturnos(>=18h): ${nocturnos.map((r) => r.hora_limpia).join(", ") || "ninguno"}`,
  );
  console.log(
    `[Nocturno] Diurnos(<12h): ${diurnos.map((r) => r.hora_limpia).join(", ") || "ninguno"}`,
  );

  // CASO A: hay entrada nocturna hoy → salida en el día siguiente
  if (nocturnos.length > 0) {
    const entrada = nocturnos[nocturnos.length - 1];
    if (regSiguiente.length > 0) {
      const salidaMañana =
        regSiguiente.find((r) => horaAMinutos(r.hora_limpia) < 15 * 60) ||
        regSiguiente[0];
      return {
        entrada,
        salida: salidaMañana,
        escenario: "nocturno_entrada_hoy_salida_mañana",
      };
    }
    return { entrada, salida: null, escenario: "nocturno_sin_salida" };
  }

  // CASO B: solo diurnos hoy → son la salida, entrada en el día anterior
  if (diurnos.length > 0) {
    const salida = diurnos[diurnos.length - 1];
    const nocturnosAyer = regAnterior.filter(
      (r) => horaAMinutos(r.hora_limpia) >= 18 * 60,
    );
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
    return {
      entrada: regActual[0],
      salida: regActual[regActual.length - 1],
      escenario: "lapso_directo",
    };
  }
  return null; // 1 registro → se maneja interactivamente en procesarPorLapso
}

// ─────────────────────────────────────────────────────────────────────────────
// seleccionarEntradaDiurna ← NUEVA
//
// Elige la ENTRADA real cuando hay varios registros en el día, anclando la
// selección a la entrada IDEAL del turno asignado. Esto descarta registros
// "fantasma" (p. ej. una salida del día anterior que quedó registrada de
// madrugada como 00:31, o dobles checadas por pasar cerca del checador).
//
//   1. Si hay registros dentro de ±margenMin de la entrada ideal del turno,
//      toma el PRIMERO de ellos (respeta dobles checadas cercanas y entradas
//      anticipadas/tardías legítimas).
//   2. Si ninguno cae en la ventana, toma el MÁS CERCANO a la ideal — nunca
//      el fantasma lejano.
//
// dist() contempla el cruce de medianoche para turnos nocturnos (turno3).
// No rompe casos sanos: si el primer registro ya es la entrada, lo devuelve.
// ─────────────────────────────────────────────────────────────────────────────
function seleccionarEntradaDiurna(def, regActual, margenMin = 90) {
  if (!regActual.length) return null;
  const ideal = def.entrada.ideal;
  const dist = (min) =>
    Math.min(
      Math.abs(min - ideal),
      Math.abs(min - ideal + 1440),
      Math.abs(min - ideal - 1440),
    );
  const enVentana = regActual.filter(
    (r) => dist(horaAMinutos(r.hora_limpia)) <= margenMin,
  );
  if (enVentana.length) return enVentana[0]; // primero dentro de la ventana
  return regActual.reduce((mejor, r) =>
    dist(horaAMinutos(r.hora_limpia)) < dist(horaAMinutos(mejor.hora_limpia))
      ? r
      : mejor,
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// seleccionarSalidaDespuesDe ← NUEVA (auxiliar)
//
// Dado el minuto de la entrada ya elegida, devuelve el ÚLTIMO registro del día
// posterior a esa entrada (la salida real). Si no hay ninguno posterior,
// devuelve el último registro disponible como respaldo.
// ─────────────────────────────────────────────────────────────────────────────
function seleccionarSalidaDespuesDe(entradaMin, regActual) {
  const posteriores = regActual.filter(
    (r) => horaAMinutos(r.hora_limpia) > entradaMin,
  );
  return posteriores.length
    ? posteriores[posteriores.length - 1]
    : regActual[regActual.length - 1];
}

// ─────────────────────────────────────────────────────────────────────────────
// fetchChecador — descarga registros de 3 días
// ─────────────────────────────────────────────────────────────────────────────
async function fetchChecador(noemp, date) {
  const fa = new Date(date + "T00:00:00");
  fa.setDate(fa.getDate() - 1);
  const fs = new Date(date + "T00:00:00");
  fs.setDate(fs.getDate() + 1);
  const fmt = (f) =>
    `${f.getFullYear()}-${String(f.getMonth() + 1).padStart(2, "0")}-${String(f.getDate()).padStart(2, "0")}`;

  const [rawA, rawC, rawS] = await Promise.all([
    fetch(
      `../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${fmt(fa)}`,
    ).then((r) => r.json()),
    fetch(
      `../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${date}`,
    ).then((r) => r.json()),
    fetch(
      `../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=${noemp}&fechabien=${fmt(fs)}`,
    ).then((r) => r.json()),
  ]);

  return {
    regAnterior: limpiarRegistros(rawA),
    regActual: limpiarRegistros(rawC),
    regSiguiente: limpiarRegistros(rawS),
    rawActual: rawC,
    fechaAnterior: fmt(fa),
    fechaActual: date,
    fechaSiguiente: fmt(fs),
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
    const r = await fetch(
      `php/index.php?${horaFinNueva ? "actualizarHoraFin" : "actualizarEstadoValidado"}`,
      { method: "POST", body: data },
    );
    const resp = await r.json();
    if (!resp.success)
      Swal.fire("Error", `No se pudo actualizar: ${resp.error}`, "error");
    return resp.success;
  } catch (err) {
    console.error("Error actualizarValidado:", err);
    return false;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// actualizarFilaDOM
// ─────────────────────────────────────────────────────────────────────────────
function actualizarFilaDOM(folioRegistro, registro) {
  const fila = document.querySelector(`#msg-${folioRegistro}`)?.closest("tr");
  if (!fila) return;
  const ec = fila.querySelector("td:nth-child(13)");
  const ac = fila.querySelector("td:nth-child(14)");
  if (ec)
    ec.innerHTML =
      registro?.validado == 1
        ? `<span class="badge bg-success">Validado</span>`
        : `<span class="badge bg-warning">No validado aún</span>`;
  if (ac) {
    ac.innerHTML =
      registro?.validado == 1
        ? `<span class="badge bg-success">Ya validado</span><small id="msg-${folioRegistro}" class="d-block mt-1"></small>`
        : `<button class="btn btn-sm btn-warning" onclick="validarInfo('${registro.NoEmpleadoSol}','${registro.fechaSol}','${registro.folioRegistro}','${registro.HoraInicio}','${registro.turnoAsignado}',${registro.folioRegistro})" id="btnValidar-${registro.folioRegistro}"><i class="fa-solid fa-eye"></i> Validar T. extra</button>
               <button class="btn btn-sm btn-danger" onclick="window.deletesub(${registro.folioRegistro})" id="btnEliminar-${registro.folioRegistro}" hidden><i class="fas fa-times"></i> Eliminar</button>
               <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// ajustarHorasRegistro — actualiza HoraInicio y HoraFin en BD
// ─────────────────────────────────────────────────────────────────────────────
async function ajustarHorasRegistro(
  folioRegistro,
  nuevaHoraInicio,
  nuevaHoraFin,
) {
  const d = new FormData();
  d.append("folioRegistro", folioRegistro);
  d.append("nuevaHoraFin", nuevaHoraFin);
  d.append("nuevaHoraInicio", nuevaHoraInicio);
  try {
    const r = await fetch("php/index.php?actualizarHorasReales", {
      method: "POST",
      body: d,
    });
    const resp = await r.json();
    if (!resp.success) {
      await actualizarValidado(folioRegistro, nuevaHoraFin);
      return false;
    }
    const reg = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    );
    if (reg) {
      reg.HoraInicio = nuevaHoraInicio;
      reg.HoraFin = nuevaHoraFin;
      reg.validado = 1;
    }
    actualizarFilaDOM(folioRegistro, reg);
    return true;
  } catch (err) {
    await actualizarValidado(folioRegistro, nuevaHoraFin);
    return false;
  }
}

async function crearRegistroExcedente(
  datosRegistro,
  excedenteMin,
  motivo,
  horaInicioExcedente = null,
  horaFinExcedente = null,
) {
  // Si no se pasan horas explícitas, calcular desde el fin del turno (comportamiento original)
  if (!horaInicioExcedente || !horaFinExcedente) {
    const def = CATALOGO_TURNOS[datosRegistro.turnoAsignado];
    const esSab = [6, 0].includes(
      new Date(datosRegistro.fechaSol + "T00:00:00").getDay(),
    );
    const salidaDef = esSab && def?.sabado ? def.sabado.salida : def?.salida;
    const finMin = salidaDef ? salidaDef.ideal : 0;
    horaInicioExcedente = minutosAHoraConSegundos(finMin);
    horaFinExcedente = minutosAHoraConSegundos(
      (finMin + excedenteMin) % (24 * 60),
    );
  }

  const data = new FormData();
  data.append("noemp", datosRegistro.NoEmpleadoSol);
  data.append("fechainput", datosRegistro.fechaSol);
  data.append("horai", horaInicioExcedente);
  data.append("horaf", horaFinExcedente);
  data.append("maquina", datosRegistro.maquina || "");
  data.append("motivos", motivo);
  data.append("razon", datosRegistro.razon || "");
  data.append("folio", datosRegistro.id);
  data.append("turnosel", datosRegistro.turnoAsignado);
  data.append("nombre", datosRegistro.NombreEmpleadoSol);
  data.append("esExcedente", "1");
  data.append("validado", "1");

  try {
    const r = await fetch("php/index.php?guardartiempoextra", {
      method: "POST",
      body: data,
    });
    const resp = await r.json();
    if (resp === "Listo") {
      await Swal.fire(
        "Excedente registrado",
        `Se creó un registro adicional de <b>${minutosAHoraConSegundos(excedenteMin)}</b> hrs.<br>
                 Inicio: <b>${horaInicioExcedente}</b> | Fin: <b>${horaFinExcedente}</b>`,
        "success",
      );
    } else {
      Swal.fire(
        "Advertencia",
        `No se pudo crear el registro de excedente: ${JSON.stringify(resp)}`,
        "warning",
      );
    }
  } catch (err) {
    console.error("Error crearRegistroExcedente:", err);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarPorLapso — función genérica para procesar cualquier turno por lapso
//
// Para TODOS los turnos (turno1, turno2, mixtos) en motivos normales.
// Si hay 1 solo registro hoy → muestra los primeros 2 registros del día
// siguiente para que el supervisor elija cuál es la hora de salida real.
// Siempre ajusta HoraInicio y HoraFin en BD con las horas reales.
// ─────────────────────────────────────────────────────────────────────────────
async function procesarPorLapso(
  noemp,
  date,
  folioRegistro,
  turnoAsignado,
  tipoEmpleado,
  datosRegistro,
  mostrarError,
) {
  const def = CATALOGO_TURNOS[turnoAsignado];
  if (!def) {
    mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`);
    return;
  }

  const esSabado = [6, 0].includes(new Date(date + "T00:00:00").getDay());
  const hrsReglDef =
    esSabado && def.sabado
      ? def.sabado.horasReglamentarias
      : def.horasReglamentarias;
  const hrsReglMin = horaAMinutos(hrsReglDef);

  console.log(
    `═══ procesarPorLapso: folio=${folioRegistro} emp=${noemp} fecha=${date} turno=${turnoAsignado} regl=${hrsReglDef}`,
  );

  let checador;
  try {
    checador = await fetchChecador(noemp, date);
  } catch (e) {
    mostrarError("Error al conectar con el checador.");
    return;
  }

  const regHoyStr =
    checador.regActual.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regAyerStr =
    checador.regAnterior.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regMañanaStr =
    checador.regSiguiente.map((r) => r.hora_limpia).join(", ") || "ninguno";

  console.log(
    `Ayer(${checador.fechaAnterior}): ${regAyerStr} | Hoy(${checador.fechaActual}): ${regHoyStr} | Mañana(${checador.fechaSiguiente}): ${regMañanaStr}`,
  );

  // Sin registros hoy → no se puede validar
  if (checador.regActual.length === 0) {
    mostrarError(`No hay ningún registro en el checador para el día ${date}.`, {
      registrosHoy: "ninguno",
      registrosAyer: regAyerStr,
      registrosMañana: regMañanaStr,
    });
    return;
  }

  let primerReg, ultimoReg, diaSalida;

  // ── CASO: 1 solo registro hoy → pedir al supervisor elegir la salida ──────
  if (checador.regActual.length === 1) {
    primerReg = checador.regActual[0];

    if (checador.regSiguiente.length === 0) {
      mostrarError(
        `Solo se encontró 1 registro hoy (<b>${primerReg.hora_limpia}</b>) y no hay registros en el día siguiente (${checador.fechaSiguiente}) para determinar la salida.`,
        {
          registrosHoy: regHoyStr,
          registrosAyer: regAyerStr,
          registrosMañana: "ninguno",
        },
      );
      return;
    }

    // Mostrar solo los primeros 2 registros del día siguiente como opciones
    const candidatos = checador.regSiguiente.slice(0, 2);
    const opcionesHTML = candidatos
      .map(
        (r, i) =>
          `<div style="margin:8px 0;">
                <button id="opcion-salida-${i}" class="swal2-confirm swal2-styled" style="background:#4caf50; margin:4px; padding:8px 20px; font-size:14px;">
                    ${r.hora_limpia} <small style="opacity:0.8">(${checador.fechaSiguiente})</small>
                </button>
             </div>`,
      )
      .join("");

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
          document
            .getElementById(`opcion-salida-${i}`)
            ?.addEventListener("click", () => {
              Swal.close();
              // Guardar la elección en el elemento del DOM para recuperarla
              document.getElementById(`opcion-salida-${i}`).dataset.elegida =
                r.hora_limpia;
              window._salidaElegidaLapso = r.hora_limpia;
            });
        });
      },
    });

    // Si canceló → no validar
    if (!window._salidaElegidaLapso) {
      const fid = window.Reportes.data.find(
        (e) => e.folioRegistro == folioRegistro,
      )?.id;
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
      cancelButtonText: "No, cancelar",
    });

    if (!confirmacion.isConfirmed) {
      const fid = window.Reportes.data.find(
        (e) => e.folioRegistro == folioRegistro,
      )?.id;
      if (fid) window.Reportes.evaluarFolio(fid);
      return;
    }

    ultimoReg = { hora_limpia: salidaElegida };
    diaSalida = checador.fechaSiguiente;
  } else {
    // ── CASO NORMAL: ≥2 registros hoy ────────────────────────────────────
    // Entrada: la más cercana a la entrada ideal del turno (descarta fantasmas)
    primerReg = seleccionarEntradaDiurna(def, checador.regActual);
    // Salida: último registro DESPUÉS de la entrada elegida
    const entMin = horaAMinutos(primerReg.hora_limpia);
    ultimoReg = seleccionarSalidaDespuesDe(entMin, checador.regActual);
    diaSalida = checador.fechaActual;
  }

  // ── Calcular excedente ────────────────────────────────────────────────────
  const entradaMin = horaAMinutos(primerReg.hora_limpia);
  let salidaMin = horaAMinutos(ultimoReg.hora_limpia);
  if (salidaMin < entradaMin) salidaMin += 1440;

  const minutosTrabajados = salidaMin - entradaMin;
  const excedente = Math.max(0, minutosTrabajados - hrsReglMin);
  const horaFinReal = ultimoReg.hora_limpia;
  const horaInicioExtra =
    excedente > 0
      ? minutosAHoraConSegundos((salidaMin - excedente + 24 * 60) % (24 * 60))
      : horaFinReal;

  console.log(
    `Entrada(${checador.fechaActual}): ${primerReg.hora_limpia} | Salida(${diaSalida}): ${ultimoReg.hora_limpia} | Trabajado: ${minutosTrabajados}min | Excedente: ${excedente}min`,
  );

  if (minutosTrabajados < hrsReglMin - 5) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: `${turnoAsignado} — No apto`,
      html: `El empleado trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero el turno requiere <b>${hrsReglDef}</b>.<br><br>
                    Turno: <b>${turnoAsignado}</b><br>
                    Entrada (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                    Salida (${diaSalida}): <b>${ultimoReg.hora_limpia}</b><br>
                    Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>
                    Registros ayer (${checador.fechaAnterior}): <b>${regAyerStr}</b><br>
                    Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  if (excedente < 50) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: `${turnoAsignado} — No apto (excedente insuficiente)`,
      html: `El empleado completó las horas reglamentarias pero el excedente (<b>${minutosAHoraConSegundos(excedente)}</b>) no alcanza los 50 minutos mínimos.<br><br>
                    Turno: <b>${turnoAsignado}</b><br>
                    Entrada (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                    Salida (${diaSalida}): <b>${ultimoReg.hora_limpia}</b><br>
                    Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                    Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>
                    Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  // ── Apto → mostrar resultado y ajustar horas en BD ────────────────────────
  window.Reportes.marcarRegistroComoApto(folioRegistro);
  await Swal.fire({
    title: `${turnoAsignado} — Apto`,
    html: `Turno: <b>${turnoAsignado}</b><br>
                Horas reglamentarias: <b>${hrsReglDef}</b><br>
                Entrada (${checador.fechaActual}): <b>${primerReg.hora_limpia}</b><br>
                Salida (${diaSalida}): <b>${ultimoReg.hora_limpia}</b><br>
                Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                Excedente (T. extra): <b>${minutosAHoraConSegundos(excedente)}</b><br>
                Tipo: <b>${tipoEmpleado}</b><br><br>
                Horas T. extra ajustadas:<br>
                Inicio: <b>${horaInicioExtra}</b> | Fin: <b>${horaFinReal}</b>`,
    icon: "success",
  });

  // Siempre ajustar horas (incluso si no hubo excedente para dejar las horas reales)
  const ok = await ajustarHorasRegistro(
    folioRegistro,
    horaInicioExtra,
    horaFinReal,
  );
  if (ok)
    await Swal.fire(
      "Actualización realizada",
      `Registro validado.<br>Horas ajustadas: <b>${horaInicioExtra}</b> → <b>${horaFinReal}</b>`,
      "success",
    );

  const fid = window.Reportes.data.find(
    (e) => e.folioRegistro == folioRegistro,
  )?.id;
  if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarDoblete
//
// Lógica:
//   · Entrada = registro más cercano a la entrada ideal del turno
//     (seleccionarEntradaDiurna), descartando fantasmas de madrugada.
//   · Salida  = hoy o día siguiente (resolverSalidaConLapso). Esto arregla el
//     caso del doblete en un segundo (14:48 → 07:00 del día siguiente).
//   · Excedente = (salida - entrada) - reglamentarias.
//   · Doblete HACIA ADELANTE: el extra va DESPUÉS de la salida normal del turno:
//        HoraInicio = salida ideal del turno
//        HoraFin    = salida ideal + excedente
// ─────────────────────────────────────────────────────────────────────────────
async function procesarDoblete(
  noemp,
  date,
  folioRegistro,
  turnoAsignado,
  tipoEmpleado,
  datosRegistro,
  mostrarError,
) {
  const def = CATALOGO_TURNOS[turnoAsignado];
  if (!def) {
    mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`);
    return;
  }

  const esSabado = [6, 0].includes(new Date(date + "T00:00:00").getDay());
  const hrsReglDef =
    esSabado && def.sabado
      ? def.sabado.horasReglamentarias
      : def.horasReglamentarias;
  const hrsReglMin = horaAMinutos(hrsReglDef);
  const esTurnoNocturno = ["turno3", "turno3_12hrs"].includes(turnoAsignado);

  console.log(
    `═══ procesarDoblete: folio=${folioRegistro} turno=${turnoAsignado} regl=${hrsReglDef}`,
  );

  let checador;
  try {
    checador = await fetchChecador(noemp, date);
  } catch (e) {
    mostrarError("Error al conectar con el checador.");
    return;
  }

  const regHoyStr =
    checador.regActual.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regAyerStr =
    checador.regAnterior.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regMañanaStr =
    checador.regSiguiente.map((r) => r.hora_limpia).join(", ") || "ninguno";

  if (checador.regActual.length < 1) {
    mostrarError(`No hay registros en el checador para el día ${date}.`, {
      registrosHoy: "ninguno",
      registrosAyer: regAyerStr,
      registrosMañana: regMañanaStr,
    });
    return;
  }

  // ── Resolver entrada y salida (cruzando medianoche si hace falta) ──────────
  let entradaReg, salidaReg, diaSalida;

  if (esTurnoNocturno) {
    const resol = resolverTurnoNocturno(
      checador.regAnterior,
      checador.regActual,
      checador.regSiguiente,
    );
    if (!resol || !resol.entrada || !resol.salida) {
      mostrarError(
        `No se pudo determinar entrada/salida del turno nocturno para el día ${date}.`,
        {
          registrosHoy: regHoyStr,
          registrosAyer: regAyerStr,
          registrosMañana: regMañanaStr,
        },
      );
      return;
    }
    entradaReg = resol.entrada;
    salidaReg = resol.salida;
    diaSalida =
      resol.escenario.includes("mañana") ||
      resol.escenario.includes("siguiente")
        ? checador.fechaSiguiente
        : checador.fechaActual;
  } else {
    // Diurnos / mixtos: entrada = registro más cercano a la entrada ideal del turno
    entradaReg = seleccionarEntradaDiurna(def, checador.regActual);
    const entradaMin = horaAMinutos(entradaReg.hora_limpia);
    const salidaInfo = resolverSalidaConLapso(
      entradaMin,
      checador.regActual,
      checador.regSiguiente,
      hrsReglMin,
      false,
    );
    if (!salidaInfo || !salidaInfo.salida) {
      mostrarError(
        `Se encontró la entrada (<b>${entradaReg.hora_limpia}</b>) pero no hay registro de salida ni hoy ni en el día siguiente (${checador.fechaSiguiente}).`,
        { registrosHoy: regHoyStr, registrosMañana: regMañanaStr },
      );
      return;
    }
    salidaReg = salidaInfo.salida;
    diaSalida =
      salidaInfo.dia === "siguiente"
        ? checador.fechaSiguiente
        : checador.fechaActual;
  }

  // ── Calcular lapso trabajado ──────────────────────────────────────────────
  const entradaMin = horaAMinutos(entradaReg.hora_limpia);
  let salidaMin = horaAMinutos(salidaReg.hora_limpia);
  if (diaSalida === checador.fechaSiguiente) salidaMin += 1440;
  else if (salidaMin < entradaMin) salidaMin += 1440;

  const minutosTrabajados = salidaMin - entradaMin;
  const excedente = Math.max(0, minutosTrabajados - hrsReglMin);

  // Doblete hacia adelante: el extra arranca al terminar el turno normal
  const horaInicioExtra = minutosAHoraConSegundos(def.salida.ideal);
  const horaFinExtra = minutosAHoraConSegundos(
    (def.salida.ideal + excedente) % 1440,
  );

  console.log(
    `Entrada (${esTurnoNocturno ? "nocturno" : checador.fechaActual}): ${entradaReg.hora_limpia} | Salida (${diaSalida}): ${salidaReg.hora_limpia} | Trabajado: ${minutosTrabajados}min | Excedente: ${excedente}min`,
  );

  if (minutosTrabajados < hrsReglMin - 5) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: "Doblete — No apto",
      html: `El empleado trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero el turno requiere <b>${hrsReglDef}</b>.<br><br>
                   Turno: <b>${turnoAsignado}</b><br>
                   Entrada: <b>${entradaReg.hora_limpia}</b><br>
                   Salida (${diaSalida}): <b>${salidaReg.hora_limpia}</b><br>
                   Registros hoy: <b>${regHoyStr}</b><br>
                   Registros ayer (${checador.fechaAnterior}): <b>${regAyerStr}</b><br>
                   Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>
                   Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  if (excedente < 50) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: "Doblete — No apto (excedente insuficiente)",
      html: `El empleado completó las reglamentarias pero el excedente (<b>${minutosAHoraConSegundos(excedente)}</b>) no alcanza los 50 minutos mínimos.<br><br>
                   Turno: <b>${turnoAsignado}</b><br>
                   Entrada: <b>${entradaReg.hora_limpia}</b><br>
                   Salida (${diaSalida}): <b>${salidaReg.hora_limpia}</b><br>
                   Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                   Registros hoy: <b>${regHoyStr}</b><br>
                   Registros mañana: <b>${regMañanaStr}</b><br>
                   Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  window.Reportes.marcarRegistroComoApto(folioRegistro);
  await Swal.fire({
    title: "Doblete — Apto",
    html: `Turno: <b>${turnoAsignado}</b><br>
               Horas reglamentarias: <b>${hrsReglDef}</b><br>
               Entrada: <b>${entradaReg.hora_limpia}</b><br>
               Salida (${diaSalida}): <b>${salidaReg.hora_limpia}</b><br>
               Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
               Excedente (T. extra doblete): <b>${minutosAHoraConSegundos(excedente)}</b><br>
               Tipo: <b>${tipoEmpleado}</b><br><br>
               Horas T. extra (hacia adelante):<br>
               Inicio: <b>${horaInicioExtra}</b> | Fin: <b>${horaFinExtra}</b>`,
    icon: "success",
  });

  const ok = await ajustarHorasRegistro(
    folioRegistro,
    horaInicioExtra,
    horaFinExtra,
  );
  if (ok)
    await Swal.fire(
      "Actualización realizada",
      `Registro validado.<br>Horas ajustadas: <b>${horaInicioExtra}</b> → <b>${horaFinExtra}</b>`,
      "success",
    );

  const fid = window.Reportes.data.find(
    (e) => e.folioRegistro == folioRegistro,
  )?.id;
  if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarDescansoOFestivo
// (SIN CAMBIOS — aquí se respeta exactamente lo que marque el checador)
// ─────────────────────────────────────────────────────────────────────────────
async function procesarDescansoOFestivo(
  noemp,
  date,
  folioRegistro,
  turnoAsignado,
  motivo,
  tipoEmpleado,
  datosRegistro,
  mostrarError,
) {
  const def = CATALOGO_TURNOS[turnoAsignado];
  if (!def) {
    mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`);
    return;
  }

  // Para descanso/festivo siempre horas completas del turno, sin regla de guardia
  const esSabado = false;
  const LIMITE_HRS_MIN = 8 * 60; // 8 hrs en minutos — umbral para dividir en excedente
  const MINIMO_TE_MIN = 50; // mínimo para ser apto

  const esTurnoNocturno = ["turno3", "turno3_12hrs"].includes(turnoAsignado);
  const esMixtoFestivo =
    motivo === MOTIVO_DIA_FESTIVO && TURNOS_MIXTOS.includes(turnoAsignado);

  let checador;
  try {
    checador = await fetchChecador(noemp, date);
  } catch (e) {
    mostrarError("Error al conectar con el checador.");
    return;
  }

  const regHoyStr =
    checador.regActual.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regAyerStr =
    checador.regAnterior.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regMañanaStr =
    checador.regSiguiente.map((r) => r.hora_limpia).join(", ") || "ninguno";

  if (!checador.rawActual.length) {
    mostrarError(`No hay registros en el checador para el día ${date}.`, {
      registrosHoy: "ninguno",
      registrosAyer: regAyerStr,
    });
    return;
  }

  // ── Resolver entrada/salida según tipo de turno ───────────────────────────
  let resolucion;
  if (esTurnoNocturno) {
    resolucion = resolverTurnoNocturno(
      checador.regAnterior,
      checador.regActual,
      checador.regSiguiente,
    );
    if (!resolucion || !resolucion.entrada || !resolucion.salida) {
      mostrarError(
        `No se pudo determinar entrada/salida del turno nocturno para el día ${date}.`,
        {
          registrosHoy: regHoyStr,
          registrosAyer: regAyerStr,
          registrosMañana: regMañanaStr,
        },
      );
      return;
    }
  } else {
    // Diurnos y mixtos: primer y último registro del día
    if (checador.regActual.length < 2) {
      mostrarError(
        `Se necesitan al menos 2 registros en el checador del día ${date}.`,
        {
          registrosHoy: regHoyStr,
          registrosAyer: regAyerStr,
          registrosMañana: regMañanaStr,
        },
      );
      return;
    }
    resolucion = {
      entrada: checador.regActual[0],
      salida: checador.regActual[checador.regActual.length - 1],
      escenario: "lapso_directo",
    };
  }

  const entradaReal = resolucion.entrada.hora_limpia;
  const salidaReal = resolucion.salida.hora_limpia;

  const entradaMin = horaAMinutos(entradaReal);
  let salidaMin = horaAMinutos(salidaReal);
  if (salidaMin < entradaMin) salidaMin += 1440;

  const minutosTrabajados = salidaMin - entradaMin;

  console.log(
    `═══ procesarDescansoOFestivo: folio=${folioRegistro} turno=${turnoAsignado} motivo=${motivo}`,
  );
  console.log(
    `Entrada: ${entradaReal} | Salida: ${salidaReal} | Trabajado: ${minutosTrabajados}min`,
  );

  // ── Menos de 55 min → no apto ─────────────────────────────────────────────
  if (minutosTrabajados < MINIMO_TE_MIN) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: `${motivo === MOTIVO_DESCANSO_TRABAJADO ? "Descanso trabajado" : "Día festivo"} — No apto`,
      html: `El empleado trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b>, no alcanza los 50 minutos mínimos.<br><br>
                    Turno: <b>${turnoAsignado}</b><br>
                    Entrada real (${date}): <b>${entradaReal}</b><br>
                    Salida real: <b>${salidaReal}</b><br>
                    Registros hoy: <b>${regHoyStr}</b><br>
                    Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  // ── 55 min a 8 hrs → ajustar horas reales, sin registro extra ─────────────
  if (minutosTrabajados <= LIMITE_HRS_MIN) {
    window.Reportes.marcarRegistroComoApto(folioRegistro);
    await Swal.fire({
      title: `${motivo === MOTIVO_DESCANSO_TRABAJADO ? "Descanso trabajado" : "Día festivo"} — Apto`,
      html: `Turno: <b>${turnoAsignado}</b><br>
                    Entrada real (${date}): <b>${entradaReal}</b><br>
                    Salida real: <b>${salidaReal}</b><br>
                    Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                    Tipo: <b>${tipoEmpleado}</b><br><br>
                    Horas ajustadas: <b>${entradaReal}</b> → <b>${salidaReal}</b>`,
      icon: "success",
    });
    const ok = await ajustarHorasRegistro(
      folioRegistro,
      entradaReal,
      salidaReal,
    );
    if (ok)
      await Swal.fire(
        "Actualización realizada",
        `Registro validado.<br>Horas ajustadas: <b>${entradaReal}</b> → <b>${salidaReal}</b>`,
        "success",
      );

    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  // ── Más de 8 hrs → ajustar a 8 hrs + crear registro excedente ─────────────
  const excedente = minutosTrabajados - LIMITE_HRS_MIN;
  const salidaOcho = minutosAHoraConSegundos(
    (entradaMin + LIMITE_HRS_MIN) % (24 * 60),
  );
  const inicioExcedente = salidaOcho;
  const finExcedente = salidaReal;

  window.Reportes.marcarRegistroComoApto(folioRegistro);
  await Swal.fire({
    title: `${motivo === MOTIVO_DESCANSO_TRABAJADO ? "Descanso trabajado" : "Día festivo"} — Apto (más de 8 hrs)`,
    html: `Turno: <b>${turnoAsignado}</b><br>
                Entrada real (${date}): <b>${entradaReal}</b><br>
                Salida real: <b>${salidaReal}</b><br>
                Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                Tipo: <b>${tipoEmpleado}</b><br><br>
                Registro principal ajustado a 8 hrs:<br>
                <b>${entradaReal}</b> → <b>${salidaOcho}</b><br><br>
                Registro excedente (<b>${minutosAHoraConSegundos(excedente)}</b>):<br>
                <b>${inicioExcedente}</b> → <b>${finExcedente}</b>`,
    icon: "success",
  });

  // Ajustar el registro principal a 8 hrs exactas
  const ok = await ajustarHorasRegistro(folioRegistro, entradaReal, salidaOcho);
  if (ok) {
    await Swal.fire(
      "Registro principal ajustado",
      `Horas ajustadas a 8 hrs: <b>${entradaReal}</b> → <b>${salidaOcho}</b>`,
      "success",
    );
  }

  // Crear registro excedente con el mismo motivo y razón
  await crearRegistroExcedente(
    datosRegistro,
    excedente,
    motivo,
    inicioExcedente,
    finExcedente,
  );

  const fid = window.Reportes.data.find(
    (e) => e.folioRegistro == folioRegistro,
  )?.id;
  if (fid) window.Reportes.evaluarFolio(fid);
}

async function procesarTurnoNocturno(
  noemp,
  date,
  folioRegistro,
  turnoAsignado,
  tipoEmpleado,
  datosRegistro,
  mostrarError,
) {
  const def = CATALOGO_TURNOS[turnoAsignado];
  if (!def) {
    mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`);
    return;
  }
  const hrsReglMin = horaAMinutos(def.horasReglamentarias);

  let checador;
  try {
    checador = await fetchChecador(noemp, date);
  } catch (e) {
    mostrarError("Error al conectar con el checador.");
    return;
  }

  const regHoyStr =
    checador.regActual.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regAyerStr =
    checador.regAnterior.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regMañanaStr =
    checador.regSiguiente.map((r) => r.hora_limpia).join(", ") || "ninguno";

  const resolucion = resolverTurnoNocturno(
    checador.regAnterior,
    checador.regActual,
    checador.regSiguiente,
  );

  if (!resolucion || !resolucion.entrada || !resolucion.salida) {
    const detalle = !resolucion
      ? "Sin registros nocturnos ni diurnos."
      : !resolucion.entrada
        ? `Salida detectada (<b>${resolucion.salida?.hora_limpia}</b>) pero sin entrada nocturna en ${checador.fechaAnterior}.`
        : `Entrada detectada (<b>${resolucion.entrada?.hora_limpia}</b>) pero sin salida en ${checador.fechaSiguiente}.`;
    mostrarError(
      `No se pudo determinar entrada/salida del turno nocturno para el día ${date}.<br>${detalle}`,
      {
        registrosHoy: regHoyStr,
        registrosAyer: regAyerStr,
        registrosMañana: regMañanaStr,
        entradaDetectada: resolucion?.entrada?.hora_limpia || "no encontrada",
        salidaDetectada: resolucion?.salida?.hora_limpia || "no encontrada",
      },
    );
    return;
  }

  const entradaMin = horaAMinutos(resolucion.entrada.hora_limpia);
  let salidaMin = horaAMinutos(resolucion.salida.hora_limpia);
  if (salidaMin < entradaMin) salidaMin += 1440;
  const minutosTrabajados = salidaMin - entradaMin;
  const excedente = Math.max(0, minutosTrabajados - hrsReglMin);

  const diaEntrada = resolucion.escenario.includes("ayer")
    ? checador.fechaAnterior
    : checador.fechaActual;
  const diaSalida =
    resolucion.escenario.includes("mañana") ||
    resolucion.escenario.includes("siguiente")
      ? checador.fechaSiguiente
      : checador.fechaActual;

  // ── Determinar dirección del excedente ────────────────────────────────────
  // turno3_12hrs (19:00-07:00) y turno2_13hrs (10:30-22:30): el turno empieza
  // ANTES de su hora reglamentaria base, por eso el excedente va al INICIO.
  //   HoraInicio T.extra = entrada real
  //   HoraFin    T.extra = entrada real + excedente
  //
  // turno3 normal (22:30-07:00): el excedente va al FINAL (después de las 07:00).
  //   HoraFin    T.extra = salida real
  //   HoraInicio T.extra = salida real - excedente
  // ─────────────────────────────────────────────────────────────────────────
  const esTurnoExtendido = ["turno3_12hrs", "turno2_13hrs"].includes(
    turnoAsignado,
  );

  let horaInicioExtra, horaFinExtra;
  if (esTurnoExtendido) {
    // Excedente al inicio — el empleado llegó antes de su hora base
    horaInicioExtra = resolucion.entrada.hora_limpia;
    horaFinExtra = minutosAHoraConSegundos(
      (entradaMin + excedente) % (24 * 60),
    );
  } else {
    // Excedente al final — el empleado se quedó después de su hora de salida
    horaFinExtra = resolucion.salida.hora_limpia;
    horaInicioExtra =
      excedente > 0
        ? minutosAHoraConSegundos((salidaMin - excedente + 24 * 60) % (24 * 60))
        : horaFinExtra;
  }

  if (minutosTrabajados < hrsReglMin - 5) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: `${turnoAsignado} — No apto`,
      html: `Trabajó <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> pero requiere <b>${def.horasReglamentarias}</b>.<br><br>
                   Entrada (${diaEntrada}): <b>${resolucion.entrada.hora_limpia}</b><br>
                   Salida (${diaSalida}): <b>${resolucion.salida.hora_limpia}</b><br>
                   Hoy: <b>${regHoyStr}</b> | Ayer: <b>${regAyerStr}</b> | Mañana: <b>${regMañanaStr}</b><br>
                   Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }
  if (excedente < 50) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: `${turnoAsignado} — No apto (excedente insuficiente)`,
      html: `Excedente <b>${minutosAHoraConSegundos(excedente)}</b> no alcanza los 50 min mínimos.<br><br>
                   Entrada (${diaEntrada}): <b>${resolucion.entrada.hora_limpia}</b><br>
                   Salida (${diaSalida}): <b>${resolucion.salida.hora_limpia}</b><br>
                   Total trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b><br>
                   Hoy: <b>${regHoyStr}</b> | Ayer: <b>${regAyerStr}</b> | Mañana: <b>${regMañanaStr}</b><br>
                   Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  window.Reportes.marcarRegistroComoApto(folioRegistro);
  await Swal.fire({
    title: `${turnoAsignado} — Apto`,
    html: `Reglamentarias: <b>${def.horasReglamentarias}</b><br>
               Entrada (${diaEntrada}): <b>${resolucion.entrada.hora_limpia}</b><br>
               Salida (${diaSalida}): <b>${resolucion.salida.hora_limpia}</b><br>
               Trabajado: <b>${minutosAHoraConSegundos(minutosTrabajados)}</b> | Excedente: <b>${minutosAHoraConSegundos(excedente)}</b><br>
               Tipo: <b>${tipoEmpleado}</b><br><br>
               ${
                 esTurnoExtendido
                   ? `Excedente al <b>inicio</b> del turno:`
                   : `Excedente al <b>final</b> del turno:`
               }<br>
               Inicio T. extra: <b>${horaInicioExtra}</b> | Fin T. extra: <b>${horaFinExtra}</b>`,
    icon: "success",
  });
  const ok = await ajustarHorasRegistro(
    folioRegistro,
    horaInicioExtra,
    horaFinExtra,
  );
  if (ok)
    await Swal.fire(
      "Actualización realizada",
      `Horas ajustadas: <b>${horaInicioExtra}</b> → <b>${horaFinExtra}</b>`,
      "success",
    );
  const fid = window.Reportes.data.find(
    (e) => e.folioRegistro == folioRegistro,
  )?.id;
  if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// resolverSalidaConLapso
//
// Encuentra la salida real de un lapso que PUEDE cruzar la medianoche
// (anticipos y dobletes de turnos largos / nocturnos).
//
//   entradaMin         : minuto de la entrada ya detectada (0–1439)
//   regActual          : registros del día (limpios y ordenados)
//   regSiguiente       : registros del día siguiente
//   hrsReglMin         : horas reglamentarias del turno, en minutos
//   forzarDiaSiguiente : true para turno3 / turno3_12hrs (la salida SIEMPRE
//                        está en la mañana del día siguiente)
//
// Devuelve { salida, span, dia } o null.
//   · span ya contempla el cruce de medianoche (+24h cuando aplica)
//   · dia : "hoy" | "siguiente"
// ─────────────────────────────────────────────────────────────────────────────
function resolverSalidaConLapso(
  entradaMin,
  regActual,
  regSiguiente,
  hrsReglMin,
  forzarDiaSiguiente = false,
) {
  // Salida candidata HOY: último registro posterior a la entrada
  const candidatosHoy = regActual.filter(
    (r) => horaAMinutos(r.hora_limpia) > entradaMin,
  );
  const salidaHoy = candidatosHoy.length
    ? candidatosHoy[candidatosHoy.length - 1]
    : null;

  // Salida candidata MAÑANA: registro matutino (< 15:00) o el primero disponible
  const salidaMañana = regSiguiente.length
    ? regSiguiente.find((r) => horaAMinutos(r.hora_limpia) < 15 * 60) ||
      regSiguiente[0]
    : null;

  const spanHoy = salidaHoy
    ? (horaAMinutos(salidaHoy.hora_limpia) < entradaMin
        ? horaAMinutos(salidaHoy.hora_limpia) + 1440
        : horaAMinutos(salidaHoy.hora_limpia)) - entradaMin
    : null;

  const spanMañana = salidaMañana
    ? horaAMinutos(salidaMañana.hora_limpia) + 1440 - entradaMin
    : null;

  // Turno nocturno → la salida SIEMPRE es del día siguiente
  if (forzarDiaSiguiente && salidaMañana) {
    return { salida: salidaMañana, span: spanMañana, dia: "siguiente" };
  }
  // Si HOY ya completa las reglamentarias → salida de hoy (no cruzó medianoche)
  if (spanHoy !== null && spanHoy >= hrsReglMin) {
    return { salida: salidaHoy, span: spanHoy, dia: "hoy" };
  }
  // Si MAÑANA completa las reglamentarias → cruzó la medianoche
  if (spanMañana !== null && spanMañana >= hrsReglMin) {
    return { salida: salidaMañana, span: spanMañana, dia: "siguiente" };
  }
  // Ninguno completa: devolver el lapso más largo disponible
  if (spanMañana !== null && (spanHoy === null || spanMañana > spanHoy)) {
    return { salida: salidaMañana, span: spanMañana, dia: "siguiente" };
  }
  if (spanHoy !== null) return { salida: salidaHoy, span: spanHoy, dia: "hoy" };
  return null;
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarAnticipo
//
// Lógica:
//   1. Entrada = registro dentro del rango solicitado (con margen de tolerancia
//      de ±30 min). Se elige el MÁS CERCANO a la hora de inicio solicitada,
//      descartando otros registros del día (salidas/fantasmas).
//   2. Salida  = se busca con resolverSalidaConLapso. Para turno3/turno3_12hrs
//      se fuerza la salida del día siguiente; para el resto se usa hoy salvo que
//      hoy no complete la jornada (entonces cruza al día siguiente).
//   3. Excedente = (salida - entrada) - reglamentarias.
//   4. El excedente se coloca ANTES de la entrada normal del turno:
//        HoraFin    = entrada ideal del turno
//        HoraInicio = entrada ideal del turno - excedente
// ─────────────────────────────────────────────────────────────────────────────
async function procesarAnticipo(
  noemp,
  date,
  folioRegistro,
  turnoAsignado,
  tipoEmpleado,
  datosRegistro,
  mostrarError,
) {
  const def = CATALOGO_TURNOS[turnoAsignado];
  if (!def) {
    mostrarError(`Turno <b>${turnoAsignado}</b> no reconocido.`);
    return;
  }
  const hrsReglMin = horaAMinutos(def.horasReglamentarias);
  const esTurnoNocturno = ["turno3", "turno3_12hrs"].includes(turnoAsignado);

  console.log(">>> procesarAnticipo datosRegistro:", datosRegistro);

  if (!datosRegistro.HoraInicio || !datosRegistro.HoraFin) {
    mostrarError("No se encontraron horas solicitadas en el registro.");
    return;
  }

  const horaiSolicitadaMin = horaAMinutos(datosRegistro.HoraInicio);
  const horafSolicitadaMin = horaAMinutos(datosRegistro.HoraFin);
  let hrsSolicitadasMin = horafSolicitadaMin - horaiSolicitadaMin;
  if (hrsSolicitadasMin < 0) hrsSolicitadasMin += 1440;

  let checador;
  try {
    checador = await fetchChecador(noemp, date);
  } catch (e) {
    mostrarError("Error al conectar con el checador.");
    return;
  }

  console.log(">>> checador:", checador);

  const regHoyStr =
    checador.regActual.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regMañanaStr =
    checador.regSiguiente.map((r) => r.hora_limpia).join(", ") || "ninguno";

  if (checador.regActual.length < 1) {
    mostrarError(`No hay registros en el checador para el día ${date}.`, {
      registrosHoy: "ninguno",
      registrosMañana: regMañanaStr,
    });
    return;
  }

  // ── Entrada: registro dentro del rango solicitado (con margen) ────────────
  // Se aplica una tolerancia de ±30 min al rango solicitado para tolerar
  // checadas ligeramente fuera del rango (p. ej. 16:53 para un rango 17:00→22:30)
  // y se elige el MÁS CERCANO a la hora de inicio solicitada.
  const MARGEN_ANTICIPO = 30; // tolerancia antes/después del rango solicitado
  const candidatosRango = checador.regActual.filter((r) => {
    if (!r.hora_limpia) return false;
    const min = horaAMinutos(r.hora_limpia);
    return (
      min >= horaiSolicitadaMin - MARGEN_ANTICIPO &&
      min <= horafSolicitadaMin + MARGEN_ANTICIPO
    );
  });
  const regDentroRango = candidatosRango.length
    ? candidatosRango.reduce((mejor, r) =>
        Math.abs(horaAMinutos(r.hora_limpia) - horaiSolicitadaMin) <
        Math.abs(horaAMinutos(mejor.hora_limpia) - horaiSolicitadaMin)
          ? r
          : mejor,
      )
    : null;

  if (!regDentroRango) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: "Anticipo — No apto",
      html: `Solicitó <b>${minutosAHoraConSegundos(hrsSolicitadasMin)}</b> pero no se encontró ningún registro en el rango
                   <b>${datosRegistro.HoraInicio} → ${datosRegistro.HoraFin}</b> (±${MARGEN_ANTICIPO} min).<br><br>
                   Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b>`,
      icon: "error",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  const entradaMin = horaAMinutos(regDentroRango.hora_limpia);

  // ── Salida: hoy o día siguiente según el turno y los registros ────────────
  const salidaInfo = resolverSalidaConLapso(
    entradaMin,
    checador.regActual,
    checador.regSiguiente,
    hrsReglMin,
    esTurnoNocturno,
  );

  if (!salidaInfo || !salidaInfo.salida) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: "Anticipo — No apto",
      html: `Se encontró la entrada (<b>${regDentroRango.hora_limpia}</b>) pero no hay registro de salida ni hoy ni en el día siguiente.<br><br>
                   Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>
                   Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  const totalTrabajadoMin = salidaInfo.span;
  const diaSalida =
    salidaInfo.dia === "siguiente"
      ? checador.fechaSiguiente
      : checador.fechaActual;
  const excedenteMin = Math.max(0, totalTrabajadoMin - hrsReglMin);

  console.log(
    `Anticipo: entrada=${regDentroRango.hora_limpia}(${checador.fechaActual}) salida=${salidaInfo.salida.hora_limpia}(${diaSalida}) total=${totalTrabajadoMin}min excedente=${excedenteMin}min solicitado=${hrsSolicitadasMin}min`,
  );

  // ── Validaciones ──────────────────────────────────────────────────────────
  if (totalTrabajadoMin < hrsReglMin - 5) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: "Anticipo — No apto",
      html: `No completó las reglamentarias.<br><br>
                   Turno: <b>${turnoAsignado}</b> (regl.: <b>${def.horasReglamentarias}</b>)<br>
                   Entrada (${checador.fechaActual}): <b>${regDentroRango.hora_limpia}</b><br>
                   Salida (${diaSalida}): <b>${salidaInfo.salida.hora_limpia}</b><br>
                   Total: <b>${minutosAHoraConSegundos(totalTrabajadoMin)}</b><br>
                   Registros hoy: <b>${regHoyStr}</b><br>
                   Registros mañana: <b>${regMañanaStr}</b><br>
                   Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  if (excedenteMin < 50) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: "Anticipo — No apto (excedente insuficiente)",
      html: `Excedente <b>${minutosAHoraConSegundos(excedenteMin)}</b> no alcanza los 50 min mínimos.<br><br>
                   Entrada (${checador.fechaActual}): <b>${regDentroRango.hora_limpia}</b><br>
                   Salida (${diaSalida}): <b>${salidaInfo.salida.hora_limpia}</b><br>
                   Total trabajado: <b>${minutosAHoraConSegundos(totalTrabajadoMin)}</b><br>
                   Solicitado: <b>${minutosAHoraConSegundos(hrsSolicitadasMin)}</b><br>
                   Registros hoy: <b>${regHoyStr}</b><br>
                   Registros mañana: <b>${regMañanaStr}</b><br>
                   Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  // ── Apto: el excedente del anticipo va ANTES de la entrada normal ─────────
  const finAnticipoMin = def.entrada.ideal;
  const inicioAnticipoMin =
    (((finAnticipoMin - excedenteMin) % 1440) + 1440) % 1440;
  const nuevaHoraInicio = minutosAHoraConSegundos(inicioAnticipoMin);
  const nuevaHoraFin = minutosAHoraConSegundos(finAnticipoMin);

  window.Reportes.marcarRegistroComoApto(folioRegistro);
  await Swal.fire({
    title: "Anticipo — Apto",
    html: `Turno: <b>${turnoAsignado}</b> | Regl.: <b>${def.horasReglamentarias}</b><br>
               Entrada (${checador.fechaActual}): <b>${regDentroRango.hora_limpia}</b><br>
               Salida (${diaSalida}): <b>${salidaInfo.salida.hora_limpia}</b><br>
               Total trabajado: <b>${minutosAHoraConSegundos(totalTrabajadoMin)}</b><br>
               Excedente (anticipo): <b>${minutosAHoraConSegundos(excedenteMin)}</b><br>
               Solicitado: <b>${minutosAHoraConSegundos(hrsSolicitadasMin)}</b><br>
               Tipo: <b>${tipoEmpleado}</b><br><br>
               Horas T. extra (antes de la entrada del turno):<br>
               Inicio: <b>${nuevaHoraInicio}</b> | Fin: <b>${nuevaHoraFin}</b>`,
    icon: "success",
  });

  const ok = await ajustarHorasRegistro(
    folioRegistro,
    nuevaHoraInicio,
    nuevaHoraFin,
  );
  if (ok)
    await Swal.fire(
      "Actualización realizada",
      `Horas ajustadas: <b>${nuevaHoraInicio}</b> → <b>${nuevaHoraFin}</b>`,
      "success",
    );
  const fid = window.Reportes.data.find(
    (e) => e.folioRegistro == folioRegistro,
  )?.id;
  if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// procesarReingreso — razon = "Reingreso" (antes Apoyo)
// (SIN CAMBIOS — ya ancla por ±30 min de la hora de inicio solicitada)
// ─────────────────────────────────────────────────────────────────────────────

async function procesarReingreso(
  noemp,
  date,
  folioRegistro,
  turnoAsignado,
  tipoEmpleado,
  datosRegistro,
  mostrarError,
) {
  const MARGEN = 30;
  const horaInicioRef = datosRegistro?.HoraInicio || null;
  if (!horaInicioRef) {
    mostrarError(
      "No se encontró la hora de inicio de reingreso en el registro.",
    );
    return;
  }

  let checador;
  try {
    checador = await fetchChecador(noemp, date);
  } catch (e) {
    mostrarError("Error al conectar con el checador.");
    return;
  }

  const regHoyStr =
    checador.regActual.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const regMañanaStr =
    checador.regSiguiente.map((r) => r.hora_limpia).join(", ") || "ninguno";
  const refMin = horaAMinutos(horaInicioRef);

  const registroInicio = checador.regActual.find(
    (r) => Math.abs(horaAMinutos(r.hora_limpia) - refMin) <= MARGEN,
  );
  if (!registroInicio) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: "Reingreso — No apto",
      html: `No se encontró ningún registro dentro de <b>±${MARGEN} minutos</b> de la hora de inicio indicada (<b>${horaInicioRef}</b>).<br><br>Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  const inicioRealMin = horaAMinutos(registroInicio.hora_limpia);
  const candidatosHoy = checador.regActual.filter(
    (r) => horaAMinutos(r.hora_limpia) > inicioRealMin,
  );
  const registroFin =
    candidatosHoy.length > 0
      ? candidatosHoy[0]
      : checador.regSiguiente.length > 0
        ? checador.regSiguiente[0]
        : null;

  if (!registroFin) {
    window.Reportes.marcarRegistroComoNoApto(folioRegistro);
    await Swal.fire({
      title: "Reingreso — No apto",
      html: `Se encontró el inicio (<b>${registroInicio.hora_limpia}</b>) pero no hay ningún registro posterior.<br><br>Registros hoy (${checador.fechaActual}): <b>${regHoyStr}</b><br>Registros mañana (${checador.fechaSiguiente}): <b>${regMañanaStr}</b><br>Tipo: <b>${tipoEmpleado}</b>`,
      icon: "warning",
    });
    const fid = window.Reportes.data.find(
      (e) => e.folioRegistro == folioRegistro,
    )?.id;
    if (fid) window.Reportes.evaluarFolio(fid);
    return;
  }

  const nuevaHoraInicio = registroInicio.hora_limpia;
  const nuevaHoraFin = registroFin.hora_limpia;
  let finMin = horaAMinutos(nuevaHoraFin);
  if (finMin < inicioRealMin) finMin += 1440;
  const duracion = finMin - inicioRealMin;

  window.Reportes.marcarRegistroComoApto(folioRegistro);
  await Swal.fire({
    title: "Reingreso — Apto",
    html: `Inicio encontrado (${checador.fechaActual}): <b>${nuevaHoraInicio}</b><br>Fin encontrado: <b>${nuevaHoraFin}</b><br>Duración: <b>${minutosAHoraConSegundos(duracion)}</b><br>Tipo: <b>${tipoEmpleado}</b><br><br>Las horas se ajustarán con los registros reales.`,
    icon: "success",
  });
  const ok = await ajustarHorasRegistro(
    folioRegistro,
    nuevaHoraInicio,
    nuevaHoraFin,
  );
  if (ok)
    await Swal.fire(
      "Actualización realizada",
      `Horas ajustadas: <b>${nuevaHoraInicio}</b> → <b>${nuevaHoraFin}</b>`,
      "success",
    );
  const fid = window.Reportes.data.find(
    (e) => e.folioRegistro == folioRegistro,
  )?.id;
  if (fid) window.Reportes.evaluarFolio(fid);
}

// ─────────────────────────────────────────────────────────────────────────────
// CLASE PRINCIPAL
// ─────────────────────────────────────────────────────────────────────────────
class Reportes {
  constructor() {
    this.data = [];
  }

  async getinfohoraentradaysalida(
    noemp,
    date,
    folioRegistro,
    HoraInicio,
    turnoAsignado,
    datosRegistro = null,
  ) {
    if (!datosRegistro)
      datosRegistro =
        this.data.find((e) => e.folioRegistro == folioRegistro) || null;

    const motivo = parseInt(datosRegistro?.motivo ?? 0);
    const rawTipo = datosRegistro?.tipo_empleado;
    let tipoEmpleado;
    if (rawTipo === 1 || rawTipo === "1" || rawTipo === "empleado")
      tipoEmpleado = "empleado";
    else if (rawTipo === 0 || rawTipo === "0" || rawTipo === "sindicalizado")
      tipoEmpleado = "sindicalizado";
    else tipoEmpleado = "empleado";

    const razonRegistro = (datosRegistro?.razon || "")
      .toString()
      .trim()
      .toLowerCase();
    const esAnticipo = razonRegistro === "anticipo";
    const esReingreso = razonRegistro === "reingreso";
    const esDoblete = razonRegistro === "doblete";
    const esTurnoNocturno = ["turno3", "turno3_12hrs"].includes(turnoAsignado);
    const esTurnoMixto = TURNOS_MIXTOS.includes(turnoAsignado);

    console.log(
      `═══ INICIO validación: folio=${folioRegistro} emp=${noemp} fecha=${date} turno=${turnoAsignado} motivo=${motivo} razon="${razonRegistro}" tipoEmpleado="${tipoEmpleado}"`,
    );
    console.log(
      `esAnticipo=${esAnticipo} | esReingreso=${esReingreso} | esDoblete=${esDoblete} | esTurnoNocturno=${esTurnoNocturno}`,
    );

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
        ctx.registrosHoy ? `Registros hoy: <b>${ctx.registrosHoy}</b>` : "",
        ctx.registrosAyer ? `Registros ayer: <b>${ctx.registrosAyer}</b>` : "",
        ctx.registrosMañana
          ? `Registros mañana: <b>${ctx.registrosMañana}</b>`
          : "",
        ctx.entradaDetectada
          ? `Entrada detectada: <b>${ctx.entradaDetectada}</b>`
          : "",
        ctx.salidaDetectada
          ? `Salida detectada: <b>${ctx.salidaDetectada}</b>`
          : "",
      ]
        .filter(Boolean)
        .join("<br>");
      Swal.fire({
        title: "Resultados de validación",
        html: `Se recomienda revisar este registro.<br><br><b>${msg}</b><br><br>── Diagnóstico ──<br>${lineas}`,
        icon: "info",
      });
    };

    if (!CATALOGO_TURNOS[turnoAsignado]) {
      mostrarError(
        `El turno "<b>${turnoAsignado}</b>" no está reconocido. Comunícate con Nóminas.`,
      );
      return;
    }

    // ── Reingreso ─────────────────────────────────────────────────────────
    if (esReingreso) {
      await procesarReingreso(
        noemp,
        date,
        folioRegistro,
        turnoAsignado,
        tipoEmpleado,
        datosRegistro,
        mostrarError,
      );
      return;
    }

    // ── Anticipo ──────────────────────────────────────────────────────────
    if (esAnticipo) {
      await procesarAnticipo(
        noemp,
        date,
        folioRegistro,
        turnoAsignado,
        tipoEmpleado,
        datosRegistro,
        mostrarError,
      );
      return;
    }

    // ── Doblete (hacia adelante) ───────────────────────────────────────────
    if (esDoblete) {
      await procesarDoblete(
        noemp,
        date,
        folioRegistro,
        turnoAsignado,
        tipoEmpleado,
        datosRegistro,
        mostrarError,
      );
      return;
    }

    // ── Auto-aptos ────────────────────────────────────────────────────────
    if (MOTIVOS_AUTO_APTOS.includes(motivo)) {
      window.Reportes.marcarRegistroComoApto(folioRegistro);
      const ok = await actualizarValidado(folioRegistro, null);
      if (ok) {
        const reg = this.data.find((e) => e.folioRegistro == folioRegistro);
        if (reg) {
          reg.validado = 1;
          actualizarFilaDOM(folioRegistro, reg);
        }
        await Swal.fire(
          "Validado",
          `El registro de ${motivo === MOTIVO_HORA_COMIDA ? "hora de comida" : "cambio de horario"} fue marcado como validado.`,
          "success",
        );
      }
      const fid = this.data.find((e) => e.folioRegistro == folioRegistro)?.id;
      if (fid) this.evaluarFolio(fid);
      return;
    }

    // ── Descanso trabajado / Día festivo ──────────────────────────────────
    if (MOTIVOS_ESPECIALES.includes(motivo)) {
      await procesarDescansoOFestivo(
        noemp,
        date,
        folioRegistro,
        turnoAsignado,
        motivo,
        tipoEmpleado,
        datosRegistro,
        mostrarError,
      );
      return;
    }

    // ── Turno nocturno (flujo normal) ─────────────────────────────────────
    if (esTurnoNocturno) {
      await procesarTurnoNocturno(
        noemp,
        date,
        folioRegistro,
        turnoAsignado,
        tipoEmpleado,
        datosRegistro,
        mostrarError,
      );
      return;
    }

    // ── TODOS los demás (turno1, turno2, mixtos) → lógica de lapso ────────
    await procesarPorLapso(
      noemp,
      date,
      folioRegistro,
      turnoAsignado,
      tipoEmpleado,
      datosRegistro,
      mostrarError,
    );
  }

  async consulta() {
    const respuetaraw = await fetch("php/index.php?tblautorizatp");
    const respuesta = await respuetaraw.json();

    if (Array.isArray(respuesta)) {
      this.data = respuesta;
      let pendientes = "";
      let procesadas = "";
      let countPendientes = 0;
      let countProcesadas = 0;

      respuesta.forEach((e) => {
        const row = this.renderRow(e);

        // Clasificación según estado terminado
        if (e.validado === null || e.validado === 0) {
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

  renderRow(elemento) {
    let accionHtml = "",
      accionTerminadoHtml = "",
      badgeValidado = "";

    if (elemento.terminado === null || elemento.terminado === "") {
      if (elemento.validado == null || elemento.validado == 0) {
        accionHtml = `<button class="btn btn-sm btn-warning"
                              onclick="validarInfo('${elemento.NoEmpleadoSol}','${elemento.fechaSol}','${elemento.folioRegistro}','${elemento.HoraInicio}','${elemento.turnoAsignado}',${elemento.folioRegistro})" id="btnValidar-${elemento.folioRegistro}"><i class="fa-solid fa-eye"></i> Validar T. extra</button>
                              <button class="btn btn-sm btn-danger" onclick="window.deletesub(${elemento.folioRegistro})" id="btnEliminar-${elemento.folioRegistro}" hidden><i class="fas fa-times"></i> Eliminar</button>`;
      } else {
        accionHtml = `<span class="badge bg-success">Solicitud validada</span>`;
      }
      accionTerminadoHtml = `<span class="badge bg-warning text-dark">En espera de gerente</span>`;
    } else if (elemento.terminado == 1) {
      accionHtml = `<span class="badge bg-success">Aprobado</span>`;
      accionTerminadoHtml = `<span class="badge bg-success">Aprobado por gerente</span>`;
    } else if (elemento.terminado == 2) {
      accionHtml = `<span class="badge bg-danger">Rechazado</span>`;
      accionTerminadoHtml = `<span class="badge bg-danger">Rechazado por gerente</span>`;
    }
    badgeValidado =
      elemento.validado == 1
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
                    <td>${accionHtml}<small id="msg-${elemento.folioRegistro}" class="d-block mt-1"></small></td>
                </tr>`;
  }

  _aplicarEstadoFecha(elemento, ahora) {
    const fechaSol = new Date(elemento.fechaSol + "T00:00:00");
    const [h, m, s] = elemento.HoraFin.split(":").map(Number);
    const horaFinDate = new Date(fechaSol);
    horaFinDate.setHours(h, m, s);
    const margen = new Date(horaFinDate.getTime() + 5 * 60000);
    const btnValidar = document.getElementById(
      `btnValidar-${elemento.folioRegistro}`,
    );
    const msg = document.getElementById(`msg-${elemento.folioRegistro}`);
    if (ahora < fechaSol) {
      if (btnValidar) btnValidar.hidden = true;
      if (msg) {
        msg.textContent = `El día solicitado (${elemento.fechaSol}) aún no ha llegado.`;
        msg.className = "alert alert-warning p-1 mt-1";
      }
    } else if (
      ahora.toDateString() === fechaSol.toDateString() &&
      ahora < margen
    ) {
      if (btnValidar) btnValidar.hidden = true;
      if (msg) {
        msg.textContent = `Aún no es momento. Se habilitará a las ${margen.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })} hrs.`;
        msg.className = "alert alert-warning p-1 mt-1";
      }
    } else {
      if (msg) {
        msg.textContent = "";
        msg.dataset.estado = "pendiente";
      }
    }
  }

  filtrarPorFolio(folioId) {
    const filtrados = this.data.filter((e) => e.id == folioId);

    let pendientes = "";
    let procesadas = "";
    let countPendientes = 0;
    let countProcesadas = 0;

    filtrados.forEach((e) => {
      const row = this.renderRow(e);

      if (e.validado === null || e.validado === 0) {
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

    // Acciones globales
    document
      .getElementById(`btnAutorizar-${folioId}`)
      ?.addEventListener("click", () => this.enviar(folioId, 1));
    document
      .getElementById(`btnRechazar-${folioId}`)
      ?.addEventListener("click", () => this.enviar(folioId, 2));

    const ahora = new Date();
    filtrados.forEach((e) => this._aplicarEstadoFecha(e, ahora));
    this.evaluarFolio(folioId);
  }

  async deletesub(id) {
    const data = new FormData();
    data.append("id", id);
    const resp = await fetch("./php/index.php?deletesub", {
      method: "POST",
      body: data,
    }).then((r) => r.json());
    this.consulta();
    resp === "Listo"
      ? Swal.fire("Listo!!!", "Registro eliminado", "success")
      : Swal.fire("ERROR!!!", "Hay un problema al eliminar", "error");
  }

  async enviar(id, autor) {
    const verif = await fetch("./php/verificar_firma.php")
      .then((r) => r.json())
      .catch(() => null);
    if (!verif?.success) {
      Swal.fire({
        icon: "warning",
        title: "Firma no registrada",
        text: "Debes registrar tu firma primero.",
        confirmButtonText: "Entendido",
        confirmButtonColor: "#f0ad4e",
      });
      return;
    }
    const resp = await fetch(
      `./php/index.php?autorizafol&id=${id}&autor=${autor}`,
    ).then((r) => r.json());
    resp === false
      ? Swal.fire({
          icon: "error",
          title: "Error",
          text: resp.message || "Error con la base de datos.",
        })
      : Swal.fire({
          icon: "success",
          title: "Autorizado",
          text: "Autorizado con éxito",
          timer: 2000,
          showConfirmButton: false,
        });
    window.open(`./pdf/reporte.php?folio=${btoa(id)}&true=1trlist`);
    window.location.reload();
  }

  pdffin(id) {
    window.open(`./pdf/reporte.php?folio=${btoa(id)}&true=1trlist`);
  }

  marcarRegistroComoApto(folioRegistro) {
    const btn = document.getElementById(`btnValidar-${folioRegistro}`),
      msg = document.getElementById(`msg-${folioRegistro}`);
    if (btn) btn.hidden = true;
    if (msg) {
      msg.textContent = "Registro apto para tiempo extra.";
      msg.className = "alert alert-success p-1 mt-1";
      msg.dataset.estado = "apto";
    }
  }

  marcarRegistroComoNoApto(folioRegistro) {
    const btn = document.getElementById(`btnValidar-${folioRegistro}`),
      be = document.getElementById(`btnEliminar-${folioRegistro}`),
      msg = document.getElementById(`msg-${folioRegistro}`);
    if (btn) btn.hidden = true;
    if (be) be.hidden = false;
    if (msg) {
      msg.textContent = "Registro no apto, puede eliminarse.";
      msg.className = "alert alert-warning p-1 mt-1";
      msg.dataset.estado = "noapto";
    }
  }

  evaluarFolio(folioId) {
    const registros = this.data.filter((e) => e.id == folioId);
    let todosAnalizados = true,
      todosAptos = true;
    registros.forEach((e) => {
      const estado = document.getElementById(`msg-${e.folioRegistro}`)?.dataset
        .estado;
      if (estado !== "apto" && estado !== "noapto") todosAnalizados = false;
      if (estado === "noapto") todosAptos = false;
    });
    const bA = document.getElementById(`btnAutorizar-${folioId}`),
      bR = document.getElementById(`btnRechazar-${folioId}`);
    if (todosAnalizados && todosAptos) {
      if (bA) bA.hidden = false;
      if (bR) bR.hidden = false;
    } else {
      if (bA) bA.hidden = true;
      if (bR) bR.hidden = true;
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// INSTANCIA GLOBAL
// ─────────────────────────────────────────────────────────────────────────────
window.Reportes = new Reportes();
window.Reportes.consulta();

window.validarInfo = function (
  noEmpSol,
  fechaSol,
  folioRegistro,
  HoraInicio,
  turnoAsignado,
  folioRegistroId,
) {
  const datosRegistro =
    window.Reportes.data.find(
      (e) => e.folioRegistro == (folioRegistroId ?? folioRegistro),
    ) || null;
  window.Reportes.getinfohoraentradaysalida(
    noEmpSol,
    fechaSol,
    folioRegistro,
    HoraInicio,
    turnoAsignado,
    datosRegistro,
  );
};
window.deletesub = (id) => window.Reportes.deletesub(id);
window.Autoriza = (id) => window.Reportes.enviar(id, 1);
window.Rechazar = (id) => window.Reportes.enviar(id, 2);
window.pdfFin = (id) => window.Reportes.pdffin(id);

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
    infoPlanEntregados = axiosResponse.data.map((item) => ({ ...item }));
    if (axiosResponse.status === 200) mostrarTabla();
  } catch (error) {
    Swal.fire("Error", "Hay un problema con la base de datos", "error");
  }
}
obtenerDatosArray();

function mostrarTabla(query = document.getElementById("filtroGlobal").value) {
  const q = (query || "").toString().trim().toLowerCase();
  const datosFiltrados = q
    ? infoPlanEntregados.filter((item) =>
        [
          item.folioRegistro,
          item.id,
          item.fecha,
          item.departamento,
          item.creado,
          item.terminado,
          item.autorizado,
          item.NoEmp,
          item.SupervisorNombre,
          item.noempautoriza,
          item.fechaSol,
          item.NoEmpleadoSol,
          item.HoraInicio,
          item.HoraFin,
          item.NombreEmpleadoSol,
          item.validado,
          item.motivo,
          item.turnoAsignado,
          item.razon,
        ].some((v) => v && v.toString().toLowerCase().includes(q)),
      )
    : infoPlanEntregados.slice();

  let pendientes = "";
  let procesadas = "";
  let countPendientes = 0;
  let countProcesadas = 0;

  if (!datosFiltrados.length) {
    pendientes = `<tr><td colspan="14" class="text-center">No hay registros que coincidan</td></tr>`;
  } else {
    datosFiltrados.forEach((e) => {
      const row = window.Reportes.renderRow(e);
      if (e.validado === null || e.validado === 0) {
        pendientes += row;
        countPendientes++;
      } else {
        procesadas += row;
        countProcesadas++;
      }
    });
  }

  document.getElementById("tblPendientes").innerHTML = pendientes;
  document.getElementById("tblProcesadas").innerHTML = procesadas;
  document.getElementById("countPendientes").innerText = countPendientes;
  document.getElementById("countProcesadas").innerText = countProcesadas;

  const ahora = new Date();
  datosFiltrados.forEach((e) => window.Reportes._aplicarEstadoFecha(e, ahora));
}

// ── Tutorial ──────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
  const driver = window.driver.js.driver;
  const steps = [
    {
      element: ".tittlecont",
      popover: {
        title: "Validación de solicitudes",
        description:
          "Aquí podrás validar las solicitudes de tiempo extra antes de enviarlas a Gerencia.",
        side: "bottom",
      },
    },
    {
      element: "#filtroGlobal",
      popover: {
        title: "Filtro global",
        description: "Busca solicitudes por cualquier dato.",
        side: "top",
        popoverClass: "popover-importante",
      },
    },
    {
      element: "table thead th:nth-child(12)",
      popover: {
        title: "Estatus",
        description: "Indica si fue aprobada o rechazada por Gerencia.",
        side: "top",
        popoverClass: "popover-importante",
      },
    },
    {
      element: "table thead th:nth-child(13)",
      popover: {
        title: "Validado",
        description: "Indica si ya fue validada.",
        side: "top",
        popoverClass: "popover-importante",
      },
    },
    {
      element: "table thead th:nth-child(14)",
      popover: {
        title: "Acciones",
        description: "Botones para validar individualmente.",
        side: "top",
        popoverClass: "popover-importante",
      },
    },
    {
      element: "#btnAyuda",
      popover: {
        title: "Tutorial",
        description: "Presiona para repetir el tutorial.",
        side: "bottom",
      },
    },
  ];
  const driverObj = driver({
    showProgress: true,
    allowClose: false,
    disableInteraction: true,
    progressText: "Paso {{current}} de {{total}}",
    doneBtnText: "Finalizar",
    nextBtnText: "Siguiente",
    prevBtnText: "Atrás",
    steps,
  });
  const tk = "tutorial_validacionTE";
  if (!localStorage.getItem(tk)) {
    driverObj.drive();
    localStorage.setItem(tk, "true");
  }
  document
    .getElementById("btnAyuda")
    ?.addEventListener("click", () => driverObj.drive());
});
