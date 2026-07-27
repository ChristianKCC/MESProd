export class Maquinas {
  // Funcion asincrona que espera la respuesta de informacion para editar la informacion
  async consultarxid(clave, ruta) {
    const dataPromise = await fetch(
      "php/Maquinas.php?" + ruta + "&id=" + clave
    );
    const resp = await dataPromise.json();
    console.log(resp);
    return resp;
  }

  //--------------------------- INICIO DE FUNCIONES PARA SECCIONES -----------------------------------
  async tblSecciones(busqueda) {
    const data = new FormData();
    data.append("busqueda", busqueda);
    const dataPromise = await fetch("php/Maquinas.php?tblDatosSecciones", {
      method: "POST",
      body: data,
    });
    const resp = await dataPromise.json();
    let bodyTable = "";
    resp.forEach((element) => {
      bodyTable += `
        <tr>
            <td>${element.ID}</td>
            <td>${element.NombreSeccion}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editSecciones(${element.ID})"><i class="fas fa-tools"></i></button>
            </td>
        </tr>
      `;
    });

    return bodyTable;
  }

  // FUncion para obtener la informacion de la seccion a editar
  async editSeccion(id, idSeccion, noSeccion, nombreSeccion) {
    const modal = new bootstrap.Modal(
      document.getElementById("modalUpdateSecciones")
    );
    modal.show();

    this.consultarxid(id, "editseccionxid").then((resp) => {
      document.getElementById(idSeccion).value = resp[0].ID;
      document.getElementById(noSeccion).value = resp[0].ID;
      document.getElementById(nombreSeccion).value = resp[0].NombreSeccion;
    });
  }

  // Funcion para guardar la seccion
  async saveSeccion(idSeccion, nombreSeccion) {
    const data = new FormData();
    let ruta = "";
    data.append("idSeccion", idSeccion);
    data.append("nombreSeccion", nombreSeccion);

    idSeccion == "" ? (ruta = "saveNuevaSeccion") : (ruta = "updateSecciones");

    const dataResp = await fetch("php/Maquinas.php?" + ruta, {
      method: "POST",
      body: data,
    });

    dataResp.status == 200 &&
      swal.fire("Listo!!!", "Se guardó la sección correctamente", "success");
    dataResp.status == 500 &&
      swal.fire("Ups!!!", "Hay un problema al guardar la clase", "error");
    dataResp.status == 201 &&
      swal.fire("Ups!!!", "Todos los campos son obligatorios", "warning");
    dataResp.status == 202 && swal.fire("Ups", "Ya existe la clave", "warning");
  }

  //--------------------------- FIN DE FUNCIONES PARA SECCIONES -----------------------------------

  //--------------------------- INICIO DE FUNCIONES PARA MODULOS -----------------------------------
  async tblModulos(busqueda) {
    const data = new FormData();
    data.append("busqueda", busqueda);
    const dataPromise = await fetch("php/Maquinas.php?tblDatosModulos", {
      method: "POST",
      body: data,
    });

    const resp = await dataPromise.json();
    let bodyTable = "";
    resp.forEach((element) => {
      bodyTable += `
      <tr>
        <td>${element.ID}</td>
        <td>${element.NombreModulo}</td>
        <td>
            <button class="btn btn-sm btn-warning" onclick="editModulos(${element.ID})"><i class="fas fa-tools"></i></button>
        </td>
      </tr>`;
    });
    return bodyTable;
  }

  async editModulo(id, idModulo, noModulo, nombreModulo) {
    const modal = new bootstrap.Modal(
      document.getElementById("modalUpdateModulos")
    );
    modal.show();

    this.consultarxid(id, "editmoduloxid").then((resp) => {
      document.getElementById(idModulo).value = resp[0].ID;
      document.getElementById(noModulo).value = resp[0].ID;
      document.getElementById(nombreModulo).value = resp[0].NombreModulo;
    });
  }

  async saveModulo(idModulo, nombreModulo) {
    const data = new FormData();
    let ruta = "";
    data.append("idModulo", idModulo);
    data.append("nombreModulo", nombreModulo);

    idModulo == "" ? (ruta = "saveNuevoModulo") : (ruta = "updateModulo");

    const dataResp = await fetch("php/Maquinas.php?" + ruta, {
      method: "POST",
      body: data,
    });

    dataResp.status == 200 &&
      swal.fire("Listo!!!", "Se guardó el módulo correctamente", "success");
    dataResp.status == 500 &&
      swal.fire("Ups!!!", "Hay un problema al guardar la clase", "error");
    dataResp.status == 201 &&
      swal.fire("Ups!!!", "Todos los campos son obligatorios", "warning");
    dataResp.status == 202 && swal.fire("Ups", "Ya existe la clave", "warning");
  }

  //-------------------------------- FIN DE FUNCIONES PARA MODULOS

  //------------------------------- INICIO DE FUNCIONES PARA COMBINACIONES
  async tblCombinaciones(busqueda) {
    console.log(busqueda);
    const data = new FormData();
    data.append("busqueda", busqueda);
    const dataPromise = await fetch("php/Maquinas.php?tblDatosCombinaciones", {
      method: "POST",
      body: data,
    });

    const resp = await dataPromise.json();
    console.log(resp);
    let bodyTable = "";
    resp.forEach((element) => {
      bodyTable += `
        <tr>
            <td>${element.IDComb}</td>
            <td>${element.NoMaquina}</td>
            <td>${element.NombMaquina}</td>
            <td>${element.IDSecc}</td>
            <td>${element.NombSeccion}</td>
            <td>${element.IDModulo}</td>
            <td>${element.NombModulo}</td>
            <td>${element.IDFallas}</td>
            <td>${element.NombFalla}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editconvinacionesxid(${element.IDComb})">
                    <i class="fas fa-tools"></i>
                </button>
            </td>
        </tr>
      `;
    });
    return bodyTable;
  }

  async slcautocomplete(e, autocomplete, idVal, ruta, text) {
    console.log(e);
    console.log(autocomplete);
    console.log(idVal);
    console.log(ruta);
    console.log(text);
    const query = e.target.value;
    document.getElementById(idVal).value = "";

    if (!query) {
      autocomplete.innerHTML = "";
      return;
    }

    const dataPromise = await fetch(ruta + "&q=" + query);
    const respuesta = await dataPromise.json();
    console.log(respuesta);
    autocomplete.innerHTML = "";

    respuesta.forEach((item) => {
      const div = document.createElement("div");
      div.textContent = item.Nombre;
      div.addEventListener("click", function () {
        document.getElementById(text).value = item.Nombre;
        document.getElementById(idVal).value = item.idVal;
        autocomplete.innerHTML = "";
      });
      autocomplete.appendChild(div);
    });
  }

  async saveCombinacion(
    idconvinacion,
    maquinaconv,
    seccionConb,
    moduloConb,
    fallaConb
  ) {
    const data = new FormData();
    data.append("idconvinacion", idconvinacion);
    data.append("maquinaconv", maquinaconv);
    data.append("seccionConb", seccionConb);
    data.append("moduloConb", moduloConb);
    data.append("fallaConb", fallaConb);

    const dataPromise = await fetch("php/Maquinas.php?saveCombinacion", {
      method: "POST",
      body: data,
    });

    console.log(dataPromise);
    dataPromise.status == 200 &&
      swal.fire("Listo!!!", "Se guardó correctamente la clave", "success");
    dataPromise.status == 500 &&
      swal.fire("Ups!!!", "Hay un problema al guardar la clave", "error");
    dataPromise.status == 201 &&
      swal.fire("Ups!!!", "Todos los campos son obligatorios", "warning");
    dataPromise.status == 202 &&
      swal.fire("Ups!!!", "Ya existe una convinación igual", "warning");
  }

  async editconvinaciones(
    id,
    idCombinacion,
    idMaquina,
    idSeccion,
    idModulo,
    idFalla,
    nameSeccion,
    nameModulo,
    nameFalla
  ) {
    const modal = new bootstrap.Modal(
      document.getElementById("modalCombinaciones")
    );
    modal.show();
    this.consultarxid(id, "editCombinaciones").then((respuesta) => {
      document.getElementById(idCombinacion).value = respuesta[0].idConb;
      document.getElementById(idMaquina).value = respuesta[0].idMaquina;
      document.getElementById(idSeccion).value = respuesta[0].idSecc;
      document.getElementById(idModulo).value = respuesta[0].idMod;
      document.getElementById(idFalla).value = respuesta[0].idFalla;
      document.getElementById(nameSeccion).value = respuesta[0].NombSeccion;
      document.getElementById(nameModulo).value = respuesta[0].NombModulo;
      document.getElementById(nameFalla).value = respuesta[0].NombFalla;
    });
  }

  //--------------------------- FIN DE FUNCIONES PARA COMBINACIONES -----------------------------------

  //--------------------------- INICIO DE FUNCIONES PARA FALLAS -----------------------------------

  async tblFallas(busqueda) {
    const data = new FormData();
    data.append("busqueda", busqueda);
    const dataPromise = await fetch("php/Maquinas.php?tblDatosFallas", {
      method: "POST",
      body: data,
    });

    const resp = await dataPromise.json();
    let bodyTable = "";
    resp.forEach((element) => {
      bodyTable += `
      <tr>
        <td>${element.ID}</td>
        <td>${element.NombreFalla}</td>
        <td>
            <button class="btn btn-sm btn-warning" onclick="editFallas(${element.ID})"><i class="fas fa-tools"></i></button>
        </td>
      </tr>`;
    });
    return bodyTable;
  }

  async editFalla(id, idFalla, noFalla, nombreFalla) {
    const modal = new bootstrap.Modal(
      document.getElementById("modalUpdateFallas")
    );
    modal.show();

    this.consultarxid(id, "editFallaxid").then((resp) => {
      document.getElementById(idFalla).value = resp[0].ID;
      document.getElementById(noFalla).value = resp[0].ID;
      document.getElementById(nombreFalla).value = resp[0].NombreFalla;
    });
  }

  async saveFalla(idFalla, nombreFalla) {
    const data = new FormData();
    let ruta = "";
    data.append("idFalla", idFalla);
    data.append("nombreFalla", nombreFalla);

    idFalla == "" ? (ruta = "saveNuevaFalla") : (ruta = "updateFalla");

    const dataResp = await fetch("php/Maquinas.php?" + ruta, {
      method: "POST",
      body: data,
    });

    dataResp.status == 200 &&
      swal.fire("Listo!!!", "Se guardó la falla correctamente", "success");
    dataResp.status == 500 &&
      swal.fire("Ups!!!", "Hay un problema al guardar la clase", "error");
    dataResp.status == 201 &&
      swal.fire("Ups!!!", "Todos los campos son obligatorios", "warning");
    dataResp.status == 202 && swal.fire("Ups!!!", "Ya existe la clave", "warning");
  }
}
