<?php
require_once(__DIR__ . "/../Session/seguridad.php");
require_once(__DIR__ . "/../Cambiopuesto/php/guard.php");
require_once(__DIR__ . "/../Vacaciones/php/guard.php");
require_once(__DIR__ . "/../Tiempoextra/php/guard.php");

$baseUrl = "..";
?>


<!DOCTYPE html>
<html lang="es-mx">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- <link rel="stylesheet" type="text/css" href="../assets/bootstrap5/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../css/config.css">
  <link rel="stylesheet" type="text/css" href="../css/MenuDes.css">
  <link rel="stylesheet" type="text/css" href="../assets/awesome5/css/all.css"> -->

  <link rel="stylesheet" href="<?php echo $baseUrl ?>/assets/bootstrap5/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo $baseUrl ?>/css/config.css">
  <link rel="stylesheet" href="<?php echo $baseUrl ?>/css/MenuDes.css">
  <link rel="stylesheet" href="<?php echo $baseUrl ?>/assets/awesome5/css/all.css">

  <script src="<?php echo $baseUrl ?>/assets/axios.min.js"></script>
  

  <link rel="shortcut icon" href="<?php echo $baseUrl ?>/img/favicon.ico" type="image/x-icon">


  <!-- <script src="../assets/axios.min.js"></script>
  <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">    -->


  <title>Manufacturing Execution System</title>
</head>

