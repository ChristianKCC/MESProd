import { Toolsjs } from "../../Tools/Tools.js";
import { Preusos } from "../module/preusos.js";
const Tools = new Toolsjs();
const PreusosObj = new Preusos();
const tblpreusos = document.getElementById("tblReportePreusos");
const buscarPreusos = document.getElementById("buscarPreusos");
const fechai = document.getElementById("fechai");
const fechaf = document.getElementById("fechaf");
const departamento = document.getElementById("departamento");
const maquina = document.getElementById("maquina");
Tools.llnarslc("CatalogoPersonal", "GetSlcDeps", "departamento", 0);
document.getElementById("departamento").addEventListener("click", (e) => {
  e.preventDefault();
  Tools.llnarslc("CatalogoPersonal", "GetSlcMaquinasxdep&departamento="+e.target.value, "maquina", 0);
});
let data = [];

buscarPreusos.addEventListener("click", async (e) => {
  e.preventDefault();
  const data = await PreusosObj.reportePreusos(fechai.value, fechaf.value,departamento.value,maquina.value);
  window.datosAgrupados = PreusosObj.agruparPorId(data);
  PreusosObj.tblPreusos(data, tblpreusos);
});

window.verDetalles = (elment) => PreusosObj.verDetalles(elment);
