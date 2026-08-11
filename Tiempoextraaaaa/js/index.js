import { Toolsjs } from "../../Tools/Tools.js";

class Tiempoextra {
  inicio() {
    this.Tools = new Toolsjs();
    setInterval(this.Tools.mostrarHoraSimple(), 1000);
    this.Tools.llnarslcruta("php/index.php?motivostiempoextra", "motivos");
    this.Tools.llnarslc("CatalogoPersonal", "GetSlcDeps", "departamentoenc", 0);

    // aplicar restricción después de llenar
    setTimeout(() => this.aplicarRestriccionDepartamento(), 500);
    this.tblenc();
  }

  // aplicarRestriccionDepartamento() {
  //     const claveDepto = document.getElementById("clvdepartamento").innerText.trim();
  //     const selectDeptos = document.getElementById("departamentoenc");
  //     const ibmUsuario = document.getElementById("ibmSup").innerText.trim(); // Ajusta al id correcto

  //     // Grupo 1: PAÑAL Y WASTE RECLAIM
  //     const grupo1Ibms = ["27419", "27644", "27676", "27705"];
  //     const grupo1DeptosPermitidos = ["1", "34",];

  //     // Grupo 2: PROTECCION FEMENINA, INCONTINENCIA, MASCARILLAS, REEMPAQUE
  //     const grupo2Ibms = ["28082", "37133", "46473", "47795"];
  //     const grupo2DeptosPermitidos = ["24", "25", "33", "73"];

  //     for (let i = 0; i < selectDeptos.options.length; i++) {
  //         const opt = selectDeptos.options[i];

  //         // Caso Grupo 1
  //         if (grupo1Ibms.includes(ibmUsuario)) {
  //             if (opt.value === claveDepto || grupo1DeptosPermitidos.includes(opt.value)) {
  //                 opt.disabled = false;
  //                 opt.style.color = "";
  //                 opt.style.backgroundColor = "";
  //                 opt.title = "";
  //             } else {
  //                 opt.disabled = true;
  //                 opt.style.color = "#aaa";
  //                 opt.style.backgroundColor = "#f0f0f0";
  //                 opt.title = "Tu departamento se selecciona automáticamente";
  //             }
  //         }
  //         // Caso Grupo 2
  //         else if (grupo2Ibms.includes(ibmUsuario)) {
  //             if (opt.value === claveDepto || grupo2DeptosPermitidos.includes(opt.value)) {
  //                 opt.disabled = false;
  //                 opt.style.color = "";
  //                 opt.style.backgroundColor = "";
  //                 opt.title = "";
  //             } else {
  //                 opt.disabled = true;
  //                 opt.style.color = "#aaa";
  //                 opt.style.backgroundColor = "#f0f0f0";
  //                 opt.title = "Tu departamento se selecciona automáticamente";
  //             }
  //         }
  //         // Caso IBM normal
  //         else {
  //             if (opt.value !== claveDepto) {
  //                 opt.disabled = true;
  //                 opt.style.color = "#aaa";
  //                 opt.style.backgroundColor = "#f0f0f0";
  //                 opt.title = "Tu departamento se selecciona automáticamente";
  //             } else {
  //                 opt.disabled = false;
  //                 opt.style.color = "";
  //                 opt.style.backgroundColor = "";
  //                 opt.title = "";
  //                 selectDeptos.value = claveDepto;
  //             }
  //         }
  //     }
  // }

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

    // El propio departamento del supervisor siempre queda habilitado
    const permitidos = new Set(idsPermitidos);
    if (claveDepto) permitidos.add(claveDepto);

    for (let i = 0; i < selectDeptos.options.length; i++) {
      const opt = selectDeptos.options[i];
      if (permitidos.has(String(opt.value))) {
        opt.disabled = false;
        opt.style.color = "";
        opt.style.backgroundColor = "";
        opt.title = "";
      } else {
        opt.disabled = true;
        opt.style.color = "#aaa";
        opt.style.backgroundColor = "#f0f0f0";
        opt.title = "No tienes este departamento asignado";
      }
    }

