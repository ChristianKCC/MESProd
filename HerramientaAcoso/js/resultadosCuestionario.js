let infoCuestionarios = [];
let currentPage = 1;
let pageSize = 20;

const questions = [
  "Mi superior restringe mis posibilidades de comunicarme, hablar o reunirme con el...",
  "Me ignoran, me excluyen o me hacen el vacío, fingen no verme o me hacen 'invisible'...",
  "Me interrumpeb continuamente  impidiendo expresarme...",
  "Me fuerzan a realizar trabajos que van contra mis principios o  mi ética...",
  "Evalúan mi trabajo de manera inequitativa o de forma sesgada...",
  "Me dejan sin ningún trabajo que hacer, ni siquiera a iniciativa propia...",
  "me asignan tareas o trabajos absurdos o sin sentido...",
  "Me asignan tareas o trabajos por debajo de mi capacidad profesional o mis competencias...",
  "Me asignan tareas rutinarias o sin valor o interés alguno...",
  "Me abruman con una carga de trabajo insoportable de manera malintencionada...",
  "Me asignan tareas que ponen en peligro mi integridad física o mi salud a proposito...",
  "Me impiden que adopten las medidas de seguridad necesarias para realizar mi trabajo con la debida seguridad...",
  "Se me ocasionan gastos con intención de perjudicarme económicamente...",
  "Prohíben a mis colegas hablar conmigo...",
  "Minusvaloran y echan por tierra mi trabajo, no importa lo que haga...",
  "Me acusan injustificadamente de incumplimientos, errores, fallos, inconcretos y difusos...",
  "Recibo críticas y reproches por cualquier cosa que haga o decisión que tome en mi trabajo...",
  "Se amplifican y dramatizan de manera injustificada errores pequeños o intrascendentes...",
  "Me humillan, desprecian o minusvaloran en público ante otros colegas o ante otras personas...",
  "Me amenazan con usar instrumentos disciplinarios (recisión de contrato, expedientes, despido, traslados, etc.)...",
  "Intentan aislarme de mis colegas, dándome trabajos o tareas que me alejan físicamente de ellos...",
  "Distorcionan malintencionadamente lo qeu digo o hago en mi trabajo...",
  "Se intenta buscarme las cosquillas para 'hacerme explotar'...",
  "Me menosprecian personal o profesionalmente...",
  "Hacen burla de mí o bromas intentado ridiculizar mi forma de hablar, de andar, etc..",
  "Recibo feroces e injustas críticas acerca de aspectos de mi vida personal...",
  "Recibo amenazas verbales o mediante gestos intimidatorios...",
  "Recibo amenazas por escrito o por teléfono en mi domicilio...",
  "Me chillan o gritan, o elevan la voz de manera a intimidarme...",
  "Me zarandean, empuja o avasallan físicamente parra intimidarme...",
  "Se hacen bromas inapropiadas y crueles acera de mí...",
  "Inventan o difunden rumores y calumnias acerca de mí, de manera malintencionada...",
  "Me privan de información imprescindible y necesaria para ahcer mi trabajo...",
  "Limitan malintencionadamente mi acceso a cursos, promociones, ascensos, etc...",
  "Me atribuyen malintencionadamente conductas ilícitas o antiéticas para perjudicar mi imagen y reputación...",
  "Recibo una presión indebida para sacar adelante el trabajo...",
  "Me asignan plazos de ejecución o cargas de trabajo irrazonables...",
  "Modifican mis responsaibilidades o las tareas a ejecutar sin decirme nada...",
  "Desvaloran continuamente mi esfuerzo profesional...",
  "Intentan persistentemente desmoralizarme...",
  "Utilizan varias formas de hacerme incurrir en errores profesionales de manera malintencionada...",
  "Controlan aspectos de mi trabajo de forma malintencionada para intentar 'pillarme en algún renuncio'...",
  "Me lanzan insinuaciones o proposiciones sexuales directas o indirectas...",
  "En el transcurso de los últimos 6 meses ¿Ha sido Ud. víctima de por lo menos alguna de las anteriores formas de maltrato psicológioco de manera continuada (con una frecuencia de más de 1 vez por semana)? (ver lista de preguntas 1 a 43)",
];

const subjects = [
  { text: "Jefas/jefes o personas supervisoras", value: 1 },
  { text: "Personas compañeras de trabajo", value: 2 },
  { text: "Personas subordinadas", value: 3 },
];

