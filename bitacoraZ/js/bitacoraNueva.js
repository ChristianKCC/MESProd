import { Toolsjs } from "../../Tools/Tools.js";
import { Tiempos } from "./sectionctrolTiemposOldN.js";
import { BitTiempos } from "./sectionCtrolTiemposNuevo.js";
import { BitInspeccion } from "./sectionInspeccion.js";
import { PlanProducc } from "../modules/Bitacora.js";

const TiemposObjNuevo = new Tiempos();
const BitTiemposObjNuevo = new BitTiempos();
const BitInspeccionObj = new BitInspeccion();
const Tools = new Toolsjs();
const PlanProduccObj = new PlanProducc();
let intervaloSesion; // para TurnoVale y checkSession
let intervaloParos; // para tblParos cada 10 segundos
let horasTurno = 0;
let horasTurnoTrabajadas = 0;
const horasPorTurno = {
  1: 8,
  2: 7.5,
  3: 8.5,
};

class Bitacorastart {
  async abrirturno() {
    const respuestaraw = await fetch("./php/bitacora.php?abreturno");
    const respuesta = await respuestaraw.json();

    document.getElementById("folio").value = respuesta[0].id;
    document.getElementById("folioenctext").textContent = respuesta[0].id;
    document.getElementById("turnoenctext").textContent = respuesta[0].turno;
    obtenerTurnoYHoras(respuesta[0].turno, respuesta[0].horasTrabajadas);
    horasTurno = obtenerTurnoYHoras(
      respuesta[0].turno,
      respuesta[0].horasTrabajadas
    );
  }

  // Solo para volver al turno anterior sin credenciales
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
    const folioTurnoAnterior = respuesta[0].id;
    document.getElementById("folioenctext").textContent = respuesta[0].id;
    document.getElementById("turnoenctext").textContent = respuesta[0].turno;
    obtenerTurnoYHoras(respuesta[0].turno, respuesta[0].horasTrabajadas);
    BitTiemposObjNuevo.tblParos(folioTurnoAnterior);
    return folioTurnoAnterior;
  }

  async checkSession() {
    let response = await fetch("../Session/sessioncheck.php");
    let data = await response.json();
    if (data.status === "expired") window.location.href = "../login.php";
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

  async actualizarHorasTurno(folio, horas) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("horas", horas);

    const dataPromise = await fetch("./php/bitacora.php?actualizarHoras", {
      method: "POST",
      body: data,
    });
    dataPromise.status === 500 &&
      console.log("Hay un error en la base de datos");
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

  // TiemposObjNuevo.tblctrltiempos(folio);
  TiemposObjNuevo.tblInfoParos(folio);
  // BitTiemposObjNuevo.tblParos(folio);

  Bitacorastart.actualizarHorasTurno(folio, horasTurno);

  BitInspeccionObj.tblInspeccion(folio, "tblinspeccions");
});
document.getElementById("btnreporte").addEventListener("click", (e) => {
  e.preventDefault();
  reporteObj.inicia(folio);
});

