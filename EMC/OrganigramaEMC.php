<?php
require_once(dirname (__DIR__, 1) . "/Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location: ../index/index.php");
    exit;
}
require_once(__DIR__ . "/Organigrama/VerOrganigrama/LogicaVerOrganigrama.php");
require_once(__DIR__ . "/../index/header.php");
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/pptxstyle.css">
  
</head>
<body>

<div class="container p-4">
    <h5 class="tittlecont">Consulta de datos 'Organigrama EMC'</h5>
    <br /><br />

    <div style="float:left" class="row">
        <div class="col-20">    
            <small class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                     class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                     aria-label="Warning:">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 
                             1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 
                             1.566zM8 5c.535 0 .954.462.9.995l-.35 
                             3.507a.552.552 0 0 1-1.1 0L7.1 
                             5.995A.905.905 0 0 1 8 5zm.002 
                             6a1 1 0 1 1 0 2 1 1 0 0 1 
                             0-2z"/>
                </svg>                
                Desde esta sección consulta el 'Organigrama EMC'
            </small>
        </div>
    </div>
    <br />

<?php if (!$fileExists): ?>
<!-- ══ SIN ARCHIVO ══ -->
<div class="emc-page">
  <div class="emc-empty">
    <span class="empty-icon">📭</span>
    <h2>Presentación no disponible</h2>
    <p>Vuelve mas tarde para poder consultarlo aquí.</p>    
  </div>
</div>

<?php else: ?>
<!-- ══ VISOR ══ -->
<div class="emc-page-wide">

  <!-- Shell del visor -->
  <div class="pptx-shell">

    <!-- Toolbar -->
    <div class="pptx-toolbar">
      <div class="pptx-title-area">
        <i class="bi bi-file-earmark-ppt-fill" style="color:#D14343;font-size:1.15rem"></i>
        <div>
          <div class="pptx-fname"><?= $fileName ?></div>
          <div class="pptx-fmeta">
            <?= $fileUpdated ?> &nbsp;·&nbsp; <?= $fileSize ?>
            &nbsp;·&nbsp;
            <span class="anim-badge"><i class="bi bi-stars"></i> Visualiza con 'Abrir en PowerPoint'</span>
          </div>
        </div>
      </div>
      <div class="pptx-actions">
        <button class="tb-btn" id="btnPresent" title="Modo presentación (tecla F)">
          <i class="bi bi-fullscreen"></i> Presentar
        </button>
        <!-- Abrir en PowerPoint REAL -->
        <a href="<?= htmlspecialchars($msPptUrl) ?>"
           class="tb-btn tb-btn-ppt pulsing"
           id="btnOpenPpt"
           title="Abre el archivo en PowerPoint con fidelidad 100%">
          <i class="bi bi-play-circle-fill"></i> Abrir en PowerPoint
        </a>
        <!-- Descargar -->
        <a href="uploads/organigrama.pptx" download class="tb-btn" title="Descargar archivo">
          <i class="bi bi-download"></i> Descargar
        </a>
        
      </div>
    </div>

 

    <!-- Stage: pptx-preview renderiza aquí -->
    <div class="pptx-stage" id="pptxStage">

      <!-- Loading inicial -->
      <div class="pptx-loading" id="pptxLoading">
        <div class="spinner"></div>
        <p>Cargando presentación…</p>
        <span class="sub">Procesando <?= $fileName ?></span>
      </div>

      <!-- Wrapper que pptx-preview usa -->
      <div id="pptxWrapper" style="display:none"></div>

    </div>

    <!-- Navegación flotante -->
    <div class="pptx-nav" id="pptxNav">
      <button id="btnFirst" title="Primera"><i class="bi bi-skip-backward-fill"></i></button>
      <button id="btnPrev"  title="Anterior"><i class="bi bi-chevron-left"></i></button>
      <span class="nav-info" id="navInfo">— / —</span>
      <button id="btnNext"  title="Siguiente"><i class="bi bi-chevron-right"></i></button>
      <button id="btnLast"  title="Última"><i class="bi bi-skip-forward-fill"></i></button>
    </div>

  </div><!-- /pptx-shell -->

  <!-- Botón salir presentación (fixed) -->
  <button class="tb-btn" id="btnExitPresent">
    <i class="bi bi-fullscreen-exit"></i> Salir
  </button>

</div><!-- /emc-page-wide -->
</div>
<?php endif; ?>

<?php if ($fileExists): ?>
<script>
  window.FILE_URL   = <?= json_encode($fileUrl) ?>;
  window.MS_PPT_URL = <?= json_encode($msPptUrl) ?>;
</script>
<script type="module" src="js/pptx.js"></script>
<?php endif; ?>

<script src="js/app.js"></script>
<?php require_once("../index/footer.php") ?>