// PASO DE DATOS SEGUN CONSULTA

// Función para enviar solicitud
function enviarSolicitudVacaciones(tipo) {
  const ibm = document.getElementById("ibmActual").value;
  const nombre = document.getElementById("nombreActual").value;
  const dias = document.getElementById("diasActual").value;
  const empleado = document.getElementById("empleadoActual").value;
  const fingreso = document.getElementById("fingresoActual").value;

  const form = document.createElement("form");
  form.method = "POST";
  form.action = "solicitar.php";

  // Campos del form
  const campos = {
    ibm,
    nombre,
    dias,
    empleado,
    fingreso,
    tipo,
  };

  // Bifurcacion de datos para agregar al form
  for (const [name, value] of Object.entries(campos)) {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = name;
    input.value = value;
    form.appendChild(input);
  }

  document.body.appendChild(form);
  form.submit();
}

// Funcion que muestra el boton segun la cantidad de dias disponibles
function mostrarBotonSegunDias(diasDisponibles) {
  const btn = document.getElementById("btnSolicitarVacaciones");
  const img = document.getElementById("btnAdelantarVacaciones");
  const labelP = document.getElementById("labelP");

  if (!btn || !img || !labelP) {
    console.warn("Elementos no encontrados en el DOM");
    return;
  }

  if (parseInt(diasDisponibles) > 0) {
    btn.style.display = "inline-block";
    img.style.display = "none";
    labelP.style.display = "none";
  } else {
    btn.style.display = "none";
    img.style.display = "inline-block";
    labelP.style.display = "block";
  }
}

function normalizarFechaISO(fechaStr) {
  if (!fechaStr) return "";

  const partes = fechaStr.split(/[-/]/);
  if (partes.length !== 3) return "";

  let dia, mes, anio;

  if (fechaStr.includes("-")) {
    // Ya viene como ISO: YYYY-MM-DD
    [anio, mes, dia] = partes.map((p) => parseInt(p, 10));
  } else {
    // Viene como LATAM: d/m/Y
    [dia, mes, anio] = partes.map((p) => parseInt(p, 10));
  }

  if (anio < 1000) {
    console.warn("⚠️ Año inválido detectado:", anio, "en", fechaStr);
    return "";
  }

  return `${anio.toString().padStart(4, "0")}-${mes.toString().padStart(2, "0")}-${dia.toString().padStart(2, "0")}`;
}

function calcularAntiguedad(fechaISO) {
  if (!fechaISO) return "-";

  const ingreso = new Date(fechaISO);
  if (isNaN(ingreso)) return "-";

  const hoy = new Date();

  let anios = hoy.getFullYear() - ingreso.getFullYear();
  let meses = hoy.getMonth() - ingreso.getMonth();
  let dias = hoy.getDate() - ingreso.getDate();

  if (dias < 0) {
    meses--; // aún no se cumple el mes completo
    // días restantes del mes anterior
    const ultimoMes = new Date(hoy.getFullYear(), hoy.getMonth(), 0).getDate();
    dias += ultimoMes;
  }

  if (meses < 0) {
    anios--;
    meses += 12;
  }

  let resultado = "";
  if (anios > 0) resultado += `${anios} año(s) `;
  if (meses > 0) resultado += `${meses} mes(es) `;
  if (dias > 0) resultado += `${dias} día(s)`;

  return resultado.trim() || "0 días";
}

function calcularAniversario(fechaIngreso) {
  if (!fechaIngreso) return "";

  // Normalizar separadores
  const partes = fechaIngreso.split(/[-/]/);
  if (partes.length !== 3) return "";

  // Asumimos formato YYYY-MM-DD o similar
  let anio, mes, dia;
  if (fechaIngreso.includes("-")) {
    [anio, mes, dia] = partes;
  } else {
    [mes, dia, anio] = partes;
  }

  const proximo = new Date();
  proximo.setMonth(parseInt(mes) - 1);
  proximo.setDate(parseInt(dia));
  // Año actual
  proximo.setFullYear(new Date().getFullYear());

  return proximo.toLocaleDateString("es-MX");
}

// Funcion de busqueda de carga de solicitudes
function cargarSolicitudes(ibm) {
  fetch(`solicitudesVacaciones.php?ibm=${encodeURIComponent(ibm)}`)
    .then((res) => res.text())
    .then((html) => {
      const contenedor = document.getElementById("tablaSolicitudes");
      if (html.trim() !== "") {
        contenedor.innerHTML = html;
        contenedor.style.display = "block";
      } else {
        contenedor.innerHTML = "";
        contenedor.style.display = "none";
      }
    });
}

