import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();
class Reportes {
    reportegenral(){
        Tools.llnarslc('CatalogoPersonal', "GetSlcMaquinas", "departamento", 0);
    }
    // Consulta y llenado de datos para reporte general
    async consulta(){
        let fechai = document.getElementById("fechai").value;
        let fechaf = document.getElementById("fechaf").value;
        let noemp = document.getElementById("noemp").value;
        let folio = document.getElementById("folio").value;
        let departamento = document.getElementById("departamento").value;
        let body = "";
        const data = new FormData();
        if((fechai == "" || fechaf == "") && folio == ""){
        Swal.fire('Ups!!!', 'El intervalo de fechas o el folio es obligatorio', 'info');
        return false
        }
        data.append("fechai",fechai);
        data.append("fechaf",fechaf);
        data.append("noemp",noemp);
        data.append("folio",folio);
        data.append("departamento",departamento);
        const respuesta = await fetch("php/index.php?reportegenral",{
            method: "POST",
            body: data
        });
        const respuestaraw = await respuesta.json();
        respuestaraw.forEach(element => {
            body += `
            <tr>
            <td>${element.id}</td>
            <td>${element.folio}</td>
            <td>${element.noempsub}</td>
            <td>${element.nombresub}</td>
            <td>${element.fecha}</td>
            <td>${element.maquina}</td>
            <td>${element.lunes }</td>
            <td>${element.martes }</td>
            <td>${element.miercoles }</td>
            <td>${element.jueves }</td>
            <td>${element.viernes}</td>
            <td>${element.sabado }</td>
            <td>${element.domingo}</td>
            <td>${element.puestoregular}</td> 
            <td>${element.puestotemporal}</td>
            <td><span class="${element.estadoClass}">${element.estadoTexto}</span></td>
            </tr>`;
        });
        document.getElementById("tblrtegenral").innerHTML=body;
    }
}
Reportes = new Reportes();
Reportes.reportegenral();
document.getElementById("consultar").addEventListener("click",function(event){
    event.preventDefault();
    Reportes.consulta();
});
document.getElementById("limpiartodo").addEventListener("click",function(event){
    event.preventDefault();
    document.getElementById("formrep").reset();
    document.getElementById("tblrtegenral").innerHTML= "";
});
document.getElementById('exportarexcel').addEventListener('click',(e)=>{
    e.preventDefault();
    Tools.exportartablaexcel('tbltiempoextra');
})