import { Toolsjs } from "../../Tools/Tools.js";
import { ConfClaves } from "../module/ValesM.js";
// import { driver } from "driver.js";
// import "driver.js/dist/driver.min.css";
const tools     = new Toolsjs();
const confClave = new ConfClaves();
let infoClavesProduccion = [];
let currentPage = 1;
let pageSize    = 15;

const modal          = new bootstrap.Modal(document.getElementById("modalClaves"));
const modalProductos = new bootstrap.Modal(document.getElementById("modalProductos"));
const modalTamano    = new bootstrap.Modal(document.getElementById("modalTamano"));

confClave.llnarslcMaquinas("maqGrid");

tools.llnarslc("CatalogosBitacora", "getClaveClase",     "claveclase",       0);
tools.llnarslc("CatalogosBitacora", "getClaveTipo",      "clavetipo",        0);
tools.llnarslc("CatalogosBitacora", "getClaveProducto",  "claveproducto",    0);
tools.llnarslc("CatalogosBitacora", "getClaveTamaño",    "clavetamaño",      0);
tools.llnarslc("CatalogosBitacora", "getClaveCategoria", "categoriaProducto", 0);

// ─────────────────────────────────────────────────────────────────
// MAPEO CATEGORÍA → MÁQUINAS (temporal hasta tener tabla en BD)
// ─────────────────────────────────────────────────────────────────
const categoriaMaquinas = {
  1:  [61, 62, 63, 60, 65],           // Pañal Infantil
  2:  [64],                       // Calzón Entrenador
  3:  [67],
  4:  [101, 138, 139],            // Conteos Bajos
  6:  [81],                       // Pañal Abierto
  7:  [81, 82, 83],               // Predoblado
  8:  [84, 97],                   // Ropa Interior
  9:  [69, 70, 76, 73, 74, 137],  // Toalla
  10: [75, 72, 77],               // Panty
  11: [68],                       // Lactancia
};

// ─────────────────────────────────────────────────────────────────
// aplicarLogicaCategoria
// Centraliza TODA la lógica que depende del valor de categoría:
//   - xCajaValue label
//   - pesoBaseDiv / anchoDiv
//   - conteosBajos
//   - ustd disabled
//   - filtro de máquinas en el grid
// Se expone en window para que ValesM.js pueda llamarla
// ─────────────────────────────────────────────────────────────────
window.aplicarLogicaCategoria = function (valorCategoria) {
  const val          = parseInt(valorCategoria);
  const xCajaValue   = document.getElementById("xCajaValue");
  const xcaja        = document.getElementById("xcaja");
  const factor       = document.getElementById("factor");
  const ustd         = document.getElementById("ustd");
  const pesoBaseDiv  = document.getElementById("pesoBaseDiv");
  const anchoDiv     = document.getElementById("anchoDiv");
  const conteosBajos = document.getElementById("conteosBajos");

  const categoriaTNT = [14, 15, 16, 17, 18];

  // ── xCaja label ─────────────────────────────────────────────
  if (xCajaValue) {
    xCajaValue.innerText = categoriaTNT.includes(val)
      ? "Ancho de Rollo Maestro"
      : "xCaja";
  }

  // ── pesoBase / ancho (solo categoria 15) ────────────────────
  if (pesoBaseDiv) pesoBaseDiv.hidden = val !== 15;
  if (anchoDiv)    anchoDiv.hidden    = val !== 15;

  // ── conteosBajos (categoria 4) ──────────────────────────────
  if (conteosBajos) conteosBajos.hidden = val !== 4;

  // ── ustd y factor para categorías TNT ───────────────────────
  if (categoriaTNT.includes(val)) {
    if (ustd) {
      ustd.value    = 1000;
      ustd.disabled = true;
    }
    if (xcaja) {
      xcaja.oninput = () => {
        const xcajaVal  = parseFloat(xcaja.value)  || 0;
        const ustdVal   = parseFloat(ustd?.value)  || 1;
        const factorCalc = Math.round((xcajaVal / ustdVal) * 1000) / 1000;
        if (factor) factor.value = factorCalc;
      };
    }
  } else {
    if (ustd) ustd.disabled = false;
    if (xcaja) xcaja.oninput = null;
  }

  // ── Filtrar máquinas según categoría ────────────────────────
  filtrarMaquinasPorCategoria(val);
};

