// =============================================================
// js/certificados_liberacion.js — v10 (dos banderas + excluidos)
// =============================================================

let LIB_FILTRO = { estatus: null, desde: null, texto: "" };

const LIB_CLS = {
  LIBERADO: "cf-autorizado",
  PARCIAL: "cf-captura",
  "EN PROCESO": "cf-enviado",
  "SIN CERTIFICAR": "cf-bloqueado",
  RECHAZADO: "cf-rechazado",
};

const LIB_ICO = {
  LIBERADO: "fa-circle-check",
  PARCIAL: "fa-circle-half-stroke",
  "EN PROCESO": "fa-hourglass-half",
  "SIN CERTIFICAR": "fa-circle-minus",
  RECHAZADO: "fa-circle-xmark",
};

async function libCargar() {
  const cont = cfQ("#libContenido");
  cont.innerHTML = `<div class="text-center py-4 text-muted">
      <i class="fa-solid fa-circle-notch fa-spin"></i> Cargando…</div>`;

  const r = await cfApi({ accion: "folios_liberacion", ...LIB_FILTRO });
  if (!r.ok) {
    cont.innerHTML = `<div class="alert alert-danger">${cfEsc(r.error)}</div>`;
    return;
  }

  const s = r.resumen;

  const tarjeta = (etiqueta, valor, estatus, clase) => `
    <div class="lib-card ${clase} ${LIB_FILTRO.estatus === estatus ? "activa" : ""}"
         onclick="libFiltrar(${estatus === null ? "null" : `'${estatus}'`})">
      <div class="lib-card-num">${valor}</div>
      <div class="lib-card-txt">${etiqueta}</div>
    </div>`;

  const resumen = `
    <div class="lib-resumen">
      ${tarjeta("Todos los folios", s.folios, null, "")}
      ${tarjeta("Liberados", s.liberados, "LIBERADO", "ok")}
      ${tarjeta("Parciales", s.parciales, "PARCIAL", "warn")}
      ${tarjeta("En proceso", s.proceso, "EN PROCESO", "info")}
      ${tarjeta("Sin certificar", s.sinCertificar, "SIN CERTIFICAR", "gris")}
      ${tarjeta("Rechazados", s.rechazados, "RECHAZADO", "err")}
    </div>
    ${
      s.conExcluidos
        ? `<div class="alert alert-warning py-2 mt-3 mb-0" style="font-size:.85rem;">
             <i class="fa-solid fa-circle-exclamation me-1"></i>
             <b>${s.conExcluidos} folio(s)</b> traen palets excluidos del certificado.
           </div>`
        : ""
    }`;

  const filas = r.items.length
    ? r.items
        .map((f) => {
          const cls = LIB_CLS[f.EstatusFolio] || "cf-bloqueado";
          const ico = LIB_ICO[f.EstatusFolio] || "fa-circle";

          // El avance se mide contra los palets que sí entraron al certificado
          const base = f.PaletsALiberar || f.TotalPalets;
          const barra = `
            <div class="lib-barra" title="${f.Liberados} de ${base} palets liberados">
              <div class="lib-barra-fill" style="width:${f.avance}%"></div>
              <span>${f.Liberados}/${base}</span>
            </div>`;

          const detalle = [
            f.EnProceso ? `${f.EnProceso} en proceso` : "",
            f.RechazadosGerencia ? `${f.RechazadosGerencia} rechazado(s)` : "",
            f.RechazadosOrigen
              ? `${f.RechazadosOrigen} rechazado(s) en origen`
              : "",
          ]
            .filter(Boolean)
            .join(" · ");

          // Columna de excluidos, con su motivo
          const excluidos = f.PaletsExcluidos
            ? `<span class="cf-badge cf-captura" title="${cfEsc(f.MotivoExclusion || "")}">
                 <i class="fa-solid fa-circle-exclamation"></i> ${f.PaletsExcluidos}
               </span>
               ${
                 f.MotivoExclusion
                   ? `<small class="text-muted d-block mt-1 lib-motivo">${cfEsc(f.MotivoExclusion)}</small>`
                   : ""
               }`
            : `<span class="text-muted">—</span>`;

          return `<tr>
            <td>${cfEsc(f.fechaProduccion)}</td>
            <td><strong>${cfEsc(f.Folio)}</strong></td>
            <td><code>${cfEsc(f.Clave)}</code></td>
            <td class="text-start">${cfEsc(f.Producto || "")}</td>
            <td>${cfEsc(f.Maquina || "")} · T${cfEsc(f.Turno)}</td>
            <td><b>${f.PaletsALiberar}</b> <small class="text-muted">de ${f.TotalPalets}</small></td>
            <td style="min-width:150px;">${barra}
              ${detalle ? `<small class="text-muted d-block mt-1">${cfEsc(detalle)}</small>` : ""}
            </td>
            <td style="min-width:130px;">${excluidos}</td>
            <td>${f.CajasLiberadas} <small class="text-muted">/ ${f.TotalCajas}</small></td>
            <td>
              <span class="cf-badge ${cls}"><i class="fa-solid ${ico}"></i> ${cfEsc(f.EstatusFolio)}</span>
              ${
                f.ultimaLiberacion
                  ? `<br><small class="text-muted">${cfEsc(f.ultimaLiberacion)}</small>`
                  : ""
              }
            </td>
            <td>${
              f.IdCertificado
                ? `<button class="btn btn-sm btn-outline-primary"
                     onclick="cfPreviewCert(${f.IdCertificado})">
                     <i class="fa-solid fa-eye"></i> #${f.IdCertificado}</button>`
                : "—"
            }</td>
          </tr>`;
        })
        .join("")
    : `<tr><td colspan="11">${cfVacio("Sin folios que coincidan con el filtro.")}</td></tr>`;

  cont.innerHTML = `
    ${resumen}
    <div class="table-responsive mt-3" style="max-height:520px;">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>FECHA</th><th>FOLIO</th><th>CLAVE</th><th>PRODUCTO</th>
            <th>MÁQUINA</th><th>A LIBERAR</th><th>LIBERADOS</th>
            <th>EXCLUIDOS</th><th>CAJAS</th><th>ESTATUS</th><th>CERT.</th>
          </tr>
        </thead>
        <tbody>${filas}</tbody>
      </table>
    </div>`;
}

function libFiltrar(estatus) {
  LIB_FILTRO.estatus = LIB_FILTRO.estatus === estatus ? null : estatus;
  libCargar();
}

function libInit() {
  const puede = (CF_PERFIL.roles || []).some((r) =>
    ["CONSULTA_LIBERACION", "GERENTE", "CONFIGURADOR"].includes(r),
  );
  const tab = cfQ("#tabBtnLiberacion");
  if (tab) tab.classList.toggle("d-none", !puede);
  if (!puede) return;

  const bt = document.getElementById("buscarLiberacion");
  if (bt) {
    let t;
    bt.addEventListener("input", () => {
      clearTimeout(t);
      t = setTimeout(() => {
        LIB_FILTRO.texto = bt.value.trim();
        libCargar();
      }, 350);
    });
  }

  const fd = document.getElementById("libDesde");
  if (fd)
    fd.addEventListener("change", () => {
      LIB_FILTRO.desde = fd.value || null;
      libCargar();
    });

  libCargar();
}
