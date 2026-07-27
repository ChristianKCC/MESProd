<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
require_once("./php/perfilcontrol.php");
$ibm = $_GET['ibm'];
$Empleados = new Perfil();
$empleado = $Empleados->informacionempleado($ibm);
?>
<!-- Contenido -->
<input type="hidden" id="noempenc" value="<?php echo $ibm ?>">
<a id="informacion"></a>
<div class="container">
    <div class="row m-3">
        <div class="col-12">
            <div class="row mb-2 p-2">
                <h3>Perfil de Empleado</h3>
            </div>
            <div class="row">
                <div class="col-12 text-center"><img src="./fotos/<?php echo $ibm; ?>.jpg" width="100" height="100" onerror="this.src='./fotos/0.png'" ; class="img-fluid rounded-circle"></div>
                <div class="col-12 text-center">
                    <h4><?php echo " (" . $ibm . ")" . " " . $empleado['Nombres'] . " " . $empleado['ApellidoPaterno'] . " " . $empleado['ApellidoMaterno']; ?></h4>
                </div>
            </div>
            <div class="btn-group" role="group" aria-label="Basic example">
                <a href="#informacion" class="btn btn-secondary">Perfil</a>
                <a href="#cursos" class="btn btn-secondary">Destacado</a>
            </div>

            <hr class="bg-info">
            <div class="row text-justify">
                <div class="col-4">
                    <p class="font-weight-bold">Departamento: <span class="font-weight-normal"><?php echo $empleado['NombreDepartamento']; ?> </span></p>
                    <p class="font-weight-bold">Puesto: <span class="font-weight-normal"><?php echo $empleado['nompuesto']; ?> </span></p>
                    <p class="font-weight-bold">RFC: <span class="font-weight-normal"><?php echo $empleado['RFC']; ?> </span></p>
                    <p class="font-weight-bold">CURP: <span class="font-weight-normal"><?php echo $empleado['CURP']; ?> </span></p>
                    <p class="font-weight-bold">IMSS: <span class="font-weight-normal"><?php echo $empleado['IMSS']; ?> </span></p>
                    <p class="font-weight-bold">Telefono: <span class="font-weight-normal"><?php echo $empleado['Telefono']; ?> </span></p>
                    <p class="font-weight-bold">Estudios: <span class="font-weight-normal"><?php echo $empleado['NombreEstudioCarrera']; ?> </span></p>
                </div>
                <div class="col-4">
                    <p class="font-weight-bold">Nivel de estudio: <span class="font-weight-normal"><?php echo $empleado['Nvlestudios']; ?></span></p>
                    <p class="font-weight-bold">Tipo de trabajador: <span class="font-weight-normal"><?php echo $empleado['TipoTrabajador']; ?></span></p>
                    <p class="font-weight-bold">Direccion: <span class="font-weight-normal"><?php echo  $empleado['Domicilio']; ?></span></p>
                    <p class="font-weight-bold">Telefono: <span class="font-weight-normal"><?php echo $empleado['Telefono']; ?></span></p>
                    <p class="font-weight-bold">Telefono2: <span class="font-weight-normal"><?php echo $empleado['Telefono1']; ?></span></p>
                    <p class="font-weight-bold">Correo: <span class="font-weight-normal"><?php echo $empleado['CorreoInterno']; ?></span></p>
                </div>
            </div>
            <hr class="bg-info">
            <a id="cursos"></a>
            <div class="row text-justify">
                <h3>Información General del Usuario</h3>
                <div class="col-8">
                    <h5 class="fw-bold">Cursos</h5>
                    <div class="table-responsive" style="height: 400px;">
                        <table class="table table-striped table-sm">
                            <thead class="table-dark">
                                <th>Curso</th>
                                <th>Calificación</th>
                                <th>Duración</th>
                                <th>Fecha</th>
                                <th>Cap</th>
                                <th>Contesto</th>
                                <th>Categoria</th>
                            </thead>
                            <tbody>
                                <?php
                                $Empleados->tblcursosempleado($ibm);
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-4">
                    <h6>Cursos restantes</h6>
                    <canvas id="chartcursos" height="300px"></canvas>
                </div>
            </div>
            <div class="row">
                <div class="col-5">
                    <h5 class="fw-bold">Registros de llegada</h5>
                    <table class="table table-striped table-sm">
                        <thead class="table-dark">
                            <th>NoEmp</th>
                            <th>Nombre</th>
                            <th>Fecha</th>
                        </thead>
                        <tbody id="tblasistencias">
                        </tbody>
                    </table>
                </div>
                <div class="col-7">
                    <h5 class="fw-bold">Consultas enfermeria</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead class="table-dark">
                                <th>ID</th>
                                <th>NoEmp</th>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <th>Edad</th>
                                <th>Tratamiento</th>
                                <th>Observación</th>
                                <th>Aparato</th>
                                <th>Enfermedad</th>
                                <th>fecha</th>
                            </thead>
                            <tbody id="tblconsultasEnfermeria">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once("../index/footer.php"); ?>
    <script type="text/javascript" src="./js/Perfil.js"></script>
    <script type="text/javascript">
        perfil = new Perfil();
        perfil.perfil();
    </script>