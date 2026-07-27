<?php
require_once(dirname (__DIR__, 1) . "/Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location: ../index/index.php");
    exit;
}

require_once(__DIR__ . "/Organigrama/CargarOrganigrama/LogicaCargarOrganigrama.php");
require_once(__DIR__ . "/../index/header.php");
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">  
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/pptxstyle.css">
  <link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
  <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
</head>
<body>

<div class="container p-4">
    <h5 class="tittlecont">Actualización de archivo PowerPoint para 'Matriz EMC'</h5>
    <br /><br />

    <div class="row">
        <div class="col-20">    
            <small class="alert alert-info">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>                
                Sube el archivo <strong>.pptx</strong> del organigrama. El archivo anterior será reemplazado automáticamente y estará disponible para consulta inmediata.
            </small>
        </div>
    </div>
    <br />

  <!-- ══ CONTENIDO ═══════════════════════════ -->
  <div class="emc-page">
    
    <!-- Alertas dinámicas (JS) -->
    <div id="emc-alert-container"></div>

    <!-- Alerta PHP (recarga sin AJAX) -->
    <?php if (!$isAjax && !empty($response)): ?>
      <div class="emc-alert emc-alert-<?= $response['success'] ? 'success' : 'danger' ?> auto-hide">
        <span><i class="fa-solid <?= $response['success'] ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i></span>
        <span><?= htmlspecialchars($response['message']) ?></span>        
      </div>
    <?php endif; ?>

    <!-- ── Estado actual del archivo ─────── -->
    <div class="emc-card">
        <div class="emc-card-title d-flex justify-content-between align-items-center">
          <div class="icon icon-info"><i class="fa-solid fa-circle-info"></i></div>
          <span>Estado del archivo actual</span>

           <?php if ($fileExists): ?>
            <div class="d-flex gap-2 flex-wrap ms-auto">
              <a href="OrganigramaEMC.php" class="emc-btn emc-btn-primary emc-btn-sm">
                <i class="fa-solid fa-eye"></i> Ver presentación
              </a>
              <a href="uploads/organigrama.pptx" download class="emc-btn emc-btn-outline emc-btn-sm">
                <i class="fa-solid fa-download"></i> Descargar
              </a>
            </div>
          <?php endif; ?>
        </div>
      
      <div class="emc-status-panel">
        <span class="status-icon">
          <?php if ($fileExists): ?>
            <i class="fa-solid fa-file-powerpoint"></i>
          <?php else: ?>
            <i class="fa-regular fa-folder-open"></i>
          <?php endif; ?>
        </span>
        <div class="status-body">
          <div class="status-name" id="status-file-name">
            <?= $fileExists ? htmlspecialchars($fileName) : 'Sin archivo cargado' ?>
          </div>
          <div class="status-meta" id="status-file-meta">
            <?php if ($fileExists): ?>
              Actualizado: <?= $fileUpdated ?> &nbsp;·&nbsp; <?= $fileSize ?>
            <?php else: ?>
              El organigrama aún no ha sido cargado
            <?php endif; ?>
          </div>
        </div>
        <span class="status-badge <?= $fileExists ? 'badge-ok' : 'badge-none' ?>" id="status-badge">
          <?= $fileExists ? 'Disponible' : 'Sin archivo' ?>
        </span>
      </div>
     
    </div>

    <!-- ── Formulario de carga ────────────── -->
    <div class="emc-card">
      <div class="emc-card-title">
        <div class="icon icon-ppt"><i class="fa-solid fa-cloud-arrow-up"></i></div>
        <?= $fileExists ? 'Reemplazar presentación' : 'Subir presentación' ?>
      </div>

      <form id="emc-upload-form" method="POST" enctype="multipart/form-data">      
        <!-- Drop zone -->
        <div class="emc-dropzone" id="dropzone">
          <input type="file"
                name="pptx_file"
                class="emc-file-input"
                accept=".pptx,.ppt"
                required>          
          <i class="fa-solid fa-file-powerpoint fa-2x"></i>
          <div class="dz-title">Arrastra tu archivo aquí o haz clic para seleccionarlo</div>
          <div class="dz-sub">Formatos aceptados: <strong>.pptx</strong> · Máx. <?= MAX_SIZE_MB ?> MB</div>
        </div>

        <!-- Archivo seleccionado -->
        <div class="emc-selected-file" id="emc-selected-file-wrap">
          <i class="fa-solid fa-file-powerpoint" style="color:#D14343"></i>
          <span id="selected-file-name">—</span>
        </div>

        <!-- Barra de progreso -->
        <div class="emc-progress-wrap" id="emc-progress-wrap">
          <div class="emc-progress-label">
            <span>Subiendo…</span>
            <span id="emc-progress-pct">0%</span>
          </div>
          <div class="emc-progress-bar-bg">
            <div class="emc-progress-bar-fill" id="emc-progress-fill"></div>
          </div>
        </div>

        <!-- Botones -->
        <div class="emc-file-select-row d-flex justify-content-center align-items-center gap-3" style="margin-top:1.5rem">
          <button type="submit" class="emc-btn btn-primary emc-btn-lg">
            <i class="fa-solid fa-cloud-upload-alt"></i> Subir archivo
          </button>
          <?php if ($fileExists): ?>
            <span class="emc-alert emc-alert-warning" style="font-size:.78rem;padding:8px 12px;margin:0">
              <i class="fa-solid fa-triangle-exclamation"></i>
              Esto reemplazará la presentación actual
            </span>
          <?php endif; ?>
        </div>

      </form>
    </div>
  </div><!-- /emc-page -->
</div>

<!-- <script src="js/app.js"></script> -->
<script src="js/app.js"></script>
<?php require_once("../index/footer.php") ?>