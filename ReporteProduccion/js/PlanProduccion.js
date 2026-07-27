import { Toolsjs } from "../../Tools/Tools.js";
import { ReporteProduccionTurnos } from "../modules/dataTurnosAnteriores.js";
const ReporteTurnosObj = new ReporteProduccionTurnos();
const Tools = new Toolsjs();

let infoPlanProduccion = [];
let currentPage = 1;
let pageSize = 15;

const idsCamposObligatorios = [
  "configuracion",
  "maquina",
  "clave",
  "fecha",
  "STD",
];

// ToolObj.llnarslc("CatalogoMaquinas", "GetSlcMaquinas", "maquina", 0);
ReporteTurnosObj.llnarslcMaquinas("maquina");

function obtenerDatosFormulario() {
  return {
    folio: document.getElementById("folio").value,
    configuracion: document.getElementById("configuracion").value,
    clave: document.getElementById("clave").value,
    descripcion: document.getElementById("descripcion").value,
    fecha: document.getElementById("fecha").value,
    maquina: document.getElementById("maquina").value,
    STD: document.getElementById("STD").value,
  };
}

document.getElementById("maquina").addEventListener("change", async (e) => {
  e.preventDefault();
  const tipoEl = document.getElementById("tipoPrograma");
  const tituloMaquina = document.getElementById("tituloMaquina");
  if (tipoEl) {
    tipoEl.hidden = false;
    tipoEl.classList.remove("d-none", "hidden");
    tipoEl.style.display = "";
    if (document.getElementById("maquina").value === "85") {
      if (tituloMaquina) tituloMaquina.textContent = "PROGRAMA MENSUAL MM2";
    } else {
      tituloMaquina.textContent = "PROGRAMA MENSUAL USTD";
    }
  }
});

async function reddata() {
  Tools.validarCamposPorID(idsCamposObligatorios) !== false &&
    (await guardarDatos());
}
async function guardarDatos() {
  const data = obtenerDatosFormulario();
  let ruta = "";
  data.folio == ""
    ? (ruta = "RegistrarPlanProduccion")
    : (ruta = "actualizarPlanProduccion");

  const promesa = await fetch("php/producciones.php?" + ruta, {
    method: "POST",
    body: JSON.stringify(data),
  });

  const respronmesa = await promesa.json();

  promesa.status === 200
    ? swal.fire("Listo", respronmesa, "success")
    : swal.fire("Error", respronmesa, "warning");

  const tipoEl = document.getElementById("tipoPrograma");
  if (tipoEl) {
    tipoEl.hidden = true;
    tipoEl.classList.add("d-none", "hidden");
    tipoEl.style.display = "none";
  }

  document.getElementById("folio").value = "";
  document.getElementById("configuracion").value = "";
  document.getElementById("clave").value = "";
  document.getElementById("descripcion").value = "";
  document.getElementById("fecha").value = "";
  document.getElementById("maquina").value = "";
  document.getElementById("STD").value = "";
  // document.getElementById("produccion").value = "";

  document.getElementById("btnsave").classList.remove("btn-warning");
  document.getElementById("btnsave").classList.add("bg-target");
  document.getElementById("btnsave").innerHTML =
    '<i class="fas fa-save"></i> Guardar';
  createtable();
}

async function createtable() {
  const data = obtenerDatosFormulario();
  try {
    // Usando axios en lugar de fetch
    const axiosResponse = await axios.post(
      "php/producciones.php?ObtenerdatosPlanProduccion",
      data,
    );
    const promesa = {
      status: axiosResponse.status,
      json: async () => axiosResponse.data,
    };
    // console.log(axiosResponse);
    // console.log(promesa.data);

    infoPlanProduccion = axiosResponse.data.map((item) => ({
      id: item.id,
      configuracion: item.configuracion,
      clave: item.clave,
      descripcion: item.descripcion,
      fecha: item.fecha,
      NombreMaquina: item.NombreMaquina,
      produccion: item.produccion,
      STD: item.STD,
    }));

    if (promesa.status === 200) {
      mostrarTabla();
    }
  } catch (error) {
    console.log(error);
    swal.fire("Error", "Hay un problema con la base de datos", "error");
  }
}

