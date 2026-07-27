<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>

<html>

</html>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Examen Medico</h5>
    <form id="formExamenmedico">

        <div class="paso" id="paso1">
            <div class="row text-center">
                <div class="col-1"><small>Noemp</small><input type="number" id="noemp" name="noemp" class="form-control form-control-sm" /></div>
                <div class="col-3"><small>Nombre</small><input type="text" id="nombre" name="nombre" class="form-control form-control-sm" /></div>
                <div class="col-3"><small>Departamento</small><select id="departamento" name="departamento" class="form-control form-control-sm"></select></div>
                <div class="col-3"><small>Puesto</small><select id="puesto" name="puesto" class="form-control form-control-sm"></select></div>
                <div class="col-3"><small>Maquina</small><select id="maquina" name="maquina" class="form-control form-control-sm"></select></div>
                <div class="col-2"><small>Fecha nacimiento</small><input type="date" id="fechanaimiento" name="fechanaimiento" class="form-control form-control-sm" /></div>
                <div class="col-3"><small>Lugar de nacimiento</small><input type="text" id="lugarnac" name="lugarnac" class="form-control form-control-sm" /></div>
                <div class="col-4"><small>Domicilio</small><input type="text" id="domicilio" name="domicilio" class="form-control form-control-sm" /></div>
                <div class="col-2"><small>Escolaridad</small><select id="escolaridad" name="escolaridad" class="form-control form-control-sm"></select></div>
                <div class="col-3"><small>Religion</small><select id="religion" name="religion" class="form-control form-control-sm"></select></div>
                <div class="col-1"><small>Grupo sanguineo</small><select id="tiposangre" name="tiposangre" class="form-control form-control-sm"></select></div>
                <div class="col-2"><small>Fecha Ingreso</small><input type="date" id="fechaingreso" name="fechaingreso" class="form-control form-control-sm" /></div>
            </div>
            <hr>
            <div class="row text-center mb-2">
                <div class="col-6"><small>Problemas de salud actuales</small><input type="text" id="problemasdesalud" name="problemasdesalud" class="form-control form-control-sm" /></div>
                <div class="col-6"><small>Toma de Algun Medicamento</small><input type="text" id="tomamedicamento" name="tomamedicamento" class="form-control form-control-sm" /></div>
                <div class="col-6"><small>Tratamiento Medico Actual</small><input type="text" id="tratamientomedico" name="tratamientomedico" class="form-control form-control-sm" /></div>
                <div class="col-6"><small>Enfermedad Cronico Degenerativa</small><input type="text" id="enfermedadcronica" name="enfermedadcronica" class="form-control form-control-sm" /></div>
            </div>
            <div class="row text-center mb-2">
                <div class="col-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="tabaquismo" name="tabaquismo">
                        <label class="form-check-label" for="tabaquismo">
                            Tabaquismo
                        </label>
                    </div>
                </div>
                <div class="col-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="alcoholismo" name="alcoholismo">
                        <label class="form-check-label" for="alcoholismo">
                            Alcoholismo
                        </label>
                    </div>
                </div>
            </div>
            <div class="row text-center">
                <h5>Atencedentes Personales Patologicos y No Patologicos Alergias</h5>
                <div class="col-4">
                    <p class="fw-bold">ULTIMOS 2 AÑOS</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Alt.Fisica</small><input type="text" id="altfisica" name="altfisica" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Quirurgicos</small><input type="text" id="quirurgicos" name="quirurgicos" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Traumaticos</small><input type="text" id="traumaticos" name="traumaticos" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Transfuciones</small><input type="text" id="transfuciones" name="transfuciones" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-4">
                    <p class="fw-bold">ALERGIAS</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Antivioticos</small><input type="text" id="antivioticos" name="antivioticos" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Analgesicos</small><input type="text" id="analgesitos" name="analgesitos" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Anti-inflamatorios</small><input type="text" id="antiinflamatorios" name="antiinflamatorios" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Otros</small><input type="text" id="otrosalergias" name="otrosalergias" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-4">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Hab.HigienicoDietetico</small><input type="text" id="habhigienicodietetico" name="habhigienicodietetico" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Alimentacion</small><input type="text" id="alimentacion" name="alimentacion" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Aseo.General</small><input type="text" id="aseogeneral" name="aseogeneral" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Hobbies</small><input type="text" id="hobbies" name="hobbies" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>OtrasAct.Laborales</small><input type="text" id="otrasactlaborales" name="otrasactlaborales" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Incapacidades</small><input type="text" id="incapacidades" name="incapacidades" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Diagnostico</small><input type="text" id="diagnostico" name="diagnostico" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Dias.Incapacidad</small><input type="text" id="diasIncapacidad" name="diasIncapacidad" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Secuelas</small><input type="text" id="secuela" name="secuela" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Rehabilitacion</small><input type="text" id="rehabilitacion" name="rehabilitacion" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="trayecto" name="trayecto">
                        <label class="form-check-label" for="trayecto">
                            Trayecto
                        </label>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="enfgeneral" name="enfgeneral">
                        <label class="form-check-label" for="enfgeneral">
                            Enfermedad General
                        </label>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="accidentetrabajo" name="accidentetrabajo">
                        <label class="form-check-label" for="accidentetrabajo">
                            Accidentes de Trabajo
                        </label>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="enfermedadtrabajo" name="enfermedadtrabajo">
                        <label class="form-check-label" for="enfermedadtrabajo">
                            Enfermedad de Trabajo
                        </label>
                    </div>
                </div>
            </div>


            <button class="btn btn-sm btn-success" type="button" onclick="mostrarPaso(2)">Siguiente</button>

        </div>
        <div class="paso" id="paso2" style="display:none;">
            <div class="row text-center mb-2">
                <h5>Interrogatorio por Aparato y Sistemas</h5>
                <div class="col-3 border">
                    <p class="fw-bold">Cardio - Respiratorio</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="Tos" name="Tos">
                        <label class="form-check-label" for="Tos">
                            Tos
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="expectoracion" name="expectoracion">
                        <label class="form-check-label" for="expectoracion">
                            Expectoracion
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="dolortoracico" name="dolortoracico">
                        <label class="form-check-label" for="dolortoracico">
                            Dolor Toracico
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="taquicardia" name="taquicardia">
                        <label class="form-check-label" for="taquicardia">
                            Taquicardia
                        </label>
                    </div>
                </div>
                <div class="col-3 border">
                    <br>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="disnea" name="disnea">
                        <label class="form-check-label" for="disnea">
                            Disnea
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="cianosis" name="cianosis">
                        <label class="form-check-label" for="cianosis">
                            Cianosis
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="edema" name="edema">
                        <label class="form-check-label" for="edema">
                            Edema/Insuf. Venosa
                        </label>
                    </div>
                    <input type="text" id="obscardio" name="obscardio" class="form-control form-control-sm" />
                </div>
                <div class="col-3 border">
                    <p class="fw-bold">Digestivo</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="dolorabdominal" name="dolorabdominal">
                        <label class="form-check-label" for="dolorabdominal">
                            Dolor Abdominal
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="transintestinal" name="transintestinal">
                        <label class="form-check-label" for="transintestinal">
                            Transito Intestinal
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="excretaxdia" name="excretaxdia">
                        <label class="form-check-label" for="excretaxdia">
                            Excreta por dia
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="orofaringeo" name="orofaringeo">
                        <label class="form-check-label" for="orofaringeo">
                            Orofaringeo
                        </label>
                    </div>
                </div>
                <div class="col-3 border">
                    <br>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="abdomen" name="abdomen">
                        <label class="form-check-label" for="abdomen">
                            Abdomen
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="hernia" name="hernia">
                        <label class="form-check-label" for="hernia">
                            Hernia
                        </label>
                    </div>
                    <br><input type="text" id="obsdigestivo" name="obsdigestivo" class="form-control form-control-sm" />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <small>Observaciones</small><textarea class="form-control form-control-sm" id="Observaciongeneral" name="Observaciongeneral"></textarea>
                </div>
            </div>
            <div class="row mt-4">
                <p class="fw-bold">Exploracion fisica</p>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Peso</small><input type="text" id="peso" name="peso" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Talla</small><input type="text" id="talla" name="talla" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>IMC</small><input type="text" id="imc" name="imc" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>FC</small><input type="text" id="fc" name="fc" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>FR</small><input type="text" id="fr" name="fr" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>T.A.</small><input type="text" id="ta" name="ta" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <p class="fw-bold">Agudeza Visual</p>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Ojo.Der</small><input type="text" id="ojoder" name="ojoder" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Ojo.Izq</small><input type="text" id="ojoizq" name="ojoizq" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Bilateral</small><input type="text" id="bilateral" name="bilateral" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Pupilas</small><input type="text" id="pupilas" name="pupilas" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row m-2">
                <div class="col-3 border">
                    <p class="fw-bold">Nervioso</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Conciencia</small><input type="text" id="conciencia" name="conciencia" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Sensibilidad</small><input type="text" id="sensible" name="sensible" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Sueño</small><input type="text" id="sueno" name="sueno" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Reflejos</small><input type="text" id="reflejo" name="reflejo" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Observaciones</small><input type="text" id="observacionnervios" name="observacionnervios" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <br>
                    <label>Audicion</label><br>
                    <label>Agilidd Visual</label><br>
                    <label>Reflejos</label><br>
                    <label>Campimetria</label><br>
                    <label>Olfato</label><br>
                    <label>Tacto</label>
                </div>
                <div class="col-3">
                    <br>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="audicion" id="audicion0" value="0" checked>
                            <label class="form-check-label" for="audicion0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="audicion" id="audicion1" value="1">
                            <label class="form-check-label" for="audicion1">Anormal</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="agilidadvisual" id="agilidadvisual0" value="0" checked>
                            <label class="form-check-label" for="agilidadvisual0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="agilidadvisual" id="agilidadvisual1" value="1">
                            <label class="form-check-label" for="agilidadvisual1">Diminuida</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="reflejosnervios" id="reflejos0" value="0" checked>
                            <label class="form-check-label" for="reflejos0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="reflejosnervios" id="reflejos1" value="1">
                            <label class="form-check-label" for="reflejos1">Anormal</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="campimetria" id="campimetria0" value="0" checked>
                            <label class="form-check-label" for="campimetria0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="campimetria" id="campimetria1" value="1">
                            <label class="form-check-label" for="campimetria1">Anormal</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="olfato" id="olfato0" value="0" checked>
                            <label class="form-check-label" for="olfato0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="olfato" id="olfato1" value="1">
                            <label class="form-check-label" for="olfato1">Anormal</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tactonerv" id="tactonerv0" value="0" checked>
                            <label class="form-check-label" for="tactonerv0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tactonerv" id="tactonerv1" value="1">
                            <label class="form-check-label" for="tactonerv1">Anormal</label>
                        </div>
                    </div>
                </div>
            </div>

                <button class="btn btn-sm btn-danger" type="button" onclick="mostrarPaso(1)">Anterior</button>
            <button class="btn btn-sm btn-success" type="button" onclick="mostrarPaso(3)">Siguiente</button>
        </div>

        <div class="paso" id="paso3" style="display:none;">
            <div class="row mt-4">
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Romberg</small><input type="text" id="cardiopulmonar" name="cardiopulmonar" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Bibinskli</small><input type="text" id="tecnicarte" name="tecnicarte" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Octocerosis</small><input type="text" id="octocerosis" name="octocerosis" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Timpano</small><input type="text" id="timpano" name="timpano" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row m-2">
                <div class="col-3 border">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small class="fw-bold">Cardiopulmonar</small><input type="text" id="cardiopulmonar2" name="cardiopulmonar2" class="form-control form-control-sm" hidden />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Tencin.Arterial</small><input type="text" id="tecnicarte2" name="tecnicarte2" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Frec.Cardiaca</small><input type="text" id="freccardiaca" name="freccardiaca" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Vias.Resp.Sup.</small><input type="text" id="viasrespi" name="viasrespi" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Camp.Pulmonares</small><input type="text" id="camppulmonar" name="camppulmonar" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Observaciones.</small><input type="text" id="obsgencardio" name="obsgencardio" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-3 border">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small class="fw-bold">Digestivo</small><input type="text" id="digestivo" name="digestivo" class="form-control form-control-sm" hidden />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Peristalsis</small><input type="text" id="peristalsis" name="peristalsis" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Dolor</small><input type="text" id="dolor" name="dolor" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Organomegalias</small><input type="text" id="organomegalias" name="organomegalias" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>HerniaUmbilical</small><input type="text" id="herniaumbilical" name="herniaumbilical" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-2 mt-4">
                    <p class="fw-bold"></p>
                    <label class="mb-1">Cuello</label><br>
                    <label class="mb-1">Columna Vertebral</label><br>
                    <label class="mb-1">Movilidad.M.T.M.P</label><br>
                    <label class="mb-1">Marcha</label><br>
                    <label class="mb-1">R.O.T.S</label><br>
                    <label class="mb-1">Punto de Riesgo Lumbar</label>
                </div>
                <div class="col-2">
                    <p class="fw-bold">Musculo Esqueletico</p>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cuello" id="cuello0" value="0" checked>
                            <label class="form-check-label" for="cuello0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cuello" id="cuello1" value="1">
                            <label class="form-check-label" for="cuello1">Anormal</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="columnavertebral" id="columnavertebral0" value="0" checked>
                            <label class="form-check-label" for="columnavertebral0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="columnavertebral" id="columnavertebral1" value="1">
                            <label class="form-check-label" for="columnavertebral1">Anormal</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="movilidad" id="movilidad0" value="0" checked>
                            <label class="form-check-label" for="movilidad0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="movilidad" id="movilidad1" value="1">
                            <label class="form-check-label" for="movilidad1">Anormal</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="marcha" id="marcha0" value="0" checked>
                            <label class="form-check-label" for="marcha0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="marcha" id="marcha1" value="1">
                            <label class="form-check-label" for="marcha1">Anormal</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rots" id="rots0" value="0" checked>
                            <label class="form-check-label" for="rots0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rots" id="rots1" value="1">
                            <label class="form-check-label" for="rots1">Anormal</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="puntorlumbar" id="puntorlumbar0" value="0" checked>
                            <label class="form-check-label" for="puntorlumbar0">Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="puntorlumbar" id="puntorlumbar1" value="1">
                            <label class="form-check-label" for="puntorlumbar1">Anormal</label>
                        </div>
                    </div>
                </div>
                <div class="col-5">
                    <p class="fw-bold">Pruebas Especiales Osteomuscular NOM-006-STPS-2014</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Lasage</small><input type="text" id="lasage" name="lasage" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Bragard</small><input type="text" id="bragard" name="bragard" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Tinel</small><input type="text" id="tinel" name="tinel" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Phanel</small><input type="text" id="phanel" name="phanel" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Trendelemburg</small><input type="text" id="trendelemburg" name="trendelemburg" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <small>Observaciones</small><textarea class="form-control form-control-sm" id="obsmusculo" name="obsmusculo"></textarea>
                </div>
            </div>

                <button class="btn btn-sm btn-danger" type="button" onclick="mostrarPaso(2)">Anterior</button>
            <button class="btn btn-sm btn-success" type="button" onclick="mostrarPaso(4)">Siguiente</button>
        </div>

        <div class="paso" id="paso4" style="display:none;">
            <div class="row mt-4">
                <p class="fw-bold">Espirometria</p>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Normal</small><input type="text" id="espnormal" name="espnormal" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Obstructivo</small><input type="text" id="espobstructivo" name="espobstructivo" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Restrictivo</small><input type="text" id="esprestrictivo" name="esprestrictivo" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-2">
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Mixto</small><input type="text" id="espmixto" name="espmixto" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-6">
                    <p class="fw-bold">Audiometria NOM-011-STPS-2001</p>
                    <table class="table table-bordered">
                        <thead>
                            <th></th>
                            <th>500</th>
                            <th>1000</th>
                            <th>2000</th>
                            <th>3000</th>
                            <th>4000</th>
                            <th>5000</th>
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
                                <td contenteditable="true" id="d8" name="d8"></td>
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
                                <td contenteditable="true" id="i8" name="i8"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
                <button class="btn btn-sm btn-danger" type="button" onclick="mostrarPaso(3)">Anterior</button>
            <button class="btn btn-sm btn-success" type="button" onclick="mostrarPaso(5)">Siguiente</button>
        </div>

        <div class="paso" id="paso5" style="display:none;">
            <div class="row">
                <div class="col-4">
                    <p class="fw-bold">Pruebas Especiales Osteomuscular NOM-006-STPS-2014</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>DiagnosticoSANO</small><input type="text" id="diagnostivosano" name="diagnostivosano" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Conductiva</small><input type="text" id="conductiva" name="conductiva" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Sensorial</small><input type="text" id="sensorial" name="sensorial" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Mixta</small><input type="text" id="mixma" name="mixma" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Unilateral</small><input type="text" id="unilateral" name="unilateral" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Bilateral</small><input type="text" id="bilateralstp" name="bilateralstp" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Superficial</small><input type="text" id="superficial" name="superficial" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Moderada</small><input type="text" id="moderada" name="moderada" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Prfunda</small><input type="text" id="profunda" name="profunda" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-4">
                    <p class="fw-bold">Trauma Acustico Cronico</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Degenerativo</small><input type="text" id="traumadegenerativo" name="traumadegenerativo" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Mixto</small><input type="text" id="traumamixto" name="traumamixto" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Otros</small><input type="text" id="traumaotros" name="traumaotros" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="col-4">
                    <p class="fw-bold">No Valorable Por:</p>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Otocerosis</small><input type="text" id="otocerosis" name="otocerosis" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Infeccion Faringea</small><input type="text" id="infeccionfaringea" name="infeccionfaringea" class="form-control form-control-sm" />
                    </div>
                    <div class="d-flex align-items-center gap-2 m-2">
                        <small>Performancia Timpanica</small><input type="text" id="perforanciatimpanica" name="perforanciatimpanica" class="form-control form-control-sm" />
                    </div>
                </div>
            </div>


            <div class="row justify-content-end">

                
                <div class="col-1"><br><button class="btn btn-sm btn-danger" type="button" onclick="mostrarPaso(4)">Anterior</button> </div>
                <div class="col-1"><br><button class="btn btn-sm bg-target" id="saveExamen"><i class="fas fa-save"></i> Guardar</button> </div>
                <div class="col-1"><br><button type="reset" class="btn btn-sm btn-secondary" id="limpiaExamenM"><i class="fas fa-undo-alt"></i> Limpiar</button></div>
            </div>
        </div>
    </form>
    <div class="row my-4" style="height: 450px;">
        <table class="table table-bordered">
            <thead class="table-dark">
                <th>ID</th>
                <th>NoEmp</th>
                <th>Nombre</th>
                <th>Departamento</th>
                <th>Puesto</th>
                <th>Fecha</th>
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
<script>
    function mostrarPaso(numero) {
        document.querySelectorAll('.paso').forEach(p => p.style.display = 'none');
        document.getElementById('paso' + numero).style.display = 'block';
    }
</script>