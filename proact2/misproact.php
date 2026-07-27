<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
    <h5 class="tittlecont">Estas son las observaciones que te han hecho</h5>
    <div class="row">
        <div class="table-responsive">
            <table class="table table-sm" id="tblobserb">
                <thead class="table-dark">
                    <th>ID</th>
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
                <tbody id="tableobs">
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/misobs.js" type="text/javascript"></script>