<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php"); ?>
<style>
    .col-3 {
        width: 24%;
        margin-bottom: 10px;
    }
</style>
<!-- Contenido -->
<div class="container p-2 rounded shadow">
    <h5 class="tittlecont">Reporte de Incidentes</h5>
    <div class="row">
        <div class="col-10"><input type="text" class="form-control form-control-sm" placeholder="Buscar Incidencia" /></div>
        <div class="col-2"><button class="btn bg-target btn-sm " data-bs-toggle="modal" data-bs-target="#modalencabezado"><i class="fas fa-plus-square"></i> Registrar Nueva Incidencia</button></div>
    </div>
    <div class="row justify-content-between m-2 align-items-center" id="tblenc"></div>
</div>


<div class="modal fade" id="modalencabezado" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Etapa 1 Clasificación del Evento</h5>
                <button type="button" class="btn-close" id="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Solicitado por: <?php echo $_SESSION['nombre'] ?> con número de empleado, <?php echo $_SESSION['ibm'] ?>.</h6>
                <form id="formcapa">
                    <div class="row mb-3">
                        <!-- Campo solo para efectos de programación, siempre oculto -->
                        <div class="text-center col-1" hidden>
                            <small class="fw-bold">Folio</small>
                            <input type="text" class="form-control form-control-sm" id="folio" name="" readonly >
                        </div>
                        <!-- Campo solo para efectos de programación, siempre oculto -->
                        <div class="text-center col-1">
                            <small class="fw-bold">No. Reporte</small>
                            <input type="text" class="form-control form-control-sm" id="noReporte" name="" >
                        </div>
                        <div class="text-center col-2">
                            <?php $hoy = date("Y-m-d"); ?>
                            <small class="fw-bold">Fecha</small>
                            <input type="date" class="form-control form-control-sm" id="fecha" name="" value="<?php echo $hoy ?>">
                        </div>
                        <div class="text-center col-3">
                            <small class="fw-bold">Departamento</small>
                            <select name="NoDepto" id="NoDepto" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-3">
                            <small class="fw-bold">Ubicación</small>
                            <select name="NoMaquina" id="NoMaquina" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="text-center col-3">
                            <small class="fw-bold">Versión</small>
                            <select name="version" id="version" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-4">
                            <small class="fw-bold">Clasificación de evento</small>
                            <select name="clasificacion" id="clasificacion" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-4">
                            <small class="fw-bold">Sub-Clasificacion del evento</small>
                            <select name="incidencias" id="incidencias" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-4">
                            <small class="fw-bold">Descripción del evento reportado</small>
                            <textarea id="descripcioncapa" class="form-control form-control-sm"></textarea>
                        </div>
                        <div class="col-1">
                            Lesión Reportable
                        </div>
                        <div class="col-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="etapa1check1" id="radioSi1" value="1">
                                <label class="form-check-label" for="radioSi1">Sí</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="etapa1check1" id="radioNo1" value="0" checked>
                                <label class="form-check-label" for="radioNo1">No</label>
                            </div>
                        </div>
                        <div class="col-2">
                            Contacto con equipos energizado
                        </div>
                        <div class="col-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="etapa1check2" id="radioSi2" value="1">
                                <label class="form-check-label" for="radioSi2">Sí</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="etapa1check2" id="radioNo2" value="0" checked>
                                <label class="form-check-label" for="radioNo2">No</label>
                            </div>
                        </div>
                        <div class="text-center col-4 mt-2">
                            <div class="row">
                                <div class="col-2">
                                    <img src="" id="imgPreview" alt="">
                                </div>
                                <br>
                                <div class="col-1"></div>
                                <div class="col-9">
                                    <input type="file" class="form-control form-control-sm" id="archivo" name="archivo">
                                    <span id="nombreArchivo"></span>
                                </div>    
                            </div>
                        </div>
                        <h5 class="modal-title mt-2" id="exampleModalLabel">Datos Generales</h5>
                        <!-- Campos opcionales (vacios) -->
                        <hr class="mt-2">
                        <div class="text-center col-1">
                            <small class="fw-bold">NoEmp</small>
                            <input type="text" class="form-control form-control-sm" id="implicado" name="implicado">
                        </div>
                        <div class="text-center col-2">
                            <small class="fw-bold">Nombre implicado</small>
                            <input type="text" class="form-control form-control-sm" id="implicadonombre" name="implicadonombre" readonly>
                        </div>
                        <div class="text-center col-2">
                            <small class="fw-bold">Puesto implicado</small>
                            <input type="text" class="form-control form-control-sm" id="implicadopuesto" name="implicadopuesto" readonly>
                        </div>
                        <div class="text-center col-2">
                            <small class="fw-bold">Antiguedad en el puesto</small>
                            <select name="antiguedadpuesto" id="antiguedadpuesto" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-2">
                            <small class="fw-bold">Antiguedad en la empresa</small>
                            <select name="antiguedadempresa" id="antiguedadempresa" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-1">
                            <small class="fw-bold">Dias Incapacidad</small>
                            <input type="number" class="form-control form-control-sm" id="diasincapacidad" name="diasincapacidad">
                        </div>
                        <div class="text-center col-1">
                            <small class="fw-bold">Dias Trabajo</small>
                            <input type="number" class="form-control form-control-sm" id="diastrabajo" name="diastrabajo">
                        </div>
                        <div class="text-center col-2">
                            <small class="fw-bold">Tipo de contacto</small>
                            <select name="tipocontacto" id="tipocontacto" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-2">
                            <small class="fw-bold">Que provoco la lesión: </small>
                            <input type="text" class="form-control form-control-sm" id="provocolesion" name="provocolesion">
                        </div>
                        <div class="text-center col-2">
                            <small class="fw-bold">Tipo de lesión</small>
                            <select name="tipolesion" id="tipolesion" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-2">
                            <small class="fw-bold">Parte afectada</small>
                            <select name="parteafectada" id="parteafectada" class="form-control form-control-sm">
                            </select>
                        </div>
                        <h5 class="modal-title mt-2" id="exampleModalLabel">Etapa 2 Evaluación</h5>
                        <hr class="mt-2">
                        <small class="fw-bold my-2">Evalúa el riesgo del evento reportado para determinar el nivel de atención. <a class="text-danger" data-bs-toggle="collapse" href="#collapseExample1" role="button" aria-expanded="false" aria-controls="collapseExample">
                                <i class="fas fa-question"></i>
                            </a></small>
                        <div class="collapse" id="collapseExample1">
                            <div class="row">
                                <div class="col-4">
                                    <div class="card card-body">
                                        <h5>Severidad</h5>
                                        <p><span class="fw-bold">Fatalidad: </span> Fatalidad / Muerte de una o más personas (incluidas enfermedades terminales). Pérdidas ecónomicas iguales o superiores a $ 1,000,000.00 MXN.</p>
                                        <p><span class="fw-bold">Mayor Irreversible severo: </span>Pérdidas e dos miembros/ ojos, ambas manos, ambos pies o seria y permanente enfermedad (perdida permanente de funciones respiratorias, pérdida de audición por encima de la media, enfermedades no terminales). Completa perdida de piel/quemaduras de tercer. Pérdidas ecónomicas iguales o superiores a $ 100,000.00 MXN a $ 1,000,000.00 MXN.</p>
                                        <p><span class="fw-bold">Mayor Irreversible: </span> Pérdida de un miembro/ojo, una mano un pie. Pérdida parcial de piel/quemaduras del segundo grado >9% del cuerpo. Pérdidas ecónomicas iguales o superiores a $ 100,000.00 MXN a $ 1,000,000.00 MXN.</p>
                                        <p><span class="fw-bold">Menor Irreversible: </span> Pérdida de dedos (mano/pie), fractura de un hueso mayor (ej. cráneo, brazo, espalda, pelvis, pierna, costilla) o menores enfermedades permanentes (ej. pérdida auditiva ligera). Perdida de piel/quemaduras de tercer grado <-9% del cuerpo. Pérdidas ecónomicas iguales o superiores a $ 100,000.00 MXN a $ 1,000,000.00 MXN.</p>
                                                <p><span class="fw-bold">Mayor Reversible: </span> Fractura de un hueso menor (dedo de la mano, mano, dedo del pie, pie) o una enfermedad menor temporal (ej., contusión, torcedura o esguince) Parcial perdida de piel o quemaduras entre el 1% a 9% del cuerpo. Pérdidas ecónomicas entre $ 10,000.00 MXN a $ 100,000.00 MXN.</p>
                                                <p><span class="fw-bold">Menor Reversible: </span> Fatalidad / Muerte de una o más personas (incluidas enfermedades terminales). Pérdidas ecónomicas iguales o superiores a $ 1,000,000.00 MXN.</p>
                                                <p><span class="fw-bold">Bajo: </span> Fatalidad / Muerte de una o más personas (incluidas enfermedades terminales). Pérdidas ecónomicas iguales o superiores a $ 1,000,000.00 MXN.</p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card card-body">
                                        <h5>Probabilidad</h5>
                                        <p><span class="fw-bold">Acceso al peligro es muy posible:</span>100% Posibilidad de lesiónes y Acceso al Peligro</p>
                                        <p><span class="fw-bold">Acceso al peligro es posible:</span> 60% Posibilidad de lesiónes y Acceso al Peligro</p>
                                        <p><span class="fw-bold">Acceso al peligro obstruido:</span> 25% Posibilidad de lesiónes y Acceso al Peligro</p>
                                        <p><span class="fw-bold">Acceso al peligro muy obstruido:</span> Caso Imposible, Solo Bajo Condiciones Extremas</p>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card card-body">
                                        <h5>Frecuencia</h5>
                                        <p><span class="fw-bold">Anual:</span> Anual.</p>
                                        <p><span class="fw-bold">Mensual:</span> Mensual, una vez por mes o menos de una vez por semana.</p>
                                        <p><span class="fw-bold">Semanal:</span> Semanal, una vez por semana o menos de una vez por día.</p>
                                        <p><span class="fw-bold">Diario:</span> Diario, una vez por día o menos de una vez por hora.</p>
                                        <p><span class="fw-bold">Cada Hora:</span> Cada hora, una vez por hora o menos que constantemente.</p>
                                        <p><span class="fw-bold">Constantemete:</span> Constantemente</p>
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
                            <small class="fw-bold">Frecuencia</small>
                            <select name="frecuencia" id="frecuencia" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-3">
                            <small class="fw-bold">Número de personas expuestas</small>
                            <select name="noexpuetas" id="noexpuetas" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="text-center col-1">
                            <small class="fw-bold">Total: </small>
                            <input type="number" name="total" id="total" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row justify-content-between text-center my-2">
                        <div class="col">
                            <button type="button" class="btn bg-target btn-sm" name="guardaret1" id="guardaret1" title="Guardar"><i class="fas fa-save"></i> Guardar datos</button>
                        </div>
                        <div class="col">
                            <button type="reset" class="btn btn-primary btn-sm" name="" id="" onclick="nuevacapa()" title="Limpiar" hidden><i class="fas fa-plus" ></i> Nueva Revisión</button>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-secondary btn-sm" id="cerrarEt1" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar</button>
                        </div>
                    </div>
                </form>
                <small class="text-danger">Nota: En caso de ver un número en el campo folio, estas editando un registro.</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Etapa 3 -->
