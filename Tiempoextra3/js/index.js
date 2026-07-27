import { Toolsjs } from "../../Tools/Tools.js";

class Tiempoextra {

    inicio() {
        this.Tools = new Toolsjs();
        
        setInterval(this.Tools.mostrarHoraSimple(), 1000);
        this.Tools.llnarslcruta("php/index.php?motivostiempoextra", "motivos");
        //Tools.llnarslc('CatalogoPersonal', "GetSlcMaquinas", "maquinas", 0);
        this.Tools.llnarslc('CatalogoPersonal', "GetSlcDeps", "departamentoenc", 0);
        this.tblenc();
    }

    // async getinfoemp(noemp) {
    //     const respuetaraw = await fetch("../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp);
    //     const respuesta = await respuetaraw.json();
    //     if (respuesta.length === 0) {
    //         document.getElementById("nombre").value = "";
    //         document.getElementById("departamento").value = "";
    //         document.getElementById("puesto").value = "";
    //         return
    //     }
    //     document.getElementById("nombre").value = respuesta[0].nombre;
    //     document.getElementById("departamento").value = respuesta[0].departamento;
    //     document.getElementById("puesto").value = respuesta[0].puesto;        
    // }

    async getinfoemp(noemp) {
        const respuetaraw = await fetch("../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp);
        const respuesta = await respuetaraw.json();
        if (respuesta.length === 0) {
            document.getElementById("nombre").value = "";
            document.getElementById("departamento").value = "";
            document.getElementById("puesto").value = "";
            return;
        }
        document.getElementById("nombre").value = respuesta[0].nombre;
        // document.getElementById("departamento").value = respuesta[0].departamento;
        document.getElementById("departamento").value = respuesta[0].departamento.trim();
        document.getElementById("puesto").value = respuesta[0].puesto;            
        
        document.getElementById("departamento").dispatchEvent(new Event("change"));
    }

    // Obtener hora de entrada y de salida by numero de empleado
    async getinfohoraentradaysalida(noemp, date) {
        var fechaActual = new Date(date + "T00:00:00");

        var fechaAnterior = new Date(fechaActual);
        fechaAnterior.setDate(fechaAnterior.getDate() - 1);

        var fechaAnteriorStr = formatearFecha(fechaAnterior);

        var fechaSiguiente = new Date(fechaActual);
        fechaSiguiente.setDate(fechaSiguiente.getDate() + 1);
        var fechaSiguienteStr = formatearFecha(fechaSiguiente);

        // Formateo de fecha
        function formatearFecha(fecha) {
            var año = fecha.getFullYear();
            var mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
            var dia = fecha.getDate().toString().padStart(2, '0');
            return año + "-" + mes + "-" + dia;
        }

        // Verificacion de fechas obtenidas
        const respuestaAnteriorRaw = await fetch("../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=" + noemp + "&fechabien=" + fechaAnteriorStr);
        const respuestaAnterior = await respuestaAnteriorRaw.json();

        const respuestaActualRaw = await fetch("../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=" + noemp + "&fechabien=" + date);
        const respuestaActual = await respuestaActualRaw.json();

        const respuestaSiguienteRaw = await fetch("../Components/CatalogoSeguridad.php?datoshoraysalida&noemp=" + noemp + "&fechabien=" + fechaSiguienteStr);
        const respuestaSiguiente = await respuestaSiguienteRaw.json();
        
        // **************************************************************************************************
        // Primera validacion para verificar que el empleado tenga registros de horas en el dia especificado
        // **************************************************************************************************
        function agruparPorHora(registros) {
            var horasVistas = {};
            var resultado = [];

            registros.forEach(function (registro) {
                if (registro.fecha_h) {
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
                entrada: { min: 7 * 60 + 20, max: 9 * 60 },
                salida: { min: 15 * 60 + 30, max: 19 * 60 },
                horaIdealEntrada: "07:30:00",
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
            },
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

        function analizarCasoEspecial(registrosLimpios){
            if(registrosLimpios.length){
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

                var hayGapLargo = gaps.some(g => g.minutos >= 6*60 && g.minutos <= 10*60);
                if(hayGapLargo) {
                    return {
                        tipo: "doble_turno",
                        entrada: registrosLimpios[0], 
                        salida: registrosLimpios[registrosLimpios.length - 1]
                    };
                }

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

        var entrada, salida, turnoDetectado;
        
        var casoEspecial = analizarCasoEspecial(registrosActualLimpios);

        // Primera validacion
        if(casoEspecial){
            entrada = casoEspecial.entrada;
            salida = casoEspecial.salida;
        }

        else if(registrosActualLimpios.length >= 2){
            var primeraHoraActual = registrosActualLimpios[0];
            var ultimaHoraActual = registrosActualLimpios[registrosActualLimpios.length - 1];

            var primeraMin = horaAMinutos(primeraHoraActual.hora_limpia);
            var ultimaMin = horaAMinutos(ultimaHoraActual.hora_limpia);

            turnoDetectado = identificarTurno(primeraMin, ultimaMin);

            if (turnoDetectado){
                entrada = primeraHoraActual;
                salida = ultimaHoraActual;
            }
            else {
                var segundaMin = ultimaMin;

                if(estaEnRango(segundaMin, turnos.turno3.entrada) || estaEnRango(segundaMin, turnos.mixto3.salida)){
                    // Asignaicion a la entrada el valor de la ultima hora actual
                    entrada = ultimaHoraActual;

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
        
        // Solo registros en el dia actual
        if (registrosActualLimpios.length > 0) {

            // Caso 1a: 2 o mas en el dia actual
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
                    // la segunda hora representaria la entrada del dia/turno actual
                    // Primero verificamos que la segunda hora coincide con la entrada de turno nocturno
                    var segundaMin = ultimaMin;

                    if (estaEnRango(segundaMin, turnos.turno3.entrada) || estaEnRango(segundaMin, turnos.mixto3.entrada)) {
                        // Caso de que la segunda hora es entrada de turno nocturno
                        // La hora nocturna es la entrada
                        entrada = ultimaHoraActual;

                        // Caso 1A - Primera hora es salida anterior, segunda entrada es nocturna
                        if (registrosActualLimpios.length > 0) {
                            salida = registrosSiguienteLimpios[0];
                        } else {
                            // No hay salida registrada
                        }
                    } else {
                        // Caso de que no se pueda identificar el turno
                        // No se pudo identificar el turno con los registros del dia actual, o el turno no ha terminado o solo existe un registro en el dia actual y no hay anteriores/posteriores para hacer la identificacion del turno
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
                        // No hay salida regisrada
                    }
                }

                // Verificar si es salida de turno nocturno del dia anterior
                else if (estaEnRango(unicaMin, turnos.turno3.salida) || estaEnRango(unicaMin, turnos.mixto3.salida)) {
                    //es salida, buscar entrada en dia anterior
                    salida = unicaHoraActual;

                    // Caso 1b: Salida en dia actual, entrada en dia anterior
                    if (registrosAnteriorLimpios.length > 0) {
                        entrada = registrosAnteriorLimpios[registrosAnteriorLimpios.length - 1];
                    } else {
                        // No hay entrada en dia anteror
                    }
                }
                else {
                    // No hay salida regisrada
                }
            }
        }
        else {
            // No hay registros en el dia actual
        }

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
       
        // Asignacion de pruebas para validar que el turno de salida segun el turno identificado se asigne como hora de inicio del turno extra

        // document.getElementById("horaER").value = horaentrada;
        // document.getElementById("horaSR").value = horasalida;

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
            return;
        }
        
        const horaExtrass = horaAMinutos(resultado.totalHoras);
        const horaReglamen = horaAMinutos(resultado.horasReglamentarias);

        if (horaExtrass >= horaReglamen) {
            const diff2 = horaExtrass - horaReglamen;
            horaExtra2 = minutosAHora(diff2);
        }

        const hrsReg = horaReglamen;

        document.getElementById("totalHoras").value = resultado.totalHoras;
        document.getElementById("turno").value = turnoCompleto;

        // Verificar si se cumplen las horas extra
        validarTiempoExtra(horaExtra2, resultado.totalHoras, resultado.horasReglamentarias);
        
        document.getElementById("horaEX").value = horaExtra2;
        document.getElementById("hrsReg").value = resultado.horasReglamentarias;

    }

    // Funcion para abrir el tiempo extra y creacion de registro
    async abrirtiempoextra() {
        let folio = document.getElementById("folio").value;
        let fechaenc = document.getElementById("fechaenc").value;
        let departamentoenc = document.getElementById("departamentoenc").value;
        // Validacion de que fecha de creacion de turno extra y el campo de departamento esten completos para generar el folio
        if (fechaenc === "" || departamentoenc === "") {
            Swal.fire('UPS!!!', 'No puede haber campos vacíos', 'info');
            return false;
        }
        else if (folio != "") {
            Swal.fire('UPS!!!', 'Estas editando un folio, selecciona empezar de nuevo para crear un nuevo folio', 'info');
            return false;
        }

        // Recuperacion de fechas y departamento
        const data = new FormData();
        data.append("fechaenc", fechaenc);
        data.append("departamentoenc", departamentoenc);
        try {
            const respuetaraw = await fetch("php/index.php?abrirtiempoextra", {
                method: "POST",
                body: data
            });            
            const respuesta = await respuetaraw.json();

            if (respuesta.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo registrar el folio',
                    text: respuesta.error,
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Mostrar mensaje con el folio
            Swal.fire('Listo!!!', 'Empieza a cargar los tiempos extra al folio ' + respuesta.id, 'success');

            // Guardar folio y semana en campos ocultos
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

    // Funcion para guardar los detalles del turno extra
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

        // Valor de la semana
        let semanaFolio = document.getElementById("semanaFolioHidden").value;

        // Calcular semana de la fecha seleccionada
        let fechaObj = new Date(fechainput);
        let semanaRegistro = getWeekNumber(fechaObj);

        //console.log("semana registro: " + semanaRegistro);
        //console.log("semana folio: " + semanaFolio);
        // if (semanaRegistro != semanaFolio) {
        //     Swal.fire('UPS!!!', 'La fecha seleccionada no pertenece al rango semanal del folio !', 'warning');
        //     return;
        // }

        if (semanaRegistro < (semanaFolio - 1) ) {
            Swal.fire(
                'UPS!!!',
                'La fecha seleccionada pertenece a una semana demasiado antigua respecto al folio (más de 1 semana atrás).',
                'warning'
            );
            return;
        } else if (semanaRegistro > (semanaFolio + 1)) {
            Swal.fire(
                'UPS!!!',
                'La fecha seleccionada pertenece a una semana muy adelantada respecto al folio (más de 1 semana adelante).',
                'warning'
            );
            return;
        }

        let turnoSeleccionado = document.getElementById("turnoSeleccionadoHidden").value;
        let horaFinalSinMargen = document.getElementById("horaFinalSinMargenHidden").value;
        let horaFinalConMargen = document.getElementById("horaFinalConMargenHidden").value;
        let estado = document.getElementById("estadoHidden").value;
        
        // Manejo para select en caso de que el motivo sea un cambio de horario
        // if (motivos == 8) {
        //     let hra = document.getElementById("cambiohrario").value;
        //     // Se asignan horas de inicio y de fin si es 3
        //     if (hra == 3) {
        //         horai = "01:00:00";
        //         horaf = "04:00:00";
        //     }
        //     // Se asignan horas de inicio y de fin si es 5 
        //     else {
        //         horai = "01:00:00";
        //         horaf = "06:00:00";
        //     }
        // }

        if (motivos == 8) {
            let hra = document.getElementById("cambiohrario").value;
            if (hra == 3) {
                horai = "01:00:00";
                horaf = "04:00:00";
            } else {
                horai = "01:00:00";
                horaf = "06:00:00";
            }
            // Forzar turno vacío o un valor fijo
            turnosel = "";
        }

        // Se adjuntan registros o valores segun los obtenidos del form
        // El folio se toma del que se crea primero al seleccionar la fecha y un departamento
        const data = new FormData();
        const dataCSV = new FormData();

        const TiempoEStr = document.getElementById("duracionTE").value;
        const duracionTiempoE = TiempoEStr ? horaAMinutos(TiempoEStr) : 0;
        const activaTurno3_12hrs = document.getElementById("checkboxTurno3").checked;

        // Validaciones de campos vacios
        if(folio === ""){
            Swal.fire('Error !', 'Necesitas crear un folio primero', 'warning');
            return;
        }
        // else if (noemp === "" || fechainput === "" || horai === "" || horaf === "" || motivos === "" || maquina === "" || turnoSeleccionado === "") {
        //     Swal.fire('Error', 'No puede haber campos vacíos', 'info');
        //     return;
        // }                
        // Validación de campos vacíos
        else if (noemp === "" || fechainput === "" || maquina === "" || motivos === "" || razon === "" || folio === "" || nombre === "") {
            Swal.fire('Error', 'No puede haber campos vacíos', 'info');
            return;
        }

        // Validación de máximo 4 horas en turno2 y turno3
        else if (duracionTiempoE > 240 &&(turnoSeleccionado === "turno3" || turnoSeleccionado === "turno2")) {            
            if (parseInt(motivos) === 10 || parseInt(motivos) === 12) {
                
            } else {
                // Para cualquier otro motivo, mostrar error
                Swal.fire('Error', 'No puedes agregar más de 4 hrs en el turno seleccionado', 'info');
                return;
            }
        }

        // Validación de activar casilla de 12 hrs después de 3:30 hrs
        else if (duracionTiempoE >= 210 && (turnoSeleccionado === "turno3" || turnoSeleccionado === "turno2") && !activaTurno3_12hrs && (motivos == 10 || motivos == 12)) {
            Swal.fire('Error', 'Después de 3:30 hrs en este turno debes activar la casilla de 12 hrs para guardar el registro.', 'info');
            return;
        }

        // Asignación de turnos especiales de 12 hrs
        if (activaTurno3_12hrs && turnoSeleccionado === "turno3") {
            turnosel = "turno3_12hrs";
        } else if (activaTurno3_12hrs && turnoSeleccionado === "turno2") {
            turnosel = "turno2_12hrs";
        }
        
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
        dataCSV.append("estado", estado);
        
        // Aqui se crea y se envia al supervisor los tiempos extra solicitados paa su autorizacion
        const respuetaraw = await fetch("php/index.php?guardartiempoextra", {
            method: "POST",
            body: data
        });

        const respuetarawcsv = await fetch("php/guardarCSV.php?guardarCSV", {
            method: "POST",
            body: dataCSV
        });

        // Manejo de respuesta segun el retorno del JSON new
        const respuesta = await respuetaraw.json();

        if (respuesta.warning) {
            Swal.fire({
                title: 'Advertencia',
                text: respuesta.message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'No, cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    
                    const confirmRaw = await fetch("php/index.php?guardartiempoextraExt", {
                        method: "POST",
                        body: data
                    });
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

        // Funcion extra para limpiar informacion despues de registrar a un empleado
        function cleanData(){
            const horariosDeTurno = document.getElementById("horariosDeTurno");

            document.getElementById("noemp").value = "";
            document.getElementById("fechainput").value = "";
            document.getElementById("horai").value = "";
            document.getElementById("horaf").value = "";
            document.getElementById("maquinas").value = "";
            document.getElementById("motivos").value = "";
            document.getElementById("razon").value = "";
            document.getElementById("turnosel").value = "";
            document.getElementById("nombre").value = "";
            document.getElementById("departamento").value = "";
            document.getElementById("puesto").value = "";
            document.getElementById("duracionTE").value = "";            
            horariosDeTurno.style.display = "none";
        }

        // Manejo de datos segun respuesta old
        /*
        const respuesta = await respuetaraw.json();
        if (respuesta.warning) {
            Swal.fire('UPS!!!', 'Se alcanzó el límite de 60 horas en esta semana, espera a la siguiente semana', 'warning');
            return;
        } else if (respuesta === "Listo") {
            Swal.fire('Listo!!!', 'Registro guardado y enviado con éxito', 'success');
        } else if (respuesta === "Existe") {
            Swal.fire('UPS!!!', 'Ya tienes un tiempo extra existente', 'error');
        } else {
            Swal.fire('ERROR!!!', 'Error al guardar en la base de datos, contacta al administrador', 'error');
        }
        */

        // Manejo de respuesta segun el retorno del JSON para creacion de CSV
        const respuestaCSV = await respuetarawcsv.json();
        if (respuestaCSV === "CSV Guardado") {
        }

        
    }
    
    // Funcion para eliminar los registros de detalles en las solicitudes realizadas
    // Funcion asincrona para eliminar registros por ID
    async deletesub(id) {
        const data = new FormData();
        data.append("id", id);
        const respuestaraw = await fetch("./php/index.php?deletesub", {
            method: "POST",
            body: data
        });
        const respuesta = await respuestaraw.json();
        this.tblsubenc();
        respuesta === "Listo" ? Swal.fire('Listo!!!', 'Registro eliminado', 'success') : Swal.fire('ERROR!!!', 'Hay un problema al eliminar', 'error');
    }

    /*
    // Funcion asincrona para el envio de datos y creacion de PDF
    // Insercacion de datos en la tabla de tblMXPRCamTemTurno
    async cambioTempTurno(noemp){
        abrirModal();
        
        console.log("Numero de empleado recuperado segun el boton: ", noemp);

        let fecha_emision = document.getElementById("fecha_emision").value;
        let departamento = document.getElementById("departamento").value;
        let de_area = document.getElementById("de_area").value;
        let nombre_receptor = document.getElementById("nombre_receptor").value;
        let tripulacion = document.getElementById("tripulacion").value;
        let rol = document.getElementById("rol").value;
        let fecha_inicio = document.getElementById("fecha_inicio").value;
        let hora_presentacion = document.getElementById("hora_presentacion").value;
        let duracion_horas = document.getElementById("duracion_horas").value;
        let horario_desde = document.getElementById("horario_desde").value;
        let horario_hasta = document.getElementById("horario_hasta").value;
        let hasta_tripulacion = document.getElementById("hasta_tripulacion").value;
        let descansos = document.getElementById("descansos").value;
        let dias_adicionales = document.getElementById("dias_adicionales").value;

        // Adjuntar datos para fetch
        const data = new FormData();

        data.append("fecha_emision", fecha_emision);
        data.append("departamento", departamento);
        data.append("de_area", de_area);
        data.append("nombre_receptor", nombre_receptor);
        data.append("tripulacion", tripulacion);
        data.append("rol", rol);
        data.append("fecha_inicio", fecha_inicio);
        data.append("hora_presentacion", hora_presentacion);
        data.append("duracion_horas", duracion_horas);
        data.append("horario_desde", horario_desde);
        data.append("horario_hasta", horario_hasta);
        data.append("hasta_tripulacion", hasta_tripulacion);
        data.append("descansos", descansos);
        data.append("dias_adicionales", dias_adicionales);

        // Validacion en caso de que los campos esten vacios
        if (fecha_emision === "" || departamento === "" || de_area === "" || nombre_receptor === "" || hora_presentacion === "" || duracion_horas === "" || horario_hasta === "" || horario_desde === "") {
            Swal.fire('Alerta', 'Debes escribir en todos los campos', 'info');
        }

        // Enviar al backend
        const response = await fetch("php/index.php?guardarCambioTurno", {
            method: "POST",
            body: data
        });

        const result = await response.json();
        if(result.success){
            Swal.fire('Guardado', 'El cambio temporal de turno fue registrado', 'success');
        } else {
            Swal.fire('Error', result.message || 'No se pudo guardar', 'error');
        }

    }
    */

    // Funcion de abrir el modal con los datos
    async cambioTempTurno(noemp, estadoTurno, nombre, depto, id){
        abrirModal();
        const nombreFull = nombre + " - " + noemp;
        document.getElementById("nombre_receptor").value = nombreFull;
        document.getElementById("turno_presentacion").value = estadoTurno;
        document.getElementById("Depto_m").value = depto;
        document.getElementById("folioTiempoExtra").value = id;
    }


    // Funcion que refresca la pagina y carga los datos en forma de tabla para ver que datos fueron cargados
    async tblsubenc() {
        let folio = document.getElementById("folio").value;
        if (folio === "") {
            return false;
        }
        const respuetaraw = await fetch("php/index.php?tblsubenc&folio=" + folio);
        const respuesta = await respuetaraw.json();
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
            body += `                    
                <button class="btn btn-sm btn-primary" onclick="verPDF(${elemento.Ctt_id})">
                    <i class="fa-solid fa-file-pdf"></i> 
                    Ver PDF Cambio T. Turno
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteSub(${elemento.id})">
                    <i class="fa-solid fa-trash"></i> 
                        Eliminar 
                </button>
                `;
        } else {
            body += `<button class="btn btn-sm btn-success" onclick="cambioTempTurno(${elemento.noemp}, '${elemento.estadoTurno}', '${elemento.nombre}', '${elemento.depto}', '${elemento.id}')">
                        <i class="fa-solid fa-plus"></i>
                        Crea un Cambio T. Turno
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteSub(${elemento.id})">
                    <i class="fa-solid fa-trash"></i> 
                        Eliminar 
                </button>
                    `;
        }

        body += `</td></tr>`;
    });
    document.getElementById("tbltiempoextra").innerHTML = body;
    }

    // Funciona para la primer tabla cuando se selecciona (Ver folios) para ello se cargan los datos de registros o folios creados
    async tblenc() {
        // Obtencion de datos segun fetch
        const respuetaraw = await fetch("php/index.php?tblenc");
        const respuesta = await respuetaraw.json();
        let body = "";
        respuesta.forEach(elemento => {
            body += `<tr><td>${elemento.id}</td><td>${elemento.fecha}</td><td>${elemento.departamento}</td>
            <td>${elemento.creado}</td><td>`
            if (elemento.terminado == null) {
                body += `<button class="btn btn-sm btn-warning" onclick="editEnc(${elemento.id})"><i class="fa-solid fa-pen-to-square"> </i> Crear/eliminar registros </button> `;
            } 
            // En el caso de que ya se haya finalizado la solicitud o ya se contenga un valor, este solo muestra el PDF del resultado sin posibilidad a crear o eliminar mas registros
            else {
                body += `<button class="btn btn-sm btn-danger" onclick="pdfFin(${elemento.id})"> <i class="fa-solid fa-file-pdf"> </i> Descargar resultado PDF </button></td>`;
            }

        });
        // Inyeccion de la tabla en el HTML
        document.getElementById("tblenc").innerHTML = body;
    }


    async editenc(id) {
        const respuetaraw = await fetch("php/index.php?editenc&id=" + id);
        const respuesta = await respuetaraw.json();

        let foliodom = document.getElementById("folio");
        let fechaencdom = document.getElementById("fechaenc");
        let departamentoencdom = document.getElementById("departamentoenc");

        foliodom.value = respuesta[0].id;
        fechaencdom.value = respuesta[0].fecha;
        departamentoencdom.value = respuesta[0].departamento;
        fechaencdom.disabled = true;
        departamentoencdom.disabled = true;

        // Guardar semana en hidden
        document.getElementById("semanaFolioHidden").value = respuesta[0].semana;

        // Llamada para crear la tabla recorriendo las respuestas obtenidas
        this.tblsubenc();
    }


    // ANTIGUA FUNCION PARA EL BOTON DE ENVIAR
    // En esta funcion se guarda la solicitud y se "ENVIA" al supervisor para su solicitud de aprobacion o rechazo
    // Acciones para evento de enviar turno extra a autorizar 
    enviar(id) {
        // Alerta con sweet alert para confirmar que se va a crear y finzalizar el turno extra
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas enviar este tiempo extra a autorizacion del supervisor?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, seguro!',
            cancelButtonText: 'No, cancela!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                (async () => {
                    // Llamada a fetch para actualizar el estado de --terminado a 1 para indicar que ya esta autorizado
                    // Solo hace un update a la tabla para pasar de 0 a 1 para indicar que esta autorizado
                    const respuestaraw = await fetch("./php/index.php?enviarfol&id=" + id);
                    const respuesta = await respuestaraw.json();
                    respuesta === false ?
                        Swal.fire('ERROR!', 'Hay un error con la base de datos', 'error') :
                        Swal.fire('Terminado!', 'La solicitud fue enviada al area correspondiente, visualiza la respuesta en el campo estado en la tabla de abajos', 'success');
                    // Finalmente se crea u reporte PDF con los datos recuperados de los tiempos extra autorizados
                    // Archivo 'estatico' que trabaja con los datos pasados por parametro como el id
                    window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
                    window.location.reload();
                })();
            }
        })
    }

    // Funcion para abrir un PDF con base en un ID
    pdffin(id) {
        window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
    }
} 

// Obtener el numero de la semana
// function getWeekNumber(d) {
//     d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
//     d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
//     let yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
//     let weekNo = Math.ceil((((d - yearStart) / 86400000) + 1)/7);
//     return weekNo;
// }

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
    const yearStart = new Date(tempDate.getFullYear(), 0, 1);
    const weekNumber = Math.ceil((((tempDate - yearStart) / 86400000) + 1) / 7);
    return weekNumber;
}
    
//FUNCIONES PARA EL CALCULO DE HORAS EN TIEMPOS EXTRA    

// Funcion para validar mas de 55 minutos
function validarTiempoExtra(horasExtra, totalHoras, horasReglamentarias){
    function horaAMinutos(hora) {
        const partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }
    
    // Validaciones para considerar  si se cumplen las horas reglamentarias y si esas horas reglamentarias superan los 55 minutos extra para ser T. extra
    const minutosExtra = horaAMinutos(horasExtra);
    const minutosTotal = horaAMinutos(totalHoras);
    const minutosReglamentarios = horaAMinutos(horasReglamentarias);
}

// Instancia de metodos y clases
Tiempoextra = new Tiempoextra();
Tiempoextra.inicio();

// Crea un registro de tiempo extra con la fecha y el departamento
document.getElementById("abrir").addEventListener("click", function (event) {
    event.preventDefault();
    // De la clase de TiempoExtra se llama al metodo de abrirtiempoextra y se crea el registro
    Tiempoextra.abrirtiempoextra().then(() => {
        // Creacion de tablas con informacion del turno extra como saber si esta autorizado o no
        Tiempoextra.tblsubenc();
        Tiempoextra.tblenc();
    });
})

// (Para el boton de guardar y enviar)
// Acciones para el boton de guardar
document.getElementById("guardar").addEventListener("click", function (event) {
    event.preventDefault();
    Tiempoextra.guardartiempoextra().then(() => {
        Tiempoextra.tblsubenc();
    });
})

function verPDF(id) {
    window.open("./pdf/cambio_turno.php?id=" + btoa(id), "_blank");
}
window.verPDF = verPDF;


/*
-----------------------------------------------------------------------------------------------------------------
Manejo de datos para el modal de cambio de turno temporal
*/
const IBM_SESSION = '<?= htmlsprecialchars($ibm) ?>';
function abrirModal() {
    const hoy = new Date();
    const fechaLocal = hoy.getFullYear() + "-" +
        String(hoy.getMonth() + 1).padStart(2, '0') + "-" +
        String(hoy.getDate()).padStart(2, '0');

    document.getElementById('fecha_emision').value = fechaLocal;

    const modal = new bootstrap.Modal(document.getElementById('modalOverlay'));
    modal.show();
}


function cerrarModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalOverlay'));
    modal.hide();
    document.getElementById('formCambio').reset();
}

// Botón Guardar: solo guarda en BD
document.getElementById("btnGuardar").addEventListener("click", async function(){
    let folio = document.getElementById("folio").value.trim();
    let hora_presentacion = document.getElementById("hora_presentacion").value.trim();
    let horario_desde = document.getElementById("horario_desde").value.trim();
    let horario_hasta = document.getElementById("horario_hasta").value.trim();
    let hasta_tripulacion = document.getElementById("hasta_tripulacion").value.trim();
    let folioTE = document.getElementById("folioTiempoExtra").value.trim();


    const form = document.getElementById("formCambio");

    // Validación previa
    if(folio === "" || hora_presentacion === "" || horario_desde === "" || horario_hasta === "" || hasta_tripulacion === ""){
        Swal.fire('Error', 'Debes de ingresar todos los campos', 'error');
        return;
    }

    if(folioTE === "") {
        Swal.fire('Error', 'El folio no esta asociado a algun tiempo extra', 'error');
        return;
    }

    const data = new FormData(form);

    const response = await fetch("php/index.php?guardarCambioTurno&folio=" + folio, {
        method: "POST",
        body: data
    });

    const result = await response.json();
    if(result.success){
        Tiempoextra.tblsubenc();
        Swal.fire('Guardado', 'El cambio temporal de turno fue registrado', 'success');
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalOverlay'));
        modal.hide();
        form.reset();
    } else {
        Swal.fire('Error','No se pudo guardar', 'error');
    }
});


// // Botón PDF: genera el PDF en nueva pestaña
// document.getElementById("btnPDF").addEventListener("click", function(e){
//     e.preventDefault();

//     const form = document.getElementById("formCambio");

//     // Campos a validar
//     let campos = [
//         document.getElementById("folio"),
//         document.getElementById("hora_presentacion"),
//         document.getElementById("horario_desde"),
//         document.getElementById("horario_hasta"),
//         document.getElementById("hasta_tripulacion")
//     ];

//     let camposVacios = [];

//     // Limpia estilos previos
//     campos.forEach(campo => campo.classList.remove("input-error", "is-invalid"));

//     // Valida
//     campos.forEach(campo => {
//         if(campo.value.trim() === ""){
//             camposVacios.push(campo);
//             campo.classList.add("input-error");
//         }
//     });

//     if(camposVacios.length > 0){
//         Swal.fire('Error', 'Debes completar todos los campos antes de generar el PDF', 'error');
//         return;
//     }

//     // Si todo está correcto, enviamos el formulario al script del PDF
//     form.action = "./pdf/cambioTemporalTurno.php";
//     form.submit();
// });



/*
Fin da manejo de datos para el cambio de turno temporal
-----------------------------------------------------------------------------------------------------------------
*/

// Funcion para obtener el fin de turno y ponerlo como inicio de turno, capturar la duracion de T. extra y junto al fin de turno sumarle los valores mas 30 min de tolerancia
// Junto con ello se obtendran los nuevos valores de final de turno mas la tolerancia para aprobar o rechazar
document.getElementById("turnosel").addEventListener("change", function(){
    let turnoSeleccionado = document.getElementById("turnosel").value;

    // Definición de turnos
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
            entrada: { min: 7 * 60 + 20, max: 9 * 60 },
            salida: { min: 15 * 60 + 30, max: 17 * 60 + 10},
            horaIdealEntrada: "07:30:00",
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
        },
        mixto4: {
            entrada: { min: 7 * 60, max: 9 * 60 },
            salida: { min: 17 * 60, max: 17 * 60 + 30},
            horaIdealEntrada: "07:00:00",
            horaIdealSalida: "17:00:00"
        }
    };

