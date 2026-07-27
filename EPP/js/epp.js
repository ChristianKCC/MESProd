import { Toolsjs } from "../../Tools/Tools.js";
import { EPPMod } from "../module/eppmod.js";
const Tools = new Toolsjs();
const EPPObj = new EPPMod();
document.getElementById('noemp').addEventListener('keyup', (e) => Tools.getDataEmpleado(e.target.value, 'nombre', 'departamento', 'puesto'));
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
    const noemp = document.getElementById('noemp').value;
    const nombre = document.getElementById('nombre').value;
    const comentario = document.getElementById('comentario').value;
    Swal.fire({
        title: "¿Estás seguro de capturar?",
        text: "¡Ya no puedes cambiar la información!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "¡Sí, seguro!"
    }).then((result) => {
        if (result.isConfirmed) {
            if (noemp === '' || nombre === '') {
                swal.fire('Ups!!!', 'Por favor selecciona un empleado válido', 'warning');
                return false;
            }
            let checkboxValues = [];
            checkboxes.forEach(function (checkbox) {
                checkboxValues.push({ nombre: checkbox.getAttribute('name'), valor: checkbox.value });
            });
            EPPObj.saveEpp(noemp, checkboxValues, comentario).then(() =>
                EPPObj.tblEPP().then(element => document.getElementById('tbleppenc').innerHTML = element)
            );
        } else {
            return false;
        }
    });

})
EPPObj.tblEPP().then(element => document.getElementById('tbleppenc').innerHTML = element);
document.getElementById('limpiar').addEventListener('click', function (e) {
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