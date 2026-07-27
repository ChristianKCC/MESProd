export class Platicas5min {
  async savePlatica(noemp, nombre, fecha, tipo, nombreplatica, minutos) {
    const fileInput = document.getElementById("archivoplatica");
    const file = fileInput.files[0];
    if (file) {
      const data = new FormData();
      data.append("noemp", noemp);
      data.append("nombre", nombre);
      data.append("fecha", fecha);
      data.append("tipo", tipo);
      data.append("nombreplatica", nombreplatica);
      data.append("minutos", minutos);
      data.append("file", file);
      const respuestaraw = await fetch("php/platicas.php?savePlaticas5min", {
        method: "POST",
        body: data,
      });
      respuestaraw.status == 200 &&
        swal.fire("Listo!", "Se agrego correctamente la platica", "success");
      if (respuestaraw.status == 500) {
        swal.fire(
          "Error!",
          "Hay un problema la guardar en la base de datos",
          "error"
        );
        return false;
      } else if (respuestaraw.status == 201) {
        swal.fire("ups!", "Todos los campos son obligatorios", "warning");
        return false;
      }
    } else {
      swal.fire("Ups!", "Debes cargar un archivo", "warning");
      return false;
    }
  }
  async tblPlaticas() {
    const respuestaraw = await fetch("php/platicas.php?tblPlaticas5min");
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr><td>${element.id}</td><td>${element.noemp}</td><td>${
        element.Nombre
      }</td><td>${element.NombreDepto}</td><td>${element.fecha.date.slice(
        0,
        -16
      )}</td>
            <td>${element.tipo}</td><td>${element.nombreplatica}</td><td>${
        element.minutos
      }</td><td><a href="php/${element.archivo}">${element.archivo}</a></td>
            <td><button class="btn btn-sm btn-danger" onclick="deletePlatica(${
              element.id
            }); return false;"><i class="fa-solid fa-trash"></i></button></td></tr>`;
    });
    document.getElementById("tblPlaticas").innerHTML = body;
  }
  async deletePlatica(folio) {
    const data = new FormData();
    data.append('folio',folio);
    const respuestaraw = await fetch("php/platicas.php?deletePlatica", {
      method: "POST",
      body: data,
    });
    if (respuestaraw.status == 500) {
      swal.fire(
        "Error!",
        "Hay un problema en la base de datos",
        "error"
      );
      return false;
    } else{
      swal.fire("Listo!", "Platica eliminada", "success");
      this.tblPlaticas();
    }
  }
}
