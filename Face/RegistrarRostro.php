<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<style>
    #video {
        position: relative;
        left: 50%;
        transform: translateX(-50%);
        border: 2px solid #444;
    }

    #overlay {
        position: absolute;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        pointer-events: none;
    }

    #result {
        margin-top: 600px;
        font-size: 24px;
    }
</style>
<div class="container">
    <h5 class="tittlecont">Registrar rostro</h5>
    <video id='video' width='600' height='500' autoplay></video>
    <canvas id="overlay" width="600" height="500"></canvas>
    <div class="row justify-content-center">
        <div class="col-3">
            <input class="form-control" type='number' id='name' placeholder='Numero de empleado'>
        </div>
        <div class="col-1">
            <button class="form-control btn bg-target" onclick='saveFace()'>Guardar</button>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src='js/faceRegister.js'></script>