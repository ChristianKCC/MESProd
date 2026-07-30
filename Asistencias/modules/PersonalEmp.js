import { Toolsjs } from "../../Tools/Tools.js";

export class Asistencias {
    // Funcion para obtener datos como centro de costos, tipo de empleado y departamento basados en el numero de empleado
    async cargarDatosbyNoEmp() {
        const empno = document.getElementById("empno").value;
        // const empno = this.value;

        try {
            const resp = await fetch('src/Repositorios/getEmpleadoInfo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'empno=' + encodeURIComponent(empno)
            });
            const data = await resp.json();

            if (data.success) {
                // Solo asigna si existe valor, si no deja el select como está
                if (data.IdCentroCosto !== undefined && data.IdCentroCosto !== null && data.IdCentroCosto !== '') {
                    document.getElementById('ctrocstos').value = data.IdCentroCosto;
                }
                if (data.EmpleadoSindicalizado !== undefined && data.EmpleadoSindicalizado !== null && data.EmpleadoSindicalizado !== '') {
                    document.getElementById('tipemp').value = data.EmpleadoSindicalizado;
                }
                if (data.NombreDepartamento !== undefined && data.NombreDepartamento !== null && data.NombreDepartamento !== '') {
                    document.getElementById('departamento').value = data.NombreDepartamento;
                }
            } else {
                document.getElementById('ctrocstos').value = "";
                document.getElementById('tipemp').value = "";
                document.getElementById('departamento').value = "";
            }
        } catch (err) {
            console.error('Error consultando empleado');
        }
    }


    // Funcion asincrona que verifica las asistencias segun un rango de fechas
    async buscarAsistencias() {
        let fechai = document.getElementById("fechai").value;
        let fechaf = document.getElementById("fechaf").value;
        let empno = document.getElementById("empno").value;
        let departamento = document.getElementById("departamento").value;
        if (fechaf == '' || fechai == '') {
            Swal.fire('Error', 'El intervalo de fechas es obligatorio', 'warning');
            return false;
        };
        const formdata = new FormData();
        formdata.append("fechai", fechai);
        formdata.append("fechaf", fechaf);
        formdata.append("empno", empno);
        formdata.append("departamento", departamento);
        const respuestaraw = await fetch('../Asistencias/php/asistencias.php?reporteasistencias', {
            method: "POST",
            body: formdata
        });
        const respuesta = await respuestaraw.json();
        let body = "";
        let no = 1;
        respuesta.forEach(elemento => {
            body +=
                "<tr><td>" + no +
                "</td><td>" + elemento.ibm +
                "</td><td>" + elemento.nombre +
                "</td><td>" + elemento.fecha +
                "</td><td>" + elemento.temperatura +
                "</td><td>" + elemento.ubicacion +
                "</td></tr>";
            no++;
        })
        document.getElementById("consultaacceso").innerHTML = body;
    }
}

export class Descansos {
    async uploadfile(fileInput, fechadescansos) {
        const file = fileInput.files[0];
        if (fechadescansos === '') {
            Swal.fire('Ups!!!', 'No hay una fecha seleccionada', 'warning');
            return false;
        }
        else if (file) {
            const formData = new FormData();
            formData.append("fechadescansos", fechadescansos);
            formData.append("filec", file);
            const response = await fetch("./php/asistencias.php?uploadFile", {
                method: "POST",
                body: formData
            });
            response.ok ?
                Swal.fire('Listo!!!', 'Archivo cargado', 'success') :
                Swal.fire('Error!!!', 'Hay problemas al cargar la información', 'error')
        } else {
            Swal.fire('Ups!!!', 'Debes cargar un archivo', 'warning');
            return false;
        }
    }
    async getDatatblDescansos(data) {
        const respuestaraw = await fetch('php/asistencias.php?getDatatblDescansos', {
            method: 'POST',
            body: data
        });
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    async tblDescansos(fechai, fechaf, noemp) {
        const data = new FormData();
        let body = '';
        data.append('fechai', fechai);
        data.append('fechaf', fechaf);
        data.append('noemp', noemp);
        this.getDatatblDescansos(data).then((respuesta) => {
            respuesta.forEach((elemet) => {
                body += `<tr><td>${elemet.noemp}</td><td>${elemet.nombre}</td><td>${elemet.fecha}</td><td>${elemet.lunes}</td>
                <td>${elemet.martes}</td><td>${elemet.miercoles}</td><td>${elemet.jueves}</td><td>${elemet.viernes}</td>
                <td>${elemet.sabado}</td><td>${elemet.domingo}</td>
                <td><button class="btn btn-sm btn-danger" onclick='deleteDesc(${elemet.id})'><i class="fas fa-trash"></i></button></td></tr>`;
            })
            document.getElementById('tbldescansos').innerHTML = body;
        })
    }
    async deleteDesc(params) {
        const data = new FormData();
        data.append('id', params);
        const respuestaraw = await fetch('php/asistencias.php?deleteDescanso', {
            method: 'POST',
            body: data
        });
        respuestaraw.ok ? swal.fire('Listo!!!', 'Se eliminó el descanso con folio: ' + params, 'success') :
            swal.fire('Error!!!', 'Hay un problema al eliminar el registro', 'error');
    }
}