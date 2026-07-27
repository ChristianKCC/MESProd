
(async function datahome() {
    const respuestaraw = await fetch('./php/index.php?getDataNoCursos');
    const respuesta = await respuestaraw.json();
    document.getElementById('cursos').innerHTML = respuesta[0].cursos;
    document.getElementById('proact').innerHTML = respuesta[0].proact;
    document.getElementById('IMC').innerHTML = respuesta[0].IMC;
})();
const modal = new bootstrap.Modal(document.getElementById('modalpassword'));
(async function modalopen() {
    const respuestaraw = await fetch('./php/index.php?validaChgPassword');
    const respuesta = await respuestaraw.json();
    respuesta === 'nocambiado' && modal.show();
})();

document.getElementById('cambiarcontrasena').addEventListener('click', function (e) {
    e.preventDefault();
    const contrasena = document.getElementById('contrasena').value;
    const contrasenaconf = document.getElementById('contrasenaconf').value;
    if (contrasena != contrasenaconf) {
        swal.fire('Hay un problema', 'Las contraseñas no coinciden', 'error');
        return false;
    } else if (contrasena.length < 10) {
        swal.fire('Lo siento', 'La contraseña debe contener al menos 10 digitos', 'warning');
        return false;
    } else {
        (async () => {
            const data = new FormData();
            data.append('password', contrasena);
            const respuestaraw = await fetch('./php/index.php?updatePassword', {
                method: 'POST',
                body: data
            });
            respuestaraw.ok ?
                swal.fire('Gracias', 'La contraseña se actualizo correctamente', 'success') :
                swal.fire('ERROR', 'Hay un problema al actualizar tu contraseña', 'error');
            modal.hide();
        })();
    }
})

const ctx = document.getElementById('canvasgraf');
const myChart = new Chart(ctx, {
    type: 'radar',
    data: {
        labels: ['SEGURIDAD', 'Orden y limpieza', 'Calidad', 'Manejo de activos', 'Materia Prima', 'Costos', 'RI', 'Producción', 'Servicios'],
        datasets: [{
            label: 'Responsabilidades',
            data: [10, 10, 10, 10, 10, 10, 10, 10, 10],
            backgroundColor: 'rgba(0, 109, 160, 0.12)',
            borderColor: [
                'rgb(0, 109, 160)',
            ],
            borderWidth: 1
        }]
    }
});