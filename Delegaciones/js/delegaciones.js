// delegaciones.js
const API = "php/delegaciones_api.php";
const $ = (id) => document.getElementById(id);
const modal = new bootstrap.Modal($("modalConfirmar"));
let pendiente = null,
  timerId = null;

// reemplaza init(): ahora refresca ambas listas
function init() {
  const hoy = new Date().toISOString().slice(0, 10);
  $("fechaInicio").min = hoy;
  $("fechaFin").min = hoy;
  $("btnDelegar").addEventListener("click", prepararDelegacion);
  $("btnConfirmar").addEventListener("click", confirmarDelegacion);
  refrescar();
}

async function refrescar() {
  await Promise.all([cargarLista(), cargarRecibidas(), cargarHistorial()]);
  iniciarTimer();
}

async function cargarHistorial() {
  const cont = $("listaHistorial");
  try {
    const res = await postForm({ action: "listarHistorial" });
    const data = res.data || [];
    cont.innerHTML = data.length
      ? data.map(tarjetaHistorial).join("")
      : `<div class="text-muted small">Aún no tienes delegaciones en el historial.</div>`;
  } catch (e) {
    cont.innerHTML = `<div class="text-danger small">No se pudo cargar el historial.</div>`;
  }
}

function tarjetaHistorial(d) {
  const estadoBadge = d.Estado === "Cancelada" ? "bg-danger" : "bg-secondary";
  const esHecha = d.Tipo === "Hecha";
  const dirBadge = esHecha ? "bg-primary" : "bg-info text-dark";
  const etiqueta = esHecha
    ? `A: IBM ${d.Contraparte}`
    : `De: IBM ${d.Contraparte}`;
  return `
  <div class="col-md-6">
    <div class="card h-100 shadow-sm opacity-75 ${esHecha ? "" : "border-start border-4 border-info"}">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold">${etiqueta}</div>
            <div class="small text-muted">${fmt(d.FechaInicio)} → ${fmt(d.FechaFin)}</div>
          </div>
          <div class="text-end">
            <span class="badge ${dirBadge} mb-1">${d.Tipo}</span><br>
            <span class="badge ${estadoBadge}">${d.Estado}</span>
          </div>
        </div>
        ${d.Comentario ? `<div class="small mt-1 fst-italic">${escapar(d.Comentario)}</div>` : ""}
        <div class="text-end mt-2">
          <a class="btn btn-sm btn-outline-danger" target="_blank"
             href="php/generar_pdf.php?id=${d.IdDelegacion}">             
            <i class="fa-solid fa-file-pdf"></i> Ver PDF
          </a>
        </div>
      </div>
    </div>
  </div>`;
}

// reemplaza cargarLista(): ya NO maneja el timer aquí
async function cargarLista() {
  const cont = $("listaDelegaciones");
  try {
    const res = await postForm({ action: "listar" });
    const data = res.data || [];
    cont.innerHTML = data.length
      ? data.map(tarjeta).join("")
      : `<div class="text-muted small">No tienes delegaciones activas ni programadas.</div>`;
    cont
      .querySelectorAll("[data-cancelar]")
      .forEach((b) =>
        b.addEventListener("click", () => cancelar(b.dataset.cancelar)),
      );
  } catch (e) {
    cont.innerHTML = `<div class="text-danger small">No se pudo cargar la lista.</div>`;
  }
}

// NUEVA: delegaciones que otros te dieron a ti (sin botón cancelar)
async function cargarRecibidas() {
  const cont = $("listaRecibidas");
  const cnt = $("cntRecibidas");
  try {
    const res = await postForm({ action: "listarRecibidas" });
    const data = res.data || [];
    if (data.length) {
      cnt.textContent = data.length;
      cnt.classList.remove("d-none");
    } else cnt.classList.add("d-none");
    cont.innerHTML = data.length
      ? data.map(tarjetaRecibida).join("")
      : `<div class="text-muted small">Nadie te ha delegado responsabilidades por ahora.</div>`;
  } catch (e) {
    cont.innerHTML = `<div class="text-danger small">No se pudo cargar la lista.</div>`;
  }
}