<body>
  <header>
    <nav class="navbar navbar-dark navbar-expand-xxl mb-1">
      <div class="container-fluid">
        <a class="tittle" href="../index/index.php">
          <h5><img class="" src="../../img/mes.gif"> Kimberly Clark de México </h5><small class="subtittle">Planta Cuautitlán</small>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-lg-0">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Seguridad
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="../AOPOE/index">Cap IT/OPT</a></li>
                <li><a class="dropdown-item" href="../AOPOE/reporte.php">Reporte IT/OPT</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../proact/proact">PROACTivo</a></li>
                <li><a class="dropdown-item" href="../proact/reporte.php">Reporte PROACTivo</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../IMC/CondicionesIns">IMC</a></li>
                <li><a class="dropdown-item" href="../IMC/Reporteimc.php">Reporte IMC</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../EPP/EPP">EPP</a></li>
                <li><a class="dropdown-item" href="../EPP/ReporteEPP">Reporte EPP</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../CapaSeg/Home">Capa</a></li>
                <li><a class="dropdown-item" href="../capa/autorizacapa">Autoriza Capa</a></li>
                <li><a class="dropdown-item" href="../capa/misacciones">Mis acciones</a></li>
                <li><a class="dropdown-item" href="../capa/capavalidaraccionesefectividad">Efectividad</a></li>
                <li><a class="dropdown-item" href="../capa/capareporte">Reporte</a></li>
              </ul>
            </li>             

            <li class="nav-item">
              <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">Orden y limpieza</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Calidad
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="../Laboratorio/index">Laboratorio</a></li>
                <li><a class="dropdown-item" href="../proactcalidad/obscalidad">Observaciones de calidad</a></li>
                <li><a class="dropdown-item" href="../trazabilidad/ReporteRill">Reporte Rill</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../NoConformidad/ReporteNoC">Reporte de No conformidad</a></li>
                <li><a class="dropdown-item" href="../Laboratorio/Reporte">Reporte Folio Laboratorio</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../produccion/ReporteCalidadMaquinas">Reporte Calidad Máquinas</a>
                </li>
              </ul>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">Manejo de activos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">Materia Prima</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">Costos</a>
            </li>
            
           
            <li class="nav-item dropdown has-megamenu">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"> RI </a>
              <div class="dropdown-menu megamenu" role="menu">
                <div class="row g-3">
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Personal</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../RI/index.php">Empleados</a></li>
                        <li><a class="dropdown-item" href="../Asistencias/Asistencias">Asistencias</a></li>
                        <li><a class="dropdown-item" href="../Asistencias/Descansos">Descansos</a></li>
                        <li><a class="dropdown-item" href="../Asistencias/biostart">Asistencias Externos</a></li>
                        <li><a class="dropdown-item" href="../ReciboNom/EnvRecibo">Recibos de nomina</a></li>
                        <li><a class="dropdown-item" href="../FirmaDigital/firma">Firma Digital</a></li>


                        <?php
                            if (session_status() == PHP_SESSION_NONE)
                              session_start();
                            if (!isset($_SESSION['ibm']) || $_SESSION['ibm'] === 58998 || $_SESSION['ibm'] === 51947 || $_SESSION['ibm'] === 22622 || $_SESSION['ibm'] === 55268 || $_SESSION['ibm'] === 53224) {
                        ?>
                          <li><a class="dropdown-item" href="../BDNominas/index.php">Actualización de Base De Datos Nominas</a></li>
                          <li><a class="dropdown-item" href="../Vacaciones/upload">Actualización de Archivos para Vacaciones</a></li>
                        <?php
                          }
                        ?>

                        <?php
                        if (session_status() == PHP_SESSION_NONE)
                          session_start();
                          if (!isset($_SESSION['ibm']) || $_SESSION['ibm'] === 58998 || $_SESSION['ibm'] === 51947 || $_SESSION['ibm'] === 22622 || $_SESSION['ibm'] === 55268 || $_SESSION['ibm'] === 53224) {
                            ?>
                            <hr class="dropdown-divider">

                            <?php
                            if (session_status() == PHP_SESSION_NONE) session_start();
                            require_once(__DIR__ . "/../Tiempoextra/php/guard.php");
                            $VerificarsesionVac = new VerificarSesionVac();
                            
                            
                            $pendientes = $VerificarsesionVac->contarCorrecciones();
                            ?>                              
                            <li class="position-relative">
                                <a class="dropdown-item d-flex justify-content-between align-items-center" href="../Vacaciones/validarVacaciones">
                                    <span>Validación/Corrección en solicitudes de vacaciones</span>
                                    <?php if($pendientes > 0): ?>
                                        <span class="notif-loader"><?php echo $pendientes; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php                          
                          ?>                          
                          <li><a class="dropdown-item" href="../Tiempoextra/validarTiempoExtra">Validación/Corrección en solicitudes de tiempos extras</a></li>
                            <?php
                        }
                        ?>

                        <hr class="dropdown-divider">
                        <li><a class="dropdown-item"
                            href="../HerramientaAcoso/cuestionarioAcosoLaboral.php">Cuestionario Acoso Laboral</a></li>
                        <?php
                        if (session_status() == PHP_SESSION_NONE)
                          session_start();
                        if (!isset($_SESSION['ibm']) || $_SESSION['ibm'] === 58998 || $_SESSION['ibm'] === 22622 || $_SESSION['ibm'] === 34374) {
                          ?>
                          <li><a class="dropdown-item" href="../HerramientaAcoso/resultadosCuestionario.php"
                              id="resultadosCuestionario">Resultados
                              Cuestionario
                              Acoso Laboral</a></li>
                          <li>
                            <?php
                        }
                        ?>
                        <hr class="dropdown-divider">
                        <!-- Vista para RI -->
                        <!-- ------------------------------------- CONSULTA DE DATOS PARA VACACIONES ------------------------------------- -->
                        <!-- <?php
                        if (session_status() == PHP_SESSION_NONE)
                          session_start();
                        if (!isset($_SESSION['ibm']) || $_SESSION['ibm'] === 58998 || $_SESSION['ibm'] === 22622) {
                          ?>
                          <li><a class="dropdown-item" href="../Vacaciones/firmarvacacionesRI">Firma Relaciones Inds. para solicitudes de vacaciones</a></li>
                            <?php
                        }
                        ?> -->

                        <?php
                        if (session_status() == PHP_SESSION_NONE) session_start();
                        require_once(__DIR__ . "/../Tiempoextra/php/guard.php");
                        $VerificarsesionVac = new VerificarSesionVac();

                        if (!isset($_SESSION['ibm']) || $_SESSION['ibm'] === 58998 || $_SESSION['ibm'] === 22622) {
                            // Llamamos al método que cuenta las firmas pendientes
                            $pendientesFirma = $VerificarsesionVac->contarPorFirma();
                            ?>
                            <li class="position-relative">
                                <a class="dropdown-item d-flex justify-content-between align-items-center" href="../Vacaciones/firmarvacacionesRI">
                                    <span>Firma Relaciones Inds. para solicitudes de vacaciones</span>
                                    <?php if($pendientesFirma > 0): ?>
                                        <span class="notif-loader"><?php echo $pendientesFirma; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php
                        }
                        ?>



                        <li><a class="dropdown-item" href="../Vacaciones/Consulta">Consulta y Solicita tus Vacaciones</a></li>

                        <!-- Vista para Super Intendente -->
                        <!-- <?php
                        if (session_status() == PHP_SESSION_NONE)
                        session_start();
                        require_once(__DIR__ . "/../Vacaciones/php/guard.php");
                        $VerificarsesionVac = new VerificarSesionVac();
                        if ($VerificarsesionVac->puedeVerVacacionesSupInt()) {
                        ?>
                          <li><a class="dropdown-item" href="../Vacaciones/autorizaSupIntendente">Pre-Autoriza/Rechaza Solicitudes de Vacaciones</a></li>
                        <?php
                          }
                        ?> -->


                        <?php
                          if (session_status() == PHP_SESSION_NONE) session_start();
                          require_once(__DIR__ . "/../Tiempoextra/php/guard.php");
                          $VerificarsesionVac = new VerificarSesionVac();

                          if ($VerificarsesionVac->puedeVerVacacionesSupInt()) {
                              $pendientes = $VerificarsesionVac->contarVacacionesPendientesSupInt();
                              ?>
                              <li class="position-relative">
                                  <a class="dropdown-item d-flex justify-content-between align-items-center" href="../Vacaciones/autorizaSupIntendente">
                                      <span>Pre-Autoriza/Rechaza Solicitudes de Vacaciones</span>
                                      <?php if($pendientes > 0): ?>
                                          <span class="notif-loader"><?php echo $pendientes; ?></span>
                                      <?php endif; ?>
                                  </a>
                              </li>
                              <?php
                          }
                        ?>

                        <!-- Vista para Gerente -->
                        <!-- <?php
                        if (session_status() == PHP_SESSION_NONE)
                        session_start();
                        require_once(__DIR__ . "/../Vacaciones/php/guard.php");
                        $VerificarsesionVac = new VerificarSesionVac();
                        if ($VerificarsesionVac->puedeVerVacaciones()) {
                        ?>
                          <li><a class="dropdown-item" href="../Vacaciones/AutorizarVacaciones">Autoriza/Rechaza Solicitudes de Vacaciones</a></li>
                        <?php
                          }
                        ?> -->


                        <?php
                        if (session_status() == PHP_SESSION_NONE) session_start();
                        require_once(__DIR__ . "/../Vacaciones/php/guard.php");
                        $VerificarsesionVac = new VerificarSesionVac();

                        if ($VerificarsesionVac->puedeVerVacaciones()) {
                            // Llamamos a tu función
                            $pendientes = $VerificarsesionVac->contarVacacionesPendientes();
                            ?>
                            <li class="position-relative">
                                <a class="dropdown-item d-flex justify-content-between align-items-center" href="../Vacaciones/AutorizarVacaciones">
                                    <span>Autoriza/Rechaza Solicitudes de Vacaciones</span>
                                    <?php if($pendientes > 0): ?>
                                        <span class="notif-loader"><?php echo $pendientes; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php
                        }
                        ?>



                        <!-- ------------------------------------- CONSULTA DE DATOS PARA TIEMPOS EXTRA ------------------------------------- -->                                                
                         <hr class="dropdown-divider">
                        <?php
                          if (session_status() == PHP_SESSION_NONE) session_start();
                          require_once(__DIR__ . "/../Vacaciones/php/vacacionesLogistica.php");

                          // IBM del usuario en sesión
                          $ibmSesion = $_SESSION["ibm"] ?? null;

                          // Obtener lista de supervisores
                          $listaSupervisores = obtenerSupervisoresIBM();                          

                          // Validar acceso para mostrar el menú
                          if ($ibmSesion && (in_array($ibmSesion, $listaSupervisores))) {
                              ?>                              
                              <li>
                                  <a class="dropdown-item" href="../Tiempoextra/index.php">
                                      Crea Solicitudes de Tiempo Extra
                                  </a>
                              </li>
                              <?php
                          }
                        ?>

                        <!-- <li><a class="dropdown-item" href="../Tiempoextra/index.php">Crea Solicitudes de Tiempo Extra</a></li> -->

                        <!-- <?php
                        if (session_status() == PHP_SESSION_NONE)
                        session_start();
                        require_once(__DIR__ . "/../Tiempoextra/php/guard.php");
                        $VerificarsesionTiempoT = new VerificarSesion();
                        if ($VerificarsesionTiempoT->puedeVerTiemposExtraSupInt()) {
                        ?>
                          <li><a class="dropdown-item" href="../Tiempoextra/autorizaSupIntendente.php">Pre-Autoriza/Rechaza Solicitudes de Tiempos Extra</a></li>
                        <?php
                          }
                        ?> -->


                        <?php
                          if (session_status() == PHP_SESSION_NONE) session_start();
                          require_once(__DIR__ . "/../Tiempoextra/php/guard.php");
                          $VerificarsesionTiempoT = new VerificarSesion();

                          if ($VerificarsesionTiempoT->puedeVerTiemposExtraSupInt()) {
                              $pendientes = $VerificarsesionTiempoT->contarTiemposExtraSupInt();
                              ?>
                              <li class="position-relative">
                                  <a class="dropdown-item d-flex justify-content-between align-items-center" href="../Tiempoextra/autorizaSupIntendente.php">
                                      <span>Pre-Autoriza/Rechaza Solicitudes de Tiempos Extra</span>
                                      <?php if($pendientes > 0): ?>
                                          <span class="notif-loader"><?php echo $pendientes; ?></span>
                                      <?php endif; ?>
                                  </a>
                              </li>
                              <?php
                          }
                        ?>


                        <!-- <?php
                        if (session_status() == PHP_SESSION_NONE)
                        session_start();
                        require_once(__DIR__ . "/../Tiempoextra/php/guard.php");
                        $VerificarsesionTiempoT = new VerificarSesion();
                        if ($VerificarsesionTiempoT->puedeVerTiemposExtra()) {
                        ?>
                          <li><a class="dropdown-item" href="../Tiempoextra/Autorizafol.php">Autoriza/Rechaza Solicitudes de Tiempo Extra</a></li>
                        <?php
                          }
                        ?> -->


                        <?php
                        if (session_status() == PHP_SESSION_NONE) session_start();
                        require_once(__DIR__ . "/../Tiempoextra/php/guard.php");
                        $VerificarsesionTiempoT = new VerificarSesion();

                        if ($VerificarsesionTiempoT->puedeVerTiemposExtra()) {
                            // Llamamos a tu función
                            $pendientes = $VerificarsesionTiempoT->contarTiemposExtra();
                            ?>
                            <li class="position-relative">
                                <a class="dropdown-item d-flex justify-content-between align-items-center" href="../Tiempoextra/Autorizafol.php">
                                    <span>Autoriza/Rechaza Solicitudes de Tiempo Extra</span>
                                    <?php if($pendientes > 0): ?>
                                        <span class="notif-loader"><?php echo $pendientes; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php
                        }
                        ?>

                      

                        <!-- ------------------------------------- CONSULTA DE DATOS PARA CAMBIO TEMPORAL DE TURNO ------------------------------------- -->
                        <!-- <hr class="dropdown-divider"> -->
                        
                        <!-- <li><a class="dropdown-item" href="../CambioTemporalDeTurno/index.php">Crea Cambios Temporales de Turno</a></li> -->                         
                        <?php
                          if (session_status() == PHP_SESSION_NONE) session_start();
                          require_once(__DIR__ . "/../Vacaciones/php/vacacionesLogistica.php");

                          // IBM del usuario en sesión
                          $ibmSesion = $_SESSION["ibm"] ?? null;

                          // Obtener lista de supervisores
                          $listaSupervisores = obtenerSupervisoresIBM();                          

                          // Validar acceso para mostrar el menú
                          if ($ibmSesion && (in_array($ibmSesion, $listaSupervisores))) {
                              ?>                              
                              <hr class="dropdown-divider">
                              <li>
                                <a class="dropdown-item" href="../CambioTemporalDeTurno/index.php">
                                  Crea Cambios Temporales de Turno
                                </a>
                              </li>
                              <?php
                          }
                        ?>
                                                

                        <!-- ------------------------------------- CONSULTA DE DATOS PARA CAMBIOS DE PUESTO ------------------------------------- -->
                        <hr class="dropdown-divider">
                        <!-- <li><a class="dropdown-item" href="../Cambiopuesto/index.php">Crea Solicitudes de Cambio de Puesto</a></li> -->

                        <?php
                          if (session_status() == PHP_SESSION_NONE) session_start();
                          require_once(__DIR__ . "/../Vacaciones/php/vacacionesLogistica.php");

                          // IBM del usuario en sesión
                          $ibmSesion = $_SESSION["ibm"] ?? null;

                          // Obtener lista de supervisores
                          $listaSupervisores = obtenerSupervisoresIBM();                          

                          // Validar acceso para mostrar el menú
                          if ($ibmSesion && (in_array($ibmSesion, $listaSupervisores))) {
                              ?>
                              
                              <li>
                                <a class="dropdown-item" href="../Cambiopuesto/index.php">
                                  Crea Solicitudes de Cambio de Puesto
                                </a>
                              </li>
                              <?php
                          }
                        ?>


                        <!-- <?php
                        if (session_status() == PHP_SESSION_NONE)
                        session_start();
                        require_once(__DIR__ . "/../Cambiopuesto/php/guard.php");
                        $VerificarsesionCambP = new VerificarSesionCambP();
                        if ($VerificarsesionCambP->puedeVerListaSuperIntendente()) {
                        ?>
                          <li><a class="dropdown-item" href="../Cambiopuesto/autorizaSupIntendente.php">Pre-Autoriza/Rechaza Solicitudes de Cambios de Puesto</a></li>
                        <?php
                          }
                        ?> -->


                        <?php
                          if (session_status() == PHP_SESSION_NONE) session_start();
                          require_once(__DIR__ . "/../Cambiopuesto/php/guard.php");
                          $VerificarsesionCambP = new VerificarSesionCambP();

                          if ($VerificarsesionCambP->puedeVerListaSuperIntendente()) {
                              $pendientes = $VerificarsesionCambP->contarCambiosPuestoSupInt();
                              ?>
                              <li class="position-relative">
                                  <a class="dropdown-item d-flex justify-content-between align-items-center" href="../Cambiopuesto/autorizaSupIntendente.php">
                                      <span>Pre-Autoriza/Rechaza Solicitudes de Cambios de Puesto</span>
                                      <?php if($pendientes > 0): ?>
                                          <span class="notif-loader"><?php echo $pendientes; ?></span>
                                      <?php endif; ?>
                                  </a>
                              </li>
                              <?php
                          }
                        ?>


                        <!-- <?php
                        if (session_status() == PHP_SESSION_NONE)
                        session_start();
                        require_once(__DIR__ . "/../Cambiopuesto/php/guard.php");
                        $VerificarsesionCambP = new VerificarSesionCambP();
                        if ($VerificarsesionCambP->puedeVerListaGerente()) {
                        ?>
                          <li><a class="dropdown-item" href="../Cambiopuesto/AutorizaCambio.php">Autoriza/Rechaza Solicitudes de Cambio de Puesto</a></li>
                        <?php
                          }
                        ?> -->

                        <?php
                        if (session_status() == PHP_SESSION_NONE) session_start();
                        require_once(__DIR__ . "/../Cambiopuesto/php/guard.php");
                        $VerificarsesionCambP = new VerificarSesionCambP();

                        if ($VerificarsesionCambP->puedeVerListaGerente()) {
                            // Llamamos a tu función
                            $pendientes = $VerificarsesionCambP->contarCambiosPuesto();
                            ?>
                            <li class="position-relative">
                                <a class="dropdown-item d-flex justify-content-between align-items-center" href="../Cambiopuesto/AutorizaCambio.php">
                                    <span>Autoriza/Rechaza Solicitudes de Cambio de Puesto</span>
                                    <?php if($pendientes > 0): ?>
                                        <span class="notif-loader"><?php echo $pendientes; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Cursos</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../Cursos/crearcurso.php">Crear Curso</a></li>
                        <li><a class="dropdown-item" href="../Cursos/capacitaciones.php">Agregar Capacitación</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title"> Generales</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../Cursos/reportecurso.php">Reporte de Capacitaciones</a>
                        </li>
                        <li><a class="dropdown-item" href="../Cardex/cardexreportecurso.php">Reporte Asistencia por
                            Curso</a></li>
                        <li><a class="dropdown-item" href="../Cardex/cardexreporteemp.php">Reporte Asistencia por
                            Empleado</a></li>
                        <li><a class="dropdown-item" href="../Cardex/cardexreportecardex.php">Cardex por puesto</a></li>
                        <li><a class="dropdown-item" href="../Cursos/Matrizcap.php">Matriz de capacitación</a></li>
                        <li><a class="dropdown-item" href="../Cursos/reportedc3.php">Reporte Dc3</a></li>
                        <li>
                          <hr class="dropdown-divider">
                        </li>

                        <?php
                          if (session_status() == PHP_SESSION_NONE)
                            session_start();
                          if (!isset($_SESSION['ibm']) || $_SESSION['ibm'] === 58998 || $_SESSION['ibm'] === 22622 || $_SESSION['ibm'] === 51947 || $_SESSION['ibm'] === 55268 || $_SESSION['ibm'] === 53224) {
                            ?>
                          <h6 class="title">Reportes Nominas</h6>
                          <li><a class="dropdown-item" href="../Tiempoextra/reporte60hrs.php">Reporte +60.5hrs & Dobletes turno</a>
                          </li>
                          <li><a class="dropdown-item" href="../Tiempoextra/reportegenral">Reporte Tiempo Extra</a>
                          </li>
                          <li><a class="dropdown-item" href="../TiempoExtra/reporteCTT">Reporte Cambio Temporal de Turno</a>
                          </li>
                          <li><a class="dropdown-item" href="../Cambiopuesto/reportegenral">Reporte Cambio de Puesto</a>
                          </li>
                          <li><a class="dropdown-item" href="../Vacaciones/reporteVacaciones">Reporte Vacaciones</a>
                          </li>
                          <li>
                            <hr class="dropdown-divider">
                          </li>
                            <?php
                          }
                        ?>

                        <h6 class="title">Reportes Medicos</h6>
                        <li><a class="dropdown-item" href="../Enfermeria/ReporteConsultas">Reporte Consultas Enfermeria</a></li>
                        <li><a class="dropdown-item" href="../Enfermeria/ReporteExamenMedico">Reporte Examen Medico</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Enfermeria</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../Enfermeria/Consultas">Consultas</a></li>
                        <li><a class="dropdown-item" href="../Enfermeria/Incapacidades">Incapacidades</a></li>
                        <li><a class="dropdown-item" href="../Enfermeria/ExamenMedico">Examen medico</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Productos</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../ValeProductos/generar">Vale de Productos</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Salas de Juntas</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../SalaJuntas/index">Registrar Reunión</a></li>
                        <li><a class="dropdown-item" href="../SalaJuntas/salasReservadas">SalasReservadas</a></li>
                        <li><a class="dropdown-item" href="../SalaJuntas/infoSalasAgendadas">Juntas en salas</a></li>
                        <li><a class="dropdown-item" href="../SalaJuntas/infoJuntas">Información de juntas</a></li>
                        <li><a class="dropdown-item" href="../SalaJuntas/juntasMes">Juntas al mes</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              
            </li>



            <!-- produccion -->

            <li class="nav-item dropdown has-megamenu">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"> Producción </a>
              <div class="dropdown-menu megamenu" role="menu">
                <div class="row g-3">
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Monitor Maquinas</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../produccion/pañal.php">Pañal</a></li>
                        <li><a class="dropdown-item" href="../produccion/inconew.php">Incontinencia</a></li>
                        <li><a class="dropdown-item" href="../produccion/inco.php">Incontinencia Anterior</a></li>
                        <li><a class="dropdown-item" href="../produccion/pf.php">Protección Femenina</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Utilidades</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../platicas/platicas">Cargar platicas</a></li>
                        <li><a class="dropdown-item" href="../ValesE/ValeAutoriza">Vales Electronicos</a></li>
                        <li><a class="dropdown-item" href="../ValesE/Reportevale">Reporte Vales Electronicos</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Reportes</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../bitacora/reporte.php">Reporte paros de maquina</a></li>
                        <li><a class="dropdown-item" href="../bitacora/reporteParosNuevo.php">Reporte paros de
                            automáticos</a>
                        </li>
                        <li><a class="dropdown-item" href="../platicas/reporte.php">Reporte platicas mensual</a></li>
                        <li><a class="dropdown-item" href="../platicas/reporteasistencias.php">Reporte platicas
                            asistencias</a></li>
                        <li><a class="dropdown-item" href="../RechazosAris/index">Reporte Aris</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Producción</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../Reporteproduccion/producciones">Producciones</a></li>
                        <li><a class="dropdown-item" href="../Reporteproduccion/Entregas">Entregas</a></li>
                        <li><a class="dropdown-item" href="../Reporteproduccion/PlanProduccion">Plan Producción</a></li>
                        <li><a class="dropdown-item" href="../Reporteproduccion/reporteproducciones">Reporte de
                            producciones</a></li>
                        <li><a class="dropdown-item" href="../Reporteproduccion/reporteProduccion">Reporte de
                            producción</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Configuracion Bitacora</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../ValesE/Configuracionclaves">Configuración Claves</a></li>
                        <li><a class="dropdown-item" href="../ValesE/Configuracionclases">Configuración Clases</a></li>
                        <li><a class="dropdown-item" href="../ValesE/Configuracionmateriales">Configuración
                            Materiales</a></li>
                        <li><a class="dropdown-item" href="../ValesE/Configuracionconv">Configuración Combinaciones</a>
                        </li>
                      </ul>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../Produccion1/ConfiguracionSecciones">Configuración
                            Secciones</a>
                        </li>
                        <li><a class="dropdown-item" href="../Produccion1/ConfiguracionModulos">Configuración
                            Modulos</a></li>
                        <li><a class="dropdown-item" href="../Produccion1/ConbiSeccModFall">Combinación Secciones y
                            Modulos</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div class="col-lg-4 col-6">
                    <div class="col-megamenu">
                      <h6 class="title">Reporte Diario</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../Reporteproduccion/Reportediario?dep=24">Protección
                            Femenina</a></li>
                        <li><a class="dropdown-item" href="../Reporteproduccion/Reportediario?dep=?25">Incontinencia</a>
                        </li>

                      </ul>
                    </div>
                    <div class="col-megamenu">
                      <h6 class="title">Preusos</h6>
                      <ul class="list-unstyled">
                        <li><a class="dropdown-item" href="../Preusos/ReportePreusos">Reporte
                            Preusos</a></li>

                      </ul>
                    </div>
                  </div>
              </div>
            </li>
            <!-- <li class="nav-item">
              <a class="nav-link" href="../Creador/GeneradorPantallas" tabindex="-1" aria-disabled="true">Creador</a>
            </li> -->
            <!--  -->

          </ul>
          <form>
            <a class="" href="../Session/salir.php">Salir</a>
          </form>
        </div>
      </div>
    </nav>
  </header>
  <div class="contenedor2" id="contenedor2">

  <!-- <script src="../assets/menuDesplegable.js"></script> -->
  <script src="<?php echo $baseUrl ?>/assets/menuDesplegable.js"></script>