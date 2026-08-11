<?php
require_once("../Session/seguridad.php");
require_once("../indexmaquina/header.php");
require_once(__DIR__ . "/../WR/utils/utils.php");
?>

<style>
    .list-group-item-new {
        position: relative;
        display: block;
        color: #212529;
        text-decoration: none;
        background-color: #fff;
        border: 1px solid rgba(0, 0, 0, 0.125);
        margin-bottom: 5px;
        border-top-left-radius: inherit;
        border-top-right-radius: inherit;
    }

    .container-pesos {
        display: flex;
        flex-wrap: wrap;
    }

    .result {
        margin-top: 20px;
        font-weight: bold;
    }

    .form-control-new {
        width: 100px;
        margin: 5px;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .border-success-new {
        border-color: #0f5132 !important;
        box-shadow: 2px 1px 19px 1px rgba(15, 81, 50, 0.6);
        -webkit-box-shadow: 2px 1px 19px 1px rgba(15, 81, 50, 0.6);
        -moz-box-shadow: 2px 1px 19px 1px rgba(15, 81, 50, 0.6);
    }

    @media (prefers-reduced-motion: reduce) {
        .form-control-new {
            transition: none;
        }
    }

    .tabla-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    .table {
        border-radius: 8px;
        overflow: hidden;
    }

    .table-header-kc {
        background-color: #002B75 !important;
        color: white !important;
        font-weight: 600;
        text-align: center;
        border: none;
        padding: 12px 8px;
    }

    .table thead tr:first-child th:first-child {
        border-top-left-radius: 8px;
    }

    .table thead tr:first-child th:last-child {
        border-top-right-radius: 8px;
    }

    .table-striped>tbody>tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }

    .table-striped>tbody>tr:hover {
        background-color: #e8f0ff;
    }

    tbody tr td {
        vertical-align: middle;
        padding: 12px 8px;
        text-align: center;
        border-color: #e0e0e0;
    }

    .row-total {
        background-color: #e8e8e8 !important;
        color: #333;
        font-weight: 700;
    }

    .row-total td {
        color: #333;
        border-color: #e8e8e8;
    }

    .table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 8px;
    }

    .table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 8px;
    }
</style>

<link rel="stylesheet" href="./css/estilosWR.css">
<link rel="stylesheet" href="./css/estilosLS.css">