    // Obtener datos del turno elegido
    if (turnos[turnoSeleccionado]) {
        let datosTurno = turnos[turnoSeleccionado];
        document.getElementById("horai").value = datosTurno.horaIdealSalida;        

        const horariosDeTurno = document.getElementById("horariosDeTurno");

        // Mostrar los rangos concatenados en una sola variable
        let infoHoras = datosTurno.horaIdealEntrada + ' - ' + datosTurno.horaIdealSalida;
        
        horariosDeTurno.style.display = "block";        
        document.getElementById("valorTurnoHora").textContent = infoHoras;
        // console.log("Horario ajustado: ", infoHoras);

        // Base: fin de turno = hora ideal de salida del turno
        let finTurnoMin = horaAMinutos(datosTurno.horaIdealSalida);

        // Duración extra
        const TiempoEStr = document.getElementById("duracionTE").value;
        const duracionTiempoE = TiempoEStr ? horaAMinutos(TiempoEStr) : 0;

        // Fin real del tiempo extra
        let finTiempoExtraReal = minutosAHora(finTurnoMin + duracionTiempoE);
        document.getElementById("horaf").value = finTiempoExtraReal;

        // Fin con margen de 30 min
        let horaFinalsinMargen = minutosAHora(finTurnoMin + duracionTiempoE);
        let horaFinalconMargen = minutosAHora(finTurnoMin + duracionTiempoE + 15);

        document.getElementById("horaf").value = horaFinalsinMargen;

         // Guardar en inputs ocultos
        document.getElementById("turnoSeleccionadoHidden").value = turnoSeleccionado;
        document.getElementById("horaFinalSinMargenHidden").value = horaFinalsinMargen;
        document.getElementById("horaFinalConMargenHidden").value = horaFinalconMargen;   
    } else {
        horariosDeTurno.style.display = "none";
    }

