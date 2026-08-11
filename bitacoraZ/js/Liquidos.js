const lsQ = (s) => document.querySelector(s);
const lsQA = (s) => [...document.querySelectorAll(s)];
const LS_API = "php/bajadas_api.php";
const LS_IMPRIMIR = "../../../../MES/KCMes/zpl/imprimirl.php";
const LS_NBLOQUES = 4;

// 8 = 203 dpi, 12 = 300 dpi, 24 = 600 dpi
const LS_DPMM = 8;

// Recuperacion de datos
async function lsApi(payload) {
  const r = await fetch(LS_API, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });
  return r.json();
}

// Limpieza y normalizacion de datos
function lsEsc(s) {
  return String(s ?? "").replace(
    /[&<>"]/g,
    (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" })[c],
  );
}

// Recuperacion de idEncabezadoBitacora
function lsIdEnc() {
  const e = document.getElementById("folioenctext");
  return e ? (e.value ?? e.textContent ?? "").trim() : "";
}

// Recuperacion de turno de encabezado
function lsTurno() {
  const e = document.getElementById("turnoenctext");
  return e ? (e.value ?? e.textContent ?? "").trim() : "";
}

// Obtencion de datos para Presentaciones de scope global en bitacora
function lsOpcionesCatalogo() {
  const o = document.getElementById("presentacion1");
  return o && o.options.length ? o.innerHTML : "";
}

// Obtencion de claves usadas para no repetirlas en nuevas presentaciones
function lsClavesUsadas() {
  return lsQA("#paginaLiquidos .ls-bloque[data-fijado='1'] .ls-sel").map(
    (s) => s.value,
  );
}
function lsSincronizarSelects() {
  const usadas = new Set(lsClavesUsadas());
  lsQA("#paginaLiquidos .ls-bloque[data-fijado='0'] .ls-sel").forEach((sel) => {
    [...sel.options].forEach((op) => {
      if (!op.value) return;
      op.hidden = usadas.has(op.value);
      op.disabled = usadas.has(op.value);
    });
  });
}

// Construye los 4 bloques desde el template en Liquidos.php y rehidrata los que ya tengan bajadas
async function lsMontarBloques() {
  const cont = lsQ("#paginaLiquidos .ls-bloques");
  const tpl = document.getElementById("ls-tpl-bloque");
  // Inyeccion de opciones segun presentacion1
  const opciones = lsOpcionesCatalogo();
  cont.innerHTML = "";
  const bloques = [];
  // Renderizacion de presentaciones con opciones disponibles
  for (let i = 1; i <= LS_NBLOQUES; i++) {
    const nodo = tpl.content.firstElementChild.cloneNode(true);
    nodo.querySelector(".ls-bloque-num").textContent = "PRESENTACIÓN " + i;
    const sel = nodo.querySelector(".ls-sel");
    sel.innerHTML = '<option value=""> Elige un producto </option>' + opciones;
    // Inyeccion de acciones para bajadas y
    nodo
      .querySelector(".ls-bajar")
      .addEventListener("click", () => lsBajar(nodo));
    nodo
      .querySelector(".ls-cancelar")
      .addEventListener("click", () => lsCancelar(nodo));
    sel.addEventListener("focus", () => {
      if (sel.options.length <= 1) {
        sel.innerHTML =
          '<option value=""> Elige un producto </option>' +
          lsOpcionesCatalogo();
        lsSincronizarSelects();
      }
    });
    cont.appendChild(nodo);
    bloques.push(nodo);
  }
  //   lsSincronizarSelects();
  //   await lsRehidratar(bloques);

  lsSincronizarSelects();
  lsLoader(true);
  try {
    await lsRehidratar(bloques);
  } finally {
    lsLoader(false);
  }
}

// Recupera los productos ya capturados en este encabezado y los coloca en los bloques
async function lsRehidratar(bloques) {
  if (!lsIdEnc()) return;
  let r;
  try {
    r = await lsApi({ accion: "productos", idEnc: lsIdEnc() });
  } catch (e) {
    return;
  }
  if (!r || !r.ok || !r.items.length) return;

  r.items.slice(0, LS_NBLOQUES).forEach((p, idx) => {
    const bloque = bloques[idx];
    const sel = bloque.querySelector(".ls-sel");
    // asegura que la opción exista aunque el catálogo no la tenga cargada
    if (![...sel.options].some((o) => o.value === p.clave)) {
      const op = document.createElement("option");
      op.value = p.clave;
      op.textContent = p.producto || p.clave;
      sel.appendChild(op);
    }
    sel.value = p.clave;
    sel.disabled = true;
    bloque.dataset.fijado = "1";
    bloque.querySelector(".ls-bloque-prod").textContent = p.producto || p.clave;
    lsCargarTablaBloque(bloque, p.clave); // llena su tabla (y de paso AcumR/USTD)
  });
  lsSincronizarSelects();
}

// Bajada de un bloque
async function lsBajar(bloque) {
  const sel = bloque.querySelector(".ls-sel");
  const clave = sel.value;
  const producto = sel.options[sel.selectedIndex]?.textContent.trim() || "";
  const cajas = bloque.querySelector(".ls-cajas").value;

  if (!clave) {
    lsToast("Elige un producto en el bloque", "err");
    return;
  }
  if (!lsIdEnc()) {
    lsToast("Abre un turno antes de registrar", "err");
    return;
  }
  if (cajas === "" || +cajas < 0) {
    lsToast("Captura las cajas", "err");
    return;
  }

  const btn = bloque.querySelector(".ls-bajar");
  btn.disabled = true;
  try {
    const r = await lsApi({
      accion: "crear",
      clave,
      producto,
      idEnc: lsIdEnc(),
      turno: lsTurno(),
      cajas,
    });
    if (r.ok) {
      if (bloque.dataset.fijado === "0") {
        bloque.dataset.fijado = "1";
        sel.disabled = true;
        bloque.querySelector(".ls-bloque-prod").textContent = producto;
        lsSincronizarSelects();
      }
      bloque.querySelector(".ls-c-acum").textContent = r.bajada.acumR;
      bloque.querySelector(".ls-c-ustd").textContent = r.bajada.ustd;
      bloque.querySelector(".ls-cajas").value = "";
      lsToast(`Bajada #${r.bajada.id} (palet ${r.bajada.palet})`, "ok");
      lsCargarTablaBloque(bloque, clave);
    } else {
      lsToast("Error: " + r.error, "err");
    }
  } catch (e) {
    lsToast("No se pudo registrar", "err");
  } finally {
    btn.disabled = false;
  }
}

// Cancelar producto (borra sus bajadas) con confirmación
async function lsCancelar(bloque) {
  const sel = bloque.querySelector(".ls-sel");
  const clave = sel.value;
  const producto = bloque.querySelector(".ls-bloque-prod").textContent;
  if (bloque.dataset.fijado === "0" || !clave) {
    lsResetBloque(bloque);
    return;
  }

  const confirmar = await Swal.fire({
    title: "¿Estas seguro de querer eliminar este producto?",
    html: `Se eliminarán <b>TODOS</b> los registros asociados de esta clave <b>${lsEsc(producto)}</b> en este turno. Esta acción no se puede deshacer.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, cancelar",
    cancelButtonText: "No",
    confirmButtonColor: "#B02A2A",
  });
  if (!confirmar.isConfirmed) return;

  try {
    const r = await lsApi({ accion: "cancelar", clave, idEnc: lsIdEnc() });
    if (r.ok) {
      lsToast(`Producto cancelado (${r.eliminados} bajadas)`, "ok");
      lsResetBloque(bloque);
    } else {
      lsToast("Error: " + r.error, "err");
    }
  } catch (e) {
    lsToast("No se pudo cancelar", "err");
  }
}

// Deja el bloque libre otra vez
function lsResetBloque(bloque) {
  bloque.dataset.fijado = "0";
  const sel = bloque.querySelector(".ls-sel");
  sel.disabled = false;
  sel.value = "";
  bloque.querySelector(".ls-bloque-prod").textContent = "Sin producto";
  bloque.querySelector(".ls-c-acum").textContent = "–";
  bloque.querySelector(".ls-c-ustd").textContent = "–";
  bloque.querySelector(".ls-cajas").value = "";
  bloque.querySelector(".ls-tbody").innerHTML =
    `<tr><td colspan="7" class="ls-vacio">Sin bajadas.</td></tr>`;
  lsSincronizarSelects();
}

// Tabla de UN bloque (filtra la lista por su clave)
async function lsCargarTablaBloque(bloque, clave) {
  const tb = bloque.querySelector(".ls-tbody");
  try {
    const r = await lsApi({ accion: "listar", idEnc: lsIdEnc(), clave });
    if (!r.ok) {
      tb.innerHTML = `<tr><td colspan="7" class="ls-vacio">Error al cargar</td></tr>`;
      return;
    }
    const items = r.items.filter((b) => b.claveProducto === clave);
    if (!items.length) {
      tb.innerHTML = `<tr><td colspan="9" class="ls-vacio">Sin bajadas.</td></tr>`;
      return;
    }
    const ultima = items[0];
    const cAcum = bloque.querySelector(".ls-c-acum"),
      cUstd = bloque.querySelector(".ls-c-ustd");
    if (cAcum) cAcum.textContent = ultima.acumR ?? "–";
    if (cUstd) cUstd.textContent = ultima.ustd ?? "–";
    const ordenados = [...items].reverse();
    tb.innerHTML = ordenados
      .map(
        (b) => `
      <tr>
        <td class="ls-id">#${b.id}</td>
        <td class="ls-num">${lsEsc(b.palet)}</td>
        <td>${lsEsc(b.folio)}</td>        
        <td>${lsEsc(b.hora)}</td>
        <td class="ls-num">${lsEsc(b.cajas ?? "")}</td>
        <td class="ls-num">${lsEsc(b.piezas ?? "")}</td>
        <td class="ls-num">${lsEsc(b.acumR ?? "")}</td>
        <td class="ls-num">${lsEsc(b.ustd ?? "")}</td>
        <td><div class="ls-acc">
          <button class="ls-btn ls-ghost" onclick="lsPrevisualizar(${b.id})">Ver</button>
          <button class="ls-btn ls-ghost ls-primary" onclick="lsImprimir(${b.id})">Imprimir</button>
        </div></td>
      </tr>`,
      )
      .join("");
  } catch (e) {
    tb.innerHTML = `<tr><td colspan="7" class="ls-vacio">No se pudo conectar</td></tr>`;
  }
}

// Al cargar/refrescar: repuebla cada bloque ya fijado con su tabla
async function lsCargarTodo() {
  const r = await lsApi({ accion: "listar", idEnc: lsIdEnc() });
  if (!r.ok) return;
  lsQA("#paginaLiquidos .ls-bloque[data-fijado='1']").forEach((bloque) => {
    const clave = bloque.querySelector(".ls-sel").value;
    lsCargarTablaBloque(bloque, clave);
  });
}

// ====== ROTACIÓN 90° POR IMAGEN ======
async function lsEtiquetaRotada(id) {
  const r = await lsApi({ accion: "zpl", id });
  if (!r.ok) throw new Error(r.error);
  const fd = new FormData();
  fd.append("file", new Blob([r.zpl], { type: "text/plain" }), "l.zpl");
  const resp = await fetch(
    `https://api.labelary.com/v1/printers/${LS_DPMM}dpmm/labels/${r.w}x${r.h}/0/`,
    { method: "POST", headers: { Accept: "image/png" }, body: fd },
  );
  if (!resp.ok) throw new Error("Labelary " + resp.status);
  const url = URL.createObjectURL(await resp.blob());
  const img = await new Promise((res, rej) => {
    const im = new Image();
    im.onload = () => res(im);
    im.onerror = rej;
    im.src = url;
  });
  const c = document.createElement("canvas");
  c.width = img.height;
  c.height = img.width;
  const ctx = c.getContext("2d");
  ctx.fillStyle = "#fff";
  ctx.fillRect(0, 0, c.width, c.height);
  ctx.translate(0, c.height);
  ctx.rotate(-Math.PI / 2);
  ctx.drawImage(img, 0, 0);
  URL.revokeObjectURL(url);
  return c;
}
function lsCanvasAGFA(canvas) {
  const w = canvas.width,
    h = canvas.height;
  const data = canvas.getContext("2d").getImageData(0, 0, w, h).data;
  const rowBytes = Math.ceil(w / 8),
    total = rowBytes * h;
  let hex = "";
  for (let row = 0; row < h; row++) {
    for (let b = 0; b < rowBytes; b++) {
      let byte = 0;
      for (let bit = 0; bit < 8; bit++) {
        const col = b * 8 + bit;
        if (col < w) {
          const i = (row * w + col) * 4;
          const lum =
            data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114;
          if (data[i + 3] > 128 && lum < 128) byte |= 0x80 >> bit;
        }
      }
      hex += byte.toString(16).padStart(2, "0").toUpperCase();
    }
  }
  return { hex, rowBytes, total };
}
async function lsPrevisualizar(id) {
  lsToast("Renderizando…");
  try {
    const c = await lsEtiquetaRotada(id);
    lsVerGrande(c.toDataURL("image/png"));
    lsToast("");
  } catch (e) {
    lsToast("No se pudo previsualizar: " + e.message, "err");
  }
}
async function lsImprimir(id) {
  lsToast("Preparando etiqueta…");
  try {
    const c = await lsEtiquetaRotada(id);
    const g = lsCanvasAGFA(c);
    const zpl = `^XA\n^PW${c.width}\n^LL${c.height}\n^FO0,0^GFA,${g.total},${g.total},${g.rowBytes},${g.hex}^FS\n^PQ1\n^XZ`;
    const r = await fetch(LS_IMPRIMIR, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ zpl }),
    }).then((r) => r.json());
    r.ok
      ? lsToast(`Enviado (${r.bytes} bytes)`, "ok")
      : lsToast("Error: " + r.error, "err");
  } catch (e) {
    lsToast("No se pudo imprimir: " + e.message, "err");
  }
}
function lsVerGrande(url) {
  const ov = document.createElement("div");
  ov.className = "ls-overlay ls-preview";
  ov.innerHTML = `<div class="ls-modal"><img src="${url}"></div>`;
  ov.addEventListener("click", () => ov.remove());
  document.body.appendChild(ov);
}

