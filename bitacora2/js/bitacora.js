import { Toolsjs } from "../../Tools/Tools.js";
import { BitCalidad } from "./sectioncalidad.js";
import { BitAsistencias } from "./sectionasistencias.js";
import { BitCorrugados } from "./sectioncorrugados.js";
import { ReporteBitacora } from "./sectionreporte.js";
import { Tiempos } from "./sectionctrolTiemposOld.js";
import { BitPresentaciones } from "./sectionpresentaciones.js";
import { Comentarios } from "./sectioncomentarios.js";
import { BitTiempos } from "./sectionCtrolTiempos.js";
import { BitInspeccion } from "./sectionInspeccion.js";
import { PlanProducc } from "../modules/Bitacora.js";
import { RegistroCOV } from "../modules/Bitacora.js";

const Calidad = new BitCalidad();
const Asistencias = new BitAsistencias();
const Corrugados = new BitCorrugados();
const reporteObj = new ReporteBitacora();
const TiemposObj = new Tiempos();
const Presentaciones = new BitPresentaciones();
const ComentariosObj = new Comentarios();
const BitTiemposObj = new BitTiempos();
const BitInspeccionObj = new BitInspeccion();
const Tools = new Toolsjs();
const PlanProduccObj = new PlanProducc();
const RegistroCOVObj = new RegistroCOV();
let intervaloSesion;
let intervaloParos;

class Bitacorastart {
  async abrirturno() {
    const respuestaraw = await fetch("./php/bitacora.php?abreturno");
    const respuesta = await respuestaraw.json();
    document.getElementById("folio").value = respuesta[0].id;
    document.getElementById("folioenctext").textContent = respuesta[0].id;
    document.getElementById("turnoenctext").textContent = respuesta[0].turno;
  }
  // async turnoanterior(fecha, turno) {
  // // , usuario, password Para agregar a los parametros despues 
  //   const data = new FormData();
  //   data.append("fecha", fecha);
  //   data.append("turno", turno);
  //   // data.append("usuario", parseInt(usuario));
  //   // data.append("password", password);

  //   const respuestaraw = await fetch("./php/bitacora.php?turnoanterior", {
  //     method: "POST",
  //     body: data,
  //   });

  //   const respuesta = await respuestaraw.json();

  //     document.getElementById("folio").value = respuesta[0].id;
  //     const folioTurnoAnterior = (document.getElementById("folio").value =
  //       respuesta[0].id);
  //     document.getElementById("folioenctext").textContent = respuesta[0].id;
  //     document.getElementById("turnoenctext").textContent = respuesta[0].turno;
  //     BitTiemposObj.tblParos(folioTurnoAnterior);

  //   // if (respuesta.error) {
  //   //   Swal.fire({
  //   //     icon: "error",
  //   //     title: "Credenciales inválidas",
  //   //     text: "No tienes acceso para ver esta información.",
  //   //   });
  //   //   return null;
  //   // } else {
  //   //   document.getElementById("folio").value = respuesta[0].id;
  //   //   const folioTurnoAnterior = (document.getElementById("folio").value =
  //   //     respuesta[0].id);
  //   //   document.getElementById("folioenctext").textContent = respuesta[0].id;
  //   //   document.getElementById("turnoenctext").textContent = respuesta[0].turno;
  //   //   BitTiemposObj.tblParos(folioTurnoAnterior);
  //   //   return folioTurnoAnterior;
  //   // }
  // }

