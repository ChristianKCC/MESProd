import { Toolsjs } from "../../Tools/Tools.js";

class Tiempoextra {

    inicio() {
        this.Tools = new Toolsjs();
        setInterval(this.Tools.mostrarHoraSimple(), 1000);
        this.Tools.llnarslcruta("php/index.php?motivostiempoextra", "motivos");
        this.Tools.llnarslc('CatalogoPersonal', "GetSlcDeps", "departamentoenc", 0);
        this.tblenc();
    }

    async getinfoemp(noemp) {
        const respuetaraw = await fetch("../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp);
        const respuesta = await respuetaraw.json();
        if (respuesta.length === 0) {
            document.getElementById("nombre").value = "";
            document.getElementById("departamento").value = "";
            document.getElementById("puesto").value = "";
            document.getElementById("tipoEmpleadoHidden").value = "";
            aplicarRestriccionMotivo9("");
            return;
        }
        document.getElementById("nombre").value = respuesta[0].nombre;
        document.getElementById("departamento").value = respuesta[0].departamento.trim();
        document.getElementById("puesto").value = respuesta[0].puesto;

        const rawTipo = parseInt(respuesta[0].EmpleadoSindicalizado);
        const tipo = rawTipo === 1 ? "empleado" : "sindicalizado";
        document.getElementById("tipoEmpleadoHidden").value = tipo;

        // Aplicar restricción del motivo 9 según tipo de empleado
        aplicarRestriccionMotivo9(tipo);

        document.getElementById("departamento").dispatchEvent(new Event("change"));
    }

    async getinfohoraentradaysalida(noemp, date) {
        var fechaActual = new Date(date + "T00:00:00");

        var fechaAnterior = new Date(fechaActual);
        fechaAnterior.setDate(fechaAnterior.getDate() - 1);
        var fechaAnteriorStr = formatearFecha(fechaAnterior);

        var fechaSiguiente = new Date(fechaActual);
        fechaSiguiente.setDate(fechaSiguiente.getDate() + 1);
        var fechaSiguienteStr = formatearFecha(fechaSiguiente);

        function formatearFecha(fecha) {
            var año = fecha.getFullYear();
            var mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
            var dia = fecha.getDate().toString().padStart(2, '0');
            return año + "-" + mes + "-" + dia;
        }

        const respuestaAnteriorRaw = await fetch("../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=" + noemp + "&fechabien=" + fechaAnteriorStr);
        const respuestaAnterior = await respuestaAnteriorRaw.json();

        const respuestaActualRaw = await fetch("../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=" + noemp + "&fechabien=" + date);
        const respuestaActual = await respuestaActualRaw.json();

        const respuestaSiguienteRaw = await fetch("../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=" + noemp + "&fechabien=" + fechaSiguienteStr);
        const respuestaSiguiente = await respuestaSiguienteRaw.json();

        function agruparPorHora(registros) {
            var horasVistas = {};
            var resultado = [];
            registros.forEach(function (registro) {
                if (registro.fecha_h) {
                    var horaSinSegundos = registro.fecha_h.substring(0, 8);
                    if (!horasVistas[horaSinSegundos]) {
                        horasVistas[horaSinSegundos] = true;
                        resultado.push({ fecha_h: registro.fecha_h, hora_limpia: horaSinSegundos });
                    }
                }
            });
            return resultado;
        }

        var registrosAnteriorLimpios  = agruparPorHora(respuestaAnterior);
        var registrosActualLimpios    = agruparPorHora(respuestaActual);
        var registrosSiguienteLimpios = agruparPorHora(respuestaSiguiente);

        function horaAMinutos(hora) {
            var partes = hora.split(":");
            return parseInt(partes[0]) * 60 + parseInt(partes[1]);
        }

        var turnos = {
            turno1:  { entrada: { min: 5*60,  max: 11*60 }, salida: { min: 13*60, max: 20*60 }, horaIdealEntrada: "07:00:00", horaIdealSalida: "15:00:00" },
            turno2:  { entrada: { min: 13*60, max: 17*60 }, salida: { min: 20*60, max: 24*60 }, horaIdealEntrada: "15:00:00", horaIdealSalida: "22:30:00" },
            turno3:  { entrada: { min: 20*60, max: 24*60 }, salida: { min: 5*60,  max: 11*60 }, horaIdealEntrada: "22:30:00", horaIdealSalida: "07:00:00" },
            mixto1:  { entrada: { min: 7*60+20, max: 9*60  }, salida: { min: 15*60+30, max: 19*60 }, horaIdealEntrada: "07:30:00", horaIdealSalida: "17:00:00" },
            mixto2:  { entrada: { min: 7*60+30, max: 10*60 }, salida: { min: 17*60, max: 21*60    }, horaIdealEntrada: "08:30:00", horaIdealSalida: "18:30:00" },
            mixto3:  { entrada: { min: 7*60,    max: 9*60  }, salida: { min: 16*60, max: 17*60    }, horaIdealEntrada: "07:00:00", horaIdealSalida: "16:30:00" },
            mixto4:  { entrada: { min: 6*60+45, max: 7*60+15 }, salida: { min: 16*60+30, max: 17*60+30 }, horaIdealEntrada: "07:00:00", horaIdealSalida: "17:00:00" }
        };

        function estaEnRango(horaMin, rango) {
            return horaMin >= rango.min && horaMin <= rango.max;
        }

        var entrada, salida;

        if (registrosActualLimpios.length >= 2) {
            var primeraMin = horaAMinutos(registrosActualLimpios[0].hora_limpia);
            var ultimaMin  = horaAMinutos(registrosActualLimpios[registrosActualLimpios.length - 1].hora_limpia);

            var esTurnoNocturno = false;
            for (var t in turnos) {
                if (estaEnRango(ultimaMin, turnos[t].entrada) && (t === "turno3" || t === "mixto3")) {
                    esTurnoNocturno = true;
                    break;
                }
            }

            if (esTurnoNocturno) {
                entrada = registrosActualLimpios[registrosActualLimpios.length - 1];
                salida  = registrosSiguienteLimpios.length > 0 ? registrosSiguienteLimpios[0] : null;
            } else {
                entrada = registrosActualLimpios[0];
                salida  = registrosActualLimpios[registrosActualLimpios.length - 1];
            }
        } else if (registrosActualLimpios.length === 1) {
            var unicaMin = horaAMinutos(registrosActualLimpios[0].hora_limpia);
            if (estaEnRango(unicaMin, turnos.turno3.entrada) || estaEnRango(unicaMin, turnos.mixto3.entrada)) {
                entrada = registrosActualLimpios[0];
                salida  = registrosSiguienteLimpios.length > 0 ? registrosSiguienteLimpios[0] : null;
            } else if (estaEnRango(unicaMin, turnos.turno3.salida)) {
                salida  = registrosActualLimpios[0];
                entrada = registrosAnteriorLimpios.length > 0 ? registrosAnteriorLimpios[registrosAnteriorLimpios.length - 1] : null;
            } else {
                entrada = registrosActualLimpios[0];
                salida  = null;
            }
        }

        if (!entrada || !salida) return;

        var horaentrada = entrada.hora_limpia;
        var horasalida  = salida.hora_limpia;
        const fechar    = new Date(date + "T00:00:00");
        var esSabado    = fechar.getDay() === 6;
        var resultado   = calcularTurnoYHoras(horaentrada, horasalida, esSabado);

        function minutosAHora(minutos) {
            const horas = Math.floor(minutos / 60);
            const mins  = minutos % 60;
            return horas.toString().padStart(2, '0') + ":" + mins.toString().padStart(2, '0');
        }

        let horaExtra2 = "00:00:00";
        const comp = "00:05:00";
        if (resultado.totalHoras <= comp) return;

        const horaExtrass  = horaAMinutos(resultado.totalHoras);
        const horaReglamen = horaAMinutos(resultado.horasReglamentarias);
        if (horaExtrass >= horaReglamen) {
            const diff2 = horaExtrass - horaReglamen;
            horaExtra2 = minutosAHora(diff2);
        }

        validarTiempoExtraLocal(horaExtra2, resultado.totalHoras, resultado.horasReglamentarias);
        document.getElementById("horaEX").value  = horaExtra2;
        document.getElementById("hrsReg").value  = resultado.horasReglamentarias;
    }

    async abrirtiempoextra() {
        let folio           = document.getElementById("folio").value;
        let fechaenc        = document.getElementById("fechaenc").value;
        let departamentoenc = document.getElementById("departamentoenc").value;

        if (fechaenc === "" || departamentoenc === "") {
            Swal.fire('UPS!!!', 'No puede haber campos vacíos', 'info');
            return false;
        }
        else if (folio != "") {
            Swal.fire('UPS!!!', 'Estas editando un folio, selecciona empezar de nuevo para crear un nuevo folio', 'info');
            return false;
        }

        const data = new FormData();
        data.append("fechaenc", fechaenc);
        data.append("departamentoenc", departamentoenc);

        try {
            const respuetaraw = await fetch("php/index.php?abrirtiempoextra", { method: "POST", body: data });
            const respuesta   = await respuetaraw.json();

            if (respuesta.error) {
                Swal.fire({ icon: 'error', title: 'No se pudo registrar el folio', text: respuesta.error, confirmButtonText: 'Entendido' });
                return;
            }

            Swal.fire('Listo!!!', 'Empieza a cargar los tiempos extra al folio ' + respuesta.id, 'success');
            document.getElementById("folio").value = respuesta.id;
            document.getElementById("formtiempoextra").reset();
            document.getElementById("fechaenc").disabled = true;
            document.getElementById("departamentoenc").disabled = true;
            document.getElementById("semanaFolioHidden").value = respuesta.semana;
        }
        catch (err) {
            Swal.fire('ERROR!', 'La respuesta no es JSON válido: ' + err, 'error');
        }
    }

    async guardartiempoextra() {
        let noemp      = document.getElementById("noemp").value;
        let fechainput = document.getElementById("fechainput").value;
        let horai      = document.getElementById("horai").value;
        let horaf      = document.getElementById("horaf").value;
        let maquina    = document.getElementById("maquinas").value;
        let motivos    = document.getElementById("motivos").value;
        let razon      = document.getElementById("razon").value;
        let folio      = document.getElementById("folio").value;
        let turnosel   = document.getElementById("turnosel").value;
        let nombre     = document.getElementById("nombre").value;
        let semanaFolio = document.getElementById("semanaFolioHidden").value;

        // Leer estado de checkboxes
        const chk12hrs   = document.getElementById("checkboxTurno3");
        const chkAnticipo = document.getElementById("checkboxAnticipo");
        const chkApoyo    = document.getElementById("checkboxApoyo");
        const activaTurno3_12hrs = chk12hrs.checked;
        const activaAnticipo     = chkAnticipo.checked;
        const activaApoyo        = chkApoyo.checked;

        // Validación de semana
        let fechaObj = new Date(fechainput);
        let semanaRegistro = getWeekNumber(fechaObj);
        if (semanaRegistro < (semanaFolio - 1)) {
            Swal.fire('UPS!!!', 'La fecha seleccionada pertenece a una semana demasiado antigua respecto al folio (más de 1 semana atrás).', 'warning');
            return;
        } else if (semanaRegistro > (semanaFolio + 1)) {
            Swal.fire('UPS!!!', 'La fecha seleccionada pertenece a una semana muy adelantada respecto al folio (más de 1 semana adelante).', 'warning');
            return;
        }

        let turnoSeleccionado   = document.getElementById("turnoSeleccionadoHidden").value;
        let horaFinalSinMargen  = document.getElementById("horaFinalSinMargenHidden").value;
        let horaFinalConMargen  = document.getElementById("horaFinalConMargenHidden").value;

        const motivoNum = parseInt(motivos);

        // ── Lógica por modo especial de checkbox ─────────────────────────────
        if (activaApoyo) {
            // Apoyo: usar hora inicio manual, horas y turno son rellenos
            turnosel  = "turno1";
            const TiempoEStr = document.getElementById("duracionTE").value;
            turnoSeleccionado = "turno1";
            // horai ya está capturado en el campo habilitado
            // horaf queda como calculado (o vacío, se valida en autorizatp)
            horaf = horaf || "00:00:00";
        }

        // ── Lógica por motivo ─────────────────────────────────────────────────
        if (motivoNum === 8) {
            let hra = parseFloat(document.getElementById("cambiohrario").value);
            if (hra === 3) {
                horai = "01:00:00"; horaf = "04:00:00";
            } else if (hra === 4) {
                horai = "01:00:00"; horaf = "06:00:00";
            } else if (hra === 2.5) {
                horai = "01:00:00"; horaf = "03:30:00";
            }
            turnosel = "turno1";

        } else if (motivoNum === 9) {
            const durComida = document.getElementById("horacomida").value;
            horai = "12:00:00";
            const [hc, mc]    = durComida.split(":").map(Number);
            const finComidaMin = 12 * 60 + hc * 60 + mc;
            const hFin = Math.floor(finComidaMin / 60).toString().padStart(2, "0");
            const mFin = (finComidaMin % 60).toString().padStart(2, "0");
            horaf    = `${hFin}:${mFin}:00`;
            turnosel = "turno1";

        } else if (motivoNum === 10 || motivoNum === 12) {
            if (!horai || !horaf || turnosel === "") {
                Swal.fire("Error", "Debes seleccionar el turno antes de guardar.", "warning");
                return;
            }

        } else if (!activaAnticipo && !activaApoyo) {
            // Motivos normales sin checkboxes especiales: asignación de turno 12hrs
            if (activaTurno3_12hrs && turnoSeleccionado === "turno3") {
                turnosel = "turno3_12hrs";
            } else if (activaTurno3_12hrs && turnoSeleccionado === "turno2") {
                turnosel = "turno2_13hrs";
            }
        }

        // ── Validaciones de campos vacíos ─────────────────────────────────────
        const motivosSinDuracion = [9, 8, 10, 12];
        const necesitaDuracion   = !motivosSinDuracion.includes(motivoNum) && !activaApoyo;

        if (folio === "") {
            Swal.fire('Error!', 'Necesitas crear un folio primero', 'warning'); return;
        }
        if (noemp === "" || fechainput === "" || maquina === "" || motivos === "" || razon === "" || nombre === "") {
            Swal.fire('Error', 'No puede haber campos vacíos', 'info'); return;
        }
        if (necesitaDuracion && !document.getElementById("duracionTE").value) {
            Swal.fire('Error', 'Debes ingresar la cantidad de horas de tiempo extra.', 'info'); return;
        }
        if (![9, 8].includes(motivoNum) && !activaApoyo && (!turnosel || turnosel === "")) {
            Swal.fire('Error', 'Debes seleccionar un turno.', 'info'); return;
        }

        // Validaciones de máximo de horas solo para flujo normal (sin anticipo ni apoyo)
        if (!activaAnticipo && !activaApoyo) {
            const TiempoEStr      = document.getElementById("duracionTE").value;
            const duracionTiempoE = TiempoEStr ? horaAMinutos(TiempoEStr) : 0;

            if (necesitaDuracion && duracionTiempoE > 240 && (turnoSeleccionado === "turno3" || turnoSeleccionado === "turno2")) {
                if (parseInt(motivos) !== 10 && parseInt(motivos) !== 12) {
                    Swal.fire('Error', 'No puedes agregar más de 4 hrs en el turno seleccionado', 'info'); return;
                }
            }

            if (necesitaDuracion && duracionTiempoE >= 210 && (turnoSeleccionado === "turno3" || turnoSeleccionado === "turno2") && !activaTurno3_12hrs && (motivoNum === 10 || motivoNum === 12)) {
                Swal.fire('Error', 'Después de 3:30 hrs en este turno debes activar la casilla de 12 hrs para guardar el registro.', 'info'); return;
            }

            // Validar que se haya activado 12hrs cuando es obligatorio
            if (necesitaDuracion) {
                const TiempoEStr2     = document.getElementById("duracionTE").value;
                const duracionTiempoE2 = TiempoEStr2 ? horaAMinutos(TiempoEStr2) : 0;
                const horasDecimal    = duracionTiempoE2 / 60;
                if ((turnoSeleccionado === "turno3" && horasDecimal >= 3.5 && horasDecimal <= 4) ||
                    (turnoSeleccionado === "turno2" && horasDecimal >= 4 && horasDecimal <= 4.5)) {
                    if (!activaTurno3_12hrs) {
                        Swal.fire('Error', 'Para esta cantidad de horas en el turno seleccionado debes activar la casilla de 12 hrs.', 'info');
                        return;
                    }
                }
            }
        }

        // ── Construir FormData ────────────────────────────────────────────────
        const data    = new FormData();
        const dataCSV = new FormData();

        data.append("noemp",      noemp);
        data.append("fechainput", fechainput);
        data.append("horai",      horai);
        data.append("horaf",      horaf);
        data.append("maquina",    maquina);
        data.append("motivos",    motivos);
        data.append("razon",      razon);
        data.append("folio",      folio);
        data.append("turnosel",   turnosel);
        data.append("nombre",     nombre);

        dataCSV.append("noemp",              noemp);
        dataCSV.append("fechainput",         fechainput);
        dataCSV.append("horai",              horai);
        dataCSV.append("horaf",              horaf);
        dataCSV.append("maquina",            maquina);
        dataCSV.append("motivos",            motivos);
        dataCSV.append("razon",              razon);
        dataCSV.append("folio",              folio);
        dataCSV.append("turnoSeleccionado",  turnoSeleccionado);
        dataCSV.append("horaFinalSinMargen", horaFinalSinMargen);
        dataCSV.append("horaFinalConMargen", horaFinalConMargen);

        const respuetaraw    = await fetch("php/index.php?guardartiempoextra", { method: "POST", body: data });
        const respuetarawcsv = await fetch("php/guardarCSV.php?guardarCSV",   { method: "POST", body: dataCSV });
        const respuesta      = await respuetaraw.json();

        if (respuesta.warning) {
            Swal.fire({
                title: 'Advertencia',
                text: respuesta.message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText:  'No, cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const confirmRaw  = await fetch("php/index.php?guardartiempoextraExt", { method: "POST", body: data });
                    const confirmResp = await confirmRaw.json();
                    if (confirmResp === "Listo") {
                        this.tblsubenc();
                        Swal.fire('Listo!!!', 'Registro guardado y enviado con éxito', 'success');
                        cleanData();
                    } else {
                        Swal.fire('Error', 'No se pudo guardar', 'error');
                    }
                }
            });
            return;
        }

        if (respuesta === "Listo") {
            Swal.fire('Listo!!!', 'Registro guardado y enviado con éxito', 'success');
            cleanData();
        } else if (respuesta === "Existe") {
            Swal.fire('UPS!!!', 'Ya tienes un tiempo extra existente', 'error');
        } else if (respuesta === "LimiteSemana") {
            Swal.fire('UPS!!!', 'Se alcanzó el límite de 60.5 horas en esta semana', 'warning');
        } else {
            Swal.fire('ERROR!!!', 'Error al guardar en la base de datos', 'error');
        }

        function cleanData() {
            const horariosDeTurno = document.getElementById("horariosDeTurno");
            document.getElementById("noemp").value       = "";
            document.getElementById("fechainput").value  = "";
            document.getElementById("horai").value       = "";
            document.getElementById("horaf").value       = "";
            document.getElementById("maquinas").value    = "";
            document.getElementById("motivos").value     = "";
            document.getElementById("razon").value       = "";
            document.getElementById("turnosel").value    = "";
            document.getElementById("nombre").value      = "";
            document.getElementById("departamento").value = "";
            document.getElementById("puesto").value      = "";
            document.getElementById("duracionTE").value  = "";
            document.getElementById("tipoEmpleadoHidden").value = "";
            horariosDeTurno.style.display = "none";
            // Resetear checkboxes
            resetearCheckboxes();
        }
    }

