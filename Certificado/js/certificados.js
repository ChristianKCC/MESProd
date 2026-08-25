// js/certificados.js — Certificados de Calidad (FORM-63297) — v5
const CF_API = "php/certificados_api.php";
const CF_PDF = "php/certificado_pdf.php";
const CF_FIRMA_URL = (noemp) => `../../KCMes/FirmaDigital/firmas/${noemp}.png`;
const CF_LOGO_URL = "../../KCMes/img/imglogoprosede.png";

let CF_PERFIL = { roles: [], nombre: "" };
let CF_ESPACIO = []; // cache para el buscador

const CF_EST_PALET = {
  "EN PROCESO": "cf-enviado",
  LIBERADO: "cf-autorizado",
  RECHAZADO: "cf-rechazado",
  "RECHAZADO EN ORIGEN": "cf-rechazado",
};

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
  await Promise.all([cfCargarEspacio(), cfCargarCerts(), cfCargarGrupos()]);
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
const CF_PRE = {
  inspeccion: "INS",
  fisicoquimico: "FIS",
  microbiologia: "MIC",
};

// ================= MI ESPACIO (tarjetas desplegables) =================
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

// Historial acumulado: captura y autorización conviven, no se reemplazan
function cfHistorial(et) {
  const filas = [];

  if (et.capturo) {
    filas.push(`<div class="cf-hist-item cf-hist-cap">
        <i class="fa-solid fa-pen"></i>
        <div>Capturó <b>${cfEsc(et.capturo)}</b>
          ${et.fechaCaptura ? `<span class="cf-hist-fecha">· ${cfEsc(et.fechaCaptura)}</span>` : ""}
        </div>
      </div>`);
  }

  if (et.autorizo) {
    const rechazada = et.estatus === 3;
    filas.push(`<div class="cf-hist-item ${rechazada ? "cf-hist-rec" : "cf-hist-aut"}">
        <i class="fa-solid ${rechazada ? "fa-circle-xmark" : "fa-circle-check"}"></i>
        <div>${rechazada ? "Rechazó" : "Autorizó"} <b>${cfEsc(et.autorizo)}</b>
          ${et.fechaAutoriza ? `<span class="cf-hist-fecha">· ${cfEsc(et.fechaAutoriza)}</span>` : ""}
          ${rechazada && et.motivoRechazo ? `<br>Motivo: ${cfEsc(et.motivoRechazo)}` : ""}
        </div>
      </div>`);
  }

  return filas.length
    ? `<div class="cf-historial">${filas.join("")}</div>`
    : "";
}