<div class="modal fade" id="modaletapa3" tabindex="-1" aria-labelledby="modaletapa3ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modaletapa3ModalLabel">New message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Solo para efectos de programacion, siempre oculto -->
                    <div class="col-1" hidden>
                        <small>Folio</small>
                        <input type="number" class="form-control form-control-sm" id="folioetap3" readonly>
                    </div>
                    <!-- Solo para efectos de programacion, siempre oculto -->
                    <div class="col-2">
                            <small class="fw-bold">No. Reporte</small>
                            <input type="text" class="form-control form-control-sm" id="noReporteEtapa3" name="" readonly>
                        </div>
                    <div class="col-5">
                        <small>Eventos Previos</small>
                        <textarea class="form-control form-control-sm" id="eventosprev"></textarea>
                    </div>
                    <div class="col-5">
                        <small>Incidente / Evento de falla/ Desviación</small>
                        <textarea class="form-control form-control-sm" id="eventofalla"></textarea>
                    </div>
                    <h5>Cuantificacion de daños</h5>
                    <div class="col-2">
                        <small>Daños a equipo</small>
                        <input type="number" class="form-control form-control-sm" value="0" id="equipos" />
                    </div>
                    <div class="col-2">
                        <small>Suspensión de operaciones</small>
                        <input type="number" class="form-control form-control-sm" value="0" id="operacion" />
                    </div>
                    <div class="col-2">
                        <small>Pérdida de producto</small>
                        <input type="number" class="form-control form-control-sm" value="0" id="producto" />
                    </div>
                    <div class="col-2">
                        <small>Perdida de material</small>
                        <input type="number" class="form-control form-control-sm" value="0" id="material" />
                    </div>
                    <div class="col-2">
                        <small>Otro</small>
                        <input type="number" class="form-control form-control-sm" value="0" id="otro" />
                    </div>
                    <div class="col-2">
                        <small>Explique</small>
                        <input type="text" class="form-control form-control-sm" id="otroexplique" />
                    </div>

                    <br>
                    <!-- Busqueda por nombres -->
                    <h5>Acciones de Contención</h5>
                    <div class="col-6">
                        <small>Descripción</small>
                        <input type="text" class="form-control form-control-sm" id="descp1" />
                    </div>
                    <div class="col-1">
                        <small>Responsable</small>
                        <input type="number" class="form-control form-control-sm" id="responsable1" />
                    </div>
                    <div class="col-3">
                        <small> Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="responsablenombre1" readonly />
                    </div>
                    <div class="col-2">
                        <small>Fecha Implementación</small>
                        <input type="date" class="form-control form-control-sm" id="fechaimp1" />
                    </div>
                    <div class="col-6">
                        <small>Descripción</small>
                        <input type="text" class="form-control form-control-sm" id="descp2" />
                    </div>
                    <div class="col-1">
                        <small>Responsable</small>
                        <input type="number" class="form-control form-control-sm" id="responsable2" />
                    </div>
                    <div class="col-3">
                        <small> Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="responsablenombre2" readonly />
                    </div>
                    <div class="col-2">
                        <small>Fecha Implementación</small>
                        <input type="date" class="form-control form-control-sm" id="fechaimp2" />
                    </div>
                    <div class="col-6">
                        <small>Descripción</small>
                        <input type="text" class="form-control form-control-sm" id="descp3" />
                    </div>
                    <div class="col-1">
                        <small>Responsable</small>
                        <input type="number" class="form-control form-control-sm" id="responsable3" />
                    </div>
                    <div class="col-3">
                        <small> Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="responsablenombre3" readonly />
                    </div>
                    <div class="col-2">
                        <small>Fecha Implementación</small>
                        <input type="date" class="form-control form-control-sm" id="fechaimp3" />
                    </div>
                </div>
                <div class="row justify-content-end m-2">
                    <div class="col-2"> <button class="btn btn-sm bg-target" id="saveetapa3"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div>
                    <div class="col-2"> <button class="btn btn-sm btn-secondary" id="limpiaretapa3"><i class="fa-solid fa-rotate-left"></i> Limpiar</button></div>
                </div>
                <div class="row table-responsive border">
                    <table class="table table-bordered" style="height: 280px;">
                        <thead class="table-dark">
                            <th># Reporte</th>
                            <th>Eventos Previos</th>
                            <th>Incidente</th>
                            <th>Acción 1</th>
                            <th>Nombre</th>
                            <th>Acción 2</th>
                            <th>Nombre</th>
                            <th>Acción 3</th>
                            <th>Nombre</th>
                            <th></th>
                        </thead>
                        <tbody id="tbletapa3">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Etapa 4 -->
