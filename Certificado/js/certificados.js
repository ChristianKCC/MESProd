// js/certificados.js — Certificados de Calidad (FORM-63297)
const CF_API = "php/certificados_api.php";
const CF_PDF = "php/certificado_pdf.php";
const CF_FIRMA_URL = (noemp) => `../../KCMes/FirmaDigital/firmas/${noemp}.png`;
const CF_LOGO_URL = "../../KCMes/img/imglogoprosede.png"; // logo para el PDF

let CF_PERFIL = { roles: [], nombre: "" };
let CF_ESPACIO = []; // cache para el buscador

const cfQ = (s) => document.querySelector(s);
const cfEsc = (s) =>
  String(s ?? "").replace(
    /[&<>"]/g,
    (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" })[c],
  );

async function cfApi(payload) {
  const r = await fetch(CF_API, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });
  return r.json();
}

async function cfRefrescarTodo() {
  await Promise.all([cfCargarEspacio(), cfCargarCerts(), cfCargarFolios()]);
}

// SweetAlert dentro de un modal Bootstrap: sin esto el input no recibe foco
const cfSwal = (opts) =>
  Swal.fire({ heightAuto: false, allowOutsideClick: false, ...opts });

const CF_ETAPA_TXT = {
  inspeccion: "Inspección",
  fisicoquimico: "Lab. Fisicoquímico",
  microbiologia: "Lab. Microbiología",
};
const CF_ETAPA_ICO = {
  inspeccion: "fa-magnifying-glass",
  fisicoquimico: "fa-flask",
  microbiologia: "fa-bacterium",
};
const CF_EST_TXT = {
  0: "Captura pendiente",
  1: "En autorización",
  2: "Autorizada",
  3: "Rechazada",
};
const CF_EST_CLS = {
  0: "cf-captura",
  1: "cf-enviado",
  2: "cf-autorizado",
  3: "cf-rechazado",
};

// ================= MI ESPACIO (tarjetas desplegables) =================
// async function cfCargarEspacio() {
//   const cont = cfQ("#listaEspacio");
//   const r = await cfApi({ accion: "espacio" });
//   if (!r.ok) {
//     cont.innerHTML = cfVacio(r.error);
//     return;
//   }
//   CF_ESPACIO = r.items || [];
//   cfPintarEspacio(CF_ESPACIO);
// }

async function cfCargarEspacio() {
  const cont = cfQ("#listaEspacio");
  const r = await cfApi({ accion: "espacio" });
  if (!r.ok) {
    cont.innerHTML = cfVacio(r.error);
    return;
  }
  CF_ESPACIO = r.items || [];
  cfPintarEspacio(CF_ESPACIO, r.roles || [], r.ibm);
}

function cfVacio(txt) {
  return `<div class="cf-vacio"><i class="fa-regular fa-folder-open"></i>${cfEsc(txt)}</div>`;
}

// ================= INICIAR CERTIFICADO =================

function cfPintarEspacio(items, roles = [], ibm = "") {
  const cont = cfQ("#listaEspacio");

  // Aviso claro cuando el IBM no está dado de alta en tblMXPRCertificadoPerfilFR
  let aviso = "";
  if (!roles.length) {
    aviso = `<div class="alert alert-warning py-2 mb-3">
      <i class="fa-solid fa-triangle-exclamation me-1"></i>
      Tu IBM <b>${cfEsc(ibm)}</b> no tiene ningún rol asignado en el módulo.
      Puedes consultar, pero no capturar ni autorizar hasta que se registre en
      <code>tblMXPRCertificadoPerfilFR</code>.
    </div>`;
  }

  if (!items.length) {
    cont.innerHTML = aviso + cfVacio("No hay certificados en proceso.");
    return;
  }

  cont.innerHTML =
    aviso +
    items
      .map((it) => {
        const mini = ["inspeccion", "fisicoquimico", "microbiologia"]
          .map((e) => {
            const et = it.etapas[e];
            const cls = et.bloqueado ? "cf-m-x" : "cf-m-" + et.estatus;
            const ini = {
              inspeccion: "INS",
              fisicoquimico: "FQ",
              microbiologia: "MB",
            }[e];
            return `<span class="${cls}" title="${CF_ETAPA_TXT[e]}: ${et.bloqueado ? "Bloqueada" : CF_EST_TXT[et.estatus]}">${ini}</span>`;
          })
          .join("");

        const pasos = ["inspeccion", "fisicoquimico", "microbiologia"]
          .map((e) => {
            const et = it.etapas[e];
            if (!et.visible) {
              return `<div class="cf-paso bloq">
            <div class="cf-paso-nom"><i class="fa-solid ${CF_ETAPA_ICO[e]}"></i> ${CF_ETAPA_TXT[e]}</div>
            <span class="cf-badge cf-bloqueado"><i class="fa-solid fa-lock"></i> Sin visibilidad</span>
          </div>`;
            }
            let badge,
              info = "",
              btn = "";
            if (et.bloqueado) {
              badge = `<span class="cf-badge cf-bloqueado"><i class="fa-solid fa-lock"></i> Esperando etapas previas</span>`;
              info = "Requiere Inspección y Fisicoquímico autorizados.";
            } else {
              badge = `<span class="cf-badge ${CF_EST_CLS[et.estatus]}">${CF_EST_TXT[et.estatus]}</span>`;
              if (et.estatus === 3 && et.motivoRechazo)
                info = `Motivo: ${cfEsc(et.motivoRechazo)}`;
              else if (et.estatus === 1 && et.capturo)
                info = `Capturó ${cfEsc(et.capturo)} · ${cfEsc(et.fechaCaptura || "")}`;
              else if (et.estatus === 2 && et.autorizo)
                info = `Autorizó ${cfEsc(et.autorizo)}`;
            }
            if (et.puedeCapturar && !et.bloqueado) {
              const txt = et.estatus === 3 ? "Corregir" : "Capturar";
              const color = et.estatus === 3 ? "btn-warning" : "btn-primary";
              btn = `<button class="btn btn-sm ${color}" onclick="cfAbrirEtapa(${it.id}, '${e}')">${txt}</button>`;
            } else if (et.puedeAutorizar) {
              btn = `<button class="btn btn-sm btn-success" onclick="cfAbrirEtapa(${it.id}, '${e}', true)">Revisar</button>`;
            } else {
              btn = `<button class="btn btn-sm btn-outline-secondary" onclick="cfAbrirEtapa(${it.id}, '${e}')">Ver</button>`;
            }
            return `<div class="cf-paso ${et.bloqueado ? "bloq" : ""}">
          <div class="cf-paso-nom"><i class="fa-solid ${CF_ETAPA_ICO[e]}"></i> ${CF_ETAPA_TXT[e]}</div>
          ${badge}
          ${info ? `<div class="cf-paso-info">${info}</div>` : ""}
          <div>${btn}</div>
        </div>`;
          })
          .join("");

        let accionCert = "";
        if (it.accionCert === "enviar_gt") {
          accionCert = `<div class="cf-acciones-cert">
          <span class="cf-badge cf-autorizado me-2">3 etapas autorizadas</span>
          <button class="btn btn-sm btn-dark" onclick="cfEnviarGT(${it.id})">Enviar certificado a Gerencia Técnica</button>
        </div>`;
        } else if (it.accionCert === "validacion") {
          accionCert = `<div class="cf-acciones-cert">
          <span class="cf-badge cf-gt me-2">Validación final</span>
          <button class="btn btn-sm btn-success" onclick="cfPreviewCert(${it.id}, true)">Revisar y firmar</button>
        </div>`;
        }

        // Marca discreta cuando el certificado no requiere nada de este usuario
        const sinAccion = it.tieneAccion
          ? ""
          : `<span class="cf-badge cf-bloqueado ms-auto me-2">Sin pendientes tuyos</span>`;

        return `<div class="cf-item" data-buscar="${cfEsc((it.folio + " " + it.clave + " " + it.producto).toLowerCase())}">
        <div class="cf-item-head" onclick="this.parentElement.classList.toggle('abierto')">
          <div class="cf-mini">${mini}</div>
          <div class="cf-item-folio">${cfEsc(it.folio)}</div>
          <div class="cf-item-clave">${cfEsc(it.clave)}</div>
          <div class="cf-item-prod">${cfEsc(it.producto)}</div>
          ${sinAccion}
          <i class="fa-solid fa-chevron-down cf-chevron"></i>
        </div>
        <div class="cf-item-body">
          <div class="cf-pasos">${pasos}</div>
          ${accionCert}
        </div>
      </div>`;
      })
      .join("");
}

async function cfCargarFolios() {
  const tb = cfQ("#tblFolios");
  const r = await cfApi({ accion: "folios" });
  if (!r.ok) {
    tb.innerHTML = `<tr><td colspan="6">${cfVacio(r.error)}</td></tr>`;
    return;
  }
  if (!r.items.length) {
    tb.innerHTML = `<tr><td colspan="6">${cfVacio("Todos los folios ya tienen certificado.")}</td></tr>`;
    return;
  }
  tb.innerHTML = r.items
    .map(
      (f) => `<tr>
      <td>${cfEsc(f.fecha)}</td><td>${cfEsc(f.maquina)}</td>
      <td><strong>${cfEsc(f.folio)}</strong></td><td><code>${cfEsc(f.clave)}</code></td>
      <td>${cfEsc(f.producto)}</td>
      <td><button class="btn btn-sm btn-primary" onclick="cfIniciar('${cfEsc(f.folio)}')">Iniciar</button></td>
    </tr>`,
    )
    .join("");
}

async function cfIniciar(folio) {
  const r = await cfApi({ accion: "iniciar", folio });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });
  cfSwal({
    title: "Listo",
    text: "Certificado iniciado. Las etapas ya pueden capturar.",
    icon: "success",
  });
  // cfCargarFolios();
  // cfCargarEspacio();
  cfRefrescarTodo();
}

