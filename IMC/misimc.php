<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
    <h5 class="tittlecont">Estos son los IMC que te han hecho</h5>
    <div class="row">
        <div class="table-responsive">
            <table class="table table-sm" id="tblimc">
                <thead class="table-dark">
                    <th>IMC</th>
                    <th>Creado</th>
                    <th>Emisor</th>
                    <th>Departamento</th>
                    <th>Area</th>
                    <th>Detección</th>
                    <th>Riesgo</th>
                    <th>Tipo</th>
                    <th>Responsable</th>
                    <th>Compromiso</th>
                    <th>Estado</th>
                    <th>Descripción</th>
                    <th>Sugerencia</th>
                    <th></th>
                    <th></th>
                </thead>
                <tbody id="tblReporteIMC">
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/MisIMC.js" type="text/javascript"></script>