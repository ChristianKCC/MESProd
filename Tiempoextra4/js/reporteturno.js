import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();

class ReporteTurno {
    inicio() {
        // Llenar select de departamentos
        Tools.llnarslc('CatalogoPersonal', "GetSlcMaquinas", "departamento", 0);
    }

    async consulta() {
        let fechai = document.getElementById("fechai").value;
        let fechaf = document.getElementById("fechaf").value;
        let folio = document.getElementById("folio").value;
        let departamento = document.getElementById("departamento").value;

        if ((fechai === "" || fechaf === "") && folio === "") {
            Swal.fire('Ups!!!', 'El intervalo de fechas o el folio es obligatorio', 'info');
            return false;
        }

        const data = new FormData();
        data.append("fechai", fechai);
        data.append("fechaf", fechaf);
        data.append("folio", folio);
        data.append("departamento", departamento);

        const respuesta = await fetch("php/index.php?reporteturno", {
            method: "POST",
            body: data
        });
        const respuestaraw = await respuesta.json();

        let body = "";
        respuestaraw.forEach(element => {
            body += `
            <tr>
                <td>${element.Ctt_id}</td>
                <td>${element.Ctt_folio}</td>
                <td>${element.Ctt_fecha}</td>
                <td>${element.Ctt_depto}</td>
                <td>${element.Ctt_de}</td>
                <td>${element.Ctt_a}</td>
                <td>${element.Ctt_tripulacion}</td>
                <td>${element.Ctt_horario}</td>
                <td>${element.Ctt_rol}</td>
                <td>${element.Ctt_aPartirDel}</td>
                <td>${element.Ctt_hastaEl}</td>
                <td>${element.Ctt_horaPresentacion}</td>
                <td>${element.Ctt_turnoPresentacion}</td>
            </tr>`;
        });
        document.getElementById("tblrturno").innerHTML = body;
    }
}

const reporteTurno = new ReporteTurno();
reporteTurno.inicio();

document.getElementById("consultar").addEventListener("click", function (event) {
    event.preventDefault();
    reporteTurno.consulta();
});

document.getElementById("limpiartodo").addEventListener("click", function (event) {
    event.preventDefault();
    document.getElementById("formrepturno").reset();
    document.getElementById("tblrturno").innerHTML = "";
});

document.getElementById("exportarexcel").addEventListener("click", function (event) {
    event.preventDefault();
    Tools.exportartablaexcel("tblturnotemp");
});