// ================= FORMULARIOS POR ETAPA =================
const CF_CAMPOS = {
  inspeccion: (e, ro) => `
    <div class="row g-3 ${ro ? "cf-solo-lectura" : ""}">
      <div class="col-md-6"><label class="form-label">Fecha de fabricación</label>
        <input type="date" class="form-control" id="cf_fechaFabricacion" value="${cfEsc(cfFecha(e?.INS_fechaFabricacion))}"></div>
      <div class="col-md-6"><label class="form-label">Fecha de caducidad (si aplica)</label>
        <input type="date" class="form-control" id="cf_fechaCaducidad" value="${cfEsc(cfFecha(e?.INS_fechaCaducidad))}"></div>
      <div class="col-md-4"><label class="form-label">Seguridad (AQL &lt;0.025)</label>${cfSel("cf_seguridad", e?.INS_seguridad)}</div>
      <div class="col-md-4"><label class="form-label">Desempeño (AQL &lt;2.5)</label>${cfSel("cf_desempeno", e?.INS_desempeno)}</div>
      <div class="col-md-4"><label class="form-label">Apariencia (AQL &lt;4.0)</label>${cfSel("cf_apariencia", e?.INS_apariencia)}</div>
      <div class="col-12"><label class="form-label">Observaciones</label>
        <textarea class="form-control" id="cf_observaciones" rows="2">${cfEsc(e?.INS_observaciones || "")}</textarea></div>
    </div>`,
  fisicoquimico: (e, ro) => `
    <div class="row g-3 ${ro ? "cf-solo-lectura" : ""}">
      <div class="col-md-4"><label class="form-label">Viscosidad (cps) — TTM-00557</label>
        <input type="number" step="0.01" class="form-control" id="cf_viscosidad" value="${cfEsc(e?.FIS_viscosidad ?? "")}"></div>
      <div class="col-md-4"><label class="form-label">pH — TTM-00558</label>
        <input type="number" step="0.01" class="form-control" id="cf_ph" value="${cfEsc(e?.FIS_ph ?? "")}"></div>
      <div class="col-md-4"><label class="form-label">Densidad (g/mL) — TTM-00559</label>
        <input type="number" step="0.0001" class="form-control" id="cf_densidad" value="${cfEsc(e?.FIS_densidad ?? "")}"></div>
      <div class="col-md-6"><label class="form-label">Aspecto / Color</label>${cfSel("cf_aspectoColor", e?.FIS_aspectoColor)}</div>
      <div class="col-md-6"><label class="form-label">Olor</label>${cfSel("cf_olor", e?.FIS_olor)}</div>
      <div class="col-12"><label class="form-label">Observaciones</label>
        <textarea class="form-control" id="cf_observaciones" rows="2">${cfEsc(e?.FIS_observaciones || "")}</textarea></div>
    </div>`,
  microbiologia: (e, ro) => `
    <div class="row g-3 ${ro ? "cf-solo-lectura" : ""}">
      <div class="col-md-4"><label class="form-label">TAMC (UFC/g, ≤100)</label>
        <input type="number" class="form-control" id="cf_tamc" value="${cfEsc(e?.MIC_tamc ?? "")}"></div>
      <div class="col-md-4"><label class="form-label">TYMC (UFC/g, ≤10)</label>
        <input type="number" class="form-control" id="cf_tymc" value="${cfEsc(e?.MIC_tymc ?? "")}"></div>
      <div class="col-md-4"><label class="form-label">Patógenos</label>
        <select class="form-select" id="cf_patogenos">
          <option value="">-- Selecciona --</option>
          <option ${e?.MIC_patogenos === "Ausentes" ? "selected" : ""}>Ausentes</option>
          <option ${e?.MIC_patogenos === "Presentes" ? "selected" : ""}>Presentes</option>
        </select></div>
      <div class="col-12"><label class="form-label">Observaciones</label>
        <textarea class="form-control" id="cf_observaciones" rows="2">${cfEsc(e?.MIC_observaciones || "")}</textarea></div>
    </div>`,
};

