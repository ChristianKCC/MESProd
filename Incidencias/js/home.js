import { Toolsjs } from "../../Tools/Tools.js";
import { Incidencias } from "../module/Incidencias.js";
import { ReporteIncidencias } from "../module/Incidencias.js";
const ReporteIncidenciasObj = new ReporteIncidencias();

const Tools = new Toolsjs();
const Incidenciaobj = new Incidencias();
const idEtapa5 = document.getElementById("idEtapa5");
const idEtapa6 = document.getElementById("idEtapa6");
const idEtapa7 = document.getElementById("idEtapa7");
const idEtapa8 = document.getElementById("idEtapa8");
const btnSaveEtapa4 = document.getElementById("saveetapa4");
const btnSaveEtapa5 = document.getElementById("saveetapa5");
const btnSaveEtapa6 = document.getElementById("saveetapa6");
const btnSaveEtapa7 = document.getElementById("saveEtapa7");
const btnSaveEtapa8 = document.getElementById("saveEtapa8");
const btnLimpiarEtapa3 = document.getElementById("limpiaretapa3");
const btnLimpiarEtapa4 = document.getElementById("limpiaretapa4");
const btnLimpiarEtapa5 = document.getElementById("limpiaretapa5");
const btnLimpiarEtapa6 = document.getElementById("limpiaretapa6");
const btnLimpiarEtapa7 = document.getElementById("limpiarEtapa7");
const btnLimpiarEtapa8 = document.getElementById("limpiarEtapa8");

let folioenc = 0;
Tools.llnarslc("CatalogoPersonal", "GetSlcDeps", "NoDepto", 0);
Tools.llnarslc(
  "CatalogoSeguridad",
  "GetSlctblIncidenciasVersion",
  "version",
  0
);
Tools.llnarslc(
  "CatalogoSeguridad",
  "GetSlctbltblIncidenciasClasEven",
  "clasificacion",
  0
);
Tools.llnarslc(
  "CatalogoSeguridad",
  "GetSlcAntiguedadpuesto",
  "antiguedadpuesto",
  0
);
Tools.llnarslc(
  "CatalogoSeguridad",
  "GetSlcAntiguedadempresa",
  "antiguedadempresa",
  0
);
Tools.llnarslc("CatalogoSeguridad", "GetSlcTipocontacto", "tipocontacto", 0);
Tools.llnarslc("CatalogoSeguridad", "GetSlcTipolesion", "tipolesion", 0);
Tools.llnarslc("CatalogoSeguridad", "GetSlcParteAfect", "parteafectada", 0);
Incidenciaobj.llnarslcValor("GetSlcSeveridad", "severidad", 0);
Incidenciaobj.llnarslcValor("GetSlcProbabilidad", "probabilidad", 0);
Incidenciaobj.llnarslcValor("GetSlcFrecuencia", "frecuencia", 0);
Incidenciaobj.llnarslcValor("GetSlcPersonasafectadas", "noexpuetas", 0);

Incidenciaobj.tblEncabezado().then((element) => {
  let body = "";
  element.forEach((dataelement) => {
    body += `
         <div class="col-3 mx-4 mb-2 border rounded shadow-sm p-2 bg-white">
          <h4 class='fw-bold'>Folio: ${dataelement.id}</h4>
          <span>Departamento: ${dataelement.departamento}</span><br>
          <span>Maquina: ${dataelement.area}</span><br>
          <span>Clasificación:  ${dataelement.clasificacion}</span><br>
          <span>Incidencia:  ${dataelement.incidencia}</span><br>
          <span>Genero:  ${dataelement.noemgenero}</span><br>
          <span>Implicado:  ${dataelement.noempimplicado}</span>
          <p><a href="#" class="btn text-white form-control btn-sm my-2" style="background:#0096c7;" data-bs-toggle="modal" data-bs-target="#modaletapa3" data-bs-whatever="${dataelement.id}"><i class="fa-solid fa-magnifying-glass"></i> Etapa 3</a>
          <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#0077B6;" data-bs-toggle="modal" data-bs-target="#modaletapa4" data-bs-whatever="${dataelement.id}"> <i class="fa-solid fa-triangle-exclamation"></i> Etapa 4</a>
          <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#023E8A;" data-bs-toggle="modal" data-bs-target="#modaletapa5" data-bs-whatever="${dataelement.id}"><i class="fa-solid fa-people-carry-box"></i>  Etapa 5</a>
          <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#021E9A;" data-bs-toggle="modal" data-bs-target="#modaletapa6" data-bs-whatever="${dataelement.id}"><i class="fa fa-hand-pointer"></i>  Etapa 6</a>
          <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#021E9A;" data-bs-toggle="modal" data-bs-target="#modaletapa7" data-bs-whatever="${dataelement.id}"><i class="fa-solid fa-clipboard"></i>  Etapa 7</a>
          <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#021E9A;" data-bs-toggle="modal" data-bs-target="#modaletapa8" data-bs-whatever="${dataelement.id}"><i class="fa fa-check-circle"></i>  Etapa 8</a></p>
          <div class="row justify-content-end m-2">
            <div class="col-6">
              <button class="btn btn-sm btn-danger" id="editBtnE1" onclick="generarPDF(${dataelement.id})"><i class="fas fa-file-pdf"></i></button>
              <button class="btn btn-sm btn-warning" onclick="editBtnE1(${dataelement.id})">Editar <i class="fas fa-edit"></i></button>
            </div>
          </div>
        </div>        
        `;
  });
  document.getElementById("tblenc").innerHTML = body;
});

document.getElementById("implicado").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(
    e.target.value,
    "implicadonombre",
    "",
    "implicadopuesto"
  );
});

const idsCamposObligatorios = [
  "fecha",
  "NoDepto",
  "NoMaquina",
  "version",
  "clasificacion",
  "incidencias",
  "descripcioncapa",
  "severidad",
  "probabilidad",
  "frecuencia",
  "noexpuetas",
  // "asigusauariocapa",
];

document.getElementById("NoDepto").addEventListener("change", () => {
  const dep = document.getElementById("NoDepto").value;
  Tools.llnarslc("CatalogoSeguridad", "GetSlcAreas&dep=" + dep, "NoMaquina", 0);
});

