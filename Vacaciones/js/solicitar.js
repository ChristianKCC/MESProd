const formSolicitud = document.getElementById("formSolicitud");
if (formSolicitud) {
  formSolicitud.addEventListener("submit", function (e) {
    const diasSeleccionados = parseInt(
      document.getElementById("dias-input").value || "0",
      10,
    );
    const puesto = document.getElementById("tipo-empleado").value;
    const limiteDias = parseInt(
      document.getElementById("limite-dias").value || "0",
      10,
    );
    const tipo = document.querySelector("input[name='tipo']").value || "Normal";

    // Si es adelanto, solo validamos que haya al menos 1 día
    if (tipo === "Adelanto") {
      if (diasSeleccionados <= 0) {
        e.preventDefault();
        Swal.fire("Error", "Debes seleccionar al menos 1 día.", "error");
        return;
      }
      return;
    }

    // Validaciones normales
    if (diasSeleccionados <= 0) {
      e.preventDefault();
      Swal.fire("Error", "Debes seleccionar al menos 1 día.", "error");
      return;
    }
    if (diasSeleccionados > limiteDias) {
      e.preventDefault();
      Swal.fire("Error", "No tienes suficientes días disponibles.", "error");
      return;
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  let diasSeleccionados = 0;
  const calendarEl = document.getElementById("calendar");
  const diasFestivos = [
    // Por ley
    {
      title: "Año Nuevo",
      start: "2026-01-01",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Día de la Constitución",
      start: "2026-02-02",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Natalicio de Benito Juárez",
      start: "2026-03-16",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Día del Trabajo",
      start: "2026-05-01",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Día de la Independencia",
      start: "2026-09-16",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Revolución Mexicana",
      start: "2026-11-16",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Cambio de puesto presidencial",
      start: "2030-12-1",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Cambio de puesto presidencial",
      start: "2036-12-1",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Cambio de puesto presidencial",
      start: "2042-12-1",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Navidad",
      start: "2026-12-25",
      color: "#ff6666",
      textColor: "#fff",
    },

    // Adicionales
    {
      title: "Viernes Santo",
      start: "2026-04-03",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Jueves Santo",
      start: "2026-04-02",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Día de muertos",
      start: "2026-11-02",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Día de la Virgen de Guadalupe",
      start: "2026-12-12",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Nochebuena",
      start: "2026-12-24",
      color: "#ff6666",
      textColor: "#fff",
    },
    {
      title: "Fin de año",
      start: "2026-12-31",
      color: "#ff6666",
      textColor: "#fff",
    },
  ];

  // Mantener hoy como objeto Date
  const hoy = new Date();

  // Calcular límite un mes atrás
  const limiteMesAtras = new Date();
  limiteMesAtras.setMonth(hoy.getMonth() - 1);
  const diasBloqueadosSet = new Set();

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    locale: "es",
    selectable: true,
    events: [
      ...eventosBloqueados,
      ...diasFestivos,
      {
        start: "1900-01-01",
        end: new Date(limiteMesAtras.getTime() + 86400000)
          .toISOString()
          .split("T")[0],
        display: "background",
        color: "#5d5e5f6e",
        opacity: 0.3,
      },
    ],
    // selectAllow: function (selectionInfo) {
    //   // Evitar que se seleccionen días anteriores a un mes atrás
    //   return selectionInfo.start >= limiteMesAtras;
    // },
    selectAllow: function (selectionInfo) {
      if (selectionInfo.start < limiteMesAtras) return false;
      let cursor = new Date(selectionInfo.start);
      const fin = new Date(selectionInfo.end);
      while (cursor < fin) {
        if (diasBloqueadosSet.has(cursor.toISOString().split("T")[0]))
          return false;
        cursor.setDate(cursor.getDate() + 1);
      }
      return true;
    },

    select: function (info) {
      const start = new Date(info.startStr);
      const end = new Date(info.endStr);
      diasSeleccionados = (end - start) / (1000 * 60 * 60 * 24);
      document.getElementById("dias-seleccionados").textContent =
        diasSeleccionados;
      document.getElementById("dias-input").value = diasSeleccionados;
      document.getElementById("fecha-de").value = info.startStr;
      document.getElementById("fecha-a").value = new Date(
        end.getTime() - 86400000,
      )
        .toISOString()
        .split("T")[0];

      // Guardar todos los días seleccionados en un array con fecha completa
      let diasArray = [];
      let cursor = new Date(start);
      while (cursor < end) {
        diasArray.push(cursor.toISOString().split("T")[0]);
        cursor.setDate(cursor.getDate() + 1);
      }
      document.getElementById("dias-festivos").value = diasArray.join(",");
    },
  });

  calendar.render();

  (async () => {
    const ibmSolicitud = document.getElementById("ibmSolicitud")?.value || "";
    if (!ibmSolicitud) return;
    try {
      const resp = await fetch(
        `php/diasBloqueadosValidados.php?ibm=${encodeURIComponent(ibmSolicitud)}`,
      );
      const eventos = await resp.json();
      (eventos || []).forEach((ev) => {
        calendar.addEvent(ev); // fondo verde
        let cursor = new Date(ev.start),
          fin = new Date(ev.end);
        while (cursor < fin) {
          diasBloqueadosSet.add(cursor.toISOString().split("T")[0]);
          cursor.setDate(cursor.getDate() + 1);
        }
      });
    } catch (e) {
      console.error("No se pudieron cargar días bloqueados:", e);
    }
  })();

  const unselect = document.getElementById("btn-unselect");
  unselect.addEventListener("click", () => {
    calendar.unselect();
    document.getElementById("dias-seleccionados").textContent = 0;
    document.getElementById("dias-input").value = 0;
  });

  // -------------------------------------------------------------------------------------------------------------------------
  const driver = window.driver.js.driver;

  const steps = [
    {
      element: ".alert.alert-primary",
      popover: {
        title: "Instrucciones",
        description:
          "Aqui encontraras información basica de como funciona el sistema",
        side: "bottom",
      },
    },
    {
      element: ".card:has(.fa-id-card)",
      popover: {
        title: "Tu información",
        description:
          "Aquí verás tus datos personales: nombre, IBM, días disponibles, tipo de empleado y tipo de solicitud.",
        side: "left",
      },
    },
    {
      element: "#calendar",
      popover: {
        title: "Calendario",
        description:
          "Desde esta sección marca los días de vacaciones deseados haciendo clic en la fecha de inicio y arrastrando hasta el día de finalización. Si tus días de vacaciones pasan estan entre dos meses y no puedes seleccionar los del siguiente mes, realiza una segunda solicitud con el resto de dias correspondientes.",
        side: "top",
      },
    },
    {
      element: "#btn-continuar",
      popover: {
        title: "Continuar",
        description:
          "Cuando hayas seleccionado tus días, presiona este botón para avanzar con la solicitud.",
        side: "top",
      },
    },
    {
      element: "#btn-unselect",
      popover: {
        title: "Quitar selección",
        description:
          "Si te equivocaste o quieres seleccionar otro rango de dias, usa este botón para limpiar los días seleccionados anteriormente.",
        side: "top",
      },
    },
    {
      element: "#btn-regresar",
      popover: {
        title: "Regresar",
        description:
          "Este botón te devuelve a la pantalla principal de 'Mis Vacaciones'.",
        side: "top",
      },
    },
    {
      element: ".card:has(.fa-circle-info)",
      popover: {
        title: "Significado de colores",
        description:
          "Aquí se explica qué significa cada color en el calendario: día actual, pendiente, aprobado, festivo y no disponible.",
        side: "left",
      },
    },
    {
      element: "#btnAyuda",
      popover: {
        title: "Volver a ver el tutorial",
        description:
          "Si necesitas repasar cómo usar esta pantalla, presiona este botón para repetir el tutorial.",
        side: "bottom",
      },
    },
  ];

  const driverObj = driver({
    showProgress: true,
    allowClose: false,
    disableInteraction: true,
    progressText: "Paso {{current}} de {{total}}",
    doneBtnText: "Finalizar",
    nextBtnText: "Siguiente",
    prevBtnText: "Atrás",
    steps,
  });

  // Clave única para este tutorial
  const tutorialKey = "tutorial_solicitarJE";
  const tutorialYaVisto = localStorage.getItem(tutorialKey);

  if (!tutorialYaVisto) {
    driverObj.drive();
    localStorage.setItem(tutorialKey, "true");
  }

  // Botón de ayuda para relanzar el tutorial
  const btnAyuda = document.getElementById("btnAyuda");
  if (btnAyuda) {
    btnAyuda.addEventListener("click", () => {
      driverObj.drive();
    });
  }
});

