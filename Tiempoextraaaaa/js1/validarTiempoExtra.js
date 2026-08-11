import { Toolsjs } from "../../Tools/Tools.js";
class TExtra {
    constructor() {
        this.currentPage = 1;
        this.pageSize = 10;
        this.data = [];
       this.Tools = new Toolsjs();
    }

    async consulta(page = this.currentPage, pageSize = this.pageSize, folio = null, depto = null) {        
        // this.currentPage = page;
        // this.pageSize = pageSize;
        // this.Tools.llnarslc('CatalogoPersonal', "GetSlcDeps", "deptoSelect", 0);

        // // Leer el valor actual del select si no se pasó como parámetro
        // if (!depto) {
        //     depto = document.getElementById("deptoSelect")?.value || "";
        // }
        
        // let url = `php/index.php?tblValidarTE&page=${page}&pageSize=${pageSize}`;
        // if (folio) url += `&folio=${folio}`;
        // if (depto) url += `&deptoSelect=${depto}`;

        // console.log("valor en depto: ");
        // console.log(depto);

        // const respuestaRaw = await fetch(url);
        // const respuesta = await respuestaRaw.json();

        // this.data = respuesta;

        // // Llenar el select si aún no está inicializado
        // if (!this.foliosUnicos) {
        //      this.foliosUnicos = [...new Set(respuesta.map(f => f.folio))];
        //      const folioSelect = document.getElementById("folioSelect");
        //      folioSelect.innerHTML = `<option value="">Selecciona una opcion</option>`;
        //      this.foliosUnicos.forEach(folio => {
        //          folioSelect.innerHTML += `<option value="${folio}">${folio}</option>`;
        //      });
        //  }

        // this.renderTabla(respuesta);

        // // Guardar datos en la clase para poder filtrar después
        // this.data = respuesta;

        // // Generar folios únicos
        // const foliosUnicos = [...new Set(respuesta.map(f => f.folio))];

        // // Llenar el select una sola vez
        // const folioSelect = document.getElementById("folioSelect");
        //  folioSelect.innerHTML = `<option value="">Selecciona una opcion</option>`;
        //  foliosUnicos.forEach(folio => {
        //      folioSelect.innerHTML += `<option value="${folio}">${folio}</option>`;
        //  });
        this.currentPage = page;
        this.pageSize = pageSize;
        // this.Tools.llnarslc('CatalogoPersonal', "GetSlcDeps", "deptoSelect", 0);

        // Guardar el valor actual
const currentDepto = document.getElementById("deptoSelect")?.value || "";

// Repoblar el select
this.Tools.llnarslc('CatalogoPersonal', "GetSlcDeps", "deptoSelect", 0);

// Restaurar el valor seleccionado
if (currentDepto) {
    const deptoSelect = document.getElementById("deptoSelect");
    deptoSelect.value = currentDepto;
}


        if (!depto) {
            depto = document.getElementById("deptoSelect")?.value || "";
        }

        let url = `php/index.php?tblValidarTE&page=${page}&pageSize=${pageSize}`;
        if (folio) url += `&folio=${folio}`;
        if (depto) url += `&deptoSelect=${depto}`;

        const respuestaRaw = await fetch(url);
        const respuesta = await respuestaRaw.json();

        this.data = respuesta;

        // Llenar el select de folios una sola vez
        if (!this.foliosUnicos) {
            this.foliosUnicos = [...new Set(respuesta.map(f => f.folio))];
            const folioSelect = document.getElementById("folioSelect");
            folioSelect.innerHTML = `<option value="">Selecciona una opcion</option>`;
            this.foliosUnicos.forEach(folio => {
                folioSelect.innerHTML += `<option value="${folio}">${folio}</option>`;
            });
        }

        // Renderizar tabla
        this.renderTabla(respuesta);

        let body = "";
        respuesta.forEach(folio => {        
            let estadoClass = "badge bg-warning text-dark";
            let estadoTexto = "Sin turno registrado";
            const turnoReal = folio.turnoAsignado;
            if (turnoReal === "turno1") estadoTexto = "TURNO 1";
            else if (turnoReal === "turno2") estadoTexto = "TURNO 2";
            else if (turnoReal === "turno3") estadoTexto = "TURNO 3";
            else if (turnoReal === "mixto1") estadoTexto = "MIXTO 1";
            else if (turnoReal === "mixto2") estadoTexto = "MIXTO 2";
            else if (turnoReal === "mixto3") estadoTexto = "MIXTO 3";
            else if (turnoReal === "mixto4") estadoTexto = "MIXTO 4";
            else if (turnoReal === "turno3_12hrs") estadoTexto = "TURNO 3 - 12 HRS";
            else if (turnoReal === "turno2_12hrs") estadoTexto = "TURNO 2 - 12 HRS";

            body += `
                <tr>
                    <td>${folio.id}</td>
                    <td>${folio.folio}</td>
                    <td>${folio.noemp}</td>
                    <td>${folio.Nombre}</td>
                    <td>${folio.depto}</td>
                    <td>${folio.puesto}</td>
                    <td>${folio.horai}</td>                    
                    <td>${folio.horaf}</td>                    
                    <td>${folio.motivo}</td>
                    <td>${folio.razon}</td>
                    <td><span class="${estadoClass}">${estadoTexto}</span></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editarTE(${folio.id})">
                            <i class="fa-solid fa-arrows-rotate"></i> Corregir info.
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.folio})">
                            <i class="fa-solid fa-file-pdf"></i> Ver PDF
                        </button>
                    </td>
                </tr>
            `;
        });
        document.getElementById("tblTiempoExtra").innerHTML = body;
        this.renderTabla(respuesta);

    }

