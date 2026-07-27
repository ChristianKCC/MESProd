<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5>Reporte bitacora de maquina</h5>
    <form>
        <div class="row">
            <div class="col"><small>Maquina</small><select class="form-control form-control-sm" id="maquinas"></select></div>
            <div class="col"><small>Fecha Inicio</small><input type="date" class="form-control form-control-sm" id="fechai"></div>
            <div class="col"><small>Fecha Final</small><input type="date" class="form-control form-control-sm" id="fechaf"></div>
            <div class="col"><small>Turno</small>
                <select class="form-control form-control-sm" id="turno">
                    <option value="">Selecciona una opción</option>
                    <option value="1">Primero</option>
                    <option value="2">Segundo</option>
                    <option value="3">Tercero</option>
                </select>
            </div>
            <div class="col-1"><br><button class="btn btn-sm bg-target" id="greporte">Buscar</button></div>
            <div class="col-1"><br><button type="reset" class="btn btn-sm btn-danger">Limpiar</button></div>
            <div class="col-1"><br><button id="excelRep" class="btn btn-sm btn-success">Excel</button></div>
        </div>
    </form>
    <div id="excelrep">
        <div class="row">
            <div class="col-4">
                <h3>Asistencias</h3>
                <div class="table-responsive" style="height: 400px;">
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
                <div class="table-responsive" style="height: 400px;">
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
                <div class="table-responsive" style="height: 400px;">
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
                <div class="table-responsive" style="height: 400px;">
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
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="./js/sectionreporteuser.js"></script>