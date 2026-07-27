import { Toolsjs } from "../../Tools/Tools.js";
// INCLUSION DEL SEGUNDO DE 13 HORAS Y TERCERO DE 12 HORAS Y MIXTO 4
const Tools = new Toolsjs();
class Reportes {
    constructor() {
        this.data = [];
    }

    // Obtener hora de entrada y de salida segun el numero de empleado
    async getinfohoraentradaysalida(noemp, date, folioRegistro, HoraInicio) {
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

    const btnEliminar = document.getElementById("btnEliminar");
    const lblMensaje = document.getElementById("lblMensaje");

    if (respuestaActual.length === 0) {
        lblMensaje.hidden = false;
        Swal.fire({
            title: 'Resultados de validación:',
            html: `
                Se recomienda eliminar este registro de empleado<br><br>
                <b>No hay registros de horarios checados para el empleado en el dia especificado. Si crees que es un error comunicate con el departamento de Nominas</b><br>
            `,
            icon: 'info'
        });
        const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);
        if (btnEliminarFila) btnEliminarFila.hidden = false;
        return;
    }

    function agruparPorHora(registros) {
        var horasVistas = {};
        var resultado = [];
        registros.forEach(function (registro) {
            if (registro.fecha_h) {
                var horaSinSegundos = registro.fecha_h.substring(0, 8);
                if (!horasVistas[horaSinSegundos]) {
                    horasVistas[horaSinSegundos] = true;
                    resultado.push({
                        fecha_h: registro.fecha_h,
                        hora_limpia: horaSinSegundos
                    });
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

    // ─────────────────────────────────────────────────────────────
    // DEFINICIÓN DE TURNOS
    // ─────────────────────────────────────────────────────────────
    var turnos = {
        turno1: {
            entrada: { min: 6 * 60 + 30, max: 7 * 60 + 30 },
            salida:  { min: 13 * 60 + 25, max: 20 * 60 },
            horaIdealEntrada: "07:00:00",
            horaIdealSalida:  "15:00:00"
        },
        turno2: {
            entrada: { min: 14 * 60 + 30, max: 15 * 60 + 30 },
            salida:  { min: 21 * 60, max: 23 * 60 },
            horaIdealEntrada: "15:00:00",
            horaIdealSalida:  "22:30:00"
        },
        // ── Turno 2 de 13 hrs ─────────────────────────────────────
        // Entrada: 10:15–10:45  (±15 min alrededor de 10:30)
        // Salida:  21:00–23:00  (igual que turno2 normal, sale ~22:30)
        turno2_13hrs: {
            entrada: { min: 10 * 60 + 15, max: 10 * 60 + 45 },
            salida:  { min: 21 * 60,       max: 23 * 60 },
            horaIdealEntrada: "10:30:00",
            horaIdealSalida:  "22:30:00"
        },
        turno3: {
            entrada: { min: 22 * 60, max: 23 * 60 },
            salida:  { min: 6 * 60 + 25, max: 7 * 60 + 25 },
            horaIdealEntrada: "22:30:00",
            horaIdealSalida:  "07:00:00"
        },
        turno3_12hrs: {
            entrada: { min: 18 * 60 + 50, max: 19 * 60 + 10 },
            salida:  { min: 6 * 60 + 55,  max: 7 * 60 + 5 },
            horaIdealEntrada: "19:00:00",
            horaIdealSalida:  "07:00:00"
        },
        mixto1: {
            entrada: { min: 7 * 60 + 20, max: 8 * 60 },
            salida:  { min: 15 * 60 + 30, max: 19 * 60 },
            horaIdealEntrada: "07:30:00",
            horaIdealSalida:  "17:00:00"
        },
        mixto2: {
            entrada: { min: 8 * 60 + 20, max: 9 * 60 },
            salida:  { min: 18 * 60,     max: 20 * 60 },
            horaIdealEntrada: "08:30:00",
            horaIdealSalida:  "18:30:00"
        },
        mixto3: {
            entrada: { min: 6 * 60 + 20, max: 7 * 60 + 20 },
            salida:  { min: 16 * 60,     max: 17 * 60 },
            horaIdealEntrada: "07:00:00",
            horaIdealSalida:  "16:30:00"
        },
        // ── NUEVO: Mixto 4 ────────────────────────────────────────
        // Entrada: 06:45–07:15 (±15 min, margen estrecho para NO
        //   solapar mixto1 que empieza a 07:20 y mixto3 que llega
        //   hasta 07:20 también).
        // Salida:  16:30–17:30 (~17:00).
        //   La salida a las 17:00 lo diferencia de turno1 (15:00)
        //   y de mixto3 (max 17:00 pero ideal 16:30).
        // El combo entrada+salida es lo que lo identifica de forma
        // unívoca frente a sus vecinos.
        mixto4: {
            entrada: { min: 6 * 60 + 45, max: 7 * 60 + 15 },
            salida:  { min: 16 * 60 + 30, max: 17 * 60 + 30 },
            horaIdealEntrada: "07:00:00",
            horaIdealSalida:  "17:00:00"
        }
    };

    function estaEnRango(horaMin, rango) {
        return horaMin >= rango.min && horaMin <= rango.max;
    }

    function identificarTurno(horaEntradaMin, horaSalidaMin) {
        for (var nombreTurno in turnos) {
            var turno = turnos[nombreTurno];
            if (estaEnRango(horaEntradaMin, turno.entrada) && estaEnRango(horaSalidaMin, turno.salida)) {
                return nombreTurno;
            }
        }
        return null;
    }

    function analizarCasoEspecial(registrosLimpios) {
        if (registrosLimpios.length) {
            var horas = registrosLimpios.map(r => ({
                hora: r.hora_limpia,
                minutos: horaAMinutos(r.hora_limpia)
            }));
            var gaps = [];
            for (var i = 1; i < horas.length; i++) {
                gaps.push({ minutos: horas[i].minutos - horas[i - 1].minutos });
            }

            // Doble turno: un gap largo entre 8 y 10 horas
            var hayGapLargo = gaps.some(g => g.minutos >= 8 * 60 && g.minutos <= 10 * 60);
            if (hayGapLargo) {
                return {
                    tipo: "doble_turno",
                    entrada: registrosLimpios[0],
                    salida: registrosLimpios[registrosLimpios.length - 1]
                };
            }

            // Todos juntos (menos de 30 min entre todos): múltiples intentos simples
            var todosJuntos = gaps.every(g => g.minutos < 30);
            if (todosJuntos) {
                return {
                    tipo: "multiples_intentos",
                    entrada: registrosLimpios[0],
                    salida: registrosLimpios[registrosLimpios.length - 1]
                };
            }

            // Patrón de grupos separados por un gap largo (>10 hrs)
            var hayGapMuyLargo = gaps.some(g => g.minutos > 10 * 60);
            if (hayGapMuyLargo) {
                var indiceGapGrande = gaps.findIndex(g => g.minutos > 10 * 60);
                var gapsAntes   = gaps.slice(0, indiceGapGrande);
                var gapsDespues = gaps.slice(indiceGapGrande + 1);
                var antesJuntos   = gapsAntes.every(g => g.minutos < 30);
                var despuesJuntos = gapsDespues.every(g => g.minutos < 30);

                if (antesJuntos && despuesJuntos) {
                    return {
                        tipo: "turno_con_grupos",
                        entrada: registrosLimpios[0],
                        salida: registrosLimpios[registrosLimpios.length - 1]
                    };
                }
            }

            return {
                tipo: "desconocido",
                entrada: registrosLimpios[0],
                salida: registrosLimpios[registrosLimpios.length - 1]
            };
        }
        return null;
    }

    var entrada, salida, turnoDetectado;

    var casoEspecial = analizarCasoEspecial(registrosActualLimpios);

    if (casoEspecial) {
        var ultimaHoraCasoMin = horaAMinutos(casoEspecial.salida.hora_limpia);

        // ── PRIORIDAD 1: turno3_12hrs (entrada nocturna ~19:00) ───
        if (estaEnRango(ultimaHoraCasoMin, turnos.turno3_12hrs.entrada)) {
            entrada = casoEspecial.salida;
            if (registrosSiguienteLimpios.length > 0) {
                salida = registrosSiguienteLimpios[0];
                turnoDetectado = "turno3_12hrs";
            } else {
                return;
            }

        // ── PRIORIDAD 2: turno2_13hrs (entrada matutina ~10:30) ───
        } else if (estaEnRango(casoEspecial.entrada.hora_limpia
                    ? horaAMinutos(casoEspecial.entrada.hora_limpia) : -1,
                    turnos.turno2_13hrs.entrada)) {
            entrada = casoEspecial.entrada;
            salida  = casoEspecial.salida;
            turnoDetectado = "turno2_13hrs";

        // ── PRIORIDAD 3: mixto4 (entrada ~07:00, salida ~17:00) ───
        } else if (estaEnRango(casoEspecial.entrada.hora_limpia
                    ? horaAMinutos(casoEspecial.entrada.hora_limpia) : -1,
                    turnos.mixto4.entrada) &&
                   estaEnRango(horaAMinutos(casoEspecial.salida.hora_limpia),
                    turnos.mixto4.salida)) {
            entrada = casoEspecial.entrada;
            salida  = casoEspecial.salida;
            turnoDetectado = "mixto4";

        } else {
            entrada = casoEspecial.entrada;
            salida  = casoEspecial.salida;
        }

    } else if (registrosActualLimpios.length >= 2) {

        var primeraHoraActual = registrosActualLimpios[0];
        var ultimaHoraActual  = registrosActualLimpios[registrosActualLimpios.length - 1];
        var primeraMin = horaAMinutos(primeraHoraActual.hora_limpia);
        var ultimaMin  = horaAMinutos(ultimaHoraActual.hora_limpia);

        turnoDetectado = identificarTurno(primeraMin, ultimaMin);

        if (turnoDetectado) {
            entrada = primeraHoraActual;
            salida  = ultimaHoraActual;
        } else {
            var segundaMin = ultimaMin;

            // ── turno3_12hrs tiene prioridad sobre turno3 ─────────
            if (estaEnRango(segundaMin, turnos.turno3_12hrs.entrada)) {
                entrada = ultimaHoraActual;
                turnoDetectado = "turno3_12hrs";
                if (registrosSiguienteLimpios.length > 0) {
                    salida = registrosSiguienteLimpios[0];
                } else {
                    return;
                }

            // ── turno2_13hrs ──────────────────────────────────────
            } else if (estaEnRango(primeraMin, turnos.turno2_13hrs.entrada)) {
                entrada = primeraHoraActual;
                salida  = ultimaHoraActual;
                turnoDetectado = "turno2_13hrs";

            // ── mixto4 (entrada 06:45–07:15, salida 16:30–17:30) ──
            } else if (estaEnRango(primeraMin, turnos.mixto4.entrada) && estaEnRango(ultimaMin, turnos.mixto4.salida)) {
                entrada = primeraHoraActual;
                salida  = ultimaHoraActual;
                turnoDetectado = "mixto4";

            } else if (estaEnRango(segundaMin, turnos.turno3.entrada) || estaEnRango(segundaMin, turnos.mixto3.salida)) {
                entrada = ultimaHoraActual;
                if (registrosSiguienteLimpios.length > 0) {
                    salida = registrosSiguienteLimpios[0];
                } else {
                    return;
                }
            } else {
                return;
            }
        }

    } else if (registrosActualLimpios.length === 1) {

        var unicaHoraActual = registrosActualLimpios[0];
        var unicaMin = horaAMinutos(unicaHoraActual.hora_limpia);

        // -- turno3_12hrs primero ----------------------------------
        if (estaEnRango(unicaMin, turnos.turno3_12hrs.entrada)) {
            entrada = unicaHoraActual;
            turnoDetectado = "turno3_12hrs";
            if (registrosSiguienteLimpios.length > 0) {
                salida = registrosSiguienteLimpios[0];
            } else {
                return;
            }
       
        // -- turno2_13hrs ------------------------------------------
        } else if (estaEnRango(unicaMin, turnos.turno2_13hrs.entrada)) {
            lblMensaje.hidden = false;
            Swal.fire({
                title: 'Resultados de validación:',
                html: `
                    Se recomienda eliminar este registro de empleado<br><br>
                    <b>Solo se detectó la entrada del turno 2 de 12 hrs. Si lo deseas, consulta más tarde para verificar si el registro de salida fue realizado más tarde. Si crees que es un error comunicate con el departamento de Nominas</b><br>                    
                `,
                icon: 'info'
            });
            lblMensaje.className = "alert alert-warning mt-2";
            lblMensaje.style.display = "block";
            const btnEliminarFila2_13 = document.getElementById(`btnEliminar-${folioRegistro}`);
            if (btnEliminarFila2_13) btnEliminarFila2_13.hidden = false;
            return;

        } else if (estaEnRango(unicaMin, turnos.turno3.entrada) || estaEnRango(unicaMin, turnos.mixto3.entrada)) {
            entrada = unicaHoraActual;
            if (registrosSiguienteLimpios.length > 0) {
                salida = registrosSiguienteLimpios[0];
            } else {
                return;
            }
        } else if (estaEnRango(unicaMin, turnos.turno3.salida) || estaEnRango(unicaMin, turnos.mixto3.salida)) {
            salida = unicaHoraActual;
            if (registrosAnteriorLimpios.length > 0) {
                entrada = registrosAnteriorLimpios[registrosAnteriorLimpios.length - 1];
            } else {
                return;
            }
        } else {
            return;
        }
    }

    if (!entrada || !salida) {
        return;
    }
   
    if (registrosActualLimpios.length > 0) {
        if (registrosActualLimpios.length >= 2) {
            var primeraHoraActual = registrosActualLimpios[0];
            var ultimaHoraActual  = registrosActualLimpios[registrosActualLimpios.length - 1];
            var primeraMin = horaAMinutos(primeraHoraActual.hora_limpia);
            var ultimaMin  = horaAMinutos(ultimaHoraActual.hora_limpia);
            var turnoDetectadoBloque = identificarTurno(primeraMin, ultimaMin);

            if (turnoDetectadoBloque) {
                // No sobreescribir si ya detectamos turno3_12hrs, turno2_13hrs o mixto4
                if (turnoDetectado !== "turno3_12hrs" && turnoDetectado !== "turno2_13hrs" && turnoDetectado !== "mixto4") {
                    entrada = primeraHoraActual;
                    salida  = ultimaHoraActual;
                    turnoDetectado = turnoDetectadoBloque;
                }
            } else {
                var segundaMin = ultimaMin;
                if (estaEnRango(segundaMin, turnos.turno3_12hrs.entrada)) {
                    if (turnoDetectado !== "turno3_12hrs" && turnoDetectado !== "turno2_13hrs" && turnoDetectado !== "mixto4") {
                        entrada = ultimaHoraActual;
                        turnoDetectado = "turno3_12hrs";
                        if (registrosSiguienteLimpios.length > 0) {
                            salida = registrosSiguienteLimpios[0];
                        }
                    }
                } else if (estaEnRango(primeraMin, turnos.turno2_13hrs.entrada)) {
                    if (turnoDetectado !== "turno3_12hrs" && turnoDetectado !== "turno2_13hrs" && turnoDetectado !== "mixto4") {
                        entrada = primeraHoraActual;
                        salida  = ultimaHoraActual;
                        turnoDetectado = "turno2_13hrs";
                    }
                // ── NUEVO: mixto4 ──────────────────────────────────
                } else if (estaEnRango(primeraMin, turnos.mixto4.entrada) && estaEnRango(ultimaMin, turnos.mixto4.salida)) {
                    if (turnoDetectado !== "turno3_12hrs" && turnoDetectado !== "turno2_13hrs" && turnoDetectado !== "mixto4") {
                        entrada = primeraHoraActual;
                        salida  = ultimaHoraActual;
                        turnoDetectado = "mixto4";
                    }
                } else if (estaEnRango(segundaMin, turnos.turno3.entrada) || estaEnRango(segundaMin, turnos.mixto3.entrada)) {
                    if (turnoDetectado !== "turno3_12hrs" && turnoDetectado !== "turno2_13hrs" && turnoDetectado !== "mixto4") {
                        entrada = ultimaHoraActual;
                        if (registrosSiguienteLimpios.length > 0) {
                            salida = registrosSiguienteLimpios[0];
                        }
                    }
                }
            }
        } else {
            var unicaHoraActual = registrosActualLimpios[0];
            var unicaMin = horaAMinutos(unicaHoraActual.hora_limpia);

            if (estaEnRango(unicaMin, turnos.turno3_12hrs.entrada)) {
                if (turnoDetectado !== "turno3_12hrs" && turnoDetectado !== "turno2_13hrs" && turnoDetectado !== "mixto4") {
                    entrada = unicaHoraActual;
                    turnoDetectado = "turno3_12hrs";
                    if (registrosSiguienteLimpios.length > 0) {
                        salida = registrosSiguienteLimpios[0];
                    }
                }
            } else if (estaEnRango(unicaMin, turnos.turno3.entrada) || estaEnRango(unicaMin, turnos.mixto3.entrada)) {
                if (turnoDetectado !== "turno3_12hrs" && turnoDetectado !== "turno2_13hrs" && turnoDetectado !== "mixto4") {
                    entrada = unicaHoraActual;
                    if (registrosSiguienteLimpios.length > 0) {
                        salida = registrosSiguienteLimpios[0];
                    }
                }
            } else if (estaEnRango(unicaMin, turnos.turno3.salida) || estaEnRango(unicaMin, turnos.mixto3.salida)) {
                if (turnoDetectado !== "turno3_12hrs" && turnoDetectado !== "turno2_13hrs" && turnoDetectado !== "mixto4") {
                    salida = unicaHoraActual;
                    if (registrosAnteriorLimpios.length > 0) {
                        entrada = registrosAnteriorLimpios[registrosAnteriorLimpios.length - 1];
                    }
                }
            }
        }
    }

    if (!entrada || !entrada.fecha_h || !salida || !salida.fecha_h) {
        btnEliminar.hidden = true;
        lblMensaje.hidden = false;
        lblMensaje.textContent = "No se pudo determinar entrada y salida correctamente.";
        Swal.fire({
            title: 'Resultados de validación:',
            html: `
                Se recomienda eliminar este registro de empleado<br><br>
                <b>No se pudo determinar entrada y salida correctamente. O si lo deseas consulta mas tarde para verificar si su registro fue realizado mas tarde. Si crees que es un error comunicate con el departamento de Nominas</b><br>
            `,
            icon: 'info'
        });
        lblMensaje.className = "alert alert-warning mt-2";
        lblMensaje.style.display = "block";
        const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);
        if (btnEliminarFila) btnEliminarFila.hidden = false;
        return;
    }

    var horaentrada = entrada.hora_limpia;
    var horasalida  = salida.hora_limpia;

    const fechar = new Date(date + "T00:00:00");
    var esSabado = fechar.getDay() === 6;

    var resultado = calcularTurnoYHoras(horaentrada, horasalida, esSabado);

    const turnonom = resultado.turno.split(/[\s,;()\-\.]+/);
    var turnoCompleto = turnonom[0] + " " + turnonom[1];

    if (turnonom[3] == undefined) {
        lblMensaje.hidden = false;
        lblMensaje.textContent = "No se pudo determinar entrada y salida correctamente.";
        Swal.fire({
            title: 'Resultados de validación:',
            html: `
                Se recomienda eliminar este registro de empleado<br><br>
                <b>No se pudo determinar entrada y salida correctamente. O si lo deseas consulta mas tarde para verificar si su registro fue realizado mas tarde. Si crees que es un error comunicate con el departamento de Nominas</b><br>
            `,
            icon: 'info'
        });
        lblMensaje.className = "alert alert-warning mt-2";
        lblMensaje.style.display = "block";
        const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);
        if (btnEliminarFila) btnEliminarFila.hidden = false;
        return;
    }

    function horaAMinutosLocal(hora) {
        const partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }

    function minutosAHora(minutos) {
        const horas = Math.floor(minutos / 60);
        const mins  = minutos % 60;
        return horas.toString().padStart(2, '0') + ":" + mins.toString().padStart(2, '0');
    }

    let horaExtra2 = "00:00:00";
    const comp = "00:05:00";

    if (resultado.totalHoras <= comp) {
        lblMensaje.hidden = false;
        lblMensaje.textContent = "Solo se tiene un registro de entrada/salida para el empleado.";
        Swal.fire({
            title: 'Resultados de validación:',
            html: `
                Se recomienda eliminar este registro de empleado<br><br>
                <b>Solo se tiene un registro de entrada/salida para el empleado. Si lo deseas, consulta mas tarde para verificar si el registro de salida fue realizada mas tarde. . Si crees que es un error comunicate con el departamento de Nominas</b><br>
            `,
            icon: 'info'
        });
        lblMensaje.className = "alert alert-warning mt-2";
        lblMensaje.style.display = "block";
        const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);
        if (btnEliminarFila) btnEliminarFila.hidden = false;
        return;
    }

    const horaExtrass  = horaAMinutosLocal(resultado.totalHoras);
    const horaReglamen = horaAMinutosLocal(resultado.horasReglamentarias);

    if (horaExtrass >= horaReglamen) {
        const diff2 = horaExtrass - horaReglamen;
        horaExtra2 = minutosAHora(diff2);
    }

    validarTiempoExtra(horaExtra2, resultado.totalHoras, resultado.horasReglamentarias, folioRegistro, horasalida, horaExtra2, HoraInicio, turnoDetectado);
    this.evaluarFolio(this.data.find(e => e.folioRegistro == folioRegistro).id);
}

    // Metodos predifinidos en caso de que la fecha haya llegado y se deba de calcular los datos o analizarlos
    async consulta() {
        const respuetaraw = await fetch("php/index.php?tblautorizatp");
        const respuesta = await respuetaraw.json();

        if (Array.isArray(respuesta)) {
            this.data = respuesta;
            let body = "";
            const folios = new Set();

            respuesta.forEach(elemento => {
                folios.add(elemento.id);
                body += this.renderRow(elemento);
            });

            document.getElementById("tblenc").innerHTML = body;

            const accionesContainer = document.getElementById("accionesGlobales");
            accionesContainer.innerHTML = "";
            folios.forEach(folioId => {
                accionesContainer.innerHTML += `
                    <div class="mb-3" id="acciones-folio-${folioId}">
                        <button class="btn btn-success" id="btnAutorizar-${folioId}" hidden>
                            Autorizar TODO el Folio ${folioId}
                        </button>
                        <button class="btn btn-danger" id="btnRechazar-${folioId}" hidden>
                            Rechazar TODO el Folio ${folioId}
                        </button>
                    </div>
                `;
            });

            folios.forEach(folioId => {
                const btnAutorizar = document.getElementById(`btnAutorizar-${folioId}`);
                const btnRechazar  = document.getElementById(`btnRechazar-${folioId}`);

                if (btnAutorizar) {
                    btnAutorizar.addEventListener("click", () => { this.enviar(folioId, 1); });
                }
                if (btnRechazar) {
                    btnRechazar.addEventListener("click", () => { this.enviar(folioId, 2); });
                }
            });

            const ahora = new Date();
            respuesta.forEach(elemento => {
                const fechaSol = new Date(elemento.fechaSol + "T00:00:00");
                const [h, m, s] = elemento.HoraFin.split(":").map(Number);
                const horaFinDate = new Date(fechaSol);
                horaFinDate.setHours(h, m, s);

                const btnValidar = document.getElementById(`btnValidar-${elemento.folioRegistro}`);
                const msg = document.getElementById(`msg-${elemento.folioRegistro}`);
                const horaFinMasMargen = new Date(horaFinDate.getTime() + 5 * 60000);

                if (ahora < fechaSol) {
                    btnValidar.hidden = true;
                    msg.textContent = `El día solicitado (${elemento.fechaSol}) aún no ha llegado para hacer el cálculo correspondiente.`;
                    msg.className = "alert alert-warning p-1 mt-1";
                } else if (ahora.toDateString() === fechaSol.toDateString() && ahora < (horaFinMasMargen)) {
                    btnValidar.hidden = true;
                    msg.textContent = `Aún no es momento de calcular. El botón se habilitará a las ${horaFinMasMargen.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} hrs.`;
                    msg.className = "alert alert-warning p-1 mt-1";
                } else if (ahora >= horaFinMasMargen) {
                    msg.textContent = "";
                    msg.dataset.estado = "pendiente";
                }
            });
        }
    }

    renderRow(elemento) {
        let accionHtml = "";
        let accionTerminadoHtml = "";
        let badgeValidado = "";

        if (elemento.terminado === null || elemento.terminado === "") {
            if (elemento.validado == null || elemento.validado == 0) {
                accionHtml = `
                    <button class="btn btn-sm btn-warning"
                            onclick="validarInfo('${elemento.NoEmpleadoSol}','${elemento.fechaSol}','${elemento.folioRegistro}','${elemento.HoraInicio}')"
                            id="btnValidar-${elemento.folioRegistro}">
                        <i class="fa-solid fa-eye"></i> Validar T. extra
                    </button>
                    <button class="btn btn-sm btn-danger"
                            onclick="window.deletesub(${elemento.folioRegistro})"
                            id="btnEliminar-${elemento.folioRegistro}" hidden>
                        <i class="fas fa-times"></i> Eliminar
                    </button>
                `;
            } else {
                accionHtml = `<span class="badge bg-success">Solicitud validada</span>`;
            }
            accionTerminadoHtml = `<span class="badge bg-warning"> No procesada (Pendiente)</span>`;
        } else if (elemento.terminado == 1) {
            accionHtml = `<span class="badge bg-success">Aprobado</span>`;
            accionTerminadoHtml = `<span class="badge bg-success">Solicitud procesada (Aprobado)</span>`;
        } else if (elemento.terminado == 2) {
            accionHtml = `<span class="badge bg-danger">Rechazado</span>`;
            accionTerminadoHtml = `<span class="badge bg-danger">Solicitud procesada (Rechazado)</span>`;
        }

        if (elemento.validado === null || elemento.validado === 0) {
            badgeValidado = `<span class="badge bg-secondary">No validado</span>`;
        } else if (elemento.validado == 1) {
            badgeValidado = `<span class="badge bg-success">Validado</span>`;
        }

        return `<tr>
            <td>${elemento.folioRegistro}</td>
            <td>${elemento.id}</td>
            <td>${elemento.creado}</td>
            <td>${elemento.NoEmp}</td>
            <td>${elemento.SupervisorNombre}</td>
            <td>${elemento.NoEmpleadoSol}</td>
            <td>${elemento.fechaSol}</td>
            <td>${elemento.HoraInicio}</td>
            <td>${elemento.HoraFin}</td>
            <td>${elemento.NombreEmpleadoSol}</td>
            <td>${elemento.departamento}</td>
            <td>${accionTerminadoHtml}<br></td>
            <td>${badgeValidado}</td>
            <td>
                ${accionHtml}
                <small id="msg-${elemento.folioRegistro}" class="d-block mt-1"></small>
            </td>
        </tr>`;
    }

    filtrarPorFolio(folioId) {
        const filtrados = this.data.filter(e => e.id == folioId);
        let body = "";
        filtrados.forEach(e => body += this.renderRow(e));
        document.getElementById("tblenc").innerHTML = body;

        const accionesContainer = document.getElementById("accionesGlobales");
        accionesContainer.innerHTML = `
            <div class="mb-3" id="acciones-folio-${folioId}">
                <button class="btn btn-success" id="btnAutorizar-${folioId}" hidden>
                    Autorizar TODO el Folio ${folioId}
                </button>
                <button class="btn btn-danger" id="btnRechazar-${folioId}" hidden>
                    Rechazar TODO el Folio ${folioId}
                </button>
            </div>
        `;

        const btnAutorizar = document.getElementById(`btnAutorizar-${folioId}`);
        const btnRechazar  = document.getElementById(`btnRechazar-${folioId}`);

        if (btnAutorizar) {
            btnAutorizar.addEventListener("click", () => { this.enviar(folioId, 1); });
        }
        if (btnRechazar) {
            btnRechazar.addEventListener("click", () => { this.enviar(folioId, 2); });
        }

        const ahora = new Date();
        filtrados.forEach(elemento => {
            const fechaSol = new Date(elemento.fechaSol + "T00:00:00");
            const [h, m, s] = elemento.HoraFin.split(":").map(Number);
            const horaFinDate = new Date(fechaSol);
            horaFinDate.setHours(h, m, s);
            const horaFinMasMargen = new Date(horaFinDate.getTime() + 5 * 60000);

            const btnValidar = document.getElementById(`btnValidar-${elemento.folioRegistro}`);
            const msg = document.getElementById(`msg-${elemento.folioRegistro}`);

            if (ahora < fechaSol) {
                btnValidar.hidden = true;
                msg.textContent = `El día solicitado (${elemento.fechaSol}) aún no ha llegado.`;
                msg.className = "alert alert-warning p-1 mt-1";
                msg.dataset.estado = "pendiente";
            } else if (ahora.toDateString() === fechaSol.toDateString() && ahora < horaFinMasMargen) {
                btnValidar.hidden = true;
                msg.textContent = `Aún no es momento de calcular. El botón se habilitará a las ${horaFinMasMargen.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} hrs.`;
                msg.className = "alert alert-warning p-1 mt-1";
                msg.dataset.estado = "pendiente";
            } else if (ahora >= horaFinMasMargen) {
                msg.textContent = "";
                msg.dataset.estado = "pendiente";
            }
        });

        this.evaluarFolio(folioId);
    }

    async deletesub(id) {
        const data = new FormData();
        data.append("id", id);
        const respuestaraw = await fetch("./php/index.php?deletesub", {
            method: "POST",
            body: data
        });
        const respuesta = await respuestaraw.json();
        this.consulta();
        respuesta === "Listo"
            ? Swal.fire('Listo!!!', 'Registro eliminado', 'success')
            : Swal.fire('ERROR!!!', 'Hay un problema al eliminar', 'error');
    }

    async enviar(id, autor) {
        const verificacion = await fetch('./php/verificar_firma.php')
            .then(res => res.json())
            .catch(() => null);

        if (!verificacion || !verificacion.success) {
            Swal.fire({
                icon: 'warning',
                title: 'Firma no registrada',
                text: 'Debes registrar tu firma primero antes de autorizar el tiempo extra. Consulta a RI para el registro de tu firma digital.',
                confirmButtonText: 'Ententido',
                confirmButtonColor: '#f0ad4e'
            });
            return;
        }

        const respuestaraw = await fetch("./php/index.php?autorizafol&id=" + id + "&autor=" + autor);
        const respuesta = await respuestaraw.json();

        respuesta === false ?
            Swal.fire({ icon: 'error', title: 'Error', text: respuesta.message || 'Hay un error con la base de datos.' }) :
            Swal.fire({ icon: 'success', title: 'Autorizado', text: 'El registro fue autorizado con exito', timer: 2000, showConfirmButton: false });

        window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
        window.location.reload();
    }

    pdffin(id) {
        window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
    }

    marcarRegistroComoApto(folioRegistro) {
        const btnValidar = document.getElementById(`btnValidar-${folioRegistro}`);
        const msg = document.getElementById(`msg-${folioRegistro}`);
        if (btnValidar) btnValidar.hidden = true;
        if (msg) {
            msg.textContent = "Registro apto para tiempo extra.";
            msg.className = "alert alert-success p-1 mt-1";
            msg.dataset.estado = "apto";
        }
    }

    marcarRegistroComoNoApto(folioRegistro) {
        const btnValidar  = document.getElementById(`btnValidar-${folioRegistro}`);
        const btnEliminar = document.getElementById(`btnEliminar-${folioRegistro}`);
        const msg = document.getElementById(`msg-${folioRegistro}`);
        if (btnValidar)  btnValidar.hidden = true;
        if (btnEliminar) btnEliminar.hidden = false;
        if (msg) {
            msg.textContent = "Registro no apto, puede eliminarse.";
            msg.className = "alert alert-warning p-1 mt-1";
            msg.dataset.estado = "noapto";
        }
    }

    evaluarFolio(folioId) {
        const registros = this.data.filter(e => e.id == folioId);
        let todosAnalizados = true;
        let todosAptos = true;

        registros.forEach(e => {
            const msg = document.getElementById(`msg-${e.folioRegistro}`);
            const estado = msg ? msg.dataset.estado : null;
            if (estado !== "apto" && estado !== "noapto") todosAnalizados = false;
            if (estado === "noapto") todosAptos = false;
        });

        const btnAutorizar = document.getElementById(`btnAutorizar-${folioId}`);
        const btnRechazar  = document.getElementById(`btnRechazar-${folioId}`);

        if (todosAnalizados && todosAptos) {
            btnAutorizar.hidden = false;
            btnRechazar.hidden  = false;
        } else {
            btnAutorizar.hidden = true;
            btnRechazar.hidden  = true;
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// validarTiempoExtra
// ─────────────────────────────────────────────────────────────────────────────
function validarTiempoExtra(horasExtra, totalHoras, horasReglamentarias, folioRegistro, horasalida, horasExtra2, HoraInicio, turnoDetectado) {
    function horaAMinutos(hora) {
        const partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }

    function sumarHoras(horaInicio, horasExtra) {
        const [h1, m1] = horaInicio.split(":").map(Number);
        const [h2, m2] = horasExtra.split(":").map(Number);
        let totalMin = h1 * 60 + m1 + h2 * 60 + m2;
        totalMin = totalMin % (24 * 60);
        const horas = Math.floor(totalMin / 60);
        const mins  = totalMin % 60;
        return horas.toString().padStart(2, '0') + ":" + mins.toString().padStart(2, '0') + ":00";
    }

    const nuevaHoraFin = sumarHoras(HoraInicio, horasExtra2);

    const minutosExtra = horaAMinutos(horasExtra);
    const minutosTotal = horaAMinutos(totalHoras);
    const minutosReglamentarios = horaAMinutos(horasReglamentarias);

    const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);

    // ── CASO: turno2_13hrs ────────────────────────────────────────────────────
    if (turnoDetectado === "turno2_13hrs") {
        window.Reportes.marcarRegistroComoApto(folioRegistro);

        const horaFinExtra2_13 = "16:30:00";

        Swal.fire({
            title: 'Resultados de validación:',
            html: `
                Se detectó un <b>Turno 2 de 13 hrs</b> válido<br><br>
                Hora de Inicio de T. extra: <b>${HoraInicio}</b><br>
                Hora de Entrada al turno: <b>10:30</b><br>
                Hora de Fin de T. extra ajustada: <b>${horaFinExtra2_13}</b><br>
            `,
            icon: 'info'
        }).then(() => {
            const data = new FormData();
            data.append("folioRegistro", folioRegistro);
            data.append("nuevaHoraFin", horaFinExtra2_13);

            fetch("php/index.php?actualizarHoraFin", {
                method: "POST",
                body: data
            })
            .then(r => r.json())
            .then(respuesta => {
                if (respuesta.success) {
                    const registro = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
                    if (registro) {
                        registro.HoraFin  = horaFinExtra2_13;
                        registro.validado = 1;
                    }

                    const fila = document.querySelector(`#msg-${folioRegistro}`).closest("tr");
                    if (fila) {
                        const estatusCell = fila.querySelector("td:nth-child(13)");
                        if (estatusCell) {
                            estatusCell.innerHTML = registro && registro.validado == 1
                                ? `<span class="badge bg-success">Validado</span>`
                                : `<span class="badge bg-warning">No validado aun</span>`;
                        }

                        const accionesCell = fila.querySelector("td:nth-child(14)");
                        if (accionesCell) {
                            accionesCell.innerHTML = registro && registro.validado == 1
                                ? `<span class="badge bg-success">Ya validado</span>
                                   <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`
                                : `<button class="btn btn-sm btn-warning"
                                           onclick="validarInfo('${registro.NoEmpleadoSol}','${registro.fechaSol}','${registro.folioRegistro}','${registro.HoraInicio}')"
                                           id="btnValidar-${registro.folioRegistro}">
                                       <i class="fa-solid fa-eye"></i> Validar T. extra
                                   </button>
                                   <button class="btn btn-sm btn-danger"
                                           onclick="window.deletesub(${registro.folioRegistro})"
                                           id="btnEliminar-${registro.folioRegistro}" hidden>
                                       <i class="fas fa-times"></i> Eliminar
                                   </button>
                                   <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`;
                        }
                    }

                    Swal.fire(
                        'Actualización realizada',
                        `La Hora Final de T. extra se ajustó a ${horaFinExtra2_13} y el registro quedó marcado como validado.`,
                        'success'
                    ).then(() => {
                        const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro).id;
                        window.Reportes.evaluarFolio(folioId);
                    });
                } else {
                    Swal.fire('Error en actualización', `No se pudo actualizar la Hora Fin. Detalle: ${respuesta.error}`, 'error');
                }
            })
            .catch(err => { console.error("Error al actualizar Hora Fin:", err); });
        });

        if (btnEliminarFila) btnEliminarFila.hidden = true;

    // ── CASO: turno3_12hrs ────────────────────────────────────────────────────
    } else if (turnoDetectado === "turno3_12hrs") {
        window.Reportes.marcarRegistroComoApto(folioRegistro);

        Swal.fire({
            title: 'Resultados de validación:',
            html: `
                Se detectó un <b>turno 3ro de 12 horas</b> válido<br><br>
                Hora de Inicio de T. extra: <b>${HoraInicio}</b><br>
                Horas de Salida de turno: <b>${horasalida}</b><br>
            `,
            icon: 'info'
        }).then(() => {
            if (horasalida !== nuevaHoraFin) {
                const data = new FormData();
                data.append("folioRegistro", folioRegistro);

                fetch("php/index.php?actualizarEstadoValidado", {
                    method: "POST",
                    body: data
                })
                .then(r => r.json())
                .then(respuesta => {
                    if (respuesta.success) {
                        const registro = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
                        if (registro) {
                            registro.HoraFin  = nuevaHoraFin;
                            registro.validado = 1;
                        }

                        const fila = document.querySelector(`#msg-${folioRegistro}`).closest("tr");
                        if (fila) {
                            const estatusCell = fila.querySelector("td:nth-child(13)");
                            if (estatusCell) {
                                estatusCell.innerHTML = registro && registro.validado == 1
                                    ? `<span class="badge bg-success">Validado</span>`
                                    : `<span class="badge bg-warning">No validado aun</span>`;
                            }

                            const accionesCell = fila.querySelector("td:nth-child(14)");
                            if (accionesCell) {
                                accionesCell.innerHTML = registro && registro.validado == 1
                                    ? `<span class="badge bg-success">Ya validado</span>
                                       <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`
                                    : `<button class="btn btn-sm btn-warning"
                                               onclick="validarInfo('${registro.NoEmpleadoSol}','${registro.fechaSol}','${registro.folioRegistro}','${registro.HoraInicio}')"
                                               id="btnValidar-${registro.folioRegistro}">
                                           <i class="fa-solid fa-eye"></i> Validar T. extra
                                       </button>
                                       <button class="btn btn-sm btn-danger"
                                               onclick="window.deletesub(${registro.folioRegistro})"
                                               id="btnEliminar-${registro.folioRegistro}" hidden>
                                           <i class="fas fa-times"></i> Eliminar
                                       </button>
                                       <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`;
                            }
                        }

                        Swal.fire('Actualización realizada', `El registro quedó marcado como validado.`, 'success')
                        .then(() => {
                            const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro).id;
                            window.Reportes.evaluarFolio(folioId);
                        });
                    } else {
                        Swal.fire('Error en actualización', `No se pudo actualizar la Hora Fin. Detalle: ${respuesta.error}`, 'error');
                    }
                })
                .catch(err => { console.error("Error al actualizar Hora Fin:", err); });
            }
        });

        if (btnEliminarFila) btnEliminarFila.hidden = true;

    // ── CASO: mixto4 ──────────────────────────────────────────────────────────
    // El empleado entra a las 07:00 y sale a las 17:00 (turno de 10 hrs).
    // El flujo de validación es idéntico al de un turno normal con tiempo extra:
    // se calcula la hora fin ajustada con base en las horas extra y se guarda.
    } else if (turnoDetectado === "mixto4") {
        window.Reportes.marcarRegistroComoApto(folioRegistro);

        Swal.fire({
            title: 'Resultados de validación:',
            html: `
                Se detectó un <b>Mixto 4 (07:00 - 17:00)</b> válido<br><br>
                Hora de Inicio de T. extra: <b>${HoraInicio}</b><br>
                Horas Extra trabajadas: <b>${horasExtra2}</b><br>
                Hora de Fin de T. extra ajustada: <b>${nuevaHoraFin}</b><br>
            `,
            icon: 'info'
        }).then(() => {
            if (horasalida !== nuevaHoraFin) {
                const data = new FormData();
                data.append("folioRegistro", folioRegistro);
                data.append("nuevaHoraFin", nuevaHoraFin);

                fetch("php/index.php?actualizarHoraFin", {
                    method: "POST",
                    body: data
                })
                .then(r => r.json())
                .then(respuesta => {
                    if (respuesta.success) {
                        const registro = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
                        if (registro) {
                            registro.HoraFin  = nuevaHoraFin;
                            registro.validado = 1;
                        }

                        const fila = document.querySelector(`#msg-${folioRegistro}`).closest("tr");
                        if (fila) {
                            const estatusCell = fila.querySelector("td:nth-child(13)");
                            if (estatusCell) {
                                estatusCell.innerHTML = registro && registro.validado == 1
                                    ? `<span class="badge bg-success">Validado</span>`
                                    : `<span class="badge bg-warning">No validado aun</span>`;
                            }

                            const accionesCell = fila.querySelector("td:nth-child(14)");
                            if (accionesCell) {
                                accionesCell.innerHTML = registro && registro.validado == 1
                                    ? `<span class="badge bg-success">Ya validado</span>
                                       <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`
                                    : `<button class="btn btn-sm btn-warning"
                                               onclick="validarInfo('${registro.NoEmpleadoSol}','${registro.fechaSol}','${registro.folioRegistro}','${registro.HoraInicio}')"
                                               id="btnValidar-${registro.folioRegistro}">
                                           <i class="fa-solid fa-eye"></i> Validar T. extra
                                       </button>
                                       <button class="btn btn-sm btn-danger"
                                               onclick="window.deletesub(${registro.folioRegistro})"
                                               id="btnEliminar-${registro.folioRegistro}" hidden>
                                           <i class="fas fa-times"></i> Eliminar
                                       </button>
                                       <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`;
                            }
                        }

                        Swal.fire(
                            'Actualización realizada',
                            `La Hora Final de T. extra se ajustó a ${nuevaHoraFin} y el registro quedó marcado como validado.`,
                            'success'
                        ).then(() => {
                            const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro).id;
                            window.Reportes.evaluarFolio(folioId);
                        });
                    } else {
                        Swal.fire('Error en actualización', `No se pudo actualizar la Hora Fin. Detalle: ${respuesta.error}`, 'error');
                    }
                })
                .catch(err => { console.error("Error al actualizar Hora Fin (mixto4):", err); });
            }
        });

        if (btnEliminarFila) btnEliminarFila.hidden = true;

    // ── CASO: turno normal con ≥55 min de tiempo extra ────────────────────────
    } else if (minutosTotal >= minutosReglamentarios && minutosExtra >= 55) {
        window.Reportes.marcarRegistroComoApto(folioRegistro);
        Swal.fire({
            title: 'Resultados de validación:',
            html: `
                Con base en las horas checadas, esta persona cumple los requisitos para el T. extra solicitado<br><br>
                Hora de Inicio de T. extra: <b>${HoraInicio}</b><br>
                Horas Extras trabajadas: <b>${horasExtra2}</b><br>
                Hora de Fin de T. extra ajustada: <b>${nuevaHoraFin}</b><br>
            `,
            icon: 'info'
        }).then(() => {
            if (horasalida !== nuevaHoraFin) {
                const data = new FormData();
                data.append("folioRegistro", folioRegistro);
                data.append("nuevaHoraFin", nuevaHoraFin);

                fetch("php/index.php?actualizarHoraFin", {
                    method: "POST",
                    body: data
                })
                .then(r => r.json())
                .then(respuesta => {
                    if (respuesta.success) {
                        const registro = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
                        if (registro) {
                            registro.HoraFin  = nuevaHoraFin;
                            registro.validado = 1;
                        }

                        const fila = document.querySelector(`#msg-${folioRegistro}`).closest("tr");
                        if (fila) {
                            const estatusCell = fila.querySelector("td:nth-child(13)");
                            if (estatusCell) {
                                estatusCell.innerHTML = registro && registro.validado == 1
                                    ? `<span class="badge bg-success">Validado</span>`
                                    : `<span class="badge bg-warning">No validado aun</span>`;
                            }

                            const accionesCell = fila.querySelector("td:nth-child(14)");
                            if (accionesCell) {
                                accionesCell.innerHTML = registro && registro.validado == 1
                                    ? `<span class="badge bg-success">Ya validado</span>
                                       <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`
                                    : `<button class="btn btn-sm btn-warning"
                                               onclick="validarInfo('${registro.NoEmpleadoSol}','${registro.fechaSol}','${registro.folioRegistro}','${registro.HoraInicio}')"
                                               id="btnValidar-${registro.folioRegistro}">
                                           <i class="fa-solid fa-eye"></i> Validar T. extra
                                       </button>
                                       <button class="btn btn-sm btn-danger"
                                               onclick="window.deletesub(${registro.folioRegistro})"
                                               id="btnEliminar-${registro.folioRegistro}" hidden>
                                           <i class="fas fa-times"></i> Eliminar
                                       </button>
                                       <small id="msg-${folioRegistro}" class="d-block mt-1"></small>`;
                            }
                        }

                        Swal.fire(
                            'Actualización realizada',
                            `La Hora Final de T. extra se ajustó a ${nuevaHoraFin} y el registro quedó marcado como validado.`,
                            'success'
                        ).then(() => {
                            const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro).id;
                            window.Reportes.evaluarFolio(folioId);
                        });
                    } else {
                        Swal.fire('Error en actualización', `No se pudo actualizar la Hora Fin. Detalle: ${respuesta.error}`, 'error');
                    }
                })
                .catch(err => { console.error("Error al actualizar Hora Fin:", err); });
            }
        });

