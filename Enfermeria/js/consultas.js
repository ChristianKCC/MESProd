import { Toolsjs } from "../../Tools/Tools.js";
import { Consultas } from "../module/index.js";
import { Firma } from "../../Modules/Autenticacion.js";

const Tools = new Toolsjs();
const ConsultasObj = new Consultas();
const folio = document.getElementById("id");
const noemp = document.getElementById("noemp");
const nombre = document.getElementById("nombre");
const departamento = document.getElementById("departamento");
const puesto = document.getElementById("puesto");
const maquinas = document.getElementById("maquinas");
const edad = document.getElementById("edad");
const antiguedad = document.getElementById("antiguedad");
const tratamiento = document.getElementById("tratamiento");
const observacion = document.getElementById("observacion");
const tipoaparato = document.getElementById("tipoaparato");
const tipoenfermedad = document.getElementById("tipoenfermedad");
const tipoconsulta = document.getElementById("tipoconsulta");
const sexo = document.getElementById("sexo");
const rolturno = document.getElementById("rolturno");
const temperatura = document.getElementById("temperatura");
const frecuencia = document.getElementById("frecuencia");
const pasistolica = document.getElementById("pasistolica");
const padistolica = document.getElementById("padistolica");
const nombreexterno = document.getElementById("nombreexterno");
const empresaexterna = document.getElementById("empresaexterna");
const fecharevision = document.getElementById("fecharevision");
const horaRevision = document.getElementById("horaRevision");

const canvas = document.getElementById("canvas");
canvas.width = 1200;
canvas.height = 600;
const ctx1 = canvas.getContext("2d", { alpha: true });

const hoy = new Date().toISOString().split("T")[0];
document.getElementById("fecharevision").value = hoy;
ConsultasObj.tblConsultaSession("tblconsultas");
const idsCamposObligatorios = [
  "noemp",
  "departamento",
  "puesto",
  "maquinas",
  "edad",
  "antiguedad",
  "tratamiento",
  "observacion",
  "tipoaparato",
  "tipoenfermedad",
  "tipoconsulta",
  "temperatura",
  "frecuencia",
  "pasistolica",
  "padistolica",
];

Tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "departamento", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcPuestos", "puesto", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcMaquinas", "maquinas", 0);
Tools.llnarslc("CatalogoEnfermeria", "GetEnfermeriaEquipos", "tipoaparato", 0);
Tools.llnarslc(
  "CatalogoEnfermeria",
  "GetEnfermeriaTipoConsult",
  "tipoconsulta",
  0
);

document.getElementById("tipoaparato").addEventListener("change", (e) => {
  e.preventDefault();
  Tools.llnarslc(
    "CatalogoEnfermeria",
    "GetEnfermeriaEnfermedades&id=" + e.target.value,
    "tipoenfermedad",
    0
  );
});
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
      } else {
        nombre.value = "";
        departamento.value = "";
        puesto.value = "";
      }
    });
});

let timerHandle = null;

document.getElementById("btnSign").addEventListener("click", () => {
  ctx1.clearRect(0, 0, canvas.width, canvas.height);
  try {
    SetDisplayXSize(canvas.width);
    SetDisplayYSize(canvas.height);

    SetTabletState(0, timerHandle);
    ClearTablet();

    timerHandle = SetTabletState(1, ctx1, 50);
  } catch (e) {
    alert(
      "No se pudo inicializar SigWeb. ¿Está instalado y permitido el acceso de red local en el navegador?",
    );
    console.error(e);
  }
});

document.getElementById("saveConsulta").addEventListener("click", (e) => {
  e.preventDefault();
  const canvas = document.getElementById("canvas");
  const externo = document.getElementById("externo");
  
  if (externo.checked) {
    noemp.value = 1;
    departamento.value = 56;
    puesto.value = 78;
    maquinas.value = 90;
    antiguedad.value = 0;
    nombre.value = nombreexterno.value;
  }
  Tools.validarCamposPorID(idsCamposObligatorios) != false &&
    ConsultasObj.saveConsulta(
      noemp.value,
      nombre.value,
      departamento.value,
      puesto.value,
      maquinas.value,
      edad.value,
      antiguedad.value,
      tratamiento.value,
      observacion.value,
      tipoaparato.value,
      tipoenfermedad.value,
      tipoconsulta.value,
      sexo.value,
      rolturno.value,
      temperatura.value,
      frecuencia.value,
      pasistolica.value,
      padistolica.value,
      nombreexterno.value,
      empresaexterna.value,
      folio.value,
      fecharevision.value,
      horaRevision.value,
      canvas
    ).then(() => {
      ConsultasObj.tblConsultaSession("tblconsultas");
      document.getElementById("saveConsulta").classList.remove("btn-warning");
      document.getElementById("saveConsulta").classList.add("bg-target");
      document.getElementById("saveConsulta").innerHTML =
        '<i class="fas fa-save"></i> Guardar';
      folio.value = "";
      document.getElementById("formConsultas").reset();
      document.getElementById("camposExternos").hidden = !externo.checked;
      document.getElementById("camposInternos").hidden = externo.checked;
      FirmaObj.limpiarCanvas();
    });
});

document.getElementById("externo").addEventListener("change", function () {
  const campose = document.getElementById("camposExternos");
  const camposi = document.getElementById("camposInternos");
  const mostrar = this.checked;
  campose.hidden = !mostrar;
  camposi.hidden = mostrar;
});

document.getElementById("limpiadoConsulta").addEventListener("click", (e) => {
  e.preventDefault();
  document.getElementById("saveConsulta").classList.remove("btn-warning");
  document.getElementById("saveConsulta").classList.add("bg-target");
  document.getElementById("saveConsulta").innerHTML =
    '<i class="fas fa-save"></i> Guardar';
  folio.value = "";
  document.getElementById("formConsultas").reset();
  FirmaObj.limpiarCanvas();
});

window.editconsulta = (id) => {
  ConsultasObj.editconsulta(id).then((element) => {
    folio.value = element[0][0];
    noemp.value = element[0][1];
    departamento.value = element[0][2];
    puesto.value = element[0][3];
    maquinas.value = element[0][4];
    edad.value = element[0][5];
    antiguedad.value = element[0][6];
    tratamiento.value = element[0][7];
    observacion.value = element[0][8];
    tipoaparato.value = element[0][9];
    Tools.llnarslc(
      "CatalogoEnfermeria",
      "GetEnfermeriaEnfermedades&id=" + element[0][9],
      "tipoenfermedad",
      0
    ).then(() => {
      tipoenfermedad.value = element[0][10];
    });
    tipoconsulta.value = element[0][11];
    fecharevision.value = element[0][12];
    horaRevision.value = element[0][23];
    sexo.value = element[0][13];
    rolturno.value = element[0][14];
    temperatura.value = element[0][15];
    frecuencia.value = element[0][16];
    pasistolica.value = element[0][17];
    padistolica.value = element[0][18];
    nombreexterno.value = element[0][19];
    empresaexterna.value = element[0][20];
    nombre.value = element[0][24];
    document.getElementById("saveConsulta").classList.remove("bg-target");
    document.getElementById("saveConsulta").classList.add("btn-warning");
    document.getElementById("saveConsulta").innerHTML =
      '<i class="fa-solid fa-pen-to-square"></i> Actualizar';
  });
};


document.getElementById("limpiarCanvas").addEventListener("click", () => {
  FirmaObj.limpiarCanvas();
});
