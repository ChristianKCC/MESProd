import { Maquinas } from "../modules/ProduccM.js";

const MaquinasObj = new Maquinas();
const modal = new bootstrap.Modal(document.getElementById("modalNuevaSeccion"));

MaquinasObj.tblSecciones("").then(
  (element) => (document.getElementById("tblSecciones").innerHTML = element)
);

document.getElementById("buscarSeccion").addEventListener("click", (e) => {
  e.preventDefault();
  const busqueda = document.getElementById("seccionBusqueda").value;
  MaquinasObj.tblSecciones(busqueda).then(
    (element) => (document.getElementById("tblSecciones").innerHTML = element)
  );
});

document.getElementById("updateSecciones").addEventListener("click", (e) => {
  e.preventDefault();

  const busqueda = document.getElementById("seccionBusqueda").value;
  const idSeccion = document.getElementById("idSeccionUpdate").value;
  const nombreSeccion = document.getElementById("nombreSeccionUpdate").value;

  MaquinasObj.saveSeccion(idSeccion, nombreSeccion).then(() => {
    MaquinasObj.tblSecciones(busqueda).then(
      (element) => (document.getElementById("tblSecciones").innerHTML = element)
    );
    modal.hide;
  });
});

document.getElementById("nuevaSeccion").addEventListener("click", (e) => {
  e.preventDefault();
  modal.show();
});

document.getElementById("saveNuevaSeccion").addEventListener("click", (e) => {
  e.preventDefault();
  const busqueda = document.getElementById("seccionBusqueda").value;
  const idSeccionNueva = document.getElementById("idSeccionNueva").value;
  const nameNuevaSeccion = document.getElementById("nombreSeccionNueva").value;
  MaquinasObj.saveSeccion(idSeccionNueva, nameNuevaSeccion).then(() => {
    MaquinasObj.tblSecciones(busqueda).then(
      (element) => (document.getElementById("tblSecciones").innerHTML = element)
    );
    modal.hide;
  });
});

window.editSecciones = (id) => {
  MaquinasObj.editSeccion(
    id,
    "idSeccionUpdate",
    "noSeccionUpdate",
    "nombreSeccionUpdate"
  );
};