<div class="modal fade" id="modaletapa4" tabindex="-1" aria-labelledby="modaletapa4ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modaletapa4ModalLabel">New message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="row">
                        <!-- Campo solo para fines de programacion, siempre oculto -->
                        <div class="col-1" hidden>
                            <small class="fw-bold">Folio </small>
                            <input type="text" id="folioetapa4" class="form-control form-control-sm" readonly>
                        </div>
                        <!-- Campo solo para fines de programacion, siempre oculto -->
                        <div class="col-2">
                            <small class="fw-bold">No. Reporte</small>
                            <input type="text" class="form-control form-control-sm" id="noReporteEtapa4" name="" readonly>
                        </div>
                        <div class="col-3">
                            <small class="fw-bold">Causas inmediatas</small>
                            <select class="form-control form-control-sm" id="comportamiento">
                            </select>
                        </div>
                        <div class="col-3">
                            <small class="fw-bold">subcausa</small>
                            <select class="form-control form-control-sm" id="causainmediata">
                            </select>
                        </div>
                        <div class="col-4">
                            <small class="fw-bold">¿Por qué? </small>
                            <input type="text" id="porquecausa" class="form-control form-control-sm">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Causas basicas</small>
                            <select class="form-control form-control-sm" id="causabasica">
                            </select>
                        </div>
                        <div class="col-6">
                            <small class="fw-bold">¿Por qué?</small>
                            <input type="text" id="1porque" class="form-control form-control-sm">
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Causa raíz</small>
                            <select class="form-control form-control-sm" id="causaraiz">
                            </select>
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">¿Por qué?</small>
                            <input type="text" id="porqueraiz" class="form-control form-control-sm">
                        </div>
                        <!-- Primera accion correctiva -->
                        <div class="col-6">
                            <small class="fw-bold">Acción correctiva</small>
                            <input type="text" id="accioncorrectiva" class="form-control form-control-sm">
                        </div>
                        <div class="col-1">
                            <small class="fw-bold">Reponsable</small>
                            <input type="number" id="responsableetapa4" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <small class="fw-bold">Nombre</small>
                            <input type="text" id="responsablenombreetapa4" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Fecha implementación</small>
                            <input type="date" id="fechaac" class="form-control form-control-sm">
                        </div>
                        <!-- Segunda accion correctiva -->
                        <div class="col-6">
                            <small class="fw-bold">Acción correctiva</small>
                            <input type="text" id="accioncorrectiva2" class="form-control form-control-sm">
                        </div>
                        <div class="col-1">
                            <small class="fw-bold">Reponsable</small>
                            <input type="number" id="responsableetapa42" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <small class="fw-bold">Nombre</small>
                            <input type="text" id="responsablenombreetapa42" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Fecha implementación</small>
                            <input type="date" id="fechaac2" class="form-control form-control-sm">
                        </div>
                        <!-- Tercera accion correctiva -->
                        <div class="col-6">
                            <small class="fw-bold">Acción correctiva</small>
                            <input type="text" id="accioncorrectiva3" class="form-control form-control-sm">
                        </div>
                        <div class="col-1">
                            <small class="fw-bold">Reponsable</small>
                            <input type="number" id="responsableetapa43" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <small class="fw-bold">Nombre</small>
                            <input type="text" id="responsablenombreetapa43" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Fecha implementación</small>
                            <input type="date" id="fechaac3" class="form-control form-control-sm">
                        </div>
                        <!-- Cuarta accion correctiva -->
                        <div class="col-6">
                            <small class="fw-bold">Acción correctiva</small>
                            <input type="text" id="accioncorrectiva4" class="form-control form-control-sm">
                        </div>
                        <div class="col-1">
                            <small class="fw-bold">Reponsable</small>
                            <input type="number" id="responsableetapa44" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <small class="fw-bold">Nombre</small>
                            <input type="text" id="responsablenombreetapa44" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Fecha implementación</small>
                            <input type="date" id="fechaac4" class="form-control form-control-sm">
                        </div>
                        <!-- Quinta accion correctiva -->
                        <div class="col-6">
                            <small class="fw-bold">Acción correctiva</small>
                            <input type="text" id="accioncorrectiva5" class="form-control form-control-sm">
                        </div>
                        <div class="col-1">
                            <small class="fw-bold">Reponsable</small>
                            <input type="number" id="responsableetapa45" class="form-control form-control-sm">
                        </div>
                        <div class="col-3">
                            <small class="fw-bold">Nombre</small>
                            <input type="text" id="responsablenombreetapa45" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-2">
                            <small class="fw-bold">Fecha implementación</small>
                            <input type="date" id="fechaac5" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="row justify-content-end m-2">
                    <div class="col-2"> <button class="btn btn-sm bg-target" id="saveetapa4"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div>
                    <div class="col-2"> <button class="btn btn-sm btn-secondary" id="limpiaretapa4"><i class="fa-solid fa-rotate-left"></i> Limpiar</button></div>
                </div>
                <div class="row table-responsive border">
                    <table class="table table-bordered text-center" style="height: 280px;">
                        <thead class="table-dark">
                            <th># Reporte</th>
                            <th>Causas inmediatas</th>
                            <th>Causa</th>
                            <th>Porque</th>
                            <th>Causa básica</th>
                            <th>Cauza raíz</th>
                            <th>Acción correctiva</th>
                            <th>Responsable</th>
                            <th>Fecha implementación</th>
                            <th></th>
                        </thead>
                        <tbody id="tbletapa4">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Etapa 5 -->
