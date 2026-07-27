export class ValesMaterial {
    async datosemp(noemp) {
        const respuestaraw = await fetch(
            "../bitacora/php/bitacora.php?datosemp&noemp=" + noemp
        );
        const respuesta = await respuestaraw.json();
        if (respuesta.length === 0) {
            document.getElementById("nombre").value = '';
            document.getElementById("puesto").value = '';
            return false;
        }
        return respuesta;
    };
    async SelectClasesM(clave1, clave2, clave3, clave4, maquinaid = '') {
        const data = new FormData();
        data.append('clave1', clave1);
        data.append('clave2', clave2);
        data.append('clave3', clave3);
        data.append('clave4', clave4);
        data.append('maquinaid', maquinaid);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?ClaseMat", {
            method: "POST",
            body: data
        });
        const respuesta = await respuestaraw.json();
        let body = '';
        respuesta.forEach(element => {
            body += `<option value='${element.NoClase}'>${element.Nombre}</option>`;
        })
        document.getElementById('clase').innerHTML = body;
        document.getElementById('tblmateriales').innerHTML = '';
    }
    async tblMateriales(idclase, clave1, clave2, clave3, clave4, maquinaid = '') {
        const data = new FormData();
        data.append('idclase', idclase);
        data.append('clave1', clave1);
        data.append('clave2', clave2);
        data.append('clave3', clave3);
        data.append('clave4', clave4);
        data.append('maquinaid', maquinaid);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?tblMateriales", {
            method: "POST",
            body: data
        });
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    async tblMaterialesAgregados(folio) {
        const data = new FormData();
        data.append('folio', folio);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?tblMaterialesAgregados", {
            method: "POST",
            body: data
        });
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    async addMaterial(idmat) {
        const folio = document.getElementById('foliovale').value;
        if (folio === '') {
            swal.fire('Ups!', 'Debe haber un folio seleccionado', 'warning');
            return false;
        }
        Swal.fire({
            title: 'Envases Solicitados',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancelar',
        }).then(async (result) => {
            if (result.isConfirmed) {
                if (/^\d+$/.test(result.value)) {
                    const data = new FormData();
                    data.append('idmat', idmat);
                    data.append('folio', folio);
                    data.append('cantidad', result.value);
                    const respuestaraw = await fetch("../ValesE/php/Vales.php?addMaterial", {
                        method: "POST",
                        body: data
                    });
                    respuestaraw.ok ? null : swal.fire('Error', 'Error al agregar el material', 'error');
                } else {
                    swal.fire('Hay un problema', 'El numero no es valido', 'warning');
                }
            }
        }).then(() => {
            const folio = document.getElementById('foliovale').value;
            this.tblMaterialesAgregados(folio).then((infotbl) => {
                let body = '';
                infotbl.forEach(listado => {
                    body += `<tr><td>${listado.folio}</td><td>${listado.NoMaterial}</td><td>${listado.NombreMaterial}</td><td>${listado.CentroCosto}</td>
                    <td>${listado.TiempoMaterial}</td><td>${listado.TipoMontacargas}</td><td>${listado.Cantidad}  ${listado.UM} </td>
                    <td><button class="btn btn-sm btn-danger" onclick="deleteMateriales(${listado.folio}); return false;"><i class="fas fa-backspace"></i></button></td></tr>`;
                })
                document.getElementById('tblmaterialesagregados').innerHTML = body;
            })
        })

    }
    async addMaterialadmin(idmat) {
        const folio = document.getElementById('foliovale').value;
        if (folio === '') {
            swal.fire('Ups!', 'Debe haber un folio seleccionado', 'warning');
            return false;
        }
        let valor = prompt("Envases Solicitados", 0);
        if (/^\d+$/.test(valor)) {
            const data = new FormData();
            data.append('idmat', idmat);
            data.append('folio', folio);
            data.append('cantidad', valor);
            data.append('estado', 3);
            const respuestaraw = await fetch("../ValesE/php/Vales.php?addMaterialadmin", {
                method: "POST",
                body: data
            });
            respuestaraw.ok ? null : swal.fire('Error', 'Error al agregar el material', 'error');
        } else {
            swal.fire('Hay un problema', 'El numero no es valido', 'warning');
        }
    }
    async deleteMateriales(folio) {
        const data = new FormData();
        data.append('folio', folio);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?deleteMateriales", {
            method: "POST",
            body: data
        });
        respuestaraw.ok ? null : swal.fire('Error', 'Error al agregar el material', 'error');
    }
    async validaUltimoVale() {
        const respuestaraw = await fetch("../ValesE/php/Vales.php?validaUltimoVale");
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    async creaVale(noemp, turno, clave1, clave2, clave3, clave4) {
        const data = new FormData();
        data.append('noemp', noemp);
        data.append('turno', turno);
        data.append('clave1', clave1);
        data.append('clave2', clave2);
        data.append('clave3', clave3);
        data.append('clave4', clave4);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?saveValeElectronico", {
            method: "POST",
            body: data
        });
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    async cancelarVale(folio) {
        const data = new FormData();
        data.append('folio', folio);
        data.append('estado', 2);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?actualizaEstado", {
            method: 'POST',
            body: data
        });
        respuestaraw.ok ? swal.fire('Listo', 'Vale cancelado exitosamente', 'success') :
            swal.fire('Error', 'Hay un problema al cancelar el vale', 'error');
    }
    async enviarVale(folio) {
        const data = new FormData();
        data.append('folio', folio);
        data.append('estado', 3);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?actualizaEstado", {
            method: 'POST',
            body: data
        });
        respuestaraw.ok ? swal.fire('Listo', 'El vale se envio correctamente a Materia Prima', 'success') :
            swal.fire('Error', 'Hay un problema al enviar el vale', 'error');
    }
    async ValidaEnvio(folio) {
        const data = new FormData();
        data.append('folio', folio);
        data.append('estado', 4);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?actualizaEstado", {
            method: 'POST',
            body: data
        });
        respuestaraw.ok ? swal.fire('Listo', 'Se confirmo la recepcion del vale', 'success') :
            swal.fire('Error', 'Hay un problema al cancelar el vale', 'error');
    }
    async CerrarVale(folio) {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "No podrás realizar cambios una vez el vale se cierre!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si, seguro!"
        }).then(async (result) => {
            if (result.isConfirmed) {
                const data = new FormData();
                data.append('folio', folio);
                data.append('estado', 5);
                const respuestaraw = await fetch("../ValesE/php/Vales.php?actualizaEstado", {
                    method: 'POST',
                    body: data
                });
                respuestaraw.ok ? swal.fire('Listo', 'El vale se a cerrado', 'success') :
                    swal.fire('Error', 'Hay un problema al cancelar el vale', 'error');
                document.getElementById('tblValesCreados').innerHTML = '';
                document.getElementById('tblmaterialemodal').innerHTML = '';
            }
        });
    }
    async tblValesCreados(fechai, fechaf, turno, estado, maquina) {
        const data = new FormData();
        data.append('fechai', fechai);
        data.append('fechaf', fechaf);
        data.append('turno', turno);
        data.append('estado', estado);
        data.append('maquina', maquina);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?tblValesCreados", {
            method: 'POST',
            body: data
        });
        const respuesta = await respuestaraw.json();
        let body = '';
        respuesta.forEach(listado => {
            body += `<tr><td>${listado.maquina + ' - ' + listado.foliocons}</td><td>${listado.maquina}</td><td>${listado.noemp}</td><td>${listado.nombreEmp}</td><td>${listado.turno}</td>
            <td>${listado.clave1}</td><td>${listado.clave2}</td><td>${listado.clave3}</td><td>${listado.clave4}</td>
            <td>${listado.estadoid == 1 ? '<i class="fas fa-circle text-info"></i>' : ''}
            ${listado.estadoid == 2 ? '<i class="fas fa-circle text-danger"></i>' : ''}
            ${listado.estadoid == 3 ? '<i class="fas fa-circle text-warning"></i>' : ''}
            ${listado.estadoid == 4 ? '<i class="fas fa-circle text-success"></i>' : ''}
            ${listado.estadoid == 5 ? '<i class="fas fa-circle text-secondary"></i>' : ''} ${listado.estado} </td>
            <td>${listado.fechacreado}</td><td>${listado.fechaenviado}</td>
            <td>${listado.estadoid == 4 ? ('<button class="btn btn-sm btn-warning" onclick="addmm2material(' + listado.id + ')" ><i class="fas fa-edit"></i></button>') : ''}</td>
            <td>${listado.estadoid == 4 ? ('<button class="btn btn-sm btn-danger" onclick="cerrarVale(' + listado.id + ')" ><i class="far fa-paper-plane"></i></button>') : ''}</td>
             </tr>`;
        })
        document.getElementById('tblValesCreados').innerHTML = body;
    }
    async tblValesautoriza(fechai, fechaf, turno, estado, maquina) {
        const data = new FormData();
        data.append('fechai', fechai);
        data.append('fechaf', fechaf);
        data.append('turno', turno);
        data.append('estado', estado);
        data.append('maquina', maquina);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?tblValesCreados", {
            method: 'POST',
            body: data
        });
        const respuesta = await respuestaraw.json();
        let body = '';
        respuesta.forEach(listado => {
            body += `<tr><td>${listado.maquina + ' - ' + listado.foliocons}</td><td>${listado.maquina}</td><td>${listado.noemp}</td><td>${listado.nombreEmp}</td><td>${listado.turno}</td>
            <td>${listado.clave1}</td><td>${listado.clave2}</td><td>${listado.clave3}</td><td>${listado.clave4}</td>
            <td>${listado.estadoid == 1 ? '<i class="fas fa-circle text-info"></i>' : ''}
            ${listado.estadoid == 2 ? '<i class="fas fa-circle text-danger"></i>' : ''}
            ${listado.estadoid == 3 ? '<i class="fas fa-circle text-warning"></i>' : ''}
            ${listado.estadoid == 4 ? '<i class="fas fa-circle text-success"></i>' : ''}
            ${listado.estadoid == 5 ? '<i class="fas fa-circle text-secondary"></i>' : ''} ${listado.estado} </td>
            <td>${listado.fechacreado}</td><td>${listado.fechaenviado}</td>
            <td><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalenv" data-bs-whatever="${listado.id}"><i class="fas fa-stream"></i></button></td>
            <td>${listado.estadoid == 4 ? '<a href="PDF/Formato.php?folio=' + listado.id + '" target="_blank" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i></a>' : ''}</td>
            </tr>`;
        });
        document.getElementById('tblValesCreados').innerHTML = body;
    }
    async ValesConstxid(id) {
        const respuestaraw = await fetch('../ValesE/php/Vales.php?ValesConstxid&id=' + id);
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    async ValidaMatRemplazados(id) {
        const respuestaraw = await fetch('../ValesE/php/Vales.php?ValidaMatRemplazados&id=' + id);
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    limpiar() {
        document.getElementById('noemp').value = '';
        document.getElementById('nombre').value = '';
        document.getElementById('puesto').value = '';
        document.getElementById('clave1').value = '';
        document.getElementById('clave2').value = '';
        document.getElementById('clave3').value = '';
        document.getElementById('clave4').value = '';
        document.getElementById('clase').innerHTML = '';
        document.getElementById('foliovale').value = '';
        document.getElementById('tblmateriales').innerHTML = '';
        document.getElementById('tblmaterialesagregados').innerHTML = '';
    }
    async CancelaMaterial(boton, folio) {
        const data = new FormData();
        data.append('folio', folio);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?CancelaMaterial", {
            method: 'POST',
            body: data
        });
        respuestaraw.ok ? null : null;
        const fila = boton.parentNode.parentNode;
        fila.classList.add('text-danger');
    }
    async saveMM2(folio, mm2) {
        const data = new FormData();
        data.append('folio', folio);
        data.append('mm2', mm2);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?saveMM2", {
            method: 'POST',
            body: data
        });
        respuestaraw.ok ? console.log('exito') : console.log('error');
    }
    async saveEnvases(folio, envases) {
        const data = new FormData();
        data.append('folio', folio);
        data.append('envases', envases);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?saveEnvases", {
            method: 'POST',
            body: data
        });
        respuestaraw.ok ? console.log('exito') : console.log('error');
    }
    async tblValesReporte(fechai, fechaf, turno, estado, maquina) {
        const data = new FormData();
        data.append('fechai', fechai);
        data.append('fechaf', fechaf);
        data.append('turno', turno);
        data.append('estado', estado);
        data.append('maquina', maquina);
        const respuestaraw = await fetch("../ValesE/php/Vales.php?tblValesCreados", {
            method: 'POST',
            body: data
        });
        const respuesta = await respuestaraw.json();
        let body = '';
        respuesta.forEach(listado => {
            // let fecha = listado.fechacreado.replace(/[-:\s]/g,"");
            body += `<tr><td>${listado.maquina + ' - ' + listado.foliocons}</td><td>${listado.maquina}</td><td>${listado.noemp}</td><td>${listado.nombreEmp}</td><td>${listado.turno}</td>
            <td>${listado.clave1}</td><td>${listado.clave2}</td><td>${listado.clave3}</td><td>${listado.clave4}</td>
            <td>${listado.estadoid == 1 ? '<i class="fas fa-circle text-info"></i>' : ''}
            ${listado.estadoid == 2 ? '<i class="fas fa-circle text-danger"></i>' : ''}
            ${listado.estadoid == 3 ? '<i class="fas fa-circle text-warning"></i>' : ''}
            ${listado.estadoid == 4 ? '<i class="fas fa-circle text-success"></i>' : ''}
            ${listado.estadoid == 5 ? '<i class="fas fa-circle text-secondary"></i>' : ''} ${listado.estado} </td>
            <td>${listado.fechacreado}</td><td>${listado.fechaenviado}</td>
            <td><button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalenv" data-bs-whatever="${listado.id}"><i class="fas fa-stream"></i></button></td>
            <td>${listado.estadoid == 4 ? '<a href="PDF/Formato.php?folio=' + listado.id + '" target="_blank" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i></a>' : ''}</td>
            </tr>`;
        });
        document.getElementById('tblValesCreados').innerHTML = body;
    }
}
export class ConfClaves {
    async tblclases(busqueda) {
        const data = new FormData();
        data.append('busqueda', busqueda);
        const respuestaraw = await fetch('../ValesE/php/Vales.php?tblclasesConf', {
            method: 'POST',
            body: data
        });
        const respuesta = await respuestaraw.json();
        let body = '';
        respuesta.forEach(element => {
            body += `<tr><td>${element.id}</td><td>${element.noclase}</td><td>${element.descclase}</td>
            <td><button class="btn btn-sm btn-warning" onclick="editclases(${element.id})"><i class="fas fa-tools"></i></button></td></tr>`;
        })
        return body;
    }
    async tblclaves(busqueda) {
        const data = new FormData();
        data.append('busqueda', busqueda);
        const respuestaraw = await fetch('../ValesE/php/Vales.php?tblclavesConf', {
            method: 'POST',
            body: data
        });
        const respuesta = await respuestaraw.json();
        let body = '';
        respuesta.forEach(element => {
            body += `<tr><td>${element.id}</td><td>${element.noclave}</td><td>${element.descclave}</td>
            <td>${element.xcaja}</td><td>${element.factor}</td><td>${element.clase}</td><td>${element.tipo}</td>
            <td><button class="btn btn-sm btn-warning" onclick="editclaves(${element.id})"><i class="fas fa-tools"></i></button></td></tr>`;
        })
        return body;
    }
    async saveClase(idclase, noclase, nombreclase) {
        const data = new FormData();
        data.append('idclase', idclase);
        data.append('noclase', noclase);
        data.append('nombreclase', nombreclase);
        const respuestaraw = await fetch('../ValesE/php/Vales.php?saveClase', {
            method: 'POST',
            body: data
        })
        respuestaraw.status == 200 && swal.fire('Listo', 'La acción de completo correctamente', 'success');
        respuestaraw.status == 500 && swal.fire('Ups', 'Hay un problema al guardar la clase', 'error');
        respuestaraw.status == 201 && swal.fire('Ups', 'Todos los campos son obligatorios', 'warning');
        respuestaraw.status == 202 && swal.fire('Ups', 'Ya existe la clave', 'warning');
    }
    async saveClave(idclave, noclave, nombreclave, xcaja, factor, claveclase, clavetipo) {
        const data = new FormData();
        data.append('idclave', idclave);
        data.append('noclave', noclave);
        data.append('nombreclave', nombreclave);
        data.append('xcaja', xcaja);
        data.append('factor', factor);
        data.append('claveclase', claveclase);
        data.append('clavetipo', clavetipo);
        const respuestaraw = await fetch('../ValesE/php/Vales.php?saveClave', {
            method: 'POST',
            body: data
        })
        respuestaraw.status == 200 && swal.fire('Listo', 'La acción de completo correctamente', 'success');
        respuestaraw.status == 500 && swal.fire('Ups', 'Hay un problema al guardar la clave', 'error');
        respuestaraw.status == 201 && swal.fire('Ups', 'Todos los campos son obligatorios', 'warning');
        respuestaraw.status == 202 && swal.fire('Ups', 'Ya existe la clave', 'warning');
    }
    async saveMaterial(idmaterial, nomaterial, nombrematerial, ummaterial, ummat, montacargas, costos, tiempo) {
        const data = new FormData();
        data.append('idmaterial', idmaterial);
        data.append('nomaterial', nomaterial);
        data.append('nombrematerial', nombrematerial);
        data.append('ummaterial', ummaterial);
        data.append('ummat', ummat);
        data.append('montacargas', montacargas);
        data.append('costos', costos);
        data.append('tiempo', tiempo);
        const respuestaraw = await fetch('../ValesE/php/Vales.php?saveMaterial', {
            method: 'POST',
            body: data
        })
        respuestaraw.status == 200 && swal.fire('Listo', 'Se guardo correctamente el material', 'success');
        respuestaraw.status == 500 && swal.fire('Ups', 'Hay un problema al guardar el material', 'error');
        respuestaraw.status == 201 && swal.fire('Ups', 'Todos los campos son obligatorios', 'warning');
        respuestaraw.status == 202 && swal.fire('Ups', 'Ya existe el No de Material', 'warning');
    }
    async saveConvinacion(idconvinacion,maquinaconv, claseconv, claveconv, materialconv) {
        const data = new FormData();
        data.append('idconvinacion', idconvinacion);
        data.append('maquinaconv', maquinaconv);
        data.append('claseconv', claseconv);
        data.append('claveconv', claveconv);
        data.append('materialconv', materialconv);
        const respuestaraw = await fetch('../ValesE/php/Vales.php?saveConvinacion', {
            method: 'POST',
            body: data
        })
        respuestaraw.status == 200 && swal.fire('Listo', 'Se guardo correctamente la clave', 'success');
        respuestaraw.status == 500 && swal.fire('Ups', 'Hay un problema al guardar la clave', 'error');
        respuestaraw.status == 201 && swal.fire('Ups', 'Todos los campos son obligatorios', 'warning');
        respuestaraw.status == 202 && swal.fire('Ups', 'Ya existe una convinación igual', 'warning');
    }
    async tblmateriales(busqueda) {
        const data = new FormData();
        data.append('busqueda', busqueda);
        const respuestaraw = await fetch('../ValesE/php/Vales.php?tblmaterialesConf', {
            method: 'POST',
            body: data
        });
        const respuesta = await respuestaraw.json();
        let body = '';
        respuesta.forEach(element => {
            body += `<tr><td>${element.id}</td><td>${element.nomaterial}</td><td>${element.descmaterial}</td><td>${element.ummaterial}</td>
            <td>${element.um}</td><td>${element.montacargas}</td><td>${element.TiempoMat}</td>
            <td><button class="btn btn-sm btn-warning" onclick="editarmaterialbtn(${element.id})"><i class="fas fa-pen"></i></button></td></tr>`;
        })
        return body;
    }
    async cosultarxid(valor, route) {
        const respuestaraw = await fetch('../ValesE/php/Vales.php?' + route + '&id=' + valor);
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    editarClave(id, idclave, noclave, descripcionclave, xcaja, factor, clase, tipo) {
        const modal = new bootstrap.Modal(document.getElementById('modalClaves'));
        modal.show();
        this.cosultarxid(id, 'editclavexid').then((respuesta) => {
            document.getElementById(idclave).value = respuesta[0].id;
            document.getElementById(noclave).value = respuesta[0].noclave;
            document.getElementById(noclave).readOnly = true;
            document.getElementById(descripcionclave).value = respuesta[0].descclave;
            document.getElementById(xcaja).value = respuesta[0].xcaja;
            document.getElementById(factor).value = respuesta[0].factor;
            document.getElementById(clase).value = respuesta[0].clase;
            document.getElementById(tipo).value = respuesta[0].tipo;
        })
    }
    editarClases(id, idclase, noclase, descripcionclase) {
        const modal = new bootstrap.Modal(document.getElementById('modalClases'));
        modal.show();
        this.cosultarxid(id, 'editclasexid').then((respuesta) => {
            document.getElementById(idclase).value = respuesta[0].id;
            document.getElementById(noclase).value = respuesta[0].noclase;
            document.getElementById(noclase).readOnly = true;
            document.getElementById(descripcionclase).value = respuesta[0].descclase;
        })
    }
    editarMaterial(id, idmaterial, nomaterial, nombrematerial, ummaterial
        , ummat, montacargas, tiempo) {
        const modal = new bootstrap.Modal(document.getElementById('modalmaterial'));
        modal.show();
        this.cosultarxid(id, 'editmaterialxid').then((respuesta) => {
            document.getElementById(idmaterial).value = respuesta[0].id;
            document.getElementById(nomaterial).value = respuesta[0].nomaterial;
            document.getElementById(nomaterial).readOnly = true;
            document.getElementById(nombrematerial).value = respuesta[0].descmaterial;
            document.getElementById(ummaterial).value = respuesta[0].ummaterial;
            document.getElementById(ummat).value = respuesta[0].um;
            document.getElementById(montacargas).value = respuesta[0].montacargas;
            document.getElementById(tiempo).value = respuesta[0].TiempoMat;
        })
    }
    editarConvinaciones(id, idcombinacion, idmaquina, idclave, idclase, idmaterial,nameclave,nameclase,namematerial) {
        const modal = new bootstrap.Modal(document.getElementById('modalConvinaciones'));
        modal.show();
        this.cosultarxid(id, 'editconvinacionesxid').then((respuesta) => {
            document.getElementById(idcombinacion).value = respuesta[0].idconv;
            document.getElementById(idmaquina).value = respuesta[0].nomaquina;
            document.getElementById(idclave).value = respuesta[0].noclave;
            document.getElementById(idclase).value = respuesta[0].noclase;
            document.getElementById(idmaterial).value = respuesta[0].nomaterial;
            document.getElementById(nameclave).value = respuesta[0].nomclave;
            document.getElementById(nameclase).value = respuesta[0].nomclase;
            document.getElementById(namematerial).value = respuesta[0].nommaterial;
        })
    }
    async tblconvinaciones(busqueda) {
        const data = new FormData();
        data.append('busqueda', busqueda);
        const respuestaraw = await fetch('../ValesE/php/Vales.php?tblconvinaciones', {
            method: 'POST',
            body: data
        });
        const respuesta = await respuestaraw.json();
        let body = '';
        respuesta.forEach(element => {
            body += `<tr><td>${element.id}</td><td>${element.nomaquina}</td><td>${element.nommaquina}</td><td>${element.noclase}</td>
            <td>${element.nomclave}</td><td>${element.nomaterial}</td><td>${element.nomclase}</td><td>${element.noclave}</td><td>${element.nommaterial}</td>
            <td><button class="btn btn-sm btn-warning" onclick="editconvinacionesxid(${element.id})"><i class="fas fa-tools"></i></button></td></tr>`;
        })
        return body;
    }
    async slcautocomplete(e, autocompleteclaves,idval,ruta,text) {
        const query = e.target.value;
        document.getElementById(idval).value='';
        if (!query) {
            autocompleteclaves.innerHTML = "";
            return;
        }
        const respuestaraw = await fetch(ruta+'&q=' + query)
        const respuesta = await respuestaraw.json();
        autocompleteclaves.innerHTML = "";
        respuesta.forEach(item => {
            const div = document.createElement("div");
            div.textContent = item.text;
            div.addEventListener("click", function () {
                document.getElementById(text).value = item.text;
                document.getElementById(idval).value = item.id
                autocompleteclaves.innerHTML = "";
            });
            autocompleteclaves.appendChild(div);
        });
    }
}