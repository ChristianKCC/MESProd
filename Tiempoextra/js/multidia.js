// ─────────────────────────────────────────────────────────────────────────────
// CATÁLOGO DE MOTIVOS (se llena desde el select existente del flujo individual)
// ─────────────────────────────────────────────────────────────────────────────
function obtenerMotivosDelSelect() {
  const select = document.getElementById("motivos");
  const opciones = [];
  for (let i = 0; i < select.options.length; i++) {
    const opt = select.options[i];
    if (opt.value)
      opciones.push({
        value: opt.value,
        text: opt.text,
        disabled: opt.disabled,
      });
  }
  return opciones;
}

// ─────────────────────────────────────────────────────────────────────────────
// CATÁLOGO DE TURNOS para el panel múltiple (turno2_13hrs/turno3_12hrs se derivan)
// ─────────────────────────────────────────────────────────────────────────────
const TURNOS_MULTIDIA = {
  turno1: {
    label: "Turno 1 (07:00-15:00)",
    inicioSalida: "15:00:00",
    inicioTurno: "07:00:00",
  },
  turno2: {
    label: "Turno 2 (15:00-22:30)",
    inicioSalida: "22:30:00",
    inicioTurno: "15:00:00",
  },
  turno3: {
    label: "Turno 3 (22:30-07:00)",
    inicioSalida: "07:00:00",
    inicioTurno: "22:30:00",
  },
  mixto1: {
    label: "Mixto 1 (07:30-17:00)",
    inicioSalida: "17:00:00",
    inicioTurno: "07:30:00",
  },
  mixto2: {
    label: "Mixto 2 (08:30-18:30)",
    inicioSalida: "18:30:00",
    inicioTurno: "08:30:00",
  },
  mixto3: {
    label: "Mixto 3 (07:00-16:30)",
    inicioSalida: "16:30:00",
    inicioTurno: "07:00:00",
  },
  mixto4: {
    label: "Mixto 4 (07:00-17:00)",
    inicioSalida: "17:00:00",
    inicioTurno: "07:00:00",
  },
};

const DIAS_SEMANA = [
  "Lunes",
  "Martes",
  "Miércoles",
  "Jueves",
  "Viernes",
  "Sábado",
  "Domingo",
];

// Estado del módulo
let _diasConfigurados = {}; // { bid: { ...flags } }
let _semanaBase = null;
let _registrosExistentes = {}; // { "yyyy-mm-dd": [ {registro tblsubenc}, ... ] }
let _contadorSubBloques = {}; // { "yyyy-mm-dd": siguienteIdx }
let _subActivo = {}; // { "yyyy-mm-dd": índice visible (0-based) }
let _precargando = false; // true mientras se precargan datos existentes

// ─────────────────────────────────────────────────────────────────────────────
// LÍMITES Y RESTRICCIONES DE CATÁLOGO
// ─────────────────────────────────────────────────────────────────────────────
const MAX_REGISTROS_DIA = 2;

// IDs de motivos permitidos en el SEGUNDO registro de un SINDICALIZADO.
// Según el catálogo: 9 = Hora de comida, 8 = Cambio/Reducción de horario.
const MOTIVOS_2DO_SINDICALIZADO = ["9", "8"];

// IBMs que SOLO pueden registrar motivos 10 (Descanso trabajado) y 12 (Día festivo)
const IBM_ESPECIALES = [27585, 27903];
const MOTIVOS_IBM_ESPECIAL = ["10", "12"];

// ─────────────────────────────────────────────────────────────────────────────
// Helpers de bid (block id = "<fecha>__<idx>")
// ─────────────────────────────────────────────────────────────────────────────
function makeBid(fecha, idx) {
  return fecha + "__" + idx;
}
function bidToFecha(bid) {
  return String(bid).split("__")[0];
}

// ─────────────────────────────────────────────────────────────────────────────
// getTiempoextra — localiza la instancia global (index.js debe exponer
// window.Tiempoextra = Tiempoextra;)
// ─────────────────────────────────────────────────────────────────────────────
function getTiempoextra() {
  if (typeof window !== "undefined" && window.Tiempoextra)
    return window.Tiempoextra;
  try {
    if (typeof Tiempoextra !== "undefined" && Tiempoextra) return Tiempoextra;
  } catch (e) {}
  return null;
}

let _avisoTiempoextraMostrado = false;
function avisarSinTiempoextra() {
  if (_avisoTiempoextraMostrado) return;
  _avisoTiempoextraMostrado = true;
  Swal.fire({
    icon: "error",
    title: "Configuración incompleta",
    html: `No se pudo acceder a las funciones del módulo principal. Contacta a soporte tecnico<br><br>`,
    confirmButtonText: "Entendido",
  });
}