// Busqueda de empleados por el supervisor
const btnConsultarEmpleado = document.getElementById("consultarEmpleado");
if (btnConsultarEmpleado) {
  // BOTON DE SOLICITAR VACACIONES
  const btnSolicitar = document.getElementById("btnSolicitarVacaciones");
  if (btnSolicitar) {
    btnSolicitar.addEventListener("click", function () {
      enviarSolicitudVacaciones("Normal");
    });
  }

  const btnSolicitarSup = document.getElementById("btnAdelantarVacaciones");
  if (btnSolicitarSup) {
    btnSolicitarSup.addEventListener("click", function () {
      enviarSolicitudVacaciones("Adelanto");
    });
  }

  btnConsultarEmpleado.addEventListener("click", function () {
    const ibm = document.getElementById("ibmFiltro").value.trim();
    const nombre = document.getElementById("nombreFiltro").value.trim();

    if (ibm === "" && nombre === "") {
      Swal.fire({
        icon: "error",
        title: "Campos vacios",
        text: "Debes escribir al menos IBM o Nombre para buscar.",
        confirmButtonText: "Entendido",
      });
      return;
    }

    fetch(
      `../Vacaciones/php/buscarEmpleado.php?ibm=${encodeURIComponent(ibm)}&nombre=${encodeURIComponent(nombre)}`,
    )
      .then((res) => res.json())
      .then((data) => {
        if (!data || Object.keys(data).length === 0) {
          Swal.fire({
            icon: "error",
            title: "Acceso denegado",
            text: "No se encontró el empleado o no tienes permiso para verlo.",
            confirmButtonText: "Entendido",
          });
          return;
        }
        mostrarBotonSegunDias(data["VAC_DISPONIBLES"]);
        const aniversario = calcularAniversario(data["F_INGRESO"]);
        const pAniv = document.getElementById("proximoAniversario");
        if (aniversario && pAniv) {
          pAniv.style.display = "block";
          pAniv.querySelector("code").innerText = aniversario;
        }

        const fechaISO = normalizarFechaISO(data["F_INGRESO"]);
        document.querySelector(".dato-valor.fingreso code").innerText =
          fechaISO;
        document.querySelector(".dato-valor.antiguedad code").innerText =
          calcularAntiguedad(fechaISO);

        document.querySelector(".dato-valor.ibm code").innerText = data["IBM"];
        document.querySelector(".dato-valor.nombre code").innerText =
          data["NOMBRE"];
        // document.querySelector(".dato-valor.fingreso code").innerText = data["F_INGRESO"];
        // document.querySelector(".dato-valor.antiguedad code").innerText = data["ANTIGUEDAD"];
        document.querySelector(".dias-badge").innerText =
          data["VAC_DISPONIBLES"];
        //document.querySelector(".aniversario code").innerText = data["ANIVERSARIO"];
        document.getElementById("ibmActual").value = data["IBM"];
        document.getElementById("nombreActual").value = data["NOMBRE"];
        document.getElementById("diasActual").value = data["VAC_DISPONIBLES"];
        document.getElementById("empleadoActual").value = data["TIPO"];
        document.getElementById("fingresoActual").value = data["F_INGRESO"];
      });
  });
}

