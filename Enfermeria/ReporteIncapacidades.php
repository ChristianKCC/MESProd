<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Incapacidades Enfermeria</h5>
    <form id="formConsultas">
        <div class="row text-center">
            <div class="col-2"><small>Fecha Inicial</small><input type="date" id="fechai" name="fechai" class="form-control form-control-sm" /></div>
            <div class="col-2"><small>Fecha Final</small><input type="date" id="fechaf" name="fechaf" class="form-control form-control-sm" /></div>
            <div class="col-2"><small>Departamento</small><select class="form-control form-control-sm" id="departamento" name="departamento"></select></div>
            <div class="col-2"><small>Maquina</small><select class="form-control form-control-sm" id="maquina" name="maquina"></select></div>
            <div class="col-2"><small>NoEmp</small><input type="number" id="noemp" name="noemp" class="form-control form-control-sm" /></div>
            <div class="col-1"><br><button class="btn btn-sm bg-target" id="getReporteIncapacidad"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button> </div>
            <div class="col-1"><br><button type="reset" class="btn btn-sm btn-secondary" id="limpiaIncapacidad"><i class="fas fa-undo-alt"></i> Limpiar</button></div>
        </div>

    </form>
    <div class="row my-4">
        <div class="col-12 table-responsive" style="height: 300px;">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <th>ID</th>
                    <th>Noemp</th>
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Puesto</th>
                    <th>Responsable</th>
                    <th>Nombre</th>
                    <th>Folio</th>
                    <th>Tipo</th>
                    <th>Frec.</th>
                    <th>Revision</th>
                    <th>Dias</th>
                    <th>FechaInicio</th>
                    <th>ST1</th>
                    <th>STPS</th>
                    <th>FechaEntrega</th>
                    <th>DX</th>
                    <th>Firma</th>
                </thead>
                <tbody id="tblreporteIncapacidades">
                </tbody>
            </table>
        </div>



        <!-- <div class="col-8">
            <button id="mostrarEquipos" class="btn btn-sm bg-target" hidden>Volver a equipos médicos</button>
            <canvas id="chart" height="100"></canvas>
        </div>

        <div class="col-3">
            <canvas id="graficaDepartamentos" height="40"></canvas>
        </div> -->

    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/reporteIncapacidad.js"></script>