function cfPintarEspacio(items, roles = [], ibm = "") {
  const cont = cfQ("#listaEspacio");

  // Aviso cuando el IBM no está dado de alta en tblMXPRCertificadoPerfilFR
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
        // Mini semáforo de las 3 etapas
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

        // Los 3 pasos dentro del desplegable
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
              btn = "";
            if (et.bloqueado) {
              badge = `<span class="cf-badge cf-bloqueado"><i class="fa-solid fa-lock"></i> Esperando etapas previas</span>`;
            } else {
              badge = `<span class="cf-badge ${CF_EST_CLS[et.estatus]}">${CF_EST_TXT[et.estatus]}</span>`;
            }

            const info = et.bloqueado
              ? `<div class="cf-paso-info">Requiere Inspección y Fisicoquímico autorizados.</div>`
              : cfHistorial(et);

            if (et.puedeCapturar && !et.bloqueado) {
              const txt = et.estatus === 3 ? "Corregir" : "Capturar";
              const color = et.estatus === 3 ? "btn-warning" : "btn-primary";
              btn = `<button class="btn btn-sm ${color}" onclick="cfAbrirEtapa(${it.id}, '${e}')"><i class="fa-solid fa-pen-to-square"></i> ${txt}</button>`;
            } else if (et.puedeAutorizar) {
              btn = `<button class="btn btn-sm btn-success" onclick="cfAbrirEtapa(${it.id}, '${e}', true)"><i class="fa-solid fa-clipboard-check"></i> Revisar</button>`;
            } else {
              btn = `<button class="btn btn-sm btn-outline-secondary" onclick="cfAbrirEtapa(${it.id}, '${e}')"><i class="fa-solid fa-eye"></i> Ver</button>`;
            }

            return `<div class="cf-paso ${et.bloqueado ? "bloq" : ""}">
              <div class="cf-paso-nom"><i class="fa-solid ${CF_ETAPA_ICO[e]}"></i> ${CF_ETAPA_TXT[e]}</div>
              ${badge}
              ${info}
              <div>${btn}</div>
            </div>`;
          })
          .join("");

        // Acción a nivel certificado
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
            ${
              it.paletsPendientes > 0
                ? `<span class="cf-badge cf-captura ms-auto me-2" title="Palets integrados sin liberar">
           ${it.paletsPendientes} palet(s) por liberar</span>`
                : ""
            }
            <i class="fa-solid fa-chevron-down cf-chevron"></i>
          </div>
          <div class="cf-item-body">
          <div class="cf-item-body-inner">
            <div class="cf-pasos">${pasos}</div>
            ${accionCert}
          </div>
        </div>
        </div>`;
      })
      .join("");
}

// ================= INICIAR CERTIFICADO =================
let CF_GRUPO = null; // grupo abierto en el modal

let CF_RECHAZO = null; // grupo abierto en el modal de rechazo

// ---------- 1) REEMPLAZA cfCargarGrupos ----------
async function cfCargarGrupos() {
  const tb = cfQ("#tblFolios");
  const r = await cfApi({ accion: "grupos" });
  if (!r.ok) {
    tb.innerHTML = `<tr><td colspan="8">${cfVacio(r.error)}</td></tr>`;
    return;
  }
  if (!r.items.length) {
    tb.innerHTML = `<tr><td colspan="8">${cfVacio("No hay folios pendientes de certificar.")}</td></tr>`;
    return;
  }

  // Solo supervisores y Gerencia pueden rechazar en origen
  const puedeRechazar = (CF_PERFIL.roles || []).some((x) =>
    [
      "SUP_INSPECCION",
      "SUP_FISICOQUIMICO",
      "SUP_MICROBIOLOGIA",
      "GERENTE",
    ].includes(x),
  );

  tb.innerHTML = r.items
    .map((g) => {
      const parcial =
        g.paletsUsados > 0
          ? `<span class="cf-badge cf-captura ms-1" title="Ya hay palets certificados de este folio">Parcial</span>`
          : "";

      const btnRechazo = puedeRechazar
        ? `<button class="btn btn-sm btn-outline-danger ms-1"
                   title="Rechazar palets sin iniciar certificación"
                   onclick="cfAbrirRechazo('${cfEsc(g.folio)}','${cfEsc(g.clave)}',${g.turno})">
             <i class="fa-solid fa-ban"></i> Rechazar
           </button>`
        : "";

      return `<tr>
        <td>${cfEsc(g.fecha)}</td>
        <td>${cfEsc(g.maquina)}</td>
        <td><strong>${cfEsc(g.folio)}</strong></td>
        <td>${cfEsc(g.turno)}</td>
        <td><code>${cfEsc(g.clave)}</code></td>
        <td class="text-start">${cfEsc(g.producto)}</td>
        <td>
          <span class="cf-badge cf-autorizado">${g.disponibles} de ${g.totalPalets} palets</span>${parcial}
          <br><small class="text-muted">${cfEsc(g.totalCajas)} cajas en total</small>
        </td>
        <td class="text-nowrap">
          <button class="btn btn-sm btn-primary"
                  onclick="cfAbrirGrupo('${cfEsc(g.folio)}','${cfEsc(g.clave)}',${g.turno})">
            <i class="fa-solid fa-list-check"></i> Revisar e iniciar
          </button>
          ${btnRechazo}
        </td>
      </tr>`;
    })
    .join("");
}

async function cfAbrirRechazo(folio, clave, turno) {
  const r = await cfApi({ accion: "palets", folio, clave, turno });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

  // Solo se rechazan en origen los que no están en un certificado
  const candidatos = r.items.filter(
    (p) => !p.usado && p.estadoBits !== "RECHAZADO EN ORIGEN",
  );
  if (!candidatos.length)
    return cfSwal({
      title: "Sin palets disponibles",
      text: "Todos los palets de este folio ya están en un certificado o rechazados. El rechazo tendría que hacerse desde Gerencia Técnica.",
      icon: "info",
    });

  CF_RECHAZO = { ...r, candidatos };

  cfQ("#rechazoTitulo").textContent = `${r.folio} — ${r.clave}`;
  cfQ("#rechazoResumen").innerHTML = `
    <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem;">
      <i class="fa-solid fa-triangle-exclamation me-1"></i>
      <b>Rechazo en origen.</b> El material quedará marcado como rechazado sin pasar por
      certificación y <b>no podrá integrarse a un certificado después</b>.
    </div>
    <div class="cf-resumen">
      <div class="cf-res-item"><span>Producto</span><b>${cfEsc(r.producto)}</b></div>
      <div class="cf-res-item"><span>Máquina</span><b>${cfEsc(r.maquina)}</b></div>
      <div class="cf-res-item"><span>Turno</span><b>${cfEsc(r.turno)}</b></div>
      <div class="cf-res-item"><span>Palets disponibles</span><b>${candidatos.length}</b></div>
    </div>`;

  cfQ("#rechazoPalets").innerHTML = candidatos
    .map(
      (p) => `<tr>
        <td><input class="form-check-input cf-rech" type="checkbox"
                   value="${p.idBajada}" data-cajas="${p.cajas}"
                   data-palet="${p.noPalet}" onchange="cfContarRechazo()"></td>
        <td><strong>${String(p.noPalet).padStart(3, "0")}</strong></td>
        <td>${cfEsc(p.fecha)}</td><td>${cfEsc(p.hora)}</td>
        <td>${cfEsc(p.cajas)}</td>
        <td><span class="cf-badge cf-enviado">${cfEsc(p.estadoBits || "EN PROCESO")}</span></td>
      </tr>`,
    )
    .join("");

  cfQ("#rechazoTodos").checked = false;
  cfQ("#rechazoMotivo").value = "";
  cfContarRechazo();
  bootstrap.Modal.getOrCreateInstance(cfQ("#modalRechazo")).show();
}

function cfMarcarTodosRechazo(chk) {
  document.querySelectorAll(".cf-rech").forEach((c) => (c.checked = chk));
  cfContarRechazo();
}

function cfContarRechazo() {
  const sel = [...document.querySelectorAll(".cf-rech:checked")];
  const cajas = sel.reduce((a, c) => a + (+c.dataset.cajas || 0), 0);
  cfQ("#rechazoConteo").innerHTML = sel.length
    ? `<b>${sel.length}</b> palet(s) · <b>${cajas}</b> cajas se marcarán como rechazadas`
    : `Selecciona los palets que vas a rechazar`;
  cfQ("#btnRechazarOrigen").disabled = sel.length === 0;

  const todos = document.querySelectorAll(".cf-rech").length;
  const chkTodos = cfQ("#rechazoTodos");
  if (chkTodos) {
    chkTodos.checked = sel.length === todos && todos > 0;
    chkTodos.indeterminate = sel.length > 0 && sel.length < todos;
  }
}

async function cfRechazarOrigen() {
  const sel = [...document.querySelectorAll(".cf-rech:checked")];
  const palets = sel.map((c) => +c.value);
  const motivo = cfQ("#rechazoMotivo").value.trim();

  if (!palets.length)
    return cfSwal({
      title: "Sin palets",
      text: "Selecciona al menos uno.",
      icon: "warning",
    });
  if (!motivo)
    return cfSwal({
      title: "Falta el motivo",
      text: "Escribe por qué se rechaza el material.",
      icon: "warning",
    });

  const lista = sel
    .map((c) => String(c.dataset.palet).padStart(3, "0"))
    .join(", ");

  const c = await cfSwal({
    title: "¿Rechazar el material?",
    html: `Se rechazarán <b>${palets.length}</b> palet(s): <code>${lista}</code><br><br>
           <span class="text-danger"><b>Esta acción no se revierte desde el módulo.</b></span><br>
           <small class="text-muted">Motivo: <i>${cfEsc(motivo)}</i></small>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, rechazar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#B02A2A",
  });
  if (!c.isConfirmed) return;

  const r = await cfApi({ accion: "rechazar_origen", palets, motivo });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

  bootstrap.Modal.getInstance(cfQ("#modalRechazo"))?.hide();
  cfSwal({
    title: "Material rechazado",
    text: `${r.rechazados} palet(s) quedaron marcados como rechazados en origen.`,
    icon: "success",
  });
  cfRefrescarTodo();
}

// ---------- Tab: grupos disponibles ----------
// async function cfCargarGrupos() {
//   const tb = cfQ("#tblFolios");
//   const r = await cfApi({ accion: "grupos" });
//   if (!r.ok) {
//     tb.innerHTML = `<tr><td colspan="8">${cfVacio(r.error)}</td></tr>`;
//     return;
//   }
//   if (!r.items.length) {
//     tb.innerHTML = `<tr><td colspan="8">${cfVacio("No hay folios pendientes de certificar.")}</td></tr>`;
//     return;
//   }

//   tb.innerHTML = r.items
//     .map((g) => {
//       const parcial =
//         g.paletsUsados > 0
//           ? `<span class="cf-badge cf-captura ms-1" title="Ya hay palets certificados de este folio">Parcial</span>`
//           : "";
//       return `<tr>
//         <td>${cfEsc(g.fecha)}</td>
//         <td>${cfEsc(g.maquina)}</td>

// <td><strong>${cfEsc(g.folio)}</strong><br>
//     <small class="text-muted">${cfEsc(g.folioCompleto)}</small></td>

//         <td>${cfEsc(g.turno)}</td>
//         <td><code>${cfEsc(g.clave)}</code></td>
//         <td class="text-start">${cfEsc(g.producto)}</td>
//         <td>
//           <span class="cf-badge cf-autorizado">${g.disponibles} de ${g.totalPalets} palets</span>${parcial}
//           <br><small class="text-muted">${cfEsc(g.totalCajas)} cajas en total</small>
//         </td>
//         <td>
//           <button class="btn btn-sm btn-primary"
//                   onclick="cfAbrirGrupo('${cfEsc(g.folio)}','${cfEsc(g.clave)}',${g.turno})"
// >
//             <i class="fa-solid fa-list-check"></i> Revisar e iniciar
//           </button>
//         </td>
//       </tr>`;
//     })
//     .join("");
// }

