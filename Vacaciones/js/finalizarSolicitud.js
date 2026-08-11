const btnVerInformacion = document.getElementById("formVac");
if (btnVerInformacion) {
  document
    .getElementById("formVac")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      // Obtener la accion si fue PDF o ENVIAR
      const accion = e.submitter.value;
      const data = new FormData(e.target);

      if (accion === "pdf") {
        const resp = await fetch("php/index.php?guardarSolicitudVacaciones", {
          method: "POST",
          body: data,
        });

        const result = await resp.json();
        if (result.error) {
          Swal.fire({
            icon: "error",
            title: "No se pudo registrar la solicitud",
            text:
              typeof result.error === "string"
                ? result.error
                : JSON.stringify(result.error),
            confirmButtonText: "Entendido",
          });
          return;
        }

        if (result.success) {
          Swal.fire(
            "Guardado",
            "La solicitud se registró correctamente, ¡En 10 segundos se te redigirá a la consulta inicial!",
            "success",
          ).then(() => {
            e.target.submit();
          });
        } else {
          Swal.fire("Error", "No se pudo guardar en BD", "error");
        }
      } else if (accion === "enviar") {
        for (let [key, value] of data.entries()) {
          //console.log(key, value);
        }

        const resp = await fetch("php/index.php?enviarSolicitudVacaciones", {
          method: "POST",
          body: data,
        });

        const result = await resp.json();

        if (result.success) {
          Swal.fire(
            "Enviado",
            "La solicitud fue enviada correctamente",
            "success",
          );
        } else {
          Swal.fire("Error", "No se pudo enviar la solicitud", "error");
        }
      }
    });
}

// Funcion de carga de datos en hoja de datos
function cargarDeptoPuesto(ibm) {
  if (ibm !== "") {
    fetch("php/index.php?getDeptoPuesto", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "ibm=" + encodeURIComponent(ibm),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.error) {
          //console.warn(data.error);
        } else {
          document.getElementById("puesto").value = data.puesto;
          document.getElementById("departamento").value = data.depto;
        }
      })
      .catch((err) => console.error(err));
  }
}

