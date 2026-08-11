<?php
require_once("../Session/seguridad.php");
require_once(__DIR__ . "/../Vacaciones/php/vacacionesLogistica.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// IBM del usuario en sesión
$ibmSesion = $_SESSION["ibm"] ?? null;

// Obtener lista de supervisores
$listaSupervisores = obtenerSupervisoresIBM();
$ibmPermitidos = [60040, 58998, 22622, 51947, 55268, 53224, 55075, 53412, 27825, 30950, 59610, 55075, 28342];

// Validar acceso
if (!$ibmSesion || (!in_array($ibmSesion, $listaSupervisores) && !in_array($ibmSesion, $ibmPermitidos))) {
    // No está autorizado → redirigir
    header("Location:../index/index.php");
    exit;
}

// Si llega aquí, es supervisor autorizado
require_once(__DIR__ . "/../index/header.php");
?>


<link rel="stylesheet" href="css/estilosNav.css">
<!-- DRIVER JS -->
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css" />
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">
    <h5 class="tittlecont">Delegación de responsabilidades</h5>
    <br />

    <div style="float:left" class="row">
        <div class="col-20">
            <small class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                    class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                    aria-label="Warning:">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                </svg>
                Al delegar, <strong>TODAS TUS REPONSABILIDADES:</strong> Vacaciones, Horas Extra y
                Cambios de puesto serán autorizadas por la persona que elijas,
                <strong>SOLO DURANTE EL PERIODO INDICADO</strong>. Al terminar,
                las autorizaciones regresan automáticamente a ti.

            </small>
        </div>
    </div>
    <br />
    <br />
    <br />

    <div class="card card-body">
        <div class="row">
            <input type="hidden" id="ibmDelegante" value="<?= htmlspecialchars($ibm) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Delegar a (IBM)</label>
                    <input type="text" id="ibmDelegado" class="form-control" inputmode="numeric"
                        placeholder="Ej. 58998">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" id="fechaInicio" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" id="fechaFin" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Comentario (opcional)</label>
                    <input type="text" id="comentario" class="form-control" maxlength="255"
                        placeholder="Motivo, viaje, etc.">
                </div>
            </div>
            <div id="formMsg" class="mt-3"></div>
            <div class="text-end mt-3">
                <button id="btnDelegar" class="btn btn-primary"><i class="fa-solid fa-rotate"></i> Delegar</button>
            </div>


            <ul class="nav nav-tabs p-3" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabMias" type="button">
                        <i class="fa-solid fa-user"></i> <i class="fa-solid fa-arrow-right-long"></i> Mis
                        delegaciones
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabRecibidas" type="button">
                        <i class="fa-solid fa-user"></i> <i class="fa-solid fa-arrow-rotate-left"></i> Delegadas a mí
                        <span id="cntRecibidas" class="badge bg-secondary ms-1 d-none">0</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHistorial" type="button">
                        <i class="fa-solid fa-user"></i>
                        <i class="fa-solid fa-clock-rotate-left"></i> Mis delegaciones a otros (Historial)
                    </button>
                </li>
            </ul>

            <div class="tab-content p-3">
                <div class="tab-pane fade show active" id="tabMias">
                    <div id="listaDelegaciones" class="row g-3">
                        <div class="text-muted small">Cargando…</div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tabRecibidas">
                    <div id="listaRecibidas" class="row g-3">
                        <div class="text-muted small">Cargando…</div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tabHistorial">
                    <div id="listaHistorial" class="row g-3">
                        <div class="text-muted small">Cargando…</div>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal fade" id="modalConfirmar" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirmar delegación</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="modalResumen"></div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" id="btnConfirmar">Sí, delegar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<?php require_once("../index/footer.php") ?>

<script src="js/delegaciones.js"></script>