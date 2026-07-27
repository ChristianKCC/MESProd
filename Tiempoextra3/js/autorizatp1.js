import { Toolsjs } from "../../Tools/Tools.js";
const Tools = new Toolsjs();
class Reportes {
    constructor() {
        this.data = [];
    }

    // Obtener hora de entrada y de salida segun el numero de empleado
    async getinfohoraentradaysalida(noemp, date, folioRegistro, HoraInicio) {
        // Calcular fecha anterior y del dia siguiente
        var fechaActual = new Date(date + "T00:00:00");

        var fechaAnterior = new Date(fechaActual);
        fechaAnterior.setDate(fechaAnterior.getDate() - 1);

        // Obtener fecha en formato correcto
        var fechaAnteriorStr = formatearFecha(fechaAnterior);

        var fechaSiguiente = new Date(fechaActual);
        fechaSiguiente.setDate(fechaSiguiente.getDate() + 1);
        var fechaSiguienteStr = formatearFecha(fechaSiguiente);

        // Funcion de formateo de fecha
        function formatearFecha(fecha) {
            var año = fecha.getFullYear();
            var mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
            var dia = fecha.getDate().toString().padStart(2, '0');
            return año + "-" + mes + "-" + dia;
        }


        // Consulta de datos para esos tres dias
        // Permite manejar casos como turnos mixtos o terceros que comienzan en la noche y terminan al dia siguiente
        // Por ello se verifican en el dia anterior y posterior segun la fecha que se selecciona
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

            if(respuestaActual.length === 0){
                Swal.fire({
                    title: 'Resultados de validación:',
                    html: `
                        Se recomienda eliminar este registro de empleado<br><br>
                        <b>No hay registros de horarios checados para el empleado en el dia especificado.</b><br>
                    `,
                    icon: 'info'
                });
                // habilitar botón de eliminar de la fila
                const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);
                if (btnEliminarFila) btnEliminarFila.hidden = false;
                return;
            } 
            lblMensaje.className = "alert alert-warning mt-2";
            lblMensaje.style.display = "block";

