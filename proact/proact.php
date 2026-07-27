<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">PROACTivo</h5>
    <div style="float:right;"><small>Fecha: </small><span id="fecha"></span></div><br />
    <form id="formproact" name="formproact">
        <div class="row mb-2">
            <div class="col-1"><small>Observado</small><input type="number" name="observado" id="observado" class="form-control form-control-sm" /></div>
            <div class="col-3"><small>Nombre</small><select name="nombres" id="nombres" class="form-control form-control-sm"></select></div>
            <div class="col-2"><small>Departamento donde se observa</small><select name="areas" id="areas" class="form-control form-control-sm"></select></div>
            <div class="col-2"><small>Área donde se observa</small><select name="maquinas" id="maquinas" class="form-control form-control-sm"></select></div>
            <div class="col-2"><small>Fecha</small><input type="date" name="fecha" id="fecha" class="form-control form-control-sm"></div>
            <div class="col-2"><small>Hora</small><input type="time" name="hora" id="hora" class="form-control form-control-sm"></div>
        </div>
        <div class="row">
            <div class="col-3"><small>Tipo de desviación</small><select name="observacion" id="observacion" class="form-control form-control-sm"></select></div>
            <div class="col-3"><small>Observación</small><select name="observacionreal" id="observacionreal" class="form-control form-control-sm"></select></div>
            <div class="col-3">
                <small>Describa el evento</small><textarea name="comentarios" id="comentarios" class="form-control dorm-control-sm" rows="2"></textarea>
            </div>
            <div class="col-3">
                <small>¿No se encuentra en el listado la observación?</small><textarea name="otrainteraccion" id="otrainteraccion" class="form-control dorm-control-sm" rows="2"></textarea>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-4">
                <br>
                <div class="d-flex">
                    <label class="form-check-label" for="deacuerdo" style="font-size:16px">¿Requiere escalamiento disciplinario? </label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="deacuerdo" id="deacuerdosi" value="1">
                        <label class="form-check-label" for="deacuerdosi">SI</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="deacuerdo" id="deacuerdono" value="0">
                        <label class="form-check-label" for="deacuerdono">NO</label>
                    </div>
                </div>
            </div>
            <div class="col-2">
                <br>
                <div class="d-flex">
                    <label class="form-check-label" for="inlineRadioOptions" style="font-size:16px">¿Cumple? </label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="cumple" id="cumplesi" value="1">
                        <label class="form-check-label" for="cumplesi">SI</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="cumple" id="cumpleno" value="0">
                        <label class="form-check-label" for="cumpleno">NO</label>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <small><span id="textcumple"><br></span></small>
                <select name="comportamiento" id="comportamiento" class="form-control form-control-sm">
                </select>
            </div>
            <div class="row justify-content-end mt-4">
                <div class="col-3">
                    <button id="guardar" class="btn btn-sm bg-target"><i class="fas fa-save"></i> Guardar datos</button>
                    <button type="reset" class="btn btn-sm btn-secondary"><i class="fas fa-undo-alt"></i> Limpiar</button>
                </div>
            </div>
        </div>
    </form>
    <br />
    
    <div class="row">
        <div class="table-responsive" style="height:320px;">
            <table class="table table-sm table-bordered">
                <thead class="table-dark">
                    <th>id</th>
                    <th>Observador</th>
                    <th>Observado</th>
                    <th>Area</th>
                    <th>Maquina</th>
                    <th>Fecha</th>
                </thead>
                <tbody id="tbl">
                </tbody>
            </table>
        </div>
    </div>

    <div class="row justify-content-end">
        <div class="col-2">
            <img src="../img/proact.png" width="200px">
        </div>
    </div>

</div>


<?php require_once("../index/footer.php") ?>
<script type="text/javascript" src="../poojs/herramientas.js"></script>
<script type="text/javascript" src="js/index.js"></script>
<!-- Uso de clase de inicio -->
<script type="text/javascript">
    proact = new Proact();
    proact.inicio();
</script>