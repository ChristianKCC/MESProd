import { API, llamarGET, llamarPOST } from "../../Endpoints/endpoints.js";

/* Payload acumulado de los 3 pasos: nada se guarda hasta el Paso 3 */
export const payloadRARR = { paso1: null, paso2: null, paso3: null };

let catalogos = null;
let escenarios = []; // Cada uno lleva su p2, p3, p3b e imágenes
let genericos = []; // peligros genéricos (únicos por tab)
let indiceEdicion = null;
let imgEscTmp = null; // foto en captura del Tab 1
let idRARREdicion = null; // null = alta; con valor = edición

const MAX_IMAGEN = 5 * 1024 * 1024; // 5 MB

/* Defaults fijos de los peligros genéricos, por Orden (ids de tus catálogos) */
const GEN_DEFAULT = {
  1: {
    idCategoria: "11",
    idSeveridad: "3",
    idProbabilidad: "1",
    idFrecuencia: "3",
    idPersonas: "1",
  }, // Ruido → Mayor Irrev / 100% / Diario / 1a2
  2: {
    idCategoria: "4",
    idSeveridad: "1",
    idProbabilidad: "1",
    idFrecuencia: "3",
    idPersonas: "1",
  }, // Eléctrico electrocución → Fatalidad
  3: {
    idCategoria: "4",
    idSeveridad: "1",
    idProbabilidad: "1",
    idFrecuencia: "3",
    idPersonas: "1",
  }, // Eléctrico arco → Fatalidad
  4: {
    idCategoria: "13",
    idSeveridad: "3",
    idProbabilidad: "1",
    idFrecuencia: "3",
    idPersonas: "1",
  }, // Neumática → Mayor Irrev
  5: {
    idCategoria: "4",
    idSeveridad: "3",
    idProbabilidad: "1",
    idFrecuencia: "3",
    idPersonas: "1",
  }, // Eléctrico paros → Mayor Irrev
};

document.addEventListener("DOMContentLoaded", async () => {
  await Promise.all([cargarMaquinas(), cargarCatalogos()]);
  await cargarGenericos();
  registrarEventos();
});

/* ============================================================
   CARGA INICIAL
   ============================================================ */
async function cargarMaquinas() {
  try {
    const res = await llamarGET(API.getMaquinas);
    document.querySelectorAll(".slcMaquina").forEach((slc) => {
      res.data.forEach((m) => {
        const opt = document.createElement("option");
        opt.value = m.id;
        opt.textContent = m.nombre;
        slc.appendChild(opt);
      });
    });
  } catch (e) {
    mostrarError(e.message);
  }
}

async function cargarCatalogos() {
  try {
    const res = await llamarGET(API.getCatalogos);
    catalogos = res.data;

    llenarSelect("t1Categoria", catalogos.categoriasPeligro);
    llenarSelect("t1Consecuencia", catalogos.consecuencias);
    llenarSelect("t1Mecanismo", catalogos.mecanismos);
    llenarSelect("t1Fuente", catalogos.fuentes);
    llenarSelect("t1Severidad", catalogos.severidades);
    llenarSelect("t1Probabilidad", catalogos.probabilidades);
    llenarSelect("t1Frecuencia", catalogos.frecuencias);
    llenarSelect("t1Personas", catalogos.personasExpuestas);
  } catch (e) {
    mostrarError(e.message);
  }
}

async function cargarGenericos() {
  try {
    const res = await llamarGET(API.getGenericos);
    genericos = res.data.map((g) => {
      const def = GEN_DEFAULT[g.Orden] || {};
      return {
        idGenerico: g.IdGenerico,
        orden: g.Orden,
        escenario: g.EscenarioRiesgo,
        idCategoria: def.idCategoria ?? "",
        idSeveridad: def.idSeveridad ?? "",
        idProbabilidad: def.idProbabilidad ?? "",
        idFrecuencia: def.idFrecuencia ?? "",
        idPersonas: def.idPersonas ?? "",
        idCriterioGuarda: "",
        idMedida: "",
      };
    });
    pintarGenericosT1();
  } catch (e) {
    mostrarError(e.message);
  }
}

function llenarSelect(id, filas) {
  const slc = document.getElementById(id);
  if (!slc) return;
  (filas || []).forEach((f) => {
    const opt = document.createElement("option");
    opt.value = f.id;
    opt.textContent = f.Descripcion;
    if (f.Valor !== undefined && f.Valor !== null) opt.dataset.valor = f.Valor;
    slc.appendChild(opt);
  });
}

/* ============================================================
   EVENTOS
   ============================================================ */
function registrarEventos() {
  /* ---- Tab 1 ---- */
  document.getElementById("t1Maquina").addEventListener("change", function () {
    cargarSeccionesRARR(this.value);
  });
  document
    .getElementById("t1Seccion")
    .addEventListener("change", pintarIdEquipo);

  ["t1Consecuencia", "t1Mecanismo", "t1Fuente"].forEach((id) =>
    document.getElementById(id).addEventListener("change", armarEscenario),
  );
  ["t1Severidad", "t1Probabilidad", "t1Frecuencia", "t1Personas"].forEach(
    (id) =>
      document.getElementById(id).addEventListener("change", calcularNivelVivo),
  );

  document.getElementById("t1Imagen").addEventListener("change", (e) => {
    const r = leerImagen(e, "t1ImagenPreview");
    if (r !== undefined) imgEscTmp = r;
  });
  document
    .getElementById("t1BtnAgregar")
    .addEventListener("click", agregarEscenario);
  document
    .getElementById("t1BtnCancelarEdicion")
    .addEventListener("click", () => limpiarFormularioT1(false));
  document
    .getElementById("t1BtnLimpiar")
    .addEventListener("click", () => limpiarFormularioT1(true));
  document
    .getElementById("t1BtnContinuar")
    .addEventListener("click", continuarPaso1);

  /* ---- Tab 2 ---- */
  document
    .getElementById("t2BtnContinuar")
    .addEventListener("click", continuarPaso2);
  document
    .getElementById("t2BtnRegresar")
    .addEventListener("click", () =>
      document.querySelector('[data-bs-target="#tab1"]').click(),
    );

  /* ---- Tab 3 ---- */
  document
    .getElementById("t3BtnRegistrar")
    .addEventListener("click", registrarRARR);
  document
    .getElementById("t3BtnRegresar")
    .addEventListener("click", () =>
      document.querySelector('[data-bs-target="#tab2"]').click(),
    );

  /* Hereda contexto al entrar a cada paso */
  document
    .querySelector('[data-bs-target="#tab2"]')
    .addEventListener("shown.bs.tab", () => {
      heredarContextoPaso2();
      pintarCardsT2();
      pintarGenericosT2();
    });
  document
    .querySelector('[data-bs-target="#tab3"]')
    .addEventListener("shown.bs.tab", () => {
      heredarContextoPaso3();
      pintarCardsT3();
      pintarGenericosT3();
    });

  /* Bloquea saltarse pasos */
  ["#tab2", "#tab3"].forEach((destino) => {
    document
      .querySelector(`[data-bs-target="${destino}"]`)
      .addEventListener("show.bs.tab", (e) => {
        const requiere =
          destino === "#tab2" ? payloadRARR.paso1 : payloadRARR.paso2;
        if (!requiere) {
          e.preventDefault();
          mostrarAviso("Completa el paso anterior antes de avanzar");
        }
      });
  });

  /* ---- Tab 4 ---- */
  document.getElementById("t4Maquina").addEventListener("change", function () {
    cargarSeccionesTab4(this.value);
  });
  document
    .getElementById("t4Seccion")
    .addEventListener("change", cargarRARRTab4);
  document
    .getElementById("t4BtnEliminar")
    .addEventListener("click", eliminarRARRCompleto);
  document.getElementById("t4BtnEditar").addEventListener("click", editarRARR);
  document
    .getElementById("t4BtnConcluir")
    .addEventListener("click", concluirRARR);

  registrarEventosConfig();
}

/* ============================================================
   UTILERÍAS DE CATÁLOGO Y CÁLCULO
   ============================================================ */
function valorDe(catalogo, id) {
  const f = (catalogos[catalogo] || []).find(
    (x) => String(x.id) === String(id),
  );
  return f && f.Valor !== undefined && f.Valor !== null
    ? parseFloat(f.Valor)
    : null;
}

function textoDe(catalogo, id) {
  const f = (catalogos[catalogo] || []).find(
    (x) => String(x.id) === String(id),
  );
  return f ? f.Descripcion : "-";
}

function optsHTML(catalogo, sel) {
  return (catalogos[catalogo] || [])
    .map(
      (f) =>
        `<option value="${f.id}" ${String(f.id) === String(sel) ? "selected" : ""}>${f.Descripcion}</option>`,
    )
    .join("");
}

function selectHTML(catalogo, valorSel, campo, i) {
  const opts = optsHTML(catalogo, valorSel);
  return `<select class="form-select form-select-sm sel-gen" data-campo="${campo}" data-i="${i}">
            <option value="">--</option>${opts}
          </select>`;
}

function redondear(n) {
  return Math.round(n * 100) / 100;
}

function clasificacionVisual(puntaje) {
  if (puntaje > 500)
    return {
      texto: "RIESGO INACEPTABLE",
      clase: "nivel-alto",
      nivel: "Inaceptable",
    };
  if (puntaje > 50)
    return { texto: "RIESGO ALTO", clase: "nivel-alto", nivel: "Alto" };
  if (puntaje > 5)
    return { texto: "RIESGO BAJO", clase: "nivel-medio", nivel: "Bajo" };
  return { texto: "RIESGO ACEPTABLE", clase: "nivel-bajo", nivel: "Aceptable" };
}

function badgePuntaje(p) {
  if (p === null) return `<span class="badge-estatus est-pendiente">—</span>`;
  const v = clasificacionVisual(p);
  return `<span class="badge-riesgo ${v.clase.replace("nivel-", "")}">${v.nivel}</span>`;
}