// Busqueda de datos propios del supervisor
const btnVerInformacion = document.getElementById("verInformacion");
if (btnVerInformacion) {
  // BOTON DE SOLICITAR VACACIONES
  const btnSolicitar = document.getElementById("btnSolicitarVacaciones");
  if (btnSolicitar) {
    btnSolicitar.addEventListener("click", function () {
      enviarSolicitudVacaciones("Normal");
    });
  }

  const btnSolicitarSup = document.getElementById("btnAdelantarVacaciones");
  if (btnSolicitarSup) {
    btnSolicitarSup.addEventListener("click", function () {
      enviarSolicitudVacaciones("Adelanto");
    });
  }

  btnVerInformacion.addEventListener("click", function () {
    fetch(`../Vacaciones/php/buscarEmpleado.php?modo=propio`)
      .then((res) => res.json())
      .then((data) => {
        if (!data || Object.keys(data).length === 0) {
          Swal.fire({
            icon: "warning",
            title: "Sin datos",
            text: "No se encontraron tus datos.",
            confirmButtonText: "Ok",
          });
          return;
        }
        mostrarBotonSegunDias(data["VAC_DISPONIBLES"]);
        const aniversario = calcularAniversario(data["F_INGRESO"]);
        const pAniv = document.getElementById("proximoAniversario");
        if (aniversario && pAniv) {
          pAniv.style.display = "block";
          pAniv.querySelector("code").innerText = aniversario;
        }

        const fechaISO = normalizarFechaISO(data["F_INGRESO"]);
        document.querySelector(".dato-valor.fingreso code").innerText =
          fechaISO;
        document.querySelector(".dato-valor.antiguedad code").innerText =
          calcularAntiguedad(fechaISO);

        document.querySelector(".dato-valor.ibm code").innerText = data["IBM"];
        document.querySelector(".dato-valor.nombre code").innerText =
          data["NOMBRE"];
        // document.querySelector(".dato-valor.fingreso code").innerText = data["F_INGRESO"];
        // document.querySelector(".dato-valor.antiguedad code").innerText = data["ANTIGUEDAD"];
        document.querySelector(".dias-badge").innerText =
          data["VAC_DISPONIBLES"];
        //document.querySelector(".aniversario code").innerText = data["ANIVERSARIO"];
        document.getElementById("ibmActual").value = data["IBM"];
        document.getElementById("nombreActual").value = data["NOMBRE"];
        document.getElementById("diasActual").value = data["VAC_DISPONIBLES"];
        document.getElementById("empleadoActual").value = data["TIPO"];
        document.getElementById("fingresoActual").value = data["F_INGRESO"];
      });
  });
}

