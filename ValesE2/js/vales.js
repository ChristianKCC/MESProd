import { Toolsjs } from "../../Tools/Tools.js";
import { ValesMaterial } from "../module/ValesM.js"

const Tools = new Toolsjs();
const Vales = new ValesMaterial();
Tools.llnarslc('CatalogosBitacora', 'GetClavesValesE', 'clave1', 0);
Tools.llnarslc('CatalogosBitacora', 'GetClavesValesE', 'clave2', 0);
Tools.llnarslc('CatalogosBitacora', 'GetClavesValesE', 'clave3', 0);
Tools.llnarslc('CatalogosBitacora', 'GetClavesValesE', 'clave4', 0);
Tools.llnarslc('CatalogoPersonal', 'GetSlcMaquinas', 'maquinasvales', 0);
Tools.llnarslc('CatalogosBitacora', 'GetValesEEstados', 'estadoslist', 0);
Tools.llnarslc('CatalogosBitacora', 'GetTurnos', 'turnoslist', 0);
setInterval(() => Tools.mostrarHora(), 1000);
document.getElementById('noemp').addEventListener('keyup', function () {
    const noemp = document.getElementById('noemp').value;
    noemp != '' && Vales.datosemp(noemp).then((element) => {
        if (element != false) {
            document.getElementById('nombre').value = element[0].nombre;
            document.getElementById('puesto').value = element[0].puesto;
        }
    });
})
function llenarclase() {
    const clave1 = document.getElementById('clave1').value;
    const clave2 = document.getElementById('clave2').value;
    const clave3 = document.getElementById('clave3').value;
    const clave4 = document.getElementById('clave4').value;
    Vales.SelectClasesM(clave1, clave2, clave3, clave4);
}
document.getElementById('clave1').addEventListener('change', function () {
    llenarclase();
})
document.getElementById('clave2').addEventListener('change', function () {
    llenarclase();
})
document.getElementById('clave3').addEventListener('change', function () {
    llenarclase();
})
document.getElementById('clave4').addEventListener('change', function () {
    llenarclase();
})
document.getElementById('clase').addEventListener('change', function () {
    const clase = document.getElementById('clase').value;
    const clave1 = document.getElementById('clave1').value;
    const clave2 = document.getElementById('clave2').value;
    const clave3 = document.getElementById('clave3').value;
    const clave4 = document.getElementById('clave4').value;
    Vales.tblMateriales(clase, clave1, clave2, clave3, clave4).then((element) => {
        let body = '';
        element.forEach(listado => {
            body += `<tr><td>${listado.NoMaterial}</td><td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
            <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td>
            <td><button class="btn btn-sm btn-success" onclick="addMaterial(${listado.NoMaterial}); return false;"><i class="fas fa-plus-square"></i></button></td></tr>`;
        })
        document.getElementById('tblmateriales').innerHTML = body;
    });
})



window.addMaterial = function (param) {
    Vales.addMaterial(param);
}
document.getElementById('crearVale').addEventListener('click', function (e) {
    e.preventDefault();
    Vales.validaUltimoVale().then((element) => {
        if (element.length === 0) {
            const noemp = document.getElementById('noemp').value;
            const turno = document.getElementById('turnoen').value;
            const clave1 = document.getElementById('clave1').value;
            const clave2 = document.getElementById('clave2').value;
            const clave3 = document.getElementById('clave3').value;
            const clave4 = document.getElementById('clave4').value;
            (noemp === '' || turno === '' || clave1 === '') ?
                swal.fire('Ups!', 'Los campos Noemp, turno y Clave 1 son obligatorios', 'warning') :
                Vales.creaVale(noemp, turno, clave1, clave2, clave3, clave4).then((element) => {
                    document.getElementById('foliovale').value = element[0].id;
                    document.getElementById('foliocons').value = element[0].folio;
                    console.log(element);
                });
        } else {
            swal.fire('Vale incompleto', 'Hay un vale existente, debes terminarlo o cancelarlo.', 'warning');
            document.getElementById('noemp').value = element[0].noemp;
            Vales.datosemp(element[0].noemp).then((element) => {
                if (element != false) {
                    document.getElementById('nombre').value = element[0].nombre;
                    document.getElementById('puesto').value = element[0].puesto;
                }
            });
            document.getElementById('turnoen').value = element[0].turno;
            document.getElementById('clave1').value = element[0].clave1 === 0 ? '' : element[0].clave1;
            document.getElementById('clave2').value = element[0].clave2 === 0 ? '' : element[0].clave2;
            document.getElementById('clave3').value = element[0].clave3 === 0 ? '' : element[0].clave3;
            document.getElementById('clave4').value = element[0].clave4 === 0 ? '' : element[0].clave4;
            document.getElementById('foliovale').value = element[0].id;
            document.getElementById('foliocons').value = element[0].foliocons;
            Vales.tblMaterialesAgregados(element[0].id).then((infotbl) => {
                let body = '';
                infotbl.forEach(listado => {
                    body += `<tr><td>${listado.folio}</td><td>${listado.NoMaterial}</td><td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
                    <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td><td>${listado.Cantidad}  ${listado.UM} </td>
                    <td><button class="btn btn-sm btn-danger" onclick="deleteMateriales(${listado.folio}); return false;"><i class="fas fa-backspace"></i></button></td></tr>`;
                })
                document.getElementById('tblmaterialesagregados').innerHTML = body;
            });
            llenarclase();
        }
    });

})

