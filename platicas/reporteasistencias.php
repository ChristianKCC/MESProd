<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!-- Contenido -->
<div class="container p-3 border rounded shadow">
    <h5 class="tittlecont">Reporte platicas asistencias</h5> 
    <br />    
    
    <div style="float:left" class="row">
        <div class="col-20">    
            <small class="alert alert-info">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    fill="currentColor"
                    class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"
                    viewBox="0 0 16 16"
                    role="img"
                    aria-label="Warning:">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                Desde este apartado, consulta tus reportes de asistencias para las platicas de cumplimiento de lectura P5M.
            </small>
        </div>
    </div>
    <br />
    <br />

    <form method="POST" action="php/reporteasistenciaspdf.php" target="iframe_a" class="mb-4">
        <div class="row">
            <div class="col">
                <small>Fecha inicial</small>
                <input type="date" class="form-control form-control-sm" name="fechai" id="fechai" required>
            </div>
            <div class="col">
                <small>Fecha final</small>
                <input type="date" class="form-control form-control-sm" name="fechaf" id="fechaf" required>
            </div>
            <div class="col">
                <small>Departamento</small>
                <select id="departamento" name="departamento" class="form-control form-control-sm"></select>
            </div>
            <div class="col">
                <small>Maquinas</small>
                <select id="maquinas" name="maquinas" class="form-control form-control-sm">
                    <option value="">Selecciona una opción</option>
                </select>
            </div>
            <div class="col">
                <small>No.Emp</small>
                <input type="number" class="form-control form-control-sm" name="noemp" id="noemp">
            </div>
            <!-- <div class="col">
                <br>
                <input class="form-control form-control-sm btn btn-sm bg-target" value="Consultar" type="submit">
            </div>
            <div class="col">
                <br>
                <input class="form-control form-control-sm btn btn-sm btn-secondary" value="Limpiar" type="reset">
            </div> -->

            <!-- <div class="col">
                <br>
                <button type="submit" formaction="php/reporteasistenciaspdf.php" target="iframe_a"
                    class="form-control form-control-sm btn btn-sm bg-target">
                    <i class="fa-solid fa-file-pdf"></i> Generar PDF
                </button>
            </div> -->

            <div class="col">
            <br>
                <button type="submit" formaction="php/reporteasistenciaspdf.php" target="iframe_a"
                    class="form-control form-control-sm btn btn-sm bg-target"
                    onclick="setTimeout(()=>{window.open('reportes/ReporteAsistencias.pdf','_blank')},1500)">
                    <i class="fa-solid fa-file-pdf"></i> Generar PDF
                </button>
            </div>

            <div class="col">
                <br>
                <button type="submit" formaction="php/reporteasistenciasexcel.php"
                    class="form-control form-control-sm btn btn-sm btn-success">
                    <i class="fa-solid fa-file-excel"></i> Generar Excel
                </button>
            </div>


            <!-- <div class="col">
                <br>
                <button type="submit" class="form-control form-control-sm btn btn-sm bg-target">
                    <i class="fa-solid fa-magnifying-glass"></i> Consultar
                </button>
            </div> -->
            <div class="col">
                <br>
                <button type="reset" class="form-control form-control-sm btn btn-sm btn-secondary">
                    <i class="fa-solid fa-arrows-rotate"></i> Limpiar
                </button>
            </div>
        </div>
    </form>
    <iframe name="iframe_a" height="680px" width="100%" title="Iframe Example"></iframe>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="./js/Reporte.js"></script>