// Carga de funcion al cargar el DOM cuando se recuperan el ibm
document.addEventListener("DOMContentLoaded", function () {
  const tarjetaInput = document.getElementById("tarjeta");
  const ibmInicial = tarjetaInput.value.trim();
  cargarDeptoPuesto(ibmInicial);

  tarjetaInput.addEventListener("change", function () {
    cargarDeptoPuesto(this.value.trim());
  });

  // -------------------------------------------------------------------------------------------
  const driver = window.driver.js.driver;

  const steps = [
    {
      element: ".tittlecont",
      popover: {
        title: "Revisión de datos",
        description:
          "Aquí comienza el proceso de revisión de tu solicitud de vacaciones.",
        side: "bottom",
      },
    },
    {
      element: ".alert.alert-info",
      popover: {
        title: "Instrucciones",
        description:
          "Verifica tu información y al final guarda el PDF para enviarlo a autorización.",
        side: "bottom",
      },
    },
    {
      element: ".campos",
      popover: {
        title: "Campos principales",
        description:
          "Completa o revisa tus datos: nombre, puesto, fechas, días solicitados y antigüedad.",
        side: "right",
      },
    },
    {
      element: ".glosarioColores",
      popover: {
        title: "Glosario de colores",
        description:
          "Aquí encontraras una explicacion de lo que significa cada color segun el tipo que escojas en la tabla de abajo.",
        side: "top",
      },
    },
    {
      element: ".calendario",
      popover: {
        title: "Calendario",
        description:
          "Selecciona los días de vacaciones, descanso, festivo o reposición según corresponda.",
        side: "top",
      },
    },
    {
      element: ".diaSeleccionable",
      popover: {
        title: "Selección de dia",
        description:
          "Presiona aqui para desplegar las opciones y cambies si es necesario el tipo ya sea V | D | F | R (Usa el glosario de colores para saber a que refiere cada opción).",
        side: "top",
      },
    },
    {
      element: ".observacionesSeccion",
      popover: {
        title: "Observaciones",
        description:
          "Agrega comentarios adicionales sobre tu solicitud (máx. 150 caracteres).",
        side: "top",
      },
    },
    {
      element: ".fechasReposicionfestivo",
      popover: {
        title: "Reposición/Festivo",
        description:
          "Anota las fechas de días por reposición o festivo en formato d/m/y.",
        side: "top",
      },
    },
    {
      element: ".saldo-row",
      popover: {
        title: "Saldo",
        description:
          "Aquí se muestran el saldo al periodo y los días hábiles calculados.",
        side: "top",
      },
    },
    {
      element: ".botonRegresar",
      popover: {
        title: "Regresar a la selección",
        description: "Usa este botón para regresar a la pantalla anterior.",
        side: "top",
      },
    },
    {
      element: ".botonGuardar",
      popover: {
        title: "Guardar informacion",
        description:
          "Usa este botón para guardar y generar el PDF (Esto enviara tu solicitud a tu jefe inmediato).",
        side: "top",
      },
    },
    {
      element: "#btnAyuda",
      popover: {
        title: "Volver a ver el tutorial",
        description:
          "Si necesitas repasar cómo llenar esta pantalla, presiona este botón para repetir el tutorial.",
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
  const tutorialKey = "tutorial_finalizar";
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

document.querySelector(".botonGuardar").addEventListener("click", function () {
  // abrir el PDF en nueva pestaña (lo hace el form con target="_blank")
  // y redirigir la ventana actual
  setTimeout(() => {
    window.location.href = "./Consulta.php";
  }, 10000); // medio segundo de espera
});

document.addEventListener("DOMContentLoaded", function () {
  const inputVacaciones = document.querySelector(
    'input[name="dias_solicitados"]',
  );
  const inputReposicionFestivo = document.querySelector(
    'input[name="dias_reposicion"]',
  );
  const inputDescanso = document.querySelector('input[name="dias_descanso"]');
  const inputTotal = document.querySelector('input[name="total_dias"]');
  const solicitud_por = document.querySelector('input[name="solicitud_por"]');
  const inputAntiguedad = document.getElementById("dias_antiguedad");
  const badgeDiasV = document.getElementById("badgeDiasV");

  const colores = {
    V: "rgb(198,224,180)",
    D: "rgb(255,255,153)",
    F: "rgb(255,153,153)",
    R: "rgb(180,198,231)",
  };

  const NOMBRE_MESES = {
    1: "Enero",
    2: "Febrero",
    3: "Marzo",
    4: "Abril",
    5: "Mayo",
    6: "Junio",
    7: "Julio",
    8: "Agosto",
    9: "Septiembre",
    10: "Octubre",
    11: "Noviembre",
    12: "Diciembre",
  };
  const LETRAS = ["L", "M", "M", "J", "V", "S", "D"]; // ISO: 0=Lunes

  // ---------- Helpers de límite ----------
  function getLimiteAntiguedad() {
    const v = parseInt(inputAntiguedad?.value, 10);
    return isNaN(v) ? Infinity : v;
  }
  function contarV() {
    let v = 0;
    document.querySelectorAll(".calendario select").forEach((s) => {
      if (s.value === "V") v++;
    });
    return v;
  }

  // ---------- Recalcular (tu lógica original + badge) ----------
  function recalcularContadores() {
    let vacaciones = 0,
      reposicionFestivo = 0,
      descanso = 0;
    let ultimaFecha = null,
      primeraFecha = null;

    document.querySelectorAll(".calendario select").forEach((sel) => {
      const valor = sel.value;

      if (valor === "V") vacaciones++;
      if (valor === "F" || valor === "R") reposicionFestivo++;
      if (valor === "D") descanso++;

      const td = sel.closest("td");
      if (td)
        td.style.backgroundColor =
          valor && colores[valor] ? colores[valor] : "";

      if (valor) {
        const fechaAttr = sel.getAttribute("data-fecha");
        if (fechaAttr) {
          const fecha = new Date(fechaAttr);
          if (!ultimaFecha || fecha > ultimaFecha) ultimaFecha = fecha;
          if (!primeraFecha || fecha < primeraFecha) primeraFecha = fecha;
        }
      }
    });

    inputVacaciones.value = vacaciones;
    inputReposicionFestivo.value = reposicionFestivo;
    inputDescanso.value = descanso;
    inputTotal.value = vacaciones + reposicionFestivo + descanso;
    solicitud_por.value = vacaciones;

    if (ultimaFecha) {
      const inputHasta = document.getElementById("vacaciones_hasta");
      if (inputHasta)
        inputHasta.value = ultimaFecha.toISOString().split("T")[0];
    }
    if (primeraFecha) {
      const inputDesde = document.getElementById("periodo_de");
      if (inputDesde)
        inputDesde.value = primeraFecha.toISOString().split("T")[0];
    }

    if (badgeDiasV) {
      badgeDiasV.classList.remove(
        "bg-secondary",
        "bg-success",
        "bg-danger",
        "bg-warning",
        "bg-info",
        "text-dark",
      );

      if (!aplicaLimite()) {
        // Adelanto: sin tope por antigüedad
        badgeDiasV.textContent = `Días V: ${vacaciones} · adelanto (sin límite por antigüedad)`;
        badgeDiasV.classList.add("bg-info", "text-dark");
      } else {
        const limite = getLimiteAntiguedad();

        if (!isFinite(limite)) {
          badgeDiasV.textContent = `Días V: ${vacaciones}`;
          badgeDiasV.classList.add("bg-secondary");
        } else {
          const restantes = limite - vacaciones;
          let texto = `Días V: ${vacaciones} / ${limite}`;

          if (restantes > 0) {
            texto += ` · te quedan ${restantes}`;
            badgeDiasV.classList.add("bg-success");
          } else if (restantes === 0) {
            texto += ` · sin días restantes`;
            badgeDiasV.classList.add("bg-warning", "text-dark");
          } else {
            texto += ` · te excediste por ${Math.abs(restantes)}`;
            badgeDiasV.classList.add("bg-danger");
          }

          badgeDiasV.textContent = texto;
        }
      }
    }
  }

  // ---------- Cambio de un día (con tope de antigüedad) ----------
  function getTipoSolicitud() {
    const el = document.getElementById("tipo_solicitud");
    return (el?.value || "").trim().toLowerCase();
  }
  // El tope por antigüedad SOLO aplica a "Normal".
  // "Adelanto" admite cualquier cantidad de días.
  function aplicaLimite() {
    return getTipoSolicitud() !== "adelanto";
  }

  function onCambioDia(e) {
    const sel = e.target;
    if (sel.value === "V" && aplicaLimite()) {
      const limite = getLimiteAntiguedad();
      if (contarV() > limite) {
        sel.value = sel.dataset.prev || "";
        Swal.fire({
          icon: "warning",
          title: "Días por antigüedad agotados",
          html:
            `No puedes seleccionar más días de <b>Vacaciones (V)</b> ` +
            `de los que te corresponden.<br><br>` +
            `Te corresponden <b>${limite}</b> día(s) por antigüedad ` +
            `y ya los tienes todos seleccionados.`,
          confirmButtonText: "Entendido",
        });
      }
    }
    sel.dataset.prev = sel.value;
    recalcularContadores();
  }

  function bindSelectsDia(scope) {
    (scope || document)
      .querySelectorAll(".calendario select")
      .forEach((sel) => {
        if (sel.dataset.bound === "1") return;
        sel.dataset.bound = "1";
        if (sel.dataset.prev === undefined) sel.dataset.prev = sel.value;
        sel.addEventListener("change", onCambioDia);
      });
  }

  // ---------- Generación de meses nuevos ----------
  function diasEnMes(anio, mes) {
    return new Date(anio, mes, 0).getDate();
  }
  function letraDia(anio, mes, dia) {
    const d = new Date(anio, mes - 1, dia);
    return LETRAS[(d.getDay() + 6) % 7];
  }

  function tablaParcial(anio, mes, ini, fin) {
    const dim = diasEnMes(anio, mes);
    let ths = "",
      letras = "",
      selects = "";
    for (let i = ini; i <= fin; i++) {
      ths += `<th>${i}</th>`;
      if (i <= dim) {
        const f = `${anio}-${String(mes).padStart(2, "0")}-${String(i).padStart(2, "0")}`;
        letras += `<td>${letraDia(anio, mes, i)}</td>`;
        selects += `<td>
                    <select name="dia_${f}" data-fecha="${f}" data-prev="">
                        <option value="" selected></option>
                        <option value="V">V</option>
                        <option value="D">D</option>
                        <option value="F">F</option>
                        <option value="R">R</option>
                    </select>
                </td>`;
      } else {
        letras += `<td></td>`;
        selects += `<td></td>`;
      }
    }
    return `<table class="calendario" style="margin-top:4px">
            <thead><tr>${ths}</tr></thead>
            <tbody>
                <tr>${letras}</tr>
                <tr class="diaSeleccionable">${selects}</tr>
            </tbody>
        </table>`;
  }

  function generarMesHTML(anio, mes) {
    const wrap = document.createElement("div");
    wrap.className = "mesCalendario mesExtra";
    wrap.dataset.mes = `${anio}-${mes}`;
    wrap.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mt-3">
                <h5 class="mb-0">Calendario de ${NOMBRE_MESES[mes]} ${anio}</h5>
                <button type="button" class="btn btn-outline-danger btn-sm btnQuitarMes">
                    <i class="fa-solid fa-trash"></i> Quitar este mes
                </button>
            </div>
            ${tablaParcial(anio, mes, 1, 16)}
            ${tablaParcial(anio, mes, 17, 31)}
        `;
    return wrap;
  }

  function obtenerUltimoYM() {
    let max = null;
    document.querySelectorAll(".calendario select[data-fecha]").forEach((s) => {
      const ym = s.getAttribute("data-fecha").substring(0, 7);
      if (!max || ym > max) max = ym;
    });
    return max;
  }
  function siguienteYM(ym) {
    let [a, m] = ym.split("-").map(Number);
    m += 1;
    if (m > 12) {
      m = 1;
      a += 1;
    }
    return { anio: a, mes: m };
  }

  // ---------- Botón: agregar mes siguiente ----------
  const btnAgregar = document.getElementById("btnAgregarMes");
  if (btnAgregar) {
    btnAgregar.addEventListener("click", function () {
      const ultimo = obtenerUltimoYM();
      let sig;
      if (ultimo) {
        sig = siguienteYM(ultimo);
      } else {
        const hoy = new Date();
        sig = { anio: hoy.getFullYear(), mes: hoy.getMonth() + 1 };
      }
      const nuevo = generarMesHTML(sig.anio, sig.mes);
      document.getElementById("contenedorCalendariosExtra").appendChild(nuevo);
      bindSelectsDia(nuevo);
      recalcularContadores();
    });
  }

  // ---------- Quitar mes (delegado, solo los extra) ----------
  const contExtra = document.getElementById("contenedorCalendariosExtra");
  if (contExtra) {
    contExtra.addEventListener("click", function (e) {
      const btn = e.target.closest(".btnQuitarMes");
      if (!btn) return;
      const mes = btn.closest(".mesExtra");
      if (!mes) return;

      const tieneSeleccion = [
        ...mes.querySelectorAll(".calendario select"),
      ].some((s) => s.value !== "");

      if (tieneSeleccion) {
        Swal.fire({
          title: "¿Quitar este mes?",
          text: "Se eliminarán los días seleccionados en este calendario.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, quitar",
          cancelButtonText: "Cancelar",
        }).then((r) => {
          if (r.isConfirmed) {
            mes.remove();
            recalcularContadores();
          }
        });
      } else {
        mes.remove();
        recalcularContadores();
      }
    });
  }

  // ---------- Init ----------
  bindSelectsDia(document);
  recalcularContadores();
});