document.getElementById('cancelar').addEventListener('click', function (e) {
    e.preventDefault();
    const folio = document.getElementById('foliovale').value;
    folio != '' ? Vales.cancelarVale(folio).then(() => Vales.limpiar()) : swal.fire('Hay un problema', 'No hay un folio seleccionado', 'warning');
});
document.getElementById('enviar').addEventListener('click', function (e) {
    e.preventDefault();
    const folio = document.getElementById('foliovale').value;
    folio != '' ? Vales.enviarVale(folio).then(() => Vales.limpiar()) : swal.fire('Hay un problema', 'No hay un folio seleccionado', 'warning');
});
document.getElementById('limpiarinicio').addEventListener('click', function (e) {
    e.preventDefault();
    Vales.limpiar();
});
document.getElementById('buscar').addEventListener('click', function (e) {
    e.preventDefault();
    const fechai = document.getElementById('fechaivales').value;
    const fechaf = document.getElementById('fechafvales').value;
    const maquinas = document.getElementById('maquinasvales').value;
    const turno = document.getElementById('turnoslist').value;
    const estado = document.getElementById('estadoslist').value;
    (fechai === '' || fechaf === '') ?
        swal.fire('Ups', 'El rango de fecha es obligatorio', 'warning') :
        Vales.tblValesCreados(fechai, fechaf, turno, estado, maquinas);
})
window.deleteMateriales = function (param) {
    Vales.deleteMateriales(param).then(() => {
        const folio = document.getElementById('foliovale').value;
        Vales.tblMaterialesAgregados(folio).then((infotbl) => {
            let body = '';
            infotbl.forEach(listado => {
                body += `<tr><td>${listado.folio}</td><td>${listado.NoMaterial}</td><td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
                <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td><td>${listado.Cantidad}  ${listado.UM} </td>
                <td><button class="btn btn-sm btn-danger" onclick="deleteMateriales(${listado.folio}); return false;"><i class="fas fa-backspace"></i></button></td></tr>`;
            })
            document.getElementById('tblmaterialesagregados').innerHTML = body;
        });;
    });
}
window.addmm2material = function (param) {
    Vales.tblMaterialesAgregados(param).then((infotbl) => {
        let body = '';
        infotbl.forEach(listado => {
            body += `<tr><td>${listado.folio}</td><td>${listado.NoMaterial}</td><td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
            <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td><td>${listado.Cantidad}  ${listado.UM} </td>
            ${listado.estadomat != 2 ? '<td witdth="80px"><input type="number" class="form-control form-control-sm" name="mm2kg" value="' + listado.mm2kg + '"/></td>' : '<td></td>'}
            ${listado.estadomat != 2 ? '<td witdth="80px"><input type="number" class="form-control form-control-sm" name="envasesrec" value="' + listado.envasesrec + '"/></td>' : '<td></td>'}</tr>`;
        });
        document.getElementById('tblmaterialemodal').innerHTML = body;
    })
}
window.cerrarVale = function (param) {
    Vales.CerrarVale(param);
}
document.getElementById('tblvalescambios').addEventListener('input', function (event) {
    const target = event.target;
    if (target.tagName === 'INPUT' && target.name !== '') {
        var id = event.target.closest('tr').querySelector('td:first-child').textContent;
        var mm2 = event.target.closest('tr').querySelector('input[name=mm2kg]').value;
        var envasesrec = event.target.closest('tr').querySelector('input[name=envasesrec]').value;
        mm2 != '' && Vales.saveMM2(id, mm2);
        envasesrec != '' && Vales.saveEnvases(id, envasesrec);
    }
})
document.getElementById('limpiarvales').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('fechaivales').value = ''
    document.getElementById('fechafvales').value = ''
    document.getElementById('maquinasvales').value = ''
    document.getElementById('turnoslist').value = ''
    document.getElementById('estadoslist').value = ''
    document.getElementById('tblmaterialemodal').innerHTML = '';
    document.getElementById('tblValesCreados').innerHTML = '';
})