const frequencies = [
  { text: "Nunca", value: 0 },
  { text: "Pocas veces al año o menos", value: 1 },
  { text: "Una vez al mes o menos", value: 2 },
  { text: "Algunas veces al mes", value: 3 },
  { text: "Una vez a la semana", value: 4 },
  { text: "Varias veces a la semana", value: 5 },
  { text: "Todos los días", value: 6 },
];

async function resultadosCuestionario() {
  try {
    const response = await axios.get(
      "./php/cuestionario.php?resultadosCuestionario"
    );
    infoCuestionarios = response.data.map((item) => ({
      fecha: item.fecha,
      noEmp: item.noemp,
      neap: item.neap,
      igap: item.igap,
      imap: item.imap,
      nombre: item.nombre,
      departamento: item.departamento,
      puesto: item.puesto,
      respuestas: item.respuestas,
      id: item.id,
    }));
    mostrartabla();
  } catch (error) {
    console.error(error);
  }
}
function mostrartabla() {
  console.log(infoCuestionarios);
  const tbody = document.getElementById("tableBody");
  tbody.innerHTML = "";
  const totalRegistros = infoCuestionarios.length;
  const totalPaginas = Math.ceil(totalRegistros / pageSize);

  if (currentPage > totalPaginas) currentPage = totalPaginas || 1;

  const inicio = (currentPage - 1) * pageSize;
  const fin = inicio + pageSize;

  const paginaActualDatos = infoCuestionarios.slice(inicio, fin);
  paginaActualDatos.forEach((item) => {
    const formatNumber = (v) => {
      if (v === null || v === undefined || v === "") return "";
      let s = String(v).trim();
      // Añadir cero líder cuando venga como ".23" o "-.23"
      s = s.replace(/^([+-]?)\.(\d+)$/, "$10.$2");
      return s;
    };

    const row = `
        <tr>
            <td>${item.fecha}</td>
            <td>${item.noEmp}</td>
            <td>${formatNumber(item.neap)}</td>
            <td>${formatNumber(item.igap)}</td>
            <td>${formatNumber(item.imap)}</td>
            <td>
            <center><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalVerRespuestas" data-bs-whatever="${
              item.id
            }">Ver respuestas</button></center></td>
        </tr>
        `;
    tbody.innerHTML += row;
  });
  document.getElementById(
    "pageInfo"
  ).innerText = `Página ${currentPage} de ${totalPaginas}`;
}

// === Buscador ===
// document.getElementById("searchInput").addEventListener("input", (e) => {
//   currentPage = 1;
//   mostrarTabla(e.target.value);
// });

// === Cambiar cantidad por página ===
document.getElementById("pageSize").addEventListener("change", (e) => {
  pageSize = parseInt(e.target.value);
  currentPage = 1;
  mostrartabla();
});

// === Paginación ===
document.getElementById("prevPage").addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    mostrartabla();
  }
});

document.getElementById("nextPage").addEventListener("click", () => {
  currentPage++;
  mostrartabla();
});

const modalCuestionario = document.getElementById("modalVerRespuestas");

