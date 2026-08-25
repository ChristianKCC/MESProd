/* ============================================================================
   REPORTE RARR - SEGURIDAD DE MAQUINARIA (vista 1)
     1) Departamento -> resumen + clasificación + MÁQUINAS
     2) Click en máquina -> SECCIONES (Seg_SeccionMaquina)
     3) Click en sección -> panel del RARR de ese IdEquipo
   ============================================================================ */
import { API, llamarGET } from "../../Endpoints/endpoints.js";

let graficaAvance = null;

document.addEventListener("DOMContentLoaded", async () => {
  inicializarGrafica();
  await cargarDepartamentos();
  document
    .getElementById("slcDepartamento")
    .addEventListener("change", onCambioDepartamento);
  document
    .getElementById("chkSoloPendientes")
    .addEventListener("change", pintarSecciones);
});

/* -------------------- Departamentos -------------------- */
async function cargarDepartamentos() {
  try {
    const res = await llamarGET(API.getDepartamentos);
    const slc = document.getElementById("slcDepartamento");
    res.data.forEach((d) => {
      const opt = document.createElement("option");
      opt.value = d.id;
      opt.textContent = d.nombre;
      slc.appendChild(opt);
    });
  } catch (e) {
    mostrarError(e.message);
  }
}

/* -------------------- Cambio de departamento -------------------- */
async function onCambioDepartamento() {
  const idDepartamento = this.value;
  if (idDepartamento === "") {
    limpiarVista();
    return;
  }
  try {
    const [resumen, clasificacion, maquinas] = await Promise.all([
      llamarGET(API.getResumen, { idDepartamento }),
      llamarGET(API.getClasificacion, { idDepartamento }),
      llamarGET(API.getTablaMaquinas, { idDepartamento }),
    ]);
    pintarResumen(resumen.data);
    pintarClasificacion(clasificacion.data);
    pintarListaMaquinas(maquinas.data);
  } catch (e) {
    mostrarError(e.message);
  }
}

/* -------------------- Cards resumen + doughnut -------------------- */
function pintarResumen(d) {
  document.getElementById("statConcluidos").textContent = d.rarr.concluidos;
  document.getElementById("statPendientes").textContent = d.rarr.pendientes;
  document.getElementById("statTotal").textContent = d.rarr.total;
  document.getElementById("statPersonal").textContent = d.personal.total;
  document.getElementById("statCapacitados").textContent =
    d.personal.capacitados;
  actualizarGrafica(d.rarr.concluidos, d.rarr.pendientes);
}

/* -------------------- Calificación + KPI -------------------- */
function pintarClasificacion(d) {
  document.getElementById("calAceptable").textContent = d.aceptable;
  document.getElementById("calBajo").textContent = d.bajo;
  document.getElementById("calAlto").textContent = d.alto;
  document.getElementById("calInaceptable").textContent = d.inaceptable;

  const ind = d.indicadores || {};
  const pct = ind.pctAreasResidual ?? 0;
  document.getElementById("kpiPorcentaje").textContent =
    Number(pct).toFixed(2) + "%";
  document.getElementById("kpiPromedio").textContent =
    ind.promedioMarcador !== null && ind.promedioMarcador !== undefined
      ? Math.round(ind.promedioMarcador)
      : "-";
  document.getElementById("kpiRiesgoTotal").textContent =
    ind.riesgoTotal ?? new Date().getFullYear();
}

/* -------------------- Gráfica doughnut -------------------- */
function inicializarGrafica() {
  const ctx = document.getElementById("graficaAvance").getContext("2d");
  graficaAvance = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: ["Realizados", "Pendientes"],
      datasets: [
        {
          data: [0, 1],
          backgroundColor: ["#2563eb", "#c9ccd2"],
          borderWidth: 0,
        },
      ],
    },
    options: {
      cutout: "70%",
      plugins: { legend: { display: false }, tooltip: { enabled: true } },
      responsive: true,
    },
  });
}

function actualizarGrafica(concluidos, pendientes) {
  const total = concluidos + pendientes;
  const pctC = total > 0 ? (concluidos / total) * 100 : 0;
  const pctP = total > 0 ? 100 - pctC : 0;
  graficaAvance.data.datasets[0].data =
    total > 0 ? [concluidos, pendientes] : [0, 1];
  graficaAvance.update();
  document.getElementById("avancePorcentaje").textContent =
    pctC.toFixed(2) + "%";
  document.getElementById("avanceDetalle").innerHTML =
    `${concluidos} / ${total}<br>actividades<br>completadas`;
  document.getElementById("leyendaRealizados").textContent =
    `${pctC.toFixed(2)}%  (${concluidos})`;
  document.getElementById("leyendaPendientes").textContent =
    `${pctP.toFixed(2)}%  (${pendientes})`;
  document.getElementById("leyendaTotal").textContent =
    `Total: ${total} actividades`;
}