  async turnoanterior(fecha, turno) {
    const data = new FormData();
    data.append("fecha", fecha);
    data.append("turno", turno);
    const respuestaraw = await fetch("./php/bitacora.php?turnoanterior", {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    document.getElementById("folio").value = respuesta[0].id;
    const folioTurnoAnterior = (document.getElementById("folio").value = respuesta[0].id);
    document.getElementById("folioenctext").textContent = respuesta[0].id;
    document.getElementById("turnoenctext").textContent = respuesta[0].turno;
    BitTiemposObj.tblParos(folioTurnoAnterior);
  }

  async checkSession() {
    let response = await fetch("../Session/sessioncheck.php");
    let data = await response.json();
    if (data.status === "expired") window.location.href = "../login.php";
  }
  async main(folio) {
    await Promise.all([
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion1",
        0
      ).then(() =>
        Presentaciones.tblPresentacionSub(folio, 1, "tblpresentacionsub1")
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion2",
        0
      ).then(() =>
        Presentaciones.tblPresentacionSub(folio, 2, "tblpresentacionsub2")
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion3",
        0
      ).then(() =>
        Presentaciones.tblPresentacionSub(folio, 3, "tblpresentacionsub3")
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion4",
        0
      ).then(() =>
        Presentaciones.tblPresentacionSub(folio, 4, "tblpresentacionsub4")
      ),
    ]);
  }

  async maintelas(folio) {
    await Promise.all([
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion1telas",
        0
      ).then(() =>
        Presentaciones.tblPresentacionSubtelas(
          folio,
          1,
          "tblpresentacionsub1telas"
        )
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion2telas",
        0
      ).then(() =>
        Presentaciones.tblPresentacionSubtelas(
          folio,
          2,
          "tblpresentacionsub2telas"
        )
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion3telas",
        0
      ).then(() =>
        Presentaciones.tblPresentacionSubtelas(
          folio,
          3,
          "tblpresentacionsub3telas"
        )
      ),
    ]);
  }
  TurnoVale() {
    let fecha = new Date();
    let horas = fecha.getHours();
    horas = (horas < 10 ? "0" : "") + horas;
    if (horas >= 7 && horas <= 14) document.getElementById("turnoen").value = 1;
    else if (horas >= 15 && horas <= 21)
      document.getElementById("turnoen").value = 2;
    else if (horas >= 22 || horas <= 6)
      document.getElementById("turnoen").value = 3;
  }
}
let folio = 0;
Bitacorastart = new Bitacorastart();

Bitacorastart.abrirturno().then(() => {
  intervaloSesion = setInterval(() => {
    Bitacorastart.TurnoVale();
    Bitacorastart.checkSession();
  }, 1000);

  folio = document.getElementById("folio").value;
  Calidad.tblCalidadsd(folio, "tblcalidadsd");
  Asistencias.tblasistencias(folio);
  Corrugados.tblcorrugados(folio);
  reporteObj.inicia(folio);
  TiemposObj.tblctrltiempos(folio);
  Bitacorastart.main(folio);
  Bitacorastart.maintelas(folio);
  Presentaciones.tblGolpes(folio);
  ComentariosObj.tblcomentarios(folio);
  BitTiemposObj.tblParos(folio);

  // Actualizar datos de paros cada 10 segundos
  intervaloParos = setInterval(() => {
    BitTiemposObj.tblParos(folio);
  }, 10000);

  BitInspeccionObj.tblInspeccion(folio, "tblinspeccions");
});

document.getElementById("btnreporte").addEventListener("click", (e) => {
  e.preventDefault();
  reporteObj.inicia(folio);
});

// document.getElementById("turnoanterior").addEventListener("click", (e) => {
//   e.preventDefault();
//   Swal.fire({
//     title: "¿Estás seguro?",
//     text: "Cuidado, volveras a otro turno, deberas dar click en turno actual para volver!",
//     icon: "warning",
//     showCancelButton: true,
//     confirmButtonColor: "#3085d6",
//     cancelButtonColor: "#d33",
//     confirmButtonText: "Si, Entendido!",
//   }).then((result) => {
//     if (result.isConfirmed) {
//       clearInterval(intervaloSesion);
//       clearInterval(intervaloParos);

//       const fecha = document.getElementById("fechaturnocambio").value;
//       const turnocambio = document.getElementById("turnocambio").value;
//       const usuarioturnocambio =
//         document.getElementById("usuarioturnocambio").value;
//       const passwordturnocambio = document.getElementById(
//         "passwordturnocambio"
//       ).value;

//       Bitacorastart.turnoanterior(
//         fecha,
//         turnocambio,
//         usuarioturnocambio,
//         passwordturnocambio
//       ).then((folio) => {
//         if (!folio) return;

//         folio = document.getElementById("folio").value;
//         Calidad.tblCalidadsd(folio, "tblcalidadsd");
//         Asistencias.tblasistencias(folio);
//         Corrugados.tblcorrugados(folio);
//         TiemposObj.tblctrltiempos(folio);
//         reporteObj.inicia(folio);
//         Bitacorastart.main(folio);
//         Bitacorastart.maintelas(folio);
//         ComentariosObj.tblcomentarios(folio);
//         Presentaciones.tblGolpes(folio);
//         const modalElement = document.getElementById("ModalTurno");
//         const modalInstance = bootstrap.Modal.getInstance(modalElement);
//         modalInstance.hide();
//         document.getElementById("msjturno").innerHTML =
//           " No estas en el turno actual";
//       });