document.getElementById("clasificacion").addEventListener("change", () => {
  const clasificacion = document.getElementById("clasificacion").value;
  Tools.llnarslc(
    "CatalogoSeguridad",
    "GetSlcIncidencias&clasificacion=" + clasificacion,
    "incidencias",
    0
  );
});
document.getElementById("guardaret1").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const fecha = document.getElementById("fecha").value;
  const NoDepto = document.getElementById("NoDepto").value;
  const NoMaquina = document.getElementById("NoMaquina").value;
  const version = document.getElementById("version").value;
  const clasificacion = document.getElementById("clasificacion").value;
  const incidencias = document.getElementById("incidencias").value;
  const descripcioncapa = document.getElementById("descripcioncapa").value;
  const implicado = document.getElementById("implicado").value;
  const antiguedadpuesto = document.getElementById("antiguedadpuesto").value;
  const antiguedadempresa = document.getElementById("antiguedadempresa").value;
  const diasincapacidad = document.getElementById("diasincapacidad").value;
  const diastrabajo = document.getElementById("diastrabajo").value;
  const tipocontacto = document.getElementById("tipocontacto").value;
  const provocolesion = document.getElementById("provocolesion").value;
  const tipolesion = document.getElementById("tipolesion").value;
  const parteafectada = document.getElementById("parteafectada").value;
  const severidad = document.getElementById("severidad").value;
  const probabilidad = document.getElementById("probabilidad").value;
  const frecuencia = document.getElementById("frecuencia").value;
  const noexpuetas = document.getElementById("noexpuetas").value;
  const etapa1check1 = document.querySelector(
    'input[name="etapa1check1"]:checked'
  ).value;
  const etapa1check2 = document.querySelector(
    'input[name="etapa1check2"]:checked'
  ).value;
  const fileInput = document.getElementById("archivo");
  const file =
    fileInput.files[0] !== undefined ? fileInput.files[0] : "Sin archivo";
  const noReporte = document.getElementById("noReporte").value;
  const totalE1 = document.getElementById("total").value;

  const res = Tools.validarCamposPorID(idsCamposObligatorios);
  res != false &&
    Incidenciaobj.saveIncidencia(
      fecha,
      NoDepto,
      NoMaquina,
      version,
      clasificacion,
      incidencias,
      descripcioncapa,
      implicado,
      antiguedadpuesto,
      antiguedadempresa,
      diasincapacidad,
      diastrabajo,
      tipocontacto,
      provocolesion,
      tipolesion,
      parteafectada,
      severidad,
      probabilidad,
      frecuencia,
      noexpuetas,
      etapa1check1,
      etapa1check2,
      file,
      noReporte,
      totalE1,
      folio
    ).then(() => {
      Incidenciaobj.tblEncabezado().then((element) => {
        let body = "";
        element.forEach((dataelement) => {
          body += `
                        <div class="col-3 mx-4 mb-2 border rounded shadow-sm p-2 bg-white">
                          <h4 class='fw-bold'>Folio: ${dataelement.id}</h4>
                          <span>Departamento: ${dataelement.departamento}</span><br>
                          <span>Maquina: ${dataelement.area}</span><br>
                          <span>Clasificación:  ${dataelement.clasificacion}</span><br>
                          <span>Incidencia:  ${dataelement.incidencia}</span><br>
                          <span>Genero:  ${dataelement.noemgenero}</span><br>
                          <span>Implicado:  ${dataelement.noempimplicado}</span>
                          <p><a href="#" class="btn text-white form-control btn-sm my-2" style="background:#0096c7;" data-bs-toggle="modal" data-bs-target="#modaletapa3" data-bs-whatever="${dataelement.id}"><i class="fa-solid fa-magnifying-glass"></i> Etapa 3</a>
                          <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#0077B6;" data-bs-toggle="modal" data-bs-target="#modaletapa4" data-bs-whatever="${dataelement.id}"> <i class="fa-solid fa-triangle-exclamation"></i> Etapa 4</a>
                          <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#023E8A;" data-bs-toggle="modal" data-bs-target="#modaletapa5" data-bs-whatever="${dataelement.id}"><i class="fa-solid fa-people-carry-box"></i>  Etapa 5</a>
                            <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#021E9A;" data-bs-toggle="modal" data-bs-target="#modaletapa6" data-bs-whatever="${dataelement.id}"><i class="fa-solid fa-clipboard"></i>  Etapa 6</a></p>
                          <div class="row justify-content-end m-2">
                            <div class="col-6">
                              <button class="btn btn-outline-danger" id="editBtnE1" onclick="generarPDF(${dataelement.id})"><i class="fa-regular fa-file-pdf"></i></button>
                              <button class="btn btn-outline-warning" onclick="editBtnE1(${dataelement.id})">Editar <i class="fas fa-edit"></i></button>
                            </div>
                          </div>
                        </div>
                        `;
        });
        document.getElementById("tblenc").innerHTML = body;
      });
      document.getElementById("guardaret1").classList.remove("btn-warning");
      document.getElementById("guardaret1").classList.add("bg-target");
      document.getElementById("guardaret1").innerHTML =
        '<i class="fas fa-save"></i> Guardar';
    });
});

window.generarPDF = (id) => {
  ReporteIncidenciasObj.generarReporte(id);
};

const modal = new bootstrap.Modal(document.getElementById("modalencabezado"));
window.editBtnE1 = (id) => {
  modal.show();
  Incidenciaobj.editEtapa1y2(id).then((element) => {
    console.log(element);
    document.getElementById("folio").value = element[0].id;
    document.getElementById("fecha").value = element[0].fecha;
    document.getElementById("NoDepto").value = element[0].departamento;
    Tools.llnarslc(
      "CatalogoSeguridad",
      "GetSlcAreas&dep=" + element[0].departamento,
      "NoMaquina",
      0
    ).then(() => {
      document.getElementById("NoMaquina").value = element[0].area;
    });
    document.getElementById("version").value = element[0].vesion;
    document.getElementById("clasificacion").value = element[0].clasificacion;
    Tools.llnarslc(
      "CatalogoSeguridad",
      "GetSlcIncidencias&clasificacion=" + element[0].clasificacion,
      "incidencias",
      0
    ).then(() => {
      document.getElementById("incidencias").value = element[0].incidencia;
    });
    document.getElementById("descripcioncapa").value = element[0].descripcion;
    document.getElementById("implicado").value = element[0].noempimplicado;
    document.getElementById("implicadonombre").value = element[0].Nombres;
    document.getElementById("implicadopuesto").value = element[0].NombrePuesto;
    document.getElementById("antiguedadpuesto").value =
      element[0].antiguedadpuesto;
    document.getElementById("antiguedadempresa").value =
      element[0].antiguedadempresa;
    document.getElementById("diasincapacidad").value =
      element[0].diasincapacidad;
    document.getElementById("diastrabajo").value = element[0].diastrabajo;
    document.getElementById("tipocontacto").value = element[0].tipocontacto;
    document.getElementById("provocolesion").value = element[0].provoco;
    document.getElementById("tipolesion").value = element[0].tipolesion;
    document.getElementById("parteafectada").value = element[0].parteafectada;
    document.getElementById("severidad").value = element[0].severidad;
    document.getElementById("probabilidad").value = element[0].probabilidad;
    document.getElementById("frecuencia").value = element[0].frecuencia;
    document.getElementById("noexpuetas").value = element[0].numafectados;
    document.getElementById("total").value = element[0].Total;
    document.getElementById("noReporte").value = element[0].NoReporte;

    let valLesion = parseInt(element[0].lesion);
    let valEquipos = parseInt(element[0].equipos);

    valLesion === 1
      ? (document.getElementById("radioSi1").checked = true)
      : (document.getElementById("radioNo1").checked = true);

    valEquipos === 1
      ? (document.getElementById("radioSi2").checked = true)
      : (document.getElementById("radioNo2").checked = true);

    document.getElementById("guardaret1").classList.remove("bg-target");
    document.getElementById("guardaret1").classList.add("btn-warning");
    document.getElementById("guardaret1").innerHTML =
      '<i class="fa-solid fa-pen-to-square"></i> Actualizar';
  });
};