// NUEVA: tarjeta para "delegadas a mí"
function tarjetaRecibida(d) {
  return `
  <div class="col-md-6">
    <div class="card h-100 shadow-sm border-start border-4 border-primary">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold">De: IBM ${d.IBMDelegante}</div>
            <div class="small text-muted">${fmt(d.FechaInicio)} → ${fmt(d.FechaFin)}</div>
          </div>
          <span class="badge" data-badge="${d.IdDelegacion}">—</span>
        </div>
        ${d.Comentario ? `<div class="small mt-1 fst-italic">${escapar(d.Comentario)}</div>` : ""}
        <div class="progress mt-2" style="height:6px;">
          <div class="progress-bar" data-bar="${d.IdDelegacion}" style="width:0%"></div>
        </div>
        <div class="small mt-1" data-restante="${d.IdDelegacion}"
             data-ini="${d.FechaInicio}" data-fin="${d.FechaFin}"></div>
        <div class="d-flex justify-content-between align-items-center mt-2">
          <span class="small text-primary">
            <i class="bi bi-check2-square me-1"></i>Estás autorizando en su lugar
          </span>
          <a class="btn btn-sm btn-outline-danger me-1" target="_blank"
             href="php/generar_pdf.php?id=${d.IdDelegacion}">
            <i class="fa-solid fa-file-pdf"></i> Ver PDF
          </a>
        </div>
      </div>
    </div>
  </div>`;
}

function prepararDelegacion() {
  const d = {
    ibmDelegado: $("ibmDelegado").value.trim(),
    fechaInicio: $("fechaInicio").value,
    fechaFin: $("fechaFin").value,
    comentario: $("comentario").value.trim(),
  };
  const err = validar(d);
  if (err) return mostrarMsg(err, "danger");
  pendiente = d;
  $("modalResumen").innerHTML = `
    <p>Vas a delegar tus autorizaciones a:</p>
    <ul class="mb-2">
      <li><strong>IBM:</strong> ${d.ibmDelegado}</li>
      <li><strong>Periodo:</strong> ${fmt(d.fechaInicio)} → ${fmt(d.fechaFin)} (${dias(d.fechaInicio, d.fechaFin)} días)</li>
      ${d.comentario ? `<li><strong>Comentario:</strong> ${escapar(d.comentario)}</li>` : ""}
    </ul>
    <div class="alert alert-warning small mb-0">
      Durante ese periodo, esta persona podrá aprobar o rechazar tus solicitudes en tu lugar.
    </div>`;
  modal.show();
}

function validar(d) {
  if (!d.ibmDelegado) return "Escribe el IBM de la persona a delegar.";
  if (!/^\d{1,20}$/.test(d.ibmDelegado)) return "El IBM debe ser numérico.";
  if (d.ibmDelegado === $("ibmDelegante").value)
    return "No puedes delegarte a ti mismo.";
  if (!d.fechaInicio || !d.fechaFin)
    return "Selecciona el periodo (desde / hasta).";
  if (d.fechaFin < d.fechaInicio)
    return "La fecha fin no puede ser anterior a la de inicio.";
  return null;
}

async function confirmarDelegacion() {
  if (!pendiente) return;
  const btn = $("btnConfirmar");
  btn.disabled = true;
  try {
    const res = await postForm({ action: "guardar", ...pendiente });
    if (res.error) mostrarMsg(textoError(res.error), "danger");
    else {
      modal.hide();
      limpiarForm();
      mostrarMsg("Delegación registrada correctamente.", "success");
      window.open("php/generar_pdf.php?id=" + res.id, "_blank");
      refrescar();
    }
  } catch (e) {
    mostrarMsg("Error de conexión.", "danger");
  } finally {
    btn.disabled = false;
  }
}

function tarjeta(d) {
  return `
  <div class="col-md-6">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold">A: IBM ${d.IBMDelegado}</div>
            <div class="small text-muted">${fmt(d.FechaInicio)} → ${fmt(d.FechaFin)}</div>
          </div>
          <span class="badge" data-badge="${d.IdDelegacion}">—</span>
        </div>
        ${d.Comentario ? `<div class="small mt-1 fst-italic">${escapar(d.Comentario)}</div>` : ""}
        <div class="progress mt-2" style="height:6px;">
          <div class="progress-bar" data-bar="${d.IdDelegacion}" style="width:0%"></div>
        </div>
        <div class="small mt-1" data-restante="${d.IdDelegacion}"
             data-ini="${d.FechaInicio}" data-fin="${d.FechaFin}"></div>
        <div class="text-end mt-2">
          <a class="btn btn-sm btn-outline-danger me-1" target="_blank"
             href="php/generar_pdf.php?id=${d.IdDelegacion}">
            <i class="fa-solid fa-file-pdf"></i> Ver PDF
          </a>
          <button class="btn btn-sm btn-outline-warning" data-cancelar="${d.IdDelegacion}">
            <i class="fa-solid fa-ban"></i> Cancelar
          </button>
        </div>
        </div>
      </div>
    </div>
  </div>`;
}

