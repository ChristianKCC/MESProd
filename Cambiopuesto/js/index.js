import { Toolsjs } from "../../Tools/Tools.js";
class CambioPuesto {
  inicio() {
    const Tools = new Toolsjs();
    setInterval(Tools.mostrarHoraSimple(), 1000);
    Tools.llnarslcruta("php/index.php?motivosCambioPuesto", "motivos");
    Tools.llnarslc("CatalogoPersonal", "GetSlcMaquinas", "maquinas", 0);
    Tools.llnarslcruta("php/index.php?slcistpuestoscambiopuesto", "puestoant");
    Tools.llnarslcruta("php/index.php?slcistpuestoscambiopuesto", "temporal");
    Tools.llnarslc("CatalogoPersonal", "GetSlcDeps", "departamentoenc", 0);

    setTimeout(() => this.aplicarRestriccionDepartamento(), 500);
    this.tblenc();
  }

  // ── Reemplaza aplicarRestriccionDepartamento() por la versión CSV (punto 1) ──
  async aplicarRestriccionDepartamento() {
    const selectDeptos = document.getElementById("departamentoenc");
    const claveDepto = document
      .getElementById("clvdepartamento")
      .innerText.trim();

    let idsPermitidos = [];
    try {
      const respu = await fetch("php/departamentosPermitidos.php");
      const data = await respu.json();
      if (Array.isArray(data.ids)) idsPermitidos = data.ids.map(String);
    } catch (e) {
      console.error("No se pudieron obtener los departamentos permitidos:", e);
    }

    const permitidos = new Set(idsPermitidos);
    if (claveDepto) permitidos.add(claveDepto);

    for (let i = 0; i < selectDeptos.options.length; i++) {
      const opt = selectDeptos.options[i];
      const ok = permitidos.has(String(opt.value));
      opt.disabled = !ok;
      opt.style.color = ok ? "" : "#aaa";
      opt.style.backgroundColor = ok ? "" : "#f0f0f0";
      opt.title = ok ? "" : "No tienes este departamento asignado";
    }
    if (claveDepto && permitidos.has(claveDepto))
      selectDeptos.value = claveDepto;
  }

  async tblenc() {
    const respuetaraw = await fetch("php/index.php?tblenc");
    const respuesta = await respuetaraw.json();
    let body = "";
    respuesta.forEach((elemento) => {
      const semana = getWeekNumber(elemento.fecha);
      document.getElementById("nosemana").innerHTML = semana;

      body += `
                <tr>
                    <td>${elemento.id}</td>
                    <td>${elemento.supervisor}</td>
                    <td>${elemento.NombreEmpleado}</td>
                    <td>${elemento.fecha}</td>
                    <td>`;
      // <button class="btn btn-sm btn-success" onclick="enviarEnc(${elemento.id})"><i class="fa-solid fa-share-from-square"></i> Enviar </button>
      if (elemento.terminado == null) {
        body += `
                        <button class="btn btn-sm btn-warning" onclick="editEnc(${elemento.id}, '${elemento.fecha}')">
                            <i class="fa-solid fa-pen-to-square"></i> Consultar/Eliminar
                        </button>
                    </td>

                </tr>`;
      } else {
        body += `
                            <button class="btn btn-sm btn-danger" onclick="pdfFin(${elemento.id})"> <i class="fa-solid fa-file-pdf"></i> Descargar resultado en PDF </button></td>`;
      }
    });
    document.getElementById("tblenc").innerHTML = body;
  }

  // Obtencion de datos para su 'edicion' (consulta y eliminacion de datos)
  async editenc(id, fecha) {
    const respuestaraw = await fetch("php/index.php?getheader&id=" + id);
    const respuesta = await respuestaraw.json();

    // Calcular semana a partir de la fecha del folio
    const semana = getWeekNumber(fecha);

    // Guardar en los inputs
    let foliodom = document.getElementById("folio");
    let fechaencdom = document.getElementById("fechainput");
    let semanaDom = document.getElementById("nosemana");

    foliodom.value = respuesta[0].id;
    fechaencdom.value = respuesta[0].fecha;
    fechaencdom.disabled = true;
    semanaDom.value = semana; // aquí guardas el número de semana

    this.tblsubenc();
  }

  // Creacion dinamica de datos para tabla de datos general
  async tblsubenc() {
    // Validacion extra para los elementos
    let folio = document.getElementById("folio").value;

    if (folio === "") {
      Swal.fire("Error", "Selecciona o crea un folio,", "info");
      return;
    }
    const respuetaraw = await fetch("php/index.php?tblsubenc&folio=" + folio);
    const respuesta = await respuetaraw.json();
    let body = "";
    respuesta.forEach((elemento) => {
      let motivoHTML = "";

      if (elemento.nombreMotivos == null || elemento.nombreMotivos === "") {
        motivoHTML = `No hay motivo registrado`;
      } else {
        motivoHTML = `${elemento.nombreMotivos}`;
      }

      body += `
            <tr>
            <td>${elemento.id}</td>
            <td>${elemento.folio}</td>
            <td>${elemento.noemp}</td>
            <td>${elemento.nombre}</td>
            <td>${elemento.depto}</td>
            <td>${elemento.maquina}</td>
            <td>${elemento.puestoant}</td>
            <td>${elemento.regular}</td>
            <td>${motivoHTML}</td>
            <td>${elemento.lunes}</td>
            <td>${elemento.martes}</td>
            <td>${elemento.miercoles}</td>
            <td>${elemento.jueves}</td>
            <td>${elemento.viernes}</td>
            <td>${elemento.sabado}</td>
            <td>${elemento.domingo}</td>            
            <td><button class="btn btn-sm btn-outline-danger" onclick="deleteItemSub(${elemento.id})"><i class="fa-solid fa-trash"></i> Eliminar </button></td></tr>`;
    });
    document.getElementById("tblCambiopuesto").innerHTML = body;
  }

