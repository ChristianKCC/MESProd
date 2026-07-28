<?php
// require_once(dirname (__DIR__, 1) . "/Session/seguridad.php");
// if (is_null($_SESSION["admincursos"])) {
//     header("Location: ../index/index.php");
//     exit;
// }
require_once(__DIR__ . "/PDT/VerPDT/LogicaVerPDT.php");
require_once(__DIR__ . "/../index/header.php");
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/excelstyle.css">
  <link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css" />
  <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
</head>

<body>

  <div class="container p-4">
    <h5 class="tittlecont">Consulta de datos 'Puestos De Trabajo'</h5>
    <br />

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
                             0-2z" />
          </svg>
          Desde esta sección consulta el archivo de 'Puestos De Trabajo'
        </small>
      </div>
    </div>
    <br />

    <?php if (!$fileExists): ?>
      <!-- ══ SIN ENLACE ══ -->
      <div class="emc-page">
        <div class="emc-empty">
          <span class="empty-icon">📋</span>
          <h2>No hay datos disponibles</h2>
          <p>Vuelve a intentarlo más tarde para consultarlo aquí.</p>
        </div>
      </div>
    <?php else: ?>
      <!-- ══ VISOR EMBEBIDO ══ -->
      <div class="emc-page-wide">
        <div class="xl-shell">

          <!-- Toolbar -->
          <div class="xl-toolbar">
            <div class="xl-title-area">
              <i class="bi bi-file-earmark-excel-fill" style="font-size:1.15rem;color:#229A54"></i>
              <div>
                <div class="xl-fname"><?= htmlspecialchars($fileName) ?>
                </div>
                <div class="xl-fmeta"><?= $fileUpdated ?><?= $fileSize ? ' &nbsp;·&nbsp; ' . $fileSize : '' ?>
                  &nbsp;·&nbsp;
                  <span class="anim-badge">
                    <i class="bi bi-stars"></i> Interactua con el archivo
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!--
            El src del iframe ahora es DINAMICO: viene de tblMXPREnlaceEMC
            (enlace recortado) + los parametros fijos de funciones_enlace.php
          -->
          <div class="xl-iframe-wrap">
            <iframe src="<?= htmlspecialchars($enlaceEmbed) ?>" class="xl-iframe" frameborder="0" scrolling="no">
            </iframe>
          </div>

        </div><!-- /xl-shell -->
      </div>
    <?php endif; ?>
  </div>

  <!-- Bloquear clic derecho y teclas de descarga -->
  <script>
    document.addEventListener('contextmenu', function (e) {
      e.preventDefault();
      return false;
    });

    document.addEventListener('keydown', function (e) {
      if (e.ctrlKey && ['s', 'p', 'u'].includes(e.key.toLowerCase())) {
        e.preventDefault();
        return false;
      }
    });
  </script>

  <script src="js/app.js"></script>
  <?php require_once("../index/footer.php") ?>