document.getElementById("cerrarEt1").addEventListener("click", (e) => {
  e.preventDefault();
  document.getElementById("folio").value = "";
  document.getElementById("fecha").value = "";
  document.getElementById("NoDepto").value = "";
  document.getElementById("NoMaquina").value = "";

  document.getElementById("version").value = "";
  document.getElementById("clasificacion").value = "";
  document.getElementById("incidencias").value = "";
  document.getElementById("descripcioncapa").value = "";
  document.getElementById("implicado").value = "";
  document.getElementById("implicadonombre").value = "";
  document.getElementById("implicadopuesto").value = "";
  document.getElementById("antiguedadpuesto").value = "";
  document.getElementById("antiguedadempresa").value = "";
  document.getElementById("diasincapacidad").value = "";
  document.getElementById("diastrabajo").value = "";
  document.getElementById("tipocontacto").value = "";
  document.getElementById("provocolesion").value = "";
  document.getElementById("tipolesion").value = "";
  document.getElementById("parteafectada").value = "";
  document.getElementById("severidad").value = "";
  document.getElementById("probabilidad").value = "";
  document.getElementById("frecuencia").value = "";
  document.getElementById("noexpuetas").value = "";

  document.getElementById("radioSi1").checked = false;
  document.getElementById("radioNo1").checked = true;

  document.getElementById("radioSi2").checked = false;
  document.getElementById("radioNo2").checked = true;
  document.getElementById("guardaret1").classList.remove("btn-warning");
  document.getElementById("guardaret1").classList.add("bg-target");
  document.getElementById("guardaret1").innerHTML =
    '<i class="fas fa-save"></i> Guardar';
  document.getElementById("noReporte").value = "";
  document.getElementById("total").value = "";
});

document.getElementById("btn-close").addEventListener("click", (e) => {
  e.preventDefault;
  document.getElementById("folio").value = "";
  document.getElementById("fecha").value = "";
  document.getElementById("NoDepto").value = "";
  document.getElementById("NoMaquina").value = "";

  document.getElementById("version").value = "";
  document.getElementById("clasificacion").value = "";
  document.getElementById("incidencias").value = "";
  document.getElementById("descripcioncapa").value = "";
  document.getElementById("implicado").value = "";
  document.getElementById("implicadonombre").value = "";
  document.getElementById("implicadopuesto").value = "";
  document.getElementById("antiguedadpuesto").value = "";
  document.getElementById("antiguedadempresa").value = "";
  document.getElementById("diasincapacidad").value = "";
  document.getElementById("diastrabajo").value = "";
  document.getElementById("tipocontacto").value = "";
  document.getElementById("provocolesion").value = "";
  document.getElementById("tipolesion").value = "";
  document.getElementById("parteafectada").value = "";
  document.getElementById("severidad").value = "";
  document.getElementById("probabilidad").value = "";
  document.getElementById("frecuencia").value = "";
  document.getElementById("noexpuetas").value = "";

  document.getElementById("radioSi1").checked = false;
  document.getElementById("radioNo1").checked = true;

  document.getElementById("radioSi2").checked = false;
  document.getElementById("radioNo2").checked = true;
  document.getElementById("guardaret1").classList.remove("btn-warning");
  document.getElementById("guardaret1").classList.add("bg-target");
  document.getElementById("guardaret1").innerHTML =
    '<i class="fas fa-save"></i> Guardar';
  document.getElementById("noReporte").value = "";
  document.getElementById("total").value = "";
});

// Etapa 3
// modal1

const exampleModal = document.getElementById("modaletapa3");
exampleModal.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = exampleModal.querySelector(".modal-title");
  modalTitle.textContent = "Descripción del evento";
  folioenc = recipient;
  Incidenciaobj.tblEtapa3(recipient);
});

document.getElementById("responsable1").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "responsablenombre1", "", "");
});

document.getElementById("responsable2").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "responsablenombre2", "", "");
});

document.getElementById("responsable3").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "responsablenombre3", "", "");
});

