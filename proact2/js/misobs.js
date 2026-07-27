(async()=>{
    const respuestaraw = await fetch('php/index.php?misproact');
    const respuesta = await respuestaraw.json();
    console.log(respuesta);
    let body = '';
    respuesta.forEach(elemento => {
        body += `<tr><td>${elemento.id}</td><td>${elemento.Observacion}</td><td>${elemento.Observado}</td>
        <td>${elemento.Obsnombre}</td><td>${elemento.opcion == 1 ? "SI" : "NO"}</td>
        <td>${elemento.Area}</td><td>${elemento.Maquina}</td><td>${elemento.Fecha}</td><td>${elemento.Hora}</td><td>${elemento.Comentario}</td>
        <td>${elemento.Otra}</td><td>${elemento.Deacuerdo == 1 ? "SI" : "NO"}</td>
        </tr>`;
    })
    document.getElementById("tableobs").innerHTML = body;
})();