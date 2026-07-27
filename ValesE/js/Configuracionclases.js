import { Toolsjs } from "../../Tools/Tools.js";
import { ConfClaves } from "../module/ValesM.js";
const tools = new Toolsjs();
const confClave = new ConfClaves();
const modal = new bootstrap.Modal(document.getElementById('modalClases'));
confClave.tblclases('').then(element => document.getElementById('tblclases').innerHTML = element);
document.getElementById('buscarclase').addEventListener('click', (e) => {
    e.preventDefault();
    const busqueda = document.getElementById('clasebusqueda').value;
    console.log('presionado');
    confClave.tblclases(busqueda).then(element => document.getElementById('tblclases').innerHTML = element);
})
document.getElementById('savechgclases').addEventListener('click', function (e) {
    e.preventDefault();
    const busqueda = document.getElementById('clasebusqueda').value;
    const idclase = document.getElementById('idclase').value;
    const noclase = document.getElementById('noclase').value;
    const nombreclase = document.getElementById('descripcionclase').value;
    confClave.saveClase(idclase, noclase, nombreclase).then(() => {
        confClave.tblclases(busqueda).then(element => document.getElementById('tblclases').innerHTML = element);
        modal.hide();
    });
})

document.getElementById('nuevaclase').addEventListener('click', (e) => {
    e.preventDefault();
    modal.show();
    document.getElementById('idclase').value = '';
    document.getElementById('noclase').readOnly = false;
    document.getElementById('noclase').value = '';
    document.getElementById('descripcionclase').value = '';
})

window.editclases = (param) => { confClave.editarClases(param, 'idclase', 'noclase', 'descripcionclase') };