import { Toolsjs } from "../../Tools/Tools.js";
import { ValesMaterial } from "../module/ValesM.js"

const Tools = new Toolsjs();
const ValesReporte = new ValesMaterial();

Tools.llnarslc('CatalogoPersonal', 'GetSlcMaquinas', 'maquinas', 0);
Tools.llnarslc('CatalogosBitacora', 'GetValesEEstados', 'estadoslist', 0);
Tools.llnarslc('CatalogosBitacora', 'GetTurnos', 'turnoslist', 0);

document.getElementById('buscar').addEventListener('click', function (e) {
    e.preventDefault();
    const fechai = document.getElementById('fechai').value;
    const fechaf = document.getElementById('fechaf').value;
    const maquinas = document.getElementById('maquinas').value;
    const turno = document.getElementById('turnoslist').value;
    const estado = document.getElementById('estadoslist').value;
    (fechai === '' || fechaf === '') ?
        swal.fire('Ups', 'El rango de fecha es obligatorio', 'warning') :
        ValesReporte.tblValesReporte(fechai, fechaf, turno, estado, maquinas);
})
const modalenv = document.getElementById('modalenv');
modalenv.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget
    const recipient = button.getAttribute('data-bs-whatever')
    const modalBodyInput = modalenv.querySelector('.modal-body input')
    document.getElementById('folioencvista').innerHTML = 'Folio: ' + recipient
    let btncancelar = 0;
    ValesReporte.ValesConstxid(recipient).then((element) => {
        document.getElementById('clave1').value = element[0].clave1;
        document.getElementById('clave2').value = element[0].clave2;
        document.getElementById('clave3').value = element[0].clave3;
        document.getElementById('clave4').value = element[0].clave4;
        document.getElementById('noemp').value = element[0].noemp;
        document.getElementById('nombre').value = element[0].nombre;
        document.getElementById('maquina').value = element[0].maquina;
        document.getElementById('maquinaid').value = element[0].maquinaid;
        document.getElementById('turno').value = element[0].turno;
        document.getElementById('fechac').value = element[0].fechac;
        document.getElementById('fechae').value = element[0].fechae;
        document.getElementById('estado').value = element[0].estado;
        document.getElementById('folioencvista').innerHTML = 'Folio: ' + element[0].maquina + ' - ' + element[0].foliocons
        ValesReporte.tblMaterialesAgregados(recipient).then((infotbl) => {
            let body = '';
            infotbl.forEach(listado => {
                body += `<tr ${listado.estadomat == 2 && 'class="text-danger"'}><td>${listado.folio}</td><td>${listado.NoMaterial}</td>
                <td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
                <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td><td>${listado.Cantidad}  ${listado.UM} </td>
                <td>${listado.estadomat == 1 && 'Solicitado' || listado.estadomat == 2 && 'Remplazado' || listado.estadomat == 3 && 'Envio MP'}</td>
                <td>${listado.mm2kg != null ? listado.mm2kg : ''}</td><td>${listado.envasesrec != null ? listado.envasesrec : ''}</td></tr>`;
            })
            document.getElementById('tblmaterialesagregados').innerHTML = body;
        })
        modalBodyInput.value = recipient;
    });
})

document.getElementById('exportvaleexcel').addEventListener('click',(e)=>{
    e.preventDefault();
    Tools.exportartablaexcel('datavale');
})