            return;
        }

        // Funcion para agrupar registros eliminando duplicados (Con esto si en un dia hay 3 o + registros para la entrada y salida 
        // solo nos quedamos con el primero evitando problemas de valores de mas que no permitan identificar el turno del empleado
        function agruparPorHora(registros) {
            var horasVistas = {};
            var resultado = [];

            registros.forEach(function (registro) {
                if (registro.fecha_h) {
                    // Se le da un formato en HH:MM
                    var horaSinSegundos = registro.fecha_h.substring(0, 8);

                    // Se agregan si es la primera vez que se ve esa hora
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

        // Limpieza de duplicados en consulta de 3 dias desde anterior, actual y siguiente
        var registrosAnteriorLimpios = agruparPorHora(respuestaAnterior);
        var registrosActualLimpios = agruparPorHora(respuestaActual);
        var registrosSiguienteLimpios = agruparPorHora(respuestaSiguiente);

        // Funcion para convertir hora a minutos
        function horaAMinutos(hora) {
            var partes = hora.split(":");
            return parseInt(partes[0] * 60 + parseInt(partes[1]));
        }
        
        // Segunda definicion de turnos con margenes mas flexibles para ajustar turnos y horarios
        var turnos = {
            turno1: {
                entrada: { min: 5 * 60, max: 11 * 60 },
                salida: { min: 13 * 60, max: 20 * 60 },
                horaIdealEntrada: "07:00:00",
                horaIdealSalida: "15:00:00"
            },
            turno2: {
                entrada: { min: 13 * 60, max: 17 * 60 },
                salida: { min: 20 * 60, max: 24 * 60 },
                horaIdealEntrada: "15:00:00",
                horaIdealSalida: "22:30:00"
            },
            turno3: {
                entrada: { min: 20 * 60, max: 24 * 60 },
                salida: { min: 5 * 60, max: 11 * 60 },
                horaIdealEntrada: "22:30:00",
                horaIdealSalida: "07:00:00"
            },
            mixto1: {
                entrada: { min: 6 * 60, max: 9 * 60 },
                salida: { min: 15 * 60 + 30, max: 19 * 60 },
                horaIdealEntrada: "07:00:00",
                horaIdealSalida: "17:00:00"
            },
            mixto2: {
                entrada: { min: 7 * 60 + 30, max: 10 * 60 },
                salida: { min: 17 * 60, max: 21 * 60 },
                horaIdealEntrada: "08:30:00",
                horaIdealSalida: "18:30:00"
            },
            mixto3: {
                entrada: { min: 7 * 60, max: 9 * 60 },
                salida: { min: 16 * 60, max: 17 * 60 },
                horaIdealEntrada: "07:00:00",
                horaIdealSalida: "16:30:00"
            }
        };

        // Funcion para verificar que la hora de entrada/salida este en un rango adecuado, para ello se usa en valor de min y max que estan definidos en el array
        // anterior, con ello se permite dar flexibilidad a horas de entrada/salida en caso de que lleguen o salgan minutos antes o despues y poder identificar el turno correctamente
        function estaEnRango(horaMin, rango) {
            return horaMin >= rango.min && horaMin <= rango.max;
        }

        // Funicion de verificacion de turnos con base en las horas de entrada y salida haciendo un aproximado 
        function identificarTurno(horaEntradaMin, horaSalidaMin) {
            for (var nombreTurno in turnos) {
                var turno = turnos[nombreTurno];

                // Si las horas de entrada y salida coinciden con las horas de entrada y salida o estan en ese rango se considera que ese es su turno y se retorna el nombre de dicho turno
                if (estaEnRango(horaEntradaMin, turno.entrada) && estaEnRango(horaSalidaMin, turno.salida)) {
                    return nombreTurno;
                }
            }
            return null;
        }

        // Funcion para analisis de casos con 3 registros (Depuracion de datos para verificar si alguno tiene mas de 2 registros con horas distintas)
        function analizarCasoEspecial(registrosLimpios){
            if(registrosLimpios.length){
                // Buscar si existen mas de 3 registros con horas diferentes en el dia actual
                var horas = registrosLimpios.map(r => ({
                    hora: registrosActualLimpios.hora_limpia,
                    minutos:
                    horaAMinutos(r.hora_limpia)
                }));
                var gaps = [];
                for (var i=1; i<horas.length; i++){
                    gaps.push({
                        minutos: horas[i].minutos - horas [i - 1].minutos
                    });
                }

                // Doble turno: consiste en un gap de 6-10 horas (Dobles turnos)
                var hayGapLargo = gaps.some(g => g.minutos >= 6*60 && g.minutos <= 10*60);
                if(hayGapLargo) {
                    return {
                        tipo: "doble_turno",
                        entrada: registrosLimpios[0], 
                        salida: registrosLimpios[registrosLimpios.length - 1]
                    };
                }

                // Deteccion de multiples intentos en una entrada o en salidas
                var todosJuntos = gaps.every(g => g.minutos < 30);
                if (todosJuntos){
                    return {
                        tipo: "multiples_intentos",
                        entrada: registrosLimpios[0], 
                        salida: registrosLimpios[registrosLimpios.length - 1]
                    };
                }

                return {
                    tipo: "desconocido",
                    entrada: registrosLimpios[0],
                    salida: registrosLimpios[registrosLimpios.length - 1]
                };
            }
            return null;
        }

        // Variables para el calculo de datos de entrada, salida y turno
        var entrada, salida, turnoDetectado;

        // Analisis de casos especiales segun las condiciones de arriba
        var casoEspecial = analizarCasoEspecial(registrosActualLimpios);

        // Primera validacion
        if(casoEspecial){
            entrada = casoEspecial.entrada;
            salida = casoEspecial.salida;
        }

        else if(registrosActualLimpios.length >= 2){
            var primeraHoraActual = registrosActualLimpios[0];
            var ultimaHoraActual = registrosActualLimpios[registrosActualLimpios.length - 1];

            // Conversion de horas a minutos para el analisis de turnos
            var primeraMin = horaAMinutos(primeraHoraActual.hora_limpia);
            var ultimaMin = horaAMinutos(ultimaHoraActual.hora_limpia);

            // Identificacion del turno
            turnoDetectado = identificarTurno(primeraMin, ultimaMin);

            // Validaciones de turno segun el analisis previo
            // Identificacion de turno actual
            if (turnoDetectado){
                entrada = primeraHoraActual;
                salida = ultimaHoraActual;
            }
            else {
                var segundaMin = ultimaMin;

                if(estaEnRango(segundaMin, turnos.turno3.entrada) || estaEnRango(segundaMin, turnos.mixto3.salida)){
                    // Con esto asignamos a la entrada el valor de la ultima hora actual
                    entrada = ultimaHoraActual;

                    // Busqueda de la hora de salida en el primer registro del dia siguiente
                    if(registrosSiguienteLimpios.length > 0 ){
                        salida = registrosSiguienteLimpios[0];
                    } else {
                        return;
                    }
                } else {
                    return;
                }
            }
        }

        // Validacion final 
        else if(registrosActualLimpios.length === 1){
            var unicaHoraActual = registrosActualLimpios[0];
            var unicaMin = horaAMinutos(unicaHoraActual.hora_limpia);
                
            if (estaEnRangounica(unicaMin, turnos.turno3.entrada) || estaEnRango(unicaMin, turnos.mixto3.entrada)){
                entrada = unicaHoraActual;

                if(registrosSiguienteLimpios.len > 0){
                    salida = registrosSiguienteLimpios[0];
                } else {
                    return;
                }
            } else if(estaEnRango(unicaMin, turnos.turno3.salida) || estaEnRango(unicaMin, turnos.mixto3.salida)) {
                salida = unicaHoraActual;

                if(registrosAnteriorLimpios.length > 0){
                    entrada = registrosAnteriorLimpios[registrosAnteriorLimpios.length - 1];
                } else {
                    return;
                }
            }
            else {
                return;
            }
        }

        // Validaciones de que los campos esten completos
        if (!entrada || !salida){
            return;
        }
        
        // Primer caso: Solo hay registros en el dia actual
        if (registrosActualLimpios.length > 0) {

            // Caso 1a: Hay 2 o mas registros en el dia actual
            if (registrosActualLimpios.length >= 2) {
                var primeraHoraActual = registrosActualLimpios[0];
                var ultimaHoraActual = registrosActualLimpios[registrosActualLimpios.length - 1];

                var primeraMin = horaAMinutos(primeraHoraActual.hora_limpia);
                var ultimaMin = horaAMinutos(ultimaHoraActual.hora_limpia);

                // Identificar turno con entrada y salida del mismo dia
                turnoDetectado = identificarTurno(primeraMin, ultimaMin);

                if (turnoDetectado) {
                    // Turno identificado corectamente como Turnos del 1 al 2 en normal o mixto
                    // Caso 1a: La entrada y salida estan en el mismo dia
                    entrada = primeraHoraActual;
                    salida = ultimaHoraActual;
                } else {
                    // No coincide con ningun turno conocido
                    var segundaMin = ultimaMin;

                    if (estaEnRango(segundaMin, turnos.turno3.entrada) || estaEnRango(segundaMin, turnos.mixto3.entrada)) {
                        // Caso de que la segunda hora es entrada de turno nocturno
                        // La primera hora es salida del turno anterior
                        entrada = ultimaHoraActual; // La hora nocturna es la entrada

                        // Ahora se busca la salida en el dia siguiente
                        // Caso 1A - Primera hora es salida anterior, segunda entrada es nocturna
                        if (registrosActualLimpios.length > 0) {
                            salida = registrosSiguienteLimpios[0];
                        } else {
                        }
                    } else {
                    }
                }
            }
            // Caso 1b: Solo hay un registro en el dia actual
            else {
                var unicaHoraActual = registrosActualLimpios[0];
                var unicaMin = horaAMinutos(unicaHoraActual.hora_limpia);

                // Verificamos si es entrada de turno nocturno para 3 o mixto
                if (estaEnRango(unicaMin, turnos.turno3.entrada) || estaEnRango(unicaMin, turnos.mixto3.entrada)) {
                    // Es entrada de turno nocturno, y se busca su salida en el dia sigiente
                    entrada = unicaHoraActual;

                    // Caso 1b: Entrada nocturna en dia actual y la salida en el dia siguiente
                    if (registrosSiguienteLimpios.length > 0) {
                        salida = registrosSiguienteLimpios[0];
                    } else {
                    }
                }

                // Verificar si es salida de turno nocturno del dia anterior
                else if (estaEnRango(unicaMin, turnos.turno3.salida) || estaEnRango(unicaMin, turnos.mixto3.salida)) {
                    salida = unicaHoraActual;

                    // Caso 1b: Salida en dia actual, entrada en dia anterior
                    if (registrosAnteriorLimpios.length > 0) {
                        entrada = registrosAnteriorLimpios[registrosAnteriorLimpios.length - 1];
                    } else {
                    }
                }
                else {
                }
            }
        }
        else {
        }

        if (!entrada || !entrada.fecha_h || !salida || !salida.fecha_h) {

            btnEliminar.hiden = true;

            lblMensaje.hidden = false;

            lblMensaje.textContent = "No se pudo determinar entrada y salida correctamente.";
            Swal.fire({
                title: 'Resultados de validación:',
                html: `
                    Se recomienda eliminar este registro de empleado<br><br>
                    <b>No se pudo determinar entrada y salida correctamente. O si lo deseas consulta mas tarde para verificar si su registro fue realizado mas tarde.</b><br>
                `,
                icon: 'info'
            });
            lblMensaje.className = "alert alert-warning mt-2";
            lblMensaje.style.display = "block";

            // habilitar botón de eliminar de la fila
            const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);
            if (btnEliminarFila) btnEliminarFila.hidden = false;
            return;
        }

        // Extraer horas limpias
        var horaentrada = entrada.hora_limpia;
        var horasalida = salida.hora_limpia;

        // Verificar si es sabado (Pues solo se contemplan como medio dia en caso de guardias)
        const fechar = new Date(date + "T00:00:00");

        var esSabado = fechar.getDay() === 6;

        // Calcular turno y horas
        var resultado = calcularTurnoYHoras(horaentrada, horasalida, esSabado);

        // Variable para nombre de turno bien
        const turnonom = resultado.turno.split(/[\s,;()\-\.]+/);
        var turnoCompleto = turnonom[0] + " " + turnonom[1];

        // Se valida que se tengan entradas y salidas
        if (turnonom[3] == undefined) {

            lblMensaje.hidden = false;

            lblMensaje.textContent = "No se pudo determinar entrada y salida correctamente.";
            Swal.fire({
                title: 'Resultados de validación:',
                html: `
                    Se recomienda eliminar este registro de empleado<br><br>
                    <b>No se pudo determinar entrada y salida correctamente. O si lo deseas consulta mas tarde para verificar si su registro fue realizado mas tarde.</b><br>
                `,
                icon: 'info'
            });
            lblMensaje.className = "alert alert-warning mt-2";
            lblMensaje.style.display = "block";

            // habilitar botón de eliminar de la fila
            const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);
            if (btnEliminarFila) btnEliminarFila.hidden = false;
            return;
        }

        function horaAMinutos(hora) {
            const partes = hora.split(":");
            return parseInt(partes[0]) * 60 + parseInt(partes[1]);
        }

        function minutosAHora(minutos) {
            const horas = Math.floor(minutos / 60);
            const mins = minutos % 60;
            return horas.toString().padStart(2, '0') + ":" + mins.toString().padStart(2, '0');
        }

        // Recuperar esta linea si la siguiente no funciona
        let horaExtra2 = "00:00:00";

        // Variable para identificar maximo de lapso en registros de entrada/salida  si solo existen una vez
        const comp = "00:05:00";
        
        if (resultado.totalHoras <= comp){

            lblMensaje.hidden = false;

            lblMensaje.textContent = "Solo se tiene un registro de entrada/salida para el empleado.";
            Swal.fire({
                title: 'Resultados de validación:',
                html: `
                    Se recomienda eliminar este registro de empleado<br><br>
                    <b>Solo se tiene un registro de entrada/salida para el empleado. Si lo deseas, consulta mas tarde para verificar si el registro de salida fue realizada mas tarde.</b><br>
                `,
                icon: 'info'
            });
            lblMensaje.className = "alert alert-warning mt-2";
            lblMensaje.style.display = "block";

            // habilitar botón de eliminar de la fila
            const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);
            if (btnEliminarFila) btnEliminarFila.hidden = false;
            return;
        }
        
        const horaExtrass = horaAMinutos(resultado.totalHoras);
        const horaReglamen = horaAMinutos(resultado.horasReglamentarias);

        if (horaExtrass >= horaReglamen) {
            const diff2 = horaExtrass - horaReglamen;
            horaExtra2 = minutosAHora(diff2);
        }

        const hrsReg = horaReglamen;
        // Verificar si se cumplen las horas extra
        validarTiempoExtra(horaExtra2, resultado.totalHoras, resultado.horasReglamentarias, folioRegistro, horasalida, horaExtra2, HoraInicio, turnoDetectado);
        // después de validar el registro, evaluar el folio completo
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

            // llenar select de folios
            const folioSelect = document.getElementById("folioSelect");
            folioSelect.innerHTML = "";
            folios.forEach(folio => {
                folioSelect.innerHTML += `<option value="${folio}">${folio}</option>`;
            });

            // generar botones de acción por folio
            const accionesContainer = document.getElementById("accionesGlobales");
            accionesContainer.innerHTML = ""; // limpiar antes de volver a pintar
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

            // registrar eventos dinámicos
            folios.forEach(folioId => {
                const btnAutorizar = document.getElementById(`btnAutorizar-${folioId}`);
                const btnRechazar = document.getElementById(`btnRechazar-${folioId}`);

                if (btnAutorizar) {
                    btnAutorizar.addEventListener("click", () => {
                        this.enviar(folioId, 1);
                    });
                }
                if (btnRechazar) {
                    btnRechazar.addEventListener("click", () => {
                        this.enviar(folioId, 2);
                    });
                }
            });

            // validaciones de fecha/hora por registro
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
                    btnValidar.hidden = false;
                    msg.textContent = "";
                    msg.dataset.estado = "pendiente";
                }
            });
        }
    }

    // Renderizacion de elementos dinamicos segun resultados de la BD
    renderRow(elemento) {
        let accionHtml = "";
        let accionTerminadoHtml = "";
        let badgeValidado = "";

        // Estados de terminado
        if (elemento.terminado === null || elemento.terminado === "") {
            // Caso pendiente: mostrar botón de validar SOLO si no está validado
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
                // Ya validado, no mostrar botón
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

        // Estados de validado
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
            <td>
                ${accionTerminadoHtml}<br>
            </td>
            <td>
                ${badgeValidado}
            </td>
            <td>
                ${accionHtml}
                <small id="msg-${elemento.folioRegistro}" class="d-block mt-1"></small>
            </td>
        </tr>`;
    }


    // Filtrar por folio seleccionado
    filtrarPorFolio(folioId) {
        const filtrados = this.data.filter(e => e.id == folioId);
        let body = "";
        filtrados.forEach(e => body += this.renderRow(e));
        document.getElementById("tblenc").innerHTML = body;

        // generar botones de acción solo para este folio
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

        // registrar eventos dinámicos
        const btnAutorizar = document.getElementById(`btnAutorizar-${folioId}`);
        const btnRechazar = document.getElementById(`btnRechazar-${folioId}`);

        if (btnAutorizar) {
            btnAutorizar.addEventListener("click", () => {
                this.enviar(folioId, 1);
            });
        }
        if (btnRechazar) {
            btnRechazar.addEventListener("click", () => {
                this.enviar(folioId, 2);
            });
        }

        // volver a aplicar validaciones de fecha/hora
        const ahora = new Date();
        filtrados.forEach(elemento => {
            const fechaSol = new Date(elemento.fechaSol + "T00:00:00");
            const [h, m, s] = elemento.HoraFin.split(":").map(Number);
            const horaFinDate = new Date(fechaSol);
            horaFinDate.setHours(h, m, s);

            // sumar 15 minutos al fin
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
                btnValidar.hidden = false;
                msg.textContent = "";
                msg.dataset.estado = "pendiente"; // hasta que se valide
            }
        });

        // evaluar el folio completo
        this.evaluarFolio(folioId);
    }

    // Funcion para eliminar datos segun correspondan si no cumplen los criterios correspondientes
    // Para ello se deben de eliminar los datos y conservar los unicos que sirvan
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


    // Funcion para autorizar el tiempo extra con parametros como el id y quien lo autoriza
    async enviar(id, autor){
        // Verificacion de que la firma exista antes de otra cosa
        const verificacion = await fetch('./php/verificar_firma.php')
            .then(res => res.json())
            .catch(() => null);

            // Manejo de casos
            if(!verificacion || !verificacion.success){
                Swal.fire({
                    icon: 'warning',
                    title: 'Firma no registrada',
                    text: 'Debes registrar tu firma primero antes de autorizar el tiempo extra.',
                    confirmButtonText: 'Ententido',
                    confirmButtonColor: '#f0ad4e'
                });
                // Devolvemos un corte aqui sin hacer el fetch a la autorizacion para evitar un envio de datos
                return;
            }
            
            const respuestaraw = await fetch("./php/index.php?autorizafol&id=" + id + "&autor=" + autor );
            const respuesta = await respuestaraw.json();

            // Manejo segun el caso de la respuesta
            respuesta === false ?
                        Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: respuesta.message || 'Hay un error con la base de datos.'
                        }) :
                        Swal.fire({
                                icon: 'success',
                                title: 'Autorizado',
                                text: 'El registro fue autorizado con exito',
                                timer: 2000,
                                showConfirmButton: false
                        });
                
                window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
                window.location.reload();
    }

    pdffin(id) {
        window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
    }

    // Funcion de evaluacion de registros por folio para saber cuando mostrar el boton
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
        const btnValidar = document.getElementById(`btnValidar-${folioRegistro}`);
        const btnEliminar = document.getElementById(`btnEliminar-${folioRegistro}`);
        const msg = document.getElementById(`msg-${folioRegistro}`);
        if (btnValidar) btnValidar.hidden = true;
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

            if (estado !== "apto" && estado !== "noapto") {
                todosAnalizados = false;
            }
            if (estado === "noapto") {
                todosAptos = false;
            }
        });

        const btnAutorizar = document.getElementById(`btnAutorizar-${folioId}`);
        const btnRechazar = document.getElementById(`btnRechazar-${folioId}`);

        if (todosAnalizados && todosAptos) {
            btnAutorizar.hidden = false;
            btnRechazar.hidden = false;
        } else {
            btnAutorizar.hidden = true;
            btnRechazar.hidden = true;
        }
    }

}

// Funcion para validar mas de 55 minutos
function validarTiempoExtra(horasExtra, totalHoras, horasReglamentarias, folioRegistro, horasalida, horasExtra2, HoraInicio, turnoDetectado){
    function horaAMinutos(hora) {
        const partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }

    function sumarHoras(horaInicio, horasExtra) {
        const [h1, m1] = horaInicio.split(":").map(Number);
        const [h2, m2] = horasExtra.split(":").map(Number);

        let totalMin = h1 * 60 + m1 + h2 * 60 + m2;

        // Normalizar al rango de 24 horas
        totalMin = totalMin % (24 * 60);

        const horas = Math.floor(totalMin / 60);
        const mins = totalMin % 60;

        return horas.toString().padStart(2, '0') + ":" + mins.toString().padStart(2, '0') + ":00";
    }


    const nuevaHoraFin = sumarHoras(HoraInicio, horasExtra2);

    const minutosExtra = horaAMinutos(horasExtra);
    const minutosTotal = horaAMinutos(totalHoras);
    const minutosReglamentarios = horaAMinutos(horasReglamentarias);

    const btnEliminarFila = document.getElementById(`btnEliminar-${folioRegistro}`);

    if (minutosTotal >= minutosReglamentarios && minutosExtra >= 55){
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
                .then(respuetaraw => respuetaraw.json())
                .then(respuesta => {
                    if (respuesta.success) {
                        const registro = window.Reportes.data.find(e => e.folioRegistro == folioRegistro);
                        if (registro) {
                            registro.HoraFin = nuevaHoraFin;
                            registro.validado = 1;
                        }

                        // Actualizar directamente la celda de la tabla
                        const fila = document.querySelector(`#msg-${folioRegistro}`).closest("tr");
                        if (fila) {
                            // actualizar la columna de estatus
                            const estatusCell = fila.querySelector("td:nth-child(13)");
                            if (estatusCell) {
                                if (registro.validado == 1) {
                                    estatusCell.innerHTML = `
                                        <span class="badge bg-success">Validado</span>
                                    `;
                                } else {
                                    estatusCell.innerHTML = `
                                        <span class="badge bg-warning">No validado aun</span>
                                    `;
                                }
                            }

                            const accionesCell = fila.querySelector("td:nth-child(14)");
                            if (accionesCell) {
                                if (registro.validado == 1) {
                                    accionesCell.innerHTML = `
                                        <span class="badge bg-success">Ya validado</span>
                                        <small id="msg-${folioRegistro}" class="d-block mt-1"></small>
                                    `;
                                } else {
                                    accionesCell.innerHTML = `
                                        <button class="btn btn-sm btn-warning" 
                                                onclick="validarInfo('${registro.NoEmpleadoSol}','${registro.fechaSol}','${registro.folioRegistro}','${registro.HoraInicio}')"
                                                id="btnValidar-${registro.folioRegistro}">
                                            <i class="fa-solid fa-eye"></i> Validar T. extra
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="window.deletesub(${registro.folioRegistro})" 
                                                id="btnEliminar-${registro.folioRegistro}" hidden>
                                            <i class="fas fa-times"></i> Eliminar
                                        </button>
                                        <small id="msg-${folioRegistro}" class="d-block mt-1"></small>
                                    `;
                                }
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
                        Swal.fire(
                            'Error en actualización',
                            `No se pudo actualizar la Hora Fin. Detalle: ${respuesta.error}`,
                            'error'
                        );
                    }
                })

                .catch(err => {
                    console.error("Error al actualizar Hora Fin:");
                });
            }
        });
        if (btnEliminarFila) btnEliminarFila.hidden = true;
    } else {
        window.Reportes.marcarRegistroComoNoApto(folioRegistro);
        Swal.fire(
            'Resultados de validación: ',
            `No se cumplen los requisitos para solicitar un tiempo extra.`,
            'info'
        );
        if (btnEliminarFila) btnEliminarFila.hidden = false;
    }

    // después de marcar el registro, evaluar el folio completo
        const folioId = window.Reportes.data.find(e => e.folioRegistro == folioRegistro).id;
    window.Reportes.evaluarFolio(folioId);
    
}

