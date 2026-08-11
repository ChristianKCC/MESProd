import { Toolsjs } from "../../Tools/Tools.js";
const Herramientas = new Toolsjs();
class ReporteBitacora {
  inicia() {
    Herramientas.llnarslc("CatalogoPersonal", "GetSlcMaquinas", "maquinas", 0);
    async function tblasistencias(form) {
      const [
        respuestarawasi,
        respuestarawcom,
        respuestarawctrl,
        respuestarawpres,
        respuestarawcorr,
      ] = await Promise.all([
        fetch("../bitacora/php/Reportebituser.php?tblasistenciasbitrep", {
          method: "POST",
          body: form,
        }),
        fetch("../bitacora/php/Reportebituser.php?tblcomentariosbitrep", {
          method: "POST",
          body: form,
        }),
        fetch("../bitacora/php/Reportebituser.php?tblParosAutomaticos", {
          method: "POST",
          body: form,
        }),
        fetch("../bitacora/php/Reportebituser.php?tblpresentacionesbitrep", {
          method: "POST",
          body: form,
        }),
        fetch("../bitacora/php/Reportebituser.php?tblcorrugadosbitrep", {
          method: "POST",
          body: form,
        }),
      ]);
      const respuestaasi = await respuestarawasi.json();
      const respuestacom = await respuestarawcom.json();
      const respuestactrl = await respuestarawctrl.json();
      const respuesta = await respuestarawpres.json();
      const respuestacorr = await respuestarawcorr.json();

      console.log(respuestacorr);
      let body = "";
      respuestaasi.forEach((element) => {
        body += `<tr>
                  <td>${element.folio}</td>
                  <td>${element.turno}</td>
                  <td>${element.noemp}</td>
                  <td>${element.nombre}</td>
                  <td>${element.puesto}</td>
                </tr>`;
      });
      document.getElementById("tblasistenciasbot").innerHTML = body;

      body = "";
      respuestacom.forEach((element) => {
        body += `<tr>
                  <td>${element.folio}</td>
                  <td>${element.turno}</td>
                  <td>${element.seguridad}</td>
                  <td>${element.calidad}</td>
                  <td>${element.oyl}</td>
                  <td>${element.pendientes}</td>
                  <td>${element.otros}</td>
                </tr>`;
      });
      document.getElementById("tblcomentariosbitrep").innerHTML = body;

      body = "";
      respuesta.forEach((element) => {
        body += `<tr>
                  <td>${element.presentacion}</td>
                  <td>${element.turno}</td>
                  <td>${element.hora}</td>
                  <td>${element.real}</td>
                  <td>${element.acumulado}</td>
                  <td>${element.std}</td>
                  <td>${element.golpes}</td>
                  <td>${element.merma}</td>
                </tr>`;
      });
      document.getElementById("tblpresentacionesbitrep").innerHTML = body;

      body = "";
      respuestactrl.forEach((element) => {
        body += `<tr>
                  <td>${element.folio}</td>
                  <td>${element.fecha}</td>
                  <td>${element.horaInicio}</td>
                  <td>${element.TiempoParo}</td>
                  <td>${element.Seccion ?? ""}</td>
                  <td>${element.Modulo ?? ""}</td>
                  <td>${element.Cortes}</td>
                  <td>${element.Rechazos}</td>
                  <td>${element.Motivo}</td>
                  <td>${element.Correccion}</td>`;
      });
      document.getElementById("tblctrltiemposbitrep").innerHTML = body;

      body = "";
      respuestacorr.forEach((element) => {
        body += `<tr>
                  <td>${element.folio}</td>
                  <td>${element.turno}</td>
                  <td>${element.crecibidas}</td>
                  <td>${element.calmacen}</td>
                  <td>${element.cproducidas}</td>
                  <td>${element.centregadas}</td>
                  <td>${element.claveproducto}</td>
                </tr>`;
      });
      document.getElementById("tblcorrugadosbitrep").innerHTML = body;
    }

    document
      .getElementById("greporte")
      .addEventListener("click", function (event) {
        event.preventDefault();
        let fechai = document.getElementById("fechai").value;
        let fechaf = document.getElementById("fechaf").value;
        let turno = document.getElementById("turno").value;
        let maquinas = document.getElementById("maquinas").value;
        if (fechai == "" || maquinas == "") {
          alert("La fecha y la maquina es obligatorio");
          return false;
        }
        const form = new FormData();
        form.append("fechai", fechai);
        form.append("fechaf", fechaf);
        form.append("turno", turno);
        form.append("maquinas", maquinas);
        tblasistencias(form);
      });
  }
}
ReporteBitacora = new ReporteBitacora();
ReporteBitacora.inicia();
document.getElementById("excelRep").addEventListener("click", function (e) {
  e.preventDefault();
  Herramientas.exportartablaexcel("excelrep");
});
