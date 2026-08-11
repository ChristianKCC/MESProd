import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();

class ReporteVacaciones {
    reporteVacaciones(){
        Tools.llnarslc('CatalogoPersonal', "GetSlcDeps", "departamento", 0);
    }

    async consulta() {
        let fechai = document.getElementById("fechai").value;
        let fechaf = document.getElementById("fechaf").value;
        let noemp = document.getElementById("noemp").value;
        let folio = document.getElementById("folio").value;
        let departamento = document.getElementById("departamento").value;

        if ((fechai === "" || fechaf === "") && folio === "") {
            Swal.fire('UPS!!!', 'El intervalo de fechas o el folio es obligatorio', 'info');
            return false;
        }

        const data = new FormData();
        data.append("fechai", fechai);
        data.append("fechaf", fechaf);
        data.append("noemp", noemp);
        data.append("folio", folio);
        data.append("departamento", departamento);

        const respuesta = await fetch("php/index.php?reporteVacaciones", {
            method: "POST",
            body: data
        });
        const respuestaraw = await respuesta.json();

        let body = "";
        respuestaraw.forEach(element => {
    let semanas = {};

    if (element.diasCalendario) {
        const diasSemana = ["D","L","M","Mi","J","V","S"];

        element.diasCalendario.forEach(d => {
            const weekNum = getWeekNumber(d.fecha);
            if (!semanas[weekNum]) {
                semanas[weekNum] = {
                    dias: { "L":[], "M":[], "Mi":[], "J":[], "V":[], "S":[], "D":[] },
                    conteo: { "V":0, "F":0, "D":0, "R":0 },
                    primerDia: d.fecha
                };
            }
            const [year, month, day] = d.fecha.split("-").map(Number);
            const fechaObj = new Date(year, month-1, day);
            const diaSemana = diasSemana[fechaObj.getDay()];
            semanas[weekNum].dias[diaSemana].push(d);
            semanas[weekNum].conteo[d.tipo] += 1;
        });
    }

    let estadoClass = "badge bg-warning text-dark";
    let estadoTexto = "En espera";
    
    if (element.autorizado == 1 && element.Vc_revisado == 1 && element.Vc_firmaRI == 1) {
        estadoClass = "badge bg-success";
        estadoTexto = "Aprobado";
    } else if (element.autorizado == 2) {
        estadoClass = "badge bg-danger";
        estadoTexto = "Rechazado";
    }

    for (const semana in semanas) {
        const mesNum = semanas[semana].primerDia.split("-")[1];
        const mesNombre = {
            "01":"ene","02":"feb","03":"mar","04":"abr","05":"may","06":"jun",
            "07":"jul","08":"ago","09":"sep","10":"oct","11":"nov","12":"dic"
        }[mesNum];

        let diasCols = "";
        ["L","M","Mi","J","V","S","D"].forEach(diaSem => {
            diasCols += "<td>";
            semanas[semana].dias[diaSem].forEach(d => {
                let colorStyle = "background-color: #eee; color:#000;";
                if (d.tipo === "V") colorStyle = "background-color: rgb(198,224,180); color:#000;";
                if (d.tipo === "D") colorStyle = "background-color: rgb(255,255,153); color:#000;";
                if (d.tipo === "F") colorStyle = "background-color: rgb(255,153,153); color:#000;";
                if (d.tipo === "R") colorStyle = "background-color: rgb(180,198,231); color:#000;";
                const dia = d.fecha.split("-")[2];
                // diasCols += `<span style="${colorStyle} padding:2px 3px; border:1px solid #000; margin:2px; display:inline-block;">${dia}</span>`;
                diasCols += `<span data-tipo="${d.tipo}" 
                    style="${colorStyle} padding:2px 3px; border:1px solid #000; margin:2px; display:inline-block;">
                    ${dia}
                </span>`;
            });
            diasCols += "</td>";
        });

        let conteoCols = `
            <td>${semanas[semana].conteo["V"]}</td>
            <td>${semanas[semana].conteo["F"]}</td>
            <td>${semanas[semana].conteo["D"]}</td>
            <td>${semanas[semana].conteo["R"]}</td>
        `;

        body += `<tr>
            <td>${element.folio}</td>
            <td>${element.noemp}</td>
            <td>${element.nombre}</td>
            <td>${element.departamento}</td>
            <td>${element.puesto}</td>
            <td>${element.del}</td>
            <td>${element.hasta}</td>
            <td>${element.totalDias}</td>
            <td>${element.tipoSolicitud}</td>
            <td>${element.diasPorAntiguedad}</td>
            <td><span class="${estadoClass}">${estadoTexto}</span></td>
            <td>${mesNombre} (Sem. ${semana})</td>
            ${diasCols}
            ${conteoCols}
        </tr>`;
    }

    // Fila vacía para separar registros
    body += `<tr><td colspan="23" style="background-color:#fff;"></td></tr>`;
});

        document.getElementById("tblVacBody").innerHTML = body;
    }
}

function getWeekNumber(input) {
    let date;
    if (typeof input === "string") {
        const [year, month, day] = input.split('-').map(Number);
        date = new Date(year, month - 1, day);
    } else if (input instanceof Date) {
        date = new Date(input.getTime());
    } else {
        throw new Error("Formato de fecha no válido");
    }

    const tempDate = new Date(date.getTime());
    tempDate.setDate(tempDate.getDate() + 4 - (tempDate.getDay() || 7));
    const yearStart = new Date(tempDate.getFullYear(), 0, 1);
    const weekNumber = Math.ceil((((tempDate - yearStart) / 86400000) + 1) / 7);
    return weekNumber;
}

const Reportes = new ReporteVacaciones();
Reportes.reporteVacaciones();
document.getElementById("consultar").addEventListener("click", function(event){
    event.preventDefault();
    Reportes.consulta();
});
document.getElementById("limpiartodo").addEventListener("click", function(event){
    event.preventDefault();
    document.getElementById("formVacRep").reset();
    document.getElementById("tblVacBody").innerHTML = "";
});
document.getElementById("exportexcel").addEventListener("click", (e) => {
    e.preventDefault();
    Tools.exportartablaexcelVacaciones('tblVacaciones');
});