const idsCamposObligatoriosetapa3 = [];
document.getElementById("saveetapa3").addEventListener("click", (e) => {
  e.preventDefault();
  const id = document.getElementById("folioetap3").value;
  const eventosprev = document.getElementById("eventosprev").value;
  const eventofalla = document.getElementById("eventofalla").value;
  const equipos = document.getElementById("equipos").value;
  const operacion = document.getElementById("operacion").value;
  const producto = document.getElementById("producto").value;
  const material = document.getElementById("material").value;
  const otro = document.getElementById("otro").value;
  const otroexplique = document.getElementById("otroexplique").value;
  const descp1 = document.getElementById("descp1").value;
  const responsable1 = document.getElementById("responsable1").value;
  const fechaimp1 = document.getElementById("fechaimp1").value;
  const descp2 = document.getElementById("descp2").value;
  const responsable2 = document.getElementById("responsable2").value;
  const fechaimp2 = document.getElementById("fechaimp2").value;
  const descp3 = document.getElementById("descp3").value;
  const responsable3 = document.getElementById("responsable3").value;
  const fechaimp3 = document.getElementById("fechaimp3").value;
  const res = Tools.validarCamposPorID(idsCamposObligatoriosetapa3);
  res != false &&
    Incidenciaobj.saveIncidenciaEtapa3(
      id,
      eventosprev,
      eventofalla,
      equipos,
      operacion,
      producto,
      material,
      otro,
      otroexplique,
      descp1,
      responsable1,
      fechaimp1,
      descp2,
      responsable2,
      fechaimp2,
      descp3,
      responsable3,
      fechaimp3,
      folioenc
    ).then(() => {
      Incidenciaobj.tblEtapa3(folioenc);
      document.getElementById("saveetapa3").classList.remove("btn-warning");
      document.getElementById("saveetapa3").classList.add("bg-target");
      document.getElementById("saveetapa3").innerHTML =
        '<i class="fa-solid fa-floppy-disk"></i> Guardar';
      const id = (document.getElementById("folioetap3").value = "");
      const eventosprev = (document.getElementById("eventosprev").value = "");
      const eventofalla = (document.getElementById("eventofalla").value = "");
      const equipos = (document.getElementById("equipos").value = "");
      const operacion = (document.getElementById("operacion").value = "");
      const producto = (document.getElementById("producto").value = "");
      const material = (document.getElementById("material").value = "");
      const otro = (document.getElementById("otro").value = "");
      const otroexplique = (document.getElementById("otroexplique").value = "");
      const descp1 = (document.getElementById("descp1").value = "");
      const responsable1 = (document.getElementById("responsable1").value = "");
      const fechaimp1 = (document.getElementById("fechaimp1").value = "");
      const descp2 = (document.getElementById("descp2").value = "");
      const responsable2 = (document.getElementById("responsable2").value = "");
      const fechaimp2 = (document.getElementById("fechaimp2").value = "");
      const descp3 = (document.getElementById("descp3").value = "");
      const responsable3 = (document.getElementById("responsable3").value = "");
      const fechaimp3 = (document.getElementById("fechaimp3").value = "");
    });
});

window.editEtapa3 = (id) => {
  Incidenciaobj.editEtapaTres(id).then((element) => {
    console.log(element);
    const folio = (document.getElementById("folioetap3").value = element[0].id);
    const noReporteE3 = (document.getElementById("noReporteEtapa3").value =
      element[0].NoReporte);
    const eventosprev = (document.getElementById("eventosprev").value =
      element[0].EventosPrev);
    const incidente = (document.getElementById("eventofalla").value =
      element[0].Eventofalla);
    const equipos = (document.getElementById("equipos").value =
      element[0].danoequipo);
    const operacion = (document.getElementById("operacion").value =
      element[0].suspension);
    const producto = (document.getElementById("producto").value =
      element[0].producto);
    const material = (document.getElementById("material").value =
      element[0].material);
    const otro = (document.getElementById("otro").value = element[0].otro);
    const otroexplique = (document.getElementById("otroexplique").value =
      element[0].otrodesc);
    const descp1 = (document.getElementById("descp1").value = element[0].desc1);
    const responsable1 = (document.getElementById("responsable1").value =
      element[0][10]);
    const responsablenombre1 = (document.getElementById(
      "responsablenombre1"
    ).value = element[0].Nombre1);
    const fechaimp1 = (document.getElementById("fechaimp1").value =
      element[0].fecha1);
    const descp2 = (document.getElementById("descp2").value = element[0].desc2);
    const responsable2 = (document.getElementById("responsable2").value =
      element[0].resp2);
    const responsablenombre2 = (document.getElementById(
      "responsablenombre2"
    ).value = element[0].Nombre2);
    const fechaimp2 = (document.getElementById("fechaimp2").value =
      element[0].fecha2);
    const descp3 = (document.getElementById("descp3").value = element[0].desc3);
    const responsable3 = (document.getElementById("responsable3").value =
      element[0].resp3);
    const responsablenombre3 = (document.getElementById(
      "responsablenombre3"
    ).value = element[0].Nombre3);
    const fechaimp3 = (document.getElementById("fechaimp3").value =
      element[0].fecha3);

    document.getElementById("saveetapa3").classList.remove("bg-target");
    document.getElementById("saveetapa3").classList.add("btn-warning");
    document.getElementById("saveetapa3").innerHTML =
      '<i class="fa-solid fa-pen-to-square"></i> Actualizar';
  });
};

// modal2

const exampleModal2 = document.getElementById("modaletapa4");
exampleModal2.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = exampleModal2.querySelector(".modal-title");
  folioenc = recipient;
  modalTitle.textContent = "Análisis de causas y plan de acciones correctivas";
  Incidenciaobj.tblEtapa4(recipient);
});

// ------------- ETAPA 4 ---------------
Tools.llnarslc("CatalogoSeguridad", "getComportamiento", "comportamiento", 0);
Tools.llnarslc("CatalogoSeguridad", "getCausaraiz", "causaraiz", 0);

document.getElementById("comportamiento").addEventListener("change", (e) => {
  Tools.llnarslc(
    "CatalogoSeguridad",
    "getCausainmediata&comportamiento=" + e.target.value,
    "causainmediata",
    0
  );
  Tools.llnarslc(
    "CatalogoSeguridad",
    "getCausabasica&comportamiento=" + e.target.value,
    "causabasica",
    0
  );
});

document.getElementById("responsableetapa4").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "responsablenombreetapa4", "", "");
});
document.getElementById("responsableetapa42").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "responsablenombreetapa42", "", "");
});
document.getElementById("responsableetapa43").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "responsablenombreetapa43", "", "");
});
document.getElementById("responsableetapa44").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "responsablenombreetapa44", "", "");
});
document.getElementById("responsableetapa45").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "responsablenombreetapa45", "", "");
});