const exampleModal = document.getElementById("modalsanitizacionNuevo");
exampleModal.addEventListener("show.bs.modal", function (event) {
  // TiemposObj.limpiar();
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  console.log(recipient);
  const modalTitle = exampleModal.querySelector(".modal-title");
  const modalBodyInput = exampleModal.querySelector(".modal-body input");
  modalTitle.textContent = "Sanitización con folio " + recipient;
  modalBodyInput.value = recipient;
  TiemposObjNuevo.infoSanitizacion(recipient);
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
  document.querySelector("#tblempsanNuevo tbody").appendChild(nuevaFila);
  document.getElementById("noempsanitizacion").value = "";
  document.getElementById("nombresanitizacion").value = "";
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

const modalNuevoParo = new bootstrap.Modal(
  document.getElementById("modalNuevoParo")
);

document.getElementById("crearNuevoParo").addEventListener("click", (e) => {
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
      const folio = document.getElementById("folio").value;
      const seccion = document.getElementById("seccionNuevoParo").value;
      const modulo = document.getElementById("moduloNuevoParo").value;
      const cortes = document.getElementById("cortesNuevoParo").value;
      const rechazos = document.getElementById("rechazosNuevoParo").value;
      const tiempoparo = document.getElementById("tiempoParoNuevoParo").value;
      const hora = document.getElementById("horaNuevoParo").value;
      const motivo = document.getElementById("motivosNuevoParo").value;
      const correccion = document.getElementById("correccionNuevoParo").value;
      const usuario = document.getElementById("usuarioNuevoParo").value;
      const password = document.getElementById("passwordNuevoParo").value;

      BitTiemposObj.crearNuevoParo(
        folio,
        seccion,
        modulo,
        cortes,
        rechazos,
        tiempoparo,
        hora,
        motivo,
        correccion,
        usuario,
        password
      ).then((res) => {
        if (res === false) return false;

        document.getElementById("seccionNuevoParo").value = "";
        document.getElementById("moduloNuevoParo").value = "";
        document.getElementById("cortesNuevoParo").value = "";
        document.getElementById("rechazosNuevoParo").value = "";
        document.getElementById("tiempoParoNuevoParo").value = "";
        document.getElementById("horaNuevoParo").value = "";
        document.getElementById("motivosNuevoParo").value = "";
        document.getElementById("correccionNuevoParo").value = "";
        document.getElementById("usuarioNuevoParo").value = "";
        document.getElementById("passwordNuevoParo").value = "";

        modalNuevoParo.hide();
        BitTiemposObj.tblParos(folio);
      });
    }
  });
});

window.eliminarParo = (idParo) => {
  const folio = document.getElementById("folio").value;
  Swal.fire({
    title: "¿Estás seguro?",
    text: "Una vez eliminado, no podrás recuperar este registro.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, eliminar",
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Ingrese sus credenciales",
        html:
          '<input id="usuarioEliminarParo" type="password" class="swal2-input" placeholder="Usuario">' +
          '<input id="passwordEliminarParo" type="password" class="swal2-input" placeholder="Contraseña">',
        showCancelButton: true,
        confirmButtonText: "Eliminar",
        preConfirm: () => {
          const usuario = document.getElementById("usuarioEliminarParo").value;
          const password = document.getElementById(
            "passwordEliminarParo"
          ).value;
          if (!usuario || !password) {
            Swal.showValidationMessage("Debe ingresar usuario y contraseña");
            return false;
          }
          return { usuario, password };
        },
      }).then((result2) => {
        if (result2.isConfirmed) {
          BitTiemposObj.eliminarParo(
            idParo,
            result2.value.usuario,
            result2.value.password
          ).then((res) => {
            if (res === false) return false;
            BitTiemposObj.tblParos(folio);
          });
        }
      });
    }
  });
};

// Obtener el turno y las horas trabajadas del mismo

function obtenerTurnoYHoras(turno, horasTrabajadas) {
  const horasMaximas = horasPorTurno[turno] || 0;
  const horasInput = document.getElementById("horaNuevoParo");

  if (horasTrabajadas !== null) {
    horasInput.value = horasTrabajadas;
    horasInput.max = horasMaximas;
  } else {
    horasInput.value = horasMaximas;
    horasInput.max = horasMaximas;
  }

  return horasInput.value;
}

document.getElementById("horaNuevoParo").addEventListener("blur", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const horasTurnoActu = document.getElementById("horaNuevoParo").value;
  Bitacorastart.actualizarHorasTurno(folio, horasTurnoActu);
});

document
  .getElementById("guardarHorasTrabajadas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const horasTurnoActu = document.getElementById("horaNuevoParo").value;
    Bitacorastart.actualizarHorasTurno(folio, horasTurnoActu).then(() => {
      Swal.fire({
        icon: "success",
        title: "Exito",
        text: "Se han guardado las horas trabajadas correctamente.",
        timer: 2000,
        showConfirmButton: false,
      });
    });
  });