    async deletesub(id) {
        const data = new FormData();
        data.append("id", id);
        const respuestaraw = await fetch("./php/index.php?deletesub", { method: "POST", body: data });
        const respuesta    = await respuestaraw.json();
        this.tblsubenc();
        respuesta === "Listo"
            ? Swal.fire('Listo!!!', 'Registro eliminado', 'success')
            : Swal.fire('ERROR!!!', 'Hay un problema al eliminar', 'error');
    }

    async cambioTempTurno(noemp, estadoTurno, nombre, depto, id) {
        abrirModal();
        const nombreFull = nombre + " - " + noemp;
        document.getElementById("nombre_receptor").value    = nombreFull;
        document.getElementById("turno_presentacion").value = estadoTurno;
        document.getElementById("Depto_m").value            = depto;
        document.getElementById("folioTiempoExtra").value   = id;
    }

    async tblsubenc() {
        let folio = document.getElementById("folio").value;
        if (folio === "") return false;
        const respuetaraw = await fetch("php/index.php?tblsubenc&folio=" + folio);
        const respuesta   = await respuetaraw.json();
        let body = "";
        respuesta.forEach(elemento => {
            body += `<tr>
                <td>${elemento.id}</td>
                <td>${elemento.noemp}</td>
                <td>${elemento.nombre}</td>
                <td>${elemento.depto}</td>
                <td>${elemento.puesto}</td>
                <td>${elemento.fecha}</td>
                <td>${elemento.horai}</td>
                <td>${elemento.horaf}</td>
                <td>${elemento.maquina}</td>
                <td>${elemento.motivo}</td>
                <td>${elemento.razon}</td>
                <td>${elemento.estadoTurno}</td>
                <td><span class="${elemento.estadoClass}">${elemento.estadoTexto}</span></td>
                <td>`;

            if (elemento.cambioTempExiste) {
                body += `<button class="btn btn-sm btn-primary" onclick="verPDF(${elemento.Ctt_id})">
                            <i class="fa-solid fa-file-pdf"></i> Ver PDF Cambio T. Turno
                         </button>
                         <button class="btn btn-sm btn-danger" onclick="deleteSub(${elemento.id})">
                            <i class="fa-solid fa-trash"></i> Eliminar
                         </button>`;
            } else {
                body += `<button class="btn btn-sm btn-success" onclick="cambioTempTurno(${elemento.noemp}, '${elemento.estadoTurno}', '${elemento.nombre}', '${elemento.depto}', '${elemento.id}')">
                            <i class="fa-solid fa-plus"></i> Crea un Cambio T. Turno
                         </button>
                         <button class="btn btn-sm btn-danger" onclick="deleteSub(${elemento.id})">
                            <i class="fa-solid fa-trash"></i> Eliminar
                         </button>`;
            }
            body += `</td></tr>`;
        });
        document.getElementById("tbltiempoextra").innerHTML = body;
    }

