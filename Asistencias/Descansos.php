<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<div class="container ">
    <h3>Descansos</h3>
    <div class="row">
        <div class="col-2">
            <small>Fecha inicio</small>
            <input type="date" id="fechadescansos" class="form-control form-control-sm" />
        </div>
        <div class="col-2">
            <small>Archivo de descansos</small>
            <input type="file" id="archivo" />
        </div>
        <div class="col-2">
            <br />
            <button id="savedescansos" class="btn btn-sm bg-target"><i class="fas fa-upload"></i> Subir</button>
        </div>
    </div>
    <hr />
    <div class="row border m-2 p-2">
        <div class="col">
            <small>Fecha inicio</small>
            <input type="date" id="fechai" class="form-control form-control-sm" />
        </div>
        <div class="col">
            <small>Fecha final</small>
            <input type="date" id="fechaf" class="form-control form-control-sm" />
        </div>
        <div class="col">
            <small>Noemp</small>
            <input type="number" id="noemp" class="form-control form-control-sm" />
        </div>
        <div class="col-1">
            <small><br /></small>
            <button class="btn bg-target btn-sm" id="buscar"><i class="fas fa-search"></i> Buscar</button>
        </div>
        <div class="table-responsive mt-2" style="height: 600px;">
            <table class="table">
                <thead class="table-dark">
                    <th>NoEmp</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>L</th>
                    <th>M</th>
                    <th>M</th>
                    <th>J</th>
                    <th>V</th>
                    <th>S</th>
                    <th>D</th>
                    <th></th>
                </thead>
                <tbody id="tbldescansos">
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Descansos.js"></script>