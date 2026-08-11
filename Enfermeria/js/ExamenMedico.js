import { Toolsjs } from "../../Tools/Tools.js";
import { Consultas, ExamenMedico } from "../module/index.js";
import { Firma } from "../../Modules/Autenticacion.js";
const Tools = new Toolsjs();
const ConsultasObj = new Consultas();
const ExamenMObj = new ExamenMedico();
// const FirmaObj = new Firma(canvas);

const folio = document.getElementById("id");
const noemp = document.getElementById("noemp");
const nombre = document.getElementById("nombre");
const departamento = document.getElementById("departamento");
const puesto = document.getElementById("puesto");
const maquina = document.getElementById("maquina");
const fechanaimiento = document.getElementById("fechanaimiento");
const lugarnac = document.getElementById("lugarnac");
const domicilio = document.getElementById("domicilio");
const escolaridad = document.getElementById("escolaridad");
// const religion = document.getElementById("religion");
const edad = document.getElementById("edad");
const tiposangre = document.getElementById("tiposangre");
const fechaingreso = document.getElementById("fechaingreso");
const problemasdesalud = document.getElementById("problemasdesalud");
const tomamedicamento = document.getElementById("tomamedicamento");
const tratamientomedico = document.getElementById("tratamientomedico");
const enfermedadcronica = document.getElementById("enfermedadcronica");
const tabaquismo = document.getElementById("tabaquismo");
const alcoholismo = document.getElementById("alcoholismo");
const altfisica = document.getElementById("altfisica");
const quirurgicos = document.getElementById("quirurgicos");
const traumaticos = document.getElementById("traumaticos");
const transfuciones = document.getElementById("transfuciones");
const antivioticos = document.getElementById("antivioticos");
const analgesitos = document.getElementById("analgesitos");
const antiinflamatorios = document.getElementById("antiinflamatorios");
const otrosalergias = document.getElementById("otrosalergias");
const alimentacion = document.getElementById("alimentacion");
const aseogeneral = document.getElementById("aseogeneral");
const hobbies = document.getElementById("hobbies");
const otrasactlaborales = document.getElementById("otrasactlaborales");
const incapacidades = document.getElementById("incapacidades");
const diagnostico = document.getElementById("diagnostico");
const diasIncapacidad = document.getElementById("diasIncapacidad");
const secuela = document.getElementById("secuela");
const rehabilitacion = document.getElementById("rehabilitacion");
const trayecto = document.getElementById("trayecto");
const enfgeneral = document.getElementById("enfgeneral");
const accidentetrabajo = document.getElementById("accidentetrabajo");
const enfermedadtrabajo = document.getElementById("enfermedadtrabajo");

let tos = document.querySelector('input[name="Tos"]:checked');
let expectoracion = document.querySelector(
  'input[name="expectoracion"]:checked',
);
let dolortoracico = document.querySelector(
  'input[name="dolortoracico"]:checked',
);
let taquicardia = document.querySelector('input[name="taquicardia"]:checked');
let disnea = document.querySelector('input[name="disnea"]:checked');
let cianosis = document.querySelector('input[name="cianosis"]:checked');
let edema = document.querySelector('input[name="edema"]:checked');
const obscardio = document.getElementById("obscardio");
let dolorabdominal = document.querySelector(
  'input[name="dolorabdominal"]:checked',
);
let transintestinal = document.querySelector(
  'input[name="transintestinal"]:checked',
);
const excretaxdia = document.getElementById("excretaxdia");
let orofaringeo = document.querySelector('input[name="orofaringeo"]:checked');
let abdomen = document.querySelector('input[name="abdomen"]:checked');
let hernia = document.querySelector('input[name="hernia"]:checked');
const obsdigestivo = document.getElementById("obsdigestivo");
const Observaciongeneral = document.getElementById("Observaciongeneral");
const peso = document.getElementById("peso");
const talla = document.getElementById("talla");
const imc = document.getElementById("imc");
const fc = document.getElementById("fc");
const fr = document.getElementById("fr");
const ta = document.getElementById("ta");
const ClasificacionIMC = document.getElementById("imcClasificacion");
const ojoder = document.getElementById("ojoder");
const ojoizq = document.getElementById("ojoizq");
const bilateral = document.getElementById("bilateral");
const pupilas = document.getElementById("pupilas");
const conciencia = document.getElementById("conciencia");
const sensible = document.getElementById("sensible");
const sueno = document.getElementById("sueno");
const reflejo = document.getElementById("reflejo");
const observacionnervios = document.getElementById("observacionnervios");
const fecharevision = document.getElementById("fecharevision");

let audicion = document.querySelector('input[name="audicion"]:checked');
let UsoLentes = document.querySelector('input[name="usoLentes"]:checked');
const observacionAgudezaVisual = document.getElementById(
  "observacionAgudezaVisual",
);
let agilidadvisual = document.querySelector(
  'input[name="agilidadvisual"]:checked',
);
let reflejosnervios = document.querySelector(
  'input[name="reflejosnervios"]:checked',
);

