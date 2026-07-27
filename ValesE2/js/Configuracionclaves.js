import { Toolsjs } from "../../Tools/Tools.js";
import { ConfClaves } from "../module/ValesM.js";
const tools = new Toolsjs();
const confClave = new ConfClaves();
const modal = new bootstrap.Modal(document.getElementById('modalClaves'));
confClave.tblclaves('').then(element => document.getElementById('tblclaves').innerHTML = element);
tools.llnarslc('CatalogosBitacora', 'getClaveClase', 'claveclase', 0);
tools.llnarslc('CatalogosBitacora', 'getClaveTipo', 'clavetipo', 0);
document.getElementById('buscarclave').addEventListener('click', (e) => {
    e.preventDefault();
    const busqueda = document.getElementById('clavebusqueda').value;
    console.log('presionado');
    confClave.tblclaves(busqueda).then(element => document.getElementById('tblclaves').innerHTML = element);
})
document.getElementById('savechgclaves').addEventListener('click', function (e) {
    e.preventDefault();
    const busqueda = document.getElementById('clavebusqueda').value;
    const idclave = document.getElementById('idclave').value;
    const noclave = document.getElementById('noclave').value;
    const nombreclave = document.getElementById('descripcionclave').value;
    const xcaja = document.getElementById('xcaja').value;
    const factor = document.getElementById('factor').value;
    const claveclase = document.getElementById('claveclase').value;
    const clavetipo = document.getElementById('clavetipo').value;
    confClave.saveClave(idclave, noclave, nombreclave, xcaja, factor, claveclase, clavetipo).then(() => {
        confClave.tblclaves(busqueda).then(element => document.getElementById('tblclaves').innerHTML = element);
        modal.hide();
    });
})

document.getElementById('nuevaclave').addEventListener('click', (e) => {
    e.preventDefault();
    modal.show();
    document.getElementById('idclave').value = '';
    document.getElementById('noclave').readOnly = false;
    document.getElementById('noclave').value = '';
    document.getElementById('descripcionclave').value = '';
    document.getElementById('xcaja').value = '';
    document.getElementById('factor').value = '';
    document.getElementById('claveclase').value = '';
    document.getElementById('clavetipo').value = '';
})

window.editclaves = (param) => { confClave.editarClave(param, 'idclave', 'noclave', 'descripcionclave', 'xcaja', 'factor', 'claveclase', 'clavetipo') };