document.getElementById("saveetapa4").addEventListener("click", (e) => {
  e.preventDefault();
  const folioetapa4 = document.getElementById("folioetapa4").value;
  const comportamiento = document.getElementById("comportamiento").value;
  const causainmediata = document.getElementById("causainmediata").value;
  const porquecausa = document.getElementById("porquecausa").value;
  const causabasica = document.getElementById("causabasica").value;
  const porque1 = document.getElementById("1porque").value;
  const causaraiz = document.getElementById("causaraiz").value;
  const porqueraiz = document.getElementById("porqueraiz").value;

  const accioncorrectiva = document.getElementById("accioncorrectiva").value;
  const responsableetapa4 = document.getElementById("responsableetapa4").value;
  const fechaac = document.getElementById("fechaac").value;

  const accioncorrectiva2 = document.getElementById("accioncorrectiva2").value;
  const responsableetapa42 =
    document.getElementById("responsableetapa42").value;
  const fechaac2 = document.getElementById("fechaac2").value;

  const accioncorrectiva3 = document.getElementById("accioncorrectiva3").value;
  const responsableetapa43 =
    document.getElementById("responsableetapa43").value;
  const fechaac3 = document.getElementById("fechaac3").value;

  const accioncorrectiva4 = document.getElementById("accioncorrectiva4").value;
  const responsableetapa44 =
    document.getElementById("responsableetapa44").value;
  const fechaac4 = document.getElementById("fechaac4").value;

  const accioncorrectiva5 = document.getElementById("accioncorrectiva5").value;
  const responsableetapa45 =
    document.getElementById("responsableetapa45").value;
  const fechaac5 = document.getElementById("fechaac5").value;
  Incidenciaobj.saveEtapa4(
    folioetapa4,
    comportamiento,
    causainmediata,
    porquecausa,
    causabasica,
    porque1,
    causaraiz,
    porqueraiz,
    accioncorrectiva,
    responsableetapa4,
    fechaac,
    accioncorrectiva2,
    responsableetapa42,
    fechaac2,
    accioncorrectiva3,
    responsableetapa43,
    fechaac3,
    accioncorrectiva4,
    responsableetapa44,
    fechaac4,
    accioncorrectiva5,
    responsableetapa45,
    fechaac5,
    folioenc
  ).then(() => {
    Incidenciaobj.tblEtapa4(folioenc);
    btnSaveEtapa4.classList.remove("btn-warning");
    btnSaveEtapa4.classList.add("bg-target");
    btnSaveEtapa4.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar';
    document.getElementById("folioetapa4").value = "";
    document.getElementById("comportamiento").value = "";
    document.getElementById("causainmediata").value = "";
    document.getElementById("porquecausa").value = "";
    document.getElementById("causabasica").value = "";
    document.getElementById("1porque").value = "";
    document.getElementById("causaraiz").value = "";
    document.getElementById("porqueraiz").value = "";
    document.getElementById("accioncorrectiva").value = "";
    document.getElementById("responsableetapa4").value = "";
    document.getElementById("responsablenombreetapa4").value = "";
    document.getElementById("fechaac").value = "";
    document.getElementById("accioncorrectiva2").value = "";
    document.getElementById("responsableetapa42").value = "";
    document.getElementById("responsablenombreetapa42").value = "";
    document.getElementById("fechaac2").value = "";
    document.getElementById("accioncorrectiva3").value = "";
    document.getElementById("responsableetapa43").value = "";
    document.getElementById("responsablenombreetapa43").value = "";
    document.getElementById("fechaac3").value = "";
    document.getElementById("accioncorrectiva4").value = "";
    document.getElementById("responsableetapa44").value = "";
    document.getElementById("responsablenombreetapa44").value = "";
    document.getElementById("fechaac4").value = "";
    document.getElementById("accioncorrectiva5").value = "";
    document.getElementById("responsableetapa45").value = "";
    document.getElementById("responsablenombreetapa45").value = "";
    document.getElementById("fechaac5").value = "";
  });
});

window.editEtapa4 = (id) => {
  Incidenciaobj.editEtapaCuatro(id).then((element) => {
    document.getElementById("noReporteEtapa4").value = element[0].NoReporte;
    document.getElementById("folioetapa4").value = element[0].id;
    document.getElementById("comportamiento").value = element[0].comportamiento;
    Tools.llnarslc(
      "CatalogoSeguridad",
      "getCausainmediata&comportamiento=" + element[0].comportamiento,
      "causainmediata",
      0
    ).then(() => {
      document.getElementById("causainmediata").value =
        element[0].causainmediata;
    });
    document.getElementById("porquecausa").value = element[0].porquecausa;
    Tools.llnarslc(
      "CatalogoSeguridad",
      "getCausabasica&comportamiento=" + element[0].comportamiento,
      "causabasica",
      0
    ).then(() => {
      document.getElementById("causabasica").value = element[0].causabasica;
    });
    document.getElementById("1porque").value = element[0].porque1;
    document.getElementById("causaraiz").value = element[0].causaraiz;
    document.getElementById("porqueraiz").value = element[0].porqueraiz;

    // Acciones Correctivas
    document.getElementById("accioncorrectiva").value =
      element[0].accioncorrectiva;
    document.getElementById("responsableetapa4").value =
      element[0].responsableetapa4;
    document.getElementById("responsablenombreetapa4").value =
      element[0].Nombre;
    document.getElementById("fechaac").value = element[0].fechaac;
    document.getElementById("accioncorrectiva2").value =
      element[0].accioncorrectiva2;
    document.getElementById("responsableetapa42").value =
      element[0].responsableetapa42;
    document.getElementById("responsablenombreetapa42").value =
      element[0].Nombre2;
    document.getElementById("fechaac2").value = element[0].fechaac2;
    document.getElementById("accioncorrectiva3").value =
      element[0].accioncorrectiva3;
    document.getElementById("responsableetapa43").value =
      element[0].responsableetapa43;
    document.getElementById("responsablenombreetapa43").value =
      element[0].Nombre3;
    document.getElementById("fechaac3").value = element[0].fechaac3;
    document.getElementById("accioncorrectiva4").value =
      element[0].accioncorrectiva4;
    document.getElementById("responsableetapa44").value =
      element[0].responsableetapa44;
    document.getElementById("responsablenombreetapa44").value =
      element[0].Nombre4;
    document.getElementById("fechaac4").value = element[0].fechaac4;
    document.getElementById("accioncorrectiva5").value =
      element[0].accioncorrectiva5;
    document.getElementById("responsableetapa45").value =
      element[0].responsableetapa45;
    document.getElementById("responsablenombreetapa45").value =
      element[0].Nombre5;
    document.getElementById("fechaac5").value = element[0].fechaac5;
    btnSaveEtapa4.classList.remove("bg-target");
    btnSaveEtapa4.classList.add("btn-warning");
    btnSaveEtapa4.innerHTML =
      '<i class="fa-solid fa-pen-to-square"></i> Actualizar';
  });
};

// ------------- ETAPA 5 ---------------

Tools.llnarslc("CatalogoSeguridad", "sistemaGestion", "sistemagestion", 0);

document.getElementById("sistemagestion").addEventListener("change", (e) => {
  Tools.llnarslc(
    "CatalogoSeguridad",
    "sistemaGestionsub&tipoelemento=" + e.target.value,
    "sistemagestionsub",
    0
  );
});

const modaletapa5 = document.getElementById("modaletapa5");

modaletapa5.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  console.log(recipient);
  const modalTitle = modaletapa5.querySelector(".modal-title");
  folioenc = recipient;
  modalTitle.textContent = "Seguridad Centrada en las personas";
  // Incidenciaobj.tblEtapa5(recipient);
});

