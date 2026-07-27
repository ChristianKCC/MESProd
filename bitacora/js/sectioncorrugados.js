import { Toolsjs } from "../../Tools/Tools.js";
export class BitCorrugados {
    async tblcorrugados(folio) {
        const respuestaraw = await fetch(
            "./php/bitacora.php?tblcorrugados&folio=" + folio
        );
        const respuesta = await respuestaraw.json();
        let body = "";
        respuesta.forEach((element) => {
            body += `<tr><td>${element.folio}</td><td>${element.crecibidas}</td><td>${element.calmacen}</td><td>${element.cproducidas}</td><td>${element.centregadas}</td>
             <td>${element.claveproducto}</td><td><button class="btn btn-sm btn-warning" onclick="consultarCorrugado(${element.id}); return false;"><i class="fa-solid fa-pen"></i></button></td></tr>`;
        });
        document.getElementById("tblcorrugados").innerHTML = body;
    }

    async consultarCorrugado(id) {
        const respuestaraw = await fetch(
            "./php/bitacora.php?consultarcorrugado&id=" + id
        );
        const respuesta = await respuestaraw.json();
        document.getElementById("crecibidas").value = respuesta[0].crecibidas;
        document.getElementById("calmacen").value = respuesta[0].calmacen;
        document.getElementById("cproducidas").value = respuesta[0].cproducidas;
        document.getElementById("centregadas").value = respuesta[0].centregadas;
        document.getElementById("claveproducto").value = respuesta[0].claveproducto;
        document.getElementById("idregconsultado").value = respuesta[0].id;
        new bootstrap.Modal(document.getElementById('CorrugadosModal')).show();
    }
    async savecorrugados(idregconsultado, folio, crecibidas, calmacen, cproducidas, centregadas, claveproducto) {
        const form = new FormData();
        form.append("crecibidas", crecibidas);
        form.append("calmacen", calmacen);
        form.append("cproducidas", cproducidas);
        form.append("centregadas", centregadas);
        form.append("claveproducto", claveproducto);
        form.append("id", idregconsultado);
        form.append("folio", folio);
        const respuestaraw = await fetch(
            "./php/bitacora.php?guardacorrugados",
            {
                method: "POST",
                body: form,
            }
        );
        const respuesta = await respuestaraw.json();
        respuestaraw.status == 200 && swal.fire('Listo!!!', respuesta, 'success');
        respuestaraw.status == 500 && swal.fire('Error!!!', 'Hay un problema al guardar en la base de datos', 'error');
        const modal = bootstrap.Modal.getInstance(document.getElementById('CorrugadosModal'));
        modal.hide();
    }

    limpiar() {
        document.getElementById("crecibidas").value = '';
        document.getElementById("calmacen").value = '';
        document.getElementById("cproducidas").value = '';
        document.getElementById("centregadas").value = '';
        document.getElementById("claveproducto").value = '';
        document.getElementById("idregconsultado").value = '';
    }
}