// ─────────────────────────────────────────────────────────────────────────────
// toggleModoMultiple — interruptor desde el HTML: onchange="toggleModoMultiple(this)"
// ─────────────────────────────────────────────────────────────────────────────
function toggleModoMultiple(checkbox) {
  const panelWrap = document.getElementById("panelMultidiaWrap");
  const formTE = document.getElementById("formtiempoextra");
  const folio = document.getElementById("folio").value;

  if (checkbox.checked) {
    if (!folio) {
      Swal.fire(
        "Sin folio",
        "Primero debes crear o seleccionar un folio para usar la captura múltiple.",
        "warning",
      );
      checkbox.checked = false;
      return;
    }
    panelWrap.style.display = "block";
    if (formTE) formTE.style.display = "none";
    iniciarModoMultiple();
  } else {
    panelWrap.style.display = "none";
    if (formTE) formTE.style.display = "block";
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// iniciarModoMultiple
// ─────────────────────────────────────────────────────────────────────────────
function iniciarModoMultiple() {
  const semanaFolio = document.getElementById("semanaFolioHidden").value;
  _semanaBase = calcularLunesDeSemana(parseInt(semanaFolio));
  _diasConfigurados = {};
  _registrosExistentes = {};
  _contadorSubBloques = {};
  renderizarPanel();
}

// ─────────────────────────────────────────────────────────────────────────────
// calcularLunesDeSemana
// ─────────────────────────────────────────────────────────────────────────────
function calcularLunesDeSemana(semana) {
  const year = new Date().getFullYear();
  const jan4 = new Date(year, 0, 4);
  const startOfWeek1 = new Date(jan4);
  startOfWeek1.setDate(jan4.getDate() - ((jan4.getDay() + 6) % 7));
  const lunes = new Date(startOfWeek1);
  lunes.setDate(startOfWeek1.getDate() + (semana - 1) * 7);
  return lunes;
}

function formatFecha(date) {
  return (
    date.getFullYear() +
    "-" +
    String(date.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(date.getDate()).padStart(2, "0")
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// generarSubBloque — HTML de UN registro dentro de una card. Usa bid.
// ─────────────────────────────────────────────────────────────────────────────
function generarSubBloque(fecha, idx, motivosHtml, turnosHtml) {
  const bid = makeBid(fecha, idx);
  return `
    <div class="sub-bloque" id="sub-${bid}" data-bid="${bid}" data-fecha="${fecha}">
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <small>Motivo</small>
                <select class="form-control form-control-sm campo-motivo" data-bid="${bid}"
                        onchange="onCambioMotivoDia('${bid}')">
                    <option value="">Selecciona...</option>
                    ${motivosHtml}
                </select>
            </div>
            <div class="col-12 col-md-4 campo-turno-wrap" data-bid="${bid}">
                <small>Turno</small>
                <select class="form-control form-control-sm campo-turno" data-bid="${bid}"
                        onchange="onCambioTurnoDia('${bid}')">
                    <option value="">Selecciona turno...</option>
                    ${turnosHtml}
                </select>
            </div>
            <div class="col-12 col-md-4 campo-duracion-wrap" data-bid="${bid}">
                <small>Cant. (hh:mm)</small>
                <input type="text" class="form-control form-control-sm campo-duracion" data-bid="${bid}"
                       placeholder="hh:mm" oninput="onCambioDuracionDia('${bid}')">
            </div>
            <div class="col-12 d-flex gap-2 mt-1">
                <div class="col-6">
                    <small>De</small>
                    <input type="time" step="1" class="form-control form-control-sm campo-horai" data-bid="${bid}" readonly>
                </div>
                <div class="col-6">
                    <small>A</small>
                    <input type="time" step="1" class="form-control form-control-sm campo-horaf" data-bid="${bid}" readonly>
                </div>
            </div>
            <div class="col-12 col-md-4 campo-razon-wrap" data-bid="${bid}">
                <small>Razón</small>
                <input type="text" class="form-control form-control-sm campo-razon" data-bid="${bid}" placeholder="Razón...">
            </div>
            <div class="col-12 col-md-8 especiales-wrap" data-bid="${bid}">
                <small>Tipo de registro:</small>
                <div class="d-flex gap-3 mt-1 flex-wrap">
                    <label><input type="radio" name="especial-${bid}" value="normal" checked
                        onchange="onEspecialDia('${bid}', 'normal')"> Normal</label>
                    <label class="chk12-wrap" data-bid="${bid}" style="display:none;"><input type="radio" name="especial-${bid}" value="12hrs"
                        onchange="onEspecialDia('${bid}', '12hrs')"> 12 hrs</label>
                    <label><input type="radio" name="especial-${bid}" value="anticipo"
                        onchange="onEspecialDia('${bid}', 'anticipo')"> Anticipo</label>
                    <label><input type="radio" name="especial-${bid}" value="reingreso"
                        onchange="onEspecialDia('${bid}', 'reingreso')"> Reingreso</label>
                </div>
            </div>
            <div class="col-6 col-md-3 campo-hora-manual-wrap" data-bid="${bid}" style="display:none;">
                <small>Hora inicio reingreso</small>
                <input type="time" step="1" class="form-control form-control-sm campo-hora-manual" data-bid="${bid}"
                       onchange="recalcularHorasDia('${bid}')">
            </div>
            <div class="col-6 col-md-3 campo-cambio-wrap" data-bid="${bid}" style="display:none;">
                <small>Horas cambio</small>
                <select class="form-control form-control-sm campo-cambio" data-bid="${bid}"
                        onchange="recalcularHorasDia('${bid}')">
                    <option value="2.5">2.5 horas</option>
                    <option value="3">3 horas</option>
                    <option value="5">5 horas</option>
                </select>
            </div>
            <div class="col-6 col-md-3 campo-comida-wrap" data-bid="${bid}" style="display:none;">
                <small>Duración comida</small>
                <select class="form-control form-control-sm campo-comida" data-bid="${bid}"
                        onchange="recalcularHorasDia('${bid}')">
                    <option value="00:30">30 minutos</option>
                    <option value="01:00">1 hora</option>
                </select>
            </div>
        </div>
    </div>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// renderizarPanel — encabezado de empleado + cards (cada una con sub-bloques)
// ─────────────────────────────────────────────────────────────────────────────
function renderizarPanel() {
  const container = document.getElementById("panelMultidia");
  if (!container) return;

  const motivos = obtenerMotivosDelSelect();
  const motivosHtml = motivos
    .map(
      (m) =>
        `<option value="${m.value}" ${m.disabled ? "disabled" : ""}>${m.text}</option>`,
    )
    .join("");

  const turnosHtml = Object.entries(TURNOS_MULTIDIA)
    .map(([k, v]) => `<option value="${k}">${v.label}</option>`)
    .join("");

  let cardsHtml = "";
  for (let i = 0; i < 7; i++) {
    const fecha = new Date(_semanaBase);
    fecha.setDate(_semanaBase.getDate() + i);
    const fechaStr = formatFecha(fecha);
    const diaLabel = DIAS_SEMANA[i];

    // Cada día arranca con 1 sub-bloque (idx 0)
    _contadorSubBloques[fechaStr] = 1;
    const primerSub = generarSubBloque(fechaStr, 0, motivosHtml, turnosHtml);

    cardsHtml += `
        <div class="card-dia card-dia--colapsada" id="card-dia-${fechaStr}" data-fecha="${fechaStr}">
            <div class="card-dia__header" onclick="toggleColapso('${fechaStr}')">
                <div class="card-dia__check">
                    <input type="checkbox" id="chk-dia-${fechaStr}"
                        onchange="toggleCardDia('${fechaStr}')" onclick="event.stopPropagation()">
                </div>
                <div class="card-dia__titulo">
                    <strong>${diaLabel}</strong>
                    <span class="text-muted ms-2" style="font-size:11px;">${fechaStr}</span>
                    <span class="card-dia__resumen text-muted" id="resumen-${fechaStr}" style="font-size:11px;"></span>
                </div>
                <span class="badge bg-secondary ms-auto">Sin registrar</span>
                <i class="fa-solid fa-chevron-down card-dia__chevron" id="chevron-${fechaStr}"></i>
            </div>
            <div class="card-dia__body" id="body-dia-${fechaStr}" style="display:none;">
                <!-- Paginador de registros del día -->
                <div class="sub-paginador" id="paginador-${fechaStr}">
                    <div class="sub-paginador__nav">
                        <button class="btn btn-sm btn-light sub-paginador__prev" onclick="navegarSubBloque('${fechaStr}', -1)" title="Reg. ant.">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span class="sub-paginador__label" id="paginador-label-${fechaStr}">Reg. 1 de 1</span>
                        <button class="btn btn-sm btn-light sub-paginador__next" onclick="navegarSubBloque('${fechaStr}', 1)" title="Reg. sig.">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="sub-paginador__acciones">
                        <button class="btn btn-sm btn-outline-success sub-paginador__add" onclick="agregarSubBloque('${fechaStr}')" title="Agregar otro registro a este día">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger sub-paginador__del" onclick="eliminarSubBloqueActual('${fechaStr}')" title="Quitar el registro actual (Esto no elimina el registro solo limpia la tarjeta)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                    </div>
                </div>
                <div class="sub-bloques-container" id="subs-${fechaStr}">
                    ${primerSub}
                </div>
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-primary" onclick="copiarConfiguracion('${fechaStr}')">
                        <i class="fa-solid fa-copy"></i> Copiar día a otros días
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="limpiarCardDia('${fechaStr}')">
                        <i class="fa-solid fa-eraser"></i> Limpiar día
                    </button>
                </div>
            </div>
        </div>`;
  }

  container.innerHTML = `
    <div class="card mt-3">
        <div class="card-header d-flex align-items-center gap-3 flex-wrap">
            <strong><i class="fa-solid fa-calendar-week"></i> Captura múltiple — Semana ${document.getElementById("semanaFolioHidden").value}</strong>
            <span class="text-muted" style="font-size:12px;">Activa los días que deseas registrar</span>
            <div class="ms-auto d-flex align-items-center gap-3 flex-wrap">
                <button class="btn btn-sm btn-warning" id="consultarVT" onclick="lanzarValidacion()">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    Validar Solicitudes
                </button>

                <button class="btn btn-sm btn-info" id="btnAyudaMulti" onclick="lanzarTutorialMulti()">
                    <i class="fa-solid fa-circle-question"></i>
                    ¿Quieres ver un tutorial de la captura múltiple?
                </button>

                <button class="btn btn-sm btn-outline-secondary" onclick="seleccionarTodosDias(true)">
                    Activar todos
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="seleccionarTodosDias(false)">
                    Limpiar todos
                </button>

                <button class="btn btn-sm btn-success" id="btnGuardarMultiple" onclick="guardarTodosDias()">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar todos los días
                </button>
            </div>
        </div>
	<div class="card-body border-bottom pb-2">
                <small class="text-muted"><b>DATOS DEL EMPLEADO</b> (aplican a todos los días seleccionados)</small>
                <div class="row g-2 mt-1">
                    <div class="col-6 col-md-2">
                        <small>No. Emp</small>
                        <input type="number" id="multiNoemp" class="form-control form-control-sm"
                               placeholder="Escribe el IBM"
                               onchange="onCambioNoempMulti()"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();onCambioNoempMulti();}">
                    </div>
                    <div class="col-6 col-md-3">
                        <small>Nombre</small>
                        <input type="text" id="multiNombre" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="col-6 col-md-3">
                        <small>Departamento</small>
                        <input type="text" id="multiDepartamento" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="col-6 col-md-2">
                        <small>Puesto</small>
                        <input type="text" id="multiPuesto" class="form-control form-control-sm" readonly>
                    </div>
                    <div class="col-6 col-md-2">
                        <small>Máquina</small>
                        <select id="multiMaquinas" class="form-control form-control-sm"></select>
                    </div>
                </div>
            </div>
        <div class="card-body p-2">
            <div class="cards-dias-grid">
                ${cardsHtml}
            </div>
        </div>
    </div>`;

  // Inicializar el paginador de cada día (muestra el registro 0)
  for (let i = 0; i < 7; i++) {
    const f = new Date(_semanaBase);
    f.setDate(_semanaBase.getDate() + i);
    const fs = formatFecha(f);
    _subActivo[fs] = 0;
    mostrarSubBloque(fs, 0);
  }

  const tutMultiKey = "tutorial_multidia";
  if (!localStorage.getItem(tutMultiKey)) {
    setTimeout(() => {
      lanzarTutorialMulti();
      localStorage.setItem(tutMultiKey, "true");
    }, 300);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// agregarSubBloque — añade otro registro a un día
// ─────────────────────────────────────────────────────────────────────────────
function agregarSubBloque(fecha) {
  const cont = document.getElementById(`subs-${fecha}`);
  if (!cont) return;

  // Límite de registros por día
  if (bidsDeFecha(fecha).length >= MAX_REGISTROS_DIA) {
    Swal.fire(
      "Límite alcanzado",
      `Solo se permiten ${MAX_REGISTROS_DIA} registros por día.`,
      "info",
    );
    return;
  }

  const motivos = obtenerMotivosDelSelect();
  const motivosHtml = motivos
    .map(
      (m) =>
        `<option value="${m.value}" ${m.disabled ? "disabled" : ""}>${m.text}</option>`,
    )
    .join("");
  const turnosHtml = Object.entries(TURNOS_MULTIDIA)
    .map(([k, v]) => `<option value="${k}">${v.label}</option>`)
    .join("");

  const idx = _contadorSubBloques[fecha] || 0;
  _contadorSubBloques[fecha] = idx + 1;

  const div = document.createElement("div");
  div.innerHTML = generarSubBloque(fecha, idx, motivosHtml, turnosHtml).trim();
  cont.appendChild(div.firstChild);

  sincronizarRestriccionComidaCards();
  actualizarNumerosSubBloques(fecha);

  // El segundo registro puede tener motivos restringidos según el tipo de empleado
  aplicarRestriccionSegundoRegistro(fecha);

  // Restricción de IBMs especiales (solo motivos 10 y 12) también en el bloque nuevo
  aplicarRestriccionIbmEspecial(
    document.getElementById("multiNoemp")?.value || "",
  );

  // Saltar al registro recién agregado
  mostrarSubBloque(fecha, bidsDeFecha(fecha).length - 1);
}

// ─────────────────────────────────────────────────────────────────────────────
// eliminarSubBloque — quita un sub-bloque por bid (uso interno/resincronización)
// ─────────────────────────────────────────────────────────────────────────────
function eliminarSubBloque(bid) {
  const fecha = bidToFecha(bid);
  const cont = document.getElementById(`subs-${fecha}`);
  if (!cont) return;

  const bloques = cont.querySelectorAll(".sub-bloque");
  if (bloques.length <= 1) {
    limpiarSubBloque(bid);
    return;
  }

  const el = document.getElementById(`sub-${bid}`);
  if (el) el.remove();
  delete _diasConfigurados[bid];

  actualizarNumerosSubBloques(fecha);
  mostrarSubBloque(fecha, 0);
}

// actualizarNumerosSubBloques — ahora solo refresca el paginador (no headers)
function actualizarNumerosSubBloques(fecha) {
  const idxActual = _subActivo[fecha] || 0;
  mostrarSubBloque(fecha, idxActual);
}

// Lista de bids de un día (en orden del DOM)
function bidsDeFecha(fecha) {
  const cont = document.getElementById(`subs-${fecha}`);
  if (!cont) return [];
  return Array.from(cont.querySelectorAll(".sub-bloque")).map((b) =>
    b.getAttribute("data-bid"),
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// mostrarSubBloque — muestra SOLO el registro en la posición idx; oculta el resto.
// Actualiza el paginador (label, botones prev/next, botón eliminar).
// ─────────────────────────────────────────────────────────────────────────────
function mostrarSubBloque(fecha, idx) {
  const bids = bidsDeFecha(fecha);
  if (bids.length === 0) return;

  // Acotar idx a rango válido
  if (idx < 0) idx = 0;
  if (idx > bids.length - 1) idx = bids.length - 1;
  _subActivo[fecha] = idx;

  // Mostrar solo el bloque activo
  bids.forEach((bid, i) => {
    const el = document.getElementById(`sub-${bid}`);
    if (el) el.style.display = i === idx ? "block" : "none";
  });

  // Actualizar label y botones
  const label = document.getElementById(`paginador-label-${fecha}`);
  if (label) label.textContent = `Registro ${idx + 1} de ${bids.length}`;

  const paginador = document.getElementById(`paginador-${fecha}`);
  if (paginador) {
    const prev = paginador.querySelector(".sub-paginador__prev");
    const next = paginador.querySelector(".sub-paginador__next");
    const del = paginador.querySelector(".sub-paginador__del");
    const add = paginador.querySelector(".sub-paginador__add");
    if (prev) prev.disabled = idx === 0;
    if (next) next.disabled = idx === bids.length - 1;
    // No permitir eliminar si solo queda 1 (se limpia en su lugar)
    if (del)
      del.title =
        bids.length <= 1
          ? "Limpiar este registro"
          : "Quitar el registro actual";
    // Ocultar "Agregar registro" al llegar al máximo
    if (add)
      add.style.display =
        bids.length >= MAX_REGISTROS_DIA ? "none" : "inline-block";
  }
}

// navegarSubBloque — avanza/retrocede entre los registros del día
function navegarSubBloque(fecha, delta) {
  const actual = _subActivo[fecha] || 0;
  mostrarSubBloque(fecha, actual + delta);
}

// eliminarSubBloqueActual — quita el registro visible actualmente
function eliminarSubBloqueActual(fecha) {
  const idx = _subActivo[fecha] || 0;
  const bids = bidsDeFecha(fecha);
  const bid = bids[idx];
  if (!bid) return;

  if (bids.length <= 1) {
    // Único registro: limpiarlo en vez de borrarlo
    limpiarSubBloque(bid);
    mostrarSubBloque(fecha, 0);
    return;
  }

  const el = document.getElementById(`sub-${bid}`);
  if (el) el.remove();
  delete _diasConfigurados[bid];

  actualizarNumerosSubBloques(fecha);
  // Mostrar el anterior (o el primero)
  mostrarSubBloque(fecha, Math.max(0, idx - 1));
}

// ─────────────────────────────────────────────────────────────────────────────
// onCambioNoempMulti — reutiliza getinfoemp completo del index.js
// ─────────────────────────────────────────────────────────────────────────────
async function onCambioNoempMulti() {
  const noemp = document.getElementById("multiNoemp").value;
  const limpiar = () => {
    document.getElementById("multiNombre").value = "";
    document.getElementById("multiDepartamento").value = "";
    document.getElementById("multiPuesto").value = "";
    document.getElementById("multiMaquinas").innerHTML = "";
  };

  if (!noemp) {
    limpiar();
    // Borrar IBM → limpiar todas las cards y registros existentes
    _registrosExistentes = {};
    for (let i = 0; i < 7; i++) {
      const f = new Date(_semanaBase);
      f.setDate(_semanaBase.getDate() + i);
      const fs = formatFecha(f);
      const chk = document.getElementById(`chk-dia-${fs}`);
      limpiarCardDia(fs);
      if (chk) {
        chk.checked = false;
        toggleCardDia(fs);
      }
    }
    // Restablecer restricción de IBM especial (al quedar vacío, libera selects)
    aplicarRestriccionIbmEspecial("");
    return;
  }

  const TE = getTiempoextra();
  if (!TE || typeof TE.getinfoemp !== "function") {
    avisarSinTiempoextra();
    return;
  }

  document.getElementById("noemp").value = noemp;
  await TE.getinfoemp(noemp);

  const nombreInd = document.getElementById("nombre").value;
  if (!nombreInd) {
    limpiar();
    return;
  }

  document.getElementById("multiNombre").value = nombreInd;
  document.getElementById("multiDepartamento").value =
    document.getElementById("departamento").value;
  document.getElementById("multiPuesto").value =
    document.getElementById("puesto").value;

  await copiarMaquinasDesdeIndividual();
  limpiarCardsPrecargadas();
  sincronizarRestriccionComidaCards();

  // Re-aplicar restricción del segundo registro en todos los días (por si hay 2º abierto)
  for (let i = 0; i < 7; i++) {
    const f = new Date(_semanaBase);
    f.setDate(_semanaBase.getDate() + i);
    aplicarRestriccionSegundoRegistro(formatFecha(f));
  }

  // Restricción de IBMs especiales (solo motivos 10 y 12)
  aplicarRestriccionIbmEspecial(noemp);

  await marcarDiasExistentes(noemp);
}

// limpiarCardsPrecargadas — limpia cards llenadas con datos existentes (respeta manual)
function limpiarCardsPrecargadas() {
  for (let i = 0; i < 7; i++) {
    const f = new Date(_semanaBase);
    f.setDate(_semanaBase.getDate() + i);
    const fs = formatFecha(f);

    // ¿algún bloque de este día fue precargado?
    const algunoPrecargado = bidsDeFecha(fs).some(
      (bid) => _diasConfigurados[bid] && _diasConfigurados[bid]._idExistente,
    );
    if (algunoPrecargado) {
      const chk = document.getElementById(`chk-dia-${fs}`);
      limpiarCardDia(fs);
      if (chk) {
        chk.checked = false;
        toggleCardDia(fs);
      }
    }
  }
  _registrosExistentes = {};
}

// copiarMaquinasDesdeIndividual — espera a que #maquinas se llene y copia a #multiMaquinas
async function copiarMaquinasDesdeIndividual() {
  const destino = document.getElementById("multiMaquinas");
  const origen = document.getElementById("maquinas");
  if (!destino || !origen) return;

  document.getElementById("departamento").dispatchEvent(new Event("change"));

  for (let intento = 0; intento < 20; intento++) {
    if (origen.options.length > 0) {
      destino.innerHTML = origen.innerHTML;
      return;
    }
    await new Promise((r) => setTimeout(r, 100));
  }
  destino.innerHTML = origen.innerHTML;
}

// sincronizarRestriccionComidaCards — replica aplicarRestriccionMotivo9 en cada sub-bloque
function sincronizarRestriccionComidaCards() {
  const tipo = document.getElementById("tipoEmpleadoHidden").value;
  const esEmpleadoNormal = tipo === "empleado";

  document.querySelectorAll(".campo-motivo").forEach((select) => {
    const bid = select.getAttribute("data-bid");
    for (let i = 0; i < select.options.length; i++) {
      const opt = select.options[i];
      if (opt.value === "9" || opt.value === "8") {
        if (esEmpleadoNormal) {
          opt.disabled = true;
          opt.style.color = "#aaa";
          opt.style.backgroundColor = "#f0f0f0";
          opt.title = "Solo disponible para sindicalizados";
          if (select.value === "9" || select.value === "8") {
            select.value = "";
            if (bid) onCambioMotivoDia(bid);
          }
        } else {
          opt.disabled = false;
          opt.style.color = "";
          opt.style.backgroundColor = "";
          opt.title = "";
        }
      }
    }
  });
}

// ─────────────────────────────────────────────────────────────
// aplicarRestriccionPrimerRegistro — en el PRIMER registro de un día:
//   - Si el motivo es 10 (descanso trabajado) o 12 (día festivo),
//     se oculta el botón "Agregar otro registro".
// ─────────────────────────────────────────────────────────────
function aplicarRestriccionPrimerRegistro(fecha) {
  const bids = bidsDeFecha(fecha);
  if (bids.length === 0) return;

  // Primer registro siempre es índice 0
  const primerBid = bids[0];
  const select = document.querySelector(
    `.campo-motivo[data-bid="${primerBid}"]`,
  );
  if (!select) return;

  const motivoSel = select.value;

  // Buscar el botón de agregar dentro del paginador de este día
  const btnAdd = document.querySelector(
    `#paginador-${fecha} .sub-paginador__add`,
  );
  if (!btnAdd) return;

  if (motivoSel === "10" || motivoSel === "12") {
    btnAdd.style.display = "none"; // ocultar
  } else {
    btnAdd.style.display = ""; // mostrar normal
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// aplicarRestriccionSegundoRegistro — en el SEGUNDO registro de un día:
//   - Empleado normal: todos los motivos disponibles (respetando que el 9 ya está
//     bloqueado para él por sincronizarRestriccionComidaCards).
//   - Sindicalizado: SOLO 'Hora de comida' y 'Reducción de horario' habilitados;
//     el resto se bloquea (deshabilita).
// El primer registro (índice 0) nunca se restringe por esta regla.
// ─────────────────────────────────────────────────────────────────────────────
function aplicarRestriccionSegundoRegistro(fecha) {
  const tipo = document.getElementById("tipoEmpleadoHidden").value;
  const esSindicalizado = tipo === "sindicalizado";
  const bids = bidsDeFecha(fecha);

  bids.forEach((bid, idx) => {
    const select = document.querySelector(`.campo-motivo[data-bid="${bid}"]`);
    if (!select) return;

    // Solo el segundo registro en adelante (idx >= 1) se restringe
    const esSegundo = idx >= 1;

    for (let i = 0; i < select.options.length; i++) {
      const opt = select.options[i];
      if (!opt.value) continue; // saltar placeholder

      if (esSegundo && esSindicalizado) {
        // Sindicalizado en 2º registro: solo los permitidos
        if (MOTIVOS_2DO_SINDICALIZADO.includes(opt.value)) {
          opt.disabled = false;
          opt.style.color = "";
          opt.style.backgroundColor = "";
          opt.title = "";
        } else {
          opt.disabled = true;
          opt.style.color = "#aaa";
          opt.style.backgroundColor = "#f0f0f0";
          opt.title =
            "En el segundo registro solo se permite Hora de comida o Reducción de horario";
        }
      } else {
        // Primer registro o empleado normal: liberar la restricción de esta regla.
        // (La restricción del motivo 9 para empleado normal la maneja
        //  sincronizarRestriccionComidaCards, que se llama por separado.)
        if (!(opt.value === "9")) {
          opt.disabled = false;
          opt.style.color = "";
          opt.style.backgroundColor = "";
          opt.title = "";
        }
      }
    }

    // Si el motivo actualmente seleccionado quedó deshabilitado, limpiarlo
    // (excepto durante una precarga de datos existentes)
    const optSel = select.options[select.selectedIndex];
    if (!_precargando && optSel && optSel.disabled) {
      select.value = "";
      onCambioMotivoDia(bid);
    }
  });

  // Re-aplicar la restricción del motivo 9 para empleado normal (no se pisa)
  sincronizarRestriccionComidaCards();
}

// ─────────────────────────────────────────────────────────────────────────────
// aplicarRestriccionIbmEspecial — si el IBM es especial (27585, 27903),
// deshabilita en TODOS los sub-bloques cualquier motivo que no sea 10 o 12.
// Para un IBM normal no toca nada (deja intactas las otras restricciones).
// ─────────────────────────────────────────────────────────────────────────────
function aplicarRestriccionIbmEspecial(noemp) {
  const esEspecial = IBM_ESPECIALES.includes(parseInt(noemp));

  document.querySelectorAll(".campo-motivo").forEach((select) => {
    const bid = select.getAttribute("data-bid");

    for (let i = 0; i < select.options.length; i++) {
      const opt = select.options[i];
      if (!opt.value) continue; // saltar placeholder

      if (esEspecial) {
        if (MOTIVOS_IBM_ESPECIAL.includes(opt.value)) {
          opt.disabled = false;
          opt.style.color = "";
          opt.style.backgroundColor = "";
          opt.title = "";
        } else {
          opt.disabled = true;
          opt.style.color = "#aaa";
          opt.style.backgroundColor = "#f0f0f0";
          opt.title =
            "Este empleado solo puede solicitar Descanso Trabajado o Día Festivo";
        }
      }
      // IBM normal: NO tocamos nada aquí para no pisar las otras restricciones
    }

    // Si el motivo seleccionado quedó deshabilitado, limpiarlo (salvo en precarga)
    const optSel = select.options[select.selectedIndex];
    if (esEspecial && !_precargando && optSel && optSel.disabled) {
      select.value = "";
      if (bid) onCambioMotivoDia(bid);
    }
  });
}

// ─────────────────────────────────────────────────────────────────────────────
// marcarDiasExistentes — marca badge "Ya existe" en días con registros del empleado
// ─────────────────────────────────────────────────────────────────────────────
async function marcarDiasExistentes(noemp, resincronizar = false) {
  const folio = document.getElementById("folio").value;
  if (!folio || !noemp) return;

  let registros = [];
  try {
    const r = await fetch("php/index.php?tblsubenc&folio=" + folio);
    registros = await r.json();
  } catch (e) {
    return;
  }

  if (!Array.isArray(registros)) return;

  const noempStr = String(parseInt(noemp));
  const fechasOcupadas = {};
  registros.forEach((reg) => {
    if (String(parseInt(reg.noemp)) === noempStr && reg.fecha) {
      const fechaNorm = String(reg.fecha).substring(0, 10);
      if (!fechasOcupadas[fechaNorm]) fechasOcupadas[fechaNorm] = [];
      fechasOcupadas[fechaNorm].push(reg);
    }
  });
  _registrosExistentes = fechasOcupadas;

  for (let i = 0; i < 7; i++) {
    const f = new Date(_semanaBase);
    f.setDate(_semanaBase.getDate() + i);
    const fs = formatFecha(f);
    const card = document.getElementById(`card-dia-${fs}`);
    if (!card) continue;
    const badge = card.querySelector(".card-dia__header .badge");
    const chk = document.getElementById(`chk-dia-${fs}`);
    if (!badge) continue;

    // Re-sincronizar sub-bloques precargados con los registros que quedan.
    // Solo si la card está activa y sus bloques venían de datos existentes.
    if (resincronizar && chk && chk.checked) {
      const bidsConId = bidsDeFecha(fs).filter(
        (bid) => _diasConfigurados[bid] && _diasConfigurados[bid]._idExistente,
      );
      if (bidsConId.length > 0) {
        resincronizarSubBloques(fs, fechasOcupadas[fs] || []);
      }
    }

    if (fechasOcupadas[fs]) {
      const detalles = fechasOcupadas[fs]
        .map((rg) =>
          `${rg.motivo || ""} ${rg.horai || ""}-${rg.horaf || ""}`.trim(),
        )
        .join(" | ");
      badge.className = "badge bg-warning text-dark ms-auto";
      badge.textContent =
        fechasOcupadas[fs].length > 1
          ? `Ya existe (${fechasOcupadas[fs].length})`
          : "Ya existe";
      badge.title = "Registro(s) en este día: " + detalles;
      card.style.borderColor = "#ffc107";
    } else {
      if (String(badge.textContent).startsWith("Ya existe")) {
        const activo = chk && chk.checked;
        badge.className = activo
          ? "badge bg-success ms-auto"
          : "badge bg-secondary ms-auto";
        badge.textContent = activo ? "Configurado" : "Sin registrar";
        badge.title = "";
        card.style.borderColor = "";
      }
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// resincronizarSubBloques — ajusta los sub-bloques precargados de un día para
// que coincidan con los registros que quedan tras un borrado desde la tabla.
// Quita los sub-bloques cuyo _idExistente ya no está entre los registros vivos.
// ─────────────────────────────────────────────────────────────────────────────
function resincronizarSubBloques(fecha, registrosVivos) {
  const idsVivos = registrosVivos.map((r) => String(r.id));
  const bids = bidsDeFecha(fecha);

  bids.forEach((bid) => {
    const cfg = _diasConfigurados[bid];
    if (cfg && cfg._idExistente) {
      // Si el registro de este bloque ya no existe, quitar el bloque
      if (!idsVivos.includes(String(cfg._idExistente))) {
        const cont = document.getElementById(`subs-${fecha}`);
        const bloquesActuales = cont
          ? cont.querySelectorAll(".sub-bloque").length
          : 0;
        if (bloquesActuales > 1) {
          const el = document.getElementById(`sub-${bid}`);
          if (el) el.remove();
          delete _diasConfigurados[bid];
        } else {
          // Es el único bloque: limpiarlo en vez de borrarlo
          limpiarSubBloque(bid);
        }
      }
    }
  });

  actualizarNumerosSubBloques(fecha);
}

function cargarMaquinasMulti(idDepto) {
  const TE = getTiempoextra();
  if (TE && TE.Tools && typeof TE.Tools.llnarslc === "function") {
    TE.Tools.llnarslc(
      "CatalogoPersonal",
      "GetSlcMaquinasxdep&departamento=" + idDepto,
      "multiMaquinas",
      0,
    );
    return;
  }
  copiarMaquinasDesdeIndividual();
}

// ─────────────────────────────────────────────────────────────────────────────
// expandirCard / colapsarCard / toggleColapso — manejan SOLO la visibilidad
// ─────────────────────────────────────────────────────────────────────────────
function expandirCard(fecha) {
  const body = document.getElementById(`body-dia-${fecha}`);
  const card = document.getElementById(`card-dia-${fecha}`);
  if (body) body.style.display = "block";
  if (card) card.classList.remove("card-dia--colapsada");
  // Inicializar el paginador en el registro recordado (o el primero)
  mostrarSubBloque(fecha, _subActivo[fecha] || 0);
}

function colapsarCard(fecha) {
  const body = document.getElementById(`body-dia-${fecha}`);
  const card = document.getElementById(`card-dia-${fecha}`);
  if (body) body.style.display = "none";
  if (card) card.classList.add("card-dia--colapsada");
  actualizarResumenDia(fecha);
}

// Clic en el header: alterna colapso. Si el día NO está activo, lo activa al expandir.
function toggleColapso(fecha) {
  const card = document.getElementById(`card-dia-${fecha}`);
  const chk = document.getElementById(`chk-dia-${fecha}`);
  const estaColapsada = card.classList.contains("card-dia--colapsada");

  if (estaColapsada) {
    // Expandir: si no está activo, activarlo (dispara toggleCardDia que expande)
    if (chk && !chk.checked) {
      chk.checked = true;
      toggleCardDia(fecha);
    } else {
      expandirCard(fecha);
    }
  } else {
    colapsarCard(fecha);
  }
}

// actualizarResumenDia — texto corto en el header con lo capturado ese día
function actualizarResumenDia(fecha) {
  const span = document.getElementById(`resumen-${fecha}`);
  if (!span) return;

  const bids = bidsDeFecha(fecha);
  const partes = [];
  bids.forEach((bid) => {
    const motSel = document.querySelector(`.campo-motivo[data-bid="${bid}"]`);
    const motTxt =
      motSel && motSel.value ? motSel.options[motSel.selectedIndex].text : "";
    const horaI =
      document.querySelector(`.campo-horai[data-bid="${bid}"]`)?.value || "";
    const horaF =
      document.querySelector(`.campo-horaf[data-bid="${bid}"]`)?.value || "";
    if (motTxt) {
      const h =
        horaI && horaF
          ? ` ${horaI.substring(0, 5)}-${horaF.substring(0, 5)}`
          : "";
      partes.push(`${motTxt}${h}`);
    }
  });

  if (partes.length === 0) {
    span.textContent = "";
  } else {
    const resumen = partes.join(" • ");
    span.textContent =
      " · " +
      (partes.length > 1 ? `${partes.length} registros: ` : "") +
      resumen;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// toggleCardDia — activa/desactiva un día (checkbox); ofrece precargar si "ya existe"
// ─────────────────────────────────────────────────────────────────────────────
async function toggleCardDia(fecha) {
  const chk = document.getElementById(`chk-dia-${fecha}`);
  const card = document.getElementById(`card-dia-${fecha}`);
  const activo = chk.checked;

  card.classList.toggle("card-dia--activo", activo);

  const existentes = _registrosExistentes[fecha] || [];
  const tieneExistente = existentes.length > 0;

  if (activo) {
    // Al activar, expandir automáticamente
    expandirCard(fecha);

    const yaPrecargado = bidsDeFecha(fecha).some(
      (bid) => _diasConfigurados[bid] && _diasConfigurados[bid]._precargado,
    );
    if (tieneExistente && !yaPrecargado) {
      const lista = existentes
        .map(
          (r, i) =>
            `<li>#${i + 1} — <b>${r.motivo || "-"}</b> ${r.horai || "-"} a ${r.horaf || "-"} (${r.razon || "-"})</li>`,
        )
        .join("");
      const resp = await Swal.fire({
        title: `Ya existe(n) registro(s) — ${fecha}`,
        html: `Este empleado ya tiene <b>${existentes.length}</b> registro(s) ese día:<ul style="text-align:left">${lista}</ul>¿Cargar estos datos para revisarlos/editarlos?`,
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Sí, cargar datos",
        cancelButtonText: "No, dejar vacío",
      });
      if (resp.isConfirmed) precargarTodosExistentes(fecha, existentes);
      bidsDeFecha(fecha).forEach((bid) => {
        if (!_diasConfigurados[bid]) _diasConfigurados[bid] = {};
        _diasConfigurados[bid]._precargado = true;
      });
    }

    const badge = card.querySelector(".card-dia__header .badge");
    if (badge) {
      if (tieneExistente) {
        badge.className = "badge bg-warning text-dark ms-auto";
        badge.textContent =
          existentes.length > 1
            ? `Ya existe (${existentes.length})`
            : "Ya existe";
      } else {
        badge.className = "badge bg-success ms-auto";
        badge.textContent = "Configurado";
      }
    }
    actualizarResumenDia(fecha);
  } else {
    // Al desactivar, colapsar y olvidar config
    colapsarCard(fecha);
    bidsDeFecha(fecha).forEach((bid) => delete _diasConfigurados[bid]);
    const badge = card.querySelector(".card-dia__header .badge");
    if (badge) {
      if (tieneExistente) {
        badge.className = "badge bg-warning text-dark ms-auto";
        badge.textContent =
          existentes.length > 1
            ? `Ya existe (${existentes.length})`
            : "Ya existe";
      } else {
        badge.className = "badge bg-secondary ms-auto";
        badge.textContent = "Sin registrar";
      }
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// precargarTodosExistentes — crea/llena un sub-bloque por cada registro del día
// ─────────────────────────────────────────────────────────────────────────────
function precargarTodosExistentes(fecha, registros) {
  _precargando = true;
  // Asegurar que haya tantos sub-bloques como registros
  let bids = bidsDeFecha(fecha);
  while (bids.length < registros.length) {
    agregarSubBloque(fecha);
    bids = bidsDeFecha(fecha);
  }
  // Llenar cada bloque con su registro
  registros.forEach((reg, i) => {
    const bid = bids[i];
    if (bid) precargarDatosExistentes(bid, reg);
  });
  _precargando = false;
  // Aplicar restricción ahora que los datos están puestos (sin limpiarlos)
  aplicarRestriccionSegundoRegistro(fecha);
  // Mostrar el primer registro en el paginador
  mostrarSubBloque(fecha, 0);
}

// inferirTurnoDesdeHoras — deduce turno desde horas De/A cuando no viene en el registro
function inferirTurnoDesdeHoras(horai, horaf) {
  const hi = String(horai).substring(0, 5);
  const hf = String(horaf).substring(0, 5);
  if (hi === "19:00" && hf === "07:00")
    return { turnoBase: "turno3", especial: "12hrs" };
  if (hi === "10:30" && hf === "22:30")
    return { turnoBase: "turno2", especial: "12hrs" };
  const finTurnoATurno = {
    "15:00": "turno1",
    "22:30": "turno2",
    "07:00": "turno3",
    "17:00": "mixto1",
    "18:30": "mixto2",
    "16:30": "mixto3",
  };
  if (finTurnoATurno[hi])
    return { turnoBase: finTurnoATurno[hi], especial: "normal" };
  return null;
}

// ─────────────────────────────────────────────────────────────────────────────
// precargarDatosExistentes — llena UN sub-bloque (bid) con un registro de tblsubenc
// ─────────────────────────────────────────────────────────────────────────────
function precargarDatosExistentes(bid, reg) {
  const fecha = bidToFecha(bid);

  // turnoAsignado es el VALOR real; estadoTurno es solo texto de display.
  const turnoReg = String(
    reg.turnoAsignado ?? reg.turno ?? reg.turnosel ?? "",
  ).trim();
  const razonReg = (reg.razon || "").trim();

  let especial = "normal";
  let turnoBase = turnoReg;

  if (turnoReg === "turno3_12hrs") {
    turnoBase = "turno3";
    especial = "12hrs";
  } else if (turnoReg === "turno2_13hrs") {
    turnoBase = "turno2";
    especial = "12hrs";
  }

  if (razonReg === "Anticipo") especial = "anticipo";
  if (razonReg === "Reingreso") {
    especial = "reingreso";
    turnoBase = "turno1";
  }

  if (!turnoBase && reg.horai && reg.horaf && especial === "normal") {
    const inferido = inferirTurnoDesdeHoras(reg.horai, reg.horaf);
    if (inferido) {
      turnoBase = inferido.turnoBase;
      if (inferido.especial) especial = inferido.especial;
    }
  }

  // 1) Motivo (texto → value)
  const motSel = document.querySelector(`.campo-motivo[data-bid="${bid}"]`);
  if (motSel && reg.motivo) {
    for (let i = 0; i < motSel.options.length; i++) {
      if (motSel.options[i].text.trim() === String(reg.motivo).trim()) {
        motSel.value = motSel.options[i].value;
        break;
      }
    }
    onCambioMotivoDia(bid);
  }
  const motivoNum = parseInt(motSel?.value || "0");

  // 2) Turno base
  const turSel = document.querySelector(`.campo-turno[data-bid="${bid}"]`);
  if (turSel && turnoBase) {
    const existeOpcion = Array.from(turSel.options).some(
      (o) => o.value === turnoBase,
    );
    if (existeOpcion) {
      turSel.value = turnoBase;
      if (!_diasConfigurados[bid]) _diasConfigurados[bid] = {};
      _diasConfigurados[bid].turno = turnoBase;
    }
  }

  // 3) Duración
  const durEl = document.querySelector(`.campo-duracion[data-bid="${bid}"]`);
  if (durEl) {
    if (especial === "12hrs") {
      if (turnoBase === "turno3") durEl.value = "03:30";
      else if (turnoBase === "turno2") durEl.value = "04:30";
      _diasConfigurados[bid].duracion = durEl.value;
    } else if (
      especial === "normal" &&
      reg.horai &&
      reg.horaf &&
      ![8, 9, 10, 12].includes(motivoNum)
    ) {
      const dur = calcularDuracionEntre(reg.horai, reg.horaf);
      if (dur) {
        durEl.value = dur;
        _diasConfigurados[bid].duracion = dur;
      }
    }
  }

  // 3b) Motivo 8 (horas cambio)
  if (motivoNum === 8) {
    const cambioEl = document.querySelector(`.campo-cambio[data-bid="${bid}"]`);
    if (cambioEl) {
      const hf = String(reg.horaf).substring(0, 5);
      if (hf === "03:30") cambioEl.value = "2.5";
      else if (hf === "04:00") cambioEl.value = "3";
      else if (hf === "06:00") cambioEl.value = "5";
    }
  }

  // 3c) Motivo 9 (comida)
  if (motivoNum === 9) {
    const comidaEl = document.querySelector(`.campo-comida[data-bid="${bid}"]`);
    if (comidaEl) {
      const hf = String(reg.horaf).substring(0, 5);
      if (hf === "12:30") comidaEl.value = "00:30";
      else if (hf === "13:00") comidaEl.value = "01:00";
    }
  }

  // 4) Casilla 12hrs y radio del caso
  evaluarVisibilidad12hrs(bid);
  const espEl = document.querySelector(
    `input[name="especial-${bid}"][value="${especial}"]`,
  );
  if (espEl) espEl.checked = true;

  if (especial === "reingreso" && reg.horai) {
    const manWrap = document.querySelector(
      `.campo-hora-manual-wrap[data-bid="${bid}"]`,
    );
    if (manWrap) manWrap.style.display = "block";
    const manEl = document.querySelector(
      `.campo-hora-manual[data-bid="${bid}"]`,
    );
    if (manEl) manEl.value = reg.horai;
  }

  // 5) Recalcular
  if (!_diasConfigurados[bid]) _diasConfigurados[bid] = {};
  _diasConfigurados[bid].especial = especial;
  recalcularHorasDia(bid);

  // 6) Razón
  const razEl = document.querySelector(`.campo-razon[data-bid="${bid}"]`);
  if (razEl && razonReg) razEl.value = razonReg;

  // 7) Forzar horas exactas guardadas
  const horaIEl = document.querySelector(`.campo-horai[data-bid="${bid}"]`);
  const horaFEl = document.querySelector(`.campo-horaf[data-bid="${bid}"]`);
  if (horaIEl && reg.horai) horaIEl.value = reg.horai;
  if (horaFEl && reg.horaf) horaFEl.value = reg.horaf;

  // Guardar id existente para hacer UPDATE (borrar+insertar)
  _diasConfigurados[bid]._idExistente = reg.id;
}

function calcularDuracionEntre(horaI, horaF) {
  try {
    const hm = (h) => {
      const p = h.split(":").map(Number);
      return p[0] * 60 + p[1];
    };
    let ini = hm(horaI),
      fin = hm(horaF);
    if (fin < ini) fin += 1440;
    const diff = fin - ini;
    if (diff <= 0) return null;
    return (
      Math.floor(diff / 60)
        .toString()
        .padStart(2, "0") +
      ":" +
      String(diff % 60).padStart(2, "0")
    );
  } catch (e) {
    return null;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Funciones por sub-bloque (reciben bid)
// ─────────────────────────────────────────────────────────────────────────────
function onCambioMotivoDia(bid) {
  const select = document.querySelector(`.campo-motivo[data-bid="${bid}"]`);
  const motivoNum = parseInt(select.value);
  const razonEl = document.querySelector(`.campo-razon[data-bid="${bid}"]`);

  const show = (sel) => {
    const el = document.querySelector(sel);
    if (el) el.style.display = "block";
  };
  const hide = (sel) => {
    const el = document.querySelector(sel);
    if (el) el.style.display = "none";
  };

  show(`.campo-turno-wrap[data-bid="${bid}"]`);
  show(`.campo-duracion-wrap[data-bid="${bid}"]`);
  show(`.campo-razon-wrap[data-bid="${bid}"]`);
  hide(`.campo-cambio-wrap[data-bid="${bid}"]`);
  hide(`.campo-comida-wrap[data-bid="${bid}"]`);

  const espWrap = document.querySelector(`.especiales-wrap[data-bid="${bid}"]`);
  if (espWrap) espWrap.style.display = "block";

  if (motivoNum === 8) {
    hide(`.campo-turno-wrap[data-bid="${bid}"]`);
    hide(`.campo-duracion-wrap[data-bid="${bid}"]`);
    show(`.campo-cambio-wrap[data-bid="${bid}"]`);
    if (espWrap) espWrap.style.display = "none";
    if (razonEl) razonEl.value = select.options[select.selectedIndex].text;
  } else if (motivoNum === 9) {
    hide(`.campo-turno-wrap[data-bid="${bid}"]`);
    hide(`.campo-duracion-wrap[data-bid="${bid}"]`);
    show(`.campo-comida-wrap[data-bid="${bid}"]`);
    if (espWrap) espWrap.style.display = "none";
    if (razonEl) razonEl.value = select.options[select.selectedIndex].text;
  } else if (motivoNum === 10 || motivoNum === 12) {
    hide(`.campo-duracion-wrap[data-bid="${bid}"]`);
    if (espWrap) espWrap.style.display = "none";
    if (razonEl) razonEl.value = select.options[select.selectedIndex].text;
  } else if (motivoNum === 7) {
    if (razonEl) razonEl.value = "";
  } else {
    if (razonEl) razonEl.value = select.options[select.selectedIndex].text;
  }

  if (!_diasConfigurados[bid]) _diasConfigurados[bid] = {};
  _diasConfigurados[bid].motivo = select.value;
  recalcularHorasDia(bid);

  // ───────────────────────────────────────────────
  // Regla: ocultar botón "Agregar otro registro"
  // si el primer registro tiene motivo 10 o 12
  // ───────────────────────────────────────────────
  const fecha = document
    .querySelector(`.campo-motivo[data-bid="${bid}"]`)
    ?.closest(".card-dia")?.dataset?.fecha;
  if (fecha) {
    const bids = bidsDeFecha(fecha);
    if (bids.length > 0 && bids[0] === bid) {
      const btnAdd = document.querySelector(
        `#paginador-${fecha} .sub-paginador__add`,
      );
      if (btnAdd) {
        if (motivoNum === 10 || motivoNum === 12) {
          btnAdd.style.display = "none";
        } else {
          btnAdd.style.display = "";
        }
      }
    }
  }
}

function onCambioTurnoDia(bid) {
  if (!_diasConfigurados[bid]) _diasConfigurados[bid] = {};
  const sel = document.querySelector(`.campo-turno[data-bid="${bid}"]`);
  _diasConfigurados[bid].turno = sel.value;
  evaluarVisibilidad12hrs(bid);
  recalcularHorasDia(bid);
}

function onCambioDuracionDia(bid) {
  if (!_diasConfigurados[bid]) _diasConfigurados[bid] = {};
  const inp = document.querySelector(`.campo-duracion[data-bid="${bid}"]`);

  // --- Formateo automático hh:mm ---
  let valor = inp.value.replace(/[^0-9]/g, ""); // solo números
  if (valor.length > 2) {
    valor = valor.slice(0, 2) + ":" + valor.slice(2, 4);
  }
  inp.value = valor;
  // ----------------------------------

  _diasConfigurados[bid].duracion = inp.value;
  evaluarVisibilidad12hrs(bid);
  recalcularHorasDia(bid);
}

function evaluarVisibilidad12hrs(bid) {
  const turno =
    document.querySelector(`.campo-turno[data-bid="${bid}"]`)?.value || "";
  const durStr =
    document.querySelector(`.campo-duracion[data-bid="${bid}"]`)?.value || "";
  const wrap = document.querySelector(`.chk12-wrap[data-bid="${bid}"]`);
  const radio12 = document.querySelector(
    `input[name="especial-${bid}"][value="12hrs"]`,
  );
  if (!wrap) return;

  const hm = (h) => {
    const [a, b] = h.split(":").map(Number);
    return a * 60 + b;
  };
  const minutos = durStr && /^\d{1,2}:\d{2}$/.test(durStr) ? hm(durStr) : 0;

  if (
    (turno === "turno3" || turno === "turno2") &&
    minutos >= 210 &&
    minutos <= 270
  ) {
    wrap.style.display = "inline-block";
  } else {
    wrap.style.display = "none";
    if (radio12 && radio12.checked) {
      const normal = document.querySelector(
        `input[name="especial-${bid}"][value="normal"]`,
      );
      if (normal) {
        normal.checked = true;
        onEspecialDia(bid, "normal");
      }
    }
  }
}

function onEspecialDia(bid, valor) {
  if (!_diasConfigurados[bid]) _diasConfigurados[bid] = {};
  _diasConfigurados[bid].especial = valor;

  const wrap = document.querySelector(
    `.campo-hora-manual-wrap[data-bid="${bid}"]`,
  );
  if (wrap) wrap.style.display = valor === "reingreso" ? "block" : "none";

  if (valor === "12hrs") {
    const turnoSel =
      document.querySelector(`.campo-turno[data-bid="${bid}"]`)?.value || "";
    const durEl = document.querySelector(`.campo-duracion[data-bid="${bid}"]`);
    if (durEl) {
      if (turnoSel === "turno3") {
        durEl.value = "03:30";
        _diasConfigurados[bid].duracion = "03:30";
      } else if (turnoSel === "turno2") {
        durEl.value = "04:30";
        _diasConfigurados[bid].duracion = "04:30";
      }
    }
  }

  const razonEl = document.querySelector(`.campo-razon[data-bid="${bid}"]`);
  if (razonEl) {
    if (valor === "anticipo") razonEl.value = "Anticipo";
    else if (valor === "reingreso") razonEl.value = "Reingreso";
    else if (valor === "normal" || valor === "12hrs") {
      if (razonEl.value === "Anticipo" || razonEl.value === "Reingreso")
        razonEl.value = "";
    }
  }

  recalcularHorasDia(bid);
}

function recalcularHorasDia(bid) {
  const turnoSel =
    document.querySelector(`.campo-turno[data-bid="${bid}"]`)?.value || "";
  const durStr =
    document.querySelector(`.campo-duracion[data-bid="${bid}"]`)?.value || "";
  const especial =
    document.querySelector(`input[name="especial-${bid}"]:checked`)?.value ||
    "normal";
  const horaInpI = document.querySelector(`.campo-horai[data-bid="${bid}"]`);
  const horaInpF = document.querySelector(`.campo-horaf[data-bid="${bid}"]`);
  const motivoNum = parseInt(
    document.querySelector(`.campo-motivo[data-bid="${bid}"]`)?.value || "0",
  );
  if (!horaInpI || !horaInpF) return;

  const hm = (hora) => {
    const [h, m] = hora.split(":").map(Number);
    return h * 60 + m;
  };
  const mh = (min) => {
    min = ((min % 1440) + 1440) % 1440;
    return (
      Math.floor(min / 60)
        .toString()
        .padStart(2, "0") +
      ":" +
      String(min % 60).padStart(2, "0") +
      ":00"
    );
  };

  if (motivoNum === 8) {
    const cambioEl = document.querySelector(`.campo-cambio[data-bid="${bid}"]`);
    const hra = parseFloat(cambioEl?.value || "3");
    if (hra === 3) {
      horaInpI.value = "01:00:00";
      horaInpF.value = "04:00:00";
    } else if (hra === 5) {
      horaInpI.value = "01:00:00";
      horaInpF.value = "06:00:00";
    } else if (hra === 2.5) {
      horaInpI.value = "01:00:00";
      horaInpF.value = "03:30:00";
    }
    return;
  }

  if (motivoNum === 9) {
    const comidaEl = document.querySelector(`.campo-comida[data-bid="${bid}"]`);
    const dur = comidaEl?.value || "01:00";
    horaInpI.value = "12:00:00";
    const [hc, mc] = dur.split(":").map(Number);
    horaInpF.value = mh(12 * 60 + hc * 60 + mc);
    return;
  }

  if (
    (motivoNum === 10 || motivoNum === 12) &&
    turnoSel &&
    TURNOS_MULTIDIA[turnoSel]
  ) {
    horaInpI.value = TURNOS_MULTIDIA[turnoSel].inicioTurno;
    horaInpF.value = TURNOS_MULTIDIA[turnoSel].inicioSalida;
    return;
  }

  if (!turnoSel || !TURNOS_MULTIDIA[turnoSel]) {
    horaInpI.value = "";
    horaInpF.value = "";
    return;
  }

  if (especial === "reingreso") {
    const horaManual =
      document.querySelector(`.campo-hora-manual[data-bid="${bid}"]`)?.value ||
      "";
    horaInpI.value = horaManual || "";
    horaInpF.value = "00:00:00";
    return;
  }

  if (!durStr || !/^\d{1,2}:\d{2}$/.test(durStr)) {
    horaInpI.value = "";
    horaInpF.value = "";
    return;
  }

  const durMin = hm(durStr);
  const turnoInicioSalida = hm(TURNOS_MULTIDIA[turnoSel].inicioSalida);
  const turnoInicio = hm(TURNOS_MULTIDIA[turnoSel].inicioTurno);

  if (especial === "anticipo") {
    horaInpI.value = mh(turnoInicio - durMin);
    horaInpF.value = mh(turnoInicio);
  } else if (especial === "12hrs") {
    if (turnoSel === "turno2") {
      horaInpI.value = "10:30:00";
      horaInpF.value = "22:30:00";
    } else if (turnoSel === "turno3") {
      horaInpI.value = "19:00:00";
      horaInpF.value = "07:00:00";
    } else {
      horaInpI.value = "";
      horaInpF.value = "";
    }
  } else {
    horaInpI.value = mh(turnoInicioSalida);
    horaInpF.value = mh(turnoInicioSalida + durMin);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// copiarConfiguracion — copia TODOS los sub-bloques de un día a otros días libres
// ─────────────────────────────────────────────────────────────────────────────
async function copiarConfiguracion(fechaOrigen) {
  const otrosDias = [];
  for (let i = 0; i < 7; i++) {
    const f = new Date(_semanaBase);
    f.setDate(_semanaBase.getDate() + i);
    const fs = formatFecha(f);
    if (fs === fechaOrigen) continue;
    // ¿el día destino ya tiene algún motivo configurado?
    const yaConfig = bidsDeFecha(fs).some(
      (bid) =>
        (document.querySelector(`.campo-motivo[data-bid="${bid}"]`)?.value ||
          "") !== "",
    );
    otrosDias.push({
      fecha: fs,
      label: DIAS_SEMANA[i] + " " + fs,
      yaConfigurado: yaConfig,
    });
  }

  const disponibles = otrosDias.filter((d) => !d.yaConfigurado);
  if (disponibles.length === 0) {
    Swal.fire(
      "Sin días disponibles",
      "Todos los demás días ya tienen configuración. Limpia alguno para sobrescribirlo.",
      "info",
    );
    return;
  }

  const checkboxesHtml = disponibles
    .map(
      (d) =>
        `<div><label><input type="checkbox" value="${d.fecha}" checked> ${d.label}</label></div>`,
    )
    .join("");
  const yaConfigHtml =
    otrosDias.filter((d) => d.yaConfigurado).length > 0
      ? `<hr><small class="text-muted">Días ya configurados (no se sobrescriben): ${otrosDias
          .filter((d) => d.yaConfigurado)
          .map((d) => d.label)
          .join(", ")}</small>`
      : "";

  const { isConfirmed, value } = await Swal.fire({
    title: "Copiar día completo",
    html: `<p>Copiar TODOS los registros de <b>${fechaOrigen}</b> a:</p>
               <div id="swal-dias-copia" style="text-align:left; max-height:200px; overflow-y:auto; padding:8px;">${checkboxesHtml}</div>${yaConfigHtml}`,
    showCancelButton: true,
    confirmButtonText: "Copiar",
    cancelButtonText: "Cancelar",
    preConfirm: () =>
      Array.from(
        document.querySelectorAll(
          "#swal-dias-copia input[type=checkbox]:checked",
        ),
      ).map((c) => c.value),
  });
  if (!isConfirmed || !value || value.length === 0) return;

  // Leer la configuración de cada sub-bloque del día origen
  const bidsOrigen = bidsDeFecha(fechaOrigen);
  const config = bidsOrigen.map((bid) => ({
    motivo:
      document.querySelector(`.campo-motivo[data-bid="${bid}"]`)?.value || "",
    turno:
      document.querySelector(`.campo-turno[data-bid="${bid}"]`)?.value || "",
    dur:
      document.querySelector(`.campo-duracion[data-bid="${bid}"]`)?.value || "",
    razon:
      document.querySelector(`.campo-razon[data-bid="${bid}"]`)?.value || "",
    espec:
      document.querySelector(`input[name="especial-${bid}"]:checked`)?.value ||
      "normal",
    cambio:
      document.querySelector(`.campo-cambio[data-bid="${bid}"]`)?.value || "3",
    comida:
      document.querySelector(`.campo-comida[data-bid="${bid}"]`)?.value ||
      "01:00",
    horaMan:
      document.querySelector(`.campo-hora-manual[data-bid="${bid}"]`)?.value ||
      "",
  }));

  value.forEach((fdest) => {
    const chk = document.getElementById(`chk-dia-${fdest}`);
    if (chk && !chk.checked) {
      chk.checked = true;
      toggleCardDia(fdest);
    }

    // Asegurar misma cantidad de sub-bloques
    let bidsDest = bidsDeFecha(fdest);
    while (bidsDest.length < config.length) {
      agregarSubBloque(fdest);
      bidsDest = bidsDeFecha(fdest);
    }

    config.forEach((cfg, i) => {
      const bid = bidsDest[i];
      if (!bid) return;
      const motEl = document.querySelector(`.campo-motivo[data-bid="${bid}"]`);
      if (motEl) {
        motEl.value = cfg.motivo;
        onCambioMotivoDia(bid);
      }
      const turEl = document.querySelector(`.campo-turno[data-bid="${bid}"]`);
      if (turEl) turEl.value = cfg.turno;
      const durEl = document.querySelector(
        `.campo-duracion[data-bid="${bid}"]`,
      );
      if (durEl) durEl.value = cfg.dur;
      const camEl = document.querySelector(`.campo-cambio[data-bid="${bid}"]`);
      if (camEl) camEl.value = cfg.cambio;
      const comEl = document.querySelector(`.campo-comida[data-bid="${bid}"]`);
      if (comEl) comEl.value = cfg.comida;
      const manEl = document.querySelector(
        `.campo-hora-manual[data-bid="${bid}"]`,
      );
      if (manEl) manEl.value = cfg.horaMan;
      evaluarVisibilidad12hrs(bid);
      const espEl = document.querySelector(
        `input[name="especial-${bid}"][value="${cfg.espec}"]`,
      );
      if (espEl) {
        espEl.checked = true;
        onEspecialDia(bid, cfg.espec);
      }
      const razEl = document.querySelector(`.campo-razon[data-bid="${bid}"]`);
      if (razEl) razEl.value = cfg.razon;
      recalcularHorasDia(bid);
    });

    // Re-aplicar restricciones por si el destino tiene 2º registro o IBM especial
    aplicarRestriccionSegundoRegistro(fdest);
    aplicarRestriccionIbmEspecial(
      document.getElementById("multiNoemp")?.value || "",
    );

    // Dejar el paginador del día destino en el primer registro
    mostrarSubBloque(fdest, 0);
  });

  Swal.fire(
    "Listo",
    `Configuración copiada a ${value.length} día(s).`,
    "success",
  );
}

// limpiarSubBloque — limpia un sub-bloque individual
function limpiarSubBloque(bid) {
  delete _diasConfigurados[bid];
  const set = (sel, val = "") => {
    const el = document.querySelector(sel);
    if (el) el.value = val;
  };
  set(`.campo-motivo[data-bid="${bid}"]`);
  set(`.campo-turno[data-bid="${bid}"]`);
  set(`.campo-duracion[data-bid="${bid}"]`);
  set(`.campo-razon[data-bid="${bid}"]`);
  set(`.campo-horai[data-bid="${bid}"]`);
  set(`.campo-horaf[data-bid="${bid}"]`);
  set(`.campo-hora-manual[data-bid="${bid}"]`);
  const normal = document.querySelector(
    `input[name="especial-${bid}"][value="normal"]`,
  );
  if (normal) normal.checked = true;
  onCambioMotivoDia(bid);
}

// limpiarCardDia — limpia un día completo: deja 1 sub-bloque vacío y resetea badge/borde
function limpiarCardDia(fecha) {
  const cont = document.getElementById(`subs-${fecha}`);
  if (cont) {
    // Eliminar todos los sub-bloques menos el primero
    const bloques = Array.from(cont.querySelectorAll(".sub-bloque"));
    bloques.forEach((b, i) => {
      const bid = b.getAttribute("data-bid");
      delete _diasConfigurados[bid];
      if (i > 0) b.remove();
    });
    // Limpiar el primero
    const primerBid = bidsDeFecha(fecha)[0];
    if (primerBid) limpiarSubBloque(primerBid);
    _subActivo[fecha] = 0;
    actualizarNumerosSubBloques(fecha);
  }

  const card = document.getElementById(`card-dia-${fecha}`);
  if (card) card.style.borderColor = "";
  const badge = card?.querySelector(".card-dia__header .badge");
  const chk = document.getElementById(`chk-dia-${fecha}`);
  if (badge) {
    const existentes = _registrosExistentes[fecha] || [];
    if (existentes.length > 0) {
      badge.className = "badge bg-warning text-dark ms-auto";
      badge.textContent =
        existentes.length > 1
          ? `Ya existe (${existentes.length})`
          : "Ya existe";
      if (card) card.style.borderColor = "#ffc107";
    } else {
      const activo = chk && chk.checked;
      badge.className = activo
        ? "badge bg-success ms-auto"
        : "badge bg-secondary ms-auto";
      badge.textContent = activo ? "Configurado" : "Sin registrar";
    }
  }
}

function seleccionarTodosDias(activar) {
  for (let i = 0; i < 7; i++) {
    const f = new Date(_semanaBase);
    f.setDate(_semanaBase.getDate() + i);
    const fs = formatFecha(f);
    const chk = document.getElementById(`chk-dia-${fs}`);
    if (!chk) continue;
    if (activar) {
      chk.checked = true;
      bidsDeFecha(fs).forEach((bid) => {
        if (!_diasConfigurados[bid]) _diasConfigurados[bid] = {};
        _diasConfigurados[bid]._precargado = true;
      });
      toggleCardDia(fs);
      // Activar todos los deja colapsados para no saturar la pantalla
      colapsarCard(fs);
    } else {
      limpiarCardDia(fs);
      chk.checked = false;
      toggleCardDia(fs);
    }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// leerConfigBloque — ensambla los datos de UN sub-bloque para enviarlo
// ─────────────────────────────────────────────────────────────────────────────
function leerConfigBloque(bid) {
  const fecha = bidToFecha(bid);
  const motivoEl = document.querySelector(`.campo-motivo[data-bid="${bid}"]`);
  const turnoEl = document.querySelector(`.campo-turno[data-bid="${bid}"]`);
  const durEl = document.querySelector(`.campo-duracion[data-bid="${bid}"]`);
  const razonEl = document.querySelector(`.campo-razon[data-bid="${bid}"]`);
  const horaIEl = document.querySelector(`.campo-horai[data-bid="${bid}"]`);
  const horaFEl = document.querySelector(`.campo-horaf[data-bid="${bid}"]`);
  const especEl = document.querySelector(
    `input[name="especial-${bid}"]:checked`,
  );
  const cambioEl = document.querySelector(`.campo-cambio[data-bid="${bid}"]`);
  const comidaEl = document.querySelector(`.campo-comida[data-bid="${bid}"]`);
  const horaManEl = document.querySelector(
    `.campo-hora-manual[data-bid="${bid}"]`,
  );

  const motivoNum = parseInt(motivoEl?.value || "0");
  const especial = especEl?.value || "normal";

  let horai = horaIEl?.value || "";
  let horaf = horaFEl?.value || "";
  let turno = turnoEl?.value || "";
  let razon = razonEl?.value || "";
  let duracion = durEl?.value || "";
  const turnoBase = turnoEl?.value || "";

  if (motivoNum === 8) {
    const hra = parseFloat(cambioEl?.value || "3");
    if (hra === 3) {
      horai = "01:00:00";
      horaf = "04:00:00";
    } else if (hra === 5) {
      horai = "01:00:00";
      horaf = "06:00:00";
    } else if (hra === 2.5) {
      horai = "01:00:00";
      horaf = "03:30:00";
    }
    turno = "turno1";
  } else if (motivoNum === 9) {
    const dur = comidaEl?.value || "01:00";
    horai = "12:00:00";
    const [hc, mc] = dur.split(":").map(Number);
    const fin = 12 * 60 + hc * 60 + mc;
    horaf =
      Math.floor(fin / 60)
        .toString()
        .padStart(2, "0") +
      ":" +
      String(fin % 60).padStart(2, "0") +
      ":00";
    turno = "turno1";
  }

  if (especial === "anticipo") razon = "Anticipo";
  if (especial === "reingreso") {
    razon = "Reingreso";
    horai = horaManEl?.value || "00:00:00";
    horaf = "00:00:00";
    turno = "turno1";
  }
  if (especial === "12hrs") {
    if (turno === "turno3") turno = "turno3_12hrs";
    else if (turno === "turno2") turno = "turno2_13hrs";
  }

  return {
    bid,
    fecha,
    motivo: motivoEl?.value || "",
    turno,
    turnoBase,
    horai,
    horaf,
    razon,
    duracion,
    especial,
    idExistente: _diasConfigurados[bid]?._idExistente || null,
  };
}

function validarConfigBloque(cfg) {
  const errores = [];
  if (!cfg.motivo) errores.push("Falta el motivo.");
  if (!cfg.horai) errores.push("Falta la hora de inicio.");
  if (!cfg.horaf) errores.push("Falta la hora de fin.");
  if (
    ![8, 9].includes(parseInt(cfg.motivo)) &&
    cfg.especial !== "reingreso" &&
    !cfg.turno
  )
    errores.push("Falta el turno.");
  return errores;
}

function parseRespuestaSegura(texto) {
  if (texto === null || texto === undefined) return null;
  const limpio = String(texto).trim();
  try {
    return JSON.parse(limpio);
  } catch (e) {}
  const sinComillas = limpio.replace(/^"+|"+$/g, "");
  if (["Listo", "Existe", "LimiteSemana"].includes(sinComillas))
    return sinComillas;
  const match = limpio.match(/\{[\s\S]*\}/);
  if (match) {
    try {
      return JSON.parse(match[0]);
    } catch (e) {}
  }
  if (limpio.includes("Listo")) return "Listo";
  if (limpio.includes("LimiteSemana")) return "LimiteSemana";
  if (limpio.includes("Existe")) return "Existe";
  return null;
}

function descripcionError(resp, textoRaw) {
  if (resp && typeof resp === "object") {
    if (resp.message) return resp.message;
    return JSON.stringify(resp);
  }
  if (typeof resp === "string" && resp) return resp;
  const frag = String(textoRaw || "")
    .replace(/\s+/g, " ")
    .trim()
    .slice(0, 120);
  return frag
    ? "Respuesta inesperada del servidor: " + frag
    : "Respuesta vacía del servidor.";
}

// ─────────────────────────────────────────────────────────────────────────────
// guardarTodosDias — recorre todos los sub-bloques de los días activos
// ─────────────────────────────────────────────────────────────────────────────
async function guardarTodosDias() {
  const noemp = document.getElementById("multiNoemp").value;
  const nombre = document.getElementById("multiNombre").value;
  const folio = document.getElementById("folio").value;
  const maquina = document.getElementById("multiMaquinas").value;

  if (!folio) {
    Swal.fire("Sin folio", "Debes crear un folio primero.", "warning");
    return;
  }
  if (!noemp || !nombre) {
    Swal.fire("Sin empleado", "Ingresa el número de empleado.", "warning");
    return;
  }
  if (!maquina) {
    Swal.fire("Sin maquina", "Selecciona una maquina.", "warning");
    return;
  }

  // Recolectar todos los bids de los días activos
  let bidsActivos = [];
  const diasActivos = [];
  for (let i = 0; i < 7; i++) {
    const f = new Date(_semanaBase);
    f.setDate(_semanaBase.getDate() + i);
    const fs = formatFecha(f);
    const chk = document.getElementById(`chk-dia-${fs}`);
    if (chk && chk.checked) {
      diasActivos.push(fs);
      bidsActivos = bidsActivos.concat(bidsDeFecha(fs));
    }
  }
  if (bidsActivos.length === 0) {
    Swal.fire("Sin días", "Activa al menos un día para guardar.", "warning");
    return;
  }

  // ── Validar todos los bloques (campos vacíos) ──
  const erroresPrevios = [];
  bidsActivos.forEach((bid) => {
    const cfg = leerConfigBloque(bid);
    const errs = validarConfigBloque(cfg);
    if (errs.length > 0)
      erroresPrevios.push(`<b>${cfg.fecha}</b> (registro): ${errs.join(" ")}`);
  });
  if (erroresPrevios.length > 0) {
    Swal.fire(
      "Campos incompletos",
      `Corrige antes de guardar:<br><br>${erroresPrevios.join("<br>")}`,
      "warning",
    );
    return;
  }

  // ── Restricción IBMs especiales (solo motivos 10 y 12) ──
  if (IBM_ESPECIALES.includes(parseInt(noemp))) {
    const diasInvalidos = [];
    bidsActivos.forEach((bid) => {
      const m = parseInt(leerConfigBloque(bid).motivo);
      if (!MOTIVOS_IBM_ESPECIAL.map(Number).includes(m))
        diasInvalidos.push(bidToFecha(bid));
    });
    if (diasInvalidos.length > 0) {
      Swal.fire(
        "Restricción",
        `El empleado <b>${noemp}</b> solo puede solicitar <b>Descanso Trabajado</b> o <b>Día Festivo</b>.<br><br>Corrige: ${[...new Set(diasInvalidos)].join(", ")}`,
        "warning",
      );
      return;
    }
  }

  const totalReg = bidsActivos.length;

  const confirm = await Swal.fire({
    title: "Guardar registros múltiples",
    html: `Se guardarán <b>${totalReg}</b> registro(s) en <b>${diasActivos.length}</b> día(s) para <b>${nombre}</b>.`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, guardar todos",
    cancelButtonText: "Cancelar",
  });
  if (!confirm.isConfirmed) return;

  // ── Validación de doblete + 12HRS por bloque (igual que el individual) ──
  const razonOverride = {};
  if (
    typeof evaluarDoblete === "function" &&
    typeof horaAMinutos === "function"
  ) {
    for (const bid of bidsActivos) {
      const cfg = leerConfigBloque(bid);
      const motivoNum = parseInt(cfg.motivo);
      if ([8, 9, 10, 12].includes(motivoNum)) continue;
      if (cfg.especial === "reingreso") continue;

      const durStr = cfg.duracion;
      if (!durStr || !/^\d{1,2}:\d{2}$/.test(durStr)) continue;

      const durMin = horaAMinutos(durStr);
      const esSabado = [6, 0].includes(
        new Date(cfg.fecha + "T00:00:00").getDay(),
      );
      const resultado = evaluarDoblete(cfg.turnoBase, durMin, esSabado);

      // 3:30–4:30 en turno2/turno3: obliga a marcar el caso "12 hrs"
      if (resultado === "12HRS" && cfg.especial !== "12hrs") {
        await Swal.fire(
          "Falta marcar 12 hrs",
          `<b>${cfg.fecha}</b> — La cantidad (<b>${durStr}</b>) en turno <b>${cfg.turnoBase}</b> corresponde a un caso de <b>12 horas</b>. Selecciona el caso <b>"12 hrs"</b> en ese registro.`,
          "warning",
        );
        return;
      }

      if (resultado === "ERROR_LIMITE") {
        await Swal.fire(
          "Cantidad no permitida",
          `<b>${cfg.fecha}</b> — En turno <b>${cfg.turnoBase}</b> la cantidad (${durStr}) no es válida. Corrige ese registro.`,
          "warning",
        );
        return;
      }

      if (resultado === "DOBLETE" && cfg.especial !== "anticipo") {
        const cd = await Swal.fire({
          title: `Doblete — ${cfg.fecha}`,
          html: `La cantidad (<b>${durStr}</b>) en turno <b>${cfg.turnoBase}</b> es un DOBLETE.<br>Se guardará con razón <b>"Doblete"</b>.`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, es doblete",
          cancelButtonText: "Cancelar todo",
        });
        if (!cd.isConfirmed) return;
        razonOverride[bid] = "Doblete";
        const razEl = document.querySelector(`.campo-razon[data-bid="${bid}"]`);
        if (razEl) razEl.value = "Doblete";
      }
    }
  }

  // ── Confirmación de SOBRESCRITURA ─────────────────────────────────────
  // Días que ya tienen registro(s) en BD pero cuyos bloques NO se cargaron
  // (el usuario dijo "No" a precargar y escribió encima).
  const diasOmitidos = new Set();
  const idsABorrarPorDia = {}; // { fecha: [id, id...] } para los que SÍ confirma reescribir

  for (const fs of diasActivos) {
    const existentes = _registrosExistentes[fs] || [];
    if (existentes.length === 0) continue;

    const algunoCargado = bidsDeFecha(fs).some(
      (bid) => _diasConfigurados[bid] && _diasConfigurados[bid]._idExistente,
    );
    if (algunoCargado) continue; // se editó lo existente intencionalmente, no preguntamos

    const detalle = existentes
      .map(
        (r, i) =>
          `<li>#${i + 1} — <b>${r.motivo || "-"}</b> ${r.horai || "-"} a ${r.horaf || "-"} (${r.razon || "-"})</li>`,
      )
      .join("");

    const conf = await Swal.fire({
      title: `Ya existen datos — ${fs}`,
      html: `El día <b>${fs}</b> ya tiene <b>${existentes.length}</b> registro(s) guardado(s):
                   <ul style="text-align:left">${detalle}</ul>
                   Estás a punto de <b>reescribirlos</b> con lo que capturaste. ¿Deseas continuar?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, reescribir",
      cancelButtonText: "No, conservar los anteriores",
    });

    if (conf.isConfirmed) {
      idsABorrarPorDia[fs] = existentes.map((r) => r.id);
    } else {
      diasOmitidos.add(fs); // este día NO se guarda; los demás sí
    }
  }

  // Si TODOS los días quedaron omitidos, no hay nada que guardar
  const bidsOmitidos = bidsActivos.filter((bid) =>
    diasOmitidos.has(bidToFecha(bid)),
  );
  const totalEfectivo = totalReg - bidsOmitidos.length;
  if (totalEfectivo === 0) {
    Swal.fire(
      "Sin cambios",
      "No se guardó ningún día (todos fueron omitidos).",
      "info",
    );
    return;
  }

  // Borrar los registros previos SOLO de los días confirmados para reescribir
  for (const fs of Object.keys(idsABorrarPorDia)) {
    for (const id of idsABorrarPorDia[fs]) {
      try {
        const dataDel = new FormData();
        dataDel.append("id", id);
        await fetch("./php/index.php?deletesub", {
          method: "POST",
          body: dataDel,
        });
      } catch (e) {
        console.warn("No se pudo borrar registro previo", e);
      }
    }
  }

  const btn = document.getElementById("btnGuardarMultiple");
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
  }

  let exitosos = 0,
    fallidos = [];
  for (const bid of bidsActivos) {
    const cfg = leerConfigBloque(bid);

    // Omitir los días que el usuario decidió NO reescribir
    if (diasOmitidos.has(cfg.fecha)) continue;

    if (razonOverride[bid]) cfg.razon = razonOverride[bid];

    try {
      // UPDATE: si el bloque venía de un registro existente, borrarlo primero
      if (cfg.idExistente) {
        try {
          const dataDel = new FormData();
          dataDel.append("id", cfg.idExistente);
          await fetch("./php/index.php?deletesub", {
            method: "POST",
            body: dataDel,
          });
        } catch (eDel) {
          console.warn("No se pudo borrar registro previo", eDel);
        }
      }

      const data = new FormData();
      data.append("noemp", noemp);
      data.append("fechainput", cfg.fecha);
      data.append("horai", cfg.horai);
      data.append("horaf", cfg.horaf);
      data.append("maquina", maquina);
      data.append("motivos", cfg.motivo);
      data.append("razon", cfg.razon);
      data.append("folio", folio);
      data.append("turnosel", cfg.turno);
      data.append("nombre", nombre);

      const r = await fetch("php/index.php?guardartiempoextra", {
        method: "POST",
        body: data,
      });
      const textoRaw = await r.text();
      const resp = parseRespuestaSegura(textoRaw);

      if (resp === "Listo") {
        exitosos++;
        if (_diasConfigurados[bid]) delete _diasConfigurados[bid]._idExistente;
      } else if (resp && resp.warning) {
        const cw = await Swal.fire({
          title: `Advertencia — ${cfg.fecha}`,
          text: resp.message,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, guardar igual",
          cancelButtonText: "Omitir",
        });
        if (cw.isConfirmed) {
          const dataExt = new FormData();
          data.forEach((v, k) => dataExt.append(k, v));
          const r2 = await fetch("php/index.php?guardartiempoextraExt", {
            method: "POST",
            body: dataExt,
          });
          const resp2 = parseRespuestaSegura(await r2.text());
          if (resp2 === "Listo") {
            exitosos++;
            if (_diasConfigurados[bid])
              delete _diasConfigurados[bid]._idExistente;
          } else
            fallidos.push({
              fecha: cfg.fecha,
              error: descripcionError(resp2, ""),
            });
        } else
          fallidos.push({ fecha: cfg.fecha, error: "Omitido por el usuario." });
      } else if (resp && resp.error_especial) {
        fallidos.push({ fecha: cfg.fecha, error: resp.message });
      } else if (resp === "Existe") {
        fallidos.push({
          fecha: cfg.fecha,
          error: "Ya existe un tiempo extra igual.",
        });
      } else if (resp === "LimiteSemana") {
        fallidos.push({
          fecha: cfg.fecha,
          error: "Se alcanzó el límite de 60.5 hrs.",
        });
      } else {
        fallidos.push({
          fecha: cfg.fecha,
          error: descripcionError(resp, textoRaw),
        });
      }
    } catch (err) {
      fallidos.push({ fecha: cfg.fecha, error: err.message });
    }
  }

  // Marcar cards de días totalmente exitosos (omitidos no cuentan como fallidos)
  diasActivos.forEach((fs) => {
    if (diasOmitidos.has(fs)) return;
    if (!fallidos.some((f) => f.fecha === fs)) marcarCardGuardada(fs);
  });

  if (btn) {
    btn.disabled = false;
    btn.innerHTML =
      '<i class="fa-solid fa-floppy-disk"></i> Guardar todos los días';
  }

  let htmlResultado = `<b>${exitosos}</b> de <b>${totalEfectivo}</b> registro(s) guardado(s) correctamente.`;
  if (diasOmitidos.size > 0) {
    htmlResultado += `<br><br>Días omitidos por tu elección (se conservaron los datos anteriores): <b>${[...diasOmitidos].join(", ")}</b>.`;
  }
  if (fallidos.length > 0) {
    htmlResultado += `<br><br>Con problemas:<ul style="text-align:left">${fallidos.map((f) => `<li><b>${f.fecha}</b>: ${f.error}</li>`).join("")}</ul>`;
  }

  await Swal.fire(
    exitosos === totalEfectivo ? "¡Listo!" : "Completado con advertencias",
    htmlResultado,
    exitosos === totalEfectivo ? "success" : "warning",
  );

  const TE = getTiempoextra();
  if (TE) {
    try {
      if (typeof TE.tblsubenc === "function") await TE.tblsubenc();
      if (typeof TE.tblenc === "function") await TE.tblenc();
    } catch (e) {
      console.warn("No se pudo recargar la tabla:", e);
    }
  }

  await marcarDiasExistentes(noemp);
}

function marcarCardGuardada(fecha) {
  const card = document.getElementById(`card-dia-${fecha}`);
  if (card) card.style.borderColor = "#28a745";
  const badge = card?.querySelector(".card-dia__header .badge");
  if (badge) {
    badge.className = "badge bg-success ms-auto";
    badge.textContent = "Guardado ✓";
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// TUTORIAL (driver.js)
// ─────────────────────────────────────────────────────────────────────────────
function lanzarTutorialMulti() {
  if (!window.driver || !window.driver.js) {
    console.warn(
      "Librerias no cargadas para mostrar tutorial, contacta al area de sistemas.",
    );
    return;
  }
  const driver = window.driver.js.driver;

  const f0 = new Date(_semanaBase);
  const fechaPrimera = formatFecha(f0);
  const primerBid = bidsDeFecha(fechaPrimera)[0] || makeBid(fechaPrimera, 0);

  const chk0 = document.getElementById(`chk-dia-${fechaPrimera}`);
  if (chk0 && !chk0.checked) {
    chk0.checked = true;
    toggleCardDia(fechaPrimera);
  }

  const pasos = [
    {
      element: "#multiNoemp",
      popover: {
        title: "Número de empleado",
        description:
          "Escribe el número de empleado, sus datos (nombre, departamento, puesto) y sus máquinas se cargarán automáticamente y aplicarán a TODOS los días que registres. Si el departamento no coincide con el del folio, te avisará.",
        side: "bottom",
      },
    },
    {
      element: "#multiNombre",
      popover: {
        title: "Datos del empleado",
        description:
          "El nombre, departamento y puesto se llenan solos al buscar el empleado. Estos datos son compartidos: el mismo empleado aplica para todos los días que actives.",
        side: "bottom",
      },
    },
    {
      element: "#multiMaquinas",
      popover: {
        title: "Máquina",
        description:
          "Selecciona la máquina. Se cargan las máquinas del departamento del folio, igual que en el registro individual. La máquina es la misma para todos los días seleccionados.",
        side: "bottom",
      },
    },
    {
      element: `#card-dia-${fechaPrimera}`,
      popover: {
        title: "Tarjeta de día",
        description:
          "Cada día de la semana (Lun a Dom) tiene su propia tarjeta independiente. Puedes configurar cada día con su propio motivo, turno, horas y caso especial.",
        side: "right",
      },
    },
    {
      element: `#card-dia-${fechaPrimera} .card-dia__check`,
      popover: {
        title: "Activar el día",
        description:
          "Marca esta casilla para activar el día. Solo los días activos se guardarán al final. Si lo desmarcas, ese día se ignora.",
        side: "right",
      },
    },
    {
      element: `#card-dia-${fechaPrimera} .badge`,
      popover: {
        title: "Estado del día",
        description:
          "La etiqueta indica el estado: <b style='color:#858585'>Sin registrar</b> (gris), <b style='color:#189E45'>Configurado</b> (verde) o <b style='color:#b8860b'>Ya existe</b> (amarillo) cuando el empleado YA tiene un tiempo extra ese día en este folio. Si ya existe y activas el día, te preguntará si deseas <b>recuperar esos datos</b> para revisarlos o editarlos, evitando que crees un registro duplicado.",
        side: "left",
        popoverClass: "popover-importante",
      },
    },
    {
      element: `.sub-paginador__nav`,
      popover: {
        title: "Navegacion entre tiempos extras de un mismo día",
        description:
          "Presiona las flechas de navegacion para desplazarte entre los registros existentes durante el mismo día, al centro encontraras la cantidad de solicitudes existentes para un empleado en un mismo día",
        side: "left",
      },
    },
    {
      element: `.sub-paginador__acciones`,
      popover: {
        title: "Agregar mas tiempos extras en un mismo día",
        description:
          "Presiona este boton para agregar mas de un registro en el mismo dia",
        side: "left",
        popoverClass: "popover-importante",
      },
    },
    {
      element: `.campo-motivo`,
      popover: {
        title: "Motivo (por día)",
        description:
          "Cada día puede tener su propio motivo. Según el motivo, se muestran u ocultan los campos correspondientes (igual que en el registro individual). La opción de Hora de Comida solo aparece para empleados sindicalizados.",
        side: "top",
      },
    },
    {
      element: `.campo-turno`,
      popover: {
        title: "Turno (por día)",
        description:
          "Selecciona el turno de ese día: Turno 1, 2, 3 o Mixto 1-4. La opción de 12 hrs aparece automáticamente solo cuando el turno es 2 o 3 y la cantidad de horas está entre 3:30 y 4:30.",
        side: "top",
      },
    },
    {
      element: `.campo-duracion`,
      popover: {
        title: "Cantidad de horas",
        description:
          "Escribe las horas de tiempo extra en formato <b>hh:mm</b>. Los campos De y A se calculan automáticamente según el turno y el caso especial. Para 12 hrs la duración se fija sola (3:30 en turno 3, 4:30 en turno 2).",
        side: "top",
      },
    },
    {
      element: `.campo-horai`,
      popover: {
        title: "Horas De / A",
        description:
          "Estos campos se llenan solos a partir del turno, la cantidad de horas y el caso especial. No es necesario escribirlos manualmente: para normal suman al final del turno, para anticipo restan al inicio, para 12 hrs usan el horario completo (turno 2: 10:30-22:30, turno 3: 19:00-07:00).",
        side: "top",
      },
    },
    {
      element: `.especiales-wrap`,
      popover: {
        title: "Caso especial (por día)",
        description:
          "Indica si ese día es <b>Normal</b>, <b>12 hrs</b>, <b>Anticipo</b> o <b>Reingreso</b>. Cada caso ajusta las horas De/A de forma distinta. En Reingreso podrás escribir la hora de inicio manualmente.",
        side: "top",
      },
    },
    {
      element: `.campo-razon`,
      popover: {
        title: "Razón (por día)",
        description:
          "La razón se llena automáticamente según el motivo o el caso especial (Anticipo, Reingreso). Si el motivo es 'Otros', podrás escribirla manualmente. La razón es independiente para cada día.",
        side: "top",
      },
    },
    {
      element: `#card-dia-${fechaPrimera} .btn-outline-primary`,
      popover: {
        title: "Copiar a otros días",
        description:
          "Si varios días comparten la misma configuración, configura uno y cópialo a los demás. Solo aparecerán como destino los días que aún no tengan configuración, para no sobrescribir lo que ya armaste.",
        side: "top",
      },
    },
    {
      element: `#card-dia-${fechaPrimera} .btn-outline-danger`,
      popover: {
        title: "Limpiar día",
        description:
          "Borra la configuración de ese día específico (en pantalla) sin afectar a los demás. Si el día ya estaba guardado en la base, esto no lo elimina de ahí; solo limpia la tarjeta.",
        side: "top",
      },
    },
    {
      element: "#btnGuardarMultiple",
      popover: {
        title: "Guardar todos los días",
        description:
          "Cuando termines, presiona aquí. Se guardan uno por uno todos los días activos y verás un resumen al final. Si un día ya tenía registro y recuperaste sus datos, se <b>actualiza</b> en vez de duplicarse. Si un día ya tenía datos y escribiste encima sin recuperarlos, te pedirá confirmación antes de reescribirlo. Recuerda validar y enviar cada registro después, como de costumbre.",
        side: "left",
        popoverClass: "popover-importante",
      },
    },
    {
      element: "#btnAyudaMulti",
      popover: {
        title: "Volver a ver el tutorial",
        description:
          "Si necesitas repasar cómo funciona la captura múltiple, presiona este botón para repetir el tutorial cuando quieras.",
        side: "left",
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
    steps: pasos,
  });
  driverObj.drive();
}

// ─────────────────────────────────────────────────────────────────────────────
// Funcion para apertura de validaciones en tiempos extras con redireccion
// ─────────────────────────────────────────────────────────────────────────────
function lanzarValidacion() {
  window.open("../Tiempoextra/Autorizatp.php");
}

// ─────────────────────────────────────────────────────────────────────────────
// Hook para refrescar badges al borrar desde la tabla inferior
// ─────────────────────────────────────────────────────────────────────────────
(function () {
  function instalarHookDelete() {
    if (typeof window.deleteSub !== "function")
      return setTimeout(instalarHookDelete, 300);
    if (window.deleteSub.__hookMulti) return;

    const original = window.deleteSub;
    const envuelta = function (id) {
      const r = original(id);
      const refrescar = () => {
        const panelVisible = document.getElementById("panelMultidiaWrap");
        const noemp = document.getElementById("multiNoemp")?.value;
        if (panelVisible && panelVisible.style.display !== "none" && noemp)
          setTimeout(() => marcarDiasExistentes(noemp, true), 600);
      };
      if (r && typeof r.then === "function") r.then(refrescar);
      else refrescar();
      return r;
    };
    envuelta.__hookMulti = true;
    window.deleteSub = envuelta;
  }
  instalarHookDelete();
})();

// ─────────────────────────────────────────────────────────────────────────────
// Funcion de agregacion automatica de : para formatos de cantidad de horas
// ─────────────────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".campo-duracion").forEach((input) => {
    input.addEventListener("input", function (e) {
      let valor = this.value.replace(/[^0-9]/g, ""); // solo números
      // Si ya hay más de 2 dígitos, insertar :
      if (valor.length > 2) {
        valor = valor.slice(0, 2) + ":" + valor.slice(2, 4);
      }
      this.value = valor;
    });
    input.addEventListener("blur", function () {
      // Validación opcional al salir del campo
      if (!/^\d{2}:\d{2}$/.test(this.value)) {
        console.log("Formato inválido, debe ser hh:mm");
      }
    });
  });
});

// ─────────────────────────────────────────────────────────────────────────────
// Exports al scope global
// ─────────────────────────────────────────────────────────────────────────────
window.iniciarModoMultiple = iniciarModoMultiple;
window.toggleModoMultiple = toggleModoMultiple;
window.onCambioNoempMulti = onCambioNoempMulti;
window.marcarDiasExistentes = marcarDiasExistentes;
window.toggleCardDia = toggleCardDia;
window.toggleColapso = toggleColapso;
window.actualizarResumenDia = actualizarResumenDia;
window.agregarSubBloque = agregarSubBloque;
window.aplicarRestriccionSegundoRegistro = aplicarRestriccionSegundoRegistro;
window.aplicarRestriccionIbmEspecial = aplicarRestriccionIbmEspecial;
window.eliminarSubBloque = eliminarSubBloque;
window.eliminarSubBloqueActual = eliminarSubBloqueActual;
window.navegarSubBloque = navegarSubBloque;
window.mostrarSubBloque = mostrarSubBloque;
window.onCambioMotivoDia = onCambioMotivoDia;
window.onCambioTurnoDia = onCambioTurnoDia;
window.onCambioDuracionDia = onCambioDuracionDia;
window.onEspecialDia = onEspecialDia;
window.recalcularHorasDia = recalcularHorasDia;
window.copiarConfiguracion = copiarConfiguracion;
window.limpiarCardDia = limpiarCardDia;
window.seleccionarTodosDias = seleccionarTodosDias;
window.guardarTodosDias = guardarTodosDias;
window.lanzarTutorialMulti = lanzarTutorialMulti;
window.precargarDatosExistentes = precargarDatosExistentes;
