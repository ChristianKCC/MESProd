export class ValesMaterial {
  async datosemp(noemp) {
    const respuestaraw = await fetch(
      "../bitacora/php/bitacora.php?datosemp&noemp=" + noemp,
    );
    const respuesta = await respuestaraw.json();
    if (respuesta.length === 0) {
      document.getElementById("nombre").value = "";
      document.getElementById("puesto").value = "";
      return false;
    }
    return respuesta;
  }
  async SelectClasesM(clave1, clave2, clave3, clave4, maquinaid = "") {
    const data = new FormData();
    data.append("clave1", clave1);
    data.append("clave2", clave2);
    data.append("clave3", clave3);
    data.append("clave4", clave4);
    data.append("maquinaid", maquinaid);
    const respuestaraw = await fetch("../ValesE/php/Vales.php?ClaseMat", {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<option value='${element.NoClase}'>${element.Nombre}</option>`;
    });
    document.getElementById("clase").innerHTML = body;
    document.getElementById("tblmateriales").innerHTML = "";
  }
  async tblMateriales(idclase, clave1, clave2, clave3, clave4, maquinaid = "") {
    const data = new FormData();
    data.append("idclase", idclase);
    data.append("clave1", clave1);
    data.append("clave2", clave2);
    data.append("clave3", clave3);
    data.append("clave4", clave4);
    data.append("maquinaid", maquinaid);
    const respuestaraw = await fetch("../ValesE/php/Vales.php?tblMateriales", {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  async tblMaterialesAgregados(folio, maquinaid = "") {
    const data = new FormData();
    data.append("folio", folio);
    data.append("maquinaid", maquinaid);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?tblMaterialesAgregados",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  async addMaterial(idmat) {
    console.log(idmat);
    const folio = document.getElementById("foliovale").value;
    if (folio === "") {
      swal.fire("Ups!", "Debe haber un folio seleccionado", "warning");
      return false;
    }
    Swal.fire({
      title: "Envases Solicitados",
      input: "text",
      showCancelButton: true,
      confirmButtonText: "OK",
      cancelButtonText: "Cancelar",
    })
      .then(async (result) => {
        if (result.isConfirmed) {
          if (/^\d+(\.\d+)?$/.test(result.value)) {
            const data = new FormData();
            data.append("idmat", idmat);
            data.append("folio", folio);
            data.append("cantidad", result.value);
            const respuestaraw = await fetch(
              "../ValesE/php/Vales.php?addMaterial",
              {
                method: "POST",
                body: data,
              },
            );
            respuestaraw.ok
              ? null
              : swal.fire("Error", "Error al agregar el material", "error");
          } else {
            swal.fire("Hay un problema", "El numero no es valido", "warning");
          }
        }
      })
      .then(() => {
        const folio = document.getElementById("foliovale").value;
        this.tblMaterialesAgregados(folio).then((infotbl) => {
          let body = "";
          infotbl.forEach((listado) => {
            body += `<tr><td>${listado.folio}</td><td>${listado.NoMaterial}</td><td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
                    <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td><td>${listado.Cantidad}  ${listado.UM} </td>
                    <td><button class="btn btn-sm btn-danger" onclick="deleteMateriales(${listado.folio}); return false;"><i class="fas fa-backspace"></i></button></td></tr>`;
          });
          document.getElementById("tblmaterialesagregados").innerHTML = body;
        });
      });
  }
  async addMaterialadmin(idmat) {
    console.log(idmat);
    const folio = document.getElementById("folio").value;
    console.log(folio);
    if (folio === "") {
      swal.fire("Ups!", "Debe haber un folio seleccionado", "warning");
      return false;
    }
    const { value: cajas } = await Swal.fire({
      title: "Envases Solicitados",
      input: "number",
      inputLabel: "Cantidad de envases",
      inputPlaceholder: "Ingresa la cantidad de envases",
      showCancelButton: true,
      inputValidator: (value) => {
        if (!value) {
          return "¡Necesitas escribir algo!";
        }
      },
    });
    if (cajas) {
      swal.fire(`Envases solicitados: ${cajas}`);
    }
    let valor = prompt("Envases Solicitados", 0);
    if (/^\d+$/.test(valor)) {
      const data = new FormData();
      data.append("idmat", idmat);
      data.append("folio", folio);
      data.append("cantidad", valor);
      data.append("estado", 3);
      const respuestaraw = await fetch(
        "../ValesE/php/Vales.php?addMaterialadmin",
        {
          method: "POST",
          body: data,
        },
      );
      respuestaraw.ok
        ? null
        : swal.fire("Error", "Error al agregar el material", "error");
    } else {
      swal.fire("Hay un problema", "El numero no es valido", "warning");
    }
  }
  async deleteMateriales(folio) {
    const data = new FormData();
    data.append("folio", folio);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?deleteMateriales",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.ok
      ? null
      : swal.fire("Error", "Error al agregar el material", "error");
  }
  async validaUltimoVale() {
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?validaUltimoVale",
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  async creaVale(noemp, turno, clave1, clave2, clave3, clave4) {
    const data = new FormData();
    data.append("noemp", noemp);
    data.append("turno", turno);
    data.append("clave1", clave1);
    data.append("clave2", clave2);
    data.append("clave3", clave3);
    data.append("clave4", clave4);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?saveValeElectronico",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  async cancelarVale(folio) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("estado", 2);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?actualizaEstado",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.ok
      ? swal.fire("Listo", "Vale cancelado exitosamente", "success")
      : swal.fire("Error", "Hay un problema al cancelar el vale", "error");
  }
  async enviarVale(folio) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("estado", 3);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?actualizaEstado",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.ok
      ? swal.fire(
          "Listo",
          "El vale se envio correctamente a Materia Prima",
          "success",
        )
      : swal.fire("Error", "Hay un problema al enviar el vale", "error");
  }
  async ValidaEnvio(folio) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("estado", 4);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?actualizaEstado",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.ok
      ? swal.fire("Listo", "Se confirmo la recepcion del vale", "success")
      : swal.fire("Error", "Hay un problema al cancelar el vale", "error");
  }
  async CerrarVale(folio) {
    Swal.fire({
      title: "¿Estás seguro?",
      text: "No podrás realizar cambios una vez el vale se cierre!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Si, seguro!",
    }).then(async (result) => {
      if (result.isConfirmed) {
        const data = new FormData();
        data.append("folio", folio);
        data.append("estado", 5);
        const respuestaraw = await fetch(
          "../ValesE/php/Vales.php?actualizaEstado",
          {
            method: "POST",
            body: data,
          },
        );
        respuestaraw.ok
          ? swal.fire("Listo", "El vale se a cerrado", "success")
          : swal.fire("Error", "Hay un problema al cancelar el vale", "error");
        document.getElementById("tblValesCreados").innerHTML = "";
        document.getElementById("tblmaterialemodal").innerHTML = "";
      }
    });
  }
  async tblValesCreados(fechai, fechaf, turno, estado, maquina) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("turno", turno);
    data.append("estado", estado);
    data.append("maquina", maquina);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?tblValesCreados",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((listado) => {
      body += `<tr><td>${listado.maquina + " - " + listado.foliocons}</td><td>${
        listado.maquina
      }</td><td>${listado.noemp}</td><td>${listado.nombreEmp}</td><td>${
        listado.turno
      }</td>
            <td>${listado.clave1}</td><td>${listado.clave2}</td><td>${
              listado.clave3
            }</td><td>${listado.clave4}</td>
            <td>${
              listado.estadoid == 1
                ? '<i class="fas fa-circle text-info"></i>'
                : ""
            }
            ${
              listado.estadoid == 2
                ? '<i class="fas fa-circle text-danger"></i>'
                : ""
            }
            ${
              listado.estadoid == 3
                ? '<i class="fas fa-circle text-warning"></i>'
                : ""
            }
            ${
              listado.estadoid == 4
                ? '<i class="fas fa-circle text-success"></i>'
                : ""
            }
            ${
              listado.estadoid == 5
                ? '<i class="fas fa-circle text-secondary"></i>'
                : ""
            } ${listado.estado} </td>
            <td>${listado.fechacreado}</td><td>${listado.fechaenviado}</td>
            <td>${
              listado.estadoid == 4
                ? '<button class="btn btn-sm btn-warning" onclick="addmm2material(' +
                  listado.id +
                  ')" ><i class="fas fa-edit"></i></button>'
                : ""
            }</td>
            <td>${
              listado.estadoid == 4
                ? '<button class="btn btn-sm btn-danger" onclick="cerrarVale(' +
                  listado.id +
                  ')" ><i class="far fa-paper-plane"></i></button>'
                : ""
            }</td>
             </tr>`;
    });
    document.getElementById("tblValesCreados").innerHTML = body;
  }
  async tblValesautoriza(fechai, fechaf, turno, estado, maquina) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("turno", turno);
    data.append("estado", estado);
    data.append("maquina", maquina);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?tblValesCreados",
      {
        method: "POST",
        body: data,
      },
    );
    console.log(respuestaraw);
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((listado) => {
      body += `<tr><td>${listado.maquina + " - " + listado.foliocons}</td><td>${
        listado.maquina
      }</td><td>${listado.noemp}</td><td>${listado.nombreEmp}</td><td>${
        listado.turno
      }</td>
            <td>${listado.clave1}</td><td>${listado.clave2}</td><td>${
              listado.clave3
            }</td><td>${listado.clave4}</td>
            <td>${
              listado.estadoid == 1
                ? '<i class="fas fa-circle text-info"></i>'
                : ""
            }
            ${
              listado.estadoid == 2
                ? '<i class="fas fa-circle text-danger"></i>'
                : ""
            }
            ${
              listado.estadoid == 3
                ? '<i class="fas fa-circle text-warning"></i>'
                : ""
            }
            ${
              listado.estadoid == 4
                ? '<i class="fas fa-circle text-success"></i>'
                : ""
            }
            ${
              listado.estadoid == 5
                ? '<i class="fas fa-circle text-secondary"></i>'
                : ""
            } ${listado.estado} </td>
            <td>${listado.fechacreado}</td><td>${listado.fechaenviado}</td>
            <td><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalenv" data-bs-whatever="${
              listado.id
            }"><i class="fas fa-stream"></i></button></td>
            <td>${
              listado.estadoid == 4
                ? '<a href="PDF/Formato.php?folio=' +
                  listado.id +
                  '" target="_blank" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i></a>'
                : ""
            }</td>
            </tr>`;
    });
    document.getElementById("tblValesCreados").innerHTML = body;
  }
  async ValesConstxid(id) {
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?ValesConstxid&id=" + id,
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  async ValidaMatRemplazados(id) {
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?ValidaMatRemplazados&id=" + id,
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }
  limpiar() {
    document.getElementById("noemp").value = "";
    document.getElementById("nombre").value = "";
    document.getElementById("puesto").value = "";
    document.getElementById("clave1").value = "";
    document.getElementById("clave2").value = "";
    document.getElementById("clave3").value = "";
    document.getElementById("clave4").value = "";
    document.getElementById("clase").innerHTML = "";
    document.getElementById("foliovale").value = "";
    document.getElementById("foliocons").value = "";
    document.getElementById("tblmateriales").innerHTML = "";
    document.getElementById("tblmaterialesagregados").innerHTML = "";

    // Rehabilitar campos
    document.getElementById("noemp").disabled = false;
    document.getElementById("turnoen").disabled = false;
    document.getElementById("clave1").disabled = false;
    document.getElementById("clave2").disabled = false;
    document.getElementById("clave3").disabled = false;
    document.getElementById("clave4").disabled = false;
  }
  async CancelaMaterial(boton, folio) {
    const data = new FormData();
    data.append("folio", folio);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?CancelaMaterial",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.ok ? null : null;
    const fila = boton.parentNode.parentNode;
    fila.classList.add("text-danger");
  }
  async saveMM2(folio, mm2) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("mm2", mm2);
    const respuestaraw = await fetch("../ValesE/php/Vales.php?saveMM2", {
      method: "POST",
      body: data,
    });
    respuestaraw.ok ? console.log("exito") : console.log("error");
  }
  async saveEnvases(folio, envases) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("envases", envases);
    const respuestaraw = await fetch("../ValesE/php/Vales.php?saveEnvases", {
      method: "POST",
      body: data,
    });
    respuestaraw.ok ? console.log("exito") : console.log("error");
  }
  async tblValesReporte(fechai, fechaf, turno, estado, maquina) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("turno", turno);
    data.append("estado", estado);
    data.append("maquina", maquina);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?tblValesCreados",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((listado) => {
      body += `<tr><td>${listado.maquina + " - " + listado.foliocons}</td><td>${
        listado.maquina
      }</td><td>${listado.noemp}</td><td>${listado.nombreEmp}</td><td>${
        listado.turno
      }</td>
            <td>${listado.clave1}</td><td>${listado.clave2}</td><td>${
              listado.clave3
            }</td><td>${listado.clave4}</td>
            <td>${
              listado.estadoid == 1
                ? '<i class="fas fa-circle text-info"></i>'
                : ""
            }
            ${
              listado.estadoid == 2
                ? '<i class="fas fa-circle text-danger"></i>'
                : ""
            }
            ${
              listado.estadoid == 3
                ? '<i class="fas fa-circle text-warning"></i>'
                : ""
            }
            ${
              listado.estadoid == 4
                ? '<i class="fas fa-circle text-success"></i>'
                : ""
            }
            ${
              listado.estadoid == 5
                ? '<i class="fas fa-circle text-secondary"></i>'
                : ""
            } ${listado.estado} </td>
            <td>${listado.fechacreado}</td><td>${listado.fechaenviado}</td>
            <td><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalenv" data-bs-whatever="${
              listado.id
            }"><i class="fas fa-stream"></i></button></td>
            <td>${
              listado.estadoid == 4
                ? '<a href="PDF/Formato.php?folio=' +
                  listado.id +
                  '" target="_blank" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i></a>'
                : ""
            }</td>
            </tr>`;
    });
    document.getElementById("tblValesCreados").innerHTML = body;
  }
}

export class ConfClaves {
  async tblclases(busqueda) {
    const data = new FormData();
    data.append("busqueda", busqueda);
    const respuestaraw = await fetch("../ValesE/php/Vales.php?tblclasesConf", {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr><td>${element.id}</td><td>${element.noclase}</td><td>${element.descclase}</td>
            <td><button class="btn btn-sm btn-warning" onclick="editclases(${element.id})"><i class="fas fa-tools"></i></button></td></tr>`;
    });
    return body;
  }
  async tblclaves(busqueda) {
    const data = new FormData();
    data.append("busqueda", busqueda);
    const respuestaraw = await fetch("../ValesE/php/Vales.php?tblclavesConf", {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr>
                <td>${element.id}</td>
                <td>${element.Categoria ?? "Sin Información"}</td>
                <td>${element.noclave}</td>
                <td>${element.descclave}</td>
                <td>${element.Producto ?? "Sin Información"}</td>
                <td>${element.Etapa ?? "Sin Información"}</td>
                <td>${element.xcaja}</td>
                <td>${element.factor}</td>
                <td>
                  <button class="btn btn-sm btn-warning" onclick="editclaves(${element.id})">
                    <i class="fas fa-tools"></i>
                  </button>
                </td>
              </tr>`;
    });
    return body;
  }
  async saveClase(idclase, noclase, nombreclase) {
    const data = new FormData();
    data.append("idclase", idclase);
    data.append("noclase", noclase);
    data.append("nombreclase", nombreclase);
    const respuestaraw = await fetch("../ValesE/php/Vales.php?saveClase", {
      method: "POST",
      body: data,
    });
    respuestaraw.status == 200 &&
      swal.fire("Listo", "La acción de completo correctamente", "success");
    respuestaraw.status == 500 &&
      swal.fire("Ups", "Hay un problema al guardar la clase", "error");
    respuestaraw.status == 201 &&
      swal.fire("Ups", "Todos los campos son obligatorios", "warning");
    respuestaraw.status == 202 &&
      swal.fire("Ups", "Ya existe la clave", "warning");
  }
  async saveClave(formData) {
    try {
      const res = await axios.post(
        "../ValesE/php/Vales.php?saveClave",
        formData,
      );

      switch (res.status) {
        case 200:
          swal.fire(
            "Listo",
            res.data.mensaje || "La clave se guardó correctamente",
            "success",
          );
          return { ok: true, status: 200 };

        case 201:
          swal.fire("Ups", "Todos los campos son obligatorios", "warning");
          return { ok: false, status: 201 };

        case 202:
          swal.fire(
            "Ups",
            res.data.mensaje || "La clave ya está asignada a todas las máquinas seleccionadas",
            "warning",
          );
          return { ok: false, status: 202 };

        case 203:
          swal.fire(
            "Aviso",
            res.data.mensaje || "Se asignaron solo las máquinas nuevas",
            "info",
          );
          return { ok: true, status: 203 };

        default:
          swal.fire("Ups", "Hay un problema al guardar la clave", "error");
          return { ok: false, status: res.status };
      }
    } catch (error) {
      console.log("Error completo:", error);

      if (error.response) {
        switch (error.response.status) {
          case 201:
            swal.fire("Ups", "Todos los campos son obligatorios", "warning");
            return { ok: false, status: 201 };
          case 202:
            swal.fire(
              "Ups",
              error.response.data?.mensaje || "La clave ya está asignada a todas las máquinas",
              "warning",
            );
            return { ok: false, status: 202 };
          case 203:
            swal.fire(
              "Aviso",
              error.response.data?.mensaje || "Se asignaron solo las máquinas nuevas",
              "info",
            );
            return { ok: true, status: 203 };
          case 500:
            swal.fire(
              "Error",
              error.response.data?.mensaje || "Error interno del servidor",
              "error",
            );
            return { ok: false, status: 500 };
          default:
            swal.fire("Error", "Error inesperado del servidor", "error");
            return { ok: false, status: error.response.status };
        }
      } else if (error.request) {
        swal.fire(
          "Error",
          "No se pudo conectar al servidor. Verifica tu conexión.",
          "error",
        );
      } else {
        swal.fire("Error", "Error inesperado. Intenta nuevamente.", "error");
      }

      return { ok: false, status: -1, error };
    }
  }
  async saveMaterial(
    idmaterial,
    nomaterial,
    nombrematerial,
    ummaterial,
    ummat,
    montacargas,
    costos,
    tiempo,
  ) {
    const data = new FormData();
    data.append("idmaterial", idmaterial);
    data.append("nomaterial", nomaterial);
    data.append("nombrematerial", nombrematerial);
    data.append("ummaterial", ummaterial);
    data.append("ummat", ummat);
    data.append("montacargas", montacargas);
    data.append("costos", costos);
    data.append("tiempo", tiempo);
    const respuestaraw = await fetch("../ValesE/php/Vales.php?saveMaterial", {
      method: "POST",
      body: data,
    });
    respuestaraw.status == 200 &&
      swal.fire("Listo", "Se guardo correctamente el material", "success");
    respuestaraw.status == 500 &&
      swal.fire("Ups", "Hay un problema al guardar el material", "error");
    respuestaraw.status == 201 &&
      swal.fire("Ups", "Todos los campos son obligatorios", "warning");
    respuestaraw.status == 202 &&
      swal.fire("Ups", "Ya existe el No de Material", "warning");
  }
  async saveConvinacion(
    idconvinacion,
    maquinaconv,
    claseconv,
    claveconv,
    materialconv,
  ) {
    const data = new FormData();
    data.append("idconvinacion", idconvinacion);
    data.append("maquinaconv", maquinaconv);
    data.append("claseconv", claseconv);
    data.append("claveconv", claveconv);
    data.append("materialconv", materialconv);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?saveConvinacion",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.status == 200 &&
      swal.fire("Listo", "Se guardo correctamente la clave", "success");
    respuestaraw.status == 500 &&
      swal.fire("Ups", "Hay un problema al guardar la clave", "error");
    respuestaraw.status == 201 &&
      swal.fire("Ups", "Todos los campos son obligatorios", "warning");
    respuestaraw.status == 202 &&
      swal.fire("Ups", "Ya existe una convinación igual", "warning");
  }
  async tblmateriales(busqueda) {
    const data = new FormData();
    data.append("busqueda", busqueda);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?tblmaterialesConf",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      if (element.EstadoMaterial !== 1) {
        body += `<tr>
                  <td>${element.id}</td>
                  <td>${element.nomaterial}</td>
                  <td>${element.descmaterial}</td>
                  <td>${element.ummaterial}</td>
                  <td>${element.um}</td>
                  <td>${element.montacargas}</td>
                  <td>${element.TiempoMat}</td>
                  <td>
                    <button class="btn btn-sm btn-warning" onclick="editarmaterialbtn(${element.id})"><i class="fas fa-pen"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="borrarMaterialbtn(${element.id})"><i class="fas fa-trash"></i></button>
                  </td>
                  </tr>`;
      }
    });
    return body;
  }
  async cosultarxid(valor, route) {
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?" + route + "&id=" + valor,
    );
    const respuesta = await respuestaraw.json();
    return respuesta;
  }

  // ─────────────────────────────────────────────────────────────
  // editarClave — carga los datos en el modal y aplica la lógica
  // de categoría al abrir (xCajaValue, pesoBase, filtro máquinas)
  // ─────────────────────────────────────────────────────────────
  editarClave(
    id, idclave, noclave, descripcionclave, xcaja, factor,
    producto, tamaño, categoria, ustd, pesoBaseDiv, pesobase,
    anchoDiv, ancho, conteosBajos, claveconv, clavePuente,
  ) {
    const modal = new bootstrap.Modal(document.getElementById("modalClaves"));
    modal.show();

    return this.cosultarxid(id, "editclavexid").then((respuesta) => {
      // ── Campos básicos ───────────────────────────────────────
      document.getElementById(idclave).value          = respuesta[0].id;
      document.getElementById(noclave).value          = respuesta[0].noclave;
      document.getElementById(noclave).readOnly       = true;
      document.getElementById(descripcionclave).value = respuesta[0].descclave;
      document.getElementById(xcaja).value            = respuesta[0].xcaja;
      document.getElementById(factor).value           = respuesta[0].factor;
      document.getElementById(producto).value         = respuesta[0].producto  ?? "";
      document.getElementById(tamaño).value           = respuesta[0].tamaño    ?? "";
      document.getElementById(ustd).value             = respuesta[0].EquivalenciaUSTD;

      // ── Categoría: clonar para evitar listeners duplicados ───
      const categoriaEl      = document.getElementById(categoria);
      const categoriaElClone = categoriaEl.cloneNode(true);
      categoriaEl.parentNode.replaceChild(categoriaElClone, categoriaEl);
      categoriaElClone.value = respuesta[0].categoria ?? "";

      // ── Aplicar lógica de categoría al cargar ────────────────
      if (typeof window.aplicarLogicaCategoria === "function") {
        window.aplicarLogicaCategoria(respuesta[0].categoria);
      }

      // ── Filtros y visibilidad al cargar ──────────────────────
      const catStr = String(respuesta[0].categoria ?? "");

      this.#filtrarClaveProducto(catStr, producto);
      this.#filtrarClaveTamaño(catStr, tamaño);

      document.getElementById("xcajaDiv").style.display = catStr === "3" ? "none" : "";
      document.getElementById("ustdDiv").style.display  = catStr === "3" ? "none" : "";

      // ── Campos de pesoBase y ancho (solo si categoria === 15) ─
      if (respuesta[0].categoria === 15) {
        document.getElementById(pesobase).value = respuesta[0].pesoBase;
        document.getElementById(ancho).value    = respuesta[0].ancho;
      } else {
        document.getElementById(pesobase).value = "";
        document.getElementById(ancho).value    = "";
      }

      // ── Conteos bajos (categoria === 4) ──────────────────────
      if (respuesta[0].categoria == 4) {
        document.getElementById("claveconv").value   = respuesta[0].clavePuente        ?? "";
        document.getElementById("clavePuente").value = respuesta[0].Descripcion_Puente ?? "";
      } else {
        document.getElementById("claveconv").value   = "";
        document.getElementById("clavePuente").value = "";
      }

      // ── Listener para cambios manuales de categoría ──────────
      categoriaElClone.addEventListener("change", () => {
        const val = categoriaElClone.value;

        if (typeof window.aplicarLogicaCategoria === "function") {
          window.aplicarLogicaCategoria(val);
        }

        // Limpiar claveconv si no es categoria 4
        if (val != "4") {
          document.getElementById("claveconv").value   = "";
          document.getElementById("clavePuente").value = "";
        }

        // Filtros de selects
        this.#filtrarClaveProducto(val, producto);
        this.#filtrarClaveTamaño(val, tamaño);

        // Visibilidad de xcajaDiv y ustdDiv
        document.getElementById("xcajaDiv").style.display = val === "3" ? "none" : "";
        document.getElementById("ustdDiv").style.display  = val === "3" ? "none" : "";

        // Desmarcar todas las máquinas al cambiar categoría
        document.querySelectorAll("#maqGrid input[type=checkbox]").forEach((cb) => {
          cb.checked = false;
          cb.closest(".maq-chip")?.classList.remove("selected");
        });

        if (typeof window.actualizarSummary === "function") {
          window.actualizarSummary();
        }
      });

      // ── Precargar máquinas asignadas ─────────────────────────
      document.querySelectorAll("#maqGrid input").forEach((cb) => {
        cb.checked = false;
        cb.closest(".maq-chip")?.classList.remove("selected");
      });

      const maquinasAsignadas = respuesta
        .map((r) => String(r.maquina))
        .filter((m) => m !== "null" && m !== "undefined" && m != null);

      maquinasAsignadas.forEach((idMaq) => {
        const cb = document.querySelector(`#maqGrid input[value="${idMaq}"]`);
        if (cb) {
          cb.checked = true;
          cb.closest(".maq-chip")?.classList.add("selected");
        }
      });
    });
  }

  /**
 * Filtra las opciones del select de claveproducto.
 * Si categoría es "3" solo muestra 61 y 62; en otro caso restaura todo.
 *
 * @param {string} categoriaVal  - Valor actual del select de categoría
 * @param {string} productoId    - ID del elemento select de producto
 */