    renderTabla(lista) {
    let body = "";
    lista.forEach(folio => {
        let estadoClass = "badge bg-warning text-dark";
        let estadoTexto = "Sin turno registrado";
        const turnoReal = folio.turnoAsignado;
        if (turnoReal === "turno1") estadoTexto = "TURNO 1";
        else if (turnoReal === "turno2") estadoTexto = "TURNO 2";
        else if (turnoReal === "turno3") estadoTexto = "TURNO 3";
        else if (turnoReal === "mixto1") estadoTexto = "MIXTO 1";
        else if (turnoReal === "mixto2") estadoTexto = "MIXTO 2";
        else if (turnoReal === "mixto3") estadoTexto = "MIXTO 3";
        else if (turnoReal === "mixto4") estadoTexto = "MIXTO 4";
        else if (turnoReal === "turno3_12hrs") estadoTexto = "TURNO 3 - 12 HRS";
        else if (turnoReal === "turno2_12hrs") estadoTexto = "TURNO 2 - 12 HRS";

        body += `
            <tr>
                <td>${folio.id}</td>
                <td>${folio.folio}</td>
                <td>${folio.noemp}</td>
                <td>${folio.Nombre}</td>
                <td>${folio.depto}</td>
                <td>${folio.puesto}</td>
                <td>${folio.horai}</td>
                <td>${folio.horaf}</td>
                <td>${folio.motivo}</td>
                <td>${folio.razon}</td>
                <td><span class="${estadoClass}">${estadoTexto}</span></td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="editarTE(${folio.id})">
                        <i class="fa-solid fa-arrows-rotate"></i> Corregir info.
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="verPDF(${folio.folio})">
                        <i class="fa-solid fa-file-pdf"></i> Ver PDF
                    </button>
                </td>
            </tr>
        `;
    });
    document.getElementById("tblTiempoExtra").innerHTML = body;
}


