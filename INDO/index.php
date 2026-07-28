<?php
require_once("../Session/seguridad.php");


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/utils/config.php';
require_once __DIR__ . '/utils/funciones.php';
require_once __DIR__ . '/php/cargaINDO.php';

// Si llega aquí, es supervisor autorizado
require_once(__DIR__ . "/../index/header.php");
?>

<link rel="stylesheet" href="css/estilos.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css" />
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">
    <h5 class="tittlecont">Inventario y Análisis de Riesgos en Operaciones por Puesto Operativo de Trabajo</h5>
    <br>

    <div style="float:left" class="row">
        <div class="col-20">
            <small class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                    class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                    aria-label="Warning:">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                </svg>
                Desde este apartado agrega o elimina datos a tu archivo excel de Inventario y Análisis de Riesgos en
                Operaciones por Puesto Operativo de Trabajo
            </small>
        </div>
    </div>
    <br />
    <br />
    <br />

    <div class="card-body">
        <div class="app-header d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="header-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div>
                    <h6 class="mb-0">Gestión de Riesgos en Tareas Críticas</h6>
                    <?php if (empty($errorCarga)): ?>
                        <small class="text-muted">Puesto de trabajo: <b><?= htmlspecialchars($puesto) ?></b></small>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                    <i class="fa-solid fa-trash-can me-1"></i> Eliminar un registro
                </button>
            </div>
        </div>

        <?php if ($errorCarga): ?>
            <div class="alert alert-danger">No se pudo abrir el Excel: <?= htmlspecialchars($errorCarga) ?></div>
        <?php else: ?>

            <form id="formAgregar">
                <div class="accordion" id="accAspectos">
                    <?php $i = 0;
                    foreach ($CFG['grupos'] as $g):
                        [$titulo, $ini, $fin, $subs] = $g;
                        $i++;
                        $collapseId = "grp$i"; ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $i > 1 ? 'collapsed' : '' ?>" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                                    <?= htmlspecialchars($titulo) ?>
                                </button>
                            </h2>
                            <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $i == 1 ? 'show' : '' ?>"
                                data-bs-parent="#accAspectos">
                                <div class="accordion-body">
                                    <?php if ($subs):
                                        foreach ($subs as $sg):
                                            [$st, $si, $sfin] = $sg; ?>
                                            <div class="subgrupo-title"><?= htmlspecialchars($st) ?></div>
                                            <div class="row">
                                                <?php foreach (cols_rango($si, $sfin) as $col)
                                                    echo render_campo($col); ?>
                                            </div>
                                        <?php endforeach; else: ?>
                                        <div class="row">
                                            <?php foreach (cols_rango($ini, $fin) as $col)
                                                echo render_campo($col); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end my-4">
                    <button type="button" id="btnAgregar" class="btn btn-success">
                        <i class="fa-solid fa-circle-plus me-1"></i> Agregar registro
                    </button>
                </div>
            </form>

        <?php endif; ?>
    </div>

    <!-- Modal eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-trash-can me-2"></i>Eliminar registro por # o Folio -
                        ID POE</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" id="buscarNum" class="form-control"
                            placeholder="Ingresa el numero de identificador">
                    </div>

                    <div class="table-responsive" style="max-height:55vh">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Folio</th>
                                    <th>Tarea</th>
                                    <th>Tipo</th>
                                    <th>eliminar</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyRegistros">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Cargando información...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/app.js"></script>