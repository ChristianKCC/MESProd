import { Toolsjs } from "../../Tools/Tools.js";
import { ConfClaves } from "../module/ValesM.js";
const tools = new Toolsjs();
const confClave = new ConfClaves();
confClave.tblclases().then(element => document.getElementById('tblclases').innerHTML = element).then(() => tools.agregarFiltros('tblclasesenc'));
confClave.tblclaves().then(element => document.getElementById('tblclaves').innerHTML = element).then(() => tools.agregarFiltros('tblclavesenc'));
confClave.tblmateriales().then(element => document.getElementById('tblmateriales').innerHTML = element).then(() => tools.agregarFiltros('tblmaterialesenc'));
confClave.tblconvinaciones().then(element => document.getElementById('tblconvinaciones').innerHTML = element).then(() => tools.agregarFiltros('tblconvinacionesenc'));

tools.llnarslc('CatalogoPersonal', 'GetSlcMaquinas', 'maquinaconv', 0);

document.getElementById('savechgclases').addEventListener('click', function (e) {
    // empezar con esta.
    e.preventDefault();
    const idclase = document.getElementById('idclase').value;
    const noclase = document.getElementById('noclase').value;
    const nombreclase = document.getElementById('nombreclase').value;
    confClave.saveClase(idclase, noclase, nombreclase).then(() => {
        confClave.tblclases().then(element => document.getElementById('tblclases').innerHTML = element);
        document.getElementById('')
    });
})
document.getElementById('savechgclaves').addEventListener('click', function (e) {
    e.preventDefault();
    const idclave = document.getElementById('idclave').value;
    const noclave = document.getElementById('noclave').value;
    const nombreclave = document.getElementById('nombreclave').value;
    const umclave = document.getElementById('umclave').value;
    confClave.saveClave(idclave, noclave, nombreclave, umclave).then(() => {
        confClave.tblclaves().then(element => document.getElementById('tblclaves').innerHTML = element);
    });
})
document.getElementById('savechgmateriales').addEventListener('click', function (e) {
    e.preventDefault();
    const idmaterial = document.getElementById('idmaterial').value;
    const nomaterial = document.getElementById('nomaterial').value;
    const nombrematerial = document.getElementById('nombrematerial').value;
    const ummaterial = document.getElementById('ummaterial').value;
    const ummat = document.getElementById('ummat').value;
    const montacargas = document.getElementById('montacargas').value;
    const costos = document.getElementById('costos').value;
    const tiempo = document.getElementById('tiempo').value;
    confClave.saveMaterial(idmaterial, nomaterial, nombrematerial, ummaterial, ummat, montacargas, costos, tiempo);
})
document.getElementById('saveconvinacion').addEventListener('click', function (e) {
    e.preventDefault();
    const maquinaconv = document.getElementById('maquinaconv').value;
    const claseconv = document.getElementById('claseconv').value;
    const claveconv = document.getElementById('claveconv').value;
    const materialconv = document.getElementById('materialconv').value;
    confClave.saveConvinacion(maquinaconv, claseconv, claveconv, materialconv);
})



document.getElementById('cleanclases').addEventListener('click', () => document.getElementById('noclase').removeAttribute('readonly', true));
document.getElementById('cleanclaves').addEventListener('click', () => document.getElementById('noclave').removeAttribute('readonly', true));
document.getElementById('cleanmateriales').addEventListener('click', () => document.getElementById('nomaterial').removeAttribute('readonly', true));

window.editclases = (params) => confClave.cosultarxid(params, "editclasexid").then((respuesta) => {
    document.getElementById('idclase').value = respuesta[0].id;
    document.getElementById('noclase').value = respuesta[0].noclase;
    document.getElementById('claseconv').value = respuesta[0].noclase;
    document.getElementById('nombreclase').value = respuesta[0].descclase;
    document.getElementById('noclase').setAttribute('readonly', true);
});
window.editclaves = (params) => confClave.cosultarxid(params, "editclavexid").then((respuesta) => {
    document.getElementById('idclave').value = respuesta[0].id;
    document.getElementById('noclave').value = respuesta[0].noclave;
    document.getElementById('nombreclave').value = respuesta[0].descclave;
    document.getElementById('umclave').value = respuesta[0].umclave;
    document.getElementById('claveconv').value = respuesta[0].noclave;
    document.getElementById('noclave').setAttribute('readonly', true);
});
window.editmaterial = (params) => confClave.cosultarxid(params, "editmaterialxid").then((respuesta) => {
    document.getElementById('idmaterial').value = respuesta[0].id;
    document.getElementById('nomaterial').value = respuesta[0].nomaterial;
    document.getElementById('nombrematerial').value = respuesta[0].descmaterial;
    document.getElementById('ummaterial').value = respuesta[0].ummaterial;
    document.getElementById('ummat').value = respuesta[0].um;
    document.getElementById('montacargas').value = respuesta[0].montacargas;
    document.getElementById('costos').value = respuesta[0].costos;
    document.getElementById('tiempo').value = respuesta[0].TiempoMat;
    document.getElementById('materialconv').value = respuesta[0].nomaterial;
    document.getElementById('nomaterial').setAttribute('readonly', true);
});
// window.editconvinacion = (params) => confClave.cosultarxid(params,"editconvinacionesxid").then((respuesta)=>{
//     document.getElementById('idmaterial').value=respuesta[0].id;
//     document.getElementById('nomaterial').value=respuesta[0].noaquina;
//     document.getElementById('nomaterial').value=respuesta[0].nomaquina;
//     document.getElementById('nombrematerial').value=respuesta[0].noclase;
//     document.getElementById('ummaterial').value=respuesta[0].nomclase;
//     document.getElementById('ummat').value=respuesta[0].noclave;
//     document.getElementById('montacargas').value=respuesta[0].nomclave;
//     document.getElementById('costos').value=respuesta[0].nomaterial;
//     document.getElementById('tiempo').value=respuesta[0].nommaterial;
// });