    function horaAMinutos(hora){
        let partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }

    function minutosAHora(minutos){
        minutos = minutos % (24 * 60);

        let horas = Math.floor(minutos / 60);
        let mins = minutos % 60;
        return horas.toString().padStart(2,'0') + ":" + mins.toString().padStart(2,'0') + ":00";
    }
    
    const TiempoEStr = document.getElementById("duracionTE").value;
    const duracionTiempoE = horaAMinutos(TiempoEStr);

    let finTurnoStr = document.getElementById("horaf").value;
    let finTurnoMin;

    if (!finTurnoStr) {
        // Si está vacío, inicializa con la hora de inicio
        finTurnoMin = horaAMinutos(document.getElementById("horai").value);
    } else {
        finTurnoMin = horaAMinutos(finTurnoStr);
    }

    // Si hay duración de tiempo extra, calcula el fin real
    let finTiempoExtraReal = 0;
    if (TiempoEStr) {
        // Final calculado con la duración del tiempo extra indicado más el fin del turno
        finTiempoExtraReal = minutosAHora(duracionTiempoE + finTurnoMin);    
    }

    const salidaStr = document.getElementById("horai").value;

    // Convertir a minutos
    const salidaMin = horaAMinutos(salidaStr);
    
    // Calculo de horas extra
    var horasExtrasMin = finTurnoMin - salidaMin;
    var horasExtras = horasExtrasMin > 0 ? minutosAHora(horasExtrasMin) : "00:00:00";
})

