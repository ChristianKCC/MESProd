export class BitTiempos {
  // Antigua creacion de paros
  // async tblParos(folio) {
  //   const respuestaraw = await fetch("php/Tiempos.php?tblParos&folio=" + folio);
  //   const dataPromise = await fetch(
  //     "php/Tiempos.php?tblParosAutomaticos&folio=" + folio
  //   );
  //   console.log(dataPromise);
  //   const respuesta = await respuestaraw.json();
  //   const dataResponse = await dataPromise.json();
  //   console.log(dataResponse);
  //   let body = "";
  //   respuesta.forEach((element) => {
  //     body += `<div class="col-3 mb-4">
  //                   <div class="card" style="width: 20rem;">
  //                   <div class="card-header">
  //                     Paro con hora:  ${element.hora}
  //                   </div>
  //                   <ul class="list-group list-group-flush">
  //                       <li class="list-group-item">SECCION: ${
  //                         element.nombreseccion == null
  //                           ? "Sin información"
  //                           : element.nombreseccion
  //                       }</li>
  //                       <li class="list-group-item">MODULO: ${
  //                         element.nombremodulo == null
  //                           ? "Sin información"
  //                           : element.nombremodulo
  //                       }</li>
  //                       <li class="list-group-item">FALLA:  ${
  //                         element.nombrefalla
  //                       }</li>
  //                       <li class="list-group-item">Cortes:  ${
  //                         element.cortes
  //                       } Cortes</li>
  //                       <li class="list-group-item">Rechazos:  ${
  //                         element.rechazos
  //                       } Rechazos</li>
  //                       <li class="list-group-item">Tiempo paro:  ${
  //                         element.tiempoparo === null
  //                           ? ""
  //                           : element.tiempoparo.toFixed(0)
  //                       } minutos</li>
  //                       <li class="list-group-item">Tiempo corrida:  ${
  //                         element.tarriba === null
  //                           ? ""
  //                           : element.tarriba.toFixed(0)
  //                       } minutos</li>
  //                   </ul>`;
  //     body += `<ul><li style="list-style:none; padding-top: 15px;">`;
  //     body +=
  //       element.estadoparo != 1
  //         ? `<button type="button" class="btn btn-sm bg-target" data-bs-toggle="modal" data-bs-target="#modalTiempos" data-bs-whatever="${element.id}"><i class="fa-solid fa-list-check"></i> Completar</button>
  //           <button type="button" class="btn btn-sm btn-danger" onclick="eliminarParo(${element.id})" data-bs-whatever="${element.id}"><i class="fa-solid fa-trash"></i> Eliminar</button>`
  //         : `<button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalTiempos" data-bs-whatever="${element.id}">Completo</button>`;
  //     body += `</li></ul></div></div>`;
  //   });
  //   document.getElementById("Tiemposparos").innerHTML = body;
  // }
  // Nueva creeacion de paros
  async tblParos(folio) {
    // const respuestaraw = await fetch("php/Tiempos.php?tblParos&folio=" + folio);
    const dataPromise = await fetch(
      "php/Tiempos.php?tblParosAutomaticos&folio=" + folio
    );
    // const respuesta = await respuestaraw.json();
    const dataResponse = await dataPromise.json();
    let body = "";
    dataResponse.forEach((element) => {
      body += `<div class="col-3 mb-4">
                    <div class="card" style="width: 20rem;">
                    <div class="card-header">
                      Paro con hora:  ${element.HoraParo}
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">SECCION: ${
                          element.Seccion == null
                            ? "Sin información"
                            : element.Seccion
                        }</li>
                        <li class="list-group-item">MODULO: ${
                          element.Modulo == null
                            ? "Sin información"
                            : element.Modulo
                        }</li>
                        <li class="list-group-item">FALLA:  ${
                          element.Falla == null
                            ? "Sin información"
                            : element.Falla
                        }</li>
                        <li class="list-group-item">Cortes:  ${
                          element.Cortes
                        } Cortes</li>
                        <li class="list-group-item">Rechazos:  ${
                          element.Rechazos
                        } Rechazos</li>
                        <li class="list-group-item">Tiempo paro:  ${
                          element.TiempoParo === null ? "" : element.TiempoParo
                        } minutos</li>
                    </ul>`;
      body += `<ul><li style="list-style:none; padding-top: 15px;">`;
      body +=
        element.EstadoParo != 1
          ? `<button type="button" class="btn btn-sm bg-target" data-bs-toggle="modal" data-bs-target="#modalTiempos" data-bs-whatever="${element.id}"><i class="fa-solid fa-list-check"></i> Completar</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarParo(${element.id})" data-bs-whatever="${element.id}"><i class="fa-solid fa-trash"></i> Eliminar</button>`
          : `<button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalTiempos" data-bs-whatever="${element.id}">Completo</button>`;
      body += `</li></ul></div></div>`;
    });
    document.getElementById("Tiemposparos").innerHTML = body;
  }
  // async getdataxidParos(folio) {
  //   const respuestaraw = await fetch(
  //     "php/Tiempos.php?tblParosxid&folio=" + folio
  //   );
  //   const respuesta = await respuestaraw.json();
  //   return respuesta;
  // }

  async dataParosAutomaticos(folio) {
    const respuestaraw = await fetch(
      "php/Tiempos.php?parosAutomaticosxid&folio=" + folio
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }

  async updateDataParo(
    folio,
    seccion,
    modulo,
    motivo,
    correccion,
    motivosanitizacion,
    tiemposanitizacion,
    empleados
  ) {
    if ([folio, seccion, modulo, motivo, correccion].includes("")) {
      swal.fire("Ups!", "Todos los campos son obligatorios", "warning");
      return false;
    }
    const arrayaemp = this.getDataEmpleadoSant(empleados);
    const data = new FormData();
    data.append("folio", folio);
    data.append("seccion", seccion);
    data.append("modulo", modulo);
    data.append("motivo", motivo);
    data.append("correccion", correccion);
    data.append("motivosanitizacion", motivosanitizacion);
    data.append("tiemposanitizacion", tiemposanitizacion);
    data.append("empleados", JSON.stringify(arrayaemp));
    const respuestaraw = await fetch("php/Tiempos.php?updateDataParo", {
      method: "POST",
      body: data,
    });

    respuestaraw.status === 200 &&
      swal.fire("Listo!!", "El registro se actualizo con exito", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "Error",
        "Hay un problema para guardar en la base de datos",
        "error"
      );
  }

  getDataEmpleadoSant(tbl) {
    const table = document.getElementById(tbl);
    const employees = [];
    for (let i = 1; i < table.rows.length; i++) {
      const row = table.rows[i];
      const id = row.cells[0].innerText;
      const name = row.cells[1].innerText;
      employees.push({ id: id, name: name });
    }
    return employees;
  }

  async infoSanitizacion(folio) {}

  async crearNuevoParo(
    folio,
    seccion,
    modulo,
    cortes,
    rechazos,
    tiempoparo,
    hora,
    motivo,
    correccion,
    usuario,
    password
  ) {
    if (
      [
        seccion,
        modulo,
        cortes,
        rechazos,
        tiempoparo,
        hora,
        motivo,
        correccion,
        usuario,
        password,
      ].includes("")
    ) {
      swal.fire("Ups!", "Todos los campos son obligatorios", "warning");
      return false;
    }

    const data = new FormData();
    data.append("folio", folio);
    data.append("seccion", seccion);
    data.append("modulo", modulo);
    data.append("cortes", cortes);
    data.append("rechazos", rechazos);
    data.append("tiempoparo", tiempoparo);
    data.append("hora", hora);
    data.append("motivo", motivo);
    data.append("correccion", correccion);
    data.append("usuario", usuario);
    data.append("password", password);

    const dataPromise = await fetch("php/Tiempos.php?crearNuevoParo", {
      method: "POST",
      body: data,
    });

    dataPromise.status === 200 &&
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    dataPromise.status === 401 &&
      swal.fire(
        "ERROR!!",
        "Las credenciales de acceso son incorrectas",
        "error"
      );
    dataPromise.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
    dataPromise.status === 501 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }

  async eliminarParo(folio, usuario, password) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("usuario", usuario);
    data.append("password", password);
    const respuestaraw = await fetch("php/Tiempos.php?eliminarParo", {
      method: "POST",
      body: data,
    });
    respuestaraw.status === 200 &&
      swal.fire("Listo!!", "El registro se elimino con exito", "success");
    respuestaraw.status === 401 &&
      swal.fire(
        "ERROR!!",
        "Las credenciales de acceso son incorrectas",
        "error"
      );
    respuestaraw.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
    respuestaraw.status === 501 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }

  async tblEmpleadosSanitizacion(folioParo) {
    // Promesa de peticion GET para obtener los datos del servidor
    const datPromise = await fetch(
      "php/Tiempos.php?tablEmpleadosSanitizacion&folio=" + folioParo
    );

    // Convertir la respuesta en formato JSON
    const respuesta = await datPromise.json();
    // Dibujo e iserccion de los datos en tabla HTML
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr>
                  <td>${element.NoEmp}</td>
                  <td>${element.Nombre}</td>
              </tr>`;
    });
    document.getElementById("tblEmpSanitizacionNew").innerHTML = body;
  }
}