let campimetria = document.querySelector('input[name="campimetria"]:checked');
let olfato = document.querySelector('input[name="olfato"]:checked');
let tactonerv = document.querySelector('input[name="tactonerv"]:checked');
const cardiopulmonar = document.getElementById("cardiopulmonar");
const tecnicarte = document.getElementById("tecnicarte");
const octocerosis = document.getElementById("octocerosis");
const timpano = document.getElementById("timpano");
const cardiopulmonar2 = document.getElementById("cardiopulmonar2");
const tecnicarte2 = document.getElementById("tecnicarte2");
const freccardiaca = document.getElementById("freccardiaca");
const viasrespi = document.getElementById("viasrespi");
const camppulmonar = document.getElementById("camppulmonar");
const obsgencardio = document.getElementById("obsgencardio");
const digestivo = document.getElementById("digestivo");
const peristalsis = document.getElementById("peristalsis");
const dolor = document.getElementById("dolor");
const organomegalias = document.getElementById("organomegalias");
const herniaumbilical = document.getElementById("herniaumbilical");
let cuello = document.querySelector('input[name="cuello"]:checked');
let columnavertebral = document.querySelector(
  'input[name="columnavertebral"]:checked',
);
let movilidad = document.querySelector('input[name="movilidad"]:checked');
let marcha = document.querySelector('input[name="marcha"]:checked');
let rots = document.querySelector('input[name="rots"]:checked');
let puntorlumbar = document.querySelector('input[name="puntorlumbar"]:checked');
const lasage = document.getElementById("lasage");
const bragard = document.getElementById("bragard");
const tinel = document.getElementById("tinel");
const phanel = document.getElementById("phanel");
const trendelemburg = document.getElementById("trendelemburg");
const obsmusculo = document.getElementById("obsmusculo");
const espnormal = document.getElementById("espnormal");
const espobstructivo = document.getElementById("espobstructivo");
const esprestrictivo = document.getElementById("esprestrictivo");
const espmixto = document.getElementById("espmixto");
const d1 = document.getElementById("d1");
const d2 = document.getElementById("d2");
const d3 = document.getElementById("d3");
const d4 = document.getElementById("d4");
const d5 = document.getElementById("d5");
const d6 = document.getElementById("d6");
const d7 = document.getElementById("d7");
const i1 = document.getElementById("i1");
const i2 = document.getElementById("i2");
const i3 = document.getElementById("i3");
const i4 = document.getElementById("i4");
const i5 = document.getElementById("i5");
const i6 = document.getElementById("i6");
const i7 = document.getElementById("i7");
const DiagnosAudio = document.getElementById("audioClasificacion");
const diagnostivosano = document.getElementById("diagnostivosano");
const sensorial = document.getElementById("sensorial");
const mixma = document.getElementById("mixma");
const unilateral = document.getElementById("unilateral");
const bilateralstp = document.getElementById("bilateralstp");
const superficial = document.getElementById("superficial");
const moderada = document.getElementById("moderada");
const profunda = document.getElementById("profunda");
const traumadegenerativo = document.getElementById("traumadegenerativo");
const traumamixto = document.getElementById("traumamixto");
const traumaotros = document.getElementById("traumaotros");
const otocerosis = document.getElementById("otocerosis");
const infeccionfaringea = document.getElementById("infeccionfaringea");
const perforanciatimpanica = document.getElementById("perforanciatimpanica");
const btnSaveExamen = document.getElementById("saveExamen");
const archivo = document.getElementById("archivo");

const canvas = document.getElementById("canvas");
canvas.width = 1200;
canvas.height = 600;
const ctx1 = canvas.getContext("2d", { alpha: true });

//Nuevos campos de ingreso
const puestoAnterior = document.getElementById("puestoAnterior");
const horarioAnterior = document.getElementById("horariolaboral");
const tiempoTrabajoAnterior = document.getElementById("tiempotrabajado");
const seguridadindusrial = document.getElementById("seguridadIndustrial");
const equipoPP = document.getElementById("equipoproteccion");
const expoRuidos = document.getElementById("expoRuidos");
const expoQuimicos = document.getElementById("expoQuimicos");
const examenTipo = document.getElementById("examenTipo");

const filtroNoemp = document.getElementById("filtroNoemp");
const filtroDepartamento = document.getElementById("filtroDepartamento");
const filtroFechaI = document.getElementById("filtroFechaI");
const filtroFechaF = document.getElementById("filtroFechaF");

Tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "filtroDepartamento", 0);

document.getElementById("btnFiltrarExamen").addEventListener("click", (e) => {
  e.preventDefault();
  ExamenMObj.filtrarExamenM(
    filtroNoemp.value.trim(),
    filtroDepartamento.value,
    filtroFechaI.value,
    filtroFechaF.value,
  ).then((data) => ExamenMObj.pintarTablaExamenM("tblExamenMedico", data, 1));
});

document
  .getElementById("btnLimpiarFiltroExamen")
  .addEventListener("click", (e) => {
    e.preventDefault();
    filtroNoemp.value = "";
    filtroDepartamento.value = "";
    filtroFechaI.value = "";
    filtroFechaF.value = "";
    ExamenMObj.tblExamenMSession("tblExamenMedico", 1);
  });

const idsCamposObligatorios = ["noemp"];

Tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "departamento", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcPuestos", "puesto", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcMaquinas", "maquina", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcNvlEstudios", "escolaridad", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcTipoSangre", "tiposangre", 0);
// Tools.llnarslc("CatalogoPersonal", "GetSlcReligion", "religion", 0);
ExamenMObj.llenarSlcIMC("imcClasificacion", 0);
Tools.llnarslc(
  "CatalogoEnfermeria",
  "GetEnfermeriaAudiometria",
  "audioClasificacion",
  0,
);
ExamenMObj.tblExamenMSession("tblExamenMedico", 1);

document.getElementById("noemp").addEventListener("keyup", (e) => {
  e.preventDefault();
  e.target.value != "" &&
    ConsultasObj.datauserEnfermeria(e.target.value).then((element) => {
      if (element.length > 0) {
        document.getElementById("nombre").value = element[0].nombre;
        document.getElementById("departamento").value = element[0].departamento;
        document.getElementById("puesto").value = element[0].puesto;
        departamento.disabled = true;
        puesto.disabled = true;
      }
    });
});

// function mostrarPaso(numero) {
//   document.querySelectorAll(".paso").forEach((p) => (p.style.display = "none"));
//   document.getElementById("paso" + numero).style.display = "block";
// }
// window.mostrarPaso = (numero) => mostrarPaso(numero);

function mostrarPaso(numero) {
  const triggerEl = document.querySelector(
    '#tabExamen button[data-bs-target="#paso' + numero + '"]',
  );
  if (triggerEl) bootstrap.Tab.getOrCreateInstance(triggerEl).show();
}
window.mostrarPaso = (numero) => mostrarPaso(numero);

function limpiarTabsError() {
  document
    .querySelectorAll("#tabExamen .nav-link")
    .forEach((b) => b.classList.remove("tab-error"));
}

function marcarTabDeCampo(idCampo) {
  const pane = document.getElementById(idCampo)?.closest(".tab-pane");
  if (!pane) return;
  const btn = document.querySelector(
    '#tabExamen button[data-bs-target="#' + pane.id + '"]',
  );
  if (btn) btn.classList.add("tab-error");
}

