<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); ?>
<div class="col-6">
  <div class="card border-warning mb-1">
    <div class="card-header">
      <div class="row">
        <div class="col-6 col-md-2">
          <h5 class="card-title mb-0">Máquina 67 <span id="estadoOpc67"></span></h5>
        </div>
        <div class="col-6 col-md-2 fs-5"><strong>Velocidad:</strong> <span id="velocidadOpc67"></span></div>
        <div class="col-6 col-md-2"><strong>Merma:</strong> <span id="mermaOpc67"></span></div>
        <div class="col-6 col-md-2"><strong>Paros:</strong> <span id="parosOpc67"></span></div>
        <div class="col-6 col-md-2"><strong>% T. Perdido:</strong> <span id="pctPerdidoOpc67"></span></div>
        <div class="col-6 col-md-2"><strong id="turnoOpc67"></strong></div>
        <div class="col-6 col-md-2">
          <input type="number" class="form-control form-control-sm" value="180" id="numregOpc67">
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

<!--
  3. Al final de pañal.php, junto a tus otros <script type="module">, agrega:

  <script type="module" src="js/monitor_opc_init.js"></script>
-->