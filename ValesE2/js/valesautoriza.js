import { ValesMaterial } from "../module/ValesM.js";
import { Toolsjs } from "../../Tools/Tools.js";

const Valesm = new ValesMaterial();
const Tools = new Toolsjs();
Tools.llnarslc('CatalogosBitacora', 'GetValesEEstados', 'estadoslist', 0);
Tools.llnarslc('CatalogosBitacora', 'GetTurnos', 'turnoslist', 0);
Tools.llnarslc('CatalogoPersonal', 'GetSlcMaquinas', 'maquinas', 0);
function llenarclase() {
    const clave1 = document.getElementById('clave1').value;
    const clave2 = document.getElementById('clave2').value;
    const clave3 = document.getElementById('clave3').value;
    const clave4 = document.getElementById('clave4').value;
    const maquinaid = document.getElementById('maquinaid').value;
    Valesm.SelectClasesM(clave1, clave2, clave3, clave4, maquinaid);
}
document.getElementById('buscar').addEventListener('click', function (e) {
    e.preventDefault();
    const fechai = document.getElementById('fechai').value;
    const fechaf = document.getElementById('fechaf').value;
    const maquinas = document.getElementById('maquinas').value;
    const turno = document.getElementById('turnoslist').value;
    const estado = document.getElementById('estadoslist').value;
    (fechai === '' || fechaf === '') ?
        swal.fire('Ups', 'El rango de fecha es obligatorio', 'warning') :
        Valesm.tblValesautoriza(fechai, fechaf, turno, estado, maquinas);
})
const modalenv = document.getElementById('modalenv');
modalenv.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget
    const recipient = button.getAttribute('data-bs-whatever')
    const modalBodyInput = modalenv.querySelector('.modal-body input')
    let btncancelar = 0;
    Valesm.ValesConstxid(recipient).then((element) => {
        document.getElementById('clave1').value = element[0].clave1;
        document.getElementById('clave2').value = element[0].clave2;
        document.getElementById('clave3').value = element[0].clave3;
        document.getElementById('clave4').value = element[0].clave4;
        document.getElementById('noemp').value = element[0].noemp;
        document.getElementById('nombre').value = element[0].nombre;
        document.getElementById('maquina').value = element[0].maquina;
        document.getElementById('maquinaid').value = element[0].maquinaid;
        document.getElementById('turno').value = element[0].turno;
        document.getElementById('fechac').value = element[0].fechac;
        document.getElementById('fechae').value = element[0].fechae;
        document.getElementById('estado').value = element[0].estado;
        document.getElementById('folioencvista').innerHTML = 'Folio: ' + element[0].maquina + ' - ' + element[0].foliocons
        element[0].estado != "Enviado" ? (document.getElementById('validaenvio').hidden = true) : (document.getElementById('validaenvio').hidden = false);
        element[0].estado == 'Enviado' ? btncancelar = 1 : btncancelar = 0;
        element[0].estado == 'Enviado' ? (document.getElementById('formaddmat').style.display = "block") : (document.getElementById('formaddmat').style.display = "none");
        llenarclase();
        Valesm.tblMaterialesAgregados(recipient).then((infotbl) => {
            let body = '';
            infotbl.forEach(listado => {
                body += `<tr ${listado.estadomat == 2 && 'class="text-danger"'}><td>${listado.folio}</td><td>${listado.NoMaterial}</td><td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
                <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td><td>${listado.Cantidad}  ${listado.UM} </td>
                <td>${btncancelar == 1 ? '<button class="btn btn-sm btn-danger" onclick="cancelaMaterial(this,' + listado.folio + ')"><i class="fas fa-random"></i></button>' : ''}</td></tr>`;
            })
            document.getElementById('tblmaterialesagregados').innerHTML = body;
        })
        modalBodyInput.value = recipient;
    });

})
document.getElementById('validaenvio').addEventListener('click', function (e) {
    e.preventDefault();
    const folio = document.getElementById('folio').value;
    Valesm.ValidaEnvio(folio).then(() => {
        const fechai = document.getElementById('fechai').value;
        const fechaf = document.getElementById('fechaf').value;
        const turno = document.getElementById('turnoslist').value;
        const estado = document.getElementById('estadoslist').value;
        (fechai === '' || fechaf === '') ?
            swal.fire('Ups', 'El rango de fecha es obligatorio', 'warning') :
            Valesm.tblValesautoriza(fechai, fechaf, turno, estado);
    });
    const truck_modal = document.querySelector('#modalenv');
    const modal = bootstrap.Modal.getInstance(truck_modal);
    modal.hide();

})
document.getElementById('clase').addEventListener('change', function () {
    const clase = document.getElementById('clase').value;
    const clave1 = document.getElementById('clave1').value;
    const clave2 = document.getElementById('clave2').value;
    const clave3 = document.getElementById('clave3').value;
    const clave4 = document.getElementById('clave4').value;
    const maquinaid = document.getElementById('maquinaid').value;
    Valesm.tblMateriales(clase, clave1, clave2, clave3, clave4, maquinaid).then((element) => {
        let body = '';
        element.forEach(listado => {
            body += `<tr><td>${listado.NoMaterial}</td><td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
            <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td>
            <td><button class="btn btn-sm btn-success" onclick="addMaterial(${listado.NoMaterial}); return false;"><i class="fas fa-plus-square"></i></button></td></tr>`;
        })
        document.getElementById('tblmateriales').innerHTML = body;
    });
})

window.cancelaMaterial = (boton, folio) => Valesm.CancelaMaterial(boton, folio); 
window.addMaterial = function (param) {
    const folio = document.getElementById('folio').value;
    Valesm.ValidaMatRemplazados(folio).then((respuesta) => {
        if (respuesta[0].cont2 == respuesta[0].cont3) {
            alert('Ya no puedes cambiar mas materiales')
            return false;
        }
        Valesm.addMaterialadmin(param).then(() => {
            const folio = document.getElementById('folio').value;
            const estado = document.getElementById('estado').value;
            let btncancelar = 0
            estado == 'Enviado' ? btncancelar = 1 : btncancelar = 0;
            Valesm.tblMaterialesAgregados(folio).then((infotbl) => {
                let body = '';
                infotbl.forEach(listado => {
                    body += `<tr ${listado.estadomat == 2 && 'class="text-danger"'}><td>${listado.folio}</td><td>${listado.NoMaterial}</td><td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
                    <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td><td>${listado.Cantidad}  ${listado.UM} </td>
                    <td>${btncancelar == 1 ? '<button class="btn btn-sm btn-danger" onclick="cancelaMaterial(this,' + listado.folio + ')"><i class="fas fa-random"></i></button>' : ''}</td></tr>`;
                })
                document.getElementById('tblmaterialesagregados').innerHTML = body;
            })
        });
    })

}