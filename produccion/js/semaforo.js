import { Semaforo } from "../module/Semaforo.js";
const SemaforoObj = new Semaforo();

function actualizarSemaforos() {
    SemaforoObj.obtenerRegistros('tachesContainerBCM4', 'luzAmarilloBCM4', 'luzNaranjaBCM4', 'luzRojoBCM4', 60);
    SemaforoObj.obtenerRegistros('tachesContainerBCM3', 'luzAmarilloBCM3', 'luzNaranjaBCM3', 'luzRojoBCM3', 61);
    SemaforoObj.obtenerRegistros('tachesContainerPE10', 'luzAmarilloPE10', 'luzNaranjaPE10', 'luzRojoPE10', 62);
    SemaforoObj.obtenerRegistros('tachesContainerBCM1', 'luzAmarilloBCM1', 'luzNaranjaBCM1', 'luzRojoBCM1', 63);
    SemaforoObj.obtenerRegistros('tachesContainerMP22', 'luzAmarilloMP22', 'luzNaranjaMP22', 'luzRojoMP22', 64);
    SemaforoObj.obtenerRegistros('tachesContainerMP25', 'luzAmarilloMP25', 'luzNaranjaMP25', 'luzRojoMP25', 65);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', actualizarSemaforos, { once: true });
} else {
    actualizarSemaforos();
}

setInterval(actualizarSemaforos, 30000);