import { Toolsjs } from "../../Tools/Tools.js";
import { IMC } from "../modules/IMC.js";

let imc = new IMC();
const tools = new Toolsjs();
imc.tblIMCEnc();
const folioimc = document.getElementById("folioimc");
const fecha = document.getElementById("fecha");
const emisor = document.getElementById("noempemisor");
const departamento = document.getElementById("departamento");
const area = document.getElementById("area");
const detriesgo = document.getElementById("detriesgo");
const tiporiesgo = document.getElementById("tiporiesgo");
const tipo = document.getElementById("tipo");
const descripcion = document.getElementById("descripcion");
const responsable = document.getElementById("responsable");
const sugerencias = document.getElementById("sugerencias");
const fechacompromiso = document.getElementById("fechacompromiso");
const estado = document.getElementById("estado");

tools.llnarslc("CatalogoPersonal", "GetSlcDeps", "departamento", 0);
tools.llnarslc("CatalogoSeguridad", "GetSlcDeteccionRiesto", "detriesgo", 0);
tools.llnarslc("CatalogoSeguridad", "GetSlcTipoRiesgo", "tiporiesgo", 0);
tools.llnarslc("CatalogoSeguridad", "GetSlcTipo", "tipo", 0);
// tools.llnarslc("CatalogoSeguridad", "GetSlcIMCEstado", "estado", 0);
(async () => {
  const respuestaraw = await fetch("./php/imc.php?getDataCondiciones");
  const respuesta = await respuestaraw.json();
  let body = "";
  respuesta.forEach((element) => {
    body += ` 
    <div class="form-check">
    <input class="form-check-input" type="radio" name="ListCondicionesIns" id="flexRadioDefault${
      element.id
    }" value="${element.id}" ${element.id == 1 && "checked"}>
    <label class="form-check-label" for="flexRadioDefault${element.id}">
        ${element.nombre}
    </label>
     </div>`;
  });
  document.getElementById("divcondiciones").innerHTML = body;
})();
document.getElementById("departamento").addEventListener("change", () => {
  const dep = document.getElementById("departamento").value;
  tools.llnarslc("CatalogoSeguridad", "GetSlcAreas&dep=" + dep, "area", 0);
});
document.getElementById("noempemisor").addEventListener("keyup", function (e) {
  e.preventDefault();
  const noemp = document.getElementById("noempemisor").value;
  noemp != "" && imc.getinfoemp(noemp, "nombreemisor", "depemisor", "");
});
document.getElementById("responsable").addEventListener("keyup", function (e) {
  e.preventDefault();
  const noemp = document.getElementById("responsable").value;
  noemp != "" && imc.getinfoemp(noemp, "responsablenombre", "", "");
});
document.getElementById("guardarimc").addEventListener("click", function (e) {
  e.preventDefault();
  const condicion = document.querySelector(
    'input[name="ListCondicionesIns"]:checked'
  ).value;
  imc = new IMC(
    fecha.value,
    emisor.value,
    departamento.value,
    area.value,
    detriesgo.value,
    tiporiesgo.value,
    tipo.value,
    descripcion.value,
    responsable.value,
    sugerencias.value,
    fechacompromiso.value,
    estado.value,
    condicion
  );
  const validacion = imc.validacion()
  if(validacion===false)
  {
    swal.fire('Ups!!!','No puede haber campos vacíos','warning');
    return false;
  }
  imc.saveIMC().then((e) => imc.tblIMCEnc());
});