function pintarCaja(idPuntaje, idNivel, puntaje) {
  const boxP = document.getElementById(idPuntaje);
  const boxN = document.getElementById(idNivel);
  if (puntaje === null) {
    boxP.className = "nivel-riesgo-box nivel-nulo";
    boxP.innerHTML = `<span>—</span>`;
    boxN.className = "nivel-riesgo-box nivel-nulo";
    boxN.innerHTML = `<i class="fa-solid fa-shield-halved"></i><span>—</span>`;
    return;
  }
  const v = clasificacionVisual(puntaje);
  boxP.className = "nivel-riesgo-box " + v.clase;
  boxP.innerHTML = `<span>${puntaje}</span>`;
  boxN.className = "nivel-riesgo-box " + v.clase;
  boxN.innerHTML = `<i class="fa-solid fa-shield-halved"></i><span>${v.texto}</span>`;
}

/* Factores propios de cada escenario para P2 y P3 */
function factoresEsc(f) {
  return {
    s: valorDe("severidades", f.idSeveridad),
    fr: valorDe("frecuencias", f.idFrecuencia),
    n: valorDe("personasExpuestas", f.idPersonas),
  };
}
function puntajeP2(f) {
  const { s, fr, n } = factoresEsc(f);
  const c = valorDe("criteriosGuarda", f.p2 && f.p2.idCriterioGuarda);
  if (s === null || c === null || fr === null || n === null) return null;
  return redondear(s * c * fr * n);
}
function puntajeP3(f) {
  const { s, fr, n } = factoresEsc(f);
  const m = valorDe("medidasMitigacion", f.p3 && f.p3.idMedida);
  if (s === null || m === null || fr === null || n === null) return null;
  return redondear(s * m * fr * n);
}

/* ============================================================
   IMÁGENES
   ============================================================ */
function leerImagen(e, idPrev) {
  const file = e.target.files[0];
  const prev = document.getElementById(idPrev);
  if (!file) {
    if (prev) prev.style.display = "none";
    return null;
  }
  if (!file.type.startsWith("image/")) {
    mostrarAviso("El archivo debe ser una imagen");
    e.target.value = "";
    return undefined;
  }
  if (file.size > MAX_IMAGEN) {
    mostrarAviso("La imagen no debe pesar más de 5 MB");
    e.target.value = "";
    return undefined;
  }
  if (prev) {
    prev.src = URL.createObjectURL(file);
    prev.style.display = "inline-block";
  }
  return file;
}

function celdaFoto(img) {
  if (img instanceof File)
    return `<img src="${URL.createObjectURL(img)}" style="height:36px;border-radius:4px">`;
  if (typeof img === "string" && img)
    return `<img src="${img}" style="height:36px;border-radius:4px">`;
  return `<span class="text-muted">—</span>`;
}

/* ============================================================
   IBM -> NOMBRE
   ============================================================ */
async function nombreEmpleado(noEmp) {
  if (!noEmp) return "";
  try {
    const res = await llamarGET(API.getEmpleado, { noEmp });
    return res.data.Nombre;
  } catch (e) {
    mostrarAviso(`No se encontró un empleado con el IBM ${noEmp}`);
    return "";
  }
}

/* ============================================================
   TAB 1 — ESCENARIOS
   ============================================================ */
async function cargarSeccionesRARR(idMaquina) {
  const slc = document.getElementById("t1Seccion");
  slc.innerHTML = `<option value="">Seleccione una opción</option>`;
  document.getElementById("t1IdEquipo").value = "";
  if (idMaquina === "") return;

  try {
    const res = await llamarGET(API.getSeccionesRARR, { idMaquina });
    if (res.data.length === 0) {
      slc.innerHTML = `<option value="">Esta máquina no tiene secciones dadas de alta</option>`;
      return;
    }
    res.data.forEach((s) => {
      const opt = document.createElement("option");
      opt.value = s.IdSeccion;
      opt.textContent = s.Seccion;
      opt.dataset.idEquipo = s.IdEquipo;
      slc.appendChild(opt);
    });
  } catch (e) {
    mostrarError(e.message);
  }
}

function pintarIdEquipo() {
  const slc = document.getElementById("t1Seccion");
  const opt = slc.options[slc.selectedIndex];
  document.getElementById("t1IdEquipo").value =
    opt && opt.value !== "" ? opt.dataset.idEquipo : "";
}

function armarEscenario() {
  const partes = ["t1Consecuencia", "t1Mecanismo", "t1Fuente"]
    .map((id) => textoSeleccionado(id))
    .filter((t) => t !== null);
  document.getElementById("t1Escenario").value =
    partes.length === 3 ? partes.join(" ") : "";
}

function textoSeleccionado(id) {
  const slc = document.getElementById(id);
  const opt = slc.options[slc.selectedIndex];
  return opt && opt.value !== "" ? opt.textContent.trim() : null;
}

function valorSeleccionado(id) {
  const slc = document.getElementById(id);
  const opt = slc.options[slc.selectedIndex];
  if (!opt || opt.value === "") return null;
  return parseFloat(opt.dataset.valor ?? "0");
}

function calcularNivelVivo() {
  const sev = valorSeleccionado("t1Severidad");
  const prob = valorSeleccionado("t1Probabilidad");
  const frec = valorSeleccionado("t1Frecuencia");
  const pers = valorSeleccionado("t1Personas");
  if (sev === null || prob === null || frec === null || pers === null) {
    pintarCaja("t1Puntaje", "t1NivelRiesgo", null);
    return null;
  }
  const puntaje = redondear(sev * prob * frec * pers);
  pintarCaja("t1Puntaje", "t1NivelRiesgo", puntaje);
  return puntaje;
}

function entrarEdicionT1() {
  document.getElementById("t1BtnAgregar").innerHTML =
    `<i class="fa-solid fa-floppy-disk me-1"></i>Guardar cambios`;
  document.getElementById("t1BtnCancelarEdicion").style.display =
    "inline-block";
}
function salirEdicionT1() {
  indiceEdicion = null;
  document.getElementById("t1BtnAgregar").innerHTML =
    `<i class="fa-solid fa-plus me-1"></i>Agregar a la lista`;
  document.getElementById("t1BtnCancelarEdicion").style.display = "none";
}

function agregarEscenario() {
  if (
    !validarCampos([
      { id: "t1Maquina", nombre: "Máquina" },
      { id: "t1Seccion", nombre: "Sección / Módulo" },
      { id: "t1Categoria", nombre: "Categoría de Peligro" },
      { id: "t1Consecuencia", nombre: "Consecuencia" },
      { id: "t1Mecanismo", nombre: "Mecanismo" },
      { id: "t1Fuente", nombre: "Fuente" },
      { id: "t1Severidad", nombre: "Severidad" },
      { id: "t1Probabilidad", nombre: "Probabilidad" },
      { id: "t1Frecuencia", nombre: "Frecuencia" },
      { id: "t1Personas", nombre: "N. de Personas del Riesgo" },
    ])
  )
    return;

  const editando = indiceEdicion !== null;
  const imgP1 =
    imgEscTmp || (editando ? escenarios[indiceEdicion].imgP1 : null);
  if (!imgP1) {
    mostrarAviso("Adjunta la foto del escenario antes de agregarlo");
    return;
  }

  const puntaje = calcularNivelVivo();
  const visual = clasificacionVisual(puntaje);
  const previo = editando ? escenarios[indiceEdicion] : {};

  const fila = {
    idEscenario: previo.idEscenario || null,
    idSeccion: valor("t1Seccion"),
    seccion: textoSeleccionado("t1Seccion"),
    idEquipo: valor("t1IdEquipo"),
    idCategoria: valor("t1Categoria"),
    categoria: textoSeleccionado("t1Categoria"),
    idConsecuencia: valor("t1Consecuencia"),
    idMecanismo: valor("t1Mecanismo"),
    idFuente: valor("t1Fuente"),
    escenario: valor("t1Escenario"),
    idSeveridad: valor("t1Severidad"),
    severidad: textoSeleccionado("t1Severidad"),
    idProbabilidad: valor("t1Probabilidad"),
    probabilidad: textoSeleccionado("t1Probabilidad"),
    idFrecuencia: valor("t1Frecuencia"),
    frecuencia: textoSeleccionado("t1Frecuencia"),
    idPersonas: valor("t1Personas"),
    personas: textoSeleccionado("t1Personas"),
    puntaje: puntaje,
    nivel: visual.nivel,
    imgP1: imgP1,
    imgP3: previo.imgP3 || null,
    p2: previo.p2 || {},
    p3: previo.p3 || {},
    p3b: previo.p3b || {},
  };

  if (editando) escenarios[indiceEdicion] = fila;
  else escenarios.push(fila);

  limpiarFormularioT1(false);
  pintarEscenarios();
}

