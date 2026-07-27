import { Toolsjs } from "../../Tools/Tools.js";
import { ConfClaves } from "../module/ValesM.js";
const tools = new Toolsjs();
const confClave = new ConfClaves();
tools.llnarslc('CatalogoPersonal', 'GetSlcMaquinas', 'maquinaconv', 0);
const modal = new bootstrap.Modal(document.getElementById('modalConvinaciones'));
confClave.tblconvinaciones('').then(element => document.getElementById('tblconvinaciones').innerHTML = element);
document.getElementById('buscarcomb').addEventListener('click', (e) => {
    e.preventDefault();
    const busqueda = document.getElementById('combbusqueda').value;
    confClave.tblconvinaciones(busqueda).then(element => document.getElementById('tblconvinaciones').innerHTML = element);
})
document.getElementById('savecombinacion').addEventListener('click', function (e) {
    e.preventDefault();
    const busqueda = document.getElementById('buscarcomb').value;
    const idconvinacion = document.getElementById('idconvinacion').value;
    const maquinaconv = document.getElementById('maquinaconv').value;
    const claseconv = document.getElementById('claseconv').value;
    const claveconv = document.getElementById('claveconv').value;
    const materialconv = document.getElementById('materialconv').value;
    confClave.saveConvinacion(idconvinacion,maquinaconv, claseconv, claveconv, materialconv).then(() => {
        confClave.tblconvinaciones(busqueda).then(element => document.getElementById('tblconvinaciones').innerHTML = element);
        modal.hide();
    });
});

document.getElementById('nuevacomb').addEventListener('click', (e) => {
    e.preventDefault();
    modal.show();
    document.getElementById('idconvinacion').value = '';
    document.getElementById('maquinaconv').value = '';
    document.getElementById('claseconv').value = '';
    document.getElementById('claveconv').value = '';
    document.getElementById('materialconv').value = '';
    document.getElementById('claveinput').value = '';
    document.getElementById('claseinput').value = '';
    document.getElementById('materialinput').value = '';
})

window.editconvinacionesxid = (param) => { confClave.editarConvinaciones(param, 'idconvinacion', 'maquinaconv', 'claveconv', 'claseconv', 'materialconv',
    'claveinput', 'claseinput', 'materialinput'
) };



document.getElementById("claveinput").addEventListener("input", (e) => {
    const autocompleteclaves = document.getElementById("autocompleteclaves");
    confClave.slcautocomplete(e, autocompleteclaves, 'claveconv', '../ValesE/php/Vales.php?autoclaves', 'claveinput');
});
document.getElementById("claseinput").addEventListener("input", (e) => {
    const autocompleteclases = document.getElementById("autocompleteclases");
    confClave.slcautocomplete(e, autocompleteclases, 'claseconv', '../ValesE/php/Vales.php?autoclases', 'claseinput');
});
document.getElementById("materialinput").addEventListener("input", (e) => {
    const autocompletematerial = document.getElementById("autocompletematerial");
    confClave.slcautocomplete(e, autocompletematerial, 'materialconv', '../ValesE/php/Vales.php?automateriales', 'materialinput');
});
