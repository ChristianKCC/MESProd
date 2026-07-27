class SemaforoManager {
    constructor(maquinaId) {
        this.maquinaId = maquinaId;
        this.currentTurno = null;
        
        // Elementos DOM
        this.luzAmarillo = document.getElementById(`luzAmarillo${maquinaId}`);
        this.luzNaranja = document.getElementById(`luzNaranja${maquinaId}`);
        this.luzRojo = document.getElementById(`luzRojo${maquinaId}`);
        this.tachesContainer = document.getElementById(`tachesContainer${maquinaId}`);
    }

    async obtenerRegistros() {
        try {
            // Ahora pasamos el parámetro maquina en la URL
            const response = await fetch(`../produccion/php/semaforo.php?obtenerRegistrosTurno&maquina=${this.maquinaId}`);
            const data = await response.json();
            console.log(this.maquinaId);
            
            if(data.success){
                this.currentTurno = data.turno_actual;
                const totalRegistros = data.total_registros_turno;
                this.actualizarSemaforoTaches(totalRegistros);
            } else {
                console.error(`Error en respuesta para ${this.maquinaId}:`, data.error);
            }
        } catch (error) {
            console.error(`Error al obtener estadísticas para ${this.maquinaId}:`, error);
        }
    }

    actualizarSemaforoTaches(cantidad) {
        // Semáforo
        const amarillo = cantidad >= 1;
        const naranja = cantidad >= 2;
        const rojo = cantidad >= 3;
        
        if (this.luzAmarillo) {
            if (amarillo) this.luzAmarillo.classList.add('active');
            else this.luzAmarillo.classList.remove('active');
        }
        
        if (this.luzNaranja) {
            if (naranja) this.luzNaranja.classList.add('active');
            else this.luzNaranja.classList.remove('active');
        }
        
        if (this.luzRojo) {
            if (rojo) this.luzRojo.classList.add('active');
            else this.luzRojo.classList.remove('active');
        }
        
        // Taches
        if (this.tachesContainer) {
            this.tachesContainer.innerHTML = '';
            
            if (cantidad === 0) {
                this.tachesContainer.innerHTML = '<div style="color:#5b6e8c;">Sin registros en este turno</div>';
                return;
            }
            
            for (let i = 0; i < Math.min(cantidad, 3); i++) {
                const div = document.createElement('div');
                div.className = 'tache';
                div.innerText = '✗';
                this.tachesContainer.appendChild(div);
            }
            
            if (cantidad > 3) {
                const div = document.createElement('div');
                div.className = 'tache mas';
                div.innerText = '+';
                this.tachesContainer.appendChild(div);
            }
        }
    }

    async init() {
        await this.obtenerRegistros();
        // Actualizar cada minuto
        setInterval(() => this.obtenerRegistros(), 60000);
    }
}

// Inicializar todas las máquinas
const maquinas = [60, 61, 62, 63, 64, 65];
const semaforos = {};

async function inicializarTodo() {
    for (const maquina of maquinas) {
        const semaforo = new SemaforoManager(maquina);
        semaforos[maquina] = semaforo;
        await semaforo.init();
    }
}

inicializarTodo();
export default SemaforoManager;