//       setTimeout(() => {
//         const folioAnterior = document.getElementById("folio").value;
//         RegistroCOVObj.mostrarPesos(folioAnterior);
//       }, 500);
//     }
//   });
// });


document.getElementById("turnoanterior").addEventListener("click", (e) => {
  e.preventDefault();
  Swal.fire({
    title: "¿Estás seguro?",
    text: "Cuidado, volveras a otro turno, deberas dar click en turno actual para volver!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, Entendido!",
  }).then((result) => {
    if (result.isConfirmed) {
      clearInterval(intervaloSesion);
      clearInterval(intervaloParos);
      const fecha = document.getElementById("fechaturnocambio").value;
      const turnocambio = document.getElementById("turnocambio").value;
      Bitacorastart.turnoanterior(fecha, turnocambio).then(() => {
        folio = document.getElementById("folio").value;
        Calidad.tblCalidadsd(folio, "tblcalidadsd");
        Asistencias.tblasistencias(folio);
        Corrugados.tblcorrugados(folio);
        TiemposObj.tblctrltiempos(folio);
        reporteObj.inicia(folio);
        Bitacorastart.main(folio);
        Bitacorastart.maintelas(folio);
        ComentariosObj.tblcomentarios(folio);
        Presentaciones.tblGolpes(folio);
        const modalElement = document.getElementById("ModalTurno");
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        modalInstance.hide();
        document.getElementById("msjturno").innerHTML =
          " No estas en el turno actual";
      });
    }
  });
});

document.getElementById("cerrarturno").addEventListener("click", (e) => {
  e.preventDefault();
  Swal.fire({
    title: "¿Estás seguro?",
    text: "¿Quieres ir al turno actual? ",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, Estoy seguro!",
  }).then((result) => {
    if (result.isConfirmed) {
      Bitacorastart.abrirturno().then(() => {
        location.reload();
        folio != document.getElementById("folio").value
          ? location.reload()
          : Swal.fire({
              title: "Lo siento!",
              text: "Ya estas en el turno actual",
              icon: "warning",
            });
      });
    }
  });
});

// Asistencias
document.getElementById("asistenciasModalid").addEventListener("click", () => {
  Asistencias.limpiar();
});
document
  .getElementById("noempasis")
  .addEventListener("keyup", function (event) {
    event.preventDefault();
    let noemp = document.getElementById("noempasis").value;
    Tools.getDataEmpleado(
      noemp,
      "nombreasis",
      "departamentoasis",
      "puestoasis"
    );
  });

document
  .getElementById("guardarasistencias")
  .addEventListener("click", async function (event) {
    event.preventDefault();
    let folio = document.getElementById("folio").value;
    let noemp = document.getElementById("noempasis").value;
    let nombre = document.getElementById("nombreasis").value;
    let idregconsultado = document.getElementById("idregconsultado").value;
    if (folio == "") {
      alert("Debe haber un folio seleccionado");
      return false;
    } else if (noemp == "" || nombre == "") {
      alert("Debe llenar el campo de numero de empleado");
      return false;
    }
    Asistencias.saveAsistencia(idregconsultado, folio, noemp).then(() => {
      Asistencias.tblasistencias(folio);
    });
  });

window.consultarAsistencia = (param) => Asistencias.consultarAsistencia(param);

// Calidad
document.getElementById("CalidadModalid").addEventListener("click", () => {
  Calidad.limpiar();
});
document
  .getElementById("guardacalidad")
  .addEventListener("click", function (e) {
    e.preventDefault();
    const idcalidad = document.getElementById("idcalidad").value;
    const inspeccionados = document.getElementById("inspeccionados").value;
    const sd = document.getElementById("sd").value;
    const ql = document.getElementById("ql").value;
    const sdobservaciones = document.getElementById("sdobservaciones").value;
    const folio = document.getElementById("folio").value;
    Calidad.savecalidad(
      idcalidad,
      folio,
      inspeccionados,
      sd,
      ql,
      sdobservaciones
    ).then(() => {
      Calidad.tblCalidadsd(folio, "tblcalidadsd");
    });
  });
window.consultarCalidad = (e) => Calidad.consultarCalidadxID(e);

// Corrugados

