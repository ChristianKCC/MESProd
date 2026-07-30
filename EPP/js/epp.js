import { Toolsjs } from "../../Tools/Tools.js";
import { EPPMod } from "../module/eppmod.js";
import 'https://ka-f.webawesome.com/webawesome@3.9.0/components/divider/divider.js';
const Tools = new Toolsjs();
const EPPObj = new EPPMod();

// Buscar nombre, departamento y puesto
document.getElementById('noemp').addEventListener('keyup', (e) => Tools.getDataEmpleado(e.target.value, 'nombre', 'departamento', 'puesto'));

// Llenado de Equipo de proteccion basico
EPPObj.getListEquipo('ListEppBasico').then(val => {
    document.getElementById('listeppbasico').innerHTML = val;
});

// Llenado de Equipo de proteccion especifico
EPPObj.getListEquipo('ListEppEspecifico').then(val => {
    document.getElementById('listeppespecifico').innerHTML = val;
});

// Llenado de Equipo de BPM
EPPObj.getListEquipo('ListEppBPM').then(val => {
    document.getElementById('listeppbpm').innerHTML = val;
});

// Acciones para modal de solicitud de EPP
document.getElementById("btnguardarEPP").addEventListener("click", function() {
    const empleado = document.getElementById("solEmp").value.trim();
    const nombre = document.getElementById("solNombre").value.trim();
    const motivo = document.getElementById("opciones").value;    
    const radiosSi = document.querySelectorAll('#tblSolicitudEPP input[type="radio"][value="1"]:checked');
    const clave = document.getElementById('claveEPP').value.trim();

    // Validaciones
    if (clave.length < 4) {
        Swal.fire({ icon: 'warning', title: 'Clave requerida',
            text: 'Crea una clave de al menos 4 caracteres. La necesitarás para recibir tu equipo.' });
        return;
    }

    if (empleado === "") {
        Swal.fire({
            icon: 'warning',
            title: 'Error',
            text: 'Por favor selecciona un empleado valido.'
        });
        return;
    }

    if (!motivo || motivo === "Elige una opción") {
        Swal.fire({
            icon: 'warning',
            title: 'Falta motivo',
            text: 'Debes seleccionar un motivo para tu solicitud.'
        });
        return;
    }

    if (radiosSi.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Falta selección',
            text: 'Debes seleccionar al menos un EPP antes de continuar.'
        });
        return;
    }

    // Mostrar resumen de EPP seleccionado a forma de confirmacion
    let resumen = `<b>Empleado:</b> ${nombre} (${empleado})<br>
                   <b>Motivo:</b> ${motivo}<br><br>
                   <b>EPP seleccionados:</b><br>`;

    radiosSi.forEach(radio => {
        const fila = radio.closest("tr");
        const categoria = fila.querySelector("td:nth-child(1)").innerText;
        const equipo = fila.querySelector("td:nth-child(2)").innerText;
        resumen += `- ${categoria}: ${equipo}<br>`;
    });

    // Confirmar solicitud
    Swal.fire({
        icon: 'question',
        title: '¿Estás seguro?',
        html: resumen,
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const items = [];
            radiosSi.forEach(radio => {
                const fila = radio.closest("tr");
                const categoria = fila.querySelector("td:nth-child(1)").innerText;
                const equipo = fila.querySelector("td:nth-child(2)").innerText;
                const qty = fila.querySelector('input[type="number"]');
                items.push({ categoria, equipo, cantidad: qty ? (qty.value || 1) : 1 });
            });

            const payload = {
                tipo: 'epp',
                noemp: empleado,
                nombre: nombre,
                departamento: document.getElementById('solDepartamento').value,
                puesto: document.getElementById('solPuesto').value,
                motivo: motivo,
                clave: clave,
                items: items
            };

            EPPObj.generarValePDF(payload);

            Swal.fire({
                icon: 'success',
                title: 'EPP solicitado correctamente',
                text: 'Recuerda ir a Almacén de Refacciones y solicitar tu EPP con tu IBM y la contraseña que acabas de generar.'
            });
        }
    });
});

