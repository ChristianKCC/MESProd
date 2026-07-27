import { Toolsjs } from "../../Tools/Tools.js";
class CambioPuesto {
    inicio() {
        const Tools = new Toolsjs();
        setInterval(Tools.mostrarHoraSimple(), 1000);
        Tools.llnarslcruta("php/index.php?motivosCambioPuesto", "motivos");
        Tools.llnarslc('CatalogoPersonal', "GetSlcMaquinas", "maquinas", 0);
        Tools.llnarslcruta("php/index.php?slcistpuestoscambiopuesto", "puestoant");
        Tools.llnarslcruta("php/index.php?slcistpuestoscambiopuesto", "temporal");
        this.tblenc();
    }
    
    // Llenado de segunda tabla para ver folios
    async tblenc() {
        const respuetaraw = await fetch("php/index.php?tblenc");
        const respuesta = await respuetaraw.json();
        let body = "";
        respuesta.forEach(elemento => {
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
                }
                
            );
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

        if (folio === "" ) {
            Swal.fire('Error', 'Selecciona o crea un folio,', 'info');
            return
        }
        const respuetaraw = await fetch("php/index.php?tblsubenc&folio=" + folio);
        const respuesta = await respuetaraw.json();
        let body = "";
        respuesta.forEach(elemento => {                        
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
        })
        document.getElementById("tblCambiopuesto").innerHTML = body;
    }
    
    // Guardado de datos con llamada al fetch
    async guardarcambiopuesto() {
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
        let ibmACubrir = document.getElementById("IBMCubrir").value;
        // Contenido textual sin value
        let temporalSelect = document.getElementById("temporal");
        let contenidoTemporal = temporalSelect.options[temporalSelect.selectedIndex].text.trim();

        let puestoRecuperadoIBM = document.getElementById("puestoCubrir").value;
        let motivos = document.getElementById("motivos").value;

        // Comparaciones entre numeros de semana para no hacer inserciones en semanas pasadas, si no solo en la actual 
        // Comparaciones entre el nuimero de semana sefun si se crea un folio o si se selecciona contra el numero de semana del sistema
        const today = new Date();
        const semanaActual = getWeekNumber(today);

        // No. semana oculto segun la fecha del folio
        const semanaFol = document.getElementById("nosemana").value;        

        // Fecha seleccionada
        let fechainput = document.getElementById("fechainput").value;
        let fechaObj = new Date(fechainput);

        // Semana actual del sistema (cuando se crea el folio)
        let semanaSistema = getWeekNumber(new Date());

        // Semana de la fecha seleccionada
        let semanaRegistro = getWeekNumber(fechaObj);

        console.log("semana registro: " + semanaFol);
        console.log("semana folio: " + semanaSistema);

        // Validación: tolerancia de ±1 semana
        if (semanaFol < (semanaSistema - 1)) {
            Swal.fire(
                'UPS!!!',
                'La fecha seleccionada pertenece a una semana demasiado antigua respecto al folio (más de 1 semana atrás).',
                'warning'
            );
            return;
        } else if (semanaFol > (semanaSistema + 1)) {
            Swal.fire(
                'UPS!!!',
                'La fecha seleccionada pertenece a una semana muy adelantada respecto al folio (más de 1 semana adelante).',
                'warning'
            );
            return;
        }

        
        // Comparacion entre no. semana
        // if(semanaActual != semanaFol) {
        //     //Swal.fire('Error', 'La fecha de creación del folio seleccionado se encuentra en una semana diferente al de la semana actual, crea un nuevo folio para hacer el registro corresopondiente a esta semana', 'warning');
        //     Swal.fire('Error', 'El folio seleccionado se abrio en una semana diferente a la fecha actual, crea un nuevo folio para hacer el registro correspondiente a esta semana.', 'warning');            
        // }

        else {
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

            // Validaciones
            if (noemp === "" || puestoant === "" || temportal === "" || folio === "" || maquina === "" || ibmACubrir === "" || puestoRecuperadoIBM === "") {
                Swal.fire('Error', 'No puede haber campos vacíos.', 'info');
                return false;
            }
            
            if (noemp === ibmACubrir){
                Swal.fire('Error', 'El IBM de la persona a cubrir no puede ser el mismo que el tuyo, verifica tu información.', 'error');
                return false;
            } 

            if (lunes + martes + miercoles + jueves + viernes + sabado + domingo === 0) {
                Swal.fire('Error', 'Debes seleccionar al menos un día.', 'info');
                return false;
            }

            // Validacion final al registrar
            const deptoFinal = document.getElementById("departamento").value.trim();
            if (deptoFinal != "Servicios auxiliares"){
                if (contenidoTemporal != puestoRecuperadoIBM) {
                    Swal.fire('Error', 'El puesto a cubrir debe ser el mismo que el del IBM seleccionado.', 'info');
                    return false;
                }
            } 

            if (!motivos) {
                Swal.fire('Error', 'Debes de seleccionar un motivo.', 'error');
                return false;
            }

            // Llamada principal de detalles
            const respuetaraw = await fetch("php/index.php?guardarcambiopuesto", {
                method: "POST",
                body: data
            });
            
            // Manejo de respuesta
            const respuesta = await respuetaraw.json();
            respuesta === "Listo" ?
                Swal.fire('Listo !', 'Registro guardado con éxito.', 'success') :
                respuesta === "Existe" ?
                    Swal.fire('Error', 'Estás duplicando un registro existente.', 'error') :
                    Swal.fire('Error', 'Error al guardar en la base de datos, contacta a soporte.', 'error');
        }
    }

