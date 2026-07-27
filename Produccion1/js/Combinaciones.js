import { Maquinas } from "../modules/ProduccM.js";
import { Toolsjs } from "../../Tools/Tools.js";

const MaquinasObj = new Maquinas();
const tools = new Toolsjs();

tools.llnarslc("CatalogoPersonal", "GetSlcMaquinas", "maquinaconv", 0);

MaquinasObj.tblCombinaciones("").then((element) => {
  document.getElementById("tblCombinaciones").innerHTML = element;
});

const modal = new bootstrap.Modal(
  document.getElementById("modalCombinaciones")
);

// Buscar una combinación de claves
document.getElementById("buscarCombinacion").addEventListener("click", (e) => {
  e.preventDefault();
  const busqueda = document.getElementById("combinacionBusqueda").value;
  MaquinasObj.tblCombinaciones(busqueda).then((element) => {
    document.getElementById("tblCombinaciones").innerHTML = element;
  });
});

// Abrir modal para nueva combinacion
document.getElementById("nuevaCombinacion").addEventListener("click", (e) => {
  e.preventDefault();
  modal.show();
});

document.getElementById("seccionInput").addEventListener("input", (e) => {
  const autoCompleteSecciones = document.getElementById(
    "autocompleteSecciones"
  );

  MaquinasObj.slcautocomplete(
    e,
    autoCompleteSecciones,
    "seccionConb",
    "php/Maquinas.php?autoSecciones",
    "seccionInput"
  );
});

document.getElementById("moduloInput").addEventListener("input", (e) => {
  const autocompleteModulos = document.getElementById("autocompleteModulos");

  MaquinasObj.slcautocomplete(
    e,
    autocompleteModulos,
    "moduloConb",
    "php/Maquinas.php?autoModulos",
    "moduloInput"
  );
});

document.getElementById("fallaInput").addEventListener("input", (e) => {
  const autocompletefalla = document.getElementById("autocompletefalla");

  MaquinasObj.slcautocomplete(
    e,
    autocompletefalla,
    "fallaConb",
    "php/Maquinas.php?autoFallas",
    "fallaInput"
  );
});

document.getElementById("savecombinacion").addEventListener("click", (e) => {
  e.preventDefault();
  const busqueda = document.getElementById("combinacionBusqueda").value;
  const idconvinacion = document.getElementById("idconvinacion").value;
  const maquinaconv = document.getElementById("maquinaconv").value;
  const seccionConb = document.getElementById("seccionConb").value;
  const moduloConb = document.getElementById("moduloConb").value;
  const fallaConb = document.getElementById("fallaConb").value;
  MaquinasObj.saveCombinacion(
    idconvinacion,
    maquinaconv,
    seccionConb,
    moduloConb,
    fallaConb
  ).then(() => {
    MaquinasObj.tblCombinaciones(busqueda).then((element) => {
      document.getElementById("tblCombinaciones").innerHTML = element;
    });
  });
});

window.editconvinacionesxid = (id) => {
  MaquinasObj.editconvinaciones(
    id,
    "idconvinacion",
    "maquinaconv",
    "seccionConb",
    "moduloConb",
    "fallaConb",
    "seccionInput",
    "moduloInput",
    "fallaInput"
  );
};