class Vacaciones {
  // Funciona para la primer tabla cuando se selecciona (Ver folios) para ello se cargan los datos de registros o folios creados
  async tblenc() {
    // Obtencion de datos segun fetch
    const respuetaraw = await fetch("php/index.php?tblenc");
    const respuesta = await respuetaraw.json();
    let body = "";
    respuesta.forEach((elemento) => {
      body += `<tr>
                <td>${elemento.id}</td>
                <td>${elemento.fecha}</td>
                <td>${elemento.departamento}</td>
                <td>${elemento.creado}</td>
                <td>`;
      if (elemento.terminado == null) {
        body += `<button class="btn btn-sm btn-warning" onclick="editEnc(${elemento.id})"><i class="fa-solid fa-pen-to-square"> </i> Crear/eliminar registros </button> `;
      }
      // En el caso de que ya se haya finalizado la solicitud o ya se contenga un valor, este solo muestra el PDF del resultado sin posibilidad a crear o eliminar mas registros
      else {
        body += `<button class="btn btn-sm btn-danger" onclick="pdfFin(${elemento.id})"> <i class="fa-solid fa-file-pdf"> </i> Descargar resultado PDF </button></td>`;
      }
    });
    // Inyeccion de la tabla en el HTML
    document.getElementById("tblenc").innerHTML = body;
  }
}