function cfSel(id, val) {
  return `<select class="form-select" id="${id}">
    <option value="">-- Selecciona --</option>
    <option ${val === "Cumple" ? "selected" : ""}>Cumple</option>
    <option ${val === "No Cumple" ? "selected" : ""}>No Cumple</option>
  </select>`;
}
function cfFecha(v) {
  if (!v) return "";
  if (typeof v === "string") return v.substring(0, 10);
  if (v.date) return v.date.substring(0, 10);
  return "";
}

function cfLeerDatos(etapa) {
  const g = (id) => cfQ("#" + id)?.value ?? "";
  if (etapa === "inspeccion")
    return {
      fechaFabricacion: g("cf_fechaFabricacion"),
      fechaCaducidad: g("cf_fechaCaducidad"),
      seguridad: g("cf_seguridad"),
      desempeno: g("cf_desempeno"),
      apariencia: g("cf_apariencia"),
      observaciones: g("cf_observaciones"),
    };
  if (etapa === "fisicoquimico")
    return {
      viscosidad: g("cf_viscosidad"),
      ph: g("cf_ph"),
      densidad: g("cf_densidad"),
      aspectoColor: g("cf_aspectoColor"),
      olor: g("cf_olor"),
      observaciones: g("cf_observaciones"),
    };
  return {
    tamc: g("cf_tamc"),
    tymc: g("cf_tymc"),
    patogenos: g("cf_patogenos"),
    observaciones: g("cf_observaciones"),
  };
}