// Funcion principal para validar/identificar el turno actual segun la hora de entrada y de salida
function calcularTurnoYHoras(horaEntrada, horaSalida, esSabado = false) {
    function horaAMinutos(hora) {
        var partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }

    function minutosAHora(minutos) {
        var horas = Math.floor(minutos / 60);
        var mins = minutos % 60;
        return horas.toString().padStart(2, '0') + ":" + mins.toString().padStart(2, '0');
    }

    var entradaMin = horaAMinutos(horaEntrada);
    var salidaMin = horaAMinutos(horaSalida);

    // Si la salida es menor que la entrada, cruza inmediatamente
    if (salidaMin < entradaMin) {
        salidaMin += 1440;
    }

    // Variables para identificacion de turno y horas
    var horaInicioTurno, horaFinTurno, nombreTurno, horasReglamentarias;
    // Margen de tolerancia en tiempos de entrada y salida
    var margen = 35;

    // Definicion de turnos
    var turnos = {
        turno1: { 
            inicio: 7 * 60, 
            fin: 15 * 60, 
            nombre: "Turno 1 (07:00:00 - 15:00:00)",
            duracion: "08:00:00"
        },
        turno2: { 
            inicio: 15 * 60, 
            fin: 22 * 60 + 30, 
            nombre: "Turno 2 (15:00:00 - 22:30:00)",
            duracion: "07:30:00" 
        },
        turno3: { 
            inicio: 22 * 60 + 30, 
            fin: 7 * 60 + 10, 
            nombre: "Turno 3 (22:30:00 - 07:00:00)",
            duracion: "08:30:00"
        },
        mixto1: { 
            inicio: 7 * 60 + 10, 
            fin: 17 * 60 + 10, 
            nombre: "Mixto 1 (07:00:00 - 17:00:00)" ,
            duracion: "10:00:00"
        },
        mixto2: { 
            inicio: 8 * 60 + 30, 
            fin: 18 * 60 + 30, 
            nombre: "Mixto 2 (08:30:00 - 18:30:00)" ,
            duracion: "10:00:00"
        },
        mixto3: { 
            inicio: 7 * 60, 
            fin: 16 * 60 + 30, 
            nombre: "Mixto 3 (07:00:00 - 16:30:00)" ,
            duracion: "09:30:00"
        }
    };

    // Deteccion de turno validando entrada y salida
    var turnoDetectado = null;

    // Asignacion y verificacion de turnos (mas estricta y precisa)
    for(var key in turnos){
        var turno = turnos[key];
        var entradaCercana = Math.abs(entradaMin - turno.inicio) <= margen;

        // Para turnos que cruzan a medianoche se ajusta un fin
        var finTurnoAjustado = turno.fin;
        if(turno.fin > 1440 ){
            finTurnoAjustado = turno.fin;
        }

        // Se le asigna un margen mas amplio
        var salidaCercana = Math.abs(salidaMin - finTurnoAjustado) <= margen * 4;

        // Si la entrada coincide y la salida esta cerca del fin del turno es ese turno
        if (entradaCercana && salidaCercana) {
            turnoDetectado = key;
            break;
        }
    }

    // Si no se detecto por entra a y salida se intenta solo por entrada
    // Por prioridad a los turnos mas especificos como mixtos
    if (!turnoDetectado){
        var prioridad = ['mixto1', 'mixto2', 'turno1', 'turno2', 'mixto3', 'turno3'];

        for (var i = 0; i < prioridad.length; i++){
            var key = prioridad[i];
            var turno = turnos[key];

            if(Math.abs(entradaMin - turno.inicio) <= margen){
                turnoDetectado = key;
                break;
            }
        }
    }

    // Asignacion de valores segun el turno
    if(turnoDetectado === 'turno1'){
        horaInicioTurno = minutosAHora(turnos.turno1.inicio);
        horaFinTurno = minutosAHora(turnos.turno1.fin);
        nombreTurno = turnos.turno1.nombre;
        horasReglamentarias = turnos.turno1.duracion;
    } 
    else if(turnoDetectado === 'turno2'){
        horaInicioTurno = minutosAHora(turnos.turno2.inicio);
        horaFinTurno = minutosAHora(turnos.turno2.fin);
        nombreTurno = turnos.turno2.nombre;
        horasReglamentarias = turnos.turno2.duracion;
    }
    else if(turnoDetectado === 'turno3'){
        horaInicioTurno = minutosAHora(turnos.turno3.inicio);
        horaFinTurno = minutosAHora(turnos.turno3.fin);
        nombreTurno = turnos.turno3.nombre;
        horasReglamentarias = turnos.turno3.duracion;
    }
    else if(turnoDetectado === 'mixto1'){
        horaInicioTurno = minutosAHora(turnos.mixto1.inicio);

        if (esSabado) {
            horaFinTurno = minutosAHora(turnos.mixto1.inicio + 5 * 60);
            nombreTurno = "MIXTO SABADO (07:30:00 - 12:30:00)";
            horasReglamentarias = "05:00:00";
        } else {
            horaFinTurno = minutosAHora(turnos.mixto1.fin);
            nombreTurno = turnos.mixto1.nombre;
            horasReglamentarias = turnos.mixto1.duracion;
        }
    }

    else if(turnoDetectado === 'mixto2'){
         horaInicioTurno = minutosAHora(turnos.mixto2.inicio);

        if (esSabado) {
            horaFinTurno = minutosAHora(turnos.mixto2.inicio + 5 * 60);
            nombreTurno = "MIXTO SABADO (08:30:00 - 13:30:00)";
            horasReglamentarias = "05:00:00";
        } else {
            horaFinTurno = minutosAHora(turnos.mixto2.fin);
            nombreTurno = turnos.mixto2.nombre;
            horasReglamentarias = turnos.mixto2.duracion;
        }
    } 

    else if(turnoDetectado === 'mixto3'){
        horaInicioTurno = minutosAHora(turnos.mixto3.inicio);
        
        if (esSabado) {
            horaFinTurno = minutosAHora(turnos.mixto3.inicio + 5 * 60);
            nombreTurno = "MIXTO SABADO (19:00:00 - 04:30:00)";
            horasReglamentarias = "05:00:00";
        } else {
            horaFinTurno = minutosAHora(turnos.mixto3.fin);
            nombreTurno = turnos.mixto3.nombre;
            horasReglamentarias = turnos.mixto3.duracion;
        }
    }  
    else {
        // Caso de que no se encuentren turnos
        horaInicioTurno = horaEntrada;
        horaFinTurno = "00:00:00";
        nombreTurno = "No hay";
        horasReglamentarias = "00:00:00";
    }

    // Calcular horas trabajadas
    var minutosTrabajados = salidaMin - entradaMin;

    // Calcular fin de turno esperado (minutos)
    var finTurnoMin;
    if (nombreTurno.includes("Turno 1")) finTurnoMin = turnos.turno1.fin;
    else if (nombreTurno.includes("Turno 2")) finTurnoMin = turnos.turno2.fin;
    else if (nombreTurno.includes("Turno 3")) finTurnoMin = turnos.turno3.fin;
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

    // Calculo de horas extra
    var horasExtrasMin = salidaMin - finTurnoMin;
    var horasExtras = horasExtrasMin > 0 ? minutosAHora(horasExtrasMin) : "00:00:00";

    // Calcular total de horas trabajadas
    var totalHoras = minutosAHora(minutosTrabajados);

    //var salidaMin = horaAMinutos(horaSalida)

    return {
        turno: nombreTurno,
        horaInicioTurno: horaInicioTurno,
        horaFinTurno: horaFinTurno,
        horasExtras: horasExtras,
        totalHoras: totalHoras,

        salidaMin: salidaMin,
        entradaMin: entradaMin,
        finTurnoMin: finTurnoMin,
        horaSalida: horaSalida,
        horaEntrada: horaEntrada,
        horasReglamentarias: horasReglamentarias
    };
}

// Instancia de clase Reportes para uso global
window.Reportes = new Reportes();
window.Reportes.consulta();

// Eventos para la seleccion del folio

const folioSelect = document.getElementById("folioSelect");
folioSelect.addEventListener("change", () => {
    const folio = folioSelect.value;
    if (folio) {
        window.Reportes.filtrarPorFolio(folio);
    }
});

// Cambiar metodo actua de validarInfo para hacer todo lo que ya se hace en index.js en 
// getinfoHoraEntradaySalida con los datos recuperados de si se cumple o no el tiempo extra
window.validarInfo = function(noEmpSol, fechaSol, folioRegistro, HoraInicio) {
    window.Reportes.getinfohoraentradaysalida(noEmpSol, fechaSol, folioRegistro, HoraInicio);
}

window.deletesub = function(id) {
    window.Reportes.deletesub(id);
}

window.Autoriza = function (id) {
    window.Reportes.enviar(id, 1);
}

window.Rechazar = function (id) {
    window.Reportes.enviar(id, 2);
}

window.pdfFin = function (id) {
    window.Reportes.pdffin(id);
}
