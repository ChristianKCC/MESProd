<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Incapacidades</h5>
    <form id="formIncapacidad">
        <div class="row text text-center">
            <input type="hidden" id="id" name="id">
            <div class="col-1"><small>Noemp</small><input type="number" id="noemp" name="noemp"
                    class="form-control form-control-sm" min="0" /> </div>
            <div class="col-1"><br>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="empleadoActivo" disabled="">
                    <input type="hidden" name="checkActivoVal" id="checkActivoVal" value="1">
                    <label class="form-check-label" for="externo">
                        Empleado
                    </label>
                </div>
            </div>
            <div class="col-3"><small>Nombre</small><input type="text" id="nombre" name="nombre"
                    class="form-control form-control-sm" readonly /></div>
            <div class="col-1"><small>Departamento</small><select id="departamento" name="departamento"
                    class="form-control form-control-sm" disabled></select></div>
            <div class="col-2"><small>Puesto</small><select id="puesto" name="puesto"
                    class="form-control form-control-sm" disabled></select></div>
            <div class="col-1"><small>Responsable</small><input type="number" id="responsable" name="responsable"
                    class="form-control form-control-sm" /></div>
            <div class="col-3"><small>Nombre Responsable</small><input type="text" id="nombreresponsable"
                    name="nombreresponsable" class="form-control form-control-sm" readonly /></div>
        </div>
        <div class="row text-center">
            <div class="col-1"><small>Folio</small><input type="text" id="folio" name="folio"
                    class="form-control form-control-sm" /></div>
            <div class="col-2"><small>Tipo</small><select id="tipo" name="tipo"
                    class="form-control form-control-sm"></select></div>
            <div class="col-2"><small>Tipo incapacidad</small><select id="frecuencia" name="frecuencia"
                    class="form-control form-control-sm"></select></div>
            <div class="col-1"><small>Fecha revisión</small><input type="date" id="fecharevision" name="fecharevision"
                    class="form-control form-control-sm" /></div>
            <div class="col-1"><small>Días acumulados</small><input type="number" id="diasAcumulados"
                    name="diasAcumulados" class="form-control form-control-sm" disabled /></div>
            <div class="col-1"><small>Días</small><input type="number" id="dias" name="dias"
                    class="form-control form-control-sm" /></div>
            <div class="col-2"><small>Fecha inicia</small><input type="date" id="fechainicio" name="fechainicio"
                    class="form-control form-control-sm" /></div>
            <div class="col-2"><small>Fecha termina</small><input type="date" id="fechatermina" name="fechatermina"
                    class="form-control form-control-sm" readonly /></div>
            <!-- <div class="col-1"><br><a href="#" class="btn btn-sm btn-dark" data-bs-toggle="modal"
                    data-bs-target="#modalfirm"><i class="fas fa-pencil-alt"></i> Firmar</a> </div> -->
        </div>
        <div class="row text-center">
            <div class="col-1"><small>ST-7</small><select id="st1" name="st1"
                    class="form-control form-control-sm"></select></div>
            <div class="col-1"><small>ST-2</small><select id="stps" name="stps"
                    class="form-control form-control-sm"></select></div>
            <div class="col-3"><small>Fecha de entrega del dictamen</small><input type="text" id="fechaentrega"
                    name="fechaentrega" class="form-control form-control-sm" /></div>
            <div class="col-3"><small>DX</small><input type="text" id="dx" name="dx"
                    class="form-control form-control-sm" /></div>
        </div>

        <div class="row justify-content-end">
            <div class="col-1"><br><button class="btn btn-sm bg-target" id="saveIncapacidad"><i class="fas fa-save"></i>
                    Guardar</button> </div>
            <div class="col-1"><br><button type="reset" class="btn btn-sm btn-secondary" id="limpiadoIncapacidad"><i
                        class="fas fa-undo-alt"></i> Limpiar</button></div>
        </div>

        <div class="modal fade" id="modalfirm" tabindex="-1" aria-labelledby="modalfirmLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalfirmLabel">Firma del paciente</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <canvas id="canvas" style="border:1px solid #000;"></canvas>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" id="limpiarCanvas"><i
                                class="fas fa-undo-alt"></i> Limpiar Firma</button>
                        <button type="button" class="btn btn-sm bg-target" data-bs-dismiss="modal"><i
                                class="fas fa-thumbs-up"></i> Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="my-4 table-responsive" style="height: 450px;">
        <table class="table table-bordered">
            <thead class="table-dark">
                <th>Noemp</th>
                <th>Nombre</th>
                <th>Departamento</th>
                <th>Puesto</th>
                <th>Responsable</th>
                <th>Nombre</th>
                <th>Folio</th>
                <th>Tipo</th>
                <th>Frec.</th>
                <th>Revision</th>
                <th>Dias</th>
                <th>DiasAcc</th>
                <th>FechaInicio</th>
                <th>FechaFin</th>
                <th>ST7</th>
                <th>STPS</th>
                <th>FechaEntrega</th>
                <th>DX</th>
                <!-- <th>Firma</th> -->
                <th></th>
            </thead>
            <tbody id="tblIncapacidades">

            </tbody>
        </table>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/incapacidades.js"></script>