async function cfAbrirEtapa(id, etapa, autorizar = false) {
  const r = await cfApi({ accion: "detalle", id });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

  const e = r.etapas[etapa];
  if (e === undefined)
    return cfSwal({
      title: "Sin acceso",
      text: "No tienes visibilidad de esta etapa.",
      icon: "info",
    });

  const editable = !autorizar && r.editable[etapa];
  cfQ("#etapaTitulo").textContent =
    `${CF_ETAPA_TXT[etapa]} — ${r.cert.CER_folio} (${r.cert.CER_clave})`;

  let body = "";
  const esp = r.especs;
  if (esp && etapa === "fisicoquimico") {
    body += `<div class="alert alert-info py-2 mb-3">
      <b>Especificaciones ${cfEsc(r.cert.CER_clave)}</b><br>
      Viscosidad ${esp.visMin}–${esp.visMax} (obj ${esp.visObj}) ·
      pH ${esp.phMin}–${esp.phMax} (obj ${esp.phObj}) ·
      Densidad ${esp.denMin}–${esp.denMax} (obj ${esp.denObj})<br>
      <small>Aspecto: ${cfEsc(esp.aspecto)} · Olor: ${cfEsc(esp.olor)}</small></div>`;
  }
  body += CF_CAMPOS[etapa](e, !editable);

  const pre = { inspeccion: "INS", fisicoquimico: "FIS", microbiologia: "MIC" }[
    etapa
  ];
  if (+e[`${pre}_estatus`] === 2)
    body += `<div class="alert alert-success mt-3 py-2">Autorizada por ${cfEsc(e[`${pre}_nombreAutoriza`] || "")} — solo lectura.</div>`;
  if (+e[`${pre}_estatus`] === 3 && e[`${pre}_motivoRechazo`])
    body += `<div class="alert alert-danger mt-3 py-2">Rechazo: ${cfEsc(e[`${pre}_motivoRechazo`])}</div>`;
  cfQ("#etapaBody").innerHTML = body;

  let footer = `<button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>`;
  if (editable) {
    footer += `<button class="btn btn-outline-primary" onclick="cfGuardar(${id}, '${etapa}', false)">Guardar borrador</button>
               <button class="btn btn-primary" onclick="cfGuardar(${id}, '${etapa}', true)">Enviar a autorización</button>`;
  }
  if (autorizar) {
    footer += `<button class="btn btn-danger" onclick="cfAutorizar(${id}, '${etapa}', false)">Rechazar</button>
               <button class="btn btn-success" onclick="cfAutorizar(${id}, '${etapa}', true)">Autorizar</button>`;
  }
  cfQ("#etapaFooter").innerHTML = footer;
  bootstrap.Modal.getOrCreateInstance(cfQ("#modalEtapa")).show();
}