// ---------- Modal: resumen del folio y selección de palets ----------
// async function cfAbrirGrupo(folio, clave, turno) {
//   const r = await cfApi({ accion: "palets", folio, clave, turno });
//   if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

//   CF_GRUPO = r;

//   cfQ("#grupoTitulo").textContent = `${r.folio} — ${r.clave}`;

//   // Resumen tipo RARR
//   cfQ("#grupoResumen").innerHTML = `
//     <div class="cf-resumen">
//       <div class="cf-res-item"><span>Producto</span><b>${cfEsc(r.producto)}</b></div>
//       <div class="cf-res-item"><span>Máquina</span><b>${cfEsc(r.maquina)}</b></div>
//       <div class="cf-res-item"><span>Turno</span><b>${cfEsc(r.turno)}</b></div>
//       <div class="cf-res-item"><span>Palets disponibles</span><b>${r.disponibles}</b></div>
//       <div class="cf-res-item"><span>Cajas disponibles</span><b>${r.cajasDisponibles}</b></div>
//     </div>`;

//   const ce = r.certExistente;
//   if (ce) {
//     const yaFirmado = ce.estatus === "APROBADO" || ce.estatus === "RECHAZADO";
//     cfQ("#grupoResumen").insertAdjacentHTML(
//       "beforeend",
//       `<div class="alert ${yaFirmado ? "alert-warning" : "alert-info"} py-2 mt-3 mb-0" style="font-size:.85rem;">
//          <i class="fa-solid fa-link me-1"></i>
//          Este folio ya tiene el <b>certificado #${ce.id}</b> (${cfEsc(ce.estatus)}, v${ce.version})
//          con ${ce.palets} palet(s). Los que selecciones se <b>integrarán ahí</b>, no se creará otro.
//          ${
//            yaFirmado
//              ? `<br><i class="fa-solid fa-triangle-exclamation me-1"></i>
//                 Como ya está firmado, se <b>reabrirá</b> y Gerencia Técnica deberá validarlo de nuevo.`
//              : ""
//          }
//        </div>`,
//     );
//   }

//   // Tabla de palets individuales
//   cfQ("#grupoPalets").innerHTML = r.items
//     .map((p) => {
//       if (p.usado) {
//         return `<tr class="table-light text-muted">
//           <td><i class="fa-solid fa-lock"></i></td>
//           <td>${String(p.noPalet).padStart(3, "0")}</td>
//           <td>${cfEsc(p.fecha)}</td><td>${cfEsc(p.hora)}</td>
//           <td>${cfEsc(p.cajas)}</td>
//           <td><span class="cf-badge cf-bloqueado">En certificado #${p.idCertUsado}</span></td>
//         </tr>`;
//       }
//       return `<tr>
//         <td><input class="form-check-input cf-palet" type="checkbox"
//                    value="${p.idBajada}" data-cajas="${p.cajas}" checked
//                    onchange="cfContarPalets()"></td>
//         <td><strong>${String(p.noPalet).padStart(3, "0")}</strong></td>
//         <td>${cfEsc(p.fecha)}</td><td>${cfEsc(p.hora)}</td>
//         <td>${cfEsc(p.cajas)}</td>
//         <td><span class="cf-badge cf-autorizado">Disponible</span></td>
//       </tr>`;
//     })
//     .join("");

//   cfQ("#grupoTodos").checked = true;
//   cfContarPalets();
//   bootstrap.Modal.getOrCreateInstance(cfQ("#modalGrupo")).show();
// }

async function cfAbrirGrupo(folio, clave, turno) {
  const r = await cfApi({ accion: "palets", folio, clave, turno });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

  CF_GRUPO = r;
  cfQ("#grupoTitulo").textContent = `${r.folio} — ${r.clave}`;

  cfQ("#grupoResumen").innerHTML = `
    <div class="cf-resumen">
      <div class="cf-res-item"><span>Producto</span><b>${cfEsc(r.producto)}</b></div>
      <div class="cf-res-item"><span>Máquina</span><b>${cfEsc(r.maquina)}</b></div>
      <div class="cf-res-item"><span>Turno</span><b>${cfEsc(r.turno)}</b></div>
      <div class="cf-res-item"><span>Palets disponibles</span><b>${r.disponibles}</b></div>
      <div class="cf-res-item"><span>Cajas disponibles</span><b>${r.cajasDisponibles}</b></div>
    </div>`;

  // Certificado ya existente para este folio
  const ce = r.certExistente;
  if (ce) {
    const yaFirmado = ce.estatus === "APROBADO" || ce.estatus === "RECHAZADO";
    cfQ("#grupoResumen").insertAdjacentHTML(
      "beforeend",
      `<div class="alert ${yaFirmado ? "alert-warning" : "alert-info"} py-2 mt-3 mb-0" style="font-size:.85rem;">
         <i class="fa-solid fa-link me-1"></i>
         Este folio ya tiene el <b>certificado #${ce.id}</b> (${cfEsc(ce.estatus)}, v${ce.version})
         con ${ce.palets} palet(s). Los que selecciones se <b>integrarán ahí</b>.
         ${
           yaFirmado
             ? `<br><i class="fa-solid fa-triangle-exclamation me-1"></i>
                Como ya está firmado, se <b>reabrirá</b> y Gerencia Técnica deberá validarlo de nuevo.`
             : ""
         }
       </div>`,
    );
  }

  // Exclusiones previas sin resolver
  const exc = r.exclusiones || [];
  if (exc.length) {
    cfQ("#grupoResumen").insertAdjacentHTML(
      "beforeend",
      `<div class="alert alert-secondary py-2 mt-2 mb-0" style="font-size:.82rem;">
         <b><i class="fa-solid fa-circle-exclamation me-1"></i> Palets excluidos anteriormente:</b>
         ${exc
           .map(
             (e) =>
               `<div class="mt-1">Palet ${String(e.palet).padStart(3, "0")} —
                 ${cfEsc(e.motivo)}
                 <small class="text-muted">(${cfEsc(e.quien || "")} ${cfEsc(e.fecha || "")})</small>
               </div>`,
           )
           .join("")}
       </div>`,
    );
  }

  cfQ("#grupoPalets").innerHTML = r.items
    .map((p) => {
      const est = p.estadoBits || "EN PROCESO";
      const clsEst = CF_EST_PALET[est] || "cf-bloqueado";

      if (p.usado) {
        return `<tr class="table-light text-muted">
          <td><i class="fa-solid fa-lock"></i></td>
          <td>${String(p.noPalet).padStart(3, "0")}</td>
          <td>${cfEsc(p.fecha)}</td><td>${cfEsc(p.hora)}</td>
          <td>${cfEsc(p.cajas)}</td>
          <td><span class="cf-badge cf-bloqueado">En certificado #${p.idCertUsado}</span>
              <br><small>${cfEsc(est)}</small></td>
        </tr>`;
      }

      // Rechazado en origen: ya no se puede integrar
      if (est === "RECHAZADO EN ORIGEN") {
        return `<tr class="table-light text-muted">
          <td><i class="fa-solid fa-ban"></i></td>
          <td>${String(p.noPalet).padStart(3, "0")}</td>
          <td>${cfEsc(p.fecha)}</td><td>${cfEsc(p.hora)}</td>
          <td>${cfEsc(p.cajas)}</td>
          <td><span class="cf-badge cf-rechazado">Rechazado en origen</span></td>
        </tr>`;
      }

      return `<tr>
        <td><input class="form-check-input cf-palet" type="checkbox"
                   value="${p.idBajada}" data-cajas="${p.cajas}"
                   data-palet="${p.noPalet}" checked onchange="cfContarPalets()"></td>
        <td><strong>${String(p.noPalet).padStart(3, "0")}</strong></td>
        <td>${cfEsc(p.fecha)}</td><td>${cfEsc(p.hora)}</td>
        <td>${cfEsc(p.cajas)}</td>
        <td><span class="cf-badge ${clsEst}">${cfEsc(est)}</span></td>
      </tr>`;
    })
    .join("");

  cfQ("#grupoTodos").checked = true;
  cfContarPalets();
  bootstrap.Modal.getOrCreateInstance(cfQ("#modalGrupo")).show();
}