// Eventos de Driver JS
document.addEventListener("DOMContentLoaded", () => {
  const driver = window.driver.js.driver;
  const tipoUsuario = document.body.dataset.tipo; // 'EMPL' o 'SIND'
  const rolUsuario = document.body.dataset.rol; // 'SUPERVISOR' o 'NORMAL'

  let steps = [];

  if (tipoUsuario === "EMPL" && rolUsuario === "SUPERVISOR") {
    // Tour para supervisor
    steps = [
      {
        element: ".header-vacaciones",
        popover: {
          title: "Bienvenido",
          description: "Aquí verás tu nombre y mensaje de bienvenida.",
          side: "bottom",
        },
      },
      {
        element: ".busquedaIBM",
        popover: {
          title: "Buscar empleados",
          description:
            "Usa este campo para buscar a un empleado tipo sindicalizado por su IBM.",
          side: "bottom",
        },
      },
      {
        element: ".busquedaNOMBRE",
        popover: {
          title: "Buscar empleados",
          description:
            "Usa este campo para buscar a un empleado tipo sindicalizado por su NOMBRE.",
          side: "bottom",
        },
      },
      {
        element: ".botonBuscarEspecifica",
        popover: {
          title: "Consulta información",
          description:
            "Una vez escribas el nombre o el ibm del empleado presiona este botón para realizar la busqueda, si no esta a tu cargo no tendras permiso para su consulta.",
          side: "bottom",
        },
      },
      {
        element: ".botonBuscarPropia",
        popover: {
          title: "Consulta información",
          description:
            "Usa este botón para ver tu propia información como supervisor.",
          side: "bottom",
        },
      },
      {
        element: ".datainfosup",
        popover: {
          title: "Tus datos",
          description:
            "Una vez que hagas la consulta, observaras en esta ventana un breve resumen sobre tu información como IBM, Nombre, Fecha de ingreso y Antiguedad.",
          side: "bottom",
        },
      },
      {
        element: ".dataVacacionesDiassup",
        popover: {
          title: "Tus días disponibles",
          description:
            "Una vez que hagas la consulta, aquí se indicara cuántos días de vacaciones te quedan segun tu historial y aniversarios.",
          side: "left",
        },
      },
      {
        element: ".infoPersonalsup",
        popover: {
          title: "Información adicional",
          description:
            "Aquí encontrarás información como tu próximo aniversario y un boton para solicitar vacaciones o adelantarlas en caso de que no tengas días disponibles",
          side: "bottom",
        },
      },
      {
        element: ".ayudaSupervisor",
        popover: {
          title: "Volver a ver el tutorial",
          description:
            "Si necesitas repasar cómo usar esta pantalla, presiona este botón para repetir el tutorial.",
          side: "bottom",
        },
      },
    ];
  } else if (tipoUsuario === "EMPL" && rolUsuario === "NORMAL") {
    // Tour para empleado normal
    steps = [
      {
        element: ".header-vacaciones",
        popover: {
          title: "Bienvenido",
          description: "Aquí verás tu nombre y mensaje de bienvenida.",
          side: "right",
        },
      },
      {
        element: ".card-datainfo",
        popover: {
          title: "Tus datos",
          description:
            "En esta ventana observaras un breve resumen sobre tu información como IBM, Nombre, Fecha de ingreso y Antiguedad.",
          side: "left",
        },
      },
      {
        element: ".card-dataVacacionesDias",
        popover: {
          title: "Tus días disponibles",
          description:
            "Este número indica cuántos días de vacaciones te quedan segun tu historial y aniversarios.",
          side: "left",
        },
      },
      {
        element: ".card-infoPersonal",
        popover: {
          title: "Información adicional",
          description:
            "Aquí encontrarás datos como el estado de tus ultimas tres solicitudes y tu próximo aniversario",
          side: "top",
        },
      },
      {
        element: "form[action='solicitarJE.php'] button",
        popover: {
          title: "Solicitar vacaciones",
          description:
            "Haz clic aquí para iniciar tu solicitud (En el caso de que no tengas días disponibles podras pedir un adelanto de días de vacaciones).",
          side: "bottom",
        },
      },
      {
        element: ".ayudaEmpleado",
        popover: {
          title: "Volver a ver el tutorial",
          description:
            "Si necesitas repasar cómo usar esta pantalla, presiona este botón para repetir el tutorial.",
          side: "bottom",
        },
      },
    ];
  } else if (tipoUsuario === "SIND") {
    // Tour para sindicalizado
    steps = [
      {
        element: ".infoSind",
        popover: {
          title: "Sindicalizado",
          description: "Consulta tu informacíon con tu supervisor.",
          side: "bottom",
        },
      },
    ];
  }

  if (steps.length > 0) {
    const driverObj = driver({
      showProgress: true,
      allowClose: false,
      progressText: "Paso {{current}} de {{total}}",
      doneBtnText: "Finalizar",
      nextBtnText: "Siguiente",
      prevBtnText: "Atrás",
      steps,
    });

    // Verificar si ya se mostró el tutorial
    const tutorialKey = `tutorial_${tipoUsuario}_${rolUsuario}`;
    const tutorialYaVisto = localStorage.getItem(tutorialKey);

    if (!tutorialYaVisto) {
      // Primera vez: mostrar tutorial y marcarlo como visto
      driverObj.drive();
      localStorage.setItem(tutorialKey, "true");
    }

    // Botón de ayuda para relanzar el tutorial
    const btnAyuda = document.getElementById("btnAyuda");
    if (btnAyuda) {
      btnAyuda.addEventListener("click", () => {
        driverObj.drive();
      });
    }
  }
});

