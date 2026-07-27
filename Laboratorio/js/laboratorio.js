import { Toolsjs } from "../../Tools/Tools.js";
import { LabFolio, LaFolioSub } from "../module/classlaboratorio.js";
const Tools = new Toolsjs();

let fecha = document.getElementById('fecha');
let turno = document.getElementById('turno');
let monitor = document.getElementById('monitor');
let monitornombre = document.getElementById('monitornombre');
let sd = document.getElementById('sd');
let ql = document.getElementById('ql');
let muestras = document.getElementById('numuestras');
let departamento = document.getElementById('departamento');
let maquina = document.getElementById('maquina');
let conductor = document.getElementById('conductor');
let conductornombre = document.getElementById('conductornombre');
let supervisor = document.getElementById('supervisor');
let supervisornombre = document.getElementById('supervisornombre');

const LabFolioObj = new LabFolio(fecha, turno, monitor, sd, ql,
    muestras, departamento, maquina, conductor, supervisor);



let folio = document.getElementById('noofolio');
let clave = document.getElementById('clave');
let retenido = document.getElementById('retenido');
let merma = document.getElementById('merma');
let recuperado = document.getElementById('recuperado');
let defecto = document.getElementById('defecto');
let componente = document.getElementById('componente');
let numeroaparto = document.getElementById('numeroaparto');
let numerolibero = document.getElementById('numerolibero');
let hora = document.getElementById('hora');
let numerooperador = document.getElementById('numerooperador');
let estatus = document.getElementById('estatus');
let comentario = document.getElementById('comentario');
let seccion = document.getElementById('seccion');
let idencabezado = document.getElementById('idencabezado');

const LabFolioSubObj = new LaFolioSub(folio, clave, retenido, merma, recuperado,
    defecto, componente, numeroaparto, numerolibero, hora, numerooperador, estatus,
    comentario, seccion, idencabezado);


LabFolioObj.tblLaboratorioEnc().then((tblEnc) => {
    let body = '';
    tblEnc.forEach(element => {
        body += `<tr id='${element.id}'><td>${element.id}</td><td>${element.fecha}</td><td>${element.turno}</td>
        <td>${element.monitor}</td><td>${element.sd}</td><td>${element.ql}</td><td>${element.muestras}</td>
        <td>${element.NombreDepto}</td><td>${element.NombreMaquina}</td><td>${element.conductor}</td>
        <td>${element.supervisor}</td></tr>`;
    });
    document.getElementById('tblencfolio').innerHTML = body;
});


Tools.seleccionarFila('tblencfolio', (idSeleccionado) => {
    document.getElementById('idencabezado').value = idSeleccionado;
    LabFolioSubObj.tblLaboratorioSubEnc(idSeleccionado).then((tblEnc) => {
        let body = '';
        tblEnc.forEach(element => {
            body += `<tr id='${element.idsubEncabezado}'><td>${element.idsubEncabezado}</td><td>${element.folio}</td><td>${element.clave}</td>
                <td>${element.retenido}</td><td>${element.merma}</td><td>${element.recuperado}</td><td>${element.defecto}</td>
                <td>${element.componente}</td><td>${element.aparto}</td><td>${element.libero}</td>
                <td>${element.operador}</td><td>${element.hora}</td><td>${element.estado}</td><td>${element.comentario}</td>
                <td>${element.seccion}</td><td>${element.idencabezado}</td></tr>`;
        });
        document.getElementById('tblSubencfolio').innerHTML = body;
    });
    LabFolioObj.tblLaboratorioEncxid(idSeleccionado).then((element) => {
        fecha.value = element[0].fecha;
        turno.value = element[0].turno;
        monitornombre.value = element[0].monitor;
        sd.value = element[0].sd;
        ql.value = element[0].ql;
        muestras.value = element[0].muestras;
        departamento.value = element[0].NoDepto;
        conductornombre.value = element[0].conductor;
        supervisornombre.value = element[0].supervisor;
        departamento === '' ? document.getElementById('maquina').innerHTML = '' :
            Tools.llnarslc("CatalogoPersonal", "GetSlcMaquinasxdep&departamento=" + element[0].NoDepto, "maquina", 0).then(() => (maquina.value = element[0].NoMaquina));
        Tools.llnarslc("CatalogosBitacora", "GetDefectosxdep&deps=" + element[0].NoDepto, "defecto", 0);
        Tools.llnarslc("CatalogosBitacora", "GetComponentesNoconformidad&departamento=" + element[0].NoDepto, "componente", 0);
    });
}, 'resetEnc');


const idsCamposObligatorios = ['fecha', 'turno', 'monitor', 'sd', 'ql', 'numuestras', 'departamento', 'maquina', 'conductor', 'supervisor'];
const idsCamposlimpiar = ['fecha', 'turno', 'monitor', 'sd', 'ql', 'numuestras', 'departamento', 'maquina', 'conductor', 'supervisor',
    'monitornombre', 'supervisornombre', 'conductornombre'];