// Eventos para almacenar datos de solicitudes de herramientas mediante modal
document.getElementById("btnguardarTools").addEventListener("click", function() {
    const empleado = document.getElementById("solEmpT").value.trim();
    const nombre = document.getElementById("solNombreT").value.trim();
    const motivo = document.getElementById("opcionesT").value;    
    const radiosSi = document.querySelectorAll('#tblSolicitudTool input[type="radio"][value="1"]:checked');
    const clave = document.getElementById('claveTool').value.trim();

    // Validaciones
    if (clave.length < 4) {
        Swal.fire({ icon: 'warning', title: 'Clave requerida',
            text: 'Crea una clave de al menos 4 caracteres. La necesitarás para recibir tu equipo.' });
        return;
    }

    if (empleado === "") {
        Swal.fire({
            icon: 'warning',
            title: 'Error',
            text: 'Por favor selecciona un empleado valido.'
        });
        return;
    }

    if (!motivo || motivo === "Elige una opción") {
        Swal.fire({
            icon: 'warning',
            title: 'Falta motivo',
            text: 'Debes seleccionar un motivo para tu solicitud.'
        });
        return;
    }

    if (radiosSi.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Falta selección',
            text: 'Debes seleccionar al menos una herramienta antes de continuar.'
        });
        return;
    }

    // Mostrar resumen de EPP seleccionado a forma de confirmacion
    let resumen = `<b>Empleado:</b> ${nombre} (${empleado})<br>
                   <b>Motivo:</b> ${motivo}<br><br>
                   <b>Herramientas seleccionadas:</b><br>`;

    radiosSi.forEach(radio => {
        const fila = radio.closest("tr");
        const categoria = fila.querySelector("td:nth-child(1)").innerText;
        const equipo = fila.querySelector("td:nth-child(2)").innerText;
        resumen += `- ${categoria}: ${equipo}<br>`;
    });

    Swal.fire({
        icon: 'question',
        title: '¿Estás seguro?',
        html: resumen,
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const items = [];
            radiosSi.forEach(radio => {
                const fila = radio.closest("tr");
                const categoria = fila.querySelector("td:nth-child(1)").innerText;
                const equipo = fila.querySelector("td:nth-child(2)").innerText;
                const qty = fila.querySelector('input[type="number"]');
                items.push({ categoria, equipo, cantidad: qty ? (qty.value || 1) : 1 });
            });

            const payload = {
                tipo: 'tool',
                noemp: empleado,
                nombre: nombre,
                departamento: document.getElementById('solDepartamentoT').value,
                puesto: document.getElementById('solPuestoT').value,
                motivo: motivo,
                clave: clave,
                items: items
            };

            EPPObj.generarValePDF(payload);

            Swal.fire({
                icon: 'success',
                title: 'Herramientas solicitadas correctamente',
                text: 'Recuerda ir a Almacén de Refacciones y solicitar tus herramientas con tu IBM y la contraseña que acabas de generar.'
            });
        }
    });
});

// Acciones para el guardado del EPP
document.getElementById('saveEpp').addEventListener('click', function (e) {
    e.preventDefault();
    var checkboxes = document.querySelectorAll('input[type=radio]:checked');
    const noemp = document.getElementById('noemp').value;
    const nombre = document.getElementById('nombre').value;
    const comentario = document.getElementById('comentario').value;
    Swal.fire({
        title: "¿Estas seguro de capturar?",
        text: "Ya no podras cambiar la información posteriormente!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, seguro!"
    }).then((result) => {
        if (result.isConfirmed) {
            if (noemp === '' || nombre === '') {
                swal.fire('Error!', 'Por favor selecciona un IBM valido', 'warning');
                return false;
            }
            let checkboxValues = [];
            checkboxes.forEach(function (checkbox) {
                checkboxValues.push({ nombre: checkbox.getAttribute('name'), valor: checkbox.value });
            });
            EPPObj.saveEpp(noemp, checkboxValues, comentario).then(() =>
                EPPObj.tblEPP().then(element => document.getElementById('tbleppenc').innerHTML = element)
            );
        } else {
            return false;
        }
    });
})

// Acciones para el boton limpiar para ibm y datos
EPPObj.tblEPP().then(element => document.getElementById('tbleppenc').innerHTML = element);
document.getElementById('limpiar').addEventListener('click', function (e) {
    e.preventDefault();
    EPPObj.limpiar();
})

// Lanzado de modal
const exampleModal = document.getElementById('exampleModal')
exampleModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget
    const recipient = button.getAttribute('data-bs-whatever')
    const modalTitle = exampleModal.querySelector('.modal-title')
    modalTitle.textContent = 'Información del folio: ' + recipient
    EPPObj.tblEPPSubEnc(recipient).then((element) => {
        document.getElementById('tblsubenc').innerHTML = element
    });
})

// Autollenado de IBM dentro del modal de solicitud de EPP
document.getElementById('solEmp').addEventListener('keyup', (e) =>
    Tools.getDataEmpleado(e.target.value, 'solNombre', 'solDepartamento', 'solPuesto')
);

// Autollenado de IBM dentro del modal de solicitud de herramientas
document.getElementById('solEmpT').addEventListener('keyup', (e) =>
    Tools.getDataEmpleado(e.target.value, 'solNombreT', 'solDepartamentoT', 'solPuestoT')
);

// Cargar EPP desde el Excel según departamento y puesto
document.getElementById('btnCargarEPP').addEventListener('click', function () {
    const departamento = document.getElementById('solDepartamento').value;
    const puesto = document.getElementById('solPuesto').value;
    if (departamento === '' || puesto === '') {
        swal.fire('Error', 'Primero captura un empleado válido', 'warning');
        return;
    }
    EPPObj.getEPPExcel(departamento, puesto).then(lista => {
        document.getElementById('tblSolicitudEPP').innerHTML = EPPObj.renderEPPExcel(lista);
    });
});