const Tools = new Toolsjs();

// (Acciones al poner un numero de empleado y traer de vuelta a los registros)
// Acciones para obtener info segun el numero de empleado
document.getElementById("noemp").addEventListener("keyup", function () {
    var noemp = document.getElementById("noemp").value;
    Tiempoextra.getinfoemp(noemp);
});

// document.getElementById("departamento").addEventListener("change", function () {
//     const departamento = this.value;
//     if (departamento === '') {
//         document.getElementById('maquinas').innerHTML = '';
//     } else {
//         Tools.llnarslc(
//             'CatalogoPersonal',
//             'GetSlcMaquinasxdep&departamento=' + departamento,
//             'maquinas',
//             0
//         );
//     }
// });

document.getElementById("departamento").addEventListener("change", function () {
    const nombreDepto = this.value.trim();
    
    if (nombreDepto === '') {
        document.getElementById('maquinas').innerHTML = '';
        return;
    }

    // Buscar el id numérico en el select de departamentoenc que ya está cargado
    const selectEnc = document.getElementById("departamentoenc");
    let idDepto = null;

    for (let i = 0; i < selectEnc.options.length; i++) {
        if (selectEnc.options[i].text.trim() === nombreDepto) {
            idDepto = selectEnc.options[i].value;
            break;
        }
    }

    if (!idDepto) {
        document.getElementById('maquinas').innerHTML = '';
        return;
    }

    Tools.llnarslc(
        'CatalogoPersonal',
        'GetSlcMaquinasxdep&departamento=' + idDepto,
        'maquinas',
        0
    );
});

