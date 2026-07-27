import { Toolsjs } from "../../Tools/Tools.js";
import { ReporteProduccionTurnos } from "../modules/prodmodules.js";
const ToolObj = new Toolsjs();
const ReporteTurnosObj = new ReporteProduccionTurnos();

const btn = document.getElementById("generarTabla");
const maquina = document.getElementById("maquinas");
const turno = document.getElementById("turnos");
const fecha = document.getElementById("fecha");

ReporteTurnosObj.llnarslcMaquinas("maquinas");

// ToolObj.llnarslcMaquinas("CatalogoMaquinas", "GetSlcMaquinas", "maquinas", 0);
ToolObj.llnarslc("CatalogoTurnos", "GetSlcTurnos", "turnos", 0);

btn.addEventListener("click", async (e) => {
  e.preventDefault();
  const fechaValue = fecha.value;
  const maquinaValue = maquina.value;
  const turnoValue = turno.value;

  let html = "";
  if (
    ["67", "68", "69", "70", "71", "72", "75", "85", "86"].includes(
      maquinaValue
    )
  ) {
    html = await ReporteTurnosObj.generarTablaMaquinasSinRed(
      fechaValue,
      maquinaValue,
      turnoValue
    );
  } else {
    html = await ReporteTurnosObj.generarTabla(
      fechaValue,
      maquinaValue,
      turnoValue
    );
  }

  document.getElementById("tblTurnosAnteriores").innerHTML = html;
});

// document.getElementById("generarTabla").addEventListener("click", (e) => {
//   e.preventDefault();
//   const fecha = document.getElementById("fecha").value;
//   const maquina = document.getElementById("maquinas").value;
//   const turno = document.getElementById("turnos").value;
//   ReporteTurnosObj.generarTabla(fecha, maquina, turno).then((element) => {
//     document.getElementById("tblTurnosAnteriores").innerHTML = element;
//   });
// });

window.reporteMaquinaAnterior = (id) => {
  ReporteTurnosObj.generarPDFFolio(id);
};
window.reporteMaquinasSinRed = (fecha, maquina, turno) => {
  ReporteTurnosObj.generarPDF(fecha, maquina, turno);
};

let folioenc = "";
const modalTurnos = document.getElementById("modadalEditRegistroTurno");
modalTurnos.addEventListener("shown.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = modalTurnos.querySelector(".modal-title");
  modalTitle.textContent = `Editar registro folio #${recipient}`;
  folioenc = recipient;
  ReporteTurnosObj.dataForActualizarRegistro(recipient).then((data) => {
    document.getElementById("folioBitacora").value = data.IdEncabezadoBItacora;
    document.getElementById("cortes").value = data.CortesA;
    document.getElementById("rechazos").value = data.RechazosA;
    document.getElementById("tiempoabajo").value = data.TAbajoA;
    document.getElementById("minutosenhebrando").value = data.fMinEnhebrandoA;
    document.getElementById("tiempoarriba").value = data.TArribaA;
    document.getElementById("tiempoperdido").value = data.fTiempoPerdidoA;
    document.getElementById("paros").value = data.fParoMaqinaA;
    document.getElementById("horastrabajadas").value = data.HorasTrabajadas;
    // manejar null/undefined/valor vacío
    const motivo = String(data.motivo ?? "").trim();
    document.getElementById("motivoCambio").value = motivo;
  });
});

document
  .getElementById("actualizarRegistroTurnoMaquina")
  .addEventListener("click", (e) => {
    e.preventDefault();

    Swal.fire({
      title: "¿Deseas actualizar este registro?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, actualizar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        ReporteTurnosObj.actualizarRegistroTurnoMaquina(
          folioenc,
          document.getElementById("folioBitacora").value,
          document.getElementById("cortes").value,
          document.getElementById("rechazos").value,
          document.getElementById("tiempoabajo").value,
          document.getElementById("minutosenhebrando").value,
          document.getElementById("tiempoarriba").value,
          document.getElementById("tiempoperdido").value,
          document.getElementById("paros").value,
          document.getElementById("horastrabajadas").value,
          document.getElementById("motivoCambio").value
        );

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modadalEditRegistroTurno")
        );
        modal.hide();
      }
    });
  });