// ---------- Audiograma en tiempo real ----------
const AUDIO_FREQS = [
  { d: "d1", i: "i1", hz: 500 },
  { d: "d2", i: "i2", hz: 1000 },
  { d: "d3", i: "i3", hz: 2000 },
  { d: "d4", i: "i4", hz: 3000 },
  { d: "d5", i: "i5", hz: 4000 },
  { d: "d6", i: "i6", hz: 6000 },
  { d: "d7", i: "i7", hz: 8000 },
  // Para 125/250: agrega celdas nuevas y aquí { d:"dX", i:"iX", hz:125 }, etc.
];

function dibujarAudiograma() {
  const canvas = document.getElementById("audiograma");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");
  const W = canvas.width,
    H = canvas.height;
  ctx.clearRect(0, 0, W, H);

  const mL = 45,
    mR = 90,
    mT = 20,
    mB = 30;
  const plotW = W - mL - mR,
    plotH = H - mT - mB;
  const dbMin = -10,
    dbMax = 120;
  const yFor = (db) => mT + ((db - dbMin) / (dbMax - dbMin)) * plotH; // 0 arriba
  const xFor = (idx) =>
    mL +
    (AUDIO_FREQS.length === 1
      ? plotW / 2
      : (idx / (AUDIO_FREQS.length - 1)) * plotW);

  ctx.font = "10px sans-serif";
  ctx.textAlign = "right";
  ctx.textBaseline = "middle";
  for (let db = dbMin; db <= dbMax; db += 10) {
    const y = yFor(db);
    ctx.strokeStyle = "#e9ecef";
    ctx.beginPath();
    ctx.moveTo(mL, y);
    ctx.lineTo(W - mR, y);
    ctx.stroke();
    ctx.fillStyle = "#6c757d";
    ctx.fillText(db, mL - 5, y);
  }
  ctx.textAlign = "center";
  ctx.textBaseline = "top";
  AUDIO_FREQS.forEach((f, idx) => {
    const x = xFor(idx);
    ctx.strokeStyle = "#e9ecef";
    ctx.beginPath();
    ctx.moveTo(x, mT);
    ctx.lineTo(x, H - mB);
    ctx.stroke();
    ctx.fillStyle = "#6c757d";
    ctx.fillText(f.hz, x, H - mB + 4);
  });

  const serie = (idKey, color, tipo) => {
    ctx.strokeStyle = color;
    ctx.fillStyle = color;
    ctx.lineWidth = 2;
    let prev = null;
    AUDIO_FREQS.forEach((f, idx) => {
      const cell = document.getElementById(f[idKey]);
      const val = cell ? parseFloat((cell.innerText || "").trim()) : NaN;
      if (isNaN(val)) {
        prev = null;
        return;
      }
      const x = xFor(idx),
        y = yFor(val);
      if (prev) {
        ctx.beginPath();
        ctx.moveTo(prev.x, prev.y);
        ctx.lineTo(x, y);
        ctx.stroke();
      }
      ctx.beginPath();
      if (tipo === "O") {
        ctx.arc(x, y, 5, 0, Math.PI * 2);
        ctx.stroke();
      } else {
        ctx.moveTo(x - 5, y - 5);
        ctx.lineTo(x + 5, y + 5);
        ctx.moveTo(x + 5, y - 5);
        ctx.lineTo(x - 5, y + 5);
        ctx.stroke();
      }
      prev = { x, y };
    });
  };
  serie("d", "#dc3545", "O"); // Derecho: rojo, O
  serie("i", "#0d6efd", "X"); // Izquierdo: azul, X

  ctx.textAlign = "left";
  ctx.textBaseline = "middle";
  ctx.lineWidth = 1;
  ctx.fillStyle = "#dc3545";
  ctx.fillText("O  Derecho", W - mR + 5, mT + 6);
  ctx.fillStyle = "#0d6efd";
  ctx.fillText("X  Izquierdo", W - mR + 5, mT + 22);
}

// Redibuja al escribir en las celdas de audiometría
AUDIO_FREQS.forEach((f) => {
  ["d", "i"].forEach((k) => {
    const cell = document.getElementById(f[k]);
    if (cell) cell.addEventListener("input", dibujarAudiograma);
  });
});
dibujarAudiograma(); // dibujo inicial

// Quita el rojo del tab en cuanto el usuario escribe en él
document.getElementById("formExamenmedico").addEventListener("input", (e) => {
  const pane = e.target.closest(".tab-pane");
  if (!pane) return;
  const btn = document.querySelector(
    '#tabExamen button[data-bs-target="#' + pane.id + '"]',
  );
  if (btn) btn.classList.remove("tab-error");
});

let timerHandle = null;

document.getElementById("btnSign").addEventListener("click", () => {
  ctx1.clearRect(0, 0, canvas.width, canvas.height);
  try {
    SetDisplayXSize(canvas.width);
    SetDisplayYSize(canvas.height);

    SetTabletState(0, timerHandle);
    ClearTablet();

    timerHandle = SetTabletState(1, ctx1, 50);
  } catch (e) {
    alert(
      "No se pudo inicializar SigWeb. ¿Está instalado y permitido el acceso de red local en el navegador?",
    );
    console.error(e);
  }
});

document
  .getElementById("formExamenmedico")
  .addEventListener("submit", function (e) {
    e.preventDefault();
  });

// document.getElementById("examenTipo").addEventListener("change", function (e) {
//   e.preventDefault();
//   const tipo = e.target.value;
//   console.log(tipo);

//   if (tipo == "1") {
//     document.getElementById("datosIngreso").hidden = false;
//   } else {
//     document.getElementById("datosIngreso").hidden = true;
//   }
// });

document.getElementById("examenTipo").addEventListener("change", function (e) {
  e.preventDefault();
  const esIngreso = e.target.value === "1";
  document.getElementById("datosIngreso").hidden = !esIngreso;

  if (esIngreso) {
    noemp.value = "";
    noemp.disabled = true; // aún no tiene NoEmp
    nombre.disabled = false; // se captura a mano
  } else {
    noemp.disabled = false;
    nombre.value = "";
    nombre.disabled = true;
  }
  departamento.disabled = true;
  puesto.disabled = true;
});

