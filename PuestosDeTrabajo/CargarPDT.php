<?php
require_once(dirname (__DIR__, 1) . "/Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location: ../index/index.php");
    exit;
}

require_once(__DIR__ . "/PDT/CargarPDT/LogicaUploadPDT.php");
require_once(__DIR__ . "/../index/header.php");
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/excelstyle.css">
  <link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
  <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
</head>
<body>

<div class="container p-4">
    <h5 class="tittlecont">Actualización de archivo excel para 'Puestos De Trabajo'</h5>
    <br /><br />

    <div class="row">
        <div class="col-20">    
            <small class="alert alert-info">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>                
                Sube el archivo <strong>Excel (.xlsx)</strong> o <strong>CSV (.csv)</strong> y pega el <strong>enlace de Excel Online</strong>. El archivo y el enlace anteriores serán reemplazados automáticamente.
            </small>
        </div>
    </div>
    <br />

    <!-- ══ CONTENIDO ═══════════════════════════ -->
    <div class="emc-page">

      <!-- Alertas dinámicas -->
      <div id="emc-alert-container"></div>

      <?php if (!$isAjax && !empty($response)): ?>
        <div class="emc-alert emc-alert-<?= $response['success'] ? 'success' : 'danger' ?> auto-hide">
          <span><i class="fa-solid <?= $response['success'] ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i></span>
          <span><?= htmlspecialchars($response['message']) ?></span>
        </div>
      <?php endif; ?>

      <!-- ── Estado actual ─────────────────── -->
      <div class="emc-card">
        <div class="emc-card-title d-flex justify-content-between align-items-center">
          <div class="icon icon-info"><i class="fa-solid fa-circle-info"></i></div>
          <span>Estado del archivo actual</span>

          <?php if ($fileExists): ?>
            <div class="d-flex gap-2 flex-wrap ms-auto">
              <a href="PDT.php" class="emc-btn emc-btn-primary emc-btn-sm">
                <i class="fa-solid fa-table"></i> Ver datos
              </a>
              <a href="uploads/<?= htmlspecialchars($fileName) ?>" download class="emc-btn emc-btn-outline emc-btn-sm">
                <i class="fa-solid fa-download"></i> Descargar
              </a>
            </div>
          <?php endif; ?>
        </div>

        <div class="emc-status-panel">
          <span class="status-icon">
            <?php if ($fileExists): ?>
              <i class="fa-solid <?= $activeExt === 'csv' ? 'fa-file-lines' : 'fa-file-excel' ?>"></i>
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
                Tipo: <?= $typeLabel ?> &nbsp;·&nbsp;
                Actualizado: <?= $fileUpdated ?> &nbsp;·&nbsp; <?= $fileSize ?>
              <?php else: ?>
                Aún no se ha cargado ningún archivo de datos
              <?php endif; ?>
            </div>
          </div>
          <span class="status-badge <?= $fileExists ? 'badge-ok' : 'badge-none' ?>" id="status-badge">
            <?= $fileExists ? 'Disponible' : 'Sin archivo' ?>
          </span>
        </div>        
      </div>

      <!-- ── Formulario ────────────────────── -->
      <div class="emc-card">
        <div class="emc-card-title">
          <div class="icon icon-xl"><i class="fa-solid fa-cloud-arrow-up"></i></div>
          <?= $fileExists ? 'Reemplazar archivo de datos' : 'Subir archivo de datos' ?>
        </div>

        <form id="emc-upload-form" method="POST" enctype="multipart/form-data" data-tipo="5">

          <div class="emc-dropzone" id="dropzone">
            <input type="file"
                  name="EXCEL_FILE_MEMC_PDT"
                  class="emc-file-input"
                  accept=".xlsx,.xls,.csv"
                  required>
            <i class="fa-solid fa-file-excel fa-2x"></i>
            <div class="dz-title">Arrastra tu archivo aquí o haz clic para seleccionarlo</div>
            <div class="dz-sub">Formatos: <strong>.xlsx</strong> · <strong>.xls</strong> · <strong>.csv</strong> &nbsp;·&nbsp; Máx. <?= MAX_SIZE_MB ?> MB</div>
          </div>

          <div class="emc-selected-file" id="emc-selected-file-wrap">
            <i class="fa-solid fa-file-excel" style="color:#229A54"></i>
            <span id="selected-file-name">—</span>
          </div>

          <!-- ════════ NUEVO: enlace de Excel Online ════════ -->
          <div class="emc-link-block" style="margin-top:1.5rem">
            <label for="emc-enlace-input" class="emc-link-label">
              <i class="fa-solid fa-link"></i> Enlace del archivo (Excel Online / SharePoint)
            </label>

            <textarea id="emc-enlace-input"
                      name="enlace"
                      class="emc-link-input"
                      rows="3"
                      placeholder='Pega aquí el enlace o el código embed que comparte Excel Online (debe contener "sourcedoc=...")'
                      required></textarea>

            <small class="emc-link-help">
              <i class="fa-solid fa-circle-info"></i>
              Pega el enlace completo o el <strong>código &lt;iframe&gt;</strong> del botón "Insertar / Embed". Se recortará automáticamente.
            </small>

            <!-- Preview del enlace ya recortado -->
            <div class="emc-link-preview" id="emc-link-preview-wrap" style="display:none">
              <div class="emc-link-preview-title">
                <i class="fa-solid fa-scissors"></i> Así quedará tu enlace recortado:
              </div>
              <code class="emc-link-preview-value" id="emc-link-preview-value">—</code>
            </div>

            <div class="emc-link-error" id="emc-link-error" style="display:none">
              <i class="fa-solid fa-circle-xmark"></i>
              <span>No se detectó un <strong>sourcedoc</strong> válido en el enlace.</span>
            </div>
          </div>
          <!-- ════════ /NUEVO ════════ -->

          <div class="emc-progress-wrap" id="emc-progress-wrap">
            <div class="emc-progress-label">
              <span>Subiendo…</span>
              <span id="emc-progress-pct">0%</span>
            </div>
            <div class="emc-progress-bar-bg">
              <div class="emc-progress-bar-fill" id="emc-progress-fill"></div>
            </div>
          </div>

          <div class="emc-file-select-row d-flex justify-content-center align-items-center gap-3" style="margin-top:1.5rem">
            <button type="submit" class="emc-btn btn-primary emc-btn-lg">
              <i class="fa-solid fa-cloud-upload-alt"></i> Subir archivo y enlace
            </button>
            <?php if ($fileExists): ?>
              <span class="emc-alert emc-alert-warning" style="font-size:.78rem;padding:8px 12px;margin:0">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Esto reemplazará los datos actuales
              </span>
            <?php endif; ?>
          </div>

        </form>
      </div>      

    </div><!-- /emc-page -->
