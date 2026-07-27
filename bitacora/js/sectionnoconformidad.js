import { NoConformidad } from "../modules/Bitacora.js";
import { Toolsjs } from "../../Tools/Tools.js";

const tools = new Toolsjs();
const noconformidad = new NoConformidad();
noconformidad.tblNoConformidad();
tools.llnarslc('CatalogoPersonal', 'GetSlcDeps', 'depsconf', 0);
tools.llnarslc('CatalogosBitacora', 'empleadosallTraz', 'selladorconf', 0);
tools.llnarslc('CatalogosBitacora', 'empleadosallTraz', 'operadorconf', 0);
tools.llnarslc('CatalogosBitacora', 'empleadosallTraz', 'liderconf', 0);
tools.llnarslc('CatalogosBitacora', 'GetTurnos', 'turnoconf', 0);
tools.llnarslc('CatalogosBitacora', 'GetClavesxmaquinaSession', 'claveprodconf', 0);
document.getElementById('turnoconf').addEventListener('change',(event)=>{
    event.target.value == 3 && swal.fire('Pasando las 12:00 AM debes seleccionar el día anterior','','warning')
})
document.getElementById('depsconf').addEventListener('change', () => {
    const depsconf = document.getElementById('depsconf').value;
    tools.llnarslc('CatalogosBitacora', 'GetComponentesNoconformidad&departamento='+depsconf, 'componentesconf', 0);
    depsconf == '' ? null : tools.llnarslc('CatalogosBitacora', 'GetDefectosxdep&deps=' + depsconf, 'defectoconf', 0);
    depsconf == 1 ? (document.getElementById('totalprodconf').value = 35) : (document.getElementById('totalprodconf').value = '');
})
document.getElementById("guardarconf").addEventListener("click", function (e) {
    e.preventDefault();
    const idconf = document.getElementById('folioconf').value;
    const fechaconf = document.getElementById('fechaconf').value;
    const depsconf = document.getElementById('depsconf').value;
    const selladorconf = document.getElementById('selladorconf').value;
    const operadorconf = document.getElementById('operadorconf').value;
    const turnoconf = document.getElementById('turnoconf').value;
    const claveprodconf = document.getElementById('claveprodconf').value;
    const horaconf = document.getElementById('horaconf').value;
    const defectoconf = document.getElementById('defectoconf').value;
    const descripcionconf = document.getElementById('descripcionconf').value;
    const totalprodconf = document.getElementById('totalprodconf').value;
    const prodrecuperadoconf = document.getElementById('prodrecuperadoconf').value;
    const prodmermaconf = document.getElementById('prodmermaconf').value;
    const empdefectioconf = document.getElementById('empdefectioconf').value;
    const terdefectoconf = document.getElementById('terdefectoconf').value;
    const liderconf = document.getElementById('liderconf').value;
    const accionescorrectivasconf = document.getElementById('accionescorrectivasconf').value;
    const componentesconf = document.getElementById('componentesconf').value;
    const tipeatributeconf = $('input[name="tipeatributeconf"]:checked').val();
    const noConformidad = new NoConformidad(fechaconf, depsconf, selladorconf, operadorconf, turnoconf, claveprodconf, horaconf,
        defectoconf, descripcionconf, totalprodconf, prodrecuperadoconf, prodmermaconf, empdefectioconf, terdefectoconf, liderconf, accionescorrectivasconf,
        tipeatributeconf, idconf,componentesconf);
    const resvalidacion = noConformidad.validarcampos();
    if (resvalidacion === false) return false;
    noConformidad.saveNoConformidad().then(() => noConformidad.tblNoConformidad());
});
document.getElementById('selladorconfnoemp').addEventListener('keyup', function () {
    const noemp = document.getElementById('selladorconfnoemp').value;
    document.getElementById('selladorconf').value = noemp;
})
document.getElementById('operadorconfnoemp').addEventListener('keyup', function () {
    const noemp = document.getElementById('operadorconfnoemp').value;
    document.getElementById('operadorconf').value = noemp;
})
document.getElementById('liderconfnoemp').addEventListener('keyup', function () {
    const noemp = document.getElementById('liderconfnoemp').value;
    document.getElementById('liderconf').value = noemp;
})
document.getElementById('selladorconf').addEventListener('change', function () {
    const noemp = document.getElementById('selladorconf').value;
    document.getElementById('selladorconfnoemp').value = noemp;
})
document.getElementById('operadorconf').addEventListener('change', function () {
    const noemp = document.getElementById('operadorconf').value;
    document.getElementById('operadorconfnoemp').value = noemp;
})
document.getElementById('liderconf').addEventListener('change', function () {
    const noemp = document.getElementById('liderconf').value;
    document.getElementById('liderconfnoemp').value = noemp;
})

