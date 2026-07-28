<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); ?>
<style>
  .semaforo-container {
    background: #11161f;
    border-radius: 48px;
    padding: 7px;
    display: flex;
    justify-content: center;
    gap: 15px;
  }

  .luz {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #2d3a4e;
    transition: all 0.2s;
  }

  .luz.amarillo.active {
    background: #fbbf24;
    box-shadow: 0 0 20px #fbbf24;
  }

  .luz.naranja.active {
    background: #f97316;
    box-shadow: 0 0 20px #f97316;
  }

  .luz.rojo.active {
    background: #ef4444;
    box-shadow: 0 0 22px #ef4444;
  }

  .strikes-area {
    border-radius: 48px;
    text-align: center;
  }

  .taches-container {
    display: flex;
    justify-content: center;
    gap: 2px;
    flex-wrap: wrap;
    align-items: center;
    min-height: 35px;
  }

  .tache {
    background: #1e293b;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 30px;
    font-size: .9rem;
    font-weight: 800;
    color: #eb2828;
  }

  .tache.mas {
    background: #1e293b;
    color: #f9f9f9;
  }
</style>
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
              <h5 class="card-title mb-0">BCM4 <span id="estadomp12"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong>Merma:</strong> <span id="mermamp12"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp12"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp12"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc12"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc12"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor1"></div>
            <div class="col-6 col-md-2">
              <select id="scaleMermaBCM4" class="form-control form-control-sm">
                <option value="">Auto</option>
                <option value="0.5">0.5 %</option>
                <option value="1">1 %</option>
                <option value="2">2 %</option>
                <option value="5">5 %</option>
                <option value="7">7 %</option>
                <option value="10">10 %</option>
                <option value="15">15 %</option>
                <option value="20">20 %</option>
              </select>
            </div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptBCM4"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadBCM4"></span></strong></div>
            <div class="col-6 col-md-2">
              <div class="strikes-area">
                <strong>
                  <center>S+D:</center>
                </strong>
                <div class="taches-container" id="tachesContainerBCM4">
                </div>
              </div>
            </div>
            <div class="col-12 col-md-2">
              <strong>
                <center>QL:</center>
              </strong>
              <div class="semaforo-container">
                <div class="luz amarillo" id="luzAmarilloBCM4"></div>
                <div class="luz naranja" id="luzNaranjaBCM4"></div>
                <div class="luz rojo" id="luzRojoBCM4"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div id="GrafMaquina" style="width: 100%;"></div>
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
              <h5 class="card-title mb-0">BCM3 <span id="estadomp13"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong class="fw-bold">Merma:</strong> <span id="mermamp13"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp13"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp13"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc13"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc13"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor2"></div>
            <div class="col-6 col-md-2">
              <select id="scaleMermaBCM3" class="form-control form-control-sm">
                <option value="">Auto</option>
                <option value="0.5">0.5 %</option>
                <option value="1">1 %</option>
                <option value="2">2 %</option>
                <option value="5">5 %</option>
                <option value="7">7 %</option>
                <option value="10">10 %</option>
                <option value="15">15 %</option>
                <option value="20">20 %</option>
              </select>
            </div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptBCM3"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadBCM3"></span></strong></div>
            <div class="col-6 col-md-2">
              <div class="strikes-area">
                <strong>
                  <center>S+D:</center>
                </strong>
                <div class="taches-container" id="tachesContainerBCM3">
                </div>
              </div>
            </div>
            <div class="col-12 col-md-2">
              <strong>
                <center>QL:</center>
              </strong>
              <div class="semaforo-container">
                <div class="luz amarillo" id="luzAmarilloBCM3"></div>
                <div class="luz naranja" id="luzNaranjaBCM3"></div>
                <div class="luz rojo" id="luzRojoBCM3"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div id="GrafMaquina2" style="width: 100%;"></div>
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
              <h5 class="card-title mb-0">PE10 <span id="estadomp14"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong class="fw-bold">Merma:</strong> <span id="mermamp14"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp14"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp14"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc14"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc14"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor3"></div>
            <div class="col-6 col-md-2">
              <select id="scaleMermaPE10" class="form-control form-control-sm">
                <option value="">Auto</option>
                <option value="0.5">0.5 %</option>
                <option value="1">1 %</option>
                <option value="2">2 %</option>
                <option value="5">5 %</option>
                <option value="7">7 %</option>
                <option value="10">10 %</option>
                <option value="15">15 %</option>
                <option value="20">20 %</option>
              </select>
            </div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptPE10"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadPE10"></span></strong></div>
            <div class="col-6 col-md-2">
              <div class="strikes-area">
                <strong>
                  <center>S+D:</center>
                </strong>
                <div class="taches-container" id="tachesContainerPE10">
                </div>
              </div>
            </div>
            <div class="col-12 col-md-2">
              <strong>
                <center>QL:</center>
              </strong>
              <div class="semaforo-container">
                <div class="luz amarillo" id="luzAmarilloPE10"></div>
                <div class="luz naranja" id="luzNaranjaPE10"></div>
                <div class="luz rojo" id="luzRojoPE10"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div id="GrafMaquina3" style="width: 100%;"></div>
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
              <h5 class="card-title mb-0">BCM1 <span id="estadomp15"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong class="fw-bold">Merma:</strong> <span id="mermamp15"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp15"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp15"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc15"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc15"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor4"></div>
            <div class="col-6 col-md-2">
              <select id="scaleMermaBCM1" class="form-control form-control-sm">
                <option value="">Auto</option>
                <option value="0.5">0.5 %</option>
                <option value="1">1 %</option>
                <option value="2">2 %</option>
                <option value="5">5 %</option>
                <option value="7">7 %</option>
                <option value="10">10 %</option>
                <option value="15">15 %</option>
                <option value="20">20 %</option>
              </select>
            </div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptBCM1"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadBCM1"></span></strong></div>
            <div class="col-6 col-md-2">
              <div class="strikes-area">
                <strong>
                  <center>S+D:</center>
                </strong>
                <div class="taches-container" id="tachesContainerBCM1">
                </div>
              </div>
            </div>
            <div class="col-12 col-md-2">
              <strong>
                <center>QL:</center>
              </strong>
              <div class="semaforo-container">
                <div class="luz amarillo" id="luzAmarilloBCM1"></div>
                <div class="luz naranja" id="luzNaranjaBCM1"></div>
                <div class="luz rojo" id="luzRojoBCM1"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div id="GrafMaquina4" style="width: 100%;"></div>
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
              <h5 class="card-title mb-0">MP22 <span id="estadomp16"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong class="fw-bold">Merma:</strong> <span id="mermamp16"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp16"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp16"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc16"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc16"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor5"></div>
            <div class="col-6 col-md-2">
              <select id="scaleMermaMP22" class="form-control form-control-sm">
                <option value="">Auto</option>
                <option value="0.5">0.5 %</option>
                <option value="1">1 %</option>
                <option value="2">2 %</option>
                <option value="5">5 %</option>
                <option value="7">7 %</option>
                <option value="10">10 %</option>
                <option value="15">15 %</option>
                <option value="20">20 %</option>
              </select>
            </div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptMP22"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadMP22"></span></strong></div>
            <div class="col-6 col-md-2">
              <div class="strikes-area">
                <strong>
                  <center>S+D:</center>
                </strong>
                <div class="taches-container" id="tachesContainerMP22">
                </div>
              </div>
            </div>
            <div class="col-12 col-md-2">
              <strong>
                <center>QL:</center>
              </strong>
              <div class="semaforo-container">
                <div class="luz amarillo" id="luzAmarilloMP22"></div>
                <div class="luz naranja" id="luzNaranjaMP22"></div>
                <div class="luz rojo" id="luzRojoMP22"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div id="GrafMaquina5" style="width: 100%;"></div>
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
              <h5 class="card-title mb-0">MP25 <span id="estadomp17"></span></h5>
            </div>
            <div class="col-6 col-md-2 fs-5"><strong class="fw-bold">Merma:</strong> <span id="mermamp17"></span></div>
            <div class="col-6 col-md-2"><strong>Arriba:</strong> <span id="tamp17"></span></div>
            <div class="col-6 col-md-2"><strong>Abajo:</strong> <span id="tpmp17"></span></div>
            <div class="col-6 col-md-2"><strong>Cortes:</strong> <span id="cc17"></span></div>
            <div class="col-6 col-md-2"><strong>Rechazos:</strong> <span id="rc17"></span></div>
            <div class="col-6 col-md-2"><input type="number" class="form-control form-control-sm " value="1"
                id="numregmonitor6"></div>
            <div class="col-6 col-md-2">
              <select id="scaleMermaMP25" class="form-control form-control-sm">
                <option value="">Auto</option>
                <option value="0.5">0.5 %</option>
                <option value="1">1 %</option>
                <option value="2">2 %</option>
                <option value="5">5 %</option>
                <option value="7">7 %</option>
                <option value="10">10 %</option>
                <option value="15">15 %</option>
                <option value="20">20 %</option>
              </select>
            </div>
            <div class="col-6 col-md-2"><strong>TPT: <span id="tptMP25"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadMP25"></span></strong></div>
            <div class="col-6 col-md-2">
              <div class="strikes-area">
                <strong>
                  <center>S+D:</center>
                </strong>
                <div class="taches-container" id="tachesContainerMP25">
                </div>
              </div>
            </div>
            <div class="col-12 col-md-2">
              <strong>
                <center>QL:</center>
              </strong>
              <div class="semaforo-container">
                <div class="luz amarillo" id="luzAmarilloMP25"></div>
                <div class="luz naranja" id="luzNaranjaMP25"></div>
                <div class="luz rojo" id="luzRojoMP25"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div id="GrafMaquina6" style="width: 100%;"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6">
      <div class="card border-warning mb-1">
        <div class="card-header">
          <div class="row">
            <div class="col-6 col-md-2">
              <h5 class="card-title mb-0">HookMesh <span id="estadoOpc67"></span></h5>
            </div>
            <div class="col-6 col-md-2"><strong>Velocidad: <span id="velocidadOpc67"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Merma: <span id="mermaOpc67"></span></strong></div>
            <div class="col-6 col-md-2"><strong>Paros: <span id="parosOpc67"></span></strong></div>
            <div class="col-6 col-md-2"><strong>T. Perdido: <span id="pctPerdidoOpc67"></span></strong></div>
            <div class="col-6 col-md-2"><strong id="turnoOpc67"></strong></div>
            <div class="col-6 col-md-2">
              <input type="number" class="form-control form-control-sm" value="1" id="numregOpc67">
            </div>
            <div class="col-6 col-md-2">
              <select id="scaleMermaOpc67" class="form-control form-control-sm">
                <option value="">Auto</option>
                <option value="0.5">0.5 %</option>
                <option value="1">1 %</option>
                <option value="2">2 %</option>
                <option value="5">5 %</option>
                <option value="7">7 %</option>
                <option value="10">10 %</option>
                <option value="15">15 %</option>
                <option value="20">20 %</option>
              </select>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <div id="GrafOpc67" style="width: 100%;"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/monitorpanal.js"></script>
<script type="module" src="js/semaforo.js"></script>
<script type="module" src="js/monitoropc_init.js"></script>
</body>

</html>