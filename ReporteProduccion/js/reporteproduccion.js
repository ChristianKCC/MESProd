import { Toolsjs } from "../../Tools/Tools.js";
import { ReporteProduccion } from "../modules/prodmodules.js";

const ToolsObj = new Toolsjs();
const ReporteP = new ReporteProduccion();
const fechai = document.getElementById("fechai");
const fechaf = document.getElementById("fechaf");
const departamento = document.getElementById("departamento");
const maquina = document.getElementById("maquinas");
const turno = document.getElementById("turno");
const tblReporteProdEnc = document.getElementById("tblReporteProdEnc");
const tblParosManual = document.getElementById("tblParosManual");
ToolsObj.llnarslc("CatalogoPersonal", "GetSlcDeps", "departamento", 0);

ToolsObj.llnarslc("CatalogosBitacora", "GetTurnos", "turno", 0);
document.getElementById("departamento").addEventListener("change", (e) => {
  const departamento = e.target.value;
  departamento === ""
    ? (document.getElementById("maquinas").innerHTML = "")
    : ToolsObj.llnarslc(
        "CatalogoPersonal",
        "GetSlcMaquinasxdep&departamento=" + departamento,
        "maquinas",
        0
      );
});
document
  .getElementById("reporteProduccion")
  .addEventListener("click", async (e) => {
    e.preventDefault();
    const data = await ReporteP.getdataReporteProduccion(
      fechai.value,
      fechaf.value,
      departamento.value,
      maquina.value
    );

    // const dataTurnos = await ReporteP.getdataReporteProduccionTurnos(
    //   fechai.value,
    //   fechaf.value,
    //   departamento.value,
    //   maquina.value
    // );

    const dataparos = await ReporteP.getDataParos(
      fechai.value,
      fechaf.value,
      departamento.value,
      maquina.value
    );

    ReporteP.tbldataReporte(data, tblReporteProdEnc);
    // ReporteP.tbldataReporteTurnos(
    //   dataTurnos,
    //   tablaDetallesProduccion,
    //   turno.value
    // );

    ReporteP.tbldataParosM(
      dataparos,
      tblParosManual,
      fechai.value,
      fechaf.value
    );
  });
window.verDetalles = (seccion, modulo, fechai, fechaf, row) =>
  ReporteP.verDetalles(seccion, modulo, fechai, fechaf, row);