function pintarEscenarios() {
  const tbody = document.getElementById("t1Tbody");
  const cont = { Inaceptable: 0, Alto: 0, Bajo: 0, Aceptable: 0 };
  tbody.innerHTML = "";

  if (escenarios.length === 0) {
    tbody.innerHTML = `<tr><td colspan="11" class="text-center text-muted py-4">
      Aún no has agregado escenarios</td></tr>`;
  } else {
    escenarios.forEach((f, i) => {
      const visual = clasificacionVisual(f.puntaje);
      cont[visual.nivel]++;
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${i + 1}</td>
        <td>${f.categoria}</td>
        <td>${f.escenario}</td>
        <td>${f.severidad}</td>
        <td>${f.probabilidad}</td>
        <td>${f.frecuencia}</td>
        <td>${f.personas}</td>
        <td class="text-center fw-bold">${f.puntaje}</td>
        <td><span class="badge-riesgo ${visual.clase.replace("nivel-", "")}">${visual.texto}</span></td>
        <td class="text-center">${celdaFoto(f.imgP1)}</td>
        <td class="text-center">
          <button class="icono-accion icono-editar"   data-i="${i}" title="Editar">
            <i class="fa-solid fa-pencil"></i></button>
          <button class="icono-accion icono-eliminar" data-i="${i}" title="Quitar">
            <i class="fa-regular fa-trash-can"></i></button>
        </td>`;
      tbody.appendChild(tr);
    });
  }

  tbody
    .querySelectorAll(".icono-editar")
    .forEach((b) =>
      b.addEventListener("click", () => editarEscenario(+b.dataset.i)),
    );
  tbody
    .querySelectorAll(".icono-eliminar")
    .forEach((b) =>
      b.addEventListener("click", () => quitarEscenario(+b.dataset.i)),
    );

  genericos.forEach((g) => {
    const p = puntajeGenerico(g, "probabilidades", g.idProbabilidad);
    if (p !== null) cont[clasificacionVisual(p).nivel]++;
  });

  document.getElementById("resInaceptables").textContent = cont.Inaceptable;
  document.getElementById("resAltos").textContent = cont.Alto;
  document.getElementById("resBajos").textContent = cont.Bajo;
  document.getElementById("resAceptables").textContent = cont.Aceptable;
  document.getElementById("t1PieTabla").textContent =
    `Mostrando 1 a ${escenarios.length} de ${escenarios.length} registros`;
}

function editarEscenario(i) {
  const f = escenarios[i];
  document.getElementById("t1Seccion").value = f.idSeccion;
  pintarIdEquipo();
  [
    "Categoria",
    "Consecuencia",
    "Mecanismo",
    "Fuente",
    "Severidad",
    "Probabilidad",
    "Frecuencia",
    "Personas",
  ].forEach((c) => (document.getElementById("t1" + c).value = f["id" + c]));
  armarEscenario();
  calcularNivelVivo();

  imgEscTmp = null;
  const prev = document.getElementById("t1ImagenPreview");

  if (f.imgP1 instanceof File) {
    prev.src = URL.createObjectURL(f.imgP1);
    prev.style.display = "inline-block";
  } else if (typeof f.imgP1 === "string" && f.imgP1) {
    prev.src = f.imgP1;
    prev.style.display = "inline-block";
  } else {
    prev.style.display = "none";
  }

  document.getElementById("t1Imagen").value = "";

  indiceEdicion = i;
  entrarEdicionT1();
}

async function quitarEscenario(i) {
  if (!(await confirmarQuitar("¿Quitar el escenario?"))) return;
  escenarios.splice(i, 1);
  indiceEdicion = null;
  salirEdicionT1();
  pintarEscenarios();
}

function limpiarFormularioT1(avisar = true) {
  [
    "t1Categoria",
    "t1Consecuencia",
    "t1Mecanismo",
    "t1Fuente",
    "t1Severidad",
    "t1Probabilidad",
    "t1Frecuencia",
    "t1Personas",
  ].forEach((id) => (document.getElementById(id).value = ""));
  document.getElementById("t1Escenario").value = "";
  imgEscTmp = null;
  document.getElementById("t1Imagen").value = "";
  document.getElementById("t1ImagenPreview").style.display = "none";
  salirEdicionT1();
  calcularNivelVivo();
  if (avisar === true) {
    Swal.fire({
      icon: "info",
      title: "Formulario limpio",
      timer: 1100,
      showConfirmButton: false,
    });
  }
}

/* ============================================================
   PELIGROS GENÉRICOS (únicos por tab)
   ============================================================ */
function puntajeGenerico(g, catalogoCuartoFactor, idCuartoFactor) {
  const s = valorDe("severidades", g.idSeveridad);
  const p = valorDe(catalogoCuartoFactor, idCuartoFactor);
  const f = valorDe("frecuencias", g.idFrecuencia);
  const n = valorDe("personasExpuestas", g.idPersonas);
  if (s === null || p === null || f === null || n === null) return null;
  return redondear(s * p * f * n);
}

function pintarGenericosT1() {
  const tbody = document.getElementById("t1TbodyGen");
  if (!tbody) return;
  tbody.innerHTML = "";
  genericos.forEach((g) => {
    const p = puntajeGenerico(g, "probabilidades", g.idProbabilidad);
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${g.orden}</td>
      <td>${selFijo("categoriasPeligro", g.idCategoria)}</td>
      <td><small>${g.escenario}</small></td>
      <td>${selFijo("severidades", g.idSeveridad)}</td>
      <td>${selFijo("probabilidades", g.idProbabilidad)}</td>
      <td>${selFijo("frecuencias", g.idFrecuencia)}</td>
      <td>${selFijo("personasExpuestas", g.idPersonas)}</td>
      <td class="text-center fw-bold">${p ?? "—"}</td>
      <td class="text-center">${badgePuntaje(p)}</td>`;
    tbody.appendChild(tr);
  });
  const suma = sumaGenericos("probabilidades", "idProbabilidad");
  const completos = genericos.filter(
    (g) => puntajeGenerico(g, "probabilidades", g.idProbabilidad) !== null,
  ).length;
  document.getElementById("t1PieGen").innerHTML =
    `${completos} de ${genericos.length} completos — <b>Puntaje genéricos: ${suma}</b>`;
}

function selFijo(catalogo, sel) {
  return `<select class="form-select form-select-sm" disabled>
            <option>${textoDe(catalogo, sel)}</option>
          </select>`;
}

function pintarGenericosT2() {
  const tbody = document.getElementById("t2TbodyGen");
  if (!tbody) return;
  tbody.innerHTML = "";
  genericos.forEach((g, i) => {
    const p = puntajeGenerico(g, "criteriosGuarda", g.idCriterioGuarda);
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${g.orden}</td>
      <td><small>${g.escenario}</small></td>
      <td>${selectHTML("criteriosGuarda", g.idCriterioGuarda, "idCriterioGuarda", i)}</td>
      <td><small>${textoDe("severidades", g.idSeveridad)}</small></td>
      <td><small>${textoDe("frecuencias", g.idFrecuencia)}</small></td>
      <td><small>${textoDe("personasExpuestas", g.idPersonas)}</small></td>
      <td class="text-center fw-bold">${p ?? "—"}</td>
      <td class="text-center">${badgePuntaje(p)}</td>`;
    tbody.appendChild(tr);
  });
  conectarSelectsGen(tbody, pintarGenericosT2);
  const suma = sumaGenericos("criteriosGuarda", "idCriterioGuarda");
  const completos = genericos.filter(
    (g) => puntajeGenerico(g, "criteriosGuarda", g.idCriterioGuarda) !== null,
  ).length;
  document.getElementById("t2PieGen").innerHTML =
    `${completos} de ${genericos.length} completos — <b>Puntaje con guardas: ${suma}</b>`;
}

function pintarGenericosT3() {
  const tbody = document.getElementById("t3TbodyGen");
  if (!tbody) return;
  tbody.innerHTML = "";
  genericos.forEach((g, i) => {
    const p = puntajeGenerico(g, "medidasMitigacion", g.idMedida);
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${g.orden}</td>
      <td><small>${g.escenario}</small></td>
      <td>${selectHTML("medidasMitigacion", g.idMedida, "idMedida", i)}</td>
      <td><small>${textoDe("severidades", g.idSeveridad)}</small></td>
      <td><small>${textoDe("frecuencias", g.idFrecuencia)}</small></td>
      <td><small>${textoDe("personasExpuestas", g.idPersonas)}</small></td>
      <td class="text-center fw-bold">${p ?? "—"}</td>
      <td class="text-center">${badgePuntaje(p)}</td>`;
    tbody.appendChild(tr);
  });
  conectarSelectsGen(tbody, pintarGenericosT3);
  const suma = sumaGenericos("medidasMitigacion", "idMedida");
  const completos = genericos.filter(
    (g) => puntajeGenerico(g, "medidasMitigacion", g.idMedida) !== null,
  ).length;
  document.getElementById("t3PieGen").innerHTML =
    `${completos} de ${genericos.length} completos — <b>Puntaje con ingeniería: ${suma}</b>`;
}

function conectarSelectsGen(tbody, repintar) {
  tbody.querySelectorAll(".sel-gen").forEach((s) =>
    s.addEventListener("change", () => {
      genericos[+s.dataset.i][s.dataset.campo] = s.value;
      repintar();
    }),
  );
}

function sumaGenericos(catalogo, campo) {
  return redondear(
    genericos.reduce(
      (t, g) => t + (puntajeGenerico(g, catalogo, g[campo]) ?? 0),
      0,
    ),
  );
}

/* ============================================================
   CONTINUAR PASO 1
   ============================================================ */
function continuarPaso1() {
  if (escenarios.length === 0) {
    mostrarAviso("Agrega al menos un escenario de riesgo antes de continuar");
    return;
  }
  const incompletos = genericos.filter(
    (g) => puntajeGenerico(g, "probabilidades", g.idProbabilidad) === null,
  );
  if (incompletos.length > 0) {
    mostrarAviso(
      `Faltan ${incompletos.length} peligro(s) genérico(s) por completar`,
    );
    return;
  }

  const sumaEsc = redondear(escenarios.reduce((s, f) => s + f.puntaje, 0));
  const total = redondear(
    sumaEsc + sumaGenericos("probabilidades", "idProbabilidad"),
  );

  payloadRARR.paso1 = {
    idMaquina: valor("t1Maquina"),
    maquina: textoSeleccionado("t1Maquina"),
    idSeccion: escenarios[0].idSeccion,
    seccion: escenarios[0].seccion,
    idEquipo: escenarios[0].idEquipo,
    escenarios: [], // se llena en registrarRARR con p2/p3/p3b ya completos
    genericos: [...genericos],
    marcadorPuro: total,
  };

  Swal.fire({
    icon: "success",
    title: "Paso 1 completo",
    html: `<b>Escenarios:</b> ${escenarios.length} — <b>Marcador Peligro Puro:</b> ${total} (${clasificacionVisual(total).texto})`,
    confirmButtonText: "Ir al Paso 2",
    confirmButtonColor: "#1a56db",
  }).then(() => document.querySelector('[data-bs-target="#tab2"]').click());
}

/* ============================================================
   TAB 2 — TARJETAS POR ESCENARIO
   ============================================================ */
function heredarContextoPaso2() {
  if (!payloadRARR.paso1) return;
  document.getElementById("t2Maquina").value = payloadRARR.paso1.maquina;
  document.getElementById("t2Componente").value = payloadRARR.paso1.seccion;
  document.getElementById("t2IdEquipo").value = payloadRARR.paso1.idEquipo;
  document.getElementById("t2FechaUltima").value = fechaHoy();
}

function heredarContextoPaso3() {
  if (!payloadRARR.paso1) return;
  const set = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.value = val;
  };
  set("t3Maquina", payloadRARR.paso1.maquina);
  set("t3Componente", payloadRARR.paso1.seccion);
  set("t3IdEquipo", payloadRARR.paso1.idEquipo);
  set("t3FechaUltima", fechaHoy());
}

