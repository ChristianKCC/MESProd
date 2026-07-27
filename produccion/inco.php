
<?php 
require_once("../Session/seguridad.php");
require_once("../index/header.php"); ?>
<!-- Contenido -->
<div class="container-maquinas">
  <h4>Estado maquinas de Incontinencia</h4>
  <div class="row">
  <div class="col-6">
      <div class="row">
        <div class="col">
        <small>Horas: </small>
         <input class="form-control form-control-sm" type="number" id="numhrspa01" value="1" min="1" max="100"/>
        </div>
        <div class="col">Cortes: <h5 id="cortespa01"></h5></div>
        <div class="col">Rechazos: <h5 id="rechazospa01"></h5></div>
        <div class="col">Velocidad: <h5 id="velocidadpa01"></h5></div>
        <div class="col">Merma: <h5 id="mermapa01"></h5></div>
        <div class="col">
        <br><h5 class="fw-bold"> PA01 <span id="estadopa01"></span></h5>
      </div>
      </div>
       <canvas style="background-color: #000; color:aliceblue;" height="80px"  id="pa01" height="90"></canvas>
  </div>
    <div class="col-6">
    <div class="row">
        <div class="col">
        <small>Horas: </small>
         <input class="form-control form-control-sm" type="number" id="numhrspa02" value="1" min="1" max="100"/>
        </div>
        <div class="col">Cortes: <h5 id="cortespa02"></h5></div>
        <div class="col">Rechazos: <h5 id="rechazospa02"></h5></div>
        <div class="col">Velocidad: <h5 id="velocidadpa02"></h5></div>
        <div class="col">Merma: <h5 id="mermapa02"></h5></div>
        <div class="col">
        <br><h5 class="fw-bold"> PA02 <span id="estadopa02"></span></h5>
      </div>
      </div>
    <canvas style="background-color: #000; color:aliceblue;" height="80px"   id="pa02" height="90"></canvas>
  </div>
</div>  
<div class="row">
  <div class="col-6">
  <div class="row">
        <div class="col">
        <small>Horas: </small>
         <input class="form-control form-control-sm" type="number" id="numhrspa03" value="1" min="1" max="100"/>
        </div>
        <div class="col">Cortes: <h5 id="cortespa03"></h5></div>
        <div class="col">Rechazos: <h5 id="rechazospa03"></h5></div>
        <div class="col">Velocidad: <h5 id="velocidadpa03"></h5></div>
        <div class="col">Merma: <h5 id="mermapa03"></h5></div>
        <div class="col">
        <br><h5 class="fw-bold"> PA03 <span id="estadopa03"></span></h5>
      </div>
      </div>
    <canvas style="background-color: #000; color:aliceblue;" height="80px"  id="pa03" height="90"></canvas>
  </div>
  <div class="col-6">
  <div class="row">
        <div class="col">
        <small>Horas: </small>
         <input class="form-control form-control-sm" type="number" id="numhrspa04" value="1" min="1" max="100"/>
        </div>
        <div class="col">Cortes: <h5 id="cortespa04"></h5></div>
        <div class="col">Rechazos: <h5 id="rechazospa04"></h5></div>
        <div class="col">Velocidad: <h5 id="velocidadpa04"></h5></div>
        <div class="col">Merma: <h5 id="mermapa04"></h5></div>
        <div class="col">
        <br><h5 class="fw-bold"> PA04 <span id="estadopa04"></span></h5>
      </div>
      </div>
    <canvas style="background-color: #000; color:aliceblue;" height="80px"   id="pa04" height="90"></canvas>
  </div>
</div>
<div class="row">
  <div class="col-6">
  <div class="row">
        <div class="col">
        <small>Horas: </small>
         <input class="form-control form-control-sm" type="number" id="numhrspa05" value="1" min="1" max="100"/>
        </div>
        <div class="col">Cortes: <h5 id="cortespa05"></h5></div>
        <div class="col">Rechazos: <h5 id="rechazospa05"></h5></div>
        <div class="col">Velocidad: <h5 id="velocidadpa05"></h5></div>
        <div class="col">Merma: <h5 id="mermapa05"></h5></div>
        <div class="col">
        <br><h5 class="fw-bold"> PA05  <span id="estadopa05"></span></h5>
      </div>
      </div>
    <canvas style="background-color: #000; color:aliceblue;" height="80px"   id="pa05" height="90"></canvas>
  </div>
 
</div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="text/javascript" src="js/index.js"></script> 
      <script type="text/javascript">
        Monitoreo = new Monitoreo();
        Monitoreo.cargarattrinco();
      </script> 
        
    </body>
</html>