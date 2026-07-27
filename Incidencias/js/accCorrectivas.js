import { AccionesCorrectivas } from "../module/index.js";

const AccionesObj = new AccionesCorrectivas();
const folio = document.getElementById("folioetapa4");
const comentarios = document.getElementById("comentarios");
const fecha = document.getElementById("fechaRevision");
const archivo = document.getElementById("archivo");
const checkRegAcc = document.getElementById("checkRegistrarAccion");
const btnRegistrarAccion = document.getElementById("btnRegistrar");

AccionesObj.consultaAcciones("tblAcciones");

window.registrarAccion = (id) => {
  folio.value = id;
};

btnRegistrarAccion.addEventListener("click", (e) => {
  e.preventDefault();

  AccionesObj.guardarRegistroAcciones(
    folio.value,
    comentarios.value,
    fecha.value,
    checkRegAcc.checked ? 2 : 3,
    archivo
  ).then(() => {
    comentarios.value = "";
    fecha.value = "";
    checkRegAcc.checked = false;
  });
});
