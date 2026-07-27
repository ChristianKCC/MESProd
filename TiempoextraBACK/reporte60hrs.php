<?php
require_once("../Session/seguridad.php");
if($_SESSION["permisoPersonal"]!=1){
  header('Location: ../index/index');
}
require_once("../index/header.php");
?>

<div class="container p-4">
  <div id="loader">
    <div class="loader-dots">
      <div></div>
      <div></div>
      <div></div>
    </div>
  </div>

  <h5 class="tittlecont">Reporte 60.5hrs y Dobletes</h5>

    <div style="float-left" class="row">
    <div class="col-20">
        <small class="alert alert-info" style= "float:left">
                <svg 
                    xmlns="http://www.w3.org/2000/svg" 
                    width="16" 
                    height="16" 
                    fill="currentColor" 
                    class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" 
                    viewBox="0 0 16 16" 
                    role="img" 
                    aria-label="Warning:">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                Desde esta vista consulta el reporte de 60.5 hrs en tiempos extras y dobletes.
        </small>
        </div>
    </div>
    
  <form method="POST" id="pdfForm" action="PDF/reportePDF60hrs" target="_blank">
  
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
        <small>Departamento</small>
        <select class="form-control form-control-sm" id="departamento" name="departamento"></select>
      </div>
      <div class="col">
        <small>Turno</small>
          <select class="form-control form-control-sm" id="turno" name="turno">
            <option value="">Selecciona turno</option>
            <option value="turno1">Turno 1</option>
            <option value="turno2">Turno 2</option>
            <option value="turno3">Turno 3</option>
            <option value="turno3_12hrs">Turno 3 (12 hrs)</option>            
            <option value="mixto1">Mixto 1</option>
            <option value="mixto2">Mixto 2</option>
            <option value="mixto3">Mixto 3</option>            
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

    <br />

    <small class="text-primary"> 
        Los registros obtenidos son aquellos que sus horas reglamentarias mas sus horas extras son iguales o mayores a 60 hrs.
    </small>
    <br>
    <small class="text-success"> 
        Tanto los archivos 'PDF' como 'Excel' se generaran segun los filtros de busqueda aplicados (Fechas de inicio y fin / No. empleado / Departamento / Turno)
    </small>
    <br />

    <button type="submit" name="accion" id="crearPdf" class="btn btn-sm btn-danger">
      <i class="fa fa-file-pdf"></i>  Generar PDF
    </button>
    <button type='submit' name="accion" id="exportexcel" class="btn btn-sm btn-success">        
      <i class="fas fa-file-excel"></i> Generar Excel
    </button>

    <br><br>
  </form>

  <div id="content">
    <div class="table-responsive">
      <table class="table table-bordered table-striped" style="text-align: center;" id="tablaReporteExc">
        <thead class="table-dark">
          <th>Id</th>
          <th>Folio de Turno Extra</th>
          <th>NoEmp</th>
          <th>Departamento</th>
          <th>Nombre</th>
          <th>Fecha</th>
          <th>Inicio T. extra</th>
          <th>Fin T. extra</th>
          <th>Turno</th>
          <th>Hrs. Regl. x turno</th>
          <th>Hrs. Ext. Semanal en folio</th>
          <th>Hrs. Ext. individuales en folio</th>
          <th>Horas totales</th>
          <th>Tipo de registro</th>
        </thead>
        <tbody id="tablaReporte">
        </tbody>
      </table>
    </div>
  </div>
  
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/reporte60hrs.js"></script>