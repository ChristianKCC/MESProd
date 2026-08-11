export class Tiempos {
  // async tblctrltiempos() {
  //   let folio = document.getElementById("folio").value;
  //   const respuestaraw = await fetch(
  //     "./php/bitacoraNueva.php?tblctrltiempos&folio=" + folio
  //   );
  //   const respuesta = await respuestaraw.json();
  //   let body = "";
  //   respuesta.forEach((element) => {
  //     body += `
  //           <tr>
  //               <td>${element.horainicio}</td>
  //               <td>${element.horafinal}</td>
  //               <td>${element.operacion}</td>
  //               <td>${element.electrico}</td>
  //               <td>${element.mecanico}</td>
  //               <td>${element.materias}</td>
  //               <td>${element.grado}</td>
  //               <td>${element.prev}</td>
  //               <td>${element.servicios}</td>
  //               <td>${element.subtotal}</td>
  //               <td>${element.seccion}</td>
  //               <td>${element.modulo}</td>
  //               <td>${element.motivo}</td>
  //               <td>${element.correccion}</td>
  //               <td>
  //                   <a class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalsanitizacion" data-bs-whatever="${element.id}">Sanitización</a>
  //               </td>
  //           </tr>`;
  //   });
  //   document.getElementById("tblctrltiempos").innerHTML = body;
  // }

  async tblInfoParos() {
    let folio = document.getElementById("folio").value;
    const dataPromise = await fetch(
      "./php/bitacoraNueva.php?tblInfoParos&folio=" + folio
    );
    const data = await dataPromise.json();
    let body = "";
    data.forEach((element) => {
      body += `
        <tr>
            <td>${element.Fecha ?? "Sin informacion"}</td>
            <td>${element.Hora ?? "Sin informacion"}</td>
            <td>${element.Seccion ?? "Sin informacion"}</td>
            <td>${element.Modulo ?? "Sin informacion"}</td>
            <td>${element.Cortes ?? "Sin informacion"}</td>
            <td>${element.Rechazos ?? "Sin informacion"}</td>
            <td>${element.TParo ?? "Sin informacion"}</td>
            <td>${element.Motivo ?? "Sin informacion"}</td>
            <td>${element.Correccion ?? "Sin informacion"}</td>
            <td>
            <a class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalsanitizacion" data-bs-whatever="${
              element.id
            }">Sanitización</a>
            </td>
        </tr>
      `;
    });
    document.getElementById("tblctrltiemposNuevo").innerHTML = body;
  }

  async consultarCtrlTiempos(id) {
    const respuestaraw = await fetch(
      "./php/bitacoraNueva.php?consultarctrltiempos&id=" + id
    );
    const respuesta = await respuestaraw.json();
    document.getElementById("horainicio").value = respuesta[0].horainicio;
    document.getElementById("horafinal").value = respuesta[0].horafinal;
    document.getElementById("operacion").value = respuesta[0].operacion;
    document.getElementById("electrico").value = respuesta[0].electrico;
    document.getElementById("mecanico").value = respuesta[0].mecanico;
    document.getElementById("materias").value = respuesta[0].materias;
    document.getElementById("grado").value = respuesta[0].grado;
    document.getElementById("prev").value = respuesta[0].prev;
    document.getElementById("servicios").value = respuesta[0].servicios;
    document.getElementById("seccion").value = respuesta[0].seccion;
    this.seccionChg(respuesta[0].seccion).then(() => {
      document.getElementById("modulo").value = respuesta[0].modulo;
    });
    document.getElementById("motivo").value = respuesta[0].motivo;
    document.getElementById("correccion").value = respuesta[0].correccion;
    document.getElementById("idregconsultado").value = respuesta[0].id;
    document.getElementById("editando").innerHTML =
      "Estas editando un registro";
  }
  async seccionChg(seccion) {
    const respuestaraw = await fetch(
      "../Components/CatalogosBitacoraNueva.php?GetModulosTiempos&seccion=" +
        seccion
    );
    const respuesta = await respuestaraw.json();
    let body = "<option value=''>Selecciona una opción</option>";
    respuesta.forEach((element) => {
      body += `<option value="${element.id}">${element.nombre}</option>`;
    });
    document.getElementById("modulo").innerHTML = body;
  }
  async saveSanitizacion(folio, motivo, tiempo, usuario, password, empleados) {
    const data = new FormData();
    const arrayaemp = this.getDataEmpleadoSant(empleados);
    data.append("folio", folio);
    data.append("motivo", motivo);
    data.append("tiempo", tiempo);
    data.append("usuario", usuario);
    data.append("password", password);
    data.append("empleados", JSON.stringify(arrayaemp));
    const respuestaraw = await fetch(
      "./php/bitacoraNueva.php?saveSanitizacion",
      {
        method: "POST",
        body: data,
      }
    );
    respuestaraw.status === 500 &&
      swal.fire(
        "Error",
        "Hay un problema al guardar en la base de datos",
        "error"
      );
    if (respuestaraw.status === 200) {
      swal.fire("Listo!!", "Se guardo la información correctamente", "success");
      this.limpiar();
      this.tblctrltiempos();
      const modal = document.getElementById("modalsanitizacion");
      const bootstrapModal = bootstrap.Modal.getInstance(modal);
      bootstrapModal.hide();
    }
    respuestaraw.status === 201 &&
      swal.fire(
        "Ups!!",
        "La contraseña es incorrecta o no tienes permiso de crear un registro ",
        "warning"
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
  limpiar() {
    document.getElementById("tblEmpSanitizacion").innerHTML = "";
    document.getElementById("motivosanitizacion").value = "";
    document.getElementById("tiemposanitizacion").value = "";
    document.getElementById("usuariosanitizacion").value = "";
    document.getElementById("passwordsanitizacion").value = "";
    document.getElementById("recipient-name").value = "";
    document.getElementById("noempsanitizacion").value = "";
    document.getElementById("nombresanitizacion").value = "";
  }
}