  async guardarcambiopuesto(esExcepcion = false, motivoExcepcion = "") {
    let noemp = document.getElementById("noemp").value;
    let maquina = document.getElementById("maquinas").value;
    let lunes = document.getElementById("lunes").checked ? 1 : 0;
    let martes = document.getElementById("martes").checked ? 1 : 0;
    let miercoles = document.getElementById("miercoles").checked ? 1 : 0;
    let jueves = document.getElementById("jueves").checked ? 1 : 0;
    let viernes = document.getElementById("viernes").checked ? 1 : 0;
    let sabado = document.getElementById("sabado").checked ? 1 : 0;
    let domingo = document.getElementById("domingo").checked ? 1 : 0;
    let puestoant = document.getElementById("puestoant").value;
    let temportal = document.getElementById("temporal").value;
    let folio = document.getElementById("folio").value;
    let motivos = document.getElementById("motivos").value;
    let porcion = document.getElementById("porcionTurno").value;
    let temporalSelect = document.getElementById("temporal");
    let contenidoTemporal =
      temporalSelect.options[temporalSelect.selectedIndex].text.trim();
    let puestoRecuperadoIBM = document.getElementById("puestoCubrir").value;

    const vacante = esMotivoVacante();
    let ibmACubrir = vacante ? "" : document.getElementById("IBMCubrir").value;

    // Validación de semana
    const semanaFol = document.getElementById("nosemana").value;
    const semanaSistema = getWeekNumber(new Date());
    if (semanaFol < semanaSistema - 1) {
      Swal.fire(
        "UPS!!!",
        "La fecha del folio pertenece a una semana demasiado antigua.",
        "warning",
      );
      return;
    }
    if (semanaFol > semanaSistema + 1) {
      Swal.fire(
        "UPS!!!",
        "La fecha del folio pertenece a una semana muy adelantada.",
        "warning",
      );
      return;
    }

    // Validaciones de campos
    if (vacante) {
      if (
        noemp === "" ||
        puestoant === "" ||
        temportal === "" ||
        folio === "" ||
        maquina === ""
      ) {
        Swal.fire("Error", "No puede haber campos vacíos.", "info");
        return false;
      }
    } else {
      if (
        noemp === "" ||
        puestoant === "" ||
        temportal === "" ||
        folio === "" ||
        maquina === "" ||
        ibmACubrir === "" ||
        puestoRecuperadoIBM === ""
      ) {
        Swal.fire("Error", "No puede haber campos vacíos.", "info");
        return false;
      }
      if (noemp === ibmACubrir) {
        Swal.fire(
          "Error",
          "El IBM de la persona a cubrir no puede ser el mismo que el tuyo.",
          "error",
        );
        return false;
      }
      const deptoFinal = document.getElementById("departamento").value.trim();
      if (
        deptoFinal != "Servicios auxiliares" &&
        contenidoTemporal != puestoRecuperadoIBM
      ) {
        Swal.fire(
          "Error",
          "El puesto a cubrir debe ser el mismo que el del IBM seleccionado.",
          "info",
        );
        return false;
      }
    }
    if (
      lunes + martes + miercoles + jueves + viernes + sabado + domingo ===
      0
    ) {
      Swal.fire("Error", "Debes seleccionar al menos un día.", "info");
      return false;
    }
    if (!motivos) {
      Swal.fire("Error", "Debes de seleccionar un motivo.", "error");
      return false;
    }

    const data = new FormData();
    data.append("noemp", noemp);
    data.append("maquina", maquina);
    data.append("lunes", lunes);
    data.append("martes", martes);
    data.append("miercoles", miercoles);
    data.append("jueves", jueves);
    data.append("viernes", viernes);
    data.append("sabado", sabado);
    data.append("domingo", domingo);
    data.append("puestoant", puestoant);
    data.append("temportal", temportal);
    data.append("folio", folio);
    data.append("ibmACubrir", ibmACubrir);
    data.append("motivos", motivos);
    data.append("porcionTurno", porcion);
    data.append("esExcepcion", esExcepcion ? "1" : "0");
    data.append("motivoExcepcion", motivoExcepcion);

    const raw = await fetch("php/index.php?guardarcambiopuesto", {
      method: "POST",
      body: data,
    });
    const r = await raw.json();

    if (r === "Listo") {
      Swal.fire("Listo !", "Registro guardado con éxito.", "success");
      this.tblsubenc();
      return;
    }
    if (r === "Existe") {
      Swal.fire("Error", "Estás duplicando un registro existente.", "error");
      return;
    }

    if (r && r.estado === "BloqueoSlot") {
      const diasTxt = (r.dias || []).map(traducirDia).join(", ");
      const quien =
        r.vacante == 1 ? "Esta vacante" : `El empleado <b>${ibmACubrir}</b>`;
      Swal.fire({
        title: "No permitido — ya está cubierto",
        html: `${quien} ya está cubierto por completo (o esa mitad ya está tomada) los días <b>${diasTxt}</b>
           de la semana <b>${r.semana}</b>.<br><br>
           No se puede volver a cubrir esos días. Si solo está cubierto a medias,
           selecciona la <b>mitad libre</b> del turno.`,
        icon: "error",
      });
      return;
    }

    if (r && r.estado === "DuplicadoSemana") {
      // solo caso coverer
      const diasTxt = (r.dias || []).map(traducirDia).join(", ");
      const res = await Swal.fire({
        title: "El empleado que cubre ya tiene otra cobertura",
        html: `El empleado <b>${noemp}</b> ya está asignado a cubrir a otra persona los días <b>${diasTxt}</b>
           de la semana <b>${r.semana}</b>.<br><br>
           Continúa solo si es una excepción justificada (p. ej. es el único que puede cubrir). Indica el motivo.`,
        icon: "warning",
        input: "text",
        inputPlaceholder: "Motivo de la excepción",
        showCancelButton: true,
        confirmButtonText: "Registrar como excepción",
        cancelButtonText: "Cancelar",
        inputValidator: (v) => (!v || !v.trim()) && "Debes escribir un motivo",
      });
      if (res.isConfirmed)
        return this.guardarcambiopuesto(true, res.value.trim());
      return;
    }

    if (r && r.estado === "FaltaMotivoExcepcion") {
      Swal.fire("Error", "Falta el motivo de la excepción.", "warning");
      return;
    }
    Swal.fire(
      "Error",
      "Error al guardar en la base de datos, contacta a soporte.",
      "error",
    );
  }

  // Creacion de folio principal
  async abrirCambioPuesto() {
    const fechainicio = document.getElementById("fechainput").value;
    if (fechainicio == "") {
      Swal.fire("Error", "No puede haber campos vacíos.", "info");
      return false;
    }

    // Calcular número de semana
    const semana = getWeekNumber(fechainicio);
    document.getElementById("nosemana").value = semana;

    // Llamada principal a ticket principal
    const data = new FormData();
    data.append("fechainicio", fechainicio);
    data.append("nosemana", semana);

    const departamentoenc = document.getElementById("departamentoenc").value;
    data.append("departamentoenc", departamentoenc);

    const respuetaraw = await fetch("php/index.php?abrircambiopuesto", {
      method: "POST",
      body: data,
    });

    const respuesta = await respuetaraw.json();

    // Validar si hay error
    if (respuesta.error) {
      Swal.fire({
        icon: "error",
        title: "No se pudo registrar",
        text:
          respuesta.error +
          ". Verifica que tu IBM esté en BD Nóminas o que tengas un jefe inmediato asignado.",
        confirmButtonText: "Entendido",
      });
      return;
    }

    // Si no hay error, mostrar éxito
    Swal.fire(
      "Listo!!!",
      "Carga los cambios de puesto al folio " + respuesta,
      "success",
    );
    this.tblenc();
    document.getElementById("folio").value = respuesta;
    document.getElementById("formtiempoextra").reset();
  }

  // Obtencion de datos
  async getinfoemp(noemp) {
    if (noemp == "") return false;
    const respuestaraw = await fetch(
      "../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp,
    );
    const respuesta = await respuestaraw.json();
    if (respuesta.length === 0) {
      document.getElementById("nombre").value = "";
      document.getElementById("departamento").value = "";
      document.getElementById("puesto").value = "";
      return;
    }

    const departamentoObtenido = respuesta[0].departamento.trim();
    const selectDepto = document.getElementById("departamentoenc");
    const departamentoCompararTexto =
      selectDepto.options[selectDepto.selectedIndex].text;

    // Validación de departamento
    if (departamentoObtenido !== departamentoCompararTexto) {
      document.getElementById("noemp").value = "";
      document.getElementById("nombre").value = "";
      document.getElementById("departamento").value = "";
      document.getElementById("puesto").value = "";

      Swal.fire({
        icon: "warning",
        title: "Departamentos no coinciden",
        html: `El departamento del empleado no corresponde a tu departamento.`,
        confirmButtonText: "Entendido",
        confirmButtonColor: "#f0ad4e",
      });
      return;
    }

    document.getElementById("nombre").value = respuesta[0].nombre;
    document.getElementById("departamento").value = respuesta[0].departamento;
    document.getElementById("departamento").dispatchEvent(new Event("change"));

    document.getElementById("puesto").value = respuesta[0].puesto;

    // Normalizar el puesto recuperado
    const puestoNormalizado = normalizarPuesto(respuesta[0].puesto);
    const sel1 = document.getElementById("puestoant");

    let encontrado = false;
    const normalizadoLower = quitarAcentos(
      puestoNormalizado.toLowerCase().trim(),
    );

    for (let i = 0; i < sel1.options.length; i++) {
      const optionText = quitarAcentos(
        sel1.options[i].text.toLowerCase().trim(),
      );
      if (optionText === normalizadoLower) {
        sel1.selectedIndex = i;
        encontrado = true;
        break;
      }
    }

    // Si se encontró coincidencia → mantener bloqueado
    // Si NO se encontró → habilitar para que el usuario pueda elegir
    sel1.disabled = !encontrado;

    if (encontrado) {
      sel1.disabled = true;
    } else {
      sel1.disabled = true;
      document.getElementById("noemp").value = "";
      document.getElementById("nombre").value = "";
      document.getElementById("departamento").value = "";
      document.getElementById("puesto").value = "";

      Swal.fire(
        "Error!",
        'Tu puesto actual: "' +
          respuesta[0].puesto +
          '" no admite cambios de turno, si crees que es un error contacta a tu jefe inmediato.',
        "error",
      );
    }

    // Disparar el evento change para que se ejecute la lógica de puestos siguientes
    sel1.dispatchEvent(new Event("change"));
  }

