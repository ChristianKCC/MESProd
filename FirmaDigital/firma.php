<?php
require_once("../Session/seguridad.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// IBM del usuario en sesión
$ibmSesion = $_SESSION["ibm"] ?? null;

$ibmPermitidos = [60040, 58998, 22622, 51947, 55268, 53224, 50502, 50879, 35025];

// Validar acceso
if (!$ibmSesion || !in_array($ibmSesion, $ibmPermitidos)) {
    header("Location:../index/index.php");
    exit;
}

require_once("../index/header.php");
?>
<link rel="stylesheet" href="css/estiloFirma.css">
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container p-4">
    <h5 class="tittlecont">Captura Digital de Firma</h5>
    <div style="float:right" class="p-1 ayudaSupervisor">
        <button id="btnAyuda" class="btn btn-info">
            <i class="fa-solid fa-circle-question"></i> ¿Quieres ver un tutorial del sistema?
        </button>
    </div>
    
    <br>
    <div style="float:left" class="row">
        <div class="col-20">    
            <small class="alert alert-info">
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
            Desde esta seccion captura tu firma digital, ingresa los datos correspondientes y cuando finalices da click en Guardar Firma
            </small>
        </div>
    </div>
    <br />
    <br />
    <br />

    <form id="FORM1" name="FORM1" class="border m-2 p-2 rounded">

      <p id="dispositivo" class="text-muted"> <code> Detectando tableta Topaz... </code> </p>
      <small>
        COMPLETA LOS CAMPOS CORRESPONDIENTES:
      </small>
      <div class="row">
          <div class="col-1">
              <small>No. Emp</small>
              <input type="number" id="noemp" class="form-control form-control-sm" />
          </div>
          <div class="col">
              <small>Nombre</small>
              <input type="text" id="nombre" class="form-control form-control-sm" readonly />
          </div>
          <div class="col">
              <small>Departamento</small>
              <input type="text" id="departamento" class="form-control form-control-sm" readonly />
          </div>
          <div class="col">
              <small>Puesto</small>
              <input type="text" id="puesto" class="form-control form-control-sm" readonly />
          </div>      
      </div>

      <br />
      <br />
      <br />
      
      <div style="float:center" >
          <div class="col-20">    
              <small class="alert alert-warning">
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
                  Comienza a registrar con 'Iniciar Captura' cuando este lista presiona 'Detener captura' y 'Guardar firma'.
              </small>
          </div>
      </div>
      <br />

      <div class="text-center">
        <canvas id="sig-canvas" width="400" height="150" class="mx-auto d-block"></canvas>

        <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
          <button type="button" class="btn btn-sm btn-warning" onclick="iniciarCaptura()">
            <i class="fa-solid fa-play"></i> Iniciar captura
          </button>
          <button type="button" class="btn btn-sm btn-danger" onclick="finalizarCaptura()" value="Done">
            <i class="fa-solid fa-circle-stop"></i> Detener captura
          </button>
          <button type="button" class="btn btn-sm btn-primary" onclick="limpiarFirma()">
            <i class="fa-solid fa-eraser"></i> Limpiar
          </button>
          <button type="button" class="btn btn-sm btn-success" onclick="guardarFirma()">
            <i class="fa-solid fa-floppy-disk"></i> Guardar firma
          </button>
        </div>

        <div id="status" class="alert mt-3 d-none"></div>

        <img id="firma-preview" class="d-none mt-3 border mx-auto d-block" width="300" alt="Vista previa de la firma" />
      </div>

    </form>

    <br />

    <!-- Tabla renderizada -->
    <div class="table-responsive" style="max-height: 200px;">
        <table class="table table-sm" width="1000px;">
            <thead class="table-dark">
                <th>Id</th>
                <th>NoEmp</th>
                <th>Nombre</th>
                <th>Departamento</th>
                <th>Puesto</th>                
                <th>Fecha de registro</th>
                <th>Acciones</th>
            </thead>
            <tbody id="tblFD">
            </tbody>
        </table>
    </div>

  <!-- Modal para ver firma -->
  <div class="modal fade" id="modalFirma" tabindex="-1" aria-labelledby="modalFirmaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFirmaLabel">Firma digital</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
          <div class="modal-body text-center">     
            <code>Firma recuperada de: </code>
            <label id="autorFirma"> </label>
            <br />
          </div>
        <div class="modal-body text-center">
          <img id="firma-preview-modal" src="" alt="Firma" class="img-fluid border p-2" style="max-height:300px;">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para capturar/editar firma -->
  <div class="modal fade" id="modalEditFirma" tabindex="-1" aria-labelledby="modalEditFirmaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditFirmaLabel">Capturar/Editar firma</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="text-center">
          <div class="modal-body">
            <div style="float:left" >

              <div class="col-20">    
                    <small class="alert alert-info">
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
                        Comienza a registrar con 'Iniciar Captura' cuando este lista presiona 'Detener captura' y 'Actualizar firma'.
                    </small>
                </div>
            </div>

            <br />
            <br />
            <br />

            <p id="dispositivo-edit" class="text-muted">Tableta Topaz Detectada !</p>
               
            <canvas id="sig-canvas-edit" width="400" height="150" class="border"></canvas>
            <div class="mt-3">
              <button type="button" class="btn btn-sm btn-warning" onclick="iniciarCapturaEdit()">
                <i class="fa-solid fa-play"></i> Iniciar captura
              </button>
              <button type="button" class="btn btn-sm btn-danger" onclick="finalizarCapturaEdit()">
                <i class="fa-solid fa-circle-stop"></i> Detener captura
              </button>
              <button type="button" class="btn btn-sm btn-primary" onclick="limpiarFirmaEdit()">
                <i class="fa-solid fa-eraser"></i> Limpiar
              </button>
              <button type="button" class="btn btn-sm btn-success" onclick="guardarFirmaEdit()">
                <i class="fa-solid fa-floppy-disk"></i> Actualizar firma
              </button>
            </div>
            <div id="status-edit" class="alert mt-3 d-none"></div>
            <br />
            <code> Firma Anterior: </code>    
            <br />        
            <img id="firma-preview-edit" class="d-none mt-3 border" width="300" alt="Vista previa de la firma" />
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/firma.js"></script>
<script src="js/SigWebTablet.js"></script>