Tools.llnarslc("CatalogosBitacora", "GetClavesValesE", "claveproducto", 0);
document.getElementById("CorrugadosModalid").addEventListener("click", () => {
  Corrugados.limpiar();
});
document
  .getElementById("guardacorrugados")
  .addEventListener("click", function (event) {
    event.preventDefault();
    let folio = document.getElementById("folio").value;
    let crecibidas = document.getElementById("crecibidas").value;
    let calmacen = document.getElementById("calmacen").value;
    let cproducidas = document.getElementById("cproducidas").value;
    let centregadas = document.getElementById("centregadas").value;
    let claveproducto = document.getElementById("claveproducto").value;
    let idregconsultado = document.getElementById("idregconsultado").value;
    if (folio == "") {
      alert("Debe haber un folio seleccionado");
      return false;
    } else if (
      crecibidas == "" ||
      calmacen == "" ||
      cproducidas == "" ||
      centregadas == ""
    ) {
      alert("Debe llenar todos los campo");
      return false;
    }
    Corrugados.savecorrugados(
      idregconsultado,
      folio,
      crecibidas,
      calmacen,
      cproducidas,
      centregadas,
      claveproducto
    ).then(() => {
      Corrugados.tblcorrugados(folio);
    });
  });

window.consultarCorrugado = (e) => Corrugados.consultarCorrugado(e);

document.getElementById("excelRep").addEventListener("click", function (e) {
  e.preventDefault();
  Herramientas.exportartablaexcel("excelrep");
});

// Tiempos

Tools.llnarslc("CatalogosBitacora", "GetSeccionesTiempos", "seccion", 0);
document.getElementById("seccion").addEventListener("change", function () {
  let seccion = document.getElementById("seccion").value;
  TiemposObj.seccionChg(seccion);
});
document.getElementById("horafinal").addEventListener("blur", function () {
  let horai = document.getElementById("horainicio").value;
  let horaf = document.getElementById("horafinal").value;
  var fecha1 = new Date("2000-01-01T" + horai + ":00Z");
  var fecha2 = new Date("2000-01-01T" + horaf + ":00Z");
  var diferencia = fecha2.getTime() - fecha1.getTime();
  var minutos = Math.floor(diferencia / 1000 / 60);
  document.getElementById("diftiempo").innerHTML = minutos + " minutos";
});
document
  .getElementById("guardarctrltiempos")
  .addEventListener("click", function (event) {
    event.preventDefault();
    let horainicio = document.getElementById("horainicio").value;
    let horafinal = document.getElementById("horafinal").value;
    let operacion = document.getElementById("operacion").value;
    let electrico = document.getElementById("electrico").value;
    let mecanico = document.getElementById("mecanico").value;
    let materias = document.getElementById("materias").value;
    let grado = document.getElementById("grado").value;
    let prev = document.getElementById("prev").value;
    let servicios = document.getElementById("servicios").value;
    let subtotal = document.getElementById("subtotal").value;
    let seccion = document.getElementById("seccion").value;
    let modulo = document.getElementById("modulo").value;
    let motivo = document.getElementById("motivo").value;
    let correccion = document.getElementById("correccion").value;
    let folio = document.getElementById("folio").value;
    let idregconsultado = document.getElementById("idregconsultado").value;
    if (folio == "") {
      alert("Debe haber un folio seleccionado");
      return false;
    } else if (
      horainicio == "" ||
      horafinal == "" ||
      operacion == "" ||
      electrico == "" ||
      mecanico == "" ||
      materias == "" ||
      grado == "" ||
      prev == "" ||
      servicios == "" ||
      seccion == "" ||
      modulo == "" ||
      motivo == "" ||
      correccion == ""
    ) {
      alert("Debe llenar todos los campos");
      return false;
    }
    const form = new FormData();
    form.append("horainicio", horainicio);
    form.append("horafinal", horafinal);
    form.append("operacion", operacion);
    form.append("electrico", electrico);
    form.append("mecanico", mecanico);
    form.append("materias", materias);
    form.append("grado", grado);
    form.append("prev", prev);
    form.append("servicios", servicios);
    form.append("subtotal", subtotal);
    form.append("seccion", seccion);
    form.append("modulo", modulo);
    form.append("motivo", motivo);
    form.append("correccion", correccion);
    form.append("id", idregconsultado);
    form.append("folio", folio);
    if (idregconsultado != "") {
      (async () => {
        const respuestaraw = await fetch(
          "./php/bitacora.php?editarctrltiempos",
          {
            method: "POST",
            body: form,
          }
        );
        const respuesta = await respuestaraw.json();
        TiemposObj.tblctrltiempos();
        document.getElementById("formctrltiempos").reset();
        document.getElementById("idregconsultado").value = "";
        document.getElementById("editando").innerHTML = "";
      })();
      alert("Información actualizada");
      return false;
    }
    (async () => {
      const respuestaraw = await fetch(
        "./php/bitacora.php?guardarctrltiempos",
        {
          method: "POST",
          body: form,
        }
      );
      const respuesta = await respuestaraw.json();
      TiemposObj.tblctrltiempos();
      document.getElementById("formctrltiempos").reset();
    })();
  });
