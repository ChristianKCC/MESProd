import { Toolsjs } from "../../Tools/Tools.js";
import { RegistroCOV } from "../modules/Bitacora.js";

const Tools = new Toolsjs();
const RegistroCOVObj = new RegistroCOV();

const container = document.getElementById("inputsContainer");
const peso = document.getElementById("peso");
const btnAgregar = document.getElementById("agregarPeso");
const cov = document.getElementById("cov");
const promedioInput = document.getElementById("promedio");
const minVal = document.getElementById("min");
const maxVal = document.getElementById("max");
const folio = document.getElementById("folio");
const folioPP = document.getElementById("folioPP");

// Tools.llnarslc("CatalogoTurnos", "GetSlcTurnos", "turno", 0);

const inputs = [];

// Crear 100 campos de entrada
for (let i = 1; i <= 100; i++) {
  const input = document.createElement("input");
  input.classList.add("form-control-new", "number");
  input.type = "number";
  input.placeholder = `#${i}`;
  input.id = `${i}`;
  input.min = "0";
  input.step = "any";
  // input.disabled = i !== 1; // Solo el primero habilitado
  input.readOnly = i !== 1; // Solo el primero editable
  container.appendChild(input);
  inputs.push(input);
}

let currentIndex = 0;
btnAgregar.addEventListener("click", () => {
  const value = peso.value;
  if (value === "") return;

  inputs[currentIndex].value = value;
  inputs[currentIndex].classList.remove("border-success");
  inputs[currentIndex].readOnly = true;

  currentIndex++;
  if (currentIndex < inputs.length) {
    inputs[currentIndex].readOnly = true;
    inputs[currentIndex].classList.add("border-success");
    peso.focus();
  }

  peso.value = "";

  const inputss = container.querySelectorAll("input");
  let valores = [];
  inputss.forEach((input) => {
    const valor = parseFloat(input.value);
    if (!isNaN(valor)) {
      valores.push(valor);
    }
  });

  const suma = valores.reduce((acc, val) => acc + val, 0);
  const promedio = valores.length > 0 ? suma / valores.length : 0;
  const min = valores.length > 0 ? Math.min(...valores) : 0;
  const max = valores.length > 0 ? Math.max(...valores) : 0;

  promedioInput.value = `${promedio.toFixed(2)}`;
  minVal.value = `${min.toFixed(1)}`;
  maxVal.value = `${max.toFixed(1)}`;

  const desviacion = calcularDesviacionEstandar(valores);
  const coefv = desviacion / promedio;

  cov.value = `${(coefv * 100).toFixed(2)} %`;
});

function calcularDesviacionEstandar(valores) {
  const n = valores.length;
  if (n === 0) return 0;

  const promedio = valores.reduce((a, b) => a + b, 0) / n;

  const sumaCuadrados = valores.reduce((acum, valor) => {
    return acum + Math.pow(valor - promedio, 2);
  }, 0);

  const desviacion = Math.sqrt(sumaCuadrados / (n - 1)); // Muestral
  return desviacion;
}

function obtenerValores() {
  const valores = [];
  for (let i = 1; i <= 100; i++) {
    const input = document.getElementById(`${i}`);
    const valor = parseFloat(input.value);
    if (!isNaN(valor)) {
      valores.push(valor);
    }
  }
  return valores;
}

document.getElementById("btnGuardarPesos").addEventListener("click", (e) => {
  e.preventDefault();
  const pesos = obtenerValores();
  RegistroCOVObj.enviarPesos(
    folioPP.value,
    pesos,
    cov.value,
    promedioInput.value,
    minVal.value,
    maxVal.value,
    folio.value
  );
});

document.getElementById("btnPesoPanal").addEventListener("click", (e) => {
  e.preventDefault();
  RegistroCOVObj.mostrarPesos(folio.value).then((resp) => {
    const arrPesos = JSON.parse(resp[0].pesos);
    arrPesos.forEach((peso, index) => {
      const input = document.getElementById(`${index + 1}`);
      if (input) {
        input.value = peso;
      }
    });
    folioPP.value = resp[0].id;
    document.getElementById("cov").value = resp[0].cov + " %";
    document.getElementById("promedio").value = resp[0].promedio;
    document.getElementById("min").value = resp[0].min;
    document.getElementById("max").value = resp[0].max;
  });
});

container.addEventListener("dblclick", (e) => {
  if (e.target.tagName === "INPUT") {
    e.preventDefault();

    const input = e.target;
    input.readOnly = false;
    input.focus();

    // Cuando presiona Enter
    input.addEventListener("keydown", function onKeyDown(ev) {
      if (ev.key === "Enter") {
        input.readOnly = true;
        input.removeEventListener("keydown", onKeyDown);
        recalcularValores(); // Recalcula al presionar Enter
      }
    });

    // Cuando hace clic fuera
    input.addEventListener("blur", function onBlur() {
      input.readOnly = true;
      input.removeEventListener("blur", onBlur);
      recalcularValores(); // Recalcula al salir del campo
    });
  }
});

function recalcularValores() {
  const valores = obtenerValores();

  const suma = valores.reduce((acc, val) => acc + val, 0);
  const promedio = valores.length > 0 ? suma / valores.length : 0;
  const min = valores.length > 0 ? Math.min(...valores) : 0;
  const max = valores.length > 0 ? Math.max(...valores) : 0;

  promedioInput.value = `${promedio.toFixed(2)}`;
  minVal.value = `${min.toFixed(1)}`;
  maxVal.value = `${max.toFixed(1)}`;

  const desviacion = calcularDesviacionEstandar(valores);
  const coefv = desviacion / promedio;

  cov.value = `${(coefv * 100).toFixed(2)} %`;
}