// ─────────────────────────────────────────────────────────────────
// filtrarMaquinasPorCategoria
// Muestra solo las máquinas que pertenecen a la categoría.
// Si no hay mapeo para esa categoría, muestra todas.
// ─────────────────────────────────────────────────────────────────
function filtrarMaquinasPorCategoria(categoriaId) {
  const maquinasPermitidas = categoriaMaquinas[categoriaId] ?? null;
  const cols = document.querySelectorAll("#maqGrid .col-6");

  cols.forEach((col) => {
    const cb = col.querySelector("input[type=checkbox]");
    if (!cb) return;

    const idMaq = parseInt(cb.value);

    if (!maquinasPermitidas) {
      // Sin categoría o sin mapeo → mostrar todas
      col.style.display = "";
    } else {
      col.style.display = maquinasPermitidas.includes(idMaq) ? "" : "none";

      // Si el chip queda oculto y estaba seleccionado, desmarcarlo
      if (!maquinasPermitidas.includes(idMaq) && cb.checked) {
        cb.checked = false;
        col.querySelector(".maq-chip")?.classList.remove("selected");
      }
    }
  });

  actualizarSummary();
}

// ─────────────────────────────────────────────────────────────────
// Listener del campo ustd (calcula factor)
// ─────────────────────────────────────────────────────────────────
document.getElementById("ustd").addEventListener("change", (e) => {
  e.preventDefault();
  const xcaja = parseFloat(document.getElementById("xcaja").value);
  const ustd  = parseFloat(document.getElementById("ustd").value);
  let factor  = xcaja / ustd;
  factor      = Math.round(factor * 1000) / 1000;
  document.getElementById("factor").value = factor;
});

// ─────────────────────────────────────────────────────────────────
// Listener del select de categoría (modo nueva clave)
// En modo edición el listener se agrega en editarClave de ValesM.js
// ─────────────────────────────────────────────────────────────────
document.getElementById("categoriaProducto").addEventListener("change", (e) => {
  const val = e.target.value;

  window.aplicarLogicaCategoria(val);

  // Limpiar claveconv si no es categoría 4
  if (val != "4") {
    document.getElementById("claveconv").value = "";
    document.getElementById("clavePuente").value = "";
  }

  // ── Filtrar #claveproducto si categoría es 3 ──
  const selectProducto = document.getElementById("claveproducto");
  const PERMITIDOS_PRODUCTO = ["61", "62"];

  Array.from(selectProducto.options).forEach((option) => {
    if (option.value === "") return;
    option.hidden = val === "3" && !PERMITIDOS_PRODUCTO.includes(option.value);
    option.disabled =
      val === "3" && !PERMITIDOS_PRODUCTO.includes(option.value);
  });

  if (val === "3" && !PERMITIDOS_PRODUCTO.includes(selectProducto.value)) {
    selectProducto.value = "";
  }

  // ── Filtrar #clavetamaño si categoría es 3 ──
  const selectTamaño = document.getElementById("clavetamaño");
  const PERMITIDOS_TAMAÑO = ["66", "67", "68", "69", "70"];

  Array.from(selectTamaño.options).forEach((option) => {
    if (option.value === "") return;
    option.hidden = val === "3" && !PERMITIDOS_TAMAÑO.includes(option.value);
    option.disabled = val === "3" && !PERMITIDOS_TAMAÑO.includes(option.value);
  });

  if (val === "3" && !PERMITIDOS_TAMAÑO.includes(selectTamaño.value)) {
    selectTamaño.value = "";
  }
  document.getElementById("xcajaDiv").style.display = val === "3" ? "none" : "";
  document.getElementById("ustdDiv").style.display = val === "3" ? "none" : "";

  // Desmarcar chips al cambiar categoría en nueva clave
  document.querySelectorAll("#maqGrid input[type=checkbox]").forEach((cb) => {
    cb.checked = false;
    cb.closest(".maq-chip")?.classList.remove("selected");
  });

  actualizarSummary();
});