function mostrarTabla(query = document.getElementById("searchInput").value) {
  const tbody = document.getElementById("tblProgramaMaquina");
  tbody.innerHTML = "";
  const q = (query || "").toString().trim().toLowerCase();

  // Filtrar datos según query (busca en configuracion, clave, descripcion, NombreMaquina, fecha)
  const datosFiltrados = q
    ? infoPlanProduccion.filter((item) =>
        [
          item.configuracion,
          item.clave,
          item.descripcion,
          item.NombreMaquina,
          item.fecha,
        ].some((v) => v && v.toString().toLowerCase().includes(q)),
      )
    : infoPlanProduccion.slice();

  const totalRegistros = datosFiltrados.length;
  const totalPaginas = Math.max(1, Math.ceil(totalRegistros / pageSize));

  if (currentPage > totalPaginas) currentPage = totalPaginas;

  const inicio = (currentPage - 1) * pageSize;
  const fin = inicio + pageSize;

  const paginaActualDatos = datosFiltrados.slice(inicio, fin);

  let body = "";

  if (paginaActualDatos.length === 0) {
    body = `<tr><td colspan="10" class="text-center">No hay registros que coincidan</td></tr>`;
  } else {
    paginaActualDatos.forEach((element) => {
      const porcentaje = checkInfinity(
        (element.produccion / element.STD) * 100,
      );
      const fechaRaw = element.fecha ?? "";
      let fechaDisplay = "Sin Información";
      if (fechaRaw) {
        const f = fechaRaw.toString();
        if (/^\d{4}-\d{2}$/.test(f) || /^\d{4}-\d{2}-\d{2}$/.test(f)) {
          fechaDisplay = f;
        } else {
          const d = new Date(f);
          if (!isNaN(d)) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, "0");
            const day = String(d.getDate()).padStart(2, "0");
            fechaDisplay = `${y}-${m}-${day}`;
          } else {
            fechaDisplay = f;
          }
        }
      }

      const yearMonth = (() => {
        if (!fechaDisplay || fechaDisplay === "Sin Información")
          return "Sin Información";
        const m = fechaDisplay.match(/^(\d{4})-(\d{2})/);
        if (m) return `${m[1]}-${m[2]}`;
        const d = new Date(fechaDisplay);
        if (!isNaN(d))
          return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(
            2,
            "0",
          )}`;
        return fechaDisplay;
      })();

      const formatoMiles = new Intl.NumberFormat("es-MX", {
        maximumFractionDigits: 0,
      });

      const valorSTD = Number(element.STD);

      const textoSTD = Number.isFinite(valorSTD)
        ? formatoMiles.format(valorSTD)
        : "Sin Información";

      const configMap = {
        0: "cero",
        1: "primera",
        2: "segunda",
        3: "tercera",
        4: "cuarta",
        5: "quinta",
        6: "sexta",
        7: "séptima",
        8: "octava",
        9: "novena",
        10: "décima",
      };
      const rawConfig = element.configuracion;
      let configText = "Sin Información";
      if (rawConfig !== undefined && rawConfig !== null && rawConfig !== "") {
        const k = Number(rawConfig);
        if (
          !Number.isNaN(k) &&
          Object.prototype.hasOwnProperty.call(configMap, k)
        ) {
          configText = configMap[k];
        } else {
          configText = rawConfig;
        }
      }

      body += `
        <tr>
          <td>${element.id ?? "Sin Información"}</td>
          <td>${yearMonth}</td>
          <td>${element.NombreMaquina ?? "Ninguna"}</td>
          <td>${configText.toUpperCase()}</td>
          <td>${element.clave ?? "Sin Información"}</td>
          <td>${
            element.descripcion
              ? element.descripcion.toUpperCase()
              : "Sin Información"
          }</td>
          <td>${textoSTD}</td>
          <td>
        <button class='btn btn-sm btn-warning' onclick="edit('${element.id}')">
          <i class="fa-solid fa-pen-to-square"></i>
        </button>
        <button class="btn btn-sm btn-danger" onclick="borrarRegistro('${
          element.id
        }')" disabled>
          <i class="fa-solid fa-trash"></i>
        </button>
          </td>
        </tr>`;
    });
  }

  tbody.innerHTML = body;
  document.getElementById("pageInfo").innerText =
    `PÁGINA ${currentPage} DE ${totalPaginas} (TOTAL: ${totalRegistros})`;
}
document.getElementById("clave").addEventListener("keyup", async (e) => {
  e.preventDefault();
  const formdata = new FormData();
  formdata.append("clave", e.target.value);
  const promesa = await fetch("php/producciones.php?Buscarclave", {
    method: "POST",
    body: formdata,
  });
  const datapromesa = await promesa.json();
  if (datapromesa.length == 0) {
  } else {
    document.getElementById("descripcion").value = datapromesa[0][2];
  }
});

async function edit(id) {
  // const formdata = new FormData();
  // formdata.append("id", id);
  const promesa = await fetch("php/producciones.php?getdataxid&id=" + id);
  const datapromesa = await promesa.json();

  const tipoEl = document.getElementById("tipoPrograma");
  const tituloMaquina = document.getElementById("tituloMaquina");
  if (tipoEl) {
    tipoEl.hidden = false;
    tipoEl.classList.remove("d-none", "hidden");
    tipoEl.style.display = "";
    const maquinaValor = (datapromesa[0].maquina || "").toString().trim();
    if (maquinaValor === "85") {
      if (tituloMaquina) tituloMaquina.textContent = "PROGRAMA MENSUAL MM2";
    } else {
      if (tituloMaquina) tituloMaquina.textContent = "PROGRAMA MENSUAL USTD";
    }
  }
  document.getElementById("folio").value = datapromesa[0].id;
  document.getElementById("configuracion").value = datapromesa[0].configuracion;
  document.getElementById("clave").value = datapromesa[0].clave;
  document.getElementById("descripcion").value = datapromesa[0].descripcion;
  // document.getElementById("etapa").value = datapromesa[0].nombreEtapa;
  // document.getElementById("idEtapa").value = datapromesa[0].Etapa;
  // document.getElementById("producto").value = datapromesa[0].nombreProducto;
  // document.getElementById("idProducto").value = datapromesa[0].Producto;
  const rawFecha = datapromesa[0].fecha ?? "";
  let fechaValor = "";

  if (typeof rawFecha === "string") {
    const f = rawFecha.trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(f)) {
      fechaValor = f.slice(0, 7); // "YYYY-MM-DD" -> "YYYY-MM"
    } else if (/^\d{4}-\d{2}$/.test(f)) {
      fechaValor = f; // already "YYYY-MM"
    } else {
      const d = new Date(f);
      if (!isNaN(d)) {
        fechaValor = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(
          2,
          "0",
        )}`;
      } else {
        fechaValor = f;
      }
    }
  } else {
    fechaValor = rawFecha || "";
  }

  document.getElementById("fecha").value = fechaValor;
  document.getElementById("maquina").value = datapromesa[0].maquina.trim();
  document.getElementById("STD").value = datapromesa[0].STD;
  // document.getElementById("produccion").value = datapromesa[0].produccion;
  // document.getElementById("prodvsreal").value = datapromesa[0].produccvsreal;

  document.getElementById("btnsave").classList.remove("bg-target");
  document.getElementById("btnsave").classList.add("btn-warning");
  document.getElementById("btnsave").innerHTML =
    '<i class="fa-solid fa-pen-to-square"></i> Actualizar';
  // calcularDiferencia();
}

