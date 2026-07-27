import { Maquinas } from "../modules/ProduccM.js";

const MaquinasObj = new Maquinas();
const modal = new bootstrap.Modal(document.getElementById("modalNuevoModulo"));

MaquinasObj.tblModulos("").then(
  (element) => (document.getElementById("tblModulos").innerHTML = element)
);

document.getElementById("buscarModulo").addEventListener("click", (e) => {
  e.preventDefault();
  const busqueda = document.getElementById("moduloBusqueda").value;
  MaquinasObj.tblModulos(busqueda).then(
    (element) => (document.getElementById("tblModulos").innerHTML = element)
  );
});

document.getElementById("updateModulos").addEventListener("click", (e) => {
  e.preventDefault();

  const busqueda = document.getElementById("moduloBusqueda").value;
  const idModulo = document.getElementById("idModuloUpdate").value;
  const nombreModulo = document.getElementById("nombreModuloUpdate").value;

  MaquinasObj.saveModulo(idModulo, nombreModulo).then(() => {
    MaquinasObj.tblModulos(busqueda).then(
      (element) => (document.getElementById("tblModulos").innerHTML = element)
    );
    modal.hide;
  });
});

document.getElementById("nuevaModulo").addEventListener("click", (e) => {
  e.preventDefault();
  modal.show();
});

document.getElementById("saveNuevoModulo").addEventListener("click", (e) => {
  e.preventDefault();
  const busqueda = document.getElementById("moduloBusqueda").value;
  const idNuevoModulo = document.getElementById("idModuloNuevo").value;
  const nameNuevoModulo = document.getElementById("nombreModuloNuevo").value;
  MaquinasObj.saveModulo(idNuevoModulo, nameNuevoModulo).then(() => {
    MaquinasObj.tblModulos(busqueda).then(
      (element) => (document.getElementById("tblModulos").innerHTML = element)
    );
    modal.hide;
  });
});

window.editModulos = (id) => {
  MaquinasObj.editModulo(
    id,
    "idModuloUpdate",
    "noModuloUpdate",
    "nombreModuloUpdate"
  );
};
