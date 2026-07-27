import { Maquinas } from "../modules/ProduccM.js";

const MaquinasObj = new Maquinas();
const modal = new bootstrap.Modal(document.getElementById("modalNuevaFalla"));

MaquinasObj.tblFallas("").then(
  (element) => (document.getElementById("tblFallas").innerHTML = element)
);

document.getElementById("buscarFalla").addEventListener("click", (e) => {
  e.preventDefault();
  const busqueda = document.getElementById("fallaBusqueda").value;
  MaquinasObj.tblFallas(busqueda).then(
    (element) => (document.getElementById("tblFallas").innerHTML = element)
  );
});

document.getElementById("updateFallas").addEventListener("click", (e) => {
  e.preventDefault();

  const busqueda = document.getElementById("fallaBusqueda").value;
  const idFalla = document.getElementById("idModuloFallas").value;
  const nombreFalla = document.getElementById("nombreFallaUpdate").value;

  MaquinasObj.saveFalla(idFalla, nombreFalla).then(() => {
    MaquinasObj.tblFallas(busqueda).then(
      (element) => (document.getElementById("tblFallas").innerHTML = element)
    );
    modal.hide;
  });
});

document.getElementById("nuevaFalla").addEventListener("click", (e) => {
  e.preventDefault();
  modal.show();
});

document.getElementById("saveNuevaFalla").addEventListener("click", (e) => {
  e.preventDefault();
  const busqueda = document.getElementById("fallaBusqueda").value;
  const idFallaNueva = document.getElementById("idFallaNueva").value;
  const nombreFallaNueva = document.getElementById("nombreFallaNueva").value;
  MaquinasObj.saveFalla(idFallaNueva, nombreFallaNueva).then(() => {
    MaquinasObj.tblFallas(busqueda).then(
      (element) => (document.getElementById("tblFallas").innerHTML = element)
    );
    modal.hide;
  });
});

window.editFallas = (id) => {
  MaquinasObj.editFalla(
    id,
    "idModuloFallas",
    "noModuloFallas",
    "nombreFallaUpdate"
  );
};
