<?php
require_once("../Session/seguridad.php");
$_SESSION["nvlplaticas"] > 0 ? NULL : header("Location:../index/index.php");

require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-3 border rounded shadow">
    <h4>Reporte platicas</h4>
    <form method="POST" action="php/crearpdf.php" target="iframe_a" class="mb-4">
    <div class="row">
        <div class="col">
        <input type="date" class="form-control form-control-sm" name="fechai" id="fechai" required>
        </div>
        <div class="col">
        <input type="date" class="form-control form-control-sm" name="fechaf" id="fechaf" required>
        </div>
        <div class="col">
        <input class="form-control btn btn-sm bg-target" value="Consultar" type="submit">
        </div>
    </div>
    </form>
    <iframe name="iframe_a" height="700px" width="100%" title="Iframe Example"></iframe>
</div>
<?php require_once("../index/footer.php") ?>
<script type="text/javascript" src="js/index.js"></script>
