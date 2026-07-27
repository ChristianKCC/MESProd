import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();
async function tblRillReporte(idmaquina, fechai, fechaf) {
    const data = new FormData();
    data.append('idmaquina', idmaquina)
    data.append('fechai', fechai)
    data.append('fechaf', fechaf)
    const respuestaraw = await fetch('./php/rill.php?tblRillReporte', {
        method: 'POST',
        body: data
    });
    const respuesta = await respuestaraw.json();
    let body = '';
    let tipomat = '';
    let tipofolio = '';
    respuesta.forEach(res => {
        res.material == 0 ? tipomat = (res.material + ' ' + res.materialprueba + '(Material de prueba)') : tipomat = (res.material + ' ' + res.materialnombre);
        res.foliovalesril == 0 ? tipofolio = res.foliovalemanual + '(Papel)' : tipofolio = res.foliovaleconsmaq;
        body += `<tr><td>${res.id}</td><td>${res.clave}</td><td>${res.clase}</td><td>${tipomat}</td><td>${res.noemp + ' ' + res.empleadonombre}</td>
        <td>${res.lote}</td><td>${tipofolio}</td><td>${res.horaril}</td><td>${res.fecha}</td></tr>`;
    })
    return body;
}
Tools.llnarslc('CatalogosBitacora', 'GetMaquinasAll', 'maquina', 0);

document.getElementById('filtro').addEventListener('click', (e) => {
    e.preventDefault();
    const idmaquina = document.getElementById('maquina').value;
    const fechai = document.getElementById('fechai').value;
    const fechaf = document.getElementById('fechaf').value;
    if (idmaquina == '' || fechai == '' || fechaf == '') {
        swal.fire('Ups', 'Los campos maquina, fecha inicio y fecha final son obligatorios', 'warning')
        return false;
    }
    tblRillReporte(idmaquina, fechai, fechaf).then(data => {
        document.getElementById('rilltbl').innerHTML = data;
    })
})
document.getElementById('crearpdf').addEventListener('click',function(e){
    e.preventDefault();
    alert('crearpdf')
})
document.getElementById('crearexcel').addEventListener('click', (e) => {
	e.preventDefault();
	Tools.exportartablaexcel('tblrillidexce');
})
