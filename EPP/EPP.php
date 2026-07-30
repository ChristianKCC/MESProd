<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>
<!--  Contenido  -->
<div class="container p-3">
    <h5 class="tittlecont">Equipo de Protección Personal</h5>
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
                Desde este apartado realiza tus EPP, solicita EPP y herramientas con los botones designados.
            </small>
        </div>
    </div>
    <br />
    <br />
    <br />

    <form id="formepp">
        <div class="row">
            <div class="col-1">
                <small>No Emp</small>
                <input type="number" class="form-control form-control-sm" id="noemp" name="noemp" />
            </div>
            <div class="col-2">
                <small>Nombre</small>
                <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" readonly />
            </div>
            <div class="col-2">
                <small>Departamento</small>
                <input type="text" class="form-control form-control-sm" id="departamento" readonly />
            </div>
            <div class="col-2">
                <small>Puesto</small>
                <input type="text" class="form-control form-control-sm" id="puesto" readonly />
            </div>
            <div class="col-1">
                <br />
                <button type="button" class="btn bg-success btn-sm text-white" id="saveEpp"><i class="fas fa-save"></i> Guardar</button>
            </div>
            <div class="col-1">
                <br />
                <button type="button" class="btn btn-secondary btn-sm" id="limpiar"><i class="fas fa-save"></i> Limpiar</button>
            </div>
            <div class="col-1">
                <br />
                <button type="button" class="btn btn-warning btn-sm" id="btnSolicitudEPP"
                        data-bs-toggle="modal" data-bs-target="#modalSolicitudEPP">
                    <i class="fa-solid fa-shield"></i> Solicitar EPP
                </button>
            </div>

            <div class="col-2">
                <br />
                <button type="button" class="btn btn-info btn-sm" id="btnSolicitudTool"
                        data-bs-toggle="modal" data-bs-target="#modalSolicitudTool">
                    <i class="fa-solid fa-toolbox"></i> Solicitar Herramientas
                </button>
            </div>

            <div class="col-2">
                <br />
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnMisSolicitudes"
                        style="display:none" data-bs-toggle="modal" data-bs-target="#modalMisSolicitudes">
                    <i class="fas fa-clipboard-list"></i> Solicitudes realizadas <span class="badge bg-primary">0</span>
                </button>
                <button type="button" class="btn btn-dark btn-sm" id="btnEntregas"
                        style="display:none" data-bs-toggle="modal" data-bs-target="#modalEntregas">
                    <i class="fas fa-truck"></i> Entregas pendientes <span class="badge bg-danger">0</span>
                </button>
            </div>
        </div>
        <br />

        <div class="row">
            <div class="row">
                <!-- Equipo de Protección Básico -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        Equipo de Protección Básico
                    </div>
                    <div class="table-responsive">
                        <table class="table-sm mb-0">
                        <tbody id="listeppbasico"></tbody>
                        </table>
                    </div>
                    </div>
                </div>

                <!-- Equipo de Protección Específico -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        Equipo de Protección Específico
                    </div>
                    <div class="table-responsive">
                        <table class="table-sm mb-0">
                        <tbody id="listeppespecifico"></tbody>
                        </table>
                    </div>
                    </div>
                </div>

                <!-- BPM -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            BPM
                        </div>
                        <div class="table-responsive">
                            <table class="table-sm mb-0">
                            <tbody id="listeppbpm"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comentarios -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <label for="comentario" class="form-label fw-bold">Comentarios</label>
                            <textarea class="form-control form-control-sm" id="comentario" rows="3" placeholder="Escribe tus observaciones aquí..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="table-responsive" style="height: 350px;">
                    <table class="table text-center">
                        <thead class="table-dark">
                            <th>Folio</th>
                            <th>Noemp</th>
                            <th>Nombre</th>
                            <th>Departamento</th>
                            <th>Comentario</th>                            
                            <th></th>
                        </thead>
                        <tbody id="tbleppenc">

                        </tbody>
                    </table>
                </div>
            </div>
        </form>

    <!-- Modal de consulta de observaciones EPP -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">New message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive" style="height: 500px;">
                        <table class="table">
                            <thead class="table-dark text-center">
                                <th>Noemp</th>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <!-- <th>Fecha</th> -->
                                <th>Equipo</th>
                                <th>Res</th>
                            </thead>
                            <tbody id="tblsubenc">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalSolicitudEPP" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Solicitud de EPP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                    <div class="col-2">
                        <small>No Emp</small>
                        <input type="number" class="form-control form-control-sm" id="solEmp" />
                    </div>
                    <div class="col-4">
                        <small>Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="solNombre" readonly />
                    </div>
                    <div class="col-3">
                        <small>Departamento</small>
                        <input type="text" class="form-control form-control-sm" id="solDepartamento" readonly />
                    </div>
                        <div class="col-3">
                            <small>Puesto</small>
                            <input type="text" class="form-control form-control-sm" id="solPuesto" readonly />
                        </div>
                    </div>
                    
                    <div class="row align-items-end">                        
                        <div class="col-md-4">
                            <label for="opciones" class="form-label mb-1">Motivo</label>
                            <select class="form-select form-select-sm" id="opciones">                        
                            <option selected disabled>Elige una opción</option>
                            <option value="Olvido">Olvido</option>
                            <option value="Perdida">Pérdida</option>
                            <option value="Reposicion">Reposición vida útil</option>
                            <option value="Nuevo/Cambio">Nuevo ingreso / Cambio de puesto</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-1">Clave de recepción</label>
                            <input type="password" class="form-control form-control-sm" id="claveEPP"
                                placeholder="Créala tu clave, la usarás al recibir tu solicitud" maxlength="20" />
                        </div>
                    
                        <div class="col-md-4">
                            <button type="button" class="btn bg-warning btn-sm" id="btnCargarEPP">
                            <i class="fas fa-search"></i> Buscar EPP correspondiente
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center my-3">
                        <hr class="flex-grow-1" style="border-top: 2px solid dark;">
                            <span class="px-2 fw-bold text-uppercase" style="color: dark;">Datos de solicitud</span>
                        <hr class="flex-grow-1" style="border-top: 2px solid dark;">
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">                        
                        <table class="table table-hover align-middle">
                            <thead class="table-dark text-center">
                            <tr>
                                <th scope="col">Categoría</th>
                                <th scope="col">Equipo</th>
                                <th scope="col">Sí</th>
                                <th scope="col">No</th>
                                <th scope="col">Cantidad</th>                                
                            </tr>
                            </thead>
                            <tbody id="tblSolicitudEPP"></tbody>
                        </table>
                    </div>                    

                    <div class="d-flex align-items-center my-3">
                        <hr class="flex-grow-1" style="border-top: 2px solid dark;">
                            <span class="px-2 fw-bold text-uppercase" style="color: dark;">Enviar solicitud</span>
                        <hr class="flex-grow-1" style="border-top: 2px solid dark;">
                    </div>

                    <div class="row align-items-end">                                                
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success btn-sm" id="btnguardarEPP">
                            <i class="fa-regular fa-floppy-disk"></i> Realizar solicitud
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSolicitudTool" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Solicitud de Herramientas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                    <div class="col-2">
                        <small>No Emp</small>
                        <input type="number" class="form-control form-control-sm" id="solEmpT" />
                    </div>
                    <div class="col-4">
                        <small>Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="solNombreT" readonly />
                    </div>
                    <div class="col-3">
                        <small>Departamento</small>
                        <input type="text" class="form-control form-control-sm" id="solDepartamentoT" readonly />
                    </div>
                    <div class="col-3">
                        <small>Puesto</small>
                        <input type="text" class="form-control form-control-sm" id="solPuestoT" readonly />
                    </div>
                    </div>

                    <div class="row align-items-end">                        
                        <div class="col-md-4">
                            <label for="opcionesT" class="form-label mb-1">Motivo</label>
                            <select class="form-select form-select-sm" id="opcionesT">                        
                            <option selected disabled>Elige una opción</option>
                            <option value="Olvido">Olvido</option>
                            <option value="Perdida">Pérdida</option>
                            <option value="Reposicion">Reposición vida útil</option>
                            <option value="Nuevo/Cambio">Nuevo ingreso / Cambio de puesto</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-1">Clave de recepción</label>
                            <input type="password" class="form-control form-control-sm" id="claveTool"
                                placeholder="Créala tu clave, la usarás al recibir tu solicitud" maxlength="20" />                            
                        </div>

                        <div class="col-md-4">
                            <button type="button" class="btn bg-warning btn-sm" id="btnCargarTools">
                            <i class="fas fa-search"></i> Buscar herramientas
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center my-3">
                        <hr class="flex-grow-1" style="border-top: 2px solid dark;">
                            <span class="px-2 fw-bold text-uppercase" style="color: dark;">Datos de solicitud</span>
                        <hr class="flex-grow-1" style="border-top: 2px solid dark;">
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-dark text-center">
                            <th>Categoría</th>
                            <th>Equipo</th>
                            <th>Sí</th>
                            <th>No</th>
                            <th>Cantidad</th>
                            </thead>
                            <tbody id="tblSolicitudTool"></tbody>
                        </table>
                    </div>                    

                    <div class="d-flex align-items-center my-3">
                        <hr class="flex-grow-1" style="border-top: 2px solid dark;">
                            <span class="px-2 fw-bold text-uppercase" style="color: dark;">Enviar solicitud</span>
                        <hr class="flex-grow-1" style="border-top: 2px solid dark;">
                    </div>

                    <div class="row align-items-end">                                                
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success btn-sm" id="btnguardarTools">
                            <i class="fa-regular fa-floppy-disk"></i> Realizar solicitud
                            </button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de solicitudes pendientes -->
    <div class="modal fade" id="modalMisSolicitudes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Solicitudes realizadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                <table class="table table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Folio</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody id="tblMisSolicitudes"></tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Entregas (almacén) -->
    <div class="modal fade" id="modalEntregas" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Entregas pendientes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                    <div class="col-2"><small>No Emp</small>
                        <input type="number" class="form-control form-control-sm" id="solEmpA" /></div>
                    <div class="col-4"><small>Nombre</small>
                        <input type="text" class="form-control form-control-sm" id="solNombreA" readonly /></div>
                    <div class="col-3"><small>Departamento</small>
                        <input type="text" class="form-control form-control-sm" id="solDepartamentoA" readonly /></div>
                    <div class="col-3"><small>Puesto</small>
                        <input type="text" class="form-control form-control-sm" id="solPuestoA" readonly /></div>
                    </div>
                    <div class="row mb-3">
                    <div class="col-6">
                        <button type="button" class="btn bg-warning btn-sm" id="btnBuscarPend">
                        <i class="fas fa-search"></i> Buscar pendientes
                        </button>
                    </div>
                    </div>
                    <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark text-center">
                        <tr>
                            <th>Folio</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="tblEntregas"></tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once("../index/footer.php") ?>
<script type="module" src="js/epp.js"></script>