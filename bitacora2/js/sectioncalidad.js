import { Toolsjs } from "../../Tools/Tools.js";

export class BitCalidad {
    async tblCalidadsd(folio, tbl) {
        const respuestaraw = await fetch(
            "./php/bitacora.php?tblcalidad&folio=" + folio
        );
        const respuesta = await respuestaraw.json();
        let body = "";
        respuesta.forEach((element) => {
            body += `<tr data-id='${element.id}'><td>${element.inspeccionados}</td><td>${element.sd}</td><td>${element.ql}</td><td>${element.observacion}</td>
      <td><button class="btn btn-sm btn-warning" onclick="consultarCalidad(${element.id}); return false;"><i class="fa-solid fa-pen"></i></button></td></tr>`;
        });
        document.getElementById(tbl).innerHTML = body;
    }
    async savecalidad(id, folio, insp, sd, ql, obs) {
        const data = new FormData()
        data.append('id', id);
        data.append('folio', folio);
        data.append('insp', insp);
        data.append('sd', sd);
        data.append('ql', ql);
        data.append('obs', obs);
        const respuestaraw = await fetch('./php/bitacora.php?savecalidadsd', {
            method: 'POST',
            body: data
        })
        const respuesta = await respuestaraw.json();
        respuestaraw.status == 200 && swal.fire('Listo!!!', respuesta, 'success');
        respuestaraw.status == 500 && swal.fire('Error!!!', 'Hay un problema al guardar en la base de datos', 'error');
        const modal = bootstrap.Modal.getInstance(document.getElementById('CalidadModal'));
        modal.hide();
    }
    async consultarCalidadxID(id) {
        const respuestaraw = await fetch(
            "./php/bitacora.php?consultarCalidadxID&id=" + id
        );
        const respuesta = await respuestaraw.json();
        document.getElementById('idcalidad').value = respuesta[0].id;
        document.getElementById('inspeccionados').value = respuesta[0].inspeccionados;
        document.getElementById('sd').value = respuesta[0].sd;
        document.getElementById('ql').value = respuesta[0].ql;
        document.getElementById('sdobservaciones').value = respuesta[0].observacion;
        new bootstrap.Modal(document.getElementById('CalidadModal')).show();
    }
    limpiar() {
        document.getElementById('idcalidad').value = '';
        document.getElementById('inspeccionados').value = '';
        document.getElementById('sd').value = '';
        document.getElementById('ql').value = '';
        document.getElementById('sdobservaciones').value = '';
    }
}