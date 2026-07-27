<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
  <div class="row">
    <div class="col">
      <h5 class="fw-bold">Reporte de asistencias por curso</h5>
    </div>
    <form id="formresert" class="p-2">
      <div class="row"><small>Curso</small>
        <div class="col-6"><select class="form-control form-control-sm" id="slccurso"></select></div>
        <div class="col-2"><input type="date" class="form-control form-control-sm" id="fechai"><small>Del:</small></div>
        <div class="col-2"><input type="date" class="form-control form-control-sm" id="fechaf"><small>Al:</small></div>
        <div class="col-1"><button class="form-control form-control-sm bg-target btn" id="consultarxcurso">Aceptar</button></div>
        <div class="col-1"><button type="reset" class="form-control form-control-sm btn btn-danger">Limpiar</button></div>
      </div>
    </form>
  </div>
  <div class="table-responsive" style="height:600px">
    <table class="table table-hover table-sm" id='tblCursostomados'>
      <thead class="table-dark">
        <th>No.</th>
        <th>IBM</th>
        <th>Nombre</th>
        <th>Curso</th>
        <th>Área</th>
        <th>Puesto</th>
        <th>Cap.</th>
        <th>Calificación</th>
        <th>Duración</th>
        <th>Fecha</th>
        <th>Inst.</th>
        <th></th>
      </thead>
      <tbody id="tblreporte">
      </tbody>
    </table>
  </div>
  <a href="pdf/crearpdfcardexcurso.php?query=<?php echo $query; ?>&idcurso=<?php echo $curso; ?>&fechai=<?php echo $fechai; ?>&fechaf=<?php echo $fechaf; ?>" target="_blank" class="btn btn-danger mb-2">Generar pdf</a>
  <a style="float: right;" href="#" id="crearexcel">Crear excel</a>
  <div class="modal fade" id="examenModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Exámen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="examenes"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script>
  (async function slccurso() {
    const respuestaraw = await fetch(
      "../Components/CatalogoCursos.php?GetSlcCursos"
    );
    const respuesta = await respuestaraw.json();
    let body = "<option value=''>Selecciona una opción</option>";
    respuesta.forEach((element) => {
      body += `<option value='${element.id}'>${element.nombre}</option>`;
    });
    document.getElementById("slccurso").innerHTML = body;
  })();
  async function tblReporte(curso, fechai, fechaf) {
    const data = new FormData();
    data.append('curso', curso);
    data.append('fechai', fechai);
    data.append('fechaf', fechaf);
    const respuestaraw = await fetch('php2/Reportes.php?getDataReportxCurso', {
      method: 'POST',
      body: data
    });
    const respuesta = await respuestaraw.json();
    let body = ''
    respuesta.forEach(element => {
      body += `<tr id='${element.id}'><td>${element.cont}</td><td>${element.noemp}</td><td>${element.nombre}</td><td>${element.nombrecurso}</td><td>${element.depto}</td>
      <td>${element.puesto}</td><td>${element.idcap}</td><td>${element.calificacion}</td><td>${element.duracion}</td>
      <td>${element.fecha}</td><td>${element.instructor}</td>
      <td><button type="button" class="btn btn-sm btn-primary" onclick='abrirexamen(${element.id})'>Exámen</button></td></tr>`;
    })
    document.getElementById('tblreporte').innerHTML = body;
  }
  document.getElementById('consultarxcurso').addEventListener('click', async (e) => {
    e.preventDefault();
    const curso = document.getElementById('slccurso').value
    const fechai = document.getElementById('fechai').value
    const fechaf = document.getElementById('fechaf').value
    if (fechai == "" || fechaf == "") {
      alert("El rango de fechas es obligatorio");
      return false;
    }
    tblReporte(curso, fechai, fechaf);
  })

  async function abrirmodalexamen(id) {
    const modal = new bootstrap.Modal(document.getElementById('examenModal'));
    modal.show();
    const data = new FormData();
    data.append('id', id);
    const respuestaraw = await fetch('php2/Reportes.php?getDataExament', {
      method: 'POST',
      body: data
    });
    const respuesta = await respuestaraw.json();
    let body = ''
    let res1, res2, color = '';

    respuesta.forEach(element => {

      const respuestas = {
        1: element.r1,
        2: element.r2,
        3: element.r3
      };
      res1 = respuestas[element.correcta] || null;

      const respuestas2 = {
        1: element.r1,
        2: element.r2,
        3: element.r3
      };
      res2 = respuestas2[element.respuesta] || null;
      body += `<h5>${element.pregunta}</h5>
      <h6>R1. ${element.r1}</h6><h6>R2. ${element.r2}</h6><h6>R3. ${element.r3}</h6><h6 class='text-info'>RC. ${res1}</h6><h6 class='${res2 != res1 ? 'text-danger' : 'text-success'}'>RE. ${res2}</h6>`
    })
    document.getElementById('examenes').innerHTML = body;

  }
  window.abrirexamen = function(data) {
    abrirmodalexamen(data)
  }
</script>
<script type="module">
  import {Toolsjs} from "../Tools/Tools.js";
  const tool = new Toolsjs();
  document.getElementById('crearexcel').addEventListener('click', (e) => {
    e.preventDefault();
    tool.exportartablaexcel('tblCursostomados');
  })
</script>