async function cfGuardar(id, etapa, enviar) {
  if (enviar) {
    const c = await cfSwal({
      title: "¿Enviar a autorización?",
      text: "Después de autorizarse ya no podrás editar esta información.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, enviar",
      cancelButtonText: "Cancelar",
    });
    if (!c.isConfirmed) return;
  }
  const r = await cfApi({
    accion: "guardar",
    id,
    etapa,
    enviar,
    datos: cfLeerDatos(etapa),
  });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });
  bootstrap.Modal.getInstance(cfQ("#modalEtapa"))?.hide();
  cfSwal({
    title: "Listo",
    text: enviar ? "Enviado a autorización." : "Borrador guardado.",
    icon: "success",
  });
  // cfCargarEspacio();
  cfRefrescarTodo();
}

// Pide motivo con el modal Bootstrap ya cerrado (evita el bloqueo del input)
async function cfPedirMotivo(titulo) {
  const m = await cfSwal({
    title: titulo,
    input: "textarea",
    inputPlaceholder: "Escribe el motivo…",
    inputAttributes: { "aria-label": "Motivo" },
    showCancelButton: true,
    confirmButtonText: "Enviar",
    cancelButtonText: "Cancelar",
    inputValidator: (v) =>
      !v || !v.trim() ? "El motivo es obligatorio" : undefined,
    didOpen: () => {
      const i = Swal.getInput();
      if (i) {
        i.removeAttribute("disabled");
        i.focus();
      }
    },
  });
  return m.isConfirmed ? m.value.trim() : null;
}

async function cfAutorizar(id, etapa, aprobar) {
  const modal = bootstrap.Modal.getInstance(cfQ("#modalEtapa"));
  let motivo = "";
  if (!aprobar) {
    modal?.hide(); // cerrar antes de pedir el texto
    motivo = await cfPedirMotivo("Motivo de rechazo");
    if (!motivo) return;
  } else {
    const c = await cfSwal({
      title: "¿Autorizar etapa?",
      text: "La información quedará en solo lectura de forma definitiva.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Autorizar",
      cancelButtonText: "Cancelar",
    });
    if (!c.isConfirmed) return;
    modal?.hide();
  }
  const r = await cfApi({ accion: "autorizar", id, etapa, aprobar, motivo });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });
  cfSwal({
    title: "Listo",
    text: aprobar ? "Etapa autorizada." : "Etapa rechazada; regresa a captura.",
    icon: "success",
  });
  // cfCargarEspacio();
  cfRefrescarTodo();
}

async function cfEnviarGT(id) {
  const c = await cfSwal({
    title: "¿Enviar certificado a Gerencia Técnica?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Enviar",
    cancelButtonText: "Cancelar",
  });
  if (!c.isConfirmed) return;
  const r = await cfApi({ accion: "enviar_gt", id });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });
  cfSwal({
    title: "Listo",
    text: "Certificado enviado a validación final.",
    icon: "success",
  });
  // cfCargarEspacio();
  cfRefrescarTodo();
}

// ================= CERTIFICADOS =================
async function cfCargarCerts() {
  const tb = cfQ("#tblCerts");
  const r = await cfApi({ accion: "certificados" });
  if (!r.ok) {
    tb.innerHTML = `<tr><td colspan="7">${cfVacio(r.error)}</td></tr>`;
    return;
  }
  if (!r.items.length) {
    tb.innerHTML = `<tr><td colspan="7">${cfVacio("Sin certificados.")}</td></tr>`;
    return;
  }
  const badge = (s) =>
    ({
      ABIERTO: "cf-captura",
      LISTO: "cf-autorizado",
      ENVIADO_GT: "cf-gt",
      APROBADO: "cf-autorizado",
      RECHAZADO: "cf-rechazado",
    })[s] || "cf-bloqueado";
  tb.innerHTML = r.items
    .map(
      (c) => `<tr>
      <td><strong>${cfEsc(c.folio)}</strong></td><td><code>${cfEsc(c.clave)}</code></td>
      <td>${cfEsc(c.producto)}</td><td>${cfEsc(c.fechaEmision || "—")}</td>
      <td><span class="cf-badge ${badge(c.estatus)}">${cfEsc(c.estatus)}</span></td>
      <td>${cfEsc(c.gerente || "—")}<br><small>${cfEsc(c.fechaFirma || "")}</small></td>
      <td>${
        c.estatus === "APROBADO"
          ? `<button class="btn btn-sm btn-outline-primary" onclick="cfPreviewCert(${c.id})">Ver</button>
        <a class="btn btn-sm btn-primary" target="_blank" href="${CF_PDF}?id=${c.id}">PDF</a>`
          : "—"
      }</td>
    </tr>`,
    )
    .join("");
}

