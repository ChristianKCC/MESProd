import { Toolsjs } from "../../Tools/Tools.js";
import { ProduccionEnc } from "../modules/prodmodules.js";

let id = document.getElementById('id');
let fecha = document.getElementById('fecha');
let departamento = document.getElementById('departamento');
let maquina = document.getElementById('maquina');
let clave = document.getElementById('clave');
let noemp = document.getElementById('noemp');
let conductor = document.getElementById('conductor');
let turno = document.getElementById('turno');
let hrs = document.getElementById('horastrabajadas');
let cajastotales = document.getElementById('cajastotales');
let cajasreales = document.getElementById('cajasreales');
const ToolsObj = new Toolsjs();
const idsCamposObligatorios = ['fecha', 'departamento', 'maquina', 'clave', 'noemp', 'conductor', 'turno', 'horastrabajadas', 'cajastotales', 'cajasreales'];
const idsCamposlimpiarsub = ['fecha', 'departamento', 'maquina', 'clave', 'noemp', 'conductor', 'turno', 'horastrabajadas', 'cajastotales', 'cajasreales', 'id'];


const ProduccionEncObj = new ProduccionEnc(id, fecha, departamento, maquina, clave, noemp,
    turno, hrs, cajastotales, cajasreales);
ProduccionEncObj.tblProduccionesEnc(0, 0, '', '').then(element => {
    document.getElementById('tblencproduccion').innerHTML = element
});
ToolsObj.llnarslc('CatalogoPersonal', 'GetSlcMaquinas', 'slcmaquina', 0)
ToolsObj.llnarslc('CatalogoPersonal', 'GetSlcDeps', 'departamento', 0)
ToolsObj.llnarslc('CatalogosBitacora', 'GetTurnos', 'turno', 0)
document.getElementById('btnbuscar').addEventListener('click', (e) => {
    e.preventDefault();
    const fechai = document.getElementById('fechai').value;
    const fechaf = document.getElementById('fechaf').value;
    const maquinaslc = document.getElementById('slcmaquina').value;
    const idproduccion = document.getElementById('idproduccion').value;
    ProduccionEncObj.tblProduccionesEnc(fechai, fechaf, maquinaslc, idproduccion).then(element => {
        document.getElementById('tblencproduccion').innerHTML = element
    });

})

document.getElementById("departamento").addEventListener("change", (e) => {
    const departamento = e.target.value;
    departamento === '' ? document.getElementById('maquina').innerHTML = '' : ToolsObj.llnarslc("CatalogoPersonal", "GetSlcMaquinasxdep&departamento=" + departamento, "maquina", 0);
});
document.getElementById("maquina").addEventListener("change", (e) => {
    const maquina = e.target.value;
    maquina === '' ? document.getElementById('clave').innerHTML = '' : ToolsObj.llnarslc("CatalogosBitacora", "GetClavesxmaquina&maquina=" + maquina, "clave", 0);
});
document.getElementById('noemp').addEventListener('blur', (e) => {
    ToolsObj.getDataEmpleado(e.target.value, 'conductor');
})
document.getElementById('saveEncabezado').addEventListener('click', (e) => {
    e.preventDefault();
    const res = ToolsObj.validarCamposPorID(idsCamposObligatorios);
    res != false && ProduccionEncObj.saveEncProduccion().then(() => {
        ProduccionEncObj.tblProduccionesEnc(0, 0, '', '').then(element => {
            document.getElementById('tblencproduccion').innerHTML = element
            ToolsObj.limpiarCamposPorID(idsCamposlimpiarsub);
            document.getElementById("tblctrltiempos").innerHTML = '';
            document.getElementById("tblpresentacionesbit").innerHTML = '';
        })
    });
});
document.getElementById('resetEnc').addEventListener('click', (e) => {
    e.preventDefault();
    ToolsObj.limpiarCamposPorID(idsCamposlimpiarsub);
    document.getElementById("tblctrltiempos").innerHTML = '';
    document.getElementById("tblpresentacionesbit").innerHTML = '';
})
ToolsObj.seleccionarFila('tblencproduccion', async (idSeleccionado) => {
    let respuestaraw = await fetch("./php/producciones.php?tblProduccionesEncxid&id=" + idSeleccionado);
    let respuesta = await respuestaraw.json();
    id.value = respuesta[0].idProduccion;
    fecha.value = respuesta[0].fecha;
    departamento.value = respuesta[0].departamento;
    await ToolsObj.llnarslc("CatalogoPersonal", "GetSlcMaquinasxdep&departamento=" + respuesta[0].departamento, 'maquina', 0)
    maquina.value = respuesta[0].maquina;
    await ToolsObj.llnarslc("CatalogosBitacora", "GetClavesxmaquina&maquina=" + respuesta[0].maquina, "clave", 0)
    clave.value = respuesta[0].clave;
    noemp.value = respuesta[0].noemp;
    conductor.value = respuesta[0].conductor;
    turno.value = respuesta[0].turno;
    hrs.value = respuesta[0].hrs;
    cajastotales.value = respuesta[0].golpestotales;
    cajasreales.value = respuesta[0].cajasreales;

    respuestaraw = await fetch("./php/producciones.php?tblctrltiempos&folio=" + idSeleccionado);
    respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
        body += `<tr><td>${element.folio}</td><td>${element.horainicio}</td><td>${element.horafinal}</td><td>${element.operacion}</td><td>${element.electrico}</td>
      <td>${element.mecanico}</td><td>${element.materias}</td><td>${element.grado}</td><td>${element.prev}</td><td>${element.servicios}</td><td>${element.subtotal}</td>
      <td>${element.seccion}</td><td>${element.modulo}</td><td>${element.motivo}</td><td>${element.correccion}</td></tr>`;
    });
    document.getElementById("tblctrltiempos").innerHTML = body;

    respuestaraw = await fetch("./php/producciones.php?tblpresentacionesbit&folio=" + idSeleccionado);
    respuesta = await respuestaraw.json();
    body = "";
    respuesta.forEach((element) => {
        body += `<tr><td>${element.presentacion}</td><td>${element.turno}</td><td>${element.hora}</td><td>${element.real}</td>
                <td>${element.acumulado}</td><td>${element.std}</td><td>${element.golpes}</td><td>${element.merma}</td></tr>`;
    })
    document.getElementById("tblpresentacionesbit").innerHTML = body;

}, 'resetEnc', 2)



window.deleteProduccion = (fila, event) => ProduccionEncObj.deleteProduccion(fila, event);