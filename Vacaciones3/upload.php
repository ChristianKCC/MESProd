<?php
require_once(__DIR__ . "/../Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location:../index/index.php");
    exit;
}
$ibmPermitidos = [60040, 58998, 51947, 22622];

if (!isset($_SESSION['ibm']) || !in_array($_SESSION['ibm'], $ibmPermitidos)) {
    header("Location:../index/index.php");
    exit;
}

require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/./php/uploadLogistica.php");
require_once(__DIR__ . "/../index/header.php");
?>

<link rel="stylesheet" href="css/estilos.css">

<!-- DRIVER JS -->
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">
    <h5 class="tittlecont">Actualización de archivo de vacaciones</h5>
    <div style="float:right" class=" ayudaSupervisor">
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
                Desde esta sección actualiza tus archivos, recuerda que deben ser en formato '.csv'
            </small>
        </div>

        <br><br>
        <div>
            <h4 class="mb-0 fw-bold"> Módulo de vacaciones </h4>
            <p class="mb-0">Última actualización archivo vacaciones: <?= $csvFecha ? $csvFecha : 'Ninguna' ?></p>
            <p class="mb-0">Última actualización archivo relacional: <?= $csvSindFecha ? $csvSindFecha : 'Ninguna' ?></p>               
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="upload-alert alert alert-<?= $tipo_msg ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold ">
            <i class="fa-solid fa-file-csv text-success me-2"></i>
            Estado del archivo de vacaciones:
        </div>
        <div class="card-body">
            <?php if ($cvsExiste): ?>
                <p class="mb-0"><code>Archivo:</code> <strong><?= basename(CSV_FILE) ?></strong></p>
                <p class="mb-0"><code>Número de registros:</code> <strong><?= $csvLineas ?></strong></p>
                <p class="mb-0"><code>Última actualización:</code> <strong><?= $csvFecha ?></strong></p>
            <?php else: ?>
                <div class="alert alert-primary mb-0">
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
                    No hay archivo cargado actualmente.
                </div>
            <?php endif; ?>

            <br />
            <?php if ($cvsSindExiste): ?>
                <p class="mb-0"><code>Archivo:</code> <strong><?= basename(CSV_FILE_SIND) ?></strong></p>
                <p class="mb-0"><code>Número de registros:</code> <strong><?= $csvSindLineas ?></strong></p>
                <p class="mb-0"><code>Última actualización:</code> <strong><?= $csvSindFecha ?></strong></p>
            <?php else: ?>
                <div class="alert alert-primary mb-0">
                    No hay archivo de sindicalizados cargado actualmente.
                </div>
            <?php endif; ?>

        </div>        
    </div>

    <br><br>
    <div class="alert alert-warning small py-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                aria-label="Warning:">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 
                        13.233c-.457.778.091 1.767.98 
                        1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 
                        1.566zM8 5c.535 0 .954.462.9.995l-.35 
                        3.507a.552.552 0 0 1-1.1 0L7.1 
                        5.995A.905.905 0 0 1 8 5zm.002 
                        6a1 1 0 1 1 0 2 1 1 0 0 1 
                        0-2z"/>
        </svg>
        <strong>Nota para XLSX:</strong>
        Si intentas subir un Excel con formato '.xlsx', el sistema no lo guardará, por tanto necesitarás convertirlo a '.CSV' para que el sistema pueda leerlo.
        <em>Archivo -> Guardar como -> .CSV</em>
    </div>

    <div>
        <form id="form" name="form" method="POST" enctype="multipart/form-data" action="upload.php" class="card-body">                        
            <div class="upload-zone zonaArchivoUno mb-3" onclick="document.getElementById('archivo').click()">
                <i class="bi bi-file-earmark-arrow-up fs-1 text-primary mb-2"></i>
                <p id="texto-upload" class="mb-1 fw-semibold nombre-archivo-Uno"><strong>Haz click para seleccionar el archivo de vacaciones de empleados</strong></p>
                <small class="text-muted">Solo se permiten archivos '.CSV'. Tamaño máximo: 5MB.</small>
            </div>
            <input 
                type="file" 
                name="archivo" 
                id="archivo" 
                class="form-control d-none archivoUno" 
                accept=".csv"
            />
            <div id="nombre-archivo" name="nombre-archivo" class="text-muted small mb-3 text-center nombre-archivo"></div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-upload me-2"></i> Subir archivo de vacaciones
            </button>
        </form>

        <form id="formSind" name="formSind" method="POST" enctype="multipart/form-data" action="upload.php" class="card-body">
            <input type="hidden" name="tipo" value="sind">
            <div class="upload-zone zonaArchivoDos mb-3" onclick="document.getElementById('archivoSind').click()">
                <i class="bi bi-file-earmark-arrow-up fs-1 text-primary mb-2"></i>
                <p id="texto-upload-sind" class="mb-1 fw-semibold"><strong>Haz click para seleccionar el archivo de relación de sindicalizados con supervisores</strong></p>
                <small class="text-muted">Solo se permiten archivos '.CSV'. Tamaño máximo: 5MB.</small>
            </div>
            <input 
                type="file" 
                name="archivo" 
                id="archivoSind" 
                class="form-control d-none" 
                accept=".csv" 
            />
            <div id="nombre-archivo-sind" class="text-muted small mb-3 text-center"></div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-upload me-2"></i> Subir archivo de sindicalizados
            </button>
        </form>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="./js/upload.js"></script>