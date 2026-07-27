<?php
require_once("../Session/seguridad.php");
if (is_null($_SESSION["admincursos"])) {
    header("Location:../index/index.php");
    exit;
}

$ibmPermitidos = [60040, 58998, 51947, 22622];

if (!isset($_SESSION['ibm']) || !in_array($_SESSION['ibm'], $ibmPermitidos)) {
    header("Location:../index/index.php");
    exit;
}

require_once("./php/autorizarLogistica.php");
?>

<div class="container p-4" style="max-width: 1500px; ">
    <h5 class="tittlecont">Autorización de vacaciones</h5>
    <div style="float-left" class="row">
        <div class="col-20">
            <small class="alert alert-info" style= "float:left">
                    <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        width="16" 
                        height="16" 
                        fill="currentColor" 
                        class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" 
                        viewBox="0 0 16 16" 
                        role="img" 
                        aria-label="Warning:">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    Desde esta vista aprueba/rechaza las vacaciones solicitadas
            </small>
        </div>
    </div>

    <div class="fw-semibold py-3">
        Filtros de busqueda:
    </div>

    <form method="GET" class="row g-4 mb-2">
        <div class="col-md-3">
            <input type="text" name="ibm" class="form-control" placeholder="Filtrar por IBM" value="<?= htmlspecialchars($_GET['ibm'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="nombre" class="form-control" placeholder="Filtrar por nombre" value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($_GET['fecha'] ?? '') ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill rounded-3">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>
            <a href="autorizar.php" class="btn btn-secondary flex-fill rounded-3">
                <i class="fa-solid fa-eraser"></i> Limpiar
            </a>
        </div>
    </form>
    
    <div class="fw-semibold py-3">
        Estado general de solicitudes : 
        <span class="badge bg-warning text-dark me-2">Pendientes: <?= $pendientes ?></span>
        <span class="badge bg-success me-2">Aprobadas: <?= $aprobadas ?></span>
        <span class="badge bg-danger">Rechazadas: <?= $rechazadas ?></span>
    </div>

    <?php if (empty($solicitudesFiltradas)): ?>
    <table class="table table-bordered table-sm">
        <thead class="table-dark">
            <tr>
                <th>IBM</th>
                <th>Nombre</th>
                <th>Días solicitados</th>
                <th>Fecha solicitud</th>
                <th>De</th>
                <th>A</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8" class="text-center text-muted">
                    No hay registros de solicitudes.
                </td>
            </tr>
        </tbody>
    </table>
    <?php else: ?>
        <table class="table table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>IBM</th>
                    <th>Nombre</th>
                    <th>Días solicitados</th>
                    <th>Fecha solicitud</th>
                    <th>De</th>
                    <th>A</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($solicitudesFiltradas)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No se encontraron solicitudes con los filtros aplicados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($solicitudesFiltradas as $solicitud): ?>
                        <?php 
                            $estatus = trim($solicitud[7]);
                            $badgeClass = "bg-secondary";
                            if ($estatus === "Pendiente") $badgeClass = "bg-warning text-dark";
                            elseif ($estatus === "Aprobado") $badgeClass = "bg-success";
                            elseif ($estatus === "Rechazado") $badgeClass = "bg-danger";
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($solicitud[0]) ?></td>
                            <td><?= htmlspecialchars($solicitud[1]) ?></td>
                            <td><?= htmlspecialchars($solicitud[6]) ?></td>
                            <td><?= htmlspecialchars($solicitud[3]) ?></td>
                            <td><?= htmlspecialchars($solicitud[4]) ?></td>
                            <td><?= htmlspecialchars($solicitud[5]) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= $estatus ?></span></td>
                            <td>
                                <?php if ($estatus === "Pendiente"): ?>
                                    <form action="procesarAutorizacion.php" method="POST" class="d-inline">
                                        <input type="hidden" name="ibm" value="<?= htmlspecialchars($solicitud[0]) ?>">
                                        <input type="hidden" name="fecha_de" value="<?= htmlspecialchars($solicitud[4]) ?>">
                                        <input type="hidden" name="fecha_a" value="<?= htmlspecialchars($solicitud[5]) ?>">
                                        <button type="submit" name="accion" value="Aprobado" class="btn btn-success btn-sm">
                                            <i class="fa-solid fa-check"></i> Aprobar
                                        </button>
                                        <button type="submit" name="accion" value="Rechazado" class="btn btn-danger btn-sm">
                                            <i class="fa-solid fa-xmark"></i> Rechazar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Ya procesada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once("../index/footer.php"); ?>