/* -------------------- 1) Lista de MÁQUINAS -------------------- */
function pintarListaMaquinas(maquinas) {
  const cont = document.getElementById("listaMaquinas");
  cont.innerHTML = "";

  if (!maquinas || maquinas.length === 0) {
    cont.innerHTML = `<div class="vacio">No hay máquinas para este departamento</div>`;
    limpiarLista("listaSecciones", "Selecciona una máquina");
    limpiarPanelRARR();
    return;
  }

  maquinas.forEach((m) => {
    const div = document.createElement("div");
    div.className = "item";
    div.textContent = m.Maquina;
    div.addEventListener("click", () => {
      cont
        .querySelectorAll(".item")
        .forEach((i) => i.classList.remove("activo"));
      div.classList.add("activo");
      cargarSecciones(m.IdMaquina);
    });
    cont.appendChild(div);
  });

  limpiarLista("listaSecciones", "Selecciona una máquina");
  limpiarPanelRARR();
}

/* -------------------- 2) Lista de SECCIONES -------------------- */
// async function cargarSecciones(idMaquina) {
//   const cont = document.getElementById("listaSecciones");
//   cont.innerHTML = `<div class="vacio">Cargando…</div>`;
//   limpiarPanelRARR();

//   try {
//     const res = await llamarGET(API.getSeccionesRARR, { idMaquina });
//     cont.innerHTML = "";
//     if (res.data.length === 0) {
//       cont.innerHTML = `<div class="vacio">Esta máquina no tiene secciones dadas de alta</div>`;
//       return;
//     }
//     res.data.forEach((s) => {
//       const div = document.createElement("div");
//       div.className = "item";
//       div.innerHTML = `${s.Seccion}<br><small class="text-muted">${s.IdEquipo}</small>`;
//       div.addEventListener("click", () => {
//         cont
//           .querySelectorAll(".item")
//           .forEach((i) => i.classList.remove("activo"));
//         div.classList.add("activo");
//         cargarRARR(s.IdEquipo);
//       });
//       cont.appendChild(div);
//     });
//   } catch (e) {
//     mostrarError(e.message);
//   }
// }

let seccionesActuales = [];

async function cargarSecciones(idMaquina) {
  const cont = document.getElementById("listaSecciones");
  cont.innerHTML = `<div class="vacio">Cargando…</div>`;
  limpiarPanelRARR();
  try {
    const res = await llamarGET(API.getSeccionesRARR, { idMaquina });
    seccionesActuales = res.data || [];
    pintarSecciones();
  } catch (e) {
    mostrarError(e.message);
  }
}

function pintarSecciones() {
  const cont = document.getElementById("listaSecciones");
  const solo = document.getElementById("chkSoloPendientes").checked;

  const lista = solo
    ? seccionesActuales.filter(
        (s) =>
          Number(s.TieneRARR) === 0 ||
          s.EstatusRARR !== "Concluido" ||
          Number(s.AccionesPendientes) > 0,
      )
    : seccionesActuales;

  cont.innerHTML = "";
  if (lista.length === 0) {
    cont.innerHTML = `<div class="vacio">${
      solo
        ? "No hay secciones pendientes en esta máquina"
        : "Esta máquina no tiene secciones dadas de alta"
    }</div>`;
    return;
  }

  lista.forEach((s) => {
    let etiqueta;
    if (Number(s.TieneRARR) === 0) {
      etiqueta = `<span class="badge bg-danger">Sin RARR</span>`;
    } else if (s.EstatusRARR === "Concluido") {
      etiqueta = `<span class="badge bg-success">Concluido</span>`;
    } else {
      etiqueta = `<span class="badge bg-warning text-dark">En proceso</span>`;
    }
    const acc =
      Number(s.AccionesPendientes) > 0
        ? `<span class="badge bg-secondary ms-1" title="Acciones por concluir">${s.AccionesPendientes} pend.</span>`
        : "";

    const div = document.createElement("div");
    div.className = "item";
    div.innerHTML = `${s.Seccion}<br>
      <small class="text-muted">${s.IdEquipo}</small><br>${etiqueta}${acc}`;
    div.addEventListener("click", () => {
      cont
        .querySelectorAll(".item")
        .forEach((i) => i.classList.remove("activo"));
      div.classList.add("activo");
      cargarRARR(s.IdEquipo);
    });
    cont.appendChild(div);
  });
}