function fechaHoy() {
  const d = new Date();
  return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}`;
}

function pintarCardsT2() {
  const cont = document.getElementById("t2Cards");
  if (!cont) return;
  if (escenarios.length === 0) {
    cont.innerHTML = `<div class="text-center text-muted py-4">Agrega escenarios en el Paso 1</div>`;
    return;
  }
  cont.innerHTML = escenarios
    .map((f, i) => {
      f.p2 = f.p2 || {};
      const punt = puntajeP2(f);
      const v = punt !== null ? clasificacionVisual(punt) : null;
      return `
      <div class="card-seccion" style="margin-bottom:2.5rem;border:1px solid #2631c9;border-radius:10px">
      <div class="encabezado"><i class="fa-solid fa-user-shield"></i>Escenario ${i + 1}: <small class="ms-2">${f.escenario}</small></div>
        <div class="cuerpo">
          <div class="subtexto">Severidad: <b>${f.severidad}</b> · Frecuencia: <b>${f.frecuencia}</b> · Personas: <b>${f.personas}</b></div>

          <div class="encabezado ps-0 mt-2" style="font-size:0.98rem"><i class="fa-solid fa-gears"></i>Evaluación de la protección actual</div>
          <div class="row g-3 mt-1 align-items-end">
            <div class="col-lg-4">
              <label class="form-label">Descripción de Guarda Actual <span class="text-danger">*</span></label>
              <textarea class="form-control t2f" data-i="${i}" data-campo="descGuarda" rows="3">${f.p2.descGuarda || ""}</textarea>
            </div>
            <div class="col-lg-4">
              <label class="form-label">Criterio de Guarda Actual <span class="text-danger">*</span></label>
              <select class="form-select t2f" data-i="${i}" data-campo="idCriterioGuarda"><option value="">Seleccione</option>${optsHTML("criteriosGuarda", f.p2.idCriterioGuarda)}</select>
              <label class="form-label mt-2">Nivel de Desempeño de Seguridad <span class="text-danger">*</span></label>
              <select class="form-select t2f" data-i="${i}" data-campo="idSeguridadFuncional"><option value="">Seleccione</option>${optsHTML("seguridadFuncional", f.p2.idSeguridadFuncional)}</select>
            </div>
            <div class="col-lg-2">
              <label class="form-label">Puntaje</label>
              <div class="nivel-riesgo-box ${v ? v.clase : "nivel-nulo"}" id="t2Punt${i}"><span>${punt ?? "—"}</span></div>
            </div>
            <div class="col-lg-2">
              <label class="form-label">Nivel</label>
              <div class="nivel-riesgo-box ${v ? v.clase : "nivel-nulo"}" id="t2Niv${i}"><span>${v ? v.texto : "—"}</span></div>
            </div>
          </div>

          <hr class="my-3">
          <div class="encabezado ps-0 mt-2" style="font-size:0.98rem"><i class="fa-solid fa-gears"></i>Plan de Contención</div>
          <div class="row g-3 mt-1">
            <div class="col-lg-3">
              <label class="form-label">Acciones de Contención <span class="text-danger">*</span></label>
              <textarea class="form-control t2f" data-i="${i}" data-campo="accionesContencion" rows="3">${f.p2.accionesContencion || ""}</textarea>
            </div>
            <div class="col-lg-2">
              <label class="form-label">Progreso (%)</label>
              <input type="number" min="0" max="100" class="form-control t2f" data-i="${i}" data-campo="avance" value="${f.p2.avance ?? 0}">
              <div class="avance-linea mt-1">
                <div class="progress progress-rarr flex-grow-1"><div class="progress-bar" id="t2Barra${i}" style="width:${f.p2.avance ?? 0}%"></div></div>
                <span class="pct" id="t2Pct${i}">${f.p2.avance ?? 0}%</span>
              </div>
            </div>
            <div class="col-lg-3">
              <label class="form-label">Medidas de Mitigación <span class="text-danger">*</span></label>
              <textarea class="form-control t2f" data-i="${i}" data-campo="mitigacion" rows="3">${f.p2.mitigacion || ""}</textarea>
            </div>
            <div class="col-lg-2">
              <label class="form-label">IBM Responsable <span class="text-danger">*</span></label>
              <input type="text" class="form-control t2ibm" data-i="${i}" value="${f.p2.ibm || ""}" placeholder="IBM">
              <input type="text" class="form-control input-solo-lectura mt-2" id="t2Resp${i}" readonly value="${f.p2.responsable || ""}" placeholder="Nombre">
            </div>
            <div class="col-lg-2">
              <label class="form-label">Fecha Impl. <span class="text-danger">*</span></label>
              <input type="date" class="form-control t2f" data-i="${i}" data-campo="fecha" value="${f.p2.fecha || ""}">
            </div>
          </div>
        </div>
      </div>`;
    })
    .join("");

  cont.querySelectorAll(".t2f").forEach((el) =>
    el.addEventListener("change", () => {
      const i = +el.dataset.i;
      escenarios[i].p2[el.dataset.campo] = el.value;
      if (el.dataset.campo === "avance") {
        let pct = Math.max(0, Math.min(100, parseInt(el.value, 10) || 0));
        document.getElementById("t2Barra" + i).style.width = pct + "%";
        document.getElementById("t2Pct" + i).textContent = pct + "%";
      }
      actualizarPuntajeT2(i);
    }),
  );
  cont.querySelectorAll(".t2ibm").forEach((el) =>
    el.addEventListener("blur", async () => {
      const i = +el.dataset.i;
      escenarios[i].p2.ibm = el.value.trim();
      const nombre = await nombreEmpleado(el.value.trim());
      escenarios[i].p2.responsable = nombre;
      document.getElementById("t2Resp" + i).value = nombre;
    }),
  );
}

function actualizarPuntajeT2(i) {
  const punt = puntajeP2(escenarios[i]);
  const v = punt !== null ? clasificacionVisual(punt) : null;
  const bp = document.getElementById("t2Punt" + i);
  const bn = document.getElementById("t2Niv" + i);
  bp.className = "nivel-riesgo-box " + (v ? v.clase : "nivel-nulo");
  bp.innerHTML = `<span>${punt ?? "—"}</span>`;
  bn.className = "nivel-riesgo-box " + (v ? v.clase : "nivel-nulo");
  bn.innerHTML = `<span>${v ? v.texto : "—"}</span>`;
}

function continuarPaso2() {
  if (!payloadRARR.paso1) {
    mostrarAviso("Primero completa el Paso 1");
    return;
  }
  for (let i = 0; i < escenarios.length; i++) {
    const p = escenarios[i].p2 || {};
    if (
      !p.descGuarda ||
      !p.idCriterioGuarda ||
      !p.idSeguridadFuncional ||
      !p.accionesContencion ||
      !p.mitigacion ||
      !p.ibm ||
      !p.responsable ||
      !p.fecha
    ) {
      mostrarAviso(`Completa la evaluación del Escenario ${i + 1}`);
      return;
    }
    const av = parseInt(p.avance, 10) || 0;
    if (av < 0 || av > 100) {
      mostrarAviso(
        `El progreso del Escenario ${i + 1} debe estar entre 0 y 100`,
      );
      return;
    }
  }
  const sinCriterio = genericos.filter(
    (g) => puntajeGenerico(g, "criteriosGuarda", g.idCriterioGuarda) === null,
  );
  if (sinCriterio.length > 0) {
    mostrarAviso(
      `Faltan ${sinCriterio.length} peligro(s) genérico(s) sin criterio de guarda`,
    );
    return;
  }

  const sumaEval = redondear(
    escenarios.reduce((s, f) => s + (puntajeP2(f) ?? 0), 0),
  );
  const total = redondear(
    sumaEval + sumaGenericos("criteriosGuarda", "idCriterioGuarda"),
  );

  payloadRARR.paso2 = {
    genericos: genericos.map((g) => ({
      idGenerico: g.idGenerico,
      idCriterioGuarda: g.idCriterioGuarda,
    })),
    marcadorGuardas: total,
  };

  Swal.fire({
    icon: "success",
    title: "Paso 2 completo",
    html: `<b>Marcador con Guardas:</b> ${total} (${clasificacionVisual(total).texto})`,
    confirmButtonText: "Ir al Paso 3",
    confirmButtonColor: "#1a56db",
  }).then(() => document.querySelector('[data-bs-target="#tab3"]').click());
}

/* ============================================================
   TAB 3 — CONTROLES + PLAN, UN DIV POR ESCENARIO
   ============================================================ */
function pintarCardsT3() {
  const cont = document.getElementById("t3Cards");
  if (!cont) return;
  if (escenarios.length === 0) {
    cont.innerHTML = `<div class="text-center text-muted py-4">Agrega escenarios en el Paso 1</div>`;
    return;
  }
  cont.innerHTML = escenarios
    .map((f, i) => {
      f.p3 = f.p3 || {};
      f.p3b = f.p3b || {};
      const punt = puntajeP3(f);
      const v = punt !== null ? clasificacionVisual(punt) : null;

      const prev =
        f.imgP3 instanceof File
          ? `src="${URL.createObjectURL(f.imgP3)}" style="display:inline-block;max-height:90px;border-radius:6px"`
          : typeof f.imgP3 === "string" && f.imgP3
            ? `src="${f.imgP3}" style="display:inline-block;max-height:90px;border-radius:6px"`
            : `style="display:none"`;
      return `
      <div class="card-seccion" style="margin-bottom:2.5rem;border:1px solid #2631c9;border-radius:10px">
        <div class="encabezado"><i class="fa-solid fa-user-shield"></i>Escenario ${i + 1}: <small class="ms-2">${f.escenario}</small></div>
        <div class="cuerpo">
          <div class="subtexto">Severidad: <b>${f.severidad}</b> · Frecuencia: <b>${f.frecuencia}</b> · Personas: <b>${f.personas}</b></div>

          <div class="encabezado ps-0 mt-2" style="font-size:0.98rem"><i class="fa-solid fa-gears"></i>Reducción de Riesgo por Controles de Ingeniería</div>
          <div class="row g-3 mt-1 align-items-end">
            <div class="col-lg-4"><label class="form-label">Medida de mitigación <span class="text-danger">*</span></label>
              <select class="form-select t3af" data-i="${i}" data-campo="idMedida"><option value="">Seleccione</option>${optsHTML("medidasMitigacion", f.p3.idMedida)}</select></div>
            <div class="col-lg-2"><label class="form-label">Fecha Impl. <span class="text-danger">*</span></label>
              <input type="date" class="form-control t3af" data-i="${i}" data-campo="fecha" value="${f.p3.fecha || ""}"></div>
            <div class="col-lg-2"><label class="form-label">Inversión</label>
              <input type="number" min="0" step="0.01" class="form-control t3af" data-i="${i}" data-campo="inversion" value="${f.p3.inversion || ""}"></div>
            <div class="col-lg-2"><label class="form-label">Estatus <span class="text-danger">*</span></label>
              <select class="form-select t3af" data-i="${i}" data-campo="idEstatus"><option value="">Seleccione</option>${optsHTML("estatus", f.p3.idEstatus)}</select></div>
            <div class="col-lg-1"><label class="form-label">Puntaje</label>
              <div class="nivel-riesgo-box ${v ? v.clase : "nivel-nulo"}" id="t3aPunt${i}"><span>${punt ?? "—"}</span></div></div>
            <div class="col-lg-1"><label class="form-label">Nivel</label>
              <div class="nivel-riesgo-box ${v ? v.clase : "nivel-nulo"}" id="t3aNiv${i}"><span>${v ? v.texto : "—"}</span></div></div>
          </div>
          <div class="row g-3 mt-1 align-items-center">
            <div class="col-lg-5"><label class="form-label">Foto del control <span class="text-danger">*</span></label>
              <input type="file" accept="image/*" class="form-control t3aimg" data-i="${i}">
              ${f.imgP3 && !(f.imgP3 instanceof File) ? `<div class="subtexto text-success mt-1">Imagen cargada. Sube otra solo para reemplazarla.</div>` : ""}</div>
            <div class="col-lg-4 text-center"><img id="t3aPrev${i}" ${prev}></div>
          </div>

          <hr class="my-3">
          <div class="encabezado ps-0" style="font-size:0.98rem"><i class="fa-solid fa-gears"></i>Plan de Acción – Solución de Diseño Ideal</div>
          <div class="row g-3 align-items-end">
            <div class="col-lg-4"><label class="form-label">Acción a realizar <span class="text-danger">*</span></label>
              <textarea class="form-control t3bf" data-i="${i}" data-campo="descripcion" rows="2">${f.p3b.descripcion || ""}</textarea></div>
            <div class="col-lg-3"><label class="form-label">IBM Responsable <span class="text-danger">*</span></label>
              <input type="text" class="form-control t3bibm" data-i="${i}" value="${f.p3b.ibm || ""}" placeholder="IBM">
              <input type="text" class="form-control input-solo-lectura mt-2" id="t3bResp${i}" readonly value="${f.p3b.responsable || ""}" placeholder="Nombre"></div>
            <div class="col-lg-2"><label class="form-label">Fecha objetivo <span class="text-danger">*</span></label>
              <input type="date" class="form-control t3bf" data-i="${i}" data-campo="fecha" value="${f.p3b.fecha || ""}"></div>
            <div class="col-lg-3"><label class="form-label">Estatus <span class="text-danger">*</span></label>
              <select class="form-select t3bf" data-i="${i}" data-campo="idEstatus"><option value="">Seleccione</option>${optsHTML("estatus", f.p3b.idEstatus)}</select></div>
          </div>
        </div>
      </div>`;
    })
    .join("");

  cont.querySelectorAll(".t3af").forEach((el) =>
    el.addEventListener("change", () => {
      escenarios[+el.dataset.i].p3[el.dataset.campo] = el.value;
      actualizarPuntajeT3a(+el.dataset.i);
    }),
  );
  cont.querySelectorAll(".t3aimg").forEach((el) =>
    el.addEventListener("change", (e) => {
      const i = +el.dataset.i;
      const r = leerImagen(e, "t3aPrev" + i);
      if (r instanceof File) escenarios[i].imgP3 = r;
    }),
  );
  cont.querySelectorAll(".t3bf").forEach((el) =>
    el.addEventListener("change", () => {
      escenarios[+el.dataset.i].p3b[el.dataset.campo] = el.value;
    }),
  );
  cont.querySelectorAll(".t3bibm").forEach((el) =>
    el.addEventListener("blur", async () => {
      const i = +el.dataset.i;
      escenarios[i].p3b.ibm = el.value.trim();
      const nombre = await nombreEmpleado(el.value.trim());
      escenarios[i].p3b.responsable = nombre;
      document.getElementById("t3bResp" + i).value = nombre;
    }),
  );
}

function actualizarPuntajeT3a(i) {
  const punt = puntajeP3(escenarios[i]);
  const v = punt !== null ? clasificacionVisual(punt) : null;
  const bp = document.getElementById("t3aPunt" + i);
  const bn = document.getElementById("t3aNiv" + i);
  bp.className = "nivel-riesgo-box " + (v ? v.clase : "nivel-nulo");
  bp.innerHTML = `<span>${punt ?? "—"}</span>`;
  if (bn) {
    bn.className = "nivel-riesgo-box " + (v ? v.clase : "nivel-nulo");
    bn.innerHTML = `<span>${v ? v.texto : "—"}</span>`;
  }
}

/* ============================================================
   REGISTRAR RARR
   ============================================================ */
async function registrarRARR() {
  if (!payloadRARR.paso1 || !payloadRARR.paso2) {
    mostrarAviso("Completa los pasos 1 y 2 antes de registrar");
    return;
  }
  for (let i = 0; i < escenarios.length; i++) {
    const p3 = escenarios[i].p3 || {};
    const p3b = escenarios[i].p3b || {};
    if (!p3.idMedida || !p3.fecha || !p3.idEstatus) {
      mostrarAviso(`Completa el control de ingeniería del Escenario ${i + 1}`);
      return;
    }
    if (!escenarios[i].imgP3) {
      mostrarAviso(`Falta la foto del control del Escenario ${i + 1}`);
      return;
    }
    if (
      !p3b.descripcion ||
      !p3b.ibm ||
      !p3b.responsable ||
      !p3b.fecha ||
      !p3b.idEstatus
    ) {
      mostrarAviso(`Completa el plan de acción del Escenario ${i + 1}`);
      return;
    }
  }
  const sinMedida = genericos.filter(
    (g) => puntajeGenerico(g, "medidasMitigacion", g.idMedida) === null,
  );
  if (sinMedida.length > 0) {
    mostrarAviso(
      `Faltan ${sinMedida.length} peligro(s) genérico(s) sin medida de mitigación`,
    );
    return;
  }

  const sumaSol = redondear(
    escenarios.reduce((s, f) => s + (puntajeP3(f) ?? 0), 0),
  );
  const marcadorIng = redondear(
    sumaSol + sumaGenericos("medidasMitigacion", "idMedida"),
  );

  payloadRARR.paso3 = {
    genericos: genericos.map((g) => ({
      idGenerico: g.idGenerico,
      idMedida: g.idMedida,
    })),
    marcadorIngenieria: marcadorIng,
  };

  /* Los escenarios llevan su p2/p3/p3b adentro*/
  payloadRARR.paso1.escenarios = escenarios.map((f) => {
    const { imgP1, imgP3, ...resto } = f;
    return resto;
  });

  const p1 = payloadRARR.paso1,
    p2 = payloadRARR.paso2;
  const confirmar = await Swal.fire({
    title: idRARREdicion ? "Actualizar RARR" : "Resumen del RARR",
    html: `
    <div style="text-align:left;font-size:0.9rem">
      <b>Equipo:</b> ${p1.idEquipo}<br>
      <b>Máquina:</b> ${p1.maquina} &nbsp; <b>Sección:</b> ${p1.seccion}<hr>
      <table class="table table-sm">
        <thead><tr><th>Paso</th><th>Marcador</th><th>Nivel</th></tr></thead>
        <tbody>
          <tr><td style="text-align:left">1 — Peligro Puro</td><td><b>${p1.marcadorPuro}</b></td><td>${clasificacionVisual(p1.marcadorPuro).texto}</td></tr>
          <tr><td style="text-align:left">2 — Con Guardas</td><td><b>${p2.marcadorGuardas}</b></td><td>${clasificacionVisual(p2.marcadorGuardas).texto}</td></tr>
          <tr><td style="text-align:left">3 — Con Ingeniería</td><td><b>${marcadorIng}</b></td><td>${clasificacionVisual(marcadorIng).texto}</td></tr>
        </tbody>
      </table>
      <b>Escenarios:</b> ${escenarios.length} &nbsp; <b>Genéricos:</b> ${genericos.length}<br>
      <b>Inversión total:</b> $ ${escenarios.reduce((s, f) => s + (parseFloat(f.p3.inversion) || 0), 0).toLocaleString("es-MX", { minimumFractionDigits: 2 })}
    </div>`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: idRARREdicion
      ? "Confirmar y actualizar"
      : "Confirmar y registrar",
    cancelButtonText: "Revisar",
    confirmButtonColor: "#1a56db",
    width: 650,
  });

  if (!confirmar.isConfirmed) return;

  try {
    payloadRARR.idRARR = idRARREdicion;
    const datos = { payload: JSON.stringify(payloadRARR) };
    escenarios.forEach((f, i) => {
      if (f.imgP1 instanceof File) datos[`imgP1_${i}`] = f.imgP1;
      if (f.imgP3 instanceof File) datos[`imgP3_${i}`] = f.imgP3;
    });

    const res = await llamarPOST(API.registrarRARR, datos);

    await Swal.fire({
      icon: "success",
      title: idRARREdicion ? "RARR actualizado" : "RARR registrado",
      html: `Folio RARR: <b>${res.data.idRARR}</b>`,
      confirmButtonColor: "#1a56db",
    });
    location.reload();
  } catch (e) {
    mostrarError(e.message);
  }
}

/* ============================================================
   TAB 4 — ANÁLISIS DE REGISTROS RARR
   ============================================================ */
let gauges = { g1: null, g2: null, g3: null };
let rarrActual = null;

const CORTES = [0, 5, 50, 500, 4500];
const COLORES_BANDA = ["#23923d", "#e8c31a", "#f0930d", "#dc3545"];

function fraccionGauge(v) {
  if (v <= 0) return 0;
  for (let i = 0; i < 4; i++) {
    if (v <= CORTES[i + 1]) {
      const lo = CORTES[i];
      const hi = CORTES[i + 1];
      return (i + (v - lo) / (hi - lo)) * 0.25;
    }
  }
  return 1;
}

function nivelDeMarcador(v) {
  if (v > 500) return { texto: "Inaceptable", color: "#dc3545" };
  if (v > 50) return { texto: "Alto", color: "#f0930d" };
  if (v > 5) return { texto: "Bajo", color: "#e8c31a" };
  return { texto: "Aceptable", color: "#23923d" };
}

const pluginAguja = {
  id: "aguja",
  afterDatasetDraw(chart) {
    const v = chart.options.plugins.aguja?.valor;
    if (v === null || v === undefined) return;
    const meta = chart.getDatasetMeta(0).data[0];
    if (!meta) return;
    const { ctx } = chart;
    const cx = meta.x;
    const cy = meta.y;
    const r = meta.outerRadius * 0.9;
    const ang = Math.PI + Math.PI * fraccionGauge(v);
    ctx.save();
    ctx.translate(cx, cy);
    ctx.rotate(ang);
    ctx.beginPath();
    ctx.moveTo(0, -3);
    ctx.lineTo(r, 0);
    ctx.lineTo(0, 3);
    ctx.fillStyle = "#1f2430";
    ctx.fill();
    ctx.restore();
    ctx.beginPath();
    ctx.arc(cx, cy, 5, 0, Math.PI * 2);
    ctx.fillStyle = "#1f2430";
    ctx.fill();
  },
};
Chart.register(pluginAguja);

function crearGauge(canvasId) {
  const ctx = document.getElementById(canvasId).getContext("2d");
  return new Chart(ctx, {
    type: "doughnut",
    data: {
      datasets: [
        { data: [1, 1, 1, 1], backgroundColor: COLORES_BANDA, borderWidth: 0 },
      ],
    },
    options: {
      rotation: -90,
      circumference: 180,
      cutout: "62%",
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 300 },
      plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
        aguja: { valor: null },
      },
    },
  });
}

function pintarGauge(chart, valor) {
  chart.options.plugins.aguja.valor = valor;
  chart.update();
}

async function cargarSeccionesTab4(idMaquina) {
  const slc = document.getElementById("t4Seccion");
  slc.innerHTML = `<option value="">Seleccione una opción</option>`;
  document.getElementById("t4IdEquipo").value = "";
  ocultarRARR(
    "Selecciona una máquina y su sección para ver el RARR registrado",
  );
  if (idMaquina === "") return;
  try {
    const res = await llamarGET(API.getSeccionesRARR, { idMaquina });
    if (res.data.length === 0) {
      slc.innerHTML = `<option value="">Esta máquina no tiene secciones dadas de alta</option>`;
      return;
    }
    res.data.forEach((s) => {
      const opt = document.createElement("option");
      opt.value = s.IdSeccion;
      opt.textContent = s.Seccion;
      opt.dataset.idEquipo = s.IdEquipo;
      slc.appendChild(opt);
    });
  } catch (e) {
    mostrarError(e.message);
  }
}

async function cargarRARRTab4() {
  const slc = document.getElementById("t4Seccion");
  const opt = slc.options[slc.selectedIndex];
  const idEquipo = opt && opt.value !== "" ? opt.dataset.idEquipo : "";
  document.getElementById("t4IdEquipo").value = idEquipo;
  if (idEquipo === "") {
    ocultarRARR(
      "Selecciona una máquina y su sección para ver el RARR registrado",
    );
    return;
  }
  try {
    const res = await llamarGET(API.getRARRxEquipo, { idEquipo });
    if (!res.data) {
      ocultarRARR("Este equipo aún no tiene RARR registrado");
      return;
    }
    rarrActual = res.data;
    pintarTab4(res.data);
  } catch (e) {
    mostrarError(e.message);
  }
}

function ocultarRARR(mensaje) {
  rarrActual = null;
  document.getElementById("t4Contenido").style.display = "none";
  const aviso = document.getElementById("t4SinDatos");
  aviso.style.display = "block";
  aviso.textContent = mensaje;
}

function pintarTab4(d) {
  document.getElementById("t4SinDatos").style.display = "none";
  document.getElementById("t4Contenido").style.display = "block";

  if (!gauges.g1) {
    gauges.g1 = crearGauge("t4Gauge1");
    gauges.g2 = crearGauge("t4Gauge2");
    gauges.g3 = crearGauge("t4Gauge3");
  }

  const puro = d.paso1.marcadorPuro;
  const niv1 = nivelDeMarcador(puro);
  pintarGauge(gauges.g1, puro);
  const m1 = document.getElementById("t4Marcador1");
  m1.textContent = puro;
  m1.style.color = niv1.color;
  document.getElementById("t4Etiqueta1").textContent =
    `Peligro Puro — ${niv1.texto}`;
  document.getElementById("t4Etiqueta1").style.color = niv1.color;

  const c = d.paso1.conteo;
  const total = c.Aceptable + c.Bajo + c.Alto + c.Inaceptable;
  document.getElementById("t4TablaNiveles").innerHTML = `
    <tr><td>Riesgo Aceptable</td><td class="text-center">${c.Aceptable}</td></tr>
    <tr><td>Riesgo Bajo</td><td class="text-center">${c.Bajo}</td></tr>
    <tr><td>Riesgo Alto</td><td class="text-center">${c.Alto}</td></tr>
    <tr><td>Riesgo Inaceptable</td><td class="text-center">${c.Inaceptable}</td></tr>
    <tr class="fw-bold"><td>Total</td><td class="text-center">${total}</td></tr>`;

  const guardas = d.paso2.marcadorGuardas;
  pintarGauge(gauges.g2, guardas);
  const m2 = document.getElementById("t4Marcador2");
  m2.textContent = guardas ?? "—";
  if (guardas !== null) m2.style.color = nivelDeMarcador(guardas).color;
  document.getElementById("t4TablaGuardas").innerHTML = d.paso2.evaluaciones
    .length
    ? d.paso2.evaluaciones
        .map(
          (e) =>
            `<tr><td><small>${e.CriterioGuarda ?? "-"}</small></td><td class="text-center">${e.Calificacion ?? "—"}</td></tr>`,
        )
        .join("")
    : `<tr><td colspan="2" class="text-center text-muted">Sin evaluación</td></tr>`;

  const ing = d.paso3.marcadorIngenieria;
  pintarGauge(gauges.g3, ing);
  const m3 = document.getElementById("t4Marcador3");
  m3.textContent = ing ?? "—";
  if (ing !== null) m3.style.color = nivelDeMarcador(ing).color;
  document.getElementById("t4TablaControles").innerHTML = d.paso3.soluciones
    .length
    ? d.paso3.soluciones
        .map(
          (s) =>
            `<tr><td><small>${s.Descripcion ?? "-"}</small></td><td class="text-center"><span class="badge-estatus ${claseEstatus(s.Estatus, true)}">${s.Estatus}</span></td></tr>`,
        )
        .join("")
    : `<tr><td colspan="2" class="text-center text-muted">Sin controles</td></tr>`;

  document.getElementById("t4ResInaceptables").textContent = c.Inaceptable;
  document.getElementById("t4ResAltos").textContent = c.Alto;
  document.getElementById("t4ResBajos").textContent = c.Bajo;
  document.getElementById("t4ResAceptables").textContent = c.Aceptable;

  const av = d.paso2.avance;
  document.getElementById("t4BarraAvance").style.width = av + "%";
  document.getElementById("t4PctAvance").textContent = av + "%";
  document.getElementById("t4Inversion").textContent =
    "$ " +
    Number(d.paso3.inversionTotal).toLocaleString("es-MX", {
      minimumFractionDigits: 2,
    });

  const btnConcluir = document.getElementById("t4BtnConcluir");
  const concluido = d.rarr.Estatus === "Concluido";

  if (concluido) {
    btnConcluir.disabled = true;
    btnConcluir.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i>RARR Concluido`;
  } else {
    // Consulta de pendientes para habilitar/deshabilitar
    llamarGET(API.getPendientesRARR, { idEquipo: d.rarr.IdEquipo })
      .then((res) => {
        const r = res.data.resumen;
        if (r.totalPendientes > 0) {
          btnConcluir.disabled = true;
          btnConcluir.innerHTML = `<i class="fa-solid fa-lock me-1"></i>Faltan ${r.totalPendientes} pendiente(s)`;
          btnConcluir.title = `Paso 2: ${r.p2Pendientes} sin 100% · Paso 3: ${r.p3Pendientes} sin concluir`;
        } else {
          btnConcluir.disabled = false;
          btnConcluir.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i>Concluir RARR`;
        }
      })
      .catch(() => {
        btnConcluir.disabled = false;
        btnConcluir.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i>Concluir RARR`;
      });
  }
}