document.getElementById("saveetapa5").addEventListener("click", (e) => {
  e.preventDefault();
  const error1 = document.getElementById("incprisa").checked == true ? 1 : 0;
  const error2 =
    document.getElementById("incojostarea").checked == true ? 1 : 0;
  const error3 = document.getElementById("frustracion").checked == true ? 1 : 0;
  const error4 = document.getElementById("mente").checked == true ? 1 : 0;
  const error5 = document.getElementById("fatiga").checked == true ? 1 : 0;
  const error6 = document.getElementById("peligro").checked == true ? 1 : 0;
  const error7 = document.getElementById("riesgo").checked == true ? 1 : 0;
  const error8 = document.getElementById("equilibrio").checked == true ? 1 : 0;

  const interaccion1 = document.querySelector(
    'input[name="interaccion1"]:checked'
  ).value;
  const interaccion2 = document.querySelector(
    'input[name="interaccion2"]:checked'
  ).value;
  const interaccion3 = document.querySelector(
    'input[name="interaccion3"]:checked'
  ).value;
  const interaccion4 = document.querySelector(
    'input[name="interaccion4"]:checked'
  ).value;
  const interaccion5 = document.querySelector(
    'input[name="interaccion5"]:checked'
  ).value;
  const interaccion6 = document.querySelector(
    'input[name="interaccion6"]:checked'
  ).value;

  const riesgos1 = document.querySelector(
    'input[name="riesgos1"]:checked'
  ).value;
  const riesgos2 = document.querySelector(
    'input[name="riesgos2"]:checked'
  ).value;
  const riesgos3 = document.querySelector(
    'input[name="riesgos3"]:checked'
  ).value;
  const riesgos4 = document.querySelector(
    'input[name="riesgos4"]:checked'
  ).value;

  const riesgos1porque = document.getElementById("riesgos1porque").value;
  const riesgos2porque = document.getElementById("riesgos2porque").value;
  const riesgos3porque = document.getElementById("riesgos3porque").value;
  const riesgos4porque = document.getElementById("riesgos4porque").value;

  const sistemagestion = document.getElementById("sistemagestion").value;
  const sistemagestionsub = document.getElementById("sistemagestionsub").value;
  const sistemagestionporque = document.getElementById(
    "sistemagestionporque"
  ).value;
  Incidenciaobj.saveEtapa5(
    idEtapa5.value,
    error1,
    error2,
    error3,
    error4,
    error5,
    error6,
    error7,
    error8,
    interaccion1,
    interaccion2,
    interaccion3,
    interaccion4,
    interaccion5,
    interaccion6,
    riesgos1,
    riesgos1porque,
    riesgos2,
    riesgos2porque,
    riesgos3,
    riesgos3porque,
    riesgos4,
    riesgos4porque,
    folioenc
  ).then(() => {
    // Incidenciaobj.tblEtapa5(folioenc);
    btnSaveEtapa5.classList.remove("btn-warning");
    btnSaveEtapa5.classList.add("bg-target");
    btnSaveEtapa5.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar';
    idEtapa5.value = "";

    // Descarmar todos los radio buttons
    const radios = document.querySelectorAll('input[type="radio"]');
    radios.forEach((radio) => {
      radio.checked = false;
    });

    // Desmarcar todos los checkboxes
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });

    document.getElementById("riesgos1porque").value = "";
    document.getElementById("riesgos2porque").value = "";
    document.getElementById("riesgos3porque").value = "";
    document.getElementById("riesgos4porque").value = "";
    document.getElementById("sistemagestion").value = "";
    document.getElementById("sistemagestionsub").value = "";
    document.getElementById("sistemagestionporque").value = "";
  });
});

const selects = ["severidad", "probabilidad", "frecuencia", "noexpuetas"];

function calcularTotal() {
  let total = 1;
  selects.forEach((id) => {
    const select = document.getElementById(id);
    const selectedOption = select.options[select.selectedIndex];
    const value2 = parseFloat(selectedOption.dataset.value2 || 1);
    total *= value2;
  });
  document.getElementById("total").value = total.toFixed(2);
}

selects.forEach((id) => {
  document.getElementById(id).addEventListener("change", calcularTotal);
});

window.editEtapa5 = (id) => {
  Incidenciaobj.editEtapaCinco(id).then((element) => {
    idEtapa5.value = element[0].id;
    document.getElementById("incprisa").checked = element[0].incprisa;
    document.getElementById("incojostarea").checked = element[0].incojostarea;
    document.getElementById("frustracion").checked = element[0].frustracion;
    document.getElementById("mente").checked = element[0].mente;
    document.getElementById("fatiga").checked = element[0].fatiga;
    document.getElementById("peligro").checked = element[0].peligro;
    document.getElementById("riesgo").checked = element[0].riesgo;
    document.getElementById("equilibrio").checked = element[0].equilibrio;

    // Validción para los radio buttons interacciones
    let interaccion1 = parseInt(element[0].interaccion1);
    let interaccion2 = parseInt(element[0].interaccion2);
    let interaccion3 = parseInt(element[0].interaccion3);
    let interaccion4 = parseInt(element[0].interaccion4);
    let interaccion5 = parseInt(element[0].interaccion5);
    let interaccion6 = parseInt(element[0].interaccion6);

    interaccion1 === 1
      ? (document.getElementById("radioSiE1").checked = true)
      : (document.getElementById("radioNoE1").checked = true);

    interaccion2 === 1
      ? (document.getElementById("radioSi2E2").checked = true)
      : (document.getElementById("radioNo2E2").checked = true);

    interaccion3 === 1
      ? (document.getElementById("radioSi3").checked = true)
      : (document.getElementById("radioNo3").checked = true);

    interaccion4 === 1
      ? (document.getElementById("radioSi4").checked = true)
      : (document.getElementById("radioNo4").checked = true);

    interaccion5 === 1
      ? (document.getElementById("radioSi5").checked = true)
      : (document.getElementById("radioNo5").checked = true);

    interaccion6 === 1
      ? (document.getElementById("radioSi6").checked = true)
      : (document.getElementById("radioNo6").checked = true);

    // Validación para los radio buttos de riesgos
    let riesgo1 = parseInt(element[0].riesgos1);
    let riesgo2 = parseInt(element[0].riesgos2);
    let riesgo3 = parseInt(element[0].riesgos3);
    let riesgo4 = parseInt(element[0].riesgos4);

    riesgo1 === 1
      ? (document.getElementById("radioSi7").checked = true)
      : (document.getElementById("radioNo7").checked = true);

    riesgo2 === 1
      ? (document.getElementById("radioSi8").checked = true)
      : (document.getElementById("radioNo8").checked = true);

    riesgo3 === 1
      ? (document.getElementById("radioSi9").checked = true)
      : (document.getElementById("radioNo9").checked = true);

    riesgo4 === 1
      ? (document.getElementById("radioSi10").checked = true)
      : (document.getElementById("radioNo10").checked = true);

    document.getElementById("riesgos1porque").value = element[0].riesgos1porque;
    document.getElementById("riesgos2porque").value = element[0].riesgos2porque;
    document.getElementById("riesgos3porque").value = element[0].riesgos3porque;
    document.getElementById("riesgos4porque").value = element[0].riesgos4porque;
    document.getElementById("sistemagestion").value = element[0].sistemagestion;
    Tools.llnarslc(
      "CatalogoSeguridad",
      "sistemaGestionsub&tipoelemento=" + element[0].sistemagestion,
      "sistemagestionsub",
      0
    ).then(() => {
      document.getElementById("sistemagestionsub").value =
        element[0].sistemagestionsub;
    });
    document.getElementById("sistemagestionporque").value =
      element[0].sistemagestionporque;

    btnSaveEtapa5.classList.remove("bg-target");
    btnSaveEtapa5.classList.add("btn-warning");
    btnSaveEtapa5.innerHTML =
      '<i class="fa-solid fa-pen-to-square"></i> Actualizar';
  });
};
// Etapa 6 evaluacion

