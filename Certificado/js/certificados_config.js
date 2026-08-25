// =============================================================
// PARTE 3 — js/certificados_config.js
// Pantalla de Configuraciones (rol CONFIGURADOR) + bitácora.
// Cárgalo DESPUÉS de certificados.js
// =============================================================

const CFG_SECCIONES = {
  parametros: {
    titulo: "Parámetros por clave",
    icono: "fa-sliders",
    ayuda:
      "Mínimo, objetivo y máximo de cada variable fisicoquímica. Sin esto, el modal avisa que no hay datos para validar.",
    cols: [
      { f: "clave", t: "Clave", tipo: "text", req: true },
      {
        f: "variable",
        t: "Variable",
        tipo: "select",
        ops: ["DENSIDAD", "VISCOSIDAD", "PH"],
        req: true,
      },
      { f: "unidad", t: "Unidad", tipo: "text", ph: "g/mL, cps, pH" },
      { f: "minimo", t: "Mínimo", tipo: "number", step: "0.0001" },
      { f: "objetivo", t: "Objetivo", tipo: "number", step: "0.0001" },
      { f: "maximo", t: "Máximo", tipo: "number", step: "0.0001" },
      { f: "metodo", t: "Método", tipo: "text", ph: "TTM-00557" },
    ],
  },
  organolepticas: {
    titulo: "Especificaciones organolépticas",
    icono: "fa-eye",
    ayuda:
      "Texto de especificación por clave: qué aspecto, color y olor debe tener el producto.",
    cols: [
      { f: "clave", t: "Clave", tipo: "text", req: true },
      {
        f: "tipo",
        t: "Tipo",
        tipo: "select",
        ops: ["ASPECTO", "COLOR", "OLOR"],
        req: true,
      },
      {
        f: "especificacion",
        t: "Especificación",
        tipo: "text",
        req: true,
        ancho: true,
        ph: "Líquido viscoso, cristalino, color rojo",
      },
    ],
  },
  defectos: {
    titulo: "Defectos por atributo",
    icono: "fa-triangle-exclamation",
    ayuda:
      "Se muestran como checkbox en Inspección cuando el atributo se marca como no cumple.",
    cols: [
      {
        f: "atributo",
        t: "Atributo",
        tipo: "select",
        ops: ["SEGURIDAD", "DESEMPENO", "APARIENCIA"],
        req: true,
      },
      { f: "nombre", t: "Defecto", tipo: "text", req: true, ancho: true },
      // { f: "orden", t: "Orden", tipo: "number", step: "1" },
    ],
  },
  mo: {
    titulo: "Microorganismos objetables (MO)",
    icono: "fa-bacterium",
    ayuda:
      "Lista que se captura en Microbiología. RECUENTO pide un número; AUSENCIA pide Ausente/Presente.",
    cols: [
      {
        f: "nombre",
        t: "Microorganismo",
        tipo: "text",
        req: true,
        ancho: true,
      },
      {
        f: "tipo",
        t: "Tipo",
        tipo: "select",
        ops: ["AUSENCIA", "RECUENTO"],
        req: true,
      },
      {
        f: "especificacion",
        t: "Especificación",
        tipo: "text",
        ph: "< 100 · Ausencia",
      },
      { f: "unidad", t: "Unidad", tipo: "text", ph: "UFC/g" },
      { f: "metodo", t: "Técnica", tipo: "text", ph: "TTM-00554" },
      // { f: "orden", t: "Orden", tipo: "number", step: "1" },
    ],
  },
  opciones: {
    titulo: "Opciones de resultado",
    icono: "fa-list-check",
    ayuda:
      "Valores que el técnico puede elegir. Marcar 'Es falla' hace que esa opción despliegue defectos o cuente como incumplimiento.",
    cols: [
      {
        f: "tipo",
        t: "Tipo",
        tipo: "select",
        ops: ["ATRIBUTO", "ASPECTO", "COLOR", "OLOR", "MO"],
        req: true,
      },
      { f: "valor", t: "Valor", tipo: "text", req: true },
      { f: "esFalla", t: "Es falla", tipo: "check" },
      // { f: "orden", t: "Orden", tipo: "number", step: "1" },
    ],
  },
  perfiles: {
    titulo: "Roles y permisos",
    icono: "fa-user-shield",
    ayuda:
      "Quién puede capturar, autorizar, validar y configurar. Un IBM puede tener varios roles.",
    cols: [
      { f: "ibm", t: "IBM", tipo: "text", req: true },
      { f: "nombre", t: "Nombre", tipo: "text", ancho: true },
      {
        f: "noemp",
        t: "No. empleado",
        tipo: "text",
        ph: "para la firma digital",
      },
      {
        f: "rol",
        t: "Rol",
        tipo: "select",
        req: true,
        ops: [
          "INSPECCION",
          "SUP_INSPECCION",
          "FISICOQUIMICO",
          "SUP_FISICOQUIMICO",
          "MICROBIOLOGIA",
          "SUP_MICROBIOLOGIA",
          "GERENTE",
          "CONFIGURADOR",
        ],
      },
    ],
  },
};