// Cargar herramientas desde el excel segun departamento y puesto
document.getElementById("btnCargarTools").addEventListener('click', function (){
    const departamentoT = document.getElementById('solDepartamentoT').value;
    const puestoT = document.getElementById('solPuestoT').value;
    if (departamentoT === "" || puestoT === ""){
        swal.fire('Error', 'Primero captura un empleado válido', 'warning');
        return;
    }
    EPPObj.getToolExcel(departamentoT, puestoT).then(lista => {
        document.getElementById('tblSolicitudTool').innerHTML = EPPObj.renderToolExcel(lista);
    });
});

// ---------------------------------------------------------------------------------------------------
// Datos de carga para entrega de vales
// Botón "Solicitudes realizadas"
// Renderizado de datos y generacion de PDF en caso de existir datos
EPPObj.misSolicitudes().then(lista => {
    if (Array.isArray(lista) && lista.length > 0) {
        const btn = document.getElementById('btnMisSolicitudes');
        btn.style.display = 'inline-block';
        btn.querySelector('.badge').textContent = lista.length;
    }
});
document.getElementById('btnMisSolicitudes').addEventListener('click', () => {
    EPPObj.misSolicitudes().then(l =>
        document.getElementById('tblMisSolicitudes').innerHTML = EPPObj.renderMisSolicitudes(l));
});
document.getElementById('tblMisSolicitudes').addEventListener('click', (e) => {
    const b = e.target.closest('.btn-pdf-mis');
    if (b) EPPObj.descargarVale(b.dataset.folio);
});

// Botón "Entregas pendientes" (solo para almacen)
EPPObj.esAlmacen().then(res => {
    if (res.esAlmacen) {
        const btn = document.getElementById('btnEntregas');
        btn.style.display = 'inline-block';
        btn.querySelector('.badge').textContent = res.pendientes;
    }
});

// Llenado de datos para modal de entrega de EPP
document.getElementById('solEmpA').addEventListener('keyup', (e) =>
    Tools.getDataEmpleado(e.target.value, 'solNombreA', 'solDepartamentoA', 'solPuestoA'));

// Acciones para el boton de buscar pendientes segun el IBm escrito anteriormente
document.getElementById('btnBuscarPend').addEventListener('click', () => {
    const ibm = document.getElementById('solEmpA').value;
    if (ibm === '') { swal.fire('Error', 'Captura un IBM válido', 'warning'); return; }
    EPPObj.pendientesPorEmp(ibm).then(l =>
        document.getElementById('tblEntregas').innerHTML = EPPObj.renderEntregas(l));
});

// Acciones de la tabla de entregas
document.getElementById('tblEntregas').addEventListener('click', async (e) => {
    const pdf = e.target.closest('.btn-pdf');
    const ent = e.target.closest('.btn-entregar');
    const rec = e.target.closest('.btn-rechazar');
    if (!pdf && !ent && !rec) return;
    e.preventDefault();

    if (pdf) { EPPObj.descargarVale(pdf.dataset.folio); return; }

    if (rec) {
        const c = await Swal.fire({ title: '¿Rechazar solicitud?', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Sí, rechazar', cancelButtonText: 'Cancelar' });
        if (c.isConfirmed) {
            const r = await EPPObj.rechazarVale(rec.dataset.folio);
            if (r.ok) { Swal.fire('Listo', 'Solicitud rechazada', 'success');
                document.getElementById('btnBuscarPend').click(); }
        }
        return;
    }

    if (ent) {
        const folio = ent.dataset.folio;
        const { value: clave } = await Swal.fire({
            title: 'Confirmar entrega',
            input: 'password',
            inputLabel: 'Solicite al trabajador la clave que generó al momento de hacer su solicitud.',
            inputPlaceholder: 'Clave del trabajador',
            showCancelButton: true, confirmButtonText: 'Entregar', cancelButtonText: 'Cancelar',
            heightAuto: false,
            allowEnterKey: true,
            didOpen: () => {
                // Quitar propiedades de readonly opara hacer editable el campo de la ocntraseña del empleado
                const input = Swal.getInput();
                if (input) {
                    input.removeAttribute('readonly');
                    setTimeout(() => input.focus(), 100);
                }
            }
        });
        if (!clave) return;
        const r = await EPPObj.entregarVale(folio, clave);
        if (r.ok) {
            Swal.fire('Entregado', 'Equipo entregado y recibido correctamente', 'success');
            document.getElementById('btnBuscarPend').click();
        } else {
            Swal.fire('Error', r.msg || 'No se pudo entregar', 'error');
        }
    }
});