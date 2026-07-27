import { Toolsjs } from "../../Tools/Tools.js";
import { SalaJuntas } from "../module/index.js";
import { RFacial } from "../../Modules/Autenticacion.js";

// Concstructores de objetos
const Tools = new Toolsjs();
const SalaJuntasObj = new SalaJuntas();
const rfacial = new RFacial("video", "overlay", "result", null);

// Declaración de variables
const card = document.getElementById("card-juntas");
const estadoSala = document.getElementById("estadoSala");
const tituloJunta = document.getElementById("tituloJunta");
const nombre = document.getElementById("nombre");
const descripcion = document.getElementById("descripcion");
const fecha = document.getElementById("fecha");
const horario = document.getElementById("horario");
const capacitacion = document.getElementById("capacitacion");
const btnFacial = document.getElementById("recFacial");
const var2 = window.location.search;
const var3 = new URLSearchParams(var2);
const salaId = var3.get("sala");
let numSala = null;
let idReunion = null;
const idSala = salaId;

// Verificar que el id no sea vacio
if (idSala !== "") {
  limpiarCampos();
  numSala = idSala;
  actualizarSalas(idSala);
  cargarListaReunionesAgendadas(idSala);
}

// Actualizar las tarjetas de sala para mostrar la información actualizada
async function actualizarSalas(idSala) {
  try {
    // Obtener datos del servidor (incluye fecha correcta)
    const response = await SalaJuntasObj.dataJuntas(idSala);
    const reuniones = response.reuniones;
    const hoyServidor = response.hoyServidor;

    const ahora = new Date();

    SalaJuntasObj.datosReunionActual = null;

    // Limpiar tarjeta si no hay reuniones
    if (!reuniones || reuniones.length === 0) {
      limpiarCampos();
      card.classList.add("list-group-item-secondary");
      card.classList.remove("list-group-item-info");
      estadoSala.innerText = "No hay reuniones programadas para el día de hoy";
      estadoSala.style.display = "block";
      return;
    }

    // Buscar reunión en curso (usando fecha del servidor)
    const reunionActual = reuniones.find((reunion) => {
      const inicio = new Date(`${hoyServidor}T${reunion.horaInicio}`);
      const final = new Date(`${hoyServidor}T${reunion.horaFin}`);
      return ahora >= inicio && ahora <= final;
    });

    // Mostrar reunión en curso si existe
    if (reunionActual) {
      limpiarCampos();
      estadoSala.style.display = "none";
      card.classList.remove("list-group-item-secondary");
      card.classList.add("list-group-item-info");

      estadoSala.innerText = "Reunión en curso";
      estadoSala.style.display = "block";
      tituloJunta.innerText = reunionActual.titulo;
      nombre.innerText = reunionActual.nombre;
      descripcion.innerText = reunionActual.descripcion;
      capacitacion.checked = reunionActual.capacitacion == 1;
      fecha.innerText = reunionActual.fecha;
      horario.innerText = `${reunionActual.horaInicio} - ${reunionActual.horaFin}`;

      idReunion = reunionActual.idJunta;

      return;
    }

    // Buscar próxima reunión del día
    const siguienteReunion = reuniones.find((reunion) => {
      const inicio = new Date(`${hoyServidor}T${reunion.horaInicio}`);
      return ahora < inicio;
    });

    // Mostrar estado según próxima reunión
    limpiarCampos();
    if (siguienteReunion) {
      estadoSala.style.display = "block";
      estadoSala.innerText = `Esta sala está libre hasta las: ${siguienteReunion.horaInicio}`;
      card.classList.add("list-group-item-secondary");
      card.classList.remove("list-group-item-info");

      // Guardar datos para posible reserva
      SalaJuntasObj.datosReunionActual = siguienteReunion;
    } else {
      estadoSala.innerText =
        "No hay más reuniones programadas para el día de hoy";
      estadoSala.style.display = "block";
      card.classList.add("list-group-item-secondary");
      card.classList.remove("list-group-item-info");
      SalaJuntasObj.datosReunionActual = null;
    }
  } catch (error) {
    console.error("Error en actualizarSalas:", error);
    estadoSala.innerText = "Error al cargar reuniones";
    estadoSala.style.display = "block";
    SalaJuntasObj.datosReunionActual = null;
  }
}

// Crear una lista para guardar las listas que esten programadas para el día actual
async function cargarListaReunionesAgendadas(idSala) {
  const response = await SalaJuntasObj.dataJuntas(idSala);
  const reuniones = response.reuniones;
  const hoy = new Date().toISOString().split("T")[0];
  const ahora = new Date();

  const lista = document.getElementById("listaReunionesAgendadas");
  lista.innerHTML = ""; // Limpiar contenido anterior

  const agendadasHoy = reuniones.filter((r) => {
    if (r.estado !== 2 || r.fecha !== hoy) return false;

    const horaInicioReunion = new Date(`${r.fecha}T${r.horaInicio}`);
    return ahora < horaInicioReunion;
  });

  if (agendadasHoy.length === 0) {
    const li = document.createElement("li");
    li.textContent = "No hay reuniones agendadas para hoy.";
    li.classList.add("list-group-item", "list-group-item-secondary");
    li.classList.remove("list-group-item-success");
    lista.appendChild(li);
    return;
  }

  agendadasHoy.forEach((reunion) => {
    const li = document.createElement("li");
    li.classList.add("list-group-item", "list-group-item-success");
    li.innerHTML = `
      <strong>Nombre del expositor:</strong> ${reunion.nombre}
      <br> 
      <strong>Titulo de la reunión:</strong> ${reunion.titulo} 
      <br>
      <strong>Descripción:</strong> ${reunion.descripcion}
      <br>
      <strong>Fecha:</strong> ${reunion.fecha}
      <br>
      <strong>Horario:</strong> ${reunion.horaInicio} - ${reunion.horaFin}
    
    `;
    lista.appendChild(li);
  });
}

function limpiarCampos() {
  tituloJunta.innerText = "Titulo de Junta";
  nombre.innerText = "";
  descripcion.innerText = "";
  fecha.innerText = "";
  horario.innerText = "";
  capacitacion.checked = 0;
}

btnFacial.addEventListener("click", async () => {
  rfacial.startRecognition().then((noempface) => {
    noempface = noempface.trim();
    console.log(noempface);
    if (noempface === "No encontrado") {
      swal.fire({
        title: "Lo siento",
        text: "Rostro no encontrado",
        icon: "warning",
        timer: 2000,
        timerProgressBar: true,
        didOpen: () => {
          Swal.showLoading();
          const timer = Swal.getPopup().querySelector("b");
          timerInterval = setInterval(() => {
            timer.textContent = `${Swal.getTimerLeft()}`;
          }, 100);
        },
        willClose: () => {
          clearInterval(timerInterval);
        },
      });
      return false;
    }
    SalaJuntasObj.guardarRegistroSala(idReunion, noempface);
  });
});

setInterval(() => {
  if (numSala) {
    actualizarSalas(numSala);
    cargarListaReunionesAgendadas(numSala);
  }
}, 15000);