/* -------------------- 3) Panel del RARR (por IdEquipo) -------------------- */
async function cargarRARR(idEquipo) {
  const cont = document.getElementById("panelRARR");
  cont.innerHTML = `<div class="vacio">Cargando…</div>`;

  try {
    const res = await llamarGET(API.getRARRxSeccion, { idEquipo });
    if (res.data.length === 0) {
      cont.innerHTML = `<div class="vacio">Sin RARR registrado para este equipo</div>`;
      return;
    }

    const r = res.data[0];
    const estatusBadge =
      r.EstatusRARR === "Concluido"
        ? `<span class="badge-estatus est-completado">Concluido</span>`
        : `<span class="badge-estatus est-enproceso">${r.EstatusRARR}</span>`;

    cont.innerHTML = `
      <div class="rarr-encabezado">
        <b>${r.IdEquipo}</b><br>
        <span class="text-muted">${r.Maquina} — ${r.SeccionEquipo}</span><br>
        ${estatusBadge}
        <span class="text-muted ms-1">${r.Escenarios} escenario(s)</span>


        <div class="rarr-pendientes mp-3 mb-3 mt-3" id="rarrPendientes">
          <div class="etiqueta mb-3">Acciones correctivas por concluir</div>
          <div class="d-flex gap-2">
            <div class="card-pendiente flex-fill text-center p-2" id="cardPend2"
                  style="border:1px solid #e2e5ea;border-radius:8px;cursor:pointer">
              <div class="fs-4 fw-bold" id="pend2Num" style="color:#f0930d">–</div>
              <small class="text-muted">Plan de Contención</small>
            </div>
            <div class="card-pendiente flex-fill text-center p-2" id="cardPend3"
                  style="border:1px solid #e2e5ea;border-radius:8px;cursor:pointer">
              <div class="fs-4 fw-bold" id="pend3Num" style="color:#dc3545">–</div>
              <small class="text-muted">Plan de Acción</small>
            </div>
          </div>
        </div>

      </div>

      <div class="rarr-datos p-3 mt-2" style="border-radius:6px">
        <div class="rarr-fecha mb-3">
          <i class="fa-regular fa-calendar me-1"></i>
          Fecha Última Actualización: <b>${r.fechaActualizacion}</b>
        </div>
        <div class="rarr-marcadores mb-3">
          ${marcadorCard("Peligro Puro", r.marcadorPuro)}
          ${marcadorCard("Reducción con Guardas Actuales", r.marcadorGuardas)}
          ${marcadorCard("Potencial de Reducción con Ingeniería", r.marcadorIngenieria)}
        </div>
        <div class="rarr-avance mb-3">
          <div class="etiqueta">Implementación de Controles Administrativos</div>
          <div class="avance-linea">
            <div class="progress progress-rarr flex-grow-1">
              <div class="progress-bar" style="width:${r.avance}%"></div>
            </div>
            <span class="pct">${r.avance}%</span>
          </div>
        </div>
        <div class="rarr-inversion">
          <div class="etiqueta">Inversión Estimada</div>
          <div class="fs-6 fw-bold">
            <i class="fa-solid fa-dollar-sign text-primary me-1"></i>
            $ ${Number(r.inversion).toLocaleString("es-MX", { minimumFractionDigits: 2 })}
          </div>
        </div>
      </div>

      <div class="text-center mt-3 mb-4 d-flex gap-2 justify-content-center">
        <button type="button" class="btn p-3 rounded-4 btn-success" id="btnExportarRARR" style="color:#fff;font-weight:700">
            <i class="fa-solid fa-file-excel me-1"></i>Exportar a Excel
        </button>
        <button type="button" class="btn p-3 rounded-4 btn-danger" id="btnExportarPDF" style="color:#fff;font-weight:700">
            <i class="fa-solid fa-file-pdf me-1"></i>Exportar a PDF
        </button>
      </div>`;

    document.getElementById("btnExportarPDF").addEventListener("click", () => {
      window.location = `${API.exportarPDF}?idEquipo=${encodeURIComponent(r.IdEquipo)}`;
    });

    // Pendientes (tarjetas + modal)
    cargarPendientes(r.IdEquipo);
    document
      .getElementById("cardPend2")
      .addEventListener("click", () => abrirModalPendientes("paso2"));
    document
      .getElementById("cardPend3")
      .addEventListener("click", () => abrirModalPendientes("paso3"));
  } catch (e) {
    mostrarError(e.message);
  }
}

function marcadorCard(titulo, valor) {
  if (valor === null || valor === undefined) {
    return `<div class="marcador-card nivel-nulo">
              <div class="valor">—</div><div class="titulo">${titulo}</div>
            </div>`;
  }
  const v = nivelMarcador(valor);
  return `<div class="marcador-card" style="border-color:${v.color}">
            <div class="valor" style="color:${v.color}">${valor}</div>
            <div class="titulo">${titulo}</div>
            <div class="nivel-txt" style="color:${v.color}">${v.texto}</div>
          </div>`;
}