#filtrarClaveProducto(categoriaVal, productoId) {
  const selectClave       = document.getElementById(productoId);
  const VALORES_PERMITIDOS = ["61", "62"];

  Array.from(selectClave.options).forEach((option) => {
    if (option.value === "") return; // placeholder siempre visible

    if (categoriaVal === "3") {
      option.hidden   = !VALORES_PERMITIDOS.includes(option.value);
      option.disabled = !VALORES_PERMITIDOS.includes(option.value);
    } else {
      option.hidden   = false;
      option.disabled = false;
    }
  });

  // Si el valor actual quedó oculto, resetear el select
  if (
    categoriaVal === "3" &&
    !VALORES_PERMITIDOS.includes(selectClave.value)
  ) {
    selectClave.value = "";
  }
}

#filtrarClaveTamaño(categoriaVal, tamañoId) {
  const selectTamaño       = document.getElementById(tamañoId);
  const VALORES_PERMITIDOS = ["66", "67", "68", "69", "70"];

  Array.from(selectTamaño.options).forEach((option) => {
    if (option.value === "") return;

    if (categoriaVal === "3") {
      option.hidden   = !VALORES_PERMITIDOS.includes(option.value);
      option.disabled = !VALORES_PERMITIDOS.includes(option.value);
    } else {
      option.hidden   = false;
      option.disabled = false;
    }
  });

  if (
    categoriaVal === "3" &&
    !VALORES_PERMITIDOS.includes(selectTamaño.value)
  ) {
    selectTamaño.value = "";
  }
}

  editarClases(id, idclase, noclase, descripcionclase) {
    const modal = new bootstrap.Modal(document.getElementById("modalClases"));
    modal.show();
    this.cosultarxid(id, "editclasexid").then((respuesta) => {
      document.getElementById(idclase).value = respuesta[0].id;
      document.getElementById(noclase).value = respuesta[0].noclase;
      document.getElementById(noclase).readOnly = true;
      document.getElementById(descripcionclase).value = respuesta[0].descclase;
    });
  }
  editarMaterial(
    id,
    idmaterial,
    nomaterial,
    nombrematerial,
    ummaterial,
    ummat,
    montacargas,
    tiempo,
  ) {
    const modal = new bootstrap.Modal(document.getElementById("modalmaterial"));
    modal.show();
    this.cosultarxid(id, "editmaterialxid").then((respuesta) => {
      document.getElementById(idmaterial).value    = respuesta[0].id;
      document.getElementById(nomaterial).value    = respuesta[0].nomaterial;
      document.getElementById(nombrematerial).value = respuesta[0].descmaterial;
      document.getElementById(ummaterial).value    = respuesta[0].ummaterial;
      document.getElementById(ummat).value         = respuesta[0].um;
      document.getElementById(montacargas).value   = respuesta[0].montacargas;
      document.getElementById(tiempo).value        = respuesta[0].TiempoMat;
    });
  }
  async borrarMaterial(id) {
    const data = new FormData();
    data.append("id", id);
    const respuesta = await fetch("../ValesE/php/Vales.php?deleteMaterial", {
      method: "POST",
      body: data,
    });
    respuesta.ok
      ? swal.fire("Listo", "Se borro correctamente el material", "success")
      : swal.fire("Ups", "Hay un problema al borrar el material", "error");
  }
  async borrarClave(id) {
    console.log(id);
    const data = new FormData();
    data.append("id", id);
    const respuesta = await fetch("../ValesE/php/Vales.php?deleteClave", {
      method: "POST",
      body: data,
    });
    respuesta.ok
      ? swal.fire("Listo", "Se borro correctamente la clave", "success")
      : swal.fire("Ups", "Hay un problema al borrar la clave", "error");
  }
  editarConvinaciones(
    id,
    idcombinacion,
    idmaquina,
    idclave,
    idclase,
    idmaterial,
    nameclave,
    nameclase,
    namematerial,
  ) {
    const modal = new bootstrap.Modal(
      document.getElementById("modalConvinaciones"),
    );
    modal.show();
    this.cosultarxid(id, "editconvinacionesxid").then((respuesta) => {
      document.getElementById(idcombinacion).value = respuesta[0].idconv;
      document.getElementById(idmaquina).value     = respuesta[0].nomaquina;
      document.getElementById(idclave).value       = respuesta[0].noclave;
      document.getElementById(idclase).value       = respuesta[0].noclase;
      document.getElementById(idmaterial).value    = respuesta[0].nomaterial;
      document.getElementById(nameclave).value     = respuesta[0].nomclave;
      document.getElementById(nameclase).value     = respuesta[0].nomclase;
      document.getElementById(namematerial).value  = respuesta[0].nommaterial;
    });
  }
  async tblconvinaciones(busqueda) {
    const data = new FormData();
    data.append("busqueda", busqueda);
    const respuestaraw = await fetch(
      "../ValesE/php/Vales.php?tblconvinaciones",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr><td>${element.id}</td><td>${element.nomaquina}</td><td>${element.nommaquina}</td><td>${element.noclave}</td>
            <td>${element.nomclave}</td><td>${element.noclase}</td><td>${element.nomclase}</td><td>${element.nomaterial}</td><td>${element.nommaterial}</td>
            <td><button class="btn btn-sm btn-warning" onclick="editconvinacionesxid(${element.id})"><i class="fas fa-tools"></i></button></td></tr>`;
    });
    return body;
  }
  async slcautocomplete(e, autocompleteclaves, idval, ruta, text) {
    const query = e.target.value;
    document.getElementById(idval).value = "";
    if (!query) {
      autocompleteclaves.innerHTML = "";
      return;
    }
    const respuestaraw = await fetch(ruta + "&q=" + query);
    const respuesta    = await respuestaraw.json();
    autocompleteclaves.innerHTML = "";
    respuesta.forEach((item) => {
      const div = document.createElement("div");
      div.textContent = item.text;
      div.addEventListener("click", function () {
        document.getElementById(text).value  = item.text;
        document.getElementById(idval).value = item.id;
        autocompleteclaves.innerHTML = "";
      });
      autocompleteclaves.appendChild(div);
    });
  }
  async saveProducto(producto) {
    const data = new FormData();
    data.append("producto", producto);
    const respuestaraw = await fetch("../ValesE/php/Vales.php?saveProducto", {
      method: "POST",
      body: data,
    });
    respuestaraw.status == 200 &&
      swal.fire("Listo", "Se guardo correctamente el producto", "success");
    respuestaraw.status == 500 &&
      swal.fire("Ups", "Hay un problema al guardar el producto", "error");
  }
  async llnarslcMaquinas(dom, tipo = 0) {
    const respuestaraw = await fetch("../ValesE/php/Vales.php?getDataMaquinas");
    const respuesta    = await respuestaraw.json();
    const grid         = document.getElementById(dom);

    grid.innerHTML = `
      <div class="col-12 text-center text-muted py-3">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Cargando máquinas...
      </div>`;
    await new Promise((r) => setTimeout(r, 1000));

    try {
      const maquinas = respuesta;

      if (maquinas.length === 0) {
        grid.innerHTML = `<div class="col-12">
          <div class="alert alert-warning py-2 mb-0 small">
            <i class="bi bi-exclamation-triangle me-2"></i>
            No hay máquinas asignadas a tu departamento.
          </div></div>`;
        return;
      }

      grid.innerHTML = maquinas
        .map(
          (m) => `
          <div class="col-6 col-md-3">
            <label class="maq-chip d-flex align-items-center gap-2 w-100" id="chip-${m.NoMaquina}">
              <input type="checkbox" class="form-check-input mt-0 flex-shrink-0"
                    value="${m.NoMaquina}" onchange="toggleChip(this)" aria-label="${m.NombreMaquina}" />
              <div>
                <div class="maq-name">${m.NombreMaquina}</div>
              </div>
            </label>
          </div>
        `,
        )
        .join("");
    } catch (error) {
      grid.innerHTML = `<div class="col-12">
            <div class="alert alert-danger py-2 mb-0 small">
              <i class="bi bi-x-circle me-2"></i>
              Error al cargar las máquinas.
              <button class="btn btn-sm btn-outline-danger ms-2 py-0" onclick="cargarMaquinas()">Reintentar</button>
            </div></div>`;
    }
  }
}