    // Funcion para autorizar el tiempo extra con parametros como el id y quien lo autoriza
    async enviar(id, autor){        
        const respuestaraw = await fetch("./php/index.php?updateTE&id=" + id + "&autor=" + autor );
        const respuesta = await respuestaraw.json();

        respuesta === false ?
                    Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: respuesta.message || 'Hay un error con la base de datos.'
                    }) :
                    Swal.fire({
                            icon: 'success',
                            title: 'Autorizado',
                            text: 'El registro fue procesado con exito',
                            timer: 2000,
                            showConfirmButton: false
                    });

            window.location.reload();
    }

     filtrarPorFolio(folioId) {
         let filtrados = [];
         if (folioId) {
             filtrados = this.data.filter(e => e.folio == folioId);
         } else {
             filtrados = this.data;
         }

         let body = "";
         filtrados.forEach(e => {
             body += `
                 <tr>
                     <td>${e.id}</td>
                     <td>${e.folio}</td>
                     <td>${e.noemp}</td>
                     <td>${e.Nombre}</td>
                     <td>${e.depto}</td>
                     <td>${e.puesto}</td>
                     <td>${e.horai}</td>
                     <td>${e.horaf}</td>
                     <td>${e.motivo}</td>
                     <td>${e.razon}</td>
                     <td><span class="badge bg-warning text-dark">${e.turnoAsignado || "Sin turno"}</span></td>
                     <td>
                         <button class="btn btn-primary btn-sm" onclick="editarTE(${e.id})">
                             <i class="fa-solid fa-arrows-rotate"></i> Corregir info.
                         </button>
                         <button class="btn btn-danger btn-sm" onclick="verPDF(${e.folio})">
                             <i class="fa-solid fa-file-pdf"></i> Ver PDF
                         </button>
                     </td>
                 </tr>
             `;
         });
          document.getElementById("tblTiempoExtra").innerHTML = body;
         this.renderTabla(filtrados);
     }
    
}
function normalizarHora(hora) {    
    return hora.length > 5 ? hora.substring(0,5) : hora;
}

function calcularLapso() {
    let horaI = document.getElementById("editHoraI").value;
    let horaF = document.getElementById("editHoraF").value;

    if (!horaI || !horaF) {
        document.getElementById("tiempoExtraTmp").value = "";
        return;
    }

    horaI = normalizarHora(horaI);
    horaF = normalizarHora(horaF);

    const inicio = new Date(`1970-01-01T${horaI}`);
    const fin = new Date(`1970-01-01T${horaF}`);

    let diffMs = fin - inicio;
    if (diffMs < 0) diffMs += 24 * 60 * 60 * 1000;

    const diffMin = Math.floor(diffMs / 60000);
    const horas = Math.floor(diffMin / 60);
    const minutos = diffMin % 60;

    const hh = String(horas).padStart(2, "0");
    const mm = String(minutos).padStart(2, "0");

    document.getElementById("tiempoExtraTmp").value = `${hh}:${mm}`;
}

function colocarHrsbyTurno() {
    let turn = document.getElementById("editTurno").value;
    if(turn === "turno1"){
        document.getElementById("horaInicioTurno").value = "07:00:00";
        document.getElementById("horaFinTurno").value = "15:00:00";
    } else if(turn === "turno2"){
        document.getElementById("horaInicioTurno").value = "15:00:00";
        document.getElementById("horaFinTurno").value = "22:30:00";
    } else if(turn === "turno3"){
        document.getElementById("horaInicioTurno").value = "22:30:00";
        document.getElementById("horaFinTurno").value = "07:00:00";
    } else if(turn === "mixto1"){
        document.getElementById("horaInicioTurno").value = "07:30:00";
        document.getElementById("horaFinTurno").value = "17:00:00";
    } else if(turn === "mixto2"){
        document.getElementById("horaInicioTurno").value = "08:30:00";
        document.getElementById("horaFinTurno").value = "18:30:00";
    } else if(turn === "mixto3"){
        document.getElementById("horaInicioTurno").value = "07:00:00";
        document.getElementById("horaFinTurno").value = "16:30:00";
    } else if(turn === "mixto4"){
        document.getElementById("horaInicioTurno").value = "07:00:00";
        document.getElementById("horaFinTurno").value = "17:00:00";
    } else if(turn === "turno3_12hrs"){
        document.getElementById("horaInicioTurno").value = "19:00:00";
        document.getElementById("horaFinTurno").value = "07:00:00";
    } else if(turn === "turno2_12hrs"){
        document.getElementById("horaInicioTurno").value = "11:30:00";
        document.getElementById("horaFinTurno").value = "15:00:00";
    } else {
        document.getElementById("horaInicioTurno").value = "";
        document.getElementById("horaFinTurno").value = "";
    }
}

// Fin de clase de TExtra

