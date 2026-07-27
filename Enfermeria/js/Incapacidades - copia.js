import { Toolsjs } from "../../Tools/Tools.js";
import { Incapacidades, Consultas } from "../module/index.js";
// import { Firma } from "../../Modules/Autenticacion.js";
const Tools = new Toolsjs();
const IncapacidadesObj = new Incapacidades();
const ConsultasObj = new Consultas();
const hoy = new Date().toISOString().split("T")[0];
const id = document.getElementById("id");
const noemp = document.getElementById("noemp");
const nombre = document.getElementById("nombre");
const departamento = document.getElementById("departamento");
const puesto = document.getElementById("puesto");
const responsable = document.getElementById("responsable");
const nombreresponsable = document.getElementById("nombreresponsable");
const folio = document.getElementById("folio");
const tipo = document.getElementById("tipo");
const frecuencia = document.getElementById("frecuencia");
const fecharevision = document.getElementById("fecharevision");
const dias = document.getElementById("dias");
const fechainicio = document.getElementById("fechainicio");
const fechatermina = document.getElementById("fechatermina");
const st1 = document.getElementById("st1");
const stps = document.getElementById("stps");
const fechaentrega = document.getElementById("fechaentrega");
const dx = document.getElementById("dx");
const empleadoActivo = document.getElementById("empleadoActivo");
const checkActivo = document.getElementById("checkActivoVal");

fechainicio.addEventListener("change", (e) => {
  e.preventDefault();
  const nuevaFecha = new Date(e.target.value);
  const diasASumar = parseInt(dias.value, 10);
  if (!isNaN(diasASumar)) {
    nuevaFecha.setDate(nuevaFecha.getDate() + (diasASumar - 1));
    const fechaFormateada = nuevaFecha.toISOString().split("T")[0];
    fechatermina.value = fechaFormateada;
  } else {
    swal.fire("Ups", "El campo de dias esta vacío", "warning");
  }
});
document.getElementById("fecharevision").value = hoy;
const idsCamposObligatorios = [
  "noemp",
  "nombre",
  "departamento",
  "puesto",
  "responsable",
  "nombreresponsable",
  "folio",
  "tipo",
  "frecuencia",
  "dias",
  "fechainicio",
  "st1",
  "stps",
];

Tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "departamento", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcPuestos", "puesto", 0);
Tools.llnarslc("CatalogoEnfermeria", "GetEnfermeriaTipoIncapacidad", "tipo", 0);
Tools.llnarslc("CatalogoEnfermeria", "GetEnfermeriaFrec", "frecuencia", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcGeneralSiNoNa", "st1", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcGeneralSiNoNa", "stps", 0);
let data = await IncapacidadesObj.tblIncapacidadessession();

IncapacidadesObj.tblIncapacidad("tblIncapacidades", data, 1);

document.getElementById("noemp").addEventListener("keyup", (e) => {
  e.preventDefault();
  e.target.value != "" &&
    ConsultasObj.datauserEnfermeria(e.target.value).then((element) => {
      if (element.length > 0) {
        document.getElementById("nombre").value = element[0].nombre;
        document.getElementById("departamento").value = element[0].departamento;
        document.getElementById("puesto").value = element[0].puesto;
        departamento.disabled = true;
        puesto.disabled = true;
        empleadoActivo.checked = element[0].sindicalizado == 1;
        checkActivo.value = element[0].sindicalizado;
        document.getElementById("diasAcumulados").value =
          element[0].DiasAcumulados;
      } else {
        nombre.value = "";
        departamento.value = "";
        puesto.value = "";
      }
    });
});
responsable.addEventListener("keyup", (e) => {
  e.preventDefault();
  Tools.getDataEmpleado(e.target.value, "nombreresponsable");
});
document.getElementById("saveIncapacidad").addEventListener("click", (e) => {
  e.preventDefault();
  Tools.validarCamposPorID(idsCamposObligatorios) != false &&
    IncapacidadesObj.saveIncapacidad(
      noemp.value,
      departamento.value,
      puesto.value,
      responsable.value,
      folio.value,
      tipo.value,
      frecuencia.value,
      fecharevision.value,
      dias.value,
      fechainicio.value,
      st1.value,
      stps.value,
      fechaentrega.value,
      dx.value,
      id.value
    ).then(() => {
      IncapacidadesObj.tblIncapacidadessession().then((data) => {
        IncapacidadesObj.tblIncapacidad("tblIncapacidades", data, 1);
      });
      document
        .getElementById("saveIncapacidad")
        .classList.remove("btn-warning");
      document.getElementById("saveIncapacidad").classList.add("bg-target");
      document.getElementById("saveIncapacidad").innerHTML =
        '<i class="fas fa-save"></i> Guardar';
      folio.value = "";
      document.getElementById("formIncapacidad").reset();
    });
});

document
  .getElementById("limpiadoIncapacidad")
  .addEventListener("click", (e) => {
    e.preventDefault();
    document.getElementById("saveIncapacidad").classList.remove("btn-warning");
    document.getElementById("saveIncapacidad").classList.add("bg-target");
    document.getElementById("saveIncapacidad").innerHTML =
      '<i class="fas fa-save"></i> Guardar';
    folio.value = "";
    document.getElementById("formIncapacidad").reset();
    // FirmaObj.limpiarCanvas();
  });

window.editIncapacidad = (passelement) => {
  IncapacidadesObj.editarIncapacidad(passelement).then((element) => {
    console.log(element);
    id.value = passelement;
    noemp.value = element[0]["noemp"];
    nombre.value = element[0]["Nombre"];
    departamento.value = element[0]["departamento"];
    puesto.value = element[0]["puesto"];
    responsable.value = element[0]["responsable"];
    nombreresponsable.value = element[0]["responsablenombre"];
    folio.value = element[0]["folio"];
    tipo.value = element[0]["tipo"];
    frecuencia.value = element[0]["frecuencia"];
    dias.value = element[0]["dias"];
    fechainicio.value = element[0]["fechainicio"];
    st1.value = element[0]["st1"];
    stps.value = element[0]["stps"];
    fechaentrega.value = element[0]["fechaentrega"];
    dx.value = element[0]["dx"];
    diasAcumulados.value = element[0]["DiasAcumulados"];
    empleadoActivo.checked = element[0]["sindicalizado"] == 1;
    checkActivo.value = element[0]["sindicalizado"];

    calcularFechaTermina();
    document.getElementById("saveIncapacidad").classList.remove("bg-target");
    document.getElementById("saveIncapacidad").classList.add("btn-warning");
    document.getElementById("saveIncapacidad").innerHTML =
      '<i class="fa-solid fa-pen-to-square"></i> Actualizar';
  });
};

// FirmaObj.agregarEventos();
// document.getElementById("limpiarCanvas").addEventListener("click", () => {
//   FirmaObj.limpiarCanvas();
// });

// Calcula fecha de término al cargar la incapacidad
const calcularFechaTermina = () => {
  const fechaInicioValor = fechainicio.value;
  const diasASumar = parseInt(dias.value, 10);

  if (fechaInicioValor && !isNaN(diasASumar)) {
    const nuevaFecha = new Date(fechaInicioValor);
    nuevaFecha.setDate(nuevaFecha.getDate() + (diasASumar - 1));
    const fechaFormateada = nuevaFecha.toISOString().split("T")[0];
    fechatermina.value = fechaFormateada;
  } else {
    swal.fire("Ups", "El campo de días o fecha inicio está vacío", "warning");
  }
};
