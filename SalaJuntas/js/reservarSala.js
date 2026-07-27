import { Toolsjs } from "../../Tools/Tools.js";
import { SalaJuntas } from "../module/index.js";

const Tools = new Toolsjs();
const SalaJuntasObj = new SalaJuntas();

const numeroSala = document.getElementById("salaJuntas");
const noemp = document.getElementById("noemp");
const nombre = document.getElementById("nombre");
const departamento = document.getElementById("departamento");
const puesto = document.getElementById("puesto");
const fecha = document.getElementById("fechaReservacion");
const horaInicio = document.getElementById("horaInicio");
const horaFin = document.getElementById("horaFin");
const titulo = document.getElementById("titulo");
const descripcion = document.getElementById("descripcion");
const capacitacion = document.getElementById("capacitacion");
const checkActivo = document.getElementById("checkActivoVal");
const estado = 1;

const btnReservar = document.getElementById("reservarSala");
const btnLimpiarFormulario = document.getElementById("limpiarFormulario");

const idsCamposObligatorios = [
  "salaJuntas",
  "noemp",
  "fechaReservacion",
  "horaInicio",
  "horaFin",
  "titulo",
  "descripcion",
];

// Tools.llnarslc("CatalogoSalasJuntas", "GetNombreSalasJuntas", "salaJuntas", 0);
SalaJuntasObj.tblConsultaInfoSala("salaJuntas", 0);
SalaJuntasObj.mostrarTabla("tblReservaciones");

Tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "departamento", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcPuestos", "puesto", 0);

document.getElementById("noemp").addEventListener("keyup", (e) => {
  e.preventDefault();

  e.target.value != "" &&
    SalaJuntasObj.datauserEmpleado(e.target.value).then((element) => {
      if (element.length > 0) {
        nombre.value = element[0].nombre;
        departamento.value = element[0].departamento;
        puesto.value = element[0].puesto;
      } else {
        nombre.value = "";
        departamento.value = "";
        puesto.value = "";
      }
    });
});

// Evento para reservar la sala de Juntas
btnReservar.addEventListener("click", (e) => {
  e.preventDefault();

  checkActivo.value = capacitacion.checked ? 1 : 0;

  Tools.validarCamposPorID(idsCamposObligatorios) != false &&
    SalaJuntasObj.saveReservacionSala(
      numeroSala.value,
      noemp.value,
      fecha.value,
      horaInicio.value,
      horaFin.value,
      titulo.value,
      descripcion.value,
      checkActivo.value,
      estado
    ).then(() => {
      numeroSala.value = "";
      noemp.value = "";
      nombre.value = "";
      departamento.value = "";
      puesto.value = "";
      fecha.value = "";
      horaInicio.value = "";
      horaFin.value = "";
      titulo.value = "";
      descripcion.value = "";
      capacitacion.checked = false;
      SalaJuntasObj.mostrarTabla("tblReservaciones");
    });
});

// Evneto para limpiar el formulario
btnLimpiarFormulario.addEventListener("click", (e) => {
  e.preventDefault();
  numeroSala.value = "";
  noemp.value = "";
  fecha.value = "";
  horaInicio.value = "";
  horaFin.value = "";
  titulo.value = "";
  descripcion.value = "";
  capacitacion.checked = false;
});