window.consultarCtrlTiempos = (param) => TiemposObj.consultarCtrlTiempos(param);
// Tiempos sanitisacion

const exampleModal = document.getElementById("modalsanitizacion");
exampleModal.addEventListener("show.bs.modal", function (event) {
  TiemposObj.limpiar();
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = exampleModal.querySelector(".modal-title");
  const modalBodyInput = exampleModal.querySelector(".modal-body input");
  modalTitle.textContent = "Sanitización con folio " + recipient;
  modalBodyInput.value = recipient;
});

document.getElementById("noempsanitizacion").addEventListener("keyup", (e) => {
  e.preventDefault();
  Tools.getDataEmpleado(e.target.value, "nombresanitizacion", "", "");
});

document.getElementById("addempsanitizacion").addEventListener("click", (e) => {
  e.preventDefault();
  const noemp = document.getElementById("noempsanitizacion").value;
  const nombre = document.getElementById("nombresanitizacion").value;
  if (nombre === "") {
    alert("No coincide el número de empleado con ningún nombre");
    return false;
  }
  const nuevaFila = document.createElement("tr");
  const celdanoemp = document.createElement("td");
  const celdanombre = document.createElement("td");
  celdanoemp.textContent = noemp;
  celdanombre.textContent = nombre;
  nuevaFila.appendChild(celdanoemp);
  nuevaFila.appendChild(celdanombre);
  nuevaFila.addEventListener("dblclick", function () {
    this.remove();
  });
  document.querySelector("#tblempsan tbody").appendChild(nuevaFila);
  document.getElementById("noempsanitizacion").value = "";
  document.getElementById("nombresanitizacion").value = "";
});
document.getElementById("saveSanitizacion").addEventListener("click", (e) => {
  e.preventDefault();
  const motivo = document.getElementById("motivosanitizacion").value;
  const tiempo = document.getElementById("tiemposanitizacion").value;
  const usuario = document.getElementById("usuariosanitizacion").value;
  const password = document.getElementById("passwordsanitizacion").value;
  const folio = document.getElementById("recipient-name").value;
  TiemposObj.saveSanitizacion(
    folio,
    motivo,
    tiempo,
    usuario,
    password,
    "tblempsan"
  );
});

// Seccion Presetntaciones

window.getCellValue = (e) => {
  Presentaciones.getCellValue(e);
};
window.getCellValueGolpes = (e) => {
  Presentaciones.getCellValueGolpes(e);
};