// ---- Toast ----
let lsToastT;
function lsToast(msg, tipo) {
  const t = lsQ("#ls-toast");
  t.textContent = msg;
  t.className = "ls-toast ls-show " + (tipo ? "ls-" + tipo : "");
  clearTimeout(lsToastT);
  lsToastT = setTimeout(
    () => (t.className = "ls-toast " + (tipo ? "ls-" + tipo : "")),
    1900,
  );
}

function lsLoader(mostrar) {
  let el = lsQ("#ls-loader");
  if (mostrar) {
    if (!el) {
      el = document.createElement("div");
      el.id = "ls-loader";
      el.className = "ls-loader";
      el.innerHTML = `<div class="ls-loader-box"><div class="ls-spin"></div><span>Recuperando bitacora…</span></div>`;
      lsQ("#paginaLiquidos").appendChild(el);
    }
  } else if (el) {
    el.remove();
  }
}

function lsRefrescar() {
  lsMontarBloques();
}

// Inicio
lsMontarBloques();

// Espera a que el encabezado (#folioenctext) tenga valor antes de montar
async function lsArranque() {
  lsLoader(true);
  for (let i = 0; i < 40 && !lsIdEnc(); i++) {
    // hasta ~8s esperando el encabezado
    await new Promise((r) => setTimeout(r, 200));
  }
  lsLoader(false);
  await lsMontarBloques();
}
lsArranque();

