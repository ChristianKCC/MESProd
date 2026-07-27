import { Toolsjs } from "../../Tools/Tools.js";
import { Platicas5min } from "../modules/platicasmod.js";
const Tools = new Toolsjs();
const platicas5min = new Platicas5min();
Tools.llnarslc('CatalogoSeguridad', 'GetSlcTipoPlaticas5', 'tipo', 0);
platicas5min.tblPlaticas();
const noemp = document.getElementById('noemp');
const nombre = document.getElementById('nombre');
const fecha = document.getElementById('fecha');
const tipo = document.getElementById('tipo');
const nombreplatica = document.getElementById('nombreplatica');
const minutos = document.getElementById('minutos');
document.getElementById('noemp').addEventListener('keyup', function (event) {
    event.preventDefault();
    const noemp = document.getElementById('noemp').value;
    Tools.getDataEmpleado(noemp, 'nombre', 'departamento', '');
})
document.getElementById('guardarEnc').addEventListener('click', (event) => {
    event.preventDefault();
    platicas5min.savePlatica(noemp.value, nombre.value, fecha.value, tipo.value, nombreplatica.value, minutos.value).then((element) => {
        if (element != false) {
            platicas5min.tblPlaticas();
            document.getElementById('formenc').reset();
        }
    });
})

window.deletePlatica = (data) => platicas5min.deletePlatica(data);