btnSaveExamen.addEventListener("click", (e) => {
  e.preventDefault();

  // const esIngreso = examenTipo.value === "1";

  // if (examenTipo.value === "") {
  //   return swal.fire(
  //     "Falta información",
  //     "Selecciona el tipo de examen.",
  //     "warning",
  //   );
  // }
  // if (esIngreso && nombre.value.trim() === "") {
  //   return swal.fire(
  //     "Falta información",
  //     "Captura el nombre del empleado de nuevo ingreso.",
  //     "warning",
  //   );
  // }
  // if (!esIngreso && noemp.value.trim() === "") {
  //   return swal.fire(
  //     "Falta información",
  //     "Captura un número de empleado.",
  //     "warning",
  //   );
  // }
  // if (!esIngreso && nombre.value.trim() === "") {
  //   return swal.fire(
  //     "Número no válido",
  //     "No se encontró un empleado con ese NoEmp.",
  //     "warning",
  //   );
  // }
  limpiarTabsError();
  const esIngreso = examenTipo.value === "1";

  const faltantes = [];
  if (examenTipo.value === "") {
    faltantes.push({ id: "examenTipo", msg: "Selecciona el tipo de examen." });
  } else if (esIngreso) {
    if (nombre.value.trim() === "")
      faltantes.push({
        id: "nombre",
        msg: "Captura el nombre del empleado de nuevo ingreso.",
      });
  } else {
    if (noemp.value.trim() === "")
      faltantes.push({ id: "noemp", msg: "Captura un número de empleado." });
    else if (nombre.value.trim() === "")
      faltantes.push({
        id: "noemp",
        msg: "No se encontró un empleado con ese NoEmp.",
      });
  }

  if (faltantes.length > 0) {
    faltantes.forEach((f) => marcarTabDeCampo(f.id));
    const pane = document.getElementById(faltantes[0].id)?.closest(".tab-pane");
    if (pane) mostrarPaso(pane.id.replace("paso", ""));
    swal.fire("Faltan datos", faltantes[0].msg, "warning");
    return;
  }

  tos = document.querySelector('input[name="Tos"]:checked');
  expectoracion = document.querySelector('input[name="expectoracion"]:checked');
  dolortoracico = document.querySelector('input[name="dolortoracico"]:checked');
  taquicardia = document.querySelector('input[name="taquicardia"]:checked');
  disnea = document.querySelector('input[name="disnea"]:checked');
  cianosis = document.querySelector('input[name="cianosis"]:checked');
  edema = document.querySelector('input[name="edema"]:checked');
  dolorabdominal = document.querySelector(
    'input[name="dolorabdominal"]:checked',
  );
  transintestinal = document.querySelector(
    'input[name="transintestinal"]:checked',
  );
  orofaringeo = document.querySelector('input[name="orofaringeo"]:checked');
  abdomen = document.querySelector('input[name="abdomen"]:checked');
  hernia = document.querySelector('input[name="hernia"]:checked');

  UsoLentes = document.querySelector('input[name="usoLentes"]:checked');

  audicion = document.querySelector('input[name="audicion"]:checked');
  agilidadvisual = document.querySelector(
    'input[name="agilidadvisual"]:checked',
  );
  reflejosnervios = document.querySelector(
    'input[name="reflejosnervios"]:checked',
  );
  campimetria = document.querySelector('input[name="campimetria"]:checked');
  olfato = document.querySelector('input[name="olfato"]:checked');
  tactonerv = document.querySelector('input[name="tactonerv"]:checked');
  cuello = document.querySelector('input[name="cuello"]:checked');
  columnavertebral = document.querySelector(
    'input[name="columnavertebral"]:checked',
  );
  movilidad = document.querySelector('input[name="movilidad"]:checked');
  marcha = document.querySelector('input[name="marcha"]:checked');
  rots = document.querySelector('input[name="rots"]:checked');
  puntorlumbar = document.querySelector('input[name="puntorlumbar"]:checked');
  const canvas = document.getElementById("canvas");

  const fileInput = document.getElementById("archivo");
  const file =
    fileInput.files[0] !== undefined ? fileInput.files[0] : "Sin archivo";
  const examenData = {
    noemp: noemp.value,
    nombre: nombre.value,
    departamento: departamento.value,
    puesto: puesto.value,
    maquina: maquina.value,
    fechanaimiento: fechanaimiento.value,
    edad: edad.value,
    lugarnac: lugarnac.value,
    domicilio: domicilio.value,
    escolaridad: escolaridad.value,
    // religion: religion.value,
    tiposangre: tiposangre.value,
    fechaingreso: fechaingreso.value,
    fecharevision: fecharevision.value,
    problemasdesalud: problemasdesalud.value,
    tomamedicamento: tomamedicamento.value,
    tratamientomedico: tratamientomedico.value,
    enfermedadcronica: enfermedadcronica.value,
    tabaquismo: tabaquismo.checked ? 1 : 0,
    alcoholismo: alcoholismo.checked ? 1 : 0,
    altfisica: altfisica.value,
    quirurgicos: quirurgicos.value,
    traumaticos: traumaticos.value,
    transfuciones: transfuciones.value,
    antivioticos: antivioticos.value,
    analgesitos: analgesitos.value,
    antiinflamatorios: antiinflamatorios.value,
    otrosalergias: otrosalergias.value,
    alimentacion: alimentacion.value,
    aseogeneral: aseogeneral.value,
    hobbies: hobbies.value,
    otrasactlaborales: otrasactlaborales.value,
    incapacidades: incapacidades.value,
    diagnostico: diagnostico.value,
    diasIncapacidad: diasIncapacidad.value,
    secuela: secuela.value,
    rehabilitacion: rehabilitacion.value,
    trayecto: trayecto.checked ? 1 : 0,
    enfgeneral: enfgeneral.checked ? 1 : 0,
    accidentetrabajo: accidentetrabajo.checked ? 1 : 0,
    enfermedadtrabajo: enfermedadtrabajo.checked ? 1 : 0,
    Tos: tos.value,
    expectoracion: expectoracion.value,
    dolortoracico: dolortoracico.value,
    taquicardia: taquicardia.value,
    disnea: disnea.value,
    cianosis: cianosis.value,
    edema: edema.value,
    obscardio: obscardio.value,
    dolorabdominal: dolorabdominal.value,
    transintestinal: transintestinal.value,
    excretaxdia: excretaxdia.value,
    orofaringeo: orofaringeo.value,
    abdomen: abdomen.value,
    hernia: hernia.value,
    obsdigestivo: obsdigestivo.value,
    Observaciongeneral: Observaciongeneral.value,
    peso: peso.value,
    talla: talla.value,
    imc: imc.value,
    fc: fc.value,
    fr: fr.value,
    ta: ta.value,
    ojoder: ojoder.value,
    ojoizq: ojoizq.value,
    bilateral: bilateral.value,
    pupilas: pupilas.value,
    conciencia: conciencia.value,
    sensible: sensible.value,
    sueno: sueno.value,
    reflejo: reflejo.value,
    observacionnervios: observacionnervios.value,
    audicion: audicion.value,
    agilidadvisual: agilidadvisual.value,
    reflejos: reflejosnervios.value,
    campimetria: campimetria.value,
    olfato: olfato.value,
    tacto: tactonerv.value,
    cardiopulmonar: cardiopulmonar.value,
    tecnicarte: tecnicarte.value,
    octocerosis: octocerosis.value,
    timpano: timpano.value,
    cardiopulmonar2: cardiopulmonar2.value,
    tecnicarte2: tecnicarte2.value,
    freccardiaca: freccardiaca.value,
    viasrespi: viasrespi.value,
    camppulmonar: camppulmonar.value,
    obsgencardio: obsgencardio.value,
    digestivo: digestivo.value,
    peristalsis: peristalsis.value,
    dolor: dolor.value,
    organomegalias: organomegalias.value,
    herniaumbilical: herniaumbilical.value,
    cuello: cuello.value,
    columnavertebral: columnavertebral.value,
    movilidad: movilidad.value,
    marcha: marcha.value,
    rots: rots.value,
    puntorlumbar: puntorlumbar.value,
    lasage: lasage.value,
    bragard: bragard.value,
    tinel: tinel.value,
    phanel: phanel.value,
    trendelemburg: trendelemburg.value,
    obsmusculo: obsmusculo.value,
    espnormal: espnormal.value,
    espobstructivo: espobstructivo.value,
    esprestrictivo: esprestrictivo.value,
    espmixto: espmixto.value,
    d1: d1.innerText,
    d2: d2.innerText,
    d3: d3.innerText,
    d4: d4.innerText,
    d5: d5.innerText,
    d6: d6.innerText,
    d7: d7.innerText,
    i1: i1.innerText,
    i2: i2.innerText,
    i3: i3.innerText,
    i4: i4.innerText,
    i5: i5.innerText,
    i6: i6.innerText,
    i7: i7.innerText,
    diagnostivosano: diagnostivosano.value,
    conductiva: conductiva.value,
    sensorial: sensorial.value,
    mixma: mixma.value,
    unilateral: unilateral.value,
    bilateralstp: bilateralstp.value,
    superficial: superficial.value,
    moderada: moderada.value,
    profunda: profunda.value,
    traumadegenerativo: traumadegenerativo.value,
    traumamixto: traumamixto.value,
    traumaotros: traumaotros.value,
    otocerosis: otocerosis.value,
    infeccionfaringea: infeccionfaringea.value,
    perforanciatimpanica: perforanciatimpanica.value,
    canvas,
    folio: folio.value,
    ClasificacionIMC: ClasificacionIMC.value,
    UsoLentes: UsoLentes.value,
    DiagnosAudio: DiagnosAudio.value,
    obsAguVisual: observacionAgudezaVisual.value,
    ruta: file,
    puestoAnterior: puestoAnterior.value,
    horarioAnterior: horarioAnterior.value,
    tiempoTrabajoAnterior: tiempoTrabajoAnterior.value,
    seguridadIndustrial: seguridadindusrial.value,
    expoRuidos: expoRuidos.value,
    expoQuimicos: expoQuimicos.value,
    equipoproteccion: equipoPP.value,
    tipoExamen: examenTipo.value,
  };

  const obligatorios = esIngreso ? ["nombre"] : ["noemp"];

  Tools.validarCamposPorID(obligatorios) !== false &&
    ExamenMObj.saveExamen(examenData, folio.value).then(() => {
      // Tools.validarCamposPorID(idsCamposObligatorios) !== false &&
      //   ExamenMObj.saveExamen(examenData, folio.value).then(() => {
      ExamenMObj.tblExamenMSession("tblExamenMedico", 1);
      btnSaveExamen.classList.remove("btn-warning");
      btnSaveExamen.classList.add("bg-target");
      btnSaveExamen.innerHTML = '<i class="fas fa-save"></i> Guardar';
      folio.value = "";
      const presionArterialEl = document.getElementById("presionArterial");
      if (presionArterialEl)
        presionArterialEl.className = "form-control form-control-sm";
      // document.getElementById("formExamenmedico").reset();
      // mostrarPaso(1);

      document.getElementById("formExamenmedico").reset();
      AUDIO_FREQS.forEach((f) => {
        const cd = document.getElementById(f.d),
          ci = document.getElementById(f.i);
        if (cd) cd.innerText = "";
        if (ci) ci.innerText = "";
      });
      dibujarAudiograma();
      const linkPdf = document.getElementById("archivopdf");
      if (linkPdf) {
        linkPdf.style.display = "none";
        linkPdf.innerHTML = "";
      }
      mostrarPaso(1);
    });
});

