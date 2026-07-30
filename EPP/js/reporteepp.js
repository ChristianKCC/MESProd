import { Toolsjs } from "../../Tools/Tools.js";
import { EPPMod } from "../module/eppmod.js";
const Tools = new Toolsjs();
const EeppObj = new EPPMod();

Tools.llnarslc('CatalogoPersonal', 'GetSlcDepsall', 'departamento', 0);

document.getElementById('buscar').addEventListener('click', function (e) {
    e.preventDefault();
    const fechai = document.getElementById('fechai').value;
    const fechaf = document.getElementById('fechaf').value;
    const noemp = document.getElementById('noemp').value;
    const observador = document.getElementById('observador').value;
    const departamento = document.getElementById('departamento').value;
    if(fechai === '' || fechaf ===''){
        swal.fire('Ups!','El rango de fecha es obligatorio','warning');
        return false;
    }
    EeppObj.tblEPPReporte(fechai,fechaf,noemp,observador,departamento).then((element) => {
        document.getElementById('tbleppenc').innerHTML = element
    })
})
const exampleModal = document.getElementById('exampleModal')
exampleModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget
    const recipient = button.getAttribute('data-bs-whatever')
    const modalTitle = exampleModal.querySelector('.modal-title')
    modalTitle.textContent = 'Información del folio: ' + recipient
    EeppObj.tblEPPSubEnc(recipient).then((element) => {
        document.getElementById('tblsubenc').innerHTML = element
    });
})