let CFG_ACTUAL = "parametros";

// ---------- Navegación entre catálogos ----------
function cfgCambiar(seccion) {
  CFG_ACTUAL = seccion;
  document
    .querySelectorAll("#cfgMenu .list-group-item")
    .forEach((b) => b.classList.toggle("active", b.dataset.sec === seccion));
  cfgCargar();
}

// ---------- Listado ----------
async function cfgCargar() {
  const s = CFG_SECCIONES[CFG_ACTUAL];
  const cont = cfQ("#cfgContenido");
  cont.innerHTML = `<div class="text-center py-4 text-muted">
      <i class="fa-solid fa-circle-notch fa-spin"></i> Cargando…</div>`;

  const r = await cfApi({ accion: "config_listar", catalogo: CFG_ACTUAL });
  if (!r.ok) {
    cont.innerHTML = `<div class="alert alert-danger">${cfEsc(r.error)}</div>`;
    return;
  }

  const ths = s.cols.map((c) => `<th>${cfEsc(c.t)}</th>`).join("");
  const trazable = CFG_ACTUAL !== "perfiles";

  const filas = r.items.length
    ? r.items
        .map((it) => {
          const tds = s.cols
            .map((c) => {
              let v = it[c.f];
              if (c.tipo === "check")
                v = +v ? `<i class="fa-solid fa-check text-danger"></i>` : "—";
              return `<td>${v === null || v === "" ? "—" : cfEsc(v)}</td>`;
            })
            .join("");
          const traza = trazable
            ? `<td><small class="text-muted">${cfEsc(it.quien || "—")}<br>${cfEsc(it.cuando || "")}</small></td>`
            : "";
          return `<tr>${tds}${traza}
            <td class="text-nowrap">
              <button class="btn btn-sm btn-outline-primary" onclick='cfgEditar(${JSON.stringify(it)})'>
                <i class="fa-solid fa-pen"></i></button>
              <button class="btn btn-sm btn-outline-danger" onclick="cfgEliminar(${it.id})">
                <i class="fa-solid fa-trash"></i></button>
            </td></tr>`;
        })
        .join("")
    : `<tr><td colspan="${s.cols.length + (trazable ? 2 : 1)}">
         ${cfVacio("Sin registros configurados.")}</td></tr>`;

  cont.innerHTML = `
    <div class="cf-toolbar">
      <div>
        <div class="cf-card-title mb-1"><i class="fa-solid ${s.icono}"></i> ${cfEsc(s.titulo)}</div>
        <small class="text-muted">${cfEsc(s.ayuda)}</small>
      </div>
      <button class="btn btn-primary btn-sm" onclick="cfgEditar(null)">
        <i class="fa-solid fa-plus"></i> Agregar
      </button>
    </div>
    <div class="table-responsive" style="max-height:460px;">
      <table class="table table-sm table-bordered align-middle text-center mb-0">
        <thead class="table-dark">
          <tr>${ths}${trazable ? "<th>Configuró</th>" : ""}<th style="width:100px;">Acciones</th></tr>
        </thead>
        <tbody>${filas}</tbody>
      </table>
    </div>`;
}