// Map de id de tamaño → valor real
const TAMAÑO_VALOR_REAL = {
  66: 145,
  67: 184,
  68: 193,
  69: 215,
  70: 223,
};

document.getElementById("clavetamaño").addEventListener("change", (e) => {
  const valorReal = TAMAÑO_VALOR_REAL[e.target.value];

  if (valorReal !== undefined) {
    document.getElementById("factor").value = (valorReal / 1000).toFixed(3);
  } else {
    // Si se selecciona un tamaño fuera del map (categoría distinta a 3), limpiar
    document.getElementById("factor").value = "";
  }
});


// ─────────────────────────────────────────────────────────────────
// Guardar clave
// ─────────────────────────────────────────────────────────────────
document
  .getElementById("savechgclaves")
  .addEventListener("click", async function (e) {
    e.preventDefault();

    // 1. Recoger valores
    const idclave     = document.getElementById("idclave").value.trim();
    const noclave     = document.getElementById("noclave").value.trim();
    const descripcion = document.getElementById("descripcionclave").value.trim();
    const categoria   = document.getElementById("categoriaProducto").value;
    const producto    = document.getElementById("claveproducto").value;
    const tamaño      = document.getElementById("clavetamaño").value;
    const xcaja       = document.getElementById("xcaja").value;
    const ustd        = document.getElementById("ustd").value;
    const factor      = document.getElementById("factor").value;
    const pesoBase    = document.getElementById("pesobase").value;
    const ancho       = document.getElementById("ancho").value;
    const clavePuente = document.getElementById("claveconv").value;
    const maquinas    = [...document.querySelectorAll("#maqGrid input:checked")]
                          .map((cb) => cb.value);

    // 2. Validar
    ocultarError();
    if (!noclave) {
      mostrarError("El No. clave es obligatorio.");
      document.getElementById("noclave").focus();
      return;
    }
    if (!descripcion) {
      mostrarError("La descripción es obligatoria.");
      document.getElementById("descripcionclave").focus();
      return;
    }
    if (maquinas.length === 0) {
      mostrarError("Debes asignar al menos una máquina.");
      return;
    }

    // 3. Armar payload
    const formData = {
      idclave:     idclave || "",
      NoClave:     noclave,
      Descripcion: descripcion,
      Categoria:   categoria,
      Producto:    producto,
      Tamaño:      tamaño,
      xcaja:       xcaja,
      ustd:        ustd,
      factor:      factor,
      pesoBase:    pesoBase,
      ancho:       ancho,
      clavePuente: clavePuente,
      maquinas:    maquinas,
    };

    // 4. Spinner
    const btn = document.getElementById("savechgclaves");
    btn.disabled  = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" 
                      role="status" aria-hidden="true"></span> Guardando...`;

    try {
      const { ok, status } = await confClave.saveClave(formData);

      if (ok) {
        // 5. Cerrar modal
        bootstrap.Modal
          .getInstance(document.getElementById("modalClaves"))
          .hide();

        // 6. Limpiar formulario completo
        resetFormularioClaves();

        // 7. Recargar tabla
        try {
          await obtenerDatos();
          currentPage = 1;
          mostrarTabla();
        } catch (err) {
          console.error("Error recargando datos:", err);
          mostrarTabla();
        }
      }

    } catch (error) {
      console.log(error);
    } finally {
      btn.disabled  = false;
      btn.innerHTML = `<i class="fas fa-save"></i> Guardar`;
    }
  });

// ─────────────────────────────────────────────────────────────────
// Autocomplete clave puente
// ─────────────────────────────────────────────────────────────────
document.getElementById("clavePuente").addEventListener("input", (e) => {
  const autocompleteclaves = document.getElementById("autocompleteclaves");
  confClave.slcautocomplete(
    e,
    autocompleteclaves,
    "claveconv",
    "../ValesE/php/Vales.php?autoclaves",
    "clavePuente",
  );
});

// ─────────────────────────────────────────────────────────────────
// Nueva clave — limpiar todo el formulario
// ─────────────────────────────────────────────────────────────────
document.getElementById("nuevaclave").addEventListener("click", (e) => {
  e.preventDefault();
  modal.show();
  resetFormularioClaves();
});

// ─────────────────────────────────────────────────────────────────
// resetFormularioClaves
// Limpia TODOS los campos del modal: inputs, selects,
// chips de máquinas, estados especiales y filtro de categoría
// ─────────────────────────────────────────────────────────────────
function resetFormularioClaves() {
  // Campos de texto y selects
  [
    "idclave",
    "noclave",
    "descripcionclave",
    "xcaja",
    "ustd",
    "factor",
    "claveproducto",
    "clavetamaño",
    "categoriaProducto",
    "pesobase",
    "ancho",
    "claveconv",
    "clavePuente",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = "";
  });

  // Campos especiales
  const noclaveEl  = document.getElementById("noclave");
  const ustdEl     = document.getElementById("ustd");
  const xCajaLabel = document.getElementById("xCajaValue");
  if (noclaveEl)  noclaveEl.readOnly = false;
  if (ustdEl)     ustdEl.disabled    = false;
  if (xCajaLabel) xCajaLabel.innerText = "xCaja";

  // ── ★ Restaurar visibilidad de divs ocultos por categoría 3 ──
  document.getElementById("xcajaDiv").style.display = "";
  document.getElementById("ustdDiv").style.display  = "";

  // ── ★ Restaurar todas las opciones de claveproducto y clavetamaño ──
  ["claveproducto", "clavetamaño"].forEach((selectId) => {
    const select = document.getElementById(selectId);
    if (!select) return;
    Array.from(select.options).forEach((option) => {
      option.hidden   = false;
      option.disabled = false;
    });
  });

  // Lógica de categoría con valor vacío (oculta todo y muestra todas las máquinas)
  window.aplicarLogicaCategoria("");

  // Desmarcar chips
  document.querySelectorAll("#maqGrid input[type=checkbox]").forEach((cb) => {
    cb.checked = false;
    cb.closest(".maq-chip")?.classList.remove("selected");
  });

  // Mostrar todas las máquinas (resetear filtro)
  document.querySelectorAll("#maqGrid .col-6").forEach((col) => {
    col.style.display = "";
  });

  // Quitar borde rojo de validación de máquinas
  document
    .getElementById("maqGrid")
    .classList.remove("border", "border-danger", "rounded");

  actualizarSummary();
  ocultarError();
}

// ─────────────────────────────────────────────────────────────────
// Guardar producto
// ─────────────────────────────────────────────────────────────────
document.getElementById("btnGuardarProducto").addEventListener("click", (e) => {
  e.preventDefault();
  const producto = document.getElementById("descripcionProducto").value;
  confClave.saveProducto(producto).then((res) => {
    if (res === false) return false;
    modalProductos.hide();
    document.getElementById("descripcionProducto").value = "";
  });
});

// ─────────────────────────────────────────────────────────────────
// Editar clave (llamado desde la tabla)
// ─────────────────────────────────────────────────────────────────
window.editclaves = async (param) => {
  await confClave.editarClave(
    param,
    "idclave",
    "noclave",
    "descripcionclave",
    "xcaja",
    "factor",
    "claveproducto",
    "clavetamaño",
    "categoriaProducto",
    "ustd",
    "pesoBaseDiv",
    "pesobase",
    "anchoDiv",
    "ancho",
    "conteosBajos",
    "claveconv",
    "clavePuente",
  );
  // Se llama DESPUÉS del await para asegurarse que los chips ya están marcados
  actualizarSummary();
};

// ─────────────────────────────────────────────────────────────────
// Borrar clave
// ─────────────────────────────────────────────────────────────────
window.borrarClavebtn = (param) => {
  Swal.fire({
    title: "¿Está seguro?",
    text: "Este material se eliminará permanentemente.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      confClave.borrarClave(param).then(() => {
        obtenerDatos();
        Swal.fire("Eliminado", "La clave ha sido eliminada.", "success");
      });
    }
  });
};

// ─────────────────────────────────────────────────────────────────
// toggleChip — marcado/desmarcado de chips de máquinas
// ─────────────────────────────────────────────────────────────────
window.toggleChip = (cb) => {
  cb.closest(".maq-chip").classList.toggle("selected", cb.checked);
  actualizarSummary();
};

// ─────────────────────────────────────────────────────────────────
// actualizarSummary — actualiza el texto del resumen de máquinas
// Se expone en window para que ValesM.js pueda llamarla
// ─────────────────────────────────────────────────────────────────
window.actualizarSummary = function actualizarSummary() {
  const sel   = [...document.querySelectorAll("#maqGrid input:checked")];
  const alert = document.getElementById("summaryAlert");
  const txt   = document.getElementById("summaryTxt");

  if (sel.length === 0) {
    alert.className = "alert alert-secondary d-flex align-items-center py-2 mb-0";
    txt.textContent = "Selecciona al menos una máquina";
  } else {
    alert.className = "alert alert-primary d-flex align-items-center py-2 mb-0";
    const nombres = sel
      .map((cb) => {
        const label = document.querySelector(`#chip-${cb.value} .maq-name`);
        return label ? label.textContent : cb.value;
      })
      .join(", ");
    txt.textContent =
      sel.length === 1
        ? `Asignada a: ${nombres}`
        : `Asignada a ${sel.length} máquinas: ${nombres}`;
  }
}