function nivelMarcador(v) {
  if (v > 500) return { texto: "INACEPTABLE", color: "#c00000" };
  if (v > 50) return { texto: "ALTO", color: "#ff6600" };
  if (v > 5) return { texto: "BAJO", color: "#e8a800" };
  return { texto: "ACEPTABLE", color: "#1d7044" };
}

/* -------------------- Utilerías -------------------- */
function limpiarLista(id, mensaje) {
  document.getElementById(id).innerHTML = `<div class="vacio">${mensaje}</div>`;
}

function limpiarPanelRARR() {
  document.getElementById("panelRARR").innerHTML =
    `<div class="vacio">Selecciona una sección / equipo</div>`;
}

function limpiarVista() {
  [
    "statConcluidos",
    "statPendientes",
    "statTotal",
    "statPersonal",
    "statCapacitados",
    "calAceptable",
    "calBajo",
    "calAlto",
    "calInaceptable",
  ].forEach((id) => (document.getElementById(id).textContent = "0"));
  document.getElementById("kpiPorcentaje").textContent = "0%";
  document.getElementById("kpiPromedio").textContent = "-";
  // document.getElementById("kpiRiesgoTotal").textContent = "-";
  document.getElementById("kpiRiesgoTotal").textContent =
    new Date().getFullYear();
  actualizarGrafica(0, 0);
  limpiarLista("listaMaquinas", "Selecciona un departamento");
  limpiarLista("listaSecciones", "Selecciona una máquina");
  limpiarPanelRARR();
}

function mostrarError(mensaje) {
  Swal.fire({
    icon: "error",
    title: "Ocurrió un problema",
    text: mensaje,
    confirmButtonColor: "#1a56db",
  });
}

/* -------------------- Pendientes del RARR -------------------- */
let pendientesActual = null;

async function cargarPendientes(idEquipo) {
  try {
    const res = await llamarGET(API.getPendientesRARR, { idEquipo });
    pendientesActual = res.data;
    const r = res.data.resumen;
    document.getElementById("pend2Num").textContent =
      `${r.p2Pendientes} / ${r.p2Total}`;
    document.getElementById("pend3Num").textContent =
      `${r.p3Pendientes} / ${r.p3Total}`;
  } catch (e) {}
}

function abrirModalPendientes(paso) {
  if (!pendientesActual) return;
  const esP2 = paso === "paso2";
  const lista = esP2 ? pendientesActual.paso2 : pendientesActual.paso3;
  const titulo = esP2 ? "Plan de Contención" : "Plan de Acción";

  const pendientes = lista.filter((x) => Number(x.Concluido) === 0);
  const concluidos = lista.filter((x) => Number(x.Concluido) === 1);

  const filaP2 = (x, ok) => `
    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
      <div>
        <div class="fw-bold">${x.EscenarioRiesgo}</div>
        <small class="text-muted">${x.AccionesPropuestas ?? "-"} · Responsable: ${x.Responsable}</small>
        
      </div>
      <div class="text-end" style="min-width:120px">
        <div class="progress" style="height:8px">
          <div class="progress-bar ${ok ? "bg-success" : "bg-warning"}" style="width:${x.Avance}%"></div>
        </div>
        <small class="${ok ? "text-success" : "text-warning"}">${x.Avance}%</small>
      </div>
    </div>`;

  const filaP3 = (x, ok) => `
    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
      <div>
        <div class="fw-bold">${x.EscenarioRiesgo}</div>
        <small class="text-muted">${x.Accion ?? "-"} · ${x.Responsable}</small>
      </div>
      <span class="badge ${ok ? "bg-success" : "bg-secondary"}">${x.Estatus}</span>
    </div>`;

  const pinta = (arr, ok) =>
    arr.length === 0
      ? `<div class="text-muted small py-2">Ninguno</div>`
      : arr.map((x) => (esP2 ? filaP2(x, ok) : filaP3(x, ok))).join("");

  const html = `
    <div class="text-start">
      <div class="fw-bold text-warning mb-1"><i class="fa-solid fa-hourglass-half me-1"></i>Pendientes (${pendientes.length})</div>
      ${pinta(pendientes, false)}
      <div class="fw-bold text-success mt-3 mb-1"><i class="fa-solid fa-circle-check me-1"></i>Concluidos (${concluidos.length})</div>
      ${pinta(concluidos, true)}
    </div>`;

  Swal.fire({
    title: titulo,
    html: html,
    width: 640,
    confirmButtonText: "Cerrar",
    confirmButtonColor: "#1a56db",
  });
}
