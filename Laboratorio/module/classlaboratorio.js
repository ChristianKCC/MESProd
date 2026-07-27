import { Toolsjs } from "../../Tools/Tools.js";

export class LabFolio {
    constructor(fecha, turno, monitor, sd, ql, muestras, departamento,
        maquina, conductor, supervisor) {
        this.fecha = fecha;
        this.turno = turno;
        this.monitor = monitor;
        this.sd = sd;
        this.ql = ql;
        this.muestras = muestras;
        this.departamento = departamento;
        this.maquina = maquina;
        this.conductor = conductor;
        this.supervisor = supervisor;
    }
    async guardarencabezado() {
        const data = new FormData();
        data.append('fecha', this.fecha.value);
        data.append('turno', this.turno.value);
        data.append('monitor', this.monitor.value);
        data.append('sd', this.sd.value);
        data.append('ql', this.ql.value);
        data.append('muestras', this.muestras.value);
        data.append('departamento', this.departamento.value);
        data.append('maquina', this.maquina.value);
        data.append('conductor', this.conductor.value);
        data.append('supervisor', this.supervisor.value);
        const respuestaraw = await fetch('../Laboratorio/php/laboratorio.php?guardarencabezado', {
            method: 'POST',
            body: data
        })
        const respuesta = await respuestaraw.json();
        respuestaraw.status === 200 && swal.fire('Listo!!!', respuesta, 'success');
        respuestaraw.status === 500 && swal.fire('ERROR!!!', respuesta, 'error');
    }
    async tblLaboratorioReporte(fechai,fechaf){
        const data = new FormData();
        data.append('fechai',fechai);
        data.append('fechaf',fechaf);
        const respuestaraw = await fetch('../Laboratorio/php/laboratorio.php?tblLaboratorioReporte',{
            method: 'POST',
            body: data
        })
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    async tblLaboratorioEnc() {
        const respuestaraw = await fetch('../Laboratorio/php/laboratorio.php?tblLaboratorioEnc')
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
    async tblLaboratorioEncxid(id) {
        const respuestaraw = await fetch('../Laboratorio/php/laboratorio.php?tblLaboratorioEncxid&id=' + id);
        const respuesta = await respuestaraw.json();
        return respuesta;
    }
}
export class LaFolioSub {
    constructor(folio, clave, retenido, merma, recuperado,
        defecto, componente, numeroaparto, numerolibero, hora, numerooperador, estatus,
        comentario, seccion, idencabezado) {
        this.folio = folio;
        this.clave = clave;
        this.retenido = retenido;
        this.merma = merma;
        this.recuperado = recuperado;
        this.defecto = defecto;
        this.componente = componente;
        this.numeroaparto = numeroaparto;
        this.numerolibero = numerolibero;
        this.hora = hora;
        this.numerooperador = numerooperador;
        this.estatus = estatus;
        this.comentario = comentario;
        this.seccion = seccion;
        this.idencabezado = idencabezado;
    }
    async guardarsubencabezado() {
        const data = new FormData();
        data.append('folio', this.folio.value);
        data.append('clave', this.clave.value);
        data.append('retenido', this.retenido.value);
        data.append('merma', this.merma.value);
        data.append('recuperado', this.recuperado.value);
        data.append('defecto', this.defecto.value);
        data.append('componente', this.componente.value);
        data.append('numeroaparto', this.numeroaparto.value);
        data.append('numerolibero', this.numerolibero.value);
        data.append('hora', this.hora.value);
        data.append('numerooperador', this.numerooperador.value);
        data.append('estatus', this.estatus.value);
        data.append('comentario', this.comentario.value);
        data.append('seccion', this.seccion.value);
        data.append('idencabezado', this.idencabezado.value);
        const respuestaraw = await fetch('../Laboratorio/php/laboratorio.php?guardarsubencabezado', {
            method: 'POST',
            body: data
        })
        const respuesta = await respuestaraw.json();
        respuestaraw.status === 200 && swal.fire('Listo!!!', respuesta, 'success');
        respuestaraw.status === 500 && swal.fire('ERROR!!!', respuesta, 'error');
    }
    async tblLaboratorioSubEnc(id) {
        const respuestaraw = await fetch('../Laboratorio/php/laboratorio.php?tblLaboratorioSubEnc&id=' + id);
        const respuesta = await respuestaraw.json();
        return respuesta;
    }

}