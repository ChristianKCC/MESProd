import { Toolsjs } from "../../Tools/Tools.js";
import { LabFolio } from "../module/classlaboratorio.js";
const Tools = new Toolsjs();
const LabFolioObj = new LabFolio();
let fechai = document.getElementById('fechainicio');
let fechaf = document.getElementById('fechafinal');
document.getElementById('buscar').addEventListener('click', (e) => {
    e.preventDefault();
    if (fechai.value === '' || fechaf.value === '') {
        swal.fire('Ups!!!', 'El rango de fecha es obligatorio', 'warning')
        return false;
    } else {
        LabFolioObj.tblLaboratorioReporte(fechai.value, fechaf.value).then((tblEnc) => {
            let body = '';
            tblEnc.forEach(element => {
                body += `<tr id='${element.id}'><td>${element.id}</td><td>${element.fecha}</td><td>${element.turno}</td>
            <td>${element.monitor}</td><td>${element.sd}</td><td>${element.ql}</td><td>${element.muestras}</td>
            <td>${element.NombreDepto}</td><td>${element.NombreMaquina}</td><td>${element.conductor}</td>
            <td>${element.supervisor}</td><td><button class='btn btn-danger btn-sm' id='btntr'><i class="fas fa-file-pdf"></i></button></td></tr>`;
            });
            document.getElementById('tblReportefolio').innerHTML = body;
        });
    }
})
document.getElementById('btntr').addEventListener('click',(e)=>{
    e.preventDefault();
    alert(e.target.value)

})
