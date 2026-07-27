
<?php 
 require_once("../Session/seguridad.php");
 require_once("../index/header.php"); ?>
<!-- Contenido -->
<div class="container rounded shadow">
<div class="row">
  <div class="col-10">
  <h5>Lista de revisiones iniciales</h5>
  </div>
  <div class="col-2">
  <small><a href="#" onclick="tblcapa()">Actualizar información</a></small>
  </div>
</div>
  <div class="row">
    <div class="col-9"><input type="text" class="form-control" id="buscarinvestigacion" value="" placeholder="Buscar revisión"></div>
    <div class="col-3"><button class="form-control btn bg-target" data-bs-toggle="modal" data-bs-target="#modalencabezado"><i class="fa-solid fa-square-plus"></i> Registro nueva no conformidad</button></div>
  </div>
  <div id="resulttbl"></div>
  </div>
  <div class="modal fade" id="modalcapa" tabindex="-1" aria-labelledby="capamodal" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-body">
        <div id="contenidomodal"></div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalencabezado" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Etapa 1: Revisión inicial</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
         <h6>Solicitado por: <?php echo $_SESSION['nombre'] ?> con número de empleado, <?php echo $_SESSION['ibm'] ?>.</h6>
   <form id="formcapa">
  <div class="row mb-3">
     <div class="text-center col-1">
        <small class="fw-bold">Folio CAPA</small>
        <input type="text" class="form-control form-control-sm" id="folio" name="" readonly>
      </div>
      <div class="text-center col-1">
        <?php $hoy = date("Y-m-d"); ?>
        <small class="fw-bold">Fecha</small>
        <input type="date" class="form-control form-control-sm" id="fecha" name="" value="<?php echo $hoy ?>" readonly>
      </div>
            <div class="text-center col-4">
              <small class="fw-bold">Departamento</small>
              <select name="NoDepto" id="NoDepto" class="form-control form-control-sm">
                </select>
              </div>
              <div class="text-center col-3">
                <small class="fw-bold">Máquina/Ubicación</small>
                <select name="NoMaquina" id="NoMaquina" class="form-control form-control-sm">
                </select>
          </div>
          <div class="text-center col-3">
                  <small class="fw-bold">Sección</small>
                  <select name="NoSeccion" id="NoSeccion" class="form-control form-control-sm">
                  </select>
            </div>
             <div class="text-center col-4">
                  <small class="fw-bold">Responsabilidad</small>
                  <select name="IdMCM" id="IdMCM" class="form-control form-control-sm">
                  </select>
              </div>
                <div class="text-center col-4">
                  <small class="fw-bold">Fuente</small>
                  <select name="IdFuente" id="IdFuente" class="form-control form-control-sm">
                  </select>
                </div>
                <div class="text-center col-4">
                  <small class="fw-bold">Tipo Fuente</small>
                  <select name="IdTipoFuente" id="IdTipoFuente" class="form-control form-control-sm">
                  </select>
                </div>
                <div class="row">
                <div class="text-center col-12">
                  <small class="fw-bold">Descripción del evento reportado</small>
                <textarea id="descripcioncapa" class="form-control form-control-sm"></textarea>
              </div>
          </div>
        <h5 class="modal-title mt-2" id="exampleModalLabel">Etapa 2: Evaluación</h5>
          <hr class="mt-2">
          <small class="fw-bold my-2">Evalúa el riesgo del evento reportado para determinar el nivel de atención. <a class="text-danger" data-bs-toggle="collapse" href="#collapseExample1" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="fas fa-question"></i>
                  </a></small>
                  <div class="collapse" id="collapseExample1">
                  <div class="row">
                    <div class="col-4">
                    <div class="card card-body">
                      <h5>Severidad</h5>
                      <p><span class="fw-bold">Sin riesgo:</span> Sin efecto.</p>
                      <p><span class="fw-bold">Poco:</span>Observaciones y probabilidad de mejora.</p>
                      <p><span class="fw-bold">Moderado:</span> Pequeñas desviaciones que pudieran afectar el resultado final.</p>
                      <p><span class="fw-bold">Mayor:</span> Controles de calidad no cumplen los criterios de aceptación.</p>
                      <p><span class="fw-bold">Peligroso:</span> Efecto peligroso pérdidas económicas significativas.</p>
                    </div>
                  </div>
                   <div class="col-4">
                    <div class="card card-body">
                      <h5>Probabilidad</h5>
                      <p><span class="fw-bold">Improbable:</span> 0% de ocurrencia.</p>
                      <p><span class="fw-bold">Remoto:</span> Menor al 0.1%.</p>
                      <p><span class="fw-bold">Baja:</span> Entre el 1% y 2% .</p>
                      <p><span class="fw-bold">Moderada:</span> Entre el 3% y 4%.</p>
                      <p><span class="fw-bold">Alta:</span> Entre el 7% y 8%.</p>
                      <p><span class="fw-bold">Muy alta:</span> Mayor 8%.</p>
                    </div>
                  </div>
                   <div class="col-4">
                    <div class="card card-body">
                      <h5>Detección</h5>
                      <p><span class="fw-bold">Falla obvia:</span> Una adecuada supervisión seguro puede detectarlo.</p>
                      <p><span class="fw-bold">Detección alta:</span> La supervisión la puede detectar.</p>
                      <p><span class="fw-bold">Det. Moderada:</span> La supervisión tiene buena oportunidad de detectarlo.</p>
                      <p><span class="fw-bold">Det. Muy Baja:</span> La supervisión tiene poca oportunidad de detectarlo.</p>
                      <p><span class="fw-bold">Det. Imposible:</span> Certeza absoluta de que no se puede detectar.</p>
                    </div>
                  </div>
                  <a class="text-danger" data-bs-toggle="collapse" href="#collapseExample1" role="button" aria-expanded="false" aria-controls="collapseExample">
                  <i class="fa-solid fa-angles-up"></i> Cerrar
                  </a>
                </div>
                  </div>
                <div class="text-center col-3">
                  <small class="fw-bold">Severidad</small>
                  <select name="severidad" id="severidad" class="form-control form-control-sm">
                  </select>
                </div>
                <div class="text-center col-3">
                  <small class="fw-bold">Probabilidad</small>
                  <select name="probabilidad" id="probabilidad" class="form-control form-control-sm">
                  </select>
                </div>
                <div class="text-center col-3">
                  <small class="fw-bold">Detección</small>
                  <select name="deteccion" id="deteccion" class="form-control form-control-sm">
                  </select>
                </div>
                <div class="text-center col-3">
                  <small class="fw-bold">Número de personas expuestas</small>
                  <select name="noexpuetas" id="noexpuetas" class="form-control form-control-sm">
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-12">
                  <small class="fw-bold">¿A quién vas a asignar esta CAPA?</small>
                  <select id="asigusauariocapa" class="form-control form-control-sm">
                  </select>
                </div>
                </div>
          <div class="row justify-content-between text-center my-2">
            <div class="col">
              <button type="button" class="btn bg-target btn-sm" name="" id="guardar" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
            </div><div class="col">
                <button type="reset" class="btn btn-primary btn-sm" name="" id="" onclick="nuevacapa()" title="Limpiar"><i class="fas fa-plus"></i> Nueva revisión</button>
            </div>
            <div class="col">
               <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar</button>
             </div>
          </div>
        </form>
      <small class="text-danger">Nota: En caso de ver un número en el campo folio, estas editando un registro.</small>
        <div id="resultencabezado"></div>
              <div id="tblcapas"></div>
    </div>
  </div>
</div>
</div>


<?php require_once("../index/footer.php") ?>
<script src="js/index.js" type="text/javascript"></script>