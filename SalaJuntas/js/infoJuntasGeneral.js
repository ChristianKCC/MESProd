async function tblConsultaSalaAgendada() {
  const dataPromise = await fetch("php/Salas.php?salasReservadas");
  const dataraw = await dataPromise.json();
  const ahora = new Date();
  const hoy = ahora.toISOString().split("T")[0];

  const contenedor = document.getElementById("informacionSalasAgendadas");
  contenedor.innerHTML = "";

  const salas = {};

  // Registrar todos los nombres de salas, aunque no tengan reuniones hoy
  dataraw.forEach((element) => {
    const numSala = element.numSala;
    if (!salas[numSala]) {
      salas[numSala] = {
        nombre: element.NombreSala,
        reuniones: [],
      };
    }

    // Solo agregar reuniones de hoy con estado 2
    if (element.estado === 2 && element.fecha === hoy) {
      salas[numSala].reuniones.push(element);
    }
  });

  // Obtener por número de sala
  const numerosDeSala = Object.keys(salas).sort((a, b) => a - b);

  // Iterar por dada sala para obtener sus valores
  numerosDeSala.forEach((numSala) => {
    const { nombre, reuniones } = salas[numSala];
    const ul = document.createElement("ul");
    ul.classList.add("list-group");
    ul.id = `sala_${numSala}_list`;

    if (reuniones.length === 0) {
      const li = document.createElement("li");
      li.className = "list-group-item list-group-item-warning";
      li.textContent = "Sin reuniones programadas para hoy.";
      ul.appendChild(li);
    } else {
      reuniones.forEach((element) => {
        const fechaInicio = new Date(`${element.fecha}T${element.HoraInicio}`);
        const fechaFinal = new Date(`${element.fecha}T${element.HoraFin}`);

        let clase =
          "list-group-item list-group-item-action list-group-item-success";
        if (ahora >= fechaInicio && ahora <= fechaFinal) {
          clase = "list-group-item list-group-item-action list-group-item-info";
        } else if (ahora > fechaFinal) {
          clase =
            "list-group-item list-group-item-action list-group-item-secondary";
        }

        // Crear elementos li y guardarlos en los eleemntos ul
        const item = document.createElement("li");
        item.className = clase;
        item.innerHTML = `
          <strong>Nombre del expositor:</strong> ${element.NombreAgenda}
          <br> 
          <strong>Titulo de la reunión:</strong> ${element.titulo} 
          <br>
          <strong>Descripción:</strong> ${element.descripcion}
          <br>
          <strong>Fecha:</strong> ${element.fecha}
          <br>
          <strong>Horario:</strong> ${element.HoraInicio} - ${element.HoraFin}
        
        `;
        ul.appendChild(item);
      });
    }

    // Crear las tarjetas de salas de forma dinámica
    const card = document.createElement("div");
    card.className = "col";
    card.innerHTML = `
      <div class="card border-secondary mb-3">
        <div class="card-header">
          <h5 class="card-title">Reuniones - ${nombre}</h5>
        </div>
        <div class="card-body"></div>
      </div>
    `;
    card.querySelector(".card-body").appendChild(ul);
    contenedor.appendChild(card);
  });
}

tblConsultaSalaAgendada(); // Primera ejecución inmediata

setInterval(() => {
  tblConsultaSalaAgendada();
}, 15000); // Cada 15 segundos
