<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">IMC</h5>
    <form>
    <div class="row">
        <div class="col-8">
            <div class="row justify-content-center">
                <div class="col-2"><small hidden>No. IMC</small><input type="text" id="folioimc" class="form-control form-control-sm" hidden/></div>
                <div class="col-2"><small>Fecha</small><input type="date" id="fecha" class="form-control form-control-sm" /></div>
            </div>
            <div class="row p-2">
                <div class="col-6 p-2">
                    <div class="row p-2 border">
                        <h5 class="text-center"> Emisor </h5>
                        <div class="col-3"><small>No. Emp</small><input type="number" id="noempemisor" class="form-control form-control-sm" /></div>
                        <div class="col-9"><small>Nombre</small><input type="text" id="nombreemisor" class="form-control form-control-sm" readonly/></div>
                        <div class="col-12"><small>Departamento</small><input type="text" id="depemisor" class="form-control form-control-sm" readonly/></div>
                    </div>
                </div>
                <div class="col-6 p-2">
                    <div class="row p-2 border">
                        <h5 class="text-center"> Donde se encontro la condición</h5>
                        <div class="col-12"><small>Departamento</small><select id="departamento" class="form-control form-control-sm"></select></div>
                        <div class="col-12"><small>Area</small><select id="area" class="form-control form-control-sm"></select></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row mb-2">
                        <div class="col-4"><small>¿Como detectaste el riesgo?</small><select id="detriesgo" class="form-control form-control-sm"></select></div>
                        <div class="col-4"><small>Tipo de riesgo</small><select id="tiporiesgo" class="form-control form-control-sm"></select></div>
                        <div class="col-4"><small>Tipo</small><select id="tipo" class="form-control form-control-sm"></select></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row px-2">
                        <div class="col-12"><small>Descripción</small><textarea id="descripcion" class="form-control form-control-sm" rows="3"></textarea></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row border p-2 mb-2">
                        <h5>Responsable</h5>
                        <div class="col-3"><small>No. Emp</small><input type="text" id="responsable" class="form-control form-control-sm" /></div>
                        <div class="col-9"><small>Nombre</small><input type="text" id="responsablenombre" class="form-control form-control-sm" readonly/></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row px-2">
                        <div class="col-12"><small>Sugerencia</small><textarea id="sugerencias" class="form-control form-control-sm" rows="3"></textarea></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row border p-2">
                        <div class="col-12"><small>Fecha Compromiso</small><input type="date" id="fechacompromiso" class="form-control form-control-sm" /></div>
                        <div class="col-12"><small>Estado</small><select id="estado" class="form-control form-control-sm">
                            <option value="1">Pendiente</option>
                        </select></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <h5 class="text-center">Condiciones Inseguras</h5>
            <div id="divcondiciones"></div>
            <button class="btn btn-sm bg-target" id="guardarimc"><i class="fas fa-save"></i> Guardar datos</button>
            <button type="reset" class="btn btn-sm btn-secondary"><i class="fas fa-undo-alt"></i> Limpiar</button>
        </div>
    </div>
    </form>
    <div class="table-responsive" style="height: 230px;">
        <table class="table">
            <thead>
                <th>IMC</th>
                <th>Fecha</th>
                <th>Emisor</th>
                <th>Ubicacion</th>
                <th>Reponsable</th>
                <th>Descripcion</th>
                <th>Compromiso</th>
            </thead>
            <tbody id="tblimc">

            </tbody>
        </table>
    </div>
</div>

</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/CondIns.js"></script>