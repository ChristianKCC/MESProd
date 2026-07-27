<?php
require_once("../Session/seguridad.php");
if($_SESSION["permisoProact"]!=1){
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Interacciones LSW</h5>
    <br />
    <div style="float:left" class="row">
        <div class="col-20">    
            <small class="alert alert-info">
             <svg 
                xmlns="http://www.w3.org/2000/svg" 
                width="24" 
                height="24" 
                fill="currentColor" 
                class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" 
                viewBox="0 0 16 16" 
                role="img" 
                aria-label="Warning:">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
                Desde esta seccion consulta un resumen general de las interacciones LSW
            </small>
        </div>
    </div>
    <br />
    <br />
    <br />    

    <form id="reporteLSW">
        <div class="row">
            <div class="col">
                <small>Fecha Inicial</small>
                    <input 
                        type="date" 
                        class="form-control form-control-sm" 
                        name="fechai" 
                        id="fechai">
            </div>
            <div class="col">
                <small>Fecha Final</small>
                    <input 
                    type="date" 
                    class="form-control form-control-sm" 
                    name="fechaf" 
                    id="fechaf">
            </div>

            <div class="col">
                <small>IBM Observado</small>
                <input 
                    type="number" 
                    class="form-control form-control-sm" 
                    name="observado" 
                    id="observado">
            </div>
        
            <div class="col">
                <small>Area</small>
                <select 
                    name="areas" 
                    id="areas" 
                    class="form-control form-control-sm">
                </select>
            </div>

            <div class="col">
                <small>Maquina</small>
                <select 
                    name="maquinas[]" 
                    id="maquinas" 
                    class="form-control form-control-sm">
                </select>
            </div>
            
            <div class="col">
                <br />
                <button class="bg-target btn btn-sm" id="consulta">
                    <i class="fa-solid fa-database"></i> Consultar información
                </button>
            </div>

            <div class="col">     
                <br />           
                <a href="#" class="btn btn-sm btn-success" onclick="proact.exportartablaexcel('tblobserb')">
                    <i class="fa-solid fa-file-excel"></i> 
                    Exportar a Excel
                </a>
            </div>
        </div>
    </form>

    <div id="resultado"></div>
    <hr>
    <div class="table-responsive" style="height:300px">
        <table class="table table-sm" id="tblobserb">
            <thead class="table-dark">
                <th>ID</th>
                <th>Observador</th>
                <th>TObservacion</th>
                <th>Observado</th>
                <th>Observacion</th>
                <th>Cumplio</th>
                <th>Area</th>
                <th>Maquina</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Comentarios</th>
                <th>Otra</th>
                <th>Critico</th>
            </thead>
            <tbody id="table">

            </tbody>
        </table>
    </div>
    
    <br />
    <div class="row">
        <div class="col-6 text-center">
            <h5 class="text-center">TOTAL DE OBSERVACIONES POR MAQUINA</h5><canvas id="myChart" height="300"></canvas>
        </div>
        <div class="col-6 text-center">
            <h5 class="text-center">TOTAL DE PERSONAS OBSERVADAS POR MAQUINA</h5><canvas id="myChart3" height="200"></canvas>
        </div>
    </div>

    <br /> <br />
    <div class="row justify-content-center">
        <div class="col-6">
            <h5 class="text-center">OBSERVACIONES CON LAS QUE EL TRABAJADOR NO CUMPLE</h5><canvas id="myChart2" height="200"></canvas>
        </div>
        <div class="col-6">
            <h5 class="text-center">SE ESTÁ IMCUMPLIENDO ALGUNA REGLA CRÍTICA</h5><canvas id="myChart5" height="200"></canvas>
        </div>
    </div>

    <br /> <br />
    <div class="row">
        <div class="col-6">
            <h5 class="text-center">TOP PERSONAS OBSERVADAS</h5><canvas id="myChart4" height="200"></canvas>
        </div>
    </div>

</div>

<!-- Modal detalle persona -->
<div class="modal fade" id="modalDetallePersona" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl"> <!-- más ancho para ver mejor la tabla -->
    <div class="modal-content">
      <div class="modal-header text-dark">
        <h5 class="modal-title">
          <i class="fas fa-user"></i> Detalles de persona observada
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Encabezado fijo -->
        <div class="mb-3 p-2 bg-primary text-white border rounded text-center" style="width:100%;">
            <h6 id="detalleTitulo" class="m-0 fw-bold "></h6>
        </div>

        <!-- Tabla -->
        <div class="table-responsive" style="max-height:500px; overflow-y:auto;">
            <table class="table table-hover table-striped table-bordered align-middle">
            <thead class="table-dark text-center">
                <tr>
                <th>ID</th>
                <th>Observador</th>
                <th>Departamento</th>
                <th>Máquina</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Observación</th>
                <th>Interaccion</th>
                <th>Comentarios</th>
                </tr>
            </thead>
            <tbody id="detallePersonaBody">
                <!-- Aquí se cargará la info -->
            </tbody>
            </table>
        </div>
        </div>

      <div class="modal-footer">        
        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-xmark"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>


<?php require_once("../index/footer.php") ?>
<script type="text/javascript" src="../poojs/herramientas.js"></script>
<script type="text/javascript" src="js/index.js"></script>
<!-- <script type="text/javascript" src="../poojs/index.js"></script> -->
 <!-- Uso de clase de reporte -->
<script type="text/javascript">
    proact = new Proact();
    proact.reporte();
</script>