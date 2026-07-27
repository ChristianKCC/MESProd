import { Toolsjs } from "../../Tools/Tools.js";
import { Entregas } from "../modules/prodmodules.js";

let folio = document.getElementById('folio');
let fecha = document.getElementById('fecha');
let maquinas = document.getElementById('maquinas');
let clave = document.getElementById('clave');
let Entregado = document.getElementById('Entregado');

const ToolsObj = new Toolsjs();
const idsCamposObligatorios = ['folio', 'fecha', 'maquinas', 'clave', 'Entregado'];
const idsCamposlimpiarsub = ['folio', 'fecha', 'maquinas', 'clave', 'Entregado'];


const EntregasObj = new Entregas(folio, fecha, maquinas, clave, Entregado);
EntregasObj.tblEntregados().then(element => {
    document.getElementById('tblEntregados').innerHTML = element
})

ToolsObj.llnarslc("CatalogoPersonal", "GetSlcMaquinas", "maquinas", 0);
document.getElementById("maquinas").addEventListener("change", (e) => {
    const maquina = e.target.value;
    maquina === '' ? document.getElementById('clave').innerHTML = '' : ToolsObj.llnarslc("CatalogosBitacora", "GetClavesxmaquina&maquina=" + maquina, "clave", 0);
});
document.getElementById('btnsave').addEventListener('click', (e) => {
    e.preventDefault();
    const res = ToolsObj.validarCamposPorID(idsCamposObligatorios);
    res != false && EntregasObj.saveEntregas().then(() => {
        EntregasObj.tblEntregados().then(element => {
            document.getElementById('tblEntregados').innerHTML = element
        })
    });
});
document.getElementById('btnclean').addEventListener('click',(e)=>{
    e.preventDefault();
    ToolsObj.limpiarCamposPorID(idsCamposlimpiarsub)
})


window.deleteEntregas = (fila,event)=> EntregasObj.deleteEntregas(fila,event);