<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<div class="container ">
    <h5 class="tittlecont">Registro de asistencias Biostar</h5>
    <form method="POST" target="iframe_a">
        <div class="row">
            <div class="col"><small>Fecha inicio</small><input type="date" class="form-control form-control-sm" id="fechai" name="fechai" required></div>
            <div class="col"><small>Centro de costos</small><select class="form-control form-control-sm" id="ctrocstos" name="ctrocstos"></select></div>
            <div class="col"><small>Departamento</small><select class="form-control form-control-sm" id="departamento" name="departamento"></select></div>
            <div class="col"><small>Tipo de empleados</small><select class="form-control form-control-sm" id="tipemp" name="tipemp">
                    <option value="">Todos los empleados</option>
                    <option value="0">Sindicalizado</option>
                    <option value="1">Empleado</option>
                </select></div>
            <div class="col-1"><small><br></small> <button class="btn btn-sm bg-target" formaction="PDF/asistenciasbiostar.php"><i class="fas fa-soap"></i>Limpieza</button></div>
            <div class="col-1"><small><br></small> <button class="btn btn-sm bg-target" formaction="PDF/creartarjetassuprema.php"><i class="fas fa-users"></i> Empleados</button></div>
            <div class="col-1"><small><br></small><button type="reset" class="btn btn-sm btn-danger"><i class="fas fa-undo-alt"></i> Reiniciar</button></div>
        </div>
    </form>
    <form action="PDF/cargararchivobiostar.php" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-3">
                <small>Tipo</small><select class="form-control form-control-sm" id="tipoempleado" name="tipoempleado">
                    <option value="1">Empleados</option>
                    <option value="2">Limpieza</option>
                </select>
            </div>
            <div class="col">
                <br />
                <input type="file" name="archivo_csv">
                <button class="btn btn-sm btn-danger" type="submit"><i class="fas fa-file-excel"></i> Cargar archivo</button>
            </div>
        </div>
    </form>
    <iframe name="iframe_a" height="680px" width="100%" title="Iframe Example"></iframe>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Asistencias.js"></script>