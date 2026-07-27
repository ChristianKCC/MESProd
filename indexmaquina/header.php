<?php require_once("../Session/seguridad.php"); ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" href="../assets/bootstrap5/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../css/config.css">
  <link rel="stylesheet" type="text/css" href="../assets/awesome5/css/all.css">
  <link rel="stylesheet" type="text/css" href="../assets/sweetalert2.min.css">
  <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
  <title>Manufacturing Execution System</title>
</head>

<body>
  <nav class="navbar navbar-dark navbar-expand-lg mb-2">
    <div class="container-fluid">
      <a href="#" class="tittle" onclick="showSection('sectionplaticas')">
        <h5><img class="" src="../../img/mes.gif">Kimberly Clark de México </h5><small class="subtittle">Producción</small>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
           <button class=" m-1 btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ModalPlanProduccion" id="btnPlanProducc"><i class="fa-solid fa-calendar"></i> Plan Producción</button>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" tabindex="-1" aria-disabled="true">
              Bitacora
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="#" onclick="showSection('pagina1')">Presentaciones</a></li>
              <li><a class="dropdown-item" href="#" onclick="showSection('pagina1telas')">Telas</a></li>
              <li><a class="dropdown-item" href="#" onclick="showSection('pagina2')">Control de tiempos</a></li>
              <li><a class="dropdown-item" href="#" onclick="showSection('sectionctrolTiempos')">Paros automáticos</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" tabindex="-1" aria-disabled="true">
              Calidad
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="#" onclick="showSection('sectiontrazabilidad')">Trazabilidad</a></li>
              <li><a class="dropdown-item" href="#" onclick="showSection('sectionnoconformidad')">No Conformidad</a></li>
              <li class="nav-item">
                <a class="dropdown-item" href="#" onclick="showSection('sectionPesosPanal')" id="btnPesoPanal">Registro COV</a>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" tabindex="-1" aria-disabled="true">
              Seguridad
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="#" onclick="showSection('sectionplaticas')">Platicas de 5 minutos</a></li>
              <li><a class="dropdown-item" href="#" onclick="showSection('sectionepp')">EPP</a></li>
              <li><a class="dropdown-item" href="#" onclick="showSection('insp1')">Pre-usos </a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link" onclick="showSection('sectionvales')" href="#" id="btnvales" tabindex="-1" aria-disabled="true">Vales</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" onclick="showSection('sectionreporte')" href="#" id="btnreporte" tabindex="-1" aria-disabled="true">Reporte</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-bold headermaq" href="#" tabindex="-1" aria-disabled="true"> Maquina: <?php echo $_SESSION['usuario'] ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-bold headermaq" href="#" tabindex="-1" aria-disabled="true"> Turno: <span id="turnoenctext"></span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-bold headermaq" href="#" tabindex="-1" aria-disabled="true"> Folio: <span id="folioenctext"></span></a>
            <input type="text" id="folio" class="form-control form-control-sm" readonly hidden />
          </li>
          <li class="nav-item">
           <button class=" m-1 btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#ModalTurno"><i class="fa-solid fa-backward-step"></i> Seleccionar Turno</button>
          </li>
          <li class="nav-item">
           <button class=" m-1 btn btn-sm btn-danger" id="cerrarturno"><i class="fas fa-external-link-square-alt"></i> Ir Turno Actual</button>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">Hora: <span id="fecha"></span> <span class=" fw-bold text-danger" id="msjturno"></span></a>
          </li>

        </ul>
        <span class="navbar-text">
          <a class="nav-link" href="../Session/salir.php" tabindex="-1" aria-disabled="true">Salir</a>
        </span>
      </div>
    </div>
  </nav>
      <!-- Button trigger modal -->


<!-- Modal -->
<div class="modal fade" id="ModalTurno" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Seleccionar Turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col">
            <small>Fecha</small>
            <input type="date" class="form-control" id="fechaturnocambio">
            
            <small>Turno</small>
            <select class="form-control" id="turnocambio">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            </select>
            <!-- <small>Usuario (IBM)</small>
            <input type="number" class="form-control" id="usuarioturnocambio">
            <small>Contraseña</small>
            <input type="password" class="form-control" id="passwordturnocambio"> -->
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="turnoanterior">Ir al turno</button>
      </div>
    </div>
  </div>
</div>