  // Eliminacion de datos item
  async deleteitemsub(id) {
    const data = new FormData();
    data.append("id", id);
    const respuestaraw = await fetch("php/index.php?deleteitemsub", {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    respuesta === "Listo"
      ? Swal.fire("Listo!!!", "Registro eliminado con éxito.", "success")
      : Swal.fire(
          "Error!!!",
          "Error al hacer cambios en la base de datos, contacta a soporte.",
          "error",
        );

    this.tblsubenc();
  }

  // Autorizacion
  enviar(id) {
    Swal.fire({
      title: "¿Estás seguro?",
      text: "¡Una vez creado el archivo final no podrás hacer cambios y desaparecerá de tu lista de folios!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "¡Sí, seguro!",
      cancelButtonText: "¡No, cancela!",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        (async () => {
          // Autorizacion de folio
          const respuestaraw = await fetch(
            "./php/index.php?enviarfol&id=" + id,
          );
          const respuesta = await respuestaraw.json();
          respuesta === false
            ? Swal.fire(
                "Error!!!",
                "Hay un error con la base de datos.",
                "error",
              )
            : Swal.fire("¡Terminaste!", "", "success");
          window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
          window.location.reload();
        })();
      }
    });
  }

  async cargarDisponibles(idsPuestos) {
    try {
      const semana = document.getElementById("nosemana").value;
      const noemp = document.getElementById("noemp").value;

      const respuestaraw = await fetch(
        "../Components/CatalogoSeguridad.php?disponibles&puestos=" +
          idsPuestos.join(",") +
          "&nosemana=" +
          semana +
          "&noemp=" +
          noemp,
      );
      const respuesta = await respuestaraw.json();

      const tbody = document.getElementById("tblDisponibles");
      tbody.innerHTML = "";

      if (!respuesta || respuesta.length === 0) {
        tbody.innerHTML =
          "<tr><td colspan='11'>No hay vacantes a cubrir esta semana !</td></tr>";
        return;
      }

      const orden = [
        "lunes",
        "martes",
        "miercoles",
        "jueves",
        "viernes",
        "sabado",
        "domingo",
      ];
      const cel = (estado) => {
        if (estado === "COMPLETO")
          return `<span title="Libre completo">✔</span>`;
        if (estado === "PRIMERA")
          return `<span class="text-warning" title="Solo 1ª mitad libre">½1</span>`;
        if (estado === "SEGUNDA")
          return `<span class="text-warning" title="Solo 2ª mitad libre">½2</span>`;
        return `<span class="text-muted">✘</span>`;
      };

      respuesta.forEach((r) => {
        const cubiertoTxt = r.ibmACubrir ? r.noemp : `${r.noemp} (Vacante)`;
        const celdas = orden
          .map((d) => `<td class="text-center">${cel(r.libre[d])}</td>`)
          .join("");
        const libreJson = encodeURIComponent(JSON.stringify(r.libre));
        const tr = document.createElement("tr");
        tr.innerHTML = `
        <td class="text-center">${cubiertoTxt}</td>
        <td class="text-center">${r.puestoRegular}</td>
        <td class="text-center">${r.NombreMaquina}</td>
        ${celdas}
        <td class="text-center" style="width:1%; white-space:nowrap;">
          <button class="btn btn-primary btn-sm"
            onclick="tomarVacanteV2(${r.noemp}, '${(r.puestoRegular || "").replace(/'/g, "\\'")}', '${libreJson}')">
            <i class="fa-solid fa-hand-pointer"></i> Cubrir
          </button>
        </td>`;
        tbody.appendChild(tr);
      });
    } catch (err) {
      console.error("Error al cargar", err);
    }
  }

  // PDF
  pdffin(id) {
    window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
  }
}

// Instancia de objeto para clase
CambioPuesto = new CambioPuesto();
CambioPuesto.inicio();

document
  .getElementById("formtiempoextra")
  .addEventListener("reset", function () {
    sel1.selectedIndex = -1;
    sel2.innerHTML = "";
    sel2.disabled = true;

    [
      "lunes",
      "martes",
      "miercoles",
      "jueves",
      "viernes",
      "sabado",
      "domingo",
    ].forEach((id) => {
      document.getElementById(id).checked = false;
    });

    ibmCubrirInput.value = "";
    puestoCubrirInput.value = "";
    ibmCubrirInput.readOnly = true;

    validarActivacion();
  });

function getWeekNumber(input) {
  let date;
  if (typeof input === "string") {
    const [year, month, day] = input.split("-").map(Number);
    date = new Date(year, month - 1, day);
  } else if (input instanceof Date) {
    date = new Date(input.getTime());
  } else {
    throw new Error("Formato de fecha no válido");
  }

  const tempDate = new Date(date.getTime());
  tempDate.setDate(tempDate.getDate() + 4 - (tempDate.getDay() || 7));
  const yearStart = new Date(tempDate.getFullYear(), 0, 1);
  const weekNumber = Math.ceil(((tempDate - yearStart) / 86400000 + 1) / 7);
  return weekNumber;
}

// Creacion de folio
document.getElementById("abrir").addEventListener("click", function (event) {
  event.preventDefault();
  CambioPuesto.abrirCambioPuesto().then((exito) => {
    exito && CambioPuesto.tblsubenc();
  });
});

// Guardar detalles de folio
document.getElementById("guardar").addEventListener("click", function (event) {
  event.preventDefault();
  CambioPuesto.guardarcambiopuesto().then(() => {
    CambioPuesto.tblsubenc();
  });
});

// validacion de que al crear un folio el inicio de semana sea lunea
document.getElementById("fechainput").addEventListener("change", function () {
  const fecha = new Date(this.value);
  const diaSemana = fecha.getDay();
  // D=0, L=1...

  if (diaSemana !== 0) {
    Swal.fire(
      "Atención",
      "Tus inicios de semana deben de ser lunes.",
      "warning",
    );
    this.value = "";
  }
});

// Get datos de empleado
document.getElementById("noemp").addEventListener("keyup", function () {
  let noemp = document.getElementById("noemp").value;
  if (noemp === "") {
    document.getElementById("puestoant").selectedIndex = -1;
    document.getElementById("temporal").innerHTML = "";
    document.getElementById("puestoant").disabled = true;
    document.getElementById("temporal").disabled = true;

    document.getElementById("nombre").value = "";
    document.getElementById("departamento").value = "";
    document.getElementById("puesto").value = "";
    return;
  }
  CambioPuesto.getinfoemp(noemp);
});

// PDF
document.getElementById("creapdf").addEventListener("click", function (event) {
  event.preventDefault();
  let folio = document.getElementById("folio").value;
  if (folio === "") {
    Swal.fire("Error", "No hay un folio creado.", "info");
    return false;
  }
  window.open("./pdf/reporte.php?folio=" + btoa(folio));
});

// Funcion de quitar acentos para normalizar los valores antes de que lleguen
function quitarAcentos(texto) {
  return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

// Funcion para tomar la vacante en caso de seleccionarla mediante la tabla
// function tomarVacante(
//   noempCubrir,
//   puestoCubrir,
//   lunes,
//   martes,
//   miercoles,
//   jueves,
//   viernes,
//   sabado,
//   domingo,
// ) {
//   document.getElementById("IBMCubrir").value = noempCubrir;
//   document.getElementById("puestoCubrir").value = puestoCubrir;

//   document.getElementById("lunes").checked = lunes == 1;
//   document.getElementById("martes").checked = martes == 1;
//   document.getElementById("miercoles").checked = miercoles == 1;
//   document.getElementById("jueves").checked = jueves == 1;
//   document.getElementById("viernes").checked = viernes == 1;
//   document.getElementById("sabado").checked = sabado == 1;
//   document.getElementById("domingo").checked = domingo == 1;

//   aplicarCandadoDias(noempCubrir);

//   validarActivacion();
// }

// window.tomarVacante = tomarVacante;

// Normalización
function normalizarPuesto(nombre) {
  const nombreLower = nombre.toLowerCase();

  // Normalizacion de datos para operador y conductor con el mismo = Ayudante de conductor
  if (nombreLower.includes("ayudante") && nombreLower.includes("operador")) {
    return "Ayudante de conductor";
  }

  if (nombreLower.includes("ayudante") && nombreLower.includes("conductor")) {
    return "Ayudante de conductor";
  }

  // Normalizacion de datos segun los ayudantes y conversion a mayusculas
  if (nombreLower.includes("ayudante")) return "Ayudante general";
  if (nombreLower.includes("empacador")) return "Empacador";
  if (nombreLower.includes("sellador")) return "Sellador";
  if (nombreLower.includes("mecanico")) return "Mecanico A";

  return nombre;
}

// Opciones con ids reales de la BD
const opciones = [
  { id: 1, nombre: "Ayudante general" },
  { id: 2, nombre: "Empacador" },
  { id: 4, nombre: "Sellador" },
  { id: 5, nombre: "Ayudante de conductor" },
  { id: 8, nombre: "Mecánico A" },
];

const sel1 = document.getElementById("puestoant");
const sel2 = document.getElementById("temporal");

document.getElementById("btnVacantes").addEventListener("click", () => {
  // Tomar los IDs de las opciones del select temporal
  const siguientes = Array.from(sel2.options).map((opt) => opt.value);

  if (siguientes.length === 0) {
    Swal.fire("Atención", "No hay puestos disponibles para mostrar.", "info");
    return;
  }

  // Cargar la tabla dentro del modal
  CambioPuesto.cargarDisponibles(siguientes);
});

// Lista de IBM que pueden visualizar la ultima opcion
const ibmEspeciales = [
  28154, 30108, 32738, 32849, 50764, 51447, 57270, 57429, 57511, 58811, 59697,
  59698,
];

function actualizarTemporal() {
  const puestoSeleccionado = sel1.options[sel1.selectedIndex].text.trim();
  const noemp = parseInt(document.getElementById("noemp").value);

  const normalizadoSeleccionado = quitarAcentos(
    puestoSeleccionado.toLowerCase(),
  );
  const index = opciones.findIndex(
    (o) => quitarAcentos(o.nombre.toLowerCase()) === normalizadoSeleccionado,
  );

  if (index === -1) {
    return;
  }

  // Último puesto → Mecánico A
  if (index === opciones.length - 1) {
    document.getElementById("noemp").value = "";
    document.getElementById("nombre").value = "";
    document.getElementById("departamento").value = "";
    document.getElementById("puesto").value = "";
    document.getElementById("temporal").innerHTML = "";

    Swal.fire(
      "Atención",
      "Este puesto es la categoría más alta, no puedes hacer un cambio de puesto.",
      "warning",
    );
    sel2.innerHTML = "";
    sel2.disabled = true;
    return;
  }

  let siguientes = [];
  if (index + 1 < opciones.length) siguientes.push(opciones[index + 1].id);
  if (index + 2 < opciones.length) siguientes.push(opciones[index + 2].id);

  const ultimoId = opciones[opciones.length - 1].id;
  if (ibmEspeciales.includes(noemp) && !siguientes.includes(ultimoId)) {
    siguientes.push(ultimoId);
  }

  // Validación en caso de que el departamento sea Servicios auxiliares
  const depto = document.getElementById("departamento").value.trim();

  sel2.innerHTML = "";
  if (depto === "Servicios auxiliares") {
    // Mostrar solo Mecánico A
    const opt = document.createElement("option");
    opt.value = ultimoId;
    opt.textContent = opciones.find((o) => o.id === ultimoId).nombre;
    sel2.appendChild(opt);
  } else {
    // Mostrar los siguientes puestos (y el último si aplica)
    siguientes.forEach((id) => {
      const opt = document.createElement("option");
      opt.value = id;
      opt.textContent = opciones.find((o) => o.id === id).nombre;
      sel2.appendChild(opt);
    });
  }

  CambioPuesto.cargarDisponibles(siguientes);
  sel2.disabled = sel2.options.length === 0;
}

// Bloqueo de campo de No. Emp hasta que se tenga un folio o se cree uno
const fol = document.getElementById("folio").value;

// Ejecutar cuando el usuario cambie manualmente
sel1.addEventListener("change", actualizarTemporal);

const ibmCubrirInput = document.getElementById("IBMCubrir");
const puestoCubrirInput = document.getElementById("puestoCubrir");
const maquinaSelect = document.getElementById("maquinas");
const ibmpropio = document.getElementById("noemp");
const folio = document.getElementById("folio");
const motivoCambioPT = document.getElementById("motivos");
ibmCubrirInput.readOnly = true;

maquinaSelect.addEventListener("change", function () {
  if (this.value.trim() === "") {
    // Si no hay máquina seleccionada -> deshabilitar IBM
    ibmCubrirInput.value = "";
    puestoCubrirInput.value = "";
    ibmCubrirInput.readOnly = true;
  } else {
    // Si hay máquina seleccionada -> habilitar IBM
    ibmCubrirInput.readOnly = false;
  }
  validarActivacion();
});

// Acciones para el select de IBM a cubrir
document
  .getElementById("IBMCubrir")
  .addEventListener("keyup", async function () {
    const ibm = this.value.trim();
    if (ibm === "") {
      puestoCubrirInput.value = "";
      return;
    }
    const respuestaraw = await fetch(
      "../Components/CatalogoSeguridad.php?datosemp&noemp=" + ibm,
    );
    const respuesta = await respuestaraw.json();

    if (respuesta.length > 0) {
      const puestoRecuperado = normalizarPuesto(respuesta[0].puesto);
      const deptoSolicitante = document
        .getElementById("departamento")
        .value.trim();

      if (deptoSolicitante === "Servicios auxiliares") {
        // Aceptar cualquier IBM y mostrar su puesto real
        puestoCubrirInput.value = puestoRecuperado;
      } else {
        // Validación normal
        puestoCubrirInput.value = puestoRecuperado;

        const idsPermitidos = Array.from(sel2.options).map((opt) => opt.value);
        const puestoPermitido = opciones.find(
          (o) => o.nombre.toLowerCase() === puestoRecuperado.toLowerCase(),
        );

        if (
          !puestoPermitido ||
          !idsPermitidos.includes(String(puestoPermitido.id))
        ) {
          Swal.fire(
            "Error",
            "El puesto del IBM ingresado no coincide con los puestos disponibles según tu puesto actual. Verifica el IBM e intenta de nuevo.",
            "warning",
          );
          this.value = "";
          puestoCubrirInput.value = "";
          return;
        }
      }
      aplicarCandadoDias(ibm);
    } else {
      puestoCubrirInput.value = "";
      window._estadoCobDias = {};
      refrescarDiasSegunPorcion();
    }
    validarActivacion();
  });

function validarActivacion() {
  const maquina = maquinaSelect.value.trim();
  const ibmCubrir = ibmCubrirInput.value.trim();
  const puestoCubrir = puestoCubrirInput.value.trim();
  const ibmp = ibmpropio.value.trim();
  const motivo = motivoCambioPT.value.trim();
  const fol = folio.value.trim();

  const btnGuardar = document.getElementById("guardar");
  const btnVacantes = document.getElementById("btnVacantes");

  let mostrarGuardar;
  if (esMotivoVacante()) {
    // Vacante: NO exige IBM a cubrir
    mostrarGuardar =
      fol !== "" && motivo !== "" && ibmp !== "" && maquina !== "";
  } else {
    mostrarGuardar =
      fol !== "" &&
      motivo !== "" &&
      ibmp !== "" &&
      ibmCubrir !== "" &&
      puestoCubrir !== "";
  }

  btnGuardar.hidden = !mostrarGuardar;
  btnGuardar.style.display = mostrarGuardar ? "inline-block" : "none";

  btnVacantes.style.display =
    fol !== "" && ibmp !== "" && maquina !== "" ? "inline-block" : "none";
}

document.getElementById("departamento").addEventListener("change", function () {
  const nombreDepto = this.value.trim();

  if (nombreDepto === "") {
    // Restaurar todas las máquinas si se limpia el departamento
    const Tools = new Toolsjs();
    Tools.llnarslc("CatalogoPersonal", "GetSlcMaquinas", "maquinas", 0);
    return;
  }

  // Buscar el id numérico en el select de departamentoenc o en el select de maquinas
  // Como no hay un select de deptos visible, usamos el mismo truco: hacer fetch directo
  fetch("../Components/CatalogoPersonal.php?GetSlcDeps")
    .then((r) => r.json())
    .then((deptos) => {
      const depto = deptos.find((d) => d.nombre.trim() === nombreDepto);
      if (!depto) return;

      const Tools = new Toolsjs();
      Tools.llnarslc(
        "CatalogoPersonal",
        "GetSlcMaquinasxdep&departamento=" + depto.id,
        "maquinas",
        0,
      );
    });
});

// ── Añade al final del archivo: reporte de coberturas (punto 6) ──────────────
async function cargarFiltroDeptosReporte() {
  const sel = document.getElementById("repDepto");
  if (!sel) return;
  sel.innerHTML = `<option value="">Todos mis departamentos</option>`;
  try {
    const r = await fetch("php/departamentosPermitidos.php");
    const d = await r.json();
    (d.ids || []).forEach((id, i) => {
      const opt = document.createElement("option");
      opt.value = id;
      opt.textContent = d.nombres && d.nombres[i] ? d.nombres[i] : id;
      sel.appendChild(opt);
    });
  } catch (e) {
    console.error(e);
  }
}

// async function cargarReporteCoberturas() {
//   const fecha = document.getElementById("repFecha").value;
//   const depto = document.getElementById("repDepto").value;
//   let params = [];
//   if (fecha) {
//     params.push("semana=" + getWeekNumber(fecha));
//     params.push("anio=" + fecha.split("-")[0]);
//   }
//   if (depto) params.push("departamento=" + depto);

//   const raw = await fetch(
//     "php/index.php?reporteCoberturas&" + params.join("&"),
//   );
//   const rows = await raw.json();
//   const tbody = document.getElementById("tblCoberturas");
//   const etiquetaPorcion = {
//     completo: "Completo",
//     primera_mitad: "1ª mitad",
//     segunda_mitad: "2ª mitad",
//   };

//   if (!Array.isArray(rows) || rows.length === 0) {
//     tbody.innerHTML = `<tr><td colspan="14" class="text-center">No hay coberturas para el filtro seleccionado.</td></tr>`;
//     return;
//   }
//   tbody.innerHTML = rows
//     .map((r) => {
//       const d = r.dias.map((x) => (x == 1 ? "✔" : "✘"));
//       const exc =
//         r.esExcepcion == 1
//           ? `<span class="badge bg-warning text-dark" title="${(r.motivoExcepcion || "").replace(/"/g, "&quot;")}">Excepción</span>`
//           : "";
//       return `<tr>
//       <td>${r.noSemana}</td><td>${r.folio}</td>
//       <td>${r.noempCubre}<br><small>${r.nombreCubre}</small></td>
//       <td>${r.ibmACubrir}<br><small>${r.nombreCubierto}</small></td>
//       <td>${etiquetaPorcion[r.porcion] || r.porcion} ${exc}</td>
//       <td>${r.puestoTemporal || ""}</td><td>${r.maquina || ""}</td>
//       <td class="text-center">${d[0]}</td><td class="text-center">${d[1]}</td>
//       <td class="text-center">${d[2]}</td><td class="text-center">${d[3]}</td>
//       <td class="text-center">${d[4]}</td><td class="text-center">${d[5]}</td>
//       <td class="text-center">${d[6]}</td>
//     </tr>`;
//     })
//     .join("");
// }

let _rowsCob = [];

async function cargarReporteCoberturas() {
  const fecha = document.getElementById("repFecha").value;
  const depto = document.getElementById("repDepto").value;
  let params = [];
  if (fecha) {
    params.push("semana=" + getWeekNumber(fecha));
    params.push("anio=" + fecha.split("-")[0]);
  }
  if (depto) params.push("departamento=" + depto);

  const raw = await fetch(
    "php/index.php?reporteCoberturas&" + params.join("&"),
  );
  _rowsCob = await raw.json();
  renderReporteCoberturas();
}

// function renderReporteCoberturas() {
//   const rows = Array.isArray(_rowsCob) ? _rowsCob : [];
//   const tbody = document.getElementById("tblCoberturas");
//   const soloRep = document.getElementById("repSoloRepetidos")?.checked;
//   const etiquetaPorcion = {
//     completo: "Completo",
//     primera_mitad: "1ª mitad",
//     segunda_mitad: "2ª mitad",
//   };
//   const solapaJS = (a, b) =>
//     a === "completo" || b === "completo" ? true : a === b;

//   // Detección de repetidos por (año-semana-cubierto)
//   const grupos = {};
//   rows.forEach((r, idx) => {
//     if (!r.ibmACubrir) return;
//     const k = `${r.fecha.slice(0, 4)}|${r.noSemana}|${r.ibmACubrir}`;
//     (grupos[k] = grupos[k] || []).push({
//       idx,
//       porcion: r.porcion,
//       dias: r.dias,
//     });
//   });
//   const repFila = {};
//   const grupoRep = new Set();
//   Object.values(grupos).forEach((items) => {
//     if (items.length < 2) return;
//     for (let d = 0; d < 7; d++) {
//       const cubren = items.filter((it) => it.dias[d] === 1);
//       if (cubren.length < 2) continue;
//       let overlap = false;
//       for (let i = 0; i < cubren.length && !overlap; i++)
//         for (let j = i + 1; j < cubren.length; j++)
//           if (solapaJS(cubren[i].porcion, cubren[j].porcion)) {
//             overlap = true;
//             break;
//           }
//       if (overlap)
//         cubren.forEach((c) => {
//           (repFila[c.idx] = repFila[c.idx] || new Set()).add(d);
//           grupoRep.add(c.idx);
//         });
//     }
//   });

//   const visibles = rows
//     .map((r, idx) => ({ r, idx }))
//     .filter((o) => !soloRep || grupoRep.has(o.idx));
//   if (visibles.length === 0) {
//     tbody.innerHTML = `<tr><td colspan="16" class="text-center">No hay coberturas para el filtro seleccionado.</td></tr>`;
//     return;
//   }

//   tbody.innerHTML = visibles
//     .map(({ r, idx }) => {
//       const dias = r.dias
//         .map((x, di) => {
//           const rep = repFila[idx] && repFila[idx].has(di);
//           const st = rep
//             ? ' style="color:#dc3545;font-weight:bold;" title="Día repetido en la semana"'
//             : "";
//           return `<td class="text-center"${st}>${x == 1 ? "✔" : "✘"}</td>`;
//         })
//         .join("");
//       const exc =
//         r.esExcepcion == 1
//           ? ` <span class="badge bg-warning text-dark" title="${(r.motivoExcepcion || "").replace(/"/g, "&quot;")}">Exc.</span>`
//           : "";
//       const repBadge = grupoRep.has(idx)
//         ? ` <span class="badge bg-danger">Repetido</span>`
//         : "";
//       const cubierto = r.ibmACubrir
//         ? `${r.ibmACubrir}<br><small>${r.nombreCubierto || ""}</small>${repBadge}`
//         : `<span class="badge bg-secondary">VACANTE</span>`;
//       const rowClass = grupoRep.has(idx) ? ' class="table-warning"' : "";
//       return `<tr${rowClass}>
//       <td>${fmtFecha(r.fecha)}<br><small>a ${fmtFecha(r.fechaFin)}</small><br><small class="text-muted">Sem ${r.noSemana}</small></td>
//       <td>${r.folio}</td>
//       <td>${r.supervisor || ""}<br><small>${r.ibmSupervisor || ""}</small></td>
//       <td><small>${r.fechaCreacion || ""}</small></td>
//       <td>${r.noempCubre}<br><small>${r.nombreCubre}</small></td>
//       <td>${cubierto}</td>
//       <td>${etiquetaPorcion[r.porcion] || r.porcion}${exc}</td>
//       <td>${r.puestoTemporal || ""}</td>
//       <td>${r.maquina || ""}</td>
//       ${dias}
//     </tr>`;
//     })
//     .join("");
// }

function renderReporteCoberturas() {
  const rows = Array.isArray(_rowsCob) ? _rowsCob : [];
  const tbody = document.getElementById("tblCoberturas");
  const soloRep = document.getElementById("repSoloRepetidos")?.checked;
  const etiquetaPorcion = {
    completo: "Completo",
    primera_mitad: "1ª mitad",
    segunda_mitad: "2ª mitad",
  };
  const nombresDia = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];
  const solapaJS = (a, b) =>
    a === "completo" || b === "completo" ? true : a === b;
  const claveOrden = (r) =>
    (r.fechaCreacion && r.fechaCreacion !== ""
      ? r.fechaCreacion
      : "9999-99-99 99:99") +
    "|" +
    String(r.folio).padStart(8, "0");

  // Firma del slot cubierto (persona o vacante), acotada a año+semana
  const slot = (r) =>
    r.ibmACubrir
      ? `P|${r.fecha.slice(0, 4)}|${r.noSemana}|${r.ibmACubrir}`
      : `V|${r.fecha.slice(0, 4)}|${r.noSemana}|${r.maquina}|${r.puestoTemporal}`;

  const grupos = {};
  rows.forEach((r, idx) => {
    const k = slot(r);
    (grupos[k] = grupos[k] || []).push(idx);
  });

  const diasRep = {}; // idx -> Set(díaIndex duplicado)
  const conflictos = {}; // idx -> [{folio, supervisor, dias[], motivo}]
  const esOriginal = {};
  const enCluster = new Set();

  Object.values(grupos).forEach((idxs) => {
    if (idxs.length < 2) return;
    for (let i = 0; i < idxs.length; i++) {
      for (let j = i + 1; j < idxs.length; j++) {
        const a = rows[idxs[i]],
          b = rows[idxs[j]];
        const comunes = [];
        for (let d = 0; d < 7; d++)
          if (
            a.dias[d] === 1 &&
            b.dias[d] === 1 &&
            solapaJS(a.porcion, b.porcion)
          )
            comunes.push(d);
        if (comunes.length === 0) continue;

        enCluster.add(idxs[i]);
        enCluster.add(idxs[j]);
        diasRep[idxs[i]] = diasRep[idxs[i]] || new Set();
        diasRep[idxs[j]] = diasRep[idxs[j]] || new Set();
        comunes.forEach((d) => {
          diasRep[idxs[i]].add(d);
          diasRep[idxs[j]].add(d);
        });

        (conflictos[idxs[i]] = conflictos[idxs[i]] || []).push({
          folio: b.folio,
          supervisor: b.supervisor,
          dias: comunes.map((d) => nombresDia[d]),
          motivo: b.esExcepcion == 1 ? b.motivoExcepcion || "" : "",
        });
        (conflictos[idxs[j]] = conflictos[idxs[j]] || []).push({
          folio: a.folio,
          supervisor: a.supervisor,
          dias: comunes.map((d) => nombresDia[d]),
          motivo: a.esExcepcion == 1 ? a.motivoExcepcion || "" : "",
        });
      }
    }
    const participantes = idxs.filter((ix) => enCluster.has(ix));
    if (participantes.length) {
      participantes.sort((x, y) =>
        claveOrden(rows[x]) < claveOrden(rows[y]) ? -1 : 1,
      );
      esOriginal[participantes[0]] = true;
    }
  });

  const visibles = rows
    .map((r, idx) => ({ r, idx }))
    .filter((o) => !soloRep || enCluster.has(o.idx));
  if (visibles.length === 0) {
    tbody.innerHTML = `<tr><td colspan="16" class="text-center">No hay coberturas para el filtro seleccionado.</td></tr>`;
    return;
  }

  tbody.innerHTML = visibles
    .map(({ r, idx }) => {
      const dias = r.dias
        .map((x, di) => {
          const rep = diasRep[idx] && diasRep[idx].has(di);
          const st = rep
            ? ' style="color:#dc3545;font-weight:bold;" title="Día duplicado en la semana"'
            : "";
          return `<td class="text-center"${st}>${x == 1 ? "✔" : "✘"}</td>`;
        })
        .join("");

      const exc =
        r.esExcepcion == 1
          ? ` <span class="badge bg-warning text-dark" title="${(r.motivoExcepcion || "").replace(/"/g, "&quot;")}">Exc.</span>`
          : "";

      let etiqDup = "";
      if (enCluster.has(idx))
        etiqDup = esOriginal[idx]
          ? ` <span class="badge bg-primary">Original</span>`
          : ` <span class="badge bg-danger">Duplicado</span>`;

      const cubierto = r.ibmACubrir
        ? `${r.ibmACubrir}<br><small>${r.nombreCubierto || ""}</small>${etiqDup}`
        : `<span class="badge bg-secondary">VACANTE</span>${etiqDup}`;

      let notaConf = "";
      if (conflictos[idx] && conflictos[idx].length) {
        const items = conflictos[idx]
          .map(
            (c) =>
              `Folio ${c.folio} (${c.supervisor}) — ${c.dias.join(", ")}${c.motivo ? ` · <i>${c.motivo}</i>` : ""}`,
          )
          .join("<br>");
        notaConf = `<div class="small text-danger mt-1"><i class="fa-solid fa-link"></i> Duplicado con:<br>${items}</div>`;
      }

      const rowClass = enCluster.has(idx) ? ' class="table-warning"' : "";
      return `<tr${rowClass}>
      <td>${fmtFecha(r.fecha)}<br><small>a ${fmtFecha(r.fechaFin)}</small><br><small class="text-muted">Sem ${r.noSemana}</small></td>
      <td>${r.folio}</td>
      <td>${r.supervisor || ""}<br><small>${r.ibmSupervisor || ""}</small></td>
      <td><small>${r.fechaCreacion || ""}</small></td>
      <td>${r.noempCubre}<br><small>${r.nombreCubre}</small></td>
      <td>${cubierto}${notaConf}</td>
      <td>${etiquetaPorcion[r.porcion] || r.porcion}${exc}</td>
      <td>${r.puestoTemporal || ""}</td>
      <td>${r.maquina || ""}</td>
      ${dias}
    </tr>`;
    })
    .join("");
}

document
  .getElementById("repSoloRepetidos")
  ?.addEventListener("change", renderReporteCoberturas);

document.getElementById("btnReporteCob")?.addEventListener("click", () => {
  cargarFiltroDeptosReporte();
  cargarReporteCoberturas();
});
document
  .getElementById("repAplicar")
  ?.addEventListener("click", cargarReporteCoberturas);

const ID_MOTIVO_VACANTE = "4";

function esMotivoVacante() {
  const sel = document.getElementById("motivos");
  return !!sel && sel.value === ID_MOTIVO_VACANTE;
}
function traducirDia(d) {
  const m = {
    lunes: "Lunes",
    martes: "Martes",
    miercoles: "Miércoles",
    jueves: "Jueves",
    viernes: "Viernes",
    sabado: "Sábado",
    domingo: "Domingo",
  };
  return m[d] || d;
}
function fmtFecha(d) {
  return d ? d.split("-").reverse().join("-") : "";
}

document.getElementById("motivos").addEventListener("change", function () {
  const ibmInput = document.getElementById("IBMCubrir");
  const puestoCubrir = document.getElementById("puestoCubrir");
  if (esMotivoVacante()) {
    ibmInput.value = "";
    puestoCubrir.value = "";
    ibmInput.disabled = true;
    ibmInput.readOnly = true;
    ibmInput.placeholder = "No requerido (Vacante)";
    puestoCubrir.placeholder = "No aplica";
    window._estadoCobDias = {};
    refrescarDiasSegunPorcion();
  } else {
    ibmInput.disabled = false;
    ibmInput.placeholder = "";
    puestoCubrir.placeholder = "";
    // habilitado para escribir en cuanto haya máquina
    ibmInput.readOnly = document.getElementById("maquinas").value.trim() === "";
  }
  validarActivacion();
});

// Eliminacion y consulta
window.editEnc = function (id, fecha) {
  CambioPuesto.editenc(id, fecha);
};

// Eliminacion principal de datos
window.deleteItemSub = function (id) {
  CambioPuesto.deleteitemsub(id);
};

// Autorizacion de datos
window.enviarEnc = function (id) {
  CambioPuesto.enviar(id);
};

// Apertura del PDF
window.pdfFin = function (id) {
  CambioPuesto.pdffin(id);
};

document.addEventListener("DOMContentLoaded", () => {
  const driver = window.driver.js.driver;

  const steps = [
    {
      element: ".tittlecont",
      popover: {
        title: "Cambio de Puesto",
        description:
          "Aquí comienza el proceso para elaborar tus solicitudes de cambio de puesto.",
        side: "bottom",
      },
    },
    {
      element: ".alert.alert-info",
      popover: {
        title: "Instrucciones iniciales",
        description:
          "Desde esta sección podrás crear y gestionar tus solicitudes de cambio de puesto.",
        side: "bottom",
      },
    },
    {
      element: "#folio",
      popover: {
        title: "Folio",
        description: "Este campo muestra el folio generado para tu solicitud.",
        side: "top",
      },
    },
    {
      element: "#fechainput",
      popover: {
        title: "Inicio de semana",
        description:
          "Selecciona la fecha de inicio de la semana para tu solicitud.",
        side: "top",
      },
    },
    {
      element: "#abrir",
      popover: {
        title: "Crear folio",
        description: "Haz clic aquí para generar un nuevo folio de solicitud.",
        side: "top",
      },
    },
    {
      element: "#btnverfolio",
      popover: {
        title: "Ver folios",
        description:
          "Consulta los folios creados previamente desde esta opción.",
        side: "top",
      },
    },
    {
      element: ".empezarDeNuevo",
      popover: {
        title: "Reiniciar proceso",
        description:
          "Presiona este botón para limpiar todos los campos e iniciar un nuevo registro.",
        side: "top",
      },
    },
    {
      element: "#creapdf",
      popover: {
        title: "Previsualizar PDF",
        description: "Genera una vista previa en PDF de tu solicitud.",
        side: "top",
      },
    },
    {
      element: "#noemp",
      popover: {
        title: "Número de empleado",
        description: "Ingresa el número de empleado para cargar sus datos.",
        side: "top",
      },
    },
    {
      element: "#maquinas",
      popover: {
        title: "Máquina",
        description:
          "Selecciona la máquina en la que trabajará el empleado (Esta se actualizara segun el departamento que se tenga asignado).",
        side: "top",
      },
    },
    {
      element: "#temporal",
      popover: {
        title: "Puesto a cubrir",
        description:
          "Selecciona el puesto temporal que se cubrirá (Solo podras ver dos puestos arriba de tu puesto actual).",
        side: "top",
      },
    },
    {
      element: ".motivoSeleccion",
      popover: {
        title: "Motivo del cambio de puesto",
        description:
          "Selecciona un motivo de las opciones disponibles por la cual haces tu cambio de puesto).",
        side: "top",
      },
    },
    {
      element: "#IBMCubrir",
      popover: {
        title: "IBM de persona a cubrir",
        description:
          "Ingresa el IBM de la persona que será cubierta (Solo puede ser alguien que tenga el mismo puesto que tus opciones disponibles para cubrir, no podras cubir a alguien que tiene un puesto diferente a tus opciones en 'Puesto a cubrir').",
        side: "top",
        popoverClass: "popover-importante",
      },
    },
    {
      element: ".diasSeleccion",
      popover: {
        title: "Selección de días",
        description: "Selecciona los dias que vas a cubrir la vacante.",
        side: "top",
      },
    },
    {
      element: ".alert.alert-warning",
      popover: {
        title: "Tabla de solicitudes",
        description:
          "Aquí encontrarás las solicitudes creadas y asociadas al folio seleccionado.",
        side: "bottom",
      },
    },
    {
      element: "#tblCambiopuesto",
      popover: {
        title: "Solicitudes creadas",
        description:
          "Consulta el detalle de las solicitudes registradas en la tabla.",
        side: "top",
      },
    },
    {
      element: "#btnAyuda",
      popover: {
        title: "Volver a ver el tutorial",
        description:
          "Si necesitas repasar cómo usar esta pantalla, presiona este botón para repetir el tutorial.",
        side: "bottom",
      },
    },
  ];

  const driverObj = driver({
    showProgress: true,
    allowClose: false,
    disableInteraction: true,
    progressText: "Paso {{current}} de {{total}}",
    doneBtnText: "Finalizar",
    nextBtnText: "Siguiente",
    prevBtnText: "Atrás",
    steps,
  });

  // Clave única para este tutorial
  const tutorialKey = "tutorial_cambiopuesto";
  const tutorialYaVisto = localStorage.getItem(tutorialKey);

  if (!tutorialYaVisto) {
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
});

window._estadoCobDias = {};
const _DIAS_ID = [
  "lunes",
  "martes",
  "miercoles",
  "jueves",
  "viernes",
  "sabado",
  "domingo",
];

async function aplicarCandadoDias(ibm) {
  const semana = document.getElementById("nosemana").value;
  const fechaInput = document.getElementById("fechainput").value;
  const anio = fechaInput ? fechaInput.split("-")[0] : new Date().getFullYear();
  window._estadoCobDias = {};
  if (ibm && semana && !esMotivoVacante()) {
    try {
      const r = await fetch(
        `php/index.php?estadoCoberturaIBM&ibm=${ibm}&semana=${semana}&anio=${anio}`,
      );
      window._estadoCobDias = await r.json();
    } catch (e) {
      console.error(e);
      window._estadoCobDias = {};
    }
  }
  refrescarDiasSegunPorcion();
}

// function refrescarDiasSegunPorcion() {
//   const est = window._estadoCobDias || {};
//   const porc = document.getElementById("porcionTurno").value;
//   // _DIAS_ID.forEach((d) => {
//   //   const chk = document.getElementById(d);
//   //   if (!chk) return;
//   //   const lbl = chk.closest(".form-check")?.querySelector("label");
//   //   const estado = est[d] || "LIBRE";

//   //   let permitido = true,
//   //     nota = "",
//   //     mitadLibre = null;
//   //   if (estado === "TAKEN") {
//   //     permitido = false;
//   //     nota = "Cubierto completo";
//   //   } else if (estado === "FREE_SEGUNDA") {
//   //     permitido = true;
//   //     nota = "Solo 2ª mitad libre";
//   //     mitadLibre = "segunda_mitad";
//   //   } else if (estado === "FREE_PRIMERA") {
//   //     permitido = true;
//   //     nota = "Solo 1ª mitad libre";
//   //     mitadLibre = "primera_mitad";
//   //   }

//   //   chk.disabled = !permitido; // solo se bloquea si está TAKEN
//   //   if (!permitido) chk.checked = false;
//   //   chk.dataset.mitadLibre = mitadLibre || ""; // recordar qué mitad admite este día

//   //   if (lbl) {
//   //     const base =
//   //       lbl.getAttribute("data-base") ||
//   //       lbl.textContent.replace(/\s*\(.*\)$/, "").trim();
//   //     lbl.setAttribute("data-base", base);
//   //     lbl.textContent = nota ? `${base} (${nota})` : base;
//   //     lbl.style.color = permitido ? "" : "#adb5bd";
//   //   }
//   // });

//   _DIAS_ID.forEach((d) => {
//     const chk = document.getElementById(d);
//     if (!chk) return;
//     chk.addEventListener("change", function () {
//       if (!this.checked) return;
//       const mitad = this.dataset.mitadLibre || "";
//       if (!mitad) return; // día totalmente libre, sin restricción

//       const selPorc = document.getElementById("porcionTurno");

//       // ¿Hay otros días marcados que exijan la mitad contraria?
//       const contraria =
//         mitad === "primera_mitad" ? "segunda_mitad" : "primera_mitad";
//       const choque = _DIAS_ID.some((x) => {
//         const c = document.getElementById(x);
//         return c && c.checked && c.dataset.mitadLibre === contraria;
//       });
//       if (choque) {
//         this.checked = false;
//         Swal.fire(
//           "Atención",
//           "Tienes días que solo admiten la primera mitad y otros solo la segunda. Regístralos por separado.",
//           "warning",
//         );
//         return;
//       }

//       if (selPorc.value !== mitad) {
//         selPorc.value = mitad;
//         const etiqueta =
//           mitad === "primera_mitad" ? "primera mitad" : "segunda mitad";
//         Swal.fire(
//           "Porción ajustada",
//           `Ese día solo tiene libre la <b>${etiqueta}</b>, así que la porción se ajustó automáticamente.`,
//           "info",
//         );
//         refrescarDiasSegunPorcion();
//       }
//     });
//   });
//   validarActivacion();
// }

function refrescarDiasSegunPorcion() {
  const est = window._estadoCobDias || {};
  _DIAS_ID.forEach((d) => {
    const chk = document.getElementById(d);
    if (!chk) return;
    const lbl = chk.closest(".form-check")?.querySelector("label");
    const estado = est[d] || "LIBRE";

    let permitido = true,
      nota = "",
      mitadLibre = null;
    if (estado === "TAKEN") {
      permitido = false;
      nota = "Cubierto completo";
    } else if (estado === "FREE_SEGUNDA") {
      permitido = true;
      nota = "Solo 2ª mitad libre";
      mitadLibre = "segunda_mitad";
    } else if (estado === "FREE_PRIMERA") {
      permitido = true;
      nota = "Solo 1ª mitad libre";
      mitadLibre = "primera_mitad";
    }

    chk.disabled = !permitido; // solo se bloquea si está TAKEN
    if (!permitido) chk.checked = false;
    chk.dataset.mitadLibre = mitadLibre || ""; // qué mitad admite este día

    if (lbl) {
      const base =
        lbl.getAttribute("data-base") ||
        lbl.textContent.replace(/\s*\(.*\)$/, "").trim();
      lbl.setAttribute("data-base", base);
      lbl.textContent = nota ? `${base} (${nota})` : base;
      lbl.style.color = permitido ? "" : "#adb5bd";
    }
  });
  validarActivacion();
}

// Registrar UNA sola vez el auto-ajuste de porción al marcar un día parcial
_DIAS_ID.forEach((d) => {
  const chk = document.getElementById(d);
  if (!chk) return;
  chk.addEventListener("change", function () {
    if (!this.checked) return;
    const mitad = this.dataset.mitadLibre || "";
    if (!mitad) return; // día totalmente libre, sin restricción

    const selPorc = document.getElementById("porcionTurno");
    const contraria =
      mitad === "primera_mitad" ? "segunda_mitad" : "primera_mitad";
    const choque = _DIAS_ID.some((x) => {
      const c = document.getElementById(x);
      return c && c.checked && c.dataset.mitadLibre === contraria;
    });
    if (choque) {
      this.checked = false;
      Swal.fire(
        "Atención",
        "Tienes días que solo admiten la primera mitad y otros solo la segunda. Regístralos por separado.",
        "warning",
      );
      return;
    }

    if (selPorc.value !== mitad) {
      selPorc.value = mitad;
      const etiqueta =
        mitad === "primera_mitad" ? "primera mitad" : "segunda mitad";
      Swal.fire(
        "Porción ajustada",
        `Ese día solo tiene libre la <b>${etiqueta}</b>, así que la porción se ajustó automáticamente.`,
        "info",
      );
      refrescarDiasSegunPorcion();
    }
  });
});

document
  .getElementById("porcionTurno")
  .addEventListener("change", refrescarDiasSegunPorcion);

function tomarVacanteV2(noempCubrir, puestoCubrir, libreJson) {
  const libre = JSON.parse(decodeURIComponent(libreJson));
  document.getElementById("IBMCubrir").value = noempCubrir;
  document.getElementById("puestoCubrir").value = puestoCubrir;

  let hayPrimera = false,
    haySegunda = false,
    hayCompleto = false;
  _DIAS_ID.forEach((d) => {
    const estado = libre[d] || "NADA";
    const marcar = estado !== "NADA";
    document.getElementById(d).checked = marcar;
    if (estado === "PRIMERA") hayPrimera = true;
    if (estado === "SEGUNDA") haySegunda = true;
    if (estado === "COMPLETO") hayCompleto = true;
  });

  // Si todos los días libres piden la misma mitad, preselecciona esa porción
  const selPorc = document.getElementById("porcionTurno");
  if (hayPrimera && !haySegunda && !hayCompleto)
    selPorc.value = "primera_mitad";
  else if (haySegunda && !hayPrimera && !hayCompleto)
    selPorc.value = "segunda_mitad";
  else selPorc.value = "completo";

  aplicarCandadoDias(noempCubrir);
  validarActivacion();
}
window.tomarVacanteV2 = tomarVacanteV2;
