class Perfil{
    perfil(){
        this.consultAsistencia();
        this.graficaCursos();
        this.consultasEnfermeria();
    }
    async  consultAsistencia() {
        let noemp = document.getElementById("noempenc").value;
        const respuestaraw = await fetch("./php/empleados.php?consultaasistencias&noemp="+noemp);
        const respuesta = await respuestaraw.json();
        let body = "";
        respuesta.forEach(elemento => {
            body += "<tr><td>" + elemento.ibm + "</td><td>" + elemento.nombre + "</td><td>" + elemento.fecha + "</td></tr>";
        });
        document.getElementById("tblasistencias").innerHTML = body;  
    };
    async graficaCursos(){
        let noemp = document.getElementById("noempenc").value;
        const respuestaRaw = await fetch("./php/empleados.php?grafcursos&ibm=" + noemp);
        const respuesta = await respuestaRaw.json();
        const grafica = document.querySelector("#chartcursos");
        const etiquetas = respuesta.etiquetas;
        const datosVentas2020 = {
            data: respuesta.datos,
            backgroundColor: respuesta.colores,
            borderColor: 'rgba(0, 0, 0, .2)',
            borderWidth: 2,
        };
        new Chart(grafica, {
            type: 'doughnut',
            data: {
                labels: etiquetas,
                datasets: [
                    datosVentas2020,
                ]
            },
    
            options: {
    
            }
        })
    }
    async  consultasEnfermeria() {
        let noemp = document.getElementById("noempenc").value;
        const respuestaraw = await fetch("./php/empleados.php?consultasEnfermeria&noemp="+noemp);
        const respuesta = await respuestaraw.json();
        let body = "";
        respuesta.forEach(elemento => {
            body += `<tr><td>${elemento.id}</td><td>${elemento.noemp}</td><td>${elemento.Nombre}</td><td>${elemento.NombreDepto}</td>
            <td>${elemento.edad}</td><td>${elemento.tratamiento}</td><td>${elemento.observacion}</td><td>${elemento.equipomedico}</td>
            <td>${elemento.enfermedad}</td><td>${elemento.fecha}</td></tr>`;
        });
        document.getElementById("tblconsultasEnfermeria").innerHTML = body;  
    };
}