createtable();
window.reddata = () => {
  reddata();
};

window.edit = (id) => {
  edit(id);
};

document.getElementById("btnclean").addEventListener("click", (e) => {
  e.preventDefault();
  document.getElementById("folio").value = "";
  document.getElementById("configuracion").value = "";
  document.getElementById("clave").value = "";
  document.getElementById("descripcion").value = "";
  document.getElementById("fecha").value = "";
  document.getElementById("maquina").value = "";
  document.getElementById("STD").value = "";

  const tipoEl = document.getElementById("tipoPrograma");
  if (tipoEl) {
    tipoEl.hidden = true;
    tipoEl.classList.add("d-none", "hidden");
    tipoEl.style.display = "none";
  }

  document.getElementById("btnsave").classList.remove("btn-warning");
  document.getElementById("btnsave").classList.add("bg-target");
  document.getElementById("btnsave").innerHTML =
    '<i class="fas fa-save"></i> Guardar';
});

const std = document.getElementById("STD");
const producc = document.getElementById("produccion");
// const difProd = document.getElementById("prodvsreal");
const porcProd = document.getElementById("porcenProducc");

std.addEventListener("keyup", (e) => {
  e.preventDefault();
  // calcularDiferencia();
});

// producc.addEventListener("keyup", (e) => {
//   e.preventDefault();
//   calcularDiferencia();
// });

