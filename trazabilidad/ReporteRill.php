<?php require_once("../index/header.php"); ?>
<div class="container rounded shadow">
    <h4 class="tittlecont">Reporte Rill</h4>
    <form>
        <div class="row mb-2">
            <div class="col"><small>Maquina</small><select class="form-control form-control-sm" id="maquina"></select></div>
            <div class="col"><small>Fecha Inicio</small><input type="date" id="fechai" class="form-control form-control-sm"></div>
            <div class="col"><small>Fecha Final</small><input type="date" id="fechaf" class="form-control form-control-sm"></div>
            <div class="col-1"><br /><button id="filtro" class="form-control btn btn-sm bg-target"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button></div>
            <div class="col-1"><br /><button type="reset" class="form-control btn btn-sm btn-danger"><i class="fa-solid fa-rotate-left"></i> Limpiar</button></div>
        </div>
    </form>
    <div class="table-responsive" style="height: 620px;">
        <table class="table table-bordered" id="tblrillidexce">
            <thead class="table-dark">
                <th>id</th>
                <th>Clave</th>
                <th>Modulo</th>
                <th>Material</th>
                <th>Empleado</th>
                <th>Lote</th>
                <th>Folio</th>
                <th>Hora</th>
                <th>Fecha</th>
            </thead>
            <tbody id="rilltbl">
            </tbody>
        </table>
    </div>
    <button class="btn btn-danger btn-sm" id="crearpdf">Crear PDF</button>
    <a href="#" class="btn btn-success btn-sm" id="crearexcel">Crear Excel</a>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/rillReporte.js"></script>