// Acciones para obtener horas de entrada y salida segun le fecha y noemp
document.getElementById("fechainput").addEventListener("change", function () {
    var noemp = document.getElementById("noemp").value;
    var date = document.getElementById("fechainput").value;
    Tiempoextra.getinfohoraentradaysalida(noemp, date);
})

// Funcion principal para validar/identificar el turno actual segun la hora de entrada y de salida
// Funcion de calculo de horas extras segun los turnos y las horas registradas (entrada y salida)
function calcularTurnoYHoras(horaEntrada, horaSalida, esSabado = false) {
    // Funcion de conversion de hora a minutos
    function horaAMinutos(hora) {
        var partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }

    // Funcion de conversion de minutos a hora
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
            inicio: 7 * 60 + 20, 
            fin: 17 * 60 + 10, 
            nombre: "Mixto 1 (07:30:00 - 17:00:00)" ,
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
        },
        mixto4: {
            inicio: 7 * 60, 
            fin: 17 * 60,
            nombre: "Mixto 4 (07:00:00 - 17:00:00)" ,
            duracion: "09:30:00"
        }

    };

    // Deteccion de turno validando entrada y salida
    var turnoDetectado = null;

    // Asignacion y verificacion de turnos (mas estricta y precisa)
    // Verificacion de turnos comparando entrada y la salida con comparacines segun horarios reglamentarios 
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
        // Orden de prioridad Mixto > Turno 1 / Mixto2 > resto
        var prioridad = ['mixto4','mixto1', 'mixto2', 'turno1', 'turno2', 'mixto3', 'turno3'];

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

        // Si es sabado
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
        // No se detecto  se asume que no hay extras
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

