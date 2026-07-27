import { Toolsjs } from "../../Tools/Tools.js";
import { Consultas } from "../module/index.js";
const Tools = new Toolsjs();
const ConsultasObj = new Consultas();

// Declaración de variables
const folio = document.getElementById("id");
const status = document.getElementById("status");
const noemp = document.getElementById("noemp");
const empleadoActivo = document.getElementById("empleadoActivo");
const checkActivo = document.getElementById("checkActivoVal");
const nombre = document.getElementById("nombre");
const departamento = document.getElementById("departamento");
const puesto = document.getElementById("puesto");
const claveProducto = document.getElementById("claveProducto");
const categoria = document.getElementById("categoria");
const idCategoria = document.getElementById("idCategoria");
const subcategoria = document.getElementById("subcategoria");
const idSubCategoria = document.getElementById("idSubCategoria");
const cantidad = document.getElementById("cantidad");
const contenedorContraseña = document.getElementById("contenedorContraseña");
const contraseña = document.getElementById("password");
const precio = document.getElementById("precio");
const imgContPadre = document.getElementById("img-contenedor-padre");
const imgProducto = document.getElementById("img-producto");

const hoy = new Date().toISOString().split("T")[0];
document.getElementById("fecharevision").value = hoy;
const btnSaveVale = document.getElementById("saveValeProducto");
const btnSaveExcel = document.getElementById("descargarExcel");
const btnLimpiarForm = document.getElementById("limpiarFormulario");

const idsCamposObligatorios = [
  "noemp",
  "nombre",
  "categoria",
  "subcategoria",
  "cantidad",
  "precio",
];

// Rellenar la tabla
ConsultasObj.tblConsultaSession("tblconsultas");
document.addEventListener("DOMContentLoaded", (e) => {
  e.preventDefault();
  e.target.value != "" &&
    ConsultasObj.userSession().then((element) => {
      if (element[0].status) {
        status.value = true;
        noemp.value = element[0].noemp;
        noemp.disabled = true;
        nombre.value = element[0].nombre;
        departamento.value = element[0].departamento;
        puesto.value = element[0].puesto;
        empleadoActivo.checked = element[0].sindicalizado == 1;
        checkActivo.value = empleadoActivo.checked ? 1 : 0;
        nombre.disabled = true;
        departamento.disabled = true;
        puesto.disabled = true;
        contenedorContraseña.hidden = true;
        if (element[0].adminValeProducto !== 1) {
          document.getElementById("descargarExcel").style.display = "none";
        }
      }
    });

  ConsultasObj.comprobarFecha().then((element) => {
    let fechaActual = new Date(element.fechaActual);
    let fechaInicio = new Date(element.inicioPeriodo);
    if (fechaActual < fechaInicio) {
      // Swal.fire({
      //   title: "¡Lo siento!",
      //   text: "Aún no es periodo para registrar. Vuelve cuando sea el tiempo correcto.",
      //   icon: "warning",
      //   confirmButtonText: "Regresar",
      //   allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
      // }).then((result) => {
      //   if (result.isConfirmed) {
      //     window.location.href = "../index/index.php"; // Reemplaza con tu URL
      //   }
      // }),
      noemp.disabled = true;
      claveProducto.disabled = true;
      contraseña.disabled = true;
      btnSaveVale.disabled = true;
      btnLimpiarForm.disabled = true;
    } else {
      noemp.disabled = false;
      claveProducto.disabled = false;
      contraseña.disabled = false;
      btnSaveVale.disabled = false;
      btnLimpiarForm.disabled = false;
    }
  });
});

Tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "departamento", 0);
Tools.llnarslc("CatalogoPersonal", "GetSlcPuestos", "puesto", 0);
Tools.llnarslc("CatalogoAlmacen", "GetCategoriaProductos", "categoria", 0);

document.getElementById("noemp").addEventListener("keyup", (e) => {
  e.preventDefault();
  e.target.value != "" &&
    ConsultasObj.datauserEmpleado(e.target.value).then((element) => {
      console.log(element);
      if (element.length > 0) {
        nombre.value = element[0].nombre;
        departamento.value = element[0].departamento;
        puesto.value = element[0].puesto;
        empleadoActivo.checked = element[0].sindicalizado == 1;
        checkActivo.value = empleadoActivo.checked ? 1 : 0;
        departamento.disabled = true;
        puesto.disabled = true;
      } else {
        nombre.value = "";
        departamento.value = "";
        puesto.value = "";
      }
    });
});

document.getElementById("claveProducto").addEventListener("keyup", (e) => {
  e.preventDefault();
  e.target.value != "" &&
    ConsultasObj.dataProductosDos(e.target.value).then((element) => {
      if (element.length > 0) {
        subcategoria.value = element[0].descProducto;
        idSubCategoria.value = element[0].idProducto;
        categoria.value = element[0].categoria;
        idCategoria.value = element[0].IdCategoria;
        cantidad.value = element[0].paqueteCorr;
        precio.value = element[0].precio;
        imgContPadre.classList.add("img-contenedor-padre");
        imgProducto.classList.add("img-producto");
        imgProducto.src = element[0].RutaImagen;
      }
    });
});

