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

/*
  NEAP = respuestas !== 0
  IGAP = sum(items) / questions.length -1
  IMAp = sum(items) / NEAP
*/

const btnScore = document.getElementById("calculateScore");
const noEmp = document.getElementById("noEmp");

// Render dinámico del formulario
const form =
  document.getElementById("quizForm") ||
  (() => {
    const f = document.createElement("form");
    f.id = "quizForm";
    document.body.appendChild(f);
    return f;
  })();

form.innerHTML = "";

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

function getResponses({ excludeLast = false } = {}) {
  const limit = excludeLast ? questions.length - 1 : questions.length;

  const responses = [];
  for (let i = 0; i < limit; i++) {
    const subjectEl = document.querySelector(`[name="subject${i}"]`);
    const frequencyEl = document.querySelector(`[name="frequency${i}"]`);

    // Valores seleccionados (numéricos)
    const subjectValue = subjectEl ? parseInt(subjectEl.value, 10) : null;
    const frequencyValue = frequencyEl ? parseInt(frequencyEl.value, 10) : null;

    // Texto visible de las opciones seleccionadas
    const subjectText = subjectEl
      ? subjectEl.options[subjectEl.selectedIndex]?.text ?? ""
      : "";
    const frequencyText = frequencyEl
      ? frequencyEl.options[frequencyEl.selectedIndex]?.text ?? ""
      : "";

    responses.push({
      index: i,
      question: questions[i],
      subjectValue,
      subjectText,
      frequencyValue,
      frequencyText,
    });
  }
  return responses;
}

// --- Cálculo de métricas ---
function computeMetrics() {
  const lastIndexExcluded = questions.length - 1; // Excluye la última
  const nItems = lastIndexExcluded;

  // Lee las frecuencias como enteros
  const freqValues = Array.from({ length: nItems }, (_, i) => {
    const el = document.querySelector(`[name="frequency${i}"]`);
    return el ? parseInt(el.value, 10) : 0;
  });

  // Suma total de puntajes
  const sum = freqValues.reduce((acc, v) => acc + (isNaN(v) ? 0 : v), 0);

  // NEAP: conteo de ítems con respuesta > 0
  const NEAP = freqValues.filter((v) => v > 0).length;

  // IGAP: suma / número de ítems (promedio global)
  const IGAP = nItems > 0 ? sum / nItems : 0;

  // IMAP: suma / NEAP (si NEAP=0 → 0)
  const IMAP = NEAP > 0 ? sum / NEAP : 0;

  return { sum, NEAP, IGAP, IMAP, nItems };
}

async function userSession() {
  const datapromise = await fetch("./php/cuestionario.php?dataUserSesion");
  const dataraw = await datapromise.json();
  return dataraw;
}

async function consultarCuestionario(noemp) {
  const data = new FormData();
  data.append("noemp", noemp);
  try {
    const response = await axios.post(
      "./php/cuestionario.php?consultarCuestionario",
      data
    );

    return response.data;
  } catch (error) {
    console.error("Error al consultar el cuestionario:", error);
    throw error;
  }
}

// Guardar el cuestionario
async function guardarCuestionario() {
  const { sum, NEAP, IGAP, IMAP, nItems } = computeMetrics();
  const respuestas = getResponses({ excludeLast: true });

  const data = new FormData();
  data.append("sum", sum);
  data.append("NEAP", NEAP);
  data.append("IGAP", IGAP);
  data.append("IMAP", IMAP);
  data.append("nItems", nItems);
  data.append("responses", JSON.stringify(respuestas));

  try {
    const response = await axios.post(
      "./php/cuestionario.php?guardarCuestionario",
      data
    );

    // Manejo de respuesta por status
    if (response.status === 200) {
      swal.fire({
        title: "¡Guardado!",
        text: "Tus respuestas han sido guardadas.",
        icon: "success",
      });
      return response.data; // Retorna datos si es necesario
    } else {
      // Cubre otros 2xx que no sean 200, o casos raros
      swal.fire(
        "Atención",
        `Respuesta inesperada del servidor (status: ${response.status})`,
        "warning"
      );
    }
  } catch (error) {
    console.error("Error al guardar el cuestionario:", error);
    throw error;
  }
}

btnScore.addEventListener("click", (e) => {
  e.preventDefault();
  Swal.fire({
    title: "¿Estás seguro de continuar?",
    text: "Verifica bien tus respuestas antes de guardar.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, guardar",
  }).then((result) => {
    if (result.isConfirmed) {
      consultarCuestionario(noEmp.value).then((data) => {
        console.log(data);

        if (!data || data.length === 0) {
          // No existe registro, entonces puede continuar a guardar
          guardarCuestionario();
          form.reset();
          return;
        }

        // Ya existe registro → validar
        if (data[0].estadoCuestionario == 1) {
          swal.fire(
            "Atención",
            "Ya has completado el cuestionario previamente.",
            "info"
          );
          return;
        }

        guardarCuestionario();
        form.reset();
      });
    }
  });
});

document.addEventListener("DOMContentLoaded", () => {
  Swal.fire({
    title: "Aviso",
    text: "El presente cuestionario es totalmente anónimo, tus datos están protegidos y no serán utilizado para ningún fin distinto a la evaluación del entorno laboral. Te agradecemos tu honestidad al responder.",
    icon: "info",
    confirmButtonText: "Entendido",
  }).then(() => {
    userSession().then((data) => {
      if (data && data.length > 0) {
        noEmp.value = data[0].noemp || "";
      }
    });
    resultadosCuestionario();
  });
});
