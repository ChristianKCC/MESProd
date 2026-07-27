class MisCursos extends Herramientas {

    //constructor
    questions = document.querySelectorAll('.question');
    prevBtn = document.getElementById('prevBtn');
    nextBtn = document.getElementById('nextBtn');
    currentPage = 0;
    foliog = 0;
    idcapacitacion = 0;
    async tblMisCursos() {
        const dataraw = await fetch("php/cursos.php?getDatatblMisCursos");
        const data = await dataraw.json();
        let body = '';
        data.forEach(element => {
            body += `<tr><td>${element.folio}</td><td>${element.curso}</td><td>${element.idenc}</td>
            <td class='text-center'><button class='btn btn-sm bg-target' onclick='cursos.startcurso(${element.folio},${element.idsubenc})'><i class="fa-solid fa-circle-play"></i> Iniciar curso</button></td></tr>`;
        });
        document.getElementById('tblmiscursos').innerHTML = body;
    }
    async startcurso(folio, idcap) {
        this.foliog = folio;
        this.idcapacitacion = idcap;
        document.getElementById('prevBtn').style.display = 'inline'
        document.getElementById('nextBtn').style.display = 'inline'
        document.getElementById('finish').style.display = 'none'
        const dommodas = document.getElementById('cursosmodal');
        const modal = new bootstrap.Modal(dommodas);
        modal.show();
        const dataf = new FormData();
        dataf.append('folio', folio);
        const dataraw = await fetch("php/cursos.php?getFileMisCursos", {
            method: 'POST',
            body: dataf
        });
        const dataruta = await dataraw.json();
        document.getElementById('contcurso').innerHTML = `
        <video  width="100%" height="600px" controls>
        <source src="Archivos/${dataruta[0].ruta}" width="100%" height="600px" type="video/mp4" />
        </video>`;

        const dataraw2 = await fetch('php/cursos.php?getQuestions', {
            method: 'POST',
            body: dataf
        })
        const dataquestions = await dataraw2.json();
        let body = '';
        dataquestions.forEach(element => {
            body += `<div class="question">
                <h3>${element.pregunta}</h3>
                <h5><input type='radio' name='${element.id}' value='1' data-extra='1'> ${element.r1}</h5>
                <h5><input type='radio' name='${element.id}' value='2' data-extra='2'> ${element.r2}</h5>
                <h5><input type='radio' name='${element.id}' value='3' data-extra='3'> ${element.r3}</h5>
            </div>`;
        })
        document.getElementById('questions').innerHTML += body;
        this.questions = document.querySelectorAll('.question');
        this.showPage(this.currentPage);
    }
    async enviarDatos(array) {
        let response = await fetch('php/cursos.php?SaveExamen', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(array)
        });
    }
    async finish() {
        const dataf = new FormData();
        dataf.append('folio', this.foliog);
        const dataraw = await fetch("php/cursos.php?getResCorrect", {
            method: 'POST',
            body: dataf
        });
        const data = await dataraw.json();
        let ratings = 0;
        let array = [];
        data.forEach(element => {
            const radios = document.getElementsByName(element.id);
            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    let radioSeleccionado = radios[i];
                    radioSeleccionado.value == element.respuesta && ratings++;
                    array.push([this.idcapacitacion,radioSeleccionado.name,radioSeleccionado.getAttribute('data-extra')])
                    break;
                }
            }
        })
        console.log(array)
        this.enviarDatos(array);
        ratings = ((ratings / data.length) * 10);
        Swal.fire('Gracias!!!', 'Calificación del curso: ' + ratings.toFixed(2), 'success');
        dataf.append('idcap', this.idcapacitacion);
        dataf.append('calificacion', ratings);
        const datarawCal = await fetch("php/cursos.php?saveCalificacion", {
            method: 'POST',
            body: dataf
        });
        this.idcapacitacion = 0;
        this.folio = 0;
        this.currentPage = 0;
        const truck_modal = document.querySelector('#cursosmodal');
        const modal = bootstrap.Modal.getInstance(truck_modal);
        modal.hide();
        datarawCal.ok ? this.tblMisCursos() :
            Swal.fire('Error!!!', 'No se puede actualizar la información, contacta al administrador', 'error');
    }
    showPage(page) {
        this.questions.forEach((question, index) => {
            if (index === page) {
                question.style.display = 'block';
            } else {
                question.style.display = 'none';
            }
        });
    }
    goToPrevPage() {
        if (this.currentPage > 0) {
            this.currentPage--;
            this.showPage(this.currentPage);
        }
    }
    goToNextPage() {
        if (this.currentPage < this.questions.length - 2) {
            this.currentPage++;
            this.showPage(this.currentPage);
        } else {
            this.currentPage++;
            this.showPage(this.currentPage);
            document.getElementById('prevBtn').style.display = 'none';
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('finish').style.display = 'block';
        }
    }
    start() {
        document.getElementById('prevBtn').addEventListener('click', () => this.goToPrevPage());
        document.getElementById('nextBtn').addEventListener('click', () => this.goToNextPage());
        document.getElementById('finish').addEventListener('click', () => this.finish());
        this.tblMisCursos();
    }

}