Tools.llnarslc("CatalogoPersonal", "GetSlcDeps", "departamento", 0);
Tools.llnarslc("CatalogosBitacora", "GetTurnos", "turno", 0);


document.getElementById('resetEnc').addEventListener('click', (e) => {
    e.preventDefault();
    Tools.limpiarCamposPorID(idsCamposlimpiar)
    document.getElementById('tblSubencfolio').innerHTML = '';
    document.getElementById('defecto').innerHTML = '';
    document.getElementById('componente').innerHTML = '';
    document.getElementById('idencabezado').value = '';

})
document.getElementById("departamento").addEventListener("change", (e) => {
    const departamento = e.target.value;
    departamento === '' ? document.getElementById('maquina').innerHTML = '' : Tools.llnarslc("CatalogoPersonal", "GetSlcMaquinasxdep&departamento=" + departamento, "maquina", 0);
});
document.getElementById('monitor').addEventListener('blur', (e) => {
    Tools.getDataEmpleado(e.target.value, 'monitornombre');
})
document.getElementById('conductor').addEventListener('blur', (e) => {
    Tools.getDataEmpleado(e.target.value, 'conductornombre');
})
document.getElementById('supervisor').addEventListener('blur', (e) => {
    Tools.getDataEmpleado(e.target.value, 'supervisornombre');
})

document.getElementById('saveEnclab').addEventListener('click', (e) => {
    const res = Tools.validarCamposPorID(idsCamposObligatorios);
    res != false && (
        LabFolioObj.guardarencabezado().then(() => {
            LabFolioObj.tblLaboratorioEnc().then((tblEnc) => {
                let body = '';
                tblEnc.forEach(element => {
                    body += `<tr id='${element.id}'><td>${element.id}</td><td>${element.fecha}</td><td>${element.turno}</td>
                    <td>${element.monitor}</td><td>${element.sd}</td><td>${element.ql}</td><td>${element.muestras}</td>
                    <td>${element.NombreDepto}</td><td>${element.NombreMaquina}</td><td>${element.conductor}</td>
                    <td>${element.supervisor}</td></tr>`;
                });
                document.getElementById('tblencfolio').innerHTML = body;
            });
        }),
        Tools.limpiarCamposPorID(idsCamposlimpiar)
    )
})



document.getElementById('numeroaparto').addEventListener('blur', (e) => {
    Tools.getDataEmpleado(e.target.value, 'apartonombre');
})
document.getElementById('numerolibero').addEventListener('blur', (e) => {
    Tools.getDataEmpleado(e.target.value, 'liberonombre');
})
document.getElementById('numerooperador').addEventListener('blur', (e) => {
    Tools.getDataEmpleado(e.target.value, 'operadornombre');
})

const idsCamposObligatoriossub = ['noofolio', 'clave', 'retenido', 'merma', 'defecto', 'componente', 'numeroaparto', 'numerolibero', 'hora', 'numerooperador',
    'estatus', 'comentario', 'idencabezado'];

const idsCamposlimpiarsub = ['noofolio', 'clave', 'retenido', 'merma', 'defecto', 'componente', 'numeroaparto', 'numerolibero', 'hora', 'numerooperador',
    'estatus', 'comentario', 'seccion', 'apartonombre', 'liberonombre', 'operadornombre'];


Tools.llnarslc("CatalogosBitacora", "GetClavesallConf", "clave", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcEstadosLaboratorio", "estatus", 0);
document.getElementById('departamento').addEventListener('change', (e) => {
    const departamento = e.target.value;
    Tools.llnarslc("CatalogosBitacora", "GetDefectosxdep&deps=" + departamento, "defecto", 0);
    Tools.llnarslc("CatalogosBitacora", "GetComponentesNoconformidad&departamento=" + departamento, "componente", 0);
})




document.getElementById('resetsub').addEventListener('click', (e) => {
    e.preventDefault();
    Tools.limpiarCamposPorID(idsCamposlimpiarsub)
})
document.getElementById('saveSubEnc').addEventListener('click', (e) => {
    const res = Tools.validarCamposPorID(idsCamposObligatoriossub);
    res != false && (
        LabFolioSubObj.guardarsubencabezado().then(() => {
            LabFolioSubObj.tblLaboratorioSubEnc(document.getElementById('idencabezado').value).then((tblEnc) => {
                let body = '';
                tblEnc.forEach(element => {
                    body += `<tr id='${element.idsubEncabezado}'><td>${element.idsubEncabezado}</td><td>${element.folio}</td><td>${element.clave}</td>
                        <td>${element.retenido}</td><td>${element.merma}</td><td>${element.recuperado}</td><td>${element.defecto}</td>
                        <td>${element.componente}</td><td>${element.aparto}</td><td>${element.libero}</td>
                        <td>${element.operador}</td><td>${element.hora}</td><td>${element.estado}</td><td>${element.comentario}</td>
                        <td>${element.seccion}</td><td>${element.idencabezado}</td></tr>`;
                });
                document.getElementById('tblSubencfolio').innerHTML = body;
            });
        }),
        Tools.limpiarCamposPorID(idsCamposlimpiarsub)
    )
})