const sistemagestion = document.getElementById("sistemagestion");
const sistemagestionsub = document.getElementById("sistemagestionsub");
const sistemagestionporque = document.getElementById("sistemagestionporque");
const modaletapa6 = document.getElementById("modaletapa6");

modaletapa6.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = modaletapa6.querySelector(".modal-title");
  folioenc = recipient;
  modalTitle.textContent =
    "Elemento del sistema de gestion EHS que debe ser mejorado";
  Incidenciaobj.tblEtapa6(recipient);
});

btnSaveEtapa6.addEventListener("click", (e) => {
  e.preventDefault();
  Incidenciaobj.saveEtapa6(
    idEtapa6.value,
    sistemagestion.value,
    sistemagestionsub.value,
    sistemagestionporque.value,
    folioenc
  ).then(() => {
    btnSaveEtapa6.classList.remove("btn-warning");
    btnSaveEtapa6.classList.add("bg-target");
    btnSaveEtapa6.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>';
    idEtapa6.value = "";
    Incidenciaobj.tblEtapa6(folioenc);
    document.getElementById("idEtapa6").value = "";
    document.getElementById("sistemagestion").value = "";
    document.getElementById("sistemagestionsub").value = "";
    document.getElementById("sistemagestionporque").value = "";
  });
});

window.editEtapa6 = (id) => {
  Incidenciaobj.editEtapaSeis(id).then((element) => {
    document.getElementById("idEtapa6").value = element[0].id;
    document.getElementById("sistemagestion").value = element[0].sistemagestion;
    Tools.llnarslc(
      "CatalogoSeguridad",
      "sistemaGestionsub&tipoelemento=" + element[0].sistemagestion,
      "sistemagestionsub",
      0
    ).then(() => {
      document.getElementById("sistemagestionsub").value =
        element[0].sistemagestionsub;
    });
    document.getElementById("sistemagestionporque").value =
      element[0].sistemagestionporque;

    btnSaveEtapa6.classList.remove("bg-target");
    btnSaveEtapa6.classList.add("btn-warning");
    btnSaveEtapa6.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>';
  });
};

// ----------------- ETAPA 7 ---------------------
const modaletapa7 = document.getElementById("modaletapa7");
const noempEtapa7 = document.getElementById("noempEtapa7");

modaletapa7.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = modaletapa7.querySelector(".modal-title");
  folioenc = recipient;
  modalTitle.textContent = "Elaboracion del reporte de investigación";
  Incidenciaobj.tblEtapa7(recipient);
});

document.getElementById("noempEtapa7").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(
    e.target.value,
    "nombreEtapa7",
    "areaEtapa7",
    "puestoEtapa7"
  );
});

btnSaveEtapa7.addEventListener("click", (e) => {
  e.preventDefault();
  Incidenciaobj.saveEtapa7(idEtapa7.value, noempEtapa7.value, folioenc).then(
    () => {
      Incidenciaobj.tblEtapa7(folioenc);
      btnSaveEtapa7.classList.remove("btn-warning");
      btnSaveEtapa7.classList.add("bg-target");
      btnSaveEtapa7.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>';
      document.getElementById("noempEtapa7").value = "";
      document.getElementById("nombreEtapa7").value = "";
      document.getElementById("areaEtapa7").value = "";
      document.getElementById("puestoEtapa7").value = "";
    }
  );
});

window.editEtapa7 = (id) => {
  Incidenciaobj.editEtapaSiete(id).then((element) => {
    document.getElementById("idEtapa7").value = element[0].id;
    document.getElementById("noempEtapa7").value = element[0].noemp;
    document.getElementById("nombreEtapa7").value = element[0].Nombre;
    document.getElementById("areaEtapa7").value = element[0].departamento;
    document.getElementById("puestoEtapa7").value = element[0].puesto;
    btnSaveEtapa7.classList.remove("bg-target");
    btnSaveEtapa7.classList.add("btn-warning");
    btnSaveEtapa7.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>';
  });
};

// ----------------- ETAPA 8 ---------------------
const modaletapa8 = document.getElementById("modaletapa8");
const noEmpEtapa8 = document.getElementById("noEmpEtapa8");
const tipoEvalua = document.getElementById("tipoEvalua");

modaletapa8.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = modaletapa8.querySelector(".modal-title");
  folioenc = recipient;
  modalTitle.textContent = "Revisión del reporte de investigación";
  Incidenciaobj.tblEtapa8(recipient);
});

Tools.llnarslc("CatalogoSeguridad", "TipoEvaluador", "tipoEvalua", 0);

document.getElementById("noEmpEtapa8").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "nombreEtapa8", "puestoEtapa8", "");
});

btnSaveEtapa8.addEventListener("click", (e) => {
  e.preventDefault();
  Incidenciaobj.saveEtapa8(
    idEtapa8.value,
    noEmpEtapa8.value,
    tipoEvalua.value,
    folioenc
  ).then(() => {
    Incidenciaobj.tblEtapa8(folioenc);
    btnSaveEtapa8.classList.remove("btn-warning");
    btnSaveEtapa8.classList.add("bg-target");
    btnSaveEtapa8.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>';
    document.getElementById("noEmpEtapa8").value = "";
    document.getElementById("nombreEtapa8").value = "";
    document.getElementById("puestoEtapa8").value = "";
    document.getElementById("tipoEvalua").value = "";
  });
});

