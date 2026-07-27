class Platicasmaquina {
    async tblsubencabezado() {
        const respuestaraw = await fetch("../platicas/php/platicas.php?getDataAsistencias");
        const respuesta = await respuestaraw.json();
        let body = "";
        respuesta.forEach((element) => {
            body += `<tr><td>${element.id}</td><td>${element.noemp}</td><td>${element.Nombre}</td><td>${element.puesto}</td></tr>`;
        });
        document.getElementById("tblsubencabezadoplaticas5").innerHTML = body;
    }
    editPlatica(evento, id) {
        const botonPresionado = evento.target;
        const tdTabla = botonPresionado.parentNode;
        const filaTabla = tdTabla.parentNode;
        const dato = filaTabla.cells[4].textContent;
        const fechaHoy = new Date();
        const partesFecha = dato.split("-");
        const fecha = new Date(Number(partesFecha[0]), Number(partesFecha[1]) - 1, Number(partesFecha[2]));
        if (fechaHoy < fecha) {
            alert("Aun no puedes tomar esta platica, espera al día estipulado.");
            document.getElementById("folioplaticas5").value = '';
            document.getElementById("tblsubencabezadoplaticas5").innerHTML = '';
            return false;
        }
        document.getElementById("folioplaticas5").value = id;
        this.tblsubencabezado();
    }
    async datosemp(noemp) {
        const respuestaraw = await fetch(
            "./php/bitacora.php?datosemp&noemp=" + noemp
        );
        const respuesta = await respuestaraw.json();
        if (respuesta.length === 0) {
            document.getElementById("nombresubplaticas5").value = '';
            document.getElementById("departamentosubplaticas5").value = '';
            return false;
        }
        document.getElementById("nombresubplaticas5").value = respuesta[0].nombre;
        document.getElementById("departamentosubplaticas5").value = respuesta[0].puesto;
    }
    cargarattr() {
        this.tblsubencabezado();
        (async () => {
            const idplatica = document.getElementById('folioplaticas5').value;
            const respuestaraw = await fetch('../platicas/php/platicas.php?getPlaticatoday');
            const respueta = await respuestaraw.json();
            document.getElementById('folioplaticas5').value = respueta[0].id;
            document.getElementById('archivo').innerHTML = `<embed src="../platicas/platicas/${respueta[0].ruta}"></embed>`;
        })();
        document.getElementById("guardarsubplaticas5").addEventListener("click", function (event) {
            event.preventDefault();
            (async () => {
                let folio = document.getElementById("folioplaticas5").value;
                let noemp = document.getElementById("noempsubplaticas5").value;
                let nombre = document.getElementById("nombresubplaticas5").value;
                if (noemp === '' || nombre === '') {
                    swal.fire('Lo siento!!!', 'Todos los campos son obligatorios', 'error');
                    return false;
                }
                const formdata = new FormData();
                formdata.append("noemp", noemp);
                formdata.append("idplatica", folio);
                const respuestaraw = await fetch("../platicas/php/platicas.php?regPlaticasub", {
                    method: "POST",
                    body: formdata,
                });
                respuestaraw.status == 200 && swal.fire('Listo!!!', 'Se registro tu asistencia', 'success');
                respuestaraw.status == 201 && swal.fire('Gracias!!!', 'Ya estabas registrado', 'success');
                respuestaraw.status == 500 && swal.fire('Error!!!', 'hay un problema al consultar tu asistencia', 'error');
                document.getElementById('formencplaticas').reset();
                const Platicas = new Platicasmaquina();
                Platicas.tblsubencabezado();
            })();
        });
    }
}

Platicasmaquina = new Platicasmaquina();
Platicasmaquina.cargarattr();
window.editPlatica = (event, param) => Platicasmaquina.editPlatica(event, param);

document.getElementById('noempsubplaticas5').addEventListener('keyup', function (e) {
    e.preventDefault();
    const noemp = document.getElementById('noempsubplaticas5').value;
    noemp != '' &&
        Platicasmaquina.datosemp(noemp);
})
