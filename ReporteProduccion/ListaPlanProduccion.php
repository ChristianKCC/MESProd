<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-4">
    <h5 class="tittlecont">Plan de Producción</h5>
    <div class="row justify-content-end">
        <div class="col-2">
            <small>Maquina</small>
            <select class="form-control form-control-sm" id="maquina" name="maquina"></select>
        </div>
        <div class="col-2">
            <small>Clave</small>
            <input class="form-control form-control-sm" type="number" name="claveProducc" id="claveProducc" min="0">
        </div>
        <div class="col-1">
            <br>
            <button class="form-control form-control-sm btn btn-sm bg-target" id="btnFiltrar" name="btnFiltrar"><i class="fa fa-search"></i> Buscar</button>
        </div>
        <div class="col-1">
            <br>
            <button class="form-control form-control-sm btn btn-sm bg-info" data-bs-toggle="modal" data-bs-target="#ModalPlanProduccion" id="btnPlanProducc"><i class="fa-solid fa-eye"></i> Ver Plan</button>
        </div>
        <div class="col-1">
            <br>
            <button class="form-control form-control-sm btn btn-sm bg-warning" id="reset" name="reset"><i class="fa fa-repeat" aria-hidden="true"></i> Reset</button>
        </div>
    </div>
    <div class="row mt-2">
        <div class="table-responsive" style="height: 500px;">
            <table class="table table-hover" style="text-align: center;">
                <thead class="table-dark">
                    <th>Folio</th>
                    <th>Clave</th>
                    <th>Descripción</th>
                    <th>Etapa</th>
                    <th>Producto</th>
                    <th>Fecha</th>
                    <th>Maquina</th>
                    <th>Programa Mensual</th>
                    <th>STD Acumuladas</th>
                    <th>Programa vs Producción</th>
                    <th>% Producción</th>
                </thead>
                <tbody id="tblProgramaMaquinaNueva" class="justify-content-center"></tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><span class="fw-bold">Total</span></td>
                        <td id="totalSTD"></td>
                        <td id="totalProducc"></td>
                        <td id="totalProduccvsReal"></td>
                        <td id="porcenTotal"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Modal Plan Produccion-->
    <div class="modal fade" id="ModalPlanProduccion" tabindex="-1" aria-labelledby="ModalPlanProduccionLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalPlanProduccionLabel">Sin información</h5>
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
<?php require_once("../index/footer.php") ?>
<script src="js/PlanProduccionNueva.js" type="module"></script>q