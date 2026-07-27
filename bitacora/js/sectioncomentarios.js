export class Comentarios {
    async tblcomentarios(folio) {
        const respuestaraw = await fetch(
            "./php/bitacora.php?tblcomentarios&folio=" + folio
        );
        const respuesta = await respuestaraw.json();
        let body = "";
        respuesta.forEach((element) => {
            body += `<tr><td>${element.folio}</td><td>${element.seguridad}</td><td>${element.calidad}</td><td>${element.oyl}</td><td>${element.pendientes}</td>
      <td>${element.otros}</td><td><button class="btn btn-sm btn-warning" onclick="consultarComentarios(${element.id}); return false;"><i class="fa-solid fa-pen"></i></button></td></tr>`;
        });
        document.getElementById("tblcomentarios").innerHTML = body;
    }
    async consultarComentarios(id) {
        const respuestaraw = await fetch(
            "./php/bitacora.php?consultarcomentarios&id=" + id
        );
        const respuesta = await respuestaraw.json();
        document.getElementById("seguridad").value = respuesta[0].seguridad;
        document.getElementById("calidadcom").value = respuesta[0].calidad;
        document.getElementById("oyl").value = respuesta[0].oyl;
        document.getElementById("pendientes").value = respuesta[0].pendientes;
        document.getElementById("otros").value = respuesta[0].otros;
        document.getElementById("idregconsultado").value = respuesta[0].id;
        document.getElementById("editando").innerHTML = "Estas editando un registro";
    }
}
