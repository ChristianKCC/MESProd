<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
require_once("./php/vacacionesLogistica.php");

$tipoSolicitud = $_POST["tipo"] ?? ($_SESSION['solicitud']['tipo'] ?? $_SESSION['tipo']);
if ($tipoSolicitud === "Adelanto"){
    $diasDisponibles = $_POST["dias"] ?? ($_SESSION['solicitud']['limite_dias'] ?? $_SESSION['dias']);
}
?>

<link rel="stylesheet" href="css/estilosConsulta.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DRIVER JS -->
<link rel="stylesheet" href="css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="p-4">
  <div style="float:right" class="d-flex justify-content-end p-4">
      <button id="btnAyuda" class="btn btn-info ayudaEmpleado">
          <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
      </button>
  </div>

  <div style="float:left" class="row">
      <div class="px-4">    
          <small class="alert alert-primary">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              width="24" 
              height="24" 
              fill="currentColor" 
              class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" 
              viewBox="0 0 16 16" 
              role="img" 
              aria-label="Warning:">
              <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
          </svg>
          Desde esta sección marca los días de vacaciones deseados haciendo clic en la fecha de inicio y arrastra con el <code>MOUSE</code> hasta el dia de finalización de tus vacaciones, cuando finalices da clic en 'Continuar con el proceso'.
          </small>          
      </div>
  </div>
  <br />
  <br />

  <div  style="max-width: 1200px; margin: 20px auto;">
    <div class="row ">
      <div class="col-md-2">
          <div class="card">
            <div class="card-header bg-white fw-semibold py-3">
              <i class="fa-solid fa-id-card text-primary me-2"></i> TU INFORMACIÓN
          </div>

            <div class="card-body">
                <?php if ($empleado): ?>
                    <p><strong>Nombre:</strong> <code><?= col($empleado, COL_NOMBRE) ?></code></p>
                    <p><strong>IBM:</strong> <code><?= col($empleado, COL_IBM) ?></code></p>
                    <p><strong>Días disponibles:</strong> <code><?= $diasDisponibles ?> </code></p>
                    <?php 
                        $tipo = col($empleado, COL_TIPO);

                        if ($tipo === "EMPL") {
                            echo '<p><strong>Tipo de empleado:</strong> <code>Empleado</code></p>';
                        } elseif ($tipo === "SIND") {
                            echo '<p><strong>Tipo de empleado:</strong> <code>Sindicalizado</code></p>';
                        }
                    ?>
                    <p><strong>Tipo de solicitud:</strong> <code><?= $tipoSolicitud ?> </code></p>
                    
                <?php else: ?>
                    <div>
                        El sistema de vacaciones no está disponible en este momento.
                    </div>
                <?php endif; ?>
            </div>

          </div>
        </div>

        <div class="col-md-7">
          <div class="card mb-2">
            <div class="card-header bg-white fw-semibold py-3">
              <i class="fa-solid fa-calendar-day text-primary me-2"></i> SOLICITA TUS VACACIONES
            </div>
            <div class="card-body">
              <div id="calendar"></div>

              <div class="mt-3">
                <p>Días seleccionados: <span id="dias-seleccionados">0</span></p>
              </div>

              <div class="d-flex justify-content-between gap-3 mt-4">
                <!-- 
                  <form action="registrarSolicitud.php" method="POST">
                  <input type="hidden" name="dias" id="dias-input">
                  <input type="hidden" name="fecha_de" id="fecha-de">
                  <input type="hidden" name="fecha_a" id="fecha-a">
                  
                  
                  <button type="submit" id="btn-solicitar" class="btn btn-warning rounded-3">
                      <i class="fa-solid fa-calendar-plus"></i> Solicitar vacaciones
                  </button>
                  
                  <button type="submit" id="btn-continuar" class="btn btn-warning rounded-3">
                      <i class="fa-solid fa-calendar-plus"></i> Continuar con el proceso
                  </button>

                  </form>
                  -->

                <!-- <form action="finalizarSolicitud.php" method="POST">
                    <input type="hidden" name="nombre" value="<?= col($empleado, COL_NOMBRE) ?>">
                    <input type="hidden" name="ibm" value="<?= col($empleado, COL_IBM) ?>">
                    <input type="hidden" name="limite_dias" value="<?= $diasDisponibles ?>">
                    <input type="hidden" name="tipo_empleado" value="<?= col($empleado, COL_TIPO) ?>">
                    <input type="hidden" name="fecha_ingreso" value="<?= col($empleado, COL_FINGRESO) ?>">
                    <input type="hidden" name="origen" value="solicitarJE.php">

                    <input type="hidden" name="dias" id="dias-input">
                    <input type="hidden" name="fecha_de" id="fecha-de">
                    <input type="hidden" name="fecha_a" id="fecha-a">
                    <input type="hidden" name="dias_festivos" id="dias-festivos">

                    <button type="submit" id="btn-continuar" class="btn btn-warning rounded-3">
                      <i class="fa-solid fa-calendar-plus"></i> Continuar con el proceso
                    </button>
                </form> -->

                <form id="formSolicitud" action="finalizarSolicitud.php" method="POST">
                  <input type="hidden" name="nombre" id="nombre" value="<?= col($empleado, COL_NOMBRE) ?>">
                  <input type="hidden" name="ibm" id="ibm" value="<?= col($empleado, COL_IBM) ?>">
                  <input type="hidden" name="limite_dias" id="limite-dias" value="<?= $diasDisponibles ?>">
                  <input type="hidden" name="tipo_empleado" id="tipo-empleado" value="<?= col($empleado, COL_TIPO) ?>">
                  <input type="hidden" name="fecha_ingreso" id="fecha-ingreso" value="<?= col($empleado, COL_FINGRESO) ?>">                                    
                  <input type="hidden" name="origen" id="origen" value="solicitarJE.php">

                  <input type="hidden" name="tipo" id="tipo" value="<?= $tipoSolicitud ?>">

                  <input type="hidden" name="dias" id="dias-input">
                  <input type="hidden" name="fecha_de" id="fecha-de">
                  <input type="hidden" name="fecha_a" id="fecha-a">
                  <input type="hidden" name="dias_festivos" id="dias-festivos">

                  <div class="d-flex gap-3">
                      <button type="submit" id="btn-continuar" class="btn btn-warning rounded-3">
                          <i class="fa-solid fa-arrow-right"></i> Continuar con el proceso
                      </button>

                      <button type="button" id="btn-unselect" class="btn btn-primary rounded-3">
                          <i class="fa-solid fa-arrow-rotate-right"></i> Quitar la selección
                      </button>
                  </div>
                </form> 

                <form action="Consulta.php" method="POST">                
                  <button type="submit" id="btn-regresar" class="btn btn-danger rounded-3">
                    <i class="fa-solid fa-arrow-rotate-left"></i> Regresar a Mis Vacaciones
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-2">
          <div class="card">
            <div class="card-header bg-white fw-semibold py-3">
              <i class="fa-solid fa-circle-info text-primary me-2"></i> SIGNIFICADO SEGUN COLOR DEL DIA
            </div>
            <div class="card-body">
              <div class="d-flex flex-column gap-3">
                  <div class="d-flex align-items-center">
                      <div style="width:20px; height:20px; background-color:#fff3cd; border:1px solid #000000;" class="me-2"></div>
                      <span>Dia actual</span>
                  </div>  
                  <div class="d-flex align-items-center">
                  <div style="width:20px; height:20px; background-color:#cdd9ff; border:1px solid #000000;" class="me-2"></div>
                  <span>Pendiente</span>
                </div>
                <div class="d-flex align-items-center">
                  <div style="width:20px; height:20px; background-color:#d4edda; border:1px solid #000000;" class="me-2"></div>
                  <span>Aprobado</span>
                </div>
                <div class="d-flex align-items-center">
                  <div style="width:20px; height:20px; background-color:#ff6666; border:1px solid #000000;" class="me-2"></div>
                  <span>Dias Festivos</span>
                </div>
                <div class="d-flex align-items-center">
                  <div style="width:20px; height:20px; background-color:#5f5d5d6e; border:1px solid #000000;" class="me-2"></div>
                  <span>No disponibles</span>
                </div>              
                <div class="d-flex align-items-center">
                  <span>Los dias 'Pendiente' o 'Aprobado' no podran volverse a seleccionar a menos que sean rechazados.</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/locales-all.global.min.js"></script>

<script type="module" src="./js/solicitar.js"></script>
<script>
    const eventosBloqueados = <?= json_encode($eventosBloqueados) ?>;
</script>

<?php require_once("../index/footer.php") ?>