// (Boton de preliminar de PDF)
// Accion para crear PDF segun el folio
document.getElementById("creapdf").addEventListener("click", function (event) {
    event.preventDefault();
    let folio = document.getElementById("folio").value;
    if (folio === "") {
        Swal.fire('UPS!!!', 'No hay un folio creado', 'info');
        return false;
    }
    window.open("./pdf/reporte.php?folio=" + btoa(folio));
})

// (Acciones para el boton de ver folios)
// Acciones para ver folio
document.getElementById("btnverfolio").addEventListener("click", function (event) {
    event.preventDefault();
    let valortxt = document.getElementById("txtview").textContent;
    document.getElementById("txtview").innerHTML = valortxt === "Cerrar" ? "Ver Folio" : "Cerrar";
})

// (Acciones para el boton de consultar solicitudes)
// Acciones del boton de consultar
document.getElementById("consultar").addEventListener("click", function() {
    window.open("../Tiempoextra/Autorizatp.php")
})

// Acciones para caso de que el motivo se un cambio de horario
document.getElementById("motivos").addEventListener("change", function () {
    let motivo = document.getElementById("motivos").value;
    // Si el motivo es 8
    // if (motivo == 8) {
    //     document.getElementById("horai").hidden = true;
    //     document.getElementById("horaf").hidden = true;
    //     document.getElementById("cambiohrario").hidden = false;
    // } else {
    //     document.getElementById("horai").hidden = false;
    //     document.getElementById("horaf").hidden = false;
    //     document.getElementById("cambiohrario").hidden = true;
    // }

    if (motivo == 8) {
    document.getElementById("horai").hidden = true;
    document.getElementById("horaf").hidden = true;
    document.getElementById("turnosel").hidden = true;
    document.getElementById("duracionTE").hidden = true;
    document.getElementById("cambiohrario").hidden = false;
} else {
    document.getElementById("horai").hidden = false;
    document.getElementById("horaf").hidden = false;
    document.getElementById("turnosel").hidden = false;
    document.getElementById("duracionTE").hidden = false;
    document.getElementById("cambiohrario").hidden = true;
}


})

