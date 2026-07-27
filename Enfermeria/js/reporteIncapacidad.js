import { Toolsjs } from "../../Tools/Tools.js";
import { Incapacidades } from "../module/index.js";
const Tools = new Toolsjs();
const IncapacidadesObj = new Incapacidades();
const fechai = document.getElementById("fechai");
const fechaf = document.getElementById("fechaf");
const tipoaparato = document.getElementById("tipoaparato");
const tipoenfermedad = document.getElementById("tipoenfermedad");
const departamento = document.getElementById("departamento");
const maquina = document.getElementById("maquina");
const noemp = document.getElementById("noemp");
let chart = null;
let graficaDepartamentos = null;
let groupedData = {};
let equipos = [];
let conteos = [];
const idsCamposObligatorios = ["fechai", "fechaf"];
Tools.llnarslc("CatalogoPersonal", "GetSlcDeps", "departamento", 0);
document.getElementById("departamento").addEventListener("change", (e) => {
  e.preventDefault();
  e.target.value == ""
    ? (maquina.innerHTML = "")
    : Tools.llnarslc(
        "CatalogoPersonal",
        "GetSlcMaquinasxdep&departamento=" + e.target.value,
        "maquina",
        0
      );
});




function getRandomColor() {
  const r = Math.floor(Math.random() * 255);
  const g = Math.floor(Math.random() * 255);
  const b = Math.floor(Math.random() * 255);
  return `rgba(${r}, ${g}, ${b}, 0.6)`;
}
function generarGrafica(data) {
  const conteo = {};

  data.forEach((item) => {
    const depto = item.departamento;
    conteo[depto] = (conteo[depto] || 0) + 1;
  });

  const labels = Object.keys(conteo);
  const values = Object.values(conteo);

  const ctx = document.getElementById("graficaDepartamentos").getContext("2d");

  if (graficaDepartamentos) {
    graficaDepartamentos.data.labels = labels;
    graficaDepartamentos.data.datasets[0].data = values;
    graficaDepartamentos.update();
  } else {
    graficaDepartamentos = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Cantidad por departamento",
            data: values,
            backgroundColor: [
              "#FF6384",
              "#36A2EB",
              "#FFCE56",
              "#4BC0C0",
              "#9966FF",
              "#FF9F40",
            ],
            borderColor: "#fff",
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: "Registros por Departamento",
          },
          legend: {
            position: "bottom",
          },
        },
      },
    });
  }
}

document.getElementById("getReporteIncapacidad").addEventListener("click", async (e) => {
  e.preventDefault();

  if (Tools.validarCamposPorID(idsCamposObligatorios) === false) return;

  const element = await IncapacidadesObj.ReporteIncapacidad(
    fechai.value,
    fechaf.value,
    departamento.value,
    maquina.value,
    noemp.value
  );

  if (element.length === 0) {
    swal.fire("Ups!", "No hay datos para mostrar", "warning");
    return;
  }
  IncapacidadesObj.tblIncapacidad("tblreporteIncapacidades", element);
});

function mostrarEnfermedades(equipo) {
  const enfermedades = Object.keys(groupedData[equipo]);
  const conteos = Object.values(groupedData[equipo]);

  chart.data.labels = enfermedades;
  chart.data.datasets[0].label = `Enfermedades en ${equipo}`;
  chart.data.datasets[0].data = conteos;
  chart.data.datasets[0].backgroundColor = "rgba(255, 99, 132, 0.6)";
  chart.options.plugins.title.text = `Enfermedades detectadas en ${equipo}`;
  chart.update();
}

// document.getElementById("mostrarEquipos").addEventListener("click", () => {
//   chart.data.labels = equipos;
//   chart.data.datasets[0].label = "Consultas por equipo médico";
//   chart.data.datasets[0].data = conteos;
//   chart.data.datasets[0].backgroundColor = "rgba(54, 162, 235, 0.6)";
//   chart.options.plugins.title.text = "Consultas por equipo médico";
//   chart.update();
// });