function cfMarcarTodos(chk) {
  document.querySelectorAll(".cf-palet").forEach((c) => (c.checked = chk));
  cfContarPalets();
}

function cfContarPalets() {
  const sel = [...document.querySelectorAll(".cf-palet:checked")];
  const cajas = sel.reduce((a, c) => a + (+c.dataset.cajas || 0), 0);
  cfQ("#grupoConteo").innerHTML =
    `<b>${sel.length}</b> palet(s) · <b>${cajas}</b> cajas seleccionadas`;
  cfQ("#btnIniciarGrupo").disabled = sel.length === 0;

  const todos = document.querySelectorAll(".cf-palet").length;
  const chkTodos = cfQ("#grupoTodos");
  if (chkTodos) {
    chkTodos.checked = sel.length === todos && todos > 0;
    chkTodos.indeterminate = sel.length > 0 && sel.length < todos;
  }
}

// async function cfIniciarGrupo() {
//   if (!CF_GRUPO) return;
//   const palets = [...document.querySelectorAll(".cf-palet:checked")].map(
//     (c) => +c.value,
//   );
//   if (!palets.length)
//     return cfSwal({
//       title: "Sin palets",
//       text: "Selecciona al menos uno.",
//       icon: "warning",
//     });

//   const total = document.querySelectorAll(".cf-palet").length;
//   const ce = CF_GRUPO.certExistente;
//   const yaFirmado =
//     ce && (ce.estatus === "APROBADO" || ce.estatus === "RECHAZADO");

//   let html;
//   if (ce) {
//     html = `Se integrarán <b>${palets.length}</b> palet(s) al <b>certificado #${ce.id}</b>.`;
//     if (yaFirmado)
//       html += `<br><br><span class="text-danger"><b>El certificado ya está firmado.</b></span><br>
//                <small>Se reabrirá en una nueva versión y Gerencia Técnica tendrá que validarlo otra vez.
//                Los palets ya liberados conservan su estado.</small>`;
//   } else {
//     html =
//       palets.length < total
//         ? `Se creará el certificado con <b>${palets.length}</b> de <b>${total}</b> palets.<br>
//            <small class="text-muted">Los que dejes fuera se integrarán a este mismo certificado cuando los certifiques.</small>`
//         : `Se creará el certificado con los <b>${palets.length}</b> palets disponibles.`;
//   }

//   const c = await cfSwal({
//     title: ce ? "¿Integrar al certificado existente?" : "¿Iniciar certificado?",
//     html,
//     icon: yaFirmado ? "warning" : "question",
//     showCancelButton: true,
//     confirmButtonText: ce ? "Integrar" : "Iniciar",
//     cancelButtonText: "Cancelar",
//   });
//   if (!c.isConfirmed) return;

//   const r = await cfApi({
//     accion: "iniciar",
//     folio: CF_GRUPO.folio,
//     clave: CF_GRUPO.clave,
//     palets,
//   });
//   if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

//   bootstrap.Modal.getInstance(cfQ("#modalGrupo"))?.hide();
//   cfSwal({
//     title: r.integrado ? "Palets integrados" : "Certificado iniciado",
//     html: r.reabierto
//       ? `Se agregaron ${r.palets} palet(s) al certificado #${r.id}.<br>
//          <b>Reabierto en versión ${r.version}</b>: requiere nueva validación de Gerencia Técnica.`
//       : `${r.palets} palet(s) y ${r.cajas} cajas integradas.`,
//     icon: "success",
//   });
//   cfRefrescarTodo();
// }

// ================= FORMULARIOS POR ETAPA =================

async function cfIniciarGrupo() {
  if (!CF_GRUPO) return;
  const marcados = [...document.querySelectorAll(".cf-palet:checked")];
  const palets = marcados.map((c) => +c.value);
  if (!palets.length)
    return cfSwal({
      title: "Sin palets",
      text: "Selecciona al menos uno.",
      icon: "warning",
    });

  const todos = [...document.querySelectorAll(".cf-palet")];
  const fuera = todos.filter((c) => !c.checked);
  const ce = CF_GRUPO.certExistente;
  const yaFirmado =
    ce && (ce.estatus === "APROBADO" || ce.estatus === "RECHAZADO");

  // Si se deja algún palet fuera, se pide el motivo
  let motivoExclusion = "";
  if (fuera.length) {
    const lista = fuera
      .map((c) => String(c.dataset.palet).padStart(3, "0"))
      .join(", ");
    const res = await cfSwal({
      title: "Palets excluidos",
      html: `Quedarán fuera <b>${fuera.length}</b> palet(s): <code>${lista}</code><br>
             <small class="text-muted">Escribe el motivo; se mostrará en la vista de liberación.</small>`,
      input: "textarea",
      inputPlaceholder: "Motivo de la exclusión…",
      showCancelButton: true,
      confirmButtonText: "Continuar",
      cancelButtonText: "Cancelar",
      inputValidator: (v) =>
        !v || !v.trim() ? "El motivo es obligatorio" : undefined,
      didOpen: () => {
        const i = Swal.getInput();
        if (i) i.focus();
      },
    });
    if (!res.isConfirmed) return;
    motivoExclusion = (res.value || "").trim();
  }

  let html;
  if (ce) {
    html = `Se integrarán <b>${palets.length}</b> palet(s) al <b>certificado #${ce.id}</b>.`;
    if (yaFirmado)
      html += `<br><br><span class="text-danger"><b>El certificado ya está firmado.</b></span><br>
               <small>Se reabrirá en una nueva versión y Gerencia Técnica tendrá que validarlo otra vez.</small>`;
  } else {
    html = `Se creará el certificado con <b>${palets.length}</b> de <b>${todos.length}</b> palets.`;
  }
  if (fuera.length)
    html += `<br><br><small class="text-muted">${fuera.length} palet(s) quedan excluidos:
             <i>${cfEsc(motivoExclusion)}</i></small>`;

  const c = await cfSwal({
    title: ce ? "¿Integrar al certificado existente?" : "¿Iniciar certificado?",
    html,
    icon: yaFirmado ? "warning" : "question",
    showCancelButton: true,
    confirmButtonText: ce ? "Integrar" : "Iniciar",
    cancelButtonText: "Cancelar",
  });
  if (!c.isConfirmed) return;

  const r = await cfApi({
    accion: "iniciar",
    folio: CF_GRUPO.folio,
    clave: CF_GRUPO.clave,
    palets,
    motivoExclusion,
  });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

  bootstrap.Modal.getInstance(cfQ("#modalGrupo"))?.hide();
  cfSwal({
    title: r.integrado ? "Palets integrados" : "Certificado iniciado",
    html: r.reabierto
      ? `Se agregaron ${r.palets} palet(s) al certificado #${r.id}.<br>
         <b>Reabierto en versión ${r.version}</b>: requiere nueva validación.`
      : `${r.palets} palet(s) y ${r.cajas} cajas integradas.`,
    icon: "success",
  });
  cfRefrescarTodo();
}

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