// ---------- Alta / edición ----------
function cfgEditar(item) {
  const s = CFG_SECCIONES[CFG_ACTUAL];
  const campos = s.cols
    .map((c) => {
      const val = item ? (item[c.f] ?? "") : "";
      const ancho = c.ancho ? "col-12" : "col-md-6";
      let input;
      if (c.tipo === "select") {
        input = `<select class="form-select" id="cfg_${c.f}">
            <option value="">-- Selecciona --</option>
            ${c.ops.map((o) => `<option ${val === o ? "selected" : ""}>${o}</option>`).join("")}
          </select>`;
      } else if (c.tipo === "check") {
        input = `<div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="cfg_${c.f}" ${+val ? "checked" : ""}>
            <label class="form-check-label" for="cfg_${c.f}">Marcar como falla</label>
          </div>`;
      } else {
        input = `<input type="${c.tipo}" class="form-control" id="cfg_${c.f}"
                   value="${cfEsc(val)}" ${c.step ? `step="${c.step}"` : ""}
                   placeholder="${cfEsc(c.ph || "")}">`;
      }
      return `<div class="${ancho}">
          <label class="form-label">${cfEsc(c.t)}${c.req ? ' <span class="text-danger">*</span>' : ""}</label>
          ${input}
        </div>`;
    })
    .join("");

  cfQ("#cfgFormTitulo").textContent =
    (item ? "Editar" : "Agregar") + " — " + s.titulo;
  cfQ("#cfgFormBody").innerHTML = `<div class="row g-3">${campos}</div>`;
  cfQ("#cfgFormId").value = item?.id ?? "";
  bootstrap.Modal.getOrCreateInstance(cfQ("#modalConfig")).show();
}

async function cfgGuardar() {
  const s = CFG_SECCIONES[CFG_ACTUAL];
  const datos = {};
  for (const c of s.cols) {
    const el = cfQ("#cfg_" + c.f);
    datos[c.f] = c.tipo === "check" ? (el.checked ? 1 : 0) : el.value.trim();
    if (c.req && !datos[c.f])
      return cfSwal({
        title: "Falta un dato",
        text: `${c.t} es obligatorio.`,
        icon: "warning",
      });
  }

  const id = cfQ("#cfgFormId").value;
  const r = await cfApi({
    accion: "config_guardar",
    catalogo: CFG_ACTUAL,
    id: id || 0,
    datos,
  });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });

  bootstrap.Modal.getInstance(cfQ("#modalConfig"))?.hide();
  cfSwal({
    title: "Guardado",
    icon: "success",
    timer: 1400,
    showConfirmButton: false,
  });
  cfgCargar();
}

async function cfgEliminar(id) {
  const c = await cfSwal({
    title: "¿Eliminar el registro?",
    text: "Se dará de baja; los certificados ya emitidos conservan lo que capturaron.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Eliminar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#B02A2A",
  });
  if (!c.isConfirmed) return;

  const r = await cfApi({
    accion: "config_eliminar",
    catalogo: CFG_ACTUAL,
    id,
  });
  if (!r.ok) return cfSwal({ title: "Error", text: r.error, icon: "error" });
  cfgCargar();
}

// ---------- Estado de configuración por clave ----------
async function cfgClaves() {
  const cont = cfQ("#cfgContenido");
  const r = await cfApi({ accion: "config_claves" });
  if (!r.ok) {
    cont.innerHTML = `<div class="alert alert-danger">${cfEsc(r.error)}</div>`;
    return;
  }

  const filas = r.items
    .map((c) => {
      const okPar = c.parametros >= 3;
      const okOrg = c.organolepticas >= 3;
      const estado =
        okPar && okOrg
          ? `<span class="cf-badge cf-autorizado">Configurada</span>`
          : `<span class="cf-badge cf-captura">Incompleta</span>`;
      return `<tr>
        <td><code>${cfEsc(c.clave)}</code></td>
        <td class="text-start">${cfEsc(c.producto)}</td>
        <td>${cfEsc(c.categoria || "—")}</td>
        <td>${c.parametros} / 3</td>
        <td>${c.organolepticas} / 3</td>
        <td>${estado}</td>
      </tr>`;
    })
    .join("");

  cont.innerHTML = `
    <div class="cf-toolbar">
      <div>
        <div class="cf-card-title mb-1"><i class="fa-solid fa-clipboard-check"></i> Estado por clave</div>
        <small class="text-muted">Qué claves ya tienen sus parámetros y especificaciones cargados.</small>
      </div>
    </div>
    <div class="table-responsive" style="max-height:460px;">
      <table class="table table-sm table-bordered align-middle text-center mb-0">
        <thead class="table-dark">
          <tr><th>CLAVE</th><th>PRODUCTO</th><th>CATEGORÍA</th>
              <th>PARÁMETROS</th><th>ORGANOLÉPTICAS</th><th>ESTADO</th></tr>
        </thead>
        <tbody>${filas || `<tr><td colspan="6">${cfVacio("Sin claves.")}</td></tr>`}</tbody>
      </table>
    </div>`;
}