    async tblenc() {
        const respuetaraw = await fetch("php/index.php?tblenc");
        const respuesta   = await respuetaraw.json();
        let body = "";
        respuesta.forEach(elemento => {
            body += `<tr><td>${elemento.id}</td><td>${elemento.fecha}</td><td>${elemento.departamento}</td><td>${elemento.creado}</td><td>`;
            if (elemento.terminado == null) {
                body += `<button class="btn btn-sm btn-warning" onclick="editEnc(${elemento.id})"><i class="fa-solid fa-pen-to-square"></i> Crear/eliminar registros</button>`;
            } else {
                body += `<button class="btn btn-sm btn-danger" onclick="pdfFin(${elemento.id})"><i class="fa-solid fa-file-pdf"></i> Descargar resultado PDF</button>`;
            }
            body += `</td></tr>`;
        });
        document.getElementById("tblenc").innerHTML = body;
    }

    async editenc(id) {
        const respuetaraw = await fetch("php/index.php?editenc&id=" + id);
        const respuesta   = await respuetaraw.json();
        document.getElementById("folio").value              = respuesta[0].id;
        document.getElementById("fechaenc").value           = respuesta[0].fecha;
        document.getElementById("departamentoenc").value    = respuesta[0].departamento;
        document.getElementById("fechaenc").disabled        = true;
        document.getElementById("departamentoenc").disabled = true;
        document.getElementById("semanaFolioHidden").value  = respuesta[0].semana;
        this.tblsubenc();
    }

