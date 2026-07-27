<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<div class="container p-5">
    <h4 class="tittlecont">Valoración del acoso laboral (Resultados)</h4>
    <br>
    <div class="row">
        <div class="col-3"></div>
        <div class="col-3"></div>
        <div class="col-3"></div>
        <div class="col-3">
            <div class="text-end">
                <div class="row text-start">
                    <div class="col">
                        <strong>Glosario</strong>
                    </div>
                </div>
                <div class="row text-start">
                    <ul style="list-style: none;">
                        <li><strong>NEAP:</strong> Número total de estrategias de acoso</li>
                        <li><strong>IGAP:</strong> Índice global de acoso psicológico</li>
                        <li><strong>IMAP:</strong> Índice medio de las estrategias de acoso</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
    <!-- Tabla -->
    <div class="table-responsive">
        <!-- Controles de paginación y buscador -->
        <div class="d-flex justify-content-between align-items-center mt-3 pagination-controls">
            <div>
                <label class="mb-0">
                    Mostrar:
                    <select id="pageSize" class="form-select form-select-sm d-inline-block" style="width:80px;">
                        <option value="10">10</option>
                        <option value="15" selected>15</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    registros
                </label>
            </div>
            <!-- Buscador -->
            <div class="me-2" style="flex:1; max-width:420px;" hidden>
                <input type="text" id="searchInput" class="form-control form-control-sm"
                    placeholder="Buscar por máquina..." />
            </div>
        </div>
        <br>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>NoEmp</th>
                    <th>NEAP</th>
                    <th>IGAP</th>
                    <th>IMAP</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <br>
        <!-- Controles para pasar a la siguiente pagina -->
        <div class="d-flex justify-content-end">
            <button id="prevPage" class="btn btn-dark btn-sm">Anterior</button>
            <span id="pageInfo" class="mx-2 my-auto"></span>
            <button id="nextPage" class="btn btn-dark btn-sm">Siguiente</button>
        </div>
    </div>
    <!-- Modal editar datos -->
    <div class="modal fade" id="modalVerRespuestas" tabindex="-1" aria-labelledby="modalVerRespuestasModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVerRespuestasModalLabel">New message</h5>
                    <button class="btn btn-info" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                        Ver descripción
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quizForm">
                        <!-- Preguntas dinámicas -->
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once("../index/footer.php") ?>
    <script type="module" src="./js/resultadosCuestionario.js"></script>