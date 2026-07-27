<?php
require_once("../Session/seguridad.php");
// if ($_SESSION["adminReportesProduccion"] != 1) {
//     header('Location: ../index/index');
// }
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-4">
    <h5 class="tittlecont">REPORTE POR DEPARTAMENTO</h5>
    <!-- Seleccionador de fechas -->
    <div class="row">
        <div class="col-2">
            <small>FECHA INICIO</small>
            <input type="date" class="form-control form-control-sm" id="fechaInicio" name="fechaInicio" />
        </div>
        <div class="col-2">
            <small>FECHA FIN</small>
            <input type="date" class="form-control form-control-sm" id="fechaFin" name="fechaFin" />
        </div>
        <div class="col-2">
            <small>DEPARTAMENTO</small>
            <select class="form-control form-control-sm" id="departamentos" name="departamentos"></select>
        </div>
        <div class="col-1">
            <br>
            <button class="form-control form-control-sm btn btn-sm btn-primary" id="generarTabla"
                name="btnGenerarTabla"><i class="fa-solid fa-magnifying-glass"></i> Ver tabla</button>
        </div>
        <div class="col-1">
            <br>
            <button class="form-control form-control-sm btn btn-sm btn-danger" id="btnGenerarReportePDF"
                name="btnGenerarReportePDF" hidden><i class="fas fa-file-pdf"></i> Generar reporte</button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
        <!-- Controles de paginación y buscador -->
        <div class="d-flex justify-content-between align-items-center mt-3 pagination-controls">
            <div>
                <label class="mb-0">
                    Mostrar:
                    <select id="pageSize" class="form-select form-select-sm d-inline-block" style="width:80px;">
                        <option value="10">10</option>
                        <option value="15" selected>15</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    registros
                </label>
            </div>
            <!-- Buscador -->
            <div class="me-2" style="flex:1; max-width:420px;" hidden>
                <input type="text" id="searchInput" class="form-control form-control-sm"
                    placeholder="Buscar por máquina..." />
            </div>
        </div>
        <br>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>FECHA</th>
                    <th>MÁQUINA</th>
                    <th>PIEZAS</th>
                    <th>TOTAL USTD</th>
                    <th>TOTAL REALES</th>
                </tr>
            </thead>
            <tbody id="tbodyReporteDepartamentos"></tbody>
        </table>
        <br>
        <!-- Controles para pasar a la siguiente pagina -->
        <div class="d-flex justify-content-end">
            <button id="prevPage" class="btn btn-dark btn-sm">Anterior</button>
            <span id="pageInfo" class="mx-2 my-auto"></span>
            <button id="nextPage" class="btn btn-dark btn-sm">Siguiente</button>
        </div>
    </div>


</div>
<?php require_once("../index/footer.php") ?>
<script src="js/ReporteDepartamentos.js" type="module"></script>