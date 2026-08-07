<?php require_once("../index/header.php"); ?>
<style>
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

<div class="container rounded shadow p-4">
    <h4 class="tittlecont">Calidad de líquidos y formulados</h4>
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
            <table class="table table-striped table-hover border" style="text-align: center;">
                <thead class="table-dark">
                    <th class="table-header-kc">FECHA</th>
                    <th class="table-header-kc">MAQUINA</th>
                    <th class="table-header-kc">FOLIO</th>
                    <th class="table-header-kc">CATEGORIA</th>
                    <th class="table-header-kc">PRODUCTO</th>
                    <th class="table-header-kc">CLAVE</th>
                    <th class="table-header-kc">DESCRIPCIÓN</th>
                    <th class="table-header-kc">ESTATUS</th>
                </thead>
                <tbody id="tblFoliosFormulados"></tbody>
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