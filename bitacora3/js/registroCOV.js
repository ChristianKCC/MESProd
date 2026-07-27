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
let wr = document.querySelector('input[name="wr"]:checked');
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
  input.readOnly = i !== 1; // Solo el primero editable
  container.appendChild(input);
  inputs.push(input);
}

let currentIndex = 0;
btnAgregar.addEventListener("click", () => {
  const value = peso.value;
  if (value === "") return;

  // Buscar el primer input vacío
  const nextInput = inputs.find((input) => input.value === "");

  if (!nextInput) {
    alert("Ya se han ingresado todos los valores.");
    return;
  }

  nextInput.value = value;
  nextInput.classList.remove("border-success-new");
  nextInput.readOnly = true;

  // Activar visualmente el siguiente input vacío (si existe)
  const siguiente = inputs.find((input) => input.value === "");
  if (siguiente) {
    siguiente.readOnly = true;
    siguiente.classList.add("border-success-new");
    peso.focus();
  }

  peso.value = "";

  // Recalcular estadísticas
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

  cov.value = isNaN(coefv) ? "0 %" : `${(coefv * 100).toFixed(2)} %`;
});

// Función para calcular la desviación estándar
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
  wr = document.querySelector('input[name="wr"]:checked');
  RegistroCOVObj.enviarPesos(
    folioPP.value,
    pesos,
    cov.value,
    promedioInput.value,
    minVal.value,
    maxVal.value,
    folio.value,
    wr.value
  );
});

document.getElementById("btnLimpiarCOV").addEventListener("click", (e) => {
  e.preventDefault();
  // Limpiar todos los campos de entrada
  inputs.forEach((input) => {
    input.value = "";
    input.readOnly = true;
    input.classList.remove("border-success");
  });
  cov.value = "";
  promedioInput.value = "";
  minVal.value = "";
  maxVal.value = "";
  folioPP.value = "";
});

document.getElementById("btnPesoPanal").addEventListener("click", (e) => {
  e.preventDefault();
  RegistroCOVObj.mostrarPesos(folio.value).then((resp) => {
    console.log(resp);
    if (resp.length === 0) return false;
    const arrPesos = JSON.parse(resp[0].pesos);
    arrPesos.forEach((peso, index) => {
      const input = document.getElementById(`${index + 1}`);
      if (input) {
        input.value = peso;
      }
    });
    folioPP.value = resp[0].id;
    document.getElementById("cov").value = isNaN(resp[0].cov)
      ? "0"
      : resp[0].cov + " %";
    document.getElementById("promedio").value = resp[0].promedio;
    document.getElementById("min").value = resp[0].min;
    document.getElementById("max").value = resp[0].max;
    document.querySelector(
      `input[name="wr"][value="${resp[0].wr}"]`
    ).checked = true;
  });
});

// Delegación de eventos sobre el contenedor
// Asume que los inputs empiezan con readOnly = true en el HTML/CSS

container.addEventListener("focusin", (e) => {
  if (e.target.tagName !== "INPUT") return;

  const input = e.target;

  // Habilitar edición y aplicar estilo
  input.readOnly = false;
  input.classList.add("border-success-new");

  function finalizarEdicionYIrA(dir = 0) {
    input.readOnly = true;
    input.classList.remove("border-success-new"); // Quitar estilo al salir
    input.removeEventListener("keydown", onKeyDown);
    input.removeEventListener("blur", onBlur);
    recalcularValores?.();

    if (dir !== 0) {
      const inputs = Array.from(container.querySelectorAll("input"));
      const index = inputs.indexOf(input);
      const targetIndex = index + dir;
      if (targetIndex >= 0 && targetIndex < inputs.length) {
        const next = inputs[targetIndex];
        next.readOnly = false;
        next.focus();
        next.classList.add("border-success-new"); // Aplicar estilo al siguiente
      }
    }
  }

  function onKeyDown(ev) {
    if (ev.key === "Enter" || ev.key === "Tab") {
      ev.preventDefault();
      finalizarEdicionYIrA(ev.shiftKey ? -1 : +1);
    }
    if (ev.key === "Escape") {
      ev.preventDefault();
      input.readOnly = true;
      input.blur();
    }
  }

  function onBlur() {
    finalizarEdicionYIrA(0);
  }

  input.addEventListener("keydown", onKeyDown);
  input.addEventListener("blur", onBlur);
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

  cov.value = isNaN(coefv) ? "0 %" : `${(coefv * 100).toFixed(2)} %`;
}