document.addEventListener("DOMContentLoaded", function () {
    const duracionTE = document.getElementById("duracionTE");
    const turnoSel = document.getElementById("turnosel");
    const checkboxTurno3 = document.getElementById("checkboxTurno3");
    const contenedorCheckbox = document.getElementById("contenedorCheckbox");
    const horai = document.getElementById("horai");
    const horaf = document.getElementById("horaf");
    const motivo = document.getElementById("motivos");

    duracionTE.addEventListener("input", () => {
        if (duracionTE.value.trim() === "") {
            turnoSel.disabled = true;
            turnoSel.value = "";
            horai.value = "";
            horaf.value = "";
        } else {
            turnoSel.disabled = false;
        }
    });

    // Inputs ocultos
    const turnoHidden = document.getElementById("turnoSeleccionadoHidden");
    const horaFinalSinMargenHidden = document.getElementById("horaFinalSinMargenHidden");
    const horaFinalConMargenHidden = document.getElementById("horaFinalConMargenHidden");

    // Variables para guardar estado original
    let estadoOriginal = {
        horai: null,
        horaf: null,
        horaFinalSinMargen: null,
        horaFinalConMargen: null
    };

    function horaAMinutos(hora) {
        const partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }

    function minutosAHora(minutos) {
        minutos = minutos % (24 * 60);
        let horas = Math.floor(minutos / 60);
        let mins = minutos % 60;
        return horas.toString().padStart(2,'0') + ":" + mins.toString().padStart(2,'0') + ":00";
    }

    function validarCondiciones() {
        const turno = turnoSel.value;
        const motiv = motivo.value;
        const horasStr = duracionTE.value;
        if (!horasStr) return;

        const minutosDuracion = horaAMinutos(horasStr);
        const horasDecimal = minutosDuracion / 60;

        if (turno === "turno3" || turno === "turno2") {
            if (horasDecimal >= 3.5 && horasDecimal <= 4) {
                contenedorCheckbox.style.display = "block";
            } else if (horasDecimal > 4) {                
                if (motiv == 10 || motiv == 12) {
                    contenedorCheckbox.style.display = "block";
                    checkboxTurno3.checked = false;
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Horas no válidas",
                        text: "En el Turno 3 no se pueden solicitar más de 4 horas."
                    });
                    document.getElementById("turnosel").value = "";
                    document.getElementById("horai").value = "";
                    document.getElementById("horaf").value = "";
                    contenedorCheckbox.style.display = "none";
                    checkboxTurno3.checked = false;
                }
            } else {
                contenedorCheckbox.style.display = "none";
                checkboxTurno3.checked = false;
            }
        } else {
            contenedorCheckbox.style.display = "none";
            checkboxTurno3.checked = false;
        }

    }

    // Al cambiar duración o turno, validamos
    duracionTE.addEventListener("input", validarCondiciones);
    turnoSel.addEventListener("change", validarCondiciones);
    motivo.addEventListener("change", validarCondiciones);

    // Al activar/desactivar checkbox
    checkboxTurno3.addEventListener("change", function () {
        if (checkboxTurno3.checked && turnoSel.value === "turno3") {
            // Guardar estado original
            estadoOriginal.horai = horai.value;
            estadoOriginal.horaf = horaf.value;
            estadoOriginal.horaFinalSinMargen = horaFinalSinMargenHidden.value;
            estadoOriginal.horaFinalConMargen = horaFinalConMargenHidden.value;

            // Reescribir valores especiales
            horai.value = "19:00:00";
            horaf.value = "22:30:00";

            // Actualizar inputs ocultos
            turnoHidden.value = "turno3";
            horaFinalSinMargenHidden.value = "22:30:00";
            horaFinalConMargenHidden.value = "22:45:00"; // margen de 15 min
        } else if (checkboxTurno3.checked && turnoSel.value === "turno2"){
            // Guardar estado original
            estadoOriginal.horai = horai.value;
            estadoOriginal.horaf = horaf.value;
            estadoOriginal.horaFinalSinMargen = horaFinalSinMargenHidden.value;
            estadoOriginal.horaFinalConMargen = horaFinalConMargenHidden.value;

            // Reescribir valores especiales
            horai.value = "11:30:00";
            horaf.value = "15:00:00";

            // Actualizar inputs ocultos
            turnoHidden.value = "turno2";
            horaFinalSinMargenHidden.value = "11:30:00";
            horaFinalConMargenHidden.value = "15:15:00"; // margen de 15 min
        } else {
            // Restaurar valores originales
            if (estadoOriginal.horai) horai.value = estadoOriginal.horai;
            if (estadoOriginal.horaf) horaf.value = estadoOriginal.horaf;
            if (estadoOriginal.horaFinalSinMargen) horaFinalSinMargenHidden.value = estadoOriginal.horaFinalSinMargen;
            if (estadoOriginal.horaFinalConMargen) horaFinalConMargenHidden.value = estadoOriginal.horaFinalConMargen;
        }
    });

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

});

