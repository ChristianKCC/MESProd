<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container">
    <h5 class="tittlecont">Reporte de producción</h5>
    <div class="row m-2">
        <div class="col-2">
            <small>Fecha</small>
            <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" />
        </div>
        <div class="col-3">
            <small>Departamento</small>
            <select class="form-control form-control-sm" id="departamento" name="departamento"></select>
        </div>
        <div class="col-3">
            <small>Maquina</small>
            <select class="form-control form-control-sm" id="maquina" name="maquina"></select>
        </div>
        <div class="col-4">
            <small>Clave</small>
            <select class="form-control form-control-sm" id="clave" name="clave"></select>
        </div>
        <div class="col-1">
            <small>Noemp</small>
            <input type="number" class="form-control form-control-sm" id="noemp" name="noemp" />
        </div>
        <div class="col-3">
            <small>Conductor</small>
            <input type="text" class="form-control form-control-sm" id="conductor" name="conductor" />
        </div>
        <div class="col-1">
            <small>Turno</small>
            <select class="form-control form-control-sm" id="turno" name="turno"></select>
        </div>
        <div class="col">
            <small>Hrs trabajo</small>
            <input type="number" class="form-control form-contro-sm" id="horastrabajadas" name="horastrabajadas">
        </div>
        <div class="col">
            <small>Golpes Totales</small>
            <input type="number" class="form-control form-contro-sm" id="cajastotales" name="cajastotales">
        </div>
        <div class="col">
            <small>Cajas Reales</small>
            <input type="number" class="form-control form-contro-sm" id="cajasreales" name="cajasreales">
        </div>
        <div class="col">
            <small>ID Editando</small>
            <input type="number" class="form-control form-contro-sm" id="id" name="id" readonly>
        </div>
        <div class="col">
            <br />
            <button class="btn btn-sm bg-target" id="saveEncabezado"><i class="fas fa-save"></i> Guardar</button>
        </div>
        <div class="col">
            <br />
            <button class="btn btn-sm btn-danger" id="resetEnc"><i class="fas fa-undo-alt"></i> Limpiar</button>
        </div>
    </div>
    <div class="row">
        <hr>
        <div class="row mb-2">
            <div class="col-2"><small>Filtrar por Fechas</small><input type="date" id="fechai" class="form-control form-control-sm"></div>
            <div class="col-1 text-center"><br /><small>A:</small></div>
            <div class="col-2"><br /> <input type="date" id="fechaf" class="form-control form-control-sm"></div>
            <div class="col-2"><small>Filtrar por maquina</small>
            <select class="form-control form-control-sm" id="slcmaquina"></select></div>
            <div class="col-2"><small>Buscar por ID</small><input type="text" id="idproduccion" class="form-control form-control-sm"></div>
            <div class="col-1"><br /><button class="btn btn-sm bg-target" id="btnbuscar"><i class="fas fa-search"></i> Buscar</button></div>
        </div>

        <div class="table-responsive " style="height: 300px;">
            <table class="table table-hover border">
                <thead class="table-dark">
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Departamento</th>
                    <th>Maquina</th>
                    <th>Clave</th>
                    <th>Clase</th>
                    <th>Tipo</th>
                    <th>Conductor</th>
                    <th>Turno</th>
                    <th>Hrs</th>
                    <th>Totales</th>
                    <th>Reales</th>
                    <th>Estandar</th>
                    <th>Merma</th>
                    <th></th>
                </thead>
                <tbody id="tblencproduccion"></tbody>
            </table>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-12">
            <h5 class="text-center">Control de tiempos</h5>
            <form id="formctrltiempos" class="border px-2 p-2">
                <div class="table-responsive my-2" style="height: 300px;">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <th>Folio</th>
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
                        </thead>
                        <tbody id="tblctrltiempos">
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
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
                    <tbody id="tblpresentacionesbit">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script src="js/produccion.js" type="module"></script>