        if (btnEliminarFila) btnEliminarFila.hidden = true;

    // ── CASO: no cumple requisitos ────────────────────────────────────────────
    } else {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        Swal.fire({
            title: 'Resultados de validación:',
            html: `
                No se cumplen los requisitos para solicitar un tiempo extra.<br><br>                                
                Tiempo extra trabajado: <b>${horasExtra2}</b><br>                                
            `,
            icon: 'info'
        });
        if (btnEliminarFila) btnEliminarFila.hidden = false;
    }

    const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro).id;
    window.Reportes.evaluarFolio(folioId);
}

// ─────────────────────────────────────────────────────────────────────────────
// calcularTurnoYHoras
// ─────────────────────────────────────────────────────────────────────────────
function calcularTurnoYHoras(horaEntrada, horaSalida, esSabado = false) {
    function horaAMinutos(hora) {
        var partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }

    function minutosAHora(minutos) {
        var horas = Math.floor(minutos / 60);
        var mins  = minutos % 60;
        return horas.toString().padStart(2, '0') + ":" + mins.toString().padStart(2, '0');
    }

    var entradaMin = horaAMinutos(horaEntrada);
    var salidaMin  = horaAMinutos(horaSalida);

    if (salidaMin < entradaMin) {
        salidaMin += 1440; // cruce de medianoche
    }

    var horaInicioTurno, horaFinTurno, nombreTurno, horasReglamentarias;
    var margen = 35;

    // ── IMPORTANTE: mixto4 definido ANTES de mixto1 para que el loop
    // general lo evalúe primero y el margen estrecho de entrada (±15 min
    // via bloque prioritario) evite colisión con mixto1 (07:30).
    var turnos = {
        turno1: {
            inicio:   7 * 60,
            fin:      15 * 60,
            nombre:   "Turno 1 (07:00:00 - 15:00:00)",
            duracion: "08:00:00"
        },
        turno2: {
            inicio:   15 * 60,
            fin:      22 * 60 + 30,
            nombre:   "Turno 2 (15:00:00 - 22:30:00)",
            duracion: "07:30:00"
        },
        turno2_13hrs: {
            inicio:   10 * 60 + 30,
            fin:      22 * 60 + 30,
            nombre:   "Turno 2 13_hrs (10:30:00 - 22:30:00)",
            duracion: "04:30:00"
        },        
        turno3: {
            inicio:   22 * 60 + 30,
            fin:      7 * 60 + 10,
            nombre:   "Turno 3 (22:30:00 - 07:00:00)",
            duracion: "08:30:00"
        },
        turno3_12hrs: {
            inicio:   19 * 60,
            fin:      7 * 60,
            nombre:   "Turno 3 12_hrs (19:00:00 - 07:00:00)",
            duracion: "08:30:00"
        },
        // ── NUEVO: Mixto 4 ────────────────────────────────────────
        // Definido ANTES de mixto1 para ser evaluado primero en el
        // loop general. La detección prioritaria usa margen ±15 min
        // en entrada para no solapar con mixto1 (07:30).
        mixto4: {
            inicio:   7 * 60,       // 420 min = 07:00
            fin:      17 * 60,      // 1020 min = 17:00
            nombre:   "Mixto 4 (07:00:00 - 17:00:00)",
            duracion: "10:00:00"
        },
        mixto1: {
            inicio:   7 * 60 + 30,
            fin:      17 * 60 + 10,
            nombre:   "Mixto 1 (07:30:00 - 17:00:00)",
            duracion: "10:00:00"
        },
        mixto2: {
            inicio:   8 * 60 + 30,
            fin:      18 * 60 + 30,
            nombre:   "Mixto 2 (08:30:00 - 18:30:00)",
            duracion: "10:00:00"
        },
        mixto3: {
            inicio:   7 * 60,
            fin:      16 * 60 + 30,
            nombre:   "Mixto 3 (07:00:00 - 16:30:00)",
            duracion: "09:30:00"
        }
    };

    var turnoDetectado = null;

    // ── DETECCIÓN PRIORITARIA 1: turno3_12hrs ─────────────────────────────────
    var entradaEs12hrs = Math.abs(entradaMin - turnos.turno3_12hrs.inicio) <= margen;
    var salidaEsperada12hrs = turnos.turno3_12hrs.fin + 1440; // cruza medianoche
    var salidaCercana12hrs  = Math.abs(salidaMin - salidaEsperada12hrs) <= margen * 4;

    if (entradaEs12hrs && salidaCercana12hrs) {
        turnoDetectado = "turno3_12hrs";
    }

    // ── DETECCIÓN PRIORITARIA 2: turno2_13hrs ────────────────────────────────
    if (!turnoDetectado) {
        var margen2_13 = 20;
        var entradaEs2_13hrs = Math.abs(entradaMin - turnos.turno2_13hrs.inicio) <= margen2_13;
        var salidaCercana2_13hrs = Math.abs(salidaMin - turnos.turno2_13hrs.fin) <= margen * 4;

        if (entradaEs2_13hrs && salidaCercana2_13hrs) {
            turnoDetectado = "turno2_13hrs";
        }
    }

    // ── DETECCIÓN PRIORITARIA 3: mixto4 ──────────────────────────────────────
    // Margen de entrada ±15 min (06:45–07:15) para NO solapar:
    //   - mixto1 cuya entrada ideal es 07:30 (fuera del rango)
    //   - mixto3 cuya entrada ideal es 07:00 (dentro del rango, pero la
    //     SALIDA a 17:00 vs 16:30 de mixto3 es lo que los diferencia)
    //   - turno1 cuya salida es 15:00 (muy diferente a 17:00)
    // Margen de salida ±70 min (16:30–17:30) cubre checadas tardías.
    if (!turnoDetectado) {
        var margenMixto4Entrada = 15;
        var margenMixto4Salida  = margen * 2; // ±70 min
        var entradaEsMixto4 = Math.abs(entradaMin - turnos.mixto4.inicio) <= margenMixto4Entrada;
        var salidaEsMixto4  = Math.abs(salidaMin  - turnos.mixto4.fin)    <= margenMixto4Salida;

        if (entradaEsMixto4 && salidaEsMixto4) {
            turnoDetectado = "mixto4";
        }
    }

    // ── Loop general (excluye los ya evaluados) ───────────────────────────────
    if (!turnoDetectado) {
        for (var key in turnos) {
            if (key === "turno3_12hrs" || key === "turno2_13hrs" || key === "mixto4") continue;
            var turno = turnos[key];
            var entradaCercana = Math.abs(entradaMin - turno.inicio) <= margen;
            var salidaCercana  = Math.abs(salidaMin  - turno.fin)    <= margen * 4;

            if (entradaCercana && salidaCercana) {
                turnoDetectado = key;
                break;
            }
        }
    }

    // ── Fallback por entrada sola ─────────────────────────────────────────────
    // mixto4 va primero con margen reducido de ±15 min para que 07:00 no
    // caiga en mixto1 (07:30) ni en el margen general de turno1.
    if (!turnoDetectado) {
        var prioridad = ['mixto4', 'mixto1', 'mixto2', 'turno1', 'turno2_13hrs', 'turno2', 'mixto3', 'turno3_12hrs', 'turno3'];
        for (var i = 0; i < prioridad.length; i++) {
            var key2   = prioridad[i];
            var turno2 = turnos[key2];
            var margenFallback = (key2 === "turno2_13hrs" || key2 === "mixto4") ? 15 : margen;
            if (Math.abs(entradaMin - turno2.inicio) <= margenFallback) {
                turnoDetectado = key2;
                break;
            }
        }
    }

    // ── Asignación de valores según turno detectado ───────────────────────────
    if (turnoDetectado === 'turno1') {
        horaInicioTurno     = minutosAHora(turnos.turno1.inicio);
        horaFinTurno        = minutosAHora(turnos.turno1.fin);
        nombreTurno         = turnos.turno1.nombre;
        horasReglamentarias = turnos.turno1.duracion;
    } else if (turnoDetectado === 'turno2') {
        horaInicioTurno     = minutosAHora(turnos.turno2.inicio);
        horaFinTurno        = minutosAHora(turnos.turno2.fin);
        nombreTurno         = turnos.turno2.nombre;
        horasReglamentarias = turnos.turno2.duracion;
    } else if (turnoDetectado === 'turno2_13hrs') {
        horaInicioTurno     = minutosAHora(turnos.turno2_13hrs.inicio);
        horaFinTurno        = minutosAHora(turnos.turno2_13hrs.fin);
        nombreTurno         = turnos.turno2_13hrs.nombre;
        horasReglamentarias = turnos.turno2_13hrs.duracion;
    } else if (turnoDetectado === 'turno3') {
        horaInicioTurno     = minutosAHora(turnos.turno3.inicio);
        horaFinTurno        = minutosAHora(turnos.turno3.fin);
        nombreTurno         = turnos.turno3.nombre;
        horasReglamentarias = turnos.turno3.duracion;
    } else if (turnoDetectado === 'turno3_12hrs') {
        horaInicioTurno     = minutosAHora(turnos.turno3_12hrs.inicio);
        horaFinTurno        = minutosAHora(turnos.turno3_12hrs.fin);
        nombreTurno         = turnos.turno3_12hrs.nombre;
        horasReglamentarias = turnos.turno3_12hrs.duracion;
    } else if (turnoDetectado === 'mixto4') {
        horaInicioTurno = minutosAHora(turnos.mixto4.inicio);
        if (esSabado) {
            horaFinTurno        = minutosAHora(turnos.mixto4.inicio + 5 * 60); // 12:00
            nombreTurno         = "MIXTO SABADO (07:00:00 - 12:00:00)";
            horasReglamentarias = "05:00:00";
        } else {
            horaFinTurno        = minutosAHora(turnos.mixto4.fin);
            nombreTurno         = turnos.mixto4.nombre;
            horasReglamentarias = turnos.mixto4.duracion;
        }
    } else if (turnoDetectado === 'mixto1') {
        horaInicioTurno = minutosAHora(turnos.mixto1.inicio);
        if (esSabado) {
            horaFinTurno        = minutosAHora(turnos.mixto1.inicio + 5 * 60);
            nombreTurno         = "MIXTO SABADO (07:30:00 - 12:30:00)";
            horasReglamentarias = "05:00:00";
        } else {
            horaFinTurno        = minutosAHora(turnos.mixto1.fin);
            nombreTurno         = turnos.mixto1.nombre;
            horasReglamentarias = turnos.mixto1.duracion;
        }
    } else if (turnoDetectado === 'mixto2') {
        horaInicioTurno = minutosAHora(turnos.mixto2.inicio);
        if (esSabado) {
            horaFinTurno        = minutosAHora(turnos.mixto2.inicio + 5 * 60);
            nombreTurno         = "MIXTO SABADO (08:30:00 - 13:30:00)";
            horasReglamentarias = "05:00:00";
        } else {
            horaFinTurno        = minutosAHora(turnos.mixto2.fin);
            nombreTurno         = turnos.mixto2.nombre;
            horasReglamentarias = turnos.mixto2.duracion;
        }
    } else if (turnoDetectado === 'mixto3') {
        horaInicioTurno = minutosAHora(turnos.mixto3.inicio);
        if (esSabado) {
            horaFinTurno        = minutosAHora(turnos.mixto3.inicio + 5 * 60);
            nombreTurno         = "MIXTO SABADO (19:00:00 - 04:30:00)";
            horasReglamentarias = "05:00:00";
        } else {
            horaFinTurno        = minutosAHora(turnos.mixto3.fin);
            nombreTurno         = turnos.mixto3.nombre;
            horasReglamentarias = turnos.mixto3.duracion;
        }
    } else {
        horaInicioTurno     = horaEntrada;
        horaFinTurno        = "00:00:00";
        nombreTurno         = "No hay";
        horasReglamentarias = "00:00:00";
    }

    var minutosTrabajados = salidaMin - entradaMin;
   
    var finTurnoMin;
    if      (nombreTurno.includes("Turno 1"))         finTurnoMin = turnos.turno1.fin;
    else if (nombreTurno.includes("Turno 2 13"))      finTurnoMin = 15 * 60; // 900 min = 15:00
    else if (nombreTurno.includes("Turno 2"))         finTurnoMin = turnos.turno2.fin;
    else if (nombreTurno.includes("Turno 3 12"))      finTurnoMin = turnos.turno3_12hrs.fin + 1440;
    else if (nombreTurno.includes("Turno 3"))         finTurnoMin = turnos.turno3.fin;
    // ── NUEVO: mixto4 → "07:00:00" lo diferencia de mixto1 "07:30:00" ────────
    // Este bloque debe ir ANTES del de mixto1 para que el includes("07:00:00")
    // capture "Mixto 4" y no caiga en el bloque genérico de mixto1.
    else if (nombreTurno.includes("Mixto") && nombreTurno.includes("07:00:00")) {
        finTurnoMin = esSabado ? turnos.mixto4.inicio + 5 * 60 : turnos.mixto4.fin;
    }
    else if (nombreTurno.includes("Mixto") && nombreTurno.includes("07:30:00")) {
        finTurnoMin = esSabado ? turnos.mixto1.inicio + 5 * 60 : turnos.mixto1.fin;
    }
    else if (nombreTurno.includes("Mixto") && nombreTurno.includes("08:30:00")) {
        finTurnoMin = esSabado ? turnos.mixto2.inicio + 5 * 60 : turnos.mixto2.fin;
    }
    else if (nombreTurno.includes("Mixto") && nombreTurno.includes("19:00:00")) {
        finTurnoMin = esSabado ? turnos.mixto3.inicio + 5 * 60 : turnos.mixto3.fin;
    }
    else {
        finTurnoMin = salidaMin;
    }

    var horasExtrasMin = salidaMin - finTurnoMin;
    var horasExtras    = horasExtrasMin > 0 ? minutosAHora(horasExtrasMin) : "00:00:00";
    var totalHoras     = minutosAHora(minutosTrabajados);

    if (turnoDetectado === "turno3_12hrs") {
        Swal.fire({
            title: 'Turno detectado: Tercero 12 hrs',
            html: `
                Se identificó turno <b>Tercero 12 hrs</b><br><br>
                Entrada: <b>${horaInicioTurno}</b><br>
                Salida esperada: <b>${horaFinTurno}</b><br>
                Total horas trabajadas: <b>${totalHoras}</b><br>
                Horas reglamentarias: <b>${horasReglamentarias}</b>
            `,
            icon: 'info'
        });
    }

    if (turnoDetectado === "turno2_13hrs") {
        Swal.fire({
            title: 'Turno detectado: Segundo 13 hrs',
            html: `
                Se identificó turno <b>Segundo 13 hrs</b><br><br>
                Entrada: <b>${horaInicioTurno}</b> (10:30)<br>
                Salida de turno normal: <b>${horaFinTurno}</b> (~22:30)<br>
                Horas reglamentarias (bloque extra): <b>${horasReglamentarias}</b><br>
                Hora fin T. extra ajustada: <b>16:30</b>
            `,
            icon: 'info'
        });
    }

    if (turnoDetectado === "mixto4") {
        Swal.fire({
            title: 'Turno detectado: Mixto 4',
            html: `
                Se identificó turno <b>Mixto 4 (07:00 - 17:00)</b><br><br>
                Entrada: <b>${horaInicioTurno}</b><br>
                Salida esperada: <b>${horaFinTurno}</b><br>
                Total horas trabajadas: <b>${totalHoras}</b><br>
                Horas reglamentarias: <b>${horasReglamentarias}</b>
            `,
            icon: 'info'
        });
    }

    return {
        turno:              nombreTurno,
        horaInicioTurno:    horaInicioTurno,
        horaFinTurno:       horaFinTurno,
        horasExtras:        horasExtras,
        totalHoras:         totalHoras,
        salidaMin:          salidaMin,
        entradaMin:         entradaMin,
        finTurnoMin:        finTurnoMin,
        horaSalida:         horaSalida,
        horaEntrada:        horaEntrada,
        horasReglamentarias: horasReglamentarias
    };
}

