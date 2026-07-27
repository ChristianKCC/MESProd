<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<style>
    .swal-btn {
        margin: 0 0.5rem;
    }
</style>

<!-- Contenido -->
<div class="container p-4">
    <h5 class="tittlecont">PLAN DE PRODUCCIÓN</h5>
    <div class="row">
        <div class="col-1">
            <small>FOLIO</small>
            <input type="number" class="form-control form-control-sm" id="folio" name="folio" readonly />
        </div>
        <div class="col-2">
            <small># MODIFICACIÓN</small>
            <select class="form-control form-control-sm" id="configuracion" name="configuracion">
                <option value="" selected disabled>SELECCIONA UNA OPCIÓN</option>
                <option value="0">CERO</option>
                <option value="1">PRIMERA</option>
                <option value="2">SEGUNDA</option>
                <option value="3">TERCERA</option>
                <option value="4">CUARTA</option>
                <option value="5">QUINTA</option>
                <option value="6">SEXTA</option>
                <option value="7">SÉPTIMA</option>
                <option value="8">OCTAVA</option>
                <option value="9">NOVENA</option>
                <option value="10">DÉCIMA</option>
            </select>
        </div>
        <div class="col-2">
            <small>MÁQUINA</small>
            <select class="form-control form-control-sm" id="maquina" name="maquina"></select>
        </div>
        <div class="col-1">
            <small>CLAVE</small>
            <input type="text" class="form-control form-control-sm" id="clave" name="clave" />
        </div>
        <div class="col-4">
            <small>DESCRIPCIÓN</small>
            <input type="text" class="form-control form-control-sm" id="descripcion" name="desripcion" readonly />
        </div>
        <div class="col-2">
            <small>FECHA</small>
            <input type="month" class="form-control form-control-sm" id="fecha" name="fecha" />
        </div>
        <!-- <div class="col-2">
            <small>Etapa</small>
            <input type="text" class="form-control form-control-sm" id="etapa" name="etapa" readonly/>
            <input type="hidden" name="idEtapa" id="idEtapa">
        </div> -->
        <!-- <div class="col-2">
            <small>Producto</small>
            <input type="text" class="form-control form-control-sm" id="producto" name="producto" readonly/>
            <input type="hidden" name="idProducto" id="idProducto">
        </div> -->

    </div>
    <div class="row">
        <div class="col-2">
            <div class="col-12" id="tipoPrograma" hidden>
                <small id="tituloMaquina">PROGRAMA MENSUAL USTD</small>
                <input type="number" class="form-control form-control-sm" id="STD" name="STD" />
            </div>
        </div>
        <div class="col-2"></div>
        <div class="col-4">
            <br>
            <center>
                <h5>TODOS LOS PLANES DE PRODUCCIÓN COMIENZAN CON LA MODIFICACIÓN CERO</h5>
            </center>
        </div>
        <!-- <div class="col-2" hidden>
            <small>Avance producción</small>
            <input type="number" class="form-control form-control-sm" id="produccion" name="produccion">
        </div> -->
        <!-- <div class="col-2">
            <small>Diferencia de Producción</small>
            <input type="number" class="form-control form-control-sm" id="prodvsreal" name="prodvsreal" readonly>
        </div>
        <div class="col-2">
            <small>Porcentaje de Producción</small>
            <input type="text" class="form-control form-control-sm" id="porcenProducc" name="porcenProducc" readonly>
        </div> -->
        <div class="col-2"></div>
        <div class="col-1">
            <br />
            <button class="form-control form-control-sm btn btn-sm bg-target" onclick="reddata()" id="btnsave"
                name="btnsave"><i class="fas fa-save"></i> GUARDAR</button>
        </div>
        <div class="col-1">
            <br />
            <button class="form-control form-control-sm btn btn-sm btn-danger" id="btnclean" name="btnclean"><i
                    class="fas fa-undo-alt"></i> LIMPIAR</button>
        </div>
        <div class="vol-4">

        </div>

    </div>
    <div class="row justify-content-end">

    </div>
    <div class="row mt-2">
        <div class="table-responsive">
            <!-- Controles de paginación y buscador -->
            <div class="d-flex justify-content-between align-items-center mt-3 pagination-controls">
                <div>
                    <label class="mb-0">
                        MOSTRAR:
                        <select id="pageSize" class="form-select form-select-sm d-inline-block" style="width:80px;">
                            <option value="10">10</option>
                            <option value="15" selected>15</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        REGISTROS
                    </label>
                </div>
                <!-- Buscador -->
                <div class="me-2" style="flex:1; max-width:300px;">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="BUSCAR..." />
                </div>
            </div>
            <br>
            <table class="table table-bordered table-striped" style="text-align: center;">
                <thead class="table-dark">
                    <th>FOLIO</th>
                    <th>FECHA</th>
                    <th>MÁQUINA</th>
                    <th># MODIFICACIÓN</th>
                    <th>CLAVE</th>
                    <th>DESCRIPCIÓN</th>
                    <!-- <th>Etapa</th>
                    <th>Producto</th> -->
                    <th>PROGRAMA MENSUAL</th>
                    <!-- <th>Programa vs Producción</th>
                    <th>% Producción</th> -->
                    <th>ACCIÓN</th>
                </thead>
                <tbody id="tblProgramaMaquina"></tbody>
            </table>
            <br>
            <!-- Controles para pasar a la siguiente pagina -->
            <div class="d-flex justify-content-end">
                <button id="prevPage" class="btn btn-dark btn-sm">Anterior</button>
                <span id="pageInfo" class="mx-2 my-auto"></span>
                <button id="nextPage" class="btn btn-dark btn-sm">Siguiente</button>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/PlanProduccion.js" type="module"></script>