document.getElementById("savePresentacion1").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const presentacion = document.getElementById("presentacion1").value;
  const turnoen = document.getElementById("turnoenctext").textContent;
  if (presentacion == "") {
    swal.fire("UPS", "Selecciona una clave", "warning");
    return false;
  }
  document.getElementById("presentacion1").disabled = true;
  document.getElementById("savePresentacion1").disabled = true;
  Presentaciones.savePresentacion(folio, presentacion, turnoen, 1).then(() => {
    Presentaciones.tblPresentacionSub(folio, 1, "tblpresentacionsub1");
    Presentaciones.tblGolpes(folio);
  });
});
document.getElementById("savePresentacion2").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const presentacion = document.getElementById("presentacion2").value;
  const turnoen = document.getElementById("turnoenctext").textContent;
  if (presentacion == "") {
    swal.fire("UPS", "Selecciona una clave", "warning");
    return false;
  }
  document.getElementById("presentacion2").disabled = true;
  document.getElementById("savePresentacion2").disabled = true;
  Presentaciones.savePresentacion(folio, presentacion, turnoen, 2).then(() => {
    Presentaciones.tblPresentacionSub(folio, 2, "tblpresentacionsub2");
    Presentaciones.tblGolpes(folio);
  });
});
document.getElementById("savePresentacion3").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const presentacion = document.getElementById("presentacion3").value;
  const turnoen = document.getElementById("turnoenctext").textContent;
  if (presentacion == "") {
    swal.fire("UPS", "Selecciona una clave", "warning");
    return false;
  }
  document.getElementById("presentacion3").disabled = true;
  document.getElementById("savePresentacion3").disabled = true;
  Presentaciones.savePresentacion(folio, presentacion, turnoen, 3).then(() => {
    Presentaciones.tblPresentacionSub(folio, 3, "tblpresentacionsub3");
    Presentaciones.tblGolpes(folio);
  });
});
document.getElementById("savePresentacion4").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const presentacion = document.getElementById("presentacion4").value;
  const turnoen = document.getElementById("turnoenctext").textContent;
  if (presentacion == "") {
    swal.fire("UPS", "Selecciona una clave", "warning");
    return false;
  }
  document.getElementById("presentacion4").disabled = true;
  document.getElementById("savePresentacion4").disabled = true;
  Presentaciones.savePresentacion(folio, presentacion, turnoen, 4).then(() => {
    Presentaciones.tblPresentacionSub(folio, 4, "tblpresentacionsub4");
    Presentaciones.tblGolpes(folio);
  });
});
document.getElementById("resetPresentacion1").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  Presentaciones.DeletePresentacion(folio, 1).then(() => {
    Presentaciones.tblPresentacionSub(folio, 1, "tblpresentacionsub1");
  });
});
document.getElementById("resetPresentacion2").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  Presentaciones.DeletePresentacion(folio, 2).then(() => {
    Presentaciones.tblPresentacionSub(folio, 2, "tblpresentacionsub2");
  });
});
document.getElementById("resetPresentacion3").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  Presentaciones.DeletePresentacion(folio, 3).then(() => {
    Presentaciones.tblPresentacionSub(folio, 3, "tblpresentacionsub3");
  });
});
document.getElementById("resetPresentacion4").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  Presentaciones.DeletePresentacion(folio, 4).then(() => {
    Presentaciones.tblPresentacionSub(folio, 4, "tblpresentacionsub4");
  });
});

// Seccion presentaciones Telas

window.getCellValueTelas = (e) => {
  Presentaciones.getCellValueTelas(e);
};
document
  .getElementById("savePresentacion1telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const presentacion = document.getElementById("presentacion1telas").value;
    const turnoen = document.getElementById("turnoenctext").textContent;
    if (presentacion == "") {
      swal.fire("UPS", "Selecciona una clave", "warning");
      return false;
    }
    document.getElementById("presentacion1telas").disabled = true;
    document.getElementById("savePresentacion1telas").disabled = true;
    Presentaciones.savePresentaciontelas(folio, presentacion, turnoen, 1).then(
      () => {
        Presentaciones.tblPresentacionSubtelas(
          folio,
          1,
          "tblpresentacionsub1telas"
        );
      }
    );
  });
document
  .getElementById("savePresentacion2telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const presentacion = document.getElementById("presentacion2telas").value;
    const turnoen = document.getElementById("turnoenctext").textContent;
    if (presentacion == "") {
      swal.fire("UPS", "Selecciona una clave", "warning");
      return false;
    }
    document.getElementById("presentacion2telas").disabled = true;
    document.getElementById("savePresentacion2telas").disabled = true;
    Presentaciones.savePresentaciontelas(folio, presentacion, turnoen, 2).then(
      () => {
        Presentaciones.tblPresentacionSubtelas(
          folio,
          2,
          "tblpresentacionsub2telas"
        );
      }
    );
  });
document
  .getElementById("savePresentacion3telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const presentacion = document.getElementById("presentacion3telas").value;
    const turnoen = document.getElementById("turnoenctext").textContent;
    if (presentacion == "") {
      swal.fire("UPS", "Selecciona una clave", "warning");
      return false;
    }
    document.getElementById("presentacion3telas").disabled = true;
    document.getElementById("savePresentacion3telas").disabled = true;
    Presentaciones.savePresentaciontelas(folio, presentacion, turnoen, 3).then(
      () => {
        Presentaciones.tblPresentacionSubtelas(
          folio,
          3,
          "tblpresentacionsub3telas"
        );
      }
    );
  });

document
  .getElementById("resetPresentacion1telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    Presentaciones.DeletePresentacionTelas(folio, 1).then(() => {
      Presentaciones.tblPresentacionSubtelas(
        folio,
        1,
        "tblpresentacionsub1telas"
      );
    });
  });
document
  .getElementById("resetPresentacion2telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    Presentaciones.DeletePresentacionTelas(folio, 2).then(() => {
      Presentaciones.tblPresentacionSubtelas(
        folio,
        2,
        "tblpresentacionsub2telas"
      );
    });
  });