// ─────────────────────────────────────────────────────────────────────────────
// Instancia global y eventos
// ─────────────────────────────────────────────────────────────────────────────
window.Reportes = new Reportes();
window.Reportes.consulta();

window.validarInfo = function(noEmpSol, fechaSol, folioRegistro, HoraInicio) {
    window.Reportes.getinfohoraentradaysalida(noEmpSol, fechaSol, folioRegistro, HoraInicio);
}

window.deletesub = function(id) {
    window.Reportes.deletesub(id);
}

window.Autoriza = function(id) {
    window.Reportes.enviar(id, 1);
}

window.Rechazar = function(id) {
    window.Reportes.enviar(id, 2);
}

window.pdfFin = function(id) {
    window.Reportes.pdffin(id);
}

// ── Paginación y buscador ─────────────────────────────────────────────────────
let infoPlanEntregados = [];
let currentPage = 1;
let pageSize = 15;

document.getElementById("filtroGlobal").addEventListener("keyup", (e) => {
    e.preventDefault();
    clearTimeout(e.target._searchTimer);
    e.target._searchTimer = setTimeout(() => {
        currentPage = 1;
        mostrarTabla();
    }, 250);
});

async function obtenerDatosArray() {
    try {
        const axiosResponse = await axios.post("php/index.php?tblautorizatp");
        const promesa = {
            status: axiosResponse.status,
            json: async () => axiosResponse.data,
        };
        infoPlanEntregados = axiosResponse.data.map((item) => ({
            folioRegistro:     item.folioRegistro,
            id:                item.id,
            fecha:             item.fecha,
            departamento:      item.departamento,
            creado:            item.creado,
            terminado:         item.terminado,
            autorizado:        item.autorizado,
            NoEmp:             item.NoEmp,
            SupervisorNombre:  item.SupervisorNombre,
            noempautoriza:     item.noempautoriza,
            fechaSol:          item.fechaSol,
            NoEmpleadoSol:     item.NoEmpleadoSol,
            HoraInicio:        item.HoraInicio,
            HoraFin:           item.HoraFin,
            NombreEmpleadoSol: item.NombreEmpleadoSol,
            validado:          item.validado,
        }));

        if (promesa.status === 200) mostrarTabla();
    } catch (error) {
        console.log(error);
        swal.fire("Error", "Hay un problema con la base de datos", "error");
    }
}

