

export class Semaforo{
    
    async obtenerRegistros(tachesContainerId, luzAmarilloId, luzNaranjaId, luzRojoId, maquinaId) {
        let currentTurno = null;
        let horaServidorOffset = 0; // Diferencia entre hora cliente y servidor
        try {
            const response = await fetch(`../produccion/php/semaforo.php?obtenerRegistrosTurno=1&maquina=${maquinaId}`);
            const data = await response.json();
            if(data.success){
                currentTurno = data.turnoActual;
                const totalRegistros = data.total_registros_turno;

                actualizarSemaforoTaches(tachesContainerId, luzAmarilloId, luzNaranjaId, luzRojoId, maquinaId,totalRegistros);
            }
        } catch (error) {
            console.error('Error al obtener estadísticas:', error);
        }
    }

   
}

 function actualizarSemaforoTaches(tachesContainerId, luzAmarilloId, luzNaranjaId, luzRojoId, maquinaId, cantidad) {
    // Semáforo
    const amarillo = cantidad >= 1;
    const naranja = cantidad >= 2;
    const rojo = cantidad >= 3;
    
    if (amarillo) document.getElementById(luzAmarilloId).classList.add('active');
    else document.getElementById(luzAmarilloId).classList.remove('active');
    
    if (naranja) document.getElementById(luzNaranjaId).classList.add('active');
    else document.getElementById(luzNaranjaId).classList.remove('active');
    
    if (rojo) document.getElementById(luzRojoId).classList.add('active');
    else document.getElementById(luzRojoId).classList.remove('active');
    
    // Taches
    document.getElementById(tachesContainerId).innerHTML = '';
    
    if (cantidad === 0) {
        document.getElementById(tachesContainerId).innerHTML = '<div style="color:#5b6e8c;">Sin registros en este turno</div>';
        return;
    }
    
    const taches = [];
    for (let i = 0; i < Math.min(cantidad, 3); i++) {
        taches.push('✗');
    }
    if (cantidad > 3) {
        taches.push('+');
    }
    
    taches.forEach(simbolo => {
        const div = document.createElement('div');
        div.className = 'tache';
        if (simbolo === '+') div.classList.add('mas');
        div.innerText = simbolo;
        document.getElementById(tachesContainerId).appendChild(div);
    });
}