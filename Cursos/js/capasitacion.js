class Capacitacion extends Herramientas {
    slcIntructores() {
        let folio = document.getElementById('desccurso').value;
        capacitacion.llnarslcCatalogo('CatalogoCursos','GetSlcInstructorxCurso&folio=' + folio, 'instructor', 0);
        document.getElementById('noemp').value = '';
    }
    async tblCapacitaciones() {
        const dataraw = await fetch("./php/capacitacion.php?getDatatblCapacitaciones");
        const data = await dataraw.json();
        let body = '';
        data.forEach(element => {
            body += `<tr><td>${element.folio}</td><td>${element.inicio}</td><td>${element.finalizo}</td>
            <td>${element.idcurso}</td><td>${element.curso}</td><td>${element.noemp}</td>
            <td>${element.instructor}</td><td>${element.comentarios}</td><td>${element.induccion === 1 ? 'SI' : 'NO'}</td><td>${element.reinduccion === 1 ? 'SI' : 'NO'}</td>
            <td><button class="btn btn-sm btn-warning" onclick="capacitacion.editCapacitacion(${element.folio});"><i class="fa-solid fa-pen-to-square"></i></button></td></tr>`;
        });
        document.getElementById('tblcapacitaciones').innerHTML = body;
    }
    async guardarCapacitacion(event) {
        event.preventDefault();
        const folio = document.getElementById('folio').value;
        const inicio = document.getElementById('fechainicio').value;
        const finalizo = document.getElementById('fechafinal').value;
        const curso = document.getElementById('desccurso').value;
        const duracion = document.getElementById('duracion').value;
        const instructor = document.getElementById('instructor').value;
        const comentarios = document.getElementById('coment').value;
        const induccion = document.getElementById('induccion').checked == true ? 1 : 0;
        const reinduccion = document.getElementById('reinduccion').checked == true ? 1 : 0;
        const dataf = new FormData();
        dataf.append('folio', folio);
        dataf.append('inicio', inicio);
        dataf.append('finalizo', finalizo);
        dataf.append('curso', curso);
        dataf.append('duracion', duracion);
        dataf.append('instructor', instructor);
        dataf.append('comentarios', comentarios);
        dataf.append('induccion', induccion);
        dataf.append('reinduccion', reinduccion);
        const dataraw = await fetch('./php/capacitacion.php?saveCapacitacion', {
            method: "POST",
            body: dataf
        });
        const data = await dataraw.json();
        data === 'done' ? Swal.fire('Listo!!!', 'Se actualizo la información', 'success') :
            Swal.fire('Listo!!!', 'Se guardo la capacitación con folio:' + data, 'success'), document.getElementById('folio').value = data;
        capacitacion.tblCapacitaciones();
    }
    async editCapacitacion(folio) {
        const dataf = new FormData();
        dataf.append('folio', folio);
        const dataraw = await fetch('./php/capacitacion.php?getDatabyfolioCapacitacion', {
            method: "POST",
            body: dataf
        });
        const data = await dataraw.json();
        document.getElementById('folio').value = data[0].folio;
        document.getElementById('fechainicio').value = data[0].inicio;
        document.getElementById('fechafinal').value = data[0].finalizo;
        document.getElementById('idcurso').value = data[0].curso;
        document.getElementById('desccurso').value = data[0].curso;
        document.getElementById('noemp').value = data[0].instructor;
        document.getElementById('duracion').value = data[0].duracion;
        document.getElementById('coment').value = data[0].comentarios;
        capacitacion.llnarslcCatalogo('CatalogoCursos','GetSlcInstructorxCurso&folio=' + data[0].curso, 'instructor',0).then(() => {
            document.getElementById('instructor').value = data[0].instructor;
        });
        document.getElementById('induccion').checked = data[0].induccion === 0 ? false : true;
        document.getElementById('reinduccion').checked = data[0].reinduccion === 0 ? false : true;
        capacitacion.tblSubCapacitacion();
    }
    async borrarapacitacion(event) {
        event.preventDefault();
        const folio = document.getElementById('folio').value;
        if (folio === '') {
            Swal.fire('Ups!!!', 'No hay un folio seleccionado', 'warning');
            return false;
        }
        const dataf = new FormData();
        dataf.append('folio', folio);
        const dataraw = await fetch('./php/capacitacion.php?deleteCapacitacion', {
            method: "POST",
            body: dataf
        });
        dataraw.ok ? Swal.fire('Listo!!!', 'Se eliminó la capacitación', 'success') :
            Swal.fire('Error!!!', 'Hay un problema al borrar', 'error');
        capacitacion.tblCapacitaciones();
        capacitacion.limpiarCapacitacion(event);
    }
    limpiarCapacitacion(event) {
        event.preventDefault();
        document.getElementById('folio').value = '';
        document.getElementById('fechainicio').value = '';
        document.getElementById('fechafinal').value = '';
        document.getElementById('idcurso').value = '';
        document.getElementById('desccurso').value = '';
        document.getElementById('duracion').value = '';
        document.getElementById('noemp').value = '';
        document.getElementById('instructor').innerHTML = '';
        document.getElementById('coment').value = '';
        document.getElementById('induccion').checked = false;
        document.getElementById('reinduccion').checked = false;
        document.getElementById('tblsubcapacitacion').innerHTML = '';
    }
    chgidcurso(event, env, data) {
        document.getElementById(env).value = document.getElementById(data).value;
    }
    async tblSubCapacitacion() {
        const folio = document.getElementById('folio').value;
        const dataf = new FormData();
        dataf.append('folio', folio);
        const dataraw = await fetch("./php/capacitacion.php?getDatatblSubCapacitacion", {
            method: "POST",
            body: dataf
        });
        const data = await dataraw.json();
        let body = '';
        let cont = 1;
        data.forEach(element => {
            body += `<tr><td>${cont++}</td><td>${element.folio}</td><td>${element.noemp}</td><td>${element.nombre}</td>
            <td>${element.calificacion}</td><td>${element.contesto == 1 ? '<i class="far fa-thumbs-up text-success"></i>' : '<i class="far fa-thumbs-down text-danger"></i>'}</td>
            <td><button class="btn btn-sm btn-danger" onclick="capacitacion.eliminarSubCapacitacion(${element.folio});"><i class="fa-solid fa-delete-left"></i></button></td></tr>`;
        });
        document.getElementById('tblsubcapacitacion').innerHTML = body;
    }
    async eliminarSubCapacitacion(id) {
        const folio = document.getElementById('folio').value;
        const dataf = new FormData();
        dataf.append('folio', folio);
        dataf.append('id', id);
        const dataraw = await fetch('./php/capacitacion.php?deleteSubCapacitacion', {
            method: "POST",
            body: dataf
        });
        dataraw.ok ? Swal.fire('Listo!!!', 'Se eliminó la capacitación', 'success') :
            Swal.fire('Error!!!', 'Hay un problema al borrar', 'error');
        capacitacion.tblSubCapacitacion();
    }
    async guardarSubCapacitacion(event) {
        event.preventDefault();
        const folio = document.getElementById('folio').value;
        const noempcap = document.getElementById('noempcap').value;
        const empleados = document.getElementById('empleados').value;
        const calificacion = document.getElementById('calificacion').value;
        if (folio === '' || noempcap === '' || empleados === '' || calificacion === '') {
            Swal.fire('Ups!!!', 'No puede haber campos vacíos', 'warning')
            return false;
        }
        const dataf = new FormData();
        dataf.append('folio', folio);
        dataf.append('noemp', noempcap);
        dataf.append('calificacion', calificacion);
        const dataraw = await fetch('./php/capacitacion.php?saveSubCapacitacion', {
            method: "POST",
            body: dataf
        });
        dataraw.ok ? (document.getElementById('noempcap').focus(),document.getElementById('noempcap').value='') :
            Swal.fire('Error!!!', 'Hay un problema al borrar', 'error');
        capacitacion.tblSubCapacitacion();
    }
    start() {
        this.llnarslcCatalogo("CatalogoCursos","GetSlcCursos", "desccurso",0);
        this.llnarslcCatalogo("CatalogoCursos","GetSlcEmpleados", "empleados",0);
        this.tblCapacitaciones();
        document.getElementById('desccurso').addEventListener('change', this.slcIntructores);
        document.getElementById('guardar').addEventListener('click', this.guardarCapacitacion);
        document.getElementById('eliminar').addEventListener('click', this.borrarapacitacion);
        document.getElementById('limpiar').addEventListener('click', this.limpiarCapacitacion);
        document.getElementById('desccurso').addEventListener('change', e => { this.chgidcurso(e, 'idcurso', 'desccurso') });
        document.getElementById('instructor').addEventListener('change', e => { this.chgidcurso(e, 'noemp', 'instructor') });
        document.getElementById('idcurso').addEventListener('change', e => { this.chgidcurso(e, 'desccurso', 'idcurso'), this.slcIntructores() });
        document.getElementById('noemp').addEventListener('change', e => { this.chgidcurso(e, 'instructor', 'noemp') });
        document.getElementById('guardaremp').addEventListener('click', this.guardarSubCapacitacion);
        document.getElementById('empleados').addEventListener('change', e => { this.chgidcurso(e, 'noempcap', 'empleados') });
        document.getElementById('noempcap').addEventListener('keyup', e => { this.chgidcurso(e, 'empleados', 'noempcap') });
    }
}