<?php
require_once(__DIR__ . "/php/logicaReportesF.php");
?>


<link rel="stylesheet" href="css/estilosReporte.css">
<!-- DRIVER JS -->
<link rel="stylesheet" href="../Vacaciones/css/estilosDriverJs.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

<div class="container p-4">
    <h5 class="tittlecont">Consulta de Tiempos Extra</h5>    
    <br />
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
                Desde este apartado consulta los registros existentes para los tiempos extras filtrando por los datos necesarios.
            </small>
        </div>
    </div>
    <br />
    <br />
    <br />
       
    <div class="card card-body">
        <div class="row">            
            <div class="card-head">
                <h2>Consulta de reportes por filtros</h2>
                <p>Genera un PDF con una hoja por cada folio que cumpla los criterios. Cada hoja incluye únicamente a los empleados que aplican.</p>
            </div>
            <div class="card-body">
                <form action="./pdf/reporteFiltrado.php" method="get" target="_blank" id="formFiltros">
                    <div class="grid">

                        <div class="field">
                            <label for="ibm">IBM / No. de empleado <span class="hint">(opcional)</span></label>
                            <input type="text" id="ibm" name="ibm" inputmode="numeric" pattern="[0-9]*" placeholder="Ej. 11111">
                        </div>

                        <div class="field">
                            <label for="tipoEmpleado">Tipo de empleado</label>
                            <select id="tipoEmpleado" name="tipoEmpleado">
                                <option value="">Todos</option>
                                <option value="0">Sindicalizado</option>
                                <option value="1">Empleado</option>
                            </select>
                        </div>

                        <div class="field full">
                            <label>Rango de fechas</label>
                            <div class="row-dates">
                                <input type="date" name="fechaInicio" aria-label="Fecha inicio" required>
                                <input type="date" name="fechaFin" aria-label="Fecha fin" required>
                            </div>
                        </div>

                        <div class="field full">
                            <label for="departamento">Departamento</label>
                            <select id="departamento" name="departamento">
                                <option value="">Todos</option>
                                <?php foreach ($departamentos as $dep): ?>
                                    <option value="<?php echo htmlspecialchars($dep['NoDepto']); ?>">
                                        <?php echo htmlspecialchars($dep['NombreDepto']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label for="tipoAprobado">Validados</label>
                            <select id="tipoAprobado" name="tipoAprobado">
                                <option value="">Selecciona una opción</option>
                                <option value="1">Validados</option>
                                <option value="0">No validados</option>
                            </select>
                        </div>

                    </div>

                    <div class="actions">
                        <button type="reset" class="btn-secondary"> <i class="fa-solid fa-eraser"></i> Limpiar</button>
                        <button type="submit" class="btn-danger"> <i class="fa-solid fa-file-pdf"></i> Generar PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/consultaFolios"></script>