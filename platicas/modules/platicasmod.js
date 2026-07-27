export class Platicas5min {

  // Funcion de muestra de modal
  async showUpdateModal(id, archivo) {    
    const link = document.getElementById("archivoActual");
    link.href = "php/" + archivo;
    link.textContent = archivo;
    
    document.getElementById("btnActualizarArchivo").dataset.id = id;

    const modal = new bootstrap.Modal(document.getElementById("modalUpdateArchivo"));
    modal.show();
  }

  // Funcion de actualizacion de archivo para platica de 5 min
  async updateArchivo() {
    const id = document.getElementById("btnActualizarArchivo").dataset.id;
    const fileInput = document.getElementById("nuevoArchivo");
    const file = fileInput.files[0];

    if (!file) {
      swal.fire("Ups!", "Debes seleccionar un archivo nuevo", "warning");
      return;
    }

    const data = new FormData();
    data.append("folio", id);
    data.append("file", file);

    const respuestaraw = await fetch("php/platicas.php?updatePlaticaArchivo", {
      method: "POST",
      body: data,
    });

    if (respuestaraw.status == 200) {
      swal.fire("Listo!", "Archivo actualizado correctamente", "success");
      this.tblPlaticas();
      bootstrap.Modal.getInstance(document.getElementById("modalUpdateArchivo")).hide();
    } else {
      swal.fire("Error!", "No se pudo actualizar el archivo", "error");
    }
  }

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
      body += `<tr>
            <td>${element.id}</td>
            <td>${element.noemp}</td>
            <td>${element.Nombre}</td>
            <td>${element.NombreDepto}</td>
            <td>${element.fecha.date.slice(0,-16)}</td>
            <td>${element.tipo}</td>
            <td>${element.nombreplatica}</td>
            <td>${element.minutos}</td>
            <td><a href="php/${element.archivo}">${element.archivo}</a></td>
            <td>
              <button 
                class="btn btn-sm btn-danger"
                onclick="deletePlatica(${element.id}); 
                return false;">
                  <i class="fa-solid fa-trash"></i>
              </button>
              <button 
                type="button" 
                class="btn btn-sm btn-warning"
                onclick="platicas5min.showUpdateModal(${element.id}, '${element.archivo}')">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
            </td>
          </tr>`;
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

