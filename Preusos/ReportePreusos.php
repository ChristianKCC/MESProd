 <?php
    require_once("../Session/seguridad.php");
    require_once("../index/header.php");
    ?>
 <div class="container p-2">
     <h5 class="tittlecont">Reporte de Pre-Usos</h5>
    <div class="row m-2">
        <div class="col"> <br><input type="date" id="fechai" name="fechai" class="form-control form-control-sm"> </div>
        <div class="col"> <br><input type="date" id="fechaf" name="fechaf" class="form-control form-control-sm"> </div>
        <div class="col"> <small>Departamento</small><select id="departamento" class="form-control form-control-sm"></select></div>
        <div class="col"> <small>Maquina</small><select id="maquina" class="form-control form-control-sm"></select></div>
        <div class="col"> <br><button id="buscarPreusos" class="btn btn-sm bg-target"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button> </div>
    </div>
     <div class="table-responsive m-2" style="height: 700px;">
         <table class="table table-bordered">
             <thead class="table-dark">
                 <th>ID</th>
                 <th>Tipo</th>
                 <th>Seccion</th>
                 <th>NoEmp</th>
                 <th>Nombre</th>
                 <th>Turno</th>
                 <th>Maquina</th>
                 <th>Comentarios</th>
                 <th>Fecha</th>
                 <th></th>
             </thead>
             <tbody id="tblReportePreusos">
             </tbody>
         </table>
     </div>
 </div>
 <?php require_once("../index/footer.php") ?>
 <script type="module" src="./js/reportepreusos.js"></script>