// FirmaObj.agregarEventos();

document.getElementById("limpiarCanvas").addEventListener("click", () => {
  try {
    ClearTablet();
  } catch (e) {
    console.error(e);
  }
});

document.getElementById("limpiadoConsulta").addEventListener("click", () => {
  btnSaveExamen.classList.remove("btn-warning");
  btnSaveExamen.classList.add("bg-target");
  btnSaveExamen.innerHTML = '<i class="fas fa-save"></i> Guardar';
});

window.editExamenM = (id) => {
  ExamenMObj.editExamenM(id).then((element) => {
    console.log(element);
    folio.value = element[0].id;
    examenTipo.value = element[0].tipoExamen;
    if (element[0].tipoExamen == 1) {
      document.getElementById("datosIngreso").removeAttribute("hidden");
    } else {
      document.getElementById("datosIngreso").setAttribute("hidden", "");
    }
    noemp.value = element[0].noemp;
    nombre.value = element[0].nombre;
    departamento.value = element[0].departamento;
    puesto.value = element[0].puesto;
    maquina.value = element[0].maquina;
    fechanaimiento.value = element[0].fechanac;
    lugarnac.value = element[0].lugarnac;
    domicilio.value = element[0].domicilio;
    escolaridad.value = element[0].escolaridad;
    // religion.value = element[0].religion;
    tiposangre.value = element[0].sangre;
    fechaingreso.value = element[0].fechaing;
    edad.value = element[0].edad;
    fecharevision.value = element[0].fecharevision;
    puestoAnterior.value = element[0].puestoAnterior;
    horarioAnterior.value = element[0].horarioAnterior;
    tiempoTrabajoAnterior.value = element[0].tiempoTrabajoAnterior;
    seguridadIndustrial.value = element[0].seguridadIndustrial;
    expoRuidos.value = element[0].expoRuidos;
    expoQuimicos.value = element[0].expoQuimicos;
    equipoproteccion.value = element[0].equipoproteccion;
    problemasdesalud.value = element[0].problemassalud;
    tratamientomedico.value = element[0].tratamientoMed;
    tomamedicamento.value = element[0].tomaMedic;
    enfermedadcronica.value = element[0].enfermCron;
    tabaquismo.checked = element[0].tabaquismo;
    alcoholismo.checked = element[0].alcoholismo;
    altfisica.value = element[0].altfisica;
    quirurgicos.value = element[0].quirurgicos;
    traumaticos.value = element[0].traumaticos;
    transfuciones.value = element[0].transfuciones;
    antivioticos.value = element[0].antivioticos;
    analgesitos.value = element[0].analgesitos;
    antiinflamatorios.value = element[0].antiInfla;
    otrosalergias.value = element[0].otrosalergias;
    alimentacion.value = element[0].alimentacion;
    aseogeneral.value = element[0].aseogeneral;
    hobbies.value = element[0].hobbies;
    otrasactlaborales.value = element[0].otrasAct;
    incapacidades.value = element[0].inca;
    diagnostico.value = element[0].diagnostico;
    diasIncapacidad.value = element[0].diasInca;
    secuela.value = element[0].secuela;
    rehabilitacion.value = element[0].rehab;
    trayecto.checked = element[0].trayecto;
    enfgeneral.checked = element[0].enfgeneral;
    accidentetrabajo.checked = element[0].accTrab;
    enfermedadtrabajo.checked = element[0].enfTrab;
    const base64Image = element[0].firma;
    const img = new Image();
    img.src = base64Image;
    img.onload = function () {
      const canvas = document.getElementById("canvas");
      const ctx = canvas.getContext("2d");

      // Dibujar la imagen en el canvas
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
    };

    // Validacion para los radio buttons de aparatos y sistemas
    let valTos = parseInt(element[0].tos);
    let valExpectoracion = parseInt(element[0].expectoracion);
    let valDolorToracico = parseInt(element[0].dolorTora);
    let valTaquicardia = parseInt(element[0].taquicardia);
    let valDisnea = parseInt(element[0].disnea);
    let valCianosis = parseInt(element[0].cianosis);
    let valEdema = parseInt(element[0].edema);
    let valDolorAbdo = parseInt(element[0].dolorAbd);
    let valTransIntes = parseInt(element[0].transInst);
    let valOroFaringe = parseInt(element[0].orofaringeo);
    let valAbdomen = parseFloat(element[0].abdomen);
    let valHernia = parseInt(element[0].hernia);

    valTos === 1
      ? (document.getElementById("Tos1").checked = true)
      : (document.getElementById("Tos0").checked = true);

    valExpectoracion === 1
      ? (document.getElementById("expectoracion1").checked = true)
      : (document.getElementById("expectoracion0").checked = true);

    valDolorToracico === 1
      ? (document.getElementById("dolortoracico1").checked = true)
      : (document.getElementById("dolortoracico0").checked = true);

    valTaquicardia === 1
      ? (document.getElementById("taquicardia1").checked = true)
      : (document.getElementById("taquicardia0").checked = true);

    valDisnea === 1
      ? (document.getElementById("disnea1").checked = true)
      : (document.getElementById("disnea0").checked = true);

    valCianosis === 1
      ? (document.getElementById("cianosis1").checked = true)
      : (document.getElementById("cianosis0").checked = true);

    valEdema === 1
      ? (document.getElementById("edema1").checked = true)
      : (document.getElementById("edema0").checked = true);

    valDolorAbdo === 1
      ? (document.getElementById("dolorabdominal1").checked = true)
      : (document.getElementById("dolorabdominal0").checked = true);

    valTransIntes === 1
      ? (document.getElementById("transintestinal1").checked = true)
      : (document.getElementById("transintestinal0").checked = true);

    valOroFaringe === 1
      ? (document.getElementById("orofaringeo1").checked = true)
      : (document.getElementById("orofaringeo0").checked = true);

    valAbdomen === 1
      ? (document.getElementById("abdomen1").checked = true)
      : (document.getElementById("abdomen0").checked = true);

    valHernia === 1
      ? (document.getElementById("hernia1").checked = true)
      : (document.getElementById("hernia0").checked = true);

    obscardio.value = element[0].obscardio;
    excretaxdia.value = element[0].excretaxdia;
    obsdigestivo.value = element[0].obsdigestio;

    Observaciongeneral.value = element[0].obsGeneral;
    peso.value = element[0].Peso;
    talla.value = element[0].talla;
    imc.value = element[0].imc;
    ClasificacionIMC.value = element[0].ClassIMC;
    fc.value = element[0].fc;
    fr.value = element[0].fr;
    ta.value = element[0].ta;
    ojoder.value = element[0].ojoder;
    ojoizq.value = element[0].ojoizq;
    bilateral.value = element[0].bilateral;
    pupilas.value = element[0].pupilas;

    let valUsoLentes = parseInt(element[0].UsoLentes);
    valUsoLentes === 1
      ? (document.getElementById("lentes1").checked = true)
      : (document.getElementById("lentes0").checked = true);

    observacionAgudezaVisual.value = element[0].obsAguVisual;

    conciencia.value = element[0].conciencia;
    sensible.value = element[0].sensible;
    sueno.value = element[0].sueno;
    reflejo.value = element[0].reflejo;
    observacionnervios.value = element[0].obsNervios;

    // Validación para los radio buttons organos de los sentidos
    let valAudicion = parseInt(element[0].audicion);
    let valAgiVisual = parseInt(element[0].agiVis);
    let valReflejos = parseInt(element[0].reflejos);
    let valCampi = parseInt(element[0].campimetria);
    let valOlfato = parseInt(element[0].olfato);
    let valTacto = parseInt(element[0].tacto);

    valAudicion === 1
      ? (document.getElementById("audicion1").checked = true)
      : (document.getElementById("audicion0").checked = true);

    valAgiVisual === 1
      ? (document.getElementById("agilidadvisual1").checked = true)
      : (document.getElementById("agilidadvisual0").checked = true);

    valReflejos === 1
      ? (document.getElementById("reflejos1").checked = true)
      : (document.getElementById("reflejos0").checked = true);

    valCampi === 1
      ? (document.getElementById("campimetria1").checked = true)
      : (document.getElementById("campimetria0").checked = true);

    valOlfato === 1
      ? (document.getElementById("olfato1").checked = true)
      : (document.getElementById("olfato0").checked = true);

    valTacto === 1
      ? (document.getElementById("tactonerv1").checked = true)
      : (document.getElementById("tactonerv0").checked = true);

    // -- Fin validación radio buttons

    cardiopulmonar.value = element[0].cardPulm;
    tecnicarte.value = element[0].tecnicarte;
    octocerosis.value = element[0].octocerosis;
    timpano.value = element[0].timpano;
    cardiopulmonar2.value = element[0].cardPulm2;
    tecnicarte2.value = element[0].tecnicarte2;
    freccardiaca.value = element[0].freccCard;
    viasrespi.value = element[0].viasrespi;
    camppulmonar.value = element[0].campPulm;
    obsgencardio.value = element[0].obsGenCard;
    peristalsis.value = element[0].peristalsis;
    dolor.value = element[0].dolor;
    organomegalias.value = element[0].organomegalias;
    herniaumbilical.value = element[0].herniaumbilical;

    // Validación para los radio buttons del músculo esquelético
    let valCuello = parseInt(element[0].cuello);
    let valColVert = parseInt(element[0].columVert);
    let valMovilidad = parseInt(element[0].movilidad);
    let valMarcha = parseInt(element[0].marcha);
    let valRots = parseInt(element[0].rots);
    let valPuntorLumb = parseInt(element[0].puntorlumbar);

    valCuello === 1
      ? (document.getElementById("cuello1").checked = true)
      : (document.getElementById("cuello0").checked = true);

    valColVert === 1
      ? (document.getElementById("columnavertebral1").checked = true)
      : (document.getElementById("columnavertebral0").checked = true);

    valMovilidad === 1
      ? (document.getElementById("movilidad1").checked = true)
      : (document.getElementById("movilidad0").checked = true);

    valMarcha === 1
      ? (document.getElementById("marcha1").checked = true)
      : (document.getElementById("marcha0").checked = true);

    valRots === 1
      ? (document.getElementById("rots1").checked = true)
      : (document.getElementById("rots0").checked = true);

    valPuntorLumb === 1
      ? (document.getElementById("puntorlumbar1").checked = true)
      : (document.getElementById("puntorlumbar0").checked = true);

    // -- Fin validación radio buttons

    lasage.value = element[0].lasage;
    bragard.value = element[0].bragard;
    tinel.value = element[0].tinel;
    phanel.value = element[0].phanel;
    trendelemburg.value = element[0].trendelemburg;
    obsmusculo.value = element[0].obsmusculo;
    espnormal.value = element[0].espnormal;
    espobstructivo.value = element[0].espobstructivo;
    esprestrictivo.value = element[0].esprestrictivo;
    espmixto.value = element[0].espmixto;

    // Valores de la tabla derecho
    d1.innerText = element[0].d1;
    d2.innerText = element[0].d2;
    d3.innerText = element[0].d3;
    d4.innerText = element[0].d4;
    d5.innerText = element[0].d5;
    d6.innerText = element[0].d6;
    d7.innerText = element[0].d7;

    // Valores de la tabla izquierdo
    i1.innerText = element[0].i1;
    i2.innerText = element[0].i2;
    i3.innerText = element[0].i3;
    i4.innerText = element[0].i4;
    i5.innerText = element[0].i5;
    i6.innerText = element[0].i6;
    i7.innerText = element[0].i7;

    dibujarAudiograma();

    DiagnosAudio.value = element[0].DiagnosAudio;
    diagnostivosano.value = element[0].diagSano;
    // conductiva.value = element[0].conductiva;
    // sensorial.value = element[0].sensorial;
    // mixma.value = element[0].mixma;
    unilateral.value = element[0].unilateral;
    bilateralstp.value = element[0].bilateralstp;
    // superficial.value = element[0].superficial;
    // moderada.value = element[0].moderada;
    // profunda.value = element[0].profunda;
    traumadegenerativo.value = element[0].traumDege;
    traumamixto.value = element[0].traumamixto;
    traumaotros.value = element[0].traumaotros;
    otocerosis.value = element[0].otocerosis;
    infeccionfaringea.value = element[0].indeccFaring;
    perforanciatimpanica.value = element[0].perfAtimp;
    // const link = document.getElementById("archivopdf");
    // console.log("Elemento link", link);
    // if (element[0].ruta) {
    //   const nombreArchivo = element[0].ruta.split("/").pop();
    //   link.href = "Files/" + nombreArchivo;
    //   link.style.display = "block";
    // }

    const link = document.getElementById("archivopdf");
    if (element[0].ruta && element[0].ruta !== "Sin archivo") {
      const nombreArchivo = element[0].ruta.split("/").pop();
      link.href = "Files/" + nombreArchivo;
      link.innerHTML = '<i class="fa-solid fa-paperclip"></i> ' + nombreArchivo;
      link.style.display = "inline-block";
    } else {
      link.style.display = "none";
    }

    mostrarPaso(1);
    btnSaveExamen.classList.remove("bg-target");
    btnSaveExamen.classList.add("btn-warning");
    btnSaveExamen.innerHTML =
      '<i class="fa-solid fa-pen-to-square"></i> Actualizar';
  });
};

