export class BitInspeccion {
  async saveInpeccion(datos) {
    const data = await fetch("php/Inspecciones.php?saveInspeccion", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(datos),
    });
    data.status == 200
      ? swal.fire(
          "Listo",
          "Se guarco correctamente en la base de datos",
          "success"
        )
      : swal.fire(
          "Error",
          "Hay un problema al guardar en la base de datos",
          "error"
        );
  }
  async tblInspeccion(folio, dom) {
    const data = await fetch(
      "php/Inspecciones.php?tblInspeccion&folio=" + folio
    );
    const dataraw = await data.json();
    let body = "";
    dataraw.forEach((element) => {
      body += `<tr><td>${element.id}</td><td>${element.fechasave}</td><td>${element.NoEmp}</td><td>${element.Nombre}</td><td>${element.Turno}</td>
        <td>${element.tipoinspeccion}</td><td>${element.seccionpre}</td><td>${element.comentarios}</td></tr>`;
    });
    document.getElementById(dom).innerHTML = body;
  }

  validarSeleccionCompleta(contenedorId) {
    const grupos = new Set();
    const radios = document.querySelectorAll(
      `#${contenedorId} input[type="radio"]`
    );
    radios.forEach((radio) => {
      grupos.add(radio.name);
    });
    for (let name of grupos) {
      const seleccionado = document.querySelector(
        `input[name="${name}"]:checked`
      );
      if (!seleccionado) {
        swal.fire("Ups!", "Todos los campos son obligatorios", "warning");
        return false;
      }
    }
    return true;
  }
}