async function eliminarRARRCompleto() {
  if (!rarrActual) return;
  const idEquipo = rarrActual.rarr.IdEquipo;
  const c = await Swal.fire({
    icon: "warning",
    title: "¿Eliminar todo el RARR?",
    html: `Se borrará <b>permanentemente</b> todo lo registrado bajo <b>${idEquipo}</b>.<br><br>Esta acción no se puede deshacer.`,
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar todo",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#dc3545",
  });
  if (!c.isConfirmed) return;
  try {
    await llamarPOST(API.eliminarRARR, { idEquipo });
    await Swal.fire({
      icon: "success",
      title: "RARR eliminado",
      confirmButtonColor: "#1a56db",
    });
    document.getElementById("t4Seccion").value = "";
    document.getElementById("t4IdEquipo").value = "";
    ocultarRARR(
      "Selecciona una máquina y su sección para ver el RARR registrado",
    );
  } catch (e) {
    mostrarError(e.message);
  }
}

/* ============================================================
   EDICIÓN DEL RARR (desde el Tab 4)
   ============================================================ */
async function editarRARR() {
  if (!rarrActual) return;
  const c = await Swal.fire({
    icon: "question",
    title: "¿Editar este RARR?",
    html: `Se cargarán todos los datos de <b>${rarrActual.rarr.IdEquipo}</b> en los pasos 1, 2 y 3.<br><br>Al guardar, el RARR vuelve a estado <b>Pendiente</b> hasta que lo concluyas de nuevo.`,
    showCancelButton: true,
    confirmButtonText: "Sí, editar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#1a56db",
  });
  if (!c.isConfirmed) return;

  const d = rarrActual;
  idRARREdicion = d.rarr.IdRARR;

  /* Máquina y sección */
  const idMaquina = await maquinaDeEquipo(d.rarr.IdEquipo);
  document.getElementById("t1Maquina").value = idMaquina;
  await cargarSeccionesRARR(idMaquina);
  const slcSec = document.getElementById("t1Seccion");
  const optSec = [...slcSec.options].find(
    (o) => o.dataset.idEquipo === d.rarr.IdEquipo,
  );
  if (optSec) {
    slcSec.value = optSec.value;
    pintarIdEquipo();
  }

  const idSeccion = optSec ? optSec.value : "";
  const seccion = optSec ? optSec.textContent : d.rarr.SeccionEquipo;

  /* URL servible del binario, empatando por IdEscenario + Paso */
  const urlImg = (idEsc, paso) => {
    const tiene = (d.imagenes || []).some(
      (x) =>
        String(x.IdEscenario) === String(idEsc) &&
        String(x.Paso) === String(paso),
    );
    return tiene
      ? `${API.getImagenRARR}?idEscenario=${idEsc}&paso=${paso}`
      : null;
  };

  /* Escenarios propios con su p2/p3/p3b e imágenes existentes */
  escenarios = d.paso1.escenarios
    .filter((e) => e.EsGenerico == 0)
    .map((e) => {
      const ev =
        d.paso2.evaluaciones.find(
          (x) => String(x.IdEscenario) === String(e.IdEscenario),
        ) || {};
      const so =
        d.paso3.soluciones.find(
          (x) => String(x.IdEscenario) === String(e.IdEscenario),
        ) || {};
      const ac =
        d.paso3.acciones.find(
          (x) => String(x.IdEscenario) === String(e.IdEscenario),
        ) || {};
      return {
        idEscenario: e.IdEscenario,
        idSeccion: idSeccion,
        seccion: seccion,
        idEquipo: d.rarr.IdEquipo,
        idCategoria: String(e.IdCategoriaPeligro ?? ""),
        categoria: e.CategoriaPeligro,
        idConsecuencia: String(e.IdConsecuencia ?? ""),
        idMecanismo: String(e.IdMecanismo ?? ""),
        idFuente: String(e.IdFuente ?? ""),
        escenario: e.EscenarioRiesgo,
        idSeveridad: String(e.IdSeveridad ?? ""),
        severidad: e.Severidad,
        idProbabilidad: String(e.IdProbabilidad ?? ""),
        probabilidad: e.Probabilidad,
        idFrecuencia: String(e.IdFrecuencia ?? ""),
        frecuencia: e.Frecuencia,
        idPersonas: String(e.IdPersonas ?? ""),
        personas: e.PersonalExpuesto,
        puntaje: parseFloat(e.Calificacion),
        nivel: e.NivelRiesgo,
        imgP1: urlImg(e.IdEscenario, 1),
        imgP3: urlImg(e.IdEscenario, 3),
        p2: {
          descGuarda:
            ev.DescripcionHallazgo === "-" ? "" : ev.DescripcionHallazgo || "",
          idCriterioGuarda: String(ev.IdCriterioGuarda ?? ""),
          idSeguridadFuncional: String(ev.IdSeguridadFuncional ?? ""),
          accionesContencion:
            ev.AccionesPropuestas === "-" ? "" : ev.AccionesPropuestas || "",
          mitigacion:
            ev.MedidasMitigacion === "-" ? "" : ev.MedidasMitigacion || "",
          avance: parseInt(ev.PorcentajeAvance, 10) || 0,
          ibm: ev.IbmResponsable || "",
          responsable:
            ev.NombreResponsable === "-" ? "" : ev.NombreResponsable || "",
          fecha: soloFecha(ev.FechaCompromiso),
        },
        p3: {
          idMedida: String(so.IdMedidaMitigacion ?? ""),
          fecha: soloFecha(so.FechaImplementacion),
          inversion:
            so.InversionEstimada != null ? String(so.InversionEstimada) : "",
          idEstatus: String(so.IdEstatus ?? ""),
        },
        p3b: {
          descripcion: ac.Descripcion === "-" ? "" : ac.Descripcion || "",
          ibm: ac.IbmResponsable || "",
          responsable: ac.Responsable === "-" ? "" : ac.Responsable || "",
          fecha: soloFecha(ac.FechaImplementacion),
          idEstatus: String(ac.IdEstatus ?? ""),
        },
      };
    });

  /* Genéricos: rellenar sus selects desde los guardados */
  const guardados = d.paso1.escenarios.filter((e) => e.EsGenerico == 1);
  genericos.forEach((g) => {
    const s = guardados.find(
      (x) => String(x.IdGenerico) === String(g.idGenerico),
    );
    if (!s) return;
    g.idCategoria = String(s.IdCategoriaPeligro ?? "");
    g.idSeveridad = String(s.IdSeveridad ?? "");
    g.idProbabilidad = String(s.IdProbabilidad ?? "");
    g.idFrecuencia = String(s.IdFrecuencia ?? "");
    g.idPersonas = String(s.IdPersonas ?? "");
    g.idCriterioGuarda = String(s.IdCriterioGuarda ?? "");
    g.idMedida = String(s.IdMedidaMitigacion ?? "");
  });

  /* Payload de los pasos ya validados */
  payloadRARR.paso1 = {
    idMaquina: idMaquina,
    maquina: textoSeleccionado("t1Maquina"),
    idSeccion: idSeccion,
    seccion: seccion,
    idEquipo: d.rarr.IdEquipo,
    escenarios: [],
    genericos: [...genericos],
    marcadorPuro: parseFloat(d.paso1.marcadorPuro),
  };
  payloadRARR.paso2 = {
    genericos: genericos.map((g) => ({
      idGenerico: g.idGenerico,
      idCriterioGuarda: g.idCriterioGuarda,
    })),
    marcadorGuardas: parseFloat(d.paso2.marcadorGuardas),
  };

  pintarEscenarios();
  pintarGenericosT1();
  pintarCardsT2();
  pintarGenericosT2();
  pintarCardsT3();
  pintarGenericosT3();

  document.getElementById("t3BtnRegistrar").innerHTML =
    `<i class="fa-solid fa-floppy-disk me-1"></i>Actualizar RARR`;
  document.querySelector('[data-bs-target="#tab1"]').click();

  Swal.fire({
    icon: "success",
    title: "RARR cargado para edición",
    html: `Revisa los 3 pasos y presiona <b>Actualizar RARR</b> al final.`,
    confirmButtonColor: "#1a56db",
  });
}