modalCuestionario.addEventListener("shown.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = modalCuestionario.querySelector(".modal-title");
  modalTitle.textContent = `Respuestas del cuestionario con folio #${recipient}`;

  const modalBody = modalCuestionario.querySelector(".modal-body");

  // Buscar registro por ID
  const data = infoCuestionarios.find((x) => x.id == recipient);
  if (data.imap < 2.8) {
    modalTitle.innerHTML += " - <strong>Nivel leve o inexistente</strong>";
    // eliminar colapso previo si existe
    const existingCollapse =
      modalCuestionario.querySelector("#collapseExample");
    if (existingCollapse) existingCollapse.remove();

    const modalHeader = modalCuestionario.querySelector(".modal-header");

    const collapseHtml = `
    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            Los resultados obtenidos no son contundentes; no parece que haya recibido agresiones de manera frecuente ni con gran intensidad. Se recomienda hablar con su jefe directo para mejorar estrategias de retroalimentación con el victimario. Se sugiere además, acercarse al Comité de Ética de su institución. Para mayor información sobre el acoso y hostigamiento psicológico laboral (mobbing), tenemos a su disposición cursos de capacitación dentro de esta plataforma.
        </div>
    </div>
    `;

    if (modalHeader) {
      modalHeader.insertAdjacentHTML("afterend", collapseHtml);
    } else {
      modalBody.insertAdjacentHTML("beforeend", collapseHtml);
    }
  } else if (data.imap > 2.8 && data.igap < 1.23) {
    modalTitle.innerHTML += " - <strong>Nivel medio</strong>";
    // eliminar colapso previo si existe
    const existingCollapse =
      modalCuestionario.querySelector("#collapseExample");
    if (existingCollapse) existingCollapse.remove();

    const modalHeader = modalCuestionario.querySelector(".modal-header");

    const collapseHtml = `
    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            Los resultados obtenidos muestran que usted puede estar en una primera fase de acoso. La institución debe tomar acciones, evaluando tanto al victimario como a los mecanismos de comunicación y prevención dentro de su institución. Favor de acercarse al área de enseñanza y/o al Comité de Ética de su establecimiento conforme al siguiente directorio.
            <br/><br/>
            <a href="https://calidad.salud.gob.mx/site/has/index.html#directorios" target="_blank" rel="noopener noreferrer">Ver directorio del Comité de Ética</a>
        </div>
    </div>
    `;

    if (modalHeader) {
      modalHeader.insertAdjacentHTML("afterend", collapseHtml);
    } else {
      modalBody.insertAdjacentHTML("beforeend", collapseHtml);
    }
  } else if (data.imap > 2.8 && data.igap > 1.23) {
    modalTitle.innerHTML += " - <strong>Nivel grave a muy grave</strong>";
    // eliminar colapso previo si existe
    const existingCollapse =
      modalCuestionario.querySelector("#collapseExample");
    if (existingCollapse) existingCollapse.remove();

    const modalHeader = modalCuestionario.querySelector(".modal-header");

    const collapseHtml = `
    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            Los resultados obtenidos refieren que el acoso es continuo, persistente o incluso intolerable por su gran incidencia. Es necesario poner un alto total y tomar medidas disciplinarias, actas administrativas u otras sanciones determinadas por la propia dependencia, conforme a la normatividad aplicable. Favor de acercarse al área de enseñanza, la cual ya debe haber recibido el informe sobre su situación, o al Comité de Ética o al OIC de su establecimiento de acuerdo al siguiente directorio.
            <br/><br/>
            <a href="https://calidad.salud.gob.mx/site/has/index.html#directorios" target="_blank" rel="noopener noreferrer">Ver directorio del Comité de Ética</a>
        </div>
    </div>
    `;

    if (modalHeader) {
      modalHeader.insertAdjacentHTML("afterend", collapseHtml);
    } else {
      modalBody.insertAdjacentHTML("beforeend", collapseHtml);
    }
  }

  // Parsear JSON de respuestas
  const respuestas = JSON.parse(data.respuestas);

  // Crear o reutilizar formulario
  const form =
    document.getElementById("quizForm") ||
    (() => {
      const f = document.createElement("form");
      f.id = "quizForm";
      modalBody.appendChild(f);
      return f;
    })();

  form.innerHTML = "";

  // Generar preguntas dinámicas
  questions.forEach((q, index) => {
    const div = document.createElement("div");
    div.className = "question";
    div.innerHTML = `
      <p><strong>${index + 1}. ${q}</strong></p>

      <div class="options">
        <label>¿Quién?</label>
        <select name="subject${index}">
          <option value="" disabled selected>Selecciona una opción</option>
          ${subjects
            .map((s) => `<option value="${s.value}">${s.text}</option>`)
            .join("")}
        </select>
      </div>

      <div class="frequency">
        <label>Frecuencia:</label>
        <select name="frequency${index}">
          <option value="" disabled selected>Selecciona una opción</option>
          ${frequencies
            .map((f) => `<option value="${f.value}">${f.text}</option>`)
            .join("")}
        </select>
      </div>
    `;
    form.appendChild(div);
  });

  // === CARGAR RESPUESTAS GUARDADAS ===
  respuestas.forEach((r) => {
    const selectSubject = form.querySelector(
      `select[name="subject${r.index}"]`
    );
    const selectFrequency = form.querySelector(
      `select[name="frequency${r.index}"]`
    );

    if (selectSubject) selectSubject.value = r.subjectValue ?? "";
    if (selectFrequency) selectFrequency.value = r.frequencyValue ?? "";
  });

  // === DESHABILITAR TODOS LOS SELECTS ===
  form.querySelectorAll("select").forEach((sel) => {
    sel.disabled = true;
  });
});

document.addEventListener("DOMContentLoaded", (e) => {
  resultadosCuestionario();
});