async function cfPreviewCert(id, validarGT = false) {
  const r = await cfApi({ accion: "pdf_datos", id });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

  const { cert: c, ins, fis, mic, especs: esp } = r;
  const prodPDF = r.pdfProducto ?? c.CER_producto;
  const catPDF = r.pdfCategoria ?? c.CER_categoria;
  const presPDF = r.pdfPresentacion ?? c.CER_presentacion;

  const f = (v) => cfEsc(v ?? "—");
  const fd = (v) => cfEsc(cfFecha(v) || "—");
  const firmaImg =
    c.CER_estatus === "APROBADO" && r.noempFirma
      ? `<img src="${CF_FIRMA_URL(r.noempFirma)}" alt="Firma" onerror="this.style.display='none'">`
      : "";

  {
    /* <td><b>Categoría del Producto</b></td><td>${f(c.CER_categoria)}</td>
          <td><b>Nombre del Producto</b></td><td>${f(c.CER_producto)}</td></tr> */
  }

  cfQ("#certHoja").innerHTML = `
    <div class="cert-top">
      <img src="${CF_LOGO_URL}" alt="KCM" onerror="this.style.display='none'">
      <div class="cert-tit">
        <h5>CERTIFICADO DE CALIDAD LÍQUIDOS Y FORMULADOS</h5>
        <small>FORM-63297</small>
      </div>
      <div style="text-align:right;font-size:10.5px;"><b>FECHA DE EMISIÓN</b><br>${fd(c.CER_fechaEmision)}</div>
    </div>

    <table>
      <tr><td><b>Categoría del Producto</b></td><td>${f(catPDF)}</td>
          <td><b>Nombre del Producto</b></td><td>${f(prodPDF)}</td></tr>
      <tr><td><b>Presentación</b></td><td>${f(presPDF)}</td>
          <td><b>Nombre del Fabricante</b></td><td>${f(c.CER_fabricante)}</td></tr>
      <tr><td><b>País de Origen</b></td><td>${f(c.CER_paisOrigen)}</td>
          <td><b>Fecha de Fabricación</b></td><td>${fd(ins?.INS_fechaFabricacion)}</td></tr>
      <tr><td><b>Fecha de Caducidad</b></td><td>${fd(ins?.INS_fechaCaducidad)}</td>
          <td><b>Número de Lote</b></td><td>${f(c.CER_lote)}</td></tr>
      <tr><td><b>Clave KCM</b></td><td colspan="3">${f(c.CER_clave)}</td></tr>
    </table>

    <div class="cert-bloque-tit">Variables Fisicoquímicas</div>
    <table>
      <tr><th>Variable</th><th>Método</th><th>Unidad</th><th>Mínimo</th><th>Objetivo</th><th>Máximo</th><th>Resultado</th></tr>
      <tr><td>Viscosidad</td><td>TTM-00557</td><td>cps</td>
          <td>${f(esp.visMin)}</td><td>${f(esp.visObj)}</td><td>${f(esp.visMax)}</td><td>${f(fis?.FIS_viscosidad)}</td></tr>
      <tr><td>pH</td><td>TTM-00558</td><td>pH</td>
          <td>${f(esp.phMin)}</td><td>${f(esp.phObj)}</td><td>${f(esp.phMax)}</td><td>${f(fis?.FIS_ph)}</td></tr>
      <tr><td>Densidad</td><td>TTM-00559</td><td>g / mL</td>
          <td>${f(esp.denMin)}</td><td>${f(esp.denObj)}</td><td>${f(esp.denMax)}</td><td>${f(fis?.FIS_densidad)}</td></tr>
    </table>

    <div class="cert-bloque-tit">Especificaciones Organolépticas</div>
    <table>
      <tr><th style="width:40%">Característica</th><th>Especificación</th><th style="width:22%">Resultado</th></tr>
      <tr><td>Aspecto / Color</td><td>${f(esp.aspecto)}</td><td>${f(fis?.FIS_aspectoColor)}</td></tr>
      <tr><td>Olor</td><td>${f(esp.olor)}</td><td>${f(fis?.FIS_olor)}</td></tr>
    </table>

    <div class="cert-bloque-tit">Especificaciones Microbiológicas</div>
    <table>
      <tr><th style="width:45%">Determinación</th><th>Especificación</th><th>Técnica</th><th style="width:18%">Resultado</th></tr>
      <tr><td>Recuento total de microorganismos Mesófilos Aerobios (TAMC)</td><td>≤ 100 UFC / g</td>
          <td rowspan="3">TTM-00554<br>TTM-00556</td><td>${f(mic?.MIC_tamc)}</td></tr>
      <tr><td>Recuento total de Hongos y Levaduras (TYMC)</td><td>≤ 10 UFC / g</td><td>${f(mic?.MIC_tymc)}</td></tr>
      <tr><td style="font-size:9px;">Pseudomonas aeruginosa, Escherichia coli, Salmonella spp., Coliformes fecales y totales,
          Burkholderia cepacia, Staphylococcus aureus, Aspergillus brasiliensis, Candida albicans, Pluralibacter gergoviae</td>
          <td>Ausencia</td><td>${f(mic?.MIC_patogenos)}</td></tr>
    </table>

    <div class="cert-bloque-tit">Evaluación de atributos</div>
    <table>
      <tr><th style="width:45%">Atributo</th><th>Especificación (AQL)</th><th style="width:22%">Resultado</th></tr>
      <tr><td>Seguridad</td><td>&lt;0.025</td><td>${f(ins?.INS_seguridad)}</td></tr>
      <tr><td>Desempeño</td><td>&lt;2.5</td><td>${f(ins?.INS_desempeno)}</td></tr>
      <tr><td>Apariencia</td><td>&lt;4.0</td><td>${f(ins?.INS_apariencia)}</td></tr>
    </table>

    <p style="font-size:11px;"><b>Conclusión:</b> ${
      c.CER_observacionesGT
        ? `<p style="font-size:11px;"><b>Observaciones de Gerencia Técnica:</b> ${cfEsc(c.CER_observacionesGT)}</p>`
        : ""
    }</p>


    <div class="cert-pie">
      <div class="cert-sello">
        <b style="font-size:10.5px;color:#7a8291;">CONTROL DE CALIDAD</b>
        <span>Espacio reservado para el sello<br>de acuerdo al estatus</span>
      </div>
      <div class="cert-firma">
        <b style="font-size:10.5px;">KIMBERLY CLARK DE MÉXICO S.A.B DE C.V.</b>
        <div style="flex:1"></div>
        ${firmaImg}
        <div class="cert-linea">${f(c.CER_nombreGerente || "")}<br>Nombre y Firma — Gerente Técnico</div>
      </div>
    </div>`;

  let footer = `<button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>`;
  if (validarGT) {
    footer += `<button class="btn btn-danger" onclick="cfValidarGT(${id}, false)">Rechazar</button>
               <button class="btn btn-success" onclick="cfValidarGT(${id}, true)">Confirmar y firmar</button>`;
  } else if (c.CER_estatus === "APROBADO") {
    // footer += `<button class="btn btn-primary" onclick="cfDescargarPDF('${cfEsc(c.CER_folio)}')">Descargar PDF</button>`;
    footer += `<a class="btn btn-primary" target="_blank" href="${CF_PDF}?id=${id}">
                    <i class="fa-regular fa-file-pdf me-1"></i> Abrir PDF</a>`;
  }
  cfQ("#certFooter").innerHTML = footer;
  bootstrap.Modal.getOrCreateInstance(cfQ("#modalCert")).show();
}