window.eliminarExamenM = (id) => {
  Swal.fire({
    title: "¿Eliminar registro?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#dc3545",
    customClass: {
      actions: "d-flex gap-2 justify-content-center",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      ExamenMObj.eliminarExamenM(id).then((resp) => {
        if (resp.success) {
          Swal.fire(
            "Eliminado",
            "El registro se eliminó correctamente.",
            "success",
          );
          ExamenMObj.tblExamenMSession("tblExamenMedico", 1);
        } else {
          Swal.fire("Error", resp.error || "No se pudo eliminar.", "error");
        }
      });
    }
  });
};

peso.addEventListener("keyup", (e) => {
  e.preventDefault();
  calcularIMC();
});

talla.addEventListener("keyup", (e) => {
  e.preventDefault();
  calcularIMC();
});

function calcularIMC() {
  let pesoCalc = parseFloat(peso.value);
  let tallaCalc = parseFloat(talla.value);
  let imcCalc = 0;

  imcCalc = (pesoCalc / Math.pow(tallaCalc, 2)).toFixed(2);

  imc.value = imcCalc;
  clasificarIMC(imcCalc);
}

function clasificarIMC(imc) {
  let imcCalc = imc;
  const selectIMC = ClasificacionIMC;

  for (let option of selectIMC.options) {
    const min = parseFloat(option.getAttribute("data-min"));
    const max = parseFloat(option.getAttribute("data-max"));
    if (imcCalc >= min && imcCalc < max) {
      option.selected = true;
      break;
    }
  }
}