window.editEtapa8 = (id) => {
  Incidenciaobj.editEtapaOcho(id).then((element) => {
    btnSaveEtapa8.classList.remove("bg-target");
    btnSaveEtapa8.classList.add("btn-warning");
    btnSaveEtapa8.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>';
    document.getElementById("idEtapa8").value = element[0].id;
    document.getElementById("noEmpEtapa8").value = element[0].noemp;
    document.getElementById("nombreEtapa8").value = element[0].Nombre;
    document.getElementById("puestoEtapa8").value = element[0].departamento;
    document.getElementById("tipoEvalua").value = element[0].tipo;
  });
};

// ------ BOTONES PARA LIMPIAR LOS FORMULARIOS
btnLimpiarEtapa3.addEventListener("click", (e) => {
  e.preventDefault();
  document.getElementById("saveetapa3").classList.remove("btn-warning");
  document.getElementById("saveetapa3").classList.add("bg-target");
  document.getElementById("saveetapa3").innerHTML =
    '<i class="fa-solid fa-floppy-disk"></i> Guardar';

  document.getElementById("folioetap3").value = "";
  document.getElementById("eventosprev").value = "";
  document.getElementById("eventofalla").value = "";
  document.getElementById("equipos").value = "";
  document.getElementById("operacion").value = "";
  document.getElementById("producto").value = "";
  document.getElementById("material").value = "";
  document.getElementById("otro").value = "";
  document.getElementById("otroexplique").value = "";
  document.getElementById("descp1").value = "";
  document.getElementById("responsable1").value = "";
  document.getElementById("fechaimp1").value = "";
  document.getElementById("descp2").value = "";
  document.getElementById("responsable2").value = "";
  document.getElementById("fechaimp2").value = "";
  document.getElementById("descp3").value = "";
  document.getElementById("responsable3").value = "";
  document.getElementById("fechaimp3").value = "";
  document.getElementById("responsablenombre1").value = "";
  document.getElementById("responsablenombre2").value = "";
  document.getElementById("responsablenombre3").value = "";
  document.getElementById("noReporteEtapa3").value = "";
});

btnLimpiarEtapa4.addEventListener("click", (e) => {
  e.preventDefault();
  btnSaveEtapa4.classList.remove("btn-warning");
  btnSaveEtapa4.classList.add("bg-target");
  btnSaveEtapa4.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar';
  document.getElementById("noReporteEtapa4").value = "";
  document.getElementById("folioetapa4").value = "";
  document.getElementById("comportamiento").value = "";
  document.getElementById("causainmediata").value = "";
  document.getElementById("porquecausa").value = "";
  document.getElementById("causabasica").value = "";
  document.getElementById("1porque").value = "";
  document.getElementById("causaraiz").value = "";
  document.getElementById("causaraiz").value = "";
  document.getElementById("porqueraiz").value = "";
  document.getElementById("accioncorrectiva").value = "";
  document.getElementById("responsableetapa4").value = "";
  document.getElementById("responsablenombreetapa4").value = "";
  document.getElementById("fechaac").value = "";
  document.getElementById("accioncorrectiva2").value = "";
  document.getElementById("responsableetapa42").value = "";
  document.getElementById("responsablenombreetapa42").value = "";
  document.getElementById("fechaac2").value = "";
  document.getElementById("accioncorrectiva3").value = "";
  document.getElementById("responsableetapa43").value = "";
  document.getElementById("responsablenombreetapa43").value = "";
  document.getElementById("fechaac3").value = "";
  document.getElementById("accioncorrectiva4").value = "";
  document.getElementById("responsableetapa44").value = "";
  document.getElementById("responsablenombreetapa44").value = "";
  document.getElementById("fechaac4").value = "";
  document.getElementById("accioncorrectiva5").value = "";
  document.getElementById("responsableetapa45").value = "";
  document.getElementById("responsablenombreetapa45").value = "";
  document.getElementById("fechaac5").value = "";
});

btnLimpiarEtapa5.addEventListener("click", (e) => {
  e.preventDefault();
  btnSaveEtapa5.classList.remove("btn-warning");
  btnSaveEtapa5.classList.add("bg-target");
  btnSaveEtapa5.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar';
  idEtapa5.value = "";

  // Descarmar todos los radio buttons
  const radios = document.querySelectorAll('input[type="radio"]');
  radios.forEach((radio) => {
    radio.checked = false;
  });

  // Desmarcar todos los checkboxes
  const checkboxes = document.querySelectorAll('input[type="checkbox"]');
  checkboxes.forEach((checkbox) => {
    checkbox.checked = false;
  });

  document.getElementById("riesgos1porque").value = "";
  document.getElementById("riesgos2porque").value = "";
  document.getElementById("riesgos3porque").value = "";
  document.getElementById("riesgos4porque").value = "";
  document.getElementById("sistemagestion").value = "";
  document.getElementById("sistemagestionsub").value = "";
  document.getElementById("sistemagestionporque").value = "";
});

btnLimpiarEtapa6.addEventListener("click", (e) => {
  e.preventDefault();
  btnSaveEtapa6.classList.remove("btn-warning");
  btnSaveEtapa6.classList.add("bg-target");
  btnSaveEtapa6.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>';
  idEtapa6.value = "";
  document.getElementById("idEtapa6").value = "";
  document.getElementById("sistemagestion").value = "";
  document.getElementById("sistemagestionsub").value = "";
  document.getElementById("sistemagestionporque").value = "";
});

btnLimpiarEtapa7.addEventListener("click", (e) => {
  e.preventDefault();
  btnSaveEtapa7.classList.remove("btn-warning");
  btnSaveEtapa7.classList.add("bg-target");
  btnSaveEtapa7.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>';
  idEtapa7.value = "";
  document.getElementById("noempEtapa7").value = "";
  document.getElementById("nombreEtapa7").value = "";
  document.getElementById("areaEtapa7").value = "";
  document.getElementById("puestoEtapa7").value = "";
});

btnLimpiarEtapa8.addEventListener("click", (e) => {
  e.preventDefault();
  btnSaveEtapa8.classList.remove("btn-warning");
  btnSaveEtapa8.classList.add("bg-target");
  btnSaveEtapa8.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>';
  document.getElementById("noEmpEtapa8").value = "";
  document.getElementById("nombreEtapa8").value = "";
  document.getElementById("puestoEtapa8").value = "";
  document.getElementById("tipoEvalua").value = "";
});