document.getElementById("editHoraI").addEventListener("input", calcularLapso);
document.getElementById("editHoraF").addEventListener("input", calcularLapso);

window.Tex = new TExtra();
window.Tex.consulta();

window.verPDF = function(id) {
    if (!id) {
        Swal.fire('UPS!!!', 'No hay un folio válido', 'info');
        return false;
    }
    window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
};

window.editarTE = async function(id) {
    try {
        const respRaw = await fetch("php/index.php?tblGetTE=" + id);
        const data = await respRaw.json();

        document.getElementById("editId").value = data.id;
        document.getElementById("editFolio").value = data.folio;
        document.getElementById("editNoEmp").value = data.noemp;
        document.getElementById("editNombre").value = data.Nombre;
        document.getElementById("editDepto").value = data.depto;
        document.getElementById("editPuesto").value = data.puesto;
        
        document.getElementById("editHoraI").value = data.horai.substring(0,5);
        document.getElementById("editHoraF").value = data.horaf.substring(0,5);

        document.getElementById("editMotivo").value = data.motivo;
        document.getElementById("editRazon").value = data.razon;
        document.getElementById("editTurno").value = data.turnoAsignado; 
        
        calcularLapso();
        colocarHrsbyTurno();

        const modal = new bootstrap.Modal(document.getElementById("modalEditarTE"));
        modal.show();
    } catch (err) {
        Swal.fire("Error", "No se pudo cargar la información", "error");
    }
};

// Guardar cambios
document.getElementById("btnGuardarYVTE").addEventListener("click", async () => {
    const payload = {
        id: document.getElementById("editId").value,
        noemp: document.getElementById("editNoEmp").value,
        Nombre: document.getElementById("editNombre").value,
        depto: document.getElementById("editDepto").value,
        puesto: document.getElementById("editPuesto").value,
        horai: document.getElementById("editHoraI").value,
        horaf: document.getElementById("editHoraF").value,
        motivo: document.getElementById("editMotivo").value,
        razon: document.getElementById("editRazon").value,
        turnoAsignado: document.getElementById("editTurno").value,
        tiempoExtra: document.getElementById("tiempoExtraTmp").value
    };

    const resp = await fetch("php/index.php?updateTE", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    });

    const result = await resp.json();
    if (result.success) {
        Swal.fire("Correcto", "Información actualizada", "success");
        window.Tex.consulta();
        bootstrap.Modal.getInstance(document.getElementById("modalEditarTE")).hide();
    } else {
        Swal.fire("Error", "No se pudo actualizar", "error");
    }
});

// Acciones para el select de turno
document.getElementById("editTurno").addEventListener("click", async () =>{
    colocarHrsbyTurno();
});

// Eliminar registro
document.getElementById("btnDelete").addEventListener("click", async () => {
    const id = document.getElementById("editId").value;
    const resp = await fetch("php/index.php?deleteModalSub", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + encodeURIComponent(id)
    });

    const result = await resp.json();
    if (result.success) {
        Swal.fire("Correcto", "Registro eliminado", "success");
        window.Tex.consulta();
        bootstrap.Modal.getInstance(document.getElementById("modalEditarTE")).hide();
    } else {
        Swal.fire("Error", "No se pudo eliminar", "error");
    }
});


window.Autoriza = function (id) {
    window.Tex.enviar(id, 1);
}

window.Rechazar = function (id) {
    window.Tex.enviar(id, 2);
}

window.deleteSub = function (id) {
    window.Tex.deletesub(id);
}

const folioSelect = document.getElementById("folioSelect");
folioSelect.addEventListener("change", () => {
    const folio = folioSelect.value;
    if (folio) {
        window.Tex.filtrarPorFolio(folio);
    }
});

const deptoSelect = document.getElementById("deptoSelect");
deptoSelect.addEventListener("change", () => {
    const depto = deptoSelect.value;
    // Reinicia en página 1 para que el filtro se aplique desde el inicio
    window.Tex.consulta(1, window.Tex.pageSize, null, depto);
});


