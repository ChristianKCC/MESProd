
export class ReporteBitacora {
    inicia(folio) {
        const data = new FormData()
        data.append('folio',folio);
        (async function tblasistencias() {
            const [
                respuestarawasi,
                respuestarawcom,
                respuestarawctrl,
                respuestarawpres,
                respuestarawcorr,
            ] = await Promise.all([
                fetch("../bitacora/php/Reportebit.php?tblasistenciasbitrep", {
                    method: "POST",
                    body: data,
                }),
                fetch("../bitacora/php/Reportebit.php?tblcomentariosbitrep", {
                    method: "POST",
                    body: data,
                }),
                fetch("../bitacora/php/Reportebit.php?tblctrltiemposbitrep", {
                    method: "POST",
                    body: data,
                }),
                fetch("../bitacora/php/Reportebit.php?tblpresentacionesbitrep", {
                    method: "POST",
                    body: data,
                }),
                fetch("../bitacora/php/Reportebit.php?tblcorrugadosbitrep", {
                    method: "POST",
                    body: data,
                }),
            ]);
            const respuestaasi = await respuestarawasi.json();
            const respuestacom = await respuestarawcom.json();
            const respuestactrl = await respuestarawctrl.json();
            const respuesta = await respuestarawpres.json();
            const respuestacorr = await respuestarawcorr.json();
            let body = "";
            respuestaasi.forEach((element) => {
                body += `<tr><td>${element.folio}</td><td>${element.turno}</td><td>${element.noemp}</td><td>${element.nombre}</td><td>${element.puesto}</td></tr>`;
            });
            document.getElementById("tblasistenciasbot").innerHTML = body;
            body = "";
            respuestacom.forEach((element) => {
                body += `<tr><td>${element.folio}</td><td>${element.turno}</td><td>${element.seguridad}</td><td>${element.calidad}</td><td>${element.oyl}</td><td>${element.pendientes}</td>
          <td>${element.otros}</td></tr>`;
            });
            document.getElementById("tblcomentariosbitrep").innerHTML = body;
            body = "";
            respuestactrl.forEach((element) => {
                body += `<tr><td>${element.folio}</td><td>${element.turno}</td><td>${element.horainicio}</td><td>${element.horafinal}</td><td>${element.fechah}</td><td>${element.operacion}</td><td>${element.electrico}</td>
          <td>${element.mecanico}</td><td>${element.materias}</td><td>${element.grado}</td><td>${element.prev}</td><td>${element.servicios}</td><td>${element.subtotal}</td>
          <td>${element.seccion}</td><td>${element.modulo}</td><td>${element.motivo}</td><td>${element.correccion}</td></tr>`;
            });
            document.getElementById("tblctrltiemposbitrep").innerHTML = body;

            body = "";
            respuesta.forEach((element) => {
                body += `<tr><td>${element.presentacion}</td><td>${element.turno}</td><td>${element.hora}</td><td>${element.real}</td>
                <td>${element.acumulado}</td><td>${element.std}</td><td>${element.golpes}</td><td>${element.merma}</td></tr>`;
            })
            document.getElementById("tblpresentacionesbitrep").innerHTML = body;
            body = "";
            respuestacorr.forEach((element) => {
                body += `<tr><td>${element.folio}</td><td>${element.turno}</td><td>${element.crecibidas}</td><td>${element.calmacen}</td><td>${element.cproducidas}</td><td>${element.centregadas}</td>
          <td>${element.claveproducto}</td></tr>`;
            });
            document.getElementById("tblcorrugadosbitrep").innerHTML = body;
        })();
    }
}
