<?php

require_once("../Session/seguridad.php");
require_once("../index/header.php"); ?>
<!-- Contenido -->
<div class="container-maquinas">
  <div class="row ">
    <div class="col-6 col-md-2" hidden><input type="number" class="form-control form-control-sm " value="1"
        id="numregmonitor"></div>
    <div class="col-6">
      <div class="card border-warning mb-1">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
          </div>
          <div class="row ">
            <div class="col-6 col-md-2">
              <h5 class="card-title mb-0">MP12 <span id="estadomp12"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong>Merma:</strong> <span id="mermamp12"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp12"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp12"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc12"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc12"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor1"></div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptBCM4"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadBCM4"></span></strong></div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <canvas class="GraficaMonitor" id="GrafMaquina" style="height: 220px; width: 100%;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6">
      <div class="card border-warning  mb-1">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
          </div>
          <div class="row ">
            <div class="col-6 col-md-2">
              <h5 class="card-title mb-0">MP13 <span id="estadomp13"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong class="fw-bold">Merma:</strong> <span id="mermamp13"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp13"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp13"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc13"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc13"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor2"></div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptBCM3"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadBCM3"></span></strong></div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <canvas class="GraficaMonitor" id="GrafMaquina2" style="height: 220px; width: 100%;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6">
      <div class="card border-warning  mb-1">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
          </div>
          <div class="row ">
            <div class="col-6 col-md-2">
              <h5 class="card-title mb-0">MP14 <span id="estadomp14"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong class="fw-bold">Merma:</strong> <span id="mermamp14"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp14"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp14"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc14"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc14"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor3"></div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptPE10"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadPE10"></span></strong></div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <canvas class="GraficaMonitor" id="GrafMaquina3" style="height: 220px; width: 100%;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6">
      <div class="card border-warning mb-1">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
          </div>
          <div class="row ">
            <div class="col-6 col-md-2">
              <h5 class="card-title mb-0">MP15 <span id="estadomp15"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong class="fw-bold">Merma:</strong> <span id="mermamp15"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp15"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp15"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc15"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc15"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor4"></div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptBCM1"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadBCM1"></span></strong></div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <canvas class="GraficaMonitor" id="GrafMaquina4" style="height: 220px; width: 100%;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6">
      <div class="card border-warning mb-1">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
          </div>
          <div class="row ">
            <div class="col-6 col-md-2">
              <h5 class="card-title mb-0">MP16 <span id="estadomp16"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong class="fw-bold">Merma:</strong> <span id="mermamp16"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp16"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp16"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc16"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc16"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor5"></div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptMP16"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadMP16"></span></strong></div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <canvas class="GraficaMonitor" id="GrafMaquina5" style="height: 220px; width: 100%;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/monitorpf.js"></script>
</body>

</html>