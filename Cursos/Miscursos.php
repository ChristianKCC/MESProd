<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container">
    <h4>Estos son tus cursos pendientes</h4>
    <div class="row">
        <div class="col">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>Curso</th>
                        <th>Capacitación</th>
                        <th class="text-center">Cuestionario</th>
                    </thead>
                    <tbody id="tblmiscursos">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="tblmiscursos"></div>
</div>
<div class="modal fade" id="cursosmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="questions">
                    <div class="question">
                        <div id="contcurso"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="justify-content-center" id="pagination">
                    <button class="btn btn-sm bg-target" id="prevBtn" hidden>Anterior</button>
                    <button class="btn btn-sm bg-target" id="nextBtn">Siguiente</button>
                    <button class="btn btn-success" id="finish" style="display: none;">Finalizar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="text/javascript" src="../poojs/herramientas.js"></script>
<script src="js/cursos.js" type="text/javascript"></script>
<script type="text/javascript">
    cursos = new MisCursos();
    cursos.start();
</script>