// También disponible sin window. para llamadas internas
function actualizarSummary() {
  window.actualizarSummary();
}

// ─────────────────────────────────────────────────────────────────
// mostrarError / ocultarError
// ─────────────────────────────────────────────────────────────────
function mostrarError(msg) {
  const el = document.getElementById("errorAlert");
  document.getElementById("errorTxt").textContent = msg;
  el.classList.remove("d-none");
}

function ocultarError() {
  document.getElementById("errorAlert").classList.add("d-none");
}

// ─────────────────────────────────────────────────────────────────
// limpiarFormulario (utilitario genérico por IDs)
// ─────────────────────────────────────────────────────────────────
function limpiarFormulario(idsToClear) {
  idsToClear.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = "";
  });
  document.querySelectorAll("#maqGrid input[type=checkbox]").forEach((cb) => {
    cb.checked = false;
    cb.closest(".maq-chip")?.classList.remove("selected");
  });
  document.getElementById("maqGrid").classList.remove("border", "border-danger", "rounded");
  actualizarSummary();
  ocultarError();
}

// ─────────────────────────────────────────────────────────────────
// Tabla de claves con paginación
// ─────────────────────────────────────────────────────────────────
obtenerDatos();

async function obtenerDatos() {
  try {
    const axiosResponse = await axios.get("../ValesE/php/Vales.php?tblclavesConf");

    infoClavesProduccion = axiosResponse.data.map((item) => ({
      id:          item.id,
      categoria:   item.categoria,
      noclave:     item.noclave,
      descripcion: item.descclave,
      producto:    item.Producto,
      tamaño:      item.Etapa,
      piezasCaja:  item.xcaja,
      factor:      item.factor,
      departamento: item.NoDepto,
      maquina:     item.Nomaquina,
      estadoClave: item.EstadoClave,
    }));

    if (axiosResponse.status === 200) {
      mostrarTabla();
    }
  } catch (error) {
    console.log(error);
    swal.fire("Error", "Hay un problema con la base de datos", "error");
  }
}

