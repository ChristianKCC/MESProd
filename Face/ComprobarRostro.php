<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<div class="container">
  <h5 class="tittlecont">Comporbar rostro</h5>

  <video id='video' width='600' height='500' autoplay></video>
  <canvas id="overlay" width="600" height="500"></canvas>

  <br>
  <h3 id="result"></h3>
  <div class="row justify-content-center">
    <div class="col-2">
      <button class="form-control btn bg-target" id="startRecognition">Iniciar Reconocimiento</button>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src='js/face.js' type="module"></script>