import { Toolsjs } from "../../Tools/Tools.js";
import { EPPMod } from "../../EPP/module/eppmod.js";
const Tools = new Toolsjs();
const EPPObj = new EPPMod();

document.getElementById('noempresepp').addEventListener('keyup', (e) => Tools.getDataEmpleado(e.target.value, 'nombreresepp', 'departamentoresepp', 'puestoresepp'));
document.getElementById('noempobsepp').addEventListener('keyup', (e) => Tools.getDataEmpleado(e.target.value, 'nombreobsepp', 'departamentoobsepp', 'puestoobsepp'));
EPPObj.getListEquipo('ListEppBasico').then(val => {
    document.getElementById('listeppbasico').innerHTML = val;
});
EPPObj.getListEquipo('ListEppEspecifico').then(val => {
    document.getElementById('listeppespecifico').innerHTML = val;
});
EPPObj.getListEquipo('ListEppBPM').then(val => {
    document.getElementById('listeppbpm').innerHTML = val;
});
document.getElementById('saveEpp').addEventListener('click', function (e) {
    e.preventDefault();
    var checkboxes = document.querySelectorAll('input[type=radio]:checked');
    const noempresepp = document.getElementById('noempresepp').value;
    const nombreresepp = document.getElementById('nombreresepp').value;
    const noempobsepp = document.getElementById('noempobsepp').value;
    const nombreobsepp = document.getElementById('nombreobsepp').value;
    const comentario = document.getElementById('comentarioepp').value;
    Swal.fire({
        title: "estas seguro de capturar?",
        text: "Ya no puedes cambiar la información!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, seguro!"
    }).then((result) => {
        if (result.isConfirmed) {
            if (noempresepp === '' || nombreresepp === '' || noempobsepp === '' || nombreobsepp === '') {
                swal.fire('UPS!', 'Por favor selecciona un empleado valido', 'warning');
                return false;
            }
            let checkboxValues = [];
            checkboxes.forEach(function (checkbox) {
                checkboxValues.push({ nombre: checkbox.getAttribute('name'), valor: checkbox.value });
            });
            EPPObj.saveEpp(noempobsepp, checkboxValues, comentario, noempresepp).then(() =>
                EPPObj.tblEPP(2).then(element => document.getElementById('tbleppenc').innerHTML = element)
            );
        } else {
            return false;
        }
    });
})
EPPObj.tblEPP(2).then(element => document.getElementById('tbleppenc').innerHTML = element);
document.getElementById('limpiarepp').addEventListener('click', function (e) {
    e.preventDefault();
    EPPObj.limpiar();
})
const exampleModal = document.getElementById('exampleModal')
exampleModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget
    const recipient = button.getAttribute('data-bs-whatever')
    const modalTitle = exampleModal.querySelector('.modal-title')
    modalTitle.textContent = 'Información del folio: ' + recipient
    EPPObj.tblEPPSubEnc(recipient).then((element) => {
        document.getElementById('tblsubenc').innerHTML = element
    });
})