// ---------- Bitácora ----------
async function cfgBitacora(modulo = null) {
  const cont = cfQ("#cfgContenido");
  const r = await cfApi({ accion: "bitacora", modulo });
  if (!r.ok) {
    cont.innerHTML = `<div class="alert alert-danger">${cfEsc(r.error)}</div>`;
    return;
  }

  const color = {
    INICIAR: "cf-enviado",
    CAPTURA: "cf-captura",
    ENVIA: "cf-enviado",
    AUTORIZA: "cf-autorizado",
    VALIDA: "cf-autorizado",
    RECHAZA: "cf-rechazado",
    CONFIG_ALTA: "cf-gt",
    CONFIG_EDITA: "cf-gt",
    CONFIG_BAJA: "cf-rechazado",
  };

  const filas = r.items
    .map(
      (b) => `<tr>
        <td>${cfEsc(b.cuando)}</td>
        <td>${b.idCertificado ? "#" + b.idCertificado : "—"}</td>
        <td><span class="cf-badge ${color[b.accion] || "cf-bloqueado"}">${cfEsc(b.accion)}</span></td>
        <td>${cfEsc(b.etapa || "—")}</td>
        <td class="text-start"><small>${cfEsc(b.detalle || "—")}</small></td>
        <td>${cfEsc(b.quien || b.ibm)}</td>
      </tr>`,
    )
    .join("");

  cont.innerHTML = `
    <div class="cf-toolbar">
      <div>
        <div class="cf-card-title mb-1"><i class="fa-solid fa-clock-rotate-left"></i> Bitácora</div>
        <small class="text-muted">Quién capturó, autorizó, validó y configuró.</small>
      </div>
      <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-secondary" onclick="cfgBitacora(null)">Todo</button>
        <button class="btn btn-outline-secondary" onclick="cfgBitacora('CERTIFICADO')">Certificados</button>
        <button class="btn btn-outline-secondary" onclick="cfgBitacora('CONFIG')">Configuración</button>
      </div>
    </div>
    <div class="table-responsive" style="max-height:460px;">
      <table class="table table-sm table-bordered align-middle text-center mb-0">
        <thead class="table-dark">
          <tr><th>FECHA</th><th>CERT.</th><th>ACCIÓN</th><th>ETAPA</th><th>DETALLE</th><th>QUIÉN</th></tr>
        </thead>
        <tbody>${filas || `<tr><td colspan="6">${cfVacio("Sin movimientos.")}</td></tr>`}</tbody>
      </table>
    </div>`;
}

