<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>

<style>
    .bd-red-200 {
        color: #000;
        background-color: #f1aeb5;
    }

    .bd-red-500 {
        color: #fff;
        background-color: #dc3545;
    }

    .bd-red-700 {
        color: #fff;
        background-color: #842029;
    }
</style>
<script src="https://www.sigplusweb.com/SigWebTablet.js"></script>
<!--  Contenido  -->
<div class="container p-3">
    <form id="formExamenmedico">
        <div class="paso" id="paso1">
            <input type="hidden" name="id" id="id">
            <div class="row text-center bg-dark m-1">
                <h5 class="text-white">Datos Personales</h5>
            </div>
            <div class="row text-center m-1">
                <div class="col-2">
                    <small>Tipo de Examen</small>
                    <select id="examenTipo" name="examenTipo"
                        class="form-control form-control-sm">
                        <option value="" selected>Selecciona una opción</option>
                        <option value="1">Ingreso</option>
                        <option value="2">Periódico</option>
                        <option value="3">Egreso</option>
                    </select>
                </div>
                <div class="col-2"><small>Noemp</small><input type="number" id="noemp" name="noemp"
                        class="form-control form-control-sm" /></div>
                <div class="col-4"><small>Nombre</small><input type="text" id="nombre" name="nombre"
                        class="form-control form-control-sm" disabled /></div>
                <div class="col-2"><small>Departamento</small><select id="departamento" name="departamento"
                        class="form-control form-control-sm" disabled></select></div>
                <div class="col-2"><small>Puesto</small><select id="puesto" name="puesto"
                        class="form-control form-control-sm" disabled></select></div>
                <div class="col-2"><small>Maquina</small><select id="maquina" name="maquina"
                        class="form-control form-control-sm"></select></div>
                <div class="col-2"><small>Fecha nacimiento</small><input type="date" id="fechanaimiento"
                        name="fechanaimiento" class="form-control form-control-sm" /></div>
                <div class="col-4"><small>Lugar de nacimiento</small><input type="text" id="lugarnac" name="lugarnac"
                        class="form-control form-control-sm" /></div>
                <div class="col-4"><small>Domicilio</small><input type="text" id="domicilio" name="domicilio"
                        class="form-control form-control-sm" /></div>
                <div class="col-2"><small>Escolaridad</small><select id="escolaridad" name="escolaridad"
                        class="form-control form-control-sm"></select></div>
                <div class="col-3"><small>Religion</small><select id="religion" name="religion"
                        class="form-control form-control-sm"></select></div>
                <div class="col-3"><small>Grupo sanguineo</small><select id="tiposangre" name="tiposangre"
                        class="form-control form-control-sm"></select></div>
                <div class="col-2"><small>Fecha Ingreso</small><input type="date" id="fechaingreso" name="fechaingreso"
                        class="form-control form-control-sm" /></div>
                <div class="col-2">
                    <small>Fecha de Revision</small><input type="date" id="fecharevision" name="fecharevision"
                        class="form-control form-control-sm" />
                </div>
            </div>
            <div class="row text-center m-1" id="datosIngreso" hidden>
                <div class="col-2 align-items-center">
                    <small>¿Qué puesto ocupaba anteriormente?</small><input type="text" id="puestoAnterior"
                        name="puestoAnterior" class="form-control form-control-sm d-flex" />
                    <small>Horario Laboral</small><input type="text" id="horariolaboral" name="horariolaboral"
                        class="form-control form-control-sm" />
                </div>
                <div class="col-2 align-items-center">
                    <small>¿Cuanto tiempo trabajó ahí?</small><input type="number" min="0" id="tiempotrabajado"
                        name="tiempotrabajado" class="form-control form-control-sm d-flex" />
                    <small>¿Qué es seguridad industrial?</small><input type="text" id="seguridadIndustrial" name="seguridadIndustrial"
                        class="form-control form-control-sm" />
                </div>
                <div class="col-4 align-items-center">
                    <small>¿Que tipo de equipo de protección personal utilizo?</small><input type="text" id="equipoproteccion" name="equipoproteccion"
                        class="form-control form-control-sm" />
                    <small>¿Llego a estar expuesto a ruido, polvos, calor, humedad, vibraciones, radiaciones o a otros? Indique</small><input type="text" id="expoRuidos" name="expoRuidos"
                        class="form-control form-control-sm" />
                </div>
                <div class="col-4 align-items-center">
                    <small>Estuvo expuesto a monoxido de piridina, dimetilanina de carbono o a algún otro medicamento?</small><input type="text" id="expoQuimicos" name="expoQuimicos"
                        class="form-control form-control-sm" />
                </div>
            </div>
            <div class="row align-items-center m-1">
                <div class="col-4 align-items-center">
                    <small>Problemas de Salud Actuales</small><input type="text" id="problemasdesalud"
                        name="problemasdesalud" class="form-control form-control-sm d-flex" />
                    <small>Toma Algún Medicamento</small><input type="text" id="tomamedicamento" name="tomamedicamento"
                        class="form-control form-control-sm" />
                </div>
                <div class="col-4 align-items-center">
                    <small>Tratamiento Médico Actual</small><input type="text" id="tratamientomedico"
                        name="tratamientomedico" class="form-control form-control-sm" />
                    <small>Enfermedad Crónico Degenerativa</small><input type="text" id="enfermedadcronica"
                        name="enfermedadcronica" class="form-control form-control-sm" />
                </div>
                <div class="col-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="tabaquismo" name="tabaquismo">
                        <label class="form-check-label" for="tabaquismo">
                            Tabaquismo
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="alcoholismo" name="alcoholismo">
                        <label class="form-check-label" for="alcoholismo">
                            Alcoholismo
                        </label>
                    </div>
                </div>
                <div class="col-2">
                    <br>
                    <a href="#" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#modalfirm"
                        id="btnSign"><i class="fas fa-pencil-alt"></i> Firmar</a>
                    <a href="#" class="btn btn-sm btn-danger" id="consentimiento" hidden><i
                            class="fa-solid fa-file-pdf"></i> Consentimiento</a>
                </div>
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
            <div class="row justify-content-end">
                <div class="col-1">
                    <button class="btn btn-sm btn-success" type="button" onclick="mostrarPaso(2)">Siguiente</button>
                </div>
            </div>
        </div>
        <div class="paso" id="paso2" style="display:none;">
            <div class="row text-center">
                <div class="row text-center bg-dark m-1">
                    <h5 class="text-white">Atencedentes Personales Patologicos y No Patologicos Alergias</h5>
                </div>
                <div class="col-4">
                    <p class="fw-bold">ULTIMOS 2 AÑOS</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Alt.Física</small><input type="text" id="altfisica" name="altfisica"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Quirúrgicos</small><input type="text" id="quirurgicos" name="quirurgicos"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Traumáticos</small><input type="text" id="traumaticos" name="traumaticos"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Transfuciones</small><input type="text" id="transfuciones" name="transfuciones"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-4">
                    <p class="fw-bold">ALERGIAS</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Antibióticos</small><input type="text" id="antivioticos" name="antivioticos"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Analgésicos</small><input type="text" id="analgesitos" name="analgesitos"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Anti-inflamatorios</small><input type="text" id="antiinflamatorios"
                            name="antiinflamatorios" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Otros</small><input type="text" id="otrosalergias" name="otrosalergias"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-4">
                    <p class="fw-bold">HÁBITO HIGIÉNICO DIETÉTICO</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Alimentación</small><input type="text" id="alimentacion" name="alimentacion"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Aseo.General</small><input type="text" id="aseogeneral" name="aseogeneral"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Hobbies</small><input type="text" id="hobbies" name="hobbies"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>OtrasAct.Laborales</small><input type="text" id="otrasactlaborales"
                            name="otrasactlaborales" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-3">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Incapacidades</small><input type="text" id="incapacidades" name="incapacidades"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Diagnóstico</small><input type="text" id="diagnostico" name="diagnostico"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Días.Incapacidad</small><input type="text" id="diasIncapacidad" name="diasIncapacidad"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Secuelas</small><input type="text" id="secuela" name="secuela"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Rehabilitación</small><input type="text" id="rehabilitacion" name="rehabilitacion"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="trayecto" name="trayecto">
                        <label class="form-check-label" for="trayecto">
                            Trayecto
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="enfgeneral" name="enfgeneral">
                        <label class="form-check-label" for="enfgeneral">
                            Enfermedad General
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="accidentetrabajo"
                            name="accidentetrabajo">
                        <label class="form-check-label" for="accidentetrabajo">
                            Accidentes de Trabajo
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="enfermedadtrabajo"
                            name="enfermedadtrabajo">
                        <label class="form-check-label" for="enfermedadtrabajo">
                            Enfermedad de Trabajo
                        </label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end">
                <div class="col-2">
                    <button class="btn btn-sm btn-danger" type="button" onclick="mostrarPaso(1)">Anterior</button>
                    <button class="btn btn-sm btn-success" type="button" onclick="mostrarPaso(3)">Siguiente</button>
                </div>
            </div>
        </div>
        <div class="paso" id="paso3" style="display:none;">
            <div class="row text-center bg-dark mb-1">
                <h5 class="text-white">Interrogatorio por Aparato y Sistemas</h5>
            </div>
            <div class="row m-1">
                <div class="col-3 border">
                    <div class="row">
                        <div class="col-6">
                            <p class="fw-bold">Cardio - Respiratorio</p>
                            <label class="mb-1">Tos</label><br>
                            <label class="mb-1">Expectoración</label><br>
                            <label class="mb-1">Dolor Torácico</label><br>
                            <label class="mb-1">Taquicardia</label>
                        </div>
                        <div class="col-6">
                            <br>
                            <br>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="Tos0" name="Tos" value="0" checked>
                                    <label class="form-check-label" for="Tos0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="Tos1" name="Tos" value="1">
                                    <label class="form-check-label" for="Tos1">No</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="expectoracion0"
                                        name="expectoracion" value="0" checked>
                                    <label class="form-check-label" for="expectoracion0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="expectoracion1"
                                        name="expectoracion" value="1">
                                    <label class="form-check-label" for="expectoracion1">No</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="dolortoracico0"
                                        name="dolortoracico" value="0" checked>
                                    <label class="form-check-label" for="dolortoracico0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="dolortoracico1"
                                        name="dolortoracico" value="1">
                                    <label class="form-check-label" for="dolortoracico1">No</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="taquicardia0" name="taquicardia"
                                        value="0" checked>
                                    <label class="form-check-label" for="taquicardia0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="taquicardia1" name="taquicardia"
                                        value="1">
                                    <label class="form-check-label" for="taquicardia1">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3 border">
                    <br>
                    <div class="row">
                        <div class="col-6">
                            <label class="mb-1">Disnea</label><br>
                            <label class="mb-1">Cianosis</label><br>
                            <label class="mb-1">Edema/Insuficiencia Venosa</label>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="disnea0" name="disnea" value="0"
                                        checked>
                                    <label class="form-check-label" for="disnea0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="disnea1" name="disnea" value="1">
                                    <label class="form-check-label" for="disnea1">No</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="cianosis0" name="cianosis"
                                        value="0" checked>
                                    <label class="form-check-label" for="cianosis0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="cianosis1" name="cianosis"
                                        value="1">
                                    <label class="form-check-label" for="cianosis1">No</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="edema0" name="edema" value="0"
                                        checked>
                                    <label class="form-check-label" for="edema0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="edema1" name="edema" value="1">
                                    <label class="form-check-label" for="edema1">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="text" id="obscardio" name="obscardio" class="form-control form-control-sm"
                        placeholder="Comentarios" />
                    <br>
                </div>
                <div class="col-3 border">
                    <div class="row">
                        <div class="col-7">
                            <p class="fw-bold">Digestivo</p>
                            <label class="mb-1">Dolor Abdominal</label><br>
                            <label class="mb-1">Tránsito Intestinal</label><br>
                            <label class="mb-1">Excretas por dia</label><br>
                            <label class="mb-1">Orofaringe</label>
                        </div>
                        <div class="col-5">
                            <br>
                            <br>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="dolorabdominal0"
                                        name="dolorabdominal" value="0" checked>
                                    <label class="form-check-label" for="dolorabdominal0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="dolorabdominal1"
                                        name="dolorabdominal" value="1">
                                    <label class="form-check-label" for="dolorabdominal1">No</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="transintestinal0"
                                        name="transintestinal" value="0" checked>
                                    <label class="form-check-label" for="transintestinal0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="transintestinal1"
                                        name="transintestinal" value="1">
                                    <label class="form-check-label" for="transintestinal1">No</label>
                                </div>
                            </div>
                            <input type="number" id="excretaxdia" name="excretaxdia"
                                class="form-control form-control-sm" min="1" />
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="orofaringeo0" name="orofaringeo"
                                        value="0" checked>
                                    <label class="form-check-label" for="orofaringeo0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="orofaringeo1" name="orofaringeo"
                                        value="1">
                                    <label class="form-check-label" for="orofaringeo1">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3 border">
                    <br>
                    <div class="row">
                        <div class="col-6">
                            <label class="mb-1">Abdomen</label><br>
                            <label class="mb-1">Hernia</label><br>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="abdomen0" name="abdomen" value="0"
                                        checked>
                                    <label class="form-check-label" for="abdomen0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="abdomen1" name="abdomen" value="1">
                                    <label class="form-check-label" for="abdomen1">No</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="hernia0" name="hernia" value="0"
                                        checked>
                                    <label class="form-check-label" for="hernia0">Si</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="hernia1" name="hernia" value="1">
                                    <label class="form-check-label" for="hernia1">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br><input type="text" id="obsdigestivo" name="obsdigestivo" class="form-control form-control-sm"
                        placeholder="Comentarios" />
                    <br>
                </div>
            </div>
            <div class="row m-3 mt-1">
                <div class="col">
                    <small>Observaciones</small><textarea class="form-control form-control-sm" id="Observaciongeneral"
                        name="Observaciongeneral"></textarea>
                </div>
            </div>
            <div class="row m-2 justify-content-center">
                <div class="col-6 border p-2">
                    <p class="fw-bold">Exploración Física</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Peso</small><input type="text" id="peso" name="peso"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Talla</small><input type="text" id="talla" name="talla"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>IMC</small><input type="text" id="imc" name="imc"
                                        class="form-control form-control-sm" />
                                </div>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>F.C</small><input type="text" id="fc" name="fc"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>F.R</small><input type="text" id="fr" name="fr"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>T.A</small><input type="text" id="ta" name="ta"
                                        class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 m-2">
                                <small>Clasificacion IMC</small>
                                <select id="imcClasificacion" name="imcClasificacion"
                                    class="form-control form-control-sm" disabled readonly></select>
                            </div>
                        </div>
                        <div class="col-md-6 ">
                            <div class="d-flex align-items-center gap-2 m-2">
                                <small>Presion</small>
                                <input type="text" id="presionArterial" name="presionArterial"
                                    class="form-control form-control-sm" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 border p-2">
                    <p class="fw-bold">Agudeza Visual</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Ojo Derecho</small><input type="text" id="ojoder" name="ojoder"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Ojo Izquierdo</small><input type="text" id="ojoizq" name="ojoizq"
                                        class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Bilateral</small><input type="text" id="bilateral" name="bilateral"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Pupilas</small><input type="text" id="pupilas" name="pupilas"
                                        class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Uso de Lentes</small>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="usoLentes" id="lentes0"
                                            value="0" checked>
                                        <label class="form-check-label" for="lentes0">Si</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="usoLentes" id="lentes1"
                                            value="1">
                                        <label class="form-check-label" for="lentes1">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <small>Observaciones</small>
                            <textarea class="form-control form-control-sm" id="observacionAgudezaVisual"
                                name="observacionAgudezaVisual"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end">
                <div class="col-2">
                    <button class="btn btn-sm btn-danger" type="button" onclick="mostrarPaso(2)">Anterior</button>
                    <button class="btn btn-sm btn-success" type="button" onclick="mostrarPaso(4)">Siguiente</button>
                </div>
            </div>
        </div>
        <div class="paso" id="paso4" style="display:none;">
            <div class="row m-2 justify-content-center">
                <div class="col-md-4 border">
                    <p class="fw-bold">Nervioso</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Conciencia</small><input type="text" id="conciencia" name="conciencia"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Sensibilidad</small><input type="text" id="sensible" name="sensible"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Sueño</small><input type="text" id="sueno" name="sueno"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Reflejos</small><input type="text" id="reflejo" name="reflejo"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-5 border">
                    <div class="row">
                        <div class="col-6">
                            <p class="fw-bold">Órganos de los Sentidos</p>
                            <label class="mb-1">Audición</label><br>
                            <label class="mb-1">Agilidad Visual</label><br>
                            <label class="mb-1">Reflejos</label><br>
                            <label class="mb-1">Campimetría</label><br>
                            <label class="mb-1">Olfato</label><br>
                            <label class="mb-1">Tacto</label>
                        </div>
                        <div class="col-6">
                            <br>
                            <br>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="audicion" id="audicion0"
                                        value="0" checked>
                                    <label class="form-check-label" for="audicion0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="audicion" id="audicion1"
                                        value="1">
                                    <label class="form-check-label" for="audicion1">Anormal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="agilidadvisual"
                                        id="agilidadvisual0" value="0" checked>
                                    <label class="form-check-label" for="agilidadvisual0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="agilidadvisual"
                                        id="agilidadvisual1" value="1">
                                    <label class="form-check-label" for="agilidadvisual1">Diminuida</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="reflejosnervios" id="reflejos0"
                                        value="0" checked>
                                    <label class="form-check-label" for="reflejos0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="reflejosnervios" id="reflejos1"
                                        value="1">
                                    <label class="form-check-label" for="reflejos1">Anormal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="campimetria" id="campimetria0"
                                        value="0" checked>
                                    <label class="form-check-label" for="campimetria0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="campimetria" id="campimetria1"
                                        value="1">
                                    <label class="form-check-label" for="campimetria1">Anormal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="olfato" id="olfato0" value="0"
                                        checked>
                                    <label class="form-check-label" for="olfato0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="olfato" id="olfato1" value="1">
                                    <label class="form-check-label" for="olfato1">Anormal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tactonerv" id="tactonerv0"
                                        value="0" checked>
                                    <label class="form-check-label" for="tactonerv0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tactonerv" id="tactonerv1"
                                        value="1">
                                    <label class="form-check-label" for="tactonerv1">Anormal</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 border">
                    <p class="fw-bold">Pruebas especiales equilibrio y propiocepción NOM-036-1-STPS-2018</p>
                    <div class="d-flex gap-2 m-2">
                        <small>Romberg</small><input type="text" id="cardiopulmonar" name="cardiopulmonar"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Bibinskli</small><input type="text" id="tecnicarte" name="tecnicarte"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Octocerosis</small><input type="text" id="octocerosis" name="octocerosis"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Timpano</small><input type="text" id="timpano" name="timpano"
                            class="form-control form-control-sm" />
                    </div>
                </div>
            </div>

            <div class="row m-1">
                <div class="col">
                    <small>Observaciones</small><input type="text" id="observacionnervios" name="observacionnervios"
                        class="form-control form-control-sm" />
                </div>
            </div>

            <div class="row m-2 justify-content-center">
                <div class="col-6 border">
                    <div class="align-items-center gap-2 m-2">
                        <p class="fw-bold">Cardiopulmonar</p>
                        <input type="text" id="cardiopulmonar2" name="cardiopulmonar2"
                            class="form-control form-control-sm" hidden />
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Tensión.Arterial</small><input type="text" id="tecnicarte2"
                                        name="tecnicarte2" class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Frec.Cardiaca</small><input type="text" id="freccardiaca" name="freccardiaca"
                                        class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Vias.Resp.Sup.</small><input type="text" id="viasrespi" name="viasrespi"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Camp.Pulmonares</small><input type="text" id="camppulmonar"
                                        name="camppulmonar" class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col mb-3">
                        <small>Observaciones.</small><input type="text" id="obsgencardio" name="obsgencardio"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-6 border">
                    <div class="align-items-center gap-2 m-2">
                        <small class="fw-bold">Digestivo</small>
                        <input type="text" id="digestivo" name="digestivo" class="form-control form-control-sm"
                            hidden />
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-3">
                                    <small>Peristalsis</small><input type="text" id="peristalsis" name="peristalsis"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Dolor</small><input type="text" id="dolor" name="dolor"
                                        class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Organomegalias</small><input type="text" id="organomegalias"
                                        name="organomegalias" class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Hernia.Umbilical</small><input type="text" id="herniaumbilical"
                                        name="herniaumbilical" class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end">
                <div class="col-2">
                    <button class="btn btn-sm btn-danger" type="button" onclick="mostrarPaso(3)">Anterior</button>
                    <button class="btn btn-sm btn-success" type="button" onclick="mostrarPaso(5)">Siguiente</button>
                </div>
            </div>
        </div>
        <div class="paso" id="paso5" style="display:none;">
            <div class="row m-1 justify-content-center">
                <div class="col-6 border">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="fw-bold">Músculo Esquelético</p>
                            <label class="mb-1">Cuello</label><br>
                            <label class="mb-1">Columna Vertebral</label><br>
                            <label class="mb-1">Movilidad.M.T.M.P</label><br>
                            <label class="mb-1">Marcha</label><br>
                            <label class="mb-1">R.O.T.S</label><br>
                            <label class="mb-1">Punto de Riesgo Lumbar</label>
                        </div>
                        <div class="col-md-6">
                            <br>
                            <br>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cuello" id="cuello0" value="0"
                                        checked>
                                    <label class="form-check-label" for="cuello0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cuello" id="cuello1" value="1">
                                    <label class="form-check-label" for="cuello1">Anormal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="columnavertebral"
                                        id="columnavertebral0" value="0" checked>
                                    <label class="form-check-label" for="columnavertebral0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="columnavertebral"
                                        id="columnavertebral1" value="1">
                                    <label class="form-check-label" for="columnavertebral1">Anormal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="movilidad" id="movilidad0"
                                        value="0" checked>
                                    <label class="form-check-label" for="movilidad0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="movilidad" id="movilidad1"
                                        value="1">
                                    <label class="form-check-label" for="movilidad1">Anormal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="marcha" id="marcha0" value="0"
                                        checked>
                                    <label class="form-check-label" for="marcha0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="marcha" id="marcha1" value="1">
                                    <label class="form-check-label" for="marcha1">Anormal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rots" id="rots0" value="0"
                                        checked>
                                    <label class="form-check-label" for="rots0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rots" id="rots1" value="1">
                                    <label class="form-check-label" for="rots1">Anormal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="puntorlumbar" id="puntorlumbar0"
                                        value="0" checked>
                                    <label class="form-check-label" for="puntorlumbar0">Normal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="puntorlumbar" id="puntorlumbar1"
                                        value="1">
                                    <label class="form-check-label" for="puntorlumbar1">Anormal</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 border">
                    <p class="fw-bold">Pruebas Especiales Osteomuscular NOM-006-STPS-2014</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Lasage</small><input type="text" id="lasage" name="lasage"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Bragard</small><input type="text" id="bragard" name="bragard"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Tinel</small><input type="text" id="tinel" name="tinel"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Phanel</small><input type="text" id="phanel" name="phanel"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Trendelemburg</small><input type="text" id="trendelemburg" name="trendelemburg"
                            class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row m-3 mt-1">
                <div class="col">
                    <small>Observaciones</small><textarea class="form-control form-control-sm" id="obsmusculo"
                        name="obsmusculo"></textarea>
                </div>
            </div>
            <div class="row m-2">
                <p class="fw-bold">Espirometria</p>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Normal</small><input type="text" id="espnormal" name="espnormal"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Obstructivo</small><input type="text" id="espobstructivo" name="espobstructivo"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Restrictivo</small><input type="text" id="esprestrictivo" name="esprestrictivo"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Mixto</small><input type="text" id="espmixto" name="espmixto"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-4">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Archivo</small><input type="file" class="form-control form-control-sm" id="archivo"
                            name="archivo">                        
                        <a href="" id="archivopdf" target="_blank">
                        Ver archivo actual
                        </a>
                    </div>
                </div>
            </div>
            <div class="row justify-content-end">
                <div class="col-2">
                    <button class="btn btn-sm btn-danger" type="button" onclick="mostrarPaso(4)">Anterior</button>
                    <button class="btn btn-sm btn-success" type="button" onclick="mostrarPaso(6)">Siguiente</button>
                </div>
            </div>
        </div>
        <div class="paso" id="paso6" style="display:none;">
            <div class="row justify-content-center">
                <div class="row text-center bg-dark">
                    <p class="text-white">Audiometria NOM-011-STPS-2001</p>
                </div>
                <div class="col-9">
                    <br>
                    <table class="table table-bordered">
                        <thead>
                            <th></th>
                            <th>500</th>
                            <th>1000</th>
                            <th>2000</th>
                            <th>3000</th>
                            <th>4000</th>
                            <th>6000</th>
                            <th>8000</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>DERECHO</td>
                                <td contenteditable="true" id="d1" name="d1"></td>
                                <td contenteditable="true" id="d2" name="d2"></td>
                                <td contenteditable="true" id="d3" name="d3"></td>
                                <td contenteditable="true" id="d4" name="d4"></td>
                                <td contenteditable="true" id="d5" name="d5"></td>
                                <td contenteditable="true" id="d6" name="d6"></td>
                                <td contenteditable="true" id="d7" name="d7"></td>
                            </tr>
                            <tr>
                                <td>IZQUIERDO</td>
                                <td contenteditable="true" id="i1" name="i1"></td>
                                <td contenteditable="true" id="i2" name="i2"></td>
                                <td contenteditable="true" id="i3" name="i3"></td>
                                <td contenteditable="true" id="i4" name="i4"></td>
                                <td contenteditable="true" id="i5" name="i5"></td>
                                <td contenteditable="true" id="i6" name="i6"></td>
                                <td contenteditable="true" id="i7" name="i7"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-3">
                    <small>Diagnostico</small>
                    <select id="audioClasificacion" name="audioClasificacion"
                        class="form-control form-control-sm"></select>
                </div>
            </div>
            <div class="row m-1">
                <div class="col-6 border p-2">
                    <p class="fw-bold">Pruebas Especiales Osteomuscular NOM-006-STPS-2014</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Diagnostico SANO</small><input type="text" id="diagnostivosano"
                                        name="diagnostivosano" class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" hidden>
                            <div class="d-flex align-items-center gap-2 m-2">
                                <small>Conductiva</small><input type="text" id="conductiva" name="conductiva"
                                    class="form-control form-control-sm" />
                            </div>
                            <div class="d-flex align-items-center gap-2 m-2">
                                <small>Sensorial</small><input type="text" id="sensorial" name="sensorial"
                                    class="form-control form-control-sm" />
                            </div>
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Mixta</small><input type="text" id="mixma" name="mixma"
                                        class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4" hidden>
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Superficial</small><input type="text" id="superficial" name="superficial"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Moderada</small><input type="text" id="moderada" name="moderada"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="d-flex align-items-center gap-2 m-2">
                                    <small>Profunda</small><input type="text" id="profunda" name="profunda"
                                        class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 m-2">
                                <small>Hipoacusia Unilateral</small><input type="text" id="unilateral" name="unilateral"
                                    class="form-control form-control-sm" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 m-2">
                                <small>Hipoacusia Bilateral</small><input type="text" id="bilateralstp"
                                    name="bilateralstp" class="form-control form-control-sm" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 border">
                    <p class="fw-bold mt-2 mb-4">Trauma Acústico Crónico</p>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <small>Degenerativo</small><input type="text" id="traumadegenerativo" name="traumadegenerativo"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-3">
                        <small>Mixto</small><input type="text" id="traumamixto" name="traumamixto"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-3">
                        <small>Otros</small><input type="text" id="traumaotros" name="traumaotros"
                            class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-md-3 border">
                    <p class="fw-bold mt-2 mb-4">No Valorable Por:</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Otocerosis</small><input type="text" id="otocerosis" name="otocerosis"
                            class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Infección Faringea</small><input type="text" id="infeccionfaringea"
                            name="infeccionfaringea" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Perforación Timpánica</small><input type="text" id="perforanciatimpanica"
                            name="perforanciatimpanica" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>

            <div class="row justify-content-end">
                <div class="col-1"><br>
                    <button class="btn btn-sm btn-danger" type="button" onclick="mostrarPaso(5)">Anterior</button>
                </div>
                <div class="col-1"><br><button class="btn btn-sm bg-target" id="saveExamen" type="button"><i class="fas fa-save"></i>
                        Guardar</button> </div>
                <div class="col-1"><br><button type="reset" class="btn btn-sm btn-secondary" id="limpiadoConsulta"><i
                            class="fas fa-undo-alt"></i> Limpiar</button></div>
            </div>
        </div>
    </form>
    <div class="table-responsive my-4" style="height: 380px;">
        <table class="table table-bordered">
            <thead class="table-dark">
                <th hidden>ID</th>
                <th>NoEmp</th>
                <th>Nombre</th>
                <th>Departamento</th>
                <th>Puesto</th>
                <th>Fecha de revision</th>
                <th>Firma</th>
                <th></th>
            </thead>
            <tbody id="tblExamenMedico">

            </tbody>
        </table>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/ExamenMedico.js"></script>