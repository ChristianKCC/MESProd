
export class BitAsistencias {
    async tblasistencias(folio) {
        const respuestaraw = await fetch(
            "./php/bitacora.php?tblasistencias&folio=" + folio
        );
        const respuesta = await respuestaraw.json();
        let body = "";
        respuesta.forEach((element) => {
            body += `<tr><td>${element.noemp}</td><td>${element.nombre}</td><td>${element.puesto}</td>
      <td><button class="btn btn-sm btn-warning" onclick="consultarAsistencia(${element.id}); return false;"><i class="fa-solid fa-pen"></i></button></td></tr>`;
        });
        document.getElementById("tblasistencias").innerHTML = body;
    }
    async consultarAsistencia(id) {
        const respuestaraw = await fetch(
            "./php/bitacora.php?consultarasistencia&id=" + id
        );
        const respuesta = await respuestaraw.json();
        new bootstrap.Modal(document.getElementById('asistenciasModal')).show();
        document.getElementById("noempasis").value = respuesta[0].noemp;
        document.getElementById("nombreasis").value = respuesta[0].nombre;
        document.getElementById("departamentoasis").value = respuesta[0].puesto;
        document.getElementById("puestoasis").value = respuesta[0].puesto;
        document.getElementById("idregconsultado").value = respuesta[0].id;
    }
    async saveAsistencia(id,folio,noemp) {
        const form = new FormData();
        form.append("noemp", noemp);
        form.append("folio", folio);
        form.append("id", id);
        const respuestaraw = await fetch("./php/bitacora.php?guardarasistencias", {
            method: "POST",
            body: form,
        });
        const respuesta = await respuestaraw.json();
        respuestaraw.status == 200 && swal.fire('Listo!!!', respuesta, 'success');
        respuestaraw.status == 500 && swal.fire('Error!!!', 'Hay un problema al guardar en la base de datos', 'error');
        const modal = bootstrap.Modal.getInstance(document.getElementById('asistenciasModal'));
        modal.hide();
    }
    limpiar(){
        document.getElementById("noempasis").value = '';
        document.getElementById("nombreasis").value = '';
        document.getElementById("departamentoasis").value = ''
        document.getElementById("puestoasis").value = '';
        document.getElementById("idregconsultado").value = '';
    }
}
