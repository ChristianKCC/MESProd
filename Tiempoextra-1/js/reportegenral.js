import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();
class Reportes {
    reportegenral(){
        Tools.llnarslc('CatalogoPersonal', "GetSlcDeps", "departamento", 0);
        Tools.llnarslcruta("php/index.php?motivostiempoextra", "motivos");
    }

    async consulta(){
        let fechai = document.getElementById("fechai").value;
        let fechaf = document.getElementById("fechaf").value;
        let noemp = document.getElementById("noemp").value;
        let folio = document.getElementById("folio").value;
        let departamento = document.getElementById("departamento").value;
        let motivo = document.getElementById("motivos").value;
        let body = "";
        const data = new FormData();
        if((fechai == "" || fechaf == "") && folio == ""){
        Swal.fire('UPS!!!', 'El intervalo de fechas o el folio es obligatorio', 'info');
        return false
        }
        data.append("fechai",fechai);
        data.append("fechaf",fechaf);
        data.append("noemp",noemp);
        data.append("folio",folio);
        data.append("departamento",departamento);
        data.append("motivo",motivo);
        const respuesta = await fetch("php/index.php?reportegenral",{
            method: "POST",
            body: data
        });
        const respuestaraw = await respuesta.json();
        respuestaraw.forEach(element => {
            body += `<tr>
                <td>${element.id}</td>
                <td>${element.folio}</td>
                <td>${element.noempsub}</td>
                <td>${element.nombresub}</td>
                <td>${element.fecha}</td>
                <td>${element.dif}</td>
                <td>${element.maquina}</td>
                <td>${element.motivo}</td>
                <td>${element.razon}</td>
                <td>${element.supervisor}</td>
                <td>${element.nombresup}</td>
                <td>${element.depto}</td>
                <td>
                    <span class="${element.estadoClass}">
                        ${element.estadoTexto}
                    </span>
                </td>
                <td>
                    <button 
                        class="btn btn-sm btn-danger" 
                        onclick="pdfFin(${element.folio})">
                        <i class="fa-solid fa-file-pdf"></i> Consultar PDF
                    </button>
                </td>

            </tr>`;
        });
        document.getElementById("tblrtegenral").innerHTML=body;
    }

    pdffin(id) {
        window.open("./pdf/reporte.php?folio=" + btoa(id));
    }
}
Reportes = new Reportes();
Reportes.reportegenral();
document.getElementById("consultar").addEventListener("click",function(event){
    event.preventDefault();
    Reportes.consulta();
});
window.pdfFin = id => Reportes.pdffin(id);
document.getElementById("limpiartodo").addEventListener("click",function(event){
    event.preventDefault();
    document.getElementById("formrep").reset();
    document.getElementById("tblrtegenral").innerHTML= "";
});
document.getElementById('exportexcel').addEventListener('click',(e)=>{
    e.preventDefault();
    Tools.exportartablaexcel('tbltiempoextra');
})