async function cancelar(id) {
  const result = await Swal.fire({
    title: "¿Cancelar esta delegación?",
    text: "Las autorizaciones volverán a ti de inmediato.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, cancelar",
    cancelButtonText: "No, mantener",
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    customClass: {
      actions: "d-flex gap-4 justify-content-center",
    },
  });

  if (!result.isConfirmed) return;

  const res = await postForm({ action: "cancelar", id });
  if (res.error) {
    mostrarMsg(textoError(res.error), "danger");
  } else {
    Swal.fire({
      title: "Delegación cancelada",
      icon: "success",
      timer: 2000,
      showConfirmButton: false,
    });
    cargarLista();
  }
}

// --- Tiempo restante en vivo ---
function iniciarTimer() {
  detenerTimer();
  actualizarTiempos();
  timerId = setInterval(actualizarTiempos, 1000);
}
function detenerTimer() {
  if (timerId) {
    clearInterval(timerId);
    timerId = null;
  }
}

function actualizarTiempos() {
  const ahora = Date.now();
  document.querySelectorAll("[data-restante]").forEach((el) => {
    const id = el.dataset.restante;
    const ini = new Date(el.dataset.ini + "T00:00:00").getTime();
    const fin = new Date(el.dataset.fin + "T23:59:59").getTime();
    const badge = document.querySelector(`[data-badge="${id}"]`);
    const bar = document.querySelector(`[data-bar="${id}"]`);
    if (ahora < ini) {
      el.textContent = `Inicia en ${humano(ini - ahora)}`;
      // Programada
      setBadge(badge, "Programada", "bg-secondary");
      bar.style.width = "0%";
      bar.className = "progress-bar bg-secondary";
    } else if (ahora <= fin) {
      const pct = Math.min(
        100,
        Math.max(0, ((ahora - ini) / (fin - ini)) * 100),
      );
      const restante = fin - ahora;
      const porVencer = restante <= 86400000;
      bar.style.width = pct.toFixed(1) + "%";
      bar.className =
        "progress-bar " + (porVencer ? "bg-warning" : "bg-success");
      el.textContent = `Tiempo restante: ${humano(restante)}`;
      setBadge(
        badge,
        porVencer ? "Por vencer" : "Activa",
        porVencer ? "bg-warning" : "bg-success",
      );
    } else {
      el.textContent = "Vencida";
      setBadge(badge, "Vencida", "bg-secondary");
      bar.style.width = "100%";
      bar.className = "progress-bar bg-secondary";
    }
  });
}

function setBadge(el, txt, cls) {
  if (el) {
    el.textContent = txt;
    el.className = "badge " + cls;
  }
}
function humano(ms) {
  const s = Math.floor(ms / 1000),
    d = Math.floor(s / 86400),
    h = Math.floor((s % 86400) / 3600),
    m = Math.floor((s % 3600) / 60),
    seg = s % 60;
  if (d > 0) return `${d}d ${h}h ${m}m`;
  if (h > 0) return `${h}h ${m}m ${seg}s`;
  return `${m}m ${seg}s`;
}

async function postForm(obj) {
  const r = await fetch(API, {
    method: "POST",
    body: new URLSearchParams(obj),
  });
  return r.json();
}
function mostrarMsg(msg, tipo) {
  $("formMsg").innerHTML =
    `<div class="alert alert-${tipo} py-2 mb-0">${msg}</div>`;
  if (tipo === "success")
    setTimeout(() => {
      $("formMsg").innerHTML = "";
    }, 4000);
}
function textoError(e) {
  return typeof e === "string" ? e : "No se pudo completar la operación.";
}
function limpiarForm() {
  ["ibmDelegado", "fechaInicio", "fechaFin", "comentario"].forEach(
    (k) => ($(k).value = ""),
  );
}
function fmt(iso) {
  const [y, m, d] = iso.split("-");
  return `${d}/${m}/${y}`;
}
function dias(a, b) {
  return Math.round((new Date(b) - new Date(a)) / 86400000) + 1;
}
function escapar(s) {
  return s.replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        c
      ],
  );
}

init();