    // Creacion de folio principal
    async abrirCambioPuesto() {
        const fechainicio = document.getElementById("fechainput").value;
        if (fechainicio == '') {
            Swal.fire('Error', 'No puede haber campos vacíos.', 'info');
            return false;
        }

        // Calcular número de semana
        const semana = getWeekNumber(fechainicio);
        document.getElementById("nosemana").value = semana;

        // Llamada principal a ticket principal
        const data = new FormData();
        data.append('fechainicio', fechainicio);
        data.append('nosemana', semana);

        const respuetaraw = await fetch("php/index.php?abrircambiopuesto", {
            method: 'POST',
            body: data
        });

        const respuesta = await respuetaraw.json();

        // Validar si hay error
        if (respuesta.error) {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo registrar',
                text: respuesta.error + ". Verifica que tu IBM esté en BD Nóminas o que tengas un jefe inmediato asignado.",
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Si no hay error, mostrar éxito
        Swal.fire('Listo!!!', 'Carga los cambios de puesto al folio ' + respuesta, 'success');
        this.tblenc();
        document.getElementById("folio").value = respuesta;
        document.getElementById("formtiempoextra").reset();
    }


    // Obtencion de datos
    async getinfoemp(noemp) {
        if (noemp == '') return false;
        const respuestaraw = await fetch("../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp);
        const respuesta = await respuestaraw.json();
        if (respuesta.length === 0) {
            document.getElementById("nombre").value = "";
            document.getElementById("departamento").value = "";
            document.getElementById("puesto").value = "";
            return;
        }

        document.getElementById('nombre').value = respuesta[0].nombre;
        document.getElementById('departamento').value = respuesta[0].departamento;
        document.getElementById('departamento').dispatchEvent(new Event('change'));

        document.getElementById('puesto').value = respuesta[0].puesto;

        // Normalizar el puesto recuperado
        const puestoNormalizado = normalizarPuesto(respuesta[0].puesto);
        const sel1 = document.getElementById("puestoant");            

        let encontrado = false;
        const normalizadoLower = quitarAcentos(puestoNormalizado.toLowerCase().trim());

        for (let i = 0; i < sel1.options.length; i++) {
            const optionText = quitarAcentos(sel1.options[i].text.toLowerCase().trim());
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
            document.getElementById("noemp").value ="";
            document.getElementById("nombre").value = "";
            document.getElementById("departamento").value = "";
            document.getElementById("puesto").value = "";

            Swal.fire('Error!', 'Tu puesto actual: "' + respuesta[0].puesto + '" no admite cambios de turno, si crees que es un error contacta a tu jefe inmediato.', 'error');
        }

        // Disparar el evento change para que se ejecute la lógica de puestos siguientes
        sel1.dispatchEvent(new Event('change'));
    }

    // Eliminacion de datos item
    async deleteitemsub(id) {
        const data = new FormData();
        data.append('id', id);
        const respuestaraw = await fetch('php/index.php?deleteitemsub', {
            method: 'POST',
            body: data
        })
        const respuesta = await respuestaraw.json();
        respuesta === "Listo" ?
            Swal.fire('Listo!!!', 'Registro eliminado con éxito.', 'success') :
            Swal.fire('Error!!!', 'Error al hacer cambios en la base de datos, contacta a soporte.', 'error');

        this.tblsubenc();
    }

    // Autorizacion
    enviar(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Una vez creado el archivo final no podrás hacer cambios y desaparecerá de tu lista de folios!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '¡Sí, seguro!',
            cancelButtonText: '¡No, cancela!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                (async () => {
                    // Autorizacion de folio
                    const respuestaraw = await fetch("./php/index.php?enviarfol&id=" + id);
                    const respuesta = await respuestaraw.json();
                    respuesta === false ?
                        Swal.fire('Error!!!', 'Hay un error con la base de datos.', 'error') :
                        Swal.fire('¡Terminaste!', '', 'success');
                    window.open("./pdf/reporte.php?folio=" + btoa(id) + "&true=1trlist");
                    window.location.reload();
                })();
            }
        })
    }

    async cargarDisponibles(idsPuestos) {
        try {
            // Numero de semana segun la fecha del sistema
            const today = new Date();
            const semanaActual = getWeekNumber(today);
            
            // Numero de semana segun el la fecha del folio que se seleccione
            const semana = document.getElementById("nosemana").value;
            const noemp = document.getElementById("noemp").value;

            const respuestaraw = await fetch("../Components/CatalogoSeguridad.php?disponibles&puestos=" + idsPuestos.join(",") + "&nosemana=" + semana +"&noemp=" + noemp);
            // uso de variable de semanaActual si la fecha del sistema es correcta
            //const respuestaraw = await fetch("../Components/CatalogoSeguridad.php?disponibles&puestos=" + idsPuestos.join(",") + "&nosemana=" + semanaActual);
            const respuesta = await respuestaraw.json();

            const tbody = document.getElementById("tblDisponibles");
            tbody.innerHTML = "";

            if (!respuesta || respuesta.length === 0) {
                tbody.innerHTML = "<tr><td colspan='11'>No hay vacantes a cubrir esta semana !</td></tr>";
                return;
            }

            respuesta.forEach(r => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td class="text-center">${r.noemp}</td>
                    <td class="text-center">${r.puestoRegular}</td>
                    <td class="text-center">${r.NombreMaquina}</td>
                    <td class="text-center">${r.lunes == 1 ? '✔' : "✘"}</td>
                    <td class="text-center">${r.martes == 1 ? "✔" : "✘"}</td>
                    <td class="text-center">${r.miercoles == 1 ? "✔" : "✘"}</td>
                    <td class="text-center">${r.jueves == 1 ? "✔" : "✘"}</td>
                    <td class="text-center">${r.viernes == 1 ? "✔" : "✘"}</td>
                    <td class="text-center">${r.sabado == 1 ? "✔" : "✘"}</td>
                    <td class="text-center">${r.domingo == 1 ? "✔" : "✘"}</td>
                    <td class="text-center" style="width:1%; white-space:nowrap;">
                        <button class="btn btn-primary btn-sm" onclick="tomarVacante(${r.noemp}, '${r.puestoRegular}', ${r.lunes}, ${r.martes}, ${r.miercoles}, ${r.jueves}, ${r.viernes}, ${r.sabado}, ${r.domingo} )">
                            <i class="fa-solid fa-hand-pointer"></i>
                            Cubrir esta vacante
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (err) {
            console.error("Error al cargar");
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


document.getElementById("formtiempoextra").addEventListener("reset", function () {    
    sel1.selectedIndex = -1;
    sel2.innerHTML = "";
    sel2.disabled = true;

    ["lunes","martes","miercoles","jueves","viernes","sabado","domingo"].forEach(id => {
        document.getElementById(id).checked = false;
    });

    ibmCubrirInput.value = "";
    puestoCubrirInput.value = "";
    ibmCubrirInput.readOnly = true;

    validarActivacion();
});

// Funcion para obtener el numero de semana
//  function getWeekNumber(dateString) {
//      const date = new Date(dateString);
//      // Ajustar al jueves para cumplir con ISO week
//      date.setHours(0, 0, 0, 0);
//      // Jueves de la semana actual
//      date.setDate(date.getDate() + 4 - (date.getDay() || 7));
//      const yearStart = new Date(date.getFullYear(), 0, 1);
//      const weekNo = Math.ceil((((date - yearStart) / 86400000) + 2) / 7);
//      return weekNo;
//  }

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



// Creacion de folio
document.getElementById("abrir").addEventListener("click", function (event) {
    event.preventDefault();
    CambioPuesto.abrirCambioPuesto().then(exito => {
        exito && CambioPuesto.tblsubenc();
    });
})

// Guardar detalles de folio
document.getElementById("guardar").addEventListener("click", function (event) {
    event.preventDefault();
    CambioPuesto.guardarcambiopuesto().then(() => {
        CambioPuesto.tblsubenc();
    });
})

// validacion de que al crear un folio el inicio de semana sea lunea
document.getElementById("fechainput").addEventListener("change", function () {
    const fecha = new Date(this.value);
    const diaSemana = fecha.getDay();
    // D=0, L=1...

    if (diaSemana !== 0) { 
        Swal.fire('Atención', 'Tus inicios de semana deben de ser lunes.', 'warning');
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
        Swal.fire('Error', 'No hay un folio creado.', 'info');
        return false;
    }
    window.open("./pdf/reporte.php?folio=" + btoa(folio));
})

// Funcion de quitar acentos para normalizar los valores antes de que lleguen
function quitarAcentos(texto) {
    return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

// Funcion para tomar la vacante en caso de seleccionarla mediante la tabla
function tomarVacante(noempCubrir, puestoCubrir, lunes, martes, miercoles, jueves, viernes, sabado, domingo) {
    document.getElementById("IBMCubrir").value = noempCubrir;
    document.getElementById("puestoCubrir").value = puestoCubrir;

    document.getElementById("lunes").checked = lunes == 1;
    document.getElementById("martes").checked = martes == 1;
    document.getElementById("miercoles").checked = miercoles == 1;
    document.getElementById("jueves").checked = jueves == 1;
    document.getElementById("viernes").checked = viernes == 1;
    document.getElementById("sabado").checked = sabado == 1;
    document.getElementById("domingo").checked = domingo == 1;

    validarActivacion();
}

window.tomarVacante = tomarVacante;

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
    {id: 1, nombre: "Ayudante general"},
    {id: 2, nombre: "Empacador"},
    {id: 4, nombre: "Sellador"},
    {id: 5, nombre: "Ayudante de conductor"},
    {id: 8, nombre: "Mecánico A"},
];

const sel1 = document.getElementById("puestoant");
const sel2 = document.getElementById("temporal");

document.getElementById("btnVacantes").addEventListener("click", () => {
    // Tomar los IDs de las opciones del select temporal
    const siguientes = Array.from(sel2.options).map(opt => opt.value);

    if (siguientes.length === 0) {
        Swal.fire('Atención', 'No hay puestos disponibles para mostrar.', 'info');
        return;
    }

    // Cargar la tabla dentro del modal
    CambioPuesto.cargarDisponibles(siguientes);
});


// Lista de IBM que pueden visualizar la ultima opcion
const ibmEspeciales = [28154, 30108, 32738, 32849, 50764, 51447, 57270, 57429, 57511, 58811, 59697, 59698];

// Lógica para llenar el select temporal de forma que el ibm entre de forma manual
// function actualizarTemporal() {
//     const puestoSeleccionado = sel1.options[sel1.selectedIndex].text.trim();
//     const noemp = parseInt(document.getElementById("noemp").value);

//     // Normalizar ambos lados para evitar problemas con acentos
//     const normalizadoSeleccionado = quitarAcentos(puestoSeleccionado.toLowerCase());
//     const index = opciones.findIndex(o => quitarAcentos(o.nombre.toLowerCase()) === normalizadoSeleccionado);

//     if (index === -1) {
//         return;
//     }

//     // Último puesto → Mecánico A
//     if (index === opciones.length - 1) {
//         document.getElementById("noemp").value = "";
//         document.getElementById("nombre").value = "";
//         document.getElementById("departamento").value = "";
//         document.getElementById("puesto").value = "";
//         document.getElementById("temporal").innerHTML = "";

//         Swal.fire('Atención', 'Este puesto es la categoría más alta, no puedes hacer un cambio de puesto.', 'warning');
//         sel2.innerHTML = '';
//         sel2.disabled = true;
//         return;
//     }

//     // Siguientes 2 puestos según posición en el arreglo
//     let siguientes = [];
//     let sigFin = [];
//     if (index + 1 < opciones.length) siguientes.push(opciones[index + 1].id);
//     if (index + 2 < opciones.length) siguientes.push(opciones[index + 2].id);

//     // Si el IBM está en la lista especial, agregar el último
//     const ultimoId = opciones[opciones.length - 1].id;
//     if (ibmEspeciales.includes(noemp) && !siguientes.includes(ultimoId)) {
//         sigFin.push(ultimoId);
//     }
    
//     // Validacion en caso de que el departamento sea el de Servicios auxiliares
//     const depto = document.getElementById("departamento").value.trim();

//     if (depto === "Servicios auxiliares"){
//         // Llenar select temporal
//         console.log("Caso: " + depto);
//         sel2.innerHTML = '';
//         sigFin.forEach(id => {
//             const opt = document.createElement('option');
//             opt.value = id;
//             opt.textContent = opciones.find(o => o.id === id).nombre;
//             sel2.appendChild(opt);
//         });
//     } else {
//         console.log("El departamento del IBM solicitante no entra en Servicios Auxiliares");
//         // Llenar select temporal
//         sel2.innerHTML = '';
//         siguientes.forEach(id => {
//             const opt = document.createElement('option');
//             opt.value = id;
//             opt.textContent = opciones.find(o => o.id === id).nombre;
//             sel2.appendChild(opt);
//         });
//     }

//     CambioPuesto.cargarDisponibles(siguientes);
//     sel2.disabled = siguientes.length === 0;
// }

function actualizarTemporal() {
    const puestoSeleccionado = sel1.options[sel1.selectedIndex].text.trim();
    const noemp = parseInt(document.getElementById("noemp").value);

    const normalizadoSeleccionado = quitarAcentos(puestoSeleccionado.toLowerCase());
    const index = opciones.findIndex(o => quitarAcentos(o.nombre.toLowerCase()) === normalizadoSeleccionado);

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

        Swal.fire('Atención', 'Este puesto es la categoría más alta, no puedes hacer un cambio de puesto.', 'warning');
        sel2.innerHTML = '';
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

    sel2.innerHTML = '';
    if (depto === "Servicios auxiliares") {        
        // Mostrar solo Mecánico A
        const opt = document.createElement('option');
        opt.value = ultimoId;
        opt.textContent = opciones.find(o => o.id === ultimoId).nombre;
        sel2.appendChild(opt);
    } else {        
        // Mostrar los siguientes puestos (y el último si aplica)
        siguientes.forEach(id => {
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = opciones.find(o => o.id === id).nombre;
            sel2.appendChild(opt);
        });
    }

    CambioPuesto.cargarDisponibles(siguientes);
    sel2.disabled = sel2.options.length === 0;
}

// Bloqueo de campo de No. Emp hasta que se tenga un folio o se cree uno
const fol = document.getElementById("folio").value;

// Ejecutar cuando el usuario cambie manualmente
sel1.addEventListener('change', actualizarTemporal);

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

// document.getElementById("IBMCubrir").addEventListener("keyup", async function () {
//     const ibm = this.value.trim();
//     if (ibm === "") {
//         puestoCubrirInput.value = "";
//         return;
//     }
//     const respuestaraw = await fetch("../Components/CatalogoSeguridad.php?datosemp&noemp=" + ibm);
//     const respuesta = await respuestaraw.json();
//     if (respuesta.length > 0) {
//         const puestoRecuperado = normalizarPuesto(respuesta[0].puesto);
//         puestoCubrirInput.value = puestoRecuperado;

//         // Validar que esté dentro de los permitidos
//         const idsPermitidos = Array.from(sel2.options).map(opt => opt.value);
//         const puestoPermitido = opciones.find(o => o.nombre.toLowerCase() === puestoRecuperado.toLowerCase());

//         if (!puestoPermitido || !idsPermitidos.includes(String(puestoPermitido.id))) {
//             Swal.fire('Error', 'El puesto del IBM ingresado no coincide con los puestos disponibles según tu puesto actual. Verifica el IBM e intenta de nuevo.', 'warning');
//             this.value = "";
//             puestoCubrirInput.value = "";
//             return;
//         }
//     } else {
//         puestoCubrirInput.value = "";
//     }
//     validarActivacion();
// });

// Acciones para el select de IBM a cubrir
document.getElementById("IBMCubrir").addEventListener("keyup", async function () {
    const ibm = this.value.trim();
    if (ibm === "") {
        puestoCubrirInput.value = "";
        return;
    }
    const respuestaraw = await fetch("../Components/CatalogoSeguridad.php?datosemp&noemp=" + ibm);
    const respuesta = await respuestaraw.json();

    if (respuesta.length > 0) {
        const puestoRecuperado = normalizarPuesto(respuesta[0].puesto);
        const deptoSolicitante = document.getElementById("departamento").value.trim();

        if (deptoSolicitante === "Servicios auxiliares") {
            // Aceptar cualquier IBM y mostrar su puesto real
            puestoCubrirInput.value = puestoRecuperado;
        } else {
            // Validación normal
            puestoCubrirInput.value = puestoRecuperado;

            const idsPermitidos = Array.from(sel2.options).map(opt => opt.value);
            const puestoPermitido = opciones.find(o => o.nombre.toLowerCase() === puestoRecuperado.toLowerCase());

            if (!puestoPermitido || !idsPermitidos.includes(String(puestoPermitido.id))) {
                Swal.fire('Error', 'El puesto del IBM ingresado no coincide con los puestos disponibles según tu puesto actual. Verifica el IBM e intenta de nuevo.', 'warning');
                this.value = "";
                puestoCubrirInput.value = "";
                return;
            }
        }
    } else {
        puestoCubrirInput.value = "";
    }
    validarActivacion();
});

// Funcion de validacion antes de activar elementos
function validarActivacion() {
    const maquina = maquinaSelect.value.trim();
    const ibmCubrir = ibmCubrirInput.value.trim();
    const puestoCubrir = puestoCubrirInput.value.trim();
    const ibmp = ibmpropio.value.trim();
    const motivo = motivoCambioPT.value.trim();
    const fol = folio.value.trim();

    const btnGuardar = document.getElementById("guardar");
    const btnVacantes = document.getElementById("btnVacantes");

    // Mostrar/ocultar Guardar
    if (fol !== "" && ibmCubrir !== "" && puestoCubrir !== "" && motivo !== "") {
        btnGuardar.style.display = "inline-block";
    } else {
        btnGuardar.hidden = false;
        btnGuardar.style.display = "none";
    }

    // Mostrar/ocultar botón Vacantes
    if (fol !== "" && ibmp !== "" && maquina !== "") {
        btnVacantes.style.display = "inline-block";
    } else {
        btnVacantes.style.display = "none";
    }
}

document.getElementById("departamento").addEventListener("change", function () {
    const nombreDepto = this.value.trim();

    if (nombreDepto === '') {
        // Restaurar todas las máquinas si se limpia el departamento
        const Tools = new Toolsjs();
        Tools.llnarslc('CatalogoPersonal', 'GetSlcMaquinas', 'maquinas', 0);
        return;
    }

    // Buscar el id numérico en el select de departamentoenc o en el select de maquinas
    // Como no hay un select de deptos visible, usamos el mismo truco: hacer fetch directo
    fetch('../Components/CatalogoPersonal.php?GetSlcDeps')
        .then(r => r.json())
        .then(deptos => {
            const depto = deptos.find(d => d.nombre.trim() === nombreDepto);
            if (!depto) return;

            const Tools = new Toolsjs();
            Tools.llnarslc(
                'CatalogoPersonal',
                'GetSlcMaquinasxdep&departamento=' + depto.id,
                'maquinas',
                0
            );
        });
});

// Eliminacion y consulta
window.editEnc = function(id, fecha){
    CambioPuesto.editenc(id, fecha);
}

// Eliminacion principal de datos
window.deleteItemSub = function(id){
    CambioPuesto.deleteitemsub(id);
}

// Autorizacion de datos
window.enviarEnc = function(id){
    CambioPuesto.enviar(id);
}

// Apertura del PDF
window.pdfFin = function(id){
    CambioPuesto.pdffin(id);
}

document.addEventListener("DOMContentLoaded", () => {
    // -------------------------------------------------------------------------------------------------------------------------
    const driver = window.driver.js.driver;

    const steps = [
        {
            element: ".tittlecont",
            popover: {
                title: "Cambio de Puesto",
                description: "Aquí comienza el proceso para elaborar tus solicitudes de cambio de puesto.",
                side: "bottom"
            }
        },
        {
            element: ".alert.alert-info",
            popover: {
                title: "Instrucciones iniciales",
                description: "Desde esta sección podrás crear y gestionar tus solicitudes de cambio de puesto.",
                side: "bottom"
            }
        },
        {
            element: "#folio",
            popover: {
                title: "Folio",
                description: "Este campo muestra el folio generado para tu solicitud.",
                side: "top"
            }
        },
        {
            element: "#fechainput",
            popover: {
                title: "Inicio de semana",
                description: "Selecciona la fecha de inicio de la semana para tu solicitud.",
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
            element: ".empezarDeNuevo",
            popover: {
                title: "Reiniciar proceso",
                description: "Presiona este botón para limpiar todos los campos e iniciar un nuevo registro.",
                side: "top"
            }
        },
        {
            element: "#creapdf",
            popover: {
                title: "Previsualizar PDF",
                description: "Genera una vista previa en PDF de tu solicitud.",
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
            element: "#maquinas",
            popover: {
                title: "Máquina",
                description: "Selecciona la máquina en la que trabajará el empleado (Esta se actualizara segun el departamento que se tenga asignado).",
                side: "top"
            }
        },
        {
            element: "#temporal",
            popover: {
                title: "Puesto a cubrir",
                description: "Selecciona el puesto temporal que se cubrirá (Solo podras ver dos puestos arriba de tu puesto actual).",
                side: "top"
            }
        },
        {
            element: ".motivoSeleccion",
            popover: {
                title: "Motivo del cambio de puesto",
                description: "Selecciona un motivo de las opciones disponibles por la cual haces tu cambio de puesto).",
                side: "top"
            }
        },
        {
            element: "#IBMCubrir",
            popover: {
                title: "IBM de persona a cubrir",
                description: "Ingresa el IBM de la persona que será cubierta (Solo puede ser alguien que tenga el mismo puesto que tus opciones disponibles para cubrir, no podras cubir a alguien que tiene un puesto diferente a tus opciones en 'Puesto a cubrir').",
                side: "top",
                popoverClass: "popover-importante"
            }
        },
        {
            element: ".diasSeleccion",
            popover: {
                title: "Selección de días",
                description: "Selecciona los dias que vas a cubrir la vacante.",
                side: "top"
            }
        },
        {
            element: ".alert.alert-warning",
            popover: {
                title: "Tabla de solicitudes",
                description: "Aquí encontrarás las solicitudes creadas y asociadas al folio seleccionado.",
                side: "bottom"
            }
        },
        {
            element: "#tblCambiopuesto",
            popover: {
                title: "Solicitudes creadas",
                description: "Consulta el detalle de las solicitudes registradas en la tabla.",
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