<div class="modal fade" id="modaletapa5" tabindex="-1" aria-labelledby="modaletapa5ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modaletapa5ModalLabel">New message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idEtapa5" id="idEtapa5">
                <div class="row">
                    <div class="col-6">
                        <p class="fw-bold">¿Qué estados y errores estan asociados con la decisión / comportamiento?</p>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="incprisa">
                                    <label class="form-check-label" for="incprisa">Prisa</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="incojostarea">
                                    <label class="form-check-label" for="incojostarea">Ojos y mente no en la tarea</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="frustracion">
                                    <label class="form-check-label" for="frustracion">Frustracion</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="mente">
                                    <label class="form-check-label" for="mente">Mente no en la tarea</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="fatiga">
                                    <label class="form-check-label" for="fatiga">Fatiga</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="peligro">
                                    <label class="form-check-label" for="peligro">Colocarse en la zona de peligro</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="riesgo">
                                    <label class="form-check-label" for="riesgo">Tolerancia el Riesgo</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="equilibrio">
                                    <label class="form-check-label" for="equilibrio">Perdida del equilñibrio, tracción y agarre</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <p class="fw-bold">Interacciones 1 a 1</p>
                        <div class="row">
                            <div class="col-9">
                                ¿Tuvo una conversación con el(los) colaborador(es) implicado(s)?
                            </div>
                            <div class="col-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion1" id="radioSiE1" value="1">
                                    <label class="form-check-label" for="radioSiE1">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion1" id="radioNoE1" value="0" checked>
                                    <label class="form-check-label" for="radioNoE1">No</label>
                                </div>
                            </div>
                            <div class="col-9">
                                ¿Reconoció las acciones y decisiones seguras realizadas?
                            </div>
                            <div class="col-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion2" id="radioSi2E2" value="1">
                                    <label class="form-check-label" for="radioSi2E2">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion2" id="radioNo2E2" value="0" checked>
                                    <label class="form-check-label" for="radioNo2E2">No</label>
                                </div>
                            </div>
                            <div class="col-9">
                                ¿Generó acuerdos con la(s) persona(s) observada(s)?
                            </div>
                            <div class="col-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion3" id="radioSi3" value="1">
                                    <label class="form-check-label" for="radioSi3">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion3" id="radioNo3" value="0" checked>
                                    <label class="form-check-label" for="radioNo3">No</label>
                                </div>
                            </div>
                            <div class="col-9">
                                ¿Proporcionó una retroalimentación positiva a la(s) persona(s)?
                            </div>
                            <div class="col-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion4" id="radioSi4" value="1">
                                    <label class="form-check-label" for="radioSi4">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion4" id="radioNo4" value="0" checked>
                                    <label class="form-check-label" for="radioNo4">No</label>
                                </div>
                            </div>
                            <div class="col-9">
                                ¿Se modificó la experiencia de la(s) persona(s) durante la retroalimentación?
                            </div>
                            <div class="col-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion5" id="radioSi5" value="1">
                                    <label class="form-check-label" for="radioSi5">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion5" id="radioNo5" value="0" checked>
                                    <label class="form-check-label" for="radioNo5">No</label>
                                </div>
                            </div>
                            <div class="col-9">
                                ¿Requiere observación de seguimiento? ¿Cuándo? Inmediato
                            </div>
                            <div class="col-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion6" id="radioSi6" value="1">
                                    <label class="form-check-label" for="radioSi6">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="interaccion6" id="radioNo6" value="0" checked>
                                    <label class="form-check-label" for="radioNo6">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-8">
                        ¿Si el incidente ocurrió en un equipo o maquinaria, éste cuenta con una evaluación de riesgos?
                    </div>
                    <div class="col-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="riesgos1" id="radioSi7" value="1">
                            <label class="form-check-label" for="radioSi7">Sí</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="riesgos1" id="radioNo7" value="0" checked>
                            <label class="form-check-label" for="radioSi7">No</label>
                        </div>
                    </div>
                    <div class="col-2">
                        <input type="text" class="form-control form-control-sm" id="riesgos1porque">
                    </div>
                    <div class="col-8">
                        ¿La evaluación de riesgos de la máquina o equipo considera la exposición al peligro y/o escenario de riesgo?
                    </div>
                    <div class="col-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="riesgos2" id="radioSi8" value="1">
                            <label class="form-check-label" for="radioNo8">Sí</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="riesgos2" id="radioNo8" value="0" checked>
                            <label class="form-check-label" for="radioNo8">No</label>
                        </div>
                    </div>
                    <div class="col-2">
                        <input type="text" class="form-control form-control-sm" id="riesgos2porque">
                    </div>
                    <div class="col-8">
                        ¿Se cuenta con un análisis de riesgos y procedimiento de operación de la tarea que se estaba ejecutando?
                    </div>
                    <div class="col-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="riesgos3" id="radioSi9" value="1">
                            <label class="form-check-label" for="radioSi9">Sí</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="riesgos3" id="radioNo9" value="0" checked>
                            <label class="form-check-label" for="radioNo9">No</label>
                        </div>
                    </div>
                    <div class="col-2">
                        <input type="text" class="form-control form-control-sm" id="riesgos3porque">
                    </div>
                    <div class="col-8">
                        ¿El Análisis de Riesgos y Procedimiento de Operación Estándar consideran el escenario de riesgo relacionado con el incidente ocurrido?
                    </div>
                    <div class="col-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="riesgos4" id="radioSi10" value="1">
                            <label class="form-check-label" for="radioSi10">Sí</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="riesgos4" id="radioNo10" value="0" checked>
                            <label class="form-check-label" for="radioNo10">No</label>
                        </div>
                    </div>
                    <div class="col-2">
                        <input type="text" class="form-control form-control-sm" id="riesgos4porque">
                    </div>
                </div>
                <div class="row">
                    <div class="row justify-content-end m-2">
                        <div class="col-2"> <button class="btn btn-sm bg-target" id="saveetapa5"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div>
                        <div class="col-2"> <button class="btn btn-sm btn-secondary" id="limpiaretapa5"><i class="fa-solid fa-rotate-left"></i> Limpiar</button></div>
                    </div>
                    <!-- <div class="table-responsive" style="height: 120px;">
                        <table class="table">
                            <thead class="table-dark">
                                <th>Elemento</th>
                                <th>SubGestion</th>
                                <th>Porque</th>
                                <th>Ojos en la tarea</th>
                                <th>Prisa</th>
                                <th>Frustracion</th>
                                <th></th>
                            </thead>
                            <tbody id="tbletapa5">
                            </tbody>
                        </table>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Etapa 6 -->
