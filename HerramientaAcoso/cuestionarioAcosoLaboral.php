<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<div class="container p-5">
    <h4 class="tittlecont">Valoración del acoso laboral</h4>
    <br>
    <div class="container p-4" style="height: 650px;">
        <input type="hidden" id="noEmp" name="noEmp">
        <div class="row">
            <div class="col-12">
                <strong>¿Cual de las siguientes formas de maltrato psicológico (ver lista de preguntas 1 a 43) se han
                    ejercido
                    contra usted?</strong>
                <br>
                <span>Señale, en su caso, quíenes son las personas autoras de las conductas o acciones de acoso
                    laboral</span>
                <ol style="margin-left: 1.5rem; padding-left: 1rem;">
                    <li>Jefas/jefes o personas supervisoras</li>
                    <li>Personas compañeras de trabajo</li>
                    <li>Personas subordinadas</li>
                </ol>
                <strong>Señale, en su caso, el grado de frecuencia con que se producen estas conductas de acoso</strong>
                <ol start="0" style="margin-left: 1.5rem; padding-left: 1rem;">
                    <li>Nunca</li>
                    <li>Pocas veces al año o menos</li>
                    <li>Una vez al mes o menos</li>
                    <li>Algunas veces al mes</li>
                    <li>Una vez a la semana</li>
                    <li>Varias veces a la semana</li>
                    <li>Todos los días</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <form id="quizForm">
                <!-- Preguntas dinámicas -->
            </form>
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-primary px-4" id="calculateScore">Enviar</button>
            </div>
            <div id="result"></div>
        </div>


    </div>

</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="./js/cuestionario.js"></script>