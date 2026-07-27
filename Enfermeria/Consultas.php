<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<script src="https://www.sigplusweb.com/SigWebTablet.js"></script>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Consultas Enfermeria</h5>
    <form id="formConsultas">
        <div class="row text-center">
            <input type="hidden" id="id" name="id">
            <div class="col-1"><br />
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="externo">
                    <label class="form-check-label" for="externo">
                        Externo
                    </label>
                </div>
            </div>
            <div class="col-10" id="camposInternos">
                <div class="row">
                    <div class="col-1"><small>Noemp</small><input type="number" id="noemp" name="noemp"
                            class="form-control form-control-sm" min="0" /> </div>
                    <div class="col-3"><small>Nombre</small><input type="text" id="nombre" name="nombre"
                            class="form-control form-control-sm" readonly /></div>
                    <div class="col-2"><small>Departamento</small><select id="departamento" name="departamento"
                            class="form-control form-control-sm" disabled></select></div>
                    <div class="col-3"><small>Puesto</small><select id="puesto" name="puesto"
                            class="form-control form-control-sm" disabled></select></div>
                    <div class="col-2"><small>Maquina</small><select id="maquinas" name="maquinas"
                            class="form-control form-control-sm"></select></div>
                    <div class="col-1"><small>Antigüedad</small><input type="number" id="antiguedad" name="antiguedad"
                            class="form-control form-control-sm" min="0" /></div>
                </div>
            </div>
            <div class="col-8" id="camposExternos" hidden>
                <div class="row">
                    <div class="col"><small>Nombre completo</small><input type="text" id="nombreexterno"
                            name="nombreexterno" class="form-control form-control-sm" /></div>
                    <div class="col"><small>Empresa</small><input type="text" id="empresaexterna" name="empresaexterna"
                            class="form-control form-control-sm" /></div>
                    <div class="col"><small>Área de trabajo</small><input type="text" id="areatrabajoext"
                            name="areatrabajoext" class="form-control form-control-sm" /></div>
                </div>
            </div>
            <div class="col-1"><small>Edad</small><input type="number" id="edad" name="edad"
                    class="form-control form-control-sm" min="0" /></div>
            <div class="col-2"><small>Tipo de consulta</small><select id="tipoconsulta" name="tipoconsulta"
                    class="form-control form-control-sm"></select></div>
            <div class="col-1"><small>Fecha de revisión</small><input type="date" id="fecharevision" name=""
                    class="form-control form-control-sm" /></div>
            <div class="col-1"><small>Hora</small><input type="time" name="" id="horaRevision"
                    class="form-control form-control-sm""></div>
            <div class=" col-1"><small>Sexo</small><select id="sexo" name="sexo" class="form-control form-control-sm">
                    <option value="H">Masculino</option>
                    <option value="M">Femenino</option>
                </select></div>
            <div class="col-1"><small>Horario</small><select id="rolturno" name="rolturno"
                    class="form-control form-control-sm">
                    <option value="1">Mixto</option>
                    <option value="2">Rol de turno</option>
                </select></div>
            <div class="col-1"><small>Temperatura</small><input type="number" id="temperatura" name="temperatura"
                    class="form-control form-control-sm" min="0" /></div>
            <div class="col-1"><small>Frecuencia C</small><input type="number" id="frecuencia" name="frecuencia"
                    class="form-control form-control-sm" min="0" /></div>
            <div class="col-1"><small>PA Sistolica</small><input type="number" id="pasistolica" name="pasistolica"
                    class="form-control form-control-sm" min="0" /></div>
            <div class="col-1"><small>PA Diastolica</small><input type="number" id="padistolica" name="padistolica"
                    class="form-control form-control-sm" min="0" /></div>
            <div class="col-2"><small>Tipo de aparato</small><select id="tipoaparato" name="tipoaparato"
                    class="form-control form-control-sm"></select></div>
            <div class="col-2"><small>Tipo de enfermedad</small><select id="tipoenfermedad" name="tipoenfermedad"
                    class="form-control form-control-sm"></select></div>
            <div class="col-3"><small>Observación</small><textarea id="observacion" name="observacion"
                    class="form-control form-control-sm"></textarea></div>
            <div class="col-3"><small>Tratamiento</small><textarea id="tratamiento" name="tratamiento"
                    class="form-control form-control-sm"></textarea></div>
            <div class="col-1"><br><a href="#" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#modalfirm"
                        id="btnSign"><i class="fas fa-pencil-alt"></i> Firmar</a> </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-1"><br><button class="btn btn-sm bg-target" id="saveConsulta"><i class="fas fa-save"></i>
                    Guardar</button> </div>
            <div class="col-1"><br><button type="reset" class="btn btn-sm btn-secondary" id="limpiadoConsulta"><i
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
                            <canvas id="canvas" style="border:1px solid #000; background: transparent;"></canvas>
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
                <th>Maquina</th>
                <th>Edad</th>
                <th>Antiguedad</th>
                <th>Tratamiento</th>
                <th>Observación</th>
                <th>Aparato</th>
                <th>Enfermedad</th>
                <th>Consulta</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Firma</th>
                <th></th>
            </thead>
            <tbody id="tblconsultas">

            </tbody>
        </table>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/consultas.js"></script>