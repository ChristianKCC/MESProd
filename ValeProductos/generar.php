<?php
require_once("../Session/seguridad.php");
require_once("../index/header.php");
?>

<style>
    .img-contenedor-padre{
        height: 160px;
        width: 220px;

        display: flex;
        flex-direction: column;
        
        .img-container{
            width: 100%;
            max-width: 250px;
            height: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 5px;
            border: 1px solid rgba(233, 231, 231, 0.64);
            padding: 10px;

            .img-producto{
                max-width: 100%;
                max-height: 100%;
                height: auto;
                width: auto;
                object-fit: contain;
            }
        }
    }
    
    
</style>

<div class="container p-4">
    <h5 class="tittlecont">Vale de Productos</h5>
    <!-- Formulario generar vale de productos -->
    <form id="formValeProductos">
        <div class="row text-center">
            <input type="hidden" name="id" id="id">
            <input type="hidden" name="status" id="status">
            <div class="col-1">
                <small class="fw-bold">Noemp</small>
                <input type="number" id="noemp" name="noemp" class="form-control form-control-md" min="0" /> 
            </div>
            <div class="col-1"><br />
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="empleadoActivo" disabled>
                    <input type="hidden" name="checkActivoVal" id="checkActivoVal">
                    <label class="form-check-label" for="externo">
                        Empleado
                    </label>
                </div>
            </div>
            <div class="col-4"><small class="fw-bold">Nombre</small><input type="text" name="nombre" id="nombre" class="form-control form-control-md" disabled></div>
            <div class="col-3"><small class="fw-bold">Departamento</small><select id="departamento" name="departamento" class="form-control form-control-md" disabled></select></div>
            <div class="col-3"><small class="fw-bold">Puesto</small><select id="puesto" name="puesto" class="form-control form-control-md" disabled></select></div>
        </div>
        <br>
        <div class="row text-center">
            <div class="col-2">
                 <div class="row justify-content-center">
                    <div class="col-12" id="img-contenedor-padre">
                        <small>Imagen del Producto</small>
                        <center>
                            <div class="img-container">
                                <img src="#" alt="" class="" id="img-producto" width="60%">
                            </div>                           
                        </center>
                    </div>
                </div>
            </div>
            <div class="col-8">
                <div class="row row-center">
                    <div class="col-2"><small class="fw-bold">Clave Producto</small><input type="number" id="claveProducto" name="claveProducto" class="form-control form-control-md" min="0" /></div>
                    <!-- <div class="col-2"><small>Categoria</small><select name="categoria" id="categoria" class="form-control form-control-md"></select></div> -->
                    <!-- <div class="col-2"><small>Subcategoria</small><select name="subcategoria" id="subcategoria" class="form-control form-control-md"></select></div> -->
                    <div class="col-5"><small class="fw-bold">Producto</small><input type="text" name="subcategoria" id="subcategoria" class="form-control form-control-md" readonly></div>
                    <input type="hidden" name="idSubCategoria" id="idSubCategoria">
                    <div class="col-3"><small class="fw-bold">Categoria</small><input type="text" name="categoria" id="categoria" class="form-control form-control-md" readonly></div>
                    <input type="hidden" name="idCategoria" id="idCategoria">
                    <div class="col-2"><small class="fw-bold">Piezas en corrugado</small><input type="text" name="cantidad" id="cantidad" class="form-control form-control-md" min="0" readonly></div>
                </div>
                <div class="row text-center justify-content-end">
                    <div class="col-2"><small class="fw-bold">Precio</small><input type="text" name="precio" id="precio" class="form-control form-control-md" readonly></div>
                </div>
            </div>
            <div class="col-2 text-center">
                <div class="col-12"><small class="fw-bold">Fecha de revision</small><input type="date" id="fecharevision" name="" class="form-control form-control-md" readonly /></div>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-1"></div>
            <div class="col-2">
            </div>
            <div class="col-9">
                <div class="row text-center justify-content-end">
                <div class="col-3" id="contenedorContraseña">
                    <small>Contraseña</small>
                    <input type="password" name="password" id="password" class="form-control form-control-md" />
                </div>
            </div>
            <div class="row justify-content-end">
                <div class="col-1"><br><button class="btn btn-sm bg-target" id="saveValeProducto"><i class="fas fa-save"></i> Guardar</button></div>
                <div class="col-1"><br><button class="btn btn-sm btn-success" id="descargarExcel"><i class="fas fa-download" ></i> Excel</button></div>
                <div class="col-1"><br><button class="btn btn-sm btn-secondary" id="limpiarFormulario"><i class="fas fa-undo-alt"></i> Limpiar</button></div>
            </div>  
            </div>
        </div>
        <!-- <div class="row justify-content-end"> -->
            <!-- <div class="row text-center justify-content-end">
                <div class="col-3" id="contenedorContraseña">
                    <small>Contraseña</small>
                    <input type="password" name="password" id="password" class="form-control form-control-md" />
                </div>
            </div>
            <div class="row justify-content-end">
                <div class="col-1"><br><button class="btn btn-sm bg-target" id="saveValeProducto"><i class="fas fa-save"></i> Guardar</button></div>
                <div class="col-1"><br><button class="btn btn-sm btn-success" id="descargarExcel"><i class="fas fa-download" ></i> Excel</button></div>
                <div class="col-1"><br><button class="btn btn-sm btn-secondary" id="limpiarFormulario"><i class="fas fa-undo-alt"></i> Limpiar</button></div>
            </div>             -->
        <!-- </div> -->

    </form>

    <!-- Tabla -->
    <div class="my-4 table-responsive" style="height: 450px;">
        <table class="table table-bordered" id="tblValeProductos">
            <thead class="table-dark">
                <th>IDVale</th>
                <th>NoEmp</th>
                <th>Empleado</th>
                <th>Nombre</th>
                <th>Departamento</th>
                <th>Puesto</th>
                <th>Categoria</th>
                <th>Subcategoria</th>
                <!-- <th>Descripción</th> -->
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Fecha</th>
                <th></th>
                <th></th>
            </thead>
            <tbody id="tblconsultas">
                
            </tbody>
        </table>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/consultas.js"></script>