obtenerDatosArray();

function mostrarTabla(query = document.getElementById("filtroGlobal").value) {
    const tbody = document.getElementById("tblenc");
    tbody.innerHTML = "";
    const q = (query || "").toString().trim().toLowerCase();

    const datosFiltrados = q
        ? infoPlanEntregados.filter((item) =>
            [
                item.folioRegistro, item.id, item.fecha, item.departamento,
                item.creado, item.terminado, item.autorizado, item.NoEmp,
                item.SupervisorNombre, item.noempautoriza, item.fechaSol,
                item.NoEmpleadoSol, item.HoraInicio, item.HoraFin,
                item.NombreEmpleadoSol, item.validado,
            ].some((v) => v && v.toString().toLowerCase().includes(q))
          )
        : infoPlanEntregados.slice();

    const totalRegistros = datosFiltrados.length;
    const totalPaginas   = Math.max(1, Math.ceil(totalRegistros / pageSize));
    if (currentPage > totalPaginas) currentPage = totalPaginas;

    const paginaActualDatos = datosFiltrados;
    let body = "";

    if (paginaActualDatos.length === 0) {
        body = `<tr><td colspan="14" class="text-center">No hay registros que coincidan</td></tr>`;
    } else {
        paginaActualDatos.forEach((element) => {
            const fechaRaw = element.fecha ?? "";
            let fechaDisplay = "Sin Información";
            if (fechaRaw) {
                const f = fechaRaw.toString();
                if (/^\d{4}-\d{2}$/.test(f) || /^\d{4}-\d{2}-\d{2}$/.test(f)) {
                    fechaDisplay = f;
                } else {
                    const d = new Date(f);
                    if (!isNaN(d)) {
                        const y   = d.getFullYear();
                        const m   = String(d.getMonth() + 1).padStart(2, "0");
                        const day = String(d.getDate()).padStart(2, "0");
                        fechaDisplay = `${y}-${m}-${day}`;
                    } else {
                        fechaDisplay = f;
                    }
                }
            }

            let accionHtml = "";
            let accionTerminadoHtml = "";
            let badgeValidado = "";

            if (element.terminado === null || element.terminado === "") {
                if (element.validado == null || element.validado == 0) {
                    accionHtml = `
                        <button class="btn btn-sm btn-warning"
                                onclick="validarInfo('${element.NoEmpleadoSol}','${element.fechaSol}','${element.folioRegistro}','${element.HoraInicio}')"
                                id="btnValidar-${element.folioRegistro}">
                            <i class="fa-solid fa-eye"></i> Validar T. extra
                        </button>
                        <button class="btn btn-sm btn-danger"
                                onclick="window.deletesub(${element.folioRegistro})"
                                id="btnEliminar-${element.folioRegistro}" hidden>
                            <i class="fas fa-times"></i> Eliminar
                        </button>
                    `;
                } else {
                    accionHtml = `<span class="badge bg-success">Solicitud validada</span>`;
                }
                accionTerminadoHtml = `<span class="badge bg-warning"> No procesada (Pendiente)</span>`;
            } else if (element.terminado == 1) {
                accionHtml          = `<span class="badge bg-success">Aprobado</span>`;
                accionTerminadoHtml = `<span class="badge bg-success">Solicitud procesada (Aprobado)</span>`;
            } else if (element.terminado == 2) {
                accionHtml          = `<span class="badge bg-danger">Rechazado</span>`;
                accionTerminadoHtml = `<span class="badge bg-danger">Solicitud procesada (Rechazado)</span>`;
            }

            if (element.validado === null || element.validado === 0) {
                badgeValidado = `<span class="badge bg-secondary">No validado</span>`;
            } else if (element.validado == 1) {
                badgeValidado = `<span class="badge bg-success">Validado</span>`;
            }

            body += `
            <tr>
                <td>${element.folioRegistro}</td>
                <td>${element.id}</td>
                <td>${element.creado}</td>
                <td>${element.NoEmp}</td>
                <td>${element.SupervisorNombre}</td>
                <td>${element.NoEmpleadoSol}</td>
                <td>${element.fechaSol}</td>
                <td>${element.HoraInicio}</td>
                <td>${element.HoraFin}</td>
                <td>${element.NombreEmpleadoSol}</td>
                <td>${element.departamento}</td>
                <td>${accionTerminadoHtml}<br></td>
                <td>${badgeValidado}</td>
                <td>
                    ${accionHtml}
                    <small id="msg-${element.folioRegistro}" class="d-block mt-1"></small>
                </td>
            </tr>`;
        });
    }

    tbody.innerHTML = body;

    const ahora = new Date();
    paginaActualDatos.forEach(elemento => {
        const fechaSol = new Date(elemento.fechaSol + "T00:00:00");
        const [h, m, s] = elemento.HoraFin.split(":").map(Number);
        const horaFinDate = new Date(fechaSol);
        horaFinDate.setHours(h, m, s);

        const btnValidar = document.getElementById(`btnValidar-${elemento.folioRegistro}`);
        const msg = document.getElementById(`msg-${elemento.folioRegistro}`);
        const horaFinMasMargen = new Date(horaFinDate.getTime() + 5 * 60000);

        if (ahora < fechaSol) {
            if (btnValidar) btnValidar.hidden = true;
            if (msg) {
                msg.textContent = `El día solicitado (${elemento.fechaSol}) aún no ha llegado para hacer el cálculo correspondiente.`;
                msg.className = "alert alert-warning p-1 mt-1";
            }
        } else if (ahora.toDateString() === fechaSol.toDateString() && ahora < horaFinMasMargen) {
            if (btnValidar) btnValidar.hidden = true;
            if (msg) {
                msg.textContent = `Aún no es momento de calcular. El botón se habilitará a las ${horaFinMasMargen.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} hrs.`;
                msg.className = "alert alert-warning p-1 mt-1";
            }
        } else if (ahora >= horaFinMasMargen) {
            if (msg) {
                msg.textContent = "";
                msg.dataset.estado = "pendiente";
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
// -------------------------------------------------------------------------------------------------------------------------
const driver = window.driver.js.driver;

const steps = [
    {
        element: ".tittlecont",
        popover: {
            title: "Validación de solicitudes",
            description: "Aquí podrás validar las solicitudes de tiempo extra antes de enviarlas a Gerencia.",
            side: "bottom"
        }
    },
    {
        element: ".alert.alert-info",
        popover: {
            title: "Instrucciones",
            description: "Desde esta sección revisa qué solicitudes son aptas para autorización.",
            side: "bottom"
        }
    },
    {
        element: "#filtroGlobal",
        popover: {
            title: "Filtro global",
            description: "Usa este campo para buscar solicitudes por nombre, folio o cualquier dato de la tabla.",
            side: "top",
            popoverClass: "popover-importante"
        }
    },
    {
        element: "table thead th:nth-child(1)",
        popover: {
            title: "ID Registro",
            description: "Identificador único de cada solicitud.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(2)",
        popover: {
            title: "Folio",
            description: "Número de folio al que pertenece la solicitud.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(3)",
        popover: {
            title: "Creacion de folio",
            description: "La fecha en la que se creo el folio inicial.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(4)",
        popover: {
            title: "Noemp de supervisor",
            description: "Número de empleado del supervisor.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(5)",
        popover: {
            title: "Nombre de supervisor",
            description: "Nombre del supervisor que abrio el folio para los tiempos extra.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(6)",
        popover: {
            title: "Noemp solicitante",
            description: "Número empleado de la persona que va a realizar el tiempo extra.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(7)",
        popover: {
            title: "Fecha Solicitud",
            description: "Día en que el empleado solicitó el tiempo extra.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(8)",
        popover: {
            title: "Hora Inicio",
            description: "Hora en que comienza el tiempo extra.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(9)",
        popover: {
            title: "Hora Fin",
            description: "Hora en que termina el tiempo extra.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(10)",
        popover: {
            title: "Nombre solicitante",
            description: "Nombre de la persona que va a realizar el tiempo extra.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(11)",
        popover: {
            title: "Departamento",
            description: "Departamento de la persona que va a solicitar su tiempo extra.",
            side: "top"
        }
    },
    {
        element: "table thead th:nth-child(12)",
        popover: {
            title: "Estatus",
            description: "Aquí se mostrará si la solicitud fue aprobada o rechazada por Gerencia.",
            side: "top",
            popoverClass: "popover-importante"
        }
    },
    {
        element: "table thead th:nth-child(13)",
        popover: {
            title: "Validado",
            description: "Aquí se mostrará si la solicitud ya fue Validada con el boton de 'Validar T. extra'.",            
            side: "top",
            popoverClass: "popover-importante"
        }
    },
    {
        element: "table thead th:nth-child(14)",
        popover: {
            title: "Acciones",
            description: "Aquí encontrarás botones para validar individualmente cada registro. Si la fecha u hora de alguna solicitud esta en una fecha y hora futura al dia actual se mostrara un mensaje mediante el cual se indicara a partir de que momento se podra hacer la validacion del Tiempo Extra",
            side: "top",
            popoverClass: "popover-importante"
        }
    },
    {
        element: "#tblenc",
        popover: {
            title: "Solicitudes listadas",
            description: "En esta tabla se mostrarán todas las solicitudes con sus datos y estado actual.",
            side: "top"
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

// Detectar mensajes dinámicos (msg-*) y agregarlos como pasos adicionales
document.querySelectorAll('[id^="msg-"]').forEach(msgEl => {
    steps.push({
        element: `#${msgEl.id}`,
        popover: {
            title: "Mensaje de validación",
            description: msgEl.textContent || "Aquí aparecerán mensajes sobre el estado de la solicitud.",
            side: "top"
        }
    });
});

const driverObj = driver({
    showProgress: true,
    allowClose: false,
    disableInteraction: true,
    progressText: "Paso {{current}} de {{total}}",
    doneBtnText: "Finalizar",
    nextBtnText: "Siguiente",
    prevBtnText: "Atrás",
    steps
});

// Clave única para este tutorial
const tutorialKey = "tutorial_validacionTE";
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