// Proceso para mostrar PDF de consentimiento
// fechanaimiento.addEventListener("change", (e) => {
//   e.preventDefault();
//   document.getElementById("consentimiento").hidden = false;
// });

fechanaimiento.addEventListener("change", (e) => {
  e.preventDefault();
  document.getElementById("consentimiento").hidden = false;
  if (fechanaimiento.value) {
    const nac = new Date(fechanaimiento.value);
    const hoy = new Date();
    let años = hoy.getFullYear() - nac.getFullYear();
    const m = hoy.getMonth() - nac.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) años--;
    if (!isNaN(años) && años >= 0) edad.value = años;
  }
});

document.getElementById("consentimiento").addEventListener("click", (e) => {
  e.preventDefault();
  ExamenMObj.generarReporteConsentimiento(
    noemp.value,
    fechanaimiento.value,
    canvas,
  );
});

document.getElementById("ta").addEventListener("blur", (e) => {
  e.preventDefault();
  const presionValue = parseInt(document.getElementById("ta").value);
  const presionArterialClass = document.getElementById("presionArterial");

  // Limpiar todas las clases posibles antes de asignar nuevas
  presionArterialClass.classList.remove(
    "bg-success",
    "text-white",
    "bg-warning",
    "text-dark",
    "bd-red-200",
    "bd-red-500",
    "bd-red-700",
  );

  if (presionValue < 80) {
    presionArterialClass.value = "Hipotensión";
  } else if (presionValue > 0 && presionValue <= 120) {
    presionArterialClass.value = "Normal";
    presionArterialClass.classList.add("bg-success", "text-white");
  } else if (presionValue > 120 && presionValue <= 129) {
    presionArterialClass.value = "Elevada";
    presionArterialClass.classList.add("bg-warning", "text-dark");
  } else if (presionValue >= 130 && presionValue <= 139) {
    presionArterialClass.value = "Presión arterial alta - Nivel 1";
    presionArterialClass.classList.add("bd-red-200");
  } else if (presionValue >= 140 && presionValue <= 179) {
    presionArterialClass.value = "Presión arterial alta - Nivel 2";
    presionArterialClass.classList.add("bd-red-500");
  } else if (presionValue >= 180) {
    presionArterialClass.value = "Crisis hipertensiva";
    presionArterialClass.classList.add("bd-red-700");
  }
});

