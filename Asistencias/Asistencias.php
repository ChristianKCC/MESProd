<?php
require_once("../Session/seguridad.php");
if($_SESSION["permisoPersonal"]!=1){
  header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<link rel="stylesheet" href="css/estilos.css">

<div class="container p-4">
  <div id="loader">
    <div class="loader-dots">
      <div></div>
      <div></div>
      <div></div>
    </div>
  </div>

  <h5 class="tittlecont">Asistencias</h5>
  <form method="POST" id="pdfForm" action="PDF/procesar.php" target="_blank">
  
    <div class="row">
      <div class="col">
        <small>Fecha inicio</small>
        <input type="date" class="form-control form-control-sm" id="fechai" name="fechai" required>
      </div>
      <div class="col">
        <small>Fecha final</small>
        <input type="date" class="form-control form-control-sm" id="fechaf" name="fechaf" required>
      </div>
      <div class="col">
        <small>No. Empleado</small>
        <input type="number" class="form-control form-control-sm" id="empno" name="empno">
      </div>
      <div class="col">
        <small>Centro de costos</small>
        <select class="form-control form-control-sm" id="ctrocstos" name="ctrocstos"></select>
      </div>
      <div class="col">
        <small>Departamento</small>
        <select class="form-control form-control-sm" id="departamento" name="departamento"></select>
      </div>
      <div class="col">
        <small>Tipo de empleados</small>
          <select class="form-control form-control-sm" id="tipemp" name="tipemp">
            <option value="">Todos los empleados</option>
            <option value="0">Sindicalizado</option>
            <option value="1">Empleado</option>
          </select>
      </div>

      <div class="col-1">
        <small><br></small>
          <button class="form-control btn btn-sm btn-primary" id="consultar">
              <i class="fa-solid fa-magnifying-glass"></i> Buscar
          </button>
      </div>
      <div class="col-1">
        <small><br></small>
        <button type="reset" class=" form-control btn btn-sm btn-danger" id="reiniciar">
          <i class="fa-solid fa-rotate-left"></i> Reiniciar
        </button>
      </div>
    </div>

    <br>
    <button type="submit" name="accion" value="crear_pdf" class="btn btn-sm btn-danger">
      <i class="fa fa-file-pdf"></i>  Generar PDF
    </button>
    <button type='submit' name="accion" value="crear_excel" class="btn btn-sm btn-success">
      <i class="fas fa-file-excel"></i> Generar Excel
    </button>
    <br>
    <small class="text-primary"> 
      Las tarjetas se crearán por semana de acuerdo con la fecha de inicio seleccionada (6 dias despues de la fecha inicial).
    </small>
    <br>
    <small class="text-success"> 
      Tanto los archivos 'PDF' como 'Excel' se generaran segun los filtros de busqueda aplicados (Fechas de inicio y fin / No. empleado / Centro de costos / Departamento o Tipo de empleado)
    </small>
    <br><br>
  </form>

  <div id="content">
    <div class="table-responsive" style="height: 600px;">
      <table class="table table-bordered table-striped" style="text-align: center;">
        <thead class="table-dark">
          <th>No</th>
          <th>NoEmp</th>
          <th>Nombre</th>
          <th>Fecha</th>
          <th>Temperatura</th>
          <th>Ubicación</th>
        </thead>
        <tbody id="consultaacceso">
        </tbody>
      </table>
    </div>
  </div>
  
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Asistencias.js"></script>