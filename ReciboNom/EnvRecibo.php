<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5>Enviar Recibo de Nomina</h5>
    <div class="row justify-content-center">
        <div class="col-2">
            <small>Escribe tu numero de empleado</small>
            <input type="text" id="noempnom" class="form-control form-control-sm" value="<?php echo $_SESSION['ibm']?>" readonly>
        </div>
        <div class="col-3">
            <small>Escribe tu correo</small>
            <input type="mail" id="correo" class="form-control form-control-sm">
        </div>
        <div class="col-1">
            <br />
            <button type="button" id='buscarecibo' class="btn btn-sm bg-target"> <i class="fas fa-search"></i> Buscar</button>
        </div>
        <div class="col-2">
            <br />
            <button type="button" id='enviarrecibo' class="btn btn-sm btn-danger"><i class="fas fa-mail-bulk"></i> Enviar Email</button>
        </div>
        <?php
        if($_SESSION["permisoPersonal"]==1){
        ?>
        <div class="col-2">
            <small>Cargar archivo</small>
            <input type="file" id='inputFile' class="form-control" />
        </div>
        <div class="col-2">
            <br />
            <button type="button" id='cargaarchivo' class="btn btn-warning form-control"><i class="fas fa-file-pdf"></i> Cargar archivo</button>
        </div>
        <?php
        }
        ?>
        <div class="row justify-content-center">
            <div class="col-12">
                <canvas id="miCanvas"></canvas>
                <canvas id="new"></canvas>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="text/javascript" src="../assets/pdflec/build/pdf.min.js"></script>
<script type="text/javascript" src="js/index.js"></script>