    // Preseleccionar el departamento propio si está disponible
    if (claveDepto && permitidos.has(claveDepto)) {
      selectDeptos.value = claveDepto;
    }
  }

  // async getinfoemp(noemp) {
  //     const respuetaraw = await fetch("../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp);
  //     const respuesta = await respuetaraw.json();
  //     if (respuesta.length === 0) {
  //         document.getElementById("nombre").value = "";
  //         document.getElementById("departamento").value = "";
  //         document.getElementById("puesto").value = "";
  //         document.getElementById("tipoEmpleadoHidden").value = "";
  //         aplicarRestriccionMotivo9("");
  //         return;
  //     }
  //     document.getElementById("nombre").value = respuesta[0].nombre;
  //     document.getElementById("departamento").value = respuesta[0].departamento.trim();
  //     document.getElementById("puesto").value = respuesta[0].puesto;

  //     const rawTipo = parseInt(respuesta[0].EmpleadoSindicalizado);
  //     const tipo = rawTipo === 1 ? "empleado" : "sindicalizado";
  //     document.getElementById("tipoEmpleadoHidden").value = tipo;
  //     aplicarRestriccionMotivo9(tipo);
  //     document.getElementById("departamento").dispatchEvent(new Event("change"));
  // }

  async getinfoemp(noemp) {
    const respuetaraw = await fetch(
      "../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp,
    );
    const respuesta = await respuetaraw.json();

    if (respuesta.length === 0) {
      document.getElementById("nombre").value = "";
      document.getElementById("departamento").value = "";
      document.getElementById("puesto").value = "";
      document.getElementById("tipoEmpleadoHidden").value = "";
      aplicarRestriccionMotivo9("");
      return;
    }

    const departamentoObtenido = respuesta[0].departamento.trim();

    // Select de comparación
    const selectDepto = document.getElementById("departamentoenc");
    const departamentoCompararId = selectDepto.value;
    const departamentoCompararTexto =
      selectDepto.options[selectDepto.selectedIndex].text;

    // Comparar contra el texto visible
    if (departamentoObtenido !== departamentoCompararTexto) {
      // Limpiar campos
      document.getElementById("noemp").value = "";
      document.getElementById("nombre").value = "";
      document.getElementById("departamento").value = "";
      document.getElementById("puesto").value = "";
      document.getElementById("tipoEmpleadoHidden").value = "";
      aplicarRestriccionMotivo9("");

      // Alerta con SweetAlert
      Swal.fire({
        icon: "warning",
        title: "Departamentos no coinciden",
        html: `El departamento del empleado no corresponde a tu departamento.`,
        confirmButtonText: "Entendido",
        confirmButtonColor: "#f0ad4e",
      });

      return;
    }

    // Si coincide, llenar normalmente
    document.getElementById("nombre").value = respuesta[0].nombre;
    document.getElementById("departamento").value = departamentoObtenido;
    document.getElementById("puesto").value = respuesta[0].puesto;

    const rawTipo = parseInt(respuesta[0].EmpleadoSindicalizado);
    const tipo = rawTipo === 1 ? "empleado" : "sindicalizado";
    document.getElementById("tipoEmpleadoHidden").value = tipo;
    aplicarRestriccionMotivo9(tipo);

    document.getElementById("departamento").dispatchEvent(new Event("change"));
  }

  async getinfohoraentradaysalida(noemp, date) {
    var fechaActual = new Date(date + "T00:00:00");
    var fechaAnterior = new Date(fechaActual);
    fechaAnterior.setDate(fechaAnterior.getDate() - 1);
    var fechaSiguiente = new Date(fechaActual);
    fechaSiguiente.setDate(fechaSiguiente.getDate() + 1);

    function formatearFecha(fecha) {
      return (
        fecha.getFullYear() +
        "-" +
        (fecha.getMonth() + 1).toString().padStart(2, "0") +
        "-" +
        fecha.getDate().toString().padStart(2, "0")
      );
    }

    const [respuestaAnterior, respuestaActual, respuestaSiguiente] =
      await Promise.all([
        fetch(
          "../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=" +
            noemp +
            "&fechabien=" +
            formatearFecha(fechaAnterior),
        ).then((r) => r.json()),
        fetch(
          "../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=" +
            noemp +
            "&fechabien=" +
            date,
        ).then((r) => r.json()),
        fetch(
          "../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=" +
            noemp +
            "&fechabien=" +
            formatearFecha(fechaSiguiente),
        ).then((r) => r.json()),
      ]);

    function agruparPorHora(registros) {
      var horasVistas = {},
        resultado = [];
      registros.forEach(function (registro) {
        if (registro.fecha_h) {
          var h = registro.fecha_h.substring(0, 8);
          if (!horasVistas[h]) {
            horasVistas[h] = true;
            resultado.push({ fecha_h: registro.fecha_h, hora_limpia: h });
          }
        }
      });
      return resultado;
    }

    var rAnt = agruparPorHora(respuestaAnterior);
    var rAct = agruparPorHora(respuestaActual);
    var rSig = agruparPorHora(respuestaSiguiente);

    function horaAMinutos(hora) {
      var p = hora.split(":");
      return parseInt(p[0]) * 60 + parseInt(p[1]);
    }
    function minutosAHora(m) {
      var h = Math.floor(m / 60),
        mn = m % 60;
      return (
        h.toString().padStart(2, "0") + ":" + mn.toString().padStart(2, "0")
      );
    }

    var turnos = {
      turno1: {
        entrada: { min: 5 * 60, max: 11 * 60 },
        salida: { min: 13 * 60, max: 20 * 60 },
      },
      turno2: {
        entrada: { min: 13 * 60, max: 17 * 60 },
        salida: { min: 20 * 60, max: 24 * 60 },
      },
      turno3: {
        entrada: { min: 20 * 60, max: 24 * 60 },
        salida: { min: 5 * 60, max: 11 * 60 },
      },
      mixto1: {
        entrada: { min: 7 * 60 + 20, max: 9 * 60 },
        salida: { min: 15 * 60 + 30, max: 19 * 60 },
      },
      mixto2: {
        entrada: { min: 7 * 60 + 30, max: 10 * 60 },
        salida: { min: 17 * 60, max: 21 * 60 },
      },
      mixto3: {
        entrada: { min: 7 * 60, max: 9 * 60 },
        salida: { min: 16 * 60, max: 17 * 60 },
      },
      mixto4: {
        entrada: { min: 6 * 60 + 45, max: 7 * 60 + 15 },
        salida: { min: 16 * 60 + 30, max: 17 * 60 + 30 },
      },
    };

    function estaEnRango(m, r) {
      return m >= r.min && m <= r.max;
    }

    var entrada, salida;
    if (rAct.length >= 2) {
      var primeraMin = horaAMinutos(rAct[0].hora_limpia);
      var ultimaMin = horaAMinutos(rAct[rAct.length - 1].hora_limpia);
      var esTurnoNocturno = false;
      for (var t in turnos) {
        if (
          estaEnRango(ultimaMin, turnos[t].entrada) &&
          (t === "turno3" || t === "mixto3")
        ) {
          esTurnoNocturno = true;
          break;
        }
      }
      if (esTurnoNocturno) {
        entrada = rAct[rAct.length - 1];
        salida = rSig.length > 0 ? rSig[0] : null;
      } else {
        entrada = rAct[0];
        salida = rAct[rAct.length - 1];
      }
    } else if (rAct.length === 1) {
      var unicaMin = horaAMinutos(rAct[0].hora_limpia);
      if (
        estaEnRango(unicaMin, turnos.turno3.entrada) ||
        estaEnRango(unicaMin, turnos.mixto3.entrada)
      ) {
        entrada = rAct[0];
        salida = rSig.length > 0 ? rSig[0] : null;
      } else if (estaEnRango(unicaMin, turnos.turno3.salida)) {
        salida = rAct[0];
        entrada = rAnt.length > 0 ? rAnt[rAnt.length - 1] : null;
      } else {
        entrada = rAct[0];
        salida = null;
      }
    }

    if (!entrada || !salida) return;

    var horaentrada = entrada.hora_limpia,
      horasalida = salida.hora_limpia;
    var esSabado = [6, 0].includes(new Date(date + "T00:00:00").getDay());
    var resultado = calcularTurnoYHoras(horaentrada, horasalida, esSabado);

    let horaExtra2 = "00:00:00";
    if (resultado.totalHoras <= "00:05:00") return;
    const horaExtrass = horaAMinutos(resultado.totalHoras);
    const horaReglamen = horaAMinutos(resultado.horasReglamentarias);
    if (horaExtrass >= horaReglamen)
      horaExtra2 = minutosAHora(horaExtrass - horaReglamen);

    validarTiempoExtraLocal(
      horaExtra2,
      resultado.totalHoras,
      resultado.horasReglamentarias,
    );
    if (document.getElementById("horaEX"))
      document.getElementById("horaEX").value = horaExtra2;
    if (document.getElementById("hrsReg"))
      document.getElementById("hrsReg").value = resultado.horasReglamentarias;
  }

  async abrirtiempoextra() {
    let folio = document.getElementById("folio").value;
    let fechaenc = document.getElementById("fechaenc").value;
    let departamentoenc = document.getElementById("departamentoenc").value;

    if (fechaenc === "" || departamentoenc === "") {
      Swal.fire("UPS!!!", "No puede haber campos vacíos", "info");
      return false;
    }
    if (folio != "") {
      Swal.fire(
        "UPS!!!",
        "Estas editando un folio, selecciona empezar de nuevo para crear un nuevo folio",
        "info",
      );
      return false;
    }

    const data = new FormData();
    data.append("fechaenc", fechaenc);
    data.append("departamentoenc", departamentoenc);

    try {
      const respuetaraw = await fetch("php/index.php?abrirtiempoextra", {
        method: "POST",
        body: data,
      });
      const respuesta = await respuetaraw.json();
      if (respuesta.error) {
        Swal.fire({
          icon: "error",
          title: "No se pudo registrar el folio",
          text: respuesta.error,
          confirmButtonText: "Entendido",
        });
        return;
      }
      Swal.fire(
        "Listo!!!",
        "Empieza a cargar los tiempos extra al folio " + respuesta.id,
        "success",
      );
      document.getElementById("folio").value = respuesta.id;
      document.getElementById("formtiempoextra").reset();
      document.getElementById("fechaenc").disabled = true;
      document.getElementById("departamentoenc").disabled = true;
      document.getElementById("semanaFolioHidden").value = respuesta.semana;
    } catch (err) {
      Swal.fire("ERROR!", "La respuesta no es JSON válido: " + err, "error");
    }
  }

  async guardartiempoextra() {
    let noemp = document.getElementById("noemp").value;
    let fechainput = document.getElementById("fechainput").value;
    let horai = document.getElementById("horai").value;
    let horaf = document.getElementById("horaf").value;
    let maquina = document.getElementById("maquinas").value;
    let motivos = document.getElementById("motivos").value;
    let razon = document.getElementById("razon").value;
    let folio = document.getElementById("folio").value;
    let turnosel = document.getElementById("turnosel").value;
    let nombre = document.getElementById("nombre").value;
    let semanaFolio = document.getElementById("semanaFolioHidden").value;

    const chk12hrs = document.getElementById("checkboxTurno3");
    const chkAnticipo = document.getElementById("checkboxAnticipo");
    const chkReingreso = document.getElementById("checkboxApoyo");
    const activaTurno3_12hrs = chk12hrs.checked;
    const activaAnticipo = chkAnticipo.checked;
    const activaReingreso = chkReingreso.checked;

    // ── Validación de semana ──────────────────────────────────────────────
    let semanaRegistro = getWeekNumber(new Date(fechainput));
    if (semanaRegistro < semanaFolio - 1) {
      Swal.fire(
        "UPS!!!",
        "La fecha seleccionada pertenece a una semana demasiado antigua respecto al folio.",
        "warning",
      );
      return;
    }
    if (semanaRegistro > semanaFolio + 1) {
      Swal.fire(
        "UPS!!!",
        "La fecha seleccionada pertenece a una semana muy adelantada respecto al folio.",
        "warning",
      );
      return;
    }

    let turnoSeleccionado = document.getElementById(
      "turnoSeleccionadoHidden",
    ).value;
    let horaFinalSinMargen = document.getElementById(
      "horaFinalSinMargenHidden",
    ).value;
    let horaFinalConMargen = document.getElementById(
      "horaFinalConMargenHidden",
    ).value;
    const motivoNum = parseInt(motivos);

    // ── Modo Reingreso ────────────────────────────────────────────────────
    if (activaReingreso) {
      turnosel = "turno1";
      turnoSeleccionado = "turno1";
      horaf = horaf || "00:00:00";
    }

    // ── Lógica por motivo ─────────────────────────────────────────────────
    if (motivoNum === 8) {
      let hra = parseFloat(document.getElementById("cambiohrario").value);
      if (hra === 3) {
        horai = "01:00:00";
        horaf = "04:00:00";
      } else if (hra === 5) {
        horai = "01:00:00";
        horaf = "06:00:00";
      } else if (hra === 2.5) {
        horai = "01:00:00";
        horaf = "03:30:00";
      }
      turnosel = "turno1";
    } else if (motivoNum === 9) {
      const durComida = document.getElementById("horacomida").value;
      horai = "12:00:00";
      const [hc, mc] = durComida.split(":").map(Number);
      const finComidaMin = 12 * 60 + hc * 60 + mc;
      horaf =
        Math.floor(finComidaMin / 60)
          .toString()
          .padStart(2, "0") +
        ":" +
        (finComidaMin % 60).toString().padStart(2, "0") +
        ":00";
      turnosel = "turno1";
    } else if (motivoNum === 10 || motivoNum === 12) {
      if (!horai || !horaf || turnosel === "") {
        Swal.fire(
          "Error",
          "Debes seleccionar el turno antes de guardar.",
          "warning",
        );
        return;
      }
    } else if (!activaAnticipo && !activaReingreso) {
      if (activaTurno3_12hrs && turnoSeleccionado === "turno3")
        turnosel = "turno3_12hrs";
      else if (activaTurno3_12hrs && turnoSeleccionado === "turno2")
        turnosel = "turno2_13hrs";
    }

    // ── Validaciones de campos vacíos ─────────────────────────────────────
    const motivosSinDuracion = [9, 8, 10, 12];
    const necesitaDuracion =
      !motivosSinDuracion.includes(motivoNum) && !activaReingreso;

    if (folio === "") {
      Swal.fire("Error!", "Necesitas crear un folio primero", "warning");
      return;
    }
    if (
      noemp === "" ||
      fechainput === "" ||
      maquina === "" ||
      motivos === "" ||
      razon === "" ||
      nombre === ""
    ) {
      Swal.fire("Error", "No puede haber campos vacíos", "info");
      return;
    }
    if (necesitaDuracion && !document.getElementById("duracionTE").value) {
      Swal.fire(
        "Error",
        "Debes ingresar la cantidad de horas de tiempo extra.",
        "info",
      );
      return;
    }
    if (
      ![9, 8].includes(motivoNum) &&
      !activaReingreso &&
      (!turnosel || turnosel === "")
    ) {
      Swal.fire("Error", "Debes seleccionar un turno.", "info");
      return;
    }

    // ── Validación IBM especiales (27585 y 27903) ─────────────────────────
    // Solo pueden solicitar motivos 10 y 12, máximo 10 hrs acumuladas por folio
    const IBM_ESPECIALES = [27585, 27903];
    const noempInt = parseInt(noemp);
    if (IBM_ESPECIALES.includes(noempInt)) {
      // Solo motivos 10 (descanso trabajado) y 12 (día festivo)
      if (![10, 12].includes(motivoNum)) {
        Swal.fire(
          "Restricción",
          `El empleado <b>${noemp}</b> solo puede solicitar tiempos extra por <b>Descanso Trabajado</b> o <b>Día Festivo</b>.`,
          "warning",
        );
        return;
      }
      // Verificar acumulado del folio — se valida también en PHP,
      // pero aquí bloqueamos antes del fetch para mejor UX
      // (la validación definitiva viene del servidor)
    }

    // ── Validación de DOBLETE en JS ───────────────────────────────────────
    if (!activaAnticipo && !activaReingreso && necesitaDuracion) {
      const durStr = document.getElementById("duracionTE").value;
      const durMin = durStr ? horaAMinutos(durStr) : 0;
      const esSabado = [6, 0].includes(
        new Date(fechainput + "T00:00:00").getDay(),
      );

      // const resultadoDoblete = evaluarDoblete(turnoSeleccionado, durMin, esSabado);

      // if (resultadoDoblete === "ERROR_LIMITE") {
      //     Swal.fire('Error',
      //         `En el turno <b>${turnoSeleccionado}</b> solo se permiten:<br>
      //          • Hasta 3:30 hrs (tiempo extra normal)<br>
      //          • Entre 3:30 y 4:30 hrs (requiere casilla 12 hrs)<br>
      //          • Igual a las horas reglamentarias de un turno adyacente (doblete)<br>
      //          • Más de 8 hrs (doblete de turno completo)<br>
      //          La cantidad ingresada no corresponde a ninguno de estos casos.`,
      //         'warning');
      //     return;
      // }

      // if (resultadoDoblete === "DOBLETE") {

      const resultadoDoblete = evaluarDoblete(
        turnoSeleccionado,
        durMin,
        esSabado,
      );

      // 3:30 a 4:30 hrs en turno 2/3: obligatorio marcar la casilla 12 hrs
      if (resultadoDoblete === "12HRS" && !activaTurno3_12hrs) {
        Swal.fire(
          "Falta marcar 12 hrs",
          `La cantidad ingresada (<b>${durStr}</b>) en el turno <b>${turnoSeleccionado}</b>
                        corresponde a un caso de <b>12 horas</b>.<br><br>
                        Debes marcar la casilla <b>"12 hrs"</b> para poder registrarlo.`,
          "warning",
        );
        return;
      }

      if (resultadoDoblete === "ERROR_LIMITE") {
        Swal.fire(
          "Error",
          `La cantidad ingresada (<b>${durStr}</b>) en el turno <b>${turnoSeleccionado}</b> no es válida.`,
          "warning",
        );
        return;
      }

      if (resultadoDoblete === "DOBLETE") {
        const confirmDoblete = await Swal.fire({
          title: "Advertencia — Doblete",
          html: `La cantidad de horas solicitadas (<b>${durStr}</b>) en el turno <b>${turnoSeleccionado}</b>
                           corresponde a un turno adyacente completo, lo que genera un <b>DOBLETE</b>.<br><br>
                           Si continúas, el registro se guardará con la razón <b>"Doblete"</b>.`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, es un doblete",
          cancelButtonText: "No, cancelar",
        });
        if (!confirmDoblete.isConfirmed) return;
        // Sobreescribir razón con Doblete
        razon = "Doblete";
        document.getElementById("razon").value = "Doblete";
      }
    }

    // ── Validación de anticipo con doblete ────────────────────────────────
    if (activaAnticipo && necesitaDuracion) {
      const durStr = document.getElementById("duracionTE").value;
      const durMin = durStr ? horaAMinutos(durStr) : 0;
      const esSabado = [6, 0].includes(
        new Date(fechainput + "T00:00:00").getDay(),
      );
      const resultadoDoblete = evaluarDoblete(
        turnoSeleccionado,
        durMin,
        esSabado,
      );

      if (resultadoDoblete === "DOBLETE") {
        const confirmDoblete = await Swal.fire({
          title: "Advertencia — Anticipo con Doblete",
          html: `La cantidad de horas anticipadas (<b>${durStr}</b>) en el turno <b>${turnoSeleccionado}</b>
                           corresponde a un turno adyacente completo, lo que genera un <b>DOBLETE</b>.<br><br>
                           Si continúas, el registro se guardará con la razón <b>"Anticipo"</b> (el anticipo ya implica doblete).`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, continuar",
          cancelButtonText: "No, cancelar",
        });
        if (!confirmDoblete.isConfirmed) return;
        // Para anticipo con doblete, la razón ya es "Anticipo"
      }
    }

    // ── Construir FormData ────────────────────────────────────────────────
    const data = new FormData();
    const dataCSV = new FormData();

    data.append("noemp", noemp);
    data.append("fechainput", fechainput);
    data.append("horai", horai);
    data.append("horaf", horaf);
    data.append("maquina", maquina);
    data.append("motivos", motivos);
    data.append("razon", razon);
    data.append("folio", folio);
    data.append("turnosel", turnosel);
    data.append("nombre", nombre);

    dataCSV.append("noemp", noemp);
    dataCSV.append("fechainput", fechainput);
    dataCSV.append("horai", horai);
    dataCSV.append("horaf", horaf);
    dataCSV.append("maquina", maquina);
    dataCSV.append("motivos", motivos);
    dataCSV.append("razon", razon);
    dataCSV.append("folio", folio);
    dataCSV.append("turnoSeleccionado", turnoSeleccionado);
    dataCSV.append("horaFinalSinMargen", horaFinalSinMargen);
    dataCSV.append("horaFinalConMargen", horaFinalConMargen);

    const respuetaraw = await fetch("php/index.php?guardartiempoextra", {
      method: "POST",
      body: data,
    });
    const respuetarawcsv = await fetch("php/guardarCSV.php?guardarCSV", {
      method: "POST",
      body: dataCSV,
    });
    const respuesta = await respuetaraw.json();

    // ── Restricción IBM especiales (respuesta del servidor) ───────────────
    if (respuesta.error_especial) {
      Swal.fire("Restricción", respuesta.message, "warning");
      return;
    }

    if (respuesta.warning) {
      const result = await Swal.fire({
        title: "Advertencia",
        text: respuesta.message,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, continuar",
        cancelButtonText: "No, cancelar",
      });
      if (result.isConfirmed) {
        const confirmRaw = await fetch("php/index.php?guardartiempoextraExt", {
          method: "POST",
          body: data,
        });
        const confirmResp = await confirmRaw.json();
        if (confirmResp === "Listo") {
          this.tblsubenc();
          Swal.fire(
            "Listo!!!",
            "Registro guardado y enviado con éxito",
            "success",
          );
          cleanData();
        } else {
          Swal.fire("Error", "No se pudo guardar", "error");
        }
      }
      return;
    }

    if (respuesta === "Listo") {
      Swal.fire("Listo!!!", "Registro guardado y enviado con éxito", "success");
      cleanData();
    } else if (respuesta === "Existe") {
      Swal.fire("UPS!!!", "Ya tienes un tiempo extra existente", "error");
    } else if (respuesta === "LimiteSemana") {
      Swal.fire(
        "UPS!!!",
        "Se alcanzó el límite de 60.5 horas en esta semana",
        "warning",
      );
    } else {
      Swal.fire("ERROR!!!", "Error al guardar en la base de datos", "error");
    }

    function cleanData() {
      // document.getElementById("noemp").value       = "";
      document.getElementById("fechainput").value = "";
      document.getElementById("horai").value = "";
      document.getElementById("horaf").value = "";
      // document.getElementById("maquinas").value    = "";
      document.getElementById("motivos").value = "";
      document.getElementById("razon").value = "";
      document.getElementById("turnosel").value = "";
      // document.getElementById("nombre").value      = "";
      // document.getElementById("departamento").value = "";
      // document.getElementById("puesto").value      = "";
      document.getElementById("duracionTE").value = "";
      document.getElementById("tipoEmpleadoHidden").value = "";
      document.getElementById("horariosDeTurno").style.display = "none";
      resetearCheckboxes();
    }
  }

  async deletesub(id) {
    const data = new FormData();
    data.append("id", id);
    const respuestaraw = await fetch("./php/index.php?deletesub", {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    this.tblsubenc();
    respuesta === "Listo"
      ? Swal.fire("Listo!!!", "Registro eliminado", "success")
      : Swal.fire("ERROR!!!", "Hay un problema al eliminar", "error");
  }

  async cambioTempTurno(noemp, estadoTurno, nombre, depto, id) {
    abrirModal();
    // + " - " + noemp
    // document.getElementById("ibmCTT").value = noemp;
    document.getElementById("nombre_receptor").value = nombre;
    document.getElementById("turno_presentacion").value = estadoTurno;
    document.getElementById("Depto_m").value = depto;
    document.getElementById("folioTiempoExtra").value = id;
    document.getElementById("ibm_empleado").value = noemp;
  }

  async tblsubenc() {
    let folio = document.getElementById("folio").value;
    if (folio === "") return false;
    const respuetaraw = await fetch("php/index.php?tblsubenc&folio=" + folio);
    const respuesta = await respuetaraw.json();
    let body = "";
    respuesta.forEach((elemento) => {
      body += `<tr>
                <td>${elemento.id}</td><td>${elemento.noemp}</td><td>${elemento.nombre}</td>
                <td>${elemento.depto}</td><td>${elemento.puesto}</td><td>${elemento.fecha}</td>
                <td>${elemento.horai}</td><td>${elemento.horaf}</td><td>${elemento.maquina}</td>
                <td>${elemento.motivo}</td><td>${elemento.razon}</td><td>${elemento.estadoTurno}</td>
                <td><span class="${elemento.estadoClass}">${elemento.estadoTexto}</span></td><td>`;
      if (elemento.cambioTempExiste) {
        body += `<button class="btn btn-sm btn-primary" onclick="verPDF(${elemento.Ctt_id})"><i class="fa-solid fa-file-pdf"></i> Ver PDF Cambio T. Turno</button>
                         <button class="btn btn-sm btn-danger" onclick="deleteSub(${elemento.id})"><i class="fa-solid fa-trash"></i> Eliminar</button>`;
      } else {
        body += `<button class="btn btn-sm btn-success" onclick="cambioTempTurno(${elemento.noemp}, '${elemento.estadoTurno}', '${elemento.nombre}', '${elemento.depto}', '${elemento.id}')"><i class="fa-solid fa-plus"></i> Crea un Cambio T. Turno</button>
                         <button class="btn btn-sm btn-danger" onclick="deleteSub(${elemento.id})"><i class="fa-solid fa-trash"></i> Eliminar</button>`;
      }
      body += `</td></tr>`;
    });
    document.getElementById("tbltiempoextra").innerHTML = body;
  }

  // async tblenc() {
  //     const respuetaraw = await fetch("php/index.php?tblenc");
  //     const respuesta   = await respuetaraw.json();
  //     let body = "";
  //     respuesta.forEach(elemento => {
  //         body += `<tr>
  //                     <td>${elemento.id}</td>
  //                     <td>${elemento.fecha}</td>
  //                     <td>${elemento.departamento}</td>
  //                     <td>${elemento.creado}</td>
  //                     <td>`;
  //                         if (elemento.terminado == null)
  //                             body += `<button class="btn btn-sm btn-warning" onclick="editEnc(${elemento.id})">
  //                                         <i class="fa-solid fa-pen-to-square"></i>
  //                                             Crear/eliminar registros
  //                                         </button>`;
  //                         else body += `<button class="btn btn-sm btn-danger" onclick="pdfFin(${elemento.id})">
  //                                         <i class="fa-solid fa-file-pdf"></i>
  //                                             Descargar resultado PDF
  //                                         </button>`;
  //                         body += `</td></tr>`;
  //     });
  //     document.getElementById("tblenc").innerHTML = body;
  // }

  async tblenc() {
    const respuetaraw = await fetch("php/index.php?tblenc");
    const respuesta = await respuetaraw.json();

    let pendientes = "";
    let procesadas = "";
    let countPendientes = 0;
    let countProcesadas = 0;

    respuesta.forEach((elemento) => {
      let accionesHtml = "";

      if (elemento.terminado == null) {
        accionesHtml = `
                    <button class="btn btn-sm btn-warning" onclick="editEnc(${elemento.id})">
                        <i class="fa-solid fa-pen-to-square"></i> Crear/eliminar registros
                    </button>
                `;
      } else {
        accionesHtml = `
                    <button class="btn btn-sm btn-danger" onclick="pdfFin(${elemento.id})">
                        <i class="fa-solid fa-file-pdf"></i> Descargar resultado PDF
                    </button>
                `;
      }

      let row = `
                <tr>
                    <td>${elemento.id}</td>
                    <td>${elemento.fecha}</td>
                    <td>${elemento.departamento}</td>
                    <td>${elemento.autor}</td>
                    <td>${elemento.creado}</td>
                    <td>${accionesHtml}</td>
                </tr>
            `;

      if (elemento.terminado == null) {
        pendientes += row;
        countPendientes++;
      } else {
        procesadas += row;
        countProcesadas++;
      }
    });

    document.getElementById("tblPendientes").innerHTML = pendientes;
    document.getElementById("tblProcesadas").innerHTML = procesadas;
    document.getElementById("countPendientes").innerText = countPendientes;
    document.getElementById("countProcesadas").innerText = countProcesadas;
  }

  async editenc(id) {
    const respuetaraw = await fetch("php/index.php?editenc&id=" + id);
    const respuesta = await respuetaraw.json();
    document.getElementById("folio").value = respuesta[0].id;
    document.getElementById("fechaenc").value = respuesta[0].fecha;
    document.getElementById("departamentoenc").value =
      respuesta[0].departamento;
    document.getElementById("fechaenc").disabled = true;
    document.getElementById("departamentoenc").disabled = true;
    document.getElementById("semanaFolioHidden").value = respuesta[0].semana;
    this.tblsubenc();
  }

  enviar(id) {
    Swal.fire({
      title: "¿Estás seguro?",
      text: "¿Deseas enviar este tiempo extra a autorización del supervisor?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Si, seguro!",
      cancelButtonText: "No, cancela!",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        (async () => {
          const respuestaraw = await fetch(
            "./php/index.php?enviarfol&id=" + id,
          );
          const respuesta = await respuestaraw.json();
          respuesta === false
            ? Swal.fire("ERROR!", "Hay un error con la base de datos", "error")
            : Swal.fire(
                "Terminado!",
                "La solicitud fue enviada al área correspondiente",
                "success",
              );
          window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
          window.location.reload();
        })();
      }
    });
  }

  pdffin(id) {
    window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// CATÁLOGO DE HORAS REGLAMENTARIAS POR TURNO
// ─────────────────────────────────────────────────────────────────────────────
const HRS_REGL = {
  turno1: 8 * 60,
  turno2: 7 * 60 + 30,
  turno3: 8 * 60 + 30,
  turno3_12hrs: 8 * 60 + 30,
  turno2_13hrs: 4 * 60 + 30,
  mixto1: 10 * 60,
  mixto2: 10 * 60,
  mixto3: 9 * 60 + 30,
  mixto4: 10 * 60,
};

// Turnos adyacentes a cada turno (anterior y siguiente)
const TURNOS_ADYACENTES = {
  turno1: ["turno3", "turno2"],
  turno2: ["turno1", "turno3"],
  turno3: ["turno2", "turno1"],
};

// ─────────────────────────────────────────────────────────────────────────────
// evaluarDoblete — determina si las horas solicitadas generan un doblete
//
// Retorna:
//   "NORMAL"      → tiempo extra normal (≤ 3:30 hrs en turnos 2/3)
//   "12HRS"       → caso de 12 hrs (3:30 a 4:30 en turnos 2/3)
//   "DOBLETE"     → horas coinciden con reglamentarias de turno adyacente
//                   o ≥ 8 hrs en cualquier turno
//                   o ≥ 5 hrs en sábado mixto
//   "ERROR_LIMITE" → cantidad no permitida (entre 4:30 y 8 hrs en turno 2/3 sin ser doblete)
// ─────────────────────────────────────────────────────────────────────────────
function evaluarDoblete(turno, durMin, esSabado = false) {
  const TURNOS_MIXTOS = ["mixto1", "mixto2", "mixto3", "mixto4"];
  const TURNOS_NOCTURNOS = ["turno2", "turno3"];
  const MARGEN = 20; // ±20 min de tolerancia para reconocer hrs reglamentarias

  // ── Sábado mixto: ≥ 5 hrs = doblete de guardia ───────────────────────────
  if (esSabado && TURNOS_MIXTOS.includes(turno)) {
    if (durMin >= 5 * 60) return "DOBLETE";
    return "NORMAL";
  }

  // ── Turnos mixtos: ≥ 8 hrs = doblete ─────────────────────────────────────
  if (TURNOS_MIXTOS.includes(turno)) {
    if (durMin >= 8 * 60) return "DOBLETE";
    if (durMin >= 55) return "NORMAL"; // tiempo extra normal con excedente mínimo
    return "NORMAL";
  }

  // ── Turno 1: no tiene restricción de 12 hrs, solo verificar adyacentes ───
  if (turno === "turno1") {
    // Verificar si las horas coinciden con turno adyacente (turno3: 8:30, turno2: 7:30)
    const adyacentes = TURNOS_ADYACENTES["turno1"] || [];
    for (const adj of adyacentes) {
      const reglAdj = HRS_REGL[adj];
      if (reglAdj && Math.abs(durMin - reglAdj) <= MARGEN) return "DOBLETE";
    }
    // También doblete si ≥ 8 hrs
    if (durMin >= 8 * 60) return "DOBLETE";
    return "NORMAL";
  }

  // ── Turno 2 y Turno 3: lógica con rangos ─────────────────────────────────
  //     if (TURNOS_NOCTURNOS.includes(turno)) {
  //         if (durMin <= 3 * 60 + 30) return "NORMAL";              // ≤ 3:30: normal
  //         if (durMin <= 4 * 60 + 30) return "12HRS";               // 3:30 - 4:30: 12 hrs

  //         // Entre 4:30 y 8 hrs: verificar si corresponde a turno adyacente
  //         const adyacentes = TURNOS_ADYACENTES[turno] || [];
  //         let esDoblete = false;
  //         for (const adj of adyacentes) {
  //             const reglAdj = HRS_REGL[adj];
  //             if (reglAdj && Math.abs(durMin - reglAdj) <= MARGEN) { esDoblete = true; break; }
  //         }
  //         if (esDoblete) return "DOBLETE";

  //         // ≥ 8 hrs siempre doblete
  //         if (durMin >= 8 * 60) return "DOBLETE";

  //         // Entre 4:30 y 8 hrs sin ser doblete ni 12 hrs → no permitido
  //         return "ERROR_LIMITE";
  //     }

  //     return "NORMAL";
  // }

  // ── Turno 2 y Turno 3: lógica con rangos ─────────────────────────────────
  if (TURNOS_NOCTURNOS.includes(turno)) {
    if (durMin <= 3 * 60 + 30) return "NORMAL"; // ≤ 3:30: normal
    if (durMin <= 4 * 60 + 30) return "12HRS"; // 3:30 - 4:30: requiere casilla 12 hrs

    // ≥ 8 hrs siempre doblete
    if (durMin >= 8 * 60) return "DOBLETE";

    // Coincide con horas reglamentarias de turno adyacente → doblete
    const adyacentes = TURNOS_ADYACENTES[turno] || [];
    for (const adj of adyacentes) {
      const reglAdj = HRS_REGL[adj];
      if (reglAdj && Math.abs(durMin - reglAdj) <= MARGEN) return "DOBLETE";
    }

    // Entre 4:30 y 8 hrs sin coincidir con adyacente → tiempo extra normal (ahora permitido)
    return "NORMAL";
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// UTILIDADES GLOBALES
// ─────────────────────────────────────────────────────────────────────────────
function horaAMinutos(hora) {
  let partes = hora.split(":");
  return parseInt(partes[0]) * 60 + parseInt(partes[1]);
}

function minutosAHora(minutos) {
  minutos = ((minutos % (24 * 60)) + 24 * 60) % (24 * 60);
  return (
    Math.floor(minutos / 60)
      .toString()
      .padStart(2, "0") +
    ":" +
    (minutos % 60).toString().padStart(2, "0")
  );
}

function minutosAHoraConSegundos(minutos) {
  return minutosAHora(minutos) + ":00";
}

function getWeekNumber(input) {
  let date =
    input instanceof Date
      ? new Date(input.getTime())
      : (() => {
          const [y, m, d] = input.split("-").map(Number);
          return new Date(y, m - 1, d);
        })();
  const tempDate = new Date(date.getTime());
  tempDate.setDate(tempDate.getDate() + 4 - (tempDate.getDay() || 7));
  const yearStart = new Date(tempDate.getFullYear(), 0, 1);
  return Math.ceil(((tempDate - yearStart) / 86400000 + 1) / 7);
}

function validarTiempoExtraLocal(horasExtra, totalHoras, horasReglamentarias) {
  /* referencial */
}

function calcularTurnoYHoras(horaEntrada, horaSalida, esSabado = false) {
  var entradaMin = horaAMinutos(horaEntrada);
  var salidaMin = horaAMinutos(horaSalida);
  if (salidaMin < entradaMin) salidaMin += 1440;
  var margen = 35;
  var turnos = {
    turno1: {
      inicio: 7 * 60,
      fin: 15 * 60,
      nombre: "Turno 1 (07:00:00 - 15:00:00)",
      duracion: "08:00:00",
    },
    turno2: {
      inicio: 15 * 60,
      fin: 22 * 60 + 30,
      nombre: "Turno 2 (15:00:00 - 22:30:00)",
      duracion: "07:30:00",
    },
    turno3: {
      inicio: 22 * 60 + 30,
      fin: 7 * 60 + 10,
      nombre: "Turno 3 (22:30:00 - 07:00:00)",
      duracion: "08:30:00",
    },
    mixto4: {
      inicio: 7 * 60,
      fin: 17 * 60,
      nombre: "Mixto 4 (07:00:00 - 17:00:00)",
      duracion: "10:00:00",
    },
    mixto1: {
      inicio: 7 * 60 + 20,
      fin: 17 * 60 + 10,
      nombre: "Mixto 1 (07:30:00 - 17:00:00)",
      duracion: "10:00:00",
    },
    mixto2: {
      inicio: 8 * 60 + 30,
      fin: 18 * 60 + 30,
      nombre: "Mixto 2 (08:30:00 - 18:30:00)",
      duracion: "10:00:00",
    },
    mixto3: {
      inicio: 7 * 60,
      fin: 16 * 60 + 30,
      nombre: "Mixto 3 (07:00:00 - 16:30:00)",
      duracion: "09:30:00",
    },
  };
  var turnoDetectado = null;
  for (var key in turnos) {
    var t = turnos[key];
    var mE = key === "mixto4" ? 15 : margen;
    if (
      Math.abs(entradaMin - t.inicio) <= mE &&
      Math.abs(salidaMin - t.fin) <= margen * 4
    ) {
      turnoDetectado = key;
      break;
    }
  }
  if (!turnoDetectado) {
    var prioridad = [
      "mixto4",
      "mixto1",
      "mixto2",
      "turno1",
      "turno2",
      "mixto3",
      "turno3",
    ];
    for (var i = 0; i < prioridad.length; i++) {
      var k = prioridad[i],
        tt = turnos[k],
        mE2 = k === "mixto4" ? 15 : margen;
      if (Math.abs(entradaMin - tt.inicio) <= mE2) {
        turnoDetectado = k;
        break;
      }
    }
  }
  var td = turnoDetectado ? turnos[turnoDetectado] : null;
  var horaInicioTurno, horaFinTurno, nombreTurno, horasReglamentarias;
  if (td) {
    horaInicioTurno = minutosAHora(td.inicio);
    horaFinTurno = minutosAHora(td.fin);
    nombreTurno = td.nombre;
    horasReglamentarias = esSabado ? "05:00:00" : td.duracion;
    if (esSabado) nombreTurno = "MIXTO GUARDIA (Sáb/Dom)";
  } else {
    horaInicioTurno = horaEntrada;
    horaFinTurno = "00:00:00";
    nombreTurno = "No hay";
    horasReglamentarias = "00:00:00";
  }
  var minutosTrabajados = salidaMin - entradaMin;
  var finTurnoMin = td ? td.fin : salidaMin;
  var horasExtrasMin = salidaMin - finTurnoMin;
  return {
    turno: nombreTurno,
    horaInicioTurno,
    horaFinTurno,
    horasExtras: horasExtrasMin > 0 ? minutosAHora(horasExtrasMin) : "00:00:00",
    totalHoras: minutosAHora(minutosTrabajados),
    horasReglamentarias,
    salidaMin,
    entradaMin,
    finTurnoMin,
  };
}

// ─────────────────────────────────────────────────────────────────────────────
// CATÁLOGO DE TURNOS PARA ANTICIPO
// ─────────────────────────────────────────────────────────────────────────────
const CATALOGO_TURNOS_CAPTURA = {
  turno1: { inicio: 7 * 60, fin: 15 * 60, hrsRegl: "08:00:00" },
  turno2: { inicio: 15 * 60, fin: 22 * 60 + 30, hrsRegl: "07:30:00" },
  turno3: { inicio: 22 * 60 + 30, fin: 7 * 60, hrsRegl: "08:30:00" },
  turno2_13hrs: {
    inicio: 10 * 60 + 30,
    fin: 22 * 60 + 30,
    hrsRegl: "04:30:00",
  },
  turno3_12hrs: { inicio: 19 * 60, fin: 7 * 60, hrsRegl: "08:30:00" },
  mixto1: { inicio: 7 * 60 + 30, fin: 17 * 60, hrsRegl: "10:00:00" },
  mixto2: { inicio: 8 * 60 + 30, fin: 18 * 60 + 30, hrsRegl: "10:00:00" },
  mixto3: { inicio: 7 * 60, fin: 16 * 60 + 30, hrsRegl: "09:30:00" },
  mixto4: { inicio: 7 * 60, fin: 17 * 60, hrsRegl: "10:00:00" },
};

function aplicarRestriccionMotivo9(tipo) {
  const selectMotivos = document.getElementById("motivos");
  for (let i = 0; i < selectMotivos.options.length; i++) {
    const opt = selectMotivos.options[i];
    if (opt.value === "9" || opt.value === "8") {
      if (tipo === "empleado") {
        opt.disabled = true;
        opt.style.color = "#aaa";
        opt.style.backgroundColor = "#f0f0f0";
        opt.title = "Solo disponible para sindicalizados";
        if (selectMotivos.value === "9" || selectMotivos.value === "8")
          selectMotivos.value = "";
      } else {
        opt.disabled = false;
        opt.style.color = "";
        opt.style.backgroundColor = "";
        opt.title = "";
      }
      // break;
    }
  }
}

function resetearCheckboxes() {
  document.getElementById("checkboxTurno3").checked = false;
  document.getElementById("checkboxAnticipo").checked = false;
  document.getElementById("checkboxApoyo").checked = false;
  const horai = document.getElementById("horai"),
    horaf = document.getElementById("horaf");
  const turnoSel = document.getElementById("turnosel"),
    duracionTE = document.getElementById("duracionTE");
  const razon = document.getElementById("razon");
  horai.readOnly = true;
  horaf.readOnly = true;
  turnoSel.hidden = false;
  duracionTE.hidden = false;
  razon.readOnly = false;
  razon.value = "";
  document.getElementById("contenedorCheckboxes").style.display = "block";
  document.getElementById("contenedorCheckbox12hrs").style.display = "none";
}

function recalcularHorasAnticipo() {
  const turnoSel = document.getElementById("turnosel").value;
  const durStr = document.getElementById("duracionTE").value;
  const horai = document.getElementById("horai");
  const horaf = document.getElementById("horaf");
  if (!turnoSel || !durStr) {
    horai.value = "";
    horaf.value = "";
    return;
  }
  const def = CATALOGO_TURNOS_CAPTURA[turnoSel];
  if (!def) return;
  const durMin = horaAMinutos(durStr);
  const inicioTE = (def.inicio - durMin + 24 * 60) % (24 * 60);
  horai.value = minutosAHoraConSegundos(inicioTE);
  horaf.value = minutosAHoraConSegundos(def.inicio);
  document.getElementById("turnoSeleccionadoHidden").value = turnoSel;
  document.getElementById("horaFinalSinMargenHidden").value =
    minutosAHoraConSegundos(def.inicio);
  document.getElementById("horaFinalConMargenHidden").value =
    minutosAHoraConSegundos((def.inicio + 15) % (24 * 60));
  document.getElementById("horariosDeTurno").style.display = "block";
  document.getElementById("valorTurnoHora").textContent =
    minutosAHora(def.inicio) + " - " + minutosAHora(def.fin);
}

function validarCondicionesCheckbox12hrs() {
  const turno = document.getElementById("turnosel").value;
  const horasStr = document.getElementById("duracionTE").value;
  const cont12 = document.getElementById("contenedorCheckbox12hrs");
  const chk12 = document.getElementById("checkboxTurno3");
  if (!horasStr || !turno) {
    cont12.style.display = "none";
    chk12.checked = false;
    return;
  }
  const minutos = horaAMinutos(horasStr);
  // Solo mostrar casilla 12hrs para turno2/turno3 entre 3:30 y 4:30
  if (
    (turno === "turno3" || turno === "turno2") &&
    minutos >= 210 &&
    minutos <= 270
  ) {
    cont12.style.display = "block";
  } else {
    cont12.style.display = "none";
    chk12.checked = false;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// INSTANCIA Y EVENTOS
// ─────────────────────────────────────────────────────────────────────────────
Tiempoextra = new Tiempoextra();
Tiempoextra.inicio();
window.Tiempoextra = Tiempoextra;

document.getElementById("abrir").addEventListener("click", function (event) {
  event.preventDefault();
  Tiempoextra.abrirtiempoextra().then(() => {
    Tiempoextra.tblsubenc();
    Tiempoextra.tblenc();
  });
});

document.getElementById("guardar").addEventListener("click", function (event) {
  event.preventDefault();
  const btn = this;
  if (btn.disabled) return;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
  Tiempoextra.guardartiempoextra()
    .then(() => {
      Tiempoextra.tblsubenc();
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML =
        '<i class="fa-solid fa-floppy-disk"></i> Guardar y enviar';
    });
});

// ── LISTENER DE MOTIVOS ───────────────────────────────────────────────────────
document.getElementById("motivos").addEventListener("change", function () {
  // const motivoNum = parseInt(this.value);
  // const elHorai = document.getElementById("horai"), elHoraf = document.getElementById("horaf");
  // const elTurnosel = document.getElementById("turnosel"), elDuracionTE = document.getElementById("duracionTE");
  // const elCambiohrario = document.getElementById("cambiohrario"), elHoracomida = document.getElementById("horacomida");
  // const elHorariosDeTurno = document.getElementById("horariosDeTurno");
  // const cont12 = document.getElementById("contenedorCheckbox12hrs");
  // const razon = document.getElementById("razon");
  // const razonEspec = document.getElementById("contenedorCheckboxes");

  // elHorai.hidden = false; elHoraf.hidden = false; elTurnosel.hidden = false; elDuracionTE.hidden = false;
  // elCambiohrario.hidden = true; elHoracomida.hidden = true; elTurnosel.disabled = true;
  // elHorai.readOnly = true; elHoraf.readOnly = true;
  // elHorai.value = ""; elHoraf.value = ""; elTurnosel.value = ""; elDuracionTE.value = "";
  // elHorariosDeTurno.style.display = "none"; cont12.style.display = "none";
  // document.getElementById("checkboxTurno3").checked = false;

  // // if (motivoNum === 8) {
  // //     elHorai.hidden = true;
  // //     elHoraf.hidden = true;
  // //     elTurnosel.hidden = true;
  // //     elDuracionTE.hidden = true;
  // //     elCambiohrario.hidden = false;
  // //     razonEspec.hidden = true;

  // //     const selectDepto = document.getElementById("motivos");
  // //     const motivoRa = selectDepto.options[selectDepto.selectedIndex].text;
  // //     document.getElementById("razon").value = motivoRa;
  // //     razon.hidden = true;
  // // }
  // // else if (motivoNum === 9) {
  // //     elHorai.hidden = true;
  // //     elHoraf.hidden = true;
  // //     elTurnosel.hidden = true;
  // //     elDuracionTE.hidden = true;
  // //     elHoracomida.hidden = false;
  // //     razonEspec.hidden = true;

  // //     const selectDepto = document.getElementById("motivos");
  // //     const motivoRa = selectDepto.options[selectDepto.selectedIndex].text;
  // //     document.getElementById("razon").value = motivoRa;
  // //     razon.hidden = true;
  // // }
  // // else if (motivoNum === 10 || motivoNum === 12) {
  // //     elDuracionTE.hidden = true;
  // //     elDuracionTE.value = "";
  // //     elTurnosel.disabled = false;
  // //     razonEspec.hidden = true;

  // //     const selectDepto = document.getElementById("motivos");
  // //     const motivoRa = selectDepto.options[selectDepto.selectedIndex].text;
  // //     document.getElementById("razon").value = motivoRa;
  // //     razon.hidden = true;
  // // }
  // // else if (motivoNum != 8 && motivoNum != 9 && motivoNum != 10 && motivoNum != 12){
  // //     razonEspec.hidden = false;
  // // }
  // // else if(motivoNum != 7){
  // //     const selectDepto = document.getElementById("motivos");
  // //     const motivoRa = selectDepto.options[selectDepto.selectedIndex].text;
  // //     document.getElementById("razon").value = motivoRa;
  // //     razon.hidden = true;
  // // }
  // // else if (motivoNum === 7){
  // //     razon.hidden = false;
  // //     document.getElementById("razon").value = "";

  // // }

  // // ── Lógica de razon ──
  // if (motivoNum === 7) {
  //     razon.hidden = false;
  //     razon.value = "";
  // } else {
  //     const motivoRa = this.options[this.selectedIndex].text;
  //     razon.value = motivoRa;
  //     razon.hidden = true;
  // }

  // // ── Lógica específica ──
  // if (motivoNum === 8) {
  //     elHorai.hidden = true; elHoraf.hidden = true; elTurnosel.hidden = true; elDuracionTE.hidden = true;
  //     elCambiohrario.hidden = false;
  //     razonEspec.hidden = true;
  // }
  // else if (motivoNum === 9) {
  //     elHorai.hidden = true; elHoraf.hidden = true; elTurnosel.hidden = true; elDuracionTE.hidden = true;
  //     elHoracomida.hidden = false;
  //     razonEspec.hidden = true;
  // }
  // else if (motivoNum === 10 || motivoNum === 12) {
  //     elDuracionTE.hidden = true; elDuracionTE.value = ""; elTurnosel.disabled = false;
  //     razonEspec.hidden = true;
  // }
  // else {
  //     // todos los demás ≠ 7,8,9,10,12
  //     razonEspec.hidden = false;
  // }

  const motivoNum = parseInt(this.value);
  const elHorai = document.getElementById("horai"),
    elHoraf = document.getElementById("horaf");
  const elTurnosel = document.getElementById("turnosel"),
    elDuracionTE = document.getElementById("duracionTE");
  const elCambiohrario = document.getElementById("cambiohrario"),
    elHoracomida = document.getElementById("horacomida");
  const elHorariosDeTurno = document.getElementById("horariosDeTurno");
  const cont12 = document.getElementById("contenedorCheckbox12hrs");
  const razon = document.getElementById("razon");
  const razonEspec = document.getElementById("contenedorCheckboxes");
  const labelRaz = document.getElementById("labelrazon");

  // reset
  elHorai.hidden = false;
  elHoraf.hidden = false;
  elTurnosel.hidden = false;
  elDuracionTE.hidden = false;
  elCambiohrario.hidden = true;
  elHoracomida.hidden = true;
  elTurnosel.disabled = true;
  elHorai.readOnly = true;
  elHoraf.readOnly = true;
  elHorai.value = "";
  elHoraf.value = "";
  elTurnosel.value = "";
  elDuracionTE.value = "";
  elHorariosDeTurno.style.display = "none";
  cont12.style.display = "none";
  document.getElementById("checkboxTurno3").checked = false;

  // ── Lógica de razon ──
  if (motivoNum === 7) {
    razon.hidden = false;
    labelRaz.hidden = false;
    razon.value = "";
  } else {
    const motivoRa = this.options[this.selectedIndex].text;
    razon.value = motivoRa;
    razon.hidden = true;
    labelRaz.hidden = true;
  }

  // ── Lógica específica ──
  if (motivoNum === 8) {
    elHorai.hidden = true;
    elHoraf.hidden = true;
    elTurnosel.hidden = true;
    elDuracionTE.hidden = true;
    elCambiohrario.hidden = false;
    razonEspec.hidden = true;
  } else if (motivoNum === 9) {
    elHorai.hidden = true;
    elHoraf.hidden = true;
    elTurnosel.hidden = true;
    elDuracionTE.hidden = true;
    elHoracomida.hidden = false;
    razonEspec.hidden = true;
  } else if (motivoNum === 10 || motivoNum === 12) {
    elDuracionTE.hidden = true;
    elDuracionTE.value = "";
    elTurnosel.disabled = false;
    razonEspec.hidden = true;
  } else {
    // todos los demás ≠ 7,8,9,10,12
    razonEspec.hidden = false;
  }
});

// ── LISTENER DE TURNO ─────────────────────────────────────────────────────────
document.getElementById("turnosel").addEventListener("change", function () {
  const turnoSeleccionado = this.value;
  const motivoNum = parseInt(document.getElementById("motivos").value);
  const chkAnticipo = document.getElementById("checkboxAnticipo");

  const turnosDef = {
    turno1: { horaIdealEntrada: "07:00:00", horaIdealSalida: "15:00:00" },
    turno2: { horaIdealEntrada: "15:00:00", horaIdealSalida: "22:30:00" },
    turno3: { horaIdealEntrada: "22:30:00", horaIdealSalida: "07:00:00" },
    turno3_12hrs: { horaIdealEntrada: "19:00:00", horaIdealSalida: "22:30:00" },
    turno2_13hrs: { horaIdealEntrada: "10:30:00", horaIdealSalida: "15:00:00" },
    mixto1: { horaIdealEntrada: "07:30:00", horaIdealSalida: "17:00:00" },
    mixto2: { horaIdealEntrada: "08:30:00", horaIdealSalida: "18:30:00" },
    mixto3: { horaIdealEntrada: "07:00:00", horaIdealSalida: "16:30:00" },
    mixto4: { horaIdealEntrada: "07:00:00", horaIdealSalida: "17:00:00" },
  };

  if (!turnosDef[turnoSeleccionado]) {
    document.getElementById("horariosDeTurno").style.display = "none";
    return;
  }
  const datosTurno = turnosDef[turnoSeleccionado];
  document.getElementById("horariosDeTurno").style.display = "block";
  document.getElementById("valorTurnoHora").textContent =
    datosTurno.horaIdealEntrada + " - " + datosTurno.horaIdealSalida;
  const finTurnoMin = horaAMinutos(datosTurno.horaIdealSalida);

  if (chkAnticipo.checked) {
    recalcularHorasAnticipo();
    return;
  }

  if (motivoNum === 10 || motivoNum === 12) {
    document.getElementById("horai").value = datosTurno.horaIdealEntrada;
    document.getElementById("horaf").value = datosTurno.horaIdealSalida;
    document.getElementById("turnoSeleccionadoHidden").value =
      turnoSeleccionado;
    document.getElementById("horaFinalSinMargenHidden").value =
      datosTurno.horaIdealSalida;
    document.getElementById("horaFinalConMargenHidden").value =
      minutosAHoraConSegundos(finTurnoMin + 15);
    return;
  }

  const TiempoEStr = document.getElementById("duracionTE").value;
  const duracionTiempoE = TiempoEStr ? horaAMinutos(TiempoEStr) : 0;
  document.getElementById("horai").value = datosTurno.horaIdealSalida;
  const horaFinalsinMargen = minutosAHoraConSegundos(
    finTurnoMin + duracionTiempoE,
  );
  document.getElementById("horaf").value = horaFinalsinMargen;
  document.getElementById("turnoSeleccionadoHidden").value = turnoSeleccionado;
  document.getElementById("horaFinalSinMargenHidden").value =
    horaFinalsinMargen;
  document.getElementById("horaFinalConMargenHidden").value =
    minutosAHoraConSegundos(finTurnoMin + duracionTiempoE + 15);
  validarCondicionesCheckbox12hrs();
});

// --- LISTENER DE TURNO PARA MODAL
document
  .getElementById("turno_presentacion")
  .addEventListener("change", function () {
    const turnoSeleccionado = this.value;
    const horario_desde = document.getElementById("horario_desde");
    const horario_hasta = document.getElementById("horario_hasta");

    switch (turnoSeleccionado) {
      case "turno1":
        horario_desde.value = "07:00:00";
        horario_hasta.value = "15:00:00";
        break;
      case "turno2":
        horario_desde.value = "15:00:00";
        horario_hasta.value = "22:30:00";
        break;
      case "turno3":
        horario_desde.value = "22:30:00";
        horario_hasta.value = "07:00:00";
        break;
      case "mixto1":
        horario_desde.value = "07:00:00";
        horario_hasta.value = "17:00:00";
        break;
      case "mixto2":
        horario_desde.value = "08:30:00";
        horario_hasta.value = "18:30:00";
        break;
      case "mixto3":
        horario_desde.value = "07:00:00";
        horario_hasta.value = "16:30:00";
        break;
      case "mixto4":
        horario_desde.value = "07:00:00";
        horario_hasta.value = "17:00:00";
        break;
      case "turno1_12hrs":
        horario_desde.value = "07:00:00";
        horario_hasta.value = "19:00:00";
        break;
      case "turno2_12hrs":
        horario_desde.value = "10:30:00";
        horario_hasta.value = "22:30:00";
        break;
      case "turno3_12hrs":
        horario_desde.value = "19:00:00";
        horario_hasta.value = "07:00:00";
        break;
      default:
        horario_desde.value = "";
        horario_hasta.value = "";
        break;
    }
  });

// ── DOMContentLoaded ─────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
  const duracionTE = document.getElementById("duracionTE");
  const turnoSel = document.getElementById("turnosel");
  const checkboxTurno3 = document.getElementById("checkboxTurno3");
  const checkboxAnticipo = document.getElementById("checkboxAnticipo");
  const checkboxApoyo = document.getElementById("checkboxApoyo"); // Reingreso
  const horai = document.getElementById("horai"),
    horaf = document.getElementById("horaf");
  const motivo = document.getElementById("motivos"),
    razon = document.getElementById("razon");
  const turnoHidden = document.getElementById("turnoSeleccionadoHidden");
  const horaFinalSinMargenHidden = document.getElementById(
    "horaFinalSinMargenHidden",
  );
  const horaFinalConMargenHidden = document.getElementById(
    "horaFinalConMargenHidden",
  );
  let estadoOriginal = {
    horai: null,
    horaf: null,
    horaFinalSinMargen: null,
    horaFinalConMargen: null,
  };

  // ── duracionTE: habilitar turno, recalcular si anticipo, actualizar fin ──
  duracionTE.addEventListener("input", () => {
    const motivoNum = parseInt(motivo.value);
    if ([9, 8, 10, 12].includes(motivoNum)) return;

    if (duracionTE.value.trim() === "") {
      turnoSel.disabled = true;
      turnoSel.value = "";
      horai.value = "";
      horaf.value = "";
    } else {
      turnoSel.disabled = false;
    }

    if (checkboxAnticipo.checked && turnoSel.value) {
      recalcularHorasAnticipo();
    } else if (
      !checkboxAnticipo.checked &&
      !checkboxApoyo.checked &&
      turnoSel.value
    ) {
      if (parseInt(motivo.value) !== 10 && parseInt(motivo.value) !== 12) {
        const turnosDef2 = {
          turno1: "15:00:00",
          turno2: "22:30:00",
          turno3: "07:00:00",
          turno3_12hrs: "22:30:00",
          turno2_13hrs: "15:00:00",
          mixto1: "17:00:00",
          mixto2: "18:30:00",
          mixto3: "16:30:00",
          mixto4: "17:00:00",
        };
        if (turnosDef2[turnoSel.value] && duracionTE.value) {
          const finTurnoMin = horaAMinutos(turnosDef2[turnoSel.value]);
          const dur = horaAMinutos(duracionTE.value);
          horaf.value = minutosAHoraConSegundos(finTurnoMin + dur);
          horaFinalSinMargenHidden.value = horaf.value;
          horaFinalConMargenHidden.value = minutosAHoraConSegundos(
            finTurnoMin + dur + 15,
          );
        }
      }
    }
    validarCondicionesCheckbox12hrs();
  });

  duracionTE.addEventListener("blur", function () {
    const regex = /^([01]\d|2[0-3]):([0-5]\d)$/;
    if (this.value && !regex.test(this.value)) {
      Swal.fire("UPS!!!", "Formato válido: hh:mm (00:00 a 23:59)", "info");
      this.value = "";
      turnoSel.value = "";
      horai.value = "";
      horaf.value = "";
    }
  });

  // ── CHECKBOX 12HRS ────────────────────────────────────────────────────────
  checkboxTurno3.addEventListener("change", function () {
    if (this.checked) {
      checkboxAnticipo.checked = false;
      checkboxApoyo.checked = false;
      estadoOriginal = {
        horai: horai.value,
        horaf: horaf.value,
        horaFinalSinMargen: horaFinalSinMargenHidden.value,
        horaFinalConMargen: horaFinalConMargenHidden.value,
      };
      if (turnoSel.value === "turno3") {
        horai.value = "10:30:00";
        horaf.value = "22:30:00";
        turnoHidden.value = "turno3";
        horaFinalSinMargenHidden.value = "22:30:00";
        horaFinalConMargenHidden.value = "22:45:00";
      } else if (turnoSel.value === "turno2") {
        horai.value = "10:30:00";
        horaf.value = "15:00:00";
        turnoHidden.value = "turno2";
        horaFinalSinMargenHidden.value = "15:00:00";
        horaFinalConMargenHidden.value = "15:15:00";
      }
    } else {
      if (estadoOriginal.horai) horai.value = estadoOriginal.horai;
      if (estadoOriginal.horaf) horaf.value = estadoOriginal.horaf;
      if (estadoOriginal.horaFinalSinMargen)
        horaFinalSinMargenHidden.value = estadoOriginal.horaFinalSinMargen;
      if (estadoOriginal.horaFinalConMargen)
        horaFinalConMargenHidden.value = estadoOriginal.horaFinalConMargen;
    }
  });

  // ── CHECKBOX ANTICIPO ─────────────────────────────────────────────────────
  checkboxAnticipo.addEventListener("change", function () {
    if (this.checked) {
      checkboxTurno3.checked = false;
      checkboxApoyo.checked = false;
      turnoSel.disabled = false;
      duracionTE.hidden = false;
      turnoSel.hidden = false;
      horai.readOnly = true;
      horaf.readOnly = true;
      razon.readOnly = true;
      razon.value = "Anticipo";
      document.getElementById("contenedorCheckbox12hrs").style.display = "none";
      if (turnoSel.value && duracionTE.value) recalcularHorasAnticipo();
      else {
        horai.value = "";
        horaf.value = "";
      }
    } else {
      horai.readOnly = true;
      horaf.readOnly = true;
      razon.readOnly = false;
      razon.value = "";
      horai.value = "";
      horaf.value = "";
      turnoSel.value = "";
      duracionTE.value = "";
      turnoSel.disabled = true;
      document.getElementById("horariosDeTurno").style.display = "none";
    }
  });

  // ── CHECKBOX REINGRESO (antes Apoyo) ──────────────────────────────────────
  checkboxApoyo.addEventListener("change", function () {
    if (this.checked) {
      checkboxTurno3.checked = false;
      checkboxAnticipo.checked = false;
      duracionTE.hidden = true;
      turnoSel.hidden = true;
      horai.hidden = false;
      horaf.hidden = true;
      horai.readOnly = false;
      razon.readOnly = true;
      razon.value = "Reingreso";
      duracionTE.value = "01:00";
      turnoSel.value = "turno1";
      document.getElementById("turnoSeleccionadoHidden").value = "turno1";
      horai.value = "";
      horaf.value = "00:00:00";
      document.getElementById("contenedorCheckbox12hrs").style.display = "none";
      document.getElementById("horariosDeTurno").style.display = "none";
    } else {
      duracionTE.hidden = false;
      turnoSel.hidden = false;
      horai.hidden = false;
      horaf.hidden = false;
      horai.readOnly = true;
      horaf.readOnly = true;
      razon.readOnly = false;
      razon.value = "";
      horai.value = "";
      horaf.value = "";
      duracionTE.value = "";
      turnoSel.value = "";
      turnoSel.disabled = true;
    }
  });

  turnoSel.addEventListener("change", function () {
    if (checkboxAnticipo.checked && duracionTE.value) recalcularHorasAnticipo();
    validarCondicionesCheckbox12hrs();
  });

  window.validarCondicionesCheckbox = validarCondicionesCheckbox12hrs;

  const driver = window.driver.js.driver;
  // -------------------------------
  // Pasos - Vista principal
  // -------------------------------
  const steps = [
    {
      element: ".tittlecont",
      popover: {
        title: "Tiempos Extra",
        description:
          "Aquí comienza el proceso para registrar solicitudes de tiempo extra.",
        side: "bottom",
      },
    },
    {
      element: ".alert.alert-info",
      popover: {
        title: "Instrucciones iniciales",
        description:
          "Recuerda que los tiempos extra se consideran a partir de 55 minutos después de tus horas reglamentarias.",
        side: "bottom",
      },
    },
    {
      element: "#folio",
      popover: {
        title: "Folio",
        description:
          "Este campo muestra el folio generado para tu solicitud de tiempo extra.",
        side: "top",
      },
    },
    {
      element: "#fechaenc",
      popover: {
        title: "Fecha",
        description:
          "Selecciona la fecha de inicio de la semana para tu solicitud de tiempo extra.",
        side: "top",
      },
    },
    {
      element: "#departamentoenc",
      popover: {
        title: "Departamento",
        description: "Indica el departamento al que pertenece la solicitud.",
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
      element: "#creapdf",
      popover: {
        title: "Previsualizar PDF",
        description:
          "Genera una vista previa en PDF de tu solicitud de tiempo extra (RECUERDA SELECCIONAR UN FOLIO O CREARLO ANTES).",
        side: "top",
      },
    },
    {
      element: ".botonEmpezarDeNuevo",
      popover: {
        title: "Reiniciar proceso",
        description:
          "Presiona este botón para limpiar todos los campos e iniciar un nuevo registro.",
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
      element: "#motivos",
      popover: {
        title: "Motivo del tiempo extra",
        description:
          "Selecciona el motivo por el cual se solicita el tiempo extra.",
        side: "top",
      },
    },
    {
      element: "#horai",
      popover: {
        title: "Inicio de tiempo extra",
        description:
          "Aquí se muestra la hora de inicio del tiempo extra (ESTE CAMPO SE LLENARA AUTOMATICAMENTE UNA VEZ SELECCIONES UN TURNO).",
        side: "top",
      },
    },
    {
      element: "#horaf",
      popover: {
        title: "Fin de tiempo extra",
        description:
          "Aquí se muestra la hora de finalización del tiempo extra (ESTE CAMPO SE LLENARA AUTOMATICAMENTE SUMANDO LAS HRS SOLICITADAS MAS LA HORA DE INICIO DE T. EXTRA).",
        side: "top",
      },
    },
    {
      element: "#maquinas",
      popover: {
        title: "Máquina",
        description:
          "Selecciona la máquina en la que se realizará el tiempo extra (SE MOSTRARAN UNICAMENTE LAS RELACIONADAS A TU DEPARTAMENTO).",
        side: "top",
      },
    },
    {
      element: "#fechainput",
      popover: {
        title: "Fecha de solicitud",
        description:
          "Selecciona el dia en el que el empleado hará su tiempo extra.",
        side: "top",
      },
    },
    {
      element: "#razon",
      popover: {
        title: "Razón del tiempo extra",
        description:
          "Especifica la razón por la cual se solicita el tiempo extra.",
        side: "top",
      },
    },
    {
      element: "#duracionTE",
      popover: {
        title: "Duración",
        description:
          "En este campo debes ingresar la cantidad de tiempo extra a hacer. ES IMPORTANTE QUE LO ESCRIBAS EN ESTE FORMATO hh:mm (HORAS:MINUTOS).",
        side: "top",
      },
    },
    {
      element: "#turnosel",
      popover: {
        title: "Turno",
        description:
          "En este campo debes seleccionar el turno que tendras el día que se solicita el tiempo extra.",
        side: "top",
      },
    },
    {
      element: "#contenedorCheckboxes",
      popover: {
        title: "Casos especiales",
        description:
          "Debes de seleccionar el caso que te corresponda segun tus necesidades, se contemplan 12 horas, Anticipos y Reingresos en turno seguido",
        side: "top",
      },
    },
    {
      element: "#guardar",
      popover: {
        title: "Guardar y enviar",
        description:
          "Haz clic aquí para guardar y enviar la solicitud de tiempo extra para su validación y autorización. (UNA VEZ QUE ENVIES TU SOLICITUD TODOS LOS CAMPOS SE LIMPIARAN EN AUTOMATICO)",
        side: "top",
      },
    },
    {
      element: ".LimpiarCampos",
      popover: {
        title: "Limpiar campos",
        description:
          "Si te equivocas o quieres iniciar un nuevo registro presiona este boton para limpiar todos los campos",
        side: "top",
      },
    },
    {
      element: "#consultar",
      popover: {
        title: "Validar solicitudes",
        description:
          "UNA VEZ QUE REGISTRES TUS SOLICITUDES DEBES DE VALIDAR CADA REGISTRO ANTES DE ENVIAR A SU AUTORIZACIÓN, LO HARAS UNA VEZ QUE LA FECHA Y HORA DE FINALIZACIÓN DEL TIEMPO EXTRA HAYAN FINALIZADO, DEBES HACERLO O DE LO CONTRARIO EL GERENTE CORRESPONDIENTE NO PODRA APROBAR O RECHAZAR LAS SOLICITUDES",
        side: "top",
        popoverClass: "popover-importante",
      },
    },
    {
      element: ".toggle-modo-wrap",
      popover: {
        title: "Captura múltiple (hasta 7 días)",
        description:
          "Activa este interruptor para cambiar al modo de captura múltiple. Te permite registrar tiempos extra de varios días de la semana a la vez (hasta 7),  cada uno con su propia configuración,  en lugar de capturar uno por uno. Necesitas tener un folio creado o seleccionado primero.",
        side: "top",
        popoverClass: "popover-importante",
      },
    },
    {
      element: ".alert.alert-success",
      popover: {
        title: "Tabla de solicitudes",
        description:
          "Aquí encontrarás el estado de las últimas solicitudes según el folio seleccionado.",
        side: "bottom",
      },
    },
    {
      element: "#tbltiempoextra",
      popover: {
        title: "Solicitudes creadas",
        description:
          "Consulta el detalle de las solicitudes registradas en la tabla. A LADO DE CADA REGISTRO ENCONTRARAS UN BOTON PARA CREAR CAMBIOS TEMPORALES DE TURNO.",
        side: "top",
        popoverClass: "popover-importante",
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

  // -------------------------------
  // Pasos - Modal cambio de turno
  // -------------------------------
  const stepsModal = [
    {
      element: "#modalOverlayLabel",
      popover: {
        title: "Cambio Temporal de Turno",
        description:
          "Este formulario permite registrar un cambio temporal de turno.",
        side: "bottom",
      },
    },
    {
      element: "#fecha_emision",
      popover: {
        title: "Fecha de emisión",
        description: "Selecciona la fecha en que se emite el cambio.",
        side: "top",
      },
    },
    {
      element: "#Depto_m",
      popover: {
        title: "Departamento",
        description: "Indica el departamento al que pertenece el empleado.",
        side: "top",
      },
    },
    {
      element: "#nombre_receptor",
      popover: {
        title: "Receptor",
        description:
          "Nombre completo del empleado que recibirá el cambio de turno.",
        side: "top",
      },
    },
    {
      element: "#de_area",
      popover: {
        title: "Supervisor",
        description:
          "Aquí se muestra el nombre del supervisor que autoriza el cambio.",
        side: "top",
      },
    },
    {
      element: "#horario_texto",
      popover: {
        title: "Horario",
        description:
          "Aquí debes seleccionar el rol en el que te encuentras actualmente.",
        side: "top",
      },
    },
    // {
    //     element: "#rol",
    //     popover: {
    //         title: "Rol",
    //         description: "Indica el rol o función del empleado en el nuevo turno.",
    //         side: "top"
    //     }
    // },
    {
      element: "#hasta_tripulacion",
      popover: {
        title: "Conductor",
        description:
          "Ingresa bajo el nombre de quien esta a cargo la tripulación.",
        side: "top",
      },
    },
    {
      element: "#fecha_inicio",
      popover: {
        title: "Fecha de inicio",
        description: "Selecciona el día en que comienza el cambio de turno.",
        side: "top",
      },
    },
    {
      element: "#hasta_el",
      popover: {
        title: "Fecha de término",
        description: "Selecciona el día en que finaliza el cambio de turno.",
        side: "top",
      },
    },
    {
      element: "#turno_presentacion",
      popover: {
        title: "Turno de presentación",
        description: "Indica el turno en el que debe presentarse el empleado.",
        side: "top",
      },
    },
    // {
    //     element: "#hora_presentacion",
    //     popover: {
    //         title: "Hora de presentación",
    //         description: "Especifica la hora en que debe presentarse.",
    //         side: "top"
    //     }
    // },
    {
      element: "#horario_desde",
      popover: {
        title: "Horario desde",
        description: "Hora de inicio del nuevo horario.",
        side: "top",
      },
    },
    {
      element: "#horario_hasta",
      popover: {
        title: "Horario hasta",
        description: "Hora de fin del nuevo horario.",
        side: "top",
      },
    },
    {
      element: "#descansos",
      popover: {
        title: "Descansos",
        description: "Especifica los descansos aplicables al nuevo turno.",
        side: "top",
      },
    },
    {
      element: "#dias_adicionales",
      popover: {
        title: "Días adicionales",
        description: "Indica si hay días adicionales que aplicar.",
        side: "top",
      },
    },
    {
      element: "#horario_adicional",
      popover: {
        title: "Horario adicional",
        description: "Especifica horarios adicionales si aplica.",
        side: "top",
      },
    },
    {
      element: "#btnGuardar",
      popover: {
        title: "Guardar",
        description: "Haz clic aquí para guardar el cambio temporal de turno.",
        side: "top",
        popoverClass: "popover-importante",
      },
    },
    {
      element: "#btnAyudaModal",
      popover: {
        title: "Botón de ayuda",
        description:
          "Si necesitas repasar cómo llenar este formulario, presiona este botón para repetir el tutorial.",
        side: "left",
      },
    },
  ];

  let driverObj = null;
  function launchDriver(pasos) {
    if (driverObj) driverObj.destroy();
    driverObj = driver({
      showProgress: true,
      allowClose: false,
      disableInteraction: true,
      progressText: "Paso {{current}} de {{total}}",
      doneBtnText: "Finalizar",
      nextBtnText: "Siguiente",
      prevBtnText: "Atrás",
      steps: pasos,
    });
    driverObj.drive();
  }

  const tutorialKey = "tutorial_tiempoextra";
  if (!localStorage.getItem(tutorialKey)) {
    launchDriver(steps);
    localStorage.setItem(tutorialKey, "true");
  }
  document
    .getElementById("btnAyuda")
    ?.addEventListener("click", () => launchDriver(steps));

  const tutorialModalKey = "tutorial_modalCambioTurno";
  const modalOverlay = document.getElementById("modalOverlay");
  if (modalOverlay) {
    modalOverlay.addEventListener("shown.bs.modal", () => {
      if (!localStorage.getItem(tutorialModalKey)) {
        launchDriver(stepsModal);
        localStorage.setItem(tutorialModalKey, "true");
      }
    });
  }
  document
    .getElementById("btnAyudaModal")
    ?.addEventListener("click", () => launchDriver(stepsModal));
});

// ── OTROS EVENTOS ─────────────────────────────────────────────────────────────
document.getElementById("noemp").addEventListener("keyup", function () {
  Tiempoextra.getinfoemp(this.value);
});

document.getElementById("departamento").addEventListener("change", function () {
  const nombreDepto = this.value.trim();
  if (nombreDepto === "") {
    document.getElementById("maquinas").innerHTML = "";
    return;
  }
  const selectEnc = document.getElementById("departamentoenc");
  let idDepto = null;
  for (let i = 0; i < selectEnc.options.length; i++) {
    if (selectEnc.options[i].text.trim() === nombreDepto) {
      idDepto = selectEnc.options[i].value;
      break;
    }
  }
  if (!idDepto) {
    document.getElementById("maquinas").innerHTML = "";
    return;
  }
  const Tools = new Toolsjs();
  Tools.llnarslc(
    "CatalogoPersonal",
    "GetSlcMaquinasxdep&departamento=" + idDepto,
    "maquinas",
    0,
  );
});

document.getElementById("fechainput").addEventListener("change", function () {
  const motivoNum = parseInt(document.getElementById("motivos").value);
  if ([5, 8].includes(motivoNum)) return;
  Tiempoextra.getinfohoraentradaysalida(
    document.getElementById("noemp").value,
    this.value,
  );
});

document.getElementById("creapdf").addEventListener("click", function (event) {
  event.preventDefault();
  let folio = document.getElementById("folio").value;
  if (folio === "") {
    Swal.fire("UPS!!!", "No hay un folio creado", "info");
    return false;
  }
  window.open("./pdf/reporte.php?folio=" + btoa(folio));
});

document
  .getElementById("btnverfolio")
  .addEventListener("click", function (event) {
    event.preventDefault();
    let valortxt = document.getElementById("txtview").textContent;
    document.getElementById("txtview").innerHTML =
      valortxt === "Cerrar" ? "Ver Folio" : "Cerrar";
  });

document.getElementById("consultar").addEventListener("click", function () {
  window.open("../Tiempoextra/Autorizatp.php");
});

document
  .getElementById("btnGuardar")
  .addEventListener("click", async function () {
    let folio = document.getElementById("folio").value.trim();
    let hora_presentacion = document
      .getElementById("hora_presentacion")
      .value.trim();
    let horario_desde = document.getElementById("horario_desde").value.trim();
    let horario_hasta = document.getElementById("horario_hasta").value.trim();
    let hasta_tripulacion = document
      .getElementById("hasta_tripulacion")
      .value.trim();
    let fechaInicio = document.getElementById("fecha_inicio").value.trim();
    let fechaFin = document.getElementById("hasta_el").value.trim();

    if (
      folio === "" ||
      hora_presentacion === "" ||
      horario_desde === "" ||
      horario_hasta === "" ||
      hasta_tripulacion === ""
    ) {
      Swal.fire("Error", "Debes de ingresar todos los campos", "error");
      return;
    }
    // if (folioTE === "") {
    //   Swal.fire(
    //     "Error",
    //     "El folio no está asociado a algún tiempo extra",
    //     "error",
    //   );
    //   return;
    // }
    if (fechaInicio === "" || fechaFin === "") {
      Swal.fire(
        "Error",
        "Debes seleccionar un rango de fechas valido",
        "error",
      );
      return;
    }

    const form = document.getElementById("formCambio");
    const data = new FormData(form);
    const response = await fetch(
      "php/index.php?guardarCambioTurno&folio=" + folio,
      { method: "POST", body: data },
    );
    const result = await response.json();

    if (result.success) {
      Tiempoextra.tblsubenc();
      Swal.fire(
        "Guardado",
        "El cambio temporal de turno fue registrado",
        "success",
      );
      bootstrap.Modal.getInstance(
        document.getElementById("modalOverlay"),
      ).hide();
      form.reset();
    } else {
      Swal.fire("Error", "No se pudo guardar", "error");
    }
  });

document.querySelector("#formtiempoextra").addEventListener("submit", (e) => {
  e.preventDefault();
});
document.querySelector("#formCambio").addEventListener("submit", (e) => {
  e.preventDefault();
});

// validacion de que al crear un folio el inicio de semana sea lunea
document.getElementById("fechaenc").addEventListener("change", function () {
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

function verPDF(id) {
  window.open("./pdf/cambio_turno.php?id=" + btoa(id), "_blank");
}
window.verPDF = verPDF;

function abrirModal() {
  const hoy = new Date();
  document.getElementById("fecha_emision").value =
    hoy.getFullYear() +
    "-" +
    String(hoy.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(hoy.getDate()).padStart(2, "0");
  new bootstrap.Modal(document.getElementById("modalOverlay")).show();
}

window.editEnc = (id) => Tiempoextra.editenc(id);
window.deleteSub = (id) => Tiempoextra.deletesub(id);
window.cambioTempTurno = (noemp, estadoTurno, nombre, depto, id) =>
  Tiempoextra.cambioTempTurno(noemp, estadoTurno, nombre, depto, id);
window.enviarEnc = (id) => Tiempoextra.enviar(id);
window.pdfFin = (id) => Tiempoextra.pdffin(id);
