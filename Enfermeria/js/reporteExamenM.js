import { Toolsjs } from "../../Tools/Tools.js";
import { ExamenMedico } from "../module/index.js";
const Tools = new Toolsjs();
const ExamenObj = new ExamenMedico();
const idsCamposObligatorios = ["fechai", "fechaf", "departamento", "noemp"];
Tools.llnarslc("CatalogoPersonal", "GetSlcDeps", "departamento", 0);
// document.getElementById("departamento").addEventListener("change", (e) => {
//   e.preventDefault();
//   e.target.value == ""
//     ? (maquina.innerHTML = "")
//     : Tools.llnarslc(
//         "CatalogoPersonal",
//         "GetSlcMaquinasxdep&departamento=" + e.target.value,
//         "maquina",
//         0
//       );
// });
document
  .getElementById("getReporteExamenM")
  .addEventListener("click", async (e) => {
    e.preventDefault();
    if (Tools.validarCamposPorID(idsCamposObligatorios) === false) return;
    const element = await ExamenObj.resporteExamenM(
      fechai.value,
      fechaf.value,
      departamento.value,
      noemp.value
    );

    if (!element || !Array.isArray(element)) {
      swal.fire("Error", "Ocurrió un problema al obtener los datos", "error");
      return;
    }

    if (element.length === 0) {
      swal.fire("Ups!", "No hay datos para mostrar", "warning");
      return;
    }
  });
