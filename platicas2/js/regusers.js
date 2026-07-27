setTimeout(() => {
    document.getElementById('btnconfirm').style.display = 'block';
}, 10000)
document.addEventListener("DOMContentLoaded", function () {
    const idplatica = document.getElementById('idplatica').value;
    (async () => {
        const respuestaraw = await fetch('php/platicas.php?getPlaticatoday');
        const respueta = await respuestaraw.json();
        document.getElementById('idplatica').value = respueta[0].id;
        document.getElementById('archivo').innerHTML = `<embed src="platicas/${respueta[0].ruta}"></embed>`;
    })();
});
document.getElementById('btnconfirm').addEventListener('click', function (e) {
    e.preventDefault();
    (async () => {
        const idplatica = document.getElementById('idplatica').value;
        const respuestaraw = await fetch('php/platicas.php?confirmaAsistencia&idplatica=' + idplatica);
        respuestaraw.status == 200 && swal.fire('Listo!!!', 'Se registro tu asistencia', 'success');
        respuestaraw.status == 201 && swal.fire('Gracias!!!', 'Ya estabas registrado', 'success');
        respuestaraw.status == 500 && swal.fire('Error!!!', 'hay un problema al consultar tu asistencia', 'error');
    })()
})