// Funciones helper para convertir horas a minutos y viceversa
function horaAMinutos(hora){
    let partes = hora.split(":");
    return parseInt(partes[0]) * 60 + parseInt(partes[1]);
}

function minutosAHora(minutos){
    let horas = Math.floor(minutos / 60);
    let mins = minutos % 60;
    return horas.toString().padStart(2,'0') + ":" + mins.toString().padStart(2,'0');
}

// Manejo de checkboxes para habilitar campos de edición y mostrar el boton de recalcular
const checkboxes = document.querySelectorAll("input[name='habilitarCampos']");
// Elemento del boton de recalcular
const divReCalcular = document.getElementById("divReCalcular");

// Función para revisar si al menos uno está activo
function actualizarBoton() {
    let algunoActivo = Array.from(checkboxes).some(cb => cb.checked);
    divReCalcular.hidden = !algunoActivo;
}

document.querySelector("#formtiempoextra").addEventListener("submit", e => {
    e.preventDefault();
    const data = new FormData(e.target);
    fetch("php/index.php?guardartiempoextra", { method:"POST", body:data });
});

document.querySelector("#formCambio").addEventListener("submit", e => {
    e.preventDefault();
    const data = new FormData(e.target);
    fetch("php/index.php?guardarCambioTurno", { method:"POST", body:data });
});


// Si el checkbox esta activo se activa el boton de calcular
// Agregar evento a cada checkbox para habilitar/deshabilitar campos y mostrar el botón de recalcular
checkboxes.forEach(cb => {
    cb.addEventListener("change", function() {
        const campoId = this.getAttribute("data-target");
        if (campoId) {
            let campo = document.getElementById(campoId);
            campo.disabled = !this.checked;
        }
        actualizarBoton();
    });
});

document.getElementById("duracionTE").addEventListener("blur", function() {
  const regex = /^([01]\d|2[0-3]):([0-5]\d)$/;
  if (!regex.test(this.value)) {
    Swal.fire('UPS!!!', 'Formato válido: hh:mm (00:00 a 23:59)', 'info');
    document.getElementById("duracionTE").value = "";
  } else {
    this.setCustomValidity("");
  }
  document.getElementById("turnosel").value = "";
  document.getElementById("horai").value = "";
  document.getElementById("horaf").value = "";
});

window.editEnc = function (id) {
    Tiempoextra.editenc(id);
}

window.deleteSub = function (id) {
    Tiempoextra.deletesub(id);
}

window.cambioTempTurno = function(noemp, estadoTurno, nombre, depto, id) {
    Tiempoextra.cambioTempTurno(noemp, estadoTurno, nombre, depto, id);
}

window.enviarEnc = function (id) {
    Tiempoextra.enviar(id);
}

window.pdfFin = function (id) {
    Tiempoextra.pdffin(id);
}