    enviar(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text:  "¿Deseas enviar este tiempo extra a autorización del supervisor?",
            icon:  'warning',
            showCancelButton:  true,
            confirmButtonText: 'Si, seguro!',
            cancelButtonText:  'No, cancela!',
            reverseButtons:    true
        }).then((result) => {
            if (result.isConfirmed) {
                (async () => {
                    const respuestaraw = await fetch("./php/index.php?enviarfol&id=" + id);
                    const respuesta    = await respuestaraw.json();
                    respuesta === false
                        ? Swal.fire('ERROR!', 'Hay un error con la base de datos', 'error')
                        : Swal.fire('Terminado!', 'La solicitud fue enviada al área correspondiente', 'success');
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
// UTILIDADES GLOBALES
// ─────────────────────────────────────────────────────────────────────────────
function horaAMinutos(hora) {
    let partes = hora.split(":");
    return parseInt(partes[0]) * 60 + parseInt(partes[1]);
}

function minutosAHora(minutos) {
    minutos = ((minutos % (24 * 60)) + 24 * 60) % (24 * 60);
    let horas = Math.floor(minutos / 60);
    let mins  = minutos % 60;
    return horas.toString().padStart(2, '0') + ":" + mins.toString().padStart(2, '0');
}

function minutosAHoraConSegundos(minutos) {
    return minutosAHora(minutos) + ":00";
}

function getWeekNumber(input) {
    let date;
    if (typeof input === "string") {
        const [year, month, day] = input.split('-').map(Number);
        date = new Date(year, month - 1, day);
    } else if (input instanceof Date) {
        date = new Date(input.getTime());
    } else {
        throw new Error("Formato de fecha no válido");
    }
    const tempDate = new Date(date.getTime());
    tempDate.setDate(tempDate.getDate() + 4 - (tempDate.getDay() || 7));
    const yearStart  = new Date(tempDate.getFullYear(), 0, 1);
    const weekNumber = Math.ceil((((tempDate - yearStart) / 86400000) + 1) / 7);
    return weekNumber;
}

function validarTiempoExtraLocal(horasExtra, totalHoras, horasReglamentarias) {
    // Solo referencial en captura; la validación formal es en Autorizatp
}

function calcularTurnoYHoras(horaEntrada, horaSalida, esSabado = false) {
    var entradaMin = horaAMinutos(horaEntrada);
    var salidaMin  = horaAMinutos(horaSalida);
    if (salidaMin < entradaMin) salidaMin += 1440;

    var margen = 35;

    var turnos = {
        turno1: { inicio: 7*60,    fin: 15*60,      nombre: "Turno 1 (07:00:00 - 15:00:00)", duracion: "08:00:00" },
        turno2: { inicio: 15*60,   fin: 22*60+30,   nombre: "Turno 2 (15:00:00 - 22:30:00)", duracion: "07:30:00" },
        turno3: { inicio: 22*60+30,fin: 7*60+10,    nombre: "Turno 3 (22:30:00 - 07:00:00)", duracion: "08:30:00" },
        mixto4: { inicio: 7*60,    fin: 17*60,       nombre: "Mixto 4 (07:00:00 - 17:00:00)", duracion: "10:00:00" },
        mixto1: { inicio: 7*60+20, fin: 17*60+10,   nombre: "Mixto 1 (07:30:00 - 17:00:00)", duracion: "10:00:00" },
        mixto2: { inicio: 8*60+30, fin: 18*60+30,   nombre: "Mixto 2 (08:30:00 - 18:30:00)", duracion: "10:00:00" },
        mixto3: { inicio: 7*60,    fin: 16*60+30,   nombre: "Mixto 3 (07:00:00 - 16:30:00)", duracion: "09:30:00" }
    };

    var turnoDetectado = null;
    for (var key in turnos) {
        var t = turnos[key];
        var margenE = (key === "mixto4") ? 15 : margen;
        if (Math.abs(entradaMin - t.inicio) <= margenE && Math.abs(salidaMin - t.fin) <= margen * 4) {
            turnoDetectado = key; break;
        }
    }
    if (!turnoDetectado) {
        var prioridad = ['mixto4','mixto1','mixto2','turno1','turno2','mixto3','turno3'];
        for (var i = 0; i < prioridad.length; i++) {
            var k  = prioridad[i];
            var tt = turnos[k];
            var mE = (k === "mixto4") ? 15 : margen;
            if (Math.abs(entradaMin - tt.inicio) <= mE) { turnoDetectado = k; break; }
        }
    }

    var td = turnoDetectado ? turnos[turnoDetectado] : null;
    var horaInicioTurno, horaFinTurno, nombreTurno, horasReglamentarias;
    if (td) {
        horaInicioTurno     = minutosAHora(td.inicio);
        horaFinTurno        = minutosAHora(td.fin);
        nombreTurno         = td.nombre;
        horasReglamentarias = esSabado ? "05:00:00" : td.duracion;
        if (esSabado) nombreTurno = "MIXTO SABADO";
    } else {
        horaInicioTurno = horaEntrada; horaFinTurno = "00:00:00";
        nombreTurno = "No hay"; horasReglamentarias = "00:00:00";
    }

    var minutosTrabajados = salidaMin - entradaMin;
    var finTurnoMin       = td ? td.fin : salidaMin;
    var horasExtrasMin    = salidaMin - finTurnoMin;
    var horasExtras       = horasExtrasMin > 0 ? minutosAHora(horasExtrasMin) : "00:00:00";
    var totalHoras        = minutosAHora(minutosTrabajados);

    return { turno: nombreTurno, horaInicioTurno, horaFinTurno, horasExtras, totalHoras, horasReglamentarias, salidaMin, entradaMin, finTurnoMin };
}

// ─────────────────────────────────────────────────────────────────────────────
// CATÁLOGO DE TURNOS (para cálculos de anticipo)
// ─────────────────────────────────────────────────────────────────────────────
const CATALOGO_TURNOS_CAPTURA = {
    turno1:       { inicio: 7*60,       fin: 15*60,      hrsRegl: "08:00:00" },
    turno2:       { inicio: 15*60,      fin: 22*60+30,   hrsRegl: "07:30:00" },
    turno3:       { inicio: 22*60+30,   fin: 7*60,       hrsRegl: "08:30:00" },
    turno2_13hrs: { inicio: 10*60+30,   fin: 22*60+30,   hrsRegl: "04:30:00" },
    turno3_12hrs: { inicio: 19*60,      fin: 7*60,       hrsRegl: "08:30:00" },
    mixto1:       { inicio: 7*60+30,    fin: 17*60,      hrsRegl: "10:00:00" },
    mixto2:       { inicio: 8*60+30,    fin: 18*60+30,   hrsRegl: "10:00:00" },
    mixto3:       { inicio: 7*60,       fin: 16*60+30,   hrsRegl: "09:30:00" },
    mixto4:       { inicio: 7*60,       fin: 17*60,      hrsRegl: "10:00:00" }
};

// ─────────────────────────────────────────────────────────────────────────────
// aplicarRestriccionMotivo9 — sombrea el motivo 9 si es empleado
// ─────────────────────────────name──────────────────────────────────────────
function aplicarRestriccionMotivo9(tipo) {
    const selectMotivos = document.getElementById("motivos");
    for (let i = 0; i < selectMotivos.options.length; i++) {
        const opt = selectMotivos.options[i];
        // El motivo 9 tiene value "9"
        if (opt.value === "9") {
            if (tipo === "empleado") {
                opt.disabled = true;
                opt.style.color = "#aaa";
                opt.style.backgroundColor = "#f0f0f0";
                opt.title = "Solo disponible para sindicalizados";
                // Si estaba seleccionado, limpiar
                if (selectMotivos.value === "9") {
                    selectMotivos.value = "";
                }
            } else {
                opt.disabled = false;
                opt.style.color = "";
                opt.style.backgroundColor = "";
                opt.title = "";
            }
            break;
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// resetearCheckboxes — limpia todos los checkboxes y sus efectos
// ─────────────────────────────────────────────────────────────────────────────
function resetearCheckboxes() {
    const chk12hrs    = document.getElementById("checkboxTurno3");
    const chkAnticipo = document.getElementById("checkboxAnticipo");
    const chkApoyo    = document.getElementById("checkboxApoyo");

    chk12hrs.checked    = false;
    chkAnticipo.checked = false;
    chkApoyo.checked    = false;

    // Restaurar campos al estado normal
    const horai      = document.getElementById("horai");
    const horaf      = document.getElementById("horaf");
    const turnoSel   = document.getElementById("turnosel");
    const duracionTE = document.getElementById("duracionTE");
    const razon      = document.getElementById("razon");

    horai.readOnly      = true;
    horaf.readOnly      = true;
    turnoSel.hidden     = false;
    duracionTE.hidden   = false;
    razon.readOnly      = false;
    razon.value         = "";

    document.getElementById("contenedorCheckboxes").style.display = "block";
    document.getElementById("contenedorCheckbox12hrs").style.display = "none";
}

// ─────────────────────────────────────────────────────────────────────────────
// recalcularHorasAnticipo — recalcula inicio/fin si anticipo está activo
// ─────────────────────────────────────────────────────────────────────────────
function recalcularHorasAnticipo() {
    const turnoSel   = document.getElementById("turnosel").value;
    const durStr     = document.getElementById("duracionTE").value;
    const horai      = document.getElementById("horai");
    const horaf      = document.getElementById("horaf");

    if (!turnoSel || !durStr) {
        horai.value = "";
        horaf.value = "";
        return;
    }

    const def = CATALOGO_TURNOS_CAPTURA[turnoSel];
    if (!def) return;

    const durMin  = horaAMinutos(durStr);
    const inicioTE = ((def.inicio - durMin) + 24 * 60) % (24 * 60);
    const finTE    = def.inicio;

    horai.value = minutosAHoraConSegundos(inicioTE);
    horaf.value = minutosAHoraConSegundos(finTE);

    document.getElementById("turnoSeleccionadoHidden").value    = turnoSel;
    document.getElementById("horaFinalSinMargenHidden").value   = minutosAHoraConSegundos(finTE);
    document.getElementById("horaFinalConMargenHidden").value   = minutosAHoraConSegundos((finTE + 15) % (24 * 60));

    // Mostrar info referencial del turno
    const horariosDeTurno = document.getElementById("horariosDeTurno");
    horariosDeTurno.style.display = "block";
    document.getElementById("valorTurnoHora").textContent =
        minutosAHora(def.inicio) + " - " + minutosAHora(def.fin);
}

// ─────────────────────────────────────────────────────────────────────────────
// validarCondicionesCheckbox12hrs — muestra/oculta checkbox de 12hrs
// ─────────────────────────────────────────────────────────────────────────────
function validarCondicionesCheckbox12hrs() {
    const turno    = document.getElementById("turnosel").value;
    const horasStr = document.getElementById("duracionTE").value;
    const cont12   = document.getElementById("contenedorCheckbox12hrs");
    const chk12    = document.getElementById("checkboxTurno3");

    if (!horasStr || !turno) {
        cont12.style.display = "none";
        chk12.checked = false;
        return;
    }

    const minutos = horaAMinutos(horasStr);
    const horasDec = minutos / 60;

    // Turno 3: mostrar si horas entre 3:30 y 4:00 (210 a 240 min)
    if (turno === "turno3" && minutos >= 210 && minutos <= 240) {
        cont12.style.display = "block";
    }
    // Turno 2: mostrar si horas entre 4:00 y 4:30 (240 a 270 min)
    else if (turno === "turno2" && minutos >= 240 && minutos <= 270) {
        cont12.style.display = "block";
    }
    else {
        cont12.style.display = "none";
        chk12.checked = false;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// INSTANCIA Y EVENTOS
// ─────────────────────────────────────────────────────────────────────────────
Tiempoextra = new Tiempoextra();
Tiempoextra.inicio();

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
        .then(() => { Tiempoextra.tblsubenc(); })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar y enviar';
        });
});

// ─────────────────────────────────────────────────────────────────────────────
// LISTENER DE MOTIVOS
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById("motivos").addEventListener("change", function () {
    const motivoNum = parseInt(this.value);

    const elHorai           = document.getElementById("horai");
    const elHoraf           = document.getElementById("horaf");
    const elTurnosel        = document.getElementById("turnosel");
    const elDuracionTE      = document.getElementById("duracionTE");
    const elCambiohrario    = document.getElementById("cambiohrario");
    const elHoracomida      = document.getElementById("horacomida");
    const elHorariosDeTurno = document.getElementById("horariosDeTurno");
    const cont12            = document.getElementById("contenedorCheckbox12hrs");

    // Estado base
    elHorai.hidden        = false;
    elHoraf.hidden        = false;
    elTurnosel.hidden     = false;
    elDuracionTE.hidden   = false;
    elCambiohrario.hidden = true;
    elHoracomida.hidden   = true;
    elTurnosel.disabled   = true;
    elHorai.readOnly      = true;
    elHoraf.readOnly      = true;

    elHorai.value      = "";
    elHoraf.value      = "";
    elTurnosel.value   = "";
    elDuracionTE.value = "";
    elHorariosDeTurno.style.display = "none";
    cont12.style.display = "none";
    document.getElementById("checkboxTurno3").checked = false;

    if (motivoNum === 8) {
        elHorai.hidden        = true;
        elHoraf.hidden        = true;
        elTurnosel.hidden     = true;
        elDuracionTE.hidden   = true;
        elCambiohrario.hidden = false;

    } else if (motivoNum === 9) {
        elHorai.hidden       = true;
        elHoraf.hidden       = true;
        elTurnosel.hidden    = true;
        elDuracionTE.hidden  = true;
        elHoracomida.hidden  = false;

    } else if (motivoNum === 10 || motivoNum === 12) {
        elDuracionTE.hidden  = true;
        elDuracionTE.value   = "";
        elTurnosel.disabled  = false;

    } else {
        // Flujo normal — turnosel se habilita al ingresar duracionTE
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// LISTENER DE TURNO
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById("turnosel").addEventListener("change", function () {
    const turnoSeleccionado = this.value;
    const motivoNum         = parseInt(document.getElementById("motivos").value);
    const chkAnticipo       = document.getElementById("checkboxAnticipo");

    const turnosDef = {
        turno1:       { horaIdealEntrada: "07:00:00", horaIdealSalida: "15:00:00" },
        turno2:       { horaIdealEntrada: "15:00:00", horaIdealSalida: "22:30:00" },
        turno3:       { horaIdealEntrada: "22:30:00", horaIdealSalida: "07:00:00" },
        turno3_12hrs: { horaIdealEntrada: "19:00:00", horaIdealSalida: "22:30:00" },
        turno2_13hrs: { horaIdealEntrada: "10:30:00", horaIdealSalida: "15:00:00" },
        mixto1:       { horaIdealEntrada: "07:30:00", horaIdealSalida: "17:00:00" },
        mixto2:       { horaIdealEntrada: "08:30:00", horaIdealSalida: "18:30:00" },
        mixto3:       { horaIdealEntrada: "07:00:00", horaIdealSalida: "16:30:00" },
        mixto4:       { horaIdealEntrada: "07:00:00", horaIdealSalida: "17:00:00" }
    };

    if (!turnosDef[turnoSeleccionado]) {
        document.getElementById("horariosDeTurno").style.display = "none";
        return;
    }

    const datosTurno = turnosDef[turnoSeleccionado];

    // Mostrar horario referencial
    const horariosDeTurno = document.getElementById("horariosDeTurno");
    horariosDeTurno.style.display = "block";
    document.getElementById("valorTurnoHora").textContent =
        datosTurno.horaIdealEntrada + ' - ' + datosTurno.horaIdealSalida;

    const finTurnoMin = horaAMinutos(datosTurno.horaIdealSalida);

    // ── Modo ANTICIPO ──────────────────────────────────────────────────────
    if (chkAnticipo.checked) {
        recalcularHorasAnticipo();
        return;
    }

    // ── Motivos descanso/festivo ───────────────────────────────────────────
    if (motivoNum === 10 || motivoNum === 12) {
        document.getElementById("horai").value = datosTurno.horaIdealEntrada;
        document.getElementById("horaf").value = datosTurno.horaIdealSalida;
        document.getElementById("turnoSeleccionadoHidden").value    = turnoSeleccionado;
        document.getElementById("horaFinalSinMargenHidden").value   = datosTurno.horaIdealSalida;
        document.getElementById("horaFinalConMargenHidden").value   = minutosAHoraConSegundos(finTurnoMin + 15);
        return;
    }

    // ── Flujo normal ──────────────────────────────────────────────────────
    const TiempoEStr      = document.getElementById("duracionTE").value;
    const duracionTiempoE = TiempoEStr ? horaAMinutos(TiempoEStr) : 0;

    document.getElementById("horai").value = datosTurno.horaIdealSalida;

    const horaFinalsinMargen = minutosAHoraConSegundos(finTurnoMin + duracionTiempoE);
    const horaFinalconMargen = minutosAHoraConSegundos(finTurnoMin + duracionTiempoE + 15);

    document.getElementById("horaf").value                         = horaFinalsinMargen;
    document.getElementById("turnoSeleccionadoHidden").value       = turnoSeleccionado;
    document.getElementById("horaFinalSinMargenHidden").value      = horaFinalsinMargen;
    document.getElementById("horaFinalConMargenHidden").value      = horaFinalconMargen;

    validarCondicionesCheckbox12hrs();
});

// ─────────────────────────────────────────────────────────────────────────────
// DOMContentLoaded
// ─────────────────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    const duracionTE         = document.getElementById("duracionTE");
    const turnoSel           = document.getElementById("turnosel");
    const checkboxTurno3     = document.getElementById("checkboxTurno3");
    const checkboxAnticipo   = document.getElementById("checkboxAnticipo");
    const checkboxApoyo      = document.getElementById("checkboxApoyo");
    const horai              = document.getElementById("horai");
    const horaf              = document.getElementById("horaf");
    const motivo             = document.getElementById("motivos");
    const razon              = document.getElementById("razon");
    const turnoHidden        = document.getElementById("turnoSeleccionadoHidden");
    const horaFinalSinMargenHidden = document.getElementById("horaFinalSinMargenHidden");
    const horaFinalConMargenHidden = document.getElementById("horaFinalConMargenHidden");

    let estadoOriginal = { horai: null, horaf: null, horaFinalSinMargen: null, horaFinalConMargen: null };

    // ── duracionTE: habilitar turno y recalcular si anticipo activo ──────────
    duracionTE.addEventListener("input", () => {
        const motivoNum = parseInt(motivo.value);
        if ([9, 8, 10, 12].includes(motivoNum)) return;

        if (duracionTE.value.trim() === "") {
            turnoSel.disabled = true;
            turnoSel.value    = "";
            horai.value       = "";
            horaf.value       = "";
        } else {
            turnoSel.disabled = false;
        }

        // Si anticipo activo recalcular
        if (checkboxAnticipo.checked && turnoSel.value) {
            recalcularHorasAnticipo();
        }
        // Si turno ya seleccionado en flujo normal, actualizar hora fin
        else if (!checkboxAnticipo.checked && !checkboxApoyo.checked && turnoSel.value) {
            const motivoNum2 = parseInt(motivo.value);
            if (motivoNum2 !== 10 && motivoNum2 !== 12) {
                const turnoSeleccionado = turnoSel.value;
                const turnosDef = {
                    turno1:       { horaIdealSalida: "15:00:00" },
                    turno2:       { horaIdealSalida: "22:30:00" },
                    turno3:       { horaIdealSalida: "07:00:00" },
                    turno3_12hrs: { horaIdealSalida: "22:30:00" },
                    turno2_13hrs: { horaIdealSalida: "15:00:00" },
                    mixto1:       { horaIdealSalida: "17:00:00" },
                    mixto2:       { horaIdealSalida: "18:30:00" },
                    mixto3:       { horaIdealSalida: "16:30:00" },
                    mixto4:       { horaIdealSalida: "17:00:00" }
                };
                if (turnosDef[turnoSeleccionado]) {
                    const finTurnoMin     = horaAMinutos(turnosDef[turnoSeleccionado].horaIdealSalida);
                    const duracionTiempoE = horaAMinutos(duracionTE.value);
                    const horaFinalsinMargen = minutosAHoraConSegundos(finTurnoMin + duracionTiempoE);
                    const horaFinalconMargen = minutosAHoraConSegundos(finTurnoMin + duracionTiempoE + 15);
                    horaf.value = horaFinalsinMargen;
                    horaFinalSinMargenHidden.value = horaFinalsinMargen;
                    horaFinalConMargenHidden.value = horaFinalconMargen;
                }
            }
        }

        validarCondicionesCheckbox12hrs();
    });

    // ── Validar formato hh:mm en duracionTE al salir del campo ──────────────
    duracionTE.addEventListener("blur", function () {
        const regex = /^([01]\d|2[0-3]):([0-5]\d)$/;
        if (this.value && !regex.test(this.value)) {
            Swal.fire('UPS!!!', 'Formato válido: hh:mm (00:00 a 23:59)', 'info');
            this.value = "";
            turnoSel.value = ""; horai.value = ""; horaf.value = "";
        }
    });

    // ── CHECKBOX 12HRS ────────────────────────────────────────────────────────
    checkboxTurno3.addEventListener("change", function () {
        if (this.checked) {
            // Desactivar los otros
            checkboxAnticipo.checked = false;
            checkboxApoyo.checked    = false;

            estadoOriginal = {
                horai: horai.value, horaf: horaf.value,
                horaFinalSinMargen: horaFinalSinMargenHidden.value,
                horaFinalConMargen: horaFinalConMargenHidden.value
            };

            if (turnoSel.value === "turno3") {
                horai.value = "10:30:00"; horaf.value = "22:30:00";
                turnoHidden.value = "turno3";
                horaFinalSinMargenHidden.value = "22:30:00";
                horaFinalConMargenHidden.value = "22:45:00";
            } else if (turnoSel.value === "turno2") {
                horai.value = "10:30:00"; horaf.value = "15:00:00";
                turnoHidden.value = "turno2";
                horaFinalSinMargenHidden.value = "15:00:00";
                horaFinalConMargenHidden.value = "15:15:00";
            }
        } else {
            if (estadoOriginal.horai) horai.value = estadoOriginal.horai;
            if (estadoOriginal.horaf) horaf.value = estadoOriginal.horaf;
            if (estadoOriginal.horaFinalSinMargen) horaFinalSinMargenHidden.value = estadoOriginal.horaFinalSinMargen;
            if (estadoOriginal.horaFinalConMargen) horaFinalConMargenHidden.value = estadoOriginal.horaFinalConMargen;
        }
    });

    // ── CHECKBOX ANTICIPO ─────────────────────────────────────────────────────
    checkboxAnticipo.addEventListener("change", function () {
        if (this.checked) {
            // Solo uno activo
            checkboxTurno3.checked = false;
            checkboxApoyo.checked  = false;

            // Sin restricciones de horas ni turno
            turnoSel.disabled    = false;
            duracionTE.hidden    = false;
            turnoSel.hidden      = false;
            horai.readOnly       = true;
            horaf.readOnly       = true;
            razon.readOnly       = true;
            razon.value          = "Anticipo";
            document.getElementById("contenedorCheckbox12hrs").style.display = "none";
            checkboxTurno3.checked = false;

            // Recalcular si ya hay valores
            if (turnoSel.value && duracionTE.value) {
                recalcularHorasAnticipo();
            } else {
                horai.value = "";
                horaf.value = "";
            }
        } else {
            // Restaurar estado normal
            horai.readOnly  = true;
            horaf.readOnly  = true;
            razon.readOnly  = false;
            razon.value     = "";
            horai.value     = "";
            horaf.value     = "";
            turnoSel.value  = "";
            duracionTE.value = "";
            turnoSel.disabled = true;
            document.getElementById("horariosDeTurno").style.display = "none";
        }
    });

    // ── CHECKBOX APOYO ────────────────────────────────────────────────────────
    checkboxApoyo.addEventListener("change", function () {
        if (this.checked) {
            // Solo uno activo
            checkboxTurno3.checked   = false;
            checkboxAnticipo.checked = false;

            // Ocultar duracion y turno, activar horai
            duracionTE.hidden    = true;
            turnoSel.hidden      = true;
            horai.hidden         = false;
            horaf.hidden         = true;
            horai.readOnly       = false;
            razon.readOnly       = true;
            razon.value          = "Apoyo";

            // Rellenar con valores de relleno para no romper la BD
            duracionTE.value = "01:00";
            turnoSel.value   = "turno1";
            document.getElementById("turnoSeleccionadoHidden").value = "turno1";
            horai.value      = "";
            horaf.value      = "00:00:00";

            document.getElementById("contenedorCheckbox12hrs").style.display = "none";
            document.getElementById("horariosDeTurno").style.display = "none";
            checkboxTurno3.checked = false;
        } else {
            // Restaurar
            duracionTE.hidden   = false;
            turnoSel.hidden     = false;
            horai.hidden        = false;
            horaf.hidden        = false;
            horai.readOnly      = true;
            horaf.readOnly      = true;
            razon.readOnly      = false;
            razon.value         = "";
            horai.value         = "";
            horaf.value         = "";
            duracionTE.value    = "";
            turnoSel.value      = "";
            turnoSel.disabled   = true;
        }
    });

    // ── TURNO: recalcular si anticipo activo ──────────────────────────────────
    turnoSel.addEventListener("change", function () {
        if (checkboxAnticipo.checked && duracionTE.value) {
            recalcularHorasAnticipo();
        }
        validarCondicionesCheckbox12hrs();
    });

    window.validarCondicionesCheckbox = validarCondicionesCheckbox12hrs;

    // ─────────────────────────────────────────────────────────────────────────
    // DRIVER.JS
    // ─────────────────────────────────────────────────────────────────────────
        const driver = window.driver.js.driver;

    // -------------------------------
    // Pasos - Vista principal
    // -------------------------------
    const steps = [
        {
            element: ".tittlecont",
            popover: {
                title: "Tiempos Extra",
                description: "Aquí comienza el proceso para registrar solicitudes de tiempo extra.",
                side: "bottom"
            }
        },
        {
            element: ".alert.alert-info",
            popover: {
                title: "Instrucciones iniciales",
                description: "Recuerda que los tiempos extra se consideran a partir de 55 minutos después de tus horas reglamentarias.",
                side: "bottom"
            }
        },
        {
            element: "#folio",
            popover: {
                title: "Folio",
                description: "Este campo muestra el folio generado para tu solicitud de tiempo extra.",
                side: "top"
            }
        },
        {
            element: "#fechaenc",
            popover: {
                title: "Fecha",
                description: "Selecciona la fecha de inicio de la semana para tu solicitud de tiempo extra.",
                side: "top"
            }
        },
        {
            element: "#departamentoenc",
            popover: {
                title: "Departamento",
                description: "Indica el departamento al que pertenece la solicitud.",
                side: "top"
            }
        },
        {
            element: "#abrir",
            popover: {
                title: "Crear folio",
                description: "Haz clic aquí para generar un nuevo folio de solicitud.",
                side: "top"
            }
        },
        {
            element: "#btnverfolio",
            popover: {
                title: "Ver folios",
                description: "Consulta los folios creados previamente desde esta opción.",
                side: "top"
            }
        },
        {
            element: "#creapdf",
            popover: {
                title: "Previsualizar PDF",
                description: "Genera una vista previa en PDF de tu solicitud de tiempo extra (RECUERDA SELECCIONAR UN FOLIO O CREARLO ANTES).",
                side: "top"
            }
        },
        {
            element: ".botonEmpezarDeNuevo",
            popover: {
                title: "Reiniciar proceso",
                description: "Presiona este botón para limpiar todos los campos e iniciar un nuevo registro.",
                side: "top"
            }
        },
        {
            element: "#noemp",
            popover: {
                title: "Número de empleado",
                description: "Ingresa el número de empleado para cargar sus datos.",
                side: "top"
            }
        },
        {
            element: "#motivos",
            popover: {
                title: "Motivo del tiempo extra",
                description: "Selecciona el motivo por el cual se solicita el tiempo extra.",
                side: "top"
            }
        },
        {
            element: "#horai",
            popover: {
                title: "Inicio de tiempo extra",
                description: "Aquí se muestra la hora de inicio del tiempo extra (ESTE CAMPO SE LLENARA AUTOMATICAMENTE UNA VEZ SELECCIONES UN TURNO).",
                side: "top"
            }
        },
        {
            element: "#horaf",
            popover: {
                title: "Fin de tiempo extra",
                description: "Aquí se muestra la hora de finalización del tiempo extra (ESTE CAMPO SE LLENARA AUTOMATICAMENTE SUMANDO LAS HRS SOLICITADAS MAS LA HORA DE INICIO DE T. EXTRA).",
                side: "top"
            }
        },
        {
            element: "#maquinas",
            popover: {
                title: "Máquina",
                description: "Selecciona la máquina en la que se realizará el tiempo extra (SE MOSTRARAN UNICAMENTE LAS RELACIONADAS A TU DEPARTAMENTO).",
                side: "top"
            }
        },
        {
            element: "#fechainput",
            popover: {
                title: "Fecha de solicitud",
                description: "Selecciona el dia en el que el empleado hará su tiempo extra.",
                side: "top"
            }
        },
        {
            element: "#razon",
            popover: {
                title: "Razón del tiempo extra",
                description: "Especifica la razón por la cual se solicita el tiempo extra.",
                side: "top"
            }
        },
        {
            element: "#duracionTE",
            popover: {
                title: "Duración",
                description: "En este campo debes ingresar la cantidad de tiempo extra a hacer. ES IMPORTANTE QUE LO ESCRIBAS EN ESTE FORMATO hh:mm (HORAS:MINUTOS).",
                side: "top"
            }
        },
        {
            element: "#turnosel",
            popover: {
                title: "Turno",
                description: "En este campo debes seleccionar el turno que tendras el día que se solicita el tiempo extra.",
                side: "top"
            }
        },
        {
            element: "#contenedorCheckboxes",
            popover: {
                title: "Casos especiales",
                description: "Debes de seleccionar el caso que te corresponda segun tus necesidades, se contemplan 12 horas, Anticipos y Apoyos en turno seguido",
                side: "top"
            }
        },
        {
            element: "#guardar",
            popover: {
                title: "Guardar y enviar",
                description: "Haz clic aquí para guardar y enviar la solicitud de tiempo extra para su validación y autorización. (UNA VEZ QUE ENVIES TU SOLICITUD TODOS LOS CAMPOS SE LIMPIARAN EN AUTOMATICO)",
                side: "top"
            }
        },
        {
            element: ".LimpiarCampos",
            popover: {
                title: "Limpiar campos",
                description: "Si te equivocas o quieres iniciar un nuevo registro presiona este boton para limpiar todos los campos",
                side: "top"
            }
        },
        {
            element: "#consultar",
            popover: {
                title: "Validar solicitudes",
                description: "UNA VEZ QUE REGISTRES TUS SOLICITUDES DEBES DE VALIDAR CADA REGISTRO ANTES DE ENVIAR A SU AUTORIZACIÓN, LO HARAS UNA VEZ QUE LA FECHA Y HORA DE FINALIZACIÓN DEL TIEMPO EXTRA HAYAN FINALIZADO, DEBES HACERLO O DE LO CONTRARIO EL GERENTE CORRESPONDIENTE NO PODRA APROBAR O RECHAZAR LAS SOLICITUDES",
                side: "top",
                popoverClass: "popover-importante"
            }
        },
        {
            element: ".alert.alert-success",
            popover: {
                title: "Tabla de solicitudes",
                description: "Aquí encontrarás el estado de las últimas solicitudes según el folio seleccionado.",
                side: "bottom"
            }
        },
        {
            element: "#tbltiempoextra",
            popover: {
                title: "Solicitudes creadas",
                description: "Consulta el detalle de las solicitudes registradas en la tabla. A LADO DE CADA REGISTRO ENCONTRARAS UN BOTON PARA CREAR CAMBIOS TEMPORALES DE TURNO.",
                side: "top",
                popoverClass: "popover-importante"
            }
        },
        {
            element: "#btnAyuda",
            popover: {
                title: "Volver a ver el tutorial",
                description: "Si necesitas repasar cómo usar esta pantalla, presiona este botón para repetir el tutorial.",
                side: "bottom"
            }
        }
    ];

    // -------------------------------
    // Pasos - Modal cambio de turno
    // -------------------------------
    const stepsModal = [
        {
            element: "#modalOverlayLabel",
            popover: {
                title: "Cambio Temporal de Turno",
                description: "Este formulario permite registrar un cambio temporal de turno.",
                side: "bottom"
            }
        },
        {
            element: "#fecha_emision",
            popover: {
                title: "Fecha de emisión",
                description: "Selecciona la fecha en que se emite el cambio.",
                side: "top"
            }
        },
        {
            element: "#Depto_m",
            popover: {
                title: "Departamento",
                description: "Indica el departamento al que pertenece el empleado.",
                side: "top"
            }
        },
        {
            element: "#nombre_receptor",
            popover: {
                title: "Receptor",
                description: "Nombre completo del empleado que recibirá el cambio de turno.",
                side: "top"
            }
        },
        {
            element: "#de_area",
            popover: {
                title: "Supervisor",
                description: "Aquí se muestra el nombre del supervisor que autoriza el cambio.",
                side: "top"
            }
        },
        {
            element: "#horario_texto",
            popover: {
                title: "Horario",
                description: "Especifica el nuevo horario asignado.",
                side: "top"
            }
        },
        {
            element: "#rol",
            popover: {
                title: "Rol",
                description: "Indica el rol o función del empleado en el nuevo turno.",
                side: "top"
            }
        },
        {
            element: "#fecha_inicio",
            popover: {
                title: "Fecha de inicio",
                description: "Selecciona el día en que comienza el cambio de turno.",
                side: "top"
            }
        },
        {
            element: "#hasta_el",
            popover: {
                title: "Fecha de término",
                description: "Selecciona el día en que finaliza el cambio de turno.",
                side: "top"
            }
        },
        {
            element: "#turno_presentacion",
            popover: {
                title: "Turno de presentación",
                description: "Indica el turno en el que debe presentarse el empleado.",
                side: "top"
            }
        },
        {
            element: "#hora_presentacion",
            popover: {
                title: "Hora de presentación",
                description: "Especifica la hora en que debe presentarse.",
                side: "top"
            }
        },
        {
            element: "#horario_desde",
            popover: {
                title: "Horario desde",
                description: "Hora de inicio del nuevo horario.",
                side: "top"
            }
        },
        {
            element: "#horario_hasta",
            popover: {
                title: "Horario hasta",
                description: "Hora de fin del nuevo horario.",
                side: "top"
            }
        },
        {
            element: "#hasta_tripulacion",
            popover: {
                title: "Conductor",
                description: "Ingresa bajo el nombre de quien esta a cargo la tripulación.",
                side: "top"
            }
        },
        {
            element: "#descansos",
            popover: {
                title: "Descansos",
                description: "Especifica los descansos aplicables al nuevo turno.",
                side: "top"
            }
        },
        {
            element: "#dias_adicionales",
            popover: {
                title: "Días adicionales",
                description: "Indica si hay días adicionales que aplicar.",
                side: "top"
            }
        },
        {
            element: "#horario_adicional",
            popover: {
                title: "Horario adicional",
                description: "Especifica horarios adicionales si aplica.",
                side: "top"
            }
        },
        {
            element: "#btnGuardar",
            popover: {
                title: "Guardar",
                description: "Haz clic aquí para guardar el cambio temporal de turno.",
                side: "top",
                popoverClass: "popover-importante"
            }
        },
        {
            element: "#btnAyudaModal",
            popover: {
                title: "Botón de ayuda",
                description: "Si necesitas repasar cómo llenar este formulario, presiona este botón para repetir el tutorial.",
                side: "left"
            }
        }
    ];

    // -------------------------------
    // Instancia única compartida
    // -------------------------------
    let driverObj = null;

    function launchDriver(pasos) {
        if (driverObj) {
            driverObj.destroy();
        }

        driverObj = driver({
            showProgress: true,
            allowClose: false,
            disableInteraction: true,
            progressText: "Paso {{current}} de {{total}}",
            doneBtnText: "Finalizar",
            nextBtnText: "Siguiente",
            prevBtnText: "Atrás",
            steps: pasos
        });

        driverObj.drive();
    }

    // -------------------------------
    // Tutorial vista principal
    // -------------------------------
    const tutorialKey = "tutorial_tiempoextra";
    if (!localStorage.getItem(tutorialKey)) {
        launchDriver(steps);
        localStorage.setItem(tutorialKey, "true");
    }

    const btnAyuda = document.getElementById("btnAyuda");
    if (btnAyuda) {
        btnAyuda.addEventListener("click", () => launchDriver(steps));
    }

    // -------------------------------
    // Tutorial modal cambio de turno
    // -------------------------------
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

    const btnAyudaModal = document.getElementById("btnAyudaModal");
    if (btnAyudaModal) {
        btnAyudaModal.addEventListener("click", () => launchDriver(stepsModal));
    }
    document.getElementById("btnAyudaModal")?.addEventListener("click", () => launchDriver(stepsModal));
});

// ─────────────────────────────────────────────────────────────────────────────
// OTROS EVENTOS
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById("noemp").addEventListener("keyup", function () {
    Tiempoextra.getinfoemp(this.value);
});

document.getElementById("departamento").addEventListener("change", function () {
    const nombreDepto = this.value.trim();
    if (nombreDepto === '') { document.getElementById('maquinas').innerHTML = ''; return; }
    const selectEnc = document.getElementById("departamentoenc");
    let idDepto = null;
    for (let i = 0; i < selectEnc.options.length; i++) {
        if (selectEnc.options[i].text.trim() === nombreDepto) { idDepto = selectEnc.options[i].value; break; }
    }
    if (!idDepto) { document.getElementById('maquinas').innerHTML = ''; return; }
    const Tools = new Toolsjs();
    Tools.llnarslc('CatalogoPersonal', 'GetSlcMaquinasxdep&departamento=' + idDepto, 'maquinas', 0);
});

document.getElementById("fechainput").addEventListener("change", function () {
    const motivoNum = parseInt(document.getElementById("motivos").value);
    if ([5, 8].includes(motivoNum)) return;
    Tiempoextra.getinfohoraentradaysalida(document.getElementById("noemp").value, this.value);
});

document.getElementById("creapdf").addEventListener("click", function (event) {
    event.preventDefault();
    let folio = document.getElementById("folio").value;
    if (folio === "") { Swal.fire('UPS!!!', 'No hay un folio creado', 'info'); return false; }
    window.open("./pdf/reporte.php?folio=" + btoa(folio));
});

document.getElementById("btnverfolio").addEventListener("click", function (event) {
    event.preventDefault();
    let valortxt = document.getElementById("txtview").textContent;
    document.getElementById("txtview").innerHTML = valortxt === "Cerrar" ? "Ver Folio" : "Cerrar";
});

document.getElementById("consultar").addEventListener("click", function () {
    window.open("../Tiempoextra/Autorizatp.php");
});

document.getElementById("btnGuardar").addEventListener("click", async function () {
    let folio             = document.getElementById("folio").value.trim();
    let hora_presentacion = document.getElementById("hora_presentacion").value.trim();
    let horario_desde     = document.getElementById("horario_desde").value.trim();
    let horario_hasta     = document.getElementById("horario_hasta").value.trim();
    let hasta_tripulacion = document.getElementById("hasta_tripulacion").value.trim();
    let folioTE           = document.getElementById("folioTiempoExtra").value.trim();

    if (folio === "" || hora_presentacion === "" || horario_desde === "" || horario_hasta === "" || hasta_tripulacion === "") {
        Swal.fire('Error', 'Debes de ingresar todos los campos', 'error'); return;
    }
    if (folioTE === "") { Swal.fire('Error', 'El folio no está asociado a algún tiempo extra', 'error'); return; }

    const form = document.getElementById("formCambio");
    const data = new FormData(form);
    const response = await fetch("php/index.php?guardarCambioTurno&folio=" + folio, { method: "POST", body: data });
    const result   = await response.json();

    if (result.success) {
        Tiempoextra.tblsubenc();
        Swal.fire('Guardado', 'El cambio temporal de turno fue registrado', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalOverlay')).hide();
        form.reset();
    } else {
        Swal.fire('Error', 'No se pudo guardar', 'error');
    }
});

document.querySelector("#formtiempoextra").addEventListener("submit", e => { e.preventDefault(); });
document.querySelector("#formCambio").addEventListener("submit", e => { e.preventDefault(); });

function verPDF(id) { window.open("./pdf/cambio_turno.php?id=" + btoa(id), "_blank"); }
window.verPDF = verPDF;

function abrirModal() {
    const hoy        = new Date();
    const fechaLocal = hoy.getFullYear() + "-" + String(hoy.getMonth()+1).padStart(2,'0') + "-" + String(hoy.getDate()).padStart(2,'0');
    document.getElementById('fecha_emision').value = fechaLocal;
    new bootstrap.Modal(document.getElementById('modalOverlay')).show();
}

window.editEnc         = id => Tiempoextra.editenc(id);
window.deleteSub       = id => Tiempoextra.deletesub(id);
window.cambioTempTurno = (noemp, estadoTurno, nombre, depto, id) => Tiempoextra.cambioTempTurno(noemp, estadoTurno, nombre, depto, id);
window.enviarEnc       = id => Tiempoextra.enviar(id);
window.pdfFin          = id => Tiempoextra.pdffin(id);