let CF_CAT = null; // catálogos de la clave abierta

// ---------- Utilidades ----------
function cfOpciones(id, opciones, val, onchange = "") {
  const ops = opciones
    .map(
      (o) =>
        `<option ${val === o.valor ? "selected" : ""}>${cfEsc(o.valor)}</option>`,
    )
    .join("");
  return `<select class="form-select" id="${id}" ${onchange ? `onchange="${onchange}"` : ""}>
      <option value="">-- Selecciona --</option>${ops}
    </select>`;
}

// ¿El valor elegido es de falla? (dispara el panel de defectos)
function cfEsFalla(tipo, valor) {
  const o = (CF_CAT?.opciones?.[tipo] || []).find((x) => x.valor === valor);
  return !!o?.esFalla;
}

// Muestra u oculta el panel de defectos de un atributo
function cfToggleDefectos(attr) {
  const sel = cfQ(`#cf_${attr.toLowerCase()}`);
  const panel = cfQ(`#defectos_${attr}`);
  if (!sel || !panel) return;
  const falla = cfEsFalla("ATRIBUTO", sel.value);
  panel.classList.toggle("d-none", !falla);
  if (!falla)
    panel
      .querySelectorAll("input[type=checkbox]")
      .forEach((c) => (c.checked = false));
}

// Panel de defectos de un atributo
function cfPanelDefectos(attr, etiqueta, seleccionados, ro) {
  const lista = CF_CAT?.defectos?.[attr] || [];
  if (!lista.length)
    return `<div id="defectos_${attr}" class="cf-defectos d-none">
        <div class="alert alert-warning py-2 mb-0">Sin defectos configurados para ${cfEsc(etiqueta)}.</div>
      </div>`;

  const items = lista
    .map(
      (d) => `<label class="cf-defecto">
        <input class="form-check-input" type="checkbox" data-attr="${attr}" value="${d.id}"
               ${seleccionados.includes(d.id) ? "checked" : ""} ${ro ? "disabled" : ""}>
        <span>${cfEsc(d.nombre)}</span>
      </label>`,
    )
    .join("");

  return `<div id="defectos_${attr}" class="cf-defectos d-none">
      <div class="cf-defectos-tit"><i class="fa-solid fa-triangle-exclamation"></i>
        Defectos de ${cfEsc(etiqueta)}</div>
      <div class="cf-defectos-grid">${items}</div>
    </div>`;
}

// ---------- Formularios por etapa ----------
const CF_CAMPOS = {
  inspeccion: (e, ro, fechaProd = "", defSel = []) => {
    const ids = defSel.map((d) => d.id);
    const opAtr = CF_CAT?.opciones?.ATRIBUTO || [];
    return `
    <div class="row g-3 ${ro ? "cf-solo-lectura" : ""}">
      <div class="col-md-6"><label class="form-label">Fecha de fabricación</label>
        <input type="date" class="form-control" id="cf_fechaFabricacion"
               value="${cfEsc(cfFecha(e?.INS_fechaFabricacion) || fechaProd)}">
        <div class="form-text">Tomada de la fecha de producción del folio.</div></div>
      <div class="col-md-6"><label class="form-label">Fecha de caducidad (si aplica)</label>
        <input type="date" class="form-control" id="cf_fechaCaducidad" value="${cfEsc(cfFecha(e?.INS_fechaCaducidad))}"></div>

      <div class="col-md-4"><label class="form-label">Seguridad</label>
        ${cfOpciones("cf_seguridad", opAtr, e?.INS_seguridad, "cfToggleDefectos('SEGURIDAD')")}</div>
      <div class="col-md-4"><label class="form-label">Desempeño</label>
        ${cfOpciones("cf_desempeno", opAtr, e?.INS_desempeno, "cfToggleDefectos('DESEMPENO')")}</div>
      <div class="col-md-4"><label class="form-label">Apariencia</label>
        ${cfOpciones("cf_apariencia", opAtr, e?.INS_apariencia, "cfToggleDefectos('APARIENCIA')")}</div>

      <div class="col-12">
        ${cfPanelDefectos("SEGURIDAD", "Seguridad", ids, ro)}
        ${cfPanelDefectos("DESEMPENO", "Desempeño", ids, ro)}
        ${cfPanelDefectos("APARIENCIA", "Apariencia", ids, ro)}
      </div>

      <div class="col-12"><label class="form-label">Observaciones</label>
        <textarea class="form-control" id="cf_observaciones" rows="2">${cfEsc(e?.INS_observaciones || "")}</textarea></div>
    </div>`;
  },

  microbiologia: (e, ro, _fp = "", _ds = [], moSel = {}) => {
    const lista = CF_CAT?.mo || [];
    if (!lista.length)
      return `<div class="alert alert-warning">No hay microorganismos objetables configurados.</div>`;

    const filas = lista
      .map((m) => {
        const val = moSel[m.id] ?? "";
        const campo =
          m.tipo === "RECUENTO"
            ? `<input type="text" class="form-control form-control-sm cf-mo text-center"
                      data-id="${m.id}" data-tipo="RECUENTO" value="${cfEsc(val)}"
                      placeholder="<1, 10, >100…" maxlength="15">`
            : `<select class="form-select form-select-sm cf-mo" data-id="${m.id}" data-tipo="AUSENCIA">
                 <option value="">--</option>
                 ${(CF_CAT?.opciones?.MO || [])
                   .map(
                     (o) =>
                       `<option ${val === o.valor ? "selected" : ""}>${cfEsc(o.valor)}</option>`,
                   )
                   .join("")}
               </select>`;
        return `<tr>
          <td class="text-start"><i>${cfEsc(m.nombre)}</i></td>
          <td>${cfEsc(m.especificacion || "—")}${m.unidad ? " " + cfEsc(m.unidad) : ""}</td>
          <td><small class="text-muted">${cfEsc(m.metodo || "—")}</small></td>
          <td style="width:130px;">${campo}</td>
        </tr>`;
      })
      .join("");

    return `
    <div class="${ro ? "cf-solo-lectura" : ""}">
      <div class="cf-card-title mb-2"><i class="fa-solid fa-bacterium"></i> Microorganismos Objetables (MO)</div>
      <div class="table-responsive" style="max-height:330px;">
        <table class="table table-sm table-bordered align-middle text-center mb-0">
          <thead class="table-dark">
            <tr><th class="text-start">Determinación</th><th>Especificación</th><th>Técnica</th><th>Resultado</th></tr>
          </thead>
          <tbody>${filas}</tbody>
        </table>
      </div>
      <div class="mt-3"><label class="form-label">Observaciones</label>
        <textarea class="form-control" id="cf_observaciones" rows="2">${cfEsc(e?.MIC_observaciones || "")}</textarea></div>
    </div>`;
  },
};