const btnVerInformacionPDF = document.getElementById("generarReporte");
if (btnVerInformacionPDF) {
  let currentPage = 1;
  const pageSize = 10;
  let totalRegistros = 0;

  async function cargarSolicitudesModal() {
    const ibmFiltro = document.getElementById("filtroIbm").value || "";
    const fechaFiltro = document.getElementById("filtroFecha").value || "";

    const url = `php/index.php?tblVacacionesEncSupervisor&page=${currentPage}&pageSize=${pageSize}&ibmFiltro=${ibmFiltro}&fecha=${fechaFiltro}`;
    const respuestaRaw = await fetch(url);
    const respuesta = await respuestaRaw.json();

    let pendientes = "";
    let procesadas = "";
    let countPendientes = 0;
    let countProcesadas = 0;

    (respuesta.data ?? []).forEach((folio) => {
      let estadoClass = "badge bg-warning text-dark";
      let estadoTexto = "En espera de aprobación/rechazo";

      if (folio.autorizado == 1 && folio.revisado == 1 && folio.firmaRI == 1) {
        estadoClass = "badge bg-success";
        estadoTexto = "Aprobado";
      } else if (folio.autorizado == 2) {
        estadoClass = "badge bg-danger";
        estadoTexto = "Rechazado";
      }

      let accionesHtml =
        folio.revisado == 1 || folio.autorizado == 2
          ? `<button class='btn btn-primary btn-sm' onclick='verPDF(${folio.id})'>
                    <i class='fa-solid fa-file-pdf'></i> Consultar info. en PDF
                </button>`
          : `<button class='btn btn-primary btn-sm' onclick='verPDF(${folio.id})'>
                    <i class='fa-solid fa-file-pdf'></i> Consultar info. en PDF
                </button>
                <button class='btn btn-danger btn-sm' onclick='eliminarRegistro(${folio.id})'>
                    <i class='fa-solid fa-trash-can'></i> Eliminar
                </button>`;

      let row = `
            <tr>
                <td>${folio.id}</td>
                <td>${folio.noemp}</td>
                <td>${folio.nombre}</td>
                <td>${folio.departamento}</td>
                <td>${folio.fecha}</td>
                <td><span class="${estadoClass}">${estadoTexto}</span></td>
                <td>${accionesHtml}</td>
            </tr>
            `;

      if (folio.revisado == 1 || folio.autorizado == 2) {
        procesadas += row;
        countProcesadas++;
      } else {
        pendientes += row;
        countPendientes++;
      }
    });

    // Pintar las tablas
    document.getElementById("tblPendientes").innerHTML = pendientes;
    document.getElementById("tblProcesadas").innerHTML = procesadas;

    // Actualizar contadores en pestañas
    document.getElementById("countPendientes").innerText = countPendientes;
    document.getElementById("countProcesadas").innerText = countProcesadas;

    // Actualizar paginación global
    const totalRegistros =
      respuesta.total ?? (respuesta.data ?? respuesta).length;
    // document.getElementById("paginaActual").innerText =
    //     `Página ${currentPage} - ${totalRegistros} resultados`;

    // Actualizar paginación con el total real
    document.getElementById("paginaActual").innerText =
      `Página ${currentPage} - ${respuesta.total} resultados en total`;
  }
  document.getElementById("btnBuscar").addEventListener("click", () => {
    currentPage = 1;
    cargarSolicitudesModal();
  });

  document.getElementById("btnLimpiar").addEventListener("click", () => {
    document.getElementById("filtroIbm").value = "";
    document.getElementById("filtroFecha").value = "";
    currentPage = 1;
    cargarSolicitudesModal();
  });

  document.getElementById("btnPrev").addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      cargarSolicitudesModal();
    }
  });

  document.getElementById("btnNext").addEventListener("click", () => {
    currentPage++;
    cargarSolicitudesModal();
  });

  // Inicial
  document.getElementById("generarReporte").addEventListener("click", () => {
    const modal = new bootstrap.Modal(
      document.getElementById("modalSolicitudesVacaciones"),
    );
    modal.show();
    currentPage = 1;
    cargarSolicitudesModal();
  });
}

window.verPDF = function (id) {
  if (!id) {
    Swal.fire("UPS!!!", "No hay un folio válido", "info");
    return false;
  }
  window.open("./pdf/GenPDF?folio=" + btoa(id));
};