async function maquinaDeEquipo(idEquipo) {
  const slc = document.getElementById("t1Maquina");
  for (const opt of slc.options) {
    if (opt.value === "") continue;
    try {
      const res = await llamarGET(API.getSeccionesRARR, {
        idMaquina: opt.value,
      });
      if (res.data.some((s) => s.IdEquipo === idEquipo)) return opt.value;
    } catch (e) {}
  }
  return "";
}

function soloFecha(f) {
  return f ? String(f).substring(0, 10) : "";
}

/* ============================================================
   CONCLUIR RARR (Tab 4)
   ============================================================ */
async function concluirRARR() {
  if (!rarrActual) return;
  const idEquipo = rarrActual.rarr.IdEquipo;
  if (rarrActual.rarr.Estatus === "Concluido") {
    mostrarAviso("Este RARR ya está concluido");
    return;
  }
  const c = await Swal.fire({
    icon: "question",
    title: "¿Concluir el RARR?",
    html: `El RARR de <b>${idEquipo}</b> quedará marcado como <b>Concluido</b>.`,
    showCancelButton: true,
    confirmButtonText: "Sí, concluir",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#1a56db",
  });
  if (!c.isConfirmed) return;
  try {
    await llamarPOST(API.concluirRARR, { idEquipo });
    await Swal.fire({
      icon: "success",
      title: "RARR concluido",
      confirmButtonColor: "#1a56db",
    });
    await cargarRARRTab4();
  } catch (e) {
    mostrarError(e.message);
  }
}