// ---------- Lectura de los formularios ----------

CF_CAMPOS.fisicoquimico = (e, ro) => {
  const par = CF_CAT?.parametros || {};
  const org = CF_CAT?.organolepticas || {};
  const uni = (v, fb) => cfEsc(par[v]?.unidad || fb);

  const espec = (t) => {
    const lista = org[t] || [];
    if (!lista.length)
      return `<div class="form-text text-warning">
                <i class="fa-solid fa-triangle-exclamation"></i> Sin especificación configurada</div>`;
    return `<div class="form-text">
        ${lista.map((x) => `<div>· ${cfEsc(x)}</div>`).join("")}
      </div>`;
  };

  // Rangos configurados de la clave, para guiarse al capturar
  const rangos = (variable) => {
    const p = par[variable];
    if (!p)
      return `<div class="cf-rangos cf-rangos-vacio">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Sin parámetros configurados
              </div>`;
    const val = (x) => (x === null || x === undefined ? "—" : x);
    return `<div class="cf-rangos">
        <div><span>Mín</span><b>${val(p.minimo)}</b></div>
        <div class="cf-rango-obj"><span>Objetivo</span><b>${val(p.objetivo)}</b></div>
        <div><span>Máx</span><b>${val(p.maximo)}</b></div>
      </div>`;
  };

  const campoNum = (variable, id, label, step, val) => `
    <div class="col-md-4">
      <label class="form-label">${label} (${uni(variable, "")})</label>
      ${rangos(variable)}
      <input type="number" step="${step}" class="form-control" id="${id}"
             value="${cfEsc(val ?? "")}"
             oninput="cfEvaluarFQ('${variable}','${id}')">
      <div class="mt-1" id="eval_${variable}"></div>
    </div>`;

  return `
    <div class="row g-3 ${ro ? "cf-solo-lectura" : ""}">
      ${campoNum("DENSIDAD", "cf_densidad", "Densidad", "0.0001", e?.FIS_densidad)}
      ${campoNum("VISCOSIDAD", "cf_viscosidad", "Viscosidad", "0.01", e?.FIS_viscosidad)}
      ${campoNum("PH", "cf_ph", "pH", "0.01", e?.FIS_ph)}

      <div class="col-md-4"><label class="form-label">Aspecto</label>
        ${cfOpciones("cf_aspecto", CF_CAT?.opciones?.ASPECTO || [], e?.FIS_aspecto)}
        ${espec("ASPECTO")}</div>
      <div class="col-md-4"><label class="form-label">Color</label>
        ${cfOpciones("cf_color", CF_CAT?.opciones?.COLOR || [], e?.FIS_color)}
        ${espec("COLOR")}</div>
      <div class="col-md-4"><label class="form-label">Olor</label>
        ${cfOpciones("cf_olor", CF_CAT?.opciones?.OLOR || [], e?.FIS_olor)}
        ${espec("OLOR")}</div>

      <div class="col-12"><label class="form-label">Observaciones</label>
        <textarea class="form-control" id="cf_observaciones" rows="2">${cfEsc(e?.FIS_observaciones || "")}</textarea></div>
    </div>`;
};

function cfLeerDatos(etapa) {
  const g = (id) => cfQ("#" + id)?.value ?? "";

  if (etapa === "inspeccion") {
    const defectos = [
      ...document.querySelectorAll(".cf-defectos:not(.d-none) input:checked"),
    ].map((c) => ({ id: +c.value, atributo: c.dataset.attr }));
    return {
      fechaFabricacion: g("cf_fechaFabricacion"),
      fechaCaducidad: g("cf_fechaCaducidad"),
      seguridad: g("cf_seguridad"),
      desempeno: g("cf_desempeno"),
      apariencia: g("cf_apariencia"),
      observaciones: g("cf_observaciones"),
      defectos,
    };
  }

  if (etapa === "fisicoquimico")
    return {
      densidad: g("cf_densidad"),
      viscosidad: g("cf_viscosidad"),
      ph: g("cf_ph"),
      aspecto: g("cf_aspecto"),
      color: g("cf_color"),
      olor: g("cf_olor"),
      observaciones: g("cf_observaciones"),
    };

  // microbiologia
  const mo = [...document.querySelectorAll(".cf-mo")].map((el) => ({
    id: +el.dataset.id,
    tipo: el.dataset.tipo,
    resultado: el.value,
  }));

  // TAMC / TYMC salen de los MO de tipo RECUENTO (primero y segundo)
  const recuentos = mo.filter((m) => m.tipo === "RECUENTO");
  const presentes = mo
    .filter((m) => m.tipo === "AUSENCIA" && cfEsFalla("MO", m.resultado))
    .map((m) => (CF_CAT.mo.find((x) => x.id === m.id) || {}).nombre)
    .filter(Boolean);

  return {
    tamc: recuentos[0]?.resultado ?? "",
    tymc: recuentos[1]?.resultado ?? "",
    resumenMO: presentes.length
      ? "Presentes: " + presentes.join(", ")
      : "Ausentes",
    observaciones: g("cf_observaciones"),
    mo: mo.filter((m) => m.resultado !== ""),
  };
}

