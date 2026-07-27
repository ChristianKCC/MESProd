import { Toolsjs } from "../../Tools/Tools.js";
import { ConfClaves } from "../module/ValesM.js";
const tools = new Toolsjs();
const confClave = new ConfClaves();
const modal = new bootstrap.Modal(document.getElementById("modalmaterial"));
confClave
  .tblmateriales("")
  .then(
    (element) => (document.getElementById("tblmateriales").innerHTML = element),
  );
document.getElementById("buscarmaterial").addEventListener("click", (e) => {
  e.preventDefault();
  const busqueda = document.getElementById("materialbusqueda").value;
  confClave
    .tblmateriales(busqueda)
    .then(
      (element) =>
        (document.getElementById("tblmateriales").innerHTML = element),
    );
});
document
  .getElementById("savechgmateriales")
  .addEventListener("click", function (e) {
    e.preventDefault();
    const busqueda = document.getElementById("materialbusqueda").value;
    const idclase = document.getElementById("idmaterial").value;
    const nomaterial = document.getElementById("nomaterial").value;
    const nombrematerial = document.getElementById("nombrematerial").value;
    const ummaterial = document.getElementById("ummaterial").value;
    const ummat = document.getElementById("ummat").value;
    const montacargas = document.getElementById("montacargas").value;
    const tiempo = document.getElementById("tiempo").value;
    confClave
      .saveMaterial(
        idclase,
        nomaterial,
        nombrematerial,
        ummaterial,
        ummat,
        montacargas,
        "1",
        tiempo,
      )
      .then(() => {
        confClave
          .tblmateriales(busqueda)
          .then(
            (element) =>
              (document.getElementById("tblmateriales").innerHTML = element),
          );
        modal.hide();
      });
  });

document.getElementById("nuevomaterial").addEventListener("click", (e) => {
  e.preventDefault();
  modal.show();
  document.getElementById("idmaterial").value = "";
  // document.getElementById('nomaterial').readOnly = false;
  document.getElementById("nomaterial").value = "";
  document.getElementById("nombrematerial").value = "";
  document.getElementById("ummaterial").value = "";
  document.getElementById("ummat").value = "";
  document.getElementById("montacargas").value = "";
  document.getElementById("tiempo").value = "";
});

window.editarmaterialbtn = (param) => {
  confClave.editarMaterial(
    param,
    "idmaterial",
    "nomaterial",
    "nombrematerial",
    "ummaterial",
    "ummat",
    "montacargas",
    "tiempo",
  );
};

window.borrarMaterialbtn = (param) => {
  Swal.fire({
    title: "¿Está seguro?",
    text: "Este material se eliminará permanentemente.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      confClave.borrarMaterial(param).then(() => {
        const busqueda = document.getElementById("materialbusqueda").value;
        confClave
          .tblmateriales(busqueda)
          .then(
            (element) =>
              (document.getElementById("tblmateriales").innerHTML = element),
          );
      });
    }
  });
};