async function cfgAgrupado(tipo) {
  const cont = cfQ("#cfgContenido");
  cont.innerHTML = `<div class="text-center py-4 text-muted">
      <i class="fa-solid fa-circle-notch fa-spin"></i> Cargando…</div>`;

  const r = await cfApi({ accion: "config_agrupado", tipo });
  if (!r.ok) {
    cont.innerHTML = `<div class="alert alert-danger">${cfEsc(r.error)}</div>`;
    return;
  }

  const esParam = tipo === "parametros";
  const titulo = esParam
    ? "Parámetros por clave"
    : "Especificaciones organolépticas";
  const icono = esParam ? "fa-sliders" : "fa-eye";
  const ayuda = esParam
    ? "Cada clave necesita sus tres variables: densidad, viscosidad y pH."
    : "Cada clave necesita su texto de aspecto, color y olor.";

  // Aviso de claves sin nada configurado
  // let aviso = "";
  // if (r.sinConfig.length) {
  //   aviso = `<div class="alert alert-warning py-2">
  //       <i class="fa-solid fa-triangle-exclamation me-1"></i>
  //       <b>${r.sinConfig.length} clave(s) con producción y sin configurar:</b>
  //       ${r.sinConfig.map((k) => `<code class="me-1">${cfEsc(k)}</code>`).join("")}
  //     </div>`;
  // }

  let aviso = "";
  if (r.sinConfig.length) {
    aviso = `<div class="alert alert-warning py-2">
        <div class="mb-2">
          <i class="fa-solid fa-triangle-exclamation me-1"></i>
          <b>${r.sinConfig.length} clave(s) con producción y sin configurar</b>
        </div>
        <div class="d-flex flex-wrap gap-2">
          ${r.sinConfig
            .map(
              (k) => `<button class="btn btn-sm btn-outline-dark"
                        onclick='cfgNuevoDe("${tipo}","${cfEsc(k)}")'>
                        <i class="fa-solid fa-plus me-1"></i>${cfEsc(k)}
                      </button>`,
            )
            .join("")}
        </div>
      </div>`;
  }

  const tarjetas = r.grupos.length
    ? r.grupos
        .map((g) => {
          // Un renglón por variable requerida, aunque no exista
          const filas = r.requeridas
            .map((req) => {
              const items = g.items.filter(
                (i) => String(i.variable).toUpperCase() === req,
              );
              if (!items.length) {
                return `<tr class="table-warning">
                    <td class="text-start"><b>${cfEsc(req)}</b></td>
                    <td colspan="4" class="text-muted">
                      <i class="fa-solid fa-triangle-exclamation me-1"></i> Sin configurar
                    </td>
                    <td><button class="btn btn-sm btn-primary"
                          onclick='cfgNuevoDe("${tipo}","${cfEsc(g.clave)}","${req}")'>
                          <i class="fa-solid fa-plus"></i></button></td>
                  </tr>`;
              }
              return items
                .map((i) =>
                  esParam
                    ? `<tr>
                        <td class="text-start"><b>${cfEsc(i.variable)}</b></td>
                        <td>${cfEsc(i.minimo ?? "—")}</td>
                        <td class="table-light"><b>${cfEsc(i.objetivo ?? "—")}</b></td>
                        <td>${cfEsc(i.maximo ?? "—")}</td>
                        <td>${cfEsc(i.unidad ?? "—")}<br>
                            <small class="text-muted">${cfEsc(i.metodo ?? "")}</small></td>
                        <td class="text-nowrap">
                          <button class="btn btn-sm btn-outline-primary" onclick='cfgEditarDe("${tipo}", ${JSON.stringify(i)})'>
                            <i class="fa-solid fa-pen"></i></button>
                          <button class="btn btn-sm btn-outline-danger" onclick='cfgEliminarDe("${tipo}", ${i.id})'>
                            <i class="fa-solid fa-trash"></i></button>
                        </td>
                      </tr>`
                    : `<tr>
                        <td class="text-start"><b>${cfEsc(i.variable)}</b></td>
                        <td colspan="4" class="text-start">${cfEsc(i.especificacion)}</td>
                        <td class="text-nowrap">
                          <button class="btn btn-sm btn-outline-primary" onclick='cfgEditarDe("${tipo}", ${JSON.stringify(i)})'>
                            <i class="fa-solid fa-pen"></i></button>
                          <button class="btn btn-sm btn-outline-danger" onclick='cfgEliminarDe("${tipo}", ${i.id})'>
                            <i class="fa-solid fa-trash"></i></button>
                        </td>
                      </tr>`,
                )
                .join("");
            })
            .join("");

          const encabezado = esParam
            ? `<tr><th class="text-start" style="width:20%">Variable</th>
                   <th>Mínimo</th><th>Objetivo</th><th>Máximo</th>
                   <th style="width:18%">Unidad / Método</th><th style="width:100px"></th></tr>`
            : `<tr><th class="text-start" style="width:20%">Tipo</th>
                   <th colspan="4" class="text-start">Especificación</th><th style="width:100px"></th></tr>`;

          return `<div class="cfg-grupo ${g.completo ? "" : "incompleto"}">
              <div class="cfg-grupo-head">
                <div>
                  <code class="cfg-clave">${cfEsc(g.clave)}</code>
                  <span class="cfg-producto">${cfEsc(g.producto || "")}</span>
                </div>
                <span class="cf-badge ${g.completo ? "cf-autorizado" : "cf-captura"}">
                  ${g.completo ? "Completa" : "Faltan: " + g.faltan.join(", ")}
                </span>
              </div>
              <table class="table table-sm table-bordered align-middle text-center mb-0">
                <thead class="table-dark">${encabezado}</thead>
                <tbody>${filas}</tbody>
              </table>
            </div>`;
        })
        .join("")
    : cfVacio("Sin claves configuradas todavía.");

  cont.innerHTML = `
    <div class="cf-toolbar">
      <div>
        <div class="cf-card-title mb-1"><i class="fa-solid ${icono}"></i> ${cfEsc(titulo)}</div>
        <small class="text-muted">${cfEsc(ayuda)}</small>
      </div>
      <button class="btn btn-primary btn-sm" onclick='cfgNuevoDe("${tipo}")'>
        <i class="fa-solid fa-plus"></i> Agregar
      </button>
    </div>
    ${aviso}
    <div style="max-height:460px; overflow-y:auto;">${tarjetas}</div>`;
}

