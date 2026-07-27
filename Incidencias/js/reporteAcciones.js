import { AccionesCorrectivas } from "../module/Incidencias.js";
import { Toolsjs } from "../../Tools/Tools.js";

const AccionesObj = new AccionesCorrectivas();
const Tools = new Toolsjs();

Tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "departamento", 0);

// AccionesObj.tblAccionesCorrectivas("tblAcciones");

document.getElementById("buscar").addEventListener("click", (e) => {
  e.preventDefault();
  const fechaInicial = document.getElementById("fechai").value;
  const fechaFinal = document.getElementById("fechaf").value;
  const departamento = document.getElementById("departamento").value;

  if (fechaInicial === "" || fechaFinal === "") {
    swal.fire("Ups!", "El rango de fecha es obligatorio", "warning");
    return false;
  }
  AccionesObj.tblAccionesCorrectivas(
    fechaInicial,
    fechaFinal,
    departamento,
    "tblAcciones"
  );
});

document.getElementById("limpiar").addEventListener("click", (e) => {
  e.preventDefault();
  console.log("Se hizo click");
  document.getElementById("fechai").value = "";
  document.getElementById("fechaf").value = "";
  document.getElementById("departamento").value = "";
  document.getElementById("tblAcciones").innerHTML = "";
});
