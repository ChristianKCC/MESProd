<?php
require_once("../Session/seguridad.php");
if($_SESSION["permisoPersonal"]!=1){
    header('Location: ../index/index');
}
require_once("../index/header.php");
?>
<div class="container">

    <h3>Consulta de empleados</h3>
    <form class="p-2 my-2" id="formconsultaemp">
        <div class="row mb-3">
            <div class="col-4 ">
                <select class="form-control form-control-sm" name="deps[]" id="deps" multiple>
                </select>
                <small>Departamentos.</small>
            </div>
            <div class="col-4 ">
                <select class="form-control form-control-sm" name="Puestos[]" id="Puestos" multiple>
                </select>
                <small>Puestos.</small>
            </div>
            <div class="col-4 ">
                <select class="form-control form-control-sm" name="nivestu[]" id="nivestu" multiple>
                </select>
                <small>Nivel de estudios.</small>
            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <input type="text" name="libre" id="libre" placeholder="Buscar.." class="form-control form-control-sm">
                <small>Busca por nombre, apellidos, ibm.</small>
            </div>
            <div class="col">
                <button name="cempleados" id="cempleados" class="btn bg-target btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Consultar</button>
            </div>
            <div class="col">
                <button type="reset" class="btn btn-danger btn-sm"><i class="fa-solid fa-broom"></i> Limpiar</button>
            </div>
            <div class="col">
                <button data-bs-toggle="modal" data-bs-target="#nuevoempleado" id="mondalnuevoempleado" class="btn btn-success shadow btn-sm">
                    <i class="fa-regular fa-square-plus"></i> Nuevo Empleado</button>
            </div>
        </div>
        <div id="empleados" class=""></div>
    </form>
    <div class="table-responsive" style="height: 70%">
     <a href="#" id="exportarexcel">Exportar a excel</a>
        <table class="table table-striped table-sm table-hover" id="vistatabla">
            <thead class="table-dark">
                <th>No</th>
                <th>NoEmp</th>
                <th>Nombre</th>
                <th>Paterno</th>
                <th>Materno</th>
                <th>Departamento</th>
                <th>Departamento Real</th>
                <th>Puesto</th>
                <th>Estado</th>
                <th>Funciones</th>
            </thead>
            <tbody id="bodytblcstempleados">
            </tbody>
        </table>
    </div>
    <div class="modal fade" id="nuevoempleado" tabindex="-1" aria-labelledby="Modallabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="formnuevoemp">
                    <div class="modal-header">
                        <h5 class="modal-title" id="Modallabel">Agrega la informacion del nuevo empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div class="row mb-3">
                                <div class="col-1">
                                    <small class="fw-bold">NoEmpleado</small>
                                    <input type="number" class="form-control form-control-sm" id="NoEmp" name="NoEmp" value="">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Nombre(s)</small>
                                    <input type="text" name="Nombres" id="Nombres" class="form-control form-control-sm" value="" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Apellido Paterno</small>
                                    <input type="text" name="ApellidoPaterno" id="ApellidoPaterno" class="form-control form-control-sm" value="" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Apellido Materno</small>
                                    <input type="text" name="ApellidoMaterno" id="ApellidoMaterno" value="" class="form-control form-control-sm" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Centro de Costos</small>
                                    <select name="IdCentroCosto" id="IdCentroCosto" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="col-3">
                                    <small class="fw-bold">Nombre de Departamento</small>
                                    <select name="NombreDepartamento" id="NombreDepartamento" class="form-control form-control-sm">
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-3">
                                    <small class="fw-bold">Jefe Inmediato</small>
                                    <select name="JefeInm" id="JefeInm" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="col-3">
                                    <small class="fw-bold">Puesto</small>
                                    <select name="Puesto" id="Puesto" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Tipo de Trabajador</small>
                                    <select class="form-control form-control-sm" id="TipoTrabajador" name="TipoTrabajador">
                                    </select>
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">FechaIngreso</small>
                                    <input type="date" name="FechaIngreso" id="FechaIngreso" class="form-control form-control-sm" value="">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Estado Civil</small>
                                    <select type="text" name="IdClvEstadoCivil" id="IdClvEstadoCivil" class="form-control form-control-sm">
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-2">
                                    <small class="fw-bold">Vencimiento de Contrato</small>
                                    <input type="date" name="FechaVencimientoContrato" id="FechaVencimientoContrato" class="form-control form-control-sm" value="">
                                </div>
                                <div class="col-1">
                                    <small class="fw-bold">DiasVacacion</small>
                                    <input type="number" name="DiasVacaciones" id="DiasVacaciones" class="form-control form-control-sm" value="" disabled>
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">NSS (IMSS)</small>
                                    <input type="number" class="form-control form-control-sm" name="IMSS" id="IMSS" value="">
                                </div>
                                <div class="text-center col-4">
                                    <small class="fw-bold">RFC</small>
                                    <input type="text" class="form-control form-control-sm" id="RFC" name="RFC" value="" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                </div>
                                <div class="text-center col-3">
                                    <small class="fw-bold">CURP</small>
                                    <input type="text" name="CURP" id="CURP" class="form-control form-control-sm" value="" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-2">
                                    <small class="fw-bold">Telefono</small>
                                    <input type="number" class="form-control form-control-sm" id="Telefono" name="Telefono" value="">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Telefono 1</small>
                                    <input type="number" name="Telefono1" id="Telefono1" class="form-control form-control-sm" value="">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Telefono de Emergencia</small>
                                    <input type="number" name="Telefono2" id="Telefono2" class="form-control form-control-sm" value="">
                                </div>
                                <div class="col-3">
                                    <small class="fw-bold">Nivel de Estudios</small>
                                    <select type="text" name="IdClvNivelEstudios" id="IdClvNivelEstudios" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="col-3">
                                    <small class="fw-bold">Documento Probatorio</small>
                                    <select name="IdClvProbatorio" id="IdClvProbatorio" class="form-control form-control-sm">
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-3">
                                    <small class="fw-bold">Entidad</small>
                                    <select class="form-control form-control-sm" id="IdClvEntidad" name="IdClvEntidad">
                                    </select>
                                </div>
                                <div class="col-3">
                                    <small class="fw-bold">Municipio</small>
                                    <select name="ClvMunicipioYDelegacion" id="ClvMunicipioYDelegacion" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="text-center col-6">
                                    <small class="fw-bold">Domicilio</small>
                                    <input type="text" name="Domicilio" id="Domicilio" class="form-control form-control-sm" value="" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-3">
                                    <label><input type="checkbox" name="Bajas" id="Bajas" value="1">¿BAJA?</label>
                                    <label><input type="checkbox" name="EmpleadoSindicalizado" id="EmpleadoSindicalizado" value="1">¿EMPLEADO?</label>
                                    <label><input type="checkbox" name="RecibeOferta" id="RecibeOferta" value="1">¿RECIBE OFERTA?</label>
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Fecha de Antiguedad</small>
                                    <input type="date" value="" name="FechaAntiguedad" id="FechaAntiguedad" class="form-control form-control-sm">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Clave Discapacidad</small>
                                    <select class="form-control form-control-sm" id="IdClvDiscapacidad" name="IdClvDiscapacidad">
                                    </select>
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Hijos Dependientes</small>
                                    <input type="number" class="form-control form-control-sm" id="NoHijosDependientes" name="NoHijosDependientes" value="">
                                </div>
                                <div class="col-3">
                                    <small class="fw-bold">Clave de Ocupacion</small>
                                    <select name="IdClvOcupaciones" id="IdClvOcupaciones" class="form-control form-control-sm">
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-1">
                                    <small class="fw-bold">Año Emision</small>
                                    <input type="number" class="form-control form-control-sm" id="AnioEmisionDocto" name="AnioEmisionDocto" value="">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Clave Institucional</small>
                                    <select class="form-control form-control-sm" id="IdClvInstitucionEducativa" name="IdClvInstitucionEducativa">
                                    </select>
                                </div>
                                <div class="col-3">
                                    <small class="fw-bold">Nombre Estudio/Carrera</small>
                                    <input type="text" class="form-control form-control-sm" id="NombreEstudioCarrera" name="NombreEstudioCarrera" value="" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Fecha de Baja</small>
                                    <input type="date" class="form-control form-control-sm" name="FechaBaja" id="FechaBaja" value="">
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Motivo de Baja</small>
                                    <select name="IdMotivoBaja" id="IdMotivoBaja" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Otro Motivo de Baja</small>
                                    <input type="text" name="OtroMotivoBaja" id="OtroMotivoBaja" class="form-control form-control-sm" value="" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-2">
                                    <small class="fw-bold">Centro de Costos Real</small>
                                    <select name="IdCentroCostoReal" id="IdCentroCostoReal" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="col-2">
                                    <small class="fw-bold">Departamento Real</small>
                                    <select name="NoDeptoReal" id="NoDeptoReal" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="col-1">
                                    <small class="fw-bold">Sexo</small>
                                    <select name="Sexo" id="Sexo" class="form-control form-control-sm">
                                        <option value="H">H</option>
                                        <option value="M">M</option>
                                    </select>
                                </div>
                            </div>
                            <div id="respuesta"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm  btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-rectangle-xmark"></i> Cerrar</button>
                        <button type="reset" class="btn btn-sm btn-danger"><i class="fa-solid fa-broom"></i> Limpiar Formulario</button>
                        <button type="button" class="btn btn-sm btn-primary" id="guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar Empleados</button>
                        <button type="button" class="btn btn-sm btn-warning" id="editarempleado"><i class="fa-solid fa-pen-to-square"></i> Guardar Cambios</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <?php require_once("../index/footer.php") ?>
    <script type="text/javascript" src="../poojs/herramientas.js"></script>
    <script src="js/index.js" type="text/javascript"></script>
    <script type="text/javascript">
        RI = new RI();
        RI.inicio();
    </script>