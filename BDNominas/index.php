<?php
require_once(__DIR__ . "/../Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location:../index/index.php");
    exit;
}

$ibmPermitidos = [60040, 58998, 22622, 51947, 53224, 55268];
if (!isset($_SESSION['ibm']) || !in_array($_SESSION['ibm'], $ibmPermitidos)) {
    header("Location:../index/index.php");
    exit;
}

require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/./php/uploadNominas.php");
require_once(__DIR__ . "/../index/header.php");
?>

<link rel="stylesheet" href="css/estilos.css">
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">
    <h5 class="tittlecont">Actualización de archivo BD Nóminas</h5>
    <div style="float:right" class="ayudaSupervisor">
        <button id="btnAyuda" class="btn btn-info">
            <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
        </button>
    </div>
    <br />
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
                             0-2z"/>
                </svg>                
                Desde esta sección actualiza el archivo de BD Nóminas, recuerda que debe estar en formato '.csv'
            </small>
        </div>
        <br><br>
        <div>
            <h4 class="mb-0 fw-bold"> Módulo BD Nóminas </h4>
            <p class="mb-0">Última actualización archivo nóminas: <?= $csvNominasFecha ? $csvNominasFecha : 'Ninguna' ?></p>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="upload-alert alert alert-<?= $tipo_msg ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="fa-solid fa-file-csv text-success me-2"></i>
            Estado del archivo BD Nóminas:
        </div>
        <div class="card-body">
            <?php if ($csvNominasExiste): ?>
                <p><code>Archivo:</code> <strong><?= basename(CSV_NOMINAS_FILE) ?></strong></p>
                <p><code>Número de registros:</code> <strong><?= $csvNominasLineas ?></strong></p>
                <p><code>Última actualización:</code> <strong><?= $csvNominasFecha ?></strong></p>
            <?php else: ?>
                <div class="alert alert-primary">No hay archivo de nóminas cargado actualmente.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="alert alert-warning small py-2">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <strong>Nota:</strong> El sistema no acepta archivos '.xlsx'. Debes convertirlos a '.csv' antes de subirlos.
    </div>

    <form id="formNominas" method="POST" enctype="multipart/form-data" action="./php/uploadNominas.php" class="card-body">
        <div class="upload-zone zonaArchivoNominas mb-3" onclick="document.getElementById('archivoNominas').click()">
            <i class="bi bi-file-earmark-arrow-up fs-1 text-primary mb-2"></i>
            <p id="texto-upload-nominas" class="fw-semibold"><strong>Haz click para seleccionar el archivo de BD Nóminas</strong></p>
            <small class="text-muted">Solo se permiten archivos '.CSV'. Tamaño máximo: 5MB.</small>
        </div>
        <input type="file" name="archivoNominas" id="archivoNominas" class="form-control d-none" accept=".csv"/>
        <div id="nombre-archivo-nominas" class="text-muted small mb-3 text-center"></div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-upload me-2"></i> Subir archivo BD Nóminas
        </button>
    </form>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="./js/uploadNominas.js"></script>