<!--  Contenido  -->
<div class="container p-3" style="max-width: 90%;">
    <div class="row justify-content-end">
        <div class="col-2">
            <span class="bg-warning" id="editando"></span>
            <input type="hidden" id="idregconsultado">
        </div>
    </div>


    <section id="pagina1" class="section tabla-container">

        <!-- Asistencias -->
        <div class="row">
            <div class="col-5">
                <div class="row">
                    <div class="col-8">
                        <h5 class="tittlecont">ASISTENCIA</h5>
                    </div>
                    <div class="col-4">
                        <button type="button" class="btn btn-sm bg-target" data-bs-toggle="modal"
                            id="asistenciasModalid" data-bs-target="#asistenciasModal">
                            <i class="fas fa-user-plus"></i> Nuevo registro
                        </button>
                    </div>
                </div>
                <div class="table-responsive" style="height: 380px;">
                    <table class="table table-striped table-hover border">
                        <thead>
                            <th class="table-header-kc">NoEmp</th>
                            <th class="table-header-kc">Nombre</th>
                            <th class="table-header-kc">Puesto</th>
                            <th class="table-header-kc"></th>
                        </thead>
                        <tbody id="tblasistencias">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-7">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="tittlecont">CORRUGADOS</h5>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-sm bg-target" data-bs-toggle="modal"
                                    id="CorrugadosModalid" data-bs-target="#CorrugadosModal">
                                    <i class="fas fa-plus-square"></i> Nuevo registro
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive" style="height: 100px;">
                            <table class="table table-striped table-hover border">
                                <thead>
                                    <th class="table-header-kc">Folio</th>
                                    <th class="table-header-kc">Cajas recibidas</th>
                                    <th class="table-header-kc">Corrugados de almacen</th>
                                    <th class="table-header-kc">Cajas producidas</th>
                                    <th class="table-header-kc">Cajas entregadas</th>
                                    <th class="table-header-kc">Clave de producto</th>
                                </thead>
                                <tbody id="tblcorrugados">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="row">
                            <div class="col-6">
                                <h5 class="tittlecont">INDICADORES</h5>
                            </div>
                        </div>
                        <div class="table-responsive" style="height: 100px;">
                            <table class="table table-striped table-hover border">
                                <thead>
                                    <th class="table-header-kc">Turno</th>
                                    <th class="table-header-kc">Clave</th>
                                    <th class="table-header-kc">STD</th>
                                    <th class="table-header-kc">Merma</th>
                                    <th class="table-header-kc">TPT</th>
                                    <th class="table-header-kc">S+D</th>
                                </thead>
                                <tbody id="tblindicadores">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="tittlecont">CALIDAD</h5>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-sm bg-target" data-bs-toggle="modal"
                                    id="CalidadModalid" data-bs-target="#CalidadModal">
                                    <i class="fas fa-plus-square"></i> Nuevo registro
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive" style="height: 100px;">
                            <table class="table table-striped table-hover border">
                                <thead class="table-dark text-center">
                                    <th class="table-header-kc">Inspeccionados</th>
                                    <th class="table-header-kc">S+D</th>
                                    <th class="table-header-kc">QL</th>
                                    <th class="table-header-kc">Observación</th>
                                    <th class="table-header-kc"></th>
                                </thead>
                                <tbody id="tblcalidadsd">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="row">
                <div class="col-6"><small>Horas trabajadas</small></div>
                <div class="row">
                    <div class="col-9">
                        <input type="number" id="horaNuevoParo" class="form-control form-control-sm" step="0.5" min="0"
                            max="8.5">
                    </div>
                    <div class="col-3">
                        <button class="btn btn-sm bg-target" id="guardarHorasTrabajadas"><i
                                class="fas fa-save"></i></button>
                    </div>
                </div>

            </div>
        </div>

        <div class="row">
            <!-- Otras areas -->
            <div id="paginaAreas" class="col-11" style="width: 80%;">
                <div class="row">
                    <div class="col-3" style="width: 25%;">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion1"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm bg-target" id="savePresentacion1"><i
                                        class="fas fa-play"></i></button></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion1"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion1" class="table table-bordered">
                                    <thead class="table-dark">
                                        <th class="table-header-kc">Hora</th>
                                        <th class="table-header-kc">CajasR</th>
                                        <th class="table-header-kc">Piezas</th>
                                        <th class="table-header-kc">AcumR</th>
                                        <th class="table-header-kc">USTD</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub1">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-3" style="width: 25%;">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion2"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm bg-target" id="savePresentacion2"><i
                                        class="fas fa-play"></i></button></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion2"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion2" class="table table-bordered">
                                    <thead class="table-dark">
                                        <th class="table-header-kc">Hora</th>
                                        <th class="table-header-kc">CajasR</th>
                                        <th class="table-header-kc">Piezas</th>
                                        <th class="table-header-kc">AcumR</th>
                                        <th class="table-header-kc">USTD</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub2">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-3" style="width: 25%;">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion3"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm bg-target" id="savePresentacion3"><i
                                        class="fas fa-play"></i></button></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion3"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion3" class="table table-bordered">
                                    <thead class="table-dark">
                                        <th class="table-header-kc">Hora</th>
                                        <th class="table-header-kc">CajasR</th>
                                        <th class="table-header-kc">Piezas</th>
                                        <th class="table-header-kc">AcumR</th>
                                        <th class="table-header-kc">USTD</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub3">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-3" style="width: 25%;">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion4"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm bg-target" id="savePresentacion4"><i
                                        class="fas fa-play"></i></button></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion4"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion4" class="table table-bordered">
                                    <thead class="table-dark">
                                        <th class="table-header-kc">Hora</th>
                                        <th class="table-header-kc">CajasR</th>
                                        <th class="table-header-kc">Piezas</th>
                                        <th class="table-header-kc">AcumR</th>
                                        <th class="table-header-kc">USTD</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub4">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Telas Tabbi-->
            <div id="pagina1telas" class="col-10">
                <div class="row">
                    <div class="col-3" style="width: 33%;">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion1telas"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm bg-target" id="savePresentacion1telas"><i
                                        class="fas fa-play"></i></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion1telas"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion1telas" class="table table-bordered">
                                    <thead class="table-dark">
                                        <th># Bajada</th>
                                        <th>ML</th>
                                        <th hidden>Acum MML</th>
                                        <th>MM2</th>
                                        <th>Peso Total</th>
                                        <th>Acum MM2</th>
                                        <th>Acum KG</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub1telas">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-3" style="width: 33%;">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion2telas"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm bg-target" id="savePresentacion2telas"><i
                                        class="fas fa-play"></i></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion2telas"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion2telas" class="table table-bordered">
                                    <thead class="table-dark">
                                        <th># Bajada</th>
                                        <th>ML</th>
                                        <th hidden>Acum MML</th>
                                        <th>MM2</th>
                                        <th>Peso Total</th>
                                        <th>Acum MM2</th>
                                        <th>Acum KG2</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub2telas">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-3" style="width: 33%;">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion3telas"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm bg-target" id="savePresentacion3telas"><i
                                        class="fas fa-play"></i></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion3telas"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion3telas" class="table table-bordered">
                                    <thead class="table-dark">
                                        <th># Bajada</th>
                                        <th>ML</th>
                                        <th hidden>Acum MML</th>
                                        <th>MM2</th>
                                        <th>Peso Total</th>
                                        <th>Acum MM2</th>
                                        <th>Acum KG2</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub3telas">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Telas Spooler -->
            <!-- TABS de Presentaciones -->
            <div id="paginaSpooler" class="p-4 col-10">
                <ul class="nav nav-tabs mb-3" id="navPresentaciones">
                    <li class="nav-item">
                        <button class="nav-link active" onclick="_bitacora.setPresentacion(1)">Presentación 1</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" onclick="_bitacora.setPresentacion(2)">Presentación 2</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" onclick="_bitacora.setPresentacion(3)">Presentación 3</button>
                    </li>
                </ul>
                <div class="row">
                    <!-- Tabla lado izquierdo -->
                    <div class="col-4" style="width: 33%;">
                        <div class="row">
                            <div class="row">
                                <!-- Selector de clave -->
                                <div class="col-7">
                                    <small>Presentacion</small>
                                    <select id="selectClave" class="form-select form-select-sm"
                                        onchange="_bitacora.cambiarClave(this.value)">
                                    </select>
                                </div>
                                <!-- Botón de guardar -->
                                <div class="col-1">
                                    <br>
                                    <button class="btn btn-sm bg-target" id="btnPlay" onclick="_bitacora.play()"><i
                                            class="fas fa-play"></i></button>
                                </div>
                                <div class="col-1">
                                    <br>
                                    <button class="btn btn-sm btn-danger" id="resetPresentacion1Spooler"><i
                                            class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="col-3">
                                    <br>
                                    <button class="btn btn-sm btn-primary" onclick="_bitacora.agregarRollo()"><i
                                            class="fas fa-plus"></i> Agregar Rollo
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion1Spooler" class="table table-bordered">
                                    <thead class="table-dark">
                                        <th># Rollo</th>
                                        <th>Peso Total (KG)</th>
                                        <th>Metros Lineales</th>
                                    </thead>
                                    <tbody id="tblRollos">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card col-8" style="padding: 0;">
                        <div class="card-header">
                            <strong>Nueva Bajada</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-2">
                                    <label class="form-label form-label-sm mb-1"># Bajada</label>
                                    <input id="bajada" class="form-control form-control-sm" placeholder="# Bajada">
                                </div>
                                <div class="col-2">
                                    <label class="form-label form-label-sm mb-1">Bobinas</label>
                                    <input id="bobinas" type="number" class="form-control form-control-sm"
                                        placeholder="Bobinas" oninput="_bitacora.recalcular()">
                                </div>
                                <div class="col-2">
                                    <label class="form-label form-label-sm mb-1">Peso Total</label>
                                    <input id="inpKgTotal" class="form-control form-control-sm bg-light" disabled
                                        placeholder="0.00">
                                </div>
                                <div class="col-2">
                                    <label class="form-label form-label-sm mb-1">ML Bajada</label>
                                    <input id="inpMlBajada" class="form-control form-control-sm bg-light" disabled
                                        placeholder="0.00">
                                </div>
                                <div class="col-2">
                                    <label class="form-label form-label-sm mb-1">MM² Bajada</label>
                                    <input id="inpMm2Bajada" class="form-control form-control-sm bg-light" disabled
                                        placeholder="0.000">
                                </div>
                                <div class="col-2">
                                    <label class="form-label form-label-sm mb-1">Ancho(mm)</label>
                                    <input type="number" class="form-control form-control-sm" id="anchoSpooler1"
                                        disabled>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-2">
                                    <label class="form-label form-label-sm mb-1">KG Merma</label>
                                    <input type="number" class="form-control form-control-sm" id="kgmermaSpooler1"
                                        disabled>
                                </div>
                                <div class="col-2">
                                    <label class="form-label form-label-sm mb-1">Peso Base</label>
                                    <input type="number" class="form-control form-control-sm" id="pesobaseSpooler1"
                                        disabled>
                                </div>
                                <div class="col-4">
                                    <label class="form-label form-label-sm mb-1">Comentarios</label>
                                    <input id="comentarios" class="form-control form-control-sm" placeholder="Opcional">
                                </div>
                                <div class="col-2">
                                    <br>
                                    <button class="btn btn-primary btn-sm" id="btnSaveDataSpooler1"
                                        onclick="_bitacora.guardar()"><i class="fas fa-save"></i> Guardar</button>
                                </div>
                            </div>
                            <div class="table-responsive my-2">
                                <table id="tblClaveSpooler1" class="table table-bordered">
                                    <thead class="table-dark">
                                        <th># Bajada</th>
                                        <th># Bobinas</th>
                                        <th>KG Carretes Bajada</th>
                                        <th>ML Bajada</th>
                                        <th>MM2 Bajada</th>
                                        <th>KG Merma Bajada</th>
                                    </thead>
                                    <tbody id="tblHistorial">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  Fin Spooler -->
            <!-- Hookmesh-->
            <div id="paginaHook" class="col-10" style="width: 98%;">
                <div class="row">
                    <div class="col-3">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion1Hook"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion1Hook"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion1Hook" class="table table-striped table-hover border">
                                    <thead class="table-header-kc">
                                        <th>#Rollo</th>
                                        <th>ML</th>
                                        <th>MM2</th>
                                        <th>Acum ML</th>
                                        <th>Acum MM2</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub1Hook">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion2Hook"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion2Hook"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion2Hook" class="table table-striped table-hover border">
                                    <thead class="table-header-kc">
                                        <th>#Rollo</th>
                                        <th>ML</th>
                                        <th>MM2</th>
                                        <th>Acum ML</th>
                                        <th>Acum MM2</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub2Hook">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="row">
                            <div class="col-9"><small>Presentacion</small><select class="form-control form-control-sm"
                                    id="presentacion3Hook"></select></div>
                            <div class="col-1"><br><button class="btn btn-sm btn-danger" id="resetPresentacion3Hook"><i
                                        class="fas fa-trash"></i></button></div>
                            <div class="table-responsive my-2" style="height: 300px;">
                                <table id="tablapresentacion3Hook" class="table table-striped table-hover border">
                                    <thead class="table-dark">
                                        <th class="table-header-kc">#Rollo</th>
                                        <th class="table-header-kc">ML</th>
                                        <th class="table-header-kc">MM2</th>
                                        <th class="table-header-kc">Acum ML</th>
                                        <th class="table-header-kc">Acum MM2</th>
                                    </thead>
                                    <tbody id="tblpresentacionsub3Hook">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- ================================================ -->
                    <!-- TABLA DE MERMA HOOK                              -->
                    <!-- Rollos con ML < 1900 de todas las presentaciones -->
                    <!-- ================================================ -->

                    <div class="col-3" id="seccionMermaHook">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0" style="color:#002B75;">
                                    <i class="fas fa-recycle me-1"></i> Rollos candidatos a merma
                                    <small class="text-muted ms-2" style="font-size:0.75rem;">(ML &lt; 1900)</small>
                                </h6>
                                <span id="mermaHookResumen" class="text-muted" style="font-size:0.82rem;"></span>
                            </div>
                            <div class="table-responsive" style="max-height: 260px;">
                                <table class="table table-sm table-striped table-hover border mb-0">
                                    <thead>
                                        <tr>
                                            <th class="table-header-kc" style="width:40px;">
                                                <i class="fas fa-check-square"></i>
                                            </th>
                                            <th class="table-header-kc"># Rollo</th>
                                            <th class="table-header-kc">ML</th>
                                            <th class="table-header-kc">MM²</th>
                                            <th class="table-header-kc">Clave</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tblMermaHookBody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                Carga un folio para ver los rollos
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2 text-end">
                                <button class="btn btn-sm btn-danger" id="btnGuardarMermaHook" disabled>
                                    <i class="fas fa-save me-1"></i> Guardar merma
                                </button>
                            </div>
                        </div>
                    </div>


                </div>
            </div>


            <!-- FIN TABLA DE MERMA HOOK -->

            <div class="col-1" style="width: 20%;">
                <div class="row" id="golpesMaquinaBitacora">
                    <div class="table-responsive my-2" style="height: 280px;">
                        <table id="sumTable" class="table table-bordered">
                            <thead>
                                <th class="table-header-kc">Golpes</th>
                                <th class="table-header-kc">Merma</th>
                            </thead>
                            <tbody id="tblgolpesmermatotal">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php include 'WasteReclaim.php'; ?>

            <?php include 'Liquidos.php'; ?>

            <div class="row" id="rechazosAreas">
                <div class="col-2 offset-10" id="rechazosSeccion">
                    <div class="row">
                        <div class="col-12"><small>Rechazos</small></div>
                        <div class="col-8">
                            <input type="number" id="rechazosTurno" class="form-control form-control-sm" min="0">
                        </div>
                        <div class="col-3">
                            <button class="btn btn-sm bg-target" id="guardarRechazosMaquina"><i
                                    class="fas fa-save"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="rechazosTelas">
                <div class="col-2 offset-10" id="rechazosTelasSeccion">
                    <div class="row">
                        <div class="col-12"><small>KG Rechazados</small></div>
                        <div class="col-8">
                            <input type="number" id="rechazoskgs" class="form-control form-control-sm" min="0">
                        </div>
                        <div class="col-3">
                            <button class="btn btn-sm bg-target" id="guardarRechazosTelas"><i
                                    class="fas fa-save"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- Modal Asistencias-->
    <div class="modal fade" id="asistenciasModal" tabindex="-1" aria-labelledby="asistenciasModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="asistenciasModalLabel">Asistencias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12"><small>Noemp</small><input class="form-control form-control-sm"
                                type="number" id="noempasis"></div>
                        <div class="col-12"><small>Nombre</small><input class="form-control form-control-sm" type="text"
                                id="nombreasis" readonly></div>
                        <div class="col-12"><small>Departamento</small><input class="form-control form-control-sm"
                                type="text" id="departamentoasis" readonly></div>
                        <div class="col-12"><small>Puesto</small><input class="form-control form-control-sm" type="text"
                                id="puestoasis" readonly></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="guardarasistencias" class="btn btn-sm bg-target"><i
                            class="fa-solid fa-floppy-disk"></i> Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Corrugados -->
    <div class="modal fade" id="CorrugadosModal" tabindex="-1" aria-labelledby="CorrugadosModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="CorrugadosModalLabel">Corrugados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6"><small>Cajas recibidas</small><input class="form-control form-control-sm"
                                type="number" id="crecibidas"> </div>
                        <div class="col-6"><small>Corrugados almacen</small><input class="form-control form-control-sm"
                                type="number" id="calmacen"> </div>
                        <div class="col-6"><small>Cajas producidas</small><input class="form-control form-control-sm"
                                type="number" id="cproducidas"> </div>
                        <div class="col-6"><small>Cajas entregadas</small><input class="form-control form-control-sm"
                                type="number" id="centregadas"> </div>
                        <div class="col-6"><small>Clave de producto</small><select class="form-control form-control-sm"
                                id="claveproducto"></select> </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-sm bg-target" id="guardacorrugados"><i
                            class="fa-solid fa-floppy-disk"></i> Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Calidad -->
    <div class="modal fade" id="CalidadModal" tabindex="-1" aria-labelledby="CalidadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="CalidadModalLabel">Calidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input class="form-control form-control-sm" type="hidden" id="idcalidad">
                        <div class="col-4"><small>Inspeccionados</small><input class="form-control form-control-sm"
                                type="number" id="inspeccionados"> </div>
                        <div class="col-4"><small>S+D</small><input class="form-control form-control-sm" type="number"
                                id="sd"> </div>
                        <div class="col-4"><small>QL</small><input class="form-control form-control-sm" type="number"
                                id="ql"> </div>
                        <div class="col-12"><small>Observación</small><textarea class="form-control form-control-sm"
                                id="sdobservaciones"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-sm bg-target" id="guardacalidad"><i
                            class="fa-solid fa-floppy-disk"></i> Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <section id="pagina2" class="section tabla-container">
        <!--Seccion TiemposNuevo-->
        <div class="row" id="tiemposNuevaSeccion">
            <div class="row">
                <div class="col-12">
                    <form id="formctrltiempos">
                        <div class="row">
                            <h4 class="tittlecont">Control de tiempos</h4>
                        </div>
                        <div class="table-responsive my-2" style="height: 300px;">
                            <table class="table table-striped table-hover border">
                                <thead class="table-header-kc">
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Seccion</th>
                                    <th>Modulo</th>
                                    <th>Cortes</th>
                                    <th>Rechazos</th>
                                    <th>Tiempo paro</th>
                                    <th>Motivo</th>
                                    <th>Correccion</th>
                                    <th>Sanitización</th>
                                </thead>
                                <tbody id="tblctrltiempos">
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal fade" id="modalsanitizacion" tabindex="-1" aria-labelledby="modalsanitizacionLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalsanitizacionLabel">Sanitización con folio</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive border" style="height: 200px;">
                                <table class="table table-bordered" id='tblempsanInfo'>
                                    <thead class="table-dark">
                                        <th>NoEmp</th>
                                        <th>Nombre</th>
                                    </thead>
                                    <tbody id="tblEmpSanitizacionInfo"></tbody>
                                </table>
                            </div>
                            <div class="row">
                                <div class="col-8">
                                    <label>Motivo</label>
                                    <select class="form-control  form-control-sm" id="motivoParoSanitizacion" disabled>
                                        <option value="">Selecciona una opción</option>
                                        <option value="1">Componente / Área</option>
                                        <option value="2">Máquina completa (Áreas clave) </option>
                                        <option value="3">Tanques de perfume y extracto </option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label>Tiempo:</label>
                                    <input type="number" class="form-control form-control-sm"
                                        id="tiempoParoSanitizacion" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Fin seccion TiemposNuevo  -->

        <!--Seccion TiemposOld-->
        <div class="row" id="tiemposOldSection">
            <div class="row">
                <div class="col-12">
                    <form id="formctrltiempos">
                        <div class="row">
                            <h4 class="tittlecont">Control de tiempos</h4>
                            <div class="col"><small>De</small><input class="form-control form-control-sm" type="time"
                                    id="horainicio"> </div>
                            <div class="col"><small>A</small> <span id="diftiempo" class="text-danger"></span><input
                                    class="form-control form-control-sm" type="time" id="horafinal"> </div>
                            <div class="col"><small>Operacion</small><input class="form-control form-control-sm"
                                    type="number" id="operacion" value="0"> </div>
                            <div class="col"><small>Electrico</small><input class="form-control form-control-sm"
                                    type="number" id="electrico" value="0"> </div>
                            <div class="col"><small>Mecanico</small><input class="form-control form-control-sm"
                                    type="number" id="mecanico" value="0"> </div>
                            <div class="col"><small>Materias</small><input class="form-control form-control-sm"
                                    type="number" id="materias" value="0"> </div>
                            <div class="col"><small>C Grado</small><input class="form-control form-control-sm"
                                    type="number" id="grado" value="0"> </div>
                            <div class="col"><small>Mant. Prev</small><input class="form-control form-control-sm"
                                    type="number" id="prev" value="0"> </div>
                            <div class="col"><small>Servicios</small><input class="form-control form-control-sm"
                                    type="number" id="servicios" value="0"> </div>
                            <div class="col"><small>Subtotal</small><input class="form-control form-control-sm"
                                    type="number" id="subtotal" readonly> </div><br>

                        </div>
                        <div class="row">
                            <div class="col"><small>Seccion</small><select class="form-control form-control-sm"
                                    id="seccion"></select></div>
                            <div class="col"><small>Modulo</small><select class="form-control form-control-sm"
                                    id="modulo"></select> </div>
                            <div class="col"><small>Motivo</small><textarea class="form-control form-control-sm"
                                    id="motivo"></textarea> </div>
                            <div class="col"><small>Correccion</small><textarea class="form-control form-control-sm"
                                    id="correccion"></textarea> </div>
                            <div class="col-1"><br><button class="btn btn-sm bg-target" id="guardarctrltiempos"><i
                                        class="fa-solid fa-floppy-disk"></i> Guardar</button></div>
                        </div>
                        <div class="table-responsive my-2" style="height: 300px;">
                            <table class="table table-striped table-hover border">
                                <thead class="table-header-kc">
                                    <th>De</th>
                                    <th>A</th>
                                    <th>Operacion</th>
                                    <th>Electrico</th>
                                    <th>Mecanico</th>
                                    <th>Materias</th>
                                    <th>Grado</th>
                                    <th>Prev</th>
                                    <th>Servicios</th>
                                    <th>Subtotal</th>
                                    <th>Seccion</th>
                                    <th>Modulo</th>
                                    <th>Motivo</th>
                                    <th>Correccion</th>
                                    <th></th>
                                </thead>
                                <tbody id="tblctrltiemposOld">
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal fade" id="modalsanitizacionOld" tabindex="-1" aria-labelledby="modalsanitizacionOldLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalsanitizacionOldLabel">Sanitización con folio</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="row mb-1">
                                    <input type="text" class="form-control form-control-sm" id="recipient-name"
                                        hidden="" readonly="">
                                    <div class="col-3">
                                        <label>Noemp:</label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="noempsanitizacionOld">
                                    </div>
                                    <div class="col-8">
                                        <label>Nombre:</label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="nombresanitizacionOld" readonly="">
                                    </div>
                                    <div class="col-1">
                                        <br>
                                        <button class="btn btn-sm btn-success" id="addempsanitizacionOld"><i
                                                class="fa-solid fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="table-responsive border" style="height: 200px;">
                                    <table class="table table-bordered" id="tblempsanOld">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>NoEmp</th>
                                                <th>Nombre</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblEmpSanitizacionOld"></tbody>
                                    </table>
                                </div>
                                <div class="row">
                                    <div class="col-8">
                                        <label>Motivo</label>
                                        <select class="form-control  form-control-sm" id="motivosanitizacionOld">
                                            <option value="">Selecciona una opción</option>
                                            <option value="1">Componente / Área</option>
                                            <option value="2">Máquina completa (Áreas clave) </option>
                                            <option value="3">Tanques de perfume y extracto </option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label>Tiempo:</label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="tiemposanitizacionOld">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-sm bg-target" id="saveSanitizacion"><i
                                    class="fa-solid fa-floppy-disk"></i> Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Comentarios -->
        <form id="formcomentarios">
            <div class="row">
                <h4 class="tittlecont">Comentarios</h4>
                <div class="col"><small>Seguridad</small><textarea class="form-control form-control-sm"
                        id="seguridad"></textarea></div>
                <div class="col"><small>Calidad</small><textarea class="form-control form-control-sm"
                        id="calidadcom"></textarea> </div>
                <div class="col"><small>O y L</small><textarea class="form-control form-control-sm" id="oyl"></textarea>
                </div>
                <div class="col"><small>Pendientes</small><textarea class="form-control form-control-sm"
                        id="pendientes"></textarea></div>
                <div class="col"><small>Otros</small><textarea class="form-control form-control-sm"
                        id="otros"></textarea></div>
                <div class="col-1"><br><button class="btn btn-sm bg-target" id="guardarcomentarios"><i
                            class="fa-solid fa-floppy-disk"></i> Guardar</button></div>

            </div>
        </form>
        <div class="table-responsive my-2" style="height: 200px;">
            <table class="table table-striped table-hover border">
                <thead class="table-header-kc">
                    <th>Folio</th>
                    <th>Seguridad</th>
                    <th>Calidad</th>
                    <th>O y L</th>
                    <th>Pendientes</th>
                    <th>Otros</th>
                    <th></th>
                </thead>
                <tbody id="tblcomentarios">
                </tbody>
            </table>
        </div>

        <!-- Fin seccion Comentarios -->
    </section>



    <!-- Seccion paros automaticos -->
    <section id="sectionctrolTiempos" class="section tabla-container">
        <h4 class="tittlecont">Paros de máquina</h4>
        <!-- <div class="col-2">
            <button type="button" class="btn btn-sm bg-target" data-bs-toggle="modal" data-bs-target="#modalNuevoParo">
                <i class="fas fa-plus-square"></i> Registrar nuevo paro
            </button>
        </div> -->
        <div class="row mt-2">
            <div class="row" id="Tiemposparos"></div>
        </div>
        <!-- Modal Editar Paro -->
        <div class="modal fade" id="modalTiempos" tabindex="-1" aria-labelledby="modalTimeposLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTimeposLabel"></h5>
                        <input type="hidden" id="TiemposParoFolio">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="row">
                                <h5>Informacion general del paro</h5>
                                <div class="col-3">
                                    <small>Seccion</small>
                                    <select id="tiemposSecciones" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="col-3">
                                    <small>Modulo</small>
                                    <select id="TiemposModulo" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="col-3" hidden>
                                    <small>Falla</small>
                                    <select id="TiemposFalla" class="form-control form-control-sm"></select>
                                </div>
                                <div class="col-3">
                                    <small>Cortes</small>
                                    <input type="number" id="TiemposCortes" class="form-control form-control-sm"
                                        readonly />
                                </div>
                                <div class="col-3">
                                    <small>Rechazos</small>
                                    <input type="number" id="TiemposRechazos" class="form-control form-control-sm"
                                        readonly />
                                </div>
                                <div class="col-3">
                                    <small>Tiempo paro</small>
                                    <input type="number" id="Tiempostiempoparo" class="form-control form-control-sm"
                                        readonly />
                                </div>
                                <div class="col-3">
                                    <small>Fecha</small>
                                    <input type="date" id="fechaParo" class="form-control form-control-sm" readonly />
                                </div>
                                <div class="col-3">
                                    <small>Hora</small>
                                    <input type="time" id="horaParo" class="form-control form-control-sm" readonly />
                                </div>
                                <!-- <div class="col-6">
                                    <small>Rechazos corrida</small>
                                    <input type="number" id="Tiemposrechazoscorrida" class="form-control form-control-sm" />
                                </div> -->
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    Motivo
                                    <textarea id="Tiemposmotivos" class="form-control form-control-sm"></textarea>
                                </div>
                                <div class="col-6">
                                    Corrección
                                    <textarea id="Tiemposcorreccion" class="form-control form-control-sm"></textarea>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <h5>Sanitización</h5>
                                <form>
                                    <div class="row mb-1">
                                        <input type="text" class="form-control form-control-sm" id="recipient-name"
                                            hidden readonly>
                                        <div class="col-2">
                                            <label>Noemp:</label>
                                            <input type="number" class="form-control form-control-sm"
                                                id="noempsanitizacionNew">
                                        </div>
                                        <div class="col-5">
                                            <label>Nombre:</label>
                                            <input type="text" class="form-control form-control-sm"
                                                id="nombresanitizacionNew" readonly>
                                        </div>
                                        <div class="col-1">
                                            <br />
                                            <button class="btn btn-sm btn-success" id="addempsanitizacionNew"><i
                                                    class="fa-solid fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="col-12">
                                        <table class="table table-bordered" id="tblempsanNew">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>NoEmp</th>
                                                    <th>Nombre</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tblEmpSanitizacionNew"></tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-8">
                                            <label>Motivo</label>
                                            <select class="form-control  form-control-sm" id="motivosanitizacionNew">
                                                <option value="">Selecciona una opción</option>
                                                <option value="1">Componente / Área</option>
                                                <option value="2">Máquina completa (Áreas clave) </option>
                                                <option value="3">Tanques de perfume y extracto </option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label>Tiempo:</label>
                                            <input type="number" class="form-control form-control-sm"
                                                id="tiemposanitizacionNew">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="UpdatedataParo">Completar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Nuevo Paro -->
        <div class="modal fade" id="modalNuevoParo" tabindex="-1" aria-labelledby="modalNuevoParo" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNuevoParoTitulo">Nuevo paro</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="row">
                                <div class="col-6">
                                    <small>Seccion</small>
                                    <select id="seccionNuevoParo" class="form-control form-control-sm">
                                        <option value="1">Sección A</option>
                                        <option value="2">Sección B</option>
                                        <option value="3">Sección C</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <small>Modulo</small>
                                    <select id="moduloNuevoParo" class="form-control form-control-sm">
                                        <option value="1">Modulo 1</option>
                                        <option value="2">Modulo 2</option>
                                        <option value="3">Modulo 3</option>
                                    </select>
                                </div>
                                <div class="col-6" hidden>
                                    <small>Falla</small>
                                    <select id="fallaNuevoParo" class="form-control form-control-sm"></select>
                                </div>
                                <div class="col-6">
                                    <small>Cortes</small>
                                    <input type="number" id="cortesNuevoParo" class="form-control form-control-sm" />
                                </div>
                                <div class="col-6">
                                    <small>Rechazos</small>
                                    <input type="number" id="rechazosNuevoParo" class="form-control form-control-sm" />
                                </div>
                                <div class="col-6">
                                    <small>Tiempo paro</small>
                                    <input type="number" id="tiempoParoNuevoParo"
                                        class="form-control form-control-sm" />
                                </div>
                                <div class="col-6">
                                    <small>Hora</small>
                                    <input type="datetime-local" id="horaNuevoParo"
                                        class="form-control form-control-sm" />
                                </div>
                                <!-- <div class="col-6">
                                    <small>Rechazos corrida</small>
                                    <input type="number" id="Tiemposrechazoscorrida" class="form-control form-control-sm" />
                                </div> -->
                                <div class="col-12">
                                    Motivo
                                    <textarea id="motivosNuevoParo" class="form-control form-control-sm"></textarea>
                                </div>
                                <div class="col-12">
                                    Corrección
                                    <textarea id="correccionNuevoParo" class="form-control form-control-sm"></textarea>
                                </div>
                                <div class="col-12">
                                    <small>Usuario (IBM)</small>
                                    <input type="number" class="form-control" id="usuarioNuevoParo">
                                </div>
                                <div class="col-12">
                                    <small>Contraseña</small>
                                    <input type="password" class="form-control" id="passwordNuevoParo">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="crearNuevoParo">Completar</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- No conformidad -->
    <section id="sectionnoconformidad" class="section px-2">
        <div id="noconformidad">
            <h4 class="tittlecont">Producto no conforme producción</h4>
            <form>
                <div class="row m-2">
                    <div class="col-1"><small>Folio</small><input type="number" id="folioconf"
                            class="form-control form-control-sm" readonly></div>
                    <div class="col-2"><small>Fecha</small><input type="date" id="fechaconf"
                            class="form-control form-control-sm"></div>
                    <div class="col-3"><small>Departamento</small><select id="depsconf"
                            class="form-control form-control-sm"></select></div>
                    <div class="col-1"><small>Noemp</small><input type="number" id="selladorconfnoemp"
                            class="form-control form-control-sm" /> </div>
                    <div class="col-2"><small>Sellador de Calidad</small><select id="selladorconf"
                            class="form-control form-control-sm"></select></div>
                    <div class="col-1"><small>Noemp</small><input type="number" id="operadorconfnoemp"
                            class="form-control form-control-sm" /> </div>
                    <div class="col-2"><small>Operador</small><select id="operadorconf"
                            class="form-control form-control-sm"></select></div>
                    <div class="col-2"><small>Turno</small><select id="turnoconf" class="form-control form-control-sm">
                        </select></div>
                    <div class="col-3"><small>Producto / clave</small><select id="claveprodconf"
                            class="form-control form-control-sm"></select></div>
                    <div class="col-2"><small>Hora de desvio</small><input type="time" id="horaconf"
                            class="form-control form-control-sm"></div>
                    <div class="col-3"><small>Defecto</small><select id="defectoconf"
                            class="form-control form-control-sm"></select></div>
                    <div class="col-3"><small>Causa de defecto (Que causa el defecto)</small><textarea
                            id="descripcionconf" class="form-control form-control-sm"></textarea></div>
                    <div class="col-2"><small>Total de producto retenido</small><input type="text" id="totalprodconf"
                            class="form-control form-control-sm" id=""></div>
                    <div class="col-2"><small>Producto Recuperado</small><input type="text" id="prodrecuperadoconf"
                            class="form-control form-control-sm" id=""></div>
                    <div class="col-2"><small>Producto a Merma</small><input type="text" id="prodmermaconf"
                            class="form-control form-control-sm" id=""></div>
                    <div class="col-2"><small>Codigo donde empieza el defecto</small><input type="text"
                            id="empdefectioconf" class="form-control form-control-sm" id=""></div>
                    <div class="col-2"><small>Codigo donde termina el defecto</small><input type="text"
                            id="terdefectoconf" class="form-control form-control-sm" id=""></div>
                    <div class="col-1"><small>Noemp</small><input type="number" id="liderconfnoemp"
                            class="form-control form-control-sm" /> </div>
                    <div class="col-3"><small>Lider</small><select id="liderconf"
                            class="form-control form-control-sm"></select></div>
                    <div class="col-3"><small>Correcciones</small><textarea id="accionescorrectivasconf"
                            class="form-control form-control-sm"></textarea></div>
                    <div class="col-2"><small>Componentes</small><select id="componentesconf"
                            class="form-control form-control-sm"></select></div>
                    <div class="col-1">
                        <input type="radio" id="atributoconf" name="tipeatributeconf" value="1" checked>
                        <label for="atributoconf">Atributo</label><br />
                        <input type="radio" id="variableconf" name="tipeatributeconf" value="2">
                        <label for="variableconf">Variable</label>
                    </div>
                    <div class="col-10"></div>
                    <div class="col-1"><br /><button id="guardarconf" class="btn btn-sm bg-target"><i
                                class="fas fa-save"></i> Guardar</button></div>
                    <div class="col-1"><br /><button type="reset" class="btn btn-sm btn-secondary"><i
                                class="fas fa-undo-alt"></i> Limpiar</button></div>
                    <div class="col-3" hidden><small>Calidad</small><select id="calidad"
                            class="form-control form-control-sm"></select></div>
                </div>
            </form>
            <div class="table-responsive border" style="height: 400px;">
                <table class="table table-bordered table-sm">
                    <thead class="table-dark">
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Maquina</th>
                        <th>Defecto</th>
                    </thead>
                    <tbody id="tblnoconformidad">
                    </tbody>
                </table>
            </div>

        </div>
    </section>

    <!--Seccion de Platicas-->
    <section id="sectionplaticas" class="section px-2">
        <!-- Platicas de 5 minutos  -->
        <div id="platicas5" class="p-2">
            <h4 class="tittlecont">Platica de 5 minutos</h4>
            <form id="formencplaticas">
                <div class="row">
                    <input type="hidden" id="folioplaticas5">
                    <div class="col-6">
                        <div class="row" id="archivo" style="height: 700px;"></div>
                    </div>
                    <div class="col-6">
                        <div class="row mb-2">
                            <div class="col-2">
                                <input type="number" id="noempsubplaticas5" min="0" class="form-control form-control-sm"
                                    placeholder="Noemp">
                            </div>
                            <div class="col-4">
                                <input type="text" id="nombresubplaticas5" min="0" class="form-control form-control-sm"
                                    placeholder="Nombre" readonly>
                            </div>
                            <div class="col-4">
                                <input type="text" id="departamentosubplaticas5" min="0"
                                    class="form-control form-control-sm" placeholder="Puesto" readonly>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-sm bg-target" id="guardarsubplaticas5"><i
                                        class="fa-solid fa-plus"></i> Registrar</button>
                            </div>
                        </div>
                        <div class="table-responsive" style="height:600px;">
                            <table class="table table-striped table-hover border">
                                <thead>
                                    <th class="table-header-kc">ID</th>
                                    <th class="table-header-kc">NOEMP</th>
                                    <th class="table-header-kc">NOMBRE</th>
                                    <th class="table-header-kc">PUESTO</th>
                                </thead>
                                <tbody id="tblsubencabezadoplaticas5"></tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Seccion de EPP -->
    <section id="sectionepp" class="section px-2">
        <div id="epp">
            <h4 class="tittlecont">EPP</h4>
            <form id="formepp">
                <div class="row">
                    <div class="col-1">
                        <small>No. Emp</small>
                        <input type="number" class="form-control form-control-sm" id="noempresepp" name="noempresepp" />
                    </div>
                    <div class="col-4">
                        <small>Nombre del Responsable</small>
                        <input type="text" class="form-control form-control-sm" id="nombreresepp" name="nombreresepp"
                            readonly />
                    </div>
                    <div class="col-3">
                        <small>Departamento</small>
                        <input type="text" class="form-control form-control-sm" id="departamentoresepp" readonly />
                    </div>
                    <div class="col-4">
                        <small>Puesto</small>
                        <input type="text" class="form-control form-control-sm" id="puestoresepp" readonly />
                    </div>
                </div>
                <div class="row">
                    <div class="col-1">
                        <small>No. Emp</small>
                        <input type="number" class="form-control form-control-sm" id="noempobsepp" name="noempobsepp" />
                    </div>
                    <div class="col-3">
                        <small>Nombre del observado</small>
                        <input type="text" class="form-control form-control-sm" id="nombreobsepp" name="nombreobsepp"
                            readonly />
                    </div>
                    <div class="col-3">
                        <small>Departamento</small>
                        <input type="text" class="form-control form-control-sm" id="departamentoobsepp" readonly />
                    </div>
                    <div class="col-3">
                        <small>Puesto</small>
                        <input type="text" class="form-control form-control-sm" id="puestoobsepp" readonly />
                    </div>
                    <div class="col-1">
                        <br />
                        <button class="btn bg-target btn-sm" id="saveEpp"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                    <div class="col-1">
                        <br />
                        <button class="btn btn-secondary btn-sm" id="limpiarepp"><i class="fas fa-save"></i>
                            Limpiar</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <th>Equipo de Protección Basico</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </thead>
                                <tbody id="listeppbasico">
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="col-4">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <th style="width: 320px;">Equipo de Protección Especifico</th>
                                    <th></th>
                                    <th></th>
                                </thead>
                                <tbody id="listeppespecifico">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <th>BPM</th>
                                    <th></th>
                                    <th></th>
                                </thead>
                                <tbody id="listeppbpm">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <dv class="col-12">
                        <small>Comentarios</small>
                        <textarea class="form-control form-control-sm" id="comentarioepp"></textarea>
                    </dv>
                </div>
                <div class="row mt-4">
                    <div class="table-responsive border" style="height: 320px;">
                        <table class="table table-bordered text-center">
                            <thead class="table-dark">
                                <th>Folio</th>
                                <th>Noemp</th>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <th>Comentario</th>
                                <!-- <th>Fecha</th> -->
                                <th></th>
                            </thead>
                            <tbody id="tbleppenc">

                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">New message</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive" style="height: 500px;">
                                <table class="table">
                                    <thead class="table-dark text-center">
                                        <th>Noemp</th>
                                        <th>Nombre</th>
                                        <th>Departamento</th>
                                        <!-- <th>Fecha</th> -->
                                        <th>Equipo</th>
                                        <th>Res</th>
                                    </thead>
                                    <tbody id="tblsubenc">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seccion de Vales -->
    <section id="sectionvales" class="section px-2">
        <h4 class="tittlecont">Vales Electronicos</h4>
        <div class="row">
            <div class="col-1">
                <small>NoEmp / Solicita</small>
                <input type="number" class="form-control form-control-sm" id="noemp">
            </div>
            <div class="col-2">
                <small>Nombre</small>
                <input type="text" class="form-control form-control-sm" id="nombre" readonly>
            </div>
            <div class="col-2">
                <small>Puesto</small>
                <input type="text" class="form-control form-control-sm" id="puesto" readonly>
            </div>
            <div class="col-1">
                <small>Turno</small>
                <input type="number" id="turnoen" class="form-control form-control-sm" readonly>
            </div>
            <div class="col-1">
                <small>Clave 1</small>
                <select class="form-control form-control-sm" id="clave1"></select>
            </div>
            <div class="col-1">
                <small>Clave 2</small>
                <select class="form-control form-control-sm" id="clave2"></select>
            </div>
            <div class="col-1">
                <small>Clave 3</small>
                <select class="form-control form-control-sm" id="clave3"></select>
            </div>
            <div class="col-1">
                <small>Clave 4</small>
                <select class="form-control form-control-sm" id="clave4"></select>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm bg-target" id="crearVale"><i class="fas fa-plus"></i> Crear Vale</button>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm btn-secondary" id="limpiarinicio"><i class="fas fa-undo-alt"></i>
                    Limpiar</button>
            </div>
        </div>
        <div class="row my-2 justify-content-center border">
            <h5 class="fw-bold text-center">Materiales Disponibles</h5>
            <div class="col-3">
                <small>Selecciona la clase</small>
                <select class="form-control form-control-sm" id="clase" size="8">
                </select>
            </div>
            <div class="col-8">
                <div class="table-responsive" style="height: 200px;">
                    <table class="table text-center">
                        <thead class="table-dark">
                            <th>ID</th>
                            <th width="400px">MATERIAL</th>
                            <th>Centro de constos</th>
                            <th>TIEMPO</th>
                            <th>TIPO DE MONTA</th>
                            <th></th>
                        </thead>
                        <tbody id="tblmateriales">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row justify-content-end">
            <div class="col-1 text-end">
                <br />
                <span><?php echo $_SESSION['usuario'] ?> - </span>
            </div>
            <div class="col-1">
                <small>Folio en edición</small>
                <input type="hidden" class="form-control form-control-sm" id="foliovale" readonly>
                <input type="text" class="form-control form-control-sm" id="foliocons" readonly>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm bg-target" id="enviar"><i class="far fa-paper-plane"></i> Enviar</button>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm btn-danger" id="cancelar"><i class="fas fa-ban"></i> Cancelar</button>
            </div>
            <div class="col-1">
                <br />
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ModalLista"><i
                        class="fas fa-list-ul"></i> Lista</button>
            </div>
        </div>
        <div class="row my-2">
            <h5 class="fw-bold text-center">Materiales Agregados</h5>
            <div class="table-responsive" style="height: 200px;">
                <table class="table text-center">
                    <thead class="table-dark">
                        <th>Folio</th>
                        <th>ID</th>
                        <th width="400px">MATERIAL</th>
                        <th>Centro de constos</th>
                        <th>TIEMPO</th>
                        <th>TIPO DE MONTA</th>
                        <th>CANDIDATOS</th>
                        <th></th>
                    </thead>
                    <tbody id="tblmaterialesagregados">
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="ModalLista" tabindex="-1" aria-labelledby="ModalListaLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalListaLabel">Consultar Vales Electronicos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-2">
                                <small>Fecha Inicio</small>
                                <input type="date" class="form-control form-control-sm" id="fechaivales" />
                            </div>
                            <div class="col-2">
                                <small>Fecha Final</small>
                                <input type="date" class="form-control form-control-sm" id="fechafvales" />
                            </div>
                            <div class="col-2">
                                <small>Maquina</small>
                                <select class="form-control form-control-sm" id="maquinasvales">
                                </select>
                            </div>
                            <div class="col-2">
                                <small>Turno</small>
                                <select class="form-control form-control-sm" id="turnoslist">
                                    <option value="">Selecciona el turno</option>
                                </select>
                            </div>
                            <div class="col-2">
                                <small>Estado</small>
                                <select class="form-control form-control-sm" id="estadoslist">
                                    <option value="">Selecciona un estado</option>
                                </select>
                            </div>
                            <div class="col-1">
                                <br />
                                <button class="btn btn-sm bg-target" id="buscar"><i class="fas fa-search"></i>
                                    Buscar</button>
                            </div>
                            <div class="col-1">
                                <br />
                                <button class="btn btn-sm btn-secondary" id="limpiarvales"><i
                                        class="fas fa-undo-alt"></i></button>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="table-responsive" style="height: 400px;">
                                <table class="table text-center">
                                    <thead class="table-dark">
                                        <th>Folio</th>
                                        <th>Maquina</th>
                                        <th>Noemp</th>
                                        <th>Nombre</th>
                                        <th>Turno</th>
                                        <th>Clave 1</th>
                                        <th>Clave 2</th>
                                        <th>Clave 3</th>
                                        <th>Clave 4</th>
                                        <th>Estado</th>
                                        <th>Creado</th>
                                        <th>Enviado</th>
                                        <th></th>
                                        <th></th>
                                    </thead>
                                    <tbody id="tblValesCreados">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <h5 class="fw-bold text-center">Agregar MM2 / KG</h5>
                            <div class="table-responsive" style="height: 200px;">
                                <table class="table text-center" id="tblvalescambios">
                                    <thead class="table-dark">
                                        <th>Folio</th>
                                        <th>ID</th>
                                        <th width="400px">MATERIAL</th>
                                        <th>Centro de constos</th>
                                        <th>TIEMPO</th>
                                        <th>TIPO DE MONTA</th>
                                        <th>CANDIDATOS</th>
                                        <th>MM2 / KG</th>
                                        <th>Envases</th>
                                    </thead>
                                    <tbody id="tblmaterialemodal">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seccion de trazabilidad -->
    <section id="sectiontrazabilidad" class="section">
        <h4 class="tittlecont">Trazabilidad</h4>
        <form id="formrill" class="mb-4">
            <div class="row">
                <div class="col">
                    <small class="fw-bold">Clave</small>
                    <select class="form-control form-control-sm" id="claveril">
                    </select>
                </div>
                <div class="col">
                    <small class="fw-bold">Clase</small>
                    <select class="form-control form-control-sm" id="claseril">
                    </select>
                </div>
                <div class="col">
                    <small class="fw-bold">Material</small>
                    <select class="form-control form-control-sm" id="materialril">
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-5">
                    <small class="fw-bold">Material de prueba</small>
                    <input type="text" class="form-control form-control-sm" id="materialpruebaril">
                </div>
                <div class="col-1">
                    <small class="fw-bold">Noemp</small>
                    <input type="number" class="form-control form-control-sm" id="noempril">
                </div>
                <div class="col-3">
                    <small class="fw-bold">Empleado</small>
                    <input type="text" class="form-control form-control-sm" id="empleadoril" readonly />
                </div>
                <div class="col-3">
                    <small class="fw-bold">Puesto</small>
                    <input type="text" class="form-control form-control-sm" id="puestoempril" readonly />
                </div>
                <div class="col">
                    <small class="fw-bold">Número de lote</small>
                    <div class="input-group mb-3">
                        <textarea id="loteril" class="form-control form-control-sm" rows="1"
                            onkeyup="javascript:this.value=this.value.toUpperCase();"></textarea>
                        <!-- <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalrill"><i class="fas fa-lg fa-barcode"></i></a> -->
                        <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                            data-bs-target="#modalrillqr"><i class="fas fa-lg fa-qrcode"></i></a>
                    </div>
                </div>
                <div class="col">
                    <small class="fw-bold">Vale</small>
                    <div class="input-group">
                        <select class="form-control form-control-sm" id="foliovalesril">
                        </select>
                        <a href="#" id="claverilupdate" class="btn btn-sm btn-primary"><i
                                class="fas fa-undo-alt"></i></a>
                    </div>

                </div>
                <div class="col-1">
                    <small class="fw-bold">Vale(Papel)</small>
                    <input type="number" id="foliovalemanual" class="form-control form-control-sm">
                </div>
                <div class="col">
                    <small class="fw-bold">Hora</small>
                    <input type="time" id="horaril" class="form-control form-control-sm">
                </div>
            </div>
            <div class="row my-2 justify-content-between text-center">
                <div class="col">
                    <button class="btn btn-sm bg-target" id="guardarril"><i class="fa-solid fa-floppy-disk"></i> Guardar
                        datos</button>
                </div>
                <div class="col">
                    <button type="reset" class="btn btn-sm btn-warning text-dark" id="limpiarril"><i
                            class="fa-solid fa-arrow-rotate-left"></i> Limpiar el formulario</button>
                </div>
            </div>
        </form>

        <div class="table-responsive border" style="height: 450px;">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <th>id</th>
                    <th>Clave</th>
                    <th>Modulo</th>
                    <th>Material</th>
                    <th>Empleado</th>
                    <th>Lote</th>
                    <th>Folio</th>
                    <th>Hora</th>
                    <th>Fecha</th>
                </thead>
                <tbody id="rilltbl">
                </tbody>
            </table>
        </div>
        <div class="modal fade" id="modalrill" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Lectura de codigo de barras</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="interactive" class="viewport"></div>
                        <div id="result"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalrillqr" tabindex="-1" aria-labelledby="exampleModalLabelqr" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabelqr">Lectura de codigo QR</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="qr-reader" style="width:450px"></div>
                        <div id="reader" style="width:450px"></div>
                        <div id="qr-reader-results"></div>
                        <div id="resultqr"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seccion de inspecciones -->
    <section id="insp1" class="section">
        <form name="formibnsp" id="formibnsp">
            <h4 class="tittlecont">Pre-usos </h4>
            <div class="row">
                <div class="col-1">
                    <small>NoEmp</small>
                    <input type="number" id="noempinsp" class="form-control form-control-sm" />
                </div>
                <div class="col">
                    <small>Nombre</small>
                    <input type="text" id="nombreinsp" class="form-control form-control-sm" readonly />
                </div>
                <div class="col">
                    <small>Tipo de Inspección</small>
                    <select id="inspecciontipo" class="form-control form-control-sm"></select>
                </div>
                <div class="col">
                    <small>Sección</small>
                    <select id="seccionpreusos" class="form-control form-control-sm"></select>
                </div>
                <div class="col">
                    <small>Fecha</small>
                    <input type="date" id="inpeccionfecha" class="form-control form-control-sm" />
                </div>
                <div class="col">
                    <small>Comentarios</small>
                    <textarea id="inpeccioncomentarios" class="form-control form-control-sm"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div id="inpecciondesc"></div>
                </div>
                <div class="col-6">
                    <div id="archivopreussos"></div>
                </div>
            </div>
            <div class="row justify-content-end my-2">
                <div class="col-1">
                    <button class="btn btn-sm bg-target" id="saveinsp" name="saveinsp"><i
                            class="fa-solid fa-floppy-disk"></i> Guardar</button>
                </div>
                <div class="col-1">
                    <button type="reset" id="resetinspecciones" class="btn btn-sm btn-secondary"><i
                            class="fa-solid fa-rotate-left"></i> Limpiar</button>
                </div>
            </div>
            <div class="table-responsive table-bordead" style="height: 200px;">
                <table class="table">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>FECHA</th>
                        <th>NOEMP</th>
                        <th>NOMBRE</th>
                        <th>TURNO</th>
                        <th>TIPO</th>
                        <th>SECCION</th>
                        <th>COMENTARIOS</th>
                    </thead>
                    <tbody id="tblinspeccions">

                    </tbody>
                </table>
            </div>
        </form>
    </section>

    <!-- Reporte de bitacora  -->
    <section id="sectionreporte" class="section px-2">
        <div class="row">
            <div class="col-11">
                <h4 class="tittlecont">Bitacora Maquina</h4>
            </div>
            <div class="col"><button id="excelRep" class="btn btn-sm btn-success">Excel</button></div>
        </div>
        <div id="excelrep">
            <div class="row">
                <div class="col-4">
                    <h3>Asistencias</h3>
                    <div class="table-responsive" style="height: 300px;">
                        <table class="table table-sm">
                            <thead class="table-dark">
                                <th>F</th>
                                <th>T</th>
                                <th>Noemp</th>
                                <th>Nombre</th>
                                <th>Puesto</th>
                            </thead>
                            <tbody id="tblasistenciasbot">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-8">
                    <h3>Presentacion</h3>
                    <div class="table-responsive" style="height: 300px;">
                        <table class="table table-sm">
                            <thead class="table-dark">
                                <th>Clave</th>
                                <th>Turno</th>
                                <th>Hora</th>
                                <th>Real</th>
                                <th>Acumulado</th>
                                <th>STD</th>
                                <th>Golpes</th>
                                <th>Merma</th>
                            </thead>
                            <tbody id="tblpresentacionesbitrep">
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col">
                    <h3>Control de tiempos</h3>
                    <div class="table-responsive" style="height: 300px;">
                        <table class="table table-sm">
                            <thead class="table-dark">
                                <th>F</th>
                                <th>T</th>
                                <th>De</th>
                                <th>A</th>
                                <th>Fecha</th>
                                <th>Operacion</th>
                                <th>Electrico</th>
                                <th>Mecanico</th>
                                <th>Materias</th>
                                <th>Grado</th>
                                <th>Prev</th>
                                <th>Servicios</th>
                                <th>Subtotal</th>
                                <th>Seccion</th>
                                <th>Modulo</th>
                                <th>Motivo</th>
                                <th>Correccion</th>
                            </thead>
                            <tbody id="tblctrltiemposbitrep">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <h3>Corrugados</h3>
                    <div class="table-responsive" style="height: 300px;">
                        <table class="table table-sm">
                            <thead class="table-dark">
                                <th>F</th>
                                <th>T</th>
                                <th>Cajas recibidas</th>
                                <th>Corrugados de almacen</th>
                                <th>Cajas producidas</th>
                                <th>Cajas entregadas</th>
                                <th>Clave de producto</th>
                            </thead>
                            <tbody id="tblcorrugadosbitrep">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-6">
                    <h3>Comentarios</h3>
                    <div class="table-responsive" style="height: 300px;">
                        <table class="table table-sm">
                            <thead class="table-dark">
                                <th>F</th>
                                <th>T</th>
                                <th>Seguridad</th>
                                <th>Calidad</th>
                                <th>O y L</th>
                                <th>Pendientes</th>
                                <th>Otros</th>
                            </thead>
                            <tbody id="tblcomentariosbitrep">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reporte de COV -->
    <section id="sectionPesosPanal" class="section px-2">
        <div class="row">
            <div class="col-11">
                <h4 class="titlecont">Registros cov</h4>
                <input type="hidden" name="folioPP" id="folioPP">
            </div>
        </div>
        <div class="row">
            <div class="col-1">
                <small>Peso</small>
                <input type="number" class="form-control form-control-sm" name="peso" id="peso" min="0">
            </div>
            <div class="col-1">
                <br>
                <button id="agregarPeso" class="form-control form-control-sm btn btn-sm bg-target"><i class="fa fa-plus"
                        aria-hidden="true"></i> Agregar</button>
            </div>
        </div>
        <div class="row container-pesos">
            <div class="col-9" id="inputsContainer">
                <small>Pesos de Pañales</small>
                <br>
            </div>
            <div class="col-3">
                <div class="row">
                    <div class="col-6">
                        <small>COV</small>
                        <input type="text" name="cov" id="cov" class="form-control bg-warning"
                            style="width:auto; margin:0;" readonly>
                    </div>
                    <div class="col-6" id="conOsinWR">
                        <br>
                        <div class="form-group">
                            <input type="radio" class="form-check-input" id="wr1" name="wr" value="0" checked>
                            <label for="wr2">Sin WR</label>
                            <br>
                            <input type="radio" class="form-check-input" id="wr2" name="wr" value="1">
                            <label for="wr1">Con WR</label>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <small>Promedio</small>
                    <input type="text" name="promedio" id="promedio" class="form-control bg-info"
                        style="width:auto; margin:0;" readonly>
                </div>
                <div class="col-6">
                    <small>MAX</small>
                    <input type="text" name="max" id="max" class="form-control bg-light" style="width:auto; margin:0;"
                        readonly>
                </div>
                <div class="col-6">
                    <small>MIN</small>
                    <input type="text" name="min" id="min" class="form-control bg-light" style="width:auto; margin:0;"
                        readonly>
                </div>
                <div class="col-6">
                    <br>
                    <div class="row">
                        <div class="col-6"><button id="btnLimpiarCOV"
                                class="form-control form-control-sm btn btn-sm btn-secondary"><i class="fa fa-save"
                                    aria-hidden="true"></i> Limpiar</button>
                        </div>
                        <div class="col-6">
                            <button id="btnGuardarPesos" class="form-control form-control-sm btn btn-sm bg-target"><i
                                    class="fa fa-save" aria-hidden="true"></i> Guardar</button>
                        </div>
                    </div>
                    <div class="row">

                    </div>
                </div>
            </div>
    </section>

    <!-- Modal Plan Produccion-->
    <div class="modal fade" id="ModalPlanProduccion" tabindex="-1" aria-labelledby="ModalPlanProduccionLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header sticky-top bg-white" style="z-index:1050;">
                    <h5 class="modal-title" id="ModalPlanProduccionLabel">Plan de produccion</h5>
                    <div class="col-4"></div>
                    <div class="col-2">
                        <small>Fecha de revisión</small>
                        <input type="date" class="form-control form-control-sm" id="fechaPlan" name="fechaPlan">
                    </div>
                    <div class="col-1"></div>
                    <div class="col-2">
                        <br>
                        <div class="row">
                            <div class="col-6">
                                <button class="btn btn-sm btn-primary" id="btnBuscarPlan"><i
                                        class="fa-solid fa-eye"></i> Ver</button>

                            </div>
                            <div class="col-6">
                                <button class="btn btn-sm btn-secondary" id="btnVolverPlan"><i
                                        class="fa-solid fa-rotate-left"></i> Volver</button>
                            </div>
                        </div>

                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="listaPlanProduccion">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php require_once("../indexmaquina/footer.php") ?>

<script src="./js/impresora.js"></script>
<script type="module" src="./js/bitacora.js"></script>
<script type="module" src="./js/sectionpresentaciones.js"></script>
<script type="module" src="./js/sectionctrolTiemposOld.js"></script>
<script type="module" src="./js/sectionctrolTiempos.js"></script>
<script type="module" src="./js/sectionepp.js"></script>
<script type="module" src="./js/sectionnoconformidad.js"></script>
<script type="module" src="./js/sectionplaticas.js"></script>
<script type="module" src="./js/sectionreporte.js"></script>
<script type="module" src="./js/sectiontrazabilidad.js"></script>
<script type="module" src="../ValesE/js/vales.js"></script>
<script type="module" src="./js/registroCOV.js"></script>
<script type="module" src="./js/WR.js"></script>
<script src="./js/Liquidos.js"></script>

<script src="../assets/qr/minified/html5-qrcode.min.js"></script>