// Alta con la clave y variable ya precargadas
function cfgNuevoDe(tipo, clave = "", variable = "") {
  CFG_ACTUAL = tipo;
  cfgEditar(null);
  setTimeout(() => {
    if (clave && cfQ("#cfg_clave")) cfQ("#cfg_clave").value = clave;
    if (variable) {
      const sel = cfQ("#cfg_variable") || cfQ("#cfg_tipo");
      if (sel) sel.value = variable;
    }
  }, 150);
}

function cfgEditarDe(tipo, item) {
  CFG_ACTUAL = tipo;
  cfgEditar(item);
}

async function cfgEliminarDe(tipo, id) {
  CFG_ACTUAL = tipo;
  await cfgEliminar(id);
  cfgAgrupado(tipo);
}

// Después de guardar, si estábamos en una vista agrupada, recárgala
const _cfgGuardar = cfgGuardar;
cfgGuardar = async function () {
  await _cfgGuardar();
  if (CFG_ACTUAL === "parametros" || CFG_ACTUAL === "organolepticas")
    cfgAgrupado(CFG_ACTUAL);
};

// ---------- Arranque: la pestaña solo existe para CONFIGURADOR ----------
function cfgInit() {
  const esConfig = (CF_PERFIL.roles || []).includes("CONFIGURADOR");
  const tab = cfQ("#tabBtnConfig");
  if (tab) tab.classList.toggle("d-none", !esConfig);
  if (!esConfig) return;

  // document.querySelectorAll("#cfgMenu .list-group-item").forEach((b) => {
  //   b.addEventListener("click", () => {
  //     const sec = b.dataset.sec;
  //     document
  //       .querySelectorAll("#cfgMenu .list-group-item")
  //       .forEach((x) => x.classList.toggle("active", x === b));
  //     if (sec === "claves") return cfgClaves();
  //     if (sec === "bitacora") return cfgBitacora();
  //     CFG_ACTUAL = sec;
  //     cfgCargar();
  //   });
  // });
  document.querySelectorAll("#cfgMenu .list-group-item").forEach((b) => {
    b.addEventListener("click", () => {
      const sec = b.dataset.sec;
      document
        .querySelectorAll("#cfgMenu .list-group-item")
        .forEach((x) => x.classList.toggle("active", x === b));
      if (sec === "claves") return cfgClaves();
      if (sec === "bitacora") return cfgBitacora();
      CFG_ACTUAL = sec;
      if (sec === "parametros" || sec === "organolepticas")
        return cfgAgrupado(sec);
      cfgCargar();
    });
  });
  cfgCargar();
}