// Función centralizada para actualizar clasificación y estilos según el valor de TA
function actualizarPresionArterial() {
  const presionValue = parseInt(document.getElementById("ta").value);
  const presionArterialClass = document.getElementById("presionArterial");
  if (!presionArterialClass) return;

  // Limpiar todas las clases posibles antes de asignar nuevas
  presionArterialClass.classList.remove(
    "bg-success",
    "text-white",
    "bg-warning",
    "text-dark",
    "bd-red-200",
    "bd-red-500",
    "bd-red-700",
  );

  if (isNaN(presionValue)) {
    presionArterialClass.value = "";
    return;
  }

  if (presionValue < 80) {
    presionArterialClass.value = "Hipotensión";
  } else if (presionValue >= 80 && presionValue < 120) {
    presionArterialClass.value = "Normal";
    presionArterialClass.classList.add("bg-success", "text-white");
  } else if (presionValue >= 120 && presionValue <= 129) {
    presionArterialClass.value = "Elevada";
    presionArterialClass.classList.add("bg-warning", "text-dark");
  } else if (presionValue >= 130 && presionValue <= 139) {
    presionArterialClass.value = "Presión arterial alta - Nivel 1";
    presionArterialClass.classList.add("bd-red-200");
  } else if (presionValue >= 140 && presionValue <= 179) {
    presionArterialClass.value = "Presión arterial alta - Nivel 2";
    presionArterialClass.classList.add("bd-red-500");
  } else if (presionValue >= 180) {
    presionArterialClass.value = "Crisis hipertensiva";
    presionArterialClass.classList.add("bd-red-700");
  }
}

// Escucha cambios de usuario
document.getElementById("ta").addEventListener("change", (e) => {
  e.preventDefault();
  actualizarPresionArterial();
});

document.getElementById("btnExportarExamen").addEventListener("click", (e) => {
  e.preventDefault();
  window.location.href = "php/ExamenM.php?exportarExamenM";
});

// Intercepta asignaciones programáticas a ta.value (por ejemplo desde la función de editar)
// Esto redefine la propiedad 'value' solo en la instancia del input 'ta' y llama a la función al setear.
(function interceptTaValueAssignment() {
  const taEl = document.getElementById("ta");
  if (!taEl) return;
  const nativeDesc = Object.getOwnPropertyDescriptor(
    HTMLInputElement.prototype,
    "value",
  );
  if (!nativeDesc || !nativeDesc.configurable) return;

  Object.defineProperty(taEl, "value", {
    get() {
      return nativeDesc.get.call(this);
    },
    set(v) {
      nativeDesc.set.call(this, v);
      // Llamar a la actualización inmediatamente después de asignar programáticamente
      actualizarPresionArterial();
    },
    configurable: true,
    enumerable: true,
  });
})();