<div class="modal fade" id="modaletapa6" tabindex="-1" aria-labelledby="modaletapa6ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modaletapa6ModalLabel">Evaluacion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idEtapa6" id="idEtapa6">
                <div class="row">
                    <div class="col">
                        <small>Elemento de sistema de gestion</small>
                        <select class="form-control form-control-sm" id="sistemagestion">
                            <option>Mentalidades,comportamientos y capacidades</option>
                            <option>Reduccion</option>
                            <option>Sistemas funcionales</option>
                        </select>
                    </div>
                    <div class="col">
                        <small>Sub gestion</small>
                        <select class="form-control form-control-sm" id="sistemagestionsub">
                            <option>Mentalidades,comportamientos y capacidades</option>
                            <option>Reduccion</option>
                            <option>Sistemas funcionales</option>
                        </select>
                    </div>

                    <div class="col">
                        <small>Por que</small>
                        <input type="text" class="form-control form-control-sm" id="sistemagestionporque">
                    </div>
                </div>
                <div class="row justify-content-end m-2">
                    <div class="col-2"> <button class="btn btn-sm bg-target" id="saveetapa6"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div>
                    <div class="col-2"> <button class="btn btn-sm btn-secondary" id="limpiaretapa6"><i class="fa-solid fa-rotate-left"></i> Limpiar</button></div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-dark">
                            <th># Reporte</th>
                            <th>Elemento</th>
                            <th>SubGestion</th>
                            <th>Porque</th>
                            <th></th>
                        </thead>
                        <tbody id="tbletapa6">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Etapa 7 -->