// async function cfValidarGT(id, aprobar) {
//   const modal = bootstrap.Modal.getInstance(cfQ("#modalCert"));
//   let motivo = "";
//   if (!aprobar) {
//     modal?.hide();
//     motivo = await cfPedirMotivo("Motivo de rechazo");
//     if (!motivo) return;
//   } else {
//     const c = await cfSwal({
//       title: "¿Confirmar certificado?",
//       text: "Se colocará tu firma y el certificado quedará aprobado.",
//       icon: "warning",
//       showCancelButton: true,
//       confirmButtonText: "Confirmar y firmar",
//       cancelButtonText: "Cancelar",
//     });
//     if (!c.isConfirmed) return;
//     modal?.hide();
//   }
//   const r = await cfApi({ accion: "validar_gt", id, aprobar, motivo });
//   if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });
//   cfSwal({
//     title: "Listo",
//     text: aprobar
//       ? "Certificado aprobado y firmado."
//       : "Certificado rechazado.",
//     icon: "success",
//   });
//   // cfCargarEspacio();
//   // cfCargarCerts();
//   cfRefrescarTodo();
// }

// -------- 3) cfValidarGT: el Gerente escribe la conclusión final --------
async function cfValidarGT(id, aprobar) {
  const modal = bootstrap.Modal.getInstance(cfQ("#modalCert"));
  let motivo = "";
  let observaciones = "";

  if (!aprobar) {
    modal?.hide();
    motivo = await cfPedirMotivo("Motivo de rechazo");
    if (!motivo) return;
  } else {
    modal?.hide();
    const res = await cfSwal({
      title: "Confirmar certificado",
      html: `<p style="font-size:.9rem;margin-bottom:.5rem;">
               Escribe las observaciones finales (opcional). Se colocará tu firma
               y el certificado quedará aprobado.
             </p>`,
      input: "textarea",
      inputPlaceholder: "Observaciones de Gerencia Técnica…",
      inputAttributes: { "aria-label": "Observaciones" },
      showCancelButton: true,
      confirmButtonText: "Confirmar y firmar",
      cancelButtonText: "Cancelar",
      didOpen: () => {
        const i = Swal.getInput();
        if (i) i.focus();
      },
    });
    if (!res.isConfirmed) return;
    observaciones = (res.value || "").trim();
  }

  const r = await cfApi({
    accion: "validar_gt",
    id,
    aprobar,
    motivo,
    observaciones,
  });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

  cfSwal({
    title: "Listo",
    text: aprobar
      ? "Certificado aprobado y firmado."
      : "Certificado rechazado.",
    icon: "success",
  });
  cfRefrescarTodo();
}