// ---------- Apertura del modal ----------
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

  // Catálogos configurados para esta clave
  const cat = await cfApi({ accion: "catalogos", clave: r.cert.CER_clave });
  CF_CAT = cat.ok ? cat : null;

  const editable = !autorizar && r.editable[etapa];
  cfQ("#etapaTitulo").textContent =
    `${CF_ETAPA_TXT[etapa]} — ${r.cert.CER_folio} (${r.cert.CER_clave})`;

  let body = "";

  // Aviso cuando falta configuración para validar esta clave
  if (etapa === "fisicoquimico") {
    const fp = CF_CAT?.faltanParametros || [];
    const fo = CF_CAT?.faltanOrganolepticas || [];

    if (fp.length) {
      body += `<div class="alert alert-warning py-2 mb-2">
        <i class="fa-solid fa-triangle-exclamation me-1"></i>
        <b>${cfEsc(r.cert.CER_clave)}</b> no tiene rangos configurados para:
        ${cfEsc(fp.join(", "))}. Esos resultados no se evaluarán.
      </div>`;
    }
    if (fo.length) {
      body += `<div class="alert alert-secondary py-2 mb-3" style="font-size:.85rem;">
        <i class="fa-regular fa-circle-question me-1"></i>
        Sin especificación escrita para: ${cfEsc(fo.join(", "))}.
      </div>`;
    }
    if (!fp.length && !fo.length) {
      body += `<div class="alert alert-success py-2 mb-3" style="font-size:.85rem;">
        <i class="fa-solid fa-circle-check me-1"></i>
        Configuración completa para <b>${cfEsc(r.cert.CER_clave)}</b>.
      </div>`;
    }
  }

  body += CF_CAMPOS[etapa](
    e,
    !editable,
    r.fechaProduccion || "",
    r.defectosSel || [],
    r.moSel || {},
  );

  const pre = CF_PRE[etapa];

  // Historial (captura + autorización)
  const hist = [];
  if (e[`${pre}_nombreCaptura`])
    hist.push(
      `<i class="fa-solid fa-pen me-1"></i> Capturó <b>${cfEsc(e[`${pre}_nombreCaptura`])}</b>`,
    );
  if (e[`${pre}_nombreAutoriza`])
    hist.push(
      `<i class="fa-solid fa-circle-check me-1"></i> ${+e[`${pre}_estatus`] === 3 ? "Rechazó" : "Autorizó"} <b>${cfEsc(e[`${pre}_nombreAutoriza`])}</b>`,
    );
  if (hist.length)
    body += `<div class="alert alert-light border mt-3 py-2" style="font-size:.85rem;">${hist.join("<br>")}</div>`;

  if (+e[`${pre}_estatus`] === 2)
    body += `<div class="alert alert-success mt-2 py-2">Etapa autorizada — solo lectura.</div>`;
  if (+e[`${pre}_estatus`] === 3 && e[`${pre}_motivoRechazo`])
    body += `<div class="alert alert-danger mt-2 py-2">Motivo del rechazo: ${cfEsc(e[`${pre}_motivoRechazo`])}</div>`;

  cfQ("#etapaBody").innerHTML = body;

  if (etapa === "fisicoquimico") {
    cfEvaluarFQ("DENSIDAD", "cf_densidad");
    cfEvaluarFQ("VISCOSIDAD", "cf_viscosidad");
    cfEvaluarFQ("PH", "cf_ph");
  }

  // Deja visibles los paneles de defectos que ya venían en falla
  if (etapa === "inspeccion")
    ["SEGURIDAD", "DESEMPENO", "APARIENCIA"].forEach(cfToggleDefectos);

  let footer = `<button class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-rectangle-xmark"></i> Cerrar</button>`;
  if (editable) {
    footer += `<button class="btn btn-outline-primary" onclick="cfGuardar(${id}, '${etapa}', false)"><i class="fa-solid fa-floppy-disk"></i> Guardar borrador</button>
               <button class="btn btn-primary" onclick="cfGuardar(${id}, '${etapa}', true)"><i class="fa-solid fa-paper-plane"></i> Enviar a autorización</button>`;
  }
  if (autorizar) {
    footer += `<button class="btn btn-danger" onclick="cfAutorizar(${id}, '${etapa}', false)"><i class="fa-solid fa-thumbs-down"></i> Rechazar</button>
               <button class="btn btn-success" onclick="cfAutorizar(${id}, '${etapa}', true)"><i class="fa-solid fa-thumbs-up"></i> Autorizar</button>`;
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
      <td><span class="cf-badge ${badge(c.estatus)}">${cfEsc(c.estatus)}</span> <small class="text-muted">${c.paletsLiberados}/${c.palets} palets liberados${
        c.version > 1 ? ` · v${c.version}` : ""
      }</small></td>
      <td>${cfEsc(c.gerente || "—")}<br><small>${cfEsc(c.fechaFirma || "")}</small></td>
      <td>${
        c.estatus === "APROBADO" || c.estatus === "RECHAZADO"
          ? `<button class="btn btn-sm btn-outline-primary" onclick="cfPreviewCert(${c.id})">
           <i class="fa-solid fa-eye"></i> Ver</button>
         <a class="btn btn-sm btn-primary" target="_blank" href="${CF_PDF}?id=${c.id}">
           <i class="fa-regular fa-file-pdf"></i> PDF</a>`
          : "—"
      }</td>
    </tr>`,
    )
    .join("");
}

async function cfPreviewCert(id, validarGT = false) {
  const r = await cfApi({ accion: "pdf_datos", id });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

  const { cert: c, ins, fis } = r;
  const mo = r.mo || [];
  const defectos = r.defectos || [];
  const par = r.parametros || {};
  const ev = r.evaluacion || {};
  const palets = r.palets || [];

  const f = (v) => cfEsc(v ?? "");
  const uni = (k, fb) => cfEsc(par[k]?.unidad ?? fb);
  const veredictoCel = (v) => {
    if (!v) return "";
    const cls = v === "No cumple" ? "coa-nocumple" : "coa-cumple";
    return `<span class="${cls}">${cfEsc(v)}</span>`;
  };

  const presentacion = r.pdfPresentacion ?? c.CER_presentacion ?? "";
  const descProducto = presentacion || r.pdfProducto || c.CER_producto || "";

  const falla = (v) =>
    String(v || "")
      .toLowerCase()
      .startsWith("no");
  const noCumple =
    [
      ins?.INS_seguridad,
      ins?.INS_desempeno,
      ins?.INS_apariencia,
      fis?.FIS_aspecto,
      fis?.FIS_color,
      fis?.FIS_olor,
      ev.DENSIDAD,
      ev.VISCOSIDAD,
      ev.PH,
    ].some(falla) ||
    mo.some((m) =>
      String(m.resultado || "")
        .toLowerCase()
        .includes("presente"),
    );

  const firmaImg =
    c.CER_estatus === "APROBADO" && r.noempFirma
      ? `<img src="${CF_FIRMA_URL(r.noempFirma)}" alt="Firma" onerror="this.style.display='none'">`
      : "";

  const generales = [
    ["Descripción de producto", descProducto],
    ["Procedencia", c.CER_paisOrigen || "México"],
    ["Fecha de Fabricación", cfFecha(ins?.INS_fechaFabricacion) || ""],
    ["Fecha de Emisión del CoA", cfFecha(c.CER_fechaEmision) || ""],
    ["Clave", c.CER_clave],
    ["Lote", c.CER_lote],
    ["Referencia", "Análisis: Especificación interna."],
    ["Fabricante", "KCM Prosede Planta Formulados"],
  ]
    .map(
      ([e, v]) =>
        `<tr><td class="coa-etq">${cfEsc(e)}</td><td>${f(v)}</td></tr>`,
    )
    .join("");

  let bloqueDefectos = "";
  if (defectos.length) {
    const agr = {};
    defectos.forEach((d) => (agr[d.atributo] ??= []).push(d.nombre));
    bloqueDefectos = `
      <table class="coa-tabla coa-defectos">
        <tr><th colspan="2" class="text-start">Defectos detectados</th></tr>
        ${Object.entries(agr)
          .map(
            ([a, l]) =>
              `<tr><td class="coa-etq" style="width:22%">${cfEsc(a.charAt(0) + a.slice(1).toLowerCase())}</td>
               <td class="text-start">${cfEsc(l.join(" · "))}</td></tr>`,
          )
          .join("")}
      </table>`;
  }

  const filasMO = mo.length
    ? mo
        .map((m) => {
          const res =
            m.tipo === "RECUENTO" && m.unidad
              ? `${f(m.resultado)} ${cfEsc(m.unidad)}`
              : f(m.resultado);
          const it = m.tipo === "AUSENCIA" ? "coa-italic" : "";
          return `<tr><td class="text-start ${it}">${cfEsc(m.nombre)}</td><td>${res}</td></tr>`;
        })
        .join("")
    : `<tr><td class="text-start">Sin resultados capturados</td><td></td></tr>`;

  cfQ("#certHoja").innerHTML = `
    <div class="coa">
      <div class="coa-header">
        <img src="${CF_LOGO_URL}" alt="KCM" onerror="this.style.display='none'">
        
      </div>
      <div class="coa-titulo">CERTIFICADO DE ANÁLISIS</div>

      <table class="coa-tabla coa-generales">${generales}</table>

      <div class="coa-seccion">CONTROL FÍSICO-QUÍMICO</div>
      <table class="coa-tabla">
        <tr><th style="width:29%">Característica</th><th>Resultado</th>
            <th style="width:17%">Unidades</th><th style="width:26%">Evaluación</th></tr>
        <tr><td class="text-start">Aspecto</td><td>${f(fis?.FIS_aspecto)}</td><td></td><td></td></tr>
        <tr><td class="text-start">Color</td><td>${f(fis?.FIS_color)}</td><td></td><td></td></tr>
        <tr><td class="text-start">Olor</td><td>${f(fis?.FIS_olor)}</td><td></td><td></td></tr>
        <tr><td class="text-start">Densidad</td><td>${f(fis?.FIS_densidad)}</td>
            <td>${uni("DENSIDAD", "g/mL")}</td><td>${veredictoCel(ev.DENSIDAD)}</td></tr>
        <tr><td class="text-start">Viscosidad</td><td>${f(fis?.FIS_viscosidad)}</td>
            <td>${uni("VISCOSIDAD", "cps")}</td><td>${veredictoCel(ev.VISCOSIDAD)}</td></tr>
        <tr><td class="text-start">pH</td><td>${f(fis?.FIS_ph)}</td>
            <td>${uni("PH", "")}</td><td>${veredictoCel(ev.PH)}</td></tr>
      </table>

      <div class="coa-seccion">INFORMACIÓN DE ATRIBUTOS</div>
      <table class="coa-tabla">
        <tr><th style="width:29%">Atributo</th><th>Resultado</th></tr>
        <tr><td class="text-start">Seguridad</td><td>${f(ins?.INS_seguridad)}</td></tr>
        <tr><td class="text-start">Desempeño</td><td>${f(ins?.INS_desempeno)}</td></tr>
        <tr><td class="text-start">Apariencia</td><td>${f(ins?.INS_apariencia)}</td></tr>
      </table>
      ${bloqueDefectos}

      <div class="coa-seccion">RECUENTO MICROBIOLÓGICO</div>
      <table class="coa-tabla">
        <tr><th style="width:60%">Determinación</th><th>Resultado</th></tr>
        ${filasMO}
      </table>

    

      <div class="coa-seccion">CONCLUSIÓN</div>
      <p class="coa-conclusion">
        El producto <b class="coa-veredicto">${noCumple ? "No cumple" : "cumple"}</b> con la especificación
      </p>
      ${
        c.CER_observacionesGT
          ? `<p class="coa-obs"><b>Observaciones de Gerencia Técnica:</b> ${cfEsc(c.CER_observacionesGT)}</p>`
          : ""
      }

      <div class="coa-pie">
        <div class="coa-firma">
          <div class="coa-aprobo">Apróbo</div>
          ${firmaImg}
          <div class="coa-linea">
              ${f(c.CER_nombreGerente)}<br>
              <span class="coa-cargo">Gerente Técnico</span>
            </div>
          </div>
      </div>
    </div>`;

  // en cfPreviewCert, después de asignar cfQ("#certHoja").innerHTML = ...
  const infoPalets = palets.length
    ? `<div class="alert alert-light border mt-3 mb-0" style="font-size:.8rem;">
       <b><i class="fa-solid fa-layer-group me-1"></i> Palets amparados</b>
       <span class="text-muted">(referencia interna, no aparece en el PDF)</span>
       <div class="mt-2">
         ${palets
           .map(
             (p) =>
               `<span class="cf-badge ${
                 p.estatus === "LIBERADO"
                   ? "cf-autorizado"
                   : p.estatus === "RECHAZADO"
                     ? "cf-rechazado"
                     : "cf-captura"
               } me-1 mb-1">
                  ${String(p.palet).padStart(3, "0")} · ${cfEsc(p.cajas)} cajas · ${cfEsc(p.estatus)}
                </span>`,
           )
           .join("")}
       </div>
     </div>`
    : "";

  // cfQ("#certHoja").insertAdjacentHTML("afterend", infoPalets);
  // Limpia el bloque anterior antes de volver a pintarlo
  document.querySelector("#certHoja + .cf-info-palets")?.remove();
  if (infoPalets) {
    cfQ("#certHoja").insertAdjacentHTML(
      "afterend",
      infoPalets.replace('class="alert', 'class="cf-info-palets alert'),
    );
  }

  let footer = `<button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>`;
  if (validarGT) {
    footer += `<button class="btn btn-danger" onclick="cfValidarGT(${id}, false)">Rechazar</button>
               <button class="btn btn-success" onclick="cfValidarGT(${id}, true)">Confirmar y firmar</button>`;
  } else if (c.CER_estatus === "APROBADO") {
    footer += `<a class="btn btn-primary" target="_blank" href="${CF_PDF}?id=${id}">
                 <i class="fa-regular fa-file-pdf me-1"></i> Abrir PDF</a>`;
  }
  cfQ("#certFooter").innerHTML = footer;
  bootstrap.Modal.getOrCreateInstance(cfQ("#modalCert")).show();
}

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

  cfgInit();
  libInit();
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
      if (target === "#tabFolios") cfCargarGrupos();
      if (target === "#tabCerts") cfCargarCerts();
    });
  });

function cfVeredicto(valor, p) {
  if (valor === "" || valor === null || valor === undefined || !p) return "";
  // Acepta "<1", ">10", "1.5" — se queda con el número
  const v = parseFloat(String(valor).replace(/[<>≤≥\s]/g, ""));
  if (isNaN(v)) return "";
  const { minimo: min, objetivo: obj, maximo: max } = p;
  if (min == null && max == null && obj == null) return "";
  if (min != null && v < min) return "No cumple";
  if (max != null && v > max) return "No cumple";
  if (obj != null && Math.abs(v - obj) < 0.00001) return "Cumple";
  if (obj != null && v < obj) return "Cumple";
  if (obj != null && v > obj) return "Cumple";
  return "Cumple";
}

function cfEvaluarFQ(variable, idInput) {
  const p = CF_CAT?.parametros?.[variable];
  const el = cfQ("#" + idInput);
  const out = cfQ("#eval_" + variable);
  if (!el || !out) return;
  const r = cfVeredicto(el.value, p);
  const cls = !r
    ? "cf-bloqueado"
    : r === "No cumple"
      ? "cf-rechazado"
      : "cf-autorizado";
  out.innerHTML = r
    ? `<span class="cf-badge ${cls}">${r}</span>`
    : `<span class="text-muted" style="font-size:.75rem;">Sin parámetros configurados</span>`;
}