<div class="modal fade" id="modaletapa7" tabindex="-1" aria-labelledby="modaletapa7ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modaletapa7ModalLabel">Evaluacion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idEtapa7" id="idEtapa7">
                <div class="row">
                    <div class="col-2"><small>NoEmp</small><input class="form-control form-control-sm" type="number" id="noempEtapa7"></div>
                    <div class="col"><small>Nombre</small><input class="form-control form-control-sm" type="text" id="nombreEtapa7" readonly></div>
                    <div class="col"><small>Area</small><input class="form-control form-control-sm" type="text" id="areaEtapa7" readonly></div>
                    <div class="col"><small>Puesto</small><input class="form-control form-control-sm" id="puestoEtapa7" readonly></div>
                    <div class="col-1"><br>
                    <button class="btn btn-sm bg-target" id="saveEtapa7"><i class="fa-solid fa-floppy-disk"></i></button>
                    <button class="btn btn-sm btn-secondary" id="limpiarEtapa7"><i class="fa-solid fa-rotate-left"></i></button></div>
                </div>
                <div class="table-responsive mt-2">
                    <table class="table table-sm table-bordered">
                        <thead class="table-dark">
                            <th># Reporte</th>
                            <th>NoEmp</th>
                            <th>Nombre</th>
                            <th>Area</th>
                            <th>Puesto</th>
                            <th></th>
                        </thead>
                        <tbody id="tbletapa7">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Etapa 8 -->