// function calcularDiferencia() {
//   let valSTD = parseFloat(std.value);
//   let valProdc = parseFloat(producc.value);
//   let valprodvsreal = 0;
//   let valPorcentaje = 0;

//   valprodvsreal = valSTD - valProdc;
//   valPorcentaje = valProdc / valSTD;
//   // difProd.value = valprodvsreal;
//   valPorcentaje = checkInfinity(valPorcentaje * 100);

//   // porcProd.value = valPorcentaje + "%";
// }

function checkInfinity(value) {
  if (value === Infinity || value === -Infinity || Number.isNaN(value)) {
    return 0;
  }
  return Number(value).toFixed(2);
}

// Proceso para borrar un elemento de la tabla de Plan de Producción
async function borrarRegistroProduccion(id) {
  const data = new FormData();
  data.append("id", id);

  try {
    const promesa = await fetch(
      "php/producciones.php?BorrarRegistroProduccion",
      {
        method: "POST",
        body: data,
      },
    );

    // Si es igual a 200
    if (promesa.status === 200) {
      Swal.fire({
        title: "¡Eliminado!",
        text: "El registro se ha eliminado.",
        icon: "success",
      });
      createtable();
    } else if (respuesta.status === 400) {
      Swal.fire("Error", "No se recibió un ID válido", "error");
    } else if (respuesta.status === 500) {
      Swal.fire("Error", "Hay un problema con la base de datos", "error");
    } else {
      Swal.fire("Error", "Ocurrió un error inesperado", "error");
    }
  } catch (error) {
    console.error("Error en la petición:", error);
    Swal.fire("Error", "No se pudo conectar con el servidor", "error");
  }
}

window.borrarRegistro = (id) => {
  const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: "btn btn-success swal-btn",
      cancelButton: "btn btn-danger swal-btn",
    },
    buttonsStyling: false,
  });
  swalWithBootstrapButtons
    .fire({
      title: "¿Estas seguro de querer eliminar este registro?",
      text: "¡Esta acción ya no se puede revertir!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "¡Si, eliminar!",
    })
    .then(async (result) => {
      if (result.isConfirmed) {
        await borrarRegistroProduccion(id);
      }
    });
};

// === Buscador ===
document.getElementById("searchInput").addEventListener("keyup", (e) => {
  e.preventDefault();
  clearTimeout(e.target._searchTimer);
  e.target._searchTimer = setTimeout(() => {
    currentPage = 1;
    mostrarTabla(); // si más adelante quieres filtrar por query, añade un parámetro a mostrarTabla(query)
  }, 250);
});

// === Cambiar cantidad por página ===
document.getElementById("pageSize").addEventListener("change", (e) => {
  pageSize = parseInt(e.target.value);
  currentPage = 1;
  mostrarTabla(document.getElementById("searchInput").value);
});

// === Paginación ===
document.getElementById("prevPage").addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    mostrarTabla(document.getElementById("searchInput").value);
  }
});

document.getElementById("nextPage").addEventListener("click", () => {
  currentPage++;
  mostrarTabla(document.getElementById("searchInput").value);
});