// Auto-recarga al cambiar de turno/día
let lsEncActual = lsIdEnc();
setInterval(() => {
  const actual = lsIdEnc();
  if (actual !== lsEncActual) {
    lsEncActual = actual;
    lsRefrescar();
  }
}, 800);

// const d = await (await fetch(LS_IMPRIMIR, { method: "POST", body: fd })).json();
// if (!d.ok) {
//   if (d.sin_config) {
//     bootstrap.Modal.getOrCreateInstance(
//       document.getElementById("modalImpresora"),
//     ).show();
//   }
//   // muestra d.error al usuario
// }

const IMP_BTN = document.getElementById("btnConfigImpresora");
let impTimer;

// Revelar con Ctrl+Alt+I
document.addEventListener("keydown", (e) => {
  if (e.ctrlKey && e.altKey && (e.key === "i" || e.key === "I")) {
    e.preventDefault();
    pedirClaveImpresora();
  }
});

async function pedirClaveImpresora() {
  if (!IMP_BTN.classList.contains("d-none")) return; // ya está visible

  const { value: pass } = await Swal.fire({
    title: "Configuración de impresora",
    input: "password",
    inputPlaceholder: "Contraseña",
    inputAttributes: { autocomplete: "off" },
    showCancelButton: true,
    confirmButtonText: "Entrar",
    cancelButtonText: "Cancelar",
  });
  if (!pass) return;

  try {
    const d = await (
      await fetch("../../../../Mes/KCMes/bitacora/php/impresora_auth.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ pass }),
      })
    ).json();

    if (!d.ok) return Swal.fire("Acceso denegado", d.error, "error");

    IMP_BTN.classList.remove("d-none");
    bootstrap.Modal.getOrCreateInstance(
      document.getElementById("modalImpresora"),
    ).show();

    // Se vuelve a ocultar solo al vencer el permiso
    clearTimeout(impTimer);
    impTimer = setTimeout(
      () => IMP_BTN.classList.add("d-none"),
      d.minutos * 60 * 1000,
    );
  } catch (e) {
    Swal.fire("Error", "No se pudo validar", "error");
  }
}
