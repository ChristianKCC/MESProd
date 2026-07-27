import { ReporteIncidencias } from "../module/Incidencias.js";
import { Toolsjs } from "../../Tools/Tools.js";

const ReporteIncidenciasObj = new ReporteIncidencias();
const Tools = new Toolsjs();

Tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "departamento", 0);

document.getElementById("buscar").addEventListener("click", (e) => {
  e.preventDefault();
  const fechaInicial = document.getElementById("fechai").value;
  const fechaFinal = document.getElementById("fechaf").value;
  const departamento = document.getElementById("departamento").value;

  if (fechaInicial === "" || fechaFinal === "") {
    swal.fire("Ups!", "El rango de fecha es obligatorio", "warning");
    return false;
  }
  ReporteIncidenciasObj.tblReporteIncidencias(
    fechaInicial,
    fechaFinal,
    departamento
  );
});

window.generarReporte = (id) => {
  ReporteIncidenciasObj.generarReporte(id);
};

document.getElementById("limpiar").addEventListener("click", (e) => {
  e.preventDefault();
  console.log("Se hizo click");
  document.getElementById("fechai").value = "";
  document.getElementById("fechaf").value = "";
  document.getElementById("departamento").value = "";
  document.getElementById("tblIncidencias").innerHTML = "";
});