document.getElementById("categoria").addEventListener("change", (e) => {
  e.preventDefault();
  console.log(e.target.value);
  Tools.llnarslc(
    "CatalogoAlmacen",
    "GetSubcategoriaProductos&id=" + e.target.value,
    "subcategoria",
    0,
  );
});

document.getElementById("subcategoria").addEventListener("change", (e) => {
  e.preventDefault();
  e.target.value != "" &&
    ConsultasObj.dataProductos(e.target.value).then((element) => {
      if (element.length > 0) {
        cantidad.value = element[0].cantidad;
        precio.value = element[0].precio;
      } else {
        cantidad.value = "";
        precio.value = "";
      }
    });
});

let modoEdicion = false;

btnSaveVale.addEventListener("click", async (e) => {
  e.preventDefault();

  if (modoEdicion) {
    validarCamposGuardar();
    return;
  }

  // Determinar si se encuentra la session activa. Arrojara true o false dependiendo de si tiene o no el atributo
  const sesionActiva = noemp.hasAttribute("disabled");

  if (!sesionActiva) {
    const respuesta = await ConsultasObj.datauserEmpleado(noemp.value);

    if (!respuesta || respuesta.length === 0) {
      swal.fire("UPPSS!!", "Empleado no encontrado", "warning");
      return;
    }

    const contraseñaServidor = respuesta[0].contrasena;
    const contraseñaIngresada = contraseña.value.trim();

    if (contraseñaServidor !== contraseñaIngresada) {
      swal.fire("UPPSS!!", "Contraseña incorrecta", "warning");
      return;
    }
  }

  // Obtener valores dependiendo de el noemp
  const validacion = await ConsultasObj.validarCantidadVales(noemp.value);

  if (!validacion.puedePedir) {
    swal.fire(
      "UPPSS",
      "Ya registraste los 2 vales permitidos en este periodo",
      "warning",
    );
    return;
  }

  validarCamposGuardar();
});

function validarCamposGuardar() {
  Tools.validarCamposPorID(idsCamposObligatorios) != false &&
    ConsultasObj.saveConsulta(
      noemp.value,
      checkActivo.value,
      departamento.value,
      puesto.value,
      idCategoria.value,
      idSubCategoria.value,
      cantidad.value,
      precio.value,
      folio.value,
    ).then(() => {
      ConsultasObj.tblConsultaSession("tblconsultas");
      btnSaveVale.classList.remove("btn-warning");
      btnSaveVale.classList.add("bg-target");
      btnSaveVale.innerHTML = '<i class="fas fa-save"></i> Guardar';
      claveProducto.value = "";
      categoria.value = "";
      subcategoria.value = "";
      cantidad.value = "";
      precio.value = "";
      contraseña.value = "";
      document.getElementById("fecharevision").value = hoy;
      folio.value = "";
      modoEdicion = false;
    });
}

window.editVale = (idVale) => {
  ConsultasObj.editVale(idVale).then((element) => {
    folio.value = element[0][0];
    noemp.value = element[0][1];
    empleadoActivo.checked = element[0][2];
    document.getElementById("checkActivoVal").value = document.getElementById(
      "empleadoActivo",
    ).checked
      ? 1
      : 0;
    nombre.value = element[0][10];
    departamento.value = element[0][3];
    puesto.value = element[0][4];
    claveProducto.value = element[0].claveProducto;
    categoria.value = element[0].categoria;
    subcategoria.value = element[0].descripcion;
    // Tools.llnarslc(
    //   "CatalogoAlmacen",
    //   "GetSubcategoriaProductos&id=" + element[0][5],
    //   "subcategoria",
    //   0
    // ).then(() => {
    //   subcategoria.value = element[0][6];
    // });
    cantidad.value = element[0][7];
    precio.value = element[0][8];
    document.getElementById("saveValeProducto").classList.remove("bg-target");
    document.getElementById("saveValeProducto").classList.add("btn-warning");
    document.getElementById("saveValeProducto").innerHTML =
      '<i class="fa-solid fa-pen-to-square"></i> Actualizar';

    // Activar modo edición
    modoEdicion = true;
  });
};

window.valeProducto = (idVale) => {
  ConsultasObj.generarValeProducto(idVale);
};

btnSaveExcel.addEventListener("click", (e) => {
  e.preventDefault();
  Tools.exportartablaexcel("tblValeProductos");
});

document.getElementById("limpiarFormulario").addEventListener("click", (e) => {
  e.preventDefault();
  if (status.value) {
    console.log("verdadero");
    btnSaveVale.classList.remove("btn-warning");
    btnSaveVale.classList.add("bg-target");
    btnSaveVale.innerHTML = '<i class="fas fa-save"></i> Guardar';
    claveProducto.value = "";
    categoria.value = "";
    subcategoria.value = "";
    cantidad.value = "";
    precio.value = "";
    contraseña.value = "";
  } else {
    console.log("Falso");
    btnSaveVale.classList.remove("btn-warning");
    btnSaveVale.classList.add("bg-target");
    btnSaveVale.innerHTML = '<i class="fas fa-save"></i> Guardar';
    noemp.value = "";
    nombre.value = "";
    departamento.value = "";
    puesto.value = "";
    claveProducto.value = "";
    categoria.value = "";
    subcategoria.value = "";
    cantidad.value = "";
    precio.value = "";
    contraseña.value = "";
  }
});
