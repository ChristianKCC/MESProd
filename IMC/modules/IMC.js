import { Toolsjs } from "../../Tools/Tools.js";

export class IMC {
  constructor(
    fecha,
    emisor,
    departamento,
    area,
    detriesgo,
    tiporiesgo,
    tipo,
    descripcion,
    responsable,
    sugerencias,
    fechacompromiso,
    estado,
    condicion
  ) {
    this.fecha = fecha;
    this.emisor = emisor;
    this.departamento = departamento;
    this.area = area;
    this.detriesgo = detriesgo;
    this.tiporiesgo = tiporiesgo;
    this.tipo = tipo;
    this.descripcion = descripcion;
    this.responsable = responsable;
    this.sugerencias = sugerencias;
    this.fechacompromiso = fechacompromiso;
    this.estado = estado;
    this.condicion = condicion;
  }
  validacion() {
    if (
      this.fecha == "" ||
      this.emisor == "" ||
      this.departamento == "" ||
      this.area == "" ||
      this.detriesgo == "" ||
      this.tiporiesgo == "" ||
      this.tipo == "" ||
      this.descripcion == "" ||
      this.responsable == "" ||
      this.sugerencias == "" ||
      this.fechacompromiso == "" ||
      this.estado == ""
    ) {
      return false;
    }
  }
  async getinfoemp(noemp, domnom, domdep, dompue) {
    const respuetaraw = await fetch(
      "../Components/CatalogoSeguridad.php?datosemp&noemp=" + noemp
    );
    const respuesta = await respuetaraw.json();
    if (respuesta.length === 0) {
      domnom != "" && (document.getElementById(domnom).value = "");
      domdep != "" && (document.getElementById(domdep).value = "");
      dompue != "" && (document.getElementById(dompue).value = "");
      return false;
    }
    domnom != "" &&
      (document.getElementById(domnom).value = respuesta[0].nombre);
    domdep != "" &&
      (document.getElementById(domdep).value = respuesta[0].departamento);
    dompue != "" &&
      (document.getElementById(dompue).value = respuesta[0].puesto);
  }
  async saveIMC() {
    const data = new FormData();
    data.append("fecha", this.fecha);
    data.append("emisor", this.emisor);
    data.append("departamento", this.departamento);
    data.append("area", this.area);
    data.append("detriesgo", this.detriesgo);
    data.append("tiporiesgo", this.tiporiesgo);
    data.append("tipo", this.tipo);
    data.append("descripcion", this.descripcion);
    data.append("responsable", this.responsable);
    data.append("sugerencias", this.sugerencias);
    data.append("fechacompromiso", this.fechacompromiso);
    data.append("estado", this.estado);
    data.append("condicion", this.condicion);
    const respuestaraw = await fetch("../IMC/php/imc.php?saveIMC", {
      method: "POST",
      body: data,
    });
    respuestaraw.status === 200 &&
      swal.fire("Listo!!!", "Se guardo correctamente el registro", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR!!!",
        "hay problemas para guardar la información",
        "error"
      );
  }

  async tblIMCEnc() {
    const respuestaraw = await fetch("../IMC/php/imc.php?tblIMCEnc");
    const respuesta = await respuestaraw.json();
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr><td>${element.id}</td><td>${element.fecha}</td><td>${element.Nombre}</td><td>${element.NombreDepto}</td><td>${element.NombreArea}</td>
        <td>${element.descripcion}</td><td>${element.fechacompromiso}</td></tr>`;
    });
    document.getElementById("tblimc").innerHTML = body;
  }
  async tblReporteIMC(fechai, fechaf, departamento, area,noemp,estadoimc) {
    const data = new FormData();
    data.append('fechai', fechai);
    data.append('fechaf', fechaf);
    data.append('departamento', departamento);
    data.append('area', area);
    data.append('noemp', noemp);
    data.append('estadoimc', estadoimc);
    const respuestaraw = await fetch("../IMC/php/imc.php?tblReporteIMC", {
      method: 'POST',
      body: data
    });
    const respuesta = await respuestaraw.json();
    console.log(respuesta);
    let body = "";
    respuesta.forEach((element) => {
      body += `<tr><td>${element.imc}</td><td>${element.creado}</td><td>${element.emisor}</td><td>${element.departamento}</td><td>${element.area}</td>
      <td>${element.deteccion}</td><td>${element.riesgo}</td><td>${element.tipo}</td><td>${element.responsable}</td>
      <td>${element.fechacompromiso}</td><td>${element.estatus}</td><td>${element.descripcion}</td><td>${element.sugerencias}</td>
      <td><button class='btn btn-sm btn-warning' data-bs-toggle="modal" data-bs-target="#modalRepimc" data-bs-whatever="${element.imc}"><i class="fas fa-stream"></i></button></td>
      <td><a href='./PDF/Anexo.php?folio=${element.imc}' target='_blank' class='btn btn-sm btn-danger'> <i class="fas fa-file-pdf"></i> </a></td></tr>`;
    });
    document.getElementById("tblReporteIMC").innerHTML = body;
  }
  async updateEstadoIMC(id, emisor, departamento, area, detriesgo, tiporiesgo, tipo, descripcion, responsable, sugerencias,
    fechacompromiso,estado,) {
    const data = new FormData();
    data.append("id", id);
    data.append("emisor", emisor);
    data.append("departamento", departamento);
    data.append("area", area);
    data.append("detriesgo", detriesgo);
    data.append("tiporiesgo", tiporiesgo);
    data.append("tipo", tipo);
    data.append("descripcion", descripcion);
    data.append("responsable", responsable);
    data.append("sugerencias", sugerencias);
    data.append("fechacompromiso", fechacompromiso);
    data.append("estado", estado);
    const respuestaraw = await fetch("../IMC/php/imc.php?updateEstadoIMC", {
      method: "POST",
      body: data,
    });
    respuestaraw.status === 200 &&
      swal.fire("Listo!!!", "Se actualizo la información correctamente", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR!!!",
        "hay problemas para guardar la información",
        "error"
      );
    respuestaraw.status === 502 &&
      swal.fire(
        "Lo siento!!!",
        "No tienes permisos para realizar este cambio",
        "error"
      );
  }
  async getDataIMCxid(id){
    const respuestaraw = await fetch("../IMC/php/imc.php?tblIMCEncxid&id="+id);
    const respuesta = await respuestaraw.json();
    const tools = new Toolsjs();
    console.log(respuesta);
    document.getElementById('noempemisor').value = respuesta[0].emisor;
    document.getElementById('nombreemisor').value = respuesta[0].emisornombre;
    document.getElementById('departamento').value = respuesta[0].departamento;
    document.getElementById('depemisor').value = respuesta[0].NombreDepartamento;
    document.getElementById('detriesgo').value = respuesta[0].detriesgo;
    document.getElementById('tiporiesgo').value = respuesta[0].tiporiesgo;
    document.getElementById('tipo').value = respuesta[0].tipo;
    document.getElementById('descripcion').value = respuesta[0].descripcion;
    document.getElementById('responsable').value = respuesta[0].responsable;
    document.getElementById('responsablenombre').value = respuesta[0].responsablenombre;
    document.getElementById('sugerencias').value = respuesta[0].sugerencias;
    document.getElementById('fechacompromiso').value = respuesta[0].fechacompromiso;
    document.getElementById('estado').value = respuesta[0].estado;
    tools.llnarslc("CatalogoSeguridad", "GetSlcAreas&dep=" + respuesta[0].departamento, "area", 0).then(()=> 
    document.getElementById('area').value = respuesta[0].area);
  }
}
