// tblReporteIMC
import { Toolsjs } from "../../Tools/Tools.js";
import { IMC } from "../modules/IMC.js";
const tools = new Toolsjs();
const imc = new IMC();
tools.llnarslc('CatalogoPersonal', 'GetSlcDeps', 'departamentos', 0);
tools.llnarslc("CatalogoSeguridad", "GetSlcIMCEstado", "estado", 0);
tools.llnarslc("CatalogoPersonal", "GetSlcDeps", "departamento", 0);
tools.llnarslc("CatalogoSeguridad", "GetSlcDeteccionRiesto", "detriesgo", 0);
tools.llnarslc("CatalogoSeguridad", "GetSlcTipoRiesgo", "tiporiesgo", 0);
tools.llnarslc("CatalogoSeguridad", "GetSlcTipo", "tipo", 0);
tools.llnarslc("CatalogoSeguridad", "GetSlcIMCEstado", "estadoimc", 0);

document.getElementById("departamentos").addEventListener("change", function () {
    const dep = document.getElementById("departamentos").value;
    tools.llnarslc('CatalogoSeguridad', 'GetSlcAreas&dep=' + dep, "areas", 0);
})
document.getElementById("departamento").addEventListener("change", () => {
    const dep = document.getElementById("departamento").value;
    tools.llnarslc("CatalogoSeguridad", "GetSlcAreas&dep=" + dep, "area", 0);
});
document.getElementById("noempemisor").addEventListener("keyup", function (e) {
    e.preventDefault();
    const noemp = document.getElementById("noempemisor").value;
    noemp != "" && imc.getinfoemp(noemp, "nombreemisor", "depemisor", "");
});
document.getElementById("responsable").addEventListener("keyup", function (e) {
    e.preventDefault();
    const noemp = document.getElementById("responsable").value;
    noemp != "" && imc.getinfoemp(noemp, "responsablenombre", "", "");
});
function consulta() {
    const fechai = document.getElementById('fechai').value;
    const fechaf = document.getElementById('fechaf').value;
    const departamentos = document.getElementById('departamentos').value;
    const areas = document.getElementById('areas').value;
    const noemp = document.getElementById('noemp').value;
    const estadoimc = document.getElementById('estadoimc').value;
    if (fechai == '' || fechaf == '') {
        swal.fire('UPS!!', 'El intervalo de fechas es obligatorio', 'warning')
        return false;
    }
    imc.tblReporteIMC(fechai, fechaf, departamentos, areas, noemp, estadoimc);
}
document.getElementById('consulta').addEventListener('click', (e) => {
    e.preventDefault();
    consulta();
})
document.getElementById('exportarexcel').addEventListener('click', () => tools.exportartablaexcel('tblimc'));
const modal = new bootstrap.Modal(document.getElementById('modalRepimc'));
document.getElementById('guardarcambiosimc').addEventListener('click', function (e) {
    e.preventDefault();
    const id = document.getElementById('idimc').value;
    const noempemisor = document.getElementById('noempemisor').value;
    const departamento = document.getElementById('departamento').value;
    const area = document.getElementById('area').value;
    const detriesgo = document.getElementById('detriesgo').value;
    const tiporiesgo = document.getElementById('tiporiesgo').value;
    const tipo = document.getElementById('tipo').value;
    const descripcion = document.getElementById('descripcion').value;
    const responsable = document.getElementById('responsable').value;
    const sugerencias = document.getElementById('sugerencias').value;
    const fechacompromiso = document.getElementById('fechacompromiso').value;
    const estado = document.getElementById('estado').value;
    if (id === '' || estado === '') {
        swal.fire('UPS!!', 'Debes seleccionar un estado del IMC', 'warning')
        return false;
    }
    imc.updateEstadoIMC(id, noempemisor, departamento, area, detriesgo, tiporiesgo, tipo,
         descripcion, responsable, sugerencias,fechacompromiso, estado).then(() => consulta())
    modal.hide();
})
const modalRepimc = document.getElementById('modalRepimc')
modalRepimc.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget
    document.getElementById('estado').value = '';
    const recipient = button.getAttribute('data-bs-whatever')
    document.getElementById('idimc').value = recipient;
    document.getElementById('folioencmodal').innerHTML = recipient;
    imc.getDataIMCxid(recipient);
});