function mostrarTabla(query = document.getElementById("searchInput").value) {
  const tbody = document.getElementById("tblclaves");
  tbody.innerHTML = "";
  const q = (query || "").toString().trim().toLowerCase();

  const datosFiltrados = q
    ? infoClavesProduccion.filter((item) =>
        [
          item.id,
          item.categoria,
          item.noclave,
          item.descripcion,
          item.producto,
          item.tamaño,
          item.piezasCaja,
          item.factor,
          item.departamento,
          item.maquina,
          item.estadoClave,
        ].some((v) => v && v.toString().toLowerCase().includes(q)),
      )
    : infoClavesProduccion.slice();

  const totalRegistros = datosFiltrados.length;
  const totalPaginas   = Math.max(1, Math.ceil(totalRegistros / pageSize));

  if (currentPage > totalPaginas) currentPage = totalPaginas;

  const inicio = (currentPage - 1) * pageSize;
  const fin    = inicio + pageSize;

  const paginaActualDatos = datosFiltrados.slice(inicio, fin);

  let body = "";

  if (paginaActualDatos.length === 0) {
    body = `<tr><td colspan="10" class="text-center">No hay registros que coincidan</td></tr>`;
  } else {
    paginaActualDatos.forEach((item) => {
      if (item.estadoClave !== 1) {
        body += `
          <tr>
            <td>${(item.departamento ?? "SIN DEPARTAMENTO").toUpperCase()}</td>
            <td>${(item.maquina     ?? "SIN MAQUINA").toUpperCase()}</td>
            <td>${(item.categoria   ?? "SIN CATEGORIA").toUpperCase()}</td>
            <td>${item.noclave}</td>
            <td>${(item.descripcion ?? "SIN DESCRIPCION").toUpperCase()}</td>
            <td>${(item.producto    ?? "SIN PRODUCTO").toUpperCase()}</td>
            <td>${(item.tamaño      ?? "SIN TAMAÑO").toUpperCase()}</td>
            <td>${item.piezasCaja}</td>
            <td>${item.factor}</td>
            <td>
              <button class="btn btn-sm btn-warning" onclick="editclaves(${item.id})">
                <i class="fas fa-tools"></i>
              </button>
              <button class="btn btn-sm btn-danger" onclick="borrarClavebtn(${item.id})">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
      }
    });
  }

  tbody.innerHTML = body;
  document.getElementById("pageInfo").innerText =
    `PÁGINA ${currentPage} DE ${totalPaginas} (TOTAL: ${totalRegistros})`;
}

// ── Buscador ────────────────────────────────────────────────────
document.getElementById("searchInput").addEventListener("keyup", (e) => {
  e.preventDefault();
  clearTimeout(e.target._searchTimer);
  e.target._searchTimer = setTimeout(() => {
    currentPage = 1;
    mostrarTabla();
  }, 250);
});

// ── Cambiar cantidad por página ──────────────────────────────────
document.getElementById("pageSize").addEventListener("change", (e) => {
  pageSize    = parseInt(e.target.value);
  currentPage = 1;
  mostrarTabla(document.getElementById("searchInput").value);
});

// ── Paginación ───────────────────────────────────────────────────
document.getElementById("prevPage").addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    mostrarTabla(document.getElementById("searchInput").value);
  }
});

document.getElementById("nextPage").addEventListener("click", () => {
  currentPage++;
  mostrarTabla(document.getElementById("searchInput").value);
});