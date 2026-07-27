import { Toolsjs } from "../../Tools/Tools.js";

export class BitTiempos {
  async tblParos(folio) {
    const respuestaraw = await fetch("php/Tiempos.php?tblParos&folio=" + folio);
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<div class="col-3 mb-4">
                    <div class="card" style="width: 20rem;">
                    <div class="card-header">
                      Paro con hora:  ${element.hora}
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Sección: ${
                          element.nombreseccion === null
                            ? "Sin informacion"
                            : element.nombreseccion
                        }</li>
                        <li class="list-group-item">Módulo: ${
                          element.nombremodulo === null
                            ? "Sin informacion"
                            : element.nombremodulo
                        }</li>
                        <li class="list-group-item" hidden>Falla:  ${
                          element.nombrefalla
                        }</li>
                        <li class="list-group-item">Cortes:  ${
                          element.cortes
                        } Cortes</li>
                        <li class="list-group-item">Rechazos:  ${
                          element.rechazos
                        } Rechazos</li>
                        <li class="list-group-item">Rechazos corrida:  ${
                          element.rechazoscorrida
                        } </li>
                        <li class="list-group-item">Tiempo paro:  ${
                          element.tabajo === null
                            ? ""
                            : element.tabajo.toFixed(2)
                        } minutos</li>
                        <li class="list-group-item">Tiempo corrida:  ${
                          element.tarriba === null
                            ? ""
                            : element.tarriba.toFixed(2)
                        } minutos</li>
                    </ul>`;
      body +=
        element.estadoparo != 1
          ? `<button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalTiempos" data-bs-whatever="${element.id}">Completar</button>`
          : `<button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalTiempos" data-bs-whatever="${element.id}">Completo</button>`;
      body += `</div></div>`;
    });
    document.getElementById("Tiemposparos").innerHTML = body;
  }
  async getdataxidParos(folio) {
    const respuestaraw = await fetch(
      "php/Tiempos.php?tblParosxid&folio=" + folio
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  async updateDataParo(
    folio,
    seccion,
    modulo,
    falla,
    rechazos,
    tiempoparo,
    hora,
    rechazoscorrida,
    motivo,
    correccion
  ) {
    if (
      [
        folio,
        seccion,
        modulo,
        falla,
        rechazos,
        tiempoparo,
        hora,
        rechazoscorrida,
        motivo,
        correccion,
      ].includes("")
    ) {
      swal.fire("Ups!", "Todos los campos son obligatorios", "warning");
      return false;
    }

    const data = new FormData();
    data.append("folio", folio);
    data.append("seccion", seccion);
    data.append("modulo", modulo);
    data.append("falla", falla);
    data.append("rechazos", rechazos);
    data.append("tiempoparo", tiempoparo);
    data.append("hora", hora);
    data.append("rechazoscorrida", rechazoscorrida);
    data.append("motivo", motivo);
    data.append("correccion", correccion);

    const respuestaraw = await fetch("php/Tiempos.php?updateDataParo", {
      method: "POST",
      body: data,
    });

    // const respuesta = await respuestaraw.json();

    respuestaraw.status === 200 &&
      swal.fire("Listo!", "El registro se guardo con exito", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "Error",
        "Hay un problema para guardar en la base de datos",
        "error"
      );
  }
}