window.eliminarRegistro = async function (id) {
  if (!id) {
    Swal.fire("UPS!!!", "No hay un registro válido", "info");
    return;
  }

  // Confirmación antes de eliminar
  const confirm = await Swal.fire({
    title: "¿Eliminar solicitud?",
    text: `Se eliminará la solicitud realizada, estas seguro ?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });

  if (!confirm.isConfirmed) return;

  try {
    const respuestaRaw = await fetch(`php/index.php?eliminarVacacion&id=${id}`);
    const respuesta = await respuestaRaw.json();

    if (respuesta === "Listo") {
      Swal.fire(
        "Éxito",
        `El folio ${id} fue eliminado correctamente.`,
        "success",
      ).then(() => {
        const botonSup = document.getElementById("generarReporte");
        if (botonSup) {
          location.reload();
        } else {
          location.reload();
        }
      });
    } else {
      Swal.fire("Error", "No se pudo eliminar el folio", "error");
    }
  } catch (err) {
    Swal.fire("Error", "Fallo en la petición: " + err.message, "error");
  }
};

let _rowsVac = [];
const _norm = (s) =>
  (s || "")
    .toString()
    .trim()
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
const _overlap = (a1, a2, b1, b2) => a1 <= b2 && b1 <= a2;

// async function cargarDeptosVac() {
//   const sel = document.getElementById("vacDepto");
//   if (!sel) return;
//   sel.innerHTML = `<option value="">Todos mis departamentos</option>`;
//   try {
//     const r = await fetch("php/index.php?deptosSupervisor");
//     const deptos = await r.json();
//     (deptos || []).forEach((d) => {
//       const o = document.createElement("option");
//       o.value = d;
//       o.textContent = d;
//       sel.appendChild(o);
//     });
//   } catch (e) {
//     console.error(e);
//   }
// }

async function cargarDeptosVac() {
  const sel = document.getElementById("vacDepto");
  if (!sel) return;
  sel.innerHTML = `<option value="">Todos mis departamentos</option>`;
  try {
    const r = await fetch("php/index.php?deptosSupervisor");
    const txt = await r.text(); // primero como texto
    let deptos;
    try {
      deptos = JSON.parse(txt);
    } catch {
      console.error("deptosSupervisor no devolvió JSON:", txt);
      return;
    }
    (deptos || []).forEach((d) => {
      const o = document.createElement("option");
      o.value = d;
      o.textContent = d;
      sel.appendChild(o);
    });
  } catch (e) {
    console.error(e);
  }
}

async function cargarVacacionistas() {
  const fecha = document.getElementById("vacFecha")?.value || "";
  const depto = document.getElementById("vacDepto")?.value || "";
  let p = [];
  if (fecha) p.push("fecha=" + fecha);
  if (depto) p.push("departamento=" + encodeURIComponent(depto));
  const raw = await fetch(
    "php/index.php?reporteVacacionesSupervisor" +
      (p.length ? "&" + p.join("&") : ""),
  );
  _rowsVac = await raw.json();
  renderVacacionistas();
}

function renderVacacionistas() {
  const rows = Array.isArray(_rowsVac) ? _rowsVac : [];
  const tbody = document.getElementById("tblVacacionistas");
  const soloCo = document.getElementById("vacSoloCoincidencias")?.checked;

  const coincide = new Set();
  const conflictos = {};
  for (let i = 0; i < rows.length; i++) {
    for (let j = i + 1; j < rows.length; j++) {
      const a = rows[i],
        b = rows[j];
      if (_norm(a.departamento) !== _norm(b.departamento)) continue;
      if (_norm(a.puesto) !== _norm(b.puesto)) continue;
      if (!a.de || !a.a || !b.de || !b.a) continue;
      if (_overlap(a.de, a.a, b.de, b.a)) {
        coincide.add(i);
        coincide.add(j);
        (conflictos[i] = conflictos[i] || []).push(b);
        (conflictos[j] = conflictos[j] || []).push(a);
      }
    }
  }

  const vis = rows
    .map((r, i) => ({ r, i }))
    .filter((o) => !soloCo || coincide.has(o.i));
  if (vis.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center">Sin resultados.</td></tr>`;
    return;
  }
  const badge = (e) =>
    e === "Aprobado"
      ? "bg-success"
      : e === "Rechazado"
        ? "bg-danger"
        : "bg-warning text-dark";

  tbody.innerHTML = vis
    .map(({ r, i }) => {
      const co = coincide.has(i)
        ? ` <span class="badge bg-danger">Coincidencia</span>`
        : "";
      let nota = "";
      if (conflictos[i] && conflictos[i].length) {
        nota =
          `<div class="small text-danger">Mismo puesto y depto: ` +
          conflictos[i].map((c) => `${c.noemp} (${c.de} a ${c.a})`).join("; ") +
          `</div>`;
      }
      const cls = coincide.has(i) ? ' class="table-warning"' : "";
      return `<tr${cls}>
      <td>${r.noemp}</td>
      <td>${r.nombre}${co}${nota}</td>
      <td>${r.puesto || ""}</td>
      <td>${r.departamento || ""}</td>
      <td>${r.de}</td>
      <td>${r.a}</td>
      <td>${r.supervisor || ""}</td>
      <td class="text-center"><span class="badge ${badge(r.estatus)}">${r.estatus}</span></td>
    </tr>`;
    })
    .join("");
}

document.getElementById("btnVacacionistas")?.addEventListener("click", () => {
  cargarDeptosVac();
  cargarVacacionistas();
});
document
  .getElementById("vacAplicar")
  ?.addEventListener("click", cargarVacacionistas);
document
  .getElementById("vacSoloCoincidencias")
  ?.addEventListener("change", renderVacacionistas);