<div class="modal fade" id="modaletapa8" tabindex="-1" aria-labelledby="modaletapa8ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modaletapa5ModalLabel">Evaluacion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idEtapa8" id="idEtapa8">
                <div class="row">
                    <div class="col-2"><small>NoEmp</small><input class="form-control form-control-sm" type="number" id="noEmpEtapa8"></div>
                    <div class="col"><small>Nombre</small><input class="form-control form-control-sm" type="text" id="nombreEtapa8" readonly></div>
                    <div class="col"><small>Puesto</small><input class="form-control form-control-sm" type="text" id="puestoEtapa8" readonly></div>
                    <div class="col"><small>Tipo</small><select class="form-control form-control-sm" id="tipoEvalua"></select></div>
                    <div class="col-1"><br>
                    <button class="btn btn-sm bg-target" id="saveEtapa8"><i class="fa-solid fa-floppy-disk"></i></button>
                    <button class="btn btn-sm btn-secondary" id="limpiarEtapa8"><i class="fa-solid fa-rotate-left"></i></button></div>
                </div>
                <div class="table-responsive mt-2">
                    <table class="table table-sm table-bordered">
                        <thead class="table-dark">
                            <th># Reporte</th>
                            <th>NoEmp</th>
                            <th>Nombre</th>
                            <th>Area</th>
                            <th>Tipo</th>
                            <th></th>
                        </thead>
                        <tbody id="tbletapa8">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Etapa 6 -->
<!-- Elaboracion y revision -->

<!-- datos basicos empleado Elaboracion y revision del reporte de investigacion multiples-->

<!-- Aprovacion Supervisor - jefe de departamento - ing Proceso/mantenimiento - gerente de area - gerente de mantenimiento - comision de seguridad - lider ehs - gerente del sitio -->
<?php require_once("../index/footer.php") ?>
<script src="js/home.js" type="module"></script>