export class Consultas {
  async saveConsulta(
    noemp,
    checkActivoVal,
    departamento,
    puesto,
    categoria,
    subcategoria,
    cantidad,
    precio,
    folio
  ) {
    const data = new FormData();
    let ruta = "";
    data.append("noemp", noemp);
    data.append("empleadoActivo", checkActivoVal);
    data.append("departamento", departamento);
    data.append("puesto", puesto);
    data.append("categoria", categoria);
    data.append("subcategoria", subcategoria);
    data.append("cantidad", cantidad);
    data.append("precio", precio);
    data.append("folio", folio);

    // Asignar la ruta para guardar o actualizar el vale de productos

    folio == "" ? (ruta = "saveValeProductos") : (ruta = "updateValeProductos");

    const datapromise = await fetch("php/Consultas.php?" + ruta, {
      method: "POST",
      body: data,
    });

    datapromise.status === 200 &&
      swal.fire("Listo!!", "El registro se guardo con exito", "success");
    datapromise.status === 500 &&
      swal.fire("ERROR!!", "Hay un problema en la base de datos", "error");
  }

  async datauserEmpleado(noemp) {
    const data = new FormData();
    data.append("noemp", noemp);
    const datapromise = await fetch("php/Consultas.php?dataUserCompleate", {
      method: "POST",
      body: data,
    });
    const dataraw = await datapromise.json();
    return dataraw;
  }

  async dataProductos(idproducto) {
    const data = new FormData();
    data.append("idproducto", idproducto);
    const datapromise = await fetch("php/Consultas.php?dataProductsCompleate", {
      method: "POST",
      body: data,
    });
    const dataraw = await datapromise.json();
    return dataraw;
  }

  async dataProductosDos(numProduct) {
    const data = new FormData();
    data.append("numProduct", numProduct);
    const datapromise = await fetch(
      "php/Consultas.php?dataProductsCompleteDos",
      {
        method: "POST",
        body: data,
      }
    );
    const dataraw = await datapromise.json();
    console.log(dataraw);
    return dataraw;
  }

  async tblConsultaSession(dom) {
    const datapromise = await fetch("php/Consultas.php?tblConsultas");
    const dataraw = await datapromise.json();
    let body = "";
    dataraw.forEach((element) => {
      body += `
      <tr>
        <td>${element.idVale}</td>
        <td>${element.NoEmp}</td>
        <td>${element.sindicalizado == 1 ? "Si" : "No"}</td>
        <td>${element.nombre}</td>
        <td>${element.departamento}</td>
        <td>${element.puesto}</td>
        <td>${element.categoria}</td>
        <td>${element.subcategoria + element.descripcion}</td>
        <td>${element.cantidad}</td>
        <td>${element.precio}</td>
        <td>${element.fecha}</td>
        <td><button onclick="valeProducto(${
          element.idVale
        })" class="btn btn-sm btn-info">
          <i class="fas fa-file"></i>
        </button></td>
        <td>          
          ${
            element.adminValeProducto === 1
              ? `
                  <button onclick="editVale(${element.idVale})" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                  </button>
                `
              : ""
          }
        </td>
      </tr>`;
    });
    document.getElementById(dom).innerHTML = body;
  }

  async editVale(id) {
    const datapromise = await fetch(
      "php/Consultas.php?dataforeditVale&id=" + id
    );
    const dataraw = await datapromise.json();
    return dataraw;
  }

  async userSession() {
    const datapromise = await fetch("php/Consultas.php?dataUserSesion");
    const dataraw = await datapromise.json();
    return dataraw;
  }

  async comprobarFecha() {
    const datapromise = await fetch("php/Consultas.php?comprobarFecha");
    const dataraw = await datapromise.json();
    return dataraw;
  }

  async validarCantidadVales(noemp) {
    const data = new FormData();
    data.append("noemp", noemp);

    const response = await fetch("php/Consultas.php?validarCantidadValesTipo", {
      method: "POST",
      body: data,
    });
    const result = await response.json();
    return result;
  }

  async generarValeProducto(idVale) {
    try {
      const url = `php/valePDF.php?folio=${encodeURIComponent(idVale)}`;
      window.open(url, "_blank"); // Abre el PDF en una nueva pestaña
    } catch (error) {
      console.error("Error:", error);
    }
  }
}
