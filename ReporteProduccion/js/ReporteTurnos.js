import { Toolsjs } from "../../Tools/Tools.js";
import { ReporteProduccionTurnos } from "../modules/dataTurnosAnteriores.js";

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
    ["67", "68", "69", "70", "71", "72", "75", "85", "86", "101", "138", "139"].includes(
      maquinaValue,
    )
  ) {
    html = await ReporteTurnosObj.generarTablaMaquinasSinRed(
      fechaValue,
      maquinaValue,
      turnoValue,
    );
  } else {
    html = await ReporteTurnosObj.generarTabla(
      fechaValue,
      maquinaValue,
      turnoValue,
    );
  }

  //   document.getElementById("tblTurnosAnteriores").innerHTML = html;
});

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

  folioenc = recipient;
  ReporteTurnosObj.dataForActualizarRegistro(recipient).then((data) => {
    modalTitle.textContent = `Estas editando la máquina ${data.NombreMaquina} del turno ${data.fTurnoA} de la fecha ${data.fAñoA}/${data.MesA}/${data.DiaA} y el folio #${recipient}`;
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

const modalMaquinasSinRed = document.getElementById("modalEditRegistroTurnoSinRed");
modalMaquinasSinRed.addEventListener("shown.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = modalMaquinasSinRed.querySelector(".modal-title");
  folioenc = recipient;
  ReporteTurnosObj.dataForActualizarRegistroSinRed(recipient).then((data) => {
    console.log(data);
    modalTitle.textContent = `Estas editando la máquina ${data[0].NombreMaquina} del turno ${data[0].Turno} de la fecha ${data[0].Fecha} y el folio #${recipient}`;
    document.getElementById("folioBitacoraSR").value = data[0].IdEncabezadoBItacora;
    document.getElementById("cortesSinRed").value = data[0].Cortes;
    document.getElementById("rechazosSinRed").value = data[0].Rechazos ? data[0].Rechazos : 0;
    document.getElementById("tiempoabajoSinRed").value = data[0].TiempoPerdido;
    document.getElementById("tiempoarribaSinRed").value = data[0].TiempoArriba;
    document.getElementById("parosSinRed").value = data[0].ParosMaquina;
    document.getElementById("horastrabajadasSinRed").value = data[0].HorasTrabajadas;
     // manejar null/undefined/valor vacío
     const motivo = String(data[0].MotivoCambio ?? "").trim();
     document.getElementById("motivoCambioSinRed").value = motivo;
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
          document.getElementById("motivoCambio").value,
        );

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modadalEditRegistroTurno"),
        );
        modal.hide();
        // Recargar la tabla después de cerrar el modal
        btn.click();
      }
    });
  });

  document
  .getElementById("actualizarRegistroTurnoMaquinaSinRed")
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
        ReporteTurnosObj.actualizarRegistroTurnoMaquinaSinRed(
          folioenc,
          document.getElementById("folioBitacoraSR").value,
          document.getElementById("cortesSinRed").value,
          document.getElementById("rechazosSinRed").value,
          document.getElementById("tiempoabajoSinRed").value,
          document.getElementById("tiempoarribaSinRed").value,
          document.getElementById("parosSinRed").value,
          document.getElementById("horastrabajadasSinRed").value,
          document.getElementById("motivoCambioSinRed").value,
        );

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modalEditRegistroTurnoSinRed"),
        );
        modal.hide();
        // Recargar la tabla después de cerrar el modal
        btn.click();
      }
    });
  });