// document.getElementById("folioSelect").addEventListener("change", e => {
//     const folio = e.target.value;
//     if (folio) {
//         window.Tex.consulta(1, window.Tex.pageSize, folio);
//     } else {
//         window.Tex.consulta(1, window.Tex.pageSize);
//     }
// });

document.getElementById("allFolios").addEventListener("click", () => {
    window.Tex.consulta();
});

// Cambiar tamaño de página
document.getElementById("pageSizeSelect").addEventListener("change", e => {
    const size = parseInt(e.target.value, 10);
    window.Tex.consulta(1, size); // reinicia en página 1
});

// Botón anterior
document.getElementById("prevPage").addEventListener("click", () => {
    if (window.Tex.currentPage > 1) {
        window.Tex.consulta(window.Tex.currentPage - 1, window.Tex.pageSize);
    }
});

// Botón siguiente
document.getElementById("nextPage").addEventListener("click", () => {
    window.Tex.consulta(window.Tex.currentPage + 1, window.Tex.pageSize);
});


document.addEventListener("DOMContentLoaded", function () {
  const Driver = window.driver.js.driver;

  // -------------------------------
  // Pasos - Vista principal
  // -------------------------------
  const steps = [
    {
      element: ".tittlecont",
      popover: {
        title: "Validación de solicitudes",
        description: "Aquí podrás validar la información en las solicitudes de tiempo extra.",
        side: "bottom"
      }
    },
    {
      element: ".alert.alert-info",
      popover: {
        title: "Instrucciones",
        description: "Valida la información del empleado antes de enviarla a autorización de Gerencia.",
        side: "bottom"
      }
    },
    {
      element: "#pageSizeSelect",
      popover: {
        title: "Filtrar cantidad",
        description: "Selecciona cuántos registros quieres ver por página.",
        side: "top"
      }
    },
    {
      element: "#folioSelect",
      popover: {
        title: "Filtrar por folios",
        description: "Usa este filtro para mostrar solo las solicitudes de un folio específico.",
        side: "top"
      }
    },
    {
      element: "#allFolios",
      popover: {
        title: "Visualizar todos",
        description: "Haz clic aquí para quitar filtros y mostrar todas las solicitudes.",
        side: "top"
      }
    },
    {
      element: "table thead",
      popover: {
        title: "Encabezados de la tabla",
        description: "Aquí se muestran los campos de cada solicitud: folio, empleado, horarios, motivos y acciones.",
        side: "top"
      }
    },
    {
      element: "#tblTiempoExtra",
      popover: {
        title: "Solicitudes listadas",
        description: "En esta tabla se cargan las solicitudes según los filtros aplicados.",
        side: "top"
      }
    },
    {
      element: "#prevPage",
      popover: {
        title: "Página anterior",
        description: "Haz clic para ver la página anterior de registros.",
        side: "top"
      }
    },
    {
      element: "#nextPage",
      popover: {
        title: "Página siguiente",
        description: "Haz clic para avanzar a la siguiente página de registros.",
        side: "top"
      }
    },
    {
      element: "#btnAyuda",
      popover: {
        title: "Botón de ayuda",
        description: "Presiona este botón para repetir el tutorial de la pantalla.",
        side: "left"
      }
    }
  ];

  // -------------------------------
  // Pasos - Modal
  // -------------------------------
  const stepsModal = [
    {
      element: ".modal-title",
      popover: {
        title: "Editar solicitud",
        description: "Este formulario permite modificar y validar una solicitud de tiempo extra.",
        side: "bottom"
      }
    },
    {
      element: ".alert.alert-danger",
      popover: {
        title: "Aviso importante",
        description: "Modifica la información necesaria y presiona 'Actualizar y Validar Tiempo Extra' en caso de ser APTO, o 'Eliminar registro' en caso de NO SER APTO.",
        side: "top",
        popoverClass: "popover-importante"
      }
    },
    {
      element: "#editFolio",
      popover: {
        title: "Folio",
        description: "Número de folio de la solicitud.",
        side: "top"
      }
    },
    {
      element: "#editNoEmp",
      popover: {
        title: "Número de empleado",
        description: "Identificador del empleado.",
        side: "top"
      }
    },
    {
      element: "#editNombre",
      popover: {
        title: "Nombre",
        description: "Nombre completo del empleado.",
        side: "top"
      }
    },
    {
      element: "#editDepto",
      popover: {
        title: "Departamento",
        description: "Departamento al que pertenece el empleado.",
        side: "top"
      }
    },
    {
      element: "#editPuesto",
      popover: {
        title: "Puesto",
        description: "Puesto del empleado.",
        side: "top"
      }
    },
    {
      element: "#editTurno",
      popover: {
        title: "Turno",
        description: "Selecciona el turno correcto para validar la solicitud.",
        side: "top"
      }
    },
    {
      element: "#horaInicioTurno",
      popover: {
        title: "Hora inicio turno",
        description: "Hora de inicio del turno detectado.",
        side: "top"
      }
    },
    {
      element: "#horaFinTurno",
      popover: {
        title: "Hora fin turno",
        description: "Hora de fin del turno detectado.",
        side: "top"
      }
    },
    {
      element: "#editHoraI",
      popover: {
        title: "Inicio tiempo extra",
        description: "Hora de inicio del tiempo extra.",
        side: "top"
      }
    },
    {
      element: "#editHoraF",
      popover: {
        title: "Fin tiempo extra",
        description: "Hora de fin del tiempo extra.",
        side: "top"
      }
    },
    {
      element: "#tiempoExtraTmp",
      popover: {
        title: "Horas calculadas",
        description: "Horas extra calculadas automáticamente según los lapsos.",
        side: "top"
      }
    },
    {
      element: "#editMotivo",
      popover: {
        title: "Motivo",
        description: "Motivo de la solicitud.",
        side: "top"
      }
    },
    {
      element: "#editRazon",
      popover: {
        title: "Razón",
        description: "Razón detallada de la solicitud.",
        side: "top"
      }
    },
    {
      element: "#btnGuardarYVTE",
      popover: {
        title: "Actualizar y validar",
        description: "Haz clic aquí para actualizar y validar la solicitud como apta.",
        side: "top",
        popoverClass: "popover-importante"
      }
    },
    {
      element: "#btnDelete",
      popover: {
        title: "Eliminar registro",
        description: "Haz clic aquí para eliminar la solicitud si no es apta.",
        side: "top"
      }
    },
    {
      element: "#btnAyudaModal",
      popover: {
        title: "Botón de ayuda",
        description: "Presiona este botón para repetir el tutorial del formulario.",
        side: "left"
      }
    }
  ];

  // -------------------------------
  // Instancia única compartida
  // -------------------------------
  let driverObj = null;

  function launchDriver(pasos) {
    if (driverObj) {
      driverObj.destroy();
    }

    driverObj = Driver({
      showProgress: true,
      allowClose: false,
      disableInteraction: true,
      progressText: "Paso {{current}} de {{total}}",
      doneBtnText: "Finalizar",
      nextBtnText: "Siguiente",
      prevBtnText: "Atrás",
      steps: pasos
    });

    driverObj.drive();
  }

  // -------------------------------
  // Tutorial vista principal
  // -------------------------------
  const tutorialKey = "tutorial_validacionInfoTE";
  if (!localStorage.getItem(tutorialKey)) {
    launchDriver(steps);
    localStorage.setItem(tutorialKey, "true");
  }

  const btnAyuda = document.getElementById("btnAyuda");
  if (btnAyuda) {
    btnAyuda.addEventListener("click", () => launchDriver(steps));
  }

  // -------------------------------
  // Tutorial modal
  // -------------------------------
  const tutorialModalKey = "tutorial_modalEditarTE";
  const modalEditarTE = document.getElementById("modalEditarTE");

  if (modalEditarTE) {
    modalEditarTE.addEventListener("shown.bs.modal", () => {
      if (!localStorage.getItem(tutorialModalKey)) {
        launchDriver(stepsModal);
        localStorage.setItem(tutorialModalKey, "true");
      }
    });
  }

  const btnAyudaModal = document.getElementById("btnAyudaModal");
  if (btnAyudaModal) {
    btnAyudaModal.addEventListener("click", () => launchDriver(stepsModal));
  }
});