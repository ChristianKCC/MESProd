<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5>Reporte IMC</h5>
    <form id="reporteLSW">
        <div class="row">
            <div class="col"><small>Fecha Inicial</small><input type="date" class="form-control form-control-sm" name="fechai" id="fechai"></div>
            <div class="col"><small>Fecha Final</small><input type="date" class="form-control form-control-sm" name="fechaf" id="fechaf"></div>
            <div class="col"><small>Departamento</small><select name="departamentos" id="departamentos" class="form-control form-control-sm"></select></div>
            <div class="col"><small>Area</small><select name="area" id="areas" class="form-control form-control-sm"></select></div>
            <div class="col"><small>Estado</small><select name="estadoimc" id="estadoimc" class="form-control form-control-sm"></select></div>
            <div class="col"><small>Responsable / Folio IMC</small><input type="number" name="noemp" id="noemp" class="form-control form-control-sm" /></div>
            <div class="col-1"><br /><button class="bg-target btn btn-sm" id="consulta"><i class="fa-solid fa-database"></i> Buscar</button></div>
        </div>
    </form>
    <div id="resultado"></div>
    <hr>
    <a href="#" id="exportarexcel">exportar a excel</a>
    <div class="table-responsive" style="height:600px">
        <table class="table table-sm" id="tblimc">
            <thead class="table-dark">
                <th>IMC</th>
                <th>Creado</th>
                <th>Emisor</th>
                <th>Departamento</th>
                <th>Area</th>
                <th>Detección</th>
                <th>Riesgo</th>
                <th>Tipo</th>
                <th>Responsable</th>
                <th>Compromiso</th>
                <th>Estado</th>
                <th>Descripción</th>
                <th>Sugerencia</th>
                <th></th>
                <th></th>
            </thead>
            <tbody id="tblReporteIMC">
            </tbody>
        </table>
    </div>
    <div class="modal fade" id="modalRepimc" tabindex="-1" aria-labelledby="modalRepimcLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRepimcLabel">Actualizar Información <span id="folioencmodal"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-end">
                        <div class="col-2">
                            <input type="hidden" id="idimc" class="form-control form-control-sm" readonly></select>
                        </div>
                    </div>
                    <div class="row p-2">
                        <div class="col-6 p-2">
                            <div class="row p-2 border">
                                <h5 class="text-center"> Emisor </h5>
                                <div class="col-3"><small>No. Emp</small><input type="number" id="noempemisor" class="form-control form-control-sm" /></div>
                                <div class="col-9"><small>Nombre</small><input type="text" id="nombreemisor" class="form-control form-control-sm" readonly /></div>
                                <div class="col-12"><small>Departamento</small><input type="text" id="depemisor" class="form-control form-control-sm" readonly /></div>
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
                                <div class="col-9"><small>Nombre</small><input type="text" id="responsablenombre" class="form-control form-control-sm" readonly /></div>
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
                <div class="modal-footer">
                    <button type="button" id="guardarcambiosimc" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guradar cambios</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Reporteimc.js"></script>