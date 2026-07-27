import { Toolsjs } from "../../Tools/Tools.js";
import { NoConfromidad } from "../module/modnoconformidad.js";
const tools = new Toolsjs();
tools.llnarslc('CatalogoPersonal','GetSlcDeps','departamentos',0);
tools.llnarslc('CatalogoPersonal','GetSlcDeps', 'departamentomodal', 0);
tools.llnarslc('CatalogosBitacora','empleadosallTraz', 'calidadmodal', 0);
document.getElementById('departamentomodal').addEventListener('change',()=>{
    const departamentomodal = document.getElementById('departamentomodal').value;
    tools.llnarslc('CatalogosBitacora','GetDefectosxdep&deps='+departamentomodal, 'defectomodal', 0);
})
document.getElementById("departamentos").addEventListener("change", function () {
    const dep = document.getElementById("departamentos").value;
    tools.llnarslc('CatalogoSeguridad','GetSlcAreas&dep=' + dep, "maquina",0);
})
document.getElementById('consulta').addEventListener('click',function(e){
    e.preventDefault();
    const noconf = new NoConfromidad();
    const fechai = document.getElementById('fechai').value;
    const fechaf = document.getElementById('fechaf').value;
    const departamento = document.getElementById('departamentos').value;
    const maquina = document.getElementById('maquina').value;
    if(fechai == '' || fechaf==''){
        swal.fire('UPS!!!','El intervalo de fecha es obligatorio','warning');
        return false;
    }
    noconf.tblReporteNoConformidad(fechai,fechaf,departamento,maquina);
})
const modal = document.getElementById('modalrepnocof')
modal.addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget
  const recipient = button.getAttribute('data-bs-whatever')
  const modalTitle = modal.querySelector('.modal-title')
  const modalBodyInput = modal.querySelector('.modal-body input')
  modalTitle.textContent = 'Estas editando el folio: ' + recipient
  modalBodyInput.value = recipient
  const noconf = new NoConfromidad();
  const data = noconf.dataNoconf(recipient);
  data.then(element=>{
    document.getElementById('departamentomodal').value=element[0].departamento;
    tools.llnarslc('CatalogosBitacora','GetDefectosxdep&deps='+element[0].departamento, 'defectomodal', 0).then(()=>{
        document.getElementById('defectomodal').value=element[0].defecto;
     });
   });
})
document.getElementById('saveUpdateNoconf').addEventListener('click',function(e){
    e.preventDefault();
    const folio = document.getElementById('folioNoconf').value;
    const departamento = document.getElementById('departamentomodal').value;
    const defecto = document.getElementById('defectomodal').value;
    const calidad = document.getElementById('calidadmodal').value;
    const fechai = document.getElementById('fechai').value;
    const fechaf = document.getElementById('fechaf').value;
    const departamentotbl = document.getElementById('departamentos').value;
    const maquina = document.getElementById('maquina').value;
    const noconf = new NoConfromidad();
    noconf.saveUpdateNoConf(folio,departamento,defecto,calidad).then(()=>noconf.tblReporteNoConformidad(fechai,fechaf,departamentotbl,maquina));
})
document.getElementById('exportarexcel').addEventListener('click',()=>tools.exportartablaexcel('tblnoconformidad'));