document
  .getElementById("resetPresentacion3telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    Presentaciones.DeletePresentacionTelas(folio, 3).then(() => {
      Presentaciones.tblPresentacionSubtelas(
        folio,
        3,
        "tblpresentacionsub3telas"
      );
    });
  });

// Comentarios

document
  .getElementById("guardarcomentarios")
  .addEventListener("click", function (event) {
    event.preventDefault();
    let folio = document.getElementById("folio").value;
    let seguridad = document.getElementById("seguridad").value;
    let calidad = document.getElementById("calidadcom").value;
    let oyl = document.getElementById("oyl").value;
    let pendientes = document.getElementById("pendientes").value;
    let otros = document.getElementById("otros").value;
    let idregconsultado = document.getElementById("idregconsultado").value;
    if (folio == "") {
      alert("Debe haber un folio seleccionado");
      return false;
    } else if (
      seguridad == "" ||
      calidad == "" ||
      oyl == "" ||
      pendientes == ""
    ) {
      alert("Todos los campos son obligatorios");
      return false;
    }
    const form = new FormData();
    form.append("folio", folio);
    form.append("seguridad", seguridad);
    form.append("calidad", calidad);
    form.append("oyl", oyl);
    form.append("pendientes", pendientes);
    form.append("otros", otros);
    form.append("id", idregconsultado);
    if (idregconsultado != "") {
      (async () => {
        const respuestaraw = await fetch(
          "./php/bitacora.php?editarcomentarios",
          {
            method: "POST",
            body: form,
          }
        );
        const respuesta = await respuestaraw.json();
        ComentariosObj.tblcomentarios(folio);
        document.getElementById("formcomentarios").reset();
        document.getElementById("idregconsultado").value = "";
        document.getElementById("editando").innerHTML = "";
      })();
      alert("Información actualizada");
      return false;
    }
    (async () => {
      const respuestaraw = await fetch(
        "./php/bitacora.php?guardarcomentarios",
        {
          method: "POST",
          body: form,
        }
      );
      const respuesta = await respuestaraw.json();
      ComentariosObj.tblcomentarios(folio);
      document.getElementById("formcomentarios").reset();
    })();
  });

window.consultarComentarios = function (param) {
  ComentariosObj.consultarComentarios(param);
};

// Tiempos automatico

Tools.llnarslc(
  "CatalogosBitacora",
  "GetSeccionesTiempos",
  "tiemposSecciones",
  0
);
document
  .getElementById("tiemposSecciones")
  .addEventListener("change", function (e) {
    e.preventDefault();
    Tools.llnarslc(
      "CatalogosBitacora",
      "GetModulosTiempos&seccion=" + e.target.value,
      "TiemposModulo",
      0
    );
  });
document
  .getElementById("TiemposModulo")
  .addEventListener("change", function (e) {
    e.preventDefault();
    // const seccion = document.getElementById('tiemposSecciones').value;
    // Tools.llnarslc('CatalogosBitacora', 'GetFallasParos&seccion=' + seccion + '&modulo=' + e.target.value, 'TiemposFalla', 0);
    document.getElementById("TiemposFalla").innerHTML =
      '<option value="209">Sin llenar</option>';
  });
