<?php
require_once(dirname (__DIR__, 1) . "/Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location: ../index/index.php");
    exit;
}
require_once(__DIR__ . "/Matriz/VerMatriz/LogicaVerMatriz.php");
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
    <h5 class="tittlecont">Consulta de datos 'Matriz EMC'</h5>
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
                Desde esta sección consulta el archivo de 'Matriz EMC'
            </small>
        </div>
    </div>
    <br />
    <br />        

    <?php if (!$fileExists): ?>
    <!-- ══ SIN ARCHIVO ══ -->
    <div class="emc-page">
      <div class="emc-empty">
        <span class="empty-icon">📋</span>
        <h2>No hay datos disponibles</h2>
        <p>Vuelve a intentarlo mas tarde para consultarlo aquí.</p>
      </div>
    </div>
  

    <?php else: ?>
    <!-- ══ VISOR ══ -->
    <div class="emc-page-wide">
    
    <div class="xl-shell" id="xlShell">

      <!-- Toolbar -->
      <div class="xl-toolbar">
        <div class="xl-title-area">
          <i class="bi bi-file-earmark-<?= $activeExt === 'csv' ? 'text' : 'excel' ?>-fill"
            style="font-size:1.15rem;color:<?= $activeExt === 'csv' ? '#2E6DB4' : '#229A54' ?>"></i>
          <div>
            <div class="xl-fname"><?= htmlspecialchars($fileName) ?></div>
            <div class="xl-fmeta"><?= $fileUpdated ?> &nbsp;·&nbsp; <?= $fileSize ?></div>
          </div>
        </div>
        <div class="xl-actions">
          <button class="tb-btn" id="btnFullscreen" title="Pantalla completa">
            <i class="bi bi-fullscreen"></i> Presentar
          </button>
          <a href="uploads/<?= htmlspecialchars($fileName) ?>" download class="tb-btn">
            <i class="bi bi-download"></i> Descargar
          </a>
          <!-- <a href="upload_excel.php" class="tb-btn">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
          </a> -->
        </div>
      </div>

      <!-- Pestañas de hojas (se llenan con JS) -->
      <div class="xl-sheets" id="xlSheets" style="display:none"></div>

      <!-- Barra de búsqueda -->
      <div class="xl-search-bar" id="xlSearchBar" style="display:none">
        <div class="xl-search-wrap">
          <i class="bi bi-search si"></i>
          <input type="text" id="xlSearch" placeholder="Buscar en los datos…" autocomplete="off">
        </div>
        <span class="xl-stats" id="xlStats">— filas &nbsp;·&nbsp; — columnas</span>
      </div>

      <!-- Área de contenido: loading / tabla / error -->
      <div id="xlContent">
        <div class="xl-loading" id="xlLoading">
          <div class="spinner"></div>
          <p>Leyendo archivo…</p>
          <span class="sub"><?= htmlspecialchars($fileName) ?></span>
        </div>
      </div>

    </div><!-- /xl-shell -->

  </div>
  <?php endif; ?>

  <!-- SheetJS (XLSX) desde CDN -->
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

  <?php if ($fileExists): ?>
  <script>
    window.FILE_URL = <?= json_encode($fileUrl) ?>;
    window.FILE_EXT = <?= json_encode($activeExt) ?>;
    window.FILE_NAME = <?= json_encode($fileName ?? '') ?>;
  </script>
  <script src="js/excel.js"></script>
  <?php endif; ?>

</div>
  

<script src="js/app.js"></script>
<?php require_once("../index/footer.php") ?>