/* ============================================================
   UTILERÍAS
   ============================================================ */
async function confirmarQuitar(titulo) {
  const c = await Swal.fire({
    icon: "warning",
    title: titulo,
    showCancelButton: true,
    confirmButtonText: "Sí, quitar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#dc3545",
  });
  return c.isConfirmed;
}

function claseEstatus(estatus, rojoParaPendiente) {
  const e = (estatus || "").toLowerCase();
  if (e.includes("proceso")) return "est-enproceso";
  if (e.includes("conclu") || e.includes("complet")) return "est-completado";
  if (e.includes("cancel")) return "est-cancelado";
  return rojoParaPendiente ? "est-pendiente-rojo" : "est-pendiente";
}

function valor(id) {
  const el = document.getElementById(id);
  return el ? el.value.trim() : "";
}

function validarCampos(campos) {
  for (const c of campos) {
    if (valor(c.id) === "") {
      mostrarAviso(`El campo "${c.nombre}" es obligatorio`);
      document.getElementById(c.id).focus();
      return false;
    }
  }
  return true;
}

function mostrarAviso(mensaje) {
  Swal.fire({
    icon: "warning",
    title: "Campos incompletos",
    text: mensaje,
    confirmButtonColor: "#f0930d",
  });
}

function mostrarError(mensaje) {
  Swal.fire({
    icon: "error",
    title: "Ocurrió un problema",
    text: mensaje,
    confirmButtonColor: "#1a56db",
  });
}