const modalTiempos = document.getElementById("modalTiempos");
modalTiempos.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = modalTiempos.querySelector(".modal-title");
  BitTiemposObj.getdataxidParos(recipient).then((element) => {
    modalTitle.textContent = "Paro con folio: " + recipient;
    document.getElementById("TiemposParoFolio").value = recipient;
    document.getElementById("tiemposSecciones").value = element[0].seccion;
    Tools.llnarslc(
      "CatalogosBitacora",
      "GetModulosTiempos&seccion=" + element[0].seccion,
      "TiemposModulo",
      0
    ).then(() => {
      document.getElementById("TiemposModulo").value = element[0].modulo;
      // Tools.llnarslc('CatalogosBitacora', 'GetTiemposFallas&seccion=' + element[0].seccion + '&modulo=' + element[0].modulo, 'TiemposFalla', 0).then(() => {
      //     document.getElementById('TiemposFalla').value = element[0].falla;
      // })

      document.getElementById("TiemposFalla").innerHTML =
        '<option value="209">Sin llenar</option>';
    });
    document.getElementById("TiemposCortes").value =
      element[0].cortes === null ? "Sin información" : element[0].cortes;
    document.getElementById("TiemposRechazos").value =
      element[0].rechazos === null ? "Sin información" : element[0].rechazos;
    document.getElementById("Tiempostiempoparo").value =
      element[0].tabajo === null ? 0 : element[0].tabajo;
    document.getElementById("Tiemposhora").value = element[0].hora;
    document.getElementById("Tiemposrechazoscorrida").value =
      element[0].rechazoscorrida === 0 ? 0 : element[0].rechazoscorrida;
    // document.getElementById("Tiemposcomentarios").value =
    //   element[0].comentarios;
    document.getElementById("Tiemposmotivos").value = element[0].motivo;
    document.getElementById("Tiemposcorreccion").value = element[0].correccion;
  });
});
const mymodalTiempos = new bootstrap.Modal(
  document.getElementById("modalTiempos")
);
document
  .getElementById("UpdatedataParo")
  .addEventListener("click", function (e) {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const folioParos = document.getElementById("TiemposParoFolio").value;
    const seccion = document.getElementById("tiemposSecciones").value;
    const modulo = document.getElementById("TiemposModulo").value;
    const falla = document.getElementById("TiemposFalla").value;
    const rechazos = document.getElementById("TiemposRechazos").value;
    const tiempoparo = document.getElementById("Tiempostiempoparo").value;
    const hora = document.getElementById("Tiemposhora").value;
    const rechazoscorrida = document.getElementById(
      "Tiemposrechazoscorrida"
    ).value;
    // const comentarios = document.getElementById("Tiemposcomentarios").value;
    const motivos = document.getElementById("Tiemposmotivos").value;
    const correccion = document.getElementById("Tiemposcorreccion").value;

    BitTiemposObj.updateDataParo(
      folioParos,
      seccion,
      modulo,
      falla,
      rechazos,
      tiempoparo,
      hora,
      rechazoscorrida,
      motivos,
      correccion
    ).then((res) => {
      if (res === false) return false;
      mymodalTiempos.hide();
      BitTiemposObj.tblParos(folio);
    });
  });

// Inspecciones

Tools.llnarslc("CatalogosBitacora", "GetTipoInspeccion", "inspecciontipo", 0);
Tools.llnarslc("CatalogosBitacora", "GetDescSecpreusos", "seccionpreusos", 0);

document.getElementById("noempinsp").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "nombreinsp", "", "");
});
document.getElementById("inspecciontipo").addEventListener("change", (e) => {
  if (e.target.value == "") {
    document.getElementById("inpecciondesc").innerHTML = "";
    document.getElementById("archivopreussos").innerHTML = "";
  } else {
    Tools.llenarCheckbox(
      "CatalogosBitacora",
      "GetDescInspeccion&id=" + e.target.value,
      "inpecciondesc"
    );
    document.getElementById(
      "archivopreussos"
    ).innerHTML = `<embed src="preusos/${e.target.value}.jpg" type="application/pdf" width="100%" height="500px" />`;
  }
});

document.getElementById("saveinsp").addEventListener("click", function (event) {
  event.preventDefault();
  const datos = {
    noempinsp: document.getElementById("noempinsp").value,
    nombreinsp: document.getElementById("nombreinsp").value,
    inspecciontipo: document.getElementById("inspecciontipo").value,
    seccionpreusos: document.getElementById("seccionpreusos").value,
    inpeccionfecha: document.getElementById("inpeccionfecha").value,
    inpeccioncomentarios: document.getElementById("inpeccioncomentarios").value,
    folio: document.getElementById("folio").value,
    inpecciondesc: [],
  };
  const radiosSeleccionados = document.querySelectorAll(
    '#inpecciondesc input[type="radio"]:checked'
  );
  radiosSeleccionados.forEach((radio) => {
    datos.inpecciondesc.push({
      id: radio.name.replace("opcion_", ""),
      valor: radio.value,
    });
  });
  if (BitInspeccionObj.validarSeleccionCompleta("inpecciondesc")) {
    BitInspeccionObj.saveInpeccion(datos).then(() => {
      BitInspeccionObj.tblInspeccion(datos.folio, "tblinspeccions");
      document.getElementById("formibnsp").reset();
      document.getElementById("inpecciondesc").innerHTML = "";
      document.getElementById("archivopreussos").innerHTML = "";
    });
  }
});
document.getElementById("resetinspecciones").addEventListener("click", () => {
  document.getElementById("inpecciondesc").innerHTML = "";
  document.getElementById("archivopreussos").innerHTML = "";
});

document.getElementById("btnPlanProducc").addEventListener("click", (e) => {
  e.preventDefault();
  PlanProduccObj.visualizarPlanProducc("listaPlanProduccion");
});
