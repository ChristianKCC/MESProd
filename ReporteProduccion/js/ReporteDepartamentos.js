import { llnarslcDepartamentos } from "../modules/prodmodules.js";
import { ReporteProduccionDepartamentos } from "../modules/prodmodules.js";
import { ReporteProduccionDireccion } from "../modules/prodmodules.js";

const ReporteDepartamentosObj = new ReporteProduccionDepartamentos();
const ReporteDireccionObj = new ReporteProduccionDireccion();

llnarslcDepartamentos(".slc-departamentos");

const fechaInicial = document.getElementById("fechaInicio");
const fechaFinal = document.getElementById("fechaFin");
const departamento = document.getElementsByClassName(".slc-departamentos");
const fechaDireccion = document.getElementById("fechaDireccion");
const fechaGerencia = document.getElementById("fechaGerencia");

// Lógica para generar el PDF del reporte de departamentos
// btnGenerarReportePDF.addEventListener("click", (e) => {
//   e.preventDefault();
//   const select = contenedor.querySelector(".slc-departamentos");
//   const departamento = select.value;
//   ReporteDepartamentosObj.generarPDFReporteDepartamentos(
//     fechaInicial.value,
//     fechaFinal.value,
//     departamento.value,
//   );
// });



document.querySelectorAll(".btn-generar").forEach(btn => {
  btn.addEventListener("click", (e) => {
    e.preventDefault();

    const contenedor = btn.closest(".row");
    const select = contenedor.querySelector(".slc-departamentos");
    const departamento = select.value || "0"; // Valor por defecto si no se selecciona ningún departamento
    const tipoReporte = btn.dataset.reporte;

    if (tipoReporte === "direccion") {
      ReporteDireccionObj.generarPDFDireccion(
        fechaDireccion.value
      );
    }

    if (tipoReporte === "departamentos") {
      ReporteDepartamentosObj.generarPDFReporteDepartamentos(
        fechaInicial.value,
        fechaFinal.value,
        departamento
      );
    }

    if (tipoReporte === "gerencia") {
      ReporteDireccionObj.generarPDFReporteGerencia(
        fechaGerencia.value
      );
    }
  });
});
