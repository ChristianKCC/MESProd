export class BitTiempos {
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
    const respuestaraw = await fetch("php/TiemposNuevo.php?updateDataParo", {
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
}