</div>

<!-- estilos del bloque de enlace (puedes moverlos a excelstyle.css) -->
<style>
  .emc-link-label { display:block; font-weight:600; font-size:.85rem; margin-bottom:.4rem; }
  .emc-link-input {
      width:100%; resize:vertical; border:1px solid #cfd4dd; border-radius:8px;
      padding:10px 12px; font-family:'JetBrains Mono',monospace; font-size:.8rem;
  }
  .emc-link-input:focus { outline:none; border-color:#2E6DB4; box-shadow:0 0 0 3px rgba(46,109,180,.15); }
  .emc-link-help { display:block; margin-top:.4rem; font-size:.74rem; color:#6b7280; }
  .emc-link-preview {
      margin-top:.9rem; background:#f0f8f4; border:1px solid #b6e0c8;
      border-radius:8px; padding:10px 12px;
  }
  .emc-link-preview-title { font-size:.75rem; font-weight:600; color:#1a8040; margin-bottom:.35rem; }
  .emc-link-preview-value { display:block; word-break:break-all; font-size:.76rem; color:#14532d; }
  .emc-link-error {
      margin-top:.9rem; background:#fdecec; border:1px solid #f5b5b5;
      border-radius:8px; padding:10px 12px; color:#b42318; font-size:.78rem;
      display:flex; gap:8px; align-items:center;
  }
</style>

<script src="js/app.js"></script>
<?php require_once("../index/footer.php") ?>