/* ============================================================
   MODAL PERSONALIZAR
   ============================================================ */
let cfgTipo = null;
let cfgDatos = [];
let cfgMaquinas = [];

const CFG_META = {
  maquinas: { titulo: "Máquinas", soloLectura: true },
  secciones: { titulo: "Secciones", soloLectura: false },
  categorias: { titulo: "Categorías de Peligro", soloLectura: false },
  consecuencias: { titulo: "Consecuencias", soloLectura: false },
  mecanismos: { titulo: "Mecanismos", soloLectura: false },
  fuentes: { titulo: "Fuentes", soloLectura: false },
};

function registrarEventosConfig() {
  document
    .querySelectorAll(".cfg-btn")
    .forEach((b) =>
      b.addEventListener("click", () => abrirConfig(b.dataset.tipo)),
    );
  document
    .getElementById("cfgRegistro")
    .addEventListener("change", cargarRegistroConfig);
  document.getElementById("cfgBtnNuevo").addEventListener("click", () => {
    document.getElementById("cfgRegistro").value = "";
    pintarCamposConfig(null);
  });
  document.getElementById("cfgBtnCancelar").addEventListener("click", () => {
    document.getElementById("cfgRegistro").value = "";
    pintarCamposConfig(null);
  });
  document
    .getElementById("cfgBtnGuardar")
    .addEventListener("click", guardarConfig);
}

async function abrirConfig(tipo) {
  cfgTipo = tipo;
  document
    .querySelectorAll(".cfg-btn")
    .forEach((b) => b.classList.toggle("btn-azul", b.dataset.tipo === tipo));
  document.getElementById("cfgTitulo").textContent = CFG_META[tipo].titulo;
  document.getElementById("cfgVacio").style.display = "none";
  document.getElementById("cfgForm").style.display = "block";
  try {
    const res = await llamarGET(API.getConfig, { tipo });
    cfgDatos = res.data;
    if (tipo === "secciones" && cfgMaquinas.length === 0) {
      const m = await llamarGET(API.getConfig, { tipo: "maquinas" });
      cfgMaquinas = m.data;
    }
    const slc = document.getElementById("cfgRegistro");
    slc.innerHTML = `<option value="">Seleccione para editar</option>`;
    cfgDatos.forEach((f) => {
      const opt = document.createElement("option");
      opt.value = f.id;
      opt.textContent =
        tipo === "secciones"
          ? `${f.Maquina} — ${f.Descripcion} (${f.IdEquipo})`
          : f.Descripcion;
      slc.appendChild(opt);
    });
    pintarCamposConfig(null);
  } catch (e) {
    mostrarError(e.message);
  }
}

function cargarRegistroConfig() {
  const id = document.getElementById("cfgRegistro").value;
  if (id === "") {
    pintarCamposConfig(null);
    return;
  }
  const fila = cfgDatos.find((f) => String(f.id) === String(id));
  pintarCamposConfig(fila || null);
}

function pintarCamposConfig(fila) {
  const cont = document.getElementById("cfgCampos");
  const meta = CFG_META[cfgTipo];
  const btnGuardar = document.getElementById("cfgBtnGuardar");
  const btnNuevo = document.getElementById("cfgBtnNuevo");

  if (meta.soloLectura) {
    btnGuardar.style.display = "none";
    btnNuevo.style.display = "none";
    cont.innerHTML = fila
      ? `<div class="col-lg-6"><label class="form-label">Máquina</label>
           <input type="text" class="form-control input-solo-lectura" readonly value="${fila.Descripcion}"></div>
         <div class="col-lg-6"><label class="form-label">Departamento</label>
           <input type="text" class="form-control input-solo-lectura" readonly value="${fila.Departamento}"></div>
         <div class="col-12"><div class="subtexto">El catálogo de máquinas es compartido con otros módulos de la planta, así que desde aquí solo se consulta.</div></div>`
      : `<div class="col-12 text-muted">Selecciona una máquina para ver sus datos.</div>`;
    return;
  }

  btnGuardar.style.display = "";
  btnNuevo.style.display = "";

  if (cfgTipo === "secciones") {
    const bloqueado = fila && Number(fila.TieneRARR) > 0;
    const opts = cfgMaquinas
      .map(
        (m) =>
          `<option value="${m.id}" ${fila && String(m.id) === String(fila.NoMaquina) ? "selected" : ""}>${m.Descripcion}</option>`,
      )
      .join("");
    cont.innerHTML = `
      <div class="col-lg-6"><label class="form-label">Máquina <span class="text-danger">*</span></label>
        <select id="cfgMaquina" class="form-select" ${bloqueado ? "disabled" : ""}><option value="">Seleccione una máquina</option>${opts}</select></div>
      <div class="col-lg-6"><label class="form-label">Nombre de la Sección <span class="text-danger">*</span></label>
        <input type="text" id="cfgNombreSeccion" class="form-control" maxlength="150" value="${fila ? fila.Descripcion : ""}" placeholder="Ej. Desenrollador de Celulosa"></div>
      <div class="col-lg-4"><label class="form-label">Abreviatura <span class="text-danger">*</span></label>
        <input type="text" id="cfgAbreviatura" class="form-control text-uppercase" maxlength="5" value="${fila ? fila.Abreviatura : ""}" ${bloqueado ? "readonly" : ""} placeholder="PLP">
        <div class="subtexto mt-1">2 a 5 letras o números. Es el código del área, no se calcula.</div></div>
      <div class="col-lg-8"><label class="form-label">ID Equipo</label>
        <input type="text" id="cfgIdEquipo" class="form-control input-solo-lectura" readonly value="${fila ? fila.IdEquipo : ""}" placeholder="Se genera al guardar"></div>
      ${
        bloqueado
          ? `<div class="col-12"><div class="subtexto text-danger">Esta sección ya tiene un RARR registrado. Solo puedes cambiar su nombre: si cambiara la máquina o la abreviatura, el ID de equipo dejaría de coincidir con el del RARR.</div></div>`
          : ""
      }`;
    return;
  }

  cont.innerHTML = `
    <div class="col-12"><label class="form-label">Descripción <span class="text-danger">*</span></label>
      <textarea id="cfgDescripcion" class="form-control" rows="3" maxlength="200">${fila ? fila.Descripcion : ""}</textarea>
      <div class="subtexto mt-1">Máximo 200 caracteres.</div></div>`;
}

async function guardarConfig() {
  const id = document.getElementById("cfgRegistro").value;
  try {
    let res;
    if (cfgTipo === "secciones") {
      if (
        valor("cfgMaquina") === "" ||
        valor("cfgNombreSeccion") === "" ||
        valor("cfgAbreviatura") === ""
      ) {
        mostrarAviso("Máquina, nombre y abreviatura son obligatorios");
        return;
      }
      res = await llamarPOST(API.guardarSeccion, {
        id: id,
        idMaquina: valor("cfgMaquina"),
        nombreSeccion: valor("cfgNombreSeccion"),
        abreviatura: valor("cfgAbreviatura").toUpperCase(),
      });
    } else {
      if (valor("cfgDescripcion") === "") {
        mostrarAviso("La descripción es obligatoria");
        return;
      }
      res = await llamarPOST(API.guardarConfig, {
        tipo: cfgTipo,
        id: id,
        descripcion: valor("cfgDescripcion"),
      });
    }
    await Swal.fire({
      icon: "success",
      title: id === "" ? "Registro agregado" : "Registro actualizado",
      html: res.data.idEquipo ? `ID Equipo: <b>${res.data.idEquipo}</b>` : "",
      confirmButtonColor: "#1a56db",
    });
    await abrirConfig(cfgTipo);
    await cargarCatalogos();
  } catch (e) {
    mostrarError(e.message);
  }
}
