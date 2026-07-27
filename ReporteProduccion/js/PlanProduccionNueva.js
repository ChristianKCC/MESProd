import { PlanProduccion } from "../modules/prodmodules.js";
import { Toolsjs } from "../../Tools/Tools.js";

const ToolObj = new Toolsjs();
const PlanProduccionObj = new PlanProduccion();

const maquina = document.getElementById("maquina");
const clave = document.getElementById("claveProducc");

PlanProduccionObj.tblPlanProduccion("tblProgramaMaquinaNueva");

// LLenar Seleccionador de maquinas
ToolObj.llnarslc("CatalogoPersonal", "GetSlcMaquinas", "maquina", 0);

document.getElementById("btnFiltrar").addEventListener("click", (e) => {
  e.preventDefault();
  const maquinaFiltro = maquina.value;
  const claveFiltro = clave.value;

  PlanProduccionObj.tblPlanProduccionFiltro(
    maquinaFiltro,
    claveFiltro,
    "tblProgramaMaquinaNueva"
  );
});

document.getElementById("reset").addEventListener("click", (e) => {
  e.preventDefault();
  PlanProduccionObj.tblPlanProduccion("tblProgramaMaquinaNueva");
  maquina.value = "";
  clave.value = "";
});

document.getElementById("btnPlanProducc").addEventListener("click", (e) => {
  e.preventDefault();
  const idMaquina = document.getElementById("maquina").value;
  PlanProduccionObj.visualizarPlanProducc(idMaquina, "listaPlanProduccion");
});