// Funcion auxiliar anterior
// function cfDescargarPDF(folio) {
//   html2pdf()
//     .set({
//       margin: 8,
//       filename: `Certificado_${folio}.pdf`,
//       html2canvas: { scale: 2, useCORS: true },
//       jsPDF: { orientation: "portrait", unit: "mm", format: "letter" },
//     })
//     .from(cfQ("#certHoja"))
//     .save();
// }

// ================= BUSCADORES =================
function cfFiltroTabla(inputId, tbodyId) {
  const inp = document.getElementById(inputId);
  if (!inp) return;
  inp.addEventListener("input", () => {
    const q = inp.value.toLowerCase();
    document.querySelectorAll(`#${tbodyId} tr`).forEach((tr) => {
      if (tr.querySelector("td[colspan]")) return;
      tr.style.display = tr.textContent.toLowerCase().includes(q) ? "" : "none";
    });
  });
}

// ================= ARRANQUE =================
async function cfArranque() {
  const p = await cfApi({ accion: "perfil" });
  if (p.ok) CF_PERFIL = p;

  // cfCargarEspacio();
  // cfCargarFolios();
  // cfCargarCerts();

  const be = document.getElementById("buscarEspacio");
  if (be)
    be.addEventListener("input", () => {
      const q = be.value.toLowerCase();
      document.querySelectorAll("#listaEspacio .cf-item").forEach((el) => {
        el.style.display = (el.dataset.buscar || "").includes(q) ? "" : "none";
      });
    });
  cfFiltroTabla("buscarFolios", "tblFolios");
  cfFiltroTabla("buscarCerts", "tblCerts");

  cfRefrescarTodo();
}
document.addEventListener("DOMContentLoaded", cfArranque);

// Refrescar al cambiar de pestaña
document
  .querySelectorAll('#cfTabs button[data-bs-toggle="tab"]')
  .forEach((b) => {
    b.addEventListener("shown.bs.tab", (ev) => {
      const target = ev.target.getAttribute("data-bs-target");
      if (target === "#tabEspacio") cfCargarEspacio();
      if (target === "#tabFolios") cfCargarFolios();
      if (target === "#tabCerts") cfCargarCerts();
    });
  });
