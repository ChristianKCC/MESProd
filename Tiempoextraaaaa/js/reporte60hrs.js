import { Toolsjs } from "../../Tools/Tools.js";
const tools = new Toolsjs();
tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "departamento", 0);

class Reporte60 {
  async consulta() {
    // Tomar valores del formulario
    const fechai = document.getElementById("fechai").value;
    const fechaf = document.getElementById("fechaf").value;
    const turno = document.getElementById("turno").value;
    const departamento = document.getElementById("departamento").value;

    // Construir query string dinámico
    const url = `./php/index.php?getDataReporte&fechai=${fechai}&fechaf=${fechaf}&turno=${turno}&departamento=${departamento}`;

    const respuestaRaw = await fetch(url);
    const respuesta = await respuestaRaw.json();

    let body = "";

    if (Array.isArray(respuesta) && respuesta.length > 0) {
      respuesta.forEach((row) => {
        let horasReglamentarias = 0;
        let turnoTexto = "No hay turno registrado";

        switch (row.turnoAsignado) {
          case "turno1":
            horasReglamentarias = 48;
            turnoTexto = "Turno 1";
            break;
          case "turno2":
            horasReglamentarias = 45;
            turnoTexto = "Turno 2";
            break;
          case "turno3":
            horasReglamentarias = 51;
            turnoTexto = "Turno 3";
            break;
          case "turno3_12hrs":
            horasReglamentarias = 51;
            turnoTexto = "Turno 3 (12 hrs)";
            break;
          case "turno1_12hrs":
            horario_desde.value = "07:00:00";
            horario_hasta.value = "19:00:00";
            break;
          case "turno2_12hrs":
            horasReglamentarias = 45;
            turnoTexto = "Turno 2 (12 hrs)";
            break;
          case "mixto1":
            horasReglamentarias = 48;
            turnoTexto = "Mixto 1";
            break;
          case "mixto2":
            horasReglamentarias = 48;
            turnoTexto = "Mixto 2";
            break;
          case "mixto3":
            horasReglamentarias = 48;
            turnoTexto = "Mixto 3";
            break;
          case "mixto4":
            horasReglamentarias = 48;
            turnoTexto = "Mixto 4";
            break;
        }

        const horasRegl =
          horasReglamentarias || "No hay identificación de turno";
        // Horas por folio
        const horasExtras = row.totalHorasExtrasSolicitadas ?? 0;
        // Horas individuales
        const horasIndividuales = row.horasExtrasRegistro ?? 0;
        const horasTotales =
          parseFloat(horasReglamentarias) + parseFloat(horasIndividuales);

        let tipoRegistro = "";
        if (row.esDoblete == 1 && horasTotales >= 60.5) {
          tipoRegistro =
            "<span class='badge bg-primary text-white'>≥ 60.5 hrs y Doblete</span>";
        } else if (row.esDoblete == 1) {
          tipoRegistro =
            "<span class='badge bg-warning text-dark'>Doblete</span>";
        } else if (horasTotales >= 60.5) {
          tipoRegistro = "<span class='badge bg-success'>≥ 60.5 hrs</span>";
        } else {
          tipoRegistro =
            "<span class='badge bg-secondary text-dark'>Otro</span>";
        }

        body += `
            <tr>
                <td>${row.id}</td>
                <td>${row.folio}</td>
                <td>${row.noemp}</td>
                <td>${row.departamento ?? ""}</td>
                <td>${row.nombre ?? ""}</td>
                <td>${row.fecha}</td>
                <td>${row.horaInicioTurnoExtra}</td>
                <td>${row.horaFinTurnoExtra}</td>
                <td>${turnoTexto}</td>
                <td>${horasRegl} hrs</td>
                <td>${parseFloat(row.totalHorasExtrasSolicitadas).toFixed(2)} hrs (folio)</td>
                <td>${parseFloat(row.horasExtrasRegistro).toFixed(2)} hrs (registro)</td>                
                <td>${horasTotales} hrs</td>
                <td>${tipoRegistro}</td>
            </tr>
        `;
      });
    } else {
      body = `
                <tr>
                    <td colspan="14" style="text-align:center; color:red;">
                        No se encontraron registros con los filtros seleccionados
                    </td>
                </tr>
            `;
    }

    document.getElementById("tablaReporte").innerHTML = body;
  }
}

window.Reporte60 = new Reporte60();

// Botón Buscar
document.getElementById("consultar").addEventListener("click", (e) => {
  e.preventDefault();

  document.getElementById("loader").style.display = "flex";
  document.getElementById("content").style.display = "none";

  window.Reporte60.consulta()
    .then(() => {
      document.getElementById("loader").style.display = "none";
      document.getElementById("content").style.display = "block";
    })
    .catch(() => {
      document.getElementById("loader").style.display = "none";
    });
});

// Botón Reiniciar
document.getElementById("reiniciar").addEventListener("click", () => {
  document.getElementById("tablaReporte").innerHTML = "";
});

document.getElementById("exportexcel").addEventListener("click", (e) => {
  e.preventDefault();
  tools.exportartablaexcel("tablaReporteExc");
});
